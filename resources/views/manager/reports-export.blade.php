<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Rapports - {{ ucfirst($periode) }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 16px;
        }
        
        .header h2 {
            margin: 5px 0;
            font-size: 13px;
            color: #666;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        
        table, th, td {
            border: 1px solid #000;
        }
        
        th, td {
            padding: 4px;
            text-align: left;
        }
        
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        .summary {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        
        .summary h3 {
            margin-top: 0;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }
        
        .summary-item {
            padding: 5px;
            border: 1px solid #ccc;
            background-color: white;
        }
        
        .summary-label {
            font-weight: bold;
            color: #333;
        }
        
        .summary-value {
            font-size: 12px;
            font-weight: bold;
            color: #000;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #e9e9e9;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #000;
            font-size: 9px;
            color: #666;
            text-align: center;
        }
        
        .fuel-stats {
            margin: 15px 0;
        }
        
        .fuel-stats h3 {
            margin-bottom: 10px;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
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
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <h1>EXPORT DES RAPPORTS - STATION SERVICE</h1>
        <h2>Période: {{ ucfirst($periode) }} - {{ now()->format('d/m/Y') }}</h2>
        <p>Export généré le: {{ now()->format('d/m/Y à H:i:s') }}</p>
    </div>

    <!-- Résumé Statistique -->
    <div class="summary">
        <h3>Résumé Statistique</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Nombre de Shifts:</div>
                <div class="summary-value">{{ $shifts->count() }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Ventes:</div>
                <div class="summary-value">{{ number_format($shifts->sum('total_ventes'), 0, ',', ' ') }} F CFA</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Versements:</div>
                <div class="summary-value">{{ number_format($shifts->sum('versement'), 0, ',', ' ') }} F CFA</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Écart Total:</div>
                <div class="summary-value">{{ number_format($shifts->sum('ecart'), 0, ',', ' ') }} F CFA</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Litres Vendus:</div>
                <div class="summary-value">{{ number_format($shifts->sum('total_litres'), 2, ',', ' ') }} L</div>
            </div>
        </div>
    </div>

    <!-- Liste détaillée des Shifts -->
    <h3>Détail des Shifts</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Shift</th>
                <th>Responsable</th>
                <th class="text-right">Litres</th>
                <th class="text-right">Ventes</th>
                <th class="text-right">Versement</th>
                <th class="text-right">Écart</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shifts as $shift)
            <tr>
                <td>#{{ $shift->id }}</td>
                <td>{{ $shift->date_shift->format('d/m/Y') }}</td>
                <td>{{ $shift->shift }}</td>
                <td>{{ $shift->responsable }}</td>
                <td class="text-right">{{ number_format($shift->total_litres, 2, ',', ' ') }} L</td>
                <td class="text-right">{{ number_format($shift->total_ventes, 0, ',', ' ') }} F CFA</td>
                <td class="text-right">{{ number_format($shift->versement, 0, ',', ' ') }} F CFA</td>
                <td class="text-right">{{ number_format($shift->ecart, 0, ',', ' ') }} F CFA</td>
                <td class="text-center">
                    @if($shift->ecart > 0)
                        <span class="badge badge-success">Excédent</span>
                    @elseif($shift->ecart < 0)
                        <span class="badge badge-danger">Manquant</span>
                    @else
                        <span class="badge badge-secondary">Équilibre</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4"><strong>TOTAUX:</strong></td>
                <td class="text-right">
                    <strong>{{ number_format($shifts->sum('total_litres'), 2, ',', ' ') }} L</strong>
                </td>
                <td class="text-right">
                    <strong>{{ number_format($shifts->sum('total_ventes'), 0, ',', ' ') }} F CFA</strong>
                </td>
                <td class="text-right">
                    <strong>{{ number_format($shifts->sum('versement'), 0, ',', ' ') }} F CFA</strong>
                </td>
                <td class="text-right">
                    <strong>{{ number_format($shifts->sum('ecart'), 0, ',', ' ') }} F CFA</strong>
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <!-- Statistiques par Carburant -->
    @if(isset($byFuel) && count($byFuel) > 0)
    <div class="page-break"></div>
    <div class="fuel-stats">
        <h3>Statistiques par Type de Carburant</h3>
        <table>
            <thead>
                <tr>
                    <th>Carburant</th>
                    <th class="text-right">Quantité (L)</th>
                    <th class="text-right">Montant (F CFA)</th>
                    <th class="text-right">% du Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalAmount = $shifts->sum('total_ventes');
                    $totalLiters = $shifts->sum('total_litres');
                @endphp
                @foreach($byFuel as $fuel => $stats)
                <tr>
                    <td>{{ $fuel }}</td>
                    <td class="text-right">{{ number_format($stats['litres'], 2, ',', ' ') }} L</td>
                    <td class="text-right">{{ number_format($stats['montant'], 0, ',', ' ') }} F CFA</td>
                    <td class="text-right">
                        @if($totalAmount > 0)
                            {{ number_format(($stats['montant'] / $totalAmount) * 100, 1, ',', ' ') }}%
                        @else
                            0%
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td><strong>TOTAUX:</strong></td>
                    <td class="text-right">
                        <strong>{{ number_format($totalLiters, 2, ',', ' ') }} L</strong>
                    </td>
                    <td class="text-right">
                        <strong>{{ number_format($totalAmount, 0, ',', ' ') }} F CFA</strong>
                    </td>
                    <td class="text-right">
                        <strong>100%</strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    <!-- Analyse des Écarts -->
    <div class="page-break"></div>
    <div class="fuel-stats">
        <h3>Analyse des Écarts</h3>
        <table>
            <thead>
                <tr>
                    <th>Type d'Écart</th>
                    <th class="text-right">Nombre</th>
                    <th class="text-right">Montant Total</th>
                    <th>Pourcentage</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $excédents = $shifts->where('ecart', '>', 0);
                    $manquants = $shifts->where('ecart', '<', 0);
                    $equilibres = $shifts->where('ecart', '=', 0);
                @endphp
                <tr>
                    <td>Excédents (Ventes > Versement)</td>
                    <td class="text-right">{{ $excédents->count() }}</td>
                    <td class="text-right">
                        @if($excédents->count() > 0)
                            +{{ number_format($excédents->sum('ecart'), 0, ',', ' ') }} F CFA
                        @else
                            0 F CFA
                        @endif
                    </td>
                    <td>
                        @if($shifts->count() > 0)
                            {{ number_format(($excédents->count() / $shifts->count()) * 100, 1, ',', ' ') }}%
                        @else
                            0%
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Manquants (Ventes < Versement)</td>
                    <td class="text-right">{{ $manquants->count() }}</td>
                    <td class="text-right">
                        @if($manquants->count() > 0)
                            {{ number_format($manquants->sum('ecart'), 0, ',', ' ') }} F CFA
                        @else
                            0 F CFA
                        @endif
                    </td>
                    <td>
                        @if($shifts->count() > 0)
                            {{ number_format(($manquants->count() / $shifts->count()) * 100, 1, ',', ' ') }}%
                        @else
                            0%
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Équilibres Parfaits</td>
                    <td class="text-right">{{ $equilibres->count() }}</td>
                    <td class="text-right">0 F CFA</td>
                    <td>
                        @if($shifts->count() > 0)
                            {{ number_format(($equilibres->count() / $shifts->count()) * 100, 1, ',', ' ') }}%
                        @else
                            0%
                        @endif
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td><strong>TOTAL:</strong></td>
                    <td class="text-right"><strong>{{ $shifts->count() }}</strong></td>
                    <td class="text-right">
                        <strong>{{ number_format($shifts->sum('ecart'), 0, ',', ' ') }} F CFA</strong>
                    </td>
                    <td><strong>100%</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Notes et Commentaires -->
    <div class="page-break"></div>
    <div class="summary">
        <h3>Notes et Commentaires</h3>
        <table>
            <tr>
                <td width="50%">
                    <h4>Observations Générales:</h4>
                    <ul>
                        <li>Export généré pour analyse interne</li>
                        <li>Période: {{ ucfirst($periode) }}</li>
                        <li>Total de {{ $shifts->count() }} shifts analysés</li>
                        <li>Écart moyen: {{ $shifts->count() > 0 ? number_format($shifts->sum('ecart') / $shifts->count(), 0, ',', ' ') : 0 }} F CFA par shift</li>
                    </ul>
                </td>
                <td width="50%">
                    <h4>Recommandations:</h4>
                    <ul>
                        <li>Vérifier les shifts avec écarts importants</li>
                        <li>Analyser la performance par responsable</li>
                        <li>Optimiser les niveaux de stock</li>
                        <li>Réviser les procédures de versement</li>
                    </ul>
                </td>
            </tr>
        </table>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <p>Document confidentiel - Usage interne uniquement</p>
        <p>Station Service Management System - Version 1.0</p>
        <p>Export généré automatiquement - Page @{{PAGE}} sur @{{PAGES}}</p>
    </div>

    <!-- Script pour l'export -->
    <script>
        // Suggestion pour l'export CSV/Excel
        // Vous pouvez ajouter un bouton pour convertir ce HTML en Excel
        // ou utiliser un package Laravel comme Maatwebsite/Excel
        
        // Fonction pour exporter en CSV
        function exportToCSV() {
            let csv = [];
            let rows = document.querySelectorAll("table");
            
            for (let i = 0; i < rows.length; i++) {
                let row = [], cols = rows[i].querySelectorAll("td, th");
                
                for (let j = 0; j < cols.length; j++) {
                    row.push(cols[j].innerText);
                }
                
                csv.push(row.join(","));
            }
            
            // Télécharger le fichier CSV
            downloadCSV(csv.join("\n"), 'rapports-{{ $periode }}-{{ date("Y-m-d") }}.csv');
        }
        
        function downloadCSV(csv, filename) {
            let csvFile = new Blob([csv], {type: "text/csv"});
            let downloadLink = document.createElement("a");
            
            downloadLink.download = filename;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
        }
        
        // Si on est en mode impression, imprimer automatiquement
        if (window.location.search.includes('print=yes')) {
            window.print();
        }
    </script>
</body>
</html>