@extends('layouts.master-cti')
@section('title', 'Diagnostics')
@section('content')

<div class="row mb-3">
    <div class="col-12">
        <h4 class="mb-1" style="color: var(--cti-text);"><i class="ri-stethoscope-line text-warning me-2"></i>System Diagnostics</h4>
        <p class="text-muted mb-0" style="font-size: 13px;">Quick health check — helps debug "why is UI not changing?"</p>
    </div>
</div>

{{-- Current State --}}
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card mb-0">
            <div class="card-header"><h6 class="mb-0"><i class="ri-route-line text-info me-2"></i>Current Route Info</h6></div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted" style="width:40%;">Current URL</td><td><code>{{ request()->url() }}</code></td></tr>
                    <tr><td class="text-muted">Route Name</td><td><code>{{ request()->route()?->getName() ?? '(unnamed)' }}</code></td></tr>
                    <tr><td class="text-muted">Layout</td><td><span class="badge bg-info-subtle text-info">master-cti</span> (CTI layout)</td></tr>
                    <tr><td class="text-muted">HOME constant</td><td><code>{{ App\Providers\RouteServiceProvider::HOME }}</code></td></tr>
                    <tr><td class="text-muted">Root / redirects to</td><td><code>/cti</code> → CTI Dashboard</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card mb-0">
            <div class="card-header"><h6 class="mb-0"><i class="ri-database-2-line text-success me-2"></i>Database Status</h6></div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    @foreach($tables as $table => $info)
                    <tr>
                        <td class="text-muted" style="width:40%;">{{ $table }}</td>
                        <td>
                            @if($info['exists'])
                                <span class="badge bg-success-subtle text-success">OK</span>
                                <small class="text-muted ms-2">{{ $info['count'] }} rows</small>
                            @else
                                <span class="badge bg-danger-subtle text-danger">MISSING</span>
                                <small class="text-muted ms-2">Run <code>php artisan migrate</code></small>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Quick Navigation --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card mb-0">
            <div class="card-header"><h6 class="mb-0"><i class="ri-links-line text-primary me-2"></i>Quick Navigation — All CTI Modules</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($links as $section => $items)
                    <div class="col-md-4">
                        <h6 class="text-uppercase mb-2" style="font-size: 11px; color: var(--cti-accent); letter-spacing: 1px;">{{ $section }}</h6>
                        @foreach($items as $item)
                        <a href="{{ $item['url'] }}" class="d-block py-1" style="font-size: 13px; color: var(--cti-text); text-decoration: none;">
                            <i class="{{ $item['icon'] }} me-2" style="color: var(--cti-text-muted);"></i>{{ $item['label'] }}
                            <span class="badge bg-secondary-subtle text-secondary ms-2" style="font-size: 9px;">{{ $item['route'] }}</span>
                        </a>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Cache/Config --}}
<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card mb-0">
            <div class="card-header"><h6 class="mb-0"><i class="ri-settings-4-line text-warning me-2"></i>Cache & Config</h6></div>
            <div class="card-body">
                <p class="text-muted mb-2" style="font-size: 13px;">If UI doesn't update, try clearing caches:</p>
                <code class="d-block mb-1">php artisan optimize:clear</code>
                <code class="d-block mb-1">php artisan view:clear</code>
                <code class="d-block mb-1">php artisan route:clear</code>
                <code class="d-block mb-3">php artisan sentinel:doctor</code>
                <div class="d-flex gap-2">
                    <span class="badge bg-{{ $configCached ? 'warning' : 'success' }}-subtle text-{{ $configCached ? 'warning' : 'success' }}">
                        Config cache: {{ $configCached ? 'CACHED' : 'Not cached' }}
                    </span>
                    <span class="badge bg-{{ $routesCached ? 'warning' : 'success' }}-subtle text-{{ $routesCached ? 'warning' : 'success' }}">
                        Routes cache: {{ $routesCached ? 'CACHED' : 'Not cached' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card mb-0">
            <div class="card-header"><h6 class="mb-0"><i class="ri-error-warning-line text-danger me-2"></i>Common Issues</h6></div>
            <div class="card-body">
                <div class="mb-2" style="font-size: 13px;">
                    <strong>Q: "UI masih sama / ga berubah?"</strong><br>
                    A: Pastikan buka <code>/cti</code> (bukan <code>/dashboard</code> lama). Cek sidebar harus ada menu Threats, Knowledge, Cases.
                </div>
                <div class="mb-2" style="font-size: 13px;">
                    <strong>Q: "Login masuk dashboard lama?"</strong><br>
                    A: HOME constant harus <code>/cti</code>. Clear route cache.
                </div>
                <div style="font-size: 13px;">
                    <strong>Q: "Data kosong?"</strong><br>
                    A: Run <code>php artisan migrate:fresh --seed</code> atau import STIX via <a href="{{ route('ingestion.import') }}" style="color: var(--cti-accent);">Data > Import</a>.
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
