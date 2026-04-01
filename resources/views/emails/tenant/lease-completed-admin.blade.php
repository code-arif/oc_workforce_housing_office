@extends('emails.layout.mail')

@section('content')
    <div class="greeting">
        Lease Completed Notification
    </div>

    <div class="message-content">
        <p>Dear Admin,</p>

        <p>A lease has automatically been marked as <strong>completed</strong> due to reaching its end date. Below are the details:</p>

        <div class="highlight-box">
            <h3 style="color: #ba9779; margin-bottom: 15px;">👤 Tenant Information:</h3>
            <p><strong>Tenant Name:</strong> {{ $tenant->profile->first_name ?? 'N/A' }} {{ $tenant->profile->last_name ?? '' }}</p>
            <p><strong>Email:</strong> {{ $tenant->email ?? 'N/A' }}</p>
            <p><strong>Phone:</strong> {{ $tenant->profile->phone ?? 'N/A' }}</p>
        </div>

        <div class="highlight-box">
            <h3 style="color: #ba9779; margin-bottom: 15px;">📋 Lease Details:</h3>
            <p><strong>Lease ID:</strong> #{{ $lease->id }}</p>
            <p><strong>Property:</strong> {{ $property->name ?? 'N/A' }}</p>
            @if(isset($bed))
                <p><strong>Bed/Unit:</strong> {{ $bed->bed_label ?? 'N/A' }}</p>
            @endif
            <p><strong>Address:</strong> {{ $property->address ?? 'N/A' }}{{ isset($property->city) ? ', ' . $property->city : '' }}{{ isset($property->state) ? ', ' . $property->state : '' }} {{ $property->zip_code ?? '' }}</p>
            <p><strong>Lease Start Date:</strong> {{ \Carbon\Carbon::parse($lease->start_date)->format('F d, Y') }}</p>
            <p><strong>Lease End Date:</strong> {{ \Carbon\Carbon::parse($lease->end_date)->format('F d, Y') }}</p>
            <p><strong>Monthly Rent:</strong> ${{ number_format($lease->rent_amount, 2) }}</p>
            <p><strong>Security Deposit:</strong> ${{ number_format($lease->deposit_amount, 2) }}</p>
        </div>

        <p style="margin-top: 25px;">This is an automated notification from the lease management system.</p>
    </div>
@endsection

@section('disclaimer')
    This is an automated system notification from {{ $companyName }}.
    <br>
    Lease #{{ $lease->id }} has been completed on {{ now()->format('F d, Y \a\t h:i A') }}.
    <br><br>
    © {{ date('Y') }} {{ $companyName }}. All rights reserved.
@endsection
