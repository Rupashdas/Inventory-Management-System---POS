<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Verification OTP</title>
    <style>
        /* Reset & base styles */
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }
        a {
            color: #1e90ff;
            text-decoration: none;
        }
        /* Container */
        .email-wrapper {
            width: 100%;
            background-color: #f5f7fa;
            padding: 20px 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        /* Header */
        .email-header {
            background-color: #1e90ff;
            padding: 30px 20px;
            text-align: center;
            color: #ffffff;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        /* Body */
        .email-body {
            padding: 30px 25px;
            font-size: 16px;
            line-height: 1.6;
            color: #555555;
        }
        .email-body p {
            margin-bottom: 20px;
        }
        /* OTP Section */
        .otp-code {
            display: block;
            background-color: #f0f4ff;
            color: #1e90ff;
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            padding: 15px 0;
            border-radius: 6px;
            letter-spacing: 6px;
            margin: 20px 0;
        }
        .otp-button {
            display: block;
            width: fit-content;
            margin: 20px auto;
            background-color: #1e90ff;
            color: #ffffff !important;
            font-weight: 600;
            padding: 12px 25px;
            border-radius: 5px;
            text-align: center;
        }
        /* Footer */
        .email-footer {
            padding: 20px 25px;
            font-size: 13px;
            color: #999999;
            text-align: center;
            border-top: 1px solid #e5e5e5;
        }
        .email-footer a {
            color: #1e90ff;
            margin: 0 5px;
        }
        /* Responsive */
        @media screen and (max-width: 600px) {
            .email-body, .email-footer {
                padding: 20px 15px;
            }
            .otp-code {
                font-size: 24px;
                letter-spacing: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <h1>Verify Your Account</h1>
            </div>

            <!-- Body -->
            <div class="email-body">
                <p>Hello {{ $fullName ?? 'User' }},</p>
                <p>We received a request to access your account. Please use the One-Time Password (OTP) below to verify your identity:</p>

                <span class="otp-code">{{ $otp }}</span>

                <p>Or click the button below to automatically verify your account:</p>
                <a href="{{ $verificationLink ?? '#' }}" class="otp-button">Verify Account</a>

                <p>This OTP is valid for 10 minutes. Do not share it with anyone.</p>
                <p>If you did not request this, please ignore this email or contact our support team.</p>
            </div>

            <!-- Footer -->
            <div class="email-footer">
                &copy; {{ date('Y') }} Your Company. All rights reserved.<br>
                <a href="{{ $companyWebsite ?? '#' }}">Website</a> | 
                <a href="{{ $supportEmail ?? '#' }}">Support</a>
            </div>
        </div>
    </div>
</body>
</html>
