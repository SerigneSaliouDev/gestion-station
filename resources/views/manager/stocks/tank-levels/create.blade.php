@extends('layouts.app')

@section('title', 'Nouveau Jaugeage de Cuve')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('manager.index_form') }}">Tableau de Bord</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('manager.stocks.dashboard') }}">Gestion des Stocks</a></li>
                    <li class="breadcrumb-item active">Nouveau Jaugeage</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-ruler-vertical"></i> Nouveau Jaugeage de Cuve
                    </h5>
                </div>
                <div class="card-body">
                    <form id="tankLevelForm" method="POST" action="{{ route('manager.stocks.tank-levels.store') }}">
                        @csrf

                        <div class="row">
                            <!-- Informations Générales -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Informations Générales</h6>
                                    </div>
                                    <div class="card-body">
                                        <!-- Date de Mesure -->
                                        <div class="form-group">
                                            <label for="measurement_date" class="form-label required">
                                                Date et Heure de Mesure
                                            </label>
                                            <input type="datetime-local" 
                                                   class="form-control @error('measurement_date') is-invalid @enderror" 
                                                   id="measurement_date" 
                                                   name="measurement_date" 
                                                   value="{{ old('measurement_date', now()->format('Y-m-d\TH:i')) }}" 
                                                   required>
                                            @error('measurement_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Sélection de la Cuve -->
                                        <div class="form-group">
                                            <label for="tank_number" class="form-label required">
                                                Cuve à Jauger
                                            </label>
                                            <select class="form-control @error('tank_number') is-invalid @enderror" 
                                                    id="tank_number" 
                                                    name="tank_number" 
                                                    required>
                                                <option value="">Sélectionnez une cuve</option>
                                                @foreach($tanks as $tankNumber => $tankDetails)
                                                    <option value="{{ $tankNumber }}" 
                                                            data-fuel-type="{{ $tankDetails['fuel_type'] }}"
                                                            data-capacity="{{ $tankDetails['capacity'] }}"
                                                            data-theoretical-stock="{{ $tankDetails['theoretical_stock'] ?? 0 }}"
                                                            {{ old('tank_number') == $tankNumber ? 'selected' : '' }}>
                                                        {{ $tankDetails['description'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('tank_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Informations sur la Cuve Sélectionnée -->
                                        <div id="tankInfo" class="alert alert-info mt-3" style="display: none;">
                                            <div class="row">
                                                <div class="col-6">
                                                    <strong>Type de Carburant:</strong>
                                                    <span id="tankFuelType">-</span>
                                                </div>
                                                <div class="col-6">
                                                    <strong>Capacité:</strong>
                                                    <span id="tankCapacity">-</span> L
                                                </div>
                                                <div class="col-12 mt-2">
                                                    <strong>Stock Théorique Actuel:</strong>
                                                    <span id="tankTheoreticalStock">-</span> L
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mesures Physiques -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Mesures Physiques</h6>
                                    </div>
                                    <div class="card-body">
                                        <!-- Niveau en cm -->
                                        <div class="form-group">
                                            <label for="level_cm" class="form-label required">
                                                Niveau Mesuré (cm)
                                            </label>
                                            <div class="input-group">
                                                <input type="number" 
                                                       step="0.1" 
                                                       class="form-control @error('level_cm') is-invalid @enderror" 
                                                       id="level_cm" 
                                                       name="level_cm" 
                                                       value="{{ old('level_cm') }}" 
                                                       min="0"
                                                       required>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">cm</span>
                                                </div>
                                            </div>
                                            @error('level_cm')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                Mesure prise avec la jauge graduée
                                            </small>
                                        </div>

                                        <!-- Température -->
                                        <div class="form-group">
                                            <label for="temperature_c" class="form-label">
                                                Température (°C)
                                            </label>
                                            <div class="input-group">
                                                <input type="number" 
                                                       step="0.1" 
                                                       class="form-control @error('temperature_c') is-invalid @enderror" 
                                                       id="temperature_c" 
                                                       name="temperature_c" 
                                                       value="{{ old('temperature_c', 20) }}"
                                                       min="-50" 
                                                       max="100">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">°C</span>
                                                </div>
                                            </div>
                                            @error('temperature_c')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                Température ambiante par défaut: 20°C
                                            </small>
                                        </div>

                                        <!-- Stock Théorique (non modifiable) -->
                                        <div class="form-group">
                                            <label for="theoretical_stock" class="form-label required">
                                                Stock Théorique (L)
                                            </label>
                                            <div class="input-group">
                                                <input type="number" 
                                                       step="0.01" 
                                                       class="form-control bg-light @error('theoretical_stock') is-invalid @enderror" 
                                                       id="theoretical_stock" 
                                                       name="theoretical_stock" 
                                                       value="{{ old('theoretical_stock') }}" 
                                                       readonly
                                                       required>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">L</span>
                                                </div>
                                            </div>
                                            @error('theoretical_stock')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                Calculé automatiquement à partir du stock théorique de la cuve
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Résultats et Observations -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Observations</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="observations" class="form-label">
                                                Observations
                                            </label>
                                            <textarea class="form-control @error('observations') is-invalid @enderror" 
                                                      id="observations" 
                                                      name="observations" 
                                                      rows="3">{{ old('observations') }}</textarea>
                                            @error('observations')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                Notez toute observation particulière (fuite, problème de mesure, etc.)
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Résultats Calculés</h6>
                                    </div>
                                    <div class="card-body">
                                        <!-- Volume Calculé -->
                                        <div class="form-group mb-3">
                                            <label class="form-label">Volume Calculé</label>
                                            <div class="input-group">
                                                <input type="text" 
                                                       class="form-control bg-light" 
                                                       id="calculated_volume" 
                                                       value="0" 
                                                       readonly>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">L</span>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted">
                                                Volume déduit du niveau mesuré
                                            </small>
                                        </div>

                                        <!-- Différence -->
                                        <div class="form-group">
                                            <label class="form-label">Écart Estimé</label>
                                            <div class="alert" id="differenceAlert">
                                                <div class="text-center">
                                                    <h4 id="differencePercentage">0%</h4>
                                                    <span id="differenceVolume">0 L</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Boutons d'Action -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('manager.stocks.dashboard') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Retour
                                    </a>
                                    
                                    <div>
                                        <button type="button" class="btn btn-info" id="calculateBtn">
                                            <i class="fas fa-calculator"></i> Calculer
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Enregistrer le Jaugeage
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Message de succès/erreur -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show position-fixed" 
         style="top: 20px; right: 20px; z-index: 1000;" 
         role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show position-fixed" 
         style="top: 20px; right: 20px; z-index: 1000;" 
         role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('alert'))
    <div class="alert alert-warning alert-dismissible fade show position-fixed" 
         style="top: 20px; right: 20px; z-index: 1000;" 
         role="alert">
        {{ session('alert') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
@endsection

@push('styles')
<style>
    .required:after {
        content: " *";
        color: #dc3545;
    }
    
    #differenceAlert {
        padding: 10px;
        border-radius: 5px;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .difference-success {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }
    
    .difference-warning {
        background-color: #fff3cd;
        border: 1px solid #ffeaa7;
        color: #856404;
    }
    
    .difference-danger {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }
    
    .bg-light {
        background-color: #f8f9fa !important;
    }
    
    .form-control:read-only {
        background-color: #e9ecef !important;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let currentTankCapacity = 0;
    let currentTheoreticalStock = 0;

    // Éléments du DOM
    const tankNumberSelect = document.getElementById('tank_number');
    const levelCmInput = document.getElementById('level_cm');
    const theoreticalStockInput = document.getElementById('theoretical_stock');
    const tankInfoDiv = document.getElementById('tankInfo');
    const tankFuelTypeSpan = document.getElementById('tankFuelType');
    const tankCapacitySpan = document.getElementById('tankCapacity');
    const tankTheoreticalStockSpan = document.getElementById('tankTheoreticalStock');
    const calculatedVolumeInput = document.getElementById('calculated_volume');
    const differenceAlertDiv = document.getElementById('differenceAlert');
    const differencePercentageSpan = document.getElementById('differencePercentage');
    const differenceVolumeSpan = document.getElementById('differenceVolume');
    const calculateBtn = document.getElementById('calculateBtn');
    const tankLevelForm = document.getElementById('tankLevelForm');

    // Mettre à jour les informations de la cuve sélectionnée
    tankNumberSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (selectedOption.value) {
            const fuelType = selectedOption.getAttribute('data-fuel-type');
            const capacity = parseFloat(selectedOption.getAttribute('data-capacity'));
            const theoreticalStock = parseFloat(selectedOption.getAttribute('data-theoretical-stock'));
            
            // Mettre à jour les variables globales
            currentTankCapacity = capacity;
            currentTheoreticalStock = theoreticalStock;
            
            // Afficher les informations de la cuve
            tankFuelTypeSpan.textContent = fuelType.toUpperCase();
            tankCapacitySpan.textContent = capacity.toLocaleString('fr-FR');
            tankTheoreticalStockSpan.textContent = theoreticalStock.toLocaleString('fr-FR', { 
                minimumFractionDigits: 2,
                maximumFractionDigits: 2 
            });
            
            // Mettre à jour le champ stock théorique
            theoreticalStockInput.value = theoreticalStock;
            
            // Afficher le panneau d'information
            tankInfoDiv.style.display = 'block';
            
            // Calculer automatiquement si un niveau est déjà saisi
            if (levelCmInput.value) {
                calculateResults();
            }
        } else {
            tankInfoDiv.style.display = 'none';
            theoreticalStockInput.value = '';
        }
    });

    // Calculer les résultats
    calculateBtn.addEventListener('click', calculateResults);

    // Calculer automatiquement quand le niveau change
    levelCmInput.addEventListener('input', function() {
        if (tankNumberSelect.value) {
            calculateResults();
        }
    });

    // Fonction de calcul
    function calculateResults() {
        const levelCm = parseFloat(levelCmInput.value) || 0;
        const theoreticalStock = parseFloat(theoreticalStockInput.value) || 0;
        
        // Calculer le volume à partir du niveau
        // Formule simplifiée: volume = (niveau / hauteur_max) * capacité
        // On suppose une hauteur maximale de 200 cm pour toutes les cuves
        const maxHeight = 200;
        const calculatedVolume = (levelCm / maxHeight) * currentTankCapacity;
        
        // Mettre à jour le champ volume calculé
        calculatedVolumeInput.value = calculatedVolume.toFixed(2);
        
        // Calculer la différence
        const difference = calculatedVolume - theoreticalStock;
        const differencePercentage = theoreticalStock > 0 
            ? (difference / theoreticalStock) * 100 
            : 0;
        
        // Mettre à jour l'affichage de la différence
        differencePercentageSpan.textContent = differencePercentage.toFixed(2) + '%';
        differenceVolumeSpan.textContent = difference.toFixed(2) + ' L';
        
        // Changer la couleur en fonction de l'écart
        differenceAlertDiv.className = 'alert';
        
        if (Math.abs(differencePercentage) <= 1) {
            differenceAlertDiv.classList.add('difference-success');
        } else if (Math.abs(differencePercentage) <= 2) {
            differenceAlertDiv.classList.add('difference-warning');
        } else {
            differenceAlertDiv.classList.add('difference-danger');
        }
    }

    // Validation du formulaire avant soumission
    tankLevelForm.addEventListener('submit', function(e) {
        const levelCm = parseFloat(levelCmInput.value);
        const theoreticalStock = parseFloat(theoreticalStockInput.value);
        
        if (!levelCm || levelCm <= 0) {
            e.preventDefault();
            alert('Veuillez saisir un niveau valide (supérieur à 0)');
            levelCmInput.focus();
            return false;
        }
        
        if (!theoreticalStock || theoreticalStock < 0) {
            e.preventDefault();
            alert('Le stock théorique doit être supérieur ou égal à 0');
            return false;
        }
        
        // Vérifier si une cuve est sélectionnée
        if (!tankNumberSelect.value) {
            e.preventDefault();
            alert('Veuillez sélectionner une cuve');
            tankNumberSelect.focus();
            return false;
        }
        
        // Vérification des écarts importants
        const diffPercentage = parseFloat(differencePercentageSpan.textContent.replace('%', ''));
        if (Math.abs(diffPercentage) > 2) {
            if (!confirm(`Attention: L'écart de stock est de ${diffPercentage.toFixed(2)}%.\nVoulez-vous vraiment enregistrer ce jaugeage ?`)) {
                e.preventDefault();
                return false;
            }
        }
        
        return true;
    });

    // Initialiser le calcul si des valeurs existent déjà
    if (tankNumberSelect.value) {
        tankNumberSelect.dispatchEvent(new Event('change'));
    }

    // Auto-dismiss des alertes après 5 secondes
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert:not(#differenceAlert)');
        alerts.forEach(function(alert) {
            const closeBtn = alert.querySelector('.close');
            if (closeBtn) {
                closeBtn.click();
            }
        });
    }, 5000);
});
</script>
@endpush