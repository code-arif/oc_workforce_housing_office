@extends('backend.app')

@section('title', 'Roles Management')

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">
            <!-- PAGE HEADER -->
            <div class="page-header">
                <div class="page-title">
                    <h4>Roles Management</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Roles</li>
                        </ol>
                    </nav>
                </div>
                <div class="page-btn">
                    <a href="{{ route('user-management.roles.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Add New Role
                    </a>
                </div>
            </div>
            <!-- /PAGE HEADER -->
            
            <!-- TABLE -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            @if ($message = Session::get('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ $message }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if ($message = Session::get('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ $message }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Role Name</th>
                                            <th>Display Name</th>
                                            <th>Description</th>
                                            <th>Permissions</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($roles as $role)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td><strong>{{ $role->name }}</strong></td>
                                                <td>{{ $role->display_name }}</td>
                                                <td>{{ $role->description ?? 'N/A' }}</td>
                                                <td>
                                                    <small>{{ $role->permissions->count() }} permission(s)</small>
                                                </td>
                                                <td>
                                                    <a href="{{ route('user-management.roles.edit', $role) }}"
                                                        class="btn btn-sm btn-info me-2">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    @if ($role->name !== 'admin')
                                                        <form action="{{ route('user-management.roles.destroy', $role) }}"
                                                            method="POST" style="display:inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger"
                                                                onclick="return confirm('Are you sure?')">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No roles found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center">
                                {{ $roles->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /TABLE -->
        </div>
    </div>
</div>
@endsection
