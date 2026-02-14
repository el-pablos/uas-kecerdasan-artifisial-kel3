@extends('layouts.master-cti')
@section('title', 'Knowledge Graph Explorer')
@section('css')
<style>
    #cy-container { position: relative; }
    #cy {
        width: 100%;
        height: calc(100vh - 200px);
        min-height: 500px;
        background: #080c16;
        border: 1px solid var(--cti-border);
        border-radius: 8px;
    }
    /* Loading overlay */
    #cy-loading {
        position: absolute; inset: 0; z-index: 30;
        display: flex; align-items: center; justify-content: center;
        background: rgba(8, 12, 22, 0.85); border-radius: 8px;
    }
    #cy-loading .spinner-border { width: 3rem; height: 3rem; }
    #cy-loading.d-none { display: none !important; }

    /* Legend */
    #graphLegend {
        position: absolute; bottom: 10px; left: 10px; z-index: 20;
        background: rgba(30, 30, 46, 0.92); border: 1px solid var(--cti-border);
        border-radius: 8px; padding: 10px 14px; max-width: 220px;
        backdrop-filter: blur(6px);
    }
    #graphLegend h6 { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 6px; }
    .legend-item { display: flex; align-items: center; gap: 6px; padding: 2px 0; cursor: pointer; font-size: 11px; color: #cbd5e1; }
    .legend-item:hover { color: #fff; }
    .legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .legend-item.muted { opacity: 0.3; text-decoration: line-through; }

    /* Zoom controls */
    #zoomControls {
        position: absolute; top: 10px; right: 10px; z-index: 20;
        display: flex; flex-direction: column; gap: 4px;
    }
    #zoomControls .btn { width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center; }

    /* Tooltip */
    #cyTooltip {
        position: absolute; z-index: 25; pointer-events: none;
        background: rgba(30, 30, 46, 0.95); border: 1px solid var(--cti-border);
        border-radius: 6px; padding: 8px 12px; font-size: 11px;
        color: #e2e8f0; max-width: 260px; display: none;
        backdrop-filter: blur(6px); box-shadow: 0 4px 16px rgba(0,0,0,0.4);
    }
    #cyTooltip .tt-type { font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
    #cyTooltip .tt-name { font-weight: 600; font-size: 12px; margin: 2px 0; }
    #cyTooltip .tt-meta { color: #94a3b8; font-size: 10px; }

    /* Node detail panel */
    #nodePanel {
        position: absolute; top: 10px; left: 10px; z-index: 20;
        width: 320px; max-height: calc(100% - 20px); overflow-y: auto;
        background: rgba(30, 30, 46, 0.95) !important;
        backdrop-filter: blur(8px);
    }
    #nodePanel.d-none { display: none !important; }
    .confidence-mini { height: 4px; border-radius: 2px; background: rgba(99,102,241,0.15); width: 100%; }
    .confidence-mini-fill { height: 100%; border-radius: 2px; }
    .neighbor-chip { font-size: 10px; padding: 2px 6px; border-radius: 3px; cursor: pointer; }
    .neighbor-chip:hover { filter: brightness(1.3); }

    /* Filter sidebar */
    #filterPanel {
        position: absolute; top: 10px; right: 54px; z-index: 20;
        width: 260px; background: rgba(30, 30, 46, 0.95) !important;
        backdrop-filter: blur(8px); border-radius: 8px;
        border: 1px solid var(--cti-border); padding: 14px;
    }
    #filterPanel.d-none { display: none !important; }
    #filterPanel label { font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
    #filterPanel .form-control, #filterPanel .form-select { font-size: 12px; padding: 4px 8px; }
    #filterPanel .form-range { accent-color: var(--cti-accent); }

    /* Status bar */
    #graphStatus {
        position: absolute; bottom: 10px; right: 10px; z-index: 20;
        font-size: 10px; color: #64748b; background: rgba(30,30,46,0.8);
        padding: 4px 10px; border-radius: 4px;
    }

    /* Search bar */
    #graphSearch { width: 260px; font-size: 12px; }
    .search-results {
        position: absolute; top: 100%; left: 0; right: 0; z-index: 30;
        background: var(--cti-bg-card); border: 1px solid var(--cti-border);
        border-radius: 0 0 6px 6px; max-height: 240px; overflow-y: auto;
    }
    .search-results .sr-item { padding: 6px 10px; cursor: pointer; font-size: 12px; border-bottom: 1px solid var(--cti-border); }
    .search-results .sr-item:hover { background: rgba(99,102,241,0.1); }
