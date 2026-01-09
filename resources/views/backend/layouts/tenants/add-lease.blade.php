@extends('backend.app', ['title' => 'Add Lease'])

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- PAGE HEADER -->
                <div class="page-header mb-4">
                    <h1 class="page-title">ADD LEASE</h1>
                </div>

                <!-- PROGRESS STEPS -->
                <div class="steps-container mb-5">
                    <div class="step-item active" data-step="1">
                        <div class="step-circle">
                            <i class="fe fe-home"></i>
                        </div>
                        <div class="step-label">PROPERTY DETAIL</div>
                        <div class="step-line"></div>
                    </div>
                    <div class="step-item" data-step="2">
                        <div class="step-circle">
                            <i class="fe fe-layers"></i>
                        </div>
                        <div class="step-label">SET RENTAL TERMS</div>
                        <div class="step-line"></div>
                    </div>
                    <div class="step-item" data-step="3">
                        <div class="step-circle">
                            <i class="fe fe-users"></i>
                        </div>
                        <div class="step-label">ADD TENANTS</div>
                        <div class="step-line"></div>
                    </div>
                    <div class="step-item" data-step="4">
                        <div class="step-circle">
                            <i class="fe fe-shield"></i>
                        </div>
                        <div class="step-label">RENTER'S INSURANCE</div>
                        <div class="step-line"></div>
                    </div>
                    <div class="step-item" data-step="5">
                        <div class="step-circle">
                            <i class="fe fe-check-square"></i>
                        </div>
                        <div class="step-label">FINALIZE LEASE</div>
                    </div>
                </div>

                <!-- FORM CONTAINER -->
                <div class="row">
                    <div class="col-xl-8 col-lg-7">
                        <form id="leaseForm">
                            <!-- STEP 1: SELECT LEASE TERM -->
                            <div class="step-content active" id="step-1">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="step-sidebar">
                                            <div class="step-number">1</div>
                                            <div class="step-info">
                                                <h6>Lease Dates</h6>
                                                <p>Select the type of lease and lease dates.</p>
                                            </div>
                                        </div>

                                        <div class="step-main-content">
                                            <h5 class="mb-4">SELECT LEASE TERM</h5>

                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <label class="form-label">Select Unit <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="unit_id" name="unit_id" required>
                                                        <option value="">1st Floor A-2</option>
                                                        <option value="">2nd Floor B-1</option>
                                                        <option value="">3rd Floor C-3</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Select Lease Term <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="lease_term_type" name="lease_term_type" required>
                                                        <option value="new">New Term</option>
                                                        <option value="renewal">Renewal</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="lease-type-selection">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="lease-type-card active" data-type="fixed">
                                                            <div class="lease-type-header">
                                                                <i class="fe fe-calendar"></i>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" name="lease_type" value="fixed" id="fixedTerm" checked>
                                                                </div>
                                                            </div>
                                                            <h6>FIXED TERM</h6>
                                                            <p class="text-muted">This lease has a fixed start date and will expire after a fixed end date.</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="lease-type-card" data-type="month">
                                                            <div class="lease-type-header">
                                                                <i class="fe fe-calendar"></i>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" name="lease_type" value="month_to_month" id="monthToMonth">
                                                                </div>
                                                            </div>
                                                            <h6>MONTH-TO-MONTH</h6>
                                                            <p class="text-muted">This lease has a start date but no fixed end date. It should automatically renew each month.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mt-4">
                                                <div class="col-md-6">
                                                    <label class="form-label">Lease Begin Date <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control" id="start_date" name="start_date" value="2026-01-08" required>
                                                </div>
                                                <div class="col-md-6" id="endDateField">
                                                    <label class="form-label">Lease End Date <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control" id="end_date" name="end_date" placeholder="Select Date">
                                                </div>
                                            </div>

                                            <div class="form-check mt-3">
                                                <input class="form-check-input" type="checkbox" id="switchToMonth">
                                                <label class="form-check-label" for="switchToMonth">
                                                    Switch to Month-to-Month at the end of the lease
                                                </label>
                                            </div>

                                            <div class="d-flex justify-content-end mt-4">
                                                <button type="button" class="btn btn-primary" onclick="nextStep(2)">Next</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 2: RENT AND DEPOSITS -->
                            <div class="step-content" id="step-2">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="step-sidebar">
                                            <div class="step-number">2</div>
                                            <div class="step-info">
                                                <h6>Rent and Deposits</h6>
                                                <p>Provide payment details and due dates.</p>
                                            </div>
                                        </div>

                                        <div class="step-main-content">
                                            <!-- Deposit Section -->
                                            <div class="deposit-section mb-4">
                                                <h5 class="section-title">Deposit</h5>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Deposit</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">$</span>
                                                            <input type="number" class="form-control" id="deposit_amount" name="deposit_amount" value="250">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Due On <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control" id="deposit_due_date" value="2026-01-15">
                                                    </div>
                                                </div>
                                                <div class="form-check mt-3">
                                                    <input class="form-check-input" type="checkbox" id="depositCollected">
                                                    <label class="form-check-label" for="depositCollected">
                                                        I have already collected the deposit, just create a record and mark as paid.
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Rent Section -->
                                            <div class="rent-section">
                                                <h5 class="section-title">Rent</h5>
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Payment Frequency</label>
                                                        <select class="form-select" id="payment_frequency" name="payment_frequency">
                                                            <option value="monthly">Monthly</option>
                                                            <option value="weekly">Weekly</option>
                                                            <option value="biweekly">Bi-weekly</option>
                                                            <option value="bimonthly">Bi-monthly</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Rent</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">$</span>
                                                            <input type="number" class="form-control" id="rent_amount" name="rent_amount" value="456">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Due on the</label>
                                                        <div class="d-flex align-items-center">
                                                            <select class="form-select" id="due_day" name="due_day">
                                                                <option value="14">14th</option>
                                                                <option value="1">1st</option>
                                                                <option value="15">15th</option>
                                                                <option value="30">30th</option>
                                                            </select>
                                                            <span class="ms-2 text-muted">of every month</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">On which date should the first rental invoice be due? <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="first_invoice_date">
                                                        <option value="">Select</option>
                                                        <option value="2026-01-14">January 14, 2026</option>
                                                        <option value="2026-02-14">February 14, 2026</option>
                                                    </select>
                                                    <small class="text-muted">We'll immediately create an unpaid invoice due on whichever date you select. Invoices will continue to generate due from that date onward.</small>
                                                </div>

                                                <div class="additional-fee-btn">
                                                    <button type="button" class="btn btn-link text-primary p-0">
                                                        <i class="fe fe-plus-circle me-1"></i> Add Additional Fee (Optional)
                                                    </button>
                                                </div>

                                                <div class="mt-4">
                                                    <a href="#" class="text-primary" onclick="previousStep(1); return false;">
                                                        <i class="fe fe-chevron-left me-1"></i> Back to Lease Term
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 3: ADD TENANTS -->
                            <div class="step-content" id="step-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="property-info-banner mb-4">
                                            <span>2004 Philadelphia... | 14 Units</span>
                                            <span class="ms-3">Unit: 1st Floor A-2</span>
                                        </div>

                                        <h5 class="mb-4">ADD TENANTS</h5>

                                        <div class="unit-summary-card mb-4">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">1st Floor A-2</h6>
                                                <div class="text-end">
                                                    <span class="me-3"><i class="fe fe-dollar-sign text-success"></i> $456.00 Rent/$250.00 Deposit</span>
                                                    <span><i class="fe fe-users text-primary"></i> 2 Tenant(s)</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tenants-table-container">
                                            <table class="table table-borderless">
                                                <thead>
                                                    <tr>
                                                        <th width="30"></th>
                                                        <th>First Name</th>
                                                        <th>Last Name</th>
                                                        <th>Email</th>
                                                        <th>Mobile</th>
                                                        <th>Screening</th>
                                                        <th>Application Status</th>
                                                        <th width="40"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tenantsTableBody">
                                                    <tr class="tenant-row">
                                                        <td>
                                                            <div class="tenant-number">1</div>
                                                        </td>
                                                        <td><input type="text" class="form-control" placeholder="First Name"></td>
                                                        <td><input type="text" class="form-control" placeholder="Last Name"></td>
                                                        <td><input type="email" class="form-control" placeholder="Email"></td>
                                                        <td><input type="tel" class="form-control" placeholder="Phone Number"></td>
                                                        <td>
                                                            <select class="form-select">
                                                                <option>Select Package</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary">Not applied yet</span>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeTenant(this)">
                                                                <i class="fe fe-x"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr class="tenant-row">
                                                        <td>
                                                            <div class="tenant-number">2</div>
                                                        </td>
                                                        <td><input type="text" class="form-control" placeholder="First Name"></td>
                                                        <td><input type="text" class="form-control" placeholder="Last Name"></td>
                                                        <td><input type="email" class="form-control" placeholder="Email"></td>
                                                        <td><input type="tel" class="form-control" placeholder="Phone Number"></td>
                                                        <td>
                                                            <select class="form-select">
                                                                <option>Select Package</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary">Not applied yet</span>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeTenant(this)">
                                                                <i class="fe fe-x"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                            <button type="button" class="btn btn-link text-primary p-0" onclick="addTenant()">
                                                <i class="fe fe-plus me-1"></i> Add tenant
                                            </button>
                                        </div>

                                        <!-- Rent Split Section -->
                                        <div class="rent-split-section mt-5">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Tenant</th>
                                                                <th colspan="2" class="text-center">Rent</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>No tenant added</td>
                                                                <td width="80">
                                                                    <div class="input-group input-group-sm">
                                                                        <input type="number" class="form-control text-center" value="50">
                                                                        <span class="input-group-text">%</span>
                                                                    </div>
                                                                </td>
                                                                <td width="100">
                                                                    <div class="input-group input-group-sm">
                                                                        <span class="input-group-text">$</span>
                                                                        <input type="number" class="form-control" value="228">
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>No tenant added</td>
                                                                <td>
                                                                    <div class="input-group input-group-sm">
                                                                        <input type="number" class="form-control text-center" value="50">
                                                                        <span class="input-group-text">%</span>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="input-group input-group-sm">
                                                                        <span class="input-group-text">$</span>
                                                                        <input type="number" class="form-control" value="228">
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr class="table-active">
                                                                <td><strong>TOTAL</strong></td>
                                                                <td>
                                                                    <div class="input-group input-group-sm">
                                                                        <input type="number" class="form-control text-center bg-light" value="100" readonly>
                                                                        <span class="input-group-text">%</span>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="input-group input-group-sm">
                                                                        <span class="input-group-text">$</span>
                                                                        <input type="number" class="form-control bg-light" value="456" readonly>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th></th>
                                                                <th colspan="2" class="text-center">Deposit</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td></td>
                                                                <td width="80">
                                                                    <div class="input-group input-group-sm">
                                                                        <input type="number" class="form-control text-center" value="50">
                                                                        <span class="input-group-text">%</span>
                                                                    </div>
                                                                </td>
                                                                <td width="100">
                                                                    <div class="input-group input-group-sm">
                                                                        <span class="input-group-text">$</span>
                                                                        <input type="number" class="form-control" value="125">
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td></td>
                                                                <td>
                                                                    <div class="input-group input-group-sm">
                                                                        <input type="number" class="form-control text-center" value="50">
                                                                        <span class="input-group-text">%</span>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="input-group input-group-sm">
                                                                        <span class="input-group-text">$</span>
                                                                        <input type="number" class="form-control" value="125">
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr class="table-active">
                                                                <td></td>
                                                                <td>
                                                                    <div class="input-group input-group-sm">
                                                                        <input type="number" class="form-control text-center bg-light" value="100" readonly>
                                                                        <span class="input-group-text">%</span>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="input-group input-group-sm">
                                                                        <span class="input-group-text">$</span>
                                                                        <input type="number" class="form-control bg-light" value="250" readonly>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Responsibility Options -->
                                        <div class="responsibility-section mt-4">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="radio" name="responsibility" id="equallyResponsible" checked>
                                                <label class="form-check-label" for="equallyResponsible">
                                                    <strong>All tenants are equally responsible.</strong>
                                                    <div class="text-muted small">If this is selected, we'll send a single invoice to all tenants to share. Regardless of splits, they'll each see the full rental amount. If any of the tenants fails to pay, they'll all be notified.</div>
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="responsibility" id="individualResponsible">
                                                <label class="form-check-label" for="individualResponsible">
                                                    <strong>Each tenant is only responsible for his/her portion.</strong>
                                                    <div class="text-muted small">If this is selected, each tenant will receive their own individual invoice equal to their split of the rent. They cannot see or make payments on one another's invoices.</div>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Partial Payment Toggle -->
                                        <div class="partial-payment-section mt-4">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>Partial Payment</strong>
                                                    <div class="text-muted small">Tenants are permitted to submit partial payments on invoices.</div>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="partialPayment" checked>
                                                    <label class="form-check-label" for="partialPayment">
                                                        <span class="badge bg-success">On</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 4: RENTER'S INSURANCE -->
                            <div class="step-content" id="step-4">
                                <div class="card">
                                    <div class="card-body text-center py-5">
                                        <i class="fe fe-shield text-primary" style="font-size: 64px;"></i>
                                        <h5 class="mt-3">Renter's Insurance</h5>
                                        <p class="text-muted">This feature will be available soon</p>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 5: FINALIZE LEASE -->
                            <div class="step-content" id="step-5">
                                <div class="card">
                                    <div class="card-body text-center py-5">
                                        <i class="fe fe-check-circle text-success" style="font-size: 64px;"></i>
                                        <h5 class="mt-3">Finalize Lease</h5>
                                        <p class="text-muted">Review and finalize your lease agreement</p>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>

                    <!-- RIGHT SIDEBAR - RENTAL SUMMARY -->
                    <div class="col-xl-4 col-lg-5">
                        <div class="rental-summary-card sticky-top">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">Rental Summary</h5>

                                    <div class="summary-amount text-center mb-4">
                                        <h2 class="text-primary mb-2">$0.00</h2>
                                        <p class="text-muted mb-1">Total rent from lease</p>
                                        <p class="text-muted small" id="leaseDuration">for Oops! Could not calculate duration!</p>
                                    </div>

                                    <div class="summary-details">
                                        <div class="detail-item mb-3">
                                            <span class="text-muted">Unit:</span>
                                            <strong id="summaryUnit">1st Floor A-2</strong>
                                        </div>

                                        <div class="detail-item mb-3">
                                            <span class="text-muted">Rental lease for</span>
                                            <strong id="summaryLeaseType">Fixed Term</strong>
                                        </div>

                                        <div class="detail-item mb-3">
                                            <span class="text-muted">Rental starts on</span>
                                            <strong id="summaryStartDate">Jan 08, 2026</strong>
                                        </div>

                                        <div class="detail-item mb-3" id="summaryEndDateContainer">
                                            <span class="text-muted">Rental ends on</span>
                                            <strong id="summaryEndDate">N/A</strong>
                                        </div>

                                        <div class="detail-item mb-3" id="summaryRentContainer" style="display: none;">
                                            <span class="text-muted">Rent</span>
                                            <strong id="summaryRent">N/A</strong>
                                        </div>

                                        <div class="detail-item mb-3" id="summaryDepositContainer" style="display: none;">
                                            <span class="text-muted">Deposit</span>
                                            <strong id="summaryDeposit">N/A</strong>
                                        </div>

                                        <div class="summary-footer mt-4" id="summaryInvoices" style="display: none;">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted">Total # Invoices</span>
                                                <strong>0</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">To Be Collected</span>
                                                <strong>$0.00</strong>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-outline-primary w-100 mt-4">
                                        View/Edit Rent Schedule
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BOTTOM NAVIGATION -->
                <div class="form-navigation">
                    <button type="button" class="btn btn-light" onclick="window.location='{{ route('tenants.index') }}'">
                        Cancel
                    </button>
                    <div>
                        <button type="button" class="btn btn-light me-2" id="backBtn" onclick="previousStep()" style="display: none;">
                            Back
                        </button>
                        <button type="button" class="btn btn-secondary me-2" id="saveBtn" style="display: none;">
                            Save
                        </button>
                        <button type="button" class="btn btn-primary" id="nextBtn" onclick="nextStep()">
                            Next
                        </button>
                        <button type="button" class="btn btn-primary" id="addTenantBtn" style="display: none;">
                            Add Tenant(s)
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentStep = 1;
        const totalSteps = 5;

        $(document).ready(function() {
            updateNavigationButtons();

            // Lease type card click handler
            $('.lease-type-card').on('click', function() {
                $('.lease-type-card').removeClass('active');
                $(this).addClass('active');
                $(this).find('input[type="radio"]').prop('checked', true);

                const leaseType = $(this).data('type');
                handleLeaseTypeChange(leaseType);
            });

            // Radio button change handler
            $('input[name="lease_type"]').on('change', function() {
                const leaseType = $(this).val();
                $('.lease-type-card').removeClass('active');
                $(`.lease-type-card[data-type="${leaseType === 'fixed' ? 'fixed' : 'month'}"]`).addClass('active');
                handleLeaseTypeChange(leaseType === 'fixed' ? 'fixed' : 'month');
            });

            // Form field changes
            $('#start_date, #end_date').on('change', updateRentalSummary);
            $('#rent_amount').on('input', updateRentalSummary);
            $('#deposit_amount').on('input', updateRentalSummary);
        });

        function handleLeaseTypeChange(type) {
            if (type === 'month') {
                $('#endDateField').hide();
                $('#switchToMonth').closest('.form-check').hide();
                $('#summaryEndDateContainer').hide();
                $('#leaseDuration').text('for Month-to-Month lease');
            } else {
                $('#endDateField').show();
                $('#switchToMonth').closest('.form-check').show();
                $('#summaryEndDateContainer').show();
                updateRentalSummary();
            }
        }

        function updateRentalSummary() {
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();
            const rentAmount = parseFloat($('#rent_amount').val()) || 0;
            const depositAmount = parseFloat($('#deposit_amount').val()) || 0;

            // Update summary
            $('#summaryStartDate').text(startDate ? formatDate(startDate) : 'N/A');
            $('#summaryEndDate').text(endDate ? formatDate(endDate) : 'N/A');

            if (currentStep >= 2) {
                $('#summaryRent').text(' + rentAmount.toFixed(2) + '/month');
                $('#summaryRentContainer').show();
                $('#summaryDeposit').text(' + depositAmount.toFixed(2));
                $('#summaryDepositContainer').show();
                $('#summaryInvoices').show();
            }

            // Calculate duration
            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
                $('#leaseDuration').text(`for Only ${days} days`);

                // Calculate total rent (very simplified)
                const months = Math.ceil(days / 30);
                const totalRent = rentAmount * months;
                $('.summary-amount h2').text(' + totalRent.toFixed(2));
            }
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = { year: 'numeric', month: 'short', day: '2-digit' };
            return date.toLocaleDateString('en-US', options);
        }

        function nextStep(step) {
            if (step) {
                currentStep = step;
            } else {
                if (currentStep < totalSteps) {
                    currentStep++;
                }
            }

            showStep(currentStep);
            updateNavigationButtons();
            updateRentalSummary();
        }

        function previousStep(step) {
            if (step) {
                currentStep = step;
            } else {
                if (currentStep > 1) {
                    currentStep--;
                }
            }

            showStep(currentStep);
            updateNavigationButtons();
        }

        function showStep(step) {
            // Hide all steps
            $('.step-content').removeClass('active');
            $('.step-item').removeClass('active completed');

            // Show current step
            $(`#step-${step}`).addClass('active');

            // Update step indicators
            for (let i = 1; i <= totalSteps; i++) {
                const $step = $(`.step-item[data-step="${i}"]`);
                if (i < step) {
                    $step.addClass('completed');
                } else if (i === step) {
                    $step.addClass('active');
                }
            }

            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function updateNavigationButtons() {
            // Back button
            if (currentStep === 1) {
                $('#backBtn').hide();
            } else {
                $('#backBtn').show();
            }

            // Next/Save/Add Tenant buttons
            $('#nextBtn').hide();
            $('#saveBtn').hide();
            $('#addTenantBtn').hide();

            if (currentStep === 3) {
                $('#saveBtn').show();
                $('#addTenantBtn').show();
            } else if (currentStep === totalSteps) {
                $('#addTenantBtn').show();
                $('#addTenantBtn').text('Complete');
            } else if (currentStep < 3) {
                $('#nextBtn').show();
            } else {
                $('#nextBtn').show();
            }
        }

        function addTenant() {
            const tenantCount = $('#tenantsTableBody .tenant-row').length + 1;
            const newRow = `
                <tr class="tenant-row">
                    <td>
                        <div class="tenant-number">${tenantCount}</div>
                    </td>
                    <td><input type="text" class="form-control" placeholder="First Name"></td>
                    <td><input type="text" class="form-control" placeholder="Last Name"></td>
                    <td><input type="email" class="form-control" placeholder="Email"></td>
                    <td><input type="tel" class="form-control" placeholder="Phone Number"></td>
                    <td>
                        <select class="form-select">
                            <option>Select Package</option>
                        </select>
                    </td>
                    <td>
                        <span class="badge bg-secondary">Not applied yet</span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeTenant(this)">
                            <i class="fe fe-x"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#tenantsTableBody').append(newRow);
        }

        function removeTenant(btn) {
            $(btn).closest('tr').remove();

            // Renumber tenants
            $('#tenantsTableBody .tenant-row').each(function(index) {
                $(this).find('.tenant-number').text(index + 1);
            });
        }
    </script>
@endpush

@push('styles')
    <style>
        /* Progress Steps */
        .steps-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            padding: 0 20px;
        }

        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            flex: 1;
        }

        .step-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #adb5bd;
            position: relative;
            z-index: 2;
            transition: all 0.3s;
        }

        .step-item.active .step-circle {
            background: #4285f4;
            border-color: #4285f4;
            color: #fff;
        }

        .step-item.completed .step-circle {
            background: #4285f4;
            border-color: #4285f4;
            color: #fff;
        }

        .step-label {
            margin-top: 10px;
            font-size: 11px;
            font-weight: 600;
            color: #6c757d;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .step-item.active .step-label {
            color: #4285f4;
        }

        .step-line {
            position: absolute;
            top: 30px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #dee2e6;
            z-index: 1;
        }

        .step-item:last-child .step-line {
            display: none;
        }

        .step-item.completed .step-line {
            background: #4285f4;
        }

        /* Card Layout */
        .card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .card-body {
            display: flex;
            gap: 30px;
        }

        .step-sidebar {
            width: 200px;
            flex-shrink: 0;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e3f2fd;
            color: #2196F3;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .step-info h6 {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .step-info p {
            font-size: 14px;
            color: #6c757d;
            margin: 0;
        }

        .step-main-content {
            flex: 1;
        }

        /* Lease Type Cards */
        .lease-type-selection {
            margin: 20px 0;
        }

        .lease-type-card {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s;
            height: 100%;
        }

        .lease-type-card:hover {
            border-color: #4285f4;
            background: #f8f9fa;
        }

        .lease-type-card.active {
            border-color: #4285f4;
            background: #e3f2fd;
        }

        .lease-type-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .lease-type-header i {
            font-size: 32px;
            color: #4285f4;
        }

        .lease-type-card h6 {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        /* Sections */
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 20px;
        }

        .deposit-section,
        .rent-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        /* Property Info Banner */
        .property-info-banner {
            background: #e3f2fd;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            color: #1976d2;
        }

        /* Unit Summary Card */
        .unit-summary-card {
            background: #fff3cd;
            padding: 15px 20px;
            border-radius: 6px;
            border-left: 4px solid #ffc107;
        }

        /* Tenants Table */
        .tenants-table-container {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .tenants-table-container table {
            margin-bottom: 0;
        }

        .tenants-table-container th {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #6c757d;
            border-bottom: 2px solid #e9ecef;
            padding: 10px 8px;
        }

        .tenants-table-container td {
            padding: 10px 8px;
            vertical-align: middle;
        }

        .tenant-number {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #4285f4;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
        }

        /* Rent Split Section */
        .rent-split-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }

        .rent-split-section .table {
            margin-bottom: 0;
        }

        .rent-split-section th {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #6c757d;
            border-bottom: 2px solid #dee2e6;
        }

        /* Rental Summary Sidebar */
        .rental-summary-card {
            position: sticky;
            top: 20px;
        }

        .rental-summary-card .card-title {
            font-weight: 600;
            color: #2c3e50;
        }

        .summary-amount h2 {
            font-size: 36px;
            font-weight: 700;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f8f9fa;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .summary-footer {
            border-top: 2px solid #e9ecef;
            padding-top: 15px;
        }

        /* Form Navigation */
        .form-navigation {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            padding: 15px 30px;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        }

        /* Step Content */
        .step-content {
            display: none;
        }

        .step-content.active {
            display: block;
        }

        /* Responsibility Section */
        .responsibility-section .form-check-label {
            cursor: pointer;
        }

        /* Partial Payment Section */
        .partial-payment-section {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 8px;
        }

        /* Form Controls */
        .form-control:focus,
        .form-select:focus {
            border-color: #4285f4;
            box-shadow: 0 0 0 0.2rem rgba(66, 133, 244, 0.25);
        }

        /* Responsive */
        @media (max-width: 991px) {
            .card-body {
                flex-direction: column;
                gap: 20px;
            }

            .step-sidebar {
                width: 100%;
                display: flex;
                align-items: center;
                gap: 15px;
            }

            .step-number {
                margin-bottom: 0;
            }

            .steps-container {
                overflow-x: auto;
                padding: 0 10px;
            }

            .step-label {
                font-size: 9px;
            }

            .rental-summary-card {
                position: static;
                margin-top: 20px;
            }
        }

        @media (max-width: 767px) {
            .step-circle {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .step-line {
                top: 25px;
            }

            .tenants-table-container {
                overflow-x: auto;
            }

            .form-navigation {
                flex-wrap: wrap;
                gap: 10px;
            }
        }
    </style>
@endpush
