@extends('layouts.master-cti')
@section('title', 'Knowledge — Relationships')
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 text-white"><i class="ri-link me-2 text-primary"></i> Relationships</h4>
                    </div>
                </div>
            </div>

            {{-- Create Relationship --}}
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">Create Relationship</h6></div>
                        <div class="card-body">
                            @if($errors->any())
                                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                            @endif
                            <form action="{{ route('knowledge.relationships.store') }}" method="POST" class="row g-2 align-items-end">
                                @csrf
                                <div class="col-md-3">
                                    <label class="form-label">From Entity</label>
                                    <select name="from_node_id" class="form-select form-select-sm" required>
                                        <option value="">Select...</option>
                                        @foreach($allNodes as $n)
                                            <option value="{{ $n->id }}">{{ $n->name }} ({{ $n->type }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Relationship</label>
                                    <select name="type" class="form-select form-select-sm" required>
                                        @foreach(\App\Models\Edge::relationshipTypes() as $rt)
                                            <option value="{{ $rt }}">{{ $rt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">To Entity</label>
                                    <select name="to_node_id" class="form-select form-select-sm" required>
                                        <option value="">Select...</option>
                                        @foreach($allNodes as $n)
                                            <option value="{{ $n->id }}">{{ $n->name }} ({{ $n->type }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Confidence</label>
                                    <input type="number" name="confidence" class="form-control form-control-sm" min="0" max="100" value="50">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="ri-add-line"></i> Add</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- List --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-nowrap mb-0">
                                    <thead>
                                        <tr>
                                            <th>From</th>
                                            <th>Relationship</th>
                                            <th>To</th>
                                            <th>Confidence</th>
                                            <th>Created</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($edges as $edge)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('knowledge.entities.show', $edge->fromNode) }}" class="text-info">
                                                        <i class="{{ $edge->fromNode->icon }} me-1"></i>{{ $edge->fromNode->name }}
                                                    </a>
                                                </td>
                                                <td><span class="badge bg-soft-primary text-primary">{{ $edge->type }}</span></td>
                                                <td>
                                                    <a href="{{ route('knowledge.entities.show', $edge->toNode) }}" class="text-warning">
                                                        <i class="{{ $edge->toNode->icon }} me-1"></i>{{ $edge->toNode->name }}
                                                    </a>
                                                </td>
                                                <td>{{ $edge->confidence ?? '—' }}%</td>
                                                <td>{{ $edge->created_at->diffForHumans() }}</td>
                                                <td>
                                                    <form action="{{ route('knowledge.relationships.destroy', $edge) }}" method="POST" onsubmit="return confirm('Remove?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-soft-danger"><i class="ri-delete-bin-line"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-center text-muted py-4">No relationships yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">{{ $edges->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
