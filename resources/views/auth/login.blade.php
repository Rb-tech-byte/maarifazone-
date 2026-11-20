@extends('layouts.auth')

@php
    $siteName = site_name();
    $pageTitle = 'Login | ' . $siteName;
    $logoUrl = branding_asset(['dark_logo_path', 'footer_logo_path', 'primary_logo_path', 'logos.primary'], 'assets/front/img/logo/maarifazone-logo-dark.png');
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
                <i class="fa-solid fa-sparkles"></i>
                <span>Open &amp; Online Learning for All</span>
            </div>

            <h1 class="maarifazone-auth-heading">
                Grow your skills with <span>expert-led</span> courses.
            </h1>

            <p class="maarifazone-auth-desc">
                Join learners across Tanzania and worldwide. Video lessons, quizzes, live classes, verified certificates &mdash; everything in one place.
            </p>

            <div class="maarifazone-auth-features">
                <div class="maarifazone-auth-feature-item">
                    <div class="maarifazone-auth-feature-icon">
                        <i class="fa-solid fa-play"></i>
                    </div>
                    <div class="maarifazone-auth-feature-text">
                        <h5>Learn at your own pace</h5>
                        <p>Flexible online and blended access to every course you enroll in.</p>
                    </div>
                </div>

                <div class="maarifazone-auth-feature-item">
                    <div class="maarifazone-auth-feature-icon">
                        <i class="fa-solid fa-award"></i>
                    </div>
                    <div class="maarifazone-auth-feature-text">
                        <h5>Earn verified certificates</h5>
                        <p>Share your academic achievements with QR-verifiable credentials from Maarifazone.</p>
                    </div>
                </div>

                <div class="maarifazone-auth-feature-item">
                    <div class="maarifazone-auth-feature-icon">
                        <i class="fa-solid fa-user-group"></i>
                    </div>
                    <div class="maarifazone-auth-feature-text">
                        <h5>Learn together</h5>
                        <p>Live classes, discussions, and a supportive pan-African community.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Testimonial Quote -->
        <div class="maarifazone-auth-quote-card">
            <p class="maarifazone-auth-quote-text">
                &ldquo;The Maarifazone blended learning model allowed me to balance my career and professional studies seamlessly. The instructors and course materials are exceptional.&rdquo;
            </p>
            <div class="maarifazone-auth-quote-author">
                <div class="maarifazone-auth-quote-avatar">AJ</div>
                <div>
                    <h6 class="maarifazone-auth-quote-name">Amina Juma</h6>
                    <p class="maarifazone-auth-quote-title">Software Engineer &bull; Dar es Salaam</p>
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

                <!-- One-Click Demo Credentials Card (All Roles, RBAC & Isolation Details) -->
                @php
                    $demoRoles = collect(config('demo_accounts.roles', []));
                    $demoGroups = [
                        'global' => [
                            'title' => 'Global & Cross-Branch Governance',
                            'subtitle' => 'Access across all 17 centers via crossBranchRoles()',
                        ],
                        'branch' => [
                            'title' => 'Branch-Isolated Staff',
                            'subtitle' => 'Filtered by assigned center via BranchScoped + RBAC',
                        ],
                        'portal' => [
                            'title' => 'Academic & Learner Portals',
                            'subtitle' => 'Portal middleware isolation (/instructor/* & /student/*)',
                        ],
                    ];
                @endphp
                <div class="out-demo-box">
                    <div class="out-demo-box__header">
                        <span class="out-demo-box__badge">DEMO RBAC</span>
                        <h4 class="out-demo-box__title">One-Click Login Across All {{ $demoRoles->count() }} Roles</h4>
                    </div>
                    <p class="out-demo-box__sub">
                        Click any role below to sign in instantly (Password: <strong>{{ config('demo_accounts.default_password', 'password') }}</strong>) or hover/focus to inspect RBAC &amp; isolation rules.
                    </p>

                    <div class="out-demo-tabs" role="tablist" aria-label="Filter demo roles by isolation scope">
                        <button type="button" class="out-demo-tab is-active" data-filter="all">All ({{ $demoRoles->count() }})</button>
                        <button type="button" class="out-demo-tab" data-filter="global">Global / Cross-Branch ({{ $demoRoles->where('group', 'global')->count() }})</button>
                        <button type="button" class="out-demo-tab" data-filter="branch">Branch-Isolated ({{ $demoRoles->where('group', 'branch')->count() }})</button>
                        <button type="button" class="out-demo-tab" data-filter="portal">Academic Portals ({{ $demoRoles->where('group', 'portal')->count() }})</button>
                    </div>

                    @foreach ($demoGroups as $groupKey => $groupMeta)
                        @php $groupRoles = $demoRoles->where('group', $groupKey); @endphp
                        @if ($groupRoles->isNotEmpty())
                            <div class="out-demo-group" data-group-section="{{ $groupKey }}">
                                <div class="out-demo-group-label">
                                    <span>{{ $groupMeta['title'] }}</span>
                                    <span style="font-weight: 600; text-transform: none; letter-spacing: 0; color: #92400e;">{{ $groupMeta['subtitle'] }}</span>
                                </div>
                                <div class="out-demo-grid">
                                    @foreach ($groupRoles as $item)
                                        <button
                                            type="button"
                                            class="out-demo-btn"
                                            data-email="{{ $item['email'] }}"
                                            data-password="{{ $item['password'] }}"
                                            data-role="{{ $item['label'] }}"
                                            data-scope="{{ $item['scope_badge'] }}"
                                            data-scope-type="{{ $item['scope_type'] }}"
                                            data-allowed="{{ $item['allowed_modules'] }}"
                                            data-restricted="{{ $item['restricted_from'] }}"
                                            title="{{ $item['label'] }} ({{ $item['email'] }}) — {{ $item['scope_badge'] }}. Allowed: {{ $item['allowed_modules'] }}. {{ $item['restricted_from'] }}"
                                        >
                                            <span class="out-demo-btn__icon out-demo-btn__icon--{{ $item['scope_type'] }}">
                                                <i class="{{ $item['icon'] }}"></i>
                                            </span>
                                            <span class="out-demo-btn__text">
                                                <span class="out-demo-btn__top">
                                                    <strong class="out-demo-btn__role">{{ $item['label'] }}</strong>
                                                    <span class="out-demo-scope out-demo-scope--{{ $item['scope_type'] }}">{{ $item['scope_badge'] }}</span>
                                                </span>
                                                <span class="out-demo-btn__email">{{ $item['email'] }}</span>
                                                <span class="out-demo-btn__hint">{{ $item['short_rbac'] }}</span>
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach

                    <div class="out-demo-inspector" id="demoRbacInspector">
                        <div class="out-demo-inspector__head">
                            <span class="out-demo-inspector__title" id="inspectorRoleTitle">RBAC &amp; Access Isolation Preview</span>
                            <span class="out-demo-scope out-demo-scope--global" id="inspectorScopeBadge">Hover any role card</span>
                        </div>
                        <div class="out-demo-inspector__meta">
                            <div><strong>Credential:</strong> <code id="inspectorEmail">admin@maarifazone.com</code> / <code>{{ config('demo_accounts.default_password', 'password') }}</code></div>
                            <div><strong>Allowed Modules:</strong> <span id="inspectorAllowed">Select or hover a role above to inspect its permitted routes &amp; modules.</span></div>
                            <div><strong>Isolation &amp; Restrictions:</strong> <span id="inspectorRestricted">Global roles span all 17 branches; Branch roles enforce BranchScoped center isolation; Tutor/Student roles isolate to dedicated portals.</span></div>
                        </div>
                    </div>
                </div>
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
        const filterTabs = document.querySelectorAll('.out-demo-tab');
        const groupSections = document.querySelectorAll('.out-demo-group');
        const inspectorRoleTitle = document.getElementById('inspectorRoleTitle');
        const inspectorScopeBadge = document.getElementById('inspectorScopeBadge');
        const inspectorEmail = document.getElementById('inspectorEmail');
        const inspectorAllowed = document.getElementById('inspectorAllowed');
        const inspectorRestricted = document.getElementById('inspectorRestricted');

        function updateInspector(btn) {
            if (!btn || !inspectorRoleTitle) {
                return;
            }
            const scopeType = btn.getAttribute('data-scope-type') || 'global';
            inspectorRoleTitle.textContent = btn.getAttribute('data-role') + ' — RBAC & Access Profile';
            inspectorScopeBadge.textContent = btn.getAttribute('data-scope') || 'Global';
            inspectorScopeBadge.className = 'out-demo-scope out-demo-scope--' + scopeType;
            inspectorEmail.textContent = btn.getAttribute('data-email') || '';
            inspectorAllowed.textContent = btn.getAttribute('data-allowed') || '';
            inspectorRestricted.textContent = btn.getAttribute('data-restricted') || '';
        }

        filterTabs.forEach(tab => {
            tab.addEventListener('click', function () {
                filterTabs.forEach(t => t.classList.remove('is-active'));
                this.classList.add('is-active');
                const filter = this.getAttribute('data-filter');

                groupSections.forEach(section => {
                    const key = section.getAttribute('data-group-section');
                    section.style.display = (filter === 'all' || filter === key) ? '' : 'none';
                });
            });
        });

        // One-Click Credential Autofill, RBAC Inspection & Instant Login
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

                // Quick submission
                setTimeout(() => {
                    form.submit();
                }, 250);
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
