@extends('backend.app')

@section('title', 'Edit Role')

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">
            <!-- PAGE HEADER -->
            <div class="page-header">
                <div class="page-title">
                    <h4>Edit Role</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('user-management.roles.index') }}">Roles</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- /PAGE HEADER -->

            <!-- FORM -->
            <div class="row">
                <div class="col-md-12">
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

                            <form action="{{ route('user-management.roles.update', $role) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                id="name" name="name" value="{{ old('name', $role->name) }}" required>
                                            @error('name')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="display_name" class="form-label">Display Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('display_name') is-invalid @enderror"
                                                id="display_name" name="display_name" value="{{ old('display_name', $role->display_name) }}" required>
                                            @error('display_name')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description" rows="3">{{ old('description', $role->description) }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">Assign Permissions <span class="text-danger">*</span></label>
                                    <div class="card">
                                        <div class="card-body">
                                            @foreach ($permissions as $module => $perms)
                                                <div class="mb-4">
                                                    <h6 class="text-uppercase fw-bold mb-3">
                                                        <label class="form-check-label">
                                                        <input class="form-check-input module-select-all mt-0" type="checkbox" id="all_perm_{{ $module }}"
                                                            data-module="{{ $module }}"
                                                            onchange="selectAllPermissions(this, '{{ $module }}')">
                                                            <i class="fa fa-folder"></i> {{ $module }}
                                                        </label>
                                                    </h6>
                                                    <div class="row">
                                                        @foreach ($perms as $permission)
                                                            <div class="col-md-6 col-lg-4 mb-2">
                                                                {{-- <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" name="permissions[]"
                                                                        value="{{ $permission->name }}" id="perm_{{ $permission->id }}"
                                                                        @if($role->hasPermissionTo($permission)) checked @endif>
                                                                    <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                                        {{ $permission->display_name ?? $permission->name }}
                                                                    </label>
                                                                </div> --}}
                                                                <div class="form-check">
                                                                    <input class="form-check-input module-permission" type="checkbox" name="permissions[]"
                                                                        value="{{ $permission->name }}" id="perm_{{ $permission->id }}"
                                                                        data-module="{{ $module }}"
                                                                        onchange="updateModuleSelectAll('{{ $module }}')"
                                                                        @if($role->hasPermissionTo($permission)) checked @endif>>
                                                                    <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                                        {{ strtoupper($permission->display_name) ?? $permission->name }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <hr>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @error('permissions')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary me-2">Update Role</button>
                                    <a href="{{ route('user-management.roles.index') }}" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /FORM -->
        </div>
    </div>
</div>
<script>
// Select all permissions for a module
function selectAllPermissions(checkbox, module) {
    const modulePermissions = document.querySelectorAll(`.module-permission[data-module="${module}"]`);
    modulePermissions.forEach(permission => {
        permission.checked = checkbox.checked;
    });
}

// Update the "select all" checkbox state based on individual permissions
function updateModuleSelectAll(module) {
    const modulePermissions = document.querySelectorAll(`.module-permission[data-module="${module}"]`);
    const selectAllCheckbox = document.querySelector(`.module-select-all[data-module="${module}"]`);
    
    const allChecked = Array.from(modulePermissions).every(checkbox => checkbox.checked);
    const someChecked = Array.from(modulePermissions).some(checkbox => checkbox.checked);
    
    if (allChecked) {
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
    } else if (someChecked) {
        selectAllCheckbox.indeterminate = true;
        selectAllCheckbox.checked = false;
    } else {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const modules = Array.from(document.querySelectorAll('.module-select-all')).map(el => el.dataset.module);
    modules.forEach(module => updateModuleSelectAll(module));
});
</script>
@endsection
