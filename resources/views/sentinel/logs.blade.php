@extends('layouts.master')

@section('title')
    Daftar Log
@endsection

@section('css')
    <style>
        .log-row-anomaly {
            background-color: rgba(244, 67, 54, 0.1) !important;
            border-left: 4px solid #f44336;
        }
        .log-row-normal {
            border-left: 4px solid #4caf50;
        }
        .filter-btn.active {
            box-shadow: 0 0 0 3px rgba(var(--vz-primary-rgb), 0.3);
        }
        .severity-bar {
            height: 6px;
            border-radius: 3px;
            background-color: #e0e0e0;
        }
        .severity-fill {
            height: 100%;
            border-radius: 3px;
        }
    </style>
@endsection

@section('content')
    <x-breadcrumb title="Daftar Log" pagetitle="Log Sentinel" />

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0">
                    <div class="row align-items-center g-3">
                        <div class="col-sm-12 col-md-6">
                            <h4 class="card-title mb-0">
                                <i class="ri-file-list-3-line me-2 text-primary"></i>
                                Riwayat Log Server
                            </h4>
                            <p class="text-muted mb-0 mt-1">
                                Menampilkan {{ $logs->total() }} log tercatat
                            </p>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <div class="d-flex justify-content-md-end gap-2 flex-wrap">
                                <!-- Filter Buttons -->
                                <a href="{{ route('sentinel.logs') }}" 
                                   class="btn btn-soft-secondary btn-sm filter-btn {{ !request('filter') || request('filter') == 'all' ? 'active' : '' }}">
                                    <i class="ri-list-check me-1"></i> Semua
                                </a>
                                <a href="{{ route('sentinel.logs', ['filter' => 'normal']) }}" 
                                   class="btn btn-soft-success btn-sm filter-btn {{ request('filter') == 'normal' ? 'active' : '' }}">
                                    <i class="ri-check-line me-1"></i> Normal
                                </a>
                                <a href="{{ route('sentinel.logs', ['filter' => 'anomaly']) }}" 
                                   class="btn btn-soft-danger btn-sm filter-btn {{ request('filter') == 'anomaly' ? 'active' : '' }}">
                                    <i class="ri-alert-line me-1"></i> Anomali
                                </a>
                                <a href="{{ route('sentinel.dashboard') }}" class="btn btn-primary btn-sm">
                                    <i class="ri-arrow-left-line me-1"></i> Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width: 60px;">ID</th>
                                    <th scope="col">Waktu</th>
                                    <th scope="col">IP Address</th>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Status Code</th>
                                    <th scope="col">Response Time</th>
                                    <th scope="col">User Agent</th>
                                    <th scope="col">Prediksi</th>
                                    <th scope="col">Severity</th>
                                    <th scope="col">Confidence</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr class="{{ $log->isAnomaly() ? 'log-row-anomaly' : 'log-row-normal' }}">
                                    <td>
                                        <span class="badge {{ $log->badge_class }}">
                                            #{{ $log->id }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="fw-medium">{{ $log->created_at->format('d/m/Y') }}</span>
                                        </div>
                                        <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                    </td>
                                    <td>
                                        <code class="fs-13">{{ $log->ip_address }}</code>
                                    </td>
                                    <td>
                                        @php
                                            $methodClass = match($log->method) {
                                                'GET' => 'bg-success-subtle text-success',
                                                'POST' => 'bg-primary-subtle text-primary',
                                                'PUT' => 'bg-warning-subtle text-warning',
                                                'PATCH' => 'bg-info-subtle text-info',
                                                'DELETE' => 'bg-danger-subtle text-danger',
                                                default => 'bg-secondary-subtle text-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $methodClass }}">{{ $log->method }}</span>
                                    </td>
                                    <td>
                                        <span title="{{ $log->url }}" class="d-inline-block text-truncate" style="max-width: 200px;">
                                            {{ $log->url }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match(true) {
                                                $log->status_code >= 500 => 'bg-danger',
                                                $log->status_code >= 400 => 'bg-warning',
                                                $log->status_code >= 300 => 'bg-info',
                                                default => 'bg-success',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ $log->status_code }}</span>
                                    </td>
                                    <td>
                                        <span class="{{ $log->response_time > 1000 ? 'text-danger fw-bold' : ($log->response_time > 500 ? 'text-warning' : 'text-success') }}">
                                            {{ number_format($log->response_time, 0) }} ms
                                        </span>
                                    </td>
                                    <td>
                                        <span class="d-inline-block text-truncate" style="max-width: 150px;" title="{{ $log->user_agent }}">
                                            {{ $log->user_agent ?? 'Unknown' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($log->isAnomaly())
                                            <span class="badge bg-danger">
                                                <i class="ri-alert-fill me-1"></i> ANOMALI
                                            </span>
                                        @else
                                            <span class="badge bg-success">
                                                <i class="ri-check-fill me-1"></i> Normal
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-2 fw-medium {{ $log->severity_score >= 70 ? 'text-danger' : ($log->severity_score >= 50 ? 'text-warning' : 'text-success') }}">
                                                {{ number_format($log->severity_score, 1) }}
                                            </span>
                                            <div class="severity-bar" style="width: 50px;">
                                                @php
                                                    $severityColor = match(true) {
                                                        $log->severity_score >= 80 => '#f44336',
                                                        $log->severity_score >= 60 => '#ff9800',
                                                        $log->severity_score >= 40 => '#ffc107',
                                                        default => '#4caf50',
                                                    };
                                                @endphp
                                                <div class="severity-fill" style="width: {{ min($log->severity_score, 100) }}%; background-color: {{ $severityColor }};"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ number_format($log->confidence_score * 100, 1) }}%</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="ri-inbox-line fs-1 d-block mb-3"></i>
                                            <h5>Tidak ada data log</h5>
                                            <p class="mb-0">Belum ada log yang tercatat dalam sistem</p>
                                            <a href="{{ route('sentinel.dashboard') }}" class="btn btn-primary mt-3">
                                                <i class="ri-arrow-left-line me-1"></i> Kembali ke Dashboard
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($logs->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Menampilkan {{ $logs->firstItem() }} - {{ $logs->lastItem() }} dari {{ $logs->total() }} log
                        </div>
                        <div>
                            {{ $logs->withQueryString()->links() }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
