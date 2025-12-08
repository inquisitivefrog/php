<?php

namespace Tests\Unit\Policies;

use App\Models\Cow;
use App\Models\User;
use App\Policies\CowPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CowPolicyTest extends TestCase
{
    use RefreshDatabase;

    private CowPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new CowPolicy();
    }

    /**
     * Test: viewAny allows all authenticated users
     */
    public function test_view_any_allows_all_users(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($this->policy->viewAny($user));
    }

    /**
     * Test: view allows all authenticated users
     */
    public function test_view_allows_all_users(): void
    {
        $user = User::factory()->create();
        $cow = Cow::factory()->create();
        $this->assertTrue($this->policy->view($user, $cow));
    }

    /**
     * Test: create allows all authenticated users
     */
    public function test_create_allows_all_users(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($this->policy->create($user));
    }

    /**
     * Test: update allows admin users
     */
    public function test_update_allows_admin_users(): void
    {
        $adminUser = User::factory()->create(['email' => 'admin@example.com']);
        $cow = Cow::factory()->create();
        
        $result = $this->policy->update($adminUser, $cow);
        $this->assertTrue($result);
    }

    /**
     * Test: update denies non-admin users
     */
    public function test_update_denies_non_admin_users(): void
    {
        $regularUser = User::factory()->create(['email' => 'user@example.com']);
        $cow = Cow::factory()->create();
        
        $result = $this->policy->update($regularUser, $cow);
        $this->assertInstanceOf(\Illuminate\Auth\Access\Response::class, $result);
        $this->assertFalse($result->allowed());
    }

    /**
     * Test: update allows users with @admin.example.com email
     */
    public function test_update_allows_users_with_admin_example_com_email(): void
    {
        $adminUser = User::factory()->create(['email' => 'test@admin.example.com']);
        $cow = Cow::factory()->create();
        
        $result = $this->policy->update($adminUser, $cow);
        $this->assertTrue($result);
    }

    /**
     * Test: delete allows admin users
     */
    public function test_delete_allows_admin_users(): void
    {
        $adminUser = User::factory()->create(['email' => 'admin@example.com']);
        $cow = Cow::factory()->create();
        
        $result = $this->policy->delete($adminUser, $cow);
        $this->assertTrue($result);
    }

    /**
     * Test: delete denies non-admin users
     */
    public function test_delete_denies_non_admin_users(): void
    {
        $regularUser = User::factory()->create(['email' => 'user@example.com']);
        $cow = Cow::factory()->create();
        
        $result = $this->policy->delete($regularUser, $cow);
        $this->assertInstanceOf(\Illuminate\Auth\Access\Response::class, $result);
        $this->assertFalse($result->allowed());
    }

    /**
     * Test: restore allows admin users
     */
    public function test_restore_allows_admin_users(): void
    {
        $adminUser = User::factory()->create(['email' => 'admin@example.com']);
        $cow = Cow::factory()->create();
        
        $this->assertTrue($this->policy->restore($adminUser, $cow));
    }

    /**
     * Test: restore denies non-admin users
     */
    public function test_restore_denies_non_admin_users(): void
    {
        $regularUser = User::factory()->create(['email' => 'user@example.com']);
        $cow = Cow::factory()->create();
        
        $this->assertFalse($this->policy->restore($regularUser, $cow));
    }

    /**
     * Test: forceDelete allows admin users
     */
    public function test_force_delete_allows_admin_users(): void
    {
        $adminUser = User::factory()->create(['email' => 'admin@example.com']);
        $cow = Cow::factory()->create();
        
        $this->assertTrue($this->policy->forceDelete($adminUser, $cow));
    }

    /**
     * Test: forceDelete denies non-admin users
     */
    public function test_force_delete_denies_non_admin_users(): void
    {
        $regularUser = User::factory()->create(['email' => 'user@example.com']);
        $cow = Cow::factory()->create();
        
        $this->assertFalse($this->policy->forceDelete($regularUser, $cow));
    }
}

