@extends('backend.app')

@section('title', 'Property Report')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Property Report</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="#">Reports</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Property Report</li>
                        </ol>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fe fe-download me-2"></i> Export Report
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" id="exportPdfBtn"><i class="fe fe-file-text me-2 text-danger"></i> Export as PDF</a></li>
                                <li><a class="dropdown-item" href="#" id="exportExcelBtn"><i class="fe fe-file me-2 text-success"></i> Export as Excel</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4" id="summaryCards">
                    <div class="col-xl col-lg-4 col-md-6 mb-3 mb-xl-0">
                        <div class="card overflow-hidden h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted fw-semibold mb-1">Total Leases</p>
                                        <h3 class="mb-0 fw-bold" id="summaryTotalLeases">0</h3>
                                    </div>
                                    <div class="icon-service bg-primary-transparent text-primary rounded-circle p-3">
                                        <i class="fe fe-file-text fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-lg-4 col-md-6 mb-3 mb-xl-0">
                        <div class="card overflow-hidden h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted fw-semibold mb-1">Security Deposit</p>
                                        <h3 class="mb-0 fw-bold text-warning" id="summarySecurityDeposit">$0.00</h3>
                                    </div>
                                    <div class="icon-service bg-warning-transparent text-warning rounded-circle p-3">
                                        <i class="fe fe-shield fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-lg-4 col-md-6 mb-3 mb-xl-0">
                        <div class="card overflow-hidden h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted fw-semibold mb-1">Total Due</p>
                                        <h3 class="mb-0 fw-bold text-info" id="summaryTotalDue">$0.00</h3>
                                    </div>
                                    <div class="icon-service bg-info-transparent text-info rounded-circle p-3">
                                        <i class="fe fe-dollar-sign fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-lg-6 col-md-6 mb-3 mb-xl-0">
                        <div class="card overflow-hidden h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted fw-semibold mb-1">Total Paid</p>
                                        <h3 class="mb-0 fw-bold text-success" id="summaryTotalPaid">$0.00</h3>
                                    </div>
                                    <div class="icon-service bg-success-transparent text-success rounded-circle p-3">
                                        <i class="fe fe-check-circle fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-lg-6 col-md-6 mb-3 mb-xl-0">
                        <div class="card overflow-hidden h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted fw-semibold mb-1">Balance Owed</p>
                                        <h3 class="mb-0 fw-bold text-danger" id="summaryBalance">$0.00</h3>
                                    </div>
                                    <div class="icon-service bg-danger-transparent text-danger rounded-circle p-3">
                                        <i class="fe fe-alert-circle fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Card -->
                <div class="card mb-3 border-0 shadow-sm">
                    <div class="card-body py-3">
                        <h4 class="card-title fw-semibold mb-3 fs-15"><i class="fe fe-filter me-2 text-primary"></i>Filter Report</h4>
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md">
                                <label for="filterProperty" class="form-label fw-semibold text-muted mb-1 fs-12">Property</label>
                                <select class="form-select form-select-sm select3" id="filterProperty" name="property_id">
                                    <option value="">All Properties</option>
                                    @foreach($properties as $property)
                                        <option value="{{ $property->id }}">{{ $property->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md">
                                <label class="form-label fw-semibold text-muted mb-1 fs-12">Unit</label>
                                <select class="form-select form-select-sm select3" id="bedFilter">
                                    <option value="">All Beds</option>
                                </select>
                            </div>
                            <div class="col-12 col-md">
                                <label class="form-label fw-semibold text-muted mb-1 fs-12">Tenant</label>
                                <select class="form-select form-select-sm select3" id="tenantFilter">
                                    <option value="">Select Tenant</option>
                                    @foreach ($tenants as $tenant)
                                        <option value="{{ $tenant->id }}">{{ $tenant->profile->first_name }} {{ $tenant->profile->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md">
                                <label for="filterSeason" class="form-label fw-semibold text-muted mb-1 fs-12">Season</label>
                                <select class="form-select form-select-sm select3" id="filterSeason" name="season">
                                    <option value="">Select Season</option>
                                    @foreach ($seasons as $season)
                                    <option value="{{ $season->id }}">{{ $season->name }}</option>
                                    @endforeach
                                    <option value="custom">Custom Dates</option>
                                </select>
                            </div>

                            <!-- Custom Dates Row -->
                            <div class="col-12 col-md customDateContainer" style="display: none;">
                                <label for="filterDateFrom" class="form-label fw-semibold text-muted mb-1 fs-12">Date From</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="fe fe-calendar text-muted"></i></span>
                                    <input type="text" class="form-control datepicker2 border-start-0 ps-0" id="filterDateFrom" name="date_from" placeholder="Start date...">
                                </div>
                            </div>
                            <div class="col-12 col-md customDateContainer" style="display: none;">
                                <label for="filterDateTo" class="form-label fw-semibold text-muted mb-1 fs-12">Date To</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="fe fe-calendar text-muted"></i></span>
                                    <input type="text" class="form-control datepicker2 border-start-0 ps-0" id="filterDateTo" name="date_to" placeholder="End date...">
                                </div>
                            </div>

                            <div class="col-12 col-md-auto ms-xl-auto mt-3 mt-md-0 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary px-3 shadow-sm w-100" id="resetFilters">
                                    <i class="fe fe-refresh-cw me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0"><i class="fe fe-list me-2"></i>Property Report Data</h4>
                        <span class="text-muted small" id="lastUpdated"></span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="propertyReportTable" style="width: 100%">
                                <thead class="table-light">
                                    <tr>
                                        <th>Property Name</th>
                                        <th>Unit</th>
                                        <th>Tenant Name</th>
                                        <th class="text-end">Security Deposit ($)</th>
                                        <th class="text-end">Total Due ($)</th>
                                        <th class="text-end">Total Paid ($)</th>
                                        <th class="text-end">Balance Owed ($)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <th colspan="3" class="text-end">Totals:</th>
                                        <th class="text-end" id="footerSecurityDeposit">$0.00</th>
                                        <th class="text-end" id="footerTotalDue">$0.00</th>
                                        <th class="text-end" id="footerTotalPaid">$0.00</th>
                                        <th class="text-end" id="footerBalance">$0.00</th>
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
<script src="{{asset('backend/plugins/bootstrap-datepicker/js/datepicker.js')}}"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select3').select2({
            placeholder: 'Select an option',
            allowClear: true
        });

        // Initialize Datepicker
        $('#filterDateFrom, #filterDateTo').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });

        // Track totals
        let totalDue = 0;
        let totalPaid = 0;
        let totalBalance = 0;
        let totalSecurityDeposit = 0;

        // Initialize DataTable
        var table = $('#propertyReportTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('reports.property.data') }}",
                data: function(d) {
                    d.property_id = $('#filterProperty').val();
                    d.bed_id = $('#bedFilter').val();
                    d.tenant_id = $('#tenantFilter').val();
                    d.status = $('#filterStatus').val();
                    d.season = $('#filterSeason').val();
                    d.date_from = $('#filterDateFrom').val();
                    d.date_to = $('#filterDateTo').val();
                }
            },
            columns: [
                { data: 'property_name', name: 'property_name' },
                { data: 'bed_label', name: 'bed_label' },
                { data: 'tenant_name', name: 'tenant_name' },
                { data: 'security_deposit', name: 'security_deposit', className: 'text-end' },
                { data: 'total_due', name: 'total_due', className: 'text-end' },
                { data: 'total_paid', name: 'total_paid', className: 'text-end' },
                { data: 'balance_owed', name: 'balance_owed', className: 'text-end' }
            ],
            order: [[0, 'asc']],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
            drawCallback: function(settings) {
                // Calculate totals from current page data
                calculateTotals();
                updateLastUpdated();
            }
        });

        function calculateTotals() {
            totalDue = 0;
            totalPaid = 0;
            totalBalance = 0;
            totalSecurityDeposit = 0;

            table.rows({ search: 'applied' }).every(function() {
                var data = this.data();
                totalSecurityDeposit += parseFloat(data.security_deposit.replace(/,/g, '')) || 0;
                totalDue += parseFloat(data.total_due.replace(/,/g, '')) || 0;
                totalPaid += parseFloat(data.total_paid.replace(/,/g, '')) || 0;
                totalBalance += parseFloat(data.balance_owed.replace(/,/g, '')) || 0;
            });

            // Update footer
            $('#footerSecurityDeposit').text('$' + numberFormat(totalSecurityDeposit));
            $('#footerTotalDue').text('$' + numberFormat(totalDue));
            $('#footerTotalPaid').text('$' + numberFormat(totalPaid));
            $('#footerBalance').text('$' + numberFormat(totalBalance));

            // Update summary cards
            $('#summaryTotalLeases').text(table.rows({ search: 'applied' }).count());
            $('#summarySecurityDeposit').text('$' + numberFormat(totalSecurityDeposit));
            $('#summaryTotalDue').text('$' + numberFormat(totalDue));
            $('#summaryTotalPaid').text('$' + numberFormat(totalPaid));
            $('#summaryBalance').text('$' + numberFormat(totalBalance));
        }

        function numberFormat(num) {
            return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
            if ($('#filterStatus').val()) params.append('status', $('#filterStatus').val());
            if ($('#filterDateFrom').val()) params.append('date_from', $('#filterDateFrom').val());
            if ($('#filterDateTo').val()) params.append('date_to', $('#filterDateTo').val());
            return params.toString();
        }

        // Apply Filters
        $('#applyFilters').on('click', function() {
            table.ajax.reload();
        });
        $('#filterProperty,#bedFilter,#tenantFilter, #filterStatus, #filterDateFrom, #filterDateTo').on('change', function() {
            table.ajax.reload();
        });
        $('#filterDateFrom, #filterDateTo').on('keyup', function() {
            table.ajax.reload();
        });

        // Reset Filters
        $('#resetFilters').on('click', function() {
            $('#filterProperty').val('').trigger('change');
            $('#bedFilter').val('').trigger('change');
            $('#tenantFilter').val('').trigger('change');
            $('#filterStatus').val('');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');

            table.ajax.reload();
        });

        $('#filterProperty').change(function() {
            const propertyId = $(this).val();
            $('#bedFilter').empty().append('<option value="">Select Units</option>');
            if (propertyId) {
                $.ajax({
                    url: '{{ url('admin/leases/property') }}/' + propertyId + '/beds',
                    type: 'GET',
                    success: function(response) {
                        console.log(response);

                        response.data.forEach(function(bed) {
                            $('#bedFilter').append(
                                `<option value="${bed.id}">${bed.bed_label}</option>`
                            );
                        });
                        $('#bedFilter').val(null).trigger('change');
                    },
                    error: function() {
                        toastr.error('Failed to fetch beds for the selected property.');
                    }
                });
            }
        });

        // Export PDF
        $('#exportPdfBtn').on('click', function(e) {
            e.preventDefault();
            const params = getFilterParams();
            const url = "{{ route('reports.property.export.pdf') }}" + (params ? '?' + params : '');
            window.location.href = url;
        });

        // Export Excel
        $('#exportExcelBtn').on('click', function(e) {
            e.preventDefault();
            const params = getFilterParams();
            const url = "{{ route('reports.property.export.excel') }}" + (params ? '?' + params : '');
            window.location.href = url;
        });

        // Enter key trigger for date inputs
        $('#filterDateFrom, #filterDateTo').on('keypress', function(e) {
            if (e.which === 13) {
                table.ajax.reload();
            }
        });

        $('#filterSeason').on('change', function() {
            if ($(this).val() === 'custom') {
                $('.customDateContainer').show();
            } else {
                $('.customDateContainer').hide();
                $('#filterDateFrom').val('');
                $('#filterDateTo').val('');
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
    .icon-service {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    #propertyReportTable tfoot th {
        font-weight: 600;
    }

    .card-title i {
        opacity: 0.7;
    }

    .filter-card .form-label {
        font-weight: 500;
        font-size: 13px;
        color: #6c757d;
    }
</style>
@endpush
