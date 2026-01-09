<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Supplier;
use App\Models\Tenant;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::query()->withCount('inventoryItems')->orderBy('name')->get();
        return view('admin.suppliers.index', ['suppliers' => $suppliers]);
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $tenant = Tenant::query()->where('slug', 'nexora')->firstOrFail();
        
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        Supplier::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $data['name'],
            'contact_name' => $data['contact_name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
        ]);

        return redirect()->route('admin.suppliers.index')->with('success', 'Fournisseur ajouté.');
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', ['supplier' => $supplier]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $supplier->update($data);

        return redirect()->route('admin.suppliers.index')->with('success', 'Fournisseur mis à jour.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('admin.suppliers.index')->with('success', 'Fournisseur supprimé.');
    }
}