</style>
@endsection
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 text-white"><i class="ri-mind-map me-2 text-cyan"></i> Knowledge Graph Explorer</h4>
                        <div class="d-flex gap-2 align-items-center">
                            {{-- Search --}}
                            <div class="position-relative">
                                <input type="text" id="graphSearch" class="form-control form-control-sm" placeholder="Search nodes... (Ctrl+K)" autocomplete="off">
                                <div id="searchResults" class="search-results d-none"></div>
                            </div>
                            <select id="graphLayout" class="form-select form-select-sm" style="width:auto">
                                <option value="cose">CoSE (Force)</option>
                                <option value="circle">Circle</option>
                                <option value="grid">Grid</option>
                                <option value="breadthfirst">Hierarchy</option>
                                <option value="concentric">Concentric</option>
                            </select>
                            <div class="btn-group btn-group-sm">
                                <button id="btnFilter" class="btn btn-soft-warning" title="Filters"><i class="ri-filter-3-line"></i></button>
                                <button id="btnFit" class="btn btn-soft-info" title="Fit to screen"><i class="ri-fullscreen-line"></i></button>
                                <button id="btnRefresh" class="btn btn-soft-success" title="Reload graph"><i class="ri-refresh-line"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div id="cy-container">
                        {{-- Loading overlay --}}
                        <div id="cy-loading">
                            <div class="text-center">
                                <div class="spinner-border text-info" role="status"></div>
                                <p class="text-muted mt-2 mb-0" style="font-size:12px">Loading graph data...</p>
                            </div>
                        </div>

                        <div id="cy"></div>

                        {{-- Tooltip --}}
                        <div id="cyTooltip"></div>

                        {{-- Node detail panel --}}
                        <div id="nodePanel" class="card d-none">
                            <div class="card-header d-flex justify-content-between align-items-center py-2">
                                <h6 class="mb-0 text-truncate" id="panelTitle" style="max-width:240px">—</h6>
                                <button class="btn btn-sm btn-soft-secondary" id="panelClose"><i class="ri-close-line"></i></button>
                            </div>
                            <div class="card-body py-2" id="panelBody"></div>
                        </div>

                        {{-- Legend --}}
                        <div id="graphLegend">
                            <h6><i class="ri-palette-line me-1"></i>Entity Types</h6>
                            <div id="legendItems"></div>
                        </div>

                        {{-- Zoom controls --}}
                        <div id="zoomControls">
                            <button class="btn btn-sm btn-soft-light" id="btnZoomIn" title="Zoom in"><i class="ri-zoom-in-line"></i></button>
                            <button class="btn btn-sm btn-soft-light" id="btnZoomOut" title="Zoom out"><i class="ri-zoom-out-line"></i></button>
                        </div>

                        {{-- Filter panel --}}
                        <div id="filterPanel" class="d-none">
                            <h6 class="text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:1px"><i class="ri-filter-3-line me-1"></i>Graph Filters</h6>
                            <div class="mb-2">
                                <label>Min Confidence</label>
                                <input type="range" id="fConfidence" class="form-range" min="0" max="100" value="0">
                                <div class="d-flex justify-content-between" style="font-size:10px;color:#64748b"><span>0%</span><span id="fConfVal">0%</span><span>100%</span></div>
                            </div>
                            <div class="mb-2">
                                <label>Severity</label>
                                <select id="fSeverity" class="form-select form-select-sm">
                                    <option value="">All</option>
                                    <option value="critical">Critical</option>
                                    <option value="high">High</option>
                                    <option value="medium">Medium</option>
                                    <option value="low">Low</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label>Edge Type</label>
                                <select id="fEdgeType" class="form-select form-select-sm">
                                    <option value="">All</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label>Max Depth</label>
                                <select id="fDepth" class="form-select form-select-sm">
                                    <option value="1">1</option>
                                    <option value="2" selected>2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>
                            </div>
                            <button class="btn btn-sm btn-cti-primary w-100 mt-1" id="btnApplyFilter"><i class="ri-check-line me-1"></i>Apply Filters</button>
                            <button class="btn btn-sm btn-cti-outline w-100 mt-1" id="btnResetFilter"><i class="ri-refresh-line me-1"></i>Reset</button>
                        </div>

                        {{-- Status bar --}}
                        <div id="graphStatus">0 nodes · 0 edges</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
