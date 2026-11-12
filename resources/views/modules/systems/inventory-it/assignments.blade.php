@extends('modules.systems.inventory-it.index')

@push('styles')
    <link href="{{ asset('assets/css/systems/inventoryit/assignments.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="assignment-wrapper" id="assignmentApp">

        {{-- Indicadores --}}
        <div class="ia-stats">
            <article class="ia-stat">
                <span class="ia-stat-icon">
                    <i class="fas fa-user-check" aria-hidden="true"></i>
                </span>

                <div>
                    <h2>Asignaciones activas</h2>
                    <strong id="iaStatActive">0</strong>
                    <p>Entregas vigentes</p>
                </div>
            </article>

            <article class="ia-stat">
                <span class="ia-stat-icon green">
                    <i class="fas fa-laptop" aria-hidden="true"></i>
                </span>

                <div>
                    <h2>Equipos entregados</h2>
                    <strong id="iaStatEquipment">0</strong>
                    <p>Asignados o en préstamo</p>
                </div>
            </article>

            <article class="ia-stat">
                <span class="ia-stat-icon blue">
                    <i class="fas fa-box-open" aria-hidden="true"></i>
                </span>

                <div>
                    <h2>Disponibles</h2>
                    <strong id="iaStatAvailable">0</strong>
                    <p>Listos para asignar</p>
                </div>
            </article>

            <article class="ia-stat">
                <span class="ia-stat-icon amber">
                    <i class="fas fa-clock" aria-hidden="true"></i>
                </span>

                <div>
                    <h2>Préstamos vencidos</h2>
                    <strong id="iaStatOverdue">0</strong>
                    <p>Devolución pendiente</p>
                </div>
            </article>
        </div>

        {{-- Listado --}}
        <section class="ia-panel" aria-labelledby="iaPageTitle">
            <header class="ia-panel-header">
                <div>
                    <h2 id="iaPageTitle">Asignaciones de equipos</h2>
                    <p>
                        Consulta las entregas y los equipos de cada responsable.
                    </p>
                </div>

                <button type="button" class="ia-btn primary" id="iaNew">
                    <i class="fas fa-plus" aria-hidden="true"></i>
                    Nueva asignación
                </button>
            </header>

            <div class="ia-toolbar">
                <label class="ia-search">
                    <i class="fas fa-search" aria-hidden="true"></i>

                    <input type="search" id="iaQuery" placeholder="Folio, responsable, equipo o serie..."
                        aria-label="Buscar asignaciones">
                </label>

                <select id="iaStatusFilter" aria-label="Filtrar por estado">
                    <option value="">Todos los estados</option>
                    <option value="activa">Activas</option>
                    <option value="devuelta">Devueltas</option>
                    <option value="vencida">Préstamos vencidos</option>
                </select>

                <span class="ia-muted" id="iaResultCount" aria-live="polite"></span>
            </div>

            <div class="ia-table-scroll">
                <table class="ia-table" aria-label="Asignaciones de equipos">
                    <thead>
                        <tr>
                            <th scope="col">Folio</th>
                            <th scope="col">Responsable</th>
                            <th scope="col">Equipos</th>
                            <th scope="col">Tipo de entrega</th>
                            <th scope="col">Fecha de entrega</th>
                            <th scope="col">Devolución prevista</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="ia-actions">Acciones</th>
                        </tr>
                    </thead>

                    <tbody id="iaRows"></tbody>
                </table>
            </div>

            <footer class="ia-table-footer">
                <span id="iaPageInfo" class="ia-muted" role="status"></span>

                <div class="ia-page-buttons">
                    <button type="button" class="ia-btn" id="iaPrev" aria-label="Página anterior">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                    </button>

                    <button type="button" class="ia-btn" id="iaNext" aria-label="Página siguiente">
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </button>
                </div>
            </footer>
        </section>

        {{-- Nueva asignación --}}
        <dialog class="ia-dialog" id="iaCreateDialog" aria-labelledby="iaCreateTitle">
            <header class="ia-dialog-header">
                <div>
                    <h2 id="iaCreateTitle">Nueva asignación</h2>
                    <p>
                        Selecciona al responsable y los equipos que recibirá.
                    </p>
                </div>

                <button type="button" class="ia-icon-btn" data-close aria-label="Cerrar nueva asignación">
                    <i class="fas fa-xmark" aria-hidden="true"></i>
                </button>
            </header>

            <form id="iaCreateForm" autocomplete="off" novalidate>
                @csrf

                <div class="ia-body">

                    {{-- Responsable --}}
                    <section class="ia-section" aria-labelledby="iaResponsibleTitle">
                        <h3 id="iaResponsibleTitle">
                            <span class="ia-step">1</span>
                            Responsable de los equipos
                        </h3>

                        <label class="ia-field">
                            <span>Buscar colaborador *</span>

                            <input type="search" id="iaPersonSearch" placeholder="Nombre o número de empleado" required
                                aria-describedby="iaPersonHelp" aria-controls="iaPersonResults">

                            <small id="iaPersonHelp">
                                Elige un colaborador de los resultados de búsqueda.
                            </small>
                        </label>

                        <div class="ia-person-results" id="iaPersonResults" hidden></div>

                        <div class="ia-person-card" id="iaPersonCard" hidden></div>
                    </section>

                    {{-- Datos de entrega --}}
                    <section class="ia-section" aria-labelledby="iaDeliveryTitle">
                        <h3 id="iaDeliveryTitle">
                            <span class="ia-step">2</span>
                            Datos de la entrega
                        </h3>

                        <div class="ia-grid">
                            <label class="ia-field">
                                <span>Folio de asignación</span>

                                <input type="text" id="iaFolio" readonly class="ia-readonly">
                            </label>

                            <label class="ia-field">
                                <span>Fecha de entrega *</span>

                                <input type="date" id="iaDeliveryDate" required>
                            </label>

                            <label class="ia-field">
                                <span>Tipo de entrega *</span>

                                <select id="iaDeliveryType" required>
                                    <option value="asignacion">
                                        Asignación
                                    </option>

                                    <option value="prestamo">
                                        Préstamo temporal
                                    </option>
                                </select>
                            </label>

                            <label class="ia-field" id="iaExpectedWrap" hidden>
                                <span>Devolución prevista *</span>

                                <input type="date" id="iaExpectedDate">
                            </label>
                        </div>
                    </section>

                    {{-- Equipos --}}
                    <section class="ia-section" aria-labelledby="iaEquipmentTitle">
                        <h3 id="iaEquipmentTitle">
                            <span class="ia-step">3</span>
                            Equipos a entregar
                            <span class="ia-count" id="iaSelectionCount">0</span>
                        </h3>

                        <p class="ia-help">
                            Solo se muestran activos disponibles.
                            Los accesorios incluidos se entregan junto con su equipo.
                        </p>

                        <label class="ia-search">
                            <i class="fas fa-search" aria-hidden="true"></i>

                            <input type="search" id="iaAssetSearch"
                                placeholder="Buscar por folio VES, marca, modelo o serie"
                                aria-label="Buscar equipos disponibles">
                        </label>

                        <div class="ia-picker" id="iaAssetResults"></div>

                        <p class="ia-error" id="iaSelectionError" role="alert" hidden>
                            Selecciona al menos un equipo.
                        </p>

                        <div class="ia-selected" id="iaSelected"></div>
                    </section>

                    <label class="ia-field">
                        <span>Observaciones de entrega</span>

                        <textarea id="iaDeliveryNotes" rows="3" maxlength="1000"
                            placeholder="Condiciones de entrega o indicaciones para el responsable..."></textarea>
                    </label>

                    <p class="ia-error" id="iaCreateError" role="alert" hidden></p>
                </div>

                <footer class="ia-dialog-footer">
                    <button type="button" class="ia-btn" data-close>
                        Cancelar
                    </button>

                    <button type="submit" class="ia-btn primary">
                        <i class="fas fa-check" aria-hidden="true"></i>
                        Registrar asignación
                    </button>
                </footer>
            </form>
        </dialog>

        {{-- Detalle --}}
        <dialog class="ia-dialog" id="iaDetailDialog" aria-labelledby="iaDetailTitle">
            <header class="ia-dialog-header">
                <div>
                    <h2 id="iaDetailTitle">Detalle de la asignación</h2>
                    <p id="iaDetailSubtitle"></p>
                </div>

                <button type="button" class="ia-icon-btn" data-close aria-label="Cerrar detalle">
                    <i class="fas fa-xmark" aria-hidden="true"></i>
                </button>
            </header>

            <div class="ia-body">
                <dl class="ia-detail-grid" id="iaDetailData"></dl>

                <section class="ia-section">
                    <h3>Equipos y accesorios entregados</h3>

                    <div class="ia-item-list" id="iaDetailItems"></div>
                </section>

                <section class="ia-section">
                    <h3>Observaciones de entrega</h3>

                    <p class="ia-note" id="iaDetailNotes"></p>
                </section>

                <section class="ia-section" id="iaReturnInfo" hidden>
                    <h3>Devolución</h3>

                    <p class="ia-note" id="iaReturnNotes"></p>
                </section>
            </div>

            <footer class="ia-dialog-footer">
                <button type="button" class="ia-btn" data-close>
                    Cerrar
                </button>

                <button type="button" class="ia-btn primary" id="iaDetailReturn">
                    <i class="fas fa-rotate-left" aria-hidden="true"></i>
                    Registrar devolución
                </button>
            </footer>
        </dialog>

        {{-- Devolución completa --}}
        <dialog class="ia-dialog ia-dialog-small" id="iaReturnDialog" aria-labelledby="iaReturnTitle">
            <header class="ia-dialog-header">
                <div>
                    <h2 id="iaReturnTitle">Registrar devolución</h2>
                    <p id="iaReturnSubtitle"></p>
                </div>

                <button type="button" class="ia-icon-btn" data-close aria-label="Cerrar devolución">
                    <i class="fas fa-xmark" aria-hidden="true"></i>
                </button>
            </header>

            <form id="iaReturnForm" novalidate>
                <div class="ia-body">
                    <p class="ia-help">
                        Se devolverán todos los equipos de esta entrega,
                        junto con sus accesorios incluidos.
                    </p>

                    <label class="ia-field">
                        <span>Fecha de devolución *</span>

                        <input type="date" id="iaReturnedDate" required>
                    </label>

                    <div class="ia-return-list" id="iaReturnItems"></div>

                    <label class="ia-field">
                        <span>Observaciones de devolución</span>

                        <textarea id="iaReturnedNotes" rows="3" maxlength="1000"
                            placeholder="Anota el estado en que se reciben los equipos..."></textarea>
                    </label>

                    <p class="ia-error" id="iaReturnError" role="alert" hidden></p>
                </div>

                <footer class="ia-dialog-footer">
                    <button type="button" class="ia-btn" data-close>
                        Cancelar
                    </button>

                    <button type="submit" class="ia-btn primary">
                        Confirmar devolución
                    </button>
                </footer>
            </form>
        </dialog>

        <div class="ia-toast" id="iaToast" role="status" aria-live="polite" hidden></div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/systems/inventoryit/assignments.js') }}" defer></script>
@endpush
