@extends('emails.layout.mail')

@section('content')
    <div class="message-content">
        <p>Dear {{ $tenant->first_name ?? 'Applicant' }},</p>

        <p>Please click to fill out a rental application.</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $formUrl }}" class="action-button"
                style="display: inline-block; background-color: #ba9779; color: #000000; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-weight: 600; font-size: 16px; margin: 10px; border: none; cursor: pointer; text-align: center;">
                Rental Application→
            </a>
        </div>
    </div>
@endsection

@section('disclaimer')
    © {{ date('Y') }} {{ $companyName }}. All rights reserved.
@endsection
