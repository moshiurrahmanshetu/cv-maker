<?php

namespace Tests\Feature;

use App\Models\Cv;
use App\Models\CvAward;
use App\Models\CvCertification;
use App\Models\CvCustomSection;
use App\Models\CvEducation;
use App\Models\CvExperience;
use App\Models\CvLanguage;
use App\Models\CvProject;
use App\Models\CvReference;
use App\Models\CvSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepeatableSectionsBatchTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Cv $cv;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->cv = Cv::create([
            'user_id' => $this->user->id,
            'title' => 'Software Engineer Resume',
            'status' => 'draft',
        ]);
    }

    public function test_user_can_batch_create_multiple_new_skills(): void
    {
        $payload = [
            'items' => [
                'temp_1' => [
                    'name' => 'PHP / Laravel',
                    'level' => 'Expert',
                    'rating' => 95,
                    'category' => 'Backend',
                ],
                'temp_2' => [
                    'name' => 'PostgreSQL',
                    'level' => 'Intermediate',
                    'rating' => 80,
                    'category' => 'Database',
                ],
                'temp_3' => [
                    'name' => 'Docker',
                    'level' => 'Proficient',
                    'rating' => 85,
                    'category' => 'DevOps',
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->post("/cvs/{$this->cv->id}/builder/items/skills/batch", $payload);

        $response->assertRedirect("/cvs/{$this->cv->id}/builder?section=skills");
        $this->assertDatabaseCount('cv_skills', 3);
        $this->assertDatabaseHas('cv_skills', ['cv_id' => $this->cv->id, 'name' => 'PHP / Laravel', 'sort_order' => 1]);
        $this->assertDatabaseHas('cv_skills', ['cv_id' => $this->cv->id, 'name' => 'PostgreSQL', 'sort_order' => 2]);
        $this->assertDatabaseHas('cv_skills', ['cv_id' => $this->cv->id, 'name' => 'Docker', 'sort_order' => 3]);
    }

    public function test_user_can_batch_update_existing_and_add_new_experiences(): void
    {
        $existing = CvExperience::create([
            'cv_id' => $this->cv->id,
            'job_title' => 'Junior Developer',
            'employer' => 'Old Tech Inc',
            'city' => 'Austin',
            'country' => 'USA',
            'start_date' => '2020-01',
            'end_date' => '2022-01',
            'is_current' => false,
            'description' => 'Built APIs',
            'sort_order' => 1,
        ]);

        $payload = [
            'items' => [
                (string)$existing->id => [
                    'id' => $existing->id,
                    'job_title' => 'Senior Developer', // Updated title
                    'employer' => 'Old Tech Inc',
                    'city' => 'Austin',
                    'country' => 'USA',
                    'start_date' => '2020-01',
                    'end_date' => '2022-01',
                    'is_current' => 0,
                    'description' => 'Led backend team and built microservices',
                ],
                'temp_new_exp' => [
                    'job_title' => 'Lead Engineer',
                    'employer' => 'NextGen Cloud',
                    'city' => 'Seattle',
                    'country' => 'USA',
                    'start_date' => '2022-02',
                    'is_current' => 1,
                    'description' => 'Architecting scalable cloud systems',
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->post("/cvs/{$this->cv->id}/builder/items/experience/batch", $payload);

        $response->assertRedirect("/cvs/{$this->cv->id}/builder?section=experience");
        $this->assertDatabaseCount('cv_experiences', 2);
        $this->assertDatabaseHas('cv_experiences', [
            'id' => $existing->id,
            'job_title' => 'Senior Developer',
            'sort_order' => 1,
        ]);
        $this->assertDatabaseHas('cv_experiences', [
            'cv_id' => $this->cv->id,
            'job_title' => 'Lead Engineer',
            'employer' => 'NextGen Cloud',
            'is_current' => true,
            'sort_order' => 2,
        ]);
    }

    public function test_user_can_delete_existing_records_via_batch(): void
    {
        $edu1 = CvEducation::create([
            'cv_id' => $this->cv->id,
            'institution' => 'University A',
            'degree' => 'BSc Computer Science',
            'sort_order' => 1,
        ]);
        $edu2 = CvEducation::create([
            'cv_id' => $this->cv->id,
            'institution' => 'University B',
            'degree' => 'MSc Software Engineering',
            'sort_order' => 2,
        ]);

        $payload = [
            'deleted_ids' => [$edu1->id],
            'items' => [
                (string)$edu2->id => [
                    'id' => $edu2->id,
                    'institution' => 'University B (Updated)',
                    'degree' => 'MSc Software Engineering',
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->post("/cvs/{$this->cv->id}/builder/items/education/batch", $payload);

        $response->assertRedirect("/cvs/{$this->cv->id}/builder?section=education");
        $this->assertDatabaseMissing('cv_educations', ['id' => $edu1->id]);
        $this->assertDatabaseHas('cv_educations', [
            'id' => $edu2->id,
            'institution' => 'University B (Updated)',
            'sort_order' => 1,
        ]);
    }

    public function test_batch_ignores_completely_empty_unpersisted_items(): void
    {
        $payload = [
            'items' => [
                'temp_empty_1' => [
                    'name' => '',
                    'category' => '',
                ],
                'temp_valid' => [
                    'name' => 'TypeScript',
                    'level' => 'Advanced',
                    'rating' => 90,
                    'category' => 'Frontend',
                ],
                'temp_empty_2' => [
                    'name' => '   ',
                    'category' => '',
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->post("/cvs/{$this->cv->id}/builder/items/skills/batch", $payload);

        $response->assertRedirect("/cvs/{$this->cv->id}/builder?section=skills");
        $this->assertDatabaseCount('cv_skills', 1);
        $this->assertDatabaseHas('cv_skills', ['name' => 'TypeScript']);
    }

    public function test_batch_persists_languages_and_certifications(): void
    {
        // Languages
        $langPayload = [
            'items' => [
                't1' => ['language' => 'English', 'proficiency' => 'Native'],
                't2' => ['language' => 'Spanish', 'proficiency' => 'Intermediate'],
            ],
        ];
        $this->actingAs($this->user)
            ->post("/cvs/{$this->cv->id}/builder/items/languages/batch", $langPayload)
            ->assertRedirect();
        $this->assertDatabaseCount('cv_languages', 2);

        // Certifications
        $certPayload = [
            'items' => [
                't1' => [
                    'name' => 'AWS Certified Solutions Architect',
                    'issuing_organization' => 'Amazon Web Services',
                    'issue_date' => '2023-05',
                ],
            ],
        ];
        $this->actingAs($this->user)
            ->post("/cvs/{$this->cv->id}/builder/items/certifications/batch", $certPayload)
            ->assertRedirect();
        $this->assertDatabaseCount('cv_certifications', 1);
        $this->assertDatabaseHas('cv_certifications', ['name' => 'AWS Certified Solutions Architect']);
    }

    public function test_batch_persists_projects_awards_references_and_custom_sections(): void
    {
        // Projects
        $projPayload = [
            'items' => [
                't1' => [
                    'title' => 'Open Source CRM',
                    'role' => 'Lead Creator',
                    'technologies' => 'Laravel, Vue, Tailwind',
                    'description' => 'Fast modular CRM platform',
                ],
            ],
        ];
        $this->actingAs($this->user)
            ->post("/cvs/{$this->cv->id}/builder/items/projects/batch", $projPayload)
            ->assertRedirect();
        $this->assertDatabaseCount('cv_projects', 1);

        // Awards
        $awardPayload = [
            'items' => [
                't1' => [
                    'title' => 'First Place Global Hackathon',
                    'issuer' => 'Tech Corp',
                    'issue_date' => '2023-11',
                ],
            ],
        ];
        $this->actingAs($this->user)
            ->post("/cvs/{$this->cv->id}/builder/items/awards/batch", $awardPayload)
            ->assertRedirect();
        $this->assertDatabaseCount('cv_awards', 1);

        // References
        $refPayload = [
            'items' => [
                't1' => [
                    'full_name' => 'Dr. Jane Smith',
                    'job_title' => 'CTO',
                    'company' => 'Acme Labs',
                    'email' => 'jane@acme.com',
                    'is_hidden' => 1,
                ],
            ],
        ];
        $this->actingAs($this->user)
            ->post("/cvs/{$this->cv->id}/builder/items/references/batch", $refPayload)
            ->assertRedirect();
        $this->assertDatabaseCount('cv_references', 1);
        $this->assertDatabaseHas('cv_references', ['full_name' => 'Dr. Jane Smith', 'is_hidden' => true]);

        // Custom Sections
        $customPayload = [
            'items' => [
                't1' => [
                    'section_title' => 'Publications',
                    'title' => 'High-Performance Microservices in PHP',
                    'subtitle' => 'IEEE Software Journal',
                    'date_period' => '2024',
                    'content' => 'Co-authored paper on distributed caching strategies.',
                ],
            ],
        ];
        $this->actingAs($this->user)
            ->post("/cvs/{$this->cv->id}/builder/items/custom/batch", $customPayload)
            ->assertRedirect();
        $this->assertDatabaseCount('cv_custom_sections', 1);
        $this->assertDatabaseHas('cv_custom_sections', ['section_title' => 'Publications']);
    }

    public function test_batch_returns_json_response_for_ajax_calls(): void
    {
        $payload = [
            'items' => [
                'temp_1' => [
                    'name' => 'React',
                    'level' => 'Advanced',
                    'rating' => 88,
                    'category' => 'Frontend',
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/cvs/{$this->cv->id}/builder/items/skills/batch", $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Changes saved successfully.',
        ]);
    }

    public function test_unauthorized_user_cannot_batch_save_to_other_users_cv(): void
    {
        $otherUser = User::factory()->create();

        $payload = [
            'items' => [
                'temp_1' => [
                    'name' => 'Malicious Injection',
                    'level' => 'Expert',
                ],
            ],
        ];

        $response = $this->actingAs($otherUser)
            ->post("/cvs/{$this->cv->id}/builder/items/skills/batch", $payload);

        $response->assertStatus(403);
        $this->assertDatabaseCount('cv_skills', 0);
    }
}
