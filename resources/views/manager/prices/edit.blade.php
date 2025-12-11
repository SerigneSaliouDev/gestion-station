@extends('layouts.app')

@section('title', 'Gestion des Prix')
@section('page-title', 'Modification des Prix des Carburants')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('manager.index_form') }}">Manager</a></li>
<li class="breadcrumb-item active">Prix</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header odyssee-bg-primary">
                <h3 class="card-title text-white">
                    <i class="fas fa-gas-pump"></i> Modification des Prix
                </h3>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        
                        @if(session('changed_prices'))
                            <ul class="mt-2 mb-0">
                                @foreach(session('changed_prices') as $change)
                                <li>
                                    {{ ucfirst($change['fuel_type']) }} : 
                                    {{ number_format($change['old_price'], 0, ',', ' ') }} → 
                                    {{ number_format($change['new_price'], 0, ',', ' ') }} F CFA
                                </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif

                <form method="POST" action="{{ route('manager.update_prices') }}" id="priceForm">
                    @csrf
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th width="30%">Type de Carburant</th>
                                    <th width="25%">Prix Actuel</th>
                                    <th width="25%">Nouveau Prix</th>
                                    <th width="20%">Variation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fuelTypes as $fuel)
                                <tr>
                                    <td class="font-weight-bold">
                                        <i class="fas fa-gas-pump text-primary mr-2"></i>
                                        {{ $fuel['name'] }}
                                    </td>
                                    <td class="text-right">
                                        <div class="h5 text-primary">
                                            {{ number_format($fuel['current_price'], 0, ',', ' ') }} F CFA
                                        </div>
                                        <small class="text-muted">/ litre</small>
                                    </td>
                                    <td>
                                        <input type="hidden" name="prices[{{ $loop->index }}][fuel_type]" value="{{ $fuel['id'] }}">
                                        <div class="input-group">
                                            <input type="number" 
                                                   name="prices[{{ $loop->index }}][price_per_liter]" 
                                                   class="form-control price-input" 
                                                   data-fuel-type="{{ $fuel['id'] }}"
                                                   data-old-price="{{ $fuel['current_price'] }}"
                                                   value="{{ $fuel['current_price'] }}"
                                                   step="1"
                                                   min="0"
                                                   max="10000"
                                                   required>
                                            <div class="input-group-append">
                                                <span class="input-group-text">F CFA</span>
                                            </div>
                                        </div>
                                        <small class="text-muted">Prix au litre</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="variation-display" id="variation-{{ $fuel['id'] }}">
                                            <span class="badge badge-secondary">Aucun changement</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group mt-4">
                        <label for="change_reason" class="font-weight-bold">
                            <i class="fas fa-clipboard-list text-warning mr-1"></i>
                            Raison de la modification <span class="text-danger">*</span>
                        </label>
                        <textarea name="change_reason" 
                                  id="change_reason" 
                                  class="form-control" 
                                  rows="3" 
                                  placeholder="Ex: Ajustement selon les directives de la direction, augmentation du prix d'achat, etc."
                                  required
                                  minlength="10"
                                  maxlength="500"></textarea>
                        <small class="text-muted">Minimum 10 caractères. Cette information sera enregistrée pour la traçabilité.</small>
                    </div>

                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Attention :</strong> Toute modification de prix sera horodatée et liée à votre compte. 
                        Assurez-vous d'avoir l'autorisation nécessaire avant de procéder.
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn odyssee-btn-primary btn-lg">
                            <i class="fas fa-save"></i> Enregistrer les Modifications
                        </button>
                        <button type="button" class="btn btn-secondary btn-lg ml-2" onclick="resetForm()">
                            <i class="fas fa-undo"></i> Réinitialiser
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Historique récent -->
        <div class="card">
            <div class="card-header odyssee-bg-primary">
                <h3 class="card-title text-white">
                    <i class="fas fa-history"></i> Historique Récent
                </h3>
            </div>
            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                @forelse($priceHistory as $history)
                <div class="mb-3 pb-2 border-bottom">
                    <div class="d-flex justify-content-between">
                        <div>
                            <span class="font-weight-bold">{{ ucfirst($history->fuel_type) }}</span>
                            <br>
                            <small class="text-muted">
                                <i class="far fa-clock"></i> 
                                {{ $history->created_at->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <div class="text-right">
                            <div class="h5 text-primary">
                                {{ number_format($history->price_per_liter, 0, ',', ' ') }} F CFA
                            </div>
                        </div>
                    </div>
                    <div class="mt-1">
                        <small>
                            <i class="fas fa-user"></i> 
                            {{ $history->changer->name ?? 'Utilisateur inconnu' }}
                        </small>
                        @if($history->change_reason)
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-comment"></i> 
                            {{ Str::limit($history->change_reason, 50) }}
                        </small>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-3">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <p>Aucun historique de prix disponible</p>
                </div>
                @endforelse
                
                <div class="text-center mt-3">
                    <a href="{{ route('manager.price_history') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-list"></i> Voir tout l'historique
                    </a>
                </div>
            </div>
        </div>

    
                    <!-- Statistiques -->
            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Statistiques
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="h4 text-primary">
                            {{ count($fuelTypes) }} <!-- CORRECTION ICI -->
                        </div>
                        <p class="text-muted mb-0">Types de carburant</p>
                    </div>
                    
                    <hr>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Dernière modification : 
                            @if($priceHistory->count() > 0)
                                {{ $priceHistory->first()->created_at->diffForHumans() }}
                            @else
                                Jamais
                            @endif
                        </small>
                    </div>
                </div>
            </div>
                        </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.price-input {
    font-weight: bold;
    text-align: right;
}
.variation-display .badge {
    font-size: 0.9em;
}
.table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Calculer la variation en temps réel
        $('.price-input').on('input', function() {
            const fuelType = $(this).data('fuel-type');
            const oldPrice = parseFloat($(this).data('old-price')) || 0;
            const newPrice = parseFloat($(this).val()) || 0;
            const variation = newPrice - oldPrice;
            
            const $variationDisplay = $('#variation-' + fuelType);
            
            if (variation === 0) {
                $variationDisplay.html('<span class="badge badge-secondary">Aucun changement</span>');
            } else if (variation > 0) {
                $variationDisplay.html('<span class="badge badge-danger">+' + formatNumber(variation) + ' F CFA</span>');
            } else {
                $variationDisplay.html('<span class="badge badge-success">' + formatNumber(variation) + ' F CFA</span>');
            }
        });
        
        // Validation du formulaire
        $('#priceForm').on('submit', function(e) {
            let hasChanges = false;
            $('.price-input').each(function() {
                const oldPrice = parseFloat($(this).data('old-price')) || 0;
                const newPrice = parseFloat($(this).val()) || 0;
                
                if (oldPrice !== newPrice) {
                    hasChanges = true;
                }
            });
            
            if (!hasChanges) {
                e.preventDefault();
                alert('Aucun changement de prix détecté. Veuillez modifier au moins un prix avant de soumettre.');
                return false;
            }
            
            const reason = $('#change_reason').val().trim();
            if (reason.length < 10) {
                e.preventDefault();
                alert('Veuillez fournir une raison de modification détaillée (minimum 10 caractères).');
                return false;
            }
            
            // Confirmation finale
            if (!confirm('Êtes-vous sûr de vouloir modifier les prix ? Cette action sera enregistrée et horodatée.')) {
                e.preventDefault();
                return false;
            }
        });
        
        function formatNumber(num) {
            return new Intl.NumberFormat('fr-FR').format(num);
        }
    });
    
    function resetForm() {
        if (confirm('Voulez-vous vraiment réinitialiser tous les prix à leurs valeurs actuelles ?')) {
            $('.price-input').each(function() {
                const oldPrice = $(this).data('old-price');
                $(this).val(oldPrice);
                $(this).trigger('input');
            });
            $('#change_reason').val('');
        }
    }
</script>
@endpush