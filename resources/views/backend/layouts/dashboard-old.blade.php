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
                    <!-- Left Column - Collection Stats -->
                    <div class="col-lg-8 col-md-12">
                        <!-- Collection Stats Card -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                                <h3 class="card-title mb-0">Collection Stats</h3>
                                <select class="form-select form-select-sm" style="width: 150px">
                                    <option>Dec 2025</option>
                                    <option>Dec 2026</option>
                                    <option>Dec 2027</option>
                                    <option>Dec 2028</option>
                                </select>
                            </div>

                            <div class="card-body">
                                <div class="row align-items-center mb-4">
                                    <!-- Pie Chart + Total -->
                                    <div class="col-lg-4 col-md-5 text-center">
                                        <div class="position-relative d-inline-block">
                                            <canvas id="collectionPieChart" width="250" height="250"></canvas>
                                            <!-- Total Amount in Center -->
                                            <div class="position-absolute top-50 start-50 translate-middle text-center">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Stats Grid -->
                                    <div class="col-lg-8 col-md-7">
                                        <div class="row g-4">
                                            <!-- Collected -->
                                            <div class="col-6 p-3">
                                                <div class="d-flex align-items-center h-100 position-relative border rounded-3 p-3"
                                                    style="border: 0.5px solid #e9ecef !important;">
                                                    <!-- Main Content -->
                                                    <div class="flex-grow-1">
                                                        <!-- Icon + Title (side by side) -->
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i
                                                                class="fa-solid fa-building-columns fa-lg text-primary me-1"></i>
                                                            <h6 class="text-muted mb-0">Collected</h6>
                                                        </div>
                                                        <!-- Amount -->
                                                        <h4 class="fw-bold mb-2">$4,472.00</h4>
                                                        <!-- Badge (Percentage) -->
                                                        <span class="badge bg-light rounded-1 px-3 py-2">30.8%</span>
                                                    </div>
                                                    <a href="#"
                                                        class="position-absolute end-0 p-3 text-muted bg-light rounded"
                                                        style="margin-right: 5px !important">
                                                        <i class="fa-solid fa-chevron-right"></i>
                                                    </a>
                                                </div>
                                            </div>

                                            <!-- Processing -->
                                            <div class="col-6 p-3">
                                                <div class="d-flex align-items-center h-100 position-relative border rounded-3 p-3"
                                                    style="border: 0.5px solid #e9ecef !important;">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="fa-solid fa-clock fa-lg text-warning me-1"></i>
                                                            <h6 class="text-muted mb-0">Processing</h6>
                                                        </div>
                                                        <h4 class="fw-bold mb-2">$0.00</h4>
                                                        <span class="badge bg-light rounded-1 px-3 py-2">0%</span>
                                                    </div>
                                                    <a href="#"
                                                        class="position-absolute end-0 p-3 text-muted bg-light rounded"
                                                        style="margin-right: 5px !important">
                                                        <i class="fa-solid fa-chevron-right"></i>
                                                    </a>
                                                </div>
                                            </div>

                                            <!-- Overdue -->
                                            <div class="col-6 p-3">
                                                <div class="d-flex align-items-center h-100 position-relative border rounded-3 p-3"
                                                    style="border: 0.5px solid #e9ecef !important;">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i
                                                                class="fa-solid fa-exclamation-triangle fa-lg text-danger me-1"></i>
                                                            <h6 class="text-muted mb-0">Overdue</h6>
                                                        </div>
                                                        <h4 class="fw-bold mb-2">$5,778.00</h4>
                                                        <span class="badge bg-light rounded-1 px-3 py-2">39.6%</span>
                                                    </div>
                                                    <a href="#"
                                                        class="position-absolute end-0 p-3 text-muted bg-light rounded" style="margin-right: 5px !important">
                                                        <i class="fa-solid fa-chevron-right"></i>
                                                    </a>
                                                </div>
                                            </div>

                                            <!-- Coming Due -->
                                            <div class="col-6 p-3">
                                                <div class="d-flex align-items-center h-100 position-relative border rounded-3 p-3"
                                                    style="border: 0.5px solid #e9ecef !important;">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="fa-solid fa-calendar-check text-info fa-lg me-1"></i>
                                                            <h6 class="text-muted mb-0">Coming Due</h6>
                                                        </div>
                                                        <h4 class="fw-bold mb-2">$4,350.00</h4>
                                                        <span class="badge bg-light rounded-1 px-3 py-2">29.8%</span>
                                                    </div>
                                                    <a href="#"
                                                        class="position-absolute end-0 p-3 text-muted bg-light rounded" style="margin-right: 5px !important">
                                                        <i class="fa-solid fa-chevron-right"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Application processing --}}
                        <div class="row g-4">
                            <!-- Applications Processing -->
                            <div class="col-lg-6 col-md-6 col-12">
                                <div class="card h-100">
                                    <div class="card-header bg-light">
                                        <h3 class="card-title mb-0">APPLICATIONS PROCESSING</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="application-item p-3 border mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="fw-bold mb-0">Jonathan Latta</h6>
                                                <span class="badge bg-primary px-3 py-2">New</span>
                                            </div>
                                            <p class="text-muted small mb-0">
                                                <i class="fas fa-calendar me-1"></i> Applied on Dec 13, 2025
                                            </p>
                                        </div>
                                        <div class="application-item p-3 border mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="fw-bold mb-0">Jonathan Latta</h6>
                                                <span class="badge bg-primary px-3 py-2">New</span>
                                            </div>
                                            <p class="text-muted small mb-0">
                                                <i class="fas fa-calendar me-1"></i> Applied on Dec 13, 2025
                                            </p>
                                        </div>
                                        <div class="application-item p-3 border mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="fw-bold mb-0">Jonathan Latta</h6>
                                                <span class="badge bg-primary px-3 py-2">New</span>
                                            </div>
                                            <p class="text-muted small mb-0">
                                                <i class="fas fa-calendar me-1"></i> Applied on Dec 13, 2025
                                            </p>
                                        </div>

                                        <div class="text-center mt-3">
                                            <a href="#" class="text-primary small">
                                                <i class="fas fa-plus me-1"></i> View All Applications
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Unsigned Leases -->
                            <div class="col-lg-6 col-md-6 col-12">
                                <div class="card h-100">
                                    <div class="card-header bg-light">
                                        <h3 class="card-title mb-0">UNSIGNED LEASES</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="application-item p-3 border mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="fw-bold mb-0">Jonathan Latta</h6>
                                                <span class="badge bg-primary px-3 py-2">New</span>
                                            </div>
                                            <p class="text-muted small mb-0">
                                                <i class="fas fa-calendar me-1"></i> Applied on Dec 13, 2025
                                            </p>
                                        </div>

                                        <div class="text-center mt-3">
                                            <a href="#" class="text-primary small">
                                                <i class="fas fa-plus me-1"></i> View All Unsigned Leases
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Applications & Record Payment -->
                    <div class="col-lg-4 col-md-12">
                        <!-- Occupancy Statistics Card -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h3 class="card-title">OCCUPANCY STATISTICS</h3>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6 border-end">
                                        <h2 class="fw-bold text-primary">353</h2>
                                        <p class="text-muted mb-0">Vacant</p>
                                    </div>
                                    <div class="col-6">
                                        <h2 class="fw-bold text-success">28</h2>
                                        <p class="text-muted mb-0">Occupied</p>
                                    </div>
                                </div>
                                <div class="mt-3 text-center">
                                    <h4 class="fw-bold">381</h4>
                                    <p class="text-muted mb-0">Total Units</p>
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

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // === Pie Chart Animation ===
            const ctx = document.getElementById('collectionPieChart').getContext('2d');

            const collectionChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [4472, 0, 5778, 4350], // Collected, Processing, Overdue, Coming Due
                        backgroundColor: [
                            '#28a745', // Green - Collected
                            '#ffc107', // Yellow - Processing
                            '#dc3545', // Red - Overdue
                            '#17a2b8' // Info/Blue - Coming Due
                        ],
                        borderColor: '#fff',
                        borderWidth: 1,
                        borderRadius: 8,
                        spacing: 4,
                        cutout: '70%' // Makes it doughnut with space for total in center
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true,
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.parsed || 0;
                                    let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    let percentage = Math.round((value / total) * 100 * 10) / 10;
                                    return `${label}: $${value.toLocaleString()} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 2000,
                        easing: 'easeOutQuart'
                    },
                    hover: {
                        animationDuration: 400
                    }
                },
                plugins: [{
                    id: 'centerText',
                    afterDraw(chart) {
                        const ctx = chart.ctx;
                        const width = chart.width;
                        const height = chart.height;

                        ctx.restore();
                        ctx.font = 'bold 1.2rem sans-serif';
                        ctx.fillStyle = '#2c3e50';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';

                        const centerX = width / 2;
                        const centerY = height / 2;

                        ctx.fillText('Total', centerX, centerY - 10);

                        ctx.font = 'bold 1.8rem sans-serif';
                        ctx.fillStyle = '#0d6efd';
                        ctx.fillText('$800.00', centerX, centerY + 15);

                        ctx.save();
                    }
                }]
            });
        });
    </script>
@endpush

@push('styles')
@endpush
