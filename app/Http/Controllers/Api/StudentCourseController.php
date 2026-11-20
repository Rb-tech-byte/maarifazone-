<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseTopic;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * API Student Course Controller
 *
 * REST API endpoints for online lesson player and progress tracking in Maarifazone.
 *
 * @package App\Http\Controllers\Api
 * @author Renatus Bernardo
 */
class StudentCourseController extends Controller
{
    /**
     * Get course learning player state.
     */
    public function show(Request $request, $courseId)
    {
        $user = Auth::user();

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->whereIn('status', ['approved', 'completed'])
            ->first();

        if (! $enrollment) {
            return response()->json(['message' => 'You are not enrolled in this course.'], 403);
        }

        $course = Course::with([
            'topics.lessons.liveClass',
            'instructor',
        ])->findOrFail($courseId);

        $userProgress = $user->lessonProgress()
            ->where('enrollment_id', $enrollment->id)
            ->get()
            ->keyBy('lesson_id');

        $topicsData = $course->topics->map(function ($topic) use ($userProgress) {
            return [
                'id'       => $topic->id,
                'title'    => $topic->title,
                'order'    => $topic->order,
                'progress' => $topic->getProgressStats($userProgress),
                'lessons'  => $topic->lessons->map(function ($lesson) use ($userProgress) {
                    $progress = $userProgress[$lesson->id] ?? null;
                    return [
                        'id'                 => $lesson->id,
                        'title'              => $lesson->title,
                        'order'              => $lesson->order,
                        'duration_formatted' => $lesson->formatted_duration,
                        'is_completed'       => (bool) ($progress && $progress->is_completed),
                        'is_live'            => (bool) $lesson->is_live,
                    ];
                }),
            ];
        });

        $totalLessons = $course->topics->flatMap->lessons->count();
        $completedLessons = $userProgress->where('is_completed', true)->count();
        $percentage = $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0;

        return response()->json([
            'course'           => [
                'id'         => $course->id,
                'title'      => $course->title,
                'instructor' => $course->instructor?->name,
            ],
            'enrollment'       => [
                'status'      => $enrollment->status,
                'enrolled_at' => $enrollment->enrolled_at,
            ],
            'progress'         => [
                'total_lessons'     => $totalLessons,
                'completed_lessons' => $completedLessons,
                'percentage'        => $percentage,
            ],
            'topics'           => $topicsData,
        ]);
    }

    /**
     * Load specific lesson.
     */
    public function loadLesson(Request $request, $courseId, $lessonId)
    {
        $user = Auth::user();

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->whereIn('status', ['approved', 'completed'])
            ->first();

        if (! $enrollment) {
            return response()->json(['message' => 'You are not enrolled in this course.'], 403);
        }

        $lesson = Lesson::with(['topic.course', 'liveClass'])->findOrFail($lessonId);

        if ($lesson->topic->course_id != $courseId) {
            return response()->json(['message' => 'Lesson does not belong to this course.'], 403);
        }

        $progress = LessonProgress::firstOrCreate(
            [
                'user_id'       => $user->id,
                'lesson_id'     => $lessonId,
                'enrollment_id' => $enrollment->id,
            ],
            [
                'is_completed'        => false,
                'progress_percentage' => 0,
                'last_accessed_at'    => now(),
            ]
        );

        $progress->update(['last_accessed_at' => now()]);

        return response()->json([
            'lesson'   => [
                'id'           => $lesson->id,
                'title'        => $lesson->title,
                'description'  => $lesson->description,
                'content_text' => $lesson->content_text,
                'video_type'   => $lesson->video_type,
                'video_url'    => $lesson->video_url,
                'duration'     => $lesson->duration,
                'is_live'      => (bool) $lesson->is_live,
            ],
            'progress' => [
                'is_completed'        => (bool) $progress->is_completed,
                'progress_percentage' => $progress->progress_percentage,
            ],
        ]);
    }

    /**
     * Mark lesson as complete.
     */
    public function markLessonComplete(Request $request, $courseId, $lessonId)
    {
        $user = Auth::user();

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->whereIn('status', ['approved', 'completed'])
            ->first();

        if (! $enrollment) {
            return response()->json(['message' => 'You are not enrolled in this course.'], 403);
        }

        $progress = LessonProgress::firstOrCreate(
            [
                'user_id'       => $user->id,
                'lesson_id'     => $lessonId,
                'enrollment_id' => $enrollment->id,
            ],
            [
                'is_completed'        => false,
                'progress_percentage' => 0,
            ]
        );

        $progress->markComplete();

        // Check if all lessons are completed
        $course = Course::with('topics.lessons')->findOrFail($courseId);
        $totalLessons = $course->topics->flatMap->lessons->count();
        $completedLessons = $user->lessonProgress()
            ->where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->count();

        $courseCompleted = false;
        if ($totalLessons > 0 && $completedLessons >= $totalLessons) {
            $enrollment->update(['status' => Enrollment::STATUS_COMPLETED]);
            $courseCompleted = true;
        }

        return response()->json([
            'message'          => 'Lesson marked as complete.',
            'is_completed'     => true,
            'course_completed' => $courseCompleted,
            'completed_count'  => $completedLessons,
            'total_count'      => $totalLessons,
        ]);
    }
}
