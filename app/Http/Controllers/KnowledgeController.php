<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\Edge;
use App\Services\GraphService;
use Illuminate\Http\Request;

class KnowledgeController extends Controller
{
    protected GraphService $graph;

    public function __construct(GraphService $graph)
    {
        $this->middleware('auth');
        $this->graph = $graph;
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
        $node->load(['outgoingEdges.toNode', 'incomingEdges.fromNode', 'tags', 'creator']);
        $outEdges = $node->outgoingEdges;
        $inEdges = $node->incomingEdges;
        return view('cti.knowledge.entities.show', compact('node', 'outEdges', 'inEdges'));
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
        $allNodes = Node::orderBy('name')->get();
        return view('cti.knowledge.relationships.index', compact('edges', 'allNodes'));
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
     * API: get subgraph for visualization (Cytoscape-friendly).
     */
    public function apiSubgraph(Request $request)
    {
        $request->validate([
            'node_id'        => 'nullable|uuid|exists:nodes,id',
            'depth'          => 'nullable|integer|min:0|max:4',
            'type'           => 'nullable|string|max:50',
            'edge_type'      => 'nullable|string|max:50',
            'confidence_min' => 'nullable|integer|min:0|max:100',
            'severity'       => 'nullable|string|in:critical,high,medium,low',
            'date_from'      => 'nullable|date',
            'date_to'        => 'nullable|date',
            'tag'            => 'nullable|string|max:100',
            'max_nodes'      => 'nullable|integer|min:1|max:500',
            'max_edges'      => 'nullable|integer|min:1|max:1000',
        ]);

        $filters = $request->only([
            'type', 'edge_type', 'confidence_min', 'severity',
            'date_from', 'date_to', 'tag', 'max_nodes', 'max_edges',
        ]);

        $result = $this->graph->getSubgraph(
            $request->get('node_id'),
            (int) $request->get('depth', 1),
            $filters
        );

        return response()->json($result);
    }

    /**
     * API: suggest relationship types between two node types.
     */
    public function apiSuggestRelations(Request $request)
    {
        $request->validate([
            'from_type' => 'required|string|max:50',
            'to_type'   => 'required|string|max:50',
        ]);

        return response()->json([
            'suggestions' => $this->graph->suggestRelations(
                $request->from_type, $request->to_type
            ),
        ]);
    }

    /**
     * API: search nodes (autocomplete).
     */
    public function apiSearchNodes(Request $request)
    {
        $request->validate([
            'q'     => 'required|string|min:1|max:200',
            'types' => 'nullable|array',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $nodes = $this->graph->searchNodes(
            $request->q,
            $request->get('types', []),
            (int) $request->get('limit', 20)
        );

        return response()->json([
            'results' => $nodes->map(fn(Node $n) => [
                'id'    => $n->id,
                'name'  => $n->name,
                'type'  => $n->type,
                'color' => $n->color,
                'icon'  => $n->icon,
            ]),
        ]);
    }
}
