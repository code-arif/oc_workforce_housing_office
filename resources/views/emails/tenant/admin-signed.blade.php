@extends('emails.layout.mail')

@section('content')

    <div class="greeting">
        Welcome to OC Workforce Housing
    </div>

    <div class="message-content">

        ```
        <p>
            Dear {{ $tenant->profile->first_name ?? 'Tenant' }}
            {{ $tenant->profile->last_name ?? '' }},
        </p>

        <p>
            Your lease agreement has now been reviewed and signed by our management team.
            The final step is for you to review and complete your signature so your tenancy can be fully activated.
        </p>

        <div class="highlight-box">
            <h3 style="color:#ba9779; margin-bottom:15px;">
                Lease Information
            </h3>

            <p>
                <strong>Property:</strong>
                {{ $property->name ?? 'N/A' }}
                @if($bed)
                    &mdash; {{ $bed->bed_label }}
                @endif
            </p>

            <p>
                <strong>Lease Start:</strong>
                {{ \Carbon\Carbon::parse($lease->start_date)->format('F d, Y') }}
            </p>

            <p>
                <strong>Lease End:</strong>
                {{ \Carbon\Carbon::parse($lease->end_date)->format('F d, Y') }}
            </p>

            <p>
                <strong>Monthly Rent:</strong>
                ${{ number_format($lease->rent_amount, 2) }}
            </p>
        </div>

        <div style="background:#fff8ee;border-left:4px solid #ba9779;padding:20px;margin:25px 0;border-radius:0 4px 4px 0;">
            <h3 style="color:#7a5c38;margin-bottom:10px;">
                Action Required
            </h3>

            <p style="margin:0;">
                Please review and sign your lease agreement as soon as possible.
                Your check-in process cannot be completed until the lease is fully executed.
            </p>
        </div>

        {{-- <div style="text-align:center;margin:35px 0;">
            <a href="{{ $leaseSigningUrl }}"
                style="display:inline-block;background:#ba9779;color:#000;text-decoration:none;padding:16px 38px;border-radius:6px;font-weight:700;font-size:17px;">
                Review & Sign Lease
            </a>
        </div> --}}

        <div class="highlight-box">
            <h3 style="color:#000000;margin-bottom:12px;">
                Check-In Information
            </h3>

            <ul style="margin-left:20px;line-height:2;">
                <li>Check-ins are available Monday – Saturday, 9:00 AM – 5:00 PM</li>
                <li>Office Location: 2004 Philadelphia Ave, Ocean City, MD 21842</li>
                <li>After-hours check-in requires at least 48 hours advance notice</li>
                <li>After-hours check-in cannot be scheduled until the lease is fully signed</li>
                <li>Office Phone / WhatsApp: 443-235-6865</li>
            </ul>
        </div>

        <div class="highlight-box">
            <h3 style="color:#000000;margin-bottom:12px;">
                Mail & Packages
            </h3>

            <ul style="margin-left:20px;line-height:2;">
                <li>Use your full name and apartment number on all deliveries</li>
                <li>Packages are delivered to lobby shelves with 24/7 access</li>
                <li>Paper mail is placed in apartment mailboxes</li>
                <li>Social Security cards are held by management until pickup</li>
            </ul>
        </div>

        <div class="highlight-box">
            <h3 style="color:#000000;margin-bottom:12px;">
                Cleanliness Expectations
            </h3>

            <ul style="margin-left:20px;line-height:2;">
                <li>Clean up immediately after cooking and eating</li>
                <li>Wash dishes after each use</li>
                <li>Keep kitchens and common areas clean at all times</li>
                <li>Remove trash regularly</li>
                <li>Do not leave food or personal belongings in common areas</li>
            </ul>

            <p>
                Daily inspections are performed by management. Cleaning costs and
                violation fees may be charged directly to tenant accounts.
            </p>
        </div>

        <div class="highlight-box">
            <h3 style="color:#000000;margin-bottom:12px;">
                Kitchen & Cooking Rules
            </h3>

            <ul style="margin-left:20px;line-height:2;">
                <li>Never leave cooking equipment unattended</li>
                <li>Remain in the kitchen while food is cooking</li>
                <li>Turn off all cooking equipment after use</li>
                <li>Cooking-related fire alarm activations may result in fines</li>
            </ul>
        </div>

        <div class="highlight-box">
            <h3 style="color:#000000;margin-bottom:12px;">
                Smoking Policy
            </h3>

            <p>
                Smoking is strictly prohibited inside apartments, bedrooms,
                hallways, bathrooms, lounges, stairwells, and laundry rooms.
            </p>

            <p>
                Violations may result in substantial fines and possible eviction.
            </p>
        </div>

        <div class="highlight-box">
            <h3 style="color:#000000;margin-bottom:12px;">
                Guest Policy
            </h3>

            <ul style="margin-left:20px;line-height:2;">
                <li>Guests are allowed only between 10:00 AM and 8:00 PM</li>
                <li>Guests are restricted to lounge areas only</li>
                <li>No guests are permitted in apartments or bedrooms</li>
                <li>No overnight guests are allowed</li>
            </ul>
        </div>

        <div class="highlight-box">
            <h3 style="color:#000000;margin-bottom:12px;">
                Laundry Facilities
            </h3>

            <ul style="margin-left:20px;line-height:2;">
                <li>Laundry rooms are available on the 1st and 2nd floors</li>
                <li>Mobile app and laundry card payment options are available</li>
                <li>Clean lint traps after each use</li>
                <li>Keep laundry areas clean at all times</li>
            </ul>
        </div>

        <div class="highlight-box">
            <h3 style="color:#000000;margin-bottom:12px;">
                Important Safety Information
            </h3>

            <ul style="margin-left:20px;line-height:2;">
                <li>Violence, threats, and theft are strictly prohibited</li>
                <li>Law enforcement will be contacted when necessary</li>
                <li>Property rules are actively enforced</li>
                <li>Violations may result in fines or eviction</li>
            </ul>
        </div>

        <div style="background:#f8f9fa;border:1px solid #e5e7eb;border-radius:8px;padding:20px;margin-top:25px;">
            <h3 style="margin-bottom:12px;">
                Important Contacts
            </h3>

            <p>
                <strong>Office:</strong> 443-235-6865<br>
                <strong>Email:</strong> info@ocworkforcehousing.com
            </p>

            <p>
                <strong>Emergency Line:</strong> 667-282-2502
            </p>

            <p style="margin-bottom:0;">
                For emergencies only: fire alarms, water leaks, major electrical issues,
                or situations involving immediate property damage or safety concerns.
            </p>
        </div>

        <p style="margin-top:25px;">
            We are excited to welcome you to OC Workforce Housing and look forward
            to providing you with a safe, clean, and comfortable living environment.
        </p>
        ```

    </div>
@endsection

@section('disclaimer')
    This email was sent to {{ $tenant->email }} regarding your pending lease agreement with {{ $companyName }}. <br><br>
    For assistance, contact <a href="mailto:{{ $companyEmail }}" style="color:#ba9779 !important;text-decoration:none;">
        {{ $companyEmail }} </a>
    or call {{ $companyPhone }}. <br><br>
    © {{ $currentYear }} {{ $companyName }}. All rights reserved.
@endsection
