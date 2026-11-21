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
 * End-to-end verification of Maarifazone's strict 3-role model:
 * 1. Admin: Full system management, creates courses, assigns to instructors.
 * 2. Instructor: Tutors assigned courses, cannot create courses, views earnings & payouts.
 * 3. Scholar (Student): Enrolls in 100% online courses to gain maarifa.
 *
 * @author Maarifazone Team
 */
class MaarifazoneThreeRoleWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private User $instructor;

    private User $scholar;

    private Course $onlineCourse;

    protected function setUp(): void
    {
        parent::setUp();

        URL::forceRootUrl('http://localhost');

        $this->seed(RoleSeeder::class);
        $this->seed(AdminSeeder::class);

        $this->admin = User::create([
            'name'                => 'System Administrator',
            'email'               => 'admin-' . Str::random(6) . '@maarifazone.com',
            'password'            => Hash::make('Secret@123'),
            'role'                => 'admin',
            'is_approved'         => true,
            'password_changed_at' => now(),
        ]);
        $this->admin->assignRole('admin');

        $this->instructor = User::create([
            'name'                => 'Mwalimu Instructor',
            'email'               => 'inst-' . Str::random(6) . '@maarifazone.com',
            'password'            => Hash::make('Secret@123'),
            'role'                => 'instructor',
            'is_approved'         => true,
            'password_changed_at' => now(),
        ]);
        $this->instructor->assignRole('instructor');

        $this->scholar = User::create([
            'name'                => 'Maarifa Scholar',
            'email'               => 'scholar-' . Str::random(6) . '@maarifazone.com',
            'password'            => Hash::make('Secret@123'),
            'role'                => 'student',
            'is_approved'         => true,
            'password_changed_at' => now(),
        ]);
        $this->scholar->assignRole('student');

        $this->onlineCourse = Course::create([
            'title'         => 'Advanced Web Engineering with Laravel ' . Str::random(4),
            'course_type'   => 'course',
            'delivery_mode' => 'online',
            'description'   => 'A 100% online course created by Admin and assigned to Instructor.',
            'instructor_id' => $this->instructor->id,
            'pricing_model' => 'free',
            'status'        => 'published',
            'visibility'    => 'public',
        ]);
    }

    public function test_admin_has_full_system_access_and_manages_courses(): void
    {
        // Admin dashboard
        $res = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $res->assertStatus(200);

        // Admin course management
        $courseRes = $this->actingAs($this->admin)->get(route('admin.courses.index'));
        $courseRes->assertStatus(200);
        $courseRes->assertSee($this->onlineCourse->title, false);

        // Admin students directory
        $studentsRes = $this->actingAs($this->admin)->get(route('admin.students.index'));
        $studentsRes->assertStatus(200);
        $studentsRes->assertSee('Students Directory', false);
    }

    public function test_instructor_can_access_assigned_courses_but_cannot_create_courses(): void
    {
        // Instructor dashboard
        $dashRes = $this->actingAs($this->instructor)->get(route('instructor.dashboard'));
        $dashRes->assertStatus(200);

        // Instructor assigned courses
        $coursesRes = $this->actingAs($this->instructor)->get(route('instructor.courses.index'));
        $coursesRes->assertStatus(200);
        $coursesRes->assertSee($this->onlineCourse->title, false);

        // Instructor cannot create courses; attempts redirect to assigned courses with notification
        $createRes = $this->actingAs($this->instructor)->get('/instructor/courses/create');
        $createRes->assertRedirect(route('instructor.courses'));

        // Instructor earnings & payouts access
        $payoutRes = $this->actingAs($this->instructor)->get(route('instructor.payouts.index'));
        $payoutRes->assertStatus(200);
    }

    public function test_scholar_can_browse_and_enroll_in_online_course(): void
    {
        // Public course catalog
        $catalogRes = $this->actingAs($this->scholar)->get(route('courses.index'));
        $catalogRes->assertStatus(200);
        $catalogRes->assertSee($this->onlineCourse->title, false);

        // Scholar enrolls in online course
        $enrollment = Enrollment::create([
            'user_id'      => $this->scholar->id,
            'course_id'    => $this->onlineCourse->id,
            'status'       => Enrollment::STATUS_APPROVED,
            'requested_at' => now(),
            'approved_at'  => now(),
        ]);

        // Scholar dashboard
        $scholarDash = $this->actingAs($this->scholar)->get(route('student.dashboard'));
        $scholarDash->assertStatus(200);

        // Scholar my-courses
        $myCourses = $this->actingAs($this->scholar)->get(route('student.my-courses'));
        $myCourses->assertStatus(200);
        $myCourses->assertSee($this->onlineCourse->title, false);
    }

    public function test_obsolete_campus_and_quiz_routes_are_inaccessible(): void
    {
        // Old campus apply endpoint is not accessible
        $applyRes = $this->actingAs($this->scholar)->get('/courses/' . $this->onlineCourse->id . '/apply');
        $this->assertTrue(in_array($applyRes->status(), [404, 405]));

        // Old campus admin routes are not accessible
        $campusRes = $this->actingAs($this->admin)->get('/admin/campus/branches');
        $this->assertTrue(in_array($campusRes->status(), [404, 405]));

        // Old certificate verification is not accessible
        $verifyRes = $this->get('/verify-certificate');
        $this->assertTrue(in_array($verifyRes->status(), [404, 405]));
    }

    public function test_login_page_renders_one_click_demo_accounts_and_authenticates_seamlessly(): void
    {
        // 1. Login page displays the 3-role Maarifazone demo accounts
        $loginPage = $this->get(route('login'));
        $loginPage->assertStatus(200);
        $loginPage->assertSee('One-Click Demo Access', false);
        $loginPage->assertSee('admin@maarifazone.com', false);
        $loginPage->assertSee('instructor@maarifazone.com', false);
        $loginPage->assertSee('scholar@maarifazone.com', false);
        $loginPage->assertSee('100% Online Maarifa &amp; Knowledge', false);

        // 2. Admin demo sign-in redirects to admin dashboard
        $adminLogin = $this->post(route('login'), [
            'email'    => 'admin@maarifazone.com',
            'password' => 'password',
        ]);
        $adminLogin->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs(User::where('email', 'admin@maarifazone.com')->first());
        auth()->logout();

        // 3. Instructor demo sign-in redirects to instructor dashboard
        $instructorLogin = $this->post(route('login'), [
            'email'    => 'instructor@maarifazone.com',
            'password' => 'password',
        ]);
        $instructorLogin->assertRedirect(route('instructor.dashboard'));
        $this->assertAuthenticatedAs(User::where('email', 'instructor@maarifazone.com')->first());
        auth()->logout();

        // 4. Scholar demo sign-in redirects to scholar (student) dashboard
        $scholarLogin = $this->post(route('login'), [
            'email'    => 'scholar@maarifazone.com',
            'password' => 'password',
        ]);
        $scholarLogin->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticatedAs(User::where('email', 'scholar@maarifazone.com')->first());
        auth()->logout();

        // 5. Student alias demo sign-in also redirects to scholar (student) dashboard
        $studentLogin = $this->post(route('login'), [
            'email'    => 'student@maarifazone.com',
            'password' => 'password',
        ]);
        $studentLogin->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticatedAs(User::where('email', 'student@maarifazone.com')->first());
        auth()->logout();
    }

    public function test_direct_messaging_is_removed_and_communication_is_centralized_through_admin(): void
    {
        // 1. Direct 1-on-1 messaging routes are completely removed (404)
        $instructorMessages = $this->actingAs($this->instructor)->get('/instructor/messages');
        $instructorMessages->assertStatus(404);

        $studentMessages = $this->actingAs($this->scholar)->get('/student/messages');
        $studentMessages->assertStatus(404);

        // 2. Instructor support desk route is removed (404)
        $instructorTickets = $this->actingAs($this->instructor)->get('/instructor/tickets');
        $instructorTickets->assertStatus(404);

        // 3. Scholar communicates with Central Administration via student tickets
        $scholarTicketsIndex = $this->actingAs($this->scholar)->get(route('student.tickets.index'));
        $scholarTicketsIndex->assertStatus(200);

        $ticketStore = $this->actingAs($this->scholar)->post(route('student.tickets.store'), [
            'subject'    => 'Inquiry regarding Online Curriculum',
            'department' => 'general',
            'priority'   => 'medium',
            'message'    => 'Hello Administration, I have an inquiry about the Laravel online course pacing.',
        ]);
        $ticketStore->assertRedirect();

        // 4. Admin accesses centralized support desk and manages scholar tickets
        $adminTicketsIndex = $this->actingAs($this->admin)->get(route('admin.tickets.index'));
        $adminTicketsIndex->assertStatus(200);
        $adminTicketsIndex->assertSee('Inquiry regarding Online Curriculum', false);

        $ticket = \App\Models\SupportTicket::where('subject', 'Inquiry regarding Online Curriculum')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals('student', $ticket->sender_role);

        $adminReply = $this->actingAs($this->admin)->post(route('admin.tickets.reply', $ticket), [
            'message' => 'Hello Scholar, our curriculum is self-paced with live classes every weekend.',
            'status'  => 'answered',
        ]);
        $adminReply->assertRedirect();
        $this->assertDatabaseHas('support_ticket_messages', [
            'ticket_id'      => $ticket->id,
            'is_admin_reply' => true,
        ]);
    }

    public function test_courses_are_purely_online_with_no_face_to_face_or_hybrid_options(): void
    {
        // 1. Admin course builder create page renders cleanly without campus or F2F fields
        $builderPage = $this->actingAs($this->admin)->get(route('admin.courses.create'));
        $builderPage->assertStatus(200);
        $builderPage->assertSee('100% Online Maarifa &amp; Learning', false);
        $builderPage->assertDontSee('Face-to-Face (Campus / Short Course)', false);
        $builderPage->assertDontSee('Hybrid (Campus + Online LMS)', false);
        $builderPage->assertDontSee('Default Branch', false);
        $builderPage->assertDontSee('Default Venue', false);

        // 2. Creating a course defaults to 100% online delivery
        $storeCourse = $this->actingAs($this->admin)->post(route('admin.courses.store'), [
            'title'         => 'Pure Online DevOps Mastery ' . Str::random(4),
            'instructor_id' => $this->instructor->id,
            'description'   => 'A 100% online learning course in Maarifazone.',
            'pricing_model' => 'free',
            'status'        => 'published',
            'visibility'    => 'public',
        ]);
        $storeCourse->assertRedirect();

        $newCourse = Course::where('title', 'like', 'Pure Online DevOps Mastery%')->latest()->first();
        $this->assertNotNull($newCourse);
        $this->assertEquals('online', $newCourse->delivery_mode);
        $this->assertTrue($newCourse->isOnline());
        $this->assertFalse($newCourse->isFaceToFace());
        $this->assertFalse($newCourse->isHybrid());
        $this->assertFalse($newCourse->requiresApplicantForm());

        // 3. Frontend courses catalog has no Face-to-Face or Hybrid mode filters
        $catalogPage = $this->get(route('courses.index'));
        $catalogPage->assertStatus(200);
        $catalogPage->assertDontSee('DELIVERY MODE', false);
        $catalogPage->assertDontSee('Face-to-Face (Campus)', false);
        $catalogPage->assertDontSee('Hybrid (Online & Campus)', false);

        // 4. Course details page displays pure online features and no campus F2F / attendance hurdles
        $courseDetails = $this->get(route('courses.show', $newCourse->id));
        $courseDetails->assertStatus(200);
        $courseDetails->assertSee('100% Online Maarifa &amp; Learning', false);
        $courseDetails->assertDontSee('Face-to-Face Campus Delivery', false);
        $courseDetails->assertDontSee('Hybrid (Campus + Online LMS)', false);
        $courseDetails->assertDontSee('GePG Control Number &amp; Installment Plans', false);
        $courseDetails->assertDontSee('Attendance Clearance', false);

        // 5. Admin reports page loads cleanly without broken campus routes
        $reportsPage = $this->actingAs($this->admin)->get(route('admin.reports.index'));
        $reportsPage->assertStatus(200);
        $reportsPage->assertSee('Executive &amp; Online Learning Analytics Overview', false);
    }
}
