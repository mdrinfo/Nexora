<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->where('slug', 'nexora')->firstOrFail();

        $roles = [
            ['key' => 'owner', 'name' => 'Propriétaire'],
            ['key' => 'manager', 'name' => 'Gestionnaire'],
            ['key' => 'chef', 'name' => 'Chef'],
            ['key' => 'waiter', 'name' => 'Serveur'],
            ['key' => 'cleaner', 'name' => 'Agent de nettoyage'],
        ];

        $roleIds = [];
        foreach ($roles as $r) {
            $role = Role::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'key' => $r['key']],
                ['name' => $r['name'], 'description' => null]
            );
            $roleIds[$r['key']] = $role->id;
        }

        $permissions = [
            ['key' => 'can_create_order', 'name' => 'Créer commande'],
            ['key' => 'can_delete_order', 'name' => 'Supprimer commande'],
            ['key' => 'can_close_session', 'name' => 'Clore session'],
            ['key' => 'can_edit_stock', 'name' => 'Modifier stock'],
            ['key' => 'can_manage_reservations', 'name' => 'Gérer réservations'],
            ['key' => 'can_assign_qr_key', 'name' => 'Assigner QR-Key'],
            ['key' => 'can_view_reports', 'name' => 'Voir rapports'],
        ];

        $permIds = [];
        foreach ($permissions as $p) {
            $perm = Permission::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'key' => $p['key']],
                ['name' => $p['name'], 'description' => null]
            );
            $permIds[$p['key']] = $perm->id;
        }

        $owner = Role::find($roleIds['owner']);
        $manager = Role::find($roleIds['manager']);
        $chef = Role::find($roleIds['chef']);

        $owner->permissions()->syncWithoutDetaching(array_values($permIds));
        $manager->permissions()->syncWithoutDetaching(array_values($permIds));
        $chef->permissions()->syncWithoutDetaching([
            $permIds['can_create_order'],
            $permIds['can_edit_stock'],
        ]);
    }
}
