@extends('backend.app')

@section('title', 'Lease Details')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid p-0">

                <!-- Lease Detail Container -->
                <div class="lease-detail-container">

                    {{-- <!-- Left Sidebar - Lease List -->
                    <div class="lease-sidebar">
                        <div class="sidebar-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Leases</h5>
                                <a href="{{ route('leases.index') }}" class="btn btn-sm btn-light">
                                    <i class="fe fe-arrow-left"></i>
                                </a>
                            </div>
                            <div class="search-box mt-3">
                                <input type="text" class="form-control" id="leaseSearch" placeholder="Search leases...">
                            </div>
                        </div>

                        <div class="lease-list">
                            @foreach ($leases as $l)
                                @php
                                    $lProfile = $l->tenant ? $l->tenant->profile : null;
                                    $lTenantName = $lProfile
                                        ? trim(
                                            $lProfile->first_name .
                                                ' ' .
                                                ($lProfile->middle_name ?? '') .
                                                ' ' .
                                                ($lProfile->last_name ?? ''),
                                        )
                                        : 'No Tenant';

                                    $lAssignment = $l->assignments->where('is_current', true)->first();
                                    $lUnit = $lAssignment && $lAssignment->bed ? $lAssignment->bed->bed_label : 'N/A';

                                    $statusColors = [
                                        'DRAFT' => 'secondary',
                                        'PENDING_TENANT_SIGN' => 'warning',
                                        'PENDING_ADMIN_SIGN' => 'info',
                                        'ACTIVE' => 'success',
                                        'TERMINATED' => 'danger',
                                        'COMPLETED' => 'dark',
                                        'FUTURE' => 'primary',
                                    ];

                                    // Determine display status
                                    $displayStatus = $l->status;
                                    if ($l->status == 'DRAFT' && $l->start_date > now()) {
                                        $displayStatus = 'FUTURE';
                                    } elseif (in_array($l->status, ['TERMINATED', 'COMPLETED'])) {
                                        $displayStatus = 'EXPIRING';
                                    }

                                    $statusColor = $statusColors[$displayStatus] ?? 'secondary';
                                    $statusLabel = str_replace('_', ' ', ucwords(strtolower($displayStatus)));
                                @endphp
                                <div class="lease-item {{ $l->id == $lease->id ? 'active' : '' }}"
                                    data-lease-id="{{ $l->id }}" onclick="loadLeaseDetails({{ $l->id }})">
                                    <div class="lease-status-indicator bg-{{ $statusColor }}"></div>
                                    <div class="lease-info">
                                        <div class="lease-property">
                                            <strong>{{ $l->property ? $l->property->name : 'N/A' }}</strong> |
                                            {{ $lUnit }}
                                        </div>
                                        <div class="lease-tenant">{{ $lTenantName }}</div>
                                        <div class="lease-dates">
                                            {{ date('M d, Y', strtotime($l->start_date)) }} -
                                            {{ date('M d, Y', strtotime($l->end_date)) }}
                                        </div>
                                    </div>
                                    <div class="lease-badge">
                                        <span class="badge badge-sm bg-{{ $statusColor }}">{{ $statusLabel }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div> --}}

                    <!-- Right Content - Lease Details -->
                    <div class="lease-content">
                        <div class="content-header">
                            <button class="btn btn-light mobile-sidebar-toggle d-lg-none" onclick="toggleSidebar()">
                                <i class="fe fe-menu"></i>
                            </button>
                            <h4 class="mb-0">Lease Detail</h4>
                            <button class="btn btn-light close-detail"
                                onclick="window.location='{{ route('leases.index') }}'">
                                <i class="fe fe-x"></i>
                            </button>
                        </div>

                        <div class="lease-detail-content" id="leaseDetailContent">
                            <!-- Lease Header -->
                            <div class="lease-header">
                                <div class="d-flex align-items-start justify-content-between flex-wrap">
                                    <div class="d-flex align-items-center mb-3">
                                        @php
                                            $statusColors = [
                                                'DRAFT' => 'secondary',
                                                'PENDING_TENANT_SIGN' => 'warning',
                                                'PENDING_ADMIN_SIGN' => 'info',
                                                'ACTIVE' => 'success',
                                                'TERMINATED' => 'danger',
                                                'COMPLETED' => 'dark',
                                            ];

                                            $statusLabels = [
                                                'DRAFT' => 'Draft',
                                                'PENDING_TENANT_SIGN' => 'Pending Tenant Sign',
                                                'PENDING_ADMIN_SIGN' => 'Pending Admin Sign',
                                                'ACTIVE' => 'Active',
                                                'TERMINATED' => 'Terminated',
                                                'COMPLETED' => 'Completed',
                                            ];

                                            $statusColor = $statusColors[$lease->status] ?? 'secondary';
                                            $statusLabel = $statusLabels[$lease->status] ?? $lease->status;
                                        @endphp
                                        <span
                                            class="badge bg-{{ $statusColor }} me-3 fs-6 lease-status-badge">{{ $statusLabel }}</span>

                                        @php
                                            $document = $lease->documents->first();
                                        @endphp
                                        @if ($document && $document->tenant_signed_at && $document->admin_signed_at)
                                            <span class="badge bg-success-transparent text-success">
                                                <i class="fe fe-check-circle me-1"></i> Signed Online
                                            </span>
                                        @elseif($document && $document->tenant_signed_at)
                                            <span class="badge bg-warning-transparent text-warning">
                                                <i class="fe fe-clock me-1"></i> Pending Admin Sign
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-end mb-3">
                                        @if ($lease->status == 'PENDING_TENANT_SIGN' || $lease->status == 'DRAFT')
                                            <a href="{{ route('leases.resend.signature', $lease->id) }}"
                                                class="btn btn-outline-primary me-2 d-inline-flex align-items-center gap-1"
                                                id="resendSignatureMail">
                                                <i class="fe fe-mail"></i>
                                                <span>Resend Signature Request</span>
                                            </a>
                                        @elseif($lease->status == 'COMPLETED')
                                            <a href="#" class="btn btn-outline-secondary me-2 disabled"
                                                title="Lease is already completed">
                                                <i class="fe fe-check-circle me-1"></i> Lease Closed
                                            </a>
                                        @else
                                            <a href="#" class="btn btn-outline-primary me-2" id="manuallyCloseLease"
                                                data-lease-id="{{ $lease->id }}" title="Manually closed the lease">
                                                <i class="fe fe-mail me-1"></i> Manually Close Lease
                                            </a>
                                            <a href="#" class="btn btn-outline-info me-2"
                                                title="Change bed for the lease" id="changeBedBtn"
                                                data-lease-id="{{ $lease->id }}">
                                                <i class="fe fe-edit-3 me-1"></i> Change Bed
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                @php
                                    $profile = $lease->tenant ? $lease->tenant->profile : null;
                                    $fullName = $profile
                                        ? trim(
                                            $profile->first_name .
                                                ' ' .
                                                ($profile->middle_name ?? '') .
                                                ' ' .
                                                ($profile->last_name ?? ''),
                                        )
                                        : 'No Tenant';
                                    $avatar =
                                        $profile && $profile->avatar
                                            ? asset($profile->avatar)
                                            : 'https://ui-avatars.com/api/?name=' .
                                                urlencode($fullName) .
                                                '&background=random';

                                    $totalInvoiced = $lease->invoices->where('type', 'RENT')->sum('amount');
                                    $totalPaid = $lease->invoices
                                        ->where('type', 'RENT')
                                        ->where('status', 'PAID')
                                        ->sum('amount');
                                    $outstanding =
                                        $lease->invoices
                                            ->where('type', 'RENT')
                                            ->whereIn('status', ['UNPAID', 'PARTIAL'])
                                            ->sum('amount') -
                                        $lease->invoices
                                            ->where('type', 'RENT')
                                            ->where('status', 'PARTIAL')
                                            ->sum('amount_paid');
                                    $nextDueInvoice = $lease->invoices
                                        ->where('type', 'RENT')
                                        ->where('status', 'UNPAID')
                                        ->first();
                                    $paidInvoices = $lease->invoices->where('type', 'RENT')->where('status', 'PAID');
                                    $unpaidInvoices = $lease->invoices
                                        ->where('type', 'RENT')
                                        ->where('status', 'UNPAID');
                                @endphp

                                <div class="row g-3">
                                    <!-- Tenant Card -->
                                    <div class="col-lg-4 col-md-6">
                                        <div class="info-card tenant-card h-100">
                                            <div class="info-card-header">
                                                <i class="fe fe-user"></i>
                                                <span>Tenant</span>
                                            </div>
                                            <div class="info-card-body">
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $avatar }}" alt="avatar"
                                                        class="rounded-circle tenant-avatar" width="56" height="56"
                                                        style="object-fit: cover;">
                                                    <div class="ms-3">
                                                        <div class="fw-semibold fs-6 tenant-name">{{ $fullName }}</div>
                                                        <small class="text-muted d-block tenant-contact">
                                                            <i
                                                                class="fe fe-phone me-1"></i>{{ $profile ? $profile->phone : 'N/A' }}
                                                        </small>
                                                        <small class="text-muted d-block tenant-email">
                                                            <i
                                                                class="fe fe-mail me-1"></i>{{ $lease->tenant ? $lease->tenant->email : 'N/A' }}
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <a href="{{ route('tenants.show', $lease->tenant->id) }}"
                                                        class="btn btn-sm btn-outline-primary w-100 d-inline-flex align-items-center justify-content-center gap-1">
                                                        <i class="fe fe-user"></i>
                                                        <span>View Profile</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Property & Lease Details Card -->
                                    <div class="col-lg-4 col-md-6">
                                        <div class="info-card property-card h-100">
                                            <div class="info-card-header">
                                                <i class="fe fe-home"></i>
                                                <span>Property & Lease</span>
                                            </div>
                                            <div class="info-card-body">
                                                <h5 class="mb-2 lease-property-title">
                                                    {{ $lease->property ? $lease->property->name : 'N/A' }}
                                                    <span
                                                        class="badge bg-light text-dark ms-1">{{ $lease->assignments->where('is_current', true)->first() && $lease->assignments->where('is_current', true)->first()->bed ? $lease->assignments->where('is_current', true)->first()->bed->bed_label : 'N/A' }}</span>
                                                </h5>
                                                <div class="property-detail-item">
                                                    <i class="fe fe-calendar text-muted"></i>
                                                    <span
                                                        class="lease-dates-header">{{ date('M d, Y', strtotime($lease->start_date)) }}
                                                        - {{ date('M d, Y', strtotime($lease->end_date)) }}</span>
                                                </div>
                                                <div class="property-detail-item">
                                                    <i class="fe fe-dollar-sign text-primary"></i>
                                                    <span
                                                        class="fw-bold text-primary lease-rent-amount">${{ number_format($lease->rent_amount, 2) }}</span>
                                                    <span class="text-muted lease-payment-frequency">/
                                                        {{ str_replace('_', ' ', strtolower($lease->payment_frequency)) }}</span>
                                                </div>
                                                @if ($nextDueInvoice)
                                                    <div class="property-detail-item mt-2">
                                                        <i class="fe fe-clock text-warning"></i>
                                                        <span class="text-muted">Next due:
                                                            <strong>{{ date('M d, Y', strtotime($nextDueInvoice->due_date)) }}</strong></span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Invoice Summary Card -->
                                    <div class="col-lg-4 col-md-12">
                                        <div class="info-card invoice-summary-card h-100">
                                            <div class="info-card-header">
                                                <i class="fe fe-file-text"></i>
                                                <span>Invoice Summary</span>
                                            </div>
                                            <div class="info-card-body">
                                                <div class="invoice-stats">
                                                    <div class="invoice-stat-item">
                                                        <span class="stat-label">Total Invoiced</span>
                                                        <span
                                                            class="stat-value">${{ number_format($totalInvoiced, 2) }}</span>
                                                    </div>
                                                    <div class="invoice-stat-item text-success">
                                                        <span class="stat-label">Total Paid</span>
                                                        <span class="stat-value">${{ number_format($totalPaid, 2) }}</span>
                                                    </div>
                                                    <div class="invoice-stat-item text-danger">
                                                        <span class="stat-label">Outstanding</span>
                                                        <span
                                                            class="stat-value">${{ number_format($outstanding, 2) }}</span>
                                                    </div>
                                                </div>
                                                @if ($lease->status == 'ACTIVE')
                                                    <div class="invoice-links mt-3">
                                                        @if ($paidInvoices->count() > 0)
                                                            <div class="invoice-link-group">
                                                                <span
                                                                    class="badge bg-success-transparent text-success me-1"><i
                                                                        class="fe fe-check-circle"></i> Paid:</span>
                                                                @foreach ($paidInvoices as $invoice)
                                                                    <a href="{{ route('invoices.show', $invoice->id) }}"
                                                                        class="invoice-link paid">{{ $invoice->invoice_number }}</a>
                                                                    @if (!$loop->last)
                                                                        ,
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        @if ($unpaidInvoices->count() > 0)
                                                            <div class="invoice-link-group mt-2">
                                                                <span
                                                                    class="badge bg-danger-transparent text-danger me-1"><i
                                                                        class="fe fe-alert-circle"></i> Due:</span>
                                                                @foreach ($unpaidInvoices as $invoice)
                                                                    <a href="{{ route('invoices.show', $invoice->id) }}"
                                                                        class="invoice-link unpaid">{{ $invoice->invoice_number }}</a>
                                                                    @if (!$loop->last)
                                                                        ,
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Open Documents Section -->
                            <div class="detail-section">
                                <div class="d-flex justify-content-between align-items-center section-header"
                                    data-bs-toggle="collapse" data-bs-target="#openDocsSection">
                                    <h5 class="section-title mb-0">
                                        <i class="fe fe-file-text me-2"></i> Open Documents
                                        <span class="badge bg-secondary ms-2 document-count">
                                            {{ $lease->documents->where('tenant_signed_at', null)->count() }}
                                        </span>
                                    </h5>
                                    <button class="btn btn-sm btn-outline-primary collapse-toggle-btn">
                                        <i class="fe fe-minus"></i>
                                    </button>
                                </div>

                                <div class="collapse show" id="openDocsSection">
                                    @if ($lease->tenant->leaseDocuments->where('tenant_signed_at', null)->count() > 0)
                                        @foreach ($lease->tenant->leaseDocuments as $doc)
                                            @if (!$doc->tenant_signed_at || !$doc->admin_signed_at)
                                                <div class="document-card">
                                                    <div class="d-flex align-items-center">
                                                        <div class="document-icon">
                                                            <i class="fe fe-file-text"></i>
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="mb-1">
                                                                {{ $doc->template ? $doc->template->name : 'Lease Agreement' }}
                                                            </h6>
                                                            <div class="document-status">
                                                                @if (!$doc->tenant_signed_at)
                                                                    <span class="badge badge-sm bg-warning">Pending Tenant
                                                                        Signature</span>
                                                                @elseif(!$doc->admin_signed_at)
                                                                    <span class="badge badge-sm bg-info">Pending Admin
                                                                        Signature</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="d-flex gap-2">
                                                            <a href="{{ route('lease-documents.preview-for-lease', ['leaseId' => $lease->id, 'documentId' => $doc->id]) }}"
                                                                class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1"
                                                                title="Preview Document">
                                                                <i class="fe fe-eye"></i>
                                                                <span>Sign Document</span>
                                                            </a>
                                                            {{-- <button class="btn btn-sm btn-primary"></button> --}}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <i class="fe fe-check-circle"></i>
                                            </div>
                                            <h6>No Open Documents Found</h6>
                                            <p class="text-muted">All documents have been signed</p>
                                            @if ($lease->documents->count() > 0)
                                                <a href="{{ route('lease-documents.preview-for-lease', ['leaseId' => $lease->id, 'documentId' => $lease->documents->first()->id]) }}"
                                                    class="btn btn-sm btn-outline-primary mt-2">
                                                    <i class="fe fe-eye me-1"></i> View Signed Document
                                                </a>
                                            @else
                                                <button class="btn btn-sm btn-primary mt-2">
                                                    <i class="fe fe-plus me-1"></i> Sign a Document
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @if ($lease->status == 'ACTIVE')
                                <!-- Invoice Section -->
                                <div class="detail-section">
                                    <div class="d-flex justify-content-between align-items-center section-header"
                                        data-bs-toggle="collapse" data-bs-target="#invoiceSection">
                                        <h5 class="section-title mb-0">
                                            <i class="fe fe-file-text me-2"></i> Rent Invoices
                                            <span class="badge bg-primary ms-2">
                                                {{ $lease->invoices->where('type', 'RENT')->count() }}
                                            </span>
                                        </h5>
                                        <button class="btn btn-sm btn-outline-primary collapse-toggle-btn">
                                            <i class="fe fe-minus"></i>
                                        </button>
                                    </div>

                                    <div class="collapse show" id="invoiceSection">
                                        <!-- Invoice List -->
                                        <div class="invoice-list">
                                            @if ($lease->invoices->where('type', 'RENT')->count() > 0)
                                                @php
                                                    $rentInvoices = $lease->invoices
                                                        ->where('type', 'RENT')
                                                        ->sortBy('created_at')
                                                        ->values();
                                                    $firstInvoice = $rentInvoices->first();
                                                @endphp
                                                @foreach ($rentInvoices as $index => $invoice)
                                                    @php
                                                        $isFirstInvoice = $invoice->id === $firstInvoice->id;
                                                        $hasDeposit = $isFirstInvoice && $lease->deposit_amount > 0;
                                                        $totalAmount =
                                                            $invoice->amount +
                                                            ($hasDeposit && !$lease->deposit_collected
                                                                ? $lease->deposit_amount
                                                                : 0);
                                                    @endphp
                                                    <a href="{{ route('invoices.show', $invoice->id) }}"
                                                        class="invoice-card {{ $isFirstInvoice && $hasDeposit ? 'has-deposit' : '' }}">
                                                        <div class="invoice-left">
                                                            <div
                                                                class="invoice-icon {{ $invoice->status == 'PAID' ? 'paid' : ($invoice->isOverdue() ? 'overdue' : 'pending') }}">
                                                                @if ($invoice->status == 'PAID')
                                                                    <i class="fe fe-check"></i>
                                                                @elseif($invoice->isOverdue())
                                                                    <i class="fe fe-alert-circle"></i>
                                                                @else
                                                                    <i class="fe fe-clock"></i>
                                                                @endif
                                                            </div>
                                                            <div class="invoice-details">
                                                                <h6 class="invoice-title">
                                                                    {{ $invoice->invoice_number ?? 'INV-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}
                                                                    @if ($isFirstInvoice && $hasDeposit)
                                                                        <span class="badge bg-info-light text-info ms-2">+
                                                                            Deposit</span>
                                                                    @endif
                                                                </h6>
                                                                <span class="invoice-date">Due:
                                                                    {{ date('M d, Y', strtotime($invoice->due_date)) }}</span>
                                                                @if ($isFirstInvoice && $hasDeposit && !$lease->deposit_collected)
                                                                    <span class="deposit-note">
                                                                        <i class="fe fe-shield"></i> Includes security
                                                                        deposit
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="invoice-right">
                                                            <span class="invoice-amount">Due:
                                                                ${{ number_format($totalAmount, 2) }}</span>
                                                            @if ($isFirstInvoice && $hasDeposit)
                                                                <div class="amount-breakdown">
                                                                    <small class="text-muted">Rent:
                                                                        ${{ number_format($invoice->amount, 2) }}</small>
                                                                    @if (!$lease->deposit_collected)
                                                                        <small class="text-warning">Deposit:
                                                                            ${{ number_format($lease->deposit_amount, 2) }}</small>
                                                                    @else
                                                                        <small class="text-success">Deposit:
                                                                            Collected</small>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                            @if ($invoice->status == 'PAID')
                                                                <span class="invoice-status paid">Paid</span>
                                                            @elseif($invoice->isOverdue())
                                                                <span class="invoice-status overdue">Overdue</span>
                                                            @elseif($invoice->status == 'CANCELLED')
                                                                <span class="invoice-status cancelled">Cancelled</span>
                                                            @elseif($invoice->status == 'PARTIAL')
                                                                <span class="invoice-status partial">Partially Paid</span>
                                                            @else
                                                                <span class="invoice-status pending">Unpaid</span>
                                                            @endif
                                                        </div>
                                                        <div class="invoice-arrow">
                                                            <i class="fe fe-chevron-right"></i>
                                                        </div>
                                                    </a>
                                                @endforeach
                                            @else
                                                <div class="empty-invoice-state">
                                                    <i class="fe fe-inbox"></i>
                                                    <p>No rent invoices generated yet</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- Invoice Section -->
                                <div class="detail-section">
                                    <div class="d-flex justify-content-between align-items-center section-header"
                                        data-bs-toggle="collapse" data-bs-target="#invoiceSectionEmpty">
                                        <h5 class="section-title mb-0">
                                            <i class="fe fe-file-text me-2"></i> Rent Invoices
                                        </h5>
                                        <button class="btn btn-sm btn-outline-primary collapse-toggle-btn">
                                            <i class="fe fe-plus"></i>
                                        </button>
                                    </div>

                                    <div class="collapse" id="invoiceSectionEmpty">
                                        <div class="invoice-section">
                                            <div class="empty-invoice-state">
                                                <i class="fe fe-inbox"></i>
                                                <p>Once document is signed by both party, rent invoices will be generated
                                                    here.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <!-- Completed Documents Section -->
                            <div class="detail-section">
                                <div class="d-flex justify-content-between align-items-center section-header"
                                    data-bs-toggle="collapse" data-bs-target="#completedDocsSection">
                                    <h5 class="section-title mb-0">
                                        <i class="fe fe-check-square me-2"></i> Completed Documents
                                        <span class="badge bg-success ms-2">
                                            {{ $lease->documents->where('tenant_signed_at', '!=', null)->where('admin_signed_at', '!=', null)->count() }}
                                        </span>
                                    </h5>
                                    <button class="btn btn-sm btn-outline-primary collapse-toggle-btn">
                                        <i class="fe fe-plus"></i>
                                    </button>
                                </div>

                                <div class="collapse" id="completedDocsSection">
                                    <div class="document-section">
                                        @if ($lease->documents->where('tenant_signed_at', '!=', null)->where('admin_signed_at', '!=', null)->count() > 0)
                                            <div class="completed-docs-list">
                                                @foreach ($lease->documents->where('tenant_signed_at', '!=', null)->where('admin_signed_at', '!=', null) as $doc)
<div class="completed-doc-item">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                                <i class="fe fe-file-text text-success me-2"></i>
                                                                <div>
                                                                    <div class="fw-semibold">{{ $doc->template ? $doc->template->name : 'Lease Agreement' }}</div>
                                                                    <small class="text-muted">
                                                                        Signed by {{ $lease->property ? $lease->property->name : 'Property' }}
                                                                    </small>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="text-end">
                                                                    <div class="text-muted small">
                                                                        {{ date('M d, Y | g:i A', strtotime($doc->admin_signed_at)) }}
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex gap-2">
                                                                    <a href="{{ route('lease-documents.preview-for-lease', ['leaseId' => $lease->id, 'documentId' => $doc->id]) }}" class="btn btn-sm btn-outline-primary" title="Preview">
                                                                        <i class="fe fe-eye"></i>
                                                                    </a>
                                                                    <a href="{{ route('lease-documents.download-pdf', $doc->id) }}" class="btn btn-sm btn-outline-success" target="_blank" title="Download">
                                                                        <i class="fe fe-download"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
@endforeach
                                            </div>
@else
<div class="empty-state-small">
                                                <p class="text-muted mb-0">No completed documents</p>
                                            </div>
@endif
                                    </div>
                                </div>
                            </div>

                            <!-- Lease History Section -->
                            <div class="detail-section">
                                <div class="d-flex justify-content-between align-items-center section-header" data-bs-toggle="collapse" data-bs-target="#leaseHistorySection">
                                    <h5 class="section-title mb-0">
                                        <i class="fe fe-clock me-2"></i> Lease History
                                    </h5>
                                    <button class="btn btn-sm btn-outline-primary collapse-toggle-btn">
                                        <i class="fe fe-plus"></i>
                                    </button>
                                </div>

                                <div class="collapse" id="leaseHistorySection">
                                    <div class="timeline-section">
                                        <div class="timeline-item">
                                            <div class="timeline-icon bg-success">
                                                <i class="fe fe-check"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1">Lease Signed</h6>
                                                        <p class="text-muted mb-0 small">
                                                            Lease Signed by {{ $lease->property ? $lease->property->name : 'Property' }}
                                                        </p>
                                                    </div>
                                                    <span class="text-muted small">
                                                        {{ $lease->documents->first() && $lease->documents->first()->admin_signed_at ? date('M d, Y', strtotime($lease->documents->first()->admin_signed_at)) : 'N/A' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="timeline-item">
                                            <div class="timeline-icon bg-info">
                                                <i class="fe fe-edit"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1">Lease Created</h6>
                                                        <p class="text-muted mb-0 small">
                                                            Lease was created in the system
                                                        </p>
                                                    </div>
                                                    <span class="text-muted small">
                                                        {{ date('M d, Y', strtotime($lease->created_at)) }}
                                                    </span>
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
    </div>
    <!-- Manual Close Lease Modal -->
    <div class="modal fade" id="closeLeaseModal" tabindex="-1" aria-labelledby="closeLeaseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="closeLeaseModalContent">
                <form id="closeLeaseForm">
                    @csrf
                    <input type="hidden" name="lease_id" id="closeLeaseId">

                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title" id="closeLeaseModalLabel">
                            <i class="fe fe-alert-triangle me-2"></i> Manually Close Lease
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Loading State -->
                        <div id="closeLeaseLoading" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading lease details...</p>
                        </div>

                        <!-- Content -->
                        <div id="closeLeaseContent" style="display: none;">
                            <!-- Lease Summary -->
                            <div class="card mb-3">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0"><i class="fe fe-file-text me-2"></i>Lease Summary</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Tenant:</strong> <span id="closeLeaseTenant">-</span></p>
                                            <p class="mb-1"><strong>Property:</strong> <span id="closeLeaseProperty">-</span></p>
                                            <p class="mb-1"><strong>Unit/Bed:</strong> <span id="closeLeaseBed">-</span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Lease Start:</strong> <span id="closeLeaseStart">-</span></p>
                                            <p class="mb-1"><strong>Original End:</strong> <span id="closeLeaseEnd">-</span></p>
                                            <p class="mb-1"><strong>Monthly Rent:</strong> <span id="closeLeaseRent">-</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Unpaid Invoices Warning -->
                            <div id="unpaidInvoicesSection" style="display: none;">
                                <div class="alert alert-danger mb-3">
                                    <h6 class="alert-heading"><i class="fe fe-alert-circle me-2"></i>Cannot Close Lease - Unpaid Invoices Found</h6>
                                    <p class="mb-2">The following invoices must be paid or cancelled before closing this lease:</p>
                                    <div id="unpaidInvoicesList"></div>
                                    <hr>
                                    <p class="mb-0 small">Please resolve these invoices before attempting to close the lease.</p>
                                </div>
                            </div>

                            <!-- Close Form (shown when no unpaid invoices) -->
                            <div id="closeLeaseFormSection" style="display: none;">
                                <div class="alert alert-info mb-3">
                                    <i class="fe fe-info me-2"></i>
                                    Closing this lease will:
                                    <ul class="mb-0 mt-2">
                                        <li>Mark the lease as <strong>COMPLETED</strong></li>
                                        <li>Set the lease assignment as inactive</li>
                                        <li>Mark the bed as <strong>unoccupied</strong> (available for new tenants)</li>
                                        <li>Send notification emails to tenant and admin</li>
                                    </ul>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="newEndDate" class="form-label">
                                                <strong>New Lease End Date</strong> <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="newEndDate" name="end_date" required>
                                            <small class="text-muted">This will update the lease end date and move-out date</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="closeReason" class="form-label"><strong>Reason for Early Closure</strong></label>
                                            <select class="form-select" id="closeReason" name="close_reason">
                                                <option value="">Select a reason (optional)</option>
                                                <option value="tenant_request">Tenant Request</option>
                                                <option value="mutual_agreement">Mutual Agreement</option>
                                                <option value="property_sale">Property Sale</option>
                                                <option value="renovation">Property Renovation</option>
                                                <option value="relocation">Tenant Relocation</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="closeNotes" class="form-label"><strong>Additional Notes</strong></label>
                                    <textarea class="form-control" id="closeNotes" name="notes" rows="3" placeholder="Enter any additional notes about the lease closure..."></textarea>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="sendNotifications" name="send_notifications" checked>
                                    <label class="form-check-label" for="sendNotifications">
                                        Send notification emails to tenant and admin
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning" id="closeLeaseSubmitBtn" style="display: none;">
                            <span class="spinner-border spinner-border-sm d-none me-2" id="closeLeaseSpinner"></span>
                            <i class="fe fe-check me-1"></i> Close Lease
                        </button>
                    </div>
                </form>
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
@endsection

@push('scripts')
    <script src="{{ asset('backend/plugins/bootstrap-datepicker/js/datepicker.js') }}"></script>
                <script>
                    $('.datepicker2').each(function() {
                        const value = $(this).val();

                        $(this).datepicker({
                            format: 'yyyy-mm-dd',
                            autoclose: true,

                        });

                        if (value) {
                            $(this).datepicker('setDate', value);
                        }
                    });
                    // Search functionality
                    $('#leaseSearch').on('keyup', function() {
                        const value = $(this).val().toLowerCase();
                        $('.lease-item').filter(function() {
                            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
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
                                    $('#changeBedRent').text('$' + parseFloat(lease.rent_amount).toLocaleString(
                                        'en-US', {
                                            minimumFractionDigits: 2
                                        }));

                                    // Set default effective date to today
                                    const today = new Date().toISOString().split('T')[0];
                                    $('#effectiveDate').val(today);

                                    // Populate available beds dropdown
                                    const availableBeds = response.available_beds.filter(bed => !bed.is_current);
                                    if (availableBeds.length > 0) {
                                        availableBeds.forEach(function(bed) {
                                            const rentInfo = bed.base_rent ?
                                                ` - $${parseFloat(bed.base_rent).toLocaleString('en-US', {minimumFractionDigits: 2})}/month` :
                                                '';
                                            $('#newBedId').append(
                                                `<option value="${bed.id}">${bed.bed_label}</option>`);
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

                    // Manual Close Lease Modal Handler
                    $(document).on('click', '#manuallyCloseLease', function(e) {
                        e.preventDefault();
                        let leaseId = $(this).data('lease-id');

                        // Reset modal state
                        $('#closeLeaseId').val(leaseId);
                        $('#closeLeaseLoading').show();
                        $('#closeLeaseContent').hide();
                        $('#closeLeaseSubmitBtn').hide();
                        $('#unpaidInvoicesSection').hide();
                        $('#closeLeaseFormSection').hide();
                        $('#closeLeaseForm')[0].reset();

                        $('#closeLeaseModal').modal('show');

                        // Fetch lease close data
                        $.ajax({
                            url: `{{ url('admin/leases') }}/${leaseId}/close-data`,
                            type: 'GET',
                            success: function(response) {
                                $('#closeLeaseLoading').hide();
                                $('#closeLeaseContent').show();

                                if (response.success) {
                                    const lease = response.lease;

                                    // Populate lease summary
                                    $('#closeLeaseTenant').text(lease.tenant_name);
                                    $('#closeLeaseProperty').text(lease.property_name);
                                    $('#closeLeaseBed').text(lease.bed_label);
                                    $('#closeLeaseStart').text(lease.start_date);
                                    $('#closeLeaseEnd').text(lease.end_date);
                                    $('#closeLeaseRent').text('$' + parseFloat(lease.rent_amount).toLocaleString(
                                        'en-US', {
                                            minimumFractionDigits: 2
                                        }));

                                    // Set default end date to today
                                    const today = new Date().toISOString().split('T')[0];
                                    $('#newEndDate').val(today);
                                    $('#newEndDate').attr('max', lease.original_end_date);

                                    // Check for unpaid invoices
                                    if (response.unpaid_invoices && response.unpaid_invoices.length > 0) {
                                        $('#unpaidInvoicesSection').show();
                                        $('#closeLeaseFormSection').hide();
                                        $('#closeLeaseSubmitBtn').hide();

                                        // Build unpaid invoices list
                                        let invoicesHtml =
                                            '<div class="table-responsive"><table class="table table-sm table-bordered mb-0">';
                                        invoicesHtml +=
                                            '<thead><tr><th>Invoice #</th><th>Due Date</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead><tbody>';

                                        response.unpaid_invoices.forEach(function(invoice) {
                                            invoicesHtml += `<tr>
                                    <td><strong>${invoice.invoice_number}</strong></td>
                                    <td>${invoice.due_date}</td>
                                    <td>$${parseFloat(invoice.balance_due).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                    <td><span class="badge bg-${invoice.status === 'OVERDUE' ? 'danger' : 'warning'}">${invoice.status}</span></td>
                                    <td><a href="{{ url('admin/invoices') }}/${invoice.id}" class="btn btn-xs btn-outline-primary" target="_blank">View</a></td>
                                </tr>`;
                                        });

                                        invoicesHtml += '</tbody></table></div>';
                                        $('#unpaidInvoicesList').html(invoicesHtml);
                                    } else {
                                        $('#unpaidInvoicesSection').hide();
                                        $('#closeLeaseFormSection').show();
                                        $('#closeLeaseSubmitBtn').show();
                                    }
                                } else {
                                    toastr.error(response.message || 'Failed to load lease data');
                                    $('#closeLeaseModal').modal('hide');
                                }
                            },
                            error: function(xhr) {
                                $('#closeLeaseLoading').hide();
                                toastr.error('Failed to load lease data. Please try again.');
                                $('#closeLeaseModal').modal('hide');
                            }
                        });
                    });

                    // Handle close lease form submission
                    $('#closeLeaseForm').on('submit', function(e) {
                        e.preventDefault();

                        const leaseId = $('#closeLeaseId').val();
                        const endDate = $('#newEndDate').val();

                        if (!endDate) {
                            toastr.error('Please select an end date');
                            return;
                        }

                        // Show loading state
                        $('#closeLeaseSpinner').removeClass('d-none');
                        $('#closeLeaseSubmitBtn').prop('disabled', true);

                        $.ajax({
                            url: `{{ url('admin/leases') }}/${leaseId}/close`,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                end_date: endDate,
                                close_reason: $('#closeReason').val(),
                                notes: $('#closeNotes').val(),
                                send_notifications: $('#sendNotifications').is(':checked') ? 1 : 0
                            },
                            success: function(response) {
                                $('#closeLeaseSpinner').addClass('d-none');
                                $('#closeLeaseSubmitBtn').prop('disabled', false);

                                if (response.success) {
                                    toastr.success(response.message || 'Lease closed successfully');
                                    $('#closeLeaseModal').modal('hide');

                                    // Reload the page to reflect changes
                                    setTimeout(function() {
                                        window.location.reload();
                                    }, 1000);
                                } else {
                                    toastr.error(response.message || 'Failed to close lease');
                                }
                            },
                            error: function(xhr) {
                                $('#closeLeaseSpinner').addClass('d-none');
                                $('#closeLeaseSubmitBtn').prop('disabled', false);

                                const response = xhr.responseJSON;
                                toastr.error(response?.message || 'An error occurred while closing the lease');
                            }
                        });
                    });

                    // Load lease details via AJAX
                    function loadLeaseDetails(leaseId) {
                        NProgress.start();

                        // Update active state in sidebar
                        $('.lease-item').removeClass('active');
                        $(`.lease-item[data-lease-id="${leaseId}"]`).addClass('active');

                        // Update URL without page reload
                        const newUrl = `{{ route('leases.show', ':id') }}`.replace(':id', leaseId);
                        window.history.pushState({
                            leaseId: leaseId
                        }, '', newUrl);

                        // Fetch lease details
                        $.ajax({
                            url: `{{ route('leases.details', ':id') }}`.replace(':id', leaseId),
                            type: 'GET',
                            success: function(response) {
                                NProgress.done();
                                if (response.success) {
                                    updateLeaseDetails(response.lease);
                                }
                            },
                            error: function() {
                                NProgress.done();
                                toastr.error('Failed to load lease details');
                            }
                        });
                    }

                    // Update lease details in the DOM
                    function updateLeaseDetails(lease) {
                        // Update header
                        $('.lease-property-title').text(`${lease.property_name} | ${lease.unit}`);
                        $('.lease-dates-header span').text(`${lease.start_date} - ${lease.end_date}`);
                        $('.lease-rent-amount').text(`$${lease.rent_amount}`);
                        $('.lease-payment-frequency').text(`${lease.payment_frequency} Rent`);

                        // Update tenant info
                        $('.tenant-avatar').attr('src', lease.avatar);
                        $('.tenant-name').text(lease.tenant_name);
                        $('.tenant-contact').html(`<i class="fe fe-phone me-1"></i> ${lease.tenant_phone}`);
                        $('.tenant-email').html(`<i class="fe fe-mail me-1"></i> ${lease.tenant_email}`);

                        // Update status badge
                        const statusColors = {
                            'DRAFT': 'secondary',
                            'PENDING_TENANT_SIGN': 'warning',
                            'PENDING_ADMIN_SIGN': 'info',
                            'ACTIVE': 'success',
                            'TERMINATED': 'danger',
                            'COMPLETED': 'dark'
                        };
                        const statusColor = statusColors[lease.status] || 'secondary';
                        const statusLabel = lease.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        $('.lease-status-badge').attr('class', `badge bg-${statusColor} me-3 fs-6 lease-status-badge`).text(
                            statusLabel);
                    }

                    // Handle browser back/forward buttons
                    window.addEventListener('popstate', function(event) {
                        if (event.state && event.state.leaseId) {
                            loadLeaseDetails(event.state.leaseId);
                        } else {
                            window.location.href = '{{ route('leases.index') }}';
                        }
                    });

                    // Initialize state for current page
                    window.history.replaceState({
                        leaseId: {{ $lease->id }}
                    }, '', window.location.href);

                    // Toggle sidebar on mobile
                    function toggleSidebar() {
                        $('.lease-sidebar').toggleClass('show');
                    }

                    // Close sidebar when clicking outside on mobile
                    $(document).on('click', function(e) {
                        if ($(window).width() < 992) {
                            if (!$(e.target).closest('.lease-sidebar, .mobile-sidebar-toggle').length) {
                                $('.lease-sidebar').removeClass('show');
                            }
                        }
                    });

                    // Collapse/expand sections - handle Bootstrap collapse events
                    $('.collapse').on('show.bs.collapse', function() {
                        const sectionHeader = $(this).prev('.section-header');
                        sectionHeader.find('.collapse-toggle-btn i').removeClass('fe-plus').addClass('fe-minus');
                    });

                    $('.collapse').on('hide.bs.collapse', function() {
                        const sectionHeader = $(this).prev('.section-header');
                        sectionHeader.find('.collapse-toggle-btn i').removeClass('fe-minus').addClass('fe-plus');
                    });

                    $('#resendSignatureMail').click(function(e) {
                        e.preventDefault();
                        const url = $(this).attr('href');
                        NProgress.start();
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                NProgress.done();
                                if (response.success) {
                                    toastr.success('Signature request email resent successfully');
                                } else {
                                    toastr.error('Failed to resend signature request email');
                                }
                            },
                            error: function() {
                                NProgress.done();
                                toastr.error('An error occurred while resending the email');
                            }
                        });
                    });
                </script>
@endpush

@push('styles')
    <style>

                /* Color utilities */
                .bg-warning-light {
                    background: rgba(255, 193, 7, 0.15) !important;
                }

                .bg-success-light {
                    background: rgba(76, 175, 80, 0.15) !important;
                }

                .bg-info-light {
                    background: rgba(33, 150, 243, 0.15) !important;
                }

                /* Info Cards */
                .info-card {
                    background: #fff;
                    border: 1px solid #e9ecef;
                    border-radius: 12px;
                    overflow: hidden;
                    transition: all 0.2s ease;
                }

                .info-card:hover {
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                }

                .info-card-header {
                    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                    padding: 12px 16px;
                    font-weight: 600;
                    font-size: 13px;
                    color: #6c757d;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    border-bottom: 1px solid #e9ecef;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }

                .info-card-header i {
                    font-size: 14px;
                }

                .info-card-body {
                    padding: 16px;
                }

                .tenant-card .info-card-header {
                    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
                    color: #1976d2;
                }

                .property-card .info-card-header {
                    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
                    color: #388e3c;
                }

                .invoice-summary-card .info-card-header {
                    background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
                    color: #f57c00;
                }

                .property-detail-item {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    margin-bottom: 8px;
                    font-size: 14px;
                }

                .property-detail-item i {
                    width: 16px;
                    text-align: center;
                }

                .invoice-stats {
                    display: flex;
                    flex-direction: column;
                    gap: 8px;
                }

                .invoice-stat-item {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 8px 12px;
                    background: #f8f9fa;
                    border-radius: 6px;
                    font-size: 13px;
                }

                .invoice-stat-item .stat-label {
                    color: #6c757d;
                }

                .invoice-stat-item .stat-value {
                    font-weight: 600;
                }

                .invoice-stat-item.text-success {
                    background: rgba(76, 175, 80, 0.1);
                }

                .invoice-stat-item.text-success .stat-value {
                    color: #4caf50;
                }

                .invoice-stat-item.text-danger {
                    background: rgba(244, 67, 54, 0.1);
                }

                .invoice-stat-item.text-danger .stat-value {
                    color: #f44336;
                }

                .invoice-links {
                    border-top: 1px solid #e9ecef;
                    padding-top: 12px;
                }

                .invoice-link-group {
                    font-size: 12px;
                    line-height: 1.8;
                }

                .invoice-link {
                    font-weight: 500;
                    text-decoration: none;
                    transition: all 0.2s;
                }

                .invoice-link.paid {
                    color: #4caf50;
                }

                .invoice-link.paid:hover {
                    color: #388e3c;
                    text-decoration: underline;
                }

                .invoice-link.unpaid {
                    color: #f44336;
                }

                .invoice-link.unpaid:hover {
                    color: #d32f2f;
                    text-decoration: underline;
                }

                /* Invoice List */
                .invoice-list {
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                }

                .invoice-card {
                    background: #fff;
                    border: 1px solid #e9ecef;
                    border-radius: 12px;
                    padding: 18px 20px;
                    display: flex;
                    align-items: center;
                    text-decoration: none;
                    transition: all 0.2s ease;
                    position: relative;
                }

                .invoice-card:hover {
                    border-color: #2196F3;
                    box-shadow: 0 4px 15px rgba(33, 150, 243, 0.1);
                    text-decoration: none;
                }

                .invoice-card.has-deposit {
                    border-left: 4px solid #ffc107;
                    background: linear-gradient(to right, #fffbeb 0%, #fff 15%);
                }

                .invoice-left {
                    display: flex;
                    align-items: flex-start;
                    gap: 15px;
                    flex: 1;
                }

                .invoice-icon {
                    width: 44px;
                    height: 44px;
                    border-radius: 10px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 18px;
                    flex-shrink: 0;
                }

                .invoice-icon.paid {
                    background: rgba(76, 175, 80, 0.1);
                    color: #4caf50;
                }

                .invoice-icon.pending {
                    background: rgba(255, 193, 7, 0.1);
                    color: #f9a825;
                }

                .invoice-icon.overdue {
                    background: rgba(244, 67, 54, 0.1);
                    color: #f44336;
                }

                .invoice-details {
                    flex: 1;
                }

                .invoice-title {
                    font-weight: 600;
                    font-size: 15px;
                    color: #2c3e50;
                    margin-bottom: 4px;
                    display: flex;
                    align-items: center;
                    flex-wrap: wrap;
                    gap: 8px;
                }

                .invoice-date {
                    font-size: 13px;
                    color: #6c757d;
                    display: block;
                }

                .deposit-note {
                    display: flex;
                    align-items: center;
                    gap: 5px;
                    font-size: 12px;
                    color: #f57c00;
                    margin-top: 6px;
                }

                .deposit-note i {
                    font-size: 14px;
                }

                .invoice-right {
                    text-align: right;
                    margin-right: 15px;
                }

                .invoice-amount {
                    display: block;
                    font-size: 18px;
                    font-weight: 700;
                    color: #2c3e50;
                    margin-bottom: 4px;
                }

                .amount-breakdown {
                    display: flex;
                    flex-direction: column;
                    gap: 2px;
                    margin-bottom: 6px;
                }

                .amount-breakdown small {
                    font-size: 11px;
                }

                .invoice-status {
                    font-size: 12px;
                    font-weight: 600;
                    padding: 4px 12px;
                    border-radius: 20px;
                    display: inline-block;
                }

                .invoice-status.paid {
                    background: rgba(76, 175, 80, 0.1);
                    color: #4caf50;
                }

                .invoice-status.pending {
                    background: rgba(255, 193, 7, 0.1);
                    color: #f9a825;
                }

                .invoice-status.overdue {
                    background: rgba(244, 67, 54, 0.1);
                    color: #f44336;
                }

                .invoice-arrow {
                    color: #adb5bd;
                    font-size: 18px;
                }

                .invoice-card:hover .invoice-arrow {
                    color: #2196F3;
                }

                .empty-invoice-state {
                    text-align: center;
                    padding: 40px 20px;
                    color: #adb5bd;
                }

                .empty-invoice-state i {
                    font-size: 48px;
                    margin-bottom: 15px;
                    display: block;
                }

                .empty-invoice-state p {
                    margin: 0;
                    font-size: 14px;
                }

                .invoice-item {
                    cursor: pointer;
                    padding: 15px;
                    border-bottom: 1px solid #f1f3f5;
                }
                .invoice-item:hover {
                    background-color: #f8f9fa;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                }
                .invoice-item:last-child {
                    border-bottom: none;
                }
                .lease-detail-container {
                    display: flex;
                    height: calc(100vh - 70px);
                    background: #fff;
                    margin: 15px 0px;
                }

                /* Left Sidebar */
                .lease-sidebar {
                    width: 350px;
                    border-right: 1px solid #e9ecef;
                    display: flex;
                    flex-direction: column;
                    background: #fff;
                }

                .sidebar-header {
                    padding: 20px;
                    border-bottom: 1px solid #e9ecef;
                }

                .sidebar-header h5 {
                    font-weight: 600;
                    color: #2c3e50;
                }

                .search-box input {
                    border-radius: 6px;
                    border: 1px solid #e9ecef;
                    padding: 8px 12px;
                    font-size: 14px;
                }

                .lease-list {
                    flex: 1;
                    overflow-y: auto;
                }

                .lease-item {
                    display: flex;
                    align-items: stretch;
                    padding: 0;
                    cursor: pointer;
                    border-bottom: 1px solid #f8f9fa;
                    transition: all 0.2s;
                    position: relative;
                }

                .lease-item:hover {
                    background: #f8f9fa;
                }

                .lease-item.active {
                    background: #e3f2fd;
                }

                .lease-status-indicator {
                    width: 4px;
                    min-height: 100%;
                }

                .lease-info {
                    flex: 1;
                    padding: 12px 16px;
                }

                .lease-property {
                    font-size: 14px;
                    color: #2c3e50;
                    margin-bottom: 4px;
                }

                .lease-tenant {
                    font-size: 13px;
                    color: #6c757d;
                    margin-bottom: 4px;
                }

                .lease-dates {
                    font-size: 12px;
                    color: #adb5bd;
                }

                .lease-badge {
                    padding: 12px 16px;
                    display: flex;
                    align-items: center;
                }

                /* Right Content */
                .lease-content {
                    flex: 1;
                    display: flex;
                    flex-direction: column;
                    overflow: hidden;
                }

                .content-header {
                    padding: 20px 30px;
                    border-bottom: 1px solid #e9ecef;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    background: #fff;
                }

                .content-header h4 {
                    font-weight: 600;
                    color: #2c3e50;
                    margin: 0;
                }

                .mobile-sidebar-toggle {
                    display: none;
                }

                .lease-detail-content {
                    flex: 1;
                    overflow-y: auto;
                    padding: 30px;
                }

                .lease-header {
                    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                    padding: 25px;
                    border-radius: 12px;
                    margin-bottom: 30px;
                    border: 1px solid #e9ecef;
                }

                .detail-section {
                    margin-bottom: 20px;
                    background: #fff;
                    border: 1px solid #e9ecef;
                    border-radius: 12px;
                    overflow: hidden;
                }

                .section-header {
                    padding: 18px 20px;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    border-bottom: 1px solid transparent;
                    margin: 0;
                }

                .section-header:hover {
                    background: #f8f9fa;
                }

                .section-header[aria-expanded="true"] {
                    border-bottom: 1px solid #e9ecef;
                }

                .section-title {
                    font-weight: 600;
                    color: #2c3e50;
                    font-size: 15px;
                    display: flex;
                    align-items: center;
                }

                .collapse-toggle-btn {
                    width: 32px;
                    height: 32px;
                    padding: 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 8px;
                    transition: all 0.2s ease;
                }

                .collapse-toggle-btn:hover {
                    background: #e3f2fd;
                    border-color: #2196F3;
                    color: #2196F3;
                }

                .collapse-toggle-btn i {
                    font-size: 14px;
                    transition: transform 0.2s ease;
                }

                .detail-section .collapse,
                .detail-section .collapsing {
                    padding: 0 20px;
                }

                .detail-section .collapse.show {
                    padding: 15px 20px 20px;
                }

                .document-section {
                    margin-top: 0;
                }

                .document-card {
                    background: #f8f9fa;
                    border: 1px solid #e9ecef;
                    border-radius: 8px;
                    padding: 20px;
                    margin-bottom: 15px;
                }

                .document-card:last-child {
                    margin-bottom: 0;
                }

                .document-icon {
                    width: 50px;
                    height: 50px;
                    background: #e3f2fd;
                    border-radius: 8px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #2196F3;
                    font-size: 24px;
                }

                .empty-state {
                    text-align: center;
                    padding: 40px 20px;
                }

                .empty-state-icon {
                    width: 80px;
                    height: 80px;
                    background: #e8f5e9;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 20px;
                    color: #4caf50;
                    font-size: 40px;
                }

                .empty-state-small {
                    text-align: center;
                    padding: 20px;
                }

                .completed-docs-list {
                    background: #fff;
                }

                .completed-doc-item {
                    padding: 15px;
                    border-bottom: 1px solid #f1f3f5;
                }

                .completed-doc-item:last-child {
                    border-bottom: none;
                }

                .timeline-section {
                    position: relative;
                    padding-left: 40px;
                }

                .timeline-item {
                    position: relative;
                    padding-bottom: 30px;
                }

                .timeline-item:last-child {
                    padding-bottom: 0;
                }

                .timeline-item:not(:last-child)::after {
                    content: '';
                    position: absolute;
                    left: -25px;
                    top: 35px;
                    width: 2px;
                    height: calc(100% - 35px);
                    background: #e9ecef;
                }

                .timeline-icon {
                    position: absolute;
                    left: -35px;
                    top: 0;
                    width: 32px;
                    height: 32px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #fff;
                    font-size: 14px;
                }

                .timeline-content {
                    background: #f8f9fa;
                    border-radius: 8px;
                    padding: 15px;
                }

                /* Responsive */
                @media (max-width: 991px) {
                    .lease-sidebar {
                        position: fixed;
                        left: -350px;
                        top: 0;
                        height: 100vh;
                        z-index: 1050;
                        transition: left 0.3s;
                    }

                    .lease-sidebar.show {
                        left: 0;
                    }

                    .mobile-sidebar-toggle {
                        display: block;
                    }

                    .lease-content {
                        width: 100%;
                    }

                    .lease-detail-content {
                        padding: 20px;
                    }

                    .content-header {
                        padding: 15px 20px;
                    }
                }

                @media (max-width: 767px) {
                    .lease-header .row > div {
                        margin-bottom: 20px;
                    }

                    .lease-badge {
                        padding: 8px 12px;
                    }

                    .badge-sm {
                        font-size: 10px;
                        padding: 4px 8px;
                    }
                }
            </style>
@endpush)
