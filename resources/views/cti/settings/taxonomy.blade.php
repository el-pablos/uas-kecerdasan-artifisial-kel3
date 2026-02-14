@extends('layouts.master-cti')
@section('title', 'Settings — Taxonomy')
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box"><h4 class="mb-sm-0 text-white"><i class="ri-price-tag-3-line me-2"></i> Taxonomy & Tags</h4></div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">Create Tag</h6></div>
                        <div class="card-body">
                            @if($errors->any())
                                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                            @endif
                            <form action="{{ route('settings.taxonomy.store') }}" method="POST">
                                @csrf
                                <div class="row g-2">
                                    <div class="col-7">
                                        <input type="text" name="name" class="form-control form-control-sm" placeholder="Tag name" required>
                                    </div>
                                    <div class="col-3">
                                        <input type="color" name="color" class="form-control form-control-sm form-control-color" value="#6366f1">
                                    </div>
                                    <div class="col-2">
                                        <button class="btn btn-sm btn-primary w-100"><i class="ri-add-line"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">All Tags</h6></div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-2">
                                @forelse ($tags as $tag)
                                    <div class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded" style="background: {{ $tag->color }}22; border: 1px solid {{ $tag->color }}44;">
                                        <span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:{{ $tag->color }}"></span>
                                        <span style="color: {{ $tag->color }}">{{ $tag->name }}</span>
                                        <form action="{{ route('settings.taxonomy.destroy', $tag) }}" method="POST" class="ms-1" onsubmit="return confirm('Delete tag?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-link btn-sm p-0 text-danger"><i class="ri-close-line" style="font-size:12px"></i></button>
                                        </form>
                                    </div>
                                @empty
                                    <p class="text-muted">No tags created yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
