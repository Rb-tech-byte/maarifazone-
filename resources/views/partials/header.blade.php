@php
    $siteName = site_name();
    $logoUrl = branding_asset(['primary_logo_path', 'dark_logo_path', 'logos.primary', 'logos.secondary'], 'assets/front/img/logo/maarifazone-logo.png');
    $mainMenu = $frontendMenus['main-navigation'] ?? [];
    $currentUrl = url()->current();
@endphp

<!-- Clean Sticky Header Navigation Start -->
<header id="header-sticky" class="header-1 maarifazone-navbar">
    <div class="container">
        <div class="mega-menu-wrapper">
            <div class="header-main maarifazone-navbar__inner">
                <!-- Left: Fitted Brand Logo -->
                <div class="logo maarifazone-navbar__logo-wrap">
                    <a href="{{ route('home') }}" class="maarifazone-header-brand" aria-label="{{ $siteName }}">
                        <span class="maarifazone-header-brand__badge">
                            <img src="{{ $logoUrl }}" alt="{{ $siteName }} logo" class="maarifazone-header-brand__img">
                        </span>
                    </a>
                </div>

                <!-- Center: Navigation Links -->
                <div class="mean__menu-wrapper maarifazone-navbar__menu-wrap">
                    <div class="main-menu">
                        <nav id="mobile-menu">
                            <ul class="maarifazone-navbar__nav-list">
                                @if (!empty($mainMenu))
                                    @include('partials.menu-items', [
                                        'items' => $mainMenu,
                                        'isRoot' => true,
                                    ])
                                @else
                                    <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">{{ __('frontend.home') }}</a></li>
                                    <li><a href="{{ route('courses') }}" class="{{ request()->routeIs('courses*') ? 'active' : '' }}">{{ __('frontend.courses') }}</a></li>
                                    <li><a href="{{ route('instructors.index') }}" class="{{ request()->routeIs('instructors*') ? 'active' : '' }}">{{ __('frontend.instructors') }}</a></li>
                                    <li><a href="{{ route('bundles.index') }}" class="{{ request()->routeIs('bundles*') ? 'active' : '' }}">Bundles</a></li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                </div>

                <!-- Right: Real-Time Live Search, Locale/Currency, & Auth Actions -->
                <div class="header-right maarifazone-navbar__actions">
                    <div class="maarifazone-live-search" id="maarifazone-live-search" data-endpoint="{{ route('live-search') }}">
                        <form action="{{ route('courses') }}" method="GET" class="maarifazone-live-search__form" role="search">
                            <i class="fa-solid fa-magnifying-glass maarifazone-live-search__icon"></i>
                            <input
                                type="text"
                                name="search"
                                id="maarifazone-header-search-input"
                                class="maarifazone-live-search__input"
                                value="{{ request('search') }}"
                                placeholder="Search courses, instructors..."
                                autocomplete="off"
                                aria-label="Search courses and instructors"
                                aria-expanded="false"
                                aria-controls="maarifazone-live-search-dropdown"
                            >
                            <button type="button" class="maarifazone-live-search__clear d-none" id="maarifazone-live-search-clear" aria-label="Clear search">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                            <kbd class="maarifazone-live-search__kbd" id="maarifazone-live-search-kbd">⌘K</kbd>
                        </form>

                        <!-- Real-Time Results Dropdown -->
                        <div class="maarifazone-live-search__dropdown" id="maarifazone-live-search-dropdown" role="listbox" aria-label="Live search results">
                            <div class="maarifazone-live-search__body" id="maarifazone-live-search-body">
                                <div class="maarifazone-live-search__loading">
                                    <i class="fa-solid fa-circle-notch fa-spin"></i> Searching...
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        $activeCurrencyCode = currency_code();
                    @endphp
                    <div class="maarifazone-navbar__lang d-none d-md-flex" title="Switch currency (1 USD = {{ number_format(usd_to_tzs_rate(), 0) }} TZS)">
                        <i class="fa-solid fa-coins"></i>
                        <select onchange="window.location.href=this.value" aria-label="Currency">
                            @foreach (supported_switch_currencies() as $curCode => $curLabel)
                                <option value="{{ request()->fullUrlWithQuery(['currency' => $curCode]) }}" {{ $curCode === $activeCurrencyCode ? 'selected' : '' }}>
                                    {{ $curCode === 'TZS' ? 'TSH (TZS)' : 'USD ($)' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if (!empty($availableLocales) && count($availableLocales) > 1)
                        <div class="maarifazone-navbar__lang d-none d-md-flex">
                            <i class="fa-solid fa-globe"></i>
                            <select onchange="window.location.href=this.value" aria-label="Language">
                                @foreach ($availableLocales as $code => $meta)
                                    <option value="{{ request()->fullUrlWithQuery(['lang' => $code]) }}" {{ $code === $currentLocale ? 'selected' : '' }}>
                                        {{ strtoupper($code) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <span class="maarifazone-navbar__lang d-none d-lg-inline-flex">
                            <i class="fa-solid fa-globe"></i>
                            <span>{{ strtoupper(app()->getLocale()) }}</span>
                        </span>
                    @endif

                    @if(Auth::check())
                        @php
                            $currentUser = Auth::user();
                            $dashboardRoute = route('home');
                            $dashboardText = __('frontend.dashboard');

                            if ($currentUser->isAdmin()) {
                                $dashboardRoute = route('admin.dashboard');
                                $dashboardText = __('frontend.admin_dashboard');
                            } elseif ($currentUser->isInstructor()) {
                                $dashboardRoute = route('instructor.dashboard');
                                $dashboardText = __('frontend.instructor_dashboard');
                            } elseif ($currentUser->isStudent()) {
                                $dashboardRoute = route('student.dashboard');
                                $dashboardText = __('frontend.my_dashboard');
                            }
                        @endphp

                        @include('partials.notification-dropdown', ['currentUser' => $currentUser])

                        <a href="{{ $dashboardRoute }}" class="maarifazone-navbar__cta-btn">
                            <i class="fa-regular fa-circle-user"></i>
                            <span>{{ $dashboardText }}</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="maarifazone-navbar__cta-btn">
                            <span>{{ __('frontend.login') }}</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    @endif

                    <div class="header__hamburger d-xl-none my-auto">
                        <div class="sidebar__toggle maarifazone-navbar__Burger">
                            <i class="fas fa-bars"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

@include('partials.search')
