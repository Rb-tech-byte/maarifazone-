@php
    $adminUser = auth()->user();
    $can = fn (string $perm): bool => $adminUser ? $adminUser->hasAdminPermission($perm) : false;
    $canAny = function (array $perms) use ($can): bool {
        foreach ($perms as $p) {
            if ($can($p)) {
                return true;
            }
        }
        return false;
    };

    $inCoursesGroup = request()->routeIs('admin.courses.*')
        || request()->routeIs('admin.bundles.*')
        || request()->routeIs('admin.course-categories.*')
        || request()->routeIs('admin.course-languages.*')
        || request()->routeIs('admin.reviews.*');

    $inUsersGroup = request()->routeIs('admin.instructors.*')
        || request()->routeIs('admin.students.*')
        || request()->routeIs('admin.enrollments.*');

    $inFinanceGroup = request()->routeIs('admin.payments.*')
        || request()->routeIs('admin.withdrawals.*')
        || request()->routeIs('admin.coupons.*');

    $inReportsGroup = request()->routeIs('admin.reports.*');

    $inSystemSettings = request()->routeIs([
        'admin.settings.*',
        'admin.staff.*',
        'admin.roles.*',
        'admin.frontend.*',
        'admin.custom-pages.*',
        'admin.banners.*',
        'admin.account-deletions.*',
        'admin.languages.*',
        'admin.notifications.*',
        'admin.audit-logs.*',
        'admin.backups.*',
        'admin.updater.*',
        'admin.help.*',
    ]);

    $systemSettingsRoute = match (true) {
        $can('manage-settings') => route('admin.settings.index'),
        $canAny(['manage-staff', 'manage-roles']) => route('admin.staff.index'),
        $can('manage-frontend') => route('admin.frontend.home.edit'),
        $can('view-audit-logs') => route('admin.audit-logs.index'),
        $can('manage-backups') => route('admin.backups.index'),
        $can('manage-notifications') => route('admin.notifications.index'),
        default => route('admin.help.index'),
    };
@endphp

