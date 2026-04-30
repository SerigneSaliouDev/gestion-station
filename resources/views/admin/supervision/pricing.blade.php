@extends('layouts.admin')

@section('title', 'Tarification - Supervision')
@section('page-title', 'Supervision des Prix')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.supervision.pricing') }}">Supervision</a></li>
    <li class="breadcrumb-item active">Tarification</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Gestion des Prix des Carburants</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Informations importantes</h5>
                        Cette section permet de superviser et de modifier les prix des carburants pour l'ensemble du réseau.
                        Les changements s'appliquent à toutes les stations immédiatement.
                    </div>

                    <!-- Formulaire de mise à jour des prix -->
                    <form action="{{ route('fuel-prices.update') }}" method="POST" id="pricingForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-danger">
                                        <h3 class="card-title text-white">Essence Pirogue</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="essence_price">Prix au litre (FCFA)</label>
                                            <div class="input-group">
                                                <input type="number" 
                                                       step="0.01" 
                                                       name="essence" 
                                                       id="essence_price"
                                                       class="form-control"
                                                       value="{{ $prices->essence ?? 850 }}"
                                                       required>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">FCFA/L</span>
                                                </div>
                                            </div>
                                            <small class="text-muted">Dernière mise à jour: {{ $prices->updated_at ?? 'Jamais' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-warning">
                                        <h3 class="card-title text-dark">Gas-oil</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="gasoil_price">Prix au litre (FCFA)</label>
                                            <div class="input-group">
                                                <input type="number" 
                                                       step="0.01" 
                                                       name="gasoil" 
                                                       id="gasoil_price"
                                                       class="form-control"
                                                       value="{{ $prices->gasoil ?? 800 }}"
                                                       required>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">FCFA/L</span>
                                                </div>
                                            </div>
                                            <small class="text-muted">Dernière mise à jour: {{ $prices->updated_at ?? 'Jamais' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-info">
                                        <h3 class="card-title text-white">Super</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="super_price">Prix au litre (FCFA)</label>
                                            <div class="input-group">
                                                <input type="number" 
                                                       step="0.01" 
                                                       name="super" 
                                                       id="super_price"
                                                       class="form-control"
                                                       value="{{ $prices->super ?? 900 }}"
                                                       required>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">FCFA/L</span>
                                                </div>
                                            </div>
                                            <small class="text-muted">Dernière mise à jour: {{ $prices->updated_at ?? 'Jamais' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-secondary">
                                        <h3 class="card-title text-white">Paramètres de mise à jour</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="update_reason">Raison de la mise à jour</label>
                                            <textarea name="update_reason" 
                                                      id="update_reason" 
                                                      class="form-control" 
                                                      rows="2"
                                                      placeholder="Ex: Ajustement selon les cours internationaux..."
                                                      required></textarea>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="effective_date">Date d'effet</label>
                                            <input type="datetime-local" 
                                                   name="effective_date" 
                                                   id="effective_date"
                                                   class="form-control"
                                                   value="{{ now()->format('Y-m-d\TH:i') }}"
                                                   required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" 
                                                       class="custom-control-input" 
                                                       id="notify_managers"
                                                       name="notify_managers" 
                                                       checked>
                                                <label class="custom-control-label" for="notify_managers">
                                                    Notifier les managers par email
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save mr-2"></i>Mettre à jour les prix
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                            <i class="fas fa-undo mr-2"></i>Réinitialiser
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
@endsection

@push('scripts')
<script>
    function resetForm() {
        if (confirm('Êtes-vous sûr de vouloir réinitialiser le formulaire ?')) {
            document.getElementById('pricingForm').reset();
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Graphique de comparaison
        var ctx = document.getElementById('priceComparisonChart').getContext('2d');
        var comparisonChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['ODYSSE', 'Total', 'Shell', 'BP'],
                datasets: [{
                    label: 'Essence (FCFA/L)',
                    data: [850, 855, 852, 848],
                    backgroundColor: '#dc3545',
                    borderColor: '#dc3545',
                    borderWidth: 1
                }, {
                    label: 'Gas-oil (FCFA/L)',
                    data: [800, 805, 802, 798],
                    backgroundColor: '#ffc107',
                    borderColor: '#ffc107',
                    borderWidth: 1
                }, {
                    label: 'Super (FCFA/L)',
                    data: [900, 905, 902, 898],
                    backgroundColor: '#17a2b8',
                    borderColor: '#17a2b8',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        stacked: false,
                    },
                    y: {
                        beginAtZero: false,
                        min: 780,
                        ticks: {
                            callback: function(value) {
                                return value + ' FCFA';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw + ' FCFA';
                            }
                        }
                    }
                }
            }
        });
        
        // Validation du formulaire
        $('#pricingForm').on('submit', function(e) {
            var essence = $('#essence_price').val();
            var gasoil = $('#gasoil_price').val();
            var superPrice = $('#super_price').val();
            
            if (essence < 0 || gasoil < 0 || superPrice < 0) {
                e.preventDefault();
                alert('Les prix ne peuvent pas être négatifs !');
                return false;
            }
            
            if (confirm('Êtes-vous sûr de vouloir mettre à jour les prix ? Cette action affectera toutes les stations.')) {
                return true;
            } else {
                e.preventDefault();
                return false;
            }
        });
    });
</script>
@endpush