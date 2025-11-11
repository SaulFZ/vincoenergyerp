@extends('modulos.recursoshumanos.sistemas.loadchart.index')
<STYle>
/* Variables CSS actualizadas */
:root {
    /* Colores Primarios y de Estado */
    --primary-color: #334c95;
    --secondary-color: #283848;
    --accent-color: #d67e29;
    --white: #ffffff;

    --success-color: #64946f;
    --warning-color: #da8544;
    --danger-color: #f35900;
    --info-color: #ffd900;

    /* Colores de Fondo y Texto */
    --light-color: #f7fafc;
    --dark-color: #2d3748;
    --medium-text: #4a5568;
    --border-color: #e2e8f0;

    --shadow: 0 4px 12px rgba(0,0,0,0.08);
}

/* Contenedor Principal */
.history-container {
    padding: 25px;
    background: var(--light-color);
    min-height: 100vh;
}

/* Tarjeta Base */
.card-base {
    background: var(--white);
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: var(--shadow);
    overflow: hidden;
    border: 1px solid var(--border-color);
}

/* Header */
.history-header {
    padding: 25px 30px;
    margin-bottom: 0;
    border-bottom: 1px solid var(--border-color);
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.title-section .history-title {
    color: var(--secondary-color);
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
}

.title-section .history-subtitle {
    color: var(--medium-text);
    margin: 5px 0 0 0;
    font-size: 1rem;
}

/* Estadísticas */
.header-stats {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.stat-card {
    display: flex;
    align-items: center;
    background: var(--white);
    padding: 15px 20px;
    border-radius: 10px;
    border: 1px solid var(--border-color);
    min-width: 160px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 1.4rem;
}

.stat-icon.approved {
    background: rgba(100, 148, 111, 0.15);
    color: var(--success-color);
}

.stat-icon.pending {
    background: rgba(255, 217, 0, 0.15);
    color: var(--info-color);
}

.stat-icon.rejected {
    background: rgba(243, 89, 0, 0.15);
    color: var(--danger-color);
}

.stat-number {
    display: block;
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--dark-color);
}

.stat-label {
    font-size: 0.9rem;
    color: var(--medium-text);
    text-transform: uppercase;
}

/* Panel de Filtros */
.filters-panel {
    margin-bottom: 25px;
}

.filters-header {
    padding: 20px 30px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.filters-title {
    margin: 0;
    font-size: 1.4rem;
    color: var(--secondary-color);
    font-weight: 600;
}

.btn-clear-filters {
    background: var(--medium-text);
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    font-size: 0.95rem;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s;
    font-weight: 500;
}

.btn-clear-filters:hover {
    background: var(--dark-color);
    transform: translateY(-1px);
}

.filters-content {
    padding: 30px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 25px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-weight: 600;
    margin-bottom: 10px;
    color: var(--medium-text);
}

.date-range-inputs {
    display: flex;
    align-items: center;
    gap: 10px;
}

.date-separator {
    color: var(--medium-text);
    font-weight: 600;
}

.form-control {
    padding: 10px 15px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s, box-shadow 0.3s;
    background-color: var(--white);
    color: var(--dark-color);
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(51, 76, 149, 0.2);
}

.btn-apply-filters {
    background: var(--accent-color);
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s;
    height: fit-content;
    white-space: nowrap;
}

.btn-apply-filters:hover {
    background: #c07225;
    transform: translateY(-1px);
}

/* Contenido Principal */
.history-content {
    margin-bottom: 25px;
}

.quick-summary {
    padding: 15px 30px;
    background: #ebf8ff;
    border-bottom: 1px solid var(--border-color);
}

.summary-content {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--primary-color);
    font-weight: 600;
}

/* Tabla Mejorada */
.table-container {
    padding: 0;
}

.table-responsive {
    overflow-x: auto;
}

.history-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
    font-size: 0.85rem;
}

.history-table th {
    background: var(--light-color);
    padding: 15px 10px;
    font-weight: 700;
    color: var(--secondary-color);
    border-bottom: 2px solid var(--border-color);
    text-align: left;
    text-transform: uppercase;
    font-size: 0.8rem;
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 10;
}

.history-table td {
    padding: 12px 10px;
    border-bottom: 1px solid var(--border-color);
    vertical-align: top;
    color: var(--dark-color);
}

