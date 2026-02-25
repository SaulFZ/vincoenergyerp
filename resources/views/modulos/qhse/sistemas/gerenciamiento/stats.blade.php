@extends('modulos.qhse.sistemas.gerenciamiento.index')

@push('css')
<style>
    /* ====================================================================
       VARIABLES Y TEMA DEL DASHBOARD
       ==================================================================== */
    :root {
        --bg-dashboard: #f4f7fe;
        --card-bg: #ffffff;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --border-color: #e2e8f0;

        /* Colores Base */
        --brand-blue: #0056b3;
        --brand-blue-light: #eff6ff;

        /* Gradientes para KPIs */
        --grad-blue: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        --grad-green: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --grad-orange: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --grad-red: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .stats-container {
        font-family: 'Poppins', sans-serif;
        padding: 20px 0;
    }

    /* ====================================================================
       HEADER DEL DASHBOARD
       ==================================================================== */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--card-bg);
        padding: 20px 25px;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        margin-bottom: 25px;
        border: 1px solid var(--border-color);
    }

    .dashboard-header h2 {
        color: var(--text-main);
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .dashboard-header h2 i {
        color: var(--brand-blue);
        background: var(--brand-blue-light);
        padding: 10px;
        border-radius: 10px;
        font-size: 1.2rem;
    }

    .filter-btn-group {
        display: flex;
        background: var(--bg-dashboard);
        border-radius: 8px;
        padding: 4px;
        border: 1px solid var(--border-color);
    }

    .filter-btn {
        background: transparent;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-btn.active {
        background: var(--card-bg);
        color: var(--brand-blue);
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    /* ====================================================================
       KPI CARDS (BENTO GRID STYLE)
       ==================================================================== */
    .kpi-wrapper {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    .kpi-card {
        background: var(--card-bg);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
        border: 1px solid var(--border-color);
        position: relative;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.08);
    }

    /* Franja de color superior en la tarjeta */
    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 4px;
    }
    .kpi-card.c-blue::before { background: var(--grad-blue); }
    .kpi-card.c-green::before { background: var(--grad-green); }
    .kpi-card.c-red::before { background: var(--grad-red); }
    .kpi-card.c-orange::before { background: var(--grad-orange); }

    .kpi-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .kpi-title {
        color: var(--text-muted);
        font-size: 0.9rem;
        font-weight: 600;
        margin: 0;
    }

    .kpi-icon-box {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        color: white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }

    .c-blue .kpi-icon-box { background: var(--grad-blue); }
    .c-green .kpi-icon-box { background: var(--grad-green); }
    .c-red .kpi-icon-box { background: var(--grad-red); }
    .c-orange .kpi-icon-box { background: var(--grad-orange); }

    .kpi-value {
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1;
        margin-bottom: 8px;
    }

    .kpi-trend {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .trend-up { color: #10b981; }
    .trend-down { color: #ef4444; }
    .trend-neutral { color: var(--text-muted); }

    /* ====================================================================
       SECCIÓN DE GRÁFICAS
       ==================================================================== */
    .charts-wrapper {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .chart-box {
        background: var(--card-bg);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
        border: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
    }

    .chart-box.full-width {
        grid-column: span 2;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .chart-header h3 {
        margin: 0;
        color: var(--text-main);
        font-size: 1.1rem;
        font-weight: 700;
    }

    .chart-header .btn-export {
        background: none;
        border: 1px solid var(--border-color);
        color: var(--text-muted);
        padding: 5px 12px;
        border-radius: 6px;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .chart-header .btn-export:hover {
        background: var(--bg-dashboard);
        color: var(--text-main);
    }

    .canvas-container {
        position: relative;
        height: 320px;
        width: 100%;
    }

    @media (max-width: 1024px) {
        .charts-wrapper { grid-template-columns: 1fr; }
        .chart-box.full-width { grid-column: span 1; }
    }
</style>
@endpush

@section('content')
<div class="stats-container">

    <div class="dashboard-header">
        <h2><i class="fas fa-chart-pie"></i> Analítica de Operaciones</h2>

        <div class="filter-btn-group">
            <button class="filter-btn active" onclick="cambiarFiltro(this, 'mes')">Este Mes</button>
            <button class="filter-btn" onclick="cambiarFiltro(this, 'trimestre')">Trimestre</button>
            <button class="filter-btn" onclick="cambiarFiltro(this, 'ano')">Este Año</button>
        </div>
    </div>

    <div class="kpi-wrapper">
        <div class="kpi-card c-blue">
            <div class="kpi-header">
                <p class="kpi-title">Viajes Realizados</p>
                <div class="kpi-icon-box"><i class="fas fa-route"></i></div>
            </div>
            <div class="kpi-value" id="kpiTotal">142</div>
            <div class="kpi-trend trend-up">
                <i class="fas fa-arrow-up"></i> <span>12% vs mes anterior</span>
            </div>
        </div>

        <div class="kpi-card c-green">
            <div class="kpi-header">
                <p class="kpi-title">Unidades en Ruta</p>
                <div class="kpi-icon-box"><i class="fas fa-truck-moving"></i></div>
            </div>
            <div class="kpi-value" id="kpiActivos">12</div>
            <div class="kpi-trend trend-neutral">
                <i class="fas fa-minus"></i> <span>Flota operando al 85%</span>
            </div>
        </div>

        <div class="kpi-card c-red">
            <div class="kpi-header">
                <p class="kpi-title">Viajes Riesgo Alto / Muy Alto</p>
                <div class="kpi-icon-box"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
            <div class="kpi-value" id="kpiRiesgo">8</div>
            <div class="kpi-trend trend-down">
                <i class="fas fa-arrow-down"></i> <span>-2% de incidencias</span>
            </div>
        </div>

        <div class="kpi-card c-orange">
            <div class="kpi-header">
                <p class="kpi-title">Alertas QHSE (Docs Vencidos)</p>
                <div class="kpi-icon-box"><i class="fas fa-id-card-alt"></i></div>
            </div>
            <div class="kpi-value" id="kpiVencidos">3</div>
            <div class="kpi-trend trend-up">
                <i class="fas fa-exclamation-circle" style="color:#f59e0b;"></i> <span style="color:#f59e0b;">Requiere atención</span>
            </div>
        </div>
    </div>

    <div class="charts-wrapper">

        <div class="chart-box">
            <div class="chart-header">
                <h3>Estatus Global de Solicitudes</h3>
                <button class="btn-export"><i class="fas fa-download"></i></button>
            </div>
            <div class="canvas-container">
                <canvas id="chartEstatus"></canvas>
            </div>
        </div>

        <div class="chart-box">
            <div class="chart-header">
                <h3>Distribución por Nivel de Riesgo</h3>
                <button class="btn-export"><i class="fas fa-download"></i></button>
            </div>
            <div class="canvas-container">
                <canvas id="chartRiesgo"></canvas>
            </div>
        </div>

        <div class="chart-box full-width">
            <div class="chart-header">
                <h3>Auditoría de Documentos de Conductores (QHSE)</h3>
                <button class="btn-export"><i class="fas fa-download"></i> CSV</button>
            </div>
            <div class="canvas-container" style="height: 350px;">
                <canvas id="chartDocumentos"></canvas>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Variables globales para Chart.js
    let myChartEstatus, myChartRiesgo, myChartDocumentos;

    // Configuración global para que Chart.js se vea premium
    Chart.defaults.font.family = "'Poppins', sans-serif";
    Chart.defaults.color = '#64748b';
    Chart.defaults.scale.grid.color = '#f1f5f9';

    document.addEventListener("DOMContentLoaded", function () {
        initCharts();
    });

    function initCharts() {
        // -------------------------------------------------------------
        // 1. DOUGHNUT CHART (Estatus)
        // -------------------------------------------------------------
        const ctxEstatus = document.getElementById('chartEstatus').getContext('2d');
        myChartEstatus = new Chart(ctxEstatus, {
            type: 'doughnut',
            data: {
                labels: ['Finalizados', 'En Curso', 'Por Iniciar', 'Cancelados'],
                datasets: [{
                    data: [110, 12, 15, 5],
                    backgroundColor: [
                        '#10b981', // Verde
                        '#3b82f6', // Azul
                        '#94a3b8', // Gris
                        '#ef4444'  // Rojo
                    ],
                    borderWidth: 0,
                    hoverOffset: 10 // Efecto pop-out al pasar el mouse
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%', // Dona más delgada (moderno)
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 20, usePointStyle: true, pointStyle: 'circle' }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        padding: 12,
                        cornerRadius: 8
                    }
                }
            }
        });

        // -------------------------------------------------------------
        // 2. HORIZONTAL BAR CHART (Riesgo)
        // -------------------------------------------------------------
        const ctxRiesgo = document.getElementById('chartRiesgo').getContext('2d');
        myChartRiesgo = new Chart(ctxRiesgo, {
            type: 'bar',
            data: {
                labels: ['Bajo', 'Medio', 'Alto', 'Muy Alto'],
                datasets: [{
                    label: 'Viajes',
                    data: [85, 45, 8, 4],
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.8)', // Verde
                        'rgba(245, 158, 11, 0.8)', // Amarillo
                        'rgba(249, 115, 22, 0.8)', // Naranja
                        'rgba(239, 68, 68, 0.8)'   // Rojo
                    ],
                    borderRadius: 6,
                    borderSkipped: false, // Redondea todos los lados
                    barThickness: 24
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y', // Convertir a horizontal
                plugins: {
                    legend: { display: false },
                    tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.9)', padding: 12, cornerRadius: 8 }
                },
                scales: {
                    x: { grid: { display: true, drawBorder: false, color: '#f1f5f9' } },
                    y: { grid: { display: false, drawBorder: false } }
                }
            }
        });

        // -------------------------------------------------------------
        // 3. GROUPED BAR CHART (Documentos QHSE)
        // -------------------------------------------------------------
        const ctxDoc = document.getElementById('chartDocumentos').getContext('2d');
        myChartDocumentos = new Chart(ctxDoc, {
            type: 'bar',
            data: {
                labels: ['Licencia Ligera', 'Curso Manejo Ligera', 'Licencia Federal', 'Curso Manejo Pesada'],
                datasets: [
                    {
                        label: 'Vigente',
                        data: [45, 40, 25, 22],
                        backgroundColor: '#10b981',
                        borderRadius: 4
                    },
                    {
                        label: 'Vence en < 30 días',
                        data: [3, 5, 2, 4],
                        backgroundColor: '#f59e0b',
                        borderRadius: 4
                    },
                    {
                        label: 'Vencido / Falta',
                        data: [2, 1, 3, 0],
                        backgroundColor: '#ef4444',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8 } },
                    tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.9)', padding: 12, cornerRadius: 8 }
                },
                scales: {
                    x: { grid: { display: false, drawBorder: false } },
                    y: {
                        grid: { borderDash: [4, 4], color: '#e2e8f0', drawBorder: false },
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // -------------------------------------------------------------
    // FUNCIÓN DE INTERACCIÓN: Filtros y Simulación de Carga AJAX
    // -------------------------------------------------------------
    function cambiarFiltro(btnElement, periodo) {
        // Quitar clase active a todos y ponérsela al clickeado
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        btnElement.classList.add('active');

        // Mostrar un loader (si tienes SweetAlert configurado)
        if(typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Calculando...',
                text: 'Procesando datos del ' + periodo,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });
        }

        // Simulamos el delay de red (AJAX)
        setTimeout(() => {
            // Actualizar KPIs con datos falsos
            document.getElementById('kpiTotal').innerText = Math.floor(Math.random() * 300) + 50;
            document.getElementById('kpiActivos').innerText = Math.floor(Math.random() * 40);
            document.getElementById('kpiRiesgo').innerText = Math.floor(Math.random() * 20);

            // Animar la actualización de las gráficas
            myChartEstatus.data.datasets[0].data = [
                Math.floor(Math.random() * 200), Math.floor(Math.random() * 40),
                Math.floor(Math.random() * 30), Math.floor(Math.random() * 15)
            ];
            myChartEstatus.update();

            myChartRiesgo.data.datasets[0].data = [
                Math.floor(Math.random() * 150), Math.floor(Math.random() * 80),
                Math.floor(Math.random() * 30), Math.floor(Math.random() * 10)
            ];
            myChartRiesgo.update();

            if(typeof Swal !== 'undefined') Swal.close();
        }, 600);
    }
</script>
@endpush
