@extends('backend.app')

@section('title', 'Inbox')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .email-row {
            cursor: pointer;
            transition: all 0.2s;
        }

        .email-row:hover {
            background-color: #f8f9fa;
        }

        .email-row.unread {
            background-color: #f0f4ff;
            font-weight: 600;
        }

        .email-subject {
            font-weight: 500;
            color: #333;
        }

        .email-preview {
            color: #666;
            font-size: 13px;
        }

        .compose-modal .modal-dialog {
            max-width: 700px;
            margin: 30px auto;
        }

        .compose-modal .modal-content {
            border-radius: 8px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
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
        }

        .email-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .star-icon {
            cursor: pointer;
            color: #dadce0;
            transition: color 0.2s;
        }

        .star-icon.starred {
            color: #f9ab00;
        }

        .star-icon:hover {
            color: #f9ab00;
        }

        .select2-container--bootstrap-5 .select2-selection {
            min-height: 38px;
        }

        .recipient-type-tabs .nav-link {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .recipient-type-tabs .nav-link.active {
            background-color: #1a73e8;
            color: white;
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
                        <h1 class="page-title">Inbox</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <button class="btn btn-primary" id="syncEmailsBtn">
                            <i class="fa fa-refresh me-2"></i>Sync Emails
                        </button>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- Row -->
                <div class="row">
                    <!-- Sidebar -->
                    <div class="col-md-12 col-lg-4 col-xl-3">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <button class="btn btn-primary btn-block py-2 w-100" id="composeBtn">
                                    <i class="fa fa-plus me-2"></i>Compose
                                </button>
                            </div>
                            <div class="card-body">
                                <ul class="nav1 nav-column flex-column br-7">
                                    <li class="nav-item1 mt-0">
                                        <a class="nav-link thumb folder-link {{ $folder == 'inbox' ? 'sidebar-active' : '' }}"
                                            href="{{ route('messaging.index', ['folder' => 'inbox']) }}"
                                            data-folder="inbox">
                                            <i class="fa fa-inbox me-2"></i>
                                            Inbox
                                            @if ($counts['unread'] > 0)
                                                <span class="badge-count float-end">{{ $counts['unread'] }}</span>
                                            @endif
                                        </a>
                                    </li>
                                    <li class="nav-item1">
                                        <a class="nav-link thumb folder-link {{ $folder == 'starred' ? 'sidebar-active' : '' }}"
                                            href="{{ route('messaging.index', ['folder' => 'starred']) }}"
                                            data-folder="starred">
                                            <i class="fa fa-star me-2"></i>
                                            Starred
                                            @if ($counts['starred'] > 0)
                                                <span class="badge-count float-end">{{ $counts['starred'] }}</span>
                                            @endif
                                        </a>
                                    </li>
                                    <li class="nav-item1">
                                        <a class="nav-link thumb folder-link {{ $folder == 'sent' ? 'sidebar-active' : '' }}"
                                            href="{{ route('messaging.index', ['folder' => 'sent']) }}" data-folder="sent">
                                            <i class="fa fa-send me-2"></i>
                                            Sent
                                            @if ($counts['sent'] > 0)
                                                <span class="badge-count float-end">{{ $counts['sent'] }}</span>
                                            @endif
                                        </a>
                                    </li>
                                    <li class="nav-item1">
                                        <a class="nav-link thumb folder-link {{ $folder == 'drafts' ? 'sidebar-active' : '' }}"
                                            href="{{ route('messaging.index', ['folder' => 'drafts']) }}"
                                            data-folder="drafts">
                                            <i class="fa fa-file-text me-2"></i>
                                            Drafts
                                            @if ($counts['drafts'] > 0)
                                                <span class="badge-count float-end">{{ $counts['drafts'] }}</span>
                                            @endif
                                        </a>
                                    </li>
                                    <li class="nav-item1">
                                        <a class="nav-link thumb folder-link {{ $folder == 'trash' ? 'sidebar-active' : '' }}"
                                            href="{{ route('messaging.index', ['folder' => 'trash']) }}"
                                            data-folder="trash">
                                            <i class="fa fa-trash me-2"></i>
                                            Trash
                                            @if ($counts['trash'] > 0)
                                                <span class="badge-count float-end">{{ $counts['trash'] }}</span>
                                            @endif
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

                    <!-- Email List -->
                    <div class="col-md-12 col-lg-8 col-xl-9">
                        <div class="card">
                            <div class="card-body p-0">
                                <!-- Toolbar -->
                                <div class="p-3 border-bottom">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <input type="checkbox" class="email-checkbox" id="selectAll">
                                        </div>
                                        <div class="btn-group me-2">
                                            <button class="btn btn-sm btn-white" id="refreshBtn" title="Refresh">
                                                <i class="fa fa-refresh"></i>
                                            </button>
                                        </div>
                                        <div class="btn-group me-2" id="bulkActions" style="display: none;">
                                            <button class="btn btn-sm btn-white" id="markReadBtn" title="Mark as read">
                                                <i class="fa fa-envelope-open"></i>
                                            </button>
                                            <button class="btn btn-sm btn-white" id="markUnreadBtn" title="Mark as unread">
                                                <i class="fa fa-envelope"></i>
                                            </button>
                                            <button class="btn btn-sm btn-white" id="deleteBtn" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                        <div class="ms-auto">
                                            <span class="text-muted">1-{{ $messages->count() }} of
                                                {{ $messages->total() }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Email List -->
                                <div class="inbox-body" id="emailList">
                                    @forelse($messages as $message)
                                        <div class="email-row p-3 border-bottom {{ !$message->is_read ? 'unread' : '' }}"
                                            data-id="{{ $message->id }}" onclick="viewEmail({{ $message->id }})">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3" onclick="event.stopPropagation()">
                                                    <input type="checkbox" class="email-checkbox email-select"
                                                        value="{{ $message->id }}">
                                                </div>
                                                <div class="me-3" onclick="event.stopPropagation()">
                                                    <i class="fa fa-star star-icon {{ $message->is_starred ? 'starred' : '' }}"
                                                        data-id="{{ $message->id }}"
                                                        onclick="toggleStar({{ $message->id }})"></i>
                                                </div>
                                                <div class="flex-grow-1" style="min-width: 0;">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-grow-1" style="min-width: 0;">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <span class="email-subject me-2">
                                                                    {{ $message->from_name ?: $message->from_email }}
                                                                </span>
                                                                @if ($message->has_attachments)
                                                                    <i class="fa fa-paperclip text-muted"></i>
                                                                @endif
                                                            </div>
                                                            <div class="email-preview text-truncate">
                                                                <span
                                                                    class="fw-semibold">{{ $message->subject ?: '(No Subject)' }}</span>
                                                                -
                                                                {{ strip_tags(Str::limit($message->body_text ?: $message->body_html, 100)) }}
                                                            </div>
                                                        </div>
                                                        <div class="ms-3 text-end text-muted" style="min-width: 80px;">
                                                            <small>{{ $message->email_date ? $message->email_date->diffForHumans() : '' }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center p-5">
                                            <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No emails found</p>
                                        </div>
                                    @endforelse
                                </div>

                                <!-- Pagination -->
                                @if ($messages->hasPages())
                                    <div class="p-3 border-top">
                                        {{ $messages->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compose Modal -->
    <div class="modal fade compose-modal" id="composeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="composeModalTitle">New Message</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="composeForm">
                    <div class="modal-body">
                        <!-- Recipient Type Tabs -->
                        <ul class="nav nav-tabs recipient-type-tabs mb-3" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" type="button" data-recipient-type="tenant"
                                    onclick="switchRecipientType('tenant')">
                                    <i class="fa fa-users me-1"></i>Select Tenant
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" type="button" data-recipient-type="manual"
                                    onclick="switchRecipientType('manual')">
                                    <i class="fa fa-keyboard-o me-1"></i>Enter Email
                                </button>
                            </li>
                        </ul>

                        <!-- Tenant Selection -->
                        <div id="tenantSelection" class="mb-3">
                            <label class="form-label fw-bold">To: Select Tenant(s)</label>
                            <select class="form-select" id="tenantSelect" multiple="multiple" style="width: 100%;">
                            </select>
                            <small class="text-muted">Search and select tenants from the list</small>
                        </div>

                        <!-- Manual Email Entry -->
                        <div id="manualEmailEntry" class="mb-3" style="display: none;">
                            <label class="form-label fw-bold">To:</label>
                            <input type="text" class="form-control" id="toEmails" placeholder="Enter email addresses">
                            <small class="text-muted">Separate multiple emails with commas</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cc:</label>
                                <input type="text" class="form-control" id="ccEmails" placeholder="Cc emails (optional)">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bcc:</label>
                                <input type="text" class="form-control" id="bccEmails" placeholder="Bcc emails (optional)">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Subject:</label>
                            <input type="text" class="form-control" id="subject" placeholder="Email subject" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Message:</label>
                            <textarea class="form-control" id="emailBody" rows="10" placeholder="Compose your message..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Attachments:</label>
                            <input type="file" class="form-control" id="attachments" multiple>
                            <small class="text-muted">Max 25MB per file</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn">
                            <i class="fa fa-save me-2"></i>Save Draft
                        </button>
                        <button type="submit" class="btn btn-primary" id="sendEmailBtn">
                            <i class="fa fa-send me-2"></i>Send
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let currentRecipientType = 'tenant';

        $(document).ready(function() {
            // Initialize Select2 for tenant selection
            $('#tenantSelect').select2({
                theme: 'bootstrap-5',
                placeholder: 'Search for tenants...',
                allowClear: true,
                ajax: {
                    url: '{{ route('messaging.tenants.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
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
                minimumInputLength: 0,
                dropdownParent: $('#composeModal')
            });

            // Load initial tenant list
            $.ajax({
                url: '{{ route('messaging.tenants.search') }}',
                dataType: 'json',
                success: function(data) {
                    data.results.forEach(function(tenant) {
                        const option = new Option(tenant.text, tenant.email, false, false);
                        $('#tenantSelect').append(option);
                    });
                }
            });

            // Compose Modal
            $('#composeBtn').click(function() {
                resetComposeModal();
                $('#composeModal').modal('show');
            });

            // Send Email
            $('#composeForm').submit(function(e) {
                e.preventDefault();

                let toEmails = [];

                // Get emails based on recipient type
                if (currentRecipientType === 'tenant') {
                    const selectedTenants = $('#tenantSelect').val();
                    if (!selectedTenants || selectedTenants.length === 0) {
                        toastr.error('Please select at least one tenant');
                        return;
                    }
                    toEmails = selectedTenants;
                } else {
                    toEmails = $('#toEmails').val().split(',').map(e => e.trim()).filter(e => e);
                    if (toEmails.length === 0) {
                        toastr.error('Please enter at least one recipient');
                        return;
                    }
                }

                const ccEmails = $('#ccEmails').val() ? $('#ccEmails').val().split(',').map(e => e.trim())
                    .filter(e => e) : [];
                const bccEmails = $('#bccEmails').val() ? $('#bccEmails').val().split(',').map(e => e
                    .trim()).filter(e => e) : [];

                const formData = new FormData();

                // Append email arrays - FormData way
                toEmails.forEach((email, index) => {
                    formData.append(`to[${index}][email]`, email);
                });

                ccEmails.forEach((email, index) => {
                    formData.append(`cc[${index}][email]`, email);
                });

                bccEmails.forEach((email, index) => {
                    formData.append(`bcc[${index}][email]`, email);
                });

                formData.append('subject', $('#subject').val());
                formData.append('body', $('#emailBody').val());

                // Add attachments
                const files = $('#attachments')[0].files;
                for (let i = 0; i < files.length; i++) {
                    formData.append('attachments[]', files[i]);
                }

                $('#sendEmailBtn').prop('disabled', true).html(
                    '<i class="fa fa-spinner fa-spin me-2"></i>Sending...');

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
                            $('#composeModal').modal('hide');
                            resetComposeModal();
                            toastr.success('Email sent successfully');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            toastr.error(response.message || 'Failed to send email');
                        }
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'An error occurred';
                        toastr.error(errorMsg);
                        console.error('Send error:', xhr.responseJSON);
                    },
                    complete: function() {
                        $('#sendEmailBtn').prop('disabled', false).html(
                            '<i class="fa fa-send me-2"></i>Send');
                    }
                });
            });

            // Save Draft
            $('#saveDraftBtn').click(function() {
                let toEmails = [];
                if (currentRecipientType === 'tenant') {
                    const selectedTenants = $('#tenantSelect').val() || [];
                    toEmails = selectedTenants.map(email => ({ email }));
                } else {
                    toEmails = $('#toEmails').val() ? $('#toEmails').val().split(',').map(e => e.trim())
                        .filter(e => e).map(email => ({ email })) : [];
                }

                const ccEmails = $('#ccEmails').val() ? $('#ccEmails').val().split(',').map(e => e.trim())
                    .filter(e => e).map(email => ({ email })) : [];
                const bccEmails = $('#bccEmails').val() ? $('#bccEmails').val().split(',').map(e => e
                    .trim()).filter(e => e).map(email => ({ email })) : [];

                $.ajax({
                    url: '{{ route('messaging.draft.save') }}',
                    method: 'POST',
                    data: {
                        to: toEmails,
                        cc: ccEmails,
                        bcc: bccEmails,
                        subject: $('#subject').val(),
                        body: $('#emailBody').val(),
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success('Draft saved');
                    },
                    error: function() {
                        toastr.error('Failed to save draft');
                    }
                });
            });

            // Sync Emails
            $('#syncEmailsBtn, #refreshBtn').click(function() {
                const btn = $(this);
                const originalHtml = btn.html();
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                $.ajax({
                    url: '{{ route('messaging.sync') }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success('Emails synced successfully');
                        location.reload();
                    },
                    error: function() {
                        toastr.error('Failed to sync emails');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            // Select All
            $('#selectAll').change(function() {
                $('.email-select').prop('checked', $(this).is(':checked'));
                toggleBulkActions();
            });

            $('.email-select').change(function() {
                toggleBulkActions();
            });

            function toggleBulkActions() {
                const selected = $('.email-select:checked').length;
                if (selected > 0) {
                    $('#bulkActions').show();
                } else {
                    $('#bulkActions').hide();
                }
            }

            // Bulk Actions
            $('#markReadBtn').click(function() {
                bulkAction('read');
            });

            $('#markUnreadBtn').click(function() {
                bulkAction('unread');
            });

            $('#deleteBtn').click(function() {
                if (confirm('Are you sure you want to delete selected emails?')) {
                    bulkAction('delete');
                }
            });

            function bulkAction(action) {
                const ids = $('.email-select:checked').map(function() {
                    return $(this).val();
                }).get();

                $.ajax({
                    url: '{{ route('messaging.bulk.action') }}',
                    method: 'POST',
                    data: {
                        ids: ids,
                        action: action
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success(response.message);
                        location.reload();
                    },
                    error: function() {
                        toastr.error('Action failed');
                    }
                });
            }

            // Auto-refresh every 2 minutes
            setInterval(function() {
                $.ajax({
                    url: '{{ route('messaging.sync') }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function() {
                        console.log('Auto-sync completed');
                    }
                });
            }, 120000);
        });

        function switchRecipientType(type) {
            currentRecipientType = type;
            
            // Update tab states
            $('.recipient-type-tabs .nav-link').removeClass('active');
            $(`.recipient-type-tabs .nav-link[data-recipient-type="${type}"]`).addClass('active');
            
            if (type === 'tenant') {
                $('#tenantSelection').show();
                $('#manualEmailEntry').hide();
                $('#toEmails').removeAttr('required');
            } else {
                $('#tenantSelection').hide();
                $('#manualEmailEntry').show();
                $('#toEmails').attr('required', 'required');
            }
        }

        function resetComposeModal() {
            $('#composeForm')[0].reset();
            $('#tenantSelect').val(null).trigger('change');
            $('#composeModalTitle').text('New Message');
            switchRecipientType('tenant');
        }

        function viewEmail(id) {
            window.location.href = '{{ url('messaging/read') }}/' + id;
        }

        function toggleStar(id) {
            $.ajax({
                url: `/messaging/${id}/toggle-star`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    location.reload();
                }
            });
        }
    </script>
@endpush
