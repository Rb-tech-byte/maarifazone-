<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Payment Model
 *
 * Represents an online payment for a course or bundle in Maarifazone.
 *
 * @package App\Models
 * @author Renatus Bernardo
 */
class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'bundle_id',
        'enrollment_id',
        'payment_method',
        'amount',
        'subtotal',
        'tax_amount',
        'tax_rate',
        'platform_commission',
        'instructor_earning',
        'commission_processed',
        'status',
        'verification_status',
        'stripe_payment_intent_id',
        'stripe_client_secret',
        'flutterwave_transaction_id',
        'flutterwave_tx_ref',
        'paypal_order_id',
        'paypal_payer_id',
        'paypal_payment_id',
        'pesapal_order_tracking_id',
        'pesapal_merchant_reference',
        'pesapal_confirmation_code',
        'pesapal_payment_account',
        'receipt_file',
        'payment_date',
        'transaction_id',
        'notes',
        'coupon_id',
        'discount_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'platform_commission' => 'decimal:2',
        'instructor_earning' => 'decimal:2',
        'commission_processed' => 'boolean',
        'discount_amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Booted.
     */
    protected static function booted()
    {
        static::creating(function (Payment $payment) {
            $attributes = $payment->getAttributes();
            $payment->status = $payment->status ?: self::STATUS_PENDING;

            if (! array_key_exists('verification_status', $attributes)) {
                $payment->verification_status = $payment->status === self::STATUS_COMPLETED
                    ? 'verified'
                    : 'pending';
            } elseif ($payment->verification_status === 'verified' && $payment->status === self::STATUS_PENDING) {
                $payment->status = self::STATUS_COMPLETED;
            }

            if ($payment->status === self::STATUS_COMPLETED) {
                $payment->verification_status = 'verified';
                $payment->payment_date = $payment->payment_date ?? now()->toDateString();
            }
        });

        static::updating(function (Payment $payment) {
            if ($payment->verification_status === 'verified' && $payment->status === self::STATUS_PENDING) {
                $payment->status = self::STATUS_COMPLETED;
            } elseif ($payment->isDirty('verification_status') && $payment->verification_status === 'rejected' && $payment->status === self::STATUS_PENDING) {
                $payment->status = self::STATUS_FAILED;
            }

            if ($payment->status === self::STATUS_COMPLETED) {
                if ($payment->verification_status !== 'verified') {
                    $payment->verification_status = 'verified';
                }
                if (! $payment->payment_date) {
                    $payment->payment_date = now()->toDateString();
                }
            }
        });

        static::saved(function (Payment $payment) {
            if (($payment->isDirty('status') || $payment->wasChanged('status')) && $payment->status === self::STATUS_COMPLETED) {
                if ($payment->coupon_id) {
                    \App\Models\CouponUsage::firstOrCreate([
                        'coupon_id' => $payment->coupon_id,
                        'user_id' => $payment->user_id ?? ($payment->enrollment?->user_id),
                        'enrollment_id' => $payment->enrollment_id,
                    ], [
                        'discount_amount' => $payment->discount_amount ?? 0.00,
                    ]);

                    $coupon = \App\Models\Coupon::find($payment->coupon_id);
                    if ($coupon) {
                        $coupon->increment('used_count');
                    }
                }

                // Activate linked enrollment if not already approved
                if ($payment->enrollment_id) {
                    $enrollment = $payment->enrollment;
                    if ($enrollment && $enrollment->status !== \App\Models\Enrollment::STATUS_APPROVED) {
                        $enrollment->update(['status' => \App\Models\Enrollment::STATUS_APPROVED]);
                    }
                }

                // Settle instructor and platform commissions
                if (class_exists(\App\Services\RevenueShareService::class)) {
                    app(\App\Services\RevenueShareService::class)->settlePaymentCommission($payment);
                }
            }
        });
    }

    const PAYMENT_METHOD_OFFLINE = 'offline';
    const PAYMENT_METHOD_STRIPE = 'stripe';
    const PAYMENT_METHOD_FLUTTERWAVE = 'flutterwave';
    const PAYMENT_METHOD_PAYPAL = 'paypal';
    const PAYMENT_METHOD_PESAPAL = 'pesapal';

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    /**
     * Eloquent belongsTo relationship for Enrollment.
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Eloquent belongsTo relationship for Coupon.
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Eloquent belongsTo relationship for Bundle.
     */
    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class);
    }

    /**
     * Eloquent belongsTo relationship for User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor for computed Receipt url attribute.
     */
    public function getReceiptUrlAttribute(): ?string
    {
        if (!$this->receipt_file) {
            return null;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        if (!$disk->exists($this->receipt_file)) {
            return null;
        }

        return $disk->url($this->receipt_file);
    }

    /**
     * Check condition: Is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check condition: Is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check condition: Is offline.
     */
    public function isOffline(): bool
    {
        return $this->payment_method === self::PAYMENT_METHOD_OFFLINE;
    }

    /**
     * Check condition: Is stripe.
     */
    public function isStripe(): bool
    {
        return $this->payment_method === self::PAYMENT_METHOD_STRIPE;
    }

    /**
     * Check condition: Is flutterwave.
     */
    public function isFlutterwave(): bool
    {
        return $this->payment_method === self::PAYMENT_METHOD_FLUTTERWAVE;
    }

    /**
     * Check condition: Is paypal.
     */
    public function isPaypal(): bool
    {
        return $this->payment_method === self::PAYMENT_METHOD_PAYPAL;
    }

    /**
     * Check condition: Is Pesapal.
     */
    public function isPesapal(): bool
    {
        return $this->payment_method === self::PAYMENT_METHOD_PESAPAL;
    }
}
