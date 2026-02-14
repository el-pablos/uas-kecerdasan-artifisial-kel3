<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Models\Node;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

class IngestionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function connectors()
    {
        $integrations = Integration::orderBy('updated_at', 'desc')->get();
        return view('cti.ingestion.connectors', compact('integrations'));
    }

    public function import()
    {
        return view('cti.ingestion.import');
    }

    public function importStixBundle(Request $request)
    {
        $request->validate([
            'stix_file' => 'required|file|mimes:json,txt|max:10240',
        ]);

        $content = json_decode(file_get_contents($request->file('stix_file')->getRealPath()), true);

        if (!$content || !isset($content['type']) || $content['type'] !== 'bundle') {
            return back()->withErrors(['stix_file' => 'Invalid STIX 2.1 bundle format.']);
        }

        $imported = ['nodes' => 0, 'edges' => 0];

        foreach ($content['objects'] ?? [] as $obj) {
            $stixType = $obj['type'] ?? 'unknown';

            if ($stixType === 'relationship') {
                // Try to find source/target nodes
                $from = Node::where('source_ref', $obj['source_ref'] ?? '')->first();
                $to = Node::where('source_ref', $obj['target_ref'] ?? '')->first();

                if ($from && $to) {
                    \App\Models\Edge::firstOrCreate([
                        'from_node_id' => $from->id,
                        'to_node_id' => $to->id,
                        'type' => $obj['relationship_type'] ?? 'related-to',
                    ], [
                        'confidence' => $obj['confidence'] ?? null,
                        'raw' => $obj,
                        'created_by' => auth()->id(),
                    ]);
                    $imported['edges']++;
                }
            } else {
                // Map STIX type → our node type
                $nodeType = match ($stixType) {
                    'threat-actor' => 'threat-actor',
                    'malware' => 'malware',
                    'campaign' => 'campaign',
                    'intrusion-set' => 'intrusion-set',
                    'vulnerability' => 'vulnerability',
                    'indicator' => 'indicator',
                    'attack-pattern' => 'technique',
                    'tool' => 'tool',
                    'identity' => 'identity',
                    'ipv4-addr', 'ipv6-addr', 'domain-name', 'url', 'file' => 'observable',
                    default => 'unknown',
                };

                if ($nodeType !== 'unknown') {
                    Node::firstOrCreate(
                        ['source_ref' => $obj['id'] ?? ''],
                        [
                            'type' => $nodeType,
                            'name' => $obj['name'] ?? $obj['value'] ?? $obj['id'] ?? 'Unnamed',
                            'description' => $obj['description'] ?? null,
                            'confidence' => $obj['confidence'] ?? null,
                            'first_seen' => $obj['first_seen'] ?? $obj['created'] ?? null,
                            'last_seen' => $obj['last_seen'] ?? $obj['modified'] ?? null,
                            'raw' => $obj,
                            'created_by' => auth()->id(),
                        ]
                    );
                    $imported['nodes']++;
                }
            }
        }

        activity_log('import', 'stix-bundle', null, "Imported STIX bundle: {$imported['nodes']} nodes, {$imported['edges']} edges");

        return back()->with('success', "Import complete: {$imported['nodes']} nodes, {$imported['edges']} relationships.");
    }

    public function runConnector(Request $request, Integration $integration)
    {
        $integration->update([
            'status' => 'running',
            'last_run_at' => now(),
        ]);

        try {
            if ($integration->command) {
                Artisan::call($integration->command);
                $integration->update([
                    'status' => 'success',
                    'last_message' => Artisan::output(),
                ]);
            }
        } catch (\Exception $e) {
            $integration->update([
                'status' => 'error',
                'last_message' => $e->getMessage(),
            ]);
        }

        return back()->with('success', "Connector '{$integration->name}' executed.");
    }
}
