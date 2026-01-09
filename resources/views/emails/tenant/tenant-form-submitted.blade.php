@extends('emails.layout.mail')

@section('content')
    <div class="message-content">
        <p><strong>Attention Admin,</strong></p>

        <p>A new tenant application has been submitted and requires your review.</p>

        <div class="highlight-box">
            <h3 style="color: #D9A600; margin-bottom: 20px;">Application Summary</h3>

            <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Application ID:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{{ $tenant->id }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Applicant Email:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{{ $tenant->email }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Application Source:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{{ ucfirst($tenant->application_source) }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Submission Date:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;">
                        {{ $tenant->created_at->format('F d, Y \a\t h:i A') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Current Status:</strong></td>
                    <td style="padding: 8px 0;"><span
                            style="color: #D9A600; font-weight: 600;">{{ ucfirst($tenant->status) }}</span></td>
                </tr>
            </table>
        </div>

        <p style="margin: 25px 0;">Please review this application and take appropriate action.</p>

        <div style="text-align: center; margin: 40px 0;">
            <table width="100%" cellpadding="0" cellspacing="0" style="text-align: center;">
                <tr>
                    <td align="center">
                        <a href="{{ $viewUrl }}" class="action-button"
                            style="display: inline-block; background-color: #000000; color: #ffffff !important; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-weight: 600; font-size: 16px; margin: 10px; border: none; cursor: pointer; text-align: center;">
                            View Dashboard
                        </a>
                    </td>
                </tr>
            </table>
        </div>

        <div style="background-color: #f8f8f8; padding: 20px; border-radius: 6px; margin: 25px 0;">
            <p style="margin: 0; font-size: 14px;">
                <strong>Note:</strong> This is an automated notification for a new application submission.
                The approval link above is valid for 7 days. Application was submitted on {{ $currentDate }}.
            </p>
        </div>
    </div>
@endsection

@section('disclaimer')
    This is an automated notification sent to administrators of {{ $companyName }}.
    <br>
    This email contains confidential information. If you received this in error, please delete it immediately.
    <br><br>
    © {{ date('Y') }} {{ $companyName }}. All rights reserved.
@endsection
