<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Enrollment Model
 *
 * Represents an online course enrollment for a scholar in Maarifazone.
 *
 * @package App\Models
 * @author Renatus Bernardo
 */
class Enrollment extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'course_topic_id',
        'payment_id',
        'status',
        'start_date',
        'end_date',
        'enrolled_at',
    ];

    /**
     * Booted.
     */
    protected static function booted()
    {
        static::updated(function ($enrollment) {
            if ($enrollment->wasChanged('status') && $enrollment->status === self::STATUS_APPROVED) {
                // Pre-load payment to ensure we have bundle_id
                $enrollment->loadMissing('payment');

                if ($enrollment->payment && $enrollment->payment->bundle_id) {
                    $bundleId = $enrollment->payment->bundle_id;
                    $userId = $enrollment->user_id;
                    $paymentId = $enrollment->payment_id;

                    // Fetch all course IDs in this bundle
                    $courseIds = \Illuminate\Support\Facades\DB::table('bundle_courses')
                        ->where('bundle_id', $bundleId)
                        ->pluck('course_id');

                    // Wrap in withoutEvents to prevent infinite recursion
                    static::withoutEvents(function () use ($userId, $courseIds, $paymentId, $enrollment, $bundleId) {
                        self::where('user_id', $userId)
                            ->whereIn('course_id', $courseIds)
                            ->where('status', self::STATUS_PENDING)
                            ->where('id', '!=', $enrollment->id)
                            ->update([
                                'status' => self::STATUS_APPROVED,
                                'enrolled_at' => now(),
                                'payment_id' => $paymentId,
                            ]);

                        // Complete the BundleEnrollment record if it exists
                        \App\Models\BundleEnrollment::updateOrCreate(
                            ['user_id' => $userId, 'bundle_id' => $bundleId],
                            [
                                'status' => 'approved',
                                'enrolled_at' => now(),
                                'payment_id' => $paymentId,
                            ]
                        );
                    });
                }
            }
        });
    }

    protected $casts = [
        'enrolled_at' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Query scope to filter records by Completed.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Accessor for computed Completion date attribute.
     */
    public function getCompletionDateAttribute(): ?string
    {
        return $this->updated_at?->format('M d, Y');
    }

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Eloquent belongsTo relationship for User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Eloquent belongsTo relationship for Course.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Eloquent belongsTo relationship for specific Topic (when enrolled per-topic).
     */
    public function courseTopic(): BelongsTo
    {
        return $this->belongsTo(CourseTopic::class, 'course_topic_id');
    }

    /**
     * Eloquent belongsTo relationship for Payment.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Check condition: Is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check condition: Is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check condition: Is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Eloquent hasMany relationship for Lesson progress.
     */
    public function lessonProgress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }
}
