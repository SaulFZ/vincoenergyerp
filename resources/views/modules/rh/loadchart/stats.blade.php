@extends('modules.rh.loadchart.index')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        :root {
            --primary-color: #34495e;
            --secondary-color: #2c3e50;
            --accent-color: #d67e29; /* <-- no cambies esre color porfavor */
            --text-dark: #2d3748;
            --text-medium: #4a5568;
            --bg-light: #f8fafc;
            --white: #ffffff;
            --border-color: #e2e8f0;
            --success: #27ae60;
            --danger: #e74c3c;

            /* Colores de Actividades Actualizados */
            --work-base: #334c95;
            --work-well: #6475aa;
            --home-office: #7293ff;
            --traveling: #a2b5ff;
            --rest: #118b20;
            --vacation: #59983e;
            --training: #a5a5a5;
            --medical: #dd840fe0;
            --commissioned: #249399;
            --absence: #7d4e4e;
            --permission: #49c0c9;
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

        #year-controls-wrapper {
            display: none;
            align-items: center;
            gap: 10px;
            background: var(--white);
            padding: 4px 10px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
        }

        #year-controls-wrapper label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-medium);
            margin: 0;
        }

        #global-year {
            padding: 4px 8px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            color: var(--secondary-color);
            background: var(--white);
            cursor: pointer;
            outline: none;
        }

        #loading-indicator {
            display: none;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--text-medium);
        }

        #loading-indicator .spinner {
            width: 14px;
            height: 14px;
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
            padding: 2px 8px;
            border-radius: 4px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

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

        .tab-btn:hover {
            color: var(--primary-color);
            background: rgba(0, 0, 0, 0.02);
        }

        .tab-btn.active {
            color: var(--primary-color);
            border-bottom-color: var(--accent-color);
        }

        .table-section {
            display: none;
            animation: fadeIn 0.3s ease-out;
        }

        .table-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .table-card {
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
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

        .table-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin: 0;
        }

        .table-subtitle {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 15px;
            border-left: 4px solid var(--accent-color);
            padding-left: 10px;
        }

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

        .select-field,
        .input-field {
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

        .select-field:focus,
        .input-field:focus {
            border-color: var(--primary-color);
        }

        optgroup {
            font-weight: bold;
            color: var(--secondary-color);
        }

        .multi-select-container {
            position: relative;
            width: 100%;
            user-select: none;
        }

        .multi-select-header {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background-color: var(--white);
            font-size: 13px;
            color: var(--text-dark);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 37px;
            box-sizing: border-box;
        }

        .multi-select-header:hover {
            border-color: #94a3b8;
        }

        .multi-select-options {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: var(--white);
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 100;
            max-height: 250px;
            overflow-y: auto;
            padding: 5px;
        }

        .multi-select-options.show {
            display: block;
        }

        .multi-select-options label {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            cursor: pointer;
            font-size: 13px;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .multi-select-options label:hover {
            background: #f1f5f9;
        }

        .multi-select-options input[type="checkbox"] {
            cursor: pointer;
        }

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

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .btn:hover {
            opacity: 0.9;
        }

        .table-container {
            overflow-x: auto;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            max-height: 500px;
            overflow-y: auto;
            margin-bottom: 20px;
            position: relative;
        }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: var(--white);
            font-size: 0.80rem;
            font-family: 'Calibri', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
        }

        .data-table th {
            background-color: #e2e8f0 !important;
            color: var(--text-dark) !important;
            font-weight: 700;
            padding: 8px 10px;
            text-align: center;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 10;
            border-bottom: 2px solid #cbd5e1 !important;
            border-right: 1px solid #cbd5e1 !important;
            transition: background-color 0.2s;
            user-select: none;
        }

        .sortable-header {
            cursor: pointer;
        }

        .sortable-header:hover {
            background-color: #cbd5e1 !important;
            color: var(--primary-color) !important;
        }

        .data-table .sub-header-row th {
            background-color: #cbd5e1 !important;
            color: var(--text-dark) !important;
            top: 36px;
            font-size: 0.70rem;
            padding: 6px;
        }

        .data-table td {
            padding: 6px 10px;
            border-bottom: 1px solid var(--border-color);
            border-right: 1px solid var(--border-color);
            vertical-align: middle;
            color: var(--text-dark);
        }

        .sticky-col {
            position: sticky;
            left: 0;
            background-color: var(--white);
            z-index: 5;
            box-shadow: 2px 0 5px -2px rgba(0, 0, 0, 0.1);
            font-weight: 700;
        }

        .data-table th.sticky-col {
            z-index: 15;
            background-color: #e2e8f0 !important;
        }

        .data-table .sub-header-row th.sticky-col {
            background-color: #cbd5e1 !important;
            z-index: 16;
        }

        .data-table tr:hover td {
            background-color: #f8fafc;
        }

        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .text-left { text-align: left !important; }

        .currency-mxn {
            font-family: 'Calibri', 'Segoe UI', sans-serif;
            font-weight: 700;
            color: var(--secondary-color);
        }

        .currency-usd {
            font-family: 'Calibri', 'Segoe UI', sans-serif;
            font-weight: 700;
            color: #27ae60;
        }

        .percentage-positive {
            color: var(--success);
            font-weight: 700;
        }

        .percentage-negative {
            color: var(--danger);
            font-weight: 700;
        }

        .data-table tfoot th,
        .data-table tfoot td,
        .total-row td {
            position: sticky;
            bottom: 0;
            background-color: #e2e8f0 !important;
            color: var(--text-dark) !important;
            font-weight: 700;
            border-top: 2px solid var(--secondary-color);
            z-index: 20;
            box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.05);
        }

        .data-table tfoot .sticky-col,
        .total-row .sticky-col {
            z-index: 25;
            background-color: #e2e8f0 !important;
        }

        .col-promedio {
            background-color: #f8fafc;
        }

        .badge-area {
            background-color: #e2e8f0;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-medium);
        }

        .clickable-bonus {
            cursor: pointer;
            transition: background-color 0.2s, box-shadow 0.2s;
        }

        .clickable-bonus:hover {
            background-color: #e0f2fe !important;
            box-shadow: inset 0 0 0 1px #7dd3fc !important;
        }

        .clickable-act {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .clickable-act:hover td {
            background-color: #e0f2fe !important;
        }

        .clickable-cell {
            cursor: pointer;
            transition: background-color 0.2s, box-shadow 0.2s;
        }

        .clickable-cell:hover {
            background-color: #e0f2fe !important;
            box-shadow: inset 0 0 0 1px #7dd3fc !important;
            color: #0369a1 !important;
        }

        .has-commissioned {
            background-color: rgba(36, 147, 153, 0.08) !important;
            border-left: 3px solid #249399 !important;
        }

        .has-commissioned:hover {
            background-color: rgba(36, 147, 153, 0.18) !important;
        }

        .commissioned-icon {
            color: #249399;
            margin-right: 4px;
            font-size: 14px;
            pointer-events: none;
        }

        .custom-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1050;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .custom-modal-backdrop.show {
            display: flex;
            opacity: 1;
        }

        .custom-modal-dialog {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 95%;
            max-width: 900px;
            max-height: 90vh;
            overflow: hidden;
            transform: translateY(-50px);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .custom-modal-content {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
            height: 100%;
        }

        .custom-modal-backdrop.show .custom-modal-dialog {
            transform: translateY(0);
        }

        .custom-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            background: #f8fafc;
            border-radius: 12px 12px 0 0;
        }

        .custom-modal-header .header-title {
            flex: 1;
        }

        .custom-modal-header h5 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--secondary-color);
        }

        .custom-modal-header .search-modal-container {
            margin-right: 15px;
            min-width: 250px;
        }

        .custom-modal-header .close-btn {
            background: transparent;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-medium);
            line-height: 1;
        }

        .custom-modal-body {
            padding: 20px;
            overflow-y: auto;
            flex: 1 1 auto;
            min-height: 0;
        }

        .custom-modal-footer {
            padding: 15px 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            background: #ffffff;
        }

        #actividadesModal .custom-modal-dialog,
        #historyModal .custom-modal-dialog {
            max-width: 1400px;
            width: 95%;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            align-items: start;
        }

        .grid-column {
            display: flex;
            flex-direction: column;
        }

        .grid-full-width {
            grid-column: 1 / -1;
        }

        .chart-container {
            position: relative;
            width: 100%;
            padding: 15px;
            background: var(--white);
            border: 1px solid var(--border-color);
            border-radius: 6px;
        }

        .table-container::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .table-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 4px;
        }

        @media (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        .empty-row td {
            text-align: center;
            padding: 24px;
            color: var(--text-medium);
            font-style: italic;
        }
    </style>

    <main class="dashboard-container">

        <div id="year-controls-wrapper">
            <label for="global-year">Año</label>
            <select id="global-year">
                <option value="2026" selected>2026</option>
                <option value="2025">2025</option>
                <option value="2024">2024</option>
            </select>
            <span id="loading-indicator"><span class="spinner"></span> Cargando datos…</span>
            <span id="error-indicator"></span>
        </div>

        <div class="data-tabs">
            <button class="tab-btn active" data-tab="bonos-empleados">1. Bonos por Empleado</button>
            <button class="tab-btn" data-tab="resumen-areas">2. Resumen por Área</button>
            <button class="tab-btn" data-tab="control-pozos">3. Control de Pozos</button>
            <button class="tab-btn" data-tab="evolucion-estadisticas">4. Evolución y Estadísticas</button>
            <button class="tab-btn" data-tab="resumen-actividades">5. Resumen de Actividades</button>
            <button class="tab-btn" data-tab="utilizacion-personal">6. Utilización del Personal</button>
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
                                <option value="Q1">Trimestre 1 (Ene - Mar)</option>
                                <option value="Q2">Trimestre 2 (Abr - Jun)</option>
                                <option value="Q3">Trimestre 3 (Jul - Sep)</option>
                                <option value="Q4">Trimestre 4 (Oct - Dic)</option>
                            </optgroup>
                            <optgroup label="Anual">
                                <option value="Anual">Anual Completo</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="filter-group" style="min-width: 200px;">
                        <label class="filter-label">Meses a Visualizar</label>
                        <div class="multi-select-container">
                            <div class="multi-select-header" onclick="toggleDropdown('emp-meses-dropdown', event)">
                                <span id="emp-meses-text">Seleccionando...</span><span>&#9662;</span>
                            </div>
                            <div class="multi-select-options" id="emp-meses-dropdown">
                                <label><input type="checkbox" value="Enero" class="emp-mes-chk"> Enero</label>
                                <label><input type="checkbox" value="Febrero" class="emp-mes-chk"> Febrero</label>
                                <label><input type="checkbox" value="Marzo" class="emp-mes-chk"> Marzo</label>
                                <label><input type="checkbox" value="Abril" class="emp-mes-chk"> Abril</label>
                                <label><input type="checkbox" value="Mayo" class="emp-mes-chk"> Mayo</label>
                                <label><input type="checkbox" value="Junio" class="emp-mes-chk"> Junio</label>
                                <label><input type="checkbox" value="Julio" class="emp-mes-chk"> Julio</label>
                                <label><input type="checkbox" value="Agosto" class="emp-mes-chk"> Agosto</label>
                                <label><input type="checkbox" value="Septiembre" class="emp-mes-chk"> Septiembre</label>
                                <label><input type="checkbox" value="Octubre" class="emp-mes-chk"> Octubre</label>
                                <label><input type="checkbox" value="Noviembre" class="emp-mes-chk"> Noviembre</label>
                                <label><input type="checkbox" value="Diciembre" class="emp-mes-chk"> Diciembre</label>
                            </div>
                        </div>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Área</label>
                        <select id="emp-area-filter" class="select-field">
                            <option value="TODAS">Todas las Áreas</option>
                            <option value="OPERACIONES">Operaciones</option>
                            <option value="SUMINISTROS">Suministros</option>
                            <option value="ADMINISTRACION">Administración</option>
                            <option value="QHSE">QHSE</option>
                            <option value="GEOCIENCIAS">Geociencias</option>
                            <option value="VENTAS">Ventas</option>
                            <option value="LABORATORIO">Laboratorio</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Buscar Empleado</label>
                        <input type="text" id="emp-search-filter" class="input-field"
                            placeholder="Nombre o Clave...">
                    </div>
                </div>
                <div class="table-container" style="max-height: 350px;">
                    <table id="empleados-table" class="data-table" style="min-width: 1200px;">
                        <thead></thead>
                        <tbody id="empleados-tbody">
                            <tr class="empty-row">
                                <td colspan="20">Cargando datos del servidor…</td>
                            </tr>
                        </tbody>
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
                            <optgroup label="Anual">
                                <option value="Anual">Anual Completo</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="filter-group" style="min-width: 200px;">
                        <label class="filter-label">Meses a Visualizar</label>
                        <div class="multi-select-container">
                            <div class="multi-select-header" onclick="toggleDropdown('area-meses-dropdown', event)">
                                <span id="area-meses-text">Seleccionando...</span><span>&#9662;</span>
                            </div>
                            <div class="multi-select-options" id="area-meses-dropdown">
                                <label><input type="checkbox" value="Enero" class="area-mes-chk"> Enero</label>
                                <label><input type="checkbox" value="Febrero" class="area-mes-chk"> Febrero</label>
                                <label><input type="checkbox" value="Marzo" class="area-mes-chk"> Marzo</label>
                                <label><input type="checkbox" value="Abril" class="area-mes-chk"> Abril</label>
                                <label><input type="checkbox" value="Mayo" class="area-mes-chk"> Mayo</label>
                                <label><input type="checkbox" value="Junio" class="area-mes-chk"> Junio</label>
                                <label><input type="checkbox" value="Julio" class="area-mes-chk"> Julio</label>
                                <label><input type="checkbox" value="Agosto" class="area-mes-chk"> Agosto</label>
                                <label><input type="checkbox" value="Septiembre" class="area-mes-chk"> Septiembre</label>
                                <label><input type="checkbox" value="Octubre" class="area-mes-chk"> Octubre</label>
                                <label><input type="checkbox" value="Noviembre" class="area-mes-chk"> Noviembre</label>
                                <label><input type="checkbox" value="Diciembre" class="area-mes-chk"> Diciembre</label>
                            </div>
                        </div>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Comparar con Año</label>
                        <select id="area-compare-year" class="select-field">
                            <option value="">Sin comparar</option>
                            <option value="2026">2026</option>
                            <option value="2025">2025</option>
                            <option value="2024">2024</option>
                        </select>
                    </div>
                </div>
                <div class="table-container">
                    <table id="areas-table" class="data-table" style="min-width: 1000px;">
                        <thead></thead>
                        <tbody id="areas-tbody">
                            <tr class="empty-row">
                                <td colspan="20">Cargando…</td>
                            </tr>
                        </tbody>
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
                                <option value="Q1">Trimestre 1 (Ene - Mar)</option>
                                <option value="Q2">Trimestre 2 (Abr - Jun)</option>
                                <option value="Q3">Trimestre 3 (Jul - Sep)</option>
                                <option value="Q4">Trimestre 4 (Oct - Dic)</option>
                            </optgroup>
                            <optgroup label="Anual">
                                <option value="Anual">Anual Completo</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="filter-group" style="min-width: 200px;">
                        <label class="filter-label">Meses a Visualizar</label>
                        <div class="multi-select-container">
                            <div class="multi-select-header" onclick="toggleDropdown('pozo-meses-dropdown', event)">
                                <span id="pozo-meses-text">Seleccionando...</span><span>&#9662;</span>
                            </div>
                            <div class="multi-select-options" id="pozo-meses-dropdown">
                                <label><input type="checkbox" value="Enero" class="pozo-mes-chk"> Enero</label>
                                <label><input type="checkbox" value="Febrero" class="pozo-mes-chk"> Febrero</label>
                                <label><input type="checkbox" value="Marzo" class="pozo-mes-chk"> Marzo</label>
                                <label><input type="checkbox" value="Abril" class="pozo-mes-chk"> Abril</label>
                                <label><input type="checkbox" value="Mayo" class="pozo-mes-chk"> Mayo</label>
                                <label><input type="checkbox" value="Junio" class="pozo-mes-chk"> Junio</label>
                                <label><input type="checkbox" value="Julio" class="pozo-mes-chk"> Julio</label>
                                <label><input type="checkbox" value="Agosto" class="pozo-mes-chk"> Agosto</label>
                                <label><input type="checkbox" value="Septiembre" class="pozo-mes-chk"> Septiembre</label>
                                <label><input type="checkbox" value="Octubre" class="pozo-mes-chk"> Octubre</label>
                                <label><input type="checkbox" value="Noviembre" class="pozo-mes-chk"> Noviembre</label>
                                <label><input type="checkbox" value="Diciembre" class="pozo-mes-chk"> Diciembre</label>
                            </div>
                        </div>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Filtrar Pozo</label>
                        <select id="pozo-search-filter" class="select-field">
                            <option value="TODOS">Todos los Pozos</option>
                        </select>
                    </div>
                </div>
                <div class="table-container">
                    <table id="pozos-table" class="data-table" style="min-width: 1000px;">
                        <thead></thead>
                        <tbody id="pozos-tbody">
                            <tr class="empty-row">
                                <td colspan="20">Cargando…</td>
                            </tr>
                        </tbody>
                        <tfoot id="pozos-tfoot"></tfoot>
                    </table>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px;">
                    <h3 class="table-subtitle" style="margin:0;">GRÁFICO DE COSTOS POR POZO (MXN) - TOP 10</h3>
                </div>
                <div class="chart-container" style="height: 350px;">
                    <div id="pozosChart"></div>
                </div>
            </div>
        </section>

        {{-- ─── TAB 4: REDISEÑO AVANZADO ───────────────────────── --}}
        <section id="evolucion-estadisticas" class="table-section">
            <div class="table-card">
                <div class="table-header">
                    <h2 class="table-title">Evolución, Estadísticas y Proyecciones — <span
                            id="stats-year-label">2026</span></h2>
                    <button class="btn btn-primary"
                        onclick="exportTable('stat-quincena-master-table', 'Analisis_Quincenal')">
                        <i class="fas fa-download"></i> Exportar a Excel
                    </button>
                </div>

                <h3 class="table-subtitle">RENDIMIENTO Y CRECIMIENTO QUINCENAL</h3>
                <div class="table-container" style="max-height: 400px; margin-bottom: 25px;">
                    <table id="stat-quincena-master-table" class="data-table" style="min-width: 100%;">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-left">Periodo</th>
                                <th class="text-center" title="Total de Servicios Realizados">SERV.</th>
                                <th class="text-center" title="Total de Viajes de Suministros (Solo Inicios)">SUMIN.</th>
                                <th class="text-right">Bonos MXN</th>
                                <th class="text-center">% Crecimiento</th>
                            </tr>
                        </thead>
                        <tbody id="stat-quincena-master-tbody"></tbody>
                        <tfoot id="stat-quincena-master-tfoot"></tfoot>
                    </table>
                </div>
                <div class="grid-full-width" style="margin-top: 20px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <h3 class="table-subtitle" style="margin:0;">EVOLUCIÓN DE SERVICIOS Y VIAJES DE SUMINISTROS (MENSUAL)</h3>
                    </div>
                    <div class="chart-container" style="height: 350px; margin-top:15px;">
                        <div id="serviciosSuministrosChart"></div>
                    </div>
                </div>
                <div class="dashboard-grid" style="margin-top:20px;">
                    <div class="grid-column">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <h3 class="table-subtitle" style="margin:0;">EVOLUCIÓN MENSUAL (MXN Y USD)</h3>
                        </div>
                        <div class="chart-container" style="height: 380px; margin-top:15px;">
                            <div id="evolucionChart"></div>
                        </div>
                    </div>
                    <div class="grid-column">
                        <h3 class="table-subtitle" style="margin:0; margin-bottom:15px;">DESGLOSE MENSUAL CONSOLIDADO</h3>
                        <div class="table-container" style="max-height: 380px; margin-bottom: 0;">
                            <table id="stat-mes-consolidado-table" class="data-table" style="min-width: 100%;">
                                <thead>
                                    <tr>
                                        <th class="text-left">Mes</th>
                                        <th class="text-right">Bonos (MXN)</th>
                                        <th class="text-right">Bonos (USD)*</th>
                                    </tr>
                                </thead>
                                <tbody id="stat-mes-consolidado-tbody"></tbody>
                                <tfoot id="stat-mes-consolidado-tfoot"></tfoot>
                            </table>
                        </div>
                        <p style="font-size:11px; color:var(--text-medium); margin-top:5px; text-align:right;">* TC
                            Promedio Mensual Banxico (SF43718)</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── TAB 5: RESUMEN DE ACTIVIDADES ──────────────────────────── --}}
        <section id="resumen-actividades" class="table-section">

            {{-- TABLA 1: MENSUAL --}}
            <div class="table-card">
                <div class="table-header">
                    <h2 class="table-title">Resumen de Actividades por Mes</h2>
                    <button class="btn btn-primary" onclick="exportTable('actividades-table', 'Resumen_Actividades_Mes')">
                        <i class="fas fa-download"></i> Exportar a Excel
                    </button>
                </div>

                <div class="table-filters">
                    <div class="filter-group">
                        <label class="filter-label">Periodo Rápido</label>
                        <select id="act-trimestre-filter" class="select-field">
                            <option value="Personalizado">Personalizado</option>
                            <optgroup label="Trimestral">
                                <option value="Q1">Trimestre 1 (Ene - Mar)</option>
                                <option value="Q2">Trimestre 2 (Abr - Jun)</option>
                                <option value="Q3">Trimestre 3 (Jul - Sep)</option>
                                <option value="Q4">Trimestre 4 (Oct - Dic)</option>
                            </optgroup>
                            <optgroup label="Anual">
                                <option value="Anual">Anual Completo</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="filter-group" style="min-width: 200px;">
                        <label class="filter-label">Meses a Visualizar</label>
                        <div class="multi-select-container">
                            <div class="multi-select-header" onclick="toggleDropdown('act-meses-dropdown', event)">
                                <span id="act-meses-text">Seleccionando...</span><span>&#9662;</span>
                            </div>
                            <div class="multi-select-options" id="act-meses-dropdown">
                                <label><input type="checkbox" value="Enero" class="act-mes-chk" checked> Enero</label>
                                <label><input type="checkbox" value="Febrero" class="act-mes-chk" checked> Febrero</label>
                                <label><input type="checkbox" value="Marzo" class="act-mes-chk" checked> Marzo</label>
                                <label><input type="checkbox" value="Abril" class="act-mes-chk" checked> Abril</label>
                                <label><input type="checkbox" value="Mayo" class="act-mes-chk" checked> Mayo</label>
                                <label><input type="checkbox" value="Junio" class="act-mes-chk" checked> Junio</label>
                                <label><input type="checkbox" value="Julio" class="act-mes-chk" checked> Julio</label>
                                <label><input type="checkbox" value="Agosto" class="act-mes-chk" checked> Agosto</label>
                                <label><input type="checkbox" value="Septiembre" class="act-mes-chk" checked> Septiembre</label>
                                <label><input type="checkbox" value="Octubre" class="act-mes-chk" checked> Octubre</label>
                                <label><input type="checkbox" value="Noviembre" class="act-mes-chk" checked> Noviembre</label>
                                <label><input type="checkbox" value="Diciembre" class="act-mes-chk" checked> Diciembre</label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CONTENEDOR DE TARJETAS DE RESUMEN --}}
                <div id="act-main-summary" style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px;">
                </div>

                <div class="table-container" style="margin-bottom: 10px;">
                    <table id="actividades-table" class="data-table" style="min-width: 1600px;">
                        <thead>
                            <tr>
                                <th rowspan="2" class="sticky-col text-left">MES</th>
                                <th colspan="11" class="text-center" style="background-color: #e2e8f0 !important; color: var(--text-dark) !important;">DÍAS TOTALES (ABSOLUTOS)</th>
                                <th colspan="11" class="text-center" style="background-color: #cbd5e1 !important; color: var(--text-dark) !important;">DÍAS PROMEDIO POR PERSONA</th>
                                <th rowspan="2" class="text-center" title="Porcentaje de Utilización">% UTIL.</th>
                            </tr>
                            <tr class="sub-header-row">
                                <th class="text-center" title="Trabajo en Base">B</th>
                                <th class="text-center" title="Trabajo en Pozo">P</th>
                                <th class="text-center" title="Comisionado">C</th>
                                <th class="text-center" title="Trabajo en Casa">TC</th>
                                <th class="text-center" title="Viaje">V</th>
                                <th class="text-center" title="Descanso">D</th>
                                <th class="text-center" title="Vacaciones">VAC</th>
                                <th class="text-center" title="Médico">M</th>
                                <th class="text-center" title="Entrenamiento">E</th>
                                <th class="text-center" title="Ausencia">A</th>
                                <th class="text-center" title="Permiso">PE</th>
                                <th class="text-center col-promedio" title="Promedio Base">B</th>
                                <th class="text-center col-promedio" title="Promedio Pozo">P</th>
                                <th class="text-center col-promedio" title="Promedio Comisionado">C</th>
                                <th class="text-center col-promedio" title="Promedio Trabajo en Casa">TC</th>
                                <th class="text-center col-promedio" title="Promedio Viaje">V</th>
                                <th class="text-center col-promedio" title="Promedio Descanso">D</th>
                                <th class="text-center col-promedio" title="Promedio Vacaciones">VAC</th>
                                <th class="text-center col-promedio" title="Promedio Médico">M</th>
                                <th class="text-center col-promedio" title="Promedio Entrenamiento">E</th>
                                <th class="text-center col-promedio" title="Promedio Ausencia">A</th>
                                <th class="text-center col-promedio" title="Promedio Permiso">PE</th>
                            </tr>
                        </thead>
                        <tbody id="actividades-tbody">
                            <tr class="empty-row"><td colspan="24">Cargando datos...</td></tr>
                        </tbody>
                        <tfoot id="actividades-tfoot"></tfoot>
                    </table>
                </div>
            </div>

            {{-- TABLA 2: TRIMESTRAL Y GRÁFICO --}}
            <div class="dashboard-grid">
                <div class="grid-column" style="grid-column: 1 / -1;">
                    <div class="table-card">
                        <div class="table-header">
                            <h2 class="table-title">Resumen por Trimestre (Q)</h2>
                            <button class="btn btn-primary" onclick="exportTable('actividades-q-table', 'Resumen_Trimestral')">
                                <i class="fas fa-download"></i> Exportar Qs
                            </button>
                        </div>
                        <div class="table-container" style="margin-bottom: 0;">
                            <table id="actividades-q-table" class="data-table" style="min-width: 1600px;">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="sticky-col text-left">TRIMESTRE</th>
                                        <th colspan="11" class="text-center" style="background-color: #e2e8f0 !important; color: var(--text-dark) !important;">DÍAS TOTALES (ABSOLUTOS)</th>
                                        <th colspan="11" class="text-center" style="background-color: #cbd5e1 !important; color: var(--text-dark) !important;">PROMEDIO ACUMULADO DEL TRIMESTRE</th>
                                        <th rowspan="2" class="text-center">% UTIL.</th>
                                    </tr>
                                    <tr class="sub-header-row">
                                        <th class="text-center">B</th><th class="text-center">P</th><th class="text-center">C</th><th class="text-center">TC</th><th class="text-center">V</th>
                                        <th class="text-center">D</th><th class="text-center">VAC</th><th class="text-center">M</th><th class="text-center">E</th><th class="text-center">A</th><th class="text-center">PE</th>
                                        <th class="text-center col-promedio">B</th><th class="text-center col-promedio">P</th><th class="text-center col-promedio">C</th><th class="text-center col-promedio">TC</th><th class="text-center col-promedio">V</th>
                                        <th class="text-center col-promedio">D</th><th class="text-center col-promedio">VAC</th><th class="text-center col-promedio">M</th><th class="text-center col-promedio">E</th><th class="text-center col-promedio">A</th><th class="text-center col-promedio">PE</th>
                                    </tr>
                                </thead>
                                <tbody id="actividades-q-tbody"></tbody>
                                <tfoot id="actividades-q-tfoot"></tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="grid-column" style="grid-column: 1 / -1;">
                    <div class="table-card">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <h3 class="table-subtitle" style="margin:0;">EVOLUCIÓN DE DÍAS TOTALES (ABSOLUTOS)</h3>
                        </div>
                        <div class="chart-container" style="height: 400px; margin-top:15px; border:none; padding:0;">
                            <div id="actividadesChart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── TAB 6: UTILIZACIÓN DEL PERSONAL (NUEVA SECCIÓN) ──────────────────────────── --}}
        <section id="utilizacion-personal" class="table-section">
            <div class="table-card">
                <div class="table-header">
                    <h2 class="table-title">Utilización del Personal</h2>
                    <button class="btn btn-primary" onclick="exportTable('utilizacion-table', 'Utilizacion_Personal')">
                        <i class="fas fa-download"></i> Exportar a Excel
                    </button>
                </div>

                <div class="table-container" style="margin-bottom: 25px;">
                    <table id="utilizacion-table" class="data-table" style="min-width: 1600px;">
                        <thead>
                            <tr>
                                <th rowspan="2" class="sticky-col text-left">MES</th>
                                <th colspan="11" class="text-center" style="background-color: #e2e8f0 !important; color: var(--text-dark) !important;">DÍAS TOTALES (ABSOLUTOS)</th>
                                <th colspan="11" class="text-center" style="background-color: #cbd5e1 !important; color: var(--text-dark) !important;">PROMEDIOS POR PERSONA</th>
                                <th rowspan="2" class="text-center">TOTAL DÍAS</th>
                                <th rowspan="2" class="text-center">DÍAS UTILIZADOS</th>
                                <th rowspan="2" class="text-center">TOTAL %</th>
                            </tr>
                            <tr class="sub-header-row">
                                <th class="text-center">BASE</th><th class="text-center">POZO</th><th class="text-center">COMISIÓN</th><th class="text-center">CASA</th><th class="text-center">VIAJE</th>
                                <th class="text-center">DESCANSO</th><th class="text-center">VACACIONES</th><th class="text-center">MÉDICO</th><th class="text-center">ENTRENA.</th><th class="text-center">AUSENCIA</th><th class="text-center">PERMISO</th>
                                <th class="text-center col-promedio">BASE PROM</th><th class="text-center col-promedio">POZO PROM</th><th class="text-center col-promedio">COMISIÓN PROM</th><th class="text-center col-promedio">CASA PROM</th><th class="text-center col-promedio">VIAJE PROM</th>
                                <th class="text-center col-promedio">DESC. PROM</th><th class="text-center col-promedio">VAC PROM</th><th class="text-center col-promedio">MED PROM</th><th class="text-center col-promedio">ENTR. PROM</th><th class="text-center col-promedio">AUS PROM</th><th class="text-center col-promedio">PERM PROM</th>
                            </tr>
                        </thead>
                        <tbody id="utilizacion-tbody">
                            <tr class="empty-row"><td colspan="27">Cargando datos...</td></tr>
                        </tbody>
                        <tfoot id="utilizacion-tfoot"></tfoot>
                    </table>
                </div>

                {{-- NUVAS TABLAS: TRIMESTRE (Q) y AREAS --}}
                <div style="margin-top: 30px;">
                    <h3 class="table-subtitle" style="margin-bottom:15px;">RESUMEN POR TRIMESTRE (Q)</h3>
                    <div class="table-container" style="margin-bottom: 25px;">
                        <table id="utilizacion-q-table" class="data-table" style="min-width: 1200px;">
                            <thead>
                                <tr>
                                    <th class="sticky-col text-left">TRIMESTRE</th>
                                    <th class="text-center">BASE</th><th class="text-center">POZO</th><th class="text-center">COMISIÓN</th><th class="text-center">CASA</th><th class="text-center">VIAJE</th>
                                    <th class="text-center">DESCANSO</th><th class="text-center">VACACIONES</th><th class="text-center">MÉDICO</th><th class="text-center">ENTRENA.</th><th class="text-center">AUSENCIA</th><th class="text-center">PERMISO</th>
                                    <th class="text-center">TOTAL DÍAS</th><th class="text-center">DÍAS UTILIZADOS</th><th class="text-center">TOTAL %</th>
                                </tr>
                            </thead>
                            <tbody id="utilizacion-q-tbody"></tbody>
                        </table>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:15px; flex-wrap: wrap; gap:10px;">
                        <h3 class="table-subtitle" style="margin-bottom:0;">RESUMEN POR ÁREA Y GRAFICACIÓN</h3>
                        <div class="table-filters" style="margin-bottom: 0; padding: 10px; background: transparent; border: none; gap: 10px;">
                            <div class="filter-group" style="min-width: 150px;">
                                <label class="filter-label">Trimestre</label>
                                <select id="util-q-filter" class="select-field" onchange="renderUtilizacionAreaAndCharts()">
                                    <option value="TODOS">Todos (Anual)</option>
                                    <option value="Q1">Q1</option>
                                    <option value="Q2">Q2</option>
                                    <option value="Q3">Q3</option>
                                    <option value="Q4">Q4</option>
                                </select>
                            </div>
                            <div class="filter-group" style="min-width: 150px;">
                                <label class="filter-label">Área</label>
                                <select id="util-area-filter" class="select-field" onchange="renderUtilizacionAreaAndCharts()">
                                    <option value="TODAS">Todas las Áreas</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="table-container" style="margin-bottom: 25px;">
                        <table id="utilizacion-area-table" class="data-table" style="min-width: 1200px;">
                            <thead>
                                <tr>
                                    <th class="sticky-col text-left">ÁREA</th>
                                    <th class="text-center">BASE</th><th class="text-center">POZO</th><th class="text-center">COMISIÓN</th><th class="text-center">CASA</th><th class="text-center">VIAJE</th>
                                    <th class="text-center">DESCANSO</th><th class="text-center">VACACIONES</th><th class="text-center">MÉDICO</th><th class="text-center">ENTRENA.</th><th class="text-center">AUSENCIA</th><th class="text-center">PERMISO</th>
                                    <th class="text-center">TOTAL DÍAS</th><th class="text-center">DÍAS UTILIZADOS</th><th class="text-center">TOTAL %</th>
                                </tr>
                            </thead>
                            <tbody id="utilizacion-area-tbody"></tbody>
                            <tfoot id="utilizacion-area-tfoot"></tfoot>
                        </table>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <div class="grid-column">
                        <div class="chart-container" style="height: 400px;">
                            <div id="pieChartTotal"></div>
                        </div>
                    </div>
                    <div class="grid-column">
                        <div class="chart-container" style="height: 400px;">
                            <div id="pieChartUtil"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    {{-- ⭐ MODAL UNIVERSAL HISTORIAL BONOS/SERVICIOS/SUMINISTROS --}}
    <div id="historyModal" class="custom-modal-backdrop">
        <div class="custom-modal-dialog">
            <div class="custom-modal-content">
                <div class="custom-modal-header">
                    <div class="header-title">
                        <h5><i class="fas fa-list"></i> <span id="history-modal-name"
                                style="color:var(--primary-color);"></span></h5>
                    </div>
                    <div class="search-modal-container">
                        <input type="text" id="history-search" class="input-field"
                            placeholder="Buscar en historial..."
                            onkeyup="filterModalTable('history-modal-tables', this.value, 'history')">
                    </div>
                    <button type="button" class="close-btn" onclick="closeHistoryModal()">&times;</button>
                </div>
                <div class="custom-modal-body">
                    <div id="history-modal-cards" style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
                    </div>
                    <div id="history-modal-tables"></div>
                </div>
                <div class="custom-modal-footer">
                    <button class="btn btn-secondary" onclick="closeHistoryModal()"
                        style="border: 1px solid #cbd5e1; background: #f1f5f9; color: #4a5568;">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ⭐ MODAL: DETALLE DE ACTIVIDADES POR EMPLEADO EN UN MES --}}
    <div id="actividadesModal" class="custom-modal-backdrop">
        <div class="custom-modal-dialog" style="max-width: 1500px; width: 98%;">
            <div class="custom-modal-content">
                <div class="custom-modal-header">
                    <div class="header-title">
                        <h5><i class="fas fa-calendar-alt"></i> Detalle de Actividades — <span id="act-modal-title"
                                style="color:var(--primary-color);"></span></h5>
                    </div>
                    <div class="search-modal-container">
                        <input type="text" id="act-search" class="input-field"
                            placeholder="Buscar empleado o área..."
                            onkeyup="filterModalTable('act-modal-tbody', this.value, 'act')">
                    </div>
                    <button type="button" class="close-btn" onclick="closeActModal()">&times;</button>
                </div>
                <div class="custom-modal-body">
                    <div id="act-modal-summary" style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px;">
                    </div>

                    <div class="table-container" style="max-height: 550px; margin-bottom: 0;">
                        <table class="data-table" style="min-width: 1200px;" id="act-modal-table-data">
                            <thead>
                                <tr>
                                    <th class="sticky-col text-left" style="min-width: 200px;">EMPLEADO</th>
                                    <th class="text-left">ÁREA</th>
                                    <th class="text-center" title="Trabajo en Base">B</th>
                                    <th class="text-center" title="Trabajo en Pozo">P</th>
                                    <th class="text-center" title="Comisionado">C</th>
                                    <th class="text-center" title="Trabajo en Casa">TC</th>
                                    <th class="text-center" title="Viaje">V</th>
                                    <th class="text-center" title="Descanso">D</th>
                                    <th class="text-center" title="Vacaciones">VAC</th>
                                    <th class="text-center" title="Médico">M</th>
                                    <th class="text-center" title="Entrenamiento">E</th>
                                    <th class="text-center" title="Ausencia">A</th>
                                    <th class="text-center" title="Permiso">PE</th>
                                    <th class="text-center" style="background-color: #1a252f; min-width: 80px; color: var(--white) !important;">TOTAL</th>
                                    <th class="text-center" style="background-color: #1a252f; min-width: 80px; color: var(--white) !important;"
                                        title="Porcentaje de Utilización">% UTIL.</th>
                                </tr>
                            </thead>
                            <tbody id="act-modal-tbody"></tbody>
                            <tfoot id="act-modal-tfoot"></tfoot>
                        </table>
                    </div>
                </div>
                <div class="custom-modal-footer">
                    <button class="btn" onclick="closeActModal()"
                        style="border: 1px solid #cbd5e1; background: #f1f5f9; color: #4a5568;">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ⭐ MODAL DE ALERTA PERSONALIZADO (Reemplaza al alert nativo) --}}
    <div id="customAlertModal" class="custom-modal-backdrop" style="z-index: 9999;">
        <div class="custom-modal-dialog" style="max-width: 400px; transform: translateY(0); margin: auto;">
            <div class="custom-modal-header" style="background: #fef2f2; border-bottom: 1px solid #fecaca;">
                <h5 style="color: var(--danger);"><i class="fas fa-exclamation-circle"></i> Aviso</h5>
                <button type="button" class="close-btn" onclick="document.getElementById('customAlertModal').classList.remove('show')">&times;</button>
            </div>
            <div class="custom-modal-body text-center" style="padding: 30px 20px;">
                <p id="customAlertMessage" style="font-size: 1rem; color: var(--text-dark); font-weight: 600; margin:0;"></p>
            </div>
            <div class="custom-modal-footer" style="justify-content: center;">
                <button class="btn btn-primary" onclick="document.getElementById('customAlertModal').classList.remove('show')">Entendido</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Función para mostrar el Modal de Alerta
            window.showCustomAlert = function(msg) {
                document.getElementById('customAlertMessage').textContent = msg;
                document.getElementById('customAlertModal').classList.add('show');
            }

            // Preseleccionar todos los meses para el Tab 5 (para ver los Quarters completos)
            document.querySelectorAll('.act-mes-chk').forEach(cb => cb.checked = true);

            // Preseleccionar mes actual para el resto
            const currentMonthIndex = new Date().getMonth();
            const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto',
                'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
            ];
            const currentMonthName = monthNames[currentMonthIndex];

            ['emp', 'area', 'pozo'].forEach(prefix => {
                document.querySelectorAll(`.${prefix}-mes-chk`).forEach(cb => {
                    cb.checked = (cb.value === currentMonthName);
                });
            });

            ['emp', 'area', 'pozo', 'act'].forEach(prefix => {
                const trimSelect = document.getElementById(`${prefix}-trimestre-filter`);
                if (trimSelect) trimSelect.value = prefix === 'act' ? 'Anual' : 'Personalizado';
            });

            const STATS_URL = '{{ route('loadchart.stats.data') }}';

            const periodosMap = {
                'Q1': ['Enero', 'Febrero', 'Marzo'],
                'Q2': ['Abril', 'Mayo', 'Junio'],
                'Q3': ['Julio', 'Agosto', 'Septiembre'],
                'Q4': ['Octubre', 'Noviembre', 'Diciembre'],
                'Anual': ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto',
                    'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                ]
            };

            const monthKeysMap = {
                'Enero': ['ene1', 'ene2'], 'Febrero': ['feb1', 'feb2'], 'Marzo': ['mar1', 'mar2'],
                'Abril': ['abr1', 'abr2'], 'Mayo': ['may1', 'may2'], 'Junio': ['jun1', 'jun2'],
                'Julio': ['jul1', 'jul2'], 'Agosto': ['ago1', 'ago2'], 'Septiembre': ['sep1', 'sep2'],
                'Octubre': ['oct1', 'oct2'], 'Noviembre': ['nov1', 'nov2'], 'Diciembre': ['dic1', 'dic2']
            };

            let empleadosData = [];
            let areasData = [];
            let areasDataCompare = null;
            let pozosData = [];
            let quincenasData = [];
            let mesData = [];
            let actividadesData = [];
            let actividadesPorEmpleado = {};

            let serviciosData = [];
            let suministrosData = [];

            let evChart = null;
            let actChart = null;
            let pieChartTotal = null;
            let pieChartUtil = null;
            let pozosChart = null;
            let servSuminChart = null;

            function parseCurrency(str) {
                return parseFloat(str.replace(/[$,]/g, '')) || 0;
            }

            function formatMxn(amount) {
                if (amount === null || amount === undefined || amount === '') return '';
                const num = parseFloat(amount);
                if (isNaN(num)) return amount;
                return `$${new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(num)}`;
            }

            function formatUsd(amount) {
                if (amount === null || amount === undefined || amount === '') return '';
                const num = parseFloat(amount);
                if (isNaN(num)) return amount;
                return `$${new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(num)}`;
            }

            function format1Dec(num) {
                return parseFloat(num).toFixed(1);
            }

            function makeTableSortable(tableId) {
                const table = document.getElementById(tableId);
                if (!table) return;

                const theadRows = table.querySelectorAll('thead tr');
                if (theadRows.length === 0) return;

                let matrix = [];
                theadRows.forEach((row, rIdx) => {
                    let cIdx = 0;
                    Array.from(row.children).forEach(th => {
                        while (matrix[rIdx] && matrix[rIdx][cIdx]) cIdx++;
                        th.dataset.colTarget = cIdx;
                        for (let r = 0; r < (th.rowSpan || 1); r++) {
                            for (let c = 0; c < (th.colSpan || 1); c++) {
                                if (!matrix[rIdx + r]) matrix[rIdx + r] = [];
                                matrix[rIdx + r][cIdx + c] = true;
                            }
                        }
                        cIdx += (th.colSpan || 1);
                    });
                });

                table.querySelectorAll('thead th').forEach(th => {
                    if (th.colSpan > 1) return;

                    th.classList.add('sortable-header');
                    th.title = "Clic para ordenar";
                    if (!th.innerHTML.includes('↕') && !th.innerHTML.includes('↓') && !th.innerHTML.includes('↑')) {
                        th.innerHTML += ' <span style="font-size:1.2em; opacity:1; color:#d67e29; margin-left:4px;">↕</span>';
                    }

                    const newTh = th.cloneNode(true);
                    th.parentNode.replaceChild(newTh, th);

                    newTh.addEventListener('click', function() {
                        const tbody = table.querySelector('tbody');
                        const rows = Array.from(tbody.querySelectorAll('tr:not(.empty-row)'));
                        if (rows.length === 0) return;

                        const targetColIdx = parseInt(newTh.dataset.colTarget);
                        const isAscending = newTh.classList.contains('asc');

                        table.querySelectorAll('th.sortable-header').forEach(h => {
                            h.classList.remove('asc', 'desc');
                            h.innerHTML = h.innerHTML.replace(/<span.*<\/span>/g, '<span style="font-size:1.2em; opacity:1; color:#d67e29; margin-left:4px;">↕</span>');
                        });

                        newTh.classList.toggle('asc', !isAscending);
                        newTh.classList.toggle('desc', isAscending);
                        newTh.innerHTML = newTh.innerHTML.replace(/<span.*<\/span>/g, isAscending ? '<span style="font-size:1.2em; opacity:1; color:#d67e29; margin-left:4px;">↓</span>' : '<span style="font-size:1.2em; opacity:1; color:#d67e29; margin-left:4px;">↑</span>');

                        rows.sort((a, b) => {
                            let aCell = a.children[targetColIdx];
                            let bCell = b.children[targetColIdx];
                            let aText = aCell ? aCell.textContent.trim() : '';
                            let bText = bCell ? bCell.textContent.trim() : '';

                            let aVal = parseFloat(aText.replace(/[$,%]/g, ''));
                            let bVal = parseFloat(bText.replace(/[$,%]/g, ''));

                            if (!isNaN(aVal) && !isNaN(bVal)) {
                                return isAscending ? bVal - aVal : aVal - bVal;
                            }
                            return isAscending ? bText.localeCompare(aText) : aText.localeCompare(bText);
                        });

                        tbody.append(...rows);
                    });
                });
            }

            window.filterModalTable = function(containerId, value, modalType) {
                const term = value.toLowerCase();
                const container = document.getElementById(containerId);
                const rows = container.querySelectorAll('tbody tr:not(.empty-row)');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(term) ? '' : 'none';
                });

                if (modalType === 'history') {
                    updateHistoryModalTotals();
                } else if (modalType === 'act') {
                    updateActModalTotals();
                }
            };

            function updateHistoryModalTotals() {
                const tables = document.querySelectorAll('#history-modal-tables table');
                let totalGral = 0;
                let totalNormal = 0;
                let totalComision = 0;
                let rowCount = 0;

                tables.forEach((table, index) => {
                    let tableTotal = 0;
                    const rows = table.querySelectorAll('tbody tr:not(.empty-row)');
                    rows.forEach(row => {
                        if (row.style.display !== 'none') {
                            const cells = row.querySelectorAll('td');
                            const amountCell = cells[cells.length - 1];
                            const val = parseCurrency(amountCell.textContent);
                            tableTotal += val;
                            rowCount++;
                        }
                    });

                    const footerTotalCell = table.querySelector('tfoot .footer-total-amount');
                    if(footerTotalCell) footerTotalCell.textContent = formatMxn(tableTotal);

                    if (tables.length > 1) {
                        if (index === 0) totalNormal += tableTotal;
                        if (index === 1) totalComision += tableTotal;
                    } else {
                        totalNormal += tableTotal;
                    }
                });

                totalGral = totalNormal + totalComision;

                const cardsContainer = document.getElementById('history-modal-cards');
                const strongs = cardsContainer.querySelectorAll('strong');
                if (strongs.length === 3) {
                    if (cardsContainer.innerHTML.includes('Monto Normal')) {
                        strongs[0].textContent = formatMxn(totalNormal);
                        strongs[1].textContent = formatMxn(totalComision);
                        strongs[2].textContent = formatMxn(totalGral);
                    } else if (cardsContainer.innerHTML.includes('Total de Registros')) {
                        strongs[0].textContent = rowCount;
                        strongs[1].textContent = formatMxn(totalGral);
                    }
                } else if (strongs.length === 2) {
                     strongs[0].textContent = rowCount;
                     strongs[1].textContent = formatMxn(totalGral);
                } else if (strongs.length === 1) {
                    strongs[0].textContent = formatMxn(totalGral);
                }
            }

            function updateActModalTotals() {
                const tbody = document.getElementById('act-modal-tbody');
                const tfoot = document.getElementById('act-modal-tfoot');
                const rows = tbody.querySelectorAll('tr:not(.empty-row)');

                let tB = 0, tP = 0, tC = 0, tTC = 0, tV = 0, tD = 0, tVAC = 0, tM = 0, tE = 0, tA = 0, tPE = 0;

                rows.forEach(row => {
                    if (row.style.display !== 'none') {
                        const cells = row.querySelectorAll('td');
                        tB += parseInt(cells[2].textContent) || 0;
                        tP += parseInt(cells[3].textContent) || 0;
                        tC += parseInt(cells[4].textContent) || 0;
                        tTC += parseInt(cells[5].textContent) || 0;
                        tV += parseInt(cells[6].textContent) || 0;
                        tD += parseInt(cells[7].textContent) || 0;
                        tVAC += parseInt(cells[8].textContent) || 0;
                        tM += parseInt(cells[9].textContent) || 0;
                        tE += parseInt(cells[10].textContent) || 0;
                        tA += parseInt(cells[11].textContent) || 0;
                        tPE += parseInt(cells[12].textContent) || 0;
                    }
                });

                const tTotal = tB + tP + tC + tTC + tV + tD + tVAC + tM + tE + tA + tPE;
                const tUtil = tB + tP + tC + tTC + tV + tE; // Solo Base, Pozo, Viaje, Entrena, Trabajo Casa y Comision
                const tPct = tTotal > 0 ? Math.round((tUtil / tTotal) * 100) : 0;

                if(tfoot.querySelector('.total-row')) {
                    const footCells = tfoot.querySelectorAll('.total-row td');
                    footCells[1].textContent = tB;
                    footCells[2].textContent = tP;
                    footCells[3].textContent = tC;
                    footCells[4].textContent = tTC;
                    footCells[5].textContent = tV;
                    footCells[6].textContent = tD;
                    footCells[7].textContent = tVAC;
                    footCells[8].textContent = tM;
                    footCells[9].textContent = tE;
                    footCells[10].textContent = tA;
                    footCells[11].textContent = tPE;
                    footCells[12].textContent = tTotal;
                    footCells[14].textContent = tPct + '%';
                }

                const summaryDivs = document.querySelectorAll('#act-modal-summary > div');
                if (summaryDivs.length === 12) {
                    summaryDivs[0].querySelector('div:nth-child(2)').textContent = tB;
                    summaryDivs[1].querySelector('div:nth-child(2)').textContent = tP;
                    summaryDivs[2].querySelector('div:nth-child(2)').textContent = tC;
                    summaryDivs[3].querySelector('div:nth-child(2)').textContent = tTC;
                    summaryDivs[4].querySelector('div:nth-child(2)').textContent = tV;
                    summaryDivs[5].querySelector('div:nth-child(2)').textContent = tD;
                    summaryDivs[6].querySelector('div:nth-child(2)').textContent = tVAC;
                    summaryDivs[7].querySelector('div:nth-child(2)').textContent = tM;
                    summaryDivs[8].querySelector('div:nth-child(2)').textContent = tE;
                    summaryDivs[9].querySelector('div:nth-child(2)').textContent = tA;
                    summaryDivs[10].querySelector('div:nth-child(2)').textContent = tPE;
                    summaryDivs[11].querySelector('div:nth-child(2)').textContent = tPct + '%';
                }
            }


            // ──────────────────────────────────────────────────────────────
            // ⭐ FUNCIÓN GLOBAL PARA RENDERIZAR TARJETAS DE RESUMEN
            // ──────────────────────────────────────────────────────────────

            function renderSummaryCards(containerId, B, P, C, TC, V, D, VAC, M, E, A, PE, Total, Util) {
                const pct = Total > 0 ? Math.round((Util / Total) * 100) : 0;
                const summaryColors = [
                    { label: 'B', val: B, bg: 'var(--work-base)' },
                    { label: 'P', val: P, bg: 'var(--work-well)' },
                    { label: 'C', val: C, bg: 'var(--commissioned)' },
                    { label: 'TC', val: TC, bg: 'var(--home-office)' },
                    { label: 'V', val: V, bg: 'var(--traveling)' },
                    { label: 'D', val: D, bg: 'var(--rest)' },
                    { label: 'VAC', val: VAC, bg: 'var(--vacation)' },
                    { label: 'M', val: M, bg: 'var(--medical)' },
                    { label: 'E', val: E, bg: 'var(--training)' },
                    { label: 'A', val: A, bg: 'var(--absence)' },
                    { label: 'PE', val: PE, bg: 'var(--permission)' }
                ];

                document.getElementById(containerId).innerHTML = summaryColors.map(s => `
                    <div style="flex: 1; min-width: 60px; background: #f8fafc; padding: 8px 10px; border-radius: 8px; border: 2px solid ${s.bg}; border-left-width: 6px;">
                        <div style="font-size: 11px; font-weight: 700; color: ${s.bg}; text-transform: uppercase; margin-bottom: 4px;">${s.label}</div>
                        <div style="font-size: 16px; font-weight: 700; color: ${s.bg};">${s.val}</div>
                    </div>
                `).join('') + `
                    <div style="flex: 1; min-width: 100px; background: #f1f5f9; padding: 8px 10px; border-radius: 8px; border: 1px solid #cbd5e1;">
                        <div style="font-size: 11px; font-weight: 700; color: var(--text-medium); text-transform: uppercase; margin-bottom: 4px;">% UTIL.</div>
                        <div style="font-size: 16px; font-weight: 700; color: var(--secondary-color);">${pct}%</div>
                    </div>
                `;
            }


            // ──────────────────────────────────────────────────────────────
            // MODAL ACTIVIDADES POR EMPLEADO
            // ──────────────────────────────────────────────────────────────

            window.openActModal = function(mesNombre) {
                document.getElementById('act-search').value = '';
                document.getElementById('act-modal-title').textContent = mesNombre.toUpperCase();

                const empDataArray = actividadesPorEmpleado[mesNombre] || [];
                const tbody = document.getElementById('act-modal-tbody');
                const tfoot = document.getElementById('act-modal-tfoot');
                tbody.innerHTML = '';

                let tB = 0, tP = 0, tC = 0, tTC = 0, tV = 0, tD = 0, tVAC = 0, tM = 0, tE = 0, tA = 0, tPE = 0;

                const empleadosOrdenados = empDataArray.sort((a, b) => {
                    const totA = a.B + a.P + a.C + a.TC + a.V + a.D + a.VAC + a.M + a.E + a.A + a.PE;
                    const totB = b.B + b.P + b.C + b.TC + b.V + b.D + b.VAC + b.M + b.E + b.A + b.PE;
                    return totB - totA;
                });

                empleadosOrdenados.forEach(emp => {
                    const total = emp.B + emp.P + emp.C + emp.TC + emp.V + emp.D + emp.VAC + emp.M + emp.E + emp.A + emp.PE;
                    const util = emp.B + emp.P + emp.C + emp.TC + emp.V + emp.E;
                    const pctEmp = total > 0 ? Math.round((util / total) * 100) : 0;

                    tB += emp.B; tP += emp.P; tC += emp.C; tTC += emp.TC; tV += emp.V;
                    tD += emp.D; tVAC += emp.VAC; tM += emp.M; tE += emp.E; tA += emp.A; tPE += emp.PE;

                    const pctColor = pctEmp >= 80 ? '#16a34a' : pctEmp >= 60 ? '#d97706' : '#dc2626';

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="sticky-col text-left" style="font-size:0.8rem;"><strong>${emp.nombre}</strong></td>
                        <td class="text-left"><span class="badge-area">${emp.area}</span></td>
                        <td class="text-center">${emp.B > 0 ? emp.B : '-'}</td>
                        <td class="text-center">${emp.P > 0 ? emp.P : '-'}</td>
                        <td class="text-center">${emp.C > 0 ? emp.C : '-'}</td>
                        <td class="text-center">${emp.TC > 0 ? emp.TC : '-'}</td>
                        <td class="text-center">${emp.V > 0 ? emp.V : '-'}</td>
                        <td class="text-center">${emp.D > 0 ? emp.D : '-'}</td>
                        <td class="text-center">${emp.VAC > 0 ? emp.VAC : '-'}</td>
                        <td class="text-center">${emp.M > 0 ? emp.M : '-'}</td>
                        <td class="text-center">${emp.E > 0 ? emp.E : '-'}</td>
                        <td class="text-center">${emp.A > 0 ? emp.A : '-'}</td>
                        <td class="text-center">${emp.PE > 0 ? emp.PE : '-'}</td>
                        <td class="text-center" style="background:#f1f5f9; font-weight:700;">${total}</td>
                        <td class="text-center" style="background:#f1f5f9; font-weight:700; color:${pctColor};">${pctEmp}%</td>
                    `;
                    tbody.appendChild(tr);
                });

                if (!tbody.children.length) {
                    tbody.innerHTML = '<tr class="empty-row"><td colspan="15">No hay detalle de empleados para este mes.</td></tr>';
                    tfoot.innerHTML = '';
                    document.getElementById('act-modal-summary').innerHTML = '';
                } else {
                    const tTotal = tB + tP + tC + tTC + tV + tD + tVAC + tM + tE + tA + tPE;
                    const tUtil = tB + tP + tC + tTC + tV + tE;
                    const tPct = tTotal > 0 ? Math.round((tUtil / tTotal) * 100) : 0;

                    tfoot.innerHTML = `
                        <tr class="total-row">
                            <td class="sticky-col text-right" colspan="2">TOTALES</td>
                            <td class="text-center">${tB}</td>
                            <td class="text-center">${tP}</td>
                            <td class="text-center">${tC}</td>
                            <td class="text-center">${tTC}</td>
                            <td class="text-center">${tV}</td>
                            <td class="text-center">${tD}</td>
                            <td class="text-center">${tVAC}</td>
                            <td class="text-center">${tM}</td>
                            <td class="text-center">${tE}</td>
                            <td class="text-center">${tA}</td>
                            <td class="text-center">${tPE}</td>
                            <td class="text-center" style="color:var(--text-dark);">${tTotal}</td>
                            <td class="text-center" style="color:var(--text-dark);">${tPct}%</td>
                        </tr>
                    `;
                    renderSummaryCards('act-modal-summary', tB, tP, tC, tTC, tV, tD, tVAC, tM, tE, tA, tPE, tTotal, tUtil);
                }

                document.getElementById('actividadesModal').classList.add('show');
                makeTableSortable('act-modal-table-data');
            };

            window.closeActModal = function() {
                document.getElementById('actividadesModal').classList.remove('show');
            };

            // ──────────────────────────────────────────────────────────────
            // MODAL HISTORIAL (BONOS, SERVICIOS, SUMINISTROS)
            // ──────────────────────────────────────────────────────────────

            window.openHistoryModal = function(type, id, key, title) {
                document.getElementById('history-search').value = '';

                let normalDetails = [];
                let comisionDetails = [];
                let totalNormal = 0;
                let totalComision = 0;

                if (type === 'servicios') {
                    const list = key === 'all' ? serviciosData : serviciosData.filter(s => s.qKey === key);
                    list.forEach(s => {
                        normalDetails.push({
                            fecha: s.service_real_date,
                            empleado: s.empleado_nombre,
                            tipo: 'Servicio',
                            descripcion: s.service_name,
                            monto: s.amount,
                            area_origen: 'OPERACIONES'
                        });
                    });
                } else if (type === 'suministros') {
                    if (key === 'all') {
                        Object.values(suministrosData).forEach(arr => {
                            normalDetails = normalDetails.concat(arr);
                        });
                    } else if (suministrosData[key]) {
                        normalDetails = normalDetails.concat(suministrosData[key]);
                    }
                } else if (type === 'empleado') {
                    const emp = empleadosData.find(e => e.id === id);
                    if (!emp) return;
                    let keysToFetch = monthKeysMap[key] ? monthKeysMap[key] : [key];
                    keysToFetch.forEach(k => {
                        if (emp.detalles && emp.detalles[k]) normalDetails = normalDetails.concat(emp
                            .detalles[k]);
                    });
                } else if (type === 'area') {
                    const area = areasData.find(a => a.area === id);
                    if (!area) return;
                    let keysToFetch = monthKeysMap[key] ? monthKeysMap[key] : [key];
                    keysToFetch.forEach(k => {
                        if (area[k] && area[k].normal_detalles) normalDetails = normalDetails.concat(
                            area[k].normal_detalles);
                        if (area[k] && area[k].comisionados_detalles) comisionDetails = comisionDetails
                            .concat(area[k].comisionados_detalles);
                    });
                } else if (type === 'pozo') {
                    const parts = key.split('_');
                    const mes = parts[0];
                    const q = parts[1];
                    const pozoRecords = pozosData.filter(p => p.pozo === id && p.mes === mes);
                    pozoRecords.forEach(pr => {
                        if (!q || (q === 'q1' && pr.quincena === '1RA QUINCENA') || (q === 'q2' && pr
                                .quincena === '2DA QUINCENA')) {
                            if (pr.detalles) normalDetails = normalDetails.concat(pr.detalles);
                        }
                    });
                } else if (type === 'general') {
                    if (key === 'all') {
                        areasData.forEach(area => {
                            Object.keys(monthKeysMap).forEach(m => {
                                monthKeysMap[m].forEach(k => {
                                    if (area[k] && area[k].normal_detalles)
                                        normalDetails = normalDetails.concat(area[k]
                                            .normal_detalles);
                                    if (area[k] && area[k].comisionados_detalles)
                                        comisionDetails = comisionDetails.concat(area[k]
                                            .comisionados_detalles);
                                });
                            });
                        });
                    } else {
                        let keysToFetch = monthKeysMap[key] ? monthKeysMap[key] : [key];
                        areasData.forEach(area => {
                            keysToFetch.forEach(k => {
                                if (area[k] && area[k].normal_detalles) normalDetails =
                                    normalDetails.concat(area[k].normal_detalles);
                                if (area[k] && area[k].comisionados_detalles) comisionDetails =
                                    comisionDetails.concat(area[k].comisionados_detalles);
                            });
                        });
                    }
                }

                if (normalDetails.length === 0 && comisionDetails.length === 0) {
                    showCustomAlert("No hay detalles registrados para esta selección.");
                    return;
                }

                normalDetails.sort((a, b) => new Date(a.fecha) - new Date(b.fecha));
                comisionDetails.sort((a, b) => new Date(a.fecha) - new Date(b.fecha));
                normalDetails.forEach(d => totalNormal += parseFloat(d.monto));
                comisionDetails.forEach(d => totalComision += parseFloat(d.monto));
                const totalGral = totalNormal + totalComision;

                const cardsContainer = document.getElementById('history-modal-cards');

                if (type === 'area' || type === 'general') {
                    cardsContainer.innerHTML = `
                        <div style="flex:1;min-width:150px;background:#f8fafc;padding:15px;border-radius:8px;border:1px solid #e2e8f0;">
                            <span style="font-size:11px;color:#64748b;font-weight:bold;text-transform:uppercase;">Monto Normal</span><br>
                            <strong style="font-size:18px;color:#334c95;">${formatMxn(totalNormal)}</strong>
                        </div>
                        <div style="flex:1;min-width:150px;background:#f0f9ff;padding:15px;border-radius:8px;border:1px solid #bae6fd;">
                            <span style="font-size:11px;color:#0ea5e9;font-weight:bold;text-transform:uppercase;">Monto Comisionado</span><br>
                            <strong style="font-size:18px;color:#0369a1;">${formatMxn(totalComision)}</strong>
                        </div>
                        <div style="flex:1;min-width:150px;background:#f0fdf4;padding:15px;border-radius:8px;border:1px solid #bbf7d0;">
                            <span style="font-size:11px;color:#16a34a;font-weight:bold;text-transform:uppercase;">Total General</span><br>
                            <strong style="font-size:18px;color:#15803d;">${formatMxn(totalGral)}</strong>
                        </div>`;
                } else if (type === 'servicios' || type === 'suministros') {
                    cardsContainer.innerHTML = `
                        <div style="flex:1;min-width:150px;background:#f0f9ff;padding:15px;border-radius:8px;border:1px solid #bae6fd;">
                            <span style="font-size:11px;color:#0ea5e9;font-weight:bold;text-transform:uppercase;">Total de Registros</span><br>
                            <strong style="font-size:18px;color:#0369a1;">${normalDetails.length}</strong>
                        </div>
                        <div style="flex:1;min-width:150px;background:#f0fdf4;padding:15px;border-radius:8px;border:1px solid #bbf7d0;">
                            <span style="font-size:11px;color:#16a34a;font-weight:bold;text-transform:uppercase;">Monto Acumulado</span><br>
                            <strong style="font-size:18px;color:#15803d;">${formatMxn(totalGral)}</strong>
                        </div>`;
                } else {
                    cardsContainer.innerHTML = `
                        <div style="flex:1;min-width:150px;background:#f0fdf4;padding:15px;border-radius:8px;border:1px solid #bbf7d0;">
                            <span style="font-size:11px;color:#16a34a;font-weight:bold;text-transform:uppercase;">Total Acumulado del Periodo</span><br>
                            <strong style="font-size:18px;color:#15803d;">${formatMxn(totalGral)}</strong>
                        </div>`;
                }

                const tablesContainer = document.getElementById('history-modal-tables');
                tablesContainer.innerHTML = '';

                let tableCounter = 0;

                function getTableHtml(ttl, details, showOriginArea) {
                    if (details.length === 0) return '';
                    let rows = '';
                    tableCounter++;
                    let uniqueTableId = 'modal-hist-table-' + tableCounter;

                    let isSuministros = (type === 'suministros');
                    let extraHeaders = isSuministros ?
                        `<th class="text-center" style="background:#e2e8f0;color:var(--text-dark);border-right:none;min-width:100px;">Contrato</th><th class="text-left" style="background:#e2e8f0;color:var(--text-dark);border-right:none;min-width:250px;">Servicio/Viaje</th>` :
                        '';

                    details.forEach((d, idx) => {
                        const f = new Date(d.fecha + 'T12:00:00').toLocaleDateString('es-ES', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric'
                        });
                        let badgeColor = '#64748b';
                        if (d.tipo === 'Comida') badgeColor = '#f59e0b';
                        if (d.tipo === 'Campo') badgeColor = '#10b981';
                        if (d.tipo === 'Servicio') badgeColor = '#3b82f6';
                        if (d.tipo === 'Viaje') badgeColor = '#8b5cf6';

                        let originCol = showOriginArea ?
                            `<td class="text-left" style="background:white;border-bottom:1px solid #e2e8f0;"><span class="badge-area">${d.area_origen||'-'}</span></td>` :
                            '';

                        let extraCols = isSuministros ?
                            `<td class="text-center" style="background:white;border-bottom:1px solid #e2e8f0;"><strong>${d.contrato || '-'}</strong></td><td class="text-left" style="background:white;border-bottom:1px solid #e2e8f0;">${d.tipo_servicio_viaje || '-'}</td>` :
                            '';

                        // ⭐ Etiqueta visual si es continuación o inicio
                        let isCont = '';
                        if (d.tipo === 'Viaje') {
                            if (d.is_continuation) {
                                isCont = `<span style="background:#fef08a; color:#9a3412; padding:2px 6px; border-radius:4px; font-size:10px; font-weight:bold; margin-left:5px;" title="Este viaje es continuación de uno anterior">Continuación</span>`;
                            } else {
                                isCont = `<span style="background:#bbf7d0; color:#166534; padding:2px 6px; border-radius:4px; font-size:10px; font-weight:bold; margin-left:5px;" title="Día en que inicia el viaje">Inicio</span>`;
                            }
                        }

                        rows += `<tr>
                            <td class="text-center" style="background:white;border-bottom:1px solid #e2e8f0;font-weight:bold;color:var(--text-medium);">${idx + 1}</td>
                            <td class="text-left" style="background:white;border-bottom:1px solid #e2e8f0;white-space:nowrap;">${f}</td>
                            ${type !== 'empleado' ? `<td class="text-left" style="background:white;border-bottom:1px solid #e2e8f0;white-space:nowrap;font-size:0.8rem;"><strong>${d.empleado||'-'}</strong></td>` : ''}
                            ${originCol}
                            <td class="text-left" style="background:white;border-bottom:1px solid #e2e8f0;">
                                <span style="background-color:${badgeColor};color:white;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:bold;">${d.tipo}</span>${isCont}
                            </td>
                            <td class="text-left" style="background:white;border-bottom:1px solid #e2e8f0;min-width:200px;">${d.descripcion||'-'}</td>
                            ${extraCols}
                            <td class="text-right currency-mxn" style="background:white;border-bottom:1px solid #e2e8f0;white-space:nowrap;font-weight:bold;">${formatMxn(d.monto)}</td>
                        </tr>`;
                    });

                    let originHeader = showOriginArea ?
                        `<th class="text-left" style="background:#e2e8f0;color:var(--text-dark);border-right:none;">Área Origen</th>` :
                        '';

                    let colCount = 5; // #, Fecha, Tipo, Descripcion, Monto
                    if (type !== 'empleado') colCount++;
                    if (showOriginArea) colCount++;
                    if (isSuministros) colCount += 2;

                    let tfootHtml = `<tfoot><tr class="total-row">
                        <td colspan="${colCount - 1}" class="text-right sticky-col" style="background:#ffffff; z-index:20; left:0;">TOTAL VISIBLE</td>
                        <td class="text-right currency-mxn footer-total-amount" style="background:#ffffff; z-index:20; font-weight:bold;">$0.00</td>
                    </tr></tfoot>`;

                    return `<h6 style="margin-bottom:10px;margin-top:15px;font-weight:bold;color:var(--text-dark);">${ttl}</h6>
                        <div style="border:1px solid var(--border-color);border-radius:6px;max-height:300px;overflow-y:auto;overflow-x:auto;margin-bottom:15px;">
                            <table class="data-table" id="${uniqueTableId}" style="width:100%; min-width: ${isSuministros ? '1200px' : '800px'}; font-size:13px;margin:0;">
                                <thead style="position:sticky;top:0;z-index:1;"><tr>
                                    <th class="text-center" style="background:#e2e8f0;color:var(--text-dark);border-right:none;width:40px;">#</th>
                                    <th class="text-left" style="background:#e2e8f0;color:var(--text-dark);border-right:none;">Fecha</th>
                                    ${type !== 'empleado' ? `<th class="text-left" style="background:#e2e8f0;color:var(--text-dark);border-right:none;">Empleado</th>` : ''}
                                    ${originHeader}
                                    <th class="text-left" style="background:#e2e8f0;color:var(--text-dark);border-right:none;">Tipo</th>
                                    <th class="text-left" style="background:#e2e8f0;color:var(--text-dark);border-right:none;">Descripción</th>
                                    ${extraHeaders}
                                    <th class="text-right" style="background:#e2e8f0;color:var(--text-dark);border-right:none;white-space:nowrap;">Monto</th>
                                </tr></thead>
                                <tbody>${rows}</tbody>
                                ${tfootHtml}
                            </table>
                        </div>`;
                }

                if (type === 'area' || type === 'general') {
                    tablesContainer.innerHTML += getTableHtml('Historial de Bonos Normales', normalDetails, false);
                    tablesContainer.innerHTML += getTableHtml('Historial de Comisionados (Extras)', comisionDetails, true);
                } else if (type === 'servicios') {
                    tablesContainer.innerHTML += getTableHtml('Detalle de Servicios Realizados', normalDetails, false);
                } else if (type === 'suministros') {
                    tablesContainer.innerHTML += getTableHtml('Detalle de Viajes Suministros', normalDetails, false);
                } else {
                    tablesContainer.innerHTML += getTableHtml('Historial de Bonos', normalDetails, false);
                }

                document.getElementById('history-modal-name').textContent = title;
                document.getElementById('historyModal').classList.add('show');
                updateHistoryModalTotals();

                for(let i = 1; i <= tableCounter; i++) {
                    makeTableSortable('modal-hist-table-' + i);
                }
            };

            window.closeHistoryModal = function() {
                document.getElementById('historyModal').classList.remove('show');
            };

            // ──────────────────────────────────────────────────────────────
            // CARGA DE DATOS Y TABS
            // ──────────────────────────────────────────────────────────────

            function moveYearControls() {
                const activeSection = document.querySelector('.table-section.active');
                if (!activeSection) return;
                const header = activeSection.querySelector('.table-header');
                const wrapper = document.getElementById('year-controls-wrapper');
                wrapper.style.display = 'flex';
                let actionsContainer = header.querySelector('.header-actions');
                if (!actionsContainer) {
                    actionsContainer = document.createElement('div');
                    actionsContainer.className = 'header-actions';
                    actionsContainer.style.cssText = 'display:flex;gap:15px;align-items:center;';
                    const exportBtn = header.querySelector('.btn-primary');
                    if (exportBtn) actionsContainer.appendChild(exportBtn);
                    header.appendChild(actionsContainer);
                }
                actionsContainer.insertBefore(wrapper, actionsContainer.firstChild);
            }

            async function loadData(year) {
                document.getElementById('loading-indicator').style.display = 'inline-flex';
                document.getElementById('error-indicator').style.display = 'none';
                document.getElementById('stats-year-label').textContent = year;

                areasDataCompare = null;
                document.getElementById('area-compare-year').value = "";

                try {
                    const r = await fetch(`${STATS_URL}?year=${year}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!r.ok) throw new Error(`HTTP ${r.status}`);
                    const j = await r.json();
                    if (!j.success) throw new Error(j.message || 'Error del servidor');

                    empleadosData = j.empleadosData || [];
                    areasData = j.areasData || [];
                    pozosData = j.pozosData || [];
                    quincenasData = j.quincenasData || [];
                    mesData = j.mesData || [];
                    actividadesData = j.actividadesData || [];
                    actividadesPorEmpleado = j.actividadesPorEmpleado || {};

                    serviciosData = j.serviciosData || [];
                    suministrosData = j.suministrosData || {};

                    const pozosSelect = document.getElementById('pozo-search-filter');
                    const uniquePozos = [...new Set(pozosData.map(p => p.pozo))].sort((a, b) => a.localeCompare(
                        b));
                    pozosSelect.innerHTML = '<option value="TODOS">Todos los Pozos</option>';
                    uniquePozos.forEach(pozo => {
                        pozosSelect.innerHTML += `<option value="${pozo}">${pozo}</option>`;
                    });

                    renderEmpleados();
                    renderAreas();
                    renderPozos();
                    renderEstadisticas();
                    renderActividades();
                    renderUtilizacion();

                    makeTableSortable('empleados-table');
                    makeTableSortable('areas-table');
                    makeTableSortable('pozos-table');
                    makeTableSortable('stat-quincena-master-table');
                    makeTableSortable('stat-mes-consolidado-table');

                    makeTableSortable('actividades-table');
                    makeTableSortable('actividades-q-table');

                    makeTableSortable('utilizacion-table');
                    makeTableSortable('utilizacion-q-table');
                    makeTableSortable('utilizacion-area-table');

                } catch (e) {
                    console.error(e);
                    const el = document.getElementById('error-indicator');
                    el.textContent = '⚠ ' + e.message;
                    el.style.display = 'inline-block';
                } finally {
                    document.getElementById('loading-indicator').style.display = 'none';
                }
            }

            document.getElementById('area-compare-year').addEventListener('change', async function() {
                const cYear = this.value;
                if (!cYear) {
                    areasDataCompare = null;
                    renderAreas();
                    makeTableSortable('areas-table');
                    return;
                }

                document.getElementById('loading-indicator').style.display = 'inline-flex';
                try {
                    const r = await fetch(`${STATS_URL}?year=${cYear}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const j = await r.json();
                    areasDataCompare = j.areasData || [];
                    renderAreas();
                    makeTableSortable('areas-table');
                } catch (e) {
                    console.error("Error cargando comparativa:", e);
                } finally {
                    document.getElementById('loading-indicator').style.display = 'none';
                }
            });

            window.toggleDropdown = function(id, event) {
                event.stopPropagation();
                document.getElementById(id).classList.toggle('show');
            };

            window.onclick = function(event) {
                if (!event.target.closest('.multi-select-container') && !event.target.closest(
                        '.custom-modal-content')) {
                    document.querySelectorAll('.multi-select-options.show').forEach(el => el.classList.remove(
                        'show'));
                    if (event.target.classList.contains('custom-modal-backdrop')) {
                        closeHistoryModal();
                        closeActModal();
                    }
                }
            };

            function updateDropdownText(prefix) {
                const checkboxes = document.querySelectorAll(`.${prefix}-mes-chk:checked`);
                const textSpan = document.getElementById(`${prefix}-meses-text`);
                if (checkboxes.length === 0) textSpan.textContent = 'Ningún mes';
                else if (checkboxes.length === 12) textSpan.textContent = 'Todos los meses';
                else if (checkboxes.length <= 3) textSpan.textContent = Array.from(checkboxes).map(cb => cb.value
                    .substring(0, 3)).join(', ');
                else textSpan.textContent = `${checkboxes.length} meses selec.`;
            }

            function handleTrimestreChange(prefix) {
                const selectVal = document.getElementById(`${prefix}-trimestre-filter`).value;
                if (selectVal !== 'Personalizado') {
                    const meses = periodosMap[selectVal] || [];
                    document.querySelectorAll(`.${prefix}-mes-chk`).forEach(cb => {
                        cb.checked = meses.includes(cb.value);
                    });
                    updateDropdownText(prefix);

                    if (prefix === 'emp') { renderEmpleados(); makeTableSortable('empleados-table'); }
                    if (prefix === 'area') { renderAreas(); makeTableSortable('areas-table'); }
                    if (prefix === 'pozo') { renderPozos(); makeTableSortable('pozos-table'); }
                    if (prefix === 'act') { renderActividades(); makeTableSortable('actividades-table'); makeTableSortable('actividades-q-table'); renderUtilizacion(); makeTableSortable('utilizacion-table'); makeTableSortable('utilizacion-q-table'); makeTableSortable('utilizacion-area-table'); }
                }
            }

            function handleMesCheckboxChange(prefix) {
                document.getElementById(`${prefix}-trimestre-filter`).value = 'Personalizado';
                updateDropdownText(prefix);
                if (prefix === 'emp') { renderEmpleados(); makeTableSortable('empleados-table'); }
                if (prefix === 'area') { renderAreas(); makeTableSortable('areas-table'); }
                if (prefix === 'pozo') { renderPozos(); makeTableSortable('pozos-table'); }
                if (prefix === 'act') { renderActividades(); makeTableSortable('actividades-table'); makeTableSortable('actividades-q-table'); renderUtilizacion(); makeTableSortable('utilizacion-table'); makeTableSortable('utilizacion-q-table'); makeTableSortable('utilizacion-area-table'); }
            }

            document.getElementById('emp-trimestre-filter').addEventListener('change', () => handleTrimestreChange('emp'));
            document.querySelectorAll('.emp-mes-chk').forEach(cb => cb.addEventListener('change', () => handleMesCheckboxChange('emp')));

            document.getElementById('area-trimestre-filter').addEventListener('change', () => handleTrimestreChange('area'));
            document.querySelectorAll('.area-mes-chk').forEach(cb => cb.addEventListener('change', () => handleMesCheckboxChange('area')));

            document.getElementById('pozo-trimestre-filter').addEventListener('change', () => handleTrimestreChange('pozo'));
            document.querySelectorAll('.pozo-mes-chk').forEach(cb => cb.addEventListener('change', () => handleMesCheckboxChange('pozo')));

            document.getElementById('act-trimestre-filter').addEventListener('change', () => handleTrimestreChange('act'));
            document.querySelectorAll('.act-mes-chk').forEach(cb => cb.addEventListener('change', () => handleMesCheckboxChange('act')));


            // ──────────────────────────────────────────────────────────────
            // RENDER EMPLEADOS
            // ──────────────────────────────────────────────────────────────

            function renderEmpleados() {
                const filterArea = document.getElementById('emp-area-filter').value;
                const filterSearch = document.getElementById('emp-search-filter').value.toLowerCase();
                const mesesMostrar = Array.from(document.querySelectorAll('.emp-mes-chk:checked')).map(cb => cb.value);

                const tableThead = document.querySelector('#empleados-table thead');
                const tbody = document.getElementById('empleados-tbody');
                const tfoot = document.getElementById('empleados-tfoot');

                if (mesesMostrar.length === 0) {
                    tableThead.innerHTML = '<tr><th>No hay meses seleccionados</th></tr>';
                    tbody.innerHTML =
                        '<tr class="empty-row"><td colspan="20">Selecciona al menos un mes en los filtros.</td></tr>';
                    tfoot.innerHTML = '';
                    return;
                }

                let theadHTML1 = `<tr>
                    <th rowspan="2" class="sticky-col text-center" style="left:0; z-index:20; min-width:30px;">#</th>
                    <th rowspan="2" class="sticky-col text-left" style="left:40px; z-index:20;">CLAVE</th>
                    <th rowspan="2" class="sticky-col text-left" style="left:120px; z-index:20; min-width:200px;">NOMBRE</th>
                    <th rowspan="2" class="text-left">ÁREA</th>`;
                let theadHTML2 = `<tr class="sub-header">`;
                mesesMostrar.forEach(mes => {
                    theadHTML1 += `<th colspan="3">${mes.toUpperCase()}</th>`;
                    theadHTML2 +=
                        `<th>1RA QUINCENA</th><th>2DA QUINCENA</th><th>TOTAL ${mes.substring(0,3).toUpperCase()}</th>`;
                });
                theadHTML1 += `<th rowspan="2">GRAN TOTAL</th></tr>`;
                theadHTML2 += `</tr>`;
                tableThead.innerHTML = theadHTML1 + theadHTML2;

                tbody.innerHTML = '';
                let columnTotals = new Array(mesesMostrar.length * 3 + 1).fill(0);

                const empSorted = [...empleadosData].sort((a, b) => {
                    const totA = mesesMostrar.reduce((s, m) => s + (a[monthKeysMap[m][0]] || 0) + (a[
                        monthKeysMap[m][1]] || 0), 0);
                    const totB = mesesMostrar.reduce((s, m) => s + (b[monthKeysMap[m][0]] || 0) + (b[
                        monthKeysMap[m][1]] || 0), 0);
                    return totB - totA;
                });

                let rowIndex = 1;
                empSorted.forEach(emp => {
                    const matchArea = filterArea === 'TODAS' || emp.area === filterArea;
                    const matchSearch = emp.nombre.toLowerCase().includes(filterSearch) || emp.clave
                        .toLowerCase().includes(filterSearch);
                    if (matchArea && matchSearch) {
                        const tr = document.createElement('tr');
                        let rowHTML = `
                            <td class="sticky-col text-center" style="left:0; z-index:15; font-weight:bold; color:var(--text-medium);">${rowIndex++}</td>
                            <td class="sticky-col text-left" style="left:40px; z-index:15;">${emp.clave}</td>
                            <td class="sticky-col text-left" style="left:120px; z-index:15; font-size:0.8rem;">${emp.nombre}</td>
                            <td class="text-left"><span class="badge-area">${emp.area}</span></td>`;
                        let granTotalEmp = 0;
                        mesesMostrar.forEach((mes, idx) => {
                            const key1 = monthKeysMap[mes][0];
                            const key2 = monthKeysMap[mes][1];
                            const val1 = emp[key1] || 0;
                            const val2 = emp[key2] || 0;
                            const totalM = val1 + val2;
                            granTotalEmp += totalM;
                            columnTotals[idx * 3] += val1;
                            columnTotals[idx * 3 + 1] += val2;
                            columnTotals[idx * 3 + 2] += totalM;
                            const safeName = emp.nombre.replace(/'/g, "\\'");
                            const titleStr = 'title="Clic para auditar historial general"';
                            const attr1 = val1 > 0 ?
                                `class="text-right currency-mxn clickable-bonus" onclick="openHistoryModal('empleado', ${emp.id}, '${key1}', '${safeName}')" ${titleStr}` :
                                `class="text-right currency-mxn"`;
                            const attr2 = val2 > 0 ?
                                `class="text-right currency-mxn clickable-bonus" onclick="openHistoryModal('empleado', ${emp.id}, '${key2}', '${safeName}')" ${titleStr}` :
                                `class="text-right currency-mxn"`;
                            const attrTotal = totalM > 0 ?
                                `class="text-right currency-mxn total-col clickable-bonus" onclick="openHistoryModal('empleado', ${emp.id}, '${mes}', '${safeName}')" ${titleStr}` :
                                `class="text-right currency-mxn total-col"`;
                            rowHTML +=
                                `<td ${attr1}>${val1 > 0 ? formatMxn(val1) : '-'}</td><td ${attr2}>${val2 > 0 ? formatMxn(val2) : '-'}</td><td ${attrTotal}>${totalM > 0 ? formatMxn(totalM) : '-'}</td>`;
                        });
                        columnTotals[columnTotals.length - 1] += granTotalEmp;
                        rowHTML +=
                            `<td class="text-right currency-mxn" style="background-color:#e2e8f0;">${granTotalEmp > 0 ? formatMxn(granTotalEmp) : '-'}</td>`;
                        tr.innerHTML = rowHTML;
                        tbody.appendChild(tr);
                    }
                });

                if (!tbody.children.length) {
                    tbody.innerHTML =
                        `<tr class="empty-row"><td colspan="${mesesMostrar.length*3+5}">Sin resultados.</td></tr>`;
                }

                let tfootHTML =
                    `<tr class="total-row"><td colspan="4" class="text-right sticky-col" style="left:0; z-index:25;">TOTALES</td>`;
                columnTotals.forEach((tot, idx) => {
                    const style = 'color:var(--text-dark);';
                    tfootHTML +=
                        `<td class="text-right currency-mxn" style="${style}">${formatMxn(tot)}</td>`;
                });
                tfoot.innerHTML = tfootHTML + `</tr>`;
            }

            // ──────────────────────────────────────────────────────────────
            // RENDER ÁREAS
            // ──────────────────────────────────────────────────────────────

            function renderAreas() {
                const mesesMostrar = Array.from(document.querySelectorAll('.area-mes-chk:checked')).map(cb => cb
                    .value);
                const thead = document.querySelector('#areas-table thead');
                const tbody = document.getElementById('areas-tbody');
                const tfoot = document.getElementById('areas-tfoot');

                const gYear = document.getElementById('global-year').value;
                const cYear = document.getElementById('area-compare-year').value;
                const isCompareMode = areasDataCompare !== null && cYear !== "";

                if (mesesMostrar.length === 0) {
                    thead.innerHTML = '<tr><th>No hay meses seleccionados</th></tr>';
                    tbody.innerHTML =
                        '<tr class="empty-row"><td colspan="20">Selecciona al menos un mes en los filtros.</td></tr>';
                    tfoot.innerHTML = '';
                    return;
                }

                tbody.innerHTML = '';

                if (isCompareMode) {
                    let theadHTML1 = `<tr><th rowspan="2" class="sticky-col text-left">ÁREA</th>`;
                    let theadHTML2 = `<tr class="sub-header">`;
                    mesesMostrar.forEach(mes => {
                        theadHTML1 += `<th colspan="3">${mes.toUpperCase()}</th>`;
                        theadHTML2 +=
                            `<th title="Año Base">${gYear}</th><th title="Año a Comparar">${cYear}</th><th>Δ%</th>`;
                    });
                    theadHTML1 += `<th colspan="3">GRAN TOTAL</th></tr>`;
                    theadHTML2 += `<th>${gYear}</th><th>${cYear}</th><th>Δ%</th></tr>`;
                    thead.innerHTML = theadHTML1 + theadHTML2;

                    let columnTotals = new Array(mesesMostrar.length * 3 + 3).fill(0);
                    const allAreaNames = new Set([...areasData.map(a => a.area), ...areasDataCompare.map(a => a
                        .area)]);
                    const areasSorted = Array.from(allAreaNames).sort();

                    areasSorted.forEach(areaName => {
                        const tr = document.createElement('tr');
                        let rowHTML = `<td class="sticky-col text-left"><strong>${areaName}</strong></td>`;

                        const areaObj1 = areasData.find(a => a.area === areaName) || {};
                        const areaObj2 = areasDataCompare.find(a => a.area === areaName) || {};

                        let gt1 = 0;
                        let gt2 = 0;

                        mesesMostrar.forEach((mes, idx) => {
                            const k1 = monthKeysMap[mes][0];
                            const k2 = monthKeysMap[mes][1];

                            const q1_y1 = areaObj1[k1] || { normal: 0, comisionado: 0 };
                            const q2_y1 = areaObj1[k2] || { normal: 0, comisionado: 0 };
                            const valY1 = q1_y1.normal + q1_y1.comisionado + q2_y1.normal + q2_y1.comisionado;

                            const q1_y2 = areaObj2[k1] || { normal: 0, comisionado: 0 };
                            const q2_y2 = areaObj2[k2] || { normal: 0, comisionado: 0 };
                            const valY2 = q1_y2.normal + q1_y2.comisionado + q2_y2.normal + q2_y2.comisionado;

                            const diff = valY1 - valY2;
                            const pct = valY2 > 0 ? (diff / valY2) * 100 : (valY1 > 0 ? 100 : 0);
                            const pctClass = pct > 0 ? 'percentage-positive' : (pct < 0 ? 'percentage-negative' : '');
                            const pctTxt = valY2 === 0 && valY1 === 0 ? '-' : (pct > 0 ? '+' : '') + Math.round(pct) + '%';

                            gt1 += valY1;
                            gt2 += valY2;
                            columnTotals[idx * 3] += valY1;
                            columnTotals[idx * 3 + 1] += valY2;

                            rowHTML += `<td class="text-right currency-mxn">${valY1>0?formatMxn(valY1):'-'}</td>`;
                            rowHTML += `<td class="text-right currency-mxn" style="background:#f8fafc;">${valY2>0?formatMxn(valY2):'-'}</td>`;
                            rowHTML += `<td class="text-center ${pctClass}"><strong>${pctTxt}</strong></td>`;
                        });

                        columnTotals[columnTotals.length - 3] += gt1;
                        columnTotals[columnTotals.length - 2] += gt2;

                        const gtDiff = gt1 - gt2;
                        const gtPct = gt2 > 0 ? (gtDiff / gt2) * 100 : (gt1 > 0 ? 100 : 0);
                        const gtPctClass = gtPct > 0 ? 'percentage-positive' : (gtPct < 0 ? 'percentage-negative' : '');
                        const gtPctTxt = gt2 === 0 && gt1 === 0 ? '-' : (gtPct > 0 ? '+' : '') + Math.round(gtPct) + '%';

                        rowHTML += `<td class="text-right currency-mxn total-col">${gt1>0?formatMxn(gt1):'-'}</td>`;
                        rowHTML += `<td class="text-right currency-mxn total-col" style="background:#e2e8f0;">${gt2>0?formatMxn(gt2):'-'}</td>`;
                        rowHTML += `<td class="text-center total-col ${gtPctClass}"><strong>${gtPctTxt}</strong></td>`;

                        tr.innerHTML = rowHTML;
                        tbody.appendChild(tr);
                    });

                    if (!tbody.children.length) tbody.innerHTML =
                        `<tr class="empty-row"><td colspan="${mesesMostrar.length*3+4}">Sin resultados.</td></tr>`;

                    let tfootHTML = `<tr class="total-row"><td class="text-right sticky-col">TOTALES</td>`;
                    columnTotals.forEach((tot, idx) => {
                        const isPctCol = (idx + 1) % 3 === 0;
                        if (isPctCol) {
                            const val1 = columnTotals[idx - 2];
                            const val2 = columnTotals[idx - 1];
                            const diff = val1 - val2;
                            const pct = val2 > 0 ? (diff / val2) * 100 : (val1 > 0 ? 100 : 0);
                            const pctClass = pct > 0 ? 'percentage-positive' : (pct < 0 ? 'percentage-negative' : '');
                            const pctTxt = val2 === 0 && val1 === 0 ? '-' : (pct > 0 ? '+' : '') + Math.round(pct) + '%';
                            tfootHTML += `<td class="text-center ${pctClass}" style="font-size:1.1em;">${pctTxt}</td>`;
                        } else {
                            tfootHTML += `<td class="text-right currency-mxn" style="color:var(--text-dark);">${formatMxn(tot)}</td>`;
                        }
                    });
                    tfoot.innerHTML = tfootHTML + `</tr>`;

                } else {
                    let theadHTML1 = `<tr><th rowspan="2" class="sticky-col text-left">ÁREA</th>`;
                    let theadHTML2 = `<tr class="sub-header">`;
                    mesesMostrar.forEach(mes => {
                        theadHTML1 += `<th colspan="3">${mes.toUpperCase()}</th>`;
                        theadHTML2 +=
                            `<th>1RA QUINCENA</th><th>2DA QUINCENA</th><th>TOTAL ${mes.substring(0,3).toUpperCase()}</th>`;
                    });
                    theadHTML1 += `<th rowspan="2">GRAN TOTAL</th></tr>`;
                    theadHTML2 += `</tr>`;
                    thead.innerHTML = theadHTML1 + theadHTML2;

                    let columnTotals = new Array(mesesMostrar.length * 3 + 1).fill(0);

                    const areasSorted = [...areasData].sort((a, b) => {
                        const totA = mesesMostrar.reduce((s, m) => {
                            const q1 = a[monthKeysMap[m][0]];
                            const q2 = a[monthKeysMap[m][1]];
                            return s + (q1 ? q1.normal + q1.comisionado : 0) + (q2 ? q2.normal + q2
                                .comisionado : 0);
                        }, 0);
                        const totB = mesesMostrar.reduce((s, m) => {
                            const q1 = b[monthKeysMap[m][0]];
                            const q2 = b[monthKeysMap[m][1]];
                            return s + (q1 ? q1.normal + q1.comisionado : 0) + (q2 ? q2.normal + q2
                                .comisionado : 0);
                        }, 0);
                        return totB - totA;
                    });

                    areasSorted.forEach(areaObj => {
                        const tr = document.createElement('tr');
                        let rowHTML =
                            `<td class="sticky-col text-left"><strong>${areaObj.area}</strong></td>`;
                        let granTotalArea = 0;
                        const safeArea = areaObj.area.replace(/'/g, "\\'");
                        mesesMostrar.forEach((mes, idx) => {
                            const k1 = monthKeysMap[mes][0];
                            const k2 = monthKeysMap[mes][1];
                            const q1Data = areaObj[k1] || { normal: 0, comisionado: 0, normal_detalles: [], comisionados_detalles: [] };
                            const q2Data = areaObj[k2] || { normal: 0, comisionado: 0, normal_detalles: [], comisionados_detalles: [] };
                            const val1 = q1Data.normal + q1Data.comisionado;
                            const val2 = q2Data.normal + q2Data.comisionado;
                            const totalM = val1 + val2;
                            granTotalArea += totalM;
                            columnTotals[idx * 3] += val1;
                            columnTotals[idx * 3 + 1] += val2;
                            columnTotals[idx * 3 + 2] += totalM;
                            rowHTML += generateAreaCellHtml(val1, q1Data, safeArea, k1);
                            rowHTML += generateAreaCellHtml(val2, q2Data, safeArea, k2);
                            rowHTML += generateAreaCellHtml(totalM, {
                                comisionado: q1Data.comisionado + q2Data.comisionado
                            }, safeArea, mes, true);
                        });
                        columnTotals[columnTotals.length - 1] += granTotalArea;
                        rowHTML +=
                            `<td class="text-right currency-mxn" style="background-color:#e2e8f0;">${granTotalArea > 0 ? formatMxn(granTotalArea) : '-'}</td>`;
                        tr.innerHTML = rowHTML;
                        tbody.appendChild(tr);
                    });

                    if (!tbody.children.length) tbody.innerHTML =
                        `<tr class="empty-row"><td colspan="${mesesMostrar.length*3+2}">Sin resultados.</td></tr>`;

                    let tfootHTML = `<tr class="total-row"><td class="text-right sticky-col">TOTALES</td>`;
                    columnTotals.forEach((tot, idx) => {
                        const style = 'color:var(--text-dark);';
                        tfootHTML += `<td class="text-right currency-mxn" style="${style}">${formatMxn(tot)}</td>`;
                    });
                    tfoot.innerHTML = tfootHTML + `</tr>`;
                }
            }

            function generateAreaCellHtml(total, data, areaName, key, isTotalMonth = false) {
                if (total === 0)
                    return `<td class="text-right currency-mxn ${isTotalMonth ? 'total-col' : ''}">-</td>`;
                let extraClass = 'clickable-bonus';
                let icon = '';
                if (data.comisionado > 0) {
                    extraClass += ' has-commissioned';
                    icon = `<i class="fas fa-info-circle commissioned-icon"></i> `;
                }
                const dataAttr =
                    ` onclick="openHistoryModal('area', '${areaName}', '${key}', '${areaName}')" title="Clic para auditar historial general y de comisiones"`;
                return `<td class="text-right currency-mxn ${isTotalMonth ? 'total-col' : ''} ${extraClass}" ${dataAttr}>${icon}${formatMxn(total)}</td>`;
            }

            // ──────────────────────────────────────────────────────────────
            // RENDER POZOS
            // ──────────────────────────────────────────────────────────────

            function renderPozos() {
                const filterPozo = document.getElementById('pozo-search-filter').value;
                const mesesMostrar = Array.from(document.querySelectorAll('.pozo-mes-chk:checked')).map(cb => cb.value);
                const thead = document.querySelector('#pozos-table thead');
                const tbody = document.getElementById('pozos-tbody');
                const tfoot = document.getElementById('pozos-tfoot');

                if (mesesMostrar.length === 0) {
                    thead.innerHTML = '<tr><th>No hay meses seleccionados</th></tr>';
                    tbody.innerHTML =
                        '<tr class="empty-row"><td colspan="20">Selecciona al menos un mes en los filtros.</td></tr>';
                    tfoot.innerHTML = '';
                    if (pozosChart) pozosChart.destroy();
                    return;
                }

                let tHeadHTML1 = `<tr>
                    <th rowspan="2" class="sticky-col text-center" style="left:0; z-index:20; min-width:30px;">#</th>
                    <th rowspan="2" class="sticky-col text-left" style="left:40px; z-index:20;">POZO</th>`;
                let tHeadHTML2 = `<tr class="sub-header">`;
                mesesMostrar.forEach(mes => {
                    tHeadHTML1 += `<th colspan="3">${mes.toUpperCase()}</th>`;
                    tHeadHTML2 += `<th>1RA QUINCENA</th><th>2DA QUINCENA</th><th>TOTAL ${mes.substring(0,3).toUpperCase()}</th>`;
                });
                tHeadHTML1 += `<th rowspan="2">GRAN TOTAL</th></tr>`;
                tHeadHTML2 += `</tr>`;
                thead.innerHTML = tHeadHTML1 + tHeadHTML2;

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

                const tbody2 = document.getElementById('pozos-tbody');
                tbody2.innerHTML = '';
                let columnTotals = new Array(mesesMostrar.length * 3 + 1).fill(0);

                let chartDataArr = [];

                const pozosSorted = Object.keys(pozosMap).sort((a, b) => {
                    const totA = mesesMostrar.reduce((s, m) => s + (pozosMap[a][m]?.q1 || 0) + (pozosMap[a][m]?.q2 || 0), 0);
                    const totB = mesesMostrar.reduce((s, m) => s + (pozosMap[b][m]?.q1 || 0) + (pozosMap[b][m]?.q2 || 0), 0);
                    return totB - totA;
                });

                let rowIndex = 1;
                pozosSorted.forEach(pozoName => {
                    if (filterPozo !== 'TODOS' && pozoName !== filterPozo) return;
                    const tr = document.createElement('tr');
                    let rowHTML = `
                        <td class="sticky-col text-center" style="left:0; z-index:15; font-weight:bold; color:var(--text-medium);">${rowIndex++}</td>
                        <td class="sticky-col text-left" style="left:40px; z-index:15;"><strong>${pozoName}</strong></td>`;

                    let granTotalPozo = 0;
                    let hasData = false;
                    const safePozo = pozoName.replace(/'/g, "\\'");

                    mesesMostrar.forEach((mes, idx) => {
                        const val1 = pozosMap[pozoName][mes].q1;
                        const val2 = pozosMap[pozoName][mes].q2;
                        const totalM = val1 + val2;
                        if (totalM > 0) hasData = true;
                        granTotalPozo += totalM;
                        columnTotals[idx * 3] += val1;
                        columnTotals[idx * 3 + 1] += val2;
                        columnTotals[idx * 3 + 2] += totalM;
                        const attr1 = val1 > 0 ?
                            `class="text-right currency-mxn clickable-bonus" onclick="openHistoryModal('pozo','${safePozo}','${mes}_q1','${safePozo}')" title="Clic para auditar"` :
                            `class="text-right currency-mxn"`;
                        const attr2 = val2 > 0 ?
                            `class="text-right currency-mxn clickable-bonus" onclick="openHistoryModal('pozo','${safePozo}','${mes}_q2','${safePozo}')" title="Clic para auditar"` :
                            `class="text-right currency-mxn"`;
                        const attrT = totalM > 0 ?
                            `class="text-right currency-mxn total-col clickable-bonus" onclick="openHistoryModal('pozo','${safePozo}','${mes}','${safePozo}')" title="Clic para auditar"` :
                            `class="text-right currency-mxn total-col"`;
                        rowHTML += `<td ${attr1}>${val1>0?formatMxn(val1):'-'}</td><td ${attr2}>${val2>0?formatMxn(val2):'-'}</td><td ${attrT}>${totalM>0?formatMxn(totalM):'-'}</td>`;
                    });

                    if (hasData) {
                        columnTotals[columnTotals.length - 1] += granTotalPozo;
                        rowHTML += `<td class="text-right currency-mxn" style="background-color:#e2e8f0;">${granTotalPozo>0?formatMxn(granTotalPozo):'-'}</td>`;
                        tr.innerHTML = rowHTML;
                        tbody2.appendChild(tr);

                        chartDataArr.push({ label: pozoName, value: granTotalPozo });
                    }
                });

                if (!tbody2.children.length) {
                    tbody2.innerHTML = `<tr class="empty-row"><td colspan="${mesesMostrar.length*3+3}">Sin resultados.</td></tr>`;
                } else {
                    let tfootHTML = `<tr class="total-row"><td colspan="2" class="text-right sticky-col" style="left:0; z-index:25;">TOTALES</td>`;
                    columnTotals.forEach((tot, idx) => {
                        const style = 'color:var(--text-dark);';
                        tfootHTML += `<td class="text-right currency-mxn" style="${style}">${formatMxn(tot)}</td>`;
                    });
                    document.getElementById('pozos-tfoot').innerHTML = tfootHTML + `</tr>`;
                }

                // Generar Top 10 y Otros
                chartDataArr.sort((a, b) => b.value - a.value);

                let finalLabels = [];
                let finalValues = [];
                let otrosValue = 0;

                chartDataArr.forEach((item, index) => {
                    if (index < 10) {
                        finalLabels.push(item.label);
                        finalValues.push(item.value);
                    } else {
                        otrosValue += item.value;
                    }
                });

                if (otrosValue > 0) {
                    finalLabels.push('OTROS');
                    finalValues.push(otrosValue);
                }

                if (pozosChart) pozosChart.destroy();

                const optionsPozos = {
                    series: [{ name: 'Costo Total (MXN)', data: finalValues }],
                    chart: { type: 'bar', height: 350, toolbar: { show: true } },
                    colors: [function({ value, seriesIndex, dataPointIndex, w }) {
                        return finalLabels[dataPointIndex] === 'OTROS' ? '#94a3b8' : '#2c3e50';
                    }],
                    plotOptions: { bar: { borderRadius: 4, distributed: true, dataLabels: { position: 'top' } } },
                    dataLabels: { enabled: false },
                    legend: { show: false },
                    xaxis: { categories: finalLabels },
                    yaxis: { labels: { formatter: function (val) { return "$" + val.toLocaleString('es-MX'); } } },
                    tooltip: { y: { formatter: function (val) { return "$" + val.toLocaleString('es-MX'); } } }
                };

                pozosChart = new ApexCharts(document.querySelector("#pozosChart"), optionsPozos);
                pozosChart.render();
            }

            // ──────────────────────────────────────────────────────────────
            // RENDER ESTADÍSTICAS
            // ──────────────────────────────────────────────────────────────

            function renderEstadisticas() {
                const tbQuincena = document.getElementById('stat-quincena-master-tbody');
                const tfootQuincena = document.getElementById('stat-quincena-master-tfoot');
                tbQuincena.innerHTML = '';

                let totServ = 0, totSumin = 0, totMxn = 0;

                quincenasData.forEach((q, i) => {
                    const qKey = monthKeysMap[q.mes][q.quincena === '1RA' ? 0 : 1];
                    const classDiff = q.pctDif === null ? '' : q.pctDif >= 0 ? 'percentage-positive' : 'percentage-negative';
                    const sign = (q.pctDif !== null && q.pctDif > 0) ? '+' : '';
                    const pctText = q.pctDif !== null ? `${sign}${q.pctDif}%` : '—';

                    totServ += q.serviciosCount || 0;
                    totSumin += q.suministrosCount || 0;
                    totMxn += q.bonosMxn || 0;

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="text-center">${i+1}</td>
                        <td class="text-left"><strong>${q.periodo}</strong></td>
                        <td class="text-center clickable-cell" title="Ver detalle de Servicios" onclick="openHistoryModal('servicios', null, '${qKey}', 'Servicios Realizados - ${q.periodo}')"><strong>${q.serviciosCount}</strong></td>
                        <td class="text-center clickable-cell" title="Ver detalle de Suministros (Viajes)" onclick="openHistoryModal('suministros', null, '${qKey}', 'Viajes Suministros - ${q.periodo}')"><strong>${q.suministrosCount}</strong></td>
                        <td class="text-right currency-mxn clickable-cell" title="Ver historial general de bonos" onclick="openHistoryModal('general', null, '${qKey}', 'Concentrado General - ${q.periodo}')"><strong>${formatMxn(q.bonosMxn)}</strong></td>
                        <td class="text-center ${classDiff}">${pctText}</td>`;
                    tbQuincena.appendChild(tr);
                });

                tfootQuincena.innerHTML = `
                    <tr class="total-row">
                        <td colspan="2" class="text-right sticky-col">TOTALES</td>
                        <td class="text-center clickable-cell" title="Ver todos los Servicios" onclick="openHistoryModal('servicios', null, 'all', 'Todos los Servicios')" style="color:var(--text-dark);"><strong>${totServ}</strong></td>
                        <td class="text-center clickable-cell" title="Ver todos los Suministros" onclick="openHistoryModal('suministros', null, 'all', 'Todos los Viajes de Suministros')" style="color:var(--text-dark);"><strong>${totSumin}</strong></td>
                        <td class="text-right currency-mxn clickable-cell" title="Ver historial general total" onclick="openHistoryModal('general', null, 'all', 'Concentrado Anual')" style="color:var(--text-dark);"><strong>${formatMxn(totMxn)}</strong></td>
                        <td class="text-center">-</td>
                    </tr>
                `;

                const tbMes = document.getElementById('stat-mes-consolidado-tbody');
                const tfootMes = document.getElementById('stat-mes-consolidado-tfoot');
                tbMes.innerHTML = '';

                let totMesMxn = 0, totMesUsd = 0;

                mesData.forEach(d => {
                    totMesMxn += d.bonosMxn;
                    totMesUsd += d.bonosUsd;

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="text-left"><strong>${d.mes}</strong> <span style="font-size:10px; color:#94a3b8;">(TC: $${format1Dec(d.tc)})</span></td>
                        <td class="text-right currency-mxn clickable-cell" title="Ver detalle MXN" onclick="openHistoryModal('general', null, '${d.mes}', 'Concentrado General: ${d.mes}')"><strong>${d.bonosMxn>0 ? formatMxn(d.bonosMxn) : '-'}</strong></td>
                        <td class="text-right currency-usd clickable-cell" title="Ver detalle referencial USD" onclick="openHistoryModal('general', null, '${d.mes}', 'Concentrado General: ${d.mes}')"><strong>${d.bonosUsd>0 ? formatUsd(d.bonosUsd) : '-'}</strong></td>`;
                    tbMes.appendChild(tr);
                });

                tfootMes.innerHTML = `
                    <tr class="total-row">
                        <td class="text-right sticky-col">TOTALES</td>
                        <td class="text-right currency-mxn clickable-cell" title="Ver todo" onclick="openHistoryModal('general', null, 'all', 'Concentrado Anual')" style="color:var(--text-dark);"><strong>${formatMxn(totMesMxn)}</strong></td>
                        <td class="text-right currency-usd clickable-cell" title="Ver todo" onclick="openHistoryModal('general', null, 'all', 'Concentrado Anual')" style="color:var(--text-dark);"><strong>${formatUsd(totMesUsd)}</strong></td>
                    </tr>
                `;

                if (evChart) evChart.destroy();
                const optionsEv = {
                    series: [{
                        name: 'Bonos MXN',
                        data: mesData.map(d => d.bonosMxn)
                    }, {
                        name: 'Bonos USD (Aprox)',
                        data: mesData.map(d => d.bonosUsd)
                    }],
                    chart: { type: 'bar', height: 380, toolbar: { show: true } },
                    colors: ['#34495e', '#27ae60'],
                    plotOptions: { bar: { horizontal: false, columnWidth: '55%', endingShape: 'rounded' } },
                    dataLabels: { enabled: false },
                    stroke: { show: true, width: 2, colors: ['transparent'] },
                    xaxis: { categories: mesData.map(d => d.mes) },
                    yaxis: { labels: { formatter: function (val) { return "$" + val.toLocaleString('es-MX'); } } },
                    fill: { opacity: 1 },
                    tooltip: { y: { formatter: function (val) { return "$" + val.toLocaleString('es-MX'); } } },
                    legend: { position: 'bottom' }
                };
                evChart = new ApexCharts(document.querySelector("#evolucionChart"), optionsEv);
                evChart.render();

                if (servSuminChart) servSuminChart.destroy();
                const optionsServ = {
                    series: [{
                        name: 'Servicios',
                        data: mesData.map(d => d.serviciosCount)
                    }, {
                        name: 'Viajes Suministros',
                        data: mesData.map(d => d.suministrosCount)
                    }],
                    chart: { type: 'area', height: 350, toolbar: { show: true } },
                    colors: ['#3b82f6', '#f59e0b'],
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 2 },
                    xaxis: { categories: mesData.map(d => d.mes) },
                    yaxis: { labels: { formatter: function (val) { return Math.round(val); } } },
                    tooltip: { y: { formatter: function (val) { return val + " registros"; } } },
                    legend: { position: 'bottom' }
                };
                servSuminChart = new ApexCharts(document.querySelector("#serviciosSuministrosChart"), optionsServ);
                servSuminChart.render();
            }

            // ──────────────────────────────────────────────────────────────
            // ⭐ RENDER ACTIVIDADES (TAB 5 SIN FILTROS) Y LOGICA DE GRAFICA
            // ──────────────────────────────────────────────────────────────

            function renderActividades() {
                const mesesMostrar = Array.from(document.querySelectorAll('.act-mes-chk:checked')).map(cb => cb.value.toUpperCase());

                const tbody = document.getElementById('actividades-tbody');
                const tfoot = document.getElementById('actividades-tfoot');

                const tbodyQ = document.getElementById('actividades-q-tbody');
                const tfootQ = document.getElementById('actividades-q-tfoot');

                tbody.innerHTML = '';
                tbodyQ.innerHTML = '';

                if (mesesMostrar.length === 0) {
                    tbody.innerHTML = '<tr class="empty-row"><td colspan="24">Selecciona al menos un mes en los filtros.</td></tr>';
                    tfoot.innerHTML = '';
                    tbodyQ.innerHTML = '<tr class="empty-row"><td colspan="24">Sin datos.</td></tr>';
                    tfootQ.innerHTML = '';
                    if (actChart) actChart.destroy();
                    return;
                }

                // Variables Acumuladoras Anuales para el Total (Suma de Días Absolutos)
                let yB = 0, yP = 0, yC = 0, yTC = 0, yV = 0, yD = 0, yVAC = 0, yM = 0, yE = 0, yA = 0, yPE = 0;
                let activeMonthsCount = 0;

                // Variables para la tabla Trimestral (Q) - SIEMPRE RENDERIZARÁ LOS 4 AUNQUE ESTÉN VACÍOS
                const qMap = {
                    'Q1': { meses: ['ENERO','FEBRERO','MARZO'], abs: {B:0,P:0,C:0,TC:0,V:0,D:0,VAC:0,M:0,E:0,A:0,PE:0}, avg: {B:0,P:0,C:0,TC:0,V:0,D:0,VAC:0,M:0,E:0,A:0,PE:0} },
                    'Q2': { meses: ['ABRIL','MAYO','JUNIO'], abs: {B:0,P:0,C:0,TC:0,V:0,D:0,VAC:0,M:0,E:0,A:0,PE:0}, avg: {B:0,P:0,C:0,TC:0,V:0,D:0,VAC:0,M:0,E:0,A:0,PE:0} },
                    'Q3': { meses: ['JULIO','AGOSTO','SEPTIEMBRE'], abs: {B:0,P:0,C:0,TC:0,V:0,D:0,VAC:0,M:0,E:0,A:0,PE:0}, avg: {B:0,P:0,C:0,TC:0,V:0,D:0,VAC:0,M:0,E:0,A:0,PE:0} },
                    'Q4': { meses: ['OCTUBRE','NOVIEMBRE','DICIEMBRE'], abs: {B:0,P:0,C:0,TC:0,V:0,D:0,VAC:0,M:0,E:0,A:0,PE:0}, avg: {B:0,P:0,C:0,TC:0,V:0,D:0,VAC:0,M:0,E:0,A:0,PE:0} }
                };

                // Variables para graficar los meses individuales
                const labelsMes = [];
                const dsBase = [], dsPozo = [], dsCom = [], dsCasa = [], dsViaje = [];
                const dsDesc = [], dsVac = [], dsMed = [], dsEntre = [], dsAus = [], dsPerm = [];

                mesesMostrar.forEach(mesName => {
                    const mesOriginal = Object.keys(actividadesPorEmpleado).find(k => k.toUpperCase() === mesName) || mesName;
                    const empList = actividadesPorEmpleado[mesOriginal] || [];

                    let mB = 0, mP = 0, mC = 0, mTC = 0, mV = 0, mD = 0, mVAC = 0, mM = 0, mE = 0, mA = 0, mPE = 0;
                    let hcMes = 0;

                    // Acumulamos TODOS los empleados sin filtros adicionales para Tab 5
                    empList.forEach(emp => {
                        mB += emp.B; mP += emp.P; mC += emp.C; mTC += emp.TC; mV += emp.V;
                        mD += emp.D; mVAC += emp.VAC; mM += emp.M; mE += emp.E; mA += emp.A; mPE += emp.PE;
                        hcMes++;
                    });

                    const totalMes = mB + mP + mC + mTC + mV + mD + mVAC + mM + mE + mA + mPE;

                    // SIEMPRE SE RENDERIZAN LOS MESES (así estén en ceros)
                    if (totalMes > 0 || hcMes > 0) {
                        activeMonthsCount++;
                    }

                    // Promedios Mensuales por Persona
                    let aB = hcMes > 0 ? mB/hcMes : 0;
                    let aP = hcMes > 0 ? mP/hcMes : 0;
                    let aC = hcMes > 0 ? mC/hcMes : 0;
                    let aTC = hcMes > 0 ? mTC/hcMes : 0;
                    let aV = hcMes > 0 ? mV/hcMes : 0;
                    let aD = hcMes > 0 ? mD/hcMes : 0;
                    let aVAC = hcMes > 0 ? mVAC/hcMes : 0;
                    let aM = hcMes > 0 ? mM/hcMes : 0;
                    let aE = hcMes > 0 ? mE/hcMes : 0;
                    let aA = hcMes > 0 ? mA/hcMes : 0;
                    let aPE = hcMes > 0 ? mPE/hcMes : 0;

                    // % Utilización del mes: Utilizados = Base + Pozo + Viaje + Entrenamiento + TC + Comisionado
                    const utilMes = mB + mP + mC + mTC + mV + mE;
                    const pctMes = totalMes > 0 ? (utilMes / totalMes) * 100 : 0;

                    // Sumatoria ABSOLUTA para el promedio Anual de la Tabla 1
                    yB += mB; yP += mP; yC += mC; yTC += mTC; yV += mV;
                    yD += mD; yVAC += mVAC; yM += mM; yE += mE; yA += mA; yPE += mPE;

                    // Guardar para gráfica si es mes a mes
                    labelsMes.push(mesName);
                    dsBase.push(mB); dsPozo.push(mP); dsCom.push(mC); dsCasa.push(mTC); dsViaje.push(mV);
                    dsDesc.push(mD); dsVac.push(mVAC); dsMed.push(mM); dsEntre.push(mE); dsAus.push(mA); dsPerm.push(mPE);

                    // Asignar al Trimestre correspondiente
                    for (const [qKey, qData] of Object.entries(qMap)) {
                        if (qData.meses.includes(mesName)) {
                            qData.abs.B += mB; qData.abs.P += mP; qData.abs.C += mC; qData.abs.TC += mTC; qData.abs.V += mV;
                            qData.abs.D += mD; qData.abs.VAC += mVAC; qData.abs.M += mM; qData.abs.E += mE; qData.abs.A += mA; qData.abs.PE += mPE;

                            // El promedio del Quarter es la suma de los promedios de sus meses
                            qData.avg.B += aB; qData.avg.P += aP; qData.avg.C += aC; qData.avg.TC += aTC; qData.avg.V += aV;
                            qData.avg.D += aD; qData.avg.VAC += aVAC; qData.avg.M += aM; qData.avg.E += aE; qData.avg.A += aA; qData.avg.PE += aPE;
                        }
                    }

                    // RENDER FILA MENSUAL
                    const tr = document.createElement('tr');
                    tr.classList.add('clickable-act');
                    tr.title = 'Clic para ver detalle por empleado de este mes';
                    tr.addEventListener('click', () => openActModal(mesOriginal));

                    tr.innerHTML = `
                        <td class="sticky-col text-left"><strong>${mesName}</strong></td>
                        <!-- ABSOLUTOS -->
                        <td class="text-center">${mB}</td><td class="text-center">${mP}</td><td class="text-center">${mC}</td>
                        <td class="text-center">${mTC}</td><td class="text-center">${mV}</td><td class="text-center">${mD}</td>
                        <td class="text-center">${mVAC}</td><td class="text-center">${mM}</td><td class="text-center">${mE}</td>
                        <td class="text-center">${mA}</td><td class="text-center">${mPE}</td>
                        <!-- PROMEDIOS -->
                        <td class="text-center col-promedio">${aB.toFixed(1)}</td><td class="text-center col-promedio">${aP.toFixed(1)}</td><td class="text-center col-promedio">${aC.toFixed(1)}</td>
                        <td class="text-center col-promedio">${aTC.toFixed(1)}</td><td class="text-center col-promedio">${aV.toFixed(1)}</td><td class="text-center col-promedio">${aD.toFixed(1)}</td>
                        <td class="text-center col-promedio">${aVAC.toFixed(1)}</td><td class="text-center col-promedio">${aM.toFixed(1)}</td><td class="text-center col-promedio">${aE.toFixed(1)}</td>
                        <td class="text-center col-promedio">${aA.toFixed(1)}</td><td class="text-center col-promedio">${aPE.toFixed(1)}</td>
                        <!-- UTILIZACION -->
                        <td class="text-center" style="font-weight:bold;">${Math.round(pctMes)}%</td>
                    `;
                    tbody.appendChild(tr);
                });

                const yrTotal = yB+yP+yC+yTC+yV+yD+yVAC+yM+yE+yA+yPE;
                const yrUtil = yB+yP+yC+yTC+yV+yE; // Sumatoria de Utilizados Anual
                const yrPct = yrTotal > 0 ? (yrUtil / yrTotal) * 100 : 0;

                // Promedio de promedios para la sección de promedios
                let p_aB = 0, p_aP = 0, p_aC = 0, p_aTC = 0, p_aV = 0, p_aD = 0, p_aVAC = 0, p_aM = 0, p_aE = 0, p_aA = 0, p_aPE = 0;
                let activeQsCount = 0;
                for (const [k, q] of Object.entries(qMap)) {
                    p_aB+=q.avg.B; p_aP+=q.avg.P; p_aC+=q.avg.C; p_aTC+=q.avg.TC; p_aV+=q.avg.V;
                    p_aD+=q.avg.D; p_aVAC+=q.avg.VAC; p_aM+=q.avg.M; p_aE+=q.avg.E; p_aA+=q.avg.A; p_aPE+=q.avg.PE;
                }

                if (activeMonthsCount > 0) {
                    p_aB/=activeMonthsCount; p_aP/=activeMonthsCount; p_aC/=activeMonthsCount; p_aTC/=activeMonthsCount; p_aV/=activeMonthsCount;
                    p_aD/=activeMonthsCount; p_aVAC/=activeMonthsCount; p_aM/=activeMonthsCount; p_aE/=activeMonthsCount; p_aA/=activeMonthsCount; p_aPE/=activeMonthsCount;
                }

                // FOOTER TABLA 1: TOTAL DE DIAS y PROMEDIO
                tfoot.innerHTML = `
                    <tr class="total-row">
                        <td class="sticky-col text-right">TOTAL ANUAL / PROMEDIO</td>
                        <!-- ABSOLUTOS: SUMADOS -->
                        <td class="text-center">${yB}</td><td class="text-center">${yP}</td><td class="text-center">${yC}</td>
                        <td class="text-center">${yTC}</td><td class="text-center">${yV}</td><td class="text-center">${yD}</td>
                        <td class="text-center">${yVAC}</td><td class="text-center">${yM}</td><td class="text-center">${yE}</td>
                        <td class="text-center">${yA}</td><td class="text-center">${yPE}</td>
                        <!-- PROMEDIOS: PROMEDIADOS -->
                        <td class="text-center col-promedio">${p_aB.toFixed(1)}</td><td class="text-center col-promedio">${p_aP.toFixed(1)}</td><td class="text-center col-promedio">${p_aC.toFixed(1)}</td>
                        <td class="text-center col-promedio">${p_aTC.toFixed(1)}</td><td class="text-center col-promedio">${p_aV.toFixed(1)}</td><td class="text-center col-promedio">${p_aD.toFixed(1)}</td>
                        <td class="text-center col-promedio">${p_aVAC.toFixed(1)}</td><td class="text-center col-promedio">${p_aM.toFixed(1)}</td><td class="text-center col-promedio">${p_aE.toFixed(1)}</td>
                        <td class="text-center col-promedio">${p_aA.toFixed(1)}</td><td class="text-center col-promedio">${p_aPE.toFixed(1)}</td>
                        <td class="text-center">${Math.round(yrPct)}%</td>
                    </tr>
                `;

                // PINTAR LAS TARJETAS RESUMEN EN LA VISTA PRINCIPAL (DIAS ABSOLUTOS TOTALES)
                renderSummaryCards('act-main-summary', yB, yP, yC, yTC, yV, yD, yVAC, yM, yE, yA, yPE, yrTotal, yrUtil);

                // ==========================================
                // RENDER TABLA TRIMESTRAL (Q) Y CHART
                // ==========================================
                const labelsQ = [];
                const dsQAbs_B = [], dsQAbs_P = [], dsQAbs_C = [], dsQAbs_TC = [], dsQAbs_V = [];
                const dsQAbs_D = [], dsQAbs_VAC = [], dsQAbs_M = [], dsQAbs_E = [], dsQAbs_A = [], dsQAbs_PE = [];

                for (const [qKey, qData] of Object.entries(qMap)) {
                    let totalAbs = qData.abs.B + qData.abs.P + qData.abs.C + qData.abs.TC + qData.abs.V + qData.abs.D + qData.abs.VAC + qData.abs.M + qData.abs.E + qData.abs.A + qData.abs.PE;
                    let utilAbs = qData.abs.B + qData.abs.P + qData.abs.C + qData.abs.TC + qData.abs.V + qData.abs.E;
                    let pctUtil = totalAbs > 0 ? Math.round((utilAbs / totalAbs) * 100) : 0;

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="sticky-col text-left"><strong>${qKey}</strong></td>
                        <!-- ABSOLUTOS -->
                        <td class="text-center">${qData.abs.B}</td><td class="text-center">${qData.abs.P}</td><td class="text-center">${qData.abs.C}</td>
                        <td class="text-center">${qData.abs.TC}</td><td class="text-center">${qData.abs.V}</td><td class="text-center">${qData.abs.D}</td>
                        <td class="text-center">${qData.abs.VAC}</td><td class="text-center">${qData.abs.M}</td><td class="text-center">${qData.abs.E}</td>
                        <td class="text-center">${qData.abs.A}</td><td class="text-center">${qData.abs.PE}</td>
                        <!-- PROMEDIOS TRIMESTRE -->
                        <td class="text-center col-promedio">${Math.round(qData.avg.B)}</td><td class="text-center col-promedio">${Math.round(qData.avg.P)}</td><td class="text-center col-promedio">${Math.round(qData.avg.C)}</td>
                        <td class="text-center col-promedio">${Math.round(qData.avg.TC)}</td><td class="text-center col-promedio">${Math.round(qData.avg.V)}</td><td class="text-center col-promedio">${Math.round(qData.avg.D)}</td>
                        <td class="text-center col-promedio">${Math.round(qData.avg.VAC)}</td><td class="text-center col-promedio">${Math.round(qData.avg.M)}</td><td class="text-center col-promedio">${Math.round(qData.avg.E)}</td>
                        <td class="text-center col-promedio">${Math.round(qData.avg.A)}</td><td class="text-center col-promedio">${Math.round(qData.avg.PE)}</td>
                        <!-- UTILIZACION -->
                        <td class="text-center" style="font-weight:bold;">${Math.round(pctUtil)}%</td>
                    `;
                    tbodyQ.appendChild(tr);

                    // Guardar para gráfica si es que es anual (agrupado por Q)
                    labelsQ.push(qKey);
                    dsQAbs_B.push(qData.abs.B); dsQAbs_P.push(qData.abs.P); dsQAbs_C.push(qData.abs.C);
                    dsQAbs_TC.push(qData.abs.TC); dsQAbs_V.push(qData.abs.V); dsQAbs_D.push(qData.abs.D);
                    dsQAbs_VAC.push(qData.abs.VAC); dsQAbs_M.push(qData.abs.M); dsQAbs_E.push(qData.abs.E);
                    dsQAbs_A.push(qData.abs.A); dsQAbs_PE.push(qData.abs.PE);
                }

                // TFOOT Tabla 2
                tfootQ.innerHTML = `
                    <tr class="total-row">
                        <td class="sticky-col text-right">TOTAL / PROM. TRIMESTRAL</td>
                        <!-- Sumas Absolutas -->
                        <td class="text-center">${yB}</td><td class="text-center">${yP}</td><td class="text-center">${yC}</td>
                        <td class="text-center">${yTC}</td><td class="text-center">${yV}</td><td class="text-center">${yD}</td>
                        <td class="text-center">${yVAC}</td><td class="text-center">${yM}</td><td class="text-center">${yE}</td>
                        <td class="text-center">${yA}</td><td class="text-center">${yPE}</td>
                        <!-- Promedios -->
                        <td class="text-center col-promedio">${Math.round(p_aB)}</td><td class="text-center col-promedio">${Math.round(p_aP)}</td><td class="text-center col-promedio">${Math.round(p_aC)}</td>
                        <td class="text-center col-promedio">${Math.round(p_aTC)}</td><td class="text-center col-promedio">${Math.round(p_aV)}</td><td class="text-center col-promedio">${Math.round(p_aD)}</td>
                        <td class="text-center col-promedio">${Math.round(p_aVAC)}</td><td class="text-center col-promedio">${Math.round(p_aM)}</td><td class="text-center col-promedio">${Math.round(p_aE)}</td>
                        <td class="text-center col-promedio">${Math.round(p_aA)}</td><td class="text-center col-promedio">${Math.round(p_aPE)}</td>
                        <td class="text-center">-</td>
                    </tr>
                `;

                // ==========================================
                // LÓGICA DE GRÁFICA: Absolutos Anual vs Meses
                // ==========================================
                if (actChart) actChart.destroy();

                let chartCategories = [];
                let dsChart_B = [], dsChart_P = [], dsChart_C = [], dsChart_TC = [], dsChart_V = [];
                let dsChart_D = [], dsChart_VAC = [], dsChart_M = [], dsChart_E = [], dsChart_A = [], dsChart_PE = [];

                // Si están todos los meses seleccionados, mostramos Qs.
                if (mesesMostrar.length === 12) {
                    chartCategories = labelsQ;
                    dsChart_B = dsQAbs_B; dsChart_P = dsQAbs_P; dsChart_C = dsQAbs_C;
                    dsChart_TC = dsQAbs_TC; dsChart_V = dsQAbs_V; dsChart_D = dsQAbs_D;
                    dsChart_VAC = dsQAbs_VAC; dsChart_M = dsQAbs_M; dsChart_E = dsQAbs_E;
                    dsChart_A = dsQAbs_A; dsChart_PE = dsQAbs_PE;
                } else {
                    // Si hay solo un Q o selección parcial, mostramos Mes por Mes
                    chartCategories = labelsMes;
                    dsChart_B = dsBase; dsChart_P = dsPozo; dsChart_C = dsCom;
                    dsChart_TC = dsCasa; dsChart_V = dsViaje; dsChart_D = dsDesc;
                    dsChart_VAC = dsVac; dsChart_M = dsMed; dsChart_E = dsEntre;
                    dsChart_A = dsAus; dsChart_PE = dsPerm;
                }

                const optionsAct = {
                    series: [
                        { name: 'BASE', data: dsChart_B },
                        { name: 'POZO', data: dsChart_P },
                        { name: 'COMISIÓN', data: dsChart_C },
                        { name: 'CASA', data: dsChart_TC },
                        { name: 'VIAJE', data: dsChart_V },
                        { name: 'DESCANSO', data: dsChart_D },
                        { name: 'VACACIONES', data: dsChart_VAC },
                        { name: 'MÉDICO', data: dsChart_M },
                        { name: 'ENTRENA.', data: dsChart_E },
                        { name: 'AUSENCIA', data: dsChart_A },
                        { name: 'PERMISO', data: dsChart_PE }
                    ],
                    chart: { type: 'bar', height: 400, toolbar: { show: true }, stacked: false },
                    colors: ['#334c95', '#6475aa', '#249399', '#7293ff', '#a2b5ff', '#118b20', '#59983e', '#dd840f', '#a5a5a5', '#7d4e4e', '#49c0c9'],
                    plotOptions: { bar: { horizontal: false, columnWidth: '85%' } },
                    dataLabels: { enabled: false },
                    stroke: { show: true, width: 2, colors: ['transparent'] },
                    xaxis: { categories: chartCategories },
                    yaxis: { title: { text: 'Días Totales (Absolutos)' } },
                    fill: { opacity: 1 },
                    tooltip: { y: { formatter: function (val) { return val + " días"; } } },
                    legend: { position: 'bottom' }
                };
                actChart = new ApexCharts(document.querySelector("#actividadesChart"), optionsAct);
                actChart.render();
            }

            // ──────────────────────────────────────────────────────────────
            // ⭐ RENDER TAB 6 (UTILIZACIÓN DEL PERSONAL CATEGORIAS COMPLETAS)
            // ──────────────────────────────────────────────────────────────

            window.renderUtilizacionAreaAndCharts = function() {
                const qFilter = document.getElementById('util-q-filter').value;
                const areaFilter = document.getElementById('util-area-filter').value;
                const tbodyArea = document.getElementById('utilizacion-area-tbody');
                const tfootArea = document.getElementById('utilizacion-area-tfoot');
                tbodyArea.innerHTML = '';
                tfootArea.innerHTML = '';

                let sumB = 0, sumP = 0, sumC = 0, sumTC = 0, sumV = 0, sumD = 0, sumVAC = 0, sumM = 0, sumE = 0, sumA = 0, sumPE = 0;
                let sumTotal = 0, sumUtil = 0;

                const areaMapFiltered = {};
                const mesesTotales = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

                const monthToQ = {
                    'Enero':'Q1', 'Febrero':'Q1', 'Marzo':'Q1',
                    'Abril':'Q2', 'Mayo':'Q2', 'Junio':'Q2',
                    'Julio':'Q3', 'Agosto':'Q3', 'Septiembre':'Q3',
                    'Octubre':'Q4', 'Noviembre':'Q4', 'Diciembre':'Q4'
                };

                mesesTotales.forEach(mesName => {
                    const currentQ = monthToQ[mesName];
                    if (qFilter !== 'TODOS' && currentQ !== qFilter) return;

                    const mesOriginal = Object.keys(actividadesPorEmpleado).find(k => k.toUpperCase() === mesName.toUpperCase()) || mesName;
                    const empList = actividadesPorEmpleado[mesOriginal] || [];

                    empList.forEach(emp => {
                        if (areaFilter !== 'TODAS' && emp.area !== areaFilter) return;

                        if (!areaMapFiltered[emp.area]) {
                            areaMapFiltered[emp.area] = { B:0, P:0, C:0, TC:0, V:0, D:0, VAC:0, M:0, E:0, A:0, PE:0, Total:0, Util:0 };
                        }

                        areaMapFiltered[emp.area].B += emp.B;
                        areaMapFiltered[emp.area].P += emp.P;
                        areaMapFiltered[emp.area].C += emp.C;
                        areaMapFiltered[emp.area].TC += emp.TC;
                        areaMapFiltered[emp.area].V += emp.V;
                        areaMapFiltered[emp.area].D += emp.D;
                        areaMapFiltered[emp.area].VAC += emp.VAC;
                        areaMapFiltered[emp.area].M += emp.M;
                        areaMapFiltered[emp.area].E += emp.E;
                        areaMapFiltered[emp.area].A += emp.A;
                        areaMapFiltered[emp.area].PE += emp.PE;

                        const empTotal = emp.B + emp.P + emp.C + emp.TC + emp.V + emp.D + emp.VAC + emp.M + emp.E + emp.A + emp.PE;
                        const empUtil = emp.B + emp.P + emp.C + emp.TC + emp.V + emp.E;

                        areaMapFiltered[emp.area].Total += empTotal;
                        areaMapFiltered[emp.area].Util += empUtil;

                        sumB += emp.B; sumP += emp.P; sumC += emp.C; sumTC += emp.TC; sumV += emp.V;
                        sumD += emp.D; sumVAC += emp.VAC; sumM += emp.M; sumE += emp.E; sumA += emp.A; sumPE += emp.PE;
                        sumTotal += empTotal; sumUtil += empUtil;
                    });
                });

                let areaHasData = false;
                Object.keys(areaMapFiltered).sort().forEach(area => {
                    const d = areaMapFiltered[area];
                    if (d.Total > 0) {
                        areaHasData = true;
                        const pct = Math.round((d.Util / d.Total) * 100);
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td class="sticky-col text-left"><span class="badge-area">${area}</span></td>
                            <td class="text-center">${d.B}</td><td class="text-center">${d.P}</td><td class="text-center">${d.C}</td><td class="text-center">${d.TC}</td><td class="text-center">${d.V}</td>
                            <td class="text-center">${d.D}</td><td class="text-center">${d.VAC}</td><td class="text-center">${d.M}</td><td class="text-center">${d.E}</td><td class="text-center">${d.A}</td><td class="text-center">${d.PE}</td>
                            <td class="text-center" style="font-weight:bold;">${d.Total}</td>
                            <td class="text-center">${d.Util}</td>
                            <td class="text-center" style="font-weight:bold; color:var(--primary-color);">${pct}%</td>
                        `;
                        tbodyArea.appendChild(tr);
                    }
                });

                if(!areaHasData) {
                    tbodyArea.innerHTML = '<tr class="empty-row"><td colspan="15">Sin datos para los filtros seleccionados.</td></tr>';
                } else {
                    const pctTotal = sumTotal > 0 ? Math.round((sumUtil / sumTotal) * 100) : 0;
                    tfootArea.innerHTML = `
                        <tr class="total-row">
                            <td class="sticky-col text-right">TOTAL FILTRADO</td>
                            <td class="text-center">${sumB}</td><td class="text-center">${sumP}</td><td class="text-center">${sumC}</td><td class="text-center">${sumTC}</td><td class="text-center">${sumV}</td>
                            <td class="text-center">${sumD}</td><td class="text-center">${sumVAC}</td><td class="text-center">${sumM}</td><td class="text-center">${sumE}</td><td class="text-center">${sumA}</td><td class="text-center">${sumPE}</td>
                            <td class="text-center" style="color:var(--text-dark);">${sumTotal}</td>
                            <td class="text-center" style="color:var(--text-dark);">${sumUtil}</td>
                            <td class="text-center" style="color:var(--text-dark);">${pctTotal}%</td>
                        </tr>
                    `;
                }

                if(pieChartTotal) pieChartTotal.destroy();
                if(pieChartUtil) pieChartUtil.destroy();

                if (sumTotal > 0) {
                    const colorPalette = ['#334c95', '#6475aa', '#249399', '#7293ff', '#a2b5ff', '#118b20', '#59983e', '#dd840f', '#a5a5a5', '#7d4e4e', '#49c0c9'];
                    const chartLabels = ['BASE', 'POZO', 'COMISIÓN', 'CASA', 'VIAJE', 'DESCANSO', 'VACACIONES', 'MÉDICO', 'ENTRENA.', 'AUSENCIA', 'PERMISO'];
                    const seriesData = [sumB, sumP, sumC, sumTC, sumV, sumD, sumVAC, sumM, sumE, sumA, sumPE];

                    const qLabel = qFilter === 'TODOS' ? 'Anual' : qFilter;
                    const areaLabel = areaFilter === 'TODAS' ? 'Todas las Áreas' : areaFilter;
                    const pctTotal = sumTotal > 0 ? Math.round((sumUtil / sumTotal) * 100) : 0;

                    const optionsPieTotal = {
                        series: seriesData,
                        labels: chartLabels,
                        chart: {
                            type: 'pie', // <-- Regresamos a pastel (pie)
                            height: 400
                        },
                        colors: colorPalette,
                        title: {
                            text: `Distribución Absoluta (${qLabel} / ${areaLabel})`,
                            align: 'center',
                            style: { fontSize: '14px', color: 'var(--secondary-color)', fontWeight: 'bold' }
                        },
                        legend: { position: 'bottom' },
                        stroke: { show: true, colors: ['#ffffff'], width: 2 },
                        dataLabels: {
                            formatter: function (val) { return Math.round(val) + "%"; },
                            style: { fontSize: '12px', fontWeight: 'bold', colors: ['#ffffff'] },
                            dropShadow: { enabled: true, top: 1, left: 1, blur: 1, opacity: 0.5 }
                        }
                    };
                    pieChartTotal = new ApexCharts(document.querySelector("#pieChartTotal"), optionsPieTotal);
                    pieChartTotal.render();

                    const optionsPieUtil = {
                        series: seriesData,
                        labels: chartLabels,
                        chart: {
                            type: 'donut',
                            height: 400,
                            dropShadow: { enabled: true, color: '#000', top: 1, left: 1, blur: 2, opacity: 0.1 }
                        },
                        colors: colorPalette,
                        title: {
                            text: `Utilización de Personal (${qLabel} / ${areaLabel})`,
                            align: 'center',
                            style: { fontSize: '14px', color: 'var(--secondary-color)', fontWeight: 'bold' }
                        },
                        legend: { position: 'bottom' },
                        stroke: { show: true, colors: ['#ffffff'], width: 2 },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '65%',
                                    labels: {
                                        show: true,
                                        name: { show: true, fontSize: '13px', color: 'var(--text-medium)' },
                                        value: { show: true, fontSize: '22px', fontWeight: 'bold', color: 'var(--primary-color)' },
                                        total: {
                                            show: true,
                                            label: '% Utilización',
                                            color: 'var(--accent-color)',
                                            formatter: function (w) {
                                                return pctTotal + "%";
                                            }
                                        }
                                    }
                                }
                            }
                        },
                        dataLabels: {
                            formatter: function (val) { return Math.round(val) + "%"; },
                            dropShadow: { enabled: false }
                        }
                    };
                    pieChartUtil = new ApexCharts(document.querySelector("#pieChartUtil"), optionsPieUtil);
                    pieChartUtil.render();
                }
            };

            function renderUtilizacion() {
                const tbody = document.getElementById('utilizacion-tbody');
                const tfoot = document.getElementById('utilizacion-tfoot');
                const tbodyQ = document.getElementById('utilizacion-q-tbody');

                tbody.innerHTML = '';
                tfoot.innerHTML = '';
                tbodyQ.innerHTML = '';

                // Siempre mostrar 12 meses
                const mesesTotales = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

                let sumB = 0, sumP = 0, sumC = 0, sumTC = 0, sumV = 0, sumD = 0, sumVAC = 0, sumM = 0, sumE = 0, sumA = 0, sumPE = 0;
                let sumTotal = 0, sumUtil = 0;
                let activeMonthsCount = 0;

                const qUtilMap = {
                    'Q1': { B:0, P:0, C:0, TC:0, V:0, D:0, VAC:0, M:0, E:0, A:0, PE:0, Total:0, Util:0, count:0 },
                    'Q2': { B:0, P:0, C:0, TC:0, V:0, D:0, VAC:0, M:0, E:0, A:0, PE:0, Total:0, Util:0, count:0 },
                    'Q3': { B:0, P:0, C:0, TC:0, V:0, D:0, VAC:0, M:0, E:0, A:0, PE:0, Total:0, Util:0, count:0 },
                    'Q4': { B:0, P:0, C:0, TC:0, V:0, D:0, VAC:0, M:0, E:0, A:0, PE:0, Total:0, Util:0, count:0 }
                };
                const monthToQ = {
                    'Enero':'Q1', 'Febrero':'Q1', 'Marzo':'Q1',
                    'Abril':'Q2', 'Mayo':'Q2', 'Junio':'Q2',
                    'Julio':'Q3', 'Agosto':'Q3', 'Septiembre':'Q3',
                    'Octubre':'Q4', 'Noviembre':'Q4', 'Diciembre':'Q4'
                };

                const uniqueAreas = new Set();

                mesesTotales.forEach(mesName => {
                    const mesOriginal = Object.keys(actividadesPorEmpleado).find(k => k.toUpperCase() === mesName.toUpperCase()) || mesName;
                    const empList = actividadesPorEmpleado[mesOriginal] || [];

                    let mB = 0, mP = 0, mC = 0, mTC = 0, mV = 0, mD = 0, mVAC = 0, mM = 0, mE = 0, mA = 0, mPE = 0;
                    let hcMes = 0;

                    empList.forEach(emp => {
                        mB += emp.B; mP += emp.P; mC += emp.C; mTC += emp.TC; mV += emp.V;
                        mD += emp.D; mVAC += emp.VAC; mM += emp.M; mE += emp.E; mA += emp.A; mPE += emp.PE;
                        hcMes++;
                        uniqueAreas.add(emp.area);
                    });

                    const totalMes = mB + mP + mC + mTC + mV + mD + mVAC + mM + mE + mA + mPE;

                    // Para los promedios anuales en TFOOT solo tomamos meses activos
                    if (totalMes > 0 || hcMes > 0) {
                        activeMonthsCount++;
                    }

                    const utilMes = mB + mP + mC + mTC + mV + mE;
                    const pctMes = totalMes > 0 ? (utilMes / totalMes) * 100 : 0;

                    // Q AGGREGATION (SIEMPRE LO HACEMOS, AUNQUE SEAN 0)
                    const qKey = monthToQ[mesName];
                    if (qKey) {
                        qUtilMap[qKey].B += mB; qUtilMap[qKey].P += mP; qUtilMap[qKey].C += mC;
                        qUtilMap[qKey].TC += mTC; qUtilMap[qKey].V += mV; qUtilMap[qKey].D += mD;
                        qUtilMap[qKey].VAC += mVAC; qUtilMap[qKey].M += mM; qUtilMap[qKey].E += mE;
                        qUtilMap[qKey].A += mA; qUtilMap[qKey].PE += mPE;
                        qUtilMap[qKey].Total += totalMes;
                        qUtilMap[qKey].Util += utilMes;
                        qUtilMap[qKey].count++;
                    }

                    // Acumular para totales del año
                    sumB += mB; sumP += mP; sumC += mC; sumTC += mTC; sumV += mV;
                    sumD += mD; sumVAC += mVAC; sumM += mM; sumE += mE; sumA += mA; sumPE += mPE;
                    sumTotal += totalMes; sumUtil += utilMes;

                    let aB = hcMes > 0 ? mB/hcMes : 0;
                    let aP = hcMes > 0 ? mP/hcMes : 0;
                    let aC = hcMes > 0 ? mC/hcMes : 0;
                    let aTC = hcMes > 0 ? mTC/hcMes : 0;
                    let aV = hcMes > 0 ? mV/hcMes : 0;
                    let aD = hcMes > 0 ? mD/hcMes : 0;
                    let aVAC = hcMes > 0 ? mVAC/hcMes : 0;
                    let aM = hcMes > 0 ? mM/hcMes : 0;
                    let aE = hcMes > 0 ? mE/hcMes : 0;
                    let aA = hcMes > 0 ? mA/hcMes : 0;
                    let aPE = hcMes > 0 ? mPE/hcMes : 0;

                    // RENDER SIEMPRE LA FILA DEL MES, ASÍ ESTÉ EN 0
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="sticky-col text-left"><strong>${mesName.toUpperCase()}</strong></td>
                        <td class="text-center">${mB}</td><td class="text-center">${mP}</td><td class="text-center">${mC}</td><td class="text-center">${mTC}</td><td class="text-center">${mV}</td>
                        <td class="text-center">${mD}</td><td class="text-center">${mVAC}</td><td class="text-center">${mM}</td><td class="text-center">${mE}</td><td class="text-center">${mA}</td><td class="text-center">${mPE}</td>
                        <td class="text-center col-promedio">${aB.toFixed(1)}</td><td class="text-center col-promedio">${aP.toFixed(1)}</td><td class="text-center col-promedio">${aC.toFixed(1)}</td><td class="text-center col-promedio">${aTC.toFixed(1)}</td><td class="text-center col-promedio">${aV.toFixed(1)}</td>
                        <td class="text-center col-promedio">${aD.toFixed(1)}</td><td class="text-center col-promedio">${aVAC.toFixed(1)}</td><td class="text-center col-promedio">${aM.toFixed(1)}</td><td class="text-center col-promedio">${aE.toFixed(1)}</td><td class="text-center col-promedio">${aA.toFixed(1)}</td><td class="text-center col-promedio">${aPE.toFixed(1)}</td>
                        <td class="text-center" style="font-weight:bold;">${totalMes}</td>
                        <td class="text-center">${utilMes}</td>
                        <td class="text-center" style="font-weight:bold;">${Math.round(pctMes)}%</td>
                    `;
                    tbody.appendChild(tr);
                });

                const avg_mB = activeMonthsCount > 0 ? sumB / activeMonthsCount : 0;
                const avg_mP = activeMonthsCount > 0 ? sumP / activeMonthsCount : 0;
                const avg_mC = activeMonthsCount > 0 ? sumC / activeMonthsCount : 0;
                const avg_mTC = activeMonthsCount > 0 ? sumTC / activeMonthsCount : 0;
                const avg_mV = activeMonthsCount > 0 ? sumV / activeMonthsCount : 0;
                const avg_mD = activeMonthsCount > 0 ? sumD / activeMonthsCount : 0;
                const avg_mVAC = activeMonthsCount > 0 ? sumVAC / activeMonthsCount : 0;
                const avg_mM = activeMonthsCount > 0 ? sumM / activeMonthsCount : 0;
                const avg_mE = activeMonthsCount > 0 ? sumE / activeMonthsCount : 0;
                const avg_mA = activeMonthsCount > 0 ? sumA / activeMonthsCount : 0;
                const avg_mPE = activeMonthsCount > 0 ? sumPE / activeMonthsCount : 0;

                const pctYear = sumTotal > 0 ? (sumUtil / sumTotal) * 100 : 0;

                tfoot.innerHTML = `
                    <tr class="total-row">
                        <td class="sticky-col text-right">TOTALES</td>
                        <td class="text-center">${sumB}</td><td class="text-center">${sumP}</td><td class="text-center">${sumC}</td><td class="text-center">${sumTC}</td><td class="text-center">${sumV}</td>
                        <td class="text-center">${sumD}</td><td class="text-center">${sumVAC}</td><td class="text-center">${sumM}</td><td class="text-center">${sumE}</td><td class="text-center">${sumA}</td><td class="text-center">${sumPE}</td>
                        <td class="text-center col-promedio">${Math.round(avg_mB)}</td><td class="text-center col-promedio">${Math.round(avg_mP)}</td><td class="text-center col-promedio">${Math.round(avg_mC)}</td><td class="text-center col-promedio">${Math.round(avg_mTC)}</td><td class="text-center col-promedio">${Math.round(avg_mV)}</td>
                        <td class="text-center col-promedio">${Math.round(avg_mD)}</td><td class="text-center col-promedio">${Math.round(avg_mVAC)}</td><td class="text-center col-promedio">${Math.round(avg_mM)}</td><td class="text-center col-promedio">${Math.round(avg_mE)}</td><td class="text-center col-promedio">${Math.round(avg_mA)}</td><td class="text-center col-promedio">${Math.round(avg_mPE)}</td>
                        <td class="text-center" style="color:var(--text-dark);">${sumTotal}</td>
                        <td class="text-center" style="color:var(--text-dark);">${sumUtil}</td>
                        <td class="text-center" style="color:var(--text-dark);">${Math.round(pctYear)}%</td>
                    </tr>
                `;

                // Render Q Table (SIEMPRE TODAS LAS Qs AUNQUE ESTÉN EN 0)
                Object.keys(qUtilMap).forEach(q => {
                    const d = qUtilMap[q];
                    const pct = d.Total > 0 ? Math.round((d.Util / d.Total) * 100) : 0;
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="sticky-col text-left"><strong>${q}</strong></td>
                        <td class="text-center">${d.B}</td><td class="text-center">${d.P}</td><td class="text-center">${d.C}</td><td class="text-center">${d.TC}</td><td class="text-center">${d.V}</td>
                        <td class="text-center">${d.D}</td><td class="text-center">${d.VAC}</td><td class="text-center">${d.M}</td><td class="text-center">${d.E}</td><td class="text-center">${d.A}</td><td class="text-center">${d.PE}</td>
                        <td class="text-center" style="font-weight:bold;">${d.Total}</td>
                        <td class="text-center">${d.Util}</td>
                        <td class="text-center" style="font-weight:bold; color:var(--primary-color);">${pct}%</td>
                    `;
                    tbodyQ.appendChild(tr);
                });

                // Poblar filtro de área de forma dinámica y mantener selección
                const areaSelect = document.getElementById('util-area-filter');
                const currentAreaVal = areaSelect.value;
                areaSelect.innerHTML = '<option value="TODAS">Todas las Áreas</option>';
                Array.from(uniqueAreas).sort().forEach(a => {
                    areaSelect.innerHTML += `<option value="${a}">${a}</option>`;
                });
                if (uniqueAreas.has(currentAreaVal)) {
                    areaSelect.value = currentAreaVal;
                }

                // Renderizar la tabla de Área y las gráficas invocando la nueva función
                renderUtilizacionAreaAndCharts();
            }


            // ──────────────────────────────────────────────────────────────
            // ASIGNACION DE EVENTOS RESTANTES
            // ──────────────────────────────────────────────────────────────

            const tabs = document.querySelectorAll('.tab-btn');
            const sections = document.querySelectorAll('.table-section');
            tabs.forEach(btn => {
                btn.addEventListener('click', () => {
                    tabs.forEach(t => t.classList.remove('active'));
                    sections.forEach(s => s.classList.remove('active'));
                    btn.classList.add('active');
                    document.getElementById(btn.dataset.tab).classList.add('active');
                    moveYearControls();
                });
            });

            document.getElementById('global-year').addEventListener('change', function() {
                loadData(this.value);
            });

            window.exportTable = function(tableId, filename) {
                const table = document.getElementById(tableId);
                let csv = [];
                table.querySelectorAll('thead tr').forEach(row => {
                    const rowData = [];
                    row.querySelectorAll('th').forEach(cell => rowData.push(
                        `"${cell.textContent.trim().replace(/"/g,'""').replace(/↓|↑|↕/g,'')}"`));
                    csv.push(rowData.join(','));
                });
                table.querySelectorAll('tbody tr:not(.empty-row), tfoot tr').forEach(row => {
                    const rowData = [];
                    row.querySelectorAll('td').forEach(cell => rowData.push(
                            `"${cell.textContent.trim().replace(/[$,%]/g,'').replace(/\s+/g,' ')}"`
                            ));
                    csv.push(rowData.join(','));
                });
                const blob = new Blob(['\uFEFF' + csv.join('\n')], {
                    type: 'text/csv;charset=utf-8;'
                });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = filename + '_' + document.getElementById('global-year').value + '.csv';
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            };

            updateDropdownText('emp');
            updateDropdownText('area');
            updateDropdownText('pozo');
            updateDropdownText('act');

            loadData(document.getElementById('global-year').value);
            moveYearControls();
        });
    </script>
@endsection
