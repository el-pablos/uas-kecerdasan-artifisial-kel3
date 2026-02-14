@extends('layouts.master-cti')
@section('title', 'CTI Dashboard')
@section('content')

{{-- Page Header --}}
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1" style="color: var(--cti-text);">
                    <i class="ri-shield-flash-line text-info me-2"></i>Threat Intelligence Dashboard
                </h4>
                <p class="text-muted mb-0" style="font-size: 13px;">Real-time overview of your threat landscape</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('threats.actors.create') }}" class="btn btn-cti-primary btn-sm">
                    <i class="ri-add-line me-1"></i> New Threat Actor
                </a>
                <a href="{{ route('knowledge.graph') }}" class="btn btn-cti-outline btn-sm">
                    <i class="ri-mind-map me-1"></i> Graph Explorer
                </a>
                <a href="{{ route('ingestion.import') }}" class="btn btn-cti-outline btn-sm">
                    <i class="ri-upload-2-line me-1"></i> Import STIX
                </a>
            </div>
        </div>
    </div>
</div>

{{-- KPI Row --}}
<div class="row mb-4">
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="cti-stat-card text-center">
            <div class="cti-stat-number" style="color: var(--cti-red);">{{ $counts['threat-actor'] ?? 0 }}</div>
            <div class="cti-stat-label">Threat Actors</div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="cti-stat-card text-center">
            <div class="cti-stat-number" style="color: var(--cti-orange);">{{ $counts['malware'] ?? 0 }}</div>
            <div class="cti-stat-label">Malware</div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="cti-stat-card text-center">
            <div class="cti-stat-number" style="color: #eab308;">{{ $counts['campaign'] ?? 0 }}</div>
            <div class="cti-stat-label">Campaigns</div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="cti-stat-card text-center">
            <div class="cti-stat-number" style="color: var(--cti-purple);">{{ $counts['intrusion-set'] ?? 0 }}</div>
            <div class="cti-stat-label">Intrusion Sets</div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="cti-stat-card text-center">
            <div class="cti-stat-number" style="color: var(--cti-cyan);">{{ $counts['vulnerability'] ?? 0 }}</div>
            <div class="cti-stat-label">Vulnerabilities</div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="cti-stat-card text-center">
            <div class="cti-stat-number" style="color: var(--cti-green);">{{ $totalCases }}</div>
            <div class="cti-stat-label">Active Cases</div>
        </div>
    </div>
</div>

