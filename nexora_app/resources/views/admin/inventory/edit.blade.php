@extends('layouts.admin')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-2 mb-6 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('admin.inventory.index') }}" class="hover:underline">Inventaire</a>
            <span>/</span>
            <span class="text-gray-900 dark:text-white">Modifier</span>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-6">Modifier l'article: {{ $item->name }}</h2>

            <form action="{{ route('admin.inventory.update', ['inventory' => $item->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom de l'article</label>
                        <input type="text" name="name" value="{{ old('name', $item->name) }}" required
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catégorie</label>
                        <select name="category" required class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="food" {{ $item->category === 'food' ? 'selected' : '' }}>Nourriture</option>
                            <option value="drink" {{ $item->category === 'drink' ? 'selected' : '' }}>Boisson</option>
                            <option value="cleaning" {{ $item->category === 'cleaning' ? 'selected' : '' }}>Nettoyage</option>
                            <option value="other" {{ $item->category === 'other' ? 'selected' : '' }}>Autre</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unité</label>
                        <input type="text" name="unit" value="{{ old('unit', $item->unit) }}" required
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantité Actuelle</label>
                        <input type="number" step="0.001" name="quantity" value="{{ old('quantity', (float)$item->quantity) }}" required
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Seuil Critique</label>
                        <input type="number" step="0.001" name="min_threshold" value="{{ old('min_threshold', (float)$item->min_threshold) }}" required
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prix de Revient</label>
                        <div class="relative rounded-md shadow-sm">
                            <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price', $item->cost_price) }}" required
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2 pr-8 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400 sm:text-sm">€</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fournisseur</label>
                        <select name="supplier_id" class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">-- Aucun --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ $item->supplier_id == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Image (Laisser vide pour ne pas changer)</label>
                    <input type="file" name="image" accept="image/*"
                           class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900 dark:file:text-blue-200">
                    @if($item->image_path)
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">Image actuelle : {{ basename($item->image_path) }}</div>
                    @endif
                </div>

                <div class="flex justify-between items-center pt-4 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" onclick="if(confirm('Êtes-vous sûr de vouloir supprimer cet article ?')) document.getElementById('delete-form').submit();" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium">
                        Supprimer
                    </button>

                    <div class="flex gap-3">
                        <a href="{{ route('admin.inventory.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                            Annuler
                        </a>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                            Enregistrer
                        </button>
                    </div>
                </div>
            </form>

            <form id="delete-form" action="{{ route('admin.inventory.destroy', ['inventory' => $item->id]) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
@endsection
