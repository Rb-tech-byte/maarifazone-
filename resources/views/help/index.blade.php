@extends($layout)

@section('title', 'Online Help & Security Guide')

@section($layout === 'layouts.admin' ? 'main-content' : 'content')
<div class="{{ $layout === 'layouts.admin' ? '' : 'container-fluid py-3' }}">
    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h4 class="text-primary mb-1"><i class="fas fa-question-circle mr-2"></i>{{ site_name() }} Online Help Center</h4>
                <p class="text-muted mb-0">Signed in as <strong>{{ $user->name }}</strong> (<span class="badge badge-primary">{{ $roleName }}</span>)</p>
            </div>
            <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary mt-2 mt-md-0">Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0 text-primary"><i class="fa-solid fa-graduation-cap me-1"></i> Scholar Learning Experience</h5></div>
                <div class="card-body small">
                    <ul class="mb-0">
                        <li class="mb-2"><strong>100% Online Maarifa:</strong> Enroll directly in self-paced video lessons and interactive content anytime, anywhere without physical attendance barriers.</li>
                        <li class="mb-2"><strong>Live Classes &amp; Discussions:</strong> Participate in live tutoring sessions and collaborate in open course discussions with fellow scholars.</li>
                        <li class="mb-0"><strong>Centralized Support Desk:</strong> Contact System Administration directly via Support Tickets for any billing, account, or learning inquiries.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0 text-primary"><i class="fa-solid fa-chalkboard-user me-1"></i> Instructor &amp; Administration Flow</h5></div>
                <div class="card-body small">
                    <ul class="mb-0">
                        <li class="mb-2"><strong>Instructors:</strong> Manage assigned courses, host live classes, respond to public course discussions, track revenue share earnings, and request payouts.</li>
                        <li class="mb-2"><strong>System Administration:</strong> Fully manage courses, categories, instructor assignments, payment gateways (Pesapal &amp; Offline), and platform settings.</li>
                        <li class="mb-0"><strong>Institutional Governance:</strong> Anti-disintermediation ensures all official scholar inquiries and support requests route centrally through Administration.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h5 class="mb-0 text-primary">Account &amp; Session Security Policies (SRS Section 5.1)</h5></div>
                <div class="card-body small">
                    <ul class="mb-0">
                        <li class="mb-1"><strong>Password Complexity &amp; Expiry:</strong> Passwords require at least 8 characters combining uppercase, lowercase, numbers, and special characters. Periodic password rotation is enforced when enabled by System Admin.</li>
                        <li class="mb-1"><strong>Lockout Protection:</strong> Consecutive failed sign-in attempts trigger temporary cooldown lockouts and escalate to account suspension until unblocked by an administrator.</li>
                        <li class="mb-1"><strong>Session Integrity:</strong> Idle sessions expire automatically (default 5 minutes of inactivity) and single active session rules prevent simultaneous logins across browsers.</li>
                        <li class="mb-0"><strong>Form Draft Recovery:</strong> Unsaved non-password form data is automatically cached in your browser for 15 minutes so accidental refreshes never lose work.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
