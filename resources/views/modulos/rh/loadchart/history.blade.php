@extends('modulos.rh.loadchart.index')
<style>
    /* Variables CSS actualizadas con los colores proporcionados */
    :root {
        /* Colores Primarios y de Estado */
        --primary-color: #2d3748;
        --secondary-color: #4a5568;
        --accent-color: #d67e29;
        --white: #ffffff;

        /* Colores de Tipos de Actividad */
        --work-base: #334c95;
        --work-well: #6475aa;
        --home-office: #7293ff;
        --traveling: #a2b5ff;
        --rest: #118b20;
        --vacation: #59983e;
        --training: #a5a5a5;
        --medical: #dd840fe0;
        --absence: #7d4e4e;
        --permission: #49c0c9;
        --commissioned: #249399;

        /* Colores de Estado */
        --under-review: #ffdc13ef;
        --reviewed-yellow: #ffa723ef;
        --approved-green: #26b30e;
        --rejected-red: #f55134e5;

        /* Colores de Fondo y Texto */
        --light-color: #f8fafc;
        --dark-color: #2d3748;
        --medium-text: #64748b;
        --border-color: #e2e8f0;
        --card-bg: #ffffff;

        --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-light: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        --shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    /* Contenedor Principal */
    .history-container {
        padding: 16px;
        background: var(--light-color);
        min-height: 100vh;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    /* Tarjeta Base */
    .card-base {
        background: var(--card-bg);
        border-radius: 12px;
        margin-bottom: 16px;
        box-shadow: var(--shadow);
        overflow: hidden;
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .card-base:hover {
        box-shadow: var(--shadow-hover);
    }

    /* Header Compacto y Moderno */
    .compact-header {
        padding: 20px 24px;
        background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
        color: white;
        position: relative;
        overflow: hidden;
    }

    .compact-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transform: translate(30%, -30%);
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        z-index: 2;
    }

    .title-section {
        flex: 1;
    }

    .history-title {
        color: white;
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .history-title i {
        font-size: 1.8rem;
        opacity: 0.9;
    }

    .history-subtitle {
        color: rgba(255, 255, 255, 0.8);
        margin: 6px 0 0 0;
        font-size: 0.875rem;
        font-weight: 400;
    }

    /* Estadísticas Compactas y Modernas */
    .header-stats {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
    }

    .stat-card {
        display: flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        padding: 12px 16px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        min-width: 140px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .stat-card:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 1.2rem;
        background: rgba(255, 255, 255, 0.2);
    }

    .stat-icon.approved {
        color: var(--approved-green);
    }

    .stat-icon.reviewed {
        color: var(--reviewed-yellow);
    }

    .stat-icon.rejected {
        color: var(--rejected-red);
    }

    .stat-icon.under-review {
        color: var(--under-review);
    }

    .stat-info {
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        margin-left: 10px;
        line-height: 1.2;
    }

    .stat-number {
        display: block;
        font-size: 1.5rem;
        font-weight: 700;
        color: white;
        line-height: 1;
    }

    .stat-label {
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.9);
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    /* Panel de Filtros Compacto y Moderno */
    .filters-section {
        padding: 20px 24px;
        background: var(--card-bg);
        border-bottom: 1px solid var(--border-color);
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
    }

    .filter-group label {
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--medium-text);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-control {
        padding: 10px 12px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        background-color: var(--white);
        color: var(--dark-color);
        height: 42px;
        font-family: inherit;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--work-base);
        box-shadow: 0 0 0 3px rgba(51, 76, 149, 0.1);
    }

    .filter-actions {
        display: flex;
        gap: 8px;
        align-items: end;
    }

    .btn-apply-filters {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        height: 42px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.875rem;
    }

    .btn-apply-filters:hover {
        background: var(--secondary-color);
        transform: translateY(-1px);
        box-shadow: var(--shadow-light);
    }

    .btn-clear-filters {
        background: var(--medium-text);
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
        height: 42px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-clear-filters:hover {
        background: var(--dark-color);
        transform: translateY(-1px);
        box-shadow: var(--shadow-light);
    }

    /* Contenido Principal */
    .history-content {
        margin-bottom: 20px;
    }

    .quick-summary {
        padding: 12px 24px;
        background: var(--secondary-color);
        border-bottom: 1px solid var(--border-color);
    }

    .summary-content {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--white);
        font-weight: 600;
        font-size: 0.875rem;
    }

    /* Tabla Mejorada y Compacta */
    .table-container {
        padding: 0;
    }

    .table-responsive {
        overflow-x: auto;
        border-radius: 0 0 12px 12px;
    }

    .history-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        font-size: 0.8rem;
        background: var(--card-bg);
    }

    .history-table th {
        background: var(--light-color);
        padding: 14px 12px;
        font-weight: 700;
        color: var(--secondary-color);
        border-bottom: 2px solid var(--border-color);
        text-align: left;
        text-transform: uppercase;
        font-size: 0.75rem;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 10;
        letter-spacing: 0.5px;
    }

    .history-table td {
        padding: 12px;
        border-bottom: 1px solid var(--border-color);
        color: var(--dark-color);
    }

    .history-table tbody tr {
        transition: all 0.3s ease;
    }

    .history-table tbody tr:hover {
        background: #f8fafc;
        transform: scale(1.002);
    }

    /* **Ajuste para centrar columnas solicitadas (HORIZONTALMENTE)** */
    .history-table th:nth-child(1),
    .history-table td:nth-child(1),
    .history-table th:nth-child(2),
    .history-table td:nth-child(2),
    .history-table th:nth-child(3),
    .history-table td:nth-child(3),
    .history-table th:nth-child(4),
    .history-table td:nth-child(4) {
        text-align: center;
    }

    /* Estados de la tabla - Colores actualizados */
    .status-badge {
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        display: inline-block;
        min-width: 80px;
        text-align: center;
        letter-spacing: 0.5px;
    }

    .status-approved {
        background: rgba(38, 179, 14, 0.1);
        color: var(--approved-green);
        border: 1px solid var(--approved-green);
    }

    .status-reviewed {
        background: rgba(255, 167, 35, 0.1);
        color: var(--reviewed-yellow);
        border: 1px solid var(--reviewed-yellow);
    }

    .status-rejected {
        background: rgba(245, 81, 52, 0.1);
        color: var(--rejected-red);
        border: 1px solid var(--rejected-red);
    }

    .status-under_review {
        background: rgba(255, 220, 19, 0.1);
        color: var(--under-review);
        border: 1px solid var(--under-review);
    }

    /* Celdas específicas */
    .date-cell {
        display: flex;
        flex-direction: column;
        gap: 4px;
        align-items: center;
    }

    .date-cell strong {
        font-size: 0.8rem;
        color: var(--dark-color);
        font-weight: 600;
    }

    .date-cell .text-muted {
        font-size: 0.7rem;
        color: var(--medium-text);
    }

    .activity-type-cell {
        text-align: center;
    }

    .activity-type-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--white);
        letter-spacing: 0.5px;
    }

    /* Colores para tipos de actividad */
    .activity-type-badge.B { background: var(--work-base); }
    .activity-type-badge.P { background: var(--work-well); }
    .activity-type-badge.TC { background: var(--home-office); }
    .activity-type-badge.V { background: var(--traveling); }
    .activity-type-badge.D { background: var(--rest); }
    .activity-type-badge.VAC { background: var(--vacation); }
    .activity-type-badge.E { background: var(--training); }
    .activity-type-badge.M { background: var(--medical); }
    .activity-type-badge.A { background: var(--absence); }
    .activity-type-badge.PE { background: var(--permission); }
    .activity-type-badge.C { background: var(--commissioned); }

    /* Actividad principal */
    .activity-main {
        display: flex;
        flex-direction: column;
        gap: 4px;
        align-items: center;
        text-align: center;
    }

    .activity-main strong {
        font-size: 0.8rem;
        color: var(--secondary-color);
        font-weight: 600;
    }

    .activity-details {
        font-size: 0.75rem;
        color: var(--medium-text);
        line-height: 1.3;
        display: none;
    }

    /* Items de actividad - DISPOSICIÓN HORIZONTAL */
    .activity-items {
        display: flex;
        flex-direction: row;
        gap: 8px;
        flex-wrap: wrap;
    }

    .activity-item {
        padding: 8px;
        background: var(--light-color);
        border-radius: 6px;
        border-left: 3px solid var(--primary-color);
        font-size: 0.75rem;
        transition: all 0.3s ease;
        min-width: 120px;
        flex: 1;
    }

    .activity-item:hover {
        transform: translateX(2px);
    }

    .activity-item.rejected {
        border-left-color: var(--rejected-red);
        background: rgba(245, 81, 52, 0.05);
    }

    .item-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 4px;
    }

    .item-concept {
        font-weight: 600;
        color: var(--secondary-color);
        flex: 1;
    }

    .item-amount {
        font-weight: 700;
        color: var(--dark-color);
        font-size: 0.75rem;
        white-space: nowrap;
    }

    .item-body {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .item-type {
        color: var(--medium-text);
        font-size: 0.7rem;
        font-weight: 500;
    }

    .item-details {
        color: var(--medium-text);
        font-size: 0.7rem;
        line-height: 1.3;
        white-space: normal;
    }

    .item-comment {
        background: rgba(255, 255, 255, 0.7);
        padding: 4px 6px;
        border-radius: 4px;
        border-left: 2px solid var(--accent-color);
        margin-top: 3px;
        font-size: 0.7rem;
        color: var(--dark-color);
        font-style: italic;
    }

    .rejection-reason {
        color: var(--rejected-red);
        font-size: 0.7rem;
        font-style: italic;
        margin-top: 3px;
        padding: 3px 5px;
        background: rgba(245, 81, 52, 0.1);
        border-radius: 3px;
        border-left: 2px solid var(--rejected-red);
    }

    /* Botones de acción */
    .btn-action {
        background: var(--white);
        border: 1px solid var(--border-color);
        padding: 6px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        color: var(--medium-text);
        font-size: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
    }

    .btn-action:hover {
        background: var(--secondary-color);
        color: white;
        border-color: var(--primary-color);
        transform: translateY(-1px);
        box-shadow: var(--shadow-light);
    }

    /* Estados de carga y vacío */
    .loading-row {
        padding: 60px 15px;
        text-align: center;
    }

    .loading-spinner {
        width: 32px;
        height: 32px;
        border: 3px solid var(--border-color);
        border-top: 3px solid var(--work-base);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 12px;
    }

    .empty-state {
        text-align: center;
        padding: 80px 15px;
    }

    .empty-icon {
        font-size: 4rem;
        color: var(--border-color);
        margin-bottom: 16px;
        opacity: 0.5;
    }

    .empty-state h3 {
        color: var(--medium-text);
        margin-bottom: 8px;
        font-size: 1.3rem;
        font-weight: 600;
    }

    .empty-state p {
        color: var(--medium-text);
        margin-bottom: 20px;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .filters-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .history-container { padding: 12px; }
        .compact-header { padding: 16px 20px; }
        .header-content { flex-direction: column; align-items: flex-start; gap: 16px; }
        .header-stats { width: 100%; justify-content: space-between; }
        .stat-card { min-width: calc(25% - 12px); flex-direction: column; text-align: center; padding: 12px; }
        .stat-icon { margin-right: 0; margin-bottom: 8px; }
        .stat-info { text-align: center; align-items: center; margin-left: 0; }
        .filters-section { padding: 16px 20px; }
        .filters-grid { grid-template-columns: 1fr; }
        .filter-actions { flex-direction: column; }
        .history-table { font-size: 0.75rem; }
        .history-table th, .history-table td { padding: 10px 8px; }
        .activity-items { flex-direction: column; }
    }

    @media (max-width: 576px) {
        .header-stats { flex-direction: column; width: 100%; }
        .stat-card { width: 100%; flex-direction: row; text-align: left; }
        .stat-icon { margin-right: 12px; margin-bottom: 0; }
        .stat-info { text-align: left; align-items: flex-start; margin-left: 0; }
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .fade-in { animation: fadeIn 0.5s ease-in-out; }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .table-responsive::-webkit-scrollbar { height: 6px; }
    .table-responsive::-webkit-scrollbar-track { background: var(--light-color); border-radius: 3px; }
    .table-responsive::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 3px; }
    .table-responsive::-webkit-scrollbar-thumb:hover { background: var(--medium-text); }

    .activity-id {
        background: var(--accent-color);
        color: white;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: bold;
        margin-right: 6px;
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

    .custom-modal-backdrop.show { display: flex; opacity: 1; }

    .custom-modal-dialog {
        background: var(--card-bg);
        border-radius: 12px;
        box-shadow: var(--shadow-hover);
        max-width: 900px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        transform: translateY(-50px);
        transition: transform 0.3s ease;
    }

    .custom-modal-backdrop.show .custom-modal-dialog { transform: translateY(0); }

    .custom-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
    }

    .custom-modal-header h5 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark-color);
    }

    .custom-modal-header .close-btn {
        background: transparent;
        border: none;
        font-size: 1.5rem;
        color: var(--medium-text);
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .custom-modal-header .close-btn:hover { color: var(--rejected-red); }
    .custom-modal-body { padding: 24px; }

    .custom-modal-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
    }

    .btn-close-modal {
        background: var(--medium-text);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.875rem;
    }

    .btn-close-modal:hover { background: var(--dark-color); transform: translateY(-1px); }

    .detail-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--border-color);
    }

    .detail-header h4 { margin: 0; font-size: 1.25rem; color: var(--primary-color); font-weight: 600; }

    .detail-section {
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px dotted var(--border-color);
    }

    .detail-section:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

    .detail-section h5 {
        color: var(--secondary-color);
        margin-bottom: 15px;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .detail-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px; }

    .detail-item { display: flex; flex-direction: column; gap: 5px; }

    .detail-item label { font-weight: 600; color: var(--medium-text); font-size: 0.85rem; }

    .fonttype .activity-type-badge { color: var(--white) !important; }

    .detail-item span { color: var(--dark-color); font-size: 0.9rem; }

    .items-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 15px; }

    .detail-item-card {
        padding: 15px;
        background: var(--light-color);
        border-radius: 8px;
        border-left: 4px solid var(--work-base);
        box-shadow: var(--shadow-light);
    }

    .detail-item-card.rejected {
        border-left-color: var(--rejected-red);
        background: rgba(245, 81, 52, 0.05);
    }

    .detail-item-card .item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .detail-item-card .item-body { display: flex; flex-direction: column; gap: 8px; }

    .rejection-note {
        color: var(--rejected-red);
        font-style: italic;
        padding: 8px;
        background: rgba(245, 81, 52, 0.1);
        border-radius: 4px;
        border-left: 3px solid var(--rejected-red);
    }

    .item-id-amount { display: flex; flex-direction: column; gap: 2px; margin-top: 4px; }
    .item-id { font-size: 0.65rem; color: var(--medium-text); font-weight: 600; }
    .item-amount-separate { font-size: 0.75rem; font-weight: 700; color: var(--dark-color); }
    .item-no-concept { font-style: italic; color: var(--medium-text); }
