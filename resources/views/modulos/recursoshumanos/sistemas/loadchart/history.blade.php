@extends('modulos.recursoshumanos.sistemas.loadchart.index')

@section('content')

<style>
    /* Estilos para la tabla de historial */
    .history-container {
        padding: 20px;
        font-family: 'Poppins', sans-serif;
    }

    .history-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .history-header h2 {
        font-size: 24px;
        font-weight: 600;
        margin: 0;
        color: #34495e;
    }

    .history-navigation {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .history-navigation .nav-btn {
        background-color: #f1f1f1;
        border: 1px solid #ccc;
        padding: 8px 12px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .history-navigation .nav-btn:hover {
        background-color: #e2e2e2;
    }

    .current-period-info {
        font-size: 18px;
        font-weight: 500;
        min-width: 150px;
        text-align: center;
    }

    .back-btn {
        background-color: #3498db;
        color: #fff;
        border: none;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .back-btn:hover {
        background-color: #2980b9;
    }

    .table-container {
        overflow-x: auto;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .history-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 700px;
    }

    .history-table th, .history-table td {
        padding: 12px 15px;
        text-align: left;
        border: 1px solid #ddd;
    }

    .history-table thead {
        background-color: #f8f9fa;
        color: #333;
    }

    .history-table tbody tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    .history-table tbody tr:hover {
        background-color: #e9ecef;
    }

    .status-cell .status-badge {
        padding: 5px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        color: white;
    }

    .status-cell.status-approved .status-badge { background-color: var(--approved); }
    .status-cell.status-reviewed .status-badge { background-color: var(--reviewed); }
    .status-cell.status-under_review .status-badge { background-color: var(--under-review); }
    .status-cell.status-rejected .status-badge { background-color: var(--not-approved); }
    .status-cell.status-no_data .status-badge { background-color: #95a5a6; }

    .btn-detail {
        background-color: #2c3e50;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.3s ease;
    }

    .btn-detail:hover {
        background-color: #34495e;
    }

    /* Estilos del modal de detalle */
    .modal-read-only {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.4);
        align-items: center;
        justify-content: center;
    }

    .modal-read-only-content {
        background-color: #fefefe;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        width: 90%;
        max-width: 800px;
    }

    .modal-read-only-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #ddd;
        padding-bottom: 15px;
        margin-bottom: 15px;
    }

    .modal-read-only-header h4 {
        margin: 0;
        font-size: 20px;
    }

    .modal-read-only-close {
        font-size: 24px;
        cursor: pointer;
        border: none;
        background: transparent;
        color: #aaa;
    }

    .modal-read-only-close:hover {
        color: #333;
    }

    .detail-table {
        width: 100%;
        border-collapse: collapse;
    }

    .detail-table th, .detail-table td {
        padding: 10px;
        border: 1px solid #eee;
    }

    .modal-read-only-footer {
        text-align: right;
        margin-top: 20px;
    }

    /* Bárra de desplazamiento para la tabla */
    .table-container::-webkit-scrollbar {
        height: 8px;
    }

    .table-container::-webkit-scrollbar-thumb {
        background: #bdc3c7;
        border-radius: 10px;
    }
    .table-container::-webkit-scrollbar-track {
        background: #ecf0f1;
    }
</style>

<div id="historyView">
    <div class="history-container">
        <div class="history-header">
            <h2><i class="fas fa-history"></i> Historial de Actividades</h2>
            <div class="history-navigation">
                <button class="nav-btn" id="prev-month"><i class="fas fa-chevron-left"></i></button>
                <span id="current-period">
                </span>
                <button class="nav-btn" id="next-month"><i class="fas fa-chevron-right"></i></button>
            </div>
                <button class="back-btn" id="back-to-approval">
                    <i class="fas fa-arrow-left"></i> Volver a Aprobación
                </button>
                <button class="back-btn" id="back-to-calendar">
                    <i class="fas fa-arrow-left"></i> Volver a mi Loadchart
                </button>
        </div>

        <div class="table-container">
            <table class="history-table" id="history-table">
                <thead>
                    <tr id="table-headers">
                            <th>Empleado</th>
                        <th>Mes</th>
                        <th>Estado</th>
                        <th>Revisado</th>
                        <th>Aprobado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="history-table-body">
                    {{-- El contenido se carga con JavaScript --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-read-only" id="detailModal">
    <div class="modal-read-only-content">
        <div class="modal-read-only-header">
            <div>
                <h4 class="modal-read-only-title">Detalles del Mes</h4>
                <p class="modal-read-only-subtitle" id="modal-subtitle"></p>
            </div>
            <button class="modal-read-only-close"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-read-only-body">
            <table class="detail-table">
                <thead>
                    <tr>
                        <th>Día</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody id="detail-table-body">
                    {{-- Contenido del detalle del día se carga con JS --}}
                </tbody>
            </table>
        </div>
        <div class="modal-read-only-footer">
            <button class="btn btn-secondary modal-read-only-close-btn">Cerrar</button>
        </div>
    </div>
</div>


@endsection
