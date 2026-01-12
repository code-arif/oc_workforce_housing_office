@extends('backend.app')

@section('title', 'Maintenance Request Details')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid p-0">

                <!-- Maintenance Detail Container -->
                <div class="maintenance-detail-container">

                    <!-- Left Sidebar - Request List -->
                    <div class="maintenance-sidebar">
                        <div class="sidebar-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Maintenance Requests</h5>
                                <a href="{{ route('maintanance.index') }}" class="btn btn-sm btn-light">
                                    <i class="fe fe-arrow-left"></i>
                                </a>
                            </div>
                            <div class="search-box mt-3">
                                <input type="text" class="form-control" id="maintenanceSearch"
                                    placeholder="Search requests...">
                            </div>
                        </div>

                        <div class="maintenance-list">
                            @foreach ($maintenanceRequests as $req)
                                @php
                                    $reqProfile = $req->tenant ? $req->tenant->profile : null;
                                    $reqTenantName = $reqProfile
                                        ? trim(
                                            $reqProfile->first_name .
                                                ' ' .
                                                ($reqProfile->middle_name ?? '') .
                                                ' ' .
                                                ($reqProfile->last_name ?? ''),
                                        )
                                        : 'No Tenant';

                                    $statusColors = [
                                        'pending' => 'primary',
                                        'in_progress' => 'warning',
                                        'completed' => 'success',
                                        'rejected' => 'danger',
                                        'cancelled' => 'secondary',
                                    ];

                                    $statusColor = $statusColors[$req->status] ?? 'secondary';
                                    $statusLabel = ucwords(str_replace('_', ' ', $req->status));
                                @endphp
                                <div class="maintenance-item {{ $req->id == $maintenance->id ? 'active' : '' }}"
                                    data-maintenance-id="{{ $req->id }}"
                                    onclick="loadMaintenanceDetails({{ $req->id }})">
                                    <div class="maintenance-status-indicator bg-{{ $statusColor }}"></div>
                                    <div class="maintenance-info">
                                        <div class="maintenance-title">
                                            <strong>{{ $req->title }}</strong>
                                            @if ($req->is_urgent)
                                                <span class="badge badge-sm bg-danger ms-2">Urgent</span>
                                            @endif
                                        </div>
                                        <div class="maintenance-property">
                                            {{ $req->property ? $req->property->name : 'N/A' }}
                                            @if ($req->unit)
                                                | {{ $req->unit }}
                                            @endif
                                        </div>
                                        <div class="maintenance-tenant">{{ $reqTenantName }}</div>
                                        <div class="maintenance-date">
                                            {{ date('M d, Y', strtotime($req->created_at)) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Right Content - Maintenance Details -->
                    <div class="maintenance-content">
                        <div class="content-header">
                            <button class="btn btn-light mobile-sidebar-toggle d-lg-none" onclick="toggleSidebar()">
                                <i class="fe fe-menu"></i>
                            </button>
                            <h4 class="mb-0">Maintenance Detail</h4>
                            <div class="header-actions">
                                <button class="btn btn-light" onclick="window.location='{{ route('maintanance.index') }}'">
                                    <i class="fe fe-x"></i>
                                </button>
                            </div>
                        </div>

                        <div class="maintenance-detail-content" id="maintenanceDetailContent">
                            <!-- Maintenance Header -->
                            <div class="maintenance-header">
                                <div class="d-flex align-items-start justify-content-between flex-wrap">
                                    <div class="d-flex align-items-center mb-3">
                                        @php
                                            $statusColors = [
                                                'pending' => 'primary',
                                                'in_progress' => 'warning',
                                                'completed' => 'success',
                                                'rejected' => 'danger',
                                                'cancelled' => 'secondary',
                                            ];

                                            $statusLabels = [
                                                'pending' => 'Open',
                                                'in_progress' => 'In Progress',
                                                'completed' => 'Resolved',
                                                'rejected' => 'Rejected',
                                                'cancelled' => 'Cancelled',
                                            ];

                                            $statusColor = $statusColors[$maintenance->status] ?? 'secondary';
                                            $statusLabel = $statusLabels[$maintenance->status] ?? $maintenance->status;

                                            $categoryIcons = [
                                                'ac' => 'fe-wind',
                                                'appliance' => 'fe-box',
                                                'electrical' => 'fe-zap',
                                                'heat' => 'fe-thermometer',
                                                'kitchen' => 'fe-coffee',
                                                'plumbing' => 'fe-droplet',
                                                'other' => 'fe-more-horizontal',
                                            ];

                                            $categoryIcon = $categoryIcons[$maintenance->category] ?? 'fe-tool';
                                        @endphp
                                        <span
                                            class="badge bg-{{ $statusColor }} me-3 fs-6 maintenance-status-badge">{{ $statusLabel }}</span>

                                        @if ($maintenance->is_urgent)
                                            <span class="badge bg-danger-transparent text-danger">
                                                 Urgent
                                            </span>
                                        @endif

                                        @if ($maintenance->grant_permission)
                                            <span class="badge bg-success-transparent text-success ms-2">
                                                 Granted
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-end mb-3">
                                        <button class="btn btn-sm btn-success me-2" onclick="markAsResolved()">
                                            <i class="fe fe-check me-1" style="font-size:8px"></i> Mark as Resolved
                                        </button>
                                        <button class="btn btn-sm btn-primary me-2" onclick="setReminder()">
                                            <i class="fe fe-clock me-1" style="font-size:8px"></i> Set Reminder/Recurrence
                                        </button>
                                        <button class="btn btn-sm btn-info me-2" onclick="forwardMaintenance()">
                                            <i class="fe fe-send me-1" style="font-size:8px"></i> Forward Maintenance
                                        </button>
                                        <div class="btn-group">
                                            <button class="btn btn-sm" data-bs-toggle="dropdown">
                                                <i class="fe fe-more-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item"
                                                        href="{{ route('maintanance.edit', $maintenance->id) }}">
                                                        <i class="fe fe-edit me-2"></i> Edit
                                                    </a></li>
                                                <li><a class="dropdown-item text-danger"
                                                        onclick="deleteRequest({{ $maintenance->id }})">
                                                        <i class="fe fe-trash me-2"></i> Delete
                                                    </a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <h3 class="mb-1 maintenance-title-header">
                                            <i class="fe {{ $categoryIcon }} me-2 text-primary"></i>
                                            {{ $maintenance->title }}
                                        </h3>
                                        <div class="maintenance-meta">
                                            <span class="text-muted">
                                                <i class="fe fe-calendar me-1"></i>
                                                Requested on {{ date('M d, Y', strtotime($maintenance->created_at)) }}
                                            </span>
                                        </div>
                                        <div class="mt-3">
                                            <h5 class="text-primary mb-2">
                                                {{ $maintenance->property ? $maintenance->property->name : 'N/A' }}</h5>
                                            @if ($maintenance->unit)
                                                <p class="text-muted mb-0">Unit: {{ $maintenance->unit }}</p>
                                            @endif
                                            @if ($maintenance->property)
                                                <small class="text-muted">
                                                    {{ $maintenance->property->address }},
                                                    {{ $maintenance->property->city }},
                                                    {{ $maintenance->property->state }}
                                                    {{ $maintenance->property->zip_code }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        @php
                                            $profile = $maintenance->tenant ? $maintenance->tenant->profile : null;
                                            $fullName = $profile
                                                ? trim(
                                                    $profile->first_name .
                                                        ' ' .
                                                        ($profile->middle_name ?? '') .
                                                        ' ' .
                                                        ($profile->last_name ?? ''),
                                                )
                                                : 'No Tenant';
                                            $avatar =
                                                $profile && $profile->avatar
                                                    ? asset($profile->avatar)
                                                    : 'https://ui-avatars.com/api/?name=' .
                                                        urlencode($fullName) .
                                                        '&background=random';
                                        @endphp
                                        <div class="d-flex align-items-center justify-content-md-end mb-3">
                                            <img src="{{ $avatar }}" alt="avatar"
                                                class="rounded-circle me-3 tenant-avatar" width="60" height="60"
                                                style="object-fit: cover;">
                                            <div>
                                                <div class="fw-semibold fs-6 tenant-name">{{ $fullName }}</div>
                                                <small class="text-muted d-block tenant-contact">
                                                    <i class="fe fe-phone me-1"></i>
                                                    {{ $profile ? $profile->phone : 'N/A' }}
                                                </small>
                                                <small class="text-muted d-block tenant-email">
                                                    <i class="fe fe-mail me-1"></i>
                                                    {{ $maintenance->tenant ? $maintenance->tenant->email : 'N/A' }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description Section -->
                            <div class="detail-section">
                                <h5 class="section-title mb-3">
                                    <i class="fe fe-file-text me-2"></i> Description
                                </h5>
                                <div class="description-content">
                                    <p class="maintenance-description">{{ $maintenance->description }}</p>
                                </div>
                            </div>

                            <!-- Photos Section -->
                            @if ($maintenance->attachments->count() > 0)
                                <div class="detail-section">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="section-title mb-0">
                                            <i class="fe fe-image me-2"></i> Photos
                                            <span
                                                class="badge bg-secondary ms-2">{{ $maintenance->attachments->count() }}</span>
                                        </h5>
                                        <button class="btn btn-sm btn-primary" onclick="$('#addFilesInput').click()">
                                            <i class="fe fe-plus me-1"></i> Add Files
                                        </button>
                                        <input type="file" id="addFilesInput" multiple accept="image/*,video/*,.pdf"
                                            style="display: none;">
                                    </div>

                                    <div class="photos-grid">
                                        @foreach ($maintenance->attachments as $attachment)
                                            @php
                                                $extension = pathinfo($attachment->attachment_path, PATHINFO_EXTENSION);
                                                $isImage = in_array(strtolower($extension), [
                                                    'jpg',
                                                    'jpeg',
                                                    'png',
                                                    'gif',
                                                    'bmp',
                                                    'jfif',
                                                ]);
                                                $isVideo = in_array(strtolower($extension), [
                                                    'mp4',
                                                    'mov',
                                                    'webm',
                                                    'mpeg',
                                                    'm4v',
                                                ]);
                                            @endphp

                                            <div class="photo-item">
                                                @if ($isImage)
                                                    <img src="{{ asset('storage/' . $attachment->attachment_path) }}"
                                                        alt="Attachment"
                                                        onclick="viewImage('{{ asset('storage/' . $attachment->attachment_path) }}')">
                                                @elseif($isVideo)
                                                    <video controls class="w-100" style="max-height: 200px;">
                                                        <source
                                                            src="{{ asset('storage/' . $attachment->attachment_path) }}">
                                                    </video>
                                                @else
                                                    <div class="file-preview">
                                                        <i class="fe fe-file"></i>
                                                        <small>{{ strtoupper($extension) }}</small>
                                                        <a href="{{ asset('storage/' . $attachment->attachment_path) }}"
                                                            target="_blank" class="btn btn-sm btn-primary mt-2">
                                                            <i class="fe fe-download"></i> Download
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Comments/Notes Section -->
                            <div class="detail-section">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="section-title mb-0">
                                        <i class="fe fe-message-square me-2"></i> Comments
                                        <small class="text-muted">(Only visible to your Team Members)</small>
                                    </h5>
                                    <button class="btn btn-sm btn-primary" onclick="toggleCommentForm()">
                                        <i class="fe fe-plus me-1"></i> Add Notes
                                    </button>
                                </div>

                                <div class="comment-form mb-3" id="commentForm" style="display: none;">
                                    <textarea class="form-control mb-2" rows="3" placeholder="Write your comment..."></textarea>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button class="btn btn-sm btn-light" onclick="toggleCommentForm()">Cancel</button>
                                        <button class="btn btn-sm btn-primary">Post Comment</button>
                                    </div>
                                </div>

                                <div class="comments-list">
                                    <div class="empty-state-small">
                                        <div class="empty-icon">
                                            <i class="fe fe-message-square"></i>
                                        </div>
                                        <p class="text-muted mb-0">No comments yet</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Activity Timeline -->
                            <div class="detail-section">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="section-title mb-0">
                                        <i class="fe fe-clock me-2"></i> Activity Timeline
                                    </h5>
                                </div>

                                <div class="timeline-section">
                                    <div class="timeline-item">
                                        <div class="timeline-icon bg-primary">
                                            <i class="fe fe-plus"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">Request Created</h6>
                                                    <p class="text-muted mb-0 small">
                                                        Maintenance request was submitted by {{ $fullName }}
                                                    </p>
                                                </div>
                                                <span class="text-muted small">
                                                    {{ date('M d, Y g:i A', strtotime($maintenance->created_at)) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="" id="modalImage" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Search functionality
        $('#maintenanceSearch').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('.maintenance-item').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // Load maintenance details via AJAX
        function loadMaintenanceDetails(maintenanceId) {
            NProgress.start();

            // Update active state in sidebar
            $('.maintenance-item').removeClass('active');
            $(`.maintenance-item[data-maintenance-id="${maintenanceId}"]`).addClass('active');

            // Update URL without page reload
            const newUrl = `{{ route('maintanance.show', '') }}/${maintenanceId}`;
            window.history.pushState({
                maintenanceId: maintenanceId
            }, '', newUrl);

            // Fetch maintenance details (you would implement the AJAX call)
            window.location.href = newUrl;
        }

        // Toggle sidebar on mobile
        function toggleSidebar() {
            $('.maintenance-sidebar').toggleClass('show');
        }

        // Close sidebar when clicking outside on mobile
        $(document).on('click', function(e) {
            if ($(window).width() < 992) {
                if (!$(e.target).closest('.maintenance-sidebar, .mobile-sidebar-toggle').length) {
                    $('.maintenance-sidebar').removeClass('show');
                }
            }
        });

        // View image in modal
        function viewImage(src) {
            $('#modalImage').attr('src', src);
            const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
            imageModal.show();
        }

        // Toggle comment form
        function toggleCommentForm() {
            $('#commentForm').slideToggle();
        }

        // Mark as resolved
        function markAsResolved() {
            Swal.fire({
                title: 'Mark as Resolved?',
                text: "This will change the status to resolved.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, mark as resolved'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Implement AJAX call to update status
                    toastr.success('Marked as resolved successfully');
                }
            });
        }

        // Set reminder
        function setReminder() {
            toastr.info('Reminder feature coming soon');
        }

        // Forward maintenance
        function forwardMaintenance() {
            toastr.info('Forward feature coming soon');
        }

        // Delete request
        function deleteRequest(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
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
                                window.location.href = '{{ route('maintanance.index') }}';
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Failed to delete request', 'error');
                        }
                    });
                }
            });
        }

        // Handle browser back/forward buttons
        window.addEventListener('popstate', function(event) {
            if (event.state && event.state.maintenanceId) {
                loadMaintenanceDetails(event.state.maintenanceId);
            } else {
                window.location.href = '{{ route('maintanance.index') }}';
            }
        });

        // Initialize state for current page
        window.history.replaceState({
            maintenanceId: {{ $maintenance->id }}
        }, '', window.location.href);
    </script>
