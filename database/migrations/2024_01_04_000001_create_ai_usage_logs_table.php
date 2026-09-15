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
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('cv_id')->nullable()->constrained('cvs')->onDelete('cascade');
            $table->string('feature', 50)->index(); // profile_summary, career_objective, experience_rewrite, etc.
            $table->string('provider', 50)->default('mock'); // mock, openai, gemini, anthropic
            $table->string('model', 50)->default('default');
            $table->unsignedInteger('prompt_tokens')->nullable();
            $table->unsignedInteger('completion_tokens')->nullable();
            $table->unsignedInteger('total_tokens')->nullable();
            $table->string('status', 20)->default('success')->index(); // success, failed, rate_limited
            $table->json('context_summary')->nullable(); // sanitized non-sensitive metadata for telemetry
            $table->text('error_message')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'feature']);
            $table->index(['created_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
    }
};
