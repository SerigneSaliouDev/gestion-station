<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo $__env->yieldContent('title', config('app.name', 'Laravel')); ?> - Chief</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,600,700" rel="stylesheet" />

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS pour ODYSSEE -->
    <style>
        :root {
            --odyssee-primary: #FF7F00;
            --odyssee-dark: #333333;
            --odyssee-light: #f8f9fa;
            --chief-color: #2c3e50;
        }

        .odyssee-bg-primary {
            background-color: var(--odyssee-primary) !important;
            color: white !important;
        }

        .chief-bg-primary {
            background-color: var(--chief-color) !important;
            color: white !important;
        }

        .odyssee-btn-primary {
            background-color: var(--odyssee-primary);
            border-color: var(--odyssee-primary);
            color: white;
        }

        .chief-btn-primary {
            background-color: var(--chief-color);
            border-color: var(--chief-color);
            color: white;
        }

        .odyssee-btn-primary:hover {
            background-color: #e67300;
            border-color: #e67300;
            color: white;
        }

        .chief-btn-primary:hover {
            background-color: #1a252f;
            border-color: #1a252f;
            color: white;
        }

        .odyssee-text-primary {
            color: var(--odyssee-primary) !important;
        }

        .chief-text-primary {
            color: var(--chief-color) !important;
        }

        .main-header.navbar {
            background-color: var(--chief-color) !important;
        }

        .main-sidebar {
            background-color: var(--chief-color) !important;
        }

        .brand-link {
            background-color: var(--chief-color) !important;
        }

        .nav-sidebar > .nav-item > .nav-link.active {
            background-color: var(--odyssee-primary) !important;
        }

        /* Badges pour les notifications */
        .badge-notification {
            position: absolute;
            top: 5px;
            right: 5px;
            font-size: 0.6rem;
            padding: 2px 5px;
        }

        /* Cartes de statistiques */
        .stats-card {
            border-radius: 10px;
            transition: transform 0.3s;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .stats-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }

        /* Table responsive */
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }

        /* Boutons d'action */
        .btn-action {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
        }

        /* Style pour les rapports PDF */
        .pdf-icon {
            color: #e74c3c;
        }
        
        .pdf-link:hover .pdf-icon {
            color: #c0392b;
        }
    </style>

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-dark chief-bg-primary">
        <!-- Logo -->
        <a href="<?php echo e(route('chief.dashboard')); ?>" class="navbar-brand">
            <i class="fas fa-user-shield"></i>
            <span class="brand-text font-weight-light">ODYSSEE ENERGIE SA - CHIEF</span>
        </a>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Notifications -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#" id="notificationDropdown">
                    <i class="far fa-bell"></i>
                    <span class="badge badge-warning navbar-badge" id="notificationBadge">0</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">
                        <i class="fas fa-clock text-warning mr-2"></i>
                        <span id="notificationCount">0 validation(s) en attente</span>
                    </span>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo e(route('chief.validations')); ?>" class="dropdown-item">
                        <i class="fas fa-clipboard-check mr-2"></i> Voir les validations
                    </a>
                  
            </li>

            <!-- User menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user"></i> <?php echo e(Auth::user()->name ?? 'Chief'); ?>

                    <i class="fas fa-caret-down ml-1"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <div class="dropdown-header text-center">
                        <strong><?php echo e(Auth::user()->name ?? 'Chief'); ?></strong><br>
                        <small>Chargé des Opérations</small>
                    </div>
                    <div class="dropdown-divider"></div>
                  
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                         <a href="<?php echo e(route('profile.show')); ?>" class="btn btn-default btn-flat mr-2">
                                <i class="fas fa-user mr-2"></i>Profil
                            </a>

                            <form action="<?php echo e(route('logout')); ?>" method="POST" class="m-0">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-default btn-flat">
                                    <i class="fas fa-sign-out-alt mr-1"></i> Déconnexion
                                </button>
                     </form>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="<?php echo e(route('chief.dashboard')); ?>" class="brand-link">
            <i class="fas fa-user-shield brand-image"></i>
            <span class="brand-text font-weight-light" style="color: var(--odyssee-primary);">CHIEF</span>
        </a>

        <!-- Sidebar Menu -->
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <i class="fas fa-user-circle fa-2x img-circle" style="color: var(--odyssee-primary);"></i>
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?php echo e(Auth::user()->name ?? 'Chief'); ?></a>
                    <small class="text-muted">
                        <i class="fas fa-badge-check mr-1"></i> Chargé des Opérations
                    </small>
                </div>
            </div>

            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="<?php echo e(route('chief.dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('chief.dashboard') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <!-- Validations -->
                    <li class="nav-item">
                        <a href="<?php echo e(route('chief.validations')); ?>" class="nav-link <?php echo e(request()->routeIs('chief.validations*') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-clipboard-check"></i>
                            <p>Validations</p>
                            <span class="badge badge-warning badge-pill badge-notification" id="sidebarBadge">0</span>
                        </a>
                    </li>

                    <!-- Stations -->
                    <li class="nav-item">
                        <a href="<?php echo e(route('chief.stations')); ?>" class="nav-link <?php echo e(request()->routeIs('chief.stations*') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-gas-pump"></i>
                            <p>Gestion des Stations</p>
                        </a>
                    </li>

                    <!-- Rapports -->
                    <li class="nav-item has-treeview <?php echo e(request()->routeIs('chief.rapports*') || request()->is('pdf/*') ? 'menu-open' : ''); ?>">
                        <a href="#" class="nav-link <?php echo e(request()->routeIs('chief.rapports*') || request()->is('pdf/*') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>
                                Rapports
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <!-- Rapports Standards -->
                            <li class="nav-item">
                                <a href="<?php echo e(route('chief.rapports.stations')); ?>" class="nav-link <?php echo e(request()->routeIs('chief.rapports.stations') ? 'active' : ''); ?>">
                                    <i class="far fa-chart-bar nav-icon"></i>
                                    <p>Rapport Stations</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('chief.rapports.pompistes')); ?>" class="nav-link <?php echo e(request()->routeIs('chief.rapports.pompistes') ? 'active' : ''); ?>">
                                    <i class="far fa-user-tie nav-icon"></i>
                                    <p>Analyse Gérants</p>
                                </a>
                            </li>

                            <!-- Page principale PDF -->
                           <li class="nav-item">
                                <a href="<?php echo e(route('pdf.station.report', ['stationId' => $stationId ?? 'null'])); ?>" 
                                class="nav-link <?php echo e(request()->routeIs('pdf.station.report') ? 'active' : ''); ?>">
                                    <i class="far fa-file-pdf nav-icon pdf-icon text-danger"></i>
                                    <p>Générer PDF Rapport</p>
                                </a>
                            </li>

                            <!-- ============================ -->
                            <!-- SOUS-MENU RAPPORTS PDF DIRECTS -->
                            <!-- ============================ -->
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="far fa-file-alt nav-icon"></i>
                                    <p>
                                        Rapports PDF Directs
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview" style="margin-left: 15px;">
                                    <!-- Rapport Station -->
                                    <li class="nav-item">
                                        <a href="<?php echo e(route('pdf.station.report')); ?>" 
                                           class="nav-link pdf-link <?php echo e(request()->routeIs('pdf.station.report') ? 'active' : ''); ?>"
                                           target="_blank">
                                            <i class="fas fa-gas-pump nav-icon" style="font-size: 0.8rem;"></i>
                                            <p>Rapport Station</p>
                                        </a>
                                    </li>
                                    
                                    <!-- Rapport Réconciliation -->
                                    <li class="nav-item">
                                        <a href="<?php echo e(route('pdf.reconciliation.report')); ?>" 
                                           class="nav-link pdf-link <?php echo e(request()->routeIs('pdf.reconciliation.report') ? 'active' : ''); ?>"
                                           target="_blank">
                                            <i class="fas fa-balance-scale nav-icon" style="font-size: 0.8rem;"></i>
                                            <p>Mouvements Stock</p>
                                        </a>
                                    </li>
                                    
                                    <!-- Rapport Inventaire -->
                                    <li class="nav-item">
                                        <a href="<?php echo e(route('pdf.inventory.report')); ?>" 
                                           class="nav-link pdf-link <?php echo e(request()->routeIs('pdf.inventory.report') ? 'active' : ''); ?>"
                                           target="_blank">
                                            <i class="fas fa-clipboard-list nav-icon" style="font-size: 0.8rem;"></i>
                                            <p>Inventaire</p>
                                        </a>
                                    </li>
                                    
                                    <!-- Ventes par Pompiste -->
                                    <li class="nav-item">
                                        <a href="<?php echo e(route('pdf.sales-by-pump.report')); ?>" 
                                           class="nav-link pdf-link <?php echo e(request()->routeIs('pdf.sales-by-pump.report') ? 'active' : ''); ?>"
                                           target="_blank">
                                            <i class="fas fa-user-tie nav-icon" style="font-size: 0.8rem;"></i>
                                            <p>Ventes/Stations</p>
                                        </a>
                                    </li>
                                    
                                    <!-- Rapport Shift -->
                                    <li class="nav-item">
                                        <a href="#" 
                                           class="nav-link pdf-link"
                                           onclick="promptShiftId()">
                                            <i class="fas fa-file-alt nav-icon" style="font-size: 0.8rem;"></i>
                                            <p>Rapport/Pompiste</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- FIN SOUS-MENU RAPPORTS PDF -->
                        </ul>
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
                        <h1 class="m-0">
                            <i class="fas <?php echo $__env->yieldContent('page-icon', 'fa-tachometer-alt'); ?> mr-2 chief-text-primary"></i>
                            <?php echo $__env->yieldContent('page-title', 'Tableau de Bord Chief'); ?>
                        </h1>
                        <small class="text-muted"><?php echo $__env->yieldContent('page-subtitle', 'Interface de supervision des opérations'); ?></small>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('chief.dashboard')); ?>">Chief</a></li>
                            <?php echo $__env->yieldContent('breadcrumb'); ?>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="content">
            <div class="container-fluid">
                <!-- Messages de session -->
                <?php if(session('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo e(session('success')); ?>

                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if(session('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo e(session('error')); ?>

                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if($errors->any()): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Attention: Veuillez corriger les erreurs suivantes :</strong>
                        <ul class="mb-0 mt-2">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Statistiques rapides -->
                <?php if (! empty(trim($__env->yieldContent('stats')))): ?>
                    <div class="row mb-3">
                        <?php echo $__env->yieldContent('stats'); ?>
                    </div>
                <?php endif; ?>

                <!-- Page Content -->
              <div class="content-body">
                <?php if(isset($slot)): ?>
                    <?php echo e($slot); ?>

                <?php else: ?>
                    <?php echo $__env->yieldContent('content'); ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <strong>Copyright &copy; <?php echo e(date('Y')); ?> ODYSSEE ENERGIE SA - Interface Chief.</strong> Tous droits réservés.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0.0 | <span id="currentTime"><?php echo e(date('H:i')); ?></span>
        </div>
    </footer>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>

<!-- Scripts personnalisés -->
<script>
    // Mettre à jour l'heure en temps réel
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('fr-FR', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        $('#currentTime').text(timeString);
    }

    // Récupérer le nombre de validations en attente
    function updatePendingCount() {
        $.ajax({
            url: '<?php echo e(route("chief.pending-count")); ?>',
            type: 'GET',
            success: function(data) {
                const count = data.count || 0;
                
                // Mettre à jour les badges
                $('#notificationBadge').text(count);
                $('#sidebarBadge').text(count);
                
                // Afficher/masquer les badges
                if (count > 0) {
                    $('#notificationBadge').show();
                    $('#sidebarBadge').show();
                    $('#notificationCount').html('<strong>' + count + ' validation(s) en attente</strong>');
                } else {
                    $('#notificationBadge').hide();
                    $('#sidebarBadge').hide();
                    $('#notificationCount').text('Aucune validation en attente');
                }
            },
            error: function() {
                console.error('Erreur lors de la récupération des validations');
            }
        });
    }

    // Fonction pour demander l'ID du shift
    function promptShiftId() {
        const shiftId = prompt('Entrez l\'ID du shift :');
        if (shiftId) {
            // Vérifier si c'est un nombre
            if (!isNaN(shiftId) && shiftId.trim() !== '') {
                // Rediriger vers le rapport shift
                window.open('/pdf/shift-report/' + shiftId, '_blank');
            } else {
                alert('Veuillez entrer un ID valide (nombre)');
            }
        }
    }

    // Initialisation
    $(document).ready(function() {
        // Mettre à jour l'heure
        updateTime();
        setInterval(updateTime, 60000); // Toutes les minutes
        
        // Charger le nombre de validations
        updatePendingCount();
        
        // Rafraîchir toutes les 30 secondes
        setInterval(updatePendingCount, 30000);
        
        // Tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Confirmation pour les actions importantes
        $('.confirm-action').on('click', function(e) {
            if (!confirm($(this).data('confirm') || 'Êtes-vous sûr de vouloir effectuer cette action ?')) {
                e.preventDefault();
            }
        });

        // Ouvrir les rapports PDF dans un nouvel onglet
        $('a.pdf-link[href*="pdf."]').on('click', function(e) {
            if (!$(this).hasClass('no-target')) {
                e.preventDefault();
                window.open($(this).attr('href'), '_blank');
            }
        });
    });
</script>

<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH C:\Users\LENOVO\station-gestion-app-clean\resources\views/layouts/chief.blade.php ENDPATH**/ ?>