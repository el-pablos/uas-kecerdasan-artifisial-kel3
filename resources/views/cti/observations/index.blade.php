@extends('layouts.master-cti')
@section('title', 'Observations')
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
                        <h4 class="mb-sm-0 text-white"><i class="ri-eye-line me-2 text-cyan"></i> Observations</h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('observations.correlations') }}" class="btn btn-sm btn-soft-purple"><i class="ri-git-merge-line"></i> Correlations</a>
                            <a href="{{ route('observations.alerts') }}" class="btn btn-sm btn-soft-danger"><i class="ri-alarm-warning-line"></i> Alerts</a>
                            <form method="POST" action="{{ route('observations.bulk-promote') }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-soft-success" title="Auto-promote top 10 anomalies"><i class="ri-upload-2-line"></i> Bulk Promote</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stats row --}}
            <div class="row mb-3">
                @foreach($stats as $label => $val)
                    <div class="col-md-4">
                        <div class="cti-stat-card text-center">
                            <div class="cti-stat-number text-info">{{ $val }}</div>
                            <div class="cti-stat-label">{{ ucfirst(str_replace('-', ' ', $label)) }}s</div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Filters --}}
            <div class="row mb-3">
                <div class="col-12">
                    <form method="GET" class="d-flex gap-2 align-items-center">
                        <input type="text" name="search" class="form-control form-control-sm" style="width:220px" placeholder="Search..." value="{{ request('search') }}">
                        <select name="subtype" class="form-select form-select-sm" style="width:140px">
                            <option value="">All Types</option>
                            @foreach(['observable','sighting','indicator'] as $t)
                                <option value="{{ $t }}" {{ request('subtype') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                        <select name="severity" class="form-select form-select-sm" style="width:120px">
                            <option value="">All Severity</option>
                            @foreach(['critical','high','medium','low'] as $s)
                                <option value="{{ $s }}" {{ request('severity') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-cti-primary"><i class="ri-search-line"></i></button>
                        @if(request()->hasAny(['search','subtype','severity']))
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
                                                    <span class="badge-cti badge-{{ $node->severity ?? 'unknown' }}">{{ $node->severity ?? 'unknown' }}</span>
                                                </td>
                                                <td>{{ $node->confidence ?? 0 }}%</td>
                                                <td>{{ $node->first_seen ? $node->first_seen->format('Y-m-d H:i') : '—' }}</td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <a href="{{ route('knowledge.entities.show', $node) }}" class="btn btn-sm btn-soft-info" title="View"><i class="ri-eye-line"></i></a>
                                                        <button class="btn btn-sm btn-soft-warning btn-triage" title="Triage"
                                                                data-id="{{ $node->id }}" data-name="{{ $node->name }}"
                                                                data-severity="{{ $node->severity }}" data-confidence="{{ $node->confidence }}"
                                                                data-bs-toggle="modal" data-bs-target="#triageModal">
                                                            <i class="ri-shield-check-line"></i>
                                                        </button>
                                                    </div>
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

    {{-- Triage Modal --}}
    <div class="modal fade" id="triageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background:var(--cti-bg-card);border:1px solid var(--cti-border);">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-white"><i class="ri-shield-check-line me-2 text-warning"></i>Triage Observable</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="triageForm">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <p class="text-muted mb-3">Adjust severity and confidence for <strong id="triageName" class="text-info"></strong></p>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label text-muted">Severity</label>
                                <select name="severity" id="triageSeverity" class="form-select" required>
                                    @foreach(['critical','high','medium','low','unknown'] as $s)
                                        <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label text-muted">Confidence</label>
                                <input type="number" name="confidence" id="triageConfidence" class="form-control" min="0" max="100">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Triage Note</label>
                            <textarea name="triage_note" class="form-control" rows="2" maxlength="500" placeholder="Why this severity?"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-cti-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-cti-primary"><i class="ri-check-line me-1"></i>Update Triage</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
<script>
document.querySelectorAll('.btn-triage').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('triageName').textContent = this.dataset.name;
        document.getElementById('triageSeverity').value = this.dataset.severity || 'medium';
        document.getElementById('triageConfidence').value = this.dataset.confidence || 50;
        document.getElementById('triageForm').action = '/observations/triage/' + this.dataset.id;
    });
});
</script>
@endsection
