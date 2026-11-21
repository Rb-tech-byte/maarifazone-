<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Course Topic Model
 *
 * Represents an online learning module/topic within a course.
 *
 * @package App\Models
 * @author Renatus Bernardo
 */
class CourseTopic extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'description',
        'is_free',
        'price',
        'order',
        'drip_days',
        'drip_date',
    ];

    protected $casts = [
        'is_free' => 'boolean',
        'price' => 'decimal:2',
        'order' => 'integer',
        'drip_days' => 'integer',
        'drip_date' => 'datetime',
    ];

    /**
     * Determine if topic is open for free access.
     */
    public function isFree(): bool
    {
        return (bool) $this->is_free;
    }

    /**
     * Determine if topic has its own standalone purchase price.
     */
    public function hasStandalonePrice(): bool
    {
        return !$this->is_free && (float) $this->price > 0;
    }

    /**
     * Get the enrollments specifically for this topic.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'course_topic_id');
    }

    /**
     * Get the course that owns the topic.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the lessons for the topic.
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    /**
     * Calculate progress statistics for a user.
     */
    public function getProgressStats($userProgress = []): array
    {
        $lessons = $this->lessons;
        $totalItems = $lessons->count();

        $completedItems = $lessons->filter(function ($lesson) use ($userProgress) {
            return isset($userProgress[$lesson->id]) && $userProgress[$lesson->id]->is_completed;
        })->count();

        return [
            'total_items' => $totalItems,
            'completed_items' => $completedItems,
        ];
    }
}
