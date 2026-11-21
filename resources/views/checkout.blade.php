@extends('layouts.app')

@push('styles')
<style>
    .maarifazone-checkout-page {
        background: #f8fafc;
        padding: 36px 0 72px;
        min-height: 78vh;
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .maarifazone-co-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 24px;
    }

    .maarifazone-co-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13.5px;
        font-weight: 600;
        color: #475569;
        text-decoration: none;
        transition: color 0.15s ease;
    }
    .maarifazone-co-back:hover {
        color: var(--theme, #f99f1e);
    }

    .maarifazone-co-secure-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 14px;
        border-radius: 999px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
        font-size: 12.5px;
        font-weight: 600;
    }

    .maarifazone-co-heading {
        margin-bottom: 24px;
    }
    .maarifazone-co-heading h1 {
        font-size: clamp(22px, 2.5vw, 28px);
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 6px;
        letter-spacing: -0.02em;
    }
    .maarifazone-co-heading p {
        font-size: 14.5px;
        color: #64748b;
        margin: 0;
    }

    .maarifazone-co-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
    }

    .maarifazone-co-card-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
        padding-bottom: 14px;
        border-bottom: 1px solid #f1f5f9;
    }
    .maarifazone-co-card-title h2 {
        font-size: 16.5px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .maarifazone-co-step-num {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: #0f172a;
        color: #ffffff;
        font-size: 13px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .maarifazone-co-course-row {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .maarifazone-co-course-thumb {
        width: 96px;
        height: 68px;
        border-radius: 12px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
        flex-shrink: 0;
    }
    .maarifazone-co-course-meta h3 {
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 6px;
        line-height: 1.35;
    }
    .maarifazone-co-course-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        font-size: 12.5px;
        color: #64748b;
    }
    .maarifazone-co-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #334155;
        font-size: 12px;
        font-weight: 600;
    }

    .maarifazone-co-gateway-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .maarifazone-co-gateway-option {
        position: relative;
        margin: 0;
        cursor: pointer;
        display: block;
    }
    .maarifazone-co-gateway-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .maarifazone-co-gateway-box {
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 16px;
        background: #ffffff;
        transition: all 0.18s ease;
        height: 100%;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .maarifazone-co-gateway-option:hover .maarifazone-co-gateway-box {
        border-color: #94a3b8;
        background: #f8fafc;
    }
    .maarifazone-co-gateway-option input:checked + .maarifazone-co-gateway-box {
        border-color: #0f172a;
        background: #f8fafc;
        box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.1);
    }

    .maarifazone-co-radio-dot {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 2px solid #cbd5e1;
        margin-top: 2px;
        flex-shrink: 0;
        position: relative;
        transition: all 0.15s ease;
    }
    .maarifazone-co-gateway-option input:checked + .maarifazone-co-gateway-box .maarifazone-co-radio-dot {
        border-color: #0f172a;
    }
    .maarifazone-co-gateway-option input:checked + .maarifazone-co-gateway-box .maarifazone-co-radio-dot::after {
        content: "";
        position: absolute;
        inset: 3px;
        border-radius: 50%;
        background: #0f172a;
    }

    .maarifazone-co-gateway-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #f1f5f9;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
        transition: all 0.15s ease;
    }
    .maarifazone-co-gateway-option input:checked + .maarifazone-co-gateway-box .maarifazone-co-gateway-icon {
        background: #0f172a;
        color: #ffffff;
    }

    .maarifazone-co-gateway-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        font-size: 14.5px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 4px;
    }
    .maarifazone-co-gateway-tag {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 3px 9px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #0f172a;
        border: 1px solid #cbd5e1;
    }
    .maarifazone-co-gateway-tag--offline {
        background: #ecfdf5;
        color: #047857;
        border-color: #a7f3d0;
    }
    .maarifazone-co-gateway-desc {
        font-size: 12.5px;
        color: #64748b;
        line-height: 1.45;
        margin: 0;
    }

    /* Pesapal Embedded Checkout Panel */
    .maarifazone-pesapal-panel {
        border: 1px solid #fff4e0;
        border-radius: 14px;
        background: #f8fbff;
        overflow: hidden;
        margin-top: 8px;
    }
    .maarifazone-pesapal-panel__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        padding: 12px 16px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
    }
    .maarifazone-pesapal-panel__brand {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13.5px;
        font-weight: 700;
        color: #0f172a;
    }
    .maarifazone-pesapal-panel__rails {
        font-size: 12px;
        font-weight: 500;
        color: #64748b;
    }
    .maarifazone-pesapal-panel__actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .maarifazone-pesapal-btn-sm {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 11px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #334155;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .maarifazone-pesapal-btn-sm:hover {
        border-color: var(--theme, #f99f1e);
        color: var(--theme, #f99f1e);
    }

    .maarifazone-pesapal-loading,
    .maarifazone-pesapal-error {
        padding: 48px 24px;
        text-align: center;
    }
    .maarifazone-pesapal-spinner {
        width: 40px;
        height: 40px;
        border: 3px solid #fff4e0;
        border-top-color: var(--theme, #f99f1e);
        border-radius: 50%;
        animation: lmsoutSpin 0.8s linear infinite;
        margin: 0 auto 14px;
    }
    @keyframes lmsoutSpin {
        to { transform: rotate(360deg); }
    }
    .maarifazone-pesapal-iframe {
        width: 100%;
        min-height: 620px;
        height: 620px;
        border: 0;
        display: block;
        background: #ffffff;
    }

    /* Offline Lipa Namba / TANQR panel */
    .maarifazone-offline-panel {
        border: 1.5px solid #e2e8f0;
        background: #ffffff;
        border-radius: 16px;
        padding: 22px;
        margin-top: 12px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    }
    .maarifazone-lipa-card {
        display: grid;
        grid-template-columns: 230px 1fr;
        gap: 20px;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #ffffff;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 18px;
        align-items: center;
    }
    .maarifazone-lipa-qr-box {
        background: #ffffff;
        border-radius: 12px;
        padding: 12px;
        text-align: center;
        color: #0f172a;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
    }
    .maarifazone-lipa-qr-box img {
        width: 180px;
        height: 180px;
        object-fit: contain;
        display: block;
        margin: 0 auto 8px;
    }
    .maarifazone-lipa-qr-caption {
        font-size: 11px;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }
    .maarifazone-lipa-number-box {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 12px;
        padding: 10px 16px;
        margin: 8px 0 12px;
    }
    .maarifazone-lipa-number {
        font-size: 26px;
        font-weight: 800;
        letter-spacing: 0.06em;
        color: #ffffff;
        font-family: 'JetBrains Mono', 'Courier New', monospace;
    }
    .maarifazone-lipa-copy-btn {
        border: 1px solid rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.14);
        color: #ffffff;
        border-radius: 8px;
        padding: 5px 11px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .maarifazone-lipa-copy-btn:hover {
        background: #ffffff;
        color: #0f172a;
    }
    .maarifazone-lipa-pdf-frame {
        width: 100%;
        height: 520px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-bottom: 16px;
        background: #f8fafc;
    }
    @media (max-width: 767px) {
        .maarifazone-lipa-card {
            grid-template-columns: 1fr;
            text-align: center;
        }
    }
    .maarifazone-offline-panel .form-control {
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        padding: 10px 14px;
        font-size: 14px;
        background: #ffffff;
    }
    .maarifazone-offline-panel .form-control:focus {
        border-color: var(--theme, #f99f1e);
        box-shadow: 0 0 0 3px rgba(var(--theme-rgb, 1, 59, 166), 0.12);
    }

    /* Sticky Summary Sidebar */
    .maarifazone-co-summary {
        position: sticky;
        top: 24px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    }
    .maarifazone-co-summary h3 {
        font-size: 17px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f5f9;
    }
    .maarifazone-co-line {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        font-size: 14px;
        color: #475569;
        margin-bottom: 10px;
    }
    .maarifazone-co-line strong {
        color: #0f172a;
        text-align: right;
    }
    .maarifazone-co-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 14px;
        margin-top: 14px;
        border-top: 1px solid #e2e8f0;
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
    }
    .maarifazone-co-total__amount {
        font-size: 20px;
        color: var(--theme, #f99f1e);
    }

    .maarifazone-co-coupon {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px dashed #e2e8f0;
    }
    .maarifazone-co-coupon-row {
        display: flex;
        gap: 8px;
    }
    .maarifazone-co-coupon-row input {
        flex: 1;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 8px 12px;
        font-size: 13.5px;
    }
    .maarifazone-co-coupon-row button {
        border: 0;
        border-radius: 10px;
        padding: 8px 16px;
        background: #0f172a;
        color: #ffffff;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }
    .maarifazone-co-coupon-row button:hover {
        background: var(--theme, #f99f1e);
    }

    .maarifazone-co-submit-btn {
        width: 100%;
        margin-top: 18px;
        padding: 13px 20px;
        border: 0;
        border-radius: 12px;
        background: var(--theme, #f99f1e);
        color: #ffffff;
        font-size: 15px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .maarifazone-co-submit-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(var(--theme-rgb, 1, 59, 166), 0.22);
    }
    .maarifazone-co-submit-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    .maarifazone-co-auto-hint {
        margin-top: 16px;
        padding: 12px 14px;
        border-radius: 12px;
        background: #fff8ed;
        border: 1px solid #fde2b8;
        color: #c97406;
        font-size: 12.5px;
        line-height: 1.5;
    }

    .maarifazone-co-trust {
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid #f1f5f9;
        display: grid;
        gap: 8px;
        font-size: 12.5px;
        color: #64748b;
    }
    .maarifazone-co-trust-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .maarifazone-co-trust-item i {
        color: #10b981;
    }

    @media (max-width: 767px) {
        .maarifazone-checkout-page {
            padding: 20px 0 48px;
        }
        .maarifazone-co-card,
        .maarifazone-co-summary {
            padding: 18px;
        }
        .maarifazone-co-course-row {
            flex-direction: column;
            align-items: flex-start;
        }
        .maarifazone-pesapal-iframe {
            min-height: 660px;
            height: 660px;
        }
    }
</style>
@endpush

@section('content')
    @php
        $isBundle = isset($isBundle) && $isBundle;
        $itemTitle = $course->title ?? '';
        $itemImage = !empty($course->featured_image)
            ? asset('storage/' . $course->featured_image)
            : asset('assets/front/img/inner/courses-details/courses-details.jpg');

        $enabledOnlineGateways = $onlineGateways->where('is_enabled', true)->values();
        $hasOfflineGateway = $offlineGateway && $offlineGateway->is_enabled;
        $hasPesapalGateway = $enabledOnlineGateways->contains('identifier', 'pesapal');

        // Prioritize Pesapal as the default online gateway when active, else first enabled online gateway, else offline.
        $defaultGatewayIdentifier = old('payment_method');
        if (empty($defaultGatewayIdentifier)) {
            if ($hasPesapalGateway) {
                $defaultGatewayIdentifier = 'pesapal';
            } elseif ($enabledOnlineGateways->isNotEmpty()) {
                $defaultGatewayIdentifier = $enabledOnlineGateways->first()->identifier;
            } elseif ($hasOfflineGateway) {
                $defaultGatewayIdentifier = 'offline';
            }
        }

        $taxAmt = (tax_enabled() && $price > 0) ? calculate_tax($price) : 0;
        $totalWithTax = ($price > 0) ? ($price + $taxAmt) : 0;
        $formattedTotal = $price > 0 ? currency_format($totalWithTax) : __('frontend.free');
        $backRoute = $isBundle ? route('courses') : route('courses.show', $course->id);
    @endphp

    <section class="maarifazone-checkout-page">
        <div class="container">
            <div class="maarifazone-co-topbar">
                <a href="{{ $backRoute }}" class="maarifazone-co-back">
                    <i class="fa-solid fa-arrow-left"></i>
                    {{ $isBundle ? 'Back to Courses' : 'Back to Course Details' }}
                </a>
                <span class="maarifazone-co-secure-pill">
                    <i class="fa-solid fa-shield-check"></i>
                    Encrypted Seamless Checkout
                </span>
            </div>

            <div class="maarifazone-co-heading">
                <h1>{{ __('frontend.complete_enrollment') }}</h1>
                <p>{{ __('frontend.review_details_proceed') }}</p>
            </div>

            <div class="row g-4 align-items-start">
                <div class="col-lg-8">
                    <form id="checkoutForm"
                          action="{{ $isBundle ? route('bundles.checkout.process', $course->id) : route('courses.checkout.process', $course->id) }}"
                          method="POST" enctype="multipart/form-data">
                        @csrf
                        @if ($isBundle)
                            <input type="hidden" name="type" value="bundle">
                        @endif
                        @if (isset($topic) && $topic)
                            <input type="hidden" name="topic_id" value="{{ $topic->id }}">
                        @endif
                        <input type="hidden" name="coupon_code" id="applied_coupon_code" value="">

                        {{-- Card 1: Course Overview --}}
                        <div class="maarifazone-co-card">
                            <div class="maarifazone-co-card-title">
                                <h2><span class="maarifazone-co-step-num">1</span> {{ __('frontend.course_summary') }}</h2>
                            </div>

                            <div class="maarifazone-co-course-row">
                                <img src="{{ $itemImage }}" alt="{{ $itemTitle }}" class="maarifazone-co-course-thumb">
                                <div class="maarifazone-co-course-meta flex-grow-1">
                                    <h3>{{ $itemTitle }}</h3>
                                    @if(isset($topic) && $topic)
                                        <div class="alert alert-info py-2 px-3 mt-2 mb-2" style="font-size: 13.5px; border-radius: 8px;">
                                            <i class="fa-solid fa-layer-group me-1 text-primary"></i> <strong>Module Enrollment:</strong> You are purchasing access to topic: <span class="fw-bold">{{ $topic->title }}</span>
                                        </div>
                                    @endif
                                    <div class="maarifazone-co-course-badges">
                                        @if (isset($course->instructor))
                                            <span class="maarifazone-co-badge">
                                                <i class="fa-regular fa-user"></i>
                                                {{ $course->instructor->name }}
                                            </span>
                                        @endif
                                        @if (!$isBundle && !empty($course->delivery_mode))
                                            <span class="maarifazone-co-badge">
                                                <i class="fa-solid fa-layer-group"></i>
                                                {{ ucwords(str_replace('_', ' ', $course->delivery_mode)) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card 2: Active Payment Gateways & Auto-Loaded Checkout --}}
                        @if ($price > 0)
                            <div class="maarifazone-co-card payment-method">
                                <div class="maarifazone-co-card-title">
                                    <h2><span class="maarifazone-co-step-num">2</span> {{ __('frontend.select_payment_method') }}</h2>
                                    <span class="small text-muted">
                                        @if ($enabledOnlineGateways->isNotEmpty() && $hasOfflineGateway)
                                            Instant Online or Offline Bank/Control No.
                                        @elseif ($enabledOnlineGateways->isNotEmpty())
                                            Instant Online Payment
                                        @elseif ($hasOfflineGateway)
                                            Manual Bank / Receipt Verification
                                        @endif
                                    </span>
                                </div>

                                @if ($enabledOnlineGateways->isEmpty() && !$hasOfflineGateway)
                                    <div class="alert alert-warning mb-0">
                                        No active payment gateways are currently enabled. Please contact support.
                                    </div>
                                @else
                                    @php
                                        $gatewayIcons = [
                                            'pesapal'     => 'fa-solid fa-bolt',
                                            'stripe'      => 'fa-regular fa-credit-card',
                                            'flutterwave' => 'fa-solid fa-globe',
                                            'paypal'      => 'fa-brands fa-paypal',
                                            'offline'     => 'fa-solid fa-qrcode',
                                        ];
                                    @endphp
                                    <div class="maarifazone-co-gateway-list">
                                        @foreach ($enabledOnlineGateways as $gateway)
                                            @php $identifier = $gateway->identifier; @endphp
                                            <label class="maarifazone-co-gateway-option" for="{{ $identifier }}">
                                                <input type="radio" name="payment_method" id="{{ $identifier }}"
                                                       value="{{ $identifier }}"
                                                       {{ $defaultGatewayIdentifier === $identifier ? 'checked' : '' }}>
                                                <div class="maarifazone-co-gateway-box">
                                                    <span class="maarifazone-co-radio-dot"></span>
                                                    <span class="maarifazone-co-gateway-icon">
                                                        <i class="{{ $gatewayIcons[$identifier] ?? 'fa-solid fa-lock' }}"></i>
                                                    </span>
                                                    <div class="flex-grow-1">
                                                        <div class="maarifazone-co-gateway-title">
                                                            <span>{{ $gateway->name }}</span>
                                                            <span class="maarifazone-co-gateway-tag">
                                                                {{ $identifier === 'pesapal' ? 'Express Iframe' : __('frontend.online_payment') }}
                                                            </span>
                                                        </div>
                                                        <p class="maarifazone-co-gateway-desc">
                                                            {{ $gatewayDescriptions[$identifier] ?? __('frontend.pay_securely') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach

                                        @if ($hasOfflineGateway)
                                            <label class="maarifazone-co-gateway-option" for="offline">
                                                <input type="radio" name="payment_method" id="offline"
                                                       value="offline"
                                                       {{ $defaultGatewayIdentifier === 'offline' ? 'checked' : '' }}>
                                                <div class="maarifazone-co-gateway-box">
                                                    <span class="maarifazone-co-radio-dot"></span>
                                                    <span class="maarifazone-co-gateway-icon">
                                                        <i class="fa-solid fa-qrcode"></i>
                                                    </span>
                                                    <div class="flex-grow-1">
                                                        <div class="maarifazone-co-gateway-title">
                                                            <span>{{ $offlineGateway->name ?? __('frontend.offline_payment') }}</span>
                                                            <span class="maarifazone-co-gateway-tag maarifazone-co-gateway-tag--offline">Scan QR · Lipa Namba {{ $offlineGateway->lipa_namba }}</span>
                                                        </div>
                                                        <p class="maarifazone-co-gateway-desc">Scan TIPS / Selcom Pay TANQR or pay to Lipa Namba <strong>{{ $offlineGateway->lipa_namba }}</strong> ({{ $offlineGateway->merchant_name }}).</p>
                                                    </div>
                                                </div>
                                            </label>
                                        @endif
                                    </div>

                                    @error('payment_method')
                                        <div class="invalid-feedback d-block mb-3">{{ $message }}</div>
                                    @enderror

                                    {{-- Auto-loaded Embedded Pesapal Express Checkout --}}
                                    @if ($hasPesapalGateway)
                                        <div id="pesapalEmbeddedPanel" class="maarifazone-pesapal-panel {{ $defaultGatewayIdentifier === 'pesapal' ? '' : 'd-none' }}">
                                            <div class="maarifazone-pesapal-panel__header">
                                                <div class="maarifazone-pesapal-panel__brand">
                                                    <i class="fa-solid fa-lock text-success"></i>
                                                    <span>Pesapal Express Checkout</span>
                                                    <span class="maarifazone-pesapal-panel__rails d-none d-md-inline">M-Pesa · Mixx · Airtel · HaloPesa · Visa/Mastercard</span>
                                                </div>
                                                <div class="maarifazone-pesapal-panel__actions">
                                                    <button type="button" id="pesapalReloadBtn" class="maarifazone-pesapal-btn-sm">
                                                        <i class="fa-solid fa-rotate-right"></i> Reload
                                                    </button>
                                                    <a href="#" id="pesapalExternalLink" target="_blank" rel="noopener" class="maarifazone-pesapal-btn-sm d-none">
                                                        <i class="fa-solid fa-arrow-up-right-from-square"></i> New Tab
                                                    </a>
                                                </div>
                                            </div>

                                            <div id="pesapalLoadingState" class="maarifazone-pesapal-loading">
                                                <div class="maarifazone-pesapal-spinner"></div>
                                                <h6 class="mb-1 fw-bold text-dark">Loading Pesapal Secure Payment...</h6>
                                                <p class="small text-muted mb-0">Connecting automatically so you can pay right on this page.</p>
                                            </div>

                                            <div id="pesapalErrorState" class="maarifazone-pesapal-error d-none">
                                                <div class="text-danger mb-2"><i class="fa-solid fa-circle-exclamation fa-2x"></i></div>
                                                <h6 class="fw-bold text-dark mb-1">Could not initialize Pesapal checkout</h6>
                                                <p id="pesapalErrorText" class="small text-muted mb-3">Please check your connection and try again.</p>
                                                <button type="button" id="pesapalRetryBtn" class="btn btn-primary btn-sm px-3">
                                                    <i class="fa-solid fa-rotate-right me-1"></i> Retry Payment Form
                                                </button>
                                            </div>

                                            <div id="pesapalFrameWrap" class="d-none">
                                                <iframe id="pesapalCheckoutIframe"
                                                        title="Pesapal Secure Payment"
                                                        class="maarifazone-pesapal-iframe"
                                                        allow="payment *; clipboard-write"
                                                        loading="eager"
                                                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Offline Payment: Automatic TIPS / Selcom Pay TANQR & Lipa Namba Section --}}
                                    @if ($hasOfflineGateway)
                                        <div id="offlinePaymentSection"
                                             class="maarifazone-offline-panel {{ $defaultGatewayIdentifier === 'offline' ? '' : 'd-none' }}">
                                            <div class="maarifazone-lipa-card">
                                                <div class="maarifazone-lipa-qr-box">
                                                    <img src="{{ $offlineGateway->qr_image_url }}"
                                                         alt="TIPS Selcom Pay TANQR - Lipa Namba {{ $offlineGateway->lipa_namba }}">
                                                    <div class="maarifazone-lipa-qr-caption">
                                                        <i class="fa-solid fa-camera me-1"></i> Scan TANQR to Pay
                                                    </div>
                                                </div>

                                                <div>
                                                    <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                                        <span class="badge bg-success text-white px-2 py-1" style="font-size: 11px;">
                                                            <i class="fa-solid fa-qrcode me-1"></i> {{ $offlineGateway->provider_label }}
                                                        </span>
                                                        <span class="small text-white-50">Pay with phone, bank or card</span>
                                                    </div>

                                                    <div class="small text-white-50 text-uppercase fw-semibold mt-2" style="letter-spacing: 0.05em;">
                                                        Lipa Namba (TIPS / Selcom Pay)
                                                    </div>
                                                    <div class="maarifazone-lipa-number-box">
                                                        <span class="maarifazone-lipa-number" id="lipaNambaNumber">{{ $offlineGateway->lipa_namba }}</span>
                                                        <button type="button" class="maarifazone-lipa-copy-btn" id="copyLipaNambaBtn"
                                                                data-number="{{ $offlineGateway->lipa_namba }}">
                                                            <i class="fa-regular fa-copy me-1"></i> Copy
                                                        </button>
                                                    </div>

                                                    <div class="mb-2">
                                                        <span class="small text-white-50 d-block">Merchant / Account Name</span>
                                                        <strong class="fs-6 text-white">{{ $offlineGateway->merchant_name }}</strong>
                                                    </div>

                                                    <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                                                        <a href="{{ $offlineGateway->qr_pdf_url }}" target="_blank" rel="noopener"
                                                           class="btn btn-sm btn-light fw-semibold text-dark">
                                                            <i class="fa-solid fa-file-pdf text-danger me-1"></i> Open / Download QR Poster (PDF)
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-light fw-semibold" id="toggleQrPdfPreviewBtn">
                                                            <i class="fa-solid fa-eye me-1"></i> Preview Full PDF Poster
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="qrPdfPreviewWrap" class="d-none">
                                                <iframe src="{{ $offlineGateway->qr_pdf_url }}"
                                                        title="Lipa Namba {{ $offlineGateway->lipa_namba }} QR Poster PDF"
                                                        class="maarifazone-lipa-pdf-frame"></iframe>
                                            </div>

                                            @if (!empty($offlineInstructions))
                                                <div class="alert alert-light border small mb-3" role="alert">
                                                    <i class="fa-solid fa-circle-info text-primary me-1"></i>
                                                    {{ $offlineInstructions }}
                                                </div>
                                            @endif

                                            <h6 class="mb-3 fw-bold text-dark">{{ __('frontend.payment_details') }}</h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label for="transaction_id" class="form-label fw-semibold">
                                                        {{ __('frontend.transaction_id') }} / M-Pesa / Bank Ref <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" name="transaction_id" id="transaction_id"
                                                           value="{{ old('transaction_id') }}"
                                                           placeholder="e.g. QK84H92LMS / TIPS Reference"
                                                           class="form-control @error('transaction_id') is-invalid @enderror">
                                                    @error('transaction_id')
                                                        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="receipt_file" class="form-label fw-semibold">
                                                        {{ __('frontend.upload_receipt') }} <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="file" name="receipt_file" id="receipt_file"
                                                           accept=".pdf,.jpg,.jpeg,.png"
                                                           class="form-control @error('receipt_file') is-invalid @enderror">
                                                    <small class="text-muted">{{ __('frontend.accepted_formats') }}</small>
                                                    @error('receipt_file')
                                                        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endif

                        {{-- Fallback / Non-Pesapal Form Action Button --}}
                        <div class="form-actions {{ ($price > 0 && $defaultGatewayIdentifier === 'pesapal') ? 'd-none' : '' }}" id="mainSubmitWrap">
                            <button type="submit" class="maarifazone-co-submit-btn" id="submitBtn">
                                @if ($price > 0)
                                    {{ __('frontend.proceed_to_payment') }}
                                @else
                                    {{ __('frontend.complete_enrollment') }}
                                @endif
                                <i class="fa-solid fa-arrow-up-right"></i>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Sticky Summary Column --}}
                <div class="col-lg-4">
                    <div class="maarifazone-co-summary">
                        <h3>{{ __('frontend.order_summary') }}</h3>

                        <div class="maarifazone-co-line">
                            <span>{{ $isBundle ? __('frontend.bundle') : __('frontend.course') }}</span>
                            <strong>{{ \Illuminate\Support\Str::limit($itemTitle, 44) }}</strong>
                        </div>

                        <div class="maarifazone-co-line">
                            <span>Subtotal</span>
                            <span id="summary-original-price"><strong>{{ $price > 0 ? currency_format($price) : __('frontend.free') }}</strong></span>
                        </div>

                        <div class="maarifazone-co-line d-none" id="summary-discount-row">
                            <span>{{ __('frontend.discount') }}</span>
                            <span id="summary-discount-val" class="text-success"><strong>-</strong></span>
                        </div>

                        @if (tax_enabled() && $price > 0)
                            <div class="maarifazone-co-line" id="summary-tax-row">
                                <span>{{ tax_name() }} ({{ tax_rate() }}%)</span>
                                <span id="summary-tax-val" class="text-secondary"><strong>{{ currency_format($taxAmt) }}</strong></span>
                            </div>
                        @endif

                        <div class="maarifazone-co-total">
                            <span>{{ __('frontend.total') }}</span>
                            <span id="summary-total-price" class="maarifazone-co-total__amount">{{ $formattedTotal }}</span>
                        </div>

                        @if (!$isBundle && $price > 0)
                            <div class="maarifazone-co-coupon">
                                <label for="coupon_code_input" class="form-label small fw-semibold text-muted mb-1">Have a Promo Code?</label>
                                <div class="maarifazone-co-coupon-row">
                                    <input type="text" id="coupon_code_input" placeholder="Enter coupon code">
                                    <button type="button" id="apply_coupon_btn">Apply</button>
                                </div>
                                <div id="coupon-message" class="small mt-2"></div>
                            </div>
                        @endif

                        <div id="pesapalSidebarHint" class="maarifazone-co-auto-hint {{ ($price > 0 && $defaultGatewayIdentifier === 'pesapal') ? '' : 'd-none' }}">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            Complete your payment directly in the embedded Pesapal window. You will be enrolled and redirected automatically once confirmed.
                        </div>

                        <button type="submit" form="checkoutForm" id="sidebarSubmitBtn"
                                class="maarifazone-co-submit-btn {{ ($price > 0 && $defaultGatewayIdentifier === 'pesapal') ? 'd-none' : '' }}">
                            @if ($price > 0)
                                {{ __('frontend.proceed_to_payment') }}
                            @else
                                {{ __('frontend.complete_enrollment') }}
                            @endif
                            <i class="fa-solid fa-arrow-up-right"></i>
                        </button>

                        <div class="maarifazone-co-trust">
                            <div class="maarifazone-co-trust-item">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Instant enrollment confirmation on payment</span>
                            </div>
                            <div class="maarifazone-co-trust-item">
                                <i class="fa-solid fa-shield-halved"></i>
                                <span>Verified gateway settlement &amp; receipt</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            $(document).ready(function() {
                const basePrice = {{ (float) $price }};
                let effectivePrice = basePrice;
                let loadedPesapalKey = null;
                @php
                    $pesapalStatusBaseUrl = route('payment.pesapal.status');
                @endphp
                let pesapalLoading = false;
                let pesapalTrackingId = '';
                let pesapalPollTimer = null;
                const pesapalStatusBaseUrl = @json($pesapalStatusBaseUrl);

                function stopPesapalStatusPoll() {
                    if (pesapalPollTimer) {
                        clearInterval(pesapalPollTimer);
                        pesapalPollTimer = null;
                    }
                }

                function checkPesapalOrderStatus() {
                    if (!pesapalTrackingId) return;
                    $.getJSON(pesapalStatusBaseUrl, { order_tracking_id: pesapalTrackingId }, function(res) {
                        if (res && res.status === 'completed' && res.redirect_url) {
                            stopPesapalStatusPoll();
                            window.top.location.href = res.redirect_url;
                        } else if (res && res.status === 'failed') {
                            stopPesapalStatusPoll();
                            setPesapalUiState('error', res.message || 'Pesapal payment failed or was cancelled.');
                        }
                    });
                }

                function startPesapalStatusPoll(trackingId) {
                    stopPesapalStatusPoll();
                    pesapalTrackingId = trackingId || '';
                    if (!pesapalTrackingId) return;
                    pesapalPollTimer = setInterval(checkPesapalOrderStatus, 4000);
                }

                function currentOrderKey() {
                    return '{{ $course->id }}|' + ($('#applied_coupon_code').val() || '');
                }

                function setPesapalUiState(state, message) {
                    const $loading = $('#pesapalLoadingState');
                    const $error = $('#pesapalErrorState');
                    const $frameWrap = $('#pesapalFrameWrap');

                    $loading.toggleClass('d-none', state !== 'loading');
                    $error.toggleClass('d-none', state !== 'error');
                    $frameWrap.toggleClass('d-none', state !== 'ready');

                    if (state === 'error' && message) {
                        $('#pesapalErrorText').text(message);
                    }
                }

                function initPesapalIframe(forceReload = false) {
                    if (!$('#pesapalEmbeddedPanel').length || effectivePrice <= 0) {
                        return;
                    }

                    const orderKey = currentOrderKey();
                    if (!forceReload && loadedPesapalKey === orderKey && $('#pesapalCheckoutIframe').attr('src')) {
                        setPesapalUiState('ready');
                        return;
                    }

                    if (pesapalLoading && !forceReload) {
                        return;
                    }

                    pesapalLoading = true;
                    $('#pesapalExternalLink').addClass('d-none').attr('href', '#');
                    setPesapalUiState('loading');

                    $.ajax({
                        url: $('#checkoutForm').attr('action'),
                        method: 'POST',
                        data: new FormData($('#checkoutForm')[0]),
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            pesapalLoading = false;
                            if (response.success && response.checkout_url) {
                                loadedPesapalKey = orderKey;
                                $('#pesapalCheckoutIframe').attr('src', response.checkout_url);
                                $('#pesapalExternalLink').attr('href', response.checkout_url).removeClass('d-none');
                                setPesapalUiState('ready');
                                startPesapalStatusPoll(response.order_tracking_id || '');
                            } else {
                                setPesapalUiState('error', response.message || 'Unable to generate Pesapal checkout URL.');
                            }
                        },
                        error: function(xhr) {
                            pesapalLoading = false;
                            let msg = 'Unable to connect to Pesapal right now. Please retry.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            setPesapalUiState('error', msg);
                        }
                    });
                }

                function updateGatewayVisibility() {
                    const selected = $('input[name="payment_method"]:checked').val();

                    if (effectivePrice <= 0) {
                        $('#pesapalEmbeddedPanel').addClass('d-none');
                        $('#offlinePaymentSection').addClass('d-none');
                        $('#transaction_id, #receipt_file').prop('required', false);
                        $('#pesapalSidebarHint').addClass('d-none');
                        $('#mainSubmitWrap, #sidebarSubmitBtn').removeClass('d-none');
                        return;
                    }

                    if (selected === 'pesapal') {
                        $('#offlinePaymentSection').addClass('d-none').removeClass('is-visible');
                        $('#transaction_id, #receipt_file').prop('required', false);
                        $('#pesapalEmbeddedPanel').removeClass('d-none');
                        $('#pesapalSidebarHint').removeClass('d-none');
                        $('#mainSubmitWrap, #sidebarSubmitBtn').addClass('d-none');
                        initPesapalIframe(false);
                    } else if (selected === 'offline') {
                        $('#pesapalEmbeddedPanel').addClass('d-none');
                        $('#pesapalSidebarHint').addClass('d-none');
                        $('#offlinePaymentSection').removeClass('d-none').addClass('is-visible');
                        $('#transaction_id, #receipt_file').prop('required', true);
                        $('#mainSubmitWrap, #sidebarSubmitBtn').removeClass('d-none');
                    } else {
                        $('#pesapalEmbeddedPanel').addClass('d-none');
                        $('#pesapalSidebarHint').addClass('d-none');
                        $('#offlinePaymentSection').addClass('d-none').removeClass('is-visible');
                        $('#transaction_id, #receipt_file').prop('required', false);
                        $('#mainSubmitWrap, #sidebarSubmitBtn').removeClass('d-none');
                    }
                }

                // Break cleanly out of the Pesapal iframe once callback redirects back to our own origin.
                $('#pesapalCheckoutIframe').on('load', function() {
                    checkPesapalOrderStatus();
                    try {
                        const frameHref = this.contentWindow && this.contentWindow.location ? this.contentWindow.location.href : '';
                        if (frameHref && frameHref.indexOf(window.location.origin) === 0) {
                            if (frameHref.indexOf('/enrollment/success') !== -1 || (frameHref.indexOf('/courses/') !== -1 && frameHref.indexOf('/checkout') === -1)) {
                                stopPesapalStatusPoll();
                                window.top.location.href = frameHref;
                            }
                        }
                    } catch (e) {
                        // Cross-origin Pesapal URL while customer interacts with checkout; expected.
                    }
                });

                $('#pesapalReloadBtn, #pesapalRetryBtn').on('click', function() {
                    initPesapalIframe(true);
                });

                $('#copyLipaNambaBtn').on('click', function() {
                    const $btn = $(this);
                    const number = $btn.data('number') || '61237100';
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(String(number));
                    }
                    const originalHtml = $btn.html();
                    $btn.html('<i class="fa-solid fa-check me-1"></i> Copied!');
                    setTimeout(function() {
                        $btn.html(originalHtml);
                    }, 1800);
                });

                $('#toggleQrPdfPreviewBtn').on('click', function() {
                    const $wrap = $('#qrPdfPreviewWrap');
                    $wrap.toggleClass('d-none');
                });

                $('input[name="payment_method"]').on('change', function() {
                    updateGatewayVisibility();
                });

                // Initialize default gateway (auto-loads Pesapal iframe if enabled & selected)
                updateGatewayVisibility();

                $('#apply_coupon_btn').on('click', function(e) {
                    e.preventDefault();
                    const code = ($('#coupon_code_input').val() || '').trim();
                    const courseId = '{{ $course->id }}';
                    const $message = $('#coupon-message');
                    const $btn = $(this);

                    if (!code) {
                        $message.html('<span class="text-danger">Please enter a coupon code.</span>');
                        return;
                    }

                    $btn.prop('disabled', true).text('Checking...');
                    $message.html('');

                    $.ajax({
                        url: '{{ route('coupons.validate') }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            code: code,
                            course_id: courseId
                        },
                        success: function(response) {
                            $btn.prop('disabled', false).text('Apply');
                            if (response.success) {
                                $message.html('<span class="text-success">Coupon applied successfully!</span>');
                                $('#applied_coupon_code').val(response.coupon.code);
                                $('#summary-discount-row').removeClass('d-none');
                                $('#summary-discount-val').html('<strong>-' + response.discount_amount_formatted + '</strong>');
                                $('#summary-total-price').text(response.final_price_formatted);
                                effectivePrice = parseFloat(response.final_price);
                                loadedPesapalKey = null;

                                if (effectivePrice <= 0) {
                                    $('.payment-method').slideUp();
                                    $('#submitBtn, #sidebarSubmitBtn').html('{{ __('frontend.complete_enrollment') }} <i class="fa-solid fa-arrow-up-right"></i>');
                                } else {
                                    $('.payment-method').slideDown();
                                    $('#submitBtn, #sidebarSubmitBtn').html('{{ __('frontend.proceed_to_payment') }} <i class="fa-solid fa-arrow-up-right"></i>');
                                }
                                updateGatewayVisibility();
                            } else {
                                $message.html('<span class="text-danger">' + (response.message || 'Invalid coupon code.') + '</span>');
                                resetCoupon();
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false).text('Apply');
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Invalid coupon code.';
                            $message.html('<span class="text-danger">' + msg + '</span>');
                            resetCoupon();
                        }
                    });
                });

                function resetCoupon() {
                    $('#applied_coupon_code').val('');
                    $('#summary-discount-row').addClass('d-none');
                    $('#summary-discount-val').html('<strong>-</strong>');
                    $('#summary-total-price').text('{{ $formattedTotal }}');
                    effectivePrice = basePrice;
                    loadedPesapalKey = null;
                    $('.payment-method').slideDown();
                    updateGatewayVisibility();
                }

                $('#checkoutForm').on('submit', function(e) {
                    const paymentMethod = $('input[name="payment_method"]:checked').val();

                    if (effectivePrice > 0 && paymentMethod === 'pesapal') {
                        e.preventDefault();
                        initPesapalIframe(true);
                    } else if (effectivePrice > 0 && (paymentMethod === 'stripe' || paymentMethod === 'flutterwave' || paymentMethod === 'paypal')) {
                        e.preventDefault();
                        handleRedirectGatewayCheckout();
                    }
                });

                function handleRedirectGatewayCheckout() {
                    const $btns = $('#submitBtn, #sidebarSubmitBtn');
                    $btns.prop('disabled', true).html('{{ __('frontend.processing') }} <i class="fa fa-spinner fa-spin"></i>');

                    $.ajax({
                        url: $('#checkoutForm').attr('action'),
                        method: 'POST',
                        data: new FormData($('#checkoutForm')[0]),
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success && response.checkout_url) {
                                window.location.href = response.checkout_url;
                            } else {
                                $btns.prop('disabled', false).html('{{ __('frontend.proceed_to_payment') }} <i class="fa-solid fa-arrow-up-right"></i>');
                                ToastMagic.error(response.message || 'An error occurred. Please try again.');
                            }
                        },
                        error: function(xhr) {
                            $btns.prop('disabled', false).html('{{ __('frontend.proceed_to_payment') }} <i class="fa-solid fa-arrow-up-right"></i>');
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'An error occurred. Please try again.';
                            if (typeof ToastMagic !== 'undefined') {
                                ToastMagic.error(msg);
                            } else {
                                alert(msg);
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
