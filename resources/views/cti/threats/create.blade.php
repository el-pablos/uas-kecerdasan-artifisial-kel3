@extends('layouts.master-cti')
@section('title', 'Create ' . ucfirst(str_replace('-', ' ', $type)))
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 text-white">
                            <i class="ri-add-circle-line me-2 text-primary"></i>
                            Create {{ ucfirst(str_replace('-', ' ', $type)) }}
                        </h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('threats.' . str_replace('_', '-', Str::plural($type == 'intrusion-set' ? 'intrusion-sets' : $type)) . '.store') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
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
                                            <option value="low" {{ old('severity') == 'low' ? 'selected' : '' }}>Low</option>
                                            <option value="medium" {{ old('severity', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                                            <option value="high" {{ old('severity') == 'high' ? 'selected' : '' }}>High</option>
                                            <option value="critical" {{ old('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Source Reference</label>
                                        <input type="text" name="source_ref" class="form-control" value="{{ old('source_ref') }}" placeholder="e.g. MITRE ATT&CK ID">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Aliases (comma-separated)</label>
                                    <input type="text" name="aliases" class="form-control" value="{{ old('aliases') }}" placeholder="e.g. APT28, Fancy Bear">
                                </div>

                                @if($type === 'threat-actor')
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Sophistication</label>
                                        <select name="sophistication" class="form-select">
                                            @foreach(['none','minimal','intermediate','advanced','expert','innovator','strategic'] as $s)
                                                <option value="{{ $s }}" {{ old('sophistication') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Goals</label>
                                        <input type="text" name="goals" class="form-control" value="{{ old('goals') }}" placeholder="e.g. Espionage, Financial Gain">
                                    </div>
                                </div>
                                @endif

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
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-save-line me-1"></i> Create
                                    </button>
                                    <a href="javascript:history.back()" class="btn btn-soft-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
