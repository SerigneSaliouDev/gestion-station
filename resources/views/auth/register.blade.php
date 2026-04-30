<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscription | ODYSSEE ENERGIE</title>

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

        .register-wrapper {
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

        .register-brand {
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

        .register-form-container {
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
            margin-bottom: 20px;
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
            margin-top: 10px;
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

        .login-link {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #edf2f7;
        }

        .login-link p {
            color: #64748b;
            font-size: 14px;
        }

        .login-link a {
            color: #FF8C00;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
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

        .alert-success {
            background: #dcfce7;
            color: #16a34a;
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
            .register-wrapper {
                flex-direction: column;
                margin: 15px;
                border-radius: 20px;
            }
            .register-brand {
                padding: 40px 30px;
            }
            .register-form-container {
                padding: 40px 30px;
                min-width: auto;
            }
            .brand-features {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .register-form-container {
                padding: 30px 20px;
            }
            .form-title {
                font-size: 26px;
            }
        }
    </style>
</head>

<body>

<div class="register-wrapper">
    <!-- Colonne gauche -->
    <div class="register-brand">
        <div class="brand-content">
            <div class="logo-section">
                <img src="{{ asset('adminlte/assets/img/odysse.jpg') }}" 
                     alt="ODYSSEE ENERGIE" 
                     class="company-logo"
                     onerror="this.onerror=null; this.style.display='none'; this.parentElement.innerHTML += '<div style=\'background:rgba(255,255,255,0.2);border-radius:60px;padding:15px 30px;display:inline-block;\'><i class=\'fas fa-bolt\' style=\'font-size:48px;color:white;\'></i><div style=\'font-size:28px;font-weight:800;letter-spacing:3px;\'>ODYSSEE</div><div style=\'font-size:12px;letter-spacing:2px;\'>ÉNERGIE</div></div>';">
            </div>
            <h2 class="brand-title">ODYSSEE ÉNERGIE</h2>
            <p class="brand-description">Rejoignez l'aventure énergétique de demain</p>
           
        </div>
    </div>

    <!-- Colonne droite -->
    <div class="register-form-container">
        <h1 class="form-title">Inscription</h1>
        <div class="form-subtitle">Créez votre espace client</div>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <label for="name">Prénom et Nom</label>
                <div class="input-group">
                    <input type="text" id="name" name="name" class="form-control" placeholder="Votre nom complet" value="{{ old('name') }}" required autofocus autocomplete="name">
                    <div class="input-group-append">
                        <div class="input-group-text"><i class="fas fa-user"></i></div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Adresse Email</label>
                <div class="input-group">
                    <input type="email" id="email" name="email" class="form-control" placeholder="manager@centrepine.com" value="{{ old('email') }}" required autocomplete="username">
                    <div class="input-group-append">
                        <div class="input-group-text"><i class="fas fa-envelope"></i></div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" class="form-control" placeholder="············" required autocomplete="new-password">
                    <div class="input-group-append">
                        <div class="input-group-text"><i class="fas fa-lock"></i></div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmation du mot de passe</label>
                <div class="input-group">
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="············" required autocomplete="new-password">
                    <div class="input-group-append">
                        <div class="input-group-text"><i class="fas fa-lock"></i></div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary">
                <i class="fas fa-user-plus"></i> S'inscrire
            </button>
        </form>

        <div class="login-link">
            <p>Déjà un compte ? <a href="{{ route('login') }}">Connectez-vous</a></p>
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