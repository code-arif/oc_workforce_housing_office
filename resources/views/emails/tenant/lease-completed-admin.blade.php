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

        <div style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 20px; margin: 25px 0; border-radius: 0 4px 4px 0;">
            <h3 style="color: #155724; margin-bottom: 10px;">✅ Actions Taken Automatically:</h3>
            <ul style="margin-left: 20px; margin-top: 10px; color: #155724;">
                <li>Lease status updated to <strong>COMPLETED</strong></li>
                <li>Lease assignment marked as not current (<code>is_current = false</code>)</li>
                <li>Bed marked as unoccupied (<code>is_occupied = false</code>) - Now available for new tenants</li>
                <li>Tenant notification email sent</li>
            </ul>
        </div>

        <div class="highlight-box" style="border-left-color: #ffc107; margin-top: 25px;">
            <h3 style="color: #856404; margin-bottom: 10px;">⚠️ Action Required:</h3>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li>Review the deposit status and process refund if applicable</li>
                <li>Verify that the tenant has vacated the property</li>
                <li>Schedule property inspection if required</li>
                <li>Update any pending invoices or payments</li>
            </ul>
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
