@extends('layouts.auth')

@php
    $siteName = site_name();
    $pageTitle = 'Create Account | ' . $siteName;
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
                <span>Open Admission Across All Regions</span>
            </div>

            <h1 class="maarifazone-auth-heading">
                Start learning with <span>Maarifazone</span> today.
            </h1>

            <p class="maarifazone-auth-desc">
                Create your student account to access world-class courses, digital libraries, and accredited programs designed for career advancement.
            </p>

            <div class="maarifazone-auth-features">
                <div class="maarifazone-auth-feature-item">
                    <div class="maarifazone-auth-feature-icon">
                        <i class="fa-solid fa-laptop-code"></i>
                    </div>
                    <div class="maarifazone-auth-feature-text">
                        <h5>Open &amp; Online Learning</h5>
                        <p>Study on your schedule with blended one-on-one and online modules.</p>
                    </div>
                </div>

                <div class="maarifazone-auth-feature-item">
                    <div class="maarifazone-auth-feature-icon">
                        <i class="fa-solid fa-certificate"></i>
                    </div>
                    <div class="maarifazone-auth-feature-text">
                        <h5>Accredited &amp; Recognized</h5>
                        <p>Fully accredited credentials recognized by TCU, HESLB, and employers worldwide.</p>
                    </div>
                </div>

                <div class="maarifazone-auth-feature-item">
                    <div class="maarifazone-auth-feature-icon">
                        <i class="fa-solid fa-globe"></i>
                    </div>
                    <div class="maarifazone-auth-feature-text">
                        <h5>Available Nationwide</h5>
                        <p>Learning centers in every region with regular and on-demand examinations.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Testimonial Quote -->
        <div class="maarifazone-auth-quote-card">
            <p class="maarifazone-auth-quote-text">
                &ldquo;Enrolling at Maarifazone was the best decision for my career. The flexibility to study online while working full-time is unmatched.&rdquo;
            </p>
            <div class="maarifazone-auth-quote-author">
                <div class="maarifazone-auth-quote-avatar">BK</div>
                <div>
                    <h6 class="maarifazone-auth-quote-name">Baraka Kassim</h6>
                    <p class="maarifazone-auth-quote-title">Data Analyst &bull; Arusha, Tanzania</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Registration Form Panel -->
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
        <div class="maarifazone-auth-form-container">
            <div class="maarifazone-auth-form-header">
                <h2 class="maarifazone-auth-form-title">Create your account</h2>
                <p class="maarifazone-auth-form-sub">Join thousands of students and start learning today.</p>
            </div>

            <form action="{{ route('register') }}" method="POST" id="outRegisterForm">
                @csrf

                <!-- First & Last Name (2 Columns) -->
                <div class="row g-2 mb-3">
                    <div class="col-sm-6">
                        <label for="first_name" class="maarifazone-form-label">First name</label>
                        <input
                            type="text"
                            name="first_name"
                            id="first_name"
                            value="{{ old('first_name') }}"
                            placeholder="John"
                            class="maarifazone-form-input @error('first_name') is-invalid @enderror"
                            required
                            autocomplete="given-name"
                            autofocus
                        >
                        @error('first_name')
                            <div class="invalid-feedback d-block mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-sm-6">
                        <label for="last_name" class="maarifazone-form-label">Last name</label>
                        <input
                            type="text"
                            name="last_name"
                            id="last_name"
                            value="{{ old('last_name') }}"
                            placeholder="Doe"
                            class="maarifazone-form-input @error('last_name') is-invalid @enderror"
                            required
                            autocomplete="family-name"
                        >
                        @error('last_name')
                            <div class="invalid-feedback d-block mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Email Address -->
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
                        >
                    </div>
                    @error('email')
                        <div class="invalid-feedback d-block mt-1 small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="maarifazone-form-group">
                    <label for="password" class="maarifazone-form-label">Password</label>
                    <div class="maarifazone-form-input-wrap">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="At least 8 characters"
                            class="maarifazone-form-input @error('password') is-invalid @enderror"
                            required
                            autocomplete="new-password"
                        >
                        <span class="maarifazone-form-toggle-pwd" id="togglePasswordBtn" title="Toggle password visibility">
                            <i class="fa-regular fa-eye"></i>
                        </span>
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block mt-1 small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="maarifazone-form-group">
                    <label for="password_confirmation" class="maarifazone-form-label">Confirm password</label>
                    <div class="maarifazone-form-input-wrap">
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            placeholder="Re-type password"
                            class="maarifazone-form-input"
                            required
                            autocomplete="new-password"
                        >
                        <span class="maarifazone-form-toggle-pwd" id="toggleConfirmPasswordBtn" title="Toggle password visibility">
                            <i class="fa-regular fa-eye"></i>
                        </span>
                    </div>
                </div>

                <!-- Terms & Privacy Agreement -->
                <div class="maarifazone-form-row mb-3">
                    <label class="maarifazone-form-checkbox-label" for="terms">
                        <input type="checkbox" name="terms" id="terms" class="form-check-input mt-0 @error('terms') is-invalid @enderror" required {{ old('terms') ? 'checked' : '' }}>
                        <span class="small">
                            I agree to the <a href="{{ url('/terms-conditions') }}" target="_blank" class="maarifazone-form-forgot-link">Terms of Service</a> &amp; <a href="{{ url('/terms-conditions') }}" target="_blank" class="maarifazone-form-forgot-link">Privacy Policy</a>
                        </span>
                    </label>
                </div>
                @error('terms')
                    <div class="text-danger small mb-2">{{ $message }}</div>
                @enderror

                <x-recaptcha-widget class="mb-3" action="student_register" />

                <!-- Submit Button -->
                <button type="submit" class="maarifazone-auth-submit-btn" id="registerSubmitBtn">
                    <span>Create Account</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>

                <!-- One-Click Quick Access Hint -->
                <div class="p-3 mt-3 rounded-lg text-center" style="background: #f1f5f9; border: 1px dashed #cbd5e1; border-radius: 12px;">
                    <p class="mb-1 small text-muted">
                        <i class="fa-solid fa-bolt text-warning mr-1"></i> Testing the platform or need instant demo access?
                    </p>
                    <a href="{{ route('login') }}" class="font-weight-bold" style="color: #f99f1e; font-size: 12px; text-decoration: none;">
                        Try One-Click Demo Login as Admin, Instructor or Student &rarr;
                    </a>
                </div>
            </form>
        </div>

        <!-- Panel Footer -->
        <div class="maarifazone-auth-form-footer">
            <p class="maarifazone-auth-switch-text">
                Already have an account?
                <a href="{{ route('login') }}" class="maarifazone-auth-switch-link">Sign in here</a>
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
        setupPasswordToggle('#password_confirmation', '#toggleConfirmPasswordBtn');

        const form = document.getElementById('outRegisterForm');
        const submitBtn = document.getElementById('registerSubmitBtn');

        if (form && submitBtn) {
            form.addEventListener('submit', function () {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span>Creating account...</span> <i class="fa-solid fa-circle-notch fa-spin"></i>`;
            });
        }
    });
</script>
@endpush