<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            @php($siteName = site_name())
            <a href="{{ route('admin.dashboard') }}">
                <img src="{{ branding_asset(['dark_logo_path', 'primary_logo_path', 'logos.primary'], 'assets/front/img/logo/maarifazone-logo.png') }}"
                    alt="{{ $siteName }} logo" class="img-fluid mt-3" style="max-height: 40px;">
            </a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="{{ route('admin.dashboard') }}">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($siteName, 0, 2)) }}</a>
        </div>

        <ul class="sidebar-menu">
            @if ($adminUser)
                <li class="px-3 py-2 hide-sidebar-mini">
                    <div class="rounded p-2 bg-light border small">
                        <div class="font-weight-bold text-primary">{{ $adminUser->primaryRoleLabel() }}</div>
                        <div class="text-muted" style="font-size: 11px;">
                            <i class="fas fa-user-shield mr-1"></i>Maarifazone Administration
                        </div>
                    </div>
                </li>
            @endif

            <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}" class="nav-link"><i class="fas fa-home"></i><span>Dashboard</span></a>
            </li>

            @if ($canAny(['manage-courses', 'manage-bundles']))
                <li class="dropdown {{ $inCoursesGroup ? 'active' : '' }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                        <i class="fas fa-book"></i> <span>Courses &amp; Learning</span>
                    </a>
                    <ul class="dropdown-menu">
                        @if ($can('manage-courses'))
                            <li class="{{ request()->routeIs('admin.courses.index') && ! request('status') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.courses.index') }}">All Courses</a></li>
                            <li><a class="nav-link" href="{{ route('admin.courses.index', ['status' => 'pending']) }}">Pending Courses</a></li>
                            <li><a class="nav-link" href="{{ route('admin.courses.index', ['status' => 'published']) }}">Published Courses</a></li>
                            <li><a class="nav-link" href="{{ route('admin.courses.index', ['status' => 'draft']) }}">Draft Courses</a></li>
                            <li><a class="nav-link" href="{{ route('admin.courses.index', ['status' => 'archived']) }}">Archived Courses</a></li>
                            <li class="{{ request()->routeIs('admin.course-categories.*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.course-categories.index') }}">Categories</a></li>
                            <li class="{{ request()->routeIs('admin.course-languages.*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.course-languages.index') }}">Languages</a></li>
                        @endif
                        @if ($can('manage-bundles'))
                            <li class="{{ request()->routeIs('admin.bundles.*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.bundles.index') }}">Course Bundles</a></li>
                        @endif
                        @if ($can('manage-courses'))
                            <li class="{{ request()->routeIs('admin.reviews.index') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.reviews.index') }}">All Reviews</a></li>
                            <li class="{{ request()->routeIs('admin.reviews.pending') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.reviews.pending') }}">
                                    Pending Reviews
                                    @php($pendingReviewsCount = \App\Models\Review::where('is_approved', false)->count())
                                    @if ($pendingReviewsCount > 0)
                                        <span class="badge badge-warning ml-1">{{ $pendingReviewsCount }}</span>
                                    @endif
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if ($canAny(['approve-instructor', 'reject-instructor', 'instructor:create', 'manage-courses', 'view-users', 'student:create', 'student:update']))
                <li class="dropdown {{ $inUsersGroup ? 'active' : '' }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                        <i class="fas fa-users"></i> <span>Scholars &amp; Instructors</span>
                    </a>
                    <ul class="dropdown-menu">
                        @if ($canAny(['view-users', 'student:create', 'student:update']))
                            <li class="{{ request()->routeIs('admin.students.*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.students.index') }}">Scholars (Students)</a></li>
                        @endif
                        @if ($canAny(['approve-instructor', 'reject-instructor', 'instructor:create', 'manage-courses']))
                            <li class="{{ request()->routeIs('admin.instructors.index') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.instructors.index') }}">All Instructors</a></li>
                            <li class="{{ request()->routeIs('admin.instructors.pending') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.instructors.pending') }}">Pending Instructors</a></li>
                        @endif
                        @if ($can('manage-courses'))
                            <li class="{{ request()->routeIs('admin.enrollments.index') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.enrollments.index') }}">All Enrollments</a></li>
                            <li class="{{ request()->routeIs('admin.enrollments.pending') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.enrollments.pending') }}">Pending Enrollments</a></li>
                            <li class="{{ request()->routeIs('admin.enrollments.completed') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.enrollments.completed') }}">Completed Enrollments</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            @if ($canAny(['manage-payments', 'manage-withdrawals', 'manage-coupons']))
                <li class="dropdown {{ $inFinanceGroup ? 'active' : '' }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                        <i class="fas fa-wallet"></i> <span>Finance &amp; Earnings</span>
                    </a>
                    <ul class="dropdown-menu">
                        @if ($can('manage-payments'))
                            <li class="{{ request()->routeIs('admin.payments.index') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.payments.index') }}">All Payments</a></li>
                            <li class="{{ request()->routeIs('admin.payments.pending') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.payments.pending') }}">Pending Payments</a></li>
                            <li class="{{ request()->routeIs('admin.payments.offline-pending') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.payments.offline-pending') }}">
                                    Offline Pending
                                    @php($offlinePendingCount = \App\Models\Payment::where('payment_method', 'offline')->where('status', 'pending')->count())
                                    @if ($offlinePendingCount > 0)
                                        <span class="badge badge-warning ml-1">{{ $offlinePendingCount }}</span>
                                    @endif
                                </a>
                            </li>
                        @endif
                        @if ($can('manage-withdrawals'))
                            <li class="{{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.withdrawals.index') }}">Instructor Payouts</a></li>
                        @endif
                        @if ($can('manage-coupons'))
                            <li class="{{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.coupons.index') }}">Coupons</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            @if ($can('manage-tickets'))
                <li class="{{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.tickets.index') }}">
                        <i class="fas fa-headset"></i> <span>Support Tickets</span>
                        @php($openTicketCount = \Illuminate\Support\Facades\Schema::hasTable('support_tickets') ? \App\Models\SupportTicket::whereIn('status', ['open', 'in_progress'])->count() : 0)
                        @if ($openTicketCount > 0)
                            <span class="badge badge-danger ml-1">{{ $openTicketCount }}</span>
                        @endif
                    </a>
                </li>
            @endif

            @if ($can('view-reports'))
                <li class="dropdown {{ $inReportsGroup ? 'active' : '' }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                        <i class="fas fa-chart-pie"></i> <span>Reports &amp; Analytics</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="{{ request()->routeIs('admin.reports.index') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.reports.index') }}">Executive Overview</a></li>
                    </ul>
                </li>
            @endif

            <li class="{{ $inSystemSettings ? 'active' : '' }}">
                <a href="{{ $systemSettingsRoute }}" class="nav-link">
                    <i class="fas fa-cogs"></i> <span>System Settings</span>
                </a>
            </li>
        </ul>
    </aside>
</div>
