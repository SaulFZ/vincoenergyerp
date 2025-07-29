@extends('modulos.recursoshumanos.sistemas.loadchart.index')

@section('content')

    <style>
        .statistics-container {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .statistics-container h1 {
            color: #2c3e50;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .statistics-filters {
            background: #f8f9fc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
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

        .filter-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: #5a5c69;
        }

        .date-range-picker {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .date-input {
            padding: 8px 12px;
            border: 1px solid #d1d3e2;
            border-radius: 4px;
            width: 120px;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .card-content {
            flex: 1;
        }

        .card-content h3 {
            font-size: 14px;
            color: #5a5c69;
            margin: 0 0 5px 0;
        }

        .card-value {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .card-change {
            font-size: 12px;
            margin: 5px 0 0 0;
        }

        .card-change.positive {
            color: #1cc88a;
        }

        .card-change.negative {
            color: #e74a3b;
        }

        .card-change.neutral {
            color: #f6c23e;
        }

        .chart-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .chart-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 20px;
            flex: 1;
        }

        .chart-container.full-width {
            flex: 100%;
        }

        .chart-container h3 {
            color: #5a5c69;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 16px;
        }

        .select-custom {
            padding: 8px 12px;
            border: 1px solid #d1d3e2;
            border-radius: 4px;
            background-color: white;
            width: 100%;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .btn-primary {
            background-color: #4e73df;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2e59d9;
        }

        @media (max-width: 768px) {
            .chart-row {
                flex-direction: column;
            }

            .statistics-filters {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group {
                min-width: auto;
            }
        }
    </style>
    <div class="statistics-container">
        <h1><i class="fas fa-chart-pie"></i> Estadísticas de Actividades</h1>

        <div class="statistics-filters">
            <div class="filter-group">
                <label for="time-period">Periodo:</label>
                <select id="time-period" class="select-custom">
                    <option value="monthly">Mensual</option>
                    <option value="quarterly">Trimestral</option>
                    <option value="yearly">Anual</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="date-range">Rango de fechas:</label>
                <div class="date-range-picker">
                    <input type="text" id="start-date" class="date-input" placeholder="Fecha inicio">
                    <span>a</span>
                    <input type="text" id="end-date" class="date-input" placeholder="Fecha fin">
                </div>
            </div>

            <button class="btn btn-primary" id="apply-filters">
                <i class="fas fa-filter"></i> Aplicar Filtros
            </button>
        </div>

        <div class="statistics-grid">
            <!-- Tarjetas de resumen -->
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="card-icon" style="background-color: #4e73df;">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="card-content">
                        <h3>Días Trabajados</h3>
                        <p class="card-value">184</p>
                        <p class="card-change positive">+5% vs periodo anterior</p>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="card-icon" style="background-color: #1cc88a;">
                        <i class="fas fa-umbrella-beach"></i>
                    </div>
                    <div class="card-content">
                        <h3>Días de Descanso</h3>
                        <p class="card-value">52</p>
                        <p class="card-change neutral">0% vs periodo anterior</p>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="card-icon" style="background-color: #36b9cc;">
                        <i class="fas fa-plane"></i>
                    </div>
                    <div class="card-content">
                        <h3>Días de Viaje</h3>
                        <p class="card-value">28</p>
                        <p class="card-change positive">+12% vs periodo anterior</p>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="card-icon" style="background-color: #f6c23e;">
                        <i class="fas fa-medkit"></i>
                    </div>
                    <div class="card-content">
                        <h3>Días Médicos</h3>
                        <p class="card-value">3</p>
                        <p class="card-change negative">-2% vs periodo anterior</p>
                    </div>
                </div>
            </div>

            <!-- Gráficas principales -->
            <div class="chart-row">
                <div class="chart-container">
                    <h3>Distribución de Actividades</h3>
                    <div id="activitiesPieChart"></div>
                </div>

                <div class="chart-container">
                    <h3>Tendencia Mensual</h3>
                    <div id="monthlyTrendChart"></div>
                </div>
            </div>

            <div class="chart-row">
                <div class="chart-container full-width">
                    <h3>Actividades por Departamento</h3>
                    <div id="departmentComparisonChart"></div>
                </div>
            </div>

            <div class="chart-row">
                <div class="chart-container">
                    <h3>Horas Extra</h3>
                    <div id="overtimeChart"></div>
                </div>

                <div class="chart-container">
                    <h3>Eficiencia por Puesto</h3>
                    <div id="efficiencyChart"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Incluir ApexCharts -->
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
                                    formatter: function (w) {
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
                        formatter: function (val) {
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
                                formatter: function (val) {
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
