@extends('modules.qhse.management.index')

@section('content')
    <div class="dashboard-container">
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
                            <span class="stat-number" id="active-count">0</span>
                            <span class="stat-label">En Curso</span>
                        </div>
                    </div>

                    <div class="stat-card stat-pending">
                        <div class="stat-icon stat-pending-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-number" id="pending-count">0</span>
                            <span class="stat-label">Pendientes</span>
                        </div>
                    </div>

                    <div class="stat-card stat-completed">
                        <div class="stat-icon stat-completed-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-number" id="completed-count">0</span>
                            <span class="stat-label">Completados</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="filters-section">
            <div class="filters-grid">
                <div class="filter-group filter-search" style="grid-column: span 2;">
                    <label for="searchViajes">
                        <i class="fas fa-search"></i> Búsqueda Rápida
                    </label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-search input-icon"></i>
                        <input type="text" id="searchViajes" class="form-control input-with-icon"
                            placeholder="Buscar por folio, solicitante, destino...">
                    </div>
                </div>

                <div class="filter-group">
                    <label for="solicitud-date">
                        <i class="fas fa-calendar-day"></i> Fecha Solicitud
                    </label>
                    <input type="text" id="solicitud-date" class="form-control" placeholder="DD/MM/AAAA">
                </div>

                <div class="filter-group">
                    <label>
                        <i class="fas fa-map-marker-alt"></i> Destino
                    </label>
                    <div class="custom-select-wrapper" id="wrapperDestinoFiltro">
                        <div class="custom-select-trigger form-control" onclick="toggleMenuDestinoFiltro()">
                            <span id="labelDestinoFiltroSeleccionado" class="text-truncate">Todos los Destinos</span>
                            <i class="fas fa-chevron-down select-arrow"></i>
                        </div>
                        <div class="custom-options shadow-sm" id="listaOpcionesDestinoFiltro">
                            <div class="loading-options">Cargando destinos...</div>
                        </div>
                        <input type="hidden" id="inputDestinoFiltroHidden" value="all">
                    </div>
                </div>

                <div class="filter-group">
                    <label for="status-gv-filter">
                        <i class="fas fa-clipboard-check"></i> Estatus GV
                    </label>
                    <select id="status-gv-filter" class="form-control custom-native-select">
                        <option value="all">Todos</option>
                        <option value="pending">Pendiente</option>
                        <option value="approved">Aprobado</option>
                        <option value="rejected">Rechazado</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="status-viaje-filter">
                        <i class="fas fa-truck-moving"></i> Estatus Viaje
                    </label>
                    <select id="status-viaje-filter" class="form-control custom-native-select">
                        <option value="all">Todos</option>
                        <option value="not_started">Por Iniciar</option>
                        <option value="in_progress">En Curso</option>
                        <option value="completed">Finalizado</option>
                        <option value="cancelled">Cancelado</option>
                        <option value="no_procede">No Procede</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="riesgo-filter">
                        <i class="fas fa-exclamation-triangle"></i> Nivel de Riesgo
                    </label>
                    <select id="riesgo-filter" class="form-control custom-native-select">
                        <option value="all">Todos</option>
                        <option value="bajo">Bajo</option>
                        <option value="medio">Medio</option>
                        <option value="alto">Alto</option>
                        <option value="muy_alto">Muy Alto</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button class="btn-icon btn-clear-filters" id="clear-filters" title="Limpiar Filtros"
                        aria-label="Limpiar Filtros">
                        <i class="fas fa-eraser"></i>
                    </button>
                    <button class="btn-icon btn-refresh" onclick="cargarViajes()" title="Actualizar Datos"
                        aria-label="Actualizar Datos">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="table-header">
            <h3>
                <i class="fas fa-history"></i> Viajes Recientes
            </h3>
            <div class="header-actions">
                <button type="button" class="btn-viajes btn-primary" onclick="gestionarModalFormulario(true)">
                    <i class="fas fa-plus-circle"></i> Nueva Solicitud
                </button>
            </div>
        </div>

        <div class="table-responsive-wrapper">
            <div id="tablaViajesContainer"></div>
        </div>

        <div id="paginationContainer" class="pagination-wrapper"></div>
    </div>

    <div id="loaderTemplate" style="display: none;">
        <div style="text-align: center; padding: 60px 20px;">
            <div class="custom-loader" style="width: 50px; height: 50px; margin: 0 auto 20px;">
                <svg viewBox="0 0 50 50" style="animation: rotate 2s linear infinite;">
                    <circle cx="25" cy="25" r="20" fill="none" stroke="#e0e7ff" stroke-width="4" />
                    <circle cx="25" cy="25" r="20" fill="none" stroke="#1e3a8a" stroke-width="4"
                        stroke-linecap="round"
                        style="stroke-dasharray: 125; stroke-dashoffset: 125; animation: dash 1.5s ease-in-out infinite;" />
                </svg>
            </div>
            <p style="color: #0c1d4ddf; font-weight: 500;">Cargando viajes...</p>
        </div>
    </div>

    <div id="emptyTemplate" style="display: none;">
        <div style="text-align: center; padding: 60px 20px;">
            <i class="fas fa-road" style="font-size: 60px; color: #cbd5e1; margin-bottom: 20px;"></i>
            <h3 style="color: #334155;">No hay viajes registrados</h3>
            <p style="color: #64748b; margin-bottom: 25px;">No se encontraron resultados para tu búsqueda.</p>
            <button class="btn-viajes btn-primary" onclick="gestionarModalFormulario(true)">
                <i class="fas fa-plus-circle"></i> Nueva Solicitud
            </button>
        </div>
    </div>

    <div id="errorTemplate" style="display: none;">
        <div style="text-align: center; padding: 60px 20px;">
            <i class="fas fa-exclamation-circle" style="font-size: 60px; color: #dc3545; margin-bottom: 20px;"></i>
            <h3 style="color: #334155;">Error al cargar los datos</h3>
            <p id="errorMensaje" style="color: #64748b; margin-bottom: 25px;"></p>
            <button class="btn-viajes btn-primary" onclick="cargarViajes()">
                <i class="fas fa-sync-alt"></i> Reintentar
            </button>
        </div>
    </div>
@endsection

