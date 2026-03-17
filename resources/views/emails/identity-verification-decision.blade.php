<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $headline }}</title>
</head>
<body style="margin:0;background:#f8f5f0;font-family:Arial,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:20px;overflow:hidden;">
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 12px;font-size:12px;letter-spacing:0.12em;text-transform:uppercase;color:#a16207;">Luki Online</p>
                            <h1 style="margin:0 0 16px;font-size:28px;line-height:1.2;color:#111827;">{{ $headline }}</h1>
                            <p style="margin:0 0 16px;font-size:16px;line-height:1.7;color:#374151;">{{ $messageLine }}</p>
                            @if(filled($reason))
                                <div style="margin:0 0 16px;padding:16px;border-radius:16px;background:#fff7ed;color:#9a3412;">
                                    <strong style="display:block;margin-bottom:6px;">Review note</strong>
                                    <span>{{ $reason }}</span>
                                </div>
                            @endif
                            <p style="margin:0;font-size:14px;line-height:1.6;color:#6b7280;">Open the app to view your latest verification status.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
