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
        body {
            background: white;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .header {
            background: linear-gradient(135deg, #FF8C00, #FFA500);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .logo-container {
            margin-bottom: 10px;
        }

        .company-logo {
            max-width: 350px;
            height: auto;
        }

        .register-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 50px 20px;
        }

        .register-form {
            width: 100%;
            max-width: 450px;
            background: white;
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        }

        .form-title {
            color: #333;
            margin-bottom: 35px;
            font-size: 28px;
            font-weight: 600;
            text-align: center;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #555;
            font-weight: 500;
            font-size: 15px;
        }

        .input-group {
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border 0.3s;
            height: auto;
        }

        .form-control:focus {
            border-color: #FF8C00;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(255, 140, 0, 0.25);
        }

        .input-group-append {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            display: flex;
            align-items: center;
            padding-right: 15px;
        }

        .input-group-text {
            background: transparent;
            border: none;
            color: #777;
        }

        .btn-primary {
            width: 100%;
            padding: 15px;
            background: #FF8C00;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-bottom: 25px;
        }

        .btn-primary:hover {
            background: #E67E00;
        }

        .links {
            text-align: center;
        }

        .links a {
            color: #FF8C00;
            text-decoration: none;
            font-weight: 500;
            display: block;
            margin-bottom: 12px;
            font-size: 15px;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .separator {
            border-top: 2px solid #eee;
            margin: 30px 0;
        }

        .windows-activation {
            text-align: center;
            color: #888;
            font-size: 13px;
            margin-top: 40px;
            padding-top: 25px;
            border-top: 2px solid #f0f0f0;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 25px;
            border: none;
        }

        @media (max-width: 768px) {
            .company-logo {
                max-width: 280px;
            }
            
            .register-form {
                padding: 35px 25px;
            }
            
            .header {
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>

<!-- Header avec logo -->
<div class="header">
    <div class="logo-container">
        <img src="{{ asset('images/logo-odyssee.png') }}" alt="ODYSSEE ENERGIE" class="company-logo" onerror="this.style.display='none'">
    </div>
</div>

<!-- Contenu principal -->
<div class="register-container">
    <div class="register-form">
        <h1 class="form-title">Créer un compte</h1>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <label for="name">Prénom et Nom</label>
                <div class="input-group">
                    <input type="text" id="name" name="name" class="form-control" placeholder="Votre nom complet" value="{{ old('name') }}" required autofocus autocomplete="name">
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-user"></span></div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Adresse Email</label>
                <div class="input-group">
                    <input type="email" id="email" name="email" class="form-control" placeholder="manager@centrepine.com" value="{{ old('email') }}" required autocomplete="username">
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" class="form-control" placeholder="············" required autocomplete="new-password">
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-lock"></span></div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmation de Mot de passe</label>
                <div class="input-group">
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="············" required autocomplete="new-password">
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-lock"></span></div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary">S'inscrire</button>
        </form>

        <div class="separator"></div>

        <div class="links">
            <a href="{{ route('login') }}">Déjà un compte ? Connectez-vous</a>
        </div>

  
<!-- Scripts AdminLTE -->
<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('adminlte/js/adminlte.min.js') }}"></script>

</body>
</html>