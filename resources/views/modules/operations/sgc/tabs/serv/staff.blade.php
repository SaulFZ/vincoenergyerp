{{-- ========================================== --}}
{{-- 1. FILTROS EXCLUSIVOS DE LA VISTA: STAFF  --}}
{{-- ========================================== --}}
<section class="filter-section no-print">
    <form id="filter-form" action="{{ route('operations.sgc.services') }}" method="GET">
        <input type="hidden" name="view" value="staff">

        <div class="filter-grid">

            <div class="filter-group">
                <label class="filter-label">Buscar</label>
                <input type="text" name="q" placeholder="Nombre del técnico..." class="filter-input" value="{{ request('q') }}">
            </div>

            <div class="filter-group">
                <label class="filter-label">Años</label>
                <select name="year" class="filter-input">
                    <option value="2026" {{ request('year', '2026') === '2026' ? 'selected' : '' }}>2026</option>
                    <option value="2025" {{ request('year') === '2025' ? 'selected' : '' }}>2025</option>
                    <option value="2024" {{ request('year') === '2024' ? 'selected' : '' }}>2024</option>
                </select>
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
                <label class="filter-label" style="opacity: 0;">Acciones</label>
                <div class="filter-actions">
                    <button type="button" onclick="window.print()" class="btn-icon btn-pdf" title="Exportar a PDF">
                        <i class="fas fa-file-pdf"></i>
                    </button>
                    <a href="{{ route('operations.sgc.services', ['view' => 'staff']) }}" class="btn-icon btn-clear" title="Limpiar filtros">
                        <i class="fas fa-undo-alt"></i>
                    </a>
                </div>
            </div>

        </div>
    </form>
</section>

{{-- ========================================== --}}
{{-- 2. CONTENIDO DE LA VISTA                  --}}
{{-- ========================================== --}}
<div class="tab-staff">

    <div class="summary-box">
        <div class="summary-stats">
            <span class="summary-primary">Personal activo: {{ $totalPersonal ?? 0 }}</span>
            <span class="summary-divider">|</span>
            <span>Servicios asignados: {{ $totalAsignados ?? 0 }}</span>
        </div>
    </div>

    <div class="table-wrap">
        <table class="svc-table">
            <thead>
                <tr>
                    <th>Ingeniero</th>
                    <th>Puesto</th>
                    <th>Servicios</th>
                    <th>Horas Op</th>
                    <th>Horas NPT</th>
                    <th>SQIR</th>
                    <th class="th-sticky">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($personal ?? [] as $p)
                    <tr>
                        <td class="td-bold">{{ $p->nombre }}</td>
                        <td>{{ $p->puesto ?? '-' }}</td>
                        <td>{{ $p->total_servicios ?? 0 }}</td>
                        <td>{{ $p->horas_op ?? 0 }} h</td>
                        <td class="td-npt {{ ($p->horas_npt ?? 0) > 0 ? 'td-npt-alert' : '' }}">
                            {{ $p->horas_npt ?? 0 }} h
                        </td>
                        <td class="td-dark">{{ $p->sqir ?? '-' }}%</td>
                        <td class="td-sticky">
                            <div class="actions">
                                <a href="#" class="btn-row btn-row-outline">Ver</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="td-empty">No hay personal registrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
