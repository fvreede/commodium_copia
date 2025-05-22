<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use Illuminatte\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class CategoryStructureControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $admin = User::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $admin->assignRole($role);
        $this->actingAs($admin);
    }
    public function test_admin_can_view_categories(): void
    {
        $category = Category::factory()->create();

        $response = $this->get(route('admin.categories.index'));

        $response->assertStatus(200)
            ->assertInertia(fn ($assert) => $assert
                ->component('Admin/Categories/Index')
                ->has('categories', 1)
            );
    }

    public function test_admin_can_create_category_with_image(): void
    {
        $image = UploadedFile::fake()->image('category.jpg');

        $response = $this->post(route('admin.categories.store'), [
            'name' => 'Test Category',
            'description' => 'Test Description',
            'image' => $image,
            'imagePosition' => [
                'x' => 0,
                'y' => 0
            ]
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'description' => 'Test Description'
        ]);
        Storage::disk('public')->assertExists('images/categories/' . $image->hashName());
    }

    public function test_admin_can_update_category(): void
    {
        $category = Category::factory()->create();
        $newImage = UploadedFile::fake()->image('new-category.jpg');

        $response = $this->put(route('admin.categories.update', $category), [
            'name' => 'Updated Category',
            'description' => 'Updated Description',
            'image' => $newImage,
            'imagePosition' => [
                'x' => 100,
                'y' => 100
            ]
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
            'description' => 'Updated Description'
        ]);
    }

    public function test_admin_cannot_delete_category_with_subcategories(): void
    {
        $category = Category::factory()
            ->hasSubcategories(1)
            ->create();

        $response = $this->delete(route('admin.categories.destroy', $category));

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }
}
