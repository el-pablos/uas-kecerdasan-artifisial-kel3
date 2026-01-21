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

        /* ========================================
           PAGINATION FIX - Override Velzon Conflicts
           ======================================== */
        
        /* Reset problematic pseudo-elements */
        .pagination .page-item .page-link::before,
        .pagination .page-item .page-link::after,
        .pagination .page-item::before,
        .pagination .page-item::after,
        nav[aria-label="Pagination Navigation"] .page-link::before,
        nav[aria-label="Pagination Navigation"] .page-link::after {
            content: none !important;
            display: none !important;
        }

        /* Base pagination container */
        .pagination,
        nav[aria-label="Pagination Navigation"] > span {
            display: flex !important;
            flex-wrap: wrap;
            padding-left: 0;
            list-style: none;
            gap: 4px;
            align-items: center;
            justify-content: flex-end;
        }

        /* Pagination items */
        .pagination .page-item,
        nav[aria-label="Pagination Navigation"] span a,
        nav[aria-label="Pagination Navigation"] span span {
            display: inline-flex !important;
        }

        /* Pagination links styling */
        .pagination .page-link,
        nav[aria-label="Pagination Navigation"] span a,
        nav[aria-label="Pagination Navigation"] span > span:not(.relative) {
            position: relative;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            padding: 0.375rem 0.75rem;
            font-size: 0.8125rem;
            font-weight: 500;
            line-height: 1.5;
            color: var(--vz-body-color, #212529);
            text-decoration: none;
            background-color: var(--vz-secondary-bg, #fff);
            border: 1px solid var(--vz-border-color, #dee2e6);
            border-radius: 0.25rem;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
            min-width: 36px;
            min-height: 36px;
        }

        /* Hover state */
        .pagination .page-link:hover,
        nav[aria-label="Pagination Navigation"] span a:hover {
            z-index: 2;
            color: var(--vz-link-hover-color, #405189);
            background-color: var(--vz-tertiary-bg, #e9ecef);
            border-color: var(--vz-border-color, #dee2e6);
            text-decoration: none;
        }

        /* Focus state */
        .pagination .page-link:focus,
        nav[aria-label="Pagination Navigation"] span a:focus {
            z-index: 3;
            color: var(--vz-link-hover-color, #405189);
            background-color: var(--vz-tertiary-bg, #e9ecef);
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(64, 81, 137, 0.25);
        }

        /* Active state */
        .pagination .page-item.active .page-link,
        nav[aria-label="Pagination Navigation"] span span[aria-current="page"] span {
            z-index: 3;
            color: #fff !important;
            background-color: #405189 !important;
            border-color: #405189 !important;
        }

        /* Disabled state */
        .pagination .page-item.disabled .page-link,
        nav[aria-label="Pagination Navigation"] span > span.cursor-default,
        nav[aria-label="Pagination Navigation"] span span[aria-disabled="true"] {
            color: var(--vz-secondary-color, #6c757d) !important;
            pointer-events: none;
            background-color: var(--vz-secondary-bg, #fff);
            border-color: var(--vz-border-color, #dee2e6);
            opacity: 0.65;
        }

        /* SVG icons in pagination */
        .pagination svg,
        nav[aria-label="Pagination Navigation"] svg {
            width: 16px !important;
            height: 16px !important;
            fill: currentColor;
        }

        /* Hide default arrow text, show only icons */
        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            font-size: 0.8125rem;
        }

        /* Dark mode adjustments */
        [data-bs-theme="dark"] .pagination .page-link,
        [data-bs-theme="dark"] nav[aria-label="Pagination Navigation"] span a,
        [data-bs-theme="dark"] nav[aria-label="Pagination Navigation"] span > span {
            background-color: var(--vz-input-bg, #212529);
            border-color: var(--vz-border-color, #495057);
            color: var(--vz-body-color, #adb5bd);
        }

        [data-bs-theme="dark"] .pagination .page-link:hover,
        [data-bs-theme="dark"] nav[aria-label="Pagination Navigation"] span a:hover {
            background-color: var(--vz-tertiary-bg, #343a40);
            color: #fff;
        }

        /* Relative wrapper fix for Tailwind pagination */
        nav[aria-label="Pagination Navigation"] .relative.inline-flex {
            display: inline-flex !important;
            gap: 4px;
        }

        /* Text indicators between pages */
        nav[aria-label="Pagination Navigation"] span.relative {
            display: inline-flex !important;
            align-items: center;
        }
    </style>
@endsection

@section('content')
    <x-breadcrumb title="Daftar Log" li_1="Log Sentinel" />

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
