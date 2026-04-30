<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport de Station - {{ $station->nom ?? 'Global' }}</title>
    <style>
        /* ========== CONFIGURATION PAGE PDF ========== */
        @page {
            margin: 1cm;
        }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* ========== COULEURS ET TYPOGRAPHIE ========== */
        .text-primary { color: #3498db; }
        .text-dark { color: #2c3e50; }
        .text-success { color: #27ae60; }
        .text-danger { color: #e74c3c; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }

        /* ========== EN-TÊTE ========== */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #FF8C00;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header-logo {
            flex: 1;
        }

        .header-logo img {
            height: 200px;
            max-width: 200px;
        }

        .header-title {
            flex: 2;
            text-align: center;
        }

        .header-title h1 {
            margin: 0;
            color: #333;
            font-size: 22px;
            text-transform: uppercase;
        }

        .header-title .subtitle {
            color: #666;
            font-size: 13px;
            margin-top: 5px;
        }

        .header-title .period {
            color: #888;
            margin-top: 5px;
            font-size: 11px;
        }

        /* ========== BOX INFORMATIONS ========== */
        .info-table {
            width: 100%;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 8px;
            border: none;
        }

        .info-label {
            font-weight: bold;
            color: #2c3e50;
            width: 15%;
        }

        /* ========== GRILLE DE STATISTIQUES ========== */
        .stats-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: center;
            width: 25%;
            vertical-align: top;
            border-radius: 5px;
        }

        .stat-card h3 {
            margin: 0 0 8px 0;
            font-size: 10px;
            color: #7f8c8d;
            text-transform: uppercase;
        }

        .stat-card .value {
            font-size: 16px;
            font-weight: bold;
            color: #3498db;
        }

        .stat-card .sub-value {
            font-size: 9px;
            color: #95a5a6;
            margin-top: 4px;
        }

        /* ========== TITRES DE SECTION ========== */
        .section-title {
            font-size: 15px;
            color: #2c3e50;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #FF8C00;
            text-transform: uppercase;
        }

        /* ========== TABLEAUX DE DONNÉES ========== */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table th {
            background: #FF8C00;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }

        .data-table td {
            padding: 7px 8px;
            border-bottom: 1px solid #eee;
        }

        .data-table tr:nth-child(even) {
            background: #fcfcfc;
        }

        /* ========== BADGES ========== */
        .badge {
            padding: 3px 7px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
            display: inline-block;
        }

        .badge-success { background: #27ae60; }
        .badge-warning { background: #f39c12; }
        .badge-danger { background: #e74c3c; }
        .badge-info { background: #3498db; }

        /* ========== LAYOUT DEUX COLONNES ========== */
        .two-columns {
            width: 100%;
            border-spacing: 20px 0;
            margin-bottom: 20px;
        }

        .two-columns td {
            width: 50%;
            vertical-align: top;
        }

        /* ========== FOOTER ========== */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #7f8c8d;
            font-size: 10px;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

@php
    // Helper pour les badges de stock
    function getStockLabel($quantity) {
        if ($quantity < 5000) return ['class' => 'badge-danger', 'label' => 'CRITIQUE'];
        if ($quantity < 10000) return ['class' => 'badge-warning', 'label' => 'BAS'];
        if ($quantity < 20000) return ['class' => 'badge-info', 'label' => 'MOYEN'];
        return ['class' => 'badge-success', 'label' => 'BON'];
    }

    // Fonction pour afficher le logo
    function renderLogo() {
        $logoPath = public_path('adminlte/assets/img/odysse.jpg');
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            echo '<img src="data:image/jpeg;base64,' . $logoData . '" alt="ODYSSEE ENERGIE">';
        } else {
            echo '<div style="font-size: 20px; font-weight: bold; color: #FF8C00;">ODYSSEE ÉNERGIE</div>';
        }
    }
@endphp

<!-- ========== EN-TÊTE ========== -->
<div class="header">
    <div class="header-logo">
        @php renderLogo(); @endphp
    </div>
    <div class="header-title">
        <h1>RAPPORT DE STATION</h1>
        <div class="subtitle">
            @if(isset($station))
                Station: <strong>{{ $station->nom }}</strong> | Code: <strong>{{ $station->code }}</strong>
            @else
                <strong>RAPPORT GLOBAL - TOUTES LES STATIONS</strong>
            @endif
        </div>
        <div class="period">
            Période: {{ $startDate->format('d/m/Y') }} au {{ $endDate->format('d/m/Y') }}
        </div>
    </div>
</div>

<!-- ========== INFORMATIONS STATION ========== -->
@if(isset($station))
<table class="info-table">
    <tr>
        <td class="info-label">Manager de station</td>
        <td>: {{ $station->manager->name ?? 'Non assigné' }}</td>
        <td class="info-label">Ville / Adresse</td>
        <td>: {{ $station->ville }} / {{ $station->adresse }}</td>
    </tr>
    <tr>
        <td class="info-label">Statut opérationnel</td>
        <td>
            : <span class="badge {{ $station->statut == 'actif' ? 'badge-success' : 'badge-danger' }}">
                {{ $station->statut }}
            </span>
        </td>
        <td class="info-label">Date d'extraction</td>
        <td>: {{ now()->format('d/m/Y H:i') }}</td>
    </tr>
</table>
@endif

<!-- ========== PERFORMANCE DE LA PÉRIODE ========== -->
<h2 class="section-title">Performance de la Période</h2>
<table class="stats-table">
    <tr>
        <td class="stat-card">
            <h3>Ventes Totales</h3>
            <div class="value">{{ number_format($stats['total_sales'], 0, ',', ' ') }} <small>FCFA</small></div>
            <div class="sub-value">{{ number_format($stats['total_litres'], 0, ',', ' ') }} L cumulés</div>
        </td>
        <td class="stat-card">
            <h3>Activité Shifts</h3>
            <div class="value">{{ $stats['shift_count'] }}</div>
            <div class="sub-value">{{ number_format($stats['average_sales_per_shift'], 0, ',', ' ') }} FCFA/avg</div>
        </td>
        <td class="stat-card">
            <h3>Écart Cumulé</h3>
            <div class="value {{ $stats['average_ecart'] < 0 ? 'text-danger' : 'text-success' }}">
                {{ number_format($stats['average_ecart'], 0, ',', ' ') }}
            </div>
            <div class="sub-value">{{ $stats['average_ecart'] < 0 ? 'Déficit global' : 'Surplus global' }}</div>
        </td>
        <td class="stat-card">
            <h3>Dépenses</h3>
            <div class="value text-danger">{{ number_format($stats['total_depenses'], 0, ',', ' ') }}</div>
            <div class="sub-value">Frais d'exploitation</div>
        </td>
    </tr>
</table>

<!-- ========== SECTION DEUX COLONNES : VENTES PAR CARBURANT + ÉTAT DES STOCKS ========== -->
<table class="two-columns">
    <tr>
        <!-- Colonne gauche : Ventes par Carburant -->
        <td>
            <h2 class="section-title">Ventes par Carburant</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th class="text-right">Montant (FCFA)</th>
                        <th class="text-right">%</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Super Carburant</td>
                        <td class="text-right">{{ number_format($salesByFuel['super'], 0, ',', ' ') }}</td>
                        <td class="text-right">{{ $stats['total_sales'] > 0 ? round(($salesByFuel['super'] / $stats['total_sales']) * 100, 1) : 0 }}%</td>
                    </tr>
                    <tr>
                        <td>Gazole (Diesel)</td>
                        <td class="text-right">{{ number_format($salesByFuel['gasoil'], 0, ',', ' ') }}</td>
                        <td class="text-right">{{ $stats['total_sales'] > 0 ? round(($salesByFuel['gasoil'] / $stats['total_sales']) * 100, 1) : 0 }}%</td>
                    </tr>
                </tbody>
            </table>
        </td>

        <!-- Colonne droite : État des Stocks -->
        <td>
            <h2 class="section-title">État des Stocks</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th class="text-right">Volume Actuel</th>
                        <th class="text-center">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(['super' => 'Super', 'gasoil' => 'Gazole'] as $key => $label)
                    @php $status = getStockLabel($currentStocks[$key]); @endphp
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="text-right bold">{{ number_format($currentStocks[$key], 0, ',', ' ') }} L</td>
                        <td class="text-center">
                            <span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </td>
    </tr>
</table>

<!-- ========== JOURNAL DES SHIFTS ========== -->
@if($shifts->count() > 0)
<div class="page-break"></div>
<h2 class="section-title">Journal des Shifts ({{ $shifts->count() }} derniers)</h2>
<table class="data-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Shift</th>
            <th>Pompiste</th>
            <th class="text-right">Ventes</th>
            <th class="text-right">Volume</th>
            <th class="text-right">Écart</th>
            <th class="text-right">Dépenses</th>
        </tr>
    </thead>
    <tbody>
        @foreach($shifts->take(20) as $shift)
        <tr>
            <td>{{ $shift->date_shift->format('d/m/Y') }}</td>
            <td>{{ $shift->shift }}</td>
            <td>{{ $shift->responsable }}</td>
            <td class="text-right bold">{{ number_format($shift->total_ventes, 0, ',', ' ') }}</td>
            <td class="text-right">{{ number_format($shift->total_litres, 0, ',', ' ') }} L</td>
            <td class="text-right {{ $shift->ecart_final < 0 ? 'text-danger' : 'text-success' }}">
                {{ number_format($shift->ecart_final, 0, ',', ' ') }}
            </td>
            <td class="text-right">{{ number_format($shift->total_depenses, 0, ',', ' ') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<!-- ========== FOOTER ========== -->
<div class="footer">
    <div>Document confidentiel généré par <strong>{{ $generatedBy }}</strong> le {{ $generatedAt->format('d/m/Y à H:i') }}</div>
    <div style="margin-top: 5px; color: #FF8C00; font-weight: bold;">
        ODYSSEE ÉNERGIE • Système de Pilotage Centralisé
    </div>
</div>

</body>
</html>