<?php

namespace Tests\Feature;

use App\Models\Cv;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CvOwnershipSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_another_users_cv_by_changing_id(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $cvA = Cv::create([
            'user_id' => $userA->id,
            'title' => 'Secret CV of User A',
            'status' => 'draft',
        ]);

        // User B attempts to access User A's CV
        $response = $this->actingAs($userB)->get("/cvs/{$cvA->id}");
        $response->assertStatus(403);
    }

    public function test_user_cannot_edit_another_users_cv(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $cvA = Cv::create([
            'user_id' => $userA->id,
            'title' => 'User A CV',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($userB)->get("/cvs/{$cvA->id}/edit");
        $response->assertStatus(403);
    }

    public function test_user_cannot_update_another_users_cv(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $cvA = Cv::create([
            'user_id' => $userA->id,
            'title' => 'User A CV',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($userB)->put("/cvs/{$cvA->id}", [
            'title' => 'Hacked Title',
        ]);
        $response->assertStatus(403);

        $cvA->refresh();
        $this->assertEquals('User A CV', $cvA->title);
    }

    public function test_user_cannot_delete_another_users_cv(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $cvA = Cv::create([
            'user_id' => $userA->id,
            'title' => 'User A CV',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($userB)->delete("/cvs/{$cvA->id}");
        $response->assertStatus(403);

        $this->assertNotNull(Cv::find($cvA->id));
    }

    public function test_user_cannot_duplicate_another_users_cv(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $cvA = Cv::create([
            'user_id' => $userA->id,
            'title' => 'User A CV',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($userB)->post("/cvs/{$cvA->id}/duplicate");
        $response->assertStatus(403);
    }
}
