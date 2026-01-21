@extends('layouts.master')

@section('title')
    Dashboard
@endsection

@section('css')
    <style>
        /* Custom CSS untuk Log Sentinel Dashboard */
        .threat-card {
            transition: all 0.3s ease;
        }
        .threat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .log-row-anomaly {
            background-color: rgba(244, 67, 54, 0.1) !important;
            border-left: 4px solid #f44336;
        }
        .log-row-normal {
            border-left: 4px solid #4caf50;
        }
        .pulse-danger {
            animation: pulse-danger 2s infinite;
        }
        @keyframes pulse-danger {
            0% { box-shadow: 0 0 0 0 rgba(244, 67, 54, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(244, 67, 54, 0); }
            100% { box-shadow: 0 0 0 0 rgba(244, 67, 54, 0); }
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }
        .live-indicator {
            width: 10px;
            height: 10px;
            background-color: #4caf50;
            border-radius: 50%;
            display: inline-block;
            animation: blink 1s infinite;
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        .severity-bar {
            height: 6px;
            border-radius: 3px;
            background-color: #e0e0e0;
        }
        .severity-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.5s ease;
        }
        /* System Status Pulse Indicator */
        .system-status-badge {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            padding: 8px 16px;
            border-radius: 25px;
            border: 1px solid #0f3460;
            box-shadow: 0 4px 15px rgba(0, 255, 136, 0.2);
        }
        .system-status-text {
            color: #00ff88;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-right: 10px;
        }
        .pulse-dot {
            width: 12px;
            height: 12px;
            background-color: #00ff88;
            border-radius: 50%;
            animation: pulse-glow 1.5s ease-in-out infinite;
        }
        @keyframes pulse-glow {
            0% {
                box-shadow: 0 0 0 0 rgba(0, 255, 136, 0.7);
                transform: scale(1);
            }
            50% {
                box-shadow: 0 0 0 10px rgba(0, 255, 136, 0);
                transform: scale(1.1);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(0, 255, 136, 0);
                transform: scale(1);
            }
        }
        /* Cyber Map Styling */
        #cyber-map {
            background: #0a0a0a;
            border-radius: 10px;
            border: 1px solid #1a1a2e;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        .attack-marker {
            animation: marker-pulse 0.5s ease-out;
        }
        @keyframes marker-pulse {
            0% {
                opacity: 0;
                transform: scale(0.5);
            }
            50% {
                opacity: 1;
                transform: scale(1.2);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }
        .leaflet-popup-content-wrapper {
            background: rgba(26, 26, 46, 0.95);
            color: #ff4757;
            border: 1px solid #ff4757;
            border-radius: 8px;
        }
        .leaflet-popup-tip {
            background: rgba(26, 26, 46, 0.95);
        }
        .leaflet-popup-content {
            margin: 10px 15px;
            font-size: 12px;
        }
    </style>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@endsection

@section('content')
    <x-breadcrumb title="Dashboard" li_1="Log Sentinel" />

    <!-- Header dengan Tombol Simulasi -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">
                        <i class="ri-shield-check-line text-primary me-2"></i>
                        Log Sentinel Dashboard
                    </h4>
                    <p class="text-muted mb-0">
                        <span class="live-indicator me-2"></span>
                        Sistem Deteksi Anomali Real-time
                    </p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <!-- System Status Pulse Indicator -->
                    <div class="system-status-badge me-3">
                        <span class="system-status-text">SYSTEM MONITORING: ACTIVE</span>
                        <span class="pulse-dot"></span>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-danger dropdown-toggle" type="button" id="simulateDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ri-bug-line me-1"></i> Simulasi Serangan
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="simulateDropdown">
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="simulateAttack('ddos')">
                                <i class="ri-flashlight-line me-2"></i>DDoS Attack
                            </a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="simulateAttack('bruteforce')">
                                <i class="ri-key-2-line me-2"></i>Brute Force Login
                            </a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="simulateAttack('sql_injection')">
                                <i class="ri-database-2-line me-2"></i>SQL Injection
                            </a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="simulateAttack('path_traversal')">
                                <i class="ri-folder-transfer-line me-2"></i>Path Traversal
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="simulateAttack('random')">
                                <i class="ri-shuffle-line me-2"></i>Random Mixed Attack
                            </a></li>
                        </ul>
                    </div>
                    <button class="btn btn-soft-primary" onclick="refreshDashboard()">
                        <i class="ri-refresh-line me-1"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="row">
        <!-- Total Requests -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate threat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-medium text-muted mb-0">Total Request</p>
                            <h3 class="mb-0" id="totalLogs">{{ number_format($totalLogs) }}</h3>
                        </div>
                        <div class="stat-icon bg-primary-subtle">
                            <i class="ri-global-line text-primary fs-2"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-soft-success text-success">
                            <i class="ri-arrow-up-line"></i> Aktif
                        </span>
                        <span class="text-muted ms-2 fs-12">Log tercatat</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Threats -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate threat-card {{ $totalAnomalies > 0 ? 'pulse-danger' : '' }}">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-medium text-muted mb-0">Total Ancaman</p>
                            <h3 class="mb-0 {{ $totalAnomalies > 0 ? 'text-danger' : 'text-success' }}" id="totalAnomalies">
                                {{ number_format($totalAnomalies) }}
                            </h3>
                        </div>
                        <div class="stat-icon {{ $totalAnomalies > 0 ? 'bg-danger-subtle' : 'bg-success-subtle' }}">
                            <i class="ri-alert-line {{ $totalAnomalies > 0 ? 'text-danger' : 'text-success' }} fs-2"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-soft-danger text-danger" id="threatBadge">
                            {{ $threatPercentage }}%
                        </span>
                        <span class="text-muted ms-2 fs-12">Persentase ancaman</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Normal Traffic -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate threat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-medium text-muted mb-0">Traffic Normal</p>
                            <h3 class="mb-0 text-success" id="totalNormal">{{ number_format($totalNormal) }}</h3>
                        </div>
                        <div class="stat-icon bg-success-subtle">
                            <i class="ri-check-double-line text-success fs-2"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-soft-success text-success">
                            <i class="ri-shield-check-line"></i> Aman
                        </span>
                        <span class="text-muted ms-2 fs-12">Request terverifikasi</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- High Severity -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate threat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-medium text-muted mb-0">Severity Tinggi</p>
                            <h3 class="mb-0 {{ $highSeverityCount > 0 ? 'text-warning' : 'text-success' }}" id="highSeverity">
                                {{ number_format($highSeverityCount) }}
                            </h3>
                        </div>
                        <div class="stat-icon {{ $highSeverityCount > 0 ? 'bg-warning-subtle' : 'bg-success-subtle' }}">
                            <i class="ri-fire-line {{ $highSeverityCount > 0 ? 'text-warning' : 'text-success' }} fs-2"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-soft-warning text-warning">
                            <i class="ri-alarm-warning-line"></i> Perhatian
                        </span>
                        <span class="text-muted ms-2 fs-12">Skor > 70</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart dan Live Monitoring -->
    <div class="row">
        <!-- Traffic Chart -->
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header border-0 align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">
                        <i class="ri-line-chart-line me-2 text-primary"></i>
                        Traffic Monitoring (24 Jam Terakhir)
                    </h4>
                    <div class="flex-shrink-0">
                        <button type="button" class="btn btn-soft-primary btn-sm" onclick="refreshChart()">
                            <i class="ri-refresh-line"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div id="trafficChart" style="height: 350px;"></div>
                </div>
            </div>
        </div>

        <!-- Threat Distribution -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header border-0 align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">
                        <i class="ri-pie-chart-line me-2 text-primary"></i>
                        Distribusi Traffic
                    </h4>
                </div>
                <div class="card-body pt-0">
                    <div id="distributionChart" style="height: 250px;"></div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="ri-checkbox-blank-circle-fill text-success me-1"></i> Normal</span>
                            <span class="fw-bold" id="normalPercent">{{ $totalLogs > 0 ? round(($totalNormal / $totalLogs) * 100, 1) : 0 }}%</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><i class="ri-checkbox-blank-circle-fill text-danger me-1"></i> Anomali</span>
                            <span class="fw-bold" id="anomalyPercent">{{ $threatPercentage }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PCA Visualization: Anomaly Distribution Map -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">
                        <i class="ri-scatter-chart-line me-2 text-primary"></i>
                        Anomaly Distribution Map
                        <span class="badge bg-info-subtle text-info ms-2">PCA Visualization</span>
                    </h4>
                    <div class="flex-shrink-0">
                        <button type="button" class="btn btn-soft-primary btn-sm" onclick="refreshPcaVisualization()">
                            <i class="ri-refresh-line me-1"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <div id="pcaVisualizationContainer" class="text-center">
                                @if($pcaVisualization['available'] && $pcaVisualization['image_base64'])
                                    <img src="data:image/png;base64,{{ $pcaVisualization['image_base64'] }}" 
                                         alt="PCA Scatter Plot" 
                                         class="img-fluid rounded shadow-sm"
                                         style="max-height: 500px;">
                                @else
                                    <div class="py-5">
                                        <i class="ri-scatter-chart-line text-muted" style="font-size: 4rem;"></i>
                                        <p class="text-muted mt-3">{{ $pcaVisualization['message'] ?? 'Visualisasi tidak tersedia' }}</p>
                                        <small class="text-muted">Pastikan ML Service aktif dan terdapat data log untuk divisualisasikan.</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted mb-3">
                                    <i class="ri-information-line me-1"></i> Tentang Visualisasi
                                </h6>
                                <p class="small text-muted mb-3">
                                    Grafik ini menampilkan distribusi data log server menggunakan 
                                    <strong>Principal Component Analysis (PCA)</strong> untuk mereduksi 
                                    6 fitur menjadi 2 dimensi yang dapat divisualisasikan.
                                </p>
                                
                                @if($pcaVisualization['statistics'])
                                    <div class="border-top pt-3">
                                        <h6 class="text-muted mb-2">Statistik Data</h6>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="small">Total Data Points:</span>
                                            <span class="fw-bold">{{ $pcaVisualization['statistics']['total_points'] ?? 0 }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="small"><i class="ri-checkbox-blank-circle-fill text-primary me-1"></i>Normal:</span>
                                            <span class="fw-bold text-primary">{{ $pcaVisualization['statistics']['normal_count'] ?? 0 }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="small"><i class="ri-close-circle-fill text-danger me-1"></i>Anomaly:</span>
                                            <span class="fw-bold text-danger">{{ $pcaVisualization['statistics']['anomaly_count'] ?? 0 }}</span>
                                        </div>
                                        @if(isset($pcaVisualization['statistics']['variance_explained']))
                                            <div class="border-top pt-2 mt-2">
                                                <h6 class="text-muted mb-2 small">Variance Explained</h6>
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span class="small">PC1:</span>
                                                    <span class="small fw-bold">{{ $pcaVisualization['statistics']['variance_explained']['pc1'] }}%</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span class="small">PC2:</span>
                                                    <span class="small fw-bold">{{ $pcaVisualization['statistics']['variance_explained']['pc2'] }}%</span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span class="small">Total:</span>
                                                    <span class="small fw-bold text-success">{{ $pcaVisualization['statistics']['variance_explained']['total'] }}%</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <div class="border-top pt-3 mt-3">
                                    <h6 class="text-muted mb-2">Legenda</h6>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-primary me-2" style="width: 12px; height: 12px; border-radius: 50%;"></span>
                                        <span class="small">Data Normal (Traffic Aman)</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-danger me-2" style="width: 12px; height: 12px;"></span>
                                        <span class="small">Data Anomali (Potensi Ancaman)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Cyber Threat Map -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">
                        <span class="live-indicator me-2"></span>
                        <i class="ri-earth-line me-2 text-danger"></i>Live Cyber Threat Map
                    </h4>
                    <div class="flex-shrink-0">
                        <span class="badge bg-danger-subtle text-danger px-3 py-2">
                            <i class="ri-fire-line me-1"></i>SIMULATED ATTACKS
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div id="cyber-map" style="height: 400px; border-radius: 8px; border: 2px solid rgba(239, 68, 68, 0.3);"></div>
                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-3">
                            <div class="d-flex align-items-center">
                                <span class="pulse-dot me-2"></span>
                                <span class="small text-muted">Active Attack Point</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-warning me-2" style="width: 12px; height: 12px; border-radius: 50%;"></span>
                                <span class="small text-muted">DDoS Attack</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-danger me-2" style="width: 12px; height: 12px; border-radius: 50%;"></span>
                                <span class="small text-muted">Intrusion Attempt</span>
                            </div>
                        </div>
                        <span class="small text-muted" id="attack-count">Attacks detected: <span class="fw-bold text-danger">0</span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Monitoring Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">
                        <span class="live-indicator me-2"></span>
                        <i class="ri-radar-line me-2 text-primary"></i>
                        Live Monitoring
                    </h4>
                    <div class="flex-shrink-0">
                        <span class="text-muted me-3" id="lastUpdate">Terakhir diperbarui: {{ now()->format('H:i:s') }}</span>
                        <a href="{{ route('sentinel.logs') }}" class="btn btn-soft-primary btn-sm">
                            Lihat Semua <i class="ri-arrow-right-line ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="liveLogTable">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width: 50px;">#</th>
                                    <th scope="col">Waktu</th>
                                    <th scope="col">IP Address</th>
                                    <th scope="col">Method</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Response Time</th>
                                    <th scope="col">Prediksi</th>
                                    <th scope="col">Severity</th>
                                </tr>
                            </thead>
                            <tbody id="logTableBody">
                                @forelse($recentLogs as $log)
                                <tr class="{{ $log->isAnomaly() ? 'log-row-anomaly' : 'log-row-normal' }}">
                                    <td>
                                        <span class="badge {{ $log->badge_class }}">
                                            {{ $log->id }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $log->created_at->format('H:i:s') }}</small>
                                    </td>
                                    <td>
                                        <code>{{ $log->ip_address }}</code>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info">{{ $log->method }}</span>
                                    </td>
                                    <td>
                                        <span title="{{ $log->url }}">{{ Str::limit($log->url, 40) }}</span>
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
                                        <span class="{{ $log->response_time > 1000 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($log->response_time, 0) }} ms
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
                                            <span class="me-2">{{ number_format($log->severity_score, 0) }}</span>
                                            <div class="severity-bar flex-grow-1" style="width: 60px;">
                                                @php
                                                    $severityColor = match(true) {
                                                        $log->severity_score >= 80 => '#f44336',
                                                        $log->severity_score >= 60 => '#ff9800',
                                                        $log->severity_score >= 40 => '#ffc107',
                                                        default => '#4caf50',
                                                    };
                                                @endphp
                                                <div class="severity-fill" style="width: {{ $log->severity_score }}%; background-color: {{ $severityColor }};"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ri-inbox-line fs-1 d-block mb-2"></i>
                                            Belum ada data log
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <!-- ApexCharts -->
    <script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>

    <script>
        // Data chart dari server
        var chartLabels = @json($chartData['labels']);
        var chartNormal = @json($chartData['normal']);
        var chartAnomaly = @json($chartData['anomaly']);

        // Inisialisasi Traffic Chart
        var trafficChartOptions = {
            series: [{
                name: 'Normal',
                type: 'area',
                data: chartNormal
            }, {
                name: 'Anomali',
                type: 'area',
                data: chartAnomaly
            }],
            chart: {
                height: 350,
                type: 'area',
                toolbar: {
                    show: false
                },
                animations: {
                    enabled: true
                }
            },
            colors: ['#4caf50', '#f44336'],
            stroke: {
                curve: 'smooth',
                width: 2
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.1,
                    stops: [0, 90, 100]
                }
            },
            xaxis: {
                categories: chartLabels,
                labels: {
                    rotate: -45,
                    style: {
                        fontSize: '10px'
                    }
                }
            },
            yaxis: {
                title: {
                    text: 'Jumlah Request'
                }
            },
            tooltip: {
                shared: true,
                intersect: false
            },
            legend: {
                position: 'top'
            }
        };

        var trafficChart = new ApexCharts(document.querySelector("#trafficChart"), trafficChartOptions);
        trafficChart.render();

        // Inisialisasi Distribution Chart (Donut)
        var totalNormal = {{ $totalNormal }};
        var totalAnomaly = {{ $totalAnomalies }};

        var distributionChartOptions = {
            series: [totalNormal, totalAnomaly],
            chart: {
                type: 'donut',
                height: 250
            },
            colors: ['#4caf50', '#f44336'],
            labels: ['Normal', 'Anomali'],
            legend: {
                show: false
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                }
                            }
                        }
                    }
                }
            }
        };

        var distributionChart = new ApexCharts(document.querySelector("#distributionChart"), distributionChartOptions);
        distributionChart.render();

        // Fungsi Simulasi Serangan
        function simulateAttack(attackType) {
            Swal.fire({
                title: 'Simulasi Serangan',
                text: 'Apakah Anda yakin ingin menjalankan simulasi ' + attackType.toUpperCase() + '?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Jalankan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tampilkan loading
                    Swal.fire({
                        title: 'Menjalankan Simulasi...',
                        html: 'Generating attack patterns...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Kirim request simulasi
                    fetch('{{ route("api.simulate") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            attack_type: attackType,
                            count: 10
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.fire({
                            title: 'Simulasi Selesai!',
                            html: `<strong>${data.total_generated}</strong> log serangan berhasil digenerate.<br>Silakan cek tabel monitoring.`,
                            icon: 'success',
                            timer: 3000
                        });

                        // Refresh dashboard
                        refreshDashboard();
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Gagal menjalankan simulasi: ' + error.message,
                            icon: 'error'
                        });
                    });
                }
            });
        }

        // Fungsi Refresh Dashboard
        function refreshDashboard() {
            location.reload();
        }

        // Fungsi Refresh PCA Visualization
        function refreshPcaVisualization() {
            var container = document.getElementById('pcaVisualizationContainer');
            container.innerHTML = '<div class="py-5 text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-3 text-muted">Memuat visualisasi PCA...</p></div>';
            
            // Reload page untuk mendapatkan visualisasi terbaru
            // Karena visualisasi digenerate di server-side
            setTimeout(function() {
                location.reload();
            }, 500);
        }

        // Fungsi Refresh Chart
        function refreshChart() {
            fetch('{{ route("api.chart-data") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        trafficChart.updateOptions({
                            xaxis: {
                                categories: data.data.labels
                            }
                        });
                        trafficChart.updateSeries([
                            { name: 'Normal', data: data.data.normal },
                            { name: 'Anomali', data: data.data.anomaly }
                        ]);
                    }
                });
        }

        // Auto-refresh setiap 30 detik
        setInterval(function() {
            // Update statistik
            fetch('{{ route("api.stats") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('totalLogs').textContent = data.data.total_logs.toLocaleString();
                        document.getElementById('totalAnomalies').textContent = data.data.total_anomalies.toLocaleString();
                        document.getElementById('totalNormal').textContent = data.data.total_normal.toLocaleString();
                        document.getElementById('threatBadge').textContent = data.data.threat_percentage + '%';
                        document.getElementById('lastUpdate').textContent = 'Terakhir diperbarui: ' + new Date().toLocaleTimeString();
                    }
                });
        }, 30000);
    </script>

    <!-- Leaflet.js Library -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Initialize Cyber Threat Map
        const cyberMap = L.map('cyber-map', {
            zoomControl: true,
            attributionControl: false
        }).setView([20, 0], 2);

        // Dark themed map tiles
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            maxZoom: 19,
            subdomains: 'abcd'
        }).addTo(cyberMap);

        // Attack simulation data
        const attackTypes = [
            { name: 'DDoS Attack', color: '#f59e0b', icon: '🔥' },
            { name: 'SQL Injection', color: '#ef4444', icon: '💉' },
            { name: 'Brute Force', color: '#f97316', icon: '🔨' },
            { name: 'Malware Upload', color: '#dc2626', icon: '🦠' },
            { name: 'XSS Attack', color: '#ea580c', icon: '⚡' },
            { name: 'Port Scan', color: '#fbbf24', icon: '🔍' }
        ];

        const targetCities = [
            { name: 'Jakarta', lat: -6.2088, lng: 106.8456 },
            { name: 'Singapore', lat: 1.3521, lng: 103.8198 },
            { name: 'Tokyo', lat: 35.6762, lng: 139.6503 },
            { name: 'New York', lat: 40.7128, lng: -74.0060 },
            { name: 'London', lat: 51.5074, lng: -0.1278 },
            { name: 'Sydney', lat: -33.8688, lng: 151.2093 },
            { name: 'Mumbai', lat: 19.0760, lng: 72.8777 },
            { name: 'São Paulo', lat: -23.5505, lng: -46.6333 },
            { name: 'Moscow', lat: 55.7558, lng: 37.6173 },
            { name: 'Dubai', lat: 25.2048, lng: 55.2708 },
            { name: 'Seoul', lat: 37.5665, lng: 126.9780 },
            { name: 'Frankfurt', lat: 50.1109, lng: 8.6821 }
        ];

        let attackCount = 0;

        function simulateAttack() {
            // Random city and attack type
            const city = targetCities[Math.floor(Math.random() * targetCities.length)];
            const attack = attackTypes[Math.floor(Math.random() * attackTypes.length)];
            
            // Add some randomness to coordinates
            const lat = city.lat + (Math.random() - 0.5) * 2;
            const lng = city.lng + (Math.random() - 0.5) * 2;

            // Create pulsing marker
            const marker = L.circleMarker([lat, lng], {
                radius: 8,
                fillColor: attack.color,
                color: attack.color,
                weight: 2,
                opacity: 1,
                fillOpacity: 0.8,
                className: 'attack-marker'
            }).addTo(cyberMap);

            // Add popup
            marker.bindPopup(`
                <div style="text-align: center; min-width: 150px;">
                    <div style="font-size: 24px;">${attack.icon}</div>
                    <strong style="color: ${attack.color};">${attack.name}</strong><br>
                    <small>Target: ${city.name}</small><br>
                    <small style="color: #888;">${new Date().toLocaleTimeString()}</small>
                </div>
            `).openPopup();

            // Increment attack counter
            attackCount++;
            document.querySelector('#attack-count .fw-bold').textContent = attackCount;

            // Remove marker after 3 seconds
            setTimeout(() => {
                cyberMap.removeLayer(marker);
            }, 3000);
        }

        // Start attack simulation every 1.5 seconds
        setInterval(simulateAttack, 1500);

        // Initial attack
        setTimeout(simulateAttack, 500);
    </script>

    <!-- SweetAlert2 -->
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />

    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
