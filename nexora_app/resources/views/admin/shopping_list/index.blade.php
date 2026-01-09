@extends('layouts.admin')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                <span>🛒</span> Liste de Courses
            </h2>
            
            <div class="flex gap-2">
                <form action="{{ route('admin.shopping_list.sync') }}" method="POST" onsubmit="return confirm('Confirmer les achats et mettre à jour le stock ?');">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm font-medium transition-colors shadow-sm">
                        ✅ Confirmer Achats & MAJ Stock
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <!-- Manual Add Form -->
            <div class="p-4 bg-gray-50 border-b border-gray-200">
                <form action="{{ route('admin.shopping_list.store') }}" method="POST" class="flex gap-2">
                    @csrf
                    <select name="inventory_item_id" required class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2">
                        <option value="">Ajouter un article manuellement...</option>
                        @foreach(\App\Models\InventoryItem::orderBy('name')->get() as $invItem)
                            <option value="{{ $invItem->id }}">{{ $invItem->name }} ({{ $invItem->unit }})</option>
                        @endforeach
                    </select>
                    <input type="number" name="quantity_needed" placeholder="Qté" required step="0.1" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">
                        +
                    </button>
                </form>
            </div>

            <!-- List -->
            <ul class="divide-y divide-gray-200">
                @forelse($items as $item)
                    <li class="p-4 hover:bg-gray-50 transition-colors flex items-center justify-between cursor-pointer" onclick="toggleItem('{{ $item->id }}')">
                        <div class="flex items-center gap-4">
                            <div class="relative flex items-center">
                                <input type="checkbox" id="check_{{ $item->id }}" 
                                       class="h-6 w-6 text-green-600 border-gray-300 rounded focus:ring-green-500 cursor-pointer"
                                       {{ $item->is_checked ? 'checked' : '' }}
                                       onclick="event.stopPropagation(); toggleItem('{{ $item->id }}')">
                            </div>
                            
                            <div class="{{ $item->is_checked ? 'opacity-50 line-through' : '' }} transition-all" id="text_{{ $item->id }}">
                                <div class="font-medium text-gray-900 text-lg">
                                    {{ $item->inventoryItem->name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    Besoin : <span class="font-bold text-gray-700">{{ (float)$item->quantity_needed }} {{ $item->inventoryItem->unit }}</span>
                                    @if($item->is_manual)
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Manuel</span>
                                    @else
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Auto (Stock bas)</span>
                                    @endif
                                </div>
                                @if($item->inventoryItem->supplier)
                                    <div class="text-xs text-gray-400 mt-1">
                                        Fournisseur: {{ $item->inventoryItem->supplier->name }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="p-8 text-center text-gray-500">
                        Votre liste de courses est vide. 🎉
                    </li>
                @endforelse
            </ul>
        </div>
        
        @if($items->count() > 0)
            <div class="mt-4 text-right">
                <form action="{{ route('admin.shopping_list.clear') }}" method="POST" onsubmit="return confirm('Vider toute la liste sans mettre à jour le stock ?');">
                    @csrf
                    <button type="submit" class="text-red-600 text-sm hover:underline">Vider la liste</button>
                </form>
            </div>
        @endif
    </div>

    <script>
        function toggleItem(id) {
            fetch(`/admin/shopping-list/${id}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                const checkbox = document.getElementById(`check_${id}`);
                const textDiv = document.getElementById(`text_${id}`);
                
                checkbox.checked = data.is_checked;
                if (data.is_checked) {
                    textDiv.classList.add('opacity-50', 'line-through');
                } else {
                    textDiv.classList.remove('opacity-50', 'line-through');
                }
            });
        }
    </script>
@endsection
