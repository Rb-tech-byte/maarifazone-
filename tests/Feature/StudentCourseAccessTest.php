<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseTopic;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * End-to-end verification of the Maarifazone Scholar Course Player:
 * 100% online courses, lesson playback, progress tracking, discussions,
 * and verification that quizzes, assignments, and certificates are dropped.
 *
 * @author Maarifazone Team
 */
class StudentCourseAccessTest extends TestCase
{
    use DatabaseTransactions;

    private User $instructor;

    private User $student;

    private Course $course;

    private Enrollment $enrollment;

    private Lesson $lesson1;

    private Lesson $lesson2;

    protected function setUp(): void
    {
        parent::setUp();

        URL::forceRootUrl('http://localhost');

        $this->instructor = User::create([
            'name'                => 'Course Access Instructor',
            'email'               => 'ca-inst-' . Str::random(6) . '@maarifazone.com',
            'password'            => Hash::make('Secret@123'),
            'role'                => 'instructor',
            'is_approved'         => true,
            'password_changed_at' => now(),
        ]);

        $this->student = User::create([
            'name'                => 'Course Access Scholar',
            'email'               => 'ca-stud-' . Str::random(6) . '@maarifazone.com',
            'password'            => Hash::make('Secret@123'),
            'role'                => 'student',
            'is_approved'         => true,
            'password_changed_at' => now(),
        ]);

        $this->course = Course::create([
            'title'         => 'Software Architecture Practicum ' . Str::random(4),
            'course_type'   => 'course',
            'delivery_mode' => 'online',
            'description'   => 'Course player integration test course',
            'instructor_id' => $this->instructor->id,
            'pricing_model' => 'free',
            'status'        => 'published',
            'visibility'    => 'public',
        ]);

        $topic = CourseTopic::create([
            'course_id' => $this->course->id,
            'title'     => 'Module 1: Foundations',
            'order'     => 1,
        ]);

        $this->lesson1 = Lesson::create([
            'course_topic_id' => $topic->id,
            'title'           => 'Lesson 1: Domain Driven Design',
            'description'     => 'Introductory lecture on bounded contexts.',
            'video_type'      => 'url',
            'video_url'       => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duration'        => 600,
            'order'           => 1,
        ]);

        $this->lesson2 = Lesson::create([
            'course_topic_id' => $topic->id,
            'title'           => 'Lesson 2: Event Driven Architecture',
            'description'     => 'Deep dive into asynchronous messaging.',
            'video_type'      => 'url',
            'video_url'       => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duration'        => 720,
            'order'           => 2,
        ]);

        $this->enrollment = Enrollment::create([
            'user_id'      => $this->student->id,
            'course_id'    => $this->course->id,
            'status'       => Enrollment::STATUS_APPROVED,
            'requested_at' => now(),
            'approved_at'  => now(),
        ]);
    }

    public function test_course_access_loads_first_lesson_and_renders_cleanly(): void
    {
        $response = $this->actingAs($this->student)
            ->get(route('student.courses.access', $this->course->id));

        $response->assertStatus(200);
        $response->assertSee($this->lesson1->title, false);
        $response->assertSee('youtube.com/embed/dQw4w9WgXcQ', false);

        // Initial visit initializes LessonProgress automatically
        $this->assertDatabaseHas('lesson_progress', [
            'user_id'       => $this->student->id,
            'lesson_id'     => $this->lesson1->id,
            'enrollment_id' => $this->enrollment->id,
            'is_completed'  => false,
        ]);
    }

    public function test_mark_complete_advances_scholar_through_lessons(): void
    {
        // Mark Lesson 1 as complete
        $completeRes = $this->actingAs($this->student)
            ->postJson(route('student.courses.lesson.complete', $this->course->id), [
                'lesson_id' => $this->lesson1->id,
                'course_id' => $this->course->id,
            ]);

        $completeRes->assertStatus(200)
            ->assertJson([
                'success'          => true,
                'course_completed' => false,
            ]);

        $this->assertDatabaseHas('lesson_progress', [
            'user_id'      => $this->student->id,
            'lesson_id'    => $this->lesson1->id,
            'is_completed' => true,
        ]);

        // Complete Lesson 2 to complete the course
        $completeLesson2 = $this->actingAs($this->student)
            ->postJson(route('student.courses.lesson.complete', $this->course->id), [
                'lesson_id' => $this->lesson2->id,
                'course_id' => $this->course->id,
            ]);

        $completeLesson2->assertStatus(200)
            ->assertJson([
                'success'          => true,
                'course_completed' => true,
            ]);
    }

    public function test_scholar_can_search_course_and_participate_in_discussions(): void
    {
        // Search endpoint
        $searchRes = $this->actingAs($this->student)
            ->postJson(route('student.courses.search', $this->course->id), [
                'q' => 'Domain',
            ]);
        $searchRes->assertStatus(200)
            ->assertJson(['success' => true]);
        $this->assertNotEmpty($searchRes->json('results'));

        // Discussions endpoint
        $forumRes = $this->actingAs($this->student)
            ->getJson(route('student.courses.discussions.index', $this->course->id));
        $forumRes->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_obsolete_quiz_assignment_certificate_routes_are_not_accessible(): void
    {
        // Quiz submit route should return 404
        $quizRes = $this->actingAs($this->student)
            ->postJson('/student/courses/' . $this->course->id . '/quiz/1/submit', []);
        $this->assertTrue(in_array($quizRes->status(), [404, 405]));

        // Assignment submit route should return 404
        $assignRes = $this->actingAs($this->student)
            ->postJson('/student/courses/' . $this->course->id . '/assignment/1/submit', []);
        $this->assertTrue(in_array($assignRes->status(), [404, 405]));

        // Certificate verify route should return 404
        $certRes = $this->actingAs($this->student)
            ->get('/verify-certificate');
        $this->assertTrue(in_array($certRes->status(), [404, 405]));
    }

    public function test_course_details_page_renders_cleanly_without_hero_banner(): void
    {
        $response = $this->get(route('courses.show', $this->course->id));

        $response->assertStatus(200);
        $response->assertSee($this->course->title, false);
        $response->assertSee('maarifazone-cd-header', false);
        $response->assertSee('maarifazone-cd-video-card', false);
        $response->assertDontSee('maarifazone-cd-hero', false);

        // Preview video must be positioned above/before the course details header in the rendered DOM
        $html = $response->getContent();
        $videoPos = strpos($html, 'class="maarifazone-cd-video-card"');
        $headerPos = strpos($html, 'class="maarifazone-cd-header"');
        $this->assertNotFalse($videoPos);
        $this->assertNotFalse($headerPos);
        $this->assertLessThan($headerPos, $videoPos, 'Course preview video must be positioned above/before the details header.');
    }
}
