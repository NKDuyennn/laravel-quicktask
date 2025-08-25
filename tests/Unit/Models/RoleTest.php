<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function role_can_be_created()
    {
        $role = Role::create([
            'name' => 'admin',
            'description' => 'Administrator role'
        ]);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals('admin', $role->name);
        $this->assertEquals('Administrator role', $role->description);
    }

    /** @test */
    public function role_belongs_to_many_users()
    {
        $role = Role::factory()->create(['name' => 'admin']);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $role->users()->attach([$user1->id, $user2->id]);

        $this->assertCount(2, $role->users);
        $this->assertTrue($role->users->contains($user1));
        $this->assertTrue($role->users->contains($user2));
    }

    /** @test */
    public function role_relationship_has_timestamps()
    {
        $role = Role::factory()->create();
        $user = User::factory()->create();

        $role->users()->attach($user->id);

        $pivot = $role->users()->first()->pivot;
        $this->assertNotNull($pivot->created_at);
        $this->assertNotNull($pivot->updated_at);
    }

    /** @test */
    public function role_uses_has_factory_trait()
    {
        $this->assertTrue(in_array('Illuminate\Database\Eloquent\Factories\HasFactory', class_uses(Role::class)));
    }

    /** @test */
    public function role_has_no_guarded_fields()
    {
        $role = new Role();
        
        // Kiểm tra rằng guarded array rỗng (không có field nào bị bảo vệ)
        $this->assertEquals([], $role->getGuarded());
    }
}
