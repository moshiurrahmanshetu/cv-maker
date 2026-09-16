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
        if (!Schema::hasTable('ats_analyses')) {
            Schema::create('ats_analyses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cv_id')->constrained('cvs')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('job_title', 255)->nullable();
                $table->text('job_description')->nullable();
                $table->unsignedSmallInteger('overall_score')->default(0);
                $table->unsignedSmallInteger('structure_score')->default(0);
                $table->unsignedSmallInteger('content_score')->default(0);
                $table->unsignedSmallInteger('skills_score')->default(0);
                $table->unsignedSmallInteger('completeness_score')->default(0);
                $table->unsignedSmallInteger('formatting_score')->default(0);
                $table->unsignedSmallInteger('match_score')->nullable();
                $table->json('strengths')->nullable();
                $table->json('issues')->nullable();
                $table->json('suggestions')->nullable();
                $table->json('matched_keywords')->nullable();
                $table->json('missing_keywords')->nullable();
                $table->json('metrics')->nullable();
                $table->timestamps();

                $table->index(['cv_id', 'created_at']);
                $table->index(['user_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ats_analyses');
    }
};
