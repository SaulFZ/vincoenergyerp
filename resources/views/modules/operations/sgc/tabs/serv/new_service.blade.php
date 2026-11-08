
@php
    $mode      = request('mode', 'create'); // 'create' | 'edit' | 'view'
    $serviceId = request('id');
    $isEdit    = $mode === 'edit';
    $isView    = $mode === 'view';
@endphp

{{-- ========================================== --}}
{{-- 1. TOOLBAR EXCLUSIVO DE LA VISTA: NUEVO    --}}
{{-- ========================================== --}}
<section class="filter-section no-print">
    <div class="view-toolbar">
        <a href="{{ route('operations.sgc.services', ['view' => 'list']) }}" class="btn btn-outline btn-sm service-modal-close">
            <i class="fas fa-arrow-left"></i> Volver a servicios
        </a>

        <div class="view-toolbar-actions">
            @if ($isView)
                <a href="{{ route('operations.sgc.services', ['view' => 'new_service', 'mode' => 'edit', 'id' => $serviceId]) }}"
                    class="btn btn-primary btn-sm service-modal-trigger">
                    <i class="fas fa-pen"></i> Editar este servicio
                </a>
                <button type="button" onclick="window.print()" class="btn btn-outline btn-sm">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            @endif

            <a class="btn btn-outline btn-sm" href="/evidencias?context=servicio-datos-generales{{ $serviceId ? '&serviceId='.$serviceId : '' }}">
                <i class="fas fa-paperclip"></i> Ver evidencias
            </a>

            @if (!$isView)
                <a class="btn btn-primary btn-sm" href="/evidencias/nueva?context=servicio-datos-generales{{ $serviceId ? '&serviceId='.$serviceId : '' }}">
                    <i class="fas fa-plus"></i> Agregar evidencia
                </a>
            @endif
        </div>
    </div>
</section>

