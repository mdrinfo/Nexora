@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.product-options.index') }}" class="text-gray-500 hover:text-gray-700 transition">
            &larr; Retour
        </a>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Modifier le Groupe: {{ $group->name }}</h1>
    </div>

    <form action="{{ route('admin.product-options.update', $group) }}" method="POST" id="optionForm">
        @csrf
        @method('PUT')
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 p-6 mb-6">
            <h2 class="text-lg font-bold mb-4 dark:text-white border-b dark:border-gray-700 pb-2">Configuration Générale</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Nom du Groupe</label>
                    <input type="text" name="name" value="{{ old('name', $group->name) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Type de Sélection</label>
                    <select name="type" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500" id="typeSelect" onchange="updateLimits()">
                        <option value="radio" {{ $group->type == 'radio' ? 'selected' : '' }}>Choix Unique (Radio)</option>
                        <option value="checkbox" {{ $group->type == 'checkbox' ? 'selected' : '' }}>Choix Multiple (Checkbox)</option>
                    </select>
                </div>

                <div class="flex items-center">
                    <label class="flex items-center gap-2 cursor-pointer bg-gray-50 dark:bg-gray-700/50 px-4 py-2 rounded-lg border dark:border-gray-600 w-full">
                        <input type="checkbox" name="is_required" value="1" {{ $group->is_required ? 'checked' : '' }} class="rounded text-blue-600 w-5 h-5 focus:ring-blue-500">
                        <span class="font-bold text-gray-700 dark:text-gray-300">Choix Obligatoire ?</span>
                    </label>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Min Sélection</label>
                        <input type="number" name="min_selection" id="minInput" value="{{ old('min_selection', $group->min_selection) }}" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Max Sélection</label>
                        <input type="number" name="max_selection" id="maxInput" value="{{ old('max_selection', $group->max_selection) }}" min="1" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 p-6 mb-6">
            <div class="flex justify-between items-center mb-4 border-b dark:border-gray-700 pb-2">
                <h2 class="text-lg font-bold dark:text-white">Options Disponibles</h2>
                <button type="button" onclick="addOption()" class="text-blue-600 dark:text-blue-400 font-bold hover:underline flex items-center gap-1">
                    <span class="text-xl">+</span> Ajouter une option
                </button>
            </div>
            
            <div id="optionsContainer" class="space-y-3">
                @foreach($group->options as $index => $option)
                <div class="flex gap-4 items-center bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg border dark:border-gray-700 option-row">
                    <span class="text-gray-400 cursor-move text-xl">⋮</span>
                    <input type="hidden" name="options[{{ $index }}][id]" value="{{ $option->id }}">
                    <div class="flex-1">
                        <input type="text" name="options[{{ $index }}][name]" value="{{ $option->name }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div class="w-32">
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">€</span>
                            <input type="number" name="options[{{ $index }}][price]" step="0.01" value="{{ $option->price_adjustment }}" class="w-full pl-8 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500" placeholder="Prix">
                        </div>
                    </div>
                    <button type="button" onclick="removeOption(this)" class="text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 p-2 rounded transition text-xl">&times;</button>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a href="{{ route('admin.product-options.index') }}" class="px-6 py-3 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition">Annuler</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg transition transform active:scale-95">Mettre à jour</button>
        </div>
    </form>
</div>

<template id="optionTemplate">
    <div class="flex gap-4 items-center bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg border dark:border-gray-700 option-row animate-bounce-in">
        <span class="text-gray-400 cursor-move text-xl">⋮</span>
        <div class="flex-1">
            <input type="text" name="options[INDEX][name]" placeholder="Nom de l'option" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500" required>
        </div>
        <div class="w-32">
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">€</span>
                <input type="number" name="options[INDEX][price]" step="0.01" value="0.00" class="w-full pl-8 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500" placeholder="Prix">
            </div>
        </div>
        <button type="button" onclick="removeOption(this)" class="text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 p-2 rounded transition text-xl">&times;</button>
    </div>
</template>

<script>
    let optionIndex = parseInt("{{ $group->options->count() }}");

    function addOption() {
        const container = document.getElementById('optionsContainer');
        const template = document.getElementById('optionTemplate').innerHTML;
        const html = template.replace(/INDEX/g, optionIndex++);
        container.insertAdjacentHTML('beforeend', html);
    }

    function removeOption(btn) {
        btn.closest('.option-row').remove();
    }

    function updateLimits() {
        const type = document.getElementById('typeSelect').value;
        const maxInput = document.getElementById('maxInput');
        
        if (type === 'radio') {
            maxInput.value = 1;
        }
    }
</script>
@endsection
