@extends('modulos.recursoshumanos.sistemas.loadchart.index')

@section('content')
<style>
    /* Global Styles & Variables */
    :root {
        --primary-color: #4e73df;
        --success-color: #1cc88a;
        --info-color: #36b9cc;
        --warning-color: #f6c23e;
        --danger-color: #e74a3b;
        --text-dark: #2c3e50;
        --text-medium: #5a5c69;
        --background-light: #f8f9fc;
        --border-color: #e3e6f0;
        --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    body {
        font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
        background-color: var(--background-light);
        color: var(--text-dark);
        line-height: 1.6;
    }

    /* Sobrescribe el container solo para esta vista */
    .dashboard-container {
        max-width: 100% !important;
        margin: 0 auto !important;
        padding: 0 15px !important;
    }

    /* Header */
    .dashboard-header {
        display: flex;
        align-items: center;
        margin-bottom: 30px;
        color: var(--text-dark);
    }

    .header-icon {
        font-size: 2.5rem;
        color: var(--primary-color);
        margin-right: 15px;
    }

    .header-title {
        font-size: 2rem;
        font-weight: 600;
        margin: 0;
    }

    /* Filters Section */
    .dashboard-filters {
        background: #ffffff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        margin-bottom: 40px;
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        align-items: flex-end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        min-width: 200px;
        flex: 1;
    }

    .filter-label {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-medium);
        margin-bottom: 8px;
    }

    .select-field,
    .input-field {
        padding: 10px 15px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        background-color: #fff;
        transition: border-color 0.3s;
    }

    .select-field:focus,
    .input-field:focus {
        outline: none;
        border-color: var(--primary-color);
    }

    .btn {
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-weight: 600;
        font-size: 14px;
        transition: background-color 0.3s, transform 0.2s;
        height: 42px;
    }

    .btn-primary {
        background-color: var(--primary-color);
        color: white;
    }

    .btn-primary:hover {
        background-color: #2e59d9;
        transform: translateY(-2px);
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
    }

    /* Tabs Navigation */
    .data-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
        border-bottom: 2px solid var(--border-color);
        padding-bottom: 10px;
    }

    .tab-btn {
        padding: 12px 24px;
        background: none;
        border: none;
        border-radius: 8px 8px 0 0;
        font-weight: 600;
        color: var(--text-medium);
        cursor: pointer;
        transition: all 0.3s;
    }

    .tab-btn.active {
        color: var(--primary-color);
        border-bottom: 3px solid var(--primary-color);
        background-color: rgba(78, 115, 223, 0.1);
    }

    .tab-btn:hover:not(.active) {
        background-color: var(--background-light);
    }

    /* Table Cards */
    .table-section {
        display: none;
        margin-bottom: 40px;
    }

    .table-section.active {
        display: block;
        animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .table-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        padding: 25px;
        margin-bottom: 30px;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--border-color);
    }

    .table-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: var(--text-dark);
        margin: 0;
    }

    .table-actions {
        display: flex;
        gap: 10px;
    }

    .table-container {
        overflow-x: auto;
        border-radius: 8px;
        border: 1px solid var(--border-color);
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }

    .data-table th {
        background-color: var(--primary-color);
        color: white;
        font-weight: 600;
        text-align: left;
        padding: 15px;
        position: sticky;
        top: 0;
    }

    .data-table td {
        padding: 12px 15px;
        border-bottom: 1px solid var(--border-color);
    }

    .data-table tr:hover {
        background-color: rgba(78, 115, 223, 0.05);
    }

    .data-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .currency {
        font-family: 'Courier New', monospace;
        text-align: right;
        font-weight: 600;
    }

    .total-row {
        background-color: rgba(28, 200, 138, 0.1) !important;
        font-weight: bold;
    }

    .total-row td {
        border-top: 2px solid var(--success-color);
    }

    /* Table-specific filters */
    .table-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 8px;
        border: 1px solid var(--border-color);
    }

    .table-filter-group {
        display: flex;
        flex-direction: column;
        min-width: 150px;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .dashboard-filters {
            flex-direction: column;
        }

        .filter-group {
            min-width: auto;
        }
    }

    @media (max-width: 768px) {
        .dashboard-header {
            flex-direction: column;
            text-align: center;
        }

        .header-icon {
            margin-right: 0;
            margin-bottom: 10px;
        }

        .data-tabs {
            flex-wrap: wrap;
        }

        .table-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .table-actions {
            width: 100%;
            justify-content: flex-start;
        }
    }
</style>

