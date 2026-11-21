<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\Payment;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

/**
 * Builds clean, fast dashboard analytics for Maarifazone.
 *
 * @package App\Services
 * @author Renatus Bernardo
 */
class RoleDashboardService
{
    /**
     * Assemble dashboard analytics for the authenticated user.
     */
    public function buildForUser(User $user, array $rawFilters = []): array
    {
        $profile = 'admin';
        $scope = [
            'profile' => $profile,
            'role_label' => $user->primaryRoleLabel(),
            'scope_label' => 'Full Academy Oversight',
        ];

        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // 1. Overview Metrics
        $totalRevenue = (float) Payment::where('status', Payment::STATUS_COMPLETED)->sum('amount');
        $thisMonthRevenue = (float) Payment::where('status', Payment::STATUS_COMPLETED)
            ->where('created_at', '>=', $startOfMonth)
            ->sum('amount');
        $lastMonthRevenue = (float) Payment::where('status', Payment::STATUS_COMPLETED)
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('amount');
        $revenueChangePct = $lastMonthRevenue > 0
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : ($thisMonthRevenue > 0 ? 100.0 : 0.0);

        $totalStudents = User::where('role', 'student')->count();
        $thisMonthStudents = User::where('role', 'student')->where('created_at', '>=', $startOfMonth)->count();
        $lastMonthStudents = User::where('role', 'student')->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $studentsChangePct = $lastMonthStudents > 0
            ? round((($thisMonthStudents - $lastMonthStudents) / $lastMonthStudents) * 100, 1)
            : ($thisMonthStudents > 0 ? 100.0 : 0.0);

        $totalEnrollments = Enrollment::count();
        $thisMonthEnrollments = Enrollment::where('created_at', '>=', $startOfMonth)->count();
        $pendingOfflinePayments = Payment::where('payment_method', Payment::PAYMENT_METHOD_OFFLINE)
            ->where('status', Payment::STATUS_PENDING)
            ->count();

        $completedEnrollments = Enrollment::where('status', Enrollment::STATUS_COMPLETED)->count();
        $completionRate = $totalEnrollments > 0
            ? round(($completedEnrollments / $totalEnrollments) * 100, 1)
            : 0;

        $totalCourses = Course::count();
        $activeCourses = Course::where('status', 'published')->count();
        $averageRating = (float) (Review::where('is_approved', true)->avg('rating') ?: 5.0);

        $overview = [
            'total_revenue'            => $totalRevenue,
            'this_month_revenue'       => $thisMonthRevenue,
            'revenue_change_pct'       => $revenueChangePct,
            'total_students'           => $totalStudents,
            'students_change_pct'      => $studentsChangePct,
            'total_enrollments'        => $totalEnrollments,
            'this_month_enrollments'   => $thisMonthEnrollments,
            'pending_offline_payments' => $pendingOfflinePayments,
            'completion_rate'          => $completionRate,
            'total_courses'            => $totalCourses,
            'active_courses'           => $activeCourses,
            'average_rating'           => $averageRating,
        ];

        // 2. Top Performing Courses
        $coursesQuery = Course::withCount(['enrollments'])
            ->with(['instructor'])
            ->where('status', 'published')
            ->orderByDesc('enrollments_count')
            ->take(10)
            ->get()
            ->map(function (Course $c) {
                $rev = (float) Payment::where('status', Payment::STATUS_COMPLETED)
                    ->whereHas('enrollment', fn ($eq) => $eq->where('course_id', $c->id))
                    ->sum('amount');
                $compl = $c->enrollments_count > 0
                    ? round(($c->enrollments()->where('status', Enrollment::STATUS_COMPLETED)->count() / $c->enrollments_count) * 100, 1)
                    : 0;

                return [
                    'id'              => $c->id,
                    'title'           => $c->title,
                    'thumbnail'       => $c->thumbnail,
                    'instructor'      => $c->instructor?->name ?? 'Admin Assigned',
                    'enrollments'     => $c->enrollments_count,
                    'revenue'         => $rev,
                    'rating'          => round($c->rating, 1),
                    'completion_rate' => $compl,
                ];
            });

        $perPage = max(1, (int) ($rawFilters['per_page'] ?? 5));
        $page = Paginator::resolveCurrentPage('courses_page');
        $topCourses = new LengthAwarePaginator(
            $coursesQuery->forPage($page, $perPage)->values(),
            $coursesQuery->count(),
            $perPage,
            $page,
            ['pageName' => 'courses_page', 'path' => Paginator::resolveCurrentPath()]
        );

        // 3. Recent Transactions
        $paymentsQuery = Payment::with(['user', 'enrollment.course'])
            ->orderByDesc('id')
            ->take(20)
            ->get()
            ->map(function (Payment $p) {
                return [
                    'id'          => $p->id,
                    'date'        => $p->created_at?->format('M d, Y') ?? '—',
                    'description' => $p->enrollment?->course?->title ?? 'Course Enrollment',
                    'student'     => $p->user?->name ?? 'Scholar',
                    'amount'      => (float) $p->amount,
                    'status'      => $p->status,
                    'method'      => $p->payment_method,
                ];
            });

        $txPage = Paginator::resolveCurrentPage('tx_page');
        $recentTransactions = new LengthAwarePaginator(
            $paymentsQuery->forPage($txPage, $perPage)->values(),
            $paymentsQuery->count(),
            $perPage,
            $txPage,
            ['pageName' => 'tx_page', 'path' => Paginator::resolveCurrentPath()]
        );

        // 4. Student Engagement
        $activeCount = Enrollment::where('status', Enrollment::STATUS_APPROVED)->count();
        $completingCount = Enrollment::where('status', Enrollment::STATUS_COMPLETED)->count();
        $atRiskCount = max(0, $totalEnrollments - $activeCount - $completingCount);

        $studentEngagement = [
            'active_students'       => $activeCount,
            'active_percentage'     => $totalEnrollments > 0 ? round(($activeCount / $totalEnrollments) * 100, 1) : 0,
            'completing_students'   => $completingCount,
            'completing_percentage' => $totalEnrollments > 0 ? round(($completingCount / $totalEnrollments) * 100, 1) : 0,
            'at_risk_students'      => $atRiskCount,
            'at_risk_percentage'    => $totalEnrollments > 0 ? round(($atRiskCount / $totalEnrollments) * 100, 1) : 0,
        ];

        // 5. Activity & Security Logs
        $securityLogs = class_exists(Activity::class)
            ? Activity::latest()->take(5)->get()
            : collect();

        $recentActivity = $securityLogs->map(function ($act) {
            $desc = $act->description ?? 'System event';
            return [
                'description' => $desc,
                'message'     => $desc,
                'time'        => $act->created_at?->diffForHumans() ?? 'recently',
                'icon'        => 'fa-circle-info',
            ];
        });

        // 6. Trend Charts Data
        $chart30Days = $this->buildThirtyDaysTrends();
        $chart12Months = $this->buildTwelveMonthsTrends();

        return [
            'profile'              => $profile,
            'scope'                => $scope,
            'overview'             => $overview,
            'top_courses'          => $topCourses,
            'recent_transactions'  => $recentTransactions,
            'student_engagement'   => $studentEngagement,
            'security_logs'        => $securityLogs,
            'recent_activity'      => $recentActivity,
            'filters'              => $rawFilters,
            'revenue_trend'        => $chart30Days['revenue'],
            'enrollment_trend'     => $chart30Days['enrollment'],
            'charts'               => [
                'thirty_days' => [
                    'labels'  => $chart30Days['revenue']['labels'],
                    'revenue' => $chart30Days['revenue']['series'],
                ],
                'last_12_months' => $chart12Months,
            ],
        ];
    }