@section('modals')
    <div id="modalSeguimientoRuta" class="modal-overlay">
        <div class="modal-content" style="max-width: 700px;">
            <div class="form-header-modal">
                <div class="form-header-info">
                    <div class="form-header-title">
                        <h2>Bitácora de Ruta <span id="lblRutaViaje" class="form-code-value">GV-000</span></h2>
                        <p>Notificación de paradas, relevos e incidencias operativas</p>
                    </div>
                </div>
                <button type="button" class="btn-camara-close" onclick="cerrarModalRuta()"
                    style="position: static; width: 30px; height: 30px; background: rgba(255,255,255,0.2);">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="form-body" style="padding: 20px;">
                <div class="ruta-grid">
                    <div class="form-section" style="margin-bottom: 0;">

                        <button type="button" class="btn-ruta-action btn-main-viaje" id="btnMainViaje"
                            onclick="toggleEstadoViaje()">
                            <i class="fas fa-play"></i> Iniciar Viaje
                        </button>

                        <div class="status-panel">
                            <div class="status-indicator">
                                <span class="status-dot-ruta pulsing-green" id="dotEstado"></span>
                                <div class="status-texts">
                                    <span class="status-label">ESTADO ACTUAL DEL VIAJE</span>
                                    <strong class="status-value text-green" id="txtEstadoActual">Por Iniciar</strong>
                                </div>
                            </div>
                        </div>

                        <div class="quick-actions-box">
                            <h4 class="box-subtitle"><i class="fas fa-exclamation-triangle"></i> Incidencias en Ruta</h4>

                            <button type="button" class="btn-ruta-action btn-stop" id="btnDetenerMarcha"
                                onclick="abrirFormularioDetencion()">
                                <i class="fas fa-hand-paper"></i> Notificar Detención / Retraso
                            </button>

                            <div id="formDetencion"
                                style="display: none; margin-top: 10px; padding: 15px; background: #ffebee; border-radius: 6px; border: 1px dashed #ef5350;">
                                <label style="font-size: 11px; font-weight: 600; color: #c62828;">Seleccione el motivo de
                                    la detención:</label>
                                <select id="motivoDetencionSelect" class="form-control"
                                    style="margin-bottom: 10px; font-size: 13px;">
                                    <option value="" disabled selected>-- Elija una opción --</option>
                                    <option value="Tráfico Pesado / Embotellamiento">Tráfico Pesado / Embotellamiento
                                    </option>
                                    <option value="Carretera Cerrada / Manifestación">Carretera Cerrada / Manifestación
                                    </option>
                                    <option value="Retén / Inspección de Autoridad">Retén / Inspección de Autoridad
                                    </option>
                                    <option value="Accidente en la Vía">Accidente en la Vía</option>
                                    <option value="Falla Mecánica / Ponchadura">Falla Mecánica / Ponchadura</option>
                                    <option value="Condiciones Climáticas Adversas">Condiciones Climáticas Adversas
                                    </option>
                                    <option value="Otro">Otro (Especificar en notas)</option>
                                </select>
                                <input type="text" id="notasDetencion" class="form-control form-control-sm"
                                    placeholder="Notas adicionales (Opcional)" style="margin-bottom: 10px;">
                                <div style="display: flex; gap: 5px;">
                                    <button type="button" class="btn-action-small btn-edit"
                                        style="width: 100%; justify-content: center; background: #d32f2f;"
                                        onclick="confirmarDetencion()">Confirmar Detención</button>
                                    <button type="button" class="btn-action-small btn-cancel"
                                        onclick="cancelarDetencion()"><i class="fas fa-times"></i></button>
                                </div>
                            </div>

                            <button type="button" class="btn-ruta-action btn-resume" id="btnReanudarMarcha"
                                style="display: none;" onclick="confirmarReanudacion()">
                                <i class="fas fa-play"></i> Reanudando Ruta
                            </button>
                        </div>

                        <hr class="section-divider">

                        <div class="form-section-title" style="border-bottom: none; margin-bottom: 10px;">
                            <h3 style="font-size: 14px;"><i class="fas fa-map-marked-alt"></i> Paradas Programadas</h3>
                        </div>
                        <div class="paradas-list" id="contenedorParadasProgramadas"></div>

                        <div id="seccionRelevoRuta" style="display: none;">
                            <div class="form-section-title"
                                style="border-bottom: none; margin-bottom: 10px; margin-top: 10px;">
                                <h3 style="font-size: 14px;"><i class="fas fa-users"></i> Relevo de Conductor</h3>
                            </div>
                            <div id="contenedorRelevoConductor"></div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modalHistorialActividad" class="modal-overlay">
        <div class="modal-content" style="max-width: 650px;">
            <div class="form-header-modal header-history">
                <div class="form-header-info">
                    <div class="form-header-title">
                        <h2>Historial de Actividad <span id="lblHistorialViaje" class="form-code-value">GV-000</span></h2>
                        <p>Registro completo de eventos y auditoría del viaje</p>
                    </div>
                </div>
                <button type="button" class="btn-camara-close" onclick="cerrarModalHistorial()"
                    style="position: static; width: 30px; height: 30px; background: rgba(255,255,255,0.2);">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="form-body" style="padding: 20px;">
                <div class="form-section" style="background: #f8fafc; margin-bottom: 0;">

                    <div class="timeline-container" id="timelineHistorialGlobal">
                        <div class="timeline-item">
                            <div class="timeline-icon" style="background-color: #2e7d32;"><i class="fas fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h4 style="color: #2e7d32;">Solicitud Aprobada</h4>
                                <span class="timeline-time"><i class="far fa-clock"></i> Ayer, 10:00 AM</span>
                                <p>El viaje fue autorizado y está listo (Por Iniciar).</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PRINCIPAL DEL FORMULARIO --}}
    <div class="modal-overlay" id="modalFormulario">
        <div class="modal-content">
            <div class="form-header-modal">
                <div class="form-header-info">
                    <div class="logo-img">
                        <img src="{{ asset('assets/img/logovinco1.png') }}" alt="Vinco Energy">
                    </div>
                    <div class="form-header-title">
                        <h2>Solicitud de Gerenciamiento</h2>
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
                        <span class="form-code-value" id="codigoViaje">GV-00000</span>
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
                                Conductor (Creador de GV) <span class="required">*</span>
                            </label>
                            <input type="text" name="solicitante" id="solicitante"
                                value="{{ $userData['nombre'] ?? '' }}" readonly required>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-building"></i>
                                Departamento <span class="required">*</span>
                            </label>
                            <input type="text" name="departamento" id="departamento"
                                value="{{ $userData['departamento'] ?? '' }}" readonly required>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-map-marker-alt"></i> Destino del Viaje <span class="required">*</span>
                            </label>

                            <div class="custom-select-wrapper" id="wrapperDestino">

                                <div class="custom-select-trigger" onclick="toggleMenuDestino()">
                                    <span id="labelDestinoSeleccionado">Seleccione un destino...</span>
                                    <i class="fas fa-chevron-down" style="font-size: 0.8em; color: #6c757d;"></i>
                                </div>

                                <div class="custom-options" id="listaOpcionesDestino">
                                    <div style="padding:10px; color: #6c757d;">Cargando...</div>
                                </div>

                                <input type="hidden" name="destino_predefinido" id="inputDestinoHidden" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-pencil-alt"></i>
                                Especifique Destino
                            </label>
                            <input type="text" name="destino_especifico" id="destinoEspecifico"
                                placeholder="Especifique el destino aquí" required>
                        </div>

                        <div class="form-group">
                            <label style="font-size: 13px; color: var(--blue-dark);">
                                <i class="fas fa-map-signs"></i>
                                Paradas?
                            </label>
                            <div class="radio-group-paradas">
                                <label class="radio-option">
                                    <input type="radio" name="tiene_paradas" value="no" checked
                                        onclick="toggleSeccionParadas(false)">
                                    No
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="tiene_paradas" value="si"
                                        onclick="toggleSeccionParadas(true)">
                                    Sí
                                </label>
                            </div>
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
                                <i class="fas fa-clock"></i>
                                Hora de Inicio <span class="required">*</span>
                            </label>
                            <div class="datetime-input-wrapper">
                                <input type="text" name="hora_inicio" id="horaInicioViaje" placeholder="HH:MM"
                                    required>
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>
                                <i class="fas fa-clock"></i>
                                Hora de Término
                            </label>
                            <div class="datetime-input-wrapper">
                                <input type="text" name="hora_fin" id="horaFinViaje" placeholder="HH:MM">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>


                        <div class="form-group">
                            <label>
                                <i class="fas fa-calendar-check"></i>
                                Fecha de Término
                            </label>
                            <div class="datetime-input-wrapper">
                                <input type="text" name="fecha_fin" id="fechaFinViaje" placeholder="Seleccione fecha"
                                    required>
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>
                                <i class="fas fa-map-marker"></i>
                                Llegando a
                            </label>
                            <input type="text" name="llegada" placeholder="Ej: Villahermosa, Tabasco" required>
                        </div>



                    </div>

                    <div id="contenedorParadas" class="hidden">
                        <div id="listaParadas">
                        </div>
                        <button type="button" class="btn-add-parada" id="btnAgregarParada" onclick="agregarParada()">
                            <i class="fas fa-plus"></i> Agregar Otra Parada
                        </button>
                    </div>
                </div>
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-truck-moving"></i>
                        Conductor y Unidad Solicitada
                        <span class="unidades-count" id="contadorUnidades">0</span>
                        <span class="unidades-label" id="label-tipo-unidad"></span>
                        <button type="button" class="btn-viajes btn-secondary-convoy" id="btnReunionPreConvoy"
                            onclick="gestionarModalPreConvoy(true)"
                            title="Realizar la reunión antes de enviar la solicitud.">
                            <i class="fas fa-handshake"></i> Reunión Pre-convoy
                        </button>
                    </h3>

                    <div class="unidades-container">
                        <table class="unidades-table" id="tablaUnidades">
                            <thead>
                                <tr>
                                    <th class="th-vehiculo">
                                        <span class="column-title">Vehículo</span>
                                    </th>
                                    <th class="th-conductor-completo">
                                        <span class="column-title">Conductor</span>
                                    </th>
                                    <th class="th-aptitud-fisica">
                                        <span class="column-title">Aptitud Física</span>
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

                    <button type="button" id="btnEvaluacionRiesgo" onclick="gestionarModalEvaluacion(true)" disabled>
                        <i class="fas fa-list-check"></i> Realizar Evaluación del Viaje (Requerido)
                    </button>
                    <div class="form-section destinatario-section" id="seccionDestinatario" style="display: none;">
                        <h3 class="form-section-title">
                            <i class="fas fa-paper-plane"></i>
                            Enviar solicitud a: <span class="required">*</span>
                        </h3>
                        <p class="destinatario-subtitle">
                            Seleccione al responsable que deberá autorizar este gerenciamiento (Basado en el nivel de
                            riesgo).
                        </p>

                        <div class="destinatario-grid" id="gridAutorizadores">
                        </div>
                    </div>
                </div>
            </form>

            <div class="form-footer">
                <button type="button" class="btn-viajes btn-cancel" onclick="gestionarModalFormulario(false)">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="button" onclick="enviarSolicitud()" class="btn-viajes btn-submit"
                    id="btnEnviarSolicitud">
                    <i class="fas fa-paper-plane"></i>
                    Enviar Solicitud
                </button>
                <!-- Botón para modo lectura -->
                <button type="button" class="btn-cerrar-lectura" id="btnCerrarLectura" onclick="cerrarModoLectura()"
                    style="display: none;">
                    <i class="fas fa-times-circle"></i> Cerrar Vista
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL DE EVALUACIÓN DE RIESGO --}}
    <div class="modal-overlay" id="modalEvaluacion">
        <div class="modal-content">
            <div class="form-header-modal">
                <div class="form-header-info">
                    <div class="form-header-title">
                        <h2>Evaluación de Riesgos del Viaje</h2>
                        <p>Seleccione una opción para cada categoría.</p>
                    </div>
                </div>
                <div class="form-code-document">
                    <button type="button" class="btn-viajes btn-cancel" onclick="gestionarModalEvaluacion(false)">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>

            <div class="modal-inspeccion-body">
                <form id="formEvaluacionRiesgo">
                    <div class="evaluacion-grid">
                        <div class="evaluacion-item">
                            <div class="evaluacion-titulo">1. Curso manejo defensivo</div>
                            <div class="evaluacion-opciones">
                                <label class="evaluacion-radio"><input type="radio" name="ev_manejo" value="5"
                                        required> Todos los conductores cuentan con el manejo defensivo
                                    vigente</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_manejo" value="10"> 1
                                    o más conductores con manejo defensivo no vigente</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_manejo" value="15"> 1
                                    o más conductores No tiene manejo defensivo</label>
                            </div>
                        </div>

                        <div class="evaluacion-item">
                            <div class="evaluacion-titulo">2. Horas despierto</div>
                            <div class="evaluacion-opciones">
                                <label class="evaluacion-radio"><input type="radio" name="ev_horas" value="5"
                                        required> De 1 a 8 hrs</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_horas" value="10"> De
                                    9 a 12 hrs</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_horas" value="15"> De
                                    13 a 16 hrs</label>
                            </div>
                        </div>

                        <div class="evaluacion-item">
                            <div class="evaluacion-titulo">3. Núm. de vehículos y pasajeros</div>
                            <div class="evaluacion-opciones">
                                <label class="evaluacion-radio">
                                    <input type="radio" name="ev_vehiculos" value="5" required>
                                    Vehículo con 1 ó más pasajeros
                                </label>
                                <label class="evaluacion-radio">
                                    <input type="radio" name="ev_vehiculos" value="15">
                                    Vehículo sin pasajeros (solo conductor)
                                </label>

                                <label class="evaluacion-radio">
                                    <input type="radio" name="ev_vehiculos" value="5">
                                    Convoy con un pasajero o más por vehículo
                                </label>
                                <label class="evaluacion-radio">
                                    <input type="radio" name="ev_vehiculos" value="10">
                                    Convoy sin pasajeros (solo conductores)
                                </label>
                            </div>
                        </div>

                        <div class="evaluacion-item">
                            <div class="evaluacion-titulo">4. Comunicación</div>
                            <div class="evaluacion-opciones">
                                <label class="evaluacion-radio"><input type="radio" name="ev_comunicacion"
                                        value="5" required> Celulares con señal</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_comunicacion"
                                        value="10"> Algunas zonas sin señal</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_comunicacion"
                                        value="15"> Todo el viaje sin señal</label>
                            </div>
                        </div>

                        <div class="evaluacion-item">
                            <div class="evaluacion-titulo">5. Condiciones clima</div>
                            <div class="evaluacion-opciones">
                                <label class="evaluacion-radio"><input type="radio" name="ev_clima" value="5"
                                        required> Clima seco (No lluvias)</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_clima" value="10">
                                    Clima parcialmente nublado / Llovizna</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_clima" value="15">
                                    Clima nublado / Lluvia fuerte</label>
                            </div>
                        </div>

                        <div class="evaluacion-item">
                            <div class="evaluacion-titulo">6. Condiciones de iluminación</div>
                            <div class="evaluacion-opciones">
                                <label class="evaluacion-radio"><input type="radio" name="ev_iluminacion"
                                        value="5" required> Iluminación Clara</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_iluminacion"
                                        value="10"> Atardecer / Iluminación excesiva</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_iluminacion"
                                        value="15"> Noche - Poca o nula iluminación</label>
                            </div>
                        </div>

                        <div class="evaluacion-item">
                            <div class="evaluacion-titulo">7. Condiciones de la carretera</div>
                            <div class="evaluacion-opciones">
                                <label class="evaluacion-radio"><input type="radio" name="ev_carretera" value="5"
                                        required> En buen estado, condiciones normales</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_carretera"
                                        value="10"> Carretera con baches, huecos, mal estado</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_carretera"
                                        value="10"> Zonas de carretera con tráfico / reparaciones</label>
                            </div>
                        </div>

                        <div class="evaluacion-item">
                            <div class="evaluacion-titulo">8. Otras Cond. de la carretera</div>
                            <div class="evaluacion-opciones">
                                 <label class="evaluacion-radio"><input type="radio" name="ev_otras" value="0">
                                    No aplica</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_otras" value="10"
                                        required> Zona con curvas y pendientes</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_otras" value="15">
                                    Carretera con superficies mojadas; vados</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_otras" value="15">
                                    Carretera solitaria (unidad no puede detenerse)</label>

                            </div>
                        </div>

                        <div class="evaluacion-item">
                            <div class="evaluacion-titulo">9. Animales de la zona</div>
                            <div class="evaluacion-opciones">
                                <label class="evaluacion-radio"><input type="radio" name="ev_animales" value="5"
                                        required> No se conoce actividad o cruce</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_animales" value="10">
                                    Poca actividad o cruce de animales</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_animales" value="15">
                                    Alta actividad o cruce de animales</label>
                            </div>
                        </div>

                        <div class="evaluacion-item">
                            <div class="evaluacion-titulo">10. Seguridad de la ruta</div>
                            <div class="evaluacion-opciones">
                                <label class="evaluacion-radio"><input type="radio" name="ev_seguridad" value="5"
                                        required> No se conoce actividad de riesgo</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_seguridad"
                                        value="10"> Se sabe de mediana actividad de riesgo</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_seguridad"
                                        value="15"> Se sabe de alta actividad de riesgo</label>
                            </div>
                        </div>

                        <div class="evaluacion-item">
                            <div class="evaluacion-titulo">11. Viaje cumple con</div>
                            <div class="evaluacion-subtitulo"></div>
                            <div class="evaluacion-opciones">
                                <label class="evaluacion-radio">
                                    <input type="radio" name="ev_radiactivo" value="0" required>
                                    No aplica
                                </label>
                                <label class="evaluacion-radio">
                                    <input type="radio" name="ev_radiactivo" value="0" required>
                                    Transporte de equipo y/o maquinaria industrial
                                </label>
                                <label class="evaluacion-radio">
                                    <input type="radio" name="ev_radiactivo" value="0">
                                    Transporte material radiactivo (No aplica para clasificación UN2911-PNN / Co 60)
                                </label>
                                <label class="evaluacion-radio">
                                    <input type="radio" name="ev_radiactivo" value="0">
                                    Transporte material explosivo
                                </label>
                            </div>
                        </div>

                        <div class="evaluacion-item evaluacion-full-width">
                            <div class="evaluacion-titulo">Factores Adicionales de Riesgo</div>
                            <div class="evaluacion-factores">
                                <label class="factor-check">
                                    <input type="checkbox" name="ev_horario_nocturno">
                                    <span>Todo viaje después de las 21:00 hrs hasta la media noche</span>
                                </label>

                                <label class="factor-check">
                                    <input type="checkbox" name="ev_horas_dormidas">
                                    <span>Todo viaje con personal = o < 6 hrs dormidas</span>
                                </label>

                                <label class="factor-check">
                                    <input type="checkbox" name="ev_rebase_medianoche">
                                    <span>Toda continuidad de viaje que rebase la media noche</span>
                                </label>

                                <label class="factor-check">
                                    <input type="checkbox" name="ev_16hrs_despierto">
                                    <span>Conductor(es) con más de 16 hrs despierto(s)</span>
                                </label>
                            </div>
                        </div>


                    </div>
                </form>
            </div>

            <div class="form-footer">
                <button type="button" class="btn-viajes btn-primary" onclick="guardarEvaluacion()">
                    <i class="fas fa-save"></i> Guardar Evaluación
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL DE REUNIÓN PRE-CONVOY (YA PRE-RENDERIZADO) --}}
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

                <div class="inspeccion-grid">
                    {{-- ITEM 1 --}}
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-map-marked-alt"></i>
                            <span>Todos los conductores comprenden donde serán los puntos de parada o reporte?</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si">
                                <input type="radio" name="checklist_puntos_parada" value="si">
                                Sí
                            </label>
                            <label class="no">
                                <input type="radio" name="checklist_puntos_parada" value="no">
                                No
                            </label>
                        </div>
                    </div>

                    {{-- ITEM 2 --}}
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-road-barrier"></i>
                            <span>¿Todos los conductores saben que hacer en caso de ruptura del convoy?</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si">
                                <input type="radio" name="checklist_ruptura_convoy" value="si">
                                Sí
                            </label>
                            <label class="no">
                                <input type="radio" name="checklist_ruptura_convoy" value="no">
                                No
                            </label>
                        </div>
                    </div>

                    {{-- ITEM 3 --}}
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-folder-open"></i>
                            <span>Se asegura que todos los conductores verificarón la documentación vigente (tarjeta,
                                poliza, permisos)</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si">
                                <input type="radio" name="checklist_doc_vigente" value="si">
                                Sí
                            </label>
                            <label class="no">
                                <input type="radio" name="checklist_doc_vigente" value="no">
                                No
                            </label>
                        </div>
                    </div>

                    {{-- ITEM 4 --}}
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-helmet-safety"></i>
                            <span>Se asegura que los conductores sean concientes de aplicar la medidas y controles para
                                prevenir accidentes o incidentes</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si">
                                <input type="radio" name="checklist_prevencion_acc" value="si">
                                Sí
                            </label>
                            <label class="no">
                                <input type="radio" name="checklist_prevencion_acc" value="no">
                                No
                            </label>
                        </div>
                    </div>

                    {{-- ITEM 5 --}}
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-phone-volume"></i>
                            <span>Todos los conductores tiene y llevan consigo los contactos de emergencia / PRE</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si">
                                <input type="radio" name="checklist_contactos_emerg" value="si">
                                Sí
                            </label>
                            <label class="no">
                                <input type="radio" name="checklist_contactos_emerg" value="no">
                                No
                            </label>
                        </div>
                    </div>

                    {{-- ITEM 6 --}}
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-users-line"></i>
                            <span>Como líder de convoy manifiesta tener el compromiso y liderazgo para guiar este
                                viaje</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si">
                                <input type="radio" name="checklist_compromiso_lider" value="si">
                                Sí
                            </label>
                            <label class="no">
                                <input type="radio" name="checklist_compromiso_lider" value="no">
                                No
                            </label>
                        </div>
                    </div>


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

    {{-- MODAL DE INSPECCIÓN PARA UNIDADES LIGERAS --}}
    <div class="modal-overlay" id="modalInspeccionLigera">
        <div class="modal-content">
            <div class="form-header-modal">
                <div class="form-header-info">
                    <div class="form-header-title">
                        <h2>INSPECCIÓN DE UNIDADES LIGERAS</h2>
                    </div>
                </div>

                {{-- ZONA DERECHA: ÚLTIMA INSPECCIÓN, FECHA Y BOTÓN CERRAR --}}
                <div style="display: flex; align-items: center; gap: 15px;">

                    <div class="header-meta-info"
                        style="display: flex; align-items: center; color: white; font-size: 13px;">

                        {{-- 1. NUEVO: Bloque Última Inspección (A la izquierda de la fecha) --}}
                        <div
                            style="display: flex; flex-direction: column; align-items: flex-end; margin-right: 15px; padding-right: 15px; border-right: 1px solid rgba(255,255,255,0.3);">
                            {{-- Título pequeñito --}}
                            <span
                                style="font-size: 9px; opacity: 0.7; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">
                                Última Inspección
                            </span>
                            <div style="display: flex; align-items: center; gap: 6px; font-weight: 500;">
                                <i class="fas fa-calendar-day" style="opacity: 0.8;"></i>
                                {{-- El dato (Usamos un ID nuevo para llenarlo con JS) --}}
                                <span id="headerUltimaInspeccionLigera" style="font-weight: 700; font-size: 13px;">
                                    --/--/----
                                </span>
                            </div>

                        </div>

                        {{-- 2. Fecha de Inspección --}}
                        <div style="display: flex; flex-direction: column; align-items: flex-end;">
                            <span
                                style="font-size: 9px; opacity: 0.7; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">
                                Fecha de Inspección
                            </span>
                            <div style="display: flex; align-items: center; gap: 6px; font-weight: 500;">
                                <i class="fas fa-calendar-day" style="opacity: 0.8;"></i>
                                <span id="fechaActualInspeccionLigera">{{ date('d/m/Y') }}</span>
                            </div>
                        </div>

                    </div>

                    {{-- Separador vertical sutil --}}
                    <div style="width: 1px; height: 25px; background: rgba(255,255,255,0.3);"></div>

                    {{-- Botón Cerrar --}}
                    <div class="form-code-document">
                        <button type="button" class="btn-viajes btn-cancel"
                            onclick="gestionarModalInspeccionLigera(false)"
                            style="background: rgba(255,255,255,0.9); color: var(--primary-blue); border: none;">
                            <i class="fas fa-times"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>

            <form id="formInspeccionLigera" class="modal-inspeccion-body" enctype="multipart/form-data">
                <input type="hidden" id="inspeccionUnidadIndexLigera" name="unidad_index">

                {{-- CABECERA DE DATOS GENERALES --}}
                <div class="header-inspeccion-section">
                    <h4 class="section-subtitle-small">DATOS GENERALES DE LA UNIDAD</h4>

                    <div class="header-inspeccion-grid">
                        <div class="form-group">
                            <label>Nombre del Conductor</label>
                            <input type="text" id="inputNombreConductorLigera" name="nombre_conductor"
                                class="form-control" placeholder="Nombre completo" readonly>
                        </div>

                        {{-- 1. No Economico --}}
                        <div class="form-group">
                            <label>No. Económico</label>
                            <input type="text" id="inputNoEconomicoLigera" class="form-control" readonly>
                        </div>

                        {{-- 2. Marca --}}
                        <div class="form-group">
                            <label>Marca</label>
                            <input type="text" id="inputMarcaLigera" class="form-control" readonly>
                        </div>

                        {{-- 3. VES / Rentada --}}
                        <div class="form-group">
                            <label>VES / Rentada</label>
                            <input type="text" id="inputVesRentadaLigera" class="form-control" readonly>
                        </div>

                        {{-- 5. Tanque de Gasolina --}}
                        <div class="form-group">
                            <label class="label-highlight">Nivel Gasolina <i class="fas fa-gas-pump"></i></label>
                            <select name="nivel_gasolina" class="form-control select-highlight" required>
                                <option value="" disabled selected>-- Indique fracción --</option>
                                <option value="Reserva">Reserva</option>
                                <option value="1/4">1/4 de Tanque</option>
                                <option value="1/2">1/2 Tanque</option>
                                <option value="3/4">3/4 de Tanque</option>
                                <option value="Lleno">Tanque Lleno</option>
                            </select>
                        </div>

                        {{-- 6. Kilometraje --}}
                        <div class="form-group">
                            <label class="label-highlight">Kilometraje Actual <i
                                    class="fas fa-tachometer-alt"></i></label>
                            <input type="text" name="kilometraje" class="form-control input-highlight"
                                oninput="formatearKilometraje(this)" placeholder="Ej: 20,000" required>
                        </div>
                    </div>
                </div>

                <hr class="separator-line">

                {{-- SECCIÓN 1: DOCUMENTACIÓN --}}
                <h3 class="inspeccion-modal-title">
                    <i class="fas fa-folder-open"></i> I. DOCUMENTACIÓN
                </h3>
                <div class="inspeccion-grid">
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-id-card"></i> <span>Tarjeta de circulación (Original)</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="doc_tarjeta" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="doc_tarjeta" value="no"> No</label>
                            <label class="na"><input type="radio" name="doc_tarjeta" value="na"> N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-file-contract"></i> <span>Póliza de seguro vigente (Copia)</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="doc_poliza" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="doc_poliza" value="no"> No</label>
                            <label class="na"><input type="radio" name="doc_poliza" value="na"> N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-phone-alt"></i> <span>Teléfonos Emergencia / Aseguradora</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="doc_tel_emergencia" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="doc_tel_emergencia" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="doc_tel_emergencia" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-id-badge"></i> <span>Licencia de manejo vigente</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="doc_licencia" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="doc_licencia" value="no"> No</label>
                            <label class="na"><input type="radio" name="doc_licencia" value="na"> N/A</label>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 2: INSPECCIÓN VISUAL --}}
                <h3 class="inspeccion-modal-title" style="margin-top: 25px;">
                    <i class="fas fa-eye"></i> II. INSPECCIÓN VISUAL (Marcar si está en buen estado o no)
                </h3>
                <div class="inspeccion-grid">
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-medkit"></i> <span>Kit de primeros auxilios</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_botiquin" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_botiquin" value="no"> No</label>
                            <label class="na"><input type="radio" name="vis_botiquin" value="na"> N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-exclamation-triangle"></i> <span>Triángulos de seguridad</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_triangulo" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_triangulo" value="no"> No</label>
                            <label class="na"><input type="radio" name="vis_triangulo" value="na"> N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-fire-extinguisher"></i> <span>Extintor (Cargado/Vigente)</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_extintor" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_extintor" value="no"> No</label>
                            <label class="na"><input type="radio" name="vis_extintor" value="na"> N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-tools"></i> <span>Gato y Cruceta</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_gato" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_gato" value="no"> No</label>
                            <label class="na"><input type="radio" name="vis_gato" value="na"> N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-bolt"></i> <span>Cables pasa corrientes</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_cables" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_cables" value="no"> No</label>
                            <label class="na"><input type="radio" name="vis_cables" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-toolbox"></i> <span>Kit de herramientas básicas</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_herramientas" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="vis_herramientas" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_herramientas" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-lightbulb"></i> <span>Linterna de mano</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_linterna" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_linterna" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_linterna" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-car-side"></i> <span>Espejos (Laterales/Retrovisor)</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_espejos" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_espejos" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_espejos" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-compact-disc"></i> <span>Llanta de refacción</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_refaccion" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="vis_refaccion" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_refaccion" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-truck-monster"></i> <span>Neumáticos en buen estado</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_neumaticos" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="vis_neumaticos" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_neumaticos" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-spray-can"></i> <span>Laminación y pintura</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_pintura" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_pintura" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_pintura" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-window-maximize"></i> <span>Parabrisas y limpiadores</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_parabrisas" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="vis_parabrisas" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_parabrisas" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-minus-square"></i> <span>Defensas</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_defensas" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_defensas" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_defensas" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-lightbulb"></i> <span>Luces (Altas, Bajas, Direccionales)</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_luces_gral" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="vis_luces_gral" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_luces_gral" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-traffic-light"></i> <span>Luces de Stop y Reversa</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_luces_stop" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="vis_luces_stop" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_luces_stop" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-bullhorn"></i> <span>Claxon</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_claxon" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_claxon" value="no"> No</label>
                            <label class="na"><input type="radio" name="vis_claxon" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-copyright"></i> <span>Logotipos (Compañía y No. Eco)</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_logos" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_logos" value="no"> No</label>
                            <label class="na"><input type="radio" name="vis_logos" value="na"> N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-chair"></i> <span>Asientos</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_asientos" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_asientos" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_asientos" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-tachometer-alt"></i> <span>Panel de control (Indicadores)</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_panel" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_panel" value="no"> No</label>
                            <label class="na"><input type="radio" name="vis_panel" value="na"> N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-user-shield"></i> <span>Cinturones de seguridad</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_cinturones" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="vis_cinturones" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_cinturones" value="na">
                                N/A</label>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 3: MANTENIMIENTO --}}
                <h3 class="inspeccion-modal-title" style="margin-top: 25px;">
                    <i class="fas fa-wrench"></i> III. MANTENIMIENTO
                </h3>
                <div class="inspeccion-grid">
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-calendar-check"></i> <span>Fecha/Km último mantenimiento</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="mant_fecha_km" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="mant_fecha_km" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="mant_fecha_km" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-water"></i> <span>Revisión de fugas</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="mant_fugas" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="mant_fugas" value="no"> No</label>
                            <label class="na"><input type="radio" name="mant_fugas" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-oil-can"></i> <span>Niveles (Aceite, frenos, agua)</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="mant_niveles" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="mant_niveles" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="mant_niveles" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-cogs"></i> <span>Estado de bandas</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="mant_bandas" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="mant_bandas" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="mant_bandas" value="na">
                                N/A</label>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 4: ANOMALÍAS Y EVIDENCIA --}}
                <h3 class="inspeccion-modal-title" style="margin-top: 25px;">
                    <i class="fas fa-camera"></i> IV. ANOMALÍAS Y EVIDENCIA
                </h3>

                <div class="inspeccion-item"
                    style="background-color: #ffffff; border-left: 4px solid var(--primary-blue); border-radius: 4px;">
                    <div class="inspeccion-item-label">
                        <i class="fas fa-exclamation-triangle" style="color: #f08a1f;"></i>
                        <span>¿Anomalías, golpes o fallas?</span>
                    </div>
                    <div class="inspeccion-radio-group">
                        {{-- Opción SÍ (Rojo) --}}
                        <label class="no">
                            {{-- Agregamos appearance: auto para forzar el pintado nativo --}}
                            <input type="radio" name="anomalias_ligera" value="si" required>
                            Sí
                        </label>

                        {{-- Opción NO (Verde) --}}
                        <label class="si">
                            <input type="radio" name="anomalias_ligera" value="no">
                            No
                        </label>
                    </div>
                </div>

                <div class="evidence-container" id="evidenceContainerLigera" style="display: none;">
                    <div class="form-group full-width">
                        <label for="comentariosInspeccionLigera">Comentarios, Anomalías o Fallas detectadas:</label>
                        <textarea id="comentariosInspeccionLigera" name="comentarios" rows="3" class="form-control"
                            placeholder="Describa aquí cualquier detalle, golpe, o falla mecánica detectada..."></textarea>
                    </div>

                    <div class="evidence-upload-wrapper">
                        <div class="evidence-actions-header">
                            <label class="evidence-label">
                                <i class="fas fa-cloud-upload-alt"></i> Evidencia Fotográfica (Máx. 6 fotos)
                            </label>
                            <div class="evidence-buttons">
                                <button type="button" class="btn-attach"
                                    onclick="document.getElementById('evidenciaInspeccionLigera').click()">
                                    <i class="fas fa-paperclip"></i> Adjuntar Fotos
                                </button>
                                <button type="button" class="btn-camera" onclick="abrirCamara('ligera')">
                                    <i class="fas fa-camera"></i> Usar Cámara
                                </button>
                            </div>
                        </div>

                        <input type="file" id="evidenciaInspeccionLigera" name="evidencia[]" multiple
                            accept="image/*, .pdf" class="input-file-evidence" data-max-files="6"
                            data-tipo="ligera">

                        <div class="file-upload-box unified-box" id="dropZoneLigera">

                            <div class="upload-placeholder" id="placeholderLigera">
                                <i class="fas fa-images"></i>
                                <span>Arrastra tus fotos aquí</span>
                                <small>Formatos: JPG, PNG, PDF - Máximo 6 archivos</small>
                            </div>

                            <div id="previewContainerLigera" class="preview-content" style="display: none;">
                                <div class="preview-header">
                                    <span class="preview-count" id="previewCountLigera">0 fotos seleccionadas</span>
                                    <button type="button" class="btn-clear-all" onclick="limpiarFotos('ligera')">
                                        <i class="fas fa-trash"></i> Eliminar todas
                                    </button>
                                </div>
                                <div id="previewGridLigera" class="preview-grid">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="driver-commitment">
                    <p>
                        <i class="fas fa-user-check"></i>
                        Yo conductor me comprometo a realizar a conciencia la inspección de la unidad vehicular
                        con la finalidad de asegurar que está en condiciones de realizar un viaje seguro.
                    </p>
                </div>

            </form>

            <div class="form-footer">
                <button type="button" class="btn-viajes btn-cancel" onclick="gestionarModalInspeccionLigera(false)">
                    Cancelar
                </button>
                <button type="button" class="btn-viajes btn-primary" onclick="guardarInspeccionLigera()">
                    <i class="fas fa-save"></i> Guardar Inspección
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL DE INSPECCIÓN PARA UNIDADES PESADAS --}}
    <div class="modal-overlay" id="modalInspeccionPesada">
        <div class="modal-content">
            <div class="form-header-modal">
                <div class="form-header-info">
                    <div class="form-header-title">
                        <h2>INSPECCIÓN DE UNIDADES PESADAS</h2>
                    </div>
                </div>

                {{-- ZONA DERECHA: ÚLTIMA INSPECCIÓN, FECHA Y BOTÓN CERRAR --}}
                <div style="display: flex; align-items: center; gap: 15px;">

                    {{-- Bloque de información (Inspector y Fecha) --}}
                    <div class="header-meta-info"
                        style="display: flex; align-items: center; color: white; font-size: 13px;">

                        <div
                            style="display: flex; flex-direction: column; align-items: flex-end; margin-right: 15px; padding-right: 15px; border-right: 1px solid rgba(255,255,255,0.3);">
                            {{-- Título pequeñito --}}
                            <span
                                style="font-size: 9px; opacity: 0.7; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">
                                Última Inspección
                            </span>
                            {{-- El dato (ID ESPECÍFICO PARA PESADA) --}}
                            <span id="headerUltimaInspeccionPesada" style="font-weight: 700; font-size: 13px;">
                                --/--/----
                            </span>
                        </div>

                        {{-- 2. Fecha de Inspección --}}
                        <div style="display: flex; flex-direction: column; align-items: flex-end;">
                            <span
                                style="font-size: 9px; opacity: 0.7; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">
                                Fecha de Inspección
                            </span>
                            <div style="display: flex; align-items: center; gap: 6px; font-weight: 500;">
                                <i class="fas fa-calendar-day" style="opacity: 0.8;"></i>
                                <span id="fechaActualInspeccionPesada">{{ date('d/m/Y') }}</span>
                            </div>
                        </div>

                    </div>

                    {{-- Separador vertical sutil --}}
                    <div style="width: 1px; height: 25px; background: rgba(255,255,255,0.3);"></div>

                    {{-- Botón Cerrar --}}
                    <div class="form-code-document">
                        <button type="button" class="btn-viajes btn-cancel"
                            onclick="gestionarModalInspeccionPesada(false)"
                            style="background: rgba(255,255,255,0.9); color: var(--primary-blue); border: none;">
                            <i class="fas fa-times"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>

            <form id="formInspeccionPesada" class="modal-inspeccion-body" enctype="multipart/form-data">
                <input type="hidden" id="inspeccionUnidadIndexPesada" name="unidad_index">

                {{-- CABECERA DE DATOS GENERALES --}}
                <div class="header-inspeccion-section">
                    <h4 class="section-subtitle-small">DATOS GENERALES DE LA UNIDAD</h4>

                    <div class="header-inspeccion-grid">
                        <div class="form-group">
                            <label>Nombre del Conductor</label>
                            <input type="text" id="inputNombreConductorPesada" name="nombre_conductor"
                                class="form-control" placeholder="Nombre completo" readonly>
                        </div>
                        {{-- 1. No Economico --}}
                        <div class="form-group">
                            <label>No. Económico</label>
                            <input type="text" id="inputNoEconomicoPesada" class="form-control" readonly>
                        </div>

                        {{-- 2. Marca --}}
                        <div class="form-group">
                            <label>Marca</label>
                            <input type="text" id="inputMarcaPesada" class="form-control" readonly>
                        </div>

                        {{-- 3. VES / Rentada --}}
                        <div class="form-group">
                            <label>VES / Rentada</label>
                            <input type="text" id="inputVesRentadaPesada" class="form-control" readonly>
                        </div>

                        {{-- 5. Tanque de Diesel --}}
                        <div class="form-group">
                            <label class="label-highlight">Nivel Diesel <i class="fas fa-gas-pump"></i></label>
                            <select name="nivel_diesel" class="form-control select-highlight" required>
                                <option value="" disabled selected>-- Indique fracción --</option>
                                <option value="Reserva">Reserva</option>
                                <option value="1/4">1/4 de Tanque</option>
                                <option value="1/2">1/2 Tanque</option>
                                <option value="3/4">3/4 de Tanque</option>
                                <option value="Lleno">Tanque Lleno</option>
                            </select>
                        </div>

                        {{-- 6. Kilometraje --}}
                        <div class="form-group">
                            <label class="label-highlight">Kilometraje Actual <i
                                    class="fas fa-tachometer-alt"></i></label>
                            <input type="text" name="kilometraje" class="form-control input-highlight"
                                oninput="formatearKilometraje(this)" placeholder="Ej: 20,000" required>
                        </div>
                        <div class="form-group"></div>
                        <div class="form-group"></div>
                    </div>
                </div>

                <hr class="separator-line">

                {{-- SECCIÓN 1: DOCUMENTACIÓN --}}
                <h3 class="inspeccion-modal-title">
                    <i class="fas fa-folder-open"></i> I. DOCUMENTACIÓN
                </h3>
                <div class="inspeccion-grid">
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-id-card"></i> <span>Tarjeta de circulación</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="doc_tarjeta" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="doc_tarjeta" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="doc_tarjeta" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-file-contract"></i> <span>Póliza de seguro vigente</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="doc_poliza" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="doc_poliza" value="no"> No</label>
                            <label class="na"><input type="radio" name="doc_poliza" value="na">
                                N/A</label>
                        </div>
                    </div>
                    {{-- Específicos Pesados --}}
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-truck-loading"></i> <span>Permiso de transporte de carga</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="doc_permiso_carga" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="doc_permiso_carga" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="doc_permiso_carga" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-leaf"></i> <span>Certificado de bajos contaminantes</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="doc_bajos_contam" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="doc_bajos_contam" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="doc_bajos_contam" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-tools"></i> <span>Certificado físico mecánico</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="doc_fisico_mec" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="doc_fisico_mec" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="doc_fisico_mec" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-file-invoice"></i> <span>Carta Porte</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="doc_carta_porte" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="doc_carta_porte" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="doc_carta_porte" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-phone-alt"></i> <span>Teléfonos Emergencia / Aseguradora</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="doc_tel_emergencia" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="doc_tel_emergencia" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="doc_tel_emergencia" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-id-badge"></i> <span>Licencia de manejo vigente</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="doc_licencia" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="doc_licencia" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="doc_licencia" value="na">
                                N/A</label>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 2: INSPECCIÓN VISUAL --}}
                <h3 class="inspeccion-modal-title" style="margin-top: 25px;">
                    <i class="fas fa-eye"></i> II. INSPECCIÓN VISUAL (Marcar si está en buen estado o no)
                </h3>
                <div class="inspeccion-grid">
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-medkit"></i> <span>Kit de primeros auxilios</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_botiquin" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_botiquin" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_botiquin" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-traffic-cone"></i> <span>Conos reflejantes</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_conos" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_conos" value="no"> No</label>
                            <label class="na"><input type="radio" name="vis_conos" value="na"> N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-fire-extinguisher"></i> <span>Extintor vigencia</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_extintor" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_extintor" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_extintor" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-tools"></i> <span>Gato</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_gato" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_gato" value="no"> No</label>
                            <label class="na"><input type="radio" name="vis_gato" value="na"> N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-bolt"></i> <span>Cables pasa corrientes</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_cables" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_cables" value="no"> No</label>
                            <label class="na"><input type="radio" name="vis_cables" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-lightbulb"></i> <span>Linterna de mano</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_linterna" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_linterna" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_linterna" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-car-side"></i> <span>Espejos</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_espejos" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_espejos" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_espejos" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-compact-disc"></i> <span>Llanta de refacción</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_refaccion" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="vis_refaccion" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_refaccion" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-truck-monster"></i> <span>Llantas en buen estado</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_llantas_estado" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="vis_llantas_estado" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_llantas_estado" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-compress-arrows-alt"></i> <span>Llantas calibradas</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_llantas_calib" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="vis_llantas_calib" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_llantas_calib" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-door-open"></i> <span>Puertas, vidrios y ventanas</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_puertas" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_puertas" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_puertas" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-car-crash"></i> <span>Golpes</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_golpes" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_golpes" value="no"> No</label>
                            <label class="na"><input type="radio" name="vis_golpes" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-wiper"></i> <span>Limpia parabrisas</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_limpiaparabrisas" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="vis_limpiaparabrisas" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_limpiaparabrisas" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-snowflake"></i> <span>Funcionamiento Aire Acondicionado</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_aire_acond" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="vis_aire_acond" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_aire_acond" value="na">
                                N/A</label>
                        </div>
                    </div>
                    {{-- Específicos Suspensión --}}
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-align-justify"></i> <span>Resortes y muelles</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_resortes" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_resortes" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_resortes" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-wind"></i> <span>Bolsas de aire de suspensión</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_bolsas_aire" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="vis_bolsas_aire" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_bolsas_aire" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-lightbulb"></i> <span>Luces en general</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_luces_gral" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="vis_luces_gral" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_luces_gral" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-bullhorn"></i> <span>Claxon</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_claxon" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_claxon" value="no"> No</label>
                            <label class="na"><input type="radio" name="vis_claxon" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-volume-up"></i> <span>Alarma de reversa</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_alarma_reversa" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="vis_alarma_reversa" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_alarma_reversa" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-copyright"></i> <span>Logotipos (compañía y num. económico)</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_logos" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_logos" value="no"> No</label>
                            <label class="na"><input type="radio" name="vis_logos" value="na"> N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-chair"></i> <span>Asientos</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_asientos" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_asientos" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_asientos" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-user-shield"></i> <span>Cinturones de seguridad en buen estado</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_cinturones" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="vis_cinturones" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_cinturones" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-sun"></i> <span>Torreta</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="vis_torreta" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="vis_torreta" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="vis_torreta" value="na">
                                N/A</label>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 3: MANTENIMIENTO --}}
                <h3 class="inspeccion-modal-title" style="margin-top: 25px;">
                    <i class="fas fa-wrench"></i> III. MANTENIMIENTO
                </h3>
                <div class="inspeccion-grid">
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-calendar-check"></i> <span>Fecha y kilometraje del último
                                mantenimiento</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="mant_fecha_km" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="mant_fecha_km" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="mant_fecha_km" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-key"></i> <span>Encendido de motor</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="mant_encendido" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="mant_encendido" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="mant_encendido" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-oil-can"></i> <span>Presión de aceite de motor</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="mant_presion_aceite" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="mant_presion_aceite" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="mant_presion_aceite" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-thermometer-half"></i> <span>Temperatura del motor</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="mant_temp_motor" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="mant_temp_motor" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="mant_temp_motor" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-compress"></i> <span>Presión de Aire</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="mant_presion_aire" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="mant_presion_aire" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="mant_presion_aire" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-fan"></i> <span>Fan clutch</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="mant_fan_clutch" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="mant_fan_clutch" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="mant_fan_clutch" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-car-battery"></i> <span>Condiciones de baterías</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="mant_baterias" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="mant_baterias" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="mant_baterias" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-tachometer-alt"></i> <span>Velocímetro</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="mant_velocimetro" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="mant_velocimetro" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="mant_velocimetro" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-tachometer-alt"></i> <span>Indicador de RPM</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="mant_rpm" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="mant_rpm" value="no"> No</label>
                            <label class="na"><input type="radio" name="mant_rpm" value="na"> N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-oil-can"></i> <span>Nivel Aceite de motor</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="mant_nivel_aceite" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="mant_nivel_aceite" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="mant_nivel_aceite" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-tint"></i> <span>Nivel Anticongelante</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="mant_nivel_anticongelante"
                                    value="si" required> Sí</label>
                            <label class="no"><input type="radio" name="mant_nivel_anticongelante"
                                    value="no"> No</label>
                            <label class="na"><input type="radio" name="mant_nivel_anticongelante"
                                    value="na"> N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-oil-can"></i> <span>Nivel de aceite hidraulico</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="mant_nivel_hidraulico" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="mant_nivel_hidraulico" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="mant_nivel_hidraulico" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-gas-pump"></i> <span>Nivel de diesel</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="mant_nivel_diesel" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="mant_nivel_diesel" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="mant_nivel_diesel" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-anchor"></i> <span>Freno de motor</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="mant_freno_motor" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="mant_freno_motor" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="mant_freno_motor" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-stop-circle"></i> <span>Freno de parqueo</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="mant_freno_parqueo" value="si"
                                    required> Sí</label>
                            <label class="no"><input type="radio" name="mant_freno_parqueo" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="mant_freno_parqueo" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-sync"></i> <span>Bandas</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="mant_bandas" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="mant_bandas" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="mant_bandas" value="na">
                                N/A</label>
                        </div>
                    </div>
                    <div class="inspeccion-item">
                        <div class="inspeccion-item-label">
                            <i class="fas fa-wind"></i> <span>Purgado de tanque de aire</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si"><input type="radio" name="mant_purgado" value="si" required>
                                Sí</label>
                            <label class="no"><input type="radio" name="mant_purgado" value="no">
                                No</label>
                            <label class="na"><input type="radio" name="mant_purgado" value="na">
                                N/A</label>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 4: ANOMALÍAS Y EVIDENCIA --}}
                <h3 class="inspeccion-modal-title" style="margin-top: 25px;">
                    <i class="fas fa-camera"></i> IV. ANOMALÍAS Y EVIDENCIA
                </h3>
                <div class="inspeccion-item"
                    style="background-color: #ffffff; border-left: 4px solid var(--primary-blue); border-radius: 4px;">
                    <div class="inspeccion-item-label">
                        <i class="fas fa-exclamation-triangle" style="color: #f08a1f;"></i>
                        <span>¿Anomalías, golpes o fallas?</span>
                    </div>
                    <div class="inspeccion-radio-group">
                        {{-- Opción SÍ (Rojo / Alerta) --}}
                        <label class="no">
                            {{-- Agregamos appearance: auto para forzar el pintado nativo si es necesario --}}
                            <input type="radio" name="anomalias_pesada" value="si" required>
                            Sí
                        </label>

                        {{-- Opción NO (Verde / Seguro) --}}
                        <label class="si">
                            <input type="radio" name="anomalias_pesada" value="no">
                            No
                        </label>
                    </div>
                </div>

                <div class="evidence-container" id="evidenceContainerPesada" style="display: none;">
                    <div class="form-group full-width">
                        <label for="comentariosInspeccionPesada">Comentarios, Anomalías o Fallas detectadas:</label>
                        <textarea id="comentariosInspeccionPesada" name="comentarios" rows="3" class="form-control"
                            placeholder="Describa aquí cualquier detalle, golpe, o falla mecánica detectada..."></textarea>
                    </div>

                    <div class="evidence-upload-wrapper">
                        <div class="evidence-actions-header">
                            <label class="evidence-label">
                                <i class="fas fa-cloud-upload-alt"></i> Evidencia Fotográfica (Máx. 6 fotos)
                            </label>
                            <div class="evidence-buttons">
                                <button type="button" class="btn-attach"
                                    onclick="document.getElementById('evidenciaInspeccionPesada').click()">
                                    <i class="fas fa-paperclip"></i> Adjuntar Fotos
                                </button>
                                <button type="button" class="btn-camera" onclick="abrirCamara('pesada')">
                                    <i class="fas fa-camera"></i> Usar Cámara
                                </button>
                            </div>
                        </div>

                        <input type="file" id="evidenciaInspeccionPesada" name="evidencia[]" multiple
                            accept="image/*, .pdf" class="input-file-evidence" data-max-files="6"
                            data-tipo="pesada">

                        <div class="file-upload-box unified-box" id="dropZonePesada">

                            <div class="upload-placeholder" id="placeholderPesada">
                                <i class="fas fa-images"></i>
                                <span>Arrastra tus fotos aquí</span>
                                <small>Formatos: JPG, PNG, PDF - Máximo 6 archivos</small>
                            </div>

                            <div id="previewContainerPesada" class="preview-content" style="display: none;">
                                <div class="preview-header">
                                    <span class="preview-count" id="previewCountPesada">0 fotos seleccionadas</span>
                                    <button type="button" class="btn-clear-all" onclick="limpiarFotos('pesada')">
                                        <i class="fas fa-trash"></i> Eliminar todas
                                    </button>
                                </div>
                                <div id="previewGridPesada" class="preview-grid">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </form>

            <div class="form-footer">
                <button type="button" class="btn-viajes btn-cancel" onclick="gestionarModalInspeccionPesada(false)">
                    Cancelar
                </button>
                <button type="button" class="btn-viajes btn-primary" onclick="guardarInspeccionPesada()">
                    <i class="fas fa-save"></i> Guardar Inspección
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL PARA USAR CÁMARA --}}

    <div class="modal-camara" id="modalCamara">
        <div class="camara-content">

            <video id="videoCamara" autoplay playsinline muted></video>
            <canvas id="canvasCamara" style="display:none;"></canvas>

            <button type="button" class="btn-camara-close" onclick="cerrarCamara()">
                <i class="fas fa-times"></i>
            </button>

            <div class="camara-controls-overlay">

                <button type="button" class="btn-control-secondary" onclick="alternarCamara()"
                    title="Cambiar cámara">
                    <i class="fas fa-sync-alt"></i>
                </button>

                <button type="button" class="btn-capture-main" onclick="capturarFoto()">
                    <div class="inner-circle"></div>
                </button>

                <div style="width: 50px;"></div>
            </div>

            <div class="camara-instructions">Ajusta la imagen y captura</div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/qhse/management/journey.js') }}"></script>
@endpush
