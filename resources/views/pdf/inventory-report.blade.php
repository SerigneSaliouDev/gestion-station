<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inventaire Physique - Odysse Energie SA</title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 11px; color: #333; padding: 15px; }
        .header { text-align: center; margin-bottom: 25px; }
        .company-name { color: #1a3c5e; font-size: 22px; font-weight: bold; }
        .brand-orange { color: #f39c12; }
        
        /* Stats Grid */
        .stats-container { width: 100%; margin-bottom: 20px; }
        .stat-card { display: inline-block; width: 23%; background: #fff; border: 1px solid #dee2e6; border-top: 3px solid #f39c12; padding: 10px; text-align: center; border-radius: 4px; }
        
        .stat-label { font-size: 9px; color: #7f8c8d; text-transform: uppercase; margin-bottom: 5px; }
        .stat-value { font-size: 16px; font-weight: bold; color: #1a3c5e; }

        .table { width: 100%; border-collapse: collapse; }
        .table th { background: #1a3c5e; color: white; padding: 10px 8px; text-align: left; font-size: 10px; }
        .table td { padding: 8px; border-bottom: 1px solid #eee; }
        
        .text-right { text-align: right; }
        .text-success { color: #27ae60; font-weight: bold; }
        .text-danger { color: #e74c3c; font-weight: bold; }
        .text-warning { color: #f39c12; font-weight: bold; }
        
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }

        .footer { margin-top: 40px; text-align: center; color: #7f8c8d; font-size: 9px; border-top: 1px solid #eee; padding-top: 10px; }
        .per-mille { font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">ODYSSE ENERGIE <span class="brand-orange">SA</span></div>
        <div style="font-size: 14px; color: #7f8c8d; font-weight: bold; margin-top: 5px;">RAPPORT D'INVENTAIRE PHYSIQUE (JAUGEAGE)</div>
        <div style="margin-top: 5px;">Période du {{ $startDate->format('d/m/Y') }} au {{ $endDate->format('d/m/Y') }}</div>
    </div>
    
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-label">Mesures</div>
            <div class="stat-value">{{ $stats['total_measurements'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Écart Moyen</div>
            <div class="stat-value">
                @php
                    $avgPerMille = $stats['average_difference_per_mille'] ?? $tankLevels->avg('difference_percentage');
                    $class = abs($avgPerMille) > 5 ? 'text-danger' : (abs($avgPerMille) > 2 ? 'text-warning' : 'text-success');
                @endphp
                <span class="{{ $class }}">{{ number_format($avgPerMille, 1) }}<span class="per-mille">‰</span></span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Anomalies (>5‰)</div>
            <div class="stat-value text-danger">{{ $stats['discrepancies'] ?? $tankLevels->where('is_acceptable', false)->count() }}</div>
        </div>
     
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>Date / Heure</th>
                <th>Cuve</th>
                <th>Carburant</th>
                <th class="text-right">Stock Théorique</th>
                <th class="text-right">Stock Physique</th>
                <th class="text-right">Écart (L)</th>
                <th class="text-right">Écart (‰)</th>
                <th>Jaugeur</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tankLevels as $tank)
            @php
                // Calcul des valeurs en pour mille
                $diffLiters = $tank->difference ?? ($tank->physical_stock - $tank->theoretical_stock);
                $diffPerMille = $tank->difference_percentage ?? 
                              ($tank->theoretical_stock > 0 ? ($diffLiters / $tank->theoretical_stock) * 1000 : 0);
                
                // Déterminer la classe CSS selon l'écart en ‰
                if (abs($diffPerMille) > 10) {
                    $badgeClass = 'badge-danger';
                    $textClass = 'text-danger';
                } elseif (abs($diffPerMille) > 5) {
                    $badgeClass = 'badge-warning';
                    $textClass = 'text-warning';
                } else {
                    $badgeClass = 'badge-success';
                    $textClass = 'text-success';
                }
                
                // Déterminer le statut
                if ($tank->is_acceptable !== null) {
                    $status = $tank->is_acceptable ? '✅ Conforme' : '⚠ Non conforme';
                } else {
                    $status = abs($diffPerMille) <= 5 ? '✅ Conforme' : '⚠ Non conforme';
                }
            @endphp
            <tr>
                <td>{{ $tank->measurement_date->format('d/m/Y H:i') }}</td>
                <td><strong>Cuve #{{ $tank->tank_number }}</strong></td>
                <td>{{ ucfirst($tank->fuel_type) }}</td>
                <td class="text-right">{{ number_format($tank->theoretical_stock, 0, ',', ' ') }} L</td>
                <td class="text-right" style="background: #f9f9f9;"><strong>{{ number_format($tank->physical_stock, 0, ',', ' ') }} L</strong></td>
                <td class="text-right">
                    <span class="{{ $diffLiters >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $diffLiters > 0 ? '+' : '' }}{{ number_format($diffLiters, 0, ',', ' ') }} L
                    </span>
                </td>
                <td class="text-right">
                    <span class="{{ $textClass }}">
                        {{ $diffPerMille > 0 ? '+' : '' }}{{ number_format($diffPerMille, 1) }}<span class="per-mille">‰</span>
                    </span>
                </td>
                <td>{{ $tank->measurer->name ?? 'N/A' }}</td>
                <td class="text-right">
                    <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="font-size: 9px; color: #7f8c8d; margin-top: 10px;">
        <strong>Seuils de tolérance :</strong> 
        <span class="badge-success">Écart ≤ 5‰</span> | 
        <span class="badge-warning">5‰ < Écart ≤ 10‰</span> | 
        <span class="badge-danger">Écart > 10‰</span>
    </div>
    
    <div class="footer">
        Ce document certifie les niveaux de stock physique à la date indiquée.<br>
        Odysse Energie SA - Excellence Opérationnelle - Généré par {{ $generatedBy }}
    </div>
</body>
</html>