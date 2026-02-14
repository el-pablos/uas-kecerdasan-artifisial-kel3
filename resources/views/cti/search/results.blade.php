@extends('layouts.master-cti')
@section('title', 'Search Results')
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 text-white">
                            <i class="ri-search-line me-2 text-info"></i>
                            Search Results
                            @if($query)
                                <span class="text-muted ms-2 fs-6">for "{{ $query }}"</span>
                            @endif
                        </h4>
                        <span class="badge bg-soft-info text-info fs-6">{{ $totalCount }} results</span>
                    </div>
                </div>
            </div>

            {{-- Search Bar --}}
            <div class="row mb-3">
                <div class="col-lg-8">
                    <form action="{{ route('search') }}" method="GET">
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="text" name="q" value="{{ $query }}" class="form-control form-control-lg"
                                   placeholder="Search entities, cases, logs, activities..." autofocus>
                            <button class="btn btn-primary"><i class="ri-search-line me-1"></i> Search</button>
                        </div>
                    </form>
                </div>
            </div>

            @if(strlen($query) < 2)
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="ri-search-eye-line display-4 text-muted"></i>
                        <p class="text-muted mt-3">Enter at least 2 characters to search across the platform.</p>
                    </div>
                </div>
            @else
                {{-- Entities --}}
                @if($entities->count())
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="ri-node-tree me-1 text-info"></i> Entities <span class="badge bg-soft-info text-info ms-1">{{ $entities->count() }}</span></h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead><tr><th>Name</th><th>Type</th><th>Severity</th><th>Confidence</th></tr></thead>
                                <tbody>
                                    @foreach($entities as $entity)
                                        <tr>
                                            <td>
                                                <i class="{{ $entity->icon }} me-2" style="color:{{ $entity->color }}"></i>
                                                <a href="{{ route('knowledge.entities.show', $entity) }}" class="text-info fw-medium">{{ $entity->name }}</a>
                                                @if($entity->stix_id)
                                                    <br><small class="text-muted">{{ $entity->stix_id }}</small>
                                                @endif
                                            </td>
                                            <td><span class="badge bg-soft-primary text-primary">{{ $entity->type }}</span></td>
                                            <td>
                                                @if($entity->severity)
                                                    @php
                                                        $sevColor = match($entity->severity) { 'critical' => 'danger', 'high' => 'warning', 'medium' => 'info', default => 'secondary' };
                                                    @endphp
                                                    <span class="badge bg-{{ $sevColor }}">{{ $entity->severity }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($entity->confidence)
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="confidence-bar flex-grow-1" style="width:60px">
                                                            <div class="confidence-fill bg-info" style="width:{{ $entity->confidence }}%"></div>
                                                        </div>
                                                        <small>{{ $entity->confidence }}%</small>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Cases --}}
                @if($cases->count())
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="ri-folder-warning-line me-1 text-warning"></i> Cases <span class="badge bg-soft-warning text-warning ms-1">{{ $cases->count() }}</span></h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead><tr><th>Title</th><th>Status</th><th>Severity</th><th>Created</th></tr></thead>
                                <tbody>
                                    @foreach($cases as $case)
                                        <tr>
                                            <td><a href="{{ route('cases.incidents.show', $case) }}" class="text-info fw-medium">{{ $case->title }}</a></td>
                                            <td>
                                                <span class="badge bg-{{ $case->status === 'open' ? 'warning' : ($case->status === 'in-progress' ? 'info' : 'success') }}">{{ $case->status }}</span>
                                            </td>
                                            <td><span class="badge" style="background:{{ $case->severity_color }}">{{ $case->severity }}</span></td>
                                            <td class="text-muted">{{ $case->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Server Logs --}}
                @if($logs->count())
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="ri-file-list-3-line me-1 text-success"></i> Server Logs <span class="badge bg-soft-success text-success ms-1">{{ $logs->count() }}</span></h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead><tr><th>IP</th><th>URL</th><th>Method</th><th>Prediction</th><th>Time</th></tr></thead>
                                <tbody>
                                    @foreach($logs as $log)
                                        <tr>
                                            <td class="fw-medium">{{ $log->ip_address }}</td>
                                            <td class="text-muted">{{ \Str::limit($log->url, 50) }}</td>
                                            <td><span class="badge bg-soft-secondary text-secondary">{{ $log->method }}</span></td>
                                            <td>
                                                <span class="badge bg-{{ $log->prediction_result === 'anomaly' ? 'danger' : 'success' }}">{{ $log->prediction_result }}</span>
                                            </td>
                                            <td class="text-muted">{{ $log->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Activity --}}
                @if($activities->count())
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="ri-time-line me-1 text-purple"></i> Activity <span class="badge bg-soft-primary text-primary ms-1">{{ $activities->count() }}</span></h6>
                    </div>
                    <div class="card-body">
                        @foreach($activities as $log)
                            <div class="d-flex align-items-start mb-2 pb-2 {{ !$loop->last ? 'border-bottom border-dark' : '' }}">
                                <span class="badge bg-soft-info text-info me-2">{{ $log->action }}</span>
                                <div>
                                    <p class="mb-0 small">{{ $log->description }}</p>
                                    <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($totalCount === 0)
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="ri-file-unknow-line display-4 text-muted"></i>
                        <p class="text-muted mt-3">No results found for "<strong>{{ $query }}</strong>".</p>
                        <p class="text-muted small">Try different keywords or check for typos.</p>
                    </div>
                </div>
                @endif
            @endif
        </div>
    </div>
@endsection
