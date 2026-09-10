@extends('modules.administration.expense-claims.index')

@section('title', 'Configuración del Sistema | VesCore')

@push('styles')
<style>
    :root {
        --st-bg: #f8fafc;
        --st-border: #e2e8f0;
        --st-text-dark: #0f172a;
        --st-text-muted: #64748b;
        --st-primary: #152845;
        --st-primary-light: #e6edf7;
        --st-accent: #0284c7;
        --alert-red: #ef4444;
        --alert-green: #10b981;
    }

    /* ── CONTENEDOR PRINCIPAL FLUIDO Y EXPANDIDO ── */
    .settings-container {
        display: flex;
        gap: 2rem;
        padding: 2rem;
        width: 98%;
        max-width: 1600px; /* 👈 Ampliado a 1600px para más espacio */
        margin: 0 auto;
        min-height: calc(100vh - 100px);
        align-items: flex-start;
        font-family: 'Poppins', sans-serif;
    }

    /* ── SIDEBAR DE CONFIGURACIÓN ── */
    .settings-sidebar {
        width: 280px;
        flex-shrink: 0;
        background: #fff;
        border: 1px solid var(--st-border);
        border-radius: 1rem;
        overflow: hidden;
        position: sticky;
        top: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }

    .sidebar-header {
        padding: 1.5rem;
        background: var(--st-primary);
        color: #fff;
    }

    .sidebar-header h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .sidebar-nav {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
    }

    .sidebar-nav li { border-bottom: 1px solid var(--st-border); }
    .sidebar-nav li:last-child { border-bottom: none; }

    .sidebar-nav button {
        width: 100%;
        background: transparent;
        border: none;
        padding: 1.25rem 1.5rem;
        text-align: left;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--st-text-muted);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transition: all 0.2s;
    }

    .sidebar-nav button i { font-size: 1.25rem; }
    .sidebar-nav button:hover:not(:disabled) { background: var(--st-bg); color: var(--st-text-dark); }
    .sidebar-nav button.active {
        background: var(--st-primary-light);
        color: var(--st-primary);
        border-left: 4px solid var(--st-primary);
    }
    .sidebar-nav button:disabled { opacity: 0.5; cursor: not-allowed; }

    /* ── PANEL PRINCIPAL Y PESTAÑAS (TABS) ── */
    .settings-content {
        flex: 1;
        background: #fff;
        border: 1px solid var(--st-border);
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        min-width: 0;
    }

    .tab-pane {
        display: none; /* Oculto por defecto */
        animation: fadeIn 0.3s ease;
    }
    .tab-pane.active { display: block; } /* Mostrar solo el activo */

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .content-header {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid var(--st-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .content-title h2 { margin: 0; font-size: 1.3rem; color: var(--st-text-dark); display: flex; align-items: center; gap: 0.5rem;}
    .content-title p { margin: 0.2rem 0 0 0; font-size: 0.85rem; color: var(--st-text-muted); }

    /* ── BOTONES GLOBALES PANEL ── */
    .btn-st {
        background: var(--st-primary); color: #fff; border: none; padding: 0.6rem 1.2rem;
        border-radius: 0.5rem; font-weight: 600; cursor: pointer; display: inline-flex;
        align-items: center; gap: 0.5rem; transition: all 0.2s; font-size: 0.85rem;
    }
    .btn-st:hover { background: #0f172a; transform: translateY(-1px); }

    .btn-st-outline { background: transparent; color: var(--st-primary); border: 1px solid var(--st-primary); }
    .btn-st-outline:hover { background: var(--st-primary-light); }

    .btn-alert { background: var(--alert-red); color: #fff; border: none; padding: 0.6rem 1.2rem; border-radius: 0.5rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.2s; font-size: 0.85rem; }
    .btn-alert:hover { background: #dc2626; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25); }

    /* ── DISEÑO TAB 1: CENTROS Y PROYECTOS ── */
    .catalog-list { padding: 2rem; display: flex; flex-direction: column; gap: 1rem; }
    .cc-card { border: 1px solid var(--st-border); border-radius: 0.5rem; background: var(--st-bg); overflow: hidden; }
    .cc-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; cursor: pointer; transition: background 0.2s; }
    .cc-header:hover { background: #e2e8f0; }
    .cc-info { display: flex; align-items: center; gap: 1rem; }
    .cc-icon { width: 40px; height: 40px; border-radius: 0.5rem; background: var(--st-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    .cc-text h4 { margin: 0; font-size: 1rem; color: var(--st-text-dark); display:flex; align-items:center; gap:0.5rem;}
    .cc-text p { margin: 0; font-size: 0.8rem; color: var(--st-text-muted); }

    .badge { padding: 3px 8px; border-radius: 99px; font-size: 0.7rem; font-weight: 700; }
    .b-active { background: #dcfce7; color: #166534; }
    .b-inactive { background: #fee2e2; color: #991b1b; }
    .b-code { background: #e2e8f0; color: #475569; font-family: monospace; border: 1px solid #cbd5e1;}

    .cc-actions { display: flex; gap: 0.5rem; align-items: center; }
    .prj-body { background: #fff; border-top: 1px solid var(--st-border); padding: 0; display: none; }
    .prj-body.open { display: block; }

    /* ── DISEÑO TABLA COMÚN (PARA PROYECTOS Y NODOS SAT) ── */
    .data-table-wrapper { overflow-x: auto; width: 100%; }
    .st-table { width: 100%; border-collapse: collapse; text-align: left; }
    .st-table th, .st-table td { padding: 1rem 1.5rem; border-bottom: 1px solid var(--st-border); font-size: 0.85rem; }
    .st-table th { background: #f1f5f9; color: var(--st-text-muted); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
    .st-table tr:last-child td { border-bottom: none; }
    .st-table tbody tr:hover { background: #f8fafc; }

    .btn-icon { background: transparent; border: none; font-size: 1.2rem; color: var(--st-text-muted); cursor: pointer; transition: 0.2s; padding: 4px; border-radius: 4px; }
    .btn-icon:hover { background: #e2e8f0; color: var(--st-primary); }

    /* ── BADGES SAT ── */
    .badge-live { background: #d1fae5; color: #065f46; padding: 0.35rem 0.85rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem; }
    .badge-expired { background: #fee2e2; color: #991b1b; padding: 0.35rem 0.85rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem; animation: pulse-red 2s infinite; }
    .badge-history { background: #e2e8f0; color: #475569; padding: 0.35rem 0.85rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem; }
    @keyframes pulse-red { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); } 70% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }

    /* ── MODALES UNIFICADOS ── */
    .st-modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);
        z-index: 9999; display: flex; align-items: center; justify-content: center;
        opacity: 0; visibility: hidden; transition: all 0.3s ease; padding: 1rem;
    }
    .st-modal-overlay.active { opacity: 1; visibility: visible; }
    .st-modal-box {
        background: #fff; width: 100%; max-width: 500px; border-radius: 1rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); transform: translateY(20px); transition: all 0.3s ease;
        max-height: 95vh; display: flex; flex-direction: column;
    }
    .st-modal-box.wide { max-width: 850px; }
    .st-modal-overlay.active .st-modal-box { transform: translateY(0); }

    .st-modal-header { padding: 1.5rem; border-bottom: 1px solid var(--st-border); display: flex; justify-content: space-between; align-items: center; background: var(--st-bg); border-radius: 1rem 1rem 0 0;}
    .st-modal-header h3 { margin: 0; font-size: 1.15rem; display:flex; align-items:center; gap:0.5rem; color: var(--st-text-dark); }

    .st-modal-body { padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; overflow-y: auto; }
    .st-modal-body.grid-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem 2rem; }

    .st-modal-footer { padding: 1.25rem 1.5rem; border-top: 1px solid var(--st-border); background: var(--st-bg); border-radius: 0 0 1rem 1rem; display: flex; justify-content: flex-end; gap: 0.75rem;}

    /* Formularios */
    .st-form-group { display: flex; flex-direction: column; gap: 0.4rem; }
    .st-form-group label { font-size: 0.8rem; font-weight: 600; color: var(--st-text-muted); text-transform: uppercase; letter-spacing: 0.05em; }

    .st-input-group { position: relative; display: flex; align-items: center; }
    .st-input-group i.field-icon { position: absolute; left: 1rem; color: #94a3b8; font-size: 1.2rem; pointer-events: none; z-index: 2; }
    .st-input { width: 100%; padding: 0.65rem 1rem 0.65rem 2.8rem; border: 1px solid #cbd5e1; border-radius: 0.5rem; font-family: inherit; font-size: 0.9rem; outline: none; background: #fff; transition: all 0.2s;}
    .st-input:focus { border-color: var(--st-accent); box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15); }

    .st-input-simple { width: 100%; padding: 0.65rem 0.8rem; border: 1px solid #cbd5e1; border-radius: 0.5rem; font-size: 0.9rem; outline: none; transition: all 0.2s; }
    .st-input-simple:focus { border-color: var(--st-accent); box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15); }

    .switch-container { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; }
    .switch-container input { display: none; }
    .switch-slider { width: 40px; height: 20px; background: #cbd5e1; border-radius: 20px; position: relative; transition: 0.3s; flex-shrink: 0;}
    .switch-slider::before { content: ''; position: absolute; width: 16px; height: 16px; background: #fff; border-radius: 50%; top: 2px; left: 2px; transition: 0.3s; }
    .switch-container input:checked + .switch-slider { background: #16a34a; }
    .switch-container input:checked + .switch-slider::before { transform: translateX(20px); }
    .switch-label { font-size: 0.85rem; font-weight: 600; color: var(--st-text-dark); }

    /* Dropzone Archivos SAT */
    .file-upload-wrapper { position: relative; border: 2px dashed #cbd5e1; border-radius: 0.5rem; padding: 2rem 1rem; text-align: center; background: var(--st-bg); transition: all 0.2s; cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; }
    .file-upload-wrapper.has-file { border-color: var(--st-accent); background: rgba(2, 132, 199, 0.05); border-style: solid; }
    .file-upload-wrapper:hover { border-color: var(--st-accent); }
    .file-upload-wrapper i { font-size: 2.5rem; color: #94a3b8; margin-bottom: 0.5rem; }
    .file-upload-wrapper.has-file i { color: var(--st-accent); }
    .file-upload-wrapper p { margin: 0; font-size: 0.85rem; font-weight: 600; color: var(--st-text-dark); }
    .file-upload-wrapper small { color: #64748b; font-size: 0.75rem; }
    .file-upload-wrapper input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }

    .empty-state { text-align: center; padding: 2rem; color: var(--st-text-muted); }
    .empty-state i { font-size: 3rem; color: #cbd5e1; margin-bottom: 0.5rem; }

    @media (max-width: 900px) {
        .settings-container { flex-direction: column; width: 100%; }
        .settings-sidebar { width: 100%; position: static; }
        .sidebar-nav { flex-direction: row; overflow-x: auto; }
        .sidebar-nav button { white-space: nowrap; border-bottom: 3px solid transparent; border-left: none; }
        .sidebar-nav button.active { border-bottom-color: var(--st-primary); border-left: none; }
        .st-modal-body.grid-layout { grid-template-columns: 1fr; }
        .st-form-group[style] { grid-column: 1 / -1 !important; }
    }
</style>
@endpush

@section('content')
<div class="settings-container">

    {{-- SIDEBAR DE NAVEGACIÓN --}}
    <aside class="settings-sidebar">
        <div class="sidebar-header">
            <h3><i class="bx bx-cog"></i> Configuración</h3>
        </div>
        <ul class="sidebar-nav">
            <li>
                <button id="btn-tab-cc" class="active" onclick="switchTab('tab-cc', this)">
                    <i class="bx bx-building-house"></i> Centros de Costo
                </button>
            </li>
            <li>
                <button id="btn-tab-sat" onclick="switchTab('tab-sat', this)">
                    <i class="bx bx-shield-quarter"></i> Bóveda SAT (Seguridad)
                </button>
            </li>
            <li>
                <button disabled title="Próximamente">
                    <i class="bx bx-purchase-tag"></i> Categorías de Gasto
                </button>
            </li>
            <li>
                <button disabled title="Próximamente">
                    <i class="bx bx-file-blank"></i> Políticas
                </button>
            </li>
        </ul>
    </aside>

    {{-- CONTENIDO PRINCIPAL (MULTITAB) --}}
    <main class="settings-content">

        {{-- ========================================================
             PESTAÑA 1: CATÁLOGO DE CENTROS Y PROYECTOS
             ======================================================== --}}
        <div id="tab-cc" class="tab-pane active">
            <div class="content-header">
                <div class="content-title">
                    <h2><i class="bx bx-buildings"></i> Estructura Financiera</h2>
                    <p>Gestione el catálogo principal de áreas, centros de costo y proyectos internos.</p>
                </div>
                <button class="btn-st" onclick="openCcModal()">
                    <i class="bx bx-plus-circle"></i> Nuevo Centro de Costos
                </button>
            </div>

            <div class="catalog-list">
                @foreach($costCenters as $cc)
                    <div class="cc-card">
                        <div class="cc-header" onclick="toggleProjects({{ $cc->id }})">
                            <div class="cc-info">
                                <div class="cc-icon"><i class="bx bx-building-house"></i></div>
                                <div class="cc-text">
                                    <h4>
                                        {{ $cc->name }}
                                        <span class="badge b-code">{{ $cc->code }}</span>
                                        <span class="badge {{ $cc->is_active ? 'b-active' : 'b-inactive' }}">{{ $cc->is_active ? 'Activo' : 'Inactivo' }}</span>
                                    </h4>
                                    <p>{{ $cc->description ?? 'Sin descripción' }}</p>
                                </div>
                            </div>
                            <div class="cc-actions">
                                <button class="btn-st btn-st-outline" onclick="event.stopPropagation(); openPrjModal({{ $cc->id }})">
                                    <i class="bx bx-plus"></i> Proyecto
                                </button>
                                <button class="btn-icon" onclick="event.stopPropagation(); openCcModal({{ json_encode($cc) }})" title="Editar Centro">
                                    <i class="bx bx-edit"></i>
                                </button>
                                <i class="bx bx-chevron-down" id="icon-chevron-{{ $cc->id }}" style="font-size:1.5rem; color:#94a3b8; transition:0.3s;"></i>
                            </div>
                        </div>

                        <div class="prj-body" id="prj-body-{{ $cc->id }}">
                            @if($cc->projects->count() > 0)
                                <table class="st-table">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Proyecto / Subcentro</th>
                                            <th>Fechas (Inicio - Fin)</th>
                                            <th>Estatus</th>
                                            <th style="text-align: right;">Editar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cc->projects as $prj)
                                            <tr>
                                                <td><span class="badge b-code">{{ $prj->code }}</span></td>
                                                <td>
                                                    <strong>{{ $prj->name }}</strong><br>
                                                    <span style="font-size:0.75rem; color:#94a3b8;">{{ $prj->description }}</span>
                                                </td>
                                                <td>
                                                    {{ $prj->start_date ? $prj->start_date->format('d/m/y') : '--' }} /
                                                    {{ $prj->end_date ? $prj->end_date->format('d/m/y') : '--' }}
                                                </td>
                                                <td>
                                                    <span class="badge {{ $prj->is_active ? 'b-active' : 'b-inactive' }}">{{ $prj->status }}</span>
                                                </td>
                                                <td style="text-align: right;">
                                                    <button class="btn-icon" onclick="openPrjModal({{ $cc->id }}, {{ json_encode($prj) }})"><i class="bx bx-edit"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="empty-state">
                                    <i class="bx bx-folder-open"></i>
                                    <p style="margin:0;">No hay proyectos asociados a este centro de costos.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if($costCenters->isEmpty())
                    <div class="empty-state" style="padding: 4rem;">
                        <i class="bx bx-building-house"></i>
                        <h3>Sin Centros de Costo</h3>
                        <p>Comienza creando el primer centro contable para el registro de gastos.</p>
                    </div>
                @endif
            </div>
        </div>


        {{-- ========================================================
             PESTAÑA 2: BÓVEDA SAT (Nodos de Seguridad)
             ======================================================== --}}
        <div id="tab-sat" class="tab-pane">
            <div class="content-header">
                <div class="content-title">
                    <h2><i class="bx bx-shield-quarter"></i> Nodos Criptográficos</h2>
                    <p>Monitorización y cifrado de credenciales de red para facturación electrónica SAT.</p>
                </div>
                <button class="btn-st" onclick="openNodeModal()">
                    <i class="bx bx-shield-plus"></i> Configurar Nuevo Nodo
                </button>
            </div>

            <div class="data-table-wrapper" style="padding: 1rem;">
                <table class="st-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;"># ID</th>
                            <th>Entidad / Razón Social</th>
                            <th>Identificador (RFC)</th>
                            <th>Validez (Inicio - Fin)</th>
                            <th>Estado del Nodo</th>
                            <th style="text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($nodes as $node)
                            @php
                                $isExpired = $node->end_date && clone $node->end_date->startOfDay() < now()->startOfDay();
                                $nodeData = json_encode([
                                    'id'         => $node->id,
                                    'entity_n'   => $node->e_name,
                                    'gov_id'     => $node->g_id,
                                    'start_date' => $node->start_date ? $node->start_date->format('Y-m-d') : '',
                                    'end_date'   => $node->end_date ? $node->end_date->format('Y-m-d') : ''
                                ]);
                            @endphp
                            <tr>
                                <td><strong>{{ $node->id }}</strong></td>
                                <td>{{ $node->e_name }}</td>
                                <td><span style="font-family: monospace; font-weight: 600; color:var(--st-accent);">{{ $node->g_id }}</span></td>
                                <td>
                                    @if($node->start_date && $node->end_date)
                                        {{ $node->start_date->format('d/m/Y') }} <i class="bx bx-right-arrow-alt" style="color:#94a3b8;"></i> {{ $node->end_date->format('d/m/Y') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if($node->is_live)
                                        @if($isExpired)
                                            <span class="badge-expired" title="El certificado ha caducado.">
                                                <i class="bx bx-error-circle"></i> Renovar
                                            </span>
                                        @else
                                            <span class="badge-live"><i class="bx bx-check-circle"></i> Activo</span>
                                        @endif
                                    @else
                                        <span class="badge-history"><i class="bx bx-archive"></i> Histórico</span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    @if($node->is_live && $isExpired)
                                        <button class="btn-alert" onclick="openNodeModal({{ $nodeData }})"><i class="bx bx-refresh"></i> Renovar</button>
                                    @elseif($node->is_live)
                                        <button class="btn-st" onclick="openNodeModal({{ $nodeData }})"><i class="bx bx-edit"></i> Actualizar</button>
                                    @else
                                        <button class="btn-st-outline btn-st" style="padding: 0.4rem 0.8rem;" onclick="openNodeModal({{ $nodeData }})"><i class="bx bx-edit-alt"></i> Editar</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($nodes->isEmpty())
                    <div class="empty-state" style="padding: 4rem;">
                        <div style="background: rgba(2, 132, 199, 0.05); display:inline-flex; padding: 1.5rem; border-radius: 50%; margin-bottom: 1rem;">
                            <i class="bx bx-shield-x" style="font-size: 3rem; color: var(--st-accent); margin:0;"></i>
                        </div>
                        <h4 style="margin: 0; color: var(--st-text-dark);">Sin Nodos Configurados</h4>
                        <p style="margin: 0.5rem 0 0 0; color: #64748b; max-width: 400px; margin-left: auto; margin-right:auto;">El sistema requiere la vinculación de un nodo seguro para habilitar la descarga masiva de facturas.</p>
                    </div>
                @endif
            </div>
        </div>

    </main>
</div>

{{-- ════════════════════════════════════════════════════════════════════════
     SECCIÓN DE MODALES (CENTRO DE COSTO, PROYECTO Y NODO SAT)
     ════════════════════════════════════════════════════════════════════════ --}}

{{-- MODAL: CENTRO DE COSTOS ── --}}
<div class="st-modal-overlay" id="modal-cc">
    <div class="st-modal-box">
        <div class="st-modal-header">
            <h3 id="cc-modal-title"><i class="bx bx-building-house"></i> Nuevo Centro de Costo</h3>
            <button class="btn-icon" onclick="closeModals()"><i class="bx bx-x"></i></button>
        </div>
        <form id="form-cc" onsubmit="submitCc(event)">
            @csrf
            <input type="hidden" id="cc_id" name="id">
            <div class="st-modal-body">
                <div class="st-form-group">
                    <label>Código Contable / Prefijo *</label>
                    <input type="text" id="cc_code" name="code" class="st-input-simple" placeholder="Ej. SUM" required maxlength="50" style="text-transform: uppercase;">
                </div>
                <div class="st-form-group">
                    <label>Nombre del Departamento *</label>
                    <input type="text" id="cc_name" name="name" class="st-input-simple" placeholder="Ej. Suministros" required>
                </div>
                <div class="st-form-group">
                    <label>Descripción</label>
                    <input type="text" id="cc_desc" name="description" class="st-input-simple" placeholder="Opcional">
                </div>
                <div class="st-form-group" style="margin-top: 0.5rem;">
                    <label class="switch-container">
                        <input type="checkbox" id="cc_active" name="is_active" value="1" checked>
                        <div class="switch-slider"></div>
                        <span class="switch-label">Centro Activo (Permitir Imputaciones)</span>
                    </label>
                </div>
            </div>
            <div class="st-modal-footer">
                <button type="button" class="btn-st btn-st-outline" onclick="closeModals()">Cancelar</button>
                <button type="submit" class="btn-st" id="btn-submit-cc">Guardar Centro</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: PROYECTO ── --}}
<div class="st-modal-overlay" id="modal-prj">
    <div class="st-modal-box" style="max-width: 600px;">
        <div class="st-modal-header">
            <h3 id="prj-modal-title"><i class="bx bx-briefcase"></i> Nuevo Proyecto</h3>
            <button class="btn-icon" onclick="closeModals()"><i class="bx bx-x"></i></button>
        </div>
        <form id="form-prj" onsubmit="submitPrj(event)">
            @csrf
            <input type="hidden" id="prj_id" name="id">
            <input type="hidden" id="prj_cc_id" name="cost_center_id">

            <div class="st-modal-body grid-layout" style="gap: 1rem;">

                <div class="st-form-group" style="grid-column: 1 / -1;">
                    <label>Centro de Costo Padre</label>
                    <input type="text" id="prj_cc_name" class="st-input-simple" readonly style="background: #f1f5f9; color: #64748b;">
                </div>

                <div class="st-form-group">
                    <label>Código de Proyecto *</label>
                    <input type="text" id="prj_code" name="code" class="st-input-simple" placeholder="Ej. SUM-WKSH" required maxlength="50" style="text-transform: uppercase;">
                </div>

                <div class="st-form-group">
                    <label>Nombre del Proyecto *</label>
                    <input type="text" id="prj_name" name="name" class="st-input-simple" required>
                </div>

                <div class="st-form-group" style="grid-column: 1 / -1;">
                    <label>Descripción / Alcance</label>
                    <input type="text" id="prj_desc" name="description" class="st-input-simple">
                </div>

                <div class="st-form-group">
                    <label>Fecha Estimada Inicio</label>
                    <input type="date" id="prj_start" name="start_date" class="st-input-simple">
                </div>

                <div class="st-form-group">
                    <label>Fecha Estimada Fin</label>
                    <input type="date" id="prj_end" name="end_date" class="st-input-simple">
                </div>

                <div class="st-form-group">
                    <label>Estado Operativo</label>
                    <select id="prj_status" name="status" class="st-input-simple" required>
                        <option value="Activo">Activo</option>
                        <option value="Planificacion">En Planificación</option>
                        <option value="Pausado">Pausado</option>
                        <option value="Cerrado">Cerrado</option>
                    </select>
                </div>

                <div class="st-form-group" style="display: flex; align-items: flex-end; padding-bottom: 0.5rem;">
                    <label class="switch-container">
                        <input type="checkbox" id="prj_active" name="is_active" value="1" checked>
                        <div class="switch-slider"></div>
                        <span class="switch-label">Imputación Abierta</span>
                    </label>
                </div>

            </div>
            <div class="st-modal-footer">
                <button type="button" class="btn-st btn-st-outline" onclick="closeModals()">Cancelar</button>
                <button type="submit" class="btn-st" id="btn-submit-prj">Guardar Proyecto</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: NODO SAT (SEGURIDAD) ── --}}
<div class="st-modal-overlay" id="node-modal">
    <div class="st-modal-box wide">
        <div class="st-modal-header">
            <h3><i class="bx bx-lock-alt" style="color:var(--st-accent);"></i> <span id="node-modal-title">Configuración de Nodo Criptográfico</span></h3>
            <button class="btn-icon" onclick="closeModals()"><i class="bx bx-x"></i></button>
        </div>
        <form action="{{ route('expense-claims.node.store') }}" method="POST" enctype="multipart/form-data" id="sat-vault-form">
            @csrf
            <input type="hidden" name="node_id" id="node_id" value="">

            <div class="st-modal-body grid-layout">

                {{-- Alerta de Seguridad --}}
                <div style="grid-column: 1 / -1; background: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid #f59e0b; padding: 1rem; border-radius: 0.5rem; display: flex; gap: 1rem; align-items: flex-start;">
                    <i class="bx bx-info-circle" style="color: #d97706; font-size: 1.2rem; margin-top:0.1rem;"></i>
                    <p style="margin: 0; font-size: 0.8rem; color: #92400e; line-height:1.4;">
                            <strong>Garantía de Aislamiento:</strong> El sistema procesa la información mediante algoritmos de cifrado avanzado de extremo a extremo. Los archivos se depositan en una bóveda digital aislada y completamente inaccesible desde la red pública. Al autorizar un nuevo nodo, la iteración anterior quedará bloqueada en estado Histórico para auditoría interna. Si actualiza un nodo, deje en blanco los archivos/contraseña para conservar los actuales.
                    </p>
                </div>

                {{-- Fila 1 --}}
                <div class="st-form-group" style="grid-column: 1 / -1;">
                    <label>Identidad de la Entidad (Razón Social)</label>
                    <div class="st-input-group">
                        <i class="bx bx-buildings field-icon"></i>
                        <input type="text" name="entity_n" id="entity_n" class="st-input" placeholder="Ej. Vinco Energy Services, S.A. de C.V." required autocomplete="off">
                    </div>
                </div>

                {{-- Fila 2 --}}
                <div class="st-form-group">
                    <label>ID Gubernamental (RFC)</label>
                    <div class="st-input-group">
                        <i class="bx bx-id-card field-icon"></i>
                        <input type="text" name="gov_id" id="gov_id" class="st-input" placeholder="VES1607057K7" maxlength="13" style="text-transform: uppercase;" required autocomplete="off">
                    </div>
                </div>

                <div class="st-form-group">
                    <label>Token de Seguridad (Passphrase)</label>
                    <div class="st-input-group">
                        <i class="bx bx-key field-icon"></i>
                        <input type="password" name="s_token" id="passphrase-input" class="st-input" placeholder="••••••••••••" autocomplete="new-password">
                        <i class="bx bx-hide" id="toggle-password" style="position: absolute; right: 1rem; color: #94a3b8; cursor: pointer; font-size: 1.2rem;"></i>
                    </div>
                    <small id="token-hint" style="color:#94a3b8; font-size:0.75rem; margin-top:0.2rem; display:none;">Déjalo en blanco para mantener el actual.</small>
                </div>

                {{-- Fila 3: Fechas --}}
                <div class="st-form-group">
                    <label>Fecha de Emisión (Inicio)</label>
                    <div class="st-input-group">
                        <i class="bx bx-calendar field-icon"></i>
                        <input type="text" name="start_d" id="start_d" class="st-input date-picker" placeholder="Seleccione fecha..." required>
                    </div>
                </div>

                <div class="st-form-group">
                    <label>Fecha de Caducidad (Fin)</label>
                    <div class="st-input-group">
                        <i class="bx bx-calendar-x field-icon"></i>
                        <input type="text" name="end_d" id="end_d" class="st-input date-picker" placeholder="Seleccione fecha..." required>
                    </div>
                </div>

                {{-- Fila 4: Archivos --}}
                <div class="st-form-group">
                    <label>Documento de Certificación (.CER)</label>
                    <div class="file-upload-wrapper" id="cer-wrapper">
                        <i class="bx bx-badge-check" id="cer-icon"></i>
                        <p id="cer-text">Cargar archivo .cer</p>
                        <small id="cer-subtext">Certificado público de la entidad</small>
                        <input type="file" name="doc_c" id="cer-input" accept=".cer">
                    </div>
                </div>

                <div class="st-form-group">
                    <label>Binario Privado (.KEY)</label>
                    <div class="file-upload-wrapper" id="key-wrapper">
                        <i class="bx bx-file" id="key-icon"></i>
                        <p id="key-text">Cargar archivo .key</p>
                        <small id="key-subtext">Llave privada de encriptación</small>
                        <input type="file" name="doc_k" id="key-input" accept=".key">
                    </div>
                </div>

            </div>
            <div class="st-modal-footer">
                <button type="button" class="btn-st btn-st-outline" onclick="closeModals()">Cancelar Operación</button>
                <button type="submit" class="btn-st" id="btn-submit-vault"><i class="bx bx-save"></i> Procesar y Cifrar Nodo</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>

<script>
    // ── NAVEGACIÓN POR PESTAÑAS (TABS) ──
    function switchTab(tabId, btnElement) {
        // Quitar active de todos los botones y pestañas
        document.querySelectorAll('.sidebar-nav button').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));

        // Agregar active al clickeado y su contenedor
        btnElement.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    }

    // ── VARIABLES GLOBALES ──
    const costCenters = {!! json_encode($costCenters) !!};
    let fpStart, fpEnd;

    // ── ACORDEÓN DE PROYECTOS ──
    function toggleProjects(id) {
        const body = document.getElementById('prj-body-' + id);
        const icon = document.getElementById('icon-chevron-' + id);
        if(body.classList.contains('open')) {
            body.classList.remove('open');
            icon.style.transform = 'rotate(0deg)';
        } else {
            body.classList.add('open');
            icon.style.transform = 'rotate(180deg)';
        }
    }

    // ── CONTROL GLOBAL DE MODALES ──
    function closeModals() {
        document.querySelectorAll('.st-modal-overlay').forEach(m => m.classList.remove('active'));
    }

    // Cerrar al hacer clic fuera del modal
    document.querySelectorAll('.st-modal-overlay').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) closeModals();
        });
    });

    // ── MODAL: CENTRO DE COSTOS ──
    function openCcModal(cc = null) {
        const form = document.getElementById('form-cc');
        form.reset();

        if (cc) {
            document.getElementById('cc-modal-title').innerHTML = '<i class="bx bx-edit"></i> Editar Centro de Costo';
            document.getElementById('cc_id').value = cc.id;
            document.getElementById('cc_code').value = cc.code;
            document.getElementById('cc_name').value = cc.name;
            document.getElementById('cc_desc').value = cc.description || '';
            document.getElementById('cc_active').checked = cc.is_active;
        } else {
            document.getElementById('cc-modal-title').innerHTML = '<i class="bx bx-building-house"></i> Nuevo Centro de Costo';
            document.getElementById('cc_id').value = '';
            document.getElementById('cc_active').checked = true;
        }
        document.getElementById('modal-cc').classList.add('active');
    }

    async function submitCc(e) {
        e.preventDefault();
        const id = document.getElementById('cc_id').value;
        const url = id ? `{{ url('administration/expense-claims/settings/cost-centers') }}/${id}` : `{{ route('expense-claims.settings.cc.store') }}`;
        const formData = new FormData(e.target);
        if(!document.getElementById('cc_active').checked) formData.append('is_active', 0);
        sendAjax(url, formData, 'btn-submit-cc');
    }

    // ── MODAL: PROYECTOS ──
    function openPrjModal(ccId, prj = null) {
        const form = document.getElementById('form-prj');
        form.reset();

        const parentCc = costCenters.find(c => c.id === ccId);
        document.getElementById('prj_cc_name').value = `[${parentCc.code}] ${parentCc.name}`;
        document.getElementById('prj_cc_id').value = ccId;

        if (prj) {
            document.getElementById('prj-modal-title').innerHTML = '<i class="bx bx-edit"></i> Editar Proyecto';
            document.getElementById('prj_id').value = prj.id;
            document.getElementById('prj_code').value = prj.code;
            document.getElementById('prj_name').value = prj.name;
            document.getElementById('prj_desc').value = prj.description || '';
            if(prj.start_date) document.getElementById('prj_start').value = prj.start_date.split('T')[0];
            if(prj.end_date) document.getElementById('prj_end').value = prj.end_date.split('T')[0];
            document.getElementById('prj_status').value = prj.status;
            document.getElementById('prj_active').checked = prj.is_active;
        } else {
            document.getElementById('prj-modal-title').innerHTML = '<i class="bx bx-briefcase"></i> Nuevo Proyecto';
            document.getElementById('prj_id').value = '';
            document.getElementById('prj_code').value = parentCc.code + '-';
            document.getElementById('prj_active').checked = true;
        }
        document.getElementById('modal-prj').classList.add('active');
    }

    async function submitPrj(e) {
        e.preventDefault();
        const id = document.getElementById('prj_id').value;
        const url = id ? `{{ url('administration/expense-claims/settings/projects') }}/${id}` : `{{ route('expense-claims.settings.prj.store') }}`;
        const formData = new FormData(e.target);
        if(!document.getElementById('prj_active').checked) formData.append('is_active', 0);
        sendAjax(url, formData, 'btn-submit-prj');
    }

    // ── MODAL: BÓVEDA SAT (SEGURIDAD) ──
    const formVault = document.getElementById('sat-vault-form');

    function openNodeModal(node = null) {
        formVault.reset();

        document.getElementById('cer-wrapper').classList.remove('has-file');
        document.getElementById('cer-text').textContent = 'Cargar archivo .cer';
        document.getElementById('cer-text').style.color = 'var(--st-text-muted)';
        document.getElementById('cer-subtext').textContent = 'Certificado público de la entidad';

        document.getElementById('key-wrapper').classList.remove('has-file');
        document.getElementById('key-text').textContent = 'Cargar archivo .key';
        document.getElementById('key-text').style.color = 'var(--st-text-muted)';
        document.getElementById('key-subtext').textContent = 'Llave privada de encriptación';

        if (node) {
            document.getElementById('node-modal-title').textContent = 'Actualizar Nodo Criptográfico';
            document.getElementById('node_id').value = node.id;
            document.getElementById('entity_n').value = node.entity_n;
            document.getElementById('gov_id').value = node.gov_id;
            fpStart.setDate(node.start_date);
            fpEnd.setDate(node.end_date);

            document.getElementById('passphrase-input').removeAttribute('required');
            document.getElementById('cer-input').removeAttribute('required');
            document.getElementById('key-input').removeAttribute('required');

            document.getElementById('token-hint').style.display = 'block';
            document.getElementById('cer-subtext').textContent = 'Opcional si no cambia';
            document.getElementById('key-subtext').textContent = 'Opcional si no cambia';
        } else {
            document.getElementById('node-modal-title').textContent = 'Configuración de Nodo Criptográfico';
            document.getElementById('node_id').value = '';
            fpStart.clear();
            fpEnd.clear();

            document.getElementById('passphrase-input').setAttribute('required', 'required');
            document.getElementById('cer-input').setAttribute('required', 'required');
            document.getElementById('key-input').setAttribute('required', 'required');
            document.getElementById('token-hint').style.display = 'none';
        }
        document.getElementById('node-modal').classList.add('active');
    }

    // ── INICIALIZADORES (Fechas, Password y Uploads) ──
    document.addEventListener('DOMContentLoaded', function() {

        // Fechas Flatpickr
        fpStart = flatpickr("#start_d", { locale: "es", dateFormat: "Y-m-d", altInput: true, altFormat: "d/m/Y", allowInput: true });
        fpEnd = flatpickr("#end_d", { locale: "es", dateFormat: "Y-m-d", altInput: true, altFormat: "d/m/Y", allowInput: true });

        // Ver Contraseña
        const togglePassword = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('passphrase-input');
        if(togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                if (type === 'text') {
                    this.classList.remove('bx-hide'); this.classList.add('bx-show'); this.style.color = 'var(--st-accent)';
                } else {
                    this.classList.remove('bx-show'); this.classList.add('bx-hide'); this.style.color = '#94a3b8';
                }
            });
        }

        // Drag & Drop visual
        function setupFileInput(inputId, wrapperId, textId, subtextId, defaultText) {
            const input = document.getElementById(inputId);
            const wrapper = document.getElementById(wrapperId);
            const text = document.getElementById(textId);
            const subtext = document.getElementById(subtextId);

            if(!input) return;
            input.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    wrapper.classList.add('has-file');
                    text.textContent = 'Archivo Listo';
                    text.style.color = 'var(--st-accent)';
                    subtext.textContent = this.files[0].name;
                } else {
                    wrapper.classList.remove('has-file');
                    text.textContent = defaultText;
                    text.style.color = 'var(--st-text-muted)';
                    subtext.textContent = document.getElementById('node_id').value ? 'Opcional si no cambia' : (inputId === 'cer-input' ? 'Certificado público de la entidad' : 'Llave privada de encriptación');
                }
            });
        }

        setupFileInput('cer-input', 'cer-wrapper', 'cer-text', 'cer-subtext', 'Cargar archivo .cer');
        setupFileInput('key-input', 'key-wrapper', 'key-text', 'key-subtext', 'Cargar archivo .key');

        // Submit de Nodo SAT (Maneja Formdata con Archivos de forma distinta)
        if(formVault) {
            formVault.addEventListener('submit', function(e) {
                e.preventDefault();
                const btnSubmit = document.getElementById('btn-submit-vault');
                const originalBtnContent = btnSubmit.innerHTML;
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Procesando...';

                const formData = new FormData(formVault);

                fetch(formVault.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModals();
                        Swal.fire({ title: '¡Completado!', html: data.message, icon: 'success', confirmButtonColor: '#152845' })
                        .then(() => window.location.reload());
                    } else {
                        let errorText = data.message;
                        if(data.errors) errorText = Object.values(data.errors).map(err => err.join(', ')).join('<br>');
                        Swal.fire({ title: 'Error de Verificación', html: errorText, icon: 'warning', confirmButtonColor: '#152845' });
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = originalBtnContent;
                    }
                })
                .catch(error => {
                    Swal.fire({ title: 'Error de Servidor', text: 'Ocurrió un error inesperado al enviar los datos.', icon: 'error', confirmButtonColor: '#152845' });
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = originalBtnContent;
                });
            });
        }
    });

    // ── FUNCIÓN AJAX GLOBAL (Para Centros y Proyectos) ──
    async function sendAjax(url, formData, btnId) {
        const btn = document.getElementById(btnId);
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Guardando...';
        btn.disabled = true;

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();

            if (data.success) {
                closeModals();
                Swal.fire({ icon: 'success', title: '¡Éxito!', text: data.message, confirmButtonColor: '#152845' })
                .then(() => window.location.reload());
            } else {
                let errText = data.message;
                if(data.errors) errText = Object.values(data.errors).map(e => e.join(', ')).join('<br>');
                Swal.fire({ icon: 'error', title: 'Error', html: errText, confirmButtonColor: '#152845' });
            }
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Error de Red', text: 'No se pudo conectar al servidor.', confirmButtonColor: '#152845' });
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
</script>
@endpush
