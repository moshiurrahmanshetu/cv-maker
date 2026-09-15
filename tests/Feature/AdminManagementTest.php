<?php

namespace Tests\Feature;

use App\Models\Cv;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_cvs_across_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $cv1 = Cv::create(['user_id' => $user1->id, 'title' => 'Resume User 1', 'status' => 'published']);
        $cv2 = Cv::create(['user_id' => $user2->id, 'title' => 'Resume User 2', 'status' => 'draft']);

        $response = $this->actingAs($admin)->get('/admin/cvs');
        $response->assertStatus(200);
        $response->assertSee('Resume User 1');
        $response->assertSee('Resume User 2');
    }

    public function test_admin_can_inspect_any_user_cv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $cv = Cv::create(['user_id' => $user->id, 'title' => 'User Resume Detailed', 'status' => 'published']);

        $response = $this->actingAs($admin)->get("/admin/cvs/{$cv->id}");
        $response->assertStatus(200);
        $response->assertSee('User Resume Detailed');
        $response->assertSee($user->email);
    }

    public function test_admin_can_toggle_user_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/toggle-role");
        $response->assertRedirect();

        $user->refresh();
        $this->assertEquals('admin', $user->role);

        // Toggle back to user
        $response2 = $this->actingAs($admin)->post("/admin/users/{$user->id}/toggle-role");
        $response2->assertRedirect();

        $user->refresh();
        $this->assertEquals('user', $user->role);
    }

    public function test_admin_cannot_toggle_own_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post("/admin/users/{$admin->id}/toggle-role");
        $response->assertRedirect();
        $response->assertSessionHas('error', 'You cannot change your own admin role.');

        $admin->refresh();
        $this->assertEquals('admin', $admin->role);
    }
}
