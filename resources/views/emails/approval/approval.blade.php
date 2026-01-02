@extends('emails.layout.mail')

@section('content')
<div class="message-content">
    <p>Your application for <strong>OC Workforce Housing</strong> has been approved.</p>

    <div class="highlight-box">
        <h3>Remarks:</h3>
        <p>Your custom remarks or additional information here...</p>
    </div>

    <p>If you have any questions, don't hesitate to reach out.</p>

    <a href="{{ $actionUrl ?? '#' }}" class="action-button">
        View Details
    </a>
</div>
@endsection

@section('disclaimer')
This email was sent by investigators on your contracts on your behalf registered in Hongo.
<br><br>
© {{ date('Y') }} OC Workforce Housing. All rights reserved.
@endsection
