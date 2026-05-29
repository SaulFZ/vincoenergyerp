@extends('modules.systems.tickets.index')

@section('content')
    <style>
        /* ─── FUENTE MONO ─────────────────────────────────────────────────── */
        @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@500;600;700&display=swap');

        /* ─── TOKENS DE COLOR POR ESTADO ─────────────────────────────────── */
        :root {
            --s-new-text: #0c2d5e;
            --s-new-bg: #d4e4f7;
            --s-new-border: #2e6db4;
            --s-new-dot: #1a5fb4;
            --s-open-text: #0b4a52;
            --s-open-bg: #c8edf3;
            --s-open-border: #1a8fa3;
            --s-open-dot: #0e7a8c;
            --s-wait-text: #7a2c00;
            --s-wait-bg: #fddbc7;
            --s-wait-border: #c15a00;
            --s-wait-dot: #c15a00;
            --s-pend-text: #6b3b00;
            --s-pend-bg: #fde8ab;
            --s-pend-border: #c47d00;
            --s-pend-dot: #c47d00;
            --s-done-text: #0d4a1e;
            --s-done-bg: #bbf0cc;
            --s-done-border: #1a8c38;
            --s-done-dot: #15722d;
            --s-cancel-text: #6b0c0c;
            --s-cancel-bg: #fcc8c8;
            --s-cancel-border: #b91c1c;
            --s-cancel-dot: #9b1111;
        }

        /* ─── BASE ────────────────────────────────────────────────────────── */
        .content.active {
            background: #f0f4f8;
        }

        /* ─── ESTADÍSTICAS ────────────────────────────────────────────────── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 14px;
            padding: 4px 0 16px;
        }

        .stat-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 18px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            min-height: 90px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            transition: transform .3s ease, box-shadow .3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            border-radius: 0 0 16px 16px;
            transition: opacity .3s;
            opacity: 0.85;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.08);
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-card.c-main {
            background: linear-gradient(135deg, var(--color-primary) 0%, #1a2a33 100%);
            border: none;
        }

        .stat-card.c-main::before {
            display: none;
        }

        .stat-card.c-wait::before {
            background: var(--s-wait-border);
        }

        .stat-card.c-pend::before {
            background: var(--s-pend-border);
        }

        .stat-card.c-done::before {
            background: var(--s-done-border);
        }

        .stat-card.c-cancel::before {
            background: var(--s-cancel-border);
        }

        .stat-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .stat-label {
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #64748b;
        }

        .c-main .stat-label {
            color: rgba(255, 255, 255, .7);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1;
            font-family: 'IBM Plex Mono', monospace;
        }

        .c-main .stat-value {
            color: #fff;
        }

        .stat-sub {
            font-size: .7rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .c-main .stat-sub {
            color: rgba(255, 255, 255, .5);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .c-main .stat-icon {
            background: rgba(255, 255, 255, .1);
            color: #fff;
        }

        .c-wait .stat-icon {
            background: var(--s-wait-bg);
            color: var(--s-wait-text);
        }

        .c-pend .stat-icon {
            background: var(--s-pend-bg);
            color: var(--s-pend-text);
        }

        .c-done .stat-icon {
            background: var(--s-done-bg);
            color: var(--s-done-text);
        }

        .c-cancel .stat-icon {
            background: var(--s-cancel-bg);
            color: var(--s-cancel-text);
        }

        /* ─── TABLA PRINCIPAL ─────────────────────────────────────────────── */
        .data-section {
            background: #fff;
            padding: 20px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            width: 100%;
        }

        .table-controls {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }

        .search-wrap {
            flex-grow: 1;
            position: relative;
            min-width: 220px;
        }

        .search-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: .85rem;
        }

        .search-wrap input {
            width: 100%;
            padding: 10px 14px 10px 38px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #f8fafc;
            font-size: .85rem;
            transition: all .2s;
        }

        .search-wrap input:focus {
            border-color: var(--color-primary);
            background: #fff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 73, 85, .1);
        }

        select.f-sel {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 14px;
            outline: none;
            background: #f8fafc;
            color: #1e293b;
            font-size: .85rem;
            font-weight: 500;
            transition: all .2s;
            cursor: pointer;
        }

        select.f-sel:focus,
        select.f-sel:hover {
            border-color: var(--color-primary);
            background: #fff;
        }

        .main-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .main-table th {
            background: #f1f5f9;
            color: #475569;
            padding: 12px 16px;
            text-align: left;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            border-bottom: 2px solid #e2e8f0;
        }

        .main-table th:first-child {
            border-top-left-radius: 10px;
        }

        .main-table th:last-child {
            border-top-right-radius: 10px;
        }

        .main-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: .85rem;
            vertical-align: middle;
            transition: background .2s;
        }

        .main-table tr:hover td {
            background: #f8fafc;
        }

        .main-table tr:last-child td {
            border-bottom: none;
        }

        .tk-num,
        .tk-folio {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .85rem;
        }

        .tk-num {
            font-weight: 600;
            color: #0f172a;
        }

        .tk-folio {
            font-weight: 700;
            color: var(--color-primary);
        }

        @keyframes rowIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .main-table tbody tr {
            animation: rowIn .4s cubic-bezier(.16, 1, .3, 1) forwards;
            opacity: 0;
        }

        /* ─── STATUS TAGS ─────────────────────────────────────────────────── */
        .stag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            white-space: nowrap;
        }

        .stag::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .stag.nuevo {
            background: var(--s-new-bg);
            color: var(--s-new-text);
            border: 1px solid var(--s-new-border);
        }

        .stag.nuevo::before {
            background: var(--s-new-dot);
        }

        .stag.abierto {
            background: var(--s-open-bg);
            color: var(--s-open-text);
            border: 1px solid var(--s-open-border);
        }

        .stag.abierto::before {
            background: var(--s-open-dot);
        }

        .stag.en-espera {
            background: var(--s-wait-bg);
            color: var(--s-wait-text);
            border: 1px solid var(--s-wait-border);
        }

        .stag.en-espera::before {
            background: var(--s-wait-dot);
        }

        .stag.por-concluir {
            background: var(--s-pend-bg);
            color: var(--s-pend-text);
            border: 1px solid var(--s-pend-border);
        }

        .stag.por-concluir::before {
            background: var(--s-pend-dot);
        }

        .stag.realizado {
            background: var(--s-done-bg);
            color: var(--s-done-text);
            border: 1px solid var(--s-done-border);
        }

        .stag.realizado::before {
            background: var(--s-done-dot);
        }

        .stag.cancelado {
            background: var(--s-cancel-bg);
            color: var(--s-cancel-text);
            border: 1px solid var(--s-cancel-border);
        }

        .stag.cancelado::before {
            background: var(--s-cancel-dot);
        }

        .ptag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 5px;
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .ptag.alta {
            background: var(--s-cancel-bg);
            color: var(--s-cancel-text);
            border: 1px solid var(--s-cancel-border);
        }

        .ptag.media {
            background: var(--s-pend-bg);
            color: var(--s-pend-text);
            border: 1px solid var(--s-pend-border);
        }

        .ptag.baja {
            background: var(--s-done-bg);
            color: var(--s-done-text);
            border: 1px solid var(--s-done-border);
        }

        .ptag.sin-clasificar {
            background: #f8fafc;
            color: #64748b;
            border: 1px solid #cbd5e1;
        }

        /* ─── CAMPOS DE FORMULARIO COMUNES ────────────────────────────────── */
        .form-group {
            margin-bottom: 14px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .form-divider {
            height: 1px;
            background: #e2e8f0;
            margin: 16px 0;
            border: none;
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #475569;
            margin-bottom: 8px;
        }

        .field-wrap {
            position: relative;
            display: flex;
            align-items: center;
            transition: all .2s;
        }

        .field-icon {
            position: absolute;
            left: 14px;
            font-size: .8rem;
            pointer-events: none;
            color: #94a3b8;
            transition: color .2s;
        }

        .ctrl {
            width: 100%;
            padding: 10px 14px 10px 36px;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: .85rem;
            color: #1e293b;
            transition: all .2s;
        }

        .ctrl.no-icon {
            padding-left: 14px;
        }

        .ctrl:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(52, 73, 85, .1);
        }

        textarea.ctrl {
            resize: none;
            overflow: hidden;
            min-height: 42px;
            line-height: 1.5;
        }

        /* ─── COLORES DINÁMICOS DE ESTADO Y PRIORIDAD (FUERTES Y SÓLIDOS) ─── */
        /* Agregamos opacity: 1 !important para evitar que el navegador los vuelva pálidos al estar disabled */
        .ctrl.bg-s-nuevo {
            background: var(--s-new-bg) !important;
            color: var(--s-new-text) !important;
            border-color: var(--s-new-border) !important;
            font-weight: 700;
            -webkit-text-fill-color: var(--s-new-text) !important;
            opacity: 1 !important;
        }

        .ctrl.bg-s-abierto {
            background: var(--s-open-bg) !important;
            color: var(--s-open-text) !important;
            border-color: var(--s-open-border) !important;
            font-weight: 700;
            -webkit-text-fill-color: var(--s-open-text) !important;
            opacity: 1 !important;
        }

        .ctrl.bg-s-en-espera {
            background: var(--s-wait-bg) !important;
            color: var(--s-wait-text) !important;
            border-color: var(--s-wait-border) !important;
            font-weight: 700;
            -webkit-text-fill-color: var(--s-wait-text) !important;
            opacity: 1 !important;
        }

        .ctrl.bg-s-por-concluir {
            background: var(--s-pend-bg) !important;
            color: var(--s-pend-text) !important;
            border-color: var(--s-pend-border) !important;
            font-weight: 700;
            -webkit-text-fill-color: var(--s-pend-text) !important;
            opacity: 1 !important;
        }

        .ctrl.bg-s-realizado {
            background: var(--s-done-bg) !important;
            color: var(--s-done-text) !important;
            border-color: var(--s-done-border) !important;
            font-weight: 700;
            -webkit-text-fill-color: var(--s-done-text) !important;
            opacity: 1 !important;
        }

        .ctrl.bg-s-cancelado {
            background: var(--s-cancel-bg) !important;
            color: var(--s-cancel-text) !important;
            border-color: var(--s-cancel-border) !important;
            font-weight: 700;
            -webkit-text-fill-color: var(--s-cancel-text) !important;
            opacity: 1 !important;
        }

        .ctrl.bg-p-alta {
            background: var(--s-cancel-bg) !important;
            color: var(--s-cancel-text) !important;
            border-color: var(--s-cancel-border) !important;
            font-weight: 700;
            -webkit-text-fill-color: var(--s-cancel-text) !important;
            opacity: 1 !important;
        }

        .ctrl.bg-p-media {
            background: var(--s-pend-bg) !important;
            color: var(--s-pend-text) !important;
            border-color: var(--s-pend-border) !important;
            font-weight: 700;
            -webkit-text-fill-color: var(--s-pend-text) !important;
            opacity: 1 !important;
        }

        .ctrl.bg-p-baja {
            background: var(--s-done-bg) !important;
            color: var(--s-done-text) !important;
            border-color: var(--s-done-border) !important;
            font-weight: 700;
            -webkit-text-fill-color: var(--s-done-text) !important;
            opacity: 1 !important;
        }

        .ctrl.bg-p-sin-clasificar {
            background: #f8fafc !important;
            color: #64748b !important;
            border-color: #cbd5e1 !important;
            font-weight: 700;
            -webkit-text-fill-color: #64748b !important;
            opacity: 1 !important;
        }

        /* Comportamiento base para deshabilitados */
        .ctrl[readonly],
        .ctrl:disabled:not([class*="bg-"]) {
            background: #f8fafc;
            cursor: default;
            border-color: #e4eef5;
            color: #475569;
            opacity: 1;
            -webkit-text-fill-color: #475569;
        }

        /* ─── MODALES FLOTANTES REDISEÑADOS ───────────────────────────────── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .75);
            backdrop-filter: blur(5px);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all .3s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 20px;
        }

        .modal-overlay.open {
            opacity: 1;
            visibility: visible;
        }

        .modal-box {
            background: #fff;
            border-radius: 18px;
            width: 100%;
            max-width: 580px;
            max-height: 92vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            transform: scale(.95) translateY(20px);
            transition: transform .4s cubic-bezier(.16, 1, .3, 1);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-overlay.open .modal-box {
            transform: scale(1) translateY(0);
        }

        #modal-ticket .modal-box {
            max-width: 950px;
        }

        .modal-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 26px;
            background: linear-gradient(135deg, var(--color-primary) 0%, #1e293b 100%);
            color: #fff;
            flex-shrink: 0;
        }

        .modal-head h3 {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-head .sub {
            font-size: .8rem;
            font-weight: 500;
            color: #cbd5e1;
            margin-top: 6px;
            display: flex;
            gap: 14px;
            align-items: center;
        }

        .modal-body {
            padding: 26px;
            overflow-y: auto;
            background: #fff;
            flex-grow: 1;
            position: relative;
        }

        .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
            border: 2px solid #fff;
        }

        .modal-body::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .modal-foot {
            background: #f8fafc;
            padding: 18px 26px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
            border-radius: 0 0 18px 18px;
        }

        /* ─── INTELIGENCIA DEL MODAL DE TICKET (MODO VER VS MODO ATENDER) ─── */
        /* Si el modal tiene la clase 'mode-view', ocultamos la botonera desde CSS instantáneamente */
        .modal-overlay.mode-view .modal-foot {
            display: none !important;
        }

        .modal-overlay.mode-view .modal-body {
            border-radius: 0 0 18px 18px;
            padding-bottom: 30px;
        }

        /* En modo ver, estilizamos los inputs de texto normales para que parezcan datos de lectura */
        /* EXCLUIMOS a los que tienen la clase bg- para no afectar los colores de Estado y Prioridad */
        .modal-overlay.mode-view .ts-left .ctrl:not([class*="bg-"]) {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            color: #334155;
            box-shadow: none;
            cursor: default;
        }

        .modal-overlay.mode-view .ts-left .field-icon:not(.bg-icon) {
            color: #94a3b8;
        }

        /* Mantenemos los colores dinámicos de los selectores, pero les quitamos la flecha nativa para que se vean fijos */
        .modal-overlay.mode-view .ts-left select.ctrl {
            appearance: none;
            -webkit-appearance: none;
        }

        /* ─── GRID INTERNO DEL MODAL DE TICKET ────────────────────────────── */
        .ticket-split-view {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 28px;
            align-items: start;
        }

        .ts-left {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .ts-right {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px;
            max-height: 550px;
            overflow-y: auto;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.01);
        }

        @media (max-width: 850px) {
            .ticket-split-view {
                grid-template-columns: 1fr;
            }

            .ts-right {
                max-height: none;
            }
        }

        /* ─── META STRIP EN MODAL ─────────────────────────────────────────── */
        .meta-strip {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1px;
            background: #e2e8f0;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .meta-cell {
            background: #fff;
            padding: 12px 16px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .meta-lbl {
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .meta-val {
            font-size: .9rem;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ─── BOTONERA DE ESTADOS (FOOTER) ────────────────────────────────── */
        .status-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex-grow: 1;
        }

        .status-bar-label {
            font-size: .68rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .status-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .status-btn {
            flex: 1;
            min-width: 85px;
            padding: 10px 12px;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #64748b;
            cursor: pointer;
            transition: all .2s;
            text-align: center;
        }

        .status-btn:hover {
            background: #f1f5f9;
            color: #334155;
            border-color: #94a3b8;
        }

        .status-btn.on[data-v="en-espera"] {
            background: var(--s-wait-bg);
            color: var(--s-wait-text);
            border-color: var(--s-wait-border);
            box-shadow: 0 3px 6px rgba(193, 90, 0, .15);
        }

        .status-btn.on[data-v="por-concluir"] {
            background: var(--s-pend-bg);
            color: var(--s-pend-text);
            border-color: var(--s-pend-border);
            box-shadow: 0 3px 6px rgba(196, 125, 0, .15);
        }

        .status-btn.on[data-v="realizado"] {
            background: var(--s-done-bg);
            color: var(--s-done-text);
            border-color: var(--s-done-border);
            box-shadow: 0 3px 6px rgba(26, 140, 56, .15);
        }

        .status-btn.on[data-v="cancelado"] {
            background: var(--s-cancel-bg);
            color: var(--s-cancel-text);
            border-color: var(--s-cancel-border);
            box-shadow: 0 3px 6px rgba(185, 28, 28, .15);
        }

        /* ─── BOTONES GENERALES ───────────────────────────────────────────── */
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            border: none;
            font-weight: 600;
            font-size: .85rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all .2s;
            justify-content: center;
        }

        .btn-primary {
            background: var(--color-primary);
            color: #fff;
            box-shadow: 0 2px 4px rgba(52, 73, 85, .15);
        }

        .btn-primary:hover {
            background: var(--color-medium);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(52, 73, 85, .25);
        }

        .btn-secondary {
            background: #fff;
            color: #334155;
            border: 1px solid #cbd5e1;
        }

        .btn-secondary:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
        }

        .tbl-actions {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .tbl-actions button {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid transparent;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: .2s;
            font-size: .85rem;
        }

        .btn-view {
            background: #e0f2fe;
            color: #0284c7;
            border-color: #bae6fd;
        }

        .btn-attend {
            background: #ffedd5;
            color: #ea580c;
            border-color: #fdba74;
        }

        .btn-view:hover,
        .btn-attend:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, .08);
        }

        .btn-close-modal {
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .2);
            color: #fff;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .25s;
            flex-shrink: 0;
            font-size: .9rem;
        }

        .btn-close-modal:hover {
            background: #ef4444;
            border-color: #ef4444;
            transform: rotate(90deg);
        }

        /* ─── BLOQUE DE COMENTARIO ────────────────────────────────────────── */
        .comment-block {
            background: #fffbeb;
            border: 1px solid var(--s-pend-border);
            border-radius: 10px;
            padding: 14px 16px;
            margin-top: 10px;
            display: none;
        }

        .comment-block.show {
            display: block;
            animation: slideDown .3s ease;
        }

        .comment-block.danger {
            background: #fef2f2;
            border-color: var(--s-cancel-border);
        }

        .comment-block.danger label {
            color: var(--s-cancel-text);
        }

        .comment-block.danger .ctrl {
            border-color: #fca5a5;
            background: #fff;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ─── TIMELINE ────────────────────────────────────────────────────── */
        .timeline-title {
            font-size: .75rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding-bottom: 12px;
            border-bottom: 1px solid #cbd5e1;
            letter-spacing: .05em;
        }

        .tl-item {
            margin-bottom: 18px;
            padding-left: 20px;
            border-left: 2px solid var(--color-primary);
            position: relative;
        }

        .tl-item::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 3px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--color-primary);
            border: 2px solid #f8fafc;
            box-shadow: 0 0 0 2px #cbd5e1;
        }

        .tl-date {
            font-size: .65rem;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 4px;
            font-family: 'IBM Plex Mono', monospace;
        }

        .tl-user {
            font-size: .8rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tl-badge {
            font-size: .6rem;
            padding: 3px 6px;
            border-radius: 4px;
            background: #e2e8f0;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .tl-msg {
            font-size: .8rem;
            color: #334155;
            background: #fff;
            padding: 12px 14px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            line-height: 1.5;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
        }

        .tl-empty {
            text-align: center;
            padding: 40px 10px;
            color: #94a3b8;
            font-size: .85rem;
            font-weight: 500;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .tl-empty i {
            font-size: 1.8rem;
            color: #cbd5e1;
        }

        /* ─── LOADER ──────────────────────────────────────────────────────── */
        .modal-loader {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, .9);
            backdrop-filter: blur(3px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 50;
            border-radius: 0 0 18px 18px;
            transition: opacity .3s;
        }

        .loader-spin {
            width: 44px;
            height: 44px;
            border: 3px solid #e2e8f0;
            border-top-color: var(--color-primary);
            border-radius: 50%;
            animation: spin .8s cubic-bezier(.4, 0, .2, 1) infinite;
            margin-bottom: 16px;
        }

        .modal-loader p {
            font-size: .85rem;
            font-weight: 600;
            color: #475569;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ─── PAGINACIÓN ──────────────────────────────────────────────────── */
        .pager {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
        }

        .pager-info {
            font-size: .85rem;
            color: #64748b;
            font-weight: 600;
        }

        .pager-btns {
            display: flex;
            gap: 6px;
        }

        .pager-btn {
            background: #fff;
            border: 1px solid #cbd5e1;
            color: #334155;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            font-weight: 600;
            transition: all .2s;
        }

        .pager-btn:hover:not(:disabled) {
            background: #f8fafc;
            border-color: var(--color-primary);
            color: var(--color-primary);
        }

        .pager-btn.on {
            background: var(--color-primary);
            color: #fff;
            border-color: var(--color-primary);
            box-shadow: 0 2px 4px rgba(52, 73, 85, .2);
        }

        .pager-btn:disabled {
            opacity: .5;
            cursor: not-allowed;
        }
    </style>

    <div class="content active">

        {{-- ── ESTADÍSTICAS ────────────────────────────────────────────────── --}}
        <div class="stats-row">
            <div class="stat-card c-main">
                <div class="stat-info">
                    <span class="stat-label">Total Tickets</span>
                    <h2 class="stat-value" id="stat-total">0</h2>
                    <span class="stat-sub">Histórico global</span>
                </div>
                <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
            </div>
            <div class="stat-card c-wait">
                <div class="stat-info">
                    <span class="stat-label">En Espera</span>
                    <h2 class="stat-value" id="stat-espera">0</h2>
                    <span class="stat-sub">Pausados</span>
                </div>
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
            </div>
            <div class="stat-card c-pend">
                <div class="stat-info">
                    <span class="stat-label">Por Concluir</span>
                    <h2 class="stat-value" id="stat-por-concluir">0</h2>
                    <span class="stat-sub">Pendiente cierre</span>
                </div>
                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
            <div class="stat-card c-done">
                <div class="stat-info">
                    <span class="stat-label">Realizados</span>
                    <h2 class="stat-value" id="stat-realizado">0</h2>
                    <span class="stat-sub">Completados</span>
                </div>
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            </div>
            <div class="stat-card c-cancel">
                <div class="stat-info">
                    <span class="stat-label">Cancelados</span>
                    <h2 class="stat-value" id="stat-cancelado">0</h2>
                    <span class="stat-sub">No concluidos</span>
                </div>
                <div class="stat-icon"><i class="fas fa-ban"></i></div>
            </div>
        </div>

        {{-- ── TABLA PRINCIPAL ────────────────────────────────────────────── --}}
        <section class="data-section">
            <div class="table-controls">
                <button class="btn btn-primary" onclick="openNewModal()">
                    <i class="fas fa-plus"></i> Nuevo Ticket
                </button>

                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="buscador" placeholder="Buscar folio, No. ticket, usuario o asunto...">
                </div>

                <select id="mostrar" class="f-sel">
                    <option value="7" selected>Mostrar: 7</option>
                    <option value="15">Mostrar: 15</option>
                    <option value="30">Mostrar: 30</option>
                    <option value="todos">Mostrar: Todos</option>
                </select>

                <select id="fil-estado" class="f-sel">
                    <option value="">Estado: Todos</option>
                    <option value="nuevo">Nuevo</option>
                    <option value="abierto">Abierto</option>
                    <option value="en-espera">En Espera</option>
                    <option value="por-concluir">Por Concluir</option>
                    <option value="realizado">Realizado</option>
                    <option value="cancelado">Cancelado</option>
                </select>

                <select id="fil-prioridad" class="f-sel">
                    <option value="">Prioridad: Todas</option>
                    <option value="sin clasificar">Sin Clasificar</option>
                    <option value="alta">Alta</option>
                    <option value="media">Media</option>
                    <option value="baja">Baja</option>
                </select>

                <button class="btn btn-secondary" onclick="exportarTabla()">
                    <i class="fas fa-file-export"></i> Exportar
                </button>
            </div>

            <div class="table-responsive">
                <table class="main-table" id="tabla-tickets">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Folio</th>
                            <th>Fecha</th>
                            <th>Asunto</th>
                            <th>Usuario</th>
                            <th>Dpto.</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th style="text-align:center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tickets-body"></tbody>
                </table>
            </div>

            <div class="pager" id="pager" style="display:none;">
                <div class="pager-info" id="pager-info">Mostrando 0 a 0 de 0</div>
                <div class="pager-btns" id="pager-btns"></div>
            </div>
        </section>
    </div>

    {{-- ── MODAL FLOTANTE — NUEVO TICKET ───────────────────────────────────── --}}
    <div class="modal-overlay" id="modal-new">
        <div class="modal-box">
            <div class="modal-head">
                <div>
                    <h3><i class="fas fa-plus-circle"></i> Apertura de Nuevo Ticket</h3>
                    <p class="sub">Complete los datos para registrar la incidencia</p>
                </div>
                <button class="btn-close-modal" onclick="closeNewModal()"><i class="fas fa-times"></i></button>
            </div>

            <div class="modal-body">
                <div class="meta-strip">
                    <div class="meta-cell">
                        <span class="meta-lbl"><i class="far fa-calendar-alt"></i> Fecha Emisión</span>
                        <span class="meta-val" id="modal-date">Cargando...</span>
                    </div>
                    <div class="meta-cell">
                        <span class="meta-lbl"><i class="fas fa-ticket-alt"></i> No. de Ticket</span>
                        <span class="meta-val mono tk-num">TK-AUTO</span>
                    </div>
                    <div class="meta-cell">
                        <span class="meta-lbl"><i class="fas fa-hashtag"></i> Folio del Área</span>
                        <span class="meta-val mono tk-folio">TK-AUTO</span>
                    </div>
                </div>

                <form id="form-new">
                    @csrf
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-building"></i> Área del Solicitante</label>
                            <div class="field-wrap">
                                <i class="field-icon fas fa-building"></i>
                                <input type="text" class="ctrl"
                                    value="{{ auth()->user()->employee->area->name ?? 'Área No Asignada' }}" disabled>
                                <input type="hidden" id="n-area"
                                    value="{{ auth()->user()->employee->area->code ?? 'SIS' }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Solicitante</label>
                            <div class="field-wrap">
                                <i class="field-icon fas fa-user"></i>
                                <input type="text" class="ctrl"
                                    value="{{ auth()->user()->name ?? 'Usuario Actual' }}" disabled>
                            </div>
                        </div>
                    </div>

                    <hr class="form-divider">

                    <div class="form-group">
                        <label><i class="fas fa-heading"></i> Asunto Breve *</label>
                        <div class="field-wrap">
                            <i class="field-icon fas fa-heading"></i>
                            <input type="text" id="n-asunto" class="ctrl"
                                placeholder="Ej: Falla en el acceso al servidor..." required>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label><i class="fas fa-align-left"></i> Descripción Detallada *</label>
                        <textarea id="n-desc" class="ctrl no-icon" rows="3"
                            placeholder="Proporcione los detalles de la falla o solicitud..." required></textarea>
                    </div>
                </form>
            </div>

            <div class="modal-foot" style="justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn btn-secondary" onclick="closeNewModal()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" form="form-new" class="btn btn-primary" id="btn-new-submit">
                    <i class="fas fa-paper-plane"></i> Generar Ticket
                </button>
            </div>
        </div>
    </div>

    {{-- ── MODAL FLOTANTE AMPLIO — VER/ATENDER TICKET ──────────────────────── --}}
    <!-- El JS inyecta "mode-view" o "mode-edit" aquí antes de abrir para controlar la botonera al instante -->
    <div class="modal-overlay" id="modal-ticket">
        <div class="modal-box">

            <div class="modal-head">
                <div>
                    <h3 id="modal-ticket-title"><i class="fas fa-ticket-alt"></i> Detalles del Ticket</h3>
                    <p class="sub" id="modal-ticket-sub">
                        <span><i class="fas fa-user"></i> ---</span>
                        <span><i class="fas fa-building"></i> ---</span>
                    </p>
                </div>
                <button class="btn-close-modal" onclick="closeTicketModal()"><i class="fas fa-times"></i></button>
            </div>

            <form id="ticket-form" style="display: flex; flex-direction: column; flex-grow: 1; overflow: hidden;">
                @csrf
                <input type="hidden" id="p-id">

                <div class="modal-body">
                    {{-- Loader Interno --}}
                    <div id="modal-ticket-loader" class="modal-loader" style="display:none;">
                        <div class="loader-spin"></div>
                        <p>Obteniendo información...</p>
                    </div>

                    <div class="ticket-split-view">
                        {{-- Lado Izquierdo: Formulario --}}
                        <div class="ts-left">
                            <div class="meta-strip" style="margin-bottom: 16px;">
                                <div class="meta-cell">
                                    <span class="meta-lbl"><i class="far fa-calendar-alt"></i> Fecha</span>
                                    <span class="meta-val" id="p-fecha">--</span>
                                </div>
                                <div class="meta-cell">
                                    <span class="meta-lbl"><i class="fas fa-ticket-alt"></i> Ticket</span>
                                    <span class="meta-val mono tk-num" id="p-num">--</span>
                                </div>
                                <div class="meta-cell">
                                    <span class="meta-lbl"><i class="fas fa-hashtag"></i> Folio</span>
                                    <span class="meta-val mono tk-folio" id="p-folio">--</span>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-tasks"></i> Estado Actual</label>
                                    <div class="field-wrap">
                                        <!-- Aplicamos la clase bg-icon para no opacar el ícono en modo vista -->
                                        <i class="field-icon bg-icon fas fa-circle" style="font-size:.6rem;"></i>
                                        <select id="p-estado" class="ctrl" disabled>
                                            <option value="nuevo">1. Nuevo</option>
                                            <option value="abierto">2. Abierto</option>
                                            <option value="en-espera">3. En Espera</option>
                                            <option value="por-concluir">4. Por Concluir</option>
                                            <option value="realizado">5. Realizado</option>
                                            <option value="cancelado">6. Cancelado</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-flag"></i> Prioridad</label>
                                    <div class="field-wrap">
                                        <!-- Aplicamos la clase bg-icon para no opacar el ícono en modo vista -->
                                        <i class="field-icon bg-icon fas fa-flag"></i>
                                        <select id="p-prioridad" class="ctrl" disabled
                                            onchange="setSelectColor(this,'p')">
                                            <option value="sin clasificar">Sin Clasificar</option>
                                            <option value="alta">Alta</option>
                                            <option value="media">Media</option>
                                            <option value="baja">Baja</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-heading"></i> Asunto Breve</label>
                                <div class="field-wrap">
                                    <i class="field-icon fas fa-heading"></i>
                                    <input type="text" id="p-asunto" class="ctrl" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-align-left"></i> Descripción Detallada</label>
                                <textarea id="p-desc" class="ctrl no-icon" rows="2" required></textarea>
                            </div>

                            {{-- Bloque comentario obligatorio --}}
                            <div class="comment-block" id="p-comment-block">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label><i class="fas fa-exclamation-circle"></i> <span
                                            id="p-comment-label">Justificación Requerida *</span></label>
                                    <textarea id="p-comment" class="ctrl no-icon" rows="2" placeholder="Agregue la justificación obligatoria..."></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Lado Derecho: Timeline --}}
                        <div class="ts-right">
                            <h4 class="timeline-title"><i class="fas fa-history"></i> Historial de Gestiones</h4>
                            <div id="p-timeline-items"></div>
                        </div>
                    </div>
                </div>

                {{-- Footer Activo (Botones de Estado) - Se oculta AL INSTANTE si el modal tiene .mode-view --}}
                <div class="modal-foot" id="p-footer">
                    <div class="status-actions">
                        <span class="status-bar-label"><i class="fas fa-random"></i> Modificar Estado:</span>
                        <div class="status-bar" id="p-status-bar">
                            <button type="button" class="status-btn" data-v="en-espera"
                                onclick="pickStatus('en-espera')">En Espera</button>
                            <button type="button" class="status-btn" data-v="por-concluir"
                                onclick="pickStatus('por-concluir')">Por Concluir</button>
                            <button type="button" class="status-btn" data-v="realizado"
                                onclick="pickStatus('realizado')">Realizado</button>
                            <button type="button" class="status-btn" data-v="cancelado"
                                onclick="pickStatus('cancelado')">Cancelado</button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" id="btn-save"
                        style="margin-left: 20px; padding: 12px 24px;">
                        <i class="fas fa-save"></i> Guardar Gestión
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        /* ── CONSTANTES ─────────────────────────────────────────────────────────── */
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ||
            document.querySelector('input[name="_token"]').value;
        const H = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json'
        };

        const STATUS_COLORS = {
            nuevo: 'var(--s-new-text)',
            abierto: 'var(--s-open-text)',
            'en-espera': 'var(--s-wait-text)',
            'por-concluir': 'var(--s-pend-text)',
            realizado: 'var(--s-done-text)',
            cancelado: 'var(--s-cancel-text)',
        };
        const PRIO_COLORS = {
            alta: 'var(--s-cancel-text)',
            media: 'var(--s-pend-text)',
            baja: 'var(--s-done-text)',
            'sin-clasificar': '#64748b',
        };

        let allTickets = [],
            filteredTickets = [],
            page = 1;

        /* ── INIT ───────────────────────────────────────────────────────────────── */
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('modal-date').innerText =
                new Date().toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            loadTickets();
            setupAutoResize();
        });

        /* ── AUTO-RESIZE TEXTAREAS ──────────────────────────────────────────────── */
        function setupAutoResize() {
            document.querySelectorAll('textarea').forEach(ta => {
                ta.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = this.scrollHeight + 'px';
                });
            });
        }

        function forceResizeTextareas() {
            setTimeout(() => {
                document.querySelectorAll('.modal-overlay.open textarea').forEach(ta => {
                    ta.style.height = 'auto';
                    ta.style.height = ta.scrollHeight + 'px';
                });
            }, 50);
        }

        function resetTextareas() {
            document.querySelectorAll('textarea').forEach(ta => ta.style.height = 'auto');
        }

        /* ── CARGA DE TICKETS Y ESTADÍSTICAS ────────────────────────────────────── */
        async function loadTickets() {
            try {
                const r = await fetch('{{ route('systems.tickets.get') }}', {
                    headers: H
                });
                if (!r.ok) throw new Error();
                allTickets = await r.json();
                filter();
                updateStats(allTickets);
            } catch {
                Swal.fire('Error', 'No se pudieron cargar los tickets.', 'error');
            }
        }

        function updateStats(data) {
            document.getElementById('stat-total').innerText = data.length;
            document.getElementById('stat-espera').innerText = data.filter(t => t.status === 'en-espera').length;
            document.getElementById('stat-por-concluir').innerText = data.filter(t => t.status === 'por-concluir').length;
            document.getElementById('stat-realizado').innerText = data.filter(t => t.status === 'realizado').length;
            document.getElementById('stat-cancelado').innerText = data.filter(t => t.status === 'cancelado').length;
        }

        /* ── FILTRADO Y TABLA ───────────────────────────────────────────────────── */
        document.getElementById('buscador').addEventListener('keyup', filter);
        document.getElementById('fil-estado').addEventListener('change', filter);
        document.getElementById('fil-prioridad').addEventListener('change', filter);
        document.getElementById('mostrar').addEventListener('change', () => {
            page = 1;
            renderTable();
        });

        function filter() {
            const q = document.getElementById('buscador').value.toLowerCase();
            const est = document.getElementById('fil-estado').value;
            const pri = document.getElementById('fil-prioridad').value;

            filteredTickets = allTickets.filter(t => {
                if (q && !`${t.display_id} ${t.folio} ${t.subject} ${t.user_name}`.toLowerCase().includes(q))
                return false;
                if (est && t.status !== est) return false;
                if (pri && t.priority !== pri) return false;
                return true;
            });
            page = 1;
            renderTable();
        }

        function renderTable() {
            const tbody = document.getElementById('tickets-body');
            tbody.innerHTML = '';

            if (!filteredTickets.length) {
                tbody.innerHTML =
                    `<tr><td colspan="9" style="text-align:center;padding:20px;color:#64748b;">No hay tickets que coincidan.</td></tr>`;
                document.getElementById('pager').style.display = 'none';
                return;
            }

            const perPage = document.getElementById('mostrar').value === 'todos' ?
                filteredTickets.length : parseInt(document.getElementById('mostrar').value);

            const totalPages = Math.max(1, Math.ceil(filteredTickets.length / perPage));
            if (page > totalPages) page = totalPages;

            const start = (page - 1) * perPage;
            filteredTickets.slice(start, start + perPage).forEach((t, i) => {
                const tr = document.createElement('tr');
                tr.style.animationDelay = `${i * .04}s`;
                tr.innerHTML = `
            <td><span class="tk-num">${t.display_id}</span></td>
            <td><span class="tk-folio">${t.folio}</span></td>
            <td>${t.created_at}</td>
            <td>${t.subject}</td>
            <td>${t.user_name}</td>
            <td>${t.department_name}</td>
            <td><span class="ptag ${t.priority.replace(' ', '-')}">${cap(t.priority)}</span></td>
            <td><span class="stag ${t.status}">${fmtStatus(t.status)}</span></td>
            <td class="tbl-actions">
                <button class="btn-view"   title="Ver Detalles"    onclick="openTicketModal('ver',${t.id})"><i class="fas fa-eye"></i></button>
                <button class="btn-attend" title="Atender Ticket"  onclick="openTicketModal('atender',${t.id})"><i class="fas fa-tools"></i></button>
            </td>`;
                tbody.appendChild(tr);
            });

            renderPager(totalPages, start, Math.min(start + perPage, filteredTickets.length));
        }

        /* ── PAGINACIÓN ─────────────────────────────────────────────────────────── */
        function renderPager(totalPages, start, end) {
            const pager = document.getElementById('pager');
            if (totalPages <= 1) {
                pager.style.display = 'none';
                return;
            }
            pager.style.display = 'flex';

            document.getElementById('pager-info').innerText =
                `Mostrando ${start + 1} a ${end} de ${filteredTickets.length} registros`;

            const btns = document.getElementById('pager-btns');
            btns.innerHTML = '';

            const addBtn = (content, disabled, active, onClick) => {
                const b = document.createElement('button');
                b.className = `pager-btn${active ? ' on' : ''}`;
                b.innerHTML = content;
                b.disabled = disabled;
                b.onclick = onClick;
                btns.appendChild(b);
            };

            addBtn('<i class="fas fa-chevron-left"></i>', page === 1, false, () => {
                page--;
                renderTable();
            });
            for (let i = 1; i <= totalPages; i++) {
                if (totalPages > 7 && Math.abs(i - page) > 1 && i > 2 && i < totalPages - 1) {
                    if (btns.lastChild?.innerText !== '...') {
                        const dots = document.createElement('span');
                        dots.style.padding = '5px';
                        dots.innerText = '...';
                        btns.appendChild(dots);
                    }
                    continue;
                }
                addBtn(i, false, i === page, () => {
                    page = i;
                    renderTable();
                });
            }
            addBtn('<i class="fas fa-chevron-right"></i>', page === totalPages, false, () => {
                page++;
                renderTable();
            });
        }

        /* ── COLORES DINÁMICOS DE SELECTS ───────────────────────────────────────── */
        function setSelectColor(el, type) {
            el.className = el.className.replace(/bg-[sp]-[\w-]+/g, '').trim();
            const v = el.value.replace(' ', '-');
            el.classList.add(type === 's' ? `bg-s-${v}` : `bg-p-${v}`);

            const icon = el.closest('.field-wrap')?.querySelector('.field-icon');
            if (icon) icon.style.color = type === 's' ? STATUS_COLORS[el.value] : PRIO_COLORS[v];
        }

        /* ── MODAL — NUEVO TICKET ───────────────────────────────────────────────── */
        function openNewModal() {
            document.getElementById('modal-new').classList.add('open');
            forceResizeTextareas();
        }

        function closeNewModal() {
            document.getElementById('modal-new').classList.remove('open');
            document.getElementById('form-new').reset();
            resetTextareas();
        }

        document.getElementById('form-new').addEventListener('submit', async e => {
            e.preventDefault();
            const btn = document.getElementById('btn-new-submit');
            const payload = {
                area_code: document.getElementById('n-area').value,
                subject: document.getElementById('n-asunto').value,
                description: document.getElementById('n-desc').value
            };

            try {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
                const r = await fetch('{{ route('systems.tickets.store') }}', {
                    method: 'POST',
                    headers: H,
                    body: JSON.stringify(payload)
                });
                const res = await r.json();
                if (r.ok && res.success) {
                    Swal.fire({
                        title: '¡Ticket Generado!',
                        text: `Folio asignado: ${res.folio}.`,
                        icon: 'success',
                        confirmButtonColor: '#1e293b'
                    }).then(() => {
                        closeNewModal();
                        loadTickets();
                    });
                } else throw new Error(res.message);
            } catch (err) {
                Swal.fire('Error', err.message, 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Generar Ticket';
            }
        });

        /* ── MODAL INTELIGENTE — VER / ATENDER TICKET ───────────────────────────── */
        async function openTicketModal(action, id) {
            const modal = document.getElementById('modal-ticket');
            const loader = document.getElementById('modal-ticket-loader');

            // 1. INTELIGENCIA DE UI: Setear el modo ANTES de hacer el modal visible.
            // Al agregar 'mode-view', el CSS se encarga de ocultar el footer al instante y estilizar el formulario.
            modal.classList.remove('mode-view', 'mode-edit');
            modal.classList.add(action === 'ver' ? 'mode-view' : 'mode-edit');

            // 2. Preparar UI
            loader.style.display = 'flex';
            loader.style.opacity = '1';
            modal.classList.add('open');

            document.getElementById('modal-ticket-title').innerHTML = action === 'ver' ?
                '<i class="fas fa-eye"></i> Detalles del Ticket' :
                '<i class="fas fa-tools"></i> Atendiendo Ticket';

            try {
                const r = await fetch(`{{ url('/systems/tickets/show') }}/${id}`, {
                    headers: H
                });
                const data = await r.json();
                if (!data.success) throw new Error('No se pudieron cargar los detalles.');

                let d = data.ticket;

                // Si se da click en atender, pero el ticket ya está finalizado/cancelado
                // Forzamos al instante el modo vista
                if (action === 'atender' && ['realizado', 'cancelado'].includes(d.status)) {
                    action = 'ver';
                    modal.classList.replace('mode-edit', 'mode-view');
                }

                // Lógica de "Tomar Ticket" automático
                if (action === 'atender' && d.status === 'nuevo') {
                    const upd = await fetch(`{{ url('/systems/tickets/update-status') }}/${id}`, {
                        method: 'PUT',
                        headers: H,
                        body: JSON.stringify({
                            status: 'abierto',
                            priority: d.priority,
                            subject: d.subject,
                            description: d.description,
                            comentario: 'El ticket ha sido tomado para su atención.'
                        })
                    });
                    if (upd.ok) {
                        d.status = 'abierto';
                        loadTickets();
                        if (!d.trackings) d.trackings = [];
                        d.trackings.push({
                            created_at: new Date().toISOString(),
                            user: {
                                name: '{{ auth()->user()->name ?? 'Sistema' }}'
                            },
                            status_after: 'abierto',
                            message: 'El ticket ha sido tomado para su atención.'
                        });
                    }
                }

                // Cargar datos en el form
                document.getElementById('p-id').value = d.id;
                document.getElementById('p-folio').innerText = d.folio;
                document.getElementById('p-num').innerText = d.display_id;
                document.getElementById('p-fecha').innerText = d.created_at;

                const selEst = document.getElementById('p-estado');
                selEst.value = d.status;
                setSelectColor(selEst, 's');

                const selPri = document.getElementById('p-prioridad');
                selPri.value = d.priority;
                setSelectColor(selPri, 'p');

                document.getElementById('p-asunto').value = d.subject;
                document.getElementById('p-desc').value = d.description;
                document.getElementById('p-comment').value = '';
                document.getElementById('p-comment-block').classList.remove('show', 'danger');

                document.getElementById('modal-ticket-sub').innerHTML =
                    `<span><i class="fas fa-user"></i> ${d.user_name}</span>
                     <span><i class="fas fa-building"></i> ${d.department_name}</span>`;

                renderTimeline(d.trackings);
                forceResizeTextareas();

                // Bloquear inputs para 'ver' (aunque el CSS mode-view ya disfraza los inputs visualmente)
                document.querySelectorAll('#ticket-form .ctrl').forEach(el => {
                    if (action === 'ver') {
                        el.tagName === 'SELECT' ? (el.disabled = true) : (el.readOnly = true);
                    } else {
                        if (el.id === 'p-estado') el.disabled = true;
                        else if (el.tagName === 'SELECT') el.disabled = false;
                        else el.readOnly = false;
                    }
                });

                // Si es modo atender, pre-configurar los botones de estado
                if (action === 'atender') {
                    const btns = document.querySelectorAll('.status-btn');
                    btns.forEach(b => {
                        b.style.display = 'inline-block';
                        b.classList.remove('on');
                    });

                    if (d.status === 'por-concluir') {
                        btns.forEach(b => {
                            if (!['realizado', 'cancelado'].includes(b.dataset.v)) b.style.display = 'none';
                        });
                    }

                    const cur = document.querySelector(`.status-btn[data-v="${d.status}"]`);
                    if (cur) cur.classList.add('on');
                }

                // Ocultar loader suavemente al terminar
                setTimeout(() => {
                    loader.style.opacity = '0';
                    setTimeout(() => loader.style.display = 'none', 300);
                }, 400);

            } catch (err) {
                Swal.fire('Error', err.message, 'error');
                closeTicketModal();
            }
        }

        function closeTicketModal() {
            const modal = document.getElementById('modal-ticket');
            modal.classList.remove('open');
            // Quitamos los modos una vez termine la animación de cierre (0.3s)
            setTimeout(() => {
                modal.classList.remove('mode-view', 'mode-edit');
                document.getElementById('ticket-form').reset();
                document.getElementById('p-comment-block').classList.remove('show', 'danger');
                document.getElementById('p-estado').className = 'ctrl';
                document.getElementById('p-prioridad').className = 'ctrl';
                resetTextareas();
            }, 300);
        }

        /* ── BOTONES DE ESTADO ──────────────────────────────────────────────────── */
        function pickStatus(val) {
            const sel = document.getElementById('p-estado');
            sel.value = val;
            setSelectColor(sel, 's');

            document.querySelectorAll('.status-btn').forEach(b => b.classList.remove('on'));
            const btn = document.querySelector(`.status-btn[data-v="${val}"]`);
            if (btn) btn.classList.add('on');

            checkCommentBlock(val);
        }

        function checkCommentBlock(val) {
            const block = document.getElementById('p-comment-block');
            const label = document.getElementById('p-comment-label');
            const field = document.getElementById('p-comment');

            if (val === 'por-concluir') {
                block.classList.add('show');
                block.classList.remove('danger');
                label.textContent = 'Justificación técnica de "Por Concluir" *';
                field.placeholder = 'Detalle qué impide el cierre...';
                forceResizeTextareas();
            } else if (val === 'cancelado') {
                block.classList.add('show', 'danger');
                label.textContent = 'Motivo de cancelación *';
                field.placeholder = 'Explique el motivo de la cancelación...';
                forceResizeTextareas();
            } else {
                block.classList.remove('show', 'danger');
                field.value = '';
                field.style.height = 'auto';
            }
        }

        /* ── GUARDAR GESTIÓN DE TICKET ──────────────────────────────────────────── */
        document.getElementById('ticket-form').addEventListener('submit', async e => {
            e.preventDefault();
            const id = document.getElementById('p-id').value;
            const estado = document.getElementById('p-estado').value;
            const prioridad = document.getElementById('p-prioridad').value;
            const comentario = document.getElementById('p-comment').value;

            if (prioridad === 'sin clasificar') {
                Swal.fire({
                    title: 'Prioridad Requerida',
                    text: 'Debes asignar una prioridad (Alta, Media o Baja).',
                    icon: 'warning',
                    confirmButtonColor: '#1e293b'
                });
                return;
            }
            if (['por-concluir', 'cancelado'].includes(estado) && !comentario.trim()) {
                Swal.fire({
                    title: 'Justificación obligatoria',
                    text: 'Debe agregar un comentario.',
                    icon: 'warning',
                    confirmButtonColor: '#1e293b'
                });
                return;
            }

            const payload = {
                status: estado,
                priority: prioridad,
                subject: document.getElementById('p-asunto').value,
                description: document.getElementById('p-desc').value,
                comentario: comentario,
            };
            const btn = document.getElementById('btn-save');

            try {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                const r = await fetch(`{{ url('/systems/tickets/update-status') }}/${id}`, {
                    method: 'PUT',
                    headers: H,
                    body: JSON.stringify(payload)
                });
                const res = await r.json();
                if (r.ok && res.success) {
                    Swal.fire({
                            title: '¡Gestión guardada!',
                            text: res.message,
                            icon: 'success',
                            confirmButtonColor: '#1e293b'
                        })
                        .then(() => {
                            closeTicketModal();
                            loadTickets();
                        });
                } else throw new Error(res.message);
            } catch (err) {
                Swal.fire('Error', err.message, 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Guardar Gestión';
            }
        });

        /* ── TIMELINE ───────────────────────────────────────────────────────────── */
        function renderTimeline(trackings) {
            const content = document.getElementById('p-timeline-items');
            const items = (trackings || []).filter(t => t.message?.trim());

            if (!items.length) {
                content.innerHTML = `
                    <div class="tl-empty">
                        <i class="fas fa-comment-dots" style="opacity:0.3"></i>
                        <span>No hay historial registrado aún.</span>
                    </div>`;
                return;
            }

            content.innerHTML = items.map(t => {
                const date = new Date(t.created_at || Date.now())
                    .toLocaleString('es-ES', {
                        day: '2-digit',
                        month: 'short',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                return `
                <div class="tl-item">
                    <div class="tl-date">${date}</div>
                    <div class="tl-user">${t.user?.name ?? 'Sistema'} <span class="tl-badge">${fmtStatus(t.status_after)}</span></div>
                    <div class="tl-msg">${t.message}</div>
                </div>`;
            }).join('');
        }

        /* ── HELPERS ────────────────────────────────────────────────────────────── */
        function cap(s) {
            return s.charAt(0).toUpperCase() + s.slice(1);
        }

        function fmtStatus(s) {
            return (s || '').split('-').map(cap).join(' ');
        }
    </script>
@endpush
