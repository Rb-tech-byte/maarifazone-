@php
    $dashUser = auth()->user();
    $scope = $analytics['scope'] ?? [];
@endphp
<div class="card mb-3 border-left-primary">
    <div class="card-body py-3 d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <span class="badge badge-primary mr-2" style="background-color: #F99F1E; color: #fff;">{{ $scope['role_label'] ?? 'Administrator' }}</span>
            <span class="text-muted small">
                <i class="fas fa-globe mr-1" style="color: #F99F1E;"></i>
                <strong>Online Academy Operations</strong>
            </span>
        </div>
        <div class="d-flex flex-wrap align-items-center mt-2 mt-md-0" style="gap: 8px;">
            <a href="{{ route('admin.courses.index') }}" class="badge badge-light p-2">
                <i class="fas fa-book text-warning mr-1"></i> Total Courses: <strong>{{ $analytics['overview']['total_courses'] ?? 0 }}</strong>
            </a>
            <a href="{{ route('admin.students.index') }}" class="badge badge-light p-2">
                <i class="fas fa-user-graduate text-success mr-1"></i> Scholars: <strong>{{ $analytics['overview']['total_students'] ?? 0 }}</strong>
            </a>
            <a href="{{ route('admin.instructors.index') }}" class="badge badge-light p-2">
                <i class="fas fa-chalkboard-user text-info mr-1"></i> Instructors
            </a>
        </div>
    </div>
</div>
