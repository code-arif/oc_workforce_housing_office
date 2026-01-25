{{-- resources/views/backend/layouts/invoices/create.blade.php --}}
@extends('backend.app')

@section('title', 'Create New Invoice')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header mb-4">
                    <div>
                        <h1 class="page-title">CREATE NEW INVOICE</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
                            <li class="breadcrumb-item active">Create Invoice</li>
                        </ol>
                    </div>
                </div>

                <form id="invoiceForm">
                    @csrf
                    <div class="row">
                        <!-- Left Side - Form -->
                        <div class="col-xl-8">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Property Details</h5>
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
                                                        {{ $tenant->profile->first_name }} {{ $tenant->profile->last_name }}
                                                        ({{ $tenant->email }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Term</label>
                                            <input type="text" class="form-control" id="tenantTerm" readonly
                                                placeholder="N/A">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Unit</label>
                                            <input type="text" class="form-control" id="tenantUnit" readonly
                                                placeholder="N/A">
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

                                    <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                                        <i class="fe fe-plus me-1"></i> Add Item Type
                                    </button>

                                    <!-- Internal Notes -->
                                    <div class="mt-4">
                                        <label class="form-label">Internal Notes</label>
                                        <textarea class="form-control" name="notes" rows="3" placeholder="Add internal notes here"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Due Date -->
                            <div class="card mt-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Due Date <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control datepicker2" id="due_date"
                                                name="due_date" required>
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

                                    <!-- Recurring Toggle -->
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_recurring"
                                                    name="is_recurring" value="1">
                                                <label class="form-check-label" for="is_recurring">
                                                    <strong>Make this a recurring invoice</strong>
                                                    <div class="text-muted small">Invoice will be automatically generated
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6" id="recurringFrequencyContainer" style="display: none;">
                                            <label class="form-label">Recurring Frequency</label>
                                            <select class="form-select" id="recurring_frequency"
                                                name="recurring_frequency">
                                                <option value="WEEKLY">Weekly</option>
                                                <option value="MONTHLY" selected>Monthly</option>
                                                <option value="YEARLY">Yearly</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('invoices.index') }}" class="btn btn-light">
                                    <i class="fe fe-x me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary" id="createInvoiceBtn">
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
                    url: `/admin/invoices/tenant/${tenantId}/info`,
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

            // Recurring Toggle
            $('#is_recurring').change(function() {
                if ($(this).is(':checked')) {
                    $('#recurringFrequencyContainer').slideDown();
                } else {
                    $('#recurringFrequencyContainer').slideUp();
                }
            });

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
                    placeholder: 'Select an option',
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
    </style>
@endpush
