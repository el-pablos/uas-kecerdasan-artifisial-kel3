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
    </style>
@endsection

@section('content')
    <x-breadcrumb title="Dashboard" pagetitle="Log Sentinel" />

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
                <div class="d-flex gap-2">
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

    <!-- SweetAlert2 -->
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />

    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
