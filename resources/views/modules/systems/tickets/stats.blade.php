@extends('modules.systems.tickets.index')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=IBM+Plex+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
/* ══════════════════════════════════════════════
   TOKENS DE COLOR (SLATE & TEAL + ESTADOS ORIGINALES)
══════════════════════════════════════════════ */
:root {
    /* Paleta Estructural Slate & Teal (Corporate Theme) */
    --bg:          #f1f5f9; /* Slate 100 */
    --surface:     #ffffff;
    --surface2:    #f8fafc; /* Slate 50 */
    --border:      #e2e8f0; /* Slate 200 */
    --border2:     #cbd5e1; /* Slate 300 */
    --primary:     #0f172a; /* Slate 900 */
    --primary-mid: #334155; /* Slate 700 */
    --accent:      #0f766e; /* Teal 700 */
    --accent-light:#ccfbf1; /* Teal 100 */
    --text:        #0f172a; /* Slate 900 */
    --muted:       #475569; /* Slate 600 */
    --muted2:      #64748b; /* Slate 500 */

    /* Variables de Estados Idénticas a la Vista Principal */
    --s-new-text:    #0c2d5e;
    --s-new-bg:      #d4e4f7;
    --s-new-border:  #2e6db4;
    --s-open-text:   #0b4a52;
    --s-open-bg:     #c8edf3;
    --s-open-border: #1a8fa3;
    --s-wait-text:   #7a2c00;
    --s-wait-bg:     #fddbc7;
    --s-wait-border: #c15a00;
    --s-pend-text:   #6b3b00;
    --s-pend-bg:     #fde8ab;
    --s-pend-border: #c47d00;
    --s-done-text:   #0d4a1e;
    --s-done-bg:     #bbf0cc;
    --s-done-border: #1a8c38;
    --s-cancel-text: #6b0c0c;
    --s-cancel-bg:   #fcc8c8;
    --s-cancel-border: #b91c1c;

    /* Propiedades de UI */
    --radius:      12px;
    --shadow-sm:   0 2px 8px rgba(15, 23, 42, .04);
    --shadow-md:   0 8px 24px rgba(15, 23, 42, .08);
}

* { box-sizing: border-box; margin: 0; padding: 0; }

/* ══════════════════════════════════════════════
   LAYOUT RAÍZ
══════════════════════════════════════════════ */
.sd-root {
    background: var(--bg);
    min-height: calc(100vh - 120px);
    font-family: 'Inter', sans-serif;
    padding: 24px;
    position: relative;
    border-radius: var(--radius);
    overflow: hidden;
}

/* ══════════════════════════════════════════════
   LOADER OVERLAY
══════════════════════════════════════════════ */
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
    transition: opacity .4s ease, visibility .4s ease;
    border-radius: var(--radius);
}
.sd-overlay.hide { opacity: 0; visibility: hidden; }

.sd-spinner {
    width: 42px; height: 42px;
    border: 3px solid var(--border2);
    border-top-color: var(--accent);
    border-radius: 50%;
    animation: spin .8s cubic-bezier(.4, 0, .2, 1) infinite;
}
.sd-overlay p {
    font-size: .85rem;
    font-weight: 600;
    color: var(--primary-mid);
    letter-spacing: 0.02em;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ══════════════════════════════════════════════
   KPI STRIP (TARJETAS DE ESTADOS HOMOLOGADAS)
══════════════════════════════════════════════ */
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
    bottom: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 0 0 var(--radius) var(--radius);
}
.kpi-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }

/* Aplicación de colores exactos de la vista principal */
.kpi-card.c-nuevo::before    { background: var(--s-new-border); }
.kpi-card.c-abierto::before  { background: var(--s-open-border); }
.kpi-card.c-espera::before   { background: var(--s-wait-border); }
.kpi-card.c-concluir::before { background: var(--s-pend-border); }
.kpi-card.c-realizado::before{ background: var(--s-done-border); }
.kpi-card.c-cancelado::before{ background: var(--s-cancel-border); }

.kpi-info { display: flex; flex-direction: column; gap: 3px; }

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
    width: 44px; height: 44px;
    border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}

