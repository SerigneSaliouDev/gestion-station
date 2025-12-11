@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Enregistrer une vente</h2>
            <p class="text-muted">Enregistrez une vente de carburant</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations de la vente</h5>
                </div>
                <div class="card-body">
                    <form id="saleForm" method="POST" action="{{ route('manager.sales.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sale_date" class="form-label">Date et heure *</label>
                                    <input type="datetime-local" class="form-control" id="sale_date" 
                                           name="sale_date" value="{{ old('sale_date', now()->format('Y-m-d\TH:i')) }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fuel_type" class="form-label">Type de carburant *</label>
                                    <select class="form-control" id="fuel_type" name="fuel_type" required>
                                        <option value="">Sélectionnez un carburant</option>
                                        @foreach($fuelTypes as $key => $name)
                                            <option value="{{ $key }}" 
                                                    data-stock="{{ $currentStocks[$key] ?? 0 }}"
                                                    {{ old('fuel_type') == $key ? 'selected' : '' }}>
                                                {{ $name }} (Stock: {{ number_format($currentStocks[$key] ?? 0, 0) }} L)
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Stock actuel affiché entre parenthèses</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantité (L) *</label>
                                    <input type="number" class="form-control" id="quantity" 
                                           name="quantity" step="0.1" min="0.1" max="10000"
                                           value="{{ old('quantity') }}" required>
                                    <div id="stockCheck" class="mt-1"></div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="unit_price" class="form-label">Prix unitaire (F CFA) *</label>
                                    <input type="number" class="form-control" id="unit_price" 
                                           name="unit_price" step="1" min="0"
                                           value="{{ old('unit_price') }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="total_amount" class="form-label">Montant total (F CFA)</label>
                                    <input type="number" class="form-control" id="total_amount" 
                                           name="total_amount" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">Mode de paiement *</label>
                                    <select class="form-control" id="payment_method" name="payment_method" required>
                                        <option value="">Sélectionnez un mode</option>
                                        @foreach($paymentMethods as $key => $name)
                                            <option value="{{ $key }}" {{ old('payment_method') == $key ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_type" class="form-label">Type de client</label>
                                    <select class="form-control" id="customer_type" name="customer_type">
                                        <option value="">Sélectionnez un type</option>
                                        @foreach($customerTypes as $key => $name)
                                            <option value="{{ $key }}" {{ old('customer_type') == $key ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="pump_number" class="form-label">Numéro de pompe</label>
                                    <input type="text" class="form-control" id="pump_number" 
                                           name="pump_number" value="{{ old('pump_number') }}">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="shift_id" class="form-label">Shift associé</label>
                                    <select class="form-control" id="shift_id" name="shift_id">
                                        <option value="">Sans shift</option>
                                        @foreach($currentShifts as $shift)
                                            <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>
                                                {{ $shift->date_shift->format('d/m/Y') }} - {{ $shift->shift }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_name" class="form-label">Nom du client</label>
                                    <input type="text" class="form-control" id="customer_name" 
                                           name="customer_name" value="{{ old('customer_name') }}">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vehicle_number" class="form-label">Immatriculation véhicule</label>
                                    <input type="text" class="form-control" id="vehicle_number" 
                                           name="vehicle_number" value="{{ old('vehicle_number') }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Cette vente déduira automatiquement le stock correspondant.
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save"></i> Enregistrer la vente
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
                <div class="card-header">
                    <h5 class="mb-0">Stock actuel</h5>
                </div>
                <div class="card-body">
                    @foreach($fuelTypes as $key => $name)
                        <div class="mb-3">
                            <h6>{{ $name }}</h6>
                            <div class="progress" style="height: 20px;">
                                @php
                                    $stock = $currentStocks[$key] ?? 0;
                                    $percentage = min(100, ($stock / 30000) * 100); // 30000 = capacité max
                                @endphp
                                <div class="progress-bar {{ $percentage < 20 ? 'bg-danger' : ($percentage < 50 ? 'bg-warning' : 'bg-success') }}" 
                                     role="progressbar" style="width: {{ $percentage }}%">
                                    {{ number_format($stock, 0) }} L
                                </div>
                            </div>
                            <small class="text-muted">{{ number_format($stock, 0) }} L disponibles</small>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Calcul rapide</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Quantité:</strong> <span id="calcQuantity">0</span> L
                    </div>
                    <div class="mb-2">
                        <strong>Prix unitaire:</strong> <span id="calcPrice">0</span> F CFA
                    </div>
                    <hr>
                    <div>
                        <strong>Montant total:</strong> <span id="calcTotal">0</span> F CFA
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Calcul automatique du montant total
    function calculateTotal() {
        const quantity = parseFloat($('#quantity').val()) || 0;
        const unitPrice = parseFloat($('#unit_price').val()) || 0;
        const total = quantity * unitPrice;
        
        $('#total_amount').val(total.toFixed(2));
        $('#calcQuantity').text(quantity.toFixed(1));
        $('#calcPrice').text(unitPrice.toLocaleString());
        $('#calcTotal').text(total.toLocaleString('fr-FR', {minimumFractionDigits: 0}));
        
        checkStock();
    }
    
    // Vérifier le stock
    function checkStock() {
        const fuelType = $('#fuel_type').val();
        const quantity = parseFloat($('#quantity').val()) || 0;
        
        if (fuelType && quantity > 0) {
            $.get(`/manager/check-stock/${fuelType}/${quantity}`, function(response) {
                const stockCheck = $('#stockCheck');
                if (response.can_sell) {
                    stockCheck.html(`
                        <div class="alert alert-success py-1 mb-0">
                            <i class="fas fa-check-circle"></i> ${response.message}
                            <br><small>Stock restant après vente: ${response.remaining_after.toLocaleString()} L</small>
                        </div>
                    `);
                    $('#submitBtn').prop('disabled', false);
                } else {
                    stockCheck.html(`
                        <div class="alert alert-danger py-1 mb-0">
                            <i class="fas fa-exclamation-circle"></i> ${response.message}
                        </div>
                    `);
                    $('#submitBtn').prop('disabled', true);
                }
            });
        }
    }
    
    // Écouteurs d'événements
    $('#quantity, #unit_price').on('input', calculateTotal);
    $('#fuel_type, #quantity').on('change', checkStock);
    
    // Initialiser les calculs
    calculateTotal();
    
    // Soumission du formulaire
    $('#saleForm').on('submit', function(e) {
        const fuelType = $('#fuel_type').val();
        const quantity = parseFloat($('#quantity').val()) || 0;
        
        if (!fuelType || quantity <= 0) {
            e.preventDefault();
            alert('Veuillez remplir tous les champs obligatoires.');
            return;
        }
        
        // Confirmation finale
        if (!confirm('Confirmez-vous l\'enregistrement de cette vente ? Le stock sera déduit.')) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
@endsection