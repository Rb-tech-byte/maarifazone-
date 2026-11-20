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
        'order',
        'drip_days',
        'drip_date',
    ];

    protected $casts = [
        'order' => 'integer',
        'drip_days' => 'integer',
        'drip_date' => 'datetime',
    ];

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
