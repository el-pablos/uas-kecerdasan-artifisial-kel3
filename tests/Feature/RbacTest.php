<?php

namespace Tests\Feature;

use App\Models\Node;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    private function setupRoles(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'knowledge.view', 'knowledge.create', 'knowledge.edit', 'knowledge.delete',
            'cases.view', 'cases.create', 'cases.edit', 'cases.delete',
            'settings.users',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions);

        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions(['knowledge.view', 'cases.view']);
    }

    /** @test */
    public function admin_has_all_permissions()
    {
        $this->setupRoles();
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertTrue($user->hasPermissionTo('knowledge.create'));
        $this->assertTrue($user->hasPermissionTo('knowledge.delete'));
        $this->assertTrue($user->hasPermissionTo('settings.users'));
    }

    /** @test */
    public function viewer_only_has_view_permissions()
    {
        $this->setupRoles();
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $this->assertTrue($user->hasPermissionTo('knowledge.view'));
        $this->assertFalse($user->hasPermissionTo('knowledge.create'));
        $this->assertFalse($user->hasPermissionTo('knowledge.delete'));
    }

    /** @test */
    public function admin_can_assign_role()
    {
        $this->setupRoles();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $targetUser = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('settings.users.assign-role', $targetUser), ['role' => 'viewer'])
            ->assertRedirect();

        $targetUser->refresh();
        $this->assertTrue($targetUser->hasRole('viewer'));
    }

    /** @test */
    public function node_policy_view_authorized_for_users_with_permission()
    {
        $this->setupRoles();
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $node = Node::create(['type' => 'malware', 'name' => 'TestMalware']);

        $this->assertTrue($user->can('view', $node));
        $this->assertFalse($user->can('delete', $node));
    }

    /** @test */
    public function settings_users_page_loads_for_authenticated()
    {
        $this->setupRoles();
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get(route('settings.users'))
            ->assertStatus(200)
            ->assertSee('Users', false);
    }
}
