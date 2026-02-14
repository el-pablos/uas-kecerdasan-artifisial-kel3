@extends('layouts.master-without-nav')
@section('title', 'Setup Required')

@section('body')
<body class="auth-page-wrapper" data-bs-theme="dark">
@endsection

@section('content')
<div class="auth-page-content" style="min-height:100vh; display:flex; align-items:center;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">

                {{-- Header --}}
                <div class="text-center mb-4">
                    <i class="ri-shield-flash-line text-info" style="font-size:3rem;"></i>
                    <h2 class="text-white mt-2">Log Sentinel CTI — Setup Required</h2>
                    <p class="text-muted">
                        @if($allReady)
                            Semua tabel sudah siap. Klik tombol di bawah untuk masuk.
                        @else
                            Database belum siap. Ikuti langkah di bawah supaya aplikasi bisa dijalankan.
                        @endif
                    </p>
                </div>

                {{-- Connection Status --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h6 class="text-uppercase text-muted mb-3">
                            <i class="ri-database-2-line me-1"></i> Koneksi Database
                        </h6>

                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted" style="width:140px;">Driver</td>
                                <td><code>{{ $dbDriver }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Database</td>
                                <td><code>{{ $dbName ?? '—' }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status</td>
                                <td>
                                    @if($canConnect)
                                        <span class="badge bg-success"><i class="ri-check-line"></i> Connected</span>
                                    @else
                                        <span class="badge bg-danger"><i class="ri-close-line"></i> Cannot Connect</span>
                                        <small class="text-danger d-block mt-1">Cek konfigurasi DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD di file <code>.env</code></small>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Table Checklist --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h6 class="text-uppercase text-muted mb-3">
                            <i class="ri-table-line me-1"></i>
                            Tabel Wajib CTI
                            @if($allReady)
                                <span class="badge bg-success ms-2">All OK</span>
                            @else
                                <span class="badge bg-danger ms-2">{{ $missingCount }} Missing</span>
                            @endif
                        </h6>

                        <div class="row g-2">
                            @foreach($tableStatus as $name => $info)
                                <div class="col-6 col-md-4">
                                    <div class="d-flex align-items-center gap-2 py-1">
                                        @if($info['exists'])
                                            <i class="ri-checkbox-circle-fill text-success"></i>
                                            <span class="text-white">{{ $name }}</span>
                                            <small class="text-muted">({{ $info['rows'] }})</small>
                                        @else
                                            <i class="ri-close-circle-fill text-danger"></i>
                                            <span class="text-danger">{{ $name }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Fix Instructions --}}
                @if(!$allReady)
                <div class="card border-0 shadow-sm mb-3 border-start border-warning border-3">
                    <div class="card-body">
                        <h6 class="text-uppercase text-warning mb-3">
                            <i class="ri-tools-line me-1"></i> Cara Fix
                        </h6>

                        <p class="text-muted mb-2">Jalankan perintah ini di terminal (dari root project):</p>

                        <div class="bg-dark rounded p-3 mb-2" style="font-family:monospace; font-size:.85rem;">
                            <div class="text-success mb-1"># 1. Clear cache dulu</div>
                            <div class="text-white mb-2">php artisan optimize:clear</div>

                            <div class="text-success mb-1"># 2. Migrate + seed</div>
                            <div class="text-white mb-2">php artisan migrate --seed</div>

                            <div class="text-success mb-1"># Atau pakai command setup sekali jalan:</div>
                            <div class="text-white mb-2">php artisan app:setup</div>

                            <div class="text-success mb-1"># Atau pakai doctor auto-fix:</div>
                            <div class="text-white">php artisan sentinel:doctor --fix</div>
                        </div>

                        <div class="alert alert-danger mb-0 py-2" role="alert">
                            <i class="ri-error-warning-line me-1"></i>
                            <strong>⚠️ Hanya untuk local dev:</strong>
                            <code>php artisan migrate:fresh --seed</code> akan <strong>drop semua tabel</strong>.
                            Jangan pakai di production.
                        </div>
                    </div>
                </div>

                {{-- Optional demo seeder --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h6 class="text-uppercase text-muted mb-3">
                            <i class="ri-file-list-3-line me-1"></i> Opsional: Demo Data
                        </h6>
                        <p class="text-muted mb-2">Kalau mau data contoh CTI (threat actors, malware, dll):</p>
                        <div class="bg-dark rounded p-3" style="font-family:monospace; font-size:.85rem;">
                            <div class="text-white">php artisan db:seed --class=CtiDemoSeeder</div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Action Buttons --}}
                <div class="text-center mt-4">
                    @if($allReady)
                        <a href="{{ route('cti.dashboard') }}" class="btn btn-success btn-lg">
                            <i class="ri-arrow-right-line me-1"></i> Masuk CTI Dashboard
                        </a>
                    @else
                        <a href="{{ url('/setup') }}" class="btn btn-soft-info btn-lg">
                            <i class="ri-refresh-line me-1"></i> Refresh Halaman Ini
                        </a>
                    @endif
                    <a href="{{ route('sentinel.dashboard') }}" class="btn btn-soft-secondary btn-lg ms-2">
                        Log Sentinel
                    </a>
                </div>

                <p class="text-center text-muted mt-4 small">
                    Log Sentinel v3.0 — Threat Intelligence Platform
                </p>

            </div>
        </div>
    </div>
</div>
@endsection
