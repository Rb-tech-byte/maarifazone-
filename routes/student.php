<?php

/**
 * Scholar (Student) Portal Routes
 *
 * Defines scholar registration, online course player, lesson progress,
 * discussions, live classes, community Q&A, and support tickets in Maarifazone.
 *
 * @author Renatus Bernardo
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\RegisterController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\MyCoursesController;
use App\Http\Controllers\Student\StudentCourseController;
use App\Http\Controllers\Student\SettingsController;
use App\Http\Controllers\Student\PaymentsController;
use App\Http\Controllers\Student\NotificationController;
use App\Http\Controllers\Student\CommunityController;
use App\Http\Controllers\Student\AiChatController;
use App\Http\Controllers\Student\CourseDiscussionController;
use App\Http\Controllers\Student\LiveClassController;

// Guest student routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Authenticated scholar routes
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    // Dashboard & Enrolled Courses routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/live-summary', [DashboardController::class, 'liveSummary'])->name('dashboard.live');
    Route::get('/my-courses', [MyCoursesController::class, 'index'])->name('my-courses');
    Route::get('/courses', [MyCoursesController::class, 'index'])->name('courses.index');

    // Payments routes
    Route::get('/payments', [PaymentsController::class, 'index'])->name('payments');
    Route::get('/payments/live-status', [PaymentsController::class, 'liveStatus'])->name('payments.live');

    // Profile & Settings
    Route::get('/settings', [SettingsController::class, 'show'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::put('/settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.notifications.update');
    Route::delete('/settings/account', [SettingsController::class, 'destroy'])->name('settings.account.destroy');

    // Course Learning Player routes (Pure Maarifa Lessons & Progress)
    Route::get('/courses/{courseId}', [StudentCourseController::class, 'show'])->name('courses.show');
    Route::get('/courses/{courseId}/access', [StudentCourseController::class, 'show'])->name('courses.access');
    Route::post('/courses/{courseId}/lesson/load', [StudentCourseController::class, 'loadLesson'])->name('courses.lesson.load');
    Route::post('/courses/{courseId}/lesson/complete', [StudentCourseController::class, 'markComplete'])->name('courses.lesson.complete');
    Route::post('/courses/{courseId}/lesson/progress', [StudentCourseController::class, 'updateProgress'])->name('courses.lesson.progress');
    Route::get('/courses/{courseId}/item/{itemId}/next', [StudentCourseController::class, 'getNextItem'])->name('courses.item.next');
    Route::get('/courses/{courseId}/item/{itemId}/previous', [StudentCourseController::class, 'getPreviousItem'])->name('courses.item.previous');
    Route::post('/courses/{courseId}/search', [StudentCourseController::class, 'searchContent'])->name('courses.search');
    Route::get('/courses/{courseId}/sidebar/refresh', [StudentCourseController::class, 'refreshSidebar'])->name('courses.sidebar.refresh');
    Route::get('/courses/{courseId}/completion', [StudentCourseController::class, 'showCompletionPage'])->name('courses.completion');

    // Course Discussions routes
    Route::prefix('courses/{courseId}/discussions')->name('courses.discussions.')->group(function () {
        Route::get('/', [CourseDiscussionController::class, 'index'])->name('index');
        Route::post('/', [CourseDiscussionController::class, 'store'])->name('store');
        Route::get('/{id}', [CourseDiscussionController::class, 'show'])->name('show');
        Route::post('/{id}/replies', [CourseDiscussionController::class, 'storeReply'])->name('replies.store');
        Route::post('/{id}/like', [CourseDiscussionController::class, 'toggleLike'])->name('like');
        Route::post('/replies/{replyId}/like', [CourseDiscussionController::class, 'toggleReplyLike'])->name('replies.like');
        Route::delete('/{id}', [CourseDiscussionController::class, 'destroy'])->name('destroy');
        Route::delete('/{id}/replies/{replyId}', [CourseDiscussionController::class, 'destroyReply'])->name('replies.destroy');
    });

    // Live classes routes
    Route::get('/live-classes', [LiveClassController::class, 'index'])->name('live-classes.index');
    Route::get('/live-classes/{liveClass}', fn () => redirect()->route('student.live-classes.index'))->name('live-classes.show');
    Route::get('/help', fn () => redirect()->route('contact'))->name('help.index');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/feed', [NotificationController::class, 'feed'])->name('notifications.feed');
    Route::get('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Community Q&A routes
    Route::prefix('community')->name('community.')->group(function () {
        Route::get('/', [CommunityController::class, 'index'])->name('index');
        Route::post('/', [CommunityController::class, 'store'])->name('store');
        Route::get('/{id}', [CommunityController::class, 'show'])->name('show');
        Route::post('/{id}/answers', [CommunityController::class, 'storeAnswer'])->name('answers.store');
        Route::post('/answers/{id}/accept', [CommunityController::class, 'markAnswerAccepted'])->name('answers.accept');
        Route::post('/vote/{type}/{id}', [CommunityController::class, 'vote'])->name('vote');
    });

    // AI Chat routes
    Route::get('/ai-chat', [AiChatController::class, 'index'])->name('ai-chat.index');
    Route::post('/ai-chat/send', [AiChatController::class, 'sendMessage'])->name('ai-chat.send');

    // Support Tickets (Official Support with Platform Administration)
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Student\SupportTicketController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Student\SupportTicketController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Student\SupportTicketController::class, 'store'])->name('store');
        Route::get('/{ticket}', [\App\Http\Controllers\Student\SupportTicketController::class, 'show'])->name('show');
        Route::post('/{ticket}/reply', [\App\Http\Controllers\Student\SupportTicketController::class, 'reply'])->name('reply');
        Route::post('/{ticket}/close', [\App\Http\Controllers\Student\SupportTicketController::class, 'close'])->name('close');
    });
});
