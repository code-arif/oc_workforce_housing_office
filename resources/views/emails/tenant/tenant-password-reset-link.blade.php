@extends('emails.layout.mail')

@section('content')
    <div class="message-content">
        <p>Dear {{ $tenant->first_name ?? 'Applicant' }},</p>

        <p>We are pleased to inform you that your application has been approved. Please set up a new password for your tenant account by clicking the button below.</p>

        <!-- Account information presented in a table (professional & scannable) -->
        <div class="highlight-box" style="margin: 25px 0; background-color: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 4px; overflow: hidden;">
            <h3 style="color: #ba9779; margin: 0; padding: 15px 20px 10px 20px; font-size: 18px;">Account Setup Details</h3>
            <table style="width: 100%; border-collapse: collapse; background-color: #ffffff;">
                @if(isset($tenant->email))
                    <tr>
                        <td style="padding: 12px 20px; border-bottom: 1px solid #e0e0e0; font-weight: 600; width: 35%;">Email Address</td>
                        <td style="padding: 12px 20px; border-bottom: 1px solid #e0e0e0;">{{ $tenant->email }}</td>
                    </tr>
                @endif
                @if(isset($tenant->profile->first_name) || isset($tenant->profile->last_name))
                    <tr>
                        <td style="padding: 12px 20px; border-bottom: 1px solid #e0e0e0; font-weight: 600;">Full Name</td>
                        <td style="padding: 12px 20px; border-bottom: 1px solid #e0e0e0;">
                            {{ $tenant->profile->first_name ?? '' }} {{ $tenant->profile->last_name ?? '' }}
                        </td>
                    </tr>
                @endif
                @if(isset($companyName))
                    <tr>
                        <td style="padding: 12px 20px; font-weight: 600;">Property Manager</td>
                        <td style="padding: 12px 20px;">{{ $companyName }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <!-- Smaller button without arrow emoji -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $passResetUrl }}" class="action-button"
                style="display: inline-block; background-color: #ba9779; color: #000000; text-decoration: none; padding: 10px 24px; border-radius: 6px; font-weight: 600; font-size: 14px; margin: 10px; border: none; cursor: pointer; text-align: center;">
                Proceed to Login
            </a>
        </div>

        <p style="margin-top: 25px;">If you did not request this email or need assistance, please contact our support team.</p>
    </div>
@endsection

@section('disclaimer')
    © {{ date('Y') }} {{ $companyName }}. All rights reserved.
@endsection
