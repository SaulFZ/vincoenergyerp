
{{-- ========================================== --}}
{{-- 1. FILTROS EXCLUSIVOS DE LA VISTA: LISTA   --}}
{{-- ========================================== --}}
<section class="filter-section no-print">
    <form id="filter-form" action="{{ route('operations.sgc.services') }}" method="GET">
        <input type="hidden" name="view" value="list">

        <div class="filter-grid">

            <div class="filter-group">
                <label class="filter-label">Buscar</label>
                <input type="text" name="q" placeholder="OS, cliente, pozo..." class="filter-input" value="{{ request('q') }}">
            </div>

            <div class="filter-group">
                <label class="filter-label">Cliente</label>
                <select name="cliente" class="filter-input">
                    <option value="" selected>Todos los clientes</option>
                    @foreach(['BAKER HUGHES','BLUEMARINE','CYDSA','DIXTONE','GEOTERMIA','GSM','GSM/BLOQUES','H. ENERGY','HALLIBURTON','IPS','MEXXUS','OPEX','PEMEX','PERENCO','PETROFOR','PIMEX','SIN CLIENTE','SLB','WDEA','WDEA/H. ENERGY'] as $cli)
                        <option value="{{ $cli }}" {{ request('cliente') === $cli ? 'selected' : '' }}>{{ $cli }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Pozo</label>
                <select name="pozo" class="filter-input">
                    <option value="" selected>Todos los pozos</option>
                    @foreach(['5 PRESIDENTES 165','5 PRESIDENTES 935','AYATSIL 131 REE','AYATSIL 132','BACAB 3','BLASILLO 505','CIBIX 35','IXACHI 54','IXACHI 69','OGARRIO 1480','SAMARIA 668','TUPILCO 143'] as $pz)
                        <option value="{{ $pz }}" {{ request('pozo') === $pz ? 'selected' : '' }}>{{ $pz }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group custom-dropdown">
                <label class="filter-label">Años</label>
                <div class="filter-input dropdown-trigger" id="year-dropdown-btn">
                    <span>Seleccionar años</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-menu" id="year-dropdown-menu">
                    <label class="dropdown-item"><input type="checkbox" name="year[]" value="2026" checked> 2026</label>
                    <label class="dropdown-item"><input type="checkbox" name="year[]" value="2025"> 2025</label>
                    <label class="dropdown-item"><input type="checkbox" name="year[]" value="2024"> 2024</label>
                </div>
            </div>

            <div class="filter-group">
                <label class="filter-label">Trimestre</label>
                <select name="trimestre" class="filter-input">
                    <option value="" selected>Todos</option>
                    <option value="Q1" {{ request('trimestre') === 'Q1' ? 'selected' : '' }}>Q1 · Ene-Mar</option>
                    <option value="Q2" {{ request('trimestre') === 'Q2' ? 'selected' : '' }}>Q2 · Abr-Jun</option>
                    <option value="Q3" {{ request('trimestre') === 'Q3' ? 'selected' : '' }}>Q3 · Jul-Sep</option>
                    <option value="Q4" {{ request('trimestre') === 'Q4' ? 'selected' : '' }}>Q4 · Oct-Dic</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Estado</label>
                <select name="estado" class="filter-input">
                    <option value="" selected>Todos</option>
                    @foreach(['TERMINADO','CANCELADO','OPERACIÓN CANCELADA','SERVICIO CANCELADO','CANCELACION'] as $e)
                        <option value="{{ $e }}" {{ request('estado') === $e ? 'selected' : '' }}>{{ $e }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label" style="opacity: 0;">Acciones</label>
                <div class="filter-actions">
                    <button type="button" onclick="window.print()" class="btn-icon btn-pdf" title="Exportar a PDF">
                        <i class="fas fa-file-pdf"></i>
                    </button>
                    <a href="{{ route('operations.sgc.services', ['view' => 'list']) }}" class="btn-icon btn-clear tab-link" title="Limpiar filtros">
                        <i class="fas fa-undo-alt"></i>
                    </a>
                </div>
            </div>

        </div>
    </form>
</section>

{{-- ========================================== --}}
{{-- 2. CONTENIDO DE LA VISTA                   --}}
{{-- ========================================== --}}
<div class="tab-list">

    <div class="summary-box">
        <div class="summary-stats">
            <span class="summary-primary">Servicios operativos: {{ $totalOperativos ?? 445 }}</span>
            <span class="summary-divider">|</span>
            <span class="summary-amber">Suministros: {{ $totalSuministros ?? 24 }}</span>
            <span class="summary-divider">|</span>
            <span>OS mostradas: {{ $totalMostradas ?? 469 }}</span>
        </div>
    </div>

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
                    <th class="th-sticky no-print">Acciones</th>
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
                        <td class="td-sticky no-print">
                            <div class="actions">
                                <a href="{{ route('operations.sgc.services', ['view' => 'new_service', 'mode' => 'view', 'id' => $s->id]) }}"
                                    class="btn-row btn-row-outline tab-link">Ver</a>
                                <a href="{{ route('operations.sgc.services', ['view' => 'new_service', 'mode' => 'edit', 'id' => $s->id]) }}"
                                    class="btn-row btn-row-blue tab-link">Editar</a>
                                <a href="/evidencias/nuevas?serviceId={{ $s->id }}"
                                    class="btn-row btn-row-dark">Evidencias</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="td-bold">VES-WL-OS-1550</td>
                        <td>25/3/2026</td>
                        <td class="td-dark">26/3/2026</td>
                        <td class="td-npt">0 h</td>
                        <td>WDEA/H. ENERGY</td>
                        <td>OGARRIO 903 (1 INTER)</td>
                        <td>VH-212</td>
                        <td>VH-210</td>
                        <td>-</td>
                        <td>ECP 1410-301</td>
                        <td>VH-107</td>
                        <td>-</td>
                        <td>LUIS ANTONIO HERNANDEZ JIMENEZ</td>
                        <td>Sí</td>
                        <td>-</td>
                        <td>TERMINADO</td>
                        <td>REGISTRO PNN/PNG</td>
                        <td class="td-sticky no-print">
                            <div class="actions">
                                <a href="{{ route('operations.sgc.services', ['view' => 'new_service', 'mode' => 'view', 'id' => 141]) }}"
                                    class="btn-row btn-row-outline tab-link">Ver</a>
                                <a href="{{ route('operations.sgc.services', ['view' => 'new_service', 'mode' => 'edit', 'id' => 141]) }}"
                                    class="btn-row btn-row-blue tab-link">Editar</a>
                                <a href="/evidencias/nuevas?serviceId=141"
                                    class="btn-row btn-row-dark">Evidencias</a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="td-bold">VES-WL-OS-1547</td>
                        <td>17/3/2026</td>
                        <td class="td-dark">23/3/2026</td>
                        <td class="td-npt td-npt-alert">6 h</td>
                        <td>GEOTERMIA</td>
                        <td>CERRO PRIETO 1-435D (5Inter)</td>
                        <td>VH-209</td>
                        <td>-</td>
                        <td>VH-207</td>
                        <td>-</td>
                        <td>VH-110</td>
                        <td>Chip H -P938</td>
                        <td>NOEL ARTEMIO GOMEZ JIMENEZ</td>
                        <td>Sí</td>
                        <td>-</td>
                        <td>TERMINADO</td>
                        <td>REGISTRO PT</td>
                        <td class="td-sticky no-print">
                            <div class="actions">
                                <a href="{{ route('operations.sgc.services', ['view' => 'new_service', 'mode' => 'view', 'id' => 138]) }}"
                                    class="btn-row btn-row-outline tab-link">Ver</a>
                                <a href="{{ route('operations.sgc.services', ['view' => 'new_service', 'mode' => 'edit', 'id' => 138]) }}"
                                    class="btn-row btn-row-blue tab-link">Editar</a>
                                <a href="/evidencias/nuevas?serviceId=138"
                                    class="btn-row btn-row-dark">Evidencias</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
