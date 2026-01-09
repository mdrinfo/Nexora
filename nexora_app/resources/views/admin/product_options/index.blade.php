@extends('layouts.admin')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Options & Suppléments</h1>
    <a href="{{ route('admin.product-options.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-bold shadow transition transform hover:scale-105">
        + Nouveau Groupe
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border dark:border-gray-700">
    <table class="w-full text-left">
        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-400 uppercase text-xs border-b dark:border-gray-700">
            <tr>
                <th class="p-4">Nom du Groupe</th>
                <th class="p-4">Type</th>
                <th class="p-4">Options</th>
                <th class="p-4">Règles</th>
                <th class="p-4 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y dark:divide-gray-700">
            @forelse($groups as $group)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                <td class="p-4 font-bold dark:text-white">{{ $group->name }}</td>
                <td class="p-4">
                    <span class="px-2 py-1 rounded text-xs font-bold {{ $group->type === 'radio' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                        {{ $group->type === 'radio' ? 'Choix Unique' : 'Choix Multiple' }}
                    </span>
                </td>
                <td class="p-4 text-sm text-gray-600 dark:text-gray-300 max-w-xs truncate">
                    {{ $group->options->pluck('name')->join(', ') }}
                </td>
                <td class="p-4 text-sm">
                    @if($group->is_required)
                        <span class="text-red-500 font-bold text-xs bg-red-50 dark:bg-red-900/20 px-2 py-0.5 rounded">OBLIGATOIRE</span>
                    @else
                        <span class="text-gray-500 text-xs bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">OPTIONNEL</span>
                    @endif
                    <div class="text-xs text-gray-500 mt-1 dark:text-gray-400">
                        Min: {{ $group->min_selection }} / Max: {{ $group->max_selection }}
                    </div>
                </td>
                <td class="p-4 text-right space-x-2">
                    <a href="{{ route('admin.product-options.edit', $group) }}" class="text-blue-600 hover:underline font-bold text-sm">Modifier</a>
                    <form action="{{ route('admin.product-options.destroy', $group) }}" method="POST" class="inline-block" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce groupe ? Cela affectera les produits associés.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:underline font-bold text-sm">Supprimer</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="p-8 text-center text-gray-500 dark:text-gray-400">
                    Aucun groupe d'options créé.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
