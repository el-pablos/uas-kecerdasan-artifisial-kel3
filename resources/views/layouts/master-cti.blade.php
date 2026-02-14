<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-topbar="dark" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-bs-theme="dark">

<head>
    <meta charset="utf-8" />
    <title>@yield('title') | Log Sentinel — Threat Intelligence Platform</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="OpenCTI-inspired Threat Intelligence Command Center" name="description" />
    <meta content="Log Sentinel Team" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico') }}">
    @include('layouts.head-css')
    <style>
        :root {
            --cti-bg-primary: #0a0e1a;
            --cti-bg-secondary: #111827;
            --cti-bg-card: #1e1e2e;
            --cti-border: rgba(99, 102, 241, 0.15);
            --cti-text: #e2e8f0;
            --cti-text-muted: #94a3b8;
            --cti-accent: #6366f1;
            --cti-accent-hover: #818cf8;
            --cti-red: #ef4444;
            --cti-orange: #f59e0b;
            --cti-green: #10b981;
            --cti-blue: #3b82f6;
            --cti-purple: #8b5cf6;
            --cti-cyan: #06b6d4;
        }
        body {
            background: var(--cti-bg-primary) !important;
            color: var(--cti-text);
        }
        .page-content {
            background: var(--cti-bg-primary) !important;
        }
        .card {
            background: var(--cti-bg-card) !important;
            border: 1px solid var(--cti-border) !important;
            border-radius: 8px;
        }
        .card-header {
            background: transparent !important;
            border-bottom: 1px solid var(--cti-border) !important;
        }
        .table {
            color: var(--cti-text) !important;
        }
        .table > :not(caption) > * > * {
            background-color: transparent !important;
            color: var(--cti-text);
            border-color: var(--cti-border);
        }
        .table-hover > tbody > tr:hover > * {
            background: rgba(99, 102, 241, 0.05) !important;
        }
        .badge-cti { font-size: 10px; padding: 4px 8px; border-radius: 4px; font-weight: 500; }
        .badge-critical { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
        .badge-high { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
        .badge-medium { background: rgba(99, 102, 241, 0.15); color: #818cf8; }
        .badge-low { background: rgba(16, 185, 129, 0.15); color: #10b981; }
        .badge-unknown { background: rgba(148, 163, 184, 0.15); color: #94a3b8; }

        /* Node type badges - OpenCTI style */
        .node-type { font-size: 9px; padding: 3px 8px; border-radius: 3px; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; }
        .node-type-threat-actor { background: #ef4444; color: white; }
        .node-type-malware { background: #f97316; color: white; }
        .node-type-campaign { background: #eab308; color: #1a1a2e; }
        .node-type-intrusion-set { background: #a855f7; color: white; }
        .node-type-vulnerability { background: #06b6d4; color: white; }
        .node-type-observable { background: #3b82f6; color: white; }
        .node-type-technique { background: #8b5cf6; color: white; }
        .node-type-tool { background: #64748b; color: white; }
        .node-type-identity { background: #10b981; color: white; }
        .node-type-indicator { background: #f59e0b; color: #1a1a2e; }
        .node-type-sighting { background: #ec4899; color: white; }

        .cti-stat-card {
            background: var(--cti-bg-card);
            border: 1px solid var(--cti-border);
            border-radius: 8px;
            padding: 20px;
            transition: all 0.2s;
        }
        .cti-stat-card:hover {
            border-color: var(--cti-accent);
            transform: translateY(-2px);
        }
        .cti-stat-number { font-size: 28px; font-weight: 700; }
        .cti-stat-label { font-size: 12px; color: var(--cti-text-muted); text-transform: uppercase; letter-spacing: 1px; }

        .btn-cti-primary {
            background: var(--cti-accent);
            border: none;
            color: white;
            border-radius: 6px;
            font-size: 13px;
            padding: 8px 16px;
        }
        .btn-cti-primary:hover {
            background: var(--cti-accent-hover);
            color: white;
        }
        .btn-cti-outline {
            background: transparent;
            border: 1px solid var(--cti-border);
            color: var(--cti-text-muted);
            border-radius: 6px;
            font-size: 13px;
            padding: 8px 16px;
        }
        .btn-cti-outline:hover {
            border-color: var(--cti-accent);
            color: var(--cti-accent);
        }
        .form-control, .form-select {
            background: var(--cti-bg-secondary) !important;
            border: 1px solid var(--cti-border) !important;
            color: var(--cti-text) !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--cti-accent) !important;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15) !important;
        }
        /* Global search override */
        #cti-global-search {
            background: rgba(99, 102, 241, 0.08) !important;
            border: 1px solid rgba(99, 102, 241, 0.2) !important;
            border-radius: 8px;
            padding: 8px 16px 8px 40px;
            color: var(--cti-text) !important;
            width: 400px;
            font-size: 13px;
        }
        #cti-global-search::placeholder { color: #64748b; }
        #cti-global-search:focus {
            border-color: var(--cti-accent) !important;
            width: 500px;
            transition: width 0.3s;
        }

        /* Topbar override */
        .navbar-header { background: #0a0e1a !important; border-bottom: 1px solid var(--cti-border); }
        #page-topbar { background: #0a0e1a !important; }

        .breadcrumb-item a { color: var(--cti-text-muted); }
        .breadcrumb-item.active { color: var(--cti-text); }

        /* Confidence meter */
        .confidence-bar {
            height: 4px;
            border-radius: 2px;
            background: rgba(99, 102, 241, 0.2);
        }
        .confidence-fill {
            height: 100%;
            border-radius: 2px;
            transition: width 0.3s;
        }
    </style>
    @yield('css')
</head>

@section('body')
    @include('layouts.body')
@show
    <div id="layout-wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar-cti')

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>

    @include('layouts.customizer')
    @include('layouts.vendor-scripts')
    
    <script>
        // Global AJAX CSRF
        window.CSRF_TOKEN = '{{ csrf_token() }}';
        window.ML_SERVICE_URL = '{{ env("ML_SERVICE_URL", "http://127.0.0.1:5000") }}';
    </script>
    @yield('script')
</body>
</html>
