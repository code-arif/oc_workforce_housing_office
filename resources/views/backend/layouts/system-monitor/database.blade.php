@extends('backend.app')

@section('title', 'Database Monitor')

@section('content')
<!-- Page Content -->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div class="d-flex align-items-center gap-2">
                        <div>
                            <h1 class="page-title">Database Monitor</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('system-monitor.index') }}">System Monitor</a></li>
                                    <li class="breadcrumb-item active">Database Monitor</li>
                                </ol>
                            </nav>
                        </div>

                    </div>
                    
                </div>

                @if($database['status'] === 'connected')
                    <!-- Database Connection Info -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Connection Information</h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <td><strong>Database Name:</strong></td>
                                                    <td>{{ $database['name'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Driver:</strong></td>
                                                    <td>{{ ucfirst($database['driver']) }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Host:</strong></td>
                                                    <td>{{ $database['host'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Port:</strong></td>
                                                    <td>{{ $database['port'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Connection Status:</strong></td>
                                                    <td><span class="badge bg-success">Connected</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Database Statistics -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body p-3">
                                    <p class="text-muted small mb-1">Total Tables</p>
                                    <h4 class="mb-0">{{ $database['table_count'] }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body p-3">
                                    <p class="text-muted small mb-1">Total Records</p>
                                    <h4 class="mb-0">{{ number_format($database['total_rows']) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body p-3">
                                    <p class="text-muted small mb-1">Database Size</p>
                                    <h4 class="mb-0">{{ $database['total_size'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tables Overview -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Largest Tables (Top 10)</h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Table Name</th>
                                                    <th class="text-end">Rows</th>
                                                    <th class="text-end">Size</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($database['tables'] as $table)
                                                    <tr>
                                                        <td>
                                                            <code>{{ $table['name'] }}</code>
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="badge bg-info">{{ number_format($table['rows']) }}</span>
                                                        </td>
                                                        <td class="text-end">{{ $table['size'] }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">No tables found</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                @else
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-danger d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <div>
                                    <strong>Database Connection Error</strong>
                                    <p class="mb-0 mt-1">{{ $database['error'] ?? 'Unable to connect to database' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection
