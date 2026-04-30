<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport Shift - Odysse Energie SA</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; line-height: 1.4; color: #333; }
        .header { border-bottom: 3px solid #f39c12; margin-bottom: 20px; padding-bottom: 10px; }
        .company-name { color: #1a3c5e; font-size: 20px; font-weight: bold; }
        .report-title { color: #7f8c8d; font-size: 14px; text-transform: uppercase; }
        
        .section-title { background-color: #f8f9fa; color: #1a3c5e; padding: 6px 10px; margin: 15px 0 10px; border-left: 4px solid #1a3c5e; font-weight: bold; }
        
        .info-table { width: 100%; margin-bottom: 15px; }
        .info-box { border: 1px solid #dee2e6; padding: 10px; border-radius: 4px; }
        .label { color: #7f8c8d; font-size: 10px; text-transform: uppercase; }
        .value { font-size: 13px; font-weight: bold; color: #1a3c5e; }

        .table { width: 100%; border-collapse: collapse; }
        .table th { background-color: #1a3c5e; color: white; padding: 8px; font-size: 10px; border: 1px solid #1a3c5e; }
        .table td { padding: 7px; border: 1px solid #dee2e6; text-right; }
        .total-row { background-color: #eee; font-weight: bold; }
        
        .summary-card { background: #1a3c5e; color: white; padding: 20px; border-radius: 8px; margin-top: 20px; text-align: center; }
        .summary-card hr { border: 0; border-top: 1px solid rgba(255,255,255,0.2); margin: 10px 0; }
        .ecart-pill { display: inline-block; padding: 5px 15px; border-radius: 20px; background: white; font-weight: bold; font-size: 16px; margin-top: 5px; }
        
        .text-success { color: #2ecc71; }
        .text-danger { color: #e74c3c; }
    </style>
</head>
<body>
    <div class="header">
        <table width="100%">
            <tr>
                <td>
                    <div class="company-name">ODYSSE ENERGIE SA</div>
                    <div class="report-title">Fiche de Clôture de Shift #{{ $shift->id }}</div>
                </td>
                <td align="right">
                    <div class="value">{{ $shift->date_shift->format('d/m/Y') }}</div>
                    <div class="label">Période: {{ $shift->shift }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Informations Agent</div>
    <table class="info-table">
        <tr>
            <td width="33%"><div class="label">Pompiste</div><div class="value">{{ $shift->responsable }}</div></td>
            <td width="33%"><div class="label">Total Litres</div><div class="value">{{ number_format($shift->total_litres, 2) }} L</div></td>
            <td width="33%"><div class="label">Statut</div><div class="value">{{ $shift->ecart_final == 0 ? 'Équilibré' : 'Clôturé' }}</div></td>
        </tr>
    </table>

    <div class="section-title">Relevés des Index</div>
    <table class="table">
        <thead>
            <tr>
                <th>POMPE</th>
                <th>PRODUIT</th>
                <th>OUVERTURE</th>
                <th>FERMETURE</th>
                <th>RETOUR</th>
                <th>VENDU (L)</th>
                <th>MONTANT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shift->pompeDetails as $detail)
            <tr>
                <td align="left"><strong>{{ $detail->pompe_nom }}</strong></td>
                <td align="left">{{ $detail->carburant }}</td>
                <td>{{ number_format($detail->index_ouverture, 2) }}</td>
                <td>{{ number_format($detail->index_fermeture, 2) }}</td>
                <td>{{ number_format($detail->retour_litres, 2) }}</td>
                <td><strong>{{ number_format($detail->litrage_vendu, 2) }}</strong></td>
                <td>{{ number_format($detail->montant_ventes, 0) }} F</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-card">
        <div style="font-size: 12px; opacity: 0.8;">RÉCAPITULATIF FINANCIER</div>
        <table width="100%" style="margin-top:10px;">
            <tr>
                <td>Ventes : {{ number_format($shift->total_ventes, 0) }} F</td>
                <td>Dépenses : {{ number_format($shift->total_depenses, 0) }} F</td>
                <td>Versement : {{ number_format($shift->versement, 0) }} F</td>
            </tr>
        </table>
        <hr>
        <div>ÉCART FINAL DU SHIFT</div>
        <div class="ecart-pill" style="color: {{ $shift->ecart_final >= 0 ? '#27ae60' : '#e74c3c' }}">
            {{ $shift->ecart_final > 0 ? '+' : '' }}{{ number_format($shift->ecart_final, 0, ',', ' ') }} F CFA
        </div>
    </div>
</body>
</html>