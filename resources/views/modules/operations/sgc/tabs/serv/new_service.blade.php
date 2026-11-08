
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

    <div id="service-catalog-alert" class="service-catalog-alert no-print" role="alert" hidden>
        <strong>Faltan datos de catálogo</strong>
        <ul id="service-catalog-alert-items"></ul>
        <p>Registra o activa los datos en el catálogo correspondiente antes de asignarlos a la orden. El formulario no crea registros automáticamente.</p>
    </div>
    @php
        $steps = [
            'Datos generales',
            'Servicios planeados',
            'Personal planeado',
            'Servicios / corridas / herramientas TRIH',
            'Cable / TRIH planeado / inicial',
            'NPT / No conformidad',
            'Datos del pozo / condiciones operativas',
        ];
    @endphp
    <div class="stepper-panel no-print">
        <div class="stepper-track">
            @foreach ($steps as $stepLabel)
                @unless ($loop->first)
                    <div class="step-line" data-line="{{ $loop->index }}"></div>
                @endunless
                <button type="button" class="step-nav" data-step="{{ $loop->iteration }}">
                    <span class="step-bubble {{ $loop->first ? 'is-active' : '' }}">{{ $loop->iteration }}</span>
                    <span class="step-label {{ $loop->first ? 'is-active' : '' }}">{{ $stepLabel }}</span>
                </button>
            @endforeach
        </div>
        <div class="stepper-meta">
            <span id="step-counter" class="stepper-counter">Paso 1 de {{ count($steps) }}</span>
            <span id="step-percent" class="stepper-percent">14%</span>
        </div>
        <div class="stepper-progress-track">
            <div id="progress-bar" class="stepper-progress-bar" style="width: 14%"></div>
        </div>
    </div>
<form id="wizard-form" data-catalogs="{{ json_encode(['serviceTypes' => $serviceTypes, 'families' => $toolFamilies, 'tools' => $tools, 'cables' => $cables]) }}" data-training-url="{{ route('operations.sgc.services', ['view' => 'new_service', 'catalog' => 'employee-training']) }}">
@csrf
@if ($serviceId)<input type="hidden" name="service_id" value="{{ $serviceId }}">@endif
<div id="step-1" class="step-content active">
<section class="form-card">
    <div class="form-card-header"><div><h2 class="form-card-title">Datos generales</h2><p class="form-card-subtitle">Información común de la orden de servicio.</p></div></div>
    <div class="form-card-body"><div class="form-grid form-grid-4"><div class="form-field">
    <label class="form-label" for="svc-os">Orden de servicio</label>
    <input id="svc-os" name="os" type="text" class="form-input" placeholder="VES-WL-OS-0000" >
</div><div class="form-field">
    <label class="form-label" for="svc-fecha">Fecha</label>
    <input id="svc-fecha" name="fecha" type="date" class="form-input" placeholder="" >
</div><div class="form-field">
    <label class="form-label" for="svc-estado">Estado inicial</label>
    <select id="svc-estado" name="estado" class="form-select" ><option value="Planeado">Planeado</option>
<option value="Programado">Programado</option>
<option value="En ejecución">En ejecución</option>
<option value="Terminado">Terminado</option>
<option value="Cancelado">Cancelado</option></select>
</div><div class="form-field">
    <label class="form-label" for="svc-modalidad">Modalidad de operación</label>
    <select id="svc-modalidad" name="modalidad" class="form-select" ><option value="TIERRA">TIERRA</option>
<option value="MARINA">MARINA</option></select>
</div><div class="form-field">
    <label class="form-label" for="svc-ure_unit_id">URE / Unidad principal</label>
    <select id="svc-ure_unit_id" name="ure_unit_id" class="form-select" data-catalog><option value="">Sin asignar</option>
@foreach ($units as $unit)
    <option value="{{ $unit->id }}" data-status="{{ $unit->status }}" data-serial="{{ $unit->serial_number }}">{{ $unit->economic_number }}{{ $unit->vehicle_type ? ' · '.$unit->vehicle_type : '' }}{{ $unit->status ? ' · '.$unit->status : '' }}</option>