/* Iconos pintados con los fondos y textos de la paleta principal */
.kpi-card.c-nuevo .kpi-icon    { background: var(--s-new-bg); color: var(--s-new-border); }
.kpi-card.c-abierto .kpi-icon  { background: var(--s-open-bg); color: var(--s-open-border); }
.kpi-card.c-espera .kpi-icon   { background: var(--s-wait-bg); color: var(--s-wait-border); }
.kpi-card.c-concluir .kpi-icon { background: var(--s-pend-bg); color: var(--s-pend-border); }
.kpi-card.c-realizado .kpi-icon{ background: var(--s-done-bg); color: var(--s-done-border); }
.kpi-card.c-cancelado .kpi-icon{ background: var(--s-cancel-bg); color: var(--s-cancel-border); }

/* ══════════════════════════════════════════════
   BARRA DE FILTROS & ACTUALIZAR
══════════════════════════════════════════════ */
.filters-bar {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 16px 20px;
    margin-bottom: 24px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    box-shadow: var(--shadow-sm);
}

.filters-group {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.filter-item {
    display: flex;
    align-items: center;
    gap: 8px;
}
.filter-item label {
    font-size: .75rem;
    font-weight: 600;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: .04em;
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
    min-width: 160px;
}
.filter-item select:focus { border-color: var(--accent); }

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
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
}
.sd-refresh-btn:hover {
    background: var(--primary-mid);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.2);
}
.sd-refresh-btn.loading i { animation: spin .8s linear infinite; }

/* ══════════════════════════════════════════════
   CHART GRID SYSTEM & CARDS
══════════════════════════════════════════════ */
.chart-row {
    display: grid;
    gap: 16px;
    margin-bottom: 16px;
}
.cols-1  { grid-template-columns: 1fr; }
.cols-2  { grid-template-columns: 1fr 1fr; }

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
.chart-card:hover { box-shadow: var(--shadow-md); }

.chart-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 20px;
    gap: 12px;
    padding-bottom: 12px;
    border-bottom: 1px dashed var(--border);
}

.chart-card-title {
    font-size: .95rem;
    font-weight: 700;
    color: var(--primary);
    display: flex;
    align-items: center;
    gap: 8px;
    line-height: 1.2;
}
.chart-card-title i { color: var(--accent); font-size: .9rem; }

.chart-card-desc {
    font-size: .75rem;
    color: var(--muted);
    margin-top: 6px;
    font-weight: 500;
}

.chart-wrap {
    flex: 1;
    min-height: 0;
    position: relative;
    padding-top: 8px;
}

/* Alturas de contenedor para gráficas */
.h-280 .chart-wrap { height: 280px; }
.h-300 .chart-wrap { height: 300px; }
.h-360 .chart-wrap { height: 360px; }

/* ══════════════════════════════════════════════
   TABLA DE RANKING / DEPARTAMENTOS
══════════════════════════════════════════════ */
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
    text-align: center; /* Centrado por defecto */
}
.rank-table thead th:first-child { border-radius: 8px 0 0 0; }
.rank-table thead th:last-child  { border-radius: 0 8px 0 0; }

.rank-table tbody td {
    padding: 12px;
    border-bottom: 1px solid var(--border);
    color: var(--text);
    vertical-align: middle;
    text-align: center; /* Centrado por defecto */
}
.rank-table tbody tr:last-child td { border-bottom: none; }
.rank-table tbody tr:hover td { background: var(--surface2); }

.rank-num {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .8rem;
    color: var(--muted2);
    font-weight: 700;
    width: 32px;
}

/* Etiquetas de prioridad idénticas a la primera vista */
.stag-mini {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 10px; border-radius: 5px;
    font-size: .65rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .04em;
    white-space: nowrap;
}
.stag-mini.alta  { background: var(--s-cancel-bg); color: var(--s-cancel-text); border: 1px solid var(--s-cancel-border); }
.stag-mini.media { background: var(--s-pend-bg); color: var(--s-pend-text); border: 1px solid var(--s-pend-border); }
.stag-mini.baja  { background: var(--s-done-bg); color: var(--s-done-text); border: 1px solid var(--s-done-border); }

