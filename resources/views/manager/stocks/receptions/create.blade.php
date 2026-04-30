@extends('layouts.app')

@section('title', 'Réception de carburant')
@section('page-title', 'Réception de stock')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('manager.stocks.dashboard') }}">Tableau de bord</a></li>
<li class="breadcrumb-item active">Réception</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #333, #555); color: white; border-bottom: 3px solid #FF7F00;">
                <h3 class="card-title">
                    <i class="fas fa-truck-loading mr-2"></i>Formulaire de réception
                </h3>
                <div class="card-tools">
                    <span class="badge" style="background-color: #FF7F00;">ODYSSEE ENERGIE SA</span>
                </div>
            </div>
            
            <div class="card-body">
                @if ($errors->any())
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> Erreur de validation</h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fas fa-check"></i> {{ session('success') }}
                </div>
                @endif
                
                @php
                    $fuelTypes = $fuelTypes ?? ($tanks->groupBy('fuel_type') ?? collect());
                @endphp
                
                @if($tanks->isEmpty())
                <div class="alert alert-warning">
                    <i class="icon fas fa-exclamation-triangle"></i>
                    Aucune cuve configurée. 
                    <a href="{{ route('manager.tanks.create') }}" class="alert-link">
                        Créez d'abord des cuves
                    </a>.
                </div>
                @else
                <form method="POST" action="{{ route('manager.stocks.receptions.store') }}">
                    @csrf
                    
                    <!-- Champ caché pour tank_number -->
                    <input type="hidden" id="tank_number" name="tank_number" value="{{ old('tank_number') }}">
                    
                    <!-- Section 1: Informations produit -->
                    <div class="card card-outline card-primary mb-4">
                        <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #FF7F00;">
                            <h4 class="card-title">
                                <i class="fas fa-gas-pump mr-2" style="color: #FF7F00;"></i>1. Informations produit
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fuel_type">Type de carburant *</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="fas fa-gas-pump"></i>
                                                </span>
                                            </div>
                                            <select class="form-control @error('fuel_type') is-invalid @enderror" 
                                                    id="fuel_type" name="fuel_type" required>
                                                <option value="">Sélectionnez...</option>
                                                @foreach($fuelTypes as $fuelType => $typeTanks)
                                                <option value="{{ $fuelType }}" 
                                                    {{ old('fuel_type') == $fuelType ? 'selected' : '' }}>
                                                    {{ strtoupper($fuelType) }} 
                                                    <span class="badge badge-info ml-2">{{ count($typeTanks) }} cuve(s)</span>
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('fuel_type')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tank_id">Cuve de destination *</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="fas fa-oil-can"></i>
                                                </span>
                                            </div>
                                            <select class="form-control @error('tank_id') is-invalid @enderror" 
                                                    id="tank_id" name="tank_id" required>
                                                <option value="">Sélectionnez d'abord un carburant</option>
                                            </select>
                                        </div>
                                        @error('tank_id')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section 2: Quantité et prix -->
                    <div class="card card-outline card-primary mb-4">
                        <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #FF7F00;">
                            <h4 class="card-title">
                                <i class="fas fa-calculator mr-2" style="color: #FF7F00;"></i>2. Quantité et prix
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="quantity_liters">Quantité (litres) *</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control @error('quantity_liters') is-invalid @enderror" 
                                                   id="quantity_liters" name="quantity_liters" 
                                                   value="{{ old('quantity_liters', 1000) }}"
                                                   required min="100" step="1">
                                            <div class="input-group-append">
                                                <span class="input-group-text">L</span>
                                            </div>
                                        </div>
                                        @error('quantity_liters')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="unit_price">Prix unitaire (FCFA/L) *</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control @error('unit_price') is-invalid @enderror" 
                                                   id="unit_price" name="unit_price" 
                                                   value="{{ old('unit_price', 650) }}"
                                                   required min="0" step="0.01">
                                            <div class="input-group-append">
                                                <span class="input-group-text">FCFA/L</span>
                                            </div>
                                        </div>
                                        @error('unit_price')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="delivery_date">Date de livraison *</label>
                                        <input type="datetime-local" class="form-control @error('delivery_date') is-invalid @enderror" 
                                               id="delivery_date" name="delivery_date" 
                                               value="{{ old('delivery_date', now()->format('Y-m-d\TH:i')) }}"
                                               required>
                                        @error('delivery_date')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Calcul du total -->
                            <div class="alert alert-info" style="border-left: 4px solid #FF7F00;">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <strong style="color: #333;">Montant total estimé :</strong>
                                        <h3 class="mt-2" style="color: #FF7F00;" id="total_amount">0 FCFA</h3>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            Quantité × Prix unitaire
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section 3: Fournisseur -->
                    <div class="card card-outline card-primary mb-4">
                        <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #FF7F00;">
                            <h4 class="card-title">
                                <i class="fas fa-building mr-2" style="color: #FF7F00;"></i>3. Fournisseur et documents
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="supplier">Fournisseur *</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="fas fa-building"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control @error('supplier') is-invalid @enderror" 
                                                   id="supplier" name="supplier" 
                                                   value="{{ old('supplier') }}"
                                                   required placeholder="Ex: ODYSSEE ENERGIE SA">
                                        </div>
                                        @error('supplier')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="invoice_number">N° Facture/Bon *</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="fas fa-file-invoice"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" 
                                                   id="invoice_number" name="invoice_number" 
                                                   value="{{ old('invoice_number', 'FACT-' . date('Ymd-His')) }}"
                                                   required placeholder="Ex: FACT-2024-001">
                                        </div>
                                        @error('invoice_number')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="driver_name">Nom du chauffeur</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="fas fa-user"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control" 
                                                   id="driver_name" name="driver_name" 
                                                   value="{{ old('driver_name') }}"
                                                   placeholder="Nom du chauffeur">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Observations -->
                    <div class="form-group">
                        <label for="notes">Observations</label>
                        <textarea class="form-control" id="notes" name="notes" 
                                  rows="3" placeholder="Notes supplémentaires...">{{ old('notes') }}</textarea>
                    </div>
                    
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('manager.stocks.dashboard') }}" class="btn btn-default">
                                    <i class="fas fa-arrow-left mr-1"></i> Retour
                                </a>
                                <button type="reset" class="btn btn-warning ml-2">
                                    <i class="fas fa-redo mr-1"></i> Réinitialiser
                                </button>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="submit" class="btn" style="background-color: #FF7F00; color: white; border-color: #FF7F00;">
                                    <i class="fas fa-check-circle mr-1"></i> Enregistrer la réception
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card-outline {
    border-top: 3px solid #FF7F00 !important;
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
}

