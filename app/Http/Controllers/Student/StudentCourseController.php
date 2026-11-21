<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseTopic;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use App\Services\FileUploadService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Scholar Course Learning Controller
 *
 * Delivers online lessons, video playback, progress tracking,
 * and completion recognition in Maarifazone.
 *
 * @package App\Http\Controllers\Student
 * @author Renatus Bernardo
 */
class StudentCourseController extends Controller
{
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display course learning player.
     */
    public function show(Request $request, $courseId)
    {
        $user = $this->currentUser();

        $course = Course::with([
            'topics.lessons.liveClass',
            'instructor',
        ])->findOrFail($courseId);

        $userEnrollments = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->whereIn('status', ['approved', 'completed'])
            ->get();

        if ($userEnrollments->isEmpty()) {
            $hasFreeTopic = $course->topics->contains(fn($t) => $t->is_free || (float)$t->price <= 0);
            if ($course->pricing_model === 'free' || $hasFreeTopic) {
                $enrollment = Enrollment::create([
                    'user_id' => $user->id,
                    'course_id' => $courseId,
                    'status' => Enrollment::STATUS_APPROVED,
                    'enrolled_at' => now(),
                ]);
                $userEnrollments = collect([$enrollment]);
            } else {
                ToastMagic::warning('Please enroll in this course or a topic module to access course learning.');
                return redirect()->route('courses.show', $courseId);
            }
        } else {
            $enrollment = $userEnrollments->first();
        }

        $userProgress = $user->lessonProgress()
            ->whereIn('enrollment_id', $userEnrollments->pluck('id'))
            ->get()
            ->keyBy('lesson_id');

        $allItems = $this->getAllCourseItems($course);
        $accessibleItems = [];
        foreach ($allItems as $item) {
            $accessibleItems['lesson_' . $item['id']] = $this->isItemAccessible($user, $userEnrollments, $courseId, 'lesson', $item['id'], $course);
        }

        // Resolve requested item (?item_type=lesson&item_id=...)
        $currentItem = null;
        $isCurrentItemLocked = false;
        $requestedId = $request->integer('item_id');

        if ($requestedId) {
            $requestedLesson = Lesson::with(['topic.course', 'liveClass'])->find($requestedId);
            if ($requestedLesson && $requestedLesson->topic && (int)$requestedLesson->topic->course_id === (int)$courseId) {
                $currentItem = $requestedLesson;
                if (!($accessibleItems['lesson_' . $requestedId] ?? false)) {
                    $isCurrentItemLocked = true;
                }
            }
        }

        // Resume at first incomplete accessible lesson
        if (! $currentItem) {
            foreach ($allItems as $item) {
                $isItemAccessible = $accessibleItems['lesson_' . $item['id']] ?? false;
                $isDone = isset($userProgress[$item['id']]) && $userProgress[$item['id']]->is_completed;
                if ($isItemAccessible && ! $isDone) {
                    $currentItem = Lesson::with(['topic.course', 'liveClass'])->find($item['id']);
                    if ($currentItem) {
                        break;
                    }
                }
            }
        }

        if (! $currentItem && $allItems->isNotEmpty()) {
            $fallbackItem = $allItems->first();
            $currentItem = Lesson::with(['topic.course', 'liveClass'])->find($fallbackItem['id']);
            if ($currentItem && !($accessibleItems['lesson_' . $currentItem->id] ?? false)) {
                $isCurrentItemLocked = true;
            }
        }

        // Ensure initial progress record exists for accessible lessons
        if ($currentItem instanceof Lesson && !$isCurrentItemLocked) {
            $initialProgress = LessonProgress::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'lesson_id' => $currentItem->id,
                    'enrollment_id' => $enrollment->id,
                ],
                [
                    'is_completed' => false,
                    'progress_percentage' => 0,
                    'last_accessed_at' => now(),
                ]
            );
            $userProgress[$currentItem->id] = $initialProgress;
        }

        // Check if all lessons are completed
        $progressSummary = $this->buildProgressSummary($allItems, $userProgress);
        $isCompleted = $enrollment->status === Enrollment::STATUS_COMPLETED || ($progressSummary['total_items'] > 0 && $progressSummary['percentage'] >= 100);
        if ($isCompleted && $enrollment->status !== Enrollment::STATUS_COMPLETED) {
            $enrollment->update(['status' => Enrollment::STATUS_COMPLETED]);
            $enrollment->refresh();
        }

        // Assemble topics data for sidebar
        $topicsData = [];
        foreach ($course->topics as $index => $topic) {
            $progressStats = $topic->getProgressStats($userProgress);
            $itemsData = [];

            $isTopicUnlocked = $topic->is_free
                || (float)$topic->price <= 0
                || $userEnrollments->contains(fn($e) => empty($e->course_topic_id) || (int)$e->course_topic_id === (int)$topic->id);

            foreach ($topic->lessons as $lesson) {
                $progress = $userProgress[$lesson->id] ?? null;
                $isLessonAccessible = $accessibleItems['lesson_' . $lesson->id] ?? false;

                $itemsData[] = [
                    'id'                 => $lesson->id,
                    'type'               => 'lesson',
                    'order'              => $lesson->order ?? 0,
                    'title'              => $lesson->title,
                    'duration_formatted' => $lesson->formatted_duration,
                    'is_completed'       => (bool) ($progress && $progress->is_completed),
                    'is_active'          => $currentItem && $currentItem->id == $lesson->id,
                    'is_accessible'      => $isLessonAccessible,
                    'is_free'            => (bool) ($lesson->is_free || $lesson->is_preview || $topic->is_free || (float)$topic->price <= 0),
                    'price'              => (float) ($lesson->price ?? 0),
                    'is_live'            => (bool) $lesson->is_live,
                    'live_status'        => $lesson->liveClass?->status,
                    'lesson_type'        => $lesson->lesson_type ?: 'video',
                ];
            }

            usort($itemsData, fn ($a, $b) => ($a['order'] <=> $b['order']));

            $topicsData[] = [
                'topic'             => $topic,
                'index'             => $index,
                'progress'          => $progressStats,
                'items'             => $itemsData,
                'is_free'           => (bool) ($topic->is_free || (float)$topic->price <= 0),
                'price'             => (float) $topic->price,
                'is_topic_unlocked' => $isTopicUnlocked,
            ];
        }

        $currentItemType = 'lesson';
        $currentItemDurationFormatted = $currentItem?->formatted_duration ?? '';
        $initialLessonVideoData = ($currentItem && !$isCurrentItemLocked) ? $this->prepareLessonVideoData($currentItem) : null;
        $navigation = $currentItem
            ? $this->buildNavigationState($user, $enrollment, $courseId, $allItems, 'lesson', $currentItem->id)
            : ['has_previous' => false, 'has_next' => false, 'next_accessible' => false, 'previous_item' => null, 'next_item' => null];

        // Legacy compatibility variables passed as empty collections for template safety
        $userQuizAttempts = collect();
        $userAssignmentSubmissions = collect();
        $initialQuizPassed = false;
        $initialQuizResultsData = null;

        return view('student.course-access', compact(
            'course',
            'enrollment',
            'currentItem',
            'currentItemType',
            'currentItemDurationFormatted',
            'userProgress',
            'userQuizAttempts',
            'userAssignmentSubmissions',
            'accessibleItems',
            'topicsData',
            'initialLessonVideoData',
            'initialQuizPassed',
            'initialQuizResultsData',
            'isCompleted',
            'isCurrentItemLocked',
            'progressSummary',
            'navigation'
        ));
    }

    /**
     * Load specific lesson via AJAX.
     */
    public function loadLesson(Request $request)
    {
        $user = $this->currentUser();
        $lessonId = $request->input('lesson_id');
        $courseId = $request->input('course_id');

        $userEnrollments = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->whereIn('status', ['approved', 'completed'])
            ->get();

        if ($userEnrollments->isEmpty()) {
            return response()->json(['error' => 'You are not enrolled in this course.'], 403);
        }

        $lesson = Lesson::with(['topic.course', 'liveClass'])->findOrFail($lessonId);

        if ($lesson->topic->course_id != $courseId) {
            return response()->json(['error' => 'Lesson does not belong to this course'], 403);
        }

        $isAccessible = $this->isItemAccessible($user, $userEnrollments, $courseId, 'lesson', $lessonId, $lesson->topic->course);

        if (! $isAccessible) {
            $lockedHtml = view('student.course-access.partials.locked-content', [
                'course' => $lesson->topic->course,
                'topic'  => $lesson->topic,
                'lesson' => $lesson,
            ])->render();

            return response()->json([
                'success'            => true,
                'locked'             => true,
                'html'               => $lockedHtml,
                'lesson_id'          => $lesson->id,
                'type'               => 'lesson',
                'title'              => $lesson->title,
                'duration'           => $lesson->duration,
                'navigation'         => ['has_previous' => false, 'has_next' => false, 'next_accessible' => false, 'previous_item' => null, 'next_item' => null],
                'topic_title'        => $lesson->topic->title,
                'topic_price'        => $lesson->topic->price,
                'checkout_topic_url' => route('courses.checkout', ['id' => $courseId, 'topic_id' => $lesson->course_topic_id]),
                'checkout_course_url'=> route('courses.checkout', $courseId),
            ]);
        }

        $enrollment = $userEnrollments->first();

        $progress = LessonProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lessonId,
                'enrollment_id' => $enrollment->id,
            ],
            [
                'is_completed' => false,
                'progress_percentage' => 0,
                'last_accessed_at' => now(),
            ]
        );

        $progress->update(['last_accessed_at' => now()]);

        $videoData = $this->prepareLessonVideoData($lesson);
        $navigation = $this->buildNavigationState($user, $enrollment, $courseId, null, 'lesson', $lesson->id);

        $html = view('student.course-access.partials.lesson-content', [
            'lesson' => $lesson,
            'progress' => $progress,
            'videoData' => $videoData,
        ])->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'lesson_id' => $lesson->id,
            'type' => 'lesson',
            'title' => $lesson->title,
            'duration' => $lesson->duration,
            'navigation' => $navigation,
            'videoData' => $videoData,
        ]);
    }

    /**
     * Mark lesson as complete.
     */
    public function markComplete(Request $request)
    {
        $user = $this->currentUser();
        $lessonId = $request->input('lesson_id');
        $courseId = $request->input('course_id');

        $userEnrollments = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->whereIn('status', ['approved', 'completed'])
            ->get();

        if ($userEnrollments->isEmpty()) {
            abort(404);
        }

        $enrollment = $userEnrollments->first();

        $lesson = Lesson::whereHas('topic', function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })->findOrFail($lessonId);

        $progress = LessonProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lessonId,
                'enrollment_id' => $enrollment->id,
            ],
            [
                'is_completed' => false,
                'progress_percentage' => 0,
            ]
        );

        $progress->markComplete();

        // Check if full course is complete
        $course = Course::with('topics.lessons')->findOrFail($courseId);
        $allItems = $this->getAllCourseItems($course);
        $userProgress = $user->lessonProgress()
            ->whereIn('enrollment_id', $userEnrollments->pluck('id'))
            ->get()
            ->keyBy('lesson_id');
        $progressSummary = $this->buildProgressSummary($allItems, $userProgress);

        $courseCompleted = false;
        if ($progressSummary['total_items'] > 0 && $progressSummary['percentage'] >= 100) {
            $enrollment->update(['status' => Enrollment::STATUS_COMPLETED]);
            $courseCompleted = true;
        }

        $currentIndex = $allItems->search(fn ($item) => (int) $item['id'] === (int) $lessonId);
        $nextItem = null;
        if (! $courseCompleted && $currentIndex !== false && $currentIndex < $allItems->count() - 1) {
            $potentialNext = $allItems[$currentIndex + 1];
            $nextItem = [
                'item_id'       => $potentialNext['id'],
                'id'            => $potentialNext['id'],
                'type'          => 'lesson',
                'is_accessible' => $this->isItemAccessible($user, $userEnrollments, $courseId, 'lesson', $potentialNext['id'], $course),
            ];
        }

        return response()->json([
            'success'          => true,
            'message'          => 'Lesson marked as complete',
            'next_item'        => $nextItem,
            'course_completed' => $courseCompleted,
            'progress_summary' => $progressSummary,
            'navigation'       => $this->buildNavigationState($user, $enrollment, $courseId, $allItems, 'lesson', (int) $lessonId),
        ]);
    }

    /**
     * Update video playback progress.
     */
    public function updateProgress(Request $request)
    {
        $user = $this->currentUser();
        $lessonId = $request->input('lesson_id');
        $percentage = (int) $request->input('percentage', 0);
        $courseId = $request->input('course_id');

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->whereIn('status', ['approved', 'completed'])
            ->firstOrFail();

        $progress = LessonProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lessonId,
                'enrollment_id' => $enrollment->id,
            ],
            [
                'is_completed' => false,
                'progress_percentage' => 0,
            ]
        );

        $progress->updateProgress($percentage);

        if ($percentage >= 95) {
            $progress->markComplete();
        }

        return response()->json([
            'success' => true,
            'progress_percentage' => $progress->progress_percentage,
            'is_completed' => $progress->is_completed,
        ]);
    }

    /**
     * Get next lesson (including lessons in paid modules so the unlock screen can be displayed).
     */
    public function getNextItem(Request $request, $courseId, $itemId)
    {
        $user = $this->currentUser();
        $userEnrollments = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->whereIn('status', ['approved', 'completed'])
            ->get();

        if ($userEnrollments->isEmpty()) {
            return response()->json(['error' => 'Not enrolled in this course'], 403);
        }

        $course = Course::with('topics.lessons')->findOrFail($courseId);
        $allItems = $this->getAllCourseItems($course);

        $currentIndex = $allItems->search(fn ($item) => (int) $item['id'] === (int) $itemId);
        if ($currentIndex === false || $currentIndex >= $allItems->count() - 1) {
            return response()->json([
                'success' => false,
                'message' => 'You have reached the last lesson in this course.',
            ]);
        }

        $nextItem = $allItems[$currentIndex + 1];
        $isAccessible = $this->isItemAccessible($user, $userEnrollments, $courseId, 'lesson', $nextItem['id'], $course);

        return response()->json([
            'success'       => true,
            'item_id'       => $nextItem['id'],
            'type'          => 'lesson',
            'is_accessible' => $isAccessible,
            'next_item'     => [
                'type'          => 'lesson',
                'id'            => $nextItem['id'],
                'item_id'       => $nextItem['id'],
                'is_accessible' => $isAccessible,
            ],
        ]);
    }

    /**
     * Get previous lesson.
     */
    public function getPreviousItem(Request $request, $courseId, $itemId)
    {
        $user = $this->currentUser();
        $userEnrollments = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->whereIn('status', ['approved', 'completed'])
            ->get();

        if ($userEnrollments->isEmpty()) {
            return response()->json(['error' => 'Not enrolled in this course'], 403);
        }

        $course = Course::with('topics.lessons')->findOrFail($courseId);
        $allItems = $this->getAllCourseItems($course);

        $currentIndex = $allItems->search(fn ($item) => (int) $item['id'] === (int) $itemId);
        if ($currentIndex === false || $currentIndex <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'You are on the first lesson of this course.',
            ]);
        }

        $prevItem = $allItems[$currentIndex - 1];
        $isAccessible = $this->isItemAccessible($user, $userEnrollments, $courseId, 'lesson', $prevItem['id'], $course);

        return response()->json([
            'success'       => true,
            'item_id'       => $prevItem['id'],
            'type'          => 'lesson',
            'is_accessible' => $isAccessible,
            'previous_item' => [
                'type'          => 'lesson',
                'id'            => $prevItem['id'],
                'item_id'       => $prevItem['id'],
                'is_accessible' => $isAccessible,
            ],
        ]);
    }

    /**
     * Search course lessons.
     */
    public function searchContent(Request $request, $courseId)
    {
        $query = $request->string('q')->trim()->value();
        if (strlen($query) < 2) {
            return response()->json(['success' => true, 'results' => []]);
        }

        $lessons = Lesson::with('topic:id,title')
            ->whereHas('topic', fn ($t) => $t->where('course_id', $courseId))
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->get(['id', 'title', 'course_topic_id']);

        return response()->json([
            'success' => true,
            'results' => $lessons->map(fn ($l) => [
                'id'    => $l->id,
                'type'  => 'lesson',
                'title' => $l->title,
                'topic' => $l->topic?->title ?? 'Course Module',
            ]),
        ]);
    }

    /**
     * Refresh sidebar HTML.
     */
    public function refreshSidebar(Request $request, $courseId)
    {
        $user = $this->currentUser();
        $userEnrollments = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->whereIn('status', ['approved', 'completed'])
            ->get();

        if ($userEnrollments->isEmpty()) {
            abort(404);
        }

        $activeId = (int) $request->input('active_id', 0);
        $course = Course::with(['topics.lessons.liveClass'])->findOrFail($courseId);

        $userProgress = $user->lessonProgress()
            ->whereIn('enrollment_id', $userEnrollments->pluck('id'))
            ->get()
            ->keyBy('lesson_id');
        $allItems = $this->getAllCourseItems($course);
        $accessibleItems = [];
        foreach ($allItems as $item) {
            $accessibleItems['lesson_' . $item['id']] = $this->isItemAccessible($user, $userEnrollments, $courseId, 'lesson', $item['id'], $course);
        }

        $topicsData = [];
        foreach ($course->topics as $index => $topic) {
            $itemsData = [];
            $isTopicUnlocked = $topic->is_free
                || (float) $topic->price <= 0
                || $userEnrollments->contains(fn ($e) => empty($e->course_topic_id) || (int) $e->course_topic_id === (int) $topic->id);

            foreach ($topic->lessons as $lesson) {
                $progress = $userProgress[$lesson->id] ?? null;
                $itemsData[] = [
                    'id'                 => $lesson->id,
                    'type'               => 'lesson',
                    'order'              => $lesson->order ?? 0,
                    'title'              => $lesson->title,
                    'duration_formatted' => $lesson->formatted_duration,
                    'is_completed'       => (bool) ($progress && $progress->is_completed),
                    'is_active'          => $activeId === (int) $lesson->id,
                    'is_accessible'      => $accessibleItems['lesson_' . $lesson->id] ?? false,
                    'is_free'            => (bool) ($lesson->is_free || $lesson->is_preview || $topic->is_free || (float) $topic->price <= 0),
                    'price'              => (float) ($lesson->price ?? 0),
                    'is_live'            => (bool) $lesson->is_live,
                    'live_status'        => $lesson->liveClass?->status,
                    'lesson_type'        => $lesson->lesson_type ?: 'video',
                ];
            }
            usort($itemsData, fn ($a, $b) => ($a['order'] <=> $b['order']));

            $topicsData[] = [
                'topic'             => $topic,
                'index'             => $index,
                'progress'          => $topic->getProgressStats($userProgress),
                'items'             => $itemsData,
                'is_free'           => (bool) ($topic->is_free || (float) $topic->price <= 0),
                'price'             => (float) $topic->price,
                'is_topic_unlocked' => $isTopicUnlocked,
            ];
        }

        $progressSummary = $this->buildProgressSummary($allItems, $userProgress);
        $html = view('student.course-access.partials.sidebar', compact('course', 'topicsData'))->render();

        return response()->json([
            'success'          => true,
            'html'             => $html,
            'topics_data'      => $topicsData,
            'accessibleItems'  => $accessibleItems,
            'progress_summary' => $progressSummary,
        ]);
    }

    /**
     * Show course completion page.
     */
    public function showCompletionPage($courseId)
    {
        $user = $this->currentUser();
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->where('status', Enrollment::STATUS_COMPLETED)
            ->firstOrFail();

        $course = Course::with(['topics.lessons', 'instructor'])->findOrFail($courseId);
        $totalLessons = $course->topics->flatMap->lessons->count();
        $completedLessons = $user->lessonProgress()
            ->where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->count();

        return view('student.course-completion', compact(
            'course',
            'enrollment',
            'user',
            'totalLessons',
            'completedLessons'
        ));
    }

    /**
     * Flatten course lessons.
     */
    private function getAllCourseItems(Course $course): Collection
    {
        $items = collect();
        foreach ($course->topics as $topic) {
            foreach ($topic->lessons as $lesson) {
                $items->push([
                    'id'    => $lesson->id,
                    'type'  => 'lesson',
                    'order' => ($topic->order * 1000) + ($lesson->order ?? 0),
                ]);
            }
        }

        return $items->sortBy('order')->values();
    }

    /**
     * Determine accessibility for a lesson.
     */
    private function isItemAccessible($user, $userEnrollments, $courseId, string $type, int $itemId, ?Course $course = null): bool
    {
        if (! $course) {
            $course = Course::with('topics.lessons')->find($courseId);
        }
        if (! $course) {
            return false;
        }

        // Find the lesson and its topic
        $lesson = Lesson::with('topic')->find($itemId);
        if (!$lesson) {
            return true;
        }

        // If lesson is marked preview or free, open to all
        if ($lesson->is_preview || $lesson->is_free) {
            return true;
        }

        // If the topic itself is free, all lessons in this topic are open
        if ($lesson->topic && ($lesson->topic->is_free || (float)$lesson->topic->price <= 0)) {
            return true;
        }

        // Resolve user enrollments as a collection
        if (! $userEnrollments instanceof Collection) {
            $userId = is_object($user) ? $user->id : (int) $user;
            $userEnrollments = Enrollment::where('user_id', $userId)
                ->where('course_id', $courseId)
                ->whereIn('status', ['approved', 'completed'])
                ->get();
        }

        // 1. Full course enrollment (course_topic_id is null) unlocks all topics and lessons
        if ($userEnrollments->contains(fn($e) => empty($e->course_topic_id))) {
            return true;
        }

        // 2. Topic-specific enrollment unlocks all lessons in that topic
        if ($lesson->course_topic_id && $userEnrollments->contains(fn($e) => (int)$e->course_topic_id === (int)$lesson->course_topic_id)) {
            return true;
        }

        return false;
    }

    /**
     * Build completion summary.
     */
    private function buildProgressSummary(Collection $allItems, $userProgress): array
    {
        $total = $allItems->count();
        $completed = 0;

        foreach ($allItems as $item) {
            if (isset($userProgress[$item['id']]) && $userProgress[$item['id']]->is_completed) {
                $completed++;
            }
        }

        return [
            'total_items'     => $total,
            'completed_items' => $completed,
            'percentage'      => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
        ];
    }

    /**
     * Build previous/next navigation state.
     */
    private function buildNavigationState($user, $enrollment, $courseId, ?Collection $allItems, string $currentType, int $currentId): array
    {
        if (! $allItems) {
            $course = Course::with('topics.lessons')->findOrFail($courseId);
            $allItems = $this->getAllCourseItems($course);
        }

        $currentIndex = $allItems->search(fn ($item) => (int) $item['id'] === $currentId);
        $previousItem = ($currentIndex !== false && $currentIndex > 0) ? $allItems[$currentIndex - 1] : null;
        $nextItem = ($currentIndex !== false && $currentIndex < $allItems->count() - 1) ? $allItems[$currentIndex + 1] : null;

        return [
            'has_previous'    => $previousItem !== null,
            'has_next'        => $nextItem !== null,
            'next_accessible' => $nextItem !== null,
            'previous_item'   => $previousItem ? ['type' => 'lesson', 'id' => $previousItem['id']] : null,
            'next_item'       => $nextItem ? ['type' => 'lesson', 'id' => $nextItem['id']] : null,
        ];
    }

    /**
     * Prepare video streaming data.
     */
    private function prepareLessonVideoData(Lesson $lesson): ?array
    {
        if (! $lesson->video_url && ! $lesson->video_file) {
            return null;
        }

        $url = $lesson->video_url ?? '';
        $isYoutube = (bool) preg_match('/(?:youtube\.com|youtu\.be)/i', $url);
        $isVimeo = (bool) preg_match('/vimeo\.com/i', $url);
        $embedUrl = $url;

        if ($isYoutube) {
            if (preg_match('/(?:v=|youtu\.be\/|embed\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
                $embedUrl = 'https://www.youtube.com/embed/' . $matches[1];
            }
        } elseif ($isVimeo) {
            if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
                $embedUrl = 'https://player.vimeo.com/video/' . $matches[1];
            }
        }

        return [
            'type'       => $lesson->video_type,
            'url'        => $url,
            'file'       => $lesson->video_file ? asset('storage/' . $lesson->video_file) : null,
            'is_youtube' => $isYoutube,
            'is_vimeo'   => $isVimeo,
            'embed_url'  => $embedUrl,
        ];
    }

    private function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
