@extends('layouts.admin')

@section('content')
    @php $user = auth()->user(); @endphp
    
    <!-- Operational Dashboard -->
    @if(!$user || ($user && ($user->hasRoleKey('owner') || $user->hasRoleKey('manager'))))
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm">
                <h3 class="text-sm text-gray-600 dark:text-gray-400">Sessions ouvertes</h3>
                <div class="text-2xl font-bold mt-2 text-gray-900 dark:text-white">{{ number_format($openSessions) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm">
                <h3 class="text-sm text-gray-600 dark:text-gray-400">Commandes aujourd'hui</h3>
                <div class="text-2xl font-bold mt-2 text-gray-900 dark:text-white">{{ number_format($ordersToday) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm">
                <h3 class="text-sm text-gray-600 dark:text-gray-400">Revenus aujourd'hui</h3>
                <div class="text-2xl font-bold mt-2 text-green-600">{{ number_format($revenueStats['daily'] ?? 0, 2, ',', ' ') }} €</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm">
                <h3 class="text-sm text-gray-600 dark:text-gray-400">Audit Requis (48h)</h3>
                <div class="flex justify-between items-center">
                    <div class="text-2xl font-bold mt-2 text-gray-900 dark:text-white">{{ number_format($itemsToAuditCount ?? 0) }}</div>
                    @if(isset($itemsToAuditCount) && $itemsToAuditCount > 0)
                        <a href="{{ route('admin.inventory.audit') }}" class="text-xs text-blue-600 hover:underline bg-blue-50 dark:bg-blue-900 dark:text-blue-200 px-2 py-1 rounded">Vérifier</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6 items-start">
                <div class="bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg p-5 shadow-sm">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-4">Graphique Revenus</h3>
                    <div style="height: 250px; position: relative; overflow: hidden;">
                        <canvas id="chartRevenue"
                            data-daily="{{ $revenueStats['daily'] ?? 0 }}"
                            data-weekly="{{ $revenueStats['weekly'] ?? 0 }}"
                            data-monthly="{{ $revenueStats['monthly'] ?? 0 }}"></canvas>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg p-5 shadow-sm">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-4">Heures de Pointe</h3>
                    <div style="height: 250px; position: relative; overflow: hidden;">
                        <canvas id="chartPeakHours"></canvas>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg p-5 shadow-sm">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-4">Répartition (Aujourd'hui)</h3>
                    <div class="flex flex-col">
                        <div class="flex-1 relative" style="height: 200px; min-height: 200px; overflow: hidden;">
                            <canvas id="chartOrderTypes"></canvas>
                        </div>
                        <div class="mt-4 space-y-2">
                            @foreach(['table', 'online', 'takeaway'] as $type)
                                @php $stat = $orderTypeStats[$type] ?? null; @endphp
                                <div class="flex justify-between items-center text-sm border-b border-gray-100 dark:border-gray-700 pb-1 last:border-0">
                                    <span class="capitalize text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full {{ $type=='online'?'bg-blue-600':($type=='takeaway'?'bg-orange-600':'bg-gray-700') }}"></span>
                                        @if($type=='online') En ligne
                                        @elseif($type=='takeaway') À emporter
                                        @else Sur place
                                        @endif
                                    </span>
                                    <div class="text-right">
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $stat ? $stat->count : 0 }}</span>
                                        <span class="text-xs text-gray-500 ml-1">({{ number_format($stat ? $stat->total : 0, 0) }} €)</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div id="dashboard-data" 
                 data-order-types='@json($orderTypeStats)' 
                 data-daily="{{ $revenueStats['daily'] ?? 0 }}"
                 data-weekly="{{ $revenueStats['weekly'] ?? 0 }}"
                 data-monthly="{{ $revenueStats['monthly'] ?? 0 }}"
                 style="display: none;"></div>
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
            <script id="peak-data" type="application/json">@json($peakHours->map(function($p){ return ['hour' => $p->hour, 'count' => $p->count]; }))</script>
            <script>
                // Data
                var dashboardData = document.getElementById('dashboard-data');
                var revenueDaily = Number(dashboardData.getAttribute('data-daily') || 0);
                var revenueWeekly = Number(dashboardData.getAttribute('data-weekly') || 0);
                var revenueMonthly = Number(dashboardData.getAttribute('data-monthly') || 0);

                // Revenue Chart
                var ctxR = document.getElementById('chartRevenue').getContext('2d');
                new Chart(ctxR, {
                    type: 'bar',
                    data: {
                        labels: ['Jour', 'Semaine', 'Mois'],
                        datasets: [{ label: '€', data: [revenueDaily, revenueWeekly, revenueMonthly], backgroundColor: ['#10b981','#3b82f6','#f59e0b'] }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } } 
                    }
                });

                // Peak Hours Chart
                var peak = JSON.parse(document.getElementById('peak-data').textContent);
                var ctxP = document.getElementById('chartPeakHours').getContext('2d');
                new Chart(ctxP, {
                    type: 'line',
                    data: {
                        labels: peak.map(function(p){ return p.hour; }),
                        datasets: [{ label: 'Commandes', data: peak.map(function(p){ return p.count; }), borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.2)' }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } } 
                    }
                });

                // Order Types Chart
                var orderTypes = JSON.parse(document.getElementById('dashboard-data').getAttribute('data-order-types'));
                var ctxType = document.getElementById('chartOrderTypes').getContext('2d');
                new Chart(ctxType, {
                    type: 'doughnut',
                    data: {
                        labels: ['Masa', 'Online', 'Paket'],
                        datasets: [{
                            data: [
                                (orderTypes.table?.count || 0),
                                (orderTypes.online?.count || 0),
                                (orderTypes.takeaway?.count || 0)
                            ],
                            backgroundColor: ['#374151', '#2563eb', '#ea580c'],
                            borderWidth: 0
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        cutout: '70%'
                    }
                });
            </script>
    @endif

    <!-- Owner Analytics Section -->
    @if(!$user || ($user && $user->hasRoleKey('owner')))
        <div class="mb-8">
            <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Analyses & Performance
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Revenue Restaurant -->
                <div class="bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg p-5 shadow-sm">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        Revenus Restaurant
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-end">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">Aujourd'hui</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ number_format($revenueStats['daily'], 2, ',', ' ') }} €</span>
                        </div>
                        <div class="flex justify-between items-end border-t border-dashed dark:border-gray-700 pt-2">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">Cette Semaine</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ number_format($revenueStats['weekly'], 2, ',', ' ') }} €</span>
                        </div>
                        <div class="flex justify-between items-end border-t border-dashed dark:border-gray-700 pt-2">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">Ce Mois</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ number_format($revenueStats['monthly'], 2, ',', ' ') }} €</span>
                        </div>
                    </div>
                </div>

                <!-- Banquet -->
                <div class="bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg p-5 shadow-sm">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                        Banquet
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-end">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">Aujourd'hui</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ number_format($banquetStats['daily'], 2, ',', ' ') }} €</span>
                        </div>
                        <div class="flex justify-between items-end border-t border-dashed dark:border-gray-700 pt-2">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">Cette Semaine</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ number_format($banquetStats['weekly'], 2, ',', ' ') }} €</span>
                        </div>
                        <div class="flex justify-between items-end border-t border-dashed dark:border-gray-700 pt-2">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">Ce Mois</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ number_format($banquetStats['monthly'], 2, ',', ' ') }} €</span>
                        </div>
                    </div>
                </div>

                <!-- Waste -->
                <div class="bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg p-5 shadow-sm">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        Pertes Inventaire
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-end">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">Aujourd'hui</span>
                            <span class="font-bold text-red-600 dark:text-red-400">{{ number_format($wasteStats['daily'], 2, ',', ' ') }} €</span>
                        </div>
                        <div class="flex justify-between items-end border-t border-dashed dark:border-gray-700 pt-2">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">Ce Mois</span>
                            <span class="font-bold text-red-600 dark:text-red-400">{{ number_format($wasteStats['monthly'], 2, ',', ' ') }} €</span>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t dark:border-gray-700 text-xs text-gray-400 text-center">
                        Basé sur les déclarations de perte
                    </div>
                </div>
            </div>

            <!-- Peak Hours -->
            @if(count($peakHours) > 0)
            <div class="mt-6 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg p-5 shadow-sm">
                <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-6">Heures de Pointe (30 derniers jours)</h3>
                <div class="flex items-end space-x-2 md:space-x-8 h-40 pb-2">
                    @php
                        $maxCount = $peakHours->max('count') ?: 1;
                    @endphp
                    @foreach($peakHours as $ph)
                        @php
                            $heightVal = ($ph->count / $maxCount) * 100;
                        @endphp
                        <div class="flex flex-col items-center flex-1 group">
                            <div class="w-full max-w-[60px] bg-blue-100 dark:bg-blue-900/50 rounded-t-lg relative h-full flex items-end overflow-hidden">
                                <div class="w-full bg-blue-500 rounded-t-lg transition-all duration-500 group-hover:bg-blue-600" 
                                     style="height: <?php echo $heightVal; ?>%">
                                     <div class="text-white text-xs text-center pt-1 opacity-0 group-hover:opacity-100">{{ $ph->count }}</div>
                                </div>
                            </div>
                            <span class="text-sm mt-2 font-bold text-gray-700 dark:text-gray-300">{{ $ph->hour }}h</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Detailed Analysis -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <!-- Top Selling Products -->
                <div class="bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg p-5 shadow-sm">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                        <span class="text-xl">🏆</span> Produits Top (Mois)
                    </h3>
                    <div class="space-y-3">
                        @forelse($topSellingProducts as $productItem)
                            <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-700 pb-2 last:border-0">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-xs font-bold text-gray-500 dark:text-gray-300">
                                        {{ substr($productItem->product->name ?? '?', 0, 2) }}
                                    </div>
                                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $productItem->product->name ?? 'Inconnu' }}</span>
                                </div>
                                <span class="text-sm font-bold text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ $productItem->total_sold }}</span>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500 dark:text-gray-400 italic">Aucune donnée disponible.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Waiter Performance -->
                <div class="bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg p-5 shadow-sm">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                        <span class="text-xl">🤵</span> Performance Serveurs (Mois)
                    </h3>
                    <div class="space-y-3">
                        @forelse($waiterPerformance as $waiter)
                            <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-700 pb-2 last:border-0">
                                <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $waiter->name }}</span>
                                <div class="text-xs text-right">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $waiter->orders_taken_count }} <span class="text-gray-500 dark:text-gray-400 font-normal">com.</span></div>
                                    <div class="text-green-600 dark:text-green-400 font-semibold">{{ $waiter->items_served_count }} <span class="text-gray-500 dark:text-gray-400 font-normal">servis</span></div>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500 dark:text-gray-400 italic">Aucune donnée disponible.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Table Occupancy -->
                <div class="bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg p-5 shadow-sm">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                        <span class="text-xl">⏱️</span> Occupation Moyenne (Mois)
                    </h3>
                    <div class="flex flex-col items-center justify-center py-6">
                        <div class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-2">{{ round($avgOccupancy) }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wide font-semibold">Minutes / Session</div>
                        <p class="text-xs text-center text-gray-400 dark:text-gray-500 mt-4 max-w-[200px]">
                            Temps moyen qu'une table reste occupée avant le paiement.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Lists Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @if(!$user || ($user && ($user->hasRoleKey('owner') || $user->hasRoleKey('manager') || $user->hasRoleKey('chef'))))
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-700 font-semibold text-gray-700 dark:text-gray-200">Dernières commandes</div>
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-2 text-left">Heure</th>
                        <th class="px-4 py-2 text-left">Table</th>
                        <th class="px-4 py-2 text-left">Articles</th>
                        <th class="px-4 py-2 text-left">Total</th>
                        <th class="px-4 py-2 text-left">État</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $order->created_at->format('H:i') }}</td>
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ optional($order->session->diningTable)->label }}</td>
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                                @foreach($order->items as $item)
                                    <div class="mb-1 last:mb-0">
                                        {{ $item->quantity }}x {{ $item->product->name }}
                                        @if($item->options->isNotEmpty())
                                            <span class="text-xs text-gray-500 block ml-4">
                                                + {{ $item->options->pluck('product_option_name')->join(', ') }}
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </td>
                            <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ number_format($order->grand_total, 2, ',', ' ') }} €</td>
                            <td class="px-4 py-2 text-xs">
                                <span class="px-2 py-1 rounded-full {{ $order->status == 'served' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-4 text-center text-gray-500 dark:text-gray-400" colspan="5">Aucune commande récente.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        @if(!$user || ($user && ($user->hasRoleKey('owner') || $user->hasRoleKey('manager'))))
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-700 font-semibold text-gray-700 dark:text-gray-200">Réservations à venir</div>
                <div class="p-4 flex items-center justify-center h-full">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-2">{{ number_format($upcomingReservations) }}</div>
                        <div class="text-gray-500 dark:text-gray-400 text-sm">Réservations confirmées ou en attente</div>
                    </div>
                </div>
            </div>
        @endif
        
        @if(!$user || ($user && ($user->hasRoleKey('owner') || $user->hasRoleKey('manager'))))
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden mt-6">
                <div class="px-4 py-3 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-700 font-semibold text-gray-700 dark:text-gray-200">Vue en direct des tables</div>
                <div class="p-4 space-y-6">
                    @foreach($floors as $floor)
                        <div>
                            <h4 class="text-lg font-bold text-gray-700 dark:text-gray-300 mb-3 border-b dark:border-gray-600 pb-2">{{ $floor->name }}</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                                @foreach($floor->tables as $t)
                                    @php $occupied = ($t->sessions->where('status','open')->count() > 0); @endphp
                                    <div class="px-4 py-6 rounded-lg text-center text-white {{ $occupied ? 'bg-red-600 dark:bg-red-700' : 'bg-green-600 dark:bg-green-700' }}">
                                        <div class="text-xl font-bold">{{ $t->label }}</div>
                                        <div class="text-sm mt-1">{{ $occupied ? 'Occupée' : 'Libre' }}</div>
                                        <div class="text-xs mt-1">Capacité {{ $t->capacity }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    @if($tablesWithoutFloor->count() > 0)
                        <div>
                            <h4 class="text-lg font-bold text-gray-700 dark:text-gray-300 mb-3 border-b dark:border-gray-600 pb-2">Autres Tables</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                                @foreach($tablesWithoutFloor as $t)
                                    @php $occupied = ($t->sessions->where('status','open')->count() > 0); @endphp
                                    <div class="px-4 py-6 rounded-lg text-center text-white {{ $occupied ? 'bg-red-600 dark:bg-red-700' : 'bg-green-600 dark:bg-green-700' }}">
                                        <div class="text-xl font-bold">{{ $t->label }}</div>
                                        <div class="text-sm mt-1">{{ $occupied ? 'Occupée' : 'Libre' }}</div>
                                        <div class="text-xs mt-1">Capacité {{ $t->capacity }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if(!$user || ($user && ($user->hasRoleKey('owner') || $user->hasRoleKey('manager') || $user->hasRoleKey('chef'))))
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden mt-6">
                <div class="px-4 py-3 border-b dark:border-gray-700 bg-yellow-50 dark:bg-yellow-900/20 font-semibold text-gray-700 dark:text-gray-200">Commandes en préparation 🍳</div>
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-2 text-left">Heure</th>
                        <th class="px-4 py-2 text-left">Table</th>
                        <th class="px-4 py-2 text-left">Articles</th>
                        <th class="px-4 py-2 text-left">État</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($ordersPreparing as $order)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $order->created_at->format('H:i') }}</td>
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ optional($order->session->diningTable)->label }}</td>
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                                @foreach($order->items as $item)
                                    <div class="mb-1 last:mb-0">
                                        {{ $item->quantity }}x {{ $item->product->name }}
                                        @if($item->options->isNotEmpty())
                                            <span class="text-xs text-gray-500 block ml-4">
                                                + {{ $item->options->pluck('product_option_name')->join(', ') }}
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </td>
                            <td class="px-4 py-2 text-xs">
                                <span class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-4 text-center text-gray-500 dark:text-gray-400" colspan="4">Aucune commande en préparation.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
