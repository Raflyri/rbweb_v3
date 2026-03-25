@php
    $errorCode   = '503';
    $title       = 'Under Maintenance';
    $description = config('app.debug')
        ? 'The application is currently in maintenance mode (php artisan down). Run `php artisan up` to restore service.'
        : 'We\'re performing scheduled maintenance to improve your experience. We\'ll be back shortly — thank you for your patience.';
    $errorIcon   = '<path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>';
    $hideBack    = true;
    $hideContact = false;
    $debugNote   = 'Maintenance mode active. Secret: ' . (config('app.key') ? '(set)' : '(not set)') . '. Run `php artisan up` to exit.';
@endphp

@include('errors.layout')

{{-- 503-specific: Animated maintenance indicator --}}
<style>
    /* Replace the standard code glow with an amber tint for maintenance */
    .rb-err-code {
        background: linear-gradient(135deg, #D97706 0%, #92400E 60%, #78350F 100%) !important;
        -webkit-background-clip: text !important;
        background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
    }
    .rb-err-code::after {
        background: linear-gradient(135deg, #D97706 0%, #92400E 60%, #78350F 100%) !important;
        -webkit-background-clip: text !important;
        background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
    }
    .rb-err-icon-wrap {
        background: rgba(217,119,6,0.07) !important;
        border-color: rgba(217,119,6,0.3) !important;
    }
    .rb-err-icon-wrap svg { stroke: #D97706 !important; }
    .rb-err-btn-primary {
        border-color: rgba(217,119,6,0.35) !important;
        color: #D97706 !important;
    }
    .rb-err-btn-primary:hover {
        border-color: #D97706 !important;
        box-shadow: 0 0 14px rgba(180,83,9,0.35) !important;
        background: rgba(217,119,6,0.06) !important;
        color: #F5F5F5 !important;
    }
    .rb-err-divider {
        background: linear-gradient(to right, transparent, rgba(217,119,6,0.35), transparent) !important;
    }
    .rb-err-footer {
        border-top-color: rgba(217,119,6,0.06) !important;
    }
</style>

<script>
    // Show a rotating gear animation on the icon
    (function() {
        var icon = document.querySelector('.rb-err-icon-wrap svg');
        if (icon) {
            icon.style.animation = 'rb-err-spin 4s linear infinite';
            var style = document.createElement('style');
            style.textContent = '@keyframes rb-err-spin { to { transform: rotate(360deg); } }';
            document.head.appendChild(style);
        }

        // Real-time clock to show "we're working on it" timeframe
        @if(!config('app.debug'))
        var desc = document.querySelector('.rb-err-desc');
        if (desc) {
            var clock = document.createElement('span');
            clock.style.cssText = 'display:block;margin-top:0.5rem;font-family:JetBrains Mono,monospace;font-size:0.78rem;color:rgba(217,119,6,0.65);';
            function updateClock() {
                var now = new Date();
                clock.textContent = 'Current time: ' + now.toUTCString();
            }
            updateClock();
            setInterval(updateClock, 1000);
            desc.appendChild(clock);
        }
        @endif
    })();
</script>
