<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminCourseCatalogModernizationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $instructor;

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
            'name'                => 'Prof. Daniel Carter',
            'email'               => 'inst-' . Str::random(6) . '@maarifazone.com',
            'password'            => Hash::make('Secret@123'),
            'role'                => 'instructor',
            'is_approved'         => true,
            'password_changed_at' => now(),
        ]);
        $this->instructor->assignRole('instructor');
    }

    public function test_admin_courses_page_renders_with_default_four_per_page_and_modern_layout(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            Course::create([
                'instructor_id'  => $this->instructor->id,
                'title'          => "Modern Test Course {$i} - " . Str::random(6),
                'delivery_mode'  => 'online',
                'course_type'    => 'course',
                'visibility'     => 'public',
                'pricing_model'  => $i % 2 === 0 ? 'paid' : 'free',
                'regular_price'  => $i % 2 === 0 ? 50000 : 0,
                'status'         => 'published',
            ]);
        }

        $response = $this->actingAs($this->admin)->get(route('admin.courses.index'));

        $response->assertOk();
        $response->assertSee('All Courses');
        $response->assertSee('Active Courses');
        $response->assertSee('Live / Published');
        $response->assertSee('Pending Review');
        $response->assertSee('Draft / Staged');
        $response->assertSee('course-search-input', false);
        $response->assertSee('4 per page', false);

        $coursesOnPage = $response->viewData('courses');
        $this->assertSame(4, $coursesOnPage->perPage());
        $this->assertCount(4, $coursesOnPage);
    }

    public function test_real_time_ajax_search_returns_json_with_filtered_html_and_stats(): void
    {
        $uniqueKeyword = 'QuantumAI' . Str::random(4);

        $matchedCourse = Course::create([
            'instructor_id' => $this->instructor->id,
            'title'         => "Intro to {$uniqueKeyword} Computing",
            'delivery_mode' => 'online',
            'course_type'   => 'course',
            'visibility'    => 'public',
            'pricing_model' => 'paid',
            'regular_price' => 75000,
            'status'        => 'published',
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('admin.courses.index', [
            'search' => $uniqueKeyword,
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'html',
            'pagination',
            'total',
            'firstItem',
            'lastItem',
            'stats' => [
                'total',
                'published',
                'pending',
                'draft',
                'archived',
                'total_enrollments',
            ],
        ]);

        $json = $response->json();
        $this->assertStringContainsString($matchedCourse->title, $json['html']);
        $this->assertSame(1, $json['total']);
    }

    public function test_admin_can_update_course_status_via_ajax(): void
    {
        $course = Course::create([
            'instructor_id' => $this->instructor->id,
            'title'         => 'Status Toggle Test ' . Str::random(6),
            'delivery_mode' => 'online',
            'course_type'   => 'course',
            'visibility'    => 'public',
            'pricing_model' => 'free',
            'status'        => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.courses.update-status', $course->id), [
            'status' => 'published',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status'  => 'published',
        ]);

        $course->refresh();
        $this->assertSame('published', $course->status);
    }

    public function test_admin_can_soft_archive_and_restore_course(): void
    {
        $course = Course::create([
            'instructor_id' => $this->instructor->id,
            'title'         => 'Archive Test ' . Str::random(6),
            'delivery_mode' => 'online',
            'course_type'   => 'course',
            'visibility'    => 'public',
            'pricing_model' => 'free',
            'status'        => 'published',
        ]);

        // Soft archive
        $archiveRes = $this->actingAs($this->admin)->delete(route('admin.courses.destroy', $course->id));
        $archiveRes->assertRedirect();

        $course->refresh();
        $this->assertSame('archived', $course->status);
        $this->assertNotNull($course->deleted_date);

        // Restore
        $restoreRes = $this->actingAs($this->admin)->post(route('admin.courses.update-status', $course->id), [
            'status' => 'draft',
        ]);
        $restoreRes->assertRedirect();

        $course->refresh();
        $this->assertSame('draft', $course->status);
        $this->assertNull($course->deleted_date);
    }
}
