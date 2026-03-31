{{--
    Render hook wrapper for the email verification banner.
    Registered via PanelsRenderHook::CONTENT_START in ClientAreaPanelProvider.
    Only renders for authenticated users — guests see nothing.
--}}
@auth
    <div class="px-4 pb-0 pt-4 sm:px-6">
        @livewire('resend-verification-email')
    </div>
@endauth