{{-- Summary Row --}}
<div class="row mb-4">
    <div class="col-md-3 col-6 mb-3">
        <div class="card mb-0">
            <div class="card-body d-flex align-items-center py-3">
                <div class="flex-shrink-0 me-3">
                    <div class="avatar-sm">
                        <span class="avatar-title rounded-circle" style="background: rgba(239,68,68,0.15); color: #ef4444;">
                            <i class="ri-skull-2-line fs-4"></i>
                        </span>
                    </div>
                </div>
                <div>
                    <h5 class="mb-0" style="color: var(--cti-text);">{{ $totalThreats }}</h5>
                    <span class="text-muted" style="font-size: 12px;">Total Threats</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card mb-0">
            <div class="card-body d-flex align-items-center py-3">
                <div class="flex-shrink-0 me-3">
                    <div class="avatar-sm">
                        <span class="avatar-title rounded-circle" style="background: rgba(99,102,241,0.15); color: #6366f1;">
                            <i class="ri-node-tree fs-4"></i>
                        </span>
                    </div>
                </div>
                <div>
                    <h5 class="mb-0" style="color: var(--cti-text);">{{ $totalEntities }}</h5>
                    <span class="text-muted" style="font-size: 12px;">Entities</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card mb-0">
            <div class="card-body d-flex align-items-center py-3">
                <div class="flex-shrink-0 me-3">
                    <div class="avatar-sm">
                        <span class="avatar-title rounded-circle" style="background: rgba(59,130,246,0.15); color: #3b82f6;">
                            <i class="ri-link fs-4"></i>
                        </span>
                    </div>
                </div>
                <div>
                    <h5 class="mb-0" style="color: var(--cti-text);">{{ $totalRelationships }}</h5>
                    <span class="text-muted" style="font-size: 12px;">Relationships</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card mb-0">
            <div class="card-body d-flex align-items-center py-3">
                <div class="flex-shrink-0 me-3">
                    <div class="avatar-sm">
                        <span class="avatar-title rounded-circle" style="background: rgba(16,185,129,0.15); color: #10b981;">
                            <i class="ri-folder-shield-2-line fs-4"></i>
                        </span>
                    </div>
                </div>
                <div>
                    <h5 class="mb-0" style="color: var(--cti-text);">{{ $totalCases }}</h5>
                    <span class="text-muted" style="font-size: 12px;">Cases</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Recent Threats --}}
    <div class="col-xl-8 mb-4">
        <div class="card mb-0">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="ri-skull-line text-danger me-2"></i>Recent Threats</h6>
                <a href="{{ route('threats.actors.index') }}" class="btn btn-sm btn-cti-outline">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="font-size: 11px; text-transform: uppercase; color: var(--cti-text-muted);">Name</th>
                                <th style="font-size: 11px; text-transform: uppercase; color: var(--cti-text-muted);">Type</th>
                                <th style="font-size: 11px; text-transform: uppercase; color: var(--cti-text-muted);">Severity</th>
                                <th style="font-size: 11px; text-transform: uppercase; color: var(--cti-text-muted);">Confidence</th>
                                <th style="font-size: 11px; text-transform: uppercase; color: var(--cti-text-muted);">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentThreats as $threat)
                            <tr>
                                <td>
                                    <a href="{{ route('knowledge.entities.show', $threat) }}" style="color: var(--cti-accent-hover); text-decoration: none;">
                                        {{ $threat->name }}
                                    </a>
                                </td>
                                <td><span class="node-type node-type-{{ $threat->type }}">{{ str_replace('-', ' ', $threat->type) }}</span></td>
                                <td>
                                    @if($threat->severity)
                                    <span class="badge-cti badge-{{ $threat->severity }}">{{ ucfirst($threat->severity) }}</span>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($threat->confidence)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="confidence-bar flex-grow-1" style="width: 60px;">
                                            <div class="confidence-fill" style="width: {{ $threat->confidence }}%; background: {{ $threat->confidence >= 70 ? '#10b981' : ($threat->confidence >= 40 ? '#f59e0b' : '#ef4444') }};"></div>
                                        </div>
                                        <small>{{ $threat->confidence }}%</small>
                                    </div>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ $threat->created_at->diffForHumans() }}</small></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="ri-shield-line fs-3 d-block mb-2"></i>
                                    No threats yet. <a href="{{ route('threats.actors.create') }}" style="color: var(--cti-accent);">Create one</a> or
                                    <a href="{{ route('ingestion.import') }}" style="color: var(--cti-accent);">import STIX data</a>.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Severity Distribution + Active Cases --}}
    <div class="col-xl-4 mb-4">
        {{-- Severity Distribution --}}
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="ri-pie-chart-line text-warning me-2"></i>Severity Distribution</h6>
            </div>
            <div class="card-body">
                @php
                    $sevColors = ['critical' => '#ef4444', 'high' => '#f59e0b', 'medium' => '#6366f1', 'low' => '#10b981', 'unknown' => '#64748b'];
                    $sevTotal = array_sum($severityDist) ?: 1;
                @endphp
                @forelse($severityDist as $sev => $count)
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size: 12px; text-transform: capitalize;">{{ $sev }}</span>
                        <span style="font-size: 12px;">{{ $count }}</span>
                    </div>
                    <div class="progress" style="height: 6px; background: rgba(99,102,241,0.1);">
                        <div class="progress-bar" style="width: {{ round($count/$sevTotal*100) }}%; background: {{ $sevColors[$sev] ?? '#64748b' }};"></div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center mb-0" style="font-size: 13px;">No severity data yet</p>
                @endforelse
            </div>
        </div>

        {{-- Active Cases --}}
        <div class="card mb-0">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="ri-folder-shield-2-line text-success me-2"></i>Active Cases</h6>
                <a href="{{ route('cases.incidents.index') }}" class="btn btn-sm btn-cti-outline">All Cases</a>
            </div>
            <div class="card-body p-0">
                @forelse($activeCases as $case)
                <a href="{{ route('cases.incidents.show', $case) }}" class="d-block px-3 py-2 border-bottom" style="border-color: var(--cti-border) !important; color: var(--cti-text); text-decoration: none;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span style="font-size: 13px;">{{ $case->title }}</span>
                            <br><small class="text-muted">{{ ucfirst($case->priority ?? 'medium') }} priority</small>
                        </div>
                        <span class="badge-cti badge-{{ $case->priority ?? 'medium' }}">{{ ucfirst($case->status) }}</span>
                    </div>
                </a>
                @empty
                <div class="text-center py-3 text-muted" style="font-size: 13px;">
                    No active cases. <a href="{{ route('cases.incidents.create') }}" style="color: var(--cti-accent);">Create one</a>.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Recent Activity + Connectors --}}
