<?php

/**
 * Application configuration for demo_accounts.
 * Maarifazone - Pure 3-Role Model: Admin, Instructor, Scholar (Student).
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
    | Used by AdminSeeder and the /login quick-access panel.
    */
    'roles' => [
        [
            'spatie_role' => 'admin',
            'role' => 'admin',
            'group' => 'admin',
            'label' => 'Administrator',
            'name' => 'Maarifazone Admin',
            'email' => 'admin@maarifazone.com',
            'password' => 'password',
            'icon' => 'fa-solid fa-shield-halved',
            'scope_type' => 'global',
            'scope_badge' => 'Full System Administration',
            'short_rbac' => 'Full system management: courses, instructors, scholars, finance, settings',
            'allowed_modules' => 'All System Modules, Course Authoring & Assignment, Instructors, Scholars, Financials, Platform Settings',
            'restricted_from' => 'None — full unrestricted administration',
        ],
        [
            'spatie_role' => 'instructor',
            'role' => 'instructor',
            'group' => 'instructor',
            'label' => 'Instructor',
            'name' => 'Prof. Daniel Carter',
            'email' => 'instructor@maarifazone.com',
            'password' => 'password',
            'icon' => 'fa-solid fa-chalkboard-user',
            'scope_type' => 'portal',
            'scope_badge' => 'Instructor Portal',
            'short_rbac' => 'Teach assigned courses, host live classes, view earnings & request payouts',
            'allowed_modules' => 'Assigned Courses, Enrolled Scholars, Live Classes, Discussions, Course Earnings & Payout Requests',
            'restricted_from' => 'Cannot create courses (admin assigns courses). No access to system administration or other instructors\' courses.',
        ],
        [
            'spatie_role' => 'student',
            'role' => 'student',
            'group' => 'scholar',
            'label' => 'Scholar (Student)',
            'name' => 'Alex Rivera',
            'email' => 'student@maarifazone.com',
            'password' => 'password',
            'icon' => 'fa-solid fa-graduation-cap',
            'scope_type' => 'portal',
            'scope_badge' => 'Scholar Portal',
            'short_rbac' => 'Enroll in online courses, watch lessons, participate in discussions and live classes',
            'allowed_modules' => 'Enrolled Online Courses, Video & Text Lessons, Live Classes, Community Q&A, Support Tickets',
            'restricted_from' => 'Instructor portal and system administration',
        ],
    ],
];
