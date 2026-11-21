<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseTopic;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Verifies the Scholar learning player honours modular pay-per-topic access.
 */
class ScholarPayPerTopicLearningTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $scholar;
    private Course $course;
    private CourseTopic $freeTopic;
    private CourseTopic $paidTopic;
    private Lesson $freeLesson;
    private Lesson $paidLesson;

    protected function setUp(): void
    {
        parent::setUp();
        URL::forceRootUrl('http://localhost');
        $this->seed(RoleSeeder::class);
        $this->seed(AdminSeeder::class);

        $this->admin = $this->makeUser('admin');
        $this->scholar = $this->makeUser('student');
        $instructor = $this->makeUser('instructor');

        $this->course = Course::create([
            'instructor_id'  => $instructor->id,
            'title'          => 'Pay Per Topic ' . Str::random(5),
            'course_type'    => 'course',
            'delivery_mode'  => 'online',
            'pricing_model'  => 'paid',
            'price_currency' => 'TZS',
            'regular_price'  => 1700000,
            'status'         => 'published',
            'visibility'     => 'public',
        ]);

        $this->freeTopic = CourseTopic::create(['course_id' => $this->course->id, 'title' => 'Free Foundations', 'is_free' => true, 'price' => 0, 'order' => 0]);
        $this->paidTopic = CourseTopic::create(['course_id' => $this->course->id, 'title' => 'Paid Advanced Module', 'is_free' => false, 'price' => 950000, 'order' => 1]);

        $this->freeLesson = Lesson::create(['course_topic_id' => $this->freeTopic->id, 'title' => 'Free Lesson', 'lesson_type' => 'text', 'content_text' => 'Open notes', 'order' => 0]);
        $this->paidLesson = Lesson::create(['course_topic_id' => $this->paidTopic->id, 'title' => 'Paid Lesson', 'lesson_type' => 'text', 'content_text' => 'Premium notes', 'order' => 0]);
    }

    private function makeUser(string $role): User
    {
        $user = User::create([
            'name'                => ucfirst($role) . ' ' . Str::random(4),
            'email'               => $role . '-' . Str::random(8) . '@maarifazone.com',
            'password'            => Hash::make('Secret@123'),
            'role'                => $role,
            'is_approved'         => true,
            'password_changed_at' => now(),
        ]);
        $user->assignRole($role === 'student' ? 'student' : $role);

        return $user;
    }

    private function enrollTopic(?int $topicId): Enrollment
    {
        return Enrollment::create([
            'user_id'         => $this->scholar->id,
            'course_id'       => $this->course->id,
            'course_topic_id' => $topicId,
            'status'          => Enrollment::STATUS_APPROVED,
            'enrolled_at'     => now(),
        ]);
    }

    public function test_topic_only_scholar_sees_paywall_for_unpaid_topic(): void
    {
        $this->enrollTopic($this->freeTopic->id);

        // Free topic lesson loads normally
        $free = $this->actingAs($this->scholar)->postJson(route('student.courses.lesson.load', $this->course->id), [
            'lesson_id' => $this->freeLesson->id,
            'course_id' => $this->course->id,
        ]);
        $free->assertOk()->assertJson(['success' => true]);
        $this->assertFalse((bool) $free->json('locked'));

        // Paid topic lesson returns the unlock panel with checkout links
        $paid = $this->actingAs($this->scholar)->postJson(route('student.courses.lesson.load', $this->course->id), [
            'lesson_id' => $this->paidLesson->id,
            'course_id' => $this->course->id,
        ]);
        $paid->assertOk()->assertJson(['success' => true, 'locked' => true]);
        $this->assertStringContainsString('Unlock This Topic Only', $paid->json('html'));
        $this->assertStringContainsString('topic_id=' . $this->paidTopic->id, $paid->json('checkout_topic_url'));

        // Direct URL to paid lesson renders the paywall, not the lesson
        $page = $this->actingAs($this->scholar)->get(route('student.courses.access', [
            'courseId'  => $this->course->id,
            'item_type' => 'lesson',
            'item_id'   => $this->paidLesson->id,
        ]));
        $page->assertOk();
        $page->assertSee('Unlock This Topic Only');
        $page->assertSee('Unlock Complete Course');
        $page->assertDontSee('Premium notes');
    }

    public function test_buying_the_topic_unlocks_its_lessons(): void
    {
        $this->enrollTopic($this->freeTopic->id);
        $this->enrollTopic($this->paidTopic->id);

        $res = $this->actingAs($this->scholar)->postJson(route('student.courses.lesson.load', $this->course->id), [
            'lesson_id' => $this->paidLesson->id,
            'course_id' => $this->course->id,
        ]);
        $res->assertOk()->assertJson(['success' => true]);
        $this->assertFalse((bool) $res->json('locked'));
        $this->assertStringContainsString('Premium notes', $res->json('html'));
    }

    public function test_full_course_scholar_accesses_every_topic(): void
    {
        $this->enrollTopic(null);

        $page = $this->actingAs($this->scholar)->get(route('student.courses.access', [
            'courseId'  => $this->course->id,
            'item_type' => 'lesson',
            'item_id'   => $this->paidLesson->id,
        ]));
        $page->assertOk();
        $page->assertDontSee('Unlock This Topic Only');
    }

    public function test_admin_course_details_show_topic_pricing(): void
    {
        $this->enrollTopic($this->freeTopic->id);

        $res = $this->actingAs($this->admin)->get(route('admin.courses.show', $this->course->id));
        $res->assertOk();
        $res->assertSee('Free Topic');
        $res->assertSee('Paid topics');
        $res->assertSee('Topic-only scholars');
    }

    public function test_next_and_previous_navigation_works_into_paid_topic_and_student_courses_route_works(): void
    {
        $this->enrollTopic($this->freeTopic->id);

        // Clicking Next from the free lesson into the paid topic returns item_id without 403 error
        $nextRes = $this->actingAs($this->scholar)->getJson(route('student.courses.item.next', [
            'courseId' => $this->course->id,
            'itemId'   => $this->freeLesson->id,
        ]));
        $nextRes->assertOk()
            ->assertJson([
                'success'       => true,
                'item_id'       => $this->paidLesson->id,
                'type'          => 'lesson',
                'is_accessible' => false,
            ]);

        // Clicking Previous from the paid lesson back to the free lesson returns item_id
        $prevRes = $this->actingAs($this->scholar)->getJson(route('student.courses.item.previous', [
            'courseId' => $this->course->id,
            'itemId'   => $this->paidLesson->id,
        ]));
        $prevRes->assertOk()
            ->assertJson([
                'success'       => true,
                'item_id'       => $this->freeLesson->id,
                'type'          => 'lesson',
                'is_accessible' => true,
            ]);

        // Both /student/courses and /student/my-courses work with Midnight Slate theme
        $this->actingAs($this->scholar)
            ->get('/student/courses')
            ->assertOk()
            ->assertSee($this->course->title)
            ->assertSee('#0f172a', false);
    }
}

