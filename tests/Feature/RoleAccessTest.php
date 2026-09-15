<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_admin_panel(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_normal_user_is_forbidden_from_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_normal_user_is_forbidden_from_admin_users_list(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_normal_user_is_forbidden_from_admin_cvs_list(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/cvs');
        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Platform Overview');
    }

    public function test_admin_user_can_access_admin_users_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);
        $response->assertSee('Users Directory');
    }

    public function test_admin_panel_link_appears_in_navbar_only_for_admins(): void
    {
        $normalUser = User::factory()->create(['role' => 'user']);
        $adminUser = User::factory()->create(['role' => 'admin']);

        // Normal user should NOT see Admin Panel link
        $response1 = $this->actingAs($normalUser)->get('/dashboard');
        $response1->assertStatus(200);
        $response1->assertDontSee('Admin Panel');

        // Admin user SHOULD see Admin Panel link
        $response2 = $this->actingAs($adminUser)->get('/dashboard');
        $response2->assertStatus(200);
        $response2->assertSee('Admin Panel');
    }
}
