@extends('backend.app')

@section('title', 'Tenant Management')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app" style="margin-bottom: 50px">
            <div class="main-container container-fluid">

                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Tenants</h1>
                        <p class="text-muted mb-0">Manage all property tenants and their lease agreements</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Tenants</li>
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
                                        <h6 class="text-muted mb-1">Total Tenants</h6>
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
                                        <h6 class="text-muted mb-1">Active Tenants</h6>
                                        <h3 class="mb-0">{{ $activeTenants }}</h3>
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
                                        <h6 class="text-muted mb-1">Pending</h6>
                                        <h3 class="mb-0">{{ $pendingTenants }}</h3>
                                    </div>
                                    <div class="icon-service bg-warning-transparent text-warning p-3 rounded-3">
                                        <i class="fe fe-clock fs-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                        <div class="card stats-card" style="border-left: 4px solid #6c757d;"
                            onclick="filterByAccountStatus('inactive')">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Inactive</h6>
                                        <h3 class="mb-0">{{ $inactiveTenants }}</h3>
                                    </div>
                                    <div class="icon-service bg-secondary-transparent text-secondary p-3 rounded-3">
                                        <i class="fe fe-user-x fs-20"></i>
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
                                    <label class="form-label">Tenant </label>
                                    <input type="text" name="tenantFilter" id="tenantFilter" class="form-control"
                                        placeholder="Search by name, email...">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Property</label>
                                    <select class="form-select select3" id="propertyFilter">
                                        <option value="">Select Property</option>
                                        @foreach($properties as $property)
                                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Beds</label>
                                    <select class="form-select select3" id="bedsFilter"></select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Date From</label>
                                    <input type="text" class="form-control datepicker2" id="dateFrom" placeholder="Search by tenant created from...">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Date To</label>
                                    <input type="text" class="form-control datepicker2" id="dateTo" placeholder="Search by tenant created to...">
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="button" class="btn btn-secondary" id="resetFilter">
                                        <i class="fe fe-refresh-cw me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TENANT LIST TABLE -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">Tenant List</h3>

                                <div class="card-options d-flex align-items-center">
                                    <button class="btn btn-sm btn-outline-primary me-2 d-inline-flex align-items-center"
                                        onclick="exportTenants()">
                                        <i class="fe fe-download me-1"></i>
                                        Export
                                    </button>

                                    <button class="btn btn-sm btn-primary d-inline-flex align-items-center"
                                        onclick="showAddTenantModal()">
                                        <i class="fe fe-plus me-1"></i>
                                        Add Tenant
                                    </button>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom" id="datatable">
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
        </div>
    </div>

    <!-- ADD/EDIT TENANT MODAL -->
    <div class="modal fade" id="tenantModal" tabindex="-1" aria-labelledby="tenantModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tenantModalLabel">Add Tenant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="tenantForm">
                    <input type="hidden" id="tenant_id" name="tenant_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span class="btn-text">Add Tenant</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"
                                aria-hidden="true"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{asset('backend/plugins/bootstrap-datepicker/js/datepicker.js')}}"></script>
    <script>
        let dataTable;

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    "X-Requested-With": "XMLHttpRequest" // Important for back/forward fix
                }
            });

             $('.datepicker2').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true
            });
            
            initializeDataTable();
            initializeSelect2();
        });

        function initializeDataTable() {
            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }

            dataTable = $('#datatable').DataTable({
                order: [
                    [0, 'desc']
                ],
                lengthMenu: [
                    [20, 50, 100, 200],
                    [20, 50, 100, 200]
                ],
                processing: true,
                responsive: true,
                serverSide: true,
                language: {
                    processing: `<div class="text-center">
                        <img src="{{ asset('default/loader.gif') }}" alt="Loader" style="width: 50px;">
                    </div>`
                },
                pagingType: "full_numbers",
                dom: "<'row justify-content-between table-topbar'<'col-md-4 col-sm-3'l><'col-md-5 col-sm-5 px-0'f>>tipr",
                ajax: {
                    url: "{{ route('tenants.get.data') }}",
                    type: "GET",
                    dataType: 'json', // Important for back/forward fix
                    data: function(d) {
                        d.tenant = $('#tenantFilter').val();
                        d.property_id = $('#propertyFilter').val();
                        d.bed_id = $('#bedsFilter').val();
                        d.status = $('#statusFilter').val();
                        d.account_status = $('#accountStatusFilter').val();
                        d.source = $('#sourceFilter').val();
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
                ]
            });

            // Reset filters
            $('#resetFilter').click(function() {
                // $('#filterForm')[0].reset();
                $('#propertyFilter, #bedsFilter, #tenantFilter, #statusFilter, #dateFrom, #dateTo').val(null).trigger('change');
                $('#tenantFilter').val('');
                dataTable.ajax.reload();
            });

            $('#propertyFilter, #bedsFilter, #statusFilter, #dateFrom, #dateTo').change(function() {
                dataTable.ajax.reload();
            })

            $('#tenantFilter').on('keyup', function() {
                dataTable.ajax.reload();
            });

            $('#propertyFilter').change(function() {
                const propertyId = $(this).val();
                $('#bedsFilter').empty().append('<option value="">All Beds</option>');
                if (propertyId) {
                    $.ajax({
                        url: '{{ url('admin/leases/property') }}/' + propertyId + '/beds',
                        type: 'GET',
                        success: function(response) {
                            console.log(response);
                            
                            response.data.forEach(function(bed) {
                                $('#bedsFilter').append(
                                    `<option value="${bed.id}">${bed.bed_label}</option>`
                                );
                            });
                            $('#bedsFilter').val(null).trigger('change');
                        },
                        error: function() {
                            toastr.error('Failed to fetch beds for the selected property.');
                        }
                    });
                }
            });
        }

        function applyFilters() {
            dataTable.ajax.reload();
        }

        function resetFilters() {
            $('#statusFilter, #tenantFilter, #sourceFilter, #dateFrom, #dateTo').val('');
            dataTable.ajax.reload();
        }

        function filterByStatus(status) {
            $('#statusFilter').val(status);
            $('#accountStatusFilter').val('');
            applyFilters();
        }

        function filterByAccountStatus(status) {
            $('#accountStatusFilter').val(status);
            $('#statusFilter').val('');
            applyFilters();
        }

        // Add Tenant Modal
        function showAddTenantModal() {
            $('#tenantModalLabel').text('Add Tenant');
            $('#tenantForm')[0].reset();
            $('#tenant_id').val('');
            $('.invalid-feedback').text('').parent().removeClass('is-invalid');
            $('#submitBtn .btn-text').text('Add Tenant');
            $('#tenantModal').modal('show');
        }

        // Edit Tenant
        function editTenant(id) {
            NProgress.start();
            $.ajax({
                url: "{{ route('tenants.edit', ':id') }}".replace(':id', id),
                type: 'GET',
                success: function(response) {
                    NProgress.done();
                    console.log(response);
                    
                    if (response.success) {
                        $('#tenantModalLabel').text('Edit Tenant');
                        $('#tenant_id').val(response.tenant.id);
                        $('#first_name').val(response.tenant.first_name);
                        $('#middle_name').val(response.tenant.middle_name);
                        $('#last_name').val(response.tenant.last_name);
                        $('#email').val(response.tenant.email);
                        $('#phone').val(response.tenant.phone);
                        $('#submitBtn .btn-text').text('Update Tenant');
                        $('#tenantModal').modal('show');
                    }
                },
                error: function() {
                    NProgress.done();
                    toastr.error('Failed to load tenant data');
                }
            });
        }

        // Submit Form
        $('#tenantForm').on('submit', function(e) {
            e.preventDefault();

            const tenantId = $('#tenant_id').val();
            const url = tenantId ?
                "{{ route('tenants.update', ':id') }}".replace(':id', tenantId) :
                "{{ route('tenants.store') }}";
            const method = tenantId ? 'PUT' : 'POST';

            $('.invalid-feedback').text('').parent().removeClass('is-invalid');
            $('#submitBtn').prop('disabled', true);
            $('#submitBtn .btn-text').addClass('d-none');
            $('#submitBtn .spinner-border').removeClass('d-none');

            NProgress.start();

            $.ajax({
                url: url,
                type: method,
                data: $(this).serialize(),
                success: function(response) {
                    NProgress.done();
                    $('#submitBtn').prop('disabled', false);
                    $('#submitBtn .btn-text').removeClass('d-none');
                    $('#submitBtn .spinner-border').addClass('d-none');

                    if (response.success) {
                        toastr.success(response.message);
                        $('#tenantModal').modal('hide');
                        dataTable.ajax.reload();
                    }
                },
                error: function(xhr) {
                    NProgress.done();
                    $('#submitBtn').prop('disabled', false);
                    $('#submitBtn .btn-text').removeClass('d-none');
                    $('#submitBtn .spinner-border').addClass('d-none');

                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            const input = $(`#${key}`);
                            input.addClass('is-invalid');
                            input.siblings('.invalid-feedback').text(value[0]);
                        });
                    } else {
                        toastr.error(xhr.responseJSON?.message || 'An error occurred');
                    }
                }
            });
        });

        function showDeleteConfirm(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'This tenant will be deleted permanently!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteTenant(id);
                }
            });
        }

        function deleteTenant(id) {
            NProgress.start();
            $.ajax({
                type: "DELETE",
                url: "{{ route('tenants.destroy', ':id') }}".replace(':id', id),
                success: function(resp) {
                    NProgress.done();
                    if (resp.success) {
                        toastr.success(resp.message);
                        dataTable.ajax.reload();
                    } else {
                        toastr.error(resp.message);
                    }
                },
                error: function(error) {
                    NProgress.done();
                    toastr.error(error.responseJSON?.message || 'Failed to delete tenant!');
                }
            });
        }

        function exportTenants() {
            toastr.info('Export functionality coming soon!');
        }

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
    <link href="{{ asset('default/datatable.css') }}" rel="stylesheet" />
    <style>
        .select2-container {
            width: 100% !important;
        }
        .filter-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }

        .filter-card .form-label {
            font-weight: 600;
            font-size: 13px;
            color: #495057;
            margin-bottom: 8px;
        }

        .stats-card {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            border: 1px solid #e9ecef;
        }

        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .icon-service {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .table th {
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Fix for text overflow */
        .table td {
            max-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .btn-group-sm>.btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .card-options .btn {
            font-size: 13px;
        }

        .badge {
            padding: 0.35em 0.65em;
            font-weight: 500;
        }

        .modal-header {
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
        }

        .modal-title {
            font-weight: 600;
            color: #2c3e50;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }

        .is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
@endpush
