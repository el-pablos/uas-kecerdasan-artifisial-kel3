<?php

namespace App\Services;

use App\Models\Node;
use App\Models\Edge;
use Illuminate\Support\Collection;

class GraphService
{
    /**
     * Get a filtered subgraph starting from a node or all nodes.
     */
    public function getSubgraph(?string $nodeId, int $depth = 1, array $filters = []): array
    {
        $depth = min(max($depth, 0), 4);
        $maxNodes = min((int) ($filters['max_nodes'] ?? 200), 500);
        $maxEdges = min((int) ($filters['max_edges'] ?? 500), 1000);

        if ($nodeId) {
            return $this->traverseFiltered($nodeId, $depth, $filters, $maxNodes, $maxEdges);
        }

        return $this->getAllFiltered($filters, $maxNodes, $maxEdges);
    }

    /**
     * BFS traversal with filters.
     */
    private function traverseFiltered(string $startId, int $depth, array $filters, int $maxNodes, int $maxEdges): array
    {
        $visitedNodes = collect([$startId]);
        $collectedEdges = collect();
        $queue = collect([['id' => $startId, 'depth' => 0]]);
        $truncated = false;

        while ($queue->isNotEmpty()) {
            $current = $queue->shift();
            if ($current['depth'] >= $depth) continue;

            $edgeQuery = Edge::query()
                ->where(function ($q) use ($current) {
                    $q->where('from_node_id', $current['id'])
                      ->orWhere('to_node_id', $current['id']);
                });

            if (!empty($filters['edge_type'])) {
                $edgeQuery->where('type', $filters['edge_type']);
            }

            $edges = $edgeQuery->get();

            foreach ($edges as $edge) {
                if ($collectedEdges->contains('id', $edge->id)) continue;

                $neighbor = $edge->from_node_id === $current['id']
                    ? $edge->to_node_id : $edge->from_node_id;

                // Check node limit
                if (!$visitedNodes->contains($neighbor)) {
                    if ($visitedNodes->count() >= $maxNodes) {
                        $truncated = true;
                        continue;
                    }
                    $visitedNodes->push($neighbor);
                    $queue->push(['id' => $neighbor, 'depth' => $current['depth'] + 1]);
                }

                if ($collectedEdges->count() < $maxEdges) {
                    $collectedEdges->push($edge);
                } else {
                    $truncated = true;
                }
            }
        }

        // Fetch full node objects with filters
        $nodesQuery = Node::whereIn('id', $visitedNodes);
        $this->applyNodeFilters($nodesQuery, $filters);
        $nodes = $nodesQuery->get();

        // Re-filter edges to only include edges between fetched nodes
        $nodeIds = $nodes->pluck('id');
        $edges = $collectedEdges->filter(function ($e) use ($nodeIds) {
            return $nodeIds->contains($e->from_node_id) && $nodeIds->contains($e->to_node_id);
        })->values();

        return $this->formatResponse($nodes, $edges, $truncated);
    }

    /**
     * Get all nodes/edges with filters (no start node).
     */
    private function getAllFiltered(array $filters, int $maxNodes, int $maxEdges): array
    {
        $nodesQuery = Node::query();
        $this->applyNodeFilters($nodesQuery, $filters);
        $nodes = $nodesQuery->limit($maxNodes)->get();
        $truncated = Node::count() > $maxNodes;

        $nodeIds = $nodes->pluck('id');
        $edges = Edge::whereIn('from_node_id', $nodeIds)
            ->whereIn('to_node_id', $nodeIds)
            ->when(!empty($filters['edge_type']), fn($q) => $q->where('type', $filters['edge_type']))
            ->limit($maxEdges)
            ->get();

        return $this->formatResponse($nodes, $edges, $truncated);
    }