</style>

@section('content')
    <div class="history-container">
        <div class="card-base">
            <div class="compact-header">
                <div class="header-content">
                    <div class="title-section">
                        <h1 class="history-title">
                            <i class="fas fa-history"></i>
                            Historial de Actividades
                        </h1>
                        <p class="history-subtitle">
                            Consulta el registro completo de tus actividades y su estado de aprobación
                        </p>
                    </div>

                    <div class="header-stats">
                        <div class="stat-card" data-status="approved">
                            <div class="stat-icon approved">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="approved-count">0</span>
                                <span class="stat-label">Aprobadas</span>
                            </div>
                        </div>
                        <div class="stat-card" data-status="reviewed">
                            <div class="stat-icon reviewed">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="stat-info"> <span class="stat-number" id="reviewed-count">0</span>
                                <span class="stat-label">Revisadas</span>
                            </div>
                        </div>
                        <div class="stat-card" data-status="rejected">
                            <div class="stat-icon rejected">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="rejected-count">0</span>
                                <span class="stat-label">Rechazadas</span>
                            </div>
                        </div>
                        <div class="stat-card" data-status="under_review">
                            <div class="stat-icon under-review">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="under-review-count">0</span>
                                <span class="stat-label">En Revisión</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="filters-section">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="start-date">
                            <i class="fas fa-calendar-alt"></i>
                            Fecha Inicio
                        </label>
                        <input type="date" id="start-date" class="form-control">
                    </div>

                    <div class="filter-group">
                        <label for="end-date">
                            <i class="fas fa-calendar-alt"></i>
                            Fecha Fin
                        </label>
                        <input type="date" id="end-date" class="form-control">
                    </div>

                    <div class="filter-group">
                        <label for="status-filter">
                            <i class="fas fa-filter"></i>
                            Estado
                        </label>
                        <select id="status-filter" class="form-control">
                            <option value="all">Todos los estados</option>
                            <option value="approved">Aprobado</option>
                            <option value="reviewed">Revisado</option>
                            <option value="rejected">Rechazado</option>
                            <option value="under_review">En Revisión</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="activity-type-filter">
                            <i class="fas fa-tag"></i>
                            Tipo de Actividad
                        </label>
                        <select id="activity-type-filter" class="form-control">
                            <option value="all">Todos los tipos</option>
                            <option value="B">Trabajo en Base</option>
                            <option value="P">Trabajo en Pozo</option>
                            <option value="C">Comisionado</option>
                            <option value="TC">Trabajo en Casa</option>
                            <option value="V">Viaje</option>
                            <option value="D">Descanso</option>
                            <option value="VAC">Vacaciones</option>
                            <option value="E">Entrenamiento</option>
                            <option value="M">Médico</option>
                            <option value="A">Ausencia</option>
                            <option value="PE">Permiso</option>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button class="btn-apply-filters" id="apply-filters">
                            <i class="fas fa-search"></i>
                            Aplicar Filtros
                        </button>
                        <button class="btn-clear-filters" id="clear-filters">
                            <i class="fas fa-eraser"></i>
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="history-content card-base">
            <div class="quick-summary" id="quick-summary" style="display: none;">
                <div class="summary-content">
                    <i class="fas fa-info-circle"></i>
                    <span id="summary-text"></span>
                </div>
            </div>

            <div class="table-container">
                <div class="table-responsive">
                    <table class="history-table" id="history-table">
                        <thead>
                            <tr>
                                <th width="100px">
                                    <i class="fas fa-calendar"></i>
                                    Fecha
                                </th>
                                <th width="80px">
                                    <i class="fas fa-tag"></i>
                                    Tipo
                                </th>
                                <th width="150px">
                                    <i class="fas fa-tasks"></i>
                                    Actividad
                                </th>
                                <th width="100px">
                                    <i class="fas fa-info-circle"></i>
                                    Estado
                                </th>
                                <th>
                                    <i class="fas fa-list"></i>
                                    Detalles
                                </th>
                                <th width="70px" class="text-center">
                                    <i class="fas fa-ellipsis-v"></i>
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody id="history-table-body">
                            <tr>
                                <td colspan="6" class="text-center loading-row">
                                    <div class="loading-spinner"></div>
                                    <p>Cargando historial...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="empty-state" id="empty-state" style="display: none;">
                    <div class="empty-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <div>
                        <h3>No se encontraron registros</h3>
                        <p>No hay actividades que coincidan con los filtros aplicados.</p>
                        <button class="btn-clear-filters" style="margin: 0 auto;" onclick="window.historyManager.clearFilters()">
                            <i class="fas fa-times"></i>
                            Limpiar filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL EN HTML PURO --}}
    <div class="custom-modal-backdrop" id="detailModalBackdrop">
        <div class="custom-modal-dialog">
            <div class="custom-modal-content">
                <div class="custom-modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle"></i>
                        Detalles de Actividad
                    </h5>
                    <button type="button" class="close-btn" id="closeModalHeaderBtn" aria-label="Cerrar">
                        &times;
                    </button>
                </div>
                <div class="custom-modal-body" id="detail-modal-content">
                </div>
                <div class="custom-modal-footer">
                    <button type="button" class="btn-close-modal" id="closeModalFooterBtn">
                        <i class="fas fa-times"></i>
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Clase para manejar el modal sin Bootstrap
            class CustomModal {
                constructor(modalId) {
                    this.modalBackdrop = document.getElementById(modalId + 'Backdrop');
                    this.closeHeaderBtn = document.getElementById('closeModalHeaderBtn');
                    this.closeFooterBtn = document.getElementById('closeModalFooterBtn');
                    this.contentElement = document.getElementById('detail-modal-content');

                    if (!this.modalBackdrop) {
                        console.error(`El modal con ID ${modalId} no se pudo encontrar.`);
                        return;
                    }

                    this.bindEvents();
                }

                bindEvents() {
                    // Cerrar al hacer clic en el fondo
                    this.modalBackdrop.addEventListener('click', (e) => {
                        if (e.target === this.modalBackdrop) {
                            this.hide();
                        }
                    });
                    // Cerrar con los botones
                    this.closeHeaderBtn.addEventListener('click', () => this.hide());
                    this.closeFooterBtn.addEventListener('click', () => this.hide());
                    // Cerrar con la tecla ESC
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape') {
                            this.hide();
                            // Detener la propagación para evitar acciones adicionales si el modal está abierto
                            if (this.modalBackdrop.classList.contains('show')) {
                                e.stopPropagation();
                            }
                        }
                    });
                }

                show() {
                    this.modalBackdrop.classList.add('show');
                    document.body.style.overflow = 'hidden'; // Evita el scroll del cuerpo
                }

                hide() {
                    this.modalBackdrop.classList.remove('show');
                    document.body.style.overflow = ''; // Restaura el scroll del cuerpo
                }

                setContent(contentHTML) {
                    this.contentElement.innerHTML = contentHTML;
                }
            }

            // Inicialización del modal custom
            const detailModalInstance = new CustomModal('detailModal');


            class HistoryManager {
                constructor() {
                    this.currentPage = 1;
                    this.filters = {
                        start_date: '',
                        end_date: '',
                        status: 'all',
                        activity_type: 'all'
                    };
                    this.employeeName = '';
                    this.init();
                }

                init() {
                    this.bindEvents();
                    this.setupDateDefaults();
                    this.loadHistory();
                }

                bindEvents() {
                    document.getElementById('apply-filters').addEventListener('click', () => this.applyFilters());
                    document.getElementById('clear-filters').addEventListener('click', () => this.clearFilters());

                    document.getElementById('start-date').addEventListener('change', (e) => this.onDateChange());
                    document.getElementById('end-date').addEventListener('change', (e) => this.onDateChange());
                    document.getElementById('status-filter').addEventListener('change', (e) => this.onFilterChange());
                    document.getElementById('activity-type-filter').addEventListener('change', (e) => this.onFilterChange());

                    document.querySelectorAll('.stat-card').forEach(card => {
                        card.addEventListener('click', (e) => {
                            const status = e.currentTarget.getAttribute('data-status');
                            if (status) {
                                document.getElementById('status-filter').value = status;
                                this.applyFilters();
                            }
                        });
                    });
                }

                setupDateDefaults() {
                    const today = new Date();
                    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

                    document.getElementById('start-date').value = this.formatDate(firstDay);
                    document.getElementById('end-date').value = this.formatDate(today);

                    this.filters.start_date = this.formatDate(firstDay);
                    this.filters.end_date = this.formatDate(today);
                }

                formatDate(date) {
                    return date.toISOString().split('T')[0];
                }

                async loadHistory(page = 1) {
                    this.showLoading();

                    try {
                        const params = new URLSearchParams({
                            ...this.filters,
                            page: page
                        });

                        const response = await fetch(`/rh/loadchart/history/data?${params}`);
                        const data = await response.json();

                        if (data.success) {
                            this.employeeName = data.employee_name || '';
                            const historyData = data.history.data || data.history;
                            this.renderHistory(historyData);
                            this.updateStats(historyData);
                            this.updateSummary(historyData.length);
                        } else {
                            this.showError(data.message || 'Error al cargar el historial');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.showError('Error de conexión al servidor');
                    }
                }

                renderHistory(history) {
                    const tbody = document.getElementById('history-table-body');
                    tbody.innerHTML = '';

                    if (history.length === 0) {
                        this.showEmptyState();
                        return;
                    }

                    tbody.innerHTML = history.map(item => this.createHistoryRow(item)).join('');
                    this.bindRowEvents();
                    document.getElementById('empty-state').style.display = 'none';

                    setTimeout(() => {
                        document.querySelectorAll('#history-table-body tr').forEach((row, index) => {
                            row.style.animationDelay = `${index * 0.05}s`;
                            row.classList.add('fade-in');
                        });
                    }, 100);
                }

                createHistoryRow(item) {
                    const hasRejections = item.has_rejections;
                    const rowClass = hasRejections ? 'has-rejection' : '';
                    const statusClass = `status-${item.overall_status}`;
                    const itemDataString = JSON.stringify(item).replace(/"/g, '&#34;');

                    // ⭐ LÓGICA PARA RENDERIZAR BADGES DOBLES SI ES GUARDIA
                    let typeBadgesHTML = `<span class="activity-type-badge ${item.activity_type}" title="${this.getActivityTypeText(item.activity_type)}">${item.activity_type}</span>`;
                    let descHTML = `<strong>${item.activity_description}</strong>`;

                    if (item.activity_type_vespertina && item.activity_type_vespertina !== 'N') {
                        typeBadgesHTML += `<br><span class="activity-type-badge ${item.activity_type_vespertina}" title="${this.getActivityTypeText(item.activity_type_vespertina)}" style="margin-top: 4px;">${item.activity_type_vespertina}</span>`;
                        descHTML = `<div style="display:flex; flex-direction:column; gap:4px;">
                                        <span><small class="text-muted">Mat:</small> <strong>${item.activity_description}</strong></span>
                                        <span><small class="text-muted">Vesp:</small> <strong>${item.activity_description_vespertina}</strong></span>
                                    </div>`;
                    }

                    return `
                <tr class="${rowClass}" data-item='${itemDataString}'>
                    <td class="text-center">
                        <div class="date-cell">
                            <small class="text-muted">${item.day_name}</small>
                            <strong>${item.date}</strong>
                        </div>
                    </td>
                    <td class="activity-type-cell">
                        ${typeBadgesHTML}
                    </td>
                    <td>
                        <div class="activity-main">
                            ${descHTML}
                        </div>
                    </td>
                    <td>
                        <span class="status-badge ${statusClass}">
                            ${this.getStatusText(item.overall_status)}
                        </span>
                    </td>
                    <td>
                        <div class="activity-items">
                            ${item.daily_items.map(subItem => this.createSubItem(subItem)).join('')}
                        </div>
                    </td>
                    <td class="text-center">
                        <button class="btn-action btn-view-details" title="Ver detalles" data-item-data='${itemDataString}'>
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
                }

                createSubItem(subItem) {
                    const hasRejection = subItem.rejection_reason;
                    const itemClass = hasRejection ? 'activity-item rejected' : 'activity-item';
                    const conceptContent = subItem.concept ? `<span class="item-concept">${subItem.concept}</span>` : '<span class="item-no-concept">Sin Concepto</span>';

                    let showType = subItem.type && subItem.type !== 'Actividad' && subItem.type !== subItem.concept;
                    const amountClass = `item-amount-separate`;

                    return `
                <div class="${itemClass}">
                    <div class="item-header">
                        ${conceptContent}
                    </div>
                    <div class="item-body">
                        ${showType ? `<div class="item-type">${subItem.type}</div>` : ''}
                        ${subItem.details ? `<div class="item-details">${subItem.details}</div>` : ''}
                        ${subItem.comments ? `<div class="item-comment">${subItem.comments}</div>` : ''}
                        <div class="item-id-amount">
                            ${subItem.id ? `<div class="item-id">ID: ${subItem.id}</div>` : ''}
                            ${subItem.amount ? `<div class="${amountClass}">${this.formatCurrency(subItem.amount, subItem.currency)}</div>` : ''}
                        </div>
                        ${hasRejection ? `
                                        <div class="rejection-reason">
                                            <i class="fas fa-exclamation-circle"></i>
                                            ${subItem.rejection_reason}
                                        </div>
                                    ` : ''}
                    </div>
                </div>
            `;
                }

                // ⭐ AHORA ACEPTA MONEDA DINÁMICA (USD, MXN, ETC)
                formatCurrency(amount, currency = 'MXN') {
                    const formatted = new Intl.NumberFormat('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(amount);
                    return `$${formatted} ${currency}`;
                }

                getStatusText(status) {
                    const statusMap = {
                        'approved': 'Aprobado',
                        'reviewed': 'Revisado',
                        'rejected': 'Rechazado',
                        'under_review': 'En Revisión'
                    };
                    return statusMap[status] || status;
                }

                getActivityTypeText(type) {
                    const types = {
                        'B': 'Trabajo en Base',
                        'P': 'Trabajo en Pozo',
                        'C': 'Comisionado',
                        'TC': 'Trabajo en Casa',
                        'V': 'Viaje',
                        'D': 'Descanso',
                        'VAC': 'Vacaciones',
                        'E': 'Entrenamiento',
                        'M': 'Médico',
                        'A': 'Ausencia',
                        'PE': 'Permiso'
                    };
                    return types[type] || type;
                }

                bindRowEvents() {
                    document.querySelectorAll('.btn-view-details').forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            const itemDataString = e.currentTarget.getAttribute('data-item-data').replace(/&#34;/g, '"');
                            const itemData = JSON.parse(itemDataString);
                            this.showDetailsModal(itemData);
                        });
                    });
                }

                showDetailsModal(item) {
                    // ⭐ LÓGICA PARA RENDERIZAR BADGES Y DESC EN EL MODAL SI ES GUARDIA
                    let typeBadges = `<span class="activity-type-badge ${item.activity_type}" title="${this.getActivityTypeText(item.activity_type)}">${item.activity_type}</span>`;
                    let descModal = `<span>${item.activity_description}</span>`;

                    if(item.activity_type_vespertina && item.activity_type_vespertina !== 'N') {
                        typeBadges += ` <span class="activity-type-badge ${item.activity_type_vespertina}" title="${this.getActivityTypeText(item.activity_type_vespertina)}">${item.activity_type_vespertina}</span>`;
                        descModal = `<div style="display:flex; flex-direction:column; gap:2px;">
                                        <span><strong>Mat:</strong> ${item.activity_description}</span>
                                        <span><strong>Vesp:</strong> ${item.activity_description_vespertina}</span>
                                    </div>`;
                    }

                    const modalHTML = `
            <div class="detail-header">
                <h4>Actividad del ${item.date}</h4>
                <span class="status-badge status-${item.overall_status}">
                    ${this.getStatusText(item.overall_status)}
                </span>
            </div>

            <div class="detail-section">
                <h5>Información Principal</h5>
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Día:</label>
                        <span><strong>${item.day_name}</strong> - ${item.date}</span>
                    </div>
                    <div class="detail-item">
                        <label>Tipo:</label>
                        <span class="fonttype">${typeBadges}</span>
                    </div>
                    <div class="detail-item">
                        <label>Actividad:</label>
                        ${descModal}
                    </div>
                    ${item.well_name ? `
                                            <div class="detail-item">
                                                <label>Pozo:</label>
                                                <span>${item.well_name}</span>
                                            </div>
                                            ` : ''}
                    ${item.travel_destination ? `
                                            <div class="detail-item">
                                                <label>Destino de Viaje:</label>
                                                <span>${item.travel_destination}</span>
                                            </div>
                                            ` : ''}
                    ${item.travel_reason ? `
                                            <div class="detail-item">
                                                <label>Motivo de Viaje:</label>
                                                <span>${item.travel_reason}</span>
                                            </div>
                                            ` : ''}
                    ${item.commissioned_to ? `
                                            <div class="detail-item">
                                                <label>Comisionado a:</label>
                                                <span>${item.commissioned_to}</span>
                                            </div>
                                            ` : ''}
                    ${item.total_amount ? `
                                            <div class="detail-item">
                                                <label>Monto Total:</label>
                                                <span><strong>${this.formatCurrency(item.total_amount)}</strong></span>
                                            </div>
                                            ` : ''}
                </div>
            </div>

            <div class="detail-section">
                <h5>Detalles del Día</h5>
                <div class="items-list">
                    ${item.daily_items.map(subItem => {
                        const concept = subItem.concept;
                        const conceptContent = concept ? `<strong>${concept}</strong>` : '<strong>Sin Concepto</strong>';
                        const amountClass = `item-amount`;
                        let showType = subItem.type && subItem.type !== 'Actividad' && subItem.type !== concept;

                        return `
                                                <div class="detail-item-card ${subItem.rejection_reason ? 'rejected' : ''}">
                                                    <div class="item-header">
                                                        ${conceptContent}
                                                        ${subItem.amount ? `<span class="${amountClass}">${this.formatCurrency(subItem.amount, subItem.currency)}</span>` : ''}
                                                    </div>
                                                    <div class="item-body">
                                                        ${subItem.id ? `<div><strong>ID:</strong> ${subItem.id}</div>` : ''}
                                                        ${showType ? `<div><strong>Categoría:</strong> ${subItem.type}</div>` : ''}

                                                        ${subItem.details ? `<div><strong>Detalles:</strong> ${subItem.details}</div>` : ''}

                                                        ${subItem.comments ? `<div><strong>Comentarios:</strong> ${subItem.comments}</div>` : ''}
                                                        ${subItem.rejection_reason ? `
                                        <div class="rejection-note">
                                            <strong>Motivo de rechazo:</strong> ${subItem.rejection_reason}
                                        </div>
                                    ` : ''}
                                                    </div>
                                                </div>
                                            `;
                    }).join('')}
                </div>
            </div>
        `;

                    detailModalInstance.setContent(modalHTML);
                    detailModalInstance.show();
                }

                updateStats(history) {
                    const counts = {
                        approved: history.filter(item => item.overall_status === 'approved').length,
                        reviewed: history.filter(item => item.overall_status === 'reviewed').length,
                        rejected: history.filter(item => item.overall_status === 'rejected').length,
                        under_review: history.filter(item => item.overall_status === 'under_review').length
                    };

                    document.getElementById('approved-count').textContent = counts.approved;
                    document.getElementById('reviewed-count').textContent = counts.reviewed;
                    document.getElementById('rejected-count').textContent = counts.rejected;
                    document.getElementById('under-review-count').textContent = counts.under_review;
                }

                updateSummary(total) {
                    const summary = document.getElementById('quick-summary');
                    const summaryText = document.getElementById('summary-text');

                    if (total === 0) {
                        summary.style.display = 'none';
                        return;
                    }

                    let filterText = [];
                    if (this.filters.start_date && this.filters.end_date) {
                        filterText.push(
                            `desde ${this.formatDisplayDate(this.filters.start_date)} hasta ${this.formatDisplayDate(this.filters.end_date)}`
                        );
                    }
                    if (this.filters.status !== 'all') {
                        filterText.push(`estado: ${this.getStatusText(this.filters.status)}`);
                    }
                    if (this.filters.activity_type !== 'all') {
                        filterText.push(`tipo: ${this.getActivityTypeText(this.filters.activity_type)}`);
                    }

                    const employeeText = this.employeeName ? ` de ${this.employeeName}` : '';
                    const filterString = filterText.length > 0 ? ` (filtrado por ${filterText.join(', ')})` : '';
                    summaryText.textContent = `Mostrando ${total} registro(s)${employeeText}${filterString}`;
                    summary.style.display = 'block';
                }

                formatDisplayDate(dateString) {
                    const date = new Date(dateString + 'T00:00:00');
                    if (isNaN(date)) {
                        return dateString;
                    }
                    return date.toLocaleDateString('es-ES', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                }

                applyFilters() {
                    this.filters = {
                        start_date: document.getElementById('start-date').value,
                        end_date: document.getElementById('end-date').value,
                        status: document.getElementById('status-filter').value,
                        activity_type: document.getElementById('activity-type-filter').value
                    };

                    this.currentPage = 1;
                    this.loadHistory();
                }

                clearFilters() {
                    document.getElementById('status-filter').value = 'all';
                    document.getElementById('activity-type-filter').value = 'all';

                    this.setupDateDefaults();

                    this.filters = {
                        start_date: document.getElementById('start-date').value,
                        end_date: document.getElementById('end-date').value,
                        status: 'all',
                        activity_type: 'all'
                    };

                    this.currentPage = 1;
                    this.loadHistory();
                }

                onDateChange() {
                    const startDate = document.getElementById('start-date').value;
                    const endDate = document.getElementById('end-date').value;

                    if (startDate && endDate && startDate > endDate) {
                        this.showError('La fecha de inicio no puede ser posterior a la fecha fin.');
                    }
                }

                onFilterChange() {
                }

                showLoading() {
                    const tbody = document.getElementById('history-table-body');
                    tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center loading-row">
                    <div class="loading-spinner"></div>
                    <p>Cargando historial...</p>
                </td>
            </tr>
        `;
                    document.getElementById('empty-state').style.display = 'none';
                }

                showEmptyState() {
                    document.getElementById('history-table-body').innerHTML = '';
                    document.getElementById('empty-state').style.display = 'block';
                }

                showError(message) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message,
                            confirmButtonText: 'Aceptar'
                        });
                    } else {
                        alert(message);
                    }
                }
            }

            let historyManagerInstance;

            document.addEventListener('DOMContentLoaded', function() {
                historyManagerInstance = new HistoryManager();
                window.historyManager = historyManagerInstance;
            });
        </script>
    @endpush
@endsection
