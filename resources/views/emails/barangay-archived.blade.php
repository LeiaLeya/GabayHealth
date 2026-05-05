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
        .alert-banner { background: #fef9c3; border-left: 4px solid #f59e0b; padding: 14px 20px; margin: 0; }
        .alert-banner p { margin: 0; font-size: 13px; color: #92400e; font-weight: 600; }
        .content { padding: 28px 32px; }
        .content p { margin: 0 0 14px; font-size: 14px; color: #4b5563; }
        .info-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px 20px; margin: 20px 0; }
        .info-row { display: flex; gap: 12px; padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #9ca3af; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: .4px; min-width: 110px; padding-top: 1px; }
        .info-value { color: #1f2937; font-weight: 500; }
        .notice-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 16px 20px; margin: 20px 0; }
        .notice-box p { margin: 0; font-size: 13px; color: #92400e; }
        .footer { text-align: center; padding: 20px 32px; border-top: 1px solid #f1f5f9; }
        .footer p { margin: 0; font-size: 11px; color: #9ca3af; line-height: 1.8; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <div class="header-logo">GabayHealth</div>
                <div class="header-sub">Barangay Health Center Management System</div>
            </div>

            <div class="alert-banner">
                <p>⚠️ Account Status Update — Action Required</p>
            </div>

            <div class="content">
                <p>Dear <strong>{{ $barangayName }}</strong>,</p>

                <p>We are writing to inform you that your Barangay Health Center account on the GabayHealth platform has been <strong>archived</strong> by your Rural Health Unit administrator.</p>

                <div class="info-box">
                    <div class="info-row">
                        <span class="info-label">Health Center</span>
                        <span class="info-value">{{ $barangayName }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Managed by RHU</span>
                        <span class="info-value">{{ $rhuName }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Archived on</span>
                        <span class="info-value">{{ $archivedAt }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">New Status</span>
                        <span class="info-value" style="color:#92400e;font-weight:700;">Archived</span>
                    </div>
                </div>

                <p><strong>What does this mean?</strong></p>
                <p>While your account is archived, your health center will not appear in active lists and your staff will not be able to log in to the system. All your data — including records, appointments, and reports — has been <strong>preserved</strong> and is not deleted.</p>

                <div class="notice-box">
                    <p><strong>Can this be reversed?</strong> Yes. Your account can be restored at any time by your Rural Health Unit administrator. If you believe this was done in error or need your account reactivated, please contact your RHU directly.</p>
                </div>

                <p>If you have questions or concerns, please reach out to your Rural Health Unit at <a href="mailto:{{ $contactEmail }}" style="color:#1657c1;">{{ $contactEmail }}</a>.</p>

                <p style="margin-top:24px;">Regards,<br><strong>GabayHealth System</strong></p>
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
