@extends('modulos.qhse.sistemas.gerenciamiento.index')

@push('css')
    <style>
        .empleados-container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            padding: 25px;
            margin-top: 20px;
            margin-bottom: 30px;
            font-family: 'Poppins', sans-serif;
        }

        .empleados-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .empleados-header h2 {
            color: #0056b3;
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        /* Selector de Paginación */
        .per-page-selector {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .per-page-select {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            outline: none;
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.2s;
        }

        .per-page-select:focus {
            border-color: #0056b3;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1);
        }

        .search-box {
            position: relative;
        }

        .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 0.9rem;
        }

        .search-box input {
            padding: 10px 15px 10px 38px;
            border: 1px solid #dee2e6;
            border-radius: 25px;
            outline: none;
            width: 260px;
            font-size: 0.9rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        .search-box input:focus {
            border-color: #0056b3;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(0, 86, 179, 0.1);
            width: 300px;
        }

        /* Contenedor de la tabla con scroll vertical */
        .table-responsive {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 600px; /* Altura máxima con scroll */
            width: 100%;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            scrollbar-width: thin;
            scrollbar-color: #0056b3 #e9ecef;
        }

        /* Estilos para la barra de scroll (Webkit) */
        .table-responsive::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #e9ecef;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #0056b3;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #003d80;
        }

        .table-licencias {
            width: 100%;
            border-collapse: collapse;
            min-width: 1050px;
            background: #ffffff;
        }

        .table-licencias thead {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 10;
        }

        .table-licencias th {
            background-color: #f8f9fa;
            color: #6c757d;
            font-weight: 600;
            text-align: center;
            padding: 15px 12px;
            border-bottom: 2px solid #dee2e6;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-licencias td {
            padding: 15px 12px;
            border-bottom: 1px solid #dee2e6;
            text-align: center;
            vertical-align: middle;
            font-size: 0.9rem;
            transition: background-color 0.2s;
        }

        .table-licencias tbody tr:hover {
            background-color: #f8fafc;
        }

        .empleado-info {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 15px;
            text-align: left;
        }

        .foto-avatar {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background-color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 1.2rem;
            flex-shrink: 0;
            overflow: hidden;
        }

        .empleado-detalles h4 {
            margin: 0 0 2px 0;
            font-size: 0.95rem;
            color: #212529;
            font-weight: 600;
        }

        .empleado-detalles span {
            font-size: 0.75rem;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Badges - manteniendo los colores originales */
        .badge-vigencia {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 700;
            min-width: 105px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .bg-ok {
            background-color: #28a745;
            color: white;
        }

        .bg-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .bg-orange {
            background-color: #fd7e14;
            color: white;
        }

        .bg-danger {
            background-color: #dc3545;
            color: white;
        }

        .bg-missing {
            background-color: #e9ecef;
            color: #6c757d;
        }

        .btn-actualizar {
            background-color: #eff6ff;
            color: #0056b3;
            border: 1px solid #bfdbfe;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-actualizar:hover {
            background-color: #0056b3;
            color: #ffffff;
            border-color: #0056b3;
            box-shadow: 0 2px 5px rgba(0, 86, 179, 0.2);
        }

        /* Paginación - manteniendo diseño original pero mejorado */
        .pagination-wrapper {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            width: 100%;
            font-family: 'Poppins', sans-serif;
        }

        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .page-item .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            height: 38px;
            padding: 0 10px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            color: #64748b;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
            background: #fff;
        }

        .page-item.active .page-link {
            background-color: #0056b3;
            color: white;
            border-color: #0056b3;
            box-shadow: 0 2px 4px rgba(0, 86, 179, 0.2);
            pointer-events: none;
        }

        .page-item:not(.active):not(.disabled) .page-link:hover {
            background-color: #f8fafc;
            color: #0056b3;
            border-color: #cbd5e1;
        }

        .page-item.disabled .page-link {
            background-color: #f1f5f9;
            color: #cbd5e1;
            cursor: not-allowed;
            border-color: #e2e8f0;
            pointer-events: none;
        }

        /* Modal - manteniendo colores originales */
        .modal-licencias {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1050;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-licencias.active {
            display: flex;
            opacity: 1;
        }

        .modal-content-lic {
            background: white;
            border-radius: 12px;
            width: 95%;
            max-width: 650px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 30px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transform: translateY(-30px) scale(0.95);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .modal-licencias.active .modal-content-lic {
            transform: translateY(0) scale(1);
        }

        .modal-header-lic {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .modal-header-lic h3 {
            margin: 0;
            color: #212529;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-header-lic h3 i {
            color: #0056b3;
        }

        .btn-close-modal {
            background: #f1f5f9;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            color: #6c757d;
            transition: all 0.2s;
        }

        .btn-close-modal:hover {
            background: #fee2e2;
            color: #dc3545;
        }

        .modal-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .modal-card {
            padding: 20px;
            border-radius: 10px;
        }

        .modal-card.ligera {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .modal-card.pesada {
            background: #fff3e0;
            border: 1px solid #ffd8b0;
        }

        .modal-card h4 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-card.ligera h4 {
            color: #0056b3;
        }

        .modal-card.pesada h4 {
            color: #fd7e14;
        }

        .form-group-lic {
            margin-bottom: 18px;
        }

        .form-group-lic label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #212529;
            font-size: 0.85rem;
        }

        .input-icon-wrapper {
            position: relative;
        }

        .input-icon-wrapper i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            pointer-events: none;
        }

        .input-icon-wrapper input {
            width: 100%;
            padding: 10px 35px 10px 12px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .input-icon-wrapper input:focus {
            border-color: #0056b3;
            box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1);
            outline: none;
        }

        .modal-footer-lic {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 25px;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }

        .btn-guardar {
            background: #0056b3;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            box-shadow: 0 4px 6px rgba(0, 86, 179, 0.2);
        }

        .btn-guardar:hover {
            background: #003d80;
            transform: translateY(-1px);
        }

        .btn-cancelar {
            background: white;
            color: #6c757d;
            border: 1px solid #dee2e6;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-cancelar:hover {
            background: #f8f9fa;
            color: #212529;
        }

        .no-results {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
            display: none;
        }

        /* Loader */
        .loader-cell {
            text-align: center;
            padding: 40px 20px;
        }

        .loader-cell .spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 3px solid #e9ecef;
            border-radius: 50%;
            border-top-color: #0056b3;
            animation: spin 1s linear infinite;
            margin-bottom: 15px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Tooltip simple */
        [data-tooltip] {
            position: relative;
            cursor: help;
        }

        [data-tooltip]:before {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            padding: 5px 10px;
            background: #212529;
            color: white;
            font-size: 0.7rem;
            border-radius: 4px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s;
            z-index: 1000;
        }

        [data-tooltip]:hover:before {
            opacity: 1;
            visibility: visible;
            bottom: 130%;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .empleados-container {
                padding: 15px;
            }

            .header-controls {
                flex-direction: column;
                width: 100%;
            }

            .search-box {
                width: 100%;
            }

            .search-box input {
                width: 100%;
            }

            .search-box input:focus {
                width: 100%;
            }

            .modal-grid {
                grid-template-columns: 1fr;
            }

            .modal-content-lic {
                padding: 20px;
                width: 95%;
            }
        }
    </style>
@endpush

@php
    function renderLicenseBadge($dateString)
    {
        if (!$dateString) {
            return '<span class="badge-vigencia bg-missing"><i class="fas fa-minus"></i> No Registrado</span>';
        }
        $date = \Carbon\Carbon::parse($dateString)->startOfDay();
        $now = \Carbon\Carbon::now()->startOfDay();
        $daysDiff = $now->diffInDays($date, false);
        $formattedDate = $date->format('d/m/Y');

        if ($daysDiff < 0) {
            return '<span class="badge-vigencia bg-danger" data-tooltip="Vencido"><i class="fas fa-times"></i> ' . $formattedDate . '</span>';
        } elseif ($daysDiff <= 30) {
            return '<span class="badge-vigencia bg-orange" data-tooltip="Vence en ' . $daysDiff . ' días"><i class="fas fa-exclamation-circle"></i> ' . $formattedDate . '</span>';
        } elseif ($daysDiff <= 60) {
            return '<span class="badge-vigencia bg-warning" data-tooltip="Vence en ' . $daysDiff . ' días"><i class="fas fa-exclamation-triangle"></i> ' . $formattedDate . '</span>';
        } else {
            return '<span class="badge-vigencia bg-ok" data-tooltip="Vigente"><i class="fas fa-check"></i> ' . $formattedDate . '</span>';
        }
    }
@endphp

@section('content')
    <div class="empleados-container">
        <div class="empleados-header">
            <h2><i class="fas fa-id-card-alt"></i> Control de Licencias y Cursos</h2>

            <div class="header-controls">
                <div class="per-page-selector">
                    <label for="per_page">Mostrar:</label>
                    <select id="per_page" class="per-page-select">
                        <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                        <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                        <option value="30" {{ $perPage == 30 ? 'selected' : '' }}>30</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Buscar conductor o departamento...">
                </div>
            </div>
        </div>

        <div class="table-responsive" id="tablaScrollContainer">
            <table class="table-licencias" id="tablaEmpleados">
                <thead>
                    <tr>
                        <th style="text-align: left; min-width: 250px;">Empleado / Departamento</th>
                        <th>Licencia Conductor <small>(Ligera)</small></th>
                        <th>Curso Man. Def. <small>(Ligera)</small></th>
                        <th>Licencia Federal <small>(Pesada)</small></th>
                        <th>Curso Man. Def. <small>(Pesada)</small></th>
                        <th style="min-width: 120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyEmpleados">
                    @forelse($empleados as $empleado)
                        <tr class="fila-empleado">
                            <td>
                                <div class="empleado-info">
                                    <div class="foto-avatar">
                                        @if($empleado->photo)
                                            <img src="{{ asset($empleado->photo) }}" alt="Foto" style="width: 100%; height: 100%; border-radius: 10px; object-fit: cover;">
                                        @else
                                            <i class="fas fa-user-tie"></i>
                                        @endif
                                    </div>
                                    <div class="empleado-detalles">
                                        <h4>{{ $empleado->full_name ?? $empleado->first_name . ' ' . $empleado->first_surname }}</h4>
                                        <span><i class="fas fa-sitemap"></i> {{ $empleado->department ?? 'Sin departamento' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{!! renderLicenseBadge(optional($empleado->license)->driver_license_expires_at) !!}</td>
                            <td>{!! renderLicenseBadge(optional($empleado->license)->light_defensive_course_expires_at) !!}</td>
                            <td>{!! renderLicenseBadge(optional($empleado->license)->federal_license_expires_at) !!}</td>
                            <td>{!! renderLicenseBadge(optional($empleado->license)->heavy_defensive_course_expires_at) !!}</td>
                            <td>
                                <button class="btn-actualizar"
                                    onclick="abrirModalActualizacion(
                                    {{ $empleado->id }},
                                    '{{ addslashes($empleado->full_name ?? $empleado->first_name . ' ' . $empleado->first_surname) }}',
                                    '{{ optional($empleado->license)->driver_license_expires_at ? optional($empleado->license)->driver_license_expires_at->format('Y-m-d') : '' }}',
                                    '{{ optional($empleado->license)->light_defensive_course_expires_at ? optional($empleado->license)->light_defensive_course_expires_at->format('Y-m-d') : '' }}',
                                    '{{ optional($empleado->license)->federal_license_expires_at ? optional($empleado->license)->federal_license_expires_at->format('Y-m-d') : '' }}',
                                    '{{ optional($empleado->license)->heavy_defensive_course_expires_at ? optional($empleado->license)->heavy_defensive_course_expires_at->format('Y-m-d') : '' }}'
                                )" title="Actualizar fechas de vigencia">
                                    <i class="fas fa-edit"></i> Actualizar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-users fa-3x mb-3" style="color: #cbd5e1;"></i>
                                <br>No hay empleados registrados en el sistema.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div id="noResultsMsg" class="no-results">
                <i class="fas fa-search fa-3x" style="color: #cbd5e1; margin-bottom: 15px;"></i>
                <h4>No se encontraron resultados</h4>
                <p>Intenta buscar con otro nombre o departamento.</p>
            </div>
        </div>

        <div class="pagination-wrapper">
            <nav>
                <ul class="pagination" id="customPagination"></ul>
            </nav>
        </div>
    </div>
@endsection

@section('modals')
    <div class="modal-licencias" id="modalActualizarLicencias">
        <div class="modal-content-lic">
            <div class="modal-header-lic">
                <h3>
                    <i class="fas fa-user-edit"></i>
                    Actualizar Credenciales
                </h3>
                <button class="btn-close-modal" onclick="cerrarModalActualizacion()" title="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="formActualizarLicencias" onsubmit="guardarDatosAJAX(event);">
                @csrf
                <input type="hidden" id="empleado_id_input">

                <div class="modal-grid">
                    <!-- Unidades Ligeras -->
                    <div class="modal-card ligera">
                        <h4>
                            <i class="fas fa-car"></i>
                            Unidades Ligeras
                        </h4>

                        <div class="form-group-lic">
                            <label>Vigencia Licencia Conductor</label>
                            <div class="input-icon-wrapper">
                                <input type="text" id="fecha_licencia" class="flatpickr-date" placeholder="Seleccionar fecha...">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                        </div>

                        <div class="form-group-lic">
                            <label>Vigencia Curso Man. Defensivo</label>
                            <div class="input-icon-wrapper">
                                <input type="text" id="fecha_curso_ligera" class="flatpickr-date" placeholder="Seleccionar fecha...">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Unidades Pesadas -->
                    <div class="modal-card pesada">
                        <h4>
                            <i class="fas fa-truck"></i>
                            Unidades Pesadas
                        </h4>

                        <div class="form-group-lic">
                            <label>Vigencia Licencia Federal</label>
                            <div class="input-icon-wrapper">
                                <input type="text" id="fecha_licencia_federal" class="flatpickr-date" placeholder="Seleccionar fecha...">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                        </div>

                        <div class="form-group-lic">
                            <label>Vigencia Curso Man. Def. Pesada</label>
                            <div class="input-icon-wrapper">
                                <input type="text" id="fecha_curso_pesada" class="flatpickr-date" placeholder="Seleccionar fecha...">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer-lic">
                    <button type="button" class="btn-cancelar" onclick="cerrarModalActualizacion()">Cancelar</button>
                    <button type="submit" class="btn-guardar" id="btnGuardarAJAX">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <script>
        let searchTimer;
        let currentPage = {{ $empleados->currentPage() }};
        let lastPage = {{ $empleados->lastPage() }};

        document.addEventListener("DOMContentLoaded", function() {
            flatpickr(".flatpickr-date", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                locale: "es",
                allowInput: true,
                clearButton: true,
                disableMobile: true
            });

            renderizarPaginacionUI(currentPage, lastPage);
        });

        document.getElementById('searchInput').addEventListener('keyup', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => fetchEmpleados(1), 400);
        });

        document.getElementById('per_page').addEventListener('change', function() {
            fetchEmpleados(1);
        });

        async function fetchEmpleados(page = 1) {
            currentPage = page;
            const perPage = document.getElementById('per_page').value;
            const search = document.getElementById('searchInput').value;
            const tbody = document.getElementById('tbodyEmpleados');
            const msgVacio = document.getElementById("noResultsMsg");

            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="loader-cell">
                        <div class="spinner"></div>
                        <div style="color: #6c757d; font-size: 0.9rem; margin-top: 10px;">
                            </i> Cargando datos...
                        </div>
                    </td>
                </tr>
            `;
            msgVacio.style.display = "none";

            const url = `{{ route('gerenciamiento.licenses') }}?page=${page}&per_page=${perPage}&search=${encodeURIComponent(search)}`;

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                renderizarTabla(data);
            } catch (error) {
                console.error("Error cargando los datos:", error);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4 text-danger">
                            <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                            <br>Error de conexión al cargar datos.
                        </td>
                    </tr>
                `;
            }
        }

        function renderBadgeJS(dateString) {
            if (!dateString)
                return '<span class="badge-vigencia bg-missing"><i class="fas fa-minus"></i> No Registrado</span>';

            try {
                const parts = dateString.split('-');
                const date = new Date(parts[0], parts[1] - 1, parts[2]);
                const now = new Date();
                now.setHours(0, 0, 0, 0);

                const daysDiff = Math.ceil((date - now) / (1000 * 60 * 60 * 24));
                const formatStr = `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${date.getFullYear()}`;

                if (daysDiff < 0) {
                    return `<span class="badge-vigencia bg-danger" data-tooltip="Vencido"><i class="fas fa-times"></i> ${formatStr}</span>`;
                } else if (daysDiff <= 30) {
                    return `<span class="badge-vigencia bg-orange" data-tooltip="Vence en ${daysDiff} días"><i class="fas fa-exclamation-circle"></i> ${formatStr}</span>`;
                } else if (daysDiff <= 60) {
                    return `<span class="badge-vigencia bg-warning" data-tooltip="Vence en ${daysDiff} días"><i class="fas fa-exclamation-triangle"></i> ${formatStr}</span>`;
                } else {
                    return `<span class="badge-vigencia bg-ok" data-tooltip="Vigente"><i class="fas fa-check"></i> ${formatStr}</span>`;
                }
            } catch (e) {
                return '<span class="badge-vigencia bg-missing"><i class="fas fa-question-circle"></i> Fecha inválida</span>';
            }
        }

        function renderizarTabla(payload) {
            const tbody = document.getElementById('tbodyEmpleados');
            const msgVacio = document.getElementById("noResultsMsg");

            if (payload.data.length === 0) {
                tbody.innerHTML = '';
                msgVacio.style.display = "block";
                document.getElementById('customPagination').innerHTML = '';
                return;
            }

            msgVacio.style.display = "none";

            let filasHTML = '';
            payload.data.forEach(emp => {
                const avatarHTML = emp.photo
                    ? `<img src="${emp.photo}" alt="Foto" style="width: 100%; height: 100%; border-radius: 10px; object-fit: cover;">`
                    : `<i class="fas fa-user-tie"></i>`;

                const empName = emp.name.replace(/'/g, "\\'");

                filasHTML += `
                <tr class="fila-empleado">
                    <td>
                        <div class="empleado-info">
                            <div class="foto-avatar">
                                ${avatarHTML}
                            </div>
                            <div class="empleado-detalles">
                                <h4>${emp.name}</h4>
                                <span><i class="fas fa-sitemap"></i> ${emp.department || 'Sin departamento'}</span>
                            </div>
                        </div>
                    </td>
                    <td>${renderBadgeJS(emp.driver_license)}</td>
                    <td>${renderBadgeJS(emp.light_course)}</td>
                    <td>${renderBadgeJS(emp.federal_license)}</td>
                    <td>${renderBadgeJS(emp.heavy_course)}</td>
                    <td>
                        <button class="btn-actualizar"
                                onclick="abrirModalActualizacion(
                                    ${emp.id},
                                    '${empName}',
                                    '${emp.driver_license || ''}',
                                    '${emp.light_course || ''}',
                                    '${emp.federal_license || ''}',
                                    '${emp.heavy_course || ''}'
                                )"
                                title="Actualizar fechas de vigencia">
                            <i class="fas fa-edit"></i> Actualizar
                        </button>
                    </td>
                </tr>
                `;
            });

            tbody.innerHTML = filasHTML;
            renderizarPaginacionUI(payload.pagination.current_page, payload.pagination.last_page);
        }

        function renderizarPaginacionUI(current, last) {
            const contenedor = document.getElementById('customPagination');

            if (last <= 1) {
                contenedor.innerHTML = '';
                return;
            }

            let html = '';

            // Botón anterior
            const prevDisabled = current === 1 ? 'disabled' : '';
            html += `<li class="page-item ${prevDisabled}">
                        <a class="page-link" href="#" data-page="${current - 1}">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                     </li>`;

            // Calcular rango de páginas a mostrar
            let startPage = Math.max(1, current - 2);
            let endPage = Math.min(last, current + 2);

            if (current <= 3) {
                endPage = Math.min(last, 5);
            }
            if (current >= last - 2) {
                startPage = Math.max(1, last - 4);
            }

            // Mostrar primera página si estamos lejos
            if (startPage > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
                if (startPage > 2) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            // Páginas numeradas
            for (let i = startPage; i <= endPage; i++) {
                const activeClass = current === i ? 'active' : '';
                html += `<li class="page-item ${activeClass}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                         </li>`;
            }

            // Mostrar última página si estamos lejos
            if (endPage < last) {
                if (endPage < last - 1) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
                html += `<li class="page-item"><a class="page-link" href="#" data-page="${last}">${last}</a></li>`;
            }

            // Botón siguiente
            const nextDisabled = current === last ? 'disabled' : '';
            html += `<li class="page-item ${nextDisabled}">
                        <a class="page-link" href="#" data-page="${current + 1}">
                            Siguiente <i class="fas fa-chevron-right"></i>
                        </a>
                     </li>`;

            contenedor.innerHTML = html;
            bindPaginationLinks();
        }

        function bindPaginationLinks() {
            document.querySelectorAll('#customPagination .page-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    if (this.parentElement.classList.contains('disabled') ||
                        this.parentElement.classList.contains('active')) {
                        return;
                    }

                    const page = parseInt(this.getAttribute('data-page'));
                    fetchEmpleados(page);
                });
            });
        }

        function abrirModalActualizacion(id, nombre, licLigera, cursoLigera, licPesada, cursoPesada) {
            document.getElementById('empleado_id_input').value = id;

            // Actualizar título del modal
            document.querySelector('#modalActualizarLicencias h3').innerHTML = `
                <i class="fas fa-user-edit"></i>
                Actualizar: ${nombre}
            `;

            // Establecer fechas en flatpickr
            if (document.getElementById('fecha_licencia')._flatpickr) {
                document.getElementById('fecha_licencia')._flatpickr.setDate(licLigera || null);
                document.getElementById('fecha_curso_ligera')._flatpickr.setDate(cursoLigera || null);
                document.getElementById('fecha_licencia_federal')._flatpickr.setDate(licPesada || null);
                document.getElementById('fecha_curso_pesada')._flatpickr.setDate(cursoPesada || null);
            }

            const modal = document.getElementById('modalActualizarLicencias');
            modal.classList.add('active');
            document.body.style.overflow = "hidden";
        }

        function cerrarModalActualizacion() {
            const modal = document.getElementById('modalActualizarLicencias');
            modal.classList.remove('active');
            document.body.style.overflow = "auto";
            document.getElementById('formActualizarLicencias').reset();
        }

        async function guardarDatosAJAX(e) {
            e.preventDefault();

            const idEmpleado = document.getElementById('empleado_id_input').value;
            const url = `/qhse/gerenciamiento/empleados/${idEmpleado}/actualizar-licencias`;
            const token = document.querySelector('input[name="_token"]').value;

            const data = {
                driver_license_expires_at: document.getElementById('fecha_licencia').value || null,
                light_defensive_course_expires_at: document.getElementById('fecha_curso_ligera').value || null,
                federal_license_expires_at: document.getElementById('fecha_licencia_federal').value || null,
                heavy_defensive_course_expires_at: document.getElementById('fecha_curso_pesada').value || null
            };

            Swal.fire({
                title: 'Guardando cambios...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizado!',
                        text: 'Las credenciales se guardaron correctamente.',
                        confirmButtonColor: '#0056b3',
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        cerrarModalActualizacion();
                        fetchEmpleados(currentPage);
                    });
                } else {
                    throw new Error(result.message || 'Error al guardar los datos');
                }

            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Hubo un problema de conexión.',
                    confirmButtonColor: '#dc3545'
                });
            }
        }

        // Cerrar modal con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('modalActualizarLicencias');
                if (modal.classList.contains('active')) {
                    cerrarModalActualizacion();
                }
            }
        });

        // Scroll suave al hacer clic en paginación
        function scrollToTopTabla() {
            const scrollContainer = document.getElementById('tablaScrollContainer');
            if (scrollContainer) {
                scrollContainer.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        }

        // Modificar bindPaginationLinks para incluir scroll
        const originalBind = bindPaginationLinks;
        bindPaginationLinks = function() {
            document.querySelectorAll('#customPagination .page-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    if (this.parentElement.classList.contains('disabled') ||
                        this.parentElement.classList.contains('active')) {
                        return;
                    }

                    const page = parseInt(this.getAttribute('data-page'));
                    fetchEmpleados(page);
                    scrollToTopTabla();
                });
            });
        };
    </script>
@endpush
