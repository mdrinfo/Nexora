@extends('layouts.admin')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Gestion des Stocks (Catalogue Visuel)</h2>
            @if($lowStockCount > 0)
                <p class="text-red-600 font-medium text-sm mt-1">⚠️ {{ $lowStockCount }} article(s) en stock critique</p>
            @endif
        </div>
        
        <div class="flex gap-3">
            <a href="{{ route('admin.inventory.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium transition-colors">
                + Nouvel Article
            </a>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg mb-6 w-fit">
        <a href="{{ route('admin.inventory.index') }}" 
           class="px-4 py-2 rounded-md text-sm font-medium {{ !request('category') ? 'bg-white shadow text-gray-800' : 'text-gray-500 hover:text-gray-700' }}">
           Tout
        </a>
        <a href="{{ route('admin.inventory.index', ['category' => 'food']) }}" 
           class="px-4 py-2 rounded-md text-sm font-medium {{ request('category') === 'food' ? 'bg-white shadow text-gray-800' : 'text-gray-500 hover:text-gray-700' }}">
           Nourriture
        </a>
        <a href="{{ route('admin.inventory.index', ['category' => 'drink']) }}" 
           class="px-4 py-2 rounded-md text-sm font-medium {{ request('category') === 'drink' ? 'bg-white shadow text-gray-800' : 'text-gray-500 hover:text-gray-700' }}">
           Boissons
        </a>
        <a href="{{ route('admin.inventory.index', ['category' => 'cleaning']) }}" 
           class="px-4 py-2 rounded-md text-sm font-medium {{ request('category') === 'cleaning' ? 'bg-white shadow text-gray-800' : 'text-gray-500 hover:text-gray-700' }}">
           Nettoyage
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse($items as $item)
            <div class="bg-white border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow relative">
                @if($item->quantity <= $item->min_threshold)
                    <div class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded shadow">
                        Critique
                    </div>
                @endif
                
                <div class="h-40 bg-gray-100 flex items-center justify-center overflow-hidden">
                    @if($item->image_path)
                        <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-4xl text-gray-300">📦</span>
                    @endif
                </div>

                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 truncate" title="{{ $item->name }}">{{ $item->name }}</h3>
                    <p class="text-sm text-gray-500 mb-2">{{ ucfirst($item->category) }}</p>
                    
                    <div class="flex justify-between items-end mb-3">
                        <div>
                            <span class="text-2xl font-bold {{ $item->quantity <= $item->min_threshold ? 'text-red-600' : 'text-gray-800' }}">
                                {{ (float)$item->quantity }}
                            </span>
                            <span class="text-sm text-gray-500">{{ $item->unit }}</span>
                        </div>
                        <div class="text-right text-xs text-gray-400">
                            Min: {{ (float)$item->min_threshold }}
                        </div>
                    </div>

                    <div class="border-t pt-3 flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600">
                            {{ number_format($item->cost_price, 2) }} € / {{ $item->unit }}
                        </span>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.inventory.edit', $item->id) }}" class="text-blue-600 hover:text-blue-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500">
                Aucun article trouvé. Commencez par en ajouter un !
            </div>
        @endforelse
    </div>
@endsection
