@extends('layouts.app')

@section('title', 'Saisie Index/Ventes')
@section('page-title', 'Saisie Quotidienne des Index et Ventes')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('manager.index_form') }}">Manager</a></li>
<li class="breadcrumb-item active">Saisie Index</li>
@endsection

@section('content')
<form action="{{ route('manager.store_index') }}" method="POST" id="indexForm" enctype="multipart/form-data">
    @csrf
    
    <!-- Informations Générales -->
    <div class="card card-outline card-primary">
        <div class="card-header odyssee-bg-primary">
            <h3 class="card-title">Informations Générales du Shift</h3>
        </div>
        <div class="card-body">
            <!-- AJOUTER cette ligne -->
            @if(isset($station) && $station)
            <div class="alert alert-info">
                <i class="fas fa-gas-pump"></i> <strong>Station:</strong> {{ $station->nom }} - {{ $station->ville }}
                <br><small class="text-muted">Code: {{ $station->code }}</small>
            </div>
            @endif
            
            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="shift_date">Date du Shift</label>
                    <input type="date" name="shift_date" id="shift_date" class="form-control" 
                           value="{{ old('shift_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4 form-group">
                    <label for="shift_time">Shift</label>
                    <input type="text" name="shift_time" id="shift_time" class="form-control" 
                           value="{{ old('shift_time') }}" placeholder="Ex: 8H-18H ou 18H-8H" required>
                </div>
                <div class="col-md-4 form-group">
                    <label for="responsible_name">Nom du Pompiste</label>
                    <input type="text" name="responsible_name" id="responsible_name" class="form-control" 
                           value="{{ old('responsible_name') }}" placeholder="Nom du pompiste" required>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des Pompes -->
    <div class="card">
        <div class="card-header odyssee-bg-primary">
            <h3 class="card-title text-white">Relevé des Index des Pompes</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-bordered mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Pompe</th>
                            <th>Carburant</th>
                            <th>Prix Unitaire</th>
                            <th>Index d'Ouverture</th>
                            <th>Index de Fermeture</th>
                            <th>Retour (Litres)</th>
                            <th>Litrage Vendu (L)</th>
                            <th>Montant des Ventes (F CFA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pumps as $index => $pump)
                        <tr>
                            <td>
                                {{ $pump['name'] }}
                                <input type="hidden" name="pumps[{{ $index }}][name]" value="{{ $pump['name'] }}">
                                <input type="hidden" name="pumps[{{ $index }}][fuel_type]" value="{{ $pump['fuel_type'] }}">
                                <input type="hidden" name="pumps[{{ $index }}][unit_price]" value="{{ $pump['unit_price'] }}">
                            </td>
                            <td>{{ $pump['fuel_type'] }}</td>
                            <td>{{ number_format($pump['unit_price'], 0, ',', ' ') }}</td>
                            
                            <td>
                                <input type="number" step="0.01" name="pumps[{{ $index }}][opening_index]" 
                                       value="{{ old('pumps.'.$index.'.opening_index', '0.00') }}" 
                                       class="form-control form-control-sm pump-input" 
                                       data-pump-index="{{ $index }}" data-field="opening" required min="0">
                            </td>

                            <td>
                                <input type="number" step="0.01" name="pumps[{{ $index }}][closing_index]" 
                                       value="{{ old('pumps.'.$index.'.closing_index', '0.00') }}" 
                                       class="form-control form-control-sm pump-input" 
                                       data-pump-index="{{ $index }}" data-field="closing" required min="0">
                            </td>

                            <td>
                                <input type="number" step="0.01" name="pumps[{{ $index }}][total_return]" 
                                       value="{{ old('pumps.'.$index.'.total_return', '0.00') }}" 
                                       class="form-control form-control-sm pump-input" 
                                       data-pump-index="{{ $index }}" data-field="return" min="0">
                            </td>
                            
                            <td class="text-right calculation-cell">
                                <span id="literage-{{ $index }}">0.00</span> L
                            </td>
                            
                            <td class="text-right calculation-cell odyssee-text-primary">
                                <span id="sales-amount-{{ $index }}">0</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="odyssee-bg-primary">
                            <th colspan="6" class="text-right text-white">Total Récapitulatif du Shift:</th>
                            <th class="text-right text-white"><span id="total-literage">0.00</span> L</th>
                            <th class="text-right text-white"><span id="total-sales-amount">0</span> F CFA</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Dépôt et Contrôle -->
    <div class="card">
        <div class="card-header odyssee-bg-primary">
            <h3 class="card-title text-white">Dépôt et Contrôle</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="cash_deposit_amount">Montant du Versement en Espèces (F CFA)</label>
                    <input type="number" name="cash_deposit_amount" id="cash_deposit_amount" 
                           class="form-control" value="{{ old('cash_deposit_amount', '0') }}" 
                           required min="0" step="1">
                </div>
                <div class="col-md-6">
                    <label>Écart Calculé</label>
                    <div id="total_gap_display" class="h4 font-weight-bold gap-zero">0 F CFA</div>
                    <small class="text-muted">(Versement - Total Ventes)</small>
                    <div id="gap_explanation" class="small mt-1"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dépenses diverses avec fichiers -->
    <div class="card">
        <div class="card-header odyssee-bg-primary">
            <h3 class="card-title text-white">Dépenses Diverses du Shift</h3>
            <button type="button" class="btn btn-sm btn-light float-right" id="add-depense">
                <i class="fas fa-plus"></i> Ajouter une dépense
            </button>
        </div>
        <div class="card-body">
            <div id="depenses-container">
                <!-- Les dépenses seront ajoutées ici dynamiquement -->
            </div>
            
            <template id="depense-template">
                <div class="depense-item border p-3 mb-2">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Type de dépense</label>
                            <select name="depenses[][type]" class="form-control form-control-sm">
                                <option value="carburant_vehicule">Carburant Véhicule</option>
                                <option value="nourriture">Nourriture</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="achat_divers">Achat Divers</option>
                                <option value="frais_transport">Frais de Transport</option>
                                <option value="autres">Autres</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Montant (F CFA)</label>
                            <input type="number" name="depenses[][montant]" class="form-control form-control-sm" min="0" step="1" value="0">
                        </div>
                        <div class="col-md-5">
                            <label>Description</label>
                            <input type="text" name="depenses[][description]" class="form-control form-control-sm" placeholder="Ex: Achat d'huile moteur">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <label>Fichier Justificatif</label>
                            <div class="input-group input-group-sm">
                                <div class="custom-file">
                                    <input type="file" name="depenses[][justificatif_file]" 
                                           class="custom-file-input depense-file" 
                                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
                                    <label class="custom-file-label">Choisir fichier</label>
                                </div>
                            </div>
                            <small class="text-muted">PDF, images, Excel (max 5MB)</small>
                            <div class="preview-container d-none mt-1">
                                <small class="text-success"><i class="fas fa-check-circle"></i> Fichier sélectionné: <span class="file-name"></span></small>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger mt-2 remove-depense">
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                </div>
            </template>
            
            <div class="row mt-3">
                <div class="col-md-6">
                    <label>Total Dépenses</label>
                    <div class="h4" id="total-depenses">0 F CFA</div>
                </div>
                <div class="col-md-6">
                    <label>Écart avec Dépenses</label>
                    <div class="h4 font-weight-bold" id="ecart-avec-depenses">0 F CFA</div>
                    <small>(Versement - Ventes - Dépenses)</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bouton de soumission -->
    <div class="row">
        <div class="col-12">
            <button type="submit" class="btn odyssee-btn-primary btn-lg btn-block">
                <i class="fas fa-save"></i> Enregistrer la Saisie du Shift
            </button>
        </div>
    </div>
