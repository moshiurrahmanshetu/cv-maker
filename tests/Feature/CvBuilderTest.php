<?php

namespace Tests\Feature;

use App\Models\Cv;
use App\Models\CvEducation;
use App\Models\CvExperience;
use App\Models\CvReference;
use App\Models\CvSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CvBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_builder_for_own_cv(): void
    {
        $user = User::factory()->create();
        $cv = Cv::create(['user_id' => $user->id, 'title' => 'My Resume', 'status' => 'draft']);

        $response = $this->actingAs($user)->get("/cvs/{$cv->id}/builder");
        $response->assertStatus(200);
        $response->assertSee('CV Sections');
        $response->assertSee('Personal Information');
    }

    public function test_user_cannot_access_builder_for_another_users_cv(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $cvA = Cv::create(['user_id' => $userA->id, 'title' => 'User A Resume', 'status' => 'draft']);

        $response = $this->actingAs($userB)->get("/cvs/{$cvA->id}/builder");
        $response->assertStatus(403);
    }

    public function test_user_can_save_personal_info_with_profile_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $cv = Cv::create(['user_id' => $user->id, 'title' => 'Resume', 'status' => 'draft']);

        $photo = UploadedFile::fake()->image('avatar.jpg', 300, 300);

        $response = $this->actingAs($user)->post("/cvs/{$cv->id}/builder/personal-info", [
            'full_name' => 'Alex Morgan',
            'job_title' => 'Senior Architect',
            'email' => 'alex@example.com',
            'phone' => '+1 555-0199',
            'city' => 'San Francisco',
            'country' => 'USA',
            'website' => 'https://alex.dev',
            'other_url' => 'https://dribbble.com/alex',
            'photo' => $photo,
        ]);

        $response->assertRedirect("/cvs/{$cv->id}/builder?section=personal-info");

        $cv->refresh();
        $this->assertNotNull($cv->personalInfo);
        $this->assertEquals('Alex Morgan', $cv->personalInfo->full_name);
        $this->assertEquals('https://dribbble.com/alex', $cv->personalInfo->other_url);
        $this->assertNotNull($cv->personalInfo->photo_path);
        Storage::disk('public')->assertExists($cv->personalInfo->photo_path);
    }

    public function test_user_can_remove_profile_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $cv = Cv::create(['user_id' => $user->id, 'title' => 'Resume', 'status' => 'draft']);

        $photo = UploadedFile::fake()->image('avatar.jpg');
        $path = $photo->store('photos/' . $cv->id, 'public');
        $cv->personalInfo()->create(['photo_path' => $path, 'full_name' => 'Alex']);

        $response = $this->actingAs($user)->post("/cvs/{$cv->id}/builder/personal-info", [
            'full_name' => 'Alex',
            'remove_photo' => 1,
        ]);

        $response->assertRedirect();
        $cv->refresh();
        $this->assertNull($cv->personalInfo->photo_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_user_can_save_summary(): void
    {
        $user = User::factory()->create();
        $cv = Cv::create(['user_id' => $user->id, 'title' => 'Resume', 'status' => 'draft']);

        $response = $this->actingAs($user)->post("/cvs/{$cv->id}/builder/summary", [
            'summary' => 'Experienced software engineer with 10 years of expertise.',
        ]);

        $response->assertRedirect("/cvs/{$cv->id}/builder?section=summary");
        $cv->refresh();
        $this->assertEquals('Experienced software engineer with 10 years of expertise.', $cv->summary);
    }

    public function test_user_can_add_edit_delete_and_reorder_work_experiences(): void
    {
        $user = User::factory()->create();
        $cv = Cv::create(['user_id' => $user->id, 'title' => 'Resume', 'status' => 'draft']);

        // 1. Add first position
        $this->actingAs($user)->post("/cvs/{$cv->id}/builder/items/experience", [
            'job_title' => 'Junior Developer',
            'employer' => 'Company A',
            'start_date' => '2019-01',
            'end_date' => '2020-01',
        ]);

        // 2. Add second position
        $this->actingAs($user)->post("/cvs/{$cv->id}/builder/items/experience", [
            'job_title' => 'Senior Developer',
            'employer' => 'Company B',
            'start_date' => '2020-02',
            'is_current' => 1,
        ]);

        $this->assertEquals(2, $cv->experiences()->count());
        $exp1 = $cv->experiences()->where('employer', 'Company A')->first();
        $exp2 = $cv->experiences()->where('employer', 'Company B')->first();

        // 3. Edit position
        $this->actingAs($user)->put("/cvs/{$cv->id}/builder/items/experience/{$exp1->id}", [
            'job_title' => 'Lead Developer',
            'employer' => 'Company A Updated',
        ]);
        $exp1->refresh();
        $this->assertEquals('Lead Developer', $exp1->job_title);

        // 4. Reorder position
        $this->actingAs($user)->post("/cvs/{$cv->id}/builder/items/experience/reorder", [
            'id' => $exp2->id,
            'direction' => 'up',
        ]);

        // 5. Delete position
        $this->actingAs($user)->delete("/cvs/{$cv->id}/builder/items/experience/{$exp1->id}");
        $this->assertEquals(1, $cv->experiences()->count());
    }

    public function test_user_can_add_skills_with_rating_and_level(): void
    {
        $user = User::factory()->create();
        $cv = Cv::create(['user_id' => $user->id, 'title' => 'Resume', 'status' => 'draft']);

        $response = $this->actingAs($user)->post("/cvs/{$cv->id}/builder/items/skills", [
            'name' => 'Laravel & PHP',
            'level' => 'Expert',
            'rating' => 95,
            'category' => 'Backend',
        ]);

        $response->assertRedirect("/cvs/{$cv->id}/builder?section=skills");
        $skill = $cv->skills()->first();
        $this->assertNotNull($skill);
        $this->assertEquals('Laravel & PHP', $skill->name);
        $this->assertEquals(95, $skill->rating);
        $this->assertEquals('Expert', $skill->level);
    }

    public function test_user_can_add_references_and_toggle_visibility(): void
    {
        $user = User::factory()->create();
        $cv = Cv::create(['user_id' => $user->id, 'title' => 'Resume', 'status' => 'draft']);

        $this->actingAs($user)->post("/cvs/{$cv->id}/builder/items/references", [
            'full_name' => 'Jane Smith',
            'job_title' => 'Director',
            'company' => 'Acme Corp',
            'email' => 'jane@acme.com',
            'is_hidden' => 0,
        ]);

        $ref = $cv->references()->first();
        $this->assertNotNull($ref);
        $this->assertFalse($ref->is_hidden);

        // Toggle visibility to hidden
        $this->actingAs($user)->post("/cvs/{$cv->id}/builder/references/{$ref->id}/toggle-visibility");
        $ref->refresh();
        $this->assertTrue($ref->is_hidden);
    }

    public function test_user_can_add_custom_section_entries(): void
    {
        $user = User::factory()->create();
        $cv = Cv::create(['user_id' => $user->id, 'title' => 'Resume', 'status' => 'draft']);

        $response = $this->actingAs($user)->post("/cvs/{$cv->id}/builder/items/custom", [
            'section_title' => 'Volunteer Work',
            'title' => 'Community Lead',
            'subtitle' => 'Code for Good',
            'date_period' => '2022 - Present',
            'content' => 'Organized free coding workshops for 500+ students.',
        ]);

        $response->assertRedirect("/cvs/{$cv->id}/builder?section=custom");
        $custom = $cv->customSections()->first();
        $this->assertNotNull($custom);
        $this->assertEquals('Volunteer Work', $custom->section_title);
        $this->assertEquals('Community Lead', $custom->title);
    }

    public function test_saving_one_section_never_deletes_or_modifies_other_sections(): void
    {
        $user = User::factory()->create();
        $cv = Cv::create(['user_id' => $user->id, 'title' => 'Resume', 'status' => 'draft']);

        // Create education and skill first
        $cv->educations()->create(['institution' => 'MIT', 'degree' => 'B.S. CS']);
        $cv->skills()->create(['name' => 'Python', 'level' => 'Advanced']);

        // Now save personal info
        $this->actingAs($user)->post("/cvs/{$cv->id}/builder/personal-info", [
            'full_name' => 'Alex Developer',
            'email' => 'alex@dev.com',
        ]);

        // Verify education and skill are completely preserved
        $this->assertEquals(1, $cv->educations()->count());
        $this->assertEquals('MIT', $cv->educations()->first()->institution);
        $this->assertEquals(1, $cv->skills()->count());
        $this->assertEquals('Python', $cv->skills()->first()->name);
    }

    public function test_user_b_cannot_tamper_with_user_a_section_records(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $cvA = Cv::create(['user_id' => $userA->id, 'title' => 'CV A', 'status' => 'draft']);
        $expA = $cvA->experiences()->create(['job_title' => 'Engineer', 'employer' => 'Company A']);

        // User B tries to update User A's experience
        $response = $this->actingAs($userB)->put("/cvs/{$cvA->id}/builder/items/experience/{$expA->id}", [
            'job_title' => 'Hacked Job Title',
            'employer' => 'Hacked Employer',
        ]);
        $response->assertStatus(403);

        // User B tries to delete User A's experience
        $response2 = $this->actingAs($userB)->delete("/cvs/{$cvA->id}/builder/items/experience/{$expA->id}");
        $response2->assertStatus(403);

        $expA->refresh();
        $this->assertEquals('Engineer', $expA->job_title);
    }
}
