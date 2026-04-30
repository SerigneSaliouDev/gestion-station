@extends('layouts.app')

@section('title', 'Enregistrer une vente')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-gas-pump"></i> Enregistrer une vente</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {!! session('success') !!}
                        </div>
                    @endif
                    
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('manager.sales.store') }}" id="saleForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sale_date">Date et heure *</label>
                                    <input type="datetime-local" name="sale_date" id="sale_date" 
                                           class="form-control" value="{{ old('sale_date', now()->format('Y-m-d\TH:i')) }}" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tank_id">Sélectionner la cuve *</label>
                                    <select name="tank_id" id="tank_id" class="form-control" required 
                                            onchange="updateTankInfo()">
                                        <option value="">Choisissez une cuve</option>
                                        @foreach($tanks as $tank)
                                            <option value="{{ $tank->id }}" 
                                                data-current-volume="{{ $tank->current_volume }}"
                                                data-capacity="{{ $tank->capacity }}"
                                                data-number="{{ $tank->number }}"
                                                data-fuel-type="{{ $tank->fuel_type }}">
                                                Cuve {{ $tank->number }} - {{ $tank->display_name }} 
                                                ({{ number_format($tank->current_volume, 0) }} L disponible)
                                            </option>
                                        @endforeach
                                    </select>
                                    <small id="tank-info" class="form-text text-muted"></small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">Quantité (L) *</label>
                                    <input type="number" name="quantity" id="quantity" class="form-control" 
                                           step="0.01" min="0.1" value="{{ old('quantity') }}" 
                                           required oninput="checkStock()" onchange="calculateTotal()">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="unit_price">Prix unitaire (FCFA/L) *</label>
                                    <input type="number" name="unit_price" id="unit_price" class="form-control" 
                                           step="0.01" min="0" value="{{ old('unit_price') }}" 
                                           required oninput="calculateTotal()">
                                </div>
                            </div>
                        </div>
                        
                        <div id="stock-alert" class="alert" style="display: none;"></div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="total_amount">Montant total (FCFA)</label>
                                    <input type="number" name="total_amount" id="total_amount" class="form-control" 
                                           readonly>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_method">Méthode de paiement *</label>
                                    <select name="payment_method" id="payment_method" class="form-control" required>
                                        <option value="">Choisissez</option>
                                        <option value="cash">Espèces</option>
                                        <option value="card">Carte bancaire</option>
                                        <option value="mobile_money">Mobile Money</option>
                                        <option value="credit">Crédit</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_name">Nom du client (optionnel)</label>
                                    <input type="text" name="customer_name" id="customer_name" class="form-control" 
                                           value="{{ old('customer_name') }}">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pump_number">Numéro de pompe (optionnel)</label>
                                    <input type="text" name="pump_number" id="pump_number" class="form-control" 
                                           value="{{ old('pump_number') }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Notes (optionnel)</label>
                            <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check"></i> Enregistrer la vente
                            </button>
                            <a href="{{ route('manager.sales.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-oil-can"></i> État des cuves ({{ $tanks->count() }})</h5>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    @if($tanks->count() > 0)
                        @foreach($tanks as $tank)
                            <div class="mb-3 p-2 border rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Cuve {{ $tank->number }}</strong>
                                        <span class="badge {{ $tank->badge_class }} ml-2">
                                            {{ $tank->display_name }}
                                        </span>
                                    </div>
                                    <span class="badge 
                                        @if($tank->fill_percentage > 90) badge-danger
                                        @elseif($tank->fill_percentage > 75) badge-warning
                                        @elseif($tank->fill_percentage > 25) badge-success
                                        @else badge-info @endif">
                                        {{ number_format($tank->fill_percentage, 1) }}%
                                    </span>
                                </div>
                                <div class="mt-2">
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar 
                                            @if($tank->fill_percentage > 90) bg-danger
                                            @elseif($tank->fill_percentage > 75) bg-warning
                                            @elseif($tank->fill_percentage > 25) bg-success
                                            @else bg-info @endif" 
                                            style="width: {{ $tank->fill_percentage }}%">
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        Stock: <strong>{{ number_format($tank->current_volume, 0) }} L</strong> / 
                                        Capacité: {{ number_format($tank->capacity, 0) }} L
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Aucune cuve avec du stock disponible.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function calculateTotal() {
    const quantity = parseFloat(document.getElementById('quantity').value) || 0;
    const unitPrice = parseFloat(document.getElementById('unit_price').value) || 0;
    const total = quantity * unitPrice;
    document.getElementById('total_amount').value = total.toFixed(2);
}

