@extends('backend.app')

@section('title', 'Application Statistics')

@section('content')
<!-- Page Content -->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div class="d-flex align-items-center gap-2">
                        <div>
                            <h1 class="page-title">Application Statistics</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('system-monitor.index') }}">System Monitor</a></li>
                                    <li class="breadcrumb-item active">Application Statistics</li>
                                </ol>
                            </nav>
                        </div>

                    </div>
                    
                </div>

                <!-- User Statistics -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">User Management</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Total Users</span>
                                            <strong>{{ number_format($stats['total_users']) }}</strong>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-primary" style="width: 100%"></div>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Active Users (Verified)</span>
                                            <strong>{{ number_format($stats['active_users']) }}</strong>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" style="width: {{ ($stats['active_users'] / $stats['total_users'] * 100) }}%"></div>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Admin Users</span>
                                            <strong>{{ number_format($stats['admin_users']) }}</strong>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-warning" style="width: {{ ($stats['admin_users'] / max($stats['total_users'], 1) * 100) }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Tenant Management</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Total Tenants</span>
                                            <strong>{{ number_format($stats['total_tenants']) }}</strong>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" style="width: 100%"></div>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Active Tenants</span>
                                            <strong>{{ number_format($stats['active_tenants']) }}</strong>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" style="width: {{ ($stats['active_tenants'] / max($stats['total_tenants'], 1) * 100) }}%"></div>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Pending Tenants</span>
                                            <strong>{{ number_format($stats['pending_tenants']) }}</strong>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-warning" style="width: {{ ($stats['pending_tenants'] / max($stats['total_tenants'], 1) * 100) }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Property & Lease Statistics -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Property Management</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Total Properties</span>
                                            <strong>{{ number_format($stats['total_properties']) }}</strong>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-primary" style="width: 100%"></div>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Active Properties</span>
                                            <strong>{{ number_format($stats['active_properties']) }}</strong>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" style="width: {{ ($stats['active_properties'] / max($stats['total_properties'], 1) * 100) }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Lease Management</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Total Leases</span>
                                            <strong>{{ number_format($stats['total_leases']) }}</strong>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" style="width: 100%"></div>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Active Leases</span>
                                            <strong>{{ number_format($stats['active_leases']) }}</strong>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" style="width: {{ ($stats['active_leases'] / max($stats['total_leases'], 1) * 100) }}%"></div>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Pending Signatures</span>
                                            <strong>{{ number_format($stats['pending_leases']) }}</strong>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-warning" style="width: {{ ($stats['pending_leases'] / max($stats['total_leases'], 1) * 100) }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial & Maintenance Statistics -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Financial Overview</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Total Invoices</span>
                                            <strong>{{ number_format($stats['total_invoices']) }}</strong>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-primary" style="width: 100%"></div>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Pending Invoices</span>
                                            <strong class="text-danger">{{ number_format($stats['pending_invoices']) }}</strong>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-danger" style="width: {{ ($stats['pending_invoices'] / max($stats['total_invoices'], 1) * 100) }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Maintenance Overview</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Total Requests</span>
                                            <strong>{{ number_format($stats['total_maintenance_requests']) }}</strong>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" style="width: 100%"></div>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Open Requests</span>
                                            <strong class="text-warning">{{ number_format($stats['open_maintenance_requests']) }}</strong>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-warning" style="width: {{ ($stats['open_maintenance_requests'] / max($stats['total_maintenance_requests'], 1) * 100) }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Card -->
                <div class="row">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="alert alert-info d-flex align-items-center mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <span>Statistics show the current state of your application. This page automatically refreshes every 30 seconds.</span>
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
