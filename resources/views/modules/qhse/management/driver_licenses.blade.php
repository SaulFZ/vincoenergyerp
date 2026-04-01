@extends('modules.qhse.management.index')

@push('css')
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue-main: #1d4ed8;
            --blue-light: #eff6ff;
            --blue-mid: #bfdbfe;
            --green: #16a34a;
            --orange: #ea580c;
            --yellow: #d97706;
            --red: #dc2626;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-400: #94a3b8;
            --gray-600: #475569;
            --gray-800: #1e293b;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, .08);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, .10);
            --shadow-lg: 0 12px 40px rgba(0, 0, 0, .14);
            --radius: 12px;
        }

        * {
            box-sizing: border-box;
        }

        .lic-wrap {
            font-family: 'DM Sans', sans-serif;
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            padding: 28px;
            margin-top: 20px;
            margin-bottom: 30px;
        }

        /* ── HEADER ── */
        .lic-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--gray-200);
            margin-bottom: 24px;
        }

        .lic-header-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .lic-header-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: var(--blue-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--blue-main);
            font-size: 1.15rem;
        }

        .lic-header-title h2 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--gray-800);
        }

        .lic-header-title p {
            margin: 2px 0 0;
            font-size: 0.78rem;
            color: var(--gray-400);
        }

        .lic-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* ── INPUTS DE CONTROL ── */
        .ctrl-select,
        .ctrl-search {
            height: 40px;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            background: var(--gray-50);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.875rem;
            color: var(--gray-800);
            padding: 0 14px;
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }

        .ctrl-select:focus,
        .ctrl-search:focus {
            border-color: var(--blue-main);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(29, 78, 216, .12);
        }

        .ctrl-search-wrap {
            position: relative;
        }

        .ctrl-search-wrap i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: 0.85rem;
            pointer-events: none;
        }

        .ctrl-search {
            padding-left: 36px;
            width: 240px;
            transition: width .3s, border-color .2s, box-shadow .2s, background .2s;
        }

        .ctrl-search:focus {
            width: 290px;
        }

        /* ── TABLA ── */
        .tbl-scroll {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 580px;
            border-radius: 10px;
            border: 1px solid var(--gray-200);
            scrollbar-width: thin;
            scrollbar-color: var(--blue-main) var(--gray-100);
        }

        .tbl-scroll::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .tbl-scroll::-webkit-scrollbar-track {
            background: var(--gray-100);
            border-radius: 4px;
        }

        .tbl-scroll::-webkit-scrollbar-thumb {
            background: var(--blue-main);
            border-radius: 4px;
        }

        .tbl-licencias {
            width: 100%;
            min-width: 1050px;
            border-collapse: collapse;
            background: #fff;
        }

        .tbl-licencias thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .tbl-licencias th {
            background: var(--gray-50);
            color: var(--gray-400);
            font-weight: 600;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: .6px;
            text-align: center;
            padding: 14px 12px;
            border-bottom: 1px solid var(--gray-200);
            white-space: nowrap;
        }

        .tbl-licencias th:first-child {
            text-align: left;
            padding-left: 20px;
        }

        .tbl-licencias td {
            padding: 14px 12px;
            border-bottom: 1px solid var(--gray-100);
            text-align: center;
            vertical-align: middle;
            font-size: 0.875rem;
            color: var(--gray-800);
        }

        .tbl-licencias td:first-child {
            padding-left: 20px;
        }

        .tbl-licencias tbody tr {
            transition: background .15s;
        }

        .tbl-licencias tbody tr:hover {
            background: var(--gray-50);
        }

        .tbl-licencias tbody tr:last-child td {
            border-bottom: none;
        }

        /* ── EMPLEADO CELL ── */
        .emp-cell {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .emp-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-400);
            font-size: 1.1rem;
            flex-shrink: 0;
            overflow: hidden;
        }

        .emp-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--gray-800);
            margin: 0 0 2px;
        }

        .emp-dept {
            font-size: 0.75rem;
            color: var(--gray-400);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ── BADGES ── */
        .bdg {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 11px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .bdg-ok {
            background: #dcfce7;
            color: #15803d;
        }

        .bdg-warn {
            background: #fef9c3;
            color: #92400e;
        }

        .bdg-orange {
            background: #ffedd5;
            color: #9a3412;
        }

        .bdg-danger {
            background: #fee2e2;
            color: #b91c1c;
        }

        .bdg-none {
            background: var(--gray-100);
            color: var(--gray-400);
        }

        /* Tooltip */
        [data-tip] {
            position: relative;
            cursor: help;
        }

        [data-tip]:before {
            content: attr(data-tip);
            position: absolute;
            bottom: calc(100% + 6px);
            left: 50%;
            transform: translateX(-50%);
            padding: 4px 10px;
            background: var(--gray-800);
            color: #fff;
            font-size: 0.68rem;
            border-radius: 5px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: opacity .2s;
            pointer-events: none;
            z-index: 999;
        }

        [data-tip]:hover:before {
            opacity: 1;
            visibility: visible;
        }

        /* ── BOTÓN ACTUALIZAR ── */
        .btn-upd {
            background: var(--blue-light);
            color: var(--blue-main);
            border: 1px solid var(--blue-mid);
            padding: 7px 14px;
            border-radius: 7px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all .2s;
        }

        .btn-upd:hover {
            background: var(--blue-main);
            color: #fff;
            border-color: var(--blue-main);
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(29, 78, 216, .25);
        }

        /* ── PAGINACIÓN ── */
        .pag-wrap {
            display: flex;
            justify-content: center;
            padding: 24px 0 4px;
        }

        .pag {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            gap: 6px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .pag .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 10px;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            color: var(--gray-600);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            background: #fff;
            transition: all .2s;
        }

        .pag .page-item.active .page-link {
            background: var(--blue-main);
            color: #fff;
            border-color: var(--blue-main);
            pointer-events: none;
        }

        .pag .page-item:not(.active):not(.disabled) .page-link:hover {
            background: var(--blue-light);
            color: var(--blue-main);
            border-color: var(--blue-mid);
        }

        .pag .page-item.disabled .page-link {
            background: var(--gray-50);
            color: var(--gray-200);
            cursor: not-allowed;
            pointer-events: none;
        }

        /* ── EMPTY / LOADER ── */
        .empty-cell {
            text-align: center;
            padding: 50px 20px;
            color: var(--gray-400);
        }

        .empty-cell i {
            font-size: 2.5rem;
            margin-bottom: 12px;
            opacity: .4;
        }

        .empty-cell p {
            margin: 8px 0 0;
            font-size: 0.9rem;
        }

        .spinner-cell {
            text-align: center;
            padding: 50px 20px;
        }

        .spin {
            display: inline-block;
            width: 36px;
            height: 36px;
            border: 3px solid var(--gray-200);
            border-top-color: var(--blue-main);
            border-radius: 50%;
            animation: spinning 0.8s linear infinite;
            margin-bottom: 12px;
        }

        @keyframes spinning {
            to {
                transform: rotate(360deg);
            }
        }

        /* ── MODAL ── */
        .modal-lic {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .55);
            backdrop-filter: blur(5px);
            z-index: 1050;
            align-items: center;
            justify-content: center;
        }

        .modal-lic.open {
            display: flex;
        }

        .modal-box {
            background: #fff;
            border-radius: 16px;
            width: 95%;
            max-width: 680px;
            max-height: 92vh;
            overflow-y: auto;
            padding: 32px;
            box-shadow: var(--shadow-lg);
            animation: modalIn .25s cubic-bezier(.34, 1.56, .64, 1);
            scrollbar-width: thin;
        }

        @keyframes modalIn {
            from {
                opacity: 0;
                transform: scale(.94) translateY(16px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
        }

        .modal-head-info h3 {
            margin: 0 0 4px;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--gray-800);
        }

        .modal-head-info p {
            margin: 0;
            font-size: 0.8rem;
            color: var(--gray-400);
        }

        .modal-close {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: var(--gray-100);
            color: var(--gray-600);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .2s;
            flex-shrink: 0;
        }

        .modal-close:hover {
            background: #fee2e2;
            color: var(--red);
        }

        .modal-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        @media (max-width: 600px) {
            .modal-grid {
                grid-template-columns: 1fr;
            }
        }

        .modal-card {
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--gray-200);
        }

        .modal-card.light-v {
            background: var(--blue-light);
            border-color: var(--blue-mid);
        }

        .modal-card.heavy-v {
            background: #fff7ed;
            border-color: #fed7aa;
        }

        .modal-card-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            font-weight: 700;
            margin: 0 0 18px;
        }

        .modal-card.light-v .modal-card-title {
            color: var(--blue-main);
        }

        .modal-card.heavy-v .modal-card-title {
            color: #c2410c;
        }

        .fg {
            margin-bottom: 16px;
        }

        .fg:last-child {
            margin-bottom: 0;
        }

        .fg label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--gray-600);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .fg-input-wrap {
            position: relative;
        }

        .fg-input-wrap i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: 0.85rem;
            pointer-events: none;
        }

        .fg-input-wrap input {
            width: 100%;
            height: 40px;
            padding: 0 36px 0 12px;
            border: 1.5px solid var(--gray-200);
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.875rem;
            color: var(--gray-800);
            background: #fff;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }

        .fg-input-wrap input:focus {
            border-color: var(--blue-main);
            box-shadow: 0 0 0 3px rgba(29, 78, 216, .12);
        }

        /* Campo modificado */
        .fg-input-wrap input.changed {
            border-color: #f59e0b;
            background: #fffbeb;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, .12);
        }

        .change-tag {
            display: none;
            font-size: 0.68rem;
            color: #b45309;
            font-weight: 600;
            margin-top: 4px;
        }

        .change-tag.visible {
            display: block;
        }

        /* ── RESUMEN DE CAMBIOS ── */
        .changes-summary {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 16px;
            display: none;
        }

        .changes-summary.visible {
            display: block;
        }

        .changes-summary-title {
            font-size: 0.78rem;
            font-weight: 700;
            color: #92400e;
            margin: 0 0 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .change-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.78rem;
            color: #78350f;
            padding: 4px 0;
            border-bottom: 1px dashed #fde68a;
        }

        .change-item:last-child {
            border-bottom: none;
        }

        .change-item .old-val {
            text-decoration: line-through;
            color: var(--red);
            opacity: .7;
        }

        .change-item .new-val {
            color: var(--green);
            font-weight: 600;
        }

        /* ── FOOTER DEL MODAL ── */
        .modal-foot {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-100);
        }

        .btn-cancel {
            height: 40px;
            padding: 0 20px;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            background: #fff;
            color: var(--gray-600);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all .2s;
        }

        .btn-cancel:hover {
            background: var(--gray-100);
            color: var(--gray-800);
        }

        .btn-save {
            height: 40px;
            padding: 0 24px;
            border: none;
            border-radius: 8px;
            background: var(--blue-main);
            color: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all .2s;
            box-shadow: 0 2px 8px rgba(29, 78, 216, .3);
        }

        .btn-save:hover {
            background: #1e40af;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(29, 78, 216, .4);
        }

        .btn-save:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none;
        }

        /* Badge "cambios pendientes" en el botón guardar */
        .save-badge {
            background: #fbbf24;
            color: #78350f;
            border-radius: 99px;
            font-size: 0.65rem;
            padding: 1px 6px;
            font-weight: 700;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .lic-wrap {
                padding: 16px;
            }

            .lic-controls {
                flex-direction: column;
                width: 100%;
            }

            .ctrl-search,
            .ctrl-search:focus {
                width: 100%;
            }

            .ctrl-search-wrap {
                width: 100%;
            }

            .modal-box {
                padding: 20px;
            }
        }
    </style>
