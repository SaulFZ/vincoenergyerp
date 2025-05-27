@extends('layouts.areahome')

@section('title', 'Sistemas - Vinco ERP')

@section('module-name', 'Sistemas')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/areahome.css') }}">
@endsection

@section('content')
    <div class="dashboard-header">
        <h2 class="dashboard-title">Sistema de Gestión de Tecnología y Soporte</h2>
    </div>

    <div class="grid">
        <div class="card" id="card-roles" data-area="sistemas" data-route="/sistemas/gestionderoles">
            <img src="{{ asset('assets/img/sistemas/gestionderoles.png') }}" alt="Gestión de Roles" class="card-image">
            <div class="card-content">
                <h3 class="card-title">
                    <span class="card-icon">
                        <i class="fas fa-user-shield"></i>
                    </span>
                    Gestión de Roles
                </h3>
                <div class="status-indicator">
                    <div class="status-dot"></div>
                    <span>Sistema Activo</span>
                </div>
            </div>
        </div>

        <div class="card disabled" id="card-nuevo1" data-area="sistemas" data-route="#">
            <img src="{{ asset('assets/img/areas/new-system.png') }}" alt="Nuevo Sistema" class="card-image">
            <div class="card-content">
                <h3 class="card-title">
                    <span class="card-icon">
                        <i class="fas fa-plus-circle"></i>
                    </span>
                    Nuevo Sistema
                </h3>
                <div class="status-indicator">
                    <div class="status-dot"></div>
                    <span>Próximamente</span>
                </div>
            </div>
        </div>

        <div class="card disabled" id="card-nuevo2" data-area="sistemas" data-route="#">
            <img src="{{ asset('assets/img/areas/new-system.png') }}" alt="Nuevo Sistema" class="card-image">
            <div class="card-content">
                <h3 class="card-title">
                    <span class="card-icon">
                        <i class="fas fa-plus-circle"></i>
                    </span>
                    Nuevo Sistema
                </h3>
                <div class="status-indicator">
                    <div class="status-dot"></div>
                    <span>Próximamente</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/areahome.js') }}"></script>
@endsection
