@extends('layouts.admin')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center gap-2 mb-6 text-sm text-gray-500">
            <a href="{{ route('admin.products.index') }}" class="hover:underline">Menu</a>
            <span>/</span>
            <span class="text-gray-900">Modifier</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Product Details -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm h-fit">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Détails du Produit</h2>

                <form action="{{ url('/admin/products/' . $product->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2 text-gray-900">
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                            <select name="category_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2 text-gray-900">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prix (€)</label>
                            <input type="number" name="price" value="{{ old('price', $product->price) }}" required step="0.01" min="0"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2 text-gray-900">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Produit Parent (Optionnel - pour variantes)</label>
                        <select name="parent_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2 text-gray-900">
                            <option value="">Aucun (Produit principal)</option>
                            @foreach($parentProducts as $p)
                                <option value="{{ $p->id }}" {{ old('parent_id', $product->parent_id) == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Si sélectionné, ce produit sera une variante (ex: Petit, Moyen) du produit parent.</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Options & Suppléments</label>
                        <div class="space-y-2 border rounded-md p-3 max-h-40 overflow-y-auto">
                            @foreach($optionGroups as $group)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="option_groups[]" value="{{ $group->id }}" 
                                           {{ $product->optionGroups->contains($group->id) ? 'checked' : '' }}
                                           class="rounded text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">{{ $group->name }}</span>
                                    <span class="text-xs text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">
                                        {{ $group->type === 'radio' ? 'Unique' : 'Multiple' }}
                                    </span>
                                </label>
                            @endforeach
                            @if($optionGroups->isEmpty())
                                <p class="text-sm text-gray-500 italic">Aucun groupe d'options disponible. <a href="{{ route('admin.product-options.create') }}" class="text-blue-600 hover:underline">En créer un</a></p>
                            @endif
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
                            <span class="text-sm font-medium text-gray-700">Produit Actif (Visible sur le menu)</span>
                        </label>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                        @if($product->image_path)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $product->image_path) }}" alt="Current" class="h-20 w-20 object-cover rounded border">
                            </div>
                        @endif
                        <input type="file" name="image" accept="image/*"
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                        <button type="button" onclick="if(confirm('Supprimer ce produit ?')) document.getElementById('delete-form').submit();" class="text-red-600 hover:text-red-800 text-sm font-medium">
                            Supprimer
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                            Enregistrer
                        </button>
                    </div>
                </form>

                <form id="delete-form" action="{{ url('/admin/products/' . $product->id) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>

            <!-- Recipe Management -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm h-fit">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-800">Recette (Fiche Technique)</h2>
                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">Déduction Stock</span>
                </div>

                <p class="text-sm text-gray-500 mb-4">
                    Définissez les ingrédients consommés lors de la vente de ce produit. Le stock sera automatiquement déduit.
                </p>

                <form action="{{ url('/admin/products/' . $product->id . '/recipe') }}" method="POST">
                    @csrf
                    
                    <div id="ingredients-container" class="space-y-3 mb-6" data-inventory-items="{{ json_encode($inventoryItems) }}">
                        @foreach($product->ingredients as $index => $ingredient)
                            <div class="flex gap-2 items-center ingredient-row">
                                <select name="ingredients[{{ $index }}][id]" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 text-gray-900">
                                    @foreach($inventoryItems as $item)
                                        <option value="{{ $item->id }}" {{ $ingredient->id == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }} ({{ $item->unit }})
                                        </option>
                                    @endforeach
                                </select>
                                <input type="number" name="ingredients[{{ $index }}][quantity]" value="{{ $ingredient->pivot->quantity }}" step="0.0001" min="0" placeholder="Qté" class="w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 text-gray-900">
                                <button type="button" onclick="this.closest('.ingredient-row').remove()" class="text-red-500 hover:text-red-700 px-2">
                                    ✕
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" onclick="addIngredientRow()" class="w-full py-2 border-2 border-dashed border-gray-300 rounded-md text-gray-500 hover:border-blue-500 hover:text-blue-500 transition-colors mb-6 text-sm font-medium">
                        + Ajouter un ingrédient
                    </button>

                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
                            Sauvegarder la Recette
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let ingredientIndex = Number("{{ $product->ingredients->count() }}");
        const container = document.getElementById('ingredients-container');
        const inventoryItems = JSON.parse(container.dataset.inventoryItems);

        function addIngredientRow() {
            const container = document.getElementById('ingredients-container');
            const row = document.createElement('div');
            row.className = 'flex gap-2 items-center ingredient-row';
            
            let options = '';
            inventoryItems.forEach(item => {
                options += `<option value="${item.id}">${item.name} (${item.unit})</option>`;
            });

            row.innerHTML = `
                <select name="ingredients[${ingredientIndex}][id]" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 text-gray-900">
                    ${options}
                </select>
                <input type="number" name="ingredients[${ingredientIndex}][quantity]" step="0.0001" min="0" placeholder="Qté" class="w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 text-gray-900">
                <button type="button" onclick="this.closest('.ingredient-row').remove()" class="text-red-500 hover:text-red-700 px-2">
                    ✕
                </button>
            `;
            
            container.appendChild(row);
            ingredientIndex++;
        }
    </script>
@endsection
