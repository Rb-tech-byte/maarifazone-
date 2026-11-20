<?php

/**
 * Database migration: Streamline Maarifazone schema.
 *
 * Removes obsolete Face-to-Face/Campus, Quiz, Assignment, Certificate, and Gamification tables
 * and columns, enforcing a pure 3-role (Admin, Instructor, Scholar) online learning platform.
 *
 * @author Renatus Bernardo
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Helper to safely drop foreign keys if they exist
        $dropForeignKeyIfExists = function (string $table, string $foreignKey) {
            if (! Schema::hasTable($table)) {
                return;
            }

            $fkExists = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = ? 
                  AND CONSTRAINT_NAME = ? 
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ", [$table, $foreignKey]);

            if (! empty($fkExists)) {
                Schema::table($table, function (Blueprint $t) use ($foreignKey) {
                    $t->dropForeign($foreignKey);
                });
            }
        };

        // 2. Drop foreign keys referencing obsolete tables
        // From courses
        $dropForeignKeyIfExists('courses', 'courses_certificate_template_id_foreign');
        $dropForeignKeyIfExists('courses', 'courses_default_branch_id_foreign');
        $dropForeignKeyIfExists('courses', 'courses_default_venue_id_foreign');

        // From enrollments
        $dropForeignKeyIfExists('enrollments', 'enrollments_applicant_id_foreign');
        $dropForeignKeyIfExists('enrollments', 'enrollments_branch_id_foreign');
        $dropForeignKeyIfExists('enrollments', 'enrollments_venue_id_foreign');
        $dropForeignKeyIfExists('enrollments', 'enrollments_course_assignment_id_foreign');
        $dropForeignKeyIfExists('enrollments', 'enrollments_certificate_issued_by_foreign');

        // From payments
        $dropForeignKeyIfExists('payments', 'payments_applicant_id_foreign');
        $dropForeignKeyIfExists('payments', 'payments_invoice_id_foreign');

        // From users
        $dropForeignKeyIfExists('users', 'users_branch_id_foreign');

        // 3. Drop obsolete columns from surviving tables
        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                $cols = [
                    'certificate_template_id',
                    'default_branch_id',
                    'default_venue_id',
                    'attendance_threshold',
                    'allow_online_f2f_payment',
                    'f2f_commission_enabled',
                    'legacy_scms_id',
                ];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('courses', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });

            // Ensure delivery_mode is strictly online
            if (Schema::hasColumn('courses', 'delivery_mode')) {
                DB::table('courses')->where('delivery_mode', '!=', 'online')->update(['delivery_mode' => 'online']);
            }
        }

        if (Schema::hasTable('enrollments')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $cols = [
                    'applicant_id',
                    'branch_id',
                    'venue_id',
                    'course_assignment_id',
                    'delivery_mode',
                    'session',
                    'duration_weeks',
                    'agreed_fee',
                    'attendance_percentage',
                    'certificate_status',
                    'certificate_serial',
                    'certificate_ready_at',
                    'certificate_issued_at',
                    'certificate_issued_by',
                    'legacy_scms_id',
                ];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('enrollments', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $cols = [
                    'branch_id',
                    'registration_number',
                    'national_id',
                    'legacy_scms_id',
                ];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('users', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                $cols = [
                    'applicant_id',
                    'invoice_id',
                    'installment_type',
                    'receipt_number',
                    'legacy_scms_id',
                ];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('payments', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        // 4. Drop obsolete tables in dependency order
        $tablesToDrop = [
            // Quizzes & Question banks
            'quiz_attempt_answers',
            'quiz_attempts',
            'quiz_questions',
            'quizzes',
            'question_bank_items',
            'question_banks',

            // Assignments
            'assignment_submission_files',
            'assignment_submissions',
            'assignment_files',
            'assignments',

            // Certificates
            'certificate_templates',

            // Gamification / Games
            'habit_logs',
            'habits',
            'focus_sessions',

            // Campus & F2F / Hybrid tables
            'attendance_records',
            'payment_schedules',
            'invoices',
            'applicants',
            'course_assignments',
            'venues',
            'branches',
        ];

        Schema::disableForeignKeyConstraints();
        foreach ($tablesToDrop as $tbl) {
            Schema::dropIfExists($tbl);
        }
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible streamlining
    }
};