.history-table tbody tr {
    transition: background 0.3s;
}

.history-table tbody tr:hover {
    background: var(--light-color);
}

/* Estados de la tabla */
.status-badge {
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    display: inline-block;
    min-width: 80px;
    text-align: center;
}

.status-approved {
    background: rgba(100, 148, 111, 0.15);
    color: var(--success-color);
    border: 1px solid var(--success-color);
}

.status-reviewed {
    background: rgba(218, 133, 68, 0.15);
    color: var(--warning-color);
    border: 1px solid var(--warning-color);
}

.status-rejected {
    background: rgba(243, 89, 0, 0.15);
    color: var(--danger-color);
    border: 1px solid var(--danger-color);
}

.status-under_review {
    background: rgba(255, 217, 0, 0.15);
    color: var(--info-color);
    border: 1px solid var(--info-color);
}

/* Celdas específicas */
.date-cell {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.date-cell strong {
    font-size: 0.9rem;
    color: var(--dark-color);
}

.date-cell .text-muted {
    font-size: 0.75rem;
}

.activity-type-cell {
    text-align: center;
}

.activity-type-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    background: var(--light-color);
    border: 1px solid var(--border-color);
    color: var(--dark-color);
}

.activity-type-badge.P {
    background: rgba(51, 76, 149, 0.1);
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.activity-type-badge.B {
    background: rgba(100, 117, 170, 0.1);
    border-color: #6475aa;
    color: #6475aa;
}

.activity-type-badge.C {
    background: rgba(36, 147, 153, 0.1);
    border-color: #249399;
    color: #249399;
}

.amount-cell {
    text-align: right;
    font-weight: 600;
    color: var(--dark-color);
}

.amount-cell .currency {
    font-size: 0.75rem;
    color: var(--medium-text);
    margin-left: 2px;
}

/* Actividad principal */
.activity-main {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.activity-main strong {
    font-size: 0.9rem;
    color: var(--secondary-color);
}

.activity-details {
    font-size: 0.8rem;
    color: var(--medium-text);
    line-height: 1.3;
}

/* Items de actividad */
.activity-items {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.activity-item {
    padding: 8px;
    background: var(--light-color);
    border-radius: 6px;
    border-left: 3px solid var(--primary-color);
    font-size: 0.8rem;
}

.activity-item.rejected {
    border-left-color: var(--danger-color);
    background: rgba(243, 89, 0, 0.05);
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
    font-weight: 600;
    color: var(--dark-color);
    font-size: 0.8rem;
    white-space: nowrap;
}

.item-body {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.item-type {
    color: var(--medium-text);
    font-size: 0.75rem;
}

.item-details {
    color: var(--medium-text);
    font-size: 0.75rem;
    line-height: 1.3;
}

.item-comment {
    background: rgba(255, 255, 255, 0.7);
    padding: 4px 6px;
    border-radius: 4px;
    border-left: 2px solid var(--accent-color);
    margin-top: 3px;
    font-size: 0.75rem;
    color: var(--dark-color);
    font-style: italic;
}

.rejection-reason {
    color: var(--danger-color);
    font-size: 0.75rem;
    font-style: italic;
    margin-top: 3px;
    padding: 3px 5px;
    background: rgba(243, 89, 0, 0.1);
    border-radius: 3px;
}

/* Botones de acción */
.btn-action {
    background: var(--white);
    border: 1px solid var(--border-color);
    padding: 6px 8px;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s;
    color: var(--medium-text);
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
}

.btn-action:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
    transform: translateY(-1px);
}

/* Estados de carga y vacío */
.loading-row {
    padding: 60px 20px;
    text-align: center;
}

.loading-spinner {
    width: 35px;
    height: 35px;
    border: 4px solid var(--border-color);
    border-top: 4px solid var(--accent-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 15px;
}

.empty-state {
    text-align: center;
    padding: 80px 20px;
}

.empty-icon {
    font-size: 5rem;
    color: var(--border-color);
    margin-bottom: 20px;
}

.empty-state h3 {
    color: var(--medium-text);
    margin-bottom: 10px;
    font-size: 1.5rem;
}

/* Estilos para el Modal (necesarios para que Bootstrap lo muestre) */
.modal-content {
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.modal-header {
    border-bottom: 1px solid var(--border-color);
    padding: 20px 25px;
}

.modal-title {
    color: var(--primary-color);
    font-weight: 700;
}

.modal-footer {
    border-top: 1px solid var(--border-color);
    padding: 15px 25px;
}

/* Estilos de los detalles del modal */
.detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px dashed var(--border-color);
}

.detail-section {
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
}

.detail-section h5 {
    color: var(--secondary-color);
    margin-top: 0;
    margin-bottom: 15px;
    font-weight: 600;
    font-size: 1.1rem;
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.detail-item {
    display: flex;
    flex-direction: column;
}

.detail-item label {
    font-weight: 600;
    color: var(--medium-text);
    font-size: 0.85rem;
    margin-bottom: 3px;
}

.detail-item span {
    color: var(--dark-color);
    font-size: 0.95rem;
}

.items-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.detail-item-card {
    padding: 10px 15px;
    border: 1px solid var(--border-color);
    border-left: 4px solid var(--primary-color);
    border-radius: 6px;
    background: var(--light-color);
}

.detail-item-card.rejected {
    border-left-color: var(--danger-color);
    background: rgba(243, 89, 0, 0.05);
}

.detail-item-card .item-header {
    padding: 0;
    border: none;
    margin-bottom: 5px;
}

.detail-item-card .item-header strong {
    color: var(--secondary-color);
    font-size: 1rem;
}

.detail-item-card .item-amount {
    color: var(--success-color);
}

.detail-item-card .item-body > div {
    font-size: 0.85rem;
    color: var(--medium-text);
}

.rejection-note {
    color: var(--danger-color);
    font-size: 0.85rem !important;
    font-style: italic;
    margin-top: 5px;
    padding: 5px;
    background: rgba(243, 89, 0, 0.1);
    border-radius: 4px;
}


/* Responsive */
@media (max-width: 1200px) {
    .history-table {
        font-size: 0.8rem;
    }

    .history-table th,
    .history-table td {
        padding: 10px 8px;
    }
}

@media (max-width: 768px) {
    .history-container {
        padding: 15px;
    }

    .filters-content {
        grid-template-columns: 1fr;
        padding: 20px;
    }

    .history-table {
        font-size: 0.75rem;
    }

    .activity-item {
        font-size: 0.75rem;
    }

    .detail-grid {
        grid-template-columns: 1fr;
    }
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</STYle>

@section('content')
<div class="history-container">
    <div class="history-header card-base">
        <div class="header-content">
            <div class="title-section">
                <h1 class="history-title">
                    <i class="fas fa-history"></i>
                    Historial de Actividades
                </h1>
                <p class="history-subtitle">
                    Consulta el registro completo de tus actividades y su estado de aprobación.
                </p>
            </div>
            <div class="header-stats">
                <div class="stat-card">
                    <div class="stat-icon approved">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number" id="approved-count">0</span>
                        <span class="stat-label">Aprobadas</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number" id="pending-count">0</span>
                        <span class="stat-label">Pendientes</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon rejected">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number" id="rejected-count">0</span>
                        <span class="stat-label">Rechazadas</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="filters-panel card-base">
        <div class="filters-header">
            <h3 class="filters-title">
                <i class="fas fa-filter"></i>
                Opciones de Filtro
            </h3>
            <button class="btn-clear-filters" id="clear-filters">
                <i class="fas fa-times"></i>
                Limpiar
            </button>
        </div>

        <div class="filters-content">
            <div class="filter-group">
                <label for="date-range">Rango de Fechas</label>
                <div class="date-range-inputs">
                    <input type="date" id="start-date" class="form-control" placeholder="Fecha inicio">
                    <span class="date-separator">a</span>
                    <input type="date" id="end-date" class="form-control" placeholder="Fecha fin">
                </div>
            </div>

            <div class="filter-group">
                <label for="status-filter">Estado</label>
                <select id="status-filter" class="form-control">
                    <option value="all">Todos los estados</option>
                    <option value="approved">Aprobado</option>
                    <option value="reviewed">Revisado</option>
                    <option value="under_review">Pendiente</option>
                    <option value="rejected">Rechazado</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="activity-type-filter">Tipo de Actividad</label>
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
                            <th width="90px">
                                <i class="fas fa-calendar"></i>
                                Fecha
                            </th>
                            <th width="70px">
                                <i class="fas fa-tag"></i>
                                Tipo
                            </th>
                            <th width="140px">
                                <i class="fas fa-tasks"></i>
                                Actividad
                            </th>
                                                        <th width="90px">
                                <i class="fas fa-info-circle"></i>
                                Estado
                            </th>
                            <th>
                                <i class="fas fa-list"></i>
                                Detalles
                            </th>
                            <th width="60px" class="text-center">
                                <i class="fas fa-cog"></i>
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
                <h3>No se encontraron registros</h3>
                <p>No hay actividades que coincidan con los filtros aplicados.</p>
                <button class="btn-clear-filters" onclick="clearFilters()">
                    <i class="fas fa-times"></i>
                    Limpiar filtros
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i>
                    Detalles de Actividad
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detail-modal-content">
                            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
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
        this.loadHistory();
        this.setupDateDefaults();
    }

    bindEvents() {
        document.getElementById('apply-filters').addEventListener('click', () => this.applyFilters());
        document.getElementById('clear-filters').addEventListener('click', () => this.clearFilters());

        document.getElementById('start-date').addEventListener('change', (e) => this.onDateChange());
        document.getElementById('end-date').addEventListener('change', (e) => this.onDateChange());

        document.getElementById('status-filter').addEventListener('change', (e) => this.onFilterChange());
        document.getElementById('activity-type-filter').addEventListener('change', (e) => this.onFilterChange());
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

            const response = await fetch(`/recursoshumanos/loadchart/history/data?${params}`);
            const data = await response.json();

            if (data.success) {
                this.employeeName = data.employee_name || '';
                this.renderHistory(data.history);
                this.updateStats(data.history);
                this.updateSummary(data.history.length);
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

        if (history.length === 0) {
            this.showEmptyState();
            return;
        }

        tbody.innerHTML = history.map(item => this.createHistoryRow(item)).join('');
        this.bindRowEvents();
    }

    createHistoryRow(item) {
        const hasRejections = item.has_rejections;
        const rowClass = hasRejections ? 'has-rejection' : '';

        return `
            <tr class="${rowClass}" data-item='${JSON.stringify(item).replace(/'/g, "&#39;")}'>
                <td>
                    <div class="date-cell">
                        <strong>${item.date}</strong>
                        <small class="text-muted">${item.day_name}</small>
                    </div>
                </td>
                <td class="activity-type-cell">
                    <span class="activity-type-badge ${item.activity_type}" title="${this.getActivityTypeText(item.activity_type)}">
                        ${item.activity_type}
                    </span>
                </td>
                <td>
                    <div class="activity-main">
                        <strong>${item.activity_description}</strong>
                        <div class="activity-details">
                            ${item.well_name ? `<div>Pozo: ${item.well_name}</div>` : ''}
                            ${item.travel_destination ? `<div>Destino: ${item.travel_destination}</div>` : ''}
                            ${item.commissioned_to ? `<div>Comisionado: ${item.commissioned_to}</div>` : ''}
                        </div>
                    </div>
                </td>
                                <td>
                    <span class="status-badge status-${item.overall_status}">
                        ${this.getStatusText(item.overall_status)}
                    </span>
                </td>
                <td>
                    <div class="activity-items">
                        ${item.daily_items.map(subItem => this.createSubItem(subItem)).join('')}
                    </div>
                </td>
                <td class="text-center">
                    <button class="btn-action btn-view-details" title="Ver detalles">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `;
    }

    createSubItem(subItem) {
        const hasRejection = subItem.rejection_reason;
        const itemClass = hasRejection ? 'activity-item rejected' : 'activity-item';
        // Reemplazar "Bono de campo" por "Bono"
        const concept = subItem.concept === 'Bono de campo' ? 'Bono' : subItem.concept;

        return `
            <div class="${itemClass}">
                <div class="item-header">
                    <span class="item-concept">${concept}</span>
                    ${subItem.amount ? `<span class="item-amount">${this.formatCurrency(subItem.amount)}</span>` : ''}
                </div>
                <div class="item-body">
                    <div class="item-type">${subItem.type}</div>
                    ${subItem.details ? `<div class="item-details">${subItem.details}</div>` : ''}
                    ${subItem.comments ? `<div class="item-comment">${subItem.comments}</div>` : ''}
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

    formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    getStatusText(status) {
        const statusMap = {
            'approved': 'Aprobado',
            'reviewed': 'Revisado',
            'rejected': 'Rechazado',
            'under_review': 'Pendiente'
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
                const row = e.target.closest('tr');
                const itemData = JSON.parse(row.getAttribute('data-item').replace(/&#39;/g, "'"));
                this.showDetailsModal(itemData);
            });
        });
    }

    showDetailsModal(item) {
        const modalContent = document.getElementById('detail-modal-content');

        modalContent.innerHTML = `
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
                        <label>Tipo:</label>
                        <span>${this.getActivityTypeText(item.activity_type)} (${item.activity_type})</span>
                    </div>
                    <div class="detail-item">
                        <label>Actividad:</label>
                        <span>${item.activity_description}</span>
                    </div>
                    ${item.well_name ? `
                    <div class="detail-item">
                        <label>Pozo:</label>
                        <span>${item.well_name}</span>
                    </div>
                    ` : ''}
                    ${item.travel_destination ? `
                    <div class="detail-item">
                        <label>Destino:</label>
                        <span>${item.travel_destination}</span>
                    </div>
                    ` : ''}
                    ${item.total_amount ? `
                    <div class="detail-item">
                        <label>Monto Total:</label>
                        <span><strong>${this.formatCurrency(item.total_amount)} USD</strong></span>
                    </div>
                    ` : ''}
                </div>
            </div>

            <div class="detail-section">
                <h5>Items del Día</h5>
                <div class="items-list">
                    ${item.daily_items.map(subItem => `
                        <div class="detail-item-card ${subItem.rejection_reason ? 'rejected' : ''}">
                            <div class="item-header">
                                <strong>${subItem.concept === 'Bono de campo' ? 'Bono' : subItem.concept}</strong>
                                ${subItem.amount ? `<span class="item-amount">${this.formatCurrency(subItem.amount)} USD</span>` : ''}
                            </div>
                            <div class="item-body">
                                <div><strong>Tipo:</strong> ${subItem.type}</div>
                                ${subItem.details ? `<div><strong>Detalles:</strong> ${subItem.details}</div>` : ''}
                                ${subItem.comments ? `<div><strong>Comentarios:</strong> ${subItem.comments}</div>` : ''}
                                ${subItem.rejection_reason ? `
                                    <div class="rejection-note">
                                        <strong>Motivo de rechazo:</strong> ${subItem.rejection_reason}
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;

        // Asegurarse de que Bootstrap esté cargado antes de intentar usar Modal
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modal = new bootstrap.Modal(document.getElementById('detailModal'));
            modal.show();
        } else {
            console.error('Bootstrap Modal no está disponible. Asegúrate de que los archivos JS de Bootstrap estén cargados.');
            alert('No se puede mostrar la ventana de detalles. Error de inicialización de la librería.');
        }
    }

    updateStats(history) {
        const counts = {
            approved: history.filter(item => item.overall_status === 'approved').length,
            pending: history.filter(item => item.overall_status === 'under_review').length,
            rejected: history.filter(item => item.overall_status === 'rejected').length
        };

        document.getElementById('approved-count').textContent = counts.approved;
        document.getElementById('pending-count').textContent = counts.pending;
        document.getElementById('rejected-count').textContent = counts.rejected;
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
            filterText.push(`desde ${this.formatDisplayDate(this.filters.start_date)} hasta ${this.formatDisplayDate(this.filters.end_date)}`);
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
        return new Date(dateString).toLocaleDateString('es-ES');
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
        document.getElementById('start-date').value = '';
        document.getElementById('end-date').value = '';
        document.getElementById('status-filter').value = 'all';
        document.getElementById('activity-type-filter').value = 'all';

        this.filters = {
            start_date: '',
            end_date: '',
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
            this.showError('La fecha de inicio no puede ser mayor a la fecha fin');
            document.getElementById('start-date').value = '';
        }
    }

    onFilterChange() {
        // Carga automática opcional
    }

    showLoading() {
        const tbody = document.getElementById('history-table-body');
        // Colspan ajustado de 7 a 6
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

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    new HistoryManager();
});

// Función global para el botón "Limpiar filtros" del estado vacío
function clearFilters() {
    const manager = new HistoryManager(); // Se asume que el manager es global o accesible
    manager.clearFilters();
}
</script>
@endpush
