@extends('layouts.app') 

@section('title', 'Enregistrer une Réception de Carburant')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Formulaire d'Enregistrement de Réception</h3>
                </div>
                
                {{-- Formulaire ciblant la méthode storeReception --}}
                <form action="{{ route('manager.stocks.receptions.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        
                        {{-- Affichage des erreurs de validation --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="reception_date">Date de la Réception <span class="text-danger">*</span></label>
                            <input type="date" name="reception_date" id="reception_date" class="form-control @error('reception_date') is-invalid @enderror" value="{{ old('reception_date', now()->format('Y-m-d')) }}" required>
                            @error('reception_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                        <select name="fuel_type" id="fuel_type" class="form-control @error('fuel_type') is-invalid @enderror" required>
                        <option value="">-- Sélectionnez --</option>
                        @foreach($fuelTypes as $key => $name)
                            {{-- ATTENTION : C'EST LA VALEUR $key QUI EST ENVOYÉE --}}
                            <option value="{{ $key }}" {{ old('fuel_type') == $key ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                                                @error('fuel_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="tank_number">Cuve de Réception <span class="text-danger">*</span></label>
                            <select name="tank_number" id="tank_number" class="form-control @error('tank_number') is-invalid @enderror" required>
                                <option value="">-- Sélectionnez --</option>
                                @foreach($tanks as $key => $name)
                                    <option value="{{ $key }}" {{ old('tank_number') == $key ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('tank_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="quantity">Quantité Reçue (Litres) <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}" min="1" required>
                            @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="unit_price">Prix Unitaire d'Achat (F CFA/L) <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="unit_price" id="unit_price" class="form-control @error('unit_price') is-invalid @enderror" value="{{ old('unit_price') }}" min="0" required>
                            @error('unit_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="supplier_name">Nom du Fournisseur <span class="text-danger">*</span></label>
                            <input type="text" name="supplier_name" id="supplier_name" class="form-control @error('supplier_name') is-invalid @enderror" value="{{ old('supplier_name') }}" required>
                            @error('supplier_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="invoice_number">Numéro de Facture/BL <span class="text-danger">*</span></label>
                            <input type="text" name="invoice_number" id="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror" value="{{ old('invoice_number') }}" required>
                            @error('invoice_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Notes/Observations</label>
                            <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer la Réception</button>
                        <a href="{{ route('manager.stocks.dashboard') }}" class="btn btn-default float-right">Annuler</a>
                    </div>
                </form>
            </div>
            
        </div>
    </div>
</div>
@endsection