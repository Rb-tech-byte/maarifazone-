@extends('layouts.auth')

@php
    $siteName = site_name();
    $pageTitle = 'Login | ' . $siteName;
    $logoUrl = branding_asset(['primary_logo_path', 'dark_logo_path', 'logos.primary'], 'assets/front/img/logo/maarifazone-logo.png');
@endphp

@section('content')
<div class="maarifazone-auth-layout">
    <!-- Left: Maarifazone Brand Showcase Panel -->
    <div class="maarifazone-auth-showcase">
        <!-- Brand Logo -->
        <a href="{{ route('home') }}" class="maarifazone-auth-showcase__brand" aria-label="{{ $siteName }}">
            <span class="maarifazone-auth-showcase__brand-badge">
                <img src="{{ $logoUrl }}" alt="{{ $siteName }} Logo">
            </span>
        </a>

        <!-- Main Showcase Content -->
        <div class="maarifazone-auth-showcase__content">
            <div class="maarifazone-auth-eyebrow">
                <i class="fa-solid fa-graduation-cap"></i>
                <span>100% Online Maarifa &amp; Knowledge</span>
            </div>

            <h1 class="maarifazone-auth-heading">
                Grow your mind with <span>expert-led</span> online courses.
            </h1>

            <p class="maarifazone-auth-desc">
                Join scholars across Tanzania and worldwide. High-definition video lessons, interactive discussions, live masterclasses &mdash; pure knowledge delivery on Maarifazone.
            </p>

            <div class="maarifazone-auth-features">
                <div class="maarifazone-auth-feature-item">
                    <div class="maarifazone-auth-feature-icon">
                        <i class="fa-solid fa-play"></i>
                    </div>
                    <div class="maarifazone-auth-feature-text">
                        <h5>Learn at your own pace</h5>
                        <p>100% online, flexible anytime access to every course you enroll in.</p>
                    </div>
                </div>

                <div class="maarifazone-auth-feature-item">
                    <div class="maarifazone-auth-feature-icon">
                        <i class="fa-solid fa-book-open-reader"></i>
                    </div>
                    <div class="maarifazone-auth-feature-text">
                        <h5>Pure Maarifa Learning</h5>
                        <p>Learn practical, career-defining skills directly from expert instructors.</p>
                    </div>
                </div>

                <div class="maarifazone-auth-feature-item">
                    <div class="maarifazone-auth-feature-icon">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <div class="maarifazone-auth-feature-text">
                        <h5>Interactive Q&amp;A &amp; Live Classes</h5>
                        <p>Engage directly in course discussions, ask questions, and attend live sessions.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Testimonial Quote -->
        <div class="maarifazone-auth-quote-card">
            <p class="maarifazone-auth-quote-text">
                &ldquo;Maarifazone's 100% online platform made it easy for me to master practical software engineering at my own schedule. The video lessons and community are top-notch.&rdquo;
            </p>
            <div class="maarifazone-auth-quote-author">
                <div class="maarifazone-auth-quote-avatar">AJ</div>
                <div>
                    <h6 class="maarifazone-auth-quote-name">Amina Juma</h6>
                    <p class="maarifazone-auth-quote-title">Online Scholar &bull; Dar es Salaam</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Authentication Form Panel -->
    <div class="maarifazone-auth-form-panel">
        <!-- Top Navigation -->
        <div class="maarifazone-auth-topbar">
            <a href="{{ route('home') }}" class="maarifazone-auth-back-link">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to site</span>
            </a>

            <div class="maarifazone-auth-lang-pill">
                <i class="fa-solid fa-globe text-primary"></i>
                <span>English &middot; {{ currency_code() }}</span>
            </div>
        </div>

        <!-- Form Wrapper -->
        <div class="maarifazone-auth-form-container maarifazone-auth-form-container--wide">
            <div class="maarifazone-auth-form-header">
                <h2 class="maarifazone-auth-form-title">Welcome back</h2>
                <p class="maarifazone-auth-form-sub">Login to continue your learning journey.</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success py-2 px-3 mb-3 small" style="border-radius: 9px;">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" id="outLoginForm">
                @csrf

                <!-- Email Input -->
                <div class="maarifazone-form-group">
                    <label for="email" class="maarifazone-form-label">Email address</label>
                    <div class="maarifazone-form-input-wrap">
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            placeholder="name@maarifazone.com"
                            class="maarifazone-form-input @error('email') is-invalid @enderror"
                            required
                            autocomplete="email"
                            autofocus
                        >
                    </div>
                    @error('email')
                        <div class="invalid-feedback d-block mt-1 small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password Input -->
                <div class="maarifazone-form-group">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label for="password" class="maarifazone-form-label mb-0">Password</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="maarifazone-form-forgot-link">Forgot password?</a>
                        @endif
                    </div>
                    <div class="maarifazone-form-input-wrap">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="••••••••"
                            class="maarifazone-form-input @error('password') is-invalid @enderror"
                            required
                            autocomplete="current-password"
                        >
                        <span class="maarifazone-form-toggle-pwd" id="togglePasswordBtn" title="Toggle password visibility">
                            <i class="fa-regular fa-eye"></i>
                        </span>
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block mt-1 small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember Me Checkbox -->
                <div class="maarifazone-form-row">
                    <label class="maarifazone-form-checkbox-label" for="remember">
                        <input type="checkbox" name="remember" id="remember" class="form-check-input mt-0" {{ old('remember') ? 'checked' : '' }}>
                        <span>Keep me signed in</span>
                    </label>
                </div>

                <x-recaptcha-widget class="mb-3" action="login" />

                <!-- Submit Button -->
                <button type="submit" class="maarifazone-auth-submit-btn" id="loginSubmitBtn">
                    <span>Login</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>

                <!-- One-Click Demo Credentials Card (Pure Maarifazone 3-Role Model) -->
                @php
                    $demoAccounts = config('demo_accounts.roles', []);
                @endphp
                @if (!empty($demoAccounts))
                    <div class="out-demo-box">
                        <div class="out-demo-box__header">
                            <span class="out-demo-box__badge"><i class="fa-solid fa-bolt me-1"></i> Quick Demo</span>
                            <h4 class="out-demo-box__title">One-Click Demo Access</h4>
                        </div>
                        <p class="out-demo-box__sub">
                            Click any role below to sign in instantly with one click. Default password: <strong>{{ config('demo_accounts.default_password', 'password') }}</strong>
                        </p>

                        <div class="out-demo-grid">
                            @foreach ($demoAccounts as $account)
                                <button
                                    type="button"
                                    class="out-demo-btn out-demo-btn--{{ $account['role'] }}"
                                    data-email="{{ $account['email'] }}"
                                    data-password="{{ $account['password'] ?? 'password' }}"
                                    data-role="{{ $account['label'] }}"
                                    data-scope="{{ $account['badge'] ?? $account['label'] }}"
                                    data-scope-type="{{ $account['scope_type'] ?? $account['role'] }}"
                                    data-allowed="{{ $account['allowed_modules'] ?? '' }}"
                                    data-restricted="{{ $account['restricted_from'] ?? '' }}"
                                    title="Click to sign in instantly as {{ $account['label'] }} ({{ $account['email'] }})"
                                >
                                    <span class="out-demo-btn__icon out-demo-btn__icon--{{ $account['role'] }}">
                                        <i class="{{ $account['icon'] }}"></i>
                                    </span>
                                    <span class="out-demo-btn__text">
                                        <span class="out-demo-btn__top">
                                            <strong class="out-demo-btn__role">{{ $account['label'] }}</strong>
                                            <span class="out-demo-scope out-demo-scope--{{ $account['role'] }}">{{ $account['badge'] ?? ucfirst($account['role']) }}</span>
                                        </span>
                                        <span class="out-demo-btn__name">{{ $account['name'] }}</span>
                                        <span class="out-demo-btn__email">{{ $account['email'] }}</span>
                                        <span class="out-demo-btn__action">
                                            <i class="fa-solid fa-bolt"></i> 1-Click Sign In
                                        </span>
                                    </span>
                                </button>
                            @endforeach
                        </div>

                        <div class="out-demo-inspector" id="demoRbacInspector">
                            <div class="out-demo-inspector__head">
                                <span class="out-demo-inspector__title" id="inspectorRoleTitle">Maarifazone 3-Role Access Profile</span>
                                <span class="out-demo-scope out-demo-scope--admin" id="inspectorScopeBadge">Instant Sign-In Ready</span>
                            </div>
                            <div class="out-demo-inspector__meta">
                                <div><strong>Selected Account:</strong> <code id="inspectorEmail">admin@maarifazone.com</code> &bull; Password: <code>{{ config('demo_accounts.default_password', 'password') }}</code></div>
                                <div><strong>Access Scope:</strong> <span id="inspectorAllowed">Course Authoring &amp; Assignment, Instructors, Scholars, Financials, Platform Settings</span></div>
                                <div><strong>Role Isolation:</strong> <span id="inspectorRestricted">Admins manage the entire system; Instructors manage assigned courses; Scholars stream online lessons.</span></div>
                            </div>
                        </div>
                    </div>
                @endif
            </form>
        </div>

        <!-- Panel Footer -->
        <div class="maarifazone-auth-form-footer">
            <p class="maarifazone-auth-switch-text">
                Don't have an account?
                <a href="{{ route('register') }}" class="maarifazone-auth-switch-link">Create one free</a>
            </p>
            <p class="maarifazone-auth-copy-text">
                &copy; {{ date('Y') }} Maarifazone. All rights reserved.
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        setupPasswordToggle('#password', '#togglePasswordBtn');

        const form = document.getElementById('outLoginForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const submitBtn = document.getElementById('loginSubmitBtn');
        const demoButtons = document.querySelectorAll('.out-demo-btn');
        const inspectorRoleTitle = document.getElementById('inspectorRoleTitle');
        const inspectorScopeBadge = document.getElementById('inspectorScopeBadge');
        const inspectorEmail = document.getElementById('inspectorEmail');
        const inspectorAllowed = document.getElementById('inspectorAllowed');
        const inspectorRestricted = document.getElementById('inspectorRestricted');

        function updateInspector(btn) {
            if (!btn || !inspectorRoleTitle) {
                return;
            }
            const scopeType = btn.getAttribute('data-scope-type') || 'admin';
            inspectorRoleTitle.textContent = btn.getAttribute('data-role') + ' — Access Profile';
            inspectorScopeBadge.textContent = btn.getAttribute('data-scope') || 'Active';
            inspectorScopeBadge.className = 'out-demo-scope out-demo-scope--' + scopeType;
            inspectorEmail.textContent = btn.getAttribute('data-email') || '';
            inspectorAllowed.textContent = btn.getAttribute('data-allowed') || '';
            inspectorRestricted.textContent = btn.getAttribute('data-restricted') || '';
        }

        // One-Click Credential Autofill, Inspection & Instant Login
        demoButtons.forEach(btn => {
            btn.addEventListener('mouseenter', function () {
                updateInspector(this);
            });
            btn.addEventListener('focus', function () {
                updateInspector(this);
            });
            btn.addEventListener('click', function () {
                demoButtons.forEach(b => b.classList.remove('is-active'));
                this.classList.add('is-active');
                updateInspector(this);

                const email = this.getAttribute('data-email');
                const password = this.getAttribute('data-password');
                const role = this.getAttribute('data-role');

                emailInput.value = email;
                passwordInput.value = password;

                // Visual feedback on button
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span>Signing in as ${role}...</span> <i class="fa-solid fa-circle-notch fa-spin"></i>`;

                // Instant submission
                setTimeout(() => {
                    form.submit();
                }, 180);
            });
        });

        // Form submit feedback
        if (form && submitBtn) {
            form.addEventListener('submit', function () {
                if (!submitBtn.disabled) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `<span>Signing in...</span> <i class="fa-solid fa-circle-notch fa-spin"></i>`;
                }
            });
        }
    });
</script>
@endpush
