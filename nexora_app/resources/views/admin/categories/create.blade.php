@extends('layouts.admin')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-2 mb-6 text-sm text-gray-500">
            <a href="{{ route('admin.categories.index') }}" class="hover:underline">Catégories</a>
            <span>/</span>
            <span class="text-gray-900">Nouvelle</span>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Créer une catégorie</h2>

            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom de la catégorie</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2 text-gray-900">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="destination" class="block text-sm font-medium text-gray-700 mb-1">Destination (KDS)</label>
                    <select name="destination" id="destination" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2 text-gray-900">
                        <option value="kitchen" {{ old('destination') == 'kitchen' ? 'selected' : '' }}>Cuisine 🍳</option>
                        <option value="bar" {{ old('destination') == 'bar' ? 'selected' : '' }}>Bar 🍸</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Détermine sur quel écran les commandes s'afficheront.</p>
                </div>

                <div class="mb-6">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_drink" id="is_drink" value="1" {{ old('is_drink') ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_drink" class="ml-2 block text-sm text-gray-900">
                            Est une boisson
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-6">
                        Utilisé pour les rapports et statistiques.
                    </p>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Annuler
                    </a>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
