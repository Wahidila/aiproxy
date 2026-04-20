<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Undangan {{ $appName }}</title>
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
                                Halo {{ $invitation->name }},
                            </h1>
                            <p style="margin: 0 0 16px; font-size: 15px; line-height: 1.6; color: #7b7b78;">
                                Anda telah diundang oleh <strong style="color: #111111;">{{ $invitation->invitedBy->name }}</strong> untuk bergabung di <strong style="color: #111111;">{{ $appName }}</strong>.
                            </p>
                            <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.6; color: #7b7b78;">
                                Klik tombol di bawah untuk membuat password dan mengaktifkan akun Anda.
                            </p>

                            {{-- CTA Button --}}
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 auto 24px;">
                                <tr>
                                    <td style="background-color: #111111; border-radius: 4px;">
                                        <a href="{{ $acceptUrl }}" target="_blank" style="display: inline-block; padding: 14px 32px; font-size: 15px; font-weight: 500; color: #ffffff; text-decoration: none;">
                                            Buat Password &amp; Aktifkan Akun
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 8px; font-size: 13px; line-height: 1.5; color: #7b7b78;">
                                Atau copy link berikut ke browser Anda:
                            </p>
                            <p style="margin: 0 0 24px; font-size: 13px; line-height: 1.5; color: #ff5600; word-break: break-all;">
                                {{ $acceptUrl }}
                            </p>

                            <div style="border-top: 1px solid #dedbd6; padding-top: 16px;">
                                <p style="margin: 0; font-size: 12px; line-height: 1.5; color: #7b7b78;">
                                    Link ini berlaku hingga <strong>{{ $expiresAt }}</strong> (72 jam).
                                    Jika Anda tidak merasa diundang, abaikan email ini.
                                </p>
                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding: 20px 40px; background-color: #faf9f6; border-top: 1px solid #dedbd6; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #7b7b78;">
                                &copy; {{ date('Y') }} {{ $appName }}. Email ini dikirim secara otomatis.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
