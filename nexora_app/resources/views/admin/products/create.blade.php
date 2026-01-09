@extends('layouts.admin')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-2 mb-6 text-sm text-gray-500">
            <a href="{{ route('admin.products.index') }}" class="hover:underline">Menu</a>
            <span>/</span>
            <span class="text-gray-900">Nouveau</span>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Ajouter un Produit</h2>

            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom du produit</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2 text-gray-900">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                        <select name="category_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2 text-gray-900">
                            <option value="">Sélectionner...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prix (€)</label>
                        <input type="number" name="price" value="{{ old('price') }}" required step="0.01" min="0"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2 text-gray-900">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Produit Parent (Optionnel - pour variantes)</label>
                    <select name="parent_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2 text-gray-900">
                        <option value="">Aucun (Produit principal)</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ old('parent_id') == $p->id ? 'selected' : '' }}>
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
                                       {{ in_array($group->id, old('option_groups', [])) ? 'checked' : '' }}
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

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image (Optionnel)</label>
                    <input type="file" name="image" accept="image/*"
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.products.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Annuler
                    </a>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
