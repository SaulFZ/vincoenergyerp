 <!-- ========================================== -->
<!-- 1. FILTROS EXCLUSIVOS  -->
<!-- ========================================== -->
   <section class="filter-section no-print">
       <form class="filter-form" action="{{ route('operations.sgc.index') }}" method="GET">
           <div class="filter-grid">

               <div class="filter-group custom-dropdown">
                   <label class="filter-label">Años</label>
                   <div class="filter-input dropdown-trigger">
                       <span>Seleccionar años</span>
                       <i class="fas fa-chevron-down text-slate-400"></i>
                   </div>
                   <div class="dropdown-menu">
                       <label class="dropdown-item"><input type="checkbox" name="year[]" value="2026" checked>
                           2026</label>
                       <label class="dropdown-item"><input type="checkbox" name="year[]" value="2025"> 2025</label>
                       <label class="dropdown-item"><input type="checkbox" name="year[]" value="2024"> 2024</label>
                   </div>
               </div>

               <div class="filter-group">
                   <label class="filter-label">Periodo</label>
                   <select name="period" class="filter-input">
                       <option value="ANUAL" selected>Anual</option>
                       <option value="Q1">Q1 · Ene-Mar</option>
                       <option value="Q2">Q2 · Abr-Jun</option>
                       <option value="Q3">Q3 · Jul-Sep</option>
                       <option value="Q4">Q4 · Oct-Dic</option>
                   </select>
               </div>

               <div class="filter-group">
                   <label class="filter-label">Compañía</label>
                   <select name="client" class="filter-input">
                       <option value="TODOS" selected>Todas las compañías</option>
                       <option value="PEMEX">PEMEX</option>
                       <option value="SLB">SLB</option>
                       <option value="HALLIBURTON">HALLIBURTON</option>
                   </select>
               </div>

               <div class="filter-group">
                   <label class="filter-label">Servicio</label>
                   <select name="serviceType" class="filter-input">
                       <option value="TODOS" selected>Todos los tipos</option>
                       <option value="Registros Geofísicos">Registros Geofísicos</option>
                       <option value="Disparos de Producción">Disparos de Producción</option>
                   </select>
               </div>

               <div class="filter-group">
                   <label class="filter-label">Tipo NPT</label>
                   <select name="eventType" class="filter-input">
                       <option value="TODOS" selected>Todos</option>
                       <option value="Falla de herramienta">Falla de herramienta</option>
                       <option value="Falla de cable">Falla de cable</option>
                   </select>
               </div>

               <div class="filter-group">
                   <label class="filter-label">Cálculo SQIR</label>
                   <select name="sqirMode" class="filter-input">
                       <option value="os" selected>Por trabajos</option>
                       <option value="horas">Por horas</option>
                   </select>
               </div>

               <div class="filter-group">
                   <label class="filter-label" style="opacity: 0;">Acciones</label>
                   <div class="filter-actions">
                       <button type="button" onclick="window.print()" class="btn-icon btn-pdf" title="Exportar a PDF">
                           <i class="fas fa-file-pdf"></i> &nbsp;PDF
                       </button>
                       <a href="{{ route('operations.sgc.index', ['view' => 'npt']) }}" class="btn-icon btn-clear"
                           title="Limpiar filtros">
                           <i class="fas fa-undo-alt"></i>
                       </a>
                   </div>
               </div>

           </div>
       </form>
   </section>

