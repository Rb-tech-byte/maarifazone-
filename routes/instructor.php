<?php

/**
 * Instructor Studio Portal Routes
 *
 * Defines instructor routes for assigned course delivery, scholar progress,
 * live classes, discussions, earnings, and payout requests in Maarifazone.
 * (Note: Instructors cannot create courses; courses are assigned by Admin).
 *
 * @author Renatus Bernardo
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Instructor\RegisterController;
use App\Http\Controllers\Instructor\DashboardController;
use App\Http\Controllers\Instructor\PendingController;
use App\Http\Controllers\Instructor\CoursesController;
use App\Http\Controllers\Instructor\StudentsController;
use App\Http\Controllers\Instructor\AnalyticsController;
use App\Http\Controllers\Instructor\ReviewsController;
use App\Http\Controllers\Instructor\EarningsController;
use App\Http\Controllers\Instructor\SettingsController;
use App\Http\Controllers\Instructor\NotificationController;
use App\Http\Controllers\Instructor\BundleController;
use App\Http\Controllers\Instructor\CouponController as InstructorCouponController;
use App\Http\Controllers\Instructor\CourseDiscussionController as InstructorCourseDiscussionController;
use App\Http\Controllers\Instructor\LiveClassController;

// Guest instructor routes
Route::middleware('guest')->group(function () {
    Route::get('/become-instructor', [RegisterController::class, 'showRegistrationForm'])->name('instructor.register');
    Route::post('/become-instructor', [RegisterController::class, 'register'])->name('instructor.register.submit');
});

// Authenticated instructor routes
Route::middleware(['auth', 'role:instructor'])->prefix('instructor')->name('instructor.')->group(function () {
    // Pending instructor routes
    Route::get('/pending', [PendingController::class, 'index'])->name('pending');

    // Dashboard routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Assigned Courses & Scholars
    Route::get('/courses', [CoursesController::class, 'index'])->name('courses');
    Route::get('/courses/list', [CoursesController::class, 'index'])->name('courses.index');
    Route::get('/courses/create', function () {
        return redirect()->route('instructor.courses')->with('error', 'Course creation is managed by Maarifazone Administration. Courses are assigned to you by Admin.');
    })->name('courses.create');
    Route::post('/courses', function () {
        abort(403, 'Courses are created and curated exclusively by Maarifazone Administration.');
    });
    Route::get('/courses/{course}', function (\App\Models\Course $course) {
        return redirect()->route('courses.show', $course->id);
    })->name('courses.show');
    Route::get('/courses/{course}/edit', function (\App\Models\Course $course) {
        return redirect()->route('courses.show', $course->id);
    })->name('courses.edit');
    Route::put('/courses/{course}', function () {
        abort(403, 'Courses are edited and managed exclusively by Maarifazone Administration.');
    });
    Route::delete('/courses/{course}', function () {
        abort(403, 'Courses are managed exclusively by Maarifazone Administration.');
    });

    Route::get('/students', [StudentsController::class, 'index'])->name('students');
    Route::get('/students/{id}', [StudentsController::class, 'show'])->name('students.show');

    // Analytics routes
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');

    // Reviews routes
    Route::get('/reviews', [ReviewsController::class, 'index'])->name('reviews');
    Route::post('/reviews/{reviewId}/reply', [ReviewsController::class, 'reply'])->name('reviews.reply');
    Route::put('/reviews/{reviewId}/reply/{replyId}', [ReviewsController::class, 'updateReply'])->name('reviews.reply.update');

    // Earnings & Payout Requests
    Route::get('/earnings', [EarningsController::class, 'index'])->name('earnings');
    Route::post('/earnings/stripe-connect', [EarningsController::class, 'connectStripe'])->name('earnings.stripe-connect');
    Route::get('/withdrawals', [EarningsController::class, 'withdrawals'])->name('withdrawals');
    Route::get('/payouts', [EarningsController::class, 'withdrawals'])->name('payouts.index');
    Route::post('/withdrawals', [EarningsController::class, 'storeWithdrawal'])->name('withdrawals.store');

    // Settings routes
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::put('/settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.notifications.update');
    Route::delete('/settings/account', [SettingsController::class, 'destroy'])->name('settings.account.destroy');

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/feed', [NotificationController::class, 'feed'])->name('notifications.feed');
    Route::get('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Bundles routes
    Route::resource('bundles', BundleController::class);

    // Coupons
    Route::resource('coupons', InstructorCouponController::class);

    // Course Discussions (Public & Monitored)
    Route::prefix('discussions')->name('discussions.')->group(function () {
        Route::get('/', [InstructorCourseDiscussionController::class, 'index'])->name('index');
        Route::post('/course/{courseId}', [InstructorCourseDiscussionController::class, 'store'])->name('store');
        Route::get('/{id}', [InstructorCourseDiscussionController::class, 'show'])->name('show');
        Route::post('/{id}/replies', [InstructorCourseDiscussionController::class, 'storeReply'])->name('replies.store');
        Route::post('/{id}/pin', [InstructorCourseDiscussionController::class, 'togglePin'])->name('pin');
        Route::post('/{id}/announcement', [InstructorCourseDiscussionController::class, 'toggleAnnouncement'])->name('announcement');
        Route::delete('/{id}', [InstructorCourseDiscussionController::class, 'destroy'])->name('destroy');
        Route::delete('/{id}/replies/{replyId}', [InstructorCourseDiscussionController::class, 'destroyReply'])->name('replies.destroy');
    });

    // Live Classes
    Route::get('live-classes/lessons', [LiveClassController::class, 'getLessonsForCourse'])->name('live-classes.lessons');
    Route::post('live-classes/{id}/start', [LiveClassController::class, 'startSession'])->name('live-classes.start');
    Route::post('live-classes/{id}/end', [LiveClassController::class, 'endSession'])->name('live-classes.end');
    Route::resource('live-classes', LiveClassController::class);

    Route::get('/help', [\App\Http\Controllers\HelpController::class, 'index'])->name('help.index');
});
