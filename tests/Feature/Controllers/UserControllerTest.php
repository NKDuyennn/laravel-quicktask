<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Tạo roles cần thiết
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);
    }

    /** @test */
    public function admin_can_view_users_index()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        
        $response = $this->actingAs($admin)->get('/users');
        
        $response->assertStatus(200);
        $response->assertViewIs('users.index');
        $response->assertViewHas('users');
    }

    /** @test */
    public function regular_user_cannot_view_users_index()
    {
        $user = User::factory()->create(['is_admin' => false]);
        
        $response = $this->actingAs($user)->get('/users');
        
        $response->assertStatus(403); // Forbidden
    }

    /** @test */
    public function guest_cannot_view_users_index()
    {
        $response = $this->get('/users');
        
        $response->assertRedirect('/login');
    }

    /** @test */
    public function admin_can_view_create_user_form()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        
        $response = $this->actingAs($admin)->get('/users/create');
        
        $response->assertStatus(200);
        $response->assertViewIs('users.create');
    }

    /** @test */
    public function regular_user_cannot_view_create_user_form()
    {
        $user = User::factory()->create(['is_admin' => false]);
        
        $response = $this->actingAs($user)->get('/users/create');
        
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_create_new_user()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'username' => 'johndoe',
            'is_active' => true,
        ];
        
        $response = $this->actingAs($admin)->post('/users', $userData);
        
        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    }

    /** @test */
    public function admin_can_view_user_details()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $targetUser = User::factory()->create();
        
        $response = $this->actingAs($admin)->get("/users/{$targetUser->id}");
        
        $response->assertStatus(200);
        $response->assertViewIs('users.show');
        $response->assertViewHas('user', $targetUser);
    }

    /** @test */
    public function user_can_view_their_own_details()
    {
        $user = User::factory()->create(['is_admin' => false]);
        
        $response = $this->actingAs($user)->get("/users/{$user->id}");
        
        $response->assertStatus(200);
        $response->assertViewIs('users.show');
    }

    /** @test */
    public function user_cannot_view_other_users_details()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $otherUser = User::factory()->create(['is_admin' => false]);
        
        $response = $this->actingAs($user)->get("/users/{$otherUser->id}");
        
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_user()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $targetUser = User::factory()->create([
            'first_name' => 'Old Name',
            'email' => 'old@example.com'
        ]);
        
        $updateData = [
            'first_name' => 'New Name',
            'last_name' => 'Updated',
            'email' => 'new@example.com',
            'username' => 'newusername',
            'is_active' => true,
        ];
        
        $response = $this->actingAs($admin)->put("/users/{$targetUser->id}", $updateData);
        
        $response->assertRedirect("/users/{$targetUser->id}");
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'first_name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    /** @test */
    public function user_can_update_their_own_profile()
    {
        $user = User::factory()->create([
            'first_name' => 'Old Name',
            'is_admin' => false
        ]);
        
        $updateData = [
            'first_name' => 'New Name',
            'last_name' => 'Updated',
            'email' => $user->email,
            'username' => 'newusername',
            'is_active' => true,
        ];
        
        $response = $this->actingAs($user)->put("/users/{$user->id}", $updateData);
        
        $response->assertRedirect("/users/{$user->id}");
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'New Name',
        ]);
    }

    /** @test */
    public function admin_can_delete_user()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $targetUser = User::factory()->create(['is_admin' => false]);
        
        $response = $this->actingAs($admin)->delete("/users/{$targetUser->id}");
        
        $response->assertRedirect('/users');
        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    /** @test */
    public function validation_errors_when_creating_user_with_invalid_data()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        
        $invalidData = [
            'first_name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
        ];
        
        $response = $this->actingAs($admin)->post('/users', $invalidData);
        
        $response->assertSessionHasErrors(['first_name', 'email', 'password']);
    }

    /** @test */
    public function users_index_loads_tasks_with_eager_loading()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        
        // Tạo một số tasks cho user
        \App\Models\Task::factory()->count(3)->create(['user_id' => $user->id]);
        
        $response = $this->actingAs($admin)->get('/users');
        
        $response->assertStatus(200);
        
        // Kiểm tra view có dữ liệu users với tasks
        $users = $response->viewData('users');
        $this->assertTrue($users->first()->relationLoaded('tasks'));
    }
}
