@extends('layouts.master-cti')
@section('title', 'Create Entity')
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 text-white"><i class="ri-add-circle-line me-2"></i> Create Entity</h4>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            @if($errors->any())
                                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                            @endif
                            <form action="{{ route('knowledge.entities.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Type <span class="text-danger">*</span></label>
                                        <select name="type" class="form-select" required>
                                            @foreach(['threat-actor','malware','campaign','intrusion-set','tool','technique','vulnerability','observable','indicator','sighting','infrastructure','identity','location'] as $t)
                                                <option value="{{ $t }}" {{ old('type') == $t ? 'selected' : '' }}>{{ ucfirst(str_replace('-', ' ', $t)) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Confidence (0-100)</label>
                                        <input type="number" name="confidence" class="form-control" min="0" max="100" value="{{ old('confidence', 50) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Severity</label>
                                        <select name="severity" class="form-select">
                                            @foreach(['low','medium','high','critical'] as $s)
                                                <option value="{{ $s }}" {{ old('severity','medium') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Source Reference</label>
                                        <input type="text" name="source_ref" class="form-control" value="{{ old('source_ref') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">First Seen</label>
                                        <input type="date" name="first_seen" class="form-control" value="{{ old('first_seen') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Last Seen</label>
                                        <input type="date" name="last_seen" class="form-control" value="{{ old('last_seen') }}">
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Create</button>
                                    <a href="{{ route('knowledge.entities.index') }}" class="btn btn-soft-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
