<!-- ========================================== -->
<!-- 1. FILTROS EXCLUSIVOS DE LA VISTA: EXECUTIVE -->
<!-- ========================================== -->
<section class="filter-section no-print">
    <form id="filter-form" action="{{ route('operations.sgc.index') }}" method="GET">
        <div class="filter-grid">

            <div class="filter-group">
                <label class="filter-label">Módulo</label>
                <select name="view" class="filter-input">
                    <!-- Seleccionado por defecto 'executive' -->
                    <option value="executive" selected>Dashboard Ejecutivo</option>
                    <option value="npt">NPT / No Productivo</option>
                    <option value="compliance">Cumplimiento SGC</option>
                    <option value="crew">Cuadrilla + Satisfacción</option>
                    <option value="shots">Disparos / MCR</option>
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

            <div class="filter-group custom-dropdown">
                <label class="filter-label">Años</label>
                <div class="filter-input dropdown-trigger" id="year-dropdown-btn">
                    <span>Seleccionar años</span>
                    <i class="fas fa-chevron-down text-slate-400"></i>
                </div>
                <div class="dropdown-menu" id="year-dropdown-menu">
                    <label class="dropdown-item"><input type="checkbox" name="year[]" value="2026" checked> 2026</label>
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
                <label class="filter-label">Mes comp.</label>
                <select name="compareMonth" class="filter-input">
                    <option value="TODOS" selected>Todos los meses</option>
                    <option value="ACTUAL">Mes actual vs años</option>
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
                    <!-- El botón de limpiar apunta a la ruta general con la vista executive -->
                    <a href="{{ route('operations.sgc.index') }}?view=executive" class="btn-icon btn-clear" title="Limpiar filtros">
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
<div class="tab-executive">

    <!-- BLOQUE: COMPARATIVO AÑO Y TOTALES -->
    <div class="panel year-card">
        <div class="year-card-header">
            <div>
                <p class="module-subtitle">Indicadores del año seleccionado</p>
                <h3 class="module-title">AÑO 2026</h3>
            </div>
            <div class="sqir-pill">
                <p class="sqir-pill-label">SQIR</p>
                <p class="sqir-pill-value">91.7%</p>
            </div>
        </div>

        <div class="kpi-grid">
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
                <p class="kpi-card-value">3623.0 <span class="kpi-card-value-small">h</span></p>
                <p class="kpi-card-note">suma operativa</p>
            </div>
            <div class="kpi-card kpi-rose">
                <p class="kpi-card-title">Horas NPT</p>
                <p class="kpi-card-value">68.0 <span class="kpi-card-value-small">h</span></p>
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

        <div class="kpi-grid kpi-grid-three">
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

</div>
