<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Admin seeder — seeds baseline institutional, RBAC, or reference records.
 *
 * @author Renatus Bernardo
 */
class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = config('demo_accounts.roles', []);

        // Legacy email prefixes that should be migrated cleanly to @maarifazone.com
        $legacyDomainMap = [
            'superadmin@maarifazone.com'      => ['superadmin@lsmout.ac.tz', 'superadmin@out.ac.tz'],
            'admin@maarifazone.com'           => ['admin@lsmout.ac.tz', 'admin@out.ac.tz', 'admin@edux.com', 'admin@eduex.com'],
            'sysadmin@maarifazone.com'        => ['sysadmin@lsmout.ac.tz', 'sysadmin@out.ac.tz'],
            'director@maarifazone.com'        => ['director@lsmout.ac.tz', 'director@out.ac.tz'],
            'deputy.director@maarifazone.com' => ['deputy.director@lsmout.ac.tz', 'deputy.director@out.ac.tz'],
            'marketing@maarifazone.com'       => ['marketing@lsmout.ac.tz', 'marketing@out.ac.tz'],
            'branch.manager@maarifazone.com'  => ['branch.manager@lsmout.ac.tz', 'branch.manager@out.ac.tz'],
            'coordinator@maarifazone.com'     => ['coordinator@lsmout.ac.tz', 'coordinator@out.ac.tz'],
            'accountant@maarifazone.com'      => ['accountant@lsmout.ac.tz', 'accountant@out.ac.tz'],
            'staff@maarifazone.com'           => ['staff@lsmout.ac.tz', 'staff@out.ac.tz'],
            'instructor@maarifazone.com'      => ['instructor@lsmout.ac.tz', 'instructor@out.ac.tz', 'instructor@edux.com', 'instructor@eduex.com'],
            'student@maarifazone.com'         => ['student@lsmout.ac.tz', 'student@out.ac.tz', 'student@edux.com', 'student@eduex.com'],
        ];

        $branchesByName = Branch::pluck('id', 'name');
        $fallbackBranchId = Branch::orderBy('id')->value('id');

        foreach ($accounts as $account) {
            $email = $account['email'];

            if (! User::where('email', $email)->exists() && isset($legacyDomainMap[$email])) {
                $legacyUser = User::whereIn('email', $legacyDomainMap[$email])->first();
                if ($legacyUser) {
                    $legacyUser->update(['email' => $email]);
                }
            }

            $branchId = null;
            if (! empty($account['branch_name'])) {
                $branchId = $branchesByName[$account['branch_name']] ?? $fallbackBranchId;
            }

            $attributes = [
                'name' => $account['name'],
                'password' => Hash::make($account['password'] ?? 'password'),
                'role' => $account['role'],
                'branch_id' => $branchId,
                'is_approved' => true,
                'is_blocked' => false,
                'blocked_at' => null,
                'blocked_reason' => null,
                'failed_login_attempts' => 0,
                'lockout_count' => 0,
                'locked_until' => null,
                'force_password_reset' => false,
                'email_verified_at' => now(),
            ];

            if ($account['spatie_role'] === 'instructor') {
                $attributes['bio'] = 'Passionate instructor ready to share knowledge.';
            }

            $user = User::updateOrCreate(['email' => $email], $attributes);

            $spatieRole = Role::where('name', $account['spatie_role'])->first();
            if ($spatieRole) {
                $user->syncRoles([$spatieRole->name]);
            }

            if ($this->command) {
                $this->command->info(sprintf(
                    '[%s] demo user ensured: %s / %s (branch_id=%s)',
                    $account['spatie_role'],
                    $email,
                    $account['password'] ?? 'password',
                    $branchId ?? 'ALL'
                ));
            }
        }
    }
}
