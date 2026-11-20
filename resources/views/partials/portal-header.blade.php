@php
    $portalType = $portalType ?? (auth()->user()?->hasRole('instructor') ? 'instructor' : 'student');
    $user = auth()->user();
    $homeRoute = $portalType === 'instructor' ? route('instructor.dashboard') : route('student.dashboard');
    $notificationsRoute = $portalType === 'instructor' && \Illuminate\Support\Facades\Route::has('instructor.notifications.index')
        ? route('instructor.notifications.index')
        : (\Illuminate\Support\Facades\Route::has('student.notifications.index') ? route('student.notifications.index') : '#');
    $notificationsFeedRoute = \Illuminate\Support\Facades\Route::has($portalType . '.notifications.feed')
        ? route($portalType . '.notifications.feed')
        : '';
    $notificationsReadAllRoute = \Illuminate\Support\Facades\Route::has($portalType . '.notifications.readAll')
        ? route($portalType . '.notifications.readAll')
        : '#';
    $readRouteName = $portalType . '.notifications.read';
    $settingsRoute = $portalType === 'instructor' ? route('instructor.settings') : route('student.settings');
    $ticketsRoute = $portalType === 'student' && \Illuminate\Support\Facades\Route::has('student.tickets.index')
        ? route('student.tickets.index')
        : '#';
    $userPhoto = $user?->profile_photo ? asset('storage/' . $user->profile_photo) : asset('assets/front/img/home-1/courses/client-01.png');
    $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
    $recentPortalNotifications = $user ? $user->notifications()->latest()->limit(8)->get() : collect();
@endphp

