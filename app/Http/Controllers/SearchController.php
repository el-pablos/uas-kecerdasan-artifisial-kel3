<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\Edge;
use App\Models\CaseModel;
use App\Models\ServerLog;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function search(Request $request)
    {
        $q = $request->get('q', '');
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
                'subtype' => $case->type,
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
                'label' => "{$log->ip_address} → {$log->url}",
                'url' => route('sentinel.logs', ['search' => $log->ip_address]),
                'meta' => $log->prediction_result,
            ];
        }

        return response()->json(['results' => $results, 'query' => $q]);
    }
}
