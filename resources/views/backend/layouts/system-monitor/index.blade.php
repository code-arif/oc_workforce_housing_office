@extends('backend.app')

@section('title', 'System Monitor')

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
                                    <li class="breadcrumb-item active">System Monitor</li>
                                </ol>
                            </nav>
                        </div>

                    </div>
                    
                </div>

                <!-- Health Status Card -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">System Health Status</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @php
                                        $healthStatus = $overview['health_status'];
                                        $overallStatus = $healthStatus['overall_status'];
                                        $statusColor = $overallStatus === 'healthy' ? 'success' : ($overallStatus === 'degraded' ? 'warning' : 'danger');
                                    @endphp

                                    <div class="col-md-12 mb-3">
                                        <div class="alert alert-{{ $statusColor }} d-flex align-items-center">
                                            <div>
                                                <strong>Overall Status: {{ ucfirst($overallStatus) }}</strong>
                                                <p class="mb-0 mt-2">Last Updated: {{ $healthStatus['timestamp']->format('Y-m-d H:i:s') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    @foreach($healthStatus['checks'] as $check => $details)
                                        @php
                                            $status = $details['status'];
                                            $icon = $status === 'healthy' ? 'check-circle' : ($status === 'unhealthy' ? 'alert-circle' : 'alert');
                                            $badgeClass = $status === 'healthy' ? 'bg-success' : ($status === 'unhealthy' ? 'bg-danger' : ($status === 'degraded' ? 'bg-warning' : 'bg-info'));
                                        @endphp
                                        <div class="col-md-6 mb-3">
                                            <div class="card">
                                                <div class="card-body p-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="badge {{ $badgeClass }} rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-{{ $icon }} text-white"></i>
                                                        </div>
                                                        <div class="ms-3">
                                                            <h5 class="mb-0">{{ ucfirst(str_replace('_', ' ', $check)) }}</h5>
                                                            <small class="text-muted">{{ $details['message'] }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Key Metrics Row -->
                <div class="row mb-3">
                    <!-- System Resources -->
                    <div class="col-md-3">
                        <a href="{{ route('system-monitor.resources') }}" class="card text-decoration-none h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="bg-info-lt p-3 rounded">
                                        <i class="fas fa-server text-info fa-2x"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="card-title mb-1">System Resources</h6>
                                        <p class="text-muted mb-0">CPU, Memory, Disk</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Database Info -->
                    <div class="col-md-3">
                        <a href="{{ route('system-monitor.database') }}" class="card text-decoration-none h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success-lt p-3 rounded">
                                        <i class="fas fa-database text-success fa-2x"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="card-title mb-1">Database</h6>
                                        <p class="text-muted mb-0">{{ $overview['database_info']['table_count'] ?? 0 }} Tables</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- App Statistics -->
                    <div class="col-md-3">
                        <a href="{{ route('system-monitor.applications') }}" class="card text-decoration-none h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="bg-warning-lt p-3 rounded">
                                        <i class="fas fa-chart-bar text-warning fa-2x"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="card-title mb-1">Application</h6>
                                        <p class="text-muted mb-0">{{ $overview['application_stats']['total_users'] ?? 0 }} Users</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Security -->
                    <div class="col-md-3">
                        <a href="{{ route('system-monitor.security') }}" class="card text-decoration-none h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="bg-danger-lt p-3 rounded">
                                        <i class="fas fa-shield-alt text-danger fa-2x"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="card-title mb-1">Security</h6>
                                        <p class="text-muted mb-0">Environment: {{ config('app.env') }}</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Statistics Row -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Application Statistics</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @php
                                        $stats = $overview['application_stats'];
                                        $statsList = [
                                            'Users' => ['icon' => 'users', 'color' => 'primary', 'value' => $stats['total_users']],
                                            'Active Users' => ['icon' => 'user-check', 'color' => 'success', 'value' => $stats['active_users']],
                                            'Tenants' => ['icon' => 'home', 'color' => 'info', 'value' => $stats['total_tenants']],
                                            'Properties' => ['icon' => 'building', 'color' => 'warning', 'value' => $stats['total_properties']],
                                            'Active Leases' => ['icon' => 'file-contract', 'color' => 'success', 'value' => $stats['active_leases']],
                                            'Pending Invoices' => ['icon' => 'file-invoice', 'color' => 'danger', 'value' => $stats['pending_invoices']],
                                        ];
                                    @endphp

                                    @foreach($statsList as $label => $data)
                                        <div class="col-md-4 col-lg-2 mb-3">
                                            <div class="card h-100">
                                                <div class="card-body p-3">
                                                    <div class="text-center">
                                                        <div class="mb-2">
                                                            <i class="fas fa-{{ $data['icon'] }} text-{{ $data['color'] }} fa-2x"></i>
                                                        </div>
                                                        <h3 class="mb-1">{{ number_format($data['value']) }}</h3>
                                                        <p class="text-muted small mb-0">{{ $label }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Storage & Logs Row -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Storage Information</h3>
                            </div>
                            <div class="card-body">
                                @php $storage = $overview['storage_info'] @endphp
                                <div class="mb-3">
                                    <h6>Application Storage</h6>
                                    <p class="text-muted">{{ $storage['app_storage']['size_formatted'] }}</p>
                                </div>
                                <div class="mb-3">
                                    <h6>Public Uploads</h6>
                                    <p class="text-muted">{{ $storage['public_uploads']['size_formatted'] }}</p>
                                </div>
                                <div class="mb-3">
                                    <h6>Logs</h6>
                                    <p class="text-muted">{{ $storage['logs']['size_formatted'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">System Information</h3>
                            </div>
                            <div class="card-body">
                                @php $health = $overview['health_status'] @endphp
                                @foreach($health['checks'] as $check => $details)
                                    @if($check !== 'database' && $check !== 'cache' && $check !== 'queue')
                                        <div class="mb-2">
                                            <strong>{{ ucfirst(str_replace('_', ' ', $check)) }}:</strong>
                                            <span class="float-end text-muted">{{ $details['message'] }}</span>
                                        </div>
                                        <hr class="my-2">
                                    @endif
                                @endforeach
                                <div class="mb-2">
                                    <strong>App URL:</strong>
                                    <span class="float-end text-muted small">{{ config('app.url') }}</span>
                                </div>
                                <div class="mb-2">
                                    <strong>Server Time:</strong>
                                    <span class="float-end text-muted small">{{ now()->format('Y-m-d H:i:s') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Logs Row -->
                @if(!empty($overview['recent_errors']['recent_errors']))
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Recent Errors
                                        <span class="badge bg-danger float-end">
                                            {{ count($overview['recent_errors']['recent_errors']) }} Errors
                                        </span>
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Timestamp</th>
                                                    <th>Message</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($overview['recent_errors']['recent_errors'] as $error)
                                                    <tr>
                                                        <td><small class="text-muted">{{ $error['timestamp'] }}</small></td>
                                                        <td><small>{{ substr($error['message'], 0, 100) }}...</small></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <a href="{{ route('system-monitor.errors') }}" class="btn btn-sm btn-outline-primary">
                                        View All Errors
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-refresh every 30 seconds
        setTimeout(() => location.reload(), 30000);
    });
</script>
@endsection
