<?php

namespace App\Jobs\Connectors;

use App\Jobs\ConnectorJob;
use App\Models\Node;
use App\Models\Edge;
use Illuminate\Support\Facades\Http;

/**
 * Demo connector: fetch latest CVEs from NIST NVD API.
 *
 * Mengambil CVE terbaru dan menyimpannya sebagai Node (vulnerability).
 * API: https://services.nvd.nist.gov/rest/json/cves/2.0?resultsPerPage=20
 */
class CveConnector extends ConnectorJob
{
    protected function run(): array
    {
        $config = $this->integration->config ?? [];
        $limit = $config['limit'] ?? 20;

        $url = "https://services.nvd.nist.gov/rest/json/cves/2.0?resultsPerPage={$limit}";

        // If API key is configured, use it
        $headers = [];
        if (!empty($config['api_key'])) {
            $headers['apiKey'] = $config['api_key'];
        }

        $response = Http::timeout(60)->withHeaders($headers)->get($url);

        if (!$response->successful()) {
            throw new \RuntimeException("NVD API returned HTTP {$response->status()}");
        }

        $data = $response->json();
        $vulnerabilities = $data['vulnerabilities'] ?? [];
        $created = 0;
        $skipped = 0;

        foreach ($vulnerabilities as $item) {
            $cve = $item['cve'] ?? [];
            $cveId = $cve['id'] ?? null;
            if (!$cveId) continue;

            // Check if already exists
            if (Node::where('stix_id', "cve-{$cveId}")->exists()) {
                $skipped++;
                continue;
            }

            // Extract severity from CVSS
            $severity = 'low';
            $metrics = $cve['metrics'] ?? [];
            $cvss31 = $metrics['cvssMetricV31'][0]['cvssData'] ?? null;
            $cvss2 = $metrics['cvssMetricV2'][0]['cvssData'] ?? null;
            $cvssScore = $cvss31['baseScore'] ?? $cvss2['baseScore'] ?? 0;

            if ($cvssScore >= 9.0) $severity = 'critical';
            elseif ($cvssScore >= 7.0) $severity = 'high';
            elseif ($cvssScore >= 4.0) $severity = 'medium';

            $description = '';
            foreach ($cve['descriptions'] ?? [] as $desc) {
                if ($desc['lang'] === 'en') {
                    $description = $desc['value'];
                    break;
                }
            }

            Node::create([
                'type' => 'vulnerability',
                'name' => $cveId,
                'description' => $description ?: null,
                'severity' => $severity,
                'confidence' => 90,
                'stix_id' => "cve-{$cveId}",
                'raw' => [
                    'cvss_score' => $cvssScore,
                    'cvss_vector' => $cvss31['vectorString'] ?? $cvss2['vectorString'] ?? null,
                    'published'   => $cve['published'] ?? null,
                    'lastModified' => $cve['lastModified'] ?? null,
                    'references' => collect($cve['references'] ?? [])->pluck('url')->take(5)->toArray(),
                    'weaknesses' => collect($cve['weaknesses'] ?? [])->flatMap(fn($w) => collect($w['description'] ?? [])->pluck('value'))->toArray(),
                ],
            ]);
            $created++;
        }

        return [
            'nodes_created' => $created,
            'edges_created' => 0,
            'message' => "Imported {$created} CVEs, skipped {$skipped} duplicates.",
        ];
    }
}
