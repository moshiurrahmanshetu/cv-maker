<?php

namespace Tests\Feature;

use App\Models\AiUsageLog;
use App\Models\Cv;
use App\Models\CvSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiAssistantTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_generate_profile_summary_variants_and_it_is_logged(): void
    {
        $user = User::factory()->create();
        $cv = Cv::create(['user_id' => $user->id, 'title' => 'Software Engineer CV', 'status' => 'draft']);

        $response = $this->actingAs($user)->postJson("/cvs/{$cv->id}/builder/ai/summary", [
            'target_position' => 'Senior Backend Engineer',
            'years_of_experience' => '6+ years',
            'key_skills' => 'PHP, Laravel, MySQL, Redis',
            'industry' => 'FinTech',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'feature' => 'profile_summary',
        ]);
        $response->assertJsonStructure([
            'success',
            'feature',
            'provider',
            'data' => [
                'variants' => ['professional', 'concise', 'modern'],
            ],
            'tokens_used',
        ]);

        $this->assertDatabaseHas('ai_usage_logs', [
            'user_id' => $user->id,
            'cv_id' => $cv->id,
            'feature' => 'profile_summary',
            'status' => 'success',
        ]);
    }

    public function test_user_can_generate_career_objective_variants(): void
    {
        $user = User::factory()->create();
        $cv = Cv::create(['user_id' => $user->id, 'title' => 'DevOps Resume', 'status' => 'draft']);

        $response = $this->actingAs($user)->postJson("/cvs/{$cv->id}/builder/ai/objective", [
            'target_position' => 'DevOps Specialist',
            'key_skills' => 'Kubernetes, Docker, CI/CD',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'feature' => 'career_objective',
        ]);
        $response->assertJsonStructure([
            'data' => [
                'variants' => ['professional', 'concise', 'modern'],
            ],
        ]);
    }

    public function test_user_can_rewrite_work_experience_and_generate_bullets(): void
    {
        $user = User::factory()->create();
        $cv = Cv::create(['user_id' => $user->id, 'title' => 'Product CV', 'status' => 'draft']);

        $response = $this->actingAs($user)->postJson("/cvs/{$cv->id}/builder/ai/experience", [
            'position' => 'Senior Developer',
            'company' => 'Acme Corp',
            'mode' => 'bullets',
            'draft' => 'Led migration to microservices architecture, improved system throughput by 40%, and mentored 4 junior developers.',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'feature' => 'experience_rewrite',
        ]);
        $response->assertJsonStructure([
            'data' => ['bullets'],
        ]);
    }

    public function test_user_can_rewrite_project_description(): void
    {
        $user = User::factory()->create();
        $cv = Cv::create(['user_id' => $user->id, 'title' => 'Tech Portfolio', 'status' => 'draft']);

        $response = $this->actingAs($user)->postJson("/cvs/{$cv->id}/builder/ai/project", [
            'project_name' => 'High-Throughput Analytics Engine',
            'technologies' => 'Laravel, Kafka, Redis',
            'mode' => 'describe',
            'draft' => 'Built a distributed event ingestion engine processing millions of events per hour.',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'feature' => 'project_rewrite',
        ]);
    }

    public function test_user_can_suggest_and_append_skills_to_cv(): void
    {
        $user = User::factory()->create();
        $cv = Cv::create(['user_id' => $user->id, 'title' => 'Full Stack CV', 'status' => 'draft']);

        // 1. Suggest Skills
        $suggestResponse = $this->actingAs($user)->postJson("/cvs/{$cv->id}/builder/ai/skills", [
            'target_position' => 'Full-Stack Web Architect',
        ]);

        $suggestResponse->assertStatus(200);
        $suggestResponse->assertJson(['success' => true, 'feature' => 'skills_suggestion']);
        $suggestResponse->assertJsonStructure(['data' => ['skills']]);

        // 2. Append Selected Skills
        $appendResponse = $this->actingAs($user)->postJson("/cvs/{$cv->id}/builder/ai/skills/append", [
            'skills' => ['Laravel', 'Vue.js', 'Docker', 'MySQL'],
        ]);

        $appendResponse->assertStatus(200);
        $appendResponse->assertJson([
            'success' => true,
            'added_count' => 4,
        ]);

        $this->assertDatabaseHas('cv_skills', [
            'cv_id' => $cv->id,
            'name' => 'Laravel',
        ]);
        $this->assertDatabaseHas('cv_skills', [
            'cv_id' => $cv->id,
            'name' => 'Docker',
        ]);

        // Duplicate prevention test: appending same skills should not re-add them
        $reAppendResponse = $this->actingAs($user)->postJson("/cvs/{$cv->id}/builder/ai/skills/append", [
            'skills' => ['Laravel', 'docker', 'TypeScript'],
        ]);
        $reAppendResponse->assertStatus(200);
        $reAppendResponse->assertJson([
            'success' => true,
            'added_count' => 1, // Only TypeScript added
        ]);
    }

    public function test_user_can_improve_content_with_ats_tone(): void
    {
        $user = User::factory()->create();
        $cv = Cv::create(['user_id' => $user->id, 'title' => 'Executive CV', 'status' => 'draft']);

        $response = $this->actingAs($user)->postJson("/cvs/{$cv->id}/builder/ai/improve", [
            'text' => 'I worked on code and made websites run faster for clients.',
            'tone' => 'ats',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'feature' => 'content_improve',
        ]);
        $response->assertJsonStructure(['data' => ['text']]);
    }

    public function test_user_can_generate_structured_cover_letter(): void
    {
        $user = User::factory()->create();
        $cv = Cv::create(['user_id' => $user->id, 'title' => 'Cover Letter Document', 'status' => 'draft']);

        $response = $this->actingAs($user)->postJson("/cvs/{$cv->id}/builder/ai/cover-letter", [
            'job_title' => 'Senior Cloud Engineer',
            'company_name' => 'Amazon Web Services',
            'recipient_name' => 'Hiring Manager',
            'key_skills' => 'AWS, Terraform, Go, CI/CD',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'feature' => 'cover_letter',
        ]);
        $response->assertJsonStructure([
            'data' => [
                'salutation',
                'opening',
                'body',
                'call_to_action',
                'closing',
                'signature',
                'full_text',
            ],
        ]);
    }

    public function test_user_can_generate_structured_motivation_letter(): void
    {
        $user = User::factory()->create();
        $cv = Cv::create(['user_id' => $user->id, 'title' => 'Academic Motivation Letter', 'status' => 'draft']);

        $response = $this->actingAs($user)->postJson("/cvs/{$cv->id}/builder/ai/motivation-letter", [
            'job_title' => 'Ph.D. in Computer Science',
            'company_name' => 'ETH Zurich',
            'goals' => 'Focus on distributed consensus algorithms and fault-tolerant cloud networks.',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'feature' => 'motivation_letter',
        ]);
        $response->assertJsonStructure([
            'data' => [
                'salutation',
                'opening',
                'body',
                'call_to_action',
                'closing',
                'signature',
                'full_text',
            ],
        ]);
    }

    public function test_user_cannot_generate_ai_for_another_users_cv(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $cv = Cv::create(['user_id' => $user1->id, 'title' => 'Private CV', 'status' => 'draft']);

        $response = $this->actingAs($user2)->postJson("/cvs/{$cv->id}/builder/ai/summary", [
            'target_position' => 'Unauthorized Access Attempt',
        ]);

        $response->assertStatus(403);
    }
}
