
@extends('modules.systems.inventory-it.index')

@push('styles')
    <link href="{{ asset('assets/css/systems/inventoryit/dashboard.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/systems/inventoryit/inventory.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="dashboard-wrapper" id="inventoryApp">

    <!-- Encabezado -->
    <div class="dashboard-header">
        <div class="dashboard-title">
            <h1><i class="fas fa-boxes-stacked"></i> Inventario <span>Todos los equipos del parque tecnológico</span></h1>
        </div>
        <div class="dashboard-actions">
            <button type="button" class="btn btn-outline" id="btnExport">
                <i class="fas fa-file-excel"></i> Exportar CSV
            </button>
            <button type="button" class="btn btn-primary" id="btnAlta">
                <i class="fas fa-plus"></i> Alta de equipo
            </button>
        </div>
    </div>

    <!-- Filtros por tipo -->
    <div class="type-tabs" id="typeTabs" role="tablist" aria-label="Filtrar por tipo de equipo">
        <button type="button" class="type-tab active" data-tipo="" role="tab" aria-selected="true">
            <i class="fas fa-layer-group"></i> Todos
            <span class="type-count">{{ $equipos->count() }}</span>
        </button>
        @foreach($tipos as $slug => $t)
            @if(($conteoTipo[$slug] ?? 0) > 0)
                <button type="button" class="type-tab" data-tipo="{{ $slug }}" role="tab" aria-selected="false">
                    <i class="fas {{ $t['icon'] }}"></i> {{ $t['label'] }}
                    <span class="type-count">{{ $conteoTipo[$slug] }}</span>
                </button>
            @endif
        @endforeach
    </div>

    <!-- Tabla -->
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">
                <i class="fas fa-list"></i> Equipos
                <span class="result-count" id="resultCount">{{ $equipos->count() }} resultados</span>
            </div>
            <div class="panel-filters">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Código, serie, modelo o usuario...">
                </div>
                <select class="filter-select" id="estadoFilter" aria-label="Filtrar por estado">
                    <option value="">Todos los estados</option>
                    @foreach($estados as $slug => $s)
                        <option value="{{ $slug }}">{{ $s['label'] }}</option>
                    @endforeach
                </select>
                <select class="filter-select" id="garantiaFilter" aria-label="Filtrar por garantía">
                    <option value="">Cualquier garantía</option>
                    <option value="vigente">Vigente</option>
                    <option value="por_vencer">Por vencer</option>
                    <option value="vencida">Vencida</option>
                </select>
            </div>
        </div>

        <div class="table-wrapper">
            <table id="equiposTable">
                <thead>
                    <tr>
                        <th>Equipo</th>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Asignado a</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                        <th>Garantía</th>
                        <th class="th-actions"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($equipos as $e)
                        @php
                            $t = $tipos[$e['tipo']];
                            $s = $estados[$e['estado']];
                            $buscar = strtolower(implode(' ', [
                                $e['codigo'], $e['marca'], $e['modelo'], $e['serie'],
                                $e['asignado']['nombre'] ?? '', $e['asignado']['noEmp'] ?? '', $e['ubicacion'],
                            ]));
                        @endphp
                        <tr class="equipo-row"
                            data-id="{{ $e['id'] }}"
                            data-tipo="{{ $e['tipo'] }}"
                            data-estado="{{ $e['estado'] }}"
                            data-garantia="{{ $e['garantia_estado'] }}"
                            data-search="{{ $buscar }}">
                            <td>
                                <div class="equipo-cell">
                                    <span class="tipo-icon {{ $t['color'] }}"><i class="fas {{ $t['icon'] }}"></i></span>
                                    <div>
                                        <strong>{{ $e['marca'] }} {{ $e['modelo'] }}</strong><br>
                                        <span class="text-xs">S/N: {{ $e['serie'] }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="nowrap"><code class="codigo">{{ $e['codigo'] }}</code></td>
                            <td><span class="badge badge-type">{{ $t['singular'] }}</span></td>
                            <td>
                                @if($e['asignado'])
                                    <strong>{{ $e['asignado']['nombre'] }}</strong><br>
                                    <span class="text-xs">{{ $e['asignado']['area'] }}</span>
                                @else
                                    <span class="text-muted">Sin asignar</span>
                                @endif
                            </td>
                            <td>{{ $e['ubicacion'] }}</td>
                            <td><span class="badge {{ $s['badge'] }}">{{ $s['label'] }}</span></td>
                            <td class="nowrap">
                                <span class="garantia {{ $e['garantia_estado'] }}">
                                    <i class="fas fa-circle"></i>
                                    {{ \Carbon\Carbon::parse($e['garantia_hasta'])->format('d/m/Y') }}
                                </span>
                            </td>
                            <td class="td-actions">
                                <button type="button" class="icon-btn btn-ver" title="Ver detalle" aria-label="Ver detalle de {{ $e['codigo'] }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    <tr id="emptyRow" hidden>
                        <td colspan="8" class="empty-state">
                            <i class="fas fa-magnifying-glass"></i><br>
                            No hay equipos con esos filtros. Prueba quitando alguno.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============ Modal: detalle del equipo ============ -->
<div class="modal-backdrop" id="detalleModal" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="detalleTitulo">
        <div class="modal-header">
            <div class="modal-head-info">
                <span class="tipo-icon lg" id="dIcono"></span>
                <div>
                    <h2 id="detalleTitulo">—</h2>
                    <div class="modal-sub">
                        <code class="codigo" id="dCodigo"></code>
                        <span class="badge" id="dEstado"></span>
                    </div>
                </div>
            </div>
            <button type="button" class="icon-btn" data-close aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
        </div>

        <div class="modal-body">
            <section class="detail-section">
                <h3><i class="fas fa-circle-info"></i> Datos generales</h3>
                <dl class="detail-grid" id="dGeneral"></dl>
            </section>

            <section class="detail-section">
                <h3><i class="fas fa-microchip"></i> Características</h3>
                <dl class="detail-grid" id="dSpecs"></dl>
            </section>

            <section class="detail-section">
                <h3><i class="fas fa-user"></i> Asignación</h3>
                <dl class="detail-grid" id="dAsignacion"></dl>
            </section>

            <section class="detail-section">
                <h3><i class="fas fa-plug"></i> Accesorios entregados</h3>
                <div class="chip-list" id="dAccesorios"></div>
            </section>

            <section class="detail-section">
                <h3><i class="fas fa-note-sticky"></i> Observaciones IT</h3>
                <p class="detail-note" id="dObs"></p>
            </section>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline" id="btnResponsiva">
                <i class="fas fa-file-signature"></i> Imprimir responsiva
            </button>
            <button type="button" class="btn btn-primary" data-close>Cerrar</button>
        </div>
    </div>
</div>

<!-- ============ Modal: alta de equipo (maqueta) ============ -->
<div class="modal-backdrop" id="altaModal" hidden>
    <div class="modal modal-sm" role="dialog" aria-modal="true" aria-labelledby="altaTitulo">
        <div class="modal-header">
            <div class="modal-head-info">
                <span class="tipo-icon lg blue"><i class="fas fa-plus"></i></span>
                <div><h2 id="altaTitulo">Alta de equipo</h2></div>
            </div>
            <button type="button" class="icon-btn" data-close aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
        </div>
        <form id="altaForm" autocomplete="off">
            <div class="modal-body">
                <div class="form-grid">
                    <label class="field">
                        <span>Tipo de equipo</span>
                        <select name="tipo" required>
                            @foreach($tipos as $slug => $t)
                                <option value="{{ $slug }}">{{ $t['singular'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="field">
                        <span>Estado inicial</span>
                        <select name="estado">
                            @foreach($estados as $slug => $s)
                                @if(in_array($slug, ['disponible', 'asignado']))
                                    <option value="{{ $slug }}">{{ $s['label'] }}</option>
                                @endif
                            @endforeach
                        </select>
                    </label>
                    <label class="field"><span>Marca</span><input type="text" name="marca" placeholder="Lenovo" required></label>
                    <label class="field"><span>Modelo</span><input type="text" name="modelo" placeholder="ThinkPad E16 Gen 3" required></label>
                    <label class="field"><span>Número de serie</span><input type="text" name="serie" placeholder="PF6GFE6R" required></label>
                    <label class="field"><span>Fecha de compra</span><input type="date" name="fecha_compra"></label>
                    <label class="field"><span>Garantía hasta</span><input type="date" name="garantia_hasta"></label>
                    <label class="field"><span>Ubicación</span><input type="text" name="ubicacion" placeholder="Almacén IT"></label>
                    <label class="field field-wide"><span>Observaciones IT</span><textarea name="observaciones" rows="3" placeholder="Equipo nuevo."></textarea></label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-close>Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Guardar equipo</button>
            </div>
        </form>
    </div>
</div>

<!-- ============ Responsiva (solo se ve al imprimir) ============ -->
<div id="responsivaPrint" aria-hidden="true"></div>

<!-- Toast -->
<div class="toast" id="toast" role="status" aria-live="polite" hidden></div>
@endsection

@push('scripts')
    <script>
        window.INVENTORY_DATA = @json($equipos->keyBy('id'));
        window.INVENTORY_TIPOS = @json($tipos);
        window.INVENTORY_ESTADOS = @json($estados);
    </script>
    <script src="{{ asset('assets/js/systems/inventoryit/inventory.js') }}"></script>
@endpush
