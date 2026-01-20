@extends('layouts.master')

@section('title')
    Tentang Sistem
@endsection

@section('css')
    <style>
        .team-card {
            transition: all 0.3s ease;
            border: none;
        }
        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .team-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            color: white;
            margin: 0 auto 1rem;
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.5rem;
        }
        .tech-badge {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 8px;
            margin: 4px;
            font-weight: 500;
        }
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .status-online { background-color: #4caf50; animation: pulse 2s infinite; }
        .status-offline { background-color: #f44336; }
        .status-error { background-color: #ff9800; }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
@endsection

@section('content')
    <x-breadcrumb title="Tentang Sistem" pagetitle="Log Sentinel" />

    <!-- Hero Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="display-6 fw-bold mb-3">
                                <i class="ri-shield-check-line me-2"></i>
                                Log Sentinel
                            </h1>
                            <h4 class="mb-3">Anomaly Detection System</h4>
                            <p class="lead mb-0 opacity-75">
                                Sistem deteksi anomali berbasis Machine Learning untuk menganalisis log server
                                dan mendeteksi aktivitas mencurigakan secara real-time menggunakan algoritma Isolation Forest.
                            </p>
                        </div>
                        <div class="col-md-4 text-end d-none d-md-block">
                            <i class="ri-radar-line" style="font-size: 150px; opacity: 0.2;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Proyek -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-information-line text-primary me-2"></i>
                        Informasi Proyek
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width: 40%;">Nama Proyek</td>
                                <td class="fw-medium">Log Sentinel: Anomaly Detection System</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Mata Kuliah</td>
                                <td class="fw-medium">Kecerdasan Artifisial</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Jenis Tugas</td>
                                <td class="fw-medium">Ujian Akhir Semester (UAS)</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Algoritma ML</td>
                                <td>
                                    <span class="badge bg-success-subtle text-success">Isolation Forest</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Versi</td>
                                <td class="fw-medium">1.0.0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-server-line text-primary me-2"></i>
                        Status Sistem
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3 p-3 bg-light rounded">
                        <span class="status-indicator status-online"></span>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Laravel Backend</h6>
                            <small class="text-muted">Interface & Data Handler</small>
                        </div>
                        <span class="badge bg-success">Online</span>
                    </div>
                    <div class="d-flex align-items-center p-3 bg-light rounded">
                        @php
                            $statusClass = match($mlServiceStatus['status']) {
                                'online' => 'status-online',
                                'offline' => 'status-offline',
                                default => 'status-error',
                            };
                            $badgeClass = match($mlServiceStatus['status']) {
                                'online' => 'bg-success',
                                'offline' => 'bg-danger',
                                default => 'bg-warning',
                            };
                        @endphp
                        <span class="status-indicator {{ $statusClass }}"></span>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Python ML Service</h6>
                            <small class="text-muted">{{ $mlServiceStatus['message'] }}</small>
                        </div>
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($mlServiceStatus['status']) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Teknologi yang Digunakan -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-code-s-slash-line text-primary me-2"></i>
                        Teknologi yang Digunakan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Backend & Frontend</h6>
                            <div class="d-flex flex-wrap">
                                <span class="tech-badge bg-danger-subtle text-danger">
                                    <i class="ri-laravel-fill me-2"></i> Laravel 11
                                </span>
                                <span class="tech-badge bg-primary-subtle text-primary">
                                    <i class="ri-bootstrap-fill me-2"></i> Bootstrap 5
                                </span>
                                <span class="tech-badge bg-info-subtle text-info">
                                    <i class="ri-database-2-fill me-2"></i> MySQL
                                </span>
                                <span class="tech-badge bg-warning-subtle text-warning">
                                    <i class="ri-javascript-fill me-2"></i> JavaScript
                                </span>
                                <span class="tech-badge bg-success-subtle text-success">
                                    <i class="ri-bar-chart-box-fill me-2"></i> ApexCharts
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Machine Learning</h6>
                            <div class="d-flex flex-wrap">
                                <span class="tech-badge bg-primary-subtle text-primary">
                                    <i class="ri-flask-fill me-2"></i> Flask
                                </span>
                                <span class="tech-badge bg-success-subtle text-success">
                                    <i class="ri-python-fill me-2"></i> Python 3
                                </span>
                                <span class="tech-badge bg-warning-subtle text-warning">
                                    <i class="ri-robot-fill me-2"></i> Scikit-learn
                                </span>
                                <span class="tech-badge bg-info-subtle text-info">
                                    <i class="ri-function-fill me-2"></i> NumPy
                                </span>
                                <span class="tech-badge bg-secondary-subtle text-secondary">
                                    <i class="ri-table-fill me-2"></i> Pandas
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fitur Sistem -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-star-line text-primary me-2"></i>
                        Fitur Utama Sistem
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="feature-icon bg-primary-subtle text-primary me-3">
                                    <i class="ri-radar-line"></i>
                                </div>
                                <div>
                                    <h6>Deteksi Real-time</h6>
                                    <p class="text-muted mb-0">Analisis log server secara real-time dengan respons cepat menggunakan algoritma Isolation Forest</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="feature-icon bg-danger-subtle text-danger me-3">
                                    <i class="ri-alert-line"></i>
                                </div>
                                <div>
                                    <h6>Severity Scoring</h6>
                                    <p class="text-muted mb-0">Penilaian tingkat keparahan ancaman berdasarkan multiple factors analysis</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="feature-icon bg-success-subtle text-success me-3">
                                    <i class="ri-line-chart-line"></i>
                                </div>
                                <div>
                                    <h6>Visualisasi Data</h6>
                                    <p class="text-muted mb-0">Dashboard interaktif dengan chart dan monitoring live traffic</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="feature-icon bg-warning-subtle text-warning me-3">
                                    <i class="ri-bug-line"></i>
                                </div>
                                <div>
                                    <h6>Simulasi Serangan</h6>
                                    <p class="text-muted mb-0">Fitur demonstrasi untuk menguji kemampuan deteksi dengan berbagai pola serangan</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="feature-icon bg-info-subtle text-info me-3">
                                    <i class="ri-api-fill"></i>
                                </div>
                                <div>
                                    <h6>RESTful API</h6>
                                    <p class="text-muted mb-0">Integrasi microservice antara Laravel dan Python melalui REST API</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="feature-icon bg-secondary-subtle text-secondary me-3">
                                    <i class="ri-history-line"></i>
                                </div>
                                <div>
                                    <h6>Log History</h6>
                                    <p class="text-muted mb-0">Penyimpanan dan filter histori log dengan pagination dan search</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tim Pengembang -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-team-line text-primary me-2"></i>
                        Tim Pengembang
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @foreach($teamMembers as $index => $member)
                        @php
                            $colors = ['#3b82f6', '#ef4444', '#22c55e', '#f59e0b'];
                            $initials = collect(explode(' ', $member['name']))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->join('');
                        @endphp
                        <div class="col-lg-3 col-md-6">
                            <div class="card team-card shadow-sm h-100">
                                <div class="card-body text-center p-4">
                                    <div class="team-avatar" style="background: linear-gradient(135deg, {{ $colors[$index] }}, {{ $colors[$index] }}dd);">
                                        {{ $initials }}
                                    </div>
                                    <h5 class="mb-1">{{ $member['name'] }}</h5>
                                    <p class="text-muted mb-2">
                                        <code>{{ $member['nim'] }}</code>
                                    </p>
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ $member['role'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Algoritma Isolation Forest -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-brain-line text-primary me-2"></i>
                        Tentang Algoritma Isolation Forest
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <p>
                                <strong>Isolation Forest</strong> adalah algoritma machine learning unsupervised yang dirancang khusus 
                                untuk mendeteksi anomali (outlier) dalam dataset. Algoritma ini bekerja dengan prinsip bahwa anomali 
                                adalah data yang "berbeda" dari mayoritas data lainnya.
                            </p>
                            <h6 class="mt-4">Cara Kerja:</h6>
                            <ol class="mb-4">
                                <li class="mb-2">
                                    <strong>Isolasi Acak:</strong> Algoritma membangun pohon keputusan dengan memilih fitur dan nilai split secara acak.
                                </li>
                                <li class="mb-2">
                                    <strong>Path Length:</strong> Data anomali cenderung terisolasi lebih cepat (path lebih pendek) karena berbeda dari data mayoritas.
                                </li>
                                <li class="mb-2">
                                    <strong>Ensemble:</strong> Menggunakan banyak pohon (forest) untuk meningkatkan akurasi deteksi.
                                </li>
                                <li class="mb-2">
                                    <strong>Scoring:</strong> Skor anomali dihitung berdasarkan rata-rata path length di semua pohon.
                                </li>
                            </ol>
                            <h6>Keunggulan untuk Deteksi Log:</h6>
                            <ul class="mb-0">
                                <li>Tidak memerlukan data berlabel (unsupervised)</li>
                                <li>Efisien untuk dataset besar</li>
                                <li>Dapat mendeteksi berbagai jenis anomali</li>
                                <li>Cepat dalam training dan prediksi</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light rounded p-4 h-100">
                                <h6 class="text-primary mb-3">
                                    <i class="ri-settings-3-line me-2"></i>
                                    Parameter Model
                                </h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted">n_estimators</td>
                                        <td class="fw-medium">100</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">contamination</td>
                                        <td class="fw-medium">0.1 (10%)</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">max_samples</td>
                                        <td class="fw-medium">auto</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">random_state</td>
                                        <td class="fw-medium">42</td>
                                    </tr>
                                </table>
                                <hr>
                                <h6 class="text-primary mb-3">
                                    <i class="ri-file-list-line me-2"></i>
                                    Fitur yang Dianalisis
                                </h6>
                                <ul class="list-unstyled mb-0">
                                    <li><i class="ri-checkbox-circle-fill text-success me-2"></i>IP Address</li>
                                    <li><i class="ri-checkbox-circle-fill text-success me-2"></i>HTTP Method</li>
                                    <li><i class="ri-checkbox-circle-fill text-success me-2"></i>Status Code</li>
                                    <li><i class="ri-checkbox-circle-fill text-success me-2"></i>Response Time</li>
                                    <li><i class="ri-checkbox-circle-fill text-success me-2"></i>URL Length</li>
                                    <li><i class="ri-checkbox-circle-fill text-success me-2"></i>User Agent</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
