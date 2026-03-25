@php
    $errorCode   = '403';
    $title       = 'Access Forbidden';
    $description = config('app.debug')
        ? 'You do not have permission to access this resource. Verify your role, policies, or authentication status below.'
        : 'You don\'t have permission to access this page. If you believe this is a mistake, please contact support.';
    $errorIcon   = '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>';
    $debugNote   = 'Authorization check failed. Ensure the user has the correct role / permission in Spatie.';
    $hideBack    = false;
    $hideContact = false;
@endphp

@include('errors.layout')
