@extends('layouts.master-cti')
@section('title', 'Import STIX Bundle')
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box"><h4 class="mb-sm-0 text-white"><i class="ri-upload-2-line me-2 text-primary"></i> Import STIX 2.1 Bundle</h4></div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif
                            @if($errors->any())
                                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                            @endif
                            <form action="{{ route('ingestion.import.stix') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">STIX Bundle JSON File</label>
                                    <input type="file" name="bundle" class="form-control" accept=".json" required>
                                    <small class="text-muted">Upload a STIX 2.1 bundle JSON file. Objects will be mapped to Knowledge Graph entities.</small>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="ri-upload-2-line me-1"></i> Import</button>
                            </form>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">Supported STIX Object Types</h6></div>
                        <div class="card-body">
                            <div class="row g-2">
                                @foreach(['threat-actor','malware','campaign','intrusion-set','tool','attack-pattern → technique','vulnerability','indicator','identity','infrastructure','ipv4-addr → observable','domain-name → observable','url → observable','relationship → edges'] as $t)
                                    <div class="col-md-3"><span class="badge bg-soft-primary text-primary w-100 py-2">{{ $t }}</span></div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
