@extends('emails.layout', [
    'title' => 'Your shop owner account is ready',
    'heading' => 'Your shop owner account is ready',
])

@section('content')
    <p style="margin:0 0 16px;">Hello {{ $owner->fullname }},</p>

    <p style="margin:0 0 16px;">Your shop owner account has been created. Use the details below to log in.</p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="width:100%; margin:18px 0; background:#f8fafc; border:1px solid #e5eaf1; border-radius:8px;">
        <tr>
            <td class="detail-label" style="padding:14px 16px; color:#52606d; width:150px;">Email</td>
            <td class="detail-value" style="padding:14px 16px; color:#102a43; font-weight:bold; overflow-wrap:break-word; word-break:break-word;">{{ $owner->email }}</td>
        </tr>
        <tr>
            <td class="detail-label" style="padding:14px 16px; color:#52606d; border-top:1px solid #e5eaf1;">Temporary password</td>
            <td class="detail-value detail-value-bordered" style="padding:14px 16px; color:#102a43; font-weight:bold; border-top:1px solid #e5eaf1; overflow-wrap:break-word; word-break:break-word;">{{ $temporaryPassword }}</td>
        </tr>
    </table>

    <p style="margin:24px 0;">
        <a href="{{ $loginUrl }}" class="email-button" style="display:inline-block; background:#1f7a8c; color:#ffffff; text-decoration:none; padding:12px 18px; border-radius:6px; font-weight:bold;">Log in</a>
    </p>

    <p style="margin:0 0 12px;">You can reset your password now if you want to use your own password.</p>

    <p style="margin:0 0 24px;">
        <a href="{{ $resetUrl }}" class="email-button" style="display:inline-block; background:#334e68; color:#ffffff; text-decoration:none; padding:12px 18px; border-radius:6px; font-weight:bold;">Reset password</a>
    </p>

    <p style="margin:0; color:#627d98; overflow-wrap:break-word; word-break:break-word;">If the buttons do not work, copy and open this reset link: {{ $resetUrl }}</p>
@endsection
