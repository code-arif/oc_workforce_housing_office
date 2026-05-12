<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Request #{{ $maintenance->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #1e3a5f;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        .section {
            margin-bottom: 20px;
            clear: both;
        }
        .section-title {
            background-color: #f8fafc;
            padding: 8px 12px;
            font-weight: bold;
            color: #1e3a5f;
            border-left: 4px solid #1e3a5f;
            margin-bottom: 15px;
            font-size: 14px;
            display: block;
            width: 100%;
        }
        .info-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .info-grid td {
            padding: 8px;
            vertical-align: top;
            border-bottom: 1px solid #eee;
        }
        .label {
            font-weight: bold;
            width: 30%;
            color: #555;
        }
        .value {
            width: 70%;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #fff;
        }
        .bg-primary { background-color: #3b82f6; }
        .bg-warning { background-color: #f59e0b; }
        .bg-success { background-color: #10b981; }
        .bg-danger { background-color: #ef4444; }
        .bg-secondary { background-color: #6b7280; }
        
        .description-box {
            background-color: #fdfdfd;
            border: 1px solid #eee;
            padding: 15px;
            border-radius: 4px;
            min-height: 100px;
            clear: both;
        }
        .attachments-grid {
            width: 100%;
            margin-top: 10px;
            display: block;
            clear: both;
        }
        .attachment-item {
            display: inline-block;
            width: 30%;
            margin-right: 2%;
            margin-bottom: 20px;
            text-align: center;
            vertical-align: top;
            page-break-inside: avoid;
        }
        .attachment-item img {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 5px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    @php
        $profile = $maintenance->tenant?->profile;
        $tenantName = $profile ? trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? '')) : 'No Tenant';
        
        $property = $maintenance->property ?? $maintenance->tenant?->activeLease?->property;
        $unit = $maintenance->unitModel;
        $room = $maintenance->room;
        $bed = $maintenance->bed;

        $statusColors = [
            'pending' => 'bg-primary',
            'in_progress' => 'bg-warning',
            'completed' => 'bg-success',
            'rejected' => 'bg-danger',
            'cancelled' => 'bg-secondary',
        ];
        $statusLabels = [
            'pending' => 'Open',
            'in_progress' => 'In Progress',
            'completed' => 'Resolved',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
        ];
    @endphp

    <div class="header">
        <h1>Maintenance Request Details</h1>
        <p>Request #{{ $maintenance->id }} | Generated on {{ date('M d, Y h:i A') }}</p>
    </div>

    <div class="section">
        <div class="section-title">General Information</div>
        <table class="info-grid">
            <tr>
                <td class="label">Title</td>
                <td class="value"><strong>{{ $maintenance->title }}</strong></td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="value">
                    <span class="badge {{ $statusColors[$maintenance->status] ?? 'bg-secondary' }}">
                        {{ $statusLabels[$maintenance->status] ?? ucfirst($maintenance->status) }}
                    </span>
                    @if($maintenance->is_urgent)
                        <span class="badge bg-danger">URGENT</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Category</td>
                <td class="value">{{ ucfirst($maintenance->category) }}</td>
            </tr>
            <tr>
                <td class="label">Requested Date</td>
                <td class="value">{{ date('M d, Y h:i A', strtotime($maintenance->created_at)) }}</td>
            </tr>
            <tr>
                <td class="label">Permission to Enter</td>
                <td class="value">{{ $maintenance->grant_permission ? 'Yes' : 'No' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Location & Tenant</div>
        <table class="info-grid">
            <tr>
                <td class="label">Property</td>
                <td class="value">{{ $property?->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Address</td>
                <td class="value">{{ $property?->address ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Unit/Room/Bed</td>
                <td class="value">
                    @if($unit) Unit: {{ $unit->name }} @endif
                    @if($room) | Room: {{ $room->room_number }} @endif
                    @if($bed) | Bed: {{ $bed->bed_label ?? $bed->bed_number }} @endif
                    @if(!$unit && !$room && !$bed) N/A @endif
                </td>
            </tr>
            <tr>
                <td class="label">Tenant Name</td>
                <td class="value">{{ $tenantName }}</td>
            </tr>
            <tr>
                <td class="label">Tenant Contact</td>
                <td class="value">
                    Email: {{ $maintenance->tenant?->email ?? 'N/A' }}<br>
                    Phone: {{ $profile?->phone ?? 'N/A' }}
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Description</div>
        <div class="description-box">
            {{ $maintenance->description }}
        </div>
    </div>

    @if($maintenance->attachments->count() > 0)
    <div class="section">
        <div class="section-title">Attachments ({{ $maintenance->attachments->count() }})</div>
        <div class="attachments-grid">
            @foreach($maintenance->attachments as $attachment)
                @php
                    $ext = pathinfo($attachment->attachment_path, PATHINFO_EXTENSION);
                    $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'jfif']);
                    $fullPath = public_path($attachment->attachment_path);
                @endphp
                @if($isImage && file_exists($fullPath))
                    <div class="attachment-item">
                        <img src="data:image/{{ $ext }};base64,{{ base64_encode(file_get_contents($fullPath)) }}" alt="Attachment">
                    </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    <div class="footer">
        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    </div>
</body>
</html>