    /**
     * Compute 12-month trend for charts.
     */
    private function buildTwelveMonthsTrends(): array
    {
        $labels = [];
        $enrollments = [];
        $completions = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $labels[] = $month->format('M Y');
            $enrollments[] = Enrollment::whereBetween('created_at', [$start, $end])->count();
            $completions[] = Enrollment::where('status', Enrollment::STATUS_COMPLETED)->whereBetween('updated_at', [$start, $end])->count();
        }

        return [
            'labels'      => $labels,
            'enrollments' => $enrollments,
            'completions' => $completions,
        ];
    }

    /**
     * Compute 30-day timeline series for charts.
     */
    private function buildThirtyDaysTrends(): array
    {
        $dates = [];
        $revData = [];
        $enrData = [];

        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $label = now()->subDays($i)->format('M d');
            $dates[] = $label;

            $revData[] = (float) Payment::where('status', Payment::STATUS_COMPLETED)
                ->whereDate('created_at', $day)
                ->sum('amount');

            $enrData[] = Enrollment::whereDate('created_at', $day)->count();
        }

        return [
            'revenue' => [
                'labels' => $dates,
                'series' => $revData,
            ],
            'enrollment' => [
                'labels' => $dates,
                'series' => $enrData,
            ],
        ];
    }
}
