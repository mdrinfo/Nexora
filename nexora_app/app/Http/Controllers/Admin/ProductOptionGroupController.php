<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductOptionGroup;
use App\Models\ProductOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductOptionGroupController extends Controller
{
    public function index()
    {
        $groups = ProductOptionGroup::where('tenant_id', 1)->with('options')->get();
        return view('admin.product_options.index', compact('groups'));
    }

    public function create()
    {
        return view('admin.product_options.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:radio,checkbox',
            'is_required' => 'boolean',
            'min_selection' => 'required|integer|min:0',
            'max_selection' => 'required|integer|min:1',
            'options' => 'required|array|min:1',
            'options.*.name' => 'required|string',
            'options.*.price' => 'nullable|numeric|min:0',
        ]);

        $tenantId = 1;

        $group = ProductOptionGroup::create([
            'tenant_id' => $tenantId,
            'name' => $request->name,
            'type' => $request->type,
            'is_required' => $request->boolean('is_required'),
            'min_selection' => $request->min_selection,
            'max_selection' => $request->max_selection,
        ]);

        foreach ($request->options as $opt) {
            $group->options()->create([
                'tenant_id' => $tenantId,
                'name' => $opt['name'],
                'price_adjustment' => $opt['price'] ?? 0,
            ]);
        }

        return redirect()->route('admin.product-options.index')->with('success', 'Groupe d\'options créé.');
    }

    public function edit(ProductOptionGroup $product_option)
    {
        // $product_option is the bound model for {product_option}
        $product_option->load('options');
        return view('admin.product_options.edit', ['group' => $product_option]);
    }

    public function update(Request $request, ProductOptionGroup $product_option)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:radio,checkbox',
            'is_required' => 'boolean',
            'min_selection' => 'required|integer|min:0',
            'max_selection' => 'required|integer|min:1',
            'options' => 'required|array|min:1',
            'options.*.name' => 'required|string',
            'options.*.price' => 'nullable|numeric|min:0',
        ]);

        $product_option->update([
            'name' => $request->name,
            'type' => $request->type,
            'is_required' => $request->boolean('is_required'),
            'min_selection' => $request->min_selection,
            'max_selection' => $request->max_selection,
        ]);

        // Sync options: Delete all and recreate is easiest for now, or careful update
        // To allow renaming/updating prices without losing IDs (important for historical data integrity if we used IDs),
        // but order_item_options stores snapshot, so it's fine to delete/create if IDs don't matter elsewhere.
        // However, if products reference these options, we shouldn't delete them if we want to keep references? 
        // Actually product_options table is referenced by product_product_option_group? No, that links groups.
        // So recreating options is fine unless there's a direct link I forgot.
        // Wait, order_item_options links to product_option_id. If I delete, that link breaks (set null or error).
        // I should try to update existing ones.

        $existingIds = $product_option->options->pluck('id')->toArray();
        $submittedIds = [];

        foreach ($request->options as $opt) {
            if (isset($opt['id'])) {
                $submittedIds[] = $opt['id'];
                $option = ProductOption::find($opt['id']);
                if ($option) {
                    $option->update([
                        'name' => $opt['name'],
                        'price_adjustment' => $opt['price'] ?? 0,
                    ]);
                }
            } else {
                $newOpt = $product_option->options()->create([
                    'tenant_id' => $product_option->tenant_id,
                    'name' => $opt['name'],
                    'price_adjustment' => $opt['price'] ?? 0,
                ]);
                $submittedIds[] = $newOpt->id;
            }
        }

        // Delete removed options
        $toDelete = array_diff($existingIds, $submittedIds);
        ProductOption::destroy($toDelete);

        return redirect()->route('admin.product-options.index')->with('success', 'Groupe d\'options mis à jour.');
    }

    public function destroy(ProductOptionGroup $product_option)
    {
        $product_option->delete();
        return redirect()->route('admin.product-options.index')->with('success', 'Groupe supprimé.');
    }
}
