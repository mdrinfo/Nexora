@extends('layouts.admin')

@section('content')
    <div class="grid">
        <div class="card">
            <h3>Sessions ouvertes</h3>
            <div class="value">{{ number_format($openSessions) }}</div>
        </div>
        <div class="card">
            <h3>Commandes aujourd'hui</h3>
            <div class="value">{{ number_format($ordersToday) }}</div>
        </div>
        <div class="card">
            <h3>Revenus aujourd'hui</h3>
            <div class="value">{{ number_format($revenueToday, 2, ',', ' ') }} €</div>
        </div>
        <div class="card">
            <h3>Articles en faible stock</h3>
            <div class="value">{{ number_format($lowStockCount) }}</div>
        </div>
    </div>

    <div class="section-title">Réservations à venir</div>
    <div class="card">
        <div class="value">{{ number_format($upcomingReservations) }}</div>
    </div>

    <div class="section-title">Dernières commandes</div>
    <div class="card">
        <table>
            <thead>
            <tr>
                <th>Heure</th>
                <th>Table</th>
                <th>Statut</th>
                <th>Total</th>
                <th>Articles</th>
            </tr>
            </thead>
            <tbody>
            @forelse($recentOrders as $order)
                <tr>
                    <td>{{ $order->created_at->format('H:i') }}</td>
                    <td>{{ optional($order->session->diningTable)->label }}</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td>{{ number_format($order->grand_total, 2, ',', ' ') }} €</td>
                    <td>
                        @foreach($order->items as $item)
                            {{ $item->product->name }} × {{ $item->quantity }}@if(!$loop->last), @endif
                        @endforeach
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Aucune commande récente.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection

