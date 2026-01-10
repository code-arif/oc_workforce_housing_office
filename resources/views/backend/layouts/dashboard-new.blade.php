@extends('backend.app')

@section('title', 'Dashboard')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app mb-3">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Dashboard</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Index</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- Main Dashboard Content -->
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-lg-8 col-md-12">
                        <!-- Properties Overview Card -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                                <h3 class="card-title mb-0">Properties Overview</h3>
                            </div>

                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-4 border-end">
                                        <h2 class="fw-bold text-primary">15</h2>
                                        <p class="text-muted mb-0">Total Properties</p>
                                    </div>
                                    <div class="col-4 border-end">
                                        <h2 class="fw-bold text-success">353</h2>
                                        <p class="text-muted mb-0">Available Beds</p>
                                    </div>
                                    <div class="col-4">
                                        <h2 class="fw-bold text-warning">28</h2>
                                        <p class="text-muted mb-0">Occupied Beds</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Lists -->
                        <div class="row g-4">
                             <!-- Pending Applications -->
                             <div class="col-lg-6 col-md-6 col-12">
                                <div class="card h-100">
                                    <div class="card-header bg-light">
                                        <h3 class="card-title mb-0">PENDING APPLICATIONS</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="application-item p-3 border mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="fw-bold mb-0">Emily Davis</h6>
                                                <span class="badge bg-warning px-3 py-2">Pending</span>
                                            </div>
                                            <p class="text-muted small mb-0">
                                                <i class="fas fa-calendar me-1"></i> Applied on Jan 8, 2026
                                            </p>
                                        </div>
                                        <div class="application-item p-3 border mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="fw-bold mb-0">Robert Wilson</h6>
                                                <span class="badge bg-warning px-3 py-2">Pending</span>
                                            </div>
                                            <p class="text-muted small mb-0">
                                                <i class="fas fa-calendar me-1"></i> Applied on Jan 7, 2026
                                            </p>
                                        </div>
                                        <div class="application-item p-3 border mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="fw-bold mb-0">Lisa Anderson</h6>
                                                <span class="badge bg-warning px-3 py-2">Pending</span>
                                            </div>
                                            <p class="text-muted small mb-0">
                                                <i class="fas fa-calendar me-1"></i> Applied on Jan 6, 2026
                                            </p>
                                        </div>

                                        <div class="text-center mt-3">
                                            <a href="#" class="text-primary small">
                                                <i class="fas fa-plus me-1"></i> View All Pending
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Unsigned Applications -->
                            <div class="col-lg-6 col-md-6 col-12">
                                <div class="card h-100">
                                    <div class="card-header bg-light">
                                        <h3 class="card-title mb-0">UNSIGNED APPLICATIONS</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="application-item p-3 border mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="fw-bold mb-0">David Miller</h6>
                                                <span class="badge bg-danger px-3 py-2">Unsigned</span>
                                            </div>
                                            <p class="text-muted small mb-0">
                                                <i class="fas fa-calendar me-1"></i> Lease sent Jan 5, 2026
                                            </p>
                                        </div>
                                        <div class="application-item p-3 border mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="fw-bold mb-0">Jennifer Garcia</h6>
                                                <span class="badge bg-danger px-3 py-2">Unsigned</span>
                                            </div>
                                            <p class="text-muted small mb-0">
                                                <i class="fas fa-calendar me-1"></i> Lease sent Jan 4, 2026
                                            </p>
                                        </div>

                                        <div class="text-center mt-3">
                                            <a href="#" class="text-primary small">
                                                <i class="fas fa-plus me-1"></i> View All Unsigned
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-lg-4 col-md-12">
                        <!-- Recent Tenants -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h3 class="card-title mb-0">RECENT TENANTS</h3>
                            </div>
                            <div class="card-body">
                                <div class="tenant-item p-3 border mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial bg-primary">JS</span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-0">John Smith</h6>
                                            <p class="text-muted small mb-0">Unit A-101, Bed 1</p>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-calendar me-1"></i> Joined Jan 5, 2026
                                    </p>
                                </div>
                                <div class="tenant-item p-3 border mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial bg-success">SJ</span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-0">Sarah Johnson</h6>
                                            <p class="text-muted small mb-0">Unit B-205, Bed 2</p>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-calendar me-1"></i> Joined Jan 3, 2026
                                    </p>
                                </div>
                                <div class="tenant-item p-3 border mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial bg-info">MB</span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-0">Michael Brown</h6>
                                            <p class="text-muted small mb-0">Unit C-103, Bed 1</p>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-calendar me-1"></i> Joined Jan 1, 2026
                                    </p>
                                </div>

                                <div class="text-center mt-3">
                                    <a href="#" class="text-primary small">
                                        <i class="fas fa-plus me-1"></i> View All Tenants
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Invoices -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h3 class="card-title mb-0">RECENT INVOICES</h3>
                            </div>
                            <div class="card-body">
                                <div class="invoice-item p-3 border mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold mb-0">INV-2026-001</h6>
                                        <span class="badge bg-success px-3 py-2">Paid</span>
                                    </div>
                                    <p class="text-muted small mb-1">
                                        <i class="fas fa-user me-1"></i> John Smith
                                    </p>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-calendar me-1"></i> Jan 5, 2026 - $1,200.00
                                    </p>
                                </div>
                                <div class="invoice-item p-3 border mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold mb-0">INV-2026-002</h6>
                                        <span class="badge bg-success px-3 py-2">Paid</span>
                                    </div>
                                    <p class="text-muted small mb-1">
                                        <i class="fas fa-user me-1"></i> Sarah Johnson
                                    </p>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-calendar me-1"></i> Jan 3, 2026 - $950.00
                                    </p>
                                </div>
                                <div class="invoice-item p-3 border mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold mb-0">INV-2026-003</h6>
                                        <span class="badge bg-success px-3 py-2">Paid</span>
                                    </div>
                                    <p class="text-muted small mb-1">
                                        <i class="fas fa-user me-1"></i> Michael Brown
                                    </p>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-calendar me-1"></i> Jan 1, 2026 - $1,100.00
                                    </p>
                                </div>

                                <div class="text-center mt-3">
                                    <a href="#" class="text-primary small">
                                        <i class="fas fa-plus me-1"></i> View All Invoices
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Due/Overdue Invoices -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h3 class="card-title mb-0">DUE/OVERDUE INVOICES</h3>
                            </div>
                            <div class="card-body">
                                <div class="invoice-item p-3 border mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold mb-0">INV-2025-045</h6>
                                        <span class="badge bg-danger px-3 py-2">Overdue</span>
                                    </div>
                                    <p class="text-muted small mb-1">
                                        <i class="fas fa-user me-1"></i> Emily Davis
                                    </p>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-calendar me-1"></i> Dec 15, 2025 - $800.00
                                    </p>
                                </div>
                                <div class="invoice-item p-3 border mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold mb-0">INV-2025-048</h6>
                                        <span class="badge bg-warning px-3 py-2">Due Today</span>
                                    </div>
                                    <p class="text-muted small mb-1">
                                        <i class="fas fa-user me-1"></i> Robert Wilson
                                    </p>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-calendar me-1"></i> Jan 10, 2026 - $1,050.00
                                    </p>
                                </div>
                                <div class="invoice-item p-3 border mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold mb-0">INV-2025-052</h6>
                                        <span class="badge bg-warning px-3 py-2">Due Soon</span>
                                    </div>
                                    <p class="text-muted small mb-1">
                                        <i class="fas fa-user me-1"></i> Lisa Anderson
                                    </p>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-calendar me-1"></i> Jan 15, 2026 - $720.00
                                    </p>
                                </div>

                                <div class="text-center mt-3">
                                    <a href="#" class="text-primary small">
                                        <i class="fas fa-plus me-1"></i> View All Due Invoices
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- CONTAINER CLOSED -->
@endsection

@push('scripts')
    <script src="https://kit.fontawesome.com/aadff4f1c9.js" crossorigin="anonymous"></script>
@endpush

@push('styles')
@endpush
