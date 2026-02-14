<?php

namespace App\Console\Commands;

use App\Models\Edge;
use App\Models\Node;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Artisan command untuk ingest MITRE ATT&CK Enterprise data.
 *
 * Mengambil STIX 2.1 bundle dari GitHub MITRE repository,
 * lalu meng-import attack-pattern, malware, intrusion-set, tool,
 * campaign, dan relationship sebagai Node/Edge di knowledge graph.
 *
 * Usage:
 *   php artisan ingest:mitre-attack
 *   php artisan ingest:mitre-attack --limit=50
 *   php artisan ingest:mitre-attack --dry-run
 */
class IngestMitreAttack extends Command
{
    protected $signature = 'ingest:mitre-attack
        {--limit=0 : Max objects to import (0 = unlimited)}
        {--dry-run : Simulate without writing to DB}
        {--source=github : Data source (github or local)}
        {--file= : Path to local STIX JSON file}';

    protected $description = 'Import MITRE ATT&CK Enterprise data ke knowledge graph';

    /**
     * Map STIX type ke node type kita.
     */
    private const TYPE_MAP = [
        'attack-pattern'  => 'attack-pattern',
        'malware'         => 'malware',
        'intrusion-set'   => 'intrusion-set',
        'tool'            => 'tool',
        'campaign'        => 'campaign',
        'course-of-action' => 'course-of-action',
        'x-mitre-tactic'  => 'x-mitre-tactic',
    ];

    /**
     * Type colors for visual mapping.
     */
    private const TYPE_COLORS = [
        'attack-pattern'   => '#e74c3c',
        'malware'          => '#9b59b6',
        'intrusion-set'    => '#e67e22',
        'tool'             => '#3498db',
        'campaign'         => '#f39c12',
        'course-of-action' => '#2ecc71',
        'x-mitre-tactic'   => '#1abc9c',
    ];

    private const TYPE_ICONS = [
        'attack-pattern'   => 'ri-sword-line',
        'malware'          => 'ri-bug-line',
        'intrusion-set'    => 'ri-group-line',
        'tool'             => 'ri-tools-line',
        'campaign'         => 'ri-flag-line',
        'course-of-action' => 'ri-shield-check-line',
        'x-mitre-tactic'   => 'ri-compass-3-line',
    ];

    private const GITHUB_URL = 'https://raw.githubusercontent.com/mitre/cti/master/enterprise-attack/enterprise-attack.json';

    private int $nodeCount   = 0;
    private int $edgeCount   = 0;
    private int $skippedCount = 0;

    public function handle(): int
    {
        $this->info('🛡️  MITRE ATT&CK Enterprise Importer');
        $this->info('====================================');

        $limit  = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY-RUN mode — tidak ada data yang ditulis ke database.');
        }

        // 1. Fetch STIX bundle
        $bundle = $this->fetchBundle();
        if (!$bundle) {
            return self::FAILURE;
        }

        $objects = $bundle['objects'] ?? [];
        $this->info("Total STIX objects: " . count($objects));

        // 2. Separate objects by kind
        $entities = [];
        $relationships = [];
        $stixIdMap = []; // stix_id => our node UUID

        foreach ($objects as $obj) {
            $type = $obj['type'] ?? '';
            if (isset(self::TYPE_MAP[$type])) {
                $entities[] = $obj;
            } elseif ($type === 'relationship') {
                $relationships[] = $obj;
            }
        }

        $this->info("Importable entities: " . count($entities));
        $this->info("Relationships: " . count($relationships));

        if ($limit > 0) {
            $entities = array_slice($entities, 0, $limit);
            $this->info("Limiting entities to: {$limit}");
        }

        // 3. Import entities as Nodes
        $bar = $this->output->createProgressBar(count($entities));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $bar->setMessage('Importing entities...');
        $bar->start();

