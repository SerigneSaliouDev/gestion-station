@extends('layouts.admin')

@section('title', 'Gestion des Stations')
@section('page-title', 'Liste des Stations')

@section('breadcrumb')
<li class="breadcrumb-item active">Stations</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Toutes les stations</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.stations.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouvelle station
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nom</th>
                                <th>Code</th>
                                <th>Localisation</th>
                                <th>Manager</th>
                                <th>Email</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stations as $station)
                            <tr>
                                <td>{{ $station->id }}</td>
                                <td>
                                    <strong>{{ $station->nom }}</strong><br>
                                    <small class="text-muted">{{ $station->telephone ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $station->code }}</span>
                                </td>
                                <td>
                                    {{ $station->ville ?? 'N/A' }}<br>
                                    <small class="text-muted">{{ $station->adresse ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    @if($station->manager)
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                <div class="icon-circle bg-primary">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">{{ $station->manager->name }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-danger">
                                            <i class="fas fa-exclamation-circle"></i> Non assigné
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($station->manager)
                                        <div class="text-primary">{{ $station->manager->email }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($station->statut == 'actif')
                                        <span class="badge badge-success">Actif</span>
                                    @elseif($station->statut == 'inactif')
                                        <span class="badge badge-danger">Inactif</span>
                                    @elseif($station->statut == 'maintenance')
                                        <span class="badge badge-warning">Maintenance</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $station->statut }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.stations.show', $station) }}" 
                                           class="btn btn-sm btn-info" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.stations.edit', $station) }}" 
                                           class="btn btn-sm btn-primary" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger" 
                                                data-toggle="modal" 
                                                data-target="#deleteModal{{ $station->id }}"
                                                title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Modal de suppression -->
                                    <div class="modal fade" id="deleteModal{{ $station->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Supprimer la station</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Êtes-vous sûr de vouloir supprimer la station <strong>{{ $station->nom }}</strong> ?</p>
                                                    <div class="alert alert-warning">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        Cette action est irréversible.
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                    <form action="{{ route('admin.stations.destroy', $station) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Supprimer</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-gas-pump fa-3x mb-3"></i>
                                        <p>Aucune station trouvée</p>
                                        <a href="{{ route('admin.stations.create') }}" class="btn btn-primary">
                                            Créer la première station
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($stations->hasPages())
                <div class="card-footer">
                    {{ $stations->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.icon-circle {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Confirmation de suppression
        $('.btn-danger').on('click', function(e) {
            var stationName = $(this).closest('tr').find('td:nth-child(2) strong').text();
            if (!confirm('Êtes-vous sûr de vouloir supprimer la station "' + stationName + '" ?')) {
                e.preventDefault();
            }
        });
        
        // Filtrage des stations
        $('#searchStation').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('table tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>
@endpush