@endpush

@php
    function renderLicenseBadge($dateString)
    {
        if (!$dateString) {
            return '<span class="bdg bdg-none"><i class="fas fa-minus"></i> No Registrado</span>';
        }
        $date = \Carbon\Carbon::parse($dateString)->startOfDay();
        $now = \Carbon\Carbon::now()->startOfDay();
        $diff = $now->diffInDays($date, false);
        $fmt = $date->format('d/m/Y');

        if ($diff < 0) {
            return '<span class="bdg bdg-danger" data-tip="Vencido"><i class="fas fa-times-circle"></i> ' .
                $fmt .
                '</span>';
        } elseif ($diff <= 30) {
            return '<span class="bdg bdg-orange" data-tip="Vence en ' .
                $diff .
                ' días"><i class="fas fa-exclamation-circle"></i> ' .
                $fmt .
                '</span>';
        } elseif ($diff <= 60) {
            return '<span class="bdg bdg-warn" data-tip="Vence en ' .
                $diff .
                ' días"><i class="fas fa-exclamation-triangle"></i> ' .
                $fmt .
                '</span>';
        } else {
            return '<span class="bdg bdg-ok" data-tip="Vigente"><i class="fas fa-check-circle"></i> ' .
                $fmt .
                '</span>';
        }
    }
