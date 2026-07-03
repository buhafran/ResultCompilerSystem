<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained()->nullOnDelete();
            $table->string('admission_number');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('gender', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('portal_pin_hash')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['school_id', 'admission_number']);
            $table->index(['school_id', 'school_class_id', 'is_active']);
        });

        Schema::create('teacher_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(
                ['school_id', 'user_id', 'academic_term_id', 'school_class_id', 'subject_id'],
                'teacher_assignment_unique'
            );
            $table->index(['school_id', 'academic_term_id', 'school_class_id'],'teach_asgms_school_id_acad_term_id_school_class_idx');
        });

        Schema::create('result_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('ca_score', 5, 2)->nullable();
            $table->decimal('exam_score', 5, 2)->nullable();
            $table->decimal('total_score', 5, 2)->nullable();
            $table->string('grade', 10)->nullable();
            $table->string('remark', 100)->nullable();
            $table->string('status', 30)->default('not_entered')->index();
            $table->unsignedInteger('subject_position')->nullable();
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->unique(
                ['school_id', 'academic_term_id', 'student_id', 'subject_id'],
                'student_term_subject_unique'
            );
            $table->index(['school_id', 'academic_term_id', 'school_class_id', 'subject_id'], 'result_compile_index');
        });

        Schema::create('result_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('layout', 30)->default('modern');
            $table->json('settings')->nullable();
            $table->boolean('is_default')->default(false)->index();
            $table->timestamps();
            $table->unique(['school_id', 'name']);
        });

        Schema::create('result_publications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('result_template_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->string('status', 30)->default('draft')->index();
            $table->json('statistics')->nullable();
            $table->string('checksum', 64)->nullable();
            $table->foreignId('compiled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('compiled_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'academic_term_id', 'school_class_id', 'version'], 'publication_version_unique');
            $table->index(['school_id', 'academic_term_id', 'school_class_id', 'status'], 'publication_lookup');
        });

        Schema::create('result_summaries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('result_publication_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_score', 8, 2)->default(0);
            $table->decimal('average_score', 5, 2)->default(0);
            $table->unsignedInteger('subject_count')->default(0);
            $table->unsignedInteger('class_position')->nullable();
            $table->text('teacher_comment')->nullable();
            $table->text('principal_comment')->nullable();
            $table->boolean('ai_comment_generated')->default(false);
            $table->uuid('public_token')->unique();
            $table->json('snapshot');
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            $table->unique(['result_publication_id', 'student_id']);
            $table->index(['school_id', 'academic_term_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_summaries');
        Schema::dropIfExists('result_publications');
        Schema::dropIfExists('result_templates');
        Schema::dropIfExists('result_entries');
        Schema::dropIfExists('teacher_assignments');
        Schema::dropIfExists('students');
    }
};
