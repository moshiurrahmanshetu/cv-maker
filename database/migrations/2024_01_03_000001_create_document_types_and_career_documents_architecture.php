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
        // 1. Document Types Table
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon', 50)->default('bi-file-earmark-text');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('configuration')->nullable();
            $table->timestamps();
        });

        // 2. Pivot Table: Document Types <-> CV Templates
        Schema::create('document_type_template', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_type_id')->constrained('document_types')->onDelete('cascade');
            $table->foreignId('cv_template_id')->constrained('cv_templates')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['document_type_id', 'cv_template_id'], 'doc_type_template_unique');
        });

        // 3. Document Letter Details (for Cover Letters & Motivation Letters)
        Schema::create('document_letter_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_id')->unique()->constrained('cvs')->onDelete('cascade');
            $table->string('recipient_name')->nullable();
            $table->string('recipient_title')->nullable();
            $table->string('company_name')->nullable();
            $table->text('company_address')->nullable();
            $table->string('letter_date', 50)->nullable();
            $table->string('subject')->nullable();
            $table->string('salutation')->nullable(); // e.g. "Dear Hiring Manager,"
            $table->text('opening')->nullable();
            $table->longText('body')->nullable();
            $table->text('call_to_action')->nullable();
            $table->string('closing', 100)->nullable(); // e.g. "Sincerely,"
            $table->string('sender_signature')->nullable();
            $table->timestamps();
        });

        // 4. Upgrade CVs / Documents table with document_type_id and settings JSON
        Schema::table('cvs', function (Blueprint $table) {
            $table->foreignId('document_type_id')->nullable()->after('user_id')->constrained('document_types')->onDelete('set null');
            $table->json('settings')->nullable()->after('font_family');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cvs', function (Blueprint $table) {
            $table->dropForeign(['document_type_id']);
            $table->dropColumn(['document_type_id', 'settings']);
        });

        Schema::dropIfExists('document_letter_details');
        Schema::dropIfExists('document_type_template');
        Schema::dropIfExists('document_types');
    }
};
