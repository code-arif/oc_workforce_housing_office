<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject ?? 'Email Notification' }}</title>
    <style>
        /* RESET & BASE STYLES */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #000000;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        /* EMAIL CONTAINER */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* HEADER */
        .email-header {
            background-color: #D9A600;
            padding: 30px 20px;
            text-align: center;
            color: #000000;
        }

        .email-header h1 {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 5px;
        }

        .email-header .subtitle {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 400;
        }

        /* CONTENT AREA */
        .email-content {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 25px;
            color: #000000;
        }

        .message-content {
            font-size: 16px;
            line-height: 1.7;
            color: #333333;
            margin-bottom: 30px;
        }

        .highlight-box {
            background-color: #f9f9f9;
            border-left: 4px solid #D9A600;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 4px 4px 0;
        }

        .action-button {
            display: inline-block;
            background-color: #D9A600;
            color: #000000 !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            border: none;
            cursor: pointer;
            text-align: center;
        }

        /* FOOTER */
        .email-footer {
            background-color: #000000;
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }

        .company-info {
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #D9A600;
        }

        .contact-info {
            font-size: 14px;
            line-height: 1.6;
            opacity: 0.9;
        }

        .contact-info a {
            color: #D9A600 !important;
            text-decoration: none;
        }

        .disclaimer {
            font-size: 12px;
            opacity: 0.7;
            line-height: 1.5;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* RESPONSIVE STYLES */
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px;
            }

            .email-content {
                padding: 30px 20px;
            }

            .email-header {
                padding: 25px 15px;
            }

            .email-header h1 {
                font-size: 24px;
            }

            .action-button {
                display: block;
                width: 100%;
                padding: 16px;
            }

            .highlight-box {
                padding: 15px;
            }
        }

        /* OUTLOOK SPECIFIC FIXES */
        @media screen and (max-width: 480px) {
            table[class="responsive-table"] {
                width: 100% !important;
            }
        }

        /* FORCE CENTERING IN MOBILE */
        .center-on-mobile {
            text-align: center !important;
        }
    </style>
</head>

<body>
    <!--[if mso]>
    <div style="font-family: Arial, sans-serif;">
    <![endif]-->

    <div class="email-container">
        <!-- HEADER SECTION -->
        <div class="email-header">
            <h1>{{ $companyName ?? 'OC Workforce Housing' }}</h1>
            <div class="subtitle">{{ $tagline ?? 'Professional Business Solutions' }}</div>
        </div>

        <!-- CONTENT SECTION -->
        <div class="email-content">
            <!-- Main Content Slot -->
            @yield('content')

            <!-- Default content if no yield -->
            @hasSection('content')
            @else
                <div class="message-content">
                    <p>This is your email content. Use Laravel's blade templating to extend this layout.</p>

                    <div class="highlight-box">
                        <strong>Important Information:</strong>
                        <p>Your custom content goes here in highlighted sections.</p>
                    </div>

                    <p>Additional details and explanations can be placed in regular paragraphs.</p>

                    <a href="#" class="action-button">Take Action</a>
                </div>
            @endif

            <!-- Closing -->
            <div class="message-content" style="margin-top: 30px;">
                <p>Best regards,<br>
                    <strong>{{ $senderName ?? 'The Team' }}</strong><br>
                    {{ $companyName ?? 'Company Name' }}
                </p>
            </div>
        </div>

        <!-- FOOTER SECTION -->
        <div class="email-footer">
            <div class="company-info">
                <div class="company-name">{{ $companyName ?? 'OC Workforce Housing' }}</div>
                <div class="contact-info">
                    @if (isset($companyPhone))
                        Phone: {{ $companyPhone }}<br>
                    @endif
                    @if (isset($companyEmail))
                        Email: <a href="mailto:{{ $companyEmail }}">{{ $companyEmail }}</a><br>
                    @endif
                    @if (isset($companyWebsite))
                        Website: <a href="{{ $companyWebsite }}">{{ $companyWebsite }}</a>
                    @endif
                </div>
            </div>

            <div class="disclaimer">
                @yield('disclaimer')

                @hasSection('disclaimer')
                @else
                    This email was sent to you as part of our business communications.
                    If you believe you received this in error, please contact us immediately.
                    <br><br>
                    © {{ date('Y') }} {{ $companyName ?? 'Company Name' }}. All rights reserved.
                @endif
            </div>
        </div>
    </div>

    <!--[if mso]>
    </div>
    <![endif]-->
</body>

</html>
