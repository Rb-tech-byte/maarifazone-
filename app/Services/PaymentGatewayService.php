<?php

namespace App\Services;

use App\Models\PaymentGatewaySetting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Payment Gateway Service
 *
 * This service is responsible for managing payment gateways.
 *
 * @package App\Services
 * @author Renatus Bernardo
 */
class PaymentGatewayService
{
    protected const CACHE_KEY = 'payment_gateway_settings';
    protected const CACHE_TTL = 300;

    protected array $fallbackCredentials = [
        'stripe' => ['key' => 'STRIPE_KEY', 'secret' => 'STRIPE_SECRET'],
        'flutterwave' => ['public_key' => 'FLUTTERWAVE_PUBLIC_KEY', 'secret_key' => 'FLUTTERWAVE_SECRET_KEY'],
        'paypal' => ['client_id' => 'PAYPAL_CLIENT_ID', 'client_secret' => 'PAYPAL_CLIENT_SECRET', 'mode' => 'PAYPAL_MODE'],
        'pesapal' => ['consumer_key' => 'PESAPAL_CONSUMER_KEY', 'consumer_secret' => 'PESAPAL_CONSUMER_SECRET', 'ipn_id' => 'PESAPAL_IPN_ID', 'mode' => 'PESAPAL_MODE'],
    ];

    /**
     * All.
     */
    public function all(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return PaymentGatewaySetting::orderBy('type')->orderBy('name')->get();
        });
    }

    /**
     * Online gateways (only those enabled in Admin).
     */
    public function onlineGateways(): Collection
    {
        return $this->all()->where('type', 'online')->where('is_enabled', true)->values();
    }

    /**
     * Offline gateway record.
     */
    public function offlineGateway(): ?PaymentGatewaySetting
    {
        return $this->all()->firstWhere('identifier', 'offline');
    }

    /**
     * Enabled offline gateway (returns null when disabled in Admin).
     */
    public function enabledOfflineGateway(): ?PaymentGatewaySetting
    {
        $gateway = $this->offlineGateway();

        return ($gateway && $gateway->is_enabled) ? $gateway : null;
    }

    /**
     * Offline instructions.
     */
    public function offlineInstructions(): ?string
    {
        return optional($this->offlineGateway())->offline_instructions;
    }

    /**
     * Check condition: Is enabled.
     */
    public function isEnabled(string $identifier): bool
    {
        $gateway = $this->gateway($identifier);

        return $gateway ? $gateway->is_enabled : false;
    }

    /**
     * Gateway.
     */
    public function gateway(string $identifier): ?PaymentGatewaySetting
    {
        return $this->all()->firstWhere('identifier', $identifier);
    }

    /**
     * Credentials for.
     */
    public function credentialsFor(string $identifier): array
    {
        $gateway = $this->gateway($identifier);

        $credentials = $gateway?->credentials ?? [];

        $fallback = collect($this->fallbackCredentials[$identifier] ?? [])
            ->mapWithKeys(function ($envKey, $credKey) {
                $value = env($envKey);
                return $value !== null ? [$credKey => $value] : [];
            })->toArray();

        return array_merge($fallback, $credentials);
    }

    /**
     * Refresh cache.
     */
    public function refreshCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
