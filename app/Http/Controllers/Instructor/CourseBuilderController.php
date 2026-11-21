<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseCoInstructor;
use App\Models\CourseFaq;
use App\Models\CourseLanguage;
use App\Models\CourseTopic;
use App\Models\Lesson;
use App\Services\FileUploadService;
use App\Services\GeminiService;
use App\Services\NotificationPreferenceService;
use App\Services\YouTubePlaylistService;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\CourseUpdatedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

/**
 * Course Builder Controller
 *
 * This controller handles the course builder functionality.
 *
 * @package App\Http\Controllers\Instructor
 * @author Renatus Bernardo
 */
class CourseBuilderController extends Controller
{
    /**
     * The file upload service
     *
     * @var \App\Services\FileUploadService
     */
    protected $fileUploadService;

    /**
     * The constructor
     *
     * @param \App\Services\FileUploadService $fileUploadService
     * @param \App\Services\NotificationPreferenceService $notificationPreferenceService
     */

    public function __construct(FileUploadService $fileUploadService, private NotificationPreferenceService $notificationPreferenceService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display course builder page
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        $user = Auth::user();

        if ($user->role !== 'admin' && !$user->is_approved) {
            return redirect()->route('instructor.pending');
        }

        return view('instructor.course-builder', array_merge([
            'course' => null,
            'courseDataArray' => null,
        ], $this->builderFormData()));
    }

