<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ rtrim(url('/'), '/') }}">
    <meta name="author" content="Maarifazone — Wolinet">
    <meta name="application-name" content="{{ site_name() }}">
    <meta name="theme-color" content="#f99f1e">
    <meta name="description" content="{{ $metaDescription ?? site_name() . ' - ' . site_tagline() }}">
    <title>{{ !empty($pageTitle) ? (str_contains($pageTitle, site_name()) ? $pageTitle : $pageTitle . ' | ' . site_name()) : site_name() . ' - ' . site_tagline() }}</title>

    <!-- Favicon & App Icons -->
    @php($favicon = branding_asset(['favicon_path', 'logos.favicon', 'favicon'], 'assets/front/img/logo/maarifazone-favicon.png'))
    <link rel="icon" type="image/png" href="{{ $favicon }}">
    <link rel="shortcut icon" href="{{ $favicon }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Google Fonts & Font Awesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/front/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/all.min.css') }}">

    {!! ToastMagic::styles() !!}

    <style>
        :root {
            --maarifazone-primary: #f99f1e;
            --maarifazone-primary-dark: #c97406;
            --maarifazone-secondary: #e0860a;
            --maarifazone-accent: #111827;
            --maarifazone-navy: #0f172a;
            --maarifazone-slate: #64748b;
            --maarifazone-bg: #f8fafc;
            --maarifazone-border: #e2e8f0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #ffffff;
            color: #0f172a;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .maarifazone-auth-layout {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* ──────────────── Left Showcase Panel ──────────────── */
        .maarifazone-auth-showcase {
            flex: 0 0 46%;
            max-width: 46%;
            background: linear-gradient(135deg, #c97406 0%, #f99f1e 52%, #e0860a 100%);
            color: #ffffff;
            padding: 48px 54px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        /* Decorative ambient circles resembling Maarifazone logo arcs */
        .maarifazone-auth-showcase::before {
            content: '';
            position: absolute;
            width: 520px;
            height: 520px;
            border-radius: 50%;
            border: 36px solid rgba(255, 255, 255, 0.14);
            top: -140px;
            right: -180px;
            pointer-events: none;
        }

        .maarifazone-auth-showcase::after {
            content: '';
            position: absolute;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.18) 0%, transparent 70%);
            bottom: 40px;
            left: -60px;
            pointer-events: none;
        }

        .maarifazone-auth-showcase__brand {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none !important;
        }

        .maarifazone-auth-showcase__brand-badge {
            height: 44px;
            width: auto;
            border-radius: 10px;
            background: transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: none;
        }

        .maarifazone-auth-showcase__brand-badge img {
            height: 36px;
            width: auto;
            max-width: 160px;
            object-fit: contain;
        }

        .maarifazone-auth-showcase__brand-name {
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.02em;
        }

        .maarifazone-auth-showcase__brand-name span {
            color: #111827;
        }

        .maarifazone-auth-showcase__content {
            position: relative;
            z-index: 2;
            margin: 40px 0;
        }

        .maarifazone-auth-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.22);
            color: #ffffff;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.04em;
            margin-bottom: 22px;
        }

        .maarifazone-auth-eyebrow i {
            color: #ffffff;
        }

        .maarifazone-auth-heading {
            font-size: clamp(28px, 2.6vw, 40px);
            font-weight: 800;
            line-height: 1.22;
            letter-spacing: -0.025em;
            color: #ffffff;
            margin-bottom: 14px;
        }

        .maarifazone-auth-heading span {
            color: #111827;
        }

        .maarifazone-auth-desc {
            font-size: 14.5px;
            line-height: 1.65;
            color: rgba(255, 255, 255, 0.86);
            margin-bottom: 32px;
            max-width: 480px;
        }

        /* 3 Benefit Feature Items */
        .maarifazone-auth-features {
            display: flex;
            flex-direction: column;
            gap: 18px;
            margin-bottom: 34px;
        }

        .maarifazone-auth-feature-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .maarifazone-auth-feature-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .maarifazone-auth-feature-text h5 {
            font-size: 14px;
            font-weight: 700;
            color: #ffffff;
            margin: 0 0 2px;
        }

        .maarifazone-auth-feature-text p {
            font-size: 12.5px;
            color: rgba(255, 255, 255, 0.76);
            margin: 0;
            line-height: 1.5;
        }

        /* Testimonial Quote Card */
        .maarifazone-auth-quote-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 16px;
            padding: 20px 22px;
            position: relative;
            z-index: 2;
        }

        .maarifazone-auth-quote-text {
            font-size: 13px;
            font-style: italic;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.92);
            margin-bottom: 14px;
        }

        .maarifazone-auth-quote-author {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .maarifazone-auth-quote-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #111827, #374151);
            color: #f99f1e;
            font-size: 13px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .maarifazone-auth-quote-name {
            font-size: 13px;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
        }

        .maarifazone-auth-quote-title {
            font-size: 11.5px;
            color: rgba(255, 255, 255, 0.72);
            margin: 0;
        }

        /* ──────────────── Right Form Panel ──────────────── */
        .maarifazone-auth-form-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #ffffff;
            padding: 36px 48px;
            overflow-y: auto;
        }

        .maarifazone-auth-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            margin-bottom: 24px;
        }

        .maarifazone-auth-back-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            text-decoration: none !important;
            transition: color 0.18s ease, transform 0.18s ease;
        }

        .maarifazone-auth-back-link:hover {
            color: #f99f1e;
            transform: translateX(-3px);
        }

        .maarifazone-auth-lang-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 11.5px;
            font-weight: 600;
            color: #475569;
        }

        .maarifazone-auth-form-container {
            max-width: 440px;
            width: 100%;
            margin: auto;
            padding: 10px 0;
        }

        .maarifazone-auth-form-container--wide {
            max-width: 680px;
        }

        .maarifazone-auth-form-header {
            margin-bottom: 24px;
        }

        .maarifazone-auth-form-title {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
            margin: 0 0 6px;
        }

        .maarifazone-auth-form-sub {
            font-size: 13.5px;
            color: #64748b;
            margin: 0;
            font-weight: 500;
        }

        /* Form Inputs */
        .maarifazone-form-group {
            margin-bottom: 18px;
        }

        .maarifazone-form-label {
            display: block;
            font-size: 12.5px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 7px;
        }

        .maarifazone-form-input-wrap {
            position: relative;
        }

        .maarifazone-form-input {
            width: 100%;
            height: 46px;
            padding: 0 14px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            font-size: 13.5px;
            color: #0f172a;
            outline: none;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .maarifazone-form-input:focus {
            background: #ffffff;
            border-color: #f99f1e;
            box-shadow: 0 0 0 3px rgba(249, 159, 30, 0.12);
        }

        .maarifazone-form-input.is-invalid {
            border-color: #ef4444;
            background: #fef2f2;
        }

        .maarifazone-form-toggle-pwd {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            cursor: pointer;
            padding: 6px;
            font-size: 14px;
            transition: color 0.18s ease;
        }

        .maarifazone-form-toggle-pwd:hover {
            color: #f99f1e;
        }

        .maarifazone-form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 12.5px;
        }

        .maarifazone-form-checkbox-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #475569;
            font-weight: 500;
            cursor: pointer;
            user-select: none;
            margin-bottom: 0;
        }

        .maarifazone-form-forgot-link {
            color: #f99f1e;
            font-weight: 600;
            text-decoration: none !important;
            transition: color 0.18s ease;
        }

        .maarifazone-form-forgot-link:hover {
            color: #e0860a;
            text-decoration: underline !important;
        }

        .maarifazone-auth-submit-btn {
            width: 100%;
            height: 46px;
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, #f99f1e 0%, #e0860a 100%);
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(249, 159, 30, 0.25);
            transition: all 0.2s ease;
        }

        .maarifazone-auth-submit-btn:hover {
            background: linear-gradient(135deg, #c97406 0%, #f99f1e 100%);
            box-shadow: 0 6px 20px rgba(249, 159, 30, 0.35);
            transform: translateY(-1px);
        }

        .maarifazone-auth-submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* ──────────────── One-Click Demo Credentials Card ──────────────── */
        .out-demo-box {
            background: #fffdf8;
            border: 1px solid #fed7aa;
            border-radius: 14px;
            padding: 16px 18px;
            margin-top: 22px;
            position: relative;
            box-shadow: 0 2px 8px rgba(249, 159, 30, 0.05);
        }

        .out-demo-box__header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
        }

        .out-demo-box__badge {
            display: inline-flex;
            align-items: center;
            background: #f99f1e;
            color: #ffffff;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 6px;
        }

        .out-demo-box__title {
            font-size: 13.5px;
            font-weight: 800;
            color: #92400e;
            margin: 0;
            letter-spacing: -0.01em;
        }

        .out-demo-box__sub {
            font-size: 11.5px;
            color: #78350f;
            margin: 0 0 12px;
            line-height: 1.4;
        }

        .out-demo-box__sub strong {
            background: rgba(249, 159, 30, 0.15);
            padding: 1px 6px;
            border-radius: 4px;
            color: #9a3412;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        }

        .out-demo-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        @media (max-width: 680px) {
            .out-demo-grid {
                grid-template-columns: 1fr;
            }
        }

        .out-demo-btn {
            background: #ffffff;
            border: 1.5px solid #fde68a;
            border-radius: 12px;
            padding: 12px 12px;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            gap: 6px;
            width: 100%;
            position: relative;
        }

        .out-demo-btn:hover {
            border-color: #f99f1e;
            background: #fffaf0;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(249, 159, 30, 0.14);
        }

        .out-demo-btn.is-active {
            border-color: #f99f1e;
            background: #fff7ed;
            box-shadow: 0 0 0 3px rgba(249, 159, 30, 0.25);
        }

        .out-demo-btn__icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .out-demo-btn__icon--admin {
            background: #fef3c7;
            color: #b45309;
        }

        .out-demo-btn__icon--instructor {
            background: #d1fae5;
            color: #047857;
        }

        .out-demo-btn__icon--student,
        .out-demo-btn__icon--scholar {
            background: #ffedd5;
            color: #ea580c;
        }

        .out-demo-btn__text {
            min-width: 0;
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .out-demo-btn__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 4px;
            margin-bottom: 2px;
        }

        .out-demo-btn__role {
            font-size: 12.5px;
            font-weight: 800;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .out-demo-scope {
            font-size: 9.5px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 999px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .out-demo-scope--admin {
            background: #fef3c7;
            color: #92400e;
        }

        .out-demo-scope--instructor {
            background: #d1fae5;
            color: #065f46;
        }

        .out-demo-scope--student,
        .out-demo-scope--scholar {
            background: #ffedd5;
            color: #9a3412;
        }

        .out-demo-btn__name {
            font-size: 11px;
            color: #475569;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .out-demo-btn__email {
            font-size: 10.5px;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            color: #f99f1e;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .out-demo-btn__action {
            margin-top: 4px;
            font-size: 10.5px;
            font-weight: 700;
            color: #92400e;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: color 0.15s ease;
        }

        .out-demo-btn:hover .out-demo-btn__action {
            color: #f99f1e;
        }

        .out-demo-inspector {
            margin-top: 14px;
            background: #ffffff;
            border: 1px solid #fed7aa;
            border-left: 3px solid #f99f1e;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 11px;
            line-height: 1.45;
            color: #334155;
        }

        .out-demo-inspector__head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            margin-bottom: 4px;
        }

        .out-demo-inspector__title {
            font-weight: 800;
            color: #0f172a;
            font-size: 11.5px;
        }

        .out-demo-inspector__meta {
            display: grid;
            gap: 2px;
        }

        .out-demo-inspector__meta strong {
            color: #0f172a;
        }

        /* Footer in form panel */
        .maarifazone-auth-form-footer {
            margin-top: 24px;
            text-align: center;
        }

        .maarifazone-auth-switch-text {
            font-size: 13px;
            color: #64748b;
            margin: 0 0 10px;
        }

        .maarifazone-auth-switch-link {
            color: #f99f1e;
            font-weight: 700;
            text-decoration: none !important;
        }

        .maarifazone-auth-switch-link:hover {
            color: #e0860a;
            text-decoration: underline !important;
        }

        .maarifazone-auth-copy-text {
            font-size: 11.5px;
            color: #94a3b8;
            margin: 0;
        }

        @media (max-width: 991px) {
            .maarifazone-auth-layout {
                flex-direction: column;
            }
            .maarifazone-auth-showcase {
                flex: none;
                max-width: 100%;
                padding: 36px 24px;
            }
            .maarifazone-auth-form-panel {
                padding: 32px 20px;
            }
            .out-demo-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    @yield('content')

    <!-- Scripts -->
    <script src="{{ asset('assets/front/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/front/js/bootstrap.bundle.min.js') }}"></script>
    {!! ToastMagic::scripts() !!}

    <script>
        // Password toggle function
        function setupPasswordToggle(inputSelector, toggleSelector) {
            const input = document.querySelector(inputSelector);
            const toggle = document.querySelector(toggleSelector);
            if (input && toggle) {
                toggle.addEventListener('click', function () {
                    const isPwd = input.getAttribute('type') === 'password';
                    input.setAttribute('type', isPwd ? 'text' : 'password');
                    const icon = toggle.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fa-eye', !isPwd);
                        icon.classList.toggle('fa-eye-slash', isPwd);
                    }
                });
            }
        }
    </script>
    @stack('scripts')
</body>
</html>

