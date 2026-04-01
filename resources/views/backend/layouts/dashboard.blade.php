@extends('backend.app')

@section('title', 'Dashboard')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app mb-3">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Dashboard</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Index</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- Overall Summary -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-chart-pie me-2"></i>Overall Summary</h3>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-3 border-end">
                                <h2 class="fw-bold text-primary">{{ $totals['total_properties'] ?? 0 }}</h2>
                                <p class="text-muted mb-0">Total Properties</p>
                            </div>
                            <div class="col-3 border-end">
                                <h2 class="fw-bold text-info">{{ $totals['total_beds'] ?? 0 }}</h2>
                                <p class="text-muted mb-0">Total Beds</p>
                            </div>
                            <div class="col-3 border-end">
                                <h2 class="fw-bold text-success">{{ $totals['available_beds'] ?? 0 }}</h2>
                                <p class="text-muted mb-0">Available Beds</p>
                            </div>
                            <div class="col-3">
                                <h2 class="fw-bold text-warning">{{ $totals['occupied_beds'] ?? 0 }}</h2>
                                <p class="text-muted mb-0">Occupied Beds</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Properties Overview Section -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center bg-light">
                        <h3 class="card-title mb-0"><i class="fas fa-building me-2"></i>Properties Overview</h3>
                        <a href="{{ route('property.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-list me-1"></i> View All Properties
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            @forelse($properties as $property)
                                <div class="col-lg-4 col-md-6 col-12">
                                    <div class="card h-100 border shadow-sm property-card">
                                        <div class="card-header bg-gradient-primary text-white py-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0 fw-bold">{{ $property->name }}</h5>
                                                <div class="badge bg-light text-dark ms-2">
                                                    {{ $property->occupancy_rate }}% Occupied
                                                </div>
                                            </div>
                                            {{-- @if($property->address)
                                                <small class="opacity-75"><i class="fas fa-map-marker-alt me-1"></i>{{ Str::limit($property->address, 40) }}</small>
                                            @endif --}}
                                        </div>
                                        <div class="card-body">
                                            <!-- Occupancy Progress Bar -->
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between small mb-1">
                                                    <span>Occupancy</span>
                                                    <span>{{ $property->occupied_beds }}/{{ $property->total_beds }} beds</span>
                                                </div>
                                                <div class="progress" style="height: 8px;">
                                                    @php
                                                        $barColor = $property->occupancy_rate >= 80 ? 'bg-success' : ($property->occupancy_rate >= 50 ? 'bg-warning' : 'bg-danger');
                                                    @endphp
                                                    <div class="progress-bar {{ $barColor }}" role="progressbar" 
                                                         style="width: {{ $property->occupancy_rate }}%"></div>
                                                </div>
                                            </div>

                                            <!-- Stats Grid -->
                                            <div class="row text-center g-2">
                                                <div class="col-4">
                                                    <div class="border  py-2">
                                                        <h5 class="fw-bold text-primary mb-0">{{ $property->total_units }}</h5>
                                                        <small class="text-muted">Units</small>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="border  py-2">
                                                        <h5 class="fw-bold text-info mb-0">{{ $property->total_rooms }}</h5>
                                                        <small class="text-muted">Rooms</small>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="border  py-2">
                                                        <h5 class="fw-bold text-success mb-0">{{ $property->total_beds }}</h5>
                                                        <small class="text-muted">Beds</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Detailed Stats -->
                                            <div class="mt-3">
                                                <div class="d-flex justify-content-between py-2 border-bottom">
                                                    <span class="text-muted"><i class="fas fa-bed text-success me-2"></i>Available Beds</span>
                                                    <span class="fw-bold text-success">{{ $property->available_beds }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between py-2 border-bottom">
                                                    <span class="text-muted"><i class="fas fa-user-check text-warning me-2"></i>Occupied Beds</span>
                                                    <span class="fw-bold text-warning">{{ $property->occupied_beds }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between py-2 border-bottom">
                                                    <span class="text-muted"><i class="fas fa-file-contract text-primary me-2"></i>Active Leases</span>
                                                    <span class="fw-bold text-primary">{{ $property->active_leases }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between py-2 border-bottom">
                                                    <span class="text-muted"><i class="fas fa-dollar-sign text-info me-2"></i>Total Rent</span>
                                                    <span class="fw-bold text-info">${{ number_format($property->total_rent ?? 0, 2) }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between py-2 border-bottom">
                                                    <span class="text-muted "><i class="fas fa-file-invoice-dollar text-danger me-2"></i>Total Due</span>
                                                    <span class="fw-bold text-danger">${{ number_format($property->total_due ?? 0, 2) }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between py-2">
                                                    <span class="text-muted"><i class="fas fa-hand-holding-usd text-success me-2"></i>Total Paid</span>
                                                    <span class="fw-bold text-success">${{ number_format($property->total_paid ?? 0, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-light">
                                            <a href="{{ route('property.show', $property->id) }}" class="btn btn-outline-primary btn-sm w-100">
                                                <i class="fas fa-eye me-1"></i> View Property Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-info text-center">
                                        <i class="fas fa-info-circle me-2"></i>No properties found. 
                                        <a href="{{ route('property.create') }}">Add your first property</a>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Main Dashboard Content -->
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-lg-8 col-md-12">
                        <!-- Invoice Lists -->
                        <div class="row g-4">
                             <!-- Pending Applications -->
                             <div class="col-lg-6 col-md-6 col-12">
                                <div class="card h-100">
                                    <div class="card-header bg-light">
                                        <h3 class="card-title mb-0">PENDING APPLICATIONS</h3>
                                    </div>
                                    <div class="card-body">
                                        @forelse($pendingApplications as $application)
                                            <div class="application-item p-3 border mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="fw-bold mb-0">
                                                        {{ $application->profile ? trim($application->profile->first_name . ' ' . $application->profile->last_name) : $application->email }}
                                                    </h6>
                                                    <span class="badge bg-warning px-3 py-2">Pending</span>
                                                </div>
                                                <p class="text-muted small mb-0">
                                                    <i class="fas fa-calendar me-1"></i> Applied on {{ $application->created_at->format('M d, Y') }}
                                                </p>
                                            </div>
                                        @empty
                                            <div class="text-center py-4 text-muted">
                                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                                <p>No pending applications</p>
                                            </div>
                                        @endforelse

                                        @if($pendingApplications->count() > 0)
                                            <div class="text-center mt-3">
                                                <a href="{{ route('tenants.index') }}" class="text-primary small">
                                                    <i class="fas fa-plus me-1"></i> View All Pending
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <!-- Unsigned Applications -->
                            <div class="col-lg-6 col-md-6 col-12">
                                <div class="card h-100">
                                    <div class="card-header bg-light">
                                        <h3 class="card-title mb-0">UNSIGNED LEASES</h3>
                                    </div>
                                    <div class="card-body">
                                        @forelse($unsignedLeases as $lease)
                                            <div class="application-item p-3 border mb-3 cursor-pointer" onclick="viewLease({{ $lease->id }})">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="fw-bold mb-0">
                                                        {{ $lease->tenant?->profile ? trim($lease->tenant->profile->first_name . ' ' . $lease->tenant->profile->last_name) : ($lease->tenant?->email ?? 'N/A') }}
                                                    </h6>
                                                    <span class="badge bg-danger px-3 py-2">Unsigned</span>
                                                </div>
                                                <p class="text-muted small mb-1">
                                                    <i class="fas fa-building me-1"></i> {{ $lease->property?->name ?? 'N/A' }}
                                                </p>
                                                <p class="text-muted small mb-0">
                                                    <i class="fas fa-calendar me-1"></i> Lease sent {{ $lease->created_at->format('M d, Y') }}
                                                </p>
                                            </div>
                                        @empty
                                            <div class="text-center py-4 text-muted">
                                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                                <p>No unsigned leases</p>
                                            </div>
                                        @endforelse

                                        @if($unsignedLeases->count() > 0)
                                            <div class="text-center mt-3">
                                                <a href="{{ route('leases.index') }}" class="text-primary small">
                                                    <i class="fas fa-plus me-1"></i> View All Unsigned
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-lg-4 col-md-12">
                        <!-- Recent Tenants -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h3 class="card-title mb-0">RECENT TENANTS</h3>
                            </div>
                            <div class="card-body">
                                @forelse($recentTenants as $tenant)
                                    @php
                                        $name = $tenant->profile ? trim($tenant->profile->first_name . ' ' . $tenant->profile->last_name) : 'Unknown';
                                        $initials = collect(explode(' ', $name))->map(fn($word) => strtoupper(substr($word, 0, 1)))->take(2)->join('');
                                        $currentLease = $tenant->leases->where('status', 'ACTIVE')->first();
                                        $assignment = $currentLease?->assignments->where('is_current', true)->first();
                                        $bed = $assignment?->bed;
                                        $unit = $bed?->room?->unit;
                                        $colors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger'];
                                        $colorIndex = crc32($name) % count($colors);
                                    @endphp
                                    <div class="tenant-item p-3 border mb-5">
                                        <div class="d-flex align-items-center mb-5">
                                            <div class="avatar avatar-sm me-4">
                                                <span class="avatar-initial {{ $colors[$colorIndex] }}">{{ $initials }}</span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="fw-bold mb-0">{{ $name }}</h6>
                                                <p class="text-muted small mb-0">
                                                    @if($unit && $bed)
                                                        Unit {{ $unit->unit_number }}, Bed {{ $bed->bed_label }}
                                                    @else
                                                        No assignment
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <p class="text-muted small mb-0">
                                            <i class="fas fa-calendar me-1"></i> Joined {{ $tenant->created_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-muted">
                                        <i class="fas fa-users fa-2x mb-2"></i>
                                        <p>No recent tenants</p>
                                    </div>
                                @endforelse

                                @if($recentTenants->count() > 0)
                                    <div class="text-center mt-3">
                                        <a href="{{ route('tenants.index') }}" class="text-primary small">
                                            <i class="fas fa-plus me-1"></i> View All Tenants
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- CONTAINER CLOSED -->
@endsection

@push('scripts')
    <script src="https://kit.fontawesome.com/aadff4f1c9.js" crossorigin="anonymous"></script>

    <script>

    function viewLease(leaseId) {
        window.location.href = `/admin/leases/${leaseId}`;
    }
    </script>
@endpush

@push('styles')
<style>
    .cursor-pointer {
        cursor: pointer;
    }
    .property-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .property-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .avatar-initial {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        color: white;
        font-weight: 600;
        font-size: 14px;
    }
    .progress {
        border-radius: 10px;
        overflow: hidden;
    }
    .progress-bar {
        border-radius: 10px;
    }
    .application-item , .tenant-item{
        transition: background-color 0.2s, transform 0.2s;
        border-radius: 8px;
    }
    .application-item:hover, .tenant-item:hover {
        background-color: #f8f9fa;
        transform: scale(1.02);
    }
</style>
@endpush
