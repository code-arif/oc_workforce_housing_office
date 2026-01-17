@extends('backend.app')

@section('title', 'Leases')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Leases</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Leases</li>
                        </ol>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('leases.create') }}" class="btn btn-primary">
                            <i class="fe fe-plus me-2"></i> New Lease
                        </a>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1">Active Leases</p>
                                        <h3 class="mb-0">{{ $stats['active'] }}</h3>
                                    </div>
                                    <div class="ms-3">
                                        <div class="icon-service bg-success-transparent text-success">
                                            <i class="fe fe-file-text"></i>
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
                                        <p class="text-muted mb-1">In Process</p>
                                        <h3 class="mb-0">{{ $stats['inProcess'] }}</h3>
                                    </div>
                                    <div class="ms-3">
                                        <div class="icon-service bg-info-transparent text-info">
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
                                        <p class="text-muted mb-1">Future Leases</p>
                                        <h3 class="mb-0">{{ $stats['future'] }}</h3>
                                    </div>
                                    <div class="ms-3">
                                        <div class="icon-service bg-primary-transparent text-primary">
                                            <i class="fe fe-calendar"></i>
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
                                        <p class="text-muted mb-1">Expiring in 90 Days</p>
                                        <h3 class="mb-0">{{ $stats['expiring'] }}</h3>
                                    </div>
                                    <div class="ms-3">
                                        <div class="icon-service bg-warning-transparent text-warning">
                                            <i class="fe fe-alert-circle"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leases Table -->
                <div class="card">
                    <div class="card-header border-bottom">
                        <h3 class="card-title">All Leases</h3>
                        <div class="ms-auto">
                            <button class="btn btn-sm btn-light me-2" id="exportBtn">
                                <i class="fe fe-download me-1"></i> Export
                            </button>
                            <button class="btn btn-sm btn-light" id="filterBtn">
                                <i class="fe fe-filter me-1"></i> Filter
                            </button>
                        </div>
                    </div>

                    <!-- Filter Panel -->
                    <div class="card-body border-bottom" id="filterPanel" style="display: none;">
                        <form id="filterForm" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <input type="text" class="form-control select3" name="status" id="statusFilter"
                                    data-placeholder="Select Status">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date From</label>
                                <input type="date" class="form-control" name="date_from" id="dateFrom">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date To</label>
                                <input type="date" class="form-control" name="date_to" id="dateTo">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-fill">Apply</button>
                                    <button type="button" class="btn btn-light flex-fill"
                                        id="resetFilter">Reset</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="leasesTable">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Property</th>
                                        <th>Tenant</th>
                                        <th>Lease Duration</th>
                                        <th>Rent</th>
                                        <th>Signatures</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#leasesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('leases.get.data') }}',
                    data: function(d) {
                        d.status = $('#statusFilter').val();
                        d.date_from = $('#dateFrom').val();
                        d.date_to = $('#dateTo').val();
                    }
                },
                columns: [{
                        data: 'status_badge',
                        name: 'status',
                        orderable: true,
                        searchable: false
                    },
                    {
                        data: 'property_unit',
                        name: 'property',
                        orderable: false
                    },
                    {
                        data: 'tenant_name',
                        name: 'tenant',
                        orderable: false
                    },
                    {
                        data: 'dates',
                        name: 'dates',
                        orderable: true
                    },
                    {
                        data: 'rent',
                        name: 'rent_amount',
                        orderable: true
                    },
                    {
                        data: 'signature_status',
                        name: 'signature_status',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                pageLength: 25,
                dom: '<"d-flex justify-content-between align-items-center mb-3"<"showing-info">f>rtip',
                language: {
                    search: "",
                    searchPlaceholder: "Search leases...",
                    info: "Showing _START_ to _END_ of _TOTAL_ leases",
                    infoEmpty: "Showing 0 to 0 of 0 leases",
                    infoFiltered: "(filtered from _MAX_ total leases)",
                    lengthMenu: "Show _MENU_ leases"
                },
                drawCallback: function(settings) {
                    const api = this.api();
                    const info = api.page.info();
                    $('.showing-info').html(
                        `<span class="text-muted">Showing ${info.recordsDisplay} of ${info.recordsTotal}</span>`
                    );
                }
            });

            // Row click to view details
            $('#leasesTable tbody').on('click', 'tr', function() {
                const data = table.row(this).data();
                if (data) {
                    window.location.href = '{{ route('leases.show', '') }}/' + data.id;
                }
            });

            // Filter toggle
            $('#filterBtn').click(function() {
                $('#filterPanel').slideToggle();
            });

            // Apply filters
            $('#filterForm').submit(function(e) {
                e.preventDefault();
                table.ajax.reload();
            });

            // Reset filters
            $('#resetFilter').click(function() {
                $('#filterForm')[0].reset();
                table.ajax.reload();
            });

            // Export functionality
            $('#exportBtn').click(function() {
                toastr.info('Export functionality coming soon');
            });
            initializeSelect2();
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
        .icon-service {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 24px;
        }

        #leasesTable tbody tr {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        #leasesTable tbody tr:hover {
            background-color: #f8f9fa;
        }

        .dataTables_filter input {
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 8px 12px;
            min-width: 250px;
        }

        .badge {
            padding: 6px 12px;
            font-weight: 500;
        }
    </style>
@endpush