/* ══════════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════════ */
@media (max-width: 1280px) {
    .kpi-strip { grid-template-columns: repeat(3, 1fr); }
    .filters-bar { flex-direction: column; align-items: flex-start; }
    .sd-refresh-btn { width: 100%; justify-content: center; }
}
@media (max-width: 900px) {
    .kpi-strip { grid-template-columns: repeat(2, 1fr); }
    .cols-2 { grid-template-columns: 1fr; }
    .sd-root { padding: 16px; }
}
@media (max-width: 520px) {
    .kpi-strip { grid-template-columns: 1fr; }
    .filters-group { flex-direction: column; width: 100%; align-items: flex-start; }
    .filter-item, .filter-item select { width: 100%; }
}
</style>

<div class="sd-root content active">
    {{-- Overlay loader contenido estrictamente al SD-ROOT --}}
    <div class="sd-overlay" id="sdOverlay">
        <div class="sd-spinner"></div>
        <p>Procesando y sincronizando métricas...</p>
    </div>

    {{-- ── KPIs (NUEVO, ABIERTO, ESPERA, POR CONCLUIR, REALIZADO, CANCELADO) ── --}}
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

    {{-- ── BARRA DE FILTROS & ACTUALIZACIÓN ────────────────────────────── --}}
    <div class="filters-bar">
        <div class="filters-group">
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
        </div>

        <button class="sd-refresh-btn" id="btnRefresh">
            <i class="fas fa-sync-alt"></i> Actualizar Métricas
        </button>
    </div>

    {{-- ── 1. TABLA DETALLADA (FILA 1) ─────────────────────────────────── --}}
    <div class="chart-row cols-1">
        <div class="chart-card">
            <div class="chart-card-header">
                <div>
                    <div class="chart-card-title"><i class="fas fa-table"></i> Detalle Operativo por Departamento</div>
                    <div class="chart-card-desc">Análisis exhaustivo del desempeño y flujo de trabajo por área corporativa</div>
                </div>
            </div>
            <div class="chart-wrap" style="height:auto;">
                <div style="overflow-x:auto;">
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
                                <th>Prioridad Alta</th>
                                <th>Prioridad Media</th>
                                <th>Prioridad Baja</th>
                            </tr>
                        </thead>
                        <tbody id="tablaDeptBody">
                            <tr><td colspan="12" style="text-align:center;padding:30px;color:var(--muted2);">Calculando matrices de datos...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 2. DEPARTAMENTOS Y PRIORIDADES (FILA 2) ─────────────────────── --}}
    <div class="chart-row cols-2 h-360">
        <div class="chart-card">
            <div class="chart-card-header">
                <div>
                    <div class="chart-card-title"><i class="fas fa-sort-amount-down"></i> Top Departamentos con Incidencias</div>
                    <div class="chart-card-desc">Clasificación jerárquica basada en la cantidad absoluta de tickets emitidos</div>
                </div>
            </div>
            <div class="chart-wrap"><div id="chartDeptBar" style="width:100%;height:100%;"></div></div>
        </div>
        <div class="chart-card">
            <div class="chart-card-header">
                <div>
                    <div class="chart-card-title"><i class="fas fa-flag"></i> Matriz de Prioridades por Área</div>
                    <div class="chart-card-desc">Distribución comparativa de la urgencia de solicitudes (Alta, Media, Baja)</div>
                </div>
            </div>
            <div class="chart-wrap"><div id="chartPrioArea" style="width:100%;height:100%;"></div></div>
        </div>
    </div>

    {{-- ── 3. MES Y SEMANA (FILA 3) ────────────────────────────────────── --}}
    <div class="chart-row cols-2 h-300">
        <div class="chart-card">
            <div class="chart-card-header">
                <div>
                    <div class="chart-card-title"><i class="fas fa-calendar-alt"></i> Tickets por Mes (Año Actual)</div>
                    <div class="chart-card-desc">Evolución del volumen de tickets generados mes a mes</div>
                </div>
            </div>
            <div class="chart-wrap"><div id="chartMonthly" style="width:100%;height:100%;"></div></div>
        </div>
        <div class="chart-card">
            <div class="chart-card-header">
                <div>
                    <div class="chart-card-title"><i class="fas fa-calendar-week"></i> Distribución por Día de la Semana</div>
                    <div class="chart-card-desc">Frecuencia de generación de incidencias según el día laboral</div>
                </div>
            </div>
            <div class="chart-wrap"><div id="chartWeekday" style="width:100%;height:100%;"></div></div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/plotly.js/2.27.1/plotly.min.js"></script>

