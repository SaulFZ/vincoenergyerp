<!-- ========================================== -->
<!-- 1. FILTROS EXCLUSIVOS DE LA VISTA: COMPLIANCE -->
<!-- ========================================== -->
<section class="filter-section no-print">
    <form id="filter-form" action="{{ route('operations.sgc.index') }}" method="GET">
        <div class="filter-grid">

            <div class="filter-group">
                <label class="filter-label">Módulo</label>
                <select name="view" class="filter-input">
                    <option value="executive">Dashboard Ejecutivo</option>
                    <option value="npt">NPT / No Productivo</option>
                    <option value="compliance" selected>Cumplimiento SGC</option>
                    <option value="crew">Cuadrilla + Satisfacción</option>
                    <option value="shots">Disparos / MCR</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Compañía</label>
                <select name="client" class="filter-input">
                    <option value="TODOS" selected>Todas las compañías</option>
                    <option value="PEMEX">PEMEX</option>
                    <option value="SLB">SLB</option>
                    <option value="HALLIBURTON">HALLIBURTON</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Servicio</label>
                <select name="serviceType" class="filter-input">
                    <option value="TODOS" selected>Todos los tipos</option>
                    <option value="Registros Geofísicos">Registros Geofísicos</option>
                    <option value="Disparos de Producción">Disparos de Producción</option>
                </select>
            </div>

            <div class="filter-group custom-dropdown">
                <label class="filter-label">Años</label>
                <div class="filter-input dropdown-trigger" id="year-dropdown-btn">
                    <span>Seleccionar años</span>
                    <i class="fas fa-chevron-down text-slate-400"></i>
                </div>
                <div class="dropdown-menu" id="year-dropdown-menu">
                    <label class="dropdown-item"><input type="checkbox" name="year[]" value="2026" checked> 2026</label>
                    <label class="dropdown-item"><input type="checkbox" name="year[]" value="2025"> 2025</label>
                    <label class="dropdown-item"><input type="checkbox" name="year[]" value="2024"> 2024</label>
                </div>
            </div>

            <div class="filter-group">
                <label class="filter-label">Periodo</label>
                <select name="period" class="filter-input">
                    <option value="ANUAL" selected>Anual</option>
                    <option value="Q1">Q1 · Ene-Mar</option>
                    <option value="Q2">Q2 · Abr-Jun</option>
                    <option value="Q3">Q3 · Jul-Sep</option>
                    <option value="Q4">Q4 · Oct-Dic</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Mes comp.</label>
                <select name="compareMonth" class="filter-input">
                    <option value="TODOS" selected>Todos los meses</option>
                    <option value="ACTUAL">Mes actual vs años</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Tipo NPT</label>
                <select name="eventType" class="filter-input">
                    <option value="TODOS" selected>Todos</option>
                    <option value="Falla de herramienta">Falla de herramienta</option>
                    <option value="Falla de cable">Falla de cable</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Cálculo SQIR</label>
                <select name="sqirMode" class="filter-input">
                    <option value="os" selected>Por trabajos</option>
                    <option value="horas">Por horas</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label" style="opacity: 0;">Acciones</label>
                <div class="filter-actions">
                    <button type="button" onclick="window.print()" class="btn-icon btn-pdf" title="Exportar a PDF">
                        <i class="fas fa-file-pdf"></i> &nbsp;PDF
                    </button>
                    <a href="{{ route('operations.sgc.index') }}?view=compliance" class="btn-icon btn-clear" title="Limpiar filtros">
                        <i class="fas fa-undo-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </form>
</section>

