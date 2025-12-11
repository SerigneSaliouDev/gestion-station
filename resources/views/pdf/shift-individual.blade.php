<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport Shift #{{ $shift->id }}</title>
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
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .info-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            font-size: 16px;
            margin-top: 5px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #7f8c8d;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <h1>Rapport Shift #{{ $shift->id }}</h1>
        <div class="subtitle">
            Généré le : {{ now()->format('d/m/Y H:i') }}<br>
            Par : {{ $user->name ?? 'Utilisateur' }}
        </div>
    </div>

    <!-- Informations du Shift -->
    <div class="section-title">Informations Générales</div>
    
    <div class="info-grid">
        <div class="info-card">
            <div class="info-label">Date du Shift</div>
            <div class="info-value">{{ $shift->date_shift->format('d/m/Y') }}</div>
        </div>
        <div class="info-card">
            <div class="info-label">Heure du Shift</div>
            <div class="info-value">{{ $shift->shift }}</div>
        </div>
        <div class="info-card">
            <div class="info-label">Responsable</div>
            <div class="info-value">{{ $shift->responsable }}</div>
        </div>
        <div class="info-card">
            <div class="info-label">Statut</div>
            <div class="info-value">
                @if($shift->ecart_final > 0)
                    <span class="text-success">Excédent (+{{ number_format($shift->ecart_final, 0, ',', ' ') }} F CFA)</span>
                @elseif($shift->ecart_final < 0)
                    <span class="text-danger">Manquant ({{ number_format($shift->ecart_final, 0, ',', ' ') }} F CFA)</span>
                @else
                    Équilibré
                @endif
            </div>
        </div>
    </div>

    <!-- Récapitulatif Financier -->
    <div class="section-title">Récapitulatif Financier</div>
    
    <div class="info-grid">
        <div class="info-card">
            <div class="info-label">Total Litres Vendus</div>
            <div class="info-value">{{ number_format($shift->total_litres, 2, ',', ' ') }} L</div>
        </div>
        <div class="info-card">
            <div class="info-label">Total des Ventes</div>
            <div class="info-value">{{ number_format($shift->total_ventes, 0, ',', ' ') }} F CFA</div>
        </div>
        <div class="info-card">
            <div class="info-label">Versement en Espèces</div>
            <div class="info-value">{{ number_format($shift->versement, 0, ',', ' ') }} F CFA</div>
        </div>
        <div class="info-card">
            <div class="info-label">Total Dépenses</div>
            <div class="info-value">{{ number_format($shift->total_depenses, 0, ',', ' ') }} F CFA</div>
        </div>
    </div>

    <!-- Détails des Pompes -->
    <div class="section-title">Détails des Pompes</div>
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Pompe</th>
                <th>Carburant</th>
                <th class="text-right">Prix Unitaire</th>
                <th class="text-right">Index Ouverture</th>
                <th class="text-right">Index Fermeture</th>
                <th class="text-right">Retour (L)</th>
                <th class="text-right">Litrage Vendu</th>
                <th class="text-right">Montant Ventes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shift->pompeDetails as $detail)
            <tr>
                <td>{{ $detail->pompe_nom }}</td>
                <td>{{ $detail->carburant }}</td>
                <td class="text-right">{{ number_format($detail->prix_unitaire, 0, ',', ' ') }} F CFA</td>
                <td class="text-right">{{ number_format($detail->index_ouverture, 2, ',', ' ') }} L</td>
                <td class="text-right">{{ number_format($detail->index_fermeture, 2, ',', ' ') }} L</td>
                <td class="text-right">{{ number_format($detail->retour_litres, 2, ',', ' ') }} L</td>
                <td class="text-right">{{ number_format($detail->litrage_vendu, 2, ',', ' ') }} L</td>
                <td class="text-right">{{ number_format($detail->montant_ventes, 0, ',', ' ') }} F CFA</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right"><strong>TOTAUX :</strong></td>
                <td class="text-right"><strong>{{ number_format($shift->pompeDetails->sum('litrage_vendu'), 2, ',', ' ') }} L</strong></td>
                <td class="text-right"><strong>{{ number_format($shift->pompeDetails->sum('montant_ventes'), 0, ',', ' ') }} F CFA</strong></td>
            </tr>
        </tfoot>
    </table>

    <!-- Dépenses -->
    @if($shift->depenses->count() > 0)
    <div class="section-title">Dépenses du Shift</div>
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Type de Dépense</th>
                <th>Description</th>
                <th class="text-right">Montant</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shift->depenses as $depense)
            <tr>
                <td>
                    @switch($depense->type_depense)
                        @case('carburant_vehicule')
                            Carburant Véhicule
                            @break
                        @case('nourriture')
                            Nourriture
                            @break
                        @case('maintenance')
                            Maintenance
                            @break
                        @case('achat_divers')
                            Achat Divers
                            @break
                        @case('frais_transport')
                            Frais de Transport
                            @break
                        @default
                            {{ ucfirst(str_replace('_', ' ', $depense->type_depense)) }}
                    @endswitch
                </td>
                <td>{{ $depense->description ?? 'Aucune description' }}</td>
                <td class="text-right text-danger">- {{ number_format($depense->montant, 0, ',', ' ') }} F CFA</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="text-right"><strong>Total Dépenses :</strong></td>
                <td class="text-right text-danger"><strong>- {{ number_format($shift->total_depenses, 0, ',', ' ') }} F CFA</strong></td>
            </tr>
        </tfoot>
    </table>
    @endif

    <!-- Calcul Final -->
    <div class="section-title">Calcul Final</div>
    
    <div style="text-align: center; padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin: 20px 0;">
        <h3 style="margin: 0 0 10px 0;">Récapitulatif</h3>
        <div style="font-size: 14px; line-height: 1.6;">
            <strong>Ventes Totales :</strong> {{ number_format($shift->total_ventes, 0, ',', ' ') }} F CFA<br>
            <strong>- Versement :</strong> {{ number_format($shift->versement, 0, ',', ' ') }} F CFA<br>
            @if($shift->total_depenses > 0)
                <strong>- Dépenses :</strong> {{ number_format($shift->total_depenses, 0, ',', ' ') }} F CFA<br>
            @endif
            <hr style="margin: 10px 0;">
            <strong style="font-size: 16px;">
                Écart Final : 
                @if($shift->ecart_final > 0)
                    <span class="text-success">+{{ number_format($shift->ecart_final, 0, ',', ' ') }} F CFA</span>
                @elseif($shift->ecart_final < 0)
                    <span class="text-danger">{{ number_format($shift->ecart_final, 0, ',', ' ') }} F CFA</span>
                @else
                    {{ number_format($shift->ecart_final, 0, ',', ' ') }} F CFA
                @endif
            </strong>
        </div>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        Document généré automatiquement par le système de gestion de station-service<br>
        Shift #{{ $shift->id }} - Date : {{ $shift->date_shift->format('d/m/Y') }}<br>
        Page 1
    </div>
</body>
</html>