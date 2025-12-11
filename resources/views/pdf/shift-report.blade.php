<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport de Performance - Période {{ $periode ?? 'Non spécifiée' }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
        }
        .header .subtitle {
            color: #7f8c8d;
            font-size: 14px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th {
            background-color: #2c3e50;
            color: white;
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .table-striped tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-success {
            color: #28a745;
        }
        .text-danger {
            color: #dc3545;
        }
        .section-title {
            background-color: #3498db;
            color: white;
            padding: 8px;
            margin-top: 20px;
            margin-bottom: 10px;
            border-radius: 3px;
            font-size: 16px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .stat-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <h1>Rapport de Performance</h1>
        <div class="subtitle">
            <strong>Période:</strong> {{ $periode ?? 'Non spécifiée' }}<br>
            @if(isset($startDate) && isset($endDate))
                <strong>Du:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} 
                <strong>Au:</strong> {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}<br>
            @endif
            <strong>Généré le:</strong> {{ now()->format('d/m/Y H:i') }}<br>
            <strong>Par:</strong> {{ $user->name ?? 'Utilisateur' }}<br>
        </div>
    </div>

    <!-- Statistiques Générales -->
    @if(isset($stats))
    <div class="section-title">Statistiques Générales</div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div>Nombre de Shifts</div>
            <div class="stat-value">{{ $stats['totalShifts'] ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div>Total Litres Vendus</div>
            <div class="stat-value">{{ number_format($stats['totalLitres'] ?? 0, 2, ',', ' ') }} L</div>
        </div>
        <div class="stat-card">
            <div>Total Ventes</div>
            <div class="stat-value">{{ number_format($stats['totalVentes'] ?? 0, 0, ',', ' ') }} F CFA</div>
        </div>
        <div class="stat-card">
            <div>Écart Final</div>
            <div class="stat-value">
                @php
                    // SI $stats['totalEcartFinal'] n'est pas défini, le calculer
                    if (isset($stats['totalEcartFinal'])) {
                        $ecartFinal = $stats['totalEcartFinal'];
                    } elseif (isset($stats['totalVersement']) && isset($stats['totalVentes']) && isset($stats['totalDepenses'])) {
                        $ecartFinal = $stats['totalVersement'] - ($stats['totalVentes'] - $stats['totalDepenses']);
                    } else {
                        $ecartFinal = 0;
                    }
                @endphp
                @if($ecartFinal > 0)
                    <span class="text-success">+{{ number_format($ecartFinal, 0, ',', ' ') }} F CFA</span>
                @elseif($ecartFinal < 0)
                    <span class="text-danger">{{ number_format($ecartFinal, 0, ',', ' ') }} F CFA</span>
                @else
                    {{ number_format($ecartFinal, 0, ',', ' ') }} F CFA
                @endif
            </div>
        </div>
    </div>
    
    <!-- Détail des dépenses -->
    @if(isset($stats['totalDepenses']) && $stats['totalDepenses'] > 0)
    <div style="margin: 20px 0; padding: 10px; background-color: #f8f9fa; border-radius: 5px;">
        <strong>Total Dépenses:</strong> {{ number_format($stats['totalDepenses'], 0, ',', ' ') }} F CFA
    </div>
    @endif
    @endif

    <!-- Liste des Shifts -->
    @if(isset($shifts) && $shifts->count() > 0)
    <div class="section-title">Liste des Shifts ({{ $shifts->count() }})</div>
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>Shift</th>
                <th>Responsable</th>
                <th class="text-right">Ventes</th>
                <th class="text-right">Versement</th>
                <th class="text-right">Dépenses</th>
                <th class="text-right">Écart Final</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shifts as $shift)
            <tr>
                <td>{{ $shift->date_shift->format('d/m/Y') }}</td>
                <td>{{ $shift->shift }}</td>
                <td>{{ $shift->responsable }}</td>
                <td class="text-right">{{ number_format($shift->total_ventes, 0, ',', ' ') }} F CFA</td>
                <td class="text-right">{{ number_format($shift->versement, 0, ',', ' ') }} F CFA</td>
                <td class="text-right">{{ number_format($shift->total_depenses, 0, ',', ' ') }} F CFA</td>
                <td class="text-right">
                    @php
                        // Formule pour calculer l'écart
                        $ecart = $shift->versement - ($shift->total_ventes - $shift->total_depenses);
                    @endphp
                    @if($ecart > 0)
                        <span class="text-success">+{{ number_format($ecart, 0, ',', ' ') }} F CFA</span>
                    @elseif($ecart < 0)
                        <span class="text-danger">{{ number_format($ecart, 0, ',', ' ') }} F CFA</span>
                    @else
                        {{ number_format($ecart, 0, ',', ' ') }} F CFA
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <!-- SUPPRIMÉ : Section des totaux en bas du tableau -->
    </table>
    @else
    <div style="text-align: center; padding: 40px; color: #6c757d;">
        <h3>Aucun shift trouvé pour cette période</h3>
    </div>
    @endif

    <!-- Performance par Carburant -->
    @if(isset($byFuel) && count($byFuel) > 0)
    <div class="section-title">Performance par Type de Carburant</div>
    
    <table class="table">
        <thead>
            <tr>
                <th>Carburant</th>
                <th class="text-right">Litres Vendus</th>
                <th class="text-right">Montant</th>
                <th class="text-right">Pourcentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($byFuel as $fuel => $data)
            <tr>
                <td>{{ $fuel }}</td>
                <td class="text-right">{{ number_format($data['litres'], 2, ',', ' ') }} L</td>
                <td class="text-right">{{ number_format($data['montant'], 0, ',', ' ') }} F CFA</td>
                <td class="text-right">{{ number_format($data['pourcentage_montant'] ?? 0, 2, ',', ' ') }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Pied de page -->
    <div style="margin-top: 50px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #6c757d; font-size: 10px;">
        Généré par Odysse Energie SA • {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>