        foreach ($entities as $obj) {
            $stixId = $obj['id'] ?? null;
            $type   = self::TYPE_MAP[$obj['type']];
            $name   = $obj['name'] ?? $obj['id'] ?? 'Unknown';

            // Check if already imported (by stix_id in raw)
            $existing = Node::where('stix_id', $stixId)->first();
            if ($existing) {
                $stixIdMap[$stixId] = $existing->id;
                $this->skippedCount++;
                $bar->advance();
                continue;
            }

            // Extract ATT&CK metadata
            $externalRefs = $obj['external_references'] ?? [];
            $attackId = null;
            $attackUrl = null;
            foreach ($externalRefs as $ref) {
                if (($ref['source_name'] ?? '') === 'mitre-attack') {
                    $attackId = $ref['external_id'] ?? null;
                    $attackUrl = $ref['url'] ?? null;
                    break;
                }
            }

            $description = $obj['description'] ?? null;
            // Truncate very long descriptions
            if ($description && strlen($description) > 2000) {
                $description = Str::limit($description, 2000);
            }

            $severity = $this->guessSeverity($obj);
            $confidence = 85; // MITRE data is curated

            if (!$dryRun) {
                $node = Node::create([
                    'name'        => $name,
                    'type'        => $type,
                    'stix_id'     => $stixId,
                    'description' => $description,
                    'severity'    => $severity,
                    'confidence'  => $confidence,
                    'raw'         => [
                        'mitre_attack_id' => $attackId,
                        'mitre_url'       => $attackUrl,
                        'kill_chain_phases' => $obj['kill_chain_phases'] ?? [],
                        'x_mitre_platforms' => $obj['x_mitre_platforms'] ?? [],
                        'aliases'           => $obj['aliases'] ?? [],
                        'external_references' => $externalRefs,
                        'created'           => $obj['created'] ?? null,
                        'modified'          => $obj['modified'] ?? null,
                    ],
                ]);
                $stixIdMap[$stixId] = $node->id;
            } else {
                // Dry-run: fake UUID
                $stixIdMap[$stixId] = Str::uuid()->toString();
            }

            $this->nodeCount++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // 4. Import relationships as Edges
        $this->info('Importing relationships...');
        $relBar = $this->output->createProgressBar(count($relationships));
        $relBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $relBar->setMessage('Creating edges...');
        $relBar->start();

        foreach ($relationships as $rel) {
            $sourceStix = $rel['source_ref'] ?? null;
            $targetStix = $rel['target_ref'] ?? null;
            $relType    = $rel['relationship_type'] ?? 'related-to';

            $fromId = $stixIdMap[$sourceStix] ?? null;
            $toId   = $stixIdMap[$targetStix] ?? null;

            if (!$fromId || !$toId) {
                $this->skippedCount++;
                $relBar->advance();
                continue;
            }

            if (!$dryRun) {
                // Check if edge already exists
                $exists = Edge::where('from_node_id', $fromId)
                    ->where('to_node_id', $toId)
                    ->where('type', $relType)
                    ->exists();

                if (!$exists) {
                    Edge::create([
                        'from_node_id' => $fromId,
                        'to_node_id'   => $toId,
                        'type'         => $relType,
                        'description'  => $rel['description'] ?? null,
                        'confidence'   => $rel['confidence'] ?? 80,
                        'stix_id'      => $rel['id'] ?? null,
                        'raw'          => [
                            'created'  => $rel['created'] ?? null,
                            'modified' => $rel['modified'] ?? null,
                        ],
                    ]);
                    $this->edgeCount++;
                } else {
                    $this->skippedCount++;
                }
            } else {
                $this->edgeCount++;
            }

            $relBar->advance();
        }

        $relBar->finish();
        $this->newLine(2);

        // 5. Summary
        $this->info('✅ Import selesai!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Nodes Created',        $this->nodeCount],
                ['Edges Created',        $this->edgeCount],
                ['Skipped (duplicates)', $this->skippedCount],
            ]
        );

        if ($dryRun) {
            $this->warn('Ini dry-run — tidak ada perubahan di database.');
        }

        return self::SUCCESS;
    }

    /**
     * Fetch STIX bundle dari GitHub atau file lokal.
     */
    private function fetchBundle(): ?array
    {
        $source = $this->option('source');

        if ($source === 'local' || $this->option('file')) {
            $path = $this->option('file') ?? storage_path('app/mitre-attack.json');
            $this->info("Loading from local file: {$path}");

            if (!file_exists($path)) {
                $this->error("File not found: {$path}");
                return null;
            }

            $json = json_decode(file_get_contents($path), true);
            if (!$json) {
                $this->error('Invalid JSON file.');
                return null;
            }
            return $json;
        }

        // Fetch from GitHub
        $this->info('Fetching MITRE ATT&CK bundle from GitHub...');
        $this->info('URL: ' . self::GITHUB_URL);
        $this->warn('(File ~25MB, mungkin butuh beberapa detik)');

        try {
            $response = Http::timeout(120)->get(self::GITHUB_URL);

            if (!$response->successful()) {
                $this->error("HTTP Error: {$response->status()}");
                return null;
            }

            $json = $response->json();
            if (!$json || !isset($json['objects'])) {
                $this->error('Invalid STIX bundle format.');
                return null;
            }

            return $json;
        } catch (\Exception $e) {
            $this->error("Failed to fetch: {$e->getMessage()}");
            $this->info('Tip: download manual lalu jalankan dengan --source=local --file=path/to/file.json');
            return null;
        }
    }

    /**
     * Guess severity from object metadata.
     */
    private function guessSeverity(array $obj): string
    {
        // Check kill chain phases — execution/initial-access = higher severity
        $phases = collect($obj['kill_chain_phases'] ?? [])->pluck('phase_name')->toArray();

        $criticalPhases = ['execution', 'privilege-escalation', 'defense-evasion'];
        $highPhases     = ['initial-access', 'persistence', 'lateral-movement'];

        foreach ($phases as $phase) {
            if (in_array($phase, $criticalPhases)) return 'critical';
            if (in_array($phase, $highPhases)) return 'high';
        }

        // Malware and intrusion-sets are at least medium
        $type = $obj['type'] ?? '';
        if (in_array($type, ['malware', 'intrusion-set'])) return 'medium';

        return 'low';
    }
}
