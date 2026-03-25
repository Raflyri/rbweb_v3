@php
    $errorCode   = '429';
    $title       = 'Too Many Requests';
    $description = config('app.debug')
        ? 'The rate limit for this endpoint has been exceeded. Check your throttle middleware and retry-after header.'
        : 'You\'ve sent too many requests in a short period. Please wait a moment before trying again.';
    $errorIcon   = '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>';
    $debugNote   = 'Rate limit triggered. Inspect the ThrottleRequests middleware and the X-RateLimit-* headers.';
    $hideBack    = false;
    $hideContact = false;
@endphp

@include('errors.layout')

<script>
    // Show a live countdown using the Retry-After header if available
    (function() {
        // Laravel injects retry seconds via the exception — it's not available client-side
        // so we show a generic 60s countdown as a UX affordance
        var retryAfter = 60;
        var desc = document.querySelector('.rb-err-desc');
        if (!desc) return;

        var span = document.createElement('span');
        span.style.cssText = 'display:block;margin-top:0.5rem;font-family:JetBrains Mono,monospace;font-size:0.8rem;color:rgba(220,38,38,0.7);';

        function tick() {
            if (retryAfter <= 0) {
                span.textContent = 'You can retry now.';
                clearInterval(timer);
                return;
            }
            span.textContent = 'Retry available in ' + retryAfter + 's';
            retryAfter--;
        }

        tick();
        var timer = setInterval(tick, 1000);
        desc.appendChild(span);
    })();
</script>
