@extends('backend.app')

@section('title', 'Tenant Details')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid p-0">

                <!-- Tenant Detail Container -->
                <div class="tenant-detail-container">

                    <!-- Left Sidebar - Tenant List -->
                    <div class="tenant-sidebar">
                        <div class="sidebar-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Tenants</h5>
                                <a href="{{ route('tenants.index') }}" class="btn btn-sm btn-light">
                                    <i class="fe fe-arrow-left"></i>
                                </a>
                            </div>
                            <div class="search-box mt-3">
                                <input type="text" class="form-control" id="tenantSearch"
                                    placeholder="Search tenants...">
                            </div>
                        </div>

                        <div class="tenant-list">
                            @foreach ($tenants as $t)
                                @php
                                    $tProfile = $t->profile;
                                    $tFullName = $tProfile
                                        ? trim(
                                            $tProfile->first_name .
                                                ' ' .
                                                ($tProfile->middle_name ?? '') .
                                                ' ' .
                                                ($tProfile->last_name ?? ''),
                                        )
                                        : 'No Name';
                                    $tAvatar =
                                        $tProfile && $tProfile->avatar
                                            ? asset($tProfile->avatar)
                                            : 'https://ui-avatars.com/api/?name=' .
                                                urlencode($tFullName) .
                                                '&background=random';
                                @endphp
                                <div class="tenant-item {{ $t->id == $tenant->id ? 'active' : '' }}"
                                    data-tenant-id="{{ $t->id }}" onclick="loadTenantDetails({{ $t->id }})">
                                    <img src="{{ $tAvatar }}" alt="avatar" class="tenant-avatar">
                                    <div class="tenant-info">
                                        <div class="tenant-name">{{ $tFullName }}</div>
                                        <div class="tenant-unit">PHILLIPS HOUSE | 206-C-1</div>
                                    </div>
                                    <i class="fe fe-chevron-right"></i>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Right Content - Tenant Details -->
                    <div class="tenant-content">
                        <div class="content-header">
                            <button class="btn btn-light mobile-sidebar-toggle d-lg-none" onclick="toggleSidebar()">
                                <i class="fe fe-menu"></i>
                            </button>
                            <h4 class="mb-0">Tenant Detail</h4>
                            <button class="btn btn-light close-detail"
                                onclick="window.location='{{ route('tenants.index') }}'">
                                <i class="fe fe-x"></i>
                            </button>
                        </div>

                        <div class="tenant-detail-content" id="tenantDetailContent">
                            <!-- Tenant Header -->
                            <div class="tenant-header">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div class="d-flex align-items-center">
                                        @php
                                            $profile = $tenant->profile;
                                            $fullName = $profile
                                                ? trim(
                                                    $profile->first_name .
                                                        ' ' .
                                                        ($profile->middle_name ?? '') .
                                                        ' ' .
                                                        ($profile->last_name ?? ''),
                                                )
                                                : 'No Name';
                                            $avatar =
                                                $profile && $profile->avatar
                                                    ? asset($profile->avatar)
                                                    : 'https://ui-avatars.com/api/?name=' .
                                                        urlencode($fullName) .
                                                        '&background=random';
                                        @endphp
                                        <img src="{{ $avatar }}" alt="avatar" class="tenant-detail-avatar">
                                        <div class="ms-3">
                                            <h3 class="mb-1">{{ $fullName }}</h3>
                                            <div class="tenant-contact">
                                                <span><i class="fe fe-phone me-1"></i> {{ $profile->phone ?? 'N/A' }}</span>
                                                <span class="ms-3"><i class="fe fe-mail me-1"></i>
                                                    {{ $tenant->email }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="mb-2">
                                            <small class="text-muted d-block">Insurance Status</small>
                                            <a href="#" class="text-primary">Request Renter's Insurance</a>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="fe fe-user-check text-muted me-2"></i>
                                            <span class="text-muted me-2">Innago Account Status</span>
                                            <i class="fe fe-check-circle text-success"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <small class="text-muted">Tenant Since</small>
                                    <div class="fw-semibold">{{ $tenant->created_at->format('M d, Y') }}</div>
                                </div>
                            </div>

                            <!-- Current Lease Section -->
                            <div class="detail-section">
                                <h5 class="section-title">Current Lease</h5>
                                @php
                                    $activeLease = $tenant->leases->where('status', 'ACTIVE')->first();
                                @endphp
                                @if ($activeLease)
                                    <div class="lease-card">
                                        <div class="row align-items-center">
                                            <div class="col-md-2">
                                                <div class="property-image">
                                                    <img src="https://via.placeholder.com/150" alt="property">
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="property-info">
                                                    <h6 class="mb-1">{{ $activeLease->property->name ?? 'N/A' }} |
                                                        {{ $activeLease->assignments->first()->bed->bed_number ?? 'N/A' }}
                                                    </h6>
                                                    <div class="d-flex align-items-center mt-2">
                                                        <i class="fe fe-file-text me-2 text-primary"></i>
                                                        <div>
                                                            <strong>Rent</strong>
                                                            <div class="text-primary fw-bold">
                                                                ${{ number_format($activeLease->rent_amount, 2) }}</div>
                                                            <small class="text-muted">of
                                                                ${{ number_format($activeLease->rent_amount, 2) }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="lease-dates">
                                                    <div class="mb-3">
                                                        <small class="text-muted d-block">Start</small>
                                                        <strong>{{ date('M d, Y', strtotime($activeLease->start_date)) }}</strong>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">End</small>
                                                        <strong>{{ date('M d, Y', strtotime($activeLease->end_date)) }}</strong>
                                                    </div>
                                                    <div class="mt-3">
                                                        <button class="btn btn-sm btn-outline-primary">View Lease</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="fe fe-info me-2"></i> No active lease found for this tenant.
                                    </div>
                                @endif
                            </div>

                            <!-- Collection Section -->
                            <div class="detail-section">
                                <h5 class="section-title">Collection</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="collection-card">
                                            <div class="d-flex align-items-start">
                                                <div class="collection-icon">
                                                    <i class="fe fe-clock"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-2">Current Invoices</h6>
                                                    <div class="fw-bold text-muted">No Record found.</div>
                                                    <div class="mt-2">
                                                        <small class="text-muted">Past Due Invoices</small>
                                                        <div class="fw-semibold">No Record found.</div>
                                                    </div>
                                                    <a href="#" class="text-primary mt-2 d-inline-block">View All
                                                        Invoices</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="collection-summary">
                                            <div class="summary-item">
                                                <span class="text-muted">Total Rent Collected</span>
                                                <strong class="text-success">$910.00</strong>
                                            </div>
                                            <div class="summary-item">
                                                <span class="text-muted">Other Collected</span>
                                                <strong>$0.00</strong>
                                            </div>
                                            <div class="summary-item border-top pt-2 mt-2">
                                                <span class="fw-semibold">Total</span>
                                                <strong class="fs-5">$910.00</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Message History Section -->
                            <div class="detail-section">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="section-title mb-0">Message History</h5>
                                    <a href="#" class="text-primary">Show All</a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>To</th>
                                                <th>From</th>
                                                <th>Subject</th>
                                                <th>Notification Date</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{{ $fullName }}</td>
                                                <td>OC Workforce Housing</td>
                                                <td>Security Deposit Processing</td>
                                                <td>Dec 08, 2025</td>
                                                <td><a href="#" class="text-primary">View</a></td>
                                            </tr>
                                            <tr>
                                                <td>{{ $fullName }}</td>
                                                <td>OC Workforce Housing</td>
                                                <td>Reminder: Lease Expiring</td>
                                                <td>Dec 01, 2025</td>
                                                <td><a href="#" class="text-primary">View</a></td>
                                            </tr>
                                            <tr>
                                                <td>{{ $fullName }}</td>
                                                <td>OC Workforce Housing</td>
                                                <td>Upcoming Invoice for 2004 Philadelphia Avenue</td>
                                                <td>Nov 29, 2025</td>
                                                <td><a href="#" class="text-primary">View</a></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Lease History Section -->
                            <div class="detail-section">
                                <h5 class="section-title">Lease History</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Monthly Rent</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>Days Remaining</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($tenant->leases as $lease)
                                                <tr>
                                                    <td>${{ number_format($lease->rent_amount, 2) }}</td>
                                                    <td>{{ date('M d, Y', strtotime($lease->start_date)) }}</td>
                                                    <td>{{ date('M d, Y', strtotime($lease->end_date)) }}</td>
                                                    <td>
                                                        @php
                                                            $daysRemaining = max(
                                                                0,
                                                                now()->diffInDays($lease->end_date, false),
                                                            );
                                                        @endphp
                                                        {{ $daysRemaining }} days remaining
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Search functionality
        $('#tenantSearch').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('.tenant-item').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // Load tenant details via AJAX
        function loadTenantDetails(tenantId) {
            NProgress.start();

            // Update active state in sidebar
            $('.tenant-item').removeClass('active');
            $(`.tenant-item[data-tenant-id="${tenantId}"]`).addClass('active');

            // Update URL without page reload
            const newUrl = `{{ route('tenants.show', '') }}/${tenantId}`;
            window.history.pushState({
                tenantId: tenantId
            }, '', newUrl);

            // Fetch tenant details
            $.ajax({
                url: `{{ route('tenants.details', '') }}/${tenantId}`,
                type: 'GET',
                success: function(response) {
                    NProgress.done();
                    if (response.success) {
                        updateTenantDetails(response.tenant);
                    }
                },
                error: function() {
                    NProgress.done();
                    toastr.error('Failed to load tenant details');
                }
            });
        }

        // Update tenant details in the DOM
        function updateTenantDetails(tenant) {
            // Update header
            $('.tenant-detail-avatar').attr('src', tenant.avatar);
            $('.tenant-header h3').text(tenant.full_name);
            $('.tenant-contact').html(`
                <span><i class="fe fe-phone me-1"></i> ${tenant.phone}</span>
                <span class="ms-3"><i class="fe fe-mail me-1"></i> ${tenant.email}</span>
            `);
            $('.tenant-header .fw-semibold').text(tenant.tenant_since);

            // Update current lease
            if (tenant.active_lease) {
                const lease = tenant.active_lease;
                $('.property-info h6').text(`${lease.property_name} | ${lease.unit}`);
                $('.property-info .text-primary.fw-bold').text(`$${lease.rent}`);
                $('.property-info .text-muted').text(`of $${lease.rent}`);
                $('.lease-dates').html(`
                    <div class="mb-3">
                        <small class="text-muted d-block">Start</small>
                        <strong>${lease.start_date}</strong>
                    </div>
                    <div>
                        <small class="text-muted d-block">End</small>
                        <strong>${lease.end_date}</strong>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary">View Lease</button>
                    </div>
                `);
            }

            // Update lease history
            let leaseHistoryHtml = '';
            tenant.lease_history.forEach(lease => {
                leaseHistoryHtml += `
                    <tr>
                        <td>$${lease.rent}</td>
                        <td>${lease.start_date}</td>
                        <td>${lease.end_date}</td>
                        <td>${lease.days_remaining} days remaining</td>
                    </tr>
                `;
            });
            $('.detail-section:last table tbody').html(leaseHistoryHtml);
        }

        // Handle browser back/forward buttons
        window.addEventListener('popstate', function(event) {
            if (event.state && event.state.tenantId) {
                loadTenantDetails(event.state.tenantId);
            } else {
                window.location.href = '{{ route('tenants.index') }}';
            }
        });

        // Initialize state for current page
        window.history.replaceState({
            tenantId: {{ $tenant->id }}
        }, '', window.location.href);

        // Toggle sidebar on mobile
        function toggleSidebar() {
            $('.tenant-sidebar').toggleClass('show');
        }

        // Close sidebar when clicking outside on mobile
        $(document).on('click', function(e) {
            if ($(window).width() < 992) {
                if (!$(e.target).closest('.tenant-sidebar, .mobile-sidebar-toggle').length) {
                    $('.tenant-sidebar').removeClass('show');
                }
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        .tenant-detail-container {
            display: flex;
            height: calc(100vh - 70px);
            background: #fff;
            margin: 15px 0px;
        }

        /* Left Sidebar */
        .tenant-sidebar {
            width: 320px;
            border-right: 1px solid #e9ecef;
            display: flex;
            flex-direction: column;
            background: #fff;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .sidebar-header h5 {
            font-weight: 600;
            color: #2c3e50;
        }

        .search-box input {
            border-radius: 6px;
            border: 1px solid #e9ecef;
            padding: 8px 12px;
            font-size: 14px;
        }

        .tenant-list {
            flex: 1;
            overflow-y: auto;
        }

        .tenant-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            cursor: pointer;
            border-bottom: 1px solid #f8f9fa;
            transition: all 0.2s;
        }

        .tenant-item:hover {
            background: #f8f9fa;
        }

        .tenant-item.active {
            background: #e3f2fd;
            border-left: 3px solid #2196F3;
        }

        .tenant-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 12px;
        }

        .tenant-info {
            flex: 1;
        }

        .tenant-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
            margin-bottom: 2px;
        }

        .tenant-unit {
            font-size: 12px;
            color: #6c757d;
        }

        .tenant-item i {
            color: #adb5bd;
            font-size: 14px;
        }

        /* Right Content */
        .tenant-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .content-header {
            padding: 20px 30px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
        }

        .content-header h4 {
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .mobile-sidebar-toggle {
            display: none;
        }

        .tenant-detail-content {
            flex: 1;
            overflow-y: auto;
            padding: 30px;
        }

        .tenant-header {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .tenant-detail-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }

        .tenant-header h3 {
            font-weight: 600;
            color: #2c3e50;
        }

        .tenant-contact {
            color: #6c757d;
            font-size: 14px;
        }

        .tenant-contact i {
            font-size: 14px;
        }

        .detail-section {
            margin-bottom: 35px;
        }

        .section-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .lease-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
        }

        .property-image img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 6px;
        }

        .property-info h6 {
            font-weight: 600;
            color: #2c3e50;
        }

        .collection-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
        }

        .collection-icon {
            width: 50px;
            height: 50px;
            background: #e3f2fd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2196F3;
            font-size: 24px;
        }

        .collection-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .summary-item:last-child {
            margin-bottom: 0;
        }

        .table th {
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            border-bottom: 2px solid #e9ecef;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .tenant-sidebar {
                position: fixed;
                left: -320px;
                top: 0;
                height: 100vh;
                z-index: 1050;
                transition: left 0.3s;
            }

            .tenant-sidebar.show {
                left: 0;
            }

            .mobile-sidebar-toggle {
                display: block;
            }

            .tenant-content {
                width: 100%;
            }

            .tenant-detail-content {
                padding: 20px;
            }

            .content-header {
                padding: 15px 20px;
            }
        }

        @media (max-width: 767px) {
            .lease-card .row>div {
                margin-bottom: 15px;
            }

            .collection-card {
                margin-bottom: 15px;
            }
        }
    </style>
@endpush
