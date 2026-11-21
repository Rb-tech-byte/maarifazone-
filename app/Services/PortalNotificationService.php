<?php

namespace App\Services;

use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/**
 * Portal notification service — centralizes internal user notifications and login event alerts.
 *
 * @author Renatus Bernardo
 */
class PortalNotificationService
{
    /**
     * Dispatch a database notification to a user with optional deduplication window.
     */
    public function notifyUser(
        ?User $user,
        string $title,
        string $message,
        string $type = 'info',
        ?string $url = null,
        ?string $dedupeTitle = null,
        int $dedupeMinutes = 0
    ): bool {
        if (!$user || !$this->notificationsTableReady()) {
            return false;
        }

        try {
            if ($dedupeMinutes > 0) {
                $targetTitle = $dedupeTitle ?? $title;
                $alreadySent = $user
                    ->notifications()
                    ->where('created_at', '>=', now()->subMinutes($dedupeMinutes))
                    ->limit(25)
                    ->get()
                    ->contains(fn($n) => ($n->data['title'] ?? '') === $targetTitle);

                if ($alreadySent) {
                    return false;
                }
            }

            $user->notify(new GeneralNotification($title, $message, $type, null, $url));

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Handle post-login internal notifications (session security notice, fee balance, unread inbox).
     */
    public function handleLogin(User $user): void
    {
        if (!$this->notificationsTableReady()) {
            return;
        }

        $dashboardUrl = $this->dashboardRouteFor($user);

        $this->notifyUser(
            $user,
            'Signed In to Maarifazone',
            'Your session started at ' . now()->format('M d, Y · h:i A') . '. Review recent activity and learning updates.',
            'info',
            $dashboardUrl,
            'Signed In to Maarifazone',
            720
        );

        // Remind student if unresolved support ticket updates exist
        if (!$user->isAdmin() && !$user->isInstructor() && Schema::hasTable('support_tickets')) {
            try {
                $openTickets = \App\Models\SupportTicket::where('user_id', $user->id)
                    ->whereIn('status', ['open', 'in_progress'])
                    ->count();

                if ($openTickets > 0 && Route::has('student.tickets.index')) {
                    $this->notifyUser(
                        $user,
                        'Active Support Tickets',
                        "You have {$openTickets} active support inquiry ticket(s) with Central Administration.",
                        'info',
                        route('student.tickets.index'),
                        'Active Support Tickets',
                        720
                    );
                }
            } catch (\Throwable) {
            }
        }
    }

    /**
     * Resolve landing dashboard URL based on user role.
     */
    private function dashboardRouteFor(User $user): ?string
    {
        if ($user->isAdmin() && Route::has('admin.dashboard')) {
            return route('admin.dashboard');
        }

        if ($user->isInstructor() && Route::has('instructor.dashboard')) {
            return route('instructor.dashboard');
        }

        return Route::has('student.dashboard') ? route('student.dashboard') : null;
    }

    /**
     * Check whether notifications storage table exists safely.
     */
    private function notificationsTableReady(): bool
    {
        try {
            return Schema::hasTable('notifications');
        } catch (\Throwable) {
            return false;
        }
    }
}
