@extends('modules.qhse.management.index')

@push('css')
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue-main: #1d4ed8; --blue-light: #eff6ff; --blue-mid: #bfdbfe;
            --green: #16a34a; --orange: #ea580c; --yellow: #d97706; --red: #dc2626;
            --gray-50: #f8fafc; --gray-100: #f1f5f9; --gray-200: #e2e8f0; --gray-300: #cbd5e1;
            --gray-400: #94a3b8; --gray-500: #64748b; --gray-600: #475569; --gray-800: #1e293b;
            --shadow-sm: 0 1px 3px rgba(15, 23, 42, .06);
            --shadow-md: 0 4px 16px rgba(15, 23, 42, .08);
            --shadow-lg: 0 12px 40px rgba(15, 23, 42, .14);
            --radius: 14px;
        }

        .lic-page, .lic-page * { box-sizing: border-box; }
        .lic-page { font-family: 'DM Sans', sans-serif; margin-top: 20px; margin-bottom: 30px; }

        /* ═════════════ TARJETAS (fuera de lic-wrap) ═════════════
           Clases con prefijo "lc-" para que no choquen con estilos del tema. */
        .lic-page { container-type: inline-size; }

        .lic-page .lc-stats {
            display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; margin: 0 0 18px;
        }
        @container (max-width: 1000px) { .lic-page .lc-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @container (max-width: 520px)  { .lic-page .lc-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }

        .lic-page .lc-card {
            --c: var(--blue-main); --c-soft: var(--blue-light);
            position: relative; overflow: hidden; cursor: pointer; user-select: none;
            display: flex; flex-direction: column; align-items: stretch; justify-content: flex-start;
            min-width: 0; text-align: left; text-transform: none; letter-spacing: normal;
            background: #fff; border: 1px solid var(--gray-200); border-radius: var(--radius);
            padding: 14px 14px 12px; box-shadow: var(--shadow-sm);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }
        .lic-page .lc-card::before {
            content: ''; position: absolute; left: 0; right: 0; top: 0; height: 3px; background: var(--c);
            transform: scaleX(0); transform-origin: left; transition: transform .3s ease;
        }
        .lic-page .lc-card::after {
            content: ''; position: absolute; right: -34px; top: -34px; width: 96px; height: 96px;
            border-radius: 50%; background: var(--c-soft); opacity: .6; transition: transform .35s ease; pointer-events: none;
        }
        .lic-page .lc-card > * { position: relative; z-index: 1; }
        .lic-page .lc-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); border-color: var(--gray-300); }
        .lic-page .lc-card:hover::after { transform: scale(1.15); }
        .lic-page .lc-card:focus-visible { outline: none; box-shadow: 0 0 0 3px var(--c-soft), var(--shadow-md); }
        .lic-page .lc-card.active { border-color: var(--c); box-shadow: 0 0 0 3px var(--c-soft), var(--shadow-md); }
        .lic-page .lc-card.active::before { transform: scaleX(1); }

        .lic-page .lc-top { display: flex; align-items: center; gap: 10px; margin: 0 0 12px; min-width: 0; }
        .lic-page .lc-icon {
            width: 32px; height: 32px; border-radius: 9px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            background: var(--c-soft); color: var(--c); font-size: .85rem;
        }
        .lic-page .lc-label {
            flex: 1; min-width: 0; margin: 0; font-size: .8rem; font-weight: 600; line-height: 1.2;
            color: var(--gray-600); text-transform: none; letter-spacing: 0;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .lic-page .lc-check {
            width: 18px; height: 18px; border-radius: 50%; flex-shrink: 0; background: var(--c); color: #fff;
            display: flex; align-items: center; justify-content: center; font-size: .55rem;
            opacity: 0; transform: scale(.5); transition: all .25s cubic-bezier(.34, 1.56, .64, 1);
        }
        .lic-page .lc-card.active .lc-check { opacity: 1; transform: scale(1); }

        .lic-page .lc-value {
            display: block; margin: 0; font-size: 1.65rem; font-weight: 700; line-height: 1;
            color: var(--gray-800); font-variant-numeric: tabular-nums; letter-spacing: -.5px; transition: opacity .2s;
        }
        .lic-page .lc-bar { display: block; height: 4px; border-radius: 99px; background: var(--gray-100); margin: 12px 0 8px; overflow: hidden; }
        .lic-page .lc-bar span { display: block; height: 100%; width: 0; background: var(--c); border-radius: 99px; transition: width .6s ease; }
        .lic-page .lc-foot {
            display: flex; justify-content: space-between; align-items: center; gap: 6px; margin: 0;
            font-size: .7rem; line-height: 1.2; color: var(--gray-400);
        }
        .lic-page .lc-foot span { min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .lic-page .lc-foot b { flex-shrink: 0; color: var(--c); font-weight: 700; }
        .lic-page .lc-stats.loading .lc-value { opacity: .35; }

        /* ═════════════ CONTENEDOR TABLA ═════════════ */
        .lic-wrap { background: #fff; border-radius: var(--radius); box-shadow: var(--shadow-md); padding: 24px; }

        .lic-header {
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;
            padding-bottom: 18px; border-bottom: 1px solid var(--gray-200); margin-bottom: 18px;
        }
        .lic-header-title { display: flex; align-items: center; gap: 12px; }
        .lic-header-icon {
            width: 44px; height: 44px; border-radius: 11px; background: var(--blue-light);
            display: flex; align-items: center; justify-content: center; color: var(--blue-main); font-size: 1.15rem;
        }
        .lic-header-title h2 { margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--gray-800); }
        .lic-header-title p { margin: 2px 0 0; font-size: .78rem; color: var(--gray-400); }
        .lic-controls { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

        .ctrl-select, .ctrl-search {
            height: 40px; border: 1px solid var(--gray-200); border-radius: 9px; background: var(--gray-50);
            font-family: inherit; font-size: .875rem; color: var(--gray-800); padding: 0 14px; outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s, width .3s;
        }
        .ctrl-select:focus, .ctrl-search:focus { border-color: var(--blue-main); background: #fff; box-shadow: 0 0 0 3px rgba(29, 78, 216, .12); }
        .ctrl-search-wrap { position: relative; }
        .ctrl-search-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--gray-400); font-size: .85rem; pointer-events: none; }
        .ctrl-search { padding-left: 36px; width: 240px; }
        .ctrl-search:focus { width: 290px; }

        /* Chip de filtro activo */
        .filter-bar { display: none; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 14px; font-size: .8rem; color: var(--gray-500); }
        .filter-bar.visible { display: flex; }
        .lc-chip {
            display: inline-flex; align-items: center; gap: 6px; padding: 5px 6px 5px 10px; border-radius: 99px;
            background: var(--gray-100); color: var(--gray-800); font-weight: 600; font-size: .76rem;
        }
        .lc-chip button {
            border: 0; background: rgba(15, 23, 42, .08); color: var(--gray-600); width: 18px; height: 18px; padding: 0;
            border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: .6rem;
        }
        .lc-chip button:hover { background: var(--red); color: #fff; }
        .lc-chip-dot { width: 7px; height: 7px; border-radius: 50%; }
        #filterChips { display: inline-flex; gap: 6px; flex-wrap: wrap; }

        /* Columna de área */
        .area-pill {
            display: inline-flex; align-items: center; gap: 6px; max-width: 180px; padding: 4px 10px;
            border-radius: 7px; background: var(--gray-50); border: 1px solid var(--gray-200);
            color: var(--gray-600); font-size: .76rem; font-weight: 500;
        }
        .area-pill i { color: var(--gray-400); font-size: .7rem; flex-shrink: 0; }
        .area-pill span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .area-pill.none { color: var(--gray-400); font-style: italic; }
        .link-clear { border: 0; background: none; color: var(--blue-main); font-weight: 600; font-size: .78rem; cursor: pointer; padding: 0; }
        .link-clear:hover { text-decoration: underline; }

        /* ═════════════ TABLA ═════════════ */
        .tbl-scroll {
            overflow: auto; max-height: 580px; border-radius: 11px; border: 1px solid var(--gray-200);
            scrollbar-width: thin; scrollbar-color: var(--gray-300) var(--gray-100);
        }
        .tbl-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .tbl-scroll::-webkit-scrollbar-track { background: var(--gray-100); }
        .tbl-scroll::-webkit-scrollbar-thumb { background: var(--gray-300); border-radius: 4px; }

        .tbl-licencias { width: 100%; min-width: 1080px; border-collapse: collapse; background: #fff; }
        .tbl-licencias th.th-left, .tbl-licencias td.td-left { text-align: left; }
        .tbl-licencias thead { position: sticky; top: 0; z-index: 10; }
        .tbl-licencias th {
            background: var(--gray-50); color: var(--gray-400); font-weight: 600; font-size: .7rem;
            text-transform: uppercase; letter-spacing: .6px; text-align: center; padding: 13px 12px;
            border-bottom: 1px solid var(--gray-200); white-space: nowrap; transition: background .2s, color .2s;
        }
        .tbl-licencias th small { font-weight: 400; text-transform: none; letter-spacing: 0; }
        .tbl-licencias th:first-child { text-align: left; padding-left: 20px; }
        .tbl-licencias td {
            padding: 13px 12px; border-bottom: 1px solid var(--gray-100); text-align: center;
            vertical-align: middle; font-size: .875rem; color: var(--gray-800); transition: background .2s;
        }
        .tbl-licencias td:first-child { padding-left: 20px; }
        .tbl-licencias tbody tr { transition: background .15s; }
        .tbl-licencias tbody tr:hover { background: var(--gray-50); }
        .tbl-licencias tbody tr:last-child td { border-bottom: none; }

        /* Indicador lateral con el peor estatus de la fila */
        .tbl-licencias tbody tr[data-worst] td:first-child { box-shadow: inset 3px 0 0 transparent; }
        .tbl-licencias tbody tr[data-worst="vencida"] td:first-child { box-shadow: inset 3px 0 0 var(--red); }
        .tbl-licencias tbody tr[data-worst="critica"] td:first-child { box-shadow: inset 3px 0 0 var(--orange); }
        .tbl-licencias tbody tr[data-worst="proxima"] td:first-child { box-shadow: inset 3px 0 0 #facc15; }
        .tbl-licencias tbody tr[data-worst="vigente"] td:first-child { box-shadow: inset 3px 0 0 #4ade80; }

        /* Columna resaltada cuando se filtra por un tipo de credencial */
        .tbl-licencias th.col-focus { background: var(--blue-light); color: var(--blue-main); }
        .tbl-licencias td.col-focus { background: rgba(239, 246, 255, .6); }
        .tbl-licencias td.col-dim .bdg { opacity: .45; }

        .emp-cell { display: flex; align-items: center; gap: 14px; text-align: left; }
        .emp-avatar {
            width: 40px; height: 40px; border-radius: 10px; background: var(--gray-100);
            display: flex; align-items: center; justify-content: center; color: var(--gray-400);
            font-size: 1.05rem; flex-shrink: 0; overflow: hidden;
        }
        .emp-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .emp-name { font-size: .88rem; font-weight: 600; color: var(--gray-800); margin: 0; line-height: 1.3; }
        .emp-dept { font-size: .75rem; color: var(--gray-400); display: flex; align-items: center; gap: 4px; }

        .bdg { display: inline-flex; align-items: center; gap: 5px; padding: 5px 11px; border-radius: 7px; font-size: .75rem; font-weight: 600; white-space: nowrap; transition: opacity .2s; }
        .bdg-ok { background: #dcfce7; color: #15803d; }
        .bdg-warn { background: #fef9c3; color: #92400e; }
        .bdg-orange { background: #ffedd5; color: #9a3412; }
        .bdg-danger { background: #fee2e2; color: #b91c1c; }
        .bdg-none { background: var(--gray-100); color: var(--gray-400); }

        [data-tip] { position: relative; cursor: help; }
        [data-tip]::before {
            content: attr(data-tip); position: absolute; bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%);
            padding: 4px 10px; background: var(--gray-800); color: #fff; font-size: .68rem; border-radius: 5px;
            white-space: nowrap; opacity: 0; visibility: hidden; transition: opacity .2s; pointer-events: none; z-index: 999;
        }
        [data-tip]:hover::before { opacity: 1; visibility: visible; }

        .btn-upd {
            background: var(--blue-light); color: var(--blue-main); border: 1px solid var(--blue-mid);
            padding: 7px 14px; border-radius: 8px; font-family: inherit; font-size: .82rem; font-weight: 600;
            cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all .2s;
        }
        .btn-upd:hover { background: var(--blue-main); color: #fff; border-color: var(--blue-main); transform: translateY(-1px); box-shadow: 0 3px 10px rgba(29, 78, 216, .25); }

        /* Skeleton */
        .sk { display: inline-block; height: 12px; border-radius: 6px; background: linear-gradient(90deg, var(--gray-100) 25%, var(--gray-200) 50%, var(--gray-100) 75%); background-size: 200% 100%; animation: shimmer 1.2s infinite; }
        .sk-avatar { width: 40px; height: 40px; border-radius: 10px; }
        @keyframes shimmer { to { background-position: -200% 0; } }

        /* Pie: info + paginación */
        .tbl-foot { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; padding-top: 18px; }
        .pag-info { font-size: .8rem; color: var(--gray-400); }
        .pag-info b { color: var(--gray-800); }
        .pag { display: flex; list-style: none; padding: 0; margin: 0; gap: 6px; flex-wrap: wrap; }
        .pag .page-link {
            display: flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 10px;
            border: 1px solid var(--gray-200); border-radius: 9px; color: var(--gray-600); text-decoration: none;
            font-size: .85rem; font-weight: 500; background: #fff; cursor: pointer; transition: all .2s;
        }
        .pag .page-item.active .page-link { background: var(--blue-main); color: #fff; border-color: var(--blue-main); pointer-events: none; }
        .pag .page-item:not(.active):not(.disabled) .page-link:hover { background: var(--blue-light); color: var(--blue-main); border-color: var(--blue-mid); }
        .pag .page-item.disabled .page-link { background: var(--gray-50); color: var(--gray-300); cursor: not-allowed; pointer-events: none; }

        .empty-cell { text-align: center; padding: 50px 20px; color: var(--gray-400); }
        .empty-cell i { font-size: 2.4rem; margin-bottom: 12px; opacity: .4; }
        .empty-cell p { margin: 8px 0 0; font-size: .9rem; }

        /* ═════════════ MODAL ═════════════ */
        .modal-lic { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, .55); backdrop-filter: blur(5px); z-index: 1050; align-items: center; justify-content: center; font-family: 'DM Sans', sans-serif; }
        .modal-lic.open { display: flex; }
        .modal-lic * { box-sizing: border-box; }
        .modal-box { background: #fff; border-radius: 16px; width: 95%; max-width: 680px; max-height: 92vh; overflow-y: auto; padding: 32px; box-shadow: var(--shadow-lg); animation: modalIn .25s cubic-bezier(.34, 1.56, .64, 1); scrollbar-width: thin; }
        @keyframes modalIn { from { opacity: 0; transform: scale(.94) translateY(16px); } to { opacity: 1; transform: none; } }
        .modal-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
        .modal-head-info h3 { margin: 0 0 4px; font-size: 1.1rem; font-weight: 700; color: var(--gray-800); }
        .modal-head-info p { margin: 0; font-size: .8rem; color: var(--gray-400); }
        .modal-close { width: 32px; height: 32px; border-radius: 8px; border: none; background: var(--gray-100); color: var(--gray-600); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .2s; flex-shrink: 0; }
        .modal-close:hover { background: #fee2e2; color: var(--red); }
        .modal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
        @media (max-width: 600px) { .modal-grid { grid-template-columns: 1fr; } }
        .modal-card { border-radius: 12px; padding: 20px; border: 1px solid var(--gray-200); }
        .modal-card.light-v { background: var(--blue-light); border-color: var(--blue-mid); }
        .modal-card.heavy-v { background: #fff7ed; border-color: #fed7aa; }
        .modal-card-title { display: flex; align-items: center; gap: 8px; font-size: .9rem; font-weight: 700; margin: 0 0 18px; }
        .modal-card.light-v .modal-card-title { color: var(--blue-main); }
        .modal-card.heavy-v .modal-card-title { color: #c2410c; }
        .fg { margin-bottom: 16px; }
        .fg:last-child { margin-bottom: 0; }
        .fg label { display: block; font-size: .76rem; font-weight: 600; color: var(--gray-600); margin-bottom: 6px; text-transform: uppercase; letter-spacing: .4px; }
        .fg-input-wrap { position: relative; }
        .fg-input-wrap i { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--gray-400); font-size: .85rem; pointer-events: none; }
        .fg-input-wrap input { width: 100%; height: 40px; padding: 0 36px 0 12px; border: 1.5px solid var(--gray-200); border-radius: 8px; font-family: inherit; font-size: .875rem; color: var(--gray-800); background: #fff; transition: border-color .2s, box-shadow .2s; outline: none; }
        .fg-input-wrap input:focus { border-color: var(--blue-main); box-shadow: 0 0 0 3px rgba(29, 78, 216, .12); }
        .fg-input-wrap input.changed { border-color: #f59e0b; background: #fffbeb; box-shadow: 0 0 0 3px rgba(245, 158, 11, .12); }
        .change-tag { display: none; font-size: .68rem; color: #b45309; font-weight: 600; margin-top: 4px; }
        .change-tag.visible { display: block; }
        .changes-summary { background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 14px 16px; margin-bottom: 16px; display: none; }
        .changes-summary.visible { display: block; }
        .changes-summary-title { font-size: .78rem; font-weight: 700; color: #92400e; margin: 0 0 10px; display: flex; align-items: center; gap: 6px; }
        .change-item { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-size: .78rem; color: #78350f; padding: 4px 0; border-bottom: 1px dashed #fde68a; }
        .change-item:last-child { border-bottom: none; }
        .change-item .old-val { text-decoration: line-through; color: var(--red); opacity: .7; }
        .change-item .new-val { color: var(--green); font-weight: 600; }
        .modal-foot { display: flex; justify-content: flex-end; gap: 10px; padding-top: 20px; border-top: 1px solid var(--gray-100); }
        .btn-cancel { height: 40px; padding: 0 20px; border: 1px solid var(--gray-200); border-radius: 8px; background: #fff; color: var(--gray-600); font-family: inherit; font-size: .875rem; font-weight: 500; cursor: pointer; transition: all .2s; }
        .btn-cancel:hover { background: var(--gray-100); color: var(--gray-800); }
        .btn-save { height: 40px; padding: 0 24px; border: none; border-radius: 8px; background: var(--blue-main); color: #fff; font-family: inherit; font-size: .875rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all .2s; box-shadow: 0 2px 8px rgba(29, 78, 216, .3); }
        .btn-save:hover { background: #1e40af; transform: translateY(-1px); }
        .btn-save:disabled { opacity: .6; cursor: not-allowed; transform: none; }
        .save-badge { background: #fbbf24; color: #78350f; border-radius: 99px; font-size: .65rem; padding: 1px 6px; font-weight: 700; }

        /* Laptops pequeñas: todo un poco más compacto */
        @media (max-width: 1440px) {
            .lic-wrap { padding: 20px; }
            .tbl-licencias th { padding: 12px 10px; font-size: .66rem; }
            .tbl-licencias td { padding: 11px 10px; font-size: .84rem; }
            .bdg { padding: 4px 9px; font-size: .72rem; }
            .emp-avatar { width: 36px; height: 36px; }
            .emp-cell { gap: 10px; }
            .btn-upd { padding: 6px 11px; font-size: .78rem; }
            .ctrl-search { width: 210px; }
            .ctrl-search:focus { width: 250px; }
        }

        @media (max-width: 768px) {
            .lic-wrap { padding: 16px; }
            .lic-controls { flex-direction: column; align-items: stretch; width: 100%; }
            .ctrl-search, .ctrl-search:focus, .ctrl-search-wrap { width: 100%; }
            .modal-box { padding: 20px; }
            .tbl-foot { justify-content: center; }
        }
    </style>
@endpush

@section('content')
    <div class="lic-page">

        {{-- ═════════════ TARJETAS (fuera de lic-wrap) ═════════════ --}}
        <div class="lc-stats" id="licStats">
            @php
                $cards = [
                    ['key' => 'total',        'status' => '',             'label' => 'Total conductores', 'hint' => 'Personal activo',      'icon' => 'fa-users',              'c' => '#1d4ed8', 'soft' => '#eff6ff'],
                    ['key' => 'vigente',      'status' => 'vigente',      'label' => 'Vigentes',          'hint' => 'Más de 60 días',       'icon' => 'fa-check-circle',       'c' => '#16a34a', 'soft' => '#dcfce7'],
                    ['key' => 'proxima',      'status' => 'proxima',      'label' => 'Próximas a vencer', 'hint' => '31 a 60 días',         'icon' => 'fa-exclamation-triangle','c' => '#d97706', 'soft' => '#fef3c7'],
                    ['key' => 'critica',      'status' => 'critica',      'label' => 'Críticas',          'hint' => '30 días o menos',      'icon' => 'fa-exclamation-circle', 'c' => '#ea580c', 'soft' => '#ffedd5'],
                    ['key' => 'vencida',      'status' => 'vencida',      'label' => 'Vencidas',          'hint' => 'Requieren renovación', 'icon' => 'fa-times-circle',       'c' => '#dc2626', 'soft' => '#fee2e2'],
                    ['key' => 'sin_registro', 'status' => 'sin_registro', 'label' => 'Sin registro',      'hint' => 'Sin fechas capturadas','icon' => 'fa-minus-circle',       'c' => '#64748b', 'soft' => '#f1f5f9'],
                ];
            @endphp

            @foreach ($cards as $card)
                <div class="lc-card {{ $card['status'] === '' ? 'active' : '' }}" role="button" tabindex="0"
                    data-status="{{ $card['status'] }}" data-key="{{ $card['key'] }}"
                    aria-pressed="{{ $card['status'] === '' ? 'true' : 'false' }}"
                    title="{{ $card['label'] }} · {{ $card['hint'] }}"
                    style="--c: {{ $card['c'] }}; --c-soft: {{ $card['soft'] }};">
                    <div class="lc-top">
                        <span class="lc-icon"><i class="fas {{ $card['icon'] }}"></i></span>
                        <span class="lc-label">{{ $card['label'] }}</span>
                        <span class="lc-check"><i class="fas fa-check"></i></span>
                    </div>
                    <span class="lc-value" data-count>{{ $payload['stats'][$card['key']] ?? 0 }}</span>
                    <span class="lc-bar"><span></span></span>
                    <div class="lc-foot"><span>{{ $card['hint'] }}</span><b data-pct></b></div>
                </div>
            @endforeach
        </div>

        {{-- ═════════════ TABLA ═════════════ --}}
        <div class="lic-wrap">
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
                                <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ctrl-search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" class="ctrl-search" placeholder="Buscar conductor o área..." autocomplete="off">
                    </div>
                    <select id="typeSelect" class="ctrl-select" title="Tipo de credencial">
                        <option value="">Todas las credenciales</option>
                        <option value="driver_license">Lic. Conductor (Ligera)</option>
                        <option value="light_course">Curso Man. Def. (Ligera)</option>
                        <option value="federal_license">Lic. Federal (Pesada)</option>
                        <option value="heavy_course">Curso Man. Def. (Pesada)</option>
                    </select>
                </div>
            </div>

            <div class="filter-bar" id="filterBar">
                <span>Filtros activos:</span>
                <span id="filterChips"></span>
                <button type="button" class="link-clear" id="btnClearFilters">Limpiar todo</button>
            </div>

            <div class="tbl-scroll" id="tblContainer">
                <table class="tbl-licencias" id="tblMain">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th class="th-left">Área</th>
                            <th data-col="driver_license">Lic. Conductor <small>(Ligera)</small></th>
                            <th data-col="light_course">Curso Man. Def. <small>(Ligera)</small></th>
                            <th data-col="federal_license">Lic. Federal <small>(Pesada)</small></th>
                            <th data-col="heavy_course">Curso Man. Def. <small>(Pesada)</small></th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tblBody"></tbody>
                </table>
            </div>

            <div class="tbl-foot">
                <div class="pag-info" id="pagInfo"></div>
                <ul class="pag" id="pagLinks"></ul>
            </div>
        </div>
    </div>
@endsection

@section('modals')
    <div class="modal-lic" id="modalLic">
        <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
            <div class="modal-head">
                <div class="modal-head-info">
                    <h3 id="modalTitle">Actualizar Credenciales</h3>
                    <p id="modalSubtitle">Modifica las fechas de vigencia</p>
                </div>
                <button type="button" class="modal-close" id="btnCloseModal" title="Cerrar"><i class="fas fa-times"></i></button>
            </div>

            <div class="changes-summary" id="changesSummary">
                <p class="changes-summary-title"><i class="fas fa-exclamation-triangle"></i> Cambios pendientes de guardar</p>
                <div id="changesDetail"></div>
            </div>

            <form id="formLic">
                @csrf
                <input type="hidden" id="emp_id">

                <div class="modal-grid">
                    <div class="modal-card light-v">
                        <p class="modal-card-title"><i class="fas fa-car"></i> Unidades Ligeras</p>
                        <div class="fg">
                            <label for="f_lic">Vigencia Lic. Conductor</label>
                            <div class="fg-input-wrap">
                                <input type="text" id="f_lic" class="flatpickr-date" placeholder="Seleccionar fecha...">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                            <span class="change-tag" id="tag_f_lic">Campo modificado</span>
                        </div>
                        <div class="fg">
                            <label for="f_curso_lig">Vigencia Curso Man. Defensivo</label>
                            <div class="fg-input-wrap">
                                <input type="text" id="f_curso_lig" class="flatpickr-date" placeholder="Seleccionar fecha...">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                            <span class="change-tag" id="tag_f_curso_lig">Campo modificado</span>
                        </div>
                    </div>

                    <div class="modal-card heavy-v">
                        <p class="modal-card-title"><i class="fas fa-truck"></i> Unidades Pesadas</p>
                        <div class="fg">
                            <label for="f_fed">Vigencia Lic. Federal</label>
                            <div class="fg-input-wrap">
                                <input type="text" id="f_fed" class="flatpickr-date" placeholder="Seleccionar fecha...">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                            <span class="change-tag" id="tag_f_fed">Campo modificado</span>
                        </div>
                        <div class="fg">
                            <label for="f_curso_pes">Vigencia Curso Man. Def. Pesada</label>
                            <div class="fg-input-wrap">
                                <input type="text" id="f_curso_pes" class="flatpickr-date" placeholder="Seleccionar fecha...">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                            <span class="change-tag" id="tag_f_curso_pes">Campo modificado</span>
                        </div>
                    </div>
                </div>

                <div class="modal-foot">
                    <button type="button" class="btn-cancel" id="btnCancel">Cancelar</button>
                    <button type="submit" class="btn-save" id="btnSave" disabled>
                        <i class="fas fa-save"></i> Guardar
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
    (() => {
        // ══════════════ CONFIG ══════════════
        const INITIAL    = @json($payload);
        const LIST_URL   = @json(route('management.licenses'));
        const UPDATE_URL = id => `/qhse/management/empleados/${id}/actualizar-licencias`;

        const COLS = ['driver_license', 'light_course', 'federal_license', 'heavy_course'];
        const TYPE_LABELS = {
            driver_license: 'Lic. Conductor', light_course: 'Curso Man. Def. Ligera',
            federal_license: 'Lic. Federal', heavy_course: 'Curso Man. Def. Pesada',
        };
        const STATUS_META = {
            vigente:      { label: 'Vigentes',          color: '#16a34a' },
            proxima:      { label: 'Próximas a vencer', color: '#d97706' },
            critica:      { label: 'Críticas',          color: '#ea580c' },
            vencida:      { label: 'Vencidas',          color: '#dc2626' },
            sin_registro: { label: 'Sin registro',      color: '#64748b' },
        };
        const RANK = { sin_registro: 0, vigente: 1, proxima: 2, critica: 3, vencida: 4 };

        // Campo del modal → llave del registro
        const FIELD_MAP = { f_lic: 'driver_license', f_curso_lig: 'light_course', f_fed: 'federal_license', f_curso_pes: 'heavy_course' };
        const FIELD_LABELS = {
            f_lic: 'Licencia Conductor (Ligera)', f_curso_lig: 'Curso Man. Def. (Ligera)',
            f_fed: 'Licencia Federal (Pesada)',   f_curso_pes: 'Curso Man. Def. (Pesada)',
        };

        // ══════════════ ESTADO ══════════════
        const state = {
            page: INITIAL.pagination.current_page,
            status: '',
            type: '',
        };
        let rowsById = new Map();
        let originals = { f_lic: '', f_curso_lig: '', f_fed: '', f_curso_pes: '' };
        let searchTimer = null;
        let abortCtrl = null;

        const $ = id => document.getElementById(id);
        const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

        // ══════════════ FECHAS / BADGES ══════════════
        function daysUntil(d) {
            const [y, m, day] = d.split('-').map(Number);
            const now = new Date(); now.setHours(0, 0, 0, 0);
            return Math.round((new Date(y, m - 1, day) - now) / 86400000);
        }

        function statusOf(d) {
            if (!d) return 'sin_registro';
            const n = daysUntil(d);
            if (n < 0) return 'vencida';
            if (n <= 30) return 'critica';
            if (n <= 60) return 'proxima';
            return 'vigente';
        }

        function fmtDisplay(raw) {
            if (!raw) return 'Sin fecha';
            const [y, m, d] = raw.split('-');
            return `${d}/${m}/${y}`;
        }

        function badge(d) {
            const st = statusOf(d);
            if (st === 'sin_registro') return '<span class="bdg bdg-none"><i class="fas fa-minus"></i> No registrado</span>';
            const n = daysUntil(d);
            const cfg = {
                vencida: ['bdg-danger', 'fa-times-circle', `Vencida hace ${Math.abs(n)} día${Math.abs(n) === 1 ? '' : 's'}`],
                critica: ['bdg-orange', 'fa-exclamation-circle', n === 0 ? 'Vence hoy' : `Vence en ${n} día${n === 1 ? '' : 's'}`],
                proxima: ['bdg-warn', 'fa-exclamation-triangle', `Vence en ${n} días`],
                vigente: ['bdg-ok', 'fa-check-circle', `Vigente · ${n} días`],
            }[st];
            return `<span class="bdg ${cfg[0]}" data-tip="${cfg[2]}"><i class="fas ${cfg[1]}"></i> ${fmtDisplay(d)}</span>`;
        }

        function worstStatus(row) {
            const cols = state.type ? [state.type] : COLS;
            return cols.map(c => statusOf(row[c])).reduce((a, b) => RANK[b] > RANK[a] ? b : a, 'sin_registro');
        }

        // ══════════════ RENDER: TARJETAS ══════════════
        function animateCount(el, to) {
            const from = parseInt(el.textContent, 10) || 0;
            if (from === to) { el.textContent = to; return; }
            const t0 = performance.now(), dur = 450;
            const step = t => {
                const p = Math.min((t - t0) / dur, 1);
                el.textContent = Math.round(from + (to - from) * (1 - Math.pow(1 - p, 3)));
                if (p < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        }

        function renderStats(stats) {
            const total = stats.total || 0;
            document.querySelectorAll('#licStats .lc-card').forEach(card => {
                const key = card.dataset.key;
                const val = stats[key] ?? 0;
                const pct = key === 'total' ? (total ? 100 : 0) : (total ? Math.round(val / total * 100) : 0);
                animateCount(card.querySelector('[data-count]'), val);
                card.querySelector('.lc-bar span').style.width = pct + '%';
                card.querySelector('[data-pct]').textContent = pct + '%';
            });
        }

        function syncFilterUI() {
            document.querySelectorAll('#licStats .lc-card').forEach(card => {
                const on = card.dataset.status === state.status;
                card.classList.toggle('active', on);
                card.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
            $('typeSelect').value = state.type;

            document.querySelectorAll('#tblMain thead th[data-col]').forEach(th =>
                th.classList.toggle('col-focus', !!state.type && th.dataset.col === state.type));

            const chips = [];
            if (state.status) {
                const m = STATUS_META[state.status];
                chips.push(`<span class="lc-chip"><span class="lc-chip-dot" style="background:${m.color}"></span>${m.label}<button type="button" data-clear="status" title="Quitar"><i class="fas fa-times"></i></button></span>`);
            }
            if (state.type) {
                chips.push(`<span class="lc-chip"><i class="fas fa-id-card" style="color:var(--gray-400)"></i>${TYPE_LABELS[state.type]}<button type="button" data-clear="type" title="Quitar"><i class="fas fa-times"></i></button></span>`);
            }
            $('filterChips').innerHTML = chips.join(' ');
            $('filterBar').classList.toggle('visible', chips.length > 0);
        }

        // ══════════════ RENDER: TABLA ══════════════
        function renderSkeleton() {
            const rows = Math.min(parseInt($('per_page').value, 10) || 5, 8);
            $('tblBody').innerHTML = Array.from({ length: rows }).map(() => `
                <tr>
                    <td><div class="emp-cell"><span class="sk sk-avatar"></span><span class="sk" style="width:160px"></span></div></td>
                    <td class="td-left"><span class="sk" style="width:110px;height:22px"></span></td>
                    ${COLS.map(() => '<td><span class="sk" style="width:96px;height:24px"></span></td>').join('')}
                    <td><span class="sk" style="width:100px;height:32px"></span></td>
                </tr>`).join('');
        }

        function areaPill(area) {
            const none = !area || area === 'Sin área';
            return `<span class="area-pill ${none ? 'none' : ''}" title="${esc(area || 'Sin área')}"><i class="fas fa-sitemap"></i><span>${esc(none ? 'Sin área' : area)}</span></span>`;
        }

        function renderRows(data) {
            rowsById = new Map(data.map(e => [String(e.id), e]));
            const tbody = $('tblBody');

            if (!data.length) {
                const filtered = state.status || state.type || $('searchInput').value.trim();
                tbody.innerHTML = `
                    <tr><td colspan="7"><div class="empty-cell">
                        <i class="fas ${filtered ? 'fa-filter' : 'fa-users'}"></i>
                        <p>${filtered ? 'No hay conductores que coincidan con los filtros.' : 'No hay empleados registrados.'}</p>
                    </div></td></tr>`;
                return;
            }

            tbody.innerHTML = data.map(e => {
                const avatar = e.photo
                    ? `<img src="${esc(e.photo)}" alt="" loading="lazy">`
                    : '<i class="fas fa-user-tie"></i>';
                const cells = COLS.map(c => {
                    const cls = state.type ? (c === state.type ? 'col-focus' : 'col-dim') : '';
                    return `<td class="${cls}">${badge(e[c])}</td>`;
                }).join('');
                return `
                    <tr data-worst="${worstStatus(e)}">
                        <td>
                            <div class="emp-cell">
                                <div class="emp-avatar">${avatar}</div>
                                <p class="emp-name">${esc(e.name)}</p>
                            </div>
                        </td>
                        <td class="td-left">${areaPill(e.area)}</td>
                        ${cells}
                        <td><button type="button" class="btn-upd" data-id="${esc(e.id)}"><i class="fas fa-edit"></i> Actualizar</button></td>
                    </tr>`;
            }).join('');
        }

        function renderPag(p) {
            const el = $('pagLinks');
            $('pagInfo').innerHTML = p.total
                ? `Mostrando <b>${p.from}–${p.to}</b> de <b>${p.total}</b> conductores`
                : '';

            const cur = p.current_page, last = p.last_page;
            if (last <= 1) { el.innerHTML = ''; return; }

            const item = (page, label, cls = '') => `<li class="page-item ${cls}"><a class="page-link" data-page="${page}">${label}</a></li>`;
            const dots = '<li class="page-item disabled"><span class="page-link">…</span></li>';

            let s = Math.max(1, cur - 2), e = Math.min(last, cur + 2);
            if (cur <= 3) e = Math.min(last, 5);
            if (cur >= last - 2) s = Math.max(1, last - 4);

            let html = item(cur - 1, '<i class="fas fa-chevron-left"></i>', cur === 1 ? 'disabled' : '');
            if (s > 1) { html += item(1, 1); if (s > 2) html += dots; }
            for (let i = s; i <= e; i++) html += item(i, i, i === cur ? 'active' : '');
            if (e < last) { if (e < last - 1) html += dots; html += item(last, last); }
            html += item(cur + 1, '<i class="fas fa-chevron-right"></i>', cur === last ? 'disabled' : '');

            el.innerHTML = html;
        }

        function renderAll(payload) {
            state.page = payload.pagination.current_page;
            renderStats(payload.stats);
            renderRows(payload.data);
            renderPag(payload.pagination);
            syncFilterUI();
        }

        // ══════════════ FETCH ══════════════
        async function fetchData(page = 1) {
            abortCtrl?.abort();
            abortCtrl = new AbortController();

            const params = new URLSearchParams({
                page,
                per_page: $('per_page').value,
                search: $('searchInput').value.trim(),
            });
            if (state.status) params.set('status', state.status);
            if (state.type) params.set('type', state.type);

            renderSkeleton();
            syncFilterUI();
            $('licStats').classList.add('loading');

            try {
                const res = await fetch(`${LIST_URL}?${params}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    signal: abortCtrl.signal,
                });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                renderAll(await res.json());
            } catch (err) {
                if (err.name === 'AbortError') return;
                $('tblBody').innerHTML = `
                    <tr><td colspan="7"><div class="empty-cell">
                        <i class="fas fa-exclamation-triangle" style="color:var(--red)"></i>
                        <p>Error de conexión al cargar datos.</p>
                    </div></td></tr>`;
            } finally {
                $('licStats').classList.remove('loading');
            }
        }

        // ══════════════ EVENTOS: FILTROS ══════════════
        function toggleCard(card) {
            const st = card.dataset.status;
            state.status = (st && st === state.status) ? '' : st; // volver a pulsar = quitar filtro
            fetchData(1);
        }

        $('licStats').addEventListener('click', e => {
            const card = e.target.closest('.lc-card');
            if (card) toggleCard(card);
        });

        $('licStats').addEventListener('keydown', e => {
            const card = e.target.closest('.lc-card');
            if (card && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); toggleCard(card); }
        });

        $('typeSelect').addEventListener('change', e => {
            state.type = e.target.value;
            fetchData(1);
        });

        $('filterChips').addEventListener('click', e => {
            const btn = e.target.closest('[data-clear]');
            if (!btn) return;
            state[btn.dataset.clear] = '';
            fetchData(1);
        });

        $('btnClearFilters').addEventListener('click', () => {
            state.status = ''; state.type = '';
            $('searchInput').value = '';
            fetchData(1);
        });

        $('searchInput').addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => fetchData(1), 380);
        });

        $('per_page').addEventListener('change', () => fetchData(1));

        $('pagLinks').addEventListener('click', e => {
            const a = e.target.closest('.page-link[data-page]');
            if (!a) return;
            const li = a.parentElement;
            if (li.classList.contains('disabled') || li.classList.contains('active')) return;
            fetchData(parseInt(a.dataset.page, 10));
            $('tblContainer').scrollTo({ top: 0, behavior: 'smooth' });
        });

        $('tblBody').addEventListener('click', e => {
            const btn = e.target.closest('.btn-upd');
            if (btn) openModal(rowsById.get(btn.dataset.id));
        });

        // ══════════════ MODAL ══════════════
        function getRawVal(id) {
            const fp = $(id)?._flatpickr;
            return fp && fp.selectedDates[0] ? fp.formatDate(fp.selectedDates[0], 'Y-m-d') : '';
        }

        const changedFields = () => Object.keys(originals).filter(k => getRawVal(k) !== originals[k]);
        const hasChanges = () => changedFields().length > 0;

        function openModal(emp) {
            if (!emp) return;
            $('emp_id').value = emp.id;
            $('modalTitle').textContent = emp.name;
            $('modalSubtitle').textContent = `${emp.area || 'Sin área'} · Modifica las fechas de vigencia`;

            originals = {};
            Object.entries(FIELD_MAP).forEach(([field, key]) => {
                originals[field] = emp[key] || '';
                $(field)._flatpickr?.setDate(emp[key] || null, false);
            });

            resetChangeUI();
            $('modalLic').classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function forceClose() {
            $('modalLic').classList.remove('open');
            document.body.style.overflow = '';
            Object.keys(FIELD_MAP).forEach(f => $(f)._flatpickr?.clear(false));
            resetChangeUI();
        }

        function requestClose() {
            if (!hasChanges()) return forceClose();
            Swal.fire({
                title: 'Tienes cambios sin guardar',
                text: '¿Seguro que deseas cerrar? Se perderán los cambios.',
                icon: 'warning', showCancelButton: true,
                confirmButtonText: 'Sí, descartar', cancelButtonText: 'Seguir editando',
                confirmButtonColor: '#dc2626', cancelButtonColor: '#1d4ed8',
            }).then(r => { if (r.isConfirmed) forceClose(); });
        }

        function detectChanges() {
            const changed = changedFields();

            Object.keys(originals).forEach(k => {
                const isChanged = changed.includes(k);
                $(k)._flatpickr?.altInput?.classList.toggle('changed', isChanged);
                $('tag_' + k)?.classList.toggle('visible', isChanged);
            });

            $('btnSave').disabled = !changed.length;
            $('saveBadge').style.display = changed.length ? 'inline' : 'none';
            $('saveBadge').textContent = changed.length;
            $('changesSummary').classList.toggle('visible', changed.length > 0);

            $('changesDetail').innerHTML = changed.map(k => `
                <div class="change-item">
                    <i class="fas fa-arrow-right" style="color:var(--gray-400);font-size:.7rem;"></i>
                    <strong>${FIELD_LABELS[k]}:</strong>
                    <span class="old-val">${fmtDisplay(originals[k])}</span>
                    <i class="fas fa-long-arrow-alt-right" style="font-size:.7rem;color:var(--gray-400)"></i>
                    <span class="new-val">${fmtDisplay(getRawVal(k))}</span>
                </div>`).join('');
        }

        function resetChangeUI() {
            Object.keys(FIELD_MAP).forEach(k => {
                $(k)._flatpickr?.altInput?.classList.remove('changed');
                $('tag_' + k)?.classList.remove('visible');
            });
            $('changesSummary').classList.remove('visible');
            $('btnSave').disabled = true;
            $('saveBadge').style.display = 'none';
        }

        $('btnCloseModal').addEventListener('click', requestClose);
        $('btnCancel').addEventListener('click', requestClose);
        $('modalLic').addEventListener('click', e => { if (e.target === $('modalLic')) requestClose(); });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && $('modalLic').classList.contains('open') && !Swal.isVisible()) requestClose();
        });

        // ══════════════ GUARDAR ══════════════
        $('formLic').addEventListener('submit', async e => {
            e.preventDefault();
            if (!hasChanges()) return;

            const id = $('emp_id').value;
            const token = document.querySelector('#formLic input[name="_token"]').value;
            const body = {
                driver_license_expires_at:         getRawVal('f_lic') || null,
                light_defensive_course_expires_at: getRawVal('f_curso_lig') || null,
                federal_license_expires_at:        getRawVal('f_fed') || null,
                heavy_defensive_course_expires_at: getRawVal('f_curso_pes') || null,
            };

            Swal.fire({ title: 'Guardando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            try {
                const res = await fetch(UPDATE_URL(id), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    body: JSON.stringify(body),
                });
                const result = await res.json().catch(() => ({}));

                if (res.status === 422) {
                    const first = Object.values(result.errors || {})[0];
                    throw new Error(first ? first[0] : 'Datos inválidos.');
                }
                if (!res.ok || !result.success) throw new Error(result.message || `Error HTTP ${res.status}`);

                await Swal.fire({
                    icon: 'success', title: '¡Guardado!', text: 'Las credenciales se actualizaron correctamente.',
                    confirmButtonColor: '#1d4ed8', timer: 2200, timerProgressBar: true,
                });
                forceClose();
                fetchData(state.page);
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Error', text: err.message, confirmButtonColor: '#dc2626' });
            }
        });

        // ══════════════ INIT ══════════════
        document.addEventListener('DOMContentLoaded', () => {
            flatpickr('.flatpickr-date', {
                dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y',
                locale: 'es', allowInput: true, disableMobile: true,
                onChange: detectChanges, onClose: detectChanges,
            });
            renderAll(INITIAL);
        });
    })();
    </script>
@endpush
