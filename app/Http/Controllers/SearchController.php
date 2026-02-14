<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\Edge;
use App\Models\CaseModel;
use App\Models\ServerLog;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Global search — returns HTML page with results.
     */
    public function search(Request $request)
    {
        $query = trim($request->get('q', ''));

        if ($request->wantsJson() || $request->ajax()) {
            return $this->apiSearch($query);
        }

        if (strlen($query) < 2) {
            return view('cti.search.results', [
                'query' => $query,
                'entities' => collect(),
                'cases' => collect(),
                'logs' => collect(),
                'activities' => collect(),
                'totalCount' => 0,
            ]);
        }

        // Search entities (nodes)
        $entities = Node::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->orWhere('stix_id', 'like', "%{$query}%")
            ->limit(30)
            ->get();

        // Search cases
        $cases = CaseModel::where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->limit(15)
            ->get();

        // Search server logs
        $logs = ServerLog::where('ip_address', 'like', "%{$query}%")
            ->orWhere('url', 'like', "%{$query}%")
            ->orWhere('method', 'like', "%{$query}%")
            ->limit(15)
            ->get();

        // Activity logs
        $activities = ActivityLog::where('description', 'like', "%{$query}%")
            ->orWhere('action', 'like', "%{$query}%")
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $totalCount = $entities->count() + $cases->count() + $logs->count() + $activities->count();

        return view('cti.search.results', compact('query', 'entities', 'cases', 'logs', 'activities', 'totalCount'));
    }

    /**
     * API search for AJAX autocomplete.
     */
    private function apiSearch(string $q)
    {
        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // Search nodes
        $nodes = Node::where('name', 'like', "%{$q}%")
            ->orWhere('description', 'like', "%{$q}%")
            ->limit(10)->get();

        foreach ($nodes as $node) {
            $results[] = [
                'type' => 'entity',
                'subtype' => $node->type,
                'icon' => $node->icon,
                'label' => $node->name,
                'url' => route('knowledge.entities.show', $node),
                'meta' => $node->severity,
            ];
        }

        // Search cases
        $cases = CaseModel::where('title', 'like', "%{$q}%")
            ->orWhere('description', 'like', "%{$q}%")
            ->limit(5)->get();

        foreach ($cases as $case) {
            $results[] = [
                'type' => 'case',
                'subtype' => $case->status,
                'icon' => 'ri-folder-warning-line',
                'label' => $case->title,
                'url' => route('cases.incidents.show', $case),
                'meta' => $case->status,
            ];
        }

        // Search server logs
        $logs = ServerLog::where('ip_address', 'like', "%{$q}%")
            ->orWhere('url', 'like', "%{$q}%")
            ->limit(5)->get();

        foreach ($logs as $log) {
            $results[] = [
                'type' => 'log',
                'subtype' => $log->prediction_result,
                'icon' => 'ri-file-list-3-line',
                'label' => "{$log->ip_address} → {$log->url}",
                'url' => route('sentinel.logs', ['search' => $log->ip_address]),
                'meta' => $log->prediction_result,
            ];
        }

        return response()->json([
            'results' => $results,
            'query' => $q,
            'count' => count($results),
            'search_url' => route('search', ['q' => $q]),
        ]);
    }
}
