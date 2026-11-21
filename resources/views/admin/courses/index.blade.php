@extends('layouts.admin')

@section('title', 'All Courses')

@section('breadcrumb')
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
        <div class="breadcrumb-item">Courses</div>
    </div>
@endsection

@push('styles')
    <style>
        /* Executive Maarifazone Theme - Slate Navy & Clean Neutral Palette */
        :root {
            --mz-slate-dark: #0f172a;
            --mz-slate-mid: #1e293b;
            --mz-slate-light: #f8fafc;
            --mz-slate-border: #e2e8f0;
            --mz-amber-accent: #f99f1e;
            --mz-sky: #0284c7;
            --mz-emerald: #10b981;
        }

        .mz-metric-card {
            background: #ffffff;
            border: 1px solid var(--mz-slate-border);
            border-radius: 12px;
            padding: 18px 20px;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }
        .mz-metric-card:hover {
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
            transform: translateY(-2px);
        }
        .mz-metric-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        /* Control & Filter Toolbar */
        .mz-control-panel {
            background: #ffffff;
            border: 1px solid var(--mz-slate-border);
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
            margin-bottom: 24px;
        }

        .mz-search-input {
            height: 44px;
            border-radius: 10px;
            border: 1px solid var(--mz-slate-border);
            padding-left: 40px;
            padding-right: 36px;
            font-size: 14px;
            transition: all 0.2s ease;
            background-color: #f8fafc;
        }
        .mz-search-input:focus {
            background-color: #ffffff;
            border-color: var(--mz-slate-dark);
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08);
        }

        /* Status Tab Pills */
        .mz-status-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .mz-status-btn {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid var(--mz-slate-border);
            border-radius: 8px;
            padding: 6px 14px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }
        .mz-status-btn:hover {
            background: #e2e8f0;
            color: var(--mz-slate-dark);
            text-decoration: none;
        }
        .mz-status-btn.active {
            background: var(--mz-slate-dark);
            color: #ffffff;
            border-color: var(--mz-slate-dark);
        }
        .mz-status-btn .badge {
            margin-left: 6px;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 6px;
        }
        .mz-status-btn.active .badge {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        /* Course Cards Styling */
        .mz-course-card {
            border: 1px solid var(--mz-slate-border);
            border-radius: 12px;
            background: #ffffff;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .mz-course-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.07);
            transform: translateY(-2px);
        }
        .mz-thumb-container {
            aspect-ratio: 16 / 10;
            background: #0f172a;
        }
        .mz-course-thumb {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .mz-course-card:hover .mz-course-thumb {
            transform: scale(1.03);
        }
        .mz-course-title a {
            color: var(--mz-slate-dark);
            transition: color 0.15s ease;
        }
        .mz-course-title a:hover {
            color: var(--mz-sky);
        }
        .mz-meta-chip {
            background: #f8fafc;
            border: 1px solid var(--mz-slate-border);
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 12px;
            color: #475569;
            display: inline-flex;
            align-items: center;
        }
        .mz-avatar-sm {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #0f172a;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
        }

        /* Subtle Loading Overlay */
        #courses-list-wrapper {
            position: relative;
            min-height: 220px;
        }
        .mz-loading-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(2px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 20;
            border-radius: 12px;
        }
        .mz-loading-overlay.active {
            display: flex;
        }

        @media (min-width: 768px) {
            .border-left-md {
                border-left: 1px solid var(--mz-slate-border);
            }
        }
    </style>
@endpush

@section('main-content')
    {{-- Executive Summary Counters Row --}}
    <div class="row mb-4">
        <div class="col-6 col-md-3 mb-3 mb-md-0">
            <div class="mz-metric-card d-flex align-items-center">
                <div class="mz-metric-icon mr-3" style="background: #f1f5f9; color: #0f172a;">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div>
                    <div class="h4 mb-0 font-weight-bold text-dark" id="stat-total">{{ $stats['total'] }}</div>
                    <small class="text-muted font-weight-500">Active Courses</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3 mb-md-0">
            <div class="mz-metric-card d-flex align-items-center">
                <div class="mz-metric-icon mr-3" style="background: #ecfdf5; color: #10b981;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <div class="h4 mb-0 font-weight-bold text-dark" id="stat-published">{{ $stats['published'] }}</div>
                    <small class="text-muted font-weight-500">Live / Published</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3 mb-md-0">
            <div class="mz-metric-card d-flex align-items-center">
                <div class="mz-metric-icon mr-3" style="background: #fffbeb; color: #f59e0b;">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div>
                    <div class="h4 mb-0 font-weight-bold text-dark" id="stat-pending">{{ $stats['pending'] }}</div>
                    <small class="text-muted font-weight-500">Pending Review</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3 mb-md-0">
            <div class="mz-metric-card d-flex align-items-center">
                <div class="mz-metric-icon mr-3" style="background: #f8fafc; color: #64748b;">
                    <i class="fas fa-file-signature"></i>
                </div>
                <div>
                    <div class="h4 mb-0 font-weight-bold text-dark" id="stat-draft">{{ $stats['draft'] }}</div>
                    <small class="text-muted font-weight-500">Draft / Staged</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Modern Control & Search Toolbar --}}
    <div class="mz-control-panel">
        {{-- Row 1: Search & Quick Actions --}}
        <div class="row align-items-center mb-3">
            <div class="col-12 col-lg-6 mb-3 mb-lg-0">
                <div class="position-relative">
                    <i class="fas fa-search position-absolute text-muted" style="left: 14px; top: 15px; font-size: 14px;"></i>
                    <input type="text" id="course-search-input" class="form-control mz-search-input"
                           placeholder="Real-time search by title, category, instructor name or email..."
                           value="{{ request('search') }}" autocomplete="off">
                    <button type="button" id="clear-search-btn" class="btn btn-link position-absolute text-muted p-0 {{ request('search') ? '' : 'd-none' }}"
                            style="right: 12px; top: 10px; font-size: 16px;">
                        <i class="fas fa-times-circle"></i>
                    </button>
                    <div id="search-spinner" class="position-absolute d-none" style="right: 14px; top: 14px;">
                        <i class="fas fa-spinner fa-spin text-primary"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6 text-lg-right d-flex flex-wrap justify-content-lg-end align-items-center gap-2">
                {{-- Currency Switcher --}}
                @php
                    $activeCurrencyCode = currency_code();
                @endphp
                <div class="btn-group btn-group-sm mr-2" role="group" aria-label="Currency Switch" title="1 USD = {{ number_format(usd_to_tzs_rate(), 0) }} TZS">
                    @foreach (supported_switch_currencies() as $curCode => $curLabel)
                        <a href="{{ request()->fullUrlWithQuery(['currency' => $curCode]) }}"
                           class="btn {{ $curCode === $activeCurrencyCode ? 'btn-dark' : 'btn-outline-secondary' }}">
                            {{ $curCode === 'TZS' ? 'TSH' : 'USD ($)' }}
                        </a>
                    @endforeach
                </div>

                {{-- Create Course Button --}}
                <a href="{{ route('admin.courses.create') }}" class="btn btn-primary" style="background: #0f172a; border-color: #0f172a; border-radius: 8px; font-weight: 500;">
                    <i class="fas fa-plus mr-1"></i> Create Course
                </a>
            </div>
        </div>

        {{-- Row 2: Status Tab Filters & Granular Selectors --}}
        <div class="row align-items-center">
            <div class="col-12 col-xl-7 mb-3 mb-xl-0">
                <div class="mz-status-tabs" id="status-tabs-container">
                    <button type="button" class="mz-status-btn {{ (!request('status') || request('status') === '') ? 'active' : '' }}" data-status="">
                        All Courses <span class="badge badge-light">{{ $stats['total'] }}</span>
                    </button>
                    <button type="button" class="mz-status-btn {{ request('status') === 'published' ? 'active' : '' }}" data-status="published">
                        Published <span class="badge badge-success">{{ $stats['published'] }}</span>
                    </button>
                    <button type="button" class="mz-status-btn {{ request('status') === 'pending' ? 'active' : '' }}" data-status="pending">
                        Pending <span class="badge badge-warning">{{ $stats['pending'] }}</span>
                    </button>
                    <button type="button" class="mz-status-btn {{ request('status') === 'draft' ? 'active' : '' }}" data-status="draft">
                        Draft <span class="badge badge-secondary">{{ $stats['draft'] }}</span>
                    </button>
                    <button type="button" class="mz-status-btn {{ request('status') === 'archived' ? 'active' : '' }}" data-status="archived">
                        Archived <span class="badge badge-dark">{{ $stats['archived'] }}</span>
                    </button>
                </div>
            </div>

            <div class="col-12 col-xl-5 d-flex flex-wrap justify-content-xl-end align-items-center gap-2">
                {{-- Category Filter --}}
                <select id="filter-category" class="form-control form-control-sm" style="max-width: 150px; border-radius: 8px;">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>

                {{-- Pricing Filter --}}
                <select id="filter-pricing" class="form-control form-control-sm" style="max-width: 130px; border-radius: 8px;">
                    <option value="">All Pricing</option>
                    <option value="free" {{ request('pricing_model') === 'free' ? 'selected' : '' }}>Free</option>
                    <option value="paid" {{ request('pricing_model') === 'paid' ? 'selected' : '' }}>Paid</option>
                </select>

                {{-- Per Page Selector (Default 4 per page) --}}
                <select id="filter-per-page" class="form-control form-control-sm" style="max-width: 120px; border-radius: 8px;" title="Items per page">
                    <option value="4" {{ (int) request('per_page', $perPage ?? 4) === 4 ? 'selected' : '' }}>4 per page</option>
                    <option value="8" {{ (int) request('per_page') === 8 ? 'selected' : '' }}>8 per page</option>
                    <option value="12" {{ (int) request('per_page') === 12 ? 'selected' : '' }}>12 per page</option>
                    <option value="24" {{ (int) request('per_page') === 24 ? 'selected' : '' }}>24 per page</option>
                </select>

                {{-- Reset Filters --}}
                <button type="button" id="reset-filters-btn" class="btn btn-sm btn-outline-secondary" title="Reset all search filters" style="border-radius: 8px;">
                    <i class="fas fa-undo"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Course List Container with AJAX Live Loading Overlay --}}
    <div id="courses-list-wrapper">
        <div class="mz-loading-overlay" id="courses-loading-overlay">
            <div class="text-center p-3 rounded" style="background: rgba(15, 23, 42, 0.9); color: #ffffff;">
                <i class="fas fa-circle-notch fa-spin fa-2x mb-2 text-warning"></i>
                <div class="small font-weight-500">Updating courses...</div>
            </div>
        </div>

        <div id="courses-list-content">
            @include('admin.courses.partials.courses-list')
        </div>
    </div>

    {{-- Modern Pagination & Summary Footer --}}
    <div class="card border-0 shadow-sm mt-3" style="border-radius: 12px; background: #ffffff;">
        <div class="card-body p-3 d-flex flex-column flex-md-row justify-content-between align-items-center">
            <div class="mb-2 mb-md-0">
                <span class="text-muted small" id="pagination-summary">
                    Showing {{ $courses->firstItem() ?? 0 }} to {{ $courses->lastItem() ?? 0 }} of {{ $courses->total() }} courses
                </span>
            </div>
            <div id="pagination-links-container">
                {{ $courses->links('vendor.pagination.stisla-default') }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let searchDebounceTimer = null;
            let currentXhr = null;

            // State management
            const state = {
                search: $('#course-search-input').val().trim(),
                status: '{{ request('status', '') }}',
                category: $('#filter-category').val(),
                pricing_model: $('#filter-pricing').val(),
                per_page: $('#filter-per-page').val() || 4,
                page: {{ $courses->currentPage() ?? 1 }}
            };

            // Status Tabs Click Event
            $('#status-tabs-container').on('click', '.mz-status-btn', function() {
                const selectedStatus = $(this).data('status');
                $('.mz-status-btn').removeClass('active');
                $(this).addClass('active');
                state.status = selectedStatus;
                state.page = 1;
                fetchCourses();
            });

            // Real-Time Debounced Search Input (300ms)
            $('#course-search-input').on('input', function() {
                const query = $(this).val();
                state.search = query.trim();
                state.page = 1;

                if (query.length > 0) {
                    $('#clear-search-btn').removeClass('d-none');
                } else {
                    $('#clear-search-btn').addClass('d-none');
                }

                clearTimeout(searchDebounceTimer);
                $('#search-spinner').removeClass('d-none');

                searchDebounceTimer = setTimeout(function() {
                    fetchCourses();
                }, 300);
            });

            // Clear Search Button
            $('#clear-search-btn').on('click', function() {
                $('#course-search-input').val('').focus();
                $(this).addClass('d-none');
                state.search = '';
                state.page = 1;
                fetchCourses();
            });

            // Empty State Clear Filters Button
            $(document).on('click', '#empty-clear-filters-btn, #reset-filters-btn', function() {
                $('#course-search-input').val('');
                $('#clear-search-btn').addClass('d-none');
                $('#filter-category').val('');
                $('#filter-pricing').val('');
                $('#filter-per-page').val('4');
                $('.mz-status-btn').removeClass('active');
                $('.mz-status-btn[data-status=""]').addClass('active');

                state.search = '';
                state.status = '';
                state.category = '';
                state.pricing_model = '';
                state.per_page = 4;
                state.page = 1;

                fetchCourses();
            });

            // Category & Pricing Filter Change
            $('#filter-category').on('change', function() {
                state.category = $(this).val();
                state.page = 1;
                fetchCourses();
            });

            $('#filter-pricing').on('change', function() {
                state.pricing_model = $(this).val();
                state.page = 1;
                fetchCourses();
            });

            $('#filter-per-page').on('change', function() {
                state.per_page = $(this).val();
                state.page = 1;
                fetchCourses();
            });

            // AJAX Pagination Click Interception
            $(document).on('click', '#pagination-links-container a.page-link', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                if (!url || url === '#' || $(this).parent().hasClass('disabled')) {
                    return;
                }

                try {
                    const parsedUrl = new URL(url, window.location.origin);
                    const pageVal = parsedUrl.searchParams.get('page');
                    if (pageVal) {
                        state.page = parseInt(pageVal, 10);
                        fetchCourses();
                        $('html, body').animate({
                            scrollTop: $('#courses-list-wrapper').offset().top - 120
                        }, 250);
                    }
                } catch (err) {
                    window.location.href = url;
                }
            });

            // Main Fetch Function with AJAX & URL Synchronization
            function fetchCourses() {
                if (currentXhr && currentXhr.readyState !== 4) {
                    currentXhr.abort();
                }

                $('#courses-loading-overlay').addClass('active');
                $('#search-spinner').removeClass('d-none');

                const params = new URLSearchParams();
                if (state.search) params.set('search', state.search);
                if (state.status) params.set('status', state.status);
                if (state.category) params.set('category', state.category);
                if (state.pricing_model) params.set('pricing_model', state.pricing_model);
                if (state.per_page) params.set('per_page', state.per_page);
                if (state.page > 1) params.set('page', state.page);

                // Preserve currency parameter if present
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('currency')) {
                    params.set('currency', urlParams.get('currency'));
                }

                const queryUrl = '{{ route('admin.courses.index') }}?' + params.toString();

                currentXhr = $.ajax({
                    url: queryUrl,
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        $('#courses-list-content').html(response.html);
                        $('#pagination-links-container').html(response.pagination);
                        $('#pagination-summary').text(`Showing ${response.firstItem} to ${response.lastItem} of ${response.total} courses`);

                        // Update counters if provided
                        if (response.stats) {
                            $('#stat-total').text(response.stats.total);
                            $('#stat-published').text(response.stats.published);
                            $('#stat-pending').text(response.stats.pending);
                            $('#stat-draft').text(response.stats.draft);
                        }

                        // Update browser URL without reloading
                        window.history.pushState(state, '', queryUrl);
                    },
                    error: function(xhr, status) {
                        if (status !== 'abort') {
                            console.error('Failed to update courses list', xhr);
                        }
                    },
                    complete: function() {
                        $('#courses-loading-overlay').removeClass('active');
                        $('#search-spinner').addClass('d-none');
                    }
                });
            }

            // Inline Status Switcher AJAX Handler
            $(document).on('change', '.mz-status-select', function() {
                const $select = $(this);
                const courseId = $select.data('course-id');
                const newStatus = $select.val();
                const previousStatus = $select.data('current-status');

                $.ajax({
                    url: '{{ route('admin.courses.update-status', ':id') }}'.replace(':id', courseId),
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: newStatus
                    },
                    success: function(res) {
                        $select.data('current-status', newStatus);
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Status Updated',
                                text: res.message || 'Course status changed successfully.',
                                toast: true,
                                position: 'top-end',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }
                        // Refresh current list to reflect counter & badge changes
                        fetchCourses();
                    },
                    error: function(xhr) {
                        $select.val(previousStatus);
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to update course status.'
                            });
                        }
                    }
                });
            });

            // Approve Course SweetAlert Modal
            $(document).on('click', '.approve-course-btn', function() {
                const courseId = $(this).data('course-id');
                const courseTitle = $(this).data('course-title');

                Swal.fire({
                    title: 'Approve Course',
                    html: `
                        <p class="mb-2">Are you sure you want to approve and publish this course?</p>
                        <p class="text-dark font-weight-bold">"${courseTitle}"</p>
                        <textarea id="swal-approve-reason" class="form-control mt-2" placeholder="Enter approval note or reason (optional)" rows="3"></textarea>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fas fa-check mr-1"></i> Approve & Publish',
                    cancelButtonText: 'Cancel',
                    preConfirm: () => {
                        return {
                            reason: document.getElementById('swal-approve-reason').value
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const reason = result.value.reason;
                        const form = $('<form>', {
                            'method': 'POST',
                            'action': '{{ route('admin.courses.approve', ':id') }}'.replace(':id', courseId)
                        });
                        form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': '{{ csrf_token() }}' }));
                        if (reason) {
                            form.append($('<input>', { 'type': 'hidden', 'name': 'reason', 'value': reason }));
                        }
                        $('body').append(form);
                        form.submit();
                    }
                });
            });

            // Reject Course SweetAlert Modal
            $(document).on('click', '.reject-course-btn', function() {
                const courseId = $(this).data('course-id');
                const courseTitle = $(this).data('course-title');

                Swal.fire({
                    title: 'Reject Course',
                    html: `
                        <p class="mb-2">Send this course back to Draft status?</p>
                        <p class="text-dark font-weight-bold">"${courseTitle}"</p>
                        <textarea id="swal-reject-reason" class="form-control mt-2" placeholder="Enter rejection reason for the instructor" rows="3"></textarea>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f59e0b',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fas fa-times mr-1"></i> Reject to Draft',
                    cancelButtonText: 'Cancel',
                    preConfirm: () => {
                        return {
                            reason: document.getElementById('swal-reject-reason').value
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const reason = result.value.reason;
                        const form = $('<form>', {
                            'method': 'POST',
                            'action': '{{ route('admin.courses.reject', ':id') }}'.replace(':id', courseId)
                        });
                        form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': '{{ csrf_token() }}' }));
                        if (reason) {
                            form.append($('<input>', { 'type': 'hidden', 'name': 'reason', 'value': reason }));
                        }
                        $('body').append(form);
                        form.submit();
                    }
                });
            });

            // Archive Course SweetAlert Modal
            $(document).on('click', '.archive-course-btn', function() {
                const courseId = $(this).data('course-id');
                const courseTitle = $(this).data('course-title');

                Swal.fire({
                    title: 'Archive Course',
                    text: `Are you sure you want to archive "${courseTitle}"? It will be hidden from the active catalog.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#334155',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fas fa-archive mr-1"></i> Yes, Archive',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = $('<form>', {
                            'method': 'POST',
                            'action': '{{ route('admin.courses.destroy', ':id') }}'.replace(':id', courseId)
                        });
                        form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': '{{ csrf_token() }}' }));
                        form.append($('<input>', { 'type': 'hidden', 'name': '_method', 'value': 'DELETE' }));
                        $('body').append(form);
                        form.submit();
                    }
                });
            });

            // 3-Step Secure Permanent Delete SweetAlert Modal
            $(document).on('click', '.full-delete-btn', function() {
                const courseId = $(this).data('course-id');
                const courseTitle = $(this).data('course-title');

                // Step 1: Warning
                Swal.fire({
                    title: 'PERMANENT DELETION',
                    html: `
                        <div class="alert alert-danger text-left small mb-3">
                            <strong>CRITICAL WARNING:</strong> This action cannot be reversed.
                            <ul class="mb-0 mt-2 pl-3">
                                <li>All Student Enrollments & Progress</li>
                                <li>Payment Records & Transaction References</li>
                                <li>Course Media (Videos, Documents, Slides)</li>
                                <li>All Modular Lessons & Topics</li>
                                <li>Student Reviews & Feedback</li>
                            </ul>
                        </div>
                        <p class="mb-0">Are you sure you want to permanently delete <strong>"${courseTitle}"</strong>?</p>
                    `,
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, proceed',
                    cancelButtonText: 'Cancel'
                }).then((res1) => {
                    if (res1.isConfirmed) {
                        // Step 2: Verification Input
                        Swal.fire({
                            title: 'Confirmation Required',
                            text: 'Type "DELETE" below to confirm permanent deletion:',
                            input: 'text',
                            inputPlaceholder: 'Type DELETE',
                            showCancelButton: true,
                            confirmButtonColor: '#dc2626',
                            cancelButtonColor: '#64748b',
                            confirmButtonText: 'Verify',
                            preConfirm: (val) => {
                                if (val !== 'DELETE') {
                                    Swal.showValidationMessage('You must type "DELETE" exactly.');
                                }
                                return val;
                            }
                        }).then((res2) => {
                            if (res2.isConfirmed) {
                                // Step 3: Final confirmation
                                Swal.fire({
                                    title: 'Final Confirmation',
                                    text: 'Immediate permanent wipe. Are you 100% certain?',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#dc2626',
                                    confirmButtonText: 'DELETE EVERYTHING',
                                    cancelButtonText: 'Stop, cancel'
                                }).then((res3) => {
                                    if (res3.isConfirmed) {
                                        const form = $('<form>', {
                                            'method': 'POST',
                                            'action': '{{ route('admin.courses.full-delete', ':id') }}'.replace(':id', courseId)
                                        });
                                        form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': '{{ csrf_token() }}' }));
                                        form.append($('<input>', { 'type': 'hidden', 'name': '_method', 'value': 'DELETE' }));
                                        $('body').append(form);
                                        form.submit();
                                    }
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
