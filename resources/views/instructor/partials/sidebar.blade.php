@php
    // Reusable Component Safety: only render when invoked from the portal layout shell
    if (empty($fromLayout)) {
        return;
    }

    $user = $user ?? auth()->user();

    $inCoursesGroup = request()->routeIs([
        'instructor.courses*',
        'instructor.bundles.*',
        'instructor.coupons.*',
    ]);

    $inTeachingGroup = request()->routeIs([
        'instructor.live-classes.*',
        'instructor.students*',
    ]);

    $inCommGroup = request()->routeIs([
        'instructor.discussions.*',
    ]);

    $inFinanceGroup = request()->routeIs([
        'instructor.analytics',
        'instructor.reviews',
        'instructor.earnings',
        'instructor.withdrawals',
    ]);
@endphp

<aside class="maarifazone-portal-sidebar" id="maarifazonePortalSidebar">
    <ul class="out-sidebar-nav">
        <li class="out-nav-item">
            <a href="{{ route('instructor.dashboard') }}" class="out-nav-link {{ request()->routeIs('instructor.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge"></i>
                <span>Dashboard</span>
            </a>
        </li>

        {{-- Assigned Courses --}}
        <li class="out-nav-item">
            <button type="button"
                class="out-nav-link out-nav-toggle {{ $inCoursesGroup ? 'group-active' : '' }}"
                data-bs-toggle="collapse"
                data-bs-target="#instructorCoursesMenu"
                aria-expanded="{{ $inCoursesGroup ? 'true' : 'false' }}">
                <i class="fa-solid fa-book-open"></i>
                <span>Assigned Courses</span>
                <i class="fa-solid fa-chevron-right out-nav-chevron"></i>
            </button>
            <div class="collapse {{ $inCoursesGroup ? 'show' : '' }}" id="instructorCoursesMenu">
                <ul class="out-nav-submenu">
                    <li>
                        <a href="{{ route('instructor.courses') }}" class="out-nav-link {{ request()->routeIs('instructor.courses*') ? 'active' : '' }}">
                            <i class="fa-solid fa-book-bookmark"></i>
                            <span>My Assigned Courses</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('instructor.bundles.index') }}" class="out-nav-link {{ request()->routeIs('instructor.bundles.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-layer-group"></i>
                            <span>Course Bundles</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- Teaching & Scholars --}}
        <li class="out-nav-item">
            <button type="button"
                class="out-nav-link out-nav-toggle {{ $inTeachingGroup ? 'group-active' : '' }}"
                data-bs-toggle="collapse"
                data-bs-target="#instructorTeachingMenu"
                aria-expanded="{{ $inTeachingGroup ? 'true' : 'false' }}">
                <i class="fa-solid fa-chalkboard-user"></i>
                <span>Teaching &amp; Scholars</span>
                <i class="fa-solid fa-chevron-right out-nav-chevron"></i>
            </button>
            <div class="collapse {{ $inTeachingGroup ? 'show' : '' }}" id="instructorTeachingMenu">
                <ul class="out-nav-submenu">
                    <li>
                        <a href="{{ route('instructor.live-classes.index') }}" class="out-nav-link {{ request()->routeIs('instructor.live-classes.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-video"></i>
                            <span>Live Classes</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('instructor.students') }}" class="out-nav-link {{ request()->routeIs('instructor.students*') ? 'active' : '' }}">
                            <i class="fa-solid fa-users"></i>
                            <span>Enrolled Scholars</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- Course Discussions (Public Forum Monitored by Admin) --}}
        <li class="out-nav-item">
            <a href="{{ route('instructor.discussions.index') }}" class="out-nav-link {{ request()->routeIs('instructor.discussions.*') ? 'active' : '' }}">
                <i class="fa-solid fa-comments"></i>
                <span>Course Discussions</span>
            </a>
        </li>

        {{-- Earnings & Payouts --}}
        <li class="out-nav-item">
            <button type="button"
                class="out-nav-link out-nav-toggle {{ $inFinanceGroup ? 'group-active' : '' }}"
                data-bs-toggle="collapse"
                data-bs-target="#instructorReportsMenu"
                aria-expanded="{{ $inFinanceGroup ? 'true' : 'false' }}">
                <i class="fa-solid fa-wallet"></i>
                <span>Earnings &amp; Payouts</span>
                <i class="fa-solid fa-chevron-right out-nav-chevron"></i>
            </button>
            <div class="collapse {{ $inFinanceGroup ? 'show' : '' }}" id="instructorReportsMenu">
                <ul class="out-nav-submenu">
                    <li>
                        <a href="{{ route('instructor.earnings') }}" class="out-nav-link {{ request()->routeIs('instructor.earnings') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-line"></i>
                            <span>Course Earnings</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('instructor.withdrawals') }}" class="out-nav-link {{ request()->routeIs('instructor.withdrawals') ? 'active' : '' }}">
                            <i class="fa-solid fa-money-bill-transfer"></i>
                            <span>Request Payout</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('instructor.reviews') }}" class="out-nav-link {{ request()->routeIs('instructor.reviews') ? 'active' : '' }}">
                            <i class="fa-solid fa-star"></i>
                            <span>Scholar Reviews</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    </ul>
</aside>