    /**
     * Store course
     *
     * @param \App\Http\Requests\StoreCourseRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreCourseRequest $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        if ($user->role !== 'admin' && !$user->is_approved) {
            return redirect()->route('instructor.pending')->with('error', 'Your instructor account is not approved yet.');
        }

        $taxonomy = $this->resolveTaxonomySelections($request);
        $categoryModel = $taxonomy['category'];
        $categoryLabel = $taxonomy['categoryLabel'];
        $languageModel = $taxonomy['language'];
        $languageLabel = $taxonomy['languageLabel'];

        DB::beginTransaction();

        try {

            $featuredImagePath = null;
            if ($request->hasFile('featured_image')) {
                $featuredImagePath = $this->fileUploadService->uploadCourseImage($request->file('featured_image'));
            }

            $introVideoPath = null;
            if ($request->hasFile('intro_video')) {
                $introVideoPath = $this->fileUploadService->uploadIntroVideo($request->file('intro_video'));
            }

            $isAdmin = $user->role === 'admin';
            $assignedInstructorId = $isAdmin && $request->filled('instructor_id')
                ? (int) $request->input('instructor_id')
                : $user->id;
            $courseStatus = $isAdmin && in_array($request->input('status'), ['draft', 'pending', 'published', 'archived'], true)
                ? $request->input('status')
                : ($request->input('status') === 'published' ? 'pending' : 'draft');

            $course = Course::create([
                'instructor_id' => $assignedInstructorId,
                'assistant_instructor_id' => $request->filled('assistant_instructor_id') ? (int) $request->input('assistant_instructor_id') : null,
                'course_type' => 'course',
                'delivery_mode' => 'online',
                'drip_enabled' => filter_var($request->input('drip_enabled', false), FILTER_VALIDATE_BOOLEAN),
                'title' => $request->title,
                'description' => $request->description,
                'featured_image' => $featuredImagePath,
                'intro_video' => $introVideoPath,
                'visibility' => $request->visibility,
                'public_course' => filter_var($request->input('public_course', false), FILTER_VALIDATE_BOOLEAN),
                'max_students' => filter_var($request->input('public_course', false), FILTER_VALIDATE_BOOLEAN) ? null : $request->max_students,
                'difficulty' => $request->difficulty,
                'pricing_model' => $request->pricing_model,
                'price_currency' => normalize_currency_code($request->input('price_currency', base_currency_code()), base_currency_code()),
                'regular_price' => $request->pricing_model === 'paid' ? $request->regular_price : null,
                'sale_price' => $request->pricing_model === 'paid' ? $request->sale_price : null,
                'schedule_enabled' => filter_var($request->input('schedule_enabled', false), FILTER_VALIDATE_BOOLEAN),
                'schedule_date' => filter_var($request->input('schedule_enabled', false), FILTER_VALIDATE_BOOLEAN) ? $request->schedule_date : null,
                'course_category_id' => $categoryModel?->id,
                'course_language_id' => $languageModel?->id,
                'category' => $categoryLabel,
                'language' => $languageLabel,
                'tags' => $request->tags,
                'requirements' => $request->requirements,
                'objectives'   => $request->objectives,
                'seo_title' => $request->input('seo_title'),
                'seo_description' => $request->input('seo_description'),
                'seo_tags' => $request->input('seo_tags'),
                'marketing_copy' => is_array($request->input('marketing_copy')) ? $request->input('marketing_copy') : null,
                'status'       => $courseStatus,
                'is_live_course' => filter_var($request->input('is_live_course', false), FILTER_VALIDATE_BOOLEAN),
                'has_discount' => filter_var($request->input('has_discount', false), FILTER_VALIDATE_BOOLEAN),
                'discount_amount' => (float) ($request->input('discount_amount', 0) ?: 0),
                'attendance_threshold' => max(0, min(100, (int) ($request->input('attendance_threshold', 75) ?? 75))),
                'allow_online_f2f_payment' => filter_var($request->input('allow_online_f2f_payment', true), FILTER_VALIDATE_BOOLEAN),
                'f2f_commission_enabled' => $isAdmin ? filter_var($request->input('f2f_commission_enabled', false), FILTER_VALIDATE_BOOLEAN) : false,
            ]);

            $this->syncCoInstructors($course, $request->input('co_instructors', []));
            $this->syncFaqs($course, $request->input('faqs', []));

            if ($request->has('topics') && is_array($request->topics)) {
                foreach ($request->topics as $topicIndex => $topicData) {
                    $topic = CourseTopic::create([
                        'course_id'   => $course->id,
                        'title'       => $topicData['title'] ?? '',
                        'description' => $topicData['description'] ?? null,
                        'is_free'     => filter_var($topicData['is_free'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'price'       => isset($topicData['price']) && is_numeric($topicData['price']) ? (float) $topicData['price'] : 0.00,
                        'order'       => $topicIndex,
                    ]);

                    if (isset($topicData['lessons']) && is_array($topicData['lessons'])) {
                        foreach ($topicData['lessons'] as $lessonIndex => $lessonData) {
                            $lessonVideoPath = null;
                            $lessonVideoUrl = null;
                            $contentType = $lessonData['lesson_type'] ?? ($lessonData['contentType'] ?? 'video');
                            $isLiveLesson = ($lessonData['video_type'] ?? 'url') === 'live' || $contentType === 'live';

                            if (!$isLiveLesson && ($lessonData['video_type'] ?? 'url') === 'upload') {
                                $videoFile = null;
                                $simpleKey = "lesson_video_{$topicIndex}_{$lessonIndex}";

                                if ($request->hasFile($simpleKey)) {
                                    $videoFile = $request->file($simpleKey);
                                } elseif ($request->hasFile("topics.{$topicIndex}.lessons.{$lessonIndex}.video_file")) {
                                    $videoFile = $request->file("topics.{$topicIndex}.lessons.{$lessonIndex}.video_file");
                                } else {
                                    $allFiles = $request->allFiles();
                                    if (isset($allFiles[$simpleKey])) {
                                        $videoFile = $allFiles[$simpleKey];
                                    } elseif (isset($allFiles['topics'][$topicIndex]['lessons'][$lessonIndex]['video_file'])) {
                                        $videoFile = $allFiles['topics'][$topicIndex]['lessons'][$lessonIndex]['video_file'];
                                    }
                                }

                                if ($videoFile && $videoFile instanceof \Illuminate\Http\UploadedFile) {
                                    $lessonVideoPath = $this->fileUploadService->uploadLessonVideo($videoFile);
                                } elseif (!empty($lessonData['video_path'])) {
                                    $lessonVideoPath = str_replace(asset('storage/'), '', $lessonData['video_path']);
                                }
                            } else {
                                $lessonVideoUrl = $lessonData['video_url'] ?? null;
                            }

                            // Handle downloadable material file (PDF, PPT, DOC, ZIP)
                            $materialPath = null;
                            $materialName = null;
                            $materialType = null;
                            $simpleMatKey = "lesson_material_{$topicIndex}_{$lessonIndex}";
                            $matFile = $request->file($simpleMatKey) ?? $request->file("topics.{$topicIndex}.lessons.{$lessonIndex}.material_file");
                            if ($matFile && $matFile instanceof \Illuminate\Http\UploadedFile) {
                                $matInfo = $this->fileUploadService->uploadLessonMaterial($matFile);
                                $materialPath = $matInfo['file_path'];
                                $materialName = $matInfo['original_filename'];
                                $materialType = $matInfo['extension'];
                            } elseif (!empty($lessonData['attachment_path'])) {
                                $materialPath = str_replace(asset('storage/'), '', $lessonData['attachment_path']);
                                $materialName = $lessonData['attachment_name'] ?? null;
                                $materialType = $lessonData['attachment_type'] ?? null;
                            }

                            $isPreview = filter_var($lessonData['is_preview'] ?? false, FILTER_VALIDATE_BOOLEAN);
                            $isFree = filter_var($lessonData['is_free'] ?? $isPreview, FILTER_VALIDATE_BOOLEAN) || $isPreview;

                            Lesson::create([
                                'course_topic_id' => $topic->id,
                                'title'           => $lessonData['title'] ?? '',
                                'lesson_type'     => $contentType,
                                'description'     => $lessonData['description'] ?? null,
                                'content_text'    => $lessonData['content_text'] ?? null,
                                'video_type'      => $lessonData['video_type'] ?? 'url',
                                'video_url'       => $isLiveLesson ? null : $lessonVideoUrl,
                                'video_path'      => $isLiveLesson ? null : $lessonVideoPath,
                                'attachment_path' => $materialPath,
                                'attachment_name' => $materialName,
                                'attachment_type' => $materialType,
                                'duration'        => $lessonData['duration'] ?? null,
                                'is_preview'      => $isPreview,
                                'is_free'         => $isFree,
                                'price'           => isset($lessonData['price']) && is_numeric($lessonData['price']) ? (float) $lessonData['price'] : 0.00,
                                'is_live'         => $isLiveLesson,
                                'order'           => $lessonIndex,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            $publish = filter_var($request->input('status', 'draft'), FILTER_VALIDATE_BOOLEAN) || $request->input('status') === 'published';
            $successMessage = $publish
                ? 'Course submitted successfully! It is pending admin approval.'
                : 'Course saved as draft successfully!';

            $redirectUrl = $isAdmin ? route('admin.courses.index') : route('instructor.courses');

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'course' => [
                        'id' => $course->id,
                        'title' => $course->title,
                        'status' => $course->status,
                        'url' => $redirectUrl,
                    ],
                ], 200);
            }

            return redirect()->to($redirectUrl)
                ->with('success', $successMessage);

        } catch (ValidationException $e) {
            DB::rollBack();

            if (isset($featuredImagePath)) {
                $this->fileUploadService->deleteFile($featuredImagePath);
            }
            if (isset($introVideoPath)) {
                $this->fileUploadService->deleteFile($introVideoPath);
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($featuredImagePath)) {
                $this->fileUploadService->deleteFile($featuredImagePath);
            }
            if (isset($introVideoPath)) {
                $this->fileUploadService->deleteFile($introVideoPath);
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create course: ' . $e->getMessage(),
                    'error' => config('app.debug') ? $e->getMessage() : 'An error occurred. Please try again.',
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', 'Failed to create course. Please try again.');
        }
    }

    /**
     * Display course edit page
     */
    public function edit($id)
    {
        $user = Auth::user();

        if ($user->role !== 'admin' && !$user->is_approved) {
            return redirect()->route('instructor.pending');
        }

        $course = Course::with([
            'topics.lessons',
            'coInstructors.user:id,name,email',
            'faqs',
        ])->findOrFail($id);

        if (!$course->canUserManageCurriculum($user)) {
            abort(403, 'Unauthorized action.');
        }

        $courseDataArray = [
            'id' => $course->id,
            'instructor_id' => $course->instructor_id,
            'assistant_instructor_id' => $course->assistant_instructor_id,
            'course_type' => 'course',
            'delivery_mode' => 'online',
            'drip_enabled' => (bool) $course->drip_enabled,
            'status' => $course->status,
            'title' => $course->title,
            'description' => $course->description,
            'visibility' => $course->visibility,
            'public_course' => $course->public_course,
            'max_students' => $course->max_students,
            'difficulty' => $course->difficulty,
            'pricing_model' => $course->pricing_model,
            'price_currency' => $course->getPriceCurrencyCode(),
            'regular_price' => $course->regular_price,
            'sale_price' => $course->sale_price,
            'schedule_enabled' => $course->schedule_enabled,
            'schedule_date' => $course->schedule_date ? $course->schedule_date->format('Y-m-d\TH:i') : null,
            'course_category_id' => $course->course_category_id,
            'course_language_id' => $course->course_language_id,
            'category' => $course->category,
            'language' => $course->language,
            'category_label' => $course->category,
            'language_label' => $course->language,
            'tags' => $course->tags,
            'requirements' => $course->requirements,
            'objectives' => $course->objectives,
            'seo_title' => $course->seo_title,
            'seo_description' => $course->seo_description,
            'seo_tags' => $course->seo_tags,
            'marketing_copy' => $course->marketing_copy,
            'co_instructors' => $course->coInstructors->map(fn ($co) => [
                'email' => $co->user?->email ?? '',
                'name' => $co->user?->name ?? '',
                'revenue_share' => (float) $co->revenue_share,
                'can_edit' => (bool) $co->can_edit,
            ])->values()->all(),
            'faqs' => $course->faqs->map(fn ($faq) => [
                'question' => $faq->question,
                'answer' => $faq->answer,
            ])->values()->all(),
            'featured_image' => $course->featured_image ? asset('storage/' . $course->featured_image) : null,
            'intro_video' => $course->intro_video ? asset('storage/' . $course->intro_video) : null,
            'topics' => $course->topics->map(function ($topic) {
                return [
                    'id'          => $topic->id,
                    'title'       => $topic->title,
                    'description' => $topic->description,
                    'is_free'     => (bool) $topic->is_free,
                    'price'       => (float) ($topic->price ?? 0),
                    'lessons'     => $topic->lessons->map(function ($lesson) {
                        return [
                            'id'             => $lesson->id,
                            'title'          => $lesson->title,
                            'description'    => $lesson->description,
                            'type'           => 'lesson',
                            'lessonType'     => $lesson->lesson_type ?: 'video',
                            'contentType'    => $lesson->lesson_type ?: 'video',
                            'contentText'    => $lesson->content_text,
                            'videoType'      => $lesson->video_type,
                            'videoUrl'       => $lesson->video_url,
                            'videoPath'      => $lesson->video_path ? asset('storage/' . $lesson->video_path) : null,
                            'attachmentPath' => $lesson->attachment_path ? asset('storage/' . $lesson->attachment_path) : null,
                            'attachmentName' => $lesson->attachment_name,
                            'attachmentType' => $lesson->attachment_type,
                            'duration'       => $lesson->duration,
                            'isPreview'      => (bool) $lesson->is_preview,
                            'isFree'         => (bool) ($lesson->is_free ?? $lesson->is_preview),
                            'price'          => (float) ($lesson->price ?? 0),
                            'isLive'         => (bool) $lesson->is_live,
                            'liveClassId'    => $lesson->live_class_id,
                        ];
                    })->toArray(),
                ];
            })->toArray(),
        ];

        return view('instructor.course-builder', array_merge([
            'course' => $course,
            'courseDataArray' => $courseDataArray,
        ], $this->builderFormData($course)));
    }

