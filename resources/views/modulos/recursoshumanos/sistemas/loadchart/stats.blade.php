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
        margin: 0 !important;
        padding: 0 !important;
    }

    /* O si prefieres mantener algo de padding */
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

    .date-range-picker {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .date-separator {
        color: var(--text-medium);
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
    }

    .btn-primary {
        background-color: var(--primary-color);
        color: white;
    }

    .btn-primary:hover {
        background-color: #2e59d9;
        transform: translateY(-2px);
    }

    /* Summary Cards */
    .summary-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }

    .summary-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        padding: 25px;
        display: flex;
        align-items: center;
        gap: 20px;
        transition: transform 0.2s;
    }

    .summary-card:hover {
        transform: translateY(-5px);
    }

    .card-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
    }

    .bg-primary { background-color: var(--primary-color); }
    .bg-success { background-color: var(--success-color); }
    .bg-info { background-color: var(--info-color); }
    .bg-warning { background-color: var(--warning-color); }

    .card-title {
        font-size: 14px;
        color: var(--text-medium);
        margin: 0 0 5px 0;
        font-weight: 500;
        text-transform: uppercase;
    }

    .card-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
    }

    .card-change {
        font-size: 12px;
        margin: 5px 0 0 0;
        display: flex;
        align-items: center;
        gap: 4px;
        font-weight: 600;
    }

    .card-change.positive { color: var(--success-color); }
    .card-change.negative { color: var(--danger-color); }
    .card-change.neutral { color: var(--info-color); }

    /* Charts Section */
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
    }

    .chart-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        padding: 25px;
        display: flex;
        flex-direction: column;
    }

    .chart-full-width {
        grid-column: 1 / -1;
    }

    .chart-title {
        font-size: 1.1rem;
        color: var(--text-medium);
        margin-top: 0;
        margin-bottom: 20px;
        font-weight: 600;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color);
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .charts-grid {
            grid-template-columns: 1fr;
        }

        .chart-full-width {
            grid-column: auto;
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

        .dashboard-filters {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-group {
            min-width: auto;
        }
    }
</style>
    <main class="dashboard-container">
        <header class="dashboard-header">
            <i class="fas fa-chart-line header-icon"></i>
            <h1 class="header-title">Panel de Estadísticas de Actividades</h1>
        </header>

        <section class="dashboard-filters">
            <div class="filter-group">
                <label for="time-period" class="filter-label">Periodo</label>
                <select id="time-period" class="select-field">
                    <option value="monthly">Mensual</option>
                    <option value="quarterly">Trimestral</option>
                    <option value="yearly">Anual</option>
                </select>
            </div>

            <div class="filter-group date-range-group">
                <label for="date-range" class="filter-label">Rango de Fechas</label>
                <div class="date-range-picker">
                    <input type="text" id="start-date" class="input-field" placeholder="Fecha inicio">
                    <span class="date-separator">a</span>
                    <input type="text" id="end-date" class="input-field" placeholder="Fecha fin">
                </div>
            </div>

            <button class="btn btn-primary" id="apply-filters">
                <i class="fas fa-filter"></i> Aplicar Filtros
            </button>
        </section>

        <section class="summary-cards-grid">
            <div class="summary-card">
                <div class="card-icon bg-primary">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="card-content">
                    <p class="card-title">Días Trabajados</p>
                    <p class="card-value">184</p>
                    <p class="card-change positive">▲ 5% vs periodo anterior</p>
                </div>
            </div>

            <div class="summary-card">
                <div class="card-icon bg-success">
                    <i class="fas fa-umbrella-beach"></i>
                </div>
                <div class="card-content">
                    <p class="card-title">Días de Descanso</p>
                    <p class="card-value">52</p>
                    <p class="card-change neutral">● 0% vs periodo anterior</p>
                </div>
            </div>

            <div class="summary-card">
                <div class="card-icon bg-info">
                    <i class="fas fa-plane"></i>
                </div>
                <div class="card-content">
                    <p class="card-title">Días de Viaje</p>
                    <p class="card-value">28</p>
                    <p class="card-change positive">▲ 12% vs periodo anterior</p>
                </div>
            </div>

            <div class="summary-card">
                <div class="card-icon bg-warning">
                    <i class="fas fa-medkit"></i>
                </div>
                <div class="card-content">
                    <p class="card-title">Días Médicos</p>
                    <p class="card-value">3</p>
                    <p class="card-change negative">▼ 2% vs periodo anterior</p>
                </div>
            </div>
        </section>

        <section class="charts-grid">
            <div class="chart-card">
                <h2 class="chart-title">Distribución de Actividades</h2>
                <div id="activitiesPieChart" class="chart-content"></div>
            </div>

            <div class="chart-card">
                <h2 class="chart-title">Tendencia Mensual</h2>
                <div id="monthlyTrendChart" class="chart-content"></div>
            </div>

            <div class="chart-card chart-full-width">
                <h2 class="chart-title">Actividades por Departamento</h2>
                <div id="departmentComparisonChart" class="chart-content"></div>
            </div>

            <div class="chart-card">
                <h2 class="chart-title">Horas Extra</h2>
                <div id="overtimeChart" class="chart-content"></div>
            </div>

            <div class="chart-card">
                <h2 class="chart-title">Eficiencia por Puesto</h2>
                <div id="efficiencyChart" class="chart-content"></div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

   <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de distribución de actividades (Pie)
        var pieOptions = {
            series: [35, 25, 15, 10, 8, 5, 2],
            chart: {
                type: 'donut',
                height: 350,
            },
            labels: ['Base', 'Pozo', 'Descanso', 'Entrenamiento', 'Vacaciones', 'Médico', 'Viaje'],
            colors: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69'],
            legend: {
                position: 'bottom'
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }],
            plotOptions: {
                pie: {
                    donut: {
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total Días',
                                formatter: function(w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                }
                            }
                        }
                    }
                }
            }
        };

        var pieChart = new ApexCharts(document.querySelector("#activitiesPieChart"), pieOptions);
        pieChart.render();

        // Gráfico de tendencia mensual (Line)
        var lineOptions = {
            series: [{
                name: "Base",
                data: [28, 29, 33, 36, 32, 32, 33, 30, 28, 30, 31, 29]
            }, {
                name: "Pozo",
                data: [12, 11, 14, 18, 17, 13, 13, 15, 12, 11, 12, 10]
            }, {
                name: "Descanso",
                data: [8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8]
            }],
            chart: {
                height: 350,
                type: 'line',
                dropShadow: {
                    enabled: true,
                    color: '#000',
                    top: 18,
                    left: 7,
                    blur: 10,
                    opacity: 0.2
                },
                toolbar: {
                    show: true
                }
            },
            colors: ['#4e73df', '#1cc88a', '#36b9cc'],
            dataLabels: {
                enabled: false,
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            grid: {
                borderColor: '#e7e7e7',
                row: {
                    colors: ['#f3f3f3', 'transparent'],
                    opacity: 0.5
                },
            },
            markers: {
                size: 5
            },
            xaxis: {
                categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                title: {
                    text: 'Meses'
                }
            },
            yaxis: {
                title: {
                    text: 'Días'
                },
                min: 5,
                max: 40
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                floating: true,
                offsetY: -25,
                offsetX: -5
            }
        };

        var lineChart = new ApexCharts(document.querySelector("#monthlyTrendChart"), lineOptions);
        lineChart.render();

        // Gráfico de comparación por departamento (Bar)
        var barOptions = {
            series: [{
                name: 'Base',
                data: [44, 55, 41, 37, 22, 43, 21]
            }, {
                name: 'Pozo',
                data: [53, 32, 33, 52, 13, 43, 32]
            }, {
                name: 'Entrenamiento',
                data: [12, 17, 11, 9, 15, 11, 20]
            }],
            chart: {
                type: 'bar',
                height: 350,
                stacked: true,
                stackType: '100%'
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                },
            },
            stroke: {
                width: 1,
                colors: ['#fff']
            },
            colors: ['#4e73df', '#1cc88a', '#f6c23e'],
            xaxis: {
                categories: ['Operaciones', 'Ingeniería', 'Mantenimiento', 'Logística', 'RH', 'Finanzas', 'TI'],
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + " días"
                    }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'left',
                offsetX: 40
            },
            fill: {
                opacity: 1
            }
        };

        var barChart = new ApexCharts(document.querySelector("#departmentComparisonChart"), barOptions);
        barChart.render();

        // Gráfico de horas extra (Radial)
        var radialOptions = {
            series: [75],
            chart: {
                height: 350,
                type: 'radialBar',
            },
            plotOptions: {
                radialBar: {
                    startAngle: -135,
                    endAngle: 135,
                    hollow: {
                        margin: 0,
                        size: '70%',
                    },
                    dataLabels: {
                        name: {
                            offsetY: -10,
                            color: '#333',
                            fontSize: '13px'
                        },
                        value: {
                            color: '#333',
                            fontSize: '30px',
                            show: true,
                            formatter: function(val) {
                                return val + ' hrs';
                            }
                        }
                    },
                    track: {
                        background: '#e0e0e0',
                        strokeWidth: '97%',
                        margin: 5,
                    }
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'dark',
                    shadeIntensity: 0.15,
                    inverseColors: false,
                    opacityFrom: 1,
                    opacityTo: 1,
                    stops: [0, 50, 65, 91]
                },
            },
            stroke: {
                dashArray: 4
            },
            labels: ['Horas Extra'],
            colors: ['#e74a3b']
        };

        var radialChart = new ApexCharts(document.querySelector("#overtimeChart"), radialOptions);
        radialChart.render();

        // Gráfico de eficiencia (Radar)
        var radarOptions = {
            series: [{
                name: 'Eficiencia',
                data: [80, 90, 75, 95, 70, 85]
            }],
            chart: {
                height: 350,
                type: 'radar',
            },
            colors: ['#4e73df'],
            xaxis: {
                categories: ['Puntualidad', 'Productividad', 'Colaboración', 'Calidad', 'Seguridad', 'Adaptabilidad']
            },
            yaxis: {
                show: false,
                max: 100
            },
            markers: {
                size: 5,
                hover: {
                    size: 7
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + '%';
                    }
                }
            }
        };

        var radarChart = new ApexCharts(document.querySelector("#efficiencyChart"), radarOptions);
        radarChart.render();

        // Manejo de filtros
        document.getElementById('apply-filters').addEventListener('click', function() {
            // Aquí iría la lógica para actualizar los gráficos con los nuevos filtros
            console.log('Aplicando filtros...');
            // En una implementación real, haríamos una petición AJAX para obtener nuevos datos
            // y luego actualizaríamos los gráficos con chart.updateSeries()
        });
    });
</script>
@endsection
