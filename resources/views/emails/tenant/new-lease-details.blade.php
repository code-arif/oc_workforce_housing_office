@extends('emails.layout.mail')

@section('content')
    <div class="greeting">
        @if ($sendWelcome)
            Welcome to {{ $companyName }}!
        @elseif($sendSignature)
            Lease Agreement Ready for Signature
        @else
            Your Account Details
        @endif
    </div>

    <div class="message-content">
        <p>Dear {{ $tenant->profile->first_name ?? 'Tenant' }} {{ $tenant->profile->last_name ?? '' }},</p>

        @if ($sendWelcome)
            <p>Congratulations! We are delighted to inform you that your application was approved. Welcome to the
                <strong>{{ $companyName }}</strong> community!
            </p>
        @endif

        @if ($sendSignature)
            <p>Your lease agreement is ready and requires your signature. Please review the document carefully and sign it
                electronically to complete your tenancy registration.</p>
        @endif

        <!-- Lease & Property Details in Tabular Format -->
        <div class="highlight-box" style="margin-top: 25px;">
            <h3 style="color: #ba9779; margin-bottom: 15px;">Lease & Property Details</h3>
            <table style="width: 100%; border-collapse: collapse; background-color: #ffffff; border: 1px solid #e0e0e0;">
                @if (isset($property))
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; width: 35%;">Property
                        </td>
                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">
                            {{ $property->name ?? 'N/A' }}
                            @if (isset($bed))
                                (Bed: {{ $bed->bed_label ?? 'N/A' }})
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600;">Address</td>
                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">
                            {{ $property->address ?? 'N/A' }}
                            @if (isset($property->city))
                                , {{ $property->city }}
                            @endif
                            @if (isset($property->state))
                                , {{ $property->state }}
                            @endif
                            {{ $property->zip_code ?? '' }}
                        </td>
                    </tr>
                @endif
                @if (isset($lease))
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600;">Lease Start Date</td>
                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">
                            {{ \Carbon\Carbon::parse($lease->start_date)->format('F d, Y') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600;">Lease End Date</td>
                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">
                            {{ \Carbon\Carbon::parse($lease->end_date)->format('F d, Y') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600;">Rent Amount</td>
                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">
                            ${{ number_format($lease->rent_amount, 2) }} /
                            {{ ucwords(strtolower($lease->payment_frequency)) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; font-weight: 600;">Security Deposit</td>
                        <td style="padding: 12px;">${{ number_format($lease->deposit_amount, 2) }}</td>
                    </tr>
                @endif
            </table>
        </div>

        @if ($passResetUrl)
            <!-- Account setup required box (no emojis) -->
            <div
                style="background-color: #e2f0d9; border-left: 4px solid #5cb85c; padding: 20px; margin: 25px 0; border-radius: 0 4px 4px 0;">
                <h3 style="color: #3c763d; margin-bottom: 10px;">Action Required: Account Setup</h3>
                <p style="color: #3c763d; margin: 0;">Please click the button below to set up a new password for your tenant
                    account.</p>

                <small style="color: #763c3c; margin-top: 5px; display: block;" class="text-danger">If you already set up
                    password please ignore this part.</small>
            </div>

            <!-- Smaller Setup Password button -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $passResetUrl }}" class="action-button"
                    style="display: inline-block; background-color: #000000; color: #ba9779; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 14px; margin: 10px; border: 1px solid #ba9779; cursor: pointer; text-align: center;">
                    Setup Password
                </a>
            </div>
        @endif

        @if ($sendSignature)
            <!-- Signature action required box (no emojis) -->
            <div
                style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 25px 0; border-radius: 0 4px 4px 0;">
                <h3 style="color: #856404; margin-bottom: 10px;">Action Required: Signature</h3>
                <p style="color: #856404; margin: 0;">This signature request will expire in <strong>{{ $expirationDays }}
                        days</strong>. Please sign the document before it expires to avoid delays in your move-in process.
                </p>
            </div>

            <!-- Smaller Sign button -->
            <div style="text-align: center; margin: 35px 0;">
                <a href="{{ $signatureUrl }}" class="action-button"
                    style="display: inline-block; background-color: #ba9779; color: #000000; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 700; font-size: 15px; border: none; cursor: pointer; text-align: center; box-shadow: 0 2px 8px rgba(217, 166, 0, 0.3);">
                    Sign Lease Agreement
                </a>
            </div>
        @endif

        @if ($sendWelcome)
            <!-- What Happens Next? in tabular format -->
            <div class="highlight-box" style="border-left-color: #000000; margin-top: 25px;">
                <h3 style="color: #000000; margin-bottom: 15px;">What Happens Next?</h3>
                <p>As a registered tenant, you now have access to:</p>
                <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
                    <tr>
                        <td style="padding: 8px 0; vertical-align: top; width: 40%; font-weight: 600;">Online Payment Portal
                        </td>
                        <td style="padding: 8px 0; vertical-align: top;">Pay rent and utilities online with ease</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; vertical-align: top; font-weight: 600;">Maintenance Request System</td>
                        <td style="padding: 8px 0; vertical-align: top;">Submit and track maintenance requests</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; vertical-align: top; font-weight: 600;">Document Access</td>
                        <td style="padding: 8px 0; vertical-align: top;">View lease agreement and important documents</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; vertical-align: top; font-weight: 600;">Direct Communication</td>
                        <td style="padding: 8px 0; vertical-align: top;">Message property management directly</td>
                    </tr>
                </table>
                <!-- Smaller Tenant Portal button -->
                <div style="text-align: center; margin-top: 20px;">
                    <a href="{{ $tenantPortalUrl }}" class="action-button"
                        style="display: inline-block; background-color: #f8f9fa; color: #333333; text-decoration: none; padding: 8px 16px; border-radius: 4px; font-weight: 600; font-size: 13px; border: 1px solid #ddd; cursor: pointer;">
                        Access Tenant Portal
                    </a>
                </div>
            </div>
        @endif

        <p style="margin-top: 25px;">If you have any questions or need assistance, our team is here to help. We look forward
            to providing you with excellent service throughout your tenancy.</p>

        @if ($sendSignature)
            <!-- Plain text fallback for signature link -->
            <div style="background-color: #f8f9fa; padding: 15px; border-radius: 4px; margin-top: 20px;">
                <p style="margin: 0; font-size: 14px; color: #666;">
                    <strong>Having trouble with the signature link?</strong> Copy and paste this link into your browser:<br>
                    <a href="{{ $signatureUrl }}" style="color: #ba9779; word-break: break-all;">{{ $signatureUrl }}</a>
                </p>
            </div>
        @endif
        @if ($passResetUrl)
            <!-- Plain text fallback for password reset link -->
            <div style="background-color: #f8f9fa; padding: 15px; border-radius: 4px; margin-top: 10px;">
                <p style="margin: 0; font-size: 14px; color: #666;">
                    <strong>Having trouble with the password setup link?</strong> Copy and paste this link into your
                    browser:<br>
                    <a href="{{ $passResetUrl }}" style="color: #ba9779; word-break: break-all;">{{ $passResetUrl }}</a>
                </p>
            </div>
        @endif
    </div>
@endsection

@section('disclaimer')
    This email was sent to {{ $tenant->email }} regarding your tenant registration with {{ $companyName }}.
    <br>
    Please do not reply to this automated email. For inquiries, contact <a href="mailto:{{ $companyEmail }}"
        style="color: #ba9779 !important; text-decoration: none;">{{ $companyEmail }}</a> or call {{ $companyPhone }}.
    <br><br>
    © {{ date('Y') }} {{ $companyName }}. All rights reserved.
@endsection
