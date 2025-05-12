@extends('layouts.areahome')

@section('title', 'Sistemas - Vinco ERP')

@section('module-name', 'Sistemas')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/sistemas/sistemas.css') }}">
@endsection

@section('content')
<div class="dashboard-header">
    <h2 class="dashboard-title">Administración de Sistemas</h2>
</div>

<div class="grid">
    <div class="card" id="card-gestionroles" data-system="gestionroles" data-route="/sistemas/gestionroles">
        <img src="{{ asset('assets/img/sistemas/roles.png') }}" alt="Gestión de Roles" class="card-image">
        <div class="card-content">
            <h3 class="card-title">
                <span class="card-icon">
                    <i class="fas fa-users-cog"></i>
                </span>
                Gestión de Roles
            </h3>
            <div class="status-indicator">
                <div class="status-dot"></div>
                <span>Sistema Activo</span>
            </div>
        </div>
    </div>

    <div class="card" id="card-inventarioequipos" data-system="inventarioequipos" data-route="/sistemas/inventarioequipos">
        <img src="{{ asset('assets/img/sistemas/inventario.png') }}" alt="Inventario de Equipos" class="card-image">
        <div class="card-content">
            <h3 class="card-title">
                <span class="card-icon">
                    <i class="fas fa-laptop"></i>
                </span>
                Inventario de Equipos
            </h3>
            <div class="status-indicator">
                <div class="status-dot"></div>
                <span>Sistema Activo</span>
            </div>
        </div>
    </div>

    <div class="card" id="card-monitoreo" data-system="monitoreo" data-route="/sistemas/monitoreo">
        <img src="{{ asset('assets/img/sistemas/monitoreo.png') }}" alt="Monitoreo de Servicios" class="card-image">
        <div class="card-content">
            <h3 class="card-title">
                <span class="card-icon">
                    <i class="fas fa-chart-line"></i>
                </span>
                Monitoreo de Servicios
            </h3>
            <div class="status-indicator">
                <div class="status-dot"></div>
                <span>Sistema Activo</span>
            </div>
        </div>
    </div>

    <div class="card" id="card-tickets" data-system="tickets" data-route="/sistemas/tickets">
        <img src="{{ asset('assets/img/sistemas/tickets.png') }}" alt="Gestión de Tickets" class="card-image">
        <div class="card-content">
            <h3 class="card-title">
                <span class="card-icon">
                    <i class="fas fa-ticket-alt"></i>
                </span>
                Gestión de Tickets
            </h3>
            <div class="status-indicator">
                <div class="status-dot"></div>
                <span>Sistema Activo</span>
            </div>
        </div>
    </div>

    <div class="card disabled" id="card-nuevo">
        <img src="{{ asset('assets/img/sistemas/new-system.png') }}" alt="Nuevo Sistema" class="card-image">
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Seleccionar todas las tarjetas que no están deshabilitadas
    const cards = document.querySelectorAll('.card:not(.disabled)');

    // Agregar evento de clic a cada tarjeta
    cards.forEach(card => {
        card.addEventListener('click', function() {
            // Obtener la ruta desde el atributo data-route
            const route = this.getAttribute('data-route');
            if (route) {
                // Redirigir a la ruta especificada
                window.location.href = route;
            }
        });

        // Agregar efecto de hover
        card.addEventListener('mouseenter', function() {
            this.classList.add('hover');
        });

        card.addEventListener('mouseleave', function() {
            this.classList.remove('hover');
        });
    });
});
</script>
@endsection
