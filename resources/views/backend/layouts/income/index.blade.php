@extends('backend.app')

@section('title', 'Invoices')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Income</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Invoices</li>
                        </ol>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4 g-3">
                    <div class="col-md-2">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <h3 class="mb-1 text-primary">${{ number_format($stats['total'], 2) }}</h3>
                                <p class="text-muted mb-0 small">TOTAL INVOICE AMOUNT</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <h3 class="mb-1 text-danger">${{ number_format($stats['overdue'], 2) }}</h3>
                                <p class="text-muted mb-0 small">OVERDUE</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <h3 class="mb-1 text-warning">${{ number_format($stats['unpaid'], 2) }}</h3>
                                <p class="text-muted mb-0 small">UNPAID</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <h3 class="mb-1 text-info">${{ number_format($stats['partial'], 2) }}</h3>
                                <p class="text-muted mb-0 small">PARTIALLY PAID</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <h3 class="mb-1 text-success">${{ number_format($stats['paid'], 2) }}</h3>
                                <p class="text-muted mb-0 small">FULLY PAID</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <h3 class="mb-1">${{ number_format($stats['processing'], 2) }}</h3>
                                <p class="text-muted mb-0 small">PROCESSING</p>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Main Content Row -->
                <div class="row">
                    <!-- Left Side - Invoice List -->
                    <div class="col-xl-9">
                        <!-- Filters -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <input type="text" class="form-control form-control-sm" id="searchFilter"
                                            placeholder="Search...">
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select select5" id="propertyFilter">
                                            <option value="">All Properties</option>
                                            @foreach ($properties as $property)
                                                <option value="{{ $property->id }}">{{ $property->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select select3" id="tenantFilter">
                                            <option value="">All Tenants</option>
                                            @foreach ($tenants as $tenant)
                                                <option value="{{ $tenant->id }}">
                                                    {{ $tenant?->profile?->first_name || $tenant?->profile?->last_name
                                                        ? trim(($tenant?->profile?->first_name ?? '') . ' ' . ($tenant?->profile?->last_name ?? ''))
                                                        : $tenant->email }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-select form-control" id="statusFilter">
                                            <option value="">All Statuses</option>
                                            <option value="UNPAID">Unpaid</option>
                                            <option value="PARTIAL">Partial</option>
                                            <option value="PAID">Paid</option>
                                            <option value="OVERDUE">Overdue</option>
                                            <option value="CANCELLED">Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-secondary w-100" onclick="resetFilters()">
                                            <i class="fe fe-refresh-cw"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Table -->
                        <div class="card">
                            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted" id="showingInfo">Showing invoices</span>
                                </div>
                                <div>
                                    <button class="btn btn-light btn-sm me-2 d-inline-flex align-items-center">
                                        <i class="fe fe-download me-1"></i> Export
                                    </button>
                                    <a href="{{ route('invoices.create') }}"
                                        class="btn btn-primary btn-sm d-inline-flex align-items-center">
                                        <i class="fe fe-plus me-1"></i> New Invoice
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="invoicesTable">
                                        <thead>
                                            <tr>
                                                <th>Invoice</th>
                                                <th>Tenant</th>
                                                <th>Property</th>
                                                <th>Due Date</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Summary Chart -->
                    <div class="col-xl-3">
                        <div class="card sticky-top" style="top: 20px;">
                            <div class="card-header">
                                <h6 class="mb-0">Invoice Summary</h6>
                                <div class="d-flex align-items-center">
                                    <span class="text-muted small me-3">JAN 1, 26 – JAN 31, 26</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Total Amount -->
                                <div class="text-center mb-4">
                                    <h2 class="mb-1" id="chartTotalAmount">${{ number_format($stats['total'], 2) }}</h2>
                                    <p class="text-muted small mb-0">TOTAL INVOICE AMOUNT</p>
                                </div>

                                <!-- Donut Chart -->
                                <div class="d-flex justify-content-center mb-4">
                                    <div style="width: 200px; height: 200px;">
                                        <canvas id="invoiceChart"></canvas>
                                    </div>
                                </div>

                                <!-- Stats Breakdown -->
                                <div class="stats-breakdown">
                                    <div class="stat-item mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <div class="color-indicator bg-warning me-2"></div>
                                                <span class="text-muted">TOTAL UNPAID INVOICE</span>
                                            </div>
                                        </div>
                                        <h4 class="mb-0 ms-4">${{ number_format($stats['unpaid'], 2) }}</h4>
                                    </div>

                                    <div class="stat-item mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <div class="color-indicator bg-danger me-2"></div>
                                                <span class="text-muted">OVERDUE</span>
                                            </div>
                                        </div>
                                        <h4 class="mb-0 ms-4 text-danger">${{ number_format($stats['overdue'], 2) }}</h4>
                                    </div>

                                    <div class="stat-item mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <div class="color-indicator bg-secondary me-2"></div>
                                                <span class="text-muted">PROCESSING</span>
                                            </div>
                                        </div>
                                        <h4 class="mb-0 ms-4">${{ number_format($stats['processing'], 2) }}</h4>
                                    </div>

                                    <div class="stat-item mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <div class="color-indicator bg-info me-2"></div>
                                                <span class="text-muted">PARTIALLY PAID</span>
                                            </div>
                                        </div>
                                        <h4 class="mb-0 ms-4">${{ number_format($stats['partial'], 2) }}</h4>
                                    </div>

                                    <div class="stat-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <div class="color-indicator bg-success me-2"></div>
                                                <span class="text-muted">FULLY PAID</span>
                                            </div>
                                        </div>
                                        <h4 class="mb-0 ms-4">${{ number_format($stats['paid'], 2) }}</h4>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <!-- Additional Stats -->
                                <div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">TOTAL DUE AMOUNT</span>
                                        <strong>${{ number_format($stats['due_amount'], 2) }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">TOTAL PAID AMOUNT</span>
                                        <strong>${{ number_format($stats['collected_amount'], 2) }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">TOTAL INVOICES</span>
                                        <strong>{{ number_format($stats['invoice_count']) }}</strong>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#invoicesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('invoices.get.data') }}',
                    data: function(d) {
                        d.property_id = $('#propertyFilter').val();
                        d.tenant_id = $('#tenantFilter').val();
                        d.status = $('#statusFilter').val();
                        d.type = $('#typeFilter').val();
                    }
                },
                columns: [{
                        data: 'invoice_info',
                        name: 'invoice_number',
                        orderable: true
                    },
                    {
                        data: 'tenant_info',
                        name: 'tenant',
                        orderable: false
                    },
                    {
                        data: 'property_info',
                        name: 'property',
                        orderable: false
                    },
                    {
                        data: 'due_date',
                        name: 'due_date',
                        orderable: true
                    },
                    {
                        data: 'amount_info',
                        name: 'total_amount',
                        orderable: true
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: true,
                        searchable: false
                    }
                ],
                order: [
                    [3, 'desc']
                ],
                pageLength: 25,
                dom: 'rtip',
                language: {
                    search: "",
                    searchPlaceholder: "Search invoices...",
                    info: "Showing _START_ to _END_ of _TOTAL_ invoices",
                    infoEmpty: "Showing 0 to 0 of 0 invoices",
                    infoFiltered: "(filtered from _MAX_ total invoices)",
                },
                drawCallback: function(settings) {
                    const api = this.api();
                    const info = api.page.info();
                    $('#showingInfo').html(
                        `Showing ${info.recordsDisplay} of ${info.recordsTotal} invoices`
                    );
                }
            });

            // Search functionality
            $('#searchFilter').on('keyup', function() {
                table.column(0).search(this.value).draw();
            });

            // Filter changes
            $('#propertyFilter, #tenantFilter, #statusFilter, #typeFilter').change(function() {
                table.ajax.reload();
            });

            // Reset filters
            window.resetFilters = function() {
                $('#tenantFilter, #statusFilter, #typeFilter').val('').trigger('change');
                $('#searchFilter').val('');
                table.column(0).search('').ajax.reload(); // ← CHANGED (ছিল table.search(''))
            };

            // Row click to view details
            $('#invoicesTable tbody').on('click', 'tr', function() {
                const data = table.row(this).data();
                if (data) {
                    window.location.href = '{{ route('invoices.show', '') }}/' + data.id;
                }
            });

            // Initialize Select2
            initializeSelect2();

            // Initialize Select5
            initializeSelect5();

            // Initialize Chart
            initializeChart();
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

        function initializeSelect5() {
            if ($('.select5').length && typeof $.fn.select2 !== 'undefined') {
                $('.select5').select2({
                    placeholder: 'Select a property',
                    allowClear: true,
                    width: '100%'
                });
            }
        }

        function initializeChart() {
            const ctx = document.getElementById('invoiceChart');
            if (!ctx) return;

            const unpaid = {{ $stats['unpaid'] }};
            const overdue = {{ $stats['overdue'] }};
            const partial = {{ $stats['partial'] }};
            const paid = {{ $stats['paid'] }};
            const processing = {{ $stats['processing'] ?? 0 }};

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Unpaid', 'Overdue', 'Processing', 'Partially Paid', 'Fully Paid'],
                    datasets: [{
                        data: [unpaid, overdue, processing, partial, paid],
                        backgroundColor: [
                            '#ffc107', // warning - unpaid
                            '#dc3545', // danger - overdue
                            '#6c757d', // secondary - processing
                            '#17a2b8', // info - partial
                            '#28a745' // success - paid
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': $' + context.parsed.toFixed(2);
                                }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        }
    </script>
@endpush

@push('styles')
    <style>
        .select2-container {
            width: 100% !important;
        }

        #invoicesTable tbody tr {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        #invoicesTable tbody tr:hover {
            background-color: #f8f9fa;
        }

        .color-indicator {
            width: 12px;
            height: 12px;
            border-radius: 2px;
        }

        .stat-item {
            padding: 8px 0;
        }

        .sticky-top {
            position: sticky;
            top: 20px;
        }

        .badge {
            padding: 6px 12px;
            font-weight: 500;
        }

        .progress {
            background-color: #e9ecef;
        }
    </style>
@endpush
