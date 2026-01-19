@extends('backend.app')

@section('title', 'Tenant Applications')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Applications</h1>
                        <p class="text-muted mb-0">Manage all property tenants and their applications</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item " aria-current="page">Tenants</li>
                            <li class="breadcrumb-item active" aria-current="page">Applications</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- STATISTICS ROW -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                        <div class="card stats-card" style="border-left: 4px solid #007bff;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Total Applications</h6>
                                        <h3 class="mb-0">{{ $totalTenants }}</h3>
                                    </div>
                                    <div class="icon-service bg-primary-transparent text-primary p-3 rounded-3">
                                        <i class="fe fe-users fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                        <div class="card stats-card" style="border-left: 4px solid #28a745;"
                            onclick="filterByAccountStatus('active')">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Pending Applications</h6>
                                        <h3 class="mb-0">{{ $pendingTenants }}</h3>
                                    </div>
                                    <div class="icon-service bg-success-transparent text-success p-3 rounded-3">
                                        <i class="fe fe-check-circle fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                        <div class="card stats-card" style="border-left: 4px solid #ffc107;"
                            onclick="filterByStatus('pending')">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Rejected Applications</h6>
                                        <h3 class="mb-0">{{ $pendingTenants }}</h3>
                                    </div>
                                    <div class="icon-service bg-warning-transparent text-warning p-3 rounded-3">
                                        <i class="fe fe-clock fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>

                <!-- FILTERS -->
                <div class="row">
                    <div class="col-12">
                        <div class="filter-card">
                            <div class="row align-items-end g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Tenant Status</label>
                                    <select class="form-select" id="statusFilter">
                                        <option value="">All Status</option>
                                        <option value="pending">Pending</option>
                                        <option value="processing">Processing</option>
                                        <option value="under_review">Under Review</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tenant</label>
                                    <input type="text" name="tenantFilter" id="tenantFilter" class="form-control"
                                        placeholder="Enter tenant name/phone/email">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Application Source</label>
                                    <select class="form-select" id="sourceFilter">
                                        <option value="">All Sources</option>
                                        <option value="admin">Admin Created</option>
                                        <option value="self">Self Registration</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Date From</label>
                                    <input type="date" class="form-control" id="dateFrom">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Date To</label>
                                    <input type="date" class="form-control" id="dateTo">
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="button" class="btn btn-primary me-2" onclick="applyFilters()">
                                        <i class="fe fe-filter me-1"></i> Apply Filters
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                                        <i class="fe fe-refresh-cw me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leases Table -->
                <div class="card">
                    <div class="card-header border-bottom">
                        <h3 class="card-title">All Leases</h3>
                        
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap border-bottom" id="applicationsTable">
                                <thead>
                                    <tr>
                                        <th class="bg-transparent border-bottom-0" style="width: 50px;">ID</th>
                                        <th class="bg-transparent border-bottom-0" style="width: 220px;">Name</th>
                                        <th class="bg-transparent border-bottom-0" style="width: 200px;">
                                            Property/Unit</th>
                                        <th class="bg-transparent border-bottom-0" style="width: 180px;">Address
                                        </th>
                                        <th class="bg-transparent border-bottom-0 text-center"
                                            style="width: 120px;">Account Status</th>
                                        <th class="bg-transparent border-bottom-0 text-center"
                                            style="width: 100px;">Status</th>
                                        <th class="bg-transparent border-bottom-0 text-center"
                                            style="width: 100px;">Rent</th>
                                        {{-- <th class="bg-transparent border-bottom-0 text-center" style="width: 100px;">Roommates</th> --}}
                                        <th class="bg-transparent border-bottom-0 text-center"
                                            style="width: 120px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
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
            const table = $('#applicationsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('tenants.applications.get.data') }}',
                    data: function(d) {
                        d.status = $('#statusFilter').val();
                        d.tenant = $('#tenantFilter').val();
                        d.date_from = $('#dateFrom').val();
                        d.date_to = $('#dateTo').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'property_unit',
                        name: 'property_unit',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'address',
                        name: 'address',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'account_status',
                        name: 'account_status',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'tenant_status',
                        name: 'status',
                        orderable: true,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'rent',
                        name: 'rent',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    // {data: 'roommates', name: 'roommates', orderable: false, searchable: false, className: 'text-center'},
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
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

            // Export functionality
            $('#exportBtn').click(function() {
                toastr.info('Export functionality coming soon');
            });
            initializeSelect2();

            function applyFilters() {
                table.ajax.reload();
            }

            function resetFilters() {
                $('#statusFilter').val('');
                $('#tenantFilter').val('');
                $('#sourceFilter').val('');
                $('#dateFrom').val('');
                $('#dateTo').val('');
                table.ajax.reload();
            }

            $(document).on('change', '#statusFilter, #sourceFilter', function() {
                applyFilters();
            });

            $(document).on('input', '#tenantFilter', function() {
                applyFilters();
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



        function approveTenant(id) {
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, approve it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/applications/${id}/approve`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            toastr.success('Tenant approved successfully');
                            $('#applicationsTable').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            toastr.error('An error occurred while approving the tenant');
                        }
                    })
                }
            })
        }

        function rejectTenant(id) {
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, reject it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/applications/${id}/reject`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            toastr.success('Tenant rejected successfully');
                            $('#applicationsTable').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            toastr.error('An error occurred while rejecting the tenant');
                        }
                    })
                }
            })
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
