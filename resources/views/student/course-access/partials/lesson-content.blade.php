<div class="lesson-content">
    <div class="mhq-courses-details-area">
        <div class="mhq-courses-details-wrapper">
            <div class="mhq-left-content">
                <h3 class="mb-4">
                    {{ $lesson->title }}
                    @if ($lesson->is_live)
                        <span class="badge bg-danger ms-2" style="font-size: 14px; vertical-align: middle;">
                            <i class="fas fa-broadcast-tower me-1"></i> {{ __('frontend.live') }}
                        </span>
                    @endif
                </h3>

                {{-- Live Class Banner (when lesson is linked to a live class) --}}
                @if ($videoData && ($videoData['is_live'] ?? false) && $videoData['live_class'])
                    @php $lc = $videoData['live_class']; @endphp

                    @if ($lc['status'] === 'live')
                        {{-- Currently Live --}}
                        <div style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border-radius: 16px; padding: 30px; color: #fff; margin-bottom: 25px; position: relative; overflow: hidden;">
                            <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; border-radius: 50%; background: rgba(255,255,255,0.08);"></div>
                            <div style="position: relative; z-index: 1;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                                    <span style="display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.2); padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase;">
                                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #fff; animation: livePulse 1.5s infinite;"></span>
                                        Live Now
                                    </span>
                                </div>
                                <h4 style="font-size: 20px; font-weight: 800; color: #fff; margin-bottom: 8px;">{{ $lc['title'] }}</h4>
                                <p style="opacity: 0.85; margin-bottom: 20px; font-size: 14px;">
                                    This session is currently live. Click below to join.
                                    @if ($lc['duration_minutes'])
                                        <span class="mx-2">•</span> Duration: {{ $lc['duration_minutes'] }} min
                                    @endif
                                </p>
                                <a href="{{ $lc['join_url'] }}" target="_blank" rel="noopener"
                                   style="display: inline-flex; align-items: center; gap: 8px; background: #fff; color: #dc3545; padding: 12px 28px; border-radius: 12px; font-weight: 700; font-size: 15px; text-decoration: none; box-shadow: 0 4px 15px rgba(0,0,0,0.15);">
                                    <i class="fas fa-video"></i> Join Live Session
                                </a>
                            </div>
                        </div>
                        <style>@keyframes livePulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }</style>

                    @elseif ($lc['status'] === 'scheduled')
                        {{-- Scheduled / Upcoming --}}
                        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 16px; padding: 30px; color: #fff; margin-bottom: 25px; position: relative; overflow: hidden; border-left: 4px solid #f99f1e;">
                            <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; border-radius: 50%; background: rgba(255,255,255,0.06);"></div>
                            <div style="position: relative; z-index: 1;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                                    <span style="background: rgba(255,255,255,0.12); padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase;">
                                        <i class="fas fa-clock me-1" style="color: #f99f1e;"></i> Scheduled Live Class
                                    </span>
                                </div>
                                <h4 style="font-size: 20px; font-weight: 800; color: #fff; margin-bottom: 8px;">{{ $lc['title'] }}</h4>
                                <p style="opacity: 0.85; margin-bottom: 20px; font-size: 14px;">
                                    <i class="fas fa-calendar me-1"></i> {{ $lc['scheduled_at_formatted'] }}
                                    @if ($lc['duration_minutes'])
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-hourglass-half me-1"></i> {{ $lc['duration_minutes'] }} min
                                    @endif
                                </p>
                                <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                                    @if ($lc['minutes_until'] <= 30 && $lc['minutes_until'] > 0)
                                        <a href="{{ $lc['join_url'] }}" target="_blank" rel="noopener"
                                           style="display: inline-flex; align-items: center; gap: 8px; background: #fff; color: #0f172a; padding: 10px 24px; border-radius: 10px; font-weight: 700; text-decoration: none;">
                                            <i class="fas fa-video"></i> Join Early
                                        </a>
                                    @endif
                                    <div style="background: rgba(255,255,255,0.12); padding: 8px 18px; border-radius: 10px; text-align: center;">
                                        <div style="font-size: 10px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.8;">Starts in</div>
                                        <div style="font-size: 18px; font-weight: 800;">
                                            @if ($lc['minutes_until'] > 1440)
                                                {{ floor($lc['minutes_until'] / 1440) }}d {{ floor(($lc['minutes_until'] % 1440) / 60) }}h
                                            @elseif ($lc['minutes_until'] > 60)
                                                {{ floor($lc['minutes_until'] / 60) }}h {{ $lc['minutes_until'] % 60 }}m
                                            @elseif ($lc['minutes_until'] > 0)
                                                {{ $lc['minutes_until'] }}m
                                            @else
                                                Starting soon...
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @elseif ($lc['status'] === 'ended')
                        {{-- Ended – show recording if available, otherwise info banner --}}
                        @if ($videoData['url'])
                            {{-- Recording available – render the normal video player below --}}
                            <div style="background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px 20px; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-video" style="color: #0f172a;"></i>
                                <span style="font-size: 14px; color: #0f172a;">
                                    <strong>Live session ended.</strong> Watch the recording below.
                                </span>
                            </div>
                        @else
                            <div style="background: #f5f5f5; border-radius: 12px; padding: 25px; text-align: center; margin-bottom: 20px;">
                                <i class="fas fa-video-slash" style="font-size: 36px; color: #999; margin-bottom: 10px;"></i>
                                <p style="color: #666; margin: 0;">This live session has ended. No recording is available.</p>
                            </div>
                        @endif

                    @elseif ($lc['status'] === 'cancelled')
                        <div style="background: #fce4ec; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-times-circle" style="color: #c62828; font-size: 20px;"></i>
                                <span style="font-weight: 600; color: #c62828;">This live class has been cancelled.</span>
                            </div>
                        </div>
                    @endif

                    {{-- Only render video player if ended + has recording --}}
                    @if ($lc['status'] === 'ended' && !empty($videoData['url']))
                        @if ($videoData['type'] === 'url' && (!empty($videoData['is_youtube']) || !empty($videoData['is_vimeo'])))
                            <div id="video-player-container" class="mz-external-embed-wrap" data-embed-src="{{ $videoData['embed_url'] ?? $videoData['url'] }}">
                                <iframe width="100%" height="500" src="about:blank" data-embed-src="{{ $videoData['embed_url'] ?? $videoData['url'] }}" frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen class="video-iframe mz-deferred-embed-iframe d-none"></iframe>
                                <div class="mz-embed-offline-card d-none p-5 text-center rounded-4" style="background: #0f172a; color: #f8fafc; border: 1px solid #1e293b;">
                                    <i class="fas fa-wifi-slash fa-2x mb-3" style="color: #f99f1e;"></i>
                                    <h5 class="fw-bold text-white mb-2">Offline Study Mode</h5>
                                    <p class="small text-white-50 mb-3">External video streaming is paused while your browser is offline. You can continue studying lesson notes and navigating modules below.</p>
                                    <button type="button" class="btn btn-sm btn-light fw-semibold px-3" onclick="window.maarifazoneMountExternalEmbeds && window.maarifazoneMountExternalEmbeds(true)">
                                        <i class="fas fa-sync-alt me-1"></i> Retry Video Stream
                                    </button>
                                </div>
                            </div>
                        @else
                            <div id="video-player-container">
                                <video id="lesson-video-player" class="video-js vjs-default-skin" controls preload="auto"
                                    data-setup='{}'>
                                    <source src="{{ $videoData['url'] }}" type="video/mp4">
                                </video>
                            </div>
                        @endif
                    @endif

                @else
                    {{-- Normal (non-live) lesson video --}}
                    @if ($videoData && ($videoData['type'] ?? null) === 'url' && !empty($videoData['url']))
                        @if (!empty($videoData['is_youtube']) || !empty($videoData['is_vimeo']))
                            <div id="video-player-container" class="mz-external-embed-wrap" data-embed-src="{{ $videoData['embed_url'] ?? $videoData['url'] }}">
                                <iframe width="100%" height="500" src="about:blank" data-embed-src="{{ $videoData['embed_url'] ?? $videoData['url'] }}" frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen class="video-iframe mz-deferred-embed-iframe d-none"></iframe>
                                <div class="mz-embed-offline-card d-none p-5 text-center rounded-4" style="background: #0f172a; color: #f8fafc; border: 1px solid #1e293b;">
                                    <i class="fas fa-wifi-slash fa-2x mb-3" style="color: #f99f1e;"></i>
                                    <h5 class="fw-bold text-white mb-2">Offline Study Mode</h5>
                                    <p class="small text-white-50 mb-3">External video streaming is paused while your browser is offline. You can continue studying lesson notes and navigating modules below.</p>
                                    <button type="button" class="btn btn-sm btn-light fw-semibold px-3" onclick="window.maarifazoneMountExternalEmbeds && window.maarifazoneMountExternalEmbeds(true)">
                                        <i class="fas fa-sync-alt me-1"></i> Retry Video Stream
                                    </button>
                                </div>
                            </div>
                        @else
                            <div id="video-player-container">
                                <video id="lesson-video-player" class="video-js vjs-default-skin" controls preload="auto"
                                    data-setup='{}'>
                                    <source src="{{ $videoData['url'] }}" type="video/mp4">
                                </video>
                            </div>
                        @endif
                    @elseif($videoData && ($videoData['type'] ?? null) === 'upload' && !empty($videoData['url']))
                        <div id="video-player-container">
                            <video id="lesson-video-player" class="video-js vjs-default-skin" controls preload="auto"
                                data-setup='{}'>
                                <source src="{{ $videoData['url'] }}" type="video/mp4">
                            </video>
                        </div>
                    @endif
                @endif

                @if ($videoData && ($videoData['url'] || ($videoData['embed_url'] ?? null)))
                    @php
                        $isExternalEmbed = !empty($videoData['is_youtube']) || !empty($videoData['is_vimeo']);
                    @endphp
                    <!-- v3.0.0 Enhanced Video Player Controls Bar -->
                    <div class="maarifazone-video-toolbar d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2 mb-3 px-3 py-2 rounded-3" style="background:#0f172a;color:#f8fafc;font-size:13px;">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            @if (!$isExternalEmbed)
                                <span class="text-white-50 small fw-semibold"><i class="fas fa-sliders-h me-1"></i> Player Controls:</span>
                                <button type="button" class="btn btn-sm btn-outline-light py-1 px-2" style="font-size:12px;" onclick="window.maarifazoneVideoSeek && window.maarifazoneVideoSeek(-10)" title="Rewind 10s">
                                    <i class="fas fa-undo-alt"></i> -10s
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-light py-1 px-2" style="font-size:12px;" onclick="window.maarifazoneVideoSeek && window.maarifazoneVideoSeek(10)" title="Forward 10s">
                                    <i class="fas fa-redo-alt"></i> +10s
                                </button>
                                <div class="d-inline-flex align-items-center gap-1 ms-1">
                                    <label for="maarifazone-speed-select" class="text-white-50 small mb-0">Speed:</label>
                                    <select id="maarifazone-speed-select" class="form-select form-select-sm bg-dark text-white border-secondary py-0 px-2" style="width:auto;font-size:12px;height:26px;" onchange="window.maarifazoneSetVideoSpeed && window.maarifazoneSetVideoSpeed(this.value)">
                                        <option value="0.5">0.5x</option>
                                        <option value="0.75">0.75x</option>
                                        <option value="1" selected>1.0x (Normal)</option>
                                        <option value="1.25">1.25x</option>
                                        <option value="1.5">1.5x</option>
                                        <option value="2">2.0x</option>
                                    </select>
                                </div>
                            @else
                                <span class="text-white-50 small fw-semibold"><i class="fas fa-video me-1"></i> {{ !empty($videoData['is_youtube']) ? 'YouTube Player' : 'Vimeo Player' }}</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if (!$isExternalEmbed)
                                <button type="button" class="btn btn-sm btn-outline-light py-1 px-2" style="font-size:12px;" onclick="window.maarifazoneTogglePiP && window.maarifazoneTogglePiP()" title="Picture-in-Picture">
                                    <i class="fas fa-clone me-1"></i> PiP
                                </button>
                            @endif
                            <button type="button" class="btn btn-sm btn-outline-light py-1 px-2" style="font-size:12px;" onclick="window.maarifazoneToggleTheater && window.maarifazoneToggleTheater()" title="Toggle Theater Mode (T)">
                                <i class="fas fa-expand-alt me-1"></i> Theater Mode
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Full Text / Article Lecture Reading Material --}}
                @if (!empty($lesson->content_text))
                    <div class="maarifazone-lesson-text-body p-4 my-4 rounded-3" style="background: #ffffff; border: 1px solid #e2e8f0; font-size: 16px; line-height: 1.8; color: #1e293b; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom text-muted" style="font-size: 13px;">
                            <i class="fa-regular fa-file-lines text-info"></i>
                            <span class="fw-semibold">Lesson Article &amp; Lecture Notes</span>
                        </div>
                        <div class="content-body" style="white-space: pre-wrap;">{{ $lesson->content_text }}</div>
                    </div>
                @endif

                {{-- Embedded PDF or Presentation / Downloadable Learning Materials --}}
                @if (!empty($lesson->attachment_path))
                    @php
                        $ext = strtolower(pathinfo($lesson->attachment_path, PATHINFO_EXTENSION));
                        $isPdf = $ext === 'pdf' || $lesson->lesson_type === 'pdf';
                        $isPpt = in_array($ext, ['ppt', 'pptx']) || $lesson->lesson_type === 'ppt';
                        $attachmentUrl = asset('storage/' . $lesson->attachment_path);
                        $attachmentTitle = $lesson->attachment_name ?: ($isPdf ? 'Lecture Notes (PDF)' : ($isPpt ? 'Presentation Slides' : 'Learning Material'));
                    @endphp

                    @if ($isPdf)
                        <div class="pdf-viewer-wrapper my-4">
                            <div class="d-flex align-items-center justify-content-between p-3 rounded-top" style="background: #0f172a; color: #ffffff;">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-file-pdf text-danger fa-lg"></i>
                                    <span class="fw-bold">{{ $attachmentTitle }}</span>
                                </div>
                                <a href="{{ $attachmentUrl }}" target="_blank" download="{{ $lesson->attachment_name ?: 'document.pdf' }}" class="btn btn-sm btn-outline-light">
                                    <i class="fa-solid fa-download me-1"></i> Download PDF
                                </a>
                            </div>
                            <iframe src="{{ $attachmentUrl }}" width="100%" height="650" style="border: 1px solid #0f172a; border-top: none; border-radius: 0 0 8px 8px;"></iframe>
                        </div>
                    @else
                        <div class="card p-4 my-4 rounded-3 border" style="background: #f8fafc;">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width: 48px; height: 48px; border-radius: 10px; background: #e2e8f0; display: flex; align-items: center; justify-content: center;">
                                        @if ($isPpt)
                                            <i class="fa-solid fa-file-powerpoint text-warning fa-2x"></i>
                                        @else
                                            <i class="fa-solid fa-file-arrow-down text-primary fa-2x"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <h5 class="mb-1 fw-bold text-dark">{{ $attachmentTitle }}</h5>
                                        <small class="text-muted">Format: .{{ strtoupper($ext ?: 'FILE') }} &bull; Maarifazone course resource</small>
                                    </div>
                                </div>
                                <a href="{{ $attachmentUrl }}" target="_blank" download="{{ $lesson->attachment_name ?: 'material' }}" class="btn btn-primary px-3 py-2 fw-semibold">
                                    <i class="fa-solid fa-download me-1"></i> Download File
                                </a>
                            </div>
                        </div>
                    @endif
                @endif

                @if ($lesson->description)
                    <div class="description-content mt-4">
                        <h3 class="mb-3">{{ __('student.description') }}</h3>
                        <div class="lesson-description-content">{!! $lesson->description !!}</div>
                    </div>
                @endif

                <div class="lesson-actions mt-4">
                    <button id="mark-complete-btn" class="btn btn-primary px-4 py-2 fw-semibold rounded-pill" data-lesson-id="{{ $lesson->id }}"
                        data-course-id="{{ $lesson->topic->course_id }}"
                        style="background: {{ $progress && $progress->is_completed ? '#059669' : '#0f172a' }}; border-color: {{ $progress && $progress->is_completed ? '#059669' : '#0f172a' }}; color: #ffffff;"
                        {!! $progress && $progress->is_completed ? 'disabled style="background: #059669; border-color: #059669; color: #fff; opacity: 0.85; cursor: not-allowed;"' : '' !!}>
                        @if ($progress && $progress->is_completed)
                            {{ __('student.completed_status') }} <i class="fas fa-check-circle ms-1"></i>
                        @else
                            {{ __('student.mark_as_complete') }} <i class="fas fa-check ms-1"></i>
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
