@extends('modules.systems.tickets.index')

@section('content')
<style>
    /* ─── FUENTE MONO ─────────────────────────────────────────────────── */
    @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@500;700&display=swap');

    /* ─── TOKENS DE COLOR POR ESTADO ─────────────────────────────────── */
    :root {
        --s-new-text:    #0c2d5e; --s-new-bg:    #d4e4f7; --s-new-border:    #2e6db4; --s-new-dot:    #1a5fb4;
        --s-open-text:   #0b4a52; --s-open-bg:   #c8edf3; --s-open-border:   #1a8fa3; --s-open-dot:   #0e7a8c;
        --s-wait-text:   #7a2c00; --s-wait-bg:   #fddbc7; --s-wait-border:   #c15a00; --s-wait-dot:   #c15a00;
        --s-pend-text:   #6b3b00; --s-pend-bg:   #fde8ab; --s-pend-border:   #c47d00; --s-pend-dot:   #c47d00;
        --s-done-text:   #0d4a1e; --s-done-bg:   #bbf0cc; --s-done-border:   #1a8c38; --s-done-dot:   #15722d;
        --s-cancel-text: #6b0c0c; --s-cancel-bg: #fcc8c8; --s-cancel-border: #b91c1c; --s-cancel-dot: #9b1111;
    }

    /* ─── BASE ────────────────────────────────────────────────────────── */
    .content.active { background: #f0f4f8; }

    /* ─── LAYOUT PRINCIPAL ────────────────────────────────────────────── */
    .tickets-layout {
        display: grid;
        grid-template-columns: 440px 1fr;
        gap: 20px;
        align-items: start;
        transition: grid-template-columns .55s cubic-bezier(.16,1,.3,1), gap .55s ease;
    }
    .tickets-layout.panel-off { grid-template-columns: 0 1fr; gap: 0; }

    /* ─── ESTADÍSTICAS ────────────────────────────────────────────────── */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(5,1fr);
        gap: 14px;
        padding: 4px 1rem 16px;
    }
    .stat-card {
        background: #fff;
        border: 1px solid #c8d5e3;
        border-radius: 14px;
        padding: 18px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        min-height: 90px;
        box-shadow: 0 2px 8px rgba(0,0,0,.04);
        transition: transform .25s, box-shadow .25s;
    }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 18px rgba(0,0,0,.07); }
    .stat-card.c-main { background: linear-gradient(140deg, var(--color-primary) 0%, #263840 100%); border: none; color: #fff; }

    .stat-info { display: flex; flex-direction: column; gap: 4px; }
    .stat-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #5c7a96; }
    .c-main .stat-label { color: rgba(255,255,255,.75); }
    .stat-value { font-size: 1.85rem; font-weight: 800; color: #0c1e2e; line-height: 1; }
    .c-main .stat-value { color: #fff; }

    .stat-icon {
        width: 42px; height: 42px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;
    }
    .c-main .stat-icon { background: rgba(255,255,255,.18); color: #fff; border-radius: 50%; }
    .c-wait .stat-icon   { background: var(--s-wait-bg);   color: var(--s-wait-text);   border: 1.5px solid var(--s-wait-border); }
    .c-pend .stat-icon   { background: var(--s-pend-bg);   color: var(--s-pend-text);   border: 1.5px solid var(--s-pend-border); }
    .c-done .stat-icon   { background: var(--s-done-bg);   color: var(--s-done-text);   border: 1.5px solid var(--s-done-border); }
    .c-cancel .stat-icon { background: var(--s-cancel-bg); color: var(--s-cancel-text); border: 1.5px solid var(--s-cancel-border); }

    /* ─── PANEL LATERAL ───────────────────────────────────────────────── */
    .side-panel {
        background: #f8fafc;
        border: 1px solid #c4d4e3;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(15,30,50,.07);
        position: sticky;
        top: 20px;
        max-height: calc(100vh - 40px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transition: all .55s cubic-bezier(.16,1,.3,1);
        transform-origin: left top;
    }
    .panel-off .side-panel {
        opacity: 0;
        transform: translateX(-120%) scale(.95);
        min-width: 0; width: 0; padding: 0; border: none; visibility: hidden;
    }

    /* Header del panel */
    .panel-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 14px 16px;
        background: linear-gradient(135deg, var(--color-primary) 0%, #263840 100%);
        color: #fff;
        border-radius: 13px 13px 0 0;
        flex-shrink: 0;
        gap: 12px;
    }
    .panel-head h3 { margin: 0; font-size: .95rem; font-weight: 700; display: flex; align-items: flex-start; gap: 8px; line-height: 1.3; }
    .panel-head .sub { font-size: .75rem; font-weight: 600; color: rgba(255,255,255,.85); margin-top: 4px; display: flex; align-items: center; gap: 6px; }

    /* Cuerpo con scroll */
    .panel-body {
        padding: 16px;
        flex-grow: 1;
        overflow-y: auto;
        min-height: 0;
        position: relative;
    }
    .panel-body::-webkit-scrollbar { width: 6px; }
    .panel-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .panel-body::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    /* Footer fijo */
    .panel-foot {
        flex-shrink: 0;
        background: #fff;
        border-top: 1px solid #c4d4e3;
        padding: 12px 16px;
        border-radius: 0 0 13px 13px;
        box-shadow: 0 -4px 12px rgba(0,0,0,.03);
        z-index: 10;
    }

    /* ─── LOADER ──────────────────────────────────────────────────────── */
    .panel-loader {
        position: absolute; inset: 0;
        background: rgba(248,250,252,.85);
        backdrop-filter: blur(2px);
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        z-index: 50;
    }
    .loader-spin {
        width: 40px; height: 40px;
        border: 3px solid #dce6f0;
        border-top-color: var(--color-primary);
        border-radius: 50%;
        animation: spin .8s cubic-bezier(.4,0,.2,1) infinite;
        margin-bottom: 12px;
    }
    .panel-loader p { font-size: .8rem; font-weight: 600; color: #5c7a96; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ─── META STRIP ──────────────────────────────────────────────────── */
    .meta-strip {
        display: grid;
        grid-template-columns: repeat(3,1fr);
        gap: 1px;
        background: #dce6f0;
        border: 1px solid #c4d4e3;
        border-radius: 9px;
        overflow: hidden;
        margin-bottom: 16px;
    }
    .meta-cell { background: #fff; padding: 8px 10px; display: flex; flex-direction: column; gap: 2px; }
    .meta-lbl { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #7a96ae; display: flex; align-items: center; gap: 5px; }
    .meta-val { font-size: .8rem; font-weight: 700; color: #0c1e2e; display: flex; align-items: center; gap: 6px; }
    .mono { font-family: 'IBM Plex Mono', monospace; }
    .mono-folio { color: var(--color-primary); }
    .mono-num   { color: var(--color-medium); }

    /* ─── CAMPOS DE FORMULARIO ────────────────────────────────────────── */
    .form-group { margin-bottom: 12px; }
    .form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .form-divider { height: 1px; background: #dce6f0; margin: 12px 0; border: none; }

    .form-group label {
        display: flex; align-items: center; gap: 5px;
        font-size: .7rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .04em; color: var(--color-medium); margin-bottom: 6px;
    }
    .field-wrap { position: relative; display: flex; align-items: center; }
    .field-icon { position: absolute; left: 12px; font-size: .75rem; pointer-events: none; color: #8da4b7; transition: color .2s; }

    .ctrl {
        width: 100%;
        padding: 8px 12px 8px 30px;
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        font-size: .8rem;
        color: #0f172a;
        transition: border-color .2s, box-shadow .2s;
    }
    .ctrl.no-icon  { padding-left: 10px; }
    .ctrl:focus    { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(52,73,85,.12); }
    textarea.ctrl  { resize: none; overflow: hidden; min-height: 38px; line-height: 1.4; }
    .ctrl[readonly], .ctrl:disabled { background: #f8fafc; cursor: default; border-color: #e4eef5; color: #475569; opacity: 1; -webkit-text-fill-color: #475569; }

    /* Colores dinámicos por estado / prioridad */
    .ctrl.bg-s-nuevo        { background: var(--s-new-bg)    !important; color: var(--s-new-text)    !important; border-color: var(--s-new-border)    !important; font-weight:700; -webkit-text-fill-color:var(--s-new-text)    !important; }
    .ctrl.bg-s-abierto      { background: var(--s-open-bg)   !important; color: var(--s-open-text)   !important; border-color: var(--s-open-border)   !important; font-weight:700; -webkit-text-fill-color:var(--s-open-text)   !important; }
    .ctrl.bg-s-en-espera    { background: var(--s-wait-bg)   !important; color: var(--s-wait-text)   !important; border-color: var(--s-wait-border)   !important; font-weight:700; -webkit-text-fill-color:var(--s-wait-text)   !important; }
    .ctrl.bg-s-por-concluir { background: var(--s-pend-bg)   !important; color: var(--s-pend-text)   !important; border-color: var(--s-pend-border)   !important; font-weight:700; -webkit-text-fill-color:var(--s-pend-text)   !important; }
    .ctrl.bg-s-realizado    { background: var(--s-done-bg)   !important; color: var(--s-done-text)   !important; border-color: var(--s-done-border)   !important; font-weight:700; -webkit-text-fill-color:var(--s-done-text)   !important; }
    .ctrl.bg-s-cancelado    { background: var(--s-cancel-bg) !important; color: var(--s-cancel-text) !important; border-color: var(--s-cancel-border) !important; font-weight:700; -webkit-text-fill-color:var(--s-cancel-text) !important; }
    .ctrl.bg-p-alta  { background: var(--s-cancel-bg) !important; color: var(--s-cancel-text) !important; border-color: var(--s-cancel-border) !important; font-weight:700; -webkit-text-fill-color:var(--s-cancel-text) !important; }
    .ctrl.bg-p-media { background: var(--s-pend-bg)   !important; color: var(--s-pend-text)   !important; border-color: var(--s-pend-border)   !important; font-weight:700; -webkit-text-fill-color:var(--s-pend-text)   !important; }
    .ctrl.bg-p-baja  { background: var(--s-done-bg)   !important; color: var(--s-done-text)   !important; border-color: var(--s-done-border)   !important; font-weight:700; -webkit-text-fill-color:var(--s-done-text)   !important; }

    /* ─── BLOQUE DE COMENTARIO ────────────────────────────────────────── */
    .comment-block {
        background: #fff9ed;
        border: 1px solid var(--s-pend-border);
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 12px;
        display: none;
    }
    .comment-block.show { display: block; animation: fadeIn .3s ease; }
    .comment-block.danger { background: #fff5f5; border-color: var(--s-cancel-border); }
    .comment-block.danger label { color: var(--s-cancel-text); }
    .comment-block.danger .ctrl { border-color: #fca5a5; background: #fffafa; }
    @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }

    /* ─── BOTONERA DE ESTADOS ─────────────────────────────────────────── */
    .status-bar-label { font-size: .65rem; font-weight: 700; color: #5c7a96; text-transform: uppercase; margin-bottom: 8px; display: block; }
    .status-bar { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
    .status-btn {
        flex: 1; min-width: 70px; padding: 6px;
        font-size: .65rem; font-weight: 700; text-transform: uppercase;
        border-radius: 6px; border: 1px solid #cbd5e1;
        background: #f1f5f9; color: #64748b; cursor: pointer; transition: all .2s;
    }
    .status-btn:hover { background: #e2e8f0; color: #334155; }
    .status-btn.on[data-v="en-espera"]    { background: var(--s-wait-bg);   color: var(--s-wait-text);   border-color: var(--s-wait-border);   box-shadow: 0 2px 4px rgba(193,90,0,.15); }
    .status-btn.on[data-v="por-concluir"] { background: var(--s-pend-bg);   color: var(--s-pend-text);   border-color: var(--s-pend-border);   box-shadow: 0 2px 4px rgba(196,125,0,.15); }
    .status-btn.on[data-v="realizado"]    { background: var(--s-done-bg);   color: var(--s-done-text);   border-color: var(--s-done-border);   box-shadow: 0 2px 4px rgba(26,140,56,.15); }
    .status-btn.on[data-v="cancelado"]    { background: var(--s-cancel-bg); color: var(--s-cancel-text); border-color: var(--s-cancel-border); box-shadow: 0 2px 4px rgba(185,28,28,.15); }

    /* ─── TIMELINE ────────────────────────────────────────────────────── */
    .timeline-wrap { margin-top: 18px; padding-top: 14px; border-top: 1px dashed #cbd5e1; }
    .timeline-title { font-size: .75rem; font-weight: 700; color: #5c7a96; text-transform: uppercase; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
    .tl-item { margin-bottom: 14px; padding-left: 16px; border-left: 2px solid var(--color-primary); position: relative; }
    .tl-item::before { content:''; position:absolute; left:-6px; top:3px; width:10px; height:10px; border-radius:50%; background:var(--color-primary); border:2px solid #f8fafc; }
    .tl-date { font-size: .65rem; color: #64748b; font-weight: 700; margin-bottom: 3px; font-family: 'IBM Plex Mono', monospace; }
    .tl-user { font-size: .8rem; font-weight: 700; color: #0f172a; margin-bottom: 4px; display: flex; align-items: center; gap: 5px; }
    .tl-badge { font-size: .6rem; padding: 2px 6px; border-radius: 4px; background: #e2e8f0; color: #475569; text-transform: uppercase; }
    .tl-msg { font-size: .75rem; color: #334155; background: #fff; padding: 8px 10px; border-radius: 8px; border: 1px solid #e2e8f0; line-height: 1.4; }

    /* ─── TABLA ───────────────────────────────────────────────────────── */
    .data-section { background: #fff; padding: 20px; border-radius: 14px; border: 1px solid #c4d4e3; box-shadow: 0 4px 12px rgba(0,0,0,.03); min-width: 0; }

    .table-controls { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; align-items: center; }
    .search-wrap { flex-grow: 1; position: relative; min-width: 200px; }
    .search-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ab5c8; font-size: .8rem; }
    .search-wrap input { width: 100%; padding: 8px 10px 8px 32px; border: 1px solid #c4d4e3; border-radius: 7px; background: #fff; font-size: .85rem; transition: .2s; }
    .search-wrap input:focus { border-color: var(--color-primary); outline: none; box-shadow: 0 0 0 3px rgba(52,73,85,.1); }

    select.f-sel { border: 1px solid #c4d4e3; border-radius: 7px; padding: 8px 12px; outline: none; background: #fff; color: #0c1e2e; font-size: .85rem; font-weight: 500; transition: .2s; }
    select.f-sel:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(52,73,85,.1); }

    .main-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .main-table th { background: var(--color-soft); color: var(--color-medium); padding: 11px 12px; text-align: left; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; border-bottom: 2px solid #c4d4e3; }
    .main-table th:first-child { border-top-left-radius: 8px; }
    .main-table th:last-child  { border-top-right-radius: 8px; }
    .main-table td { padding: 11px 12px; border-bottom: 1px solid #e4eef5; color: #1a2e40; font-size: .85rem; vertical-align: middle; transition: background .15s; }
    .main-table tr:hover td { background: #f8fafc; }
    .main-table tr:last-child td { border-bottom: none; }

    .tk-num  { font-family: 'IBM Plex Mono', monospace; font-size: .85rem; font-weight: 600; color: #000; }
    .tk-folio { font-family: 'IBM Plex Mono', monospace; font-size: .85rem; font-weight: 700; color: #000; }

    @keyframes rowIn { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
    .main-table tbody tr { animation: rowIn .4s cubic-bezier(.16,1,.3,1) forwards; opacity: 0; }

    /* ─── STATUS TAGS ─────────────────────────────────────────────────── */
    .stag { display:inline-flex; align-items:center; gap:5px; padding:4px 9px; border-radius:5px; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap; }
    .stag::before { content:''; width:6px; height:6px; border-radius:50%; flex-shrink:0; }
    .stag.nuevo        { background:var(--s-new-bg);    color:var(--s-new-text);    border:1px solid var(--s-new-border);    } .stag.nuevo::before        { background:var(--s-new-dot); }
    .stag.abierto      { background:var(--s-open-bg);   color:var(--s-open-text);   border:1px solid var(--s-open-border);   } .stag.abierto::before      { background:var(--s-open-dot); }
    .stag.en-espera    { background:var(--s-wait-bg);   color:var(--s-wait-text);   border:1px solid var(--s-wait-border);   } .stag.en-espera::before    { background:var(--s-wait-dot); }
    .stag.por-concluir { background:var(--s-pend-bg);   color:var(--s-pend-text);   border:1px solid var(--s-pend-border);   } .stag.por-concluir::before { background:var(--s-pend-dot); }
    .stag.realizado    { background:var(--s-done-bg);   color:var(--s-done-text);   border:1px solid var(--s-done-border);   } .stag.realizado::before    { background:var(--s-done-dot); }
    .stag.cancelado    { background:var(--s-cancel-bg); color:var(--s-cancel-text); border:1px solid var(--s-cancel-border); } .stag.cancelado::before    { background:var(--s-cancel-dot); }

    .ptag { display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:4px; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
    .ptag.alta   { background:var(--s-cancel-bg); color:var(--s-cancel-text); border:1px solid var(--s-cancel-border); }
    .ptag.media  { background:var(--s-pend-bg);   color:var(--s-pend-text);   border:1px solid var(--s-pend-border); }
    .ptag.baja   { background:var(--s-done-bg);   color:var(--s-done-text);   border:1px solid var(--s-done-border); }

    /* ─── BOTONES ─────────────────────────────────────────────────────── */
    .btn { padding: 8px 16px; border-radius: 7px; cursor: pointer; border: none; font-weight: 600; font-size: .85rem; display: inline-flex; align-items: center; gap: 6px; transition: all .2s; width: 100%; justify-content: center; }
    .btn-primary   { background: var(--color-primary); color: #fff; }
    .btn-primary:hover   { background: var(--color-medium); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(52,73,85,.28); }
    .btn-secondary { background: var(--white); color: #1a2e40; border: 1px solid #c4d4e3; }
    .btn-secondary:hover { background: var(--color-soft); border-color: var(--color-primary); transform: translateY(-1px); }

    .tbl-actions { display: flex; gap: 4px; justify-content: center; }
    .tbl-actions button { width: 28px; height: 28px; border-radius: 6px; border: 1px solid transparent; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: .2s; font-size: .75rem; }
    .btn-view   { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
    .btn-attend { background: #ffedd5; color: #c2410c; border-color: #fdba74; }
    .btn-view:hover, .btn-attend:hover { transform: translateY(-2px); filter: brightness(.92); box-shadow: 0 3px 6px rgba(0,0,0,.08); }
    .btn-close-panel {
        background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.25); color: #fff;
        width: 28px; height: 28px; border-radius: 50%; cursor: pointer;
        display: flex; align-items: center; justify-content: center; transition: .25s; flex-shrink: 0; font-size: .85rem;
    }
    .btn-close-panel:hover { background: #dc2626; border-color: #dc2626; transform: rotate(90deg); }

    /* ─── PAGINACIÓN ──────────────────────────────────────────────────── */
    .pager { display: flex; justify-content: space-between; align-items: center; margin-top: 16px; padding-top: 16px; border-top: 1px solid #e4eef5; }
    .pager-info { font-size: .8rem; color: #5c7a96; font-weight: 600; }
    .pager-btns { display: flex; gap: 4px; }
    .pager-btn { background: #fff; border: 1px solid #c4d4e3; color: #1a2e40; width: 30px; height: 30px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: .8rem; font-weight: 600; transition: .2s; }
    .pager-btn:hover:not(:disabled) { background: var(--color-soft); border-color: var(--color-primary); }
    .pager-btn.on { background: var(--color-primary); color: #fff; border-color: var(--color-primary); }
    .pager-btn:disabled { opacity: .5; cursor: not-allowed; }

    /* ─── MODAL FLOTANTE ──────────────────────────────────────────────── */
    .modal-overlay {
        position: fixed; inset: 0;
        background: rgba(10,22,40,.72);
        backdrop-filter: blur(5px);
        z-index: 2000;
        display: flex; align-items: center; justify-content: center;
        opacity: 0; visibility: hidden; transition: all .3s ease;
    }
    .modal-overlay.open { opacity: 1; visibility: visible; }

    .modal-box {
        background: #fff; border-radius: 20px; width: 100%; max-width: 620px; max-height: 90vh;
        display: flex; flex-direction: column;
        box-shadow: 0 30px 60px -10px rgba(0,0,0,.35); border: 1px solid #b8ccdc;
        transform: scale(.93) translateY(28px); transition: transform .4s cubic-bezier(.16,1,.3,1);
    }
    .modal-overlay.open .modal-box { transform: scale(1) translateY(0); }

    .modal-head {
        display: flex; justify-content: space-between; align-items: center;
        padding: 16px 22px;
        background: linear-gradient(135deg, var(--color-primary) 0%, #263840 100%);
        color: #fff; border-radius: 19px 19px 0 0; flex-shrink: 0;
    }
    .modal-head h3 { margin: 0; font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: 8px; }
    .modal-head .sub { font-size: .75rem; font-weight: 600; color: rgba(255,255,255,.85); margin-top: 4px; }

    .modal-body { padding: 22px 26px; overflow-y: auto; background: #f8fafc; border-radius: 0 0 19px 19px; }
    .modal-body .meta-strip { grid-template-columns: repeat(3,1fr); }

    .form-actions { display: flex; gap: 10px; padding-top: 8px; }
    .form-actions .btn { width: auto; }
</style>

<div class="content active">

    {{-- ── ESTADÍSTICAS ────────────────────────────────────────────────── --}}
    <div class="stats-row">
        <div class="stat-card c-main">
            <div class="stat-info">
                <span class="stat-label">Total Tickets</span>
                <h2 class="stat-value" id="stat-total">0</h2>
            </div>
            <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
        </div>
        <div class="stat-card c-wait">
            <div class="stat-info">
                <span class="stat-label">En Espera</span>
                <h2 class="stat-value" id="stat-espera">0</h2>
            </div>
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
        </div>
        <div class="stat-card c-pend">
            <div class="stat-info">
                <span class="stat-label">Por Concluir</span>
                <h2 class="stat-value" id="stat-por-concluir">0</h2>
            </div>
            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
        <div class="stat-card c-done">
            <div class="stat-info">
                <span class="stat-label">Realizados</span>
                <h2 class="stat-value" id="stat-realizado">0</h2>
            </div>
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="stat-card c-cancel">
            <div class="stat-info">
                <span class="stat-label">Cancelados</span>
                <h2 class="stat-value" id="stat-cancelado">0</h2>
            </div>
            <div class="stat-icon"><i class="fas fa-ban"></i></div>
        </div>
    </div>

    {{-- ── LAYOUT PRINCIPAL ────────────────────────────────────────────── --}}
    <div class="tickets-layout panel-off" id="ticketsLayout">

        {{-- ── PANEL LATERAL ──────────────────────────────────────────── --}}
        <div class="side-panel">
            <div class="panel-head">
                <div>
                    <h3 id="panel-title"><i class="fas fa-ticket-alt"></i> Visualizador de Ticket</h3>
                    <p class="sub" id="panel-sub">
                        <span><i class="fas fa-user"></i> ---</span>
                        <span><i class="fas fa-building"></i> ---</span>
                    </p>
                </div>
                <button type="button" class="btn-close-panel" onclick="closePanel()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="panel-form">
                @csrf
                <input type="hidden" id="p-id">

                <div class="panel-body">
                    {{-- Loader --}}
                    <div id="panel-loader" class="panel-loader" style="display:none;">
                        <div class="loader-spin"></div>
                        <p>Obteniendo información...</p>
                    </div>

                    {{-- Meta datos --}}
                    <div class="meta-strip">
                        <div class="meta-cell">
                            <span class="meta-lbl"><i class="far fa-calendar-alt"></i> Fecha</span>
                            <span class="meta-val"><span id="p-fecha">--</span></span>
                        </div>
                        <div class="meta-cell">
                            <span class="meta-lbl"><i class="fas fa-ticket-alt"></i> Ticket</span>
                            <span class="meta-val mono mono-num" id="p-num">--</span>
                        </div>
                        <div class="meta-cell">
                            <span class="meta-lbl"><i class="fas fa-hashtag"></i> Folio</span>
                            <span class="meta-val mono mono-folio" id="p-folio">--</span>
                        </div>
                    </div>

                    {{-- Estado + Prioridad --}}
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-tasks"></i> Estado Actual</label>
                            <div class="field-wrap">
                                <i class="field-icon fas fa-circle" style="font-size:.55rem;"></i>
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
                                <i class="field-icon fas fa-flag"></i>
                                <select id="p-prioridad" class="ctrl" disabled onchange="setSelectColor(this,'p')">
                                    <option value="alta">Alta</option>
                                    <option value="media">Media</option>
                                    <option value="baja">Baja</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr class="form-divider" style="margin-top:0;">

                    <div class="form-group">
                        <label><i class="fas fa-heading"></i> Asunto Breve</label>
                        <div class="field-wrap">
                            <i class="field-icon fas fa-heading"></i>
                            <input type="text" id="p-asunto" class="ctrl" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-align-left"></i> Descripción Detallada</label>
                        <textarea id="p-desc" class="ctrl no-icon" rows="1" required></textarea>
                    </div>

                    {{-- Bloque comentario obligatorio --}}
                    <div class="comment-block" id="p-comment-block">
                        <div class="form-group" style="margin-bottom:0;">
                            <label><i class="fas fa-exclamation-circle"></i> <span id="p-comment-label">Justificación Requerida *</span></label>
                            <textarea id="p-comment" class="ctrl no-icon" rows="1" placeholder="Agregue la justificación obligatoria..."></textarea>
                        </div>
                    </div>

                    {{-- Timeline --}}
                    <div class="timeline-wrap" id="p-timeline" style="display:none;">
                        <h4 class="timeline-title"><i class="fas fa-history"></i> Historial de Gestiones</h4>
                        <div id="p-timeline-items"></div>
                    </div>
                </div>

                {{-- Footer fijo --}}
                <div class="panel-foot" id="p-footer">
                    <span class="status-bar-label"><i class="fas fa-random"></i> Modificar Estado:</span>
                    <div class="status-bar" id="p-status-bar">
                        <button type="button" class="status-btn" data-v="en-espera"    onclick="pickStatus('en-espera')">En Espera</button>
                        <button type="button" class="status-btn" data-v="por-concluir" onclick="pickStatus('por-concluir')">Por Concluir</button>
                        <button type="button" class="status-btn" data-v="realizado"    onclick="pickStatus('realizado')">Realizado</button>
                        <button type="button" class="status-btn" data-v="cancelado"    onclick="pickStatus('cancelado')">Cancelado</button>
                    </div>
                    <button type="submit" class="btn btn-primary" id="btn-save">
                        <i class="fas fa-save"></i> Guardar Gestión
                    </button>
                </div>
            </form>
        </div>

        {{-- ── TABLA ──────────────────────────────────────────────────── --}}
        <section class="data-section">
            <div class="table-controls">
                <button class="btn btn-primary" onclick="openModal()" style="width:auto;">
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
                    <option value="alta">Alta</option>
                    <option value="media">Media</option>
                    <option value="baja">Baja</option>
                </select>

                <button class="btn btn-secondary" onclick="exportarTabla()" style="width:auto;">
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
</div>

{{-- ── MODAL — NUEVO TICKET ─────────────────────────────────────────────── --}}
{{--
    CAMBIOS vs versión anterior:
    ✦ Se eliminó el campo "Nivel de Prioridad" — el sistema asignará "Media" por defecto
      y el técnico la ajusta desde el panel lateral al atender el ticket.
    ✦ Se eliminó el campo "Estado Asignado (Nuevo)" — es siempre automático, mostrarlo
      no aportaba información accionable para el solicitante.
    ✦ La fecha, No. de Ticket y Folio se mantienen como referencia informativa (meta-strip).
--}}
<div class="modal-overlay" id="modal-new">
    <div class="modal-box">
        <div class="modal-head">
            <div>
                <h3><i class="fas fa-plus-circle"></i> Apertura de Nuevo Ticket</h3>
                <p class="sub">Complete los datos para registrar la incidencia</p>
            </div>
            <button class="btn-close-panel" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>

        <div class="modal-body">
            {{-- Info de referencia --}}
            <div class="meta-strip">
                <div class="meta-cell">
                    <span class="meta-lbl"><i class="far fa-calendar-alt"></i> Fecha Emisión</span>
                    <span class="meta-val" id="modal-date">Cargando...</span>
                </div>
                <div class="meta-cell">
                    <span class="meta-lbl"><i class="fas fa-ticket-alt"></i> No. de Ticket</span>
                    <span class="meta-val mono mono-num">TK-AUTO</span>
                </div>
                <div class="meta-cell">
                    <span class="meta-lbl"><i class="fas fa-hashtag"></i> Folio del Área</span>
                    <span class="meta-val mono mono-folio">TK-AUTO</span>
                </div>
            </div>

            <form id="form-new">
                @csrf

                {{-- Área y Solicitante (solo informativo, deshabilitado) --}}
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

                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Descripción Detallada *</label>
                    <textarea id="n-desc" class="ctrl no-icon" rows="1"
                        placeholder="Proporcione los detalles de la falla o solicitud..." required></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btn-new-submit">
                        <i class="fas fa-paper-plane"></i> Generar Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
/* ── CONSTANTES ─────────────────────────────────────────────────────────── */
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content
             || document.querySelector('input[name="_token"]').value;
const H = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' };

const STATUS_COLORS = {
    nuevo:        'var(--s-new-text)',
    abierto:      'var(--s-open-text)',
    'en-espera':  'var(--s-wait-text)',
    'por-concluir':'var(--s-pend-text)',
    realizado:    'var(--s-done-text)',
    cancelado:    'var(--s-cancel-text)',
};
const PRIO_COLORS = {
    alta: 'var(--s-cancel-text)',
    media:'var(--s-pend-text)',
    baja: 'var(--s-done-text)',
};

let allTickets = [], filteredTickets = [], page = 1;

/* ── INIT ───────────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('modal-date').innerText =
        new Date().toLocaleDateString('es-ES', { day:'2-digit', month:'2-digit', year:'numeric' });
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
function resetTextareas() {
    document.querySelectorAll('textarea').forEach(ta => ta.style.height = 'auto');
}

/* ── CARGA DE TICKETS ───────────────────────────────────────────────────── */
async function loadTickets() {
    try {
        const r = await fetch('{{ route("systems.tickets.get") }}', { headers: H });
        if (!r.ok) throw new Error();
        allTickets = await r.json();
        filter();
        updateStats(allTickets);
    } catch { Swal.fire('Error', 'No se pudieron cargar los tickets.', 'error'); }
}

/* ── ESTADÍSTICAS ───────────────────────────────────────────────────────── */
function updateStats(data) {
    document.getElementById('stat-total').innerText       = data.length;
    document.getElementById('stat-espera').innerText      = data.filter(t => t.status === 'en-espera').length;
    document.getElementById('stat-por-concluir').innerText= data.filter(t => t.status === 'por-concluir').length;
    document.getElementById('stat-realizado').innerText   = data.filter(t => t.status === 'realizado').length;
    document.getElementById('stat-cancelado').innerText   = data.filter(t => t.status === 'cancelado').length;
}

/* ── FILTRADO ───────────────────────────────────────────────────────────── */
document.getElementById('buscador').addEventListener('keyup', filter);
document.getElementById('fil-estado').addEventListener('change', filter);
document.getElementById('fil-prioridad').addEventListener('change', filter);
document.getElementById('mostrar').addEventListener('change', () => { page = 1; renderTable(); });

function filter() {
    const q   = document.getElementById('buscador').value.toLowerCase();
    const est = document.getElementById('fil-estado').value;
    const pri = document.getElementById('fil-prioridad').value;

    filteredTickets = allTickets.filter(t => {
        if (q   && !`${t.display_id} ${t.folio} ${t.subject} ${t.user_name}`.toLowerCase().includes(q)) return false;
        if (est && t.status   !== est) return false;
        if (pri && t.priority !== pri) return false;
        return true;
    });
    page = 1;
    renderTable();
}

/* ── RENDERIZADO DE TABLA ───────────────────────────────────────────────── */
function renderTable() {
    const tbody = document.getElementById('tickets-body');
    tbody.innerHTML = '';

    if (!filteredTickets.length) {
        tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:20px;">No hay tickets que coincidan.</td></tr>`;
        document.getElementById('pager').style.display = 'none';
        return;
    }

    const perPage = document.getElementById('mostrar').value === 'todos'
        ? filteredTickets.length
        : parseInt(document.getElementById('mostrar').value);

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
            <td><span class="ptag ${t.priority}">${cap(t.priority)}</span></td>
            <td><span class="stag ${t.status}">${fmtStatus(t.status)}</span></td>
            <td class="tbl-actions">
                <button class="btn-view"   title="Ver Detalles"    onclick="openPanel('ver',${t.id})"><i class="fas fa-eye"></i></button>
                <button class="btn-attend" title="Atender Ticket"  onclick="openPanel('atender',${t.id})"><i class="fas fa-tools"></i></button>
            </td>`;
        tbody.appendChild(tr);
    });

    renderPager(totalPages, start, Math.min(start + perPage, filteredTickets.length));
}

/* ── PAGINACIÓN ─────────────────────────────────────────────────────────── */
function renderPager(totalPages, start, end) {
    const pager = document.getElementById('pager');
    if (totalPages <= 1) { pager.style.display = 'none'; return; }
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

    addBtn('<i class="fas fa-chevron-left"></i>', page === 1, false, () => { page--; renderTable(); });

    for (let i = 1; i <= totalPages; i++) {
        if (totalPages > 7 && Math.abs(i - page) > 1 && i > 2 && i < totalPages - 1) {
            if (btns.lastChild?.innerText !== '...') {
                const dots = document.createElement('span');
                dots.style.padding = '5px'; dots.innerText = '...';
                btns.appendChild(dots);
            }
            continue;
        }
        addBtn(i, false, i === page, () => { page = i; renderTable(); });
    }

    addBtn('<i class="fas fa-chevron-right"></i>', page === totalPages, false, () => { page++; renderTable(); });
}

/* ── COLORES DINÁMICOS DE SELECTS ───────────────────────────────────────── */
function setSelectColor(el, type) {
    // Limpia clases previas
    el.className = el.className.replace(/bg-[sp]-[\w-]+/g, '').trim();
    const v = el.value;
    el.classList.add(type === 's' ? `bg-s-${v}` : `bg-p-${v}`);

    const icon = el.closest('.field-wrap')?.querySelector('.field-icon');
    if (icon) icon.style.color = type === 's' ? STATUS_COLORS[v] : PRIO_COLORS[v];
}

/* ── MODAL — NUEVO TICKET ───────────────────────────────────────────────── */
function openModal() {
    closePanel();
    document.getElementById('modal-new').classList.add('open');
}
function closeModal() {
    document.getElementById('modal-new').classList.remove('open');
    document.getElementById('form-new').reset();
    resetTextareas();
}

document.getElementById('form-new').addEventListener('submit', async e => {
    e.preventDefault();
    const btn = document.getElementById('btn-new-submit');
    const payload = {
        area_code:   document.getElementById('n-area').value,
        subject:     document.getElementById('n-asunto').value,
        description: document.getElementById('n-desc').value,
        priority:    'media'   // Siempre media por defecto; el técnico la ajusta al atender
    };

    try {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
        const r = await fetch('{{ route("systems.tickets.store") }}', { method: 'POST', headers: H, body: JSON.stringify(payload) });
        const res = await r.json();
        if (r.ok && res.success) {
            Swal.fire({ title: '¡Ticket Generado!', text: `Folio asignado: ${res.folio}.`, icon: 'success', confirmButtonColor: '#344955' })
                .then(() => { closeModal(); loadTickets(); });
        } else throw new Error(res.message);
    } catch (err) {
        Swal.fire('Error', err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Generar Ticket';
    }
});

/* ── PANEL LATERAL ──────────────────────────────────────────────────────── */
async function openPanel(action, id) {
    const layout = document.getElementById('ticketsLayout');
    const loader = document.getElementById('panel-loader');

    loader.style.display = 'flex';
    layout.classList.remove('panel-off');

    document.getElementById('panel-title').innerHTML = action === 'ver'
        ? '<i class="fas fa-eye"></i> Detalles del Ticket'
        : '<i class="fas fa-tools"></i> Atendiendo Ticket';

    try {
        const r = await fetch(`{{ url('/systems/tickets/show') }}/${id}`, { headers: H });
        const data = await r.json();
        if (!data.success) throw new Error('No se pudieron cargar los detalles.');

        let d = data.ticket;

        /* Auto-apertura al atender un ticket "nuevo" */
        if (action === 'atender' && d.status === 'nuevo') {
            const upd = await fetch(`{{ url('/systems/tickets/update-status') }}/${id}`, {
                method: 'PUT', headers: H,
                body: JSON.stringify({
                    status: 'abierto', priority: d.priority,
                    subject: d.subject, description: d.description,
                    comentario: 'El ticket ha sido tomado para su atención.'
                })
            });
            if (upd.ok) {
                d.status = 'abierto';
                loadTickets();
                if (!d.trackings) d.trackings = [];
                d.trackings.push({
                    created_at: new Date().toISOString(),
                    user: { name: '{{ auth()->user()->name ?? "Sistema" }}' },
                    status_after: 'abierto',
                    message: 'El ticket ha sido tomado para su atención.'
                });
            }
        }

        /* Poblar campos */
        document.getElementById('p-id').value    = d.id;
        document.getElementById('p-folio').innerText = d.folio;
        document.getElementById('p-num').innerText   = d.display_id;
        document.getElementById('p-fecha').innerText = d.created_at;

        const selEst = document.getElementById('p-estado');
        selEst.value = d.status; setSelectColor(selEst, 's');

        const selPri = document.getElementById('p-prioridad');
        selPri.value = d.priority; setSelectColor(selPri, 'p');

        document.getElementById('p-asunto').value = d.subject;

        const desc = document.getElementById('p-desc');
        desc.value = d.description;
        setTimeout(() => { desc.style.height = 'auto'; desc.style.height = desc.scrollHeight + 'px'; }, 50);

        document.getElementById('panel-sub').innerHTML =
            `<span><i class="fas fa-user"></i> ${d.user_name}</span>
             <span><i class="fas fa-building"></i> ${d.department_name}</span>`;

        /* Reset comentario */
        document.getElementById('p-comment-block').classList.remove('show', 'danger');
        document.getElementById('p-comment').value = '';
        document.getElementById('p-comment').style.height = 'auto';

        renderTimeline(d.trackings);

        /* Permisos según acción */
        document.querySelectorAll('#panel-form .ctrl').forEach(el => {
            if (action === 'ver') {
                el.tagName === 'SELECT' ? (el.disabled = true) : (el.readOnly = true);
            } else {
                if (['p-estado','p-prioridad'].includes(el.id)) el.disabled = true;
                else el.readOnly = false;
            }
        });

        /* Visibilidad del footer y botones de estado */
        const footer = document.getElementById('p-footer');
        const btns   = document.querySelectorAll('.status-btn');
        btns.forEach(b => { b.style.display = 'inline-block'; b.classList.remove('on'); });

        if (action === 'ver' || ['realizado','cancelado'].includes(d.status)) {
            footer.style.display = 'none';
        } else {
            footer.style.display = 'block';

            /* Si ya está "por concluir", solo permite Realizado o Cancelado */
            if (d.status === 'por-concluir') {
                btns.forEach(b => {
                    if (!['realizado','cancelado'].includes(b.dataset.v)) b.style.display = 'none';
                });
            }

            /* Marca el botón del estado actual si aplica */
            const cur = document.querySelector(`.status-btn[data-v="${d.status}"]`);
            if (cur) cur.classList.add('on');
        }

        /* Ocultar loader con fade */
        setTimeout(() => {
            loader.style.opacity = '0';
            setTimeout(() => { loader.style.display = 'none'; loader.style.opacity = '1'; }, 300);
        }, 400);

    } catch (err) {
        Swal.fire('Error', err.message, 'error');
        closePanel();
    }
}

function closePanel() {
    document.getElementById('ticketsLayout').classList.add('panel-off');
    document.getElementById('panel-form').reset();
    document.getElementById('p-comment-block').classList.remove('show', 'danger');
    document.getElementById('p-timeline').style.display = 'none';
    document.getElementById('p-estado').className   = 'ctrl';
    document.getElementById('p-prioridad').className = 'ctrl';
    resetTextareas();
}

/* ── BOTONES DE ESTADO (FOOTER) ─────────────────────────────────────────── */
function pickStatus(val) {
    const sel = document.getElementById('p-estado');
    sel.value = val; setSelectColor(sel, 's');

    document.querySelectorAll('.status-btn').forEach(b => b.classList.remove('on'));
    const btn = document.querySelector(`.status-btn[data-v="${val}"]`);
    if (btn) btn.classList.add('on');

    checkCommentBlock(val);
}

function checkCommentBlock(val) {
    const block  = document.getElementById('p-comment-block');
    const label  = document.getElementById('p-comment-label');
    const field  = document.getElementById('p-comment');

    if (val === 'por-concluir') {
        block.classList.add('show'); block.classList.remove('danger');
        label.textContent = 'Justificación técnica de "Por Concluir" *';
        field.placeholder = 'Detalle qué impide el cierre...';
    } else if (val === 'cancelado') {
        block.classList.add('show', 'danger');
        label.textContent = 'Motivo de cancelación *';
        field.placeholder = 'Explique el motivo de la cancelación...';
    } else {
        block.classList.remove('show', 'danger');
        field.value = ''; field.style.height = 'auto';
    }
}

/* ── GUARDAR GESTIÓN ────────────────────────────────────────────────────── */
document.getElementById('panel-form').addEventListener('submit', async e => {
    e.preventDefault();
    const id         = document.getElementById('p-id').value;
    const estado     = document.getElementById('p-estado').value;
    const comentario = document.getElementById('p-comment').value;

    if (['por-concluir','cancelado'].includes(estado) && !comentario.trim()) {
        Swal.fire({ title: 'Justificación obligatoria', text: 'Debe agregar un comentario para este estatus.', icon: 'warning', confirmButtonColor: '#344955' });
        return;
    }

    const payload = {
        status:      estado,
        priority:    document.getElementById('p-prioridad').value,
        subject:     document.getElementById('p-asunto').value,
        description: document.getElementById('p-desc').value,
        comentario:  comentario,
    };
    const btn = document.getElementById('btn-save');

    try {
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        const r = await fetch(`{{ url('/systems/tickets/update-status') }}/${id}`, { method: 'PUT', headers: H, body: JSON.stringify(payload) });
        const res = await r.json();
        if (r.ok && res.success) {
            Swal.fire({ title: '¡Gestión guardada!', text: res.message, icon: 'success', confirmButtonColor: '#344955' })
                .then(() => { closePanel(); loadTickets(); });
        } else throw new Error(res.message);
    } catch (err) {
        Swal.fire('Error', err.message, 'error');
    } finally {
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Guardar Gestión';
    }
});

/* ── TIMELINE ───────────────────────────────────────────────────────────── */
function renderTimeline(trackings) {
    const wrap    = document.getElementById('p-timeline');
    const content = document.getElementById('p-timeline-items');
    const items   = (trackings || []).filter(t => t.message?.trim());

    if (!items.length) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'block';
    content.innerHTML = items.map(t => {
        const date = new Date(t.created_at || Date.now())
            .toLocaleString('es-ES', { day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit' });
        return `
        <div class="tl-item">
            <div class="tl-date">${date}</div>
            <div class="tl-user">${t.user?.name ?? 'Sistema'} <span class="tl-badge">${fmtStatus(t.status_after)}</span></div>
            <div class="tl-msg">${t.message}</div>
        </div>`;
    }).join('');
}

/* ── HELPERS ────────────────────────────────────────────────────────────── */
function cap(s)       { return s.charAt(0).toUpperCase() + s.slice(1); }
function fmtStatus(s) { return (s || '').split('-').map(cap).join(' '); }
</script>
@endpush
