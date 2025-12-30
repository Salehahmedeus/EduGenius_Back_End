<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduGenius Verification</title>
</head>

<body style="margin: 0; padding: 0; background-color: #f4f7fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">

    <!-- Main Container -->
    <div style="width: 100%; table-layout: fixed; background-color: #f4f7fa; padding-top: 40px; padding-bottom: 40px;">

        <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden;">

            <!-- Header (Blue Banner) -->
            <div style="background-color: #3b82f6; padding: 30px; text-align: center;">
                <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700;">EduGenius</h1>
                <p style="color: #e0e7ff; margin: 5px 0 0 0; font-size: 14px;">AI-Powered Learning</p>
            </div>

            <!-- Content Body -->
            <div style="padding: 40px 30px; text-align: center;">

                <h2 style="color: #1f2937; margin-top: 0; font-size: 20px;">Verification Code</h2>
                <p style="color: #6b7280; font-size: 16px; line-height: 1.5; margin-bottom: 30px;">
                    Please use the following One-Time Password (OTP) to access your account.
                </p>

                <!-- The OTP Code Box -->
                <div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin-bottom: 30px; display: inline-block;">
                    <span style="font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #111827; font-family: monospace;">
                        {{ $otp }}
                    </span>
                </div>

                <p style="color: #ef4444; font-size: 14px; margin-bottom: 0;">
                    ⏰ This code expires in <strong>10 minutes</strong>.
                </p>

                <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">

                <p style="color: #9ca3af; font-size: 13px;">
                    If you did not request this code, please ignore this email. Do not share this code with anyone.
                </p>
            </div>

            <!-- Footer -->
            <div style="background-color: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
                <p style="color: #9ca3af; font-size: 12px; margin: 0;">
                    &copy; {{ date('Y') }} EduGenius Application. All rights reserved.
                </p>
            </div>

        </div>
    </div>

</body>

</html>