@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-6 py-8">
    <h3 class="text-gray-700 text-3xl font-medium">Restaurant Management</h3>
    
    <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        
        <!-- POS Terminal -->
        <a href="{{ route('restaurant.pos.index') }}" class="group block max-w-sm rounded-lg border border-gray-200 bg-white p-6 shadow hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white group-hover:text-blue-600">🖥️ POS Terminal</h5>
            <p class="font-normal text-gray-700 dark:text-gray-400">Accéder au terminal de prise de commande et gestion de salle.</p>
        </a>

        <!-- Reservations -->
        <a href="{{ route('admin.reservations.index') }}" class="group block max-w-sm rounded-lg border border-gray-200 bg-white p-6 shadow hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white group-hover:text-blue-600">📅 Réservations</h5>
            <p class="font-normal text-gray-700 dark:text-gray-400">Gérer les réservations de tables.</p>
        </a>

        <!-- Kitchen Screen -->
        <a href="{{ route('kds.kitchen') }}" class="group block max-w-sm rounded-lg border border-gray-200 bg-white p-6 shadow hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white group-hover:text-blue-600">👨‍🍳 Écran Cuisine (KDS)</h5>
            <p class="font-normal text-gray-700 dark:text-gray-400">Voir les commandes en cours de préparation.</p>
        </a>

        <!-- Bar Screen -->
        <a href="{{ route('kds.bar') }}" class="group block max-w-sm rounded-lg border border-gray-200 bg-white p-6 shadow hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white group-hover:text-blue-600">🍸 Écran Bar</h5>
            <p class="font-normal text-gray-700 dark:text-gray-400">Voir les commandes de boissons.</p>
        </a>

        <!-- Floor Plan -->
        <a href="{{ route('admin.floors.index') }}" class="group block max-w-sm rounded-lg border border-gray-200 bg-white p-6 shadow hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white group-hover:text-blue-600">🗺️ Plans de Salle</h5>
            <p class="font-normal text-gray-700 dark:text-gray-400">Configurer les étages et les tables.</p>
        </a>

    </div>
</div>
@endsection
