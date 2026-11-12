@extends('modules.systems.inventory-it.index')

@push('styles')
    <link href="{{ asset('assets/css/systems/inventoryit/equipment.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="equipment-wrapper" id="inventoryApp">

        {{-- Resumen --}}
        <div class="equipment-summary">
            <div class="equipment-summary-card">
                <div class="equipment-summary-icon">
                    <i class="fas fa-laptop" aria-hidden="true"></i>
                </div>

                <div class="equipment-summary-info">
                    <h2>Total de equipos</h2>
                    <div class="equipment-summary-value" id="statTotal">0</div>
                    <p>Dispositivos, infraestructura y accesorios</p>
                </div>
            </div>

            <div class="equipment-summary-card">
                <div class="equipment-summary-icon green">
                    <i class="fas fa-circle-check" aria-hidden="true"></i>
                </div>

                <div class="equipment-summary-info">
                    <h2>Disponibles</h2>
                    <div class="equipment-summary-value" id="statDisponibles">0</div>
                    <p>Listos para entregar</p>
                </div>
            </div>

            <div class="equipment-summary-card">
                <div class="equipment-summary-icon amber">
                    <i class="fas fa-screwdriver-wrench" aria-hidden="true"></i>
                </div>

                 <div class="equipment-summary-info">
                    <h2>Mantenimiento y garantía</h2>
                    <div class="equipment-summary-value" id="statMantenimiento">0</div>
                    <p>En revisión o con el proveedor</p>
                </div>
            </div>

            <div class="equipment-summary-card">
                <div class="equipment-summary-icon rose">
                    <i class="fas fa-shield-halved" aria-hidden="true"></i>
                </div>

                <div class="equipment-summary-info">
                    <h2>Garantías por vencer</h2>
                    <div class="equipment-summary-value" id="statGarantia">0</div>
                    <p>Vencen en los próximos 90 días</p>
                </div>
            </div>
        </div>

        {{-- Inventario --}}
        <section id="viewInventario" class="equipment-content" aria-label="Inventario de equipos">
            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">
                        <i class="fas fa-list" aria-hidden="true"></i>
                        Equipos

                        <span class="result-count" id="resultCount" aria-live="polite"></span>
                    </h2>

                    <div class="panel-filters">
                        <div class="search-box">
                            <i class="fas fa-search" aria-hidden="true"></i>

                            <input type="search" id="searchInput" placeholder="Folio VES, serie, marca, estatus..."
                                aria-label="Buscar equipos">
                        </div>

                        {{-- Selector de categoría generado desde JavaScript --}}
                        <div class="type-tabs" id="typeTabs" role="group" aria-label="Filtrar por categoría de equipo">
                        </div>

                        {{-- 1. Filtro Estado del equipo --}}
                        <select class="filter-select" id="estadoFilter" aria-label="Filtrar por estado del equipo">
                            <option value="">Todos los estados</option>
                        </select>

                        {{-- 2. Filtro Condición --}}
                        <select class="filter-select" id="condicionFilter" aria-label="Filtrar por condición física">
                            <option value="">Cualquier condición</option>
                        </select>

                        {{-- 3. Filtro Estatus --}}
                        <select class="filter-select" id="estatusFilter" aria-label="Filtrar por estatus">
                            <option value="">Todos los estatus</option>
                        </select>

                        <select class="filter-select" id="garantiaFilter" aria-label="Filtrar por garantía">
                            <option value="">Cualquier garantía</option>
                            <option value="vigente">Vigente</option>
                            <option value="por_vencer">Por vencer</option>
                            <option value="vencida">Vencida</option>
                        </select>

                        <button type="button" class="btn btn-primary" id="btnAlta">
                            <i class="fas fa-plus" aria-hidden="true"></i>
                            Alta de equipo
                        </button>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table id="equiposTable" aria-label="Inventario de equipos">
                        <thead>
                            <tr>
                                <th scope="col">Equipo</th>
                                <th scope="col">Folio</th>
                                <th scope="col">Categoría</th>
                                <th scope="col">Tipo de equipo</th>
                                <th scope="col">Accesorios</th>
                                <th scope="col">Área</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Condición</th>
                                <th scope="col">Estatus</th>
                                <th scope="col">Garantía</th>

                                <th scope="col" class="th-actions">
                                    <span class="visually-hidden">Acciones</span>
                                </th>
                            </tr>
                        </thead>

                        <tbody id="equiposBody">
                            {{-- Las filas se cargan desde la base de datos vía JavaScript --}}
                        </tbody>
                    </table>
                </div>

                {{-- Pie y paginación --}}
                <div class="table-footer">
                    <label class="page-size" for="pageSize">
                        Mostrar

                        <select id="pageSize" aria-label="Registros por página">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>

                        registros
                    </label>

                    <p class="page-info" id="pageInfo" role="status" aria-live="polite"></p>

                    <nav class="pagination" id="paginacion" aria-label="Páginas del inventario"></nav>
                </div>
            </div>
        </section>

        {{-- Modal: detalle --}}
        <div class="modal-backdrop" id="detalleModal" hidden>
            <div class="modal modal-equipment" role="dialog" aria-modal="true" aria-labelledby="detalleTitulo"
                tabindex="-1">
                <div class="modal-header">
                    <div class="modal-head-info">
                        <span class="tipo-icon lg" id="dIcono" aria-hidden="true"></span>

                        <div>
                            <h2 id="detalleTitulo">Detalle del equipo</h2>

                            <div class="modal-sub">
                                <code class="codigo" id="dCodigo"></code>
                                <span class="badge" id="dEstado"></span>
                                <span class="badge" id="dCondicion"></span>
                                <span class="badge" id="dEstatus"></span>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="icon-btn" data-close aria-label="Cerrar detalle">
                        <i class="fas fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="modal-tabs" role="tablist" aria-label="Secciones del detalle">
                    <button type="button" class="modal-tab" id="detalleTabGeneral" role="tab" aria-selected="true"
                        aria-controls="detalleGeneral" data-section="general">
                        Datos generales
                    </button>

                    <button type="button" class="modal-tab" id="detalleTabSpecs" role="tab" aria-selected="false"
                        aria-controls="detalleSpecs" data-section="specs" tabindex="-1">
                        Características
                    </button>

                    <button type="button" class="modal-tab" id="detalleTabAccesorios" role="tab"
                        aria-selected="false" aria-controls="detalleAccesorios" data-section="accesorios"
                        tabindex="-1">
                        Accesorios y componentes
                    </button>

                    <button type="button" class="modal-tab" id="detalleTabObs" role="tab" aria-selected="false"
                        aria-controls="detalleObs" data-section="observaciones" tabindex="-1">
                        Observaciones
                    </button>
                </div>

                <div class="modal-body">
                    <section class="modal-section" id="detalleGeneral" role="tabpanel"
                        aria-labelledby="detalleTabGeneral" tabindex="0">
                        <h3>
                            <i class="fas fa-circle-info" aria-hidden="true"></i>
                            Datos generales
                        </h3>

                        <dl class="detail-grid" id="dGeneral"></dl>
                    </section>

                    <section class="modal-section" id="detalleSpecs" role="tabpanel" aria-labelledby="detalleTabSpecs"
                        tabindex="0" hidden>
                        <h3>
                            <i class="fas fa-microchip" aria-hidden="true"></i>
                            Características
                        </h3>

                        <dl class="detail-grid" id="dSpecs"></dl>
                    </section>

                    <section class="modal-section" id="detalleAccesorios" role="tabpanel"
                        aria-labelledby="detalleTabAccesorios" tabindex="0" hidden>
                        <div class="accessory-card">
                            <h3>
                                <i class="fas fa-boxes-stacked" aria-hidden="true"></i>
                                Accesorios y componentes incluidos con el equipo
                            </h3>

                            <p class="section-help">
                                Periféricos, monitores, discos, cargadores, cables y piezas entregadas junto con este
                                registro (con su marca y número de serie).
                            </p>

                            <div class="comp-detail-list" id="dAccesorios"></div>
                        </div>
                    </section>

                    <section class="modal-section" id="detalleObs" role="tabpanel" aria-labelledby="detalleTabObs"
                        tabindex="0" hidden>
                        <h3>
                            <i class="fas fa-note-sticky" aria-hidden="true"></i>
                            Observaciones IT
                        </h3>

                        <p class="detail-note" id="dObs"></p>
                    </section>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-close>
                        Cerrar
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal: alta --}}
        <div class="modal-backdrop" id="altaModal" hidden>
            <div class="modal modal-equipment" role="dialog" aria-modal="true" aria-labelledby="altaTitulo"
                tabindex="-1">
                <div class="modal-header">
                    <div class="modal-head-info">
                        <span class="tipo-icon lg blue" id="altaHeaderIcon" aria-hidden="true">
                            <i class="fas fa-plus"></i>
                        </span>

                        <div>
                            <h2 id="altaTitulo">Alta de equipo</h2>

                            <p class="modal-description">
                                Completa los datos por sección. El folio VES se asigna automáticamente.
                            </p>
                        </div>
                    </div>

                    <button type="button" class="icon-btn" data-close aria-label="Cerrar alta de equipo">
                        <i class="fas fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="modal-tabs" role="tablist" aria-label="Secciones del alta">
                    <button type="button" class="modal-tab" id="altaTabGeneral" role="tab" aria-selected="true"
                        aria-controls="altaGeneral" data-section="general">
                        Datos generales
                    </button>

                    <button type="button" class="modal-tab" id="altaTabSpecs" role="tab" aria-selected="false"
                        aria-controls="altaCaracteristicas" data-section="specs" tabindex="-1">
                        Características
                    </button>

                    <button type="button" class="modal-tab" id="altaTabAccesorios" role="tab"
                        aria-selected="false" aria-controls="altaAccesorios" data-section="accesorios" tabindex="-1">
                        Accesorios y componentes
                    </button>

                    <button type="button" class="modal-tab" id="altaTabObs" role="tab" aria-selected="false"
                        aria-controls="altaObservaciones" data-section="observaciones" tabindex="-1">
                        Observaciones
                    </button>
                </div>

                <form id="altaForm" autocomplete="off" novalidate>
                    @csrf

                    <div class="modal-body">

                        {{-- 1. Datos generales --}}
                        <section class="modal-section" id="altaGeneral" role="tabpanel"
                            aria-labelledby="altaTabGeneral">
                            <div class="form-grid form-grid-inner">

                                {{-- 1. Fecha de alta --}}
                                <label class="field">
                                    <span>Fecha de alta *</span>
                                    <input type="date" name="fecha_alta" required>
                                </label>

                                {{-- 2. Folio --}}
                                <label class="field">
                                    <span>Folio del equipo (próximo disponible)</span>

                                    <input type="text" name="codigo_preview" id="altaFolio" class="input-readonly"
                                        readonly tabindex="-1">
                                </label>

                                {{-- 3. Categoría de equipo --}}
                                <label class="field">
                                    <span>Categoría de equipo *</span>

                                    <select name="tipo" id="altaTipo" required></select>
                                </label>

                                {{-- 4. Tipo de equipo --}}
                                <label class="field" id="altaSubtipoWrap">
                                    <span id="altaSubtipoLabel">Tipo de equipo *</span>

                                    <select name="subtipo" id="altaSubtipo" required></select>
                                </label>

                                {{-- 5. Estado del equipo --}}
                                <label class="field">
                                    <span>Estado del equipo *</span>

                                    <select name="estado" id="altaEstado" required></select>
                                </label>

                                {{-- 6. Condición --}}
                                <label class="field">
                                    <span>Condición *</span>

                                    <select name="condicion" id="altaCondicion" required></select>
                                </label>

                                {{-- 7. Estatus --}}
                                <label class="field">
                                    <span>Estatus *</span>

                                    <select name="estatus" id="altaEstatus" required></select>
                                </label>

                                {{-- 8. Área a la que pertenece (Vinculada al modelo Area de RH) --}}
                                <label class="field">
                                    <span>Área a la que pertenece</span>

                                    <select name="area_id" id="altaArea">
                                        <option value="">Sin área asignada</option>
                                        @foreach ($areas as $area)
                                            <option value="{{ $area->id }}">{{ $area->name }}</option>
                                        @endforeach
                                    </select>
                                </label>

                                {{-- 9. Marca --}}
                                <label class="field">
                                    <span>Marca *</span>

                                    <input type="text" name="marca" id="altaMarca" placeholder="Ej. Lenovo"
                                        required>
                                </label>

                                {{-- 10. Modelo --}}
                                <label class="field">
                                    <span>Modelo *</span>

                                    <input type="text" name="modelo" id="altaModelo"
                                        placeholder="Ej. ThinkPad E16 Gen 3" required>
                                </label>

                                {{-- 11. Número de serie --}}
                                <label class="field" id="altaSerieSimpleWrap">
                                    <span id="altaSerieLabel">Número de serie *</span>

                                    <input type="text" name="serie" id="altaSerie" placeholder="PF6GFE6R" required>
                                </label>

                                {{-- Series múltiples cuando el activo dado de alta es un combo o kit --}}
                                <div class="field-group-kit field-wide" id="altaSerieKitWrap" hidden>
                                    <div class="kit-header">
                                        <i class="fas fa-keyboard" aria-hidden="true"></i>
                                        <span>Números de serie por pieza del paquete</span>
                                    </div>

                                    <div class="form-grid form-grid-inner">
                                        <label class="field">
                                            <span id="altaSerieTecladoLabel">N/S Teclado *</span>
                                            <input type="text" name="serie_teclado" id="altaSerieTeclado"
                                                placeholder="Ej. SN-KB-99281">
                                        </label>

                                        <label class="field">
                                            <span id="altaSerieMouseLabel">N/S Mouse *</span>
                                            <input type="text" name="serie_mouse" id="altaSerieMouse"
                                                placeholder="Ej. SN-MS-99282">
                                        </label>

                                        <label class="field field-wide">
                                            <span>N/S del receptor USB o de la caja (opcional)</span>
                                            <input type="text" name="serie_receptor" id="altaSerieReceptor"
                                                placeholder="Ej. SN-RCV-0012">
                                        </label>
                                    </div>
                                </div>

                                {{-- 12. Garantía --}}
                                <label class="field">
                                    <span>Garantía hasta (opcional)</span>
                                    <input type="date" name="garantia_hasta">
                                </label>
                            </div>
                        </section>

                        {{-- 2. Características --}}
                        <section class="modal-section" id="altaCaracteristicas" role="tabpanel"
                            aria-labelledby="altaTabSpecs" hidden>
                            <h3>
                                <i class="fas fa-microchip" aria-hidden="true"></i>
                                Características técnicas
                                <span id="altaTipoLabel"></span>
                            </h3>

                            <div class="form-grid form-grid-inner" id="altaSpecs"></div>
                        </section>

                        {{-- 3. Accesorios y componentes con captura de marca y número de serie --}}
                        <section class="modal-section" id="altaAccesorios" role="tabpanel"
                            aria-labelledby="altaTabAccesorios" hidden>
                            <div class="accessory-card">
                                <h3>
                                    <i class="fas fa-boxes-stacked" aria-hidden="true"></i>
                                    Agregar accesorios y componentes incluidos
                                </h3>

                                <p class="section-help" id="altaExtrasAyuda">
                                    Selecciona qué periféricos, cables, discos o piezas trae el equipo para registrar su
                                    marca y número de serie.
                                </p>

                                <label class="field" for="altaExtras">
                                    <span>Seleccionar accesorio o componente</span>

                                    <select id="altaExtras" aria-describedby="altaExtrasAyuda"></select>
                                </label>

                                {{-- Panel dinámico para capturar marca, modelo y N/S del accesorio seleccionado --}}
                                <div class="comp-builder" id="compBuilder" hidden>
                                    <div class="comp-builder-header">
                                        <span class="comp-builder-title" id="compBuilderTitle">
                                            <i class="fas fa-keyboard" aria-hidden="true"></i>
                                            Datos del componente
                                        </span>
                                        <span class="comp-builder-hint" id="compBuilderHint"></span>
                                    </div>

                                    <div class="form-grid form-grid-inner">
                                        <label class="field" id="compMarcaWrap">
                                            <span id="compMarcaLabel">Marca</span>
                                            <input type="text" id="compMarca" placeholder="Ej. Logitech">
                                        </label>

                                        <label class="field" id="compModeloWrap">
                                            <span id="compModeloLabel">Modelo o detalle</span>
                                            <input type="text" id="compModelo" placeholder="Ej. M220">
                                        </label>

                                        {{-- Serie única del componente --}}
                                        <label class="field field-wide" id="compSerieSimpleWrap">
                                            <span id="compSerieLabel">Número de serie *</span>
                                            <input type="text" id="compSerie" placeholder="Ej. SN-8839201">
                                        </label>

                                        {{-- Series dobles cuando el accesorio incluido es un combo de teclado y mouse --}}
                                        <div class="form-grid form-grid-inner field-wide" id="compSerieComboWrap" hidden>
                                            <label class="field">
                                                <span>N/S del teclado *</span>
                                                <input type="text" id="compSerieTeclado" placeholder="Ej. SN-KB-4410">
                                            </label>

                                            <label class="field">
                                                <span>N/S del mouse *</span>
                                                <input type="text" id="compSerieMouse" placeholder="Ej. SN-MS-4411">
                                            </label>

                                            <label class="field field-wide">
                                                <span>N/S del receptor USB (opcional)</span>
                                                <input type="text" id="compSerieReceptor" placeholder="Ej. SN-RCV-09">
                                            </label>
                                        </div>
                                    </div>

                                    <p class="comp-builder-error" id="compBuilderError" hidden></p>

                                    <div class="comp-builder-actions">
                                        <button type="button" class="btn btn-outline btn-sm" id="btnCancelarComp">
                                            Cancelar
                                        </button>

                                        <button type="button" class="btn btn-primary btn-sm" id="btnConfirmarComp">
                                            <i class="fas fa-plus" aria-hidden="true"></i>
                                            Agregar al equipo
                                        </button>
                                    </div>
                                </div>

                                {{-- Lista de componentes y accesorios ya agregados al equipo --}}
                                <div class="comp-added-list" id="altaExtrasSeleccionados" role="list"
                                    aria-label="Accesorios y componentes agregados"></div>

                                <p class="section-help" id="altaExtrasVacio">
                                    No has agregado accesorios ni componentes a este equipo.
                                </p>

                                <p class="visually-hidden" id="altaExtrasEstado" role="status" aria-live="polite"></p>
                            </div>
                        </section>

                        {{-- 4. Observaciones --}}
                        <section class="modal-section" id="altaObservaciones" role="tabpanel"
                            aria-labelledby="altaTabObs" hidden>
                            <label class="field field-wide">
                                <span>Observaciones IT</span>

                                <textarea name="observaciones" rows="6"
                                    placeholder="Estado físico, detalles del paquete, ubicación o notas de entrega."></textarea>
                            </label>
                        </section>
                    </div>

                    <div class="modal-footer alta-footer">
                        <span class="step-info" id="altaPaso" role="status" aria-live="polite"></span>

                        <button type="button" class="btn btn-outline" data-close>
                            Cancelar
                        </button>

                        <button type="button" class="btn btn-outline" id="altaAnterior" hidden>
                            Anterior
                        </button>

                        <button type="button" class="btn btn-primary" id="altaSiguiente">
                            Siguiente
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </button>

                        <button type="submit" class="btn btn-primary" id="altaGuardar" hidden>
                            <i class="fas fa-floppy-disk" aria-hidden="true"></i>
                            Guardar equipo
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Notificaciones --}}
        <div class="toast" id="toast" role="status" aria-live="polite" hidden></div>
    </div>
@endsection

@push('scripts')
    <script>
        window.VES_INVENTORY_CONFIG = {
            items: @json($initialEquipments ?? []),
            storeUrl: @json(route('systems.inventory-it.equipment.store')),
            csrfToken: @json(csrf_token()),
        };
    </script>
    <script src="{{ asset('assets/js/systems/inventoryit/equipment.js') }}" defer></script>
@endpush
