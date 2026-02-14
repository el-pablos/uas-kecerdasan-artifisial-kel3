@extends('layouts.master-cti')
@section('title', 'Settings — Audit Log')
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box"><h4 class="mb-sm-0 text-white"><i class="ri-history-line me-2"></i> Audit Log</h4></div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <form method="GET" class="d-flex gap-2">
                        <input type="text" name="action" class="form-control form-control-sm" placeholder="Filter by action..." value="{{ request('action') }}">
                        <input type="text" name="entity_type" class="form-control form-control-sm" placeholder="Entity type..." value="{{ request('entity_type') }}">
                        <button class="btn btn-sm btn-soft-info"><i class="ri-filter-line"></i></button>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-nowrap mb-0">
                                    <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Entity</th><th>Description</th></tr></thead>
                                    <tbody>
                                        @forelse ($logs as $log)
                                            <tr>
                                                <td><small>{{ $log->created_at->format('Y-m-d H:i:s') }}</small></td>
                                                <td>{{ $log->user?->name ?? 'System' }}</td>
                                                <td><span class="badge bg-soft-primary text-primary">{{ $log->action }}</span></td>
                                                <td><code class="small">{{ class_basename($log->entity_type) }}#{{ Str::limit($log->entity_id, 8) }}</code></td>
                                                <td class="text-muted">{{ Str::limit($log->description, 80) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted py-4">No activity logged yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">{{ $logs->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
