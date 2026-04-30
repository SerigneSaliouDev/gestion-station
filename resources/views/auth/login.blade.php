<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion | ODYSSEE ENERGIE</title>

    <!-- AdminLTE -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/css/adminlte.min.css') }}">

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f5f7fb;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', 'Source Sans Pro', Arial, sans-serif;
        }

        .login-wrapper {
            width: 100%;
            max-width: 1300px;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            display: flex;
            flex-wrap: wrap;
            margin: 20px;
        }

        .login-brand {
            flex: 1;
            background: linear-gradient(135deg, #FF8C00 0%, #FF6B00 100%);
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            color: white;
            min-width: 280px;
        }

        .brand-content {
            max-width: 400px;
            margin: 0 auto;
            width: 100%;
            text-align: center;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .company-logo {
            max-width: 200px;
            width: auto;
            height: auto;
            max-height: 120px;
            object-fit: contain;
            margin: 0 auto;
            display: block;
        }

        .brand-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .brand-description {
            font-size: 16px;
            line-height: 1.5;
            opacity: 0.95;
            margin-bottom: 40px;
        }

        .brand-features {
            list-style: none;
            text-align: left;
            display: inline-block;
            margin-top: 20px;
        }

        .brand-features li {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
        }

        .brand-features li i {
            font-size: 18px;
            width: 24px;
        }

        .login-form-container {
            flex: 1;
            padding: 60px 50px;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 380px;
        }

        .form-title {
            color: #1a1a2e;
            margin-bottom: 10px;
            font-size: 32px;
            font-weight: 700;
        }

        .form-subtitle {
            color: #666;
            margin-bottom: 35px;
            font-size: 15px;
            border-left: 3px solid #FF8C00;
            padding-left: 15px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .form-control {
            width: 100%;
            padding: 14px 45px 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s;
            background: #fafbfc;
        }

        .form-control:focus {
            border-color: #FF8C00;
            outline: none;
            background: white;
            box-shadow: 0 0 0 3px rgba(255, 140, 0, 0.1);
        }

        .input-group-append {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            display: flex;
            align-items: center;
            padding-right: 16px;
        }

        .input-group-text {
            background: transparent;
            border: none;
            color: #94a3b8;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-wrapper input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #FF8C00;
        }

        .checkbox-wrapper label {
            margin: 0;
            color: #555;
            font-size: 14px;
            cursor: pointer;
        }

        .forgot-link {
            color: #FF8C00;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .btn-primary {
            width: 100%;
            padding: 14px;
            background: #FF8C00;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary:hover {
            background: #E67E00;
            transform: translateY(-1px);
            box-shadow: 0 10px 20px -5px rgba(255, 140, 0, 0.3);
        }

        .register-link {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #edf2f7;
        }

        .register-link p {
            color: #64748b;
            font-size: 14px;
        }

        .register-link a {
            color: #FF8C00;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 12px;
            margin-bottom: 25px;
            border: none;
            padding: 12px 16px;
            font-size: 14px;
        }

        .alert-danger {
            background: #fee2e2;
            color: #dc2626;
        }

        .copyright {
            text-align: center;
            margin-top: 35px;
            font-size: 12px;
            color: #94a3b8;
            padding-top: 20px;
            border-top: 1px solid #edf2f7;
        }

        .copyright a {
            color: #FF8C00;
            text-decoration: none;
        }

        @media (max-width: 900px) {
            .login-wrapper {
                flex-direction: column;
                margin: 15px;
                border-radius: 20px;
            }
            .login-brand {
                padding: 40px 30px;
            }
            .login-form-container {
                padding: 40px 30px;
                min-width: auto;
            }
            .brand-features {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .login-form-container {
                padding: 30px 20px;
            }
            .form-title {
                font-size: 26px;
            }
        }
    </style>
</head>

<body>

<div class="login-wrapper">
    <!-- Colonne gauche avec logo -->
    <div class="login-brand">
        <div class="brand-content">
            <div class="logo-section">
                <!-- Logo avec le bon chemin : adminlte/assets/img/odysse.jpg -->
                <img src="{{ asset('adminlte/assets/img/odysse.jpg') }}" 
                     alt="ODYSSEE ENERGIE" 
                     class="company-logo"
                     onerror="this.onerror=null; this.style.display='none'; this.parentElement.innerHTML += '<div style=\'background:rgba(255,255,255,0.2);border-radius:60px;padding:15px 30px;display:inline-block;\'><i class=\'fas fa-bolt\' style=\'font-size:48px;color:white;\'></i><div style=\'font-size:28px;font-weight:800;letter-spacing:3px;\'>ODYSSEE</div><div style=\'font-size:12px;letter-spacing:2px;\'>ÉNERGIE</div></div>';">
            </div>
            
            <h2 class="brand-title">ODYSSEE ÉNERGIE</h2>
            <p class="brand-description">L'énergie qui vous accompagne vers l'avenir</p>
            
           
        </div>
    </div>

    <!-- Colonne droite : Formulaire -->
    <div class="login-form-container">
        <h1 class="form-title">Connexion</h1>
        <div class="form-subtitle">Accédez à votre espace client</div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul style="margin-bottom:0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="email">Adresse Email</label>
                <div class="input-group">
                    <input type="email" name="email" id="email" class="form-control" 
                           placeholder="gerantB@odyssee.sn" required autofocus value="{{ old('email') }}">
                    <div class="input-group-append">
                        <div class="input-group-text"><i class="fas fa-envelope"></i></div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" 
                           placeholder="············" required>
                    <div class="input-group-append">
                        <div class="input-group-text"><i class="fas fa-lock"></i></div>
                    </div>
                </div>
            </div>

            <div class="checkbox-group">
                <div class="checkbox-wrapper">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">Se souvenir de moi</label>
                </div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot-link">Mot de passe oublié ?</a>
                @endif
            </div>

            <button type="submit" class="btn-primary">
                <i class="fas fa-arrow-right-to-bracket"></i> Se connecter
            </button>
        </form>

        <div class="register-link">
            <p>Vous n'avez pas de compte ? 
                @if (Route::has('register'))
                    <a href="{{ route('register') }}">Créer un compte</a>
                @else
                    <a href="#">Contacter le support</a>
                @endif
            </p>
        </div>

        <div class="copyright">
            © 2025 ODYSSEE ÉNERGIE. Tous droits réservés.<br>
            <a href="#">Conditions d'utilisation</a> | <a href="#">Politique de confidentialité</a>
        </div>
    </div>
</div>

<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('adminlte/js/adminlte.min.js') }}"></script>

</body>
</html>