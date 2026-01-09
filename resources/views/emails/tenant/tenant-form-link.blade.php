@extends('emails.layout.mail')

@section('content')
    <div class="message-content">
        <p>Dear {{ $tenant->first_name ?? 'Applicant' }},</p>

        <p>We are approve you email and please click the View Application button below for application</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $formUrl }}" class="action-button"
                style="display: inline-block; background-color: #D9A600; color: #000000; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-weight: 600; font-size: 16px; margin: 10px; border: none; cursor: pointer; text-align: center;">
                View Application →
            </a>
        </div>
    </div>
@endsection

@section('disclaimer')
    © {{ date('Y') }} {{ $companyName }}. All rights reserved.
@endsection
