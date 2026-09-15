<?php

namespace Tests\Feature;

use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DocumentTypeSeeder::class);

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
    }

    public function test_admin_can_view_document_types_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.document-types.index'));
        $response->assertOk();
        $response->assertSee('Career Document Types');
        $response->assertSee('Standard CV');
        $response->assertSee('Cover Letter');
        $response->assertSee('Motivation Letter');
    }

    public function test_non_admin_cannot_access_document_types_management(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.document-types.index'));
        $response->assertForbidden();

        $responseCreate = $this->actingAs($this->user)->get(route('admin.document-types.create'));
        $responseCreate->assertForbidden();
    }

    public function test_admin_can_create_new_document_type(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.document-types.store'), [
            'name' => 'Portfolio Summary',
            'description' => 'Creative portfolio summary layout',
            'icon' => 'bi bi-briefcase',
            'sort_order' => 10,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.document-types.index'));
        $this->assertDatabaseHas('document_types', [
            'name' => 'Portfolio Summary',
            'slug' => 'portfolio-summary',
        ]);
    }

    public function test_admin_can_update_document_type(): void
    {
        $docType = DocumentType::where('slug', 'cover-letter')->first();
        $this->assertNotNull($docType);

        $response = $this->actingAs($this->admin)->put(route('admin.document-types.update', $docType), [
            'name' => 'Executive Cover Letter',
            'slug' => 'cover-letter',
            'description' => 'Updated executive cover letter description',
            'icon' => 'bi bi-envelope-open',
            'sort_order' => 6,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.document-types.index'));
        $this->assertDatabaseHas('document_types', [
            'id' => $docType->id,
            'name' => 'Executive Cover Letter',
        ]);
    }

    public function test_admin_can_toggle_document_type_status(): void
    {
        $docType = DocumentType::where('slug', 'ats-cv')->first();
        $this->assertTrue((bool)$docType->is_active);

        $response = $this->actingAs($this->admin)->post(route('admin.document-types.toggle-status', $docType));
        $response->assertRedirect();

        $docType->refresh();
        $this->assertFalse((bool)$docType->is_active);
    }
}
