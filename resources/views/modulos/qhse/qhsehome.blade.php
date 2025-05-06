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
    <a href="{{ route('qhse.vescap') }}" class="card">
        <img src="{{ asset('assets/img/areas/vescap.png') }}" alt="VESCAP" class="card-image">
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
    </a>

    <a href="{{ route('qhse.incidencias') }}" class="card">
        <img src="{{ asset('assets/img/areas/incidencia.png') }}" alt="Gestión de Incidencias QHSE" class="card-image">
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
    </a>

    <a href="{{ route('qhse.auditorias') }}" class="card">
        <img src="{{ asset('assets/img/areas/auditorias.png') }}" alt="Sistema de Auditorías" class="card-image">
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
    </a>

    <a href="#" class="card disabled">
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
    </a>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/qhse.js') }}"></script>
@endsection
