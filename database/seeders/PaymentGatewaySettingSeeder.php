<?php

namespace Database\Seeders;

use App\Models\PaymentGatewaySetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

/**
 * Payment gateway setting seeder — seeds active gateways and offline Lipa Namba QR PDF.
 *
 * @author Renatus Bernardo
 */
class PaymentGatewaySettingSeeder extends Seeder
{
    /**
     * Execute the database seeder.
     */
    public function run(): void
    {
        $this->ensureQrPdfPublished();

        $stripeKey = trim((string) env('STRIPE_KEY', ''));
        $stripeSecret = trim((string) env('STRIPE_SECRET', ''));

        $flwPublic = trim((string) env('FLUTTERWAVE_PUBLIC_KEY', ''));
        $flwSecret = trim((string) env('FLUTTERWAVE_SECRET_KEY', ''));

        $paypalId = trim((string) env('PAYPAL_CLIENT_ID', ''));
        $paypalSecret = trim((string) env('PAYPAL_CLIENT_SECRET', ''));

        $gateways = [
            [
                'identifier' => 'pesapal',
                'name' => 'Pesapal',
                'type' => 'online',
                'is_enabled' => true,
                'credentials' => [
                    'consumer_key' => env('PESAPAL_CONSUMER_KEY') ?: 'ngW+UEcnDhltUc5fxPfrCD987xMh3Lx8',
                    'consumer_secret' => env('PESAPAL_CONSUMER_SECRET') ?: 'q27RChYs5UkypdcNYKzuUw460Dg=',
                    'ipn_id' => env('PESAPAL_IPN_ID', ''),
                    'mode' => env('PESAPAL_MODE', 'sandbox'),
                ],
            ],
            [
                'identifier' => 'offline',
                'name' => 'Lipa Namba / TANQR (Offline Payment)',
                'type' => 'offline',
                'is_enabled' => true,
                'credentials' => [
                    'lipa_namba' => PaymentGatewaySetting::DEFAULT_LIPA_NAMBA,
                    'merchant_name' => PaymentGatewaySetting::DEFAULT_MERCHANT_NAME,
                    'provider' => PaymentGatewaySetting::DEFAULT_PROVIDER,
                    'qr_pdf' => PaymentGatewaySetting::DEFAULT_QR_PDF,
                    'qr_image' => PaymentGatewaySetting::DEFAULT_QR_IMAGE,
                ],
                'offline_instructions' => "Scan the TIPS / Selcom Pay TANQR code or pay directly to Lipa Namba 61237100 (Account Name: KOMANYA LUCAS NGONZELA) using M-Pesa, Mixx by Yas (Tigo Pesa), Airtel Money, HaloPesa, or any banking app. Enter your transaction reference and upload your receipt below.",
            ],
            [
                'identifier' => 'stripe',
                'name' => 'Stripe',
                'type' => 'online',
                'is_enabled' => $stripeKey !== '' && $stripeSecret !== '',
                'credentials' => [
                    'key' => $stripeKey,
                    'secret' => $stripeSecret,
                ],
            ],
            [
                'identifier' => 'flutterwave',
                'name' => 'Flutterwave',
                'type' => 'online',
                'is_enabled' => $flwPublic !== '' && $flwSecret !== '',
                'credentials' => [
                    'public_key' => $flwPublic,
                    'secret_key' => $flwSecret,
                ],
            ],
            [
                'identifier' => 'paypal',
                'name' => 'PayPal',
                'type' => 'online',
                'is_enabled' => $paypalId !== '' && $paypalSecret !== '',
                'credentials' => [
                    'client_id' => $paypalId,
                    'client_secret' => $paypalSecret,
                    'mode' => env('PAYPAL_MODE', 'sandbox'),
                ],
            ],
        ];

        foreach ($gateways as $gateway) {
            PaymentGatewaySetting::updateOrCreate(
                ['identifier' => $gateway['identifier']],
                [
                    'name' => $gateway['name'],
                    'type' => $gateway['type'],
                    'credentials' => $gateway['credentials'] ?? null,
                    'is_enabled' => (bool) $gateway['is_enabled'],
                    'offline_instructions' => $gateway['offline_instructions'] ?? null,
                ]
            );
        }

        Cache::forget('payment_gateway_settings');
    }

    /**
     * Ensure 61237100_QR-2.pdf is copied into public/assets/payment and storage/app/public/payment-gateways.
     */
    protected function ensureQrPdfPublished(): void
    {
        $sourcePdf = base_path('61237100_QR-2.pdf');
        if (! File::exists($sourcePdf)) {
            return;
        }

        $publicDir = public_path('assets/payment');
        File::ensureDirectoryExists($publicDir);
        $publicTarget = $publicDir . DIRECTORY_SEPARATOR . '61237100_QR-2.pdf';
        if (! File::exists($publicTarget)) {
            File::copy($sourcePdf, $publicTarget);
        }

        $storageDir = storage_path('app/public/payment-gateways');
        File::ensureDirectoryExists($storageDir);
        $storageTarget = $storageDir . DIRECTORY_SEPARATOR . '61237100_QR-2.pdf';
        if (! File::exists($storageTarget)) {
            File::copy($sourcePdf, $storageTarget);
        }
    }
}
