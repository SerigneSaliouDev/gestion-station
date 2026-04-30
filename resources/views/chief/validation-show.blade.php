@extends('layouts.chief')

@section('title', 'Détails de la Saisie')
@section('page-icon', 'fa-eye')
@section('page-title', 'Détails de la Saisie')
@section('page-subtitle', 'Examen avant validation')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('chief.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('chief.validations') }}">Validations</a></li>
    <li class="breadcrumb-item active">Détails #{{ $saisie->id ?? 'N/A' }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif
            
            <!-- Carte principale -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clipboard-check mr-2"></i> 
                        Saisie #{{ $saisie->id }} - 
                        {{ $saisie->date_shift->format('d/m/Y') }} 
                        ({{ $saisie->shift }})
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ $saisie->statut === 'valide' ? 'success' : ($saisie->statut === 'rejete' ? 'danger' : 'warning') }}">
                            {{ $saisie->statut === 'valide' ? 'Validé' : ($saisie->statut === 'rejete' ? 'Rejeté' : 'En attente') }}
                        </span>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Informations générales -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card card-info">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i> Informations Générales</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td width="40%"><strong>Station:</strong></td>
                                            <td>
                                                {{ $saisie->station->nom ?? 'N/A' }}
                                                <small class="text-muted">({{ $saisie->station->code ?? 'N/A' }})</small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Date du shift:</strong></td>
                                            <td>{{ $saisie->date_shift->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Shift:</strong></td>
                                            <td>{{ $saisie->shift }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Pompiste:</strong></td>
                                            <td>{{ $saisie->responsable }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Créé par:</strong></td>
                                            <td>{{ $saisie->user->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Date création:</strong></td>
                                            <td>{{ $saisie->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card card-success">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i> Totaux</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td width="60%"><strong>Total Ventes:</strong></td>
                                            <td class="text-right font-weight-bold text-primary">
                                                {{ number_format($saisie->total_ventes, 0, ',', ' ') }} FCFA
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Litres:</strong></td>
                                            <td class="text-right">{{ number_format($saisie->total_litres, 2, ',', ' ') }} L</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Versement:</strong></td>
                                            <td class="text-right">{{ number_format($saisie->versement, 0, ',', ' ') }} FCFA</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Dépenses:</strong></td>
                                            <td class="text-right text-danger">
                                                {{ number_format($saisie->total_depenses, 0, ',', ' ') }} FCFA
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Écart initial:</strong></td>
                                            <td class="text-right">
                                                <span class="badge badge-{{ $saisie->ecart_formatted['classe'] ?? 'secondary' }}">
                                                    {{ $saisie->ecart_formatted['signe'] ?? '' }}
                                                    {{ number_format($saisie->ecart_formatted['montant'] ?? 0, 0, ',', ' ') }} FCFA
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Écart final:</strong></td>
                                            <td class="text-right">
                                                <span class="badge badge-{{ $saisie->ecart_final_formatted['classe'] ?? 'secondary' }}">
                                                    {{ $saisie->ecart_final_formatted['signe'] ?? '' }}
                                                    {{ number_format($saisie->ecart_final_formatted['montant'] ?? 0, 0, ',', ' ') }} FCFA
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    
                    
                    <!-- Détails des dépenses -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-danger">
                                    <h3 class="card-title">
                                        <i class="fas fa-receipt mr-2"></i> Détail des Dépenses
                                    </h3>
                                    <div class="card-tools">
                                        <span class="badge badge-light">
                                            {{ $saisie->depenses->count() ?? 0 }} dépense(s)
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body table-responsive p-0">
                                    @if($saisie->depenses && $saisie->depenses->count() > 0)
                                        <table class="table table-hover table-striped">
                                            <thead>
                                                <tr class="bg-light">
                                                    <th>#</th>
                                                    <th>Catégorie</th>
                                                    <th>Description</th>
                                                    <th class="text-center">Date</th>
                                                    <th class="text-right">Montant</th>
                                                    <th class="text-center">Justificatif</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $totalDepensesDetail = 0; @endphp
                                                @foreach($saisie->depenses as $index => $depense)
                                                    @php $totalDepensesDetail += $depense->montant ?? 0; @endphp
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>
                                                            <span class="badge badge-secondary">
                                                                {{ $depense->categorie ?? 'Non catégorisé' }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $depense->description ?? 'Sans description' }}</td>
                                                        <td class="text-center">
                                                            {{ $depense->created_at->format('d/m/Y H:i') }}
                                                        </td>
                                                        <td class="text-right text-danger font-weight-bold">
                                                            {{ number_format($depense->montant ?? 0, 0, ',', ' ') }} FCFA
                                                        </td>
                                                        <td class="text-center">
                                                            @if($depense->justificatif)
                                                                <a href="{{ asset('storage/' . $depense->justificatif) }}" 
                                                                   target="_blank" class="btn btn-sm btn-info">
                                                                    <i class="fas fa-file-invoice"></i>
                                                                </a>
                                                            @else
                                                                <span class="text-muted">Aucun</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                <!-- Total ligne -->
                                                <tr class="bg-gray">
                                                    <td colspan="4" class="text-right"><strong>TOTAL DÉPENSES:</strong></td>
                                                    <td class="text-right font-weight-bold text-danger">
                                                        {{ number_format($totalDepensesDetail, 0, ',', ' ') }} FCFA
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="text-center py-5">
                                            <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">Aucune dépense enregistrée</h5>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notes et observations -->
                    @if($saisie->notes_validation)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card card-warning">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-sticky-note mr-2"></i> Notes de Validation
                                    </h3>
                                </div>
                                <div class="card-body">
                                    {{ $saisie->notes_validation }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Boutons d'action -->
                    @if($saisie->statut === 'en_attente')
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-footer text-center bg-light">
                                    <a href="{{ route('chief.validations') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left mr-2"></i> Retour à la liste
                                    </a>
                                    
                                    <button type="button" class="btn btn-success btn-validate" 
                                            data-id="{{ $saisie->id }}">
                                        <i class="fas fa-check-circle mr-2"></i> Valider la saisie
                                    </button>
                                    
                                    <button type="button" class="btn btn-danger btn-reject" 
                                            data-id="{{ $saisie->id }}">
                                        <i class="fas fa-times-circle mr-2"></i> Rejeter la saisie
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                Cette saisie a déjà été 
                                @if($saisie->statut === 'valide')
                                    <strong class="text-success">validée</strong>
                                    @if($saisie->validateur)
                                        par {{ $saisie->validateur->name }}
                                        le {{ $saisie->validation_date->format('d/m/Y H:i') }}
                                    @endif
                                @elseif($saisie->statut === 'rejete')
                                    <strong class="text-danger">rejetée</strong>
                                    @if($saisie->validateur)
                                        par {{ $saisie->validateur->name }}
                                        le {{ $saisie->validation_date->format('d/m/Y H:i') }}
                                    @endif
                                @endif
                            </div>
                            <a href="{{ route('chief.validations') }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left mr-2"></i> Retour aux validations
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de validation -->
<div class="modal fade" id="validateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="validateForm" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle mr-2"></i> Valider la saisie
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="validateComment">Commentaire (optionnel):</label>
                        <textarea class="form-control" id="validateComment" name="comment" 
                                  rows="3" placeholder="Ajouter un commentaire..."></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        En validant cette saisie, vous confirmez que tous les montants sont corrects.
                        Cette action est définitive.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-2"></i> Confirmer la validation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de rejet -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle mr-2"></i> Rejeter la saisie
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejectReason">Raison du rejet *</label>
                        <textarea class="form-control" id="rejectReason" name="raison_rejet" 
                                  rows="3" required placeholder="Indiquer la raison du rejet..."></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Cette action est irréversible. La saisie sera marquée comme rejetée.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times mr-2"></i> Confirmer le rejet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card-header.bg-info, .card-header.bg-danger {
        color: white;
    }
    .bg-gray {
        background-color: #f8f9fa;
    }
    .table td, .table th {
        vertical-align: middle;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Gestion de la validation
    $('.btn-validate').on('click', function() {
        var saisieId = $(this).data('id');
        var url = '{{ route("chief.validation.valider", ":id") }}'.replace(':id', saisieId);
        $('#validateForm').attr('action', url);
        $('#validateModal').modal('show');
    });
    
    // Gestion du rejet
    $('.btn-reject').on('click', function() {
        var saisieId = $(this).data('id');
        var url = '{{ route("chief.validation.rejeter", ":id") }}'.replace(':id', saisieId);
        $('#rejectForm').attr('action', url);
        $('#rejectModal').modal('show');
    });
    
    // Validation des formulaires
    $('#validateForm').on('submit', function(e) {
        if (!confirm('Êtes-vous sûr de vouloir valider cette saisie ?')) {
            e.preventDefault();
        }
    });
    
    $('#rejectForm').on('submit', function(e) {
        var reason = $('#rejectReason').val().trim();
        if (!reason) {
            e.preventDefault();
            alert('Veuillez indiquer la raison du rejet.');
            $('#rejectReason').focus();
        } else if (!confirm('Êtes-vous sûr de vouloir rejeter cette saisie ?')) {
            e.preventDefault();
        }
    });
    
    // Afficher/masquer les détails
    $('.btn-toggle-details').on('click', function() {
        var target = $(this).data('target');
        $(target).slideToggle();
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });
});
</script>
@endpush