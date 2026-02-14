@extends('layouts.master-cti')
@section('title', 'Settings — API Tokens')
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box"><h4 class="mb-sm-0 text-white"><i class="ri-key-2-line me-2"></i> API Tokens</h4></div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">Create Token</h6></div>
                        <div class="card-body">
                            @if(session('newToken'))
                                <div class="alert alert-success">
                                    <strong>Token created!</strong> Copy it now — it won't be shown again.<br>
                                    <code class="d-block mt-2 p-2 bg-dark rounded">{{ session('newToken') }}</code>
                                </div>
                            @endif
                            <form action="{{ route('settings.tokens.create') }}" method="POST">
                                @csrf
                                <div class="input-group">
                                    <input type="text" name="name" class="form-control" placeholder="Token name (e.g. CI Pipeline)" required>
                                    <button class="btn btn-primary"><i class="ri-add-line"></i> Generate</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">Active Tokens</h6></div>
                        <div class="card-body">
                            @forelse ($tokens as $token)
                                <div class="d-flex align-items-center py-2 border-bottom border-dark">
                                    <i class="ri-key-line text-warning me-2"></i>
                                    <span class="flex-grow-1">{{ $token->name }}</span>
                                    <small class="text-muted me-3">{{ $token->created_at->diffForHumans() }}</small>
                                    <form action="{{ route('settings.tokens.revoke', $token->id) }}" method="POST" onsubmit="return confirm('Revoke?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-soft-danger"><i class="ri-close-line"></i></button>
                                    </form>
                                </div>
                            @empty
                                <p class="text-muted text-center py-3">No active tokens.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
