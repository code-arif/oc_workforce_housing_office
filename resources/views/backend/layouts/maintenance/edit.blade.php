@extends('backend.app')

@section('title', 'Edit Maintenance Request')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title">Edit Maintenance Request</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('maintanance.index') }}">Maintenance</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12 col-lg-12 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Edit Request #{{ $maintenance->id }}</h3>
                            </div>
                            <div class="card-body">
                                <form id="maintenanceForm" action="{{ route('maintanance.update', $maintenance->id) }}"
                                    method="POST" enctype="multipart/form-data">
                                    @csrf

                                    {{-- Row 1: Title + Tenant --}}
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label">Ticket Title <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="title"
                                                value="{{ $maintenance->title }}" placeholder="Enter Ticket Title" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Requested By <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select" name="tenant_id" id="tenantSelect" required>
                                                <option value="">Select Tenant</option>
                                                @foreach ($tenants as $tenant)
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
                                                            : $tenant->email;
                                                    @endphp
                                                    <option value="{{ $tenant->id }}"
                                                        {{ $maintenance->tenant_id == $tenant->id ? 'selected' : '' }}>
                                                        {{ $fullName }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- CASCADING: Property → Unit → Room → Bed --}}
                                    <div id="locationSection">

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">
                                                    <i class="fe fe-home me-1 text-primary"></i>Property
                                                </label>
                                                <select class="form-select" name="property_id" id="propertySelect">
                                                    <option value="">Loading...</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6" id="unitCol" style="display:none;">
                                                <label class="form-label">
                                                    <i class="fe fe-layers me-1 text-info"></i>Unit
                                                </label>
                                                <select class="form-select" name="unit_id" id="unitSelect">
                                                    <option value="">-- Select Unit --</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6" id="roomCol" style="display:none;">
                                                <label class="form-label">
                                                    <i class="fe fe-grid me-1 text-warning"></i>Room
                                                </label>
                                                <select class="form-select" name="room_id" id="roomSelect">
                                                    <option value="">-- Select Room --</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6" id="bedCol" style="display:none;">
                                                <label class="form-label">
                                                    <i class="fe fe-moon me-1 text-success"></i>Bed
                                                </label>
                                                <select class="form-select" name="bed_id" id="bedSelect">
                                                    <option value="">-- Select Bed --</option>
                                                </select>
                                            </div>
                                        </div>

                                        {{-- Lease Info Card --}}
                                        <div class="mb-4" id="leaseInfoBox" style="display:none;"></div>

                                        {{-- No Lease Warning --}}
                                        <div class="mb-4" id="noLeaseBox" style="display:none;">
                                            <div class="alert alert-warning d-flex align-items-center mb-0">
                                                <i class="fe fe-alert-triangle me-2"></i>
                                                <span>This tenant has no active lease.</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Category --}}
                                    <div class="mb-4">
                                        <label class="form-label">Category <span class="text-danger">*</span></label>
                                        <div class="row g-3">
                                            @foreach ([
            'ac' => ['fe-wind', 'A/C'],
            'appliance' => ['fe-box', 'Appliance'],
            'electrical' => ['fe-zap', 'Electrical'],
            'heat' => ['fe-thermometer', 'Heat'],
            'plumbing' => ['fe-droplet', 'Plumbing'],
            'other' => ['fe-more-horizontal', 'Other'],
        ] as $value => [$icon, $label])
                                                <div class="col-md-2 col-sm-6">
                                                    <input type="radio" class="btn-check" name="category"
                                                        id="category_{{ $value }}" value="{{ $value }}"
                                                        {{ $maintenance->category == $value ? 'checked' : '' }} required>
                                                    <label class="category-card" for="category_{{ $value }}">
                                                        <div class="category-icon"><i class="fe {{ $icon }}"></i>
                                                        </div>
                                                        <div class="category-label">{{ $label }}</div>
                                                    </label>
                                                </div>
                                            @endforeach
                                            <div class="col-md-2 col-sm-6">
                                                <input type="radio" class="btn-check" name="category"
                                                    id="category_kitchen" value="kitchen"
                                                    {{ $maintenance->category == 'kitchen' ? 'checked' : '' }}>
                                                <label class="category-card" for="category_kitchen">
                                                    <div class="category-icon">
                                                        <i class="fa-solid fa-mug-hot" style="font-size:25px"></i>
                                                    </div>
                                                    <div class="category-label">Kitchen</div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Description --}}
                                    <div class="mb-4">
                                        <label class="form-label">Description <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="description" rows="5" required>{{ $maintenance->description }}</textarea>
                                    </div>

                                    {{-- Status --}}
                                    <div class="mb-4">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select" name="status" required>
                                            @foreach ([
            'pending' => 'Open',
            'in_progress' => 'In Progress',
            'completed' => 'Resolved',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
        ] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ $maintenance->status == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Checkboxes --}}
                                    <div class="mb-4">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="is_urgent"
                                                id="isUrgent" value="1"
                                                {{ $maintenance->is_urgent ? 'checked' : '' }}>
                                            <label class="form-check-label" for="isUrgent">Mark as Urgent</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="grant_permission"
                                                id="grantPermission" value="1"
                                                {{ $maintenance->grant_permission ? 'checked' : '' }}>
                                            <label class="form-check-label" for="grantPermission">
                                                Consent to enter the premises: Granted
                                            </label>
                                        </div>
                                    </div>

                                    {{-- Existing Attachments --}}
                                    @if ($maintenance->attachments->count() > 0)
                                        <div class="mb-4">
                                            <label class="form-label">Existing Attachments</label>
                                            <div class="row g-3">
                                                @foreach ($maintenance->attachments as $attachment)
                                                    @php
                                                        $ext = pathinfo(
                                                            $attachment->attachment_path,
                                                            PATHINFO_EXTENSION,
                                                        );
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
                                                    <div class="col-md-3">
                                                        <div class="file-preview-item">
                                                            @if ($isImage)
                                                                <img src="{{ asset('storage/' . $attachment->attachment_path) }}"
                                                                    alt="Attachment">
                                                            @elseif ($isVideo)
                                                                <video style="width:100%;height:100%;object-fit:cover;">
                                                                    <source
                                                                        src="{{ asset('storage/' . $attachment->attachment_path) }}">
                                                                </video>
                                                            @else
                                                                <div class="file-icon">
                                                                    <i class="fe fe-file"></i>
                                                                    <small class="mt-2">{{ strtoupper($ext) }}</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- New File Upload --}}
                                    <div class="mb-4">
                                        <label class="form-label">Add More Photos, Videos, and Documents
                                            <span class="text-muted">(0/20)</span>
                                        </label>
                                        <div class="upload-area" id="uploadArea">
                                            <div class="upload-placeholder">
                                                <i class="fe fe-image upload-icon"></i>
                                                <h6 class="mt-3">Drag & Drop</h6>
                                                <p class="text-muted mb-1">or <span
                                                        class="text-primary browse-text">browse</span></p>
                                                <small class="text-muted">Supported: .jpg .jpeg .png .pdf .mp4 .mov (max
                                                    20MB)</small>
                                            </div>
                                        </div>
                                        <input type="file" name="attachments[]" id="fileInput" multiple
                                            accept=".jpg,.jpeg,.png,.pdf,.bmp,.jfif,.mp4,.mov,.webm,.mpeg,.m4v"
                                            style="display:none;">
                                        <div id="filePreview" class="mt-3 row g-3"></div>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('maintanance.index') }}" class="btn btn-light">Cancel</a>
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="fe fe-save me-1"></i> Update Request
                                        </button>
                                    </div>
                                </form>
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
        $(document).ready(function() {

            // Existing values to pre-select
            const CURRENT = {
                tenantId: {{ $maintenance->tenant_id ?? 'null' }},
                propertyId: {{ $maintenance->property_id ?? 'null' }},
                unitId: {{ $maintenance->unit_id ?? 'null' }},
                roomId: {{ $maintenance->room_id ?? 'null' }},
                bedId: {{ $maintenance->bed_id ?? 'null' }},
            };

            const ROUTES = {
                leaseProperties: '{{ route('maintanance.tenant.lease.properties') }}',
                propertyUnits: '{{ route('maintanance.property.units') }}',
                unitRooms: '{{ route('maintanance.unit.rooms') }}',
                roomBeds: '{{ route('maintanance.room.beds') }}',
            };

            /*==============================================
             | LOAD LEASE PROPERTIES (reusable function)
             ==============================================*/
            function loadLeaseProperties(tenantId, preSelectPropertyId) {
                $('#propertySelect').html('<option value="">Loading...</option>').prop('disabled', true);
                $('#leaseInfoBox').hide().html('');
                $('#noLeaseBox').hide();

                $.get(ROUTES.leaseProperties, {
                        tenant_id: tenantId
                    })
                    .done(function(res) {
                        $('#propertySelect').html('<option value="">-- Select Property --</option>');

                        if (res.success && res.leases.length > 0) {
                            res.leases.forEach(function(lease) {
                                $('#propertySelect').append(
                                    `<option value="${lease.property_id}" data-lease='${JSON.stringify(lease)}'>
                                ${lease.property_name}${lease.address ? ' · ' + lease.address : ''} (${lease.status})
                            </option>`
                                );
                            });
                            $('#propertySelect').prop('disabled', false);

                            // Pre-select existing property
                            const target = preSelectPropertyId ||
                                (res.leases.find(l => l.status === 'ACTIVE') || res.leases[0])?.property_id;
                            if (target) $('#propertySelect').val(target);

                            $('#propertySelect').trigger('change');
                        } else {
                            $('#propertySelect').html('<option value="">No lease found</option>').prop(
                                'disabled', true);
                            $('#noLeaseBox').show();
                        }
                    })
                    .fail(() => {
                        toastr.error('Failed to load lease properties');
                        $('#propertySelect').prop('disabled', false);
                    });
            }

            // Auto-load on page open
            if (CURRENT.tenantId) {
                loadLeaseProperties(CURRENT.tenantId, CURRENT.propertyId);
            }

            // Tenant change
            $('#tenantSelect').on('change', function() {
                const tenantId = $(this).val();
                resetFrom('property');
                $('#leaseInfoBox').hide().html('');
                $('#noLeaseBox').hide();
                if (!tenantId) return;
                loadLeaseProperties(tenantId, null);
            });

            /*==============================================
             | PROPERTY → UNITS
             ==============================================*/
            $('#propertySelect').on('change', function() {
                const propertyId = $(this).val();
                const leaseData = $(this).find(':selected').data('lease');

                resetFrom('unit');
                $('#leaseInfoBox').hide().html('');

                if (!propertyId) return;
                if (leaseData) renderLeaseCard(leaseData);

                $('#unitCol').show();
                $('#unitSelect').html('<option value="">Loading...</option>').prop('disabled', true);

                $.get(ROUTES.propertyUnits, {
                        property_id: propertyId
                    })
                    .done(function(res) {
                        $('#unitSelect').html('<option value="">-- Select Unit --</option>');
                        if (res.units?.length > 0) {
                            res.units.forEach(u => $('#unitSelect').append(
                                `<option value="${u.id}">${u.name}</option>`));
                            $('#unitSelect').prop('disabled', false);

                            const target = CURRENT.unitId || leaseData?.unit_id;
                            if (target) $('#unitSelect').val(target);
                            $('#unitSelect').trigger('change');
                        } else {
                            $('#unitSelect').html('<option value="">No units found</option>').prop(
                                'disabled', true);
                        }
                    });
            });

            /*==============================================
             | UNIT → ROOMS
             ==============================================*/
            $('#unitSelect').on('change', function() {
                const unitId = $(this).val();
                const leaseData = $('#propertySelect').find(':selected').data('lease');
                resetFrom('room');

                if (!unitId) return;

                $('#roomCol').show();
                $('#roomSelect').html('<option value="">Loading...</option>').prop('disabled', true);

                $.get(ROUTES.unitRooms, {
                        unit_id: unitId
                    })
                    .done(function(res) {
                        $('#roomSelect').html('<option value="">-- Select Room --</option>');
                        if (res.rooms?.length > 0) {
                            res.rooms.forEach(r => $('#roomSelect').append(
                                `<option value="${r.id}">${r.name}</option>`));
                            $('#roomSelect').prop('disabled', false);

                            const target = CURRENT.roomId || leaseData?.room_id;
                            if (target) $('#roomSelect').val(target);
                            $('#roomSelect').trigger('change');
                        } else {
                            $('#roomSelect').html('<option value="">No rooms found</option>').prop(
                                'disabled', true);
                        }
                    });
            });

            /*==============================================
             | ROOM → BEDS
             ==============================================*/
            $('#roomSelect').on('change', function() {
                const roomId = $(this).val();
                const leaseData = $('#propertySelect').find(':selected').data('lease');
                resetFrom('bed');

                if (!roomId) return;

                $('#bedCol').show();
                $('#bedSelect').html('<option value="">Loading...</option>').prop('disabled', true);

                $.get(ROUTES.roomBeds, {
                        room_id: roomId
                    })
                    .done(function(res) {
                        $('#bedSelect').html('<option value="">-- Select Bed --</option>');
                        if (res.beds?.length > 0) {
                            res.beds.forEach(b => {
                                const occupied = b.is_occupied ? ' (Occupied)' : '';
                                const rent = b.base_rent > 0 ?
                                    ` · $${parseFloat(b.base_rent).toFixed(2)}` : '';
                                $('#bedSelect').append(
                                    `<option value="${b.id}">${b.label}${rent}${occupied}</option>`
                                    );
                            });
                            $('#bedSelect').prop('disabled', false);

                            const target = CURRENT.bedId || leaseData?.bed_id;
                            if (target) $('#bedSelect').val(target);
                        } else {
                            $('#bedSelect').html('<option value="">No beds found</option>').prop(
                                'disabled', true);
                        }
                    });
            });

            /*==============================================
             | HELPERS
             ==============================================*/
            function resetFrom(level) {
                const levels = ['unit', 'room', 'bed'];
                const start = levels.indexOf(level);
                if (start === -1) return;
                levels.slice(start).forEach(l => {
                    $(`#${l}Col`).hide();
                    $(`#${l}Select`).html(
                        `<option value="">-- Select ${l.charAt(0).toUpperCase()+l.slice(1)} --</option>`
                        ).prop('disabled', false);
                });
            }

            function renderLeaseCard(lease) {
                const statusColors = {
                    'ACTIVE': 'success',
                    'PENDING_TENANT_SIGN': 'warning',
                    'PENDING_ADMIN_SIGN': 'info',
                    'DRAFT': 'secondary',
                    'TERMINATED': 'danger',
                    'COMPLETED': 'primary'
                };
                const color = statusColors[lease.status] || 'secondary';
                const parts = [];
                if (lease.unit_name) parts.push(
                    `<span class="hier-chip chip-unit"><i class="fe fe-layers me-1"></i>${lease.unit_name}</span>`
                    );
                if (lease.room_name) parts.push(
                    `<span class="hier-chip chip-room"><i class="fe fe-grid me-1"></i>${lease.room_name}</span>`
                    );
                if (lease.bed_label || lease.bed_number) {
                    parts.push(
                        `<span class="hier-chip chip-bed"><i class="fe fe-moon me-1"></i>${lease.bed_label ?? 'Bed '+lease.bed_number}</span>`
                        );
                }

                $('#leaseInfoBox').html(`
            <div class="lease-info-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fe fe-file-text text-primary fs-5"></i>
                        <strong>Lease Details</strong>
                    </div>
                    <span class="badge bg-${color}">${lease.status.replace(/_/g,' ')}</span>
                </div>
                <div class="row g-3 mb-2">
                    <div class="col-sm-4">
                        <small class="text-muted d-block">Property</small>
                        <span class="fw-semibold">${lease.property_name}</span>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted d-block">Period</small>
                        <span>${formatDate(lease.start_date)} – ${formatDate(lease.end_date)}</span>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted d-block">Rent</small>
                        <span class="fw-semibold text-success">$${parseFloat(lease.rent_amount).toFixed(2)}/mo</span>
                    </div>
                </div>
                ${parts.length > 0 ? `
                    <div class="hierarchy-chips">
                        <small class="text-muted me-2">Assignment:</small>
                        ${parts.join('<i class="fe fe-chevron-right text-muted mx-1"></i>')}
                    </div>` : ''}
            </div>
        `).show();
            }

            function formatDate(d) {
                if (!d) return 'N/A';
                return new Date(d).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
            }

            /*==============================================
             | FILE UPLOAD
             ==============================================*/
            let selectedFiles = [];
            const maxFiles = 20;

            function updateFileCounter() {
                $('.upload-area').prev('label').find('span').text(`(${selectedFiles.length}/${maxFiles})`);
            }

            $('#uploadArea').on('click', e => {
                e.preventDefault();
                $('#fileInput').click();
            });
            $('#uploadArea').on('dragover dragenter', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('drag-over');
            });
            $('#uploadArea').on('dragleave dragend', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');
            });
            $('#uploadArea').on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');
                handleFiles(e.originalEvent.dataTransfer.files);
            });
            $('#fileInput').on('change', function() {
                handleFiles(this.files);
                $(this).val('');
            });

            function handleFiles(files) {
                Array.from(files).forEach(file => {
                    if (selectedFiles.length >= maxFiles) {
                        toastr.error(`Max ${maxFiles} files`);
                        return;
                    }
                    if (file.size > 20 * 1024 * 1024) {
                        toastr.error(`"${file.name}" exceeds 20MB`);
                        return;
                    }
                    selectedFiles.push(file);
                    displayFilePreview(file);
                });
                updateFileCounter();
            }

            function displayFilePreview(file) {
                const reader = new FileReader(),
                    idx = selectedFiles.length - 1;
                reader.onload = function(e) {
                    const type = file.type.split('/')[0],
                        ext = file.name.split('.').pop().toUpperCase();
                    let html;
                    if (type === 'image') {
                        html =
                            `<div class="col-md-3 file-preview-col" data-file-index="${idx}"><div class="file-preview-item"><img src="${e.target.result}" alt=""><button type="button" class="remove-file" onclick="removeFile(${idx})"><i class="fe fe-x"></i></button></div></div>`;
                    } else if (type === 'video') {
                        html =
                            `<div class="col-md-3 file-preview-col" data-file-index="${idx}"><div class="file-preview-item"><video style="width:100%;height:100%;object-fit:cover;"><source src="${e.target.result}"></video><button type="button" class="remove-file" onclick="removeFile(${idx})"><i class="fe fe-x"></i></button></div></div>`;
                    } else {
                        html =
                            `<div class="col-md-3 file-preview-col" data-file-index="${idx}"><div class="file-preview-item"><div class="file-icon"><i class="fe fe-file"></i><small class="mt-2">${ext}</small></div><button type="button" class="remove-file" onclick="removeFile(${idx})"><i class="fe fe-x"></i></button></div></div>`;
                    }
                    $('#filePreview').append(html);
                };
                reader.readAsDataURL(file);
            }

            window.removeFile = function(index) {
                selectedFiles.splice(index, 1);
                $(`.file-preview-col[data-file-index="${index}"]`).remove();
                $('.file-preview-col').each(function(i) {
                    $(this).attr('data-file-index', i).find('.remove-file').attr('onclick',
                        `removeFile(${i})`);
                });
                updateFileCounter();
            };

            /*==============================================
             | FORM SUBMIT
             ==============================================*/
            $('#maintenanceForm').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.delete('attachments[]');
                selectedFiles.forEach(f => formData.append('attachments[]', f));

                const btn = $('#submitBtn'),
                    orig = btn.html();
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Processing...');

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message);
                            setTimeout(() => window.location.href = res.redirect, 1000);
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html(orig);
                        if (xhr.responseJSON?.errors) $.each(xhr.responseJSON.errors, (k, v) =>
                            toastr.error(v[0]));
                        else toastr.error('Something went wrong.');
                    }
                });
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .category-card {
            display: block;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            transition: all .3s;
            background: #fff;
            height: 100%;
        }

        .category-card:hover {
            border-color: #D9A600;
            background: #f8f9fa;
        }

        .btn-check:checked+.category-card {
            border-color: #D9A600;
            background: #d9a6001f;
        }

        .category-icon {
            font-size: 36px;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .btn-check:checked+.category-card .category-icon {
            color: #D9A600;
        }

        .category-label {
            font-weight: 500;
            color: #2c3e50;
        }

        .upload-area {
            border: 2px dashed #e9ecef;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all .3s;
            background: #fafbfc;
        }

        .upload-area:hover,
        .upload-area.drag-over {
            border-color: #D9A600;
            background: #f0f7ff;
        }

        .upload-icon {
            font-size: 48px;
            color: #cbd5e0;
        }

        .browse-text {
            cursor: pointer;
            text-decoration: underline;
        }

        .upload-placeholder {
            pointer-events: none;
        }

        .file-preview-item {
            position: relative;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
        }

        .file-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .file-icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 20px;
        }

        .file-icon i {
            font-size: 48px;
            color: #6c757d;
        }

        .file-icon small {
            font-weight: 600;
            color: #6c757d;
        }

        .remove-file {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(220, 53, 69, .9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            padding: 0;
            z-index: 10;
        }

        .remove-file:hover {
            background: #dc3545;
        }

        /* Lease Info Card */
        .lease-info-card {
            background: #f8f9fc;
            border: 1px solid #e3e8f0;
            border-left: 4px solid #0d6efd;
            border-radius: 10px;
            padding: 18px 20px;
        }

        .hierarchy-chips {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 4px;
            margin-top: 8px;
        }

        .hier-chip {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .chip-unit {
            background: #e3f6fc;
            color: #0891b2;
        }

        .chip-room {
            background: #fef9e7;
            color: #d97706;
        }

        .chip-bed {
            background: #f0fdf4;
            color: #16a34a;
        }
    </style>
@endpush