{{-- ========================================== --}}
{{-- 2. CONTENIDO DE LA VISTA                   --}}
{{-- ========================================== --}}
<div class="tab-new-service {{ $isView ? 'is-view-mode' : '' }}" data-mode="{{ $mode }}" data-id="{{ $serviceId }}">

    {{-- 2.1 HEADER DINÁMICO SEGÚN MODO --}}
    <div class="wizard-header">
        <div class="wizard-header-icon">
            @if ($isView)
                <i class="fas fa-eye"></i>
            @elseif ($isEdit)
                <i class="fas fa-pen-to-square"></i>
            @else
                <i class="fas fa-clipboard-list"></i>
            @endif
        </div>
        <div>
            <div class="wizard-title-row">
                <h1 class="wizard-title">
                    @if ($isView)
                        Detalle del Servicio {{ $serviceId ? '#'.$serviceId : '' }}
                    @elseif ($isEdit)
                        Editar Servicio {{ $serviceId ? '#'.$serviceId : '' }}
                    @else
                        Nuevo Servicio
                    @endif
                </h1>

                @if ($isView)
                    <span class="wizard-badge wizard-badge-slate">Solo lectura</span>
                @elseif ($isEdit)
                    <span class="wizard-badge wizard-badge-amber">Modo Edición</span>
                @else
                    <span class="wizard-badge wizard-badge-green">Nuevo</span>
                @endif
            </div>
            <p class="wizard-subtitle">
                @if ($isView)
                    Vista completa de datos generales, servicios planeados, personal, TRIH/cable, NPT y condiciones del pozo.
                @elseif ($isEdit)
                    Modifica la información operativa paso a paso y guarda los cambios de la orden de servicio.
                @else
                    Precarga general, personal, activos, cable, TRIH inicial y datos del pozo.
                @endif
            </p>
        </div>
    </div>

    {{-- 2.2 STEPPER (Se oculta automáticamente en modo "Ver" gracias a .is-view-mode) --}}
    <div class="stepper-panel no-print">
        <div class="stepper-track">
            <button type="button" class="step-nav" data-step="1">
                <span class="step-bubble is-active">1</span>
                <span class="step-label is-active">Datos<br>Generales</span>
            </button>
            <div class="step-line" data-line="1"></div>

            <button type="button" class="step-nav" data-step="2">
                <span class="step-bubble">2</span>
                <span class="step-label">Servicios<br>y Personal</span>
            </button>
            <div class="step-line" data-line="2"></div>

            <button type="button" class="step-nav" data-step="3">
                <span class="step-bubble">3</span>
                <span class="step-label">TRIH /<br>Cable</span>
            </button>
            <div class="step-line" data-line="3"></div>

            <button type="button" class="step-nav" data-step="4">
                <span class="step-bubble">4</span>
                <span class="step-label">NPT /<br>Pozo</span>
            </button>
        </div>

        <div class="stepper-meta">
            <span id="step-counter" class="stepper-counter">Paso 1 de 4</span>
            <span id="step-percent" class="stepper-percent">25%</span>
        </div>
        <div class="stepper-progress-track">
            <div id="progress-bar" class="stepper-progress-bar" style="width: 25%"></div>
        </div>
    </div>

    {{-- 2.3 FORMULARIO (Por pasos en Nuevo/Editar, o todos los divs corridos en Ver) --}}
    <form id="wizard-form">
        @csrf
        @if ($serviceId)
            <input type="hidden" name="service_id" value="{{ $serviceId }}">
        @endif

        {{-- PASO 1 — DATOS GENERALES --}}
        <div id="step-1" class="step-content active">
            <section class="form-card">
                <div class="form-card-header">
                    <div class="form-card-header-left">
                        <div class="form-card-icon"><i class="fas fa-info-circle"></i></div>
                        <div>
                            <h2 class="form-card-title">Datos generales</h2>
                            <p class="form-card-subtitle">Identificación de la orden de servicio y su contexto operativo.</p>
                        </div>
                    </div>
                </div>

                <div class="form-card-body">
                    <div class="form-grid form-grid-4">
                        <div class="form-field">
                            <label class="form-label">Orden de servicio</label>
                            <input name="os" class="form-input" placeholder="VES-WL-OS-0000">
                        </div>
                        <div class="form-field">
                            <label class="form-label">Fecha</label>
                            <input name="fecha" type="date" class="form-input">
                        </div>
                        <div class="form-field">
                            <label class="form-label">Estado inicial</label>
                            <select name="estado" class="form-select">
                                <option value="Planeado">Planeado</option>
                                <option value="Programado">Programado</option>
                                <option value="En ejecución">En ejecución</option>
                                <option value="Terminado">Terminado</option>
                                <option value="Cancelado">Cancelado</option>
                            </select>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Modalidad</label>
                            <select name="modalidad" class="form-select">
                                <option value="TIERRA">Tierra</option>
                                <option value="MARINA">Marina</option>
                            </select>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Cliente</label>
                            <input name="cliente" list="clientes-sugeridos" class="form-input" placeholder="Escribe cliente...">
                            <datalist id="clientes-sugeridos">
                                <option value="PEMEX"></option>
                                <option value="SLB"></option>
                                <option value="HALLIBURTON"></option>
                                <option value="WDEA/H. ENERGY"></option>
                                <option value="GEOTERMIA"></option>
                            </datalist>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Pozo</label>
                            <input name="pozo" list="pozos-sugeridos" class="form-input" placeholder="Escribe pozo...">
                            <datalist id="pozos-sugeridos">
                                <option value="OGARRIO 1480"></option>
                                <option value="OGARRIO 903 (1 INTER)"></option>
                                <option value="SAMARIA 668"></option>
                                <option value="TUPILCO 143"></option>
                                <option value="CERRO PRIETO 1-435D (5Inter)"></option>
                            </datalist>
                        </div>
                        <div class="form-field form-field-2">
                            <label class="form-label">Observaciones iniciales</label>
                            <textarea name="observaciones" rows="2" class="form-textarea" placeholder="Comentarios previos..."></textarea>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- PASO 2 — SERVICIOS Y PERSONAL --}}
        <div id="step-2" class="step-content">
            <div class="form-grid-split">

                <section class="form-card">
                    <div class="form-card-header">
                        <div class="form-card-header-left">
                            <div class="form-card-icon"><i class="fas fa-tools"></i></div>
                            <div>
                                <h2 class="form-card-title">Servicios planeados</h2>
                                <p class="form-card-subtitle">Selecciona todos los servicios que se ejecutarán en esta OS.</p>
                            </div>
                        </div>
                    </div>
                    <div class="form-card-body">
                        <div class="svc-checkbox-grid">
                            @foreach(['ACTIVO', 'CALIBRACIÓN', 'REGISTRO PLT', 'REGISTRO CBL-VDL', 'DISPARO PUNCHER', 'COLOCACIÓN DE TAPÓN DE CEMENTO'] as $svc)
                                <label class="svc-checkbox">
                                    <input type="checkbox" name="servicios[]" value="{{ $svc }}" class="svc-checkbox-input">
                                    <span class="svc-checkbox-box"><i class="fas fa-check svc-checkbox-mark"></i></span>
                                    <span class="svc-checkbox-label">{{ $svc }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section class="form-card">
                    <div class="form-card-header">
                        <div class="form-card-header-left">
                            <div class="form-card-icon"><i class="fas fa-users"></i></div>
                            <div>
                                <h2 class="form-card-title">Personal planeado</h2>
                                <p class="form-card-subtitle">Asignación de responsables y recursos.</p>
                            </div>
                        </div>
                    </div>
                    <div class="form-card-body form-card-body-stack">
                        <div class="form-field">
                            <label class="form-label">Ingeniero a cargo</label>
                            <select name="ingeniero_id" class="form-select">
                                <option value="">Seleccionar ingeniero</option>
                                <option value="1">ING. EJEMPLO</option>
                            </select>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Grúa</label>
                            <select name="grua" class="form-select">
                                <option value="">Sin grúa</option>
                                <option value="VH-204">VH-204</option>
                            </select>
                        </div>
                    </div>
                </section>

            </div>
        </div>

        {{-- PASO 3 — TRIH / CABLE --}}
        <div id="step-3" class="step-content">
            <section class="form-card">
                <div class="form-card-header">
                    <div class="form-card-header-left">
                        <div class="form-card-icon"><i class="fas fa-link"></i></div>
                        <div>
                            <h2 class="form-card-title">Cable / TRIH planeado / inicial</h2>
                            <p class="form-card-subtitle">Datos del cable y tensores al inicio del servicio.</p>
                        </div>
                    </div>
                </div>
                <div class="form-card-body">
                    <div class="form-grid form-grid-3">
                        <div class="form-field">
                            <label class="form-label">Cable</label>
                            <input name="cable" placeholder="Código de cable" class="form-input">
                        </div>
                        <div class="form-field">
                            <label class="form-label">Tambor</label>
                            <input name="tambor" placeholder="Tambor" class="form-input">
                        </div>
                        <div class="form-field">
                            <label class="form-label">Tensión máxima</label>
                            <input name="tension" placeholder="Ej. 4500 lb" class="form-input">
                        </div>
                        <div class="form-field form-field-full">
                            <label class="form-label">Notas del TRIH</label>
                            <textarea name="notas_trih" rows="3" class="form-textarea"></textarea>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- PASO 4 — NPT Y POZO --}}
        <div id="step-4" class="step-content">
            <div class="form-grid-split">

                <section class="form-card">
                    <div class="form-card-header">
                        <div class="form-card-header-left">
                            <div class="form-card-icon form-card-icon-danger"><i class="fas fa-triangle-exclamation"></i></div>
                            <div>
                                <h2 class="form-card-title">NPT / No conformidad</h2>
                                <p class="form-card-subtitle">Registra el tiempo no productivo si aplica.</p>
                            </div>
                        </div>
                    </div>
                    <div class="form-card-body form-card-body-stack">
                        <div class="form-field">
                            <label class="form-label">¿Esta OS tiene NPT?</label>
                            <div class="npt-toggle">
                                <label class="npt-toggle-option">
                                    <input type="radio" name="tiene_npt" value="NO" class="npt-toggle-input" checked>
                                    <span class="npt-toggle-label">No tiene</span>
                                </label>
                                <label class="npt-toggle-option">
                                    <input type="radio" name="tiene_npt" value="SI" class="npt-toggle-input">
                                    <span class="npt-toggle-label npt-toggle-label-danger">Sí tiene</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Horas NPT</label>
                            <input name="horas_npt" type="number" step="0.1" min="0" class="form-input" value="0">
                        </div>
                        <div class="form-field">
                            <label class="form-label">Descripción / causa</label>
                            <textarea name="descripcion_npt" rows="3" class="form-textarea"></textarea>
                        </div>
                    </div>
                </section>

                <section class="form-card">
                    <div class="form-card-header">
                        <div class="form-card-header-left">
                            <div class="form-card-icon"><i class="fas fa-oil-well"></i></div>
                            <div>
                                <h2 class="form-card-title">Datos del pozo</h2>
                                <p class="form-card-subtitle">Condiciones del pozo al momento del servicio.</p>
                            </div>
                        </div>
                    </div>
                    <div class="form-card-body">
                        <div class="form-grid form-grid-2">
                            <div class="form-field">
                                <label class="form-label">Presión pozo</label>
                                <input name="presion" placeholder="psi" class="form-input">
                            </div>
                            <div class="form-field">
                                <label class="form-label">Temperatura</label>
                                <input name="temperatura" placeholder="°C" class="form-input">
                            </div>
                            <div class="form-field">
                                <label class="form-label">H2S</label>
                                <input name="h2s" placeholder="ppm" class="form-input">
                            </div>
                            <div class="form-field">
                                <label class="form-label">CO2</label>
                                <input name="co2" placeholder="ppm" class="form-input">
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>

        {{-- FOOTER --}}
        <div class="wizard-footer no-print">
            @if ($isView)
                <a href="{{ route('operations.sgc.services', ['view' => 'list']) }}" class="btn btn-outline service-modal-close">
                    <i class="fas fa-arrow-left"></i> Volver a la lista
                </a>
                <div class="wizard-footer-right">
                    <a href="{{ route('operations.sgc.services', ['view' => 'new_service', 'mode' => 'edit', 'id' => $serviceId]) }}"
                        class="btn btn-primary service-modal-trigger">
                        <i class="fas fa-pen"></i> Editar Servicio
                    </a>
                </div>
            @else
                <button type="button" id="btn-prev" class="btn btn-outline hidden">
                    <i class="fas fa-arrow-left"></i> Anterior
                </button>

                <div class="wizard-footer-right">
                    <a href="{{ route('operations.sgc.services', ['view' => 'list']) }}" class="btn btn-outline service-modal-close">Cancelar</a>

                    <button type="button" id="btn-next" class="btn btn-primary">
                        Siguiente <i class="fas fa-arrow-right"></i>
                    </button>

                    <button type="submit" id="btn-submit" class="btn btn-success hidden">
                        <i class="fas fa-check"></i> {{ $isEdit ? 'Actualizar Servicio' : 'Guardar Servicio' }}
                    </button>
                </div>
            @endif
        </div>
    </form>
</div>
