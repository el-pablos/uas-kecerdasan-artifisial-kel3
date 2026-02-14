<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\Edge;
use Illuminate\Http\Request;

class ThreatsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Generic index for a threat subtype.
     */
    private function indexForType(string $type, Request $request)
    {
        $query = Node::where('type', $type);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        $nodes = $query->orderBy('updated_at', 'desc')->paginate(25)->withQueryString();

        return view('cti.threats.index', [
            'nodes' => $nodes,
            'type' => $type,
            'typeLabel' => self::typeLabel($type),
        ]);
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'threat-actor' => 'Threat Actors',
            'malware' => 'Malware',
            'campaign' => 'Campaigns',
            'intrusion-set' => 'Intrusion Sets',
            'vulnerability' => 'Vulnerabilities',
            default => ucfirst(str_replace('-', ' ', $type)),
        };
    }

    // ===== Threat Actors =====
    public function actorsIndex(Request $request) { return $this->indexForType('threat-actor', $request); }
    public function actorCreate() { return view('cti.threats.create', ['type' => 'threat-actor', 'typeLabel' => 'Threat Actor']); }
    public function actorStore(Request $request) { return $this->storeForType('threat-actor', $request); }

    // ===== Malware =====
    public function malwareIndex(Request $request) { return $this->indexForType('malware', $request); }
    public function malwareCreate() { return view('cti.threats.create', ['type' => 'malware', 'typeLabel' => 'Malware']); }
    public function malwareStore(Request $request) { return $this->storeForType('malware', $request); }

    // ===== Campaigns =====
    public function campaignsIndex(Request $request) { return $this->indexForType('campaign', $request); }
    public function campaignCreate() { return view('cti.threats.create', ['type' => 'campaign', 'typeLabel' => 'Campaign']); }
    public function campaignStore(Request $request) { return $this->storeForType('campaign', $request); }

    // ===== Intrusion Sets =====
    public function intrusionSetsIndex(Request $request) { return $this->indexForType('intrusion-set', $request); }
    public function intrusionSetCreate() { return view('cti.threats.create', ['type' => 'intrusion-set', 'typeLabel' => 'Intrusion Set']); }
    public function intrusionSetStore(Request $request) { return $this->storeForType('intrusion-set', $request); }

    // ===== Vulnerabilities =====
    public function vulnerabilitiesIndex(Request $request) { return $this->indexForType('vulnerability', $request); }
    public function vulnerabilityCreate() { return view('cti.threats.create', ['type' => 'vulnerability', 'typeLabel' => 'Vulnerability']); }
    public function vulnerabilityStore(Request $request) { return $this->storeForType('vulnerability', $request); }

    /**
     * Generic store for a typed node.
     */
    private function storeForType(string $type, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'confidence' => 'nullable|integer|min:0|max:100',
            'severity' => 'nullable|string|in:critical,high,medium,low,unknown',
            'first_seen' => 'nullable|date',
            'last_seen' => 'nullable|date',
            'source_ref' => 'nullable|string|max:255',
            'aliases' => 'nullable|string',
            'goals' => 'nullable|string',
            'sophistication' => 'nullable|string|in:none,minimal,intermediate,advanced,expert,innovator,strategic',
            'external_references' => 'nullable|string',
        ]);

        $raw = [];
        foreach (['aliases', 'goals', 'sophistication', 'external_references'] as $field) {
            if (isset($validated[$field])) {
                $raw[$field] = $validated[$field];
                unset($validated[$field]);
            }
        }

        $node = Node::create(array_merge($validated, [
            'type' => $type,
            'raw' => $raw ?: null,
            'created_by' => auth()->id(),
        ]));

        activity_log('create', 'node', $node->id, "Created {$type}: {$node->name}");

        return redirect()->route('knowledge.entities.show', $node)
            ->with('success', "{$node->name} created.");
    }

    /**
     * Show detail of any threat-type node (via entity detail).
     */
    public function show(Node $node)
    {
        return redirect()->route('knowledge.entities.show', $node);
    }

    /**
     * Quick-link: create relationship from threat index modal.
     */
    public function quickLink(Request $request)
    {
        $validated = $request->validate([
            'from_node_id' => 'required|uuid|exists:nodes,id',
            'to_node_id'   => 'required|uuid|exists:nodes,id|different:from_node_id',
            'type'         => 'required|string|max:80',
            'confidence'   => 'nullable|integer|min:0|max:100',
            'description'  => 'nullable|string|max:500',
        ]);

        $edge = Edge::create([
            'from_node_id' => $validated['from_node_id'],
            'to_node_id'   => $validated['to_node_id'],
            'type'         => $validated['type'],
            'confidence'   => $validated['confidence'] ?? 50,
            'description'  => $validated['description'] ?? null,
            'created_by'   => auth()->id(),
        ]);

        activity_log('create', 'edge', $edge->id, "Quick-linked {$validated['type']}");

        return back()->with('success', 'Relationship created.');
    }

    /**
     * Add a note/annotation to a node (stored in raw.notes array).
     */
    public function addNote(Request $request, Node $node)
    {
        $validated = $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        $raw = $node->raw ?? [];
        $raw['notes'] = $raw['notes'] ?? [];
        $raw['notes'][] = [
            'text'       => $validated['note'],
            'author'     => auth()->user()->name,
            'author_id'  => auth()->id(),
            'created_at' => now()->toISOString(),
        ];
        $node->update(['raw' => $raw]);

        activity_log('update', 'node', $node->id, "Added note to {$node->name}");

        return back()->with('success', 'Note added.');
    }

    /**
     * API: Threat summary stats for dashboard widget.
     */
    public function apiStats()
    {
        $threatTypes = ['threat-actor', 'malware', 'campaign', 'intrusion-set', 'vulnerability'];

        $counts = Node::whereIn('type', $threatTypes)
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        $recentThreats = Node::whereIn('type', $threatTypes)
            ->orderByDesc('created_at')
            ->take(5)
            ->get(['id', 'name', 'type', 'severity', 'confidence', 'created_at']);

        $severityDist = Node::whereIn('type', $threatTypes)
            ->whereNotNull('severity')
            ->selectRaw('severity, count(*) as total')
            ->groupBy('severity')
            ->pluck('total', 'severity')
            ->toArray();

        return response()->json([
            'counts' => $counts,
            'total'  => array_sum($counts),
            'recent' => $recentThreats,
            'severity_distribution' => $severityDist,
        ]);
    }
}
