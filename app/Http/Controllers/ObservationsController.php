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
            $query->whereJsonContains('raw->observable_type', $request->subtype);
        }

        $nodes = $query->orderBy('updated_at', 'desc')->paginate(25)->withQueryString();

        // Stats
        $totalObservables = Node::where('type', 'observable')->count();
        $totalSightings = Node::where('type', 'sighting')->count();
        $totalIndicators = Node::where('type', 'indicator')->count();

        return view('cti.observations.index', compact('nodes', 'totalObservables', 'totalSightings', 'totalIndicators'));
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
}
