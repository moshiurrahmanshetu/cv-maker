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
        // 1. Core CV Table
        Schema::create('cvs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->nullable()->index();
            $table->text('summary')->nullable();
            $table->string('status', 30)->default('draft')->index(); // 'draft', 'published'
            $table->string('template_key', 50)->default('classic');
            $table->string('primary_color', 30)->nullable();
            $table->string('font_family', 50)->nullable();
            $table->unsignedTinyInteger('completion_percentage')->default(10);
            $table->timestamps();
        });

        // 2. Personal Information (1:1 with CV)
        Schema::create('cv_personal_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_id')->unique()->constrained('cvs')->onDelete('cascade');
            $table->string('full_name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('website')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('github')->nullable();
            $table->string('other_url')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });

        // 3. Work Experiences
        Schema::create('cv_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_id')->constrained('cvs')->onDelete('cascade');
            $table->string('job_title')->nullable();
            $table->string('employer')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // 4. Educations
        Schema::create('cv_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_id')->constrained('cvs')->onDelete('cascade');
            $table->string('institution')->nullable();
            $table->string('degree')->nullable();
            $table->string('field_of_study')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->string('grade_or_gpa')->nullable();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // 5. Skills
        Schema::create('cv_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_id')->constrained('cvs')->onDelete('cascade');
            $table->string('name');
            $table->string('level', 30)->nullable(); // Beginner, Intermediate, Advanced, Expert
            $table->unsignedTinyInteger('rating')->default(80); // 1-100 percentage for versatile rendering
            $table->string('category', 50)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // 6. Languages
        Schema::create('cv_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_id')->constrained('cvs')->onDelete('cascade');
            $table->string('language');
            $table->string('proficiency', 50)->nullable(); // Native, Fluent, Professional, Basic
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // 7. Certifications
        Schema::create('cv_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_id')->constrained('cvs')->onDelete('cascade');
            $table->string('name');
            $table->string('issuing_organization')->nullable();
            $table->string('issue_date')->nullable();
            $table->string('expiration_date')->nullable();
            $table->string('credential_id')->nullable();
            $table->string('credential_url')->nullable();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // 8. Projects
        Schema::create('cv_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_id')->constrained('cvs')->onDelete('cascade');
            $table->string('title');
            $table->string('role')->nullable();
            $table->string('project_url')->nullable();
            $table->string('technologies')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // 9. Awards
        Schema::create('cv_awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_id')->constrained('cvs')->onDelete('cascade');
            $table->string('title');
            $table->string('issuer')->nullable();
            $table->string('issue_date')->nullable();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // 10. References
        Schema::create('cv_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_id')->constrained('cvs')->onDelete('cascade');
            $table->string('full_name');
            $table->string('job_title')->nullable();
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('relationship')->nullable();
            $table->boolean('is_hidden')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // 11. Custom Sections
        Schema::create('cv_custom_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_id')->constrained('cvs')->onDelete('cascade');
            $table->string('section_title');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('date_period')->nullable();
            $table->text('content')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cv_custom_sections');
        Schema::dropIfExists('cv_references');
        Schema::dropIfExists('cv_awards');
        Schema::dropIfExists('cv_projects');
        Schema::dropIfExists('cv_certifications');
        Schema::dropIfExists('cv_languages');
        Schema::dropIfExists('cv_skills');
        Schema::dropIfExists('cv_educations');
        Schema::dropIfExists('cv_experiences');
        Schema::dropIfExists('cv_personal_infos');
        Schema::dropIfExists('cvs');
    }
};
