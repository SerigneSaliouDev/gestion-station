
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,600,700" rel="stylesheet" />

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="{{ asset('css/oyesse.css') }}" rel="stylesheet">
    
    <!-- Custom CSS pour ODYSSEE -->
    <style>
        :root {
            --odyssee-primary: #FF7F00;
            --odyssee-dark: #333333;
            --odyssee-light: #f8f9fa;
        }

        .odyssee-bg-primary {
            background-color: var(--odyssee-primary) !important;
            color: white !important;
        }

        .odyssee-btn-primary {
            background-color: var(--odyssee-primary);
            border-color: var(--odyssee-primary);
            color: white;
        }

        .odyssee-btn-primary:hover {
            background-color: #e67300;
            border-color: #e67300;
            color: white;
        }

        .odyssee-text-primary {
            color: var(--odyssee-primary) !important;
        }

        .main-header.navbar {
            background-color: var(--odyssee-dark) !important;
        }

        .main-sidebar {
            background-color: var(--odyssee-dark) !important;
        }

        .brand-link {
            background-color: var(--odyssee-dark) !important;
        }

        .nav-sidebar > .nav-item > .nav-link.active {
            background-color: var(--odyssee-primary) !important;
        }
    </style>

    @stack('styles')
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-dark">
        <!-- Logo -->
        <a href="{{ route('manager.index_form') }}" class="navbar-brand">
            <i class="fas fa-gas-pump"></i>
            <span class="brand-text font-weight-light">ODYSSEE ENERGIE SA</span>
        </a>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user"></i> {{ Auth::user()->name ?? 'Utilisateur' }}
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">

                       <a class="dropdown-item" href="{{ route('profile.show') }}">
                                <i class="fas fa-user mr-2"></i>Profil
                        </a>
                   
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="{{ route('manager.index_form') }}" class="brand-link">
            <i class="fas fa-gas-pump brand-image"></i>
            <span class="brand-text font-weight-light" style="color: var(--odyssee-primary);">GÉRANT</span>
        </a>
        

        <!-- Sidebar Menu -->
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="{{ route('manager.index_form') }}" class="nav-link {{ request()->routeIs('manager.index_form') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Saisie Index/Ventes</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('manager.history') }}" class="nav-link {{ request()->routeIs('manager.history') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-history"></i>
                            <p>Historique des Saisies</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('manager.reports') }}" class="nav-link {{ request()->routeIs('manager.reports') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>Rapports</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('manager.stocks.dashboard') }}" class="nav-link {{ request()->routeIs('manager.stocks.dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-oil-can"></i>
                            <p>Suivi des Stocks</p>
                        </a>
                    </li>
                     

                 
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">@yield('page-title', 'Tableau de Bord')</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            @yield('breadcrumb')
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="content">
            <div class="container-fluid">
                <!-- Messages de session -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Attention: Veuillez corriger les erreurs suivantes :</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

              <div class="content-body">
                @if(isset($slot))
                    {{ $slot }}
                @else
                    @yield('content')
                @endif
            </div>
        
       
    
        </main>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <strong>Copyright &copy; {{ date('Y') }} ODYSSEE ENERGIE SA.</strong> Tous droits réservés.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0.0
        </div>
    </footer>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>

@stack('scripts')
</body>
</html>