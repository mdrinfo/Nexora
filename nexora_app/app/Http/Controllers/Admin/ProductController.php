<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Tenant;
use App\Models\InventoryItem;
use App\Models\ProductOptionGroup;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with('category');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $products = $query->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => $categories
        ]);
    }

    public function create()
    {
        $categories = Category::query()->orderBy('name')->get();
        $optionGroups = ProductOptionGroup::where('tenant_id', 1)->orderBy('name')->get();
        $products = Product::whereNull('parent_id')->orderBy('name')->get(); // Only main products as parents

        return view('admin.products.create', [
            'categories' => $categories,
            'optionGroups' => $optionGroups,
            'products' => $products
        ]);
    }

    public function store(Request $request)
    {
        $tenant = Tenant::query()->where('slug', 'nexora')->firstOrFail();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'parent_id' => ['nullable', 'exists:products,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'option_groups' => ['nullable', 'array'],
            'option_groups.*' => ['exists:product_option_groups,id'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $data['name'],
            'category_id' => $data['category_id'],
            'parent_id' => $data['parent_id'] ?? null,
            'price' => $data['price'],
            'image_path' => $imagePath,
            'is_active' => true,
        ]);

        if (isset($data['option_groups'])) {
            $product->optionGroups()->sync($data['option_groups']);
        }

        return redirect()->route('admin.products.index')->with('success', 'Produit créé.');
    }

    public function edit(Product $product)
    {
        $categories = Category::query()->orderBy('name')->get();
        $inventoryItems = InventoryItem::query()->orderBy('name')->get();
        $optionGroups = ProductOptionGroup::where('tenant_id', 1)->orderBy('name')->get();
        $parentProducts = Product::whereNull('parent_id')->where('id', '!=', $product->id)->orderBy('name')->get();
        
        $product->load(['ingredients', 'optionGroups']); // Load existing recipe items and option groups

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => $categories,
            'inventoryItems' => $inventoryItems,
            'optionGroups' => $optionGroups,
            'parentProducts' => $parentProducts
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'parent_id' => ['nullable', 'exists:products,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
            'option_groups' => ['nullable', 'array'],
            'option_groups.*' => ['exists:product_option_groups,id'],
        ]);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $product->image_path = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'name' => $data['name'],
            'category_id' => $data['category_id'],
            'parent_id' => $data['parent_id'] ?? null,
            'price' => $data['price'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (isset($data['option_groups'])) {
            $product->optionGroups()->sync($data['option_groups']);
        } else {
            $product->optionGroups()->detach();
        }
        
        // Handle image update if needed separately or above

        return redirect()->route('admin.products.index')->with('success', 'Produit mis à jour.');
    }

    public function updateRecipe(Request $request, Product $product)
    {
        // Sync ingredients
        // Expecting array of {inventory_item_id, quantity, unit}
        // Simplified: delete all and recreate
        
        $data = $request->validate([
            'ingredients' => ['array'],
            'ingredients.*.id' => ['required', 'exists:inventory_items,id'],
            'ingredients.*.quantity' => ['required', 'numeric', 'min:0.0001'],
        ]);

        $product->ingredients()->detach();

        if (isset($data['ingredients'])) {
            foreach ($data['ingredients'] as $ing) {
                $invItem = InventoryItem::find($ing['id']);
                $product->ingredients()->attach($ing['id'], [
                    'quantity' => $ing['quantity'],
                    'unit' => $invItem->unit // Store unit for reference, though usually comes from item
                ]);
            }
        }

        return redirect()->back()->with('success', 'Recette mise à jour.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Produit supprimé.');
    }
}
