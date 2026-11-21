@extends('layouts.admin')

@section('title', 'Payment Gateways')
@section('breadcrumb')
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></div>
        <div class="breadcrumb-item active">Payment Gateways</div>
    </div>
@endsection

@section('main-content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="mb-2 mb-md-0">Payment Gateway Configuration</h4>
                    <div class="d-flex align-items-center">
                        <span class="badge badge-primary mr-3">{{ $gateways->where('is_enabled', true)->count() }} active / {{ $gateways->count() }} total</span>
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary"><i
                                class="fas fa-arrow-left"></i> Back to Settings</a>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Only gateways marked <strong>Enabled</strong> appear on the Scholar checkout page. Disabled gateways are hidden automatically.
                    </p>

                    <div class="row">
                        @foreach ($gateways as $gateway)
                            <div class="col-lg-6 mb-4">
                                <div class="card border h-100 shadow-sm">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0">{{ $gateway->name }}</h5>
                                            <small class="text-muted text-uppercase">{{ $gateway->type }} gateway</small>
                                        </div>
                                        <span
                                            class="badge badge-{{ $gateway->is_enabled ? 'success' : 'secondary' }}">{{ $gateway->is_enabled ? 'Enabled (Visible at Checkout)' : 'Disabled (Hidden)' }}</span>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('admin.settings.gateways.update', $gateway->identifier) }}"
                                            method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')

                                            <div class="form-group">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input"
                                                        id="enable-{{ $gateway->identifier }}" name="is_enabled"
                                                        value="1" {{ $gateway->is_enabled ? 'checked' : '' }}>
                                                    <label class="custom-control-label font-weight-bold"
                                                        for="enable-{{ $gateway->identifier }}">Enable
                                                        {{ $gateway->name }}</label>
                                                </div>
                                            </div>

                                            @if ($gateway->type === 'online')
                                                <div class="border rounded p-3 mb-3">
                                                    <h6 class="mb-3">Credentials</h6>
                                                    @php($credentials = $gateway->credentials ?? [])
                                                    @php($fields = $credentialFieldMap[$gateway->identifier] ?? array_keys($credentials))
                                                    @forelse($fields as $key)
                                                        <div class="form-group">
                                                            <label
                                                                class="text-uppercase small font-weight-bold">{{ str_replace('_', ' ', $key) }}</label>
                                                            @if ($key === 'mode')
                                                                @php($modeValue = strtolower((string) ($credentials[$key] ?? 'sandbox')))
                                                                <select name="credentials[mode]" class="form-control">
                                                                    <option value="sandbox" {{ $modeValue !== 'live' ? 'selected' : '' }}>Sandbox (Testing)</option>
                                                                    <option value="live" {{ $modeValue === 'live' ? 'selected' : '' }}>Live (Production)</option>
                                                                </select>
                                                                <small class="form-text text-muted">Choose sandbox for testing or live for production transactions.</small>
                                                            @else
                                                                <input type="text" name="credentials[{{ $key }}]"
                                                                    value="{{ $credentials[$key] ?? '' }}" class="form-control"
                                                                    placeholder="Enter {{ str_replace('_', ' ', $key) }}">
                                                                @if ($key === 'ipn_id')
                                                                    <small class="form-text text-muted">Optional IPN Notification UUID; left blank, LMS will auto-register via Pesapal API 3.0.</small>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    @empty
                                                        <div class="form-group">
                                                            <label
                                                                class="text-uppercase small font-weight-bold">Credential</label>
                                                            <input type="text" name="credentials[value]" value=""
                                                                class="form-control" placeholder="Enter credential value">
                                                        </div>
                                                    @endforelse
                                                    @if ($gateway->identifier === 'pesapal')
                                                        <div class="alert alert-light border small mb-0">
                                                            <div><strong>Callback URL:</strong> <code>{{ route('payment.pesapal.callback') }}</code></div>
                                                            <div><strong>IPN Listener URL:</strong> <code>{{ route('payment.pesapal.ipn') }}</code></div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="border rounded p-3 mb-3 bg-light">
                                                    <h6 class="mb-3"><i class="fas fa-qrcode mr-1"></i> Lipa Namba &amp; TANQR Configuration</h6>
                                                    <div class="form-row">
                                                        <div class="form-group col-md-5">
                                                            <label class="small font-weight-bold text-uppercase">Lipa Namba</label>
                                                            <input type="text" name="credentials[lipa_namba]"
                                                                value="{{ $gateway->lipa_namba }}" class="form-control font-weight-bold"
                                                                placeholder="61237100">
                                                        </div>
                                                        <div class="form-group col-md-7">
                                                            <label class="small font-weight-bold text-uppercase">Merchant / Account Name</label>
                                                            <input type="text" name="credentials[merchant_name]"
                                                                value="{{ $gateway->merchant_name }}" class="form-control"
                                                                placeholder="KOMANYA LUCAS NGONZELA">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold text-uppercase">Network / Provider Label</label>
                                                        <input type="text" name="credentials[provider]"
                                                            value="{{ $gateway->provider_label }}" class="form-control"
                                                            placeholder="TIPS · Selcom Pay TANQR">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small font-weight-bold text-uppercase">QR Code Poster (PDF)</label>
                                                        <div class="d-flex align-items-center justify-content-between flex-wrap mb-2 p-2 bg-white border rounded">
                                                            <div class="d-flex align-items-center">
                                                                <img src="{{ $gateway->qr_image_url }}" alt="TANQR" style="width: 48px; height: 48px; object-fit: contain;" class="mr-2 border rounded p-1">
                                                                <div>
                                                                    <div class="small font-weight-bold text-dark">Active QR Poster: 61237100_QR-2.pdf</div>
                                                                    <a href="{{ $gateway->qr_pdf_url }}" target="_blank" rel="noopener" class="small text-primary font-weight-bold">
                                                                        <i class="fas fa-file-pdf mr-1"></i> View / Download Seeded PDF
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <input type="file" name="qr_pdf_file" accept=".pdf" class="form-control-file">
                                                        <small class="text-muted">Leave blank to keep the seeded <code>61237100_QR-2.pdf</code> TANQR poster.</small>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="instructions-{{ $gateway->identifier }}">Offline
                                                        Instructions</label>
                                                    <textarea name="offline_instructions" id="instructions-{{ $gateway->identifier }}" rows="4" class="form-control"
                                                        placeholder="Provide instructions for manual/offline payments.">{{ old('offline_instructions', $gateway->offline_instructions) }}</textarea>
                                                    <small class="text-muted">Displayed alongside the QR code &amp; Lipa Namba at checkout.</small>
                                                </div>
                                            @endif

                                            <div class="text-right">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save"></i> Save Changes
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
