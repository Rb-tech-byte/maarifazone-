@forelse($courses as $course)
    @php
        $status = $course->status ?? 'draft';
        $statusConfig = match ($status) {
            'published' => ['label' => 'Published', 'badge' => 'badge-success', 'border' => 'border-success', 'dot' => '#10b981'],
            'pending'   => ['label' => 'Pending Review', 'badge' => 'badge-warning', 'border' => 'border-warning', 'dot' => '#f59e0b'],
            'archived'  => ['label' => 'Archived', 'badge' => 'badge-dark', 'border' => 'border-dark', 'dot' => '#334155'],
            default     => ['label' => 'Draft', 'badge' => 'badge-secondary', 'border' => 'border-secondary', 'dot' => '#64748b'],
        };
        $isFree = ($course->pricing_model ?? 'paid') === 'free';
        $thumbnailUrl = $course->thumbnail;
    @endphp

    <div class="card mz-course-card mb-3 border-0 shadow-sm transition-all" data-course-id="{{ $course->id }}" id="course-card-{{ $course->id }}">
        <div class="card-body p-3 p-md-4">
            <div class="row align-items-center">
                {{-- Course Thumbnail & Media Overlay --}}
                <div class="col-12 col-md-3 col-xl-2 mb-3 mb-md-0">
                    <div class="position-relative mz-thumb-container rounded overflow-hidden">
                        <img src="{{ $thumbnailUrl }}" alt="{{ $course->title }}"
                             class="img-fluid w-100 mz-course-thumb"
                             loading="lazy"
                             onerror="this.onerror=null; this.src='{{ asset('assets/front/img/home-1/courses/courses-01.jpg') }}';">
                        
                        {{-- Top status pill overlay --}}
                        <div class="position-absolute" style="top: 8px; left: 8px;">
                            <span class="badge {{ $statusConfig['badge'] }} shadow-sm px-2 py-1" style="font-size: 11px; font-weight: 600;">
                                <i class="fas fa-circle mr-1" style="font-size: 7px;"></i>{{ $statusConfig['label'] }}
                            </span>
                        </div>

                        {{-- Online / Live badge overlay --}}
                        <div class="position-absolute" style="bottom: 8px; right: 8px;">
                            @if (!empty($course->is_live_course))
                                <span class="badge badge-danger shadow-sm px-2 py-1" style="font-size: 10px;">
                                    <i class="fas fa-broadcast-tower mr-1"></i>Live
                                </span>
                            @else
                                <span class="badge badge-dark shadow-sm px-2 py-1" style="font-size: 10px; background-color: rgba(15, 23, 42, 0.85);">
                                    <i class="fas fa-laptop-code mr-1"></i>Online
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Course Info & Curriculum Metadata --}}
                <div class="col-12 col-md-6 col-xl-7 mb-3 mb-md-0">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge badge-light text-muted border px-2 py-1" style="font-size: 11px; font-weight: 500; background: #f8fafc;">
                            <i class="fas fa-folder-open text-primary mr-1"></i>{{ $course->category ?? 'Uncategorized' }}
                        </span>
                        @if ($course->duration_label)
                            <span class="badge badge-light text-muted border px-2 py-1" style="font-size: 11px; font-weight: 500; background: #f8fafc;">
                                <i class="far fa-clock mr-1"></i>{{ $course->duration_label }}
                            </span>
                        @endif
                        <span class="badge badge-light text-muted border px-2 py-1" style="font-size: 11px; font-weight: 500; background: #f8fafc;">
                            <i class="far fa-calendar-alt mr-1"></i>{{ $course->created_at->format('M d, Y') }}
                        </span>
                    </div>

                    <h5 class="mz-course-title mb-2">
                        <a href="{{ route('admin.courses.show', $course->id) }}" class="text-dark text-decoration-none font-weight-bold" title="{{ $course->title }}">
                            {{ Str::limit($course->title, 75) }}
                        </a>
                    </h5>

                    @if ($course->description)
                        <p class="text-muted small mb-2 text-truncate" style="max-width: 650px;">
                            {{ Str::limit(strip_tags($course->description), 140) }}
                        </p>
                    @endif

                    {{-- Modular Curriculum Summary Chips --}}
                    <div class="d-flex flex-wrap align-items-center gap-2 my-2">
                        <span class="mz-meta-chip" title="Structured course modules and topics">
                            <i class="fas fa-layer-group text-primary mr-1"></i>
                            <strong>{{ (int) ($course->topics_count ?? 0) }}</strong> {{ ((int) ($course->topics_count ?? 0) === 1) ? 'Topic' : 'Topics' }}
                        </span>
                        <span class="mz-meta-chip" title="Multimedia lessons: Video, Text, PDF, PPT">
                            <i class="fas fa-play-circle text-info mr-1"></i>
                            <strong>{{ (int) ($course->lessons_count ?? 0) }}</strong> {{ ((int) ($course->lessons_count ?? 0) === 1) ? 'Lesson' : 'Lessons' }}
                        </span>
                        <span class="mz-meta-chip" title="Scholars enrolled in this course">
                            <i class="fas fa-user-graduate text-success mr-1"></i>
                            <strong>{{ (int) ($course->enrollments_count ?? 0) }}</strong> {{ ((int) ($course->enrollments_count ?? 0) === 1) ? 'Scholar' : 'Scholars' }}
                        </span>
                    </div>

                    {{-- Assigned Instructor Chip --}}
                    <div class="mz-instructor-chip d-inline-flex align-items-center mt-1">
                        @if ($course->instructor)
                            <div class="mz-avatar-sm mr-2 text-uppercase font-weight-bold">
                                {{ substr($course->instructor->name, 0, 1) }}
                            </div>
                            <div class="small">
                                <span class="font-weight-600 text-dark">{{ $course->instructor->name }}</span>
                                <span class="text-muted ml-1">&bull; {{ $course->instructor->email }}</span>
                            </div>
                        @else
                            <div class="mz-avatar-sm mr-2 text-uppercase font-weight-bold bg-secondary text-white">
                                <i class="fas fa-user-shield" style="font-size: 11px;"></i>
                            </div>
                            <div class="small text-muted font-italic">
                                Maarifazone Direct / No Instructor Assigned
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Pricing, Status Switcher & Action Buttons --}}
                <div class="col-12 col-md-3 col-xl-3 border-left-md pl-md-4 text-md-right">
                    {{-- Price Display --}}
                    <div class="mb-3">
                        @if ($isFree)
                            <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 13px; letter-spacing: 0.5px;">
                                <i class="fas fa-gift mr-1"></i> Free Course
                            </span>
                        @else
                            <div class="mz-price-block">
                                @if ($course->sale_price)
                                    <div class="h5 mb-0 font-weight-bold text-dark">
                                        {{ $course->formatPrice($course->sale_price) }}
                                    </div>
                                    <small class="text-muted text-decoration-line-through d-block">
                                        {{ $course->formatPrice($course->regular_price ?? 0) }}
                                    </small>
                                @else
                                    <div class="h5 mb-0 font-weight-bold text-dark">
                                        {{ $course->formatPrice($course->regular_price ?? 0) }}
                                    </div>
                                @endif

                                @if ($course->getPriceCurrencyCode() !== currency_code())
                                    <div class="text-muted small mt-1" style="font-size: 11px;">
                                        Base: {{ currency_format($course->sale_price ?: ($course->regular_price ?? 0), 2, 'code', $course->getPriceCurrencyCode(), $course->getPriceCurrencyCode(), false) }}
                                    </div>
                                @endif
                                <div class="text-muted small mt-1" style="font-size: 11px;">
                                    <i class="fas fa-tags text-primary mr-1"></i> Modular topic pricing
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Quick Status Changer --}}
                    <div class="mb-3 d-flex justify-content-md-end">
                        <div class="dropdown status-dropdown-wrapper">
                            <form action="{{ route('admin.courses.update-status', $course->id) }}" method="POST" class="inline-status-form">
                                @csrf
                                <select name="status" class="form-control form-control-sm mz-status-select"
                                        data-course-id="{{ $course->id }}"
                                        data-current-status="{{ $course->status }}"
                                        style="font-size: 12px; font-weight: 600; min-width: 130px; height: 32px; border-radius: 6px;">
                                    <option value="published" {{ $course->status === 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="pending" {{ $course->status === 'pending' ? 'selected' : '' }}>Pending Review</option>
                                    <option value="draft" {{ $course->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="archived" {{ $course->status === 'archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                            </form>
                        </div>
                    </div>

                    {{-- Actions Menu --}}
                    <div class="mz-actions-toolbar d-flex flex-wrap justify-content-md-end align-items-center gap-1">
                        {{-- Pending Quick Approval Buttons --}}
                        @if ($course->status === 'pending')
                            <button type="button" class="btn btn-sm btn-success approve-course-btn"
                                    data-course-id="{{ $course->id }}"
                                    data-course-title="{{ $course->title }}"
                                    title="Approve and Publish Course">
                                <i class="fas fa-check mr-1"></i> Approve
                            </button>
                            <button type="button" class="btn btn-sm btn-warning text-dark reject-course-btn"
                                    data-course-id="{{ $course->id }}"
                                    data-course-title="{{ $course->title }}"
                                    title="Reject Course">
                                <i class="fas fa-times mr-1"></i> Reject
                            </button>
                        @endif

                        {{-- View Course Details --}}
                        <a href="{{ route('admin.courses.show', $course->id) }}"
                           class="btn btn-sm btn-outline-info"
                           title="Course Overview & Statistics">
                            <i class="fas fa-eye"></i>
                        </a>

                        {{-- Edit Course --}}
                        <a href="{{ route('admin.courses.edit', $course->id) }}"
                           class="btn btn-sm btn-outline-primary"
                           title="Edit Course & Curriculum">
                            <i class="fas fa-edit"></i>
                        </a>

                        {{-- Public Page Preview Link --}}
                        <a href="{{ route('courses.show', $course->id) }}"
                           target="_blank"
                           class="btn btn-sm btn-outline-secondary"
                           title="Preview Public Course Page">
                            <i class="fas fa-external-link-alt"></i>
                        </a>

                        {{-- Archive or Restore --}}
                        @if ($course->status !== 'archived')
                            <button type="button" class="btn btn-sm btn-outline-warning archive-course-btn"
                                    data-course-id="{{ $course->id }}"
                                    data-course-title="{{ $course->title }}"
                                    title="Archive Course">
                                <i class="fas fa-archive"></i>
                            </button>
                        @else
                            <form action="{{ route('admin.courses.update-status', $course->id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="draft">
                                <button type="submit" class="btn btn-sm btn-outline-success" title="Restore to Draft">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </form>
                        @endif

                        {{-- Permanent Delete --}}
                        <button type="button" class="btn btn-sm btn-outline-danger full-delete-btn"
                                data-course-id="{{ $course->id }}"
                                data-course-title="{{ $course->title }}"
                                title="Permanent Safe Delete (Irreversible)">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="card border-0 shadow-sm py-5 text-center" style="border-radius: 14px; background: #ffffff;">
        <div class="card-body">
            <div class="mb-3">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle"
                     style="width: 72px; height: 72px; background: #f1f5f9; color: #64748b;">
                    <i class="fas fa-search-minus fa-2x"></i>
                </div>
            </div>
            <h5 class="text-dark font-weight-bold mb-1">No Courses Found</h5>
            <p class="text-muted small mb-3">No online courses match your search keywords or active filter criteria.</p>
            <div class="d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="empty-clear-filters-btn">
                    <i class="fas fa-undo mr-1"></i> Clear Filters
                </button>
                <a href="{{ route('admin.courses.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus mr-1"></i> Create New Course
                </a>
            </div>
        </div>
    </div>
@endforelse

