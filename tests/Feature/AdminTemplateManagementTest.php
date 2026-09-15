<?php

namespace Tests\Feature;

use App\Models\Cv;
use App\Models\CvTemplate;
use App\Models\TemplateCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTemplateManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;
    protected TemplateCategory $category;
    protected CvTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\TemplateSeeder::class);

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
        $this->category = TemplateCategory::first();
        $this->template = CvTemplate::first();
    }

    public function test_admin_can_view_templates_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.templates.index'));
        $response->assertOk();
        $response->assertSee('CV Templates Directory');
        $response->assertSee($this->template->name);
    }

    public function test_non_admin_cannot_access_template_management(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.templates.index'));
        $response->assertForbidden();

        $categoryResponse = $this->actingAs($this->user)->get(route('admin.templates.categories.index'));
        $categoryResponse->assertForbidden();
    }

    public function test_admin_can_create_template_with_image_upload(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('custom_preview.png', 400, 520);

        $response = $this->actingAs($this->admin)->post(route('admin.templates.store'), [
            'category_id' => $this->category->id,
            'name' => 'Nordic Minimalist',
            'key' => 'nordic-minimalist',
            'description' => 'A clean scandinavian layout.',
            'is_premium' => 1,
            'is_active' => 1,
            'sort_order' => 10,
            'preview_image' => $file,
        ]);

        $response->assertRedirect(route('admin.templates.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cv_templates', [
            'name' => 'Nordic Minimalist',
            'key' => 'nordic-minimalist',
            'is_premium' => true,
        ]);

        $created = CvTemplate::where('key', 'nordic-minimalist')->first();
        $this->assertNotNull($created->preview_image);
        Storage::disk('public')->assertExists($created->preview_image);
    }

    public function test_image_upload_validation_rejects_non_image_files(): void
    {
        Storage::fake('public');

        $invalidFile = UploadedFile::fake()->create('script.exe', 100, 'application/x-msdownload');

        $response = $this->actingAs($this->admin)->post(route('admin.templates.store'), [
            'category_id' => $this->category->id,
            'name' => 'Exploit Template',
            'key' => 'exploit-template',
            'preview_image' => $invalidFile,
        ]);

        $response->assertSessionHasErrors(['preview_image']);
    }

    public function test_admin_can_toggle_free_premium_and_status(): void
    {
        $initialPremium = $this->template->is_premium;

        $response = $this->actingAs($this->admin)->post(route('admin.templates.toggle-premium', $this->template));
        $response->assertRedirect();
        $this->template->refresh();
        $this->assertEquals(!$initialPremium, $this->template->is_premium);

        $initialActive = $this->template->is_active;
        $responseStatus = $this->actingAs($this->admin)->post(route('admin.templates.toggle-status', $this->template));
        $responseStatus->assertRedirect();
        $this->template->refresh();
        $this->assertEquals(!$initialActive, $this->template->is_active);
    }

    public function test_safe_deletion_blocks_deleting_template_used_by_cvs(): void
    {
        // Create a CV using this template
        Cv::create([
            'user_id' => $this->user->id,
            'template_id' => $this->template->id,
            'title' => 'Active User Resume',
            'status' => 'draft',
            'template_key' => $this->template->key,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.templates.destroy', $this->template));
        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Verify template was not deleted
        $this->assertDatabaseHas('cv_templates', ['id' => $this->template->id]);
    }

    public function test_safe_deletion_blocks_deleting_category_containing_templates(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.templates.categories.destroy', $this->category));
        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Verify category was not deleted
        $this->assertDatabaseHas('template_categories', ['id' => $this->category->id]);
    }
}
