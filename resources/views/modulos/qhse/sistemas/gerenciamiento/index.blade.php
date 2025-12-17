<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinco Energy - Gerenciamiento de Viajes</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="{{ asset('assets/css/qhse/gerenciamiento/index.css') }}" rel="stylesheet">


    @stack('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <header class="header-viajes">
        <div class="logo-container-viajes">
            <div class="logo-viajes">
                <div class="logo-img-viajes">
                    <img src="{{ asset('assets/img/logovinco1.png') }}" alt="Vinco Energy">
                </div>
            </div>
        </div>

        <nav class="nav-viajes">
            <a href="#" class="nav-link-viajes active" data-route="dashboard">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="#" class="nav-link-viajes" data-route="historial">
                <i class="fas fa-history"></i> Historial
            </a>
            <a href="#" class="nav-link-viajes" data-route="reportes">
                <i class="fas fa-chart-bar"></i> Reportes
            </a>
            <a href="#" class="nav-link-viajes" data-route="unidades">
                <i class="fas fa-truck-moving"></i> Gestión de Unidades
            </a>
        </nav>

        @include('components.layouts._user-profile')
    </header>

    <main class="container-viajes" id="main-content">
        <div class="card-base">
            <div class="compact-header">
                <div class="header-content">
                    <div class="title-section">
                        <h1 class="travel-title">
                            <i class="fas fa-route"></i>
                            Gerenciamiento de Viajes
                        </h1>
                        <p class="travel-subtitle">
                            Panel de control y registro completo de los viajes vehiculares de la compañía
                        </p>
                    </div>

                    <div class="header-stats stats-grid">
                        <div class="stat-card stat-active">
                            <div class="stat-icon stat-active-icon">
                                <i class="fas fa-truck-moving"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="active-count">18</span>
                                <span class="stat-label">Activos</span>
                            </div>
                        </div>

                        <div class="stat-card stat-pending">
                            <div class="stat-icon stat-pending-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="pending-count">5</span>
                                <span class="stat-label">Pendientes</span>
                            </div>
                        </div>

                        <div class="stat-card stat-completed">
                            <div class="stat-icon stat-completed-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="completed-count">89</span>
                                <span class="stat-label">Completados</span>
                            </div>
                        </div>

                        <div class="stat-card stat-available">
                            <div class="stat-icon stat-available-icon">
                                <i class="fas fa-car-side"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="available-count">42</span>
                                <span class="stat-label">Unidades Disp.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="filters-section">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="solicitud-date">
                            <i class="fas fa-calendar-day"></i>
                            Fecha Solicitud de Viaje
                        </label>
                        <input type="date" id="solicitud-date" class="form-control">
                    </div>

                    <div class="filter-group">
                        <label for="destino-filter">
                            <i class="fas fa-map-marker-alt"></i>
                            Destino del Viaje
                        </label>
                        <select id="destino-filter" class="form-control">
                            <option value="all">Todos los Destinos</option>
                            <option value="COA">Coatzacoalcos</option>
                            <option value="TAB">Paraíso, Tab.</option>
                            <option value="CAR">Cd. del Carmen</option>
                            <option value="OTRO">Otro Destino</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="status-filter">
                            <i class="fas fa-filter"></i>
                            Estatus
                        </label>
                        <select id="status-filter" class="form-control">
                            <option value="all">Todos</option>
                            <option value="approved">Aprobado</option>
                            <option value="reviewed">Revisado</option>
                            <option value="rejected">Rechazado</option>
                            <option value="under_review">En Revisión</option>
                            <option value="completed">Completado</option>
                            <option value="active">Activo (En Curso)</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="riesgo-filter">
                            <i class="fas fa-exclamation-triangle"></i>
                            Nivel de Riesgo
                        </label>
                        <select id="riesgo-filter" class="form-control">
                            <option value="all">Todos</option>
                            <option value="bajo">Bajo</option>
                            <option value="medio">Medio</option>
                            <option value="alto">Alto</option>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button class="btn-clear-filters" id="clear-filters">
                            <i class="fas fa-eraser"></i>
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-dashboard-container">
            <div class="table-header">
                <h3>
                    <i class="fas fa-history"></i>
                    Viajes Recientes
                </h3>
                <button type="button" class="btn-viajes btn-primary" onclick="gestionarModalFormulario(true)">
                    <i class="fas fa-plus-circle"></i>
                    Nueva Solicitud de Viaje
                </button>
            </div>
            <table class="table-viajes">
                <thead>
                    <tr>
                        <th class="th-codigo">N°</th>
                        <th class="th-nombre">Solicitante</th>
                        <th class="th-departamento">Departamento</th>
                        <th class="th-destino">Destino</th>
                        <th class="th-fechas">Fechas de Viaje</th>
                        <th class="th-riesgo">Riesgo</th>
                        <th class="th-estado">Estado</th>
                        <th class="th-acciones">Acc</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>GV-001</strong></td>
                        <td>Juan Pérez González</td>
                        <td>Operaciones</td>
                        <td>Coatzacoalcos, Ver.</td>
                        <td>15 al 20 Mar 2025</td>
                        <td>
                            <span class="badge-riesgo status-riesgo-bajo">Bajo</span>
                        </td>
                        <td>
                            <span class="badge-status status-aprobado">Aprobado</span>
                        </td>
                        <td>
                            <button class="btn-action-small btn-view" title="Ver detalles">
                                <i class="fas fa-eye"></i> Ver
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>GV-002</strong></td>
                        <td>María González Sánchez</td>
                        <td>Administración</td>
                        <td>Paraíso, Tab.</td>
                        <td>22 al 25 Mar 2025</td>
                        <td>
                            <span class="badge-riesgo status-riesgo-medio">Medio</span>
                        </td>
                        <td>
                            <span class="badge-status status-pendiente">Pendiente</span>
                        </td>
                        <td>
                            <button class="btn-action-small btn-edit" title="Editar">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>GV-003</strong></td>
                        <td>Carlos Rodríguez López</td>
                        <td>Comercial</td>
                        <td>Cárdenas, Tab.</td>
                        <td>10 al 12 Abr 2025</td>
                        <td>
                            <span class="badge-riesgo status-riesgo-alto">Alto</span>
                        </td>
                        <td>
                            <span class="badge-status status-encurso">En Curso</span>
                        </td>
                        <td>
                            <button class="btn-action-small btn-view" title="Ver reporte">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>GV-004</strong></td>
                        <td>Ana López Martínez</td>
                        <td>Operaciones</td>
                        <td>Ciudad del Carmen, Camp.</td>
                        <td>01 al 03 May 2025</td>
                        <td>
                            <span class="badge-riesgo status-riesgo-bajo">Bajo</span>
                        </td>
                        <td>
                            <span class="badge-status status-cancelado">Cancelado</span>
                        </td>
                        <td>
                            <button class="btn-action-small btn-view" title="Ver detalles">
                                <i class="fas fa-eye"></i> Ver
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

    <div class="modal-overlay" id="modalFormulario">
        <div class="modal-content">
            <div class="form-header-modal">
                <div class="form-header-info">
                    <div class="logo-img-viajes">
                        <img src="{{ asset('assets/img/logovinco2.png') }}" alt="Vinco Energy">
                    </div>
                    <div class="form-header-title">
                        <h2>Solicitud de Gerenciamiento de Viaje</h2>
                        <p>Formato F-6.1-VINCO-04</p>
                    </div>
                </div>

                <div class="header-right-group">
                    <div class="form-document-detail request-date">
                        <label>Fecha de Solicitud</label>
                        <span id="fechaSolicitudDisplay">08/12/2025</span>
                        <input type="hidden" name="fecha_solicitud" id="fechaSolicitudHidden">
                    </div>

                    <div class="form-document-detail travel-code">
                        <label>N° de Gerenciamiento</label>
                        <span class="form-code-value" id="codigoViaje">N°:GV-004</span>
                    </div>
                </div>
            </div>

            <form id="formViaje" class="form-body">
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-info-circle"></i>
                        Información General
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>
                                <i class="fas fa-user-tie"></i>
                                Solicitante (Creador de GM) <span class="required">*</span>
                            </label>
                            <input type="text" name="solicitante" id="solicitante" readonly required>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-building"></i>
                                Departamento <span class="required">*</span>
                            </label>
                            <input type="text" name="departamento" id="departamento" readonly required>
                        </div>
                        <div class="form-group">
                            <label>
                                <i class="fas fa-map-marker-alt"></i>
                                Destino del Viaje <span class="required">*</span>
                            </label>
                            <select name="destino_predefinido" id="destinoPredefinido" required>
                                <option value="">Seleccione un lugar de la lista</option>
                                <option value="Coatzacoalcos, Veracruz">Coatzacoalcos, Veracruz</option>
                                <option value="Paraíso, Tabasco">Paraíso, Tabasco</option>
                                <option value="Cárdenas, Tabasco">Cárdenas, Tabasco</option>
                                <option value="Ciudad del Carmen, Campeche">Ciudad del Carmen, Campeche</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>
                                <i class="fas fa-pencil-alt"></i>
                                Especifique Destino
                            </label>
                            <input type="text" name="destino_especifico" id="destinoEspecifico"
                                placeholder="Especifique el destino aquí (obligatorio si selecciona 'Otro')">
                        </div>
                    </div>

                    <h3 class="form-section-title">
                        <i class="fas fa-route"></i>
                        Detalles del Trayecto
                    </h3>

                    <div class="trayecto-grid">
                        <div class="form-group">
                            <label>
                                <i class="fas fa-map-pin"></i>
                                Saliendo de <span class="required">*</span>
                            </label>
                            <input type="text" name="origen" placeholder="Ej: Villahermosa, Tabasco" required>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-map-marker"></i>
                                Llegando a
                            </label>
                            <input type="text" name="llegada" placeholder="Ej: Villahermosa, Tabasco (Opcional)">
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-calendar-day"></i>
                                Fecha de Inicio <span class="required">*</span>
                            </label>
                            <div class="datetime-input-wrapper">
                                <input type="text" name="fecha_inicio" id="fechaInicioViaje"
                                    placeholder="Seleccione fecha" required>
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-calendar-check"></i>
                                Fecha de Término
                            </label>
                            <div class="datetime-input-wrapper">
                                <input type="text" name="fecha_fin" id="fechaFinViaje"
                                    placeholder="Seleccione fecha">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-clock"></i>
                                Hora de Inicio <span class="required">*</span>
                            </label>
                            <div class="datetime-input-wrapper">
                                <input type="text" name="hora_inicio" id="horaInicioViaje"
                                    placeholder="HH:MM AM/PM" required>
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-clock"></i>
                                Hora de Término
                            </label>
                            <div class="datetime-input-wrapper">
                                <input type="text" name="hora_fin" id="horaFinViaje" placeholder="HH:MM AM/PM">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-truck-moving"></i>
                        Conductor y Unidad Solicitada
                        <span class="unidades-count" id="contadorUnidades">0</span>
                        <span class="unidades-label" id="label-tipo-unidad"></span>
                        <button type="button" class="btn-viajes btn-secondary-convoy" id="btnReunionPreConvoy"
                            onclick="gestionarModalPreConvoy(true)" disabled
                            title="Realizar la reunión antes de enviar la solicitud.">
                            <i class="fas fa-handshake"></i> Reunión Pre-convoy
                        </button>
                    </h3>

                    <div class="unidades-container">
                        <table class="unidades-table" id="tablaUnidades">
                            <thead>
                                <tr>
                                    <th class="th-conductor-completo">
                                        <span class="column-title">Conductor</span>
                                    </th>

                                    <th class="th-vigencia">
                                        <span class="column-title">Vigencias</span>
                                    </th>

                                    <th class="th-hrs-sueno">
                                        <span class="column-title">Hrs Sueño</span>
                                    </th>

                                    <th class="th-horas-conduccion">
                                        <span class="column-title">Hrs Conducción</span>
                                    </th>

                                    <th class="th-pasajeros">
                                        <span class="column-title">Pasajeros</span>
                                    </th>

                                    <th class="th-vehiculo">
                                        <span class="column-title">Vehículo</span>
                                    </th>

                                    <th class="th-inspeccion">
                                        <span class="column-title">Insp.</span>
                                    </th>

                                    <th class="th-acciones">
                                        <span class="column-title">Acciones</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="cuerpoTablaUnidades">
                            </tbody>
                        </table>
                    </div>

                    <button type="button" class="btn-add-unidad" id="btnAgregarUnidad" onclick="agregarUnidad()">
                        <i class="fas fa-plus-circle"></i>
                        Agregar Unidad (Máximo 5)
                    </button>
                </div>
            </form>

            <div class="form-footer">
                <button type="button" class="btn-viajes btn-cancel" onclick="gestionarModalFormulario(false)">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" form="formViaje" class="btn-viajes btn-submit" id="btnEnviarSolicitud">
                    <i class="fas fa-paper-plane"></i>
                    Enviar Solicitud
                </button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalPreConvoy">
        <div class="modal-content">
            <div class="form-header-modal">
                <div class="form-header-info">
                    <div class="form-header-title">
                        <h2>REUNIÓN PRE-CONVOY Y CHECKLIST DE SEGURIDAD</h2>
                        <p>Asegure la coordinación y seguridad de todas las unidades.</p>
                    </div>
                </div>
                <div class="form-code-document">
                    <button type="button" class="btn-viajes btn-cancel" onclick="gestionarModalPreConvoy(false)">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>

            <form id="formPreConvoy" class="modal-inspeccion-body">
                <h3 class="inspeccion-modal-title">
                    <i class="fas fa-users"></i>
                    Designación de Líder y Confirmación de Seguridad
                </h3>

                <div class="form-group full-width" style="margin-bottom: 30px;">
                    <label for="liderConvoy">
                        <i class="fas fa-crown"></i>
                        Líder de Convoy <span class="required">*</span>
                    </label>
                    <select id="liderConvoy" name="lider_convoy" class="form-control" required>
                        <option value="">Seleccione un conductor como Líder</option>
                    </select>
                </div>

                <div class="inspeccion-grid" id="checklistPreConvoy">
                </div>
            </form>

            <div class="form-footer">
                <button type="button" class="btn-viajes btn-primary" id="btnGuardarPreConvoy"
                    onclick="guardarPreConvoy()">
                    <i class="fas fa-save"></i> Guardar y Confirmar Reunión
                </button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalInspeccion">
        <div class="modal-content">
            <div class="form-header-modal">
                <div class="form-header-info">
                    <div class="form-header-title">
                        <h2>INSPECCIÓN VEHICULAR PRE-VIAJE</h2>
                        <p>Unidad: <span id="inspeccionUnidadNombre">N/A</span></p>
                    </div>
                </div>
                <div class="form-code-document">
                    <button type="button" class="btn-viajes btn-cancel" onclick="gestionarModalInspeccion(false)">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>

            <form id="formInspeccion" class="modal-inspeccion-body">
                <input type="hidden" id="inspeccionUnidadIndex" name="unidad_index">

                <h3 class="inspeccion-modal-title">
                    <i class="fas fa-car-side"></i>
                    Revisión de Documentos y Elementos de Seguridad
                </h3>

                <div class="inspeccion-grid" id="inspeccionGrid">
                </div>
            </form>

            <div class="form-footer">
                <button type="button" class="btn-viajes btn-primary" onclick="guardarInspeccion()">
                    <i class="fas fa-save"></i> Guardar Inspección
                </button>
            </div>
        </div>
    </div>

    <footer class="footer-viajes">
        <p>
            <i class="fas fa-shield-alt"></i>
            Sistema de Gerenciamiento de Viajes - Vinco Energy © 2025 | Todos los derechos reservados
        </p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/js/sessionTimer.js') }}"></script>

    <script>
        // El código siempre es completo.

        let codigoViaje = 4;
        let contadorUnidades = 0;
        const MAX_UNIDADES = 5;
        const MAX_PASAJEROS = 4;

        // Almacenamiento temporal
        const datosInspeccion = {};
        // 🔑 NUEVO: Almacenamiento temporal para la reunión de convoy 🔑q
        let datosReunionConvoy = {}; // Para guardar los datos del checklist

        // Datos de ejemplo
        const vehiculos = [
            "Camioneta Pick-up",
            "Camión de Carga 3.5T",
            "Camión de Carga 5T",
            "SUV Ejecutivo",
            "Vehículo de Servicio",
            "Grúa",
            "Motoniveladora",
            "Retroexcavadora"
        ];

        // Lugares predefinidos para el nuevo selector
        const lugaresDestino = [
            "Coatzacoalcos, Veracruz",
            "Paraíso, Tabasco",
            "Cárdenas, Tabasco",
            "Ciudad del Carmen, Campeche"
        ];


        // ***************************************************************
        // MODIFICACIÓN 1: Usar nombre completo y añadir campo 'manDefVigencia'
        // ***************************************************************
        const datosConductores = {
            "Juan Pérez González": {
                vigencia: "2026-05-10",
                manDefVigencia: "2026-10-01"
            },
            "María González Sánchez": {
                vigencia: "2025-12-01",
                manDefVigencia: "2025-11-28"
            },
            "Carlos Rodríguez López": {
                vigencia: "2027-01-20",
                manDefVigencia: "2027-01-15"
            },
            "Ana López Martínez": {
                vigencia: "2025-10-15",
                manDefVigencia: "2025-09-30"
            },
            "Pedro Martínez Ruiz": {
                vigencia: "2026-11-25",
                manDefVigencia: "2026-11-15"
            },
            "Raúl Sánchez Gómez": {
                vigencia: "2027-03-08",
                manDefVigencia: "2027-03-01"
            },
            "Laura Ramírez Hernández": {
                vigencia: "2025-08-30",
                manDefVigencia: "2025-08-25"
            },
            "Gabriel Torres Cruz": {
                vigencia: "2026-06-12",
                manDefVigencia: "2026-06-05"
            }
        };

        const conductores = Object.keys(datosConductores);

        // Ítems de inspección
        const itemsInspeccion = [{
                label: "Documentos en regla (Tarjeta, Póliza)",
                icon: "fas fa-file-alt",
                name: "docs"
            },
            {
                label: "Llantas en buen estado (incl. refacción)",
                icon: "fas fa-tire",
                name: "llantas"
            },
            {
                label: "Luces funcionales (alta, baja, freno)",
                icon: "fas fa-lightbulb",
                name: "luces"
            },
            {
                label: "Extintor vigente y cargado",
                icon: "fas fa-fire-extinguisher",
                name: "extintor"
            },
            {
                label: "Botiquín de primeros auxilios",
                icon: "fas fa-suitcase-medical",
                name: "botiquin"
            },
            {
                label: "Kit de carretera (triángulos, chaleco)",
                icon: "fas fa-road",
                name: "kit"
            },
            {
                label: "Niveles de fluidos (aceite, agua)",
                icon: "fas fa-gas-pump",
                name: "fluidos"
            },
            {
                label: "Frenos en óptimas condiciones",
                icon: "fas fa-hand-paper",
                name: "frenos"
            }
        ];

        // 🔑 NUEVO: Ítems del Checklist de Reunión Pre-Convoy 🔑
        const checklistPreConvoy = [{
                label: "Todos los conductores comprenden donde serán los puntos de parada o reporte?",
                name: "puntos_parada",
                icon: "fas fa-map-marked-alt"
            },
            {
                label: "¿Todos los conductores saben que hacer en caso de ruptura del convoy?",
                name: "ruptura_convoy",
                icon: "fas fa-road-barrier"
            },
            {
                label: "Se asegura que todos los conductores verificarón la documentación vigente (tarjeta, poliza, permisos)",
                name: "doc_vigente",
                icon: "fas fa-folder-open"
            },
            {
                label: "Se asegura que los conductores sean concientes de aplicar la medidas y controles para prevenir accidentes o incidentes",
                name: "prevencion_acc",
                icon: "fas fa-helmet-safety"
            },
            {
                label: "Todos los conductores tiene y llevan consigo los contactos de emergencia / PRE",
                name: "contactos_emerg",
                icon: "fas fa-phone-volume"
            },
            {
                label: "Como líder de convoy manifiesta tener el compromiso y liderazgo para guiar este viaje",
                name: "compromiso_lider",
                icon: "fas fa-users-line"
            }
        ];
        // 🔑 FIN NUEVO 🔑


        // Nueva configuración para la hora con selector visual AM/PM
        const configHoraVisual = {
            enableTime: true,
            noCalendar: true,
            dateFormat: "h:i K", // h:i para 12h, K para AM/PM
            time_24hr: false, // Forzar modo 12 horas para usar AM/PM
            minuteIncrement: 15,
            disableMobile: true,
            allowInput: true,
            static: true, // Para mantener el selector de hora como un dropdown más bonito.
            wrap: false, // Importante para que no aplique estilos adicionales
            locale: flatpickr.l10ns.es, // Aplicar localización en AM/PM
        };

        // Inicialización de Flatpickr
        function inicializarFlatpickr() {
            flatpickr.localize(flatpickr.l10ns.es);

            // Configuración para fechas en español
            const configFecha = {
                dateFormat: "d/m/Y",
                locale: "es",
                minDate: "today",
                disableMobile: true, // Mejor experiencia en desktop
                allowInput: true,
                clickOpens: true,
                nextArrow: '<i class="fas fa-chevron-right"></i>',
                prevArrow: '<i class="fas fa-chevron-left"></i>',
            };

            // Aplicar a los campos principales
            const fechaInicio = flatpickr("#fechaInicioViaje", configFecha);
            const fechaFin = flatpickr("#fechaFinViaje", configFecha);

            // Aplicar la nueva configuración de hora con AM/PM al modal principal
            const horaInicio = flatpickr("#horaInicioViaje", configHoraVisual);
            const horaFin = flatpickr("#horaFinViaje", configHoraVisual);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Configurar fecha actual y departamento
            const hoy = new Date();

            // Formato legible para el span (DD/MM/YYYY)
            const fechaDisplay = hoy.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });

            // Formato para el input hidden (YYYY-MM-DD)
            const fechaHidden = hoy.toISOString().split('T')[0];

            // **CORRECCIÓN:** Actualizar los IDs correctos: fechaSolicitudDisplay y fechaSolicitudHidden
            document.getElementById('fechaSolicitudDisplay').textContent = fechaDisplay;
            document.getElementById('fechaSolicitudHidden').value = fechaHidden;

            document.getElementById('departamento').value = 'Administracion';
            // Configurar Solicitante (simulado)
            document.getElementById('solicitante').value =
                'Saul Falcon Perez'; // Simulación de usuario logeado

            // Navegación
            document.querySelectorAll('.nav-link-viajes').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelectorAll('.nav-link-viajes').forEach(item => {
                        item.classList.remove('active');
                    });
                    this.classList.add('active');
                });
            });

            // Lógica para el campo "Especifique Destino"
            const destinoSelector = document.getElementById('destinoPredefinido');
            const destinoEspecifico = document.getElementById('destinoEspecifico');

            destinoSelector.addEventListener('change', function() {
                // Si el valor seleccionado es "Otro", el campo "Especifique Destino" es obligatorio (required)
                if (this.value === 'Otro') {
                    destinoEspecifico.required = true;
                    // El campo ya no se deshabilita, pero el required sí se activa.
                } else {
                    // Si se selecciona un destino predefinido, el campo no es requerido y se limpia (opcionalmente)
                    // destinoEspecifico.value = ''; // Se mantiene el texto en el campo si es un destino predefinido.
                    destinoEspecifico.required = false;
                }
            });
            // Al inicio, si el selector no tiene "Otro", no es requerido
            if (destinoSelector.value !== 'Otro') {
                destinoEspecifico.required = false;
            }


            // Inicializar Flatpickr
            inicializarFlatpickr();

            // Envío del formulario
            document.getElementById('formViaje').addEventListener('submit', function(e) {
                e.preventDefault();
                enviarSolicitud();
            });

            // Generar código inicial
            generarCodigoViaje(false);

            // Inicializar el label de tipo de unidad
            actualizarLabelTipoUnidad();
            // 🔑 NUEVO: Inicializar el estado del botón de Reunión 🔑
            actualizarBotonReunionConvoy();
        });

        // ====================================================================
        // Funciones de Autocompletado y Docs del Conductor - MEJORADAS
        // ====================================================================
        function inicializarAutocompleteConductor(unidadNumero) {
            const input = document.getElementById(`conductor-${unidadNumero}`);
            const listContainer = document.getElementById(`autocomplete-list-${unidadNumero}`);
            let currentFocus = -1;

            const updateList = () => {
                const val = input.value;
                listContainer.innerHTML = '';
                currentFocus = -1;

                if (!val) {
                    // Limpiar solo si se borra el contenido
                    if (input.dataset.conductorSeleccionado !== undefined) {
                        actualizarDatosConductor(unidadNumero, '');
                    }
                    delete input.dataset.conductorSeleccionado;
                    return false;
                }

                const filtered = conductores.filter(c => c.toUpperCase().includes(val.toUpperCase()));

                if (filtered.length === 0) {
                    actualizarDatosConductor(unidadNumero, '');
                    listContainer.innerHTML =
                        `<div class="autocomplete-item" style="color: var(--accent-red); font-weight: normal; cursor: default;">No encontrado</div>`;
                } else {
                    filtered.forEach((c, index) => {
                        const item = document.createElement('div');
                        item.classList.add('autocomplete-item');
                        item.innerHTML = c;
                        item.addEventListener('click', function(e) {
                            input.value = c;
                            input.dataset.conductorSeleccionado = c; // Marcar como seleccionado
                            listContainer.innerHTML = '';
                            actualizarDatosConductor(unidadNumero, c);
                            // 🔑 NUEVO: Verificar el estado del botón Reunión al seleccionar un conductor 🔑
                            actualizarBotonReunionConvoy();
                        });
                        listContainer.appendChild(item);
                    });
                }
            }

            const navigateList = (direction) => {
                const items = listContainer.querySelectorAll('.autocomplete-item');

                if (items.length === 0) return;

                if (currentFocus !== -1) {
                    items[currentFocus].classList.remove('selected');
                }

                currentFocus += direction;

                if (currentFocus >= items.length) {
                    currentFocus = 0;
                }
                if (currentFocus < 0) {
                    currentFocus = items.length - 1;
                }

                items[currentFocus].classList.add('selected');
                items[currentFocus].scrollIntoView({
                    block: 'nearest'
                });
            }

            input.addEventListener('input', updateList);
            input.addEventListener('focus', updateList);

            input.addEventListener('keydown', function(e) {
                if (e.key === "ArrowDown") {
                    e.preventDefault();
                    navigateList(1);
                } else if (e.key === "ArrowUp") {
                    e.preventDefault();
                    navigateList(-1);
                } else if (e.key === "Enter" || e.key === "Tab") {
                    if (currentFocus > -1) {
                        e.preventDefault();
                        listContainer.querySelectorAll('.autocomplete-item')[currentFocus].click();
                    } else if (e.key === "Enter") {
                        const exactMatch = conductores.find(c => c.toUpperCase() === input.value.toUpperCase());
                        if (exactMatch) {
                            input.value = exactMatch;
                            input.dataset.conductorSeleccionado = exactMatch;
                            actualizarDatosConductor(unidadNumero, exactMatch);
                            listContainer.innerHTML = '';
                            // 🔑 NUEVO: Verificar el estado del botón Reunión al presionar Enter 🔑
                            actualizarBotonReunionConvoy();
                        } else {
                            actualizarDatosConductor(unidadNumero, '');
                            listContainer.innerHTML = '';
                        }
                    }
                }
            });

            input.addEventListener('blur', function() {
                setTimeout(() => {
                    listContainer.innerHTML = '';
                    const exactMatch = conductores.find(c => c.toUpperCase() === input.value.toUpperCase());
                    // Si no hay un match exacto con el valor, o si no se ha seleccionado previamente, limpiar la vigencia
                    if (!exactMatch || input.dataset.conductorSeleccionado !== input.value) {
                        actualizarDatosConductor(unidadNumero, '');
                        // 🔑 NUEVO: Verificar el estado del botón Reunión al perder el foco 🔑
                        actualizarBotonReunionConvoy();
                    }
                    delete input.dataset.conductorSeleccionado;
                }, 150);
            });
        }

        /**
         * Modificada para actualizar solo los campos de vigencia con los datos del conductor.
         * El campo de Man. Def. ahora toma la fecha predefinida y se hace de solo lectura.
         */
        function actualizarDatosConductor(unidadNumero, nombreConductor) {
            const inputVigenciaLic = document.getElementById(`vigencia-lic-${unidadNumero}`);
            const inputVigenciaMan = document.getElementById(`vigencia-man-${unidadNumero}`);

            const data = datosConductores[nombreConductor];

            if (data) {
                // Vigencia Licencia (sigue igual)
                inputVigenciaLic.value = data.vigencia;

                // ***************************************************************
                // MODIFICACIÓN 2: Llenar Vigencia Man. Def. con fecha predefinida y hacerlo de solo lectura
                // ***************************************************************
                inputVigenciaMan.value = data.manDefVigencia; // Usar la fecha del objeto de datos
                inputVigenciaMan.readOnly = true; // Forzar a solo lectura para evitar modificación
            } else {
                inputVigenciaLic.value = '';
                inputVigenciaMan.value = ''; // Limpiar también la fecha de Man. Def. si se borra el conductor
                inputVigenciaMan.readOnly = true; // Deshabilitar si no hay conductor seleccionado de la lista
            }
        }

        // ====================================================================
        // Funciones de Cálculo de Horas - MODIFICADAS PARA ACEPTAR SOLO TEXTO
        // ====================================================================
        function parseTimeForCalculation(timeStr) {
            if (!timeStr) return null;

            // Usa una expresión regular para manejar H:i o h:i K (12h AM/PM)
            const regex12hr = /(\d{1,2}):(\d{2})\s*(AM|PM)/i;
            const match12hr = timeStr.match(regex12hr);

            if (match12hr) {
                let [, h, m, ampm] = match12hr;
                h = parseInt(h);
                m = parseInt(m);

                if (ampm.toUpperCase() === 'PM' && h !== 12) {
                    h += 12;
                } else if (ampm.toUpperCase() === 'AM' && h === 12) {
                    h = 0; // Medianoche
                }
                return h * 60 + m; // Total de minutos en 24h
            } else {
                // Intenta formato 24h simple si no coincide con AM/PM
                const [h, m] = timeStr.split(':').map(Number);
                if (!isNaN(h) && !isNaN(m)) {
                    return h * 60 + m;
                }
            }

            return null; // Fallback
        }

        function calcularHorasDormidas(unidadNumero) {
            const horaDormirInput = document.getElementById(`dormir-${unidadNumero}`);
            const horaLevantarInput = document.getElementById(`levantar-${unidadNumero}`);
            const totalDormidasInput = document.getElementById(`total-hrs-dormidas-${unidadNumero}`);

            const dormir = horaDormirInput ? horaDormirInput.value : null;
            const levantar = horaLevantarInput ? horaLevantarInput.value : null;

            const totalMinutosDormir = parseTimeForCalculation(dormir);
            let totalMinutosLevantar = parseTimeForCalculation(levantar);

            if (totalMinutosDormir !== null && totalMinutosLevantar !== null) {
                // Si la hora de levantar es antes o igual a la de dormir, asume que es al día siguiente (+24 horas)
                if (totalMinutosLevantar <= totalMinutosDormir) {
                    totalMinutosLevantar += 24 * 60;
                }

                const duracionMinutos = totalMinutosLevantar - totalMinutosDormir;
                const duracionHoras = (duracionMinutos / 60).toFixed(1);

                totalDormidasInput.value = duracionHoras;
            } else {
                totalDormidasInput.value = '0.0';
            }

            // La columna 4 ahora es solo texto, no hay cálculo automático con la columna 3.
            // La función calcularTotalHoras ya no será llamada aquí.
        }

        function calcularTotalHoras(unidadNumero) {
            // Esta función ha sido modificada para manejar la conversión de texto a número si es posible,
            // pero ya no se usa onchange, por lo que este cálculo es solo de ejemplo si se activara manualmente.
            const hrsDespiertoInput = document.getElementById(`horas-despierto-${unidadNumero}`);
            const hrsViajeInput = document.getElementById(`horas-viaje-${unidadNumero}`);
            const totalHrsInput = document.getElementById(`total-hrs-finalizar-${unidadNumero}`);

            // Intentar parsear los valores de texto a flotante
            const hrsDespierto = parseFloat(hrsDespiertoInput ? hrsDespiertoInput.value : 0) || 0;
            const hrsViaje = parseFloat(hrsViajeInput ? hrsViajeInput.value : 0) || 0;

            const totalHoras = (hrsDespierto + hrsViaje).toFixed(1);
            if (totalHrsInput) {
                totalHrsInput.value = totalHoras;

                // Lógica de advertencia (se mantiene)
                if (totalHoras > 14) {
                    totalHrsInput.style.backgroundColor = 'var(--accent-red)';
                    totalHrsInput.style.color = 'var(--white)';
                    Swal.fire({
                        title: '¡Advertencia de Horas!',
                        text: `La unidad ${unidadNumero} acumulará ${totalHoras} horas totales. Esto excede el límite recomendado de 14 horas.`,
                        icon: 'warning',
                        confirmButtonColor: 'var(--primary-blue)'
                    });
                } else {
                    totalHrsInput.style.backgroundColor = 'var(--light-gray)';
                    totalHrsInput.style.color = 'var(--dark-gray)';
                }
            }
        }

        // ====================================================================
        // Funciones de Gestión del Modal Principal
        // ====================================================================
        function gestionarModalFormulario(abrir) {
            const modal = document.getElementById('modalFormulario');
            if (abrir) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
                if (contadorUnidades === 0) {
                    agregarUnidad();
                }
            } else {
                Swal.fire({
                    title: '¿Desea cerrar el formulario?',
                    text: 'Se perderán los datos no guardados de la solicitud actual.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cerrar',
                    cancelButtonText: 'Continuar editando',
                    confirmButtonColor: 'var(--accent-red)',
                    cancelButtonColor: 'var(--primary-blue)',
                }).then((result) => {
                    if (result.isConfirmed) {
                        modal.classList.remove('active');
                        document.body.style.overflow = 'auto';
                        limpiarFormulario();
                    }
                });
            }
        }

        function limpiarFormulario() {
            document.getElementById('formViaje').reset();
            document.getElementById('cuerpoTablaUnidades').innerHTML = '';
            contadorUnidades = 0;
            actualizarContadorUnidades();
            actualizarLabelTipoUnidad(); // Limpiar label
            actualizarBotonAgregar();
            generarCodigoViaje(false);

            for (const key in datosInspeccion) {
                delete datosInspeccion[key];
            }
            // 🔑 NUEVO: Limpiar datos de convoy 🔑
            reunionPreConvoyGuardada = false;
            datosReunionConvoy = {};
            actualizarBotonReunionConvoy();
        }

        document.getElementById('modalFormulario').addEventListener('click', function(e) {
            if (e.target === this) {
                gestionarModalFormulario(false);
            }
        });

        // ====================================================================
        // Funciones de Gestión del Modal de Inspección
        // ====================================================================
        let unidadEnInspeccion = null;

        function gestionarModalInspeccion(abrir, unidadNumero = null) {
            const modal = document.getElementById('modalInspeccion');
            const formGrid = document.getElementById('inspeccionGrid');

            if (abrir && unidadNumero !== null) {
                unidadEnInspeccion = unidadNumero;

                const selectVehiculo = document.querySelector(`#unidad-${unidadNumero} .unidad-vehiculo`);
                const nombreVehiculo = selectVehiculo ? selectVehiculo.value : 'Unidad ' + unidadNumero;
                document.getElementById('inspeccionUnidadNombre').textContent = nombreVehiculo;
                document.getElementById('inspeccionUnidadIndex').value = unidadNumero;

                formGrid.innerHTML = '';
                itemsInspeccion.forEach(item => {
                    const savedValue = datosInspeccion[unidadNumero] && datosInspeccion[unidadNumero][item.name] ||
                        '';

                    const itemDiv = document.createElement('div');
                    itemDiv.classList.add('inspeccion-item');
                    itemDiv.innerHTML = `
        <div class="inspeccion-item-label">
            <i class="${item.icon}"></i>
            <span>${item.label}</span>
        </div>
        <div class="inspeccion-radio-group">
            <label class="si">
                <input type="radio" name="inspeccion_${item.name}" value="si" ${savedValue === 'si' ? 'checked' : ''} required>
                Sí
            </label>
            <label class="no">
                <input type="radio" name="inspeccion_${item.name}" value="no" ${savedValue === 'no' ? 'checked' : ''}>
                No
            </label>
        </div>
    `;
                    formGrid.appendChild(itemDiv);
                });

                modal.classList.add('active');
                document.body.style.overflow = 'hidden';

            } else {
                modal.classList.remove('active');
                document.body.style.overflow = 'auto';
                unidadEnInspeccion = null;
            }
        }

        function guardarInspeccion() {
            const form = document.getElementById('formInspeccion');
            const unidadNumero = parseInt(document.getElementById('inspeccionUnidadIndex').value);

            let formValido = true;
            itemsInspeccion.forEach(item => {
                const checked = form.querySelector(`input[name="inspeccion_${item.name}"]:checked`);
                if (!checked) {
                    formValido = false;
                }
            });

            if (!formValido) {
                Swal.fire({
                    title: 'Inspección Incompleta',
                    text: 'Debe responder sí o no a todos los elementos de la inspección.',
                    icon: 'warning',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                return;
            }

            const data = {};
            let todoAprobado = true;

            itemsInspeccion.forEach(item => {
                const value = form.querySelector(`input[name="inspeccion_${item.name}"]:checked`).value;
                data[item.name] = value;
                if (value === 'no') {
                    todoAprobado = false;
                }
            });

            datosInspeccion[unidadNumero] = data;

            const btnInspeccion = document.getElementById(`btn-inspeccion-${unidadNumero}`);
            if (btnInspeccion) {
                if (todoAprobado) {
                    btnInspeccion.classList.add('btn-submit', 'btn-inspeccion-aprobado');
                    btnInspeccion.classList.remove('btn-inspeccion');
                    // Ícono de verificación
                    btnInspeccion.innerHTML = '<i class="fas fa-check-circle"></i>';
                    btnInspeccion.dataset.aprobado = 'true';
                    btnInspeccion.title = 'Inspección Aprobada';
                } else {
                    btnInspeccion.classList.add('btn-inspeccion');
                    btnInspeccion.classList.remove('btn-submit', 'btn-inspeccion-aprobado');
                    // Ícono de advertencia para no aprobado
                    btnInspeccion.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
                    btnInspeccion.dataset.aprobado = 'false';
                    btnInspeccion.title = 'Revisar Inspección';
                }
            }

            Swal.fire({
                title: 'Inspección Guardada',
                text: `La inspección para la Unidad ${unidadNumero} ha sido registrada.`,
                icon: 'success',
                confirmButtonColor: 'var(--primary-blue)'
            }).then(() => {
                gestionarModalInspeccion(false);
                // 🔑 NUEVO: Verificar estado del botón Reunión Convoy después de guardar inspección 🔑
                actualizarBotonReunionConvoy();
            });
        }

        // ====================================================================
        // 🔑 NUEVAS FUNCIONES DE GESTIÓN DE REUNIÓN PRE-CONVOY 🔑
        // ====================================================================

        /**
         * 🔑 NUEVA FUNCIÓN CLAVE 🔑: Habilita/deshabilita el botón de Reunión Pre-Convoy.
         * Se activa solo si contadorUnidades >= 2 Y la tabla de unidades está completa.
         */
        function actualizarBotonReunionConvoy() {
            const btn = document.getElementById('btnReunionPreConvoy');
            if (!btn) return;

            // 1. Verificar si hay al menos dos unidades
            if (contadorUnidades < 2) {
                btn.disabled = true;
                btn.classList.remove('btn-submit', 'btn-secondary-convoy-completed');
                btn.classList.add('btn-secondary-convoy');
                btn.title = "Se requiere un mínimo de 2 unidades para un Convoy";
                btn.innerHTML = '<i class="fas fa-handshake"></i> Reunión Pre-convoy';
                return;
            }

            // 2. Verificar si todos los campos requeridos de las unidades están llenos.
            let unidadesCompletas = true;
            for (let i = 1; i <= contadorUnidades; i++) {
                const fila = document.getElementById(`unidad-${i}`);
                if (!fila) {
                    unidadesCompletas = false;
                    break;
                }

                // Lista de selectores de inputs requeridos en la fila de la unidad
                const requiredSelectors = [
                    `.unidad-conductor`,
                    `#vigencia-lic-${i}`,
                    `#vigencia-man-${i}`, // Ahora es readonly, pero contiene datos esenciales
                    `.unidad-alcoholimetria`,
                    `#dormir-${i}`,
                    `#levantar-${i}`,
                    `.unidad-vehiculo`
                ];

                // Checkear inputs principales
                for (const selector of requiredSelectors) {
                    const input = fila.querySelector(selector);
                    if (input && input.value.trim() === "") {
                        unidadesCompletas = false;
                        break;
                    }
                }
                if (!unidadesCompletas) break;

                // Checkear pasajeros (al menos 1 pasajero por unidad es requerido por la plantilla)
                const pasajeros = fila.querySelectorAll('.pasajero-input-group input');
                for (const input of pasajeros) {
                    if (input.value.trim() === "") {
                        unidadesCompletas = false;
                        break;
                    }
                }
                if (!unidadesCompletas) break;
            }

            if (unidadesCompletas) {
                btn.disabled = false;
                btn.title = "Realizar la reunión antes de enviar la solicitud.";

                if (reunionPreConvoyGuardada) {
                    // 🔑 CORRECCIÓN: Cambiar a estilo de botón completado (blanco sin borde, texto e icono verde)
                    btn.classList.add('btn-secondary-convoy-completed');
                    btn.classList.remove('btn-secondary-convoy', 'btn-submit');
                    btn.innerHTML = '<i class="fas fa-check-circle"></i> Reunión Confirmada';
                } else {
                    btn.classList.add('btn-secondary-convoy');
                    btn.classList.remove('btn-submit', 'btn-secondary-convoy-completed');
                    btn.innerHTML = '<i class="fas fa-handshake"></i> Reunión Pre-convoy';
                }
            } else {
                btn.disabled = true;
                btn.classList.remove('btn-submit', 'btn-secondary-convoy-completed');
                btn.classList.add('btn-secondary-convoy');
                btn.innerHTML = '<i class="fas fa-handshake"></i> Reunión Pre-convoy';
                btn.title = "Complete todos los campos de Conductor y Unidad antes de realizar la Reunión.";
                // Si la tabla no está completa, invalidamos la reunión previa
                reunionPreConvoyGuardada = false;
            }

            // También se debe actualizar el botón de Enviar Solicitud
            actualizarBotonEnviarSolicitud();
        }

        /**
         * Habilita/deshabilita el botón de Enviar Solicitud.
         * Se activa solo si el viaje es de Unidad Única O si el viaje es Convoy Y la Reunión ha sido confirmada.
         */
        function actualizarBotonEnviarSolicitud() {
            const btnEnviar = document.getElementById('btnEnviarSolicitud');
            if (!btnEnviar) return;

            if (contadorUnidades <= 1) {
                // Unidad única: siempre disponible si el formulario principal está lleno (se valida en enviarSolicitud)
                btnEnviar.disabled = false;
                btnEnviar.title = "Enviar Solicitud";
            } else {
                // Convoy: Solo disponible si la reunión ha sido guardada
                if (reunionPreConvoyGuardada) {
                    btnEnviar.disabled = false;
                    btnEnviar.title = "Enviar Solicitud";
                } else {
                    btnEnviar.disabled = true;
                    btnEnviar.title = "Debe completar y confirmar la Reunión Pre-convoy.";
                }
            }
        }

        /**
         * Abre o cierra el modal de Reunión Pre-Convoy.
         */
        function gestionarModalPreConvoy(abrir) {
            const modal = document.getElementById('modalPreConvoy');
            const liderSelect = document.getElementById('liderConvoy');
            const checklistContainer = document.getElementById('checklistPreConvoy');

            if (abrir) {
                // 1. Generar la lista de conductores para el selector de Líder
                const conductoresDisponibles = obtenerConductoresUnidades();
                liderSelect.innerHTML = '<option value="">Seleccione un conductor como Líder</option>';

                conductoresDisponibles.forEach(c => {
                    const option = document.createElement('option');
                    option.value = c;
                    option.textContent = c;
                    // Preseleccionar el valor guardado
                    if (datosReunionConvoy.lider_convoy === c) {
                        option.selected = true;
                    }
                    liderSelect.appendChild(option);
                });

                // 2. Generar el Checklist de Seguridad
                checklistContainer.innerHTML = '';
                checklistPreConvoy.forEach(item => {
                    // Valor guardado o cadena vacía si no existe
                    const savedValue = datosReunionConvoy[item.name] || '';

                    const itemDiv = document.createElement('div');
                    itemDiv.classList.add('inspeccion-item');
                    itemDiv.innerHTML = `
                        <div class="inspeccion-item-label">
                            <i class="${item.icon}"></i>
                            <span>${item.label}</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si">
                                <input type="radio" name="checklist_${item.name}" value="si" ${savedValue === 'si' ? 'checked' : ''} required>
                                Sí
                            </label>
                            <label class="no">
                                <input type="radio" name="checklist_${item.name}" value="no" ${savedValue === 'no' ? 'checked' : ''}>
                                No
                            </label>
                        </div>
                    `;
                    checklistContainer.appendChild(itemDiv);
                });

                modal.classList.add('active');
                document.body.style.overflow = 'hidden';

            } else {
                modal.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        }

        /**
         * Obtiene la lista de nombres de conductores actualmente en la tabla.
         */
        function obtenerConductoresUnidades() {
            const conductoresList = [];
            for (let i = 1; i <= contadorUnidades; i++) {
                const inputConductor = document.getElementById(`conductor-${i}`);
                if (inputConductor && inputConductor.value.trim() !== '') {
                    conductoresList.push(inputConductor.value.trim());
                }
            }
            return conductoresList;
        }

        /**
         * Guarda los datos del formulario de Reunión Pre-Convoy.
         */
        function guardarPreConvoy() {
            const form = document.getElementById('formPreConvoy');
            const liderSelect = document.getElementById('liderConvoy');

            // 1. Validar Líder
            if (liderSelect.value.trim() === '') {
                Swal.fire({
                    title: 'Líder No Seleccionado',
                    text: 'Debe seleccionar un conductor como Líder de Convoy.',
                    icon: 'warning',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                return;
            }

            // 2. Validar Checklist
            let checklistCompleto = true;
            const data = {
                lider_convoy: liderSelect.value
            };

            checklistPreConvoy.forEach(item => {
                const checked = form.querySelector(`input[name="checklist_${item.name}"]:checked`);
                if (!checked) {
                    checklistCompleto = false;
                } else {
                    data[item.name] = checked.value;
                }
            });

            if (!checklistCompleto) {
                Swal.fire({
                    title: 'Checklist Incompleto',
                    text: 'Debe responder sí o no a todos los elementos del checklist de seguridad.',
                    icon: 'warning',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                return;
            }

            // 3. Verificar NOs en el Checklist (Advertencia o Rechazo)
            let noAprobado = false;
            let noItems = [];
            for (const key in data) {
                if (key !== 'lider_convoy' && data[key] === 'no') {
                    noAprobado = true;
                    // Buscar el label original
                    const item = checklistPreConvoy.find(i => i.name === key);
                    noItems.push(item ? item.label : key);
                }
            }

            if (noAprobado) {
                Swal.fire({
                    title: '¡Advertencia de Seguridad!',
                    html: `Se encontraron puntos de seguridad no confirmados (NO) en el checklist:<br><ul>${noItems.map(i => `<li>${i}</li>`).join('')}</ul><br>
                           Si continúa, el viaje será marcado con **ALTO RIESGO** y requerirá una aprobación superior.`,
                    icon: 'error', // Usamos error/warning para mayor impacto
                    showCancelButton: true,
                    confirmButtonText: 'Guardar y Continuar (Alto Riesgo)',
                    cancelButtonText: 'Corregir Checklist (Recomendado)',
                    confirmButtonColor: 'var(--accent-red)',
                    cancelButtonColor: 'var(--primary-blue)',
                }).then((result) => {
                    if (result.isConfirmed) {
                        finalizarGuardadoPreConvoy(data, false); // No aprobado = false
                    }
                });
            } else {
                // 4. Guardado exitoso (todo SI)
                finalizarGuardadoPreConvoy(data, true); // Aprobado = true
            }
        }

        function finalizarGuardadoPreConvoy(data, todoAprobado) {
            datosReunionConvoy = data;
            reunionPreConvoyGuardada = true;
            gestionarModalPreConvoy(false);
            actualizarBotonReunionConvoy();

            let title = 'Reunión Confirmada';
            let text = `La Reunión Pre-convoy ha sido registrada. La solicitud está lista para ser enviada.`;
            let icon = 'success';

            if (!todoAprobado) {
                title = 'Reunión Guardada con Advertencias';
                text = `La Reunión Pre-convoy ha sido registrada con **NOs**. El viaje será marcado como **ALTO RIESGO**.`;
                icon = 'warning';
            }

            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                confirmButtonColor: 'var(--primary-blue)'
            });
        }

        // ====================================================================
        // Fin de Funciones de Gestión de Reunión Pre-Convoy
        // ====================================================================


        // ====================================================================
        // Funciones de Pasajeros
        // ====================================================================
        function agregarPasajero(unidadNumero) {
            const container = document.getElementById(`pasajeros-unidad-${unidadNumero}`);
            const currentPasajeros = container.querySelectorAll('.pasajero-input-group').length;

            if (currentPasajeros >= MAX_PASAJEROS) {
                Swal.fire('Límite de Pasajeros',
                    `Solo se permiten ${MAX_PASAJEROS} pasajeros (adicionales al conductor) por unidad.`, 'warning');
                return;
            }

            const nuevoPasajeroIndex = currentPasajeros + 1;
            const passengerFieldName = `unidad[${unidadNumero}][pasajeros][p${nuevoPasajeroIndex}]`;

            const newPasajero = document.createElement('div');
            newPasajero.classList.add('pasajero-input-group');
            newPasajero.setAttribute('data-p-index', nuevoPasajeroIndex);
            newPasajero.innerHTML = `
        <input type="text" class="table-input" name="${passengerFieldName}" placeholder="Pasajero ${nuevoPasajeroIndex} (Nombre completo)" required>
        <button type="button" class="btn-remove-pasajero" onclick="eliminarPasajero(${unidadNumero}, ${nuevoPasajeroIndex})">
            <i class="fas fa-minus"></i>
        </button>
    `;

            container.appendChild(newPasajero);

            if (currentPasajeros + 1 >= MAX_PASAJEROS) {
                document.getElementById(`btn-add-pasajero-${unidadNumero}`).disabled = true;
            }
            // 🔑 NUEVO: Verificar el estado del botón Reunión al agregar un pasajero 🔑
            actualizarBotonReunionConvoy();
        }

        function eliminarPasajero(unidadNumero, pasajeroIndex) {
            const container = document.getElementById(`pasajeros-unidad-${unidadNumero}`);
            const inputGroup = container.querySelector(`.pasajero-input-group[data-p-index="${pasajeroIndex}"]`);

            if (inputGroup) {
                inputGroup.remove();
            }

            const remainingPasajeros = container.querySelectorAll('.pasajero-input-group');
            remainingPasajeros.forEach((group, index) => {
                const newIndex = index + 1;
                group.setAttribute('data-p-index', newIndex);

                const input = group.querySelector('input');
                input.setAttribute('name', `unidad[${unidadNumero}][pasajeros][p${newIndex}]`);
                input.setAttribute('placeholder', `Pasajero ${newIndex} (Nombre completo)`);

                const removeBtn = group.querySelector('.btn-remove-pasajero');
                if (removeBtn) {
                    removeBtn.setAttribute('onclick', `eliminarPasajero(${unidadNumero}, ${newIndex})`);
                }
            });

            if (remainingPasajeros.length < MAX_PASAJEROS) {
                document.getElementById(`btn-add-pasajero-${unidadNumero}`).disabled = false;
            }
            // 🔑 NUEVO: Verificar el estado del botón Reunión al eliminar un pasajero 🔑
            actualizarBotonReunionConvoy();
        }

        // ====================================================================
        // Funciones de Unidades y Auxiliares
        // ====================================================================
        function generarCodigoViaje(incrementar = false) {
            if (incrementar) {
                codigoViaje++;
            }
            // El código siempre es completo: se asegura que tenga 3 dígitos
            const codigo = `N°:GV-${String(codigoViaje).padStart(3, '0')}`;
            const codigoElement = document.getElementById('codigoViaje');
            if (codigoElement) {
                codigoElement.textContent = codigo;
            }
        }

        function actualizarContadorUnidades() {
            document.getElementById('contadorUnidades').textContent = contadorUnidades;
        }

        // 🚀 NUEVA FUNCIÓN SOLICITADA 🚀
        function actualizarLabelTipoUnidad() {
            const labelElement = document.getElementById('label-tipo-unidad');
            if (!labelElement) return;

            if (contadorUnidades === 1) {
                labelElement.textContent = ' (Unidad Única)';
                labelElement.classList.remove('convoy');
            } else if (contadorUnidades > 1) {
                labelElement.textContent = ' (Convoy de Unidades)';
                labelElement.classList.add('convoy');
            } else {
                labelElement.textContent = '';
                labelElement.classList.remove('convoy');
            }
        }

        function actualizarBotonAgregar() {
            const boton = document.getElementById('btnAgregarUnidad');
            if (contadorUnidades >= MAX_UNIDADES) {
                boton.disabled = true;
                boton.textContent = `Límite de ${MAX_UNIDADES} Unidades Alcanzado`;
            } else {
                const restantes = MAX_UNIDADES - contadorUnidades;
                boton.innerHTML =
                    `<i class="fas fa-plus-circle"></i> Agregar Unidad (${restantes} restante${restantes !== 1 ? 's' : ''})`;
            }
        }

        function agregarUnidad() {
            if (contadorUnidades >= MAX_UNIDADES) {
                Swal.fire({
                    title: 'Límite alcanzado',
                    text: `Solo puedes agregar hasta ${MAX_UNIDADES} unidades por solicitud.`,
                    icon: 'warning',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                return;
            }

            contadorUnidades++;
            const numeroUnidad = contadorUnidades;
            const filaId = `unidad-${numeroUnidad}`;

            datosInspeccion[numeroUnidad] = {};

            // 🚀 NUEVA LÓGICA SOLICITADA: Alerta de Convoy 🚀
            if (numeroUnidad === 2) {
                Swal.fire({
                    title: '¡Convoy de Unidades!',
                    html: 'Has agregado una segunda unidad. Tu solicitud ahora será gestionada como un Convoy de Unidades. Asegúrate de que las unidades cumplan con todos los requisitos.',
                    icon: 'info',
                    confirmButtonColor: 'var(--primary-blue)',
                    customClass: {
                        popup: 'swal2-convoy' // Clase CSS personalizada
                    }
                });
            }
            // 🚀 FIN NUEVA LÓGICA SOLICITADA 🚀


            const nuevaFila = document.createElement('tr');
            nuevaFila.id = filaId;
            nuevaFila.innerHTML = `
        <td>
            <div class="conductor-completo-group">
                <div class="hour-input-group" style="align-items: center;">
                    <label><i class="fas fa-user-circle"></i> Nombre Completo</label>
                    <div class="conductor-input-group">
                        <input type="text" class="table-input large unidad-conductor" id="conductor-${numeroUnidad}" name="unidad[${numeroUnidad}][conductor]" placeholder="Escriba nombre y seleccione" required autocomplete="off">
                        <div class="autocomplete-list" id="autocomplete-list-${numeroUnidad}"></div>
                    </div>
                </div>

                <div class="hour-input-group" style="display: none;">
                        <input type="hidden" id="licencia-num-${numeroUnidad}" name="unidad[${numeroUnidad}][licencia_num]">
                </div>

                <div class="hour-input-group" style="display: none;">
                        <input type="hidden" id="vigencia-lic-hidden-${numeroUnidad}" name="unidad[${numeroUnidad}][vigencia_lic_hidden]">
                </div>


                <div class="hour-input-group">
                    <label><i class="fas fa-wind"></i> ¿Realizó Alcoholimetría?</label>
                    <select class="table-input small unidad-alcoholimetria" name="unidad[${numeroUnidad}][alcoholimetria]" required onchange="actualizarBotonReunionConvoy()">
                        <option value="">Seleccionar</option>
                        <option value="si">Sí</option>
                        <option value="no">No</option>
                    </select>
                </div>
            </div>
        </td>

        <td>
            <div class="hour-input-group">
                <label><i class="fas fa-id-card"></i> Vigencia Licencia</label>
                <input type="text" class="table-input" id="vigencia-lic-${numeroUnidad}" name="unidad[${numeroUnidad}][vigencia_lic]" placeholder="202X-XX-XX" required readonly>
            </div>
            <div class="hour-input-group" style="margin-top: 5px;">
                <label><i class="fas fa-calendar-alt"></i> Vigencia Man. Def.</label>
                <input type="text" class="table-input" id="vigencia-man-${numeroUnidad}" name="unidad[${numeroUnidad}][vigencia_man]" placeholder="202X-XX-XX" required readonly>
            </div>
        </td>

        <td>
            <div class="hour-input-group">
                <label><i class="fas fa-bed"></i> Hr que Durmió</label>
                <input type="text" class="table-input small unidad-hora-dormir" id="dormir-${numeroUnidad}" name="unidad[${numeroUnidad}][hora_dormir]" placeholder="HH:MM AM/PM" required>
            </div>
            <div class="hour-input-group">
                <label><i class="fas fa-sun"></i> Hr que Despertó</label>
                <input type="text" class="table-input small unidad-hora-levantar" id="levantar-${numeroUnidad}" name="unidad[${numeroUnidad}][hora_levantar]" placeholder="HH:MM AM/PM" required>
            </div>
            <div class="hour-input-group" style="margin-top: 5px;">
                <label style="font-weight: 700; color: var(--primary-blue);"><i class="fas fa-hourglass-half"></i> Hrs Dormidas</label>
                <input type="text" class="table-input small unidad-total-dormidas hour-input-result" id="total-hrs-dormidas-${numeroUnidad}" name="unidad[${numeroUnidad}][total_dormidas]" placeholder="0.0" readonly>
            </div>
        </td>

        <td class="td-horas-conduccion">
            <div class="hour-inputs-group-combined-vertical">
                <div class="hour-input-group">
                    <label><i class="fas fa-bed"></i> Hr Despierto</label>
                    <input type="text" class="table-input small unidad-horas-despierto" id="horas-despierto-${numeroUnidad}" name="unidad[${numeroUnidad}][horas_despierto]" placeholder="0.0" required onchange="calcularTotalHoras(${numeroUnidad}); actualizarBotonReunionConvoy()">
                </div>
                <div class="hour-input-group">
                    <label><i class="fas fa-route"></i> Duración Viaje</label>
                    <input type="text" class="table-input small unidad-horas-viaje" id="horas-viaje-${numeroUnidad}" name="unidad[${numeroUnidad}][horas_viaje]" placeholder="0.0" required onchange="calcularTotalHoras(${numeroUnidad}); actualizarBotonReunionConvoy()">
                </div>
                <div class="hour-input-group" style="margin-top: 5px;">
                    <label style="font-weight: 700; color: var(--primary-blue);"><i class="fas fa-clock"></i> Total Hrs</label>
                    <input type="text" class="table-input small unidad-total-finalizar hour-input-result" id="total-hrs-finalizar-${numeroUnidad}" name="unidad[${numeroUnidad}][total_finalizar]" placeholder="0.0" readonly>
                </div>
            </div>
        </td>

        <td class="td-pasajeros">
            <div id="pasajeros-unidad-${numeroUnidad}" class="pasajero-container">
            </div>
            <button type="button" class="btn-add-pasajero" id="btn-add-pasajero-${numeroUnidad}" onclick="agregarPasajero(${numeroUnidad})" title="Agregar pasajero">
                <i class="fas fa-user-plus"></i>
            </button>
        </td>

        <td>
            <select class="table-input large unidad-vehiculo" name="unidad[${numeroUnidad}][vehiculo]" required onchange="actualizarNombreVehiculoInspeccion(${numeroUnidad}); actualizarBotonReunionConvoy()">
                <option value="">Seleccionar Vehículo</option>
                ${vehiculos.map(v => `<option value="${v}">${v}</option>`).join('')}
            </select>
        </td>

        <td>
            <button type="button" class="btn-viajes btn-inspeccion" id="btn-inspeccion-${numeroUnidad}" data-aprobado="false" onclick="gestionarModalInspeccion(true, ${numeroUnidad})" title="Realizar Inspección Pre-Viaje">
                <i class="fas fa-clipboard"></i>
            </button>
        </td>

        <td class="acciones-td">
            <button type="button" class="btn-accion eliminar" onclick="eliminarUnidad(${numeroUnidad})" title="Eliminar unidad">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;

            document.getElementById('cuerpoTablaUnidades').appendChild(nuevaFila);
            actualizarContadorUnidades();
            actualizarLabelTipoUnidad(); // Actualizar label
            actualizarBotonAgregar();
            inicializarAutocompleteConductor(numeroUnidad);

            // Inicializar Flatpickr con la nueva configuración visual de hora (AM/PM)
            flatpickr(nuevaFila.querySelector('.unidad-hora-dormir'), configHoraVisual);
            flatpickr(nuevaFila.querySelector('.unidad-hora-levantar'), configHoraVisual);

            // Re-agregar listeners de cambio para cálculo de Horas Sueño (sigue siendo automático)
            nuevaFila.querySelector('.unidad-hora-dormir').addEventListener('change', function() {
                calcularHorasDormidas(numeroUnidad);
                // 🔑 NUEVO: Verificar el estado del botón Reunión al cambiar horas 🔑
                actualizarBotonReunionConvoy();
            });

            nuevaFila.querySelector('.unidad-hora-levantar').addEventListener('change', function() {
                calcularHorasDormidas(numeroUnidad);
                // 🔑 NUEVO: Verificar el estado del botón Reunión al cambiar horas 🔑
                actualizarBotonReunionConvoy();
            });

            // 🔑 NUEVO: Listener de cambio para Alcoholimetría 🔑
            nuevaFila.querySelector('.unidad-alcoholimetria').addEventListener('change', function() {
                actualizarBotonReunionConvoy();
            });


            // Los campos de Horas de Conducción son de texto, el cálculo debe hacerse manualmente o con una función que parsee.
            // Los listeners se re-asignan para fines de ejemplo de cálculo, aunque el input sea de texto.
            nuevaFila.querySelector(`#horas-despierto-${numeroUnidad}`).addEventListener('change', function() {
                calcularTotalHoras(numeroUnidad);
            });
            nuevaFila.querySelector(`#horas-viaje-${numeroUnidad}`).addEventListener('change', function() {
                calcularTotalHoras(numeroUnidad);
            });

            // Agrega el primer pasajero
            agregarPasajero(numeroUnidad);

            // 🔑 NUEVO: Actualizar el estado del botón de Reunión 🔑
            actualizarBotonReunionConvoy();
        }

        function eliminarUnidad(numero) {
            Swal.fire({
                title: '¿Eliminar unidad?',
                text: 'Se eliminarán todos los datos de esta unidad y sus pasajeros.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: 'var(--accent-red)',
                cancelButtonColor: 'var(--primary-blue)',
            }).then((result) => {
                if (result.isConfirmed) {
                    const fila = document.getElementById(`unidad-${numero}`);
                    fila.remove();

                    delete datosInspeccion[numero];

                    contadorUnidades--;
                    actualizarContadorUnidades();
                    actualizarLabelTipoUnidad(); // Actualizar label
                    actualizarBotonAgregar();
                    reordenarNumerosUnidades();
                    // 🔑 NUEVO: Actualizar el estado del botón de Reunión 🔑
                    actualizarBotonReunionConvoy();
                }
            });
        }

        function actualizarNombreVehiculoInspeccion(unidadNumero) {
            if (unidadEnInspeccion === unidadNumero) {
                const selectVehiculo = document.querySelector(`#unidad-${unidadNumero} .unidad-vehiculo`);
                const nombreVehiculo = selectVehiculo ? selectVehiculo.value : 'Unidad ' + unidadNumero;
                document.getElementById('inspeccionUnidadNombre').textContent = nombreVehiculo;
            }
        }

        function reordenarNumerosUnidades() {
            const filas = document.querySelectorAll('#cuerpoTablaUnidades tr');
            contadorUnidades = 0;

            const nuevosDatosInspeccion = {};

            filas.forEach((fila, index) => {
                const numeroAnterior = parseInt(fila.id.split('-')[1]);
                const nuevoNumero = index + 1;
                contadorUnidades = nuevoNumero;
                const filaId = `unidad-${nuevoNumero}`;
                fila.id = filaId;

                if (datosInspeccion[numeroAnterior]) {
                    nuevosDatosInspeccion[nuevoNumero] = datosInspeccion[numeroAnterior];
                    delete datosInspeccion[numeroAnterior];
                }

                // Actualizar IDs para campos de Vigencia
                const oldLicenciaNum = fila.querySelector(`#licencia-num-${numeroAnterior}`);
                const oldVigenciaLic = fila.querySelector(`#vigencia-lic-${numeroAnterior}`);
                const oldVigenciaMan = fila.querySelector(`#vigencia-man-${numeroAnterior}`);

                if (oldLicenciaNum) oldLicenciaNum.id = `licencia-num-${nuevoNumero}`;
                if (oldVigenciaLic) oldVigenciaLic.id = `vigencia-lic-${nuevoNumero}`;
                if (oldVigenciaMan) oldVigenciaMan.id = `vigencia-man-${nuevoNumero}`;

                fila.querySelector('.unidad-conductor').id = `conductor-${nuevoNumero}`;
                fila.querySelector('#autocomplete-list-' + numeroAnterior).id = `autocomplete-list-${nuevoNumero}`;

                // Reinicializar autocompletado es necesario, ya que los IDs cambiaron
                inicializarAutocompleteConductor(nuevoNumero);

                const btnEliminar = fila.querySelector('.btn-accion.eliminar');
                if (btnEliminar) {
                    btnEliminar.setAttribute('onclick', `eliminarUnidad(${nuevoNumero})`);
                }

                const btnInspeccion = fila.querySelector('.btn-inspeccion, .btn-submit, .btn-inspeccion-aprobado');
                if (btnInspeccion) {
                    btnInspeccion.id = `btn-inspeccion-${nuevoNumero}`;
                    btnInspeccion.setAttribute('onclick', `gestionarModalInspeccion(true, ${nuevoNumero})`);
                    if (btnInspeccion.dataset.aprobado === 'true') {
                        btnInspeccion.innerHTML = '<i class="fas fa-check-circle"></i>';
                    } else {
                        btnInspeccion.innerHTML = '<i class="fas fa-clipboard"></i>';
                    }
                }

                // Actualizar IDs de Horas
                const oldDormir = document.getElementById(`dormir-${numeroAnterior}`);
                const oldLevantar = document.getElementById(`levantar-${numeroAnterior}`);
                const oldTotalDormidas = document.getElementById(`total-hrs-dormidas-${numeroAnterior}`);
                const oldHrsDespierto = document.getElementById(`horas-despierto-${numeroAnterior}`);
                const oldHrsViaje = document.getElementById(`horas-viaje-${numeroAnterior}`);
                const oldTotalFinalizar = document.getElementById(`total-hrs-finalizar-${numeroAnterior}`);

                if (oldDormir) oldDormir.id = `dormir-${nuevoNumero}`;
                if (oldLevantar) oldLevantar.id = `levantar-${nuevoNumero}`;
                if (oldTotalDormidas) oldTotalDormidas.id = `total-hrs-dormidas-${nuevoNumero}`;
                if (oldHrsDespierto) oldHrsDespierto.id = `horas-despierto-${nuevoNumero}`;
                if (oldHrsViaje) oldHrsViaje.id = `horas-viaje-${nuevoNumero}`;
                if (oldTotalFinalizar) oldTotalFinalizar.id = `total-hrs-finalizar-${nuevoNumero}`;

                // Re-agregar listeners de cambio para cálculo de horas (Horas Sueño)
                fila.querySelector('.unidad-hora-dormir').addEventListener('change', function() {
                    calcularHorasDormidas(nuevoNumero);
                    actualizarBotonReunionConvoy();
                });

                fila.querySelector('.unidad-hora-levantar').addEventListener('change', function() {
                    calcularHorasDormidas(nuevoNumero);
                    actualizarBotonReunionConvoy();
                });

                // Re-agregar listener de cambio para Alcoholimetría
                fila.querySelector('.unidad-alcoholimetria').addEventListener('change', function() {
                    actualizarBotonReunionConvoy();
                });

                // Re-asignar onchange para Horas de Conducción
                fila.querySelector(`#horas-despierto-${nuevoNumero}`).addEventListener('change', function() {
                    calcularTotalHoras(nuevoNumero);
                    actualizarBotonReunionConvoy();
                });
                fila.querySelector(`#horas-viaje-${nuevoNumero}`).addEventListener('change', function() {
                    calcularTotalHoras(nuevoNumero);
                    actualizarBotonReunionConvoy();
                });


                const pasajeroContainer = fila.querySelector('.pasajero-container');
                if (pasajeroContainer) {
                    pasajeroContainer.id = `pasajeros-unidad-${nuevoNumero}`;
                    const btnAddPasajero = fila.querySelector('.btn-add-pasajero');
                    btnAddPasajero.id = `btn-add-pasajero-${nuevoNumero}`;
                    btnAddPasajero.setAttribute('onclick', `agregarPasajero(${nuevoNumero})`);

                    pasajeroContainer.querySelectorAll('.pasajero-input-group').forEach((group, pIndex) => {
                        const newIndex = pIndex + 1;
                        const input = group.querySelector('input');
                        const removeBtn = group.querySelector('.btn-remove-pasajero');

                        group.setAttribute('data-p-index', newIndex);
                        input.setAttribute('name', `unidad[${nuevoNumero}][pasajeros][p${newIndex}]`);
                        if (removeBtn) {
                            removeBtn.setAttribute('onclick',
                                `eliminarPasajero(${nuevoNumero}, ${newIndex})`);
                        }
                    });
                }

                // Actualizar atributos `name`
                const inputs = fila.querySelectorAll('input, select');
                inputs.forEach(input => {
                    const name = input.getAttribute('name');
                    if (name && name.startsWith('unidad')) {
                        const newName = name.replace(/unidad\[\d+\]/, `unidad[${nuevoNumero}]`);
                        input.setAttribute('name', newName);
                    }
                });
            });

            Object.assign(datosInspeccion, nuevosDatosInspeccion);
            actualizarContadorUnidades();
            actualizarLabelTipoUnidad(); // Actualizar label
            actualizarBotonAgregar();
            actualizarBotonReunionConvoy();
        }

        function enviarSolicitud() {
            const form = document.getElementById('formViaje');

            // Valida campos del formulario principal
            if (!form.checkValidity()) {
                form.reportValidity();
                Swal.fire({
                    title: 'Faltan Campos',
                    text: 'Por favor, llene todos los campos requeridos en la sección de Información General y Detalles del Trayecto.',
                    icon: 'warning',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                return;
            }

            // Validar Destino si es "Otro" y/o si el campo específico está vacío
            const destinoSelector = document.getElementById('destinoPredefinido');
            const destinoEspecifico = document.getElementById('destinoEspecifico');

            // Si el selector es "Otro" O si el selector está vacío Y el campo específico también, entonces hay error.
            if (destinoSelector.value === '' || (destinoSelector.value === 'Otro' && destinoEspecifico.value.trim() ===
                    '')) {
                Swal.fire({
                    title: 'Destino no especificado',
                    text: 'Por favor, seleccione un destino de la lista o especifique uno en el campo de texto.',
                    icon: 'warning',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                // Enfocar el campo correspondiente
                if (destinoSelector.value === '') {
                    destinoSelector.focus();
                } else {
                    destinoEspecifico.focus();
                }
                return;
            }


            if (contadorUnidades === 0) {
                Swal.fire({
                    title: 'Sin unidades',
                    text: 'Debe agregar al menos una unidad vehicular y su conductor.',
                    icon: 'warning',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                return;
            }

            let unidadesCompletas = true;
            let inspeccionesAprobadas = true;
            let unidadConProblema = 0;

            for (let i = 1; i <= contadorUnidades; i++) {
                const btnInspeccion = document.getElementById(`btn-inspeccion-${i}`);
                if (!btnInspeccion || btnInspeccion.dataset.aprobado !== 'true') {
                    inspeccionesAprobadas = false;
                    unidadConProblema = i;
                }

                const fila = document.getElementById(`unidad-${i}`);
                if (fila) {
                    // Validar campos requeridos de la fila de la unidad
                    fila.querySelectorAll('input[required], select[required]').forEach(input => {
                        // Verifica campos vacíos
                        if (input.value.trim() === "") {
                            unidadesCompletas = false;
                            unidadConProblema = i;
                        }
                        // Revisa el campo de Vigencia Licencia (si está vacío, no se seleccionó conductor)
                        if (input.id.startsWith('vigencia-lic-') && input.value.trim() === "") {
                            unidadesCompletas = false;
                            unidadConProblema = i;
                        }
                    });
                }

                // Validar campos de pasajeros requeridos (que no estén vacíos)
                const pasajeroContainer = document.getElementById(`pasajeros-unidad-${i}`);
                if (pasajeroContainer) {
                    pasajeroContainer.querySelectorAll('input[required]').forEach(input => {
                        if (input.value.trim() === "") {
                            unidadesCompletas = false;
                            unidadConProblema = i;
                        }
                    });
                }

                if (!inspeccionesAprobadas || !unidadesCompletas) {
                    break;
                }
            }

            if (!unidadesCompletas) {
                Swal.fire({
                    title: 'Información de Unidades Incompleta',
                    html: `Asegúrese de **seleccionar un conductor de la lista** y llenar todos los campos requeridos de la Unidad **#${unidadConProblema}**, incluyendo la **Vigencia Man. Def.** y los **pasajeros** con nombre completo.`,
                    icon: 'warning',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                return;
            }

            if (!inspeccionesAprobadas) {
                Swal.fire({
                    title: 'Inspección Pendiente/Incompleta',
                    text: `La Unidad #${unidadConProblema} debe tener la inspección vehicular realizada y **aprobada** antes de enviar la solicitud.`,
                    icon: 'error',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                return;
            }

            // 🔑 NUEVO: Validar Reunión Pre-Convoy si hay más de una unidad 🔑
            if (contadorUnidades > 1 && !reunionPreConvoyGuardada) {
                Swal.fire({
                    title: 'Reunión Pre-Convoy Requerida',
                    text: 'Al ser un Convoy, debe realizar y confirmar la Reunión Pre-convoy antes de enviar la solicitud.',
                    icon: 'error',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                return;
            }


            Swal.fire({
                title: '¿Enviar solicitud?',
                text: 'La solicitud será enviada para su aprobación.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Revisar',
                confirmButtonColor: 'var(--primary-blue)',
                cancelButtonColor: 'var(--medium-gray)',
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: '¡Solicitud enviada!',
                        html: `<p>Tu solicitud <strong>${document.getElementById('codigoViaje').textContent}</strong> ha sido enviada exitosamente. Será revisada por el área de control.</p>`,
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: 'var(--primary-blue)'
                    }).then(() => {
                        generarCodigoViaje(true);
                        document.getElementById('modalFormulario').classList.remove('active');
                        document.body.style.overflow = 'auto';
                        limpiarFormulario();
                    });
                }
            });
        }
    </script>
    @stack('scripts')
</body>

</html>