function updateTankInfo() {
    const tankSelect = document.getElementById('tank_id');
    const selectedOption = tankSelect.options[tankSelect.selectedIndex];
    const tankInfo = document.getElementById('tank-info');
    
    if (selectedOption && selectedOption.value) {
        const currentVolume = parseFloat(selectedOption.getAttribute('data-current-volume')) || 0;
        const capacity = parseFloat(selectedOption.getAttribute('data-capacity')) || 1;
        const tankNumber = selectedOption.getAttribute('data-number');
        const fuelType = selectedOption.getAttribute('data-fuel-type');
        
        const fillPercentage = (currentVolume / capacity) * 100;
        
        tankInfo.innerHTML = `
            <div class="alert alert-info py-2">
                <strong>Cuve ${tankNumber}</strong> - ${fuelType.toUpperCase()}<br>
                <small>
                    Stock disponible: <strong>${currentVolume.toFixed(0)} L</strong><br>
                    Remplissage: ${fillPercentage.toFixed(1)}% (Capacité: ${capacity.toFixed(0)} L)
                </small>
            </div>
        `;
        
        // Vérifier le stock
        const quantity = document.getElementById('quantity').value;
        if (quantity) {
            checkStock();
        }
    } else {
        tankInfo.innerHTML = '';
    }
}

function checkStock() {
    const tankSelect = document.getElementById('tank_id');
    const quantityInput = document.getElementById('quantity');
    const alertDiv = document.getElementById('stock-alert');
    
    const selectedOption = tankSelect.options[tankSelect.selectedIndex];
    
    if (!selectedOption || !selectedOption.value) {
        alertDiv.style.display = 'none';
        return;
    }
    
    const currentVolume = parseFloat(selectedOption.getAttribute('data-current-volume')) || 0;
    const quantity = parseFloat(quantityInput.value);
    const tankNumber = selectedOption.getAttribute('data-number');
    
    if (!quantity || quantity <= 0) {
        alertDiv.style.display = 'none';
        return;
    }
    
    if (quantity > currentVolume) {
        alertDiv.className = 'alert alert-danger';
        alertDiv.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i> 
            <strong>Stock insuffisant!</strong><br>
            Cuve ${tankNumber}: ${currentVolume.toFixed(0)} L disponible<br>
            Vous demandez: ${quantity.toFixed(0)} L
        `;
        alertDiv.style.display = 'block';
    } else {
        const remaining = currentVolume - quantity;
        const capacity = parseFloat(selectedOption.getAttribute('data-capacity')) || 1;
        const fillPercentage = (remaining / capacity) * 100;
        
        alertDiv.className = 'alert alert-success';
        alertDiv.innerHTML = `
            <i class="fas fa-check-circle"></i> 
            <strong>Stock suffisant</strong><br>
            Après vente: ${remaining.toFixed(0)} L restants<br>
            Remplissage: ${fillPercentage.toFixed(1)}%
        `;
        alertDiv.style.display = 'block';
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
    
    const tankSelect = document.getElementById('tank_id');
    if (tankSelect.value) {
        updateTankInfo();
    }
    
    // Validation avant soumission
    document.getElementById('saleForm').addEventListener('submit', function(e) {
        const tankId = document.getElementById('tank_id').value;
        const quantity = parseFloat(document.getElementById('quantity').value);
        const tankSelect = document.getElementById('tank_id');
        const selectedOption = tankSelect.options[tankSelect.selectedIndex];
        const currentVolume = parseFloat(selectedOption.getAttribute('data-current-volume')) || 0;
        
        if (!tankId) {
            e.preventDefault();
            alert('Veuillez sélectionner une cuve.');
            return;
        }
        
        if (quantity > currentVolume) {
            e.preventDefault();
            alert(`ERREUR: Stock insuffisant. Disponible: ${currentVolume.toFixed(0)} L`);
            return;
        }
    });
});
</script>
@endpush