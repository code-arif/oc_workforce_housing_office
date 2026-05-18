@extends('emails.layout.mail')

@section('content')
    <div class="message-content">
        <!-- Welcome Message -->
        <div class="greeting" style="margin-bottom: 20px;">
            <h2 style="color: #ba9779; margin-bottom: 5px;">Welcome to {{ $companyName }}</h2>
        </div>

        <p>Dear Applicant,</p>

        <p>Thank you for your interest in renting with us. To begin the process, please complete the rental application form by following the steps below.</p>

        <!-- Step-by-Step Instructions in Table Format -->
        <div class="highlight-box" style="margin: 25px 0; background-color: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 4px; overflow: hidden;">
            <h3 style="color: #ba9779; margin: 0; padding: 15px 20px 10px 20px; font-size: 18px;">How to Complete Your Rental Application</h3>
            <table style="width: 100%; border-collapse: collapse; background-color: #ffffff;">
                <tr>
                    <td style="padding: 14px 20px; border-bottom: 1px solid #e0e0e0; vertical-align: top; width: 10%; font-weight: 700; color: #ba9779;">Step 1</td>
                    <td style="padding: 14px 20px; border-bottom: 1px solid #e0e0e0;">Click the button below to access the rental application form.</td>
                </tr>
                <tr>
                    <td style="padding: 14px 20px; border-bottom: 1px solid #e0e0e0; vertical-align: top; font-weight: 700; color: #ba9779;">Step 2</td>
                    <td style="padding: 14px 20px; border-bottom: 1px solid #e0e0e0;">Fill out every section of the form carefully and accurately. Incomplete or incorrect applications may delay processing.</td>
                </tr>
                <tr>
                    <td style="padding: 14px 20px; font-weight: 700; color: #ba9779;">Step 3</td>
                    <td style="padding: 14px 20px;">Submit the completed application. You will receive a confirmation email once we have received your submission.</td>
                </tr>
            </table>
        </div>

        <!-- Smaller Button (no arrow, no emoji) -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $formUrl }}" class="action-button"
                style="display: inline-block; background-color: #ba9779; color: #000000; text-decoration: none; padding: 10px 24px; border-radius: 6px; font-weight: 600; font-size: 14px; margin: 10px; border: none; cursor: pointer; text-align: center;">
                Start Rental Application
            </a>
        </div>

        <!-- Confirmation Note -->
        <div style="background-color: #e2f0d9; border-left: 4px solid #5cb85c; padding: 16px 20px; margin: 20px 0; border-radius: 0 4px 4px 0;">
            <p style="color: #3c763d; margin: 0;"><strong>What happens after you submit?</strong><br>
            We will review your application and send you a confirmation email. If approved, you will receive further instructions to complete your tenancy registration.</p>
        </div>

        <p style="margin-top: 20px;">If you have any questions or need assistance, please do not hesitate to contact our leasing team.</p>
    </div>
@endsection

@section('disclaimer')
    This email was sent to {{ $email }} regarding your request to complete a rental application for {{ $companyName }}.
    <br>
    Please do not reply directly to this automated notification. For any inquiries, please contact <a href="mailto:{{ $companyEmail ?? config('mail.admin_email') }}" style="color: #ba9779 !important; text-decoration: none;">{{ $companyEmail ?? config('mail.admin_email') }}</a> or call {{ $companyPhone ?? config('app.phone', '(443) 336-5182') }}.
    <br><br>
    © {{ date('Y') }} {{ $companyName }}. All rights reserved.
@endsection
