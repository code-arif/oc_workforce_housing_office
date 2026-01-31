@extends('backend.app', ['title' => 'Add Lease'])
@section('title', 'Create New Leases')
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
                    {{-- <div class="step-item" data-step="4">
                        <div class="step-circle">
                            <i class="fe fe-shield"></i>
                        </div>
                        <div class="step-label">RENTER'S INSURANCE</div>
                        <div class="step-line"></div>
                    </div> --}}
                    <div class="step-item" data-step="4">
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
                            <!-- STEP 1: PROPERTY DETAILS -->
                            <div class="step-content active" id="step-1">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="step-sidebar">
                                            <div class="step-number">1</div>
                                            <div class="step-info">
                                                <h6>Property Details</h6>
                                                <p>Select property, unit, room, and bed for the lease.</p>
                                            </div>
                                        </div>

                                        <div class="step-main-content">
                                            <!-- Hierarchical Property Selection -->
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Property <span class="text-danger">*</span></label>
                                                    <select class="form-select select3" id="property_id" name="property_id" required>
                                                        <option value="">Select Property</option>
                                                        @forelse ($properties as $property)
                                                        <option value="{{ $property->id }}">{{ $property->name }}</option>
                                                        @empty
                                                        <option value="">No Property Found</option>
                                                        @endforelse
                                                    </select>
                                                </div>
                                                <div class="col-md-6" id="unitFieldContainer">
                                                    <label class="form-label">Unit <span class="text-danger bed-required-marker">*</span></label>
                                                    <select class="form-select" id="unit_id" name="unit_id" disabled>
                                                        <option value="">Select Property First</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row mb-4" id="roomBedFieldContainer">
                                                <div class="col-md-6">
                                                    <label class="form-label">Room <span class="text-danger bed-required-marker">*</span></label>
                                                    <select class="form-select" id="room_id" name="room_id" disabled>
                                                        <option value="">Select Unit First</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Bed <span class="text-danger bed-required-marker">*</span></label>
                                                    <select class="form-select" id="bed_id" name="bed_id" disabled>
                                                        <option value="">Select Room First</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Assign Bed Later Option -->
                                            <div class="form-check mb-3" id="assignBedLaterSection">
                                                <input class="form-check-input" type="checkbox" id="assign_bed_later" name="assign_bed_later">
                                                <label class="form-check-label" for="assign_bed_later">
                                                    <strong>Assign bed later</strong> - Create and sign lease without specifying a bed now. 
                                                    <small class="text-muted d-block">Bed can be assigned from tenant profile when tenant arrives.</small>
                                                </label>
                                            </div>

                                            <!-- Selected Property Info -->
                                            <div class="alert alert-info" id="selectedPropertyInfo" style="display: none;">
                                                <strong>Selected:</strong> <span id="fullPropertyPath"></span>
                                            </div>

                                            <!-- Pending Bed Assignment Alert -->
                                            <div class="alert alert-warning" id="pendingBedAssignmentInfo" style="display: none;">
                                                <i class="fe fe-alert-triangle me-2"></i>
                                                <strong>Note:</strong> Bed will be assigned later from the tenant's profile. The lease can still be signed and the tenant can move in once a bed is assigned.
                                            </div>

                                            <hr class="my-4">

                                            <!-- Lease Term Selection -->
                                            <h5 class="mb-4">SELECT LEASE TERM</h5>
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <label class="form-label">Lease Term Type <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="lease_term_type" name="lease_term_type" required>
                                                        <option value="">Select Lease Term</option>
                                                        @foreach ($terms as $term)
                                                            <option value="{{$term->id}}" data-type="{{$term->term_type ?? 'fixed'}}"
                                                                data-start="{{$term->blanket_start_date ?? ''}}" data-end="{{$term->blanket_end_date ?? ''}}">{{$term->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Lease Type Visual Display -->
                                            <div class="lease-type-selection" id="leaseTypeDisplay" style="display: none;">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="lease-type-card" id="fixedTermCard" data-type="fixed">
                                                            <div class="lease-type-header">
                                                                <i class="fe fe-calendar"></i>
                                                                <div class="badge bg-primary">SELECTED</div>
                                                            </div>
                                                            <h6>FIXED TERM LEASE</h6>
                                                            <p class="text-muted">This lease has a fixed start date and will expire after a fixed end date.</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Expected Move in Date <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control datepicker2" id="actual_move_in" name="actual_move_in" required>
                                                        <small class="text-muted"></small>
                                                    </div>
                                                    {{-- <div class="col-md-6">
                                                        <div class="lease-type-card" id="monthToMonthCard" data-type="month">
                                                            <div class="lease-type-header">
                                                                <i class="fe fe-calendar"></i>
                                                                <div class="badge bg-primary">SELECTED</div>
                                                            </div>
                                                            <h6>MONTH-TO-MONTH LEASE</h6>
                                                            <p class="text-muted">This lease has a start date but no fixed end date. It automatically renews each month.</p>
                                                        </div>
                                                    </div> --}}
                                                </div>
                                            </div>

                                            <!-- Date Selection -->
                                            <div class="row mt-4" id="dateSelectionSection" style="display: none;">
                                                <div class="col-md-6">
                                                    <label class="form-label">Lease Begin Date <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control datepicker2" id="start_date" name="start_date" required>
                                                    <small class="text-muted" id="startDateHint"></small>
                                                </div>
                                                <div class="col-md-6" id="endDateField">
                                                    <label class="form-label">Lease End Date <span class="text-danger" id="endDateRequired">*</span></label>
                                                    <input type="text" class="form-control datepicker2" id="end_date" name="end_date">
                                                    <small class="text-muted" id="endDateHint"></small>
                                                </div>
                                            </div>

                                            {{-- <div class="form-check mt-3" id="switchToMonthSection" style="display: none;">
                                                <input class="form-check-input" type="checkbox" id="switchToMonth">
                                                <label class="form-check-label" for="switchToMonth">
                                                    Switch to Month-to-Month at the end of the lease
                                                </label>
                                            </div> --}}

                                            <div class="d-flex justify-content-end mt-4">
                                                <button type="button" class="btn btn-primary" id="step1NextBtn" onclick="validateAndNextStep(2)" disabled>Next: Set Rental Terms</button>
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
                                            <!-- Property Info Banner -->
                                            <div class="property-info-banner mb-4" id="step2PropertyBanner">
                                                <i class="fe fe-info-circle me-2"></i>
                                                <span id="step2PropertyInfo">Please complete Step 1 first</span>
                                            </div>

                                            <!-- Deposit Section -->
                                            <div class="deposit-section mb-4">
                                                <h5 class="section-title">Deposit</h5>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Deposit Amount <span class="text-danger">*</span></label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">$</span>
                                                            <input type="number" class="form-control" id="deposit_amount" name="deposit_amount" value="0" min="0" step="0.01" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Deposit Due Date <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control datepicker2" id="deposit_due_date" name="deposit_due_date" required>
                                                    </div>
                                                </div>
                                                <div class="form-check mt-3">
                                                    <input class="form-check-input" type="checkbox" id="deposit_collected" name="deposit_collected">
                                                    <label class="form-check-label" for="depositCollected">
                                                        I have already collected the deposit, mark as paid
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Rent Section -->
                                            <div class="rent-section">
                                                <h5 class="section-title">Rent</h5>
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Payment Frequency <span class="text-danger">*</span></label>
                                                        <select class="form-select" id="payment_frequency" name="payment_frequency" required>
                                                            <option value="WEEKLY">Weekly</option>
                                                            <option value="MONTHLY" selected>Monthly</option>
                                                            <option value="YEARLY">Yearly</option>
                                                            <option value="CUSTOM">Custom</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Rent Amount <span class="text-danger">*</span></label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">$</span>
                                                            <input type="number" class="form-control" id="rent_amount" name="rent_amount" value="0" min="0" step="0.01" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Rent Due on the <span class="text-danger">*</span></label>
                                                        <div class="d-flex align-items-center" id="standardDueDayContainer">
                                                            <select class="form-select" id="due_day" name="due_day" required>
                                                                <option value="1" selected>1st</option>
                                                                <option value="5">5th</option>
                                                                <option value="10">10th</option>
                                                                <option value="14">14th</option>
                                                                <option value="15">15th</option>
                                                                <option value="20">20th</option>
                                                                <option value="25">25th</option>
                                                                <option value="30">30th</option>
                                                            </select>
                                                            <span class="ms-2 text-muted">of every month</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6" id="standardFirstInvoiceContainer">
                                                        <label class="form-label">First Rental Invoice Due <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control datepicker2" id="first_invoice_date" name="first_invoice_date" required>
                                                        <small class="text-muted">First invoice will be created with this due date</small>
                                                    </div>
                                                </div>

                                                <!-- Custom Payment Dates Section -->
                                                <div class="custom-payment-section" id="customPaymentSection" style="display: none;">
                                                    <div class="card border-primary mb-3">
                                                        <div class="card-header bg-primary-light d-flex justify-content-between align-items-center">
                                                            <h6 class="mb-0"><i class="fe fe-calendar me-2"></i>Custom Payment Schedule</h6>
                                                            <button type="button" class="btn btn-sm btn-primary" id="addCustomPaymentBtn">
                                                                <i class="fe fe-plus me-1"></i> Add Payment Date
                                                            </button>
                                                        </div>
                                                        <div class="card-body">
                                                            <p class="text-muted small mb-3">
                                                                <i class="fe fe-info me-1"></i> Define custom payment dates with individual amounts. Each entry will generate a separate invoice.
                                                            </p>
                                                            
                                                            <div id="customPaymentsList">
                                                                <!-- Custom payment entries will be added here -->
                                                            </div>
                                                            
                                                            <div class="alert alert-info mt-3" id="noCustomPaymentsAlert">
                                                                <i class="fe fe-info me-2"></i>No custom payment dates added. Click "Add Payment Date" to create a custom schedule.
                                                            </div>

                                                            <div class="custom-payments-summary mt-3 pt-3 border-top" id="customPaymentsSummary" style="display: none;">
                                                                <div class="d-flex justify-content-between">
                                                                    <span><strong>Total Payments:</strong> <span id="customPaymentsCount">0</span></span>
                                                                    <span><strong>Total Amount:</strong> $<span id="customPaymentsTotal">0.00</span></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- <div class="additional-fee-btn">
                                                    <button type="button" class="btn btn-link text-primary p-0">
                                                        <i class="fe fe-plus-circle me-1"></i> Add Additional Fee (Optional)
                                                    </button>
                                                </div> --}}
                                            </div>

                                            <div class="d-flex justify-content-between mt-4">
                                                <button type="button" class="btn btn-light" onclick="previousStep(1)">
                                                    <i class="fe fe-chevron-left me-1"></i> Back to Property Details
                                                </button>
                                                <button type="button" class="btn btn-primary" id="step2NextBtn" onclick="validateAndNextStep(3)">
                                                    Next: Add Tenants <i class="fe fe-chevron-right ms-1"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 3: ADD TENANTS -->
                            <div class="step-content" id="step-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="step-sidebar">
                                            <div class="step-number">3</div>
                                            <div class="step-info">
                                                <h6>Select Tenant</h6>
                                                <p>Select the tenant for this lease (one tenant per bed)</p>
                                            </div>
                                        </div>

                                        <div class="unit-summary-card mb-4" id="unitSummaryCard">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0" id="unitNameInfo">Select property first</h6>
                                                <div class="text-end" id="unitPriceInfo">
                                                    <span class="me-3"><i class="fe fe-dollar-sign text-success"></i> <span id="rentDepositDisplay">$0.00 Rent/$0.00 Deposit</span></span>
                                                    <span><i class="fe fe-user text-primary"></i> <span id="tenantCount">0</span> Tenant</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Info about roommates -->
                                        <div class="alert alert-info-transparent border-info mb-4">
                                            <i class="fe fe-info me-2"></i>
                                            <strong>Note:</strong> Each lease is for one tenant and one bed. For roommates sharing a room, create separate leases for each tenant with different beds in the same room.
                                        </div>

                                        <!-- Add Tenant Section -->
                                        <div class="add-tenant-section mb-4">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="card border">
                                                        <div class="card-header bg-light">
                                                            <h6 class="mb-0"><i class="fe fe-user-check me-2"></i>Select Existing Tenant</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Active Tenants</label>
                                                                <select class="form-select select3" id="existingTenantSelect">
                                                                    <option value="">Select a tenant...</option>
                                                                   
                                                                </select>
                                                                <small class="text-muted">Select from existing active tenants</small>
                                                            </div>
                                                            <button type="button" class="btn btn-primary btn-sm w-100" id="addExistingTenantBtn">
                                                                <i class="fe fe-plus me-1"></i> Add Selected Tenant
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="card border">
                                                        <div class="card-header bg-light">
                                                            <h6 class="mb-0"><i class="fe fe-user-plus me-2"></i>Create New Tenant</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row mb-2">
                                                                <div class="col-md-6">
                                                                    <input type="text" class="form-control form-control-sm" id="newTenantFirstName" placeholder="First Name *">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <input type="text" class="form-control form-control-sm" id="newTenantLastName" placeholder="Last Name *">
                                                                </div>
                                                            </div>
                                                            <div class="mb-2">
                                                                <input type="email" class="form-control form-control-sm" id="newTenantEmail" placeholder="Email *">
                                                            </div>
                                                            <div class="mb-3">
                                                                <input type="tel" class="form-control form-control-sm" id="newTenantPhone" placeholder="Phone Number *">
                                                            </div>
                                                            <button type="button" class="btn btn-success btn-sm w-100" id="createNewTenantBtn">
                                                                <i class="fe fe-save me-1"></i> Save & Add Tenant
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Selected Tenant -->
                                        <div class="selected-tenants-section">
                                            <h6 class="mb-3">Selected Tenant for this Lease</h6>
                                            <div class="alert alert-info" id="noTenantsAlert">
                                                <i class="fe fe-info me-2"></i>No tenant selected yet. Please select an existing tenant or create a new one above.
                                            </div>
                                            <div id="selectedTenantsList" class="row" style="display: none;">
                                                <!-- Tenant card will be dynamically added here -->
                                            </div>
                                        </div>

                                        <!-- Rent Split Section -->
                                        {{-- <div class="rent-split-section mt-5">
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
                                        </div> --}}

                                        <!-- Responsibility Options -->
                                        {{-- <div class="responsibility-section mt-4">
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
                                        </div> --}}

                                        <!-- Partial Payment Toggle -->
                                        {{-- <div class="partial-payment-section mt-4">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>Partial Payment</strong>
                                                    <div class="text-muted small">Tenants are permitted to submit partial payments on invoices.</div>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" name="partial_payment" type="checkbox" id="partialPayment" checked>
                                                    <label class="form-check-label" for="partialPayment">
                                                        <span class="badge bg-success" id="partialPaymentBadge">On</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div> --}}
                                    </div>
                                </div>
                            </div>

                            
                            <!-- STEP 4: FINALIZE LEASE -->
                            <div class="step-content" id="step-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="step-sidebar">
                                            <div class="step-number">4</div>
                                            <div class="step-info">
                                                <h6>Finalize Lease</h6>
                                                <p>Review and finalize your lease agreement</p>
                                            </div>
                                        </div>

                                        <div class="step-main-content">
                                            <!-- Property Summary -->
                                            <div class="finalize-section mb-4">
                                                <div class="section-header">
                                                    <h6><i class="fe fe-home me-2"></i>Property Details</h6>
                                                    <button type="button" class="btn btn-sm btn-link" onclick="previousStep(1)">Edit</button>
                                                </div>
                                                <div class="section-content">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="info-item">
                                                                <span class="label">Property</span>
                                                                <span class="value" id="finalPropertyName">-</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="info-item">
                                                                <span class="label">Unit Info</span>
                                                                <span class="value" id="finalUnitInfo">-</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="info-item">
                                                                <span class="label">Lease Type</span>
                                                                <span class="value" id="finalLeaseType">-</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="info-item">
                                                                <span class="label">Lease Period</span>
                                                                <span class="value" id="finalLeasePeriod">-</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tenant Summary -->
                                            <div class="finalize-section mb-4">
                                                <div class="section-header">
                                                    <h6><i class="fe fe-user me-2"></i>Tenant</h6>
                                                    <button type="button" class="btn btn-sm btn-link" onclick="previousStep(3)">Edit</button>
                                                </div>
                                                <div class="section-content">
                                                    <div id="finalTenantsList" class="tenant-list">
                                                        <p class="text-muted mb-0">No tenant selected</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Rent Summary -->
                                            <div class="finalize-section mb-4">
                                                <div class="section-header">
                                                    <h6><i class="fe fe-dollar-sign me-2"></i>Rent & Deposit</h6>
                                                    <button type="button" class="btn btn-sm btn-link" onclick="previousStep(2)">Edit</button>
                                                </div>
                                                <div class="section-content">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="info-item">
                                                                <span class="label">Monthly Rent</span>
                                                                <span class="value text-success" id="finalRentAmount">$0.00</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="info-item">
                                                                <span class="label">Security Deposit</span>
                                                                <span class="value" id="finalDepositAmount">$0.00</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="info-item">
                                                                <span class="label">Payment Frequency</span>
                                                                <span class="value" id="finalPaymentFrequency">Monthly</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="info-item">
                                                                <span class="label">Due Day</span>
                                                                <span class="value" id="finalDueDay">1st of month</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="info-item">
                                                                <span class="label">First Invoice</span>
                                                                <span class="value" id="finalFirstInvoice">-</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="info-item">
                                                                <span class="label">Partial Payment</span>
                                                                <span class="value" id="finalPartialPayment">Allowed</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Lease Template Selection -->
                                            <div class="finalize-section mb-4">
                                                <div class="section-header">
                                                    <h6><i class="fe fe-file-text me-2"></i>Lease Document</h6>
                                                </div>
                                                <div class="section-content">
                                                    <div class="mb-3">
                                                        <label class="form-label">Select Lease Template <span class="text-danger">*</span></label>
                                                        <select class="form-select" id="lease_template_id" name="lease_template_id" required>
                                                            <option value="">Choose a template...</option>
                                                            @if(isset($leaseTemplates) && count($leaseTemplates) > 0)
                                                                @foreach($leaseTemplates as $template)
                                                                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                                                                @endforeach
                                                            @else
                                                                <option value="" disabled>No templates available</option>
                                                            @endif
                                                        </select>
                                                        <small class="text-muted">This template will be used to generate the lease agreement for signing</small>
                                                    </div>
                                                    
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" id="sendForSignature" name="send_for_signature" checked>
                                                        <label class="form-check-label" for="sendForSignature">
                                                            Send lease to tenant(s) for electronic signature
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="sendWelcomeEmail" name="send_welcome_email" checked>
                                                        <label class="form-check-label" for="sendWelcomeEmail">
                                                            Send welcome email to tenant(s)
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                                                <button type="button" class="btn btn-light" onclick="previousStep(3)">
                                                    <i class="fe fe-chevron-left me-1"></i> Back
                                                </button>
                                                <div>
                                                    <button type="button" class="btn btn-outline-secondary me-2" id="saveDraftBtn">
                                                        <i class="fe fe-save me-1"></i> Save as Draft
                                                    </button>
                                                    <button type="button" class="btn btn-primary" id="createLeaseBtn">
                                                        <i class="fe fe-check me-1"></i> Create Lease
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>

                    <!-- RIGHT SIDEBAR - RENTAL SUMMARY -->
                    <div class="col-xl-4 col-lg-5">
                        <div class="rental-summary-card sticky-top">
                            <div class="card">
                                <div class="card-body" style="display: block;">
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
<script src="{{asset('backend/plugins/bootstrap-datepicker/js/datepicker.js')}}"></script>

    @include('backend.layouts.leases.lease._script')
@endpush

@push('styles')
    @include('backend.layouts.leases.lease._style')
@endpush
