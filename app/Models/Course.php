<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Course Model
 *
 * Represents an online course in Maarifazone.
 *
 * @package App\Models
 * @author Renatus Bernardo
 */
class Course extends Model
{
    protected $fillable = [
        'instructor_id',
        'assistant_instructor_id',
        'title',
        'course_type',
        'delivery_mode',
        'duration_label',
        'description',
        'featured_image',
        'intro_video',
        'visibility',
        'public_course',
        'max_students',
        'difficulty',
        'pricing_model',
        'regular_price',
        'sale_price',
        'price_currency',
        'has_discount',
        'discount_amount',
        'schedule_enabled',
        'drip_enabled',
        'schedule_date',
        'course_category_id',
        'course_language_id',
        'category',
        'language',
        'tags',
        'seo_title',
        'seo_description',
        'seo_tags',
        'marketing_copy',
        'requirements',
        'objectives',
        'status',
        'deleted_date',
        'approval_reason',
        'rejection_reason',
        'is_live_course',
    ];

    protected $casts = [
        'public_course'   => 'boolean',
        'schedule_enabled' => 'boolean',
        'drip_enabled'     => 'boolean',
        'schedule_date'    => 'datetime',
        'regular_price'    => 'decimal:2',
        'sale_price'       => 'decimal:2',
        'has_discount'     => 'boolean',
        'discount_amount'  => 'decimal:2',
        'max_students'     => 'integer',
        'deleted_date'     => 'datetime',
        'is_live_course'   => 'boolean',
        'marketing_copy'   => 'array',
    ];

    protected $appends = [
        'thumbnail',
        'price',
        'discounted_price',
        'rating',
        'reviews_count',
        'lessons_count',
        'students_count',
        'intro_video_url',
    ];

    /**
     * Get the instructor that owns the course.
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Assistant instructor (optional co-instructor).
     */
    public function assistantInstructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assistant_instructor_id');
    }

    /**
     * Co-instructors relation.
     */
    public function coInstructors(): HasMany
    {
        return $this->hasMany(CourseCoInstructor::class);
    }

    /**
     * Course FAQs.
     */
    public function faqs(): HasMany
    {
        return $this->hasMany(CourseFaq::class);
    }

    /**
     * Check if a user can manage the course curriculum.
     */
    public function canUserManageCurriculum(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isAdmin() || $user->role === 'admin') {
            return true;
        }

        return (int) $this->instructor_id === (int) $user->id
            || (int) $this->assistant_instructor_id === (int) $user->id;
    }

    /**
     * Check if a user can manage course pricing and team assignment.
     */
    public function canUserManagePricingAndTeam(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->isAdmin() || $user->role === 'admin';
    }

    /**
     * Course category.
     */
    public function courseCategory(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'course_category_id');
    }

    /**
     * Alias for course category relation.
     */
    public function categoryRelation(): BelongsTo
    {
        return $this->courseCategory();
    }

    /**
     * Course language.
     */
    public function courseLanguage(): BelongsTo
    {
        return $this->belongsTo(CourseLanguage::class, 'course_language_id');
    }

    /**
     * Get the topics for the course.
     */
    public function topics(): HasMany
    {
        return $this->hasMany(CourseTopic::class)->orderBy('order');
    }

    /**
     * Get the enrollments for the course.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the reviews for the course.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true)->orderBy('created_at', 'desc');
    }

    /**
     * Get the lessons for the course directly.
     */
    public function lessons()
    {
        return $this->hasManyThrough(Lesson::class, CourseTopic::class);
    }

    /**
     * Get the bundles that include this course.
     */
    public function bundles(): BelongsToMany
    {
        return $this->belongsToMany(Bundle::class, 'bundle_courses');
    }

    /**
     * Eloquent hasMany relationship for Coupons.
     */
    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    /**
     * Get live classes for this course.
     */
    public function liveClasses(): HasMany
    {
        return $this->hasMany(LiveClass::class)->orderBy('scheduled_at');
    }

    /**
     * Get the next upcoming live class.
     */
    public function nextLiveClass()
    {
        return $this->liveClasses()->upcoming()->first();
    }

    /**
     * Accessor for computed Thumbnail attribute.
     */
    public function getThumbnailAttribute()
    {
        if (! $this->featured_image) {
            return asset('assets/front/img/home-1/courses/courses-01.jpg');
        }
        if (str_starts_with($this->featured_image, 'http://') || str_starts_with($this->featured_image, 'https://')) {
            return $this->featured_image;
        }
        if (str_starts_with($this->featured_image, 'assets/')) {
            return asset($this->featured_image);
        }
        return asset('storage/' . ltrim($this->featured_image, '/'));
    }

    /**
     * Accessor for computed Price attribute.
     */
    public function getPriceAttribute()
    {
        return $this->regular_price;
    }

    /**
     * Accessor for computed Discounted price attribute.
     */
    public function getDiscountedPriceAttribute()
    {
        return $this->sale_price;
    }

    /**
     * Resolve the currency in which this course's regular_price and sale_price are stored.
     */
    public function getPriceCurrencyCode(): string
    {
        return normalize_currency_code($this->price_currency ?: base_currency_code(), base_currency_code());
    }

    /**
     * Convert a stored course price amount into the target (or active display) currency.
     */
    public function priceInCurrency($amount, ?string $targetCurrency = null): float
    {
        return convert_currency((float) ($amount ?? 0), $this->getPriceCurrencyCode(), $targetCurrency ?? currency_code());
    }

    /**
     * Format a stored course price amount in the target (or active display) currency.
     */
    public function formatPrice($amount, int $decimals = 2, string $style = 'code', ?string $targetCurrency = null): string
    {
        return currency_format((float) ($amount ?? 0), $decimals, $style, $this->getPriceCurrencyCode(), $targetCurrency);
    }

    /**
     * Accessor for computed Rating attribute.
     */
    public function getRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0.0;
    }

    /**
     * Accessor for computed Reviews count attribute.
     */
    public function getReviewsCountAttribute()
    {
        if (array_key_exists('reviews_count', $this->attributes)) {
            return $this->attributes['reviews_count'];
        }
        return $this->reviews()->count();
    }

    /**
     * Accessor for computed Lessons count attribute.
     */
    public function getLessonsCountAttribute()
    {
        if (array_key_exists('lessons_count', $this->attributes)) {
            return $this->attributes['lessons_count'];
        }
        return $this->lessons()->count();
    }

    /**
     * Accessor for computed Students count attribute.
     */
    public function getStudentsCountAttribute()
    {
        return $this->enrollments()->whereIn('status', ['approved', 'completed'])->count();
    }

    /**
     * Accessor for computed Intro video url attribute.
     */
    public function getIntroVideoUrlAttribute()
    {
        if (! $this->intro_video) {
            return null;
        }

        return url('/api/video/stream?path=' . urlencode($this->intro_video));
    }

    /**
     * Maarifazone is 100% online.
     */
    public function isOnline(): bool
    {
        return true;
    }

    public function isFaceToFace(): bool
    {
        return false;
    }

    public function isHybrid(): bool
    {
        return false;
    }

    public function requiresApplicantForm(): bool
    {
        return false;
    }
}
