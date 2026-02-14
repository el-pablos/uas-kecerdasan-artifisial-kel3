@extends('layouts.master-cti')
@section('title', 'Observations')
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 text-white"><i class="ri-eye-line me-2 text-cyan"></i> Observations</h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('observations.alerts') }}" class="btn btn-sm btn-soft-danger"><i class="ri-alarm-warning-line"></i> Alerts</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stats row --}}
            <div class="row mb-3">
                @foreach($stats as $label => $val)
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body py-3 text-center">
                                <h4 class="text-info mb-0">{{ $val }}</h4>
                                <small class="text-muted">{{ ucfirst(str_replace('-', ' ', $label)) }}s</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-nowrap mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Severity</th>
                                            <th>Confidence</th>
                                            <th>First Seen</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($observations as $node)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('knowledge.entities.show', $node) }}" class="text-info">
                                                        <i class="{{ $node->icon }} me-1"></i>{{ $node->name }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="badge" style="background: {{ $node->color }}22; color: {{ $node->color }}">{{ $node->type }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $node->severity === 'critical' ? 'danger' : ($node->severity === 'high' ? 'warning' : 'info') }}">{{ $node->severity }}</span>
                                                </td>
                                                <td>{{ $node->confidence ?? 0 }}%</td>
                                                <td>{{ $node->first_seen ? $node->first_seen->format('Y-m-d H:i') : '—' }}</td>
                                                <td>
                                                    <a href="{{ route('knowledge.entities.show', $node) }}" class="btn btn-sm btn-soft-info"><i class="ri-eye-line"></i></a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-center text-muted py-4">No observations yet. Promote alerts or import STIX data.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">{{ $observations->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
