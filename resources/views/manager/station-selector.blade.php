@extends('layouts.app') 

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Sélection de la Station</div>

                <div class="card-body">
                    <p>Veuillez sélectionner la station avec laquelle vous souhaitez travailler :</p>

                    @if($stations->isEmpty())
                        <div class="alert alert-warning">
                            Aucune station n'est accessible pour votre compte. Veuillez contacter l'administrateur.
                        </div>
                    @else
                        <form method="POST" action="{{ route('station.select.post') }}">
                            @csrf
                            <div class="form-group">
                                <label for="station_id">Station :</label>
                                <select name="station_id" id="station_id" class="form-control" required>
                                    <option value="">-- Choisir une station --</option>
                                    @foreach ($stations as $station)
                                        <option value="{{ $station->id }}">
                                            {{ $station->nom }} 
                                            ({{ $station->shifts_count }} Shifts, {{ $station->tanks_count }} Cuves)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Sélectionner</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection