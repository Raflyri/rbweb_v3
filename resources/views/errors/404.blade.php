@php
    $errorCode   = '404';
    $title       = 'Page Not Found';
    $description = config('app.debug')
        ? 'The requested route could not be matched. Double-check the URL or inspect the route list below.'
        : 'The page you\'re looking for doesn\'t exist or has been moved. Try navigating back to the homepage.';
    $errorIcon   = '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>';
    $debugNote   = 'No route matched: ' . request()->method() . ' ' . request()->path() . ' — run `php artisan route:list` to inspect.';
    $hideBack    = false;
    $hideContact = false;
@endphp

@include('errors.layout')
