<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 11px; color: #333; padding: 20px; }
        .header { text-align: center; border-bottom: 5px solid #1a3c5e; padding-bottom: 15px; margin-bottom: 25px; }
        .header h1 { color: #1a3c5e; margin: 0; font-size: 22px; }
        .brand-orange { color: #f39c12; font-weight: bold; }
        
        .stat-banner { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 20px; }
        .stat-item { display: inline-block; width: 24%; text-align: center; border-right: 1px solid #ddd; }
        .stat-item:last-child { border-right: none; }
        .stat-label { font-size: 9px; color: #7f8c8d; text-transform: uppercase; }
        .stat-value { font-size: 15px; font-weight: bold; color: #1a3c5e; }

        .table { width: 100%; border-collapse: collapse; }
        .table th { background: #1a3c5e; color: #fff; padding: 12px 8px; text-align: left; }
        .table td { padding: 10px 8px; border-bottom: 1px solid #eee; }
        .rank-badge { width: 22px; height: 22px; line-height: 22px; display: inline-block; border-radius: 50%; color: white; font-weight: bold; text-align: center; }
        .rank-1 { background: #f39c12; box-shadow: 0 2px 4px rgba(0,0,0,0.2); } /* Or Odysse */
        .rank-2 { background: #95a5a6; }
        .rank-3 { background: #a0522d; }
        .rank-other { background: #1a3c5e; opacity: 0.7; }

        .text-success { color: #27ae60; }
        .text-danger { color: #e74c3c; }
        .footer { margin-top: 30px; text-align: center; font-size: 9px; color: #7f8c8d; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ODYSSE ENERGIE <span class="brand-orange">SA</span></h1>
        <div style="color: #7f8c8d; margin-top: 5px;">CLASSEMENT ET PERFORMANCE DES POMPISTES</div>
    </div>

    <div class="stat-banner">
        <div class="stat-item">
            <div class="stat-label">Ventes Globales</div>
            <div class="stat-value">{{ number_format($totalSales, 0, ',', ' ') }} F</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Volume Total</div>
            <div class="stat-value">{{ number_format($totalLitres, 0, ',', ' ') }} L</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Pompistes Actifs</div>
            <div class="stat-value">{{ count($salesByPump) }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Période</div>
            <div class="stat-value" style="font-size: 11px;">{{ $startDate->format('d/m/y') }} - {{ $endDate->format('d/m/y') }}</div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th width="40">Rang</th>
                <th>Nom du Gerant</th>
                <th style="text-align:right">Shifts</th>
                <th style="text-align:right">CA Réalisé</th>
                <th style="text-align:right">Volume (L)</th>
                <th style="text-align:right">Écart Moyen</th>
                <th style="text-align:right">Part CA</th>
            </tr>
        </thead>
        <tbody>
            @php $rank = 1; @endphp
            @foreach($salesByPump as $userId => $data)
            <tr>
                <td>
                    <span class="rank-badge {{ $rank <= 3 ? 'rank-'.$rank : 'rank-other' }}">
                        {{ $rank }}
                    </span>
                </td>
                <td><strong>{{ $data['user']->name ?? 'Utilisateur' }}</strong></td>
                <td align="right">{{ $data['shift_count'] }}</td>
                <td align="right" style="color:#1a3c5e; font-weight:bold;">{{ number_format($data['total_sales'], 0, ',', ' ') }} F</td>
                <td align="right">{{ number_format($data['total_litres'], 0, ',', ' ') }} L</td>
                <td align="right">
                    <span class="{{ $data['average_ecart'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($data['average_ecart'], 0, ',', ' ') }} F
                    </span>
                </td>
                <td align="right">
                    {{ number_format(($data['total_sales'] / $totalSales) * 100, 1) }}%
                </td>
            </tr>
            @php $rank++; @endphp
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Document confidentiel - Odysse Energie SA - Système de Gestion Intégré<br>
        Généré le {{ now()->format('d/m/Y à H:i') }}
    </div>
</body>
</html>