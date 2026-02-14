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
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-nowrap mb-0">
                                    <thead><tr><th>Name</th><th>Type</th><th>Status</th><th>Last Run</th><th>Actions</th></tr></thead>
                                    <tbody>
                                        @forelse ($connectors as $connector)
                                            <tr>
                                                <td class="fw-medium text-info">{{ $connector->name }}</td>
                                                <td><span class="badge bg-soft-primary text-primary">{{ $connector->type }}</span></td>
                                                <td>
                                                    <span class="badge bg-{{ $connector->status === 'running' ? 'info' : ($connector->status === 'error' ? 'danger' : 'secondary') }}">
                                                        {{ $connector->status }}
                                                    </span>
                                                </td>
                                                <td>{{ $connector->last_run_at ? $connector->last_run_at->diffForHumans() : 'Never' }}</td>
                                                <td>
                                                    @if($connector->command)
                                                        <form action="{{ route('ingestion.connectors.run', $connector) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button class="btn btn-sm btn-soft-success"><i class="ri-play-line"></i> Run</button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted py-4">No connectors configured.</td></tr>
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
