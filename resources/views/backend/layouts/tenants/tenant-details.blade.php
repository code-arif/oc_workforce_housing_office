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
                        <a href="{{ route('tenants.index') }}" class="btn btn-outline-secondary">
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
                                <div class="d-flex justify-content-center gap-2 mb-3">
                                    <span class="badge bg-{{ $tenant->status === 'approved' || $tenant->status === 'active' ? 'success' : ($tenant->status === 'pending' ? 'warning' : 'secondary') }} px-3 py-2">
                                        {{ ucfirst($tenant->status) }}
                                    </span>
                                    @if($activeLease)
                                        <span class="badge bg-primary px-3 py-2">Active Lease</span>
                                    @endif
                                </div>
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
                                                    @if($unit) Unit {{ $unit->unit_number }} @endif
                                                    @if($room) / Room {{ $room->room_number }} @endif
                                                    @if($bed) / {{ $bed->bed_number ?? $bed->bed_label }} @endif
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
                                            <div class="fw-bold text-{{ $daysRemaining < 30 ? 'warning' : 'success' }}">{{ $daysRemaining }} days</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="text-muted">Lease Progress</small>
                                        <small class="text-muted">{{ round($progress) }}%</small>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary" style="width: {{ $progress }}%"></div>
                                    </div>
                                </div>
                                <div class="mt-3 text-end">
                                    <a href="{{ route('leases.show', $activeLease->id) }}" class="btn btn-primary">
                                        <i class="fe fe-eye me-1"></i> View Lease Details
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
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0"><i class="fe fe-file-text text-primary me-2"></i>All Invoices</h5>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary active" data-filter="all">All</button>
                                    <button type="button" class="btn btn-outline-success" data-filter="paid">Paid</button>
                                    <button type="button" class="btn btn-outline-warning" data-filter="unpaid">Due</button>
                                    <button type="button" class="btn btn-outline-danger" data-filter="overdue">Overdue</button>
                                </div>
                            </div>
                            <div class="card-body p-0">
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
                                                        <span class="badge bg-success-light text-success px-3 py-2">
                                                            <i class="fe fe-check me-1"></i>Paid
                                                        </span>
                                                    @elseif($isOverdue)
                                                        <span class="badge bg-danger-light text-danger px-3 py-2">
                                                            <i class="fe fe-alert-circle me-1"></i>Overdue
                                                        </span>
                                                    @elseif($invoice->status === 'PARTIAL')
                                                        <span class="badge bg-info-light text-info px-3 py-2">
                                                            <i class="fe fe-percent me-1"></i>Partial
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning-light text-warning px-3 py-2">
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

            </div>
        </div>
    </div>
@endsection

@push('scripts')
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
</style>
@endpush
