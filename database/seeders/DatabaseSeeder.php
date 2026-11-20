<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Database seeder — seeds Maarifazone baseline records.
 *
 * @author Renatus Bernardo
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CountrySeeder::class,
            AdminSeeder::class,
            PaymentGatewaySettingSeeder::class,
            PlatformSettingSeeder::class,
            NotificationTemplateSeeder::class,
            CourseCategorySeeder::class,
            CourseLanguageSeeder::class,
            MenuSeeder::class,
            FrontendSettingSeeder::class,
            CustomPageSeeder::class,
            DemoContentSeeder::class,
            VersionUpdateSeeder::class,
        ]);
    }
}
