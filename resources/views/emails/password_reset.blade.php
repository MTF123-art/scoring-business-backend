<!DOCTYPE html>
<html lang="id" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>Password Sementara Inscore</title>
    <!--[if mso]>
    <xml>
        <o:OfficeDocumentSettings>
            <o:AllowPNG/>
            <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
    </xml>
    <![endif]-->
    <style>
        /* Mobile styles */
        @media only screen and (max-width: 600px) {
            .container { width: 100% !important; }
            .content { padding: 20px !important; }
            .code { font-size: 20px !important; }
        }
        /* Dark mode tweak for some clients */
        @media (prefers-color-scheme: dark) {
            body, .wrapper { background-color: #0f172a !important; color: #e2e8f0 !important; }
            .card { background-color: #111827 !important; }
            .muted { color: #9ca3af !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#f5f7fb; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;">
    <div style="display:none; visibility:hidden; opacity:0; height:0; width:0; overflow:hidden; mso-hide:all;">
        Password sementara Anda untuk masuk ke aplikasi Inscore.
    </div>

    <table class="wrapper" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f5f7fb;">
        <tr>
            <td align="center" style="padding:24px;">
                <table class="container" role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="width:600px; max-width:100%;">
                    <!-- Header -->
                    <tr>
                        <td style="background:#e2e8f0; padding:20px 24px; border-radius:12px 12px 0 0; text-align:center;">
                            <div style="font-family:Arial, Helvetica, sans-serif; font-size:18px; line-height:1; color:#111827; font-weight:bold;">Inscore</div>
                        </td>
                    </tr>

                    <!-- Card Body -->
                    <tr>
                        <td class="card" style="background:#ffffff; border-radius:0; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td class="content" style="padding:28px 28px 8px 28px; font-family:Arial, Helvetica, sans-serif; color:#111827;">
                                        <h1 style="margin:0 0 12px 0; font-size:20px; line-height:28px; font-weight:700; color:#111827;">Password Sementara Akun Inscore</h1>
                                        <p style="margin:0 0 12px 0; font-size:14px; line-height:22px;">Halo, <strong>{{ $name }}</strong>,</p>
                                        <p style="margin:0 0 12px 0; font-size:14px; line-height:22px;">Berikut adalah password sementara untuk akun Anda:</p>
                                        <div class="code" style="display:inline-block; font-family:Consolas, Monaco, 'Courier New', monospace; font-size:24px; letter-spacing:3px; background:#f3f4f6; color:#111827; padding:10px 16px; border-radius:8px; border:1px solid #e5e7eb; margin:6px 0 14px 0;">
                                            {{ $tempPassword }}
                                        </div>
                                        <p style="margin:10px 0 0 0; font-size:14px; line-height:22px;">Silakan login menggunakan password di atas, lalu <strong>segera ganti password</strong> melalui menu "Change Password" di aplikasi.</p>
                                    </td>
                                </tr>

                                <!-- Instruction (no URL) -->
                                <tr>
                                    <td align="left" style="padding:8px 28px 8px 28px; font-family:Arial, Helvetica, sans-serif;">
                                        <div style="display:block; background:#eef2ff; color:#1f2937; font-size:14px; line-height:20px; padding:10px 14px; border-radius:8px; border:1px solid #e5e7eb;">
                                            Buka aplikasi Inscore di ponsel Anda, lalu login menggunakan password sementara di atas.
                                        </div>
                                    </td>
                                </tr>

                                <!-- Divider -->
                                <tr>
                                    <td style="padding:20px 28px 8px 28px;">
                                        <hr style="border:none; border-top:1px solid #e5e7eb; margin:0;">
                                    </td>
                                </tr>

                                <!-- Footer / Notes -->
                                <tr>
                                    <td style="padding:12px 28px 28px 28px; font-family:Arial, Helvetica, sans-serif;">
                                        <p class="muted" style="margin:0; font-size:12px; line-height:18px; color:#6b7280;">Email ini dikirim otomatis oleh sistem Inscore.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

               
                    <!-- App Footer (merged with card) -->
                    <tr>
                        <td align="center" style="background:#e2e8f0; padding:16px 24px; border-radius:0 0 12px 12px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#111827;">
                            &copy; {{ date('Y') }} Inscore
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
