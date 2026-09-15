<?php

namespace Tests\Feature;

use App\Models\Cv;
use App\Models\CvEducation;
use App\Models\CvExperience;
use App\Models\CvLanguage;
use App\Models\CvPersonalInfo;
use App\Models\CvProject;
use App\Models\CvReference;
use App\Models\CvSkill;
use App\Models\CvTemplate;
use App\Models\TemplateCategory;
use App\Models\User;
use App\Services\TemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected CvTemplate $classicTemplate;
    protected CvTemplate $modernTemplate;
    protected CvTemplate $technicalTemplate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\TemplateSeeder::class);

        $this->user = User::factory()->create(['role' => 'user']);
        $this->classicTemplate = CvTemplate::where('key', 'classic-executive')->first();
        $this->modernTemplate = CvTemplate::where('key', 'modern-minimal')->first();
        $this->technicalTemplate = CvTemplate::where('key', 'technical-split')->first();
    }

    public function test_all_initial_templates_render_successfully_with_same_cv_data(): void
    {
        $cv = Cv::create([
            'user_id' => $this->user->id,
            'template_id' => $this->classicTemplate->id,
            'title' => 'Software Engineer Resume',
            'summary' => 'Dedicated backend developer with extensive experience.',
            'status' => 'published',
            'template_key' => 'classic-executive',
        ]);

        CvPersonalInfo::create([
            'cv_id' => $cv->id,
            'full_name' => 'John Doe',
            'job_title' => 'Staff Software Engineer',
            'email' => 'john.doe@example.com',
            'phone' => '+1 555-123-4567',
            'city' => 'New York',
            'country' => 'USA',
        ]);

        CvExperience::create([
            'cv_id' => $cv->id,
            'job_title' => 'Senior Backend Engineer',
            'employer' => 'Tech Corp',
            'start_date' => '2020-01',
            'is_current' => true,
            'description' => 'Built high-scale web APIs.',
            'sort_order' => 1,
        ]);

        CvEducation::create([
            'cv_id' => $cv->id,
            'degree' => 'B.S. Computer Science',
            'institution' => 'MIT',
            'start_date' => '2015-09',
            'end_date' => '2019-06',
            'sort_order' => 1,
        ]);

        CvSkill::create([
            'cv_id' => $cv->id,
            'name' => 'PHP & Laravel',
            'level' => 'Expert',
            'rating' => 95,
            'category' => 'Backend',
            'sort_order' => 1,
        ]);

        CvLanguage::create([
            'cv_id' => $cv->id,
            'language' => 'English',
            'proficiency' => 'Native',
            'sort_order' => 1,
        ]);

        // Test Template 1: Classic Executive
        $response1 = $this->actingAs($this->user)->get(route('cvs.show', $cv));
        $response1->assertOk();
        $response1->assertSee('John Doe');
        $response1->assertSee('Senior Backend Engineer');
        $response1->assertSee('Tech Corp');
        $response1->assertSee('PHP & Laravel');
        $response1->assertSee('classic-section-heading');

        // Switch to Template 2: Modern Minimal
        $cv->update([
            'template_id' => $this->modernTemplate->id,
            'template_key' => $this->modernTemplate->key,
        ]);

        $response2 = $this->actingAs($this->user)->get(route('cvs.show', $cv));
        $response2->assertOk();
        $response2->assertSee('John Doe');
        $response2->assertSee('Senior Backend Engineer');
        $response2->assertSee('modern-sidebar');

        // Switch to Template 3: Technical Split
        $cv->update([
            'template_id' => $this->technicalTemplate->id,
            'template_key' => $this->technicalTemplate->key,
        ]);

        $response3 = $this->actingAs($this->user)->get(route('cvs.show', $cv));
        $response3->assertOk();
        $response3->assertSee('John Doe');
        $response3->assertSee('Senior Backend Engineer');
        $response3->assertSee('tech-header');
    }

    public function test_template_resolver_prevents_path_traversal_and_falls_back(): void
    {
        $service = app(TemplateService::class);

        // Valid key
        $path = $service->resolveViewPath('classic-executive');
        $this->assertEquals('cv-templates.classic-executive.template', $path);

        // Path traversal attempt
        $traversalAttempt = '../../../../etc/passwd';
        $path = $service->resolveViewPath($traversalAttempt);
        $this->assertEquals('cv-templates.classic-executive.template', $path);

        // Non-existent template
        $nonExistent = 'non-existent-template-key';
        $path = $service->resolveViewPath($nonExistent);
        $this->assertEquals('cv-templates.classic-executive.template', $path);
    }

    public function test_switching_template_preserves_all_cv_data_completely(): void
    {
        $cv = Cv::create([
            'user_id' => $this->user->id,
            'template_id' => $this->classicTemplate->id,
            'title' => 'My Main Resume',
            'summary' => 'Initial summary',
            'status' => 'draft',
            'template_key' => 'classic-executive',
        ]);

        CvPersonalInfo::create([
            'cv_id' => $cv->id,
            'full_name' => 'Alice Smith',
            'email' => 'alice@example.com',
            'job_title' => 'Product Designer',
        ]);

        CvExperience::create([
            'cv_id' => $cv->id,
            'job_title' => 'UX Designer',
            'employer' => 'Design Studio',
            'sort_order' => 1,
        ]);

        CvSkill::create([
            'cv_id' => $cv->id,
            'name' => 'Figma',
            'level' => 'Expert',
            'sort_order' => 1,
        ]);

        // Call switch-template route
        $response = $this->actingAs($this->user)->post(route('cvs.switch-template', $cv), [
            'template_id' => $this->modernTemplate->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $cv->refresh();
        $this->assertEquals($this->modernTemplate->id, $cv->template_id);
        $this->assertEquals('modern-minimal', $cv->template_key);

        // Assert all original records remain completely untouched
        $this->assertEquals('Alice Smith', $cv->personalInfo->full_name);
        $this->assertEquals('Product Designer', $cv->personalInfo->job_title);
        $this->assertCount(1, $cv->experiences);
        $this->assertEquals('UX Designer', $cv->experiences->first()->job_title);
        $this->assertCount(1, $cv->skills);
        $this->assertEquals('Figma', $cv->skills->first()->name);
    }

    public function test_hidden_references_are_omitted_from_template_data(): void
    {
        $cv = Cv::create([
            'user_id' => $this->user->id,
            'template_id' => $this->classicTemplate->id,
            'title' => 'Reference Test CV',
            'status' => 'published',
            'template_key' => 'classic-executive',
        ]);

        CvReference::create([
            'cv_id' => $cv->id,
            'full_name' => 'Visible Manager',
            'job_title' => 'Director',
            'is_hidden' => false,
            'sort_order' => 1,
        ]);

        CvReference::create([
            'cv_id' => $cv->id,
            'full_name' => 'Confidential Contact',
            'job_title' => 'Secret Lead',
            'is_hidden' => true,
            'sort_order' => 2,
        ]);

        $response = $this->actingAs($this->user)->get(route('cvs.show', $cv));
        $response->assertOk();
        $response->assertSee('Visible Manager');
        $response->assertDontSee('Confidential Contact');
    }

    public function test_public_homepage_showcase_and_template_preview(): void
    {
        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertSee('Classic Executive');
        $response->assertSee('Modern Minimal');
        $response->assertSee('Technical Split');
        $response->assertSee('Use Template');

        // Modal preview endpoint
        $previewResponse = $this->get(route('templates.preview', $this->classicTemplate));
        $previewResponse->assertOk();
        $previewResponse->assertSee('Alexander Vance');
    }
}