@endphp

@section('content')
    <div class="lic-wrap">

        {{-- HEADER --}}
        <div class="lic-header">
            <div class="lic-header-title">
                <div class="lic-header-icon"><i class="fas fa-id-card-alt"></i></div>
                <div>
                    <h2>Control de Licencias y Cursos</h2>
                    <p>Gestión de vigencias por conductor</p>
                </div>
            </div>

            <div class="lic-controls">
                <div style="display:flex;align-items:center;gap:8px;font-size:.85rem;color:var(--gray-400);">
                    <span>Mostrar</span>
                    <select id="per_page" class="ctrl-select">
                        @foreach ([5, 10, 15, 20, 25, 30, 50, 100] as $n)
                            <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="ctrl-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" class="ctrl-search" placeholder="Buscar conductor o área...">
                </div>
            </div>
        </div>

        {{-- TABLA --}}
        <div class="tbl-scroll" id="tblContainer">
            <table class="tbl-licencias" id="tblMain">
                <thead>
                    <tr>
                        <th style="text-align:left;">Empleado</th>
                        <th>Lic. Conductor <small style="font-weight:400;">(Ligera)</small></th>
                        <th>Curso Man. Def. <small style="font-weight:400;">(Ligera)</small></th>
                        <th>Lic. Federal <small style="font-weight:400;">(Pesada)</small></th>
                        <th>Curso Man. Def. <small style="font-weight:400;">(Pesada)</small></th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tblBody">
                    @forelse($empleados as $emp)
                        <tr>
                            <td>
                                <div class="emp-cell">
                                    <div class="emp-avatar">
                                        @if ($emp->photo)
                                            <img src="{{ asset($emp->photo) }}"
                                                style="width:100%;height:100%;object-fit:cover;border-radius:10px;">
                                        @else
                                            <i class="fas fa-user-tie"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="emp-name">
                                            {{ $emp->full_name ?? $emp->first_name . ' ' . $emp->first_surname }}</p>
                                        <span class="emp-dept"><i class="fas fa-sitemap"></i>
                                            {{ $emp->department ?? 'Sin departamento' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{!! renderLicenseBadge(optional($emp->license)->driver_license_expires_at) !!}</td>
                            <td>{!! renderLicenseBadge(optional($emp->license)->light_defensive_course_expires_at) !!}</td>
                            <td>{!! renderLicenseBadge(optional($emp->license)->federal_license_expires_at) !!}</td>
                            <td>{!! renderLicenseBadge(optional($emp->license)->heavy_defensive_course_expires_at) !!}</td>
                            <td>
                                <button class="btn-upd"
                                    onclick="openModal(
                            {{ $emp->id }},
                            '{{ addslashes($emp->full_name ?? $emp->first_name . ' ' . $emp->first_surname) }}',
                            '{{ optional($emp->license)->driver_license_expires_at ? optional($emp->license)->driver_license_expires_at->format('Y-m-d') : '' }}',
                            '{{ optional($emp->license)->light_defensive_course_expires_at ? optional($emp->license)->light_defensive_course_expires_at->format('Y-m-d') : '' }}',
                            '{{ optional($emp->license)->federal_license_expires_at ? optional($emp->license)->federal_license_expires_at->format('Y-m-d') : '' }}',
                            '{{ optional($emp->license)->heavy_defensive_course_expires_at ? optional($emp->license)->heavy_defensive_course_expires_at->format('Y-m-d') : '' }}'
                        )">
                                    <i class="fas fa-edit"></i> Actualizar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-cell">
                                    <i class="fas fa-users"></i>
                                    <p>No hay empleados registrados.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINACIÓN --}}
        <div class="pag-wrap">
            <ul class="pag" id="pagLinks"></ul>
        </div>
    </div>