<div class="row">
    <div class="col-xl-7 mb-4">
        <div class="card mb-0">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="ri-time-line text-info me-2"></i>Recent Activity</h6>
                <a href="{{ route('settings.audit') }}" class="btn btn-sm btn-cti-outline">Audit Log</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="font-size: 11px; text-transform: uppercase; color: var(--cti-text-muted);">Action</th>
                                <th style="font-size: 11px; text-transform: uppercase; color: var(--cti-text-muted);">Entity</th>
                                <th style="font-size: 11px; text-transform: uppercase; color: var(--cti-text-muted);">User</th>
                                <th style="font-size: 11px; text-transform: uppercase; color: var(--cti-text-muted);">When</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentActivity as $log)
                            <tr>
                                <td>
                                    <span class="badge {{ $log->action === 'created' ? 'bg-success-subtle text-success' : ($log->action === 'updated' ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning') }}">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td style="font-size: 13px;">{{ class_basename($log->entity_type ?? '') }}</td>
                                <td style="font-size: 13px;">{{ $log->user->name ?? 'System' }}</td>
                                <td><small class="text-muted">{{ $log->created_at->diffForHumans() }}</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-3 text-muted">No recent activity</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-5 mb-4">
        <div class="card mb-0">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="ri-database-2-line text-primary me-2"></i>Data Connectors</h6>
                <a href="{{ route('ingestion.connectors') }}" class="btn btn-sm btn-cti-outline">Manage</a>
            </div>
            <div class="card-body p-0">
                @forelse($connectors as $conn)
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom" style="border-color: var(--cti-border) !important;">
                    <div>
                        <span style="font-size: 13px; color: var(--cti-text);">{{ $conn->name }}</span>
                        <br>
                        <small class="text-muted">
                            @if($conn->last_run_at)
                                Last run: {{ $conn->last_run_at->diffForHumans() }}
                            @else
                                Never run
                            @endif
                        </small>
                    </div>
                    <span class="badge {{ $conn->status === 'success' ? 'bg-success-subtle text-success' : ($conn->status === 'running' ? 'bg-info-subtle text-info' : ($conn->status === 'error' ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary')) }}">
                        {{ ucfirst($conn->status ?? 'idle') }}
                    </span>
                </div>
                @empty
                <div class="text-center py-3 text-muted" style="font-size: 13px;">
                    No connectors configured. <a href="{{ route('ingestion.connectors') }}" style="color: var(--cti-accent);">Set up connectors</a>.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    // Auto-refresh threat stats every 60s
    setInterval(function() {
        fetch('/api/threat-stats')
            .then(r => r.json())
            .then(data => {
                // Could update KPI cards here if needed
                console.log('CTI stats refreshed:', data.total, 'threats');
            })
            .catch(() => {});
    }, 60000);
</script>
@endsection