@endforeach</select>
</div><div class="form-field">
    <label class="form-label" for="svc-client_id">Cliente</label>
    <select id="svc-client_id" name="client_id" class="form-select" data-catalog data-empty-message="No hay clientes activos. Registra o activa un cliente antes de asignarlo.">
        <option value="">Seleccionar cliente</option>
        @foreach ($clients as $client)<option value="{{ $client->id }}">{{ $client->name }}</option>@endforeach
    </select>
</div><div class="form-field">
    <label class="form-label" for="svc-well_id">Pozo</label>
    <select id="svc-well_id" name="well_id" class="form-select" data-catalog><option value="">Seleccionar pozo</option>@foreach ($wells as $well)<option value="{{ $well->id }}">{{ $well->name }}</option>@endforeach</select>
</div><div class="form-field form-field-full">
    <label class="form-label" for="svc-observaciones">Observaciones iniciales</label>
    <textarea id="svc-observaciones" name="observaciones" class="form-textarea" rows="3" placeholder="Comentarios previos, condiciones, requerimientos, etc."></textarea>
</div></div></div>
</section>
</div>

<div id="step-2" class="step-content ">
<section class="form-card">
    <div class="form-card-header"><div><h2 class="form-card-title">Servicios planeados</h2><p class="form-card-subtitle">Selecciona los servicios que se ejecutarán dentro de esta OS.</p></div></div>
    <div class="form-card-body"><div class="svc-checkbox-grid">
@foreach ($serviceTypes as $type)
<label class="svc-checkbox"><input type="checkbox" name="service_type_ids[]" value="{{ $type->id }}" data-service-name="{{ $type->name }}" class="svc-checkbox-input"><span class="svc-checkbox-box"><i class="fas fa-check svc-checkbox-mark"></i></span><span class="svc-checkbox-label">{{ $type->name }}</span></label>
@endforeach
@if ($serviceTypes->isEmpty())<p class="form-card-subtitle">No hay tipos de servicio activos. Registra o activa uno en el catálogo antes de continuar.</p>@endif
</div></div>
</section>
</div>

<div id="step-3" class="step-content ">
<section class="form-card">
    <div class="form-card-header"><div><h2 class="form-card-title">Personal planeado</h2><p class="form-card-subtitle">Asignación de responsables y recursos.</p></div></div>
    <div class="form-card-body"><div class="personnel-fields"><div class="form-field">
    <label class="form-label" for="svc-engineer_id">Ingeniero a cargo</label>
    <select id="svc-engineer_id" name="engineer_id" class="form-select" data-catalog><option value="">Sin asignar</option>
@foreach ($employees as $employee)
    <option value="{{ $employee->id }}">{{ $employee->full_name }}{{ $employee->position ? ' · '.$employee->position : '' }}</option>
@endforeach</select>
<button type="button" class="btn btn-outline btn-sm no-print" data-training-for="engineer_id">Ver cursos</button>
</div><div class="form-field">
    <label class="form-label" for="svc-trainee_id">Trainee / apoyo técnico</label>
    <select id="svc-trainee_id" name="trainee_id" class="form-select" data-catalog><option value="">Sin asignar</option>
@foreach ($employees as $employee)
    <option value="{{ $employee->id }}">{{ $employee->full_name }}{{ $employee->position ? ' · '.$employee->position : '' }}</option>
@endforeach</select>
<button type="button" class="btn btn-outline btn-sm no-print" data-training-for="trainee_id">Ver cursos</button>
</div><div class="form-field">
    <label class="form-label" for="svc-crane_unit_id">Grúa</label>
    <select id="svc-crane_unit_id" name="crane_unit_id" class="form-select" data-catalog><option value="">Sin asignar</option>
@foreach ($units as $unit)
    <option value="{{ $unit->id }}" data-status="{{ $unit->status }}" data-serial="{{ $unit->serial_number }}">{{ $unit->economic_number }}{{ $unit->vehicle_type ? ' · '.$unit->vehicle_type : '' }}{{ $unit->status ? ' · '.$unit->status : '' }}</option>
@endforeach</select>
</div><div class="form-field">
    <label class="form-label" for="svc-pickup_unit_id">Pick Up</label>
    <select id="svc-pickup_unit_id" name="pickup_unit_id" class="form-select" data-catalog><option value="">Sin asignar</option>