<header class="maarifazone-portal-header"
        data-feed-url="{{ $notificationsFeedRoute }}">
    <div class="maarifazone-portal-header-left">
        <!-- Sidebar Drawer Toggle (Mobile & Desktop) -->
        <button type="button" class="maarifazone-portal-toggle-btn" id="maarifazonePortalSidebarToggle" aria-label="Toggle Navigation">
            <i class="fa-solid fa-bars"></i>
        </button>

        <!-- Maarifazone Brand Logo -->
        <a href="{{ $homeRoute }}" class="maarifazone-portal-brand">
            <img src="{{ branding_asset(['primary_logo_path', 'logos.primary'], 'assets/front/img/logo/logo.png') }}"
                 alt="{{ site_name() }} Logo"
                 onerror="this.src='{{ asset('assets/front/img/logo/maarifazone-logo.png') }}'">
        </a>

        <!-- Portal Role Pill Badge -->
        @if($portalType === 'instructor')
            <span class="maarifazone-portal-role-badge instructor d-none d-sm-inline-flex">
                <i class="fa-solid fa-chalkboard-user"></i> Instructor Studio
            </span>
        @else
            <span class="maarifazone-portal-role-badge student d-none d-sm-inline-flex">
                <i class="fa-solid fa-graduation-cap"></i> Student Campus
            </span>
        @endif
    </div>

    <div class="maarifazone-portal-header-right">
        <!-- Visit Public Website -->
        <a href="{{ route('home') }}" target="_blank" class="maarifazone-portal-visit-btn" title="View Public Website">
            <i class="fa-solid fa-globe"></i>
            <span class="d-none d-md-inline">Visit Website</span>
        </a>

        <!-- Interactive Notifications Bell Dropdown -->
        @if($notificationsRoute !== '#')
            <div class="dropdown maarifazone-portal-bell-dropdown" id="portalBellDropdown">
                <button type="button"
                        class="maarifazone-portal-icon-btn"
                        id="portalBellToggle"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="outside"
                        aria-expanded="false"
                        title="Notifications">
                    <i class="fa-solid fa-bell"></i>
                    <span id="portalBellBadge"
                          class="maarifazone-portal-icon-badge maarifazone-portal-count-badge {{ $unreadCount > 0 ? '' : 'd-none' }}">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                </button>

                <div class="dropdown-menu dropdown-menu-end maarifazone-portal-bell-menu shadow" aria-labelledby="portalBellToggle">
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom bg-light">
                        <span class="fw-bold text-dark" style="font-size: 13px;">
                            <i class="fa-solid fa-bell text-primary me-1"></i> Notifications
                        </span>
                        <a href="{{ $notificationsReadAllRoute }}"
                           id="portalBellMarkAll"
                           data-read-all-url="{{ $notificationsReadAllRoute }}"
                           class="text-decoration-none fw-semibold {{ $unreadCount > 0 ? '' : 'd-none' }}"
                           style="font-size: 11.5px; color: #f99f1e;">
                            Mark all read
                        </a>
                    </div>

                    <div id="portalBellList" class="maarifazone-portal-bell-list">
                        @forelse($recentPortalNotifications as $notification)
                            @php
                                $data = is_array($notification->data) ? $notification->data : [];
                                $isUnread = $notification->read_at === null;
                                $notifType = $data['type'] ?? 'info';
                                $iconColor = match ($notifType) {
                                    'success' => '#059669',
                                    'warning' => '#d97706',
                                    'danger'  => '#dc2626',
                                    default   => '#f99f1e',
                                };
                            @endphp
                            <a href="{{ route($readRouteName, $notification->id) }}"
                               class="dropdown-item maarifazone-portal-bell-item {{ $isUnread ? 'unread' : '' }}">
                                <div class="d-flex align-items-start gap-2">
                                    <span class="mt-1 flex-shrink-0" style="color: {{ $iconColor }}; font-size: 13px;">
                                        <i class="fa-solid fa-circle-dot"></i>
                                    </span>
                                    <div class="flex-grow-1" style="min-width: 0;">
                                        <div class="d-flex justify-content-between align-items-center gap-2">
                                            <span class="fw-semibold text-dark text-truncate" style="font-size: 12.5px;">{{ $data['title'] ?? 'Notification' }}</span>
                                            <span class="text-muted flex-shrink-0" style="font-size: 10px;">{{ $notification->created_at?->diffForHumans(null, true) }}</span>
                                        </div>
                                        <p class="text-muted mb-0 text-truncate" style="font-size: 11.5px;">{{ $data['message'] ?? '' }}</p>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="text-center py-4 px-3 text-muted" style="font-size: 12.5px;">
                                <i class="fa-regular fa-bell-slash d-block mb-1 fs-5"></i>
                                No notifications yet.
                            </div>
                        @endforelse
                    </div>

                    <div class="border-top px-3 py-2 text-center bg-light">
                        <a href="{{ $notificationsRoute }}" class="text-decoration-none fw-semibold" style="font-size: 12px; color: #f99f1e;">
                            View All Notifications <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- User Profile Dropdown Pill -->
        @if($user)
            <div class="dropdown maarifazone-portal-user-dropdown">
                <a href="#" class="maarifazone-portal-user-trigger" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ $userPhoto }}" alt="{{ $user->name }}" class="maarifazone-portal-user-avatar">
                    <div class="maarifazone-portal-user-info d-none d-md-block text-start">
                        <span class="maarifazone-portal-user-name">{{ $user->name }}</span>
                        <span class="maarifazone-portal-user-role">
                            <i class="fa-solid fa-circle-check text-success" style="font-size: 10px;"></i>
                            {{ $portalType === 'instructor' ? 'Verified Instructor' : 'Enrolled Student' }}
                        </span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-muted ms-1" style="font-size: 10px;"></i>
                </a>

                <ul class="dropdown-menu dropdown-menu-end maarifazone-portal-menu-dropdown shadow-sm">
                    <li class="px-3 py-2 border-bottom mb-1">
                        <div class="text-uppercase text-muted fw-semibold mb-1" style="font-size: 10px; letter-spacing: 0.05em;">Account</div>
                        <div class="fw-bold text-dark text-truncate" style="font-size: 13.5px;">{{ $user->name }}</div>
                        <div class="text-muted text-truncate" style="font-size: 11.5px;">{{ $user->email }}</div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ $homeRoute }}">
                            <i class="fa-solid fa-gauge text-primary"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ $settingsRoute }}">
                            <i class="fa-solid fa-gear text-secondary"></i> Profile & Settings
                        </a>
                    </li>
                    @if($ticketsRoute !== '#')
                        <li>
                            <a class="dropdown-item" href="{{ $ticketsRoute }}">
                                <i class="fa-solid fa-headset text-info"></i> Admin Support Desk
                            </a>
                        </li>
                    @endif
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="{{ route('logout') }}">
                            <i class="fa-solid fa-right-from-bracket"></i> Sign Out
                        </a>
                    </li>
                </ul>
            </div>
        @endif
    </div>
</header>
