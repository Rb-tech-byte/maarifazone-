<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Admin seeder — seeds baseline Maarifazone accounts (Admin, Instructor, Scholar).
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

        // Legacy email map
        $legacyDomainMap = [
            'admin@maarifazone.com'      => ['admin@lsmout.ac.tz', 'admin@out.ac.tz', 'admin@edux.com', 'admin@eduex.com', 'superadmin@maarifazone.com'],
            'instructor@maarifazone.com' => ['instructor@lsmout.ac.tz', 'instructor@out.ac.tz', 'instructor@edux.com', 'instructor@eduex.com'],
            'student@maarifazone.com'    => ['student@lsmout.ac.tz', 'student@out.ac.tz', 'student@edux.com', 'student@eduex.com'],
        ];

        foreach ($accounts as $account) {
            $email = $account['email'];

            if (! User::where('email', $email)->exists() && isset($legacyDomainMap[$email])) {
                $legacyUser = User::whereIn('email', $legacyDomainMap[$email])->first();
                if ($legacyUser) {
                    $legacyUser->update(['email' => $email]);
                }
            }

            $attributes = [
                'name'                  => $account['name'],
                'password'              => Hash::make($account['password'] ?? 'password'),
                'role'                  => $account['role'],
                'is_approved'           => true,
                'is_blocked'            => false,
                'blocked_at'            => null,
                'blocked_reason'        => null,
                'failed_login_attempts' => 0,
                'lockout_count'         => 0,
                'locked_until'          => null,
                'force_password_reset'  => false,
                'email_verified_at'     => now(),
            ];

            if ($account['spatie_role'] === 'instructor') {
                $attributes['bio'] = 'Passionate online instructor dedicated to sharing maarifa (knowledge).';
            }

            $user = User::updateOrCreate(['email' => $email], $attributes);

            $spatieRole = Role::where('name', $account['spatie_role'])->first();
            if ($spatieRole) {
                $user->syncRoles([$spatieRole->name]);
            }

            if ($this->command) {
                $this->command->info(sprintf(
                    '[%s] Maarifazone user ensured: %s / %s',
                    $account['spatie_role'],
                    $email,
                    $account['password'] ?? 'password'
                ));
            }
        }
    }
}