<script src="https://unpkg.com/cytoscape@3.30.4/dist/cytoscape.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Color & icon mapping (OpenCTI-inspired) ── */
    const TYPE_MAP = {
        'threat-actor':   { color: '#ef4444', icon: 'ri-spy-line',           label: 'Threat Actor' },
        'malware':        { color: '#f97316', icon: 'ri-bug-line',           label: 'Malware' },
        'campaign':       { color: '#eab308', icon: 'ri-megaphone-line',     label: 'Campaign' },
        'intrusion-set':  { color: '#a855f7', icon: 'ri-skull-2-line',       label: 'Intrusion Set' },
        'vulnerability':  { color: '#06b6d4', icon: 'ri-shield-keyhole-line',label: 'Vulnerability' },
        'observable':     { color: '#3b82f6', icon: 'ri-eye-line',           label: 'Observable' },
        'technique':      { color: '#8b5cf6', icon: 'ri-flashlight-line',    label: 'Technique' },
        'tool':           { color: '#64748b', icon: 'ri-tools-line',         label: 'Tool' },
        'identity':       { color: '#10b981', icon: 'ri-user-line',          label: 'Identity' },
        'indicator':      { color: '#f59e0b', icon: 'ri-alarm-warning-line', label: 'Indicator' },
        'sighting':       { color: '#ec4899', icon: 'ri-search-eye-line',    label: 'Sighting' },
    };
    const fallback = { color: '#6366f1', icon: 'ri-question-line', label: 'Unknown' };
    const typeOf = t => TYPE_MAP[t] || fallback;

    /* ── Cache ── */
    const _cache = {};
    const cacheKey = (p) => JSON.stringify(p);

    /* ── Init Cytoscape ── */
    const cy = cytoscape({
        container: document.getElementById('cy'),
        minZoom: 0.15, maxZoom: 4,
        wheelSensitivity: 0.3,
        boxSelectionEnabled: true,
        style: [
            { selector: 'node', style: {
                'background-color': 'data(color)',
                'label': 'data(label)',
                'color': '#e2e8f0',
                'font-size': '11px',
                'text-valign': 'bottom',
                'text-margin-y': 6,
                'width': 'mapData(size, 1, 10, 30, 60)',
                'height': 'mapData(size, 1, 10, 30, 60)',
                'border-width': 2,
                'border-color': 'data(color)',
                'text-outline-width': 2,
                'text-outline-color': '#080c16',
                'opacity': 1,
                'transition-property': 'opacity, width, height, border-width',
                'transition-duration': '0.2s'
            }},
            { selector: 'edge', style: {
                'width': 'mapData(weight, 0, 100, 1, 4)',
                'line-color': '#4b5563',
                'target-arrow-color': '#6b7280',
                'target-arrow-shape': 'triangle',
                'arrow-scale': 0.8,
                'curve-style': 'bezier',
                'label': 'data(label)',
                'font-size': '9px',
                'color': '#64748b',
                'text-rotation': 'autorotate',
                'text-outline-width': 2,
                'text-outline-color': '#080c16',
                'opacity': 0.7,
                'transition-property': 'opacity, width, line-color',
                'transition-duration': '0.2s'
            }},
            { selector: 'node:selected', style: {
                'border-color': '#6366f1',
                'border-width': 4,
                'overlay-color': '#6366f1',
                'overlay-opacity': 0.15
            }},
            { selector: 'node.dimmed', style: { 'opacity': 0.15 }},
            { selector: 'edge.dimmed', style: { 'opacity': 0.08 }},
            { selector: 'node.highlighted', style: { 'border-color': '#fbbf24', 'border-width': 4 }},
            { selector: 'edge.highlighted', style: { 'line-color': '#fbbf24', 'target-arrow-color': '#fbbf24', 'width': 3, 'opacity': 1 }},
            { selector: 'node.search-match', style: { 'border-color': '#22d3ee', 'border-width': 4, 'overlay-color': '#22d3ee', 'overlay-opacity': 0.2 }},
        ],
        layout: { name: 'cose', animate: false },
        elements: []
    });

    /* ── Helpers ── */
    const $  = s => document.querySelector(s);
    const $$ = s => document.querySelectorAll(s);
    const show = el => el.classList.remove('d-none');
    const hide = el => el.classList.add('d-none');
    let currentLayout = 'cose';

    function runLayout(name, animate = true) {
        currentLayout = name || currentLayout;
        cy.layout({
            name: currentLayout,
            animate,
            animationDuration: 400,
            fit: true,
            padding: 40,
            nodeDimensionsIncludeLabels: true,
            // cose specific
            ...(currentLayout === 'cose' ? { idealEdgeLength: 120, nodeOverlap: 20, nodeRepulsion: 8000 } : {}),
            ...(currentLayout === 'breadthfirst' ? { spacingFactor: 1.5 } : {}),
        }).run();
    }

    function updateStatus() {
        const n = cy.nodes(':visible').length;
        const e = cy.edges(':visible').length;
        $('#graphStatus').textContent = `${n} node${n!==1?'s':''} · ${e} edge${e!==1?'s':''} · zoom ${Math.round(cy.zoom()*100)}%`;
    }

    /* ── Fetch & render subgraph ── */
    async function loadGraph(params = {}) {
        const loading = $('#cy-loading');
        show(loading);
        const key = cacheKey(params);
        let data;
        if (_cache[key]) {
            data = _cache[key];
        } else {
            try {
                const qs = new URLSearchParams();
                Object.entries(params).forEach(([k,v]) => { if (v !== '' && v !== null && v !== undefined) qs.set(k, v); });
                const res = await fetch('{{ route("api.subgraph") }}?' + qs.toString());
                data = await res.json();
                _cache[key] = data;
            } catch (err) {
                console.error('Graph fetch error', err);
                hide(loading);
                return;
            }
        }

        cy.elements().remove();

        const elNodes = (data.elements?.nodes || []).map(n => ({
            data: {
                ...n.data,
                color: n.data.color || typeOf(n.data.type).color,
                icon: n.data.icon || typeOf(n.data.type).icon,
                size: Math.max(1, Math.min(10, (n.data.confidence || 50) / 10)),
                weight: n.data.confidence || 50
            }
        }));
        const elEdges = (data.elements?.edges || []).map(e => ({
            data: {
                ...e.data,
                label: e.data.type || e.data.label || '',
                weight: e.data.confidence || 50
            }
        }));
        cy.add([...elNodes, ...elEdges]);

        populateLegend();
        populateEdgeTypes();
        runLayout(currentLayout, true);
        hide(loading);
        updateStatus();
    }

    /* ── Legend ── */
    function populateLegend() {
        const types = new Set();
        cy.nodes().forEach(n => types.add(n.data('type')));
        const container = $('#legendItems');
        container.innerHTML = '';
        types.forEach(t => {
            const info = typeOf(t);
            const el = document.createElement('div');
            el.className = 'legend-item';
            el.dataset.type = t;
            el.innerHTML = `<span class="legend-dot" style="background:${info.color}"></span><span>${info.label}</span>`;
            el.addEventListener('click', () => toggleType(el, t));
            container.appendChild(el);
        });
    }

    const hiddenTypes = new Set();
    function toggleType(el, type) {
        if (hiddenTypes.has(type)) {
            hiddenTypes.delete(type);
            el.classList.remove('muted');
            cy.nodes(`[type="${type}"]`).style('display', 'element');
        } else {
            hiddenTypes.add(type);
            el.classList.add('muted');
            cy.nodes(`[type="${type}"]`).style('display', 'none');
        }
        cy.edges().forEach(e => {
            const sHidden = hiddenTypes.has(e.source().data('type'));
            const tHidden = hiddenTypes.has(e.target().data('type'));
            e.style('display', (sHidden || tHidden) ? 'none' : 'element');
        });
        updateStatus();
    }

    /* ── Edge types for filter dropdown ── */
    function populateEdgeTypes() {
        const sel = $('#fEdgeType');
        const types = new Set();
        cy.edges().forEach(e => types.add(e.data('type') || e.data('label')));
        // keep first option (All)
        sel.innerHTML = '<option value="">All</option>';
        types.forEach(t => { const o = document.createElement('option'); o.value = t; o.textContent = t; sel.appendChild(o); });
    }

    /* ── Hover tooltip ── */
    let tooltipTimer;
    cy.on('mouseover', 'node', function (e) {
        clearTimeout(tooltipTimer);
        const d = e.target.data();
        const info = typeOf(d.type);
        const tip = $('#cyTooltip');
        tip.innerHTML = `
            <div class="tt-type" style="color:${info.color}"><i class="${info.icon} me-1"></i>${info.label}</div>
            <div class="tt-name">${d.label || d.name || '—'}</div>
            <div class="tt-meta">Confidence: ${d.confidence ?? '—'}% · Severity: ${d.severity ?? '—'}</div>
        `;
        tip.style.display = 'block';
        const pos = e.renderedPosition || e.target.renderedPosition();
        const rect = $('#cy').getBoundingClientRect();
        tip.style.left = (pos.x + 20) + 'px';
        tip.style.top = (pos.y - 10) + 'px';

        // Highlight connected
        cy.elements().addClass('dimmed');
        const hood = e.target.neighborhood().add(e.target);
        hood.removeClass('dimmed').addClass('highlighted');
    });
    cy.on('mouseout', 'node', function () {
        tooltipTimer = setTimeout(() => {
            $('#cyTooltip').style.display = 'none';
            cy.elements().removeClass('dimmed highlighted');
        }, 100);
    });

    /* ── Node click → detail panel ── */
    cy.on('tap', 'node', function (e) {
        const d = e.target.data();
        const info = typeOf(d.type);
        const conf = d.confidence ?? 0;
        const confColor = conf >= 70 ? '#10b981' : conf >= 40 ? '#f59e0b' : '#ef4444';

        const neighbors = e.target.neighborhood('node');
        let neighborHtml = '';
        neighbors.slice(0, 8).forEach(n => {
            const ni = typeOf(n.data('type'));
            neighborHtml += `<span class="neighbor-chip badge me-1 mb-1" style="background:${ni.color}22;color:${ni.color}" data-id="${n.id()}">${n.data('label')}</span>`;
        });
        if (neighbors.length > 8) neighborHtml += `<span class="text-muted" style="font-size:10px">+${neighbors.length - 8} more</span>`;

        $('#panelTitle').innerHTML = `<i class="${info.icon} me-1" style="color:${info.color}"></i>${d.label || d.name}`;
        $('#panelBody').innerHTML = `
            <p class="mb-1"><span class="text-muted">Type:</span> <span class="badge node-type node-type-${d.type}">${info.label}</span></p>
            <p class="mb-1"><span class="text-muted">Confidence:</span> <strong style="color:${confColor}">${conf}%</strong></p>
            <div class="confidence-mini mb-2"><div class="confidence-mini-fill" style="width:${conf}%;background:${confColor}"></div></div>
            <p class="mb-1"><span class="text-muted">Severity:</span> ${d.severity ? `<span class="badge-cti badge-${d.severity}">${d.severity}</span>` : '—'}</p>
            <p class="mb-2 small text-muted">${d.description || 'No description available.'}</p>
            ${neighborHtml ? `<div class="mb-2"><span class="text-muted" style="font-size:10px">NEIGHBORS</span><div class="mt-1">${neighborHtml}</div></div>` : ''}
            <a href="/knowledge/entities/${d.id}" class="btn btn-sm btn-cti-primary w-100"><i class="ri-external-link-line me-1"></i>View Full Detail</a>
        `;
        show($('#nodePanel'));

        // Neighbor chips → focus node
        $$('.neighbor-chip[data-id]').forEach(chip => {
            chip.addEventListener('click', () => {
                const target = cy.getElementById(chip.dataset.id);
                if (target.length) {
                    cy.animate({ center: { eles: target }, zoom: cy.zoom() }, { duration: 300 });
                    target.select();
                    target.emit('tap');
                }
            });
        });
    });

    cy.on('tap', function (e) { if (e.target === cy) hide($('#nodePanel')); });
    $('#panelClose').addEventListener('click', () => hide($('#nodePanel')));

    /* ── Layout switcher ── */
    $('#graphLayout').addEventListener('change', function () { runLayout(this.value); });
    $('#btnFit').addEventListener('click', () => cy.fit(null, 40));
    cy.on('zoom', updateStatus);

    /* ── Zoom buttons ── */
    $('#btnZoomIn').addEventListener('click', () => cy.zoom({ level: cy.zoom() * 1.3, renderedPosition: { x: cy.width()/2, y: cy.height()/2 } }));
    $('#btnZoomOut').addEventListener('click', () => cy.zoom({ level: cy.zoom() / 1.3, renderedPosition: { x: cy.width()/2, y: cy.height()/2 } }));

    /* ── Filter panel toggle ── */
    $('#btnFilter').addEventListener('click', () => {
        $('#filterPanel').classList.toggle('d-none');
    });
    $('#fConfidence').addEventListener('input', function () {
        $('#fConfVal').textContent = this.value + '%';
    });

    /* ── Apply / reset filters ── */
    $('#btnApplyFilter').addEventListener('click', () => {
        const params = {
            depth: $('#fDepth').value,
            confidence_min: $('#fConfidence').value,
            severity: $('#fSeverity').value,
            edge_type: $('#fEdgeType').value,
        };
        loadGraph(params);
        hide($('#filterPanel'));
    });
    $('#btnResetFilter').addEventListener('click', () => {
        $('#fConfidence').value = 0; $('#fConfVal').textContent = '0%';
        $('#fSeverity').value = '';
        $('#fEdgeType').value = '';
        $('#fDepth').value = '2';
        loadGraph({});
        hide($('#filterPanel'));
    });

    /* ── Refresh ── */
    $('#btnRefresh').addEventListener('click', () => {
        Object.keys(_cache).forEach(k => delete _cache[k]);
        loadGraph({});
    });

    /* ── Debounced search ── */
    let searchTimer;
    const searchInput = $('#graphSearch');
    const searchBox = $('#searchResults');

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        const q = this.value.trim();
        if (q.length < 2) { hide(searchBox); return; }
        searchTimer = setTimeout(async () => {
            try {
                const res = await fetch('{{ route("api.graph.search-nodes") }}?q=' + encodeURIComponent(q) + '&limit=10');
                const data = await res.json();
                if (!data.results || !data.results.length) { searchBox.innerHTML = '<div class="sr-item text-muted">No results</div>'; show(searchBox); return; }
                searchBox.innerHTML = data.results.map(n => {
                    const info = typeOf(n.type);
                    return `<div class="sr-item" data-id="${n.id}"><i class="${info.icon} me-1" style="color:${info.color}"></i><strong>${n.name}</strong> <span class="text-muted" style="font-size:10px">${info.label}</span></div>`;
                }).join('');
                show(searchBox);
                searchBox.querySelectorAll('.sr-item[data-id]').forEach(item => {
                    item.addEventListener('click', () => {
                        const nodeId = item.dataset.id;
                        const existing = cy.getElementById(nodeId);
                        if (existing.length) {
                            cy.animate({ center: { eles: existing }, zoom: 1.5 }, { duration: 400 });
                            existing.addClass('search-match');
                            setTimeout(() => existing.removeClass('search-match'), 3000);
                        } else {
                            loadGraph({ node_id: nodeId, depth: 2 });
                        }
                        hide(searchBox);
                        searchInput.value = '';
                    });
                });
            } catch (err) { console.error('Search error', err); }
        }, 300);
    });

    // Ctrl+K shortcut
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
        if (e.key === 'Escape') { hide(searchBox); searchInput.blur(); hide($('#filterPanel')); }
    });

    // Close search on outside click
    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !searchBox.contains(e.target)) hide(searchBox);
    });

    /* ── Initial load ── */
    loadGraph({});
});
</script>
@endsection
