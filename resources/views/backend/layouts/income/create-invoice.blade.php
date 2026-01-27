{{-- resources/views/backend/layouts/invoices/create.blade.php --}}
@extends('backend.app')

@section('title', 'Create New Invoice')

@section('content')
    <div class="app-content main-content mt-0 mb-3">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header mb-4">
                    <div class="d-flex justify-content-between align-items-center w-100 mb-3">

                        <!-- LEFT SIDE -->
                        <div>
                            <h1 class="page-title mb-1">CREATE NEW INVOICE</h1>

                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard') }}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('invoices.index') }}">Invoices</a>
                                </li>
                                <li class="breadcrumb-item active">Create Invoice</li>
                            </ol>
                        </div>

                        <!-- RIGHT SIDE -->
                        <a href="{{ route('invoices.index') }}" class="btn btn-warning d-inline-flex align-items-center">
                            <i class="fe fe-arrow-left me-1"></i>
                            Back to invoices
                        </a>
                    </div>


                </div>

                <form id="invoiceForm">
                    @csrf
                    <div class="row">
                        <!-- Left Side - Form -->
                        <div class="col-xl-8">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Tenant Details</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Tenant Selection -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label">Tenant <span class="text-danger">*</span></label>
                                            <select class="form-select select3" id="tenant_id" name="tenant_id" required>
                                                <option value="">Select Tenant</option>
                                                @foreach ($tenants as $tenant)
                                                    <option value="{{ $tenant->id }}">
                                                        {{ $tenant->profile?->first_name }}
                                                        {{ $tenant->profile?->last_name }}
                                                        ({{ $tenant?->email }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Tenant Info Display -->
                                    <div class="alert alert-info" id="tenantInfoDisplay" style="display: none;">
                                        <div class="d-flex align-items-center">
                                            <i class="fe fe-user me-3 fs-4"></i>
                                            <div>
                                                <strong id="tenantName"></strong><br>
                                                <small class="text-muted">
                                                    <span id="tenantEmail"></span> | <span id="tenantPhone"></span><br>
                                                    Property: <span id="tenantProperty"></span>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Item Details -->
                            <div class="card mt-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Invoice Item Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th width="25%">ITEM</th>
                                                    <th width="30%">DESCRIPTION</th>
                                                    <th width="15%" class="text-center">QUANTITY</th>
                                                    <th width="15%" class="text-center">RATE</th>
                                                    <th width="15%" class="text-center">AMOUNT</th>
                                                </tr>
                                            </thead>
                                            <tbody id="invoiceItemsBody">
                                                <!-- Initial Row -->
                                                <tr class="invoice-item-row">
                                                    <td>
                                                        <select class="form-select form-select-sm item-select"
                                                            name="items[0][item_id]" required>
                                                            <option value="">Select Invoice Type</option>
                                                            @foreach ($items as $item)
                                                                <option value="{{ $item->id }}"
                                                                    data-name="{{ $item->name }}"
                                                                    data-price="{{ $item->price }}">
                                                                    {{ $item->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden" class="item-name" name="items[0][item_name]">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm"
                                                            name="items[0][description]" placeholder="Description">
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                            class="form-control form-control-sm text-center item-quantity"
                                                            name="items[0][quantity]" value="1" min="1"
                                                            required>
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                            class="form-control form-control-sm text-end item-rate"
                                                            name="items[0][rate]" value="0" min="0"
                                                            step="0.01" required>
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                            class="form-control form-control-sm text-end item-amount bg-light"
                                                            value="0" readonly>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <button type="button"
                                        class="btn btn-sm btn-outline-primary d-inline-flex align-items-center"
                                        id="addItemBtn">
                                        <i class="fe fe-plus me-1"></i>
                                        Add Item Type
                                    </button>


                                    <!-- Internal Notes -->
                                    <div class="mt-4">
                                        <label class="form-label">Internal Notes</label>
                                        <textarea class="form-control" name="notes" rows="3" placeholder="Add internal notes here"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Due Date, Type & Recurring -->
                            <div class="card mt-3">
                                <div class="card-body">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label">Due Date <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control datepicker2" id="due_date"
                                                name="due_date" required placeholder="yyyy-mm-dd">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Invoice Type <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select" id="type" name="type" required>
                                                <option value="RENT">Rent</option>
                                                <option value="DEPOSIT">Deposit</option>
                                                <option value="ITEM_SALE" selected>Item Sale</option>
                                                <option value="CLEANING_FEE">Cleaning Fee</option>
                                                <option value="LATE_FEE">Late Fee</option>
                                                <option value="FEE">Fee</option>
                                                <option value="OTHER">Other</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Recurring Section -->
                                    <div class="mt-4 pt-3 border-top">
                                        <div class="d-flex justify-content-between align-items-center mb-3"
                                            style="margin-left: 15px;">
                                            <div class="custom-recurring-row p-3">
                                                <div class="form-check form-switch d-flex align-items-start gap-3">
                                                    <input class="form-check-input mt-1" type="checkbox"
                                                        id="is_recurring" name="is_recurring" value="1">

                                                    <label class="form-check-label fw-semibold" for="is_recurring"
                                                        style="margin-left:10px">
                                                        Make this a recurring invoice
                                                        <div class="text-muted small mt-1">
                                                            Invoice will be automatically generated
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Recurring Frequency Container -->
                                        <div id="recurringFrequencyContainer" style="display: none;">
                                            <div class="row g-3 mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Frequency</label>
                                                    <select class="form-select form-control" id="recurring_frequency"
                                                        name="recurring_frequency">
                                                        <option value="WEEKLY">Weekly</option>
                                                        <option value="MONTHLY" selected>Monthly</option>
                                                        <option value="YEARLY">Yearly</option>
                                                        <option value="CUSTOM">Custom</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Custom Recurring Section -->
                                            <div id="customRecurringSection" class="mt-3" style="display: none;">
                                                <div class="card border-primary shadow-sm">
                                                    <div
                                                        class="card-header bg-primary bg-opacity-10 d-flex justify-content-between align-items-center py-2">
                                                        <h6 class="mb-0 text-primary">
                                                            <i class="fe fe-calendar me-2"></i>Custom Recurring Schedule
                                                        </h6>
                                                        <button type="button"
                                                            class="btn btn-sm btn-light d-inline-flex align-items-center"
                                                            id="addCustomRecurringBtn">
                                                            <i class="fe fe-plus me-1"></i> Add Date & Amount
                                                        </button>
                                                    </div>
                                                    <div class="card-body">
                                                        <p class="text-muted small mb-3">
                                                            Define each future invoice's due date and amount. Each entry
                                                            will create a separate scheduled invoice.
                                                        </p>

                                                        <div id="customRecurringList" class="mb-3"></div>

                                                        <div class="alert alert-info mb-0" id="noCustomRecurringAlert">
                                                            <i class="fe fe-info me-2"></i>No custom dates added yet.
                                                        </div>

                                                        <div id="customRecurringSummary" class="mt-3 pt-3 border-top"
                                                            style="display: none;">
                                                            <div class="d-flex justify-content-between">
                                                                <span><strong>Total scheduled invoices:</strong> <span
                                                                        id="customRecurringCount">0</span></span>
                                                                <span><strong>Total amount:</strong> $<span
                                                                        id="customRecurringTotal">0.00</span></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('invoices.index') }}"
                                    class="btn btn-light d-inline-flex align-items-center">
                                    <i class="fe fe-x me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary d-inline-flex align-items-center"
                                    id="createInvoiceBtn">
                                    <i class="fe fe-check me-1"></i> Create Invoice
                                </button>
                            </div>
                        </div>

                        <!-- Right Side - Summary -->
                        <div class="col-xl-4">
                            <div class="card sticky-top" style="top: 20px;">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">Invoice Summary</h5>

                                    <!-- Summary Details -->
                                    <div class="summary-details">
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="text-muted">Sub Amount:</span>
                                            <strong id="summarySubAmount">$0.00</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="text-muted">Already Paid:</span>
                                            <strong id="summaryAlreadyPaid">0</strong>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between mb-4">
                                            <span class="text-muted">Balance Due:</span>
                                            <h4 class="text-primary mb-0" id="summaryBalanceDue">$0.00</h4>
                                        </div>
                                    </div>

                                    <!-- Additional Info -->
                                    <div class="alert alert-info">
                                        <i class="fe fe-info me-2"></i>
                                        <small>Invoice will be sent to the tenant's email address after creation.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('backend/plugins/bootstrap-datepicker/js/datepicker.js') }}"></script>
    <script>
        $(document).ready(function() {
            let itemIndex = 1;
            let customRecurringIndex = 0;

            // Initialize Select2
            initializeSelect2();

            // Initialize Datepicker
            if ($('.datepicker2').length) {
                $('.datepicker2').datepicker({
                    format: 'yyyy-mm-dd',
                    autoclose: true,
                    todayHighlight: true,
                    startDate: new Date()
                });
            }

            // Tenant Selection Change
            $('#tenant_id').change(function() {
                const tenantId = $(this).val();
                if (tenantId) {
                    getTenantInfo(tenantId);
                } else {
                    $('#tenantInfoDisplay').hide();
                }
            });

            // Get Tenant Info
            function getTenantInfo(tenantId) {
                $.ajax({
                    url: `/admin/incomes/tenant/${tenantId}/info`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const tenant = response.tenant;
                            $('#tenantName').text(tenant.name);
                            $('#tenantEmail').text(tenant.email);
                            $('#tenantPhone').text(tenant.phone);
                            $('#tenantProperty').text(tenant.property);
                            $('#tenantInfoDisplay').slideDown();
                        }
                    },
                    error: function() {
                        toastr.error('Failed to fetch tenant information');
                    }
                });
            }

            // Item Selection Change
            $(document).on('change', '.item-select', function() {
                const row = $(this).closest('.invoice-item-row');
                const selectedOption = $(this).find('option:selected');
                const itemName = selectedOption.data('name');
                const itemPrice = selectedOption.data('price') || 0;

                row.find('.item-name').val(itemName);
                row.find('.item-rate').val(itemPrice);
                calculateRowAmount(row);
            });

            // Quantity/Rate Change
            $(document).on('input', '.item-quantity, .item-rate', function() {
                const row = $(this).closest('.invoice-item-row');
                calculateRowAmount(row);
            });

            // Calculate Row Amount
            function calculateRowAmount(row) {
                const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
                const rate = parseFloat(row.find('.item-rate').val()) || 0;
                const amount = quantity * rate;

                row.find('.item-amount').val(amount.toFixed(2));
                updateSummary();
            }

            // Update Summary
            function updateSummary() {
                let total = 0;
                $('.item-amount').each(function() {
                    total += parseFloat($(this).val()) || 0;
                });

                $('#summarySubAmount').text('$' + total.toFixed(2));
                $('#summaryBalanceDue').text('$' + total.toFixed(2));
            }

            // Add Item Row
            $('#addItemBtn').click(function() {
                const newRow = `
                    <tr class="invoice-item-row">
                        <td>
                            <select class="form-select form-select-sm item-select" name="items[${itemIndex}][item_id]" required>
                                <option value="">Select Invoice Type</option>
                                @foreach ($items as $item)
                                    <option value="{{ $item->id }}"
                                        data-name="{{ $item->name }}"
                                        data-price="{{ $item->price }}">
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" class="item-name" name="items[${itemIndex}][item_name]">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                name="items[${itemIndex}][description]" placeholder="Description">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm text-center item-quantity"
                                name="items[${itemIndex}][quantity]" value="1" min="1" required>
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm text-end item-rate"
                                name="items[${itemIndex}][rate]" value="0" min="0" step="0.01" required>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control text-end item-amount bg-light"
                                    value="0" readonly>
                                <button type="button" class="btn btn-outline-danger btn-sm remove-item">
                                    <i class="fe fe-x"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                $('#invoiceItemsBody').append(newRow);
                itemIndex++;
            });

            // Remove Item Row
            $(document).on('click', '.remove-item', function() {
                $(this).closest('.invoice-item-row').remove();
                updateSummary();
            });

            // ──────────────────────────────────────────────────────────
            // Recurring Invoice Logic
            // ──────────────────────────────────────────────────────────

            // Initialize datepickers function
            function initDatepickers(container = document) {
                $(container).find('.datepicker2:not(.initialized)').each(function() {
                    $(this).addClass('initialized')
                        .datepicker({
                            format: 'yyyy-mm-dd',
                            autoclose: true,
                            todayHighlight: true,
                            startDate: new Date(),
                            orientation: 'bottom auto'
                        });
                });
            }

            // Toggle recurring
            $('#is_recurring').on('change', function() {
                const isChecked = $(this).is(':checked');
                if (isChecked) {
                    $('#recurringFrequencyContainer').slideDown(200);

                    // Check if CUSTOM is already selected
                    if ($('#recurring_frequency').val() === 'CUSTOM') {
                        $('#customRecurringSection').slideDown(200);
                    }
                } else {
                    $('#recurringFrequencyContainer').slideUp(200);
                    $('#customRecurringSection').slideUp(200);
                }
            });

            // Frequency change
            $('#recurring_frequency').on('change', function() {
                const isCustom = $(this).val() === 'CUSTOM';
                if (isCustom) {
                    $('#customRecurringSection').slideDown(200, function() {
                        initDatepickers($('#customRecurringSection'));
                    });
                } else {
                    $('#customRecurringSection').slideUp(200);
                }
            });

            // Add new custom recurring row
            $('#addCustomRecurringBtn').on('click', function() {
                customRecurringIndex++;

                const html = `
                    <div class="custom-recurring-row mb-3 border p-1 bg-white shadow-sm" data-index="${customRecurringIndex}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Due Date *</label>
                                <input type="text" class="form-control form-control-sm datepicker2 custom-recurring-date"
                                       name="custom_recurring[${customRecurringIndex}][due_date]" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Amount *</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control custom-recurring-amount text-end"
                                           name="custom_recurring[${customRecurringIndex}][amount]"
                                           min="0.01" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Description (optional)</label>
                                <input type="text" class="form-control form-control-sm"
                                       name="custom_recurring[${customRecurringIndex}][description]"
                                       placeholder="e.g. Yearly service charge">
                            </div>
                            <div class="col-md-1 text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-custom-recurring">
                                    <i class="fe fe-trash-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                $('#customRecurringList').append(html);
                $('#noCustomRecurringAlert').hide();
                $('#customRecurringSummary').show();

                // Initialize datepicker on the new row
                initDatepickers($(`[data-index="${customRecurringIndex}"]`));

                // Live update summary when amount changes
                $(`[data-index="${customRecurringIndex}"] .custom-recurring-amount`).on('input',
                    updateCustomRecurringSummary);

                updateCustomRecurringSummary();
            });

            // Remove custom recurring row
            $(document).on('click', '.remove-custom-recurring', function() {
                $(this).closest('.custom-recurring-row').remove();
                if ($('.custom-recurring-row').length === 0) {
                    $('#noCustomRecurringAlert').show();
                    $('#customRecurringSummary').hide();
                }
                updateCustomRecurringSummary();
            });

            // Update custom recurring summary
            function updateCustomRecurringSummary() {
                let total = 0;
                let count = 0;

                $('.custom-recurring-row').each(function() {
                    const amt = Number($(this).find('.custom-recurring-amount').val()) || 0;
                    if (amt > 0) {
                        total += amt;
                        count++;
                    }
                });

                $('#customRecurringCount').text(count);
                $('#customRecurringTotal').text(total.toFixed(2));
            }

            // Form Submit
            $('#invoiceForm').submit(function(e) {
                e.preventDefault();

                // Validate tenant selection
                if (!$('#tenant_id').val()) {
                    toastr.error('Please select a tenant');
                    return;
                }

                // Validate at least one item
                if ($('.invoice-item-row').length === 0) {
                    toastr.error('Please add at least one item');
                    return;
                }

                // Validate custom recurring if selected
                if ($('#is_recurring').is(':checked') && $('#recurring_frequency').val() === 'CUSTOM') {
                    if ($('.custom-recurring-row').length === 0) {
                        toastr.error('Please add at least one custom recurring payment');
                        return;
                    }
                }

                const formData = $(this).serialize();
                $('#createInvoiceBtn').prop('disabled', true).html(
                    '<i class="fe fe-loader me-1"></i> Creating...');

                $.ajax({
                    url: '{{ route('invoices.store') }}',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            setTimeout(() => {
                                window.location.href = response.redirect_url;
                            }, 1000);
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Failed to create invoice';
                        toastr.error(message);
                        $('#createInvoiceBtn').prop('disabled', false).html(
                            '<i class="fe fe-check me-1"></i> Create Invoice');
                    }
                });
            });
        });

        function initializeSelect2() {
            if ($('.select3').length && typeof $.fn.select2 !== 'undefined') {
                $('.select3').select2({
                    placeholder: 'Select a tenant',
                    allowClear: true,
                    width: '100%'
                });
            }
        }
    </script>
@endpush

@push('styles')
    <style>
        .select2-container {
            width: 100% !important;
        }

        .sticky-top {
            position: sticky;
            top: 20px;
        }

        .invoice-item-row td {
            vertical-align: middle;
        }

        .table-bordered thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            font-size: 12px;
            color: #495057;
        }

        .summary-details {
            border-top: 1px solid #e9ecef;
            padding-top: 20px;
        }

        .form-check-input:checked {
            background-color: #D9A600;
            border-color: #D9A600;
        }

        /* Toggle (switch) bigger */
        .form-check-input {
            transform: scale(1.5);
            cursor: pointer;
        }

        /* Label text bigger */
        .form-check-label {
            font-size: 14px;
            line-height: 1.4;
        }

        /* Sub text */
        .form-check-label .text-muted {
            font-size: 12px;
        }
    </style>
@endpush
