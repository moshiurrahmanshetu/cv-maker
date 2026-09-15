<?php

namespace Tests\Feature;

use App\Models\Cv;
use App\Models\CvTemplate;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerDocumentCreationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DocumentTypeSeeder::class);
        $this->seed(\Database\Seeders\TemplateSeeder::class);

        $this->user = User::factory()->create(['role' => 'user']);
    }

    public function test_user_can_view_document_creation_wizard_with_all_7_types(): void
    {
        $response = $this->actingAs($this->user)->get(route('cvs.create'));
        $response->assertOk();
        $response->assertSee('Standard CV');
        $response->assertSee('ATS-Friendly CV');
        $response->assertSee('USA Resume');
        $response->assertSee('Australia-style CV');
        $response->assertSee('Europe-style CV');
        $response->assertSee('Cover Letter');
        $response->assertSee('Motivation Letter');
    }

    public function test_user_can_create_standard_cv(): void
    {
        $docType = DocumentType::where('slug', 'standard-cv')->first();
        $template = CvTemplate::where('key', 'classic-executive')->first();

        $response = $this->actingAs($this->user)->post(route('cvs.store'), [
            'title' => 'Software Engineer CV 2026',
            'document_type_id' => $docType->id,
            'template_id' => $template->id,
            'job_title' => 'Senior Backend Engineer',
        ]);

        $cv = Cv::where('title', 'Software Engineer CV 2026')->first();
        $this->assertNotNull($cv);
        $this->assertEquals($docType->id, $cv->document_type_id);
        $this->assertEquals($template->id, $cv->template_id);
        $this->assertEquals('Senior Backend Engineer', $cv->personalInfo->job_title);
        $this->assertNull($cv->letterDetail);

        $response->assertRedirect(route('cvs.builder.show', ['cv' => $cv, 'section' => 'personal-info']));
    }

    public function test_user_can_create_ats_friendly_cv(): void
    {
        $docType = DocumentType::where('slug', 'ats-cv')->first();
        $template = CvTemplate::where('key', 'ats-clean')->first();

        $response = $this->actingAs($this->user)->post(route('cvs.store'), [
            'title' => 'ATS Optimized Resume',
            'document_type_id' => $docType->id,
            'template_id' => $template?->id,
            'job_title' => 'Cloud Solutions Architect',
        ]);

        $cv = Cv::where('title', 'ATS Optimized Resume')->first();
        $this->assertNotNull($cv);
        $this->assertEquals($docType->id, $cv->document_type_id);
        $this->assertFalse($cv->isLetter());
    }

    public function test_user_can_create_cover_letter_with_auto_initialized_details(): void
    {
        $docType = DocumentType::where('slug', 'cover-letter')->first();
        $template = CvTemplate::where('key', 'cover-letter-modern')->first();

        $response = $this->actingAs($this->user)->post(route('cvs.store'), [
            'title' => 'Google Application Cover Letter',
            'document_type_id' => $docType->id,
            'template_id' => $template?->id,
            'job_title' => 'Staff Site Reliability Engineer',
            'company_or_institution' => 'Google LLC',
        ]);

        $cv = Cv::where('title', 'Google Application Cover Letter')->first();
        $this->assertNotNull($cv);
        $this->assertTrue($cv->isLetter());
        $this->assertNotNull($cv->letterDetail);
        $this->assertEquals('Google LLC', $cv->letterDetail->company_name);

        $response->assertRedirect(route('cvs.builder.show', ['cv' => $cv, 'section' => 'letter-details']));
    }

    public function test_user_can_create_motivation_letter_with_academic_settings(): void
    {
        $docType = DocumentType::where('slug', 'motivation-letter')->first();

        $response = $this->actingAs($this->user)->post(route('cvs.store'), [
            'title' => 'Stanford PhD Motivation Letter',
            'document_type_id' => $docType->id,
            'job_title' => 'PhD in Computer Science',
            'company_or_institution' => 'Stanford University',
        ]);

        $cv = Cv::where('title', 'Stanford PhD Motivation Letter')->first();
        $this->assertNotNull($cv);
        $this->assertTrue($cv->isLetter());
        $this->assertEquals('Georgia', $cv->getSetting('font_family'));
        $this->assertEquals('Stanford University', $cv->letterDetail->company_name);
    }

    public function test_user_can_duplicate_cover_letter_with_letter_details_preserved(): void
    {
        $docType = DocumentType::where('slug', 'cover-letter')->first();
        $cv = Cv::create([
            'user_id' => $this->user->id,
            'document_type_id' => $docType->id,
            'title' => 'Base Cover Letter',
            'status' => 'published',
            'template_key' => 'cover-letter-classic',
            'completion_percentage' => 80,
            'settings' => ['font_family' => 'Merriweather'],
        ]);

        $cv->letterDetail()->create([
            'recipient_name' => 'Dr. Jane Smith',
            'company_name' => 'Microsoft Research',
            'subject' => 'Research Scientist Application',
            'opening_paragraph' => 'I am delighted to submit my candidacy...',
        ]);

        $response = $this->actingAs($this->user)->post(route('cvs.duplicate', $cv));
        $response->assertRedirect();

        $duplicated = Cv::where('title', 'Base Cover Letter (Copy)')->first();
        $this->assertNotNull($duplicated);
        $this->assertEquals($docType->id, $duplicated->document_type_id);
        $this->assertNotNull($duplicated->letterDetail);
        $this->assertEquals('Microsoft Research', $duplicated->letterDetail->company_name);
        $this->assertEquals('Dr. Jane Smith', $duplicated->letterDetail->recipient_name);
        $this->assertEquals('Merriweather', $duplicated->getSetting('font_family'));
    }
}
