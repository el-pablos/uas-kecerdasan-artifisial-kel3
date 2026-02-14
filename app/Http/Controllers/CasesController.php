<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use App\Models\CaseTask;
use App\Models\CaseItem;
use App\Models\Node;
use App\Models\Edge;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class CasesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ===== INCIDENTS (Cases) =====

    public function incidentsIndex(Request $request)
    {
        $query = CaseModel::with(['owner', 'tasks']);

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        $cases = $query->orderBy('updated_at', 'desc')->paginate(25)->withQueryString();

        $stats = [
            'open' => CaseModel::where('status', 'open')->count(),
            'in-progress' => CaseModel::where('status', 'in-progress')->count(),
            'closed' => CaseModel::where('status', 'closed')->count(),
        ];

        return view('cti.cases.index', compact('cases', 'stats'));
    }

    public function incidentCreate()
    {
        return view('cti.cases.create');
    }

    public function incidentStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:incident,rfi,takedown',
            'severity' => 'required|string|in:critical,high,medium,low',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        $validated['status'] = 'open';
        $validated['owner_id'] = auth()->id();

        $case = CaseModel::create($validated);
        activity_log('create', 'case', $case->id, "Created case: {$case->title}");

        return redirect()->route('cases.incidents.show', $case)
            ->with('success', "Case '{$case->title}' created.");
    }

    public function incidentShow(CaseModel $case)
    {
        $case->load(['owner', 'tasks.assignee', 'items.itemable']);
        $availableNodes = Node::orderBy('name')->limit(100)->get();

        // Timeline: activity logs related to this case
        $timeline = ActivityLog::where(function ($q) use ($case) {
            $q->where('entity_type', 'case')->where('entity_id', $case->id);
            // Also include logs for tasks in this case
            $taskIds = $case->tasks->pluck('id')->toArray();
            if (!empty($taskIds)) {
                $q->orWhere(function ($q2) use ($taskIds) {
                    $q2->where('entity_type', 'task')->whereIn('entity_id', $taskIds);
                });
            }
        })->orderByDesc('created_at')->take(50)->get();

        // Scoped graph: entities attached to this case + their neighbors
        $caseGraph = $this->buildCaseGraph($case);

        return view('cti.cases.show', compact('case', 'availableNodes', 'timeline', 'caseGraph'));
    }

    public function incidentUpdate(Request $request, CaseModel $case)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'status' => 'sometimes|string|in:open,in-progress,closed',
            'severity' => 'sometimes|string|in:critical,high,medium,low',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        $case->update($validated);
        activity_log('update', 'case', $case->id, "Updated case: {$case->title}");

        return back()->with('success', 'Case updated.');
    }

    public function incidentDestroy(CaseModel $case)
    {
        $title = $case->title;
        activity_log('delete', 'case', $case->id, "Deleted case: {$title}");
        $case->tasks()->delete();
        $case->items()->delete();
        $case->delete();

        return redirect()->route('cases.incidents.index')
            ->with('success', "Case '{$title}' deleted.");
    }

    // ===== TASKS =====

    public function tasksIndex(Request $request)
    {
        $query = CaseTask::with(['case', 'assignee']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('assignee')) {
            $query->where('assignee_id', $request->assignee);
        }

        $tasks = $query->orderBy('due_date')->paginate(25)->withQueryString();
        return view('cti.cases.tasks', compact('tasks'));
    }

    public function taskStore(Request $request, CaseModel $case)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'pending';
        $task = $case->tasks()->create($validated);
        activity_log('create', 'task', $task->id, "Created task: {$task->title} in case #{$case->id}");

        return back()->with('success', 'Task added.');
    }

    public function taskUpdate(Request $request, CaseTask $task)
    {
        $validated = $request->validate([
            'status' => 'sometimes|string|in:pending,in-progress,done',
            'title' => 'sometimes|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $task->update($validated);
        return back()->with('success', 'Task updated.');
    }

    // ===== CASE ITEMS (attach nodes) =====

    public function attachItem(Request $request, CaseModel $case)
    {
        $validated = $request->validate([
            'node_id' => 'required|uuid|exists:nodes,id',
        ]);

        $exists = $case->items()->where('itemable_type', Node::class)
            ->where('itemable_id', $validated['node_id'])->exists();

        if (!$exists) {
            $case->items()->create([
                'itemable_type' => Node::class,
                'itemable_id' => $validated['node_id'],
            ]);
        }

        return back()->with('success', 'Item attached to case.');
    }

    public function detachItem(CaseModel $case, CaseItem $item)
    {
        $item->delete();
        return back()->with('success', 'Item removed from case.');
    }

    /**
     * Export case as Markdown report.
     */
    public function exportReport(CaseModel $case)
    {
        $case->load(['owner', 'tasks.assignee', 'items.itemable']);

        $md = "# Incident Report: {$case->title}\n\n";
        $md .= "**ID:** `{$case->id}`  \n";
        $md .= "**Severity:** {$case->severity}  \n";
        $md .= "**Status:** {$case->status}  \n";
        $md .= "**Owner:** " . ($case->owner?->name ?? '—') . "  \n";
        $md .= "**Created:** {$case->created_at->format('Y-m-d H:i')}  \n";
        $md .= "**Due:** " . ($case->due_date ? $case->due_date->format('Y-m-d') : '—') . "  \n\n";

        if ($case->description) {
            $md .= "## Description\n\n{$case->description}\n\n";
        }

        // Tasks
        $md .= "## Tasks\n\n";
        if ($case->tasks->count()) {
            $md .= "| # | Task | Status | Assignee |\n|---|------|--------|----------|\n";
            foreach ($case->tasks as $i => $task) {
                $assignee = $task->assignee?->name ?? '—';
                $md .= "| " . ($i + 1) . " | {$task->title} | {$task->status} | {$assignee} |\n";
            }
        } else {
            $md .= "No tasks.\n";
        }
        $md .= "\n";

        // Linked entities
        $md .= "## Linked Entities\n\n";
        if ($case->items->count()) {
            foreach ($case->items as $item) {
                if ($item->itemable) {
                    $md .= "- **{$item->itemable->name}** ({$item->itemable->type})\n";
                }
            }
        } else {
            $md .= "No entities linked.\n";
        }

        $md .= "\n---\n*Generated by Log Sentinel CTI on " . now()->format('Y-m-d H:i') . "*\n";

        $filename = 'case-report-' . \Str::slug($case->title) . '.md';

        return response($md, 200, [
            'Content-Type' => 'text/markdown',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Build scoped graph data for case entities.
     */
    private function buildCaseGraph(CaseModel $case): array
    {
        $nodeIds = $case->items
            ->where('itemable_type', Node::class)
            ->pluck('itemable_id')
            ->toArray();

        if (empty($nodeIds)) {
            return ['nodes' => [], 'edges' => []];
        }

        $nodes = Node::whereIn('id', $nodeIds)->get();

        // Get edges between case nodes
        $edges = Edge::where(function ($q) use ($nodeIds) {
            $q->whereIn('from_node_id', $nodeIds)
              ->whereIn('to_node_id', $nodeIds);
        })->get();

        // Also get 1-hop neighbors
        $neighborEdges = Edge::where(function ($q) use ($nodeIds) {
            $q->whereIn('from_node_id', $nodeIds)
              ->orWhereIn('to_node_id', $nodeIds);
        })->take(100)->get();

        $neighborIds = [];
        foreach ($neighborEdges as $e) {
            if (!in_array($e->from_node_id, $nodeIds)) $neighborIds[] = $e->from_node_id;
            if (!in_array($e->to_node_id, $nodeIds)) $neighborIds[] = $e->to_node_id;
        }
        $neighborIds = array_unique($neighborIds);
        $neighborNodes = Node::whereIn('id', $neighborIds)->get();

        $allNodes = $nodes->merge($neighborNodes);
        $allEdges = $neighborEdges;

        $formatted = [
            'nodes' => $allNodes->map(fn($n) => [
                'data' => [
                    'id' => $n->id,
                    'label' => $n->name,
                    'type' => $n->type,
                    'color' => $n->color,
                    'icon' => $n->icon,
                    'isCase' => in_array($n->id, $nodeIds),
                ]
            ])->values()->toArray(),
            'edges' => $allEdges->map(fn($e) => [
                'data' => [
                    'id' => $e->id,
                    'source' => $e->from_node_id,
                    'target' => $e->to_node_id,
                    'type' => $e->type,
                ]
            ])->values()->toArray(),
        ];

        return $formatted;
    }
}
