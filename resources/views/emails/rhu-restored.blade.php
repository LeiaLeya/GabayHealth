<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f4f4f4; }
        .wrapper { max-width: 600px; margin: 32px auto; padding: 0 16px; }
        .card { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #1657c1; padding: 28px 32px; text-align: center; }
        .header-logo { font-size: 22px; font-weight: 700; color: #fff; letter-spacing: -.3px; }
        .header-sub { font-size: 13px; color: rgba(255,255,255,.75); margin-top: 4px; }
        .success-banner { background: #dcfce7; border-left: 4px solid #16a34a; padding: 14px 20px; }
        .success-banner p { margin: 0; font-size: 13px; color: #14532d; font-weight: 600; }
        .content { padding: 28px 32px; }
        .content p { margin: 0 0 14px; font-size: 14px; color: #4b5563; }
        .info-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px 20px; margin: 20px 0; }
        .info-row { display: flex; gap: 12px; padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #9ca3af; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: .4px; min-width: 110px; padding-top: 1px; }
        .info-value { color: #1f2937; font-weight: 500; }
        .cta-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 16px 20px; margin: 20px 0; }
        .cta-box p { margin: 0; font-size: 13px; color: #1e40af; }
        .footer { text-align: center; padding: 20px 32px; border-top: 1px solid #f1f5f9; }
        .footer p { margin: 0; font-size: 11px; color: #9ca3af; line-height: 1.8; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <div class="header-logo">GabayHealth</div>
                <div class="header-sub">Rural Health Unit Management System</div>
            </div>

            <div class="success-banner">
                <p>✅ Account Restored — You're back online</p>
            </div>

            <div class="content">
                <p>Dear <strong>{{ $rhuName }}</strong>,</p>

                <p>Great news! Your Rural Health Unit account on the GabayHealth platform has been <strong>restored</strong> by the system administrator. Your account is now fully active.</p>

                <div class="info-box">
                    <div class="info-row">
                        <span class="info-label">RHU Name</span>
                        <span class="info-value">{{ $rhuName }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Restored on</span>
                        <span class="info-value">{{ $restoredAt }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">New Status</span>
                        <span class="info-value" style="color:#166534;font-weight:700;">Active</span>
                    </div>
                </div>

                <p>Your staff can now log in to GabayHealth using their existing credentials. All previously saved data — including barangay records, events, schedules, inventory, and reports — is intact exactly as it was before the account was archived.</p>

                <div class="cta-box">
                    <p><strong>Ready to get started?</strong> Log in to the GabayHealth system using your existing username and password. If you have trouble accessing your account, please contact the system administrator at <a href="mailto:{{ $contactEmail }}" style="color:#1657c1;">{{ $contactEmail }}</a>.</p>
                </div>

                <p style="margin-top:24px;">Welcome back,<br><strong>GabayHealth System</strong></p>
            </div>

            <div class="footer">
                <p>This is an automated notification from GabayHealth.<br>
                Please do not reply to this email.<br>
                &copy; {{ date('Y') }} GabayHealth. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
