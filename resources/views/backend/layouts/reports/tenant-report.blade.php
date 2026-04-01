@extends('backend.app')

@section('title', 'Tenant Report')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Tenant Report</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="#">Reports</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Tenant Report</li>
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

                <!-- Filter Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-title mb-0"><i class="fe fe-filter me-2"></i>Filter Report</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="filterStatus" class="form-label">Status</label>
                                <select class="form-select" id="filterStatus" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="under_review">Under Review</option>
                                    <option value="approved">Approved</option>
                                    <option value="active">Active</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="filterProperty" class="form-label">Property</label>
                                <select class="form-select select3" id="filterProperty" name="property_id">
                                    <option value="">All Properties</option>
                                    @foreach($properties as $property)
                                        <option value="{{ $property->id }}">{{ $property->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="filterSource" class="form-label">Application Source</label>
                                <select class="form-select" id="filterSource" name="application_source">
                                    <option value="">All Sources</option>
                                    <option value="self">Self Applied</option>
                                    <option value="admin">Admin Created</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="filterDateFrom" class="form-label">Move-in From</label>
                                <input type="text" class="form-control datepicker2" id="filterDateFrom" name="date_from" placeholder="From date...">
                            </div>
                            <div class="col-md-2">
                                <label for="filterDateTo" class="form-label">Move-in To</label>
                                <input type="text" class="form-control datepicker2" id="filterDateTo" name="date_to" placeholder="To date...">
                            </div>
                            <div class="col-md-2 d-flex align-items-end gap-2">
                                <button type="button" class="btn btn-outline-secondary" id="resetFilters">
                                    <i class="fe fe-refresh-cw me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4" id="summaryCards">
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1">Total Tenants</p>
                                        <h3 class="mb-0" id="summaryTotalTenants">0</h3>
                                    </div>
                                    <div class="ms-3">
                                        <div class="icon-service bg-primary-transparent text-primary">
                                            <i class="fe fe-users"></i>
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
                                        <p class="text-muted mb-1">Active Tenants</p>
                                        <h3 class="mb-0 text-success" id="summaryActiveTenants">0</h3>
                                    </div>
                                    <div class="ms-3">
                                        <div class="icon-service bg-success-transparent text-success">
                                            <i class="fe fe-user-check"></i>
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
                                        <p class="text-muted mb-1">Approved Tenants</p>
                                        <h3 class="mb-0 text-info" id="summaryApprovedTenants">0</h3>
                                    </div>
                                    <div class="ms-3">
                                        <div class="icon-service bg-info-transparent text-info">
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
                                        <p class="text-muted mb-1">Pending Tenants</p>
                                        <h3 class="mb-0 text-warning" id="summaryPendingTenants">0</h3>
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
                </div>

                <!-- Report Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0"><i class="fe fe-list me-2"></i>Tenant Report Data</h4>
                        <span class="text-muted small" id="lastUpdated"></span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tenantReportTable" style="width: 100%">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tenant Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Property</th>
                                        <th>Unit</th>
                                        <th>Move-in Date</th>
                                        <th>Lease Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
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

        // Track counts
        let totalTenants = 0;
        let activeTenants = 0;
        let approvedTenants = 0;
        let pendingTenants = 0;

        // Initialize DataTable
        var table = $('#tenantReportTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('reports.tenant.data') }}",
                data: function(d) {
                    d.status = $('#filterStatus').val();
                    d.property_id = $('#filterProperty').val();
                    d.application_source = $('#filterSource').val();
                    d.date_from = $('#filterDateFrom').val();
                    d.date_to = $('#filterDateTo').val();
                }
            },
            columns: [
                { data: 'tenant_name', name: 'tenant_name' },
                { data: 'email', name: 'email' },
                { data: 'phone', name: 'phone' },
                { data: 'status_badge', name: 'status' },
                { data: 'property_name', name: 'property_name' },
                { data: 'unit', name: 'unit' },
                { data: 'move_in_date', name: 'move_in_date' },
                { data: 'lease_status', name: 'lease_status' }
            ],
            order: [[0, 'asc']],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
            drawCallback: function(settings) {
                calculateSummary();
                updateLastUpdated();
            }
        });

        function calculateSummary() {
            totalTenants = 0;
            activeTenants = 0;
            approvedTenants = 0;
            pendingTenants = 0;

            table.rows({ search: 'applied' }).every(function() {
                totalTenants++;
                var data = this.data();
                var status = data.status_badge.toLowerCase();
                
                if (status.includes('active')) {
                    activeTenants++;
                } else if (status.includes('approved')) {
                    approvedTenants++;
                } else if (status.includes('pending') || status.includes('processing') || status.includes('under review')) {
                    pendingTenants++;
                }
            });

            // Update summary cards
            $('#summaryTotalTenants').text(totalTenants);
            $('#summaryActiveTenants').text(activeTenants);
            $('#summaryApprovedTenants').text(approvedTenants);
            $('#summaryPendingTenants').text(pendingTenants);
        }

        function updateLastUpdated() {
            const now = new Date();
            $('#lastUpdated').text('Last updated: ' + now.toLocaleTimeString());
        }

        function getFilterParams() {
            let params = new URLSearchParams();
            if ($('#filterStatus').val()) params.append('status', $('#filterStatus').val());
            if ($('#filterProperty').val()) params.append('property_id', $('#filterProperty').val());
            if ($('#filterSource').val()) params.append('application_source', $('#filterSource').val());
            if ($('#filterDateFrom').val()) params.append('date_from', $('#filterDateFrom').val());
            if ($('#filterDateTo').val()) params.append('date_to', $('#filterDateTo').val());
            return params.toString();
        }

        // Apply Filters on change
        $('#filterStatus, #filterProperty, #filterSource, #filterDateFrom, #filterDateTo').on('change', function() {
            table.ajax.reload();
        });

        // Reset Filters
        $('#resetFilters').on('click', function() {
            $('#filterStatus').val('');
            $('#filterProperty').val('').trigger('change');
            $('#filterSource').val('');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            table.ajax.reload();
        });

        // Export PDF
        $('#exportPdfBtn').on('click', function(e) {
            e.preventDefault();
            const params = getFilterParams();
            const url = "{{ route('reports.tenant.export.pdf') }}" + (params ? '?' + params : '');
            window.location.href = url;
        });

        // Export Excel
        $('#exportExcelBtn').on('click', function(e) {
            e.preventDefault();
            const params = getFilterParams();
            const url = "{{ route('reports.tenant.export.excel') }}" + (params ? '?' + params : '');
            window.location.href = url;
        });

        // Enter key trigger for date inputs
        $('#filterDateFrom, #filterDateTo').on('keypress', function(e) {
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
    .icon-service {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
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