</form>
@endsection

@push('styles')
<style>
    .calculation-cell {
        background-color: #f8f9fa;
        font-weight: bold;
    }

    .gap-positive {
        color: #28a745 !important;
        font-weight: bold;
    }

    .gap-negative {
        color: #dc3545 !important;
        font-weight: bold;
    }

    .gap-zero {
        color: #6c757d !important;
        font-weight: bold;
    }
    
    .depense-item {
        background-color: #f8f9fa;
        border-radius: 5px;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        const pumpsData = @json($pumps);
        let depenseCount = 0;

        // Fonctions utilitaires
        function formatNumber(number, decimals = 0) {
            return new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }).format(number);
        }

        // Gestion de l'affichage du nom de fichier
        function updateFileName(input) {
            const fileName = input.files[0] ? input.files[0].name : 'Choisir fichier';
            const label = input.nextElementSibling;
            label.textContent = fileName;
            
            const previewContainer = input.closest('.depense-item').querySelector('.preview-container');
            const fileNameSpan = previewContainer.querySelector('.file-name');
            
            if (input.files[0]) {
                fileNameSpan.textContent = input.files[0].name;
                previewContainer.classList.remove('d-none');
            } else {
                previewContainer.classList.add('d-none');
            }
        }

        // Gestion des dépenses avec fichiers
        $('#add-depense').click(function() {
            const template = $('#depense-template').html();
            const html = template.replace(/\[\]/g, `[${depenseCount}]`);
            $('#depenses-container').append(html);
            depenseCount++;
            calculateTotalDepenses();
        });

        // Événement pour mettre à jour le nom du fichier
        $(document).on('change', '.depense-file', function() {
            updateFileName(this);
            
            // Validation de la taille du fichier (5MB max)
            const file = this.files[0];
            if (file) {
                const maxSize = 5 * 1024 * 1024; // 5MB en bytes
                if (file.size > maxSize) {
                    alert('Le fichier est trop volumineux. Taille max: 5 MB');
                    this.value = '';
                    updateFileName(this);
                }
            }
        });

        // Calcul pour une pompe spécifique
        function calculatePumpSales(pumpIndex) {
            const opening = parseFloat($(`input[data-pump-index="${pumpIndex}"][data-field="opening"]`).val()) || 0;
            const closing = parseFloat($(`input[data-pump-index="${pumpIndex}"][data-field="closing"]`).val()) || 0;
            const returnVal = parseFloat($(`input[data-pump-index="${pumpIndex}"][data-field="return"]`).val()) || 0;
            const unitPrice = parseFloat(pumpsData[pumpIndex].unit_price);
            
            let literageVendu = 0;
            let montantVentes = 0;

            if (closing >= opening) {
                literageVendu = (closing - opening) - returnVal;
                if (literageVendu < 0) literageVendu = 0;
            } else {
                literageVendu = 0;
            }
            
            montantVentes = literageVendu * unitPrice;

            $(`#literage-${pumpIndex}`).text(formatNumber(literageVendu, 2));
            $(`#sales-amount-${pumpIndex}`).text(formatNumber(Math.round(montantVentes)));
            
            return {
                literage: literageVendu,
                sales: montantVentes
            };
        }

        // Calcul pour toutes les pompes
        function calculateAllPumps() {
            let totalLiterage = 0;
            let totalSales = 0;

            pumpsData.forEach((pump, index) => {
                const result = calculatePumpSales(index);
                totalLiterage += result.literage;
                totalSales += result.sales;
            });

            $('#total-literage').text(formatNumber(totalLiterage, 2));
            $('#total-sales-amount').text(formatNumber(Math.round(totalSales)));
            
            return {
                totalLiterage: totalLiterage,
                totalSales: Math.round(totalSales)
            };
        }

        // Calcul des dépenses
        function calculateTotalDepenses() {
            let total = 0;
            $('input[name^="depenses"][name$="[montant]"]').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('#total-depenses').text(formatNumber(total) + ' F CFA');
            calculateEcartWithDepenses(total);
            return total;
        }

        // Calcul de l'écart avec dépenses - FORMULE CORRECTE
        function calculateEcartWithDepenses(totalDepenses) {
            const totals = calculateAllPumps();
            const cashDeposit = parseFloat($('#cash_deposit_amount').val()) || 0;
            // FORMULE: Versement - Ventes - Dépenses
            const ecartFinal = cashDeposit - totals.totalSales - totalDepenses;
            
            const $ecartDisplay = $('#ecart-avec-depenses');
            if (ecartFinal > 0) {
                $ecartDisplay.removeClass('text-danger').addClass('text-success')
                    .html('+' + formatNumber(ecartFinal) + ' F CFA');
                $ecartDisplay.next('small').html('<span class="text-success">(Versement > Ventes + Dépenses)</span>');
            } else if (ecartFinal < 0) {
                $ecartDisplay.removeClass('text-success').addClass('text-danger')
                    .html(formatNumber(ecartFinal) + ' F CFA');
                $ecartDisplay.next('small').html('<span class="text-danger">(Versement < Ventes + Dépenses)</span>');
            } else {
                $ecartDisplay.removeClass('text-success text-danger')
                    .html(formatNumber(ecartFinal) + ' F CFA');
                $ecartDisplay.next('small').html('<span class="text-muted">(Équilibre parfait)</span>');
            }
            
            return ecartFinal;
        }

        // Calcul de l'écart principal - FORMULE CORRECTE
        function calculateGap() {
            const totals = calculateAllPumps();
            const cashDeposit = parseFloat($('#cash_deposit_amount').val()) || 0;
            // FORMULE: Versement - Ventes
            const gap = cashDeposit - totals.totalSales;
            
            const $gapDisplay = $('#total_gap_display');
            const $gapExplanation = $('#gap_explanation');
            
            if (gap > 0) {
                $gapDisplay.removeClass('gap-negative gap-zero').addClass('gap-positive').html('+' + formatNumber(gap) + ' F CFA');
                $gapExplanation.html('<span class="text-success">✓ Excédent: Le pompiste a versé plus que les ventes</span>');
            } else if (gap < 0) {
                $gapDisplay.removeClass('gap-positive gap-zero').addClass('gap-negative').html(formatNumber(gap) + ' F CFA');
                $gapExplanation.html('<span class="text-danger">✗ Manquant: Le pompiste a versé moins que les ventes</span>');
            } else {
                $gapDisplay.removeClass('gap-positive gap-negative').addClass('gap-zero').html(formatNumber(gap) + ' F CFA');
                $gapExplanation.html('<span class="text-muted">✓ Équilibre parfait</span>');
            }
            
            // Mettre à jour aussi l'écart avec dépenses
            calculateTotalDepenses();
            
            return gap;
        }

        // Événements
        $(document).on('click', '.remove-depense', function() {
            $(this).closest('.depense-item').remove();
            calculateTotalDepenses();
        });

        $(document).on('input', 'input[name^="depenses"][name$="[montant]"]', function() {
            calculateTotalDepenses();
        });

        $('.pump-input').on('input', calculateGap);
        $('#cash_deposit_amount').on('input', calculateGap);

        // ========== CORRECTION : INITIALISATION AUTOMATIQUE ==========
        
        // 1. Exécuter les calculs immédiatement au chargement
        calculateGap();
        
        // 2. Déclencher manuellement un événement input sur tous les champs
        setTimeout(function() {
            $('.pump-input').trigger('input');
        }, 100);
        
        // 3. Vérifier si des données existent déjà (old())
        @if(old('pumps'))
            // Si des données existent, les calculer
            setTimeout(function() {
                pumpsData.forEach((pump, index) => {
                    const opening = parseFloat($(`input[name="pumps[${index}][opening_index]"]`).val()) || 0;
                    const closing = parseFloat($(`input[name="pumps[${index}][closing_index]"]`).val()) || 0;
                    const returnVal = parseFloat($(`input[name="pumps[${index}][total_return]"]`).val()) || 0;
                    
                    if (opening > 0 || closing > 0) {
                        calculatePumpSales(index);
                    }
                });
                calculateGap();
            }, 200);
        @endif

        // Validation du formulaire
        $('#indexForm').on('submit', function(e) {
            const gap = calculateGap();
            console.log('Soumission avec écart:', gap);
        });
    });
</script>
@endpush