@extends('layouts.auth')

@php
    $siteName = site_name();
    $pageTitle = 'Forgot Password | ' . $siteName;
    $logoUrl = branding_asset(['dark_logo_path', 'footer_logo_path', 'primary_logo_path', 'logos.primary'], 'assets/front/img/logo/maarifazone-logo-dark.png');
@endphp

@section('content')
<div class="maarifazone-auth-layout">
    <!-- Left: Maarifazone Brand Showcase Panel -->
    <div class="maarifazone-auth-showcase">
        <a href="{{ route('home') }}" class="maarifazone-auth-showcase__brand" aria-label="{{ $siteName }}">
            <span class="maarifazone-auth-showcase__brand-badge">
                <img src="{{ $logoUrl }}" alt="{{ $siteName }} Logo">
            </span>
        </a>

        <div class="maarifazone-auth-showcase__content">
            <div class="maarifazone-auth-eyebrow">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Account Security &amp; Access</span>
            </div>

            <h1 class="maarifazone-auth-heading">
                Reset your account <span>password</span>.
            </h1>

            <p class="maarifazone-auth-desc">
                Don't worry &mdash; enter your registered email address and we'll send you a secure link to recover your account.
            </p>

            <div class="maarifazone-auth-features">
                <div class="maarifazone-auth-feature-item">
                    <div class="maarifazone-auth-feature-icon">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div class="maarifazone-auth-feature-text">
                        <h5>Secure Recovery Link</h5>
                        <p>Time-sensitive encrypted link delivered right to your inbox.</p>
                    </div>
                </div>

                <div class="maarifazone-auth-feature-item">
                    <div class="maarifazone-auth-feature-icon">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <div class="maarifazone-auth-feature-text">
                        <h5>Support Available</h5>
                        <p>Need extra help? Our academic helpdesk is available at info@maarifazone.com.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="maarifazone-auth-quote-card">
            <p class="maarifazone-auth-quote-text">
                &ldquo;Account recovery is fast, verified, and secure with Maarifazone.&rdquo;
            </p>
            <div class="maarifazone-auth-quote-author">
                <div class="maarifazone-auth-quote-avatar">IT</div>
                <div>
                    <h6 class="maarifazone-auth-quote-name">Maarifazone Support Services</h6>
                    <p class="maarifazone-auth-quote-title">ICT Directorate &bull; Dar es Salaam</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Forgot Password Form Panel -->
    <div class="maarifazone-auth-form-panel">
        <div class="maarifazone-auth-topbar">
            <a href="{{ route('login') }}" class="maarifazone-auth-back-link">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to login</span>
            </a>

            <div class="maarifazone-auth-lang-pill">
                <i class="fa-solid fa-globe text-primary"></i>
                <span>English &middot; {{ currency_code() }}</span>
            </div>
        </div>

        <div class="maarifazone-auth-form-container">
            <div class="maarifazone-auth-form-header">
                <h2 class="maarifazone-auth-form-title">Forgot Password</h2>
                <p class="maarifazone-auth-form-sub">Enter your email to receive a password reset link.</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success py-2 px-3 mb-3 small" style="border-radius: 9px;">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST">
                @csrf

                <div class="maarifazone-form-group">
                    <label for="email" class="maarifazone-form-label">Email address</label>
                    <div class="maarifazone-form-input-wrap">
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            placeholder="name@example.com"
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

                <x-recaptcha-widget class="mb-3" action="password_reset" />

                <button type="submit" class="maarifazone-auth-submit-btn">
                    <span>Send Reset Link</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>
        </div>

        <div class="maarifazone-auth-form-footer">
            <p class="maarifazone-auth-switch-text">
                Remember your password?
                <a href="{{ route('login') }}" class="maarifazone-auth-switch-link">Sign in here</a>
            </p>
            <p class="maarifazone-auth-copy-text">
                &copy; {{ date('Y') }} Maarifazone. All rights reserved.
            </p>
        </div>
    </div>
</div>
@endsection
