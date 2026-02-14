<?php

namespace App\Jobs\Connectors;

use App\Jobs\ConnectorJob;
use App\Models\Node;
use App\Models\Edge;
use Illuminate\Support\Facades\Http;

/**
 * Demo connector: fetch threat intelligence from AlienVault OTX.
 *
 * Mengambil pulses (threat reports) dari OTX API dan menyimpan
 * indicators + threat actors sebagai Node + Edge di knowledge graph.
 * API: https://otx.alienvault.com/api/v1/pulses/subscribed
 */
class OtxConnector extends ConnectorJob
{
    private const BASE_URL = 'https://otx.alienvault.com/api/v1';

    protected function run(): array
    {
        $config = $this->integration->config ?? [];
        $apiKey = $config['api_key'] ?? null;

        if (!$apiKey) {
            throw new \RuntimeException('OTX API key tidak diset. Konfigurasikan di integration config.');
        }

        $limit = $config['limit'] ?? 10;

        $response = Http::timeout(60)
            ->withHeaders(['X-OTX-API-KEY' => $apiKey])
            ->get(self::BASE_URL . "/pulses/subscribed?limit={$limit}&page=1");

        if (!$response->successful()) {
            throw new \RuntimeException("OTX API returned HTTP {$response->status()}");
        }

        $data = $response->json();
        $pulses = $data['results'] ?? [];
        $nodesCreated = 0;
        $edgesCreated = 0;

        foreach ($pulses as $pulse) {
            $pulseId = $pulse['id'] ?? null;
            if (!$pulseId) continue;

            // Create pulse as a campaign/report node
            $pulseStixId = "otx-pulse-{$pulseId}";
            $pulseNode = Node::where('stix_id', $pulseStixId)->first();

            if (!$pulseNode) {
                $pulseNode = Node::create([
                    'type' => 'campaign',
                    'name' => $pulse['name'] ?? "OTX Pulse {$pulseId}",
                    'description' => $pulse['description'] ?? null,
                    'severity' => $this->guessSeverity($pulse),
                    'confidence' => 75,
                    'stix_id' => $pulseStixId,
                    'raw' => [
                        'otx_id'      => $pulseId,
                        'tags'        => $pulse['tags'] ?? [],
                        'author'      => $pulse['author_name'] ?? null,
                        'created'     => $pulse['created'] ?? null,
                        'modified'    => $pulse['modified'] ?? null,
                        'adversary'   => $pulse['adversary'] ?? null,
                        'tlp'         => $pulse['TLP'] ?? null,
                        'attack_ids'  => $pulse['attack_ids'] ?? [],
                    ],
                ]);
                $nodesCreated++;
            }

            // Import indicators from pulse
            $indicators = $pulse['indicators'] ?? [];
            foreach (array_slice($indicators, 0, 50) as $indicator) {
                $indValue = $indicator['indicator'] ?? null;
                $indType = $indicator['type'] ?? 'unknown';
                if (!$indValue) continue;

                $indStixId = "otx-ind-" . md5($indValue);
                $indNode = Node::where('stix_id', $indStixId)->first();

                if (!$indNode) {
                    $indNode = Node::create([
                        'type' => 'observable',
                        'name' => $indValue,
                        'description' => "OTX indicator: {$indType}",
                        'severity' => 'medium',
                        'confidence' => 70,
                        'stix_id' => $indStixId,
                        'raw' => [
                            'indicator_type' => $indType,
                            'title'          => $indicator['title'] ?? null,
                            'created'        => $indicator['created'] ?? null,
                        ],
                    ]);
                    $nodesCreated++;
                }

                // Create edge: pulse → indicates → indicator
                $edgeExists = Edge::where('from_node_id', $pulseNode->id)
                    ->where('to_node_id', $indNode->id)
                    ->where('type', 'indicates')
                    ->exists();

                if (!$edgeExists) {
                    Edge::create([
                        'from_node_id' => $pulseNode->id,
                        'to_node_id'   => $indNode->id,
                        'type'         => 'indicates',
                        'confidence'   => 70,
                    ]);
                    $edgesCreated++;
                }
            }
        }

        return [
            'nodes_created' => $nodesCreated,
            'edges_created' => $edgesCreated,
            'message' => "Processed {$limit} pulses: {$nodesCreated} nodes, {$edgesCreated} edges created.",
        ];
    }

    private function guessSeverity(array $pulse): string
    {
        $tags = array_map('strtolower', $pulse['tags'] ?? []);
        if (array_intersect($tags, ['apt', 'ransomware', 'critical'])) return 'critical';
        if (array_intersect($tags, ['malware', 'exploit', 'phishing'])) return 'high';
        return 'medium';
    }
}
