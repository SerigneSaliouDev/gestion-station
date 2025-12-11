<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
    <div class="container">
        <!-- Logo OEDYSSEE -->
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('images/logo-oyssee.png') }}" alt="OEYSSEE ENERGIE SA" height="40" class="d-inline-block align-top">
            <span class="ml-2 font-weight-bold text-primary">OEYSSEE ENERGIE SA</span>
        </a>

        <!-- Reste du code de navigation -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav me-auto">
                @auth
                    @if(Auth::user()->hasRole('manager'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('manager.index_form') }}">
                                <i class="fas fa-tachometer-alt mr-1"></i>Tableau de Bord Manager
                            </a>
                        </li>
                    @endif
                    <!-- Autres liens selon les rôles -->
                @endauth
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ms-auto">
                <!-- Authentication Links -->
                @guest
                    @if (Route::has('login'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Connexion') }}</a>
                        </li>
                    @endif
                @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle mr-1"></i>{{ Auth::user()->name }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="{{ route('profile.show') }}">
                                <i class="fas fa-user mr-2"></i>Profil
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                                </button>
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>