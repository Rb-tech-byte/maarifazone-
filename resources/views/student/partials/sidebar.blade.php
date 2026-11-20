@php
    // Reusable Component Safety: only render when invoked from the portal layout shell
    if (empty($fromLayout)) {
        return;
    }

    $user = $user ?? auth()->user();

    $inLearningGroup = request()->routeIs([
        'student.my-courses*',
        'student.live-classes.*',
        'student.community.*',
    ]);

    $inBillingGroup = request()->routeIs([
        'student.payments*',
    ]);

    $inSupportGroup = request()->routeIs([
        'student.messages.*',
        'student.ai-chat.*',
        'student.notifications.*',
        'student.tickets.*',
        'student.help.*',
    ]);
@endphp

<aside class="maarifazone-portal-sidebar" id="maarifazonePortalSidebar">
    <ul class="out-sidebar-nav">
        <li class="out-nav-item">
            <a href="{{ route('student.dashboard') }}" class="out-nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge"></i>
                <span>Dashboard</span>
            </a>
        </li>

        {{-- My Maarifa Learning --}}
        <li class="out-nav-item">
            <button type="button"
                class="out-nav-link out-nav-toggle {{ $inLearningGroup ? 'group-active' : '' }}"
                data-bs-toggle="collapse"
                data-bs-target="#studentLearningMenu"
                aria-expanded="{{ $inLearningGroup ? 'true' : 'false' }}">
                <i class="fa-solid fa-book-open"></i>
                <span>My Learning</span>
                <i class="fa-solid fa-chevron-right out-nav-chevron"></i>
            </button>
            <div class="collapse {{ $inLearningGroup ? 'show' : '' }}" id="studentLearningMenu">
                <ul class="out-nav-submenu">
                    <li>
                        <a href="{{ route('student.my-courses') }}" class="out-nav-link {{ request()->routeIs('student.my-courses*') ? 'active' : '' }}">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <span>My Online Courses</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('student.live-classes.index') }}" class="out-nav-link {{ request()->routeIs('student.live-classes.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-video"></i>
                            <span>Live Classes</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('student.community.index') }}" class="out-nav-link {{ request()->routeIs('student.community.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-comments"></i>
                            <span>Learner Discussions</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- Payments --}}
        <li class="out-nav-item">
            <a href="{{ route('student.payments') }}" class="out-nav-link {{ $inBillingGroup ? 'active' : '' }}">
                <i class="fa-solid fa-receipt"></i>
                <span>Payment History</span>
            </a>
        </li>

        {{-- Support & Tools --}}
        <li class="out-nav-item">
            <button type="button"
                class="out-nav-link out-nav-toggle {{ $inSupportGroup ? 'group-active' : '' }}"
                data-bs-toggle="collapse"
                data-bs-target="#studentSupportMenu"
                aria-expanded="{{ $inSupportGroup ? 'true' : 'false' }}">
                <i class="fa-solid fa-headset"></i>
                <span>Support &amp; Tools</span>
                <i class="fa-solid fa-chevron-right out-nav-chevron"></i>
            </button>
            <div class="collapse {{ $inSupportGroup ? 'show' : '' }}" id="studentSupportMenu">
                <ul class="out-nav-submenu">
                    <li>
                        <a href="{{ route('student.messages.index') }}" class="out-nav-link {{ request()->routeIs('student.messages.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-envelope"></i>
                            <span>Messages</span>
                            <span class="badge bg-danger rounded-pill out-nav-badge student-chat-badge" style="display: none;"></span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('student.ai-chat.index') }}" class="out-nav-link {{ request()->routeIs('student.ai-chat.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-robot"></i>
                            <span>AI Study Assistant</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('student.notifications.index') }}" class="out-nav-link {{ request()->routeIs('student.notifications.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-bell"></i>
                            <span>Notifications</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('student.tickets.index') }}" class="out-nav-link {{ request()->routeIs('student.tickets.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-life-ring"></i>
                            <span>Support Desk</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('student.help.index') }}" class="out-nav-link {{ request()->routeIs('student.help.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-circle-question"></i>
                            <span>Online Help</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    </ul>
</aside>
