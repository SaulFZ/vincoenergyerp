@extends('layouts.areahome')

@section('title', 'Recursos Humanos - Vinco ERP')

@section('module-name', 'Recursos Humanos')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/areahome.css') }}">
@endsection

@section('content')
    <div class="dashboard-header">
        <h2 class="dashboard-title">Sistema de Gestión de Recursos Humanos</h2>
    </div>

    <div class="grid">
        <div class="card" id="card-altas" data-area="recursoshumanos" data-route="/recursoshumanos/altasempleados">
            <img src="{{ asset('assets/img/recursoshumanos/altas.png') }}" alt="Altas de Personal" class="card-image">
            <div class="card-content">
                <h3 class="card-title">
                    <span class="card-icon">
                        <i class="fas fa-user-plus"></i>
                    </span>
                    Altas de Personal
                </h3>
                <div class="status-indicator">
                    <div class="status-dot"></div>
                    <span>Sistema Activo</span>
                </div>
            </div>
        </div>

        <div class="card" id="card-loadchart" data-area="recursoshumanos" data-route="/recursoshumanos/loadchart">
            <img src="{{ asset('assets/img/recursoshumanos/loadchart.png') }}" alt="LoadChart" class="card-image">
            <div class="card-content">
                <h3 class="card-title">
                    <span class="card-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </span>
                    LoadChart
                </h3>
                <div class="status-indicator">
                    <div class="status-dot"></div>
                    <span>Sistema Activo</span>
                </div>
            </div>
        </div>

        <div class="card disabled" id="card-nuevo1" data-area="recursoshumanos" data-route="#">
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

        <div class="card disabled" id="card-nuevo2" data-area="recursoshumanos" data-route="#">
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
