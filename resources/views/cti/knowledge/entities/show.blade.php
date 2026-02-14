@extends('layouts.master-cti')
@section('title', $node->name)
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 text-white">
                            <i class="{{ $node->icon }} me-2" style="color: {{ $node->color }}"></i>
                            {{ $node->name }}
                            <span class="badge ms-2" style="background: {{ $node->color }}22; color: {{ $node->color }}; border: 1px solid {{ $node->color }}44;">{{ $node->type }}</span>
                        </h4>
                        <div class="page-title-right d-flex gap-2">
                            <a href="{{ route('knowledge.entities.edit', $node) }}" class="btn btn-sm btn-soft-warning"><i class="ri-edit-line"></i> Edit</a>
                            <form action="{{ route('knowledge.entities.destroy', $node) }}" method="POST" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-soft-danger"><i class="ri-delete-bin-line"></i> Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Main Info --}}
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">Details</h6></div>
                        <div class="card-body">
                            @if($node->description)
                                <p class="text-muted">{{ $node->description }}</p>
                            @endif
                            <div class="row g-3">
                                <div class="col-md-3"><strong class="text-muted d-block">Confidence</strong>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <div class="progress flex-grow-1" style="height:6px"><div class="progress-bar bg-info" style="width:{{ $node->confidence ?? 0 }}%"></div></div>
                                        <span>{{ $node->confidence ?? 0 }}%</span>
                                    </div>
                                </div>
                                <div class="col-md-3"><strong class="text-muted d-block">Severity</strong>
                                    <span class="badge mt-1 bg-{{ $node->severity === 'critical' ? 'danger' : ($node->severity === 'high' ? 'warning' : ($node->severity === 'medium' ? 'info' : 'secondary')) }}">{{ $node->severity ?? '—' }}</span>
                                </div>
                                <div class="col-md-3"><strong class="text-muted d-block">First Seen</strong>{{ $node->first_seen ? $node->first_seen->format('Y-m-d') : '—' }}</div>
                                <div class="col-md-3"><strong class="text-muted d-block">Last Seen</strong>{{ $node->last_seen ? $node->last_seen->format('Y-m-d') : '—' }}</div>
                            </div>
                            @if($node->source_ref)
                                <div class="mt-3"><strong class="text-muted">Source Ref:</strong> <code>{{ $node->source_ref }}</code></div>
                            @endif
                        </div>
                    </div>

                    {{-- Relationships --}}
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Relationships ({{ count($outEdges) + count($inEdges) }})</h6>
                        </div>
                        <div class="card-body">
                            @if(count($outEdges) + count($inEdges) === 0)
                                <p class="text-muted text-center py-3">No relationships yet.</p>
                            @endif

                            @foreach($outEdges as $edge)
                                <div class="d-flex align-items-center py-2 border-bottom border-dark">
                                    <span class="text-info me-2">{{ $node->name }}</span>
                                    <span class="badge bg-soft-primary text-primary mx-2">{{ $edge->type }}</span>
                                    <i class="ri-arrow-right-line text-muted mx-1"></i>
                                    <a href="{{ route('knowledge.entities.show', $edge->toNode) }}" class="text-warning">
                                        <i class="{{ $edge->toNode->icon }} me-1"></i>{{ $edge->toNode->name }}
                                    </a>
                                    @if($edge->confidence)
                                        <span class="ms-auto text-muted small">{{ $edge->confidence }}%</span>
                                    @endif
                                </div>
                            @endforeach
                            @foreach($inEdges as $edge)
                                <div class="d-flex align-items-center py-2 border-bottom border-dark">
                                    <a href="{{ route('knowledge.entities.show', $edge->fromNode) }}" class="text-warning">
                                        <i class="{{ $edge->fromNode->icon }} me-1"></i>{{ $edge->fromNode->name }}
                                    </a>
                                    <span class="badge bg-soft-primary text-primary mx-2">{{ $edge->type }}</span>
                                    <i class="ri-arrow-right-line text-muted mx-1"></i>
                                    <span class="text-info">{{ $node->name }}</span>
                                    @if($edge->confidence)
                                        <span class="ms-auto text-muted small">{{ $edge->confidence }}%</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- External References --}}
                    @php $extRefs = $node->raw['external_references'] ?? null; @endphp
                    @if($extRefs)
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0"><i class="ri-external-link-line me-1 text-info"></i>External References</h6></div>
                        <div class="card-body">
                            @foreach(explode(',', $extRefs) as $ref)
                                @php $ref = trim($ref); @endphp
                                <div class="d-flex align-items-center py-1">
                                    <i class="ri-link me-2 text-muted"></i>
                                    @if(filter_var($ref, FILTER_VALIDATE_URL))
                                        <a href="{{ $ref }}" target="_blank" class="text-info text-break">{{ $ref }}</a>
                                    @else
                                        <code class="text-break">{{ $ref }}</code>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Notes / Annotations --}}
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="ri-sticky-note-line me-1 text-warning"></i>Notes</h6>
                        </div>
                        <div class="card-body">
                            @php $notes = $node->raw['notes'] ?? []; @endphp
                            @forelse($notes as $note)
                                <div class="border-bottom border-dark py-2">
                                    <div class="d-flex justify-content-between">
                                        <strong class="small text-info">{{ $note['author'] ?? 'Unknown' }}</strong>
                                        <span class="text-muted" style="font-size:10px">{{ isset($note['created_at']) ? \Carbon\Carbon::parse($note['created_at'])->diffForHumans() : '' }}</span>
                                    </div>
                                    <p class="mb-0 mt-1 text-muted small">{{ $note['text'] }}</p>
                                </div>
                            @empty
                                <p class="text-muted text-center py-2 mb-0">No notes yet.</p>
                            @endforelse

                            <form method="POST" action="{{ route('threats.add-note', $node) }}" class="mt-3">
                                @csrf
                                <div class="input-group">
                                    <textarea name="note" class="form-control form-control-sm" rows="2" placeholder="Add a note..." required maxlength="2000"></textarea>
                                    <button class="btn btn-sm btn-cti-primary"><i class="ri-send-plane-line"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Sidebar Meta --}}
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">Metadata</h6></div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><strong class="text-muted">ID:</strong> <code class="small">{{ $node->id }}</code></li>
                                <li class="mb-2"><strong class="text-muted">Created:</strong> {{ $node->created_at->format('Y-m-d H:i') }}</li>
                                <li class="mb-2"><strong class="text-muted">Updated:</strong> {{ $node->updated_at->format('Y-m-d H:i') }}</li>
                                @if($node->creator)<li class="mb-2"><strong class="text-muted">Created By:</strong> {{ $node->creator->name }}</li>@endif
                            </ul>
                        </div>
                    </div>

                    {{-- Tags --}}
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">Tags</h6></div>
                        <div class="card-body">
                            @forelse($node->tags as $tag)
                                <span class="badge me-1 mb-1" style="background: {{ $tag->color }}33; color: {{ $tag->color }}; border: 1px solid {{ $tag->color }}55;">{{ $tag->name }}</span>
                            @empty
                                <span class="text-muted">No tags</span>
                            @endforelse
                        </div>
                    </div>

                    {{-- Raw JSON --}}
                    @if($node->raw)
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">Raw Data</h6></div>
                        <div class="card-body">
                            <pre class="bg-dark text-light p-2 rounded small mb-0" style="max-height:200px;overflow:auto;">{{ json_encode($node->raw, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
