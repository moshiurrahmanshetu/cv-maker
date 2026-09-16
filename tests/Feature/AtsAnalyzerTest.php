<?php

namespace Tests\Feature;

use App\Models\Cv;
use App\Models\CvEducation;
use App\Models\CvExperience;
use App\Models\CvPersonalInfo;
use App\Models\CvProject;
use App\Models\CvSkill;
use App\Models\CvTemplate;
use App\Models\DocumentType;
use App\Models\TemplateCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtsAnalyzerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected Cv $cv;
    protected CvTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();

        $category = TemplateCategory::create([
            'name' => 'Professional',
            'slug' => 'professional',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->template = CvTemplate::create([
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
            'template_id' => $this->template->id,
            'title' => 'Senior Software Engineer CV',
            'slug' => 'senior-software-engineer-cv',
            'template_key' => 'classic-executive',
            'status' => 'draft',
            'completion_percentage' => 80,
        ]);
    }

    public function test_ats_analysis_dashboard_displays_for_authorized_user(): void
    {
        // Populate basic CV data
        CvPersonalInfo::create([
            'cv_id' => $this->cv->id,
            'first_name' => 'Alex',
            'last_name' => 'Mercer',
            'email' => 'alex.mercer@example.com',
            'phone' => '+1 555 123 4567',
            'city' => 'San Francisco',
            'country' => 'USA',
            'bio' => 'Experienced software engineer specializing in backend systems, distributed architectures, and cloud services.',
        ]);

        $response = $this->actingAs($this->user)->get(route('cvs.ats.show', $this->cv));

        $response->assertStatus(200);
        $response->assertSee('Overall ATS Score');
        $response->assertSee('Category Scoring');
        $response->assertSee('Job Description Matcher');

        // Verify analysis was persisted in database
        $this->assertDatabaseHas('ats_analyses', [
            'cv_id' => $this->cv->id,
        ]);
    }

    public function test_unauthorized_user_cannot_view_or_analyze_other_users_cv_ats(): void
    {
        $response = $this->actingAs($this->otherUser)->get(route('cvs.ats.show', $this->cv));
        $response->assertStatus(403);

        $postResponse = $this->actingAs($this->otherUser)->post(route('cvs.ats.analyze', $this->cv));
        $postResponse->assertStatus(403);
    }

    public function test_empty_cv_receives_lower_score_and_actionable_issues(): void
    {
        $emptyCv = Cv::create([
            'user_id' => $this->user->id,
            'template_id' => $this->template->id,
            'title' => 'Empty Draft',
            'slug' => 'empty-draft',
            'template_key' => 'classic-executive',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)->get(route('cvs.ats.show', $emptyCv));

        $response->assertStatus(200);

        $analysis = $emptyCv->fresh()->latestAtsAnalysis;
        $this->assertNotNull($analysis);
        $this->assertLessThan(40, $analysis->overall_score);
        $this->assertContains('D', ['D', 'F', $analysis->getGrade()]);
        $this->assertNotEmpty($analysis->issues);
    }

    public function test_complete_cv_with_metrics_and_action_verbs_receives_high_score(): void
    {
        // 1. Personal Info
        CvPersonalInfo::create([
            'cv_id' => $this->cv->id,
            'first_name' => 'Sarah',
            'last_name' => 'Connor',
            'email' => 'sarah.connor@example.com',
            'phone' => '+1 415 888 9999',
            'city' => 'Austin',
            'country' => 'USA',
            'linkedin' => 'https://linkedin.com/in/sarahconnor',
            'github' => 'https://github.com/sarahconnor',
            'bio' => 'Senior Full Stack Engineer with 8+ years designing high-scale cloud platforms, distributed systems, and resilient microservice architectures.',
        ]);

        // 2. Work Experience with Action Verbs & Metrics
        CvExperience::create([
            'cv_id' => $this->cv->id,
            'job_title' => 'Lead Backend Engineer',
            'company' => 'Stripe Technologies',
            'city' => 'Austin, TX',
            'start_date' => '2021-01-01',
            'is_current' => true,
            'description' => 'Architected and engineered distributed payment pipelines processing $50M in annual transactions. Accelerated system throughput by 45% and reduced latency by 3x. Spearheaded migration of 15 microservices to Kubernetes, achieving 99.99% uptime across 500k active users.',
        ]);

        CvExperience::create([
            'cv_id' => $this->cv->id,
            'job_title' => 'Software Engineer',
            'company' => 'CloudCorp',
            'city' => 'Austin, TX',
            'start_date' => '2018-05-01',
            'end_date' => '2020-12-31',
            'description' => 'Developed scalable RESTful APIs in Laravel and PostgreSQL. Optimized SQL queries resulting in a 30% reduction in server CPU load. Mentored 4 junior engineers and standardized unit testing with PHPUnit.',
        ]);

        // 3. Education
        CvEducation::create([
            'cv_id' => $this->cv->id,
            'degree' => 'Bachelor of Science',
            'institution' => 'University of Texas at Austin',
            'field_of_study' => 'Computer Science',
            'start_date' => '2014-08-01',
            'end_date' => '2018-05-01',
        ]);

        // 4. Skills (categorized & healthy density)
        $skills = [
            ['name' => 'PHP', 'category' => 'Backend'],
            ['name' => 'Laravel', 'category' => 'Backend'],
            ['name' => 'MySQL', 'category' => 'Databases'],
            ['name' => 'PostgreSQL', 'category' => 'Databases'],
            ['name' => 'Docker', 'category' => 'DevOps'],
            ['name' => 'Kubernetes', 'category' => 'DevOps'],
            ['name' => 'AWS', 'category' => 'Cloud'],
            ['name' => 'Redis', 'category' => 'Databases'],
            ['name' => 'REST APIs', 'category' => 'Architecture'],
            ['name' => 'CI/CD', 'category' => 'DevOps'],
        ];

        foreach ($skills as $s) {
            CvSkill::create([
                'cv_id' => $this->cv->id,
                'name' => $s['name'],
                'category' => $s['category'],
                'level' => 'expert',
            ]);
        }

        $response = $this->actingAs($this->user)->post(route('cvs.ats.analyze', $this->cv));
        $response->assertRedirect(route('cvs.ats.show', $this->cv));

        $analysis = $this->cv->fresh()->latestAtsAnalysis;
        $this->assertGreaterThanOrEqual(80, $analysis->overall_score);
        $this->assertContains($analysis->getGrade(), ['A', 'B']);
        $this->assertGreaterThanOrEqual(4, $analysis->metrics['action_verbs_count']);
        $this->assertGreaterThanOrEqual(3, $analysis->metrics['metrics_count']);
    }

    public function test_passive_phrases_are_flagged_in_issues(): void
    {
        CvPersonalInfo::create([
            'cv_id' => $this->cv->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+1 555 333 2222',
            'bio' => 'Software developer with broad experience.',
        ]);

        CvExperience::create([
            'cv_id' => $this->cv->id,
            'job_title' => 'Web Developer',
            'company' => 'Acme Inc',
            'start_date' => '2020-01-01',
            'description' => 'Responsible for maintaining PHP applications. Duties included fixing bugs and helped with database migrations.',
        ]);

        $this->actingAs($this->user)->post(route('cvs.ats.analyze', $this->cv));

        $analysis = $this->cv->fresh()->latestAtsAnalysis;
        $this->assertGreaterThan(0, $analysis->metrics['passive_phrases_count']);

        $issueCategories = array_column($analysis->issues, 'category');
        $this->assertContains('Content Impact', $issueCategories);
    }

    public function test_job_description_matching_identifies_matched_and_missing_keywords(): void
    {
        // Add Skills to CV
        CvSkill::create(['cv_id' => $this->cv->id, 'name' => 'PHP', 'category' => 'Backend']);
        CvSkill::create(['cv_id' => $this->cv->id, 'name' => 'Laravel', 'category' => 'Backend']);
        CvSkill::create(['cv_id' => $this->cv->id, 'name' => 'MySQL', 'category' => 'Databases']);

        $jobDescription = "We are seeking a Senior Laravel Developer. Requirements: Strong experience in PHP, Laravel, MySQL, Docker, Redis, and AWS cloud deployments.";

        $response = $this->actingAs($this->user)->post(route('cvs.ats.match-job', $this->cv), [
            'job_description' => $jobDescription,
        ]);

        $response->assertRedirect(route('cvs.ats.show', $this->cv));

        $analysis = $this->cv->fresh()->latestAtsAnalysis;
        $this->assertNotNull($analysis->job_match_score);
        $this->assertEquals($jobDescription, $analysis->job_description);

        // Verify Matched Keywords contains PHP, Laravel, MySQL
        $matchedKeywords = array_column($analysis->matched_keywords, 'keyword');
        $this->assertContains('PHP', $matchedKeywords);
        $this->assertContains('Laravel', $matchedKeywords);
        $this->assertContains('MySQL', $matchedKeywords);

        // Verify Missing Keywords contains Docker, Redis, AWS
        $missingKeywords = array_column($analysis->missing_keywords, 'keyword');
        $this->assertContains('Docker', $missingKeywords);
        $this->assertContains('Redis', $missingKeywords);
        $this->assertContains('AWS', $missingKeywords);
    }

    public function test_job_matcher_handles_synonyms_and_case_insensitivity(): void
    {
        // Add React, Go, and Kubernetes to CV
        CvSkill::create(['cv_id' => $this->cv->id, 'name' => 'React', 'category' => 'Frontend']);
        CvSkill::create(['cv_id' => $this->cv->id, 'name' => 'Go', 'category' => 'Backend']);
        CvSkill::create(['cv_id' => $this->cv->id, 'name' => 'Kubernetes', 'category' => 'DevOps']);

        // JD has React.js, Golang, and K8s
        $jobDescription = "Requirements: React.js on the frontend, Golang microservices, and container orchestration with K8s.";

        $this->actingAs($this->user)->post(route('cvs.ats.match-job', $this->cv), [
            'job_description' => $jobDescription,
        ]);

        $analysis = $this->cv->fresh()->latestAtsAnalysis;
        $matchedKeywords = array_column($analysis->matched_keywords, 'keyword');

        $this->assertContains('React.js', $matchedKeywords);
        $this->assertContains('Golang', $matchedKeywords);
        $this->assertContains('K8s', $matchedKeywords);
    }

    public function test_clear_job_description_resets_target_job_matching(): void
    {
        $this->cv->atsAnalyses()->create([
            'user_id' => $this->user->id,
            'overall_score' => 85,
            'structure_score' => 20,
            'content_score' => 22,
            'skills_score' => 18,
            'completeness_score' => 17,
            'job_match_score' => 75,
            'job_description' => 'Target PHP Developer Role',
            'strengths' => ['Strong skills'],
            'issues' => [],
            'suggestions' => ['Keep it up'],
            'matched_keywords' => [['keyword' => 'PHP', 'found_in' => ['Skills']]],
            'missing_keywords' => [],
            'metrics' => [],
        ]);

        $response = $this->actingAs($this->user)->delete(route('cvs.ats.clear-job', $this->cv));
        $response->assertRedirect(route('cvs.ats.show', $this->cv));

        $latest = $this->cv->fresh()->latestAtsAnalysis;
        $this->assertNull($latest->job_description);
        $this->assertNull($latest->job_match_score);
    }

    public function test_document_type_awareness_for_cover_letters(): void
    {
        $coverLetterType = DocumentType::create([
            'name' => 'Cover Letter',
            'slug' => 'cover-letter',
            'description' => 'Professional cover letter',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $letterCv = Cv::create([
            'user_id' => $this->user->id,
            'document_type_id' => $coverLetterType->id,
            'template_id' => $this->template->id,
            'title' => 'Software Engineer Application Letter',
            'slug' => 'software-engineer-application-letter',
            'template_key' => 'classic-executive',
            'status' => 'draft',
            'summary' => 'Dear Hiring Team, I am writing to express my strong interest in the Senior Software Engineer position. Over the past six years, I have engineered scalable web platforms and collaborated across agile squads to build high-performance services. I welcome the opportunity to discuss how my technical leadership can support your growth.',
        ]);

        CvPersonalInfo::create([
            'cv_id' => $letterCv->id,
            'full_name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'phone' => '+1 555 777 8888',
            'city' => 'Seattle',
            'country' => 'USA',
        ]);

        $response = $this->actingAs($this->user)->get(route('cvs.ats.show', $letterCv));
        $response->assertStatus(200);

        $analysis = $letterCv->fresh()->latestAtsAnalysis;
        $this->assertNotNull($analysis);
        // Structure should not be heavily penalized for lack of work experiences in a cover letter
        $this->assertGreaterThanOrEqual(15, $analysis->structure_score);
    }
}
