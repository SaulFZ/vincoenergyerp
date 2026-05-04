@extends('modules.systems.tickets.index')

@section('content')
    <style>
        .stats-dashboard {
            padding: 24px;
            background: #f8fafc; /* Fondo limpio Pizarra claro */
            min-height: calc(100vh - 140px);
            border-radius: 14px;
        }

        .dashboard-header {
            margin-bottom: 28px;
        }

        .dashboard-header h2 {
            color: #0f172a;
            font-weight: 700;
            font-size: 1.6rem;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }

        .dashboard-header p {
            color: #64748b;
            margin-top: 6px;
            font-size: 0.95rem;
        }

        /* KPIs Superiores */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .kpi-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.2s ease;
        }

        .kpi-card:hover {
            transform: translateY(-4px);
        }

        .kpi-info {
            display: flex;
            flex-direction: column;
        }

        .kpi-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.05em;
            margin-bottom: 5px;
        }

        .kpi-value {
            font-size: 2rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }

        .kpi-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        /* Colores corporativos para KPIs */
        .icon-total { background: #e0f2fe; color: #0284c7; }
        .icon-pendientes { background: #fef08a; color: #b45309; }
        .icon-resueltos { background: #dcfce7; color: #15803d; }

        /* Grid de Gráficas */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }

        /* Excepción para la gráfica de línea que ocupa todo el ancho */
        .chart-card.full-width {
            grid-column: 1 / -1;
        }

        .chart-card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .chart-header {
            width: 100%;
            font-size: 1.05rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chart-container {
            position: relative;
            height: 280px;
            width: 100%;
        }

        @media (max-width: 1024px) {
            .charts-grid { grid-template-columns: 1fr; }
        }
    </style>

    <div class="stats-dashboard content active">
        <div class="dashboard-header">
            <h2><i class="fas fa-chart-bar"></i> Centro de Mando Analítico</h2>
            <p>Métricas de soporte técnico y tendencias de incidentes operacionales.</p>
        </div>

        {{-- Tarjetas KPI --}}
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-info">
                    <span class="kpi-label">Volumen Histórico</span>
                    <span class="kpi-value" id="kpi-total">0</span>
                </div>
                <div class="kpi-icon icon-total"><i class="fas fa-ticket-alt"></i></div>
            </div>

            <div class="kpi-card">
                <div class="kpi-info">
                    <span class="kpi-label">Soporte Activo</span>
                    <span class="kpi-value" id="kpi-pendientes">0</span>
                </div>
                <div class="kpi-icon icon-pendientes"><i class="fas fa-tools"></i></div>
            </div>

            <div class="kpi-card">
                <div class="kpi-info">
                    <span class="kpi-label">Tickets Concluidos</span>
                    <span class="kpi-value" id="kpi-resueltos">0</span>
                </div>
                <div class="kpi-icon icon-resueltos"><i class="fas fa-check-double"></i></div>
            </div>
        </div>

        {{-- Gráficas --}}
        <div class="charts-grid">
            <!-- Tendencia (Gráfica de Línea - Ancho Completo) -->
            <div class="chart-card full-width">
                <h3 class="chart-header"><i class="fas fa-chart-line"></i> Tendencia de Incidentes (Últimos 7 días)</h3>
                <div class="chart-container" style="height: 300px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <!-- Gráfica de Estados -->
            <div class="chart-card">
                <h3 class="chart-header"><i class="fas fa-tasks"></i> Distribución por Estatus</h3>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <!-- Departamentos Top -->
            <div class="chart-card">
                <h3 class="chart-header"><i class="fas fa-building"></i> Top Departamentos con Incidencias</h3>
                <div class="chart-container">
                    <canvas id="deptChart"></canvas>
                </div>
            </div>

            <!-- Prioridad -->
            <div class="chart-card">
                <h3 class="chart-header"><i class="fas fa-flag"></i> Matriz de Prioridades</h3>
                <div class="chart-container" style="display: flex; justify-content: center;">
                    <div style="max-width: 250px; width: 100%;">
                        <canvas id="priorityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Configuración Global para darle toque "Enterprise" (Pizarra/Cerceta)
        Chart.defaults.font.family = "'Poppins', sans-serif";
        Chart.defaults.color = '#64748b';
        Chart.defaults.scale.grid.color = '#f1f5f9';

        document.addEventListener('DOMContentLoaded', async () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const fetchConfig = { headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } };

            try {
                const response = await fetch('{{ route('systems.tickets.stats.data') }}', fetchConfig);
                if (!response.ok) throw new Error('No se pudieron cargar los datos estadísticos');

                const data = await response.json();

                // 1. Llenar KPIs
                document.getElementById('kpi-total').innerText = data.kpis.total;
                document.getElementById('kpi-pendientes').innerText = data.kpis.pendientes;
                document.getElementById('kpi-resueltos').innerText = data.kpis.resueltos;

                // 2. Paletas de Colores (Pizarra y Cerceta)
                const tealPalette = ['#0f766e', '#16a34a', '#eab308', '#f59e0b', '#0284c7', '#dc2626'];
                const statusColors = ['#94a3b8', '#38bdf8', '#facc15', '#fb923c', '#22c55e', '#ef4444'];
                const priorityColors = ['#ef4444', '#f59e0b', '#22c55e'];

                // 3. Gráfica de Tendencia (Línea)
                new Chart(document.getElementById('trendChart').getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: data.trend.labels,
                        datasets: [{
                            label: 'Tickets Creados',
                            data: data.trend.data,
                            borderColor: '#0d9488', // Cerceta oscuro
                            backgroundColor: 'rgba(13, 148, 136, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4, // Curvas suaves
                            pointBackgroundColor: '#0d9488',
                            pointRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
                });

                // 4. Gráfica de Estatus (Barras)
                new Chart(document.getElementById('statusChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: ['Nuevo', 'Abierto', 'En Espera', 'Por Concluir', 'Realizado', 'Cancelado'],
                        datasets: [{
                            data: [data.status['nuevo'], data.status['abierto'], data.status['en-espera'], data.status['por-concluir'], data.status['realizado'], data.status['cancelado']],
                            backgroundColor: statusColors,
                            borderRadius: 6
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                });

                // 5. Gráfica de Departamentos Top (Barras Horizontales)
                new Chart(document.getElementById('deptChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: data.departments.labels,
                        datasets: [{
                            data: data.departments.data,
                            backgroundColor: '#334155', // Slate oscuro
                            borderRadius: 6
                        }]
                    },
                    options: {
                        indexAxis: 'y', // La vuelve horizontal
                        responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } }
                    }
                });

                // 6. Gráfica de Prioridades (Dona)
                new Chart(document.getElementById('priorityChart').getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Alta', 'Media', 'Baja'],
                        datasets: [{
                            data: [data.priority['alta'], data.priority['media'], data.priority['baja']],
                            backgroundColor: priorityColors,
                            borderWidth: 0
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, cutout: '70%' }
                });

            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'Hubo un problema al conectar con la base de datos analítica.', 'error');
            }
        });
    </script>
@endpush
