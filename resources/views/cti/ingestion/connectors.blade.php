@extends('layouts.master-cti')
@section('title', 'Connectors')
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 text-white"><i class="ri-plug-line me-2 text-green"></i> Connectors & Integrations</h4>
                        <a href="{{ route('ingestion.import') }}" class="btn btn-sm btn-soft-primary"><i class="ri-upload-2-line"></i> Import STIX</a>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show"><i class="ri-check-line me-1"></i> {{ session('success') }}</div>
            @endif

            {{-- Stats Row --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card mini-stats-wid">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-1">Total Connectors</p>
                                    <h5 class="mb-0 text-white">{{ $integrations->count() }}</h5>
                                </div>
                                <div class="avatar-sm"><span class="avatar-title bg-soft-primary rounded-circle"><i class="ri-plug-line text-primary"></i></span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card mini-stats-wid">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-1">Active</p>
                                    <h5 class="mb-0 text-success">{{ $integrations->where('status', 'success')->count() }}</h5>
                                </div>
                                <div class="avatar-sm"><span class="avatar-title bg-soft-success rounded-circle"><i class="ri-check-line text-success"></i></span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card mini-stats-wid">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-1">Running</p>
                                    <h5 class="mb-0 text-info">{{ $integrations->where('status', 'running')->count() }}</h5>
                                </div>
                                <div class="avatar-sm"><span class="avatar-title bg-soft-info rounded-circle"><i class="ri-loader-4-line text-info"></i></span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card mini-stats-wid">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-1">Errors</p>
                                    <h5 class="mb-0 text-danger">{{ $integrations->where('status', 'error')->count() }}</h5>
                                </div>
                                <div class="avatar-sm"><span class="avatar-title bg-soft-danger rounded-circle"><i class="ri-error-warning-line text-danger"></i></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-nowrap mb-0">
                                    <thead><tr><th>Name</th><th>Type</th><th>Schedule</th><th>Status</th><th>Last Run</th><th>Message</th><th>Actions</th></tr></thead>
                                    <tbody>
                                        @forelse ($integrations as $connector)
                                            <tr>
                                                <td class="fw-medium text-info">{{ $connector->name }}</td>
                                                <td><span class="badge bg-soft-primary text-primary">{{ $connector->type }}</span></td>
                                                <td><code class="small">{{ $connector->schedule ?? '—' }}</code></td>
                                                <td>
                                                    @php
                                                        $statusColor = match($connector->status) {
                                                            'running' => 'info',
                                                            'success' => 'success',
                                                            'error' => 'danger',
                                                            default => 'secondary',
                                                        };
                                                    @endphp
                                                    <span class="badge bg-{{ $statusColor }}">
                                                        {{ $connector->status ?? 'idle' }}
                                                    </span>
                                                </td>
                                                <td>{{ $connector->last_run_at ? $connector->last_run_at->diffForHumans() : 'Never' }}</td>
                                                <td><small class="text-muted">{{ \Str::limit($connector->last_message, 60) }}</small></td>
                                                <td>
                                                    <form action="{{ route('ingestion.connectors.run', $connector) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button class="btn btn-sm btn-soft-success" {{ $connector->status === 'running' ? 'disabled' : '' }}>
                                                            <i class="ri-play-line"></i> Run
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="7" class="text-center text-muted py-4">No connectors configured. Run seeder to add demo connectors.</td></tr>
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
