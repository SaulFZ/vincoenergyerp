@extends('modules.qhse.management.index')

@push('css')
<style>
    /* ====================================================================
       VARIABLES Y TEMA DEL DASHBOARD
       ==================================================================== */
    :root {
        --bg-dashboard: #f8fafc;
        --card-bg: #ffffff;
        --text-main: #0f172a;
        --text-muted: #64748b;
        --border-color: #e2e8f0;

        /* Colores Base */
        --brand-blue: #0284c7;

        /* Gradientes para KPIs */
        --grad-blue: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        --grad-green: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --grad-orange: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --grad-red: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .stats-container {
        font-family: 'Poppins', sans-serif;
        padding: 15px 0;
    }

    /* ====================================================================
       KPI CARDS (DISEÑO COMPACTO)
       ==================================================================== */
    .kpi-wrapper {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .kpi-card {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        border: 1px solid var(--border-color);
        position: relative;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.05);
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 3px;
    }
    .kpi-card.c-blue::before { background: var(--grad-blue); }
    .kpi-card.c-green::before { background: var(--grad-green); }
    .kpi-card.c-orange::before { background: var(--grad-orange); }
    .kpi-card.c-red::before { background: var(--grad-red); }

    .kpi-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .kpi-title {
        color: var(--text-muted);
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        margin: 0;
        line-height: 1.2;
    }

    .kpi-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: white;
        flex-shrink: 0;
    }

    .c-blue .kpi-icon-box { background: var(--grad-blue); box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3); }
    .c-green .kpi-icon-box { background: var(--grad-green); box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3); }
    .c-orange .kpi-icon-box { background: var(--grad-orange); box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3); }
    .c-red .kpi-icon-box { background: var(--grad-red); box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3); }

    .kpi-body {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
    }

    .kpi-value {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1;
        margin: 0;
    }

    .kpi-trend {
        font-size: 0.75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
        background: #f1f5f9;
        padding: 3px 6px;
        border-radius: 4px;
    }

    .trend-up { color: #059669; background: #ecfdf5; }
    .trend-down { color: #dc2626; background: #fef2f2; }
    .trend-warning { color: #d97706; background: #fffbeb; }

    /* ====================================================================
       CONTROLES Y FILTROS
       ==================================================================== */
    .dashboard-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding: 0 5px;
    }

    .dashboard-controls h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .filter-btn-group {
        display: flex;
        background: #e2e8f0;
        border-radius: 8px;
        padding: 3px;
    }

    .filter-btn {
        background: transparent;
        border: none;
        padding: 6px 14px;
        border-radius: 6px;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 0.8rem;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .filter-btn.active {
        background: var(--card-bg);
        color: var(--brand-blue);
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    /* ====================================================================
       SECCIÓN DE GRÁFICAS COMPACTA
       ==================================================================== */
    .charts-wrapper {
        display: grid;
        grid-template-columns: repeat(3, 1fr); /* 3 columnas para PC */
        gap: 15px;
    }

    .chart-box {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        border: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 1px dashed var(--border-color);
    }

    .chart-header h4 {
        margin: 0;
        color: var(--text-main);
        font-size: 0.9rem;
        font-weight: 700;
    }

    .canvas-container {
        position: relative;
        height: 220px; /* Altura compacta para evitar mucho scroll */
        width: 100%;
    }

    /* Responsividad */
    @media (max-width: 1024px) {
        .charts-wrapper { grid-template-columns: 1fr 1fr; }
        .chart-box:last-child { grid-column: span 2; }
    }
    @media (max-width: 768px) {
        .charts-wrapper { grid-template-columns: 1fr; }
        .chart-box:last-child { grid-column: span 1; }
        .dashboard-controls { flex-direction: column; align-items: flex-start; gap: 10px; }
    }
</style>
@endpush

@section('content')
<div class="stats-container">

    <div class="kpi-wrapper">
        <div class="kpi-card c-blue">
            <div class="kpi-header">
                <p class="kpi-title">Viajes Realizados</p>
                <div class="kpi-icon-box"><i class="fas fa-route"></i></div>
            </div>
            <div class="kpi-body">
                <p class="kpi-value" id="kpiTotal">142</p>
                <div class="kpi-trend trend-up"><i class="fas fa-arrow-up"></i> 12%</div>
            </div>
        </div>

        <div class="kpi-card c-green">
            <div class="kpi-header">
                <p class="kpi-title">Unidades en Ruta</p>
                <div class="kpi-icon-box"><i class="fas fa-truck-fast"></i></div>
            </div>
            <div class="kpi-body">
                <p class="kpi-value" id="kpiActivos">12</p>
                <div class="kpi-trend trend-up"><i class="fas fa-check"></i> Activos</div>
            </div>
        </div>

        <div class="kpi-card c-orange">
            <div class="kpi-header">
                <p class="kpi-title">Llegadas a Tiempo</p>
                <div class="kpi-icon-box"><i class="fas fa-stopwatch"></i></div>
            </div>
            <div class="kpi-body">
                <p class="kpi-value" id="kpiEficiencia">92<span style="font-size: 1rem;">%</span></p>
                <div class="kpi-trend trend-warning"><i class="fas fa-bullseye"></i> Óptimo</div>
            </div>
        </div>

        <div class="kpi-card c-red">
            <div class="kpi-header">
                <p class="kpi-title">Unidades Detenidas</p>
                <div class="kpi-icon-box"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
            <div class="kpi-body">
                <p class="kpi-value" id="kpiDetenidos">3</p>
                <div class="kpi-trend trend-down"><i class="fas fa-bell"></i> Requiere Acción</div>
            </div>
        </div>
    </div>

    <div class="dashboard-controls">
        <h3>Métricas Operativas</h3>
        <div class="filter-btn-group">
            <button class="filter-btn active" onclick="cambiarFiltro(this, 'mes')">Este Mes</button>
            <button class="filter-btn" onclick="cambiarFiltro(this, 'trimestre')">Trimestre</button>
            <button class="filter-btn" onclick="cambiarFiltro(this, 'ano')">Este Año</button>
        </div>
    </div>

    <div class="charts-wrapper">

        <div class="chart-box">
            <div class="chart-header">
                <h4>Estatus de Viajes</h4>
            </div>
            <div class="canvas-container">
                <canvas id="chartEstatus"></canvas>
            </div>
        </div>

        <div class="chart-box">
            <div class="chart-header">
                <h4>Rendimiento de Tiempos</h4>
            </div>
            <div class="canvas-container">
                <canvas id="chartTiempos"></canvas>
            </div>
        </div>

        <div class="chart-box">
            <div class="chart-header">
                <h4>Riesgo Ponderado</h4>
            </div>
            <div class="canvas-container">
                <canvas id="chartRiesgo"></canvas>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Variables globales
    let myChartEstatus, myChartTiempos, myChartRiesgo;

    // Configuración base
    Chart.defaults.font.family = "'Poppins', sans-serif";
    Chart.defaults.color = '#64748b';
    Chart.defaults.scale.grid.color = '#f1f5f9';
    Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 23, 42, 0.9)';
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 6;

    document.addEventListener("DOMContentLoaded", function () {
        initCharts();
    });

    function initCharts() {
        // -------------------------------------------------------------
        // 1. DOUGHNUT CHART (Estatus Operativo)
        // -------------------------------------------------------------
        const ctxEstatus = document.getElementById('chartEstatus').getContext('2d');
        myChartEstatus = new Chart(ctxEstatus, {
            type: 'doughnut',
            data: {
                labels: ['En Curso', 'Detenidos', 'Finalizados', 'Por Iniciar'],
                datasets: [{
                    data: [15, 3, 110, 8],
                    backgroundColor: [
                        '#3b82f6', // Azul (En curso)
                        '#ef4444', // Rojo (Detenido)
                        '#10b981', // Verde (Finalizado)
                        '#94a3b8'  // Gris (Por iniciar)
                    ],
                    borderWidth: 0,
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { usePointStyle: true, boxWidth: 6, font: { size: 11 } }
                    }
                }
            }
        });

        // -------------------------------------------------------------
        // 2. BAR CHART (Rendimiento de Tiempos) - REEMPLAZA A DOCUMENTOS
        // -------------------------------------------------------------
        const ctxTiempos = document.getElementById('chartTiempos').getContext('2d');
        myChartTiempos = new Chart(ctxTiempos, {
            type: 'bar',
            data: {
                labels: ['Anticipado', 'A Tiempo', 'Con Retraso'],
                datasets: [{
                    label: 'Viajes',
                    data: [35, 80, 15],
                    backgroundColor: [
                        'rgba(5, 150, 105, 0.8)', // Verde esmeralda
                        'rgba(37, 99, 235, 0.8)', // Azul
                        'rgba(220, 38, 38, 0.8)'  // Rojo
                    ],
                    borderRadius: 4,
                    barThickness: 25
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { display: false, drawBorder: false }, ticks: { font: { size: 10 } } },
                    y: { grid: { borderDash: [3, 3] }, beginAtZero: true, ticks: { font: { size: 10 } } }
                }
            }
        });

        // -------------------------------------------------------------
        // 3. HORIZONTAL BAR CHART (Nivel de Riesgo)
        // -------------------------------------------------------------
        const ctxRiesgo = document.getElementById('chartRiesgo').getContext('2d');
        myChartRiesgo = new Chart(ctxRiesgo, {
            type: 'bar',
            data: {
                labels: ['Bajo', 'Medio', 'Alto', 'Crítico'],
                datasets: [{
                    label: 'Solicitudes',
                    data: [90, 40, 15, 5],
                    backgroundColor: [
                        '#10b981', // Verde
                        '#f59e0b', // Amarillo
                        '#f97316', // Naranja
                        '#dc2626'  // Rojo
                    ],
                    borderRadius: 4,
                    barThickness: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { borderDash: [3, 3] }, ticks: { font: { size: 10 } } },
                    y: { grid: { display: false, drawBorder: false }, ticks: { font: { size: 10 } } }
                }
            }
        });
    }

    // -------------------------------------------------------------
    // SIMULACIÓN DE ACTUALIZACIÓN CON LOS FILTROS
    // -------------------------------------------------------------
    function cambiarFiltro(btnElement, periodo) {
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        btnElement.classList.add('active');

        if(typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Calculando...',
                allowOutsideClick: false,
                showConfirmButton: false,
                width: 250,
                didOpen: () => { Swal.showLoading(); }
            });
        }

        setTimeout(() => {
            // Actualizar KPIs con datos simulados
            document.getElementById('kpiTotal').innerText = Math.floor(Math.random() * 200) + 50;
            document.getElementById('kpiActivos').innerText = Math.floor(Math.random() * 30);
            document.getElementById('kpiEficiencia').innerHTML = (Math.floor(Math.random() * 15) + 80) + '<span style="font-size: 1rem;">%</span>';
            document.getElementById('kpiDetenidos').innerText = Math.floor(Math.random() * 5);

            // Actualizar Chart Estatus
            myChartEstatus.data.datasets[0].data = [
                Math.floor(Math.random() * 40), Math.floor(Math.random() * 5),
                Math.floor(Math.random() * 150), Math.floor(Math.random() * 20)
            ];
            myChartEstatus.update();

            // Actualizar Chart Tiempos
            myChartTiempos.data.datasets[0].data = [
                Math.floor(Math.random() * 50), Math.floor(Math.random() * 100), Math.floor(Math.random() * 20)
            ];
            myChartTiempos.update();

            // Actualizar Chart Riesgos
            myChartRiesgo.data.datasets[0].data = [
                Math.floor(Math.random() * 120), Math.floor(Math.random() * 50),
                Math.floor(Math.random() * 20), Math.floor(Math.random() * 10)
            ];
            myChartRiesgo.update();

            if(typeof Swal !== 'undefined') Swal.close();
        }, 500);
    }
</script>
@endpush
