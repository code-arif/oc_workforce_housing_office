@extends('backend.app')

@section('title', 'Compose Mail')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .nav-link:hover {
            color: #3a3a3a !important;
        }
        .select2-container {
            width: 100% !important;
        }
        .sidebar-active {
            background-color: #e8f0fe !important;
            color: #1a73e8 !important;
            font-weight: 600 !important;
        }
        .badge-count {
            background-color: #1a73e8;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 15px;
        }
        .tenant-badge {
            display: inline-block;
            padding: 5px 10px;
            margin: 3px;
            background: #e3f2fd;
            border-radius: 20px;
            font-size: 12px;
        }
        .tenant-badge .remove-tenant {
            cursor: pointer;
            margin-left: 5px;
            color: #dc3545;
        }
        .selected-tenants-box {
            min-height: 60px;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 10px;
            background: #f8f9fa;
        }
        .selected-tenants-box.has-tenants {
            border-color: #28a745;
            background: #f0fff4;
        }
    </style>
@endpush

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Compose Mail</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('messaging.index') }}">Messaging</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Compose</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- Row -->
                <div class="row">
                    <!-- Sidebar -->
                    <div class="col-md-12 col-lg-4 col-xl-3">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <a href="{{ route('messaging.compose') }}" class="btn btn-primary btn-block py-2 w-100">
                                    <i class="fa fa-plus me-2"></i>Compose
                                </a>
                            </div>
                            <div class="card-body">
                                <ul class="nav1 nav-column flex-column br-7">
                                    <li class="nav-item1 mt-0">
                                        <a class="nav-link thumb folder-link" href="{{ route('messaging.index', ['folder' => 'inbox']) }}">
                                            <i class="fa fa-inbox me-2"></i>
                                            Inbox
                                            @if ($counts['unread'] > 0)
                                                <span class="badge-count float-end">{{ $counts['unread'] }}</span>
                                            @endif
                                        </a>
                                    </li>
                                    <li class="nav-item1">
                                        <a class="nav-link thumb folder-link" href="{{ route('messaging.index', ['folder' => 'starred']) }}">
                                            <i class="fa fa-star me-2"></i>
                                            Starred
                                        </a>
                                    </li>
                                    <li class="nav-item1">
                                        <a class="nav-link thumb folder-link" href="{{ route('messaging.index', ['folder' => 'sent']) }}">
                                            <i class="fa fa-send me-2"></i>
                                            Sent
                                        </a>
                                    </li>
                                    <li class="nav-item1">
                                        <a class="nav-link thumb folder-link" href="{{ route('messaging.index', ['folder' => 'drafts']) }}">
                                            <i class="fa fa-file-text me-2"></i>
                                            Drafts
                                        </a>
                                    </li>
                                    <li class="nav-item1">
                                        <a class="nav-link thumb folder-link" href="{{ route('messaging.index', ['folder' => 'trash']) }}">
                                            <i class="fa fa-trash me-2"></i>
                                            Trash
                                        </a>
                                    </li>
                                </ul>

                                @if ($labels && $labels->count() > 0)
                                    <div class="mt-4">
                                        <h6 class="fw-bold mb-3">Labels</h6>
                                        <ul class="nav1 nav-column flex-column br-7">
                                            @foreach ($labels as $label)
                                                <li class="nav-item1">
                                                    <a class="nav-link thumb" href="#">
                                                        <span class="wpx-10 hpx-10 rounded-circle me-2"
                                                            style="background-color: {{ $label->color }}"></span>
                                                        {{ $label->name }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Compose Form -->
                    <div class="col-md-12 col-lg-8 col-xl-9">
                        <!-- Location Filter Card -->
                        <div class="card mb-3">
                            <div class="card-header border-bottom">
                                <h3 class="card-title">
                                    <i class="fa fa-building me-2"></i>Select Recipients by Location
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <label class="form-label">Property</label>
                                        <select class="form-select" id="propertySelect">
                                            <option value="">-- Select Property --</option>
                                            @foreach($properties as $property)
                                                <option value="{{ $property->id }}">{{ $property->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <label class="form-label">Unit (Optional)</label>
                                        <select class="form-select" id="unitSelect" disabled>
                                            <option value="">-- Select Unit --</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Room (Optional)</label>
                                        <select class="form-select" id="roomSelect" disabled>
                                            <option value="">-- Select Room --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <button type="button" class="btn btn-info" id="fetchTenantsBtn">
                                            <i class="fa fa-users me-2"></i>Fetch Tenants with Active Leases
                                        </button>
                                        <span class="ms-3 text-muted" id="tenantCountInfo"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Template Selection Card -->
                        <div class="card mb-3">
                            <div class="card-header border-bottom">
                                <h3 class="card-title">
                                    <i class="fa fa-file-text-o me-2"></i>Use Mail Template
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <select class="form-select" id="templateSelect">
                                            <option value="">-- Select a Template (Optional) --</option>
                                            @foreach($mailTemplates as $template)
                                                <option value="{{ $template->id }}"
                                                    data-variables="{{ json_encode($template->variables ?? []) }}">
                                                    {{ $template->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="button" class="btn btn-primary w-100" id="applyTemplateBtn" disabled>
                                            <i class="fa fa-magic me-2"></i>Apply Template
                                        </button>
                                    </div>
                                </div>
                                <div id="templateVariablesInfo" class="mt-2 text-muted" style="display: none;">
                                    <small><i class="fa fa-info-circle me-1"></i>Available placeholders: <span id="variablesList"></span></small>
                                </div>
                            </div>
                        </div>

                        <!-- Compose Form Card -->
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h3 class="card-title">Compose Mail</h3>
                            </div>
                            <div class="card-body">
                                <form id="composeForm">
                                    <!-- Selected Tenants Display -->
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">To: (Selected Tenants)</label>
                                        <div class="selected-tenants-box" id="selectedTenantsBox">
                                            <span class="text-muted" id="noTenantsMessage">No tenants selected. Use the location filter above or search below.</span>
                                        </div>
                                    </div>

                                    <!-- Manual Tenant Search -->
                                    <div class="form-group mb-3">
                                        <label class="form-label">Or Search Tenants Manually:</label>
                                        <select class="form-select" id="manualTenantSelect" multiple="multiple">
                                        </select>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Cc:</label>
                                            <input type="text" class="form-control" id="ccEmails" placeholder="Cc emails (comma-separated, optional)">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Bcc:</label>
                                            <input type="text" class="form-control" id="bccEmails" placeholder="Bcc emails (comma-separated, optional)">
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Subject: <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="subject" placeholder="Email subject" required>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Message: <span class="text-danger">*</span></label>
                                        <textarea class="form-control summernote" id="emailBody" rows="12" placeholder="Compose your message..."></textarea>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label">Attachments:</label>
                                        <input type="file" class="form-control" id="attachments" multiple>
                                        <small class="text-muted">Max 25MB per file</small>
                                    </div>
                                </form>
                            </div>
                            <div class="card-footer d-sm-flex">
                                <div class="btn-list">
                                    <button type="button" class="btn btn-secondary" id="saveDraftBtn">
                                        <i class="fa fa-save me-2"></i>Save Draft
                                    </button>
                                </div>
                                <div class="btn-list ms-auto">
                                    <a href="{{ route('messaging.index') }}" class="btn btn-danger me-2">
                                        <i class="fa fa-times me-2"></i>Cancel
                                    </a>
                                    <button type="button" class="btn btn-primary" id="sendEmailBtn">
                                        <i class="fa fa-send me-2"></i>Send Email
                                    </button>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Store selected tenants
        let selectedTenants = [];

        $(document).ready(function() {
            // Initialize Summernote
            $('.summernote').summernote({
                height: 250,
                placeholder: 'Write your email message here...',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'italic', 'clear']],
                    ['fontname', ['fontname']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });

            // Initialize Select2 for manual tenant search
            $('#manualTenantSelect').select2({
                theme: 'bootstrap-5',
                placeholder: 'Search tenants by name or email...',
                allowClear: true,
                ajax: {
                    url: '{{ route('messaging.tenants.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return { q: params.term };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results.map(function(tenant) {
                                return {
                                    id: tenant.email,
                                    text: tenant.text,
                                    email: tenant.email,
                                    name: tenant.name
                                };
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0
            });

            // When manual selection changes, add to selected tenants
            $('#manualTenantSelect').on('select2:select', function(e) {
                addTenant({
                    id: e.params.data.id,
                    email: e.params.data.email,
                    name: e.params.data.name,
                    text: e.params.data.text
                });
                // Clear the selection
                $(this).val(null).trigger('change');
            });

            // Property change - load units
            $('#propertySelect').change(function() {
                const propertyId = $(this).val();
                $('#unitSelect').val('').prop('disabled', true);
                $('#roomSelect').val('').prop('disabled', true);

                if (propertyId) {
                    $.ajax({
                        url: '{{ route('messaging.units') }}',
                        data: { property_id: propertyId },
                        success: function(response) {
                            let options = '<option value="">-- Select Unit --</option>';
                            response.units.forEach(function(unit) {
                                options += `<option value="${unit.id}">${unit.name}</option>`;
                            });
                            $('#unitSelect').html(options).prop('disabled', false);
                        }
                    });
                }
            });

            // Unit change - load rooms
            $('#unitSelect').change(function() {
                const unitId = $(this).val();
                $('#roomSelect').val('').prop('disabled', true);

                if (unitId) {
                    $.ajax({
                        url: '{{ route('messaging.rooms') }}',
                        data: { unit_id: unitId },
                        success: function(response) {
                            let options = '<option value="">-- Select Room --</option>';
                            response.rooms.forEach(function(room) {
                                options += `<option value="${room.id}"> ${room.room_number}</option>`;
                            });
                            $('#roomSelect').html(options).prop('disabled', false);
                        }
                    });
                }
            });

            // Fetch tenants button
            $('#fetchTenantsBtn').click(function() {
                const propertyId = $('#propertySelect').val();
                const unitId = $('#unitSelect').val();
                const roomId = $('#roomSelect').val();

                if (!propertyId) {
                    toastr.warning('Please select a property first');
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i>Fetching...');

                $.ajax({
                    url: '{{ route('messaging.tenants.by-location') }}',
                    data: {
                        property_id: propertyId,
                        unit_id: unitId,
                        room_id: roomId
                    },
                    success: function(response) {
                        if (response.success) {
                            // Add fetched tenants to selection
                            response.tenants.forEach(function(tenant) {
                                addTenant(tenant);
                            });

                            $('#tenantCountInfo').text(`Found ${response.count} tenant(s) with active leases`);

                            if (response.count === 0) {
                                toastr.info('No tenants with active leases found for the selected location');
                            } else {
                                toastr.success(`Added ${response.count} tenant(s) to recipients`);
                            }
                        }
                    },
                    error: function() {
                        toastr.error('Failed to fetch tenants');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fa fa-users me-2"></i>Fetch Tenants with Active Leases');
                    }
                });
            });

            // Template selection change
            $('#templateSelect').change(function() {
                const templateId = $(this).val();
                if (templateId) {
                    $('#applyTemplateBtn').prop('disabled', false);
                    const variables = $(this).find(':selected').data('variables');
                    if (variables && variables.length > 0) {
                        $('#variablesList').text(variables.map(v => '@{{' + v + '}}').join(', '));
                        $('#templateVariablesInfo').show();
                    } else {
                        $('#templateVariablesInfo').hide();
                    }
                } else {
                    $('#applyTemplateBtn').prop('disabled', true);
                    $('#templateVariablesInfo').hide();
                }
            });

            // Apply template
            $('#applyTemplateBtn').click(function() {
                const templateId = $('#templateSelect').val();
                if (!templateId) return;

                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i>Loading...');

                $.ajax({
                    url: '{{ route('messaging.mail-template') }}',
                    data: { template_id: templateId },
                    success: function(response) {
                        if (response.success) {
                            $('#subject').val(response.template.subject);
                            $('.summernote').summernote('code', response.template.body);
                            toastr.success('Template applied successfully');
                        }
                    },
                    error: function() {
                        toastr.error('Failed to load template');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fa fa-magic me-2"></i>Apply Template');
                    }
                });
            });

            // Send Email
            $('#sendEmailBtn').click(function() {
                if (selectedTenants.length === 0) {
                    toastr.error('Please select at least one recipient');
                    return;
                }

                const subject = $('#subject').val();
                const body = $('.summernote').summernote('code');

                if (!subject.trim()) {
                    toastr.error('Please enter an email subject');
                    return;
                }

                if (!body.trim() || body === '<p><br></p>') {
                    toastr.error('Please enter an email message');
                    return;
                }

                const ccEmails = $('#ccEmails').val() ? $('#ccEmails').val().split(',').map(e => e.trim()).filter(e => e) : [];
                const bccEmails = $('#bccEmails').val() ? $('#bccEmails').val().split(',').map(e => e.trim()).filter(e => e) : [];

                const formData = new FormData();

                // Add recipients
                selectedTenants.forEach((tenant, index) => {
                    formData.append(`to[${index}][email]`, tenant.email);
                });

                ccEmails.forEach((email, index) => {
                    formData.append(`cc[${index}][email]`, email);
                });

                bccEmails.forEach((email, index) => {
                    formData.append(`bcc[${index}][email]`, email);
                });

                formData.append('subject', subject);
                formData.append('body', body);

                // Add attachments
                const files = $('#attachments')[0].files;
                for (let i = 0; i < files.length; i++) {
                    formData.append('attachments[]', files[i]);
                }

                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i>Sending...');

                $.ajax({
                    url: '{{ route('messaging.send') }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Email sent successfully!');
                            setTimeout(() => {
                                window.location.href = '{{ route('messaging.index', ['folder' => 'sent']) }}';
                            }, 1500);
                        } else {
                            toastr.error(response.message || 'Failed to send email');
                        }
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'An error occurred';
                        toastr.error(errorMsg);
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fa fa-send me-2"></i>Send Email');
                    }
                });
            });

            // Save Draft
            $('#saveDraftBtn').click(function() {
                const toEmails = selectedTenants.map(t => ({ email: t.email }));
                const ccEmails = $('#ccEmails').val() ? $('#ccEmails').val().split(',').map(e => e.trim()).filter(e => e).map(email => ({ email })) : [];
                const bccEmails = $('#bccEmails').val() ? $('#bccEmails').val().split(',').map(e => e.trim()).filter(e => e).map(email => ({ email })) : [];

                $.ajax({
                    url: '{{ route('messaging.draft.save') }}',
                    method: 'POST',
                    data: {
                        to: toEmails,
                        cc: ccEmails,
                        bcc: bccEmails,
                        subject: $('#subject').val(),
                        body: $('.summernote').summernote('code'),
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success('Draft saved successfully');
                    },
                    error: function() {
                        toastr.error('Failed to save draft');
                    }
                });
            });
        });

        // Add tenant to selected list
        function addTenant(tenant) {
            // Check if already exists
            if (selectedTenants.find(t => t.email === tenant.email)) {
                return;
            }

            selectedTenants.push({
                id: tenant.id,
                email: tenant.email,
                name: tenant.name || tenant.email,
                text: tenant.text || tenant.email
            });

            updateSelectedTenantsDisplay();
        }

        // Remove tenant from selected list
        function removeTenant(email) {
            selectedTenants = selectedTenants.filter(t => t.email !== email);
            updateSelectedTenantsDisplay();
        }

        // Update the display of selected tenants
        function updateSelectedTenantsDisplay() {
            const box = $('#selectedTenantsBox');

            if (selectedTenants.length === 0) {
                box.removeClass('has-tenants');
                box.html('<span class="text-muted" id="noTenantsMessage">No tenants selected. Use the location filter above or search below.</span>');
            } else {
                box.addClass('has-tenants');
                let html = '';
                selectedTenants.forEach(function(tenant) {
                    html += `
                        <span class="tenant-badge">
                            <i class="fa fa-user me-1"></i>${tenant.name} &lt;${tenant.email}&gt;
                            <span class="remove-tenant" onclick="removeTenant('${tenant.email}')">&times;</span>
                        </span>
                    `;
                });
                box.html(html);
            }
        }
    </script>
@endpush
