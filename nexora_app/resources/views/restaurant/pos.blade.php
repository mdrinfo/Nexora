@extends('layouts.admin')

@section('content')
<style>
    /* Hide Sidebar for Waiter View */
    aside, .sidebar, #sidebar { display: none !important; }
    main, .main-content { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
    
    /* Custom Scrollbar */
    .custom-scroll::-webkit-scrollbar { width: 6px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .dark .custom-scroll::-webkit-scrollbar-thumb { background: #475569; }

    /* Map Table Styles */
    .table-node {
        transition: all 0.2s ease;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 2px solid transparent;
    }
    .table-node:hover { transform: scale(1.05); z-index: 10; }
    .table-node.occupied { border-color: #ef4444; background: #fef2f2; color: #991b1b; }
    .table-node.free { border-color: #22c55e; background: #f0fdf4; color: #166534; }
    
    .table-shape-round { border-radius: 50%; }
    .table-shape-square { border-radius: 12px; }
    .table-shape-rectangle { border-radius: 8px; }

    @media print {
        .no-print { display: none !important; }
        .print-only { display: block !important; }
        body { background: white; }
    }

    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(100%); }
        to { opacity: 1; transform: translateX(0); }
    }
    .animate-bounce-in {
        animation: slideInRight 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }
</style>

<div id="waiter-app" class="bg-gray-100 dark:bg-gray-900 min-h-screen flex flex-col font-sans">
    
    <!-- TOP BAR -->
    <div class="bg-white dark:bg-gray-800 shadow-sm z-20 px-4 py-3 flex justify-between items-center no-print border-b dark:border-gray-700">
        <div class="flex items-center gap-4">
            <h1 class="text-xl font-bold dark:text-white flex items-center gap-2 cursor-pointer" @click="viewMode = 'dashboard'">
                <span class="text-2xl">🍽️</span> 
                <span class="hidden sm:inline">Nexora Waiter</span>
            </h1>
            
            <!-- View Switcher -->
            <div class="flex bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                <button @click="viewMode = 'dashboard'" 
                    class="px-4 py-2 rounded-md text-sm font-bold transition"
                    :class="viewMode === 'dashboard' ? 'bg-white dark:bg-gray-600 shadow text-blue-600 dark:text-blue-300' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'">
                    Plan & Statut
                </button>
                <button @click="viewMode = 'menu'" 
                    class="px-4 py-2 rounded-md text-sm font-bold transition"
                    :class="viewMode === 'menu' ? 'bg-white dark:bg-gray-600 shadow text-blue-600 dark:text-blue-300' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'">
                    Menu & Commande
                </button>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <!-- Stop Sound Button -->
            <button v-if="isSoundPlaying" @click="stopNotificationSound" 
                class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-3 rounded-full shadow animate-pulse flex items-center gap-2 transition">
                <span>🔇</span> 
                <span class="hidden sm:inline">Arrêter</span>
            </button>

            <!-- Ready Orders Notification -->
            <button @click="showReadyModal = true" class="relative p-2 rounded-full transition" 
                :class="readyItems.length > 0 ? 'bg-red-100 text-red-600 animate-pulse' : 'bg-gray-100 text-gray-500'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span v-if="readyItems.length > 0" class="absolute -top-1 -right-1 bg-red-600 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full">
                    @{{ readyItems.length }}
                </span>
            </button>

            <button @click="startCamera" class="p-2 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 hover:bg-blue-200 transition" title="Scanner QR">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
            </button>
            <div class="text-right hidden sm:block">
                <div class="text-sm font-bold dark:text-white">{{ Auth::user()->name }}</div>
                <div class="text-xs text-gray-500">Serveur</div>
            </div>
        </div>
    </div>

    <!-- DASHBOARD VIEW -->
    <div v-if="viewMode === 'dashboard'" class="flex-1 flex flex-col md:flex-row overflow-hidden no-print h-[calc(100vh-64px)]">
        
        <!-- LEFT: ACTIVE SESSIONS (25%) -->
        <div class="w-full md:w-1/4 bg-white dark:bg-gray-800 border-r dark:border-gray-700 flex flex-col z-10">
            <div class="p-4 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-between items-center">
                <h2 class="font-bold text-lg dark:text-white flex items-center gap-2">
                    <span>📋</span> Sessions Actives
                </h2>
                <span class="bg-blue-600 text-white px-2 py-0.5 rounded-full text-xs font-bold">@{{ activeSessions.length }}</span>
            </div>
            
            <div class="flex-1 overflow-y-auto p-2 space-y-2 custom-scroll">
                <div v-for="session in activeSessions" :key="session.id" 
                     class="p-3 rounded-lg border cursor-pointer hover:shadow-md transition group"
                     :class="{
                        'border-blue-300 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800': session.type === 'online',
                        'border-orange-300 bg-orange-50 dark:bg-orange-900/20 dark:border-orange-800': session.type === 'takeaway',
                        'border-gray-200 bg-white dark:bg-gray-800 dark:border-gray-700': session.type === 'table'
                     }"
                     @click="openSession(session)">
                    
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xl" v-if="session.type === 'online'">🌐</span>
                            <span class="text-xl" v-else-if="session.type === 'takeaway'">🥡</span>
                            <span class="text-xl" v-else>🍽️</span>
                            <span class="font-bold text-lg dark:text-gray-200">@{{ session.table_name }}</span>
                        </div>
                        <span class="text-xs font-mono bg-gray-200 dark:bg-gray-700 dark:text-gray-300 px-1.5 py-0.5 rounded">@{{ session.time }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">@{{ session.type }}</span>
                        <span class="font-bold text-green-600 dark:text-green-400 text-lg">@{{ Number(session.total).toFixed(2) }} €</span>
                    </div>

                    <!-- Status Icons -->
                    <div v-if="session.status_counts" class="flex gap-3 text-xs border-t border-dashed pt-2 mt-2 border-gray-300 dark:border-gray-700">
                         <div v-if="session.status_counts.pending > 0" class="flex items-center gap-1 text-gray-500" title="En attente">
                            <span>🕒</span> @{{ session.status_counts.pending }}
                        </div>
                        <div v-if="session.status_counts.preparing > 0" class="flex items-center gap-1 text-orange-500 animate-pulse" title="En préparation">
                            <span>🔥</span> @{{ session.status_counts.preparing }}
                        </div>
                        <div v-if="session.status_counts.ready > 0" class="flex items-center gap-1 text-green-600 font-bold" title="Prêt à servir">
                            <span>✅</span> @{{ session.status_counts.ready }}
                        </div>
                    </div>
                    
                    <div class="mt-2 pt-2 border-t border-dashed border-gray-300 dark:border-gray-700 flex justify-end opacity-0 group-hover:opacity-100 transition-opacity">
                         <span class="text-xs text-blue-500 font-bold flex items-center gap-1">
                            Gérer <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                         </span>
                    </div>
                </div>

                <div v-if="activeSessions.length === 0" class="text-center py-10 text-gray-400 italic">
                    Aucune session active
                </div>
            </div>
        </div>
        
        <!-- RIGHT: FLOOR PLAN (75%) -->
        <div class="flex-1 bg-gray-200 dark:bg-gray-900 relative overflow-hidden flex flex-col">
            <!-- Floor Tabs -->
            <div v-if="floors.length > 1" class="flex justify-center p-2 bg-gray-100 dark:bg-gray-800/50 border-b dark:border-gray-700 z-10">
                <div class="flex gap-2 bg-white dark:bg-gray-800 p-1 rounded-full shadow-sm border dark:border-gray-700">
                    <button v-for="floor in floors" :key="floor.id" 
                        @click="!floor.is_locked ? activeFloorId = floor.id : null"
                        :disabled="floor.is_locked"
                        class="px-4 py-1.5 rounded-full text-sm font-bold transition flex items-center gap-2"
                        :class="[
                            activeFloorId === floor.id ? 'bg-blue-600 text-white shadow' : (floor.is_locked ? 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-700 dark:text-gray-500' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700')
                        ]">
                        @{{ floor.name }}
                        <span v-if="floor.is_locked" class="text-xs">🔒</span>
                    </button>
                </div>
            </div>

            <!-- Map Area -->
            <div class="flex-1 overflow-auto relative bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] dark:bg-[radial-gradient(#334155_1px,transparent_1px)] [background-size:20px_20px] flex items-center justify-center">
                <div class="relative w-full h-full min-w-[800px] min-h-[600px] transform transition-transform duration-300 origin-center">
                    <div v-for="t in currentFloorTables" :key="t.id"
                        class="table-node absolute flex flex-col items-center justify-center text-center shadow-lg transition-all duration-300"
                        :class="[
                            'table-shape-' + (t.shape || 'square'),
                            'status-' + getTableStatus(t.id)
                        ]"
                        :style="{ 
                            left: t.x_position + 'px', 
                            top: t.y_position + 'px', 
                            width: (t.width || 80) + 'px', 
                            height: (t.height || 80) + 'px',
                            transform: 'rotate(' + (t.rotation || 0) + 'deg)'
                        }"
                        @click="selectTable(t.id)">
                        
                        <div class="font-bold text-lg leading-none">@{{ t.label }}</div>
                        <div class="text-xs mt-1 font-medium opacity-80">
                            @{{ t.capacity }} <span class="text-[10px]">👤</span>
                        </div>
                        
                        <div v-if="getTableStatus(t.id) !== 'free'" class="absolute -top-3 -right-3 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow border border-white"
                             :class="{
                                'bg-green-500': getTableStatus(t.id) === 'ready',
                                'bg-orange-500': getTableStatus(t.id) === 'waiting',
                                'bg-red-500': getTableStatus(t.id) === 'occupied'
                             }">
                            @{{ getTableStatusLabel(t.id) }}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="absolute bottom-4 left-4 bg-white/90 dark:bg-gray-800/90 backdrop-blur px-3 py-2 rounded-lg shadow text-xs text-gray-500 dark:text-gray-400 flex flex-wrap gap-3">
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-green-100 border border-green-500"></span> Libre</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-orange-100 border border-orange-500 animate-pulse"></span> En Attente</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-green-400 border border-green-600"></span> Prêt</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-red-100 border border-red-500"></span> Occupé</div>
            </div>
        </div>
    </div>

    <!-- MENU / ORDERING VIEW (3-Column Layout) -->
    <div v-else class="flex-1 flex overflow-hidden no-print h-[calc(100vh-64px)]">
        
        <!-- COLUMN 1: CATEGORIES (15%) -->
        <div class="w-48 bg-white dark:bg-gray-800 border-r dark:border-gray-700 flex flex-col overflow-y-auto custom-scroll">
            <div class="p-3 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <h3 class="font-bold text-gray-500 dark:text-gray-400 uppercase text-xs tracking-wider">Catégories</h3>
            </div>
            <button @click="selectedCategory = null" 
                class="p-4 text-left font-bold transition border-l-4"
                :class="!selectedCategory ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 border-blue-600' : 'border-transparent text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'">
                TOUT
            </button>
            <button v-for="cat in categories" :key="cat" @click="selectedCategory = cat"
                class="p-4 text-left font-bold transition border-l-4"
                :class="selectedCategory === cat ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 border-blue-600' : 'border-transparent text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'">
                @{{ cat }}
            </button>
        </div>

        <!-- COLUMN 2: PRODUCTS (55%) -->
        <div class="flex-1 bg-gray-100 dark:bg-gray-900 p-4 flex flex-col overflow-hidden">
             <!-- Header / Search -->
             <div class="flex justify-between items-center mb-4 gap-4">
                 <div class="relative flex-1">
                     <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
                     <input v-model="q" type="text" class="w-full pl-10 pr-4 py-3 rounded-xl border-none shadow-sm bg-white dark:bg-gray-800 text-lg focus:ring-2 focus:ring-blue-500 outline-none"
                            placeholder="Rechercher un produit...">
                 </div>
                 
                 <!-- Table Status -->
                 <div v-if="selectedTableId" class="bg-blue-600 text-white px-4 py-2 rounded-xl shadow-md font-bold flex items-center gap-2">
                     <span>Table: @{{ getTableLabel(selectedTableId) }}</span>
                     <button @click="selectedTableId = null; viewMode='dashboard'" class="bg-blue-700 hover:bg-blue-800 p-1 rounded text-xs ml-2">❌</button>
                 </div>
                 <button v-else @click="showTableModal = true" class="bg-yellow-500 text-white px-4 py-2 rounded-xl shadow-md font-bold hover:bg-yellow-600">
                     Choisir Table
                 </button>
             </div>

             <!-- Grid -->
             <div class="flex-1 overflow-y-auto custom-scroll pb-20">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    <button v-for="p in filteredProducts" :key="p.id" @click="addToCart(p)"
                        class="flex flex-col items-start p-4 rounded-xl bg-white dark:bg-gray-800 shadow-sm hover:shadow-lg transition transform active:scale-95 text-left h-full border border-transparent hover:border-blue-300 dark:hover:border-blue-700">
                        <div class="text-base font-bold text-gray-800 dark:text-gray-100 mb-1 leading-tight w-full break-words">@{{ p.name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-3 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">@{{ p.category }}</div>
                        <div class="mt-auto w-full flex items-center justify-between">
                            <span class="text-lg font-bold text-blue-600 dark:text-blue-400">@{{ Number(p.price).toFixed(2) }} €</span>
                            <div @click.stop="toggleFav(p.id)" class="text-xl px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                <span v-if="isFav(p.id)" class="text-yellow-500">★</span>
                                <span v-else class="text-gray-300 dark:text-gray-600">☆</span>
                            </div>
                        </div>
                    </button>
                </div>
                
                <div v-if="filteredProducts.length === 0" class="flex flex-col items-center justify-center h-64 text-gray-500">
                    <span class="text-4xl mb-2">🍽️</span>
                    <p>Aucun produit trouvé</p>
                </div>
             </div>
        </div>

        <!-- COLUMN 3: CART / ORDER (30%) -->
        <div class="w-full md:w-80 lg:w-96 bg-white dark:bg-gray-800 border-l dark:border-gray-700 flex flex-col shadow-xl z-20">
            <div class="p-4 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-between items-center">
                <h3 class="text-xl font-bold dark:text-white">Commande</h3>
                <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-sm font-bold">@{{ cartCount }} articles</span>
            </div>
            
            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scroll">
                <div v-if="cart.length === 0" class="text-center py-10 text-gray-400">
                    Panier vide
                </div>
                
                <div v-for="(item, index) in cart" :key="index" class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg border dark:border-gray-700">
                    <div class="flex-1">
                        <div class="font-bold dark:text-white">@{{ item.name }}</div>
                        <div v-if="item.options && item.options.length > 0" class="text-xs text-gray-500 mb-1">
                            <div v-for="opt in item.options" :key="opt.id">
                                + @{{ opt.name }} <span v-if="opt.price_adjustment > 0">(@{{ Number(opt.price_adjustment).toFixed(2) }}€)</span>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500">@{{ Number(item.price).toFixed(2) }} €</div>
                    </div>
                    <div class="flex items-center gap-3 bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-600 px-2 py-1">
                        <button @click="updateQty(index, -1)" class="text-red-500 font-bold px-2 hover:bg-red-50 rounded">-</button>
                        <span class="font-bold w-4 text-center dark:text-white">@{{ item.quantity }}</span>
                        <button @click="updateQty(index, 1)" class="text-green-500 font-bold px-2 hover:bg-green-50 rounded">+</button>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="p-4 bg-gray-50 dark:bg-gray-800 border-t dark:border-gray-700">
                <div class="flex justify-between items-center mb-4 text-xl font-bold dark:text-white">
                    <span>Total</span>
                    <span>@{{ Number(cartTotal).toFixed(2) }} €</span>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <button @click="resetView" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 text-gray-700 dark:text-gray-200 py-3 rounded-xl font-bold transition">
                        Annuler
                    </button>
                    <button @click="submitOrder" :disabled="isSubmitting || cart.length === 0" 
                        class="bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl font-bold shadow-lg transition flex justify-center items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span v-if="isSubmitting" class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span>
                        <span v-else>Envoyer 🚀</span>
                    </button>
                </div>
                
                <div class="mt-3">
                    <button @click="showBill" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold shadow transition">
                        Voir l'Addition / Payer
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ready Orders Modal -->
    <div v-if="showReadyModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md max-h-[80vh] flex flex-col">
            <div class="p-4 border-b dark:border-gray-700 flex justify-between items-center bg-green-50 dark:bg-green-900/20">
                <h3 class="text-xl font-bold text-green-700 dark:text-green-400">Commandes Prêtes 🚀</h3>
                <button @click="showReadyModal = false" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            </div>
            <div class="flex-1 overflow-y-auto p-4">
                <div v-if="readyItems.length === 0" class="text-center py-8 text-gray-500">
                    Aucune commande prête pour le moment.
                </div>
                <div v-else class="space-y-3">
                    <div v-for="item in readyItems" :key="item.id" class="bg-white border border-green-200 dark:bg-gray-700 dark:border-gray-600 p-4 rounded-xl shadow-sm flex justify-between items-center">
                        <div>
                            <div class="font-bold text-lg dark:text-white">@{{ item.product.name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                Table: <span class="font-bold text-gray-800 dark:text-gray-200">@{{ item.order?.session?.dining_table?.label || '?' }}</span>
                            </div>
                        </div>
                        <button @click="markItemServed(item.id)" class="bg-green-100 hover:bg-green-200 text-green-700 p-2 rounded-full transition" title="Marquer comme servi">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Table Selection Modal -->
    <div v-if="showTableModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[80vh] flex flex-col">
            <div class="p-4 border-b dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-xl font-bold dark:text-white">Choisir une Table</h3>
                <button @click="showTableModal = false" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            </div>
            <div class="flex-1 overflow-y-auto p-4 grid grid-cols-3 sm:grid-cols-4 gap-3">
                <button v-for="t in tables" :key="t.id" @click="selectTable(t.id)"
                    class="p-4 rounded-xl border-2 text-center transition hover:scale-105"
                    :class="isTableOccupied(t.id) ? 'border-red-500 bg-red-50 text-red-700' : 'border-green-500 bg-green-50 text-green-700'">
                    <div class="text-2xl font-bold">@{{ t.label }}</div>
                    <div class="text-xs">@{{ t.capacity }} Pers.</div>
                </button>
            </div>
        </div>
    </div>

    <!-- Option Selection Modal -->
    <div v-if="showOptionModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col">
            <div class="p-4 border-b dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-xl font-bold dark:text-white text-black">
                    @{{ selectedProductForOptions?.name }}
                    <span v-if="currentStepTotal > 0" class="text-blue-600 text-sm ml-2">(+@{{ currentStepTotal.toFixed(2) }}€)</span>
                </h3>
                <button @click="closeOptionsModal" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            </div>
            
            <div class="flex-1 overflow-y-auto p-4 custom-scroll">
                <div v-for="group in selectedProductForOptions?.option_groups" :key="group.id" class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-bold text-gray-800 dark:text-gray-200">
                            @{{ group.name }}
                            <span v-if="group.is_required" class="text-red-500 text-xs">* Obligatoire</span>
                        </h4>
                        <span class="text-xs text-gray-500">
                            @{{ group.type === 'radio' ? 'Choix unique' : `Max ${group.max_selection}` }}
                        </span>
                    </div>

                    <div class="space-y-2">
                        <div v-for="opt in group.options" :key="opt.id" 
                             @click="toggleOption(group, opt)"
                             class="flex items-center justify-between p-3 rounded-lg border cursor-pointer transition"
                             :class="isOptionSelected(group, opt.id) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'">
                            
                            <div class="flex items-center gap-3">
                                <div class="w-5 h-5 rounded border flex items-center justify-center"
                                     :class="group.type === 'radio' ? 'rounded-full' : 'rounded'">
                                    <div v-if="isOptionSelected(group, opt.id)" class="w-3 h-3 bg-blue-600 rounded-full"></div>
                                </div>
                                <span class="text-gray-700 dark:text-gray-300">@{{ opt.name }}</span>
                            </div>
                            
                            <span v-if="opt.price_adjustment > 0" class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                +@{{ Number(opt.price_adjustment).toFixed(2) }}€
                            </span>
                        </div>
                    </div>
                    
                    <div v-if="getValidationMessage(group)" class="text-red-500 text-xs mt-1">
                        @{{ getValidationMessage(group) }}
                    </div>
                </div>
            </div>

            <div class="p-4 border-t dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex justify-between items-center">
                <div class="text-lg font-bold dark:text-white text-black">
                    Total: @{{ (Number(selectedProductForOptions?.price || 0) + currentOptionsTotal).toFixed(2) }} €
                </div>
                <button @click="confirmOptions" :disabled="!isOptionsValid"
                        class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-8 py-3 rounded-xl font-bold shadow-lg transition">
                    Confirmer
                </button>
            </div>
        </div>
    </div>

    <!-- Variant Selection Modal -->
    <div v-if="showVariantModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md max-h-[80vh] flex flex-col">
            <div class="p-4 border-b dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-xl font-bold dark:text-white text-black">Choisir une variante</h3>
                <button @click="closeVariantModal" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                <div v-for="variant in selectedProductForVariant.children" :key="variant.id" 
                     @click="selectVariant(variant)"
                     class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 cursor-pointer transition flex justify-between items-center">
                    <span class="font-bold text-lg dark:text-white">@{{ variant.name }}</span>
                    <span class="text-blue-600 font-bold">@{{ Number(variant.price).toFixed(2) }} €</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bill Modal -->
    <div v-if="showBillModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md flex flex-col max-h-[90vh]">
            <div class="p-4 border-b dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-700">
                <h3 class="text-xl font-bold dark:text-white">Addition - Table @{{ getTableLabel(selectedTableId) }}</h3>
                <button @click="closeBillModal" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            </div>
            
            <div class="flex-1 overflow-y-auto p-4" v-if="billData">
                <div v-if="!billData.active" class="text-center py-10 text-gray-500">
                    Aucune session active pour cette table.
                </div>
                <div v-else class="space-y-4">
                    <div class="text-center mb-4">
                        <div class="text-3xl font-bold text-gray-800 dark:text-white">@{{ Number(billData.total).toFixed(2) }} €</div>
                        <div class="text-sm text-gray-500">Total à payer</div>
                    </div>
                    
                    <div class="space-y-2">
                        <div v-for="order in billData.orders" :key="order.id" class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg text-sm">
                            <div class="flex justify-between font-bold mb-1 dark:text-gray-200">
                                <span>Commande #@{{ order.id }}</span>
                                <span>@{{ Number(order.total).toFixed(2) }} €</span>
                            </div>
                            <div v-for="(item, i) in order.items" :key="i" class="text-gray-500 dark:text-gray-400 pl-2">
                                <div class="flex justify-between">
                                    <span>@{{ item.qty }}x @{{ item.name }}</span>
                                    <span>@{{ Number(item.price).toFixed(2) }}</span>
                                </div>
                                <div v-if="item.options && item.options.length > 0" class="pl-4 text-xs italic">
                                    <div v-for="opt in item.options" :key="opt.name">
                                        + @{{ opt.name }} <span v-if="opt.price > 0">(@{{ Number(opt.price).toFixed(2) }}€)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex-1 flex items-center justify-center" v-else>
                <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
            </div>
            
            <div class="p-4 border-t dark:border-gray-700 bg-gray-50 dark:bg-gray-800 space-y-2" v-if="billData && billData.active">
                <button @click="printBill" :disabled="isPrinting" class="w-full bg-gray-600 hover:bg-gray-700 text-white py-3 rounded-xl font-bold flex justify-center items-center gap-2">
                    <span v-if="isPrinting" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                    🖨️ Imprimer Ticket
                </button>
                <div class="grid grid-cols-2 gap-3">
                    <button @click="payBill('cash')" class="bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl font-bold">
                        💵 Espèces
                    </button>
                    <button @click="payBill('card')" class="bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold">
                        💳 Carte
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Preview (Hidden or Modal) -->
    <div v-if="showReceiptPreview" class="fixed inset-0 bg-black/80 z-[60] flex items-center justify-center p-4">
        <div class="bg-white text-black p-6 rounded-lg shadow-xl max-w-sm w-full">
            <div id="receipt-content" class="font-mono text-sm mb-4">
                <div class="text-center font-bold text-lg mb-2">@{{ receiptData?.restaurant_name }}</div>
                <div class="text-center text-xs mb-4">
                    @{{ receiptData?.restaurant_address }}<br>
                    Tel: @{{ receiptData?.restaurant_phone }}
                </div>
                <div class="border-b border-dashed border-black my-2"></div>
                <div class="flex justify-between">
                    <span>Table: @{{ receiptData?.table }}</span>
                    <span>@{{ receiptData?.ticket_id }}</span>
                </div>
                <div class="text-xs text-right">@{{ receiptData?.printed_at }}</div>
                <div class="border-b border-dashed border-black my-2"></div>
                
                <div v-for="(item, i) in receiptData?.items" :key="i" class="flex justify-between my-1">
                    <span class="truncate w-40">@{{ item.qty }}x @{{ item.name }}</span>
                    <span>@{{ Number(item.total).toFixed(2) }}</span>
                </div>
                
                <div class="border-t border-dashed border-black my-2 pt-2">
                    <div class="flex justify-between font-bold text-lg">
                        <span>TOTAL</span>
                        <span>@{{ Number(receiptData?.total).toFixed(2) }} €</span>
                    </div>
                </div>
                
                <div class="text-center text-xs text-gray-500 mt-6">
                    Merci de votre visite !<br>A bientôt.
                </div>
            </div>
            
            <div class="flex gap-2">
                <button @click="showReceiptPreview = false" class="flex-1 bg-gray-200 hover:bg-gray-300 py-2 rounded font-bold">Fermer</button>
                <button @click="printReceipt" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded font-bold">Imprimer</button>
            </div>
        </div>
    </div>
    
    <!-- Camera Modal -->
    <div v-if="showCameraModal" class="fixed inset-0 bg-black/90 z-[70] flex items-center justify-center p-4 no-print backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col relative">
            
            <!-- Header -->
            <div class="bg-blue-600 p-4 flex justify-between items-center text-white shadow-md z-10">
                <h3 class="font-bold text-lg flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    Scanner QR
                </h3>
                <button @click="stopCamera" class="bg-white/20 p-2 rounded-full hover:bg-white/30 transition transform hover:rotate-90">&times;</button>
            </div>

            <!-- Scanner Area -->
            <div class="p-6 bg-gray-100 dark:bg-gray-900 flex flex-col items-center">
                
                <div class="relative w-64 h-64 bg-black rounded-xl overflow-hidden shadow-inner border-4 border-white dark:border-gray-700 mb-6">
                    <!-- HTML5 QR Code Reader Container -->
                    <div id="qr-reader" class="w-full h-full object-cover"></div>
                    
                    <!-- Scanner Overlay Animation (Visual only) -->
                    <div class="absolute inset-0 pointer-events-none z-10">
                        <div class="absolute top-0 left-0 w-full h-1 bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.8)] animate-[scan_2s_infinite]"></div>
                        <!-- Corners -->
                        <div class="absolute top-2 left-2 w-6 h-6 border-t-4 border-l-4 border-blue-500 rounded-tl-lg"></div>
                        <div class="absolute top-2 right-2 w-6 h-6 border-t-4 border-r-4 border-blue-500 rounded-tr-lg"></div>
                        <div class="absolute bottom-2 left-2 w-6 h-6 border-b-4 border-l-4 border-blue-500 rounded-bl-lg"></div>
                        <div class="absolute bottom-2 right-2 w-6 h-6 border-b-4 border-r-4 border-blue-500 rounded-br-lg"></div>
                    </div>
                </div>

                <div id="scan-status" class="text-sm font-bold text-center px-4 py-2 rounded-lg bg-white dark:bg-gray-700 shadow-sm border dark:border-gray-600 mb-6 w-full transition-all duration-300">
                    Placez le QR Code au centre
                </div>

                <!-- Manual Entry Divider -->
                <div class="flex items-center gap-2 w-full mb-4">
                    <div class="h-px bg-gray-300 dark:bg-gray-600 flex-1"></div>
                    <span class="text-xs font-bold text-gray-400 uppercase">Ou saisir ID</span>
                    <div class="h-px bg-gray-300 dark:bg-gray-600 flex-1"></div>
                </div>

                <!-- Manual Entry -->
                <div class="flex gap-2 w-full">
                    <input v-model="manualTableIdInput" type="number" 
                        class="flex-1 p-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-center font-bold text-lg focus:ring-2 focus:ring-blue-500 outline-none" 
                        placeholder="Ex: 12" @keyup.enter="handleManualEntry">
                    <button @click="handleManualEntry" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg transition active:scale-95">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    @keyframes scan {
        0% { top: 0%; opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 1; }
        100% { top: 100%; opacity: 0; }
    }
    </style>

    <!-- TOAST NOTIFICATIONS (Ready Items) -->
    <div class="fixed top-20 right-4 z-50 flex flex-col gap-3 pointer-events-none">
        <div v-for="item in readyItems" :key="item.id" 
             class="bg-white dark:bg-gray-800 border-l-4 border-green-500 shadow-2xl rounded-lg p-4 pointer-events-auto flex items-start gap-4 w-80 transform transition-all duration-300 hover:scale-105 animate-bounce-in">
            
            <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-full text-green-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex justify-between items-start">
                    <h4 class="font-bold text-gray-800 dark:text-gray-100 text-sm">Commande Prête !</h4>
                    <span class="text-xs text-gray-400">@{{ item.updated_at_human }}</span>
                </div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-300 mt-1 truncate">@{{ item.product.name }}</p>
                <div class="mt-2 flex items-center justify-between">
                    <span class="text-xs font-bold bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                        Table @{{ item.order.session.dining_table.label }}
                    </span>
                    <button @click="markItemServed(item.id)" 
                            class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1.5 rounded-md font-bold shadow transition flex items-center gap-1">
                        <span>Tamam Bende</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Assign Table Modal -->
    <div v-if="showAssignModal" class="fixed inset-0 bg-black/80 z-[80] flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="bg-indigo-600 p-4 flex justify-between items-center text-white">
                <h3 class="font-bold text-lg">Assigner une table au QR</h3>
                <button @click="showAssignModal = false" class="bg-white/20 p-2 rounded-full hover:bg-white/40">&times;</button>
            </div>
            <div class="p-4 max-h-[70vh] overflow-y-auto">
                <p class="mb-4 text-gray-600">Veuillez sélectionner une table pour ce QR Code.</p>
                
                <div class="grid grid-cols-3 gap-3">
                    <button 
                        v-for="table in tables" 
                        :key="table.id"
                        @click="assignTable(table.id)"
                        :disabled="isTableOccupied(table.id)"
                        :class="[
                            'p-4 rounded-lg font-bold text-lg transition shadow-sm border-2',
                            isTableOccupied(table.id) 
                                ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed' 
                                : 'bg-white border-gray-200 hover:border-indigo-500 hover:bg-indigo-50 text-gray-800'
                        ]"
                    >
                        @{{ table.label }}
                        <div v-if="isTableOccupied(table.id)" class="text-xs font-normal mt-1 text-red-500">Occupé</div>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- PRINT TEMPLATE (Hidden) -->
<div id="print-area" class="print-only hidden">
    <!-- Content injected via JS before print if needed, or mapped from receiptData -->
</div>

    <!-- DATA PAYLOADS -->
    <script id="data-products" type="application/json">@json($products)</script>
    <script id="data-tables" type="application/json">@json($tables)</script>
    <script id="data-floors" type="application/json">@json($floors ?? [])</script>
    <script id="data-active-sessions" type="application/json">@json($activeSessions ?? [])</script>

    <script src="{{ asset('js/vue.global.js') }}"></script>
    <script src="{{ asset('js/html5-qrcode.min.js') }}"></script>
<script>
    if (typeof Vue === 'undefined') {
        document.body.innerHTML = '<div class="p-10 text-center text-red-600 font-bold text-xl">Erreur de chargement des scripts (Vue.js). Vérifiez votre connexion internet.</div>';
        throw new Error("Vue is not defined");
    }
    const { createApp, ref, computed, onMounted, watch, nextTick } = Vue;

    createApp({
        setup() {
            // Helper for safe JSON parsing
            const getJsonData = (id) => {
                try {
                    const el = document.getElementById(id);
                    return el && el.textContent ? JSON.parse(el.textContent) : [];
                } catch (e) {
                    console.error('JSON Parse Error for ' + id, e);
                    return [];
                }
            };

            // Data Initialization
            const products = ref(getJsonData('data-products'));
            const tables = ref(getJsonData('data-tables'));
            const floors = ref(getJsonData('data-floors'));
            const activeSessions = ref(getJsonData('data-active-sessions'));
            
            const favorites = ref(JSON.parse(localStorage.getItem('favorites') || '[]'));
            
            // State
            const viewMode = ref('dashboard'); // 'dashboard', 'menu'
            const activeFloorId = ref(floors.value.length > 0 ? floors.value[0].id : null);
            const q = ref('');
            const selectedCategory = ref(null);
            const cart = ref([]);
            const selectedTableId = ref(null);
            
            // UI State
            const showTableModal = ref(false);
            const showBillModal = ref(false);
            const showCameraModal = ref(false);
            const showReadyModal = ref(false); 
            const showAssignModal = ref(false);
            const currentQrToken = ref(null);
            const isSubmitting = ref(false);
            const isPrinting = ref(false);
            const showReceiptPreview = ref(false);
            const receiptData = ref(null);
            const isProcessingQr = ref(false); // New state for QR processing
            
            // Product Options State
            const showOptionModal = ref(false);
            const showVariantModal = ref(false);
            const selectedProductForOptions = ref(null);
            const tempCartItem = ref(null);
            const selectedProductForVariant = ref(null);
            const selectedOptions = ref({});
            const validationErrors = ref({});
            const currentStepTotal = ref(0);
            
            // Manual Entry
            const manualTableIdInput = ref('');
            
            // Async Data
            const billData = ref(null);
            const liveItems = ref([]);
            const readyItems = ref([]); 
            
            // Audio Notification State
            const isSoundPlaying = ref(false);
            const audioInterval = ref(null);

            const stopNotificationSound = () => {
                if (audioInterval.value) {
                    clearInterval(audioInterval.value);
                    audioInterval.value = null;
                }
                isSoundPlaying.value = false;
            };

            const playBeep = () => {
                 try {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    if (!AudioContext) return;
                    
                    const ctx = new AudioContext();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    
                    osc.type = 'sine'; 
                    osc.frequency.setValueAtTime(500, ctx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(1000, ctx.currentTime + 0.1);
                    
                    gain.gain.setValueAtTime(0.2, ctx.currentTime); 
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.5);
                    
                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + 0.5);
                } catch (e) {
                    console.error("Audio error", e);
                }
            };

            const playNotificationSound = () => {
                // If already playing, do nothing
                if (isSoundPlaying.value) return;
                
                isSoundPlaying.value = true;
                
                // Play immediately
                playBeep();
                
                // Loop every 2 seconds
                audioInterval.value = setInterval(() => {
                    playBeep();
                }, 2000);
            };

            // Watch ready items for notifications
            watch(readyItems, (newItems) => {
                if (newItems.length > 0) {
                    // Ensure sound is playing if there are ready items
                    playNotificationSound();
                } else {
                    // Stop sound if no ready items
                    stopNotificationSound();
                }
            });

            // Camera vars
            let html5QrCode = null;
            let scanning = false;

            // Computed
            const categories = computed(() => {
                const cats = new Set(products.value.map(p => p.category).filter(Boolean));
                return Array.from(cats).sort();
            });

            const currentFloorTables = computed(() => {
                if (floors.value.length === 0) return tables.value.map(t => ({...t, x_position: 50, y_position: 50}));
                const floor = floors.value.find(f => f.id === activeFloorId.value);
                return floor ? floor.tables : [];
            });

            const filteredProducts = computed(() => {
                let filtered = products.value;
                if (selectedCategory.value) {
                    filtered = filtered.filter(p => p.category === selectedCategory.value);
                }
                if (q.value.trim()) {
                    const term = q.value.trim().toLowerCase();
                    filtered = filtered.filter(p => 
                        (p.name || '').toLowerCase().includes(term)
                    );
                }
                return filtered;
            });

            const cartTotal = computed(() => {
                return cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            });

            const cartCount = computed(() => {
                return cart.value.reduce((sum, item) => sum + item.quantity, 0);
            });

            const currentOptionsTotal = computed(() => {
                if (!selectedProductForOptions.value) return 0;
                let total = 0;
                selectedProductForOptions.value.option_groups.forEach(group => {
                    const val = selectedOptions.value[group.id];
                    if (!val) return;
                    
                    const options = group.options;
                    if (group.type === 'radio') {
                        const opt = options.find(o => o.id === val);
                        if (opt) total += Number(opt.price_adjustment);
                    } else if (Array.isArray(val)) {
                        val.forEach(id => {
                            const opt = options.find(o => o.id === id);
                            if (opt) total += Number(opt.price_adjustment);
                        });
                    }
                });
                return total;
            });

            const isOptionsValid = computed(() => {
                if (!selectedProductForOptions.value) return false;
                
                let isValid = true;
                selectedProductForOptions.value.option_groups.forEach(group => {
                    const val = selectedOptions.value[group.id];
                    
                    if (group.is_required) {
                        if (group.type === 'radio' && !val) {
                            isValid = false;
                        } else if (group.type === 'checkbox' && (!val || val.length < group.min_selection)) {
                            isValid = false;
                        }
                    }
                    
                    if (group.type === 'checkbox' && val && val.length > group.max_selection) {
                        isValid = false;
                    }
                });
                return isValid;
            });

            // Methods
            const fetchActiveSessions = () => {
                fetch('/restaurant/pos/active-sessions')
                    .then(r => r.json())
                    .then(res => activeSessions.value = res)
                    .catch(console.error);
            };

            const fetchReadyItems = () => {
                fetch('/restaurant/pos/ready-items')
                    .then(r => r.json())
                    .then(res => readyItems.value = res)
                    .catch(console.error);
            };

            const markItemServed = (itemId) => {
                fetch(`/restaurant/pos/mark-served/${itemId}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                })
                .then(r => r.json())
                .then(res => {
                    if(res.success) {
                        fetchReadyItems();
                        fetchActiveSessions();
                    }
                });
            };

            const openSession = (session) => {
                if (session.table_id) {
                    selectedTableId.value = session.table_id;
                    viewMode.value = 'menu';
                } else {
                    alert('Session ID: ' + session.id + ' (Table ID manquant)');
                }
            };

            const getTableStatus = (tableId) => {
                // Simplified status check based on active sessions
                const session = activeSessions.value.find(s => s.table_id === tableId);
                if (session) return 'occupied';
                return 'free';
            };

            const getTableStatusLabel = (tableId) => {
                const status = getTableStatus(tableId);
                return status === 'occupied' ? 'OCCUPÉ' : 'LIBRE';
            };

            const isTableOccupied = (tableId) => getTableStatus(tableId) !== 'free';

            const isFav = (id) => favorites.value.includes(id);

            const toggleFav = (id) => {
                const index = favorites.value.indexOf(id);
                if (index === -1) favorites.value.push(id);
                else favorites.value.splice(index, 1);
                localStorage.setItem('favorites', JSON.stringify(favorites.value));
            };

            const getTableLabel = (id) => {
                if (!id) return '?';
                if (floors.value.length > 0) {
                     for (const f of floors.value) {
                         const t = f.tables.find(x => x.id == id);
                         if (t) return t.label;
                     }
                }
                const t = tables.value.find(x => x.id == id);
                return t ? t.label : id;
            };

            const selectTable = (id) => {
                selectedTableId.value = id;
                showTableModal.value = false;
                viewMode.value = 'menu';
            };

            // Options Logic
            const openOptionsModal = (product) => {
                selectedProductForOptions.value = product;
                selectedOptions.value = {};
                validationErrors.value = {};
                
                // Initialize default values
                product.option_groups.forEach(group => {
                    if (group.type === 'checkbox') {
                        selectedOptions.value[group.id] = [];
                    } else {
                        selectedOptions.value[group.id] = null;
                    }
                });
                
                showOptionModal.value = true;
            };

            const closeOptionsModal = () => {
                showOptionModal.value = false;
                selectedProductForOptions.value = null;
                selectedOptions.value = {};
            };

            const isOptionSelected = (group, optionId) => {
                const val = selectedOptions.value[group.id];
                if (group.type === 'radio') return val === optionId;
                return Array.isArray(val) && val.includes(optionId);
            };
            
            const getSelectionLabel = (group) => {
                if (group.type === 'radio') return 'Choix unique';
                return `Choix multiple (${group.min_selection}-${group.max_selection})`;
            };

            const confirmOptions = () => {
                let isValid = true;
                validationErrors.value = {};
                
                selectedProductForOptions.value.option_groups.forEach(group => {
                    const val = selectedOptions.value[group.id];
                    
                    if (group.is_required) {
                        if (group.type === 'radio' && !val) {
                            validationErrors.value[group.id] = 'Ce choix est obligatoire';
                            isValid = false;
                        } else if (group.type === 'checkbox' && (!val || val.length < group.min_selection)) {
                            validationErrors.value[group.id] = `Sélectionnez au moins ${group.min_selection} option(s)`;
                            isValid = false;
                        }
                    }
                    
                    if (group.type === 'checkbox' && val && val.length > group.max_selection) {
                        validationErrors.value[group.id] = `Maximum ${group.max_selection} option(s)`;
                        isValid = false;
                    }
                });
                
                if (!isValid) return;

                const product = selectedProductForOptions.value;
                const finalOptions = [];
                
                product.option_groups.forEach(group => {
                    const val = selectedOptions.value[group.id];
                    if (!val) return;
                    
                    if (group.type === 'radio') {
                         const opt = group.options.find(o => o.id === val);
                         if (opt) finalOptions.push({ ...opt, group_name: group.name });
                    } else if (Array.isArray(val)) {
                        val.forEach(id => {
                            const opt = group.options.find(o => o.id === id);
                            if (opt) finalOptions.push({ ...opt, group_name: group.name });
                        });
                    }
                });

                cart.value.push({
                    id: product.id,
                    name: product.name,
                    price: Number(product.price) + currentOptionsTotal.value,
                    quantity: 1,
                    options: finalOptions
                });
                
                closeOptionsModal();
            };

            const closeVariantModal = () => {
                showVariantModal.value = false;
                selectedProductForVariant.value = null;
            };

            const selectVariant = (variant) => {
                closeVariantModal();
                // Treat the variant as a product and check for options
                addToCart(variant);
            };

            const addToCart = (product) => {
                // 1. Check for variants (children)
                if (product.children && product.children.length > 0) {
                    selectedProductForVariant.value = product;
                    showVariantModal.value = true;
                    return;
                }

                // 2. Check for options
                if (product.option_groups && product.option_groups.length > 0) {
                    openOptionsModal(product);
                } else {
                    // 3. Add directly
                    const existing = cart.value.find(i => i.id === product.id && (!i.options || i.options.length === 0));
                    if (existing) {
                        existing.quantity++;
                    } else {
                        cart.value.push({
                            id: product.id,
                            name: product.name,
                            price: Number(product.price),
                            quantity: 1,
                            options: []
                        });
                    }
                }
            };

            const updateQty = (index, delta) => {
                const item = cart.value[index];
                item.quantity += delta;
                if (item.quantity <= 0) {
                    cart.value.splice(index, 1);
                }
            };

            const resetView = () => {
                if (cart.value.length > 0 && !confirm('Vider le panier et revenir ?')) return;
                cart.value = [];
                q.value = '';
                selectedCategory.value = null;
                selectedTableId.value = null;
                viewMode.value = 'dashboard';
            };

            const submitOrder = () => {
                if (!selectedTableId.value) {
                    showTableModal.value = true;
                    return;
                }
                
                isSubmitting.value = true;
                
                fetch('/restaurant/pos/order', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                    },
                    body: JSON.stringify({
                        table_id: selectedTableId.value,
                        items: cart.value.map(i => ({ 
                            id: i.id, 
                            quantity: i.quantity,
                            options: i.options ? i.options.map(o => o.id) : [] 
                        }))
                    })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.order_id) {
                        cart.value = [];
                        alert("Commande envoyée !");
                        fetchActiveSessions();
                        viewMode.value = 'dashboard';
                    } else {
                        alert('Erreur: ' + (res.message || 'Inconnue'));
                    }
                })
                .catch(() => alert('Erreur réseau'))
                .finally(() => {
                    isSubmitting.value = false;
                });
            };

            const showBill = () => {
                if (!selectedTableId.value) {
                    alert("Veuillez d'abord sélectionner une table.");
                    showTableModal.value = true;
                    return;
                }
                
                billData.value = null;
                showBillModal.value = true;
                
                fetch('/restaurant/pos/session/' + selectedTableId.value)
                    .then(r => r.json())
                    .then(data => {
                        billData.value = data;
                    })
                    .catch(err => {
                        console.error(err);
                        alert("Erreur lors du chargement de l'addition");
                        showBillModal.value = false;
                    });
            };

            const closeBillModal = () => {
                showBillModal.value = false;
            };

            const printBill = () => {
                if (!billData.value || !billData.value.session_id) return;

                isPrinting.value = true;
                fetch(`/restaurant/pos/session/${billData.value.session_id}/print`)
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            receiptData.value = res.data;
                            showReceiptPreview.value = true;
                        } else {
                            alert("Erreur: " + (res.error || 'Inconnue'));
                        }
                    })
                    .finally(() => isPrinting.value = false);
            };

            const printReceipt = () => {
                // Simple print logic for now, utilizing the preview
                const content = document.getElementById('receipt-content').innerHTML;
                const printWindow = window.open('', '', 'height=600,width=400');
                printWindow.document.write('<html><head><title>Ticket</title>');
                printWindow.document.write('<style>body{font-family:monospace; font-size: 12px;} .text-center{text-align:center} .flex{display:flex;justify-content:space-between} .font-bold{font-weight:bold} .text-xs{font-size:10px}</style>');
                printWindow.document.write('</head><body>');
                printWindow.document.write(content);
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.print();
            };

            const payBill = (method) => {
                if (!billData.value || !billData.value.session_id) return;
                if (!confirm("Confirmer le paiement (" + method + ") et clôturer la session ?")) return;
                
                fetch(`/restaurant/pos/session/${billData.value.session_id}/close`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                    },
                    body: JSON.stringify({ payment_method: method })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        alert("✅ Session clôturée avec succès !");
                        closeBillModal();
                        selectedTableId.value = null;
                        fetchActiveSessions();
                        viewMode.value = 'dashboard';
                    } else {
                        alert("Erreur: " + (res.error || 'Inconnue'));
                    }
                });
            };
            
            // Camera functions
            const startCamera = () => {
                // Ensure previous instance is stopped
                stopCamera().then(() => {
                    showCameraModal.value = true;
                    scanning = true;

                    // Reset Status
                    const statusEl = document.getElementById('scan-status');
                    if(statusEl) {
                         statusEl.textContent = "Placez le QR Code au centre";
                         statusEl.className = "text-sm font-bold text-center px-4 py-2 rounded-lg bg-white dark:bg-gray-700 shadow-sm border dark:border-gray-600 mb-6 w-full transition-all duration-300";
                    }

                    // Wait for DOM
                    setTimeout(() => {
                        html5QrCode = new Html5Qrcode("qr-reader");
                        
                        const config = { 
                            fps: 10, 
                            qrbox: { width: 200, height: 200 },
                            aspectRatio: 1.0,
                            disableFlip: false,
                        };
                        
                        html5QrCode.start(
                            { facingMode: "environment" }, 
                            config, 
                            (decodedText, decodedResult) => {
                                // Success callback
                                console.log(`QR Code Detected: ${decodedText}`);
                                handleQrCode(decodedText);
                            },
                            (errorMessage) => {
                                // Error callback (scanning...)
                                // console.log(errorMessage);
                            }
                        ).catch((err) => {
                            console.error("Erreur démarrage caméra:", err);
                            alert("Impossible d'accéder à la caméra: " + err);
                            showCameraModal.value = false;
                        });
                    }, 300);
                });
            };

            const stopCamera = () => {
                scanning = false;
                
                return new Promise((resolve) => {
                    if (html5QrCode && html5QrCode.isScanning) {
                        html5QrCode.stop().then(() => {
                            html5QrCode.clear();
                            html5QrCode = null;
                            showCameraModal.value = false;
                            resolve();
                        }).catch(err => {
                            console.error("Failed to stop html5QrCode", err);
                            showCameraModal.value = false;
                            resolve();
                        });
                    } else {
                        showCameraModal.value = false;
                        resolve();
                    }
                });
            };
            
            // Old tick function removed as Html5Qrcode handles the loop

            const updateScanStatus = (msg, isError = false) => {
                const el = document.getElementById('scan-status');
                if (el) {
                    el.textContent = msg;
                    el.className = isError 
                        ? "text-white bg-red-600 text-center px-4 py-2 rounded-lg text-sm font-bold shadow-md mb-6 w-full transition-all duration-300"
                        : "text-white bg-green-600 text-center px-4 py-2 rounded-lg text-sm font-bold shadow-md mb-6 w-full transition-all duration-300";
                }
            };

            const assignTable = (tableId) => {
                if (!currentQrToken.value) return;

                fetch('/restaurant/pos/assign-table', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        qr_token: currentQrToken.value,
                        table_id: tableId
                    })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        showAssignModal.value = false;
                        currentQrToken.value = null;
                        alert("Table assignée avec succès !");
                        selectTable(tableId);
                    } else {
                        alert("Erreur: " + res.message);
                    }
                })
                .catch(e => alert("Erreur connexion: " + e.message));
            };

            const handleQrCode = (data) => {
                if (isProcessingQr.value) return;
                
                isProcessingQr.value = true;
                scanning = false; 
                if(html5QrCode) html5QrCode.pause(); // Pause scanning
                
                playBeep();
                
                updateScanStatus("⌛ Vérification...", false);

                // Call backend to check QR status
                fetch('/restaurant/pos/check-qr', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ qr_data: data })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'active_session') {
                         updateScanStatus(`✅ Table ${res.table_id} détectée !`);
                         setTimeout(() => {
                            stopCamera();
                            selectTable(res.table_id);
                            // Open bill directly as requested
                            showBill();
                            isProcessingQr.value = false;
                         }, 500);
                    } else if (res.status === 'available') {
                         updateScanStatus(`ℹ️ Nouveau QR détecté.`);
                         setTimeout(() => {
                            stopCamera();
                            currentQrToken.value = res.qr_token;
                            showAssignModal.value = true;
                            isProcessingQr.value = false;
                         }, 500);
                    } else {
                         updateScanStatus(`❌ ${res.message}`, true);
                         // Restart scanning after delay
                         setTimeout(() => {
                             isProcessingQr.value = false;
                             scanning = true;
                             if(html5QrCode) html5QrCode.resume();
                             updateScanStatus("Placez le QR Code au centre");
                         }, 2500);
                    }
                })
                .catch(e => {
                    console.error(e);
                    updateScanStatus(`❌ Erreur système`, true);
                    alert("Erreur lors de la vérification du QR code.");
                    // Allow retry
                    setTimeout(() => {
                         isProcessingQr.value = false;
                         scanning = true;
                         if(html5QrCode) html5QrCode.resume();
                    }, 2000);
                });
            };

            const handleManualEntry = () => {
                if (!manualTableIdInput.value) return;
                const id = parseInt(manualTableIdInput.value);
                if (id) {
                    handleQrCode(id.toString());
                    manualTableIdInput.value = '';
                }
            };

            onMounted(() => {
                fetchActiveSessions();
                fetchReadyItems();
                setInterval(() => {
                    fetchActiveSessions();
                    fetchReadyItems();
                }, 5000);
            });

            return {
                products, tables, floors, activeSessions,
                viewMode, activeFloorId, q, selectedCategory, cart, selectedTableId,
                showTableModal, showBillModal, showCameraModal, showReadyModal, showAssignModal, currentQrToken,
                isSubmitting, isPrinting, showReceiptPreview, receiptData,
                billData, categories, currentFloorTables, filteredProducts, cartTotal, cartCount, readyItems,
                fetchActiveSessions, openSession, getTableStatus, getTableStatusLabel, isTableOccupied,
                isFav, toggleFav, getTableLabel, selectTable, addToCart, updateQty, resetView, submitOrder,
                showBill, closeBillModal, printBill, printReceipt, payBill, startCamera, stopCamera, markItemServed,
                isSoundPlaying, stopNotificationSound, manualTableIdInput, handleManualEntry, assignTable,
                // Options & Variants
                showOptionModal, showVariantModal, selectedProductForOptions, selectedProductForVariant, selectedOptions, validationErrors, currentStepTotal, currentOptionsTotal, isOptionsValid,
                openOptionsModal, closeOptionsModal, isOptionSelected, getSelectionLabel, confirmOptions, toggleOption: (group, opt) => {
                     // Toggle logic inline or separate method?
                     // Let's implement toggleOption properly
                     const val = selectedOptions.value[group.id];
                     if (group.type === 'radio') {
                         selectedOptions.value[group.id] = opt.id;
                     } else {
                         // Checkbox
                         if (!Array.isArray(val)) selectedOptions.value[group.id] = [];
                         const idx = selectedOptions.value[group.id].indexOf(opt.id);
                         if (idx === -1) {
                             if (selectedOptions.value[group.id].length < group.max_selection) {
                                 selectedOptions.value[group.id].push(opt.id);
                             }
                         } else {
                             selectedOptions.value[group.id].splice(idx, 1);
                         }
                     }
                },
                getValidationMessage: (group) => validationErrors.value[group.id],
                closeVariantModal, selectVariant, tempCartItem
            };
        }
    }).mount('#waiter-app');
</script>
@endsection
