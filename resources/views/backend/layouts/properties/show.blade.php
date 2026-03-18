@extends('backend.app')

@section('title', 'Property Details - ' . $property->name)

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Property Details</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('property.list') }}">Properties</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $property->name }}</li>
                        </ol>
                    </div>
                </div>

                <!-- Back Button & Actions -->
                <div class="row mb-3">
                    <div class="col-12 d-flex justify-content-end align-items-center">
                        <a href="{{ route('property.list') }}" class="btn btn-danger btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Properties
                        </a>
                        {{-- <div>
                            <button class="btn btn-warning btn-sm" onclick="editProperty({{ $property->id }})">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteProperty({{ $property->id }})">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </div> --}}
                    </div>
                </div>

                <!-- Property Overview Cards -->
                <div class="row mb-4">
                    <!-- Property Info Card (Compact) -->
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    @if ($property->image_path)
                                        <img src="{{ asset('storage/' . $property->image_path) }}"
                                            alt="{{ $property->name }}" class=" me-3"
                                            style="width: 80px; height: 80px; object-fit: cover;">
                                    @else
                                        <div class=" me-3 bg-light d-flex align-items-center justify-content-center"
                                            style="width: 80px; height: 80px;">
                                            <i class="bi bi-building text-muted" style="font-size: 2rem;"></i>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1 fw-bold">{{ $property->name }}</h5>
                                        @if ($property->propertyType)
                                            <span class="badge bg-primary mb-2">{{ $property->propertyType->name }}</span>
                                        @endif
                                        <p class="text-muted small mb-1">
                                            <i class="bi bi-geo-alt"></i> {{ $property->address ?? 'No address' }}
                                        </p>
                                        @if ($property->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Occupancy Stats Card -->
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="text-muted mb-3"><i class="bi bi-pie-chart me-2"></i>Occupancy Overview</h6>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <h3 class="fw-bold text-primary mb-0">{{ $stats['total_beds'] }}</h3>
                                        <small class="text-muted">Total Beds</small>
                                    </div>
                                    <div class="col-4">
                                        <h3 class="fw-bold text-danger mb-0">{{ $stats['occupied_beds'] }}</h3>
                                        <small class="text-muted">Occupied</small>
                                    </div>
                                    <div class="col-4">
                                        <h3 class="fw-bold text-success mb-0">{{ $stats['available_beds'] }}</h3>
                                        <small class="text-muted">Available</small>
                                    </div>
                                </div>
                                <div class="progress mt-3" style="height: 8px;">
                                    <div class="progress-bar bg-danger" role="progressbar"
                                        style="width: {{ $stats['occupancy_rate'] }}%;"
                                        aria-valuenow="{{ $stats['occupancy_rate'] }}" aria-valuemin="0"
                                        aria-valuemax="100">
                                    </div>
                                </div>
                                <small class="text-muted d-block text-center mt-1">{{ $stats['occupancy_rate'] }}%
                                    Occupancy Rate</small>
                            </div>
                        </div>
                    </div>

                    <!-- Rental Stats Card -->
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="text-muted mb-3"><i class="bi bi-currency-dollar me-2"></i>Rental Summary</h6>
                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <div class="border  p-2 text-center">
                                            <h4 class="fw-bold text-success mb-0">
                                                ${{ number_format($stats['total_monthly_rent'], 2) }}</h4>
                                            <small class="text-muted">Monthly Rent</small>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <div class="border  p-2 text-center">
                                            <h4 class="fw-bold text-info mb-0">{{ $stats['active_leases'] }}</h4>
                                            <small class="text-muted">Active Leases</small>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <div class="border  p-2 text-center">
                                            <h4 class="fw-bold text-warning mb-0">{{ $property->totalUnits() }}</h4>
                                            <small class="text-muted">Total Units</small>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <div class="border  p-2 text-center">
                                            <h4 class="fw-bold text-secondary mb-0">{{ $property->totalRooms() }}</h4>
                                            <small class="text-muted">Total Rooms</small>
                                        </div>
                                    </div>

                                    <div class="col-6 mb-2">
                                        <div class="border  p-2 text-center">
                                            <h4 class="fw-bold text-success mb-0">
                                                ${{ number_format($stats['total_rent'], 2) }}</h4>
                                            <small class="text-muted">Total Rent</small>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <div class="border  p-2 text-center">
                                            <h4 class="fw-bold text-danger mb-0">
                                                ${{ number_format($stats['total_due'], 2) }}</h4>
                                            <small class="text-muted">Due Rent</small>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <div class="border  p-2 text-center">
                                            <h4 class="fw-bold text-success mb-0">
                                                ${{ number_format($stats['total_paid'], 2) }}</h4>
                                            <small class="text-muted">Total Paid</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-lg-8">
                        <!-- Units, Rooms & Beds Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-building me-2"></i>Units, Rooms & Beds
                                </h5>
                                <div>
                                    <span class="badge bg-success me-2">{{ $stats['available_beds'] }} Available</span>
                                    <span class="badge bg-danger">{{ $stats['occupied_beds'] }} Occupied</span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                @forelse ($property->units->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE) as $unit)
                                    <div class="unit-section border-bottom">
                                        <!-- Unit Header -->
                                        <div class="d-flex justify-content-between align-items-center p-3 bg-light cursor-pointer unit-toggle"
                                            data-bs-toggle="collapse" data-bs-target="#unit-{{ $unit->id }}">
                                            <div>
                                                <h6 class="mb-0 fw-bold">
                                                    <i class="bi bi-building me-2"></i>{{ $unit->name }}
                                                </h6>
                                                <small class="text-muted">
                                                    {{ $unit->rooms->count() }} Rooms |
                                                    {{ $unit->rooms->sum(fn($r) => $r->beds->count()) }} Beds |
                                                    Gender: {{ ucfirst($unit->gender_designation ?? 'Mixed') }}
                                                </small>
                                            </div>
                                            <i class="bi bi-chevron-down"></i>
                                        </div>

                                        <!-- Unit Content (Collapsible) -->
                                        <div class="collapse show" id="unit-{{ $unit->id }}">
                                            @foreach ($unit->rooms as $room)
                                                <div class="room-section ms-4 border-start ps-3 py-2">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <span class="fw-semibold">
                                                                <i class="bi bi-door-closed me-1"></i>Room
                                                                {{ $room->room_number }}
                                                            </span>
                                                            <small class="text-muted ms-2">({{ $room->beds->count() }}
                                                                beds)</small>
                                                        </div>
                                                    </div>

                                                    <!-- Beds Grid -->
                                                    <div class="row g-2 mt-2">
                                                        @foreach ($room->beds as $bed)
                                                            @php
                                                                $currentAssignment = $bed->leaseAssignments
                                                                    ->where('is_current', true)
                                                                    ->first();
                                                                $tenant = $currentAssignment?->lease?->tenant;
                                                            @endphp
                                                            <div class="col-md-4 col-sm-6">
                                                                <div class="bed-card p-2  border {{ $bed->is_occupied ? 'border-danger bg-danger-subtle occupied-bed mouse-pointer' : 'border-success bg-success-subtle' }}"
                                                                    data-tenant-id="{{ $tenant ? $tenant->id : null }}">
                                                                    <div
                                                                        class="d-flex justify-content-between align-items-start">
                                                                        <div>
                                                                            <strong
                                                                                class="d-block">{{ $bed->bed_label }}</strong>
                                                                            <small
                                                                                class="text-muted">{{ $currentAssignment ? 'Move In: ' . date('d-M-Y', strtotime($currentAssignment->actual_move_in)) : '' }}</small>
                                                                        </div>
                                                                        @if ($bed->is_occupied)
                                                                            <span class="badge bg-danger">Occupied</span>
                                                                        @else
                                                                            <span class="badge bg-success">Available</span>
                                                                        @endif
                                                                    </div>
                                                                    @if ($tenant)
                                                                        <div class="pt-2 border-top">
                                                                            <small class="text-dark">
                                                                                <i class="bi bi-person"></i>
                                                                                {{ $tenant->profile->first_name }}
                                                                                {{ $tenant->profile->last_name }}
                                                                            </small>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-5">
                                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                        <p class="mt-2">No units assigned to this property</p>
                                        <a href="#" class="btn btn-sm btn-primary">Add Unit</a>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Lease History Section -->
                        <div class="card">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-file-earmark-text me-2"></i>Lease History
                                </h5>
                                <span class="badge bg-secondary">{{ $property->leases->count() }} Total Leases</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tenant</th>
                                                <th>Bed(s)</th>
                                                <th>Duration</th>
                                                <th>Rent</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($property->leases->sortByDesc('created_at') as $lease)
                                                <tr>
                                                    <td>
                                                        @if ($lease->tenant)
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar avatar-sm bg-primary-subtle -circle me-2 d-flex align-items-center justify-content-center"
                                                                    style="width: 35px; height: 35px;">
                                                                    <span
                                                                        class="text-primary fw-bold">{{ strtoupper(substr($lease->tenant->first_name ?? 'T', 0, 1)) }}</span>
                                                                </div>
                                                                <div>
                                                                    <strong>{{ $lease->tenant?->profile?->first_name }}
                                                                        {{ $lease->tenant?->profile?->last_name }}</strong>
                                                                    <br><small
                                                                        class="text-muted">{{ $lease->tenant?->email }}</small>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">No tenant</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($lease->assignments->where('is_current', true)->isNotEmpty())
                                                            @php
                                                                $assignment = $lease->assignments
                                                                    ->where('is_current', true)
                                                                    ->first();
                                                            @endphp
                                                            <span class="badge bg-light text-dark border">
                                                                {{ $assignment->bed->bed_label ?? '' }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <small>
                                                            {{ $lease->start_date ? $lease->start_date->format('M d, Y') : '-' }}<br>
                                                            <span class="text-muted">to
                                                                {{ $lease->end_date ? $lease->end_date->format('M d, Y') : '-' }}</span>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <strong>${{ number_format($lease->rent_amount ?? 0, 2) }}</strong>
                                                        <br><small
                                                            class="text-muted">{{ ucfirst($lease->payment_frequency ?? 'monthly') }}</small>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $statusColors = [
                                                                'ACTIVE' => 'success',
                                                                'PENDING' => 'warning',
                                                                'EXPIRED' => 'secondary',
                                                                'TERMINATED' => 'danger',
                                                                'DRAFT' => 'info',
                                                            ];
                                                            $color = $statusColors[$lease->status] ?? 'secondary';
                                                        @endphp
                                                        <span
                                                            class="badge bg-{{ $color }}">{{ $lease->status }}</span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('leases.show', $lease->id) }}"
                                                            class="btn btn-sm btn-outline-primary" title="View Lease">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">
                                                        <i class="bi bi-file-earmark-x" style="font-size: 2rem;"></i>
                                                        <p class="mt-2 mb-0">No leases found for this property</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Sidebar -->
                    <div class="col-lg-4">
                        <!-- Property Description -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="bi bi-info-circle me-2"></i>Description
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $property->description ?? 'No description available.' }}</p>
                            </div>
                        </div>

                        <!-- Units Breakdown -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="bi bi-list-ul me-2"></i>Units Breakdown
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    @forelse ($property->units->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE) as $unit)
                                        @php
                                            $unitBeds = $unit->rooms->flatMap->beds;
                                            $unitOccupied = $unitBeds->where('is_occupied', true)->count();
                                            $unitTotal = $unitBeds->count();
                                        @endphp
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $unit->name }}</strong>
                                                <br><small class="text-muted">{{ $unit->rooms->count() }} rooms,
                                                    {{ $unitTotal }} beds</small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-success">{{ $unitTotal - $unitOccupied }}</span>
                                                <span class="text-muted">/</span>
                                                <span class="badge bg-danger">{{ $unitOccupied }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="list-group-item text-center text-muted">
                                            No units found
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="bi bi-graph-up me-2"></i>Quick Stats
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="d-flex justify-content-between py-2 border-bottom">
                                        <span class="text-muted">Created</span>
                                        <strong>{{ $property->created_at->format('M d, Y') }}</strong>
                                    </li>
                                    <li class="d-flex justify-content-between py-2 border-bottom">
                                        <span class="text-muted">Last Updated</span>
                                        <strong>{{ $property->updated_at->format('M d, Y') }}</strong>
                                    </li>
                                    <li class="d-flex justify-content-between py-2 border-bottom">
                                        <span class="text-muted">Total Leases</span>
                                        <strong>{{ $property->leases->count() }}</strong>
                                    </li>
                                    <li class="d-flex justify-content-between py-2 border-bottom">
                                        <span class="text-muted">Active Leases</span>
                                        <strong class="text-success">{{ $stats['active_leases'] }}</strong>
                                    </li>
                                    <li class="d-flex justify-content-between py-2">
                                        <span class="text-muted">Avg Rent/Bed</span>
                                        <strong>
                                            @php
                                                $avgRent =
                                                    $stats['occupied_beds'] > 0
                                                        ? $stats['total_monthly_rent'] / $stats['occupied_beds']
                                                        : 0;
                                            @endphp
                                            ${{ number_format($avgRent, 2) }}
                                        </strong>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        @include('backend.layouts.properties.partials._stripe_connect')

                        <!-- Lease Status Distribution -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="bi bi-pie-chart me-2"></i>Lease Status
                                </h6>
                            </div>
                            <div class="card-body">
                                @php
                                    $leasesByStatus = $property->leases->groupBy('status');
                                @endphp
                                @if ($property->leases->isNotEmpty())
                                    @foreach ($leasesByStatus as $status => $leases)
                                        @php
                                            $statusColors = [
                                                'ACTIVE' => 'success',
                                                'PENDING' => 'warning',
                                                'EXPIRED' => 'secondary',
                                                'TERMINATED' => 'danger',
                                                'DRAFT' => 'info',
                                            ];
                                            $color = $statusColors[$status] ?? 'secondary';
                                            $percentage = ($leases->count() / $property->leases->count()) * 100;
                                        @endphp
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="badge bg-{{ $color }}">{{ $status }}</span>
                                                <small>{{ $leases->count() }} ({{ round($percentage) }}%)</small>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-{{ $color }}"
                                                    style="width: {{ $percentage }}%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-muted text-center mb-0">No leases yet</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            border-radius: 8px 8px 0 0 !important;
            padding: 1rem 1.25rem;
        }

        .unit-toggle {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .unit-toggle:hover {
            background-color: #e9ecef !important;
        }

        .bed-card {
            transition: all 0.2s ease;
        }

        .bed-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .bg-success-subtle {
            background-color: rgba(25, 135, 84, 0.1) !important;
        }

        .bg-danger-subtle {
            background-color: rgba(220, 53, 69, 0.1) !important;
        }

        .bg-primary-subtle {
            background-color: rgba(13, 110, 253, 0.1) !important;
        }

        .progress {
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }

        .table> :not(caption)>*>* {
            padding: 0.75rem 1rem;
        }

        .list-group-item {
            border-left: none;
            border-right: none;
        }

        .avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-outline-primary {
            border-radius: 6px;
        }

        .room-section {
            border-left-color: #dee2e6 !important;
        }

        .badge {
            font-weight: 500;
        }

        .mouse-pointer {
            cursor: pointer;
        }
    </style>
@endpush

@push('scripts')
    <script>
        function editProperty(id) {
            window.location.href = '{{ url('admin/property') }}/' + id + '/edit';
        }

        $(document).on('click', '.occupied-bed', function() {
            let tenantId = $(this).data('tenant-id');
            if (!tenantId) {
                return;
            }
            Swal.fire({
                title: 'Redirecting to Tenant Details',
                text: 'You will be redirected to the tenant details page.',
                icon: 'info',
                timer: 1500,
                showConfirmButton: false,
                willClose: () => {
                    window.location.href = "{{ route('tenants.show', ':id') }}".replace(':id',
                        tenantId); // Replace '#' with the actual tenant details URL if available
                }
            })
            // window.location.href = "{{ route('tenants.show', ':id') }}".replace(':id', tenantId); // Replace '#' with the actual tenant details URL if available
        });

        function deleteProperty(id) {
            if (confirm('Are you sure you want to delete this property? This action cannot be undone.')) {
                fetch('{{ url('admin/property') }}/' + id, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = '{{ route('property.list') }}';
                        } else {
                            alert(data.message || 'Failed to delete property');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the property');
                    });
            }
        }

        // Toggle unit collapse icon
        document.querySelectorAll('.unit-toggle').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const icon = this.querySelector('.bi-chevron-down, .bi-chevron-up');
                if (icon) {
                    icon.classList.toggle('bi-chevron-down');
                    icon.classList.toggle('bi-chevron-up');
                }
            });
        });
    </script>
@endpush
