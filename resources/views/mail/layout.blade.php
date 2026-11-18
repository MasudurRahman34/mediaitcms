<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background:#0d1220;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0d1220;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;">
                    <tr>
                        <td style="padding-bottom:24px;">
                            <span style="font-size:18px;font-weight:800;color:#f8fafc;letter-spacing:0.02em;">MEDIA</span>
                            <span style="font-size:10px;font-weight:600;color:#60a5fa;letter-spacing:0.35em;margin-left:6px;">IT SOFT</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#0f1729;border:1px solid #1e2b45;border-radius:16px;padding:28px;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top:22px;color:#475569;font-size:11px;line-height:1.7;">
                            {{ config('app.name') }} ·
                            {{ config('mail.from.address') }}<br>
                            Rivermead Drive, Westlea, Swindon, SN5 7EX, United Kingdom
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