    /**
     * Apply standard node filters to a query builder.
     */
    private function applyNodeFilters($query, array $filters): void
    {
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['confidence_min'])) {
            $query->where('confidence', '>=', (int) $filters['confidence_min']);
        }
        if (!empty($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }
        if (!empty($filters['date_from'])) {
            $query->where('first_seen', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('last_seen', '<=', $filters['date_to']);
        }
        if (!empty($filters['tag'])) {
            $query->whereHas('tags', fn($q) => $q->where('name', $filters['tag']));
        }
    }

    /**
     * Format Cytoscape-friendly response.
     */
    private function formatResponse(Collection $nodes, $edges, bool $truncated): array
    {
        return [
            'elements' => [
                'nodes' => $nodes->map(fn(Node $n) => [
                    'data' => [
                        'id'         => $n->id,
                        'label'      => $n->name,
                        'type'       => $n->type,
                        'confidence' => $n->confidence ?? 0,
                        'severity'   => $n->severity ?? 'unknown',
                        'color'      => $n->color,
                        'icon'       => $n->icon,
                        'description' => $n->description,
                        'source_ref' => $n->source_ref,
                    ],
                ])->values(),
                'edges' => $edges->map(fn(Edge $e) => [
                    'data' => [
                        'id'         => $e->id,
                        'source'     => $e->from_node_id,
                        'target'     => $e->to_node_id,
                        'type'       => $e->type,
                        'label'      => $e->type,
                        'confidence' => $e->confidence ?? 0,
                    ],
                ])->values(),
            ],
            'meta' => [
                'node_count' => $nodes->count(),
                'edge_count' => $edges instanceof Collection ? $edges->count() : count($edges),
                'truncated'  => $truncated,
                'max_nodes'  => 200,
                'max_edges'  => 500,
            ],
        ];
    }

    /**
     * Get neighbors of a specific node.
     */
    public function getNeighbors(string $nodeId, string $direction = 'both', int $limit = 50): Collection
    {
        $query = Edge::query();

        if ($direction === 'out') {
            $query->where('from_node_id', $nodeId);
        } elseif ($direction === 'in') {
            $query->where('to_node_id', $nodeId);
        } else {
            $query->where(fn($q) => $q->where('from_node_id', $nodeId)->orWhere('to_node_id', $nodeId));
        }

        return $query->with(['fromNode', 'toNode'])->limit($limit)->get();
    }

    /**
     * Search nodes by text query and optional types.
     */
    public function searchNodes(string $query, array $types = [], int $limit = 20): Collection
    {
        $q = Node::where(function ($qb) use ($query) {
            $qb->where('name', 'like', "%{$query}%")
               ->orWhere('description', 'like', "%{$query}%")
               ->orWhere('source_ref', 'like', "%{$query}%");
        });

        if (!empty($types)) {
            $q->whereIn('type', $types);
        }

        return $q->orderByRaw("CASE WHEN name LIKE ? THEN 0 ELSE 1 END", ["{$query}%"])
            ->limit($limit)
            ->get();
    }

    /**
     * Suggest likely relationship types between two node types.
     */
    public function suggestRelations(string $fromType, string $toType): array
    {
        $map = [
            'threat-actor' => [
                'malware'   => ['uses', 'delivers', 'drops'],
                'tool'      => ['uses'],
                'identity'  => ['targets', 'impersonates'],
                'campaign'  => ['attributed-to'],
                'vulnerability' => ['exploits'],
                'infrastructure' => ['uses', 'communicates-with'],
                'technique' => ['uses'],
            ],
            'malware' => [
                'vulnerability' => ['exploits'],
                'tool'          => ['uses'],
                'infrastructure' => ['communicates-with'],
                'technique'     => ['uses'],
            ],
            'campaign' => [
                'threat-actor'   => ['attributed-to'],
                'malware'        => ['uses'],
                'technique'      => ['uses'],
                'vulnerability'  => ['exploits'],
                'identity'       => ['targets'],
            ],
            'indicator' => [
                'malware'       => ['indicates'],
                'threat-actor'  => ['indicates'],
                'campaign'      => ['indicates'],
                'observable'    => ['related-to'],
            ],
            'observable' => [
                'malware'       => ['related-to'],
                'indicator'     => ['related-to'],
                'infrastructure' => ['related-to'],
            ],
            'technique' => [
                'technique' => ['sub-technique-of'],
            ],
        ];

        return $map[$fromType][$toType]
            ?? $map[$toType][$fromType]
            ?? ['related-to'];
    }
}