@foreach ($units as $unit)
    <option value="{{ $unit->id }}" data-status="{{ $unit->status }}" data-serial="{{ $unit->serial_number }}">{{ $unit->economic_number }}{{ $unit->vehicle_type ? ' · '.$unit->vehicle_type : '' }}{{ $unit->status ? ' · '.$unit->status : '' }}</option>
@endforeach</select>
</div><div class="form-field">
    <label class="form-label" for="svc-ecp_equipment_id">ECP</label>
    <select id="svc-ecp_equipment_id" name="ecp_equipment_id" class="form-select" data-catalog data-empty-message="No hay equipos de ecp registrados. Regístralos en el catálogo antes de asignarlos.">
        <option value="">Sin asignar</option>
        @foreach ($equipment->get('ecp', collect()) as $item)
            <option value="{{ $item->id }}">{{ $item->code }} · {{ $item->name }}{{ $item->serial_number ? ' · '.$item->serial_number : '' }} · {{ $item->status }}</option>
        @endforeach
    </select>
</div><div class="form-field">
    <label class="form-label" for="svc-generator_equipment_id">Generador</label>
    <select id="svc-generator_equipment_id" name="generator_equipment_id" class="form-select" data-catalog data-empty-message="No hay equipos de generador registrados. Regístralos en el catálogo antes de asignarlos.">
        <option value="">Sin asignar</option>
        @foreach ($equipment->get('generador', collect()) as $item)
            <option value="{{ $item->id }}">{{ $item->code }} · {{ $item->name }}{{ $item->serial_number ? ' · '.$item->serial_number : '' }} · {{ $item->status }}</option>
        @endforeach
    </select>
</div><div class="form-field">
    <label class="form-label" for="svc-box_equipment_id">Cajas / Contenedor</label>
    <select id="svc-box_equipment_id" name="box_equipment_id" class="form-select" data-catalog data-empty-message="No hay equipos de cajas / contenedor registrados. Regístralos en el catálogo antes de asignarlos.">
        <option value="">Sin asignar</option>
        @foreach ($equipment->get('caja', collect()) as $item)
            <option value="{{ $item->id }}">{{ $item->code }} · {{ $item->name }}{{ $item->serial_number ? ' · '.$item->serial_number : '' }} · {{ $item->status }}</option>
        @endforeach
    </select>
</div><div class="form-field">
    <label class="form-label" for="svc-chip_equipment_id">Chip</label>
    <select id="svc-chip_equipment_id" name="chip_equipment_id" class="form-select" data-catalog data-empty-message="No hay equipos de chip registrados. Regístralos en el catálogo antes de asignarlos.">
        <option value="">Sin asignar</option>
        @foreach ($equipment->get('chip', collect()) as $item)
            <option value="{{ $item->id }}">{{ $item->code }} · {{ $item->name }}{{ $item->serial_number ? ' · '.$item->serial_number : '' }} · {{ $item->status }}</option>
        @endforeach
    </select>
</div><div class="form-field">
    <label class="form-label" for="svc-additional_unit_id">Unidad adicional</label>
    <select id="svc-additional_unit_id" name="additional_unit_id" class="form-select" data-catalog><option value="">Sin asignar</option>
@foreach ($units as $unit)
    <option value="{{ $unit->id }}" data-status="{{ $unit->status }}" data-serial="{{ $unit->serial_number }}">{{ $unit->economic_number }}{{ $unit->vehicle_type ? ' · '.$unit->vehicle_type : '' }}{{ $unit->status ? ' · '.$unit->status : '' }}</option>
@endforeach</select>
</div></div>
<div id="employee-training-panel" class="unit-summary" hidden aria-live="polite"></div>
</div>
</section>
</div>

<div id="step-4" class="step-content ">
<section class="form-card">
    <div class="form-card-header"><div><h2 class="form-card-title">Servicios / corridas / herramientas TRIH planeado</h2><p class="form-card-subtitle">Cada servicio puede tener una o más corridas. En cada corrida captura herramientas y exposición dentro del pozo.</p></div></div>
    <div class="form-card-body"><div class="operation-heading no-print"><button type="button" id="add-trih-operation" class="btn btn-outline btn-sm">+ Agregar servicio TRIH</button></div><p id="operations-empty" class="form-card-subtitle">Selecciona servicios planeados o agrega un servicio TRIH para capturar corridas y herramientas.</p><div id="service-operations" class="operations-stack"></div></div>
</section>
</div>

