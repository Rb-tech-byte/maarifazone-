<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

/**
 * Notification template seeder — seeds Maarifazone online learning notification templates.
 *
 * @author Renatus Bernardo
 */
class NotificationTemplateSeeder extends Seeder
{
    /**
     * Execute the database seeder.
     */
    public function run(): void
    {
        $templates = [
            [
                'key' => 'welcome_scholar',
                'name' => 'Welcome New Scholar',
                'channel' => 'email',
                'subject' => 'Welcome to Maarifazone, {scholar_name}!',
                'body' => "Hello {scholar_name},\n\nWelcome to Maarifazone! Your journey of gaining lifelong knowledge (maarifa) begins now.\n\nBrowse our comprehensive online courses and start learning today.",
                'variables' => ['scholar_name'],
                'is_active' => true,
            ],
            [
                'key' => 'course_enrolled',
                'name' => 'Course Enrollment Confirmation',
                'channel' => 'email',
                'subject' => 'Enrollment Confirmed: {course_title}',
                'body' => "Hello {scholar_name},\n\nYou have successfully enrolled in {course_title}.\n\nAccess your lessons, join live classes, and engage in discussions directly from your scholar portal.",
                'variables' => ['scholar_name', 'course_title'],
                'is_active' => true,
            ],
            [
                'key' => 'live_class_reminder',
                'name' => 'Live Class Reminder',
                'channel' => 'email',
                'subject' => 'Upcoming Live Class: {class_title} ({course_title})',
                'body' => "Hello {scholar_name},\n\nA live session for {course_title} is scheduled for {scheduled_at}.\n\nTitle: {class_title}\nJoin Link: {join_url}\n\nSee you in class!",
                'variables' => ['scholar_name', 'course_title', 'class_title', 'scheduled_at', 'join_url'],
                'is_active' => true,
            ],
            [
                'key' => 'instructor_payout_approved',
                'name' => 'Instructor Payout Approved',
                'channel' => 'email',
                'subject' => 'Your Payout Request Has Been Approved ({amount})',
                'body' => "Hello {instructor_name},\n\nYour payout request for {amount} has been approved and processed.\n\nReference: {payout_reference}\nThank you for sharing your maarifa with our learners.",
                'variables' => ['instructor_name', 'amount', 'payout_reference'],
                'is_active' => true,
            ],
            [
                'key' => 'course_discussion_reply',
                'name' => 'Course Discussion Reply',
                'channel' => 'email',
                'subject' => 'New reply in {course_title}: {discussion_title}',
                'body' => "Hello {scholar_name},\n\n{replier_name} replied to your discussion on {course_title}.\n\nCheck your scholar portal to view the reply and continue the conversation.",
                'variables' => ['scholar_name', 'course_title', 'discussion_title', 'replier_name'],
                'is_active' => true,
            ],
        ];

        // Clean out obsolete templates
        NotificationTemplate::whereNotIn('key', array_column($templates, 'key'))->delete();

        foreach ($templates as $tpl) {
            NotificationTemplate::updateOrCreate(['key' => $tpl['key']], $tpl);
        }
    }
}
