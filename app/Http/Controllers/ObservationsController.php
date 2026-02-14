<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\Edge;
use App\Models\ServerLog;
use Illuminate\Http\Request;

class ObservationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Node::whereIn('type', ['observable', 'sighting', 'indicator']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }
        if ($request->filled('subtype')) {
            $query->where('type', $request->subtype);
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        $observations = $query->orderBy('updated_at', 'desc')->paginate(25)->withQueryString();

        $stats = [
            'observable' => Node::where('type', 'observable')->count(),
            'sighting'   => Node::where('type', 'sighting')->count(),
            'indicator'  => Node::where('type', 'indicator')->count(),
        ];

        return view('cti.observations.index', compact('observations', 'stats'));
    }

    public function alerts(Request $request)
    {
        // Alerts = high-severity anomalies from server_logs
        $query = ServerLog::where('prediction_result', 'anomaly');

        if ($request->filled('severity')) {
            if ($request->severity === 'critical') {
                $query->where('severity_score', '>=', 80);
            } elseif ($request->severity === 'high') {
                $query->whereBetween('severity_score', [60, 79]);
            } elseif ($request->severity === 'medium') {
                $query->whereBetween('severity_score', [40, 59]);
            } else {
                $query->where('severity_score', '<', 40);
            }
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('ip_address', 'like', "%{$request->search}%")
                  ->orWhere('url', 'like', "%{$request->search}%");
            });
        }

        $alerts = $query->orderBy('created_at', 'desc')->paginate(25)->withQueryString();

        return view('cti.observations.alerts', compact('alerts'));
    }

    /**
     * Create observable node from an anomaly log.
     */
    public function promoteToObservable(Request $request, ServerLog $log)
    {
        // Create or find IP observable
        $ipNode = Node::firstOrCreate(
            ['type' => 'observable', 'name' => $log->ip_address],
            [
                'description' => "IP address observed in server logs",
                'confidence' => 70,
                'severity' => $log->severity_score >= 80 ? 'critical' : ($log->severity_score >= 60 ? 'high' : 'medium'),
                'first_seen' => $log->created_at,
                'last_seen' => $log->created_at,
                'source_ref' => 'log-sentinel',
                'raw' => [
                    'observable_type' => 'ipv4-addr',
                    'value' => $log->ip_address,
                    'log_id' => $log->id,
                ],
                'created_by' => auth()->id(),
            ]
        );

        // Create sighting edge if not exists
        $sighting = Node::firstOrCreate(
            ['type' => 'sighting', 'name' => "Sighting: {$log->ip_address} anomaly"],
            [
                'description' => "Anomalous activity detected: {$log->method} {$log->url} → {$log->status_code}",
                'confidence' => intval($log->confidence_score * 100),
                'severity' => $log->severity_score >= 80 ? 'critical' : ($log->severity_score >= 60 ? 'high' : 'medium'),
                'first_seen' => $log->created_at,
                'last_seen' => $log->created_at,
                'source_ref' => 'ml-ensemble',
                'raw' => [
                    'log_id' => $log->id,
                    'prediction' => $log->prediction_result,
                    'severity_score' => $log->severity_score,
                    'url' => $log->url,
                    'method' => $log->method,
                    'status_code' => $log->status_code,
                ],
                'created_by' => auth()->id(),
            ]
        );

        // Link observable → sighting
        Edge::firstOrCreate(
            ['from_node_id' => $ipNode->id, 'to_node_id' => $sighting->id, 'type' => 'sighting-of'],
            ['confidence' => 70, 'created_by' => auth()->id()]
        );

        activity_log('create', 'observable', $ipNode->id, "Promoted log #{$log->id} to observable node");

        return back()->with('success', "Observable created for {$log->ip_address}.");
    }

    /**
     * Bulk promote: auto-promote top N unlinked anomalies.
     */
    public function bulkPromote(Request $request)
    {
        $limit = min($request->input('limit', 10), 50);

        $logs = ServerLog::where('prediction_result', 'anomaly')
            ->whereNotIn('ip_address', Node::where('type', 'observable')->pluck('name'))
            ->orderByDesc('severity_score')
            ->take($limit)
            ->get();

        $created = 0;
        foreach ($logs as $log) {
            $ipNode = Node::firstOrCreate(
                ['type' => 'observable', 'name' => $log->ip_address],
                [
                    'description' => "IP address observed in server logs",
                    'confidence'  => 70,
                    'severity'    => $log->severity_score >= 80 ? 'critical' : ($log->severity_score >= 60 ? 'high' : 'medium'),
                    'first_seen'  => $log->created_at,
                    'last_seen'   => $log->created_at,
                    'source_ref'  => 'log-sentinel',
                    'raw'         => ['observable_type' => 'ipv4-addr', 'value' => $log->ip_address],
                    'created_by'  => auth()->id(),
                ]
            );

            $sighting = Node::firstOrCreate(
                ['type' => 'sighting', 'name' => "Sighting: {$log->ip_address} anomaly"],
                [
                    'description' => "Anomalous: {$log->method} {$log->url} → {$log->status_code}",
                    'confidence'  => intval($log->confidence_score * 100),
                    'severity'    => $log->severity_score >= 80 ? 'critical' : ($log->severity_score >= 60 ? 'high' : 'medium'),
                    'first_seen'  => $log->created_at,
                    'source_ref'  => 'ml-ensemble',
                    'raw'         => ['log_id' => $log->id],
                    'created_by'  => auth()->id(),
                ]
            );

            Edge::firstOrCreate(
                ['from_node_id' => $ipNode->id, 'to_node_id' => $sighting->id, 'type' => 'sighting-of'],
                ['confidence' => 70, 'created_by' => auth()->id()]
            );
            $created++;
        }

        activity_log('create', 'observable', null, "Bulk promoted {$created} anomalies");

        return back()->with('success', "{$created} observables created from top anomalies.");
    }

    /**
     * Triage: update severity/status of an observable.
     */
    public function triage(Request $request, Node $node)
    {
        $validated = $request->validate([
            'severity'   => 'required|in:critical,high,medium,low,unknown',
            'confidence' => 'nullable|integer|min:0|max:100',
            'triage_note' => 'nullable|string|max:500',
        ]);

        $node->update([
            'severity'   => $validated['severity'],
            'confidence' => $validated['confidence'] ?? $node->confidence,
        ]);

        if (!empty($validated['triage_note'])) {
            $raw = $node->raw ?? [];
            $raw['notes'] = $raw['notes'] ?? [];
            $raw['notes'][] = [
                'text'       => "[TRIAGE] {$validated['triage_note']}",
                'author'     => auth()->user()->name,
                'author_id'  => auth()->id(),
                'created_at' => now()->toISOString(),
            ];
            $node->update(['raw' => $raw]);
        }

        activity_log('update', 'node', $node->id, "Triaged {$node->name} → {$validated['severity']}");

        return back()->with('success', "{$node->name} triaged.");
    }

    /**
     * Correlation view: find observables that share common relations.
     */
    public function correlations(Request $request)
    {
        // Get observables with their edges
        $observables = Node::where('type', 'observable')
            ->withCount(['outEdges', 'inEdges'])
            ->orderByRaw('(out_edges_count + in_edges_count) DESC')
            ->take(50)
            ->get();

        // Find clusters: observables targeting the same entities
        $clusters = [];
        $targetMap = [];
        foreach ($observables as $obs) {
            $targets = Edge::where('from_node_id', $obs->id)
                ->with('toNode:id,name,type')
                ->get()
                ->pluck('toNode')
                ->filter();
            foreach ($targets as $t) {
                $targetMap[$t->id] = $targetMap[$t->id] ?? ['target' => $t, 'sources' => []];
                $targetMap[$t->id]['sources'][] = $obs;
            }
        }
        // Only keep clusters with 2+ sources
        foreach ($targetMap as $tid => $data) {
            if (count($data['sources']) >= 2) {
                $clusters[] = $data;
            }
        }

        return view('cti.observations.correlations', compact('observables', 'clusters'));
    }
}
