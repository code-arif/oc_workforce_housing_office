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
                <!-- FILTERS -->
                <div class="row">
                    <div class="col-12">
                        <div class="filter-card">
                            <div class="row align-items-end g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Property</label>
                                    <select class="form-select select3" id="propertyFilter">
                                        <option value="">Select Property</option>
                                        @foreach ($properties as $property)
                                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Bed</label>
                                    <select class="form-select select3" id="bedFilter">
                                        <option value="">All Beds</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tenant</label>
                                    <select class="form-select select3" id="tenantFilter">
                                        <option value="">Select Tenant</option>
                                        @foreach ($tenants as $tenant)
                                            <option value="{{ $tenant->id }}">{{ $tenant?->profile?->first_name }} {{ $tenant?->profile?->last_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Status</label>
                                    <select class="form-select select3" id="statusFilter">
                                        <option value="">All Statuses</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                                        <i class="fe fe-refresh-cw me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                            {{-- <div class="row mt-3">
                                <div class="col-12">
                                    <button type="button" class="btn btn-primary me-2" onclick="applyFilters()">
                                        <i class="fe fe-filter me-1"></i> Apply Filters
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                                        <i class="fe fe-refresh-cw me-1"></i> Reset
                                    </button>
                                </div>
                            </div> --}}
                        </div>
                    </div>
                </div>
                <!-- Leases Table -->
                <div class="card">
                    <div class="card-header border-bottom">
                        <h3 class="card-title">All Leases</h3>
                        <div class="ms-auto">
                            <a href="{{ route('leases.create') }}" class="btn btn-primary d-inline-flex align-items-center">
                                <i class="fe fe-plus me-1"></i> New Lease
                            </a>
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
                        d.property_id = $('#propertyFilter').val();
                        d.bed_id = $('#bedFilter').val();
                        d.tenant_id = $('#tenantFilter').val();
                        d.status = $('#statusFilter').val();
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
                pageLength: 10,
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
                // $('#filterForm')[0].reset();
                $('#propertyFilter, #bedFilter, #tenantFilter, #statusFilter').val(null).trigger('change');
                table.ajax.reload();
            });
            $('#propertyFilter, #bedFilter, #tenantFilter, #statusFilter').change(function() {
                table.ajax.reload();
            })

            $('#propertyFilter').change(function() {
                const propertyId = $(this).val();
                $('#bedFilter').empty().append('<option value="">All Beds</option>');
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

            // Export functionality
            $('#exportBtn').click(function() {
                toastr.info('Export functionality coming soon');
            });
            initializeSelect2();
        });

        function resetFilters() {
            $('#propertyFilter, #bedFilter, #tenantFilter, #statusFilter').val(null).trigger('change');
            $('#leasesTable').DataTable().ajax.reload();
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
