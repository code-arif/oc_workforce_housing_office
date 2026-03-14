<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject ?? 'Email Notification' }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>

<body style="margin:0;padding:0;background-color:#f5f5f5;font-family:Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">

    <!-- Outer wrapper -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f5f5f5;">
        <tr>
            <td align="center" style="padding:20px 10px;">

                <!-- Email container -->
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background-color:#ffffff;">

                    <!-- HEADER -->
                    <tr>
                        <td align="center" bgcolor="#ba9779" style="background-color:#ba9779;padding:30px 20px;">
                            <h1 style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:26px;font-weight:700;color:#000000;line-height:1.3;">
                                {{ $companyName ?? 'OC Workforce Housing' }}
                            </h1>
                            <p style="margin:6px 0 0 0;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#000000;line-height:1.4;">
                                2004 Philadelphia Ave, Ocean City, Maryland 21842.
                            </p>
                        </td>
                    </tr>

                    <!-- CONTENT -->
                    <tr>
                        <td style="padding:40px 30px;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.6;color:#333333;">

                            @yield('content')

                            @hasSection('content')
                            @else
                                <p style="margin:0 0 16px 0;">
                                    This is your email content. Use Laravel's Blade templating to extend this layout.
                                </p>

                                <!-- Highlight box example -->
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:20px 0;">
                                    <tr>
                                        <td style="border-left:4px solid #ba9779;background-color:#f9f9f9;padding:16px 20px;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#333333;line-height:1.6;">
                                            <strong>Important Information:</strong><br>
                                            Your custom content goes here in highlighted sections.
                                        </td>
                                    </tr>
                                </table>

                                <p style="margin:0 0 20px 0;">Additional details can be placed in regular paragraphs.</p>

                                <!-- CTA button -->
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:20px 0;">
                                    <tr>
                                        <td align="center" bgcolor="#ba9779" style="background-color:#ba9779;border-radius:4px;">
                                            <a href="#" style="display:inline-block;padding:12px 28px;font-family:Arial,Helvetica,sans-serif;font-size:15px;font-weight:700;color:#000000;text-decoration:none;">
                                                Take Action
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <!-- Sign-off -->
                            <p style="margin:30px 0 0 0;font-family:Arial,Helvetica,sans-serif;font-size:15px;color:#333333;line-height:1.6;">
                                Best regards,<br>
                                <strong>{{ $senderName ?? 'The Team' }}</strong><br>
                                {{ $companyName ?? 'OC Workforce Housing' }}
                            </p>

                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td align="center" bgcolor="#000000" style="background-color:#000000;padding:30px 20px;">

                            <p style="margin:0 0 6px 0;font-family:Arial,Helvetica,sans-serif;font-size:16px;font-weight:700;color:#ba9779;">
                                {{ $companyName ?? 'OC Workforce Housing' }}
                            </p>

                            @if (isset($companyPhone))
                                <p style="margin:0 0 4px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#cccccc;">
                                    Phone: {{ $companyPhone }}
                                </p>
                            @endif
                            @if (isset($companyEmail))
                                <p style="margin:0 0 4px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#cccccc;">
                                    Email: <a href="mailto:{{ $companyEmail }}" style="color:#ba9779;text-decoration:none;">{{ $companyEmail }}</a>
                                </p>
                            @endif
                            @if (isset($companyWebsite))
                                <p style="margin:0 0 4px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#cccccc;">
                                    Website: <a href="{{ $companyWebsite }}" style="color:#ba9779;text-decoration:none;">{{ $companyWebsite }}</a>
                                </p>
                            @endif

                            <!-- Divider -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:20px 0 0 0;">
                                <tr>
                                    <td style="border-top:1px solid #333333;font-size:0;line-height:0;">&nbsp;</td>
                                </tr>
                            </table>

                            <p style="margin:16px 0 0 0;font-family:Arial,Helvetica,sans-serif;font-size:11px;color:#888888;line-height:1.5;">
                                @yield('disclaimer')

                                @hasSection('disclaimer')
                                @else
                                    This email was sent to you as part of our business communications.
                                    If you believe you received this in error, please contact us immediately.<br><br>
                                    &copy; {{ date('Y') }} {{ $companyName ?? 'OC Workforce Housing' }}. All rights reserved.
                                @endif
                            </p>

                        </td>
                    </tr>

                </table>
                <!-- /Email container -->

            </td>
        </tr>
    </table>
    <!-- /Outer wrapper -->

</body>

</html>
