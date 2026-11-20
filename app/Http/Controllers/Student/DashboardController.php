<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\LiveClassService;
use App\Support\DashboardFilters;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Dashboard Controller
 *
 * This controller handles the Scholar (student) dashboard functionality for Maarifazone.
 *
 * @package App\Http\Controllers\Student
 * @author Renatus Bernardo
 */
class DashboardController extends Controller
{
    /**
     * Initialize dependencies for this class.
     */
    public function __construct(
        protected LiveClassService $liveClassService
    ) {}

    /**
     * Display the student dashboard
     */
    public function index(Request $request)
    {
        $user = $this->currentUser();

        $filters = DashboardFilters::fromRequest($request);
        $dateWindow = DashboardFilters::dateWindow($filters['date_range']);
        $search = $filters['search'];
        $status = $filters['status'];
        $perPage = $filters['per_page'];

        $upcomingLiveClass = $this->liveClassService->getUpcomingForStudent($user);
        $kpis = $this->buildKpis($user);

        $enrollmentStatuses = in_array($status, [Enrollment::STATUS_APPROVED, Enrollment::STATUS_COMPLETED, Enrollment::STATUS_PENDING], true)
            ? [$status]
            : [Enrollment::STATUS_APPROVED, Enrollment::STATUS_COMPLETED];

        $recentEnrollments = Enrollment::with(['course.instructor', 'course.topics.lessons'])
            ->where('user_id', $user->id)
            ->whereIn('status', $enrollmentStatuses)
            ->when($dateWindow, fn ($q) => $q->whereBetween('enrolled_at', $dateWindow))
            ->when($search !== '', fn ($q) => $q->where(function ($sub) use ($search): void {
                $sub->whereHas('course', fn ($c) => $c->where('title', 'like', "%{$search}%"))
                    ->orWhereHas('course.instructor', fn ($i) => $i->where('name', 'like', "%{$search}%"));
            }))
            ->orderByDesc('enrolled_at')
            ->paginate($perPage, ['*'], 'courses_page')
            ->withQueryString()
            ->through(function (Enrollment $enrollment) {
                $enrollment->lessons_count = $enrollment->course?->topics->sum(fn ($topic) => $topic->lessons->count()) ?? 0;

                return $enrollment;
            });

        $recentNotifications = $user->notifications()->latest()->limit(6)->get();

        $recentTickets = SupportTicket::query()
            ->where('user_id', $user->id)
            ->withCount('messages')
            ->orderByDesc('last_reply_at')
            ->limit(4)
            ->get();

        $stats = $kpis;

        $firstName = Str::of($user->name)->before(' ');
        $firstName = $firstName === '' ? $user->name : $firstName;

        $statusOptions = [
            'all' => 'All Statuses',
            'approved' => 'Active / Approved',
            'completed' => 'Completed',
            'pending' => 'Pending',
        ];

        return view('student.dashboard', compact(
            'user',
            'stats',
            'recentEnrollments',
            'recentNotifications',
            'recentTickets',
            'firstName',
            'upcomingLiveClass',
            'filters',
            'statusOptions'
        ));
    }

    /**
     * Return live dashboard summary for real-time KPIs and notification feed syncing.
     */
    public function liveSummary(Request $request): JsonResponse
    {
        $user = $this->currentUser();
        $kpis = $this->buildKpis($user);

        $notifications = $user->notifications()
            ->latest()
            ->limit(6)
            ->get()
            ->map(function ($notification): array {
                $data = is_array($notification->data) ? $notification->data : [];

                return [
                    'id' => $notification->id,
                    'title' => (string) ($data['title'] ?? 'Notification'),
                    'message' => (string) ($data['message'] ?? ''),
                    'type' => (string) ($data['type'] ?? 'info'),
                    'url' => $data['url'] ?? null,
                    'read_url' => route('student.notifications.read', $notification->id),
                    'is_unread' => $notification->read_at === null,
                    'time_ago' => $notification->created_at?->diffForHumans() ?? 'Just now',
                ];
            })
            ->values();

        return response()->json([
            'stats' => [
                'total_enrollments' => $kpis['total_enrollments'],
                'completed_courses' => $kpis['completed_courses'],
                'pending_enrollments' => $kpis['pending_enrollments'],
                'total_lessons' => $kpis['total_lessons'],
                'unread_notifications' => $kpis['unread_notifications'],
                'open_tickets' => $kpis['open_tickets'],
            ],
            'notifications' => $notifications,
            'synced_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Compute student dashboard KPI metrics from database records.
     *
     * @return array<string, int|float>
     */
    private function buildKpis(User $user): array
    {
        $totalEnrollments = Enrollment::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'completed'])
            ->count();

        $completedCourses = Enrollment::where('user_id', $user->id)
            ->where('status', Enrollment::STATUS_COMPLETED)
            ->count();

        $pendingEnrollments = Enrollment::where('user_id', $user->id)
            ->where('status', Enrollment::STATUS_PENDING)
            ->count();

        $totalLessons = Enrollment::with('course.topics.lessons')
            ->where('user_id', $user->id)
            ->whereIn('status', ['approved', 'completed'])
            ->get()
            ->sum(function (Enrollment $enrollment) {
                return $enrollment->course?->topics->sum(fn ($topic) => $topic->lessons->count()) ?? 0;
            });

        return [
            'total_enrollments' => $totalEnrollments,
            'completed_courses' => $completedCourses,
            'pending_enrollments' => $pendingEnrollments,
            'total_lessons' => $totalLessons,
            'unread_notifications' => $user->unreadNotifications()->count(),
            'open_tickets' => SupportTicket::where('user_id', $user->id)->where('status', '!=', 'closed')->count(),
        ];
    }

    /**
     * Current user.
     */
    private function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