<div id="step-5" class="step-content ">
<section class="form-card">
    <div class="form-card-header"><div><h2 class="form-card-title">Cable / TRIH planeado / inicial</h2><p class="form-card-subtitle">Datos iniciales de cable y TRIH asociados a la orden.</p></div></div>
    <div class="form-card-body"><div class="form-grid form-grid-4"><div class="form-field">
    <label class="form-label" for="svc-ure_selected">URE seleccionada</label>
    <input id="svc-ure_selected" type="text" class="form-input" placeholder="Primero selecciona la URE" readonly data-display-only>
</div><div class="form-field">
    <label class="form-label" for="svc-cable_id">Cable</label>
    <select id="svc-cable_id" name="cable_id" class="form-select" data-catalog data-empty-message="No hay cables registrados. Registra un cable antes de asignarlo.">
        <option value="">Seleccionar cable</option>
        @foreach ($cables as $cable)<option value="{{ $cable->id }}">{{ $cable->code }} · {{ $cable->status }}</option>@endforeach
    </select>
</div><div class="form-field">
    <label class="form-label" for="svc-tambor">Tambor</label>
    <input id="svc-tambor" name="tambor" type="text" class="form-input" placeholder="Tambor" >
</div><div class="form-field">
    <label class="form-label" for="svc-tension">Tensión máxima esperada</label>
    <input id="svc-tension" name="tension" type="text" class="form-input" placeholder="Ej. 4500 lb" >
</div><div class="form-field form-field-full">
    <label class="form-label" for="svc-notas_trih">Notas del TRIH planeado / inicial</label>
    <textarea id="svc-notas_trih" name="notas_trih" class="form-textarea" rows="3" placeholder="Notas del TRIH planeado. Después del trabajo se actualizan como TRIH final."></textarea>
</div></div><div id="ure-linked-summary" class="unit-summary" hidden><h3 class="form-card-title">Datos automáticos por URE</h3><dl class="unit-summary-grid"><div><dt>Cable asignado</dt><dd data-ure-cable>Sin cable ligado</dd></div><div><dt>Tambor</dt><dd data-ure-drum>Sin tambor</dd></div><div><dt>Estado unidad</dt><dd data-ure-status>Sin dato</dd></div></dl></div></div>
</section>
</div>

<div id="step-6" class="step-content ">
<section class="form-card">
    <div class="form-card-header"><div><h2 class="form-card-title">NPT / No conformidad</h2><p class="form-card-subtitle">Captura las horas, causa y seguimiento del tiempo no productivo.</p></div></div>
    <div class="form-card-body"><div class="form-grid form-grid-4"><div class="form-field">
    <label class="form-label" for="svc-tiene_npt">¿Esta OS tiene NPT?</label>
    <select id="svc-tiene_npt" name="tiene_npt" class="form-select" ><option value="NO">No tiene NPT</option><option value="SI">Sí tiene NPT</option></select>
</div><div class="form-field">
    <label class="form-label" for="svc-horas_npt">Horas NPT</label>
    <input id="svc-horas_npt" name="horas_npt" type="number" class="form-input" placeholder="" min="0" step="0.1" value="0" data-npt>
</div><div class="form-field">
    <label class="form-label" for="svc-tipo_evento_npt">Tipo de evento NPT</label>
    <select id="svc-tipo_evento_npt" name="tipo_evento_npt" class="form-select" data-npt><option value="Sin NPT">Sin NPT</option>
<option value="Falla de herramienta">Falla de herramienta</option>
<option value="Falla de unidad">Falla de unidad</option>
<option value="Falla de cable">Falla de cable</option>
<option value="Cliente">Cliente</option>
<option value="Pozo">Pozo</option>
<option value="Operación">Operación</option>
<option value="Logística">Logística</option>
<option value="Seguridad / HSE">Seguridad / HSE</option>
<option value="Clima">Clima</option>
<option value="Personal">Personal</option>
<option value="Otro">Otro</option></select>
</div><div class="form-field">
    <label class="form-label" for="svc-responsable_npt">Responsable / área</label>
    <input id="svc-responsable_npt" name="responsable_npt" type="text" class="form-input" placeholder="Operaciones, taller, cliente, pozo..." data-npt>
