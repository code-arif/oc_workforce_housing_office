@extends('emails.layout.mail')

@section('content')
    <div class="greeting">
        Welcome to {{ $companyName }}!
    </div>

    <div class="message-content">
        <p>Dear {{ $tenant->profile->last_name }},</p>

        <p>Congratulations! We are delighted to inform you that you have been successfully registered as a tenant with <strong>{{ $companyName }}</strong>. Welcome to our community!</p>

        <div class="highlight-box">
            <h3 style="color: #ba9779; margin-bottom: 15px;">Your Tenant Information:</h3>
            <p><strong>Tenant ID:</strong> {{ $tenant->id }}</p>
            <p><strong>Name:</strong> {{ $tenant->profile->first_name }} {{ $tenant->profile->last_name }}</p>
            <p><strong>Email:</strong> {{ $tenant->email }}</p>
            <p><strong>Registration Date:</strong> {{ $tenant->created_at->format('F d, Y') }}</p>
            <p><strong>Status:</strong> <span style="color: #28a745; font-weight: 600;">Active</span></p>
        </div>

        <div class="highlight-box" style="border-left-color: #000000; margin-top: 25px;">
            <h3 style="color: #000000; margin-bottom: 15px;">Your Property Details:</h3>
            <p><strong>Property Name:</strong> {{ $property->name ?? 'N/A' }}</p>
            <p><strong>Address:</strong> {{ $property->address ?? 'N/A' }}</p>
            @if(isset($property->unit))
                <p><strong>Unit Number:</strong> {{ $property->unit }}</p>
            @endif
            @if(isset($property->type))
                <p><strong>Property Type:</strong> {{ $property->type }}</p>
            @endif
            @if(isset($lease->start_date))
                <p><strong>Lease Start Date:</strong> {{ \Carbon\Carbon::parse($lease->start_date)->format('F d, Y') }}</p>
            @endif
            @if(isset($lease->end_date))
                <p><strong>Lease End Date:</strong> {{ \Carbon\Carbon::parse($lease->end_date)->format('F d, Y') }}</p>
            @endif
            @if(isset($lease->monthly_rent))
                <p><strong>Monthly Rent:</strong> ${{ number_format($lease->monthly_rent, 2) }}</p>
            @endif
        </div>

        <p style="margin-top: 25px;">As a registered tenant, you now have access to:</p>
        <ul style="margin-left: 20px; margin-bottom: 20px; color: #333333;">
            <li>Online payment portal for rent and utilities</li>
            <li>Maintenance request submission system</li>
            <li>Important documents and lease agreement access</li>
            <li>Direct communication with property management</li>
            <li>Community updates and announcements</li>
        </ul>

        <div style="text-align: center; margin: 30px 0;">
            @if(isset($tenantPortalUrl))
                <a href="{{ $tenantPortalUrl }}" class="action-button"
                    style="display: inline-block; background-color: #ba9779; color: #000000; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-weight: 600; font-size: 16px; margin: 10px; border: none; cursor: pointer; text-align: center;">
                    Access Tenant Portal →
                </a>
            @endif
            @if(isset($supportUrl))
                <a href="{{ $supportUrl }}" class="action-button"
                    style="display: inline-block; background-color: #000000; color: #ba9779; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-weight: 600; font-size: 16px; margin: 10px; border: 1px solid #ba9779; cursor: pointer; text-align: center;">
                    Contact Support
                </a>
            @endif
        </div>

        <p><strong>Important Next Steps:</strong></p>
        <ol style="margin-left: 20px; margin-bottom: 20px; color: #333333;">
            <li>Review your lease agreement and property details carefully</li>
            <li>Set up your online payment account for convenient rent payments</li>
            <li>Familiarize yourself with property rules and community guidelines</li>
            <li>Save our contact information for future reference</li>
        </ol>

        <p>If you have any questions or need assistance, our team is here to help. We look forward to providing you with excellent service throughout your tenancy.</p>
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