<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f9f9f9; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; }
        .header { background: #1657c1; color: white; padding: 30px 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { background: white; padding: 30px; border-radius: 0 0 5px 5px; }
        .info-box { background: #f0f0f0; padding: 15px; border-left: 4px solid #1657c1; margin: 20px 0; border-radius: 0 4px 4px 0; }
        .steps-box { background: #eff6ff; padding: 15px 15px 15px 10px; border-left: 4px solid #1657c1; margin: 20px 0; border-radius: 0 4px 4px 0; }
        .steps-box ol { margin: 8px 0 0; padding-left: 20px; }
        .steps-box li { margin-bottom: 8px; }
        .warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; border-radius: 0 4px 4px 0; }
        .footer { text-align: center; font-size: 12px; color: #999; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>GabayHealth Registration Received</h1>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $rhuName }}</strong>,</p>

            <p>Thank you for registering your Rural Health Unit with <strong>GabayHealth</strong>. We have received your application and it is now pending admin review.</p>

            <div class="info-box">
                <p style="margin:0;"><strong>Registration Status:</strong> Pending Admin Approval</p>
            </div>

            <h3>What Happens Next?</h3>
            <div class="steps-box">
                <ol>
                    <li>The <strong>System Administrator</strong> will review your application.</li>
                    <li>Once approved, you will receive a <strong>separate email</strong> with your account credentials.</li>
                    <li>That email will contain a <strong>link to set your password</strong> and activate your GabayHealth account.</li>
                    <li>After setting your password, you'll have <strong>immediate access</strong> to the system.</li>
                </ol>
            </div>

            <div class="warning">
                <strong>⏰ Please Note:</strong> Once you receive the approval email, the password setup link will expire in <strong>24 hours</strong>. Make sure to set your password promptly.
            </div>

            <p>If you have any questions or need to make changes to your application, please contact the GabayHealth support team.</p>

            <p>Best regards,<br><strong>GabayHealth Team</strong></p>
        </div>

        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} GabayHealth. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
