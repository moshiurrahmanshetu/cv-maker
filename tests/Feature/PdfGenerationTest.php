<?php

namespace Tests\Feature;

use App\Models\Cv;
use App\Models\CvAward;
use App\Models\CvCertification;
use App\Models\CvCustomSection;
use App\Models\CvEducation;
use App\Models\CvExperience;
use App\Models\CvLanguage;
use App\Models\CvPersonalInfo;
use App\Models\CvProject;
use App\Models\CvReference;
use App\Models\CvSkill;
use App\Models\CvTemplate;
use App\Models\DocumentLetterDetail;
use App\Models\DocumentType;
use App\Models\TemplateCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdfGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Cv $cv;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        $category = TemplateCategory::create([
            'name' => 'Professional',
            'slug' => 'professional',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $template = CvTemplate::create([
            'category_id' => $category->id,
            'name' => 'Classic Executive',
            'slug' => 'classic-executive',
            'key' => 'classic-executive',
            'is_premium' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->cv = Cv::create([
            'user_id' => $this->user->id,
            'template_id' => $template->id,
            'title' => 'Software Engineer Resume',
            'template_key' => 'classic-executive',
            'status' => 'draft',
            'completion_percentage' => 80,
            'settings' => [
                'accent_color' => '#1e293b',
                'font_family' => 'Inter',
                'font_size' => 'normal',
                'heading_size' => 'normal',
                'line_spacing' => 'normal',
                'section_spacing' => 'normal',
                'photo_size' => 'medium',
                'show_icons' => true,
            ],
        ]);

        CvPersonalInfo::create([
            'cv_id' => $this->cv->id,
            'full_name' => 'Alex Morgan',
            'job_title' => 'Senior Backend Engineer',
            'email' => 'alex.morgan@example.com',
            'phone' => '+1 (555) 019-2834',
            'city' => 'San Francisco',
            'country' => 'United States',
            'website' => 'https://alexmorgan.dev',
        ]);
    }

    public function test_authenticated_user_can_download_pdf_for_own_cv(): void
    {
        $response = $this->actingAs($this->user)->get("/cvs/{$this->cv->id}/pdf");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.pdf', $response->headers->get('Content-Disposition'));

        // Verify content is a valid binary PDF starting with standard %PDF- header
        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $content);
    }

    public function test_authenticated_user_can_preview_pdf_inline(): void
    {
        $response = $this->actingAs($this->user)->get("/cvs/{$this->cv->id}/pdf/preview");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_builder_pdf_endpoint_alias_works(): void
    {
        $response = $this->actingAs($this->user)->get("/cvs/{$this->cv->id}/builder/pdf");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get("/cvs/{$this->cv->id}/pdf");
        $response->assertRedirect('/login');
    }

    public function test_user_cannot_download_pdf_for_another_users_cv(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)->get("/cvs/{$this->cv->id}/pdf");
        $response->assertStatus(403);
    }

    public function test_pdf_generation_with_all_repeatable_sections(): void
    {
        // Add all repeatable sections
        CvExperience::create([
            'cv_id' => $this->cv->id,
            'job_title' => 'Principal Architect',
            'employer' => 'Apex Cloud Systems',
            'city' => 'San Francisco',
            'country' => 'USA',
            'start_date' => '2021-01',
            'is_current' => true,
            'description' => 'Architected distributed systems.',
            'sort_order' => 1,
        ]);

        CvEducation::create([
            'cv_id' => $this->cv->id,
            'degree' => 'B.S. in Computer Science',
            'institution' => 'UC Berkeley',
            'start_date' => '2016-09',
            'end_date' => '2020-05',
            'grade_or_gpa' => '3.9 GPA',
            'sort_order' => 1,
        ]);

        CvSkill::create([
            'cv_id' => $this->cv->id,
            'name' => 'Laravel & PHP',
            'level' => 'Expert',
            'rating' => 95,
            'category' => 'Backend',
            'sort_order' => 1,
        ]);

        CvLanguage::create([
            'cv_id' => $this->cv->id,
            'language' => 'English',
            'proficiency' => 'Native',
            'sort_order' => 1,
        ]);

        CvCertification::create([
            'cv_id' => $this->cv->id,
            'name' => 'AWS Certified Solutions Architect',
            'issuing_organization' => 'Amazon Web Services',
            'issue_date' => '2023-01',
            'sort_order' => 1,
        ]);

        CvProject::create([
            'cv_id' => $this->cv->id,
            'title' => 'Cloud Microservices Engine',
            'technologies' => 'PHP, MySQL, Docker',
            'description' => 'High-throughput engine.',
            'sort_order' => 1,
        ]);

        CvAward::create([
            'cv_id' => $this->cv->id,
            'title' => 'Innovator of the Year',
            'issuer' => 'Tech Summit',
            'issue_date' => '2023-10',
            'sort_order' => 1,
        ]);

        CvReference::create([
            'cv_id' => $this->cv->id,
            'full_name' => 'Sarah Jenkins',
            'job_title' => 'VP Engineering',
            'company' => 'Apex Cloud',
            'email' => 'sarah@example.com',
            'is_hidden' => false,
            'sort_order' => 1,
        ]);

        CvCustomSection::create([
            'cv_id' => $this->cv->id,
            'section_title' => 'Publications',
            'title' => 'Scaling PHP Beyond Limits',
            'subtitle' => 'Tech Journal',
            'date_period' => '2024',
            'content' => 'Authored paper on microservices.',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->user)->get("/cvs/{$this->cv->id}/pdf");

        $response->assertStatus(200);
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_pdf_handles_local_profile_photo_base64_embedding(): void
    {
        Storage::fake('public');
        $photo = UploadedFile::fake()->image('avatar.jpg', 200, 200);
        $path = $photo->store('profile-photos', 'public');

        $this->cv->personalInfo->update(['photo_path' => $path]);

        $response = $this->actingAs($this->user)->get("/cvs/{$this->cv->id}/pdf");

        $response->assertStatus(200);
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_pdf_handles_missing_photo_gracefully_without_crashing(): void
    {
        // Point to non-existent file path
        $this->cv->personalInfo->update(['photo_path' => 'profile-photos/non_existent_file.jpg']);

        $response = $this->actingAs($this->user)->get("/cvs/{$this->cv->id}/pdf");

        $response->assertStatus(200);
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_pdf_generation_supports_all_document_types(): void
    {
        $types = [
            'standard-cv' => ['is_letter' => false],
            'ats-friendly-cv' => ['is_letter' => false],
            'usa-resume' => ['is_letter' => false],
            'australia-cv' => ['is_letter' => false],
            'europe-cv' => ['is_letter' => false],
            'cover-letter' => ['is_letter' => true],
            'motivation-letter' => ['is_letter' => true],
        ];

        foreach ($types as $slug => $meta) {
            $docType = DocumentType::create([
                'name' => ucwords(str_replace('-', ' ', $slug)),
                'slug' => $slug,
                'is_active' => true,
                'sort_order' => 1,
            ]);

            $doc = Cv::create([
                'user_id' => $this->user->id,
                'document_type_id' => $docType->id,
                'title' => "My {$docType->name}",
                'template_key' => $meta['is_letter'] ? 'cover-letter-classic' : 'classic-executive',
                'status' => 'draft',
            ]);

            CvPersonalInfo::create([
                'cv_id' => $doc->id,
                'full_name' => 'Alex Morgan',
                'email' => 'alex@example.com',
            ]);

            if ($meta['is_letter']) {
                DocumentLetterDetail::create([
                    'cv_id' => $doc->id,
                    'recipient_name' => 'Hiring Manager',
                    'company_name' => 'Target Corp',
                    'body' => 'I am excited to apply for this opportunity.',
                ]);
            }

            $response = $this->actingAs($this->user)->get("/cvs/{$doc->id}/pdf");
            $response->assertStatus(200);
            $this->assertStringStartsWith('%PDF-', $response->getContent());
        }
    }

    public function test_pdf_generation_supports_all_template_keys(): void
    {
        $templateKeys = [
            'classic-executive',
            'modern-minimal',
            'ats-clean',
            'technical-split',
            'australia-compact',
            'europe-europass',
            'cover-letter-classic',
            'cover-letter-modern',
            'motivation-academic',
        ];

        foreach ($templateKeys as $key) {
            $this->cv->update(['template_key' => $key]);

            $response = $this->actingAs($this->user)->get("/cvs/{$this->cv->id}/pdf");
            $response->assertStatus(200);
            $this->assertStringStartsWith('%PDF-', $response->getContent());
        }
    }

    public function test_premium_template_download_policy_foundation(): void
    {
        $category = TemplateCategory::first();
        $premiumTemplate = CvTemplate::create([
            'category_id' => $category->id,
            'name' => 'Executive Gold',
            'slug' => 'executive-gold',
            'key' => 'executive-gold',
            'is_premium' => true,
            'is_active' => true,
            'sort_order' => 99,
        ]);


        $this->cv->update([
            'template_id' => $premiumTemplate->id,
            'template_key' => 'classic-executive',
        ]);

        // Regular user without premium flag gets redirected with message
        $response = $this->actingAs($this->user)->get("/cvs/{$this->cv->id}/pdf");
        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Admin user can download premium template
        $admin = User::factory()->create(['role' => 'admin']);
        $adminCv = Cv::create([
            'user_id' => $admin->id,
            'template_id' => $premiumTemplate->id,
            'title' => 'Admin Executive Resume',
            'template_key' => 'classic-executive',
        ]);
        CvPersonalInfo::create(['cv_id' => $adminCv->id, 'full_name' => 'Admin Lead']);

        $adminResponse = $this->actingAs($admin)->get("/cvs/{$adminCv->id}/pdf");
        $adminResponse->assertStatus(200);
        $this->assertStringStartsWith('%PDF-', $adminResponse->getContent());
    }
}
