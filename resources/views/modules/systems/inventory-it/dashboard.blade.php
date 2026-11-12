@extends('modules.systems.inventory-it.index')

@push('styles')
    <link href="{{ asset('assets/css/systems/inventoryit/dashboard.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="dashboard-wrapper" id="inventoryApp">
        <section id="viewDashboard" class="dashboard-content" aria-label="Resumen del inventario">

            {{-- Tarjetas estadísticas --}}
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-laptop" aria-hidden="true"></i>
                    </div>

                    <div class="stat-info">
                        <h4>Total equipos</h4>
                        <div class="stat-value" id="statTotal">0</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-user-check" aria-hidden="true"></i>
                    </div>

                    <div class="stat-info">
                        <h4>Asignados</h4>
                        <div class="stat-value" id="statAsignados">0</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon amber">
                        <i class="fas fa-tools" aria-hidden="true"></i>
                    </div>

                    <div class="stat-info">
                        <h4>En mantenimiento</h4>
                        <div class="stat-value" id="statMant">0</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
                    </div>

                    <div class="stat-info">
                        <h4>Fallas reportadas</h4>
                        <div class="stat-value" id="statFallas">0</div>
                    </div>
                </div>
            </div>

            {{-- Equipos recientes --}}
            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">
                        <i class="fas fa-boxes-stacked" aria-hidden="true"></i>
                        Recientes en inventario
                    </h2>

                    <div class="panel-filters">
                        <div class="search-box">
                            <i class="fas fa-search" aria-hidden="true"></i>

                            <input
                                type="search"
                                id="dashSearch"
                                placeholder="Buscar equipo..."
                                aria-label="Buscar entre los equipos recientes"
                            >
                        </div>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table aria-label="Equipos recientes en inventario">
                        <thead>
                            <tr>
                                <th scope="col">Equipo</th>
                                <th scope="col">Tipo</th>
                                <th scope="col">Estado</th>
                            </tr>
                        </thead>

                        <tbody id="dashRecientesBody">
                            {{-- Las filas se cargarán desde el JavaScript del dashboard. --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
@endsection
