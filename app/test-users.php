<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;

echo "=== TEST DES UTILISATEURS ET RÔLES ===\n\n";

// Vérifier les utilisateurs
$users = User::all();
echo "Utilisateurs trouvés:\n";
foreach ($users as $user) {
    echo "- {$user->name} ({$user->email})\n";
    echo "  Rôles: " . $user->getRoleNames()->implode(', ') . "\n\n";
}

// Vérifier les rôles
$roles = Role::all();
echo "Rôles disponibles:\n";
foreach ($roles as $role) {
    echo "- {$role->name}\n";
}

echo "\n=== TEST TERMINÉ ===\n";