    /**
     * Update course
     */
    public function update(StoreCourseRequest $request, $id)
    {
        $user = Auth::user();

        if ($user->role !== 'admin' && !$user->is_approved) {
            return redirect()->route('instructor.pending')->with('error', 'Your instructor account is not approved yet.');
        }

        $course = Course::findOrFail($id);

        if (!$course->canUserManageCurriculum($user)) {
            abort(403, 'Unauthorized action.');
        }

        $isAdmin = $user->role === 'admin';
        $canManagePricingAndTeam = $course->canUserManagePricingAndTeam($user);

        $taxonomy = $this->resolveTaxonomySelections($request);
        $categoryModel = $taxonomy['category'];
        $categoryLabel = $taxonomy['categoryLabel'];
        $languageModel = $taxonomy['language'];
        $languageLabel = $taxonomy['languageLabel'];

        $originalStatus = $course->status;

        DB::beginTransaction();

        try {
            $featuredImagePath = $course->featured_image;
            if ($request->hasFile('featured_image')) {
                if ($course->featured_image) {
                    $this->fileUploadService->deleteFile($course->featured_image);
                }
                $featuredImagePath = $this->fileUploadService->uploadCourseImage($request->file('featured_image'));
            }

            $introVideoPath = $course->intro_video;
            if ($request->hasFile('intro_video')) {
                if ($course->intro_video) {
                    $this->fileUploadService->deleteFile($course->intro_video);
                }
                $introVideoPath = $this->fileUploadService->uploadIntroVideo($request->file('intro_video'));
            }

            if ($isAdmin && in_array($request->input('status'), ['draft', 'pending', 'published', 'archived'], true)) {
                $newStatus = $request->input('status');
            } else {
                $newStatus = $request->input('status') === 'published' ? 'pending' : 'draft';

                if ($originalStatus === 'published' || $originalStatus === 'pending') {
                    if ($newStatus !== 'draft') {
                        $newStatus = 'pending';
                    }
                }
            }

            $updatePayload = [
                'course_type' => 'course',
                'delivery_mode' => 'online',
                'drip_enabled' => filter_var($request->input('drip_enabled', $course->drip_enabled), FILTER_VALIDATE_BOOLEAN),
                'title' => $request->title,
                'description' => $request->description,
                'featured_image' => $featuredImagePath,
                'intro_video' => $introVideoPath,
                'visibility' => $request->visibility,
                'public_course' => filter_var($request->input('public_course', false), FILTER_VALIDATE_BOOLEAN),
                'max_students' => filter_var($request->input('public_course', false), FILTER_VALIDATE_BOOLEAN) ? null : $request->max_students,
                'difficulty' => $request->difficulty,
                'schedule_enabled' => filter_var($request->input('schedule_enabled', false), FILTER_VALIDATE_BOOLEAN),
                'schedule_date' => filter_var($request->input('schedule_enabled', false), FILTER_VALIDATE_BOOLEAN) ? $request->schedule_date : null,
                'course_category_id' => $categoryModel?->id,
                'course_language_id' => $languageModel?->id,
                'category' => $categoryLabel,
                'language' => $languageLabel,
                'tags' => $request->tags,
                'requirements'   => $request->requirements,
                'objectives'     => $request->objectives,
                'seo_title' => $request->input('seo_title', $course->seo_title),
                'seo_description' => $request->input('seo_description', $course->seo_description),
                'seo_tags' => $request->input('seo_tags', $course->seo_tags),
                'marketing_copy' => is_array($request->input('marketing_copy')) ? $request->input('marketing_copy') : $course->marketing_copy,
                'status'         => $newStatus,
                'is_live_course' => filter_var($request->input('is_live_course', false), FILTER_VALIDATE_BOOLEAN),
                'has_discount'   => filter_var($request->input('has_discount', false), FILTER_VALIDATE_BOOLEAN),
                'discount_amount' => (float) ($request->input('discount_amount', 0) ?: 0),
                'attendance_threshold' => max(0, min(100, (int) ($request->input('attendance_threshold', 75) ?? 75))),
                'allow_online_f2f_payment' => filter_var($request->input('allow_online_f2f_payment', false), FILTER_VALIDATE_BOOLEAN),
            ];
            if ($isAdmin) {
                $updatePayload['f2f_commission_enabled'] = filter_var($request->input('f2f_commission_enabled', false), FILTER_VALIDATE_BOOLEAN);
            }

            // Pricing & co-instructor / team assignment are strictly protected from co-editors
            if ($canManagePricingAndTeam) {
                $updatePayload['pricing_model'] = $request->pricing_model;
                $updatePayload['price_currency'] = normalize_currency_code(
                    $request->input('price_currency', $course->price_currency ?? base_currency_code()),
                    base_currency_code()
                );
                $updatePayload['regular_price'] = $request->pricing_model === 'paid' ? $request->regular_price : null;
                $updatePayload['sale_price'] = $request->pricing_model === 'paid' ? $request->sale_price : null;
                if ($isAdmin && $request->filled('instructor_id')) {
                    $updatePayload['instructor_id'] = (int) $request->input('instructor_id');
                }
                if ($request->has('assistant_instructor_id')) {
                    $updatePayload['assistant_instructor_id'] = $request->filled('assistant_instructor_id')
                        ? (int) $request->input('assistant_instructor_id')
                        : null;
                }
            }

            $course->update($updatePayload);

            if ($canManagePricingAndTeam && $request->has('co_instructors')) {
                $this->syncCoInstructors($course, $request->input('co_instructors', []));
            }
            if ($request->has('faqs')) {
                $this->syncFaqs($course, $request->input('faqs', []));
            }

            $existingTopicIds = $course->topics->pluck('id')->toArray();
            $existingLessonIds = $course->topics->flatMap->lessons->pluck('id')->toArray();

            $requestTopicIds = [];
            $requestLessonIds = [];

            if ($request->has('topics') && is_array($request->topics)) {
                foreach ($request->topics as $topicIndex => $topicData) {
                    $topicId = $topicData['id'] ?? null;

                    $topicPayload = [
                        'title'       => $topicData['title'] ?? '',
                        'description' => $topicData['description'] ?? null,
                        'is_free'     => filter_var($topicData['is_free'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'price'       => isset($topicData['price']) && is_numeric($topicData['price']) ? (float) $topicData['price'] : 0.00,
                        'order'       => $topicIndex,
                    ];

                    if ($topicId && CourseTopic::where('id', $topicId)->where('course_id', $course->id)->exists()) {
                        $topic = CourseTopic::find($topicId);
                        $topic->update($topicPayload);
                    } else {
                        $topic = CourseTopic::create(array_merge(['course_id' => $course->id], $topicPayload));
                    }

                    $requestTopicIds[] = $topic->id;

                    if (isset($topicData['lessons']) && is_array($topicData['lessons'])) {
                        foreach ($topicData['lessons'] as $lessonIndex => $lessonData) {
                            $lessonId = $lessonData['id'] ?? null;
                            $lessonVideoPath = null;
                            $lessonVideoUrl = null;

                            $contentType  = $lessonData['lesson_type'] ?? ($lessonData['contentType'] ?? 'video');
                            $isLiveLesson = ($lessonData['video_type'] ?? 'url') === 'live' || $contentType === 'live';

                            if (!$isLiveLesson && ($lessonData['video_type'] ?? 'url') === 'upload') {
                                $videoFile = null;
                                $simpleKey = "lesson_video_{$topicIndex}_{$lessonIndex}";

                                if ($request->hasFile($simpleKey)) {
                                    $videoFile = $request->file($simpleKey);
                                } elseif ($request->hasFile("topics.{$topicIndex}.lessons.{$lessonIndex}.video_file")) {
                                    $videoFile = $request->file("topics.{$topicIndex}.lessons.{$lessonIndex}.video_file");
                                } else {
                                    $allFiles = $request->allFiles();
                                    if (isset($allFiles[$simpleKey])) {
                                        $videoFile = $allFiles[$simpleKey];
                                    } elseif (isset($allFiles['topics'][$topicIndex]['lessons'][$lessonIndex]['video_file'])) {
                                        $videoFile = $allFiles['topics'][$topicIndex]['lessons'][$lessonIndex]['video_file'];
                                    }
                                }

                                if ($videoFile && $videoFile instanceof \Illuminate\Http\UploadedFile) {
                                    if ($lessonId && $lesson = Lesson::find($lessonId)) {
                                        if ($lesson->video_path) {
                                            $this->fileUploadService->deleteFile($lesson->video_path);
                                        }
                                    }
                                    $lessonVideoPath = $this->fileUploadService->uploadLessonVideo($videoFile);
                                } elseif (!empty($lessonData['video_path'])) {
                                    $lessonVideoPath = str_replace(asset('storage/'), '', $lessonData['video_path']);
                                } elseif ($lessonId && $lesson = Lesson::find($lessonId)) {
                                    $lessonVideoPath = $lesson->video_path;
                                }
                            } else {
                                $lessonVideoUrl = $lessonData['video_url'] ?? null;
                            }

                            // Handle downloadable material file (PDF, PPT, DOC, ZIP)
                            $materialPath = null;
                            $materialName = null;
                            $materialType = null;
                            $simpleMatKey = "lesson_material_{$topicIndex}_{$lessonIndex}";
                            $matFile = $request->file($simpleMatKey) ?? $request->file("topics.{$topicIndex}.lessons.{$lessonIndex}.material_file");
                            if ($matFile && $matFile instanceof \Illuminate\Http\UploadedFile) {
                                if ($lessonId && $lesson = Lesson::find($lessonId)) {
                                    if ($lesson->attachment_path) {
                                        $this->fileUploadService->deleteFile($lesson->attachment_path);
                                    }
                                }
                                $matInfo = $this->fileUploadService->uploadLessonMaterial($matFile);
                                $materialPath = $matInfo['file_path'];
                                $materialName = $matInfo['original_filename'];
                                $materialType = $matInfo['extension'];
                            } elseif (!empty($lessonData['attachment_path'])) {
                                $materialPath = str_replace(asset('storage/'), '', $lessonData['attachment_path']);
                                $materialName = $lessonData['attachment_name'] ?? null;
                                $materialType = $lessonData['attachment_type'] ?? null;
                            } elseif ($lessonId && $lesson = Lesson::find($lessonId)) {
                                $materialPath = $lesson->attachment_path;
                                $materialName = $lesson->attachment_name;
                                $materialType = $lesson->attachment_type;
                            }

                            $isPreview = filter_var($lessonData['is_preview'] ?? false, FILTER_VALIDATE_BOOLEAN);
                            $isFree = filter_var($lessonData['is_free'] ?? $isPreview, FILTER_VALIDATE_BOOLEAN) || $isPreview;

                            $lessonPayload = [
                                'course_topic_id' => $topic->id,
                                'title'           => $lessonData['title'] ?? '',
                                'lesson_type'     => $contentType,
                                'description'     => $lessonData['description'] ?? null,
                                'content_text'    => $lessonData['content_text'] ?? null,
                                'video_type'      => $lessonData['video_type'] ?? 'url',
                                'video_url'       => $isLiveLesson ? null : $lessonVideoUrl,
                                'video_path'      => $isLiveLesson ? null : $lessonVideoPath,
                                'attachment_path' => $materialPath,
                                'attachment_name' => $materialName,
                                'attachment_type' => $materialType,
                                'duration'        => $lessonData['duration'] ?? null,
                                'is_preview'      => $isPreview,
                                'is_free'         => $isFree,
                                'price'           => isset($lessonData['price']) && is_numeric($lessonData['price']) ? (float) $lessonData['price'] : 0.00,
                                'is_live'         => $isLiveLesson,
                                'order'           => $lessonIndex,
                            ];

                            if ($lessonId && $lesson = Lesson::find($lessonId)) {
                                if ($lesson->topic && $lesson->topic->course_id === $course->id) {
                                    $lesson->update($lessonPayload);
                                } else {
                                    $lesson = Lesson::create($lessonPayload);
                                }
                            } else {
                                $lesson = Lesson::create($lessonPayload);
                            }

                            $requestLessonIds[] = $lesson->id;
                        }
                    }
                }
            }

            $lessonsToDelete = array_diff($existingLessonIds, $requestLessonIds);
            if (!empty($lessonsToDelete)) {
                $lessonsToDeleteRecords = Lesson::whereIn('id', $lessonsToDelete)
                    ->whereHas('topic', function ($q) use ($course) {
                        $q->where('course_id', $course->id);
                    })->get();
                foreach ($lessonsToDeleteRecords as $lesson) {
                    if ($lesson->video_path) {
                        $this->fileUploadService->deleteFile($lesson->video_path);
                    }
                }
                Lesson::whereIn('id', $lessonsToDelete)
                    ->whereHas('topic', function ($q) use ($course) {
                        $q->where('course_id', $course->id);
                    })->delete();
            }

            $topicsToDelete = array_diff($existingTopicIds, $requestTopicIds);
            if (!empty($topicsToDelete)) {
                CourseTopic::whereIn('id', $topicsToDelete)
                    ->where('course_id', $course->id)
                    ->delete();
            }

            DB::commit();

            $publish = filter_var($request->input('status', 'draft'), FILTER_VALIDATE_BOOLEAN) || $request->input('status') === 'published';
            $wasPublishedOrPending = ($originalStatus === 'published' || $originalStatus === 'pending');

            if ($newStatus !== 'draft') {
                $this->notifyCourseUpdate($course, $user);
            }

            $successMessage = $wasPublishedOrPending && $newStatus !== 'draft'
                ? 'Course updated successfully! Changes are pending admin approval.'
                : 'Course draft updated successfully!';

            $redirectUrl = $isAdmin ? route('admin.courses.index') : route('instructor.courses');

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'course' => [
                        'id' => $course->id,
                        'title' => $course->title,
                        'status' => $course->status,
                        'url' => $redirectUrl,
                    ],
                ], 200);
            }

            return redirect()->to($redirectUrl)
                ->with('success', $successMessage);

        } catch (ValidationException $e) {
            DB::rollBack();

            if (isset($featuredImagePath) && $featuredImagePath !== $course->featured_image) {
                $this->fileUploadService->deleteFile($featuredImagePath);
            }
            if (isset($introVideoPath) && $introVideoPath !== $course->intro_video) {
                $this->fileUploadService->deleteFile($introVideoPath);
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($featuredImagePath) && $featuredImagePath !== $course->featured_image) {
                $this->fileUploadService->deleteFile($featuredImagePath);
            }
            if (isset($introVideoPath) && $introVideoPath !== $course->intro_video) {
                $this->fileUploadService->deleteFile($introVideoPath);
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update course: ' . $e->getMessage(),
                    'error' => config('app.debug') ? $e->getMessage() : 'An error occurred. Please try again.',
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', 'Failed to update course. Please try again.');
        }
    }

