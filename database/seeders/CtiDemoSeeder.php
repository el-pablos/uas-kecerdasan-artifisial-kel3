<?php

namespace Database\Seeders;

use App\Models\CaseModel;
use App\Models\CaseTask;
use App\Models\Edge;
use App\Models\Integration;
use App\Models\Node;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class CtiDemoSeeder extends Seeder
{
    /**
     * Seed demo data for the CTI Platform.
     * Creates a realistic threat-intelligence scenario:
     *   APT-X → uses → FooRAT → targets → Acme Corp
     *   Campaign "Operation Shadow" attributed-to APT-X
     *   Observables, indicators, vulnerability, case + tasks, tags
     */
    public function run(): void
    {
        $user = User::first();
        $uid  = $user?->id;

        // --- Tags ---
        $tagApt = Tag::firstOrCreate(['name' => 'APT'], ['color' => '#ef4444']);
        $tagMalware = Tag::firstOrCreate(['name' => 'Malware'], ['color' => '#f59e0b']);
        $tagCritical = Tag::firstOrCreate(['name' => 'Critical'], ['color' => '#dc2626']);
        $tagHigh = Tag::firstOrCreate(['name' => 'High-Priority'], ['color' => '#f97316']);
        $tagIntel = Tag::firstOrCreate(['name' => 'Threat-Intel'], ['color' => '#6366f1']);

        // --- Threat Actors ---
        $aptX = Node::firstOrCreate(
            ['name' => 'APT-X', 'type' => 'threat-actor'],
            [
                'description'  => 'Advanced persistent threat group targeting financial and government sectors in Southeast Asia. Known for sophisticated spear-phishing campaigns and custom malware toolkits.',
                'confidence'   => 85,
                'severity'     => 'critical',
                'source_ref'   => 'MITRE:G0100',
                'first_seen'   => '2023-01-15',
                'last_seen'    => '2024-12-20',
                'raw'          => ['aliases' => ['Shadow Panda', 'DarkNexus'], 'sophistication' => 'advanced', 'goals' => ['Espionage', 'Financial Gain']],
                'created_by'   => $uid,
            ]
        );
        $aptX->tags()->syncWithoutDetaching([$tagApt->id, $tagCritical->id]);

        // --- Malware ---
        $fooRAT = Node::firstOrCreate(
            ['name' => 'FooRAT', 'type' => 'malware'],
            [
                'description'  => 'Custom remote access trojan (RAT) used by APT-X. Communicates over encrypted HTTPS with C2 servers. Capable of keylogging, screen capture, and lateral movement.',
                'confidence'   => 90,
                'severity'     => 'high',
                'source_ref'   => 'MITRE:S0600',
                'first_seen'   => '2023-06-01',
                'last_seen'    => '2024-11-15',
                'raw'          => ['malware_types' => ['RAT', 'Trojan'], 'is_family' => true],
                'created_by'   => $uid,
            ]
        );
        $fooRAT->tags()->syncWithoutDetaching([$tagMalware->id]);

        // --- Identity (target) ---
        $acme = Node::firstOrCreate(
            ['name' => 'Acme Corp', 'type' => 'identity'],
            [
                'description'  => 'Major financial services corporation. Primary target of APT-X operations in Q3-Q4 2024.',
                'confidence'   => 75,
                'severity'     => 'medium',
                'created_by'   => $uid,
            ]
        );

        // --- Campaign ---
        $campaign = Node::firstOrCreate(
            ['name' => 'Operation Shadow', 'type' => 'campaign'],
            [
                'description'  => 'Multi-stage campaign combining spear-phishing with watering hole attacks to compromise financial sector targets.',
                'confidence'   => 80,
                'severity'     => 'high',
                'first_seen'   => '2024-03-01',
                'last_seen'    => '2024-12-15',
                'raw'          => ['objective' => 'Data Exfiltration'],
                'created_by'   => $uid,
            ]
        );
        $campaign->tags()->syncWithoutDetaching([$tagIntel->id]);

        // --- Technique ---
        $spearPhishing = Node::firstOrCreate(
            ['name' => 'Spear Phishing Attachment', 'type' => 'technique'],
            [
                'description'  => 'Adversaries send spear-phishing emails with malicious attachments to gain initial access.',
                'confidence'   => 95,
                'severity'     => 'high',
                'source_ref'   => 'T1566.001',
                'created_by'   => $uid,
            ]
        );

        // --- Vulnerability ---
        $vuln = Node::firstOrCreate(
            ['name' => 'CVE-2024-21762', 'type' => 'vulnerability'],
            [
                'description'  => 'FortiOS out-of-bound write vulnerability allowing RCE via specially crafted HTTP requests.',
                'confidence'   => 95,
                'severity'     => 'critical',
                'source_ref'   => 'CVE-2024-21762',
                'created_by'   => $uid,
            ]
        );

        // --- Observable (IP) ---
        $c2ip = Node::firstOrCreate(
            ['name' => '198.51.100.42', 'type' => 'observable'],
            [
                'description'  => 'Command & Control server IP observed in FooRAT traffic analysis.',
                'confidence'   => 70,
                'severity'     => 'high',
                'raw'          => ['observable_type' => 'ipv4-addr'],
                'first_seen'   => '2024-08-10',
                'created_by'   => $uid,
            ]
        );

        // --- Indicator ---
        $indicator = Node::firstOrCreate(
            ['name' => 'SHA256:abc123...def789', 'type' => 'indicator'],
            [
                'description'  => 'File hash indicator for FooRAT dropper binary.',
                'confidence'   => 88,
                'severity'     => 'high',
                'raw'          => ['pattern' => "[file:hashes.'SHA-256' = 'abc123def789']", 'pattern_type' => 'stix'],
                'created_by'   => $uid,
            ]
        );

        // --- Infrastructure ---
        $infra = Node::firstOrCreate(
            ['name' => 'C2 Proxy Network', 'type' => 'infrastructure'],
            [
                'description'  => 'Network of compromised servers used as C2 relay proxies by APT-X.',
                'confidence'   => 65,
                'severity'     => 'medium',
                'created_by'   => $uid,
            ]
        );

        // ===========================
        // EDGES (Relationships)
        // ===========================
        $edges = [
            [$aptX->id,         'uses',           $fooRAT->id,        90],
            [$aptX->id,         'targets',        $acme->id,          80],
            [$campaign->id,     'attributed-to',  $aptX->id,          85],
            [$campaign->id,     'uses',           $spearPhishing->id, 90],
            [$fooRAT->id,       'exploits',       $vuln->id,          75],
            [$c2ip->id,         'related-to',     $fooRAT->id,        70],
            [$indicator->id,    'indicates',       $fooRAT->id,        88],
            [$infra->id,        'related-to',     $c2ip->id,          65],
            [$aptX->id,         'uses',           $infra->id,         60],
        ];

        foreach ($edges as [$from, $type, $to, $conf]) {
            Edge::firstOrCreate(
                ['from_node_id' => $from, 'to_node_id' => $to, 'type' => $type],
                ['confidence' => $conf, 'created_by' => $uid]
            );
        }

        // ===========================
        // CASE + TASKS
        // ===========================
        $case = CaseModel::firstOrCreate(
            ['title' => 'INC-2024-001: APT-X Intrusion Response'],
            [
                'type'        => 'incident',
                'severity'    => 'critical',
                'status'      => 'in-progress',
                'description' => 'Active investigation into APT-X intrusion via Operation Shadow campaign. FooRAT detected on multiple endpoints.',
                'owner_id'    => $uid,
                'due_date'    => now()->addDays(7),
            ]
        );

        $tasks = [
            ['title' => 'Isolate compromised endpoints',       'status' => 'done'],
            ['title' => 'Collect forensic disk images',        'status' => 'done'],
            ['title' => 'Analyze FooRAT C2 communication',     'status' => 'in-progress'],
            ['title' => 'Block C2 IP addresses in firewall',   'status' => 'in-progress'],
            ['title' => 'Sweep network for lateral movement',  'status' => 'pending'],
            ['title' => 'Prepare incident report',             'status' => 'pending'],
        ];

        foreach ($tasks as $t) {
            CaseTask::firstOrCreate(
                ['case_id' => $case->id, 'title' => $t['title']],
                ['status' => $t['status'], 'assignee_id' => $uid]
            );
        }

        // ===========================
        // INTEGRATION (demo connector)
        // ===========================
        Integration::firstOrCreate(
            ['name' => 'MITRE ATT&CK Importer'],
            [
                'type'    => 'connector',
                'command' => 'ingest:mitre-attack',
                'status'  => 'idle',
                'config'  => ['version' => '15.1', 'source' => 'https://raw.githubusercontent.com/mitre-attack/attack-stix-data/master/enterprise-attack/enterprise-attack.json'],
            ]
        );

        Integration::firstOrCreate(
            ['name' => 'NVD CVE Feed'],
            [
                'type'     => 'connector',
                'schedule' => '0 */6 * * *',
                'status'   => 'idle',
                'config'   => [
                    'job_class' => \App\Jobs\Connectors\CveConnector::class,
                    'limit' => 20,
                ],
            ]
        );

        Integration::firstOrCreate(
            ['name' => 'AlienVault OTX'],
            [
                'type'     => 'connector',
                'schedule' => '0 */12 * * *',
                'status'   => 'idle',
                'config'   => [
                    'job_class' => \App\Jobs\Connectors\OtxConnector::class,
                    'api_key' => '',
                    'limit' => 10,
                ],
            ]
        );

        Integration::firstOrCreate(
            ['name' => 'ML Anomaly Feed'],
            [
                'type'     => 'feed',
                'schedule' => '*/5 * * * *',
                'status'   => 'idle',
                'config'   => ['endpoint' => 'http://localhost:5000/predict'],
            ]
        );

        $this->command->info('CTI Demo data seeded successfully!');
        $this->command->info("  - Nodes:  " . Node::count());
        $this->command->info("  - Edges:  " . Edge::count());
        $this->command->info("  - Cases:  " . CaseModel::count());
        $this->command->info("  - Tags:   " . Tag::count());
    }
}
