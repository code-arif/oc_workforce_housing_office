@extends('emails.layout.mail')

@section('content')
    <div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;">
        <!-- Main Content -->
        <div style="padding: 30px; background-color: #ffffff;">
            <p style="color: #333; font-size: 16px; line-height: 1.6;">Dear {{ $tenant->first_name ?? 'Tenant' }} {{ $tenant->last_name ?? '' }},</p>

            <p style="color: #333; font-size: 16px; line-height: 1.6;">We received a request to reset the password for your account at <strong>{{ $companyName }}</strong>.</p>

            <p style="color: #333; font-size: 16px; line-height: 1.6;">Please use the following One-Time Password (OTP) to reset your password. This code will expire in <strong>{{ $otpExpiresIn }} minutes</strong>:</p>

            <!-- OTP Display -->
            <div style="text-align: center; margin: 30px 0;">
                <div style="background-color: #f8f9fa; border: 2px dashed #ba9779; border-radius: 8px; padding: 20px; display: inline-block;">
                    <span style="font-size: 36px; font-weight: 800; letter-spacing: 8px; color: #333; font-family: monospace;">{{ $otp }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection
