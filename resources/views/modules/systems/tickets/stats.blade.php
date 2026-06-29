@extends('modules.systems.tickets.index')

@section('content')
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=IBM+Plex+Mono:wght@500;700&display=swap"
        rel="stylesheet">

    <style>
        /* ══════════════════════════════════════════════
       TOKENS DE COLOR
    ══════════════════════════════════════════════ */
        :root {
            --bg: #f1f5f9;
            --surface: #ffffff;
            --surface2: #f8fafc;
            --border: #e2e8f0;
            --border2: #cbd5e1;
            --primary: #0f172a;
            --primary-mid: #334155;
            --accent: #0f766e;
            --text: #0f172a;
            --muted: #475569;
            --muted2: #64748b;

            --s-new-border: #2e6db4;
            --s-new-bg: #d4e4f7;
            --s-new-text: #0c2d5e;
            --s-open-border: #1a8fa3;
            --s-open-bg: #c8edf3;
            --s-open-text: #0b4a52;
            --s-wait-border: #c15a00;
            --s-wait-bg: #fddbc7;
            --s-wait-text: #7a2c00;
            --s-pend-border: #c47d00;
            --s-pend-bg: #fde8ab;
            --s-pend-text: #6b3b00;
            --s-done-border: #1a8c38;
            --s-done-bg: #bbf0cc;
            --s-done-text: #0d4a1e;
            --s-cancel-border: #b91c1c;
            --s-cancel-bg: #fcc8c8;
            --s-cancel-text: #6b0c0c;

            --radius: 12px;
            --shadow-sm: 0 2px 8px rgba(15, 23, 42, .04);
            --shadow-md: 0 8px 24px rgba(15, 23, 42, .08);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* ── LAYOUT RAÍZ ─────────────────────────────────────────── */
        .sd-root {
            background: var(--bg);
            min-height: calc(100vh - 120px);
            font-family: 'Inter', sans-serif;
            padding: 24px;
            position: relative;
            border-radius: var(--radius);
            overflow: hidden;
        }

        /* ── OVERLAY ─────────────────────────────────────────────── */
        .sd-overlay {
            position: absolute;
            inset: 0;
            background: rgba(248, 250, 252, 0.85);
            backdrop-filter: blur(4px);
            z-index: 50;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
            transition: opacity .4s, visibility .4s;
            border-radius: var(--radius);
        }

        .sd-overlay.hide {
            opacity: 0;
            visibility: hidden;
        }

        .sd-spinner {
            width: 42px;
            height: 42px;
            border: 3px solid var(--border2);
            border-top-color: var(--accent);
            border-radius: 50%;
            animation: spin .8s cubic-bezier(.4, 0, .2, 1) infinite;
        }

        .sd-overlay p {
            font-size: .85rem;
            font-weight: 600;
            color: var(--primary-mid);
            letter-spacing: .02em;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ── KPI STRIP ───────────────────────────────────────────── */
        .kpi-strip {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }

        .kpi-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            min-height: 90px;
            box-shadow: var(--shadow-sm);
            transition: transform .25s, box-shadow .25s;
            position: relative;
            overflow: hidden;
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            border-radius: 0 0 var(--radius) var(--radius);
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .kpi-card.c-nuevo::before {
            background: var(--s-new-border);
        }

        .kpi-card.c-abierto::before {
            background: var(--s-open-border);
        }

        .kpi-card.c-espera::before {
            background: var(--s-wait-border);
        }

        .kpi-card.c-concluir::before {
            background: var(--s-pend-border);
        }

        .kpi-card.c-realizado::before {
            background: var(--s-done-border);
        }

        .kpi-card.c-cancelado::before {
            background: var(--s-cancel-border);
        }

        .kpi-info {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .kpi-label {
            font-size: .67rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--muted);
        }

        .kpi-val {
            font-size: 1.9rem;
            font-weight: 800;
            color: var(--text);
            line-height: 1;
            font-family: 'IBM Plex Mono', monospace;
        }

        .kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .kpi-card.c-nuevo .kpi-icon {
            background: var(--s-new-bg);
            color: var(--s-new-border);
        }

        .kpi-card.c-abierto .kpi-icon {
            background: var(--s-open-bg);
            color: var(--s-open-border);
        }

        .kpi-card.c-espera .kpi-icon {
            background: var(--s-wait-bg);
            color: var(--s-wait-border);
        }

        .kpi-card.c-concluir .kpi-icon {
            background: var(--s-pend-bg);
            color: var(--s-pend-border);
        }

        .kpi-card.c-realizado .kpi-icon {
            background: var(--s-done-bg);
            color: var(--s-done-border);
        }

        .kpi-card.c-cancelado .kpi-icon {
            background: var(--s-cancel-bg);
            color: var(--s-cancel-border);
        }

        /* ── CARD GENÉRICA ───────────────────────────────────────── */
        .sd-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            transition: box-shadow .2s;
            margin-bottom: 16px;
        }

        .sd-card:hover {
            box-shadow: var(--shadow-md);
        }

        /* ── TOOLBAR (filtros + botón dentro de la card) ─────────── */
        .sd-toolbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            padding-bottom: 16px;
            border-bottom: 1px dashed var(--border);
            margin-bottom: 20px;
        }

        .sd-toolbar-meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .sd-toolbar-controls {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .sd-card-title {
            font-size: .95rem;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 8px;
            line-height: 1.2;
        }

        .sd-card-title i {
            color: var(--accent);
            font-size: .9rem;
        }

        .sd-card-desc {
            font-size: .75rem;
            color: var(--muted);
            margin-top: 2px;
            font-weight: 500;
        }

        /* ── FILTROS ─────────────────────────────────────────────── */
        .filter-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-item label {
            font-size: .73rem;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
        }

        .filter-item select {
            border: 1px solid var(--border2);
            border-radius: 6px;
            padding: 8px 12px;
            font-family: 'Inter', sans-serif;
            font-size: .8rem;
            font-weight: 500;
            color: var(--text);
            background: var(--surface);
            outline: none;
            transition: border-color .2s;
            min-width: 150px;
        }

        .filter-item select:focus {
            border-color: var(--accent);
        }

        /* ── BOTÓN ACTUALIZAR ────────────────────────────────────── */
        .sd-refresh-btn {
            background: var(--primary);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: .85rem;
            font-weight: 600;
            color: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all .2s;
            box-shadow: 0 4px 12px rgba(15, 23, 42, .15);
            white-space: nowrap;
        }

        .sd-refresh-btn:hover {
            background: var(--primary-mid);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(15, 23, 42, .2);
        }

        .sd-refresh-btn.loading i {
            animation: spin .8s linear infinite;
        }

        /* ── TABLA ───────────────────────────────────────────────── */
        .rank-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: .8rem;
        }

        .rank-table thead th {
            background: var(--surface2);
            color: var(--muted);
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            padding: 12px;
            border-bottom: 1px solid var(--border);
            text-align: center;
        }

        .rank-table thead th:first-child {
            border-radius: 8px 0 0 0;
        }

        .rank-table thead th:last-child {
            border-radius: 0 8px 0 0;
        }

        .rank-table tbody td {
            padding: 12px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
            vertical-align: middle;
            text-align: center;
        }

        .rank-table tbody tr:last-child td {
            border-bottom: none;
        }

        .rank-table tbody tr:hover td {
            background: var(--surface2);
        }

        .rank-num {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .8rem;
            color: var(--muted2);
            font-weight: 700;
        }

        .stag-mini {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 5px;
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
        }

        .stag-mini.alta {
            background: var(--s-cancel-bg);
            color: var(--s-cancel-text);
            border: 1px solid var(--s-cancel-border);
        }

        .stag-mini.media {
            background: var(--s-pend-bg);
            color: var(--s-pend-text);
            border: 1px solid var(--s-pend-border);
        }

        .stag-mini.baja {
            background: var(--s-done-bg);
            color: var(--s-done-text);
            border: 1px solid var(--s-done-border);
        }

        /* ── GRID DE GRÁFICAS ────────────────────────────────────── */
        .charts-grid {
            display: grid;
            gap: 16px;
            margin-bottom: 16px;
        }

        .cols-2 {
            grid-template-columns: 1fr 1fr;
        }

        .cols-1 {
            grid-template-columns: 1fr;
        }

        .chart-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            transition: box-shadow .2s;
        }

        .chart-card:hover {
            box-shadow: var(--shadow-md);
        }

        .chart-card-header {
            padding-bottom: 12px;
            border-bottom: 1px dashed var(--border);
            margin-bottom: 16px;
        }

        .chart-wrap {
            flex: 1;
            min-height: 0;
            position: relative;
            padding-top: 4px;
        }

        .h-300 .chart-wrap {
            height: 300px;
        }

        .h-340 .chart-wrap {
            height: 340px;
        }

        .h-360 .chart-wrap {
            height: 360px;
        }

        /* ── LEYENDA PASTEL ──────────────────────────────────────── */
        .pie-legend {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 0 4px;
            justify-content: center;
        }

        .pie-legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .78rem;
            font-weight: 500;
            color: var(--primary-mid);
        }

        .pie-legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .pie-legend-pct {
            margin-left: auto;
            font-family: 'IBM Plex Mono', monospace;
            font-size: .75rem;
            font-weight: 700;
            color: var(--muted);
        }

        /* ── RESPONSIVE ──────────────────────────────────────────── */
        @media (max-width: 1280px) {
            .kpi-strip {
                grid-template-columns: repeat(3, 1fr);
            }

            .sd-toolbar {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 900px) {
            .kpi-strip {
                grid-template-columns: repeat(2, 1fr);
            }

            .cols-2 {
                grid-template-columns: 1fr;
            }

            .sd-root {
                padding: 16px;
            }
        }

        @media (max-width: 520px) {
            .kpi-strip {
                grid-template-columns: 1fr;
            }

            .sd-toolbar-controls {
                flex-direction: column;
                align-items: flex-start;
                width: 100%;
            }

            .filter-item,
            .filter-item select {
                width: 100%;
            }

            .sd-refresh-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <div class="sd-root content active">

        {{-- ── OVERLAY ── --}}
        <div class="sd-overlay" id="sdOverlay">
            <div class="sd-spinner"></div>
            <p>Procesando y sincronizando métricas...</p>
        </div>

        {{-- ══════════════════════════════════════════════
         KPIs
    ══════════════════════════════════════════════ --}}
        <div class="kpi-strip">
            <div class="kpi-card c-nuevo">
                <div class="kpi-info">
                    <span class="kpi-label">Nuevo</span>
                    <span class="kpi-val" id="kpi-new">0</span>
                </div>
                <div class="kpi-icon"><i class="fas fa-inbox"></i></div>
            </div>
            <div class="kpi-card c-abierto">
                <div class="kpi-info">
                    <span class="kpi-label">Abierto</span>
                    <span class="kpi-val" id="kpi-open">0</span>
                </div>
                <div class="kpi-icon"><i class="fas fa-folder-open"></i></div>
            </div>
            <div class="kpi-card c-espera">
                <div class="kpi-info">
                    <span class="kpi-label">En Espera</span>
                    <span class="kpi-val" id="kpi-wait">0</span>
                </div>
                <div class="kpi-icon"><i class="fas fa-clock"></i></div>
            </div>
            <div class="kpi-card c-concluir">
                <div class="kpi-info">
                    <span class="kpi-label">Por Concluir</span>
                    <span class="kpi-val" id="kpi-pend">0</span>
                </div>
                <div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
            <div class="kpi-card c-realizado">
                <div class="kpi-info">
                    <span class="kpi-label">Realizados</span>
                    <span class="kpi-val" id="kpi-done">0</span>
                </div>
                <div class="kpi-icon"><i class="fas fa-check-double"></i></div>
            </div>
            <div class="kpi-card c-cancelado">
                <div class="kpi-info">
                    <span class="kpi-label">Cancelados</span>
                    <span class="kpi-val" id="kpi-cancel">0</span>
                </div>
                <div class="kpi-icon"><i class="fas fa-ban"></i></div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════
         CARD UNIFICADA: FILTROS + TABLA
         Los filtros viven dentro de esta misma card
         junto con la tabla de detalle operativo.
    ══════════════════════════════════════════════ --}}
        <div class="sd-card">

            {{-- Toolbar: título + controles de filtrado --}}
            <div class="sd-toolbar">
                <div class="sd-toolbar-meta">
                    <div class="sd-card-title">
                        <i class="fas fa-table"></i> Detalle Operativo por Departamento
                    </div>
                    <div class="sd-card-desc">Análisis exhaustivo del desempeño y flujo de trabajo por área corporativa
                    </div>
                </div>

                <div class="sd-toolbar-controls">
                    <div class="filter-item">
                        <label><i class="fas fa-building"></i> Departamento:</label>
                        <select id="filterDept">
                            <option value="all">Todos los departamentos</option>
                            <option value="sistemas">Sistemas</option>
                            <option value="operaciones">Operaciones</option>
                            <option value="geociencias">Geociencias</option>
                            <option value="rrhh">Recursos Humanos</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label><i class="far fa-calendar-alt"></i> Periodo:</label>
                        <select id="filterPeriod">
                            <option value="month">Este Mes</option>
                            <option value="quarter">Este Trimestre</option>
                            <option value="year" selected>Año Actual</option>
                            <option value="all">Histórico Completo</option>
                        </select>
                    </div>
                    <button class="sd-refresh-btn" id="btnRefresh">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </div>

            {{-- Tabla de departamentos --}}
            <div style="overflow-x: auto;">
                <table class="rank-table" id="tablaDept">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th style="text-align:left;">Departamento</th>
                            <th>Nuevos</th>
                            <th>Abiertos</th>
                            <th>En Espera</th>
                            <th>Por Concluir</th>
                            <th>Realizados</th>
                            <th>Cancelados</th>
                            <th>Total</th>
                            <th>P. Alta</th>
                            <th>P. Media</th>
                            <th>P. Baja</th>
                        </tr>
                    </thead>
                    <tbody id="tablaDeptBody">
                        <tr>
                            <td colspan="12" style="text-align:center;padding:30px;color:var(--muted2);">
                                Calculando matrices de datos...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>{{-- /sd-card (filtros + tabla) --}}

        {{-- ══════════════════════════════════════════════
         GRÁFICAS (solo las 5 solicitadas)
    ══════════════════════════════════════════════ --}}

        {{-- Fila A: Barras por agente  +  Donut tipo incidencia --}}
        <div class="charts-grid cols-2 h-360">
            <div class="chart-card">
                <div class="chart-card-header">
                    <div class="sd-card-title"><i class="fas fa-user-check"></i> Tickets Atendidos por Agente</div>
                    <div class="sd-card-desc" style="margin-top:4px;">Comparativa de tickets asignados vs. resueltos por
                        miembro del equipo en el periodo</div>
                </div>
                <div class="chart-wrap">
                    <div id="chartAgentes" style="width:100%;height:100%;"></div>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-card-header">
                    <div class="sd-card-title"><i class="fas fa-chart-pie"></i> Distribución por Tipo de Incidencia</div>
                    <div class="sd-card-desc" style="margin-top:4px;">Proporción de tickets por categoría: soporte, acceso,
                        hardware, software y red</div>
                </div>
                <div class="chart-wrap" style="display:flex;align-items:center;gap:16px;">
                    <div id="chartPie" style="flex:1;height:100%;min-width:0;"></div>
                    <div class="pie-legend" id="pieLegend" style="width:155px;flex-shrink:0;"></div>
                </div>
            </div>
        </div>

        {{-- Fila B: Gráfica de líneas — evolución temporal (full width) --}}
        <div class="charts-grid cols-1 h-300">
            <div class="chart-card">
                <div class="chart-card-header">
                    <div class="sd-card-title"><i class="fas fa-chart-line"></i> Evolución Semanal: Volumen de Tickets y
                        Tiempo Promedio de Resolución</div>
                    <div class="sd-card-desc" style="margin-top:4px;">Eje izquierdo — barras teal: volumen semanal · Eje
                        derecho — línea naranja: tiempo medio de cierre (horas)</div>
                </div>
                <div class="chart-wrap">
                    <div id="chartTendencia" style="width:100%;height:100%;"></div>
                </div>
            </div>
        </div>

        {{-- Fila C: Histograma  +  Burndown --}}
        <div class="charts-grid cols-2 h-300">
            <div class="chart-card">
                <div class="chart-card-header">
                    <div class="sd-card-title"><i class="fas fa-stopwatch"></i> Histograma de Tiempos de Resolución</div>
                    <div class="sd-card-desc" style="margin-top:4px;">Cantidad de tickets cerrados según el rango de
                        tiempo invertido hasta su resolución</div>
                </div>
                <div class="chart-wrap">
                    <div id="chartHistograma" style="width:100%;height:100%;"></div>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-card-header">
                    <div class="sd-card-title"><i class="fas fa-fire-alt"></i> Burndown de Backlog</div>
                    <div class="sd-card-desc" style="margin-top:4px;">Reducción progresiva de tickets pendientes vs.
                        proyección ideal del sprint de soporte</div>
                </div>
                <div class="chart-wrap">
                    <div id="chartBurndown" style="width:100%;height:100%;"></div>
                </div>
            </div>
        </div>

    </div>{{-- /sd-root --}}
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/plotly.js/2.27.1/plotly.min.js"></script>

    <script>
        /* ══════════════════════════════════════════════
       CONFIGURACIÓN GLOBAL PLOTLY
    ══════════════════════════════════════════════ */
        const PLT_CFG = {
            responsive: true,
            displayModeBar: false
        };
        const PLT_FONT = {
            family: 'Inter, sans-serif',
            color: '#64748b',
            size: 11
        };
        const PLT_BASE = {
            paper_bgcolor: 'transparent',
            plot_bgcolor: 'transparent',
            font: PLT_FONT,
            margin: {
                t: 8,
                r: 10,
                b: 36,
                l: 48
            },
            hoverlabel: {
                bgcolor: '#0f172a',
                bordercolor: '#cbd5e1',
                font: {
                    color: '#fff',
                    size: 12
                }
            },
        };
        const AXIS_BASE = {
            gridcolor: 'rgba(15,23,42,.04)',
            linecolor: 'rgba(15,23,42,.08)',
            tickfont: {
                color: '#64748b',
                size: 10
            },
            zerolinecolor: 'rgba(15,23,42,.05)',
        };

        function ax(extra = {}) {
            return {
                ...AXIS_BASE,
                ...extra
            };
        }

        /* ── Paleta de estados (homologada con el CSS) ── */
        const ST = {
            nuevo: '#2e6db4',
            abierto: '#1a8fa3',
            espera: '#c15a00',
            concluir: '#c47d00',
            realizado: '#1a8c38',
            cancelado: '#b91c1c',
        };
        const PAL_PIE = [ST.nuevo, ST.abierto, ST.espera, ST.concluir, ST.realizado, '#94a3b8'];

        /* ══════════════════════════════════════════════
           CARGA DE DATOS
        ══════════════════════════════════════════════ */
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const H = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json'
        };

        async function loadData() {
            const overlay = document.getElementById('sdOverlay');
            overlay.classList.remove('hide');
            try {
                const r = await fetch('{{ route('systems.tickets.stats.data') }}', {
                    headers: H
                });
                if (!r.ok) throw new Error('HTTP ' + r.status);
                renderAll(await r.json());
            } catch (e) {
                console.warn('Datos simulados.', e);
                renderAll(buildDemoData());
            } finally {
                setTimeout(() => overlay.classList.add('hide'), 400);
            }
        }

        /* ══════════════════════════════════════════════
           DATOS SIMULADOS
        ══════════════════════════════════════════════ */
        function buildDemoData() {
            const depts = ['Geociencias', 'Sistemas', 'Operaciones', 'Mantenimiento', 'Recursos Humanos', 'Contabilidad',
                'Dirección'
            ];

            const detail = depts.map(name => {
                const t = Math.round(40 + Math.random() * 150);
                return {
                    name,
                    total: t,
                    nuevo: Math.round(t * .10),
                    abierto: Math.round(t * .15),
                    espera: Math.round(t * .08),
                    concluir: Math.round(t * .12),
                    realizado: Math.round(t * .50),
                    cancelado: Math.round(t * .05),
                    alta: Math.round(t * .20),
                    media: Math.round(t * .50),
                    baja: Math.round(t * .30),
                };
            }).sort((a, b) => b.total - a.total);

            /* Agentes */
            const agentes = ['Carlos M.', 'Laura R.', 'Miguel S.', 'Ana T.', 'José F.', 'Diana C.', 'Pedro V.'].map(
                nombre => {
                    const asignados = Math.round(40 + Math.random() * 90);
                    return {
                        nombre,
                        asignados,
                        resueltos: Math.round(asignados * (.60 + Math.random() * .35))
                    };
                });

            /* Tipos de incidencia */
            const tiposIncidencia = {
                labels: ['Soporte Técnico', 'Acceso / Permisos', 'Hardware', 'Software', 'Red / Conectividad', 'Otros'],
                values: [
                    Math.round(200 + Math.random() * 100),
                    Math.round(100 + Math.random() * 80),
                    Math.round(60 + Math.random() * 50),
                    Math.round(140 + Math.random() * 70),
                    Math.round(55 + Math.random() * 40),
                    Math.round(25 + Math.random() * 30),
                ],
            };

            /* Tendencia semanal */
            const semanas = Array.from({
                length: 12
            }, (_, i) => `Sem ${i + 1}`);
            const tendencia = {
                semanas,
                volumen: semanas.map(() => Math.round(55 + Math.random() * 90)),
                tiempo_promedio: semanas.map(() => parseFloat((18 + Math.random() * 38).toFixed(1))),
            };

            /* Histograma */
            const histograma = {
                rangos: ['< 4 h', '4–12 h', '12–24 h', '1–2 días', '2–3 días', '3–5 días', '> 5 días'],
                conteo: [
                    Math.round(75 + Math.random() * 60),
                    Math.round(130 + Math.random() * 80),
                    Math.round(190 + Math.random() * 100),
                    Math.round(150 + Math.random() * 70),
                    Math.round(80 + Math.random() * 50),
                    Math.round(40 + Math.random() * 30),
                    Math.round(15 + Math.random() * 20),
                ],
            };

            /* Burndown (sprint 20 días) */
            const sprintDays = 20,
                startBacklog = 300;
            let rem = startBacklog;
            const burndown = {
                dias: ['Inicio', ...Array.from({
                    length: sprintDays
                }, (_, i) => `Día ${i + 1}`)],
                ideal: Array.from({
                    length: sprintDays + 1
                }, (_, i) => Math.round(startBacklog * (1 - i / sprintDays))),
                real: Array.from({
                    length: sprintDays + 1
                }, (_, i) => {
                    if (i === 0) return startBacklog;
                    rem = Math.max(0, rem - Math.round((startBacklog / sprintDays) * (.5 + Math.random())));
                    return rem;
                }),
            };

            return {
                kpis: {
                    nuevo: detail.reduce((s, d) => s + d.nuevo, 0),
                    abierto: detail.reduce((s, d) => s + d.abierto, 0),
                    espera: detail.reduce((s, d) => s + d.espera, 0),
                    concluir: detail.reduce((s, d) => s + d.concluir, 0),
                    realizado: detail.reduce((s, d) => s + d.realizado, 0),
                    cancelado: detail.reduce((s, d) => s + d.cancelado, 0),
                },
                departments_detail: detail,
                agentes,
                tiposIncidencia,
                tendencia,
                histograma,
                burndown,
            };
        }

        /* ══════════════════════════════════════════════
           ORQUESTADOR
        ══════════════════════════════════════════════ */
        function renderAll(data) {
            fillKPIs(data.kpis);
            renderTabla(data.departments_detail);
            chartAgentes(data.agentes);
            chartPie(data.tiposIncidencia);
            chartTendencia(data.tendencia);
            chartHistograma(data.histograma);
            chartBurndown(data.burndown);
        }

        /* ── KPIs ────────────────────────────────────────────────── */
        function fillKPIs(k) {
            document.getElementById('kpi-new').innerText = k.nuevo ?? 0;
            document.getElementById('kpi-open').innerText = k.abierto ?? 0;
            document.getElementById('kpi-wait').innerText = k.espera ?? 0;
            document.getElementById('kpi-pend').innerText = k.concluir ?? 0;
            document.getElementById('kpi-done').innerText = k.realizado ?? 0;
            document.getElementById('kpi-cancel').innerText = k.cancelado ?? 0;
        }

        /* ── TABLA ───────────────────────────────────────────────── */
        function renderTabla(depts) {
            if (!depts?.length) return;
            document.getElementById('tablaDeptBody').innerHTML = depts.map((d, i) => `
        <tr>
            <td class="rank-num">${i + 1}</td>
            <td style="font-weight:700;color:var(--primary);text-align:left;">${d.name}</td>
            <td style="color:${ST.nuevo};font-weight:600;">${d.nuevo}</td>
            <td style="color:${ST.abierto};">${d.abierto}</td>
            <td style="color:${ST.espera};">${d.espera}</td>
            <td style="color:${ST.concluir};font-weight:600;">${d.concluir}</td>
            <td style="color:${ST.realizado};font-weight:700;">${d.realizado}</td>
            <td style="color:${ST.cancelado};">${d.cancelado}</td>
            <td style="font-family:'IBM Plex Mono',monospace;font-weight:800;font-size:.9rem;">${d.total}</td>
            <td><span class="stag-mini alta">${d.alta}</span></td>
            <td><span class="stag-mini media">${d.media}</span></td>
            <td><span class="stag-mini baja">${d.baja}</span></td>
        </tr>`).join('');
        }

        /* ══════════════════════════════════════════════
           GRÁFICA 1 — BARRAS HORIZONTALES AGRUPADAS
           Tickets asignados vs. resueltos por agente
        ══════════════════════════════════════════════ */
        function chartAgentes(agentes) {
            if (!agentes?.length) return;
            const sorted = [...agentes].sort((a, b) => b.asignados - a.asignados);
            const names = sorted.map(a => a.nombre).reverse();
            const assigned = sorted.map(a => a.asignados).reverse();
            const resolved = sorted.map(a => a.resueltos).reverse();

            Plotly.newPlot('chartAgentes', [{
                    y: names,
                    x: assigned,
                    name: 'Asignados',
                    type: 'bar',
                    orientation: 'h',
                    marker: {
                        color: '#94a3b8',
                        opacity: .80
                    },
                    hovertemplate: '<b>%{y}</b><br>Asignados: %{x}<extra></extra>',
                },
                {
                    y: names,
                    x: resolved,
                    name: 'Resueltos',
                    type: 'bar',
                    orientation: 'h',
                    marker: {
                        color: '#1a8c38',
                        opacity: .95
                    },
                    hovertemplate: '<b>%{y}</b><br>Resueltos: %{x}<extra></extra>',
                },
            ], {
                ...PLT_BASE,
                barmode: 'group',
                margin: {
                    t: 8,
                    r: 40,
                    b: 36,
                    l: 100
                },
                xaxis: ax(),
                yaxis: ax({
                    automargin: true,
                    tickfont: {
                        size: 11,
                        color: '#0f172a'
                    }
                }),
                legend: {
                    orientation: 'h',
                    y: -0.14,
                    font: {
                        size: 11,
                        color: '#475569'
                    }
                },
            }, PLT_CFG);
        }

        /* ══════════════════════════════════════════════
           GRÁFICA 2 — DONUT + LEYENDA HTML
           Distribución por tipo de incidencia
        ══════════════════════════════════════════════ */
        function chartPie(tipos) {
            if (!tipos) return;
            const total = tipos.values.reduce((s, v) => s + v, 0);

            Plotly.newPlot('chartPie', [{
                labels: tipos.labels,
                values: tipos.values,
                type: 'pie',
                hole: 0.52,
                marker: {
                    colors: PAL_PIE,
                    line: {
                        color: '#fff',
                        width: 2.5
                    }
                },
                textinfo: 'none',
                hovertemplate: '<b>%{label}</b><br>%{value} tickets (%{percent})<extra></extra>',
                direction: 'clockwise',
                sort: false,
            }], {
                ...PLT_BASE,
                margin: {
                    t: 8,
                    r: 8,
                    b: 8,
                    l: 8
                },
                showlegend: false,
                annotations: [{
                    text: `<b>${total}</b>`,
                    font: {
                        family: 'IBM Plex Mono, monospace',
                        size: 22,
                        color: '#0f172a'
                    },
                    showarrow: false,
                    x: 0.5,
                    y: 0.5,
                    xanchor: 'center',
                    yanchor: 'middle',
                }],
            }, PLT_CFG);

            /* Leyenda construida en HTML para control total del layout */
            const el = document.getElementById('pieLegend');
            if (el) {
                el.innerHTML = tipos.labels.map((label, i) => {
                    const pct = ((tipos.values[i] / total) * 100).toFixed(1);
                    return `<div class="pie-legend-item">
                        <span class="pie-legend-dot" style="background:${PAL_PIE[i]};"></span>
                        <span>${label}</span>
                        <span class="pie-legend-pct">${pct}%</span>
                    </div>`;
                }).join('');
            }
        }

        /* ══════════════════════════════════════════════
           GRÁFICA 3 — LÍNEAS CON DOBLE EJE Y
           Volumen semanal (barras) + Tiempo prom. resolución (línea)
        ══════════════════════════════════════════════ */
        function chartTendencia(t) {
            if (!t) return;
            Plotly.newPlot('chartTendencia', [{
                    x: t.semanas,
                    y: t.volumen,
                    name: 'Volumen de Tickets',
                    type: 'bar',
                    marker: {
                        color: '#0f766e',
                        opacity: .85
                    },
                    hovertemplate: '<b>%{x}</b><br>Volumen: %{y} tickets<extra></extra>',
                },
                {
                    x: t.semanas,
                    y: t.tiempo_promedio,
                    name: 'Tiempo Prom. Resolución (h)',
                    type: 'scatter',
                    mode: 'lines+markers',
                    line: {
                        color: '#c15a00',
                        width: 2.5,
                        shape: 'spline'
                    },
                    marker: {
                        size: 7,
                        color: '#c15a00',
                        symbol: 'diamond'
                    },
                    yaxis: 'y2',
                    hovertemplate: '<b>%{x}</b><br>Tiempo prom.: %{y} h<extra></extra>',
                },
            ], {
                ...PLT_BASE,
                margin: {
                    t: 16,
                    r: 62,
                    b: 48,
                    l: 48
                },
                xaxis: ax({
                    tickfont: {
                        size: 11
                    }
                }),
                yaxis: ax({
                    title: {
                        text: 'Tickets',
                        font: {
                            size: 11,
                            color: '#0f766e'
                        }
                    },
                    tickfont: {
                        size: 10,
                        color: '#0f766e'
                    }
                }),
                yaxis2: ax({
                    title: {
                        text: 'Horas',
                        font: {
                            size: 11,
                            color: '#c15a00'
                        }
                    },
                    tickfont: {
                        size: 10,
                        color: '#c15a00'
                    },
                    overlaying: 'y',
                    side: 'right',
                    showgrid: false
                }),
                legend: {
                    orientation: 'h',
                    y: -0.18,
                    font: {
                        size: 11,
                        color: '#475569'
                    }
                },
            }, PLT_CFG);
        }

        /* ══════════════════════════════════════════════
           GRÁFICA 4 — HISTOGRAMA DE TIEMPOS
           Gradiente verde → teal → naranja → rojo
           (resolución rápida → lenta)
        ══════════════════════════════════════════════ */
        function chartHistograma(h) {
            if (!h) return;
            const COLORS = ['#1a8c38', '#22c55e', '#0f766e', '#c47d00', '#c15a00', '#ef4444', '#b91c1c'];

            Plotly.newPlot('chartHistograma', [{
                x: h.rangos,
                y: h.conteo,
                type: 'bar',
                marker: {
                    color: COLORS,
                    opacity: .92,
                    line: {
                        color: 'transparent'
                    }
                },
                text: h.conteo.map(String),
                textposition: 'outside',
                textfont: {
                    size: 11,
                    color: '#475569',
                    family: 'IBM Plex Mono, monospace'
                },
                hovertemplate: '<b>%{x}</b><br>%{y} tickets<extra></extra>',
            }], {
                ...PLT_BASE,
                margin: {
                    t: 24,
                    r: 10,
                    b: 52,
                    l: 42
                },
                xaxis: ax({
                    tickfont: {
                        size: 10.5
                    }
                }),
                yaxis: ax(),
                bargap: 0.12,
            }, PLT_CFG);
        }

        /* ══════════════════════════════════════════════
           GRÁFICA 5 — BURNDOWN CHART
           Línea ideal (punteada) vs. backlog real (teal)
           El relleno entre ambas curvas muestra la desviación
        ══════════════════════════════════════════════ */
        function chartBurndown(b) {
            if (!b) return;
            Plotly.newPlot('chartBurndown', [{
                    x: b.dias,
                    y: b.ideal,
                    name: 'Quema Ideal',
                    type: 'scatter',
                    mode: 'lines',
                    line: {
                        color: '#cbd5e1',
                        width: 2,
                        dash: 'dash'
                    },
                    hovertemplate: 'Ideal — %{x}: %{y} pendientes<extra></extra>',
                },
                {
                    x: b.dias,
                    y: b.real,
                    name: 'Backlog Real',
                    type: 'scatter',
                    mode: 'lines+markers',
                    fill: 'tonexty',
                    fillcolor: 'rgba(15,118,110,.09)',
                    line: {
                        color: '#0f766e',
                        width: 2.5,
                        shape: 'spline'
                    },
                    marker: {
                        size: 5,
                        color: '#0f766e'
                    },
                    hovertemplate: 'Real — %{x}: %{y} pendientes<extra></extra>',
                },
            ], {
                ...PLT_BASE,
                margin: {
                    t: 16,
                    r: 16,
                    b: 60,
                    l: 52
                },
                xaxis: ax({
                    tickangle: -40,
                    tickfont: {
                        size: 9
                    }
                }),
                yaxis: ax({
                    title: {
                        text: 'Tickets Pendientes',
                        font: {
                            size: 10,
                            color: '#64748b'
                        }
                    }
                }),
                legend: {
                    orientation: 'h',
                    y: -0.30,
                    font: {
                        size: 11,
                        color: '#475569'
                    }
                },
            }, PLT_CFG);
        }

        /* ══════════════════════════════════════════════
           LISTENER BOTÓN ACTUALIZAR
        ══════════════════════════════════════════════ */
        document.getElementById('btnRefresh').addEventListener('click', function() {
            this.classList.add('loading');
            this.querySelector('i').className = 'fas fa-spinner';
            setTimeout(() => {
                loadData().finally(() => {
                    this.classList.remove('loading');
                    this.querySelector('i').className = 'fas fa-sync-alt';
                });
            }, 200);
        });

        /* ══════════════════════════════════════════════
           INIT
        ══════════════════════════════════════════════ */
        document.addEventListener('DOMContentLoaded', loadData);
    </script>
@endpush
