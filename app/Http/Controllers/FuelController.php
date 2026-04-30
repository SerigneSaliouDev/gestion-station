<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fuel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FuelController extends Controller
{
    /**
     * Show the form for editing fuel prices
     */
    public function edit()
    {
        // Logique pour afficher le formulaire d'édition des prix
        return view('fuels.edit', [
            'fuels' => [
                'super' => ['current_price' => 850, 'name' => 'SUPER'],
                'gazole' => ['current_price' => 750, 'name' => 'GAZOLE'],
                'diesel' => ['current_price' => 720, 'name' => 'DIESEL'],
            ]
        ]);
    }

    /**
     * Update fuel prices
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'prices' => 'required|array',
            'prices.*' => 'numeric|min:0',
        ]);

        try {
            // Ici, vous pouvez enregistrer les prix dans la base de données
            // ou dans un fichier de configuration
            
            Log::info('Prix des carburants mis à jour', [
                'prices' => $validated['prices'],
                'user_id' => Auth::id()
            ]);

            return redirect()->back()
                ->with('success', 'Prix des carburants mis à jour avec succès!');
                
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour prix carburants', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Show fuel price history
     */
    public function history()
    {
        // Logique pour afficher l'historique des prix
        // Si vous n'avez pas de table pour l'historique, retournez une vue simple
        
        $history = [
            [
                'date' => now()->subDays(2)->format('d/m/Y'),
                'super' => 830,
                'gazole' => 730,
                'diesel' => 700,
                'updated_by' => 'Admin'
            ],
            [
                'date' => now()->subDays(5)->format('d/m/Y'),
                'super' => 820,
                'gazole' => 720,
                'diesel' => 690,
                'updated_by' => 'Manager'
            ],
        ];

        return view('fuels.history', compact('history'));
    }
}