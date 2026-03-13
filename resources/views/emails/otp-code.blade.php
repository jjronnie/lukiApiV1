<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $headline }}</title>
</head>

<body style="margin:0; padding:0; background-color:#ffffff; font-family:Arial, Helvetica, sans-serif; color:#000000;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#ffffff; margin:0; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px; background-color:#ffffff; border-collapse:separate; border-spacing:0; border:1px solid #e5e7eb;">
                    <tr>
                        <td style="padding:34px 34px 22px 34px;">
                            <div style="font-size:16px; line-height:24px; font-weight:700; color:#000000; margin:0 0 18px 0;">
                                {{ $headline }}
                            </div>

                            <div style="font-size:15px; line-height:24px; color:#000000; margin:0 0 30px 0;">
                                {{ $intro }}
                            </div>

                            <table role="presentation" cellspacing="0" cellpadding="0" align="center" style="margin:0 auto 26px auto;">
                                <tr>
                                    <td style="border:2px solid #111111; border-radius:6px; background-color:#ffffff; padding:18px 28px; text-align:center;">
                                        <span style="display:inline-block; font-size:24px; line-height:24px; letter-spacing:10px; font-weight:700; color:#000000;">
                                            {{ $code }}
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#ffffff; border-left:4px solid #111111; margin:0 0 26px 0; border-top:1px solid #e5e7eb; border-right:1px solid #e5e7eb; border-bottom:1px solid #e5e7eb;">
                                <tr>
                                    <td style="padding:16px 18px; font-size:14px; line-height:22px; color:#000000;">
                                        This code expires in {{ $expiresInMinutes }} minutes ({{ $expiresAt->format('D, M j, Y g:i A') }}).
                                    </td>
                                </tr>
                            </table>

                            <div style="font-size:14px; line-height:24px; color:#000000; margin:0 0 18px 0;">
                                If you did not request this code, you can safely ignore this email.<br>
                                Never share this code with anyone.
                            </div>

                            <div style="font-size:14px; line-height:24px; color:#000000;">
                                Thanks,<br>
                                {{ $appName }}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
