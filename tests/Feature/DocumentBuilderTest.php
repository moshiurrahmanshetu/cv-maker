<?php

namespace Tests\Feature;

use App\Models\Cv;
use App\Models\CvTemplate;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected DocumentType $cvType;
    protected DocumentType $letterType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DocumentTypeSeeder::class);
        $this->seed(\Database\Seeders\TemplateSeeder::class);

        $this->user = User::factory()->create(['role' => 'user']);
        $this->cvType = DocumentType::where('slug', 'standard-cv')->first();
        $this->letterType = DocumentType::where('slug', 'cover-letter')->first();
    }

    public function test_user_can_save_letter_details_in_builder(): void
    {
        $cv = Cv::create([
            'user_id' => $this->user->id,
            'document_type_id' => $this->letterType->id,
            'title' => 'My Cover Letter',
            'status' => 'draft',
            'template_key' => 'cover-letter-modern',
        ]);

        $response = $this->actingAs($this->user)->post(route('cvs.builder.letter-details', $cv), [
            'recipient_name' => 'Sarah Connor',
            'recipient_title' => 'VP of Engineering',
            'company_name' => 'Cyberdyne Systems',
            'company_address' => "100 Innovation Way\nSilicon Valley, CA",
            'letter_date' => 'March 15, 2026',
            'subject' => 'Application for Staff AI Engineer',
            'salutation' => 'Dear Ms. Connor,',
            'opening_paragraph' => 'I am writing to express my enthusiastic interest in the Staff AI Engineer role.',
            'body_paragraph' => 'Over the past 8 years, I have architected high-performance neural compute pipelines.',
            'call_to_action' => 'I welcome the opportunity to discuss how my expertise can drive Cyberdyne forward.',
            'closing' => 'Sincerely,',
            'sender_signature' => 'John Connor',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('document_letter_details', [
            'cv_id' => $cv->id,
            'recipient_name' => 'Sarah Connor',
            'company_name' => 'Cyberdyne Systems',
            'salutation' => 'Dear Ms. Connor,',
            'sender_signature' => 'John Connor',
        ]);
    }

    public function test_user_can_save_builder_design_customization_settings(): void
    {
        $cv = Cv::create([
            'user_id' => $this->user->id,
            'document_type_id' => $this->cvType->id,
            'title' => 'Custom Styled Resume',
            'status' => 'draft',
            'template_key' => 'classic-executive',
        ]);

        $response = $this->actingAs($this->user)->post(route('cvs.builder.settings', $cv), [
            'font_family' => 'Poppins',
            'accent_color' => '#2563eb',
            'font_size' => 'small',
            'line_spacing' => 'compact',
            'section_spacing' => 'compact',
            'photo_size' => 'small',
        ]);

        $response->assertRedirect();
        $cv->refresh();
        $this->assertEquals('Poppins', $cv->getSetting('font_family'));
        $this->assertEquals('#2563eb', $cv->getSetting('accent_color'));
        $this->assertEquals('small', $cv->getSetting('font_size'));
    }

    public function test_autosave_ajax_endpoint_updates_document_state_in_background(): void
    {
        $cv = Cv::create([
            'user_id' => $this->user->id,
            'document_type_id' => $this->cvType->id,
            'title' => 'Initial Title',
            'status' => 'draft',
            'template_key' => 'classic-executive',
        ]);

        $response = $this->actingAs($this->user)->postJson(route('cvs.builder.autosave', $cv), [
            'title' => 'Autosaved Executive Resume',
            'summary' => 'Directly updated through background autosave debounce stream.',
            'settings' => [
                'font_family' => 'Roboto',
                'accent_color' => '#0f766e',
            ],
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $cv->refresh();
        $this->assertEquals('Autosaved Executive Resume', $cv->title);
        $this->assertEquals('Directly updated through background autosave debounce stream.', $cv->summary);
        $this->assertEquals('Roboto', $cv->getSetting('font_family'));
        $this->assertEquals('#0f766e', $cv->getSetting('accent_color'));
    }

    public function test_render_preview_endpoint_returns_rendered_html(): void
    {
        $cv = Cv::create([
            'user_id' => $this->user->id,
            'document_type_id' => $this->cvType->id,
            'title' => 'Live Preview Doc',
            'status' => 'draft',
            'template_key' => 'classic-executive',
        ]);

        $cv->personalInfo()->create([
            'full_name' => 'Alexander Vance',
            'job_title' => 'Lead Systems Architect',
            'email' => 'alexander@example.com',
        ]);

        $response = $this->actingAs($this->user)->getJson(route('cvs.builder.render-preview', $cv));
        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'html',
            'template',
        ]);
        $this->assertStringContainsString('Alexander Vance', $response->json('html'));
        $this->assertStringContainsString('Lead Systems Architect', $response->json('html'));
    }

    public function test_cv_and_letter_return_appropriate_checklist_sections(): void
    {
        $cvDoc = Cv::create([
            'user_id' => $this->user->id,
            'document_type_id' => $this->cvType->id,
            'title' => 'Standard CV',
            'status' => 'draft',
        ]);

        $letterDoc = Cv::create([
            'user_id' => $this->user->id,
            'document_type_id' => $this->letterType->id,
            'title' => 'Cover Letter',
            'status' => 'draft',
        ]);

        $cvChecklist = $cvDoc->getSectionChecklist();
        $letterChecklist = $letterDoc->getSectionChecklist();

        // CV has full 11 sections
        $this->assertArrayHasKey('personal-info', $cvChecklist);
        $this->assertArrayHasKey('experience', $cvChecklist);
        $this->assertArrayHasKey('education', $cvChecklist);
        $this->assertArrayHasKey('skills', $cvChecklist);
        $this->assertArrayNotHasKey('letter-details', $cvChecklist);

        // Letter has dedicated 4 sections
        $this->assertArrayHasKey('personal-info', $letterChecklist);
        $this->assertArrayHasKey('letter-details', $letterChecklist);
        $this->assertArrayHasKey('letter-content', $letterChecklist);
        $this->assertArrayHasKey('letter-closing', $letterChecklist);
        $this->assertArrayNotHasKey('experience', $letterChecklist);
    }
}
