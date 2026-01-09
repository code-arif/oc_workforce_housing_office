@extends('emails.layout.mail')

@section('content')
    <div class="message-content">
        <p>Dear {{ $tenant->name ?? 'Applicant' }},</p>

        <p>Thank you for submitting your email to <strong>{{ $companyName }}</strong>. We have successfully received
            your email.</p>

        <div class="highlight-box">
            <h3 style="color: #D9A600; margin-bottom: 15px;">Application Details:</h3>
            <p><strong>Application ID:</strong> {{ $tenant->id }}</p>
            <p><strong>Email:</strong> {{ $tenant->email }}</p>
            <p><strong>Status:</strong> <span style="color: #D9A600; font-weight: 600;">Pending Review</span></p>
            <p><strong>Submitted:</strong> {{ $tenant->created_at->format('F d, Y') }}</p>
        </div>

        <p>You will get an application form as a tenant within <strong>1-2 business
                days</strong>.</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $supportUrl }}" class="action-button"
                style="display: inline-block; background-color: #D9A600; color: #000000; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-weight: 600; font-size: 16px; margin: 10px; border: none; cursor: pointer; text-align: center;">
                Visit Contact Page →
            </a>
        </div>

        <p>If you have any questions or need to provide additional information, please don't hesitate to contact our support
            team.</p>
    </div>
@endsection

@section('disclaimer')
    This email was sent to {{ $tenant->email }} regarding your application with {{ $companyName }}.
    <br>
    Please do not reply to this automated email. For inquiries, contact <a href="mailto:{{ $companyEmail }}"
        style="color: #D9A600 !important; text-decoration: none;">{{ $companyEmail }}</a> or call {{ $companyPhone }}.
    <br><br>
    © {{ date('Y') }} {{ $companyName }}. All rights reserved.
@endsection
