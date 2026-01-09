@extends('layouts.admin')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Gestion des Fournisseurs</h2>
        <a href="{{ route('admin.suppliers.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium transition-colors">
            + Nouveau Fournisseur
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 font-semibold">Nom</th>
                    <th class="px-6 py-3 font-semibold">Contact</th>
                    <th class="px-6 py-3 font-semibold">Téléphone / Email</th>
                    <th class="px-6 py-3 font-semibold text-center">Articles Liés</th>
                    <th class="px-6 py-3 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($suppliers as $supplier)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-gray-800 font-medium">
                            {{ $supplier->name }}
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ $supplier->contact_name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if($supplier->phone)
                                <div class="flex items-center gap-1">
                                    <span class="text-xs text-gray-400">📞</span> {{ $supplier->phone }}
                                </div>
                            @endif
                            @if($supplier->email)
                                <div class="flex items-center gap-1">
                                    <span class="text-xs text-gray-400">✉️</span> {{ $supplier->email }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $supplier->inventory_items_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('admin.suppliers.edit', $supplier->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Modifier</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            Aucun fournisseur trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
