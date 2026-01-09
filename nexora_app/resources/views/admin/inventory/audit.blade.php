@extends('layouts.admin')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center gap-2 mb-6 text-sm text-gray-500">
            <a href="{{ route('admin.inventory.index') }}" class="hover:underline">Stock</a>
            <span>/</span>
            <span class="text-gray-900">Audit Rapide</span>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800">Vérification du Stock</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Les articles ci-dessous n'ont pas été audités depuis 48h. Veuillez confirmer les quantités réelles.
                </p>
            </div>

            @if($items->isEmpty())
                <div class="text-center py-12 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <span class="text-4xl">✅</span>
                    <p class="mt-2 text-gray-600 font-medium">Tout est à jour !</p>
                    <p class="text-sm text-gray-500">Aucun audit requis pour le moment.</p>
                    <a href="{{ route('admin.inventory.index') }}" class="mt-4 inline-block text-blue-600 hover:underline">Retour au stock</a>
                </div>
            @else
                <form action="{{ route('admin.inventory.audit.update') }}" method="POST">
                    @csrf
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                <tr>
                                    <th class="px-4 py-3">Article</th>
                                    <th class="px-4 py-3">Catégorie</th>
                                    <th class="px-4 py-3">Stock Théorique</th>
                                    <th class="px-4 py-3">Stock Réel</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $index => $item)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium">
                                            {{ $item->name }}
                                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2 py-0.5 rounded">
                                                {{ ucfirst($item->category) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-500">
                                            {{ $item->quantity }} {{ $item->unit }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <input type="number" name="items[{{ $index }}][quantity]" 
                                                       value="{{ $item->quantity }}" step="0.001" min="0" required
                                                       class="w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-1">
                                                <span class="text-gray-500">{{ $item->unit }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <a href="{{ route('admin.inventory.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Annuler
                        </a>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
                            Valider l'Audit
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection
