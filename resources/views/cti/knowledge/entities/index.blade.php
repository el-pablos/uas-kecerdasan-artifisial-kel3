@extends('layouts.master-cti')
@section('title', 'Knowledge — Entities')
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 text-white">
                            <i class="ri-database-2-line me-2 text-info"></i>
                            Knowledge Graph — Entities
                        </h4>
                        <div class="page-title-right">
                            <a href="{{ route('knowledge.entities.create') }}" class="btn btn-sm btn-soft-primary">
                                <i class="ri-add-line"></i> New Entity
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Type Filter Pills --}}
            <div class="row mb-3">
                <div class="col-12">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('knowledge.entities.index') }}" class="btn btn-sm {{ !request('type') ? 'btn-primary' : 'btn-soft-secondary' }}">
                            All ({{ array_sum($typeCounts->toArray()) }})
                        </a>
                        @foreach($typeCounts as $t => $c)
                            <a href="{{ route('knowledge.entities.index', ['type' => $t]) }}" class="btn btn-sm {{ request('type') == $t ? 'btn-primary' : 'btn-soft-secondary' }}">
                                {{ ucfirst(str_replace('-', ' ', $t)) }} ({{ $c }})
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Search --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <form method="GET" action="{{ route('knowledge.entities.index') }}">
                        @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
                        <div class="input-group">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search entities..." value="{{ request('search') }}">
                            <button class="btn btn-sm btn-soft-info" type="submit"><i class="ri-search-line"></i></button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-nowrap mb-0">
                                    <thead>
                                        <tr>
                                            <th>
                                                <a href="{{ route('knowledge.entities.index', array_merge(request()->all(), ['sort' => 'name', 'dir' => request('dir') == 'asc' ? 'desc' : 'asc'])) }}" class="text-muted">
                                                    Name <i class="ri-arrow-up-down-line"></i>
                                                </a>
                                            </th>
                                            <th>Type</th>
                                            <th>Confidence</th>
                                            <th>Severity</th>
                                            <th>
                                                <a href="{{ route('knowledge.entities.index', array_merge(request()->all(), ['sort' => 'created_at', 'dir' => request('dir') == 'asc' ? 'desc' : 'asc'])) }}" class="text-muted">
                                                    Created <i class="ri-arrow-up-down-line"></i>
                                                </a>
                                            </th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($entities as $node)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('knowledge.entities.show', $node) }}" class="text-info fw-medium">
                                                        <i class="{{ $node->icon }} me-1"></i>{{ $node->name }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="badge" style="background-color: {{ $node->color }}22; color: {{ $node->color }}; border: 1px solid {{ $node->color }}44;">
                                                        {{ $node->type }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-1">
                                                        <div class="progress flex-grow-1" style="height: 4px; width: 50px;">
                                                            <div class="progress-bar bg-info" style="width: {{ $node->confidence ?? 0 }}%"></div>
                                                        </div>
                                                        <small>{{ $node->confidence ?? 0 }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $node->severity === 'critical' ? 'danger' : ($node->severity === 'high' ? 'warning' : ($node->severity === 'medium' ? 'info' : 'secondary')) }}">
                                                        {{ $node->severity ?? '—' }}
                                                    </span>
                                                </td>
                                                <td>{{ $node->created_at->diffForHumans() }}</td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <a href="{{ route('knowledge.entities.show', $node) }}" class="btn btn-sm btn-soft-info" title="View"><i class="ri-eye-line"></i></a>
                                                        <a href="{{ route('knowledge.entities.edit', $node) }}" class="btn btn-sm btn-soft-warning" title="Edit"><i class="ri-edit-line"></i></a>
                                                        <form action="{{ route('knowledge.entities.destroy', $node) }}" method="POST" onsubmit="return confirm('Delete this entity?')">
                                                            @csrf @method('DELETE')
                                                            <button class="btn btn-sm btn-soft-danger" title="Delete"><i class="ri-delete-bin-line"></i></button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    No entities found. <a href="{{ route('knowledge.entities.create') }}">Create the first one</a>.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">{{ $entities->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
