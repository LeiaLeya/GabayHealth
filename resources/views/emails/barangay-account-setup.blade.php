<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; }
        .header { background: #28a745; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: white; padding: 30px; border-radius: 0 0 5px 5px; }
        .button { display: inline-block; background: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; font-size: 12px; color: #999; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
        .username-box { background: #f0f0f0; padding: 15px; border-left: 4px solid #28a745; margin: 20px 0; }
        .warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>GabayHealth Barangay Health Center Account Setup</h1>
        </div>
        
        <div class="content">
            <p>Dear <strong>{{ $barangayName }}</strong>,</p>
            
            <p>Your Barangay Health Center account has been <strong>approved</strong> by the Rural Health Unit!</p>
            
            <div class="username-box">
                <p><strong>Your Account Details:</strong></p>
                <p>Username: <code style="background: #f9f9f9; padding: 5px 10px; border-radius: 3px;">{{ $username }}</code></p>
            </div>
            
            <h3>Next Steps:</h3>
            <p>Please click the button below to set your password and activate your account:</p>
            
            <div style="text-align: center;">
                <a href="{{ $setupUrl }}" class="button">Set Password & Activate Account</a>
            </div>
            
            <p>Or copy and paste this link in your browser:</p>
            <p style="word-break: break-all; background: #f9f9f9; padding: 10px; border-radius: 3px;">{{ $setupUrl }}</p>
            
            <div class="warning">
                <strong>⏰ Important:</strong> This activation link expires in <strong>24 hours</strong>. 
                Please complete your account setup as soon as possible.
            </div>
            
            <p>Once you set your password, you'll have <strong>immediate access</strong> to the GabayHealth system to manage your health center's clinical data and reports.</p>
            
            <h3>Questions?</h3>
            <p>If you have any questions or need assistance, please contact your Rural Health Unit administrator.</p>
            
            <p>Best regards,<br><strong>GabayHealth Team</strong></p>
        </div>
        
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} GabayHealth. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
