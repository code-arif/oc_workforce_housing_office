@extends('backend.app')

@section('title', 'Rent Report')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Rent Report</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="#">Reports</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Rent Report</li>
                        </ol>
                    </div>
                    <div class="ms-auto pageheader-btn">
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

                <!-- Filter Card -->
                {{-- <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-title mb-0"><i class="fe fe-filter me-2"></i>Filter Report</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="filterProperty" class="form-label">Property</label>
                                <select class="form-select select3" id="filterProperty" name="property_id">
                                    <option value="">All Properties</option>
                                    @foreach ($properties as $property)
                                    <option value="{{ $property->id }}">{{ $property->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Bed</label>
                                <select class="form-select select3" id="bedFilter">
                                    <option value="">All Beds</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tenant</label>
                                <select class="form-select select3" id="tenantFilter">
                                    <option value="">Select Tenant</option>
                                    @foreach ($tenants as $tenant)
                                    <option value="{{ $tenant->id }}">{{ $tenant->profile->first_name }}
                                        {{ $tenant->profile->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="filterDateFrom" class="form-label">Due Date From</label>
                                <input type="text" class="form-control datepicker2" id="filterDateFrom" name="date_from"
                                    placeholder="Search by due date...">
                            </div>
                            <div class="col-md-2">
                                <label for="filterDateTo" class="form-label">Due Date To</label>
                                <input type="text" class="form-control datepicker2" id="filterDateTo" name="date_to"
                                    placeholder="Search by due date...">
                            </div>
                            <div class="col-md-2 d-flex align-items-end gap-2">
                                <button type="button" class="btn btn-outline-secondary" id="resetFilters">
                                    <i class="fe fe-refresh-cw me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div> --}}

                <!-- Summary Cards -->
                {{-- <div class="row mb-4" id="summaryCards">
                    <div class="col-xl-4 col-lg-6 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1">Total Invoices</p>
                                        <h3 class="mb-0" id="summaryTotalInvoices">0</h3>
                                    </div>
                                    <div class="ms-3">
                                        <div class="icon-service bg-primary-transparent text-primary">
                                            <i class="fe fe-file-text"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1">Total Outstanding</p>
                                        <h3 class="mb-0 text-danger" id="summaryTotalOutstanding">$0.00</h3>
                                    </div>
                                    <div class="ms-3">
                                        <div class="icon-service bg-danger-transparent text-danger">
                                            <i class="fe fe-alert-circle"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}

                <!-- Combined Filter + Summary Row -->
                <div class="row mb-3">
                    <!-- Left: Filters -->
                    <div class="col-lg-8 col-xl-9 mb-3 mb-lg-0">
                        <div class="card h-100 mb-0">
                            <div class="card-body py-2 px-3">
                                <div class="row g-2 align-items-end">
                                    <div class="col-sm-6 col-md-4 col-xl">
                                        <label for="filterProperty" class="form-label mb-1 small">Property</label>
                                        <select class="form-select form-select-sm select3" id="filterProperty"
                                            name="property_id">
                                            <option value="">All Properties</option>
                                            @foreach ($properties as $property)
                                                <option value="{{ $property->id }}">{{ $property->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-xl">
                                        <label class="form-label mb-1 small">Bed</label>
                                        <select class="form-select form-select-sm select3" id="bedFilter">
                                            <option value="">All Beds</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-xl">
                                        <label class="form-label mb-1 small">Tenant</label>
                                        <select class="form-select form-select-sm select3" id="tenantFilter">
                                            <option value="">Select Tenant</option>
                                            @foreach ($tenants as $tenant)
                                                <option value="{{ $tenant->id }}">{{ $tenant?->profile?->first_name }}
                                                    {{ $tenant?->profile?->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-xl">
                                        <label for="filterDateFrom" class="form-label mb-1 small">Due Date From</label>
                                        <input type="text" class="form-control form-control-sm datepicker2"
                                            id="filterDateFrom" name="date_from" placeholder="Start date...">
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-xl">
                                        <label for="filterDateTo" class="form-label mb-1 small">Due Date To</label>
                                        <input type="text" class="form-control form-control-sm datepicker2"
                                            id="filterDateTo" name="date_to" placeholder="End date...">
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
                    </div>

                    <!-- Right: Summary Cards -->
                    <div class="col-lg-4 col-xl-3" id="summaryCards">
                        <div class="card h-100 mb-0">
                            <div class="card-body py-2 px-3 d-flex flex-column justify-content-center gap-2">
                                <div class="d-flex align-items-center gap-2 p-2 border rounded-3">
                                    <div class="icon-service-sm bg-primary-transparent text-primary flex-shrink-0">
                                        <i class="fe fe-file-text"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted mb-0 small">Total Invoices</p>
                                        <h6 class="mb-0 fw-bold" id="summaryTotalInvoices">0</h6>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 p-2 border rounded-3">
                                    <div class="icon-service-sm bg-danger-transparent text-danger flex-shrink-0">
                                        <i class="fe fe-alert-circle"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted mb-0 small">Total Outstanding</p>
                                        <h6 class="mb-0 fw-bold text-danger" id="summaryTotalOutstanding">$0.00</h6>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 p-2 border rounded-3">
                                    <div class="icon-service-sm bg-primary-transparent text-primary flex-shrink-0">
                                        <i class="fe fe-credit-card"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted mb-0 small">Paid via Stripe</p>
                                        <h6 class="mb-0 fw-bold" id="summaryStripeAmount">$0.00</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0"><i class="fe fe-list me-2"></i>Rent Report Data</h4>
                        <span class="text-muted small" id="lastUpdated"></span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="rentReportTable" style="width: 100%">
                                <thead class="table-light">
                                    <tr>
                                        <th>Property Name</th>
                                        <th>Beds</th>
                                        <th>Tenant</th>
                                        <th>Due Date</th>
                                        <th class="text-end">Outstanding Amount ($)</th>
                                        <th>Invoice No.</th>
                                        <th>Payment Method</th>
                                        <th>Stripe Amount</th>
                                        <th>Status</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <th colspan="4" class="text-end">Total Outstanding:</th>
                                        <th class="text-end" id="footerTotalOutstanding">$0.00</th>
                                        <th colspan="5"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('backend/plugins/bootstrap-datepicker/js/datepicker.js') }}"></script>
    <script>
        $(document).ready(function () {
            // Initialize Select2
            $('.select3').select2({
                placeholder: 'Select Option',
                allowClear: true
            });

            // Initialize Datepicker
            $('#filterDateFrom, #filterDateTo').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true
            });

            // Track totals
            let totalOutstanding = 0;

            // Initialize DataTable
            var table = $('#rentReportTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('reports.rent.data') }}",
                    data: function (d) {
                        d.property_id = $('#filterProperty').val();
                        d.bed_id = $('#bedFilter').val();
                        d.tenant_id = $('#tenantFilter').val();
                        d.date_from = $('#filterDateFrom').val();
                        d.date_to = $('#filterDateTo').val();
                    }
                },
                columns: [{
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
                    data: 'due_date',
                    name: 'due_date'
                },
                {
                    data: 'outstanding_amount',
                    name: 'outstanding_amount',
                    className: 'text-end'
                },
                {
                    data: 'invoice_number',
                    name: 'invoice_number'
                },
                {
                    data: 'stripe_method',
                    name: 'stripe_payment_method',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'stripe_amount',
                    name: 'stripe_exact_amount'
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'notes',
                    name: 'notes'
                }
                ],
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                drawCallback: function (settings) {
                    // Calculate totals from current page data
                    calculateTotals();
                    updateLastUpdated();
                }
            });

            function calculateTotals() {
                totalOutstanding = 0;
                let totalStripeAmount = 0;

                table.rows({
                    search: 'applied'
                }).every(function () {
                    var data = this.data();
                    totalOutstanding += parseFloat(data.outstanding_amount.replace(/,/g, '')) || 0;
                    totalStripeAmount += parseFloat(data.raw_stripe_amount) || 0;
                });

                // Update footer
                $('#footerTotalOutstanding').text('$' + numberFormat(totalOutstanding));

                // Update summary cards
                $('#summaryTotalInvoices').text(table.rows({
                    search: 'applied'
                }).count());
                $('#summaryTotalOutstanding').text('$' + numberFormat(totalOutstanding));
                $('#summaryStripeAmount').text('$' + numberFormat(totalStripeAmount));
            }

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

            function getFilterParams() {
                let params = new URLSearchParams();
                if ($('#filterProperty').val()) params.append('property_id', $('#filterProperty').val());
                if ($('#bedFilter').val()) params.append('bed_id', $('#bedFilter').val());
                if ($('#tenantFilter').val()) params.append('tenant_id', $('#tenantFilter').val());
                if ($('#filterDateFrom').val()) params.append('date_from', $('#filterDateFrom').val());
                if ($('#filterDateTo').val()) params.append('date_to', $('#filterDateTo').val());
                return params.toString();
            }

            // Apply Filters on change
            $('#filterProperty, #bedFilter, #tenantFilter, #filterDateFrom, #filterDateTo').on('change',
                function () {
                    table.ajax.reload();
                });
            $('#filterDateFrom, #filterDateTo').on('keyup', function () {
                table.ajax.reload();
            });

            // Reset Filters
            $('#resetFilters').on('click', function () {
                $('#filterProperty').val('').trigger('change');
                $('#bedFilter').val('').trigger('change');
                $('#tenantFilter').val('').trigger('change');
                $('#filterDateFrom').val('');
                $('#filterDateTo').val('');

                table.ajax.reload();
            });

            $('#filterProperty').change(function () {
                const propertyId = $(this).val();
                $('#bedFilter').empty().append('<option value="">All Beds</option>');
                if (propertyId) {
                    $.ajax({
                        url: '{{ url('admin/leases/property') }}/' + propertyId + '/beds',
                        type: 'GET',
                        success: function (response) {
                            console.log(response);

                            response.data.forEach(function (bed) {
                                $('#bedFilter').append(
                                    `<option value="${bed.id}">${bed.bed_label}</option>`
                                );
                            });
                            $('#bedFilter').val(null).trigger('change');
                        },
                        error: function () {
                            toastr.error('Failed to fetch beds for the selected property.');
                        }
                    });
                }
            });

            // Export PDF
            $('#exportPdfBtn').on('click', function (e) {
                e.preventDefault();
                const params = getFilterParams();
                const url = "{{ route('reports.rent.export.pdf') }}" + (params ? '?' + params : '');
                window.location.href = url;
            });

            // Export Excel
            $('#exportExcelBtn').on('click', function (e) {
                e.preventDefault();
                const params = getFilterParams();
                const url = "{{ route('reports.rent.export.excel') }}" + (params ? '?' + params : '');
                window.location.href = url;
            });

            // Enter key trigger for date inputs
            $('#filterDateFrom, #filterDateTo').on('keypress', function (e) {
                if (e.which === 13) {
                    table.ajax.reload();
                }
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
            width: 34px;
            height: 34px;
            min-width: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        #rentReportTable tfoot th {
            font-weight: 600;
        }

        .card-title i {
            opacity: 0.7;
        }

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
