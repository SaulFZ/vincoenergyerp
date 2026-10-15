<div class="tab-executive">

    <!-- BLOQUE: COMPARATIVO AÑO Y TOTALES -->
    <div class="panel year-card" style="padding: 20px;">
        <div class="year-card-header">
            <div>
                <p style="font-size: 11px; font-weight: 900; color: #64748b; letter-spacing: 0.22em; text-transform: uppercase;">Indicadores del año seleccionado</p>
                <h3 style="font-size: 26px; font-weight: 900; color: var(--primary-dark); margin-top:2px;">AÑO 2026</h3>
            </div>
            <div class="sqir-pill">
                <p style="font-size: 10px; opacity: 0.7; text-transform: uppercase; margin-bottom: -2px;">SQIR</p>
                <p style="font-size: 24px; font-weight: 900;">91.7%</p>
            </div>
        </div>

        <div class="kpi-grid mb-3">
            <div class="kpi-card kpi-blue">
                <p class="kpi-card-title">Trabajos realizados</p>
                <p class="kpi-card-value">121</p>
                <p class="kpi-card-note">año 2026</p>
            </div>
            <div class="kpi-card kpi-white">
                <p class="kpi-card-title">Corridas</p>
                <p class="kpi-card-value">138</p>
                <p class="kpi-card-note">total estimado</p>
            </div>
            <div class="kpi-card kpi-blue">
                <p class="kpi-card-title">Horas Operativas</p>
                <p class="kpi-card-value">3623.0 <span style="font-size: 16px;">h</span></p>
                <p class="kpi-card-note">suma operativa</p>
            </div>
            <div class="kpi-card kpi-rose">
                <p class="kpi-card-title">Horas NPT</p>
                <p class="kpi-card-value">68.0 <span style="font-size: 16px;">h</span></p>
                <p class="kpi-card-note">1.8% del total</p>
            </div>
            <div class="kpi-card kpi-amber">
                <p class="kpi-card-title">Fallas / NPT</p>
                <p class="kpi-card-value">10</p>
                <p class="kpi-card-note">servicios con evento</p>
            </div>
            <div class="kpi-card kpi-amber">
                <p class="kpi-card-title">SQIR</p>
                <p class="kpi-card-value">91.7%</p>
                <p class="kpi-card-note">OS sin falla / OS</p>
            </div>
        </div>

        <div class="kpi-grid" style="grid-template-columns: repeat(3, 1fr); margin-top: 10px; border-top: 1px dashed #e2e8f0; padding-top: 16px;">
            <div class="kpi-card kpi-white">
                <p class="kpi-card-title">Servicios operativos</p>
                <p class="kpi-card-value">100</p>
                <p class="kpi-card-note">sin suministros / venta de producto</p>
            </div>
            <div class="kpi-card kpi-amber">
                <p class="kpi-card-title">Suministros MCR</p>
                <p class="kpi-card-value">21</p>
                <p class="kpi-card-note">venta/provisión, sin URE ni registro</p>
            </div>
            <div class="kpi-card kpi-blue">
                <p class="kpi-card-title">OS totales</p>
                <p class="kpi-card-value">121</p>
                <p class="kpi-card-note">servicios operativos + suministros</p>
            </div>
        </div>
    </div>

    <!-- GRÁFICAS EJECUTIVAS: PASTEL 1 Y 2 -->
    <div class="grid-2-cols">
        <div class="panel">
            <h2 class="panel-title">Pastel · Horas Operativas vs NPT</h2>
            <div class="chart-pie-wrapper">
                <div class="pie-circle" style="background: conic-gradient(#0b3a78 0% 98%, #1769d1 98% 100%);"></div>
                <div class="pie-legend">
                    <div class="legend-item">
                        <span><span class="legend-color-dot bg-c1"></span><strong>Horas Ops</strong></span>
                        <span><strong>3404.5</strong> · 98%</span>
                    </div>
                    <div class="legend-item">
                        <span><span class="legend-color-dot bg-c2"></span><strong>Horas NPT</strong></span>
                        <span><strong>68.0</strong> · 2%</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <h2 class="panel-title">Pastel · Trabajos sin falla vs con falla</h2>
            <div class="chart-pie-wrapper">
                <div class="pie-circle" style="background: conic-gradient(#0b3a78 0% 90%, #1769d1 90% 100%);"></div>
                <div class="pie-legend">
                    <div class="legend-item">
                        <span><span class="legend-color-dot bg-c1"></span><strong>Sin falla</strong></span>
                        <span><strong>90</strong> · 90%</span>
                    </div>
                    <div class="legend-item">
                        <span><span class="legend-color-dot bg-c2"></span><strong>Con falla</strong></span>
                        <span><strong>10</strong> · 10%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GRÁFICAS EJECUTIVAS: PASTEL 3 Y 4 -->
    <div class="grid-2-cols">
        <div class="panel">
            <h2 class="panel-title">Pastel · Servicios operativos por tipo</h2>
            <div class="chart-pie-wrapper">
                <div class="pie-circle" style="background: conic-gradient(#0b3a78 0% 29.4%, #1769d1 29.4% 50%, #22c55e 50% 60.3%, #f59e0b 60.3% 69.1%, #e11d48 69.1% 76.5%, #7c3aed 76.5% 82.4%, #0891b2 82.4% 86.8%, #64748b 86.8% 100%);"></div>
                <div class="pie-legend">
                    <div class="legend-item"><span><span class="legend-color-dot bg-c1"></span><strong>PRODUCCIÓN</strong></span><span><strong>20</strong> · 29.4%</span></div>
                    <div class="legend-item"><span><span class="legend-color-dot bg-c2"></span><strong>PNN/PNG</strong></span><span><strong>14</strong> · 20.6%</span></div>
                    <div class="legend-item"><span><span class="legend-color-dot bg-c3"></span><strong>TCP</strong></span><span><strong>7</strong> · 10.3%</span></div>
                    <div class="legend-item"><span><span class="legend-color-dot bg-c4"></span><strong>REGISTRO PT</strong></span><span><strong>6</strong> · 8.8%</span></div>
                </div>
            </div>
        </div>

        <div class="panel">
            <h2 class="panel-title">Pastel · NPT por causa / evento</h2>
            <div class="chart-pie-wrapper">
                <div class="pie-circle" style="background: conic-gradient(#0b3a78 0% 100%, #1769d1 100% 100%);"></div>
                <div class="pie-legend">
                    <div class="legend-item">
                        <span><span class="legend-color-dot bg-c1"></span><strong>Sin clasificar</strong></span>
                        <span><strong>68</strong> · 100%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GRÁFICAS: URE -->
    <div class="grid-2-cols">
        <div class="panel">
            <h2 class="panel-title">Pastel · Uso de URE por horas operativas</h2>
            <div class="chart-pie-wrapper">
                <div class="pie-circle" style="background: conic-gradient(#0b3a78 0% 29.5%, #1769d1 29.5% 46.4%, #22c55e 46.4% 61.5%, #f59e0b 61.5% 71.8%, #e11d48 71.8% 100%);"></div>
                <div class="pie-legend">
                    <div class="legend-item"><span><span class="legend-color-dot bg-c1"></span><strong>VH-202</strong></span><span><strong>1004.5</strong> · 29.5%</span></div>
                    <div class="legend-item"><span><span class="legend-color-dot bg-c2"></span><strong>CABINA 02</strong></span><span><strong>576.5</strong> · 16.9%</span></div>
                    <div class="legend-item"><span><span class="legend-color-dot bg-c3"></span><strong>VH-209</strong></span><span><strong>513.5</strong> · 15.1%</span></div>
                    <div class="legend-item"><span><span class="legend-color-dot bg-c4"></span><strong>SIN URE</strong></span><span><strong>350.0</strong> · 10.3%</span></div>
                </div>
            </div>
        </div>

        <div class="panel">
            <h2 class="panel-title">Horas operativas por URE</h2>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>VH-202</strong> <strong>1004.5 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 100%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>CABINA 02</strong> <strong>576.5 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 57.4%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>VH-209</strong> <strong>513.5 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 51.1%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>SIN URE</strong> <strong>350.0 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 34.8%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>VH-212</strong> <strong>289.5 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 28.8%;"></div></div></div>
        </div>
    </div>

    <!-- GRÁFICAS: MES -->
    <div class="grid-2-cols page-break">
        <div class="panel">
            <h2 class="panel-title">Horas operativas por mes</h2>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>junio</strong> <strong>922.5 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 100%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>marzo</strong> <strong>667.5 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 72.4%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>febrero</strong> <strong>632.5 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 68.6%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>abril</strong> <strong>617.5 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 66.9%;"></div></div></div>
        </div>

        <div class="panel">
            <h2 class="panel-title">Horas NPT por mes</h2>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>mayo</strong> <strong>30.0 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 100%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>junio</strong> <strong>14.0 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 46.7%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>abril</strong> <strong>9.0 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 30%;"></div></div></div>
            <div class="chart-bar-item"><div class="chart-bar-labels"><strong>julio</strong> <strong>9.0 h</strong></div><div class="chart-bar-track"><div class="chart-bar-fill bg-c1" style="width: 30%;"></div></div></div>
        </div>
    </div>

    <!-- TABLA DE NPT -->
    <div class="panel page-break">
        <h2 class="panel-title">Tabla de servicios con falla / NPT</h2>
        <div class="table-wrap">
            <table class="npt-table">
                <thead>
                    <tr>
                        <th>OS</th><th>Fecha</th><th>Cliente</th><th>Pozo</th><th>URE</th><th>Servicio</th><th>Tipo evento</th><th>NPT</th><th>Origen</th><th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>VES-WL-OS-1607</strong></td><td>07/07/2026</td><td>PERENCO</td><td>SANTUARIO 505</td><td>VH-202</td><td>COLOCACIÓN DE TAPÓN</td><td><strong>Sin clasificar</strong></td><td style="color: #dc2626; font-weight: 900;">4.0 h</td><td>nptHours</td><td><span class="status-badge">TERMINADO</span></td>
                    </tr>
                    <tr>
                        <td><strong>VES-WL-OS-1603</strong></td><td>05/07/2026</td><td>PERENCO</td><td>SANTUARIO 304</td><td>VH-202</td><td>TECNOLOGÍA CORTE MCR</td><td><strong>Sin clasificar</strong></td><td style="color: #dc2626; font-weight: 900;">3.0 h</td><td>Tiempo NPT</td><td><span class="status-badge">TERMINADO</span></td>
                    </tr>
                    <tr>
                        <td><strong>VES-WL-OS-1601</strong></td><td>03/07/2026</td><td>DIXTONE</td><td>EL GOLPE 41T</td><td>VH-202</td><td>REGISTRO PNN/PNG</td><td><strong>Sin clasificar</strong></td><td style="color: #dc2626; font-weight: 900;">2.0 h</td><td>Tiempo NPT</td><td><span class="status-badge">TERMINADO</span></td>
                    </tr>
                    <tr>
                        <td><strong>VES-WL-OS-1587</strong></td><td>20/06/2026</td><td>DIXTONE</td><td>SANTUARIO 603</td><td>VH-202</td><td>COLOCACIÓN DE TAPÓN</td><td><strong>Sin clasificar</strong></td><td style="color: #dc2626; font-weight: 900;">14.0 h</td><td>Tiempo NPT</td><td><span class="status-badge">TERMINADO</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
