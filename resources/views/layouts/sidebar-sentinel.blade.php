<!-- ========== App Menu - Log Sentinel ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{ route('sentinel.dashboard') }}" class="logo logo-dark">
            <span class="logo-sm">
                <i class="ri-shield-check-line text-primary fs-3"></i>
            </span>
            <span class="logo-lg">
                <div class="d-flex align-items-center">
                    <i class="ri-shield-check-line text-primary fs-3 me-2"></i>
                    <span class="fw-bold">Log Sentinel</span>
                </div>
            </span>
        </a>
        <!-- Light Logo-->
        <a href="{{ route('sentinel.dashboard') }}" class="logo logo-light">
            <span class="logo-sm">
                <i class="ri-shield-check-line text-primary fs-3"></i>
            </span>
            <span class="logo-lg">
                <div class="d-flex align-items-center">
                    <i class="ri-shield-check-line fs-3 me-2"></i>
                    <span class="fw-bold text-white">Log Sentinel</span>
                </div>
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <!-- Menu Utama Log Sentinel -->
                <li class="menu-title"><span>Log Sentinel</span></li>
                
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('sentinel.dashboard') ? 'active' : '' }}" 
                       href="{{ route('sentinel.dashboard') }}">
                        <i class="ri-dashboard-line"></i> <span>Dashboard</span>
                    </a>
                </li>

                <!-- Live Monitoring -->
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('sentinel.logs') ? 'active' : '' }}" 
                       href="{{ route('sentinel.logs') }}">
                        <i class="ri-radar-line"></i> <span>Daftar Log</span>
                    </a>
                </li>

                <!-- Filter Cepat -->
                <li class="menu-title mt-3"><span>Filter Cepat</span></li>
                
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('sentinel.logs', ['filter' => 'anomaly']) }}">
                        <i class="ri-alert-line text-danger"></i> <span>Anomali Saja</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('sentinel.logs', ['filter' => 'normal']) }}">
                        <i class="ri-check-line text-success"></i> <span>Normal Saja</span>
                    </a>
                </li>

                <!-- Informasi -->
                <li class="menu-title mt-3"><span>Informasi</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('sentinel.about') ? 'active' : '' }}" 
                       href="{{ route('sentinel.about') }}">
                        <i class="ri-information-line"></i> <span>Tentang Sistem</span>
                    </a>
                </li>

                <!-- Status ML Service -->
                <li class="menu-title mt-3"><span>Status Service</span></li>
                
                <li class="nav-item">
                    <div class="nav-link">
                        <div class="d-flex align-items-center">
                            <span class="flex-shrink-0 me-2">
                                <span class="status-dot bg-success rounded-circle d-inline-block" style="width: 8px; height: 8px; animation: pulse 2s infinite;"></span>
                            </span>
                            <span class="flex-grow-1">
                                <span class="text-muted">Laravel Backend</span>
                            </span>
                        </div>
                    </div>
                </li>
                
                <li class="nav-item">
                    <div class="nav-link" id="mlServiceStatus">
                        <div class="d-flex align-items-center">
                            <span class="flex-shrink-0 me-2">
                                <span class="status-dot bg-warning rounded-circle d-inline-block" style="width: 8px; height: 8px;"></span>
                            </span>
                            <span class="flex-grow-1">
                                <span class="text-muted">ML Service</span>
                            </span>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <!-- Sidebar -->
    </div>
    
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>

<style>
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
    .nav-link.active {
        background-color: rgba(var(--vz-primary-rgb), 0.1);
        color: var(--vz-primary) !important;
    }
</style>

<script>
    // Cek status ML Service secara periodik
    document.addEventListener('DOMContentLoaded', function() {
        checkMlServiceStatus();
        setInterval(checkMlServiceStatus, 30000); // Cek setiap 30 detik
    });

    function checkMlServiceStatus() {
        fetch('{{ env("ML_SERVICE_URL", "http://127.0.0.1:5000") }}/health')
            .then(response => {
                if (response.ok) {
                    updateMlStatus('online');
                } else {
                    updateMlStatus('error');
                }
            })
            .catch(() => {
                updateMlStatus('offline');
            });
    }

    function updateMlStatus(status) {
        const statusDot = document.querySelector('#mlServiceStatus .status-dot');
        if (statusDot) {
            statusDot.classList.remove('bg-success', 'bg-warning', 'bg-danger');
            switch(status) {
                case 'online':
                    statusDot.classList.add('bg-success');
                    statusDot.style.animation = 'pulse 2s infinite';
                    break;
                case 'error':
                    statusDot.classList.add('bg-warning');
                    statusDot.style.animation = 'none';
                    break;
                case 'offline':
                    statusDot.classList.add('bg-danger');
                    statusDot.style.animation = 'none';
                    break;
            }
        }
    }
</script>
