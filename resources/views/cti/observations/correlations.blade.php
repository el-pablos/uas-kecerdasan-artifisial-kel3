@extends('layouts.master-cti')
@section('title', 'Observable Correlations')
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 text-white"><i class="ri-git-merge-line me-2 text-purple"></i> Observable Correlations</h4>
                        <a href="{{ route('observations.index') }}" class="btn btn-sm btn-soft-info"><i class="ri-arrow-left-line"></i> Back to Observations</a>
                    </div>
                </div>
            </div>

            {{-- Correlation clusters --}}
            @if(count($clusters) > 0)
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ri-node-tree me-1 text-warning"></i> Correlation Clusters</h6>
                            <p class="text-muted mb-0 small">Observables that share common target entities (2+ sources required)</p>
                        </div>
                        <div class="card-body">
                            @foreach($clusters as $i => $cluster)
                                <div class="border border-dark rounded p-3 mb-3">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="ri-focus-3-line text-danger"></i>
                                        <strong class="text-warning">
                                            <a href="{{ route('knowledge.entities.show', $cluster['target']) }}" class="text-warning">
                                                {{ $cluster['target']->name }}
                                            </a>
                                        </strong>
                                        <span class="badge" style="background:{{ $cluster['target']->color ?? '#6366f1' }}22;color:{{ $cluster['target']->color ?? '#6366f1' }}">{{ $cluster['target']->type }}</span>
                                        <span class="text-muted small">targeted by {{ count($cluster['sources']) }} observables</span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($cluster['sources'] as $src)
                                            <a href="{{ route('knowledge.entities.show', $src) }}" class="badge bg-soft-info text-info" style="font-size:11px">
                                                <i class="{{ $src->icon }} me-1"></i>{{ $src->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @else
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-4">
                                <i class="ri-link-unlink text-muted" style="font-size:40px"></i>
                                <p class="text-muted mt-2 mb-0">No correlation clusters found. Add more observables and relationships to discover patterns.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Top connected observables --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ri-bar-chart-horizontal-line me-1 text-info"></i> Most Connected Observables</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-nowrap mb-0">
                                    <thead>
                                        <tr>
                                            <th>Observable</th>
                                            <th>Severity</th>
                                            <th>Outgoing</th>
                                            <th>Incoming</th>
                                            <th>Total Links</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($observables as $obs)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('knowledge.entities.show', $obs) }}" class="text-info">
                                                        <i class="{{ $obs->icon }} me-1"></i>{{ $obs->name }}
                                                    </a>
                                                </td>
                                                <td><span class="badge-cti badge-{{ $obs->severity ?? 'unknown' }}">{{ $obs->severity ?? '—' }}</span></td>
                                                <td>{{ $obs->out_edges_count }}</td>
                                                <td>{{ $obs->in_edges_count }}</td>
                                                <td><strong>{{ $obs->out_edges_count + $obs->in_edges_count }}</strong></td>
                                                <td>
                                                    <a href="{{ route('knowledge.graph') }}?node_id={{ $obs->id }}" class="btn btn-sm btn-soft-purple" title="View in graph">
                                                        <i class="ri-mind-map"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-center text-muted py-3">No observables found.</td></tr>
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
