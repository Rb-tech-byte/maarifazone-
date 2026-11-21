<!-- Search Bar -->
<div class="sidebar-search">
    <div class="search-input-wrapper">
        <input type="text" id="search-input" class="search-input" placeholder="{{ __('student.search_course_content') }}">
        <button type="button" class="search-icon-btn">
            <i class="fas fa-search"></i>
        </button>
    </div>
    <div id="search-results" class="search-results" style="display: none;"></div>
</div>

<!-- Course Curriculum -->
<div class="course-curriculum">
    @foreach ($topicsData as $topicData)
        <div class="chapter-section">
            <div class="chapter-header" data-chapter="{{ $topicData['index'] + 1 }}">
                <div class="chapter-header-left">
                    <i class="fas fa-chevron-down chapter-toggle-icon"></i>
                    <span class="chapter-number">{{ __('student.topic') }} {{ $topicData['index'] + 1 }}</span>
                    <span class="chapter-title">{{ $topicData['topic']->title }}</span>
                    @if ($topicData['is_free'] ?? false)
                        <span class="mz-topic-pill mz-pill-free">Free</span>
                    @elseif ($topicData['is_topic_unlocked'] ?? false)
                        <span class="mz-topic-pill mz-pill-owned"><i class="fas fa-unlock"></i> Unlocked</span>
                    @else
                        <a href="{{ route('courses.checkout', ['id' => $course->id, 'topic_id' => $topicData['topic']->id]) }}"
                           class="mz-topic-pill mz-pill-paid" onclick="event.stopPropagation();"
                           title="Pay for this topic only">
                            <i class="fas fa-lock"></i> {{ $course->formatPrice($topicData['price'] ?? 0) }}
                        </a>
                    @endif
                </div>
                <span
                    class="chapter-progress">{{ $topicData['progress']['completed_items'] }}/{{ $topicData['progress']['total_items'] }}</span>
            </div>
            <div class="chapter-content">
                @foreach ($topicData['items'] as $item)
                    <div class="curriculum-item {{ $item['type'] }}-item {{ $item['is_active'] ? 'active' : '' }} {{ !$item['is_accessible'] ? 'locked' : '' }}"
                        data-item-id="{{ $item['id'] }}" data-item-type="{{ $item['type'] }}"
                        data-course-id="{{ $course->id }}"
                        data-accessible="{{ $item['is_accessible'] ? '1' : '0' }}">
                        <div class="curriculum-item-icon">
                            @if (!$item['is_accessible'])
                                <i class="fas fa-lock"></i>
                            @elseif($item['type'] === 'lesson' && ($item['is_live'] ?? false) && ($item['live_status'] ?? null) === 'live')
                                <span style="display: inline-flex; align-items: center; justify-content: center; width: 20px; height: 20px;">
                                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #dc3545; animation: liveDot 1.5s infinite;"></span>
                                </span>
                            @elseif($item['type'] === 'lesson' && ($item['is_live'] ?? false))
                                <i class="fas fa-broadcast-tower" style="color: #dc3545; font-size: 12px;"></i>
                            @elseif($item['type'] === 'lesson')
                                @php
                                    $lType = $item['lesson_type'] ?? 'video';
                                    $icon = match($lType) {
                                        'text' => 'fa-file-lines text-info',
                                        'pdf' => 'fa-file-pdf text-danger',
                                        'ppt' => 'fa-file-powerpoint text-warning',
                                        'attachment' => 'fa-paperclip text-secondary',
                                        'live' => 'fa-broadcast-tower text-danger',
                                        default => 'fa-play text-primary',
                                    };
                                @endphp
                                <i class="fas {{ $icon }}"></i>
                            @else
                                <i class="fas fa-file-alt"></i>
                            @endif
                        </div>
                        <div class="curriculum-item-content">
                            <span class="curriculum-item-title">{{ $item['title'] }}</span>
                            @if (!$item['is_accessible'])
                                <span class="mz-lesson-tag mz-tag-paid">Paid module</span>
                            @elseif (!empty($item['is_free']) && empty($topicData['is_topic_unlocked']))
                                <span class="mz-lesson-tag mz-tag-free">Free preview</span>
                            @endif
                            @if ($item['duration_formatted'])
                                <span class="curriculum-item-duration">{{ $item['duration_formatted'] }}</span>
                            @endif
                        </div>
                        <div class="curriculum-item-status">
                            @if ($item['is_completed'])
                                <i class="fas fa-check-circle status-completed"></i>
                            @elseif(!$item['is_accessible'])
                                <i class="fas fa-lock status-locked"></i>
                            @else
                                <div class="status-circle"></div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
<style>
    .mz-topic-pill { display: inline-flex; align-items: center; gap: 4px; margin-left: 6px; padding: 2px 8px; border-radius: 999px; font-size: 10.5px; font-weight: 600; white-space: nowrap; text-decoration: none; }
    .mz-pill-free { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .mz-pill-owned { background: #f1f5f9; color: #0f172a; border: 1px solid #cbd5e1; }
    .mz-pill-paid { background: #0f172a; color: #fff !important; border: 1px solid #0f172a; }
    .mz-pill-paid:hover { background: #1e293b; }
    .mz-lesson-tag { display: inline-block; margin-top: 2px; padding: 1px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; }
    .mz-tag-paid { background: #fef3c7; color: #92400e; }
    .mz-tag-free { background: #ecfdf5; color: #047857; }
    .curriculum-item.locked { cursor: pointer; opacity: 0.85; }
    @keyframes liveDot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(0.8); }
    }
</style>
