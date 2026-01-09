@extends('layouts.admin')

@section('content')
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">Paramètres de marque</h3>
        <form method="post" action="{{ route('admin.settings.update') }}" class="space-y-4">
            @csrf
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Nom de marque</label>
                    <input type="text" name="brand_name" value="{{ old('brand_name', optional($setting)->brand_name) }}" class="w-full px-3 py-2 border border-gray-300 rounded text-gray-900">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Chemin du logo</label>
                    <input type="text" name="logo_path" value="{{ old('logo_path', optional($setting)->logo_path) }}" class="w-full px-3 py-2 border border-gray-300 rounded text-gray-900">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Couleur primaire</label>
                    <input type="text" name="primary_color_hex" value="{{ old('primary_color_hex', optional($setting)->primary_color_hex) }}" class="w-full px-3 py-2 border border-gray-300 rounded text-gray-900">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Couleur secondaire</label>
                    <input type="text" name="secondary_color_hex" value="{{ old('secondary_color_hex', optional($setting)->secondary_color_hex) }}" class="w-full px-3 py-2 border border-gray-300 rounded text-gray-900">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Couleur d'accent</label>
                    <input type="text" name="accent_color_hex" value="{{ old('accent_color_hex', optional($setting)->accent_color_hex) }}" class="w-full px-3 py-2 border border-gray-300 rounded text-gray-900">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Devise</label>
                    <input type="text" name="currency" value="{{ old('currency', optional($setting)->currency) }}" class="w-full px-3 py-2 border border-gray-300 rounded text-gray-900">
                </div>
            </div>

            <hr class="my-6 border-gray-200">

            <h3 class="text-lg font-semibold mb-4">Rétention des données</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Période de rétention (jours)</label>
                    <input type="number" name="retention_period_days" value="{{ old('retention_period_days', optional($setting)->retention_period_days ?? 365) }}" min="30" class="w-full px-3 py-2 border border-gray-300 rounded text-gray-900">
                    <p class="text-xs text-gray-500 mt-1">Les données plus anciennes que cette période seront anonymisées/purgées.</p>
                </div>
                <div class="flex items-center mt-6">
                    <input type="checkbox" id="enable_data_purge" name="enable_data_purge" value="1" {{ old('enable_data_purge', optional($setting)->enable_data_purge) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="enable_data_purge" class="ml-2 block text-sm text-gray-900">Activer la purge automatique des données</label>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded">Enregistrer les paramètres</button>
            </div>
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-6 mt-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Sauvegardes & Restauration</h3>
            <form method="post" action="{{ route('admin.settings.backup.create') }}">
                @csrf
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">Créer une sauvegarde maintenant</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Fichier</th>
                        <th scope="col" class="px-6 py-3">Taille</th>
                        <th scope="col" class="px-6 py-3">Date</th>
                        <th scope="col" class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backups as $backup)
                        <tr class="bg-white border-b">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ $backup['filename'] }}</td>
                            <td class="px-6 py-4">{{ round($backup['size'] / 1024, 2) }} KB</td>
                            <td class="px-6 py-4">{{ \Carbon\Carbon::createFromTimestamp($backup['last_modified'])->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 space-x-2">
                                <a href="{{ route('admin.settings.backup.download', $backup['filename']) }}" class="text-blue-600 hover:underline">Télécharger</a>
                                
                                <form method="post" action="{{ route('admin.settings.backup.restore', $backup['filename']) }}" class="inline-block" onsubmit="return confirm('ATTENTION: Cette action écrasera la base de données actuelle. Êtes-vous sûr ?');">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:underline ml-2">Restaurer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center">Aucune sauvegarde trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
