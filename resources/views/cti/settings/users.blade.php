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
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-nowrap mb-0">
                                    <thead><tr><th>Name</th><th>Email</th><th>Roles</th><th>Joined</th></tr></thead>
                                    <tbody>
                                        @foreach ($users as $user)
                                            <tr>
                                                <td class="fw-medium">{{ $user->name }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    @foreach($user->roles as $role)
                                                        <span class="badge bg-soft-info text-info">{{ $role->name }}</span>
                                                    @endforeach
                                                    @if($user->roles->isEmpty())<span class="text-muted">—</span>@endif
                                                </td>
                                                <td>{{ $user->created_at->diffForHumans() }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
