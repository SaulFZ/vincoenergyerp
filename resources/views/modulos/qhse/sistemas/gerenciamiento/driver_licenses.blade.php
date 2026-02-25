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
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .empleados-header h2 {
            color: var(--primary-blue);
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
            color: var(--text-muted);
        }

        .per-page-select {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            outline: none;
            background-color: var(--bg-light);
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.2s;
        }

        .per-page-select:focus {
            border-color: var(--primary-blue);
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
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .search-box input {
            padding: 10px 15px 10px 38px;
            border: 1px solid var(--border-color);
            border-radius: 25px;
            outline: none;
            width: 260px;
            font-size: 0.9rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background-color: var(--bg-light);
        }

        .search-box input:focus {
            border-color: var(--primary-blue);
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(0, 86, 179, 0.1);
            width: 300px;
        }

        .table-responsive {
            overflow-x: auto;
            width: 100%;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .table-licencias {
            width: 100%;
            border-collapse: collapse;
            min-width: 1050px;
            background: #ffffff;
        }

        .table-licencias th {
            background-color: var(--bg-light);
            color: var(--text-muted);
            font-weight: 600;
            text-align: center;
            padding: 15px 12px;
            border-bottom: 2px solid var(--border-color);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-licencias td {
            padding: 15px 12px;
            border-bottom: 1px solid var(--border-color);
            text-align: center;
            vertical-align: middle;
            font-size: 0.9rem;
            transition: background-color 0.2s;
        }

        .table-licencias tbody tr {
            transition: all 0.2s ease;
        }

        .table-licencias tbody tr:hover {
            background-color: #f8fafc;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
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
            color: var(--text-muted);
            font-size: 1.2rem;
            flex-shrink: 0;
            overflow: hidden;
        }

        .empleado-detalles h4 {
            margin: 0 0 2px 0;
            font-size: 0.95rem;
            color: var(--text-dark);
            font-weight: 600;
        }

        .empleado-detalles span {
            font-size: 0.75rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 4px;
        }

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

        /* Colores de estado */
        .bg-ok {
            background-color: #28a745; /* Verde */
            color: white;
        }

        .bg-warning {
            background-color: #ffc107; /* Amarillo (2 meses) */
            color: #212529;
        }

        .bg-orange {
            background-color: #fd7e14; /* Naranja (1 mes) */
            color: white;
        }

        .bg-danger {
            background-color: #dc3545; /* Rojo (Vencido) */
            color: white;
        }

        .bg-missing {
            background-color: #e9ecef;
            color: #6c757d;
        }

        .btn-actualizar {
            background-color: #eff6ff;
            color: var(--primary-blue);
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
            background-color: var(--primary-blue);
            color: #ffffff;
            border-color: var(--primary-blue);
            box-shadow: 0 2px 5px rgba(0, 86, 179, 0.2);
        }

        /* Paginación Personalizada */
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

        /* Estilos Modal */
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
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .modal-header-lic h3 {
            margin: 0;
            color: var(--text-dark);
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-header-lic h3 i {
            color: var(--primary-blue);
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
            color: var(--text-muted);
            transition: all 0.2s;
        }

        .btn-close-modal:hover {
            background: #fee2e2;
            color: #dc3545;
        }

        .form-group-lic {
            margin-bottom: 18px;
        }

        .form-group-lic label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-dark);
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
            color: var(--text-muted);
            pointer-events: none;
        }

        .input-icon-wrapper input {
            width: 100%;
            padding: 10px 35px 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .input-icon-wrapper input:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1);
            outline: none;
        }

        .modal-footer-lic {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 25px;
            border-top: 1px solid var(--border-color);
            padding-top: 20px;
        }

        .btn-guardar {
            background: var(--primary-blue);
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
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-cancelar {
            background: white;
            color: var(--text-muted);
            border: 1px solid var(--border-color);
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-cancelar:hover {
            background: var(--bg-light);
            color: var(--text-dark);
        }

        .no-results {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
            display: none;
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

        if ($daysDiff < 0) {
            return '<span class="badge-vigencia bg-danger"><i class="fas fa-times"></i> ' . $date->format('d/m/Y') . '</span>';
        } elseif ($daysDiff <= 30) {
            return '<span class="badge-vigencia bg-orange"><i class="fas fa-exclamation-circle"></i> ' . $date->format('d/m/Y') . '</span>';
        } elseif ($daysDiff <= 60) {
            return '<span class="badge-vigencia bg-warning"><i class="fas fa-exclamation-triangle"></i> ' . $date->format('d/m/Y') . '</span>';
        } else {
            return '<span class="badge-vigencia bg-ok"><i class="fas fa-check"></i> ' . $date->format('d/m/Y') . '</span>';
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
                        <option value="30" {{ $perPage == 30 ? 'selected' : '' }}>30</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>

                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Buscar conductor o depto...">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-licencias" id="tablaEmpleados">
                <thead>
                    <tr>
                        <th style="text-align: left;">Empleado / Departamento</th>
                        <th>Licencia Conductor<br><small>(Ligera)</small></th>
                        <th>Curso Man. Def.<br><small>(Ligera)</small></th>
                        <th>Licencia Federal<br><small>(Pesada)</small></th>
                        <th>Curso Man. Def.<br><small>(Pesada)</small></th>
                        <th>Acciones</th>
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
                                    '{{ $empleado->full_name ?? $empleado->first_name }}',
                                    '{{ optional($empleado->license)->driver_license_expires_at ? optional($empleado->license)->driver_license_expires_at->format('Y-m-d') : '' }}',
                                    '{{ optional($empleado->license)->light_defensive_course_expires_at ? optional($empleado->license)->light_defensive_course_expires_at->format('Y-m-d') : '' }}',
                                    '{{ optional($empleado->license)->federal_license_expires_at ? optional($empleado->license)->federal_license_expires_at->format('Y-m-d') : '' }}',
                                    '{{ optional($empleado->license)->heavy_defensive_course_expires_at ? optional($empleado->license)->heavy_defensive_course_expires_at->format('Y-m-d') : '' }}'
                                )">
                                    <i class="fas fa-edit"></i> Actualizar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No hay empleados registrados en el sistema.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div id="noResultsMsg" class="no-results">
                <i class="fas fa-search fa-3x" style="color: #cbd5e1; margin-bottom: 15px;"></i>
                <h4>No se encontraron conductores</h4>
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
                <h3 id="modalEmpleadoNombre"><i class="fas fa-user-edit"></i> Editar Fechas</h3>
                <button class="btn-close-modal" onclick="cerrarModalActualizacion()" title="Cerrar">&times;</button>
            </div>

            <form id="formActualizarLicencias" onsubmit="guardarDatosAJAX(event);">
                @csrf
                <input type="hidden" id="empleado_id_input">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div style="background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #e2e8f0;">
                        <h4 style="color: var(--primary-blue); margin-top: 0; font-size: 1.05rem; margin-bottom: 15px; display:flex; align-items:center; gap:8px;">
                            <i class="fas fa-car" style="font-size: 1.2rem;"></i> Unidades Ligeras
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

                    <div style="background: #fffbeb; padding: 20px; border-radius: 10px; border: 1px solid #fde68a;">
                        <h4 style="color: #d97706; margin-top: 0; font-size: 1.05rem; margin-bottom: 15px; display:flex; align-items:center; gap:8px;">
                            <i class="fas fa-truck" style="font-size: 1.2rem;"></i> Unidades Pesadas
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
                clearButton: true
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

            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin fa-2x" style="color: var(--primary-blue); margin-bottom:10px;"></i><br>Consultando datos...</td></tr>`;

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
                tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">Error de conexión al cargar datos.</td></tr>`;
            }
        }

        function renderBadgeJS(dateString) {
            if (!dateString)
                return '<span class="badge-vigencia bg-missing"><i class="fas fa-minus"></i> No Registrado</span>';

            const parts = dateString.split('-');
            const date = new Date(parts[0], parts[1] - 1, parts[2]);
            const now = new Date();
            now.setHours(0, 0, 0, 0);

            const daysDiff = Math.ceil((date - now) / (1000 * 60 * 60 * 24));
            const formatStr = `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${date.getFullYear()}`;

            if (daysDiff < 0) {
                return `<span class="badge-vigencia bg-danger"><i class="fas fa-times"></i> ${formatStr}</span>`;
            } else if (daysDiff <= 30) {
                return `<span class="badge-vigencia bg-orange"><i class="fas fa-exclamation-circle"></i> ${formatStr}</span>`;
            } else if (daysDiff <= 60) {
                return `<span class="badge-vigencia bg-warning"><i class="fas fa-exclamation-triangle"></i> ${formatStr}</span>`;
            } else {
                return `<span class="badge-vigencia bg-ok"><i class="fas fa-check"></i> ${formatStr}</span>`;
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

                filasHTML += `
                <tr class="fila-empleado">
                    <td>
                        <div class="empleado-info">
                            <div class="foto-avatar">
                                ${avatarHTML}
                            </div>
                            <div class="empleado-detalles">
                                <h4>${emp.name}</h4>
                                <span><i class="fas fa-sitemap"></i> ${emp.department}</span>
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
                                    '${emp.name}',
                                    '${emp.driver_license || ''}',
                                    '${emp.light_course || ''}',
                                    '${emp.federal_license || ''}',
                                    '${emp.heavy_course || ''}'
                                )">
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

            const prevDisabled = current === 1 ? 'disabled' : '';
            html += `<li class="page-item ${prevDisabled}">
                        <a class="page-link" href="#" data-page="${current - 1}"><i class="fas fa-chevron-left"></i> Anterior</a>
                     </li>`;

            let startPage = Math.max(1, current - 2);
            let endPage = Math.min(last, current + 2);

            if (current <= 3) {
                endPage = Math.min(last, 5);
            }
            if (current >= last - 2) {
                startPage = Math.max(1, last - 4);
            }

            for (let i = startPage; i <= endPage; i++) {
                const activeClass = current === i ? 'active' : '';
                html += `<li class="page-item ${activeClass}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                         </li>`;
            }

            const nextDisabled = current === last ? 'disabled' : '';
            html += `<li class="page-item ${nextDisabled}">
                        <a class="page-link" href="#" data-page="${current + 1}">Siguiente <i class="fas fa-chevron-right"></i></a>
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
            document.getElementById('modalEmpleadoNombre').innerHTML = `<i class="fas fa-user-edit"></i> Editar a: <b>${nombre}</b>`;

            document.getElementById('fecha_licencia')._flatpickr.setDate(licLigera || null);
            document.getElementById('fecha_curso_ligera')._flatpickr.setDate(cursoLigera || null);
            document.getElementById('fecha_licencia_federal')._flatpickr.setDate(licPesada || null);
            document.getElementById('fecha_curso_pesada')._flatpickr.setDate(cursoPesada || null);

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
                title: 'Guardando...',
                text: 'Actualizando base de datos...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizado!',
                        text: 'Las credenciales se guardaron correctamente.',
                        confirmButtonColor: '#0056b3'
                    }).then(() => {
                        cerrarModalActualizacion();
                        fetchEmpleados(currentPage);
                    });
                } else {
                    throw new Error(result.message);
                }

            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Hubo un problema de conexión.',
                    confirmButtonColor: '#dc3545'
                });
            }
        }
    </script>
@endpush
