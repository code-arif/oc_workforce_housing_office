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
                            <i class="fe fe-mail me-2"></i>Tenant Applications
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

                                        <div class="row g-3">

                                            <div class="col-md-3 d-flex flex-column">
                                                <label class="form-label">Status</label>
                                                <select class="form-select flex-grow-1" id="singleStatusFilter">
                                                    <option value="">All Status</option>
                                                    <option value="pending">Pending</option>
                                                    <option value="approved">Approved</option>
                                                    <option value="rejected">Rejected</option>
                                                </select>
                                            </div>

                                            <div class="col-md-3 d-flex flex-column">
                                                <label class="form-label">Search</label>
                                                <input type="text" class="form-control flex-grow-1"
                                                    id="singleSearchFilter" placeholder="Email, name, phone...">
                                            </div>

                                            <div class="col-md-2 d-flex flex-column">
                                                <label class="form-label">Date From</label>
                                                <input type="date" class="form-control flex-grow-1" id="singleDateFrom">
                                            </div>

                                            <div class="col-md-2 d-flex flex-column">
                                                <label class="form-label">Date To</label>
                                                <input type="date" class="form-control flex-grow-1" id="singleDateTo">
                                            </div>

                                            <div class="col-md-2 d-flex flex-column justify-content-end p-1">
                                                <button type="button" class="btn btn-secondary w-100 p-1 mt-1"
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

                                <div class="d-flex align-items-center gap-2">

                                    <button type="button"
                                        class="btn btn-sm btn-info d-flex align-items-center justify-content-center"
                                        style="height:32px;" onclick="showInvitationListModal()">
                                        Invitation List
                                    </button>

                                    <button type="button"
                                        class="btn btn-sm btn-primary d-flex align-items-center justify-content-center"
                                        style="height:32px;" onclick="showInviteModal()">
                                        <i class="fe fe-send me-1"></i>
                                        Send Invitation
                                    </button>

                                </div>
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


                        <!-- FILTERS -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <div class="row g-3">

                                            <div class="col-md-3 d-flex flex-column">
                                                <label class="form-label">Status</label>
                                                <select class="form-select flex-grow-1" id="reservationStatusFilter">
                                                    <option value="">All Status</option>
                                                    <option value="pending">Pending</option>
                                                    <option value="contacted">Contacted</option>
                                                    <option value="accepted">Accepted</option>
                                                    <option value="declined">Declined</option>
                                                </select>
                                            </div>

                                            <div class="col-md-3 d-flex flex-column">
                                                <label class="form-label">Search</label>
                                                <input type="text" class="form-control flex-grow-1"
                                                    id="reservationSearchFilter" placeholder="Company, email, name...">
                                            </div>

                                            <div class="col-md-2 d-flex flex-column">
                                                <label class="form-label">Date From</label>
                                                <input type="date" class="form-control flex-grow-1"
                                                    id="reservationDateFrom">
                                            </div>

                                            <div class="col-md-2 d-flex flex-column">
                                                <label class="form-label">Date To</label>
                                                <input type="date" class="form-control flex-grow-1"
                                                    id="reservationDateTo">
                                            </div>

                                            <div class="col-md-2 d-flex flex-column justify-content-end p-1">
                                                <button type="button" class="btn btn-secondary w-100 p-1 mt-1"
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
                        aria-label="Close">&times;</button>
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
                    <div class="dropdown me-auto">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button"
                            id="reservationStatusDropdown" data-bs-toggle="dropdown">
                            <i class="fe fe-settings me-1"></i>Update Status
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"
                                    onclick="updateReservationStatus(currentReservationId, 'pending'); return false;"><span
                                        class="badge bg-warning me-2">&nbsp;</span>Pending</a></li>
                            <li><a class="dropdown-item" href="#"
                                    onclick="updateReservationStatus(currentReservationId, 'contacted'); return false;"><span
                                        class="badge bg-info me-2">&nbsp;</span>Contacted</a></li>
                            <li><a class="dropdown-item" href="#"
                                    onclick="updateReservationStatus(currentReservationId, 'accepted'); return false;"><span
                                        class="badge bg-success me-2">&nbsp;</span>Accepted</a></li>
                            <li><a class="dropdown-item" href="#"
                                    onclick="updateReservationStatus(currentReservationId, 'declined'); return false;"><span
                                        class="badge bg-danger me-2">&nbsp;</span>Declined</a></li>
                        </ul>
                    </div>
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
                        aria-label="Close">&times;</button>
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
                    <button type="button" class="btn btn-secondary d-inline-flex align-items-center"
                        data-bs-dismiss="modal">
                        <i class="fe fe-x me-1"></i>
                        <span>Close</span>
                    </button>

                    <button type="button" class="btn btn-success d-inline-flex align-items-center d-none"
                        id="approveFromModalBtn">
                        <i class="fe fe-check me-1"></i>
                        <span>Approve & Send Form</span>
                    </button>

                    <button type="button" class="btn btn-danger d-inline-flex align-items-center d-none"
                        id="rejectFromModalBtn">
                        <i class="fe fe-x me-1"></i>
                        <span>Reject</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Invitation List Modal -->
    <div class="modal fade" id="invitationListModal" tabindex="-1" aria-labelledby="invitationListModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="invitationListModalLabel">
                        <i class="fe fe-list me-2"></i>Sent Invitations
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close">&times;</button>
                </div>

                <div class="modal-body">
                    <div id="invitationListContent">
                        <!-- Invitation list will be loaded here -->
                        <div class="text-center py-4">
                            <div class="spinner-border text-secondary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-end">
                    <button type="button" class="btn btn-secondary d-inline-flex align-items-center"
                        data-bs-dismiss="modal">
                        <i class="fe fe-x me-1"></i>Close
                    </button>
                </div>

            </div>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="button" class="btn btn-primary d-flex align-items-center justify-content-center"
                        id="inviteSubmitBtn" onclick="sendInvitation()">

                        <i class="fe fe-send me-2"></i>
                        <span class="btn-text">Send Invite</span>

                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('backend.layouts.tenants.applications._script')
@endpush

@push('styles')
    @include('backend.layouts.tenants.applications._style')
@endpush
