<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Role;

class UsersController extends Controller
{
    public function index()
    {
        $tenant = Tenant::query()->where('slug', 'nexora')->firstOrFail();
        $users = User::query()->with('roles')->get();
        $roles = Role::query()->where('tenant_id', $tenant->id)->get();

        return view('admin.users', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        $tenant = Tenant::query()->where('slug', 'nexora')->firstOrFail();
        $roles = Role::query()->where('tenant_id', $tenant->id)->get();
        
        return view('admin.users_create', ['roles' => $roles]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_ids' => ['array'],
            'role_ids.*' => ['exists:roles,id'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($request->has('role_ids')) {
            $user->roles()->sync($request->role_ids);
        }

        return redirect()->route('admin.users')->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(User $user)
    {
        $tenant = Tenant::query()->where('slug', 'nexora')->firstOrFail();
        $roles = Role::query()->where('tenant_id', $tenant->id)->get();

        return view('admin.users_edit', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_ids' => ['array'],
            'role_ids.*' => ['exists:roles,id'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        if ($request->has('role_ids')) {
            $user->roles()->sync($request->role_ids);
        }

        return redirect()->route('admin.users')->with('success', 'Utilisateur mis à jour avec succès.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'Utilisateur supprimé.');
    }

    public function syncRoles(Request $request, User $user)
    {
        // Kept for backward compatibility if needed, but likely replaced by edit
        $tenant = Tenant::query()->where('slug', 'nexora')->firstOrFail();
        $roles = Role::query()->where('tenant_id', $tenant->id)->pluck('id')->all();

        $roleIds = collect($request->input('role_ids', []))
            ->map('intval')
            ->filter(function ($id) use ($roles) {
                return in_array($id, $roles, true);
            })
            ->all();

        $user->roles()->sync($roleIds);

        return redirect()->route('admin.users');
    }
}
