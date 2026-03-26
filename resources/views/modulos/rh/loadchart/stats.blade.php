@extends('modulos.rh.loadchart.index')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    /* --- Variables Globales --- */
    :root {
        --primary-color: #34495e;
        --secondary-color: #2c3e50;
        --accent-color: #d67e29;
        --text-dark: #2d3748;
        --text-medium: #4a5568;
        --bg-light: #f8fafc;
        --white: #ffffff;
        --border-color: #e2e8f0;
        --success: #27ae60;
        --danger: #e74c3c;
    }

    body {
        font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        background-color: var(--bg-light);
        color: var(--text-dark);
        font-size: 0.9rem;
    }

    .dashboard-container {
        max-width: 100% !important;
        padding: 20px !important;
    }

    /* --- Selector de año + loading --- */
    .top-controls {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 18px;
        padding: 10px 15px;
        background: var(--white);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        flex-wrap: wrap;
    }
    .top-controls label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-medium);
        letter-spacing: .4px;
    }
    .top-controls select {
        padding: 6px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        color: var(--secondary-color);
        background: var(--white);
        cursor: pointer;
        height: 34px;
    }
    #loading-indicator {
        display: none;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: var(--text-medium);
    }
    #loading-indicator .spinner {
        width: 16px;
        height: 16px;
        border: 2px solid #e2e8f0;
        border-top-color: var(--accent-color);
        border-radius: 50%;
        animation: spin .8s linear infinite;
        display: inline-block;
    }
    #error-indicator {
        display: none;
        font-size: 12px;
        font-weight: 600;
        color: var(--danger);
        background: #fef2f2;
        border: 1px solid #fecaca;
        padding: 3px 10px;
        border-radius: 4px;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* --- Tabs --- */
    .data-tabs {
        display: flex;
        gap: 5px;
        margin-bottom: 20px;
        border-bottom: 2px solid var(--border-color);
        flex-wrap: wrap;
    }

    .tab-btn {
        padding: 12px 25px;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        font-weight: 600;
        color: var(--text-medium);
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
        margin-bottom: -2px;
    }

    .tab-btn:hover { color: var(--primary-color); background: rgba(0,0,0,0.02); }
    .tab-btn.active { color: var(--primary-color); border-bottom-color: var(--accent-color); }

    /* --- Sections & Cards --- */
    .table-section { display: none; animation: fadeIn 0.3s ease-out; }
    .table-section.active { display: block; }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

    .table-card {
        background: var(--white);
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        border: 1px solid var(--border-color);
        padding: 20px;
        margin-bottom: 20px;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--border-color);
    }

    .table-title { font-size: 1.25rem; font-weight: 700; color: var(--secondary-color); margin: 0; }
    .table-subtitle { font-size: 1rem; font-weight: 600; color: var(--primary-color); margin-bottom: 15px; border-left: 4px solid var(--accent-color); padding-left: 10px;}

    /* --- Filtros Dinámicos --- */
    .table-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        padding: 15px;
        background-color: #f1f5f9;
        border-radius: 8px;
        margin-bottom: 20px;
        align-items: flex-end;
        border: 1px solid var(--border-color);
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-width: 150px;
    }

    .filter-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-medium);
        margin-bottom: 6px;
    }

    .select-field, .input-field {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background-color: var(--white);
        font-size: 13px;
        color: var(--text-dark);
        outline: none;
        height: 37px;
    }
    .select-field:focus, .input-field:focus { border-color: var(--primary-color); }

    optgroup {
        font-weight: bold;
        color: var(--secondary-color);
    }

    /* --- Dropdown Checkboxes Custom --- */
    .multi-select-container { position: relative; width: 100%; user-select: none; }
    .multi-select-header {
        width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;
        background-color: var(--white); font-size: 13px; color: var(--text-dark);
        cursor: pointer; display: flex; justify-content: space-between; align-items: center;
        height: 37px; box-sizing: border-box;
    }
    .multi-select-header:hover { border-color: #94a3b8; }
    .multi-select-options {
        display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0;
        background: var(--white); border: 1px solid #cbd5e1; border-radius: 6px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 100; max-height: 250px;
        overflow-y: auto; padding: 5px;
    }
    .multi-select-options.show { display: block; }
    .multi-select-options label {
        display: flex; align-items: center; gap: 8px; padding: 6px 10px;
        cursor: pointer; font-size: 13px; border-radius: 4px; transition: background 0.2s;
    }
    .multi-select-options label:hover { background: #f1f5f9; }
    .multi-select-options input[type="checkbox"] { cursor: pointer; }

    /* --- Buttons --- */
    .btn {
        padding: 9px 18px;
        border-radius: 6px;
        border: none;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: transform 0.1s, opacity 0.2s;
        height: 37px;
    }

    .btn-primary { background-color: var(--primary-color); color: var(--white); }
    .btn:hover { opacity: 0.9; }

    /* --- Table Styling --- */
    .table-container {
        overflow-x: auto;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        max-height: 500px;
        overflow-y: auto;
        margin-bottom: 20px;
    }

    .data-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: var(--white);
        font-size: 0.85rem;
    }

    .data-table th {
        background-color: var(--primary-color);
        color: var(--white);
        font-weight: 600;
        padding: 10px 15px;
        text-align: center;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 10;
        border-bottom: 2px solid var(--secondary-color);
        border-right: 1px solid rgba(255,255,255,0.1);
    }

    .data-table .sub-header th {
        background-color: #4a6583;
        top: 39px;
        font-size: 0.75rem;
    }

    .data-table td {
        padding: 8px 15px;
        border-bottom: 1px solid var(--border-color);
        border-right: 1px solid var(--border-color);
        vertical-align: middle;
        color: var(--text-dark);
        white-space: nowrap;
    }

    .sticky-col {
        position: sticky;
        left: 0;
        background-color: var(--white);
        z-index: 5;
        box-shadow: 2px 0 5px -2px rgba(0,0,0,0.1);
        font-weight: 600;
    }

    .data-table th.sticky-col { z-index: 15; background-color: var(--primary-color); }
    .data-table tr:hover td { background-color: #f8fafc; }

    .text-center { text-align: center !important; }
    .text-right { text-align: right !important; }
    .text-left { text-align: left !important; }

    .currency-mxn { font-family: 'Roboto Mono', monospace; font-weight: 600; color: var(--secondary-color); }
    .currency-usd { font-family: 'Roboto Mono', monospace; font-weight: 600; color: #27ae60; }
    .percentage-positive { color: var(--success); font-weight: 700; }
    .percentage-negative { color: var(--danger); font-weight: 700; }

    .total-col { background-color: #f1f5f9; font-weight: 700; }

    .total-row td {
        background-color: #edf2f7 !important;
        font-weight: 700;
        border-top: 2px solid var(--secondary-color);
        position: sticky;
        bottom: 0;
        z-index: 5;
    }

    .badge-area {
        background-color: #e2e8f0;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-medium);
    }

  /* ⭐ ESTILOS DE COMISIONES Y TOOLTIPS */
    .has-commissioned {
        background-color: rgba(36, 147, 153, 0.08) !important; /* Fondo muy tenue basado en tu color */
        border-left: 3px solid var(--commissioned) !important;
    }
    .has-commissioned:hover {
        background-color: rgba(36, 147, 153, 0.18) !important; /* Fondo un poco más oscuro al pasar el mouse */
    }
    .commissioned-icon {
        color: var(--commissioned); /* Usa exactamente el color verde/turquesa de tu leyenda */
        margin-right: 4px;
        cursor: help;
        font-size: 14px;
    }

    .dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start; }
    .grid-column { display: flex; flex-direction: column; }
    .grid-full-width { grid-column: 1 / -1; }
    .chart-container { position: relative; width: 100%; padding: 15px; background: var(--white); border: 1px solid var(--border-color); border-radius: 6px; }

    .table-container::-webkit-scrollbar { width: 6px; height: 6px; }
    .table-container::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
    .table-container::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 4px; }

    @media (max-width: 1024px) { .dashboard-grid { grid-template-columns: 1fr; } }

    .empty-row td { text-align: center; padding: 24px; color: var(--text-medium); font-style: italic; }
</style>

<main class="dashboard-container">

    <div class="top-controls">
        <label for="global-year">Año</label>
        <select id="global-year">
            <option value="2026" selected>2026</option>
            <option value="2025">2025</option>
            <option value="2024">2024</option>
        </select>
        <span id="loading-indicator">
            <span class="spinner"></span> Cargando datos…
        </span>
        <span id="error-indicator"></span>
    </div>

    <div class="data-tabs">
        <button class="tab-btn active" data-tab="bonos-empleados">1. Bonos por Empleado</button>
        <button class="tab-btn" data-tab="resumen-areas">2. Resumen por Área</button>
        <button class="tab-btn" data-tab="control-pozos">3. Control de Pozos</button>
        <button class="tab-btn" data-tab="evolucion-estadisticas">4. Evolución y Estadísticas</button>
    </div>

    {{-- ─── TAB 1 ──────────────────────────────────────────────────── --}}
    <section id="bonos-empleados" class="table-section active">
        <div class="table-card">
            <div class="table-header">
                <h2 class="table-title">Reporte de Bonos por Empleado</h2>
                <button class="btn btn-primary" onclick="exportTable('empleados-table', 'Bonos_Empleados')">
                    <i class="fas fa-download"></i> Exportar a Excel
                </button>
            </div>

            <div class="table-filters">
                <div class="filter-group">
                    <label class="filter-label">Periodo Rápido</label>
                    <select id="emp-trimestre-filter" class="select-field">
                        <option value="Personalizado">Personalizado</option>
                        <optgroup label="Trimestral">
                            <option value="Q1" selected>Trimestre 1 (Ene - Mar)</option>
                            <option value="Q2">Trimestre 2 (Abr - Jun)</option>
                            <option value="Q3">Trimestre 3 (Jul - Sep)</option>
                            <option value="Q4">Trimestre 4 (Oct - Dic)</option>
                        </optgroup>
                        <optgroup label="Semestral / Anual">
                            <option value="H1">Semestre 1 (Ene - Jun)</option>
                            <option value="H2">Semestre 2 (Jul - Dic)</option>
                            <option value="Anual">Anual Completo</option>
                        </optgroup>
                    </select>
                </div>
                <div class="filter-group" style="min-width: 200px;">
                    <label class="filter-label">Meses a Visualizar</label>
                    <div class="multi-select-container">
                        <div class="multi-select-header" onclick="toggleDropdown('emp-meses-dropdown', event)">
                            <span id="emp-meses-text">Ene, Feb, Mar</span>
                            <span>&#9662;</span>
                        </div>
                        <div class="multi-select-options" id="emp-meses-dropdown">
                            <label><input type="checkbox" value="Enero"      class="emp-mes-chk" checked> Enero</label>
                            <label><input type="checkbox" value="Febrero"    class="emp-mes-chk" checked> Febrero</label>
                            <label><input type="checkbox" value="Marzo"      class="emp-mes-chk" checked> Marzo</label>
                            <label><input type="checkbox" value="Abril"      class="emp-mes-chk"> Abril</label>
                            <label><input type="checkbox" value="Mayo"       class="emp-mes-chk"> Mayo</label>
                            <label><input type="checkbox" value="Junio"      class="emp-mes-chk"> Junio</label>
                            <label><input type="checkbox" value="Julio"      class="emp-mes-chk"> Julio</label>
                            <label><input type="checkbox" value="Agosto"     class="emp-mes-chk"> Agosto</label>
                            <label><input type="checkbox" value="Septiembre" class="emp-mes-chk"> Septiembre</label>
                            <label><input type="checkbox" value="Octubre"    class="emp-mes-chk"> Octubre</label>
                            <label><input type="checkbox" value="Noviembre"  class="emp-mes-chk"> Noviembre</label>
                            <label><input type="checkbox" value="Diciembre"  class="emp-mes-chk"> Diciembre</label>
                        </div>
                    </div>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Área</label>
                    <select id="emp-area-filter" class="select-field">
                        <option value="TODAS">Todas las Áreas</option>
                        <option value="OPERACIONES">Operaciones</option>
                        <option value="SUMINISTROS">Suministros</option>
                        <option value="ADMINISTRACIÓN Y COMPRAS">Administración y Compras</option>
                        <option value="QHSE">QHSE</option>
                        <option value="GEOCIENCIAS">Geociencias</option>
                        <option value="VENTAS">Ventas</option>
                        <option value="LABORATORIO">Laboratorio</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Buscar Empleado</label>
                    <input type="text" id="emp-search-filter" class="input-field" placeholder="Nombre o Clave...">
                </div>
            </div>

            <div class="table-container">
                <table id="empleados-table" class="data-table" style="min-width: 1200px;">
                    <thead></thead>
                    <tbody id="empleados-tbody"><tr class="empty-row"><td colspan="20">Cargando datos del servidor…</td></tr></tbody>
                    <tfoot id="empleados-tfoot"></tfoot>
                </table>
            </div>
        </div>
    </section>

    {{-- ─── TAB 2 ──────────────────────────────────────────────────── --}}
    <section id="resumen-areas" class="table-section">
        <div class="table-card">
            <div class="table-header">
                <h2 class="table-title">Resumen de Bonos por Área</h2>
                <button class="btn btn-primary" onclick="exportTable('areas-table', 'Resumen_Areas')">
                    <i class="fas fa-download"></i> Exportar a Excel
                </button>
            </div>

            <div class="table-filters">
                <div class="filter-group">
                    <label class="filter-label">Periodo Rápido</label>
                    <select id="area-trimestre-filter" class="select-field">
                        <option value="Personalizado">Personalizado</option>
                        <optgroup label="Trimestral">
                            <option value="Q1">Trimestre 1 (Ene - Mar)</option>
                            <option value="Q2">Trimestre 2 (Abr - Jun)</option>
                            <option value="Q3">Trimestre 3 (Jul - Sep)</option>
                            <option value="Q4">Trimestre 4 (Oct - Dic)</option>
                        </optgroup>
                        <optgroup label="Semestral / Anual">
                            <option value="H1" selected>Semestre 1 (Ene - Jun)</option>
                            <option value="H2">Semestre 2 (Jul - Dic)</option>
                            <option value="Anual">Anual Completo</option>
                        </optgroup>
                    </select>
                </div>
                <div class="filter-group" style="min-width: 200px;">
                    <label class="filter-label">Meses a Visualizar</label>
                    <div class="multi-select-container">
                        <div class="multi-select-header" onclick="toggleDropdown('area-meses-dropdown', event)">
                            <span id="area-meses-text">6 meses selec.</span>
                            <span>&#9662;</span>
                        </div>
                        <div class="multi-select-options" id="area-meses-dropdown">
                            <label><input type="checkbox" value="Enero"      class="area-mes-chk" checked> Enero</label>
                            <label><input type="checkbox" value="Febrero"    class="area-mes-chk" checked> Febrero</label>
                            <label><input type="checkbox" value="Marzo"      class="area-mes-chk" checked> Marzo</label>
                            <label><input type="checkbox" value="Abril"      class="area-mes-chk" checked> Abril</label>
                            <label><input type="checkbox" value="Mayo"       class="area-mes-chk" checked> Mayo</label>
                            <label><input type="checkbox" value="Junio"      class="area-mes-chk" checked> Junio</label>
                            <label><input type="checkbox" value="Julio"      class="area-mes-chk"> Julio</label>
                            <label><input type="checkbox" value="Agosto"     class="area-mes-chk"> Agosto</label>
                            <label><input type="checkbox" value="Septiembre" class="area-mes-chk"> Septiembre</label>
                            <label><input type="checkbox" value="Octubre"    class="area-mes-chk"> Octubre</label>
                            <label><input type="checkbox" value="Noviembre"  class="area-mes-chk"> Noviembre</label>
                            <label><input type="checkbox" value="Diciembre"  class="area-mes-chk"> Diciembre</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table id="areas-table" class="data-table" style="min-width: 1000px;">
                    <thead></thead>
                    <tbody id="areas-tbody"><tr class="empty-row"><td colspan="20">Cargando…</td></tr></tbody>
                    <tfoot id="areas-tfoot"></tfoot>
                </table>
            </div>
        </div>
    </section>

    {{-- ─── TAB 3 ──────────────────────────────────────────────────── --}}
    <section id="control-pozos" class="table-section">
        <div class="table-card">
            <div class="table-header">
                <h2 class="table-title">Control de Pozos Pagados Quincenalmente</h2>
                <button class="btn btn-primary" onclick="exportTable('pozos-table', 'Control_Pozos')">
                    <i class="fas fa-download"></i> Exportar a Excel
                </button>
            </div>

            <div class="table-filters">
                <div class="filter-group">
                    <label class="filter-label">Periodo Rápido</label>
                    <select id="pozo-trimestre-filter" class="select-field">
                        <option value="Personalizado">Personalizado</option>
                        <optgroup label="Trimestral">
                            <option value="Q1" selected>Trimestre 1 (Ene - Mar)</option>
                            <option value="Q2">Trimestre 2 (Abr - Jun)</option>
                            <option value="Q3">Trimestre 3 (Jul - Sep)</option>
                            <option value="Q4">Trimestre 4 (Oct - Dic)</option>
                        </optgroup>
                        <optgroup label="Semestral / Anual">
                            <option value="H1">Semestre 1 (Ene - Jun)</option>
                            <option value="H2">Semestre 2 (Jul - Dic)</option>
                            <option value="Anual">Anual Completo</option>
                        </optgroup>
                    </select>
                </div>
                <div class="filter-group" style="min-width: 200px;">
                    <label class="filter-label">Meses a Visualizar</label>
                    <div class="multi-select-container">
                        <div class="multi-select-header" onclick="toggleDropdown('pozo-meses-dropdown', event)">
                            <span id="pozo-meses-text">Ene, Feb, Mar</span>
                            <span>&#9662;</span>
                        </div>
                        <div class="multi-select-options" id="pozo-meses-dropdown">
                            <label><input type="checkbox" value="Enero"      class="pozo-mes-chk" checked> Enero</label>
                            <label><input type="checkbox" value="Febrero"    class="pozo-mes-chk" checked> Febrero</label>
                            <label><input type="checkbox" value="Marzo"      class="pozo-mes-chk" checked> Marzo</label>
                            <label><input type="checkbox" value="Abril"      class="pozo-mes-chk"> Abril</label>
                            <label><input type="checkbox" value="Mayo"       class="pozo-mes-chk"> Mayo</label>
                            <label><input type="checkbox" value="Junio"      class="pozo-mes-chk"> Junio</label>
                            <label><input type="checkbox" value="Julio"      class="pozo-mes-chk"> Julio</label>
                            <label><input type="checkbox" value="Agosto"     class="pozo-mes-chk"> Agosto</label>
                            <label><input type="checkbox" value="Septiembre" class="pozo-mes-chk"> Septiembre</label>
                            <label><input type="checkbox" value="Octubre"    class="pozo-mes-chk"> Octubre</label>
                            <label><input type="checkbox" value="Noviembre"  class="pozo-mes-chk"> Noviembre</label>
                            <label><input type="checkbox" value="Diciembre"  class="pozo-mes-chk"> Diciembre</label>
                        </div>
                    </div>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Buscar Pozo</label>
                    <input type="text" id="pozo-search-filter" class="input-field" placeholder="Nombre del pozo...">
                </div>
            </div>

            <div class="table-container">
                <table id="pozos-table" class="data-table" style="min-width: 1000px;">
                    <thead></thead>
                    <tbody id="pozos-tbody"><tr class="empty-row"><td colspan="20">Cargando…</td></tr></tbody>
                    <tfoot id="pozos-tfoot"></tfoot>
                </table>
            </div>
        </div>
    </section>

    {{-- ─── TAB 4 ──────────────────────────────────────────────────── --}}
    <section id="evolucion-estadisticas" class="table-section">
        <div class="table-card">
            <div class="table-header">
                <h2 class="table-title">Evolución, Estadísticas y Proyecciones — <span id="stats-year-label">2026</span></h2>
            </div>
            <div class="dashboard-grid">
                <div class="grid-column">
                    <h3 class="table-subtitle">BONOS POR QUINCENA</h3>
                    <div class="table-container" style="max-height: 400px; margin-bottom: 20px;">
                        <table id="stat-quincena-table" class="data-table" style="min-width: 100%;">
                            <thead>
                                <tr>
                                    <th class="text-center">Quincena</th>
                                    <th class="text-right">Bonos</th>
                                    <th class="text-center">Servicios</th>
                                    <th class="text-center">Suministros</th>
                                </tr>
                            </thead>
                            <tbody id="stat-quincena-tbody"></tbody>
                        </table>
                    </div>

                    <h3 class="table-subtitle">EVOLUCIÓN MENSUAL (MXN)</h3>
                    <div class="chart-container" style="height: 350px;">
                        <canvas id="evolucionChart"></canvas>
                    </div>
                </div>

                <div class="grid-column">
                    <h3 class="table-subtitle">RENDIMIENTO OPERATIVO (SERVICIOS Y SUMINISTROS)</h3>
                    <div class="table-container" style="max-height: 180px; margin-bottom: 20px;">
                        <table id="stat-rendimiento-table" class="data-table" style="min-width: 100%;">
                            <thead>
                                <tr>
                                    <th class="text-center">Quincena</th>
                                    <th class="text-right">Bonos MXN</th>
                                    <th class="text-center">Servicios</th>
                                    <th class="text-center">Suministros</th>
                                </tr>
                            </thead>
                            <tbody id="stat-rendimiento-tbody"></tbody>
                        </table>
                    </div>

                    <h3 class="table-subtitle">DESGLOSE MENSUAL</h3>
                    <div class="dashboard-grid" style="gap: 15px;">
                        <div>
                            <h4 style="font-size: 0.85rem; color: var(--text-medium); margin-bottom: 8px; text-align: center;">(MXN)</h4>
                            <div class="table-container" style="max-height: 535px; margin-bottom: 0;">
                                <table id="stat-mes-mxn-table" class="data-table" style="min-width: 100%;">
                                    <thead>
                                        <tr>
                                            <th class="text-left">Mes</th>
                                            <th class="text-right">Bonos</th>
                                        </tr>
                                    </thead>
                                    <tbody id="stat-mes-mxn-tbody"></tbody>
                                </table>
                            </div>
                        </div>
                        <div>
                            <h4 style="font-size: 0.85rem; color: var(--text-medium); margin-bottom: 8px; text-align: center;">(USD — referencial)</h4>
                            <div class="table-container" style="max-height: 535px; margin-bottom: 0;">
                                <table id="stat-mes-usd-table" class="data-table" style="min-width: 100%;">
                                    <thead>
                                        <tr>
                                            <th class="text-left">Mes</th>
                                            <th class="text-right">Bonos</th>
                                        </tr>
                                    </thead>
                                    <tbody id="stat-mes-usd-tbody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid-full-width" style="margin-top: 10px;">
                    <h3 class="table-subtitle">ANÁLISIS DE CRECIMIENTO ENTRE PERIODOS</h3>
                    <div class="table-container" style="margin-bottom: 0;">
                        <table id="stat-diferencia-table" class="data-table" style="min-width: 100%;">
                            <thead>
                                <tr>
                                    <th class="text-center">Periodo</th>
                                    <th class="text-left">Quincena</th>
                                    <th class="text-center">Servicios</th>
                                    <th class="text-center">Suministros</th>
                                    <th class="text-right">Bonos MXN</th>
                                    <th class="text-center">% Diferencia</th>
                                </tr>
                            </thead>
                            <tbody id="stat-diferencia-tbody"></tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </section>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ============================================================================
    // CONFIGURACIÓN
    // ============================================================================
    const STATS_URL = '{{ route("loadchart.stats.data") }}';

    const periodosMap = {
        'Q1':    ['Enero','Febrero','Marzo'],
        'Q2':    ['Abril','Mayo','Junio'],
        'Q3':    ['Julio','Agosto','Septiembre'],
        'Q4':    ['Octubre','Noviembre','Diciembre'],
        'H1':    ['Enero','Febrero','Marzo','Abril','Mayo','Junio'],
        'H2':    ['Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
        'Anual': ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']
    };

    const monthKeysMap = {
        'Enero':['ene1','ene2'], 'Febrero':['feb1','feb2'], 'Marzo':['mar1','mar2'],
        'Abril':['abr1','abr2'], 'Mayo':['may1','may2'], 'Junio':['jun1','jun2'],
        'Julio':['jul1','jul2'], 'Agosto':['ago1','ago2'], 'Septiembre':['sep1','sep2'],
        'Octubre':['oct1','oct2'], 'Noviembre':['nov1','nov2'], 'Diciembre':['dic1','dic2']
    };

    // ============================================================================
    // ESTADO GLOBAL
    // ============================================================================
    let empleadosData = [];
    let areasData     = []; // ⭐ NUEVA VARIABLE PARA LOS COMISIONADOS
    let pozosData     = [];
    let quincenasData = [];
    let mesData       = [];
    let evChart       = null;

    // ============================================================================
    // HELPERS DE FORMATO
    // ============================================================================

    // ⭐ AHORA SE ELIMINAN LOS .00 EN TODA LA VISTA
    function formatMxn(amount) {
        if (amount === null || amount === undefined || amount === '') return '';
        const num = parseFloat(amount);
        if (isNaN(num)) return amount;
        const formatted = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        }).format(num);
        return `$${formatted}`;
    }

    function formatUsd(amount) {
        if (amount === null || amount === undefined || amount === '') return '';
        const num = parseFloat(amount);
        if (isNaN(num)) return amount;
        const formatted = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        }).format(num);
        return `$${formatted}`;
    }

    // ============================================================================
    // CARGA AJAX
    // ============================================================================
    async function loadData(year) {
        document.getElementById('loading-indicator').style.display = 'inline-flex';
        document.getElementById('error-indicator').style.display   = 'none';
        document.getElementById('stats-year-label').textContent    = year;

        try {
            const r = await fetch(`${STATS_URL}?year=${year}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!r.ok) throw new Error(`HTTP ${r.status}`);

            const j = await r.json();
            if (!j.success) throw new Error(j.message || 'Error del servidor');

            empleadosData = j.empleadosData || [];
            areasData     = j.areasData     || []; // ⭐ SE RECIBEN LAS ÁREAS DEL BACKEND
            pozosData     = j.pozosData     || [];
            quincenasData = j.quincenasData || [];
            mesData       = j.mesData       || [];

            renderEmpleados();
            renderAreas();
            renderPozos();
            renderEstadisticas();

        } catch (e) {
            console.error(e);
            const el = document.getElementById('error-indicator');
            el.textContent = '⚠ ' + e.message;
            el.style.display = 'inline-block';
        } finally {
            document.getElementById('loading-indicator').style.display = 'none';
        }
    }

    // ============================================================================
    // FUNCIONES DE CONTROL DE FILTROS
    // ============================================================================
    window.toggleDropdown = function (id, event) {
        event.stopPropagation();
        document.getElementById(id).classList.toggle('show');
    };

    window.onclick = function (event) {
        if (!event.target.closest('.multi-select-container')) {
            document.querySelectorAll('.multi-select-options.show')
                    .forEach(el => el.classList.remove('show'));
        }
    };

    function updateDropdownText(prefix) {
        const checkboxes = document.querySelectorAll(`.${prefix}-mes-chk:checked`);
        const textSpan   = document.getElementById(`${prefix}-meses-text`);

        if (checkboxes.length === 0) {
            textSpan.textContent = 'Ningún mes';
        } else if (checkboxes.length === 12) {
            textSpan.textContent = 'Todos los meses';
        } else if (checkboxes.length <= 3) {
            textSpan.textContent = Array.from(checkboxes).map(cb => cb.value.substring(0,3)).join(', ');
        } else {
            textSpan.textContent = `${checkboxes.length} meses selec.`;
        }
    }

    function handleTrimestreChange(prefix) {
        const selectVal = document.getElementById(`${prefix}-trimestre-filter`).value;
        if (selectVal !== 'Personalizado') {
            const meses = periodosMap[selectVal] || [];
            document.querySelectorAll(`.${prefix}-mes-chk`).forEach(cb => {
                cb.checked = meses.includes(cb.value);
            });
            updateDropdownText(prefix);
            if (prefix === 'emp')  renderEmpleados();
            if (prefix === 'area') renderAreas();
            if (prefix === 'pozo') renderPozos();
        }
    }

    function handleMesCheckboxChange(prefix) {
        document.getElementById(`${prefix}-trimestre-filter`).value = 'Personalizado';
        updateDropdownText(prefix);
        if (prefix === 'emp')  renderEmpleados();
        if (prefix === 'area') renderAreas();
        if (prefix === 'pozo') renderPozos();
    }

    document.getElementById('emp-trimestre-filter').addEventListener('change', () => handleTrimestreChange('emp'));
    document.querySelectorAll('.emp-mes-chk').forEach(cb => cb.addEventListener('change', () => handleMesCheckboxChange('emp')));

    document.getElementById('area-trimestre-filter').addEventListener('change', () => handleTrimestreChange('area'));
    document.querySelectorAll('.area-mes-chk').forEach(cb => cb.addEventListener('change', () => handleMesCheckboxChange('area')));

    document.getElementById('pozo-trimestre-filter').addEventListener('change', () => handleTrimestreChange('pozo'));
    document.querySelectorAll('.pozo-mes-chk').forEach(cb => cb.addEventListener('change', () => handleMesCheckboxChange('pozo')));

    // ============================================================================
    // 3. RENDERIZADO — EMPLEADOS
    // ============================================================================
    function renderEmpleados() {
        const filterArea   = document.getElementById('emp-area-filter').value;
        const filterSearch = document.getElementById('emp-search-filter').value.toLowerCase();
        const mesesMostrar = Array.from(document.querySelectorAll('.emp-mes-chk:checked')).map(cb => cb.value);

        const thead = document.querySelector('#empleados-table thead');
        const tbody = document.getElementById('empleados-tbody');
        const tfoot = document.getElementById('empleados-tfoot');

        if (mesesMostrar.length === 0) {
            thead.innerHTML = '<tr><th>No hay meses seleccionados</th></tr>';
            tbody.innerHTML = '<tr class="empty-row"><td colspan="20">Selecciona al menos un mes en los filtros.</td></tr>';
            tfoot.innerHTML = '';
            return;
        }

        let theadHTML1 = `<tr>
            <th rowspan="2" class="sticky-col text-left">CLAVE</th>
            <th rowspan="2" class="sticky-col text-left" style="left: 80px;">NOMBRE COMPLETO</th>
            <th rowspan="2" class="text-left">ÁREA</th>`;
        let theadHTML2 = `<tr class="sub-header">`;

        mesesMostrar.forEach(mes => {
            theadHTML1 += `<th colspan="3">${mes.toUpperCase()}</th>`;
            theadHTML2 += `<th>1RA QUINCENA</th><th>2DA QUINCENA</th><th>TOTAL ${mes.substring(0,3).toUpperCase()}</th>`;
        });
        theadHTML1 += `<th rowspan="2">GRAN TOTAL</th></tr>`;
        theadHTML2 += `</tr>`;
        thead.innerHTML = theadHTML1 + theadHTML2;

        tbody.innerHTML = '';
        let columnTotals = new Array(mesesMostrar.length * 3 + 1).fill(0);

        const empSorted = [...empleadosData].sort((a, b) => {
            const totA = mesesMostrar.reduce((s, m) => s + (a[monthKeysMap[m][0]]||0) + (a[monthKeysMap[m][1]]||0), 0);
            const totB = mesesMostrar.reduce((s, m) => s + (b[monthKeysMap[m][0]]||0) + (b[monthKeysMap[m][1]]||0), 0);
            return totB - totA;
        });

        empSorted.forEach(emp => {
            const matchArea   = filterArea === 'TODAS' || emp.area === filterArea;
            const matchSearch = emp.nombre.toLowerCase().includes(filterSearch) ||
                                emp.clave.toLowerCase().includes(filterSearch);

            if (matchArea && matchSearch) {
                const tr = document.createElement('tr');
                let rowHTML = `
                    <td class="sticky-col text-left">${emp.clave}</td>
                    <td class="sticky-col text-left" style="left: 80px;">${emp.nombre}</td>
                    <td class="text-left"><span class="badge-area">${emp.area}</span></td>`;

                let granTotalEmp = 0;

                mesesMostrar.forEach((mes, idx) => {
                    const val1    = emp[monthKeysMap[mes][0]] || 0;
                    const val2    = emp[monthKeysMap[mes][1]] || 0;
                    const totalM  = val1 + val2;
                    granTotalEmp += totalM;
                    columnTotals[idx * 3]     += val1;
                    columnTotals[idx * 3 + 1] += val2;
                    columnTotals[idx * 3 + 2] += totalM;

                    rowHTML += `
                        <td class="text-right currency-mxn">${val1 > 0 ? formatMxn(val1) : '-'}</td>
                        <td class="text-right currency-mxn">${val2 > 0 ? formatMxn(val2) : '-'}</td>
                        <td class="text-right currency-mxn total-col">${totalM > 0 ? formatMxn(totalM) : '-'}</td>`;
                });

                columnTotals[columnTotals.length - 1] += granTotalEmp;
                rowHTML += `<td class="text-right currency-mxn" style="background-color: #e2e8f0;">${granTotalEmp > 0 ? formatMxn(granTotalEmp) : '-'}</td>`;
                tr.innerHTML = rowHTML;
                tbody.appendChild(tr);
            }
        });

        if (!tbody.children.length) {
            tbody.innerHTML = `<tr class="empty-row"><td colspan="${mesesMostrar.length*3+4}">Sin resultados para los filtros aplicados.</td></tr>`;
        }

        let tfootHTML = `<tr class="total-row"><td colspan="3" class="text-right sticky-col" style="left: 0;">TOTALES GENERALES</td>`;
        columnTotals.forEach((tot, idx) => {
            const isFinal = idx === columnTotals.length - 1;
            const style   = isFinal ? 'color: var(--primary-color); font-size: 1.1em;' : '';
            tfootHTML += `<td class="text-right currency-mxn" style="${style}">${formatMxn(tot)}</td>`;
        });
        tfoot.innerHTML = tfootHTML + `</tr>`;
    }

// ============================================================================
    // 4. RENDERIZADO — ÁREAS ⭐ ACTUALIZADO PARA MOSTRAR ORIGEN DEL COMISIONADO
    // ============================================================================
    function renderAreas() {
        const mesesMostrar = Array.from(document.querySelectorAll('.area-mes-chk:checked')).map(cb => cb.value);
        const thead = document.querySelector('#areas-table thead');
        const tbody = document.getElementById('areas-tbody');
        const tfoot = document.getElementById('areas-tfoot');

        if (mesesMostrar.length === 0) {
            thead.innerHTML = '<tr><th>No hay meses seleccionados</th></tr>';
            tbody.innerHTML = '<tr class="empty-row"><td colspan="20">Selecciona al menos un mes en los filtros.</td></tr>';
            tfoot.innerHTML = '';
            return;
        }

        let theadHTML1 = `<tr><th rowspan="2" class="sticky-col text-left">ÁREA</th>`;
        let theadHTML2 = `<tr class="sub-header">`;
        mesesMostrar.forEach(mes => {
            theadHTML1 += `<th colspan="3">${mes.toUpperCase()}</th>`;
            theadHTML2 += `<th>1RA QUINCENA</th><th>2DA QUINCENA</th><th>TOTAL ${mes.substring(0,3).toUpperCase()}</th>`;
        });
        theadHTML1 += `<th rowspan="2">GRAN TOTAL</th></tr>`;
        theadHTML2 += `</tr>`;
        thead.innerHTML = theadHTML1 + theadHTML2;

        tbody.innerHTML = '';
        let columnTotals = new Array(mesesMostrar.length * 3 + 1).fill(0);

        const areasSorted = [...areasData].sort((a, b) => {
            const totA = mesesMostrar.reduce((s, m) => {
                const q1 = a[monthKeysMap[m][0]];
                const q2 = a[monthKeysMap[m][1]];
                return s + (q1 ? q1.normal + q1.comisionado : 0) + (q2 ? q2.normal + q2.comisionado : 0);
            }, 0);
            const totB = mesesMostrar.reduce((s, m) => {
                const q1 = b[monthKeysMap[m][0]];
                const q2 = b[monthKeysMap[m][1]];
                return s + (q1 ? q1.normal + q1.comisionado : 0) + (q2 ? q2.normal + q2.comisionado : 0);
            }, 0);
            return totB - totA;
        });

        areasSorted.forEach(areaObj => {
            const tr = document.createElement('tr');
            let rowHTML = `<td class="sticky-col text-left"><strong>${areaObj.area}</strong></td>`;
            let granTotalArea = 0;

            mesesMostrar.forEach((mes, idx) => {
                const k1 = monthKeysMap[mes][0];
                const k2 = monthKeysMap[mes][1];

                const q1Data = areaObj[k1] || { normal: 0, comisionado: 0, comisionado_fuentes: {} };
                const q2Data = areaObj[k2] || { normal: 0, comisionado: 0, comisionado_fuentes: {} };

                const val1 = q1Data.normal + q1Data.comisionado;
                const val2 = q2Data.normal + q2Data.comisionado;
                const totalM = val1 + val2;

                granTotalArea += totalM;
                columnTotals[idx * 3] += val1;
                columnTotals[idx * 3 + 1] += val2;
                columnTotals[idx * 3 + 2] += totalM;

                rowHTML += generateAreaCellHtml(val1, q1Data);
                rowHTML += generateAreaCellHtml(val2, q2Data);

                // Combinar los orígenes para el total mensual
                const mData = {
                    normal: q1Data.normal + q2Data.normal,
                    comisionado: q1Data.comisionado + q2Data.comisionado,
                    comisionado_fuentes: {}
                };

                [q1Data.comisionado_fuentes, q2Data.comisionado_fuentes].forEach(fuentes => {
                    if (fuentes) {
                        for (const [areaName, amt] of Object.entries(fuentes)) {
                            mData.comisionado_fuentes[areaName] = (mData.comisionado_fuentes[areaName] || 0) + amt;
                        }
                    }
                });

                rowHTML += generateAreaCellHtml(totalM, mData, true);
            });

            columnTotals[columnTotals.length - 1] += granTotalArea;
            rowHTML += `<td class="text-right currency-mxn" style="background-color: #e2e8f0;">${granTotalArea > 0 ? formatMxn(granTotalArea) : '-'}</td>`;
            tr.innerHTML = rowHTML;
            tbody.appendChild(tr);
        });

        let tfootHTML = `<tr class="total-row"><td class="text-right sticky-col">TOTALES</td>`;
        columnTotals.forEach((tot, idx) => {
            const isFinal = idx === columnTotals.length - 1;
            const style   = isFinal ? 'color: var(--primary-color); font-size: 1.1em;' : '';
            tfootHTML += `<td class="text-right currency-mxn" style="${style}">${formatMxn(tot)}</td>`;
        });
        tfoot.innerHTML = tfootHTML + `</tr>`;
    }

// ⭐ HELPER PARA CREAR LA CELDA Y EL TOOLTIP DETALLADO
    function generateAreaCellHtml(total, data, isTotalMonth = false) {
        if (total === 0) return `<td class="text-right currency-mxn ${isTotalMonth ? 'total-col' : ''}">-</td>`;

        let extraClass = '';
        let tooltip = '';
        let icon = '';

        if (data.comisionado > 0) {
            extraClass = 'has-commissioned';

            // Construir el mensaje del Tooltip
            tooltip = `Monto de esta Área: ${formatMxn(data.normal)}\nMonto Comisionado: ${formatMxn(data.comisionado)}\n\nDESGLOSE DE COMISIONADOS:`;

            if (data.comisionado_fuentes) {
                // Iteramos sobre los nombres de empleados y sus montos
                for (const [empleadoOrigen, monto] of Object.entries(data.comisionado_fuentes)) {
                    tooltip += `\n• ${empleadoOrigen} = ${formatMxn(monto)}`;
                }
            }

            icon = `<i class="fas fa-info-circle commissioned-icon" title="${tooltip}"></i> `;
        }

        return `<td class="text-right currency-mxn ${isTotalMonth ? 'total-col' : ''} ${extraClass}" title="${tooltip}">
                    ${icon}${formatMxn(total)}
                </td>`;
    }

    // ============================================================================
    // 5. RENDERIZADO — POZOS
    // ============================================================================
    function renderPozos() {
        const filterSearch = document.getElementById('pozo-search-filter').value.toLowerCase();
        const mesesMostrar = Array.from(document.querySelectorAll('.pozo-mes-chk:checked')).map(cb => cb.value);

        const thead = document.querySelector('#pozos-table thead');
        const tbody = document.getElementById('pozos-tbody');
        const tfoot = document.getElementById('pozos-tfoot');

        if (mesesMostrar.length === 0) {
            thead.innerHTML = '<tr><th>No hay meses seleccionados</th></tr>';
            tbody.innerHTML = '<tr class="empty-row"><td colspan="20">Selecciona al menos un mes en los filtros.</td></tr>';
            tfoot.innerHTML = '';
            return;
        }

        let theadHTML1 = `<tr><th rowspan="2" class="sticky-col text-left">POZO</th>`;
        let theadHTML2 = `<tr class="sub-header">`;
        mesesMostrar.forEach(mes => {
            theadHTML1 += `<th colspan="3">${mes.toUpperCase()}</th>`;
            theadHTML2 += `<th>1RA QUINCENA</th><th>2DA QUINCENA</th><th>TOTAL ${mes.substring(0,3).toUpperCase()}</th>`;
        });
        theadHTML1 += `<th rowspan="2">GRAN TOTAL</th></tr>`;
        theadHTML2 += `</tr>`;
        thead.innerHTML = theadHTML1 + theadHTML2;

        const pozosMap = {};
        pozosData.forEach(p => {
            if (!pozosMap[p.pozo]) pozosMap[p.pozo] = {};
            mesesMostrar.forEach(mes => {
                if (!pozosMap[p.pozo][mes]) pozosMap[p.pozo][mes] = { q1: 0, q2: 0 };
            });
            if (mesesMostrar.includes(p.mes)) {
                if (p.quincena === '1RA QUINCENA') pozosMap[p.pozo][p.mes].q1 += p.costo;
                if (p.quincena === '2DA QUINCENA') pozosMap[p.pozo][p.mes].q2 += p.costo;
            }
        });

        tbody.innerHTML = '';
        let columnTotals = new Array(mesesMostrar.length * 3 + 1).fill(0);

        const pozosSorted = Object.keys(pozosMap).sort((a, b) => {
            const totA = mesesMostrar.reduce((s, m) => s + (pozosMap[a][m]?.q1||0) + (pozosMap[a][m]?.q2||0), 0);
            const totB = mesesMostrar.reduce((s, m) => s + (pozosMap[b][m]?.q1||0) + (pozosMap[b][m]?.q2||0), 0);
            return totB - totA;
        });

        pozosSorted.forEach(pozoName => {
            if (!pozoName.toLowerCase().includes(filterSearch)) return;

            const tr = document.createElement('tr');
            let rowHTML = `<td class="sticky-col text-left"><strong>${pozoName}</strong></td>`;
            let granTotalPozo = 0;
            let hasData = false;

            mesesMostrar.forEach((mes, idx) => {
                const val1   = pozosMap[pozoName][mes].q1;
                const val2   = pozosMap[pozoName][mes].q2;
                const totalM = val1 + val2;
                if (totalM > 0) hasData = true;
                granTotalPozo             += totalM;
                columnTotals[idx * 3]     += val1;
                columnTotals[idx * 3 + 1] += val2;
                columnTotals[idx * 3 + 2] += totalM;
                rowHTML += `
                    <td class="text-right currency-mxn">${val1 > 0 ? formatMxn(val1) : '-'}</td>
                    <td class="text-right currency-mxn">${val2 > 0 ? formatMxn(val2) : '-'}</td>
                    <td class="text-right currency-mxn total-col">${totalM > 0 ? formatMxn(totalM) : '-'}</td>`;
            });

            if (hasData) {
                columnTotals[columnTotals.length - 1] += granTotalPozo;
                rowHTML += `<td class="text-right currency-mxn" style="background-color: #e2e8f0;">${granTotalPozo > 0 ? formatMxn(granTotalPozo) : '-'}</td>`;
                tr.innerHTML = rowHTML;
                tbody.appendChild(tr);
            }
        });

        let tfootHTML = `<tr class="total-row"><td class="text-right sticky-col">TOTALES</td>`;
        columnTotals.forEach((tot, idx) => {
            const isFinal = idx === columnTotals.length - 1;
            const style   = isFinal ? 'color: var(--primary-color); font-size: 1.1em;' : '';
            tfootHTML += `<td class="text-right currency-mxn" style="${style}">${formatMxn(tot)}</td>`;
        });
        tfoot.innerHTML = tfootHTML + `</tr>`;
    }

    // ============================================================================
    // 6. RENDERIZADO — ESTADÍSTICAS
    // ============================================================================
    function renderEstadisticas() {
        const tbQuincena = document.getElementById('stat-quincena-tbody');
        tbQuincena.innerHTML = '';
        quincenasData.forEach(q => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-center">${q.num}</td>
                <td class="text-right currency-mxn">${q.bonosMxn ? formatMxn(q.bonosMxn) : ''}</td>
                <td class="text-center">${q.serviciosCount ?? ''}</td>
                <td class="text-center">${q.suministrosCount ?? ''}</td>`;
            tbQuincena.appendChild(tr);
        });

        const tbRend = document.getElementById('stat-rendimiento-tbody');
        tbRend.innerHTML = '';
        quincenasData.forEach(q => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-center">${q.num}</td>
                <td class="text-right currency-mxn">${formatMxn(q.bonosMxn)}</td>
                <td class="text-center">${q.serviciosCount}</td>
                <td class="text-center">${q.suministrosCount}</td>`;
            tbRend.appendChild(tr);
        });

        const tbMesMxn = document.getElementById('stat-mes-mxn-tbody');
        const tbMesUsd = document.getElementById('stat-mes-usd-tbody');
        tbMesMxn.innerHTML = '';
        tbMesUsd.innerHTML = '';

        const TC = 20.10;

        mesData.forEach(d => {
            const trMxn = document.createElement('tr');
            trMxn.innerHTML = `
                <td class="text-left">${d.mes}</td>
                <td class="text-right currency-mxn">${d.bonosMxn > 0 ? formatMxn(d.bonosMxn) : ''}</td>`;
            tbMesMxn.appendChild(trMxn);

            const bonosUsd = d.bonosMxn > 0 ? d.bonosMxn / TC : 0;
            const trUsd = document.createElement('tr');
            trUsd.innerHTML = `
                <td class="text-left">${d.mes}</td>
                <td class="text-right currency-usd">${bonosUsd > 0 ? formatUsd(bonosUsd) : ''}</td>`;
            tbMesUsd.appendChild(trUsd);
        });

        const tbDiff = document.getElementById('stat-diferencia-tbody');
        tbDiff.innerHTML = '';
        quincenasData.forEach((q, i) => {
            const classDiff = q.pctDif === null ? '' : q.pctDif >= 0 ? 'percentage-positive' : 'percentage-negative';
            const sign      = (q.pctDif !== null && q.pctDif > 0) ? '+' : '';
            const pctText   = q.pctDif !== null ? `${sign}${q.pctDif}%` : '—';
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-center">${i + 1}</td>
                <td class="text-left"><strong>${q.periodo}</strong></td>
                <td class="text-center">${q.serviciosCount}</td>
                <td class="text-center">${q.suministrosCount}</td>
                <td class="text-right currency-mxn">${formatMxn(q.bonosMxn)}</td>
                <td class="text-center ${classDiff}">${pctText}</td>`;
            tbDiff.appendChild(tr);
        });

        if (evChart) evChart.destroy();
        const ctx = document.getElementById('evolucionChart').getContext('2d');
        evChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: mesData.map(d => d.mes),
                datasets: [
                    {
                        label: 'Bonos MXN',
                        data: mesData.map(d => d.bonosMxn),
                        backgroundColor: '#5e5e5e',
                        yAxisID: 'y'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { type: 'linear', position: 'left', ticks: { callback: v => '$' + v.toLocaleString('es-MX') } }
                },
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    // ============================================================================
    // TABS NAVEGACIÓN
    // ============================================================================
    const tabs     = document.querySelectorAll('.tab-btn');
    const sections = document.querySelectorAll('.table-section');
    tabs.forEach(btn => {
        btn.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            sections.forEach(s => s.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(btn.dataset.tab).classList.add('active');
        });
    });

    document.getElementById('emp-area-filter').addEventListener('change', renderEmpleados);
    document.getElementById('emp-search-filter').addEventListener('input', renderEmpleados);
    document.getElementById('pozo-search-filter').addEventListener('input', renderPozos);

    document.getElementById('global-year').addEventListener('change', function () {
        loadData(this.value);
    });

    // ============================================================================
    // EXPORTAR A CSV
    // ============================================================================
    window.exportTable = function (tableId, filename) {
        const table = document.getElementById(tableId);
        let csv = [];
        table.querySelectorAll('thead tr').forEach(row => {
            const rowData = [];
            row.querySelectorAll('th').forEach(cell => rowData.push(`"${cell.textContent.trim().replace(/"/g,'""')}"`));
            csv.push(rowData.join(','));
        });
        table.querySelectorAll('tbody tr, tfoot tr').forEach(row => {
            const rowData = [];
            row.querySelectorAll('td').forEach(cell => rowData.push(`"${cell.textContent.trim().replace(/[$,]/g,'').replace(/\s+/g,' ')}"`));
            csv.push(rowData.join(','));
        });
        const blob = new Blob(['\uFEFF' + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href     = URL.createObjectURL(blob);
        link.download = filename + '.csv';
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    // ============================================================================
    // INICIALIZAR
    // ============================================================================
    updateDropdownText('emp');
    updateDropdownText('area');
    updateDropdownText('pozo');
    loadData(document.getElementById('global-year').value);
});
</script>
@endsection
