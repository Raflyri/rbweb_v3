@php
    $errorCode   = '500';
    $title       = 'Internal Server Error';
    $description = config('app.debug')
        ? 'An unhandled exception occurred on the server. See the debug panel below for exception details.'
        : 'Something went wrong on our end. Our team has been notified. Please try again in a few minutes.';
    $errorIcon   = '<path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>';
    $hideBack    = false;
    $hideContact = false;
    {{-- $exception is automatically injected by Laravel's error handler when APP_DEBUG=true --}}
    {{-- No need to set $debugNote — exception panel will show class + message + file --}}
@endphp

@include('errors.layout')
