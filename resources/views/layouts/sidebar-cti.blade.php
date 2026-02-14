<!-- ========== CTI SIDEBAR - OpenCTI Inspired ========== -->
<div class="app-menu navbar-menu" style="background: #0a0e1a;">
    <!-- LOGO -->
    <div class="navbar-brand-box" style="background: #0a0e1a; border-bottom: 1px solid rgba(99, 102, 241, 0.15);">
        <a href="{{ route('sentinel.dashboard') }}" class="logo logo-dark">
            <span class="logo-sm"><i class="ri-shield-flash-line text-info fs-3"></i></span>
            <span class="logo-lg">
                <div class="d-flex align-items-center">
                    <i class="ri-shield-flash-line text-info fs-3 me-2"></i>
                    <span class="fw-bold text-white" style="font-size: 15px; letter-spacing: 1px;">LOG SENTINEL</span>
                </div>
                <small class="text-muted d-block ms-4 ps-2" style="font-size: 9px; letter-spacing: 2px;">THREAT INTELLIGENCE</small>
            </span>
        </a>
        <a href="{{ route('sentinel.dashboard') }}" class="logo logo-light">
            <span class="logo-sm"><i class="ri-shield-flash-line text-info fs-3"></i></span>
            <span class="logo-lg">
                <div class="d-flex align-items-center">
                    <i class="ri-shield-flash-line text-info fs-3 me-2"></i>
                    <span class="fw-bold text-white" style="font-size: 15px; letter-spacing: 1px;">LOG SENTINEL</span>
                </div>
                <small class="text-muted d-block ms-4 ps-2" style="font-size: 9px; letter-spacing: 2px;">THREAT INTELLIGENCE</small>
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">

                {{-- ===== HOME / DASHBOARD ===== --}}
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('sentinel.dashboard') ? 'active' : '' }}"
                       href="{{ route('sentinel.dashboard') }}">
                        <i class="ri-dashboard-2-line"></i> <span>Dashboard</span>
                    </a>
                </li>

                {{-- ===== ANALYSIS ===== --}}
                <li class="menu-title"><span style="color: #6366f1; font-size: 10px; letter-spacing: 2px;">ANALYSIS</span></li>

                {{-- Threats --}}
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('threats.*') ? 'active' : '' }}"
                       href="#sidebarThreats" data-bs-toggle="collapse" role="button"
                       aria-expanded="{{ request()->routeIs('threats.*') ? 'true' : 'false' }}"
                       aria-controls="sidebarThreats">
                        <i class="ri-skull-line" style="color: #ef4444;"></i> <span>Threats</span>
                    </a>
                    <div class="collapse {{ request()->routeIs('threats.*') ? 'show' : '' }}" id="sidebarThreats">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('threats.actors.index') }}" class="nav-link {{ request()->routeIs('threats.actors.*') ? 'active' : '' }}">Threat Actors</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('threats.malware.index') }}" class="nav-link {{ request()->routeIs('threats.malware.*') ? 'active' : '' }}">Malware</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('threats.campaigns.index') }}" class="nav-link {{ request()->routeIs('threats.campaigns.*') ? 'active' : '' }}">Campaigns</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('threats.intrusion-sets.index') }}" class="nav-link {{ request()->routeIs('threats.intrusion-sets.*') ? 'active' : '' }}">Intrusion Sets</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('threats.vulnerabilities.index') }}" class="nav-link {{ request()->routeIs('threats.vulnerabilities.*') ? 'active' : '' }}">Vulnerabilities</a>
                            </li>
                        </ul>
                    </div>
                </li>

                {{-- Knowledge --}}
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('knowledge.*') ? 'active' : '' }}"
                       href="#sidebarKnowledge" data-bs-toggle="collapse" role="button"
                       aria-expanded="{{ request()->routeIs('knowledge.*') ? 'true' : 'false' }}"
                       aria-controls="sidebarKnowledge">
                        <i class="ri-mind-map" style="color: #8b5cf6;"></i> <span>Knowledge</span>
                    </a>
                    <div class="collapse {{ request()->routeIs('knowledge.*') ? 'show' : '' }}" id="sidebarKnowledge">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('knowledge.entities.index') }}" class="nav-link {{ request()->routeIs('knowledge.entities.*') ? 'active' : '' }}">Entities</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('knowledge.relationships.index') }}" class="nav-link {{ request()->routeIs('knowledge.relationships.*') ? 'active' : '' }}">Relationships</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('knowledge.graph') }}" class="nav-link {{ request()->routeIs('knowledge.graph') ? 'active' : '' }}">Graph Explorer</a>
                            </li>
                        </ul>
                    </div>
                </li>

                {{-- ===== EVENTS ===== --}}
                <li class="menu-title"><span style="color: #6366f1; font-size: 10px; letter-spacing: 2px;">EVENTS</span></li>

                {{-- Observations --}}
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('observations.*') || request()->routeIs('sentinel.logs') ? 'active' : '' }}"
                       href="#sidebarObservations" data-bs-toggle="collapse" role="button"
                       aria-expanded="{{ request()->routeIs('observations.*') || request()->routeIs('sentinel.logs') ? 'true' : 'false' }}"
                       aria-controls="sidebarObservations">
                        <i class="ri-radar-line" style="color: #f59e0b;"></i> <span>Observations</span>
                    </a>
                    <div class="collapse {{ request()->routeIs('observations.*') || request()->routeIs('sentinel.logs') ? 'show' : '' }}" id="sidebarObservations">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('observations.index') }}" class="nav-link {{ request()->routeIs('observations.index') ? 'active' : '' }}">All Observations</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('observations.alerts') }}" class="nav-link {{ request()->routeIs('observations.alerts') ? 'active' : '' }}">Alerts</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('sentinel.logs') }}" class="nav-link {{ request()->routeIs('sentinel.logs') ? 'active' : '' }}">Log Explorer</a>
                            </li>
                        </ul>
                    </div>
                </li>

                {{-- Cases --}}
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('cases.*') ? 'active' : '' }}"
                       href="#sidebarCases" data-bs-toggle="collapse" role="button"
                       aria-expanded="{{ request()->routeIs('cases.*') ? 'true' : 'false' }}"
                       aria-controls="sidebarCases">
                        <i class="ri-folder-shield-2-line" style="color: #10b981;"></i> <span>Cases</span>
                    </a>
                    <div class="collapse {{ request()->routeIs('cases.*') ? 'show' : '' }}" id="sidebarCases">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('cases.incidents.index') }}" class="nav-link {{ request()->routeIs('cases.incidents.*') ? 'active' : '' }}">Incidents</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('cases.tasks.index') }}" class="nav-link {{ request()->routeIs('cases.tasks.*') ? 'active' : '' }}">Tasks</a>
                            </li>
                        </ul>
                    </div>
                </li>

                {{-- Investigations --}}
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('investigations.*') ? 'active' : '' }}"
                       href="{{ route('investigations.index') }}">
                        <i class="ri-search-eye-line" style="color: #06b6d4;"></i> <span>Investigations</span>
                    </a>
                </li>

                {{-- ===== DATA ===== --}}
                <li class="menu-title"><span style="color: #6366f1; font-size: 10px; letter-spacing: 2px;">DATA</span></li>

                {{-- Ingestion --}}
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('ingestion.*') ? 'active' : '' }}"
                       href="#sidebarIngestion" data-bs-toggle="collapse" role="button"
                       aria-expanded="{{ request()->routeIs('ingestion.*') ? 'true' : 'false' }}"
                       aria-controls="sidebarIngestion">
                        <i class="ri-database-2-line" style="color: #3b82f6;"></i> <span>Data & Ingestion</span>
                    </a>
                    <div class="collapse {{ request()->routeIs('ingestion.*') ? 'show' : '' }}" id="sidebarIngestion">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('ingestion.connectors') }}" class="nav-link {{ request()->routeIs('ingestion.connectors') ? 'active' : '' }}">Connectors</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('ingestion.import') }}" class="nav-link {{ request()->routeIs('ingestion.import') ? 'active' : '' }}">Import</a>
                            </li>
                        </ul>
                    </div>
                </li>

                {{-- ===== CONFIGURATION ===== --}}
                <li class="menu-title"><span style="color: #6366f1; font-size: 10px; letter-spacing: 2px;">SETTINGS</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"
                       href="#sidebarSettings" data-bs-toggle="collapse" role="button"
                       aria-expanded="{{ request()->routeIs('settings.*') ? 'true' : 'false' }}"
                       aria-controls="sidebarSettings">
                        <i class="ri-settings-4-line" style="color: #94a3b8;"></i> <span>Settings</span>
                    </a>
                    <div class="collapse {{ request()->routeIs('settings.*') ? 'show' : '' }}" id="sidebarSettings">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('settings.users') }}" class="nav-link {{ request()->routeIs('settings.users') ? 'active' : '' }}">Users & Roles</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('settings.tokens') }}" class="nav-link {{ request()->routeIs('settings.tokens') ? 'active' : '' }}">API Tokens</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('settings.taxonomy') }}" class="nav-link {{ request()->routeIs('settings.taxonomy') ? 'active' : '' }}">Taxonomy / Labels</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('settings.audit') }}" class="nav-link {{ request()->routeIs('settings.audit') ? 'active' : '' }}">Audit Logs</a>
                            </li>
                        </ul>
                    </div>
                </li>

                {{-- About --}}
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('sentinel.about') ? 'active' : '' }}"
                       href="{{ route('sentinel.about') }}">
                        <i class="ri-information-line"></i> <span>About</span>
                    </a>
                </li>

                {{-- ===== SERVICE STATUS ===== --}}
                <li class="menu-title"><span style="color: #6366f1; font-size: 10px; letter-spacing: 2px;">SERVICES</span></li>

                <li class="nav-item">
                    <div class="nav-link">
                        <div class="d-flex align-items-center">
                            <span class="flex-shrink-0 me-2">
                                <span class="bg-success rounded-circle d-inline-block" style="width: 8px; height: 8px; animation: pulse 2s infinite;"></span>
                            </span>
                            <span class="flex-grow-1"><span class="text-muted" style="font-size: 12px;">Laravel Backend</span></span>
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <div class="nav-link" id="mlServiceStatus">
                        <div class="d-flex align-items-center">
                            <span class="flex-shrink-0 me-2">
                                <span class="status-dot bg-warning rounded-circle d-inline-block" style="width: 8px; height: 8px;"></span>
                            </span>
                            <span class="flex-grow-1"><span class="text-muted" style="font-size: 12px;">ML Service (Python)</span></span>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<div class="vertical-overlay"></div>