<main class="dashboard-container">
    <header class="dashboard-header">
        <i class="fas fa-chart-line header-icon"></i>
        <h1 class="header-title">Panel de Control - Bonos y Nómina</h1>
    </header>

    <!-- Filtros principales -->
    <section class="dashboard-filters">
        <div class="filter-group">
            <label for="year-filter" class="filter-label">Año</label>
            <select id="year-filter" class="select-field">
                <option value="2025">2025</option>
                <option value="2024">2024</option>
                <option value="2023">2023</option>
            </select>
        </div>

        <div class="filter-group">
            <label for="period-type" class="filter-label">Tipo de Periodo</label>
            <select id="period-type" class="select-field">
                <option value="quincena">Quincena</option>
                <option value="mes">Mes</option>
                <option value="trimestre">Trimestre</option>
                <option value="anual">Anual</option>
            </select>
        </div>

        <div class="filter-group">
            <label for="period-filter" class="filter-label">Periodo Específico</label>
            <select id="period-filter" class="select-field">
                <!-- Opciones se llenarán dinámicamente -->
            </select>
        </div>

        <div class="filter-group">
            <label for="departamento-filter" class="filter-label">Departamento</label>
            <select id="departamento-filter" class="select-field">
                <option value="todos">Todos los Departamentos</option>
                <option value="operaciones">Operaciones</option>
                <option value="administracion">Administración</option>
                <option value="suministros">Suministros</option>
                <option value="geociencias">Geociencias</option>
                <option value="ventas">Ventas</option>
                <option value="mantenimiento">Mantenimiento</option>
                <option value="ohse">OHSE</option>
            </select>
        </div>

        <button class="btn btn-primary" id="apply-filters">
            <i class="fas fa-filter"></i> Aplicar Filtros
        </button>

        <button class="btn btn-secondary" id="reset-filters">
            <i class="fas fa-redo"></i> Limpiar Filtros
        </button>
    </section>

    <!-- Tabs de navegación -->
    <div class="data-tabs">
        <button class="tab-btn active" data-tab="bonos-quincena">Bonos por Quincena</button>
        <button class="tab-btn" data-tab="bonos-mes">Bonos por Mes</button>
        <button class="tab-btn" data-tab="record-bonos">Record de Bonos</button>
        <button class="tab-btn" data-tab="resumen-general">Resumen General</button>
    </div>

    <!-- Tabla: Bonos por Quincena -->
    <section id="bonos-quincena" class="table-section active">
        <div class="table-card">
            <div class="table-header">
                <h2 class="table-title">Bonos por Quincena 2025</h2>
                <div class="table-actions">
                    <button class="btn btn-primary" onclick="exportTable('quincena-table', 'Bonos_Quincena_2025')">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                </div>
            </div>

            <div class="table-filters">
                <div class="table-filter-group">
                    <label for="quincena-filter" class="filter-label">Quincena</label>
                    <select id="quincena-filter" class="select-field">
                        <option value="todas">Todas las Quincenas</option>
                        <!-- Las opciones se generarán dinámicamente -->
                    </select>
                </div>

                <div class="table-filter-group">
                    <label for="mes-quincena-filter" class="filter-label">Mes</label>
                    <select id="mes-quincena-filter" class="select-field">
                        <option value="todos">Todos los Meses</option>
                        <option value="enero">Enero</option>
                        <option value="febrero">Febrero</option>
                        <option value="marzo">Marzo</option>
                        <option value="abril">Abril</option>
                        <option value="mayo">Mayo</option>
                        <option value="junio">Junio</option>
                        <option value="julio">Julio</option>
                        <option value="agosto">Agosto</option>
                        <option value="septiembre">Septiembre</option>
                        <option value="octubre">Octubre</option>
                        <option value="noviembre">Noviembre</option>
                        <option value="diciembre">Diciembre</option>
                    </select>
                </div>
            </div>

            <div class="table-container">
                <table id="quincena-table" class="data-table">
                    <thead>
                        <tr>
                            <th>Quincena</th>
                            <th>Mes</th>
                            <th>Bonos</th>
                            <th>HC</th>
                            <th>Nómina</th>
                            <th>Bonos Promedio</th>
                            <th>Nómina Promedio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Datos se llenarán dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Tabla: Bonos por Mes -->
    <section id="bonos-mes" class="table-section">
        <div class="table-card">
            <div class="table-header">
                <h2 class="table-title">Bonos por Mes 2025 (USD)</h2>
                <div class="table-actions">
                    <button class="btn btn-primary" onclick="exportTable('mes-table', 'Bonos_Mes_2025')">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                </div>
            </div>

            <div class="table-filters">
                <div class="table-filter-group">
                    <label for="mes-filter" class="filter-label">Mes</label>
                    <select id="mes-filter" class="select-field">
                        <option value="todos">Todos los Meses</option>
                        <option value="enero">Enero</option>
                        <option value="febrero">Febrero</option>
                        <option value="marzo">Marzo</option>
                        <option value="abril">Abril</option>
                        <option value="mayo">Mayo</option>
                        <option value="junio">Junio</option>
                        <option value="julio">Julio</option>
                        <option value="agosto">Agosto</option>
                        <option value="septiembre">Septiembre</option>
                        <option value="octubre">Octubre</option>
                        <option value="noviembre">Noviembre</option>
                        <option value="diciembre">Diciembre</option>
                    </select>
                </div>

                <div class="table-filter-group">
                    <label for="trimestre-filter" class="filter-label">Trimestre</label>
                    <select id="trimestre-filter" class="select-field">
                        <option value="todos">Todos los Trimestres</option>
                        <option value="1">Q1 (Ene-Mar)</option>
                        <option value="2">Q2 (Abr-Jun)</option>
                        <option value="3">Q3 (Jul-Sep)</option>
                        <option value="4">Q4 (Oct-Dic)</option>
                    </select>
                </div>
            </div>

            <div class="table-container">
                <table id="mes-table" class="data-table">
                    <thead>
                        <tr>
                            <th>Mes</th>
                            <th>Nómina</th>
                            <th>Bonos</th>
                            <th>HC</th>
                            <th>Bonos/Nómina (%)</th>
                            <th>Acumulado Bonos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Datos se llenarán dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Tabla: Record de Bonos -->
    <section id="record-bonos" class="table-section">
        <div class="table-card">
            <div class="table-header">
                <h2 class="table-title">Record de Bonos por Empleado 2025</h2>
                <div class="table-actions">
                    <button class="btn btn-primary" onclick="exportTable('record-table', 'Record_Bonos_2025')">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                </div>
            </div>

            <div class="table-filters">
                <div class="table-filter-group">
                    <label for="departamento-record-filter" class="filter-label">Departamento</label>
                    <select id="departamento-record-filter" class="select-field">
                        <option value="todos">Todos los Departamentos</option>
                        <option value="operaciones">Operaciones</option>
                        <option value="administracion">Administración</option>
                        <option value="suministros">Suministros</option>
                        <option value="geociencias">Geociencias</option>
                        <option value="ventas">Ventas</option>
                        <option value="mantenimiento">Mantenimiento</option>
                        <option value="ohse">OHSE</option>
                    </select>
                </div>

                <div class="table-filter-group">
                    <label for="bono-minimo-filter" class="filter-label">Bono Mínimo (USD)</label>
                    <input type="number" id="bono-minimo-filter" class="input-field" placeholder="Ej: 1000" min="0">
                </div>

                <div class="table-filter-group">
                    <label for="bono-maximo-filter" class="filter-label">Bono Máximo (USD)</label>
                    <input type="number" id="bono-maximo-filter" class="input-field" placeholder="Ej: 20000" min="0">
                </div>
            </div>

            <div class="table-container">
                <table id="record-table" class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Departamento</th>
                            <th>Bono (USD)</th>
                            <th>% del Total</th>
                            <th>Promedio Depto</th>
                            <th>Diferencia vs Promedio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Datos se llenarán dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Tabla: Resumen General -->
    <section id="resumen-general" class="table-section">
        <div class="table-card">
            <div class="table-header">
                <h2 class="table-title">Resumen General 2025</h2>
                <div class="table-actions">
                    <button class="btn btn-primary" onclick="exportTable('resumen-table', 'Resumen_General_2025')">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                </div>
            </div>

            <div class="table-filters">
                <div class="table-filter-group">
                    <label for="resumen-periodo-filter" class="filter-label">Periodo</label>
                    <select id="resumen-periodo-filter" class="select-field">
                        <option value="anual">Anual</option>
                        <option value="trimestral">Trimestral</option>
                        <option value="mensual">Mensual</option>
                    </select>
                </div>
            </div>

            <div class="table-container">
                <table id="resumen-table" class="data-table">
                    <thead>
                        <tr>
                            <th>Periodo</th>
                            <th>Total Bonos (USD)</th>
                            <th>Total Nómina (USD)</th>
                            <th>HC Promedio</th>
                            <th>Bonos/Nómina (%)</th>
                            <th>Bonos por HC</th>
                            <th>Nómina por HC</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Datos se llenarán dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Datos de ejemplo basados en las imágenes proporcionadas
        const datosQuincena = [
            { quincena: 1, mes: 'Enero', bonos: 241897.44, hc: 104, nomina: 728738, tipo: 'HREFI' },
            { quincena: 2, mes: 'Febrero', bonos: 486820.56, hc: 104, nomina: 660201, tipo: 'HREFI' },
            { quincena: 3, mes: 'Marzo', bonos: 402365.50, hc: 103, nomina: 865010, tipo: 'HREFI' },
            { quincena: 4, mes: 'Abril', bonos: 257835.50, hc: 102, nomina: 603552, tipo: 'HREFI' },
            { quincena: 5, mes: 'Mayo', bonos: 417254.00, hc: 101, nomina: 581053, tipo: 'HREFI' },
            { quincena: 6, mes: 'Junio', bonos: 393755.05, hc: 101, nomina: 590153, tipo: 'HREFI' },
            { quincena: 7, mes: 'Julio', bonos: 299290.00, hc: 101, nomina: 509589, tipo: 'HREFI' },
            { quincena: 8, mes: 'Agosto', bonos: 304762.20, hc: 86, nomina: 458799, tipo: 'HREFI' },
            { quincena: 9, mes: 'Septiembre', bonos: 256000.00, hc: 86, nomina: 758111, tipo: 'HREFI' },
            { quincena: 10, mes: 'Octubre', bonos: 273607.50, hc: 86, nomina: 865934, tipo: 'HREFI' },
            { quincena: 11, mes: 'Noviembre', bonos: 308080.00, hc: 86, nomina: 681682, tipo: 'HREFI' },
            { quincena: 12, mes: 'Diciembre', bonos: 282143.92, hc: 86, nomina: 448975, tipo: 'HREFI' },
            { quincena: 13, mes: '', bonos: 256451.10, hc: 85, nomina: 0, tipo: '' },
            { quincena: 14, mes: '', bonos: 253121.70, hc: 85, nomina: 0, tipo: '' },
            { quincena: 15, mes: '', bonos: 259120.00, hc: 85, nomina: 0, tipo: '' },
            { quincena: 16, mes: 'Enero', bonos: 199178.50, hc: 85, nomina: 542866, tipo: 'HREFI' },
            { quincena: 17, mes: 'Febrero', bonos: 381420.72, hc: 85, nomina: 538435, tipo: 'HREFI' },
            { quincena: 18, mes: 'Marzo', bonos: 376890.70, hc: 85, nomina: 550883, tipo: 'HREFI' },
            { quincena: 19, mes: 'Abril', bonos: 406248.60, hc: 85, nomina: 535503, tipo: 'HREFI' },
            { quincena: 20, mes: 'Mayo', bonos: 403183.90, hc: 86, nomina: 532464, tipo: 'HREFI' },
            { quincena: 21, mes: 'Junio', bonos: 365804.00, hc: 86, nomina: 532969, tipo: 'HREFI' },
            { quincena: 22, mes: 'Julio', bonos: 315877.80, hc: 86, nomina: 528409, tipo: 'HREFI' },
            { quincena: 23, mes: 'Agosto', bonos: 448975.00, hc: 86, nomina: 525650, tipo: 'HREFI' }
        ];

        const datosMes = [
            { mes: 'Enero', nomina: 542866, bonos: 0, hc: 0 },
            { mes: 'Febrero', nomina: 538435, bonos: 0, hc: 0 },
            { mes: 'Marzo', nomina: 550883, bonos: 0, hc: 0 },
            { mes: 'Abril', nomina: 535503, bonos: 0, hc: 0 },
            { mes: 'Mayo', nomina: 532464, bonos: 0, hc: 0 },
            { mes: 'Junio', nomina: 532969, bonos: 0, hc: 0 },
            { mes: 'Julio', nomina: 528409, bonos: 0, hc: 0 },
            { mes: 'Agosto', nomina: 525650, bonos: 0, hc: 0 },
            { mes: 'Septiembre', nomina: 542864, bonos: 0, hc: 0 },
            { mes: 'Octubre', nomina: 548371, bonos: 0, hc: 0 },
            { mes: 'Noviembre', nomina: 538083, bonos: 0, hc: 0 },
            { mes: 'Diciembre', nomina: 525082, bonos: 0, hc: 0 }
        ];

        const datosRecord = [
            { id: 'V10012', nombre: 'MARCOS FERNANDO RUIZ GUERRERO', departamento: 'ADMINISTRACIÓN', bono: 12500.00 },
            { id: 'V10024', nombre: 'PEDRO ANTONIO TAXILAGA LOPEZ', departamento: 'SUMINISTROS', bono: 18000.00 },
            { id: 'V10026', nombre: 'JESUS AURELIO MORALES COLLADO', departamento: 'OPERACIONES', bono: 9660.00 },
            { id: 'V10029', nombre: 'FRANCISCO JAVIER LARA HIDALGO', departamento: 'SUMINISTROS', bono: 2800.00 },
            { id: 'V10030', nombre: 'RAUL TRONCO ALVAREZ', departamento: 'OPERACIONES', bono: 7500.00 },
            { id: 'V10031', nombre: 'MARVIN DEL CARMEN DE LA CRUZ IZQUIERDO', departamento: 'SUMINISTROS', bono: 10800.00 },
            { id: 'V10041', nombre: 'ANGEL MARIO LOPEZ GOMEZ', departamento: 'OPERACIONES', bono: 13880.00 },
            { id: 'V10046', nombre: 'RAMON BASTAR MEJIA', departamento: 'OPERACIONES', bono: 11520.00 },
            { id: 'V10049', nombre: 'JUAN CARLOS DIAZ RODRIGUEZ', departamento: 'OPERACIONES', bono: 2760.00 },
            { id: 'V10050', nombre: 'DIANA LAURA ARZAT ALEJANDRO', departamento: 'GEOCIENCIAS', bono: 6493.00 },
            { id: 'V10057', nombre: 'LUIS ALBERTO MENDIZ GONZALEZ', departamento: 'GEOCIENCIAS', bono: 7192.00 },
            { id: 'V10069', nombre: 'OCTAVO CESAR HERNANDEZ RAMON', departamento: 'SUMINISTROS', bono: 10000.00 },
            { id: 'V10074', nombre: 'ANTONIO RAVANALES ESCALANTE', departamento: 'OPERACIONES', bono: 5020.00 },
            { id: 'V10075', nombre: 'SALVADOR GERARDO VELASCO LOPEZ', departamento: 'OPERACIONES', bono: 16820.00 },
            { id: 'V10077', nombre: 'ERNESTINA PEREZ ACOSTA', departamento: 'ADMINISTRACIÓN', bono: 1500.00 },
            { id: 'V10084', nombre: 'CHRISTIAN NERI JIMENEZ SALAS', departamento: 'OPERACIONES', bono: 18690.00 },
            { id: 'V10087', nombre: 'EDVINI RAFAEL COLINA LANDA', departamento: 'OPERACIONES', bono: 10040.00 },
            { id: 'V10102', nombre: 'JORGE ARMANDO GARCIA NOCHEBUENA', departamento: 'OPERACIONES', bono: 11860.00 },
            { id: 'V10105', nombre: 'FABIAN HERNANDEZ REYES', departamento: 'OPERACIONES', bono: 1360.00 },
            { id: 'V10106', nombre: 'CARLOS OCTAVO BAUTISTA GONZALEZ', departamento: 'OPERACIONES', bono: 7900.00 },
            { id: 'V10109', nombre: 'RICARDO PURECO ALEJANDRO', departamento: 'OPERACIONES', bono: 11520.00 },
            { id: 'V10110', nombre: 'JOSE ANTONIO ACOSTA CAMPOS', departamento: 'OPERACIONES', bono: 16060.00 },
            { id: 'V10112', nombre: 'CECILIO RAMIREZ MONTERO', departamento: 'OHSE', bono: 1295.00 },
            { id: 'V10114', nombre: 'MIGUEL HERNANDEZ GARCIA', departamento: 'OHSE', bono: 1080.00 },
            { id: 'V10116', nombre: 'MABEL AZUCENA RODRIGUEZ CANTU', departamento: 'OHSE', bono: 380.00 },
            { id: 'V10120', nombre: 'FELIPE DANIEL LOM TIBURCIO', departamento: 'OPERACIONES', bono: 13600.00 }
        ];

        // Inicializar filtros de quincena
        const quincenaFilter = document.getElementById('quincena-filter');
        for (let i = 1; i <= 24; i++) {
            const option = document.createElement('option');
            option.value = i;
            option.textContent = `Quincena ${i}`;
            quincenaFilter.appendChild(option);
        }

        // Manejo de tabs
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tableSections = document.querySelectorAll('.table-section');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remover clase active de todos los botones y secciones
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tableSections.forEach(section => section.classList.remove('active'));

                // Agregar clase active al botón clickeado
                button.classList.add('active');

                // Mostrar la sección correspondiente
                const tabId = button.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');

                // Actualizar datos de la tabla visible
                updateTableData(tabId);
            });
        });

        // Función para formatear moneda
        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 2
            }).format(amount);
        }

        // Función para calcular promedios
        function calcularPromedioBonosQuincena() {
            const totalBonos = datosQuincena.reduce((sum, item) => sum + item.bonos, 0);
            return totalBonos / datosQuincena.length;
        }

        function calcularPromedioNominaQuincena() {
            const totalNomina = datosQuincena.reduce((sum, item) => sum + (item.nomina || 0), 0);
            const count = datosQuincena.filter(item => item.nomina > 0).length;
            return totalNomina / count;
        }

        // Actualizar datos de las tablas
        function updateTableData(tabId) {
            switch(tabId) {
                case 'bonos-quincena':
                    updateQuincenaTable();
                    break;
                case 'bonos-mes':
                    updateMesTable();
                    break;
                case 'record-bonos':
                    updateRecordTable();
                    break;
                case 'resumen-general':
                    updateResumenTable();
                    break;
            }
        }

        // Actualizar tabla de quincenas
        function updateQuincenaTable() {
            const tableBody = document.querySelector('#quincena-table tbody');
            tableBody.innerHTML = '';

            const promedioBonos = calcularPromedioBonosQuincena();
            const promedioNomina = calcularPromedioNominaQuincena();

            // Aplicar filtros
            let filteredData = [...datosQuincena];
            const quincenaFilterValue = document.getElementById('quincena-filter').value;
            const mesFilterValue = document.getElementById('mes-quincena-filter').value;

            if (quincenaFilterValue !== 'todas') {
                filteredData = filteredData.filter(item => item.quincena == quincenaFilterValue);
            }

            if (mesFilterValue !== 'todos') {
                filteredData = filteredData.filter(item =>
                    item.mes.toLowerCase().includes(mesFilterValue.toLowerCase())
                );
            }

            let totalBonos = 0;
            let totalNomina = 0;
            let totalHC = 0;

            filteredData.forEach(item => {
                const row = document.createElement('tr');
                const bonosPromedio = item.bonos / (item.hc || 1);
                const nominaPromedio = item.nomina / (item.hc || 1);

                row.innerHTML = `
                    <td>${item.quincena}</td>
                    <td>${item.mes || '-'}</td>
                    <td class="currency">${formatCurrency(item.bonos)}</td>
                    <td>${item.hc}</td>
                    <td class="currency">${item.nomina ? formatCurrency(item.nomina) : '-'}</td>
                    <td class="currency">${formatCurrency(bonosPromedio)}</td>
                    <td class="currency">${item.nomina ? formatCurrency(nominaPromedio) : '-'}</td>
                `;

                tableBody.appendChild(row);

                totalBonos += item.bonos;
                totalNomina += item.nomina || 0;
                totalHC += item.hc;
            });

            // Agregar fila de totales
            if (filteredData.length > 0) {
                const totalRow = document.createElement('tr');
                totalRow.className = 'total-row';
                totalRow.innerHTML = `
                    <td><strong>TOTAL</strong></td>
                    <td><strong>${filteredData.length} registros</strong></td>
                    <td class="currency"><strong>${formatCurrency(totalBonos)}</strong></td>
                    <td><strong>${totalHC}</strong></td>
                    <td class="currency"><strong>${formatCurrency(totalNomina)}</strong></td>
                    <td class="currency"><strong>${formatCurrency(totalBonos / totalHC)}</strong></td>
                    <td class="currency"><strong>${formatCurrency(totalNomina / totalHC)}</strong></td>
                `;
                tableBody.appendChild(totalRow);
            }
        }

        // Actualizar tabla de meses
        function updateMesTable() {
            const tableBody = document.querySelector('#mes-table tbody');
            tableBody.innerHTML = '';

            // Calcular bonos por mes a partir de datos de quincena
            const bonosPorMes = {};
            datosQuincena.forEach(item => {
                if (item.mes && item.mes !== '') {
                    if (!bonosPorMes[item.mes]) {
                        bonosPorMes[item.mes] = 0;
                    }
                    bonosPorMes[item.mes] += item.bonos;
                }
            });

            // Aplicar filtros
            let filteredData = [...datosMes];
            const mesFilterValue = document.getElementById('mes-filter').value;
            const trimestreFilterValue = document.getElementById('trimestre-filter').value;

            if (mesFilterValue !== 'todos') {
                filteredData = filteredData.filter(item =>
                    item.mes.toLowerCase().includes(mesFilterValue.toLowerCase())
                );
            }

            if (trimestreFilterValue !== 'todos') {
                const trimestreMap = {
                    '1': ['Enero', 'Febrero', 'Marzo'],
                    '2': ['Abril', 'Mayo', 'Junio'],
                    '3': ['Julio', 'Agosto', 'Septiembre'],
                    '4': ['Octubre', 'Noviembre', 'Diciembre']
                };

                filteredData = filteredData.filter(item =>
                    trimestreMap[trimestreFilterValue].includes(item.mes)
                );
            }

            let acumuladoBonos = 0;
            let totalBonos = 0;
            let totalNomina = 0;

            filteredData.forEach(item => {
                const bonosMes = bonosPorMes[item.mes] || 0;
                const porcentaje = item.nomina > 0 ? (bonosMes / item.nomina * 100) : 0;
                acumuladoBonos += bonosMes;

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.mes}</td>
                    <td class="currency">${formatCurrency(item.nomina)}</td>
                    <td class="currency">${formatCurrency(bonosMes)}</td>
                    <td>${item.hc}</td>
                    <td>${porcentaje.toFixed(2)}%</td>
                    <td class="currency">${formatCurrency(acumuladoBonos)}</td>
                `;

                tableBody.appendChild(row);

                totalBonos += bonosMes;
                totalNomina += item.nomina;
            });

            // Agregar fila de totales
            if (filteredData.length > 0) {
                const totalRow = document.createElement('tr');
                totalRow.className = 'total-row';
                totalRow.innerHTML = `
                    <td><strong>TOTAL</strong></td>
                    <td class="currency"><strong>${formatCurrency(totalNomina)}</strong></td>
                    <td class="currency"><strong>${formatCurrency(totalBonos)}</strong></td>
                    <td><strong>-</strong></td>
                    <td><strong>${(totalNomina > 0 ? (totalBonos / totalNomina * 100) : 0).toFixed(2)}%</strong></td>
                    <td class="currency"><strong>${formatCurrency(acumuladoBonos)}</strong></td>
                `;
                tableBody.appendChild(totalRow);
            }
        }

        // Actualizar tabla de record de bonos
        function updateRecordTable() {
            const tableBody = document.querySelector('#record-table tbody');
            tableBody.innerHTML = '';

            // Calcular promedios por departamento
            const promediosDepto = {};
            const conteosDepto = {};

            datosRecord.forEach(item => {
                const depto = item.departamento;
                if (!promediosDepto[depto]) {
                    promediosDepto[depto] = 0;
                    conteosDepto[depto] = 0;
                }
                promediosDepto[depto] += item.bono;
                conteosDepto[depto]++;
            });

            for (const depto in promediosDepto) {
                promediosDepto[depto] = promediosDepto[depto] / conteosDepto[depto];
            }

            // Calcular total de bonos
            const totalBonos = datosRecord.reduce((sum, item) => sum + item.bono, 0);

            // Aplicar filtros
            let filteredData = [...datosRecord];
            const deptoFilterValue = document.getElementById('departamento-record-filter').value;
            const bonoMinimo = parseFloat(document.getElementById('bono-minimo-filter').value) || 0;
            const bonoMaximo = parseFloat(document.getElementById('bono-maximo-filter').value) || Infinity;

            if (deptoFilterValue !== 'todos') {
                filteredData = filteredData.filter(item =>
                    item.departamento.toLowerCase().includes(deptoFilterValue.toLowerCase())
                );
            }

            filteredData = filteredData.filter(item =>
                item.bono >= bonoMinimo && item.bono <= bonoMaximo
            );

            // Ordenar por bono descendente
            filteredData.sort((a, b) => b.bono - a.bono);

            filteredData.forEach(item => {
                const porcentajeTotal = (item.bono / totalBonos * 100);
                const promedioDepto = promediosDepto[item.departamento] || 0;
                const diferencia = item.bono - promedioDepto;

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.id}</td>
                    <td>${item.nombre}</td>
                    <td>${item.departamento}</td>
                    <td class="currency">${formatCurrency(item.bono)}</td>
                    <td>${porcentajeTotal.toFixed(2)}%</td>
                    <td class="currency">${formatCurrency(promedioDepto)}</td>
                    <td class="currency ${diferencia >= 0 ? 'positive' : 'negative'}">
                        ${diferencia >= 0 ? '+' : ''}${formatCurrency(diferencia)}
                    </td>
                `;

                tableBody.appendChild(row);
            });
        }

        // Actualizar tabla de resumen general
        function updateResumenTable() {
            const tableBody = document.querySelector('#resumen-table tbody');
            tableBody.innerHTML = '';

            const periodoType = document.getElementById('resumen-periodo-filter').value;

            if (periodoType === 'anual') {
                // Resumen anual
                const totalBonos = datosQuincena.reduce((sum, item) => sum + item.bonos, 0);
                const totalNomina = datosMes.reduce((sum, item) => sum + item.nomina, 0);
                const hcPromedio = 85; // Valor aproximado

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><strong>2025 Anual</strong></td>
                    <td class="currency"><strong>${formatCurrency(totalBonos)}</strong></td>
                    <td class="currency"><strong>${formatCurrency(totalNomina)}</strong></td>
                    <td><strong>${hcPromedio}</strong></td>
                    <td><strong>${(totalNomina > 0 ? (totalBonos / totalNomina * 100) : 0).toFixed(2)}%</strong></td>
                    <td class="currency"><strong>${formatCurrency(totalBonos / hcPromedio)}</strong></td>
                    <td class="currency"><strong>${formatCurrency(totalNomina / hcPromedio)}</strong></td>
                `;
                tableBody.appendChild(row);

            } else if (periodoType === 'trimestral') {
                // Resumen trimestral
                const trimestres = [
                    { nombre: 'Q1 (Ene-Mar)', meses: ['Enero', 'Febrero', 'Marzo'] },
                    { nombre: 'Q2 (Abr-Jun)', meses: ['Abril', 'Mayo', 'Junio'] },
                    { nombre: 'Q3 (Jul-Sep)', meses: ['Julio', 'Agosto', 'Septiembre'] },
                    { nombre: 'Q4 (Oct-Dic)', meses: ['Octubre', 'Noviembre', 'Diciembre'] }
                ];

                trimestres.forEach(trimestre => {
                    // Calcular bonos del trimestre
                    let bonosTrimestre = 0;
                    datosQuincena.forEach(item => {
                        if (trimestre.meses.includes(item.mes)) {
                            bonosTrimestre += item.bonos;
                        }
                    });

                    // Calcular nómina del trimestre
                    let nominaTrimestre = 0;
                    datosMes.forEach(item => {
                        if (trimestre.meses.includes(item.mes)) {
                            nominaTrimestre += item.nomina;
                        }
                    });

                    const hcPromedio = 85; // Valor aproximado

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td><strong>${trimestre.nombre}</strong></td>
                        <td class="currency"><strong>${formatCurrency(bonosTrimestre)}</strong></td>
                        <td class="currency"><strong>${formatCurrency(nominaTrimestre)}</strong></td>
                        <td><strong>${hcPromedio}</strong></td>
                        <td><strong>${(nominaTrimestre > 0 ? (bonosTrimestre / nominaTrimestre * 100) : 0).toFixed(2)}%</strong></td>
                        <td class="currency"><strong>${formatCurrency(bonosTrimestre / hcPromedio)}</strong></td>
                        <td class="currency"><strong>${formatCurrency(nominaTrimestre / hcPromedio)}</strong></td>
                    `;
                    tableBody.appendChild(row);
                });

            } else if (periodoType === 'mensual') {
                // Resumen mensual
                datosMes.forEach(item => {
                    // Calcular bonos del mes
                    let bonosMes = 0;
                    datosQuincena.forEach(q => {
                        if (q.mes === item.mes) {
                            bonosMes += q.bonos;
                        }
                    });

                    const hcPromedio = 85; // Valor aproximado

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td><strong>${item.mes} 2025</strong></td>
                        <td class="currency"><strong>${formatCurrency(bonosMes)}</strong></td>
                        <td class="currency"><strong>${formatCurrency(item.nomina)}</strong></td>
                        <td><strong>${hcPromedio}</strong></td>
                        <td><strong>${(item.nomina > 0 ? (bonosMes / item.nomina * 100) : 0).toFixed(2)}%</strong></td>
                        <td class="currency"><strong>${formatCurrency(bonosMes / hcPromedio)}</strong></td>
                        <td class="currency"><strong>${formatCurrency(item.nomina / hcPromedio)}</strong></td>
                    `;
                    tableBody.appendChild(row);
                });
            }
        }

        // Función para exportar tabla
        window.exportTable = function(tableId, filename) {
            const table = document.getElementById(tableId);
            let csv = [];

            // Obtener encabezados
            const headers = [];
            table.querySelectorAll('th').forEach(th => {
                headers.push(th.textContent);
            });
            csv.push(headers.join(','));

            // Obtener filas
            table.querySelectorAll('tbody tr').forEach(row => {
                const rowData = [];
                row.querySelectorAll('td').forEach(cell => {
                    // Limpiar formato de moneda si es necesario
                    let text = cell.textContent;
                    text = text.replace(/[$,]/g, '');
                    rowData.push(`"${text}"`);
                });
                csv.push(rowData.join(','));
            });

            // Crear y descargar archivo CSV
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');

            if (navigator.msSaveBlob) {
                navigator.msSaveBlob(blob, filename + '.csv');
            } else {
                link.href = URL.createObjectURL(blob);
                link.download = filename + '.csv';
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        };

        // Inicializar filtros de periodo principal
        const periodTypeSelect = document.getElementById('period-type');
        const periodFilterSelect = document.getElementById('period-filter');

        function updatePeriodFilterOptions() {
            const periodType = periodTypeSelect.value;
            periodFilterSelect.innerHTML = '';

            let options = [];

            switch(periodType) {
                case 'quincena':
                    options.push({ value: 'todas', text: 'Todas las Quincenas' });
                    for (let i = 1; i <= 24; i++) {
                        options.push({ value: i, text: `Quincena ${i}` });
                    }
                    break;

                case 'mes':
                    options.push({ value: 'todos', text: 'Todos los Meses' });
                    const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                                   'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                    meses.forEach((mes, index) => {
                        options.push({ value: index + 1, text: mes });
                    });
                    break;

                case 'trimestre':
                    options.push({ value: 'todos', text: 'Todos los Trimestres' });
                    options.push({ value: 1, text: 'Q1 (Enero - Marzo)' });
                    options.push({ value: 2, text: 'Q2 (Abril - Junio)' });
                    options.push({ value: 3, text: 'Q3 (Julio - Septiembre)' });
                    options.push({ value: 4, text: 'Q4 (Octubre - Diciembre)' });
                    break;

                case 'anual':
                    options.push({ value: '2025', text: '2025' });
                    options.push({ value: '2024', text: '2024' });
                    options.push({ value: '2023', text: '2023' });
                    break;
            }

            options.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.value;
                optionElement.textContent = option.text;
                periodFilterSelect.appendChild(optionElement);
            });
        }

        periodTypeSelect.addEventListener('change', updatePeriodFilterOptions);
        updatePeriodFilterOptions();

        // Event listeners para filtros
        document.getElementById('apply-filters').addEventListener('click', function() {
            // Aplicar filtros principales
            const year = document.getElementById('year-filter').value;
            const periodType = document.getElementById('period-type').value;
            const period = document.getElementById('period-filter').value;
            const departamento = document.getElementById('departamento-filter').value;

            console.log('Aplicando filtros:', { year, periodType, period, departamento });

            // Actualizar la tabla visible
            const activeTab = document.querySelector('.tab-btn.active').getAttribute('data-tab');
            updateTableData(activeTab);
        });

        document.getElementById('reset-filters').addEventListener('click', function() {
            // Resetear filtros principales
            document.getElementById('year-filter').value = '2025';
            document.getElementById('period-type').value = 'quincena';
            document.getElementById('departamento-filter').value = 'todos';

            // Resetear filtros específicos de cada tabla
            document.getElementById('quincena-filter').value = 'todas';
            document.getElementById('mes-quincena-filter').value = 'todos';
            document.getElementById('mes-filter').value = 'todos';
            document.getElementById('trimestre-filter').value = 'todos';
            document.getElementById('departamento-record-filter').value = 'todos';
            document.getElementById('bono-minimo-filter').value = '';
            document.getElementById('bono-maximo-filter').value = '';
            document.getElementById('resumen-periodo-filter').value = 'anual';

            updatePeriodFilterOptions();

            // Actualizar la tabla visible
            const activeTab = document.querySelector('.tab-btn.active').getAttribute('data-tab');
            updateTableData(activeTab);
        });

        // Event listeners para filtros específicos de cada tabla
        document.getElementById('quincena-filter').addEventListener('change', () => {
            if (document.querySelector('#bonos-quincena').classList.contains('active')) {
                updateQuincenaTable();
            }
        });

        document.getElementById('mes-quincena-filter').addEventListener('change', () => {
            if (document.querySelector('#bonos-quincena').classList.contains('active')) {
                updateQuincenaTable();
            }
        });

        document.getElementById('mes-filter').addEventListener('change', () => {
            if (document.querySelector('#bonos-mes').classList.contains('active')) {
                updateMesTable();
            }
        });

        document.getElementById('trimestre-filter').addEventListener('change', () => {
            if (document.querySelector('#bonos-mes').classList.contains('active')) {
                updateMesTable();
            }
        });

        document.getElementById('departamento-record-filter').addEventListener('change', () => {
            if (document.querySelector('#record-bonos').classList.contains('active')) {
                updateRecordTable();
            }
        });

        document.getElementById('bono-minimo-filter').addEventListener('input', () => {
            if (document.querySelector('#record-bonos').classList.contains('active')) {
                updateRecordTable();
            }
        });

        document.getElementById('bono-maximo-filter').addEventListener('input', () => {
            if (document.querySelector('#record-bonos').classList.contains('active')) {
                updateRecordTable();
            }
        });

        document.getElementById('resumen-periodo-filter').addEventListener('change', () => {
            if (document.querySelector('#resumen-general').classList.contains('active')) {
                updateResumenTable();
            }
        });

        // Inicializar con la primera tabla
        updateQuincenaTable();
    });
</script>
@endsection
