<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Exception;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Tạo roles cần thiết cho test
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);
    }

    /** @test */
    public function user_can_be_created_with_valid_data()
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'username' => 'john-doe',
            'is_active' => true,
        ];

        $user = User::create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John', $user->first_name);
        $this->assertEquals('Doe', $user->last_name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertTrue($user->is_active);
    }

    /** @test */
    public function user_full_name_accessor_works_correctly()
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);

        $this->assertEquals('John Doe', $user->full_name);
    }

    /** @test */
    public function username_mutator_creates_slug()
    {
        $user = User::factory()->create([
            'username' => 'John Doe User'
        ]);

        $this->assertEquals('john-doe-user', $user->username);
    }

    /** @test */
    public function user_has_many_tasks_relationship()
    {
        $user = User::factory()->create();
        $task1 = Task::factory()->create(['user_id' => $user->id]);
        $task2 = Task::factory()->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->tasks);
        $this->assertTrue($user->tasks->contains($task1));
        $this->assertTrue($user->tasks->contains($task2));
    }

    /** @test */
    public function user_belongs_to_many_roles_relationship()
    {
        $user = User::factory()->create();
        $role = Role::first();
        
        $user->roles()->attach($role);

        $this->assertCount(1, $user->roles);
        $this->assertTrue($user->roles->contains($role));
    }

    /** @test */
    public function admin_user_gets_admin_role_on_creation()
    {
        $user = User::factory()->create(['is_admin' => true]);

        $this->assertTrue($user->roles->contains('name', 'admin'));
    }

    /** @test */
    public function regular_user_gets_user_role_on_creation()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->assertTrue($user->roles->contains('name', 'user'));
    }

    /** @test */
    public function cannot_delete_last_admin_user()
    {
        // Tạo một admin user duy nhất
        $adminUser = User::factory()->create(['is_admin' => true]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot delete the last admin user.');

        $adminUser->delete();
    }

    /** @test */
    public function can_delete_admin_user_when_multiple_admins_exist()
    {
        // Tạo 2 admin users
        $adminUser1 = User::factory()->create(['is_admin' => true]);
        $adminUser2 = User::factory()->create(['is_admin' => true]);

        // Xóa một admin không gây lỗi
        $adminUser1->delete();

        $this->assertDatabaseMissing('users', ['id' => $adminUser1->id]);
        $this->assertDatabaseHas('users', ['id' => $adminUser2->id]);
    }

    /** @test */
    public function can_delete_regular_user()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $user->delete();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /** @test */
    public function role_is_updated_when_user_admin_status_changes()
    {
        $user = User::factory()->create(['is_admin' => false]);
        
        // User ban đầu có role 'user'
        $this->assertTrue($user->roles->contains('name', 'user'));

        // Cập nhật thành admin
        $user->update(['is_admin' => true]);
        $user->refresh();

        // Kiểm tra role đã được sync thành admin
        $this->assertTrue($user->roles->contains('name', 'admin'));
        $this->assertFalse($user->roles->contains('name', 'user'));
    }

    /** @test */
    public function password_is_hidden_in_serialization()
    {
        $user = User::factory()->create(['password' => 'secret123']);

        $userArray = $user->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
    }

    /** @test */
    public function is_admin_field_is_guarded()
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true, // Thử gán mass assignment
        ];

        $user = User::create($userData);

        // is_admin không được gán qua mass assignment do guarded
        $this->assertNull($user->is_admin);
    }
}
