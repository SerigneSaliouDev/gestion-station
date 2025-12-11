<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Prix Carburants</title>
    <style>
        /* Styles Tailwind pour un design moderne */
        body { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif; background-color: #f7f7f7; display: flex; }
        .sidebar { width: 220px; background-color: #343a40; color: white; height: 100vh; position: fixed; padding-top: 20px; }
        .sidebar a { padding: 10px 15px; text-decoration: none; font-size: 1.1rem; color: #adb5bd; display: block; border-left: 3px solid transparent; transition: all 0.2s; }
        .sidebar a:hover, .sidebar .active { background-color: #495057; color: white; border-left: 3px solid #ff7f00; }
        .header-title { padding: 15px; text-align: center; font-size: 1.3rem; font-weight: 700; color: #ff7f00; border-bottom: 1px solid #495057; margin-bottom: 20px;}

        .main-content { margin-left: 220px; flex-grow: 1; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background-color: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); padding: 30px; }

        .form-title { color: #343a40; border-bottom: 2px solid #ff7f00; padding-bottom: 10px; margin-bottom: 30px; text-align: center; }
        
        /* Styles spécifiques au formulaire de prix */
        .price-card { background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 20px; margin-bottom: 20px; }
        .price-card label { display: block; font-weight: 600; margin-bottom: 5px; color: #343a40; }
        .price-card input[type="number"] { width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 1.1rem; box-sizing: border-box; }
        
        .grid-prices { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        
        .btn-submit { display: block; width: 100%; padding: 12px; background-color: #ff7f00; color: white; border: none; border-radius: 4px; font-size: 1.1rem; cursor: pointer; transition: background-color 0.3s; margin-top: 30px; }
        .btn-submit:hover { background-color: #e67000; }

        /* Messages d'alerte */
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: 600; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .error-message { color: #dc3545; font-size: 0.85rem; margin-top: 5px; display: block; }
    </style>
</head>
<body>
    <!-- Menu Latéral -->
    <div class="sidebar">
        <div class="header-title">GÉRANT</div>
        <a href="{{ route('manager.saisie-index') }}">Saisie Index/Ventes</a>
        <a href="{{ route('manager.history') }}">Historique des Saisies</a>
        <a href="{{ route('manager.reports') }}">Rapports</a>
        <a href="{{ route('manager.edit_prices') }}" class="active">Modifier Prix</a>
    </div>

    <div class="main-content">
        <div class="container">
            <h1 class="form-title">Modification des Prix Unitaires (F CFA / Litre)</h1>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('info'))
                <div class="alert alert-info">{{ session('info') }}</div>
            @endif
            
            <p style="margin-bottom: 20px; color: #6c757d;">
                **Attention :** Toute modification de prix est horodatée et s'applique **immédiatement** aux nouvelles saisies. Le prix unitaire est fixé par la direction de la station.
            </p>

            <form action="{{ route('manager.update_prices') }}" method="POST">
                @csrf
                <div class="grid-prices">
                    @foreach ($currentPrices as $index => $item)
                        <div class="price-card">
                            <label for="price_{{ $index }}">{{ $item['fuel_type'] }}</label>
                            
                            <p style="font-size: 0.9rem; color: #343a40; margin-bottom: 10px;">
                                Prix Actuel : **{{ number_format($item['price'], 0, ',', ' ') }}** F CFA
                            </p>
                            
                            <input 
                                type="number" 
                                id="price_{{ $index }}" 
                                name="prices[{{ $index }}][new_price]"
                                value="{{ old("prices.$index.new_price", $item['price']) }}"
                                min="0.01" 
                                step="any" 
                                placeholder="Nouveau prix en F CFA"
                            >
                            
                            <!-- Champ caché pour identifier le carburant -->
                            <input type="hidden" name="prices[{{ $index }}][fuel_type]" value="{{ $item['fuel_type'] }}">
                            
                            @error("prices.$index.new_price")
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    @endforeach
                </div>
                
                <button type="submit" class="btn-submit">
                    Enregistrer les Nouveaux Prix
                </button>
            </form>
        </div>
    </div>
</body>
</html>