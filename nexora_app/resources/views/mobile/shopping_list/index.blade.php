<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Liste de Courses - Nexora</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 pb-24">
    <div class="bg-blue-600 text-white p-4 shadow-md sticky top-0 z-10">
        <h1 class="text-lg font-bold">Liste de Courses</h1>
        <p class="text-xs opacity-90">{{ $list->count() }} articles à acheter</p>
    </div>

    <div class="p-4 space-y-3">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @forelse($list as $item)
            <div x-data="{ checked: {{ $item->is_checked ? 'true' : 'false' }}, editing: false, qty: '{{ $item->quantity_needed }}' }" 
                 class="bg-white rounded-lg shadow-sm border p-4 flex items-center justify-between transition-colors duration-200"
                 :class="{ 'bg-blue-50 border-blue-200': checked }">
                
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-800">{{ $item->inventoryItem->name }}</span>
                        @if($item->is_manual)
                            <span class="text-[10px] bg-yellow-100 text-yellow-800 px-1.5 py-0.5 rounded-full">Manuel</span>
                        @else
                            <span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded-full">Auto</span>
                        @endif
                    </div>
                    <div class="text-sm text-gray-500">
                        Stock: {{ $item->inventoryItem->quantity }} {{ $item->inventoryItem->unit }}
                        @if($item->inventoryItem->supplier)
                            • {{ $item->inventoryItem->supplier->name }}
                        @endif
                    </div>
                    
                    <div class="mt-2 flex items-center gap-2">
                        <span class="text-xs text-gray-500">Besoin:</span>
                        <input type="number" x-model="qty" @change="updateQty('{{ route('mobile.shopping_list.update', $item) }}', qty)" 
                               class="w-20 p-1 text-sm border rounded" step="any">
                        <span class="text-sm text-gray-600">{{ $item->inventoryItem->unit }}</span>
                    </div>
                </div>

                <div class="ml-4">
                    <label class="relative flex items-center p-3 rounded-full cursor-pointer" htmlFor="check-{{ $item->id }}">
                        <input type="checkbox" 
                               id="check-{{ $item->id }}"
                               class="w-8 h-8 text-blue-600 rounded-lg border-gray-300 focus:ring-blue-500 focus:ring-2"
                               :checked="checked"
                               @change="toggleCheck('{{ route('mobile.shopping_list.toggle', $item) }}'); checked = !checked">
                    </label>
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-gray-500">
                <p class="text-4xl mb-2">✅</p>
                <p>Tout est en stock !</p>
            </div>
        @endforelse
    </div>

    <!-- Fixed Bottom Bar -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t p-4 shadow-lg flex justify-between items-center gap-4">
        <button onclick="document.getElementById('manual-add-modal').classList.remove('hidden')" 
                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-3 px-4 rounded-lg text-center">
            + Ajouter
        </button>
        
        <form action="{{ route('mobile.shopping_list.confirm') }}" method="POST" class="flex-1">
            @csrf
            <button type="submit" onclick="return confirm('Confirmer l\'achat des articles cochés et mettre à jour le stock ?')"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow-md">
                Confirmer & Stocker
            </button>
        </form>
    </div>

    <!-- Manual Add Modal -->
    <div id="manual-add-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg w-full max-w-sm p-6">
            <h3 class="text-lg font-bold mb-4">Ajouter un article</h3>
            <form action="{{ route('mobile.shopping_list.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Article</label>
                    <select name="inventory_item_id" class="w-full border rounded p-2">
                        @foreach(App\Models\InventoryItem::orderBy('name')->get() as $invItem)
                            <option value="{{ $invItem->id }}">{{ $invItem->name }} ({{ $invItem->unit }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-1">Quantité à acheter</label>
                    <input type="number" name="quantity" class="w-full border rounded p-2" required min="0.1" step="any">
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('manual-add-modal').classList.add('hidden')" 
                            class="flex-1 bg-gray-200 py-2 rounded">Annuler</button>
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleCheck(url) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
        }

        function updateQty(url, qty) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ quantity: qty })
            });
        }
    </script>
</body>
</html>
