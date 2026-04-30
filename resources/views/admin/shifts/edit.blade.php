@extends('layouts.admin')

@section('title', 'Éditer le Shift')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Éditer le Shift #{{ $shift->id }}</h1>
        <div>
           
        </div>
    </div>

    <!-- Alerts -->
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-edit"></i> Modifier les informations
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.shifts.update', $shift->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Informations de base -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Station</label>
                                <select name="station_id" class="form-select" required>
                                    @foreach($stations as $station)
                                        <option value="{{ $station->id }}" 
                                                {{ $shift->station_id == $station->id ? 'selected' : '' }}>
                                            {{ $station->nom }} ({{ $station->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Statut</label>
                                <select name="statut" class="form-select" required>
                                    <option value="valide" {{ $shift->statut == 'valide' ? 'selected' : '' }}>
                                        Validé
                                    </option>
                                    <option value="en_attente" {{ $shift->statut == 'en_attente' ? 'selected' : '' }}>
                                        En attente
                                    </option>
                                    <option value="rejete" {{ $shift->statut == 'rejete' ? 'selected' : '' }}>
                                        Rejeté
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Totaux -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Total des ventes (FCFA)</label>
                                <input type="number" name="total_ventes" class="form-control" 
                                       value="{{ old('total_ventes', $shift->total_ventes) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Total litres vendus</label>
                                <input type="number" step="0.01" name="total_litres" class="form-control" 
                                       value="{{ old('total_litres', $shift->total_litres) }}" required>
                            </div>
                        </div>

                        <!-- Dépenses et écart -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Total dépenses (FCFA)</label>
                                <input type="number" name="total_depenses" class="form-control" 
                                       value="{{ old('total_depenses', $shift->total_depenses) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Écart final (FCFA)</label>
                                <input type="number" name="ecart_final" class="form-control 
                                    @if($shift->ecart_final >= 0) is-valid @else is-invalid @endif" 
                                       value="{{ old('ecart_final', $shift->ecart_final) }}" required>
                                <div class="form-text">
                                    Positif = excédent, Négatif = déficit
                                </div>
                            </div>
                        </div>

                        <!-- Commentaire -->
                        <div class="mb-3">
                            <label class="form-label">Commentaire de validation</label>
                            <textarea name="commentaire_validation" class="form-control" rows="3">{{ old('commentaire_validation', $shift->commentaire_validation) }}</textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.shifts.show', $shift->id) }}" 
                               class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Résumé -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Résumé actuel
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Station:</span>
                            <strong>{{ $shift->station->nom }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Date:</span>
                            <strong>{{ $shift->date_shift->format('d/m/Y') }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Shift:</span>
                            <strong class="text-capitalize">{{ $shift->shift }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Agent:</span>
                            <strong>{{ $shift->user->name ?? 'Non spécifié' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Statut actuel:</span>
                            <span class="badge 
                                @if($shift->statut == 'valide') bg-success
                                @elseif($shift->statut == 'en_attente') bg-warning
                                @else bg-danger @endif">
                                {{ $shift->statut }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card shadow">
             
               
                        
                       
                        
                        @if(auth()->user()->hasRole('admin'))
                            <form action="{{ route('admin.shifts.destroy', $shift->id) }}" method="POST" 
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce shift?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Supprimer ce shift
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails des pompes -->
    <div class="card shadow mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-gas-pump"></i> Détails des pompes
            </h6>
        </div>
        <div class="card-body">
            @if($shift->pompeDetails && $shift->pompeDetails->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Pompe</th>
                                <th>Carburant</th>
                                <th>Index début</th>
                                <th>Index fin</th>
                                <th>Litres vendus</th>
                                <th>Montant</th>
                                <!-- Supprimer la colonne Actions -->
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shift->pompeDetails as $index => $detail)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $detail->numero_pompe }}</td>
                                <td>{{ $detail->carburant }}</td>
                                <td>{{ number_format($detail->index_debut) }}</td>
                                <td>{{ number_format($detail->index_fin) }}</td>
                                <td>{{ number_format($detail->litrage_vendu) }} L</td>
                                <td>{{ number_format($detail->montant_ventes) }} FCFA</td>
                                <!-- Supprimer le bouton d'édition -->
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

<!-- Modal de validation -->
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
                    <p>Êtes-vous sûr de vouloir valider ce shift?</p>
                    <div class="mb-3">
                        <label class="form-label">Commentaire (optionnel)</label>
                        <textarea name="comment" class="form-control" rows="3"></textarea>
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

<!-- SUPPRIMER TOUTE LA SECTION DES MODALS POUR POMPE DETAILS -->
@endsection

@section('styles')
<style>
    .is-valid {
        border-color: #198754 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
    }
    .is-invalid {
        border-color: #dc3545 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    }
</style>
@endsection