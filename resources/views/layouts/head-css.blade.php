@yield('css')
<!-- Layout config Js -->
<script src="{{ URL::asset('build/js/layout.js') }}"></script>
<!-- Bootstrap Css -->
<link href="{{ URL::asset('build/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
<!-- Icons Css -->
<link href="{{ URL::asset('build/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
<!-- App Css-->
<link href="{{ URL::asset('build/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
<!-- custom Css-->
<link href="{{ URL::asset('build/css/custom.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />

<!-- Log Sentinel Cybersecurity Theme -->
<style>
    /* ==============================================================
       Log Sentinel v2.0 - Cybersecurity Command Center Theme
       Author: Muhammad Akbar Hadi Pratama (@el-pablos)
       ============================================================== */
    
    :root {
        --sentinel-bg-primary: #0f172a;
        --sentinel-bg-secondary: #1e293b;
        --sentinel-bg-card: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        --sentinel-accent-green: #4ade80;
        --sentinel-accent-red: #ef4444;
        --sentinel-accent-yellow: #fbbf24;
        --sentinel-accent-purple: #a78bfa;
        --sentinel-text-primary: #e2e8f0;
        --sentinel-text-muted: #94a3b8;
        --sentinel-border-glow: rgba(74, 222, 128, 0.3);
    }
    
    /* XAI Section Cards Glow Effect */
    .card[style*="linear-gradient"] {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
        transition: all 0.3s ease;
    }
    
    .card[style*="linear-gradient"]:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(74, 222, 128, 0.15);
    }
    
    /* Cybersecurity Pulse Animation */
    @keyframes cyber-pulse {
        0%, 100% { opacity: 0.8; }
        50% { opacity: 1; }
    }
    
    /* Terminal-style text */
    .terminal-text {
        font-family: 'JetBrains Mono', 'Fira Code', monospace;
        color: var(--sentinel-accent-green);
    }
    
    /* Threat Level Progress Bar Glow */
    #threatLevelBar {
        box-shadow: 0 0 10px currentColor;
        transition: all 0.5s ease;
    }
    
    /* Voting Cards Hover Effect */
    .col-4 > div[id^="vote"]:hover {
        transform: scale(1.02);
        transition: transform 0.2s ease;
    }
    
    /* ApexCharts Dark Theme Override */
    .apexcharts-canvas {
        background: transparent !important;
    }
    
    .apexcharts-legend-text {
        color: var(--sentinel-text-muted) !important;
    }
    
    .apexcharts-xaxis-label, .apexcharts-yaxis-label {
        fill: var(--sentinel-text-muted) !important;
    }
</style>

{{-- @yield('css') --}}
