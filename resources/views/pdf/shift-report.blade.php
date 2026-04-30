<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport de Performance - Odysse Energie SA</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 4px solid #1a3c5e; padding-bottom: 10px; }
        .header h1 { color: #1a3c5e; margin: 0; font-size: 22px; text-transform: uppercase; }
        .header .subtitle { color: #7f8c8d; font-size: 13px; margin-top: 5px; }
        
        /* Couleurs Entreprise */
        .bg-primary { background-color: #1a3c5e; color: white; }
        .section-title { background-color: #1a3c5e; color: #f39c12; padding: 8px 12px; margin: 20px 0 10px; border-radius: 4px; font-weight: bold; font-size: 14px; border-left: 5px solid #f39c12; }
        
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th { background-color: #f8f9fa; color: #1a3c5e; padding: 10px; text-align: left; border: 1px solid #dee2e6; border-bottom: 2px solid #f39c12; }
        .table td { padding: 8px; border: 1px solid #dee2e6; }
        .table-striped tbody tr:nth-child(odd) { background-color: #fdfdfd; }
        
        .stats-grid { display: table; width: 100%; border-spacing: 10px; margin: 0 -10px; }
        .stat-card { display: table-cell; border: 1px solid #dee2e6; border-top: 3px solid #f39c12; border-radius: 4px; padding: 10px; text-align: center; width: 25%; background: #fff; }
        .stat-value { font-size: 16px; font-weight: bold; color: #1a3c5e; margin-top: 5px; }
        
        .text-success { color: #27ae60; font-weight: bold; }
        .text-danger { color: #e74c3c; font-weight: bold; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Odysse Energie SA</h1>
        <div class="subtitle">RAPPORT DE PERFORMANCE GLOBALE</div>
        <div style="margin-top:10px;">
            <strong>Période :</strong> {{ $periode ?? 'Non spécifiée' }} | 
            <strong>Généré le :</strong> {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <div class="section-title">Indicateurs Clés</div>
    <div class="stats-grid">
        <div class="stat-card">
            <div style="color: #7f8c8d;">Volume Total</div>
            <div class="stat-value">{{ number_format($stats['totalLitres'] ?? 0, 2, ',', ' ') }} L</div>
        </div>
        <div class="stat-card">
            <div style="color: #7f8c8d;">Chiffre d'Affaires</div>
            <div class="stat-value">{{ number_format($stats['totalVentes'] ?? 0, 0, ',', ' ') }} F CFA</div>
        </div>
        <div class="stat-card">
            <div style="color: #7f8c8d;">Dépenses</div>
            <div class="stat-value text-danger">{{ number_format($stats['totalDepenses'] ?? 0, 0, ',', ' ') }} F CFA</div>
        </div>
        <div class="stat-card">
            <div style="color: #7f8c8d;">Écart Global</div>
            <div class="stat-value">
                @php $ecartFinal = $stats['totalEcartFinal'] ?? 0; @endphp
                <span class="{{ $ecartFinal >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $ecartFinal >= 0 ? '+' : '' }}{{ number_format($ecartFinal, 0, ',', ' ') }} F CFA
                </span>
            </div>
        </div>
    </div>

    <div class="section-title">Détails des Shifts</div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Date / Shift</th>
                <th>Pompiste</th>
                <th class="text-right">Ventes</th>
                <th class="text-right">Versement</th>
                <th class="text-right">Dépenses</th>
                <th class="text-right">Écart</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shifts as $shift)
            <tr>
                <td>{{ $shift->date_shift->format('d/m/Y') }} - {{ $shift->shift }}</td>
                <td>{{ $shift->responsable }}</td>
                <td class="text-right">{{ number_format($shift->total_ventes, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($shift->versement, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($shift->total_depenses, 0, ',', ' ') }}</td>
                <td class="text-right">
                    @php $ecart = $shift->versement - ($shift->total_ventes - $shift->total_depenses); @endphp
                    <span class="{{ $ecart >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($ecart, 0, ',', ' ') }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: center; color: #95a5a6; border-top: 1px solid #eee; padding-top: 10px;">
        Odysse Energie SA - Excellence et Performance Énergétique
    </div>
</body>
</html>