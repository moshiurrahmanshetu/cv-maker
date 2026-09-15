<?php

namespace Tests\Feature;

use App\Models\Cv;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CvManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_new_cv_as_draft_without_required_sections(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/cvs', [
            'title' => 'Incomplete Draft Resume',
            'job_title' => 'Software Architect',
        ]);

        $cv = Cv::where('title', 'Incomplete Draft Resume')->first();
        $this->assertNotNull($cv);
        $this->assertEquals('draft', $cv->status);
        $this->assertEquals($user->id, $cv->user_id);
        $this->assertEquals('Software Architect', $cv->personalInfo->job_title);

        $response->assertRedirect("/cvs/{$cv->id}/edit");
    }

    public function test_user_can_edit_and_save_cv_as_draft(): void
    {
        $user = User::factory()->create();
        $cv = Cv::create([
            'user_id' => $user->id,
            'title' => 'Initial Title',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->put("/cvs/{$cv->id}", [
            'title' => 'Updated Draft Title',
            'summary' => 'My updated summary',
            'action' => 'save_draft',
            'personal_info' => [
                'full_name' => 'John Developer',
                'email' => 'john@dev.com',
                'city' => 'Seattle',
            ],
            'experiences' => [
                [
                    'job_title' => 'Senior Developer',
                    'employer' => 'Tech Corp',
                    'start_date' => '2020-01',
                    'end_date' => '2023-01',
                    'is_current' => false,
                    'description' => 'Developed APIs',
                ]
            ],
            'skills' => [
                ['name' => 'Laravel', 'level' => 'Expert'],
                ['name' => 'MySQL', 'level' => 'Advanced'],
            ],
        ]);

        $response->assertRedirect("/cvs/{$cv->id}/edit");

        $cv->refresh();
        $this->assertEquals('Updated Draft Title', $cv->title);
        $this->assertEquals('draft', $cv->status);
        $this->assertEquals('John Developer', $cv->personalInfo->full_name);
        $this->assertEquals(1, $cv->experiences()->count());
        $this->assertEquals('Tech Corp', $cv->experiences()->first()->employer);
        $this->assertEquals(2, $cv->skills()->count());
    }

    public function test_user_can_publish_cv(): void
    {
        $user = User::factory()->create();
        $cv = Cv::create([
            'user_id' => $user->id,
            'title' => 'Draft Ready for Publish',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->put("/cvs/{$cv->id}", [
            'title' => 'Published Resume',
            'action' => 'publish',
        ]);

        $cv->refresh();
        $this->assertEquals('published', $cv->status);
    }

    public function test_user_can_duplicate_cv(): void
    {
        $user = User::factory()->create();
        $cv = Cv::create([
            'user_id' => $user->id,
            'title' => 'Master CV',
            'status' => 'published',
        ]);
        $cv->skills()->create(['name' => 'PHP', 'level' => 'Expert']);

        $response = $this->actingAs($user)->post("/cvs/{$cv->id}/duplicate");
        $response->assertRedirect('/cvs');

        $this->assertEquals(2, $user->cvs()->count());
        $duplicated = $user->cvs()->where('title', 'Master CV (Copy)')->first();
        $this->assertNotNull($duplicated);
        $this->assertEquals('draft', $duplicated->status);
        $this->assertEquals(1, $duplicated->skills()->count());
    }

    public function test_user_can_delete_cv(): void
    {
        $user = User::factory()->create();
        $cv = Cv::create([
            'user_id' => $user->id,
            'title' => 'To Be Deleted',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->delete("/cvs/{$cv->id}");
        $response->assertRedirect('/cvs');

        $this->assertNull(Cv::find($cv->id));
    }
}
