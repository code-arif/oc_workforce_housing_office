@extends('backend.app')

@section('title', 'Maintenance Request Details')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid p-0">

                <div class="maintenance-detail-container">

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
                                    $reqProfile = $req->tenant?->profile;
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
                                    $reqStatusColor = $statusColors[$req->status] ?? 'secondary';

                                    // Priority 1: Direct FK on maintenance_request
                                    $reqProperty = $req->property;
                                    $reqUnit = $req->unitModel;
                                    $reqRoom = $req->room;
                                    $reqBed = $req->bed;

                                    // Priority 2: Fallback to lease assignment
                                    if (!$reqProperty) {
                                        $reqLease = $req->tenant?->activeLease;
                                        $reqProperty = $reqLease?->property;
                                        $reqAssignment = $reqLease?->currentAssignment;
                                        $reqBed = $reqBed ?? $reqAssignment?->bed;
                                        $reqRoom = $reqRoom ?? $reqBed?->room;
                                        $reqUnit = $reqUnit ?? $reqRoom?->unit;
                                    }
                                @endphp

                                <div class="maintenance-item {{ $req->id == $maintenance->id ? 'active' : '' }}"
                                    data-maintenance-id="{{ $req->id }}"
                                    onclick="loadMaintenanceDetails({{ $req->id }})">

                                    <div class="maintenance-status-indicator bg-{{ $reqStatusColor }}"></div>

                                    <div class="maintenance-info">
                                        {{-- Title --}}
                                        <div class="maintenance-title">
                                            <strong>{{ $req->title }}</strong>
                                            @if ($req->is_urgent)
                                                <span class="badge badge-sm bg-danger ms-1">Urgent</span>
                                            @endif
                                        </div>

                                        {{-- Property --}}
                                        <div class="maintenance-property">
                                            @if ($reqProperty)
                                                <i class="fe fe-home me-1" style="font-size:11px;"></i>
                                                {{ $reqProperty->name }}
                                            @else
                                                <span class="text-muted">No Property</span>
                                            @endif
                                        </div>

                                        {{-- Unit · Room · Bed --}}
                                        <div class="maintenance-tenant">
                                            @if ($reqUnit || $reqRoom || $reqBed)
                                                @if ($reqUnit)
                                                    <span>{{ $reqUnit->name }}</span>
                                                @endif
                                                @if ($reqRoom)
                                                    <span class="{{ $reqUnit ? 'ms-1' : '' }}">
                                                        {{ $reqUnit ? '·' : '' }} Rm {{ $reqRoom->room_number }}
                                                    </span>
                                                @endif
                                                @if ($reqBed)
                                                    <span class="ms-1">
                                                        · {{ $reqBed->bed_label ?? 'Bed ' . $reqBed->bed_number }}
                                                    </span>
                                                @endif
                                            @else
                                                {{ $reqTenantName }}
                                            @endif
                                        </div>

                                        {{-- Tenant name always --}}
                                        <div class="maintenance-tenant text-muted">
                                            <i class="fe fe-user me-1" style="font-size:10px;"></i>
                                            {{ $reqTenantName }}
                                        </div>

                                        {{-- Date --}}
                                        <div class="maintenance-date">
                                            {{ date('M d, Y', strtotime($req->created_at)) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>


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

                        <div class="maintenance-detail-content">


                            <div class="maintenance-header">

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
                                    $categoryIcons = [
                                        'ac' => 'fe-wind',
                                        'appliance' => 'fe-box',
                                        'electrical' => 'fe-zap',
                                        'heat' => 'fe-thermometer',
                                        'kitchen' => 'fe-coffee',
                                        'plumbing' => 'fe-droplet',
                                        'other' => 'fe-more-horizontal',
                                    ];

                                    $statusColor = $statusColors[$maintenance->status] ?? 'secondary';
                                    $statusLabel = $statusLabels[$maintenance->status] ?? $maintenance->status;
                                    $categoryIcon = $categoryIcons[$maintenance->category] ?? 'fe-tool';

                                    // ── Location data ──────────────────────────────────────────
                                    // Priority 1: Direct FKs on maintenance_requests
                                    $property = $maintenance->property;
                                    $unit = $maintenance->unitModel;
                                    $room = $maintenance->room;
                                    $bed = $maintenance->bed;

                                    // Priority 2: Tenant's active lease assignment (fallback)
$activeLease = $maintenance->tenant?->activeLease;
if (!$property) {
    $property = $activeLease?->property;
}
if (!$unit || !$room || !$bed) {
    $assignment = $activeLease?->currentAssignment;
    $leaseBed = $assignment?->bed;
    $leaseRoom = $leaseBed?->room;
    $leaseUnit = $leaseRoom?->unit;
    $bed = $bed ?? $leaseBed;
    $room = $room ?? $leaseRoom;
    $unit = $unit ?? $leaseUnit;
}

$profile = $maintenance->tenant?->profile;
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

                                {{-- Top row: badges + actions --}}
                                <div class="d-flex align-items-start justify-content-between flex-wrap mb-3">
                                    <div class="d-flex align-items-center flex-wrap gap-2">
                                        <span class="badge bg-{{ $statusColor }} fs-6 maintenance-status-badge">
                                            {{ $statusLabel }}
                                        </span>
                                        @if ($maintenance->is_urgent)
                                            <span class="badge bg-danger-transparent text-danger">Urgent</span>
                                        @endif
                                        @if ($maintenance->grant_permission)
                                            <span class="badge bg-success-transparent text-success">
                                                <i class="fe fe-check me-1"></i>Permission Granted
                                            </span>
                                        @endif
                                    </div>

                                    <div class="d-flex align-items-center gap-2">
                                        {{-- Quick status change --}}
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                data-bs-toggle="dropdown">
                                                <i class="fe fe-refresh-cw me-1"></i> Change Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @foreach ([
            'pending' => ['Open', 'primary'],
            'in_progress' => ['In Progress', 'warning'],
            'completed' => ['Resolved', 'success'],
            'rejected' => ['Rejected', 'danger'],
            'cancelled' => ['Cancelled', 'secondary'],
        ] as $val => [$lbl, $col])
                                                    <li>
                                                        <a class="dropdown-item d-flex align-items-center gap-2
                                                            {{ $maintenance->status == $val ? 'active' : '' }}"
                                                            href="#"
                                                            onclick="changeStatus('{{ $val }}'); return false;">
                                                            <span class="badge bg-{{ $col }}"
                                                                style="width:10px;height:10px;padding:0;border-radius:50%;"></span>
                                                            {{ $lbl }}
                                                            @if ($maintenance->status == $val)
                                                                <i class="fe fe-check ms-auto"></i>
                                                            @endif
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>

                                        @if ($maintenance->status !== 'completed')
                                            <button class="btn btn-sm btn-success" onclick="markAsResolved()">
                                                <i class="fe fe-check me-1"></i> Mark Resolved
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-secondary" disabled>
                                                <i class="fe fe-check me-1"></i> Resolved
                                            </button>
                                        @endif

                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                                <i class="fe fe-more-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('maintanance.edit', $maintenance->id) }}">
                                                        <i class="fe fe-edit me-2"></i> Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#"
                                                        onclick="deleteRequest({{ $maintenance->id }}); return false;">
                                                        <i class="fe fe-trash me-2"></i> Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                {{-- Title + date --}}
                                <h3 class="mb-1">
                                    <i class="fe {{ $categoryIcon }} me-2 text-primary"></i>
                                    {{ $maintenance->title }}
                                </h3>
                                <div class="text-muted mb-4">
                                    <i class="fe fe-calendar me-1"></i>
                                    Requested on {{ date('M d, Y \a\t g:i A', strtotime($maintenance->created_at)) }}
                                </div>

                                {{-- Two column: Location + Tenant --}}
                                <div class="row g-4">

                                    {{-- Left: Location --}}
                                    <div class="col-md-6">
                                        <div class="detail-info-card">
                                            <div class="detail-info-header">
                                                <i class="fe fe-map-pin text-primary me-2"></i>
                                                <strong>Location</strong>
                                            </div>

                                            {{-- Property --}}
                                            <div class="d-flex align-items-start mt-3 mb-3">
                                                <div class="loc-icon-wrap me-2">
                                                    <i class="fe fe-home text-primary fs-5"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-primary">
                                                        {{ $property?->name ?? 'No Property Assigned' }}
                                                    </div>
                                                    @if ($property?->address)
                                                        <small class="text-muted">
                                                            <i class="fe fe-map-pin me-1"></i>{{ $property->address }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Unit → Room → Bed hierarchy --}}
                                            @if ($unit || $room || $bed)
                                                <div class="location-hierarchy">
                                                    @if ($unit)
                                                        <div class="hierarchy-step">
                                                            <div class="hierarchy-icon-box bg-info-transparent">
                                                                <i class="fe fe-layers text-info"></i>
                                                            </div>
                                                            <div class="hierarchy-text">
                                                                <div class="hierarchy-label">Unit</div>
                                                                <div class="hierarchy-value">{{ $unit->name }}</div>
                                                            </div>
                                                        </div>
                                                        @if ($room)
                                                            <div class="hierarchy-connector">
                                                                <i class="fe fe-chevron-right"></i>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if ($room)
                                                        <div class="hierarchy-step">
                                                            <div class="hierarchy-icon-box bg-warning-transparent">
                                                                <i class="fe fe-grid text-warning"></i>
                                                            </div>
                                                            <div class="hierarchy-text">
                                                                <div class="hierarchy-label">Room</div>
                                                                <div class="hierarchy-value">
                                                                    {{ $room->name ?? 'Room ' . $room->room_number }}
                                                                    <small
                                                                        class="text-muted">#{{ $room->room_number }}</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if ($bed)
                                                            <div class="hierarchy-connector">
                                                                <i class="fe fe-chevron-right"></i>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if ($bed)
                                                        <div class="hierarchy-step">
                                                            <div class="hierarchy-icon-box bg-success-transparent">
                                                                <i class="fe fe-moon text-success"></i>
                                                            </div>
                                                            <div class="hierarchy-text">
                                                                <div class="hierarchy-label">Bed</div>
                                                                <div class="hierarchy-value">
                                                                    {{ $bed->bed_label ?? 'Bed ' . $bed->bed_number }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>

                                                @if (isset($assignment) && $assignment?->actual_move_in)
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            <i class="fe fe-log-in me-1"></i>
                                                            Move-in:
                                                            {{ \Carbon\Carbon::parse($assignment->actual_move_in)->format('M d, Y') }}
                                                        </small>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="text-muted small">
                                                    <i class="fe fe-info me-1"></i>No unit/room/bed assigned
                                                </div>
                                            @endif

                                            {{-- Lease badge --}}
                                            @if ($activeLease)
                                                @php
                                                    $leaseColors = [
                                                        'ACTIVE' => ['bg' => 'success', 'label' => 'Active Lease'],
                                                        'PENDING_TENANT_SIGN' => [
                                                            'bg' => 'warning',
                                                            'label' => 'Pending Tenant Sign',
                                                        ],
                                                        'PENDING_ADMIN_SIGN' => [
                                                            'bg' => 'info',
                                                            'label' => 'Pending Admin Sign',
                                                        ],
                                                        'DRAFT' => ['bg' => 'secondary', 'label' => 'Draft'],
                                                        'TERMINATED' => ['bg' => 'danger', 'label' => 'Terminated'],
                                                        'COMPLETED' => ['bg' => 'primary', 'label' => 'Completed'],
                                                    ];
                                                    $lc = $leaseColors[$activeLease->status] ?? [
                                                        'bg' => 'secondary',
                                                        'label' => $activeLease->status,
                                                    ];
                                                @endphp
                                                <div
                                                    class="mt-3 pt-3 border-top d-flex flex-wrap align-items-center gap-2">
                                                    <span class="badge bg-{{ $lc['bg'] }}">
                                                        <i class="fe fe-file-text me-1"></i>{{ $lc['label'] }}
                                                    </span>
                                                    <small class="text-muted">
                                                        <i class="fe fe-calendar me-1"></i>
                                                        {{ \Carbon\Carbon::parse($activeLease->start_date)->format('M d, Y') }}
                                                        –
                                                        {{ \Carbon\Carbon::parse($activeLease->end_date)->format('M d, Y') }}
                                                    </small>
                                                    <small class="fw-semibold text-success">
                                                        <i class="fe fe-dollar-sign me-1"></i>
                                                        ${{ number_format($activeLease->rent_amount, 2) }}/mo
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Right: Tenant --}}
                                    <div class="col-md-6">
                                        <div class="detail-info-card">
                                            <div class="detail-info-header">
                                                <i class="fe fe-user text-primary me-2"></i>
                                                <strong>Tenant</strong>
                                            </div>
                                            <div class="d-flex align-items-center mt-3">
                                                <img src="{{ $avatar }}" alt="avatar"
                                                    class="rounded-circle me-3" width="56" height="56"
                                                    style="object-fit:cover;border:2px solid #e9ecef;">
                                                <div>
                                                    <div class="fw-semibold fs-6">{{ $fullName }}</div>
                                                    <small class="text-muted d-flex align-items-center mt-1">
                                                        <i class="fe fe-phone me-1"></i>
                                                        {{ $profile?->phone ?? 'N/A' }}
                                                    </small>
                                                    <small class="text-muted d-flex align-items-center mt-1">
                                                        <i class="fe fe-mail me-1"></i>
                                                        {{ $maintenance->tenant?->email ?? 'N/A' }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="detail-section">
                                <h5 class="section-title mb-3">
                                    <i class="fe fe-file-text me-2"></i> Description
                                </h5>
                                <div class="description-content">
                                    <p class="mb-0">{{ $maintenance->description }}</p>
                                </div>
                            </div>

                            {{-- ===== ATTACHMENTS ===== --}}
                            @if ($maintenance->attachments->count() > 0)
                                <div class="detail-section">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="section-title mb-0">
                                            <i class="fe fe-image me-2"></i> Attachments
                                            <span class="badge bg-secondary ms-2">
                                                {{ $maintenance->attachments->count() }}
                                            </span>
                                        </h5>
                                    </div>
                                    <div class="photos-grid">
                                        @foreach ($maintenance->attachments as $attachment)
                                            @php
                                                $ext = pathinfo($attachment->attachment_path, PATHINFO_EXTENSION);
                                                $isImage = in_array(strtolower($ext), [
                                                    'jpg',
                                                    'jpeg',
                                                    'png',
                                                    'gif',
                                                    'bmp',
                                                    'jfif',
                                                ]);
                                                $isVideo = in_array(strtolower($ext), [
                                                    'mp4',
                                                    'mov',
                                                    'webm',
                                                    'mpeg',
                                                    'm4v',
                                                ]);
                                            @endphp
                                            <div class="photo-item">
                                                @if ($isImage)
                                                    <img src="{{ asset('/' . $attachment->attachment_path) }}"
                                                        alt="Attachment"
                                                        onclick="viewImage('{{ asset('/' . $attachment->attachment_path) }}')">
                                                @elseif ($isVideo)
                                                    <video controls class="w-100" style="max-height:200px;">
                                                        <source
                                                            src="{{ asset('/' . $attachment->attachment_path) }}">
                                                    </video>
                                                @else
                                                    <div class="file-preview">
                                                        <i class="fe fe-file"></i>
                                                        <small>{{ strtoupper($ext) }}</small>
                                                        <a href="{{ asset('/' . $attachment->attachment_path) }}"
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

                            {{-- ===== ACTIVITY TIMELINE ===== --}}
                            <div class="detail-section">
                                <h5 class="section-title mb-3">
                                    <i class="fe fe-clock me-2"></i> Activity Timeline
                                </h5>
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
                                                        Submitted by <strong>{{ $fullName }}</strong>
                                                    </p>
                                                </div>
                                                <span class="text-muted small text-nowrap ms-3">
                                                    {{ date('M d, Y g:i A', strtotime($maintenance->created_at)) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($maintenance->status === 'completed')
                                        <div class="timeline-item">
                                            <div class="timeline-icon bg-success">
                                                <i class="fe fe-check"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1">Marked as Resolved</h6>
                                                        <p class="text-muted mb-0 small">
                                                            Request has been resolved.
                                                        </p>
                                                    </div>
                                                    <span class="text-muted small text-nowrap ms-3">
                                                        {{ date('M d, Y', strtotime($maintenance->updated_at)) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Image Modal --}}
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body text-center pt-0">
                    <img src="" id="modalImage" class="img-fluid rounded-1">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const MAINTENANCE_ID = {{ $maintenance->id }};
        const CSRF_TOKEN = '{{ csrf_token() }}';

        /*========================================
         | SEARCH
         ========================================*/
        $('#maintenanceSearch').on('keyup', function() {
            const val = $(this).val().toLowerCase();
            $('.maintenance-item').each(function() {
                $(this).toggle($(this).text().toLowerCase().includes(val));
            });
        });

        /*========================================
         | SIDEBAR NAVIGATION
         ========================================*/
        function loadMaintenanceDetails(id) {
            $('.maintenance-item').removeClass('active');
            $(`.maintenance-item[data-maintenance-id="${id}"]`).addClass('active');
            const url = `{{ route('maintanance.show', '') }}/${id}`;
            window.history.pushState({
                maintenanceId: id
            }, '', url);
            window.location.href = url;
        }

        function toggleSidebar() {
            $('.maintenance-sidebar').toggleClass('show');
        }

        $(document).on('click', function(e) {
            if ($(window).width() < 992) {
                if (!$(e.target).closest('.maintenance-sidebar, .mobile-sidebar-toggle').length) {
                    $('.maintenance-sidebar').removeClass('show');
                }
            }
        });

        window.addEventListener('popstate', function(e) {
            if (e.state?.maintenanceId) loadMaintenanceDetails(e.state.maintenanceId);
            else window.location.href = '{{ route('maintanance.index') }}';
        });

        window.history.replaceState({
            maintenanceId: MAINTENANCE_ID
        }, '', window.location.href);

        /*========================================
         | IMAGE MODAL
         ========================================*/
        function viewImage(src) {
            $('#modalImage').attr('src', src);
            new bootstrap.Modal(document.getElementById('imageModal')).show();
        }

        /*========================================
         | MARK AS RESOLVED
         ========================================*/
        function markAsResolved() {
            Swal.fire({
                title: 'Mark as Resolved?',
                text: 'This will change the status to Resolved.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, mark as resolved'
            }).then((result) => {
                if (result.isConfirmed) {
                    const url = "{{ route('maintanance.markResolved', ':id') }}".replace(':id', MAINTENANCE_ID);
                    $.post(url, {
                            _token: CSRF_TOKEN
                        })
                        .done(function(res) {
                            if (res.success) {
                                // Badge update
                                $('.maintenance-status-badge')
                                    .removeClass()
                                    .addClass(`badge bg-${res.color} fs-6 maintenance-status-badge`)
                                    .text(res.label);

                                // Button swap
                                $('button[onclick="markAsResolved()"]')
                                    .prop('disabled', true)
                                    .html('<i class="fe fe-check me-1"></i> Resolved')
                                    .removeClass('btn-success').addClass('btn-secondary');

                                // Add timeline entry
                                addTimelineEntry('Marked as Resolved', 'Request has been resolved.', 'success',
                                    'check');

                                toastr.success(res.message);
                            }
                        })
                        .fail(() => toastr.error('Failed to update status'));
                }
            });
        }

        /*========================================
         | QUICK STATUS CHANGE (dropdown)
         ========================================*/
        function changeStatus(status) {
            const labels = {
                pending: {
                    label: 'Open',
                    color: 'primary'
                },
                in_progress: {
                    label: 'In Progress',
                    color: 'warning'
                },
                completed: {
                    label: 'Resolved',
                    color: 'success'
                },
                rejected: {
                    label: 'Rejected',
                    color: 'danger'
                },
                cancelled: {
                    label: 'Cancelled',
                    color: 'secondary'
                },
            };

            Swal.fire({
                title: `Change to "${labels[status].label}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'Yes, update'
            }).then((result) => {
                if (result.isConfirmed) {
                    const url = "{{ route('maintanance.updateStatus', ':id') }}".replace(':id', MAINTENANCE_ID);
                    $.post(url, {
                            _token: CSRF_TOKEN,
                            status
                        })
                        .done(function(res) {
                            if (res.success) {
                                // Badge
                                $('.maintenance-status-badge')
                                    .removeClass()
                                    .addClass(`badge bg-${res.color} fs-6 maintenance-status-badge`)
                                    .text(res.label);

                                // Timeline
                                addTimelineEntry(`Status changed to "${res.label}"`, '', res.color,
                                    'refresh-cw');

                                toastr.success(res.message);

                                // Reload page after short delay to refresh dropdown active state
                                setTimeout(() => window.location.reload(), 1200);
                            }
                        })
                        .fail(() => toastr.error('Failed to update status'));
                }
            });
        }

        /*========================================
         | ADD TIMELINE ENTRY DYNAMICALLY
         ========================================*/
        function addTimelineEntry(title, desc, color, icon) {
            const now = new Date();
            const time = now.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
            const html = `
            <div class="timeline-item">
                <div class="timeline-icon bg-${color}">
                    <i class="fe fe-${icon}"></i>
                </div>
                <div class="timeline-content">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">${title}</h6>
                            ${desc ? `<p class="text-muted mb-0 small">${desc}</p>` : ''}
                        </div>
                        <span class="text-muted small text-nowrap ms-3">${time}</span>
                    </div>
                </div>
            </div>`;
            $('.timeline-section').append(html);
        }

        /*========================================
         | DELETE
         ========================================*/
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
                            _token: CSRF_TOKEN
                        },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire('Deleted!', res.message, 'success')
                                    .then(() => window.location.href =
                                        '{{ route('maintanance.index') }}');
                            }
                        },
                        error: () => Swal.fire('Error!', 'Failed to delete request', 'error')
                    });
                }
            });
        }
    </script>
@endpush

@push('styles')
    <style>
        .maintenance-detail-container {
            display: flex;
            height: calc(100vh - 70px);
            background: #fff;
            margin: 15px 0;
        }

        /* Sidebar */
        .maintenance-sidebar {
            width: 350px;
            border-right: 1px solid #e9ecef;
            display: flex;
            flex-direction: column;
            background: #fff;
            flex-shrink: 0;
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
            cursor: pointer;
            border-bottom: 1px solid #f8f9fa;
            transition: all .2s;
        }

        .maintenance-item:hover {
            background: #f8f9fa;
        }

        .maintenance-item.active {
            background: #e3f2fd;
        }

        .maintenance-status-indicator {
            width: 4px;
            flex-shrink: 0;
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

        /* Right content */
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

        /* Header card */
        .maintenance-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 24px;
            border: 1px solid #e9ecef;
        }

        /* Detail info cards */
        .detail-info-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 18px;
            height: 100%;
        }

        .detail-info-header {
            display: flex;
            align-items: center;
            font-size: 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f0f0f0;
        }

        /* Sections */
        .detail-section {
            margin-bottom: 24px;
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 24px;
        }

        .section-title {
            font-weight: 600;
            color: #2c3e50;
            font-size: 16px;
        }

        .description-content {
            background: #f8f9fa;
            padding: 16px 20px;
            border-radius: 8px;
        }

        /* Location hierarchy */
        .location-hierarchy {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 12px 14px;
            border: 1px solid #e9ecef;
            margin-bottom: 8px;
        }

        .hierarchy-step {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .hierarchy-icon-box {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .hierarchy-text {
            display: flex;
            flex-direction: column;
        }

        .hierarchy-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #adb5bd;
            line-height: 1;
        }

        .hierarchy-value {
            font-size: 13px;
            font-weight: 600;
            color: #2c3e50;
            line-height: 1.3;
        }

        .hierarchy-connector {
            color: #dee2e6;
            font-size: 14px;
            padding: 0 2px;
        }

        .loc-icon-wrap {
            width: 28px;
            display: flex;
            justify-content: center;
            flex-shrink: 0;
        }

        /* Photos */
        .photos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
        }

        .photo-item {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: transform .2s;
        }

        .photo-item:hover {
            transform: scale(1.04);
        }

        .photo-item img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            display: block;
        }

        .file-preview {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px;
            background: #f8f9fa;
            height: 180px;
        }

        .file-preview i {
            font-size: 40px;
            color: #6c757d;
            margin-bottom: 8px;
        }

        /* Timeline */
        .timeline-section {
            position: relative;
            padding-left: 40px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 24px;
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
            padding: 14px;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .maintenance-sidebar {
                position: fixed;
                left: -350px;
                top: 0;
                height: 100vh;
                z-index: 1050;
                transition: left .3s;
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
                padding: 16px;
            }

            .content-header {
                padding: 15px 20px;
            }
        }
    </style>
@endpush
