@extends('layouts.chief')

@section('title', 'Rapport Pompistes')
@section('page-icon', 'fa-users')
@section('page-title', 'Rapport des Gerants & Pompistes')
@section('page-subtitle', 'Performance et analyse des équipes')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('chief.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Rapport Pompistes</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-2"></i> Performance des Pompistes
                </h3>
                
                <!-- Filtres -->
                <div class="card-tools">
                    <form method="GET" action="{{ route('chief.rapports.pompistes') }}" class="form-inline">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="far fa-calendar-alt"></i>
                                </span>
                            </div>
                            <input type="date" name="start_date" class="form-control form-control-sm" 
                                   value="{{ $startDate }}" style="width: 120px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text">à</span>
                            </div>
                            <input type="date" name="end_date" class="form-control form-control-sm" 
                                   value="{{ $endDate }}" style="width: 120px;">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fas fa-filter"></i> Filtrer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Résumé des statistiques -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $pompistes->count() }}</h3>
                                <p>Gerants Actifs</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ number_format($pompistes->sum('total_ventes'), 0, ',', ' ') }}</h3>
                                <p>Total Ventes (FCFA)</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ $pompistes->sum('total_shifts') }}</h3>
                                <p>Total Shifts</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ number_format($pompistes->avg('total_ventes') ?? 0, 0, ',', ' ') }}</h3>
                                <p>Moyenne par Gerants</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tableau des pompistes -->
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr class="bg-light">
                                <th>#</th>
                                <th>Gerant de station</th>
                                <th>Station Assignée</th>
                                <th>Total Ventes (FCFA)</th>
                                <th>Nombre de Shifts</th>
                                <th>Moyenne par Shift</th>
                                <th>Écart Moyen (FCFA)</th>
                                <th>Performance</th>
                                <th>Actions</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pompistes->sortByDesc('total_ventes') as $index => $pompiste)
                                @php
                                    $avgPerShift = $pompiste->total_shifts > 0 
                                        ? $pompiste->total_ventes / $pompiste->total_shifts 
                                        : 0;
                                    
                                    // CORRECTION ICI : Utiliser la fonction passée depuis le controller
                                    $performance = $determinerPerformance($avgPerShift, $pompiste->moyenne_ecart ?? 0);
                                    
                                    $performanceClass = [
                                        'Excellent' => 'success',
                                        'Très bon' => 'info',
                                        'Bon' => 'primary',
                                        'Moyen' => 'warning',
                                        'À améliorer' => 'danger',
                                        'Inactif' => 'secondary'
                                    ][$performance] ?? 'secondary';
                                @endphp
                                
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="mr-3">
                                                <div class="avatar bg-{{ $index < 3 ? 'warning' : 'info' }} text-white d-flex align-items-center justify-content-center rounded-circle" 
                                                     style="width: 40px; height: 40px;">
                                                    {{ strtoupper(substr($pompiste->name, 0, 1)) }}
                                                </div>
                                            </div>
                                            <div>
                                                <strong>{{ $pompiste->name }}</strong><br>
                                                <small class="text-muted">{{ $pompiste->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($pompiste->station)
                                            <span class="badge badge-info">
                                                <i class="fas fa-gas-pump mr-1"></i>
                                                {{ $pompiste->station->nom }}
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">Non assigné</span>
                                        @endif
                                    </td>
                                    <td class="font-weight-bold text-primary">
                                        {{ number_format($pompiste->total_ventes, 0, ',', ' ') }}
                                    </td>
                                    <td>
                                        <span class="badge badge-dark">{{ $pompiste->total_shifts }}</span>
                                    </td>
                                    <td class="font-weight-bold">
                                        {{ number_format($avgPerShift, 0, ',', ' ') }}
                                    </td>
                                    <td>
                                        @php
                                            $ecart = $pompiste->moyenne_ecart ?? 0;
                                            $ecartClass = $ecart > 0 ? 'success' : ($ecart < 0 ? 'danger' : 'secondary');
                                        @endphp
                                        <span class="badge badge-{{ $ecartClass }}">
                                            {{ number_format($ecart, 0, ',', ' ') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $performanceClass }}">
                                            {{ $performance }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-info" 
                                                    data-toggle="modal" 
                                                    data-target="#detailsModal{{ $pompiste->id }}"
                                                    title="Voir détails">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                         
                                        </div>
                                        
                                        <!-- Modal Détails -->
                                        <div class="modal fade" id="detailsModal{{ $pompiste->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-info text-white">
                                                        <h5 class="modal-title">
                                                            <i class="fas fa-chart-line mr-2"></i>
                                                            Détails de performance - {{ $pompiste->name }}
                                                        </h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6>Informations générales</h6>
                                                                <table class="table table-sm">
                                                                    <tr>
                                                                        <td>Nom complet:</td>
                                                                        <td><strong>{{ $pompiste->name }}</strong></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Email:</td>
                                                                        <td>{{ $pompiste->email }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Station:</td>
                                                                        <td>
                                                                            @if($pompiste->station)
                                                                                {{ $pompiste->station->nom }}
                                                                            @else
                                                                                <span class="text-muted">Non assigné</span>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Date d'inscription:</td>
                                                                        <td>{{ $pompiste->created_at->format('d/m/Y') }}</td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                            
                                                            <div class="col-md-6">
                                                                <h6>Statistiques ({{ $startDate }} au {{ $endDate }})</h6>
                                                                <table class="table table-sm">
                                                                    <tr>
                                                                        <td>Total ventes:</td>
                                                                        <td class="font-weight-bold text-primary">
                                                                            {{ number_format($pompiste->total_ventes, 0, ',', ' ') }} FCFA
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Nombre de shifts:</td>
                                                                        <td>{{ $pompiste->total_shifts }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Moyenne/shift:</td>
                                                                        <td>
                                                                            {{ number_format($avgPerShift, 0, ',', ' ') }} FCFA
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Écart moyen:</td>
                                                                        <td>
                                                                            <span class="badge badge-{{ $ecartClass }}">
                                                                                {{ number_format($ecart, 0, ',', ' ') }} FCFA
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Performance:</td>
                                                                        <td>
                                                                            <span class="badge badge-{{ $performanceClass }}">
                                                                                {{ $performance }}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Derniers shifts -->
                                                        
                                                            @if($pompiste->shifts->count() > 0)
                                                                <h6 class="mt-3">Derniers shifts</h6>
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm table-hover">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Date</th>
                                                                                <th>Station</th>
                                                                                <th>Shift</th>
                                                                                <th>Pompiste</th>
                                                                                <th>Ventes</th>
                                                                                <th>Écart</th>
                                                                                <th>Statut</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach($pompiste->shifts->take(5) as $shift)
                                                                                @php
                                                                                    $statusBadge = [
                                                                                        'en_attente' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'En attente'],
                                                                                        'valide' => ['class' => 'success', 'icon' => 'check', 'text' => 'Validé'],
                                                                                        'rejete' => ['class' => 'danger', 'icon' => 'times', 'text' => 'Rejeté'],
                                                                                    ][$shift->statut] ?? ['class' => 'secondary', 'icon' => 'question', 'text' => 'Inconnu'];
                                                                                @endphp
                                                                                <tr>
                                                                                    <td>{{ $shift->date_shift->format('d/m/Y') }}</td>
                                                                                    <td>{{ $shift->station->nom ?? 'N/A' }}</td>
                                                                                    <td>{{ $shift->shift }}</td>
                                                                                    <td>
                                                                                        @if($shift->responsable)
                                                                                            <span class="font-weight-bold">{{ $shift->responsable }}</span>
                                                                                        @else
                                                                                            <span class="text-muted">-</span>
                                                                                        @endif
                                                                                    </td>
                                                                                    <td class="font-weight-bold">
                                                                                        {{ number_format($shift->total_ventes, 0, ',', ' ') }} F
                                                                                    </td>
                                                                                    <td>
                                                                                        @php
                                                                                            $shiftEcartClass = $shift->ecart_final > 0 ? 'success' : ($shift->ecart_final < 0 ? 'danger' : 'secondary');
                                                                                        @endphp
                                                                                        <span class="badge badge-{{ $shiftEcartClass }}">
                                                                                            {{ number_format($shift->ecart_final, 0, ',', ' ') }} F
                                                                                        </span>
                                                                                    </td>
                                                                                    <td>
                                                                                        <span class="badge badge-{{ $statusBadge['class'] }}">
                                                                                            <i class="fas fa-{{ $statusBadge['icon'] }} mr-1"></i>
                                                                                            {{ $statusBadge['text'] }}
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            @else
                                                                <div class="alert alert-info mt-3">
                                                                    <i class="fas fa-info-circle mr-2"></i>
                                                                    Aucun shift enregistré pour cette période.
                                                                </div>
                                                            @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                            Fermer
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
  
                
 
                
                <!-- Export et actions -->
      
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .avatar {
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .bg-gradient-info {
        background: linear-gradient(45deg, #17a2b8, #007bff) !important;
        color: white;
    }
    
    .bg-gradient-success {
        background: linear-gradient(45deg, #28a745, #20c997) !important;
        color: white;
    }
    
    .bg-gradient-warning {
        background: linear-gradient(45deg, #ffc107, #ff9800) !important;
        color: white;
    }
    
    .bg-gradient-purple {
        background: linear-gradient(45deg, #6f42c1, #e83e8c) !important;
        color: white;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .modal-lg {
        max-width: 900px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialiser les tooltips
    $('[title]').tooltip();
    
    // Graphique des pompistes
    @if($pompistes->count() > 0)
    const ctx = document.getElementById('pompistesChart').getContext('2d');
    const pompistesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($pompistes->sortByDesc('total_ventes')->pluck('name')),
            datasets: [
                {
                    label: 'Total Ventes (FCFA)',
                    data: @json($pompistes->sortByDesc('total_ventes')->pluck('total_ventes')),
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Nombre de Shifts',
                    data: @json($pompistes->sortByDesc('total_ventes')->pluck('total_shifts')),
                    backgroundColor: 'rgba(255, 159, 64, 0.7)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1,
                    type: 'line',
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label.includes('Ventes')) {
                                label += ': ' + context.parsed.y.toLocaleString('fr-FR') + ' FCFA';
                            } else {
                                label += ': ' + context.parsed.y + ' shifts';
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Ventes (FCFA)'
                    },
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('fr-FR') + ' F';
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Nombre de Shifts'
                    },
                    grid: {
                        drawOnChartArea: false,
                    }
                }
            }
        }
    });
    @endif
    
    // Fonction d'export Excel
    window.exportToExcel = function() {
        // Créer un tableau HTML
        let table = document.createElement('table');
        let thead = table.createTHead();
        let tbody = table.createTBody();
        
        // Ajouter l'en-tête
        let headerRow = thead.insertRow();
        let headers = ['Pompiste', 'Station', 'Total Ventes (FCFA)', 'Shifts', 'Moyenne/Shift', 'Écart Moyen', 'Performance'];
        
        headers.forEach(header => {
            let th = document.createElement('th');
            th.textContent = header;
            headerRow.appendChild(th);
        });
        
        // Ajouter les données
        @foreach($pompistes as $pompiste)
            let row = tbody.insertRow();
            @php
                $avgPerShift = $pompiste->total_shifts > 0 
                    ? $pompiste->total_ventes / $pompiste->total_shifts 
                    : 0;
                $performance = $determinerPerformance($avgPerShift, $pompiste->moyenne_ecart ?? 0);
            @endphp
            
            row.insertCell().textContent = '{{ $pompiste->name }}';
            row.insertCell().textContent = '{{ $pompiste->station->nom ?? "Non assigné" }}';
            row.insertCell().textContent = '{{ number_format($pompiste->total_ventes, 0, ",", " ") }}';
            row.insertCell().textContent = '{{ $pompiste->total_shifts }}';
            row.insertCell().textContent = '{{ number_format($avgPerShift, 0, ",", " ") }}';
            row.insertCell().textContent = '{{ number_format($pompiste->moyenne_ecart ?? 0, 0, ",", " ") }}';
            row.insertCell().textContent = '{{ $performance }}';
        @endforeach
        
        // Convertir en HTML et télécharger
        let html = table.outerHTML;
        let blob = new Blob([html], {type: 'application/vnd.ms-excel'});
        let url = URL.createObjectURL(blob);
        let a = document.createElement('a');
        a.href = url;
        a.download = 'rapport-pompistes-{{ date("Y-m-d") }}.xls';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    };
});
</script>
@endpush