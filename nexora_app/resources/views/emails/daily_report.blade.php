<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 15px; text-align: center; border-bottom: 1px solid #ddd; }
        .content { padding: 20px 0; }
        .footer { text-align: center; font-size: 12px; color: #777; margin-top: 20px; }
        .stat-box { background: #f1f5f9; padding: 15px; border-radius: 8px; margin-bottom: 10px; }
        .label { font-size: 14px; color: #555; }
        .value { font-size: 24px; font-weight: bold; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #eee; }
        th { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Rapport Journalier Nexora</h2>
            <p>{{ $data['date'] }}</p>
        </div>
        
        <div class="content">
            <div class="stat-box">
                <div class="label">Revenus Totaux</div>
                <div class="value">{{ number_format($data['totalRevenue'], 2, ',', ' ') }} €</div>
            </div>
            
            <div class="stat-box">
                <div class="label">Revenus en Espèces ({{ $data['cashOrdersCount'] }} commandes)</div>
                <div class="value">{{ number_format($data['cashRevenue'], 2, ',', ' ') }} €</div>
            </div>

            <h3>Articles les plus vendus</h3>
            <table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Qté</th>
                        <th>Ventes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['topItems'] as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->total_qty }}</td>
                        <td>{{ number_format($item->total_sales, 2) }} €</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <h3>Performance Serveurs</h3>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Commandes</th>
                        <th>Ventes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['waiterPerformance'] as $wp)
                    <tr>
                        <td>{{ $wp->user->name }}</td>
                        <td>{{ $wp->order_count }}</td>
                        <td>{{ number_format($wp->total_sales, 2) }} €</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>Ce rapport a été généré automatiquement par Nexora.</p>
            <p>Une copie CSV détaillée est jointe à ce mail.</p>
        </div>
    </div>
</body>
</html>
