@extends('layouts.app')

@section('title', 'Jaugeage des cuves')
@section('page-title', 'Jaugeage manuel')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('manager.stocks.dashboard') }}">Tableau de bord</a></li>
<li class="breadcrumb-item active">Jaugeage</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- En-tête avec les couleurs de l'entreprise -->
                <div class="card-header" style="background: linear-gradient(135deg, #FF7F00, #FF9900); color: white; border-bottom: 3px solid #333;">
                    <h3 class="card-title">
                        <i class="fas fa-ruler-vertical mr-2"></i>Jaugeage des cuves
                    </h3>
                    <div class="card-tools">
                        <span class="badge" style="background-color: #333; color: white; font-weight: bold;">
                            <i class="fas fa-building mr-1"></i> ODySSEE ENERGIE SA
                        </span>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Messages -->
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible" style="border-left: 4px solid #28a745;">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-check"></i> Succès</h5>
                        {!! session('success') !!}
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible" style="border-left: 4px solid #dc3545;">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-ban"></i> Erreur</h5>
                        {{ session('error') }}
                    </div>
                    @endif

                    @if(empty($tanks) || count($tanks) === 0)
                    <div class="alert alert-warning" style="border-left: 4px solid #ffc107;">
                        <i class="icon fas fa-exclamation-triangle mr-2"></i>
                        Aucune cuve configurée. 
                        <a href="{{ route('manager.tanks.create') }}" class="alert-link" style="color: #d39e00;">
                            Créez d'abord des cuves
                        </a>.
                    </div>
                    @else
                    <div class="row">
                        @foreach($tanks as $tank)
                        <div class="col-md-4 mb-4">
                            <div class="card card-outline card-primary" style="border: 1px solid #FF7F00;">
                                <div class="card-header" style="background: linear-gradient(to right, #f8f9fa, #e9ecef); border-bottom: 2px solid #FF7F00;">
                                    <h4 class="card-title" style="color: #333;">
                                        <i class="fas fa-oil-can mr-2" style="color: #FF7F00;"></i>Cuve {{ $tank['number'] }}
                                        <span class="badge {{ $tank['badge_class'] ?? 'badge-secondary' }} float-right" 
                                              style="{{ $tank['badge_class'] == 'badge-danger' ? 'background-color: #FF7F00;' : 
                                                      ($tank['badge_class'] == 'badge-success' ? 'background-color: #28a745;' : 
                                                      ($tank['badge_class'] == 'badge-dark' ? 'background-color: #333;' : '')) }}">
                                            {{ $tank['type_display'] ?? strtoupper($tank['fuel_type'] ?? 'N/A') }}
                                        </span>
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <!-- Informations de base -->
                                    <div class="mb-3" style="background-color: #f8f9fa; padding: 10px; border-radius: 5px; border-left: 3px solid #FF7F00;">
                                        <div class="d-flex justify-content-between mb-2">
                                            <small style="color: #666;">Capacité:</small>
                                            <strong style="color: #333;">{{ number_format($tank['capacity'] ?? 0, 0) }} L</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <small style="color: #666;">Stock actuel:</small>
                                            <strong style="color: #333;">{{ number_format($tank['current_volume'] ?? 0, 0) }} L</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <small style="color: #666;">Disponible:</small>
                                            <strong style="color: #28a745;">{{ number_format(($tank['available_capacity'] ?? 0), 0) }} L</strong>
                                        </div>
                                    </div>
                                    
                                    <!-- Barre de progression -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small style="color: #666;">Remplissage</small>
                                            <small><strong style="color: #333;">{{ number_format($tank['fill_percentage'] ?? 0, 1) }}%</strong></small>
                                        </div>
                                        <div class="progress progress-sm" style="height: 10px;">
                                            <div class="progress-bar bg-{{ $tank['progress_class'] ?? 'info' }}" 
                                                 style="width: {{ min($tank['fill_percentage'] ?? 0, 100) }}%;
                                                        {{ $tank['progress_class'] == 'danger' ? 'background-color: #dc3545;' : 
                                                          ($tank['progress_class'] == 'warning' ? 'background-color: #ffc107;' : 
                                                          ($tank['progress_class'] == 'success' ? 'background-color: #28a745;' : 
                                                          'background-color: #17a2b8;')) }}">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Formulaire SIMPLE -->
                                    <form method="POST" action="{{ route('manager.tank-levels.store') }}" class="tank-level-form">
                                        @csrf
                                        <input type="hidden" name="tank_id" value="{{ $tank['id'] }}">
                                        
                                        <div class="form-group">
                                            <label style="color: #333; font-weight: 500;">
                                                <i class="fas fa-ruler mr-1" style="color: #FF7F00;"></i>Niveau mesuré (cm)
                                            </label>
                                            <input type="number" class="form-control level-input" name="level_cm" 
                                                   min="0" max="400" step="0.1" required
                                                   placeholder="Ex: 150.5"
                                                   style="border: 1px solid #ced4da; border-radius: 4px; padding: 8px;">
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label style="color: #333; font-weight: 500;">
                                                        <i class="fas fa-thermometer-half mr-1" style="color: #dc3545;"></i>Température (°C)
                                                    </label>
                                                    <input type="number" class="form-control temperature-input" name="temperature_c" 
                                                           step="0.1" value="20"
                                                           style="border: 1px solid #ced4da; border-radius: 4px; padding: 8px;">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label style="color: #333; font-weight: 500;">
                                                        <i class="fas fa-calendar-alt mr-1" style="color: #17a2b8;"></i>Date
                                                    </label>
                                                    <input type="datetime-local" class="form-control" name="measurement_date" 
                                                           value="{{ now()->format('Y-m-d\TH:i') }}" required
                                                           style="border: 1px solid #ced4da; border-radius: 4px; padding: 8px;">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Aperçu du volume calculé -->
                                        <div class="volume-preview mb-3" style="display: none;">
                                            <!-- Rempli par JavaScript -->
                                        </div>
                                        
                                        <button type="submit" class="btn btn-block" 
                                                style="background: linear-gradient(135deg, #FF7F00, #FF9900); 
                                                       color: white; 
                                                       border: none;
                                                       padding: 10px;
                                                       font-weight: bold;
                                                       border-radius: 4px;
                                                       transition: all 0.3s;">
                                            <i class="fas fa-save mr-1"></i> Enregistrer jaugeage
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                
                <div class="card-footer" style="background-color: #f8f9fa; border-top: 2px solid #FF7F00;">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('manager.stocks.dashboard') }}" class="btn" 
                               style="background-color: #6c757d; color: white; border: none; padding: 8px 20px;">
                                <i class="fas fa-arrow-left mr-1"></i> Retour au tableau de bord
                            </a>
                        </div>
                        <div class="col-md-6 text-right">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1" style="color: #FF7F00;"></i>
                                Total: {{ count($tanks ?? []) }} cuve(s) • 
                                <span style="color: #333;">{{ date('d/m/Y H:i') }}</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Fonction pour calculer et afficher le volume estimé
    function calculateAndDisplayVolume(form) {
        const tankId = form.find('input[name="tank_id"]').val();
        const levelCm = form.find('.level-input').val();
        const temperature = form.find('.temperature-input').val();
        
        if (levelCm && levelCm > 0 && levelCm <= 400) {
            // Appel API pour calculer le volume
            $.ajax({
                url: '{{ route("manager.tank-levels.calculate-volume") }}',
                method: 'POST',
                data: {
                    tank_id: tankId,
                    height_cm: levelCm,
                    temperature_c: temperature,
                    _token: '{{ csrf_token() }}'
                },
                beforeSend: function() {
                    const volumePreview = form.find('.volume-preview');
                    volumePreview.html('<div class="text-center"><i class="fas fa-spinner fa-spin mr-2"></i>Calcul en cours...</div>').show();
                },
                success: function(response) {
                    if (response.success) {
                        const volumePreview = form.find('.volume-preview');
                        const fillPercentage = response.calculation.fill_percentage;
                        
                        // Déterminer la couleur selon le statut
                        let bgClass = 'alert-info';
                        let statusIcon = 'fa-info-circle';
                        
                        if (response.status_class === 'danger') {
                            bgClass = 'alert-danger';
                            statusIcon = 'fa-exclamation-triangle';
                        } else if (response.status_class === 'warning') {
                            bgClass = 'alert-warning';
                            statusIcon = 'fa-exclamation-circle';
                        } else if (response.status_class === 'success') {
                            bgClass = 'alert-success';
                            statusIcon = 'fa-check-circle';
                        }
                        
                        volumePreview.html(
                            '<div class="alert ' + bgClass + '">' +
                            '<i class="fas ' + statusIcon + ' mr-2"></i>' +
                            '<strong>Volume estimé:</strong> ' + response.volume_formatted + 
                            ' (' + fillPercentage + '% de remplissage)<br>' +
                            '<strong>Différence:</strong> <span class="' + response.status_class + '">' +
                            response.difference_formatted + ' (' + 
                            response.difference_percentage_formatted + ')</span>' +
                            (response.is_acceptable ? 
                                '<br><span class="text-success"><i class="fas fa-check mr-1"></i>Dans les tolérances</span>' : 
                                '<br><span class="text-danger"><i class="fas fa-exclamation mr-1"></i>Hors tolérance</span>'
                            ) +
                            '</div>'
                        ).show();
                    }
                },
                error: function(xhr) {
                    const volumePreview = form.find('.volume-preview');
                    volumePreview.html('<div class="alert alert-warning">Erreur lors du calcul</div>').show();
                }
            });
        }
    }
    // Calculer le volume lors de la saisie du niveau
    $('.level-input').on('input', function() {
        const form = $(this).closest('.tank-level-form');
        calculateAndDisplayVolume(form);
    });
    
    // Calculer le volume lors du changement de température
    $('.temperature-input').on('change', function() {
        const form = $(this).closest('.tank-level-form');
        calculateAndDisplayVolume(form);
    });
    
    // Masquer l'aperçu quand le niveau est vide
    $('.level-input').on('blur', function() {
        if (!$(this).val()) {
            $(this).closest('.tank-level-form').find('.volume-preview').hide();
        }
    });
    
    // Animation au survol des boutons
    $('button[type="submit"]').hover(
        function() {
            $(this).css({
                'background': 'linear-gradient(135deg, #FF9900, #FF7F00)',
                'transform': 'translateY(-2px)',
                'box-shadow': '0 4px 8px rgba(255, 127, 0, 0.3)'
            });
        },
        function() {
            $(this).css({
                'background': 'linear-gradient(135deg, #FF7F00, #FF9900)',
                'transform': 'translateY(0)',
                'box-shadow': 'none'
            });
        }
    );
});
</script>

<style>
/* Styles personnalisés pour ODySSEE ENERGIE SA */
.card-outline.card-primary {
    border-color: #FF7F00;
}

.card-outline.card-primary .card-header {
    background-color: rgba(255, 127, 0, 0.1);
    border-bottom: 2px solid #FF7F00;
}

.form-control:focus {
    border-color: #FF7F00;
    box-shadow: 0 0 0 0.2rem rgba(255, 127, 0, 0.25);
}

.badge {
    font-weight: 600;
    padding: 5px 10px;
}

.progress {
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    border-radius: 10px;
}

.btn:hover {
    transform: translateY(-2px);
    transition: all 0.3s ease;
}

/* Animation pour l'aperçu du volume */
.volume-preview {
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Style pour le badge de l'entreprise */
.badge-company {
    background: linear-gradient(135deg, #FF7F00, #333);
    color: white;
    font-size: 0.9em;
    padding: 5px 15px;
    border-radius: 20px;
}
</style>
@endsection