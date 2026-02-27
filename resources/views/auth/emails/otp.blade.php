<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset OTP</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f7f7f7; margin: 0; padding: 24px;">
    <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 24px;">
        <h2 style="margin-top: 0; color: #1f2937;">School LMS Password Reset</h2>
        <p style="color: #374151; line-height: 1.5;">
            Use the OTP below to reset your password. This OTP is valid for 5 minutes.
        </p>
        <div style="font-size: 30px; font-weight: 700; letter-spacing: 6px; color: #111827; margin: 18px 0;">
            {{ $otp }}
        </div>
        <p style="color: #6b7280; line-height: 1.5; margin-bottom: 0;">
            If you did not request this, you can ignore this email.
        </p>
    </div>
</body>
</html>