.form-control:focus, 
.form-select:focus {
    border-color: #FF7F00 !important;
    box-shadow: 0 0 0 0.2rem rgba(255, 127, 0, 0.25) !important;
}

.alert-info {
    background-color: #f0f8ff;
    border-color: #b8daff;
}

#total_amount {
    font-weight: bold;
}
</style>
@endpush

@push('scripts')
<script>
$(function () {
    // Données des cuves
    const tanksData = @json($tanks);
    const tanksByFuelType = {};
    
    tanksData.forEach(function(tank) {
        if (!tanksByFuelType[tank.fuel_type]) {
            tanksByFuelType[tank.fuel_type] = [];
        }
        tanksByFuelType[tank.fuel_type].push({
            id: tank.id,
            number: tank.number,
            description: tank.description || 'Cuve ' + tank.number,
            capacity: tank.capacity,
            current_volume: tank.current_volume,
            available_capacity: tank.capacity - tank.current_volume
        });
    });
    
    // Calcul du montant total
    function calculateTotal() {
        const quantity = parseFloat($('#quantity_liters').val()) || 0;
        const unitPrice = parseFloat($('#unit_price').val()) || 0;
        const total = quantity * unitPrice;
        $('#total_amount').text(total.toLocaleString('fr-FR') + ' FCFA');
    }
    
    $('#quantity_liters, #unit_price').on('input', calculateTotal);
    
    // Gestion du changement de carburant
    $('#fuel_type').on('change', function() {
        const fuelType = $(this).val();
        const tankSelect = $('#tank_id');
        
        tankSelect.empty();
        
        if (!fuelType) {
            tankSelect.append('<option value="">Sélectionnez d\'abord un carburant</option>');
            tankSelect.prop('disabled', true);
            return;
        }
        
        tankSelect.prop('disabled', false);
        const availableTanks = tanksByFuelType[fuelType] || [];
        
        if (availableTanks.length > 0) {
            tankSelect.append('<option value="">Choisissez une cuve</option>');
            
            availableTanks.forEach(tank => {
                const availableCapacity = Math.round(tank.available_capacity);
                const status = availableCapacity > 0 ? 
                    ` (${availableCapacity} L disponible)` : 
                    ' (PLEINE)';
                
                const option = new Option(
                    `Cuve ${tank.number} - ${tank.description}${status}`,
                    tank.id
                );
                tankSelect.append(option);
            });
            
            // Re-sélectionner si ancienne valeur
            const oldTankId = "{{ old('tank_id') }}";
            if (oldTankId) {
                tankSelect.val(oldTankId);
            }
        } else {
            tankSelect.append('<option value="">Aucune cuve disponible</option>');
        }
    });
    
    // Gestion du changement de cuve
    $('#tank_id').on('change', function() {
        const tankId = $(this).val();
        const fuelType = $('#fuel_type').val();
        
        // Récupérer et remplir le numéro de cuve
        if (tankId && fuelType && tanksByFuelType[fuelType]) {
            const tank = tanksByFuelType[fuelType].find(t => t.id == tankId);
            if (tank) {
                $('#tank_number').val(tank.number);
                $('#quantity_liters').attr('max', tank.available_capacity);
                
                // Afficher avertissement capacité
                $('.capacity-warning').remove();
                if (tank.available_capacity < 1000) {
                    $('#quantity_liters').closest('.form-group').append(`
                        <div class="alert alert-warning capacity-warning mt-2">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Capacité disponible limitée : ${Math.round(tank.available_capacity)} L
                        </div>
                    `);
                }
            }
        } else {
            $('#tank_number').val('');
        }
    });
    
    // Restaurer les valeurs si erreur de validation
    const oldFuelType = "{{ old('fuel_type') }}";
    if (oldFuelType) {
        $('#fuel_type').val(oldFuelType).trigger('change');
        
        // Si tank_id était aussi sélectionné
        setTimeout(() => {
            const oldTankId = "{{ old('tank_id') }}";
            if (oldTankId) {
                $('#tank_id').val(oldTankId).trigger('change');
            }
        }, 500);
    }
    
    // Pré-sélection si un seul carburant
    if ($('#fuel_type option').length === 2) { // 1 option + placeholder
        $('#fuel_type').val($('#fuel_type option:last').val()).trigger('change');
    }
    
    // Calcul initial
    calculateTotal();
});
</script>
@endpush