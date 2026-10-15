@extends('modules.operations.sgc-ves.index')

@section('content')

<main class="svc-page">
    <div class="svc-container">

        {{-- ============ HEADER ============ --}}
        <header class="svc-header">
            <div>
                <h1 class="svc-title">Servicios</h1>
                <p class="svc-subtitle">Vista general por Orden de Servicio</p>
            </div>
            <div class="svc-header-actions">
                <a href="{{ route('operations.sgc-ves.index') }}/nuevo" class="btn btn-primary">+ Nuevo servicio</a>
            </div>
        </header>

        {{-- ============ FILTROS ============ --}}
        <form class="svc-filters" method="GET" action="{{ route('operations.sgc-ves.index') }}">
            <div class="filter-grid">
                <input type="text" name="q" placeholder="OS, cliente, pozo, unidad..." class="input" value="{{ request('q') }}">

                <select name="cliente" class="input">
                    <option value="" selected>Todos los clientes</option>
                    @foreach(['BAKER HUGHES','BLUEMARINE','CYDSA','DIXTONE','GEOTERMIA','GSM','GSM/BLOQUES','H. ENERGY','HALLIBURTON','IPS','MEXXUS','OPEX','PEMEX','PERENCO','PETROFOR','PIMEX','SIN CLIENTE','SLB','WDEA','WDEA/H. ENERGY'] as $cli)
                        <option value="{{ $cli }}" {{ request('cliente') === $cli ? 'selected' : '' }}>{{ $cli }}</option>
                    @endforeach
                </select>

                <select name="pozo" class="input">
                    <option value="" selected>Todos los pozos</option>
                    @foreach(['5 PRESIDENTES 165','5 PRESIDENTES 935','AYATSIL 131 REE','AYATSIL 132','BACAB 3','BLASILLO 505','CIBIX 35','IXACHI 54','IXACHI 69','OGARRIO 1480','SAMARIA 668','TUPILCO 143'] as $pz)
                        <option value="{{ $pz }}" {{ request('pozo') === $pz ? 'selected' : '' }}>{{ $pz }}</option>
                    @endforeach
                </select>

                <select name="anio" class="input">
                    <option value="" selected>Todos los años</option>
                    @foreach([2023,2024,2025,2026] as $y)
                        <option value="{{ $y }}" {{ request('anio') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>

                <select name="trimestre" class="input">
                    <option value="" selected>Todos los trimestres</option>
                    <option value="Q1" {{ request('trimestre') === 'Q1' ? 'selected' : '' }}>Q1 · Ene-Mar</option>
                    <option value="Q2" {{ request('trimestre') === 'Q2' ? 'selected' : '' }}>Q2 · Abr-Jun</option>
                    <option value="Q3" {{ request('trimestre') === 'Q3' ? 'selected' : '' }}>Q3 · Jul-Sep</option>
                    <option value="Q4" {{ request('trimestre') === 'Q4' ? 'selected' : '' }}>Q4 · Oct-Dic</option>
                </select>

                <select name="mes" class="input">
                    <option value="" selected>Todos los meses</option>
                    @foreach(['01','02','03','04','05','06','07','08','09','10','11','12'] as $m)
                        <option value="{{ $m }}" {{ request('mes') === $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-grid filter-grid-second">
                <select name="estado" class="input">
                    <option value="" selected>Todos los estados</option>
                    @foreach(['TERMINADO','CANCELADO','OPERACIÓN CANCELADA','SERVICIO CANCELADO','CANCELACION'] as $e)
                        <option value="{{ $e }}" {{ request('estado') === $e ? 'selected' : '' }}>{{ $e }}</option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="{{ route('operations.sgc-ves.index') }}" class="btn btn-outline">Limpiar</a>
            </div>

            {{-- Chips de trimestres --}}
            <div class="chips-row">
                <span class="chips-label">Trimestres</span>
                <a href="{{ route('operations.sgc-ves.index') }}" class="chip chip-active">Anual / Todos</a>
                <a href="{{ route('operations.sgc-ves.index', ['trimestre' => 'Q1']) }}" class="chip">Q1 · Ene-Mar · 123 OS</a>
                <a href="{{ route('operations.sgc-ves.index', ['trimestre' => 'Q2']) }}" class="chip">Q2 · Abr-Jun · 156 OS</a>
                <a href="{{ route('operations.sgc-ves.index', ['trimestre' => 'Q3']) }}" class="chip">Q3 · Jul-Sep · 132 OS</a>
                <a href="{{ route('operations.sgc-ves.index', ['trimestre' => 'Q4']) }}" class="chip">Q4 · Oct-Dic · 58 OS</a>
            </div>

            {{-- Reporte PDF --}}
            <div class="report-box">
                <span class="chips-label">Reporte de tabla</span>
                <a target="_blank" href="{{ route('operations.sgc-ves.index') }}/reporte" class="btn btn-primary btn-sm">Imprimir / PDF tabla actual</a>
                <a target="_blank" href="{{ route('operations.sgc-ves.index', ['trimestre' => 'Q1']) }}/reporte" class="btn-pdf">PDF Q1 (123)</a>
                <a target="_blank" href="{{ route('operations.sgc-ves.index', ['trimestre' => 'Q2']) }}/reporte" class="btn-pdf">PDF Q2 (156)</a>
                <a target="_blank" href="{{ route('operations.sgc-ves.index', ['trimestre' => 'Q3']) }}/reporte" class="btn-pdf">PDF Q3 (132)</a>
                <a target="_blank" href="{{ route('operations.sgc-ves.index', ['trimestre' => 'Q4']) }}/reporte" class="btn-pdf">PDF Q4 (58)</a>
            </div>

            <p class="filter-hint">
                El filtro de año, mes y trimestre usa la FECHA DE FIN DE OPERACIÓN. Si no existe fin de operación en el RDO, usa la fecha principal del servicio como respaldo. En la tabla se muestran Inicio Op y Fin Op para validar cada OS.
            </p>
        </form>

        {{-- ============ ALERTAS ============ --}}
        <section class="alert-box">
            <div class="alert-inner">
                <div>
                    <div class="alert-text">Busca servicios por ingeniero, trainee u operador y entra directo a editar la OS.</div>
                </div>
                <a href="{{ route('operations.sgc-ves.index') }}/por-personal" class="btn btn-amber">Filtrar por ingeniero / trainee</a>
            </div>

            <h2 class="alert-title">Alertas de OS / carga del RDO</h2>
            <p class="alert-desc">
                Estas alertas no entran al conteo de servicios hasta corregirlas. Los filtros y totales usan sólo OS con formato VES-XXX-OS-####.
                WL, MCR y TCP con el mismo consecutivo son válidas y no se marcan como duplicado.
            </p>

            <div class="alert-invalid">
                <p class="alert-invalid-title">OS no válidas o combinadas detectadas: 2</p>
                <div class="alert-chips">
                    <a href="{{ route('operations.sgc-ves.show', 260) }}" class="chip chip-amber">VES-LWT-OS-1600 B</a>
                    <a href="{{ route('operations.sgc-ves.show', 214) }}" class="chip chip-amber">VES-WL-OS-1581/84</a>
                </div>
            </div>
            <p class="alert-note">
                Para limpiar folios como 17301783 o una OS combinada, usa el script que te dejo: primero listar, luego eliminar sólo la OS confirmada.
            </p>
        </section>

        {{-- ============ RESUMEN ============ --}}
        <div class="summary-box">
            <span class="summary-primary">Servicios operativos: {{ $totalOperativos ?? 445 }}</span>
            <span class="summary-divider">|</span>
            <span class="summary-amber">Suministros / venta de producto: {{ $totalSuministros ?? 24 }}</span>
            <span class="summary-divider">|</span>
            <span>OS mostradas: {{ $totalMostradas ?? 469 }}</span>
            <p class="summary-note">
                Los suministros se muestran para trazabilidad, pero no cuentan como servicio operativo porque son venta/provisión de producto sin URE, cable o registro.
            </p>
        </div>

        {{-- ============ TABLA ============ --}}
        <div class="table-wrap">
            <table class="svc-table">
                <thead>
                    <tr>
                        <th>OS</th>
                        <th>Inicio Op</th>
                        <th>Fin Op</th>
                        <th>Horas NPT</th>
                        <th>Cliente</th>
                        <th>Pozo</th>
                        <th>URE</th>
                        <th>Grúa</th>
                        <th>Plana</th>
                        <th>ECP</th>
                        <th>Pickup</th>
                        <th>Chip</th>
                        <th>Ingenieros</th>
                        <th>TRIH</th>
                        <th>Cable</th>
                        <th>Estado</th>
                        <th>Servicio</th>
                        <th class="th-sticky">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($servicios ?? [] as $s)
                        <tr>
                            <td class="td-bold">{{ $s->os }}</td>
                            <td>{{ $s->inicio_op }}</td>
                            <td class="td-dark">{{ $s->fin_op ?? '-' }}</td>
                            <td class="td-npt {{ ($s->horas_npt ?? 0) > 0 ? 'td-npt-alert' : '' }}">
                                {{ $s->horas_npt ?? 0 }} h
                            </td>
                            <td>{{ $s->cliente }}</td>
                            <td>{{ $s->pozo }}</td>
                            <td>{{ $s->ure ?? '-' }}</td>
                            <td>{{ $s->grua ?? '-' }}</td>
                            <td>{{ $s->plana ?? '-' }}</td>
                            <td>{{ $s->ecp ?? '-' }}</td>
                            <td>{{ $s->pickup ?? '-' }}</td>
                            <td>{{ $s->chip ?? '-' }}</td>
                            <td>{{ $s->ingenieros ?? '-' }}</td>
                            <td>{{ $s->trih ?? '-' }}</td>
                            <td>{{ $s->cable ?? '-' }}</td>
                            <td>{{ $s->estado }}</td>
                            <td>
                                @if($s->es_suministro ?? false)
                                    <span class="badge badge-amber">SUMINISTRO / VENTA</span>
                                @else
                                    {{ $s->servicio }}
                                @endif
                            </td>
                            <td class="td-sticky">
                                <div class="actions">
                                    <a href="{{ route('operations.sgc-ves.show', $s->id) }}" class="btn-row btn-row-outline">Ver</a>
                                    <a href="{{ route('operations.sgc-ves.index') }}/{{ $s->id }}/editar" class="btn-row btn-row-blue">Editar</a>
                                    <a href="/evidencias/nuevas?serviceId={{ $s->id }}" class="btn-row btn-row-dark">Evidencias</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="18" class="td-empty">No hay servicios registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</main>

@endsection
