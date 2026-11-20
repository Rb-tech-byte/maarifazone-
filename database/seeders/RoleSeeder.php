<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Role seeder — seeds Maarifazone 3-role matrix (Admin, Instructor, Scholar/Student).
 *
 * @author Renatus Bernardo
 */
class RoleSeeder extends Seeder
{
    /**
     * Permission groups for Maarifazone.
     */
    public static function permissionGroups(): array
    {
        return [
            'Courses & Curriculum' => [
                'view-course'    => 'View courses',
                'create-course'  => 'Create courses (Admin only)',
                'edit-course'    => 'Edit & publish courses',
                'delete-course'  => 'Delete courses',
                'manage-courses' => 'Manage all courses, categories & languages',
                'manage-bundles' => 'Manage course bundles',
            ],
            'Scholars & Instructors' => [
                'view-users'         => 'View scholars & instructors',
                'student:create'     => 'Create scholars directly',
                'student:update'     => 'Edit scholar accounts',
                'student:delete'     => 'Delete scholars',
                'instructor:create'  => 'Create instructors directly',
                'instructor:update'  => 'Edit instructor accounts',
                'instructor:delete'  => 'Delete instructors',
                'approve-instructor' => 'Approve instructor applications',
                'reject-instructor'  => 'Reject instructor applications',
                'delete-users'       => 'Delete user accounts',
            ],
            'Finance & Payouts' => [
                'manage-payments'    => 'Manage payments & offline approvals',
                'manage-withdrawals' => 'Manage instructor payout requests',
                'manage-coupons'     => 'Manage discount coupons',
                'view-earnings'      => 'View assigned course earnings',
                'request-payout'     => 'Request instructor earnings withdrawal',
            ],
            'Live Classes & Community' => [
                'manage-live-classes' => 'Schedule and host live classes',
                'manage-discussions'  => 'Participate in course discussions and Q&A',
                'manage-reviews'      => 'Moderate and reply to reviews',
            ],
            'Reports & Analytics' => [
                'view-reports'   => 'View platform enrollment and financial reports',
                'export-reports' => 'Export platform reports (PDF/Excel/CSV)',
                'view-analytics' => 'View platform learning analytics',
            ],
            'CMS & Marketing' => [
                'manage-frontend'      => 'Manage homepage, about page, menus & SEO',
                'manage-notifications' => 'Send push & broadcast notifications',
            ],
            'Support & Administration' => [
                'manage-tickets'  => 'Manage support tickets',
                'manage-roles'    => 'Manage roles & permissions',
                'view-audit-logs' => 'View activity audit logs',
                'manage-settings' => 'Manage platform, gateway & system settings',
                'manage-backups'  => 'Create and download system backups',
                'manage-all'      => 'Full Administrator unrestricted access',
            ],
            'Scholar Actions' => [
                'enroll-course'   => 'Enroll in online courses',
                'unenroll-course' => 'Unenroll from courses',
            ],
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Sync permissions
        $validPermissionNames = [];
        foreach (static::permissionGroups() as $group => $items) {
            foreach (array_keys($items) as $permissionName) {
                Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
                $validPermissionNames[] = $permissionName;
            }
        }

        // Remove obsolete permissions not in the current list
        Permission::where('guard_name', 'web')
            ->whereNotIn('name', $validPermissionNames)
            ->delete();

        // 2. Remove obsolete institutional roles from Spatie
        $obsoleteRoles = [
            'super-admin',
            'system-administrator',
            'director',
            'deputy-director',
            'branch-registrar',
            'coordinator',
            'marketing-officer',
            'accountant',
            'staff',
        ];

        foreach ($obsoleteRoles as $oldRole) {
            $r = Role::where('name', $oldRole)->first();
            if ($r) {
                // Reassign users with this role to 'admin'
                $users = $r->users;
                foreach ($users as $u) {
                    $u->update(['role' => 'admin']);
                    $u->assignRole('admin');
                }
                $r->delete();
            }
        }

        // 3. Ensure the 3 canonical roles
        $adminRole      = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $instructorRole = Role::firstOrCreate(['name' => 'instructor', 'guard_name' => 'web']);
        $studentRole    = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        // Admin gets all permissions
        $adminRole->syncPermissions(Permission::all());

        // Instructor gets assigned-course management, live classes, discussions, reviews, earnings & payouts
        // (NOTE: create-course and delete-course are explicitly excluded; admin creates and assigns courses)
        $instructorRole->syncPermissions([
            'view-course',
            'manage-live-classes',
            'manage-discussions',
            'manage-reviews',
            'view-earnings',
            'request-payout',
            'manage-tickets',
        ]);

        // Scholar (Student) gets learning permissions
        $studentRole->syncPermissions([
            'view-course',
            'enroll-course',
            'unenroll-course',
            'manage-discussions',
            'manage-tickets',
        ]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
