@extends('backend.app')

@section('title', 'System Health Status')

@section('content')
<!-- Page Content -->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div class="d-flex align-items-center gap-2">
                        <div>
                            <h1 class="page-title">System Health Status</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('system-monitor.index') }}">System Monitor</a></li>
                                    <li class="breadcrumb-item active">System Health Status</li>
                                </ol>
                            </nav>
                        </div>

                    </div>
                    
                </div>

                <!-- Overall Health Status -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Overall System Health</h3>
                            </div>
                            <div class="card-body">
                                @php
                                    $overallStatus = $health['overall_status'];
                                    $statusColor = $overallStatus === 'healthy' ? 'success' : ($overallStatus === 'degraded' ? 'warning' : 'danger');
                                    $statusIcon = $overallStatus === 'healthy' ? 'check-circle' : ($overallStatus === 'degraded' ? 'alert-circle' : 'times-circle');
                                @endphp
                                <div class="alert alert-{{ $statusColor }} d-flex align-items-center" style="padding: 20px;">
                                    <i class="fas fa-{{ $statusIcon }} fa-2x me-3"></i>
                                    <div>
                                        <h4 class="mb-2">{{ ucfirst($overallStatus) }}</h4>
                                        <p class="mb-0">Last Updated: {{ $health['timestamp']->format('Y-m-d H:i:s') }}</p>
                                        @if($overallStatus === 'healthy')
                                            <small class="text-{{ $statusColor }}">All systems are operating normally.</small>
                                        @elseif($overallStatus === 'degraded')
                                            <small class="text-{{ $statusColor }}">Some systems may have reduced performance.</small>
                                        @else
                                            <small class="text-{{ $statusColor }}">Some critical systems are experiencing issues.</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Health Checks Grid -->
                <div class="row">
                    @php
                        $statusMap = [
                            'healthy' => ['color' => 'success', 'icon' => 'check-circle', 'text' => 'Healthy'],
                            'degraded' => ['color' => 'warning', 'icon' => 'alert-circle', 'text' => 'Degraded'],
                            'unhealthy' => ['color' => 'danger', 'icon' => 'times-circle', 'text' => 'Unhealthy'],
                            'info' => ['color' => 'info', 'icon' => 'info-circle', 'text' => 'Info'],
                        ];
                    @endphp

                    @foreach($health['checks'] as $checkName => $checkDetails)
                        @php
                            $status = $checkDetails['status'] ?? 'info';
                            $statusInfo = $statusMap[$status] ?? $statusMap['info'];
                        @endphp
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-start">
                                        <div class="badge bg-{{ $statusInfo['color'] }} rounded-circle me-3"
                                             style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-{{ $statusInfo['icon'] }} fa-lg" style="color: white;"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="card-title mb-1">
                                                {{ ucfirst(str_replace('_', ' ', $checkName)) }}
                                            </h5>
                                            <p class="text-muted small mb-2">{{ $checkDetails['message'] ?? 'No message' }}</p>
                                            <span class="badge bg-{{ $statusInfo['color'] }}">
                                                {{ $statusInfo['text'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Health Check Details -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Health Check Details</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Component</th>
                                                <th>Status</th>
                                                <th>Message</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($health['checks'] as $checkName => $checkDetails)
                                                @php
                                                    $status = $checkDetails['status'] ?? 'info';
                                                    $badgeColor = $status === 'healthy' ? 'success' : ($status === 'unhealthy' ? 'danger' : ($status === 'degraded' ? 'warning' : 'info'));
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <strong>{{ ucfirst(str_replace('_', ' ', $checkName)) }}</strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $badgeColor }}">
                                                            {{ ucfirst($status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">{{ $checkDetails['message'] ?? 'N/A' }}</small>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">System Environment Information</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($health['checks'] as $checkName => $checkDetails)
                                        @if(in_array($checkName, ['laravel', 'php']))
                                            <div class="col-md-6 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body p-3">
                                                        <h6>{{ ucfirst($checkName) }}</h6>
                                                        <p class="mb-0 text-muted">{{ $checkDetails['message'] }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Health Status Legend -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Status Legend</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="badge bg-success rounded-circle me-2" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-check text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">Healthy</h6>
                                                <small class="text-muted">System operating normally</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="badge bg-warning rounded-circle me-2" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-exclamation text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">Degraded</h6>
                                                <small class="text-muted">Reduced performance</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="badge bg-danger rounded-circle me-2" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-times text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">Unhealthy</h6>
                                                <small class="text-muted">Critical issues</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="badge bg-info rounded-circle me-2" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-info text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">Info</h6>
                                                <small class="text-muted">Informational</small>
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

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-refresh every 30 seconds
        setTimeout(() => location.reload(), 30000);
    });
</script>
@endsection
