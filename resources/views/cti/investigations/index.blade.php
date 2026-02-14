@extends('layouts.master-cti')
@section('title', 'Investigations')
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box"><h4 class="mb-sm-0 text-white"><i class="ri-search-eye-line me-2 text-purple"></i> Investigation Workspace</h4></div>
                </div>
            </div>
            <div class="row">
                {{-- Recent Cases --}}
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">Recent Open Cases</h6></div>
                        <div class="card-body">
                            @forelse ($cases as $case)
                                <div class="d-flex align-items-center py-2 border-bottom border-dark">
                                    <i class="ri-folder-warning-line text-warning me-2"></i>
                                    <a href="{{ route('cases.incidents.show', $case) }}" class="text-info flex-grow-1">{{ $case->title }}</a>
                                    <span class="badge bg-{{ $case->severity_color }} ms-2">{{ $case->severity }}</span>
                                </div>
                            @empty
                                <p class="text-muted text-center py-3">No open cases.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                {{-- Recent Nodes --}}
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">Recent Entities</h6></div>
                        <div class="card-body">
                            @forelse ($nodes as $node)
                                <div class="d-flex align-items-center py-2 border-bottom border-dark">
                                    <i class="{{ $node->icon }} me-2" style="color:{{ $node->color }}"></i>
                                    <a href="{{ route('knowledge.entities.show', $node) }}" class="text-info flex-grow-1">{{ $node->name }}</a>
                                    <span class="badge" style="background:{{ $node->color }}22;color:{{ $node->color }}">{{ $node->type }}</span>
                                </div>
                            @empty
                                <p class="text-muted text-center py-3">No entities yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="ri-mind-map display-4 text-muted mb-3 d-block"></i>
                            <h5 class="text-muted">Investigation Workspace</h5>
                            <p class="text-muted mb-3">Link cases with knowledge graph entities to build investigation timelines.</p>
                            <a href="{{ route('knowledge.graph') }}" class="btn btn-soft-primary"><i class="ri-mind-map me-1"></i> Open Graph Explorer</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
