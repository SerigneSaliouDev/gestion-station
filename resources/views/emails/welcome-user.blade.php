<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bienvenue sur ODYSSE ENERGIE SA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #4F46E5;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background: #f9f9f9;
        }
        .credentials {
            background: white;
            padding: 20px;
            border-left: 4px solid #4F46E5;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #4F46E5;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenue {{ $user->name }} !</h1>
        </div>
        
        <div class="content">
            <p>Votre compte a été créé avec succès sur la plateforme OdysseeDk.</p>
            
            <div class="credentials">
                <h3>Vos identifiants de connexion :</h3>
                <p><strong>Email :</strong> {{ $user->email }}</p>
                <p><strong>Mot de passe temporaire :</strong> {{ $password }}</p>
                <p style="color: #e53e3e; font-size: 14px;">
                    ⚠️ Pour des raisons de sécurité, veuillez changer ce mot de passe dès votre première connexion.
                </p>
            </div>
            
            <a href="{{ config('app.url') }}/login" class="button">
                Se connecter à la plateforme
            </a>
            
            <p style="margin-top: 30px;">
                <strong>Votre rôle :</strong> 
                @switch($user->role)
                    @case('administrateur')
                        Administrateur
                        @break
                    @case('manager')
                        Manager de station
                        @break
                    @case('chief')
                        Chef des opérations
                        @break
                    @default
                        Utilisateur
                @endswitch
            </p>
            
            @if($user->station_id)
                <p><strong>Station assignée :</strong> {{ $user->station->nom ?? 'Non assignée' }}</p>
            @endif
        </div>
        
        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
            <p>&copy; {{ date('Y') }} Odyssee Energies SA. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>