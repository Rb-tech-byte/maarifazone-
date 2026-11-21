<div class="mz-locked-module-wrapper p-4 p-md-5 text-center" style="max-width: 850px; margin: 40px auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08);">
    {{-- Padlock Icon with Glow --}}
    <div class="mb-4">
        <div class="d-inline-flex align-items-center justify-content-center rounded-circle"
             style="width: 80px; height: 80px; background: #fef3c7; color: #d97706; box-shadow: 0 0 0 12px rgba(254, 243, 199, 0.5);">
            <i class="fas fa-lock fa-3x"></i>
        </div>
    </div>

    {{-- Topic & Lesson Header --}}
    <div class="mb-3">
        <span class="badge badge-warning text-dark px-3 py-1 font-weight-bold" style="font-size: 12px; border-radius: 20px;">
            <i class="fas fa-lock mr-1"></i> Premium Paid Module
        </span>
    </div>

    <h2 class="text-dark font-weight-bold mb-2" style="font-size: 26px;">
        {{ $lesson->title }}
    </h2>

    <p class="text-muted mb-4" style="font-size: 15px;">
        This lesson is part of <strong class="text-dark">{{ $topic->title ?? 'this course topic' }}</strong>.
        To access this video lecture, reading materials, and resources, please choose an unlock option below.
    </p>

    {{-- Unlock Option Cards --}}
    <div class="row text-left my-4">
        {{-- Option 1: Unlock Topic Only --}}
        <div class="col-12 col-md-6 mb-3 mb-md-0">
            <div class="card h-100 border transition-all" style="border-radius: 12px; border-color: #cbd5e1 !important; background: #f8fafc;">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge badge-info px-2 py-1" style="font-size: 11px;">Topic Modular Access</span>
                            <i class="fas fa-layer-group text-primary"></i>
                        </div>
                        <h5 class="font-weight-bold text-dark mb-1">Unlock This Topic Only</h5>
                        <p class="text-muted small mb-3">
                            Gain full lifetime access to all lessons and materials inside <em>{{ Str::limit($topic->title, 40) }}</em>.
                        </p>
                        <div class="h3 font-weight-bold text-dark mb-3">
                            {{ $course->formatPrice($topic->price > 0 ? $topic->price : ($lesson->price > 0 ? $lesson->price : 0)) }}
                        </div>
                    </div>
                    <a href="{{ route('courses.checkout', ['id' => $course->id, 'topic_id' => $topic->id]) }}"
                       class="btn btn-primary btn-block py-2 font-weight-bold"
                       style="background: #0f172a; border-color: #0f172a; border-radius: 8px;">
                        <i class="fas fa-unlock-alt mr-1"></i> Unlock This Topic Only
                    </a>
                </div>
            </div>
        </div>

        {{-- Option 2: Unlock Entire Course --}}
        <div class="col-12 col-md-6">
            <div class="card h-100 border transition-all position-relative overflow-hidden"
                 style="border-radius: 12px; border: 2px solid #0f172a !important; background: #ffffff; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);">
                <div class="position-absolute bg-warning text-dark font-weight-bold px-3 py-1"
                     style="top: 0; right: 0; font-size: 10px; border-bottom-left-radius: 8px;">
                    BEST VALUE
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge badge-dark px-2 py-1" style="font-size: 11px; background: #0f172a;">Full Course Access</span>
                            <i class="fas fa-graduation-cap text-warning"></i>
                        </div>
                        <h5 class="font-weight-bold text-dark mb-1">Unlock All Topics</h5>
                        <p class="text-muted small mb-3">
                            Complete access to all {{ $course->topics->count() }} modules, videos, study notes, and completion recognition.
                        </p>
                        <div class="h3 font-weight-bold text-dark mb-3">
                            {{ $course->formatPrice($course->sale_price ?: ($course->regular_price ?? 0)) }}
                            @if ($course->sale_price && $course->regular_price > $course->sale_price)
                                <small class="text-muted text-decoration-line-through ml-1" style="font-size: 14px;">
                                    {{ $course->formatPrice($course->regular_price) }}
                                </small>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('courses.checkout', $course->id) }}"
                       class="btn btn-warning text-dark btn-block py-2 font-weight-bold"
                       style="border-radius: 8px;">
                        <i class="fas fa-bolt mr-1"></i> Unlock Complete Course
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Trust / Help Footnote --}}
    <div class="text-muted small pt-2 border-top">
        <i class="fas fa-shield-alt text-success mr-1"></i> Secure direct checkout &bull; Instant automatic course activation &bull; Continuous learning support
    </div>
</div>

