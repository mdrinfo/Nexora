<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $type === 'kitchen' ? 'KDS Cuisine' : 'KDS Bar' }} - Nexora</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        [v-cloak] {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">
    <div id="kds-app" class="h-screen flex flex-col overflow-hidden" v-cloak>
        <!-- Header -->
        <div class="bg-white shadow-md border-b border-gray-200 p-4 z-20">
            <div class="max-w-7xl mx-auto w-full flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg" :class="type === 'kitchen' ? 'bg-orange-100 text-orange-600' : 'bg-purple-100 text-purple-600'">
                        <svg v-if="type === 'kitchen'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 00-2 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6a2 2 0 00-2-2H4a1 1 0 01-1-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800 tracking-tight">
                        {{ $type === 'kitchen' ? 'KDS Cuisine' : 'KDS Bar' }}
                    </h1>
                </div>

                <div class="flex items-center gap-4">
                    <button @click="toggleSound" 
                        class="flex items-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all duration-200 border"
                        :class="soundEnabled ? 'bg-green-100 text-green-700 border-green-200 hover:bg-green-200' : 'bg-gray-100 text-gray-600 border-gray-200 hover:bg-gray-200'">
                        <span class="text-lg">@{{ soundEnabled ? '🔊' : '🔇' }}</span>
                        <span>@{{ soundEnabled ? 'Son: ON' : 'Son: OFF' }}</span>
                    </button>

                    <div class="flex items-center gap-2 bg-gray-100 px-3 py-2 rounded-lg border border-gray-200">
                        <span class="relative flex h-3 w-3">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                        </span>
                        <span class="text-sm font-medium text-gray-600">@{{ lastRefreshed }}</span>
                    </div>

                    <button @click="fetchItems" 
                        class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg shadow-sm font-medium transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed"
                        :disabled="isLoading">
                        <svg v-if="isLoading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Actualiser</span>
                    </button>
                    
                    <a href="{{ route('home') }}" class="ml-2 text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-hidden p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 h-full max-w-7xl mx-auto">
                
                <!-- Pending Column -->
                <div class="flex flex-col bg-gray-200 rounded-xl overflow-hidden shadow-inner border border-gray-300">
                    <div class="p-4 bg-red-600 text-white shadow-md flex justify-between items-center">
                        <h2 class="text-lg font-bold uppercase tracking-wider flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            En Attente
                        </h2>
                        <span class="bg-red-800 text-white px-3 py-1 rounded-full text-sm font-bold">@{{ pendingOrders.length }}</span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-4 space-y-4" :class="{'bg-red-50 animate-pulse': flashAlert}">
                        <div v-for="order in pendingOrders" :key="order.order_id" 
                            class="bg-white p-0 rounded-lg shadow-sm border-l-4 border-red-500 hover:shadow-md transition-shadow duration-200">
                            <div class="p-4">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 rounded text-xs font-extrabold tracking-wide border" :class="orderTypeBadgeClass(order)">
                                            @{{ orderTypeBadgeText(order) }}
                                        </span>
                                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-sm font-bold border border-gray-200">
                                            @{{ displayTableName(order) }}
                                        </span>
                                    </div>
                                    <span class="text-xs font-mono text-gray-500 flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        @{{ formatTime(order.created_at) }}
                                    </span>
                                </div>
                                <div class="mb-4 space-y-1">
                                    <div v-for="item in order.items" :key="item.id" class="text-gray-800 font-medium border-b border-gray-100 pb-1 last:border-0">
                                        <span class="text-red-600 font-bold">@{{ item.quantity }}x</span> @{{ item.name }}
                                        <div v-if="item.options && item.options.length > 0" class="pl-6 text-sm text-gray-600 font-normal">
                                            <div v-for="opt in item.options" :key="opt.id">
                                                + @{{ (opt.product_option_group_name ? (opt.product_option_group_name + ': ') : '') + opt.product_option_name }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button @click="startOrder(order.order_id)" 
                                    class="w-full py-2.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-lg font-bold text-sm uppercase tracking-wide transition-colors flex justify-center items-center gap-2">
                                    <span>Commencer la commande</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div v-if="pendingOrders.length === 0" class="h-full flex flex-col items-center justify-center text-gray-400 opacity-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <p class="font-medium">Aucune commande</p>
                        </div>
                    </div>
                </div>

                <!-- Preparing Column -->
                <div class="flex flex-col bg-gray-200 rounded-xl overflow-hidden shadow-inner border border-gray-300">
                    <div class="p-4 bg-yellow-500 text-white shadow-md flex justify-between items-center">
                        <h2 class="text-lg font-bold uppercase tracking-wider flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            En Préparation
                        </h2>
                        <span class="bg-yellow-700 text-white px-3 py-1 rounded-full text-sm font-bold">@{{ preparingOrders.length }}</span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-4 space-y-4">
                        <div v-for="order in preparingOrders" :key="order.order_id" 
                            class="bg-white p-0 rounded-lg shadow-sm border-l-4 border-yellow-500 hover:shadow-md transition-shadow duration-200">
                            <div class="p-4">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 rounded text-xs font-extrabold tracking-wide border" :class="orderTypeBadgeClass(order)">
                                            @{{ orderTypeBadgeText(order) }}
                                        </span>
                                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-sm font-bold border border-gray-200">
                                            @{{ displayTableName(order) }}
                                        </span>
                                    </div>
                                    <span class="text-xs font-mono text-gray-500">@{{ formatTime(order.created_at) }}</span>
                                </div>
                                <div class="space-y-2">
                                    <div v-for="item in order.items" :key="item.id" 
                                         class="flex items-center justify-between p-2 rounded-lg border transition-colors cursor-pointer"
                                         :class="item.status === 'ready' ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200 hover:bg-gray-100'"
                                         @click="item.status !== 'ready' && updateStatus(item.id, 'ready')">
                                        <div class="flex items-center gap-3">
                                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors"
                                                 :class="item.status === 'ready' ? 'bg-green-500 border-green-500' : 'border-gray-300 bg-white'">
                                                <svg v-if="item.status === 'ready'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <span class="font-bold text-gray-800" :class="{'line-through text-gray-400': item.status === 'ready'}">
                                                <span :class="item.status === 'ready' ? 'text-gray-400' : 'text-yellow-600'">@{{ item.quantity }}x</span> @{{ item.name }}
                                                <div v-if="item.options && item.options.length > 0" class="pl-6 text-sm font-normal" :class="item.status === 'ready' ? 'text-gray-400' : 'text-gray-600'">
                                                    <div v-for="opt in item.options" :key="opt.id">
                                                        + @{{ (opt.product_option_group_name ? (opt.product_option_group_name + ': ') : '') + opt.product_option_name }}
                                                    </div>
                                                </div>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ready Column -->
                <div class="flex flex-col bg-gray-200 rounded-xl overflow-hidden shadow-inner border border-gray-300 opacity-90">
                    <div class="p-4 bg-green-600 text-white shadow-md flex justify-between items-center">
                        <h2 class="text-lg font-bold uppercase tracking-wider flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Prêt / À Servir
                        </h2>
                        <span class="bg-green-800 text-white px-3 py-1 rounded-full text-sm font-bold">@{{ readyOrders.length }}</span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-4 space-y-4">
                        <div v-for="order in readyOrders" :key="order.order_id" 
                            class="bg-green-50 p-4 rounded-lg border border-green-200 opacity-75 hover:opacity-100 transition-opacity">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-1 rounded text-xs font-extrabold tracking-wide border" :class="orderTypeBadgeClass(order)">
                                        @{{ orderTypeBadgeText(order) }}
                                    </span>
                                    <span class="font-bold text-lg text-gray-800">@{{ displayTableName(order) }}</span>
                                </div>
                                <span class="text-xs text-green-700 font-bold bg-green-100 px-2 py-0.5 rounded-full">Prêt</span>
                            </div>
                            <div class="space-y-1">
                                <div v-for="item in order.items" :key="item.id" class="text-lg text-gray-700 font-medium">
                                    @{{ item.quantity }}x @{{ item.name }}
                                    <div v-if="item.options && item.options.length > 0" class="pl-6 text-sm text-gray-500">
                                        <div v-for="opt in item.options" :key="opt.id">
                                            + @{{ (opt.product_option_group_name ? (opt.product_option_group_name + ': ') : '') + opt.product_option_name }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Vue.js -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <script>
        new Vue({
            el: '#kds-app',
            data: {
                type: '{{ $type }}',
                orders: [],
                lastRefreshed: '--:--',
                timer: null,
                soundEnabled: false,
                flashAlert: false,
                isLoading: false,
                audioContext: null
            },
            computed: {
                pendingOrders() { return this.orders.filter(o => o.status === 'pending'); },
                preparingOrders() { return this.orders.filter(o => o.status === 'preparing'); },
                readyOrders() { return this.orders.filter(o => o.status === 'ready'); }
            },
            mounted() {
                this.fetchItems();
                this.timer = setInterval(this.fetchItems, 5000); // Poll every 5s
                
                // Try to init AudioContext on first interaction
                document.addEventListener('click', this.initAudioContext, { once: true });
            },
            beforeDestroy() {
                clearInterval(this.timer);
            },
            methods: {
                formatTime(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
                },
                orderType(order) {
                    const raw = (order && (order.session_type || order.type)) ? String(order.session_type || order.type).toLowerCase() : 'table';
                    if (raw === 'online' || raw === 'takeaway' || raw === 'table') return raw;
                    return 'table';
                },
                orderTypeBadgeText(order) {
                    const t = this.orderType(order);
                    if (t === 'online') return '🌐 ONLINE';
                    if (t === 'takeaway') return '🥡 PAKET';
                    return '🍽️ MASA';
                },
                orderTypeBadgeClass(order) {
                    const t = this.orderType(order);
                    if (t === 'online') return 'bg-blue-600 text-white border-blue-700';
                    if (t === 'takeaway') return 'bg-orange-600 text-white border-orange-700';
                    return 'bg-gray-700 text-white border-gray-800';
                },
                displayTableName(order) {
                    if (order && order.table_name) return order.table_name;
                    const t = this.orderType(order);
                    if (t === 'online') return 'ONLINE';
                    if (t === 'takeaway') return 'PAKET';
                    return '?';
                },
                initAudioContext() {
                    if (!this.audioContext) {
                        this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    }
                    if (this.audioContext.state === 'suspended') {
                        this.audioContext.resume();
                    }
                },
                toggleSound() {
                    this.soundEnabled = !this.soundEnabled;
                    if (this.soundEnabled) {
                        this.initAudioContext();
                        this.playBeep();
                    }
                },
                playNotificationSound() {
                    if (!this.soundEnabled || !this.audioContext) return;
                    
                    const t = this.audioContext.currentTime;
                    
                    // Oscillator 1: Fundamental
                    const osc1 = this.audioContext.createOscillator();
                    const gain1 = this.audioContext.createGain();
                    osc1.connect(gain1);
                    gain1.connect(this.audioContext.destination);
                    
                    osc1.type = 'sine';
                    osc1.frequency.setValueAtTime(660, t); // E5
                    gain1.gain.setValueAtTime(0, t);
                    gain1.gain.linearRampToValueAtTime(0.3, t + 0.05);
                    gain1.gain.exponentialRampToValueAtTime(0.001, t + 1.2);
                    
                    osc1.start(t);
                    osc1.stop(t + 1.2);

                    // Oscillator 2: Harmony
                    const osc2 = this.audioContext.createOscillator();
                    const gain2 = this.audioContext.createGain();
                    osc2.connect(gain2);
                    gain2.connect(this.audioContext.destination);
                    
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(880, t); // A5
                    gain2.gain.setValueAtTime(0, t);
                    gain2.gain.linearRampToValueAtTime(0.3, t + 0.05);
                    gain2.gain.exponentialRampToValueAtTime(0.001, t + 1.2);
                    
                    osc2.start(t);
                    osc2.stop(t + 1.2);
                    
                    // Oscillator 3: "Ding" high pitch
                    setTimeout(() => {
                        const t2 = this.audioContext.currentTime;
                        const osc3 = this.audioContext.createOscillator();
                        const gain3 = this.audioContext.createGain();
                        osc3.connect(gain3);
                        gain3.connect(this.audioContext.destination);
                        
                        osc3.type = 'triangle';
                        osc3.frequency.setValueAtTime(1320, t2); // E6
                        gain3.gain.setValueAtTime(0, t2);
                        gain3.gain.linearRampToValueAtTime(0.1, t2 + 0.02);
                        gain3.gain.exponentialRampToValueAtTime(0.001, t2 + 0.8);
                        
                        osc3.start(t2);
                        osc3.stop(t2 + 0.8);
                    }, 100);
                },
                async fetchItems() {
                    if (this.isLoading) return;
                    this.isLoading = true;
                    try {
                        const response = await fetch(`/kds/items?type=${this.type}`);
                        const data = await response.json();
                        
                        // Check for new pending orders to alert
                        // We compare new data (data) against current state (this.orders)
                        // specifically looking for new Order IDs that are pending
                        const currentPendingIds = new Set(
                            this.orders.filter(o => o.status === 'pending').map(o => o.order_id)
                        );
                        
                        const incomingPendingOrders = data.filter(o => o.status === 'pending');
                        const hasNewOrder = incomingPendingOrders.some(o => !currentPendingIds.has(o.order_id));
                        
                        // Also play sound if total count increased (fallback)
                        const countIncreased = incomingPendingOrders.length > currentPendingIds.size;

                        if (hasNewOrder || countIncreased) {
                            console.log("New order detected! Playing sound.");
                            this.playNotificationSound();
                            this.flashAlert = true;
                            setTimeout(() => this.flashAlert = false, 3000);
                        }
                        
                        this.orders = data;
                        this.lastRefreshed = new Date().toLocaleTimeString('fr-FR');
                    } catch (error) {
                        console.error('Error fetching items:', error);
                    } finally {
                        this.isLoading = false;
                    }
                },
                async updateStatus(itemId, newStatus) {
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const response = await fetch(`/kds/item/${itemId}/status`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({
                                status: newStatus
                            })
                        });
                        
                        if (response.ok) {
                            this.fetchItems(); // Refresh immediately
                        }
                    } catch (error) {
                        console.error('Error updating status:', error);
                    }
                },
                async startOrder(orderId) {
                     // Find the order
                     const order = this.orders.find(o => o.order_id === orderId);
                     if (!order) return;
                     
                     // Mark all items as preparing
                     for (const item of order.items) {
                         if (item.status === 'pending') {
                             await this.updateStatus(item.id, 'preparing');
                         }
                     }
                }
            }
        });
    </script>
</body>
</html>
