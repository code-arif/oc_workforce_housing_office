@extends('backend.app')

@section('title', 'Rent Collection Report')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Rent Collection Report</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="#">Reports</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Rent Collection</li>
                        </ol>
                    </div>
                    <div class="ms-auto pageheader-btn d-flex gap-2">
                        <div class="btn-group" id="bulkActionsGroup" style="display: none;">
                            <button type="button" class="btn btn-warning dropdown-toggle d-inline-flex align-items-center"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fe fe-check-square me-2"></i> Bulk Actions (<span id="selectedCount">0</span>)
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item bulk-action" href="#" data-status="reviewed"><i
                                            class="fe fe-eye me-2 text-info"></i> Mark as Reviewed</a></li>
                                <li><a class="dropdown-item bulk-action" href="#" data-status="confirmed"><i
                                            class="fe fe-check-circle me-2 text-success"></i> Confirm All</a></li>
                                <li><a class="dropdown-item bulk-action" href="#" data-status="disputed"><i
                                            class="fe fe-alert-triangle me-2 text-danger"></i> Mark as Disputed</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item bulk-action" href="#" data-status="pending"><i
                                            class="fe fe-rotate-ccw me-2 text-warning"></i> Reset to Pending</a></li>
                            </ul>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fe fe-download me-2"></i> Export Report
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" id="exportPdfBtn"><i
                                            class="fe fe-file-text me-2 text-danger"></i> Export as PDF</a></li>
                                <li><a class="dropdown-item" href="#" id="exportExcelBtn"><i
                                            class="fe fe-file me-2 text-success"></i> Export as Excel</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                {{-- <div class="row mb-4" id="summaryCards">
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1">Total Collected</p>
                                        <h3 class="mb-0 text-primary" id="summaryTotalCollected">$0.00</h3>
                                        <small class="text-muted"><span id="summaryTotalPayments">0</span> payments</small>
                                    </div>
                                    <div class="ms-3">
                                        <div class="icon-service bg-primary-transparent text-primary">
                                            <i class="fe fe-dollar-sign"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1">Pending Review</p>
                                        <h3 class="mb-0 text-warning" id="summaryPendingAmount">$0.00</h3>
                                        <small class="text-muted"><span id="summaryPendingCount">0</span> payments</small>
                                    </div>
                                    <div class="ms-3">
                                        <div class="icon-service bg-warning-transparent text-warning">
                                            <i class="fe fe-clock"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1">Confirmed</p>
                                        <h3 class="mb-0 text-success" id="summaryConfirmedAmount">$0.00</h3>
                                        <small class="text-muted"><span id="summaryConfirmedCount">0</span> payments</small>
                                    </div>
                                    <div class="ms-3">
                                        <div class="icon-service bg-success-transparent text-success">
                                            <i class="fe fe-check-circle"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1">Disputed</p>
                                        <h3 class="mb-0 text-danger" id="summaryDisputedAmount">$0.00</h3>
                                        <small class="text-muted"><span id="summaryDisputedCount">0</span> payments</small>
                                    </div>
                                    <div class="ms-3">
                                        <div class="icon-service bg-danger-transparent text-danger">
                                            <i class="fe fe-alert-triangle"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}

                <!-- Payment Methods Breakdown -->
                {{-- <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0"><i class="fe fe-credit-card me-2"></i>Collection by Payment
                                    Method</h4>
                            </div>
                            <div class="card-body">
                                <div class="row" id="paymentMethodsBreakdown">
                                    <!-- Dynamically populated -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}

                <!-- Combined Summary + Payment Methods Row -->
                <div class="row mb-3" id="summaryCards">
                    <!-- Left: Summary Stats -->
                    <div class="col-lg-7 col-xl-8 mb-3 mb-lg-0">
                        <div class="card h-100 mb-0">
                            <div class="card-body py-3 px-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center gap-2 p-2 border rounded-3">
                                            <div class="icon-service-sm bg-primary-transparent text-primary flex-shrink-0">
                                                <i class="fe fe-dollar-sign"></i>
                                            </div>
                                            <div class="overflow-hidden">
                                                <p class="text-muted mb-0 small text-truncate">Total Collected</p>
                                                <h6 class="mb-0 text-primary fw-bold" id="summaryTotalCollected">$0.00</h6>
                                                <small class="text-muted"><span id="summaryTotalPayments">0</span>
                                                    payments</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center gap-2 p-2 border rounded-3">
                                            <div class="icon-service-sm bg-warning-transparent text-warning flex-shrink-0">
                                                <i class="fe fe-clock"></i>
                                            </div>
                                            <div class="overflow-hidden">
                                                <p class="text-muted mb-0 small text-truncate">Pending Review</p>
                                                <h6 class="mb-0 text-warning fw-bold" id="summaryPendingAmount">$0.00</h6>
                                                <small class="text-muted"><span id="summaryPendingCount">0</span>
                                                    payments</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center gap-2 p-2 border rounded-3">
                                            <div class="icon-service-sm bg-success-transparent text-success flex-shrink-0">
                                                <i class="fe fe-check-circle"></i>
                                            </div>
                                            <div class="overflow-hidden">
                                                <p class="text-muted mb-0 small text-truncate">Confirmed</p>
                                                <h6 class="mb-0 text-success fw-bold" id="summaryConfirmedAmount">$0.00</h6>
                                                <small class="text-muted"><span id="summaryConfirmedCount">0</span>
                                                    payments</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center gap-2 p-2 border rounded-3">
                                            <div class="icon-service-sm bg-danger-transparent text-danger flex-shrink-0">
                                                <i class="fe fe-alert-triangle"></i>
                                            </div>
                                            <div class="overflow-hidden">
                                                <p class="text-muted mb-0 small text-truncate">Disputed</p>
                                                <h6 class="mb-0 text-danger fw-bold" id="summaryDisputedAmount">$0.00</h6>
                                                <small class="text-muted"><span id="summaryDisputedCount">0</span>
                                                    payments</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Payment Methods -->
                    <div class="col-lg-5 col-xl-4">
                        <div class="card h-100 mb-0">
                            <div class="card-header py-2 px-3">
                                <h6 class="card-title mb-0 small d-flex align-items-center">
                                    <i class="fe fe-credit-card me-1"></i>
                                    <span>Collection by Payment Method</span>
                                </h6>
                            </div>
                            <div class="card-body py-2 px-3">
                                <div class="row g-2" id="paymentMethodsBreakdown">
                                    <!-- Dynamically populated -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Card -->
                {{-- <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-title mb-0"><i class="fe fe-filter me-2"></i>Filter Report</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-sm-6 col-md-4 col-xl">
                                <label for="filterReviewStatus" class="form-label">Review Status</label>
                                <select class="form-select select3" id="filterReviewStatus">
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending Review</option>
                                    <option value="reviewed">Reviewed</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="disputed">Disputed</option>
                                </select>
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl">
                                <label for="filterProperty" class="form-label">Property</label>
                                <select class="form-select select3" id="filterProperty">
                                    <option value="">All Properties</option>
                                    @foreach ($properties as $property)
                                    <option value="{{ $property->id }}">{{ $property->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl">
                                <label class="form-label">Tenant</label>
                                <select class="form-select select3" id="filterTenant">
                                    <option value="">All Tenants</option>
                                    @foreach ($tenants as $tenant)
                                    <option value="{{ $tenant->id }}">{{ $tenant->profile->first_name ?? '' }}
                                        {{ $tenant->profile->last_name ?? '' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl">
                                <label for="filterPaymentMethod" class="form-label">Payment Method</label>
                                <select class="form-select select3" id="filterPaymentMethod">
                                    <option value="">All Methods</option>
                                    <option value="cash">Cash</option>
                                    <option value="check">Check</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="stripe">Stripe</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl">
                                <label for="filterDateFrom" class="form-label">From Date</label>
                                <input type="text" class="form-control datepicker2" id="filterDateFrom"
                                    placeholder="Start date...">
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl">
                                <label for="filterDateTo" class="form-label">To Date</label>
                                <input type="text" class="form-control datepicker2" id="filterDateTo"
                                    placeholder="End date...">
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl">
                                <button type="button"
                                    class="btn btn-outline-secondary w-100 d-inline-flex align-items-center justify-content-center"
                                    id="resetFilters">
                                    <i class="fe fe-refresh-cw me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div> --}}

                <!-- Filter Card -->
                <div class="card mb-3">
                    <div class="card-body py-2 px-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-sm-6 col-md-4 col-xl">
                                <label for="filterReviewStatus" class="form-label mb-1 small">Review Status</label>
                                <select class="form-select form-select-sm select3" id="filterReviewStatus">
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending Review</option>
                                    <option value="reviewed">Reviewed</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="disputed">Disputed</option>
                                </select>
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl">
                                <label for="filterProperty" class="form-label mb-1 small">Property</label>
                                <select class="form-select form-select-sm select3" id="filterProperty">
                                    <option value="">All Properties</option>
                                    @foreach ($properties as $property)
                                        <option value="{{ $property->id }}">{{ $property->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl">
                                <label class="form-label mb-1 small">Tenant</label>
                                <select class="form-select form-select-sm select3" id="filterTenant">
                                    <option value="">All Tenants</option>
                                    @foreach ($tenants as $tenant)
                                        <option value="{{ $tenant->id }}">{{ $tenant->profile->first_name ?? '' }}
                                            {{ $tenant->profile->last_name ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl">
                                <label for="filterPaymentMethod" class="form-label mb-1 small">Payment Method</label>
                                <select class="form-select form-select-sm select3" id="filterPaymentMethod">
                                    <option value="">All Methods</option>
                                    <option value="cash">Cash</option>
                                    <option value="check">Check</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="stripe">Stripe</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl">
                                <label for="filterDateFrom" class="form-label mb-1 small">From Date</label>
                                <input type="text" class="form-control form-control-sm datepicker2"
                                    id="filterDateFrom" placeholder="Start date...">
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl">
                                <label for="filterDateTo" class="form-label mb-1 small">To Date</label>
                                <input type="text" class="form-control form-control-sm datepicker2" id="filterDateTo"
                                    placeholder="End date...">
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl">
                                <button type="button"
                                    class="btn btn-sm btn-outline-secondary w-100 d-inline-flex align-items-center justify-content-center"
                                    id="resetFilters">
                                    <i class="fe fe-refresh-cw me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0"><i class="fe fe-list me-2"></i>Payment Collection Details</h4>
                        <span class="text-muted small" id="lastUpdated"></span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="collectionReportTable"
                                style="width: 100%">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40px;" class="text-center align-middle">
                                            <div class="d-flex justify-content-center align-items-center">
                                                <input type="checkbox" class="form-check-input m-0" id="selectAll">
                                            </div>
                                        </th>
                                        <th>ID</th>
                                        <th>Payment #</th>
                                        <th>Property</th>
                                        <th>Bed</th>
                                        <th>Tenant</th>
                                        <th>Payment Date</th>
                                        <th class="text-end">Amount ($)</th>
                                        <th>Stripe Amount</th>
                                        <th>Stripe Method</th>
                                        <th>Method</th>
                                        <th>Invoice</th>
                                        <th>Review Status</th>
                                        <th>Reviewed By</th>
                                        <th style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <th colspan="7" class="text-end">Total Collected:</th>
                                        <th class="text-end" id="footerTotalCollected">$0.00</th>
                                        <th colspan="7"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Review Note Modal -->
    <div class="modal fade" id="reviewNoteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fe fe-edit me-2"></i>Add Review Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="reviewPaymentId">
                    <input type="hidden" id="reviewNewStatus">
                    <div class="mb-3">
                        <label for="reviewNote" class="form-label">Note (Optional)</label>
                        <textarea class="form-control" id="reviewNote" rows="3" placeholder="Add a note about this review action..."></textarea>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="fe fe-info me-2"></i>
                        <span id="statusChangeInfo"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmReviewBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('backend/plugins/bootstrap-datepicker/js/datepicker.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select3').select2({
                placeholder: 'Select Option',
                allowClear: true
            });

            // Initialize Datepicker
            $('#filterDateFrom, #filterDateTo').datepicker({
                format: 'mm/dd/yyyy',
                autoclose: true,
                todayHighlight: true
            });

            // Track selected payments
            let selectedPayments = [];
            let totalCollected = 0;

            // Initialize DataTable
            var table = $('#collectionReportTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('reports.rent-collection.data') }}",
                    data: function(d) {
                        d.review_status = $('#filterReviewStatus').val();
                        d.property_id = $('#filterProperty').val();
                        d.tenant_id = $('#filterTenant').val();
                        d.payment_method = $('#filterPaymentMethod').val();
                        d.date_from = parseDateForQuery($('#filterDateFrom').val());
                        d.date_to = parseDateForQuery($('#filterDateTo').val());
                    }
                },
                columns: [{
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            return `<div class="d-flex justify-content-center align-items-center"><input type="checkbox" class="form-check-input row-select m-0" value="${data}"></div>`;
                        }
                    },
                    {
                        data: 'id',
                        name: 'payments.id',
                        visible: false,
                        searchable: false
                    },
                    {
                        data: 'payment_number',
                        name: 'payment_number'
                    },
                    {
                        data: 'property_name',
                        name: 'property_name'
                    },
                    {
                        data: 'bed_label',
                        name: 'bed_label'
                    },
                    {
                        data: 'tenant_name',
                        name: 'tenant_name'
                    },
                    {
                        data: 'formatted_payment_date',
                        name: 'payment_date'
                    },
                    {
                        data: 'formatted_amount',
                        name: 'amount',
                        className: 'text-end'
                    },
                    {
                        data: 'stripe_amount',
                        name: 'stripe_amount',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'stripe_method',
                        name: 'stripe_method',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'payment_method_badge',
                        name: 'payment_method'
                    },


                    {
                        data: 'invoice_info',
                        name: 'invoice_number'
                    },
                    {
                        data: 'review_status_badge',
                        name: 'review_status'
                    },
                    {
                        data: 'reviewed_info',
                        name: 'reviewed_by'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [1, 'desc'] // Sort by the hidden ID column (newest first)
                ],
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                dom: '<"row align-items-center mb-2"<"col-auto d-flex align-items-center gap-2"l<"bulk-actions-container ms-1">><"col-auto ms-auto"f>>rtip',

                initComplete: function() {
                    $('#bulkActionsGroup').appendTo('.bulk-actions-container').css('display', '');
                    // hide it again since no rows selected yet
                    if (selectedPayments.length === 0) {
                        $('#bulkActionsGroup').hide();
                    }
                },
                drawCallback: function(settings) {
                    updateLastUpdated();
                    loadSummary();
                    updateSelectAllState();
                }
            });

            function numberFormat(num) {
                return num.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function updateLastUpdated() {
                const now = new Date();
                $('#lastUpdated').text('Last updated: ' + now.toLocaleTimeString());
            }

            function loadSummary() {
                $.ajax({
                    url: "{{ route('reports.rent-collection.summary') }}",
                    data: getFilterParams(),
                    success: function(data) {
                        $('#summaryTotalCollected').text('$' + numberFormat(data.total_amount || 0));
                        $('#summaryTotalPayments').text(data.total_payments || 0);

                        $('#summaryPendingAmount').text('$' + numberFormat(data.pending_amount || 0));
                        $('#summaryPendingCount').text(data.pending_review || 0);

                        $('#summaryConfirmedAmount').text('$' + numberFormat(data.confirmed_amount ||
                            0));
                        $('#summaryConfirmedCount').text(data.confirmed || 0);

                        $('#summaryDisputedAmount').text('$' + numberFormat(data.disputed_amount || 0));
                        $('#summaryDisputedCount').text(data.disputed || 0);

                        $('#footerTotalCollected').text('$' + numberFormat(data.total_amount || 0));

                        // Update payment methods breakdown
                        updatePaymentMethodsBreakdown(data.by_method || {});
                    }
                });
            }

            function updatePaymentMethodsBreakdown(methodData) {
                const icons = {
                    'cash': 'fe-dollar-sign',
                    'check': 'fe-file-text',
                    'credit_card': 'fe-credit-card',
                    'bank_transfer': 'fe-send',
                    'stripe': 'fe-zap',
                    'other': 'fe-more-horizontal'
                };
                const colors = {
                    'cash': 'success',
                    'check': 'info',
                    'credit_card': 'primary',
                    'bank_transfer': 'warning',
                    'stripe': 'purple',
                    'other': 'secondary'
                };

                let html = '';
                for (const [method, data] of Object.entries(methodData)) {
                    const icon = icons[method] || 'fe-circle';
                    const color = colors[method] || 'secondary';
                    const label = method ? method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) :
                        'Unknown';

                    html += `
                                    <div class="col-6">
                                        <div class="d-flex align-items-center gap-2 p-2 border rounded-3">
                                            <div class="icon-service-sm bg-${color}-transparent text-${color} flex-shrink-0">
                                                <i class="fe ${icon}"></i>
                                            </div>
                                            <div class="overflow-hidden">
                                                <small class="text-muted d-block text-truncate">${label}</small>
                                                <h6 class="mb-0 small fw-bold">$${numberFormat(data.total || 0)}</h6>
                                                <small class="text-muted">${data.count || 0} pmts</small>
                                            </div>
                                        </div>
                                    </div>
                                `;
                }

                if (!html) {
                    html = '<div class="col-12 text-center text-muted py-2 small">No data available</div>';
                }

                $('#paymentMethodsBreakdown').html(html);
            }

            function parseDateForQuery(dateString) {
                if (!dateString) return '';
                const parts = dateString.trim().split('/');
                if (parts.length === 3) {
                    const [month, day, year] = parts;
                    return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
                }
                return dateString;
            }

            function getFilterParams() {
                return {
                    review_status: $('#filterReviewStatus').val(),
                    property_id: $('#filterProperty').val(),
                    tenant_id: $('#filterTenant').val(),
                    payment_method: $('#filterPaymentMethod').val(),
                    date_from: parseDateForQuery($('#filterDateFrom').val()),
                    date_to: parseDateForQuery($('#filterDateTo').val())
                };
            }

            function getFilterQueryString() {
                let params = new URLSearchParams();
                const filters = getFilterParams();
                for (const [key, value] of Object.entries(filters)) {
                    if (value) params.append(key, value);
                }
                return params.toString();
            }

            // Filter change handlers
            $('#filterReviewStatus, #filterProperty, #filterTenant, #filterPaymentMethod, #filterDateFrom, #filterDateTo')
                .on('change', function() {
                    table.ajax.reload();
                });

            // Reset Filters
            $('#resetFilters').on('click', function() {
                $('#filterReviewStatus, #filterProperty, #filterTenant, #filterPaymentMethod').val('')
                    .trigger('change');
                $('#filterDateFrom, #filterDateTo').val('');
                table.ajax.reload();
            });

            // Select all checkbox
            $('#selectAll').on('change', function() {
                const isChecked = $(this).prop('checked');
                $('.row-select').prop('checked', isChecked);
                updateSelectedPayments();
            });

            // Individual row checkbox
            $(document).on('change', '.row-select', function() {
                updateSelectedPayments();
                updateSelectAllState();
            });

            function updateSelectedPayments() {
                selectedPayments = [];
                $('.row-select:checked').each(function() {
                    selectedPayments.push($(this).val());
                });

                $('#selectedCount').text(selectedPayments.length);

                if (selectedPayments.length > 0) {
                    $('#bulkActionsGroup').show();
                } else {
                    $('#bulkActionsGroup').hide();
                }
            }

            function updateSelectAllState() {
                const totalCheckboxes = $('.row-select').length;
                const checkedCheckboxes = $('.row-select:checked').length;

                $('#selectAll').prop('checked', totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes);
                $('#selectAll').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
            }

            // Single review action
            $(document).on('click', '.btn-review', function() {
                const paymentId = $(this).data('id');
                const newStatus = $(this).data('status');

                $('#reviewPaymentId').val(paymentId);
                $('#reviewNewStatus').val(newStatus);
                $('#reviewNote').val('');

                const statusLabels = {
                    'reviewed': 'mark as Reviewed',
                    'confirmed': 'Confirm this payment',
                    'disputed': 'mark as Disputed',
                    'pending': 'reset to Pending'
                };

                $('#statusChangeInfo').text(`You are about to ${statusLabels[newStatus]}.`);
                $('#reviewNoteModal').modal('show');
            });

            // Confirm review action
            $('#confirmReviewBtn').on('click', function() {
                const paymentId = $('#reviewPaymentId').val();
                const newStatus = $('#reviewNewStatus').val();
                const note = $('#reviewNote').val();

                $.ajax({
                    url: `/admin/reports/rent-collection/${paymentId}/review`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: newStatus,
                        note: note
                    },
                    success: function(response) {
                        $('#reviewNoteModal').modal('hide');
                        toastr.success(response.message);
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Failed to update status');
                    }
                });
            });

            // Bulk action handlers
            $(document).on('click', '.bulk-action', function(e) {
                e.preventDefault();

                if (selectedPayments.length === 0) {
                    toastr.warning('Please select at least one payment');
                    return;
                }

                const newStatus = $(this).data('status');
                const statusLabels = {
                    'reviewed': 'Reviewed',
                    'confirmed': 'Confirmed',
                    'disputed': 'Disputed',
                    'pending': 'Pending'
                };

                if (confirm(
                        `Are you sure you want to mark ${selectedPayments.length} payment(s) as ${statusLabels[newStatus]}?`
                    )) {
                    $.ajax({
                        url: "{{ route('reports.rent-collection.bulk-update') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            payment_ids: selectedPayments,
                            status: newStatus
                        },
                        success: function(response) {
                            toastr.success(response.message);
                            selectedPayments = [];
                            $('#bulkActionsGroup').hide();
                            $('#selectAll').prop('checked', false);
                            table.ajax.reload();
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message ||
                                'Failed to update payments');
                        }
                    });
                }
            });

            // Export PDF
            $('#exportPdfBtn').on('click', function(e) {
                e.preventDefault();
                const params = getFilterQueryString();
                const url = "{{ route('reports.rent-collection.export.pdf') }}" + (params ? '?' + params :
                    '');
                window.location.href = url;
            });

            // Export Excel
            $('#exportExcelBtn').on('click', function(e) {
                e.preventDefault();
                const params = getFilterQueryString();
                const url = "{{ route('reports.rent-collection.export.excel') }}" + (params ? '?' +
                    params : '');
                window.location.href = url;
            });

            // Initial summary load
            loadSummary();
        });
    </script>
@endpush

@push('styles')
    <style>
        .select2-container {
            width: 100% !important;
        }

        .icon-service {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        #summaryCards .card {
            min-height: 120px;
        }

        #summaryCards .card-body {
            padding: 0.9rem 1rem;
        }

        #summaryCards p {
            margin-bottom: 0.25rem;
            font-size: 0.92rem;
        }

        #summaryCards h3 {
            font-size: 1.4rem;
        }

        #paymentMethodsBreakdown .border {
            min-height: 110px;
            background-color: #fff;
        }

        #collectionReportTable tfoot th {
            font-weight: 600;
        }

        .card-title i {
            opacity: 0.7;
        }

        .bg-purple-transparent {
            background-color: rgba(102, 16, 242, 0.1) !important;
        }

        .text-purple {
            color: #6610f2 !important;
        }

        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
        }

        .row-select {
            cursor: pointer;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        /* Replace old icon-service & summaryCards styles */
        .icon-service-sm {
            width: 34px;
            height: 34px;
            min-width: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        #summaryCards .card {
            min-height: unset;
        }

        #summaryCards .card-body {
            padding: 0.75rem;
        }

        #paymentMethodsBreakdown .border {
            min-height: unset;
            background-color: #fff;
        }

        /* DataTable controls cleanup */
        .dataTables_wrapper .dataTables_length select {
            display: inline-block;
            width: auto;
            padding: 0.25rem 1.8rem 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .dataTables_wrapper .dataTables_filter input {
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 0;
        }
    </style>
@endpush
