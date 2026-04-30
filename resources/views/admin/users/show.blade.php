@extends('layouts.admin')

@section('title', 'Détails Utilisateur: ' . $user->name)

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user text-primary"></i> Détails Utilisateur
        </h1>
        <div class="btn-group">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Modifier
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <!-- Informations utilisateur -->
    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-id-card"></i> Profil
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <div class="icon-circle bg-{{ $user->getAvatarColor() }} mx-auto" style="width: 100px; height: 100px; font-size: 2rem;">
                            <span class="text-white">{{ $user->getInitial() }}</span>
                        </div>
                        <h4 class="mt-3">{{ $user->name }}</h4>
                        <p class="text-muted">{{ $user->email }}</p>
                        
                        @if($user->roles->isNotEmpty())
                        <span class="badge badge-{{ $user->getRoleBadgeColor() }}">
                            <i class="{{ $user->getRoleIcon() }} mr-1"></i>
                            {{ $user->getRoleDisplayName() }}
                        </span>
                        @endif
                        
                        <div class="mt-2">
                            @if($user->isActive())
                            <span class="badge badge-success">
                                <i class="fas fa-check-circle"></i> Actif
                            </span>
                            @else
                            <span class="badge badge-danger">
                                <i class="fas fa-times-circle"></i> Inactif
                            </span>
                            @endif
                            
                            @if($user->email_verified_at)
                            <span class="badge badge-info ml-1">
                                <i class="fas fa-check"></i> Email vérifié
                            </span>
                            @endif
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="text-left">
                        <p><strong><i class="fas fa-phone mr-2"></i>Téléphone:</strong><br>
                            {{ $user->getFormattedPhone() ?? 'Non renseigné' }}</p>
                        
                        <p><strong><i class="fas fa-gas-pump mr-2"></i>Station:</strong><br>
                            @if($user->station)
                                {{ $user->station->nom }} ({{ $user->station->code }})
                            @else
                                Non assigné
                            @endif
                        </p>
                        
                        <p><strong><i class="fas fa-calendar-plus mr-2"></i>Créé le:</strong><br>
                            {{ $user->created_at->format('d/m/Y H:i') }}</p>
                        
                        <p><strong><i class="fas fa-clock mr-2"></i>Dernière connexion:</strong><br>
                            @if($user->last_login_at)
                                {{ $user->last_login_at->format('d/m/Y H:i') }}
                                @if($user->last_login_ip)
                                    <br><small class="text-muted">IP: {{ $user->last_login_ip }}</small>
                                @endif
                            @else
                                Jamais connecté
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Shifts
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $stats['total_shifts'] ?? 0 }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Ventes Totales
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ number_format($stats['total_sales'] ?? 0, 0, ',', ' ') }} FCFA
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Shifts en attente
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $stats['pending_shifts'] ?? 0 }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Écart moyen
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ number_format($stats['avg_ecart'] ?? 0, 0, ',', ' ') }} FCFA
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-balance-scale fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Derniers shifts -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Derniers Shifts
                    </h6>
                    <a href="{{ route('admin.shifts.index', ['user_id' => $user->id]) }}" class="btn btn-sm btn-primary">
                        Voir tous
                    </a>
                </div>
                <div class="card-body">
                    @if($user->shifts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Shift</th>
                                    <th>Station</th>
                                    <th>Ventes</th>
                                    <th>Écart</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->shifts as $shift)
                                <tr>
                                    <td>{{ $shift->date_shift->format('d/m/Y') }}</td>
                                    <td>{{ $shift->shift }}</td>
                                    <td>{{ $shift->station->nom ?? 'N/A' }}</td>
                                    <td>{{ number_format($shift->total_ventes, 0, ',', ' ') }} FCFA</td>
                                    <td class="{{ $shift->ecart_final < 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($shift->ecart_final, 0, ',', ' ') }} FCFA
                                    </td>
                                    <td>
                                        @if($shift->statut == 'valide')
                                        <span class="badge badge-success">Validé</span>
                                        @elseif($shift->statut == 'en_attente')
                                        <span class="badge badge-warning">En attente</span>
                                        @else
                                        <span class="badge badge-danger">Rejeté</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucun shift trouvé pour cet utilisateur</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Activités récentes -->
            @if($recentActivities->count() > 0)
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list-alt"></i> Activités Récentes
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($recentActivities as $activity)
                        <div class="timeline-item mb-3">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between">
                                    <h6 class="mb-1">{{ $activity->description }}</h6>
                                    <small class="text-muted">{{ $activity->created_at->format('H:i') }}</small>
                                </div>
                                <small class="text-muted">{{ $activity->created_at->format('d/m/Y') }}</small>
                                @if($activity->details)
                                <div class="mt-1">
                                    <button class="btn btn-sm btn-outline-info" type="button" 
                                            data-toggle="collapse" 
                                            data-target="#details{{ $activity->id }}">
                                        <i class="fas fa-info-circle"></i> Détails
                                    </button>
                                    <div class="collapse mt-2" id="details{{ $activity->id }}">
                                        <div class="card card-body">
                                            <pre class="mb-0"><code>{{ json_encode(json_decode($activity->details), JSON_PRETTY_PRINT) }}</code></pre>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .icon-circle {
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline-item {
        position: relative;
    }
    .timeline-marker {
        position: absolute;
        left: -30px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: #4e73df;
    }
    .timeline-content {
        padding-bottom: 20px;
        border-left: 2px solid #e3e6f0;
        padding-left: 20px;
    }
    .timeline-item:last-child .timeline-content {
        border-left: none;
    }
</style>
@endpush