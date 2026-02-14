@extends('layouts.master-cti')
@section('title', $case->title)
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 text-white">
                            <i class="ri-folder-warning-line me-2 text-warning"></i> {{ $case->title }}
                            <span class="badge ms-2 bg-{{ $case->severity_color }}">{{ $case->severity }}</span>
                            <span class="badge ms-1 bg-{{ $case->status === 'open' ? 'warning' : ($case->status === 'in-progress' ? 'info' : 'success') }}">{{ $case->status }}</span>
                        </h4>
                        <div class="d-flex gap-2">
                            <form action="{{ route('cases.incidents.update', $case) }}" method="POST" class="d-inline">
                                @csrf @method('PUT')
                                <input type="hidden" name="title" value="{{ $case->title }}">
                                <input type="hidden" name="severity" value="{{ $case->severity }}">
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width:auto">
                                    @foreach(['open','in-progress','closed'] as $s)
                                        <option value="{{ $s }}" {{ $case->status == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                            </form>
                            <form action="{{ route('cases.incidents.destroy', $case) }}" method="POST" onsubmit="return confirm('Delete incident?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-soft-danger"><i class="ri-delete-bin-line"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Details --}}
                <div class="col-lg-8">
                    @if($case->description)
                        <div class="card">
                            <div class="card-header"><h6 class="mb-0">Description</h6></div>
                            <div class="card-body"><p class="text-muted mb-0">{{ $case->description }}</p></div>
                        </div>
                    @endif

                    {{-- Tasks --}}
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Tasks ({{ $case->progress }}% complete)</h6>
                        </div>
                        <div class="card-body">
                            <div class="progress mb-3" style="height:8px">
                                <div class="progress-bar bg-info" style="width:{{ $case->progress }}%"></div>
                            </div>

                            @foreach($case->tasks as $task)
                                <div class="d-flex align-items-center py-2 border-bottom border-dark">
                                    <form action="{{ route('cases.tasks.update', $task) }}" method="POST" class="me-2">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="case_id" value="{{ $case->id }}">
                                        <input type="hidden" name="title" value="{{ $task->title }}">
                                        @php $next = $task->status === 'pending' ? 'in-progress' : ($task->status === 'in-progress' ? 'done' : 'pending'); @endphp
                                        <input type="hidden" name="status" value="{{ $next }}">
                                        <button class="btn btn-sm {{ $task->status === 'done' ? 'btn-success' : ($task->status === 'in-progress' ? 'btn-info' : 'btn-soft-secondary') }}">
                                            <i class="{{ $task->status === 'done' ? 'ri-checkbox-circle-line' : ($task->status === 'in-progress' ? 'ri-loader-4-line' : 'ri-checkbox-blank-circle-line') }}"></i>
                                        </button>
                                    </form>
                                    <span class="{{ $task->status === 'done' ? 'text-decoration-line-through text-muted' : '' }}">{{ $task->title }}</span>
                                    <span class="ms-auto badge bg-{{ $task->status === 'done' ? 'success' : ($task->status === 'in-progress' ? 'info' : 'secondary') }}">{{ $task->status }}</span>
                                </div>
                            @endforeach

                            {{-- Add task --}}
                            <form action="{{ route('cases.tasks.store') }}" method="POST" class="mt-3">
                                @csrf
                                <input type="hidden" name="case_id" value="{{ $case->id }}">
                                <div class="input-group">
                                    <input type="text" name="title" class="form-control form-control-sm" placeholder="Add task..." required>
                                    <button class="btn btn-sm btn-soft-primary"><i class="ri-add-line"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Attached Items --}}
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">Linked Entities</h6></div>
                        <div class="card-body">
                            @forelse($case->items as $item)
                                <div class="d-flex align-items-center py-2 border-bottom border-dark">
                                    @if($item->itemable)
                                        <i class="{{ $item->itemable->icon ?? 'ri-question-line' }} me-2 text-info"></i>
                                        <a href="{{ route('knowledge.entities.show', $item->itemable) }}" class="text-info">{{ $item->itemable->name ?? 'Unknown' }}</a>
                                        <span class="badge ms-2 bg-soft-primary text-primary">{{ class_basename($item->itemable_type) }}</span>
                                    @else
                                        <span class="text-muted">Deleted entity</span>
                                    @endif
                                    <form action="{{ route('cases.items.detach', $item) }}" method="POST" class="ms-auto" onsubmit="return confirm('Detach?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-soft-danger"><i class="ri-close-line"></i></button>
                                    </form>
                                </div>
                            @empty
                                <p class="text-muted text-center py-3">No entities linked.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">Metadata</h6></div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><strong class="text-muted">ID:</strong> <code class="small">{{ $case->id }}</code></li>
                                <li class="mb-2"><strong class="text-muted">Owner:</strong> {{ $case->owner?->name ?? '—' }}</li>
                                <li class="mb-2"><strong class="text-muted">Due:</strong> {{ $case->due_date ? $case->due_date->format('Y-m-d') : '—' }}</li>
                                <li class="mb-2"><strong class="text-muted">Created:</strong> {{ $case->created_at->format('Y-m-d H:i') }}</li>
                                <li class="mb-2"><strong class="text-muted">Updated:</strong> {{ $case->updated_at->format('Y-m-d H:i') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
