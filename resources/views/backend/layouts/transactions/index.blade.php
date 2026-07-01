@extends('backend.app')

@section('title', 'Transaction Monitoring')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Transaction Monitoring</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Transactions</li>
                        </ol>
                    </div>
                    <div class="ms-auto pageheader-btn d-flex gap-2">
                        <!-- View Toggle -->
                        <div class="btn-group btn-group-sm" role="group" id="viewToggle">
                            <input type="radio" class="btn-check" name="viewMode" id="viewTable" value="table" checked>
                            <label class="btn btn-outline-secondary d-inline-flex align-items-center" for="viewTable" title="Table View">
                                <i class="fe fe-list me-1"></i> Table
                            </label>
                            <input type="radio" class="btn-check" name="viewMode" id="viewCompact" value="compact">
                            <label class="btn btn-outline-secondary d-inline-flex align-items-center" for="viewCompact" title="Compact View">
                                <i class="fe fe-align-justify me-1"></i> Compact
                            </label>
                            <input type="radio" class="btn-check" name="viewMode" id="viewChart" value="chart">
                            <label class="btn btn-outline-secondary d-inline-flex align-items-center" for="viewChart" title="Chart View">
                                <i class="fe fe-bar-chart-2 me-1"></i> Chart
                            </label>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary dropdown-toggle d-inline-flex align-items-center"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fe fe-download me-2"></i> Export
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" id="exportExcelBtn" target="_blank">
                                    <i class="fe fe-file-text me-2 text-success"></i> Export as Excel</a></li>
                                <li><a class="dropdown-item" href="#" id="exportCsvBtn" target="_blank">
                                    <i class="fe fe-file me-2 text-info"></i> Export as CSV</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" id="exportPdfBtn" target="_blank">
                                    <i class="fe fe-file-text me-2 text-danger"></i> Export as PDF</a></li>
                            </ul>
                        </div>
                        <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center" id="refreshBtn">
                            <i class="fe fe-refresh-cw me-2"></i> Refresh
                        </button>
                    </div>
                </div>

                <!-- Summary Cards Row -->
                <div class="row mb-3" id="summaryCards">
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-2 mb-xl-0">
                        <div class="card h-100 mb-0">
                            <div class="card-body py-3 px-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-service-sm bg-light text-secondary flex-shrink-0">
                                        <i class="fe fe-dollar-sign"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="text-muted mb-0 small text-truncate">Total Collected</p>
                                        <h5 class="mb-0 fw-bold" id="summaryTotalCollected">$0.00</h5>
                                        <small class="text-muted"><span id="summaryTotalPayments">0</span> payments</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-2 mb-xl-0">
                        <div class="card h-100 mb-0">
                            <div class="card-body py-3 px-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-service-sm bg-light text-secondary flex-shrink-0">
                                        <i class="fe fe-clock"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="text-muted mb-0 small text-truncate">Pending Review</p>
                                        <h5 class="mb-0 fw-bold" id="summaryPendingAmount">$0.00</h5>
                                        <small class="text-muted"><span id="summaryPendingCount">0</span> pending</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-2 mb-xl-0">
                        <div class="card h-100 mb-0">
                            <div class="card-body py-3 px-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-service-sm bg-light text-secondary flex-shrink-0">
                                        <i class="fe fe-check-circle"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="text-muted mb-0 small text-truncate">Confirmed</p>
                                        <h5 class="mb-0 fw-bold" id="summaryConfirmedAmount">$0.00</h5>
                                        <small class="text-muted"><span id="summaryConfirmedCount">0</span> confirmed</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-2 mb-xl-0">
                        <div class="card h-100 mb-0">
                            <div class="card-body py-3 px-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-service-sm bg-light text-secondary flex-shrink-0">
                                        <i class="fe fe-x-circle"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="text-muted mb-0 small text-truncate">Voided / Cancelled</p>
                                        <h5 class="mb-0 fw-bold" id="summaryVoidedAmount">$0.00</h5>
                                        <small class="text-muted"><span id="summaryVoidedCount">0</span> voided</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Card -->
                <div class="card mb-3" id="filterCard">
                    <div class="card-body py-2 px-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-sm-6 col-md-4 col-xl">
                                <label for="filterPaymentStatus" class="form-label mb-1 small fw-semibold">Payment Status</label>
                                <select class="form-select form-select-sm select3" id="filterPaymentStatus">
                                    <option value="">All Payments</option>
                                    <option value="active">Active Only</option>
                                    <option value="voided">Voided Only</option>
                                </select>
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl">
                                <label for="filterReviewStatus" class="form-label mb-1 small fw-semibold">Review Status</label>
                                <select class="form-select form-select-sm select3" id="filterReviewStatus">
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending Review</option>
                                    <option value="reviewed">Reviewed</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="disputed">Disputed</option>
                                </select>
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl">
                                <label for="filterProperty" class="form-label mb-1 small fw-semibold">Property</label>
                                <select class="form-select form-select-sm select3" id="filterProperty">
                                    <option value="">All Properties</option>
                                    @foreach ($properties as $property)
                                        <option value="{{ $property->id }}">{{ $property->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl">
                                <label for="filterTenant" class="form-label mb-1 small fw-semibold">Tenant</label>
                                <select class="form-select form-select-sm select3" id="filterTenant">
                                    <option value="">All Tenants</option>
                                    @foreach ($tenants as $tenant)
                                        <option value="{{ $tenant->id }}">
                                            {{ $tenant->profile->first_name ?? '' }}
                                            {{ $tenant->profile->last_name ?? '' }}
                                            ({{ $tenant->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl">
                                <label for="filterPaymentMethod" class="form-label mb-1 small fw-semibold">Method</label>
                                <select class="form-select form-select-sm select3" id="filterPaymentMethod">
                                    <option value="">All Methods</option>
                                    <option value="cash">Cash</option>
                                    <option value="check">Check</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="debit_card">Debit Card</option>
                                    <option value="online">Online</option>
                                    <option value="stripe">Stripe</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl">
                                <label for="filterPaymentType" class="form-label mb-1 small fw-semibold">Payment Type</label>
                                <select class="form-select form-select-sm select3" id="filterPaymentType">
                                    <option value="">All Types</option>
                                    <option value="full">Full</option>
                                    <option value="partial">Partial</option>
                                    <option value="deposit">Deposit</option>
                                    <option value="rent">Rent</option>
                                </select>
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl">
                                <label for="filterDateFrom" class="form-label mb-1 small fw-semibold">From Date</label>
                                <input type="text" class="form-control form-control-sm datepicker2"
                                    id="filterDateFrom" placeholder="Start date...">
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl">
                                <label for="filterDateTo" class="form-label mb-1 small fw-semibold">To Date</label>
                                <input type="text" class="form-control form-control-sm datepicker2" id="filterDateTo"
                                    placeholder="End date...">
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-primary flex-fill d-inline-flex align-items-center justify-content-center" id="applyFilters">
                                    <i class="fe fe-filter me-1"></i> Filter
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary flex-fill d-inline-flex align-items-center justify-content-center" id="resetFilters">
                                    <i class="fe fe-refresh-cw me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart View -->
                <div class="card d-none" id="chartViewCard">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0"><i class="fe fe-bar-chart-2 me-2"></i>Payment Trends</h6>
                            <div class="d-flex align-items-center gap-2">
                                <label class="small text-muted me-1">Timeframe:</label>
                                <select class="form-select form-select-sm ms-2" id="chartTimeframe" style="width: auto;">
                                    <option value="6">6 Months</option>
                                    <option value="12" selected>12 Months</option>
                                    <option value="24">24 Months</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-8">
                                <canvas id="paymentsChart" height="280"></canvas>
                            </div>
                            <div class="col-lg-4">
                                <div class="row g-2" id="chartBreakdownCards">
                                    <div class="col-12">
                                        <div class="card border mb-0">
                                            <div class="card-header py-2 bg-light">
                                                <h6 class="mb-0 small fw-bold">By Payment Method</h6>
                                            </div>
                                            <div class="card-body py-2" id="chartMethodBreakdown">
                                                <div class="text-muted small">Loading...</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="card border mb-0">
                                            <div class="card-header py-2 bg-light">
                                                <h6 class="mb-0 small fw-bold">By Review Status</h6>
                                            </div>
                                            <div class="card-body py-2" id="chartReviewBreakdown">
                                                <div class="text-muted small">Loading...</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transactions Table -->
                <div class="card" id="tableCard">
                    <div class="card-body">
                            <table class="table table-hover text-nowrap" id="transactionsTable" style="width: 100%">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Payment #</th>
                                        <th>Date</th>
                                        <th>Tenant</th>
                                        <th>Property</th>
                                        <th class="text-end">Amount ($)</th>
                                        <th>Method</th>
                                        <th>Type</th>
                                        <th>Invoice</th>
                                        <th>Paid By</th>
                                        <th>Gateway Info</th>
                                        <th>Note</th>
                                        <th>Status</th>
                                        <th class="text-center" style="width: 180px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Payment Detail Modal -->
    <div class="modal fade" id="paymentDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fe fe-info me-2"></i>Payment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body" id="paymentDetailBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading payment details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Status Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fe fe-edit me-2"></i>Update Review Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="reviewForm">
                    @csrf
                    <input type="hidden" id="reviewPaymentId" name="payment_id">
                    <input type="hidden" id="reviewNewStatus" name="status">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">New Status</label>
                            <p class="fw-bold" id="reviewStatusLabel"></p>
                        </div>
                        <div class="mb-3">
                            <label for="reviewNote" class="form-label">Note (Optional)</label>
                            <textarea class="form-control" id="reviewNote" name="note" rows="3"
                                placeholder="Add a note about this review action..."></textarea>
                        </div>
                        <div class="alert alert-info mb-0">
                            <i class="fe fe-info me-2"></i>
                            <span id="reviewStatusInfo"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="confirmReviewBtn">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Void Payment Modal -->
    <div class="modal fade" id="voidModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="fe fe-alert-triangle me-2"></i>Void / Cancel Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="voidForm">
                    @csrf
                    <input type="hidden" id="voidPaymentId" name="payment_id">
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fe fe-info me-2"></i>
                            <strong>Warning:</strong> Voiding a payment will:
                            <ul class="mb-0 mt-1">
                                <li>Mark the payment as voided/cancelled</li>
                                <li>Create a reversal transaction for audit trail</li>
                                <li>Update the invoice balance accordingly</li>
                            </ul>
                            <p class="mb-0 mt-1 small">This action <strong>cannot be undone</strong>.</p>
                        </div>
                        <div class="mb-3">
                            <label for="voidReason" class="form-label">Reason for Voiding <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="voidReason" name="reason" rows="3"
                                placeholder="Explain why this payment is being voided..." required></textarea>
                            <small class="text-muted">Minimum 3 characters required.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger" id="confirmVoidBtn">
                            <i class="fe fe-x-circle me-1"></i> Void Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('backend/plugins/bootstrap-datepicker/js/datepicker.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        var paymentsChartInstance = null;

        $(document).ready(function() {
            // Initialize Select2
            $('.select3').select2({
                placeholder: 'Select Option',
                allowClear: true,
                width: '100%'
            });

            // Initialize Datepickers
            $('#filterDateFrom, #filterDateTo').datepicker({
                format: 'mm/dd/yyyy',
                autoclose: true,
                todayHighlight: true
            });

            // Initialize DataTable
            var table = $('#transactionsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('transactions.data') }}",
                    data: function(d) {
                        d.payment_status = $('#filterPaymentStatus').val();
                        d.review_status = $('#filterReviewStatus').val();
                        d.property_id = $('#filterProperty').val();
                        d.tenant_id = $('#filterTenant').val();
                        d.payment_method = $('#filterPaymentMethod').val();
                        d.payment_type = $('#filterPaymentType').val();
                        d.date_from = parseDateForQuery($('#filterDateFrom').val());
                        d.date_to = parseDateForQuery($('#filterDateTo').val());
                    }
                },
                columns: [
                    { data: 'id', name: 'payments.id', visible: false },
                    { data: 'payment_number', name: 'payment_number' },
                    { data: 'formatted_payment_date', name: 'payment_date' },
                    { data: 'tenant_name', name: 'tenant_name', orderable: false },
                    { data: 'property_name', name: 'property_name', orderable: false },
                    { data: 'formatted_amount', name: 'amount', className: 'text-end fw-semibold' },
                    { data: 'payment_method_badge', name: 'payment_method', orderable: false },
                    { data: 'payment_type_badge', name: 'payment_type', orderable: false },
                    { data: 'invoice_info', name: 'invoice_number', orderable: false },
                    { data: 'paid_by_info', name: 'paid_by', orderable: false },
                    { data: 'gateway_info', name: 'gateway_transaction_id', orderable: false },
                    { data: 'payment_note', name: 'note', orderable: false, searchable: false },
                    { data: 'review_status_badge', name: 'review_status', orderable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[0, 'desc']],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                scrollX: true,
                columnDefs: [
                    { targets: 0, visible: false },
                    { targets: [13], orderable: false, searchable: false }
                ],
                dom: '<"row align-items-center mb-3"<"col-auto"l><"col-auto ms-auto"f>>rt<"#transactionsTotals.transactions-totals-bar">ip',
                initComplete: function() {
                    var api = this.api();
                    $('#transactionsTotals').html('\n                        <div class="transactions-last-updated text-muted small" id="lastUpdated">Last updated: --</div>\n                        <div class="transactions-totals-group">\n                            <div class="transactions-total-item bg-primary-transparent text-primary px-3 py-2 rounded-3">\n                                <span>Total Collected</span>\n                                <strong id="footerTotalCollected">$0.00</strong>\n                            </div>\n                            <div class="transactions-total-item bg-warning-transparent text-warning px-3 py-2 rounded-3">\n                                <span>Processing Fees</span>\n                                <strong class="text-danger" id="footerTotalFees">$0.00</strong>\n                            </div>\n                            <div class="transactions-total-item bg-danger-transparent px-3 py-2 rounded-3">\n                                <span>Voided</span>\n                                <strong class="text-danger" id="footerTotalVoided">$0.00</strong>\n                            </div>\n                            <div class="transactions-total-item bg-success-transparent text-success px-3 py-2 rounded-3">\n                                <span>Net Collected</span>\n                                <strong id="footerTotalNet">$0.00</strong>\n                            </div>\n                        </div>\n                    ');
                    updateLastUpdated();

                    // Wait for table to be fully drawn before syncing columns
                    setTimeout(function() {
                        api.columns.adjust();
                    }, 200);
                },
                drawCallback: function() {
                    loadSummary();
                    updateLastUpdated();
                    var dt = this.api();
                    // Sync header and body column widths after each draw
                    setTimeout(function() {
                        dt.columns.adjust();
                    }, 50);
                }
            });

            function updateLastUpdated() {
                var now = new Date();
                $('#lastUpdated').text('Last updated: ' + now.toLocaleDateString() + ' ' + now.toLocaleTimeString());
            }

            // Apply filters
            $('#applyFilters').on('click', function() {
                table.ajax.reload();
            });

            // Reset filters
            $('#resetFilters').on('click', function() {
                $('#filterPaymentStatus, #filterReviewStatus, #filterProperty, #filterTenant, #filterPaymentMethod, #filterPaymentType')
                    .val('').trigger('change');
                $('#filterDateFrom, #filterDateTo').val('');
                table.ajax.reload();
            });

            // Refresh button
            $('#refreshBtn').on('click', function() {
                table.ajax.reload(null, false);
                loadSummary();
            });

            // Parse date string for query
            function parseDateForQuery(dateString) {
                if (!dateString) return '';
                var parts = dateString.trim().split('/');
                if (parts.length === 3) {
                    return parts[2] + '-' + parts[0].padStart(2, '0') + '-' + parts[1].padStart(2, '0');
                }
                return dateString;
            }

            // Format number
            function numberFormat(num) {
                return parseFloat(num || 0).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            // Load summary statistics
            function loadSummary() {
                var params = {
                    payment_status: $('#filterPaymentStatus').val(),
                    review_status: $('#filterReviewStatus').val(),
                    property_id: $('#filterProperty').val(),
                    tenant_id: $('#filterTenant').val(),
                    payment_method: $('#filterPaymentMethod').val(),
                    date_from: parseDateForQuery($('#filterDateFrom').val()),
                    date_to: parseDateForQuery($('#filterDateTo').val())
                };

                $.ajax({
                    url: "{{ route('transactions.summary') }}",
                    data: params,
                    success: function(data) {
                        // Total active
                        $('#summaryTotalCollected').text('$' + numberFormat(data.total_active_amount));
                        $('#summaryTotalPayments').text(data.total_active_payments || 0);

                        // Pending
                        $('#summaryPendingAmount').text('$' + numberFormat(data.pending_amount));
                        $('#summaryPendingCount').text(data.pending_review || 0);

                        // Confirmed
                        $('#summaryConfirmedAmount').text('$' + numberFormat(data.confirmed_amount));
                        $('#summaryConfirmedCount').text(data.confirmed || 0);

                        // Voided
                        $('#summaryVoidedAmount').text('$' + numberFormat(data.total_voided_amount));
                        $('#summaryVoidedCount').text(data.total_voided || 0);

                        // Footer bar
                        var totalActive = data.total_active_amount || 0;
                        var totalFees = data.total_processing_fees || 0;
                        var totalVoided = data.total_voided_amount || 0;
                        var netCollected = totalActive - totalFees;

                        $('#footerTotalCollected').text('$' + numberFormat(totalActive));
                        $('#footerTotalFees').text('$' + numberFormat(totalFees));
                        $('#footerTotalVoided').text('$' + numberFormat(totalVoided));
                        $('#footerTotalNet').text('$' + numberFormat(Math.max(0, netCollected)));
                    }
                });
            }

            // View Payment Details
            $(document).on('click', '.view-payment', function() {
                var paymentId = $(this).data('id');
                var modal = $('#paymentDetailModal');
                var body = $('#paymentDetailBody');

                body.html(
                    '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Loading payment details...</p></div>'
                    );
                modal.modal('show');

                $.ajax({
                    url: "{{ route('transactions.details', ':id') }}".replace(':id', paymentId),
                    success: function(response) {
                        if (!response.success) {
                            body.html('<div class="alert alert-danger">' + response.message +
                            '</div>');
                            return;
                        }

                        var d = response;
                        var payment = d.payment;
                        var tenant = d.tenant;
                        var invoice = d.invoice;
                        var lease = d.lease;

                        var html = '';

                        // Status badge
                        var statusBadge = '';
                        if (payment.status === 'voided') {
                            statusBadge =
                                '<span class="badge bg-danger fs-6 px-3 py-2"><i class="fe fe-x-circle me-1"></i> VOIDED</span>';
                            if (payment.voided_by_name && payment.voided_by_name !== '-') {
                                statusBadge +=
                                    '<br><small class="text-muted">Voided by: ' + payment
                                    .voided_by_name + ' on ' + payment.voided_at + '</small>';
                            }
                        } else {
                            var reviewLabels = {
                                'pending': '<span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="fe fe-clock me-1"></i> Pending Review</span>',
                                'reviewed': '<span class="badge bg-info fs-6 px-3 py-2"><i class="fe fe-eye me-1"></i> Reviewed</span>',
                                'confirmed': '<span class="badge bg-success fs-6 px-3 py-2"><i class="fe fe-check-circle me-1"></i> Confirmed</span>',
                                'disputed': '<span class="badge bg-danger fs-6 px-3 py-2"><i class="fe fe-alert-triangle me-1"></i> Disputed</span>',
                            };
                            statusBadge = reviewLabels[payment.review_status] ||
                                reviewLabels['pending'];
                        }

                        html += '<div class="row g-3">';
                        html += '<div class="col-12 text-center mb-2">' + statusBadge + '</div>';

                        // Payment Info
                        html += '<div class="col-md-6">';
                        html +=
                            '<div class="card border h-100"><div class="card-header py-2"><h6 class="mb-0 fw-bold"><i class="fe fe-credit-card me-1"></i> Payment Info</h6></div><div class="card-body py-2">';
                        html +=
                            '<table class="table table-sm table-borderless mb-0"><tbody>';
                        html += '<tr><td class="text-muted ps-0" style="width: 40%;">Payment #</td><td class="fw-semibold pe-0 text-end">' +
                            payment.payment_number + '</td></tr>';
                        html += '<tr><td class="text-muted ps-0">Amount</td><td class="fw-bold pe-0 text-end text-primary">' +
                            payment.amount + '</td></tr>';
                        html += '<tr><td class="text-muted ps-0">Base Amount</td><td class="pe-0 text-end">' +
                            payment.base_amount + '</td></tr>';
                        html += '<tr><td class="text-muted ps-0">Processing Fee</td><td class="pe-0 text-end text-danger">' +
                            payment.processing_fee + '</td></tr>';
                        html += '<tr><td class="text-muted ps-0">Total Charged</td><td class="pe-0 text-end">' +
                            payment.total_charged + '</td></tr>';
                        html += '<tr><td class="text-muted ps-0">Payment Date</td><td class="pe-0 text-end">' +
                            payment.payment_date + '</td></tr>';
                        html += '<tr><td class="text-muted ps-0">Method</td><td class="pe-0 text-end">' +
                            payment.payment_method + '</td></tr>';
                        html += '<tr><td class="text-muted ps-0">Type</td><td class="pe-0 text-end">' +
                            payment.payment_type + '</td></tr>';
                        html += '<tr><td class="text-muted ps-0">Paid By</td><td class="pe-0 text-end">' +
                            payment.paid_by + '</td></tr>';
                        html += '<tr><td class="text-muted ps-0">Recorded By</td><td class="pe-0 text-end">' +
                            payment.recorded_by_name + '</td></tr>';
                        html += '<tr><td class="text-muted ps-0">Reference #</td><td class="pe-0 text-end"><code>' +
                            payment.reference_number + '</code></td></tr>';
                        html += '<tr><td class="text-muted ps-0">Deposit Date</td><td class="pe-0 text-end">' +
                            payment.deposit_date + '</td></tr>';
                        html += '<tr><td class="text-muted ps-0">Created At</td><td class="pe-0 text-end">' +
                            payment.created_at + '</td></tr>';
                        html += '</tbody></table>';
                        html += '</div></div></div>';

                        // Tenant & Lease Info
                        html += '<div class="col-md-6">';

                        // Tenant
                        html +=
                            '<div class="card border mb-2"><div class="card-header py-2"><h6 class="mb-0 fw-bold"><i class="fe fe-user me-1"></i> Tenant</h6></div><div class="card-body py-2">';
                        html +=
                            '<table class="table table-sm table-borderless mb-0"><tbody>';
                        html += '<tr><td class="text-muted ps-0" style="width: 35%;">Name</td><td class="fw-semibold pe-0 text-end">' +
                            tenant.name + '</td></tr>';
                        html += '<tr><td class="text-muted ps-0">Email</td><td class="pe-0 text-end">' +
                            tenant.email + '</td></tr>';
                        html += '<tr><td class="text-muted ps-0">Phone</td><td class="pe-0 text-end">' +
                            tenant.phone + '</td></tr>';
                        html += '</tbody></table>';
                        html += '</div></div>';

                        // Lease
                        html +=
                            '<div class="card border mb-2"><div class="card-header py-2"><h6 class="mb-0 fw-bold"><i class="fe fe-home me-1"></i> Lease & Property</h6></div><div class="card-body py-2">';
                        html +=
                            '<table class="table table-sm table-borderless mb-0"><tbody>';
                        html += '<tr><td class="text-muted ps-0" style="width: 35%;">Property</td><td class="fw-semibold pe-0 text-end">' +
                            lease.property_name + '</td></tr>';
                        html += '<tr><td class="text-muted ps-0">Bed/Unit</td><td class="pe-0 text-end">' +
                            lease.bed_label + '</td></tr>';
                        html += '<tr><td class="text-muted ps-0">Rent</td><td class="pe-0 text-end">' +
                            lease.rent_amount + '</td></tr>';
                        html += '<tr><td class="text-muted ps-0">Lease Period</td><td class="pe-0 text-end">' +
                            lease.start_date + ' - ' + lease.end_date + '</td></tr>';
                        html += '</tbody></table>';
                        html += '</div></div>';

                        // Invoice
                        html +=
                            '<div class="card border"><div class="card-header py-2"><h6 class="mb-0 fw-bold"><i class="fe fe-file me-1"></i> Invoice</h6></div><div class="card-body py-2">';
                        html +=
                            '<table class="table table-sm table-borderless mb-0"><tbody>';
                        html += '<tr><td class="text-muted ps-0" style="width: 35%;">Invoice #</td><td class="fw-semibold pe-0 text-end">' +
                            invoice.invoice_number + '</td></tr>';
                        html += '<tr><td class="text-muted ps-0">Type</td><td class="pe-0 text-end">' +
                            invoice.type + '</td></tr>';
                        html += '<tr><td class="text-muted ps-0">Total</td><td class="pe-0 text-end">' +
                            invoice.total_amount + '</td></tr>';
                        html += '<tr><td class="text-muted ps-0">Paid</td><td class="pe-0 text-end text-success">' +
                            invoice.paid_amount + '</td></tr>';
                        html += '<tr><td class="text-muted ps-0">Balance</td><td class="pe-0 text-end' +
                            (invoice.balance_due !== '$0.00' ? ' text-danger' : '') + ' fw-bold">' +
                            invoice.balance_due + '</td></tr>';
                        html += '<tr><td class="text-muted ps-0">Status</td><td class="pe-0 text-end">' +
                            invoice.status + '</td></tr>';
                        html += '<tr><td class="text-muted ps-0">Due Date</td><td class="pe-0 text-end">' +
                            invoice.due_date + '</td></tr>';
                        html += '</tbody></table>';
                        html += '</div></div>';

                        html += '</div>';

                        // Note section
                        if (payment.note && payment.note !== '-') {
                            html += '<div class="col-12">';
                            html +=
                                '<div class="card border"><div class="card-header py-2"><h6 class="mb-0 fw-bold"><i class="fe fe-file-text me-1"></i> Note</h6></div><div class="card-body py-2">';
                            html += '<p class="mb-0">' + escapeHtml(payment.note) + '</p>';
                            html += '</div></div></div>';
                        }

                        // Void info
                        if (payment.status === 'voided' && payment.void_reason !== '-') {
                            html += '<div class="col-12">';
                            html +=
                                '<div class="card border border-danger"><div class="card-header py-2 bg-danger-transparent"><h6 class="mb-0 fw-bold text-danger"><i class="fe fe-x-circle me-1"></i> Void Details</h6></div><div class="card-body py-2">';
                            html += '<p class="mb-1"><strong>Reason:</strong> ' + escapeHtml(payment
                                .void_reason) + '</p>';
                            html += '<p class="mb-0"><strong>Voided by:</strong> ' + payment
                                .voided_by_name + ' on ' + payment.voided_at + '</p>';
                            html += '</div></div></div>';
                        }

                        // Review info
                        if (payment.reviewed_by_name && payment.reviewed_by_name !== '-') {
                            html += '<div class="col-12">';
                            html +=
                                '<div class="card border"><div class="card-header py-2"><h6 class="mb-0 fw-bold"><i class="fe fe-check-square me-1"></i> Review Info</h6></div><div class="card-body py-2">';
                            html += '<p class="mb-1"><strong>Reviewed by:</strong> ' + payment
                                .reviewed_by_name + ' on ' + payment.reviewed_at + '</p>';
                            if (payment.review_note && payment.review_note !== '-') {
                                html += '<p class="mb-0"><strong>Note:</strong> ' + escapeHtml(payment
                                    .review_note) + '</p>';
                            }
                            html += '</div></div></div>';
                        }

                        // Gateway Info
                        if (payment.gateway_transaction_id !== '-' || payment.stripe_payment_intent_id !==
                            '-') {
                            html += '<div class="col-12">';
                            html +=
                                '<div class="card border"><div class="card-header py-2"><h6 class="mb-0 fw-bold"><i class="fe fe-shield me-1"></i> Gateway Details</h6></div><div class="card-body py-2">';
                            html +=
                                '<table class="table table-sm table-borderless mb-0"><tbody>';
                            if (payment.gateway_transaction_id !== '-') {
                                html +=
                                    '<tr><td class="text-muted ps-0">Gateway Transaction ID</td><td class="pe-0 text-end"><code>' +
                                    payment.gateway_transaction_id + '</code></td></tr>';
                            }
                            if (payment.stripe_payment_intent_id !== '-') {
                                html +=
                                    '<tr><td class="text-muted ps-0">Stripe Payment Intent</td><td class="pe-0 text-end"><code>' +
                                    payment.stripe_payment_intent_id + '</code></td></tr>';
                            }
                            html += '</tbody></table>';
                            html += '</div></div></div>';
                        }

                        // Related Transactions
                        if (d.transactions && d.transactions.length > 0) {
                            html += '<div class="col-12">';
                            html +=
                                '<div class="card border"><div class="card-header py-2"><h6 class="mb-0 fw-bold"><i class="fe fe-activity me-1"></i> Related Transactions (' +
                                d.transactions.length + ')</h6></div><div class="card-body py-2 px-0">';
                            html +=
                                '<div class="table-responsive"><table class="table table-sm table-striped mb-0">';
                            html +=
                                '<thead><tr><th>#</th><th>Type</th><th>Entry</th><th class="text-end">Amount</th><th>Date</th><th>Description</th></tr></thead><tbody>';
                            $.each(d.transactions, function(i, txn) {
                                var entryBadge = txn.entry_type == 'credit' ?
                                    '<span class="badge bg-success">Credit</span>' :
                                    '<span class="badge bg-danger">Debit</span>';
                                html += '<tr>';
                                html += '<td><code>' + txn.transaction_number + '</code></td>';
                                html += '<td>' + ucfirst(txn.type) + '</td>';
                                html += '<td>' + entryBadge + '</td>';
                                html += '<td class="text-end fw-semibold">' + txn.amount +
                                '</td>';
                                html += '<td>' + txn.date + '</td>';
                                html += '<td><small>' + escapeHtml(txn.description) +
                                '</small></td>';
                                html += '</tr>';
                            });
                            html += '</tbody></table></div>';
                            html += '</div></div></div>';
                        }

                        html += '</div>';
                        body.html(html);
                    },
                    error: function() {
                        body.html(
                            '<div class="alert alert-danger">Failed to load payment details. Please try again.</div>'
                            );
                    }
                });
            });

            // Review Payment
            $(document).on('click', '.review-payment', function() {
                var paymentId = $(this).data('id');
                var newStatus = $(this).data('status');
                var statusLabels = {
                    'pending': 'Reset to Pending',
                    'reviewed': 'Mark as Reviewed',
                    'confirmed': 'Confirm Payment',
                    'disputed': 'Mark as Disputed'
                };
                var statusDescs = {
                    'pending': 'reset the review status to Pending',
                    'reviewed': 'mark this payment as Reviewed',
                    'confirmed': 'confirm this payment',
                    'disputed': 'mark this payment as Disputed'
                };
                var statusIcons = {
                    'pending': 'fe-rotate-ccw text-warning',
                    'reviewed': 'fe-eye text-info',
                    'confirmed': 'fe-check-circle text-success',
                    'disputed': 'fe-alert-triangle text-danger'
                };

                $('#reviewPaymentId').val(paymentId);
                $('#reviewNewStatus').val(newStatus);
                $('#reviewNote').val('');
                $('#reviewStatusLabel').html(
                    '<i class="fe ' + (statusIcons[newStatus] || 'fe-edit') + ' me-2"></i>' + statusLabels[
                    newStatus]
                );
                $('#reviewStatusInfo').text('You are about to ' + statusDescs[newStatus] + '.');
                $('#reviewModal').modal('show');
            });

            $('#reviewForm').on('submit', function(e) {
                e.preventDefault();
                var paymentId = $('#reviewPaymentId').val();
                var newStatus = $('#reviewNewStatus').val();
                var note = $('#reviewNote').val();

                $.ajax({
                    url: "{{ route('transactions.review', ':id') }}".replace(':id', paymentId),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: newStatus,
                        note: note
                    },
                    success: function(response) {
                        $('#reviewModal').modal('hide');
                        showToast(response.message, 'success');
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON?.message || 'Failed to update status';
                        showToast(msg, 'error');
                    }
                });
            });

            // Void Payment
            $(document).on('click', '.void-payment', function() {
                var paymentId = $(this).data('id');
                $('#voidPaymentId').val(paymentId);
                $('#voidReason').val('');
                $('#voidModal').modal('show');
            });

            $('#voidForm').on('submit', function(e) {
                e.preventDefault();
                var paymentId = $('#voidPaymentId').val();
                var reason = $('#voidReason').val();

                if (!reason || reason.trim().length < 3) {
                    showToast('Please provide a valid reason (minimum 3 characters).', 'error');
                    return;
                }

                $('#confirmVoidBtn').prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

                $.ajax({
                    url: "{{ route('transactions.void', ':id') }}".replace(':id', paymentId),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        reason: reason
                    },
                    success: function(response) {
                        $('#voidModal').modal('hide');
                        showToast(response.message, 'success');
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON?.message || xhr.responseJSON?.errors?.reason?.[0] ||
                            'Failed to void payment';
                        showToast(msg, 'error');
                    },
                    complete: function() {
                        $('#confirmVoidBtn').prop('disabled', false).html(
                            '<i class="fe fe-x-circle me-1"></i> Void Payment');
                    }
                });
            });

            // Export helpers
            function getExportQueryString() {
                var params = new URLSearchParams();
                var filters = {
                    payment_status: $('#filterPaymentStatus').val(),
                    review_status: $('#filterReviewStatus').val(),
                    property_id: $('#filterProperty').val(),
                    tenant_id: $('#filterTenant').val(),
                    payment_method: $('#filterPaymentMethod').val(),
                    payment_type: $('#filterPaymentType').val(),
                    date_from: parseDateForQuery($('#filterDateFrom').val()),
                    date_to: parseDateForQuery($('#filterDateTo').val()),
                };
                for (var key in filters) {
                    if (filters[key]) params.append(key, filters[key]);
                }
                return params.toString();
            }

            $('#exportExcelBtn').on('click', function(e) {
                e.preventDefault();
                var params = getExportQueryString();
                window.location.href = "{{ route('transactions.export.excel') }}" + (params ? '?' + params : '');
            });

            $('#exportCsvBtn').on('click', function(e) {
                e.preventDefault();
                var params = getExportQueryString();
                window.location.href = "{{ route('transactions.export.csv') }}" + (params ? '?' + params : '');
            });

            $('#exportPdfBtn').on('click', function(e) {
                e.preventDefault();
                var params = getExportQueryString();
                window.location.href = "{{ route('transactions.export.pdf') }}" + (params ? '?' + params : '');
            });

            // Inline Note Editing
            $(document).on('click', '.note-display', function() {
                var wrapper = $(this).closest('.inline-note-wrapper');
                wrapper.find('.note-display').addClass('d-none');
                wrapper.find('.inline-note-editor').removeClass('d-none').focus();
                wrapper.find('.note-actions').removeClass('d-none');
            });

            $(document).on('click', '.note-cancel-btn', function() {
                var wrapper = $(this).closest('.inline-note-wrapper');
                var editor = wrapper.find('.inline-note-editor');
                // Restore original text
                var displayText = wrapper.find('.note-text').data('original') || wrapper.find('.note-text').text();
                editor.val(wrapper.find('.note-text').data('original') || '');
                wrapper.find('.inline-note-editor').addClass('d-none');
                wrapper.find('.note-actions').addClass('d-none');
                wrapper.find('.note-display').removeClass('d-none');
            });

            $(document).on('click', '.note-save-btn', function() {
                var btn = $(this);
                var wrapper = btn.closest('.inline-note-wrapper');
                var editor = wrapper.find('.inline-note-editor');
                var paymentId = editor.data('payment-id');
                var note = editor.val().trim();

                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" style="width:10px;height:10px;"></span>');

                $.ajax({
                    url: "{{ route('transactions.note', ':id') }}".replace(':id', paymentId),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        note: note
                    },
                    success: function(response) {
                        var displayText = wrapper.find('.note-text');
                        var preview = response.preview || '';
                        var fullNote = response.note || '';

                        if (fullNote) {
                            displayText.text(preview).attr('title', fullNote);
                            displayText.removeClass('fst-italic text-muted');
                        } else {
                            displayText.html('<span class="text-muted fst-italic">+ Add note</span>').attr('title', 'Click to add note');
                        }
                        displayText.data('original', fullNote);

                        wrapper.find('.inline-note-editor').addClass('d-none');
                        wrapper.find('.note-actions').addClass('d-none');
                        wrapper.find('.note-display').removeClass('d-none');
                        showToast(response.message, 'success');
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON?.message || 'Failed to save note';
                        showToast(msg, 'error');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fe fe-check" style="font-size:11px;"></i>');
                    }
                });
            });

            // Save on Ctrl+Enter
            $(document).on('keydown', '.inline-note-editor', function(e) {
                if (e.ctrlKey && e.key === 'Enter') {
                    e.preventDefault();
                    $(this).closest('.inline-note-wrapper').find('.note-save-btn').click();
                }
                // Cancel on Escape
                if (e.key === 'Escape') {
                    e.preventDefault();
                    $(this).closest('.inline-note-wrapper').find('.note-cancel-btn').click();
                }
            });

            // Helper: Show toast using toastr (consistent with rest of app)
            function showToast(message, type) {
                if (typeof toastr !== 'undefined') {
                    if (type === 'error') {
                        toastr.error(message);
                    } else {
                        toastr.success(message);
                    }
                } else {
                    alert(message);
                }
            }

            // Helper: escape HTML
            function escapeHtml(text) {
                if (!text) return '';
                var div = document.createElement('div');
                div.appendChild(document.createTextNode(text));
                return div.innerHTML;
            }

            // Helper: ucfirst
            function ucfirst(str) {
                return str.charAt(0).toUpperCase() + str.slice(1);
            }

            // View mode toggle
            $('input[name="viewMode"]').on('change', function() {
                var mode = $(this).val();
                switchView(mode);
            });

            function switchView(mode) {
                var $tableCard = $('#tableCard');
                var $chartCard = $('#chartViewCard');
                var $filterCard = $('#filterCard');

                if (mode === 'chart') {
                    $tableCard.addClass('d-none');
                    $chartCard.removeClass('d-none');
                    $filterCard.addClass('d-none');
                    initChart();
                } else {
                    $chartCard.addClass('d-none');
                    $tableCard.removeClass('d-none');
                    $filterCard.removeClass('d-none');

                    if (mode === 'compact') {
                        $tableCard.addClass('compact-mode');
                        // Hide non-essential columns in DataTable
                        table.column(4).visible(false);  // Property
                        table.column(10).visible(false); // Gateway Info
                        table.column(11).visible(false); // Note
                    } else {
                        $tableCard.removeClass('compact-mode');
                        // Show all columns
                        table.column(4).visible(true);
                        table.column(10).visible(true);
                        table.column(11).visible(true);
                    }

                    try {
                        setTimeout(function() { table.columns.adjust(); }, 100);
                    } catch(e) {}
                }

                loadSummary();
            }

            // Chart initialization
            function initChart() {
                var ctx = document.getElementById('paymentsChart');
                if (!ctx) return;

                var months = $('#chartTimeframe').val();

                $.ajax({
                    url: "{{ route('transactions.chart-data') }}",
                    data: { months: months },
                    success: function(response) {
                        if (!response.success) return;

                        if (paymentsChartInstance) {
                            paymentsChartInstance.destroy();
                        }

                        paymentsChartInstance = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: response.labels,
                                datasets: [
                                    {
                                        label: 'Collected',
                                        data: response.datasets.collected,
                                        backgroundColor: 'rgba(40, 167, 69, 0.75)',
                                        borderColor: 'rgba(40, 167, 69, 1)',
                                        borderWidth: 1,
                                        borderRadius: 4,
                                        order: 1
                                    },
                                    {
                                        label: 'Processing Fees',
                                        data: response.datasets.fees,
                                        backgroundColor: 'rgba(255, 193, 7, 0.7)',
                                        borderColor: 'rgba(255, 193, 7, 1)',
                                        borderWidth: 1,
                                        borderRadius: 4,
                                        order: 3
                                    },
                                    {
                                        label: 'Voided',
                                        data: response.datasets.voided,
                                        backgroundColor: 'rgba(220, 53, 69, 0.6)',
                                        borderColor: 'rgba(220, 53, 69, 1)',
                                        borderWidth: 1,
                                        borderRadius: 4,
                                        order: 2
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'top',
                                        labels: { usePointStyle: true, padding: 16 }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return context.dataset.label + ': $' + Number(context.raw).toLocaleString('en-US', { minimumFractionDigits: 2 });
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: { display: false },
                                        ticks: { font: { size: 11 } }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            font: { size: 11 },
                                            callback: function(value) {
                                                return '$' + value.toLocaleString();
                                            }
                                        }
                                    }
                                }
                            }
                        });

                        // Update breakdown cards
                        renderBreakdown('#chartMethodBreakdown', response.method_breakdown);
                        renderBreakdown('#chartReviewBreakdown', response.review_breakdown);
                    }
                });
            }

            function renderBreakdown(containerId, data) {
                if (!data || data.length === 0) {
                    $(containerId).html('<div class="text-muted small">No data available</div>');
                    return;
                }
                var colors = ['#28a745','#ffc107','#17a2b8','#dc3545','#6f42c1','#fd7e14','#20c997','#007bff'];
                var html = '';
                $.each(data, function(i, item) {
                    var color = colors[i % colors.length];
                    html += '<div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">';
                    html += '<span class="small"><span class="d-inline-block rounded-circle me-2" style="width:10px;height:10px;background:' + color + ';"></span>' + escapeHtml(item.label) + '</span>';
                    html += '<span class="small fw-semibold">$' + numberFormat(item.total) + ' <span class="text-muted fw-normal">(' + item.count + ')</span></span>';
                    html += '</div>';
                });
                $(containerId).html(html);
            }

            // Rebuild chart on timeframe change
            $('#chartTimeframe').on('change', function() {
                initChart();
            });

            // Initial load
            loadSummary();

            // Re-sync columns on xhr
            table.on('xhr', function() {
                setTimeout(function() {
                    table.columns.adjust();
                }, 100);
            });

            $(window).on('resize', function() {
                table.columns.adjust();
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .select2-container {
            width: 100% !important;
        }

        .icon-service-sm {
            width: 38px;
            height: 38px;
            min-width: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        #summaryCards .card {
            min-height: unset;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: default;
        }
        #summaryCards .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        #summaryCards .card-body {
            padding: 0.85rem;
        }

        #summaryCards h5 {
            font-size: 1.25rem;
            letter-spacing: -0.02em;
        }

        #summaryCards .icon-service-sm {
            transition: transform 0.2s ease;
        }
        #summaryCards .card:hover .icon-service-sm {
            transform: scale(1.1);
        }

        /* Status colors */
        .bg-purple {
            background-color: #6f42c1 !important;
        }

        .bg-indigo {
            background-color: #6610f2 !important;
        }

        .bg-blue {
            background-color: #007bff !important;
        }

        /* DataTable appearance */
        #transactionsTable th,
        #transactionsTable td {
            vertical-align: middle;
            padding: 0.55rem 0.65rem;
            font-size: 0.875rem;
        }
        #transactionsTable th {
            white-space: nowrap;
            font-weight: 600;
            background-color: #f8fafc;
        }
        #transactionsTable td {
            white-space: nowrap;
        }
        #transactionsTable tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.03);
        }
        /* Add border around the table wrapper instead of table-bordered on the table */
        #tableCard .card-body {
            padding: 0;
        }
        #tableCard .card-body > .dataTables_wrapper {
            padding: 1rem;
        }
        div.dataTables_scrollHeadInner table.dataTable {
            border-collapse: separate;
        }
        div.dataTables_scrollBody table.dataTable {
            border-collapse: separate;
            border-top: none;
        }
        div.dataTables_scrollHead table.dataTable {
            border-bottom: 2px solid #dee2e6;
        }
        div.dataTables_scrollHeadInner {
            width: 100% !important;
        }
        div.dataTables_scrollHeadInner table.dataTable {
            width: 100% !important;
        }

        /* DataTable controls */
        .dataTables_wrapper .dataTables_length select {
            display: inline-block;
            width: auto;
            min-width: 72px;
            height: 34px;
            margin: 0 0.35rem;
            padding: 0.35rem 2rem 0.35rem 0.75rem;
            font-size: 0.875rem;
        }

        .dataTables_wrapper .dataTables_filter input {
            min-width: 220px;
            height: 34px;
            margin-left: 0.5rem;
            font-size: 0.875rem;
            padding: 0.35rem 0.75rem;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 0;
        }

        /* Fix dropdown z-index */
        .select2-container--open .select2-dropdown,
        .datepicker-dropdown {
            z-index: 9999 !important;
        }

        #filterCard {
            overflow: visible !important;
        }
        #filterCard .card-body {
            overflow: visible !important;
        }

        /* Detail Modal */
        #paymentDetailBody .card {
            box-shadow: none;
            border-color: #e9edf4;
        }

        #paymentDetailBody .card-header {
            background-color: #f8fafc;
            border-bottom: 1px solid #e9edf4;
        }

        #paymentDetailBody .table-sm td {
            padding: 0.3rem 0;
        }

        /* Button groups in table */
        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
        }

        /* Footer Totals Bar */
        .transactions-totals-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            padding: 0.75rem 1rem;
            margin-top: 0.75rem;
            background: #f8fafc;
            border: 1px solid #e9edf4;
            border-radius: 6px;
        }

        .transactions-last-updated {
            flex: 1 1 220px;
        }

        .transactions-totals-group {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 1rem;
            flex: 1 1 auto;
            flex-wrap: wrap;
        }

        .transactions-total-item {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 170px;
            justify-content: space-between;
            font-size: 0.92rem;
        }

        .transactions-total-item span {
            color: #6c757d;
            font-weight: 500;
        }

        .transactions-total-item strong {
            color: #111827;
            font-weight: 700;
        }

        /* Inline Note Editor */
        .inline-note-wrapper .note-display:hover {
            background-color: rgba(217, 166, 0, 0.06);
            border-radius: 4px;
        }
        .inline-note-wrapper .note-display:hover i {
            opacity: 1 !important;
        }
        .inline-note-editor {
            width: 100% !important;
        }
        .inline-note-editor:focus {
            border-color: #D9A600;
            box-shadow: 0 0 0 2px rgba(217, 166, 0, 0.15);
        }
        .note-save-btn {
            line-height: 1;
            padding: 2px 6px;
        }
        .note-cancel-btn {
            line-height: 1;
            padding: 2px 6px;
        }

        /* Compact Mode */
        .compact-mode #transactionsTable th,
        .compact-mode #transactionsTable td {
            padding: 0.35rem 0.5rem;
            font-size: 0.8rem;
        }
        .compact-mode .btn-group-sm .btn {
            padding: 0.15rem 0.35rem;
            font-size: 0.75rem;
        }
        .compact-mode .inline-note-wrapper .note-text {
            max-width: 80px !important;
        }
        .compact-mode #transactionsTable th .badge,
        .compact-mode #transactionsTable td .badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
        }
        .compact-mode .dataTables_wrapper .dataTables_length select {
            height: 28px;
            font-size: 0.8rem;
        }
        .compact-mode .dataTables_wrapper .dataTables_filter input {
            height: 28px;
            font-size: 0.8rem;
            min-width: 160px;
        }
        .compact-mode .transactions-totals-bar {
            padding: 0.5rem 0.75rem;
        }
        .compact-mode .transactions-total-item {
            min-width: 130px;
            font-size: 0.8rem;
        }

        /* View Toggle active state */
        #viewToggle .btn-check:checked + .btn-outline-secondary {
            background-color: #6c757d;
            color: #fff;
            border-color: #6c757d;
        }

        /* Chart styles */
        #chartViewCard .card-header h6 {
            font-size: 0.95rem;
        }
        #chartBreakdownCards .card-body {
            max-height: 210px;
            overflow-y: auto;
        }

        /* Toast */
        .toast-container {
            z-index: 99999;
        }
    </style>
@endpush
