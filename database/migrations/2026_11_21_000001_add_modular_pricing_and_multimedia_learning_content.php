<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('course_topics', function (Blueprint $table) {
            if (!Schema::hasColumn('course_topics', 'is_free')) {
                $table->boolean('is_free')->default(false)->after('description');
            }
            if (!Schema::hasColumn('course_topics', 'price')) {
                $table->decimal('price', 12, 2)->unsigned()->default(0.00)->after('is_free');
            }
        });

        Schema::table('lessons', function (Blueprint $table) {
            if (!Schema::hasColumn('lessons', 'lesson_type')) {
                $table->string('lesson_type', 30)->default('video')->after('title');
            }
            if (!Schema::hasColumn('lessons', 'attachment_path')) {
                $table->string('attachment_path', 255)->nullable()->after('video_path');
            }
            if (!Schema::hasColumn('lessons', 'attachment_name')) {
                $table->string('attachment_name', 255)->nullable()->after('attachment_path');
            }
            if (!Schema::hasColumn('lessons', 'attachment_type')) {
                $table->string('attachment_type', 50)->nullable()->after('attachment_name');
            }
            if (!Schema::hasColumn('lessons', 'is_free')) {
                $table->boolean('is_free')->default(false)->after('is_preview');
            }
            if (!Schema::hasColumn('lessons', 'price')) {
                $table->decimal('price', 12, 2)->unsigned()->default(0.00)->after('is_free');
            }
        });

        Schema::table('enrollments', function (Blueprint $table) {
            if (!Schema::hasColumn('enrollments', 'course_topic_id')) {
                $table->foreignId('course_topic_id')
                    ->nullable()
                    ->after('course_id')
                    ->constrained('course_topics')
                    ->nullOnDelete();
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'course_topic_id')) {
                $table->foreignId('course_topic_id')
                    ->nullable()
                    ->after('bundle_id')
                    ->constrained('course_topics')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'course_topic_id')) {
                $table->dropForeign(['course_topic_id']);
                $table->dropColumn('course_topic_id');
            }
        });

        Schema::table('enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('enrollments', 'course_topic_id')) {
                $table->dropForeign(['course_topic_id']);
                $table->dropColumn('course_topic_id');
            }
        });

        Schema::table('lessons', function (Blueprint $table) {
            $cols = ['lesson_type', 'attachment_path', 'attachment_name', 'attachment_type', 'is_free', 'price'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('lessons', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('course_topics', function (Blueprint $table) {
            if (Schema::hasColumn('course_topics', 'price')) {
                $table->dropColumn('price');
            }
            if (Schema::hasColumn('course_topics', 'is_free')) {
                $table->dropColumn('is_free');
            }
        });
    }
};

