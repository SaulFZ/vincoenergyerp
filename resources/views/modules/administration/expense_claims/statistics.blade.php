@extends('modules.administration.expense_claims.index')

@push('styles')
    <link href="{{ asset('assets/css/administration/expense_claims/expense_claims_statistics.css') }}" rel="stylesheet">
@endpush

@section('content')
<main class="ecs" id="ecs-dashboard">
    <header class="ecs-header">
        <div><span class="ecs-eyebrow">ADMINISTRACIÓN / GASTOS</span><h1>Estadísticas de gastos</h1><p>Una visión del gasto, las comprobaciones y los compromisos de pago.</p></div>
        <div class="ecs-header-actions"><span class="ecs-demo"><span></span> Datos de demostración</span><button class="ecs-button" id="ecs-export" type="button"><i class="bx bx-download" aria-hidden="true"></i> Exportar CSV</button></div>
    </header>

    <section class="ecs-filters" aria-label="Filtros del informe">
        <div class="ecs-field"><label for="ecs-period">Periodo</label><select id="ecs-period"><option value="all">Enero — octubre 2026</option><option value="quarter" selected>Agosto — octubre 2026</option><option value="month">Octubre 2026</option></select></div>
        <div class="ecs-field"><label for="ecs-area">Departamento</label><select id="ecs-area"><option value="all">Todos los departamentos</option></select></div>
        <div class="ecs-field"><label for="ecs-person">Colaborador</label><select id="ecs-person"><option value="all">Todos los colaboradores</option></select></div>
        <button class="ecs-button ecs-button-light" id="ecs-reset" type="button"><i class="bx bx-reset" aria-hidden="true"></i> Restablecer</button>
        <div class="ecs-cutoff"><span>Corte de la muestra</span><strong>16 oct 2026</strong></div>
    </section>

    <div class="ecs-section-heading"><h2>Resumen financiero</h2><span id="ecs-scope" aria-live="polite"></span></div>
    <section class="ecs-kpis" id="ecs-kpis" aria-label="Indicadores financieros"></section>

    <div class="ecs-chart-grid">
        <section class="ecs-card ecs-trend-card"><header class="ecs-card-head"><div><span class="ecs-eyebrow">EVOLUCIÓN</span><h2>Anticipos y comprobaciones</h2><p>Importes por mes de emisión, en MXN.</p></div><span class="ecs-tag">Mensual</span></header><div class="ecs-legend"><span><b class="ecs-dot ecs-dot-navy"></b>Anticipos entregados</span><span><b class="ecs-dot ecs-dot-sage"></b>Comprobaciones aprobadas</span></div><div id="ecs-trend" class="ecs-chart"></div></section>
        <section class="ecs-card"><header class="ecs-card-head"><div><span class="ecs-eyebrow">DISTRIBUCIÓN</span><h2>¿En qué se gasta?</h2><p>Comprobaciones aprobadas por categoría.</p></div></header><div id="ecs-categories"></div></section>
    </div>

    <div class="ecs-bottom-grid">
        <section class="ecs-card"><header class="ecs-card-head"><div><span class="ecs-eyebrow">SEGUIMIENTO</span><h2>Por comprobar, por área</h2><p>Saldo abierto de los anticipos seleccionados.</p></div></header><div id="ecs-areas" class="ecs-bars"></div></section>
        <section class="ecs-card"><header class="ecs-card-head"><div><span class="ecs-eyebrow">FLUJO DE TRABAJO</span><h2>Estado de las solicitudes</h2><p>Comprobaciones y reembolsos del periodo.</p></div></header><div id="ecs-statuses" class="ecs-status-list"></div></section>
        <section class="ecs-card ecs-focus-card"><header class="ecs-card-head"><div><span class="ecs-eyebrow">ATENCIÓN OPERATIVA</span><h2>Prioridades del periodo</h2><p>Puntos que requieren seguimiento.</p></div><i class="bx bx-radar" aria-hidden="true"></i></header><div id="ecs-priorities"></div><p class="ecs-small-note">Indicadores ilustrativos. No se envían avisos ni se realizan movimientos.</p></section>
    </div>

    <section class="ecs-card ecs-register">
        <header class="ecs-card-head"><div><span class="ecs-eyebrow">DETALLE</span><h2>Registro de movimientos <span class="ecs-count" id="ecs-count">0</span></h2><p>Consulta los registros que componen el informe.</p></div><div class="ecs-table-filters"><label class="ecs-search"><i class="bx bx-search" aria-hidden="true"></i><input id="ecs-search" type="search" placeholder="Buscar folio, concepto o colaborador" aria-label="Buscar movimientos"></label><select id="ecs-type" aria-label="Tipo de movimiento"><option value="all">Todas las operaciones</option><option value="anticipo">Anticipos</option><option value="comprobacion">Comprobaciones</option><option value="reembolso">Reembolsos</option><option value="retencion">Retenciones</option></select></div></header>
        <div class="ecs-table-scroll"><table class="ecs-table"><caption class="ecs-sr-only">Movimientos ficticios del periodo y departamento seleccionados</caption><thead><tr><th>Folio / Fecha</th><th>Colaborador</th><th>Operación</th><th>Tipo</th><th>Concepto</th><th>Monto</th><th>Estado</th><th>Tesorería</th><th>Detalle</th></tr></thead><tbody id="ecs-table-body"></tbody></table></div>
        <footer class="ecs-table-footer"><span id="ecs-page-label" aria-live="polite"></span><div class="ecs-pagination"><label for="ecs-size">Filas</label><select id="ecs-size"><option>8</option><option>16</option><option>32</option></select><button type="button" id="ecs-prev" class="ecs-button ecs-button-light" aria-label="Página anterior"><i class="bx bx-chevron-left" aria-hidden="true"></i></button><span id="ecs-page"></span><button type="button" id="ecs-next" class="ecs-button ecs-button-light" aria-label="Página siguiente"><i class="bx bx-chevron-right" aria-hidden="true"></i></button></div></footer>
    </section>
    <p class="ecs-footnote">Muestra ficticia • MXN • Los filtros superiores actualizan todo el informe. La búsqueda y el tipo de operación filtran únicamente el registro y la exportación.</p>

    <dialog class="ecs-dialog" id="ecs-detail" aria-labelledby="ecs-detail-title"><header class="ecs-card-head"><div><span class="ecs-eyebrow">MOVIMIENTO DE DEMOSTRACIÓN</span><h2 id="ecs-detail-title">Detalle del movimiento</h2></div><button class="ecs-button ecs-button-light" type="button" id="ecs-close" aria-label="Cerrar detalle"><i class="bx bx-x" aria-hidden="true"></i></button></header><div id="ecs-detail-body"></div></dialog>
</main>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/administration/expense_claims/expense-claims-statistics.js') }}" defer></script>
@endpush
