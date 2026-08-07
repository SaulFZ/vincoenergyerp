<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>VesCore</title>

    {{-- FontAwesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">

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
            --sombra-card: 0 4px 12px rgba(0, 0, 0, 0.04);
            --transicion: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ========== FEED SOCIAL ========== */
        .social-feed-wrapper {
            max-width: 1300px;
            margin: 0 auto;
            animation: feedFadeIn 0.5s ease-out;
        }

        @keyframes feedFadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* --- Contenedor Superior (1 Sola Fila) --- */
        .feed-top-container {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 16px;
            padding: 16px 24px;
            margin-bottom: 24px;
            box-shadow: var(--sombra-card);
            border: 1px solid var(--borde-suave);
            border-top: 4px solid var(--naranja);
            transition: var(--transicion);

            /* Ajuste para una sola fila */
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .feed-top-container:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
        }

        .feed-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .feed-brand-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
            background: #fff;
            border: 1px solid var(--borde-suave);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .feed-brand-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 4px;
        }

        .feed-header-text h2 {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--azul-oscuro);
            letter-spacing: -0.4px;
            margin: 0;
            line-height: 1.2;
        }

        /* --- Fila de Controles (Filtros a la izq, Botón a la der) --- */
        .feed-controls-wrapper {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            flex: 1;
            justify-content: flex-end;
        }

        .feed-filters {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .feed-filter-chip {
            padding: 8px 16px;
            border-radius: 30px;
            border: 1px solid var(--borde-suave);
            background: #fff;
            color: var(--texto-secundario);
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transicion);
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            font-family: 'Poppins', sans-serif;
        }

        .feed-filter-chip:hover {
            border-color: var(--azul-oscuro);
            color: var(--azul-oscuro);
            background: #f8fafc;
            transform: translateY(-2px);
        }

        .feed-filter-chip.active {
            background: var(--azul-oscuro);
            color: #fff;
            border-color: var(--azul-oscuro);
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(26, 42, 95, 0.15);
        }

        .feed-filter-chip .chip-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .feed-filter-chip:not(.active) .chip-count {
            background: var(--fondo-app);
            color: var(--texto-secundario);
        }

        .btn-publicar {
            padding: 10px 22px;
            border-radius: 30px;
            border: none;
            background: linear-gradient(135deg, var(--naranja) 0%, #e8943f 100%);
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transicion);
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 4px 14px rgba(214, 126, 41, 0.25);
            white-space: nowrap;
        }

        .btn-publicar:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(214, 126, 41, 0.35);
        }

        /* --- Grid --- */
        .feed-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        /* --- Tarjeta --- */
        .post-card {
            background: var(--blanco);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--sombra-card);
            border: 1px solid var(--borde-suave);
            transition: var(--transicion);
            position: relative;
        }

        .post-card:hover {
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
            border-color: #cbd5e1;
        }

        .post-card-header {
            padding: 20px 24px 0;
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .post-avatar {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--azul-medio) 0%, var(--azul-claro) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 0.95rem;
            flex-shrink: 0;
            letter-spacing: -0.3px;
            box-shadow: 0 3px 8px rgba(59, 87, 189, 0.25);
            margin-top: 2px;
        }

        .post-avatar.avatar-naranja {
            background: linear-gradient(135deg, var(--naranja) 0%, #f0a54a 100%);
            box-shadow: 0 3px 8px rgba(214, 126, 41, 0.25);
        }

        .post-meta {
            flex: 1;
            min-width: 0;
        }

        /* Jerarquía Superior (Área y Nombre) */
        .post-area {
            font-weight: 700;
            color: var(--texto-principal);
            font-size: 1rem;
            letter-spacing: -0.2px;
            line-height: 1.2;
        }

        .post-author {
            font-size: 0.85rem;
            color: var(--texto-secundario);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 2px;
        }

        .post-author i {
            font-size: 0.75rem;
        }

        .post-time {
            font-size: 0.8rem;
            color: var(--texto-secundario);
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 6px;
        }

        /* --- Etiquetas en línea (Badge) --- */
        .inline-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .inline-tag i { font-size: 0.55rem; }
        .tag-aviso { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
        .tag-noticia { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); }
        .tag-folleto { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
        .tag-video { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; border: 1px solid rgba(139, 92, 246, 0.2); }

        .dot-separator {
            color: #cbd5e1;
            font-size: 0.5rem;
        }

        /* --- Menú de Opciones 3 Puntitos --- */
        .post-options {
            position: relative;
            margin-left: auto;
        }

        .btn-options {
            background: transparent;
            border: none;
            color: var(--texto-secundario);
            font-size: 1.1rem;
            cursor: pointer;
            padding: 8px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            transition: var(--transicion);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-options:hover {
            background: var(--fondo-app);
            color: var(--texto-principal);
        }

        .post-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--blanco);
            border: 1px solid var(--borde-suave);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 160px;
            z-index: 10;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.2s cubic-bezier(0.17, 0.89, 0.32, 1.49);
        }

        .post-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .post-dropdown ul {
            list-style: none;
            padding: 6px;
            margin: 0;
        }

        .post-dropdown a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            color: var(--texto-principal);
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            border-radius: 8px;
            transition: var(--transicion);
        }

        .post-dropdown a i {
            font-size: 1rem;
            width: 16px;
            text-align: center;
            color: var(--texto-secundario);
        }

        .post-dropdown a:hover {
            background: var(--fondo-app);
            color: var(--azul-oscuro);
        }

        .post-dropdown a:hover i {
            color: var(--azul-oscuro);
        }

        .post-dropdown a.delete-option {
            color: #ef4444;
        }

        .post-dropdown a.delete-option i {
            color: #ef4444;
        }

        .post-dropdown a.delete-option:hover {
            background: #fef2f2;
            color: #dc2626;
        }

        /* --- Cuerpo Tarjeta --- */
        .post-card-body {
            padding: 16px 24px 12px;
        }

        .post-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--azul-oscuro);
            margin-bottom: 8px;
            letter-spacing: -0.3px;
            line-height: 1.4;
        }

        .post-excerpt {
            color: var(--texto-secundario);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 8px;
        }

        /* --- Imagen Única --- */
        .post-media {
            position: relative;
            width: 100%;
            overflow: hidden;
            cursor: zoom-in;
            margin: 12px 0;
        }

        .post-media img {
            width: 100%;
            display: block;
            transition: transform 0.5s ease;
            object-fit: cover;
            max-height: 450px;
        }

        .post-card:hover .post-media img {
            transform: scale(1.02);
        }

        .post-media-overlay {
            position: absolute;
            inset: 0;
            background: rgba(26, 42, 95, 0.2);
            opacity: 0;
            transition: opacity 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .post-media:hover .post-media-overlay {
            opacity: 1;
        }

        .post-media-overlay i {
            font-size: 2.5rem;
            color: #fff;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            padding: 14px;
            backdrop-filter: blur(4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* --- Galería Múltiples Imágenes --- */
        .post-media-gallery {
            display: grid;
            gap: 4px;
            margin: 12px 0;
            border-radius: 12px;
            overflow: hidden;
        }

        .gallery-2 {
            grid-template-columns: 1fr 1fr;
            height: 280px;
        }

        .gallery-3 {
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 160px 160px;
        }

        .gallery-3 .gallery-item:first-child {
            grid-column: 1 / 2;
            grid-row: 1 / 3;
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            cursor: zoom-in;
            height: 100%;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.05);
        }

        .gallery-item .overlay-more {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.55);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
            backdrop-filter: blur(3px);
        }

        /* --- Video (miniatura) --- */
        .post-video-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 16 / 9;
            overflow: hidden;
            cursor: pointer;
            margin: 12px 0;
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
            z-index: 2;
            transition: opacity 0.3s ease;
        }

        .play-circle {
            width: 65px;
            height: 65px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: var(--azul-claro);
            transition: var(--transicion);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            padding-left: 5px;
        }

        .post-video-wrapper:hover .play-circle {
            transform: scale(1.1);
            background: #fff;
            color: var(--naranja);
        }

        /* --- Folleto --- */
        .post-folleto-card {
            display: flex;
            align-items: center;
            gap: 16px;
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            border: 1.5px dashed #6ee7b7;
            border-radius: 12px;
            padding: 16px;
            margin-top: 10px;
            cursor: pointer;
            transition: var(--transicion);
        }

        .post-folleto-card:hover {
            background: linear-gradient(135deg, #d1fae5 0%, #c6f6d5 100%);
            border-color: #34d399;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.12);
        }

        .folleto-icon-box {
            width: 48px;
            height: 48px;
            background: #fff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #10b981;
            box-shadow: 0 3px 8px rgba(16, 185, 129, 0.12);
            flex-shrink: 0;
        }

        .folleto-info strong {
            font-size: 0.95rem;
            color: var(--texto-principal);
            display: block;
            margin-bottom: 2px;
        }

        .folleto-info span {
            font-size: 0.8rem;
            color: var(--texto-secundario);
        }

        .folleto-download {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #10b981;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            transition: var(--transicion);
            flex-shrink: 0;
            margin-left: auto;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
        }

        .post-folleto-card:hover .folleto-download {
            transform: scale(1.1);
            background: #059669;
        }

        /* --- Footer (Likes) --- */
        .post-card-footer {
            padding: 14px 24px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid var(--borde-suave);
            background: #f8fafc;
        }

        .post-likes-info {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            color: var(--texto-secundario);
            font-weight: 500;
        }

        .post-likes-avatars {
            display: flex;
        }

        .like-avatar-mini {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2px solid #f8fafc;
            margin-left: -8px;
            background: var(--azul-claro);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.6rem;
            color: #fff;
            font-weight: 700;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .like-avatar-mini:first-child { margin-left: 0; }

        /* Botón Pulgar Azul */
        .btn-like {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 1.5px solid var(--borde-suave);
            background: var(--blanco);
            color: var(--texto-secundario);
            font-size: 1.1rem;
            cursor: pointer;
            transition: var(--transicion);
        }

        .btn-like:hover {
            border-color: #93c5fd;
            color: var(--azul-like);
            background: var(--azul-like-suave);
            transform: scale(1.08);
        }

        .btn-like.liked {
            background: var(--azul-like-suave);
            border-color: #bfdbfe;
            color: var(--azul-like);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }

        .btn-like.liked i {
            animation: thumbPop 0.45s cubic-bezier(0.17, 0.89, 0.32, 1.49);
        }

        @keyframes thumbPop {
            0%, 100% { transform: scale(1) rotate(0deg); }
            30% { transform: scale(1.3) rotate(-15deg); }
            60% { transform: scale(0.9) rotate(10deg); }
        }

        /* Partículas Azules */
        .like-particles {
            position: fixed;
            pointer-events: none;
            z-index: 9999;
        }

        .like-particle {
            position: absolute;
            font-size: 0.95rem;
            animation: particleFly 0.8s ease-out forwards;
            opacity: 0;
            color: var(--azul-like);
        }

        @keyframes particleFly {
            0% { opacity: 1; transform: translate(0, 0) scale(1); }
            100% { opacity: 0; transform: translate(var(--dx), var(--dy)) scale(0.2); }
        }

        /* --- Load more --- */
        .feed-load-more {
            text-align: center;
            margin-top: 30px;
        }

        .btn-load-more {
            padding: 14px 30px;
            border-radius: 30px;
            border: 2px solid var(--borde-suave);
            background: var(--blanco);
            color: var(--texto-principal);
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: var(--transicion);
            font-family: 'Poppins', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-load-more:hover {
            border-color: var(--azul-claro);
            color: var(--azul-claro);
            background: #f8faff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 87, 189, 0.08);
        }

        /* --- Lightbox --- */
        .lightbox-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.85);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s ease, visibility 0.4s ease;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            cursor: zoom-out;
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
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transform: scale(0.95) translateY(20px);
            transition: transform 0.4s cubic-bezier(0.17, 0.89, 0.32, 1.49);
            border: 1px solid rgba(255,255,255,0.1);
            overflow: hidden;
        }

        .lightbox-overlay.active .lightbox-content {
            transform: scale(1) translateY(0);
        }

        .lightbox-content img,
        .lightbox-content iframe {
            width: 100%;
            height: auto;
            max-height: 85vh;
            display: block;
            border-radius: 12px;
            object-fit: contain;
        }

        .lightbox-content iframe {
            width: 85vw;
            height: 48vw;
            max-height: 80vh;
        }

        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 28px;
            font-size: 1.6rem;
            color: #fff;
            cursor: pointer;
            z-index: 10;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transicion);
            border: 1px solid rgba(255,255,255,0.15);
            backdrop-filter: blur(4px);
        }

        .lightbox-close:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: rotate(90deg) scale(1.1);
        }

        /* --- Modal Publicar --- */
        .modal-publicar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(26, 42, 95, 0.6);
            backdrop-filter: blur(6px);
            z-index: 9998;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .modal-publicar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-publicar {
            background: var(--blanco);
            border-radius: 20px;
            padding: 30px;
            width: 95%;
            max-width: 600px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            transform: translateY(30px);
            transition: transform 0.35s cubic-bezier(0.17, 0.89, 0.32, 1.49);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-publicar-overlay.active .modal-publicar {
            transform: translateY(0);
        }

        .modal-publicar h3 {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--azul-oscuro);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-publicar h3 i { color: var(--naranja); }

        .form-group { margin-bottom: 18px; }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--texto-principal);
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border-radius: 12px;
            border: 1.5px solid var(--borde-suave);
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            color: var(--texto-principal);
            transition: var(--transicion);
            background: #f8fafc;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--naranja);
            box-shadow: 0 0 0 4px rgba(214, 126, 41, 0.08);
            background: #fff;
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 24px;
        }

        .btn-cancelar {
            padding: 12px 24px;
            border-radius: 30px;
            border: 1.5px solid var(--borde-suave);
            background: var(--blanco);
            color: var(--texto-secundario);
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: var(--transicion);
        }

        .btn-cancelar:hover { background: #f1f5f9; color: var(--texto-principal); }

        /* --- Toast Azul --- */
        .like-toast {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: var(--azul-like);
            color: #fff;
            padding: 12px 26px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.9rem;
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
            transition: transform 0.4s cubic-bezier(0.17, 0.89, 0.32, 1.49);
            pointer-events: none;
        }

        .like-toast.show { transform: translateX(-50%) translateY(0); }

        /* Responsive */
        @media (max-width: 768px) {
            .feed-top-container { padding: 18px; flex-direction: column; align-items: flex-start; }
            .feed-controls-wrapper { flex-direction: column; align-items: stretch; width: 100%; }
            .feed-filters { justify-content: flex-start; }
            .btn-publicar { width: 100%; justify-content: center; }
            .post-card-header { padding: 16px 16px 0; }
            .post-card-body { padding: 12px 16px 8px; }
            .post-card-footer { padding: 12px 16px; }
            .gallery-2 { height: 200px; }
            .gallery-3 { grid-template-rows: 120px 120px; }
            .lightbox-content iframe { width: 95vw; height: 55vw; }
        }
    </style>
</head>

<body>
    <div class="dashboard">

        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        {{-- HEADER --}}
        <header class="header">
            <div class="header-left">
                <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="brand-container" id="logoToggle" style="cursor:pointer" title="Minimizar/Maximizar menú">
                    <img src="{{ asset('assets/img/logovinco1.png') }}" alt="Vinco Hub" class="brand-logo"
                        id="mainLogo" />
                </div>
            </div>
            <div class="header-right">
                @include('components.layouts._user-profile')
            </div>
        </header>

        {{-- SIDEBAR --}}
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-content">
                @php
                    $userPermissions = Auth::check()
                        ? \App\Models\Systems\UserManagement\UserPermission::getUserPermissions(Auth::id())
                        : [];
                @endphp

                @if (isset($userPermissions['administration']))
                    <div class="nav-group">
                        <div class="nav-header active" data-name="Administración">
                            <div class="nav-header-title">
                                <i class="fas fa-cogs"></i>
                                <span>Administración</span>
                            </div>
                            <i class="fas fa-chevron-right arrow-icon"></i>
                        </div>
                        <ul class="nav-list active">
                            @if (in_array('expense-claims', $userPermissions['administration']) || empty($userPermissions['administration']))
                                <li class="nav-item" data-route="/administration/expense-claims"
                                    data-name="Reembolsos">
                                    <i class="fas fa-receipt nav-icon"></i>
                                    <span class="nav-text">Reembolsos</span>
                                </li>
                            @endif
                            @if (in_array('facturacion', $userPermissions['administration']) || empty($userPermissions['administration']))
                                <li class="nav-item" data-route="/administration" data-name="Facturación">
                                    <i class="fas fa-file-invoice nav-icon"></i>
                                    <span class="nav-text">Facturación</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif

                @if (isset($userPermissions['rh']))
                    <div class="nav-group">
                        <div class="nav-header active" data-name="Recursos Humanos">
                            <div class="nav-header-title">
                                <i class="fas fa-users-cog"></i>
                                <span>Recursos Humanos</span>
                            </div>
                            <i class="fas fa-chevron-right arrow-icon"></i>
                        </div>
                        <ul class="nav-list active">
                            @if (in_array('loadchart', $userPermissions['rh']) || empty($userPermissions['rh']))
                                <li class="nav-item" data-route="/rh/loadchart" data-name="Mi LoadChart">
                                    <i class="fas fa-chart-pie nav-icon"></i>
                                    <span class="nav-text">Mi LoadChart</span>
                                </li>
                            @endif
                            @if (in_array('orgmanagement', $userPermissions['rh']) || empty($userPermissions['rh']))
                                <li class="nav-item" data-route="/rh/orgmanagement" data-name="Altas Empleados">
                                    <i class="fas fa-user-plus nav-icon"></i>
                                    <span class="nav-text">Empleados</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif

                @if (isset($userPermissions['qhse']))
                    <div class="nav-group">
                        <div class="nav-header active" data-name="QHSE">
                            <div class="nav-header-title">
                                <i class="fas fa-shield-alt"></i>
                                <span>QHSE</span>
                            </div>
                            <i class="fas fa-chevron-right arrow-icon"></i>
                        </div>
                        <ul class="nav-list active">
                            @if (in_array('management', $userPermissions['qhse']) || empty($userPermissions['qhse']))
                                <li class="nav-item" data-route="/qhse/management"
                                    data-name="Gerenciamiento De Viajes">
                                    <i class="fas fa-road nav-icon"></i>
                                    <span class="nav-text">VesDrive</span>
                                </li>
                            @endif
                            @if (in_array('incidencias', $userPermissions['qhse']) || empty($userPermissions['qhse']))
                                <li class="nav-item" data-route="/qhse/incidencias" data-name="Mis Incidencias">
                                    <i class="fas fa-exclamation-triangle nav-icon"></i>
                                    <span class="nav-text">Mis Incidencias</span>
                                </li>
                            @endif
                            @if (in_array('vescap', $userPermissions['qhse']) || empty($userPermissions['qhse']))
                                <li class="nav-item" data-route="/qhse/vescap" data-name="VESCAP">
                                    <i class="fas fa-fire-extinguisher nav-icon"></i>
                                    <span class="nav-text">VesCap</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif

                @if (isset($userPermissions['systems']))
                    <div class="nav-group">
                        <div class="nav-header active" data-name="Sistemas">
                            <div class="nav-header-title">
                                <i class="fas fa-server"></i>
                                <span>Sistemas</span>
                            </div>
                            <i class="fas fa-chevron-right arrow-icon"></i>
                        </div>
                        <ul class="nav-list active">
                            @if (in_array('user-management', $userPermissions['systems']) || empty($userPermissions['systems']))
                                <li class="nav-item" data-route="/systems/user-management"
                                    data-name="Gestión de Roles">
                                    <i class="fas fa-user-shield nav-icon"></i>
                                    <span class="nav-text">Roles y Permisos</span>
                                </li>
                            @endif
                            @if (in_array('tickets', $userPermissions['systems']) || empty($userPermissions['systems']))
                                <li class="nav-item" data-route="/systems/tickets" data-name="Gestión de Tickets">
                                    <i class="fas fa-ticket-alt nav-icon"></i>
                                    <span class="nav-text">Mis Tickets</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif

                @if (isset($userPermissions['operations']))
                    <div class="nav-group">
                        <div class="nav-header active" data-name="Operaciones">
                            <div class="nav-header-title">
                                <i class="fas fa-industry"></i>
                                <span>Operaciones</span>
                            </div>
                            <i class="fas fa-chevron-right arrow-icon"></i>
                        </div>
                        <ul class="nav-list active">
                            @if (in_array('wells', $userPermissions['operations']) || empty($userPermissions['operations']))
                                <li class="nav-item" data-route="/operations/wells" data-name="Pozos">
                                    <i class="fas fa-oil-can nav-icon"></i>
                                    <span class="nav-text">Pozos</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <main class="main-content">
            <div class="content-wrapper">
<div class="content-wrapper">


                <div class="empty-state-wrapper">
                    <div class="empty-state-card">
                        <div class="icon-circle">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h2 class="empty-title">Sin comunicados recientes</h2>
                        <p class="empty-description">
                            Aún no hay avisos, novedades o comunicados en la plataforma.
                            Utiliza el menú lateral para gestionar los diferentes módulos de Vinco Hub.
                        </p>
                    </div>
                </div>

        </div>

            </div>
        </main>
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

            /* ========== SIDEBAR ========== */
            const setHeight = (header) => {
                const list = header.nextElementSibling;
                const icon = header.querySelector('.arrow-icon');
                if (!list) return;
                if (header.classList.contains('active')) {
                    list.style.maxHeight = list.scrollHeight + 'px';
                    icon && (icon.style.transform = 'rotate(90deg)');
                } else {
                    list.style.maxHeight = '0px';
                    icon && (icon.style.transform = 'rotate(0deg)');
                }
            };

            document.querySelectorAll('.nav-header').forEach(header => {
                setHeight(header);
                header.addEventListener('click', () => {
                    header.classList.toggle('active');
                    header.nextElementSibling?.classList.toggle('active');
                    setHeight(header);
                });
            });

            const currentPath = window.location.pathname;
            document.querySelectorAll('.nav-item').forEach(item => {
                if (item.dataset.route === currentPath) {
                    item.classList.add('active');
                    const group = item.closest('.nav-group');
                    const header = group?.querySelector('.nav-header');
                    const list = group?.querySelector('.nav-list');
                    if (header && list) {
                        header.classList.add('active');
                        list.classList.add('active');
                        setTimeout(() => setHeight(header), 50);
                    }
                }
                item.addEventListener('click', function() {
                    const route = this.dataset.route;
                    if (route) window.location.href = route;
                });
            });

            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleMobile = () => {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('show');
            };
            menuToggle?.addEventListener('click', toggleMobile);
            overlay?.addEventListener('click', toggleMobile);

            const logoImg = document.getElementById('mainLogo');
            document.getElementById('logoToggle')?.addEventListener('click', () => {
                if (window.innerWidth > 768) {
                    document.body.classList.toggle('sidebar-minimized');
                    logoImg.style.opacity = '0';
                    setTimeout(() => {
                        logoImg.src = document.body.classList.contains(
                            'sidebar-minimized') ?
                            "{{ asset('assets/img/logo.png') }}" :
                            "{{ asset('assets/img/logovinco1.png') }}";
                        logoImg.style.opacity = '1';
                    }, 200);
                    setTimeout(() => {
                        document.querySelectorAll('.nav-header.active').forEach(
                        header => {
                            const list = header.nextElementSibling;
                            if (list) list.style.maxHeight = list.scrollHeight +
                                'px';
                        });
                    }, 360);
                }
            });

            /* ========== Opciones 3 Puntitos (Dropdown) ========== */
            document.addEventListener('click', function(e) {
                const isDropdownBtn = e.target.closest('.btn-options');

                // Cerrar todos los menús si se hace click fuera
                if (!isDropdownBtn && e.target.closest('.post-dropdown') == null) {
                    document.querySelectorAll('.post-dropdown.show').forEach(menu => {
                        menu.classList.remove('show');
                    });
                    return;
                }

                // Si se hizo click en un botón, cerrar los demás y abrir el clickeado
                if (isDropdownBtn) {
                    const dropdown = isDropdownBtn.nextElementSibling;

                    document.querySelectorAll('.post-dropdown.show').forEach(menu => {
                        if (menu !== dropdown) {
                            menu.classList.remove('show');
                        }
                    });

                    dropdown.classList.toggle('show');
                }
            });

            /* ========== FEED: LIKES (Pulgar Azul) ========== */
            const likeToast = document.getElementById('likeToast');
            const likeToastMsg = document.getElementById('likeToastMsg');
            let toastTimer;

            function showLikeToast(message, isLiked) {
                likeToastMsg.textContent = message;
                likeToast.style.background = isLiked ? 'var(--azul-like)' : '#64748b';
                likeToast.querySelector('i').className = isLiked ? 'fas fa-thumbs-up' : 'far fa-thumbs-up';
                likeToast.classList.add('show');
                clearTimeout(toastTimer);
                toastTimer = setTimeout(() => likeToast.classList.remove('show'), 1800);
            }

            document.querySelectorAll('.btn-like').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    const isLiked = this.classList.contains('liked');
                    const icon = this.querySelector('i');

                    if (isLiked) {
                        this.classList.remove('liked');
                        icon.className = 'far fa-thumbs-up';
                        showLikeToast('Has quitado tu like', false);
                    } else {
                        this.classList.add('liked');
                        icon.className = 'fas fa-thumbs-up';
                        spawnLikeParticles(this);
                        showLikeToast('¡Te gusta esta publicación!', true);
                    }
                    updateLikesCount(this.closest('.post-card-footer'), !isLiked);
                });
            });

            function spawnLikeParticles(btn) {
                const rect = btn.getBoundingClientRect();
                const container = document.createElement('div');
                container.className = 'like-particles';
                container.style.left = rect.left + rect.width / 2 + 'px';
                container.style.top = rect.top + rect.height / 2 + 'px';
                document.body.appendChild(container);

                for (let i = 0; i < 6; i++) {
                    const particle = document.createElement('span');
                    particle.className = 'like-particle';
                    particle.innerHTML = `<i class="fas fa-thumbs-up"></i>`;
                    particle.style.setProperty('--dx', (Math.random() * 80 - 40) + 'px');
                    particle.style.setProperty('--dy', (Math.random() * -80 - 20) + 'px');
                    particle.style.animationDelay = (i * 0.05) + 's';
                    container.appendChild(particle);
                }
                setTimeout(() => container.remove(), 900);
            }

            function updateLikesCount(footer, increment) {
                const likesInfo = footer?.querySelector('.post-likes-info span');
                if (!likesInfo) return;
                const match = likesInfo.textContent.match(/\d+/);
                if (match) {
                    let count = parseInt(match[0]);
                    count = increment ? count + 1 : Math.max(0, count - 1);
                    likesInfo.textContent = count + ' personas';
                }
            }

            /* ========== FEED: FILTROS ========== */
            const filterChips = document.querySelectorAll('.feed-filter-chip');
            const feedPosts = document.querySelectorAll('.post-card[data-type]');

            filterChips.forEach(chip => {
                chip.addEventListener('click', function() {
                    filterChips.forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    const filter = this.dataset.filter;
                    feedPosts.forEach((post, index) => {
                        if (filter === 'all' || post.dataset.type === filter) {
                            post.style.display = '';
                            post.style.animation =
                                `feedFadeIn 0.4s ease-out ${index * 0.06}s both`;
                        } else {
                            post.style.display = 'none';
                            post.style.animation = '';
                        }
                    });
                });
            });

            /* ========== FEED: LIGHTBOX MEJORADO (Imágenes y Videos) ========== */
            const lightboxOverlay = document.getElementById('lightboxOverlay');
            const lightboxContent = document.getElementById('lightboxContent');
            const lightboxClose = document.getElementById('lightboxClose');

            // Abrir lightbox para imágenes (individuales y galería)
            document.querySelectorAll('.post-media[data-lightbox], .gallery-item[data-lightbox]').forEach(media => {
                media.addEventListener('click', function() {
                    const img = this.querySelector('img');
                    if (img) {
                        lightboxContent.innerHTML = `<img src="${img.src}" alt="${img.alt || 'Imagen'}" />`;
                        lightboxOverlay.classList.add('active');
                        document.body.style.overflow = 'hidden';
                    }
                });
            });

            // Abrir lightbox para videos (reproductor a pantalla completa)
            document.querySelectorAll('.post-video-wrapper').forEach(wrapper => {
                wrapper.addEventListener('click', function() {
                    const videoUrl = this.dataset.videoUrl;
                    if (videoUrl) {
                        lightboxContent.innerHTML = `<iframe src="${videoUrl}" allowfullscreen allow="autoplay; encrypted-media"></iframe>`;
                        lightboxOverlay.classList.add('active');
                        document.body.style.overflow = 'hidden';
                    }
                });
            });

            lightboxClose?.addEventListener('click', cerrarLightbox);
            lightboxOverlay?.addEventListener('click', function(e) {
                if (e.target === lightboxOverlay) cerrarLightbox();
            });

            function cerrarLightbox() {
                lightboxOverlay.classList.remove('active');
                document.body.style.overflow = '';
                setTimeout(() => {
                    lightboxContent.innerHTML = ''; // Limpia el contenido
                }, 400);
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && lightboxOverlay.classList.contains('active'))
                    cerrarLightbox();
            });

            /* ========== FEED: FOLLETO ========== */
            document.querySelectorAll('.post-folleto-card').forEach(folleto => {
                folleto.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const name = this.querySelector('.folleto-info strong')
                        ?.textContent || 'documento';
                    Swal.fire({
                        title: 'Descarga iniciada',
                        text: `El archivo "${name.trim()}" se descargaría ahora.`,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end',
                    });
                });
            });

            /* ========== FEED: LOAD MORE ========== */
            const btnLoadMore = document.getElementById('btnLoadMore');
            btnLoadMore?.addEventListener('click', function() {
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
                this.disabled = true;
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-check"></i> No hay más publicaciones';
                    this.style.borderColor = '#10b981';
                    this.style.color = '#10b981';
                    this.style.background = '#f0fdf4';
                    setTimeout(() => {
                        this.innerHTML =
                            '<i class="fas fa-chevron-down"></i> Ver más publicaciones';
                        this.disabled = false;
                        this.style.borderColor = '';
                        this.style.color = '';
                        this.style.background = '';
                    }, 2000);
                }, 1200);
            });

            /* ========== MODAL PUBLICAR ========== */
            const modalPublicarOverlay = document.getElementById('modalPublicarOverlay');
            const btnAbrirModalPublicar = document.getElementById('btnAbrirModalPublicar');
            const btnCancelarPublicacion = document.getElementById('btnCancelarPublicacion');
            const formPublicar = document.getElementById('formPublicar');

            btnAbrirModalPublicar?.addEventListener('click', () => {
                modalPublicarOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            });

            function cerrarModalPublicar() {
                modalPublicarOverlay.classList.remove('active');
                document.body.style.overflow = '';
                formPublicar?.reset();
            }

            btnCancelarPublicacion?.addEventListener('click', cerrarModalPublicar);
            modalPublicarOverlay?.addEventListener('click', function(e) {
                if (e.target === modalPublicarOverlay) cerrarModalPublicar();
            });

            formPublicar?.addEventListener('submit', function(e) {
                e.preventDefault();
                const tipo = document.getElementById('postTipo').value;
                const area = document.getElementById('postArea').value;
                const titulo = document.getElementById('postTitulo').value;
                const contenido = document.getElementById('postContenido').value;

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
                cerrarModalPublicar();
            });

            /* ========== ANIMACIÓN INICIAL ========== */
            feedPosts.forEach((post, index) => {
                post.style.animation = `feedFadeIn 0.5s ease-out ${index * 0.08}s both`;
            });

        });
    </script>

    @stack('scripts')
</body>

</html>
