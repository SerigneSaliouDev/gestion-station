<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport de Réconciliation - Odysse Energie SA</title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .header { text-align: center; border-bottom: 4px solid #1a3c5e; padding-bottom: 15px; margin-bottom: 20px; }
        .company-name { color: #1a3c5e; font-size: 20px; font-weight: bold; letter-spacing: 1px; }
        .brand-orange { color: #f39c12; }
        .report-title { color: #7f8c8d; font-size: 12px; font-weight: bold; margin-top: 5px; }

        .totals-banner { background: #1a3c5e; color: white; padding: 15px; border-radius: 6px; margin-bottom: 20px; border-right: 8px solid #f39c12; }
        .totals-banner h3 { margin: 0 0 10px 0; font-size: 11px; color: #f39c12; text-transform: uppercase; }
        .summary-grid { width: 100%; }
        .summary-grid td { color: white; font-size: 11px; }

        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th { background: #f8f9fa; color: #1a3c5e; padding: 8px; text-align: left; border-bottom: 2px solid #1a3c5e; font-size: 9px; }
        .table td { padding: 7px 8px; border-bottom: 1px solid #eee; }
        .table tr:nth-child(even) { background: #fcfcfc; }

        .badge { padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .badge-success { background: #27ae60; color: white; } /* Réception */
        .badge-danger { background: #e74c3c; color: white; }  /* Vente */
        .badge-warning { background: #f39c12; color: white; } /* Ajustement */

        .text-right { text-align: right; }
        .footer { margin-top: 30px; text-align: center; color: #95a5a6; border-top: 1px solid #eee; padding-top: 10px; font-size: 9px; }
        
        /* Styles pour les informations supplémentaires */
        .info-section { 
            background: #f8f9fa; 
            border: 1px solid #dee2e6; 
            border-radius: 4px; 
            padding: 12px; 
            margin-bottom: 20px; 
            font-size: 9px;
        }
        .info-grid { width: 100%; border-collapse: separate; border-spacing: 10px 5px; }
        .info-label { font-weight: bold; color: #1a3c5e; }
        .info-value { color: #2c3e50; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">ODYSSE ENERGIE <span class="brand-orange">SA</span></div>
        <div class="report-title">RAPPORT DE RÉCONCILIATION DE STOCK</div>
        <p style="margin: 5px 0;">
            @if($station)
                Station: <strong>{{ $station->nom }}</strong> | 
                Code: <strong>{{ $station->code }}</strong> | 
            @endif
            Période: <strong>{{ $startDate->format('d/m/Y') }} au {{ $endDate->format('d/m/Y') }}</strong>
        </p>
        @if($fuelType)
        <p style="margin: 5px 0; font-size: 9px;">
            Carburant: <strong>{{ strtoupper($fuelType) }}</strong>
        </p>
        @endif
    </div>
    
    <!-- Section informations supplémentaires -->
    
    
    <div class="totals-banner">
        <h3>Récapitulatif des flux (Réceptions & Ventes uniquement)</h3>
        <table class="summary-grid">
            <tr>
                <td width="25%"><strong>Réceptions:</strong> {{ number_format($totals['receptions'], 0, ',', ' ') }} L</td>
                <td width="25%"><strong>Ventes:</strong> {{ number_format($totals['sales'], 0, ',', ' ') }} L</td>
                <td width="25%"><strong>Différence:</strong> {{ number_format($stockTheorique, 0, ',', ' ') }} L</td>
                <td width="25%" align="right"><span style="font-size: 13px; font-weight: bold;">{{ number_format($totals['amount'], 0, ',', ' ') }} F CFA</span></td>
            </tr>
        </table>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Carburant</th>
                <th class="text-right">Quantité (L)</th>
                <th class="text-right">P.U</th>
                <th class="text-right">Montant</th>
                <th class="text-right">Stock Avant</th>
                <th class="text-right">Stock Après</th>
                <th>Agent</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $movement)
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
                <td><strong>{{ ucfirst($movement->fuel_type) }}</strong></td>
                <td class="text-right">{{ number_format(abs($movement->quantity), 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($movement->unit_price, 0, ',', ' ') }}</td>
                <td class="text-right"><strong>{{ number_format(abs($movement->total_amount), 0, ',', ' ') }}</strong></td>
                <td class="text-right" style="color: #7f8c8d;">{{ number_format($movement->stock_before ?? 0, 0, ',', ' ') }}</td>
                <td class="text-right" style="font-weight: bold;">{{ number_format($movement->stock_after ?? 0, 0, ',', ' ') }}</td>
                <td>{{ $movement->recorder->name ?? ($movement->recorded_by ?? 'N/A') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px; color: #95a5a6;">
                    Aucun mouvement de réception ou vente trouvé pour cette période.
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($movements->count() > 0)
        <tfoot style="background-color: #f8f9fa; font-weight: bold;">
            <tr>
                <td colspan="3" style="text-align: right; padding: 8px;">TOTAUX:</td>
                <td class="text-right">{{ number_format($totals['receptions'] + $totals['sales'], 0, ',', ' ') }} L</td>
                <td></td>
                <td class="text-right">{{ number_format($totals['amount'], 0, ',', ' ') }} F CFA</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
        @endif
    </table>
    
    <!-- Note sur les ajustements -->
    @if($adjustmentsData['count'] > 0)
    <div style="background-color: #fff9e6; border-left: 4px solid #f39c12; padding: 8px; margin-bottom: 20px; font-size: 9px;">
        <strong>Note :</strong> {{ $adjustmentsData['count'] }} ajustement(s) de stock détecté(s) 
        ({{ $adjustmentsData['positifs'] > 0 ? '+' . number_format($adjustmentsData['positifs'], 0, ',', ' ') . ' L' : '' }}
        {{ $adjustmentsData['negatifs'] > 0 ? ' -' . number_format($adjustmentsData['negatifs'], 0, ',', ' ') . ' L' : '' }}).
        Les ajustements ne sont pas inclus dans le tableau ci-dessus car ils ne font pas partie du flux normal de réception/vente.
    </div>
    @endif
    
    <div class="footer">
        Document de contrôle interne - Odysse Energie SA - Généré le {{ $generatedAt->format('d/m/Y H:i') }} par {{ $generatedBy }}
    </div>
</body>
</html>