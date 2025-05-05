<!-- resources/views/modulos/ventas/ventashome.blade.php -->
@extends('layouts.area_layout', ['areaName' => 'Ventas', 'title' => 'Sistema de Ventas'])

@section('styles')
<style>
    /* Estilos específicos para la página de Ventas si son necesarios */
    .ventas-header {
        margin-bottom: 2rem;
    }
</style>
@endsection

@section('content')
<div class="dashboard-header ventas-header">
    <h2 class="dashboard-title">Sistemas de Gestión de Ventas</h2>
</div>

<div class="grid">
    <a href="../ventas/crm/login.html" class="card">
        <div class="card-shine"></div>
        <img src="{{ asset('ventas/img/crm.png') }}" alt="CRM" class="card-image">
        <div class="card-content">
            <h3 class="card-title">
                <span class="card-icon">
                    <i class="fas fa-users"></i>
                </span>
                CRM
            </h3>
            <div class="status-indicator">
                <div class="status-dot"></div>
                <span>Sistema Activo</span>
            </div>
        </div>
    </a>

    <a href="../ventas/oportunidades/login.html" class="card">
        <div class="card-shine"></div>
        <img src="{{ asset('ventas/img/oportunidades.png') }}" alt="Gestión de Oportunidades" class="card-image">
        <div class="card-content">
            <h3 class="card-title">
                <span class="card-icon">
                    <i class="fas fa-handshake"></i>
                </span>
                Gestión de Oportunidades
            </h3>
            <div class="status-indicator">
                <div class="status-dot"></div>
                <span>Sistema Activo</span>
            </div>
        </div>
    </a>

    <a href="../ventas/cotizaciones/login.html" class="card">
        <div class="card-shine"></div>
        <img src="{{ asset('ventas/img/cotizaciones.png') }}" alt="Cotizaciones" class="card-image">
        <div class="card-content">
            <h3 class="card-title">
                <span class="card-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </span>
                Cotizaciones
            </h3>
            <div class="status-indicator">
                <div class="status-dot"></div>
                <span>Sistema Activo</span>
            </div>
        </div>
    </a>

    <a href="#" class="card">
        <div class="card-shine"></div>
        <img src="{{ asset('ventas/img/placeholder.png') }}" alt="Panel de Métricas" class="card-image">
        <div class="card-content">
            <h3 class="card-title">
                <span class="card-icon">
                    <i class="fas fa-chart-line"></i>
                </span>
                Panel de Métricas
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
<script>
    // Scripts específicos para la página de Ventas si son necesarios
    document.addEventListener("DOMContentLoaded", () => {
        console.log("Ventas Dashboard Loaded");
    });
</script>
@endsection
