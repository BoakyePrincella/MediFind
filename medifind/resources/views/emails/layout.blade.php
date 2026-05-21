<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $title ?? config('app.name') }}</title>
    <style>
        body,
        table,
        td,
        a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table,
        td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
            border-collapse: separate;
        }

        a {
            word-break: break-word;
        }

        .email-body {
            overflow-wrap: break-word;
            word-break: break-word;
        }

        @media only screen and (max-width: 640px) {
            .email-shell {
                padding: 16px 8px !important;
            }

            .email-container {
                width: 100% !important;
                max-width: 100% !important;
                border-radius: 0 !important;
            }

            .email-header {
                padding: 22px 20px 6px !important;
            }

            .email-heading {
                font-size: 22px !important;
                line-height: 29px !important;
            }

            .email-body {
                padding: 8px 20px 26px !important;
                font-size: 16px !important;
                line-height: 25px !important;
            }

            .email-button {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box !important;
                text-align: center !important;
            }

            .detail-label,
            .detail-value {
                display: block !important;
                width: auto !important;
                padding: 12px 14px !important;
            }

            .detail-value {
                padding-top: 0 !important;
            }

            .detail-value-bordered {
                border-top: 0 !important;
            }
        }
    </style>
</head>
<body style="margin:0; padding:0; background:#f4f7fb; color:#14213d; font-family:Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" class="email-shell" style="width:100%; background:#f4f7fb; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="620" cellspacing="0" cellpadding="0" class="email-container" style="width:100%; max-width:620px; background:#ffffff; border-radius:8px; overflow:hidden; border:1px solid #e5eaf1;">
                    <tr>
                        <td class="email-header" style="padding:28px 32px 8px;">
                            <h1 class="email-heading" style="margin:0; color:#102a43; font-size:24px; line-height:32px;">{{ $heading }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td class="email-body" style="padding:8px 32px 32px; font-size:15px; line-height:24px;">
                            @yield('content')
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