<style>
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.3} }
    .app-menu.navbar-menu {
        background: #0a0e1a !important;
    }
    .app-menu .navbar-nav .nav-link {
        color: #94a3b8;
        font-size: 13px;
        padding: 8px 16px;
        transition: all 0.2s;
    }
    .app-menu .navbar-nav .nav-link:hover {
        color: #e2e8f0;
        background: rgba(99, 102, 241, 0.08);
    }
    .app-menu .navbar-nav .nav-link.active {
        color: #818cf8 !important;
        background: rgba(99, 102, 241, 0.12);
        border-left: 2px solid #6366f1;
    }
    .app-menu .menu-title span {
        font-weight: 600;
    }
    .app-menu .nav-sm .nav-link {
        font-size: 12px;
        padding: 5px 16px 5px 40px;
        color: #64748b;
    }
    .app-menu .nav-sm .nav-link:hover { color: #a5b4fc; }
    .app-menu .nav-sm .nav-link.active {
        color: #818cf8 !important;
        background: rgba(99, 102, 241, 0.08);
        border-left: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        checkMlServiceStatus();
        setInterval(checkMlServiceStatus, 30000);
    });

    function checkMlServiceStatus() {
        fetch('{{ env("ML_SERVICE_URL", "http://127.0.0.1:5000") }}/health')
            .then(r => r.ok ? updateMlStatus('online') : updateMlStatus('error'))
            .catch(() => updateMlStatus('offline'));
    }

    function updateMlStatus(status) {
        const dot = document.querySelector('#mlServiceStatus .status-dot');
        if (!dot) return;
        dot.classList.remove('bg-success','bg-warning','bg-danger');
        dot.style.animation = status === 'online' ? 'pulse 2s infinite' : 'none';
        dot.classList.add(status === 'online' ? 'bg-success' : status === 'error' ? 'bg-warning' : 'bg-danger');
    }
</script>
