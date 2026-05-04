@extends('modules.systems.tickets.index')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@500;700&display=swap');

        :root {
            /* ESTADOS — PALETA OSCURA Y DIFERENCIADA */
            --status-new-text: #0c2d5e;
            --status-new-bg: #d4e4f7;
            --status-new-border: #2e6db4;
            --status-new-dot: #1a5fb4;

            --status-open-text: #0b4a52;
            --status-open-bg: #c8edf3;
            --status-open-border: #1a8fa3;
            --status-open-dot: #0e7a8c;

            --status-waiting-text: #7a2c00;
            --status-waiting-bg: #fddbc7;
            --status-waiting-border: #c15a00;
            --status-waiting-dot: #c15a00;

            --status-pending-text: #6b3b00;
            --status-pending-bg: #fde8ab;
            --status-pending-border: #c47d00;
            --status-pending-dot: #c47d00;

            --status-success-text: #0d4a1e;
            --status-success-bg: #bbf0cc;
            --status-success-border: #1a8c38;
            --status-success-dot: #15722d;

            --status-cancelled-text: #6b0c0c;
            --status-cancelled-bg: #fcc8c8;
            --status-cancelled-border: #b91c1c;
            --status-cancelled-dot: #9b1111;
        }

        .content.active { background: #f0f4f8; }

        .tickets-layout {
            display: grid; grid-template-columns: 440px 1fr; gap: 20px; align-items: start;
            transition: grid-template-columns 0.55s cubic-bezier(0.16, 1, 0.3, 1), gap 0.55s ease;
        }
        .tickets-layout.form-hidden { grid-template-columns: 0 1fr; gap: 0; }

        /* ESTADÍSTICAS */
        .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 14px; padding: 4px 1rem 16px; }
        .stat-card {
            background: #fff; border-radius: 14px; padding: 18px 16px; display: flex; justify-content: space-between; align-items: center;
            border: 1px solid #c8d5e3; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04); transition: transform 0.25s, box-shadow 0.25s; min-height: 90px; gap: 12px;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 18px rgba(0, 0, 0, 0.07); }
        .card-main { background: linear-gradient(140deg, var(--color-primary) 0%, #263840 100%); border: none; color: white; }
        .stat-info { display: flex; flex-direction: column; gap: 4px; }
        .stat-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #5c7a96; }
        .card-main .stat-label { color: rgba(255, 255, 255, 0.75); }
        .stat-value { font-size: 1.85rem; font-weight: 800; color: #0c1e2e; line-height: 1; }
        .card-main .stat-value { color: #fff; }
        .stat-badge { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
        .card-main .stat-badge { background: rgba(255, 255, 255, 0.18); color: #fff; border-radius: 50%; }
        .status-waiting .stat-badge { background: var(--status-waiting-bg); color: var(--status-waiting-text); border: 1.5px solid var(--status-waiting-border); }
        .status-pending .stat-badge { background: var(--status-pending-bg); color: var(--status-pending-text); border: 1.5px solid var(--status-pending-border); }
        .status-success .stat-badge { background: var(--status-success-bg); color: var(--status-success-text); border: 1.5px solid var(--status-success-border); }
        .status-cancelled .stat-badge { background: var(--status-cancelled-bg); color: var(--status-cancelled-text); border: 1.5px solid var(--status-cancelled-border); }

        /* MODALES */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(10, 22, 40, 0.72); backdrop-filter: blur(5px);
            z-index: 2000; display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transition: all 0.3s ease;
        }
        .modal-overlay.active { opacity: 1; visibility: visible; }
        .modal-container {
            background: #fff; border-radius: 20px; width: 100%; max-width: 680px; max-height: 90vh;
            display: flex; flex-direction: column; box-shadow: 0 30px 60px -10px rgba(0, 0, 0, 0.35); border: 1px solid #b8ccdc;
            transform: scale(0.93) translateY(28px); transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .modal-overlay.active .modal-container { transform: scale(1) translateY(0); }
        .panel-header {
            display: flex; justify-content: space-between; align-items: center; padding: 16px 22px;
            background: linear-gradient(135deg, var(--color-primary) 0%, #263840 100%); color: white; border-radius: 19px 19px 0 0; flex-shrink: 0;
        }
        .panel-header h3 { margin: 0; font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .panel-header .header-sub { color: #ffffff; font-weight: 600; font-size: 0.75rem; margin-top: 4px; display: flex; align-items: center; gap: 6px; }
        .btn-close-panel {
            background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.25); color: white; width: 28px; height: 28px;
            border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.25s; flex-shrink: 0; font-size: 0.85rem;
        }
        .btn-close-panel:hover { background: #dc2626; border-color: #dc2626; transform: rotate(90deg); }
        .modal-body { padding: 22px 26px; overflow-y: auto; background: #f8fafc; border-radius: 0 0 19px 19px; }

        /* PANEL LATERAL - ESTRUCTURA FLEX CON SCROLL */
        .form-section {
            background: #f8fafc; border-radius: 14px; border: 1px solid #c4d4e3; box-shadow: 0 8px 24px rgba(15, 30, 50, 0.07);
            position: sticky; top: 20px; max-height: calc(100vh - 40px);
            display: flex; flex-direction: column; overflow: hidden;
            opacity: 1; transform: translateX(0) scale(1); transform-origin: left top; transition: all 0.55s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .tickets-layout.form-hidden .form-section { opacity: 0; transform: translateX(-120%) scale(0.95); min-width: 0; width: 0; padding: 0; border: none; visibility: hidden; }

        .lateral-header {
            display: flex; justify-content: space-between; align-items: flex-start; padding: 14px 16px;
            background: linear-gradient(135deg, var(--color-primary) 0%, #263840 100%); color: white; border-radius: 13px 13px 0 0; flex-shrink: 0; gap: 12px;
        }
        .lateral-header-content { flex-grow: 1; min-width: 0; }
        .lateral-header h3 { margin: 0; font-size: 0.95rem; font-weight: 700; display: flex; align-items: flex-start; gap: 8px; line-height: 1.3; }

        #lateral-form { display: flex; flex-direction: column; flex-grow: 1; overflow: hidden; position: relative; min-height: 0; }
        .form-body { padding: 16px; flex-grow: 1; overflow-y: auto; position: relative; min-height: 0; }

        /* SCROLLBAR PERSONALIZADO ENTERPRISE */
        .form-body::-webkit-scrollbar { width: 6px; }
        .form-body::-webkit-scrollbar-track { background: transparent; }
        .form-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .form-body::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* BARRA FIJA (FOOTER) EN EL PANEL LATERAL */
        .lateral-footer {
            flex-shrink: 0; background: #fff; border-top: 1px solid #c4d4e3; padding: 12px 16px;
            border-radius: 0 0 13px 13px; box-shadow: 0 -4px 12px rgba(0,0,0,0.03); z-index: 10;
        }

        /* BOTONERA DE ESTADOS INTERACTIVA */
        .status-selector-label { font-size: 0.65rem; font-weight: 700; color: #5c7a96; text-transform: uppercase; margin-bottom: 8px; display: block; }
        .status-bar-container { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
        .status-btn {
            flex: 1; min-width: 70px; padding: 6px; font-size: 0.65rem; font-weight: 700; text-transform: uppercase;
            border-radius: 6px; border: 1px solid #cbd5e1; background: #f1f5f9; color: #64748b; cursor: pointer; transition: all 0.2s;
        }
        .status-btn:hover { background: #e2e8f0; color: #334155; }
        .status-btn.active[data-val="en-espera"]    { background: var(--status-waiting-bg); color: var(--status-waiting-text); border-color: var(--status-waiting-border); box-shadow: 0 2px 4px rgba(193,90,0,0.15); }
        .status-btn.active[data-val="por-concluir"] { background: var(--status-pending-bg); color: var(--status-pending-text); border-color: var(--status-pending-border); box-shadow: 0 2px 4px rgba(196,125,0,0.15); }
        .status-btn.active[data-val="realizado"]    { background: var(--status-success-bg); color: var(--status-success-text); border-color: var(--status-success-border); box-shadow: 0 2px 4px rgba(26,140,56,0.15); }
        .status-btn.active[data-val="cancelado"]    { background: var(--status-cancelled-bg); color: var(--status-cancelled-text); border-color: var(--status-cancelled-border); box-shadow: 0 2px 4px rgba(185,28,28,0.15); }

        /* LOADER LATERAL CENTRADO */
        .lateral-loader {
            position: absolute; inset: 0; background: rgba(248, 250, 252, 0.85); backdrop-filter: blur(2px);
            display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 50;
        }
        .loader-spinner {
            width: 40px; height: 40px; border: 3px solid #dce6f0; border-top-color: var(--color-primary);
            border-radius: 50%; animation: spin 0.8s cubic-bezier(0.4, 0, 0.2, 1) infinite; margin-bottom: 12px;
        }
        .lateral-loader p { font-size: 0.8rem; font-weight: 600; color: #5c7a96; letter-spacing: 0.03em; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* TIMELINE (BITÁCORA) */
        .timeline-container { margin-top: 18px; padding-top: 14px; border-top: 1px dashed #cbd5e1; flex-shrink: 0; }
        .timeline-title { font-size: 0.75rem; font-weight: 700; color: #5c7a96; text-transform: uppercase; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
        .timeline-item { margin-bottom: 14px; padding-left: 16px; border-left: 2px solid var(--color-primary); position: relative; }
        .timeline-item::before { content: ''; position: absolute; left: -6px; top: 3px; width: 10px; height: 10px; border-radius: 50%; background: var(--color-primary); border: 2px solid #f8fafc; }
        .timeline-date { font-size: 0.65rem; color: #64748b; font-weight: 700; margin-bottom: 3px; font-family: 'IBM Plex Mono', monospace; }
        .timeline-user { font-size: 0.8rem; font-weight: 700; color: #0f172a; margin-bottom: 4px; display: flex; align-items: center; gap: 5px; }
        .timeline-status-badge { font-size: 0.6rem; padding: 2px 6px; border-radius: 4px; background: #e2e8f0; color: #475569; text-transform: uppercase; letter-spacing: 0.04em; }
        .timeline-msg { font-size: 0.75rem; color: #334155; background: #fff; padding: 8px 10px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02); line-height: 1.4; }

        /* FORMULARIOS Y METADATOS COMPACTOS */
        .meta-strip { display: grid; gap: 1px; background: #dce6f0; border: 1px solid #c4d4e3; border-radius: 9px; overflow: hidden; margin-bottom: 16px; flex-shrink: 0; }
        .meta-cell { background: #fff; padding: 8px 10px; display: flex; flex-direction: column; gap: 2px; }
        .form-section .meta-strip { grid-template-columns: repeat(3, 1fr); }
        .modal-body .meta-strip { grid-template-columns: repeat(3, 1fr); }
        .meta-lbl { font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #7a96ae; display: flex; align-items: center; gap: 5px; }
        .meta-val { font-size: 0.8rem; font-weight: 700; color: #0c1e2e; display: flex; align-items: center; gap: 6px; }
        .meta-folio { font-family: 'IBM Plex Mono', monospace; color: var(--color-primary); font-weight: 700; }
        .meta-ticket-num { font-family: 'IBM Plex Mono', monospace; color: var(--color-medium); font-weight: 700; }
        .form-section-divider { height: 1px; background: #dce6f0; margin: 12px 0; border: none; flex-shrink: 0; }

        .form-group { margin-bottom: 12px; flex-shrink: 0; }
        .form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; flex-shrink: 0; }
        .form-group label { display: flex; align-items: center; gap: 5px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-medium); margin-bottom: 6px; }
        .input-wrapper { position: relative; display: flex; align-items: center; }
        .input-wrapper i.field-icon { position: absolute; left: 12px; font-size: 0.75rem; pointer-events: none; transition: 0.2s; color: #8da4b7; }
        .form-control { width: 100%; padding: 8px 12px 8px 30px; background: #fff; border: 1px solid #cbd5e1; border-radius: 7px; font-size: 0.8rem; color: #0f172a; transition: all 0.2s; }

        /* Autoajuste de Textareas */
        textarea.form-control { padding-left: 10px; resize: none; overflow: hidden; min-height: 38px; line-height: 1.4; transition: height 0.1s ease; }
        .no-icon .form-control { padding-left: 10px; }
        .form-control:focus { outline: none; background: #ffffff; border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(52, 73, 85, 0.12); }

        /* CLASES DE COLORES DINÁMICOS PARA SELECTS */
        .form-control.bg-estado-nuevo        { background: var(--status-new-bg) !important; color: var(--status-new-text) !important; border-color: var(--status-new-border) !important; font-weight: 700; -webkit-text-fill-color: var(--status-new-text) !important; opacity: 1 !important; }
        .form-control.bg-estado-abierto      { background: var(--status-open-bg) !important; color: var(--status-open-text) !important; border-color: var(--status-open-border) !important; font-weight: 700; -webkit-text-fill-color: var(--status-open-text) !important; opacity: 1 !important; }
        .form-control.bg-estado-en-espera    { background: var(--status-waiting-bg) !important; color: var(--status-waiting-text) !important; border-color: var(--status-waiting-border) !important; font-weight: 700; -webkit-text-fill-color: var(--status-waiting-text) !important; opacity: 1 !important; }
        .form-control.bg-estado-por-concluir { background: var(--status-pending-bg) !important; color: var(--status-pending-text) !important; border-color: var(--status-pending-border) !important; font-weight: 700; -webkit-text-fill-color: var(--status-pending-text) !important; opacity: 1 !important; }
        .form-control.bg-estado-realizado    { background: var(--status-success-bg) !important; color: var(--status-success-text) !important; border-color: var(--status-success-border) !important; font-weight: 700; -webkit-text-fill-color: var(--status-success-text) !important; opacity: 1 !important; }
        .form-control.bg-estado-cancelado    { background: var(--status-cancelled-bg) !important; color: var(--status-cancelled-text) !important; border-color: var(--status-cancelled-border) !important; font-weight: 700; -webkit-text-fill-color: var(--status-cancelled-text) !important; opacity: 1 !important; }

        .form-control.bg-prio-alta  { background: var(--status-cancelled-bg) !important; color: var(--status-cancelled-text) !important; border-color: var(--status-cancelled-border) !important; font-weight: 700; -webkit-text-fill-color: var(--status-cancelled-text) !important; opacity: 1 !important; }
        .form-control.bg-prio-media { background: var(--status-pending-bg) !important; color: var(--status-pending-text) !important; border-color: var(--status-pending-border) !important; font-weight: 700; -webkit-text-fill-color: var(--status-pending-text) !important; opacity: 1 !important; }
        .form-control.bg-prio-baja  { background: var(--status-success-bg) !important; color: var(--status-success-text) !important; border-color: var(--status-success-border) !important; font-weight: 700; -webkit-text-fill-color: var(--status-success-text) !important; opacity: 1 !important; }

        .form-control[readonly]:not([class*="bg-"]), .form-control:disabled:not([class*="bg-"]) {
            background: #f8fafc; cursor: default; border-color: #e4eef5; color: #475569; opacity: 1; -webkit-text-fill-color: #475569;
        }

        .comment-block { background: #fff9ed; border: 1px solid var(--status-pending-border); border-radius: 8px; padding: 10px 12px; margin-bottom: 12px; display: none; flex-shrink: 0; }
        .comment-block.visible { display: block; animation: fadeIn 0.3s ease; }
        .comment-block label { color: var(--status-pending-text); }
        .comment-block .form-control { border-color: #e5b05c; background: #fffdf5; }
        .comment-block.danger { background: #fff5f5; border-color: var(--status-cancelled-border); }
        .comment-block.danger label { color: var(--status-cancelled-text); }
        .comment-block.danger .form-control { border-color: #fca5a5; background: #fffafa; }

        /* TABLA Y ANIMACIONES */
        .data-section { background: #fff; padding: 20px; border-radius: 14px; border: 1px solid #c4d4e3; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03); min-width: 0; }
        .table-controls { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; align-items: center; }
        .search-box { flex-grow: 1; position: relative; min-width: 200px; }
        .search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ab5c8; font-size: 0.8rem; }
        .search-box input { width: 100%; padding: 8px 10px 8px 32px; border: 1px solid #c4d4e3; border-radius: 7px; background: #fff; font-size: 0.85rem; transition: 0.2s; }
        .search-box input:focus { border-color: var(--color-primary); outline: none; box-shadow: 0 0 0 3px rgba(52, 73, 85, 0.1); }
        select.filter-select { border: 1px solid #c4d4e3; border-radius: 7px; padding: 8px 12px; outline: none; background: #fff; color: #0c1e2e; font-size: 0.85rem; font-weight: 500; transition: 0.2s; }
        select.filter-select:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(52, 73, 85, 0.1); }

        .main-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .main-table th { background: var(--color-soft); color: var(--color-medium); padding: 11px 12px; text-align: left; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid #c4d4e3; }
        .main-table th:first-child { border-top-left-radius: 8px; }
        .main-table th:last-child { border-top-right-radius: 8px; }
        .main-table td { padding: 11px 12px; border-bottom: 1px solid #e4eef5; color: #1a2e40; font-size: 0.85rem; vertical-align: middle; transition: background 0.15s; }
        .main-table tr:hover td { background: #f8fafc; }
        .main-table tr:last-child td { border-bottom: none; }
        .tk-ticket { font-family: 'IBM Plex Mono', monospace; font-size: 0.85rem; font-weight: 600; color: #000; }
        .tk-folio { font-family: 'IBM Plex Mono', monospace; font-size: 0.85rem; font-weight: 700; color: #000; letter-spacing: 0.02em; }

        @keyframes rowFadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
        .main-table tbody tr { animation: rowFadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        /* BADGES */
        .status-tag { display: inline-flex; align-items: center; gap: 5px; padding: 4px 9px; border-radius: 5px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap; }
        .status-tag::before { content: ''; width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
        .nuevo { background: var(--status-new-bg); color: var(--status-new-text); border: 1px solid var(--status-new-border); } .nuevo::before { background: var(--status-new-dot); }
        .abierto { background: var(--status-open-bg); color: var(--status-open-text); border: 1px solid var(--status-open-border); } .abierto::before { background: var(--status-open-dot); }
        .en-espera { background: var(--status-waiting-bg); color: var(--status-waiting-text); border: 1px solid var(--status-waiting-border); } .en-espera::before { background: var(--status-waiting-dot); }
        .por-concluir { background: var(--status-pending-bg); color: var(--status-pending-text); border: 1px solid var(--status-pending-border); } .por-concluir::before { background: var(--status-pending-dot); }
        .realizado { background: var(--status-success-bg); color: var(--status-success-text); border: 1px solid var(--status-success-border); } .realizado::before { background: var(--status-success-dot); }
        .cancelado { background: var(--status-cancelled-bg); color: var(--status-cancelled-text); border: 1px solid var(--status-cancelled-border); } .cancelado::before { background: var(--status-cancelled-dot); }

        .priority-tag { display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
        .priority-alta { background: var(--status-cancelled-bg); color: var(--status-cancelled-text); border: 1px solid var(--status-cancelled-border); }
        .priority-media { background: var(--status-pending-bg); color: var(--status-pending-text); border: 1px solid var(--status-pending-border); }
        .priority-baja { background: var(--status-success-bg); color: var(--status-success-text); border: 1px solid var(--status-success-border); }

        /* BOTONES Y PAGINACIÓN */
        .btn { padding: 8px 16px; border-radius: 7px; cursor: pointer; border: none; font-weight: 600; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s ease; width: 100%; justify-content: center; }
        .btn-primary { background: var(--color-primary); color: white; }
        .btn-primary:hover { background: var(--color-medium); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(52, 73, 85, 0.28); }
        .btn-secondary { background: var(--white); color: #1a2e40; border: 1px solid #c4d4e3; }
        .btn-secondary:hover { background: var(--color-soft); border-color: var(--color-primary); transform: translateY(-1px); }

        .table-actions { display: flex; gap: 4px; justify-content: center; }
        .table-actions button { width: 28px; height: 28px; border-radius: 6px; border: 1px solid transparent; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: 0.2s; font-size: 0.75rem; }
        .btn-view { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
        .btn-attend { background: #ffedd5; color: #c2410c; border-color: #fdba74; }
        .btn-view:hover, .btn-attend:hover { transform: translateY(-2px); filter: brightness(0.92); box-shadow: 0 3px 6px rgba(0, 0, 0, 0.08); }

        .pagination-container { display: flex; justify-content: space-between; align-items: center; margin-top: 16px; padding-top: 16px; border-top: 1px solid #e4eef5; }
        .pagination-info { font-size: 0.8rem; color: #5c7a96; font-weight: 600; }
        .pagination-buttons { display: flex; gap: 4px; }
        .pagination-btn { background: #fff; border: 1px solid #c4d4e3; color: #1a2e40; width: 30px; height: 30px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 600; transition: 0.2s; }
        .pagination-btn:hover:not(:disabled) { background: var(--color-soft); border-color: var(--color-primary); }
        .pagination-btn.active { background: var(--color-primary); color: white; border-color: var(--color-primary); }
        .pagination-btn:disabled { opacity: 0.5; cursor: not-allowed; }
    </style>

    <div class="content active">
        {{-- ESTADÍSTICAS --}}
        <div class="stats-grid">
            <div class="stat-card card-main">
                <div class="stat-info"><span class="stat-label">Total Tickets</span><h2 class="stat-value" id="stat-total">0</h2></div>
                <div class="stat-badge"><i class="fas fa-ticket-alt"></i></div>
            </div>
            <div class="stat-card status-waiting">
                <div class="stat-info"><span class="stat-label">En Espera</span><h2 class="stat-value" id="stat-espera">0</h2></div>
                <div class="stat-badge"><i class="fas fa-clock"></i></div>
            </div>
            <div class="stat-card status-pending">
                <div class="stat-info"><span class="stat-label">Por Concluir</span><h2 class="stat-value" id="stat-por-concluir">0</h2></div>
                <div class="stat-badge"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
            <div class="stat-card status-success">
                <div class="stat-info"><span class="stat-label">Realizados</span><h2 class="stat-value" id="stat-realizado">0</h2></div>
                <div class="stat-badge"><i class="fas fa-check-circle"></i></div>
            </div>
            <div class="stat-card status-cancelled">
                <div class="stat-info"><span class="stat-label">Cancelados</span><h2 class="stat-value" id="stat-cancelado">0</h2></div>
                <div class="stat-badge"><i class="fas fa-ban"></i></div>
            </div>
        </div>

        {{-- LAYOUT PRINCIPAL --}}
        <div class="tickets-layout form-hidden" id="ticketsLayout">

            {{-- PANEL LATERAL --}}
            <div class="form-section">
                <div class="lateral-header">
                    <div class="lateral-header-content">
                        <h3 id="lateral-title"><i class="fas fa-ticket-alt"></i> Visualizador de Ticket</h3>
                        <p class="header-sub" id="lateral-subtitle">
                            <span><i class="fas fa-user"></i> ---</span> <span><i class="fas fa-building"></i> ---</span>
                        </p>
                    </div>
                    <button type="button" class="btn-close-panel" onclick="closeLateralPanel()"><i class="fas fa-times"></i></button>
                </div>

                <form id="lateral-form">
                    @csrf
                    <input type="hidden" id="lat-ticket-id">

                    <div class="form-body">
                        <div id="lateral-loader" class="lateral-loader" style="display: none;">
                            <div class="loader-spinner"></div>
                            <p>Obteniendo información...</p>
                        </div>

                        <div class="meta-strip">
                            <div class="meta-cell">
                                <span class="meta-lbl"><i class="far fa-calendar-alt"></i> Fecha</span>
                                <span class="meta-val"><span id="lat-fecha">--</span></span>
                            </div>
                            <div class="meta-cell">
                                <span class="meta-lbl"><i class="fas fa-ticket-alt"></i> Ticket</span>
                                <span class="meta-val"><span class="meta-ticket-num" id="lat-ticket-num">--</span></span>
                            </div>
                            <div class="meta-cell">
                                <span class="meta-lbl"><i class="fas fa-hashtag"></i> Folio</span>
                                <span class="meta-val meta-folio"><span id="lat-folio">--</span></span>
                            </div>
                        </div>

                        <div class="form-row-2">
                            <div class="form-group">
                                <label><i class="fas fa-tasks"></i> Estado Actual</label>
                                <div class="input-wrapper">
                                    <i class="field-icon fas fa-circle" style="font-size:0.55rem;"></i>
                                    <select id="lat-estado" class="form-control" disabled>
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
                                <div class="input-wrapper">
                                    <i class="field-icon fas fa-flag"></i>
                                    {{-- Bloqueada por default --}}
                                    <select id="lat-prioridad" class="form-control" onchange="updateSelectColor(this, 'prioridad')" disabled>
                                        <option value="alta">Alta</option>
                                        <option value="media">Media</option>
                                        <option value="baja">Baja</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr class="form-section-divider" style="margin-top:0;">

                        <div class="form-group">
                            <label><i class="fas fa-heading"></i> Asunto Breve</label>
                            <div class="input-wrapper">
                                <i class="field-icon fas fa-heading"></i>
                                <input type="text" id="lat-asunto" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group no-icon">
                            <label><i class="fas fa-align-left"></i> Descripción Detallada</label>
                            <textarea id="lat-descripcion" class="form-control" rows="1" required></textarea>
                        </div>

                        <div class="comment-block" id="lat-comentario-block">
                            <div class="form-group no-icon" style="margin-bottom:0;">
                                <label><i class="fas fa-exclamation-circle"></i> <span id="lat-comentario-label">Justificación Requerida *</span></label>
                                <textarea id="lat-comentario" class="form-control" rows="1" placeholder="Agregue la justificación obligatoria..."></textarea>
                            </div>
                        </div>

                        <div class="timeline-container" id="lat-timeline-container" style="display: none;">
                            <h4 class="timeline-title"><i class="fas fa-history"></i> Historial de Gestiones</h4>
                            <div id="lat-timeline-content"></div>
                        </div>
                    </div>

                    <div class="lateral-footer" id="lat-footer">
                        <span class="status-selector-label"><i class="fas fa-random"></i> Siguientes pasos (Modificar Estado):</span>
                        <div class="status-bar-container" id="lat-status-buttons">
                            {{-- BOTONES NUEVO Y ABIERTO ELIMINADOS DE AQUÍ --}}
                            <button type="button" class="status-btn" data-val="en-espera" onclick="selectStatusBtn('en-espera')">En Espera</button>
                            <button type="button" class="status-btn" data-val="por-concluir" onclick="selectStatusBtn('por-concluir')">Por Concluir</button>
                            <button type="button" class="status-btn" data-val="realizado" onclick="selectStatusBtn('realizado')">Realizado</button>
                            <button type="button" class="status-btn" data-val="cancelado" onclick="selectStatusBtn('cancelado')">Cancelado</button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="btn-save-management"><i class="fas fa-save"></i> Guardar Gestión Completa</button>
                    </div>
                </form>
            </div>

            {{-- TABLA DE DATOS --}}
            <section class="data-section">
                <div class="table-controls">
                    <button class="btn btn-primary" onclick="openNewTicketModal()" style="width:auto;"><i class="fas fa-plus"></i> Nuevo Ticket</button>

                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="buscador" placeholder="Buscar folio, No. ticket, usuario o asunto...">
                    </div>

                    <select id="mostrar-registros" class="filter-select">
                        <option value="7" selected>Mostrar: 7</option>
                        <option value="15">Mostrar: 15</option>
                        <option value="30">Mostrar: 30</option>
                        <option value="todos">Mostrar: Todos</option>
                    </select>

                    <select id="filtro-estado" class="filter-select">
                        <option value="">Estado: Todos</option>
                        <option value="nuevo">Nuevo</option>
                        <option value="abierto">Abierto</option>
                        <option value="en-espera">En Espera</option>
                        <option value="por-concluir">Por Concluir</option>
                        <option value="realizado">Realizado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>

                    <select id="filtro-prioridad" class="filter-select">
                        <option value="">Prioridad: Todas</option>
                        <option value="alta">Alta</option>
                        <option value="media">Media</option>
                        <option value="baja">Baja</option>
                    </select>

                    <button class="btn btn-secondary" onclick="exportarTabla()" style="width:auto;"><i class="fas fa-file-export"></i> Exportar</button>
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
                        <tbody id="tickets-body">
                            <!-- Filas dinámicas -->
                        </tbody>
                    </table>
                </div>

                {{-- Controles Paginación --}}
                <div class="pagination-container" id="pagination-controls" style="display: none;">
                    <div class="pagination-info" id="pagination-info">Mostrando 0 a 0 de 0</div>
                    <div class="pagination-buttons" id="pagination-buttons"></div>
                </div>
            </section>
        </div>
    </div>

    {{-- MODAL FLOTANTE — NUEVO TICKET --}}
    <div class="modal-overlay" id="modalNewTicket">
        <div class="modal-container">
            <div class="panel-header" style="border-radius:19px 19px 0 0;">
                <div>
                    <h3><i class="fas fa-plus-circle"></i> Apertura de Nuevo Ticket</h3>
                    <p class="header-sub">Complete los datos para registrar la incidencia</p>
                </div>
                <button class="btn-close-panel" onclick="closeNewTicketModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="meta-strip">
                    <div class="meta-cell">
                        <span class="meta-lbl"><i class="far fa-calendar-alt"></i> Fecha Emisión</span>
                        <span class="meta-val"><span id="current-date-display">Cargando...</span></span>
                    </div>
                    <div class="meta-cell">
                        <span class="meta-lbl"><i class="fas fa-ticket-alt"></i> No. de Ticket</span>
                        <span class="meta-val"><span class="meta-ticket-num">TK-AUTO</span></span>
                    </div>
                    <div class="meta-cell">
                        <span class="meta-lbl"><i class="fas fa-hashtag"></i> Folio del Área</span>
                        <span class="meta-val meta-folio">TK-AUTO</span>
                    </div>
                </div>

                <form id="new-ticket-form">
                    @csrf
                    <div class="form-row-2">
                        <div class="form-group">
                            <label><i class="fas fa-building"></i> Área del Solicitante *</label>
                            <div class="input-wrapper">
                                <i class="field-icon fas fa-building"></i>
                                <input type="text" class="form-control" value="{{ auth()->user()->employee->area->name ?? 'Área No Asignada' }}" disabled>
                                <input type="hidden" id="new-area" value="{{ auth()->user()->employee->area->code ?? 'SIS' }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Solicitante *</label>
                            <div class="input-wrapper">
                                <i class="field-icon fas fa-user"></i>
                                <input type="text" class="form-control" value="{{ auth()->user()->name ?? 'Usuario Actual' }}" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label><i class="fas fa-tasks"></i> Estado Asignado</label>
                            <div class="input-wrapper">
                                <i class="field-icon fas fa-circle" style="font-size:0.55rem;"></i>
                                <select id="new-estado" class="form-control" disabled>
                                    <option value="nuevo" selected>1. Nuevo</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-flag"></i> Nivel de Prioridad *</label>
                            <div class="input-wrapper">
                                <i class="field-icon fas fa-flag"></i>
                                <select id="new-prioridad" class="form-control" onchange="updateSelectColor(this, 'prioridad')" required>
                                    <option value="alta">Alta — Impacto crítico</option>
                                    <option value="media" selected>Media — Interrupción localizada</option>
                                    <option value="baja">Baja — Solicitud de rutina</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr class="form-section-divider">

                    <div class="form-group">
                        <label><i class="fas fa-heading"></i> Asunto Breve *</label>
                        <div class="input-wrapper">
                            <i class="field-icon fas fa-heading"></i>
                            <input type="text" id="new-asunto" class="form-control" placeholder="Ej: Falla en el acceso al servidor..." required>
                        </div>
                    </div>

                    <div class="form-group no-icon">
                        <label><i class="fas fa-align-left"></i> Descripción Detallada *</label>
                        <textarea id="new-descripcion" class="form-control auto-expand" rows="1" placeholder="Proporcione detalles de la falla..." required></textarea>
                    </div>

                    <div class="form-actions" style="border-top:none; padding-top:8px;">
                        <button type="button" class="btn btn-secondary" onclick="closeNewTicketModal()"><i class="fas fa-times"></i> Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btn-submit-new"><i class="fas fa-paper-plane"></i> Generar Ticket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]').value;
        const fetchConfig = { headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } };

        let allTickets = [];
        let filteredTickets = [];
        let currentPage = 1;

        const statusColors = {
            'nuevo': 'var(--status-new-text)', 'abierto': 'var(--status-open-text)', 'en-espera': 'var(--status-waiting-text)',
            'por-concluir': 'var(--status-pending-text)', 'realizado': 'var(--status-success-text)', 'cancelado': 'var(--status-cancelled-text)'
        };
        const priorityColors = { 'alta': 'var(--status-cancelled-text)', 'media': 'var(--status-pending-text)', 'baja': 'var(--status-success-text)' };

        document.addEventListener('DOMContentLoaded', () => {
            const elDate = document.getElementById('current-date-display');
            if (elDate) elDate.innerText = new Date().toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
            loadTickets();
            setupAutoResize();
        });

        // Evento para textareas auto-expandibles
        function setupAutoResize() {
            const textareas = document.querySelectorAll('textarea');
            textareas.forEach(ta => {
                ta.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            });
        }

        async function loadTickets() {
            try {
                const response = await fetch('{{ route('systems.tickets.get') }}', fetchConfig);
                if (!response.ok) throw new Error('Error al cargar tickets');
                allTickets = await response.json();
                filtrar(); // Inicializa filteredTickets y renderiza
                updateStats(allTickets);
            } catch (error) { console.error(error); Swal.fire('Error', 'No se pudieron cargar los tickets.', 'error'); }
        }

        // ====== LÓGICA DE PAGINACIÓN ======
        function renderTable() {
            const tbody = document.getElementById('tickets-body');
            tbody.innerHTML = '';
            const valMostrar = document.getElementById('mostrar-registros').value;
            const rowsPerPage = valMostrar === 'todos' ? filteredTickets.length : parseInt(valMostrar);

            if (filteredTickets.length === 0) {
                tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding: 20px;">No hay tickets registrados que coincidan con la búsqueda.</td></tr>`;
                document.getElementById('pagination-controls').style.display = 'none';
                return;
            }

            const totalPages = Math.ceil(filteredTickets.length / rowsPerPage) || 1;
            if (currentPage > totalPages) currentPage = totalPages;

            const startIdx = (currentPage - 1) * rowsPerPage;
            const endIdx = startIdx + rowsPerPage;
            const paginatedItems = filteredTickets.slice(startIdx, endIdx);

            paginatedItems.forEach((ticket, index) => {
                const tr = document.createElement('tr');
                tr.style.animationDelay = `${index * 0.04}s`;
                const areaName = ticket.department_name;

                tr.innerHTML = `
                <td><span class="tk-ticket">${ticket.display_id}</span></td>
                <td><span class="tk-folio">${ticket.folio}</span></td>
                <td>${ticket.created_at}</td>
                <td>${ticket.subject}</td>
                <td>${ticket.user_name}</td>
                <td>${areaName}</td>
                <td><span class="priority-tag priority-${ticket.priority.toLowerCase()}">${capitalize(ticket.priority)}</span></td>
                <td><span class="status-tag ${ticket.status}">${formatStatus(ticket.status)}</span></td>
                <td class="table-actions">
                    <button class="btn-view" title="Ver Detalles" onclick="openLateralPanel('ver', ${ticket.id})"><i class="fas fa-eye"></i></button>
                    <button class="btn-attend" title="Atender Ticket" onclick="openLateralPanel('atender', ${ticket.id})"><i class="fas fa-tools"></i></button>
                </td>`;
                tbody.appendChild(tr);
            });

            renderPaginationControls(totalPages, startIdx, endIdx, filteredTickets.length);
        }

        function renderPaginationControls(totalPages, startIdx, endIdx, totalItems) {
            const container = document.getElementById('pagination-controls');
            if (totalPages <= 1) { container.style.display = 'none'; return; }

            container.style.display = 'flex';

            const showingEnd = Math.min(endIdx, totalItems);
            document.getElementById('pagination-info').innerText = `Mostrando ${startIdx + 1} a ${showingEnd} de ${totalItems} registros`;

            const btnContainer = document.getElementById('pagination-buttons');
            btnContainer.innerHTML = '';

            const btnPrev = document.createElement('button');
            btnPrev.className = 'pagination-btn';
            btnPrev.innerHTML = '<i class="fas fa-chevron-left"></i>';
            btnPrev.disabled = currentPage === 1;
            btnPrev.onclick = () => { currentPage--; renderTable(); };
            btnContainer.appendChild(btnPrev);

            for (let i = 1; i <= totalPages; i++) {
                if(totalPages > 7 && (i > 2 && i < totalPages - 1 && Math.abs(i - currentPage) > 1)) {
                    if (btnContainer.lastChild.innerText !== '...') {
                        const dots = document.createElement('span');
                        dots.style.padding = '5px'; dots.innerText = '...';
                        btnContainer.appendChild(dots);
                    }
                    continue;
                }
                const btn = document.createElement('button');
                btn.className = `pagination-btn ${i === currentPage ? 'active' : ''}`;
                btn.innerText = i;
                btn.onclick = () => { currentPage = i; renderTable(); };
                btnContainer.appendChild(btn);
            }

            const btnNext = document.createElement('button');
            btnNext.className = 'pagination-btn';
            btnNext.innerHTML = '<i class="fas fa-chevron-right"></i>';
            btnNext.disabled = currentPage === totalPages;
            btnNext.onclick = () => { currentPage++; renderTable(); };
            btnContainer.appendChild(btnNext);
        }

        // Filtrado que alimenta al paginador
        document.getElementById('buscador').addEventListener('keyup', filtrar);
        document.getElementById('filtro-estado').addEventListener('change', filtrar);
        document.getElementById('filtro-prioridad').addEventListener('change', filtrar);
        document.getElementById('mostrar-registros').addEventListener('change', () => { currentPage = 1; renderTable(); });

        function filtrar() {
            const q = document.getElementById('buscador').value.toLowerCase();
            const est = document.getElementById('filtro-estado').value.toLowerCase();
            const pri = document.getElementById('filtro-prioridad').value.toLowerCase();

            filteredTickets = allTickets.filter(t => {
                let ok = true;
                if (q && !`${t.display_id} ${t.folio} ${t.subject} ${t.user_name}`.toLowerCase().includes(q)) ok = false;
                if (ok && est && t.status !== est) ok = false;
                if (ok && pri && t.priority !== pri) ok = false;
                return ok;
            });
            currentPage = 1;
            renderTable();
        }

        // ==========================================

        function updateStats(data) {
            document.getElementById('stat-total').innerText = data.length;
            document.getElementById('stat-espera').innerText = data.filter(t => t.status === 'en-espera').length;
            document.getElementById('stat-por-concluir').innerText = data.filter(t => t.status === 'por-concluir').length;
            document.getElementById('stat-realizado').innerText = data.filter(t => t.status === 'realizado').length;
            document.getElementById('stat-cancelado').innerText = data.filter(t => t.status === 'cancelado').length;
        }

        function updateSelectColor(selectElement, type) {
            selectElement.className = selectElement.className.replace(/bg-(estado|prio)-[a-z\-]+/g, '');
            const val = selectElement.value;
            if(type === 'estado') selectElement.classList.add(`bg-estado-${val}`);
            else if (type === 'prioridad') selectElement.classList.add(`bg-prio-${val}`);

            const wrapper = selectElement.closest('.input-wrapper');
            if (wrapper) {
                const icon = wrapper.querySelector('.field-icon');
                if (icon) icon.style.color = type === 'estado' ? statusColors[val] : priorityColors[val];
            }
        }

        function selectStatusBtn(val) {
            const selectEstado = document.getElementById('lat-estado');
            selectEstado.value = val;
            updateSelectColor(selectEstado, 'estado');
            checkLateralStatus(val);

            document.querySelectorAll('.status-btn').forEach(btn => btn.classList.remove('active'));
            const btn = document.querySelector(`.status-btn[data-val="${val}"]`);
            if(btn) btn.classList.add('active');
        }

        // ================= MODALES =================

        function openNewTicketModal() {
            closeLateralPanel();
            document.getElementById('modalNewTicket').classList.add('active');
            const estSelect = document.getElementById('new-estado');
            estSelect.value = 'nuevo'; updateSelectColor(estSelect, 'estado');
            const prioSelect = document.getElementById('new-prioridad');
            prioSelect.value = 'media'; updateSelectColor(prioSelect, 'prioridad');
        }

        function closeNewTicketModal() {
            document.getElementById('modalNewTicket').classList.remove('active');
            document.getElementById('new-ticket-form').reset();
            document.querySelectorAll('textarea').forEach(ta => ta.style.height = 'auto');
        }

        document.getElementById('new-ticket-form').addEventListener('submit', async e => {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-new');
            const payload = {
                area_code: document.getElementById('new-area').value,
                subject: document.getElementById('new-asunto').value,
                description: document.getElementById('new-descripcion').value,
                priority: document.getElementById('new-prioridad').value
            };

            try {
                btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
                const response = await fetch('{{ route('systems.tickets.store') }}', { method: 'POST', ...fetchConfig, body: JSON.stringify(payload) });
                const result = await response.json();
                if (response.ok && result.success) {
                    Swal.fire({ title: '¡Ticket Generado!', text: `Folio asignado: ${result.folio}.`, icon: 'success', confirmButtonColor: '#344955' })
                        .then(() => { closeNewTicketModal(); loadTickets(); });
                } else throw new Error(result.message);
            } catch (error) { Swal.fire('Error', error.message, 'error'); }
            finally { btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i> Generar Ticket'; }
        });

        async function openLateralPanel(action, id) {
            const layout = document.getElementById('ticketsLayout');
            const loader = document.getElementById('lateral-loader');
            const title = document.getElementById('lateral-title');
            const subtitle = document.getElementById('lateral-subtitle');
            const inputs = document.querySelectorAll('#lateral-form .form-control');
            const footerBar = document.getElementById('lat-footer');

            loader.style.display = 'flex';
            layout.classList.remove('form-hidden');
            title.innerHTML = action === 'ver' ? `<i class="fas fa-eye"></i> Detalles del Ticket` : `<i class="fas fa-tools"></i> Atendiendo Ticket`;

            try {
                const response = await fetch(`{{ url('/systems/tickets/show') }}/${id}`, fetchConfig);
                const data = await response.json();
                if (!data.success) throw new Error('Error al cargar detalles.');

                let d = data.ticket;

                // ===== LÓGICA DE AUTO-APERTURA =====
                if (action === 'atender' && d.status === 'nuevo') {
                    const payload = {
                        status: 'abierto', priority: d.priority, subject: d.subject, description: d.description,
                        comentario: 'El ticket ha sido tomado para su atención.' // Queda grabado en la bitácora
                    };

                    const updateRes = await fetch(`{{ url('/systems/tickets/update-status') }}/${id}`, {
                        method: 'PUT', ...fetchConfig, body: JSON.stringify(payload)
                    });

                    if (updateRes.ok) {
                        d.status = 'abierto'; // Forzamos el estado a abierto visualmente
                        loadTickets(); // Refrescamos la tabla por detrás

                        // Añadimos visualmente el track para no tener que hacer otro fetch
                        if (!d.trackings) d.trackings = [];
                        d.trackings.push({
                            created_at: new Date().toISOString(),
                            user: { name: '{{ auth()->user()->name ?? "Sistema" }}' },
                            status_after: 'abierto',
                            message: 'El ticket ha sido tomado para su atención.'
                        });
                    }
                }
                // ===================================

                const areaName = d.department_name;

                document.getElementById('lat-ticket-id').value = d.id;
                document.getElementById('lat-folio').innerText = d.folio;
                document.getElementById('lat-ticket-num').innerText = d.display_id;
                document.getElementById('lat-fecha').innerText = d.created_at;

                const estadoSelect = document.getElementById('lat-estado');
                estadoSelect.value = d.status; updateSelectColor(estadoSelect, 'estado');
                const prioridadSelect = document.getElementById('lat-prioridad');
                prioridadSelect.value = d.priority; updateSelectColor(prioridadSelect, 'prioridad');

                document.getElementById('lat-asunto').value = d.subject;

                const descTextarea = document.getElementById('lat-descripcion');
                descTextarea.value = d.description;
                setTimeout(() => { descTextarea.style.height = 'auto'; descTextarea.style.height = descTextarea.scrollHeight + 'px'; }, 50);

                subtitle.innerHTML = `<span><i class="fas fa-user"></i> ${d.user_name}</span> <span><i class="fas fa-building"></i> ${areaName}</span>`;

                document.getElementById('lat-comentario-block').classList.remove('visible', 'danger');
                document.getElementById('lat-comentario').value = '';
                document.getElementById('lat-comentario').style.height = 'auto';
                renderTimeline(d.trackings);

                // Permisos de inputs (Prioridad e inputs de estado bloqueados siempre)
                inputs.forEach(i => {
                    if (action === 'ver') {
                        if (i.tagName === 'SELECT') i.disabled = true;
                        else i.readOnly = true;
                    } else {
                        if (i.id === 'lat-prioridad' || i.id === 'lat-estado') { i.disabled = true; }
                        else { i.readOnly = false; }
                    }
                });

                // LÓGICA DE VISIBILIDAD DE BOTONES INFERIORES
                const btns = document.querySelectorAll('.status-btn');
                btns.forEach(b => {
                    b.style.display = 'inline-block'; // Reset todos
                    b.classList.remove('active'); // Quitar activos previos
                });

                // Si la acción es VER o el ticket ya está CERRADO, se esconde la barra inferior entera
                if (action === 'ver' || d.status === 'realizado' || d.status === 'cancelado') {
                    footerBar.style.display = 'none';
                } else {
                    footerBar.style.display = 'block';

                    // Si el estado actual es 'por-concluir', SOLO permite Realizado o Cancelado
                    if (d.status === 'por-concluir') {
                        btns.forEach(b => {
                            const val = b.getAttribute('data-val');
                            if(!['realizado', 'cancelado'].includes(val)) {
                                b.style.display = 'none';
                            }
                        });
                    }

                    // Pre-selecciona el botón si corresponde al estado actual (ej. si estaba En Espera)
                    const currentBtn = document.querySelector(`.status-btn[data-val="${d.status}"]`);
                    if (currentBtn) currentBtn.classList.add('active');
                }

                setTimeout(() => {
                    loader.style.opacity = '0';
                    setTimeout(() => { loader.style.display = 'none'; loader.style.opacity = '1'; }, 300);
                }, 400);

            } catch (error) { Swal.fire('Error', error.message, 'error'); closeLateralPanel(); }
        }

        function renderTimeline(trackings) {
            const container = document.getElementById('lat-timeline-container');
            const content = document.getElementById('lat-timeline-content');
            if (!trackings || trackings.length === 0) { container.style.display = 'none'; return; }

            container.style.display = 'block';
            content.innerHTML = '';

            trackings.forEach(t => {
                if (t.message && t.message.trim() !== '') {
                    const date = new Date(t.created_at || Date.now()).toLocaleString('es-ES', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
                    const userName = t.user ? t.user.name : 'Sistema';
                    content.innerHTML += `
                    <div class="timeline-item">
                        <div class="timeline-date">${date}</div>
                        <div class="timeline-user">${userName} <span class="timeline-status-badge">${formatStatus(t.status_after)}</span></div>
                        <div class="timeline-msg">${t.message}</div>
                    </div>`;
                }
            });
        }

        function closeLateralPanel() {
            document.getElementById('ticketsLayout').classList.add('form-hidden');
            document.getElementById('lateral-form').reset();
            document.getElementById('lat-comentario-block').classList.remove('visible', 'danger');
            document.getElementById('lat-timeline-container').style.display = 'none';
            document.getElementById('lat-estado').className = 'form-control';
            document.getElementById('lat-prioridad').className = 'form-control';
            document.querySelectorAll('textarea').forEach(ta => ta.style.height = 'auto');
        }

        function checkLateralStatus(val) {
            const block = document.getElementById('lat-comentario-block');
            const label = document.getElementById('lat-comentario-label');
            const field = document.getElementById('lat-comentario');

            if (val === 'por-concluir') {
                block.classList.add('visible'); block.classList.remove('danger');
                label.textContent = 'Justificación técnica de "Por Concluir" *';
                field.placeholder = 'Detalle qué impide el cierre (ej. espera de refacción)...';
            } else if (val === 'cancelado') {
                block.classList.add('visible', 'danger');
                label.textContent = 'Justificación de cancelación *';
                field.placeholder = 'Explique el motivo de la cancelación...';
            } else {
                block.classList.remove('visible', 'danger');
                field.value = '';
                field.style.height = 'auto';
            }
        }

        document.getElementById('lateral-form').addEventListener('submit', async e => {
            e.preventDefault();
            const id = document.getElementById('lat-ticket-id').value;
            const estado = document.getElementById('lat-estado').value;
            const comentario = document.getElementById('lat-comentario').value;

            if ((estado === 'por-concluir' || estado === 'cancelado') && !comentario.trim()) {
                Swal.fire({ title: 'Justificación obligatoria', text: 'Debe agregar un comentario para respaldar este estatus.', icon: 'warning', confirmButtonColor: '#344955' });
                return;
            }

            const payload = {
                status: estado,
                priority: document.getElementById('lat-prioridad').value, // Lo mandamos aunque esté bloqueado
                subject: document.getElementById('lat-asunto').value,
                description: document.getElementById('lat-descripcion').value,
                comentario: comentario
            };

            const btn = document.getElementById('btn-save-management');

            try {
                btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                const response = await fetch(`{{ url('/systems/tickets/update-status') }}/${id}`, { method: 'PUT', ...fetchConfig, body: JSON.stringify(payload) });
                const result = await response.json();

                if (response.ok && result.success) {
                    Swal.fire({ title: '¡Gestión guardada!', text: result.message, icon: 'success', confirmButtonColor: '#344955' })
                        .then(() => { closeLateralPanel(); loadTickets(); });
                } else throw new Error(result.message);
            } catch (error) { Swal.fire('Error', error.message, 'error'); }
            finally { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Guardar Gestión Completa'; }
        });

        function capitalize(str) { return str.charAt(0).toUpperCase() + str.slice(1); }
        function formatStatus(str) { return str.split('-').map(capitalize).join(' '); }
    </script>
@endpush
