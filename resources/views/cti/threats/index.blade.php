@extends('layouts.master-cti')
@section('title', 'Threat ' . ucfirst(str_replace('-', ' ', $type)) . 's')
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 text-white">
                            <i class="ri-shield-flash-line me-2 text-danger"></i>
                            {{ ucfirst(str_replace('-', ' ', $type)) }}s
                            <span class="badge bg-soft-danger text-danger ms-1">{{ $nodes->total() }}</span>
                        </h4>
                        <div class="page-title-right d-flex gap-2">
                            <a href="{{ route('threats.' . $routeSegment . '.create') }}" class="btn btn-sm btn-soft-primary">
                                <i class="ri-add-line"></i> Create
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters row --}}
            <div class="row mb-3">
                <div class="col-12">
                    <form method="GET" class="d-flex gap-2 align-items-center">
                        <input type="text" name="search" class="form-control form-control-sm" style="width:240px" placeholder="Search name or description..." value="{{ request('search') }}">
                        <select name="severity" class="form-select form-select-sm" style="width:120px">
                            <option value="">All Severity</option>
                            @foreach(['critical','high','medium','low'] as $s)
                                <option value="{{ $s }}" {{ request('severity') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-cti-primary"><i class="ri-search-line"></i></button>
                        @if(request()->hasAny(['search','severity']))
                            <a href="{{ request()->url() }}" class="btn btn-sm btn-cti-outline">Clear</a>
                        @endif
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
                                            <th>Name</th>
                                            <th>Confidence</th>
                                            <th>Severity</th>
                                            <th>Relations</th>
                                            <th>First Seen</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($nodes as $node)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('knowledge.entities.show', $node) }}" class="text-info">
                                                        <i class="{{ $node->icon }} me-1"></i>{{ $node->name }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="progress" style="height: 6px; width: 60px;">
                                                            <div class="progress-bar {{ ($node->confidence ?? 0) >= 70 ? 'bg-success' : (($node->confidence ?? 0) >= 40 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ $node->confidence ?? 0 }}%"></div>
                                                        </div>
                                                        <small class="text-muted">{{ $node->confidence ?? 0 }}%</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge-cti badge-{{ $node->severity ?? 'unknown' }}">{{ $node->severity ?? 'unknown' }}</span>
                                                </td>
                                                <td>
                                                    <span class="text-muted">{{ ($node->out_edges_count ?? 0) + ($node->in_edges_count ?? 0) }}</span>
                                                </td>
                                                <td>{{ $node->first_seen ? $node->first_seen->format('Y-m-d') : '—' }}</td>
                                                <td>{{ $node->created_at->diffForHumans() }}</td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <a href="{{ route('knowledge.entities.show', $node) }}" class="btn btn-sm btn-soft-info" title="View"><i class="ri-eye-line"></i></a>
                                                        <a href="{{ route('knowledge.entities.edit', $node) }}" class="btn btn-sm btn-soft-warning" title="Edit"><i class="ri-edit-line"></i></a>
                                                        <button class="btn btn-sm btn-soft-success btn-quick-link" title="Quick Link" data-id="{{ $node->id }}" data-name="{{ $node->name }}" data-bs-toggle="modal" data-bs-target="#quickLinkModal"><i class="ri-link"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">
                                                    No {{ str_replace('-', ' ', $type) }}s found. <a href="{{ route('threats.' . $routeSegment . '.create') }}">Create one</a>.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $nodes->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Link Modal --}}
    <div class="modal fade" id="quickLinkModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background:var(--cti-bg-card);border:1px solid var(--cti-border);">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-white"><i class="ri-link me-2 text-success"></i>Quick Link Relationship</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('threats.quick-link') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="from_node_id" id="qlFromId">
                        <div class="mb-3">
                            <label class="form-label text-muted">From</label>
                            <input type="text" id="qlFromName" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Relationship Type</label>
                            <select name="type" class="form-select" required>
                                <option value="uses">uses</option>
                                <option value="targets">targets</option>
                                <option value="attributed-to">attributed-to</option>
                                <option value="indicates">indicates</option>
                                <option value="exploits">exploits</option>
                                <option value="mitigates">mitigates</option>
                                <option value="related-to">related-to</option>
                                <option value="variant-of">variant-of</option>
                                <option value="delivers">delivers</option>
                                <option value="drops">drops</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">To (Target Entity)</label>
                            <select name="to_node_id" class="form-select" required id="qlToNode">
                                <option value="">— Select target —</option>
                                @php $allNodes = \App\Models\Node::orderBy('name')->get(['id','name','type']); @endphp
                                @foreach($allNodes as $n)
                                    <option value="{{ $n->id }}">{{ $n->name }} ({{ $n->type }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label text-muted">Confidence</label>
                                <input type="number" name="confidence" class="form-control" min="0" max="100" value="50">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Description</label>
                            <textarea name="description" class="form-control" rows="2" maxlength="500"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-cti-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-cti-primary"><i class="ri-link me-1"></i>Create Link</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
<script>
document.querySelectorAll('.btn-quick-link').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('qlFromId').value = this.dataset.id;
        document.getElementById('qlFromName').value = this.dataset.name;
    });
});
</script>
@endsection
