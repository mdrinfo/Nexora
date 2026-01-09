@extends('layouts.admin')

@section('content')
<div id="floor-editor" class="flex h-[calc(100vh-64px)] overflow-hidden bg-gray-100 dark:bg-gray-900">
    <!-- Sidebar / Toolbar -->
    <div class="w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col p-4 z-10 shadow-lg">
        <div class="mb-6">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-1">{{ $floor->name }}</h2>
            <a href="{{ route('admin.floors.index') }}" class="text-sm text-blue-500 hover:underline">&larr; Retour</a>
        </div>

        <div class="space-y-3 mb-auto">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Outils</h3>
            <button @click="addTable('square')" class="w-full flex items-center p-3 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded border border-gray-200 dark:border-gray-600">
                <div class="w-6 h-6 border-2 border-gray-600 dark:border-gray-300 mr-3"></div>
                <span class="text-sm font-medium dark:text-gray-200">Table Carrée</span>
            </button>
            <button @click="addTable('round')" class="w-full flex items-center p-3 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded border border-gray-200 dark:border-gray-600">
                <div class="w-6 h-6 rounded-full border-2 border-gray-600 dark:border-gray-300 mr-3"></div>
                <span class="text-sm font-medium dark:text-gray-200">Table Ronde</span>
            </button>
             <button @click="addTable('rectangle')" class="w-full flex items-center p-3 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded border border-gray-200 dark:border-gray-600">
                <div class="w-8 h-5 border-2 border-gray-600 dark:border-gray-300 mr-3"></div>
                <span class="text-sm font-medium dark:text-gray-200">Table Rect.</span>
            </button>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
             <button @click="saveLayout" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded shadow flex justify-center items-center gap-2">
                <span v-if="saving">Enregistrement...</span>
                <span v-else>Enregistrer</span>
             </button>
        </div>
    </div>

    <!-- Canvas Area -->
    <div class="flex-1 overflow-auto relative bg-gray-200 dark:bg-gray-900 p-8" @click.self="selectedTable = null">
        <div class="relative bg-white shadow-2xl mx-auto transition-transform origin-top-left" 
             :style="{ width: '1000px', height: '800px', transform: 'scale(' + zoom + ')' }"
             @mousemove="onMouseMove" @mouseup="onMouseUp" @mouseleave="onMouseUp">
            
            <!-- Floor Image Background -->
            @if($floor->image_path)
                <img src="{{ Storage::url($floor->image_path) }}" class="absolute inset-0 w-full h-full object-contain opacity-50 pointer-events-none select-none" draggable="false">
            @else
                <div class="absolute inset-0 flex items-center justify-center text-gray-300 text-4xl font-bold select-none pointer-events-none">
                    Plan vide
                </div>
            @endif

            <!-- Grid (Optional) -->
            <div class="absolute inset-0 pointer-events-none" style="background-image: radial-gradient(#ddd 1px, transparent 1px); background-size: 20px 20px;"></div>

            <!-- Tables -->
            <div v-for="(table, index) in tables" :key="index"
                 class="absolute flex items-center justify-center cursor-move border-2 transition-colors group"
                 :class="{
                    'border-blue-500 bg-blue-100 dark:bg-blue-900 z-50': selectedTable === table,
                    'border-gray-600 bg-white dark:bg-gray-700 dark:border-gray-400': selectedTable !== table,
                    'rounded-full': table.shape === 'round',
                    'rounded-sm': table.shape !== 'round'
                 }"
                 :style="{
                    left: table.x + 'px',
                    top: table.y + 'px',
                    width: table.width + 'px',
                    height: table.height + 'px',
                    transform: 'rotate(' + (table.rotation || 0) + 'deg)'
                 }"
                 @mousedown.stop="startDrag(table, $event)"
                 @click.stop="selectedTable = table">
                
                <span class="font-bold text-xs select-none pointer-events-none dark:text-white transform" :style="{ transform: 'rotate(' + -(table.rotation || 0) + 'deg)' }">
                    @{{ table.label }}
                </span>
                
                <!-- Capacity Badge -->
                <span class="absolute -top-2 -right-2 bg-gray-800 text-white text-[10px] px-1 rounded-full select-none" :style="{ transform: 'rotate(' + -(table.rotation || 0) + 'deg)' }">
                    @{{ table.capacity }}p
                </span>

                <!-- Delete Button (only visible when selected) -->
                <button v-if="selectedTable === table" @click.stop="removeTable(index)" 
                        class="absolute -top-3 -left-3 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs shadow hover:bg-red-600 z-50"
                        title="Supprimer">
                    &times;
                </button>
            </div>
        </div>
    </div>

    <!-- Properties Panel -->
    <div v-if="selectedTable" class="w-72 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 p-4 shadow-xl z-20">
        <h3 class="font-bold text-lg mb-4 text-gray-800 dark:text-white border-b pb-2">Propriétés</h3>
        
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nom / Numéro</label>
                <input type="text" v-model="selectedTable.label" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <div class="grid grid-cols-2 gap-2">
                 <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Largeur (px)</label>
                    <input type="number" v-model.number="selectedTable.width" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                 <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Hauteur (px)</label>
                    <input type="number" v-model.number="selectedTable.height" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Capacité (pers.)</label>
                <input type="number" v-model.number="selectedTable.capacity" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Rotation (deg)</label>
                <input type="range" v-model.number="selectedTable.rotation" min="0" max="360" class="w-full">
                <div class="text-right text-xs text-gray-500">@{{ selectedTable.rotation || 0 }}°</div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Forme</label>
                <select v-model="selectedTable.shape" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="square">Carrée</option>
                    <option value="rectangle">Rectangle</option>
                    <option value="round">Ronde</option>
                </select>
            </div>
            
            <div class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
                <button @click="removeTable(tables.indexOf(selectedTable))" class="w-full py-2 bg-red-100 text-red-700 hover:bg-red-200 rounded border border-red-200 font-bold text-sm">
                    Supprimer cette table
                </button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/vue.global.js') }}"></script>
