<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Category;
use App\Models\Tenant;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()->orderBy('name')->get();
        return view('admin.categories.index', ['categories' => $categories]);
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        // Assuming single tenant 'nexora' for now as per other controllers
        $tenant = Tenant::query()->where('slug', 'nexora')->firstOrFail();
        
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_drink' => ['boolean'],
            'destination' => ['required', 'in:kitchen,bar'],
        ]);

        Category::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $data['name'],
            'is_drink' => $request->boolean('is_drink'),
            'destination' => $data['destination'],
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie créée avec succès.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', ['category' => $category]);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_drink' => ['boolean'],
            'destination' => ['required', 'in:kitchen,bar'],
        ]);

        $category->update([
            'name' => $data['name'],
            'is_drink' => $request->boolean('is_drink'),
            'destination' => $data['destination'],
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie mise à jour avec succès.');
    }

    public function destroy(Category $category)
    {
        // Check if has products? For now simple delete.
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Catégorie supprimée.');
    }

    public function autoDetectDrinks()
    {
        // Keywords safe to use with partial match (LIKE %keyword%)
        // These are distinct enough that they are unlikely to appear inside non-drink words
        $safeKeywords = [
            'boisson', 'drink', 'biere', 'bière', 'beer', 'soda', 'cocktail', 'spiritueux', 
            'icecek', 'içecek', 'meşrubat', 'mesrubat', 'ayran', 'kola', 'fanta', 'gazoz', 
            'sarap', 'şarap', 'raki', 'rakı', 'votka', 'viski', 'likör', 'konyak', 'champagne',
            'bira', 'coffee'
        ];

        // Keywords requiring word boundaries to avoid false positives
        // e.g. 'su' in 'sushi', 'vin' in 'vinaigrette', 'jus' in 'juste'
        $strictKeywords = [
            'eau', 'water', 'vin', 'jus', 'thé', 'tea', 'su', 'cin', 'café', 'cafe'
        ];
        
        $count = 0;

        // 1. Safe Keywords (Partial Match)
        foreach ($safeKeywords as $keyword) {
             $affected = Category::query()
                ->where('name', 'LIKE', '%' . $keyword . '%')
                ->where(function($q) {
                    $q->where('destination', '!=', 'bar')
                      ->orWhere('is_drink', false);
                })
                ->update(['is_drink' => true, 'destination' => 'bar']);
             $count += $affected;
        }

        // 2. Strict Keywords (Word Boundaries)
        foreach ($strictKeywords as $keyword) {
             $affected = Category::query()
                ->where(function($q) use ($keyword) {
                    $q->where('name', $keyword)
                      ->orWhere('name', 'LIKE', $keyword . ' %')
                      ->orWhere('name', 'LIKE', '% ' . $keyword)
                      ->orWhere('name', 'LIKE', '% ' . $keyword . ' %');
                })
                ->where(function($q) {
                    $q->where('destination', '!=', 'bar')
                      ->orWhere('is_drink', false);
                })
                ->update(['is_drink' => true, 'destination' => 'bar']);
             $count += $affected;
        }

        return redirect()->route('admin.categories.index')
            ->with('success', $count . ' catégorie(s) mise(s) à jour vers le Bar.');
    }
}
