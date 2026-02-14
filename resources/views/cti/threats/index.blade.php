@extends('layouts.master-cti')
@section('title', 'Threat ' . ucfirst(str_replace('-', ' ', $type)) . 's')
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 text-white">
                            <i class="ri-shield-flash-line me-2 text-danger"></i>
                            {{ ucfirst(str_replace('-', ' ', $type)) }}s
                        </h4>
                        <div class="page-title-right">
                            <a href="{{ route('threats.' . str_replace('_', '-', Str::plural($type == 'intrusion-set' ? 'intrusion-sets' : $type)) . '.create') }}" class="btn btn-sm btn-soft-primary">
                                <i class="ri-add-line"></i> Create
                            </a>
                        </div>
                    </div>
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
                                                    <div class="progress" style="height: 6px; width: 60px;">
                                                        <div class="progress-bar bg-info" style="width: {{ $node->confidence ?? 0 }}%"></div>
                                                    </div>
                                                    <small class="text-muted">{{ $node->confidence ?? 0 }}%</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $node->severity === 'critical' ? 'danger' : ($node->severity === 'high' ? 'warning' : ($node->severity === 'medium' ? 'info' : 'secondary')) }}">
                                                        {{ $node->severity ?? 'unknown' }}
                                                    </span>
                                                </td>
                                                <td>{{ $node->first_seen ? $node->first_seen->format('Y-m-d') : '—' }}</td>
                                                <td>{{ $node->created_at->diffForHumans() }}</td>
                                                <td>
                                                    <a href="{{ route('knowledge.entities.show', $node) }}" class="btn btn-sm btn-soft-info"><i class="ri-eye-line"></i></a>
                                                    <a href="{{ route('knowledge.entities.edit', $node) }}" class="btn btn-sm btn-soft-warning"><i class="ri-edit-line"></i></a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    No {{ str_replace('-', ' ', $type) }}s found. <a href="{{ route('threats.' . str_replace('_', '-', Str::plural($type == 'intrusion-set' ? 'intrusion-sets' : $type)) . '.create') }}">Create one</a>.
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
@endsection
