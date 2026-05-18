@extends('emails.layout.mail')

@section('content')
    <div class="greeting" style="font-size: 20px; font-weight: bold; color: #ba9779; margin-bottom: 20px;">
        Reservation Request Received
    </div>

    <div class="message-content">
        <p>Dear {{ $applicantName }},</p>

        <p>Thank you for submitting your lease reservation request with <strong>{{ $companyName }}</strong>. We have successfully received your application, and our team is currently reviewing the details to ensure we find the perfect accommodation fit for your needs.</p>

        <!-- Company & Contact Details -->
        <div class="highlight-box" style="margin-top: 25px;">
            <h3 style="color: #ba9779; margin-bottom: 15px; font-size: 16px; border-bottom: 2px solid #ba9779; padding-bottom: 6px;">Reservation Details</h3>
            <table style="width: 100%; border-collapse: collapse; background-color: #ffffff; border: 1px solid #e0e0e0; font-size: 14px;">
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; width: 35%; background-color: #f9f9f9;">Company Name</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ $application->company_name }}</td>
                </tr>
                @if($application->industry)
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; background-color: #f9f9f9;">Industry</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ $application->industry }}</td>
                </tr>
                @endif
                @if($application->employee_count)
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; background-color: #f9f9f9;">Employee Count</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ $application->employee_count }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; background-color: #f9f9f9;">Contact Person</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ $applicantName }}</td>
                </tr>
                @if($application->job_title)
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; background-color: #f9f9f9;">Job Title</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ $application->job_title }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; background-color: #f9f9f9;">Email Address</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;"><a href="mailto:{{ $application->email }}" style="color: #ba9779; text-decoration: none;">{{ $application->email }}</a></td>
                </tr>
                @if($application->phone)
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; background-color: #f9f9f9;">Phone Number</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ $application->phone }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding: 12px; font-weight: 600; background-color: #f9f9f9;">Application Status</td>
                    <td style="padding: 12px;"><span style="background-color: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px;">PENDING REVIEW</span></td>
                </tr>
            </table>
        </div>

        @if(!empty($application->reservation_item))
            @php
                $items = is_string($application->reservation_item) ? json_decode($application->reservation_item, true) : $application->reservation_item;
            @endphp
            @if(is_array($items) && count($items) > 0)
                <!-- Requested Accommodations -->
                <div class="highlight-box" style="margin-top: 25px;">
                    <h3 style="color: #ba9779; margin-bottom: 15px; font-size: 16px; border-bottom: 2px solid #ba9779; padding-bottom: 6px;">Requested Accommodations</h3>
                    <table style="width: 100%; border-collapse: collapse; background-color: #ffffff; border: 1px solid #e0e0e0; font-size: 14px;">
                        <thead>
                            <tr style="background-color: #f9f9f9;">
                                <th style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; text-align: left; width: 40%;">Property Location</th>
                                <th style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; text-align: left; width: 30%;">First Preference</th>
                                <th style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; text-align: left; width: 30%;">Second Preference</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ $item['property_name'] ?? 'N/A' }}</td>
                                <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ $item['interested1'] ?? 'N/A' }}</td>
                                <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ $item['interested2'] ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif

        @if($application->notes)
        <!-- Additional Notes -->
        <div class="highlight-box" style="margin-top: 25px; background-color: #f9f9f9; border-left: 4px solid #ba9779; padding: 15px;">
            <h4 style="margin: 0 0 8px 0; color: #333333; font-size: 14px;">Additional Notes Submitted:</h4>
            <p style="margin: 0; font-size: 13.5px; color: #555555; line-height: 1.5; font-style: italic;">"{{ $application->notes }}"</p>
        </div>
        @endif

        <!-- Next Steps -->
        <div class="highlight-box" style="border-left: 4px solid #000000; background-color: #fdfdfd; padding: 20px; margin: 30px 0; border-radius: 0 4px 4px 0;">
            <h3 style="color: #000000; margin: 0 0 12px 0; font-size: 16px;">What Happens Next?</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 13.5px; line-height: 1.5; color: #555555;">
                <tr>
                    <td style="padding: 8px 0; vertical-align: top; width: 6%; font-weight: bold; color: #ba9779;">1.</td>
                    <td style="padding: 8px 0; vertical-align: top;"><strong>Application Review:</strong> Our leasing office will verify your details, check current bed availability, and assess property compatibility.</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; vertical-align: top; font-weight: bold; color: #ba9779;">2.</td>
                    <td style="padding: 8px 0; vertical-align: top;"><strong>Direct Follow-up:</strong> An agent will contact you within 24 business hours to discuss your preferences and finalize the reservation.</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; vertical-align: top; font-weight: bold; color: #ba9779;">3.</td>
                    <td style="padding: 8px 0; vertical-align: top;"><strong>Lease Agreement:</strong> Upon formal approval, we will draft and dispatch the Lease Agreement for digital signature.</td>
                </tr>
            </table>
        </div>

        <p style="margin-top: 25px;">If you need to make any changes to your submission or have immediate questions, feel free to reach out directly to our support team.</p>

        <!-- CTA button -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $supportUrl }}" class="action-button"
                style="display: inline-block; background-color: #000000; color: #ba9779; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; font-size: 14px; border: 1px solid #ba9779; cursor: pointer; text-align: center;">
                Contact Support Team
            </a>
        </div>
    </div>
@endsection

@section('disclaimer')
    This email was sent to {{ $application->email }} to confirm receipt of your lease reservation request with {{ $companyName }}.
    <br>
    Please do not reply directly to this automated notification. For any inquiries, please contact <a href="mailto:{{ $companyEmail }}" style="color: #ba9779 !important; text-decoration: none;">{{ $companyEmail }}</a> or call {{ $companyPhone }}.
    <br><br>
    © {{ $currentYear }} {{ $companyName }}. All rights reserved.
@endsection
