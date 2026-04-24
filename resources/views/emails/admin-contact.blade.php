<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #faf9f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #faf9f6; padding: 40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border: 1px solid #dedbd6; border-radius: 8px; overflow: hidden;">
                    {{-- Header --}}
                    <tr>
                        <td style="padding: 32px 40px 24px; text-align: center; border-bottom: 1px solid #dedbd6;">
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                                <tr>
                                    <td style="vertical-align: middle; padding-right: 8px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ff5600" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <span style="font-size: 20px; font-weight: 600; color: #111111; letter-spacing: -0.02em;">{{ $appName }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding: 32px 40px;">
                            <h1 style="margin: 0 0 16px; font-size: 22px; font-weight: 600; color: #111111; letter-spacing: -0.02em;">
                                Halo {{ $userName }},
                            </h1>

                            <div style="margin: 0 0 24px; font-size: 15px; line-height: 1.7; color: #333333; white-space: pre-line;">{!! nl2br(e($messageBody)) !!}</div>

                            <div style="border-top: 1px solid #dedbd6; padding-top: 16px;">
                                <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #7b7b78;">
                                    Email ini dikirim oleh tim <strong>{{ $appName }}</strong>. Jika Anda memiliki pertanyaan, silakan balas email ini atau hubungi kami melalui halaman Support di dashboard.
                                </p>
                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding: 20px 40px; background-color: #faf9f6; border-top: 1px solid #dedbd6; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #7b7b78;">
                                &copy; {{ date('Y') }} {{ $appName }}. Email ini dikirim oleh admin.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
