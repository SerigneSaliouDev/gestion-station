<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport de Réconciliation</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #2c3e50; margin: 0; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th { background: #3498db; color: white; padding: 8px; text-align: left; }
        .table td { padding: 6px; border-bottom: 1px solid #ddd; }
        .table tr:nth-child(even) { background: #f9f9f9; }
        .totals { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .badge { padding: 3px 8px; border-radius: 3px; font-size: 10px; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .footer { margin-top: 40px; text-align: center; color: #7f8c8d; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RAPPORT DE RÉCONCILIATION</h1>
        @if($station)
            <p>Station: {{ $station->nom }} ({{ $station->code }})</p>
        @endif
        @if($fuelType)
            <p>Carburant: {{ ucfirst($fuelType) }}</p>
        @endif
        <p>Période: {{ $startDate->format('d/m/Y') }} au {{ $endDate->format('d/m/Y') }}</p>
    </div>
    
    <div class="totals">
        <h3>Récapitulatif des Mouvements</h3>
        <p><strong>Réceptions:</strong> {{ number_format($totals['receptions'], 0, ',', ' ') }} L</p>
        <p><strong>Ventes:</strong> {{ number_format($totals['sales'], 0, ',', ' ') }} L</p>
        <p><strong>Ajustements:</strong> {{ number_format($totals['adjustments'], 0, ',', ' ') }} L</p>
        <p><strong>Montant total:</strong> {{ number_format($totals['amount'], 0, ',', ' ') }} FCFA</p>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Carburant</th>
                <th>Quantité (L)</th>
                <th>Prix unitaire</th>
                <th>Montant</th>
                <th>Stock Avant</th>
                <th>Stock Après</th>
                <th>Enregistré par</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movements as $movement)
            <tr>
                <td>{{ $movement->movement_date->format('d/m/Y') }}</td>
                <td>
                    @if($movement->movement_type == 'reception')
                        <span class="badge badge-success">Réception</span>
                    @elseif($movement->movement_type == 'vente')
                        <span class="badge badge-danger">Vente</span>
                    @else
                        <span class="badge badge-warning">Ajustement</span>
                    @endif
                </td>
                <td>{{ ucfirst($movement->fuel_type) }}</td>
                <td>{{ number_format($movement->quantity, 0, ',', ' ') }}</td>
                <td>{{ number_format($movement->unit_price, 0, ',', ' ') }}</td>
                <td>{{ number_format($movement->total_amount, 0, ',', ' ') }}</td>
                <td>{{ number_format($movement->stock_before, 0, ',', ' ') }}</td>
                <td>{{ number_format($movement->stock_after, 0, ',', ' ') }}</td>
                <td>{{ $movement->recorder->name ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        Généré le {{ $generatedAt->format('d/m/Y à H:i') }} par {{ $generatedBy }}
    </div>
</body>
</html>