@extends('backend.app')

@section('title', 'System Errors & Logs')

@section('content')
<!-- Page Content -->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div class="d-flex align-items-center gap-2">
                        <div>
                            <h1 class="page-title">System Errors & Logs</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('system-monitor.index') }}">System Monitor</a></li>
                                    <li class="breadcrumb-item active">System Errors & Logs</li>
                                </ol>
                            </nav>
                        </div>

                    </div>
                    
                </div>

                <!-- Error Statistics -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Error Statistics</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-body p-3">
                                                <p class="text-muted small mb-1">Recent Errors Found</p>
                                                <h4>{{ $errors['total_errors'] }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-body p-3">
                                                <p class="text-muted small mb-1">Log File Size</p>
                                                <h4>{{ $errors['log_file_size'] }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Errors -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    Recent Error Logs
                                    @if(count($errors['recent_errors']) > 0)
                                        <span class="badge bg-danger float-end">{{ count($errors['recent_errors']) }} Errors</span>
                                    @else
                                        <span class="badge bg-success float-end">No Errors</span>
                                    @endif
                                </h3>
                            </div>
                            <div class="card-body">
                                @if(count($errors['recent_errors']) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Timestamp</th>
                                                    <th>Error Message</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($errors['recent_errors'] as $error)
                                                    <tr>
                                                        <td>
                                                            <small class="text-muted">
                                                                {{ $error['timestamp'] }}
                                                            </small>
                                                        </td>
                                                        <td>
                                                            <small class="text-danger">
                                                                {{ substr($error['message'], 0, 150) }}
                                                                @if(strlen($error['message']) > 150)
                                                                    ...
                                                                @endif
                                                            </small>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-success mb-0 d-flex align-items-center">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <span>No errors detected! Your application is running smoothly.</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Information -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Error Log Information</h3>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">Log File Location</h6>
                                                <small class="text-muted">
                                                    <code>storage/logs/laravel.log</code>
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">Error Types Monitored</h6>
                                                <small class="text-muted">
                                                    [ERROR], [EXCEPTION], [CRITICAL], [WARNING]
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">Log Level Setting</h6>
                                                <small class="text-muted">
                                                    Configured in <code>.env</code> (LOG_LEVEL)
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recommendations -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Troubleshooting Tips</h3>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item">
                                        <div class="d-flex align-items-start">
                                            <div class="badge bg-primary me-3 mt-1">1</div>
                                            <div>
                                                <h6 class="mb-1">Check Log Files</h6>
                                                <small class="text-muted">
                                                    Review the error logs in <code>storage/logs/</code> to identify error patterns.
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="list-group-item">
                                        <div class="d-flex align-items-start">
                                            <div class="badge bg-primary me-3 mt-1">2</div>
                                            <div>
                                                <h6 class="mb-1">Enable Error Tracking</h6>
                                                <small class="text-muted">
                                                    Consider using error tracking services like Sentry for detailed error monitoring.
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="list-group-item">
                                        <div class="d-flex align-items-start">
                                            <div class="badge bg-primary me-3 mt-1">3</div>
                                            <div>
                                                <h6 class="mb-1">Clear Old Logs</h6>
                                                <small class="text-muted">
                                                    Run <code>php artisan logs:clear</code> to clean up old log files periodically.
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="list-group-item">
                                        <div class="d-flex align-items-start">
                                            <div class="badge bg-primary me-3 mt-1">4</div>
                                            <div>
                                                <h6 class="mb-1">Monitor Application Health</h6>
                                                <small class="text-muted">
                                                    Use the System Monitor dashboard regularly to track application health and identify issues early.
                                                </small>
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
        // Auto-refresh every 60 seconds
        setTimeout(() => location.reload(), 60000);
    });
</script>
@endsection
