<?php

/**
 * Application configuration for demo_accounts.
 * Maarifazone - Pure 3-Role Online Learning Architecture: Admin, Instructor, Scholar.
 *
 * @author Renatus Bernardo
 */

return [
    'domain' => 'maarifazone.com',
    'default_password' => 'password',

    /*
    |--------------------------------------------------------------------------
    | Maarifazone 3-Role Quick Access & Demo Accounts
    |--------------------------------------------------------------------------
    | Used by AdminSeeder, DemoContentSeeder, and the /login quick-access panel.
    */
    'roles' => [
        [
            'spatie_role' => 'admin',
            'role' => 'admin',
            'group' => 'admin',
            'label' => 'Administrator',
            'badge' => 'System Admin',
            'name' => 'Maarifazone Admin',
            'email' => 'admin@maarifazone.com',
            'password' => 'password',
            'icon' => 'fa-solid fa-shield-halved',
            'scope_type' => 'admin',
            'scope_badge' => 'Full System Admin',
            'short_rbac' => 'Full system management: courses, instructors, scholars, finances, platform settings',
            'allowed_modules' => 'Course Authoring & Assignment, Instructors, Scholars, Financials, Platform Settings',
            'restricted_from' => 'None — full unrestricted system administration',
            'description' => 'Author courses, assign instructors, manage payments, and administer platform settings.',
        ],
        [
            'spatie_role' => 'instructor',
            'role' => 'instructor',
            'group' => 'instructor',
            'label' => 'Instructor',
            'badge' => 'Instructor Portal',
            'name' => 'Prof. Daniel Carter',
            'email' => 'instructor@maarifazone.com',
            'password' => 'password',
            'icon' => 'fa-solid fa-chalkboard-user',
            'scope_type' => 'instructor',
            'scope_badge' => 'Assigned Instructor',
            'short_rbac' => 'Tutor assigned courses, host live classes, view earnings & request payouts',
            'allowed_modules' => 'Assigned Courses, Enrolled Scholars, Live Classes, Discussions, Course Earnings & Payout Requests',
            'restricted_from' => 'Cannot create courses (admin assigns courses). No access to system administration.',
            'description' => 'Teach assigned online courses, host live classes, track earnings, and request payouts.',
        ],
        [
            'spatie_role' => 'student',
            'role' => 'student',
            'group' => 'scholar',
            'label' => 'Scholar',
            'badge' => 'Scholar Portal',
            'name' => 'Alex Rivera',
            'email' => 'scholar@maarifazone.com',
            'password' => 'password',
            'icon' => 'fa-solid fa-graduation-cap',
            'scope_type' => 'scholar',
            'scope_badge' => 'Online Scholar',
            'short_rbac' => 'Enroll in online courses, watch video lessons, participate in discussions and live classes',
            'allowed_modules' => 'Enrolled Online Courses, Video & Text Lessons, Live Classes, Community Q&A, Support Tickets',
            'restricted_from' => 'Instructor portal and system administration',
            'description' => 'Enroll in online courses to gain maarifa, stream video lessons, and engage with mentors.',
        ],
    ],
];

