<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Payment Gateway Setting Model
 *
 * This model represents a setting for a payment gateway.
 *
 * @package App\Models
 * @author Renatus Bernardo
 */
class PaymentGatewaySetting extends Model
{
    public const DEFAULT_LIPA_NAMBA = '61237100';
    public const DEFAULT_MERCHANT_NAME = 'KOMANYA LUCAS NGONZELA';
    public const DEFAULT_PROVIDER = 'TIPS · Selcom Pay TANQR';
    public const DEFAULT_QR_PDF = 'assets/payment/61237100_QR-2.pdf';
    public const DEFAULT_QR_IMAGE = 'assets/payment/61237100_QR-2.png';

    protected $fillable = [
        'identifier',
        'name',
        'type',
        'credentials',
        'is_enabled',
        'offline_instructions',
    ];

    protected $casts = [
        'credentials' => 'array',
        'is_enabled' => 'bool',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('payment_gateway_settings');
        });

        static::deleted(function () {
            Cache::forget('payment_gateway_settings');
        });
    }

    public function getLipaNambaAttribute(): string
    {
        $creds = is_array($this->credentials) ? $this->credentials : [];

        return trim((string) ($creds['lipa_namba'] ?? self::DEFAULT_LIPA_NAMBA)) ?: self::DEFAULT_LIPA_NAMBA;
    }

    public function getMerchantNameAttribute(): string
    {
        $creds = is_array($this->credentials) ? $this->credentials : [];

        return trim((string) ($creds['merchant_name'] ?? self::DEFAULT_MERCHANT_NAME)) ?: self::DEFAULT_MERCHANT_NAME;
    }

    public function getProviderLabelAttribute(): string
    {
        $creds = is_array($this->credentials) ? $this->credentials : [];

        return trim((string) ($creds['provider'] ?? self::DEFAULT_PROVIDER)) ?: self::DEFAULT_PROVIDER;
    }

    public function getQrPdfUrlAttribute(): string
    {
        $creds = is_array($this->credentials) ? $this->credentials : [];
        $path = trim((string) ($creds['qr_pdf'] ?? self::DEFAULT_QR_PDF)) ?: self::DEFAULT_QR_PDF;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        if (str_starts_with($path, 'storage/') || str_starts_with($path, 'assets/')) {
            return asset($path);
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    public function getQrImageUrlAttribute(): string
    {
        $creds = is_array($this->credentials) ? $this->credentials : [];
        $path = trim((string) ($creds['qr_image'] ?? self::DEFAULT_QR_IMAGE)) ?: self::DEFAULT_QR_IMAGE;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        if (str_starts_with($path, 'storage/') || str_starts_with($path, 'assets/')) {
            return asset($path);
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}
