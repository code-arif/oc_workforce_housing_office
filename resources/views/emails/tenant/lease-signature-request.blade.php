@extends('emails.layout.mail')

@section('content')
    <div class="greeting">
        Lease Agreement Ready for Signature
    </div>

    <div class="message-content">
        <p>Dear {{ $tenant->profile->first_name ?? 'Tenant' }} {{ $tenant->profile->last_name ?? '' }},</p>

        <p>Your lease agreement is ready and requires your signature. Please review the document carefully and sign it electronically to complete your tenancy registration with <strong>{{ $companyName }}</strong>.</p>

        <div class="highlight-box">
            <h3 style="color: #D9A600; margin-bottom: 15px;">📋 Lease Agreement Details:</h3>
            <p><strong>Property:</strong> {{ $property->name ?? 'N/A' }} -> {{ $bed->bed_label ?? 'N/A' }}</p>
            <p><strong>Address:</strong> {{ $property->address ?? 'N/A' }}{{ isset($property->city) ? ', ' . $property->city : '' }}{{ isset($property->state) ? ', ' . $property->state : '' }} {{ $property->zip_code ?? '' }}</p>
            <p><strong>Lease Start Date:</strong> {{ \Carbon\Carbon::parse($lease->start_date)->format('F d, Y') }}</p>
            <p><strong>Lease End Date:</strong> {{ \Carbon\Carbon::parse($lease->end_date)->format('F d, Y') }}</p>
            <p><strong>Monthly Rent:</strong> ${{ number_format($lease->rent_amount, 2) }}</p>
            <p><strong>Security Deposit:</strong> ${{ number_format($lease->deposit_amount, 2) }}</p>
        </div>

        <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 25px 0; border-radius: 0 4px 4px 0;">
            <h3 style="color: #856404; margin-bottom: 10px;">⚠️ Important Notice:</h3>
            <p style="color: #856404; margin: 0;">This signature request will expire in <strong>{{ $expirationDays }} days</strong>. Please sign the document before it expires to avoid delays in your move-in process.</p>
        </div>

        <div style="text-align: center; margin: 35px 0;">
            <a href="{{ $signatureUrl }}" class="action-button"
                style="display: inline-block; background-color: #D9A600; color: #000000; text-decoration: none; padding: 18px 40px; border-radius: 6px; font-weight: 700; font-size: 18px; border: none; cursor: pointer; text-align: center; box-shadow: 0 4px 12px rgba(217, 166, 0, 0.3);">
                ✍️ Sign Lease Agreement
            </a>
        </div>

        <p><strong>How to Sign:</strong></p>
        <ol style="margin-left: 20px; margin-bottom: 20px; color: #333333;">
            <li>Click the "Sign Lease Agreement" button above</li>
            <li>Review the complete lease document carefully</li>
            <li>Draw or type your signature in the designated area</li>
            <li>Confirm your signature to complete the process</li>
            <li>Download a copy of the signed lease for your records</li>
        </ol>

        <div class="highlight-box" style="border-left-color: #000000; margin-top: 25px;">
            <h3 style="color: #000000; margin-bottom: 15px;">📌 What Happens Next?</h3>
            <p>After you sign the lease agreement:</p>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li>Our property management team will review and countersign the document</li>
                <li>You will receive a fully executed copy via email</li>
                <li>You can access the signed document anytime from your tenant portal</li>
                <li>Move-in instructions will be sent once all signatures are complete</li>
            </ul>
        </div>

        <p style="margin-top: 25px;">If you have any questions about the lease terms or need assistance with the signing process, please don't hesitate to contact our team.</p>

        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 4px; margin-top: 20px;">
            <p style="margin: 0; font-size: 14px; color: #666;">
                <strong>Having trouble with the button?</strong> Copy and paste this link into your browser:<br>
                <a href="{{ $signatureUrl }}" style="color: #D9A600; word-break: break-all;">{{ $signatureUrl }}</a>
            </p>
        </div>
    </div>
@endsection

@section('disclaimer')
    This email was sent to {{ $tenant->email }} regarding your lease agreement with {{ $companyName }}.
    <br>
    The signature link in this email is unique to you and expires in {{ $expirationDays }} days.
    <br>
    Please do not forward this email as it contains a secure signature link.
    <br><br>
    For inquiries, contact <a href="mailto:{{ $companyEmail }}" style="color: #D9A600 !important; text-decoration: none;">{{ $companyEmail }}</a> or call {{ $companyPhone }}.
    <br><br>
    © {{ date('Y') }} {{ $companyName }}. All rights reserved.
@endsection
