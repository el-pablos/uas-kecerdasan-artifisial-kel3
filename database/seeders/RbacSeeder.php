<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Seeder untuk RBAC roles & permissions CTI platform.
 *
 * Roles:
 * - admin: Full access — semua module + settings
 * - analyst: CRUD entities, cases, observations. Read ingestion.
 * - viewer: Read-only access ke semua module.
 */
class RbacSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // === PERMISSIONS ===
        $permissions = [
            // Knowledge
            'knowledge.view',
            'knowledge.create',
            'knowledge.edit',
            'knowledge.delete',

            // Threats
            'threats.view',
            'threats.create',
            'threats.edit',
            'threats.delete',

            // Observations
            'observations.view',
            'observations.promote',
            'observations.triage',

            // Cases
            'cases.view',
            'cases.create',
            'cases.edit',
            'cases.delete',

            // Ingestion
            'ingestion.view',
            'ingestion.import',
            'ingestion.run-connector',

            // Settings
            'settings.users',
            'settings.tokens',
            'settings.taxonomy',
            'settings.audit',

            // Search
            'search.global',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // === ROLES ===
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions); // All permissions

        $analyst = Role::firstOrCreate(['name' => 'analyst', 'guard_name' => 'web']);
        $analyst->syncPermissions([
            'knowledge.view', 'knowledge.create', 'knowledge.edit',
            'threats.view', 'threats.create', 'threats.edit',
            'observations.view', 'observations.promote', 'observations.triage',
            'cases.view', 'cases.create', 'cases.edit',
            'ingestion.view',
            'settings.tokens',
            'search.global',
        ]);

        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions([
            'knowledge.view',
            'threats.view',
            'observations.view',
            'cases.view',
            'ingestion.view',
            'search.global',
        ]);

        $this->command->info('RBAC seeded: 3 roles, ' . count($permissions) . ' permissions.');
    }
}
