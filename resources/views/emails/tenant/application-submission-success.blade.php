@extends('emails.layout.mail')

@section('content')
    <div class="greeting" style="font-size: 20px; font-weight: bold; color: #ba9779; margin-bottom: 20px;">
        Application Received & Under Review
    </div>

    <div class="message-content">
        <p>Dear {{ $application->first_name }} {{ $application->last_name ?? '' }},</p>

        <p>Thank you for submitting your rental application to <strong>{{ $companyName }}</strong>. We have successfully received your form, and our leasing team is now conducting a thorough review of your details and uploaded documentation.</p>

        <!-- Application Details Table -->
        <div class="highlight-box" style="margin-top: 25px;">
            <h3 style="color: #ba9779; margin-bottom: 15px; font-size: 16px; border-bottom: 2px solid #ba9779; padding-bottom: 6px;">Application Receipt</h3>
            <table style="width: 100%; border-collapse: collapse; background-color: #ffffff; border: 1px solid #e0e0e0; font-size: 14px;">
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; width: 35%; background-color: #f9f9f9;">Application Number</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: bold;">#{{ $applicationNumber }}</td>
                </tr>
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; background-color: #f9f9f9;">Applicant Name</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ $application->first_name }} {{ $application->last_name ?? '' }}</td>
                </tr>
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; background-color: #f9f9f9;">Email Address</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;"><a href="mailto:{{ $tenantEmail }}" style="color: #ba9779; text-decoration: none;">{{ $tenantEmail }}</a></td>
                </tr>
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; background-color: #f9f9f9;">Submission Date</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ $application->created_at->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 12px; font-weight: 600; background-color: #f9f9f9;">Application Status</td>
                    <td style="padding: 12px;"><span style="background-color: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px;">PENDING REVIEW</span></td>
                </tr>
            </table>
        </div>

        <!-- Next Steps Timeline -->
        <div class="highlight-box" style="border-left: 4px solid #000000; background-color: #fdfdfd; padding: 20px; margin: 30px 0; border-radius: 0 4px 4px 0;">
            <h3 style="color: #000000; margin: 0 0 12px 0; font-size: 16px;">What to Expect Next</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 13.5px; line-height: 1.5; color: #555555;">
                <tr>
                    <td style="padding: 8px 0; vertical-align: top; width: 6%; font-weight: bold; color: #ba9779;">1.</td>
                    <td style="padding: 8px 0; vertical-align: top;"><strong>Detailed Assessment:</strong> Our team reviews your application against our criteria. This typically takes <strong>1-2 business days</strong>.</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; vertical-align: top; font-weight: bold; color: #ba9779;">2.</td>
                    <td style="padding: 8px 0; vertical-align: top;"><strong>Document Verification:</strong> We will inspect and verify your uploaded IDs, employer info, and passport documents.</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; vertical-align: top; font-weight: bold; color: #ba9779;">3.</td>
                    <td style="padding: 8px 0; vertical-align: top;"><strong>Lease Offer:</strong> Once formally approved, we will email you your formal Lease Agreement to sign electronically.</td>
                </tr>
            </table>
        </div>

        <p style="margin-top: 25px;">If you have any questions or need to make corrections to your submitted application details, please contact our support team immediately.</p>

        <!-- CTA button -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $supportUrl }}" class="action-button"
                style="display: inline-block; background-color: #000000; color: #ba9779; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; font-size: 14px; border: 1px solid #ba9779; cursor: pointer; text-align: center;">
                Contact Support Team
            </a>
        </div>
    </div>
@endsection

@section('disclaimer')
    This email was sent to {{ $tenantEmail }} regarding your rental application with {{ $companyName }}.
    <br>
    Please do not reply to this automated email. For inquiries, contact <a href="mailto:{{ $companyEmail }}" style="color: #ba9779 !important; text-decoration: none;">{{ $companyEmail }}</a> or call {{ $companyPhone }}.
    <br><br>
    © {{ $currentYear }} {{ $companyName }}. All rights reserved.
@endsection
