@extends('backend.app')

@section('title', 'Tenant Details')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Tenant Details</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('tenants.index') }}">Tenants</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Details</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <a href="{{ route('tenants.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center">
                            <i class="fe fe-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
                </div>

                @php
                    $profile = $tenant->profile;
                    $address = $tenant->address;
                    $fullName = $profile
                        ? trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''))
                        : 'No Name';
                    $avatar = $profile && $profile->avatar
                        ? asset($profile->avatar)
                        : 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=6366f1&color=fff&size=200';
                    $activeLease = $tenant->leases->where('status', 'ACTIVE')->first();
                @endphp

                <div class="row">
                    <!-- Left Column - Profile & Info -->
                    <div class="col-xl-4 col-lg-5">
                        <!-- Profile Card -->
                        <div class="card tenant-profile-card">
                            <div class="card-body text-center pt-4 pb-4">
                                <div class="tenant-avatar-wrapper mb-3">
                                    <img src="{{ $avatar }}" alt="{{ $fullName }}" class="tenant-avatar-lg">
                                    @if($activeLease)
                                        <span class="status-badge active" title="Active Lease">
                                            <i class="fe fe-check"></i>
                                        </span>
                                    @endif
                                </div>
                                <h4 class="mb-1 fw-bold">{{ $fullName }}</h4>
                                <p class="text-muted mb-3">
                                    <i class="fe fe-calendar me-1"></i> Tenant since {{ $tenant->created_at->format('M d, Y') }}
                                </p>
                                {{-- <div class="d-flex justify-content-center gap-2 mb-3">
                                    <span class="badge bg-{{ $tenant->status === 'approved' || $tenant->status === 'active' ? 'success' : ($tenant->status === 'pending' ? 'warning' : 'secondary') }} px-3 py-2">
                                        {{ ucfirst($tenant->status) }}
                                    </span>
                                    @if($activeLease)
                                        <span class="badge bg-primary px-3 py-2">Active Lease</span>
                                    @endif
                                </div> --}}

                                <div class="d-flex justify-content-center gap-2 mb-3">
                                    <span class="badge bg-{{ $tenant->status === 'approved' || $tenant->status === 'active' ? 'success' : ($tenant->status === 'pending' ? 'warning' : 'secondary') }} px-3 py-3 rounded-pill" >
                                        {{ ucfirst($tenant->status) }}
                                    </span>
                                    @if($activeLease)
                                        <span class="badge bg-primary px-3 py-3 rounded-pill">Active Lease</span>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-secondary me-2 d-inline-flex align-items-center" title="View application details" id="viewApplicationBtn"
                                        data-tenant-id="{{ $tenant->id }}" onclick="viewApplicationDetails({{$tenant->application_id}})">
                                    <i class="fe fe-eye me-1"></i> Open Application
                                </button>
                                {{-- Approve/Reject buttons for non-approved tenants --}}
                                {{-- @if(!in_array($tenant->status, ['approved', 'active']))
                                    <div class="d-flex justify-content-center gap-2 mb-3">
                                        <button type="button"
                                                class="btn btn-success btn-sm d-inline-flex align-items-center"
                                                onclick="approveTenantDetails({{ $tenant->id }}, 'approved')">
                                            <i class="fe fe-check me-1"></i> Approve
                                        </button>
                                        <button type="button"
                                                class="btn btn-outline-danger btn-sm d-inline-flex align-items-center"
                                                onclick="approveTenantDetails({{ $tenant->id }}, 'rejected')">
                                            <i class="fe fe-x me-1"></i> Reject
                                        </button>
                                    </div>
                                @endif --}}

                            </div>
                            <div class="card-footer bg-light">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="fw-bold text-primary">{{ $tenant->leases->count() }}</div>
                                        <small class="text-muted">Leases</small>
                                    </div>
                                    <div class="col-4 border-start border-end">
                                        <div class="fw-bold text-success">${{ number_format($invoiceStats['paid_amount'] ?? 0, 2) }}</div>
                                        <small class="text-muted">Paid</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="fw-bold text-{{ ($invoiceStats['balance_due'] ?? 0) > 0 ? 'danger' : 'muted' }}">${{ number_format($invoiceStats['balance_due'] ?? 0, 2) }}</div>
                                        <small class="text-muted">Balance</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($tenant->leases && $tenant->leases->count() > 0)
                        <!-- Bed Assignments -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fe fe-home text-primary me-2"></i>Bed Assignments
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="bed-assignments-list">
                                    @foreach ($tenant->leases as $lease)
                                        @forelse ($lease->assignments as $assigned)
                                            <div class="bed-assignment-item {{ $assigned->is_current ? 'current-bed' : 'past-bed' }}">
                                                <div class="bed-icon">
                                                    @if($assigned->bed_id)
                                                        <i class="fe fe-{{ $assigned->is_current ? 'home' : 'clock' }}"></i>
                                                    @else
                                                        <i class="fe fe-alert-circle text-warning"></i>
                                                    @endif
                                                </div>
                                                <div class="bed-details">
                                                    @if($assigned->bed_id)
                                                        <div class="bed-label">
                                                            {{ $assigned->bed->bed_label ?? 'N/A' }}
                                                            @if($assigned->is_current && $assigned->actual_move_in)
                                                               <br> <small class="bed-subtext">Move In: {{ $assigned?->actual_move_in }}</small>
                                                            @endif
                                                        </div>
                                                        @if($assigned->is_current)
                                                            <span class="bed-status-badge current">Current</span>
                                                        @else
                                                            <span class="bed-status-badge past">Past</span>
                                                        @endif
                                                    @else
                                                        <div class="bed-label text-warning">Pending Assignment</div>
                                                        <span class="bed-status-badge bg-warning text-dark">Awaiting Bed</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-muted text-center mb-0">No bed assignments found</p>
                                        @endforelse
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Pending Bed Assignments Alert -->
                        @php
                            $pendingBedLeases = $tenant->leases->filter(function($lease) {
                                return $lease->bed_assignment_pending ||
                                       ($lease->assignments->where('is_current', true)->first() &&
                                        !$lease->assignments->where('is_current', true)->first()->bed_id);
                            });
                        @endphp
                        @if($pendingBedLeases->count() > 0)
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="card-title mb-0">
                                    <i class="fe fe-alert-triangle me-2"></i>Pending Bed Assignments
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">The following leases require bed assignment before the tenant can move in:</p>
                                @foreach($pendingBedLeases as $pendingLease)
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light ">
                                    <div>
                                        <strong>{{ $pendingLease->property->name ?? 'Unknown Property' }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $pendingLease->start_date->format('M d, Y') }} - {{ $pendingLease->end_date->format('M d, Y') }}
                                            | ${{ number_format($pendingLease->rent_amount, 2) }}/mo
                                        </small>
                                        <br>
                                        <span class="badge bg-{{ $pendingLease->status === 'ACTIVE' ? 'success' : 'info' }}">{{ $pendingLease->status }}</span>
                                    </div>
                                    <button type="button" class="btn btn-success btn-sm assignBedBtn"
                                            data-lease-id="{{ $pendingLease->id }}"
                                            data-property-name="{{ $pendingLease->property->name ?? 'N/A' }}">
                                        <i class="fe fe-plus-circle me-1"></i> Assign Bed
                                    </button>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @endif

                        <!-- Contact Information -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="fe fe-user text-primary me-2"></i>Contact Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="info-item mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="info-icon bg-primary-light">
                                            <i class="fe fe-mail text-primary"></i>
                                        </div>
                                        <div class="ms-3">
                                            <small class="text-muted d-block">Email</small>
                                            <span class="fw-semibold">{{ $tenant->email }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="info-item mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="info-icon bg-success-light">
                                            <i class="fe fe-phone text-success"></i>
                                        </div>
                                        <div class="ms-3">
                                            <small class="text-muted d-block">Phone</small>
                                            <span class="fw-semibold">{{ $profile->phone ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                                @if($profile?->date_of_birth || $tenant->date_of_birth)
                                <div class="info-item mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="info-icon bg-warning-light">
                                            <i class="fe fe-gift text-warning"></i>
                                        </div>
                                        <div class="ms-3">
                                            <small class="text-muted d-block">Date of Birth</small>
                                            <span class="fw-semibold">{{ ($profile?->date_of_birth ?? $tenant->date_of_birth)?->format('M d, Y') ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if($tenant->gender)
                                <div class="info-item">
                                    <div class="d-flex align-items-center">
                                        <div class="info-icon bg-info-light">
                                            <i class="fe fe-users text-info"></i>
                                        </div>
                                        <div class="ms-3">
                                            <small class="text-muted d-block">Gender</small>
                                            <span class="fw-semibold">{{ ucfirst($tenant->gender) }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Address Information -->
                        @if($address)
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="fe fe-map-pin text-danger me-2"></i>Address</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-1">{{ $address->street ?? '' }}</p>
                                <p class="mb-1">
                                    {{ $address->city ?? '' }}{{ $address->city && $address->state ? ', ' : '' }}{{ $address->state ?? '' }} {{ $address->zip ?? '' }}
                                </p>
                                <p class="mb-0 text-muted">{{ $address->country ?? '' }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Emergency Contacts -->
                        @if($tenant->emergencyContacts && $tenant->emergencyContacts->count() > 0)
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="fe fe-phone-call text-danger me-2"></i>Emergency Contacts</h5>
                            </div>
                            <div class="card-body">
                                @foreach($tenant->emergencyContacts as $contact)
                                <div class="emergency-contact {{ !$loop->last ? 'mb-3 pb-3 border-bottom' : '' }}">
                                    <div class="fw-semibold">{{ $contact->name }}</div>
                                    <small class="text-muted">{{ $contact->relationship }}</small>
                                    <div class="mt-1">
                                        <i class="fe fe-phone text-muted me-1"></i>
                                        <span>{{ $contact->phone }}</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Right Column - Lease & Invoices -->
                    <div class="col-xl-8 col-lg-7">
                        <!-- Current Lease -->
                        @if($activeLease)
                        <div class="card current-lease-card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0 text-white">
                                    <i class="fe fe-home me-2"></i>Current Lease
                                </h5>
                            </div>
                            <div class="card-body">
                                @php
                                    $assignment = $activeLease->assignments->where('is_current', true)->first();
                                    $bed = $assignment?->bed;
                                    $room = $bed?->room;
                                    $unit = $room?->unit;
                                    $daysRemaining = max(0, now()->diffInDays($activeLease->end_date, false));
                                    $totalDays = \Carbon\Carbon::parse($activeLease->start_date)->diffInDays($activeLease->end_date);
                                    $progress = $totalDays > 0 ? min(100, (($totalDays - $daysRemaining) / $totalDays) * 100) : 0;
                                @endphp
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="property-icon">
                                                <i class="fe fe-home"></i>
                                            </div>
                                            <div class="ms-3">
                                                <h5 class="mb-1">{{ $activeLease->property->name ?? 'N/A' }}</h5>
                                                <span class="text-muted">
                                                    @if($unit) Unit {{ $unit->name }} @endif
                                                    @if($room) / Room {{ $room->room_number }} @endif
                                                    @if($bed) / {{ $bed->bed_label ?? $bed->bed_number }} @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex justify-content-md-end">
                                            <div class="text-md-end">
                                                <h3 class="text-primary mb-0">${{ number_format($activeLease->rent_amount, 2) }}</h3>
                                                <small class="text-muted">Monthly Rent</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4">
                                        <div class="lease-date-box">
                                            <small class="text-muted">Start Date</small>
                                            <div class="fw-bold">{{ date('M d, Y', strtotime($activeLease->start_date)) }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="lease-date-box">
                                            <small class="text-muted">End Date</small>
                                            <div class="fw-bold">{{ date('M d, Y', strtotime($activeLease->end_date)) }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="lease-date-box">
                                            <small class="text-muted">Days Remaining</small>
                                            <div class="fw-bold text-{{ $daysRemaining < 30 ? 'warning' : 'success' }}">{{ round($daysRemaining) }} days</div>
                                        </div>
                                    </div>
                                </div>
                                {{-- <div class="mt-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="text-muted">Lease Progress</small>
                                        <small class="text-muted">{{ round($progress) }}%</small>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary" style="width: {{ $progress }}%"></div>
                                    </div>
                                </div> --}}
                                <div class="mt-3 d-flex justify-content-start">
                                    <a href="{{ route('leases.show', $activeLease->id) }}" class="btn btn-primary me-2 d-inline-flex align-items-center" title="View lease details">
                                        <i class="fe fe-eye me-1"></i> View Lease Details
                                    </a>
                                    <a href="#" class="btn btn-outline-info me-2 d-inline-flex align-items-center" title="Change bed for the lease" id="changeBedBtn"
                                            data-lease-id="{{ $activeLease->id }}">
                                        <i class="fe fe-edit-3 me-1"></i> Change Bed
                                    </a>
                                    
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <div class="empty-state">
                                    <i class="fe fe-home text-muted" style="font-size: 48px;"></i>
                                    <h5 class="mt-3">No Active Lease</h5>
                                    <p class="text-muted">This tenant doesn't have an active lease at the moment.</p>
                                    <a href="{{ route('leases.create') }}?tenant_id={{ $tenant->id }}" class="btn btn-primary">
                                        <i class="fe fe-plus me-1"></i> Create New Lease
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Invoice Summary Cards -->
                        <div class="row mt-4">
                            <div class="col-md-3 col-6">
                                <div class="card invoice-stat-card">
                                    <div class="card-body text-center">
                                        <div class="stat-icon bg-primary-light mb-2">
                                            <i class="fe fe-file-text text-primary"></i>
                                        </div>
                                        <h4 class="mb-0">{{ $invoiceStats['total'] ?? 0 }}</h4>
                                        <small class="text-muted">Total Invoices</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="card invoice-stat-card">
                                    <div class="card-body text-center">
                                        <div class="stat-icon bg-success-light mb-2">
                                            <i class="fe fe-check-circle text-success"></i>
                                        </div>
                                        <h4 class="mb-0 text-success">{{ $invoiceStats['paid'] ?? 0 }}</h4>
                                        <small class="text-muted">Paid</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="card invoice-stat-card">
                                    <div class="card-body text-center">
                                        <div class="stat-icon bg-warning-light mb-2">
                                            <i class="fe fe-clock text-warning"></i>
                                        </div>
                                        <h4 class="mb-0 text-warning">{{ $invoiceStats['unpaid'] ?? 0 }}</h4>
                                        <small class="text-muted">Due</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="card invoice-stat-card">
                                    <div class="card-body text-center">
                                        <div class="stat-icon bg-danger-light mb-2">
                                            <i class="fe fe-alert-circle text-danger"></i>
                                        </div>
                                        <h4 class="mb-0 text-danger">{{ $invoiceStats['overdue'] ?? 0 }}</h4>
                                        <small class="text-muted">Overdue</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoices Table -->
                        <div class="card" >
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0"><i class="fe fe-file-text text-primary me-2"></i>All Invoices</h5>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary active" data-filter="all">All</button>
                                    <button type="button" class="btn btn-outline-success" data-filter="paid">Paid</button>
                                    <button type="button" class="btn btn-outline-warning" data-filter="unpaid">Due</button>
                                    <button type="button" class="btn btn-outline-danger" data-filter="overdue">Overdue</button>
                                </div>
                            </div>
                            <div class="card-body p-0" style="height: 200px; overflow-y:scroll;">
                                @if(isset($invoices) && $invoices->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="invoicesTable">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Invoice #</th>
                                                <th>Property</th>
                                                <th>Amount</th>
                                                <th>Due Date</th>
                                                <th>Status</th>
                                                <th>Balance</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($invoices as $invoice)
                                            @php
                                                $isOverdue = $invoice->isOverdue();
                                                $isPaid = $invoice->isPaid();
                                                $statusClass = $isPaid ? 'paid' : ($isOverdue ? 'overdue' : 'unpaid');
                                            @endphp
                                            <tr class="invoice-row" data-status="{{ $statusClass }}">
                                                <td>
                                                    <a href="{{ route('invoices.show', $invoice->id) }}" class="fw-semibold text-primary">
                                                        {{ $invoice->invoice_number }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="text-muted">{{ $invoice->lease?->property?->name ?? 'N/A' }}</span>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold">${{ number_format($invoice->total_amount, 2) }}</span>
                                                </td>
                                                <td>
                                                    <span class="{{ $isOverdue ? 'text-danger fw-semibold' : '' }}">
                                                        {{ $invoice->due_date->format('M d, Y') }}
                                                    </span>
                                                    @if($isOverdue)
                                                        <br><small class="text-danger">{{ $invoice->due_date->diffForHumans() }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($isPaid)
                                                        <span class="badge bg-success-light text-success px-2 py-1 d-inline-flex align-items-center">
                                                            <i class="fe fe-check me-1"></i>Paid
                                                        </span>
                                                    @elseif($isOverdue)
                                                        <span class="badge bg-danger-light text-danger px-2 py-1 d-inline-flex align-items-center">
                                                            <i class="fe fe-alert-circle me-1"></i>Overdue
                                                        </span>
                                                    @elseif($invoice->status === 'PARTIAL')
                                                        <span class="badge bg-info-light text-info px-2 py-1 d-inline-flex align-items-center">
                                                            <i class="fe fe-percent me-1"></i>Partial
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning-light text-warning px-2 py-1 d-inline-flex align-items-center">
                                                            <i class="fe fe-clock me-1"></i>Due
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($invoice->balance_due > 0)
                                                        <span class="fw-bold text-danger">${{ number_format($invoice->balance_due, 2) }}</span>
                                                    @else
                                                        <span class="text-success">$0.00</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-sm btn-primary" title="View Invoice">
                                                        <i class="fe fe-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="text-center py-5">
                                    <i class="fe fe-file-text text-muted" style="font-size: 48px;"></i>
                                    <h5 class="mt-3">No Invoices Yet</h5>
                                    <p class="text-muted">No invoices have been generated for this tenant.</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Lease History -->
                        @if($tenant->leases->count() > 1 || ($tenant->leases->count() == 1 && !$activeLease))
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="fe fe-clock text-secondary me-2"></i>Lease History</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Property</th>
                                                <th>Monthly Rent</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($tenant->leases as $lease)
                                            @if($lease->id !== $activeLease?->id)
                                            <tr>
                                                <td>{{ $lease->property->name ?? 'N/A' }}</td>
                                                <td>${{ number_format($lease->rent_amount, 2) }}</td>
                                                <td>{{ date('M d, Y', strtotime($lease->start_date)) }}</td>
                                                <td>{{ date('M d, Y', strtotime($lease->end_date)) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $lease->status === 'ACTIVE' ? 'success' : ($lease->status === 'EXPIRED' ? 'secondary' : 'warning') }}">
                                                        {{ ucfirst(strtolower($lease->status)) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('leases.show', $lease->id) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fe fe-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Payment & Transaction History Section -->
                @include('backend.layouts.tenants.tenant-payment-transaction-view')
            </div>
        </div>
    </div>

    <div class="modal fade" id="changeBedModal" tabindex="-1" aria-labelledby="changeBedModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="changeBedModalContent">
                <form id="changeBedForm">
                    @csrf
                    <input type="hidden" name="lease_id" id="changeBedId">

                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title" id="changeBedModalLabel">
                            <i class="fe fe-alert-triangle me-2"></i> Change Bed Assignment
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Loading State -->
                        <div id="changeBedLoading" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading current lease details...</p>
                        </div>

                        <!-- Content -->
                        <div id="changeBedContent" style="display: none;">
                            <!-- Lease Summary -->
                            <div class="card mb-3">
                                <div class="card-header bg-light py-2">
                                    <strong><i class="fe fe-info me-2"></i> Lease Information</strong>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Tenant:</strong> <span id="changeBedTenant"></span></p>
                                            <p class="mb-1"><strong>Property:</strong> <span id="changeBedProperty"></span></p>
                                            <p class="mb-0"><strong>Current Bed:</strong> <span id="changeBedCurrent" class="badge bg-primary"></span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Lease Period:</strong> <span id="changeBedPeriod"></span></p>
                                            <p class="mb-0"><strong>Monthly Rent:</strong> <span id="changeBedRent" class="text-success fw-bold"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Change Bed Form -->
                            <div id="changeBedFormSection">
                                <div class="alert alert-info mb-3">
                                    <i class="fe fe-info me-2"></i>
                                    Select a new bed from the available beds below. The change will be recorded in the lease history.
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="newBedId" class="form-label"><strong>Select New Bed</strong> <span class="text-danger">*</span></label>
                                            <select class="form-select" id="newBedId" name="new_bed_id" required>
                                                <option value="">-- Select Available Bed --</option>
                                            </select>
                                            <div id="noBedAvailable" class="text-danger mt-2" style="display: none;">
                                                <i class="fe fe-alert-circle me-1"></i> No other beds available in this property.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="effectiveDate" class="form-label"><strong>Effective Date</strong> <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control datepicker2" id="effectiveDate" name="effective_date" placeholder=" Select a effective date" required>

                                            <small class="text-muted">Date when the bed change takes effect</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="changeBedNotes" class="form-label"><strong>Notes</strong></label>
                                    <textarea class="form-control" id="changeBedNotes" name="notes" rows="3" placeholder="Enter reason or notes for the bed change..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning" id="changeBedSubmitBtn" style="display: none;">
                            <span class="spinner-border spinner-border-sm d-none me-2" id="changeBedSpinner"></span>
                            <i class="fe fe-check me-1"></i> Change Bed
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Assign Bed Modal (for leases created without bed assignment) -->
    <div class="modal fade" id="assignBedModal" tabindex="-1" aria-labelledby="assignBedModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="assignBedModalContent">
                <form id="assignBedForm">
                    @csrf
                    <input type="hidden" name="lease_id" id="assignBedLeaseId">

                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="assignBedModalLabel">
                            <i class="fe fe-plus-circle me-2"></i> Assign Bed to Lease
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Loading State -->
                        <div id="assignBedLoading" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading lease details...</p>
                        </div>

                        <!-- Content -->
                        <div id="assignBedContent" style="display: none;">
                            <!-- Lease Summary -->
                            <div class="card mb-3">
                                <div class="card-header bg-light py-2">
                                    <strong><i class="fe fe-info me-2"></i> Lease Information</strong>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Tenant:</strong> <span id="assignBedTenant"></span></p>
                                            <p class="mb-1"><strong>Email:</strong> <span id="assignBedEmail"></span></p>
                                            <p class="mb-0"><strong>Property:</strong> <span id="assignBedProperty"></span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Lease Period:</strong> <span id="assignBedPeriod"></span></p>
                                            <p class="mb-1"><strong>Monthly Rent:</strong> <span id="assignBedRent" class="text-success fw-bold"></span></p>
                                            <p class="mb-0"><strong>Status:</strong> <span id="assignBedStatus" class="badge"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Assign Bed Form -->
                            <div id="assignBedFormSection">
                                <div class="alert alert-success mb-3">
                                    <i class="fe fe-check-circle me-2"></i>
                                    Select a bed to assign to this lease. Once assigned, the tenant can move in on the specified date.
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="assignBedSelect" class="form-label"><strong>Select Bed</strong> <span class="text-danger">*</span></label>
                                            <select class="form-select" id="assignBedSelect" name="bed_id" required>
                                                <option value="">-- Select Available Bed --</option>
                                            </select>
                                            <div id="noAssignBedAvailable" class="text-danger mt-2" style="display: none;">
                                                <i class="fe fe-alert-circle me-1"></i> No beds available in this property.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="moveInDate" class="form-label"><strong>Move-in Date</strong> <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control datepicker2" id="moveInDate" name="move_in_date" placeholder="Select move-in date" required>
                                            <small class="text-muted">Date when the tenant will move in</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="assignBedNotes" class="form-label"><strong>Notes</strong></label>
                                    <textarea class="form-control" id="assignBedNotes" name="notes" rows="3" placeholder="Enter any notes about the bed assignment..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="assignBedSubmitBtn" style="display: none;">
                            <span class="spinner-border spinner-border-sm d-none me-2" id="assignBedSpinner"></span>
                            <i class="fe fe-check me-1"></i> Assign Bed
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Single Email Application Details Modal -->
    <div class="modal fade" id="tenantApplication" tabindex="-1" aria-labelledby="tenantApplicationLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="tenantApplicationLabel">
                        <i class="fe fe-user me-2"></i>Application Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="tenantApplicationDetails">
                        <!-- Details will be loaded here -->
                        <div class="text-center py-4">
                            <div class="spinner-border text-info" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fe fe-x me-1"></i>Close
                    </button>
                    <button type="button" class="btn btn-success d-none" id="approveFromModalBtn">
                        <i class="fe fe-check me-1"></i>Approve & Send Form
                    </button>
                    <button type="button" class="btn btn-danger d-none" id="rejectFromModalBtn">
                        <i class="fe fe-x me-1"></i>Reject
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{asset('backend/plugins/bootstrap-datepicker/js/datepicker.js')}}"></script>
<script>
    // Invoice filter functionality
    document.querySelectorAll('[data-filter]').forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.dataset.filter;

            // Update active button
            document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // Filter rows
            document.querySelectorAll('.invoice-row').forEach(row => {
                if (filter === 'all' || row.dataset.status === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    $(document).ready(function() {
        // Initialize datepicker
        $('.datepicker2').datepicker({
            format: 'mm/dd/yyyy',
            autoclose: true,
            todayHighlight: true,
        });
    });

    $(document).on('click', '#changeBedBtn', function(e) {
        e.preventDefault();
        let leaseId = $(this).data('lease-id');

        // Reset modal state
        $('#changeBedId').val(leaseId);
        $('#changeBedLoading').show();
        $('#changeBedContent').hide();
        $('#changeBedSubmitBtn').hide();
        $('#noBedAvailable').hide();
        $('#changeBedForm')[0].reset();
        $('#newBedId').empty().append('<option value="">-- Select Available Bed --</option>');

        $('#changeBedModal').modal('show');

        // Fetch lease change bed data
        $.ajax({
            url: `{{ url('admin/leases') }}/${leaseId}/change-bed-data`,
            type: 'GET',
            success: function(response) {
                $('#changeBedLoading').hide();
                $('#changeBedContent').show();

                if (response.success) {
                    const lease = response.lease;

                    // Populate lease summary
                    $('#changeBedTenant').text(lease.tenant_name);
                    $('#changeBedProperty').text(lease.property_name);
                    $('#changeBedCurrent').text(lease.current_bed_label);
                    $('#changeBedPeriod').text(lease.start_date + ' - ' + lease.end_date);
                    $('#changeBedRent').text('$' + parseFloat(lease.rent_amount).toLocaleString('en-US', {minimumFractionDigits: 2}));

                    // Set default effective date to today
                    const today = new Date().toLocaleDateString('en-US', {
                                    month: '2-digit',
                                    day: '2-digit',
                                    year: 'numeric'
                                });
                    $('#effectiveDate').val(today);

                    // Populate available beds dropdown
                    const availableBeds = response.available_beds.filter(bed => !bed.is_current);
                    if (availableBeds.length > 0) {
                        availableBeds.forEach(function(bed) {
                            const rentInfo = bed.base_rent ? ` - $${parseFloat(bed.base_rent).toLocaleString('en-US', {minimumFractionDigits: 2})}/month` : '';
                            $('#newBedId').append(`<option value="${bed.id}">${bed.bed_label}</option>`);
                        });
                        $('#changeBedSubmitBtn').show();
                        $('#noBedAvailable').hide();
                    } else {
                        $('#noBedAvailable').show();
                        $('#changeBedSubmitBtn').hide();
                    }
                } else {
                    toastr.error(response.message || 'Failed to load lease data');
                    $('#changeBedModal').modal('hide');
                }
            },
            error: function(xhr) {
                $('#changeBedLoading').hide();
                const response = xhr.responseJSON;
                toastr.error(response?.message || 'Failed to load lease data. Please try again.');
                $('#changeBedModal').modal('hide');
            }
        });
    });

    // Handle change bed form submission
    $('#changeBedForm').on('submit', function(e) {
        e.preventDefault();

        const leaseId = $('#changeBedId').val();
        const newBedId = $('#newBedId').val();
        const effectiveDate = $('#effectiveDate').val();

        if (!newBedId) {
            toastr.error('Please select a new bed');
            return;
        }

        if (!effectiveDate) {
            toastr.error('Please select an effective date');
            return;
        }

        // Show loading state
        $('#changeBedSpinner').removeClass('d-none');
        $('#changeBedSubmitBtn').prop('disabled', true);

        $.ajax({
            url: `{{ url('admin/leases') }}/${leaseId}/change-bed`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                new_bed_id: newBedId,
                effective_date: effectiveDate,
                notes: $('#changeBedNotes').val()
            },
            success: function(response) {
                $('#changeBedSpinner').addClass('d-none');
                $('#changeBedSubmitBtn').prop('disabled', false);

                if (response.success) {
                    toastr.success(response.message || 'Bed changed successfully');
                    $('#changeBedModal').modal('hide');

                    // Reload the page to reflect changes
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    toastr.error(response.message || 'Failed to change bed');
                }
            },
            error: function(xhr) {
                $('#changeBedSpinner').addClass('d-none');
                $('#changeBedSubmitBtn').prop('disabled', false);

                const response = xhr.responseJSON;
                toastr.error(response?.message || 'An error occurred while changing the bed');
            }
        });
    });

    // Assign Bed Modal Handler
    $(document).on('click', '.assignBedBtn', function(e) {
        e.preventDefault();
        let leaseId = $(this).data('lease-id');
        let propertyName = $(this).data('property-name');

        // Reset modal state
        $('#assignBedLeaseId').val(leaseId);
        $('#assignBedLoading').show();
        $('#assignBedContent').hide();
        $('#assignBedSubmitBtn').hide();
        $('#noAssignBedAvailable').hide();
        $('#assignBedForm')[0].reset();
        $('#assignBedSelect').empty().append('<option value="">-- Select Available Bed --</option>');

        $('#assignBedModal').modal('show');

        // Fetch lease assign bed data
        $.ajax({
            url: `{{ url('admin/leases') }}/${leaseId}/assign-bed-data`,
            type: 'GET',
            success: function(response) {
                $('#assignBedLoading').hide();
                $('#assignBedContent').show();

                if (response.success) {
                    const lease = response.lease;

                    // Populate lease summary
                    $('#assignBedTenant').text(lease.tenant_name);
                    $('#assignBedEmail').text(lease.tenant_email);
                    $('#assignBedProperty').text(lease.property_name);
                    $('#assignBedPeriod').text(lease.start_date + ' - ' + lease.end_date);
                    $('#assignBedRent').text('$' + parseFloat(lease.rent_amount).toLocaleString('en-US', {minimumFractionDigits: 2}));

                    // Set status badge
                    const statusClass = lease.status === 'ACTIVE' ? 'bg-success' : 'bg-info';
                    $('#assignBedStatus').text(lease.status).removeClass().addClass('badge ' + statusClass);

                    // Set default move-in date to today
                    const today = new Date().toLocaleDateString('en-US', {
                                    month: '2-digit',
                                    day: '2-digit',
                                    year: 'numeric'
                                });
                    console.log(today);
                    
                    $('#moveInDate').val(today);

                    // Populate available beds dropdown
                    const availableBeds = response.available_beds;
                    if (availableBeds.length > 0) {
                        availableBeds.forEach(function(bed) {
                            const rentInfo = bed.base_rent ? ` - $${parseFloat(bed.base_rent).toLocaleString('en-US', {minimumFractionDigits: 2})}/month` : '';
                            $('#assignBedSelect').append(`<option value="${bed.id}">${bed.bed_label}</option>`);
                        });
                        $('#assignBedSubmitBtn').show();
                        $('#noAssignBedAvailable').hide();
                    } else {
                        $('#noAssignBedAvailable').show();
                        $('#assignBedSubmitBtn').hide();
                    }
                } else {
                    toastr.error(response.message || 'Failed to load lease data');
                    $('#assignBedModal').modal('hide');
                }
            },
            error: function(xhr) {
                $('#assignBedLoading').hide();
                const response = xhr.responseJSON;
                toastr.error(response?.message || 'Failed to load lease data. Please try again.');
                $('#assignBedModal').modal('hide');
            }
        });
    });

    // Handle assign bed form submission
    $('#assignBedForm').on('submit', function(e) {
        e.preventDefault();

        const leaseId = $('#assignBedLeaseId').val();
        const bedId = $('#assignBedSelect').val();
        const moveInDate = $('#moveInDate').val();

        if (!bedId) {
            toastr.error('Please select a bed');
            return;
        }

        if (!moveInDate) {
            toastr.error('Please select a move-in date');
            return;
        }

        // Show loading state
        $('#assignBedSpinner').removeClass('d-none');
        $('#assignBedSubmitBtn').prop('disabled', true);

        $.ajax({
            url: `{{ url('admin/leases') }}/${leaseId}/assign-bed`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                bed_id: bedId,
                move_in_date: moveInDate,
                notes: $('#assignBedNotes').val()
            },
            success: function(response) {
                $('#assignBedSpinner').addClass('d-none');
                $('#assignBedSubmitBtn').prop('disabled', false);

                if (response.success) {
                    toastr.success(response.message || 'Bed assigned successfully');
                    $('#assignBedModal').modal('hide');

                    // Reload the page to reflect changes
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    toastr.error(response.message || 'Failed to assign bed');
                }
            },
            error: function(xhr) {
                $('#assignBedSpinner').addClass('d-none');
                $('#assignBedSubmitBtn').prop('disabled', false);

                const response = xhr.responseJSON;
                toastr.error(response?.message || 'An error occurred while assigning the bed');
            }
        });
    });

            // View Single Email Application Details
    function viewApplicationDetails(id) {
        console.log(id);
        
        // Show modal
        $('#tenantApplication').modal('show');

        // Reset modal content to loading state
        $('#tenantApplicationDetails').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-info" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);

        // Load application details
        $.ajax({
            url: `/admin/applications/${id}`,
            type: 'GET',
            success: function(response) {
                displaytenantApplicationDetails(response);
            },
            error: function(xhr) {
                $('#tenantApplicationDetails').html(`
                    <div class="alert alert-danger">
                        <i class="fe fe-alert-triangle me-2"></i>
                        Failed to load application details. Please try again.
                    </div>
                `);
            }
        });
    }

    // Display Single Email Application Details in Modal
    function displaytenantApplicationDetails(application) {
        // Format dates
        const formatDate = (dateStr) => {
            if (!dateStr) return 'N/A';
            return new Date(dateStr).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        };

        // Build document section if documents exist
        let documentsHtml = '';
        const hasDocuments = application.passport_copy_url || application.visa_document_url || 
                            application.front_id_document_url || application.back_id_document_url;

        if (hasDocuments) {
            documentsHtml = `
                <div class="mb-4">
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="fe fe-file-text text-info me-2"></i>Documents
                    </h6>
                    <div class="row">
                        ${application.passport_copy_url ? `
                            <div class="col-md-3 mb-3">
                                <small class="text-muted d-block">Passport Copy</small>
                                <a href="${application.passport_copy_url}" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="fe fe-eye me-1"></i>View
                                </a>
                            </div>
                        ` : ''}
                        ${application.visa_document_url ? `
                            <div class="col-md-3 mb-3">
                                <small class="text-muted d-block">Visa Document</small>
                                <a href="${application.visa_document_url}" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="fe fe-eye me-1"></i>View
                                </a>
                            </div>
                        ` : ''}
                        ${application.front_id_document_url ? `
                            <div class="col-md-3 mb-3">
                                <small class="text-muted d-block">Front ID</small>
                                <a href="${application.front_id_document_url}" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="fe fe-eye me-1"></i>View
                                </a>
                            </div>
                        ` : ''}
                        ${application.back_id_document_url ? `
                            <div class="col-md-3 mb-3">
                                <small class="text-muted d-block">Back ID</small>
                                <a href="${application.back_id_document_url}" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="fe fe-eye me-1"></i>View
                                </a>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }

        // Build employer info section if exists
        let employerHtml = '';
        if (application.employer_info && Object.keys(application.employer_info).length > 0) {
            const employer = application.employer_info;
            console.log(employer);
            employer.forEach(data => {
                employerinfo = `
                    <div class="row">
                        ${data.company_name ? `
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Company Name</small>
                                <strong>${data.company_name}</strong>
                            </div>
                        ` : 'N/A    '}
                            ${data.job_title ? `
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Job Title</small>
                                <strong>${data.job_title}</strong>
                            </div>
                        ` : 'N/A    '}
                        ${data.employer_contact_person_name ? `
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Contact Person</small>
                                <strong>${data.employer_contact_person_name}</strong>
                            </div>
                        ` : ''}
                        ${data.employer_contact_person_phone ? `
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Phone</small>
                                <strong>${data.employer_contact_person_phone}</strong>
                            </div>
                        ` : ''}
                        ${data.employer_contact_person_email ? `
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Email</small>
                                <strong>${data.employer_contact_person_email}</strong>
                            </div>
                        ` : ''}
                        ${data.company_address ? `
                            <div class="col-md-12 mb-3">
                                <small class="text-muted d-block">Address</small>
                                <strong>${data.company_address}</strong>
                            </div>
                        ` : ''}
                    </div>
                `;
            });
            employerHtml = `
                <div class="mb-4">
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="fe fe-briefcase text-info me-2"></i>Employer Information
                    </h6>
                    ${employerinfo}
                </div>
            `;
        }

        // Build sponsor info section if exists
        let sponsorHtml = '';
        if (application.sponsor_name) {
            sponsorHtml = `
                <div class="mb-4">
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="fe fe-users text-info me-2"></i>Sponsor Information
                    </h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Sponsor Name</small>
                            <strong>${application.sponsor_name || 'N/A'}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Phone</small>
                            <strong>${application.sponsor_phone || 'N/A'}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Email</small>
                            <strong>${application.sponsor_email || 'N/A'}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Address</small>
                            <strong>${[
                                application.sponsor_city,
                                application.sponsor_state,
                                application.sponsor_zipcode,
                                application.sponsor_country
                            ].filter(Boolean).join(', ') || 'N/A'}</strong>
                        </div>
                        ${application.is_j1_sponsor ? `
                            <div class="col-md-12 mb-3">
                                <small class="text-muted d-block">J-1 Sponsor</small>
                                <span class="badge bg-info">${application.is_j1_sponsor}</span>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }

        const html = `
            <div class="application-details">
                <!-- Personal Information -->
                <div class="mb-4">
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="fe fe-user text-info me-2"></i>Personal Information
                    </h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Full Name</small>
                            <strong>${[application.first_name, application.middle_name, application.last_name].filter(Boolean).join(' ') || 'N/A'}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Email</small>
                            <strong>${application.email || 'N/A'}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Phone</small>
                            <strong>${application.phone || 'N/A'}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Date of Birth</small>
                            <strong>${formatDate(application.date_of_birth)}</strong>
                        </div>

                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Gender</small>
                            <strong>${application.gender || 'N/A'}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Country of Origin</small>
                            <strong>${application.country_of_origin || 'N/A'}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Interested in Property</small>
                            <strong class="badge bg-info p-3">${application.property.name || 'N/A'}</strong>
                        </div>
                    </div>
                </div>

                <!-- Travel Information -->
                <div class="mb-4">
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="fe fe-calendar text-info me-2"></i>Travel Information
                    </h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Arrival Date</small>
                            <strong>${formatDate(application.arrival_date)}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Departure Date</small>
                            <strong>${formatDate(application.departure_date)}</strong>
                        </div>
                    </div>
                </div>

                ${documentsHtml}
                ${employerHtml}
                ${sponsorHtml}

                <!-- Additional Notes -->
                ${application.notes ? `
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fe fe-message-square text-info me-2"></i>Additional Notes
                        </h6>
                        <div class="alert alert-light border">
                            ${application.notes}
                        </div>
                    </div>
                ` : ''}

                <!-- Application Status -->
                <div class="mb-3">
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="fe fe-info text-info me-2"></i>Application Status
                    </h6>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Status</small>
                            <span class="badge p-2 bg-${application.status === 'pending' ? 'warning' : application.status === 'approved' ? 'success' : 'danger'}">
                                ${application.status.charAt(0).toUpperCase() + application.status.slice(1).replace('_', ' ')}
                            </span>
                        </div>
                        ${application.application_type ? `
                            <div class="col-md-4 mb-2">
                                <small class="text-muted d-block">Application Type</small>
                                <strong>${application.application_type}</strong>
                            </div>
                        ` : ''}
                        ${application.application_number ? `
                            <div class="col-md-4 mb-2">
                                <small class="text-muted d-block">Application Number</small>
                                <strong>${application.application_number}</strong>
                            </div>
                        ` : ''}
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Submitted At</small>
                            <strong>${new Date(application.created_at).toLocaleString()}</strong>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#tenantApplicationDetails').html(html);

        // Show/hide action buttons based on status
        if (application.status === 'pending') {
            $('#approveFromModalBtn').removeClass('d-none').off('click').on('click', function() {
                $('#tenantApplication').modal('hide');
                approveSingleEmail(application.id);
            });
            $('#rejectFromModalBtn').removeClass('d-none').off('click').on('click', function() {
                $('#tenantApplication').modal('hide');
                rejectApplication(application.id);
            });
        } else {
            $('#approveFromModalBtn').addClass('d-none');
            $('#rejectFromModalBtn').addClass('d-none');
        }
    }
</script>



<script>
    // Global variables
    const tenantId = {{ $tenant->id }};
    let paymentsDataTable;
    let transactionsDataTable;

    $(document).ready(function() {
        // Load payment history on page load
        loadPaymentHistory();

        // Load transactions when tab is clicked
        $('#transactions-tab').on('click', function() {
            if (!transactionsDataTable) {
                loadTransactionHistory();
            }
        });
    });

    /**
     * Load Payment History
     */
    function loadPaymentHistory() {
        $('#paymentsLoading').show();
        $('#paymentsContent').hide();
        $('#paymentsEmpty').hide();

        $.ajax({
            url: `{{ route('tenants.payments.history', ':id') }}`.replace(':id', tenantId),
            type: 'GET',
            success: function(response) {
                $('#paymentsLoading').hide();

                if (response.success && response.data.length > 0) {
                    renderPayments(response.data);
                    $('#paymentsContent').show();
                } else {
                    $('#paymentsEmpty').show();
                }
            },
            error: function(xhr) {
                $('#paymentsLoading').hide();
                $('#paymentsEmpty').show();
                toastr.error('Failed to load payment history');
            }
        });
    }

    /**
     * Render Payments Table
     */
    function renderPayments(payments) {
        const tbody = $('#paymentsTableBody');
        tbody.empty();

        payments.forEach(function(payment) {
            const statusBadge = getReviewStatusBadge(payment.review_status);
            const methodBadge = getPaymentMethodBadge(payment.payment_method);

            const row = `
            <tr>
                <td><strong>${payment.payment_number}</strong></td>
                <td>${payment.invoice_number}</td>
                <td>${formatDate(payment.payment_date)}</td>
                <td><strong class="text-success">$${payment.amount}</strong></td>
                <td>${methodBadge}</td>
                <td><span class="badge bg-success p-3">${payment.payment_type}</span></td>
                <td><code class="small">${truncateText(payment.reference_number, 15)}</code></td>
                <td>${payment.property_name}</td>
                <td>${statusBadge}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-primary" onclick="viewPaymentDetails(${payment.id})"
                            title="View Details">
                        <i class="fe fe-eye"></i>
                    </button>
                </td>
            </tr>
        `;
            tbody.append(row);
        });

        // Initialize DataTable
        if ($.fn.DataTable.isDataTable('#paymentsTable')) {
            $('#paymentsTable').DataTable().destroy();
        }

        paymentsDataTable = $('#paymentsTable').DataTable({
            order: [
                [2, 'desc']
            ], // Sort by date descending
            pageLength: 25,
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        });
    }

    /**
     * Load Transaction History
     */
    function loadTransactionHistory() {
        $('#transactionsLoading').show();
        $('#transactionsContent').hide();
        $('#transactionsEmpty').hide();

        $.ajax({
            url: `{{ route('tenants.transactions.history', ':id') }}`.replace(':id', tenantId),
            type: 'GET',
            success: function(response) {
                $('#transactionsLoading').hide();

                if (response.success && response.data.length > 0) {
                    renderTransactions(response.data);
                    $('#transactionsContent').show();
                } else {
                    $('#transactionsEmpty').show();
                }
            },
            error: function(xhr) {
                $('#transactionsLoading').hide();
                $('#transactionsEmpty').show();
                toastr.error('Failed to load transaction history');
            }
        });
    }

    /**
     * Render Transactions Table
     */
    function renderTransactions(transactions) {
        const tbody = $('#transactionsTableBody');
        tbody.empty();

        transactions.forEach(function(transaction) {
            const entryBadge = transaction.entry_type === 'Credit' ?
                '<span class="badge bg-success p-3">Credit</span>' :
                '<span class="badge bg-danger p-3">Debit</span>';

            const row = `
            <tr>
                <td><strong>${transaction.transaction_number}</strong></td>
                <td>${formatDate(transaction.transaction_date)}</td>
                <td><span class="badge bg-info p-3">${transaction.type}</span></td>
                <td>${entryBadge}</td>
                <td><strong>$${transaction.amount}</strong></td>
                <td>${transaction.invoice_number}</td>
                <td>${transaction.payment_number}</td>
                <td>${transaction.property_name}</td>
                <td class="small">${truncateText(transaction.description, 40)}</td>
            </tr>
        `;
            tbody.append(row);
        });

        // Initialize DataTable
        if ($.fn.DataTable.isDataTable('#transactionsTable')) {
            $('#transactionsTable').DataTable().destroy();
        }

        transactionsDataTable = $('#transactionsTable').DataTable({
            order: [
                [1, 'desc']
            ], // Sort by date descending
            pageLength: 25,
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        });
    }

    /**
     * View Payment Details
     */
    function viewPaymentDetails(paymentId) {
        $('#paymentDetailsModal').modal('show');
        $('#paymentDetailsLoading').show();
        $('#paymentDetailsContent').hide();

        $.ajax({
            url: `{{ route('tenants.payments.details', ':id') }}`.replace(':id', paymentId),
            type: 'GET',
            success: function(response) {
                $('#paymentDetailsLoading').hide();

                if (response.success) {
                    populatePaymentDetails(response);
                    $('#paymentDetailsContent').show();
                } else {
                    toastr.error('Failed to load payment details');
                    $('#paymentDetailsModal').modal('hide');
                }
            },
            error: function(xhr) {
                $('#paymentDetailsLoading').hide();
                toastr.error('Failed to load payment details');
                $('#paymentDetailsModal').modal('hide');
            }
        });
    }

    /**
     * Populate Payment Details Modal
     */
    function populatePaymentDetails(data) {
        const {
            payment,
            tenant,
            invoice,
            lease,
            metadata
        } = data;

        // Payment Information
        $('#detailPaymentNumber').text(payment.payment_number);
        $('#detailAmount').text('$' + parseFloat(payment.amount).toFixed(2));
        $('#detailPaymentDate').text(formatDate(payment.payment_date));
        $('#detailPaymentMethod').text(payment.payment_method.toUpperCase());
        $('#detailPaymentType').text(payment.payment_type.toUpperCase());
        $('#detailPaidBy').text(payment.paid_by);
        $('#detailCreatedAt').text(payment.created_at);

        // Gateway Information
        $('#detailReferenceNumber').text(payment.reference_number || 'N/A');
        $('#detailTransactionId').text(payment.gateway_transaction_id || 'N/A');

        // Tenant Information
        $('#detailTenantName').text(tenant.name);
        $('#detailTenantEmail').text(tenant.email);
        $('#detailTenantPhone').text(tenant.phone);
        $('#detailTenantAddress').text(tenant.address);

        // Invoice Information
        $('#detailInvoiceNumber').text(invoice.invoice_number);
        $('#detailInvoiceType').text(invoice.type);
        $('#detailInvoiceTotal').text('$' + parseFloat(invoice.total_amount).toFixed(2));
        $('#detailInvoicePaid').text('$' + parseFloat(invoice.paid_amount).toFixed(2));
        $('#detailInvoiceBalance').text('$' + parseFloat(invoice.balance_due).toFixed(2));

        const invoiceStatusClass = invoice.status === 'PAID' ? 'bg-success' :
            (invoice.status === 'PARTIAL' ? 'bg-warning' : 'bg-danger');
        $('#detailInvoiceStatus').removeClass().addClass('badge ' + invoiceStatusClass).text(invoice.status);
        $('#detailInvoiceDueDate').text(invoice.due_date);

        // Lease Information
        $('#detailLeaseProperty').text(lease.property_name);
        $('#detailLeaseUnit').text(lease.unit);
        $('#detailLeaseRent').text('$' + parseFloat(lease.rent_amount).toFixed(2));
        $('#detailLeasePeriod').text(lease.start_date + ' - ' + lease.end_date);

        // Review Status
        $('#reviewPaymentId').val(payment.id);
        $('#reviewStatus').val(payment.review_status || 'pending');
        $('#reviewNote').val(payment.review_note || '');

        if (payment.reviewed_at) {
            $('#reviewInfo').show();
            $('#lastReviewedAt').text(payment.reviewed_at);
            $('#lastReviewedBy').text(payment.reviewed_by_name || 'System');
        } else {
            $('#reviewInfo').hide();
        }

        // Notes
        $('#detailNote').text(payment.note || 'No notes available');
    }

    /**
     * Submit Payment Review Form
     */
    $('#paymentReviewForm').on('submit', function(e) {
        e.preventDefault();

        const paymentId = $('#reviewPaymentId').val();
        const formData = {
            review_status: $('#reviewStatus').val(),
            review_note: $('#reviewNote').val()
        };

        NProgress.start();

        $.ajax({
            url: `{{ route('tenants.payments.review', ':id') }}`.replace(':id', paymentId),
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                NProgress.done();

                if (response.success) {
                    toastr.success('Payment review updated successfully');
                    $('#paymentDetailsModal').modal('hide');
                    refreshPayments();
                } else {
                    toastr.error(response.message || 'Failed to update review');
                }
            },
            error: function(xhr) {
                NProgress.done();
                toastr.error('Failed to update payment review');
            }
        });
    });

    /**
     * Refresh Functions
     */
    function refreshPayments() {
        if (paymentsDataTable) {
            paymentsDataTable.destroy();
        }
        loadPaymentHistory();
    }

    function refreshTransactions() {
        if (transactionsDataTable) {
            transactionsDataTable.destroy();
        }
        loadTransactionHistory();
    }

    /**
     * Export Payments to CSV
     */
    function exportPayments() {
        window.location.href = `{{ route('tenants.payments.export', ':id') }}`.replace(':id', tenantId);
    }

    /**
     * Helper Functions
     */
    function getReviewStatusBadge(status) {
        const badges = {
            'pending': '<span class="badge bg-warning p-3">Pending</span>',
            'reviewed': '<span class="badge bg-info p-3">Reviewed</span>',
            'confirmed': '<span class="badge bg-success p-3">Confirmed</span>',
            'disputed': '<span class="badge bg-danger p-3">Disputed</span>'
        };
        return badges[status] || badges['pending'];
    }

    function getPaymentMethodBadge(method) {
        const methodFormatted = method.charAt(0).toUpperCase() + method.slice(1).toLowerCase();
        const badges = {
            'stripe': '<span class="badge bg-primary d-inline-flex align-items-center"><i class="fe fe-credit-card me-1"></i>Stripe</span>',
            'cash': '<span class="badge bg-success d-inline-flex align-items-center"><i class="fe fe-dollar-sign me-1"></i>Cash</span>',
            'check': '<span class="badge bg-info d-inline-flex align-items-center"><i class="fe fe-file-text me-1"></i>Check</span>',
            'bank_transfer': '<span class="badge bg-secondary d-inline-flex align-items-center"><i class="fe fe-send me-1"></i>Transfer</span>'
        };
        return badges[method.toLowerCase()] || `<span class="badge bg-secondary d-inline-flex align-items-center">${methodFormatted}</span>`;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    function truncateText(text, maxLength) {
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }

    // Approve tenant
    function approveTenantDetails(id, status) {
    const actionText = status === 'approved' ? 'Approve' : 'Reject';
    const actionColor = status === 'approved' ? '#28a745' : '#dc3545';
    const actionIcon = status === 'approved' ? 'question' : 'warning';
    const actionMsg = status === 'approved'
        ? 'This will approve the tenant and send a password setup email.'
        : 'This will reject the tenant and send a notification email.';

    Swal.fire({
        title: actionText + ' Tenant?',
        text: actionMsg,
        icon: actionIcon,
        showCancelButton: true,
        confirmButtonColor: actionColor,
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, ' + actionText + '!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            NProgress.start();

            $.ajax({
                url: "{{ route('tenants.approve', ':id') }}".replace(':id', id),
                type: 'POST',
                data: { status: status },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    NProgress.done();
                    if (response.success) {
                        toastr.success(response.message);
                        // Reload page to reflect status change
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    NProgress.done();
                    toastr.error(xhr.responseJSON?.message || 'Failed to update status!');
                }
            });
        }
    });
}
</script>
@endpush

@push('styles')
<style>
    /* Profile Card */
    .tenant-profile-card {
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .tenant-avatar-wrapper {
        position: relative;
        display: inline-block;
    }

    .tenant-avatar-lg {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    }

    .status-badge {
        position: absolute;
        bottom: 5px;
        right: 5px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        border: 3px solid #fff;
    }

    .status-badge.active {
        background: #22c55e;
        color: #fff;
    }

    /* Info Icons */
    .info-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .bg-primary-light { background: rgba(99, 102, 241, 0.1); }
    .bg-success-light { background: rgba(34, 197, 94, 0.1); }
    .bg-warning-light { background: rgba(245, 158, 11, 0.1); }
    .bg-danger-light { background: rgba(239, 68, 68, 0.1); }
    .bg-info-light { background: rgba(59, 130, 246, 0.1); }

    /* Current Lease Card */
    .current-lease-card {
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .property-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 24px;
    }

    .lease-date-box {
        background: #f8f9fa;
        padding: 12px 15px;
        border-radius: 8px;
        text-align: center;
    }

    /* Invoice Stat Cards */
    .invoice-stat-card {
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s;
    }

    .invoice-stat-card:hover {
        transform: translateY(-3px);
    }

    .stat-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin: 0 auto;
    }

    /* Invoice Table */
    #invoicesTable th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        color: #6c757d;
        border-bottom: 2px solid #e9ecef;
    }

    .invoice-row {
        transition: background-color 0.2s;
    }

    .invoice-row:hover {
        background-color: #f8f9fa;
    }

    /* Filter Buttons */
    .btn-group .btn.active {
        font-weight: 600;
    }

    /* Empty State */
    .empty-state {
        padding: 20px;
    }

    /* Cards */
    .card {
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
    }

    .card-header {
        background: #fff;
        border-bottom: 1px solid #e9ecef;
        padding: 15px 20px;
    }

    .card-title {
        font-weight: 600;
        color: #2c3e50;
    }

    /* Responsive */
    @media (max-width: 991px) {
        .tenant-avatar-lg {
            width: 100px;
            height: 100px;
        }
    }
    .bed-assignments-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .bed-assignment-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 5px 15px;
        border-radius: 8px;
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }

    .bed-assignment-item.current-bed {
        background: linear-gradient(135deg, #e3f2fd 0%, #f0f7ff 100%);
        border-color: #2196F3;
        box-shadow: 0 2px 8px rgba(33, 150, 243, 0.15);
    }

    .bed-assignment-item.past-bed {
        background-color: #f8f9fa;
        border-color: #e9ecef;
    }

    .bed-assignment-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .bed-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        font-size: 20px;
        flex-shrink: 0;
    }

    .current-bed .bed-icon {
        background: linear-gradient(135deg, #2196F3, #1976D2);
        color: white;
    }

    .past-bed .bed-icon {
        background-color: #dee2e6;
        color: #6c757d;
    }

    .bed-details {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .bed-label {
        font-size: 14px;
        font-weight: 600;
        color: #212529;
    }

    .current-bed .bed-label {
        color: #1976D2;
    }

    .bed-status-badge {
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .bed-status-badge.current {
        background-color: #4CAF50;
        color: white;
    }

    .bed-status-badge.past {
        background-color: #e9ecef;
        color: #6c757d;
    }

    /* Responsive adjustments */
    @media (max-width: 576px) {
        .bed-assignment-item {
            padding: 12px;
            gap: 12px;
        }

        .bed-icon {
            width: 40px;
            height: 40px;
            font-size: 18px;
        }

        .bed-label {
            font-size: 14px;
        }
    }
</style>
@endpush
