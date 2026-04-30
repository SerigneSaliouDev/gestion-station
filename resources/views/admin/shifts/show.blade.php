@extends('layouts.admin')

@section('title', 'Détails du Shift')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Détails du Shift #{{ $shift->id }}</h1>
        <div>
            <a href="{{ route('admin.shifts.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Informations principales -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Informations du Shift
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Station:</th>
                            <td>
                                <a href="{{ route('admin.stations.show', $shift->station_id) }}">
                                    {{ $shift->station->nom }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Date:</th>
                            <td>{{ $shift->date_shift->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Shift:</th>
                            <td>
                                @if($shift->shift == 'matin')
                                    <span class="badge bg-info">Matin</span>
                                @elseif($shift->shift == 'soir')
                                    <span class="badge bg-warning">Soir</span>
                                @else
                                    <span class="badge bg-secondary">{{ $shift->shift }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Agent:</th>
                            <td>{{ $shift->user->name ?? 'Non spécifié' }}</td>
                        </tr>
                        <tr>
                            <th>Statut:</th>
                            <td>
                                @if($shift->statut == 'valide')
                                    <span class="badge bg-success">Validé</span>
                                @elseif($shift->statut == 'en_attente')
                                    <span class="badge bg-warning">En attente</span>
                                @elseif($shift->statut == 'rejete')
                                    <span class="badge bg-danger">Rejeté</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Créé le:</th>
                            <td>{{ $shift->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @if($shift->valide_le)
                        <tr>
                            <th>Validé le:</th>
                            <td>{{ $shift->valide_le->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Validé par:</th>
                            <td>{{ $shift->validateur->name ?? 'Système' }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-bar"></i> Résumé financier
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr class="table-light">
                            <th>Total des ventes:</th>
                            <td class="text-end fw-bold text-success">
                                {{ number_format($shift->total_ventes, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                        <tr>
                            <th>Total litres vendus:</th>
                            <td class="text-end fw-bold">
                                {{ number_format($shift->total_litres, 0, ',', ' ') }} L
                            </td>
                        </tr>
                        <tr>
                            <th>Total dépenses:</th>
                            <td class="text-end fw-bold text-danger">
                                {{ number_format($shift->total_depenses, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                        <tr>
                            <th>Écart final:</th>
                            <td class="text-end fw-bold 
                                @if($shift->ecart_final >= 0) text-success @else text-danger @endif">
                                {{ number_format($shift->ecart_final, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                        <tr>
                            <th>Commentaire:</th>
                            <td class="@if($shift->ecart_final >= 0) text-success @else text-danger @endif">
                                {{ $shift->commentaire_validation ?? 'Aucun commentaire' }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails par type de carburant -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-gas-pump"></i> Ventes par type de carburant
                    </h6>
                </div>
                <div class="card-body">
                    @if(!empty($fuelTotals))
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type de carburant</th>
                                        <th class="text-end">Quantité (L)</th>
                                        <th class="text-end">Montant (FCFA)</th>
                                        <th class="text-end">Prix moyen/L</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fuelTotals as $fuelType => $data)
                                    <tr>
                                        <td>
                                            <strong class="text-capitalize">{{ $fuelType }}</strong>
                                        </td>
                                        <td class="text-end">{{ number_format($data['litres'], 0, ',', ' ') }} L</td>
                                        <td class="text-end text-success">
                                            {{ number_format($data['amount'], 0, ',', ' ') }} FCFA
                                        </td>
                                        <td class="text-end">
                                            @if($data['litres'] > 0)
                                                {{ number_format($data['amount'] / $data['litres'], 0, ',', ' ') }} FCFA/L
                                            @else
                                                0 FCFA/L
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Aucune donnée de vente disponible pour ce shift.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Détails des pompes -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list"></i> Détails des pompes
                    </h6>
                </div>
                <div class="card-body">
                    @if($shift->pompeDetails && $shift->pompeDetails->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Pompe</th>
                                        <th>Carburant</th>
                                        <th class="text-end">Index début</th>
                                        <th class="text-end">Index fin</th>
                                        <th class="text-end">Vendu (L)</th>
                                        <th class="text-end">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($shift->pompeDetails as $detail)
                                    <tr>
                                        <td><strong>{{ $detail->numero_pompe }}</strong></td>
                                        <td><span class="badge bg-info">{{ $detail->carburant }}</span></td>
                                        <td class="text-end">{{ number_format($detail->index_debut) }}</td>
                                        <td class="text-end">{{ number_format($detail->index_fin) }}</td>
                                        <td class="text-end">{{ number_format($detail->litrage_vendu) }} L</td>
                                        <td class="text-end text-success">
                                            {{ number_format($detail->montant_ventes) }} FCFA
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Aucun détail de pompe enregistré pour ce shift.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Dépenses -->
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-money-bill-wave"></i> Dépenses
                    </h6>
                </div>
                <div class="card-body">
                    @if($shift->depenses && $shift->depenses->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th class="text-end">Montant</th>
                                        <th>Preuve</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($shift->depenses as $depense)
                                    <tr>
                                        <td>
                                            <span class="badge 
                                                @if($depense->type == 'frais_opérationnels') bg-primary
                                                @elseif($depense->type == 'frais_personnel') bg-warning
                                                @elseif($depense->type == 'frais_divers') bg-info
                                                @else bg-secondary @endif">
                                                {{ $depense->type }}
                                            </span>
                                        </td>
                                        <td>{{ $depense->description ?? 'Non spécifié' }}</td>
                                        <td class="text-end text-danger">
                                            {{ number_format($depense->montant) }} FCFA
                                        </td>
                                        <td>
                                            @if($depense->justificatif)
                                                <a href="{{ Storage::url($depense->justificatif) }}" 
                                                   target="_blank" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @else
                                                <span class="badge bg-secondary">Non fourni</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="2">Total dépenses</th>
                                        <th class="text-end text-danger">
                                            {{ number_format($shift->total_depenses, 0, ',', ' ') }} FCFA
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Aucune dépense enregistrée pour ce shift.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-cogs"></i> Actions
            </h6>
        </div>
        <div class="card-body">
            <div class="d-flex gap-2">
                @if($shift->statut == 'en_attente')
                    <form action="{{ route('admin.shifts.validate', $shift->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#validateModal">
                            <i class="fas fa-check"></i> Valider
                        </button>
                    </form>
                    
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal">
                        <i class="fas fa-edit"></i> Modifier
                    </button>
                @endif
                
                <a href="{{ route('admin.shifts.edit', $shift->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Éditer
                </a>
                
                @if(auth()->user()->hasRole('admin'))
                    <form action="{{ route('admin.shifts.destroy', $shift->id) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce shift?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </form>
                @endif
                
                <a href="{{ route('admin.shifts.today') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Retour aux shifts
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour validation -->
<div class="modal fade" id="validateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.validations.validate', $shift->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Valider le Shift #{{ $shift->id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Commentaire (optionnel)</label>
                        <textarea name="comment" class="form-control" rows="3" 
                                  placeholder="Ajouter un commentaire..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Confirmer la validation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .table th {
        background-color: #f8f9fc;
    }
    .badge {
        font-size: 0.8em;
    }
</style>
@endsection