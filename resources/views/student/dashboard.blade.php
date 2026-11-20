@extends('layouts.student')

@section('content')
    <!-- Scholar Hero Banner -->
    <div class="out-hero-banner out-hero-student">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="out-hero-badge">
                    <i class="fa-solid fa-graduation-cap"></i> Maarifa Scholar Portal
                </span>
                <h1 class="out-hero-title">
                    {{ __('student.welcome_back', ['name' => $firstName]) }}
                </h1>
                <p class="out-hero-subtitle">
                    Expand your skills, gain practical maarifa, and learn at your own pace.
                </p>
            </div>
            <div class="out-hero-actions">
                <a href="{{ route('courses') }}" class="out-pill-btn out-pill-yellow">
                    <i class="fa-solid fa-compass"></i> Explore Courses
                </a>
                <a href="{{ route('student.my-courses') }}" class="out-pill-btn out-pill-outline">
                    <i class="fa-solid fa-book-bookmark"></i> My Courses
                </a>
                <a href="{{ route('student.tickets.index') }}" class="out-pill-btn out-pill-outline">
                    <i class="fa-solid fa-headset"></i> Support Desk
                </a>
            </div>
        </div>
    </div>

    <!-- Metric Stat Cards Grid -->
    <div class="out-stat-grid">
        <div class="out-stat-card">
            <div class="out-stat-icon-wrap out-stat-icon-blue">
                <i class="fa-solid fa-book-open"></i>
            </div>
            <div class="out-stat-content">
                <div class="out-stat-value" id="kpiEnrolled">{{ $stats['total_enrollments'] }}</div>
                <p class="out-stat-label">{{ __('student.enrolled_courses') }}</p>
            </div>
        </div>

        <div class="out-stat-card">
            <div class="out-stat-icon-wrap out-stat-icon-emerald">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="out-stat-content">
                <div class="out-stat-value" id="kpiCompleted">{{ $stats['completed_courses'] }}</div>
                <p class="out-stat-label">{{ __('student.completed') }}</p>
            </div>
        </div>

        <div class="out-stat-card">
            <div class="out-stat-icon-wrap out-stat-icon-amber">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div class="out-stat-content">
                <div class="out-stat-value" id="kpiPending">{{ $stats['pending_enrollments'] }}</div>
                <p class="out-stat-label">In Progress / Pending</p>
            </div>
        </div>

        <div class="out-stat-card">
            <div class="out-stat-icon-wrap out-stat-icon-sky">
                <i class="fa-solid fa-circle-play"></i>
            </div>
            <div class="out-stat-content">
                <div class="out-stat-value" id="kpiLessons">{{ $stats['total_lessons'] }}</div>
                <p class="out-stat-label">Total Lessons Available</p>
            </div>
        </div>
    </div>

    <!-- Real-Time Communication Summary Bar -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <a href="{{ route('student.notifications.index') }}" class="out-card text-decoration-none d-flex align-items-center justify-content-between p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(249, 159, 30, 0.1); color: #f99f1e;">
                        <i class="fa-solid fa-bell"></i>
                    </span>
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 13.5px;">Unread Alerts</div>
                        <div class="text-muted" style="font-size: 12px;">Course updates &amp; announcements</div>
                    </div>
                </div>
                <span id="summaryUnreadNotifs" class="badge rounded-pill {{ ($stats['unread_notifications'] ?? 0) > 0 ? 'bg-danger' : 'bg-light text-muted border' }} px-3 py-2">
                    {{ $stats['unread_notifications'] ?? 0 }}
                </span>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('student.tickets.index') }}" class="out-card text-decoration-none d-flex align-items-center justify-content-between p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(249, 159, 30, 0.12); color: #c97406;">
                        <i class="fa-solid fa-headset"></i>
                    </span>
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 13.5px;">Admin Support Desk</div>
                        <div class="text-muted" style="font-size: 12px;">Assistance &amp; help tickets</div>
                    </div>
                </div>
                <span id="summaryOpenTickets" class="badge rounded-pill {{ ($stats['open_tickets'] ?? 0) > 0 ? 'bg-primary' : 'bg-light text-muted border' }} px-3 py-2">
                    {{ $stats['open_tickets'] ?? 0 }} Open
                </span>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('student.payments') }}" class="out-card text-decoration-none d-flex align-items-center justify-content-between p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(34, 197, 94, 0.12); color: #16a34a;">
                        <i class="fa-solid fa-receipt"></i>
                    </span>
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 13.5px;">Payment History</div>
                        <div class="text-muted" style="font-size: 12px;">Transactions &amp; receipts</div>
                    </div>
                </div>
                <span class="text-muted" style="font-size: 13px;">
                    View <i class="fa-solid fa-chevron-right ms-1"></i>
                </span>
            </a>
        </div>
    </div>

    <!-- Upcoming Live Class Banner (If Scheduled) -->
    @if ($upcomingLiveClass)
        <div class="out-card mb-4 overflow-hidden border-0 shadow-sm"
             style="background: {{ $upcomingLiveClass->is_currently_live ? 'linear-gradient(135deg, #b91c1c 0%, #ef4444 100%)' : 'linear-gradient(135deg, #0f172a 0%, #f99f1e 100%)' }}; color: #ffffff;">
            <div class="p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        @if ($upcomingLiveClass->is_currently_live)
                            <span class="badge bg-danger text-uppercase fw-bold px-3 py-1">
                                <i class="fa-solid fa-circle me-1" style="font-size: 8px;"></i> Live Now
                            </span>
                        @else
                            <span class="badge bg-white text-dark text-uppercase fw-bold px-3 py-1">
                                <i class="fa-solid fa-clock me-1"></i> Upcoming Session
                            </span>
                        @endif
                        <span class="text-white-50" style="font-size: 13px;">
                            {{ $upcomingLiveClass->course->title ?? 'Course' }}
                        </span>
                    </div>
                    <h3 class="text-white fw-bold mb-1" style="font-size: 20px;">
                        {{ $upcomingLiveClass->title }}
                    </h3>
                    <p class="text-white-50 mb-0" style="font-size: 13px;">
                        <i class="fa-regular fa-calendar me-1"></i>
                        {{ optional($upcomingLiveClass->start_time)->format('M d, Y · h:i A') }}
                        @if ($upcomingLiveClass->instructor)
                            <span class="ms-3">
                                <i class="fa-solid fa-chalkboard-user me-1"></i> Instructor: {{ $upcomingLiveClass->instructor->name }}
                            </span>
                        @endif
                    </p>
                </div>
                <a href="{{ route('student.live-classes.index') }}"
                   class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark shadow-sm">
                    <i class="fa-solid fa-video me-1"></i>
                    {{ $upcomingLiveClass->is_currently_live ? 'Join Class Now' : 'Session Details' }}
                </a>
            </div>
        </div>
    @endif

    <!-- Main Content Row -->
    <div class="row g-4">
        <!-- Left Column: Courses, Discussions, Notifications -->
        <div class="col-xl-8 col-lg-7">
            <!-- Enrolled Courses -->
            <div class="out-card mb-4">
                <div class="out-card-header">
                    <h3 class="out-card-title">
                        <i class="fa-solid fa-book-bookmark text-primary"></i> My Active Online Courses
                    </h3>
                    <a href="{{ route('student.my-courses') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1" style="font-size: 12px; font-weight: 600;">
                        All Courses
                    </a>
                </div>
                <div class="out-card-body p-3">
                    @forelse($recentEnrollments as $enrollment)
                        @php
                            $c = $enrollment->course;
                            $pct = (int) $enrollment->progress_percentage;
                        @endphp
                        @if ($c)
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 p-3 rounded-3 border mb-3 bg-white hover-shadow">
                                <div class="d-flex align-items-center gap-3" style="min-width: 0;">
                                    <img src="{{ $c->featured_image ? asset('storage/' . $c->featured_image) : asset('assets/front/img/inner/courses-details/courses-details.jpg') }}"
                                         alt="{{ $c->title }}"
                                         class="rounded-3 object-fit-cover flex-shrink-0"
                                         style="width: 72px; height: 52px;">
                                    <div style="min-width: 0;">
                                        <h5 class="fw-bold mb-1 text-truncate" style="font-size: 14.5px;">
                                            <a href="{{ route('student.courses.show', $c->id) }}" class="text-dark text-decoration-none">
                                                {{ $c->title }}
                                            </a>
                                        </h5>
                                        <div class="text-muted d-flex align-items-center gap-3 flex-wrap" style="font-size: 12px;">
                                            @if ($c->instructor)
                                                <span><i class="fa-regular fa-user me-1"></i>{{ $c->instructor->name }}</span>
                                            @endif
                                            <span><i class="fa-regular fa-circle-play me-1"></i>{{ $enrollment->lessons_count }} Lessons</span>
                                            <span class="badge {{ $enrollment->status === 'completed' ? 'bg-success' : 'bg-warning text-dark' }} rounded-pill">
                                                {{ ucfirst($enrollment->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-3 flex-shrink-0">
                                    <div class="text-end d-none d-sm-block" style="min-width: 100px;">
                                        <div class="fw-bold text-dark" style="font-size: 12.5px;">{{ $pct }}%</div>
                                        <div class="progress" style="height: 6px; width: 100px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pct }}%" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <a href="{{ route('student.courses.show', $c->id) }}" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold" style="font-size: 12.5px;">
                                        <i class="fa-solid fa-play me-1"></i> Learn
                                    </a>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="out-empty-state text-center py-4">
                            <div class="out-empty-state-icon fs-1 text-muted mb-2">
                                <i class="fa-solid fa-book-open"></i>
                            </div>
                            <div class="out-empty-state-title fw-bold mb-1">No Enrolled Courses Yet</div>
                            <p class="out-empty-state-text text-muted mb-3" style="font-size: 13px;">Browse the Maarifazone catalog to start learning today.</p>
                            <a href="{{ route('courses') }}" class="btn btn-primary btn-sm rounded-pill px-3 py-2 fw-semibold">
                                <i class="fa-solid fa-compass me-1"></i> Browse Course Catalog
                            </a>
                        </div>
                    @endforelse
                </div>
                @include('partials.portal-card-pagination', ['paginator' => $recentEnrollments ?? null])
            </div>

            <!-- Internal Notifications & Course Updates -->
            <div class="out-card mb-4">
                <div class="out-card-header">
                    <h3 class="out-card-title">
                        <i class="fa-solid fa-bell text-warning"></i> Recent Notifications &amp; Updates
                        <span id="dashboardNotifBadge" class="badge rounded-pill ms-2 {{ ($stats['unread_notifications'] ?? 0) > 0 ? 'bg-danger' : 'bg-secondary' }}" style="font-size: 11px;">
                            {{ $stats['unread_notifications'] ?? 0 }} Unread
                        </span>
                    </h3>
                    <div class="d-flex align-items-center gap-2">
                        @if(($stats['unread_notifications'] ?? 0) > 0)
                            <a href="{{ route('student.notifications.readAll') }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1" style="font-size: 11.5px; font-weight: 600;">
                                Mark All Read
                            </a>
                        @endif
                        <a href="{{ route('student.notifications.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1" style="font-size: 12px; font-weight: 600;">
                            View All
                        </a>
                    </div>
                </div>
                <div class="out-card-body p-3" id="dashboardNotifFeed">
                    @forelse($recentNotifications ?? [] as $notif)
                        @php
                            $ndata = is_array($notif->data) ? $notif->data : [];
                            $isUnread = $notif->read_at === null;
                        @endphp
                        <a href="{{ route('student.notifications.read', $notif->id) }}"
                           class="d-flex align-items-start justify-content-between gap-3 p-2 rounded-3 text-decoration-none mb-2 border {{ $isUnread ? 'bg-light border-primary-subtle' : 'bg-white' }}">
                            <div class="d-flex align-items-start gap-2" style="min-width: 0;">
                                <i class="fa-solid fa-circle-dot mt-1 {{ $isUnread ? 'text-primary' : 'text-muted' }}" style="font-size: 11px;"></i>
                                <div style="min-width: 0;">
                                    <div class="fw-semibold text-dark text-truncate" style="font-size: 13px;">{{ $ndata['title'] ?? 'Notification' }}</div>
                                    <div class="text-muted text-truncate" style="font-size: 12px;">{{ $ndata['message'] ?? '' }}</div>
                                </div>
                            </div>
                            <span class="text-muted flex-shrink-0" style="font-size: 11px;">{{ $notif->created_at?->diffForHumans(null, true) }}</span>
                        </a>
                    @empty
                        <div class="text-center text-muted py-3" style="font-size: 13px;">
                            No recent notifications. Learning activity updates will appear here.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Scholar Community & Discussions Card -->
            <div class="out-card">
                <div class="out-card-header">
                    <h3 class="out-card-title">
                        <i class="fa-solid fa-users text-primary"></i> Scholar Community &amp; Forum
                    </h3>
                    <a href="{{ route('student.community.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1" style="font-size: 12px; font-weight: 600;">
                        Open Community
                    </a>
                </div>
                <div class="out-card-body p-3">
                    <div class="p-3 rounded-3" style="background: rgba(249, 159, 30, 0.04); border: 1px solid rgba(249, 159, 30, 0.1);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="fs-3 text-primary"><i class="fa-solid fa-comments"></i></div>
                            <div>
                                <h6 class="fw-bold mb-1" style="color: #f99f1e; font-size: 14px;">Connect with Fellow Maarifazone Scholars</h6>
                                <p class="text-muted mb-0" style="font-size: 12.5px;">
                                    Join course discussions, share knowledge, ask questions, and collaborate with learners across all subjects.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Messages, Shortcuts & Support -->
        <div class="col-xl-4 col-lg-5">
            <!-- Platform Support Desk -->
            <div class="out-card mb-4">
                <div class="out-card-header">
                    <h3 class="out-card-title">
                        <i class="fa-solid fa-headset text-primary"></i> Support Tickets
                    </h3>
                    <a href="{{ route('student.tickets.create') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1" style="font-size: 12px; font-weight: 600;">
                        New Ticket
                    </a>
                </div>
                <div class="out-card-body p-3">
                    @forelse($recentTickets ?? [] as $ticket)
                        <a href="{{ route('student.tickets.show', $ticket) }}"
                           class="d-flex align-items-center justify-content-between gap-2 p-2 rounded-3 text-decoration-none border mb-2 {{ $ticket->status === 'open' ? 'bg-light border-primary-subtle' : 'bg-white' }}">
                            <div style="min-width: 0;">
                                <div class="fw-semibold text-dark text-truncate" style="font-size: 13px;">{{ $ticket->subject }}</div>
                                <div class="text-muted text-truncate" style="font-size: 11.5px;">
                                    #{{ $ticket->ticket_number }} &middot; {{ ucfirst($ticket->department) }} &middot; {{ $ticket->messages_count }} {{ \Illuminate\Support\Str::plural('reply', $ticket->messages_count) }}
                                </div>
                            </div>
                            <span class="badge {{ $ticket->status === 'closed' ? 'bg-secondary' : ($ticket->status === 'in_progress' ? 'bg-info' : 'bg-warning text-dark') }} rounded-pill" style="font-size: 11px;">
                                {{ ucfirst($ticket->status) }}
                            </span>
                        </a>
                    @empty
                        <div class="text-center text-muted py-2" style="font-size: 12.5px;">
                            No support tickets filed yet.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Quick Study Shortcuts -->
            <div class="out-card mb-4">
                <div class="out-card-header">
                    <h3 class="out-card-title">
                        <i class="fa-solid fa-bolt text-warning"></i> Quick Shortcuts
                    </h3>
                </div>
                <div class="out-card-body p-3">
                    <a href="{{ route('courses') }}" class="out-action-tile">
                        <i class="fa-solid fa-compass"></i>
                        <span>Browse Course Catalog</span>
                    </a>
                    <a href="{{ route('student.live-classes.index') }}" class="out-action-tile">
                        <i class="fa-solid fa-video"></i>
                        <span>Live Classes Schedule</span>
                    </a>
                    <a href="{{ route('student.community.index') }}" class="out-action-tile">
                        <i class="fa-solid fa-comments"></i>
                        <span>Learner Community Forums</span>
                    </a>
                    <a href="{{ route('student.ai-chat.index') }}" class="out-action-tile">
                        <i class="fa-solid fa-robot"></i>
                        <span>AI Study Assistant</span>
                    </a>
                    <a href="{{ route('student.payments') }}" class="out-action-tile">
                        <i class="fa-solid fa-receipt"></i>
                        <span>Payment History &amp; Receipts</span>
                    </a>
                </div>
            </div>

            <!-- Student Support -->
            <div class="out-card">
                <div class="out-card-header">
                    <h3 class="out-card-title">
                        <i class="fa-solid fa-circle-question text-info"></i> Need Assistance?
                    </h3>
                </div>
                <div class="out-card-body p-3">
                    <div class="p-3 rounded-3 mb-3" style="background: rgba(249, 159, 30, 0.06); border: 1px solid rgba(249, 159, 30, 0.15);">
                        <h6 class="fw-bold mb-1" style="color: #c97406; font-size: 13.5px;">Scholar Support Desk</h6>
                        <p class="text-muted mb-0" style="font-size: 12.5px;">
                            For course inquiries, video playback assistance, or platform technical questions.
                        </p>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <a href="{{ route('student.tickets.create') }}" class="btn btn-outline-primary btn-sm rounded-pill fw-semibold py-2">
                            <i class="fa-solid fa-headset me-1"></i> Submit Support Ticket
                        </a>
                        <a href="mailto:info@maarifazone.com" class="btn btn-light btn-sm rounded-pill text-muted border py-2" style="font-size: 12.5px;">
                            <i class="fa-solid fa-envelope me-1"></i> info@maarifazone.com
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $dashboardLiveUrl = route('student.dashboard.live');
    @endphp
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const liveUrl = @json($dashboardLiveUrl);

    function esc(s) {
        return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function refreshDashboard() {
        fetch(liveUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (!data || !data.stats) return;

                const s = data.stats;
                const setText = function (id, val) {
                    const el = document.getElementById(id);
                    if (el) el.textContent = String(val ?? 0);
                };
                setText('kpiEnrolled', s.total_enrollments);
                setText('kpiCompleted', s.completed_courses);
                setText('kpiPending', s.pending_enrollments);
                setText('kpiLessons', s.total_lessons);
                setText('summaryUnreadNotifs', s.unread_notifications);
                setText('summaryOpenTickets', (s.open_tickets ?? 0) + ' Open');

                const notifBadge = document.getElementById('dashboardNotifBadge');
                if (notifBadge) {
                    notifBadge.textContent = (s.unread_notifications || 0) + ' Unread';
                    notifBadge.className = 'badge rounded-pill ms-2 ' + ((s.unread_notifications || 0) > 0 ? 'bg-danger' : 'bg-secondary');
                }

                const feedEl = document.getElementById('dashboardNotifFeed');
                if (feedEl && Array.isArray(data.notifications)) {
                    if (data.notifications.length === 0) {
                        feedEl.innerHTML = '<div class="text-center text-muted py-3" style="font-size: 13px;">No recent notifications.</div>';
                    } else {
                        feedEl.innerHTML = data.notifications.map(function (n) {
                            return '<a href="' + esc(n.read_url || '#') + '" class="d-flex align-items-start justify-content-between gap-3 p-2 rounded-3 text-decoration-none mb-2 border ' + (n.is_unread ? 'bg-light border-primary-subtle' : 'bg-white') + '">'
                                + '<div class="d-flex align-items-start gap-2" style="min-width: 0;">'
                                + '<i class="fa-solid fa-circle-dot mt-1 ' + (n.is_unread ? 'text-primary' : 'text-muted') + '" style="font-size: 11px;"></i>'
                                + '<div style="min-width: 0;"><div class="fw-semibold text-dark text-truncate" style="font-size: 13px;">' + esc(n.title) + '</div>'
                                + '<div class="text-muted text-truncate" style="font-size: 12px;">' + esc(n.message) + '</div></div></div>'
                                + '<span class="text-muted flex-shrink-0" style="font-size: 11px;">' + esc(n.time_ago) + '</span></a>';
                        }).join('');
                    }
                }
            })
            .catch(function () {});
    }

    setInterval(refreshDashboard, 15000);
});
</script>
@endpush
