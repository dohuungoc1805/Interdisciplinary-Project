<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
</head>
<body style="font-family: system-ui, -apple-system, sans-serif; line-height: 1.5; color: #1a1a1a; max-width: 560px; margin: 0 auto; padding: 24px;">
    <p style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">{{ config('app.name') }}</p>
    <hr style="border: none; border-top: 1px solid #e5e5e5; margin: 16px 0;">
    @yield('content')
    <p style="margin-top: 32px; font-size: 12px; color: #666;">This message was sent from {{ config('app.url') }}.</p>
</body>
</html>