@endsection

@section('modals')
    <div class="modal-lic" id="modalLic">
        <div class="modal-box">

            <div class="modal-head">
                <div class="modal-head-info">
                    <h3 id="modalTitle">Actualizar Credenciales</h3>
                    <p id="modalSubtitle">Modifica las fechas de vigencia</p>
                </div>
                <button class="modal-close" id="btnCloseModal" title="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Resumen de cambios --}}
            <div class="changes-summary" id="changesSummary">
                <p class="changes-summary-title"><i class="fas fa-exclamation-triangle"></i> Cambios pendientes de guardar
                </p>
                <div id="changesDetail"></div>
            </div>

            <form id="formLic" onsubmit="saveAJAX(event)">
                @csrf
                <input type="hidden" id="emp_id">

                <div class="modal-grid">
                    {{-- LIGERA --}}
                    <div class="modal-card light-v">
                        <p class="modal-card-title"><i class="fas fa-car"></i> Unidades Ligeras</p>

                        <div class="fg">
                            <label>Vigencia Lic. Conductor</label>
                            <div class="fg-input-wrap">
                                <input type="text" id="f_lic" class="flatpickr-date"
                                    placeholder="Seleccionar fecha...">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                            <span class="change-tag" id="tag_f_lic">Campo modificado</span>
                        </div>

                        <div class="fg">
                            <label>Vigencia Curso Man. Defensivo</label>
                            <div class="fg-input-wrap">
                                <input type="text" id="f_curso_lig" class="flatpickr-date"
                                    placeholder="Seleccionar fecha...">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                            <span class="change-tag" id="tag_f_curso_lig">Campo modificado</span>
                        </div>
                    </div>

                    {{-- PESADA --}}
                    <div class="modal-card heavy-v">
                        <p class="modal-card-title"><i class="fas fa-truck"></i> Unidades Pesadas</p>

                        <div class="fg">
                            <label>Vigencia Lic. Federal</label>
                            <div class="fg-input-wrap">
                                <input type="text" id="f_fed" class="flatpickr-date"
                                    placeholder="Seleccionar fecha...">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                            <span class="change-tag" id="tag_f_fed">Campo modificado</span>
                        </div>

                        <div class="fg">
                            <label>Vigencia Curso Man. Def. Pesada</label>
                            <div class="fg-input-wrap">
                                <input type="text" id="f_curso_pes" class="flatpickr-date"
                                    placeholder="Seleccionar fecha...">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                            <span class="change-tag" id="tag_f_curso_pes">Campo modificado</span>
                        </div>
                    </div>
                </div>

                <div class="modal-foot">
                    <button type="button" class="btn-cancel" id="btnCancel">Cancelar</button>
                    <button type="submit" class="btn-save" id="btnSave" disabled>
                        <i class="fas fa-save"></i>
                        Guardar
                        <span class="save-badge" id="saveBadge" style="display:none">0</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <script>
        // ── ESTADO GLOBAL ──────────────────────────────────────────────
        let currentPage = {{ $empleados->currentPage() }};
        let lastPage = {{ $empleados->lastPage() }};
        let searchTimer;

        // Valores originales al abrir el modal
        let originals = {
            f_lic: '',
            f_curso_lig: '',
            f_fed: '',
            f_curso_pes: ''
        };

        // Etiquetas legibles por campo
        const LABELS = {
            f_lic: 'Licencia Conductor (Ligera)',
            f_curso_lig: 'Curso Man. Def. (Ligera)',
            f_fed: 'Licencia Federal (Pesada)',
            f_curso_pes: 'Curso Man. Def. (Pesada)',
        };

        // ── FLATPICKR ──────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', () => {
            flatpickr('.flatpickr-date', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                locale: 'es',
                allowInput: true,
                disableMobile: true,
                onClose() {
                    detectChanges();
                }
            });

            renderPag(currentPage, lastPage);
            bindCloseModal();
        });

        // ── BÚSQUEDA ──────────────────────────────────────────────────
        document.getElementById('searchInput').addEventListener('keyup', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => fetchData(1), 380);
        });

        document.getElementById('per_page').addEventListener('change', () => fetchData(1));

        // ── FETCH TABLA ───────────────────────────────────────────────
        async function fetchData(page = 1) {
            currentPage = page;
            const perPage = document.getElementById('per_page').value;
            const search = document.getElementById('searchInput').value;
            const tbody = document.getElementById('tblBody');

            tbody.innerHTML = `
        <tr><td colspan="6">
            <div class="spinner-cell">
                <div class="spin"></div>
                <div style="color:var(--gray-400);font-size:.875rem;">Cargando datos...</div>
            </div>
        </td></tr>`;

            try {
                const res = await fetch(
                    `{{ route('management.licenses') }}?page=${page}&per_page=${perPage}&search=${encodeURIComponent(search)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    }
                );
                const data = await res.json();
                renderTable(data);
            } catch (err) {
                tbody.innerHTML = `
            <tr><td colspan="6">
                <div class="empty-cell">
                    <i class="fas fa-exclamation-triangle" style="color:var(--red)"></i>
                    <p>Error de conexión al cargar datos.</p>
                </div>
            </td></tr>`;
            }
        }

        // ── RENDER BADGE (JS) ─────────────────────────────────────────
        function badge(d) {
            if (!d) return '<span class="bdg bdg-none"><i class="fas fa-minus"></i> No Registrado</span>';
            const [y, m, day] = d.split('-');
            const date = new Date(y, m - 1, day);
            const now = new Date();
            now.setHours(0, 0, 0, 0);
            const diff = Math.ceil((date - now) / 86400000);
            const fmt = `${String(day).padStart(2,'0')}/${String(m).padStart(2,'0')}/${y}`;
            if (diff < 0)
            return `<span class="bdg bdg-danger" data-tip="Vencido"><i class="fas fa-times-circle"></i> ${fmt}</span>`;
            if (diff <= 30)
            return `<span class="bdg bdg-orange" data-tip="Vence en ${diff} días"><i class="fas fa-exclamation-circle"></i> ${fmt}</span>`;
            if (diff <= 60)
            return `<span class="bdg bdg-warn"   data-tip="Vence en ${diff} días"><i class="fas fa-exclamation-triangle"></i> ${fmt}</span>`;
            return `<span class="bdg bdg-ok"     data-tip="Vigente"><i class="fas fa-check-circle"></i> ${fmt}</span>`;
        }

        // ── RENDER TABLA ──────────────────────────────────────────────
        function renderTable(payload) {
            const tbody = document.getElementById('tblBody');

            if (!payload.data.length) {
                tbody.innerHTML = `
            <tr><td colspan="6">
                <div class="empty-cell">
                    <i class="fas fa-search"></i>
                    <p>No se encontraron resultados.</p>
                </div>
            </td></tr>`;
                document.getElementById('pagLinks').innerHTML = '';
                return;
            }

            tbody.innerHTML = payload.data.map(e => {
                const avatar = e.photo ?
                    `<img src="${e.photo}" style="width:100%;height:100%;object-fit:cover;border-radius:10px;">` :
                    `<i class="fas fa-user-tie"></i>`;
                const name = e.name.replace(/'/g, "\\'");
                return `
        <tr>
            <td>
                <div class="emp-cell">
                    <div class="emp-avatar">${avatar}</div>
                    <div>
                        <p class="emp-name">${e.name}</p>
                        <span class="emp-dept"><i class="fas fa-sitemap"></i> ${e.department || 'Sin departamento'}</span>
                    </div>
                </div>
            </td>
            <td>${badge(e.driver_license)}</td>
            <td>${badge(e.light_course)}</td>
            <td>${badge(e.federal_license)}</td>
            <td>${badge(e.heavy_course)}</td>
            <td>
                <button class="btn-upd" onclick="openModal(
                    ${e.id},'${name}',
                    '${e.driver_license||''}','${e.light_course||''}',
                    '${e.federal_license||''}','${e.heavy_course||''}'
                )"><i class="fas fa-edit"></i> Actualizar</button>
            </td>
        </tr>`;
            }).join('');

            renderPag(payload.pagination.current_page, payload.pagination.last_page);
        }

        // ── PAGINACIÓN ────────────────────────────────────────────────
        function renderPag(cur, last) {
            const el = document.getElementById('pagLinks');
            if (last <= 1) {
                el.innerHTML = '';
                return;
            }

            let html = '';
            const prev = cur === 1 ? 'disabled' : '';
            html +=
                `<li class="page-item ${prev}"><a class="page-link" data-page="${cur-1}"><i class="fas fa-chevron-left"></i></a></li>`;

            let s = Math.max(1, cur - 2);
            let e = Math.min(last, cur + 2);
            if (cur <= 3) e = Math.min(last, 5);
            if (cur >= last - 2) s = Math.max(1, last - 4);

            if (s > 1) {
                html += `<li class="page-item"><a class="page-link" data-page="1">1</a></li>`;
                if (s > 2) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
            }

            for (let i = s; i <= e; i++) {
                html += `<li class="page-item ${cur===i?'active':''}"><a class="page-link" data-page="${i}">${i}</a></li>`;
            }

            if (e < last) {
                if (e < last - 1) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
                html += `<li class="page-item"><a class="page-link" data-page="${last}">${last}</a></li>`;
            }

            const next = cur === last ? 'disabled' : '';
            html +=
                `<li class="page-item ${next}"><a class="page-link" data-page="${cur+1}"><i class="fas fa-chevron-right"></i></a></li>`;

            el.innerHTML = html;

            el.querySelectorAll('.page-link').forEach(a => {
                a.addEventListener('click', ev => {
                    ev.preventDefault();
                    const li = a.parentElement;
                    if (li.classList.contains('disabled') || li.classList.contains('active')) return;
                    fetchData(parseInt(a.dataset.page));
                    document.getElementById('tblContainer').scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            });
        }

        // ── MODAL: ABRIR ──────────────────────────────────────────────
        function openModal(id, nombre, lic, cursoLig, fed, cursoPes) {
            document.getElementById('emp_id').value = id;
            document.getElementById('modalTitle').textContent = nombre;
            document.getElementById('modalSubtitle').textContent = 'Modifica las fechas de vigencia';

            // Guardar originales
            originals = {
                f_lic: lic,
                f_curso_lig: cursoLig,
                f_fed: fed,
                f_curso_pes: cursoPes
            };

            // Establecer fechas en flatpickr
            const setFP = (id, val) => {
                const fp = document.getElementById(id)?._flatpickr;
                if (fp) fp.setDate(val || null, false);
            };
            setFP('f_lic', lic);
            setFP('f_curso_lig', cursoLig);
            setFP('f_fed', fed);
            setFP('f_curso_pes', cursoPes);

            // Limpiar estado visual
            resetChangeUI();

            document.getElementById('modalLic').classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        // ── MODAL: CERRAR ─────────────────────────────────────────────
        function bindCloseModal() {
            const close = () => {
                if (hasChanges()) {
                    Swal.fire({
                        title: 'Tienes cambios sin guardar',
                        text: '¿Seguro que deseas cerrar? Se perderán los cambios.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, descartar',
                        cancelButtonText: 'Seguir editando',
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#1d4ed8',
                    }).then(r => {
                        if (r.isConfirmed) forceClose();
                    });
                } else {
                    forceClose();
                }
            };

            document.getElementById('btnCloseModal').addEventListener('click', close);
            document.getElementById('btnCancel').addEventListener('click', close);

            // Cerrar al hacer clic fuera
            document.getElementById('modalLic').addEventListener('click', e => {
                if (e.target === document.getElementById('modalLic')) close();
            });

            // ESC
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape' && document.getElementById('modalLic').classList.contains('open')) close();
            });
        }

        function forceClose() {
            document.getElementById('modalLic').classList.remove('open');
            document.body.style.overflow = 'auto';
            document.getElementById('formLic').reset();
            resetChangeUI();
        }

        // ── DETECCIÓN DE CAMBIOS ──────────────────────────────────────
        function getFieldVal(id) {
            return document.getElementById(id)?._flatpickr?.input?.value || '';
        }

        // Normalizar: flatpickr altInput muestra dd/mm/yyyy, pero el hidden value es yyyy-mm-dd
        function getRawVal(id) {
            const fp = document.getElementById(id)?._flatpickr;
            return fp ? (fp.selectedDates[0] ? fp.formatDate(fp.selectedDates[0], 'Y-m-d') : '') : '';
        }

        function fmtDisplay(raw) {
            if (!raw) return 'Sin fecha';
            const [y, m, d] = raw.split('-');
            return `${d}/${m}/${y}`;
        }

        function hasChanges() {
            return Object.keys(originals).some(k => getRawVal(k) !== originals[k]);
        }

        function detectChanges() {
            const fields = Object.keys(originals);
            const changed = fields.filter(k => getRawVal(k) !== originals[k]);
            const summary = document.getElementById('changesSummary');
            const detail = document.getElementById('changesDetail');
            const btn = document.getElementById('btnSave');
            const badge = document.getElementById('saveBadge');

            // Resaltar campos
            fields.forEach(k => {
                const inp = document.getElementById(k);
                const tag = document.getElementById('tag_' + k);
                const isChanged = getRawVal(k) !== originals[k];
                inp?.classList.toggle('changed', isChanged);
                tag?.classList.toggle('visible', isChanged);
            });

            if (!changed.length) {
                summary.classList.remove('visible');
                btn.disabled = true;
                badge.style.display = 'none';
                return;
            }

            // Mostrar resumen
            detail.innerHTML = changed.map(k => `
        <div class="change-item">
            <i class="fas fa-arrow-right" style="color:var(--gray-400);font-size:.7rem;"></i>
            <strong>${LABELS[k]}:</strong>
            <span class="old-val">${fmtDisplay(originals[k])}</span>
            <i class="fas fa-long-arrow-alt-right" style="font-size:.7rem;color:var(--gray-400)"></i>
            <span class="new-val">${fmtDisplay(getRawVal(k))}</span>
        </div>
    `).join('');

            summary.classList.add('visible');
            btn.disabled = false;
            badge.textContent = changed.length;
            badge.style.display = 'inline';
        }

        function resetChangeUI() {
            ['f_lic', 'f_curso_lig', 'f_fed', 'f_curso_pes'].forEach(k => {
                document.getElementById(k)?.classList.remove('changed');
                const tag = document.getElementById('tag_' + k);
                if (tag) tag.classList.remove('visible');
            });
            document.getElementById('changesSummary').classList.remove('visible');
            document.getElementById('btnSave').disabled = true;
            document.getElementById('saveBadge').style.display = 'none';
        }

        // Detectar cambios en tiempo real al cambiar fecha en flatpickr
        // Necesitamos re-inicializar flatpickr con onChange
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.flatpickr-date').forEach(el => {
                const fp = el._flatpickr;
                if (fp) {
                    fp.config.onChange.push(() => detectChanges());
                }
            });
        });

        // ── GUARDAR ───────────────────────────────────────────────────
        async function saveAJAX(e) {
            e.preventDefault();

            if (!hasChanges()) return;

            const id = document.getElementById('emp_id').value;
            const token = document.querySelector('input[name="_token"]').value;
            const url = `/qhse/management/empleados/${id}/actualizar-licencias`;

            const body = {
                driver_license_expires_at: getRawVal('f_lic') || null,
                light_defensive_course_expires_at: getRawVal('f_curso_lig') || null,
                federal_license_expires_at: getRawVal('f_fed') || null,
                heavy_defensive_course_expires_at: getRawVal('f_curso_pes') || null,
            };

            Swal.fire({
                title: 'Guardando...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(body),
                });

                if (!res.ok) throw new Error(`Error HTTP ${res.status}`);
                const result = await res.json();

                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Guardado!',
                        text: 'Las credenciales se actualizaron correctamente.',
                        confirmButtonColor: '#1d4ed8',
                        timer: 2800,
                        timerProgressBar: true,
                    }).then(() => {
                        forceClose();
                        fetchData(currentPage);
                    });
                } else {
                    throw new Error(result.message || 'Error al guardar.');
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.message,
                    confirmButtonColor: '#dc2626'
                });
            }
        }
    </script>
@endpush
