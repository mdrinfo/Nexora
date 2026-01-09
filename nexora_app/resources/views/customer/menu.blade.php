<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexora Menü</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.2/axios.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <div id="app" class="pb-24">
        <!-- Data Payload -->
        <script id="data-products" type="application/json">@json($products)</script>
        <script id="data-table" type="application/json">@json($table)</script>
        <div id="app-config" 
             data-mode="{{ $mode }}" 
             data-session-type="{{ $sessionType }}"
             style="display: none;"></div>

        <!-- Header -->
        <header class="bg-white shadow-sm sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
                <h1 class="text-xl font-bold text-gray-800">
                    <span v-if="mode === 'table'">Masa: @{{ tableName }}</span>
                    <span v-else-if="mode === 'online'">🌐 Online Sipariş</span>
                    <span v-else>🥡 Paket Sipariş</span>
                </h1>
                <div v-if="cartTotal > 0" class="text-green-600 font-bold">
                    @{{ formatPrice(cartTotal) }}
                </div>
            </div>
            
            <!-- Category Tabs -->
            <div class="overflow-x-auto whitespace-nowrap px-4 pb-2 hide-scrollbar">
                <button 
                    v-for="(items, category) in products" 
                    :key="category"
                    @click="scrollToCategory(category)"
                    class="inline-block px-4 py-2 mr-2 rounded-full text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300"
                >
                    @{{ category }}
                </button>
            </div>
        </header>

        <!-- Product List -->
        <main class="max-w-7xl mx-auto px-4 py-6 space-y-8">
            <div v-for="(items, category) in products" :key="category" :id="'cat-' + category">
                <h2 class="text-lg font-bold text-gray-800 mb-4">@{{ category }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="product in items" :key="product.id" class="bg-white rounded-lg shadow p-4 flex justify-between items-center">
                        <div>
                            <h3 class="font-medium text-gray-900">@{{ product.name }}</h3>
                            <p class="text-gray-500 text-sm">@{{ formatPrice(product.price) }}</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button @click="openProductModal(product)" class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-sm font-bold hover:bg-blue-200">
                                Ekle
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Product Options Modal -->
        <div v-if="selectedProduct" class="fixed inset-0 bg-black bg-opacity-50 flex items-end sm:items-center justify-center z-50 p-4">
            <div class="bg-white rounded-t-2xl sm:rounded-2xl w-full max-w-md overflow-hidden flex flex-col max-h-[90vh]">
                <div class="p-4 border-b flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold">@{{ selectedProduct.name }}</h3>
                    <button @click="closeModal" class="text-gray-500 hover:text-gray-700">&times;</button>
                </div>
                
                <div class="p-4 overflow-y-auto flex-grow">
                    <div v-for="group in selectedProduct.option_groups" :key="group.id" class="mb-6">
                        <h4 class="font-bold text-gray-700 mb-2">
                            @{{ group.name }} 
                            <span v-if="group.is_required" class="text-red-500 text-xs">(Zorunlu)</span>
                        </h4>
                        
                        <div class="space-y-2">
                            <div v-for="opt in group.options" :key="opt.id" class="flex items-center justify-between">
                                <label class="flex items-center space-x-3 cursor-pointer w-full">
                                    <input 
                                        v-if="group.type === 'single'" 
                                        type="radio" 
                                        :name="'group-' + group.id" 
                                        :value="opt.id" 
                                        v-model="selectedOptions[group.id]"
                                        class="form-radio text-blue-600 h-5 w-5"
                                    >
                                    <input 
                                        v-else 
                                        type="checkbox" 
                                        :value="opt.id" 
                                        v-model="selectedOptions[group.id]" 
                                        class="form-checkbox text-blue-600 h-5 w-5"
                                    >
                                    <span class="text-gray-700">@{{ opt.name }}</span>
                                </label>
                                <span v-if="opt.price_adjustment > 0" class="text-gray-500 text-sm">
                                    +@{{ formatPrice(opt.price_adjustment) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Özel Not</label>
                        <textarea v-model="productNote" rows="2" class="w-full border rounded-lg p-2 text-sm" placeholder="Varsa özel isteğiniz..."></textarea>
                    </div>
                </div>

                <div class="p-4 border-t bg-gray-50">
                    <button 
                        @click="confirmAddToCart" 
                        class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition flex justify-between px-6"
                    >
                        <span>Sepete Ekle</span>
                        <span>@{{ formatPrice(calculateModalTotal()) }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Floating Cart Button -->
        <div v-if="cart.length > 0" class="fixed bottom-0 left-0 w-full bg-white shadow-lg border-t p-4 z-40">
            <div class="max-w-7xl mx-auto">
                <!-- Cart Preview -->
                <div class="max-h-40 overflow-y-auto mb-4 space-y-2">
                     <div v-for="(item, index) in cart" :key="index" class="flex justify-between items-start text-sm border-b pb-2">
                        <div>
                            <div class="font-bold">@{{ item.quantity }}x @{{ item.name }}</div>
                            <div v-if="item.optionsDisplay.length > 0" class="text-gray-500 text-xs">
                                @{{ item.optionsDisplay.join(', ') }}
                            </div>
                            <div v-if="item.note" class="text-gray-500 text-xs italic">Not: @{{ item.note }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                             <span>@{{ formatPrice(item.totalPrice) }}</span>
                             <button @click="removeFromCart(index)" class="text-red-500 font-bold">&times;</button>
                        </div>
                     </div>
                </div>

                <div class="flex justify-between items-center">
                    <div>
                        <span class="block text-sm text-gray-500">Toplam</span>
                        <span class="text-xl font-bold text-gray-900">@{{ formatPrice(cartTotal) }}</span>
                    </div>
                    <button 
                        @click="submitOrder" 
                        :disabled="loading"
                        class="bg-green-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-green-700 disabled:opacity-50"
                    >
                        <span v-if="loading">Gönderiliyor...</span>
                        <span v-else>Siparişi Tamamla</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <div v-if="showSuccess" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-8 max-w-sm w-full text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Sipariş Alındı!</h3>
                <p class="text-gray-600 mb-6">Siparişiniz mutfağa iletildi.</p>
                <button @click="resetOrder" class="w-full bg-gray-900 text-white py-2 rounded-lg">Yeni Sipariş</button>
            </div>
        </div>
    </div>

    <script>
        const { createApp, ref, computed } = Vue;

        createApp({
            setup() {
                // Safe Data Parsing
                const getJsonData = (id) => {
                    try {
                        const el = document.getElementById(id);
                        return el && el.textContent ? JSON.parse(el.textContent) : null;
                    } catch (e) {
                        console.error('JSON Parse Error for ' + id, e);
                        return {};
                    }
                };

                const products = getJsonData('data-products') || {};
                const table = getJsonData('data-table');
                
                const configEl = document.getElementById('app-config');
                const mode = configEl ? configEl.getAttribute('data-mode') : 'table';
                const sessionType = configEl ? configEl.getAttribute('data-session-type') : 'table';
                const tableName = table ? table.label : '';

                const cart = ref([]);
                const loading = ref(false);
                const showSuccess = ref(false);

                // Modal State
                const selectedProduct = ref(null);
                const selectedOptions = ref({}); // { groupId: optionId or [optionIds] }
                const productNote = ref('');

                const openProductModal = (product) => {
                    selectedProduct.value = product;
                    productNote.value = '';
                    selectedOptions.value = {};
                    
                    // Initialize options for this product
                    if (product.option_groups) {
                        product.option_groups.forEach(group => {
                            if (group.type === 'single') {
                                // Select first if required? Or null. Let's keep null.
                                selectedOptions.value[group.id] = null;
                            } else {
                                selectedOptions.value[group.id] = [];
                            }
                        });
                    }
                };

                const closeModal = () => {
                    selectedProduct.value = null;
                    selectedOptions.value = {};
                    productNote.value = '';
                };

                const calculateModalTotal = () => {
                    if (!selectedProduct.value) return 0;
                    let total = parseFloat(selectedProduct.value.price);
                    
                    if (selectedProduct.value.option_groups) {
                        selectedProduct.value.option_groups.forEach(group => {
                            const selection = selectedOptions.value[group.id];
                            if (!selection) return;

                            if (Array.isArray(selection)) {
                                // Checkbox
                                selection.forEach(optId => {
                                    const opt = group.options.find(o => o.id === optId);
                                    if (opt) total += parseFloat(opt.price_adjustment);
                                });
                            } else {
                                // Radio
                                const opt = group.options.find(o => o.id === selection);
                                if (opt) total += parseFloat(opt.price_adjustment);
                            }
                        });
                    }
                    return total;
                };

                const confirmAddToCart = () => {
                    if (!selectedProduct.value) return;

                    // Validation for required groups could go here
                    // For now, assuming user is smart or we add validation later

                    // Flatten selected options to IDs array
                    let finalOptions = [];
                    let optionsDisplay = [];

                    if (selectedProduct.value.option_groups) {
                        selectedProduct.value.option_groups.forEach(group => {
                            const selection = selectedOptions.value[group.id];
                            if (selection) {
                                if (Array.isArray(selection)) {
                                    selection.forEach(optId => {
                                        finalOptions.push(optId);
                                        const opt = group.options.find(o => o.id === optId);
                                        if (opt) optionsDisplay.push(opt.name);
                                    });
                                } else {
                                    finalOptions.push(selection);
                                    const opt = group.options.find(o => o.id === selection);
                                    if (opt) optionsDisplay.push(opt.name);
                                }
                            }
                        });
                    }

                    cart.value.push({
                        id: selectedProduct.value.id,
                        name: selectedProduct.value.name,
                        price: parseFloat(selectedProduct.value.price),
                        quantity: 1,
                        options: finalOptions,
                        optionsDisplay: optionsDisplay,
                        note: productNote.value,
                        totalPrice: calculateModalTotal()
                    });

                    closeModal();
                };

                const removeFromCart = (index) => {
                    cart.value.splice(index, 1);
                };

                const cartTotal = computed(() => {
                    return cart.value.reduce((sum, item) => sum + item.totalPrice, 0);
                });

                const cartItemCount = computed(() => {
                    return cart.value.reduce((sum, item) => sum + item.quantity, 0);
                });

                const formatPrice = (value) => {
                    return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value);
                };

                const scrollToCategory = (cat) => {
                    const el = document.getElementById('cat-' + cat);
                    if (el) el.scrollIntoView({ behavior: 'smooth' });
                };

                const submitOrder = async () => {
                    if (cart.value.length === 0) return;
                    loading.value = true;

                    try {
                        await axios.post('{{ route("menu.order.store") }}', {
                            items: cart.value.map(i => ({ 
                                id: i.id, 
                                quantity: i.quantity,
                                options: i.options,
                                note: i.note
                            })),
                            table_id: table ? table.id : null,
                            session_type: sessionType
                        });
                        
                        showSuccess.value = true;
                        cart.value = [];
                    } catch (error) {
                        alert('Hata: ' + (error.response?.data?.message || error.message));
                    } finally {
                        loading.value = false;
                    }
                };

                const resetOrder = () => {
                    showSuccess.value = false;
                };

                return {
                    products,
                    tableName,
                    mode,
                    cart,
                    addToCart: openProductModal, // Direct add triggers modal now
                    openProductModal,
                    closeModal,
                    selectedProduct,
                    selectedOptions,
                    productNote,
                    calculateModalTotal,
                    confirmAddToCart,
                    removeFromCart,
                    cartTotal,
                    cartItemCount,
                    formatPrice,
                    scrollToCategory,
                    submitOrder,
                    loading,
                    showSuccess,
                    resetOrder
                };
            }
        }).mount('#app');
    </script>
    <style>
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</body>
</html>
