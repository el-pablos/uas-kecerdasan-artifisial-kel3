@extends('layouts.master-cti')
@section('title', 'Settings — Users')
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box"><h4 class="mb-sm-0 text-white"><i class="ri-group-line me-2"></i> Users & Roles</h4></div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show"><i class="ri-check-line me-1"></i> {{ session('success') }}</div>
            @endif

            {{-- Role Summary --}}
            <div class="row mb-3">
                @foreach($roles as $role)
                    <div class="col-md-4">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1">{{ ucfirst($role->name) }}</p>
                                        <h5 class="mb-0 text-white">{{ $role->users_count ?? $role->users()->count() }}</h5>
                                    </div>
                                    <div class="avatar-sm">
                                        <span class="avatar-title bg-soft-{{ $role->name === 'admin' ? 'danger' : ($role->name === 'analyst' ? 'info' : 'secondary') }} rounded-circle">
                                            <i class="ri-{{ $role->name === 'admin' ? 'shield-star-line' : ($role->name === 'analyst' ? 'search-eye-line' : 'eye-line') }} text-{{ $role->name === 'admin' ? 'danger' : ($role->name === 'analyst' ? 'info' : 'secondary') }}"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-nowrap mb-0">
                                    <thead><tr><th>Name</th><th>Email</th><th>Current Role</th><th>Assign Role</th><th>Joined</th></tr></thead>
                                    <tbody>
                                        @foreach ($users as $user)
                                            <tr>
                                                <td class="fw-medium">{{ $user->name }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    @foreach($user->roles as $role)
                                                        <span class="badge bg-soft-{{ $role->name === 'admin' ? 'danger' : ($role->name === 'analyst' ? 'info' : 'secondary') }} text-{{ $role->name === 'admin' ? 'danger' : ($role->name === 'analyst' ? 'info' : 'secondary') }}">{{ $role->name }}</span>
                                                    @endforeach
                                                    @if($user->roles->isEmpty())<span class="text-muted">No role</span>@endif
                                                </td>
                                                <td>
                                                    <form action="{{ route('settings.users.assign-role', $user) }}" method="POST" class="d-flex gap-1">
                                                        @csrf @method('PUT')
                                                        <select name="role" class="form-select form-select-sm" style="width:auto">
                                                            @foreach($roles as $r)
                                                                <option value="{{ $r->name }}" {{ $user->hasRole($r->name) ? 'selected' : '' }}>{{ ucfirst($r->name) }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button class="btn btn-sm btn-soft-primary"><i class="ri-save-line"></i></button>
                                                    </form>
                                                </td>
                                                <td class="text-muted">{{ $user->created_at->diffForHumans() }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">{{ $users->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
