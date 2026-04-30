@extends('layouts.app')

@section('title', 'Historique des Saisies')
@section('page-title', 'Historique des Shifts')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('manager.index_form') }}">Manager</a></li>
<li class="breadcrumb-item active">Historique</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header odyssee-bg-primary">
        <h3 class="card-title text-white">
            <i class="fas fa-history"></i> Historique des Shifts
        </h3>
        <div class="card-tools">
            <a href="{{ route('manager.index_form') }}" class="btn btn-light btn-sm">
                <i class="fas fa-plus"></i> Nouvelle Saisie
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="bg-light">
                    <tr>
                        <th width="60">ID</th>
                        <th>Date Shift</th>
                        <th>Shift</th>
                        <th>Station</th>
                        <th>Responsable</th>
                        <th class="text-right">Total Ventes</th>
                        <th class="text-right">Versement</th>
                        <th class="text-right">Écart Final</th>
                        <th class="text-center" width="180">Statut</th>
                        <th class="text-center" width="180">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($saisies as $saisie)
                    <tr>
                        <td class="font-weight-bold">#{{ $saisie->id }}</td>
                        <td>
                            <i class="far fa-calendar text-muted mr-1"></i>
                            {{ $saisie->date_shift->format('d/m/Y') }}
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $saisie->shift }}</span>
                        </td>
                        <!-- COLONNE STATION -->
                        <td>
                            @if($saisie->station)
                                <span class="badge badge-secondary">{{ $saisie->station->code }}</span>
                                <br><small>{{ $saisie->station->nom }}</small>
                            @else
                                <span class="badge badge-light">Non assigné</span>
                            @endif
                        </td>
                        <!-- FIN COLONNE STATION -->
                        <td>{{ $saisie->responsable }}</td>
                        <td class="text-right font-weight-bold text-primary">
                            {{ number_format($saisie->total_ventes, 0, ',', ' ') }} F CFA
                        </td>
                        <td class="text-right">
                            {{ number_format($saisie->versement, 0, ',', ' ') }} F CFA
                        </td>
                        
                        <!-- COLONNE ÉCART FINAL -->
                        <td class="text-right">
                            @php
                                // Calculer directement l'écart selon la formule : Versement - (Ventes - Dépenses)
                                $ecart = $saisie->versement - ($saisie->total_ventes - $saisie->total_depenses);
                            @endphp
                            
                            @if($ecart > 0)
                                <span class="badge badge-success">
                                    +{{ number_format($ecart, 0, ',', ' ') }} F CFA
                                </span>
                                <div class="small text-success">Excédent</div>
                            @elseif($ecart < 0)
                                <span class="badge badge-danger">
                                    {{ number_format($ecart, 0, ',', ' ') }} F CFA
                                </span>
                                <div class="small text-danger">Manquant</div>
                            @else
                                <span class="badge badge-secondary">0 F CFA</span>
                                <div class="small text-muted">Équilibré</div>
                            @endif
                            
                            @if($saisie->total_depenses > 0)
                                <br><small class="text-muted">(Dépenses: {{ number_format($saisie->total_depenses, 0, ',', ' ') }} F CFA)</small>
                            @endif
                        </td>
                        
                        <!-- COLONNE STATUT -->
                        <td class="text-center">
                            @if(isset($saisie->statut))
                                @switch($saisie->statut)
                                    @case('valide')
                                        <span class="badge badge-success">Validé</span>
                                        @break
                                    @case('rejete')
                                        <span class="badge badge-danger">Rejeté</span>
                                        @break
                                    @default
                                        <span class="badge badge-warning">En attente</span>
                                @endswitch
                                @if($saisie->validateur)
                                    <br><small class="text-muted">Par: {{ $saisie->validateur->name ?? '' }}</small>
                                @endif
                            @else
                                <span class="badge badge-warning">En attente</span>
                            @endif
                        </td>
                        
                        <!-- COLONNE ACTIONS -->
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <!-- Voir détails -->
                                <a href="{{ route('manager.history.show', $saisie->id) }}" 
                                   class="btn btn-info" title="Voir détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <!-- Modifier -->
                                <a href="{{ route('manager.saisie.edit', $saisie->id) }}" 
                                   class="btn btn-warning" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <!-- Télécharger PDF -->
                                <a href="{{ route('manager.saisie.pdf', $saisie->id) }}" 
                                   class="btn btn-success" title="Télécharger PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                
                               
                                
                                <!-- Supprimer -->
                                <button type="button" class="btn btn-danger" 
                                        onclick="confirmDelete({{ $saisie->id }})" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            
                            <!-- Formulaire de suppression caché -->
                            <form id="delete-form-{{ $saisie->id }}" 
                                  action="{{ route('manager.saisie.delete', $saisie->id) }}" 
                                  method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucune saisie enregistrée</h5>
                            <a href="{{ route('manager.index_form') }}" class="btn odyssee-btn-primary mt-2">
                                <i class="fas fa-plus"></i> Créer votre première saisie
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($saisies->hasPages())
    <div class="card-footer">
        <div class="float-right">
            {{ $saisies->links() }}
        </div>
        <div class="text-muted">
            Affichage de {{ $saisies->firstItem() }} à {{ $saisies->lastItem() }} sur {{ $saisies->total() }} saisies
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(id) {
        if (confirm('Voulez-vous vraiment supprimer cette saisie ? Cette action est irréversible.')) {
            event.preventDefault();
            document.getElementById('delete-form-' + id).submit();
        }
    }
</script>
@endpush