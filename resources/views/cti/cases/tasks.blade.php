@extends('layouts.master-cti')
@section('title', 'All Tasks')
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box"><h4 class="mb-sm-0 text-white"><i class="ri-task-line me-2 text-info"></i> All Tasks</h4></div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-nowrap mb-0">
                                    <thead><tr><th>Task</th><th>Case</th><th>Status</th><th>Assignee</th><th>Due</th></tr></thead>
                                    <tbody>
                                        @forelse ($tasks as $task)
                                            <tr>
                                                <td>{{ $task->title }}</td>
                                                <td><a href="{{ route('cases.incidents.show', $task->case_id) }}" class="text-info">{{ $task->case->title ?? '—' }}</a></td>
                                                <td><span class="badge bg-{{ $task->status === 'done' ? 'success' : ($task->status === 'in-progress' ? 'info' : 'secondary') }}">{{ $task->status }}</span></td>
                                                <td>{{ $task->assignee?->name ?? '—' }}</td>
                                                <td>{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted py-4">No tasks yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