<script>
/* ══════════════════════════════════════════════
   CONFIGURACIÓN GLOBAL PLOTLY
══════════════════════════════════════════════ */
const PLT_CFG   = { responsive: true, displayModeBar: false };
const PLT_FONT  = { family: 'Inter, sans-serif', color: '#64748b', size: 11 };
const PLT_BASE  = {
    paper_bgcolor: 'transparent',
    plot_bgcolor:  'transparent',
    font:          PLT_FONT,
    margin:        { t: 8, r: 10, b: 36, l: 48 },
    hoverlabel:    { bgcolor: '#0f172a', bordercolor: '#cbd5e1', font: { color: '#fff', size: 12 } },
};
const AXIS_STYLE = {
    gridcolor:     'rgba(15, 23, 42, 0.04)',
    linecolor:     'rgba(15, 23, 42, 0.08)',
    tickfont:      { color: '#64748b', size: 10 },
    zerolinecolor: 'rgba(15, 23, 42, 0.05)',
};

/* ══════════════════════════════════════════════
   PALETAS DE COLOR HOMOLOGADAS (Extraídas del CSS Root)
══════════════════════════════════════════════ */
const ST_COLORS = {
    nuevo:     '#2e6db4', /* --s-new-border */
    abierto:   '#1a8fa3', /* --s-open-border */
    espera:    '#c15a00', /* --s-wait-border */
    concluir:  '#c47d00', /* --s-pend-border */
    realizado: '#1a8c38', /* --s-done-border */
    cancelado: '#b91c1c'  /* --s-cancel-border */
};

/* Las prioridades en la vista principal usan los colores de estado: Cancelado (Alta), Por Concluir (Media), Realizado (Baja) */
const PAL_PRIO = {
    alta:  ST_COLORS.cancelado,
    media: ST_COLORS.concluir,
    baja:  ST_COLORS.realizado
};

/* Tonos apagados/sobrios (Slate) para la gráfica de departamentos */
const PAL_DEPT = [
    '#64748b', /* Slate 500 */
    '#475569', /* Slate 600 */
    '#334155', /* Slate 700 */
    '#94a3b8', /* Slate 400 */
    '#1e293b', /* Slate 800 */
    '#cbd5e1'  /* Slate 300 */
];

function axis(extra={}) { return { ...AXIS_STYLE, ...extra }; }

/* ══════════════════════════════════════════════
   CARGA DE DATOS PRINCIPAL
══════════════════════════════════════════════ */
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
const H    = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' };

async function loadData() {
    const overlay = document.getElementById('sdOverlay');
    overlay.classList.remove('hide');

    try {
        const r = await fetch('{{ route("systems.tickets.stats.data") }}', { headers: H });
        if (!r.ok) throw new Error('Error de red obteniendo analíticas');
        const data = await r.json();
        renderAll(data);
    } catch(err) {
        console.warn('Utilizando matriz de datos simulada.', err);
        renderAll(buildDemoData());
    } finally {
        setTimeout(() => overlay.classList.add('hide'), 400);
    }
}

