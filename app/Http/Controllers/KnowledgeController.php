<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\Edge;
use Illuminate\Http\Request;

class KnowledgeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ==========================================
    // ENTITIES (NODES)
    // ==========================================

    public function entitiesIndex(Request $request)
    {
        $query = Node::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }
        if ($request->filled('confidence_min')) {
            $query->where('confidence', '>=', $request->confidence_min);
        }
        if ($request->filled('date_from')) {
            $query->where('first_seen', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('last_seen', '<=', $request->date_to);
        }

        $sort = $request->get('sort', 'updated_at');
        $dir = $request->get('dir', 'desc');
        $nodes = $query->orderBy($sort, $dir)->paginate(25)->withQueryString();

        $typeCounts = Node::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        return view('cti.knowledge.entities.index', compact('nodes', 'typeCounts'));
    }

    public function entityCreate()
    {
        return view('cti.knowledge.entities.create');
    }

    public function entityStore(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'confidence' => 'nullable|integer|min:0|max:100',
            'severity' => 'nullable|string|in:critical,high,medium,low,unknown',
            'first_seen' => 'nullable|date',
            'last_seen' => 'nullable|date',
            'source_ref' => 'nullable|string|max:255',
            'raw' => 'nullable|json',
        ]);

        $validated['created_by'] = auth()->id();
        $node = Node::create($validated);

        activity_log('create', 'node', $node->id, "Created entity: {$node->name}");

        return redirect()->route('knowledge.entities.show', $node)
            ->with('success', "Entity '{$node->name}' created.");
    }

    public function entityShow(Node $node)
    {
        $node->load(['outgoingEdges.toNode', 'incomingEdges.fromNode', 'tags']);
        return view('cti.knowledge.entities.show', compact('node'));
    }

    public function entityEdit(Node $node)
    {
        return view('cti.knowledge.entities.edit', compact('node'));
    }

    public function entityUpdate(Request $request, Node $node)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'confidence' => 'nullable|integer|min:0|max:100',
            'severity' => 'nullable|string|in:critical,high,medium,low,unknown',
            'first_seen' => 'nullable|date',
            'last_seen' => 'nullable|date',
            'source_ref' => 'nullable|string|max:255',
            'raw' => 'nullable|json',
        ]);

        $node->update($validated);
        activity_log('update', 'node', $node->id, "Updated entity: {$node->name}");

        return redirect()->route('knowledge.entities.show', $node)
            ->with('success', "Entity '{$node->name}' updated.");
    }

    public function entityDestroy(Node $node)
    {
        $name = $node->name;
        activity_log('delete', 'node', $node->id, "Deleted entity: {$name}");
        $node->outgoingEdges()->delete();
        $node->incomingEdges()->delete();
        $node->delete();

        return redirect()->route('knowledge.entities.index')
            ->with('success', "Entity '{$name}' deleted.");
    }

    // ==========================================
    // RELATIONSHIPS (EDGES)
    // ==========================================

    public function relationshipsIndex(Request $request)
    {
        $query = Edge::with(['fromNode', 'toNode']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $query->whereHas('fromNode', fn($q) => $q->where('name', 'like', "%{$request->search}%"))
                  ->orWhereHas('toNode', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
        }

        $edges = $query->orderBy('updated_at', 'desc')->paginate(25)->withQueryString();
        return view('cti.knowledge.relationships.index', compact('edges'));
    }

    public function relationshipStore(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:50',
            'from_node_id' => 'required|uuid|exists:nodes,id',
            'to_node_id' => 'required|uuid|exists:nodes,id|different:from_node_id',
            'confidence' => 'nullable|integer|min:0|max:100',
            'start_time' => 'nullable|date',
            'stop_time' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();
        $edge = Edge::create($validated);
        activity_log('create', 'edge', $edge->id, "Created relationship: {$edge->type}");

        return back()->with('success', 'Relationship created.');
    }

    public function relationshipDestroy(Edge $edge)
    {
        activity_log('delete', 'edge', $edge->id, "Deleted relationship: {$edge->type}");
        $edge->delete();
        return back()->with('success', 'Relationship deleted.');
    }

    // ==========================================
    // GRAPH EXPLORER
    // ==========================================

    public function graphExplorer()
    {
        return view('cti.knowledge.graph');
    }

    /**
     * API: get subgraph for visualization.
     */
    public function apiSubgraph(Request $request)
    {
        $nodeId = $request->get('node_id');
        $depth = min((int) $request->get('depth', 1), 3);

        if ($nodeId) {
            $visited = collect();
            $edgeIds = collect();
            $this->traverseGraph($nodeId, $depth, $visited, $edgeIds);

            $nodes = Node::whereIn('id', $visited)->get();
            $edges = Edge::whereIn('id', $edgeIds)->with(['fromNode', 'toNode'])->get();
        } else {
            // Return all (limited)
            $nodes = Node::limit(100)->get();
            $edges = Edge::limit(200)->with(['fromNode', 'toNode'])->get();
        }

        return response()->json([
            'nodes' => $nodes->map(fn(Node $n) => [
                'id' => $n->id,
                'label' => $n->name,
                'type' => $n->type,
                'severity' => $n->severity,
                'confidence' => $n->confidence,
            ]),
            'edges' => $edges->map(fn(Edge $e) => [
                'id' => $e->id,
                'source' => $e->from_node_id,
                'target' => $e->to_node_id,
                'label' => $e->type,
                'confidence' => $e->confidence,
            ]),
        ]);
    }

    private function traverseGraph(string $nodeId, int $depth, &$visited, &$edgeIds): void
    {
        if ($depth < 0 || $visited->contains($nodeId)) return;
        $visited->push($nodeId);

        if ($depth === 0) return;

        $outEdges = Edge::where('from_node_id', $nodeId)->get();
        $inEdges = Edge::where('to_node_id', $nodeId)->get();

        foreach ($outEdges->merge($inEdges) as $edge) {
            $edgeIds->push($edge->id);
            $neighbor = $edge->from_node_id === $nodeId ? $edge->to_node_id : $edge->from_node_id;
            $this->traverseGraph($neighbor, $depth - 1, $visited, $edgeIds);
        }
    }
}
