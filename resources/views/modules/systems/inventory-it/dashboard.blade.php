@extends('modules.systems.inventory-it.index')

@push('styles')
    <link href="{{ asset('assets/css/systems/inventoryit/dashboard.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/systems/inventoryit/inventory.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="dashboard-wrapper" id="inventoryApp">

    <!-- Encabezado y acciones principales -->
    <div class="dashboard-header">
        <div class="dashboard-title">
            <h1><i class="fas fa-chart-pie"></i> Inventario TI <span>Parque tecnológico</span></h1>
        </div>

        <div class="view-switch" role="tablist" aria-label="Cambiar vista">
            <button type="button" class="view-btn active" data-view="dashboard" role="tab" aria-selected="true">
                <i class="fas fa-chart-pie"></i> Dashboard
            </button>
            <button type="button" class="view-btn" data-view="inventario" role="tab" aria-selected="false">
                <i class="fas fa-boxes-stacked"></i> Inventario
            </button>
        </div>

        <div class="dashboard-actions">
            <button type="button" class="btn btn-outline" id="btnExport">
                <i class="fas fa-file-excel"></i> Exportar CSV
            </button>
            <button type="button" class="btn btn-primary" id="btnAlta">
                <i class="fas fa-plus"></i> Alta de equipo
            </button>
        </div>
    </div>

    <!-- ===================== VISTA: DASHBOARD ===================== -->
    <section id="viewDashboard" class="view-section">

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-laptop"></i></div>
                <div class="stat-info">
                    <h4>Total equipos</h4>
                    <div class="stat-value" id="statTotal">0</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
                <div class="stat-info">
                    <h4>Asignados</h4>
                    <div class="stat-value" id="statAsignados">0</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><i class="fas fa-tools"></i></div>
                <div class="stat-info">
                    <h4>En mantenimiento</h4>
                    <div class="stat-value" id="statMant">0</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-triangle-exclamation"></i></div>
                <div class="stat-info">
                    <h4>Fallas reportadas</h4>
                    <div class="stat-value" id="statFallas">0</div>
                </div>
            </div>
        </div>

        <div class="panels-grid">
            <!-- Recientes -->
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title"><i class="fas fa-boxes"></i> Recientes en inventario</div>
                    <div class="panel-filters">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="dashSearch" placeholder="Buscar equipo...">
                        </div>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr><th>Equipo</th><th>Tipo</th><th>Estado</th></tr>
                        </thead>
                        <tbody id="dashRecientesBody"></tbody>
                    </table>
                </div>
            </div>

            <!-- Mantenimientos -->
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title"><i class="fas fa-clock-rotate-left"></i> Últimos mantenimientos</div>
                    <div class="panel-filters">
                        <select class="filter-select" id="dashMantFilter">
                            <option value="">Todos</option>
                            <option value="preventivo">Preventivos</option>
                            <option value="correctivo">Correctivos</option>
                        </select>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr><th>Equipo</th><th>Tipo</th><th>Técnico</th><th>Fecha</th></tr>
                        </thead>
                        <tbody id="dashMantBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="panels-grid">
            <!-- Por tipo -->
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title"><i class="fas fa-chart-bar"></i> Equipos por tipo</div>
                    <button type="button" class="panel-link" data-goto="inventario">Ver inventario</button>
                </div>
                <div class="panel-body" id="tipoBars"></div>
            </div>

            <!-- Garantías -->
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title"><i class="fas fa-shield-halved"></i> Garantías por atender</div>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr><th>Equipo</th><th>Vence</th><th>Situación</th></tr>
                        </thead>
                        <tbody id="garantiasBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== VISTA: INVENTARIO ===================== -->
    <section id="viewInventario" class="view-section" hidden>

        <div class="type-tabs" id="typeTabs" role="tablist" aria-label="Filtrar por tipo de equipo"></div>

        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">
                    <i class="fas fa-list"></i> Equipos
                    <span class="result-count" id="resultCount"></span>
                </div>
                <div class="panel-filters">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Código, serie, modelo o usuario...">
                    </div>
                    <select class="filter-select" id="estadoFilter" aria-label="Filtrar por estado"></select>
                    <select class="filter-select" id="garantiaFilter" aria-label="Filtrar por garantía">
                        <option value="">Cualquier garantía</option>
                        <option value="vigente">Vigente</option>
                        <option value="por_vencer">Por vencer</option>
                        <option value="vencida">Vencida</option>
                    </select>
                </div>
            </div>

            <div class="table-wrapper">
                <table id="equiposTable">
                    <thead>
                        <tr>
                            <th>Equipo</th>
                            <th>Código</th>
                            <th>Tipo</th>
                            <th>Asignado a</th>
                            <th>Ubicación</th>
                            <th>Estado</th>
                            <th>Garantía</th>
                            <th class="th-actions"></th>
                        </tr>
                    </thead>
                    <tbody id="equiposBody"></tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- ============ Modal: detalle del equipo ============ -->
