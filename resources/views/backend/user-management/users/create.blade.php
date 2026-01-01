@extends('backend.app')

@section('title', 'Add New User')

@section('content')
<div class="app-content content">
    <div class="content-wrapper">
        <!-- PAGE HEADER -->
        <div class="page-header">
            <div class="page-title">
                <h4>Add New User</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('user-management.users.index') }}">Users</a></li>
                        <li class="breadcrumb-item active">Add New</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- /PAGE HEADER -->

        <!-- FORM -->
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Validation errors:</strong>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('user-management.users.store') }}" method="POST">
                            @csrf

                            <div class="form-group mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                    id="phone" name="phone" value="{{ old('phone') }}">
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" required>
                                @error('password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control"
                                    id="password_confirmation" name="password_confirmation" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="roles" class="form-label">Assign Roles <span class="text-danger">*</span></label>
                                <div class="roles-container">
                                    @foreach ($roles as $role)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="roles[]"
                                                value="{{ $role->id }}" id="role_{{ $role->id }}"
                                                @if(in_array($role->id, old('roles', []))) checked @endif>
                                            <label class="form-check-label" for="role_{{ $role->id }}">
                                                {{ $role->display_name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('roles')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary me-2">Create User</button>
                                <a href="{{ route('user-management.users.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /FORM -->
    </div>
</div>
@endsection
