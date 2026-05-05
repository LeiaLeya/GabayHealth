<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GabayHealth – Account Setup</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6fb;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f6fb;padding:40px 16px;">
  <tr>
    <td align="center">
      <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;">

        {{-- Brand Header --}}
        <tr>
          <td align="center" style="padding-bottom:24px;">
            <table cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td align="center">
                  <span style="font-size:22px;font-weight:700;color:#1657c1;letter-spacing:-0.3px;">Gabay<span style="font-weight:400;font-style:italic;">Health</span></span>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        {{-- Hero Card --}}
        <tr>
          <td style="background:#1657c1;border-radius:12px 12px 0 0;padding:36px 40px 32px;text-align:center;">
            <div style="display:inline-block;width:56px;height:56px;background:rgba(255,255,255,0.15);border-radius:14px;line-height:56px;font-size:26px;margin-bottom:16px;">&#127973;</div>
            <h1 style="margin:0 0 8px;font-size:22px;font-weight:700;color:#ffffff;line-height:1.3;">Your RHU Account is Approved!</h1>
            <p style="margin:0;font-size:14px;color:rgba(255,255,255,0.78);line-height:1.6;">Complete your setup to get started with GabayHealth.</p>
          </td>
        </tr>

        {{-- Body --}}
        <tr>
          <td style="background:#ffffff;border-radius:0 0 12px 12px;padding:36px 40px;">

            <p style="margin:0 0 20px;font-size:15px;color:#374151;line-height:1.7;">
              Dear <strong style="color:#111827;">{{ $rhuName }}</strong>,
            </p>
            <p style="margin:0 0 28px;font-size:15px;color:#374151;line-height:1.7;">
              Your Rural Health Unit application has been <strong style="color:#1657c1;">approved</strong> by the GabayHealth System Administrator. You're one step away from accessing the platform.
            </p>

            {{-- Credential Box --}}
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
              <tr>
                <td style="background:#f0f5ff;border:1px solid #c7d9fa;border-radius:10px;padding:20px 24px;">
                  <p style="margin:0 0 12px;font-size:11px;font-weight:700;letter-spacing:0.7px;text-transform:uppercase;color:#1657c1;">Your Login Credentials</p>
                  <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <td style="padding:8px 0;border-bottom:1px solid #dce8fb;">
                        <span style="font-size:12px;color:#6b7280;font-weight:500;display:block;margin-bottom:2px;">Username</span>
                        <span style="font-size:16px;font-weight:700;color:#111827;font-family:'Courier New',Courier,monospace;letter-spacing:0.5px;">{{ $username }}</span>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding:8px 0 0;">
                        <span style="font-size:12px;color:#6b7280;font-weight:500;display:block;margin-bottom:2px;">Password</span>
                        <span style="font-size:14px;color:#374151;font-style:italic;">Set during account activation (link below)</span>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>

            {{-- Steps --}}
            <p style="margin:0 0 14px;font-size:13px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;color:#6b7280;">How to Activate</p>
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
              <tr>
                <td style="padding:8px 0;">
                  <table cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <td style="width:28px;height:28px;background:#1657c1;border-radius:50%;text-align:center;vertical-align:middle;font-size:12px;font-weight:700;color:#fff;">1</td>
                      <td style="padding-left:12px;font-size:14px;color:#374151;">Click the <strong>Activate Account</strong> button below.</td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td style="padding:8px 0;">
                  <table cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <td style="width:28px;height:28px;background:#1657c1;border-radius:50%;text-align:center;vertical-align:middle;font-size:12px;font-weight:700;color:#fff;">2</td>
                      <td style="padding-left:12px;font-size:14px;color:#374151;">Create a strong password for your account.</td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td style="padding:8px 0;">
                  <table cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <td style="width:28px;height:28px;background:#1657c1;border-radius:50%;text-align:center;vertical-align:middle;font-size:12px;font-weight:700;color:#fff;">3</td>
                      <td style="padding-left:12px;font-size:14px;color:#374151;">Log in using your username and new password.</td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>

            {{-- CTA Button --}}
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
              <tr>
                <td align="center">
                  <a href="{{ $setupUrl }}"
                     style="display:inline-block;background:#1657c1;color:#ffffff;text-decoration:none;font-size:15px;font-weight:600;padding:14px 40px;border-radius:8px;letter-spacing:0.2px;">
                    Activate My Account &rarr;
                  </a>
                </td>
              </tr>
            </table>

            {{-- Fallback URL --}}
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
              <tr>
                <td style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:14px 16px;">
                  <p style="margin:0 0 4px;font-size:11px;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:0.5px;">Or copy this link into your browser</p>
                  <p style="margin:0;font-size:12px;color:#6b7280;word-break:break-all;line-height:1.6;">{{ $setupUrl }}</p>
                </td>
              </tr>
            </table>

            {{-- Warning --}}
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
              <tr>
                <td style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:14px 16px;">
                  <p style="margin:0;font-size:13px;color:#92400e;line-height:1.6;">
                    <strong>&#9200; Link expires in 24 hours.</strong> Please activate your account as soon as possible. If the link expires, contact the GabayHealth System Administrator for a new one.
                  </p>
                </td>
              </tr>
            </table>

            <p style="margin:0 0 4px;font-size:14px;color:#374151;line-height:1.7;">
              If you have any questions, please reach out to the System Administrator.
            </p>
            <p style="margin:0;font-size:14px;color:#374151;line-height:1.7;">
              Welcome to GabayHealth!<br>
              <strong style="color:#111827;">The GabayHealth Team</strong>
            </p>

          </td>
        </tr>

        {{-- Footer --}}
        <tr>
          <td align="center" style="padding:28px 0 8px;">
            <p style="margin:0 0 4px;font-size:12px;color:#9ca3af;">This is an automated message — please do not reply directly to this email.</p>
            <p style="margin:0;font-size:12px;color:#9ca3af;">&copy; {{ date('Y') }} GabayHealth. All rights reserved.</p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
