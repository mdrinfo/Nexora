@extends('layouts.admin')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Gestion des Catégories</h2>
        <div class="flex gap-3">
            <form action="{{ route('admin.categories.auto_detect') }}" method="POST">
                @csrf
                <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm font-medium transition-colors border border-gray-300">
                    🪄 Détection Auto
                </button>
            </form>
            <a href="{{ route('admin.categories.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium transition-colors">
                + Nouvelle Catégorie
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 font-semibold">Nom</th>
                    <th class="px-6 py-3 font-semibold text-center">Destination (KDS)</th>
                    <th class="px-6 py-3 font-semibold text-center">Boisson</th>
                    <th class="px-6 py-3 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($categories as $category)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-gray-800 font-medium">
                            {{ $category->name }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($category->destination === 'bar')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    Bar 🍸
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    Cuisine 🍳
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($category->is_drink)
                                <span class="text-xs text-gray-500">Oui</span>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('admin.categories.edit', $category->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Modifier</a>
                            <!-- Optional delete form -->
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                            Aucune catégorie trouvée.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
