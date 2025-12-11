<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CarburantPrice;

class CarburantPriceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Afficher le formulaire de modification des prix
     */
    public function editPrices()
    {
        $carburants = ['Gazole', 'Super']; // SEULEMENT ces deux
        
        $currentPrices = [];
        foreach ($carburants as $carburant) {
            $currentPrices[$carburant] = CarburantPrice::getCurrentPrice($carburant);
        }

        $priceHistory = CarburantPrice::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('manager.edit-prices', compact('carburants', 'currentPrices', 'priceHistory'));
    }

    /**
     * Mettre à jour les prix
     */
    public function updatePrices(Request $request)
    {
        $validated = $request->validate([
            'prices' => 'required|array',
            'prices.*.type' => 'required|string',
            'prices.*.prix_unitaire' => 'required|numeric|min:0',
        ]);

        $updates = [];
        
        foreach ($request->prices as $priceData) {
            $type = $priceData['type'];
            $newPrice = floatval($priceData['prix_unitaire']);
            
            // Vérifier si le prix a changé
            $currentPrice = CarburantPrice::getCurrentPrice($type);
            
            if (!$currentPrice || $currentPrice->prix_unitaire != $newPrice) {
                // Créer une nouvelle entrée
                CarburantPrice::create([
                    'type_carburant' => $type,
                    'prix_unitaire' => $newPrice,
                    'user_id' => Auth::id(),
                ]);
                
                $updates[] = $type;
            }
        }

        if (count($updates) > 0) {
            return redirect()->route('manager.edit_prices')
                ->with('success', 'Prix mis à jour avec succès pour: ' . implode(', ', $updates));
        }

        return redirect()->route('manager.edit_prices')
            ->with('info', 'Aucun changement détecté');
    }

    /**
     * Historique des modifications de prix
     */
    public function priceHistory()
    {
        $history = CarburantPrice::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('manager.price-history', compact('history'));
    }

    /**
     * API pour récupérer les prix actuels (pour le formulaire de saisie)
     */
    public function getCurrentPrices()
    {
        $carburants = ['Gazole', 'Super']; // SEULEMENT ces deux
        $prices = [];

        foreach ($carburants as $carburant) {
            $price = CarburantPrice::getCurrentPrice($carburant);
            $prices[$carburant] = $price ? $price->prix_unitaire : 0;
        }

        return response()->json($prices);
    }
    }