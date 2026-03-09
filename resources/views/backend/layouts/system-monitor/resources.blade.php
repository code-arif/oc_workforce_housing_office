@extends('backend.app')

@section('title', 'System Resources')

@section('content')
<!-- Page Content -->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div class="d-flex align-items-center gap-2">
                        <div>
                            <h1 class="page-title">System Resources</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('system-monitor.index') }}">System Monitor</a></li>
                                    <li class="breadcrumb-item active">System Resources</li>
                                </ol>
                            </nav>
                        </div>

                    </div>
                    
                </div>

                <!-- Memory Usage -->
                @if(isset($resources['memory']))
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Memory Usage</h3>
                                </div>
                                <div class="card-body">
                                    @php
                                        $memory = $resources['memory'];
                                        $usagePercent = $memory['usage_percent'];
                                        $color = $usagePercent < 50 ? 'success' : ($usagePercent < 80 ? 'warning' : 'danger');
                                    @endphp
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="progress mb-3" style="height: 30px;">
                                                <div class="progress-bar bg-{{ $color }}" role="progressbar" 
                                                     style="width: {{ $usagePercent }}%;" 
                                                     aria-valuenow="{{ $usagePercent }}" aria-valuemin="0" aria-valuemax="100">
                                                    <span class="text-white fw-bold">{{ $usagePercent }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <h5>{{ $usagePercent }}% Used</h5>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div class="card">
                                                <div class="card-body p-3">
                                                    <p class="text-muted small mb-1">Currently Used</p>
                                                    <h4>{{ number_format($memory['used'] / 1024 / 1024, 2) }} MB</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card">
                                                <div class="card-body p-3">
                                                    <p class="text-muted small mb-1">Peak Usage</p>
                                                    <h4>{{ number_format($memory['peak'] / 1024 / 1024, 2) }} MB</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card">
                                                <div class="card-body p-3">
                                                    <p class="text-muted small mb-1">Memory Limit</p>
                                                    <h4>{{ $memory['limit'] }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Disk Usage -->
                @if(isset($resources['disk']))
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Disk Usage</h3>
                                </div>
                                <div class="card-body">
                                    @php
                                        $disk = $resources['disk'];
                                        $diskPercent = $disk['usage_percent'];
                                        $diskColor = $diskPercent < 50 ? 'success' : ($diskPercent < 80 ? 'warning' : 'danger');
                                    @endphp
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="progress mb-3" style="height: 30px;">
                                                <div class="progress-bar bg-{{ $diskColor }}" role="progressbar" 
                                                     style="width: {{ $diskPercent }}%;" 
                                                     aria-valuenow="{{ $diskPercent }}" aria-valuemin="0" aria-valuemax="100">
                                                    <span class="text-white fw-bold">{{ $diskPercent }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <h5>{{ $diskPercent }}% Used</h5>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div class="card">
                                                <div class="card-body p-3">
                                                    <p class="text-muted small mb-1">Used Space</p>
                                                    <h4>{{ $disk['used_formatted'] }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card">
                                                <div class="card-body p-3">
                                                    <p class="text-muted small mb-1">Free Space</p>
                                                    <h4>{{ $disk['free_formatted'] }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card">
                                                <div class="card-body p-3">
                                                    <p class="text-muted small mb-1">Total Space</p>
                                                    <h4>{{ $disk['total_formatted'] }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- CPU Load -->
                @if(isset($resources['cpu']))
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">CPU Load Average</h3>
                                </div>
                                <div class="card-body">
                                    @php $cpu = $resources['cpu']; @endphp
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="card">
                                                <div class="card-body p-3">
                                                    <p class="text-muted small mb-1">1 Minute</p>
                                                    <h4>{{ number_format($cpu['load_1min'], 2) }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card">
                                                <div class="card-body p-3">
                                                    <p class="text-muted small mb-1">5 Minutes</p>
                                                    <h4>{{ number_format($cpu['load_5min'], 2) }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card">
                                                <div class="card-body p-3">
                                                    <p class="text-muted small mb-1">15 Minutes</p>
                                                    <h4>{{ number_format($cpu['load_15min'], 2) }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-info d-flex align-items-center">
                            <i class="fas fa-info-circle me-2"></i>
                            <span>This page automatically refreshes every 30 seconds. Last updated: <strong>{{ now()->format('H:i:s') }}</strong></span>
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
