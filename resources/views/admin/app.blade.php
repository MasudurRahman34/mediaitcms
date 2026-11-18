<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin | {{ config('app.name') }}</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.tsx'])
</head>
<body>
    <div id="admin-root"
         data-user='@json($user->only(['id', 'name', 'email']))'
         data-permissions='@json($permissions)'
         data-logout-url="{{ route('admin.logout') }}"
         data-login-url="{{ route('admin.login') }}"></div>

    <noscript>
        <div style="padding:2rem;color:#f8fafc;font-family:sans-serif;background:#0d1220;min-height:100vh">
            The admin console requires JavaScript. Please enable it to manage content.
        </div>
    </noscript>
</body>
</html>
