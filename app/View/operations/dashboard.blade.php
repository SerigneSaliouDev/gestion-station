@extends('layouts.app')

@section('title', 'Tableau de Bord - Opérations')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-gray-900">Tableau de Bord - Chargé des Opérations</h1>
                <p class="mt-4 text-gray-600">Bienvenue, {{ Auth::user()->name }}!</p>
                <p class="mt-2 text-gray-600">Rôle: Chargé des Opérations</p>
            </div>
        </div>
    </div>
</div>
@endsection