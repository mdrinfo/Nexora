@extends('layouts.admin')

@section('content')
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Gestion des utilisateurs</h3>
            <a href="{{ route('admin.users.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                + Nouvel Utilisateur
            </a>
        </div>
        
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="text-left text-sm text-gray-600 dark:text-gray-300 font-medium px-4 py-2">Nom</th>
                <th class="text-left text-sm text-gray-600 dark:text-gray-300 font-medium px-4 py-2">Email</th>
                <th class="text-left text-sm text-gray-600 dark:text-gray-300 font-medium px-4 py-2">Rôles</th>
                <th class="text-right text-sm text-gray-600 dark:text-gray-300 font-medium px-4 py-2">Actions</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($users as $user)
                <tr class="text-gray-700 dark:text-gray-300">
                    <td class="px-4 py-2">{{ $user->name }}</td>
                    <td class="px-4 py-2">{{ $user->email }}</td>
                    <td class="px-4 py-2">
                        @foreach($user->roles as $r)
                            <span class="inline-flex items-center px-2 py-1 text-xs bg-gray-100 dark:bg-gray-600 dark:text-gray-200 rounded mr-1">{{ $r->name }}</span>
                        @endforeach
                    </td>
                    <td class="px-4 py-2 text-right space-x-2">
                        <a href="{{ route('admin.users.edit', ['user' => $user->id]) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm">Modifier</a>
                        
                        @if($user->id !== auth()->id())
                            <form action="{{ route('admin.users.destroy', ['user' => $user->id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Êtes-vous sûr ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm">Supprimer</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                        Aucun utilisateur trouvé.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
