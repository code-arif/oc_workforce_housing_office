@extends('emails.layout.mail')

@section('content')
    <div class="greeting">
        Your Lease Agreement is Fully Executed!
    </div>

    <div class="message-content">
        <p>Dear {{ $tenant->profile->first_name ?? 'Tenant' }} {{ $tenant->profile->last_name ?? '' }},</p>

        <p>Great news! Your lease agreement has been <strong>successfully signed by both you and our property management team</strong>. Your tenancy with <strong>{{ $companyName }}</strong> is now officially confirmed.</p>

        <div class="highlight-box">
            <h3 style="color: #ba9779; margin-bottom: 15px;">📋 Lease Summary</h3>
            <p><strong>Property:</strong> {{ $property->name ?? 'N/A' }}@if($bed) &mdash; {{ $bed->bed_label }}@endif</p>
            <p><strong>Address:</strong> {{ $property->address ?? 'N/A' }}{{ isset($property->city) ? ', ' . $property->city : '' }}{{ isset($property->state) ? ', ' . $property->state : '' }} {{ $property->zip_code ?? '' }}</p>
            <p><strong>Lease Start:</strong> {{ \Carbon\Carbon::parse($lease->start_date)->format('F d, Y') }}</p>
            <p><strong>Lease End:</strong> {{ \Carbon\Carbon::parse($lease->end_date)->format('F d, Y') }}</p>
            <p><strong>Monthly Rent:</strong> ${{ number_format($lease->rent_amount, 2) }}</p>
            <p><strong>Security Deposit:</strong> ${{ number_format($lease->deposit_amount, 2) }}</p>
        </div>

        {{-- First payment call-to-action --}}
        <div style="background-color: #fff8ee; border-left: 4px solid #ba9779; padding: 20px; margin: 25px 0; border-radius: 0 4px 4px 0;">
            <h3 style="color: #7a5c38; margin-bottom: 10px;">💳 Complete Your First Payment</h3>
            <p style="color: #5a4020; margin: 0;">
                Your first invoice is now available in your tenant dashboard. Please log in and complete your payment to finalise your move-in process.
            </p>
        </div>

        <div style="text-align: center; margin: 35px 0;">
            <a href="{{ $paymentsUrl }}"
               style="display: inline-block; background-color: #ba9779; color: #000000; text-decoration: none; padding: 16px 38px; border-radius: 6px; font-weight: 700; font-size: 17px; box-shadow: 0 4px 12px rgba(186,151,121,0.35);">
                Pay Your First Invoice
            </a>
        </div>

        <p style="text-align:center; margin-top: -10px; margin-bottom: 30px;">
            <a href="{{ $dashboardUrl }}"
               style="color: #ba9779; font-size: 14px; text-decoration: underline;">
                Or go to your Tenant Dashboard
            </a>
        </p>

        <div class="highlight-box" style="border-left-color: #000000; margin-top: 10px;">
            <h3 style="color: #000000; margin-bottom: 12px;">What Happens Next?</h3>
            <ul style="margin-left: 20px; margin-top: 8px; line-height: 2;">
                <li>Log in to your tenant dashboard to view and pay your invoices</li>
                <li>A copy of your fully signed lease is available for download anytime</li>
                <li>Move-in instructions and further information will follow shortly</li>
                <li>Reach out to us if you have any questions before your move-in date</li>
            </ul>
        </div>

        <p style="margin-top: 25px;">
            We look forward to welcoming you. If you need any assistance, please don't hesitate to get in touch with our team.
        </p>
    </div>
@endsection

@section('disclaimer')
    This email was sent to {{ $tenant->email }} as confirmation of your signed lease with {{ $companyName }}.
    <br><br>
    For inquiries, contact <a href="mailto:{{ $companyEmail }}" style="color: #ba9779 !important; text-decoration: none;">{{ $companyEmail }}</a> or call {{ $companyPhone }}.
    <br><br>
    © {{ $currentYear }} {{ $companyName }}. All rights reserved.
@endsection
