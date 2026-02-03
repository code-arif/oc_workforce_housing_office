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
                        <p class="text-muted mb-0">Manage tenant applications and reservations</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item">Tenants</li>
                            <li class="breadcrumb-item active" aria-current="page">Applications</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- TABS -->
                <ul class="nav nav-tabs mb-4" id="applicationTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="single-tab" data-bs-toggle="tab" data-bs-target="#single"
                            type="button" role="tab" aria-controls="single" aria-selected="true">
                            <i class="fe fe-mail me-2"></i>Single Email Applications
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reservation-tab" data-bs-toggle="tab" data-bs-target="#reservation"
                            type="button" role="tab" aria-controls="reservation" aria-selected="false">
                            <i class="fe fe-briefcase me-2"></i>Reservation Applications
                        </button>
                    </li>
                </ul>

                <!-- TAB CONTENT -->
                <div class="tab-content" id="applicationTabsContent">

                    <!-- SINGLE EMAIL TAB -->
                    <div class="tab-pane fade show active" id="single" role="tabpanel" aria-labelledby="single-tab">

                        <!-- STATISTICS ROW -->
                        <div class="row mb-4">
                            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                <div class="card stats-card" style="border-left: 4px solid #007bff;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-muted mb-1">Total Emails</h6>
                                                <h3 class="mb-0">{{ $singleEmailTotal }}</h3>
                                            </div>
                                            <div class="icon-service bg-primary-transparent text-primary p-3 rounded-3">
                                                <i class="fe fe-mail fs-20"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                <div class="card stats-card" style="border-left: 4px solid #ffc107;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-muted mb-1">Pending</h6>
                                                <h3 class="mb-0">{{ $singleEmailPending }}</h3>
                                            </div>
                                            <div class="icon-service bg-warning-transparent text-warning p-3 rounded-3">
                                                <i class="fe fe-clock fs-20"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                <div class="card stats-card" style="border-left: 4px solid #28a745;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-muted mb-1">Approved</h6>
                                                <h3 class="mb-0">{{ $singleEmailApproved }}</h3>
                                            </div>
                                            <div class="icon-service bg-success-transparent text-success p-3 rounded-3">
                                                <i class="fe fe-check-circle fs-20"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- FILTERS -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row align-items-end g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Status</label>
                                                <select class="form-select" id="singleStatusFilter">
                                                    <option value="">All Status</option>
                                                    <option value="pending">Pending</option>
                                                    <option value="approved">Approved</option>
                                                    <option value="rejected">Rejected</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Search</label>
                                                <input type="text" class="form-control" id="singleSearchFilter"
                                                    placeholder="Email, name, phone...">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Date From</label>
                                                <input type="date" class="form-control" id="singleDateFrom">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Date To</label>
                                                <input type="date" class="form-control" id="singleDateTo">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-secondary w-100"
                                                    onclick="resetSingleFilters()">
                                                    <i class="fe fe-refresh-cw me-1"></i> Reset
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TABLE -->
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h3 class="card-title">Single Email Applications</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom" id="singleEmailTable">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">ID</th>
                                                <th style="width: 250px;">Email & Name</th>
                                                <th style="width: 200px;">Contact</th>
                                                <th style="width: 150px;">Status</th>
                                                <th style="width: 150px;">Submitted At</th>
                                                <th style="width: 180px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- RESERVATION TAB -->
                    <div class="tab-pane fade" id="reservation" role="tabpanel" aria-labelledby="reservation-tab">

                        <!-- STATISTICS ROW -->
                        <div class="row mb-4">
                            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                <div class="card stats-card" style="border-left: 4px solid #007bff;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-muted mb-1">Total Reservations</h6>
                                                <h3 class="mb-0">{{ $reservationTotal }}</h3>
                                            </div>
                                            <div class="icon-service bg-primary-transparent text-primary p-3 rounded-3">
                                                <i class="fe fe-briefcase fs-20"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                <div class="card stats-card" style="border-left: 4px solid #ffc107;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-muted mb-1">Pending</h6>
                                                <h3 class="mb-0">{{ $reservationPending }}</h3>
                                            </div>
                                            <div class="icon-service bg-warning-transparent text-warning p-3 rounded-3">
                                                <i class="fe fe-clock fs-20"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                <div class="card stats-card" style="border-left: 4px solid #28a745;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-muted mb-1">Approved</h6>
                                                <h3 class="mb-0">{{ $reservationApproved }}</h3>
                                            </div>
                                            <div class="icon-service bg-success-transparent text-success p-3 rounded-3">
                                                <i class="fe fe-check-circle fs-20"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- FILTERS -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row align-items-end g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Status</label>
                                                <select class="form-select" id="reservationStatusFilter">
                                                    <option value="">All Status</option>
                                                    <option value="pending">Pending</option>
                                                    <option value="approved">Approved</option>
                                                    <option value="rejected">Rejected</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Search</label>
                                                <input type="text" class="form-control" id="reservationSearchFilter"
                                                    placeholder="Company, email, name...">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Date From</label>
                                                <input type="date" class="form-control" id="reservationDateFrom">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Date To</label>
                                                <input type="date" class="form-control" id="reservationDateTo">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-secondary w-100"
                                                    onclick="resetReservationFilters()">
                                                    <i class="fe fe-refresh-cw me-1"></i> Reset
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TABLE -->
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h3 class="card-title">Reservation Applications</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom" id="reservationTable">
                                        <thead>
                                            <tr>
                                                <th style="width: 80px;">ID</th>
                                                <th style="width: 300px;">Company & Contact</th>
                                                <th style="width: 250px;">Contact Info</th>
                                                <th style="width: 250px;">Details</th>
                                                <th style="width: 180px;">Status</th>
                                                <th style="width: 200px;">Submitted At</th>
                                                <th style="width: 150px;">Action</th>
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

    <!-- Reservation Details Modal -->
    <div class="modal fade" id="reservationModal" tabindex="-1" aria-labelledby="reservationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="reservationModalLabel">
                        <i class="fe fe-briefcase me-2"></i>Reservation Application Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="reservationDetails">
                        <!-- Details will be loaded here -->
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fe fe-x me-1"></i>Close
                    </button>
                    <button type="button" class="btn btn-primary" id="contactApplicantBtn">
                        <i class="fe fe-mail me-1"></i>Contact Applicant
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let singleEmailTable, reservationTable;

        $(document).ready(function() {
            // Initialize Single Email Table
            singleEmailTable = $('#singleEmailTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('tenants.applications.get.data') }}',
                    data: function(d) {
                        d.type = 'single';
                        d.status = $('#singleStatusFilter').val();
                        d.search = $('#singleSearchFilter').val();
                        d.date_from = $('#singleDateFrom').val();
                        d.date_to = $('#singleDateTo').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'applicant',
                        name: 'email',
                        orderable: true
                    },
                    {
                        data: 'contact',
                        name: 'contact',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: true,
                        className: 'text-center'
                    },
                    {
                        data: 'submitted_at',
                        name: 'created_at',
                        orderable: true
                    },
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
                language: {
                    search: "",
                    searchPlaceholder: "Search...",
                }
            });

            // Initialize Reservation Table
            reservationTable = $('#reservationTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('tenants.applications.get.data') }}',
                    data: function(d) {
                        d.type = 'reservation';
                        d.status = $('#reservationStatusFilter').val();
                        d.search = $('#reservationSearchFilter').val();
                        d.date_from = $('#reservationDateFrom').val();
                        d.date_to = $('#reservationDateTo').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'applicant',
                        name: 'company_name',
                        orderable: true
                    },
                    {
                        data: 'contact',
                        name: 'email',
                        orderable: true
                    },
                    {
                        data: 'details',
                        name: 'details',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: true,
                        className: 'text-center'
                    },
                    {
                        data: 'submitted_at',
                        name: 'created_at',
                        orderable: true
                    },
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
                language: {
                    search: "",
                    searchPlaceholder: "Search...",
                }
            });

            // Single Email Filters
            $('#singleStatusFilter').on('change', function() {
                singleEmailTable.ajax.reload();
            });

            $('#singleSearchFilter').on('keyup', debounce(function() {
                singleEmailTable.ajax.reload();
            }, 500));

            // Reservation Filters
            $('#reservationStatusFilter').on('change', function() {
                reservationTable.ajax.reload();
            });

            $('#reservationSearchFilter').on('keyup', debounce(function() {
                reservationTable.ajax.reload();
            }, 500));
        });

        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, arguments), wait);
            };
        }

        // Reset Filters
        function resetSingleFilters() {
            $('#singleStatusFilter').val('');
            $('#singleSearchFilter').val('');
            $('#singleDateFrom').val('');
            $('#singleDateTo').val('');
            singleEmailTable.ajax.reload();
        }

        function resetReservationFilters() {
            $('#reservationStatusFilter').val('');
            $('#reservationSearchFilter').val('');
            $('#reservationDateFrom').val('');
            $('#reservationDateTo').val('');
            reservationTable.ajax.reload();
        }

        // Approve Single Email
        function approveSingleEmail(id) {
            Swal.fire({
                title: 'Approve Application?',
                text: "This will create a tenant account and send them a form link.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Approve!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/applications/${id}/approve-single`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire('Approved!', response.message, 'success');
                            singleEmailTable.ajax.reload();
                        },
                        error: function(xhr) {
                            const message = xhr.responseJSON?.message || 'An error occurred';
                            Swal.fire('Error!', message, 'error');
                        }
                    });
                }
            });
        }

        // Approve Reservation
        function approveReservation(id) {
            Swal.fire({
                title: 'Approve Reservation?',
                text: "This will create a tenant account and send them a form link.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Approve!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/applications/${id}/approve-reservation`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire('Approved!', response.message, 'success');
                            reservationTable.ajax.reload();
                        },
                        error: function(xhr) {
                            const message = xhr.responseJSON?.message || 'An error occurred';
                            Swal.fire('Error!', message, 'error');
                        }
                    });
                }
            });
        }

        // View Reservation Details
        function seeReservation(id) {
            // Show modal
            $('#reservationModal').modal('show');

            // Load reservation details
            $.ajax({
                url: `/admin/applications/${id}`,
                type: 'GET',
                success: function(response) {
                    displayReservationDetails(response);
                },
                error: function(xhr) {
                    $('#reservationDetails').html(`
                        <div class="alert alert-danger">
                            <i class="fe fe-alert-triangle me-2"></i>
                            Failed to load reservation details. Please try again.
                        </div>
                    `);
                }
            });
        }

        // Display Reservation Details in Modal
        function displayReservationDetails(application) {
            const reservationItems = application.reservation_item ? JSON.parse(application.reservation_item) : [];
            // const reservationItems = application.reservation_item ?? [];


            let reservationItemsHtml = '';
            if (reservationItems.length > 0) {
                reservationItemsHtml = reservationItems.map((item, index) => `
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="text-primary mb-3">Reservation Item #${index + 1}</h6>
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <small class="text-muted d-block">Property Name</small>
                                    <strong>${item.property_name || 'N/A'}</strong>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <small class="text-muted d-block">Interested Type 1</small>
                                    <strong>${item.interested1 || 'N/A'}</strong>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <small class="text-muted d-block">Interested Type 2</small>
                                    <strong>${item.interested2 || 'N/A'}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                reservationItemsHtml = '<p class="text-muted">No reservation items available.</p>';
            }

            const html = `
                <div class="reservation-details">
                    <!-- Company Information -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fe fe-briefcase text-primary me-2"></i>Company Information
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Company Name</small>
                                <strong>${application.company_name || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Industry</small>
                                <strong>${application.industry || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Employee Count</small>
                                <strong>${application.employee_count || 'N/A'}</strong>
                            </div>
                            <div class="col-md-12 mb-3">
                                <small class="text-muted d-block">Company Address</small>
                                <strong>${application.company_address || 'N/A'}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Person Information -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fe fe-user text-primary me-2"></i>Contact Person
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Full Name</small>
                                <strong>${application.first_name || ''} ${application.middle_name || ''} ${application.last_name || ''}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Job Title</small>
                                <strong>${application.job_title || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Email</small>
                                <strong>${application.email || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Phone</small>
                                <strong>${application.phone || 'N/A'}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Reservation Items -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fe fe-list text-primary me-2"></i>Reservation Items
                        </h6>
                        ${reservationItemsHtml}
                    </div>

                    <!-- Additional Notes -->
                    ${application.notes ? `
                                                <div class="mb-3">
                                                    <h6 class="border-bottom pb-2 mb-3">
                                                        <i class="fe fe-message-square text-primary me-2"></i>Additional Notes
                                                    </h6>
                                                    <div class="alert alert-info">
                                                        ${application.notes}
                                                    </div>
                                                </div>
                                            ` : ''}

                    <!-- Application Info -->
                    <div class="mb-3">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fe fe-info text-primary me-2"></i>Application Status
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block">Status</small>
                                <span class="badge bg-${application.status === 'pending' ? 'warning' : application.status === 'approved' ? 'success' : 'danger'}">
                                    ${application.status.charAt(0).toUpperCase() + application.status.slice(1)}
                                </span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block">Submitted At</small>
                                <strong>${new Date(application.created_at).toLocaleString()}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('#reservationDetails').html(html);

            // Update contact button with application data
            $('#contactApplicantBtn').off('click').on('click', function() {
                // Store application ID for the contact route
                window.location.href = `/admin/applications/${application.id}/contact`;
            });
        }

        // Reject Application
        function rejectApplication(id) {
            Swal.fire({
                title: 'Reject Application?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Reject!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/applications/${id}/reject`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire('Rejected!', response.message, 'success');
                            singleEmailTable.ajax.reload();
                            reservationTable.ajax.reload();
                        },
                        error: function(xhr) {
                            const message = xhr.responseJSON?.message || 'An error occurred';
                            Swal.fire('Error!', message, 'error');
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

        .stats-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .nav-tabs .nav-link {
            color: #6c757d;
            border: none;
            border-bottom: 2px solid transparent;
            padding: 12px 24px;
        }

        .nav-tabs .nav-link.active {
            color: #fff !important;
            background: #D9A600;
        }

        .nav-tabs .nav-link:hover {
            color: #fff !important;
            background: #fcd554;
        }

        .badge {
            padding: 6px 12px;
            font-weight: 500;
        }

        /* Modal Styling */
        #reservationModal .modal-content {
            border-radius: 10px;
            overflow: hidden;
        }

        #reservationModal .modal-header {
            background: linear-gradient(135deg, #D9A600 0%, #fcd554 100%);
            border: none;
        }

        #reservationModal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        .reservation-details h6 {
            color: #333;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .reservation-details .card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }

        .reservation-details .card-body {
            background-color: #f8f9fa;
        }

        .reservation-details small.text-muted {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .reservation-details strong {
            font-size: 0.95rem;
            color: #495057;
        }

        /* Scrollbar Styling */
        #reservationModal .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        #reservationModal .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        #reservationModal .modal-body::-webkit-scrollbar-thumb {
            background: #D9A600;
        }

        #reservationModal .modal-body::-webkit-scrollbar-thumb:hover {
            background: #b88d00;
        }

        /* Reservation Modal Scroll Fix */
        #reservationModal .modal-dialog {
            max-height: 90vh;
        }

        #reservationModal .modal-content {
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        #reservationModal .modal-body {
            overflow-y: auto;
            max-height: calc(90vh - 140px);
            /* header + footer height */
        }

        .reservation-items-wrapper {
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
@endpush
