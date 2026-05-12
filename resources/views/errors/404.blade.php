@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4>Erreur 404 - Page non trouvée</h4>
                </div>
                <div class="card-body">
                    <p>La page que vous recherchez n'existe pas.</p>
                    <a href="{{ url('/') }}" class="btn btn-primary">Retour à l'accueil</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection