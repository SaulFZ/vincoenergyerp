<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vinco Energy Services</title>

    <script>
        // Aplica el estado del menú antes de pintar (evita parpadeo)
        try {
            if (localStorage.getItem('vh.sidebarMin') === '1' && window.innerWidth > 768) {
                document.documentElement.classList.add('sidebar-minimized');
            }
        } catch (e) {}
    </script>

    {{-- FontAwesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">

    {{-- CSS Principal --}}
    <link href="{{ asset('assets/css/home.css') }}" rel="stylesheet">
    @stack('styles')

    <style>
        :root {
            --azul-oscuro: #1a2a5f;
            --azul-medio: #2c4285;
            --azul-claro: #3b57bd;
            --azul-like: #2563eb;
            --azul-like-suave: #eff6ff;
            --naranja: #d67e29;
            --naranja-suave: rgba(214, 126, 41, 0.15);
            --fondo-app: #f4f7fe;
            --blanco: #ffffff;
            --borde-suave: #e2e8f0;
            --texto-principal: #1e293b;
            --texto-secundario: #64748b;
            --sombra-card: 0 1px 2px rgba(15, 23, 42, 0.04);
            --sombra-hover: 0 8px 24px rgba(15, 23, 42, 0.08);
            --transicion: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);

            /* Colores semánticos por tipo de publicación */
            --tipo-aviso: #dc2626;
            --tipo-aviso-bg: #fef2f2;
            --tipo-noticia: #2563eb;
            --tipo-noticia-bg: #eff6ff;
            --tipo-folleto: #059669;
            --tipo-folleto-bg: #ecfdf5;
            --tipo-video: #7c3aed;
            --tipo-video-bg: #f5f3ff;
        }

        /* Accesibilidad: foco visible */
        .social-feed-wrapper button:focus-visible,
        .social-feed-wrapper a:focus-visible,
        .social-feed-wrapper input:focus-visible,
        .modal-publicar button:focus-visible {
            outline: 2px solid var(--azul-claro);
            outline-offset: 2px;
        }

        /* ========== LAYOUT GENERAL DEL FEED ========== */
        .social-feed-wrapper {
            max-width: 1180px;
            margin: 0 auto;
        }

        .feed-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 300px;
            gap: 28px;
            align-items: start;
        }

        .feed-main {
            min-width: 0;
        }

        /* ========== TOOLBAR SUPERIOR ========== */
        .feed-page-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .feed-page-title {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .feed-brand-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--blanco);
            border: 1px solid var(--borde-suave);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .feed-brand-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 6px;
        }

        .feed-page-title h1 {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--azul-oscuro);
            letter-spacing: -0.4px;
            line-height: 1.2;
        }

        .feed-page-title p {
            font-size: 0.85rem;
            color: var(--texto-secundario);
            margin-top: 2px;
        }

        .btn-publicar {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 20px;
            border: none;
            border-radius: 10px;
            background: var(--naranja);
            color: var(--blanco);
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transicion);
            white-space: nowrap;
        }

        .btn-publicar:hover {
            background: #bf6d1f;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(214, 126, 41, 0.3);
        }

        .btn-publicar:active {
            transform: translateY(0);
        }

        .feed-head-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .feed-date {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border: 1px solid var(--borde-suave);
            border-radius: 10px;
            background: var(--blanco);
            color: var(--texto-secundario);
            font-size: 0.82rem;
            font-weight: 500;
        }

        .feed-date i {
            color: var(--naranja);
        }

        /* ========== COMPOSER RÁPIDO ========== */
        .feed-composer {
            background: var(--blanco);
            border: 1px solid var(--borde-suave);
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: var(--sombra-card);
        }

        .composer-top {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .composer-avatar {
            width: 42px;
            height: 42px;
            font-size: 0.85rem;
        }

        .composer-input {
            flex: 1;
            padding: 11px 16px;
            border: 1px solid var(--borde-suave);
            border-radius: 10px;
            background: var(--fondo-app);
            color: var(--texto-secundario);
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            text-align: left;
            cursor: text;
            transition: var(--transicion);
        }

        .composer-input:hover {
            border-color: var(--azul-claro);
            background: var(--blanco);
        }

        .composer-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid var(--borde-suave);
        }

        .composer-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border: none;
            border-radius: 8px;
            background: transparent;
            color: var(--texto-secundario);
            font-family: 'Poppins', sans-serif;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transicion);
        }

        .composer-chip:hover {
            background: var(--fondo-app);
            color: var(--texto-principal);
        }

        /* ========== BARRA DE BÚSQUEDA + FILTROS ========== */
        .feed-toolbar {
            background: var(--blanco);
            border: 1px solid var(--borde-suave);
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 20px;
            box-shadow: var(--sombra-card);
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .feed-search {
            position: relative;
        }

        .feed-search i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--texto-secundario);
            font-size: 0.9rem;
            pointer-events: none;
        }

        .feed-search input {
            width: 100%;
            padding: 11px 14px 11px 40px;
            border: 1px solid var(--borde-suave);
            border-radius: 10px;
            background: var(--fondo-app);
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            color: var(--texto-principal);
            transition: var(--transicion);
        }

        .feed-search input::placeholder {
            color: var(--texto-secundario);
        }

        .feed-search input:focus {
            outline: none;
            background: var(--blanco);
            border-color: var(--azul-claro);
            box-shadow: 0 0 0 3px rgba(59, 87, 189, 0.12);
        }

        .feed-filters {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 2px;
            scrollbar-width: none;
        }

        .feed-filters::-webkit-scrollbar {
            display: none;
        }

        .feed-filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border: 1px solid var(--borde-suave);
            border-radius: 8px;
            background: var(--blanco);
            color: var(--texto-secundario);
            font-family: 'Poppins', sans-serif;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transicion);
            white-space: nowrap;
        }

        .feed-filter-chip:hover {
            border-color: var(--azul-oscuro);
            color: var(--azul-oscuro);
        }

        .feed-filter-chip.active {
            background: var(--azul-oscuro);
            border-color: var(--azul-oscuro);
            color: var(--blanco);
            font-weight: 600;
        }

        .chip-count {
            min-width: 22px;
            padding: 1px 7px;
            border-radius: 20px;
            background: var(--fondo-app);
            color: var(--texto-secundario);
            font-size: 0.72rem;
            font-weight: 600;
            text-align: center;
        }

        .feed-filter-chip.active .chip-count {
            background: rgba(255, 255, 255, 0.18);
            color: var(--blanco);
        }

        /* ========== GRID DE PUBLICACIONES ========== */
        .feed-grid {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .post-card {
            background: var(--blanco);
            border: 1px solid var(--borde-suave);
            border-radius: 14px;
            box-shadow: var(--sombra-card);
            transition: var(--transicion);
            position: relative;
        }

        .post-card:hover {
            box-shadow: var(--sombra-hover);
            border-color: #cbd5e1;
        }

        .post-card.is-pinned {
            border-color: var(--naranja);
        }

        .post-card.is-removing {
            opacity: 0;
            transform: scale(0.97);
        }

        .pin-flag {
            display: none;
            align-items: center;
            gap: 6px;
            padding: 7px 24px;
            background: var(--naranja-suave);
            color: #9a5a17;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 13px 13px 0 0;
            letter-spacing: 0.2px;
        }

        .post-card.is-pinned .pin-flag {
            display: flex;
        }

        /* --- Header de tarjeta --- */
        .post-card-header {
            padding: 18px 20px 0 24px;
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .post-avatar {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: var(--azul-medio);
            color: var(--blanco);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .post-avatar.avatar-naranja {
            background: var(--naranja);
        }

        .post-meta {
            flex: 1;
            min-width: 0;
        }

        .post-area {
            font-weight: 600;
            color: var(--texto-principal);
            font-size: 0.98rem;
            line-height: 1.3;
        }

        .post-author {
            font-size: 0.82rem;
            color: var(--texto-secundario);
            margin-top: 1px;
        }

        .post-time {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 6px;
            font-size: 0.78rem;
            color: var(--texto-secundario);
        }

        .post-time i {
            font-size: 0.72rem;
        }

        .dot-separator {
            width: 3px;
            height: 3px;
            border-radius: 50%;
            background: #cbd5e1;
        }

        .inline-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 6px;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .inline-tag i {
            font-size: 0.6rem;
        }

        .tag-aviso {
            background: var(--tipo-aviso-bg);
            color: var(--tipo-aviso);
        }

        .tag-noticia {
            background: var(--tipo-noticia-bg);
            color: var(--tipo-noticia);
        }

        .tag-folleto {
            background: var(--tipo-folleto-bg);
            color: var(--tipo-folleto);
        }

        .tag-video {
            background: var(--tipo-video-bg);
            color: var(--tipo-video);
        }

        /* --- Menú de opciones --- */
        .post-options {
            position: relative;
            margin-left: auto;
        }

        .btn-options {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 8px;
            background: transparent;
            color: var(--texto-secundario);
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transicion);
        }

        .btn-options:hover,
        .btn-options[aria-expanded="true"] {
            background: var(--fondo-app);
            color: var(--texto-principal);
        }

        .post-dropdown {
            position: absolute;
            top: calc(100% + 6px);
            right: 0;
            width: 180px;
            background: var(--blanco);
            border: 1px solid var(--borde-suave);
            border-radius: 12px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
            z-index: 20;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-6px);
            transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s;
        }

        .post-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .post-dropdown ul {
            list-style: none;
            padding: 6px;
        }

        .post-dropdown button {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border: none;
            border-radius: 8px;
            background: transparent;
            color: var(--texto-principal);
            font-family: 'Poppins', sans-serif;
            font-size: 0.85rem;
            font-weight: 500;
            text-align: left;
            cursor: pointer;
            transition: var(--transicion);
        }

        .post-dropdown button i {
            width: 16px;
            text-align: center;
            color: var(--texto-secundario);
        }

        .post-dropdown button:hover {
            background: var(--fondo-app);
            color: var(--azul-oscuro);
        }

        .post-dropdown button:hover i {
            color: var(--azul-oscuro);
        }

        .post-dropdown .menu-divider {
            height: 1px;
            background: var(--borde-suave);
            margin: 4px 6px;
        }

        .post-dropdown button.delete-option,
        .post-dropdown button.delete-option i {
            color: var(--tipo-aviso);
        }

        .post-dropdown button.delete-option:hover {
            background: var(--tipo-aviso-bg);
        }

        /* --- Cuerpo --- */
        .post-card-body {
            padding: 16px 24px 18px;
        }

        .post-title {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 1.12rem;
            font-weight: 700;
            color: var(--azul-oscuro);
            letter-spacing: -0.2px;
            line-height: 1.4;
            margin-bottom: 8px;
        }

        .post-title i {
            margin-top: 4px;
            font-size: 0.95rem;
        }

        .post-excerpt {
            font-size: 0.93rem;
            line-height: 1.65;
            color: var(--texto-secundario);
        }

        .post-excerpt strong {
            color: var(--texto-principal);
            font-weight: 600;
        }

        /* --- Imagen única --- */
        .post-media {
            position: relative;
            margin-top: 16px;
            overflow: hidden;
            cursor: zoom-in;
            background: #0f172a;
        }

        .post-media img {
            width: 100%;
            max-height: 420px;
            display: block;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .post-media:hover img {
            transform: scale(1.02);
        }

        .post-media-overlay {
            position: absolute;
            inset: 0;
            background: rgba(26, 42, 95, 0.25);
            opacity: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.25s ease;
        }

        .post-media:hover .post-media-overlay {
            opacity: 1;
        }

        .post-media-overlay i {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            color: var(--azul-oscuro);
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* --- Galería --- */
        .post-media-gallery {
            display: grid;
            gap: 3px;
            margin-top: 16px;
            overflow: hidden;
        }

        .gallery-2 {
            grid-template-columns: 1fr 1fr;
            height: 300px;
        }

        .gallery-3 {
            grid-template-columns: 1.4fr 1fr;
            grid-template-rows: 1fr 1fr;
            height: 340px;
        }

        .gallery-3 .gallery-item:first-child {
            grid-row: 1 / 3;
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            cursor: zoom-in;
            background: #0f172a;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.5s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.04);
        }

        .overlay-more {
            position: absolute;
            inset: 0;
            background: rgba(26, 42, 95, 0.7);
            color: var(--blanco);
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* --- Video --- */
        .post-video-wrapper {
            position: relative;
            margin-top: 16px;
            aspect-ratio: 16 / 9;
            overflow: hidden;
            cursor: pointer;
            background: #0f172a;
        }

        .post-video-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.85;
            transition: opacity 0.3s ease, transform 0.5s ease;
        }

        .post-video-wrapper:hover img {
            opacity: 1;
            transform: scale(1.03);
        }

        .video-play-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .play-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: var(--blanco);
            color: var(--azul-oscuro);
            font-size: 1.3rem;
            padding-left: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            transition: var(--transicion);
        }

        .post-video-wrapper:hover .play-circle {
            transform: scale(1.08);
            background: var(--naranja);
            color: var(--blanco);
        }

        .video-duration {
            position: absolute;
            right: 12px;
            bottom: 12px;
            padding: 3px 9px;
            border-radius: 6px;
            background: rgba(15, 23, 42, 0.8);
            color: var(--blanco);
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* --- Folleto / archivo --- */
        .post-folleto-card {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 16px;
            padding: 14px 16px;
            border: 1px solid var(--borde-suave);
            border-radius: 12px;
            background: var(--fondo-app);
            cursor: pointer;
            transition: var(--transicion);
        }

        .post-folleto-card:hover {
            border-color: var(--tipo-folleto);
            background: var(--tipo-folleto-bg);
        }

        .folleto-icon-box {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            background: var(--blanco);
            border: 1px solid var(--borde-suave);
            color: var(--tipo-folleto);
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .folleto-info {
            min-width: 0;
        }

        .folleto-info strong {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--texto-principal);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .folleto-info span {
            font-size: 0.78rem;
            color: var(--texto-secundario);
        }

        .folleto-download {
            width: 38px;
            height: 38px;
            margin-left: auto;
            border-radius: 10px;
            background: var(--tipo-folleto);
            color: var(--blanco);
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: var(--transicion);
        }

        .post-folleto-card:hover .folleto-download {
            transform: translateY(-2px);
        }

        /* --- Footer --- */
        .post-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 24px;
            border-top: 1px solid var(--borde-suave);
            border-radius: 0 0 13px 13px;
        }

        .post-likes-info {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            color: var(--texto-secundario);
            font-weight: 500;
        }

        .post-likes-avatars {
            display: flex;
        }

        .like-avatar-mini {
            width: 26px;
            height: 26px;
            margin-left: -7px;
            border-radius: 50%;
            border: 2px solid var(--blanco);
            background: var(--azul-claro);
            color: var(--blanco);
            font-size: 0.58rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .like-avatar-mini:first-child {
            margin-left: 0;
        }

        .like-avatar-mini.more {
            background: var(--azul-oscuro);
        }

        .btn-like {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border: 1px solid var(--borde-suave);
            border-radius: 8px;
            background: var(--blanco);
            color: var(--texto-secundario);
            font-family: 'Poppins', sans-serif;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transicion);
        }

        .btn-like:hover {
            border-color: #93c5fd;
            background: var(--azul-like-suave);
            color: var(--azul-like);
        }

        .btn-like.liked {
            border-color: #bfdbfe;
            background: var(--azul-like-suave);
            color: var(--azul-like);
        }

        .btn-like.liked i {
            animation: thumbPop 0.4s cubic-bezier(0.17, 0.89, 0.32, 1.49);
        }

        @keyframes thumbPop {

            0%,
            100% {
                transform: scale(1) rotate(0);
            }

            35% {
                transform: scale(1.3) rotate(-14deg);
            }

            65% {
                transform: scale(0.92) rotate(8deg);
            }
        }

        /* --- Estado vacío --- */
        .feed-empty {
            display: none;
            text-align: center;
            padding: 56px 24px;
            background: var(--blanco);
            border: 1px dashed var(--borde-suave);
            border-radius: 14px;
        }

        .feed-empty.show {
            display: block;
        }

        .feed-empty i {
            width: 64px;
            height: 64px;
            margin-bottom: 16px;
            border-radius: 50%;
            background: var(--naranja-suave);
            color: var(--naranja);
            font-size: 1.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .feed-empty h3 {
            font-size: 1.05rem;
            color: var(--azul-oscuro);
            margin-bottom: 4px;
        }

        .feed-empty p {
            font-size: 0.88rem;
            color: var(--texto-secundario);
        }

        /* --- Load more --- */
        .feed-load-more {
            text-align: center;
            margin-top: 24px;
        }

        .btn-load-more {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 26px;
            border: 1px solid var(--borde-suave);
            border-radius: 10px;
            background: var(--blanco);
            color: var(--texto-principal);
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transicion);
        }

        .btn-load-more:hover:not(:disabled) {
            border-color: var(--azul-claro);
            color: var(--azul-claro);
        }

        .btn-load-more:disabled {
            cursor: default;
            opacity: 0.8;
        }

        /* ========== PANEL LATERAL DERECHO ========== */
        .feed-aside {
            position: sticky;
            top: 88px;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .aside-card {
            background: var(--blanco);
            border: 1px solid var(--borde-suave);
            border-radius: 14px;
            padding: 18px;
            box-shadow: var(--sombra-card);
        }

        .aside-card h2 {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--azul-oscuro);
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .aside-card h2 i {
            color: var(--naranja);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .stat-tile {
            padding: 12px 8px;
            border-radius: 10px;
            background: var(--fondo-app);
            text-align: center;
        }

        .stat-tile strong {
            display: block;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--azul-oscuro);
            line-height: 1.2;
        }

        .stat-tile span {
            font-size: 0.68rem;
            color: var(--texto-secundario);
            font-weight: 500;
        }

        .area-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .area-list li {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px;
            border-radius: 10px;
            transition: var(--transicion);
        }

        .area-list li:hover {
            background: var(--fondo-app);
        }

        .area-dot {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: var(--azul-medio);
            color: var(--blanco);
            font-size: 0.72rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .area-dot.naranja {
            background: var(--naranja);
        }

        .area-name {
            flex: 1;
            font-size: 0.87rem;
            font-weight: 500;
            color: var(--texto-principal);
        }

        .area-count {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--texto-secundario);
            background: var(--fondo-app);
            padding: 2px 9px;
            border-radius: 20px;
        }

        /* ========== LIGHTBOX ========== */
        .lightbox-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(15, 23, 42, 0.88);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            cursor: zoom-out;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .lightbox-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .lightbox-content {
            position: relative;
            max-width: 90vw;
            max-height: 90vh;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            transform: scale(0.96);
            transition: transform 0.3s ease;
            cursor: default;
        }

        .lightbox-overlay.active .lightbox-content {
            transform: scale(1);
        }

        .lightbox-content img {
            display: block;
            max-width: 90vw;
            max-height: 85vh;
            object-fit: contain;
        }

        .lightbox-content iframe {
            display: block;
            width: min(85vw, 1100px);
            aspect-ratio: 16 / 9;
            border: 0;
        }

        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 24px;
            width: 46px;
            height: 46px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: var(--blanco);
            font-size: 1.3rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transicion);
        }

        .lightbox-close:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        /* ========== MODAL PUBLICAR ========== */
        .modal-publicar-overlay {
            position: fixed;
            inset: 0;
            z-index: 9998;
            background: rgba(26, 42, 95, 0.55);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }

        .modal-publicar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-publicar {
            width: 100%;
            max-width: 620px;
            max-height: 92vh;
            overflow-y: auto;
            background: var(--blanco);
            border-radius: 16px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }

        .modal-publicar-overlay.active .modal-publicar {
            transform: translateY(0);
        }

        .modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 28px;
            border-bottom: 1px solid var(--borde-suave);
        }

        .modal-head h3 {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--azul-oscuro);
        }

        .modal-head h3 i {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--naranja-suave);
            color: var(--naranja);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-x {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 8px;
            background: transparent;
            color: var(--texto-secundario);
            font-size: 1.05rem;
            cursor: pointer;
            transition: var(--transicion);
        }

        .modal-x:hover {
            background: var(--fondo-app);
            color: var(--texto-principal);
        }

        #formPublicar {
            padding: 24px 28px 28px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--texto-principal);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid var(--borde-suave);
            border-radius: 10px;
            background: var(--fondo-app);
            font-family: 'Poppins', sans-serif;
            font-size: 0.92rem;
            color: var(--texto-principal);
            transition: var(--transicion);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            background: var(--blanco);
            border-color: var(--naranja);
            box-shadow: 0 0 0 3px rgba(214, 126, 41, 0.12);
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-hint {
            display: block;
            margin-top: 6px;
            font-size: 0.78rem;
            color: var(--texto-secundario);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding-top: 20px;
            border-top: 1px solid var(--borde-suave);
            margin-top: 6px;
        }

        .btn-cancelar,
        .btn-enviar {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 22px;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transicion);
        }

        .btn-cancelar {
            border: 1px solid var(--borde-suave);
            background: var(--blanco);
            color: var(--texto-secundario);
        }

        .btn-cancelar:hover {
            background: var(--fondo-app);
            color: var(--texto-principal);
        }

        .btn-enviar {
            border: none;
            background: var(--azul-oscuro);
            color: var(--blanco);
        }

        .btn-enviar:hover {
            background: var(--azul-medio);
        }

        /* ========== TOAST ========== */
        .like-toast {
            position: fixed;
            bottom: 28px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 22px;
            border-radius: 10px;
            background: var(--azul-like);
            color: var(--blanco);
            font-size: 0.88rem;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.25);
            pointer-events: none;
            transition: transform 0.35s cubic-bezier(0.17, 0.89, 0.32, 1.2);
        }

        .like-toast.show {
            transform: translateX(-50%) translateY(0);
        }

        /* ========== ANIMACIÓN DE ENTRADA ========== */
        @keyframes feedFadeIn {
            from {
                opacity: 0;
                transform: translateY(14px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 1100px) {
            .feed-layout {
                grid-template-columns: minmax(0, 1fr);
            }

            .feed-aside {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .feed-page-head {
                align-items: stretch;
            }

            .btn-publicar {
                width: 100%;
                justify-content: center;
            }

            .pin-flag {
                padding: 7px 16px;
            }

            .feed-head-actions {
                width: 100%;
            }

            .feed-date {
                display: none;
            }

            .composer-chip {
                flex: 1;
                justify-content: center;
            }

            .post-card-header {
                padding: 16px 12px 0 16px;
            }

            .post-card-body {
                padding: 14px 16px 16px;
            }

            .post-card-footer {
                padding: 12px 16px;
            }

            .gallery-2 {
                height: 210px;
            }

            .gallery-3 {
                height: 250px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .modal-head,
            #formPublicar {
                padding-left: 20px;
                padding-right: 20px;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard">

        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        {{-- HEADER --}}
        <header class="header">
            <div class="header-left">
                <button class="menu-toggle" id="menuToggle" type="button" aria-label="Alternar menú"
                    aria-controls="sidebar" aria-expanded="false">
                    <i class="fas fa-bars"></i>
                </button>
                <button type="button" class="brand-container" id="brandToggle" aria-label="Contraer menú"
                    title="Contraer menú">
                    <img src="{{ asset('assets/img/logovinco1.png') }}" alt="Vinco Hub"
                        class="brand-logo brand-logo--full" />
                    <img src="{{ asset('assets/img/logo.png') }}" alt="Vinco Hub" class="brand-logo brand-logo--mini" />
                </button>
            </div>
            <div class="header-right">
                @include('components.layouts._user-profile')
            </div>
        </header>

        {{-- SIDEBAR --}}
        <aside class="sidebar" id="sidebar" aria-label="Menú principal">
            @php
            $userPermissions = Auth::check()
            ? \App\Models\Systems\UserManagement\UserPermission::getUserPermissions(Auth::id())
            : [];

            // Definición del menú: grupo => título, icono e ítems (key = permiso)
            $menu = [
            'administration' => [
            'title' => 'Administración',
            'icon' => 'fa-building-columns',
            'items' => [
            ['key' => 'expense-claims', 'route' => '/administration/expense-claims', 'label' => 'Reembolsos', 'icon' => 'fa-hand-holding-dollar'],
            ['key' => 'facturacion', 'route' => '/administration', 'label' => 'Facturación', 'icon' => 'fa-file-invoice-dollar'],
            ],
            ],
            'rh' => [
            'title' => 'Recursos Humanos',
            'icon' => 'fa-users',
            'items' => [
            ['key' => 'loadchart', 'route' => '/rh/loadchart', 'label' => 'Mi LoadChart', 'icon' => 'fa-chart-column'],
            ['key' => 'orgmanagement', 'route' => '/rh/orgmanagement', 'label' => 'Empleados', 'icon' => 'fa-address-card'],
            ],
            ],
            'qhse' => [
            'title' => 'QHSE',
            'icon' => 'fa-shield-halved',
            'items' => [
            ['key' => 'management', 'route' => '/qhse/management', 'label' => 'VesDrive', 'icon' => 'fa-route'],
            ['key' => 'incidents', 'route' => '/qhse/incidents', 'label' => 'Mis Incidencias', 'icon' => 'fa-triangle-exclamation'],
            ['key' => 'vescap', 'route' => '/qhse/vescap', 'label' => 'VesCap', 'icon' => 'fa-helmet-safety'],
            ],
            ],
            'systems' => [
            'title' => 'Sistemas',
            'icon' => 'fa-network-wired',
            'items' => [
            ['key' => 'user-management', 'route' => '/systems/user-management', 'label' => 'Roles y Permisos', 'icon' => 'fa-user-lock'],
            ['key' => 'inventory-it', 'route' => '/systems/inventory-it', 'label' => 'Inventario TI', 'icon' => 'fa-boxes-stacked'],
            ['key' => 'tickets', 'route' => '/systems/tickets', 'label' => 'Mis Tickets', 'icon' => 'fa-headset'],
            ],
            ],
            'operations' => [
            'title' => 'Operaciones',
            'icon' => 'fa-gears',
            'items' => [
            ['key' => 'wells', 'route' => '/operations/wells', 'label' => 'Pozos', 'icon' => 'fa-oil-well'],
            ['key' => 'sgc-ves', 'route' => '/operations/sgc-ves', 'label' => 'SGC VES', 'icon' => 'fa-award'],
            ],
            ],
            ];
            @endphp

            <nav class="sidebar-nav" id="sidebarNav">

                @foreach ($menu as $groupKey => $group)
                @if (isset($userPermissions[$groupKey]))
                @php $allowed = $userPermissions[$groupKey]; @endphp
                <div class="nav-group" data-group="{{ $groupKey }}">
                    <button type="button" class="nav-header" aria-expanded="true"
                        aria-controls="nav-{{ $groupKey }}">
                        <span class="nav-header-title">
                            <i class="fas {{ $group['icon'] }}"></i>
                            <span>{{ $group['title'] }}</span>
                        </span>
                        <i class="fas fa-chevron-down arrow-icon"></i>
                    </button>
                    <div class="nav-collapse" id="nav-{{ $groupKey }}">
                        <ul class="nav-list">
                            @foreach ($group['items'] as $item)
                            @if (in_array($item['key'], $allowed) || empty($allowed))
                            <li>
                                <a class="nav-item" href="{{ url($item['route']) }}"
                                    data-route="{{ $item['route'] }}" data-name="{{ $item['label'] }}">
                                    <i class="fas {{ $item['icon'] }} nav-icon"></i>
                                    <span class="nav-text">{{ $item['label'] }}</span>
                                </a>
                            </li>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
                @endforeach
            </nav>
        </aside>


        {{-- MAIN CONTENT
        <main class="main-content">
            <div class="content-wrapper">

                @hasSection('content')
                @yield('content')
                @else

                @php
                /*
                |--------------------------------------------------------------------------
                | Datos de ejemplo (reemplazar por tu consulta real: $posts del controlador)
                |--------------------------------------------------------------------------
                */
                $initials = Auth::check()
                ? collect(explode(' ', trim((string) (Auth::user()->name ?? ''))))->filter()->take(2)
                ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('')
                : '';
                $initials = $initials ?: 'VH';

                $tipos = [
                'aviso' => ['label' => 'Aviso', 'icon' => 'fa-circle-exclamation', 'plural' => 'Avisos'],
                'noticia' => ['label' => 'Noticia', 'icon' => 'fa-newspaper', 'plural' => 'Noticias'],
                'folleto' => ['label' => 'Folleto', 'icon' => 'fa-file-pdf', 'plural' => 'Folletos'],
                'video' => ['label' => 'Video', 'icon' => 'fa-circle-play', 'plural' => 'Videos'],
                ];

                $posts = [
                [
                'type' => 'aviso',
                'area' => 'Dirección General',
                'initials' => 'DG',
                'accent' => true,
                'author' => 'Ing. Carlos Mendoza',
                'time' => 'Hace 2 horas',
                'pinned' => true,
                'icon' => 'fa-wrench',
                'title' => 'Mantenimiento programado – VesDrive',
                'excerpt' => 'El próximo <strong>sábado 15 de julio</strong> de 22:00 a 04:00 hrs se realizará mantenimiento a <strong>VesDrive</strong>. El servicio no estará disponible. Planifique sus registros con anticipación.',
                'media' => null,
                'likes' => 15,
                'liked' => false,
                'likers' => ['JD', 'MR'],
                ],
                [
                'type' => 'noticia',
                'area' => 'Recursos Humanos',
                'initials' => 'RH',
                'accent' => false,
                'author' => 'Lic. Ana Lucía Rojas',
                'time' => 'Ayer a las 16:30',
                'pinned' => false,
                'icon' => 'fa-heart-circle-check',
                'title' => 'Nuevo Programa de Bienestar Laboral 2026',
                'excerpt' => 'Lanzamos <strong>"Vinco Vital"</strong>, programa integral de bienestar con horarios flexibles, mindfulness y gimnasios afiliados. Disfruta de las nuevas áreas renovadas. Inscripciones abiertas desde el próximo lunes.',
                'media' => [
                'kind' => 'gallery',
                'extra' => 2,
                'images' => [
                'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=800&q=80',
                'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=400&q=80',
                'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=400&q=80',
                ],
                ],
                'likes' => 37,
                'liked' => true,
                'likers' => ['AL', 'CG'],
                ],
                [
                'type' => 'video',
                'area' => 'QHSE',
                'initials' => 'QH',
                'accent' => true,
                'author' => 'Ing. Luis Paredes',
                'time' => 'Hace 3 días',
                'pinned' => false,
                'icon' => 'fa-shield-halved',
                'title' => 'Protocolo de Respuesta a Emergencias – Simulacro 2026',
                'excerpt' => 'Revive el simulacro de emergencia. Conoce protocolos actualizados, tiempos de respuesta y mejores prácticas.',
                'media' => [
                'kind' => 'video',
                'url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ?autoplay=1',
                'thumb' => 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=800&h=450&fit=crop',
                'duration' => '04:32',
                ],
                'likes' => 11,
                'liked' => false,
                'likers' => ['LP', 'FT'],
                ],
                [
                'type' => 'folleto',
                'area' => 'Operaciones',
                'initials' => 'OP',
                'accent' => false,
                'author' => 'Ing. Ricardo Muñoz',
                'time' => 'Hace 5 días',
                'pinned' => false,
                'icon' => 'fa-book-open',
                'title' => 'Manual de Operaciones – Actualización Q2 2026',
                'excerpt' => 'Nueva versión del Manual con procedimientos actualizados, seguridad operativa y eficiencia energética.',
                'media' => [
                'kind' => 'file',
                'name' => 'Manual_Operaciones_Q2_2026.pdf',
                'meta' => '4.2 MB · 128 páginas',
                ],
                'likes' => 8,
                'liked' => false,
                'likers' => ['RM'],
                ],
                [
                'type' => 'noticia',
                'area' => 'Dirección General',
                'initials' => 'DG',
                'accent' => true,
                'author' => 'Mtro. Jorge Salinas',
                'time' => 'Hace 1 semana',
                'pinned' => false,
                'icon' => 'fa-solar-panel',
                'title' => 'Vinco Energy invertirá $12M en energía solar para 2027',
                'excerpt' => 'Inversión histórica en parques solares que abastecerán el 40% de nuestras operaciones para finales de 2027.',
                'media' => [
                'kind' => 'image',
                'src' => 'https://images.unsplash.com/photo-1466611653911-95081537e5b7?w=800&h=400&fit=crop',
                'alt' => 'Energía renovable',
                ],
                'likes' => 55,
                'liked' => false,
                'likers' => ['JS', 'MG'],
                ],
                ];

                // Fijados primero
                $posts = collect($posts)->sortByDesc(fn ($p) => !empty($p['pinned']) ? 1 : 0)->values()->all();

                $areas = [
                ['name' => 'Dirección General', 'initials' => 'DG', 'accent' => true],
                ['name' => 'Recursos Humanos', 'initials' => 'RH', 'accent' => false],
                ['name' => 'QHSE', 'initials' => 'QH', 'accent' => true],
                ['name' => 'Operaciones', 'initials' => 'OP', 'accent' => false],
                ];
                @endphp

                <div class="social-feed-wrapper">

                    <div class="feed-page-head">
                        <div class="feed-page-title">
                            <div class="feed-brand-icon">
                                <img src="{{ asset('assets/img/logo.png') }}" alt="Vinco Energy" />
                            </div>
                            <div>
                                <h1>Vinco Energy Service</h1>
                                <p>Comunicados, noticias y recursos de toda la organización</p>
                            </div>
                        </div>
                        <div class="feed-head-actions">
                            <span class="feed-date" id="feedDate"><i class="far fa-calendar"></i> <span></span></span>
                            <button class="btn-publicar" id="btnAbrirModalPublicar" type="button">
                                <i class="fas fa-pen-to-square"></i> Nueva publicación
                            </button>
                        </div>
                    </div>

                    <div class="feed-layout">

                        <section class="feed-main" aria-label="Publicaciones">

                            <div class="feed-composer">
                                <div class="composer-top">
                                    <div class="post-avatar composer-avatar">{{ $initials }}</div>
                                    <button type="button" class="composer-input" data-compose="">
                                        ¿Qué quieres compartir con el equipo?
                                    </button>
                                </div>
                                <div class="composer-actions">
                                    @foreach ($tipos as $clave => $tipo)
                                    <button type="button" class="composer-chip" data-compose="{{ $clave }}">
                                        <i class="fas {{ $tipo['icon'] }}" style="color: var(--tipo-{{ $clave }});"></i>
                                        {{ $tipo['label'] }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>

                            <div class="feed-toolbar">
                                <div class="feed-search">
                                    <i class="fas fa-magnifying-glass"></i>
                                    <input type="search" id="feedSearch" placeholder="Buscar por título, área o contenido..."
                                        aria-label="Buscar publicaciones" autocomplete="off" />
                                </div>

                                <div class="feed-filters" id="feedFilters" role="tablist"
                                    aria-label="Filtrar por tipo">
                                    <button class="feed-filter-chip active" data-filter="all" type="button"
                                        role="tab" aria-selected="true">
                                        <i class="fas fa-border-all"></i> Todo
                                        <span class="chip-count" data-count-for="all">0</span>
                                    </button>
                                    @foreach ($tipos as $clave => $tipo)
                                    <button class="feed-filter-chip" data-filter="{{ $clave }}" type="button"
                                        role="tab" aria-selected="false">
                                        <i class="fas {{ $tipo['icon'] }}"></i> {{ $tipo['plural'] }}
                                        <span class="chip-count" data-count-for="{{ $clave }}">0</span>
                                    </button>
                                    @endforeach
                                </div>
                            </div>

                            <div class="feed-grid" id="feedGrid">
                                @foreach ($posts as $post)
                                @php
                                $tipo = $tipos[$post['type']];
                                $media = $post['media'];
                                $extraLikers = max(0, $post['likes'] - count($post['likers']));
                                @endphp

                                <article class="post-card {{ !empty($post['pinned']) ? 'is-pinned' : '' }}"
                                    data-type="{{ $post['type'] }}" data-area="{{ $post['area'] }}">

                                    <div class="pin-flag"><i class="fas fa-thumbtack"></i> Publicación fijada</div>

                                    <div class="post-card-header">
                                        <div class="post-avatar {{ $post['accent'] ? 'avatar-naranja' : '' }}">
                                            {{ $post['initials'] }}</div>
                                        <div class="post-meta">
                                            <div class="post-area">{{ $post['area'] }}</div>
                                            <div class="post-author">{{ $post['author'] }}</div>
                                            <div class="post-time">
                                                <span><i class="far fa-clock"></i> {{ $post['time'] }}</span>
                                                <span class="dot-separator"></span>
                                                <span class="inline-tag tag-{{ $post['type'] }}">
                                                    <i class="fas {{ $tipo['icon'] }}"></i> {{ $tipo['label'] }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="post-options">
                                            <button class="btn-options" type="button" aria-label="Opciones"
                                                aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="post-dropdown" role="menu">
                                                <ul>
                                                    <li><button type="button" role="menuitem" data-action="edit"><i
                                                                class="fas fa-pen"></i> Editar</button></li>
                                                    <li><button type="button" role="menuitem" data-action="pin"><i
                                                                class="fas fa-thumbtack"></i>
                                                            <span
                                                                class="pin-label">{{ !empty($post['pinned']) ? 'Quitar fijado' : 'Fijar' }}</span></button>
                                                    </li>
                                                    <li class="menu-divider" role="separator"></li>
                                                    <li><button type="button" role="menuitem"
                                                            class="delete-option" data-action="delete"><i
                                                                class="fas fa-trash-can"></i> Eliminar</button></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="post-card-body">
                                        <h3 class="post-title">
                                            <i class="fas {{ $post['icon'] }}"
                                                style="color: var(--tipo-{{ $post['type'] }});"></i>
                                            <span>{{ $post['title'] }}</span>
                                        </h3>
                                        <p class="post-excerpt">{!! $post['excerpt'] !!}</p>

                                        @if ($media && $media['kind'] === 'file')
                                        <div class="post-folleto-card" role="button" tabindex="0">
                                            <div class="folleto-icon-box"><i class="fas fa-file-pdf"></i></div>
                                            <div class="folleto-info">
                                                <strong>{{ $media['name'] }}</strong>
                                                <span>{{ $media['meta'] }}</span>
                                            </div>
                                            <div class="folleto-download"><i class="fas fa-download"></i></div>
                                        </div>
                                        @endif
                                    </div>

                                    @if ($media && $media['kind'] === 'image')
                                    <div class="post-media" data-lightbox style="margin-top:0;">
                                        <img src="{{ $media['src'] }}" alt="{{ $media['alt'] }}" loading="lazy" />
                                        <div class="post-media-overlay"><i class="fas fa-expand"></i></div>
                                    </div>
                                    @elseif ($media && $media['kind'] === 'gallery')
                                    @php $total = count($media['images']); @endphp
                                    <div class="post-media-gallery gallery-{{ $total }}" style="margin-top:0;">
                                        @foreach ($media['images'] as $idx => $src)
                                        <div class="gallery-item" data-lightbox>
                                            <img src="{{ $src }}" alt="Imagen {{ $idx + 1 }}" loading="lazy" />
                                            @if ($loop->last && !empty($media['extra']))
                                            <div class="overlay-more">+{{ $media['extra'] }}</div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    @elseif ($media && $media['kind'] === 'video')
                                    <div class="post-video-wrapper" data-video-url="{{ $media['url'] }}" role="button"
                                        tabindex="0" aria-label="Reproducir video" style="margin-top:0;">
                                        <img src="{{ $media['thumb'] }}" alt="Miniatura del video" loading="lazy" />
                                        <div class="video-play-overlay">
                                            <div class="play-circle"><i class="fas fa-play"></i></div>
                                        </div>
                                        <span class="video-duration">{{ $media['duration'] }}</span>
                                    </div>
                                    @endif

                                    <div class="post-card-footer">
                                        <div class="post-likes-info">
                                            <div class="post-likes-avatars">
                                                @foreach ($post['likers'] as $liker)
                                                <span class="like-avatar-mini">{{ $liker }}</span>
                                                @endforeach
                                                @if ($extraLikers > 0)
                                                <span class="like-avatar-mini more">+{{ $extraLikers }}</span>
                                                @endif
                                            </div>
                                            <span class="likes-text">{{ $post['likes'] }} personas</span>
                                        </div>
                                        <button type="button"
                                            class="btn-like {{ $post['liked'] ? 'liked' : '' }}"
                                            aria-pressed="{{ $post['liked'] ? 'true' : 'false' }}">
                                            <i class="{{ $post['liked'] ? 'fas' : 'far' }} fa-thumbs-up"></i>
                                            <span class="like-label">{{ $post['liked'] ? 'Te gusta' : 'Me gusta' }}</span>
                                        </button>
                                    </div>
                                </article>
                                @endforeach
                            </div>

                            <div class="feed-empty" id="feedEmpty">
                                <i class="fas fa-inbox"></i>
                                <h3>Sin resultados</h3>
                                <p>No encontramos publicaciones con ese filtro o búsqueda.</p>
                            </div>

                            <div class="feed-load-more">
                                <button class="btn-load-more" id="btnLoadMore" type="button">
                                    <i class="fas fa-chevron-down"></i> Ver más publicaciones
                                </button>
                            </div>
                        </section>

                        <aside class="feed-aside" aria-label="Resumen">
                            <div class="aside-card">
                                <h2><i class="fas fa-chart-simple"></i> Resumen</h2>
                                <div class="stats-grid">
                                    <div class="stat-tile"><strong id="statPosts">0</strong><span>Publicaciones</span>
                                    </div>
                                    <div class="stat-tile"><strong id="statAvisos">0</strong><span>Avisos</span></div>
                                    <div class="stat-tile"><strong id="statLikes">0</strong><span>Reacciones</span>
                                    </div>
                                </div>
                            </div>

                            <div class="aside-card">
                                <h2><i class="fas fa-building"></i> Áreas que publican</h2>
                                <ul class="area-list">
                                    @foreach ($areas as $area)
                                    <li>
                                        <span class="area-dot {{ $area['accent'] ? 'naranja' : '' }}">{{ $area['initials'] }}</span>
                                        <span class="area-name">{{ $area['name'] }}</span>
                                        <span class="area-count" data-area-count="{{ $area['name'] }}">0</span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </aside>
                    </div>
                </div>

                <div class="lightbox-overlay" id="lightboxOverlay" role="dialog" aria-modal="true"
                    aria-label="Visor multimedia">
                    <button class="lightbox-close" id="lightboxClose" type="button" aria-label="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="lightbox-content" id="lightboxContent"></div>
                </div>

                <div class="modal-publicar-overlay" id="modalPublicarOverlay">
                    <div class="modal-publicar" role="dialog" aria-modal="true" aria-labelledby="modalTitulo">
                        <div class="modal-head">
                            <h3 id="modalTitulo"><i class="fas fa-pen-to-square"></i> Nueva publicación</h3>
                            <button type="button" class="modal-x" id="btnCerrarModal" aria-label="Cerrar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <form id="formPublicar" novalidate>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="postTipo">Tipo de publicación</label>
                                    <select id="postTipo" required>
                                        <option value="">Selecciona un tipo...</option>
                                        @foreach ($tipos as $clave => $tipo)
                                        <option value="{{ $clave }}">{{ $tipo['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="postArea">Área</label>
                                    <select id="postArea" required>
                                        <option value="">Selecciona tu área...</option>
                                        <option value="Dirección General">Dirección General</option>
                                        <option value="Recursos Humanos">Recursos Humanos</option>
                                        <option value="QHSE">QHSE</option>
                                        <option value="Operaciones">Operaciones</option>
                                        <option value="Tecnología">Tecnología</option>
                                        <option value="Marketing">Marketing</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="postTitulo">Título</label>
                                <input type="text" id="postTitulo" placeholder="Escribe un título claro y conciso"
                                    required />
                            </div>
                            <div class="form-group">
                                <label for="postContenido">Contenido</label>
                                <textarea id="postContenido" rows="4" placeholder="Describe tu publicación aquí..."
                                    required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="postArchivo">Adjuntar archivos (opcional)</label>
                                <input type="file" id="postArchivo" multiple accept="image/*,video/*,.pdf,.doc,.docx" />
                                <span class="form-hint" id="archivoHint">Puedes seleccionar más de una imagen.</span>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn-cancelar" id="btnCancelarPublicacion">Cancelar</button>
                                <button type="submit" class="btn-enviar">
                                    <i class="fas fa-paper-plane"></i> Publicar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="like-toast" id="likeToast" role="status" aria-live="polite">
                    <i class="fas fa-thumbs-up"></i> <span id="likeToastMsg"></span>
                </div>
                @endif

            </div>
        </main>
        --}}
    </div>

    {{-- SCRIPTS --}}
    @if (session('swal'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '{{ session('swal')['icon'] }}',
                    title: '{{ session('swal')['title'] }}',
                    text: '{{ session('swal')['text'] }}',
                    timer: {{ session('swal')['timer'] ?? 4000 }},
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                });
            });
    </script>
    @endif

    <script src="{{ asset('assets/js/sessionTimer.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            /* ========== SIDEBAR + HEADER ========== */
            const root = document.documentElement;
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const menuToggle = document.getElementById('menuToggle');
            const brandToggle = document.getElementById('brandToggle');
            const isDesktop = () => window.innerWidth > 768;
            const store = {
                get: (k) => { try { return localStorage.getItem(k); } catch (e) { return null; } },
                set: (k, v) => { try { localStorage.setItem(k, v); } catch (e) {} }
            };

            /* --- Tooltip flotante (modo contraído) --- */
            const tip = document.createElement('div');
            tip.className = 'sidebar-tooltip';
            tip.setAttribute('role', 'tooltip');
            document.body.appendChild(tip);

            const hideTip = () => tip.classList.remove('show');
            const showTip = (el) => {
                if (!isDesktop() || !root.classList.contains('sidebar-minimized')) return;
                const r = el.getBoundingClientRect();
                tip.textContent = el.dataset.name || '';
                tip.style.top = (r.top + r.height / 2) + 'px';
                tip.style.left = (r.right + 12) + 'px';
                tip.classList.add('show');
            };

            sidebar.addEventListener('mouseover', (e) => {
                const el = e.target.closest('[data-name]');
                el ? showTip(el) : hideTip();
            });
            sidebar.addEventListener('mouseleave', hideTip);
            sidebar.addEventListener('focusin', (e) => {
                const el = e.target.closest('[data-name]');
                if (el) showTip(el);
            });
            sidebar.addEventListener('focusout', hideTip);
            sidebar.addEventListener('scroll', hideTip, true);

            /* --- Contraer / expandir (escritorio) --- */
            function setMinimized(min) {
                [root, document.body].forEach(el => el.classList.toggle('sidebar-minimized', min));
                store.set('vh.sidebarMin', min ? '1' : '0');
                if (brandToggle) {
                    const label = min ? 'Expandir menú' : 'Contraer menú';
                    brandToggle.setAttribute('aria-label', label);
                    brandToggle.title = label;
                }
                hideTip();
            }

            if (store.get('vh.sidebarMin') === '1' && isDesktop()) setMinimized(true);

            // El logo contrae/expande el menú (solo escritorio)
            brandToggle?.addEventListener('click', () => {
                if (isDesktop()) setMinimized(!root.classList.contains('sidebar-minimized'));
            });

            /* --- Drawer móvil --- */
            function setDrawer(open) {
                sidebar.classList.toggle('open', open);
                sidebarOverlay.classList.toggle('show', open);
                menuToggle.setAttribute('aria-expanded', String(open));
                document.body.style.overflow = open ? 'hidden' : '';
            }

            // El botón del header solo se muestra en móvil (abre/cierra el drawer)
            menuToggle?.addEventListener('click', () => setDrawer(!sidebar.classList.contains('open')));
            sidebarOverlay?.addEventListener('click', () => setDrawer(false));
            window.addEventListener('resize', () => {
                if (isDesktop() && sidebar.classList.contains('open')) setDrawer(false);
            });

            /* --- Grupos colapsables (siempre inician abiertos) --- */
            try { localStorage.removeItem('vh.navGroups'); } catch (e) {}

            const setGroup = (group, collapsed) => {
                group.classList.toggle('is-collapsed', collapsed);
                group.querySelector('.nav-header').setAttribute('aria-expanded', String(!collapsed));
            };

            document.querySelectorAll('.nav-group').forEach(group => {
                setGroup(group, false);
                group.querySelector('.nav-header').addEventListener('click', () => {
                    setGroup(group, !group.classList.contains('is-collapsed'));
                });
            });

            /* --- Ítem activo: gana la ruta más específica --- */
            const path = window.location.pathname.replace(/\/+$/, '') || '/';
            let best = null;
            document.querySelectorAll('.nav-item[data-route]').forEach(item => {
                const route = item.dataset.route;
                const match = route === '/' ? path === '/' : (path === route || path.startsWith(route + '/'));
                if (match && (!best || route.length > best.dataset.route.length)) best = item;
            });
            if (best) {
                best.classList.add('active');
                best.setAttribute('aria-current', 'page');
                const group = best.closest('.nav-group');
                if (group) setGroup(group, false);
            }

            /* --- Cerrar drawer al navegar / con Escape --- */
            sidebar.addEventListener('click', (e) => {
                if (e.target.closest('.nav-item') && !isDesktop()) setDrawer(false);
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && sidebar.classList.contains('open')) setDrawer(false);
            });

            /* ========== FEED ========== */
            const feedGrid = document.getElementById('feedGrid');
            if (!feedGrid) return; // La vista está usando @section('content')

            const feedEmpty = document.getElementById('feedEmpty');
            const feedSearch = document.getElementById('feedSearch');
            const filterChips = document.querySelectorAll('.feed-filter-chip');
            const state = {
                filter: 'all',
                query: ''
            };

            const getPosts = () => Array.from(feedGrid.querySelectorAll('.post-card'));
            const normalize = (s) => (s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

            /* --- Toast --- */
            const likeToast = document.getElementById('likeToast');
            const likeToastMsg = document.getElementById('likeToastMsg');
            let toastTimer;

            function showToast(message, active = true) {
                likeToastMsg.textContent = message;
                likeToast.style.background = active ? 'var(--azul-like)' : '#64748b';
                likeToast.querySelector('i').className = active ? 'fas fa-thumbs-up' : 'far fa-thumbs-up';
                likeToast.classList.add('show');
                clearTimeout(toastTimer);
                toastTimer = setTimeout(() => likeToast.classList.remove('show'), 1800);
            }

            /* --- Contadores (chips, resumen, áreas) --- */
            function updateCounts() {
                const posts = getPosts();
                const byType = {};
                const byArea = {};
                let likes = 0;

                posts.forEach(p => {
                    byType[p.dataset.type] = (byType[p.dataset.type] || 0) + 1;
                    byArea[p.dataset.area] = (byArea[p.dataset.area] || 0) + 1;
                    likes += parseInt(p.querySelector('.btn-like')?.dataset.total || p.querySelector(
                        '.likes-text')?.textContent.match(/\d+/)?.[0] || 0, 10);
                });

                document.querySelectorAll('[data-count-for]').forEach(el => {
                    const key = el.dataset.countFor;
                    el.textContent = key === 'all' ? posts.length : (byType[key] || 0);
                });
                document.querySelectorAll('[data-area-count]').forEach(el => {
                    el.textContent = byArea[el.dataset.areaCount] || 0;
                });

                const set = (id, val) => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = val;
                };
                set('statPosts', posts.length);
                set('statAvisos', byType['aviso'] || 0);
                set('statLikes', likes);
            }

            /* --- Filtros + búsqueda --- */
            function applyFilters() {
                const q = normalize(state.query.trim());
                let visible = 0;

                getPosts().forEach(post => {
                    const matchType = state.filter === 'all' || post.dataset.type === state.filter;
                    const matchText = !q || normalize(post.textContent).includes(q);
                    const show = matchType && matchText;

                    post.style.display = show ? '' : 'none';
                    if (show) {
                        post.style.animation = `feedFadeIn 0.4s ease-out ${visible * 0.05}s both`;
                        visible++;
                    } else {
                        post.style.animation = '';
                    }
                });

                feedEmpty.classList.toggle('show', visible === 0);
            }

            filterChips.forEach(chip => {
                chip.addEventListener('click', function() {
                    filterChips.forEach(c => {
                        c.classList.remove('active');
                        c.setAttribute('aria-selected', 'false');
                    });
                    this.classList.add('active');
                    this.setAttribute('aria-selected', 'true');
                    state.filter = this.dataset.filter;
                    applyFilters();
                });
            });

            let searchTimer;
            feedSearch?.addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    state.query = this.value;
                    applyFilters();
                }, 160);
            });

            /* --- Menú de opciones --- */
            function closeAllMenus(except = null) {
                document.querySelectorAll('.post-dropdown.show').forEach(menu => {
                    if (menu === except) return;
                    menu.classList.remove('show');
                    menu.previousElementSibling?.setAttribute('aria-expanded', 'false');
                });
            }

            document.addEventListener('click', (e) => {
                const optionsBtn = e.target.closest('.btn-options');
                if (optionsBtn) {
                    const dropdown = optionsBtn.nextElementSibling;
                    closeAllMenus(dropdown);
                    const isOpen = dropdown.classList.toggle('show');
                    optionsBtn.setAttribute('aria-expanded', String(isOpen));
                    return;
                }

                const actionBtn = e.target.closest('.post-dropdown [data-action]');
                if (actionBtn) {
                    handleMenuAction(actionBtn);
                }
                closeAllMenus();
            });

            function handleMenuAction(btn) {
                const card = btn.closest('.post-card');
                const action = btn.dataset.action;

                if (action === 'edit') {
                    showToast('Edición disponible próximamente', true);
                }

                if (action === 'pin') {
                    const pinned = card.classList.toggle('is-pinned');
                    btn.querySelector('.pin-label').textContent = pinned ? 'Quitar fijado' : 'Fijar';
                    if (pinned) feedGrid.prepend(card);
                    showToast(pinned ? 'Publicación fijada' : 'Publicación desfijada', pinned);
                }

                if (action === 'delete') {
                    Swal.fire({
                        title: '¿Eliminar publicación?',
                        text: 'Esta acción no se puede deshacer.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#64748b',
                        reverseButtons: true,
                    }).then(result => {
                        if (!result.isConfirmed) return;
                        card.classList.add('is-removing');
                        setTimeout(() => {
                            card.remove();
                            updateCounts();
                            applyFilters();
                        }, 250);
                    });
                }
            }

            /* --- Likes --- */
            feedGrid.addEventListener('click', (e) => {
                const btn = e.target.closest('.btn-like');
                if (!btn) return;

                const liked = btn.classList.toggle('liked');
                btn.setAttribute('aria-pressed', String(liked));
                btn.querySelector('i').className = (liked ? 'fas' : 'far') + ' fa-thumbs-up';
                btn.querySelector('.like-label').textContent = liked ? 'Te gusta' : 'Me gusta';

                const text = btn.closest('.post-card-footer').querySelector('.likes-text');
                const current = parseInt(text.textContent.match(/\d+/)?.[0] || 0, 10);
                text.textContent = Math.max(0, current + (liked ? 1 : -1)) + ' personas';

                showToast(liked ? '¡Te gusta esta publicación!' : 'Has quitado tu like', liked);
                updateCounts();
            });

            /* --- Lightbox --- */
            const lightboxOverlay = document.getElementById('lightboxOverlay');
            const lightboxContent = document.getElementById('lightboxContent');
            const lightboxClose = document.getElementById('lightboxClose');

            function openLightbox(html) {
                lightboxContent.innerHTML = html;
                lightboxOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
                lightboxClose.focus();
            }

            function cerrarLightbox() {
                lightboxOverlay.classList.remove('active');
                document.body.style.overflow = '';
                setTimeout(() => {
                    lightboxContent.innerHTML = '';
                }, 300);
            }

            feedGrid.addEventListener('click', (e) => {
                const media = e.target.closest('[data-lightbox]');
                if (media) {
                    const img = media.querySelector('img');
                    if (img) openLightbox(`<img src="${img.src}" alt="${img.alt || 'Imagen'}" />`);
                    return;
                }
                const video = e.target.closest('.post-video-wrapper');
                if (video?.dataset.videoUrl) {
                    openLightbox(
                        `<iframe src="${video.dataset.videoUrl}" allowfullscreen allow="autoplay; encrypted-media"></iframe>`
                    );
                }
            });

            feedGrid.addEventListener('keydown', (e) => {
                if (e.key !== 'Enter' && e.key !== ' ') return;
                const target = e.target.closest('.post-video-wrapper, .post-folleto-card');
                if (target) {
                    e.preventDefault();
                    target.click();
                }
            });

            lightboxClose.addEventListener('click', cerrarLightbox);
            lightboxOverlay.addEventListener('click', (e) => {
                if (e.target === lightboxOverlay) cerrarLightbox();
            });

            /* --- Folleto --- */
            feedGrid.addEventListener('click', (e) => {
                const folleto = e.target.closest('.post-folleto-card');
                if (!folleto) return;
                const name = folleto.querySelector('.folleto-info strong')?.textContent.trim() || 'documento';
                Swal.fire({
                    title: 'Descarga iniciada',
                    text: `El archivo "${name}" se descargaría ahora.`,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                });
            });

            /* --- Ver más --- */
            const btnLoadMore = document.getElementById('btnLoadMore');
            btnLoadMore?.addEventListener('click', function() {
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
                this.disabled = true;
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-check"></i> No hay más publicaciones';
                    this.style.borderColor = '#059669';
                    this.style.color = '#059669';
                    setTimeout(() => {
                        this.innerHTML = '<i class="fas fa-chevron-down"></i> Ver más publicaciones';
                        this.disabled = false;
                        this.style.borderColor = '';
                        this.style.color = '';
                    }, 2000);
                }, 1200);
            });

            /* --- Modal publicar --- */
            const modalOverlay = document.getElementById('modalPublicarOverlay');
            const formPublicar = document.getElementById('formPublicar');
            const archivoInput = document.getElementById('postArchivo');
            const archivoHint = document.getElementById('archivoHint');
            const hintDefault = archivoHint.textContent;

            function abrirModal() {
                modalOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
                setTimeout(() => document.getElementById('postTipo').focus(), 150);
            }

            function cerrarModal() {
                modalOverlay.classList.remove('active');
                document.body.style.overflow = '';
                formPublicar.reset();
                archivoHint.textContent = hintDefault;
            }

            document.getElementById('btnAbrirModalPublicar')?.addEventListener('click', abrirModal);
            document.getElementById('btnCancelarPublicacion')?.addEventListener('click', cerrarModal);
            document.getElementById('btnCerrarModal')?.addEventListener('click', cerrarModal);
            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) cerrarModal();
            });

            archivoInput.addEventListener('change', function() {
                const n = this.files.length;
                archivoHint.textContent = n ?
                    (n === 1 ? this.files[0].name : `${n} archivos seleccionados`) :
                    hintDefault;
            });

            formPublicar.addEventListener('submit', function(e) {
                e.preventDefault();
                const tipo = document.getElementById('postTipo').value;
                const area = document.getElementById('postArea').value;
                const titulo = document.getElementById('postTitulo').value.trim();
                const contenido = document.getElementById('postContenido').value.trim();

                if (!tipo || !area || !titulo || !contenido) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Campos incompletos',
                        text: 'Por favor completa todos los campos requeridos.',
                        confirmButtonColor: '#d67e29',
                    });
                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Publicación enviada',
                    text: 'Tu publicación ha sido enviada para revisión.',
                    timer: 2500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                });
                cerrarModal();
            });

            /* --- Composer rápido: abre el modal con el tipo preseleccionado --- */
            document.querySelectorAll('[data-compose]').forEach(btn => {
                btn.addEventListener('click', () => {
                    abrirModal();
                    if (btn.dataset.compose) document.getElementById('postTipo').value = btn.dataset.compose;
                });
            });

            /* --- Fecha de hoy --- */
            const feedDate = document.querySelector('#feedDate span');
            if (feedDate) {
                const txt = new Date().toLocaleDateString('es-MX', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'long'
                });
                feedDate.textContent = txt.charAt(0).toUpperCase() + txt.slice(1);
            }

            /* --- Escape cierra lo que esté abierto --- */
            document.addEventListener('keydown', (e) => {
                if (e.key !== 'Escape') return;
                closeAllMenus();
                if (lightboxOverlay.classList.contains('active')) cerrarLightbox();
                if (modalOverlay.classList.contains('active')) cerrarModal();
            });

            /* --- Init --- */
            updateCounts();
            applyFilters();
        });
    </script>

    @stack('scripts')
</body>

</html>











