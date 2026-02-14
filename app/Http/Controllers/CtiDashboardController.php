<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\Edge;
use App\Models\CaseModel;
use App\Models\ActivityLog;
use App\Models\Integration;
use Illuminate\Http\Request;

class CtiDashboardController extends Controller
{
    public function index()
    {
        $threatTypes = ['threat-actor', 'malware', 'campaign', 'intrusion-set', 'vulnerability'];

        // KPI counts per type
        $counts = Node::whereIn('type', $threatTypes)
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        // Fill missing types with 0
        foreach ($threatTypes as $t) {
            $counts[$t] = $counts[$t] ?? 0;
        }

        $totalThreats = array_sum($counts);
        $totalEntities = Node::count();
        $totalRelationships = Edge::count();
        $totalCases = CaseModel::count();

        // Severity distribution
        $severityDist = Node::whereIn('type', $threatTypes)
            ->whereNotNull('severity')
            ->selectRaw('severity, count(*) as total')
            ->groupBy('severity')
            ->pluck('total', 'severity')
            ->toArray();

        // Recent threats
        $recentThreats = Node::whereIn('type', $threatTypes)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        // Recent activity
        $recentActivity = ActivityLog::orderByDesc('created_at')
            ->take(10)
            ->get();

        // Active cases
        $activeCases = CaseModel::where('status', '!=', 'closed')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Connector status
        $connectors = Integration::orderByDesc('last_run_at')->take(5)->get();

        return view('cti.dashboard.index', compact(
            'counts',
            'totalThreats',
            'totalEntities',
            'totalRelationships',
            'totalCases',
            'severityDist',
            'recentThreats',
            'recentActivity',
            'activeCases',
            'connectors'
        ));
    }
}
