@extends('layouts.master-cti')
@section('title', 'Alerts')
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 text-white"><i class="ri-alarm-warning-line me-2 text-danger"></i> Anomaly Alerts</h4>
                        <a href="{{ route('observations.index') }}" class="btn btn-sm btn-soft-info"><i class="ri-arrow-left-line"></i> Observations</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <p class="text-muted mb-0">High-severity anomalies detected by the ML Service. Promote to Knowledge Graph observables for investigation.</p>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-nowrap mb-0">
                                    <thead>
                                        <tr>
                                            <th>IP Address</th>
                                            <th>URL</th>
                                            <th>Score</th>
                                            <th>Severity</th>
                                            <th>Method</th>
                                            <th>Status</th>
                                            <th>Time</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($alerts as $log)
                                            @php
                                                $score = $log->anomaly_score ?? 0;
                                                $sev = $score >= 80 ? 'critical' : ($score >= 60 ? 'high' : 'medium');
                                            @endphp
                                            <tr>
                                                <td><code>{{ $log->ip_address }}</code></td>
                                                <td class="text-truncate" style="max-width:200px" title="{{ $log->url }}">{{ $log->url }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $sev === 'critical' ? 'danger' : ($sev === 'high' ? 'warning' : 'info') }}">
                                                        {{ number_format($score, 1) }}
                                                    </span>
                                                </td>
                                                <td><span class="badge bg-{{ $sev === 'critical' ? 'danger' : ($sev === 'high' ? 'warning' : 'info') }}">{{ $sev }}</span></td>
                                                <td>{{ $log->method }}</td>
                                                <td>{{ $log->status_code }}</td>
                                                <td>{{ $log->created_at->diffForHumans() }}</td>
                                                <td>
                                                    <form action="{{ route('observations.promote', $log) }}" method="POST">
                                                        @csrf
                                                        <button class="btn btn-sm btn-soft-primary" title="Promote to Observable">
                                                            <i class="ri-upload-2-line"></i> Promote
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="8" class="text-center text-muted py-4">No high-severity alerts found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">{{ $alerts->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
