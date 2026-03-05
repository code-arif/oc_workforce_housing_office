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
                        {{-- <div class="row mb-4">
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
                        </div> --}}

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
                            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">Single Email Applications</h3>
                                <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center"
                                    onclick="showInviteModal()">
                                    <i class="fe fe-send me-1"></i> Send Invitation
                                </button>
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
                        {{-- <div class="row mb-4">
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
                        </div> --}}

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
                                    {{-- <table class="table table-bordered text-nowrap border-bottom" id="reservationTable"> --}}
                                    <table class="table table-bordered border-bottom w-100" id="reservationTable">
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

    <!-- Single Email Application Details Modal -->
    <div class="modal fade" id="singleEmailModal" tabindex="-1" aria-labelledby="singleEmailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="singleEmailModalLabel">
                        <i class="fe fe-user me-2"></i>Application Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="singleEmailDetails">
                        <!-- Details will be loaded here -->
                        <div class="text-center py-4">
                            <div class="spinner-border text-info" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fe fe-x me-1"></i>Close
                    </button>
                    <button type="button" class="btn btn-success d-none" id="approveFromModalBtn">
                        <i class="fe fe-check me-1"></i>Approve & Send Form
                    </button>
                    <button type="button" class="btn btn-danger d-none" id="rejectFromModalBtn">
                        <i class="fe fe-x me-1"></i>Reject
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Invite Modal -->
    <div class="modal fade" id="inviteModal" tabindex="-1" aria-labelledby="inviteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="inviteModalLabel">
                        <i class="fe fe-send text-primary me-2"></i>Send Invitation
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        Enter the email address to send a tenant application form link directly.
                    </p>
                    <div class="mb-3">
                        <label for="inviteEmail" class="form-label fw-semibold">
                            Email Address <span class="text-danger">*</span>
                        </label>
                        <input type="email" class="form-control" id="inviteEmail" placeholder="tenant@example.com">
                        <div class="invalid-feedback" id="inviteEmailError"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center" id="inviteSubmitBtn"
                        onclick="sendInvitation()">
                        <span class="btn-text"><i class="fe fe-send me-1"></i> Send Invite</span>
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
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
                text: "This will approve the application and ready for processing.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Approve!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loader
                    Swal.fire({
                        title: 'Processing...',
                        html: 'Approving application and sending email.<br><small class="text-muted">This may take a moment.</small>',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: `/admin/applications/${id}/approve-single`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Approved!',
                                html: response.message,
                                confirmButtonColor: '#28a745'
                            });
                            singleEmailTable.ajax.reload();
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed!',
                                text: xhr.responseJSON?.message || 'An error occurred',
                                confirmButtonColor: '#dc3545'
                            });
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

        // View Single Email Application Details
        function viewSingleEmailDetails(id) {
            // Show modal
            $('#singleEmailModal').modal('show');

            // Reset modal content to loading state
            $('#singleEmailDetails').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);

            // Load application details
            $.ajax({
                url: `/admin/applications/${id}`,
                type: 'GET',
                success: function(response) {
                    displaySingleEmailDetails(response);
                },
                error: function(xhr) {
                    $('#singleEmailDetails').html(`
                        <div class="alert alert-danger">
                            <i class="fe fe-alert-triangle me-2"></i>
                            Failed to load application details. Please try again.
                        </div>
                    `);
                }
            });
        }

        // Display Single Email Application Details in Modal
        function displaySingleEmailDetails(application) {
            // Format dates
            const formatDate = (dateStr) => {
                if (!dateStr) return 'N/A';
                return new Date(dateStr).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            };

            // Build document section if documents exist
            let documentsHtml = '';
            const hasDocuments = application.passport_copy_url || application.visa_document_url || 
                                application.front_id_document_url || application.back_id_document_url;

            if (hasDocuments) {
                documentsHtml = `
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fe fe-file-text text-info me-2"></i>Documents
                        </h6>
                        <div class="row">
                            ${application.passport_copy_url ? `
                                <div class="col-md-3 mb-3">
                                    <small class="text-muted d-block">Passport Copy</small>
                                    <a href="${application.passport_copy_url}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fe fe-eye me-1"></i>View
                                    </a>
                                </div>
                            ` : ''}
                            ${application.visa_document_url ? `
                                <div class="col-md-3 mb-3">
                                    <small class="text-muted d-block">Visa Document</small>
                                    <a href="${application.visa_document_url}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fe fe-eye me-1"></i>View
                                    </a>
                                </div>
                            ` : ''}
                            ${application.front_id_document_url ? `
                                <div class="col-md-3 mb-3">
                                    <small class="text-muted d-block">Front ID</small>
                                    <a href="${application.front_id_document_url}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fe fe-eye me-1"></i>View
                                    </a>
                                </div>
                            ` : ''}
                            ${application.back_id_document_url ? `
                                <div class="col-md-3 mb-3">
                                    <small class="text-muted d-block">Back ID</small>
                                    <a href="${application.back_id_document_url}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fe fe-eye me-1"></i>View
                                    </a>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            }

            // Build employer info section if exists
            let employerHtml = '';
            if (application.employer_info && Object.keys(application.employer_info).length > 0) {
                const employer = application.employer_info;
                employerHtml = `
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fe fe-briefcase text-info me-2"></i>Employer Information
                        </h6>
                        <div class="row">
                            ${employer.company_name ? `
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">Company Name</small>
                                    <strong>${employer.company_name}</strong>
                                </div>
                            ` : ''}
                            ${employer.contact_person ? `
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">Contact Person</small>
                                    <strong>${employer.contact_person}</strong>
                                </div>
                            ` : ''}
                            ${employer.phone ? `
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">Phone</small>
                                    <strong>${employer.phone}</strong>
                                </div>
                            ` : ''}
                            ${employer.email ? `
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">Email</small>
                                    <strong>${employer.email}</strong>
                                </div>
                            ` : ''}
                            ${employer.address ? `
                                <div class="col-md-12 mb-3">
                                    <small class="text-muted d-block">Address</small>
                                    <strong>${employer.address}</strong>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            }

            // Build sponsor info section if exists
            let sponsorHtml = '';
            if (application.sponsor_name) {
                sponsorHtml = `
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fe fe-users text-info me-2"></i>Sponsor Information
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Sponsor Name</small>
                                <strong>${application.sponsor_name || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Relationship</small>
                                <strong>${application.sponsor_relationship || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Phone</small>
                                <strong>${application.sponsor_phone || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Email</small>
                                <strong>${application.sponsor_email || 'N/A'}</strong>
                            </div>
                            <div class="col-md-12 mb-3">
                                <small class="text-muted d-block">Address</small>
                                <strong>${[
                                    application.sponsor_city,
                                    application.sponsor_state,
                                    application.sponsor_zipcode,
                                    application.sponsor_country
                                ].filter(Boolean).join(', ') || 'N/A'}</strong>
                            </div>
                            ${application.is_j1_sponsor ? `
                                <div class="col-md-12 mb-3">
                                    <small class="text-muted d-block">J-1 Sponsor</small>
                                    <span class="badge bg-info">${application.is_j1_sponsor}</span>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            }

            const html = `
                <div class="application-details">
                    <!-- Personal Information -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fe fe-user text-info me-2"></i>Personal Information
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Full Name</small>
                                <strong>${[application.first_name, application.middle_name, application.last_name].filter(Boolean).join(' ') || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Email</small>
                                <strong>${application.email || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Phone</small>
                                <strong>${application.phone || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Date of Birth</small>
                                <strong>${formatDate(application.date_of_birth)}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Country of Origin</small>
                                <strong>${application.country_of_origin || 'N/A'}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Travel Information -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fe fe-calendar text-info me-2"></i>Travel Information
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Arrival Date</small>
                                <strong>${formatDate(application.arrival_date)}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Departure Date</small>
                                <strong>${formatDate(application.departure_date)}</strong>
                            </div>
                        </div>
                    </div>

                    ${documentsHtml}
                    ${employerHtml}
                    ${sponsorHtml}

                    <!-- Additional Notes -->
                    ${application.notes ? `
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="fe fe-message-square text-info me-2"></i>Additional Notes
                            </h6>
                            <div class="alert alert-light border">
                                ${application.notes}
                            </div>
                        </div>
                    ` : ''}

                    <!-- Application Status -->
                    <div class="mb-3">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fe fe-info text-info me-2"></i>Application Status
                        </h6>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <small class="text-muted d-block">Status</small>
                                <span class="badge p-2 bg-${application.status === 'pending' ? 'warning' : application.status === 'approved' ? 'success' : 'danger'}">
                                    ${application.status.charAt(0).toUpperCase() + application.status.slice(1).replace('_', ' ')}
                                </span>
                            </div>
                            ${application.application_type ? `
                                <div class="col-md-4 mb-2">
                                    <small class="text-muted d-block">Application Type</small>
                                    <strong>${application.application_type}</strong>
                                </div>
                            ` : ''}
                            ${application.application_number ? `
                                <div class="col-md-4 mb-2">
                                    <small class="text-muted d-block">Application Number</small>
                                    <strong>${application.application_number}</strong>
                                </div>
                            ` : ''}
                            <div class="col-md-4 mb-2">
                                <small class="text-muted d-block">Submitted At</small>
                                <strong>${new Date(application.created_at).toLocaleString()}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('#singleEmailDetails').html(html);

            // Show/hide action buttons based on status
            if (application.status === 'pending') {
                $('#approveFromModalBtn').removeClass('d-none').off('click').on('click', function() {
                    $('#singleEmailModal').modal('hide');
                    approveSingleEmail(application.id);
                });
                $('#rejectFromModalBtn').removeClass('d-none').off('click').on('click', function() {
                    $('#singleEmailModal').modal('hide');
                    rejectApplication(application.id);
                });
            } else {
                $('#approveFromModalBtn').addClass('d-none');
                $('#rejectFromModalBtn').addClass('d-none');
            }
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
                    Swal.fire({
                        title: 'Processing...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: `/admin/applications/${id}/reject`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Rejected!',
                                text: response.message
                            });
                            singleEmailTable.ajax.reload();
                            reservationTable.ajax.reload();
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: xhr.responseJSON?.message || 'An error occurred'
                            });
                        }
                    });
                }
            });
        }

        // Show Invitation Modal
        function showInviteModal() {
            $('#inviteEmail').val('').removeClass('is-invalid');
            $('#inviteEmailError').text('');
            $('#inviteModal').modal('show');
        }

        // Send Invitation
        function sendInvitation() {
            const email = $('#inviteEmail').val().trim();

            // Basic validation
            $('#inviteEmail').removeClass('is-invalid');
            if (!email) {
                $('#inviteEmail').addClass('is-invalid');
                $('#inviteEmailError').text('Email is required.');
                return;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                $('#inviteEmail').addClass('is-invalid');
                $('#inviteEmailError').text('Please enter a valid email address.');
                return;
            }

            // Show loading
            const btn = $('#inviteSubmitBtn');
            btn.prop('disabled', true);
            btn.find('.btn-text').addClass('d-none');
            btn.find('.spinner-border').removeClass('d-none');

            $.ajax({
                url: "{{ route('tenants.applications.invite') }}",
                type: 'POST',
                data: {
                    email: email,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    btn.prop('disabled', false);
                    btn.find('.btn-text').removeClass('d-none');
                    btn.find('.spinner-border').addClass('d-none');

                    if (response.success) {
                        $('#inviteModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Invitation Sent!',
                            html: `Form link has been sent to <strong>${email}</strong>`,
                            confirmButtonColor: '#28a745'
                        });
                        singleEmailTable.ajax.reload();
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false);
                    btn.find('.btn-text').removeClass('d-none');
                    btn.find('.spinner-border').addClass('d-none');

                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        const firstError = Object.values(errors)[0][0];
                        $('#inviteEmail').addClass('is-invalid');
                        $('#inviteEmailError').text(firstError);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed!',
                            text: xhr.responseJSON?.message || 'An error occurred'
                        });
                    }
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

        /* Single Email Modal Styling */
        #singleEmailModal .modal-content {
            border-radius: 10px;
            overflow: scroll;
        }

        #singleEmailModal .modal-header {
            background: linear-gradient(135deg, #17a2b8 0%, #6dd5ed 100%);
            border: none;
        }

        #singleEmailModal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        .application-details h6 {
            color: #333;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .application-details small.text-muted {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .application-details strong {
            font-size: 0.95rem;
            color: #495057;
        }

        /* Scrollbar Styling for Single Email Modal */
        #singleEmailModal .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        #singleEmailModal .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        #singleEmailModal .modal-body::-webkit-scrollbar-thumb {
            background: #17a2b8;
        }

        #singleEmailModal .modal-body::-webkit-scrollbar-thumb:hover {
            background: #138496;
        }

        /* Single Email Modal Scroll Fix */
        #singleEmailModal .modal-dialog {
            max-height: 90vh;
        }

        #singleEmailModal .modal-content {
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        #singleEmailModal .modal-body {
            overflow-y: auto;
            max-height: calc(90vh - 140px);
        }
    </style>
@endpush
