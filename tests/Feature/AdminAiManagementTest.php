<?php

namespace Tests\Feature;

use App\Models\AiUsageLog;
use App\Models\Cv;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAiManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_ai_management_dashboard_and_telemetry(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $cv = Cv::create(['user_id' => $user->id, 'title' => 'Sample CV', 'status' => 'draft']);

        // Seed some usage logs
        AiUsageLog::create([
            'user_id' => $user->id,
            'cv_id' => $cv->id,
            'feature' => 'profile_summary',
            'provider' => 'mock',
            'prompt_tokens' => 120,
            'completion_tokens' => 80,
            'total_tokens' => 200,
            'status' => 'success',
            'meta' => ['model' => 'mock-career-v1'],
        ]);

        AiUsageLog::create([
            'user_id' => $user->id,
            'cv_id' => $cv->id,
            'feature' => 'cover_letter',
            'provider' => 'mock',
            'prompt_tokens' => 250,
            'completion_tokens' => 300,
            'total_tokens' => 550,
            'status' => 'success',
            'meta' => ['model' => 'mock-career-v1'],
        ]);

        $response = $this->actingAs($admin)->get('/admin/ai');

        $response->assertStatus(200);
        $response->assertSee('AI Assistant Telemetry & Logs', false);
        $response->assertSee('profile summary');
        $response->assertSee('cover letter');
        $response->assertSee($user->email);
    }

    public function test_non_admin_cannot_access_ai_management(): void
    {
        $regularUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($regularUser)->get('/admin/ai');

        $response->assertStatus(403);
    }
}
