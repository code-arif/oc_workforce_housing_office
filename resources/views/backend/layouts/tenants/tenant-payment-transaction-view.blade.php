<div class="row mt-4">
    <div class="col-12">
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs nav-tabs-custom mb-3" id="paymentTransactionTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments"
                    type="button" role="tab">
                    <i class="fe fe-credit-card me-2"></i>Payment History
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="transactions-tab" data-bs-toggle="tab" data-bs-target="#transactions"
                    type="button" role="tab">
                    <i class="fe fe-activity me-2"></i>Transaction History
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="paymentTransactionTabContent">
            <!-- Payments Tab -->
            <div class="tab-pane fade show active" id="payments" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fe fe-credit-card text-primary me-2"></i>Payment History
                        </h5>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary d-inline-flex align-items-center" onclick="refreshPayments()">
                                <i class="fe fe-refresh-cw me-1"></i>Refresh
                            </button>
                            <button class="btn btn-outline-success d-inline-flex align-items-center" onclick="exportPayments()">
                                <i class="fe fe-download me-1"></i>Export CSV
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Loading State -->
                        <div id="paymentsLoading" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading payment history...</p>
                        </div>

                        <!-- Payment Table -->
                        <div id="paymentsContent" style="display: none;">
                            <div class="table-responsive">
                                <table class="table table-hover" id="paymentsTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Payment #</th>
                                            <th>Invoice</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Type</th>
                                            <th>Reference</th>
                                            <th>Property</th>
                                            <th>Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="paymentsTableBody">
                                        <!-- Dynamic content -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div id="paymentsEmpty" style="display: none;" class="text-center py-5">
                            <i class="fe fe-credit-card text-muted" style="font-size: 48px;"></i>
                            <h5 class="mt-3">No Payments Yet</h5>
                            <p class="text-muted">No payment records found for this tenant.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions Tab -->
            <div class="tab-pane fade" id="transactions" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fe fe-activity text-info me-2"></i>Transaction History
                        </h5>
                        <button class="btn btn-sm btn-outline-primary d-inline-flex align-items-center" onclick="refreshTransactions()">
                            <i class="fe fe-refresh-cw me-1"></i>Refresh
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Loading State -->
                        <div id="transactionsLoading" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading transaction history...</p>
                        </div>

                        <!-- Transaction Table -->
                        <div id="transactionsContent" style="display: none;">
                            <div class="table-responsive">
                                <table class="table table-hover" id="transactionsTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Transaction #</th>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Entry</th>
                                            <th>Amount</th>
                                            <th>Invoice</th>
                                            <th>Payment</th>
                                            <th>Property</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody id="transactionsTableBody">
                                        <!-- Dynamic content -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div id="transactionsEmpty" style="display: none;" class="text-center py-5">
                            <i class="fe fe-activity text-muted" style="font-size: 48px;"></i>
                            <h5 class="mt-3">No Transactions Yet</h5>
                            <p class="text-muted">No transaction records found for this tenant.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Details Modal -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fe fe-file-text me-2"></i>Payment Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Loading State -->
                <div id="paymentDetailsLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Loading payment details...</p>
                </div>

                <!-- Content -->
                <div id="paymentDetailsContent" style="display: none;">
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <!-- Payment Information -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <strong><i class="fe fe-credit-card me-2"></i>Payment Information</strong>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted" width="40%">Payment Number:</td>
                                            <td><strong id="detailPaymentNumber"></strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Amount:</td>
                                            <td><strong class="text-success" id="detailAmount"></strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Payment Date:</td>
                                            <td id="detailPaymentDate"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Payment Method:</td>
                                            <td><span class="badge bg-info" id="detailPaymentMethod"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Payment Type:</td>
                                            <td><span class="badge bg-primary" id="detailPaymentType"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Paid By:</td>
                                            <td id="detailPaidBy"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Created At:</td>
                                            <td id="detailCreatedAt"></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Gateway Information -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <strong><i class="fe fe-zap me-2"></i>Gateway Information</strong>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted" width="40%">Reference Number:</td>
                                            <td><code id="detailReferenceNumber"></code></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Transaction ID:</td>
                                            <td><code id="detailTransactionId"></code></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Tenant Information -->
                            <div class="card">
                                <div class="card-header bg-light">
                                    <strong><i class="fe fe-user me-2"></i>Tenant Information</strong>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted" width="40%">Name:</td>
                                            <td id="detailTenantName"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Email:</td>
                                            <td id="detailTenantEmail"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Phone:</td>
                                            <td id="detailTenantPhone"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Address:</td>
                                            <td id="detailTenantAddress"></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <!-- Invoice Information -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <strong><i class="fe fe-file-text me-2"></i>Invoice Information</strong>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted" width="40%">Invoice Number:</td>
                                            <td><strong id="detailInvoiceNumber"></strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Type:</td>
                                            <td><span class="badge bg-warning" id="detailInvoiceType"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Total Amount:</td>
                                            <td id="detailInvoiceTotal"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Paid Amount:</td>
                                            <td class="text-success" id="detailInvoicePaid"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Balance Due:</td>
                                            <td class="text-danger" id="detailInvoiceBalance"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Status:</td>
                                            <td><span class="badge" id="detailInvoiceStatus"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Due Date:</td>
                                            <td id="detailInvoiceDueDate"></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Lease Information -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <strong><i class="fe fe-home me-2"></i>Lease Information</strong>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted" width="40%">Property:</td>
                                            <td id="detailLeaseProperty"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Unit:</td>
                                            <td id="detailLeaseUnit"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Monthly Rent:</td>
                                            <td id="detailLeaseRent"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Lease Period:</td>
                                            <td id="detailLeasePeriod"></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Review Status -->
                            <div class="card">
                                <div class="card-header bg-light">
                                    <strong><i class="fe fe-check-circle me-2"></i>Review Status</strong>
                                </div>
                                <div class="card-body">
                                    <form id="paymentReviewForm">
                                        <input type="hidden" id="reviewPaymentId">
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" id="reviewStatus" name="review_status">
                                                <option value="pending">Pending</option>
                                                <option value="reviewed">Reviewed</option>
                                                <option value="confirmed">Confirmed</option>
                                                <option value="disputed">Disputed</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Review Note</label>
                                            <textarea class="form-control" id="reviewNote" name="review_note" rows="3" placeholder="Add review notes..."></textarea>
                                        </div>
                                        <div id="reviewInfo" class="mb-3" style="display: none;">
                                            <small class="text-muted">
                                                <strong>Last Reviewed:</strong> <span id="lastReviewedAt"></span><br>
                                                <strong>Reviewed By:</strong> <span id="lastReviewedBy"></span>
                                            </small>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fe fe-save me-1"></i>Update Review
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <strong><i class="fe fe-message-square me-2"></i>Payment Notes</strong>
                        </div>
                        <div class="card-body">
                            <p id="detailNote" class="mb-0"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<style>
    /* Tab Styling */
    .nav-tabs-custom {
        border-bottom: 2px solid #e9ecef;
    }

    .nav-tabs-custom .nav-link {
        border: none;
        color: #6c757d;
        padding: 12px 24px;
        font-weight: 500;
        transition: all 0.3s;
        border-bottom: 3px solid transparent;
    }

    .nav-tabs-custom .nav-link:hover {
        color: #D9A600 !important;
        background: #f8f9fa;
        border-bottom: 3px solid #D9A600;
    }

    .nav-tabs-custom .nav-link.active {
        color: #D9A600 !important;
        background: transparent;
        border-bottom: 3px solid #D9A600;
    }

    /* Payment Table Styling */
    #paymentsTable thead th,
    #transactionsTable thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        color: #6c757d;
        border-bottom: 2px solid #e9ecef;
        padding: 12px 8px;
    }

    #paymentsTable tbody tr,
    #transactionsTable tbody tr {
        transition: background-color 0.2s;
    }

    #paymentsTable tbody tr:hover,
    #transactionsTable tbody tr:hover {
        background-color: #f8f9fa;
    }

    #paymentsTable td,
    #transactionsTable td {
        vertical-align: middle;
        padding: 12px 8px;
        font-size: 13px;
    }

    /* Badge Styling */
    .badge {
        font-weight: 500;
        padding: 4px 10px;
        font-size: 11px;
    }

    /* Code Styling */
    code {
        background: #f8f9fa;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 11px;
        color: #495057;
    }

    /* Modal Styling */
    .modal-xl {
        max-width: 1200px;
    }

    .modal-header.bg-primary {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    }

    /* Card in Modal */
    .modal-body .card {
        border: 1px solid #e9ecef;
        box-shadow: none;
        margin-bottom: 0;
    }

    .modal-body .card-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 10px 15px;
        font-size: 13px;
    }

    .modal-body .card-body {
        padding: 15px;
    }

    /* Table in Modal */
    .modal-body table.table-borderless td {
        padding: 6px 0;
        font-size: 13px;
    }

    .modal-body table.table-borderless td:first-child {
        font-weight: 500;
    }

    /* Loading State */
    #paymentsLoading .spinner-border,
    #transactionsLoading .spinner-border,
    #paymentDetailsLoading .spinner-border {
        width: 3rem;
        height: 3rem;
    }

    /* Empty State */
    #paymentsEmpty i,
    #transactionsEmpty i {
        opacity: 0.3;
    }

    #paymentsEmpty h5,
    #transactionsEmpty h5 {
        color: #6c757d;
        margin-top: 15px;
    }

    /* Button Group */
    .btn-group-sm .btn {
        padding: 4px 12px;
        font-size: 12px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .modal-xl {
            max-width: 95%;
        }

        .nav-tabs-custom .nav-link {
            padding: 10px 16px;
            font-size: 13px;
        }

        #paymentsTable,
        #transactionsTable {
            font-size: 12px;
        }

        .modal-body .row .col-md-6 {
            margin-bottom: 15px;
        }
    }

    /* DataTables Custom Styling */
    .dataTables_wrapper .dataTables_length select {
        padding: 4px 8px;
        border-radius: 4px;
        border: 1px solid #ced4da;
    }

    .dataTables_wrapper .dataTables_filter input {
        padding: 4px 8px;
        border-radius: 4px;
        border: 1px solid #ced4da;
        margin-left: 8px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 4px 10px;
        margin: 0 2px;
        border-radius: 4px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #6366f1;
        color: white !important;
        border: 1px solid #6366f1;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        color: #495057 !important;
    }

    /* Review Form Styling */
    #paymentReviewForm .form-label {
        font-weight: 600;
        font-size: 13px;
        color: #495057;
        margin-bottom: 6px;
    }

    #paymentReviewForm .form-select,
    #paymentReviewForm .form-control {
        font-size: 13px;
    }

    #reviewInfo {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 6px;
        border-left: 3px solid #6366f1;
    }

    /* Smooth Transitions */
    .card,
    .modal-content,
    .badge,
    .btn {
        transition: all 0.3s ease;
    }

    /* Print Styles */
    @media print {

        .modal-header,
        .modal-footer,
        .btn,
        #paymentReviewForm {
            display: none !important;
        }

        .modal-body {
            padding: 0;
        }

        .card {
            border: 1px solid #ddd;
            page-break-inside: avoid;
        }
    }
</style>
