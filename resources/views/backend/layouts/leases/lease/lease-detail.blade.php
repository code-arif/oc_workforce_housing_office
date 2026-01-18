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
                                        <button class="btn btn-sm btn-outline-primary me-2">
                                            <i class="fe fe-edit me-1"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="fe fe-trash me-1"></i> Delete
                                        </button>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <h3 class="mb-1 lease-property-title">
                                            {{ $lease->property ? $lease->property->name : 'N/A' }} |
                                            {{ $lease->assignments->where('is_current', true)->first() && $lease->assignments->where('is_current', true)->first()->bed ? $lease->assignments->where('is_current', true)->first()->bed->bed_label : 'N/A' }}
                                        </h3>
                                        <div class="lease-dates-header">
                                            <span class="text-muted">
                                                {{ date('M d, Y', strtotime($lease->start_date)) }} -
                                                {{ date('M d, Y', strtotime($lease->end_date)) }}
                                            </span>
                                        </div>
                                        <div class="mt-2">
                                            <span class="text-primary fw-bold fs-5 lease-rent-amount">
                                                ${{ number_format($lease->invoices->where('type', 'RENT')->sum('amount'), 2) }}
                                            </span>
                                            <span class="text-muted ms-2 lease-payment-frequency">
                                                {{ str_replace('_', ' ', ucwords(strtolower($lease->payment_frequency))) }}
                                                Rent
                                            </span>
                                            <span class="text-muted ms-3">|</span>
                                            <span class="text-muted ms-3">
                                                Next Due on {{ $lease->invoices->where('type', 'RENT')->where('status', 'UNPAID')->first() ? date('M d, Y', strtotime($lease->invoices->where('type', 'RENT')->where('status', 'UNPAID')->first()->due_date)) : 'N/A' }} of
                                                every
                                                {{ str_replace('_', ' ', strtolower($lease->payment_frequency)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-center">
                                        <div class="me-2">
                                            <h6 class="mb-1">Invoice Information</h6>
                                            <p class="text-muted mb-0">
                                                Details about the Invoice associated with this lease
                                            </p>
                                            <ul>
                                                <li>
                                                    <strong>Total Rent Invoiced:</strong>
                                                    ${{ number_format($lease->invoices->where('type', 'RENT')->sum('amount'), 2) }}
                                                </li>
                                                <li>
                                                    <strong>Total Rent Paid:</strong>
                                                    ${{ number_format($lease->invoices->where('type', 'RENT')->where('status', 'PAID')->sum('amount'), 2) }}
                                                </li>
                                                <li>
                                                    <strong>Outstanding Rent:</strong>
                                                    ${{ number_format($lease->invoices->where('type', 'RENT')->whereIn('status', ['UNPAID', 'PARTIAL'])->sum('amount') - $lease->invoices->where('type', 'RENT')->where('status', 'PARTIAL')->sum('amount_paid'), 2) }}
                                                </li>
                                                
                                            </ul>
                                        </div>
                                        <ul>
                                            @if($lease->status == 'ACTIVE')
                                            @foreach ($lease->invoices as $key => $invoice)
                                            <li class="mb-1" > 
                                                <a href="{{ route('invoices.show', $invoice->id) }}"
                                                        style="{{$invoice->status == 'UNPAID' ? '' : 'color: var(--bs-green)'}}"> {{ $invoice->invoice_number }}@if(!$loop->last),@endif
                                                </a>
                                            </li>
                                            @endforeach
                                            @endif
                                        </ul>
                                    </div>
                                    <div class="col-md-4">
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
                                        @endphp
                                        <div class="d-flex align-items-center justify-content-md-end">
                                            <img src="{{ $avatar }}" alt="avatar"
                                                class="rounded-circle me-3 tenant-avatar" width="60" height="60"
                                                style="object-fit: cover;">
                                            <div>
                                                <div class="fw-semibold fs-6 tenant-name">{{ $fullName }}</div>
                                                <small class="text-muted d-block tenant-contact">
                                                    <i class="fe fe-phone me-1"></i>
                                                    {{ $profile ? $profile->phone : 'N/A' }}
                                                </small>
                                                <small class="text-muted d-block tenant-email">
                                                    <i class="fe fe-mail me-1"></i>
                                                    {{ $lease->tenant ? $lease->tenant->email : 'N/A' }}
                                                </small>
                                            </div>
                                        </div>
                                        <div class="mt-3 text-md-end">
                                            <button class="btn btn-sm btn-primary">
                                                <i class="fe fe-user me-1"></i> View Tenant Profile
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                           
                            <!-- Open Documents Section -->
                            <div class="detail-section">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="section-title mb-0">
                                        <i class="fe fe-file-text me-2"></i> Open Documents
                                        <span class="badge bg-secondary ms-2 document-count">
                                            {{ $lease->documents->where('tenant_signed_at', null)->count() }}
                                        </span>
                                    </h5>
                                    <button class="btn btn-sm btn-outline-primary" id="collapseOpenDocs">
                                        <i class="fe fe-minus"></i>
                                    </button>
                                </div>

                                <div class="document-section" id="openDocsSection">
                                    @if ($lease->documents->where('tenant_signed_at', null)->count() > 0)
                                        @foreach ($lease->documents as $doc)
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
                                                                 class="btn btn-sm btn-primary" title="Preview Document">
                                                                <i class="fe fe-eye"></i> Sign Document
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
                                            @if($lease->documents->count() > 0)
                                                <a href="{{ route('lease-documents.preview-for-lease', ['leaseId' => $lease->id, 'documentId' => $lease->documents->first()->id]) }}" class="btn btn-sm btn-outline-primary mt-2">
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
                            @if($lease->status == 'ACTIVE')
                             <!-- Invoice Section -->
                            <div class="detail-section">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="section-title mb-0">
                                        <i class="fe fe-file-text me-2"></i> Rent Invoices
                                        <span class="badge bg-primary ms-2">
                                            {{ $lease->invoices->where('type', 'RENT')->count() }}
                                        </span>
                                    </h5>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse"
                                        data-bs-target="#invoiceSection">
                                        <i class="fe fe-plus"></i>
                                    </button>
                                </div>

                                <div class="collapse show" id="invoiceSection">
                                    <!-- Invoice List -->
                                    <div class="invoice-list">
                                        @if ($lease->invoices->where('type', 'RENT')->count() > 0)
                                            @php
                                                $rentInvoices = $lease->invoices->where('type', 'RENT')->sortBy('created_at')->values();
                                                $firstInvoice = $rentInvoices->first();
                                            @endphp
                                            @foreach ($rentInvoices as $index => $invoice)
                                                @php
                                                    $isFirstInvoice = $invoice->id === $firstInvoice->id;
                                                    $hasDeposit = $isFirstInvoice && $lease->deposit_amount > 0;
                                                    $totalAmount = $invoice->amount + ($hasDeposit && !$lease->deposit_collected ? $lease->deposit_amount : 0);
                                                @endphp
                                                <a href="{{ route('invoices.show', $invoice->id) }}" class="invoice-card {{ $isFirstInvoice && $hasDeposit ? 'has-deposit' : '' }}">
                                                    <div class="invoice-left">
                                                        <div class="invoice-icon {{ $invoice->status == 'PAID' ? 'paid' : ($invoice->isOverdue() ? 'overdue' : 'pending') }}">
                                                            @if($invoice->status == 'PAID')
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
                                                                @if($isFirstInvoice && $hasDeposit)
                                                                    <span class="badge bg-info-light text-info ms-2">+ Deposit</span>
                                                                @endif
                                                            </h6>
                                                            <span class="invoice-date">Due: {{ date('M d, Y', strtotime($invoice->due_date)) }}</span>
                                                            @if($isFirstInvoice && $hasDeposit && !$lease->deposit_collected)
                                                                <span class="deposit-note">
                                                                    <i class="fe fe-shield"></i> Includes security deposit
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="invoice-right">
                                                        <span class="invoice-amount">Due: ${{ number_format($totalAmount, 2) }}</span>
                                                        @if($isFirstInvoice && $hasDeposit)
                                                            <div class="amount-breakdown">
                                                                <small class="text-muted">Rent: ${{ number_format($invoice->amount, 2) }}</small>
                                                                @if(!$lease->deposit_collected)
                                                                    <small class="text-warning">Deposit: ${{ number_format($lease->deposit_amount, 2) }}</small>
                                                                @else
                                                                    <small class="text-success">Deposit: Collected</small>
                                                                @endif
                                                            </div>
                                                        @endif
                                                        @if($invoice->status == 'PAID')
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
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="section-title mb-0">
                                        <i class="fe fe-file-text me-2"></i> Rent Invoices
                                    </h5>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse"
                                        data-bs-target="#invoiceSection">
                                        <i class="fe fe-plus"></i>
                                    </button>
                                </div>

                                <div class="collapse" id="invoiceSection">
                                    <div class="invoice-section">
                                        <div class="empty-invoice-state">
                                            <i class="fe fe-inbox"></i>
                                            <p>Once document is signed by both party, rent invoices will be generated here.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            <!-- Completed Documents Section -->
                            <div class="detail-section">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="section-title mb-0">
                                        <i class="fe fe-check-square me-2"></i> Completed Documents
                                        <span class="badge bg-success ms-2">
                                            {{ $lease->documents->where('tenant_signed_at', '!=', null)->where('admin_signed_at', '!=', null)->count() }}
                                        </span>
                                    </h5>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse"
                                        data-bs-target="#completedDocsSection">
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
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="section-title mb-0">
                                        <i class="fe fe-clock me-2"></i> Lease History
                                    </h5>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#leaseHistorySection">
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
@endsection

@push('scripts')
    <script>
        // Search functionality
        $('#leaseSearch').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('.lease-item').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
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

        // Collapse/expand sections
        $('#collapseOpenDocs').click(function() {
            const icon = $(this).find('i');
            $('#openDocsSection').slideToggle(function() {
                icon.toggleClass('fe-minus fe-plus');
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
        margin-bottom: 30px;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 25px;
    }

    .section-title {
        font-weight: 600;
        color: #2c3e50;
        font-size: 16px;
    }

    .document-section {
        margin-top: 20px;
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
@endpush
