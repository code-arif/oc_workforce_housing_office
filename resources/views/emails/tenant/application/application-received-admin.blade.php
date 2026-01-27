@extends('emails.layout.mail')

@section('content')
    <div class="message-content">
        <p><strong>Attention Admin,</strong></p>

        <p>A new {{ $applicationType }} reservation request has been submitted and requires your action.</p>

        <div class="highlight-box">
            <h3 style="color: #D9A600; margin-bottom: 20px;">Application Summary</h3>

            <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Application ID:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{{ $application->id }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Application Type:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{{ $applicationType }}</td>
                </tr>

                @if ($application->isCorporate())
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Company Name:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{{ $application->company_name }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Industry:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{{ $application->industry }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Company Address:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{{ $application->company_address }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Contact Person:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{{ $applicantName }}</td>
                    </tr>
                @else
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Applicant Name:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{{ $applicantName }}</td>
                    </tr>
                @endif

                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Email:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{{ $applicantEmail }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Phone:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{{ $applicantPhone }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Submission Date:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;">
                        {{ $application->created_at->format('F d, Y \a\t h:i A') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Current Status:</strong></td>
                    <td style="padding: 8px 0;"><span
                            style="color: #D9A600; font-weight: 600;">{{ $applicationStatus }}</span></td>
                </tr>
            </table>
        </div>

        @if ($application->reservation_item && count($application->reservation_item) > 0)
            <div class="highlight-box" style="margin-top: 25px; border-left-color: #000000;">
                <h3 style="color: #000000; margin-bottom: 15px;">Reservation Details:</h3>
                @foreach ($application->reservation_item as $item)
                    <div style="padding: 10px 0; border-bottom: 1px solid #eee;">
                        <p style="margin: 5px 0;"><strong>Property:</strong> {{ $item['property_name'] ?? 'N/A' }}</p>

                        @if ($application->isIndividual())
                            <p style="margin: 5px 0;">
                                <strong>Interested in bed reservation:</strong>
                                {{ isset($item['is_interested']) && $item['is_interested'] ? 'Yes' : 'No' }}
                            </p>
                        @else
                            <p style="margin: 5px 0;">
                                <strong>Whole Bedroom:</strong>
                                {{ isset($item['is_interested_whole_bedroom']) && $item['is_interested_whole_bedroom'] ? 'Yes' : 'No' }}
                            </p>
                            <p style="margin: 5px 0;">
                                <strong>By Bed:</strong>
                                {{ isset($item['is_interested_by_bed']) && $item['is_interested_by_bed'] ? 'Yes' : 'No' }}
                            </p>
                            @if (isset($item['house_people_per_room']))
                                <p style="margin: 5px 0;">
                                    <strong>People Per Room:</strong> {{ $item['house_people_per_room'] }}
                                </p>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if ($application->notes)
            <div class="highlight-box" style="margin-top: 25px; background-color: #fffbf0;">
                <h4 style="color: #333; margin-bottom: 10px;">Additional Notes:</h4>
                <p style="color: #555; white-space: pre-wrap;">{{ $application->notes }}</p>
            </div>
        @endif

        <p style="margin: 25px 0;">Please review this application and take appropriate action.</p>

        <div style="text-align: center; margin: 40px 0;">
            <table width="100%" cellpadding="0" cellspacing="0" style="text-align: center;">
                <tr>
                    <td align="center">
                        <a href="{{ $viewUrl }}" class="action-button"
                            style="display: inline-block; background-color: #000000; color: #ffffff !important; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-weight: 600; font-size: 16px; margin: 10px; border: none; cursor: pointer; text-align: center;">
                            View Dashboard
                        </a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
@endsection

@section('disclaimer')
    © {{ date('Y') }} {{ $companyName }}. All rights reserved.
@endsection
