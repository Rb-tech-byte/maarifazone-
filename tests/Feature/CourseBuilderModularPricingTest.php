<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseTopic;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class CourseBuilderModularPricingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_course_supports_topic_pricing_and_multimedia_contents(): void
    {
        $instructor = User::create([
            'name' => 'Instructor ' . Str::random(5),
            'email' => 'inst-' . Str::random(6) . '@maarifazone.com',
            'password' => Hash::make('Secret@123'),
            'role' => 'instructor',
            'is_approved' => true,
        ]);

        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'Mastering AI Systems ' . Str::random(4),
            'course_type' => 'course',
            'delivery_mode' => 'online',
            'pricing_model' => 'paid',
            'regular_price' => 170000,
            'status' => 'published',
            'visibility' => 'public',
        ]);

        $topic1 = CourseTopic::create([
            'course_id' => $course->id,
            'title' => 'Introduction to Neural Architecture',
            'description' => 'Free fundamentals',
            'is_free' => true,
            'price' => 0,
            'order' => 0,
        ]);

        $lesson1 = Lesson::create([
            'course_topic_id' => $topic1->id,
            'title' => 'Lecture 1: Foundations',
            'lesson_type' => 'text',
            'content_text' => 'Full lecture text on transformers and recurrent nets.',
            'is_free' => true,
            'order' => 0,
        ]);

        $topic2 = CourseTopic::create([
            'course_id' => $course->id,
            'title' => 'Module 5: Advanced Inference Optimization',
            'description' => 'Specialized standalone topic',
            'is_free' => false,
            'price' => 50000,
            'order' => 1,
        ]);

        $lesson2 = Lesson::create([
            'course_topic_id' => $topic2->id,
            'title' => 'Optimization Handbook (PDF)',
            'lesson_type' => 'pdf',
            'attachment_name' => 'handbook.pdf',
            'attachment_path' => 'lessons/materials/handbook.pdf',
            'attachment_type' => 'pdf',
            'is_free' => false,
            'price' => 0,
            'order' => 0,
        ]);

        $this->assertTrue($topic1->isFree());
        $this->assertFalse($topic2->isFree());
        $this->assertTrue($topic2->hasStandalonePrice());
        $this->assertEquals(50000, (float) $topic2->price);
        $this->assertEquals('text', $lesson1->lesson_type);
        $this->assertEquals('pdf', $lesson2->lesson_type);
    }

    public function test_student_can_enroll_in_free_topic_and_checkout_standalone_paid_topic(): void
    {
        $student = User::create([
            'name' => 'Student ' . Str::random(5),
            'email' => 'stud-' . Str::random(6) . '@maarifazone.com',
            'password' => Hash::make('Secret@123'),
            'role' => 'student',
            'is_approved' => true,
        ]);

        $instructor = User::create([
            'name' => 'Instructor ' . Str::random(5),
            'email' => 'inst2-' . Str::random(6) . '@maarifazone.com',
            'password' => Hash::make('Secret@123'),
            'role' => 'instructor',
            'is_approved' => true,
        ]);

        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'Full Stack Cloud Engineering ' . Str::random(4),
            'course_type' => 'course',
            'delivery_mode' => 'online',
            'pricing_model' => 'paid',
            'regular_price' => 200000,
            'status' => 'published',
            'visibility' => 'public',
        ]);

        $topicFree = CourseTopic::create([
            'course_id' => $course->id,
            'title' => 'Topic 1: Free Foundations',
            'is_free' => true,
            'price' => 0,
            'order' => 0,
        ]);

        $topicPaid = CourseTopic::create([
            'course_id' => $course->id,
            'title' => 'Topic 5: Distributed Microservices',
            'is_free' => false,
            'price' => 60000,
            'order' => 1,
        ]);

        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');

        // Student visits checkout for free topic -> automatically enrolled without payment
        $response = $this->actingAs($student)->get('/courses/' . $course->id . '/checkout?topic_id=' . $topicFree->id);

        $response->assertRedirect(route('student.courses.show', $course->id));

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'course_topic_id' => $topicFree->id,
            'status' => Enrollment::STATUS_APPROVED,
        ]);

        // Student visits checkout for paid standalone topic -> reaches checkout page with topic price
        $checkoutResponse = $this->actingAs($student)->get(route('courses.checkout', [
            'id' => $course->id,
            'topic_id' => $topicPaid->id,
        ]));

        $checkoutResponse->assertStatus(200);
        $checkoutResponse->assertSee('Topic 5: Distributed Microservices');
        $checkoutResponse->assertSee('60,000');
    }
}
