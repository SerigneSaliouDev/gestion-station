@extends('layouts.app')

@section('title', 'Modifier Saisie')
@section('page-title', 'Modifier la Saisie #' . $shift->id)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('manager.index_form') }}">Manager</a></li>
<li class="breadcrumb-item"><a href="{{ route('manager.history') }}">Historique</a></li>
<li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')
<form action="{{ route('manager.saisie.update', $shift->id) }}" method="POST" id="indexForm" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <!-- Informations Générales -->
    <div class="card card-outline card-primary">
        <div class="card-header odyssee-bg-primary">
            <h3 class="card-title">Informations Générales du Shift</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="shift_date">Date du Shift</label>
                    <input type="date" name="shift_date" id="shift_date" class="form-control" 
                           value="{{ old('shift_date', $shift->date_shift->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4 form-group">
                    <label for="shift_time">Shift</label>
                    <input type="text" name="shift_time" id="shift_time" class="form-control" 
                           value="{{ old('shift_time', $shift->shift) }}" placeholder="Ex: 8H-18H ou 18H-8H" required>
                </div>
                <div class="col-md-4 form-group">
                    <label for="responsible_name">Nom du Responsable</label>
                    <input type="text" name="responsible_name" id="responsible_name" class="form-control" 
                           value="{{ old('responsible_name', $shift->responsable) }}" placeholder="Nom du pompiste" required>
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
                            <th>Prix Unitaire (F CFA)</th>
                            <th>Index d'Ouverture</th>
                            <th>Index de Fermeture</th>
                            <th>Retour (Litres)</th>
                            <th>Litrage Vendu (L)</th>
                            <th>Montant des Ventes (F CFA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pumps as $index => $pump)
                        @php
                            $detail = $shift->pompeDetails->where('pompe_nom', $pump['name'])->first();
                        @endphp
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
                                       value="{{ old('pumps.'.$index.'.opening_index', $detail ? $detail->index_ouverture : '0.00') }}" 
                                       class="form-control form-control-sm pump-input" 
                                       data-pump-index="{{ $index }}" data-field="opening" required min="0">
                            </td>

                            <td>
                                <input type="number" step="0.01" name="pumps[{{ $index }}][closing_index]" 
                                       value="{{ old('pumps.'.$index.'.closing_index', $detail ? $detail->index_fermeture : '0.00') }}" 
                                       class="form-control form-control-sm pump-input" 
                                       data-pump-index="{{ $index }}" data-field="closing" required min="0">
                            </td>

                            <td>
                                <input type="number" step="0.01" name="pumps[{{ $index }}][total_return]" 
                                       value="{{ old('pumps.'.$index.'.total_return', $detail ? $detail->retour_litres : '0.00') }}" 
                                       class="form-control form-control-sm pump-input" 
                                       data-pump-index="{{ $index }}" data-field="return" min="0">
                            </td>
                            
                            <td class="text-right calculation-cell">
                                <span id="literage-{{ $index }}">{{ $detail ? number_format($detail->litrage_vendu, 2, ',', ' ') : '0.00' }}</span> L
                            </td>
                            
                            <td class="text-right calculation-cell odyssee-text-primary">
                                <span id="sales-amount-{{ $index }}">{{ $detail ? number_format($detail->montant_ventes, 0, ',', ' ') : '0' }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="odyssee-bg-primary">
                            <th colspan="6" class="text-right text-white">Total Récapitulatif du Shift:</th>
                            <th class="text-right text-white"><span id="total-literage">{{ number_format($shift->total_litres, 2, ',', ' ') }}</span> L</th>
                            <th class="text-right text-white"><span id="total-sales-amount">{{ number_format($shift->total_ventes, 0, ',', ' ') }}</span> F CFA</th>
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
                           class="form-control" value="{{ old('cash_deposit_amount', $shift->versement) }}" 
                           required min="0" step="1">
                </div>
                <div class="col-md-6">
                    <label>Écart Calculé</label>
                    <div id="total_gap_display" class="h4 font-weight-bold gap-zero">{{ number_format($shift->ecart, 0, ',', ' ') }} F CFA</div>
                    <small class="text-muted">(Montant Ventes Total - Versement)</small>
                    <div id="gap_explanation" class="small mt-1"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dépenses existantes -->
    @if($shift->depenses->count() > 0)
    <div class="card">
        <div class="card-header odyssee-bg-primary">
            <h3 class="card-title text-white">Dépenses Existantes</h3>
        </div>
        <div class="card-body">
            <div id="existing-depenses">
                @foreach($shift->depenses as $depIndex => $depense)
                <div class="depense-item border p-3 mb-2">
                    <input type="hidden" name="existing_depenses[{{ $depIndex }}][id]" value="{{ $depense->id }}">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Type de dépense</label>
                            <select name="existing_depenses[{{ $depIndex }}][type]" class="form-control form-control-sm">
                                <option value="carburant_vehicule" {{ $depense->type_depense == 'carburant_vehicule' ? 'selected' : '' }}>Carburant Véhicule</option>
                                <option value="nourriture" {{ $depense->type_depense == 'nourriture' ? 'selected' : '' }}>Nourriture</option>
                                <option value="maintenance" {{ $depense->type_depense == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="achat_divers" {{ $depense->type_depense == 'achat_divers' ? 'selected' : '' }}>Achat Divers</option>
                                <option value="frais_transport" {{ $depense->type_depense == 'frais_transport' ? 'selected' : '' }}>Frais de Transport</option>
                                <option value="autres" {{ $depense->type_depense == 'autres' ? 'selected' : '' }}>Autres</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Montant (F CFA)</label>
                            <input type="number" name="existing_depenses[{{ $depIndex }}][montant]" 
                                   class="form-control form-control-sm" min="0" step="1" 
                                   value="{{ old('existing_depenses.'.$depIndex.'.montant', $depense->montant) }}">
                        </div>
                        <div class="col-md-3">
                            <label>Description</label>
                            <input type="text" name="existing_depenses[{{ $depIndex }}][description]" 
                                   class="form-control form-control-sm" 
                                   value="{{ old('existing_depenses.'.$depIndex.'.description', $depense->description) }}">
                        </div>
                        <div class="col-md-2">
                            <label>Supprimer</label>
                            <div>
                                <input type="checkbox" name="existing_depenses[{{ $depIndex }}][delete]" 
                                       class="delete-depense-checkbox" value="1">
                                <label class="text-danger ml-1">Supprimer</label>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <label>Fichier Justificatif</label>
                            @if($depense->justificatif_file_path)
                            <div class="mb-2">
                                <span class="badge badge-success">
                                    <i class="fas fa-paperclip"></i> 
                                    {{ $depense->justificatif_file_name }}
                                </span>
                                <a href="{{ Storage::url($depense->justificatif_file_path) }}" 
                                   target="_blank" class="btn btn-sm btn-info ml-2">
                                    <i class="fas fa-eye"></i> Voir
                                </a>
                            </div>
                            @endif
                            <div class="input-group input-group-sm">
                                <div class="custom-file">
                                    <input type="file" name="existing_depenses[{{ $depIndex }}][justificatif_file]" 
                                           class="custom-file-input depense-file">
                                    <label class="custom-file-label">Changer le fichier...</label>
                                </div>
                            </div>
                            <small class="text-muted">Laissez vide pour garder le fichier existant</small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Nouvelles dépenses -->
    <div class="card">
        <div class="card-header odyssee-bg-primary">
            <h3 class="card-title text-white">Ajouter de Nouvelles Dépenses</h3>
            <button type="button" class="btn btn-sm btn-light float-right" id="add-depense">
                <i class="fas fa-plus"></i> Ajouter une dépense
            </button>
        </div>
        <div class="card-body">
            <div id="depenses-container">
                <!-- Les nouvelles dépenses seront ajoutées ici dynamiquement -->
            </div>
            
            <template id="depense-template">
                <div class="depense-item border p-3 mb-2">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Type de dépense</label>
                            <select name="new_depenses[][type]" class="form-control form-control-sm">
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
                            <input type="number" name="new_depenses[][montant]" class="form-control form-control-sm" min="0" step="1" value="0">
                        </div>
                        <div class="col-md-3">
                            <label>Description</label>
                            <input type="text" name="new_depenses[][description]" class="form-control form-control-sm" placeholder="Ex: Achat d'huile moteur">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-danger mt-4 remove-depense">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <label>Fichier Justificatif</label>
                            <div class="input-group input-group-sm">
                                <div class="custom-file">
                                    <input type="file" name="new_depenses[][justificatif_file]" 
                                           class="custom-file-input new-depense-file" 
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
                </div>
            </template>
            
            <div class="row mt-3">
                <div class="col-md-6">
                    <label>Total Dépenses (calculé)</label>
                    <div class="h4" id="total-depenses">{{ number_format($shift->total_depenses, 0, ',', ' ') }} F CFA</div>
                </div>
                <div class="col-md-6">
                    <label>Écart avec Dépenses</label>
                    <div class="h4 font-weight-bold" id="ecart-avec-depenses">
                        @php
                            $ecartFinal = $shift->total_ventes - $shift->versement - $shift->total_depenses;
                        @endphp
                        @if($ecartFinal > 0)
                            +{{ number_format($ecartFinal, 0, ',', ' ') }} F CFA
                        @else
                            {{ number_format($ecartFinal, 0, ',', ' ') }} F CFA
                        @endif
                    </div>
                    <small>(Ventes - Versement - Dépenses)</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Visualisation des fichiers existants -->
    @if($shift->depenses->count() > 0)
    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Fichiers Justificatifs Existants</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($shift->depenses as $depense)
                    @if($depense->justificatif_file_path)
                        @php
                            $extension = strtolower(pathinfo($depense->justificatif_file_name, PATHINFO_EXTENSION));
                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                            $fileUrl = Storage::url($depense->justificatif_file_path);
                        @endphp
                        
                        <div class="col-md-3 mb-3">
                            <div class="card file-card">
                                <div class="card-body text-center">
                                    @if($isImage)
                                        <img src="{{ asset($fileUrl) }}" 
                                             alt="Justificatif" 
                                             class="img-fluid mb-2" 
                                             style="max-height: 100px;">
                                    @else
                                        <i class="fas fa-file fa-3x text-secondary mb-2"></i>
                                    @endif
                                    <h6 class="text-truncate" title="{{ $depense->justificatif_file_name }}">
                                        {{ \Illuminate\Support\Str::limit($depense->justificatif_file_name, 20) }}
                                    </h6>
                                    <small class="text-muted">{{ strtoupper($extension) }}</small>
                                    <br>
                                    <a href="{{ asset($fileUrl) }}" 
                                       target="_blank" 
                                       class="btn btn-sm btn-info mt-2">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Boutons -->
    <div class="row mt-4">
        <div class="col-6">
            <a href="{{ route('manager.history') }}" class="btn btn-secondary btn-lg btn-block">
                <i class="fas fa-arrow-left"></i> Annuler
            </a>
        </div>
        <div class="col-6">
            <button type="submit" class="btn odyssee-btn-primary btn-lg btn-block">
                <i class="fas fa-save"></i> Mettre à jour
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
    
    .delete-depense-checkbox {
        transform: scale(1.2);
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        const pumpsData = @json($pumps);
        let newDepenseCount = 0;

        // Fonctions utilitaires
        function formatNumber(number, decimals = 0) {
            return new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }).format(number);
        }

        // Gestion de l'affichage du nom de fichier pour les nouvelles dépenses
        function updateFileName(input) {
            const fileName = input.files[0] ? input.files[0].name : 'Choisir fichier';
            const label = input.nextElementSibling;
            label.textContent = fileName;
            
            const previewContainer = input.closest('.depense-item').querySelector('.preview-container');
            if (previewContainer) {
                const fileNameSpan = previewContainer.querySelector('.file-name');
                
                if (input.files[0]) {
                    fileNameSpan.textContent = input.files[0].name;
                    previewContainer.classList.remove('d-none');
                } else {
                    previewContainer.classList.add('d-none');
                }
            }
        }

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

        // Calcul des dépenses totales
        function calculateTotalDepenses() {
            let total = 0;
            
            // Dépenses existantes (non supprimées)
            $('input[name^="existing_depenses"][name$="[montant]"]').each(function() {
                const index = $(this).attr('name').match(/\[(\d+)\]/)[1];
                const deleteChecked = $(`input[name="existing_depenses[${index}][delete]"]`).is(':checked');
                if (!deleteChecked) {
                    total += parseFloat($(this).val()) || 0;
                }
            });
            
            // Nouvelles dépenses
            $('input[name^="new_depenses"][name$="[montant]"]').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            
            $('#total-depenses').text(formatNumber(total) + ' F CFA');
            calculateEcartWithDepenses(total);
            return total;
        }

        // Calcul de l'écart avec dépenses
        function calculateEcartWithDepenses(totalDepenses) {
            const totals = calculateAllPumps();
            const cashDeposit = parseFloat($('#cash_deposit_amount').val()) || 0;
            const ecart = totals.totalSales - cashDeposit - totalDepenses;
            
            const $ecartDisplay = $('#ecart-avec-depenses');
            if (ecart > 0) {
                $ecartDisplay.removeClass('text-danger').addClass('text-success')
                    .html('+' + formatNumber(ecart) + ' F CFA');
            } else if (ecart < 0) {
                $ecartDisplay.removeClass('text-success').addClass('text-danger')
                    .html(formatNumber(ecart) + ' F CFA');
            } else {
                $ecartDisplay.removeClass('text-success text-danger')
                    .html(formatNumber(ecart) + ' F CFA');
            }
            
            return ecart;
        }

        // Calcul de l'écart principal
        function calculateGap() {
            const totals = calculateAllPumps();
            const cashDeposit = parseFloat($('#cash_deposit_amount').val()) || 0;
            const gap = totals.totalSales - cashDeposit;
            
            const $gapDisplay = $('#total_gap_display');
            const $gapExplanation = $('#gap_explanation');
            
            if (gap > 0) {
                $gapDisplay.removeClass('gap-negative gap-zero').addClass('gap-positive').html('+' + formatNumber(gap) + ' F CFA');
                $gapExplanation.html('<span class="text-success">✓ Excédent: Les ventes dépassent le versement</span>');
            } else if (gap < 0) {
                $gapDisplay.removeClass('gap-positive gap-zero').addClass('gap-negative').html(formatNumber(gap) + ' F CFA');
                $gapExplanation.html('<span class="text-danger">✗ Manquant: Le versement dépasse les ventes</span>');
            } else {
                $gapDisplay.removeClass('gap-positive gap-negative').addClass('gap-zero').html(formatNumber(gap) + ' F CFA');
                $gapExplanation.html('<span class="text-muted">✓ Équilibre parfait</span>');
            }
            
            // Mettre à jour aussi l'écart avec dépenses
            calculateTotalDepenses();
            
            return gap;
        }

        // Gestion des nouvelles dépenses
        $('#add-depense').click(function() {
            const template = $('#depense-template').html();
            const html = template.replace(/\[\]/g, `[${newDepenseCount}]`);
            $('#depenses-container').append(html);
            newDepenseCount++;
            calculateTotalDepenses();
        });

        // Événements
        $(document).on('change', '.new-depense-file, .depense-file', function() {
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

        $(document).on('click', '.remove-depense', function() {
            $(this).closest('.depense-item').remove();
            calculateTotalDepenses();
        });

        $(document).on('input', 'input[name^="existing_depenses"][name$="[montant]"], input[name^="new_depenses"][name$="[montant]"]', function() {
            calculateTotalDepenses();
        });

        $(document).on('change', '.delete-depense-checkbox', function() {
            calculateTotalDepenses();
        });

        $('.pump-input').on('input', calculateGap);
        $('#cash_deposit_amount').on('input', calculateGap);

        // Initialisation
        calculateGap();
    });
</script>
@endpush