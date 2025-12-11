<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impression Shift #{{ $shift->id }}</title>
    <style>
        /* Style pour l'impression */
        @media print {
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                line-height: 1.4;
            }
            
            .no-print {
                display: none !important;
            }
            
            .page-break {
                page-break-before: always;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 15px;
            }
            
            table, th, td {
                border: 1px solid #000;
            }
            
            th, td {
                padding: 6px;
                text-align: left;
            }
            
            th {
                background-color: #f2f2f2;
                font-weight: bold;
            }
            
            .header {
                text-align: center;
                margin-bottom: 20px;
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
            }
            
            .header h1 {
                margin: 0;
                font-size: 18px;
                color: #333;
            }
            
            .header h2 {
                margin: 5px 0;
                font-size: 14px;
                color: #666;
            }
            
            .info-box {
                margin-bottom: 15px;
                padding: 10px;
                border: 1px solid #ddd;
                background-color: #f9f9f9;
            }
            
            .total-row {
                font-weight: bold;
                background-color: #e9e9e9;
            }
            
            .text-right {
                text-align: right;
            }
            
            .text-center {
                text-align: center;
            }
            
            .badge {
                display: inline-block;
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 11px;
                font-weight: bold;
            }
            
            .badge-success {
                background-color: #28a745;
                color: white;
            }
            
            .badge-danger {
                background-color: #dc3545;
                color: white;
            }
            
            .badge-secondary {
                background-color: #6c757d;
                color: white;
            }
            
            .footer {
                margin-top: 30px;
                padding-top: 10px;
                border-top: 1px solid #000;
                font-size: 10px;
                color: #666;
                text-align: center;
            }
        }
        
        /* Style pour l'affichage à l'écran */
        @media screen {
            body {
                font-family: Arial, sans-serif;
                background-color: #f5f5f5;
                padding: 20px;
            }
            
            .print-container {
                max-width: 800px;
                margin: 0 auto;
                background-color: white;
                padding: 20px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
            
            .print-actions {
                text-align: center;
                margin-bottom: 20px;
                padding: 10px;
                background-color: #f8f9fa;
                border-radius: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Actions d'impression (masquées à l'impression) -->
        <div class="print-actions no-print">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Imprimer
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                <i class="fas fa-times"></i> Fermer
            </button>
            <a href="{{ route('manager.saisies.pdf', $shift->id) }}" class="btn btn-success">
                <i class="fas fa-file-pdf"></i> Télécharger PDF
            </a>
            <a href="{{ route('manager.history') }}" class="btn btn-info">
                <i class="fas fa-arrow-left"></i> Retour à l'historique
            </a>
        </div>

        <!-- En-tête -->
        <div class="header">
            <h1>STATION SERVICE - RAPPORT DE SHIFT</h1>
            <h2>Shift #{{ $shift->id }} - {{ $shift->date_shift->format('d/m/Y') }}</h2>
            <p>Généré le: {{ now()->format('d/m/Y à H:i') }}</p>
        </div>

        <!-- Informations Générales -->
        <div class="info-box">
            <h3>Informations du Shift</h3>
            <table>
                <tr>
                    <td width="30%"><strong>Date du Shift:</strong></td>
                    <td>{{ $shift->date_shift->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td><strong>Heure du Shift:</strong></td>
                    <td>{{ $shift->shift }}</td>
                </tr>
                <tr>
                    <td><strong>Responsable:</strong></td>
                    <td>{{ $shift->responsable }}</td>
                </tr>
                <tr>
                    <td><strong>Créé le:</strong></td>
                    <td>{{ $shift->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            </table>
        </div>

        <!-- Récapitulatif Financier -->
        <div class="info-box">
            <h3>Récapitulatif Financier</h3>
            <table>
                <tr>
                    <td width="40%"><strong>Total Litres Vendus:</strong></td>
                    <td class="text-right">{{ number_format($shift->total_litres, 2, ',', ' ') }} L</td>
                </tr>
                <tr>
                    <td><strong>Total Ventes:</strong></td>
                    <td class="text-right">{{ number_format($shift->total_ventes, 0, ',', ' ') }} F CFA</td>
                </tr>
                <tr>
                    <td><strong>Versement en Espèces:</strong></td>
                    <td class="text-right">{{ number_format($shift->versement, 0, ',', ' ') }} F CFA</td>
                </tr>
                <tr class="total-row">
                    <td><strong>Écart:</strong></td>
                    <td class="text-right">
                        @if($shift->ecart > 0)
                            <span class="badge badge-success">+{{ number_format($shift->ecart, 0, ',', ' ') }} F CFA</span>
                            <small>(Excédent)</small>
                        @elseif($shift->ecart < 0)
                            <span class="badge badge-danger">{{ number_format($shift->ecart, 0, ',', ' ') }} F CFA</span>
                            <small>(Manquant)</small>
                        @else
                            <span class="badge badge-secondary">{{ number_format($shift->ecart, 0, ',', ' ') }} F CFA</span>
                            <small>(Équilibre parfait)</small>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Détails des Pompes -->
        <div class="info-box">
            <h3>Détail des Pompes</h3>
            <table>
                <thead>
                    <tr>
                        <th>Pompe</th>
                        <th>Carburant</th>
                        <th class="text-center">Prix Unitaire</th>
                        <th class="text-center">Index Ouverture</th>
                        <th class="text-center">Index Fermeture</th>
                        <th class="text-center">Retour (L)</th>
                        <th class="text-center">Litrage Vendu</th>
                        <th class="text-right">Montant Ventes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shift->pompeDetails as $detail)
                    <tr>
                        <td>{{ $detail->pompe_nom }}</td>
                        <td>{{ $detail->carburant }}</td>
                        <td class="text-center">{{ number_format($detail->prix_unitaire, 0, ',', ' ') }} F CFA</td>
                        <td class="text-center">{{ number_format($detail->index_ouverture, 2, ',', ' ') }} L</td>
                        <td class="text-center">{{ number_format($detail->index_fermeture, 2, ',', ' ') }} L</td>
                        <td class="text-center">{{ number_format($detail->retour_litres, 2, ',', ' ') }} L</td>
                        <td class="text-center">{{ number_format($detail->litrage_vendu, 2, ',', ' ') }} L</td>
                        <td class="text-right">{{ number_format($detail->montant_ventes, 0, ',', ' ') }} F CFA</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="6"><strong>TOTAUX:</strong></td>
                        <td class="text-center">
                            <strong>{{ number_format($shift->pompeDetails->sum('litrage_vendu'), 2, ',', ' ') }} L</strong>
                        </td>
                        <td class="text-right">
                            <strong>{{ number_format($shift->pompeDetails->sum('montant_ventes'), 0, ',', ' ') }} F CFA</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Observations -->
        <div class="info-box">
            <h3>Observations</h3>
            <table>
                <tr>
                    <td>
                        <p>Signature du Responsable:</p>
                        <br><br><br>
                        <p>________________________________</p>
                    </td>
                    <td>
                        <p>Signature du Superviseur:</p>
                        <br><br><br>
                        <p>________________________________</p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Pied de page -->
        <div class="footer">
            <p>Copyright © {{ date('Y') }} STATION SERVICE. Tous droits réservés.</p>
            <p>Document généré automatiquement par le système de gestion de station</p>
            <p>Page 1 sur 1</p>
        </div>
    </div>

    <!-- Script pour l'impression -->
    <script>
        // Si on est en mode impression, imprimer automatiquement
        if (window.location.search.includes('print=auto')) {
            window.print();
        }
        
        // Après l'impression, revenir à la page précédente
        window.onafterprint = function() {
            setTimeout(function() {
                window.close();
            }, 1000);
        };
        
        // Pour l'affichage écran
        @if(request()->has('print'))
            window.print();
        @endif
    </script>
</body>
</html>