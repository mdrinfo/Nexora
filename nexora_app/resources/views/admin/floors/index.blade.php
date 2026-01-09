@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Plans de Salle</h2>
        <button onclick="document.getElementById('createFloorModal').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            + Ajouter un étage
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($floors as $floor)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="h-48 bg-gray-200 dark:bg-gray-700 relative">
                @if($floor->image_path)
                    <img src="{{ Storage::url($floor->image_path) }}" alt="{{ $floor->name }}" class="w-full h-full object-cover">
                @else
                    <div class="flex items-center justify-center h-full text-gray-400">
                        <span>Pas d'image</span>
                    </div>
                @endif
                <div class="absolute top-2 right-2 bg-black bg-opacity-50 text-white px-2 py-1 rounded text-xs">
                    Niveau {{ $floor->level }}
                </div>
            </div>
            <div class="p-4">
                <h3 class="text-xl font-semibold mb-2 text-gray-800 dark:text-white">{{ $floor->name }}</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">{{ $floor->tables->count() }} Tables</p>
                <div class="flex justify-between items-center">
                    <a href="{{ route('admin.floors.edit', $floor) }}" class="text-blue-600 hover:text-blue-800 font-medium">Éditer le plan</a>
                    <form action="{{ route('admin.floors.destroy', $floor) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Create Modal -->
    <div id="createFloorModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg w-full max-w-md">
            <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Ajouter un étage</h3>
            <form action="{{ route('admin.floors.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Nom</label>
                    <input type="text" name="name" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Niveau (ex: 1, 2)</label>
                    <input type="number" name="level" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" value="1" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Plan (Image)</label>
                    <input type="file" name="image" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('createFloorModal').classList.add('hidden')" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800">Annuler</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
