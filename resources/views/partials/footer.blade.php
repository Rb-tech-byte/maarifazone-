@php
    $siteName = site_name();
    $logoUrl = branding_asset(['footer_logo_path', 'dark_logo_path', 'logos.footer', 'primary_logo_path', 'logos.primary'], 'assets/front/img/logo/maarifazone-logo-dark.png');
    $footerLinks = $frontendMenus['footer-links'] ?? [];
    $quickLinks = $frontendMenus['quick-links'] ?? [];
    $aboutText = $footerBranding['about_text'] ?? 'Maarifazone connects learners with expert instructors through engaging online and blended learning experiences.';
    $copyrightText = $footerBranding['copyright'] ?? ('© ' . date('Y') . ' ' . $siteName . '. All rights reserved.');
    $contactEmail = $footerContact['email'] ?? 'info@maarifazone.com';
    $contactPhone = $footerContact['phone'] ?? '+255 700 000 000';
    $contactAddress = $footerContact['address'] ?? 'Dar es Salaam, Tanzania';
    $contactHours = $footerContact['hours'] ?? 'Mon - Fri: 8:00am - 5:00pm EAT';
@endphp

<footer class="maarifazone-brand-footer">
    <!-- Maarifazone Vibrant Orange Smile-Inspired Top Wave -->
    <div class="maarifazone-brand-footer__top-shape" aria-hidden="true">
        <svg viewBox="0 0 1440 96" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M0 72C260 98 620 12 1040 28C1230 35 1365 58 1440 76V96H0V72Z" fill="#f99f1e" fill-opacity="0.24"/>
            <path d="M180 64C480 18 920 8 1440 58V96H0V82C60 78 115 72 180 64Z" fill="#f99f1e" fill-opacity="0.9"/>
            <path d="M210 74C520 28 940 20 1440 68V96H0V88C70 85 135 81 210 74Z" fill="#0b1120"/>
        </svg>
        <!-- Signature Vibrant Orange Dot & Smile Accent echoing the Maarifa logo -->
        <div class="maarifazone-brand-footer__sun-orb"></div>
        <div class="maarifazone-brand-footer__arc-ring"></div>
    </div>

    <!-- Decorative Watermark of the Maarifazone Geometric 'M' + Vibrant Orange Dot & Smile -->
    <svg class="maarifazone-brand-footer__watermark" viewBox="0 0 320 320" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <!-- Geometric 'M' Monogram -->
        <path d="M58 210V92H92L160 166L228 92H262V210H230V136L168 202H152L90 136V210H58Z" fill="#ffffff" fill-opacity="0.05"/>
        <!-- Signature Vibrant Orange Dot -->
        <circle cx="256" cy="64" r="22" fill="#f99f1e" fill-opacity="0.18"/>
        <!-- Signature Vibrant Orange Smile Arc -->
        <path d="M68 240C122 276 198 276 252 240" stroke="#f99f1e" stroke-width="18" stroke-linecap="round" stroke-opacity="0.18"/>
    </svg>

    <div class="container position-relative" style="z-index: 2;">
        <!-- Floating Newsletter Banner -->
        <div class="maarifazone-brand-footer__newsletter">
            <div class="maarifazone-brand-footer__newsletter-badge" aria-hidden="true">
                <span class="maarifazone-brand-footer__newsletter-dot"></span>
            </div>
            <div class="maarifazone-brand-footer__newsletter-text">
                <h3 class="maarifazone-brand-footer__newsletter-title">Stay in the loop</h3>
                <p class="maarifazone-brand-footer__newsletter-desc">
                    Get new {{ $siteName }} courses, special offers, and learning tips straight to your inbox.
                </p>
            </div>
            <form action="{{ route('newsletter.subscribe') }}" method="POST" class="maarifazone-brand-footer__newsletter-form">
                @csrf
                <input type="email" name="email" class="maarifazone-brand-footer__newsletter-input" placeholder="Your email address" required>
                <button type="submit" class="maarifazone-brand-footer__newsletter-btn">
                    Subscribe <i class="fa-solid fa-arrow-right ms-1"></i>
                </button>
            </form>
        </div>

        <!-- Main 4-Column Footer Grid -->
        <div class="row g-4 justify-content-between maarifazone-brand-footer__main">
            <!-- Column 1: Brand Wordmark + About & Highlights -->
            <div class="col-xl-4 col-lg-4 col-md-6">
                <div class="maarifazone-brand-footer__brand">
                    <a href="{{ route('home') }}" class="maarifazone-brand-footer__logo-link" aria-label="{{ $siteName }}">
                        <span class="maarifazone-brand-footer__logo-emblem">
                            <img src="{{ $logoUrl }}" alt="{{ $siteName }} logo">
                        </span>
                    </a>

                    <p class="maarifazone-brand-footer__about mb-3">
                        {{ $aboutText }}
                    </p>

                    <ul class="maarifazone-brand-footer__identity-list">
                        <li><span class="maarifazone-id-dot"></span>Self-paced 100% online learning</li>
                        <li><span class="maarifazone-id-dot"></span>Expert-led courses, multimedia lessons &amp; live sessions</li>
                        <li><span class="maarifazone-id-dot"></span>Practical hands-on knowledge &amp; modular topics</li>
                        <li><span class="maarifazone-id-dot"></span>Accessible learning across every region</li>
                    </ul>
                </div>
            </div>

            <!-- Column 2: Explore -->
            <div class="col-xl-2 col-lg-2 col-md-6">
                <h4 class="maarifazone-brand-footer__heading">Explore</h4>
                <ul class="maarifazone-brand-footer__links">
                    @if (!empty($footerLinks))
                        @foreach ($footerLinks as $link)
                            <li>
                                <a href="{{ $link['url'] }}" target="{{ $link['target'] ?? '_self' }}">
                                    {{ $link['title'] }} <i class="fa-solid fa-arrow-right maarifazone-ext-icon"></i>
                                </a>
                            </li>
                        @endforeach
                    @else
                        <li><a href="{{ route('courses') }}">Browse Courses <i class="fa-solid fa-arrow-right maarifazone-ext-icon"></i></a></li>
                        <li><a href="{{ route('bundles.index') }}">Course Bundles <i class="fa-solid fa-arrow-right maarifazone-ext-icon"></i></a></li>
                        <li><a href="{{ route('instructors.index') }}">Instructors <i class="fa-solid fa-arrow-right maarifazone-ext-icon"></i></a></li>
                        <li><a href="{{ route('login') }}">Portal Login <i class="fa-solid fa-arrow-right maarifazone-ext-icon"></i></a></li>
                        <li><a href="{{ route('register') }}">Create Account <i class="fa-solid fa-arrow-right maarifazone-ext-icon"></i></a></li>
                    @endif
                </ul>
            </div>

            <!-- Column 3: Resources -->
            <div class="col-xl-3 col-lg-3 col-md-6">
                <h4 class="maarifazone-brand-footer__heading">Resources</h4>
                <ul class="maarifazone-brand-footer__links">
                    @if (!empty($quickLinks))
                        @foreach ($quickLinks as $link)
                            <li>
                                <a href="{{ $link['url'] }}" target="{{ $link['target'] ?? '_self' }}">
                                    {{ $link['title'] }} <i class="fa-solid fa-arrow-right maarifazone-ext-icon"></i>
                                </a>
                            </li>
                        @endforeach
                    @else
                        <li><a href="{{ route('about') }}">About {{ $siteName }} <i class="fa-solid fa-arrow-right maarifazone-ext-icon"></i></a></li>
                        <li><a href="{{ route('contact') }}">Contact &amp; Support <i class="fa-solid fa-arrow-right maarifazone-ext-icon"></i></a></li>
                        <li><a href="{{ url('/terms-conditions') }}">Terms &amp; Conditions <i class="fa-solid fa-arrow-right maarifazone-ext-icon"></i></a></li>
                        <li><a href="{{ route('register') }}">Become a Scholar <i class="fa-solid fa-arrow-right maarifazone-ext-icon"></i></a></li>
                    @endif
                </ul>
            </div>

            <!-- Column 4: Contact Us -->
            <div class="col-xl-3 col-lg-3 col-md-6">
                <h4 class="maarifazone-brand-footer__heading">Contact Us</h4>
                <ul class="maarifazone-brand-footer__contact">
                    <li>
                        <i class="fa-solid fa-building"></i>
                        <strong class="text-white">{{ $siteName }}</strong>
                    </li>
                    @if (filled($contactAddress))
                        <li>
                            <i class="fa-solid fa-location-dot"></i>
                            <span>{{ $contactAddress }}</span>
                        </li>
                    @endif
                    @if (filled($contactPhone))
                        <li>
                            <i class="fa-solid fa-phone"></i>
                            <a href="tel:{{ preg_replace('/[^\d+]/', '', $contactPhone) }}">{{ $contactPhone }}</a>
                        </li>
                    @endif
                    @if (filled($contactEmail))
                        <li>
                            <i class="fa-regular fa-envelope"></i>
                            <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>
                        </li>
                    @endif
                    @if (filled($contactHours))
                        <li>
                            <i class="fa-regular fa-clock"></i>
                            <span>{{ $contactHours }}</span>
                        </li>
                    @endif
                </ul>

                <div class="maarifazone-brand-footer__socials mt-3">
                    @if (!empty($footerSocialLinks))
                        @foreach ($footerSocialLinks as $social)
                            <a href="{{ $social['url'] }}" target="_blank" rel="noopener" aria-label="{{ $social['label'] ?? 'Social Link' }}">
                                <i class="{{ $social['icon'] ?? 'fab fa-link' }}"></i>
                            </a>
                        @endforeach
                    @else
                        <a href="{{ route('home') }}" aria-label="Website"><i class="fa-solid fa-globe"></i></a>
                        <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Bottom Copyright Bar -->
        <div class="maarifazone-brand-footer__bottom">
            <p class="maarifazone-brand-footer__copy">
                {{ $copyrightText }}
            </p>
            <div class="maarifazone-brand-footer__bottom-links">
                <a href="{{ route('courses') }}">Courses</a>
                <a href="{{ url('/terms-conditions') }}">Terms &amp; Privacy</a>
                <a href="{{ route('contact') }}">Contact</a>
            </div>
        </div>
    </div>
</footer>
