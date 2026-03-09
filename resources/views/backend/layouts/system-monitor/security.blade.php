@extends('backend.app')

@section('title', 'Security Overview')

@section('content')
<!-- Page Content -->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div class="d-flex align-items-center gap-2">
                        <div>
                            <h1 class="page-title">System Security Overview</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('system-monitor.index') }}">System Monitor</a></li>
                                    <li class="breadcrumb-item active">Security Overview</li>
                                </ol>
                            </nav>
                        </div>

                    </div>
                    
                </div>

                <!-- Security Status -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Security Configuration</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="card">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center mb-3">
                                                    @if($security['https_enabled'])
                                                        <div class="badge bg-success rounded-circle me-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-lock text-white"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0">HTTPS Enabled</h6>
                                                            <small class="text-success">✓ Secure connection</small>
                                                        </div>
                                                    @else
                                                        <div class="badge bg-danger rounded-circle me-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-lock-open text-white"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0">HTTPS Disabled</h6>
                                                            <small class="text-danger">✗ Not using HTTPS</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="card">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center mb-3">
                                                    @if(!$security['app_debug'])
                                                        <div class="badge bg-success rounded-circle me-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-shield-alt text-white"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0">Debug Mode Disabled</h6>
                                                            <small class="text-success">✓ Production ready</small>
                                                        </div>
                                                    @else
                                                        <div class="badge bg-warning rounded-circle me-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-exclamation text-white"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0">Debug Mode Enabled</h6>
                                                            <small class="text-warning">⚠ Development mode</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body p-3">
                                                <p class="text-muted small mb-1">Environment</p>
                                                <h5>{{ ucfirst($security['app_env']) }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body p-3">
                                                <p class="text-muted small mb-1">Superadmin Count</p>
                                                <h5>{{ $security['superadmin_count'] }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body p-3">
                                                <p class="text-muted small mb-1">Admin Count</p>
                                                <h5>{{ $security['admin_count'] }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Suspended Users -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    Unverified/Suspended Users
                                    <span class="badge bg-warning float-end">{{ $security['suspended_users'] }}</span>
                                </h3>
                            </div>
                            <div class="card-body">
                                @if($security['suspended_users'] > 0)
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        There are {{ $security['suspended_users'] }} users with unverified email addresses.
                                    </div>
                                @else
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle me-2"></i>
                                        All users have verified email addresses.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Password Updates -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Recent User Activity</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm">
                                        <thead>
                                            <tr>
                                                <th>User Name</th>
                                                <th>Email</th>
                                                <th>Last Updated</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($security['latest_password_changes'] as $user)
                                                <tr>
                                                    <td>{{ $user->name }}</td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>
                                                        <small class="text-muted">
                                                            {{ $user->updated_at->diffForHumans() }}
                                                        </small>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">No users found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Recommendations -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Security Recommendations</h3>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item">
                                        @if($security['https_enabled'])
                                            <div class="d-flex align-items-center">
                                                <div class="badge bg-success">✓</div>
                                                <div class="ms-3">
                                                    <h6 class="mb-0">HTTPS Enabled</h6>
                                                    <small class="text-muted">Your application is using HTTPS encryption.</small>
                                                </div>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-center">
                                                <div class="badge bg-danger">✗</div>
                                                <div class="ms-3">
                                                    <h6 class="mb-0">HTTPS Not Enabled</h6>
                                                    <small class="text-muted">Configure your application to use HTTPS for security.</small>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="list-group-item">
                                        @if(!$security['app_debug'])
                                            <div class="d-flex align-items-center">
                                                <div class="badge bg-success">✓</div>
                                                <div class="ms-3">
                                                    <h6 class="mb-0">Debug Mode Disabled</h6>
                                                    <small class="text-muted">Your application is not exposing debug information.</small>
                                                </div>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-center">
                                                <div class="badge bg-danger">✗</div>
                                                <div class="ms-3">
                                                    <h6 class="mb-0">Debug Mode Enabled</h6>
                                                    <small class="text-muted">Disable debug mode in production to prevent information leakage.</small>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="list-group-item">
                                        <div class="d-flex align-items-center">
                                            <div class="badge bg-info">ℹ</div>
                                            <div class="ms-3">
                                                <h6 class="mb-0">Regular Security Audits</h6>
                                                <small class="text-muted">Perform regular security audits and update dependencies.</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="list-group-item">
                                        <div class="d-flex align-items-center">
                                            <div class="badge bg-info">ℹ</div>
                                            <div class="ms-3">
                                                <h6 class="mb-0">User Access Control</h6>
                                                <small class="text-muted">Review user permissions and remove inactive admin accounts.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
