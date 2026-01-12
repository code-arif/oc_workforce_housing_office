@extends('backend.app')

@section('title', 'Maintenance Requests')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Maintenance Requests</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Maintenance</li>
                        </ol>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('maintanance.create') }}" class="btn btn-primary">
                            <i class="fe fe-plus me-2"></i> New Maintenance
                        </a>
                    </div>
                </div>

                <!-- Quick Filter Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                        <div class="card quick-filter-card" data-filter="open">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1">Open</p>
                                        <h3 class="mb-0">{{ $stats['open'] }}</h3>
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

                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                        <div class="card quick-filter-card" data-filter="resolved">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1">Resolved</p>
                                        <h3 class="mb-0">{{ $stats['resolved'] }}</h3>
                                    </div>
                                    <div class="ms-3">
                                        <div class="icon-service bg-success-transparent text-success">
                                            <i class="fe fe-check-circle"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                        <div class="card quick-filter-card" data-filter="scheduled">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1">Scheduled</p>
                                        <h3 class="mb-0">{{ $stats['scheduled'] }}</h3>
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

                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                        <div class="card quick-filter-card" data-filter="urgent">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1">Urgent</p>
                                        <h3 class="mb-0">{{ $stats['urgent'] }}</h3>
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
                </div>

                <!-- Maintenance Table -->
                <div class="card">
                    <div class="card-header border-bottom">
                        <h3 class="card-title">All Maintenance Requests</h3>
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
                                <select class="form-select" name="status" id="statusFilter">
                                    <option value="">All Statuses</option>
                                    <option value="pending">Open</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Resolved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category" id="categoryFilter">
                                    <option value="">All Categories</option>
                                    <option value="ac">A/C</option>
                                    <option value="appliance">Appliance</option>
                                    <option value="electrical">Electrical</option>
                                    <option value="heat">Heat</option>
                                    <option value="kitchen">Kitchen</option>
                                    <option value="plumbing">Plumbing</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date From</label>
                                <input type="date" class="form-control" name="date_from" id="dateFrom">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date To</label>
                                <input type="date" class="form-control" name="date_to" id="dateTo">
                            </div>
                            <div class="col-md-2">
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
                            <table class="table table-hover" id="maintenanceTable">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Request</th>
                                        <th>Property/Unit</th>
                                        <th>Requested by</th>
                                        <th>Issue Date</th>
                                        <th>Actions</th>
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
            const table = $('#maintenanceTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('maintanance.getData') }}',
                    data: function(d) {
                        d.status = $('#statusFilter').val();
                        d.category = $('#categoryFilter').val();
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
                        data: 'request_info',
                        name: 'title',
                        orderable: false
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
                        data: 'issue_date',
                        name: 'created_at',
                        orderable: true
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [4, 'desc']
                ],
                pageLength: 25,
                dom: '<"d-flex justify-content-between align-items-center mb-3"<"showing-info">f>rtip',
                language: {
                    search: "",
                    searchPlaceholder: "Search maintenance...",
                    info: "Showing _START_ to _END_ of _TOTAL_ requests",
                    infoEmpty: "Showing 0 to 0 of 0 requests",
                    infoFiltered: "(filtered from _MAX_ total requests)",
                    lengthMenu: "Show _MENU_ requests"
                },
                drawCallback: function(settings) {
                    const api = this.api();
                    const info = api.page.info();
                    $('.showing-info').html(
                        `<span class="text-muted">Showing ${info.recordsDisplay} of ${info.recordsTotal}</span>`
                    );
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

            // Quick filter cards
            $('.quick-filter-card').click(function() {
                const filter = $(this).data('filter');
                $('#statusFilter').val('');
                $('#categoryFilter').val('');

                switch (filter) {
                    case 'open':
                        $('#statusFilter').val('pending');
                        break;
                    case 'resolved':
                        $('#statusFilter').val('completed');
                        break;
                    case 'scheduled':
                        $('#statusFilter').val('in_progress');
                        break;
                    case 'urgent':
                        // Implement urgent filter logic
                        break;
                }

                table.ajax.reload();
            });

            // Export functionality
            $('#exportBtn').click(function() {
                toastr.info('Export functionality coming soon');
            });
        });

        // View details function
        function viewDetails(id) {
            window.location.href = '{{ route('maintanance.show', '') }}/' + id;
        }

        // Edit function
        function editRequest(id) {
            window.location.href = '{{ route('maintanance.edit', '') }}/' + id;
        }

        // Delete function
        function deleteRequest(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('maintanance.delete', '') }}/' + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Deleted!', response.message, 'success');
                                $('#maintenanceTable').DataTable().ajax.reload();
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', 'Failed to delete request', 'error');
                        }
                    });
                }
            });
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

        .quick-filter-card {
            cursor: pointer;
            transition: all 0.3s;
        }

        .quick-filter-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        #maintenanceTable tbody tr {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        #maintenanceTable tbody tr:hover {
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

        .btn-group .btn {
            padding: 4px 8px;
        }
    </style>
@endpush
