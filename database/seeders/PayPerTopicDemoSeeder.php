<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseTopic;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo data for the modular pay-per-topic learning model.
 *
 * Course 45 ("Agile Product Management & Tech Leadership Essentials"):
 *  - Full course: TSH 1,700,000 (sale TSH 1,500,000)
 *  - Module 1: FREE topic (everyone enrolled can learn it)
 *  - Module 2: PAID topic  TSH 950,000
 *  - Module 3: PAID topic  TSH 650,000
 *
 * scholar@maarifazone.com is switched to a topic-only (Module 1) enrollment so
 * the locked / pay-per-topic flow can be tested in the Scholar Portal.
 *
 * Run: php artisan db:seed --class=PayPerTopicDemoSeeder
 */
class PayPerTopicDemoSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::with('topics.lessons')->find(45);
        if (! $course) {
            $this->command?->warn('Course 45 not found - skipping pay-per-topic demo.');
            return;
        }

        $course->update([
            'pricing_model'  => 'paid',
            'price_currency' => 'TZS',
            'regular_price'  => 1700000,
            'sale_price'     => 1500000,
            'status'         => 'published',
            'visibility'     => 'public',
        ]);

        $topics = $course->topics->sortBy('order')->values();

        // Module 1 - free foundations
        if ($first = $topics->get(0)) {
            $first->update(['is_free' => true, 'price' => 0]);
        }

        // Module 2 - paid
        if ($second = $topics->get(1)) {
            $second->update(['is_free' => false, 'price' => 950000]);
            $second->lessons()->update(['is_free' => false, 'is_preview' => false, 'price' => 0]);
        }

        // Module 3 - paid (created if missing)
        $third = $topics->get(2) ?? CourseTopic::create([
            'course_id'   => $course->id,
            'title'       => 'Module 3: Product Strategy, Roadmaps & Stakeholder Leadership',
            'description' => 'Turn product vision into roadmaps, align stakeholders and lead cross-functional tech teams.',
            'order'       => ($topics->max('order') ?? 1) + 1,
            'is_free'     => false,
            'price'       => 650000,
        ]);
        $third->update(['is_free' => false, 'price' => 650000]);

        if ($third->lessons()->count() === 0) {
            Lesson::create([
                'course_topic_id' => $third->id,
                'title'           => 'Building Outcome-Driven Product Roadmaps',
                'lesson_type'     => 'video',
                'video_type'      => 'url',
                'video_url'       => 'https://www.youtube.com/watch?v=502ILHjX9EE',
                'duration'        => 18,
                'is_free'         => false,
                'is_preview'      => false,
                'price'           => 0,
                'order'           => 0,
            ]);
            Lesson::create([
                'course_topic_id' => $third->id,
                'title'           => 'Stakeholder Alignment Playbook (Reading Notes)',
                'lesson_type'     => 'text',
                'content_text'    => '<h3>Stakeholder Alignment Playbook</h3><p>Map stakeholders by influence and interest, agree on measurable outcomes, and run a fortnightly roadmap review to keep commitments transparent.</p><ul><li>Identify decision makers early</li><li>Share trade-offs, not just features</li><li>Publish a single source of truth for priorities</li></ul>',
                'duration'        => 10,
                'is_free'         => false,
                'is_preview'      => false,
                'price'           => 0,
                'order'           => 1,
            ]);
        }

        // Demo scholar: topic-only enrollment (Module 1) to exercise the paywall
        $scholar = User::where('email', 'scholar@maarifazone.com')->first();
        if ($scholar && $first) {
            $enrollment = Enrollment::where('user_id', $scholar->id)->where('course_id', $course->id)->first();
            if ($enrollment) {
                $enrollment->update(['course_topic_id' => $first->id, 'status' => Enrollment::STATUS_APPROVED]);
            } else {
                Enrollment::create([
                    'user_id'         => $scholar->id,
                    'course_id'       => $course->id,
                    'course_topic_id' => $first->id,
                    'status'          => Enrollment::STATUS_APPROVED,
                    'enrolled_at'     => now(),
                ]);
            }
        }

        $this->command?->info('Pay-per-topic demo applied to course 45.');
    }
}

