@extends('layouts.areahome')

@section('title', 'QHSE - Vinco ERP')

@section('module-name', 'QHSE')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/qhse/qhse.css') }}">
@endsection

@section('content')
<div class="dashboard-header">
    <h2 class="dashboard-title">Sistemas de Gestión QHSE</h2>
</div>

<div class="grid">
    <div class="card" id="card-vescap" data-area="qhse" data-route="/vescap">
        <img src="{{ asset('assets/img/qhse/vescap.png') }}" alt="VESCAP" class="card-image">
        <div class="card-content">
            <h3 class="card-title">
                <span class="card-icon">
                    <i class="fas fa-graduation-cap"></i>
                </span>
                VESCAP
            </h3>
            <div class="status-indicator">
                <div class="status-dot"></div>
                <span>Sistema Activo</span>
            </div>
        </div>
    </div>

    <div class="card" id="card-incidencias" data-area="qhse" data-route="/incidencias">
        <img src="{{ asset('assets/img/qhse/incidencia.png') }}" alt="Gestión de Incidencias QHSE" class="card-image">
        <div class="card-content">
            <h3 class="card-title">
                <span class="card-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </span>
                Gestión de Incidencias QHSE
            </h3>
            <div class="status-indicator">
                <div class="status-dot"></div>
                <span>Sistema Activo</span>
            </div>
        </div>
    </div>

    <div class="card" id="card-auditorias" data-area="qhse" data-route="/auditorias">
        <img src="{{ asset('assets/img/qhse/img.png') }}" alt="Sistema de Auditorías" class="card-image">
        <div class="card-content">
            <h3 class="card-title">
                <span class="card-icon">
                    <i class="fas fa-clipboard-check"></i>
                </span>
                Sistema de Auditorías
            </h3>
            <div class="status-indicator">
                <div class="status-dot"></div>
                <span>Sistema Activo</span>
            </div>
        </div>
    </div>

    <div class="card disabled" id="card-nuevo" data-area="qhse" data-route="#">
        <img src="{{ asset('assets/img/qhse/new-system.png') }}" alt="Nuevo Sistema" class="card-image">
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
