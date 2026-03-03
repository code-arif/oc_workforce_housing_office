@extends('emails.layout.mail')

@section('content')
    <div class="greeting">
        New Contact Form Submission
    </div>

    <div class="message-content">
        <p>Dear Admin,</p>

        <p>A new message has been received through the contact form. Here are the details submitted by the user:</p>

        <div class="highlight-box">
            <h3 style="color: #ba9779; margin-bottom: 15px;">Contact Information:</h3>
            <p><strong>Name:</strong> {{ $contact->first_name ?? '' }} {{ $contact->last_name ?? 'N/A' }}</p>
            <p><strong>Email:</strong> {{ $contact->email ?? 'N/A' }}</p>
            <p><strong>Phone:</strong> {{ $contact->phone ?? 'N/A' }}</p>
        </div>

        <div class="highlight-box">
            <h3 style="color: #ba9779; margin-bottom: 15px;">Message Details:</h3>
            <p><strong>Subject:</strong> {{ $contact->subject ?? 'No subject provided' }}</p>
            <p><strong>Message:</strong></p>
            <div style="background-color: #f8f9fa; border: 1px solid #e2e8f0; padding: 15px; border-radius: 6px; white-space: pre-wrap; word-wrap: break-word;">
                {{ $contact->message ?? 'No message content provided.' }}
            </div>
            <p style="margin-top: 12px; color: #6b7280; font-size: 0.95em;">
                Submitted on: {{ $contact->created_at->format('F d, Y \a\t h:i A') }}
            </p>
        </div>

        <p style="margin-top: 25px;">This is an automated notification from the contact form on your website.</p>
    </div>
@endsection
