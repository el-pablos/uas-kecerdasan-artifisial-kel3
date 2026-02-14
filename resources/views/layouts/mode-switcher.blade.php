{{-- Mode Switcher: CTI / Sentinel --}}
<div class="dropdown ms-1 topbar-head-dropdown header-item">
    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" 
            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            title="Switch Mode">
        @if(request()->routeIs('sentinel.*'))
            <i class="ri-shield-check-line fs-22 text-primary"></i>
        @else
            <i class="ri-shield-flash-line fs-22 text-info"></i>
        @endif
    </button>
    <div class="dropdown-menu dropdown-menu-end">
        <div class="p-2 border-bottom" style="border-color: rgba(99,102,241,0.15) !important;">
            <h6 class="text-muted mb-0" style="font-size: 11px; letter-spacing: 1px;">SWITCH MODE</h6>
        </div>
        <a class="dropdown-item py-2 {{ !request()->routeIs('sentinel.*') ? 'active' : '' }}" href="{{ route('cti.dashboard') }}">
            <i class="ri-shield-flash-line text-info me-2 align-middle"></i>
            <span class="align-middle fw-medium">Threat Intelligence (CTI)</span>
            @if(!request()->routeIs('sentinel.*'))
                <i class="ri-check-line text-success ms-2 align-middle"></i>
            @endif
        </a>
        <a class="dropdown-item py-2 {{ request()->routeIs('sentinel.*') ? 'active' : '' }}" href="{{ route('sentinel.dashboard') }}">
            <i class="ri-shield-check-line text-primary me-2 align-middle"></i>
            <span class="align-middle fw-medium">Log Sentinel (Anomaly)</span>
            @if(request()->routeIs('sentinel.*'))
                <i class="ri-check-line text-success ms-2 align-middle"></i>
            @endif
        </a>
    </div>
</div>
