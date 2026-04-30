<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Administration') - ODYSSE ENERGIE</title>
    
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <!-- Theme ODYSSE -->
    <style>
        :root {
            --primary-color: #1a3c5e;
            --secondary-color: #f39c12;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        /* Personnalisation AdminLTE pour ODYSSE */
        .main-header .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #0d2845 100%);
        }
        
        .main-header .navbar-brand,
        .main-header .navbar-nav .nav-link {
            color: white !important;
        }
        
        .main-sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, #0d2845 100%);
        }
        
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active,
        .sidebar-light-primary .nav-sidebar > .nav-item > .nav-link.active {
            background-color: var(--secondary-color);
            color: white;
            border-left: 4px solid white;
        }
        
        .brand-link {
            background: var(--primary-color) !important;
            border-bottom: 2px solid var(--secondary-color);
        }
        
        .brand-link .brand-text {
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .brand-link .brand-text span {
            color: var(--secondary-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #0d2845;
            border-color: #0d2845;
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .bg-primary {
            background-color: var(--primary-color) !important;
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        .border-primary {
            border-color: var(--primary-color) !important;
        }
        
        /* Cartes personnalisées */
        .card-primary:not(.card-outline) > .card-header {
            background: linear-gradient(to right, var(--primary-color), #0d2845);
            color: white;
        }
        
        .card-warning:not(.card-outline) > .card-header {
            background: linear-gradient(to right, var(--secondary-color), #e67e22);
            color: white;
        }
        
        /* Badges ODYSSE */
        .badge-od-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .badge-od-secondary {
            background-color: var(--secondary-color);
            color: white;
        }
        
        /* User panel personnalisé */
        .user-panel .info {
            color: white;
        }
        
        /* Breadcrumb */
        .breadcrumb {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 8px 15px;
        }
    </style>
    
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="https://adminlte.io/themes/v3/dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
    </div>

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
            
            <li class="nav-item d-none d-sm-inline-block">
                <a href="#" class="nav-link">Contact</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
          
 
            
            <!-- User Account Menu -->
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=1a3c5e&color=fff&size=128" 
                         class="user-image img-circle elevation-2" alt="User Image">
                    <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <!-- User image -->
                    <li class="user-header bg-primary">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=f39c12&color=fff&size=150" 
                             class="img-circle elevation-2" alt="User Image">
                        <p>
                            {{ Auth::user()->name }}
                            <small>Membre depuis {{ Auth::user()->created_at->format('M Y') }}</small>
                            <small>
                                @foreach(Auth::user()->roles as $role)
                                    <span class="badge badge-light">{{ $role->name }}</span>
                                @endforeach
                            </small>
                        </p>
                    </li>
                    
                    <!-- Menu Body -->
                    <li class="user-body">
                        
                       
                        <form action="{{ route('logout') }}" method="POST" class="d-inline float-right">
                            @csrf
                            <a href="{{ route('profile.show') }}" class="btn btn-default btn-flat mr-2">
                                <i class="fas fa-user mr-2"></i>Profil
                            </a>

                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-default btn-flat">
                                    <i class="fas fa-sign-out-alt mr-1"></i> Déconnexion
                                </button>
                            </form>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</div>
</body>
            
            <!-- Control Sidebar Toggle Button -->
            <li class="nav-item">
                <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
                    <i class="fas fa-th-large"></i>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="{{ route('admin.users.index') }}" class="brand-link">
            <img src="https://ui-avatars.com/api/?name=ODYSSE&background=f39c12&color=1a3c5e&bold=true" 
                 alt="ODYSSE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">ODYSSE <span class="text-warning">ADMIN</span></span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=f39c12&color=1a3c5e" 
                         class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block">{{ Auth::user()->name }}</a>
                    <span class="badge badge-warning">
                        @foreach(Auth::user()->roles as $role)
                            {{ $role->name }}
                        @endforeach
                    </span>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                    <!-- Gestion des Utilisateurs -->
                    <li class="nav-item {{ request()->routeIs('admin.users.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>
                                Utilisateurs
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.users.index') }}" 
                                   class="nav-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Liste des utilisateurs</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.users.create') }}" 
                                   class="nav-link {{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Créer un utilisateur</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Gestion des Stations -->
                    <li class="nav-item {{ request()->routeIs('admin.stations.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('admin.stations.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-gas-pump"></i>
                            <p>
                                Stations
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.stations.index') }}" 
                                   class="nav-link {{ request()->routeIs('admin.stations.index') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Liste des stations</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.stations.create') }}" 
                                   class="nav-link {{ request()->routeIs('admin.stations.create') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Créer une station</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Supervision -->
                    <li class="nav-item {{ request()->routeIs('admin.supervision.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('admin.supervision.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-eye"></i>
                            <p>
                                Supervision
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.supervision.pricing') }}" 
                                   class="nav-link {{ request()->routeIs('admin.supervision.pricing') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Tarification</p>
                                </a>
                            </li>
                            <!--
                            <li class="nav-item">
                                <a href="{{ route('admin.supervision.data.corrections') }}" 
                                   class="nav-link {{ request()->routeIs('admin.supervision.data.corrections') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Corrections de données</p>
                                </a>
                            </li> -->
                            <li class="nav-item">
                                <a href="{{ route('admin.supervision.maintenance.index') }}" 
                                   class="nav-link {{ request()->routeIs('admin.supervision.maintenance.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Maintenance</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Reporting -->
                    <li class="nav-item {{ request()->routeIs('admin.reports.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>
                                Reporting
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                          <!--  <li class="nav-item">
                                <a href="{{ route('admin.users.index') }}" 
                                   class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Tableau de bord BI</p>
                                </a>
                            </li> -->
                            
                          
                            <li class="nav-item">
                                <a href="{{ route('admin.reports.station.comparison') }}" 
                                   class="nav-link {{ request()->routeIs('admin.reports.station.comparison') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Comparaison stations</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Système -->
                    <li class="nav-item {{ request()->routeIs('admin.system.logs') ? 'menu-open' : '' }}">
                       <a href="#" class="nav-link {{ request()->routeIs('admin.system.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>
                                Système
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                        
                            <li class="nav-item">
                                <a href="{{ route('admin.debug.info') }}" 
                                   class="nav-link {{ request()->routeIs('admin.debug.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Debug info</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Séparateur -->
                    <li class="nav-header">AUTRES</li>
                    
                   
                    
                    <li class="nav-item">
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                           class="nav-link">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>Déconnexion</p>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>@yield('page-title', 'Tableau de bord')</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Admin</a></li>
                            @yield('breadcrumb')
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Alert Messages -->
                @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-check"></i> Succès !</h5>
                    {{ session('success') }}
                </div>
                @endif
                
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> Erreur !</h5>
                    {{ session('error') }}
                </div>
                @endif
                
                @if(session('warning'))
                <div class="alert alert-warning alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Attention !</h5>
                    {{ session('warning') }}
                </div>
                @endif
                
                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Contenu principal -->
         <div class="content-body">
                @if(isset($slot))
                    {{ $slot }}
                @else
                    @yield('content')
                @endif
            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Footer -->
    <footer class="main-footer">
        <div class="float-right d-none d-sm-inline">
            Version 1.0.0
        </div>
        <strong>Copyright &copy; {{ date('Y') }} <a href="#">ODYSSE ENERGIE SA</a>.</strong> Tous droits réservés.
    </footer>

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
        <div class="p-3">
            <h5>Statistiques rapides</h5>
            <p>Utilisateurs: {{ \App\Models\User::count() }}</p>
            <p>Stations: {{ \App\Models\Station::count() ?? 0 }}</p>
            <p>Saisies en attente: {{ \App\Models\ShiftSaisie::where('statut', 'en_attente')->count() }}</p>
        </div>
    </aside>
    <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Custom Scripts -->
<script>
    $(document).ready(function() {
        // Initialiser les tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Initialiser les popovers
        $('[data-toggle="popover"]').popover();
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    });
</script>

@stack('scripts')
</body>
</html>