@extends('emails.layout.mail')

@section('content')
    <div class="greeting">
        Your Lease Has Ended
    </div>

    <div class="message-content">
        <p>Dear {{ $tenant->profile->first_name ?? 'Tenant' }} {{ $tenant->profile->last_name ?? '' }},</p>

        <p>We wanted to inform you that your lease agreement with <strong>{{ $companyName }}</strong> has officially ended. We hope your time with us was a pleasant experience.</p>

        <div class="highlight-box">
            <h3 style="color: #ba9779; margin-bottom: 15px;">📋 Lease Summary:</h3>
            <p><strong>Property:</strong> {{ $property->name ?? 'N/A' }} {{ isset($bed) ? '-> ' . ($bed->bed_label ?? 'N/A') : '' }}</p>
            <p><strong>Address:</strong> {{ $property->address ?? 'N/A' }}{{ isset($property->city) ? ', ' . $property->city : '' }}{{ isset($property->state) ? ', ' . $property->state : '' }} {{ $property->zip_code ?? '' }}</p>
            <p><strong>Lease Start Date:</strong> {{ \Carbon\Carbon::parse($lease->start_date)->format('F d, Y') }}</p>
            <p><strong>Lease End Date:</strong> {{ \Carbon\Carbon::parse($lease->end_date)->format('F d, Y') }}</p>
            <p><strong>Status:</strong> <span style="color: #28a745; font-weight: 600;">Completed</span></p>
        </div>

        <div style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 20px; margin: 25px 0; border-radius: 0 4px 4px 0;">
            <h3 style="color: #155724; margin-bottom: 10px;">✅ Lease Completed Successfully</h3>
            <p style="color: #155724; margin: 0;">Your lease has been marked as complete in our system. The bed/unit you were assigned is now available for future tenants.</p>
        </div>

        {{-- <div class="highlight-box" style="border-left-color: #000000; margin-top: 25px;">
            <h3 style="color: #000000; margin-bottom: 15px;">📌 What's Next?</h3>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li>If you have any pending matters regarding your deposit, our team will contact you shortly</li>
                <li>Please ensure all your belongings have been removed from the property</li>
                <li>Return all keys and access cards to the property management office</li>
                <li>If you're interested in a new lease, feel free to reach out to us</li>
            </ul>
        </div> --}}

        <p style="margin-top: 25px;">Thank you for being our tenant. We wish you all the best in your future endeavors!</p>

        <p>If you have any questions or concerns, please don't hesitate to contact us.</p>
    </div>
@endsection

@section('disclaimer')
    This email was sent to {{ $tenant->email }} regarding your completed lease with {{ $companyName }}.
    <br><br>
    For inquiries, contact <a href="mailto:{{ $companyEmail }}" style="color: #ba9779 !important; text-decoration: none;">{{ $companyEmail }}</a>{{ !empty($companyPhone) ? ' or call ' . $companyPhone : '' }}.
    <br><br>
    © {{ date('Y') }} {{ $companyName }}. All rights reserved.
@endsection