<div class="modal-backdrop" id="detalleModal" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="detalleTitulo">
        <div class="modal-header">
            <div class="modal-head-info">
                <span class="tipo-icon lg" id="dIcono"></span>
                <div>
                    <h2 id="detalleTitulo">—</h2>
                    <div class="modal-sub">
                        <code class="codigo" id="dCodigo"></code>
                        <span class="badge" id="dEstado"></span>
                    </div>
                </div>
            </div>
            <button type="button" class="icon-btn" data-close aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
        </div>

        <div class="modal-body">
            <section class="detail-section">
                <h3><i class="fas fa-circle-info"></i> Datos generales</h3>
                <dl class="detail-grid" id="dGeneral"></dl>
            </section>
            <section class="detail-section">
                <h3><i class="fas fa-microchip"></i> Características</h3>
                <dl class="detail-grid" id="dSpecs"></dl>
            </section>
            <section class="detail-section">
                <h3><i class="fas fa-user"></i> Asignación</h3>
                <dl class="detail-grid" id="dAsignacion"></dl>
            </section>
            <section class="detail-section">
                <h3><i class="fas fa-plug"></i> Accesorios entregados</h3>
                <div class="chip-list" id="dAccesorios"></div>
            </section>
            <section class="detail-section">
                <h3><i class="fas fa-note-sticky"></i> Observaciones IT</h3>
                <p class="detail-note" id="dObs"></p>
            </section>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline" id="btnResponsiva">
                <i class="fas fa-file-signature"></i> Imprimir responsiva
            </button>
            <button type="button" class="btn btn-primary" data-close>Cerrar</button>
        </div>
    </div>
</div>

<!-- ============ Modal: alta de equipo (maqueta) ============ -->
<div class="modal-backdrop" id="altaModal" hidden>
    <div class="modal modal-sm" role="dialog" aria-modal="true" aria-labelledby="altaTitulo">
        <div class="modal-header">
            <div class="modal-head-info">
                <span class="tipo-icon lg blue"><i class="fas fa-plus"></i></span>
                <div><h2 id="altaTitulo">Alta de equipo</h2></div>
            </div>
            <button type="button" class="icon-btn" data-close aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
        </div>
        <form id="altaForm" autocomplete="off">
            <div class="modal-body">
                <div class="form-grid">
                    <label class="field">
                        <span>Tipo de equipo</span>
                        <select name="tipo" id="altaTipo" required></select>
                    </label>
                    <label class="field">
                        <span>Estado inicial</span>
                        <select name="estado">
                            <option value="disponible">Disponible</option>
                            <option value="asignado">Asignado</option>
                        </select>
                    </label>
                    <label class="field"><span>Marca</span><input type="text" name="marca" placeholder="Lenovo" required></label>
                    <label class="field"><span>Modelo</span><input type="text" name="modelo" placeholder="ThinkPad E16 Gen 3" required></label>
                    <label class="field"><span>Número de serie</span><input type="text" name="serie" placeholder="PF6GFE6R" required></label>
                    <label class="field"><span>Fecha de compra</span><input type="date" name="fecha_compra"></label>
                    <label class="field"><span>Garantía hasta</span><input type="date" name="garantia_hasta"></label>
                    <label class="field"><span>Ubicación</span><input type="text" name="ubicacion" placeholder="Almacén IT"></label>
                    <label class="field field-wide"><span>Observaciones IT</span><textarea name="observaciones" rows="3" placeholder="Equipo nuevo."></textarea></label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-close>Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Guardar equipo</button>
            </div>
        </form>
    </div>
</div>

<!-- Responsiva (solo visible al imprimir) -->
<div id="responsivaPrint" aria-hidden="true"></div>

<!-- Toast -->
<div class="toast" id="toast" role="status" aria-live="polite" hidden></div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/systems/inventoryit/inventory.js') }}"></script>
@endpush