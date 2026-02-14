@extends('layouts.master-cti')
@section('title', 'Knowledge Graph Explorer')
@section('css')
<style>
    #cy {
        width: 100%;
        height: 600px;
        background: var(--cti-bg-primary);
        border: 1px solid var(--cti-border);
        border-radius: 8px;
    }
    .graph-controls {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 10;
        display: flex;
        gap: 4px;
    }
</style>
@endsection
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 text-white"><i class="ri-mind-map me-2 text-cyan"></i> Knowledge Graph Explorer</h4>
                        <div class="d-flex gap-2">
                            <select id="graphLayout" class="form-select form-select-sm" style="width:auto">
                                <option value="cose">Force-directed (CoSE)</option>
                                <option value="circle">Circle</option>
                                <option value="grid">Grid</option>
                                <option value="breadthfirst">Hierarchy</option>
                                <option value="concentric">Concentric</option>
                            </select>
                            <button id="btnFit" class="btn btn-sm btn-soft-info"><i class="ri-fullscreen-line"></i> Fit</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 position-relative">
                    <div id="cy"></div>
                    {{-- Node detail panel --}}
                    <div id="nodePanel" class="card position-absolute top-0 start-0 m-2 d-none" style="width:300px;z-index:20;">
                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                            <h6 class="mb-0" id="panelTitle">—</h6>
                            <button class="btn btn-sm btn-soft-secondary" onclick="document.getElementById('nodePanel').classList.add('d-none')"><i class="ri-close-line"></i></button>
                        </div>
                        <div class="card-body py-2" id="panelBody"></div>
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
    const cy = cytoscape({
        container: document.getElementById('cy'),
        style: [
            { selector: 'node', style: {
                'background-color': 'data(color)',
                'label': 'data(label)',
                'color': '#e2e8f0',
                'font-size': '11px',
                'text-valign': 'bottom',
                'text-margin-y': 6,
                'width': 36, 'height': 36,
                'border-width': 2,
                'border-color': 'data(color)',
                'text-outline-width': 2,
                'text-outline-color': '#0a0e1a'
            }},
            { selector: 'edge', style: {
                'width': 2,
                'line-color': '#4b5563',
                'target-arrow-color': '#6b7280',
                'target-arrow-shape': 'triangle',
                'curve-style': 'bezier',
                'label': 'data(label)',
                'font-size': '9px',
                'color': '#94a3b8',
                'text-rotation': 'autorotate',
                'text-outline-width': 2,
                'text-outline-color': '#0a0e1a'
            }},
            { selector: 'node:selected', style: {
                'border-color': '#6366f1',
                'border-width': 4,
                'overlay-color': '#6366f1',
                'overlay-opacity': 0.2
            }}
        ],
        layout: { name: 'cose', animate: true, animationDuration: 500 },
        elements: []
    });

    // Load data
    fetch('{{ route("api.subgraph") }}')
        .then(r => r.json())
        .then(data => {
            const elements = [];
            (data.nodes || []).forEach(n => {
                elements.push({ data: { id: n.id, label: n.name, color: n.color || '#6366f1', type: n.type, nodeData: n }});
            });
            (data.edges || []).forEach(e => {
                elements.push({ data: { id: e.id, source: e.from_node_id, target: e.to_node_id, label: e.type }});
            });
            cy.add(elements);
            cy.layout({ name: 'cose', animate: true, animationDuration: 500 }).run();
        });

    // Node click → detail panel
    cy.on('tap', 'node', function (e) {
        const d = e.target.data('nodeData');
        const panel = document.getElementById('nodePanel');
        document.getElementById('panelTitle').innerHTML = `<i class="${d.icon || 'ri-question-line'} me-1"></i>${d.name}`;
        document.getElementById('panelBody').innerHTML = `
            <p class="mb-1"><span class="text-muted">Type:</span> <span class="badge" style="background:${d.color}22;color:${d.color}">${d.type}</span></p>
            <p class="mb-1"><span class="text-muted">Confidence:</span> ${d.confidence ?? '—'}%</p>
            <p class="mb-1"><span class="text-muted">Severity:</span> ${d.severity ?? '—'}</p>
            <p class="mb-2 small text-muted">${d.description || 'No description'}</p>
            <a href="/knowledge/entities/${d.id}" class="btn btn-sm btn-soft-info w-100">View Full Detail</a>
        `;
        panel.classList.remove('d-none');
    });

    cy.on('tap', function (e) { if (e.target === cy) document.getElementById('nodePanel').classList.add('d-none'); });

    // Layout switcher
    document.getElementById('graphLayout').addEventListener('change', function () {
        cy.layout({ name: this.value, animate: true, animationDuration: 500 }).run();
    });
    document.getElementById('btnFit').addEventListener('click', () => cy.fit(null, 30));
});
</script>
@endsection
