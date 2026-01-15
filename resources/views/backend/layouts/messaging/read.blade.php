@extends('backend.app')

@section('title', 'Read Message')

@push('styles')
    <style>
        .email-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
        }

        .sender-info {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .sender-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #1a73e8;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 600;
            margin-right: 15px;
        }

        .attachment-item {
            display: inline-block;
            padding: 10px 15px;
            margin: 5px;
            background: #f1f3f4;
            border-radius: 4px;
            border: 1px solid #dadce0;
        }

        .attachment-item:hover {
            background: #e8eaed;
        }

        .email-body-content {
            padding: 20px 0;
            line-height: 1.6;
        }

        .action-buttons {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
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
                        <h1 class="page-title">{{ $message->subject ?: '(No Subject)' }}</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <a href="{{ route('messaging.index') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left me-2"></i>Back to Inbox
                        </a>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- Row -->
                <div class="row">
                    <!-- Sidebar -->
                    <div class="col-lg-4 col-xl-3 col-md-12 col-sm-12">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <button class="btn btn-primary btn-block py-2 w-100"
                                    onclick="$('#composeModal').modal('show')">
                                    <i class="fa fa-plus me-2"></i>Compose
                                </button>
                            </div>
                            <div class="card-body">
                                <ul class="nav1 nav-column flex-column br-7">
                                    <li class="nav-item1 mt-0">
                                        <a class="nav-link thumb"
                                            href="{{ route('messaging.index', ['folder' => 'inbox']) }}">
                                            <i class="fa fa-inbox me-2"></i>Inbox
                                            @if ($counts['unread'] > 0)
                                                <span class="badge-count float-end">{{ $counts['unread'] }}</span>
                                            @endif
                                        </a>
                                    </li>
                                    <li class="nav-item1">
                                        <a class="nav-link thumb"
                                            href="{{ route('messaging.index', ['folder' => 'starred']) }}">
                                            <i class="fa fa-star me-2"></i>Starred
                                            @if ($counts['starred'] > 0)
                                                <span class="badge-count float-end">{{ $counts['starred'] }}</span>
                                            @endif
                                        </a>
                                    </li>
                                    <li class="nav-item1">
                                        <a class="nav-link thumb"
                                            href="{{ route('messaging.index', ['folder' => 'sent']) }}">
                                            <i class="fa fa-send me-2"></i>Sent
                                        </a>
                                    </li>
                                    <li class="nav-item1">
                                        <a class="nav-link thumb"
                                            href="{{ route('messaging.index', ['folder' => 'drafts']) }}">
                                            <i class="fa fa-file-text me-2"></i>Drafts
                                        </a>
                                    </li>
                                    <li class="nav-item1">
                                        <a class="nav-link thumb"
                                            href="{{ route('messaging.index', ['folder' => 'trash']) }}">
                                            <i class="fa fa-trash me-2"></i>Trash
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

                    <!-- Email Content -->
                    <div class="col-lg-8 col-xl-9 col-md-12 col-sm-12">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h4 class="card-title fw-bold mb-0">{{ $message->subject ?: '(No Subject)' }}</h4>
                            </div>
                            <div class="card-body">
                                <!-- Action Toolbar -->
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="btn-group me-2">
                                        <button class="btn btn-sm btn-white" onclick="toggleStar({{ $message->id }})"
                                            title="Star">
                                            <i class="fa fa-star {{ $message->is_starred ? 'text-warning' : '' }}"></i>
                                        </button>
                                        <button class="btn btn-sm btn-white" onclick="toggleRead({{ $message->id }})"
                                            title="Mark as unread">
                                            <i class="fa fa-envelope"></i>
                                        </button>
                                        <button class="btn btn-sm btn-white" onclick="deleteEmail({{ $message->id }})"
                                            title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-white"
                                            onclick="moveToFolder({{ $message->id }}, 'spam')" title="Report spam">
                                            <i class="fa fa-ban"></i> Spam
                                        </button>
                                    </div>
                                </div>

                                <!-- Sender Info -->
                                <div class="sender-info">
                                    <div class="sender-avatar">
                                        {{ strtoupper(substr($message->from_name ?: $message->from_email, 0, 1)) }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">{{ $message->from_name ?: $message->from_email }}</div>
                                        <div class="text-muted small">
                                            <span>{{ $message->from_email }}</span>
                                        </div>
                                        <div class="text-muted small">
                                            To:
                                            @foreach ($message->to as $recipient)
                                                {{ $recipient['email'] }}{{ !$loop->last ? ', ' : '' }}
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="text-end text-muted small">
                                        {{ $message->email_date ? $message->email_date->format('M d, Y h:i A') : '' }}
                                    </div>
                                </div>

                                <!-- Email Body -->
                                <div class="email-body-content">
                                    @if ($message->body_html)
                                        {!! $message->body_html !!}
                                    @else
                                        <pre style="white-space: pre-wrap; font-family: inherit;">{{ $message->body_text }}</pre>
                                    @endif
                                </div>

                                <!-- Attachments -->
                                @if ($message->has_attachments && $message->attachmentFiles->count() > 0)
                                    <div class="mt-4 pt-3 border-top">
                                        <h6 class="fw-bold mb-3">
                                            <i class="fa fa-paperclip me-2"></i>
                                            {{ $message->attachmentFiles->count() }} Attachment(s)
                                        </h6>
                                        <div class="attachments-list">
                                            @foreach ($message->attachmentFiles as $attachment)
                                                <a href="{{ route('messaging.attachment.download', $attachment->id) }}"
                                                    class="attachment-item text-decoration-none">
                                                    <i class="fa fa-file me-2"></i>
                                                    {{ $attachment->filename }}
                                                    <span
                                                        class="text-muted small">({{ $attachment->formatted_size }})</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Action Buttons -->
                                <div class="action-buttons">
                                    <button class="btn btn-primary me-2" onclick="replyEmail()">
                                        <i class="fa fa-reply me-2"></i>Reply
                                    </button>
                                    <button class="btn btn-primary me-2" onclick="replyAllEmail()">
                                        <i class="fa fa-reply-all me-2"></i>Reply All
                                    </button>
                                    <button class="btn btn-info" onclick="forwardEmail()">
                                        <i class="fa fa-share me-2"></i>Forward
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compose Modal (for Reply/Forward) -->
    <div class="modal fade compose-modal" id="composeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="composeModalTitle">New Message</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="composeForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <input type="text" class="form-control" id="toEmails" placeholder="To" required>
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" id="ccEmails" placeholder="Cc">
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" id="subject" placeholder="Subject" required>
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" id="emailBody" rows="12" placeholder="Compose your message..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <input type="file" class="form-control" id="attachments" multiple>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
    <script>
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

        function toggleRead(id) {
            $.ajax({
                url: `/messaging/${id}/toggle-read`,
                method: 'POST',
                data: {
                    is_read: false
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    toastr.success('Marked as unread');
                    setTimeout(() => window.location.href = '{{ route('messaging.index') }}', 1000);
                }
            });
        }

        function deleteEmail(id) {
            if (confirm('Are you sure you want to delete this email?')) {
                $.ajax({
                    url: `/messaging/${id}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success('Email deleted');
                        window.location.href = '{{ route('messaging.index') }}';
                    }
                });
            }
        }

        function moveToFolder(id, folder) {
            $.ajax({
                url: `/messaging/${id}/move`,
                method: 'POST',
                data: {
                    folder: folder
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    toastr.success('Email moved to ' + folder);
                    window.location.href = '{{ route('messaging.index') }}';
                }
            });
        }

        function replyEmail() {
            $('#composeModalTitle').text('Reply');
            $('#toEmails').val('{{ $message->from_email }}');
            $('#subject').val('Re: {{ $message->subject }}');

            const originalMessage = `

------- Original Message -------
From: {{ $message->from_name }} <{{ $message->from_email }}>
Date: {{ $message->email_date ? $message->email_date->format('M d, Y h:i A') : '' }}
Subject: {{ $message->subject }}

{{ strip_tags($message->body_text ?: $message->body_html) }}`;

            $('#emailBody').val(originalMessage);
            $('#composeModal').modal('show');
        }

        function replyAllEmail() {
            $('#composeModalTitle').text('Reply All');

            const allRecipients = [
                '{{ $message->from_email }}',
                @foreach ($message->to as $recipient)
                    '{{ $recipient['email'] }}',
                @endforeach
            ].join(', ');

            $('#toEmails').val(allRecipients);
            $('#subject').val('Re: {{ $message->subject }}');

            const originalMessage = `

------- Original Message -------
From: {{ $message->from_name }} <{{ $message->from_email }}>
Date: {{ $message->email_date ? $message->email_date->format('M d, Y h:i A') : '' }}
Subject: {{ $message->subject }}

{{ strip_tags($message->body_text ?: $message->body_html) }}`;

            $('#emailBody').val(originalMessage);
            $('#composeModal').modal('show');
        }

        function forwardEmail() {
            $('#composeModalTitle').text('Forward');
            $('#toEmails').val('');
            $('#subject').val('Fwd: {{ $message->subject }}');

            const originalMessage = `

------- Forwarded Message -------
From: {{ $message->from_name }} <{{ $message->from_email }}>
Date: {{ $message->email_date ? $message->email_date->format('M d, Y h:i A') : '' }}
Subject: {{ $message->subject }}

{{ strip_tags($message->body_text ?: $message->body_html) }}`;

            $('#emailBody').val(originalMessage);
            $('#composeModal').modal('show');
        }

        // Send Email
        $('#composeForm').submit(function(e) {
            e.preventDefault();

            const toEmails = $('#toEmails').val().split(',').map(e => ({
                email: e.trim()
            }));
            const ccEmails = $('#ccEmails').val() ? $('#ccEmails').val().split(',').map(e => ({
                email: e.trim()
            })) : [];

            const formData = new FormData();
            formData.append('to', JSON.stringify(toEmails));
            formData.append('cc', JSON.stringify(ccEmails));
            formData.append('subject', $('#subject').val());
            formData.append('body', $('#emailBody').val());

            const files = $('#attachments')[0].files;
            for (let i = 0; i < files.length; i++) {
                formData.append('attachments[]', files[i]);
            }

            $('#sendEmailBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i>Sending...');

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
                        toastr.success('Email sent successfully');
                        setTimeout(() => window.location.href = '{{ route('messaging.index') }}',
                            1000);
                    } else {
                        toastr.error(response.message || 'Failed to send email');
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'An error occurred');
                },
                complete: function() {
                    $('#sendEmailBtn').prop('disabled', false).html(
                        '<i class="fa fa-send me-2"></i>Send');
                }
            });
        });
    </script>
@endpush