@endpush

@push('styles')
    <style>
        .maintenance-detail-container {
            display: flex;
            height: calc(100vh - 70px);
            background: #fff;
            margin: 15px 0px;
        }

        /* Left Sidebar */
        .maintenance-sidebar {
            width: 350px;
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

        .maintenance-list {
            flex: 1;
            overflow-y: auto;
        }

        .maintenance-item {
            display: flex;
            align-items: stretch;
            padding: 0;
            cursor: pointer;
            border-bottom: 1px solid #f8f9fa;
            transition: all 0.2s;
            position: relative;
        }

        .maintenance-item:hover {
            background: #f8f9fa;
        }

        .maintenance-item.active {
            background: #e3f2fd;
        }

        .maintenance-status-indicator {
            width: 4px;
            min-height: 100%;
        }

        .maintenance-info {
            flex: 1;
            padding: 12px 16px;
        }

        .maintenance-title {
            font-size: 14px;
            color: #2c3e50;
            margin-bottom: 4px;
        }

        .maintenance-property {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 2px;
        }

        .maintenance-tenant {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 2px;
        }

        .maintenance-date {
            font-size: 11px;
            color: #adb5bd;
        }

        /* Right Content */
        .maintenance-content {
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

        .maintenance-detail-content {
            flex: 1;
            overflow-y: auto;
            padding: 30px;
        }

        .maintenance-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 1px solid #e9ecef;
        }

        .detail-section {
            margin-bottom: 30px;
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 25px;
        }

        .section-title {
            font-weight: 600;
            color: #2c3e50;
            font-size: 16px;
        }

        .description-content {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }

        .photos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .photo-item {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .photo-item:hover {
            transform: scale(1.05);
        }

        .photo-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .file-preview {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: #f8f9fa;
            height: 200px;
        }

        .file-preview i {
            font-size: 48px;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .timeline-section {
            position: relative;
            padding-left: 40px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 30px;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-item:not(:last-child)::after {
            content: '';
            position: absolute;
            left: -25px;
            top: 35px;
            width: 2px;
            height: calc(100% - 35px);
            background: #e9ecef;
        }

        .timeline-icon {
            position: absolute;
            left: -35px;
            top: 0;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 14px;
        }

        .timeline-content {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
        }

        .empty-state-small {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-icon {
            width: 60px;
            height: 60px;
            background: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: #6c757d;
            font-size: 28px;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .maintenance-sidebar {
                position: fixed;
                left: -350px;
                top: 0;
                height: 100vh;
                z-index: 1050;
                transition: left 0.3s;
            }

            .maintenance-sidebar.show {
                left: 0;
            }

            .mobile-sidebar-toggle {
                display: block;
            }

            .maintenance-content {
                width: 100%;
            }

            .maintenance-detail-content {
                padding: 20px;
            }

            .content-header {
                padding: 15px 20px;
            }
        }
    </style>
@endpush