/* ══════════════════════════════════════════════
   CONSTRUCTOR DE DATOS (MOCKS)
══════════════════════════════════════════════ */
function buildDemoData() {
    const depts = ['Geociencias', 'Sistemas', 'Operaciones', 'Mantenimiento', 'Recursos Humanos', 'Contabilidad', 'Dirección'];

    const departmentsDetail = depts.map((name, i) => {
        let tTotal = Math.round(40 + Math.random()*150);
        return {
            name,
            total:     tTotal,
            nuevo:     Math.round(tTotal * 0.10),
            abierto:   Math.round(tTotal * 0.15),
            espera:    Math.round(tTotal * 0.08),
            concluir:  Math.round(tTotal * 0.12),
            realizado: Math.round(tTotal * 0.50),
            cancelado: Math.round(tTotal * 0.05),
            alta:      Math.round(tTotal * 0.20),
            media:     Math.round(tTotal * 0.50),
            baja:      Math.round(tTotal * 0.30),
        };
    });

    departmentsDetail.sort((a,b) => b.total - a.total);

    const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    const currentMonth = new Date().getMonth();
    const days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

    return {
        kpis: {
            nuevo:     departmentsDetail.reduce((sum, d) => sum + d.nuevo, 0),
            abierto:   departmentsDetail.reduce((sum, d) => sum + d.abierto, 0),
            espera:    departmentsDetail.reduce((sum, d) => sum + d.espera, 0),
            concluir:  departmentsDetail.reduce((sum, d) => sum + d.concluir, 0),
            realizado: departmentsDetail.reduce((sum, d) => sum + d.realizado, 0),
            cancelado: departmentsDetail.reduce((sum, d) => sum + d.cancelado, 0),
        },
        monthly: {
            labels: months.slice(0, currentMonth + 1),
            created: months.slice(0, currentMonth + 1).map(() => Math.round(50 + Math.random() * 120)),
        },
        weekday: {
            labels: days,
            data: [
                Math.round(100+Math.random()*60),
                Math.round(80+Math.random()*50),
                Math.round(90+Math.random()*40),
                Math.round(85+Math.random()*40),
                Math.round(70+Math.random()*30),
                Math.round(15+Math.random()*20),
                Math.round(5+Math.random()*10)
            ],
        },
        departments: {
            labels: departmentsDetail.map(d => d.name),
            data:   departmentsDetail.map(d => d.total),
        },
        departments_detail: departmentsDetail,
        prio_by_area: {
            areas: departmentsDetail.map(d => d.name),
            alta:  departmentsDetail.map(d => d.alta),
            media: departmentsDetail.map(d => d.media),
            baja:  departmentsDetail.map(d => d.baja),
        },
    };
}

/* ══════════════════════════════════════════════
   ORQUESTADOR DE RENDERIZADO
══════════════════════════════════════════════ */
function renderAll(data) {
    fillKPIs(data.kpis);
    renderTablaDept(data.departments_detail);
    chartDeptBar(data.departments);
    chartPrioArea(data.prio_by_area);
    chartMonthly(data.monthly);
    chartWeekday(data.weekday);
}

/* ── ACTUALIZACIÓN DE TARJETAS KPI ───────────────────────── */
function fillKPIs(k) {
    document.getElementById('kpi-new').innerText    = k.nuevo     ?? 0;
    document.getElementById('kpi-open').innerText   = k.abierto   ?? 0;
    document.getElementById('kpi-wait').innerText   = k.espera    ?? 0;
    document.getElementById('kpi-pend').innerText   = k.concluir  ?? 0;
    document.getElementById('kpi-done').innerText   = k.realizado ?? 0;
    document.getElementById('kpi-cancel').innerText = k.cancelado ?? 0;
}

/* ── TABLA MAESTRA DETALLADA (FILA 1) ────────────────────── */
function renderTablaDept(depts) {
    if (!depts || !depts.length) return;
    const tbody = document.getElementById('tablaDeptBody');

    tbody.innerHTML = depts.map((d, i) => {
        return `
        <tr>
            <td class="rank-num">${i+1}</td>
            <td style="font-weight:700;color:var(--primary);text-align:left;">${d.name}</td>
            <td style="color:${ST_COLORS.nuevo}; font-weight:600;">${d.nuevo}</td>
            <td style="color:${ST_COLORS.abierto};">${d.abierto}</td>
            <td style="color:${ST_COLORS.espera};">${d.espera}</td>
            <td style="color:${ST_COLORS.concluir}; font-weight:600;">${d.concluir}</td>
            <td style="color:${ST_COLORS.realizado}; font-weight:700;">${d.realizado}</td>
            <td style="color:${ST_COLORS.cancelado};">${d.cancelado}</td>
            <td style="font-family:'IBM Plex Mono', monospace; font-weight:800; font-size:.9rem;">${d.total}</td>
            <td><span class="stag-mini alta">${d.alta}</span></td>
            <td><span class="stag-mini media">${d.media}</span></td>
            <td><span class="stag-mini baja">${d.baja}</span></td>
        </tr>`;
    }).join('');
}

