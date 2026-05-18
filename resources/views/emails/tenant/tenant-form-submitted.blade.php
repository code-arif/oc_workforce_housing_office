@extends('emails.layout.mail')

@section('content')
    <div class="greeting" style="font-size: 20px; font-weight: bold; color: #ba9779; margin-bottom: 20px;">
        New Tenant Application Received
    </div>

    <div class="message-content">
        <p>Dear Admin,</p>

        <p>A new tenant application has been submitted and is ready for your review. Below is a detailed summary of the applicant's submission:</p>

        <!-- Application Summary Table -->
        <div class="highlight-box" style="margin-top: 25px;">
            <h3 style="color: #ba9779; margin-bottom: 15px; font-size: 16px; border-bottom: 2px solid #ba9779; padding-bottom: 6px;">Application Details</h3>
            <table style="width: 100%; border-collapse: collapse; background-color: #ffffff; border: 1px solid #e0e0e0; font-size: 14px;">
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; width: 35%; background-color: #f9f9f9;">Application Number</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: bold;">#{{ $application->application_number }}</td>
                </tr>
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; background-color: #f9f9f9;">Applicant Name</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ $application->full_name }}</td>
                </tr>
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; background-color: #f9f9f9;">Email Address</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;"><a href="mailto:{{ $application->email }}" style="color: #ba9779; text-decoration: none;">{{ $application->email }}</a></td>
                </tr>
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; background-color: #f9f9f9;">Phone Number</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ $application->phone ?? 'N/A' }}</td>
                </tr>
                @if($application->arrival_date)
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; background-color: #f9f9f9;">Arrival Date</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ \Carbon\Carbon::parse($application->arrival_date)->format('F d, Y') }}</td>
                </tr>
                @endif
                @if($application->departure_date)
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; background-color: #f9f9f9;">Departure Date</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ \Carbon\Carbon::parse($application->departure_date)->format('F d, Y') }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; background-color: #f9f9f9;">Submission Date</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ $currentDate }}</td>
                </tr>
                <tr>
                    <td style="padding: 12px; font-weight: 600; background-color: #f9f9f9;">Current Status</td>
                    <td style="padding: 12px;"><span style="background-color: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px;">{{ strtoupper($tenantStatus) }}</span></td>
                </tr>
            </table>
        </div>

        @if($application->notes)
        <!-- Notes Box -->
        <div class="highlight-box" style="margin-top: 25px; background-color: #f9f9f9; border-left: 4px solid #ba9779; padding: 15px;">
            <h4 style="margin: 0 0 8px 0; color: #333333; font-size: 14px;">Applicant Notes:</h4>
            <p style="margin: 0; font-size: 13.5px; color: #555555; line-height: 1.5; font-style: italic;">"{{ $application->notes }}"</p>
        </div>
        @endif

        <p style="margin-top: 25px;">Please log in to the admin panel to view the complete details, including uploaded identity documents, employer info, and sponsor validation.</p>

        <!-- CTA button -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $viewUrl }}/admin/applications" class="action-button"
                style="display: inline-block; background-color: #000000; color: #ba9779; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; font-size: 14px; border: 1px solid #ba9779; cursor: pointer; text-align: center;">
                View on Admin Dashboard
            </a>
        </div>
    </div>
@endsection

@section('disclaimer')
    This is an automated administrative notification sent to the property management team of {{ $companyName }}.
    <br>
    Contains confidential tenant data. If you received this in error, please report to <a href="mailto:{{ $companyEmail }}" style="color: #ba9779 !important; text-decoration: none;">{{ $companyEmail }}</a> immediately.
    <br><br>
    © {{ date('Y') }} {{ $companyName }}. All rights reserved.
@endsection
