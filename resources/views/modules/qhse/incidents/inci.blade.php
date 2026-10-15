@extends('modules.qhse.incidents.index')

@push('styles')
<style>
    :root {
        /* ── TONOS PRINCIPALES (QHSE - INCIDENCIAS) ── */
        --primary-darker: #051a14;
        --primary-dark: #092c22;
        --primary-mid: #145946;
        --primary-light: #f4f8f6;

        /* ── TONOS SECUNDARIOS / NEUTROS ── */
        --secondary-dark: #111827;
        --secondary-medium: #1f2937;
        --secondary-light: #374151;
        --secondary-lighter: #4b5563;

        /* ── SUPERFICIES ── */
        --surface: #ffffff;
        --surface-alt: #f8fafc;
        --surface-muted: #f1f5f9;

        /* ── COLORES SEMÁNTICOS ── */
        --danger: #dc2626;
        --warning: #f59e0b;
        --info: #3b82f6;
        --success: #10b981;
    }

    /* ══════════════════════════════════════════════════════════════
       DASHBOARD STATS - TARJETAS KPI (más delgadas)
       ══════════════════════════════════════════════════════════════ */
    .dashboard-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
        padding: 1.25rem 0;
        max-width: 1900px;
        margin: 0 auto;
    }

    .stat-card {
        background: var(--surface);
        padding: 1rem 1.25rem;
        border-radius: 14px;
        position: relative;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        gap: 1rem;
        align-items: center;
        height: 95px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.06);
        overflow: hidden;
        cursor: default;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: transparent;
        transition: background 0.35s ease;
    }

    .stat-card:nth-child(1)::before { background: linear-gradient(90deg, var(--primary-mid), var(--primary-dark)); }
    .stat-card:nth-child(2)::before { background: linear-gradient(90deg, #fcd34d, var(--warning)); }
    .stat-card:nth-child(3)::before { background: linear-gradient(90deg, #fca5a5, var(--danger)); }
    .stat-card:nth-child(4)::before { background: linear-gradient(90deg, #93c5fd, var(--info)); }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(9, 44, 34, 0.10), 0 4px 10px rgba(0, 0, 0, 0.04);
        border-color: transparent;
    }

    .stat-icon {
        width: 46px;
        height: 46px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.25rem;
        position: relative;
        color: white;
        flex-shrink: 0;
    }

    .stat-card:nth-child(1) .stat-icon { background: linear-gradient(135deg, var(--primary-mid), var(--primary-dark)); }
    .stat-card:nth-child(2) .stat-icon { background: linear-gradient(135deg, #fcd34d, var(--warning)); }
    .stat-card:nth-child(3) .stat-icon { background: linear-gradient(135deg, #fca5a5, var(--danger)); }
    .stat-card:nth-child(4) .stat-icon { background: linear-gradient(135deg, #93c5fd, var(--info)); }

    .stat-icon::after {
        content: '';
        position: absolute;
        inset: 0;
        background: inherit;
        border-radius: inherit;
        opacity: 0.25;
        transform: scale(1.18);
        z-index: -1;
        filter: blur(6px);
    }

    .stat-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        min-width: 0;
    }

    .stat-title {
        color: var(--secondary-lighter);
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .stat-value {
        font-size: 1.55rem;
        font-weight: 800;
        color: var(--primary-dark);
        line-height: 1;
        letter-spacing: -0.02em;
    }

    /* ══════════════════════════════════════════════════════════════
       DATA GRID (TABLA + FILTROS UNIFICADOS)
       ══════════════════════════════════════════════════════════════ */
    .data-grid-card {
        background: var(--surface);
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 4px 10px rgba(0, 0, 0, 0.02);
        border: 1px solid rgba(226, 232, 240, 0.8);
        margin-bottom: 2rem;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    /* Cabecera del Grid - Fondo verde, más delgada, letras blancas */
    .grid-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.85rem 1.5rem;
        border-bottom: 1px solid var(--primary-mid);
        background: linear-gradient(135deg, var(--primary-dark), var(--primary-mid));
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .grid-header h2 {
        font-size: 1.05rem;
        font-weight: 700;
        color: #ffffff;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.55rem;
    }

    .grid-header h2 i { color: #ffffff; font-size: 1.1rem; }

    /* Botón Nuevo */
    .btn-new {
        background: rgba(255, 255, 255, 0.15);
        color: #fff;
        padding: 0.5rem 1.1rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 9px;
        font-weight: 600;
        font-size: 0.8rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        transition: all 0.25s ease;
        backdrop-filter: blur(4px);
    }

    .btn-new:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    }

    /* Barra de Herramientas (Filtros y Búsqueda General) */
    .grid-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.9rem 1.5rem;
        background-color: #fdfdfd;
        border-bottom: 1px solid var(--surface-muted);
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    /* Buscador General Estilizado */
    .search-box {
        position: relative;
        flex: 1;
        min-width: 280px;
        max-width: 450px;
    }

    .search-box i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 1rem;
    }

    .search-box input {
        width: 100%;
        padding: 0.6rem 1rem 0.6rem 2.5rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.88rem;
        color: var(--secondary-dark);
        background: var(--surface);
        transition: all 0.25s ease;
        font-family: inherit;
    }

    .search-box input:focus {
        outline: none;
        border-color: var(--primary-mid);
        box-shadow: 0 0 0 3px rgba(20, 89, 70, 0.12);
        background: #ffffff;
    }

    .search-box input::placeholder { color: #94a3b8; font-size: 0.85rem; }

    /* Filtros Dropdowns y Botones Icono */
    .filters-group {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .filter-select {
        padding: 0.6rem 1.75rem 0.6rem 1rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.85rem;
        color: var(--secondary-dark);
        background-color: var(--surface);
        outline: none;
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 500;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.5rem center;
        background-size: 1em;
    }

    .filter-select:hover, .filter-select:focus { border-color: var(--primary-mid); }

    /* Botones de solo Icono (Exportar / Reset) */
    .btn-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.25s ease;
    }

    .btn-reset-icon {
        background: var(--surface-muted);
        color: var(--secondary-light);
        border: 1.5px solid #e2e8f0;
    }
    .btn-reset-icon:hover {
        background: #e2e8f0;
        color: var(--secondary-dark);
        border-color: #cbd5e1;
        transform: rotate(-15deg);
    }

    .btn-export-icon {
        background: linear-gradient(135deg, #ea580c, #c2410c);
        color: #fff;
        box-shadow: 0 2px 6px rgba(234, 88, 12, 0.25);
    }
    .btn-export-icon:hover {
        background: linear-gradient(135deg, #c2410c, #9a3412);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(234, 88, 12, 0.35);
    }

    /* Tabla interna */
    .table-container { overflow-x: auto; width: 100%; }

    .table {
        font-size: 0.85rem;
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table th, .table td {
        padding: 0.8rem 0.9rem;
        text-align: center;
        border-bottom: 1px solid var(--surface-muted);
        vertical-align: middle;
    }

    /* Thead con tonalidad verde de la root */
    .table th {
        background: linear-gradient(180deg, var(--primary-light), #e8f0ec);
        color: var(--primary-dark);
        font-weight: 700;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        white-space: nowrap;
        border-bottom: 2px solid rgba(20, 89, 70, 0.15);
    }

    .table tbody tr { transition: background 0.2s ease; }
    .table tbody tr:hover { background-color: #f0fdf4; }
    .table tbody tr:last-child td { border-bottom: none; }

    .numr {
        font-weight: 700;
        background: var(--surface-muted);
        color: var(--secondary-dark);
        border-radius: 6px;
        padding: 3px 8px;
        font-size: 0.78rem;
        display: inline-block;
        min-width: 24px;
        border: 1px solid #e2e8f0;
    }

    /* TAGS / BADGES */
    .tag {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 100px;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        white-space: nowrap;
    }

    .tag-quality { background: rgba(20, 89, 70, 0.12); color: var(--primary-mid); }
    .tag-unsafe { background: rgba(220, 38, 38, 0.10); color: var(--danger); }
    .tag-explosive { background: rgba(245, 158, 11, 0.12); color: #b45309; }
    .tag-high { background: rgba(234, 88, 12, 0.12); color: #c2410c; }
    .tag-closed { background: rgba(59, 130, 246, 0.12); color: #1d4ed8; }

    /* Celdas con truncado y tooltip al hacer click */
    .cell-truncate {
        max-width: 180px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
        margin: 0 auto;
        cursor: pointer;
        transition: color 0.2s ease;
        font-weight: 500;
    }

    .cell-truncate:hover {
        color: var(--primary-mid);
        text-decoration: underline;
        text-decoration-style: dotted;
    }

    /* Columna fecha compacta */
    .fecha-cell {
        white-space: nowrap;
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--secondary-medium);
    }

    .fecha-cell .dia {
        font-weight: 800;
        color: var(--primary-dark);
        font-size: 0.95rem;
        display: block;
        line-height: 1.1;
    }

    .fecha-cell .mes-anio {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: var(--secondary-lighter);
        font-weight: 600;
    }

    /* Botón Ver (Dentro de tabla) */
    .ver-btn {
        background: var(--surface);
        color: var(--primary-mid);
        border: 1px solid #cbd5e1;
        padding: 5px 12px;
        border-radius: 8px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .ver-btn:hover {
        background: var(--primary-light);
        border-color: var(--primary-mid);
        color: var(--primary-dark);
    }

    /* Paginación (Dentro de la Card) */
    .pagination {
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 0.4rem;
        background-color: var(--surface);
        border-top: 1px solid var(--surface-muted);
        flex-wrap: wrap;
    }

    .pagination button {
        padding: 0.5rem 0.85rem;
        border: 1.5px solid #e2e8f0;
        background: var(--surface);
        color: var(--secondary-light);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.25s ease;
        font-weight: 600;
        font-size: 0.82rem;
        min-width: 36px;
    }

    .pagination button:hover:not(.active) {
        background: var(--surface-muted);
        border-color: #cbd5e1;
        color: var(--primary-dark);
    }

    .pagination button.active {
        background: var(--primary-mid);
        color: var(--surface);
        border-color: var(--primary-dark);
        box-shadow: 0 2px 6px rgba(9, 44, 34, 0.2);
    }

    /* ══════════════════════════════════════════════════════════════
       TOOLTIP MODAL (para ver contenido completo de celdas truncadas)
       ══════════════════════════════════════════════════════════════ */
    .cell-tooltip-modal {
        position: fixed;
        z-index: 3000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(5, 26, 20, 0.55);
        backdrop-filter: blur(3px);
        display: none;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: opacity 0.25s ease;
        padding: 1rem;
    }

    .cell-tooltip-modal.show {
        display: flex;
        opacity: 1;
    }

    .cell-tooltip-content {
        background: var(--surface);
        border-radius: 16px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        max-width: 600px;
        width: 95%;
        padding: 24px 28px;
        border-top: 4px solid var(--primary-mid);
        position: relative;
        transform: scale(0.95) translateY(10px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        max-height: 80vh;
        overflow-y: auto;
    }

    .cell-tooltip-modal.show .cell-tooltip-content {
        transform: scale(1) translateY(0);
    }

    .cell-tooltip-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 14px;
        border-bottom: 2px solid var(--surface-muted);
        margin-bottom: 16px;
    }

    .cell-tooltip-header h3 {
        color: var(--primary-dark);
        font-size: 1rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    .cell-tooltip-header h3 i { color: var(--primary-mid); }

    .cell-tooltip-close {
        background: var(--surface-muted);
        border: none;
        color: var(--secondary-medium);
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        transition: all 0.25s ease;
        font-size: 0.9rem;
    }

    .cell-tooltip-close:hover {
        background: var(--danger);
        color: var(--surface);
        transform: rotate(90deg) scale(1.05);
    }

    .cell-tooltip-body {
        color: var(--secondary-dark);
        font-size: 0.92rem;
        line-height: 1.6;
        word-wrap: break-word;
        white-space: normal;
        text-align: left;
    }

    /* ══════════════════════════════════════════════════════════════
       MODAL REPORTE (Se mantiene idéntico)
       ══════════════════════════════════════════════════════════════ */
    .reporte-modal {
        position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%;
        background-color: rgba(5, 26, 20, 0.65); backdrop-filter: blur(4px);
        display: none; justify-content: center; align-items: center;
        opacity: 0; transition: opacity 0.3s ease; padding: 1rem;
    }

    .reporte-modal.show { display: flex; opacity: 1; }

    .reporte-modal-content {
        background-color: var(--surface); border-radius: 20px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25); width: 95%; max-width: 1400px;
        padding: 28px; border-top: 5px solid var(--primary-dark);
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 22px;
        position: relative; transform: scale(0.95) translateY(10px);
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        max-height: 90vh; overflow-y: auto;
    }

    .reporte-modal.show .reporte-modal-content { transform: scale(1) translateY(0); }
    .reporte-modal-content::-webkit-scrollbar { width: 8px; }
    .reporte-modal-content::-webkit-scrollbar-track { background: var(--surface-muted); border-radius: 10px; }
    .reporte-modal-content::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .reporte-modal-content::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    .reporte-modal-header {
        grid-column: 1 / -1; display: flex; justify-content: space-between; align-items: center;
        padding-bottom: 18px; border-bottom: 2px solid var(--surface-muted); margin-bottom: 6px;
    }
    .reporte-modal-header h2 {
        color: var(--primary-dark); font-size: 1.35rem; font-weight: 800;
        display: flex; align-items: center; gap: 10px; margin: 0; letter-spacing: -0.02em;
    }
    .reporte-modal-header h2 i { color: var(--primary-mid); font-size: 1.4rem; }

    .reporte-modal-close {
        background: var(--surface-muted); border: none; color: var(--secondary-medium);
        width: 36px; height: 36px; border-radius: 50%; display: flex; justify-content: center; align-items: center;
        cursor: pointer; transition: all 0.25s ease; font-size: 1rem;
    }
    .reporte-modal-close:hover { background: var(--danger); color: var(--surface); transform: rotate(90deg) scale(1.05); }

    .column { display: flex; flex-direction: column; gap: 16px; }

    .form-group label {
        display: flex; align-items: center; gap: 7px; margin-bottom: 7px;
        color: var(--primary-dark); font-weight: 700; font-size: 0.78rem; text-transform: uppercase;
    }
    .form-group label i { color: var(--primary-mid); font-size: 1rem; }

    .form-group input, .form-group select, .form-group textarea {
        width: 100%; padding: 10px 12px; border: 1.5px solid #e2e8f0; border-radius: 10px;
        font-size: 0.88rem; transition: all 0.25s ease; font-family: inherit; background: var(--surface); color: var(--secondary-dark);
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
        border-color: var(--primary-mid); outline: none; box-shadow: 0 0 0 3px rgba(20, 89, 70, 0.12); background: #fafcfb;
    }
    .form-group textarea { resize: vertical; min-height: 100px; }

    .form-actions {
        grid-column: 1 / -1; display: flex; justify-content: flex-end; gap: 12px;
        margin-top: 12px; padding-top: 22px; border-top: 2px solid var(--surface-muted);
    }

    .btn-guardar { background: linear-gradient(135deg, var(--primary-dark), var(--primary-mid)); color: var(--surface); padding: 11px 28px; border: none; border-radius: 10px; cursor: pointer; font-weight: 700; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 8px; transition: all 0.25s ease; }
    .btn-guardar:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(9, 44, 34, 0.35); }
    .btn-cancelar { background: var(--surface-muted); color: var(--secondary-dark); padding: 11px 28px; border: 1.5px solid #e2e8f0; border-radius: 10px; cursor: pointer; font-weight: 700; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 8px; transition: all 0.25s ease; }
    .btn-cancelar:hover { background: #e2e8f0; border-color: #cbd5e1; transform: translateY(-2px); }

    /* RESPONSIVE */
    @media (max-width: 1200px) { .reporte-modal-content { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 768px) {
        .dashboard-stats { grid-template-columns: 1fr; }
        .grid-toolbar { flex-direction: column; align-items: stretch; }
        .search-box { max-width: 100%; }
        .filters-group { flex-direction: column; align-items: stretch; }
        .action-buttons { display: flex; gap: 10px; justify-content: flex-end; }
        .reporte-modal-content { grid-template-columns: 1fr; padding: 20px; }
        .cell-truncate { max-width: 120px; }
    }
</style>
@endpush

@section('content')
<!-- Dashboard Stats -->
<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
        <div class="stat-content">
            <div class="stat-title">Total Reportes</div>
            <div class="stat-value">1,284</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-content">
            <div class="stat-title">Reportes Abiertos</div>
            <div class="stat-value">45</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-triangle-exclamation"></i></div>
        <div class="stat-content">
            <div class="stat-title">Riesgo Alto</div>
            <div class="stat-value">12</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-content">
            <div class="stat-title">Completados Hoy</div>
            <div class="stat-value">8</div>
        </div>
    </div>
</div>

<section class="reports">
    <!-- DATA GRID UNIFICADO -->
    <div class="data-grid-card">

        <!-- Header del Grid -->
        <div class="grid-header">
            <h2><i class="fas fa-clipboard-check"></i> Reportes de Acciones Correctivas (TACIS)</h2>
            <button type="button" class="btn-new" id="btn-nuevo"><i class="fas fa-plus"></i> Nuevo Registro</button>
        </div>

        <!-- Toolbar (Búsqueda + Filtros) -->
        <div class="grid-toolbar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar reporte, folio, descripción o responsable..." />
            </div>

            <div class="filters-group">
                <select class="filter-select">
                    <option value="">Todos los responsables</option>
                    <option value="1">Alberto Jiménez</option>
                    <option value="2">Ana García</option>
                    <option value="3">Carlos Martínez</option>
                </select>

                <select class="filter-select">
                    <option value="">Todos los años</option>
                    <option value="2026">2026</option>
                    <option value="2025">2025</option>
                    <option value="2024">2024</option>
                </select>

                <button type="button" class="btn-icon btn-reset-icon" title="Restablecer filtros">
                    <i class="fas fa-rotate-left"></i>
                </button>
                <button type="button" class="btn-icon btn-export-icon" title="Exportar a Excel">
                    <i class="fas fa-file-excel"></i>
                </button>
            </div>
        </div>

        <!-- Contenedor Tabla -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Fecha</th>
                        <th>Categoría</th>
                        <th>Tipo</th>
                        <th>Condición</th>
                        <th>Título</th>
                        <th>Peligro</th>
                        <th>Riesgo</th>
                        <th>Nivel</th>
                        <th>Reporta</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="numr">001</span></td>
                        <td>
                            <div class="fecha-cell">
                                <span class="dia">21</span>
                                <span class="mes-anio">Oct 2026</span>
                            </div>
                        </td>
                        <td><span class="tag tag-quality">Calidad</span></td>
                        <td>Condición</td>
                        <td><span class="tag tag-unsafe">Inseguro</span></td>
                        <td>
                            <span class="cell-truncate"
                                  data-full="POLYVORIN 2 ALMACENAMIENTO"
                                  data-label="Título">POLYVORIN 2 ALMACENAMIENTO</span>
                        </td>
                        <td><span class="tag tag-explosive">Explosivos</span></td>
                        <td>Daño a materiales</td>
                        <td><span class="tag tag-high">Muy Alto</span></td>
                        <td>
                            <span class="cell-truncate"
                                  data-full="Alberto Jiménez"
                                  data-label="Reporta">Alberto Jiménez</span>
                        </td>
                        <td><span class="tag tag-closed">Cerrado</span></td>
                        <td>
                            <button class="ver-btn" title="Ver Detalles"><i class="fas fa-eye"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="numr">002</span></td>
                        <td>
                            <div class="fecha-cell">
                                <span class="dia">22</span>
                                <span class="mes-anio">Oct 2026</span>
                            </div>
                        </td>
                        <td><span class="tag tag-quality" style="background: rgba(59, 130, 246, 0.12); color: #1d4ed8;">Seguridad</span></td>
                        <td>Acto</td>
                        <td><span class="tag tag-quality">Seguro</span></td>
                        <td>
                            <span class="cell-truncate"
                                  data-full="INSPECCIÓN DE RUTINA LÍNEA 4"
                                  data-label="Título">INSPECCIÓN DE RUTINA LÍNEA 4</span>
                        </td>
                        <td><span class="tag tag-closed">Ninguno</span></td>
                        <td>Prevenido</td>
                        <td><span class="tag" style="background: #f1f5f9; color:#475569">Bajo</span></td>
                        <td>
                            <span class="cell-truncate"
                                  data-full="Ana García"
                                  data-label="Reporta">Ana García</span>
                        </td>
                        <td><span class="tag tag-quality" style="background: #fef3c7; color: #b45309">En Proceso</span></td>
                        <td>
                            <button class="ver-btn" title="Ver Detalles"><i class="fas fa-eye"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer / Paginación -->
        <div class="pagination">
            <button title="Página anterior"><i class="fas fa-chevron-left"></i></button>
            <button class="active">1</button>
            <button>2</button>
            <button>3</button>
            <span style="color: var(--secondary-light);">...</span>
            <button>10</button>
            <button title="Página siguiente"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
</section>

<!-- TOOLTIP MODAL (para celdas truncadas) -->
<div class="cell-tooltip-modal" id="cellTooltipModal">
    <div class="cell-tooltip-content">
        <div class="cell-tooltip-header">
            <h3><i class="fas fa-info-circle"></i> <span id="tooltipLabel">Detalle</span></h3>
            <button class="cell-tooltip-close" id="btnCloseTooltip"><i class="fas fa-times"></i></button>
        </div>
        <div class="cell-tooltip-body" id="tooltipBody"></div>
    </div>
</div>

<!-- MODAL REPORTE -->
<div class="reporte-modal" id="modalReporte">
    <div class="reporte-modal-content">
        <div class="reporte-modal-header">
            <h2><i class="fas fa-clipboard-list"></i> Captura de Reporte</h2>
            <button class="reporte-modal-close" id="btnCloseModal"><i class="fas fa-times"></i></button>
        </div>

        <div class="column">
            <div class="form-group">
                <label for="num-reporte"><i class="fas fa-hashtag"></i> Número de Reporte</label>
                <input type="text" id="num-reporte" required placeholder="Ingrese número">
            </div>
            <div class="form-group">
                <label for="titulo-reporte"><i class="fas fa-heading"></i> Título del Reporte</label>
                <input type="text" id="titulo-reporte" required placeholder="Ingrese título">
            </div>
            <div class="form-group">
                <label for="categoria"><i class="fas fa-tags"></i> Categoría</label>
                <select id="categoria" required>
                    <option value="">Seleccione categoría</option>
                    <option value="calidad">CALIDAD</option>
                    <option value="seguridad">Seguridad</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
            <div class="form-group">
                <label for="tipo-tarjeta"><i class="fas fa-id-card"></i> Tipo Tarjeta</label>
                <select id="tipo-tarjeta" required>
                    <option value="">Seleccione Tipo</option>
                    <option value="tipo1">Tipo 1</option>
                    <option value="tipo2">Tipo 2</option>
                </select>
            </div>
            <div class="form-group">
                <label for="descripcion"><i class="fas fa-align-left"></i> Descripción</label>
                <textarea id="descripcion" rows="4" required placeholder="Detalle lo sucedido..."></textarea>
            </div>
        </div>

        <div class="column">
            <div class="form-group">
                <label for="tipo-condicion"><i class="fas fa-shield-alt"></i> Tipo Condición</label>
                <select id="tipo-condicion" required>
                    <option value="">Seleccione Condición</option>
                    <option value="seguro">Seguro</option>
                    <option value="inseguro">Inseguro</option>
                </select>
            </div>
            <div class="form-group">
                <label for="peligro"><i class="fas fa-exclamation-triangle"></i> Peligro</label>
                <select id="peligro" required>
                    <option value="">Seleccione Peligro</option>
                    <option value="caida">Caída de objetos</option>
                    <option value="incendio">Incendio</option>
                </select>
            </div>
            <div class="form-group">
                <label for="consecuencia-perdida"><i class="fas fa-chart-line"></i> Consecuencia</label>
                <input type="text" id="consecuencia-perdida" required placeholder="Consecuencia">
            </div>
            <div class="form-group">
                <label for="probabilidad"><i class="fas fa-chart-bar"></i> Probabilidad</label>
                <select id="probabilidad" required>
                    <option value="">Seleccione</option>
                    <option value="baja">Baja</option>
                    <option value="media">Media</option>
                    <option value="alta">Alta</option>
                </select>
            </div>
        </div>

        <div class="column">
            <div class="form-group">
                <label for="valor-riesgo"><i class="fas fa-radiation"></i> Valor Riesgo</label>
                <input type="number" id="valor-riesgo" required placeholder="Valor">
            </div>
            <div class="form-group">
                <label for="nivel-riesgo"><i class="fas fa-tachometer-alt"></i> Nivel Riesgo</label>
                <select id="nivel-riesgo" required>
                    <option value="">Seleccione Nivel</option>
                    <option value="bajo">Bajo</option>
                    <option value="medio">Medio</option>
                    <option value="alto">Alto</option>
                </select>
            </div>
            <div class="form-group">
                <label for="cliente"><i class="fas fa-user-tie"></i> Cliente</label>
                <input type="text" id="cliente" required placeholder="Nombre del cliente">
            </div>
            <div class="form-group">
                <label for="mes-reporte"><i class="fas fa-calendar-alt"></i> Mes Reporte</label>
                <select id="mes-reporte" required>
                    <option value="">Seleccione Mes</option>
                    <option value="enero">Enero</option>
                    <option value="febrero">Febrero</option>
                </select>
            </div>
        </div>

        <div class="column">
            <div class="form-group">
                <label for="fecha-reporte"><i class="fas fa-calendar-day"></i> Fecha Reporte</label>
                <input type="date" id="fecha-reporte" required>
            </div>
            <div class="form-group">
                <label for="fecha-evento"><i class="fas fa-calendar-check"></i> Fecha Evento</label>
                <input type="date" id="fecha-evento" required>
            </div>
            <div class="form-group">
                <label for="estatus"><i class="fas fa-info-circle"></i> Estatus</label>
                <select id="estatus" required>
                    <option value="">Seleccione Estatus</option>
                    <option value="abierto">Abierto</option>
                    <option value="cerrado">Cerrado</option>
                </select>
            </div>
            <div class="form-group">
                <label for="tipo-atencion"><i class="fas fa-medkit"></i> Tipo Atención</label>
                <select id="tipo-atencion" required>
                    <option value="">Seleccione</option>
                    <option value="preventiva">Preventiva</option>
                    <option value="correctiva">Correctiva</option>
                </select>
            </div>
            <div class="form-group">
                <label for="reporta"><i class="fas fa-user-hard-hat"></i> Reporta</label>
                <select id="reporta" required>
                    <option value="">Seleccione persona</option>
                    <option value="1">Alberto Jiménez</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn-cancelar" id="btnCancelForm"><i class="fas fa-times"></i> Cancelar</button>
            <button type="submit" class="btn-guardar"><i class="fas fa-save"></i> Guardar Reporte</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // ─── MODAL REPORTE ───
        const modal = document.getElementById('modalReporte');
        const btnNuevo = document.getElementById('btn-nuevo');
        const btnClose = document.getElementById('btnCloseModal');
        const btnCancel = document.getElementById('btnCancelForm');

        function openModal() {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        btnNuevo.addEventListener('click', openModal);
        btnClose.addEventListener('click', closeModal);
        btnCancel.addEventListener('click', closeModal);

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        // ─── TOOLTIP MODAL (celdas truncadas) ───
        const tooltipModal = document.getElementById('cellTooltipModal');
        const tooltipBody = document.getElementById('tooltipBody');
        const tooltipLabel = document.getElementById('tooltipLabel');
        const btnCloseTooltip = document.getElementById('btnCloseTooltip');

        function openTooltip(label, fullText) {
            tooltipLabel.textContent = label || 'Detalle';
            tooltipBody.textContent = fullText || '';
            tooltipModal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeTooltip() {
            tooltipModal.classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        // Event delegation para celdas truncadas
        document.querySelectorAll('.cell-truncate').forEach(cell => {
            cell.addEventListener('click', () => {
                const fullText = cell.getAttribute('data-full') || cell.textContent;
                const label = cell.getAttribute('data-label') || 'Detalle';
                openTooltip(label, fullText);
            });
        });

        btnCloseTooltip.addEventListener('click', closeTooltip);

        tooltipModal.addEventListener('click', (event) => {
            if (event.target === tooltipModal) {
                closeTooltip();
            }
        });

        // ─── ESC para cerrar cualquier modal ───
        document.addEventListener('keydown', (event) => {
            if (event.key === "Escape") {
                if (modal.classList.contains('show')) closeModal();
                if (tooltipModal.classList.contains('show')) closeTooltip();
            }
        });
    });
</script>
@endpush
