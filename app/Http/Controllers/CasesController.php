<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use App\Models\CaseTask;
use App\Models\CaseItem;
use App\Models\Node;
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
            'in_progress' => CaseModel::where('status', 'in-progress')->count(),
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

        return view('cti.cases.show', compact('case', 'availableNodes'));
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
}
