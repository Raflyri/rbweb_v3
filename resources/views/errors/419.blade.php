@php
    $errorCode   = '419';
    $title       = 'Session Expired';
    $description = config('app.debug')
        ? 'The CSRF token for this request is missing or has expired. This usually happens after a long idle period.'
        : 'Your session has expired or the security token is invalid. Please refresh the page and try again.';
    $errorIcon   = '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>';
    $debugNote   = 'CSRF token mismatch. Check SESSION_DRIVER, SESSION_LIFETIME, and that the form has @csrf.';
    $hideBack    = false;
    $hideContact = false;
@endphp

@php $hideBack = false; @endphp

{{-- Override: show "Refresh" as primary action for 419 --}}
@php
    // We'll use the standard layout but add a refresh hint via debugNote
@endphp

@include('errors.layout')

{{-- Append: auto-suggest page refresh --}}
<script>
    // Auto-add a "Refresh Page" button for 419
    (function() {
        var actions = document.querySelector('.rb-err-actions');
        if (!actions) return;
        var btn = document.createElement('a');
        btn.href = window.location.href;
        btn.className = 'rb-err-btn-primary';
        btn.setAttribute('aria-label', 'Refresh the current page');
        btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>Refresh Page';
        actions.insertBefore(btn, actions.firstChild);
    })();
</script>