<!-- ========================================== -->
<!-- 2. CONTENIDO DE LA VISTA                   -->
<!-- ========================================== -->
<div class="tab-npt">

    <!-- ENCABEZADO NPT -->
    <div class="panel panel-blue-header">
        <div class="module-header">
            <div>
                <p class="module-subtitle">Indicadores de tiempo no productivo</p>
                <h2 class="module-title">NPT · Trabajos · Horas · Corridas</h2>
            </div>
            <p class="module-description">
                Todos los indicadores usan los mismos filtros de año, periodo, cliente y servicio. Si eliges una causa de NPT, el numerador se limita a esa causa y el denominador conserva el universo operativo filtrado.
            </p>
        </div>
    </div>

    <!-- KPIs PRINCIPALES (Fila 1) -->
    <div class="kpi-grid kpi-grid-large">
        <div class="kpi-card kpi-blue">
            <p class="kpi-card-title">Trabajos realizados</p>
            <p class="kpi-card-value">100</p>
            <p class="kpi-card-note">90 sin NPT · 10 con NPT</p>
        </div>
        <div class="kpi-card kpi-amber">
            <p class="kpi-card-title">NPT / Trabajos</p>
            <p class="kpi-card-value">10%</p>
            <p class="kpi-card-note">10 trabajos con NPT de 100</p>
        </div>
        <div class="kpi-card kpi-blue">
            <p class="kpi-card-title">Corridas realizadas</p>
            <p class="kpi-card-value">116</p>
            <p class="kpi-card-note">104 sin NPT · 12 con NPT</p>
        </div>
        <div class="kpi-card kpi-rose">
            <p class="kpi-card-title">NPT / Corridas</p>
            <p class="kpi-card-value">10.3%</p>
            <p class="kpi-card-note">12 corridas con NPT de 116</p>
        </div>
        <div class="kpi-card kpi-blue">
            <p class="kpi-card-title">Horas operativas</p>
            <p class="kpi-card-value">3404.5 <span class="kpi-card-value-small">h</span></p>
            <p class="kpi-card-note">tiempo operativo acumulado</p>
        </div>
        <div class="kpi-card kpi-blue">
            <p class="kpi-card-title">NPT / Horas</p>
            <p class="kpi-card-value">2%</p>
            <p class="kpi-card-note">NPT / (horas OPS + horas NPT)</p>
        </div>
        <div class="kpi-card kpi-amber">
            <p class="kpi-card-title">Horas NPT</p>
            <p class="kpi-card-value">68.0 <span class="kpi-card-value-small">h</span></p>
            <p class="kpi-card-note">10 eventos / trabajos afectados</p>
        </div>
        <div class="kpi-card kpi-amber">
            <p class="kpi-card-title">Trabajos libres de NPT</p>
            <p class="kpi-card-value">90%</p>
            <p class="kpi-card-note">90 de 100 trabajos</p>
        </div>
    </div>

    <!-- KPIs SECUNDARIOS (Fila 2) -->
    <div class="kpi-grid kpi-grid-large">
        <div class="kpi-card kpi-white">
            <p class="kpi-card-title">Promedio NPT / evento</p>
            <p class="kpi-card-value">6.8 <span class="kpi-card-value-small">h</span></p>
            <p class="kpi-card-note">horas NPT / trabajos con NPT</p>
        </div>
        <div class="kpi-card kpi-white">
            <p class="kpi-card-title">Eventos NPT / 100 corridas</p>
            <p class="kpi-card-value">8.6</p>
            <p class="kpi-card-note">frecuencia normalizada por actividad</p>
        </div>
        <div class="kpi-card kpi-blue">
            <p class="kpi-card-title">Horas OPS entre NPT</p>
            <p class="kpi-card-value">340.4 <span class="kpi-card-value-small">h</span></p>
            <p class="kpi-card-note">confiabilidad operacional</p>
        </div>
        <div class="kpi-card kpi-rose">
            <p class="kpi-card-title">Corridas libres de NPT</p>
            <p class="kpi-card-value">89.7%</p>
            <p class="kpi-card-note">104 de 116 corridas</p>
        </div>
    </div>

    <!-- GRÁFICAS PASTEL -->
    <div class="grid-2-cols">
        <div class="panel">
            <h2 class="panel-title">Trabajos · sin NPT vs con NPT</h2>
            <div class="chart-pie-wrapper">
                <div class="pie-circle" style="background: conic-gradient(#0b3a78 0% 90%, #1769d1 90% 100%);"></div>
                <div class="pie-legend">
                    <div class="legend-item">
                        <span><span class="legend-color-dot bg-c1"></span><strong>Trabajos sin NPT</strong></span>
                        <span><strong>90</strong> · 90%</span>
                    </div>
                    <div class="legend-item">
                        <span><span class="legend-color-dot bg-c2"></span><strong>Trabajos con NPT</strong></span>
                        <span><strong>10</strong> · 10%</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel">
            <h2 class="panel-title">Corridas · sin NPT vs con NPT</h2>
            <div class="chart-pie-wrapper">
                <div class="pie-circle" style="background: conic-gradient(#0b3a78 0% 89.7%, #1769d1 89.7% 100%);"></div>
                <div class="pie-legend">
                    <div class="legend-item">
                        <span><span class="legend-color-dot bg-c1"></span><strong>Corridas sin NPT</strong></span>
                        <span><strong>104</strong> · 89.7%</span>
                    </div>
                    <div class="legend-item">
                        <span><span class="legend-color-dot bg-c2"></span><strong>Corridas con NPT</strong></span>
                        <span><strong>12</strong> · 10.3%</span>
                    </div>
                </div>
            </div>
            <p class="kpi-card-note" style="margin-top: 12px;">En OS históricas sin detalle de corrida, un NPT confirmado se asigna conservadoramente a una sola corrida para no inflar el indicador.</p>
        </div>
    </div>

    <!-- GRÁFICAS BARRAS (PARETO Y TENDENCIA) -->
    <div class="grid-2-cols">
        <div class="panel">
            <h2 class="panel-title">Pareto · horas NPT por causa / evento</h2>
            <div class="chart-bar-item">
                <div class="chart-bar-labels"><strong>Sin clasificar</strong> <strong>68.0 h</strong></div>
                <div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 100%;"></div></div>
            </div>
        </div>
        <div class="panel">
            <h2 class="panel-title">Tendencia · horas NPT por mes</h2>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>mar 2026</strong> <strong>6 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 20%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>abr 2026</strong> <strong>9 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 30%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>may 2026</strong> <strong>30 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 100%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>jun 2026</strong> <strong>14 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 46.7%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>jul 2026</strong> <strong>9 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 30%;"></div></div></div>
        </div>
    </div>

    <!-- TABLA TENDENCIA MENSUAL -->
    <div class="panel page-break">
        <h2 class="panel-title">Tendencia mensual de desempeño NPT</h2>
        <div class="table-wrap">
            <table class="sgc-table">
                <thead class="table-dark-header">
                    <tr>
                        <th>Mes</th>
                        <th class="th-center">Trabajos</th>
                        <th class="th-center">Con NPT</th>
                        <th class="th-center">NPT / trabajos</th>
                        <th class="th-center">Corridas</th>
                        <th class="th-center">Corridas NPT</th>
                        <th class="th-center">NPT / corridas</th>
                        <th class="th-center">Horas OPS</th>
                        <th class="th-center">Horas NPT</th>
                        <th class="th-center">NPT / horas</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>ene 2026</strong></td>
                        <td class="td-center td-bold">4</td>
                        <td class="td-center">0</td>
                        <td class="td-center td-bold">0%</td>
                        <td class="td-center">6</td>
                        <td class="td-center">0</td>
                        <td class="td-center td-bold">0%</td>
                        <td class="td-center">165.5 h</td>
                        <td class="td-center">0.0 h</td>
                        <td class="td-center td-bold">0%</td>
                    </tr>
                    <tr>
                        <td><strong>feb 2026</strong></td>
                        <td class="td-center td-bold">9</td>
                        <td class="td-center">0</td>
                        <td class="td-center td-bold">0%</td>
                        <td class="td-center">14</td>
                        <td class="td-center">0</td>
                        <td class="td-center td-bold">0%</td>
                        <td class="td-center">632.5 h</td>
                        <td class="td-center">0.0 h</td>
                        <td class="td-center td-bold">0%</td>
                    </tr>
                    <tr>
                        <td><strong>mar 2026</strong></td>
                        <td class="td-center td-bold">12</td>
                        <td class="td-center td-danger">1</td>
                        <td class="td-center td-bold">8.3%</td>
                        <td class="td-center">14</td>
                        <td class="td-center td-danger">1</td>
                        <td class="td-center td-bold">7.1%</td>
                        <td class="td-center">667.5 h</td>
                        <td class="td-center td-danger">6.0 h</td>
                        <td class="td-center td-bold">0.9%</td>
                    </tr>
                    <tr>
                        <td><strong>abr 2026</strong></td>
                        <td class="td-center td-bold">12</td>
                        <td class="td-center td-danger">2</td>
                        <td class="td-center td-bold">16.7%</td>
                        <td class="td-center">12</td>
                        <td class="td-center td-danger">2</td>
                        <td class="td-center td-bold">16.7%</td>
                        <td class="td-center">617.5 h</td>
                        <td class="td-center td-danger">9.0 h</td>
                        <td class="td-center td-bold">1.4%</td>
                    </tr>
                    <tr>
                        <td><strong>may 2026</strong></td>
                        <td class="td-center td-bold">9</td>
                        <td class="td-center td-danger">3</td>
                        <td class="td-center td-bold">33.3%</td>
                        <td class="td-center">14</td>
                        <td class="td-center td-danger">5</td>
                        <td class="td-center td-bold">35.7%</td>
                        <td class="td-center">399.0 h</td>
                        <td class="td-center td-danger">30.0 h</td>
                        <td class="td-center td-bold">7%</td>
                    </tr>
                    <tr>
                        <td><strong>jun 2026</strong></td>
                        <td class="td-center td-bold">15</td>
                        <td class="td-center td-danger">1</td>
                        <td class="td-center td-bold">6.7%</td>
                        <td class="td-center">15</td>
                        <td class="td-center td-danger">1</td>
                        <td class="td-center td-bold">6.7%</td>
                        <td class="td-center">922.5 h</td>
                        <td class="td-center td-danger">14.0 h</td>
                        <td class="td-center td-bold">1.5%</td>
                    </tr>
                    <tr>
                        <td><strong>jul 2026</strong></td>
                        <td class="td-center td-bold">18</td>
                        <td class="td-center td-danger">3</td>
                        <td class="td-center td-bold">16.7%</td>
                        <td class="td-center">18</td>
                        <td class="td-center td-danger">3</td>
                        <td class="td-center td-bold">16.7%</td>
                        <td class="td-center">0.0 h</td>
                        <td class="td-center td-danger">9.0 h</td>
                        <td class="td-center td-bold">100%</td>
                    </tr>
                    <tr>
                        <td><strong>ago 2026</strong></td>
                        <td class="td-center td-bold">21</td>
                        <td class="td-center">0</td>
                        <td class="td-center td-bold">0%</td>
                        <td class="td-center">23</td>
                        <td class="td-center">0</td>
                        <td class="td-center td-bold">0%</td>
                        <td class="td-center">0.0 h</td>
                        <td class="td-center">0.0 h</td>
                        <td class="td-center td-bold">0%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TABLA DE DETALLES NPT -->
    <div class="panel page-break">
        <h2 class="panel-title">Detalle de trabajos con NPT</h2>
        <div class="table-wrap">
            <table class="sgc-table">
                <thead>
                    <tr>
                        <th>OS</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Pozo</th>
                        <th>URE</th>
                        <th>Servicio</th>
                        <th>Tipo evento</th>
                        <th>NPT</th>
                        <th>Origen</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="td-bold">VES-WL-OS-1607</td>
                        <td>07/07/2026</td>
                        <td>PERENCO</td>
                        <td>SANTUARIO 505</td>
                        <td>VH-202</td>
                        <td>COLOCACIÓN DE TAPÓN</td>
                        <td><strong>Sin clasificar</strong></td>
                        <td class="td-danger">4.0 h</td>
                        <td>nptHours</td>
                        <td><span class="status-badge">TERMINADO</span></td>
                    </tr>
                    <tr>
                        <td class="td-bold">VES-WL-OS-1603</td>
                        <td>05/07/2026</td>
                        <td>PERENCO</td>
                        <td>SANTUARIO 304</td>
                        <td>VH-202</td>
                        <td>TECNOLOGÍA CORTE MCR</td>
                        <td><strong>Sin clasificar</strong></td>
                        <td class="td-danger">3.0 h</td>
                        <td>Tiempo NPT</td>
                        <td><span class="status-badge">TERMINADO</span></td>
                    </tr>
                    <tr>
                        <td class="td-bold">VES-WL-OS-1601</td>
                        <td>03/07/2026</td>
                        <td>DIXTONE</td>
                        <td>EL GOLPE 41T</td>
                        <td>VH-202</td>
                        <td>REGISTRO PNN/PNG</td>
                        <td><strong>Sin clasificar</strong></td>
                        <td class="td-danger">2.0 h</td>
                        <td>Tiempo NPT</td>
                        <td><span class="status-badge">TERMINADO</span></td>
                    </tr>
                    <tr>
                        <td class="td-bold">VES-WL-OS-1587</td>
                        <td>20/06/2026</td>
                        <td>DIXTONE</td>
                        <td>SANTUARIO 603</td>
                        <td>VH-202</td>
                        <td>COLOCACIÓN DE TAPÓN</td>
                        <td><strong>Sin clasificar</strong></td>
                        <td class="td-danger">14.0 h</td>
                        <td>Tiempo NPT</td>
                        <td><span class="status-badge">TERMINADO</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
