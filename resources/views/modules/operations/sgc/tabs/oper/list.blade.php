<style>
    .sgc-operations [hidden] {
        display: none !important;
    }
</style>

<div class="sgc-operations" data-operations-root>

    <!-- ENCABEZADO -->
    <header class="op-page-header">
        <h1>Flujo de Operaciones y Calidad</h1>

        <p>
            Selecciona el servicio para consultar sus etapas,
            completar información y acceder a las evidencias.
        </p>
    </header>

    <!-- MANTENIMIENTO -->
    <section class="op-maintenance-banner no-print">
        <div>
            <strong>Control de mantenimiento</strong>

            <p>
                Consulta evidencias, registra mantenimiento
                o llena un formato.
            </p>
        </div>

        <div class="op-actions">
            <a href="/operations/sgc/maintenance?view=list" class="btn-row btn-row-dark">
                <i class="fas fa-tools" aria-hidden="true"></i>
                Mantenimiento
            </a>

            <a href="/operations/sgc/maintenance?view=formats" class="btn-row op-button-amber">
                <i class="fas fa-file-alt" aria-hidden="true"></i>
                Formatos
            </a>
        </div>
    </section>

    <!-- FILTROS -->
    <section class="filter-section no-print">
        <form id="filter-form" data-operations-filters action="/operations/sgc/operations" method="GET">
            <input type="hidden" name="view" value="list">

            <fieldset data-op-fields disabled style="margin: 0; padding: 0; border: 0; min-width: 0;">
                <div class="filter-grid">

                    <div class="filter-group">
                        <label class="filter-label" for="op-search">
                            Buscar
                        </label>

                        <input id="op-search" type="search" name="q" class="filter-input"
                            placeholder="OS, cliente, pozo...">
                    </div>

                    <div class="filter-group">
                        <label class="filter-label" for="op-year">
                            Año
                        </label>

                        <select id="op-year" name="anio" class="filter-input" disabled>
                            <option value="">Todos los años</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label" for="op-month">
                            Mes
                        </label>

                        <select id="op-month" name="mes" class="filter-input" disabled>
                            <option value="">Todos los meses</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label" for="op-client">
                            Cliente
                        </label>

                        <select id="op-client" name="cliente" class="filter-input">
                            <option value="">Todos los clientes</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label" for="op-well">
                            Pozo
                        </label>

                        <select id="op-well" name="pozo" class="filter-input">
                            <option value="">Todos los pozos</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label" for="op-status">
                            Estado
                        </label>

                        <select id="op-status" name="estado" class="filter-input" disabled>
                            <option value="">Todos los estados</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label" for="op-type">
                            Tipo de servicio
                        </label>

                        <select id="op-type" name="tipo" class="filter-input" disabled>
                            <option value="">Todos los tipos</option>
                        </select>
                    </div>

                    <div class="filter-group op-service-picker">
                        <label class="filter-label" for="op-service">
                            Servicio / OS
                        </label>

                        <select id="op-service" name="serviceId" class="filter-input">
                            <option value="">
                                Cargando servicios...
                            </option>
                        </select>
                    </div>

                </div>
            </fieldset>

            <div class="op-filter-actions">
                <button type="submit" class="btn btn-sm btn-primary" data-op-submit disabled>
                    <i class="fas fa-filter" aria-hidden="true"></i>
                    Filtrar / cargar flujo
                </button>

                <a href="/operations/sgc/operations?view=list" class="btn btn-sm btn-outline tab-link">
                    Limpiar filtros
                </a>

                <a href="#" class="btn btn-sm btn-outline" data-op-edit hidden>
                    Editar servicio
                </a>
            </div>

            <p class="op-data-notice" data-op-loading>
                Cargando catálogo de operaciones...
            </p>

            <p class="op-filter-count" role="status">
                Servicios filtrados:
                <strong data-op-filtered>—</strong>
                de
                <strong data-op-total>—</strong>
            </p>

            <p class="op-data-notice">
                Los filtros de año, mes, estado y tipo requieren
                sus datos asociados a cada servicio.
            </p>
        </form>
    </section>

    <!-- ERROR DE CARGA -->
    <section class="op-empty no-print" data-op-error hidden>
        <h2>No se pudo cargar el catálogo de servicios</h2>

        <p>Reintenta la carga para consultar el flujo.</p>

        <button type="button" class="btn btn-sm btn-outline" data-op-retry>
            Reintentar carga
        </button>
    </section>

    <!-- SERVICIO SELECCIONADO -->
    <section class="op-selected-service" data-op-selected hidden>
        <span>Servicio seleccionado:</span>

        <strong data-op-os></strong>

        <span class="op-separator">·</span>
        <span data-op-client></span>

        <span class="op-separator">·</span>
        <span data-op-well></span>
    </section>

    <!-- ETAPAS -->
    <section class="op-flow-grid" aria-label="Etapas del servicio seleccionado" data-op-flow hidden></section>

    <!-- SIN RESULTADOS -->
    <section class="op-empty" data-op-empty hidden>
        <i class="fas fa-search" aria-hidden="true"></i>

        <h2>No se encontraron servicios</h2>

        <p>
            Modifica los filtros para seleccionar un servicio
            y consultar su flujo.
        </p>

        <a href="/operations/sgc/operations?view=list" class="btn btn-sm btn-outline tab-link">
            Limpiar filtros
        </a>
    </section>

    <!-- PLANTILLA DE ETAPA -->
    <template data-op-phase-template>
        <article class="op-phase">
            <header class="op-phase-header">
                <h2 data-op-phase-name></h2>
                <span data-op-phase-count></span>
            </header>

            <div class="op-phase-body" data-op-phase-body></div>
        </article>
    </template>

    <!-- PLANTILLA DE ACTIVIDAD -->
    <template data-op-activity-template>
        <section class="op-activity">
            <div class="op-activity-heading">
                <span class="op-activity-number" data-op-number></span>

                <div>
                    <h3 data-op-activity-title></h3>

                    <p>
                        Completa información del servicio,
                        adjunta evidencia y consulta
                        procedimientos/checklists.
                    </p>
                </div>
            </div>

            <div class="op-activity-actions no-print">
                <a href="#" class="btn-row btn-row-dark" data-op-action="edit">
                    Editar / completar
                </a>

                <a href="#" class="btn-row btn-row-outline" data-op-action="evidence">
                    Ver evidencias
                </a>

                <a href="#" class="btn-row op-button-blue" data-op-action="upload">
                    + Subir evidencia
                </a>

                <a href="#" class="btn-row op-button-green" data-op-action="catalog">
                    Catálogo docs
                </a>
            </div>
        </section>
    </template>

</div>
