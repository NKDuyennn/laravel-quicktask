<?php

namespace Tests\Unit\Policies;

use Tests\TestCase;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new UserPolicy();
    }

    /** @test */
    public function admin_can_view_any_users()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $result = $this->policy->viewAny($admin);

        $this->assertTrue($result);
    }

    /** @test */
    public function regular_user_cannot_view_any_users()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $result = $this->policy->viewAny($user);

        $this->assertFalse($result);
    }

    /** @test */
    public function admin_can_view_any_user()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $targetUser = User::factory()->create();

        $result = $this->policy->view($admin, $targetUser);

        $this->assertTrue($result);
    }

    /** @test */
    public function user_can_view_their_own_profile()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $result = $this->policy->view($user, $user);

        $this->assertTrue($result);
    }

    /** @test */
    public function user_cannot_view_other_users_profile()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $otherUser = User::factory()->create(['is_admin' => false]);

        $result = $this->policy->view($user, $otherUser);

        $this->assertFalse($result);
    }

    /** @test */
    public function admin_can_create_users()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $result = $this->policy->create($admin);

        $this->assertTrue($result);
    }

    /** @test */
    public function regular_user_cannot_create_users()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $result = $this->policy->create($user);

        $this->assertFalse($result);
    }

    /** @test */
    public function admin_can_update_any_user()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $targetUser = User::factory()->create();

        $result = $this->policy->update($admin, $targetUser);

        $this->assertTrue($result);
    }

    /** @test */
    public function user_can_update_their_own_profile()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $result = $this->policy->update($user, $user);

        $this->assertTrue($result);
    }

    /** @test */
    public function user_cannot_update_other_users_profile()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $otherUser = User::factory()->create(['is_admin' => false]);

        $result = $this->policy->update($user, $otherUser);

        $this->assertFalse($result);
    }

    /** @test */
    public function admin_can_delete_any_user()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $targetUser = User::factory()->create();

        $result = $this->policy->delete($admin, $targetUser);

        $this->assertTrue($result);
    }

    /** @test */
    public function user_can_delete_their_own_account()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $result = $this->policy->delete($user, $user);

        $this->assertTrue($result);
    }

    /** @test */
    public function user_cannot_delete_other_users_account()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $otherUser = User::factory()->create(['is_admin' => false]);

        $result = $this->policy->delete($user, $otherUser);

        $this->assertFalse($result);
    }

    /** @test */
    public function admin_can_restore_any_user()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $targetUser = User::factory()->create();

        $result = $this->policy->restore($admin, $targetUser);

        $this->assertTrue($result);
    }

    /** @test */
    public function user_can_restore_their_own_account()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $result = $this->policy->restore($user, $user);

        $this->assertTrue($result);
    }

    /** @test */
    public function user_cannot_restore_other_users_account()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $otherUser = User::factory()->create(['is_admin' => false]);

        $result = $this->policy->restore($user, $otherUser);

        $this->assertFalse($result);
    }

    /** @test */
    public function admin_can_force_delete_any_user()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $targetUser = User::factory()->create();

        $result = $this->policy->forceDelete($admin, $targetUser);

        $this->assertTrue($result);
    }

    /** @test */
    public function user_can_force_delete_their_own_account()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $result = $this->policy->forceDelete($user, $user);

        $this->assertTrue($result);
    }

    /** @test */
    public function user_cannot_force_delete_other_users_account()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $otherUser = User::factory()->create(['is_admin' => false]);

        $result = $this->policy->forceDelete($user, $otherUser);

        $this->assertFalse($result);
    }
}
