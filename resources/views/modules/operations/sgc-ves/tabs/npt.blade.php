<div class="tab-npt">

    <!-- ENCABEZADO NPT -->
    <div class="panel" style="border-color: #bfdbfe; padding: 16px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
            <div>
                <p style="font-size: 11px; font-weight: 900; color: #1d4ed8; letter-spacing: 0.2em; text-transform: uppercase;">Indicadores de tiempo no productivo</p>
                <h2 style="font-size: 24px; font-weight: 900; color: var(--primary-dark); margin-top: 4px;">NPT · Trabajos · Horas · Corridas</h2>
            </div>
            <p style="max-width: 600px; font-size: 12px; color: #475569; text-align: right;">
                Todos los indicadores usan los mismos filtros de año, periodo, cliente y servicio. Si eliges una causa de NPT, el numerador se limita a esa causa y el denominador conserva el universo operativo filtrado.
            </p>
        </div>
    </div>

    <!-- KPIs PRINCIPALES (Fila 1) -->
    <div class="kpi-grid mb-3" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 20px;">
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
            <p class="kpi-card-value">3404.5 h</p>
            <p class="kpi-card-note">tiempo operativo acumulado</p>
        </div>
        <div class="kpi-card kpi-blue">
            <p class="kpi-card-title">NPT / Horas</p>
            <p class="kpi-card-value">2%</p>
            <p class="kpi-card-note">NPT / (horas OPS + horas NPT)</p>
        </div>
        <div class="kpi-card kpi-amber">
            <p class="kpi-card-title">Horas NPT</p>
            <p class="kpi-card-value">68.0 h</p>
            <p class="kpi-card-note">10 eventos / trabajos afectados</p>
        </div>
        <div class="kpi-card kpi-amber">
            <p class="kpi-card-title">Trabajos libres de NPT</p>
            <p class="kpi-card-value">90%</p>
            <p class="kpi-card-note">90 de 100 trabajos</p>
        </div>
    </div>

    <!-- KPIs SECUNDARIOS (Fila 2) -->
    <div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 24px;">
        <div class="kpi-card kpi-white">
            <p class="kpi-card-title">Promedio NPT / evento</p>
            <p class="kpi-card-value">6.8 h</p>
            <p class="kpi-card-note">horas NPT / trabajos con NPT</p>
        </div>
        <div class="kpi-card kpi-white">
            <p class="kpi-card-title">Eventos NPT / 100 corridas</p>
            <p class="kpi-card-value">8.6</p>
            <p class="kpi-card-note">frecuencia normalizada por actividad</p>
        </div>
        <div class="kpi-card kpi-blue">
            <p class="kpi-card-title">Horas OPS entre NPT</p>
            <p class="kpi-card-value">340.4 h</p>
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
                <div class="pie-circle" style="background: conic-gradient(rgb(11, 58, 120) 0%, rgb(11, 58, 120) 90%, rgb(23, 105, 209) 90%, rgb(23, 105, 209) 100%);"></div>
                <div class="pie-legend">
                    <div class="legend-item"><span><span class="legend-color-dot bg-c1"></span><strong>Trabajos sin NPT</strong></span><span><strong>90</strong> · 90%</span></div>
                    <div class="legend-item"><span><span class="legend-color-dot bg-c2"></span><strong>Trabajos con NPT</strong></span><span><strong>10</strong> · 10%</span></div>
                </div>
            </div>
        </div>
        <div class="panel">
            <h2 class="panel-title">Corridas · sin NPT vs con NPT</h2>
            <div class="chart-pie-wrapper">
                <div class="pie-circle" style="background: conic-gradient(rgb(11, 58, 120) 0%, rgb(11, 58, 120) 89.7%, rgb(23, 105, 209) 89.7%, rgb(23, 105, 209) 100%);"></div>
                <div class="pie-legend">
                    <div class="legend-item"><span><span class="legend-color-dot bg-c1"></span><strong>Corridas sin NPT</strong></span><span><strong>104</strong> · 89.7%</span></div>
                    <div class="legend-item"><span><span class="legend-color-dot bg-c2"></span><strong>Corridas con NPT</strong></span><span><strong>12</strong> · 10.3%</span></div>
                </div>
            </div>
            <p style="margin-top: 12px; font-size: 11px; color: #64748b;">En OS históricas sin detalle de corrida, un NPT confirmado se asigna conservadoramente a una sola corrida para no inflar el indicador.</p>
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

    <!-- TABLA TENDENCIA MENSUAL (Movia justo debajo de las gráficas de tendencia) -->
    <div class="panel page-break">
        <h2 class="panel-title">Tendencia mensual de desempeño NPT</h2>
        <div class="table-wrap">
            <table class="npt-table">
                <thead class="table-dark-header">
                    <tr>
                        <th>Mes</th>
                        <th class="text-center">Trabajos</th>
                        <th class="text-center">Con NPT</th>
                        <th class="text-center">NPT / trabajos</th>
                        <th class="text-center">Corridas</th>
                        <th class="text-center">Corridas NPT</th>
                        <th class="text-center">NPT / corridas</th>
                        <th class="text-center">Horas OPS</th>
                        <th class="text-center">Horas NPT</th>
                        <th class="text-center">NPT / horas</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="striped">
                        <td><strong>ene 2026</strong></td>
                        <td class="text-center font-bold">4</td>
                        <td class="text-center">0</td>
                        <td class="text-center font-bold">0%</td>
                        <td class="text-center">6</td>
                        <td class="text-center">0</td>
                        <td class="text-center font-bold">0%</td>
                        <td class="text-center">165.5 h</td>
                        <td class="text-center">0.0 h</td>
                        <td class="text-center font-bold">0%</td>
                    </tr>
                    <tr class="striped">
                        <td><strong>feb 2026</strong></td>
                        <td class="text-center font-bold">9</td>
                        <td class="text-center">0</td>
                        <td class="text-center font-bold">0%</td>
                        <td class="text-center">14</td>
                        <td class="text-center">0</td>
                        <td class="text-center font-bold">0%</td>
                        <td class="text-center">632.5 h</td>
                        <td class="text-center">0.0 h</td>
                        <td class="text-center font-bold">0%</td>
                    </tr>
                    <tr class="striped">
                        <td><strong>mar 2026</strong></td>
                        <td class="text-center font-bold">12</td>
                        <td class="text-center text-danger">1</td>
                        <td class="text-center font-bold">8.3%</td>
                        <td class="text-center">14</td>
                        <td class="text-center text-danger">1</td>
                        <td class="text-center font-bold">7.1%</td>
                        <td class="text-center">667.5 h</td>
                        <td class="text-center text-danger">6.0 h</td>
                        <td class="text-center font-bold">0.9%</td>
                    </tr>
                    <tr class="striped">
                        <td><strong>abr 2026</strong></td>
                        <td class="text-center font-bold">12</td>
                        <td class="text-center text-danger">2</td>
                        <td class="text-center font-bold">16.7%</td>
                        <td class="text-center">12</td>
                        <td class="text-center text-danger">2</td>
                        <td class="text-center font-bold">16.7%</td>
                        <td class="text-center">617.5 h</td>
                        <td class="text-center text-danger">9.0 h</td>
                        <td class="text-center font-bold">1.4%</td>
                    </tr>
                    <tr class="striped">
                        <td><strong>may 2026</strong></td>
                        <td class="text-center font-bold">9</td>
                        <td class="text-center text-danger">3</td>
                        <td class="text-center font-bold">33.3%</td>
                        <td class="text-center">14</td>
                        <td class="text-center text-danger">5</td>
                        <td class="text-center font-bold">35.7%</td>
                        <td class="text-center">399.0 h</td>
                        <td class="text-center text-danger">30.0 h</td>
                        <td class="text-center font-bold">7%</td>
                    </tr>
                    <tr class="striped">
                        <td><strong>jun 2026</strong></td>
                        <td class="text-center font-bold">15</td>
                        <td class="text-center text-danger">1</td>
                        <td class="text-center font-bold">6.7%</td>
                        <td class="text-center">15</td>
                        <td class="text-center text-danger">1</td>
                        <td class="text-center font-bold">6.7%</td>
                        <td class="text-center">922.5 h</td>
                        <td class="text-center text-danger">14.0 h</td>
                        <td class="text-center font-bold">1.5%</td>
                    </tr>
                    <tr class="striped">
                        <td><strong>jul 2026</strong></td>
                        <td class="text-center font-bold">18</td>
                        <td class="text-center text-danger">3</td>
                        <td class="text-center font-bold">16.7%</td>
                        <td class="text-center">18</td>
                        <td class="text-center text-danger">3</td>
                        <td class="text-center font-bold">16.7%</td>
                        <td class="text-center">0.0 h</td>
                        <td class="text-center text-danger">9.0 h</td>
                        <td class="text-center font-bold">100%</td>
                    </tr>
                    <tr class="striped">
                        <td><strong>ago 2026</strong></td>
                        <td class="text-center font-bold">21</td>
                        <td class="text-center">0</td>
                        <td class="text-center font-bold">0%</td>
                        <td class="text-center">23</td>
                        <td class="text-center">0</td>
                        <td class="text-center font-bold">0%</td>
                        <td class="text-center">0.0 h</td>
                        <td class="text-center">0.0 h</td>
                        <td class="text-center font-bold">0%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- GRÁFICAS BARRAS (CLIENTE Y TIPO) -->
    <div class="grid-2-cols">
        <div class="panel">
            <h2 class="panel-title">NPT por cliente</h2>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>SLB</strong> <strong>21 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 100%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>GEOTERMIA</strong> <strong>19.5 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 92.9%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>DIXTONE</strong> <strong>16 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 76.2%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>PERENCO</strong> <strong>7 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 33.3%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>WDEA/H. ENERGY</strong> <strong>4.5 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 21.4%;"></div></div></div>
        </div>
        <div class="panel">
            <h2 class="panel-title">NPT por tipo de servicio</h2>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>DISPARO PUNCHER</strong> <strong>21 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 100%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>COLOCACIÓN DE TAPÓN DE CEMENTO</strong> <strong>18 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 85.7%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>REGISTRO PT</strong> <strong>10.5 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 50%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>REGISTRO PNN/PNG</strong> <strong>6.5 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 31%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>REGISTRO CBL-VDL</strong> <strong>6 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 28.6%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>REGISTRO WI</strong> <strong>3 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 14.3%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>TECNOLOGÍA CORTE MCR + EMA</strong> <strong>3 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 14.3%;"></div></div></div>
        </div>
    </div>

    <!-- GRÁFICAS BARRAS (POZO Y URE) -->
    <div class="grid-2-cols">
        <div class="panel">
            <h2 class="panel-title">NPT por pozo</h2>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>PLATAO 1</strong> <strong>21 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 100%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>SANTUARIO 603 (2 inter)</strong> <strong>14 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 66.7%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>CERRO PRIETO 1-435D (5Inter)</strong> <strong>6 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 28.6%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>CERRO PRIETO 1-435D (9Inter)</strong> <strong>6 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 28.6%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>CERRO PRIETO 1-435D (11 Inter)</strong> <strong>4.5 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 21.4%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>OGARRIO 1517 (1 Inter)</strong> <strong>4.5 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 21.4%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>SANTUARIO 505 (2 INTER)</strong> <strong>4 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 19%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>CERRO PRIETO 1-435D (7Inter)</strong> <strong>3 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 14.3%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>SANTUARIO 304</strong> <strong>3 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 14.3%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>EL GOLPE 41T</strong> <strong>2 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 9.5%;"></div></div></div>
        </div>
        <div class="panel">
            <h2 class="panel-title">NPT por URE / unidad</h2>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>VH-202</strong> <strong>27.5 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 100%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>CABINA 01</strong> <strong>21 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 76.4%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>VH-209</strong> <strong>19.5 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 70.9%;"></div></div></div>
        </div>
    </div>

    <!-- TABLA COMPARATIVO ANUAL -->
    <div class="panel page-break">
        <h2 class="panel-title">Comparativo anual · 2026</h2>
        <div class="table-wrap">
            <table class="npt-table">
                <thead>
                    <tr>
                        <th>Año</th>
                        <th>Trabajos</th>
                        <th>Corridas</th>
                        <th>Horas OPS</th>
                        <th>Horas NPT</th>
                        <th>% NPT</th>
                        <th>Fallas</th>
                        <th>SQIR</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-weight: 900;">2026</td>
                        <td>121</td>
                        <td>138</td>
                        <td style="font-weight: 700;">3623.0 h</td>
                        <td class="text-danger">68.0 h</td>
                        <td style="font-weight: 900;">1.8%</td>
                        <td>10</td>
                        <td style="font-weight: 900;">91.7%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TABLA DE DETALLES NPT -->
    <div class="panel page-break">
        <h2 class="panel-title">Detalle de trabajos con NPT</h2>
        <div class="table-wrap">
            <table class="npt-table">
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
                        <td><strong><a href="#" style="color:#1d4ed8; text-decoration:underline;">VES-WL-OS-1607</a></strong></td>
                        <td>07/07/2026</td>
                        <td>PERENCO</td>
                        <td>SANTUARIO 505 (2 INTER)</td>
                        <td>VH-202</td>
                        <td>COLOCACIÓN DE TAPÓN DE CEMENTO</td>
                        <td><strong>Sin clasificar</strong></td>
                        <td class="text-danger">4.0 h</td>
                        <td>nptHours</td>
                        <td><span class="status-badge">TERMINADO</span></td>
                    </tr>
                    <tr>
                        <td><strong><a href="#" style="color:#1d4ed8; text-decoration:underline;">VES-WL-OS-1603</a></strong></td>
                        <td>05/07/2026</td>
                        <td>PERENCO</td>
                        <td>SANTUARIO 304</td>
                        <td>VH-202</td>
                        <td>SERVICIO DE TECNOLOGÍA CORTE MCR + EMA</td>
                        <td><strong>Sin clasificar</strong></td>
                        <td class="text-danger">3.0 h</td>
                        <td>Tiempo No Productivo NPT</td>
                        <td><span class="status-badge">TERMINADO</span></td>
                    </tr>
                    <tr>
                        <td><strong><a href="#" style="color:#1d4ed8; text-decoration:underline;">VES-WL-OS-1601</a></strong></td>
                        <td>03/07/2026</td>
                        <td>DIXTONE</td>
                        <td>EL GOLPE 41T</td>
                        <td>VH-202</td>
                        <td>REGISTRO PNN/PNG</td>
                        <td><strong>Sin clasificar</strong></td>
                        <td class="text-danger">2.0 h</td>
                        <td>Tiempo No Productivo NPT</td>
                        <td><span class="status-badge">TERMINADO</span></td>
                    </tr>
                    <tr>
                        <td><strong><a href="#" style="color:#1d4ed8; text-decoration:underline;">VES-WL-OS-1587</a></strong></td>
                        <td>20/06/2026</td>
                        <td>DIXTONE</td>
                        <td>SANTUARIO 603 (2 inter)</td>
                        <td>VH-202</td>
                        <td>COLOCACIÓN DE TAPÓN DE CEMENTO</td>
                        <td><strong>Sin clasificar</strong></td>
                        <td class="text-danger">14.0 h</td>
                        <td>Tiempo No Productivo NPT</td>
                        <td><span class="status-badge">TERMINADO</span></td>
                    </tr>
                    <tr>
                        <td><strong><a href="#" style="color:#1d4ed8; text-decoration:underline;">VES-WL-OS-1580</a></strong></td>
                        <td>28/05/2026</td>
                        <td>GEOTERMIA</td>
                        <td>CERRO PRIETO 1- 435D (11 Inter)</td>
                        <td>VH-209</td>
                        <td>REGISTRO PT</td>
                        <td><strong>Sin clasificar</strong></td>
                        <td class="text-danger">4.5 h</td>
                        <td>Tiempo No Productivo NPT</td>
                        <td><span class="status-badge">TERMINADO</span></td>
                    </tr>
                    <tr>
                        <td><strong><a href="#" style="color:#1d4ed8; text-decoration:underline;">VES-WL-OS-1578</a></strong></td>
                        <td>27/05/2026</td>
                        <td>WDEA/H. ENERGY</td>
                        <td>OGARRIO 1517 (1 Inter)</td>
                        <td>VH-202</td>
                        <td>REGISTRO PNN/PNG</td>
                        <td><strong>Sin clasificar</strong></td>
                        <td class="text-danger">4.5 h</td>
                        <td>Tiempo No Productivo NPT</td>
                        <td><span class="status-badge">TERMINADO</span></td>
                    </tr>
                    <tr>
                        <td><strong><a href="#" style="color:#1d4ed8; text-decoration:underline;">VES-WL-OS-1574</a></strong></td>
                        <td>24/05/2026</td>
                        <td>SLB</td>
                        <td>PLATAO 1</td>
                        <td>CABINA 01</td>
                        <td>DISPARO PUNCHER</td>
                        <td><strong>Sin clasificar</strong></td>
                        <td class="text-danger">21.0 h</td>
                        <td>Tiempo No Productivo NPT</td>
                        <td><span class="status-badge">TERMINADO</span></td>
                    </tr>
                    <tr>
                        <td><strong><a href="#" style="color:#1d4ed8; text-decoration:underline;">VES-WL-OS-1568</a></strong></td>
                        <td>30/04/2026</td>
                        <td>GEOTERMIA</td>
                        <td>CERRO PRIETO 1-435D (9Inter)</td>
                        <td>VH-209</td>
                        <td>REGISTRO CBL-VDL</td>
                        <td><strong>Sin clasificar</strong></td>
                        <td class="text-danger">6.0 h</td>
                        <td>Tiempo No Productivo NPT</td>
                        <td><span class="status-badge">TERMINADO</span></td>
                    </tr>
                    <tr>
                        <td><strong><a href="#" style="color:#1d4ed8; text-decoration:underline;">VES-WL-OS-1559</a></strong></td>
                        <td>12/04/2026</td>
                        <td>GEOTERMIA</td>
                        <td>CERRO PRIETO 1-435D (7Inter)</td>
                        <td>VH-209</td>
                        <td>REGISTRO WI</td>
                        <td><strong>Sin clasificar</strong></td>
                        <td class="text-danger">3.0 h</td>
                        <td>Tiempo No Productivo NPT</td>
                        <td><span class="status-badge">TERMINADO</span></td>
                    </tr>
                    <tr>
                        <td><strong><a href="#" style="color:#1d4ed8; text-decoration:underline;">VES-WL-OS-1547</a></strong></td>
                        <td>23/03/2026</td>
                        <td>GEOTERMIA</td>
                        <td>CERRO PRIETO 1-435D (5Inter)</td>
                        <td>VH-209</td>
                        <td>REGISTRO PT</td>
                        <td><strong>Sin clasificar</strong></td>
                        <td class="text-danger">6.0 h</td>
                        <td>Tiempo No Productivo NPT</td>
                        <td><span class="status-badge">TERMINADO</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