<script>
    function initFloorEditor() {
        if (typeof Vue === 'undefined') {
             console.error("Vue is not defined. Attempting to load from CDN...");
             // Dynamic load as last resort
             var script = document.createElement('script');
             script.src = 'https://unpkg.com/vue@3/dist/vue.global.prod.js';
             script.onload = initFloorEditor;
             document.head.appendChild(script);
             return;
        }
        
        // Prevent double mounting
        if (document.getElementById('floor-editor') && document.getElementById('floor-editor').__vue_app__) {
            return;
        }

        const { createApp } = Vue;
        const floorTables = <?php echo json_encode($floor->tables); ?>;

        const app = createApp({
            data() {
                return {
                    tables: floorTables,
                    selectedTable: null,
                    dragging: false,
                    dragOffset: { x: 0, y: 0 },
                    zoom: 1,
                    saving: false
                }
            },
            methods: {
                addTable(shape) {
                    const id = Date.now(); // Temp ID
                    this.tables.push({
                        label: 'T' + (this.tables.length + 1),
                        x: 100,
                        y: 100,
                        width: shape === 'rectangle' ? 120 : 80,
                        height: 80,
                        capacity: 4,
                        shape: shape,
                        rotation: 0
                    });
                    this.selectedTable = this.tables[this.tables.length - 1];
                },
                removeTable(index) {
                    if (confirm('Supprimer cette table ?')) {
                        this.tables.splice(index, 1);
                        this.selectedTable = null;
                    }
                },
                startDrag(table, event) {
                    this.selectedTable = table;
                    this.dragging = true;
                    this.dragOffset = {
                        x: event.clientX - table.x,
                        y: event.clientY - table.y
                    };
                },
                onMouseMove(event) {
                    if (this.dragging && this.selectedTable) {
                        this.selectedTable.x = event.clientX - this.dragOffset.x;
                        this.selectedTable.y = event.clientY - this.dragOffset.y;
                    }
                },
                onMouseUp() {
                    this.dragging = false;
                },
                saveLayout() {
                    this.saving = true;
                    fetch('{{ route("admin.floors.update_tables", $floor) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ tables: this.tables })
                    })
                    .then(response => {
                        if (!response.ok) {
                             return response.json().then(err => { throw err; });
                        }
                        return response.json();
                    })
                    .then(data => {
                        this.saving = false;
                        if (data.success) {
                            alert('Disposition enregistrée !');
                            // Reload to sync IDs and prevent duplicates on subsequent saves
                            window.location.reload();
                        } else {
                            alert('Erreur lors de l\'enregistrement');
                        }
                    })
                    .catch(error => {
                        console.error('Save Error:', error);
                        this.saving = false;
                        let msg = 'Erreur réseau';
                        if (error.message) msg = error.message;
                        if (error.errors) {
                            msg = Object.values(error.errors).flat().join('\n');
                        }
                        alert(msg);
                    });
                }
            },
            mounted() {
                this.tables.forEach(t => {
                    t.x = parseFloat(t.x_position || t.x || 0);
                    t.y = parseFloat(t.y_position || t.y || 0);
                    t.width = parseFloat(t.width || 80);
                    t.height = parseFloat(t.height || 80);
                    t.rotation = parseFloat(t.rotation || 0);
                });
            }
        });
        
        app.mount('#floor-editor');
    }

    // Handle both regular load and Turbolinks load
    document.addEventListener('DOMContentLoaded', initFloorEditor);
    document.addEventListener('turbolinks:load', initFloorEditor);
</script>
@endsection
