@extends('emails.layout', [
    'title' => 'Reset your password',
    'heading' => 'Reset your password',
])

@section('content')
    <p style="margin:0 0 16px;">Hello {{ $user->fullname }},</p>

    <p style="margin:0 0 20px;">We received a request to reset your password. Click the button below to choose a new one.</p>

    <p style="margin:24px 0;">
        <a href="{{ $resetUrl }}" class="email-button" style="display:inline-block; background:#1f7a8c; color:#ffffff; text-decoration:none; padding:12px 18px; border-radius:6px; font-weight:bold;">Reset password</a>
    </p>

    <p style="margin:0 0 12px;">If you did not request this, you can ignore this email.</p>

    <p style="margin:0; color:#627d98; overflow-wrap:break-word; word-break:break-word;">If the button does not work, copy and open this link: {{ $resetUrl }}</p>
@endsection