<!-- ========================================== -->
<!-- 2. CONTENIDO DE LA VISTA                   -->
<!-- ========================================== -->
<div class="tab-compliance">

    <!-- ENCABEZADO CUMPLIMIENTO SGC -->
    <div class="panel panel-blue-header">
        <div class="module-header">
            <div>
                <p class="module-subtitle">Módulo de Calidad Operativa</p>
                <h2 class="module-title">Cumplimiento SGC Global</h2>
            </div>
            <p class="module-description">
                Evaluación del porcentaje de cumplimiento de las evidencias obligatorias por cada servicio operativo. Los suministros simples no suman al cálculo de este indicador.
            </p>
        </div>
    </div>

    <!-- KPIs CUMPLIMIENTO SGC -->
    <div class="kpi-grid">
        <div class="kpi-card kpi-rose">
            <p class="kpi-card-title">Cumplimiento SGC global</p>
            <p class="kpi-card-value">20.7%</p>
            <p class="kpi-card-note">0 servicios completos · 100 críticos</p>
        </div>
        <div class="kpi-card kpi-blue">
            <p class="kpi-card-title">Servicios SGC completos</p>
            <p class="kpi-card-value">0</p>
            <p class="kpi-card-note">90% o más</p>
        </div>
        <div class="kpi-card kpi-rose">
            <p class="kpi-card-title">Servicios SGC críticos</p>
            <p class="kpi-card-value">100</p>
            <p class="kpi-card-note">menor a 70%</p>
        </div>
        <div class="kpi-card kpi-amber">
            <p class="kpi-card-title">Pendientes SGC</p>
            <p class="kpi-card-value">1427</p>
            <p class="kpi-card-note">evidencias requeridas faltantes</p>
        </div>
        <div class="kpi-card kpi-white">
            <p class="kpi-card-title">Servicios operativos</p>
            <p class="kpi-card-value">100</p>
            <p class="kpi-card-note">base SGC sin suministros 2026</p>
        </div>
    </div>

    <!-- PANELES INFERIORES: PENDIENTES Y ALERTAS -->
    <div class="grid-2-cols">

        <!-- PENDIENTES SGC POR ETAPA -->
        <div class="panel">
            <h2 class="panel-title">Pendientes SGC por etapa</h2>
            <div class="chart-bar-item">
                <div class="chart-bar-labels"><strong>Planeación</strong> <strong>498 faltantes</strong></div>
                <div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 100%;"></div></div>
            </div>
            <div class="chart-bar-item">
                <div class="chart-bar-labels"><strong>Ejecución</strong> <strong>471 faltantes</strong></div>
                <div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 94.6%;"></div></div>
            </div>
            <div class="chart-bar-item">
                <div class="chart-bar-labels"><strong>Cierre</strong> <strong>458 faltantes</strong></div>
                <div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 92%;"></div></div>
            </div>
        </div>

        <!-- SERVICIOS CON MENOR CUMPLIMIENTO -->
        <div class="panel">
            <h2 class="panel-title">Servicios con menor cumplimiento SGC</h2>
            <div class="alert-list">
                <a class="alert-link-item" href="/servicios/482?tab=sgc">
                    <span class="alert-text">VES-MCR-OS-1381 · PEMEX · MACAVIL 1EXP (3 INTER)</span>
                    <span class="alert-value">0%</span>
                </a>
                <a class="alert-link-item" href="/servicios/480?tab=sgc">
                    <span class="alert-text">VES-MCR-OS-1379 · OPEX · XANAB 58A</span>
                    <span class="alert-value">0%</span>
                </a>
                <a class="alert-link-item" href="/servicios/479?tab=sgc">
                    <span class="alert-text">VES-MCR-OS-1378 · SIN CLIENTE · IXACHI 54</span>
                    <span class="alert-value">0%</span>
                </a>
                <a class="alert-link-item" href="/servicios/329?tab=sgc">
                    <span class="alert-text">VES-WL-OS-1226 · MEXXUS · BRICOL 1 (2 INTER)</span>
                    <span class="alert-value">0%</span>
                </a>
                <a class="alert-link-item" href="/servicios/325?tab=sgc">
                    <span class="alert-text">VES-WL-OS-1221 · GSM · TUPILCO 3012</span>
                    <span class="alert-value">0%</span>
                </a>
                <a class="alert-link-item" href="/servicios/322?tab=sgc">
                    <span class="alert-text">VES-WL-OS-1225 · PEMEX · TECHIAKTLI-1EXP</span>
                    <span class="alert-value">0%</span>
                </a>
                <a class="alert-link-item" href="/servicios/321?tab=sgc">
                    <span class="alert-text">VES-WL-OS-1224 · GSM · TUPILCO 143</span>
                    <span class="alert-value">0%</span>
                </a>
                <a class="alert-link-item" href="/servicios/320?tab=sgc">
                    <span class="alert-text">VES-WL-OS-1218 · MEXXUS · TOKAL 7 (2 INTER)</span>
                    <span class="alert-value">0%</span>
                </a>
                <a class="alert-link-item" href="/servicios/318?tab=sgc">
                    <span class="alert-text">VES-WL-OS-1217 · GSM · IXACHI 7</span>
                    <span class="alert-value">0%</span>
                </a>
                <a class="alert-link-item" href="/servicios/277?tab=sgc">
                    <span class="alert-text">VES-WL-OS-1576 · GSM · IXACHI 69</span>
                    <span class="alert-value">0%</span>
                </a>
            </div>
        </div>

    </div>
</div>
