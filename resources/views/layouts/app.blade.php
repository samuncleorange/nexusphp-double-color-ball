<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Double Color Ball') - {{ get_setting('base.site_name') }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/dcb/css/style.css') }}">
    <style>
        /* Shared layout styles */
        body {
            padding-bottom: 50px;
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
        @yield('content')
    </div>

    <script src="{{ asset('vendor/dcb/js/app.js') }}"></script>
    <script>
        // Global initialization or shared logic
    </script>
    @yield('scripts')
</body>
</html>
