<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGatewaySetting;
use App\Services\PaymentGatewayService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Payment Gateway Controller
 *
 * This controller handles the management of payment gateways.
 *
 * @package App\Http\Controllers\Admin
 * @author Renatus Bernardo
 */
class PaymentGatewayController extends Controller
{
    /**
     * Display a listing of the resource records.
     */
    public function index(): View
    {
        $gateways = PaymentGatewaySetting::orderBy('type')->orderBy('name')->get();

        $credentialFieldMap = [
            'stripe' => ['key', 'secret'],
            'flutterwave' => ['public_key', 'secret_key'],
            'paypal' => ['client_id', 'client_secret', 'mode'],
            'pesapal' => ['consumer_key', 'consumer_secret', 'ipn_id', 'mode'],
        ];

        return view('admin.settings.gateways.index', compact('gateways', 'credentialFieldMap'));
    }

    /**
     * Validate and update the specified resource record.
     */
    public function update(Request $request, string $identifier): RedirectResponse
    {
        $gateway = PaymentGatewaySetting::where('identifier', $identifier)->firstOrFail();

        $validated = $request->validate([
            'is_enabled' => ['nullable', 'boolean'],
            'offline_instructions' => ['nullable', 'string'],
            'credentials' => ['nullable', 'array'],
            'qr_pdf_file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $gateway->is_enabled = (bool) ($validated['is_enabled'] ?? false);

        if ($gateway->type === 'offline') {
            $gateway->offline_instructions = $validated['offline_instructions'] ?? $gateway->offline_instructions;

            $existingCreds = is_array($gateway->credentials) ? $gateway->credentials : [];
            $incomingCreds = is_array($validated['credentials'] ?? null) ? $validated['credentials'] : [];

            $mergedCreds = array_merge([
                'lipa_namba' => PaymentGatewaySetting::DEFAULT_LIPA_NAMBA,
                'merchant_name' => PaymentGatewaySetting::DEFAULT_MERCHANT_NAME,
                'provider' => PaymentGatewaySetting::DEFAULT_PROVIDER,
                'qr_pdf' => PaymentGatewaySetting::DEFAULT_QR_PDF,
                'qr_image' => PaymentGatewaySetting::DEFAULT_QR_IMAGE,
            ], $existingCreds);

            foreach (['lipa_namba', 'merchant_name', 'provider'] as $field) {
                if (array_key_exists($field, $incomingCreds) && is_string($incomingCreds[$field]) && trim($incomingCreds[$field]) !== '') {
                    $mergedCreds[$field] = trim($incomingCreds[$field]);
                }
            }

            if ($request->hasFile('qr_pdf_file')) {
                $storedPath = $request->file('qr_pdf_file')->store('payment-gateways', 'public');
                $mergedCreds['qr_pdf'] = 'storage/' . ltrim($storedPath, '/');
            }

            $gateway->credentials = $mergedCreds;
        } else {
            $credentials = $validated['credentials'] ?? [];
            $gateway->credentials = collect($credentials)
                ->map(fn($value) => is_string($value) ? trim($value) : $value)
                ->filter(fn($value) => $value !== null && $value !== '')
                ->toArray();
        }

        $gateway->save();

        app(PaymentGatewayService::class)->refreshCache();

        ToastMagic::success('Gateway settings updated successfully.');

        return redirect()->route('admin.settings.gateways.index');
    }
}
