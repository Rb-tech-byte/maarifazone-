@php
    $portalFooterEmail = frontend_setting('footer.contact.email') ?: 'info@maarifazone.com';
    $portalFooterVersion = platform_settings('system_version', '3.0.0');
@endphp
<footer class="maarifazone-portal-footer">
    <div class="maarifazone-portal-footer-inner">
        <div>
            <span>&copy; {{ date('Y') }} {{ site_name() }}. All rights reserved.</span>
        </div>
        <div class="maarifazone-portal-footer-links d-flex align-items-center gap-3">
            <span class="badge bg-light text-secondary border">v{{ $portalFooterVersion }}</span>
            <a href="mailto:{{ $portalFooterEmail }}"><i class="fa-solid fa-envelope me-1"></i> {{ $portalFooterEmail }}</a>
            <span class="text-muted d-none d-md-inline">&bull;</span>
            <a href="{{ route('about') }}" target="_blank" class="d-none d-md-inline">About {{ site_name() }}</a>
            <span class="text-muted d-none d-md-inline">&bull;</span>
            <a href="{{ route('contact') }}" target="_blank" class="d-none d-md-inline">Contact</a>
        </div>
    </div>
</footer>
