<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Tenant;
use App\Models\Role;
use App\Models\Permission;

class RolesPermissionsController extends Controller
{
    public function index()
    {
        $tenant = Tenant::query()->where('slug', 'nexora')->firstOrFail();
        $roles = Role::query()->where('tenant_id', $tenant->id)->with('permissions')->get();
        $permissions = Permission::query()->where('tenant_id', $tenant->id)->get();

        return view('admin.roles_permissions', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function syncRolePermissions(Request $request, Role $role)
    {
        $tenant = Tenant::query()->where('slug', 'nexora')->firstOrFail();
        if ((int) $role->tenant_id !== (int) $tenant->id) {
            abort(403);
        }
        $permIds = Permission::query()->where('tenant_id', $tenant->id)->pluck('id')->all();
        $selected = collect($request->input('permission_ids', []))
            ->map('intval')
            ->filter(function ($id) use ($permIds) {
                return in_array($id, $permIds, true);
            })
            ->all();
        $role->permissions()->sync($selected);
        return redirect()->route('admin.roles_permissions');
    }
}