</div></div><div class="form-grid form-grid-2 npt-description-grid"><div class="form-field form-field-full">
    <label class="form-label" for="svc-descripcion_npt">Descripción / causa</label>
    <textarea id="svc-descripcion_npt" name="descripcion_npt" class="form-textarea" rows="3" placeholder="Describe la causa del NPT."></textarea>
</div><div class="form-field form-field-full">
    <label class="form-label" for="svc-accion_correctiva_npt">Acción correctiva / seguimiento</label>
    <textarea id="svc-accion_correctiva_npt" name="accion_correctiva_npt" class="form-textarea" rows="3" placeholder="Acciones, ICR/ACR, responsable y cierre."></textarea>
</div></div></div>
</section>
</div>

<div id="step-7" class="step-content ">
<section class="form-card">
    <div class="form-card-header"><div><h2 class="form-card-title">Datos del pozo / condiciones operativas</h2><p class="form-card-subtitle">Condiciones del pozo al momento de la operación.</p></div></div>
    <div class="form-card-body"><div class="form-grid form-grid-4"><div class="form-field">
    <label class="form-label" for="svc-presion">Presión pozo</label>
    <input id="svc-presion" name="presion" type="text" class="form-input" placeholder="psi" >
</div><div class="form-field">
    <label class="form-label" for="svc-temperatura">Temperatura pozo</label>
    <input id="svc-temperatura" name="temperatura" type="text" class="form-input" placeholder="°C / °F" >
</div><div class="form-field">
    <label class="form-label" for="svc-h2s">H2S</label>
    <input id="svc-h2s" name="h2s" type="text" class="form-input" placeholder="ppm / %" >
</div><div class="form-field">
    <label class="form-label" for="svc-co2">CO2</label>
    <input id="svc-co2" name="co2" type="text" class="form-input" placeholder="ppm / %" >
</div><div class="form-field">
    <label class="form-label" for="svc-desviacion_maxima">Desviación máxima</label>
    <input id="svc-desviacion_maxima" name="desviacion_maxima" type="text" class="form-input" placeholder="°" >
</div><div class="form-field">
    <label class="form-label" for="svc-profundidad_desviacion">A qué profundidad</label>
    <input id="svc-profundidad_desviacion" name="profundidad_desviacion" type="text" class="form-input" placeholder="m / ft" >
</div><div class="form-field">
    <label class="form-label" for="svc-presion_maxima">Presión máxima</label>
    <input id="svc-presion_maxima" name="presion_maxima" type="text" class="form-input" placeholder="psi" >
</div><div class="form-field">
    <label class="form-label" for="svc-temperatura_maxima">Temperatura máxima</label>
    <input id="svc-temperatura_maxima" name="temperatura_maxima" type="text" class="form-input" placeholder="°C / °F" >
</div><div class="form-field">
    <label class="form-label" for="svc-arbol_flange">Árbol / Flange</label>
    <input id="svc-arbol_flange" name="arbol_flange" type="text" class="form-input" placeholder="Tipo de árbol o flange" >
</div><div class="form-field">
    <label class="form-label" for="svc-tipo_pozo">Tipo de pozo</label>
    <select id="svc-tipo_pozo" name="tipo_pozo" class="form-select" ><option value="">Seleccionar</option><option value="Productor">Pozo productor</option><option value="Inyector">Pozo inyector</option></select>
</div><div class="form-field">
    <label class="form-label" for="svc-flujo_bpd">Flujo BPD</label>
    <input id="svc-flujo_bpd" name="flujo_bpd" type="text" class="form-input" placeholder="BPD" >
</div><div class="form-field">
    <label class="form-label" for="svc-flujo_gas_mmscfd">Flujo gas</label>
    <input id="svc-flujo_gas_mmscfd" name="flujo_gas_mmscfd" type="text" class="form-input" placeholder="MMscfd" >
</div><div class="form-field">
    <label class="form-label" for="svc-tipo_fluido">Tipo de fluido</label>
    <input id="svc-tipo_fluido" name="tipo_fluido" type="text" class="form-input" placeholder="Aceite, agua, gas, mezcla..." >
</div><div class="form-field form-field-full">
    <label class="form-label" for="svc-notas_pozo">Notas del pozo</label>
    <textarea id="svc-notas_pozo" name="notas_pozo" class="form-textarea" rows="3" placeholder=""></textarea>
</div></div></div>
</section>
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
