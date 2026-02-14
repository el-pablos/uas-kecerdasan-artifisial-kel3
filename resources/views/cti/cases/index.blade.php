@extends('layouts.master-cti')
@section('title', 'Cases — Incidents')
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 text-white"><i class="ri-folder-warning-line me-2 text-warning"></i> Incident Response</h4>
                        <a href="{{ route('cases.incidents.create') }}" class="btn btn-sm btn-soft-primary"><i class="ri-add-line"></i> New Incident</a>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card"><div class="card-body py-3 text-center"><h4 class="text-warning mb-0">{{ $stats['open'] ?? 0 }}</h4><small class="text-muted">Open</small></div></div>
                </div>
                <div class="col-md-3">
                    <div class="card"><div class="card-body py-3 text-center"><h4 class="text-info mb-0">{{ $stats['in-progress'] ?? 0 }}</h4><small class="text-muted">In Progress</small></div></div>
                </div>
                <div class="col-md-3">
                    <div class="card"><div class="card-body py-3 text-center"><h4 class="text-success mb-0">{{ $stats['closed'] ?? 0 }}</h4><small class="text-muted">Closed</small></div></div>
                </div>
                <div class="col-md-3">
                    <div class="card"><div class="card-body py-3 text-center"><h4 class="text-light mb-0">{{ ($stats['open'] ?? 0) + ($stats['in-progress'] ?? 0) + ($stats['closed'] ?? 0) }}</h4><small class="text-muted">Total</small></div></div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-nowrap mb-0">
                                    <thead>
                                        <tr><th>Title</th><th>Severity</th><th>Status</th><th>Progress</th><th>Owner</th><th>Due</th><th>Actions</th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($cases as $case)
                                            <tr>
                                                <td><a href="{{ route('cases.incidents.show', $case) }}" class="text-info fw-medium">{{ $case->title }}</a></td>
                                                <td><span class="badge bg-{{ $case->severity_color }}">{{ $case->severity }}</span></td>
                                                <td><span class="badge bg-{{ $case->status === 'open' ? 'warning' : ($case->status === 'in-progress' ? 'info' : 'success') }}">{{ $case->status }}</span></td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="progress flex-grow-1" style="height:6px;width:80px">
                                                            <div class="progress-bar bg-info" style="width:{{ $case->progress }}%"></div>
                                                        </div>
                                                        <small>{{ $case->progress }}%</small>
                                                    </div>
                                                </td>
                                                <td>{{ $case->owner?->name ?? '—' }}</td>
                                                <td>{{ $case->due_date ? $case->due_date->format('Y-m-d') : '—' }}</td>
                                                <td>
                                                    <a href="{{ route('cases.incidents.show', $case) }}" class="btn btn-sm btn-soft-info"><i class="ri-eye-line"></i></a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="7" class="text-center text-muted py-4">No incidents. <a href="{{ route('cases.incidents.create') }}">Create one</a>.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">{{ $cases->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
