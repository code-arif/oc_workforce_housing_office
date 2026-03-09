@extends('backend.app')

@section('title', 'User Activity')

@section('content')
<!-- Page Content -->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div class="d-flex align-items-center gap-2">
                        <div>
                            <h1 class="page-title">System Monitor</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('system-monitor.index') }}">System Monitor</a></li>
                                    <li class="breadcrumb-item active">User Activity</li>
                                </ol>
                            </nav>
                        </div>

                    </div>
                    
                </div>

                <!-- Activity Summary -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body p-3">
                                <p class="text-muted small mb-1">Active Users (Last 30 Days)</p>
                                <h4 class="mb-0">{{ $activity['total_active_users'] }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body p-3">
                                <p class="text-muted small mb-1">Verified Users</p>
                                <h4 class="mb-0">{{ $activity['total_verified_users'] }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body p-3">
                                <p class="text-muted small mb-1">Logged In Today</p>
                                <h4 class="mb-0">{{ $activity['users_logged_in_today'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Registrations -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Recent User Registrations</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm">
                                        <thead>
                                            <tr>
                                                <th>User Name</th>
                                                <th>Email</th>
                                                <th>Registration Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($activity['recent_registrations'] as $user)
                                                <tr>
                                                    <td>{{ $user->name }}</td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>
                                                        <small class="text-muted">
                                                            {{ $user->created_at->format('M d, Y H:i') }}
                                                            <br>
                                                            <em>({{ $user->created_at->diffForHumans() }})</em>
                                                        </small>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">No recent registrations</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Logins -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Recent Login Activity</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm">
                                        <thead>
                                            <tr>
                                                <th>User Information</th>
                                                <th>Last Login</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($activity['recent_logins'] as $login)
                                                <tr>
                                                    <td>
                                                        @if(isset($login->name))
                                                            <strong>{{ $login->name }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $login->email ?? 'N/A' }}</small>
                                                        @else
                                                            <strong>{{ $login->user_name ?? 'N/A' }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $login->email ?? 'N/A' }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            @if(isset($login->last_login))
                                                                {{ $login->last_login->format('M d, Y H:i') }}
                                                                <br>
                                                                <em>({{ $login->last_login->diffForHumans() }})</em>
                                                            @elseif(isset($login->created_at))
                                                                {{ $login->created_at->format('M d, Y H:i') }}
                                                                <br>
                                                                <em>({{ $login->created_at->diffForHumans() }})</em>
                                                            @else
                                                                N/A
                                                            @endif
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success">Active</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">No login activity found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Timeline -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Activity Summary</h3>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">Active Users</h6>
                                                <small class="text-muted">Users logged in during the last 30 days</small>
                                            </div>
                                            <span class="badge bg-primary">{{ $activity['total_active_users'] }}</span>
                                        </div>
                                    </div>

                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">Total Verified Users</h6>
                                                <small class="text-muted">Users with verified email addresses</small>
                                            </div>
                                            <span class="badge bg-success">{{ $activity['total_verified_users'] }}</span>
                                        </div>
                                    </div>

                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">Today's Logins</h6>
                                                <small class="text-muted">Users who logged in today</small>
                                            </div>
                                            <span class="badge bg-info">{{ $activity['users_logged_in_today'] }}</span>
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

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-refresh every 60 seconds
        setTimeout(() => location.reload(), 60000);
    });
</script>
@endsection