    /**
     * Notify course update.
     */
    protected function notifyCourseUpdate(Course $course, User $instructor): void
    {
        $emailPreferences = settings('email.preferences', []);

        if (empty($emailPreferences['course_update_digest'])) {
            return;
        }

        if (!$this->notificationPreferenceService->prefers($instructor, 'course_updates')) {
            return;
        }

        $course->loadMissing('instructor');

        $enrollments = $course->enrollments()
            ->whereIn('status', [Enrollment::STATUS_APPROVED, Enrollment::STATUS_COMPLETED])
            ->with('user.notificationSettings')
            ->get()
            ->filter(function (Enrollment $enrollment) {
                $student = $enrollment->user;

                if (!$student) {
                    return false;
                }

                return $this->notificationPreferenceService->prefers($student, 'course_updates');
            })
            ->unique('user_id');

        foreach ($enrollments as $enrollment) {
            $student = $enrollment->user;

            if (!$student || empty($student->email)) {
                continue;
            }

            try {
                Mail::to($student->email)->send(new CourseUpdatedMail(
                    $course,
                    $instructor,
                    sprintf('%s just refreshed the course content. Log in to explore the latest updates.', $instructor->name),
                    now(),
                    $student->name
                ));
            } catch (\Throwable $e) {
                \Log::error("Course updated notification email failed for student ID {$student->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Builder form data.
     */
    private function builderFormData(?Course $course = null): array
    {
        $categoryOptions = CourseCategory::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        $languageOptions = CourseLanguage::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        $user = Auth::user();
        return [
            'categoryOptions' => $categoryOptions,
            'languageOptions' => $languageOptions,
            'selectedCategoryId' => old('course_category_id', $course?->course_category_id),
            'selectedLanguageId' => old('course_language_id', $course?->course_language_id),
            'selectedCategoryLabel' => old('category_label', $course?->category),
            'selectedLanguageLabel' => old('language_label', $course?->language),
            'isAdminBuilder' => $user?->role === 'admin',
            'canManagePricingAndTeam' => $course ? $course->canUserManagePricingAndTeam($user) : true,
            'instructorsList' => User::where('role', 'instructor')
                ->where('is_approved', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
            'questionBanks' => collect([]),
        ];
    }

    /**
     * Resolve taxonomy selections.
     */
    private function resolveTaxonomySelections(Request $request): array
    {
        $category = null;
        $language = null;

        if ($request->filled('course_category_id')) {
            $category = CourseCategory::find($request->input('course_category_id'));
        }

        if ($request->filled('course_language_id')) {
            $language = CourseLanguage::find($request->input('course_language_id'));
        }

        $categoryLabel = $category?->name ?? $request->input('category_label') ?? $request->input('category');
        $languageLabel = $language?->name ?? $request->input('language_label') ?? $request->input('language');

        $categoryLabel = $categoryLabel !== null ? trim($categoryLabel) : null;
        $languageLabel = $languageLabel !== null ? trim($languageLabel) : null;

        if ($categoryLabel === '') {
            $categoryLabel = null;
        }

        if ($languageLabel === '') {
            $languageLabel = null;
        }

        return [
            'category' => $category,
            'categoryLabel' => $categoryLabel,
            'language' => $language,
            'languageLabel' => $languageLabel,
        ];
    }

    /**
     * Public disk file meta.
     */
    private function publicDiskFileMeta(string $path): array
    {
        $size = 0;
        $mimeType = 'application/octet-stream';

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        if ($disk->exists($path)) {
            $size = $disk->size($path);

            try {
                $mimeType = $disk->mimeType($path);
            } catch (\Throwable $e) {
                $mimeType = 'application/octet-stream';
            }
        }

        return [$size, $mimeType];
    }

    /**
     * Generate course description using AI
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\GeminiService $geminiService
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateDescription(Request $request, \App\Services\GeminiService $geminiService)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        try {
            $prompt = "Write a comprehensive and engaging course description for a course titled '{$request->title}'. 
                      The description should be structured with HTML tags suitable for a rich text editor.
                      
                      **Requirements:**
                      1. **Structure:**
                         - Start with a compelling **Introduction** (use <p>).
                         - Include a **What You Will Learn** section with a bulleted list (<ul><li>...</li></ul>).
                         - Include a **Target Audience** section (use <p>).
                         - End with a **Conclusion** or call to action (use <p>).
                      2. **Formatting:**
                         - Use <h2> for section headings.
                         - Use <strong> for emphasis.
                         - Do NOT use markdown code blocks (like ```html ... ```). Return raw HTML only.
                      3. **Tone:** Professional, encouraging, and informative.
                      4. **Length:** detailed enough to sell the course (approx. 300-500 words).";

            $description = $geminiService->generateContent($prompt);

            // Clean up any potential markdown code blocks if the AI still includes them
            $description = preg_replace('/^```html\s*|\s*```$/', '', trim($description));
            // Also remove just ``` if it appears
            $description = preg_replace('/^```\s*|\s*```$/', '', trim($description));

            return response()->json([
                'success' => true,
                'description' => $description,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate description: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AI Course Builder: one prompt (and optional document) drafts full course + curriculum.
     */
    public function aiCourseBuilder(Request $request, GeminiService $geminiService): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string|max:2000',
            'document' => 'nullable|file|mimes:txt,md,csv,html,json|max:2048',
        ]);

        $docText = null;
        if ($request->hasFile('document')) {
            $docText = @file_get_contents($request->file('document')->getRealPath()) ?: null;
        }

        $result = $geminiService->draftFullCourse($request->input('prompt'), $docText);

        return response()->json([
            'success' => true,
            'source' => $result['source'],
            'draft' => $result['data'],
        ]);
    }

    /**
     * AI Studio: outline, lesson_text, quiz_questions, assignment_rubric, faqs, marketing_copy.
     */
    public function aiStudio(Request $request, GeminiService $geminiService): JsonResponse
    {
        $request->validate([
            'mode' => 'required|in:outline,lesson_text,quiz_questions,assignment_rubric,faqs,marketing_copy',
            'prompt' => 'required|string|max:2000',
            'course_title' => 'nullable|string|max:255',
            'document' => 'nullable|file|mimes:txt,md,csv,html,json|max:2048',
        ]);

        $docText = null;
        if ($request->hasFile('document')) {
            $docText = @file_get_contents($request->file('document')->getRealPath()) ?: null;
        }

        $result = $geminiService->generateStudioContent($request->input('mode'), $request->input('prompt'), [
            'course_title' => $request->input('course_title'),
            'document_text' => $docText,
        ]);

        return response()->json([
            'success' => true,
            'mode' => $request->input('mode'),
            'source' => $result['source'],
            'data' => $result['data'],
        ]);
    }

    /**
     * One-link YouTube playlist import into lesson drafts.
     */
    public function importYoutubePlaylist(Request $request, YouTubePlaylistService $ytService): JsonResponse
    {
        $request->validate([
            'playlist_url' => 'required|string|max:500',
        ]);

        $result = $ytService->fetchPlaylistVideos($request->input('playlist_url'));

        return response()->json([
            'success' => empty($result['error']),
            'playlist_id' => $result['playlist_id'],
            'title' => $result['title'],
            'videos' => $result['videos'],
            'message' => $result['error'] ?? ('Imported ' . count($result['videos']) . ' videos.'),
        ], empty($result['error']) ? 200 : 422);
    }

    /**
     * Sync co-instructors for a course (only approved instructors; primary keeps remaining %).
     */
    private function syncCoInstructors(Course $course, mixed $rows): void
    {
        if (!is_array($rows)) {
            return;
        }

        $course->coInstructors()->delete();
        $totalShare = 0.0;

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $email = strtolower(trim((string) ($row['email'] ?? '')));
            if ($email === '') {
                continue;
            }
            $coUser = User::where('email', $email)
                ->where('role', 'instructor')
                ->where('is_approved', true)
                ->first();

            if (!$coUser || $coUser->id === (int) $course->instructor_id) {
                continue;
            }

            $share = max(0.0, min(100.0, (float) ($row['revenue_share'] ?? $row['revenue_share_percent'] ?? 0)));
            if ($totalShare + $share > 100.0) {
                $share = max(0.0, 100.0 - $totalShare);
            }
            $totalShare += $share;

            CourseCoInstructor::updateOrCreate(
                ['course_id' => $course->id, 'user_id' => $coUser->id],
                [
                    'revenue_share' => $share,
                    'can_edit' => filter_var($row['can_edit'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ]
            );
        }
    }

    /**
     * Sync Course FAQs.
     */
    private function syncFaqs(Course $course, mixed $rows): void
    {
        if (!is_array($rows)) {
            return;
        }
        $course->faqs()->delete();
        foreach (array_values($rows) as $idx => $row) {
            if (!is_array($row)) {
                continue;
            }
            $q = trim((string) ($row['question'] ?? ''));
            $a = trim((string) ($row['answer'] ?? ''));
            if ($q === '' || $a === '') {
                continue;
            }
            CourseFaq::create([
                'course_id' => $course->id,
                'question' => $q,
                'answer' => $a,
                'order' => $idx,
            ]);
        }
    }
}