/* ── GRÁFICA DE BARRAS HORIZONTALES - TOP DEPTOS (FILA 2) ── */
function chartDeptBar(d) {
    const labels = [...d.labels].reverse();
    const data   = [...d.data].reverse();
    const colors = labels.map((_, i) => PAL_DEPT[i % PAL_DEPT.length]);

    Plotly.newPlot('chartDeptBar', [{
        y: labels,
        x: data,
        type: 'bar',
        orientation: 'h',
        marker: { color: colors, opacity: 0.95, line: { color: 'transparent' } },
        text: data.map(String),
        textposition: 'outside',
        textfont: { size: 11, color: '#475569', family: 'IBM Plex Mono, monospace', weight: 700 },
    }], {
        ...PLT_BASE,
        margin: { t: 8, r: 40, b: 24, l: 140 },
        xaxis: { ...axis() },
        yaxis: { ...axis(), automargin: true, tickfont: { size: 11.5, color: '#0f172a', weight: 600 } },
    }, PLT_CFG);
}

/* ── BARRAS APILADAS - PRIORIDADES POR ÁREA (FILA 2) ─────── */
function chartPrioArea(p) {
    Plotly.newPlot('chartPrioArea', [
        { name:'Prioridad Alta',  x: p.areas, y: p.alta,  type:'bar', marker:{ color: PAL_PRIO.alta }, opacity:.95 },
        { name:'Prioridad Media', x: p.areas, y: p.media, type:'bar', marker:{ color: PAL_PRIO.media }, opacity:.95 },
        { name:'Prioridad Baja',  x: p.areas, y: p.baja,  type:'bar', marker:{ color: PAL_PRIO.baja }, opacity:.95 },
    ], {
        ...PLT_BASE,
        barmode: 'stack',
        margin: { t: 8, r: 10, b: 70, l: 40 },
        xaxis: { ...axis(), tickangle: -30, tickfont: { size: 10.5, color: '#334155' } },
        yaxis: { ...axis() },
        legend: { orientation: 'h', y: -0.35, font: { size: 11, color: '#475569' } },
    }, PLT_CFG);
}

/* ── BARRAS MENSUALES - TICKETS CREADOS (FILA 3) ─────────── */
function chartMonthly(m) {
    Plotly.newPlot('chartMonthly', [
        {
            x: m.labels,
            y: m.created,
            name: 'Tickets Creados',
            type: 'bar',
            marker: { color: 'var(--accent)' }, /* Teal */
            opacity: .95
        },
    ], {
        ...PLT_BASE,
        margin: { t: 16, r: 10, b: 40, l: 40 },
        xaxis: { ...axis(), tickfont: { size: 11 } },
        yaxis: { ...axis() },
        legend: { orientation: 'h', y: -0.25, font: { size: 11 } },
    }, PLT_CFG);
}

/* ── DISTRIBUCIÓN POR DÍAS DE LA SEMANA (FILA 3) ─────────── */
function chartWeekday(w) {
    const colors = w.data.map((v, i) => i < 5 ? 'var(--primary-mid)' : 'var(--border2)');

    Plotly.newPlot('chartWeekday', [{
        x: w.labels,
        y: w.data,
        type: 'bar',
        marker: { color: colors, line: { color: 'transparent' } },
        text: w.data.map(String),
        textposition: 'outside',
        textfont: { size: 11, color: '#475569', family: 'IBM Plex Mono, monospace' },
    }], {
        ...PLT_BASE,
        margin: { t: 16, r: 10, b: 40, l: 40 },
        xaxis: { ...axis(), tickfont: { size: 11 } },
        yaxis: { ...axis() },
    }, PLT_CFG);
}

/* ══════════════════════════════════════════════
   LISTENERS (ACTUALIZAR)
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
