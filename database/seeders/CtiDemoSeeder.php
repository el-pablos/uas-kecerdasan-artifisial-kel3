<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\CaseItem;
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
     * Seed comprehensive CTI demo data.
     *
     * Creates 5 interconnected threat scenarios:
     *   1. APT-X / Operation Shadow  (financial sector espionage)
     *   2. Lazarus Group / WannaCry  (ransomware + crypto theft)
     *   3. Sandworm / BlackEnergy    (critical infrastructure / ICS)
     *   4. FIN7 / Carbanak           (POS / retail financial fraud)
     *   5. OceanLotus / APT32        (media & human-rights targeting)
     *
     * Total: ~50 nodes, ~60 edges, 5 cases, 30+ tasks, 20+ activity logs
     */
    public function run(): void
    {
        $user = User::first();
        $uid  = $user?->id;

        // ╔══════════════════════════════════════╗
        // ║              TAGS                    ║
        // ╚══════════════════════════════════════╝
        $tags = [];
        $tagData = [
            'APT'            => '#ef4444',
            'Malware'        => '#f59e0b',
            'Critical'       => '#dc2626',
            'High-Priority'  => '#f97316',
            'Threat-Intel'   => '#6366f1',
            'Ransomware'     => '#7c3aed',
            'State-Sponsored'=> '#1d4ed8',
            'Financial'      => '#059669',
            'ICS-SCADA'      => '#0891b2',
            'Zero-Day'       => '#be123c',
            'Supply-Chain'   => '#a16207',
            'Phishing'       => '#9333ea',
            'C2-Infra'       => '#64748b',
            'Espionage'      => '#475569',
            'Crypto'         => '#d97706',
        ];
        foreach ($tagData as $name => $color) {
            $tags[$name] = Tag::firstOrCreate(['name' => $name], ['color' => $color]);
        }

        // ╔══════════════════════════════════════╗
        // ║   SCENARIO 1: APT-X / Op Shadow     ║
        // ╚══════════════════════════════════════╝
        $this->command->info('🔴 Seeding Scenario 1: APT-X / Operation Shadow...');

        $aptX = $this->node('threat-actor', 'APT-X (Shadow Panda)', 'Advanced persistent threat group targeting financial and government sectors in Southeast Asia. Known for sophisticated spear-phishing and custom RAT toolkits. Aliases: Shadow Panda, DarkNexus.', 85, 'critical', $uid, 'MITRE:G0100', '2023-01-15', '2025-12-20', ['aliases' => ['Shadow Panda', 'DarkNexus'], 'sophistication' => 'advanced', 'goals' => ['Espionage', 'Financial Gain'], 'motivation' => 'state-sponsored']);
        $aptX->tags()->syncWithoutDetaching([$tags['APT']->id, $tags['Critical']->id, $tags['State-Sponsored']->id, $tags['Espionage']->id]);

        $fooRAT = $this->node('malware', 'FooRAT v2.1', 'Custom remote access trojan used by APT-X. Communicates over encrypted HTTPS with C2 servers. Capabilities: keylogging, screen capture, credential harvesting, lateral movement via PsExec/WMI.', 92, 'high', $uid, 'MITRE:S0600', '2023-06-01', '2025-11-15', ['malware_types' => ['RAT', 'Trojan'], 'is_family' => true, 'architecture' => ['x86', 'x64'], 'kill_chain_phases' => ['command-and-control', 'execution']]);
        $fooRAT->tags()->syncWithoutDetaching([$tags['Malware']->id]);

        $shadowLoader = $this->node('malware', 'ShadowLoader', 'First-stage loader/dropper used to deliver FooRAT. Packed with Themida, uses process hollowing to inject payload into svchost.exe.', 80, 'high', $uid, null, '2024-01-10', '2025-10-05', ['malware_types' => ['Loader', 'Dropper'], 'packer' => 'Themida']);
        $shadowLoader->tags()->syncWithoutDetaching([$tags['Malware']->id]);

        $acme = $this->node('identity', 'Acme Financial Corp', 'Major financial services corporation headquartered in Singapore. Primary target of APT-X operations in Q3-Q4 2024. 12,000+ employees, $8B annual revenue.', 75, 'medium', $uid);
        $govMinistry = $this->node('identity', 'Ministry of Finance (ID)', 'Indonesian Ministry of Finance. Secondary target identified in Operation Shadow spear-phishing campaign.', 70, 'medium', $uid);

        $opShadow = $this->node('campaign', 'Operation Shadow', 'Multi-stage campaign combining spear-phishing with watering hole attacks to compromise financial sector targets in ASEAN region. Active since March 2024.', 82, 'high', $uid, null, '2024-03-01', '2025-12-15', ['objective' => 'Data Exfiltration', 'target_sectors' => ['Financial Services', 'Government']]);
        $opShadow->tags()->syncWithoutDetaching([$tags['Threat-Intel']->id, $tags['Espionage']->id]);

        $spearPhish = $this->node('technique', 'Spear Phishing Attachment (T1566.001)', 'Adversaries send spear-phishing emails with malicious Office documents containing VBA macros to gain initial access.', 95, 'high', $uid, 'T1566.001');
        $processHollow = $this->node('technique', 'Process Hollowing (T1055.012)', 'Inject malicious code into suspended legitimate processes to evade detection.', 90, 'high', $uid, 'T1055.012');
        $credDump = $this->node('technique', 'OS Credential Dumping (T1003)', 'Dump credentials from LSASS memory using Mimikatz variant bundled with FooRAT.', 88, 'high', $uid, 'T1003');

        $cve2024_21762 = $this->node('vulnerability', 'CVE-2024-21762', 'FortiOS out-of-bound write vulnerability (CVSS 9.8) allowing RCE via specially crafted HTTP requests. Actively exploited in the wild by APT-X.', 95, 'critical', $uid, 'CVE-2024-21762', null, null, ['cvss' => 9.8, 'vendor' => 'Fortinet', 'product' => 'FortiOS']);
        $cve2024_21762->tags()->syncWithoutDetaching([$tags['Zero-Day']->id, $tags['Critical']->id]);

        $cve2024_3400 = $this->node('vulnerability', 'CVE-2024-3400', 'Palo Alto PAN-OS command injection (CVSS 10.0) in GlobalProtect feature. Zero-day exploited by multiple APT groups including APT-X.', 97, 'critical', $uid, 'CVE-2024-3400', null, null, ['cvss' => 10.0, 'vendor' => 'Palo Alto Networks', 'product' => 'PAN-OS']);
        $cve2024_3400->tags()->syncWithoutDetaching([$tags['Zero-Day']->id, $tags['Critical']->id]);

        $c2ip1 = $this->node('observable', '198.51.100.42', 'Primary C2 server IP for FooRAT. Hosted on bulletproof hosting in Moldova. Active since Aug 2024.', 72, 'high', $uid, null, '2024-08-10', null, ['observable_type' => 'ipv4-addr', 'geo' => 'MD']);
        $c2ip2 = $this->node('observable', '203.0.113.77', 'Secondary C2 fallback IP. Hosted on compromised VPS in Romania.', 65, 'medium', $uid, null, '2024-09-20', null, ['observable_type' => 'ipv4-addr', 'geo' => 'RO']);
        $c2domain = $this->node('observable', 'update-service.cloud', 'C2 domain masquerading as update service. Registered via Njalla privacy registrar.', 78, 'high', $uid, null, '2024-07-01', null, ['observable_type' => 'domain-name', 'registrar' => 'Njalla']);
        $hashFoo = $this->node('indicator', 'SHA256:a1b2c3d4e5f6...9z8y7x', 'File hash indicator for FooRAT dropper binary v2.1. Detection rate: 12/72 on VT at time of discovery.', 88, 'high', $uid, null, null, null, ['pattern' => "[file:hashes.'SHA-256' = 'a1b2c3d4e5f67890abcdef1234567890']", 'pattern_type' => 'stix', 'valid_from' => '2024-06-01']);
        $hashLoader = $this->node('indicator', 'SHA256:ff00ee11dd22...bb99', 'File hash for ShadowLoader packed binary. Themida-packed, ~1.2MB.', 82, 'high', $uid, null, null, null, ['pattern' => "[file:hashes.'SHA-256' = 'ff00ee11dd22cc33bb44aa5566778899']", 'pattern_type' => 'stix']);
        $yaraRule = $this->node('indicator', 'YARA: APT_X_FooRAT_Strings', 'YARA rule detecting FooRAT string artifacts in memory. Matches encoded config block and mutex name.', 85, 'high', $uid, null, null, null, ['pattern_type' => 'yara', 'rule_name' => 'APT_X_FooRAT_Strings']);

        $c2infra = $this->node('infrastructure', 'APT-X C2 Proxy Network', 'Network of 12+ compromised servers used as relay proxies for FooRAT C2. Spans Moldova, Romania, and Ukraine.', 68, 'medium', $uid);
        $c2infra->tags()->syncWithoutDetaching([$tags['C2-Infra']->id]);

        // Scenario 1 edges
        $this->edges($uid, [
            [$aptX, 'uses', $fooRAT, 92],
            [$aptX, 'uses', $shadowLoader, 80],
            [$aptX, 'targets', $acme, 82],
            [$aptX, 'targets', $govMinistry, 70],
            [$aptX, 'uses', $c2infra, 65],
            [$aptX, 'exploits', $cve2024_21762, 88],
            [$aptX, 'exploits', $cve2024_3400, 75],
            [$opShadow, 'attributed-to', $aptX, 85],
            [$opShadow, 'uses', $spearPhish, 90],
            [$opShadow, 'targets', $acme, 80],
            [$shadowLoader, 'drops', $fooRAT, 95],
            [$shadowLoader, 'uses', $processHollow, 88],
            [$fooRAT, 'uses', $credDump, 85],
            [$fooRAT, 'exploits', $cve2024_21762, 78],
            [$fooRAT, 'communicates-with', $c2ip1, 90],
            [$fooRAT, 'communicates-with', $c2ip2, 70],
            [$fooRAT, 'communicates-with', $c2domain, 82],
            [$c2ip1, 'related-to', $c2infra, 68],
            [$c2ip2, 'related-to', $c2infra, 60],
            [$c2domain, 'related-to', $c2ip1, 75],
            [$hashFoo, 'indicates', $fooRAT, 92],
            [$hashLoader, 'indicates', $shadowLoader, 85],
            [$yaraRule, 'indicates', $fooRAT, 88],
        ]);

        // ╔══════════════════════════════════════╗
        // ║  SCENARIO 2: Lazarus / WannaCry      ║
        // ╚══════════════════════════════════════╝
        $this->command->info('🟡 Seeding Scenario 2: Lazarus Group / WannaCry...');

        $lazarus = $this->node('threat-actor', 'Lazarus Group', 'North Korean state-sponsored threat group responsible for major financial heists and destructive attacks worldwide. Also known as HIDDEN COBRA, Guardians of Peace.', 95, 'critical', $uid, 'MITRE:G0032', '2009-01-01', '2025-12-01', ['aliases' => ['HIDDEN COBRA', 'Guardians of Peace', 'Zinc'], 'sophistication' => 'expert', 'goals' => ['Financial Gain', 'Disruption', 'Crypto Theft'], 'motivation' => 'state-sponsored', 'country' => 'KP']);
        $lazarus->tags()->syncWithoutDetaching([$tags['APT']->id, $tags['Critical']->id, $tags['State-Sponsored']->id, $tags['Crypto']->id]);

        $wannacry = $this->node('malware', 'WannaCry 2.0', 'Ransomware worm that leverages EternalBlue exploit for propagation. Encrypts files with RSA-2048 + AES-128. Demands BTC ransom. Variant 2.0 includes improved kill-switch evasion.', 98, 'critical', $uid, 'MITRE:S0366', '2017-05-12', '2025-06-01', ['malware_types' => ['Ransomware', 'Worm'], 'encryption' => 'RSA-2048 + AES-128']);
        $wannacry->tags()->syncWithoutDetaching([$tags['Malware']->id, $tags['Ransomware']->id]);

        $dtrack = $this->node('malware', 'DTrack', 'Modular backdoor used by Lazarus for reconnaissance and data exfiltration. Collects system info, browsing history, running processes, and network configs.', 85, 'high', $uid, 'MITRE:S0567', '2019-09-01', '2025-08-20', ['malware_types' => ['Backdoor', 'Spyware']]);
        $dtrack->tags()->syncWithoutDetaching([$tags['Malware']->id]);

        $cryptoExchange = $this->node('identity', 'CryptoVault Exchange', 'Major cryptocurrency exchange handling $2B daily volume. Primary financial target of Lazarus Group crypto theft operations.', 80, 'high', $uid);
        $hospital = $this->node('identity', 'NHS Hospital Trust', 'UK National Health Service hospital network. Impacted by WannaCry attack in 2017 and again targeted in 2025 resurge.', 85, 'high', $uid);

        $opAppleJeus = $this->node('campaign', 'Operation AppleJeus 2.0', 'Supply chain attack targeting cryptocurrency platforms. Trojanized trading applications distributed via fake companies and GitHub repos.', 88, 'critical', $uid, null, '2024-06-01', '2025-11-30', ['objective' => 'Cryptocurrency Theft', 'target_sectors' => ['Cryptocurrency', 'Financial Technology']]);
        $opAppleJeus->tags()->syncWithoutDetaching([$tags['Threat-Intel']->id, $tags['Crypto']->id, $tags['Supply-Chain']->id]);

        $eternalBlue = $this->node('technique', 'Exploit Public-Facing App (T1190)', 'EternalBlue SMBv1 exploit for initial access and lateral movement. Targets MS17-010 vulnerability.', 98, 'critical', $uid, 'T1190');
        $supplyChain = $this->node('technique', 'Supply Chain Compromise (T1195)', 'Compromise software supply chain to distribute malware via trusted update channels.', 90, 'critical', $uid, 'T1195');

        $cve2017_0144 = $this->node('vulnerability', 'CVE-2017-0144 (EternalBlue)', 'Windows SMBv1 remote code execution vulnerability. CVSS 8.1. Basis for WannaCry propagation mechanism.', 99, 'critical', $uid, 'CVE-2017-0144', null, null, ['cvss' => 8.1, 'vendor' => 'Microsoft', 'product' => 'Windows SMBv1']);
        $cve2017_0144->tags()->syncWithoutDetaching([$tags['Critical']->id]);

        $lazC2_1 = $this->node('observable', '185.62.58.207', 'Lazarus Group C2 IP observed serving DTrack payloads. Hosted in Russia.', 70, 'high', $uid, null, '2024-11-01', null, ['observable_type' => 'ipv4-addr', 'geo' => 'RU']);
        $lazDomain = $this->node('observable', 'cryptovault-update.com', 'Typosquatting domain used in AppleJeus supply chain attacks. Mimics legitimate exchange update portal.', 82, 'high', $uid, null, '2024-07-15', null, ['observable_type' => 'domain-name']);
        $lazHash = $this->node('indicator', 'SHA256:deadbeef0123...cafe', 'File hash for trojanized CryptoVault trading app installer. Signed with stolen code-signing certificate.', 90, 'critical', $uid, null, null, null, ['pattern_type' => 'stix', 'pattern' => "[file:hashes.'SHA-256' = 'deadbeef0123456789abcdef0000cafe']"]);
        $btcWallet = $this->node('observable', 'bc1q42lja79elem0anu8q8s3h2n...', 'Bitcoin wallet address used for WannaCry 2.0 ransom collection. Received 4.2 BTC as of analysis date.', 75, 'high', $uid, null, null, null, ['observable_type' => 'cryptocurrency-wallet', 'currency' => 'BTC']);

        $lazInfra = $this->node('infrastructure', 'Lazarus Bulletproof Hosting', 'Bulletproof hosting infrastructure spanning Russia and Belarus. Used for C2, payload staging, and data exfiltration.', 72, 'high', $uid);
        $lazInfra->tags()->syncWithoutDetaching([$tags['C2-Infra']->id]);

        $this->edges($uid, [
            [$lazarus, 'uses', $wannacry, 95],
            [$lazarus, 'uses', $dtrack, 88],
            [$lazarus, 'targets', $cryptoExchange, 90],
            [$lazarus, 'targets', $hospital, 75],
            [$lazarus, 'uses', $lazInfra, 80],
            [$opAppleJeus, 'attributed-to', $lazarus, 92],
            [$opAppleJeus, 'uses', $supplyChain, 95],
            [$opAppleJeus, 'targets', $cryptoExchange, 90],
            [$wannacry, 'exploits', $cve2017_0144, 99],
            [$wannacry, 'uses', $eternalBlue, 98],
            [$dtrack, 'communicates-with', $lazC2_1, 80],
            [$lazC2_1, 'related-to', $lazInfra, 70],
            [$lazDomain, 'related-to', $opAppleJeus, 85],
            [$lazHash, 'indicates', $dtrack, 88],
            [$btcWallet, 'related-to', $wannacry, 72],
        ]);

        // ╔══════════════════════════════════════╗
        // ║  SCENARIO 3: Sandworm / BlackEnergy  ║
        // ╚══════════════════════════════════════╝
        $this->command->info('🟣 Seeding Scenario 3: Sandworm / BlackEnergy...');

        $sandworm = $this->node('threat-actor', 'Sandworm (Voodoo Bear)', 'Russian GRU Unit 74455 cyber-warfare group. Responsible for devastating attacks on Ukrainian infrastructure, NotPetya, and Olympic Destroyer.', 97, 'critical', $uid, 'MITRE:G0034', '2011-01-01', '2025-12-10', ['aliases' => ['Voodoo Bear', 'IRIDIUM', 'Telebots', 'Unit 74455'], 'sophistication' => 'expert', 'goals' => ['Disruption', 'Espionage', 'Sabotage'], 'motivation' => 'state-sponsored', 'country' => 'RU']);
        $sandworm->tags()->syncWithoutDetaching([$tags['APT']->id, $tags['Critical']->id, $tags['State-Sponsored']->id, $tags['ICS-SCADA']->id]);

        $blackEnergy = $this->node('malware', 'BlackEnergy 3', 'Modular malware framework targeting ICS/SCADA systems. Used in 2015 Ukraine power grid attack. Contains KillDisk wiper component.', 90, 'critical', $uid, 'MITRE:S0089', '2014-06-01', '2025-03-20', ['malware_types' => ['RAT', 'Wiper', 'ICS-Malware'], 'target_systems' => ['SCADA', 'HMI', 'Windows']]);
        $blackEnergy->tags()->syncWithoutDetaching([$tags['Malware']->id, $tags['ICS-SCADA']->id]);

        $industroyer2 = $this->node('malware', 'Industroyer2', 'Second-generation ICS-targeted malware specifically designed to interact with industrial control protocols (IEC-104, IEC-61850, OPC DA).', 93, 'critical', $uid, 'MITRE:S0604', '2022-04-01', '2025-01-15', ['malware_types' => ['ICS-Malware', 'Wiper'], 'protocols' => ['IEC-104', 'IEC-61850', 'OPC DA']]);
        $industroyer2->tags()->syncWithoutDetaching([$tags['Malware']->id, $tags['ICS-SCADA']->id]);

        $notPetya = $this->node('malware', 'NotPetya', 'Destructive wiper malware disguised as ransomware. Spread via compromised M.E.Doc update. Caused $10B+ global damage. Uses EternalBlue + Mimikatz for propagation.', 99, 'critical', $uid, 'MITRE:S0368', '2017-06-27', '2017-07-15', ['malware_types' => ['Wiper', 'Pseudo-Ransomware'], 'damage_estimate' => '$10B+']);
        $notPetya->tags()->syncWithoutDetaching([$tags['Malware']->id, $tags['Critical']->id]);

        $ukrenergo = $this->node('identity', 'Ukrenergo (UA Power Grid)', 'Ukrainian national energy company operating the power grid. Attacked in Dec 2015 and Apr 2022 by Sandworm.', 95, 'critical', $uid);
        $ukrenergo->tags()->syncWithoutDetaching([$tags['ICS-SCADA']->id]);

        $opBlackout = $this->node('campaign', 'Operation Blackout 2.0', 'Coordinated campaign targeting Ukrainian critical infrastructure ahead of winter 2025. Combines Industroyer2 variants with social engineering of OT personnel.', 88, 'critical', $uid, null, '2025-09-01', '2025-12-31', ['objective' => 'Infrastructure Disruption', 'target_sectors' => ['Energy', 'Utilities']]);
        $opBlackout->tags()->syncWithoutDetaching([$tags['Threat-Intel']->id, $tags['ICS-SCADA']->id, $tags['Critical']->id]);

        $icsProto = $this->node('technique', 'Manipulation of Control (T0831)', 'Manipulate ICS protocols to send unauthorized commands to PLCs and RTUs.', 92, 'critical', $uid, 'T0831');
        $validAccounts = $this->node('technique', 'Valid Accounts (T1078)', 'Use compromised OT operator credentials to access SCADA systems.', 88, 'high', $uid, 'T1078');

        $swC2 = $this->node('observable', '91.235.116.80', 'Sandworm C2 IP for BlackEnergy modules. VPS in Ukraine border region.', 75, 'high', $uid, null, '2025-09-15', null, ['observable_type' => 'ipv4-addr', 'geo' => 'UA/RU border']);
        $swDomain = $this->node('observable', 'ua-energy-portal.net', 'Phishing domain impersonating Ukrainian energy portals. Used to harvest OT engineer credentials.', 80, 'high', $uid, null, '2025-08-20', null, ['observable_type' => 'domain-name']);

        $this->edges($uid, [
            [$sandworm, 'uses', $blackEnergy, 95],
            [$sandworm, 'uses', $industroyer2, 92],
            [$sandworm, 'uses', $notPetya, 99],
            [$sandworm, 'targets', $ukrenergo, 95],
            [$opBlackout, 'attributed-to', $sandworm, 90],
            [$opBlackout, 'targets', $ukrenergo, 95],
            [$opBlackout, 'uses', $icsProto, 88],
            [$opBlackout, 'uses', $validAccounts, 82],
            [$industroyer2, 'uses', $icsProto, 95],
            [$blackEnergy, 'communicates-with', $swC2, 78],
            [$swDomain, 'related-to', $opBlackout, 80],
            [$notPetya, 'exploits', $cve2017_0144, 95],
        ]);

        // ╔══════════════════════════════════════╗
        // ║  SCENARIO 4: FIN7 / Carbanak         ║
        // ╚══════════════════════════════════════╝
        $this->command->info('🟢 Seeding Scenario 4: FIN7 / Carbanak...');

        $fin7 = $this->node('threat-actor', 'FIN7 (Carbanak Group)', 'Financially motivated threat group targeting retail, restaurant, and hospitality sectors. Known for sophisticated POS malware and social engineering. Estimated $1B+ in theft.', 90, 'critical', $uid, 'MITRE:G0046', '2013-01-01', '2025-11-01', ['aliases' => ['Carbanak', 'Navigator Group', 'Anunak'], 'sophistication' => 'advanced', 'goals' => ['Financial Gain'], 'motivation' => 'criminal', 'estimated_theft' => '$1B+']);
        $fin7->tags()->syncWithoutDetaching([$tags['APT']->id, $tags['Financial']->id]);

        $carbanak = $this->node('malware', 'Carbanak Backdoor', 'Sophisticated backdoor targeting financial institutions. Capable of video recording of bank operator screens, SWIFT transaction manipulation, and ATM cash-out coordination.', 88, 'critical', $uid, 'MITRE:S0030', '2014-01-01', '2025-09-01', ['malware_types' => ['Backdoor', 'Banking Trojan'], 'capabilities' => ['Screen Recording', 'SWIFT Manipulation', 'ATM Jackpotting']]);
        $carbanak->tags()->syncWithoutDetaching([$tags['Malware']->id, $tags['Financial']->id]);

        $griffon = $this->node('malware', 'GRIFFON', 'JavaScript-based backdoor used by FIN7 for initial foothold. Delivered via malicious Office documents, communicates via DNS TXT records for C2.', 82, 'high', $uid, null, '2023-01-01', '2025-10-15', ['malware_types' => ['Backdoor', 'JavaScript RAT'], 'c2_method' => 'DNS TXT']);
        $griffon->tags()->syncWithoutDetaching([$tags['Malware']->id]);

        $posTerminal = $this->node('identity', 'RetailMax POS Network', 'Major US retail chain with 3,000+ POS terminals. Compromised by FIN7 resulting in 15M card details stolen.', 85, 'critical', $uid);
        $bank = $this->node('identity', 'Eastern European Bank Consortium', 'Group of 8 Eastern European banks targeted by Carbanak for SWIFT fraud totaling $300M.', 80, 'critical', $uid);

        $opBigScore = $this->node('campaign', 'Operation Big Score', 'Large-scale campaign targeting financial institutions via employee phishing. Deploys Carbanak for long-term persistence and SWIFT transaction manipulation.', 85, 'critical', $uid, null, '2024-01-15', '2025-10-30', ['objective' => 'Financial Theft', 'target_sectors' => ['Banking', 'Retail']]);
        $opBigScore->tags()->syncWithoutDetaching([$tags['Threat-Intel']->id, $tags['Financial']->id]);

        $bec = $this->node('technique', 'Business Email Compromise (T1534)', 'Use of fake LinkedIn profiles posing as vendor representatives to social engineer targets into opening malicious documents.', 85, 'high', $uid, 'T1534');
        $posScraping = $this->node('technique', 'Input Capture: Credential API Hooking (T1056.004)', 'Hook POS terminal memory to scrape credit card track data during transactions.', 90, 'critical', $uid, 'T1056.004');

        $fin7C2 = $this->node('observable', '104.168.44.130', 'FIN7 C2 server IP for GRIFFON DNS-based communication. Hosted on rented infrastructure.', 68, 'high', $uid, null, '2025-03-01', null, ['observable_type' => 'ipv4-addr', 'geo' => 'US']);
        $fin7Domain = $this->node('observable', 'vendor-invoices.biz', 'Domain used in BEC phishing campaigns. Hosts malicious document templates.', 76, 'high', $uid, null, '2025-02-15', null, ['observable_type' => 'domain-name']);
        $fin7Hash = $this->node('indicator', 'SHA256:cafe1234dead...beef', 'File hash for GRIFFON loader distributed via phishing. Macro-enabled .xlsm file.', 80, 'high', $uid, null, null, null, ['pattern_type' => 'stix']);

        $this->edges($uid, [
            [$fin7, 'uses', $carbanak, 90],
            [$fin7, 'uses', $griffon, 85],
            [$fin7, 'targets', $posTerminal, 88],
            [$fin7, 'targets', $bank, 82],
            [$opBigScore, 'attributed-to', $fin7, 88],
            [$opBigScore, 'uses', $bec, 85],
            [$opBigScore, 'targets', $bank, 80],
            [$carbanak, 'uses', $posScraping, 90],
            [$griffon, 'communicates-with', $fin7C2, 78],
            [$fin7Domain, 'related-to', $opBigScore, 76],
            [$fin7Hash, 'indicates', $griffon, 82],
        ]);

        // ╔══════════════════════════════════════╗
        // ║  SCENARIO 5: OceanLotus / APT32      ║
        // ╚══════════════════════════════════════╝
        $this->command->info('🔵 Seeding Scenario 5: OceanLotus / APT32...');

        $apt32 = $this->node('threat-actor', 'OceanLotus (APT32)', 'Vietnamese state-sponsored threat group targeting foreign governments, media, and dissidents. Known for macOS malware and sophisticated watering hole attacks.', 88, 'high', $uid, 'MITRE:G0050', '2014-01-01', '2025-11-20', ['aliases' => ['APT32', 'SeaLotus', 'Canvas Cyclone'], 'sophistication' => 'advanced', 'goals' => ['Espionage', 'Political Intelligence'], 'motivation' => 'state-sponsored', 'country' => 'VN']);
        $apt32->tags()->syncWithoutDetaching([$tags['APT']->id, $tags['State-Sponsored']->id, $tags['Espionage']->id]);

        $kerrdown = $this->node('malware', 'KERRDOWN', 'macOS-targeting backdoor attributed to OceanLotus. Arrives via trojanized applications. Persists via LaunchAgent. Collects system info, screenshots, keystrokes.', 80, 'high', $uid, null, '2020-01-01', '2025-10-01', ['malware_types' => ['Backdoor', 'macOS Malware'], 'platforms' => ['macOS']]);
        $kerrdown->tags()->syncWithoutDetaching([$tags['Malware']->id]);

        $ratankba = $this->node('malware', 'RATANKBA', 'Multi-platform RAT used by OceanLotus. Supports Windows and Linux. Communicates via HTTPS with custom protocol. Modular plugin architecture.', 78, 'high', $uid, null, '2018-01-01', '2025-08-15', ['malware_types' => ['RAT'], 'platforms' => ['Windows', 'Linux']]);
        $ratankba->tags()->syncWithoutDetaching([$tags['Malware']->id]);

        $mediaOrg = $this->node('identity', 'ASEAN Press Alliance', 'Regional media consortium covering politics and human rights in Southeast Asia. Primary target of APT32.', 78, 'high', $uid);
        $ngo = $this->node('identity', 'Human Rights Watch (SEA Office)', 'Southeast Asia regional office of Human Rights Watch. Targeted for intelligence on dissidents.', 82, 'high', $uid);

        $opSeaFog = $this->node('campaign', 'Operation Sea Fog', 'Espionage campaign combining watering hole attacks on media sites with targeted spear-phishing of journalists and activists.', 80, 'high', $uid, null, '2025-01-15', '2025-12-01', ['objective' => 'Political Espionage', 'target_sectors' => ['Media', 'NGO', 'Government']]);
        $opSeaFog->tags()->syncWithoutDetaching([$tags['Threat-Intel']->id, $tags['Espionage']->id]);

        $wateringHole = $this->node('technique', 'Drive-by Compromise (T1189)', 'Inject malicious JavaScript into legitimate news websites to selectively compromise visiting targets.', 85, 'high', $uid, 'T1189');
        $macPersist = $this->node('technique', 'Launch Agent (T1543.001)', 'Persist on macOS via LaunchAgent plist file in ~/Library/LaunchAgents.', 82, 'high', $uid, 'T1543.001');

        $apt32C2 = $this->node('observable', '45.77.39.101', 'OceanLotus C2 infrastructure. Cloud VPS in Singapore.', 72, 'high', $uid, null, '2025-02-01', null, ['observable_type' => 'ipv4-addr', 'geo' => 'SG']);
        $apt32Domain = $this->node('observable', 'asean-news-update.com', 'Watering hole domain compromised by APT32. Legitimate news aggregator with injected exploit kit.', 80, 'high', $uid, null, '2025-01-20', null, ['observable_type' => 'domain-name']);

        $this->edges($uid, [
            [$apt32, 'uses', $kerrdown, 82],
            [$apt32, 'uses', $ratankba, 78],
            [$apt32, 'targets', $mediaOrg, 80],
            [$apt32, 'targets', $ngo, 85],
            [$opSeaFog, 'attributed-to', $apt32, 82],
            [$opSeaFog, 'uses', $wateringHole, 88],
            [$opSeaFog, 'targets', $mediaOrg, 82],
            [$kerrdown, 'uses', $macPersist, 85],
            [$kerrdown, 'communicates-with', $apt32C2, 75],
            [$ratankba, 'communicates-with', $apt32C2, 72],
            [$apt32Domain, 'related-to', $opSeaFog, 80],
        ]);

        // ╔══════════════════════════════════════╗
        // ║            INTRUSION SETS            ║
        // ╚══════════════════════════════════════╝
        $this->command->info('🔶 Seeding Intrusion Sets...');

        $is1 = $this->node('intrusion-set', 'Shadow Nexus Cluster', 'Cluster of activity linked to APT-X operations across ASEAN. Characterized by FooRAT/ShadowLoader usage and FortiOS exploitation.', 78, 'high', $uid, null, '2023-06-01', '2025-12-01');
        $is2 = $this->node('intrusion-set', 'HIDDEN COBRA Cluster', 'Activity cluster associated with Lazarus Group crypto operations. Identified by common C2 patterns and AppleJeus tooling.', 85, 'critical', $uid, null, '2020-01-01', '2025-11-30');
        $is3 = $this->node('intrusion-set', 'Voodoo Bear OT Cluster', 'ICS-focused activity cluster linked to Sandworm destructive operations against Ukrainian infrastructure.', 90, 'critical', $uid, null, '2015-12-01', '2025-12-10');

        $this->edges($uid, [
            [$is1, 'attributed-to', $aptX, 80],
            [$is2, 'attributed-to', $lazarus, 88],
            [$is3, 'attributed-to', $sandworm, 92],
            [$is1, 'uses', $fooRAT, 82],
            [$is2, 'uses', $dtrack, 78],
            [$is3, 'uses', $industroyer2, 90],
        ]);

        // ╔══════════════════════════════════════╗
        // ║     CROSS-SCENARIO RELATIONSHIPS     ║
        // ╚══════════════════════════════════════╝
        $this->command->info('🔗 Seeding cross-scenario relationships...');

        $this->edges($uid, [
            // Shared vulnerability exploitation
            [$lazarus, 'exploits', $cve2017_0144, 95],
            // Sandworm also uses credential dumping
            [$sandworm, 'uses', $credDump, 80],
            // FIN7 also uses valid accounts technique
            [$fin7, 'uses', $validAccounts, 75],
            // Lazarus infrastructure overlap with APT-X in some hosting
            [$lazInfra, 'related-to', $c2infra, 40],
        ]);

        // ╔══════════════════════════════════════╗
        // ║      CASES + TASKS (5 cases)         ║
        // ╚══════════════════════════════════════╝
        $this->command->info('📋 Seeding Cases & Tasks...');

        // Case 1: APT-X Intrusion
        $case1 = $this->createCase('INC-2025-001: APT-X Intrusion via FortiOS Exploit', 'incident', 'critical', 'in-progress', 'Active investigation: APT-X exploited CVE-2024-21762 on perimeter FortiGate. FooRAT detected on 4 endpoints. ShadowLoader found in email attachment. Lateral movement via PsExec confirmed.', $uid, 7);
        $this->createTasks($case1, $uid, [
            ['Isolate 4 compromised endpoints from network', 'done'],
            ['Collect forensic disk images from endpoints', 'done'],
            ['Block C2 IPs (198.51.100.42, 203.0.113.77) in firewall', 'done'],
            ['Analyze FooRAT C2 communication protocol', 'in-progress'],
            ['Extract IOCs from ShadowLoader sample', 'in-progress'],
            ['Run YARA rule sweep across all endpoints', 'in-progress'],
            ['Check for credential compromise (Mimikatz artifacts)', 'pending'],
            ['Sweep network for lateral movement indicators', 'pending'],
            ['Assess data exfiltration scope', 'pending'],
            ['Prepare incident report for CSIRT', 'pending'],
        ]);

        // Case 2: Lazarus Crypto Theft
        $case2 = $this->createCase('INC-2025-002: Lazarus Crypto Exchange Breach', 'incident', 'critical', 'in-progress', 'CryptoVault Exchange reporting unauthorized withdrawals totaling 250 BTC ($15M). Trojanized trading app (AppleJeus variant) identified on operator workstations. DTrack backdoor found establishing persistence.', $uid, 5);
        $this->createTasks($case2, $uid, [
            ['Freeze affected exchange wallets', 'done'],
            ['Identify compromised operator accounts', 'done'],
            ['Reverse engineer trojanized trading app', 'in-progress'],
            ['Trace stolen crypto through mixers', 'in-progress'],
            ['Analyze DTrack C2 traffic for exfil data', 'in-progress'],
            ['Coordinate with blockchain analytics firm', 'pending'],
            ['Notify affected customers', 'pending'],
            ['File law enforcement report', 'pending'],
        ]);

        // Case 3: Sandworm ICS Attack
        $case3 = $this->createCase('INC-2025-003: Sandworm ICS Attack on Power Grid', 'incident', 'critical', 'open', 'CERT-UA alert: Industroyer2 variant detected in Ukrenergo SCADA network. Attempts to manipulate IEC-104 commands to trip circuit breakers. OT operator credentials compromised via phishing.', $uid, 3);
        $this->createTasks($case3, $uid, [
            ['Isolate affected SCADA segments', 'in-progress'],
            ['Reset all OT operator credentials', 'in-progress'],
            ['Deploy IEC-104 protocol monitoring', 'pending'],
            ['Analyze Industroyer2 variant for new capabilities', 'pending'],
            ['Coordinate with CERT-UA', 'pending'],
            ['Implement network segmentation between IT/OT', 'pending'],
        ]);

        // Case 4: FIN7 POS Compromise
        $case4 = $this->createCase('INC-2025-004: FIN7 POS Terminal Compromise', 'incident', 'high', 'in-progress', 'RetailMax reporting unusual POS terminal behavior. GRIFFON backdoor detected on 12 POS terminals across 3 store locations. Carbanak backdoor found on corporate payment processing server.', $uid, 14);
        $this->createTasks($case4, $uid, [
            ['Isolate affected POS terminals', 'done'],
            ['Engage payment forensics team (PCI-DSS)', 'done'],
            ['Determine scope of card data compromise', 'in-progress'],
            ['Analyze GRIFFON C2 DNS traffic', 'in-progress'],
            ['Remove Carbanak from payment server', 'pending'],
            ['Re-image affected POS terminals', 'pending'],
            ['Notify payment card brands', 'pending'],
            ['Prepare breach notification', 'pending'],
        ]);

        // Case 5: APT32 Media Investigation
        $case5 = $this->createCase('INV-2025-001: OceanLotus Media Targeting Investigation', 'investigation', 'high', 'open', 'Proactive investigation into APT32 watering hole campaign targeting ASEAN journalists. Compromised news site (asean-news-update.com) serving selective exploits. KERRDOWN macOS backdoor found on 2 journalist devices.', $uid, 21);
        $this->createTasks($case5, $uid, [
            ['Scan journalist devices for KERRDOWN indicators', 'done'],
            ['Take down compromised watering hole site', 'in-progress'],
            ['Analyze injected JavaScript exploit kit', 'in-progress'],
            ['Identify all targeted visitors via web logs', 'pending'],
            ['Coordinate with hosting provider for takedown', 'pending'],
            ['Brief media organizations on threat', 'pending'],
        ]);

        // Link entities to cases
        CaseItem::firstOrCreate(['case_id' => $case1->id, 'itemable_type' => Node::class, 'itemable_id' => $aptX->id]);
        CaseItem::firstOrCreate(['case_id' => $case1->id, 'itemable_type' => Node::class, 'itemable_id' => $fooRAT->id]);
        CaseItem::firstOrCreate(['case_id' => $case1->id, 'itemable_type' => Node::class, 'itemable_id' => $cve2024_21762->id]);
        CaseItem::firstOrCreate(['case_id' => $case2->id, 'itemable_type' => Node::class, 'itemable_id' => $lazarus->id]);
        CaseItem::firstOrCreate(['case_id' => $case2->id, 'itemable_type' => Node::class, 'itemable_id' => $dtrack->id]);
        CaseItem::firstOrCreate(['case_id' => $case3->id, 'itemable_type' => Node::class, 'itemable_id' => $sandworm->id]);
        CaseItem::firstOrCreate(['case_id' => $case3->id, 'itemable_type' => Node::class, 'itemable_id' => $industroyer2->id]);
        CaseItem::firstOrCreate(['case_id' => $case4->id, 'itemable_type' => Node::class, 'itemable_id' => $fin7->id]);
        CaseItem::firstOrCreate(['case_id' => $case4->id, 'itemable_type' => Node::class, 'itemable_id' => $carbanak->id]);
        CaseItem::firstOrCreate(['case_id' => $case5->id, 'itemable_type' => Node::class, 'itemable_id' => $apt32->id]);
        CaseItem::firstOrCreate(['case_id' => $case5->id, 'itemable_type' => Node::class, 'itemable_id' => $kerrdown->id]);

        // ╔══════════════════════════════════════╗
        // ║          ACTIVITY LOGS               ║
        // ╚══════════════════════════════════════╝
        $this->command->info('📝 Seeding Activity Logs...');

        $activities = [
            ['create', 'Node', $aptX->id, 'Created threat actor: APT-X (Shadow Panda)', 45],
            ['create', 'Node', $fooRAT->id, 'Created malware: FooRAT v2.1', 44],
            ['create', 'Edge', null, 'Linked APT-X → uses → FooRAT v2.1 (confidence: 92%)', 43],
            ['create', 'Node', $cve2024_21762->id, 'Created vulnerability: CVE-2024-21762 (CVSS 9.8)', 42],
            ['update', 'Node', $aptX->id, 'Updated APT-X confidence to 85%, added aliases', 40],
            ['create', 'CaseModel', $case1->id, 'Created incident: INC-2025-001 (Critical)', 38],
            ['create', 'Node', $lazarus->id, 'Created threat actor: Lazarus Group', 35],
            ['create', 'Node', $wannacry->id, 'Created malware: WannaCry 2.0', 34],
            ['create', 'CaseModel', $case2->id, 'Created incident: INC-2025-002 (Critical)', 32],
            ['create', 'Node', $sandworm->id, 'Created threat actor: Sandworm (Voodoo Bear)', 30],
            ['create', 'Node', $industroyer2->id, 'Created malware: Industroyer2', 28],
            ['create', 'Node', $fin7->id, 'Created threat actor: FIN7 (Carbanak Group)', 25],
            ['update', 'CaseModel', $case1->id, 'Updated INC-2025-001 status to in-progress', 22],
            ['create', 'Integration', null, 'Configured MITRE ATT&CK Importer connector', 20],
            ['create', 'Node', $apt32->id, 'Created threat actor: OceanLotus (APT32)', 18],
            ['create', 'CaseModel', $case3->id, 'Created incident: INC-2025-003 (Critical)', 15],
            ['update', 'Node', $lazarus->id, 'Updated Lazarus Group with new crypto wallet IOC', 12],
            ['create', 'Edge', null, 'Linked Sandworm → uses → Industroyer2 (confidence: 92%)', 10],
            ['create', 'CaseModel', $case4->id, 'Created incident: INC-2025-004 (High)', 8],
            ['create', 'CaseModel', $case5->id, 'Created investigation: INV-2025-001 (High)', 5],
            ['update', 'CaseModel', $case2->id, 'Added 250 BTC theft to INC-2025-002 description', 3],
            ['update', 'CaseModel', $case4->id, 'Marked POS isolation task as done', 2],
            ['create', 'Edge', null, 'Linked Lazarus → exploits → CVE-2017-0144 (cross-reference)', 1],
        ];

        foreach ($activities as $a) {
            ActivityLog::create([
                'user_id'     => $uid,
                'action'      => $a[0],
                'entity_type' => $a[1],
                'entity_id'   => $a[2],
                'description' => $a[3],
                'created_at'  => now()->subMinutes($a[4]),
                'updated_at'  => now()->subMinutes($a[4]),
            ]);
        }

        // ╔══════════════════════════════════════╗
        // ║       INTEGRATIONS / CONNECTORS      ║
        // ╚══════════════════════════════════════╝
        $this->command->info('🔌 Seeding Integrations...');

        Integration::firstOrCreate(
            ['name' => 'MITRE ATT&CK Importer'],
            [
                'type'         => 'connector',
                'command'      => 'ingest:mitre-attack',
                'status'       => 'success',
                'last_run_at'  => now()->subHours(6),
                'last_message' => 'Imported 15 techniques, 8 groups',
                'config'       => ['version' => '15.1', 'source' => 'https://raw.githubusercontent.com/mitre-attack/attack-stix-data/master/enterprise-attack/enterprise-attack.json'],
            ]
        );

        Integration::firstOrCreate(
            ['name' => 'NVD CVE Feed'],
            [
                'type'         => 'connector',
                'schedule'     => '0 */6 * * *',
                'status'       => 'success',
                'last_run_at'  => now()->subHours(2),
                'last_message' => 'Fetched 12 new CVEs, 3 critical',
                'config'       => ['job_class' => \App\Jobs\Connectors\CveConnector::class, 'limit' => 20],
            ]
        );

        Integration::firstOrCreate(
            ['name' => 'AlienVault OTX'],
            [
                'type'         => 'connector',
                'schedule'     => '0 */12 * * *',
                'status'       => 'success',
                'last_run_at'  => now()->subHours(8),
                'last_message' => 'Synced 45 pulses, 200+ indicators',
                'config'       => ['job_class' => \App\Jobs\Connectors\OtxConnector::class, 'api_key' => 'demo-key-xxx', 'limit' => 50],
            ]
        );

        Integration::firstOrCreate(
            ['name' => 'ML Anomaly Feed'],
            [
                'type'         => 'feed',
                'schedule'     => '*/5 * * * *',
                'status'       => 'running',
                'last_run_at'  => now()->subMinutes(3),
                'last_message' => 'Processed 150 logs, 8 anomalies detected',
                'config'       => ['endpoint' => 'http://localhost:5000/predict'],
            ]
        );

        Integration::firstOrCreate(
            ['name' => 'MISP Threat Sharing'],
            [
                'type'         => 'connector',
                'schedule'     => '0 */4 * * *',
                'status'       => 'idle',
                'config'       => ['endpoint' => 'https://misp.example.org', 'api_key' => '', 'verify_ssl' => true],
            ]
        );

        Integration::firstOrCreate(
            ['name' => 'VirusTotal Enrichment'],
            [
                'type'         => 'feed',
                'schedule'     => '*/15 * * * *',
                'status'       => 'idle',
                'config'       => ['api_key' => '', 'rate_limit' => 4],
            ]
        );

        // ╔══════════════════════════════════════╗
        // ║             SUMMARY                  ║
        // ╚══════════════════════════════════════╝
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('  ✅ CTI Demo Data Seeded Successfully');
        $this->command->info('═══════════════════════════════════════');
        $this->command->info("  Nodes (entities):    " . Node::count());
        $this->command->info("  Edges (relations):   " . Edge::count());
        $this->command->info("  Cases:               " . CaseModel::count());
        $this->command->info("  Case Tasks:          " . CaseTask::count());
        $this->command->info("  Case Items:          " . CaseItem::count());
        $this->command->info("  Tags:                " . Tag::count());
        $this->command->info("  Activity Logs:       " . ActivityLog::count());
        $this->command->info("  Integrations:        " . Integration::count());
        $this->command->info('═══════════════════════════════════════');

        // Breakdown by node type
        $types = Node::selectRaw('type, count(*) as cnt')->groupBy('type')->orderByDesc('cnt')->pluck('cnt', 'type');
        foreach ($types as $type => $count) {
            $this->command->info("    {$type}: {$count}");
        }
    }

    // ───── Helper: create node ─────
    private function node(string $type, string $name, string $desc, int $conf, string $sev, ?string $uid, ?string $sourceRef = null, ?string $firstSeen = null, ?string $lastSeen = null, ?array $raw = null): Node
    {
        return Node::firstOrCreate(
            ['name' => $name, 'type' => $type],
            array_filter([
                'description' => $desc,
                'confidence'  => $conf,
                'severity'    => $sev,
                'source_ref'  => $sourceRef,
                'first_seen'  => $firstSeen,
                'last_seen'   => $lastSeen,
                'raw'         => $raw,
                'created_by'  => $uid,
            ], fn ($v) => !is_null($v))
        );
    }

    // ───── Helper: create edges in bulk ─────
    private function edges(?string $uid, array $edgeList): void
    {
        foreach ($edgeList as [$from, $type, $to, $conf]) {
            Edge::firstOrCreate(
                ['from_node_id' => $from->id, 'to_node_id' => $to->id, 'type' => $type],
                ['confidence' => $conf, 'created_by' => $uid]
            );
        }
    }

    // ───── Helper: create case ─────
    private function createCase(string $title, string $type, string $severity, string $status, string $desc, ?string $uid, int $dueDays): CaseModel
    {
        return CaseModel::firstOrCreate(
            ['title' => $title],
            [
                'type'        => $type,
                'severity'    => $severity,
                'status'      => $status,
                'description' => $desc,
                'owner_id'    => $uid,
                'due_date'    => now()->addDays($dueDays),
            ]
        );
    }

    // ───── Helper: create tasks for case ─────
    private function createTasks(CaseModel $case, ?string $uid, array $tasks): void
    {
        foreach ($tasks as [$title, $status]) {
            CaseTask::firstOrCreate(
                ['case_id' => $case->id, 'title' => $title],
                ['status' => $status, 'assignee_id' => $uid]
            );
        }
    }
}
