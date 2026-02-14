<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\Edge;
use App\Models\CaseModel;
use Illuminate\Http\Request;

class InvestigationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Investigations = Cases + Knowledge graph workspace
        $recentCases = CaseModel::with('owner')
            ->where('status', '!=', 'closed')
            ->orderBy('updated_at', 'desc')
            ->limit(10)->get();

        $recentNodes = Node::orderBy('updated_at', 'desc')->limit(20)->get();

        return view('cti.investigations.index', compact('recentCases', 'recentNodes'));
    }
}
