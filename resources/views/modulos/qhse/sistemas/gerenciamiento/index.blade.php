<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinco Energy - Gerenciamiento de Viajes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root {
            /* Colores Principales Mantenidos */
            --primary-blue: #334c95;
            --primary-orange: #d67e29;
            --orange-dark: #d67d29b6;
            --dark-gray: #2d3748;
            --medium-gray: #4a5568;
            --light-gray: #e2e8f0;
            --background-gray: #f7fafc;
            --secondary-blue: #34495e;
            --white: #ffffff;
            --blue-dark: #263a74;
            --blue-darker: #1a2853;
            --blue-light: #4a67b0;
            --blue-lighter: #6f85c6;
            --blue-very-light: #a2b0dc;
            --blue-pale: #d8deef;
            --accent-green: #4caf50;
            --accent-red: #f44336;
            --border-gray: #cccccc;

            /* 🌟 Nueva Paleta de Colores de Estado de Estadísticas */
            --stat-active-bg: #e1f5fe;
            --stat-active-color: #0277bd;
            --stat-pending-bg: #fff3e0;
            --stat-pending-color: #ef6c00;
            --stat-completed-bg: #e8f5e9;
            --stat-completed-color: #2e7d32;
            --stat-available-bg: #f5f5f5;
            --stat-available-color: #607d8b;

            /* Sombra para las tarjetas */
            --shadow-light: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.06);
            --shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.08);

            /* Colores de estado de tabla */
            --status-aprobado-bg: #e8f5e9;
            --status-aprobado-color: #2e7d32;
            --status-pendiente-bg: #fff3e0;
            --status-pendiente-color: #ef6c00;
            --status-encurso-bg: #e3f2fd;
            --status-encurso-color: #1565c0;
            --status-cancelado-bg: #ffebee;
            --status-cancelado-color: #c62828;

            /* 🚨 NUEVOS COLORES DE RIESGO 🚨 */
            --riesgo-bajo-bg: #e8f5e9;
            --riesgo-bajo-color: #2e7d32;
            --riesgo-medio-bg: #fff3e0;
            --riesgo-medio-color: #ff9800;
            --riesgo-alto-bg: #ffebee;
            --riesgo-alto-color: #d32f2f;
        }

        /* ======================================================= */
        /* ESTILOS GENERALES */
        /* ======================================================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--background-gray);
            color: var(--dark-gray);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-size: 14px;
        }

        .swal2-container {
            z-index: 3000 !important;
        }

        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background-color: var(--primary-blue);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-track {
            background-color: var(--light-gray);
        }

        .container-viajes {
            flex: 1;
            padding: 25px;
            margin: 0 auto;
            width: 100%;
            max-width: 1800px;
        }

        /* ======================================================= */
        /* HEADER */
        /* ======================================================= */
        .header-viajes {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--blue-dark) 100%);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            padding: 8px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .logo-container-viajes {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-img-viajes {
            width: 100px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            padding: 5px;
        }

        .logo-img-viajes img {
            width: 100%;
            height: auto;
            object-fit: cover;
            filter: brightness(0) invert(1);
        }

        .form-header-modal .logo-img-viajes img {
            max-height: 35px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .nav-viajes {
            display: flex;
            gap: 8px;
        }

        .nav-link-viajes {
            text-decoration: none;
            color: var(--white);
            background: rgba(255, 255, 255, 0.1);
            font-weight: 500;
            font-size: 13px;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.3s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .nav-link-viajes:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .nav-link-viajes.active {
            background: var(--white);
            color: var(--primary-blue);
            border-color: var(--white);
            font-weight: 600;
        }

        /* ======================================================= */
        /* DASHBOARD MEJORADO */
        /* ======================================================= */
        .card-base {
            background: var(--white);
            border-radius: 12px;
            margin-bottom: 16px;
            box-shadow: var(--shadow-light);
            overflow: hidden;
            border: 1px solid var(--border-gray);
            transition: all 0.3s ease;
        }

        .card-base:hover {
            box-shadow: var(--shadow-hover);
        }

        .compact-header {
            padding: 20px 24px;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--blue-dark) 100%);
            color: var(--white);
            position: relative;
            overflow: hidden;
        }

        .compact-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: rgba(253, 253, 253, 0.425);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .travel-title {
            font-size: 2.2rem;
            color: var(--white);
            margin: 0 0 5px 0;
            font-weight: 700;
        }

        .travel-title i {
            color: var(--white);
            margin-right: 10px;
        }

        .travel-subtitle {
            font-size: 1rem;
            color: var(--blue-pale);
            margin: 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-top: 15px;
        }

        .stat-card {
            background-color: var(--white);
            padding: 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid var(--border-gray);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .stat-icon {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 1.2rem;
            margin-right: 15px;
        }

        .stat-info {
            display: flex;
            flex-direction: column;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--medium-gray);
            margin-top: 3px;
            white-space: nowrap;
        }

        .stat-active {
            background-color: var(--stat-active-bg);
            color: var(--stat-active-color);
        }

        .stat-active-icon {
            background-color: var(--stat-active-color);
            color: var(--white);
        }

        .stat-pending {
            background-color: var(--stat-pending-bg);
            color: var(--stat-pending-color);
        }

        .stat-pending-icon {
            background-color: var(--stat-pending-color);
            color: var(--white);
        }

        .stat-completed {
            background-color: var(--stat-completed-bg);
            color: var(--stat-completed-color);
        }

        .stat-completed-icon {
            background-color: var(--stat-completed-color);
            color: var(--white);
        }

        .stat-available {
            background-color: var(--stat-available-bg);
            color: var(--stat-available-color);
        }

        .stat-available-icon {
            background-color: var(--stat-available-color);
            color: var(--white);
        }

        /* ======================================================= */
        /* FILTROS */
        /* ======================================================= */
        .filters-section {
            padding: 20px 25px;
            border-top: 1px solid var(--light-gray);
            margin-top: 20px;
            background-color: var(--white);
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 8px;
        }

        .filter-group label i {
            color: var(--primary-blue);
            margin-right: 5px;
        }

        .form-control {
            padding: 10px 12px;
            border: 1px solid var(--border-gray);
            border-radius: 6px;
            font-size: 1rem;
            color: var(--medium-gray);
            background-color: var(--white);
            transition: border-color 0.2s, box-shadow 0.2s;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.04);
            width: 100%;
        }

        .form-control:focus {
            border-color: var(--blue-light);
            outline: none;
            box-shadow: 0 0 0 2px var(--blue-pale);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
        }

        .btn-clear-filters {
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s, box-shadow 0.2s, opacity 0.2s;
            white-space: nowrap;
            background-color: var(--light-gray);
            color: var(--dark-gray);
            border: 1px solid var(--border-gray);
            width: 100%;
        }

        .btn-clear-filters:hover {
            background-color: var(--medium-gray);
            color: var(--white);
        }

        .btn-clear-filters i {
            margin-right: 5px;
        }

        /* TABLA DASHBOARD */
        .table-dashboard-container {
            background: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            overflow-x: auto;
        }

        .table-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--blue-dark) 100%);
            color: var(--white);
            padding: 18px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h3 {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-viajes {
            width: 100%;
            min-width: 1000px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-viajes th {
            background: var(--blue-pale);
            color: var(--primary-blue);
            font-weight: 700;
            padding: 15px 12px;
            text-align: left;
            border-bottom: 3px solid var(--primary-blue);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .table-viajes td {
            padding: 15px 12px;
            border-bottom: 1px solid var(--light-gray);
            transition: background-color 0.2s ease;
            font-size: 14px;
            white-space: nowrap;
        }

        .table-viajes tbody tr:hover td {
            background-color: rgba(51, 76, 149, 0.05);
        }

        .badge-status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            display: inline-block;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .status-aprobado {
            background: linear-gradient(135deg, var(--status-aprobado-bg) 0%, #c8e6c9 100%);
            color: var(--status-aprobado-color);
            border: 1px solid #81c784;
        }

        .status-pendiente {
            background: linear-gradient(135deg, var(--status-pendiente-bg) 0%, #ffe0b2 100%);
            color: var(--status-pendiente-color);
            border: 1px solid #ffb74d;
        }

        .status-encurso {
            background: linear-gradient(135deg, var(--status-encurso-bg) 0%, #bbdefb 100%);
            color: var(--status-encurso-color);
            border: 1px solid #64b5f6;
        }

        .status-cancelado {
            background: linear-gradient(135deg, var(--status-cancelado-bg) 0%, #ffcdd2 100%);
            color: var(--status-cancelado-color);
            border: 1px solid #e57373;
        }

        .badge-riesgo {
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            display: inline-block;
            min-width: 60px;
            text-align: center;
        }

        .status-riesgo-bajo {
            background-color: var(--riesgo-bajo-bg);
            color: var(--riesgo-bajo-color);
            border: 1px solid #c8e6c9;
        }

        .status-riesgo-medio {
            background-color: var(--riesgo-medio-bg);
            color: var(--riesgo-medio-color);
            border: 1px solid #ffe0b2;
        }

        .status-riesgo-alto {
            background-color: var(--riesgo-alto-bg);
            color: var(--riesgo-alto-color);
            border: 1px solid #ffcdd2;
        }

        .btn-action-small {
            padding: 8px 14px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .btn-view {
            background: var(--blue-pale);
            color: var(--primary-blue);
        }

        .btn-view:hover {
            background: var(--blue-very-light);
            transform: translateY(-1px);
        }

        .btn-edit {
            background: var(--primary-orange);
            color: var(--white);
        }

        .btn-edit:hover {
            background: #e68900;
            transform: translateY(-1px);
        }

        /* ======================================================= */
        /* MODAL FORMULARIO */
        /* ======================================================= */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(3px);
            z-index: 2000;
            overflow-y: auto;
            padding: 20px;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active {
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .modal-content {
            background: var(--white);
            border-radius: 15px;
            width: 100%;
            max-width: 1600px;
            margin: 20px auto;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4);
            transform: scale(0.98);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .modal-overlay.active .modal-content {
            transform: scale(1);
            opacity: 1;
        }

        #modalInspeccion,
        #modalPreConvoy,
        #modalEvaluacion {
            z-index: 2100;
        }

        #modalInspeccion .modal-content,
        #modalPreConvoy .modal-content,
        #modalEvaluacion .modal-content {
            z-index: 2101;
        }

        .form-body {
            padding: 30px;
        }

        .form-section {
            margin-bottom: 30px;
            border: 1px solid var(--blue-pale);
            border-radius: 10px;
            padding: 20px;
            background: var(--white);
        }

        .form-header-modal {
            background: linear-gradient(90deg, var(--primary-blue) 0%, var(--blue-dark) 100%);
            padding: 12px 20px;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--white);
            border-bottom: 2px solid var(--primary-orange);
        }

        .form-header-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-header-modal .logo-img-viajes {
            width: 60px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;

        }

        .form-header-title h2 {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.5px;
        }

        .form-header-title p {
            font-size: 11px;
            margin: 0;
            font-weight: 400;
            opacity: 0.8;
        }

        .header-right-group {
            display: flex;
            align-items: stretch;
            gap: 15px;
            height: 100%;
        }

        .form-document-detail {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 2px;
            padding: 2px 0;
        }

        .form-document-detail label {
            font-size: 10px;
            font-weight: 500;
            opacity: 0.7;
            margin: 0;
            padding: 1px 4px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 3px;
            white-space: nowrap;
            line-height: 1;
        }

        .form-document-detail span:not(.form-code-value) {
            font-weight: 600;
            letter-spacing: 0.5px;
            color: var(--white);
            font-size: 13px;
        }

        .request-date {
            border-right: 1px dashed rgba(255, 255, 255, 0.4);
            padding-right: 15px;
        }

        .form-code-value {
            font-weight: 700;
            letter-spacing: 1px;
            background: var(--primary-orange);
            padding: 3px 6px;
            border-radius: 4px;
            display: inline-block;
            font-size: 13px;
        }

        .form-section-title {
            color: var(--primary-blue);
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 3px solid var(--blue-pale);
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
        }

        .form-section-title i {
            background: var(--primary-blue);
            color: var(--white);
            padding: 8px;
            border-radius: 50%;
            font-size: 14px;
        }

        .form-section-title {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .form-section-title h3 {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
        }

        .form-section-title .unidades-count {
            margin-left: 10px;
        }

        .form-section-title #label-tipo-unidad {
            margin-right: auto;
        }

        .form-section-title #btnReunionPreConvoy {
            margin-left: auto;
            order: 3;
        }

        .unidades-label {
            font-size: 14px;
            font-weight: 600;
            margin-left: 5px;
            color: var(--medium-gray);
            transition: color 0.3s ease;
        }

        .unidades-label.convoy {
            color: var(--primary-orange);
        }

        .swal2-convoy .swal2-title {
            color: var(--primary-orange) !important;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .trayecto-grid {
            display: grid;
            grid-template-columns: 2fr 2fr 1fr 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        @media (max-width: 1400px) {
            .trayecto-grid {
                grid-template-columns: 2fr 1fr 1fr;
            }

            .trayecto-grid .form-group:nth-child(1),
            .trayecto-grid .form-group:nth-child(2) {
                grid-column: span 1;
            }

            .trayecto-grid .form-group:nth-child(3) {
                grid-column: span 1;
            }

            .trayecto-grid .form-group:nth-child(4) {
                grid-column: 1 / 2;
            }
        }

        @media (max-width: 992px) {
            .trayecto-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .trayecto-grid .form-group {
                grid-column: span 1 !important;
            }
        }

        @media (max-width: 768px) {
            .trayecto-grid {
                grid-template-columns: 1fr;
            }

            .header-right-group {
                flex-direction: column;
                align-items: flex-end;
                gap: 10px;
            }

            .form-date-document {
                border-right: none;
                padding-right: 0;
            }

            .form-code-document {
                padding-left: 0;
            }

            .form-section-title {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-section-title #btnReunionPreConvoy {
                margin-left: 0;
                align-self: flex-end;
            }
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            color: var(--blue-dark);
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .form-group label .required {
            color: var(--accent-red);
            margin-left: 2px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px 15px;
            border: 1px solid var(--border-gray);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(51, 76, 149, 0.15);
        }

        .form-group input[readonly] {
            background: var(--light-gray);
            color: var(--medium-gray);
            cursor: not-allowed;
            box-shadow: none;
        }

        .datetime-input-wrapper {
            position: relative;
        }

        .datetime-input-wrapper i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-blue);
            pointer-events: none;
        }

        .datetime-input-wrapper input {
            padding-right: 35px;
            width: 100%;
        }

        /* 🚀 ESTILOS MODIFICADOS PARA LA SECCIÓN DE PARADAS (4 COLUMNAS Y COMPACTO) 🚀 */
        .radio-group-paradas {
            display: flex;
            gap: 20px;
            margin-top: 5px;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .radio-option input[type="radio"] {
            appearance: none;
            height: 28px;
            border: 2px solid var(--medium-gray);
            border-radius: 100%;
            position: relative;
            cursor: pointer;
        }

        .radio-option input[type="radio"]:checked {
            border-color: var(--primary-blue);
            background-color: var(--primary-blue);
            box-shadow: inset 0 0 0 3px var(--white);
        }

        #contenedorParadas {
            margin-top: 20px;
            background: #f8fafc;
            border: 1px dashed var(--blue-lighter);
            border-radius: 8px;
        }

        /* 🚀 NUEVO ESTILO DE 4 COLUMNAS 🚀 */
        .paradas-fila {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            /* 4 Columnas */
            gap: 12px;
            /* Espacio reducido entre tarjetas */
            margin-bottom: 12px;
        }

        @media (max-width: 1200px) {
            .paradas-fila {
                grid-template-columns: repeat(2, 1fr);
                /* 2 Columnas en tablet */
            }
        }

        @media (max-width: 600px) {
            .paradas-fila {
                grid-template-columns: 1fr;
                /* 1 Columna en móvil */
            }
        }

        /* 🚀 TARJETA DE PARADA COMPACTA 🚀 */
        .parada-item {
            background: var(--white);
            border-radius: 8px;
            border: 1px solid var(--light-gray);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            /* Para que el header contenga el borde */
            display: flex;
            flex-direction: column;
        }

        /* Header de la tarjeta */
        .parada-header {
            background: var(--blue-pale);
            padding: 6px 10px;
            display: flex;
            justify-content: center;
            /* CENTRADO TOTAL */
            align-items: center;
            position: relative;
            /* Para posicionar el botón de borrar */
            border-bottom: 1px solid var(--light-gray);
        }

        /* Texto "Parada X" */
        .parada-titulo {
            font-weight: 700;
            color: var(--primary-blue);
            font-size: 13px;
            text-align: center;
            width: 100%;
            /* Ocupa todo el ancho para asegurar centrado */
        }

        /* Botón eliminar flotante a la derecha */
        .btn-remove-parada-compact {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            color: var(--accent-red);
            border: none;
            cursor: pointer;
            font-size: 12px;
            padding: 4px;
            transition: transform 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-remove-parada-compact:hover {
            transform: translateY(-50%) scale(1.15);
            /* Mantiene centrado vertical y escala */
            color: #b71c1c;
        }

        /* Cuerpo compacto de la tarjeta */
        .parada-body-compact {
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            /* Espacio entre inputs */
        }

        /* Estilo para los labels pequeños dentro de la tarjeta */
        .parada-label-small {
            font-size: 10px;
            font-weight: 600;
            color: var(--medium-gray);
            text-transform: uppercase;
            margin-bottom: 2px;
            display: block;
        }

        /* Inputs más compactos */
        .form-control-sm {
            padding: 6px 10px;
            font-size: 12px;
            height: 32px;
            /* Altura fija pequeña */
        }

        .btn-add-parada {
            background: var(--accent-green);
            color: var(--white);
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-add-parada:hover {
            background: #3d8b40;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .hidden {
            display: none !important;
        }

        /* 🚀 ESTILO PARA AGRUPAR ESPECIFIQUE DESTINO Y PARADAS 🚀 */
        .destino-paradas-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            align-items: flex-end;
        }

        @media (max-width: 768px) {
            .destino-paradas-container {
                grid-template-columns: 1fr;
            }
        }

        /* Unidades - TABLA MEJORADA */
        .unidades-container {
            border-radius: 10px;
            overflow-x: auto;
            border: 1px solid var(--border-gray);
        }

        .unidades-table {
            width: 100%;
            min-width: 1250px;
            border-collapse: collapse;
            border-spacing: 0;
            font-size: 13px;
        }

        .unidades-table th {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--blue-dark) 100%);
            color: var(--white);
            font-weight: 600;
            padding: 10px 8px;
            text-align: center;
            position: sticky;
            top: 0;
            white-space: nowrap;
            z-index: 10;
        }

        .unidades-table th .column-title {
            font-size: 12px;
            text-transform: capitalize;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 2px;
        }

        .unidades-table th.th-conductor-completo {
            min-width: 180px;
        }

        .unidades-table th.th-vigencia {
            min-width: 180px;
        }

        .unidades-table th.th-hrs-sueno {
            min-width: 160px;
        }

        .unidades-table th.th-horas-conduccion {
            min-width: 180px;
        }

        .unidades-table th.th-pasajeros {
            min-width: 220px;
        }

        .unidades-table th.th-vehiculo {
            min-width: 150px;
        }

        .unidades-table th.th-inspeccion {
            min-width: 80px;
        }

        .unidades-table th.th-acciones {
            min-width: 70px;
        }

        .unidades-table td:nth-child(1) {
            min-width: 180px;
            text-align: center;
        }

        .unidades-table td:nth-child(2) {
            min-width: 180px;
        }

        .unidades-table td:nth-child(3) {
            min-width: 160px;
        }

        .unidades-table td:nth-child(4) {
            min-width: 180px;
        }

        .unidades-table td:nth-child(5) {
            min-width: 220px;
        }

        .unidades-table td:nth-child(6) {
            min-width: 150px;
        }

        .unidades-table td:nth-child(7) {
            min-width: 80px;
        }

        .unidades-table td:nth-child(8) {
            min-width: 70px;
        }

        .hour-inputs-group-combined-vertical {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 5px;
        }

        .hour-inputs-group-combined-vertical .hour-input-group {
            margin: 0;
            padding: 0;
            align-items: center;
        }

        .unidades-table td {
            padding: 8px 10px;
            border-bottom: 1px solid var(--border-gray);
            border-right: 1px solid var(--border-gray);
            vertical-align: middle;
            background: var(--white);
            text-align: center;
        }

        .unidades-table tr:nth-child(even) td {
            background: var(--background-gray);
        }

        .unidades-table td:last-child {
            border-right: none;
        }

        .unidades-table tr:last-child td {
            border-bottom: none;
        }

        .unidades-table tr:hover td {
            background: rgba(51, 76, 149, 0.08);
        }

        .table-input {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid var(--border-gray);
            border-radius: 6px;
            font-size: 13px;
            font-family: inherit;
            background: var(--white);
            text-align: center;
        }

        .table-input[readonly] {
            background: var(--light-gray);
            color: var(--dark-gray);
            cursor: default;
            box-shadow: none;
        }

        .table-input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 2px rgba(51, 76, 149, 0.1);
        }

        .table-input.small {
            max-width: 80px;
            margin: 0 auto;
        }

        .unidad-alcoholimetria {
            max-width: 80px;
            margin: 0 auto;
        }

        .table-input.medium {
            max-width: 150px;
            margin: 0 auto;
        }

        .table-input.large {
            max-width: 180px;
            margin: 0 auto;
        }

        .table-input[type="number"]::-webkit-outer-spin-button,
        .table-input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .conductor-completo-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            text-align: center;
            padding: 5px 0;
            width: 100%;
        }

        .conductor-completo-group .hour-input-group:first-of-type {
            margin-bottom: 12px;
        }

        .conductor-completo-group .hour-input-group:nth-of-type(2) {
            display: none;
        }

        .conductor-completo-group .hour-input-group:nth-of-type(3) {
            display: none;
        }

        .conductor-completo-group .hour-input-group:nth-of-type(4) {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 12px;
        }

        .conductor-completo-group .hour-input-group label,
        .unidades-table td .hour-input-group label {
            color: var(--blue-dark);
            font-weight: 600;
            font-size: 11px;
            width: 100%;
            justify-content: center;
        }

        .conductor-completo-group .hour-input-group:not(:first-of-type):not(:last-of-type) {
            display: none;
        }

        .conductor-input-group {
            position: relative;
            flex-grow: 1;
            width: 90%;
            margin: 0 auto;
        }

        .autocomplete-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 100;
            background: var(--white);
            border: 1px solid var(--border-gray);
            border-top: none;
            border-radius: 0 0 6px 6px;
            max-height: 150px;
            overflow-y: auto;
            text-align: left;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .autocomplete-item {
            padding: 8px 10px;
            cursor: pointer;
            font-size: 13px;
            color: var(--dark-gray);
        }

        .autocomplete-item:hover,
        .autocomplete-item.selected {
            background: var(--blue-pale);
            color: var(--primary-blue);
            font-weight: 600;
        }

        .hour-input-group {
            display: flex;
            flex-direction: column;
            gap: 3px;
            margin-bottom: 8px;
            align-items: flex-start;
            width: 100%;
        }

        .unidades-table td:nth-child(2) .hour-input-group,
        .unidades-table td:nth-child(3) .hour-input-group,
        .unidades-table td:nth-child(4) .hour-input-group {
            align-items: center;
            text-align: center;
        }

        .unidades-table td .hour-input-group label {
            justify-content: center;
            width: 100%;
        }

        .hour-input-group:last-of-type {
            margin-bottom: 0;
        }

        .hour-input-group .table-input {
            padding: 4px 6px;
            max-width: 100%;
            margin: 0;
        }

        .hour-input-group .table-input.small {
            max-width: 80px;
        }

        .hour-input-result {
            background: var(--light-gray);
            font-weight: 600;
            color: var(--blue-dark);
            border-width: 2px;
        }

        .pasajero-container {
            display: flex;
            flex-direction: column;
            max-height: 150px;
            overflow-y: auto;
            padding-right: 5px;
            padding-top: 5px;
        }

        .pasajero-input-group {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-add-pasajero,
        .btn-remove-pasajero {
            border: none;
            border-radius: 6px;
            padding: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-add-pasajero {
            background: var(--accent-green);
            color: var(--white);
        }

        .btn-add-pasajero:hover:not(:disabled) {
            background: #3d8b40;
            transform: scale(1.05);
        }

        .btn-add-pasajero:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .btn-remove-pasajero {
            background: var(--accent-red);
            color: var(--white);
        }

        .btn-remove-pasajero:hover {
            background: #d32f2f;
            transform: scale(1.05);
        }

        .btn-inspeccion {
            background: var(--primary-orange);
            color: var(--white);
            padding: 8px 10px;
            border-radius: 8px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: all 0.3s ease;
            border: 1px solid #c27025;
            width: 40px;
            height: 40px;
            margin: 0 auto;
            text-decoration: none;
        }

        .btn-inspeccion i {
            font-size: 18px;
        }

        .btn-inspeccion:hover {
            background: #e68900;
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(214, 126, 41, 0.4);
        }

        .btn-submit.btn-inspeccion-aprobado {
            background: linear-gradient(135deg, var(--accent-green) 0%, #3d8b40 100%);
            border: 1px solid #2e7d32;
            padding: 8px;
            width: 40px;
            height: 40px;
            margin: 0 auto;
        }

        .btn-submit.btn-inspeccion-aprobado i {
            font-size: 18px;
        }

        .btn-inspeccion span {
            display: none;
        }

        .btn-add-unidad {
            width: 100%;
            padding: 12px;
            background: var(--blue-pale);
            color: var(--primary-blue);
            border: 2px dashed var(--blue-light);
            border-radius: 8px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
            box-shadow: 0 3px 10px rgba(51, 76, 149, 0.1);
        }

        .btn-add-unidad:hover:not(:disabled) {
            background: var(--blue-very-light);
            border-color: var(--primary-blue);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(51, 76, 149, 0.2);
        }

        .btn-add-unidad:disabled {
            background: var(--light-gray);
            color: var(--medium-gray);
            border: 2px dashed var(--border-gray);
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* 🚀 NUEVOS ESTILOS PARA TIPO DE VEHÍCULO (LIGERA/PESADA) 🚀 */
        .tipo-vehiculo-text {
            display: block;
            text-align: center;
            font-size: 11px;
            margin-top: 4px;
            font-weight: 600;
        }

        .tipo-ligera {
            color: var(--primary-blue);
        }

        .tipo-pesada {
            color: var(--primary-orange);
        }

        .acciones-td {
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
            height: 100%;
            min-height: 200px;
        }

        .btn-accion {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid var(--border-gray);
            background: var(--white);
            color: var(--medium-gray);
        }

        .btn-accion:hover {
            transform: scale(1.05);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-accion.eliminar:hover {
            background: rgba(244, 67, 54, 0.1);
            color: var(--accent-red);
            border-color: var(--accent-red);
        }

        .unidades-count {
            display: inline-block;
            background: var(--primary-orange);
            color: var(--white);
            font-size: 13px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 12px;
            margin-left: 10px;
        }

        #tipoViajeTexto {
            font-size: 16px;
            font-weight: 700;
            color: var(--blue-dark);
            margin-right: 10px;
        }

        .form-footer {
            background: var(--background-gray);
            padding: 20px 30px;
            border-radius: 0 0 15px 15px;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            border-top: 1px solid var(--border-gray);
        }

        #modalInspeccion .modal-content,
        #modalPreConvoy .modal-content,
        #modalEvaluacion .modal-content {
            max-width: 800px;
        }

        .modal-inspeccion-body {
            padding: 30px;
        }

        .inspeccion-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .inspeccion-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid var(--border-gray);
        }

        .inspeccion-item:nth-child(2n) {
            border-bottom: 1px solid var(--border-gray);
        }

        .inspeccion-item:nth-last-child(-n + 2) {
            border-bottom: none;
        }

        @media (max-width: 600px) {
            .inspeccion-grid {
                grid-template-columns: 1fr;
            }

            .inspeccion-item {
                border-bottom: 1px solid var(--border-gray) !important;
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .inspeccion-item:last-child {
                border-bottom: none !important;
            }
        }

        .inspeccion-item-label {
            font-weight: 500;
            color: var(--blue-dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .inspeccion-radio-group {
            display: flex;
            gap: 15px;
        }

        .inspeccion-radio-group label {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 400;
        }

        .inspeccion-radio-group input[type="radio"] {
            appearance: none;
            width: 16px;
            height: 16px;
            border: 2px solid var(--medium-gray);
            border-radius: 50%;
            transition: all 0.2s;
            position: relative;
            top: 0;
            margin: 0;
            flex-shrink: 0;
        }

        .inspeccion-radio-group input[type="radio"]:checked {
            border-color: var(--primary-blue);
            background-color: var(--primary-blue);
            border-width: 5px;
        }

        .inspeccion-radio-group label.si input[type="radio"]:checked {
            border-color: var(--accent-green);
            background-color: var(--accent-green);
        }

        .inspeccion-radio-group label.no input[type="radio"]:checked {
            border-color: var(--accent-red);
            background-color: var(--accent-red);
        }

        .inspeccion-modal-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 20px;
            padding-bottom: 5px;
            border-bottom: 2px solid var(--primary-orange);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-viajes {
            padding: 10px 22px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--blue-light) 100%);
            color: var(--white);
            border: 1px solid var(--blue-dark);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--blue-dark) 0%, var(--primary-blue) 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(51, 76, 149, 0.3);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--primary-blue);
            border: 1px solid var(--border-gray);
        }

        .btn-cancel {
            background: var(--white);
            color: var(--medium-gray);
            border: 1px solid var(--border-gray);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--accent-green) 0%, #3d8b40 100%);
            color: var(--white);
            border: 1px solid #2e7d32;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #3d8b40 0%, var(--accent-green) 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }

        .btn-secondary-convoy:disabled {
            background: var(--light-gray);
            color: var(--medium-gray);
            border: 1px solid var(--border-gray);
            font-weight: 700;
            padding: 6px 16px;
            font-size: 0.9rem;
            border-radius: 4px;
            cursor: not-allowed;
            opacity: 0.7;
            display: flex;
            align-items: center;
            margin-left: auto;
        }

        .btn-secondary-convoy {
            background: var(--primary-orange);
            color: var(--white);
            border: 1px solid var(--primary-orange);
            font-weight: 700;
            padding: 6px 16px;
            font-size: 0.9rem;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            margin-left: auto;
            transition: all 0.2s ease;
        }

        .btn-secondary-convoy i {
            color: var(--white) !important;
            background: transparent !important;
            margin-right: 8px;
            font-weight: normal;
        }

        .btn-secondary-convoy:hover:not(:disabled) {
            background: var(--orange-dark);
            border-color: var(--orange-dark);
            transform: translateY(-1px);
        }

        .btn-secondary-convoy-completed {
            background: var(--white) !important;
            color: var(--accent-green) !important;
            border: 1px solid var(--white) !important;
            font-weight: 700;
            padding: 6px 16px;
            font-size: 0.9rem;
            border-radius: 4px;
            margin-left: auto;
            display: flex;
            align-items: center;
            cursor: default;
            pointer-events: none;
        }

        .btn-secondary-convoy-completed i {
            color: var(--accent-green) !important;
            background: transparent !important;
            margin-right: 8px;
            font-weight: normal;
        }

        /* 🚀 ESTILOS PARA EL NUEVO BOTÓN DE EVALUACIÓN 🚀 */
        #btnEvaluacionRiesgo {
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            font-weight: 700;
            font-size: 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        /* Estado deshabilitado (cuando no hay unidades) */
        #btnEvaluacionRiesgo:disabled {
            background: var(--light-gray);
            color: var(--medium-gray);
            border: 2px solid var(--border-gray);
            cursor: not-allowed;
            opacity: 0.7;
        }

        /* Estado habilitado pero no realizado (Alerta visual) */
        #btnEvaluacionRiesgo:not(:disabled):not(.evaluacion-completada) {
            background: var(--white);
            color: var(--accent-red);
            border: 2px solid var(--accent-red);
            box-shadow: 0 4px 6px rgba(244, 67, 54, 0.1);
        }

        #btnEvaluacionRiesgo:not(:disabled):not(.evaluacion-completada):hover {
            background: #ffebee;
            transform: translateY(-2px);
        }

        /* Estado completado (Verde) */
        #btnEvaluacionRiesgo.evaluacion-completada {
            background: var(--white);
            color: var(--accent-green);
            border: 2px solid var(--accent-green);
            pointer-events: none;
            /* Bloquear clic después de realizarla si se desea */
        }

        .evaluacion-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .evaluacion-item {
            border: 1px solid var(--border-gray);
            padding: 15px;
            border-radius: 8px;
            background: #fafafa;
        }

        .evaluacion-titulo {
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 10px;
            font-size: 13px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .evaluacion-opciones {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .evaluacion-radio {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 12px;
            cursor: pointer;
        }

        .evaluacion-radio input {
            margin-top: 2px;
        }


        .footer-viajes {
            background: var(--blue-dark);
            color: var(--white);
            padding: 15px 25px;
            text-align: center;
            font-size: 13px;
            border-top: 3px solid var(--primary-orange);
            flex-shrink: 0;
        }

        .footer-viajes p {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        @media (max-width: 768px) {
            .container-viajes {
                padding: 15px;
            }

            .header-viajes {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }

            .nav-viajes {
                flex-wrap: wrap;
                justify-content: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .table-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                margin: 10px;
            }

            .form-body {
                padding: 15px;
            }

            .form-footer {
                flex-direction: column;
            }

            .btn-viajes {
                width: 100%;
                justify-content: center;
            }

            .evaluacion-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <header class="header-viajes">
        <div class="logo-container-viajes">
            <div class="logo-viajes">
                <div class="logo-img-viajes">
                    <img src="{{ asset('assets/img/logovinco1.png') }}" alt="Vinco Energy">
                </div>
            </div>
        </div>

        <nav class="nav-viajes">
            <a href="#" class="nav-link-viajes active" data-route="dashboard">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="#" class="nav-link-viajes" data-route="historial">
                <i class="fas fa-history"></i> Historial
            </a>
            <a href="#" class="nav-link-viajes" data-route="reportes">
                <i class="fas fa-chart-bar"></i> Reportes
            </a>
            <a href="#" class="nav-link-viajes" data-route="unidades">
                <i class="fas fa-truck-moving"></i> Gestión de Unidades
            </a>
        </nav>

        @include('components.layouts._user-profile')
    </header>

    <main class="container-viajes" id="main-content">
        <div class="card-base">
            <div class="compact-header">
                <div class="header-content">
                    <div class="title-section">
                        <h1 class="travel-title">
                            <i class="fas fa-route"></i>
                            Gerenciamiento de Viajes
                        </h1>
                        <p class="travel-subtitle">
                            Panel de control y registro completo de los viajes vehiculares de la compañía
                        </p>
                    </div>

                    <div class="header-stats stats-grid">
                        <div class="stat-card stat-active">
                            <div class="stat-icon stat-active-icon">
                                <i class="fas fa-truck-moving"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="active-count">18</span>
                                <span class="stat-label">Activos</span>
                            </div>
                        </div>

                        <div class="stat-card stat-pending">
                            <div class="stat-icon stat-pending-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="pending-count">5</span>
                                <span class="stat-label">Pendientes</span>
                            </div>
                        </div>

                        <div class="stat-card stat-completed">
                            <div class="stat-icon stat-completed-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="completed-count">89</span>
                                <span class="stat-label">Completados</span>
                            </div>
                        </div>

                        <div class="stat-card stat-available">
                            <div class="stat-icon stat-available-icon">
                                <i class="fas fa-car-side"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="available-count">42</span>
                                <span class="stat-label">Unidades Disp.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="filters-section">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="solicitud-date">
                            <i class="fas fa-calendar-day"></i>
                            Fecha Solicitud de Viaje
                        </label>
                        <input type="date" id="solicitud-date" class="form-control">
                    </div>

                    <div class="filter-group">
                        <label for="destino-filter">
                            <i class="fas fa-map-marker-alt"></i>
                            Destino del Viaje
                        </label>
                        <select id="destino-filter" class="form-control">
                            <option value="all">Todos los Destinos</option>
                            <option value="COA">Coatzacoalcos</option>
                            <option value="TAB">Paraíso, Tab.</option>
                            <option value="CAR">Cd. del Carmen</option>
                            <option value="OTRO">Otro Destino</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="status-filter">
                            <i class="fas fa-filter"></i>
                            Estatus
                        </label>
                        <select id="status-filter" class="form-control">
                            <option value="all">Todos</option>
                            <option value="approved">Aprobado</option>
                            <option value="reviewed">Revisado</option>
                            <option value="rejected">Rechazado</option>
                            <option value="under_review">En Revisión</option>
                            <option value="completed">Completado</option>
                            <option value="active">Activo (En Curso)</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="riesgo-filter">
                            <i class="fas fa-exclamation-triangle"></i>
                            Nivel de Riesgo
                        </label>
                        <select id="riesgo-filter" class="form-control">
                            <option value="all">Todos</option>
                            <option value="bajo">Bajo</option>
                            <option value="medio">Medio</option>
                            <option value="alto">Alto</option>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button class="btn-clear-filters" id="clear-filters">
                            <i class="fas fa-eraser"></i>
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-dashboard-container">
            <div class="table-header">
                <h3>
                    <i class="fas fa-history"></i>
                    Viajes Recientes
                </h3>
                <button type="button" class="btn-viajes btn-primary" onclick="gestionarModalFormulario(true)">
                    <i class="fas fa-plus-circle"></i>
                    Nueva Solicitud de Viaje
                </button>
            </div>
            <table class="table-viajes">
                <thead>
                    <tr>
                        <th class="th-codigo">N°</th>
                        <th class="th-nombre">Solicitante</th>
                        <th class="th-departamento">Departamento</th>
                        <th class="th-destino">Destino</th>
                        <th class="th-fechas">Fechas de Viaje</th>
                        <th class="th-riesgo">Riesgo</th>
                        <th class="th-estado">Estado</th>
                        <th class="th-acciones">Acc</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>GV-001</strong></td>
                        <td>Juan Pérez González</td>
                        <td>Operaciones</td>
                        <td>Coatzacoalcos, Ver.</td>
                        <td>15 al 20 Mar 2025</td>
                        <td>
                            <span class="badge-riesgo status-riesgo-bajo">Bajo</span>
                        </td>
                        <td>
                            <span class="badge-status status-aprobado">Aprobado</span>
                        </td>
                        <td>
                            <button class="btn-action-small btn-view" title="Ver detalles">
                                <i class="fas fa-eye"></i> Ver
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>GV-002</strong></td>
                        <td>María González Sánchez</td>
                        <td>Administración</td>
                        <td>Paraíso, Tab.</td>
                        <td>22 al 25 Mar 2025</td>
                        <td>
                            <span class="badge-riesgo status-riesgo-medio">Medio</span>
                        </td>
                        <td>
                            <span class="badge-status status-pendiente">Pendiente</span>
                        </td>
                        <td>
                            <button class="btn-action-small btn-edit" title="Editar">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>GV-003</strong></td>
                        <td>Carlos Rodríguez López</td>
                        <td>Comercial</td>
                        <td>Cárdenas, Tab.</td>
                        <td>10 al 12 Abr 2025</td>
                        <td>
                            <span class="badge-riesgo status-riesgo-alto">Alto</span>
                        </td>
                        <td>
                            <span class="badge-status status-encurso">En Curso</span>
                        </td>
                        <td>
                            <button class="btn-action-small btn-view" title="Ver reporte">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>GV-004</strong></td>
                        <td>Ana López Martínez</td>
                        <td>Operaciones</td>
                        <td>Ciudad del Carmen, Camp.</td>
                        <td>01 al 03 May 2025</td>
                        <td>
                            <span class="badge-riesgo status-riesgo-bajo">Bajo</span>
                        </td>
                        <td>
                            <span class="badge-status status-cancelado">Cancelado</span>
                        </td>
                        <td>
                            <button class="btn-action-small btn-view" title="Ver detalles">
                                <i class="fas fa-eye"></i> Ver
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

    <div class="modal-overlay" id="modalFormulario">
        <div class="modal-content">
            <div class="form-header-modal">
                <div class="form-header-info">
                    <div class="logo-img-viajes">
                        <img src="{{ asset('assets/img/logovinco2.png') }}" alt="Vinco Energy">
                    </div>
                    <div class="form-header-title">
                        <h2>Solicitud de Gerenciamiento de Viaje</h2>
                        <p>Formato F-6.1-VINCO-04</p>
                    </div>
                </div>

                <div class="header-right-group">
                    <div class="form-document-detail request-date">
                        <label>Fecha de Solicitud</label>
                        <span id="fechaSolicitudDisplay">08/12/2025</span>
                        <input type="hidden" name="fecha_solicitud" id="fechaSolicitudHidden">
                    </div>

                    <div class="form-document-detail travel-code">
                        <label>N° de Gerenciamiento</label>
                        <span class="form-code-value" id="codigoViaje">N°:GV-004</span>
                    </div>
                </div>
            </div>

            <form id="formViaje" class="form-body">
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-info-circle"></i>
                        Información General
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>
                                <i class="fas fa-user-tie"></i>
                                Conductor (Creador de GV) <span class="required">*</span>
                            </label>
                            <input type="text" name="solicitante" id="solicitante" readonly required>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-building"></i>
                                Departamento <span class="required">*</span>
                            </label>
                            <input type="text" name="departamento" id="departamento" readonly required>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-map-marker-alt"></i>
                                Destino del Viaje <span class="required">*</span>
                            </label>
                            <select name="destino_predefinido" id="destinoPredefinido" required>
                                <option value="">Seleccione un lugar de la lista</option>
                                <option value="Coatzacoalcos, Veracruz">Coatzacoalcos, Veracruz</option>
                                <option value="Paraíso, Tabasco">Paraíso, Tabasco</option>
                                <option value="Cárdenas, Tabasco">Cárdenas, Tabasco</option>
                                <option value="Ciudad del Carmen, Campeche">Ciudad del Carmen, Campeche</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-pencil-alt"></i>
                                Especifique Destino
                            </label>
                            <input type="text" name="destino_especifico" id="destinoEspecifico"
                                placeholder="Especifique el destino aquí (obligatorio si selecciona 'Otro')">
                        </div>

                        <div class="form-group">
                            <label style="font-size: 13px; color: var(--blue-dark);">
                                <i class="fas fa-map-signs"></i>
                                Paradas?
                            </label>
                            <div class="radio-group-paradas">
                                <label class="radio-option">
                                    <input type="radio" name="tiene_paradas" value="no" checked
                                        onclick="toggleSeccionParadas(false)">
                                    No
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="tiene_paradas" value="si"
                                        onclick="toggleSeccionParadas(true)">
                                    Sí
                                </label>
                            </div>
                        </div>
                    </div>

                    <h3 class="form-section-title">
                        <i class="fas fa-route"></i>
                        Detalles del Trayecto
                    </h3>

                    <div class="trayecto-grid">
                        <div class="form-group">
                            <label>
                                <i class="fas fa-map-pin"></i>
                                Saliendo de <span class="required">*</span>
                            </label>
                            <input type="text" name="origen" placeholder="Ej: Villahermosa, Tabasco" required>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-map-marker"></i>
                                Llegando a
                            </label>
                            <input type="text" name="llegada" placeholder="Ej: Villahermosa, Tabasco (Opcional)">
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-calendar-day"></i>
                                Fecha de Inicio <span class="required">*</span>
                            </label>
                            <div class="datetime-input-wrapper">
                                <input type="text" name="fecha_inicio" id="fechaInicioViaje"
                                    placeholder="Seleccione fecha" required>
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-calendar-check"></i>
                                Fecha de Término
                            </label>
                            <div class="datetime-input-wrapper">
                                <input type="text" name="fecha_fin" id="fechaFinViaje"
                                    placeholder="Seleccione fecha">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-clock"></i>
                                Hora de Inicio <span class="required">*</span>
                            </label>
                            <div class="datetime-input-wrapper">
                                <input type="text" name="hora_inicio" id="horaInicioViaje"
                                    placeholder="HH:MM AM/PM" required>
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-clock"></i>
                                Hora de Término
                            </label>
                            <div class="datetime-input-wrapper">
                                <input type="text" name="hora_fin" id="horaFinViaje" placeholder="HH:MM AM/PM">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>

                    <div id="contenedorParadas" class="hidden">
                        <div id="listaParadas">
                        </div>
                        <button type="button" class="btn-add-parada" id="btnAgregarParada"
                            onclick="agregarParada()">
                            <i class="fas fa-plus"></i> Agregar Otra Parada
                        </button>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-truck-moving"></i>
                        Conductor y Unidad Solicitada
                        <span class="unidades-count" id="contadorUnidades">0</span>
                        <span class="unidades-label" id="label-tipo-unidad"></span>
                        <button type="button" class="btn-viajes btn-secondary-convoy" id="btnReunionPreConvoy"
                            onclick="gestionarModalPreConvoy(true)" disabled
                            title="Realizar la reunión antes de enviar la solicitud.">
                            <i class="fas fa-handshake"></i> Reunión Pre-convoy
                        </button>
                    </h3>

                    <div class="unidades-container">
                        <table class="unidades-table" id="tablaUnidades">
                            <thead>
                                <tr>
                                    <th class="th-conductor-completo">
                                        <span class="column-title">Conductor</span>
                                    </th>

                                    <th class="th-vigencia">
                                        <span class="column-title">Vigencias</span>
                                    </th>

                                    <th class="th-hrs-sueno">
                                        <span class="column-title">Hrs Sueño</span>
                                    </th>

                                    <th class="th-horas-conduccion">
                                        <span class="column-title">Hrs Conducción</span>
                                    </th>

                                    <th class="th-pasajeros">
                                        <span class="column-title">Pasajeros</span>
                                    </th>

                                    <th class="th-vehiculo">
                                        <span class="column-title">Vehículo</span>
                                    </th>

                                    <th class="th-inspeccion">
                                        <span class="column-title">Insp.</span>
                                    </th>

                                    <th class="th-acciones">
                                        <span class="column-title">Acciones</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="cuerpoTablaUnidades">
                            </tbody>
                        </table>
                    </div>

                    <button type="button" class="btn-add-unidad" id="btnAgregarUnidad" onclick="agregarUnidad()">
                        <i class="fas fa-plus-circle"></i>
                        Agregar Unidad (Máximo 5)
                    </button>

                    <button type="button" id="btnEvaluacionRiesgo" onclick="gestionarModalEvaluacion(true)"
                        disabled>
                        <i class="fas fa-list-check"></i> Realizar Evaluación del Viaje (Requerido)
                    </button>
                </div>
            </form>

            <div class="form-footer">
                <button type="button" class="btn-viajes btn-cancel" onclick="gestionarModalFormulario(false)">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" form="formViaje" class="btn-viajes btn-submit" id="btnEnviarSolicitud">
                    <i class="fas fa-paper-plane"></i>
                    Enviar Solicitud
                </button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalEvaluacion">
        <div class="modal-content">
            <div class="form-header-modal">
                <div class="form-header-info">
                    <div class="form-header-title">
                        <h2>Evaluación de Riesgos del Viaje</h2>
                        <p>Seleccione una opción para cada categoría.</p>
                    </div>
                </div>
                <div class="form-code-document">
                    <button type="button" class="btn-viajes btn-cancel" onclick="gestionarModalEvaluacion(false)">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>

            <div class="modal-inspeccion-body">
                <form id="formEvaluacionRiesgo">
                    <div class="evaluacion-grid">
                        <div class="evaluacion-item">
                            <div class="evaluacion-titulo">1. Curso manejo defensivo</div>
                            <div class="evaluacion-opciones">
                                <label class="evaluacion-radio"><input type="radio" name="ev_manejo"
                                        value="0" required> Todos los conductores cuentan con el manejo defensivo
                                    vigente</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_manejo"
                                        value="5"> 1 o más conductores con manejo defensivo no vigente</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_manejo"
                                        value="10"> 1 o más conductores No tiene manejo defensivo</label>
                            </div>
                        </div>

                        <div class="evaluacion-item">
                            <div class="evaluacion-titulo">2. Horas despierto</div>
                            <div class="evaluacion-opciones">
                                <label class="evaluacion-radio"><input type="radio" name="ev_horas" value="0"
                                        required> De 1 a 8 hrs</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_horas"
                                        value="5"> De 9 a 12 hrs</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_horas"
                                        value="10"> De 13 a 16 hrs</label>
                            </div>
                        </div>

                        <div class="evaluacion-item">
                            <div class="evaluacion-titulo">3. Núm. de vehículos y pasajeros</div>
                            <div class="evaluacion-opciones">
                                <label class="evaluacion-radio"><input type="radio" name="ev_vehiculos"
                                        value="0" required> Convoy con un pasajero o más por vehículo</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_vehiculos"
                                        value="5"> Convoy sin pasajeros / Un vehículo con pasajero(s)</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_vehiculos"
                                        value="10"> Un vehículo sin pasajeros (solo conductor)</label>
                            </div>
                        </div>

                        <div class="evaluacion-item">
                            <div class="evaluacion-titulo">4. Comunicación</div>
                            <div class="evaluacion-opciones">
                                <label class="evaluacion-radio"><input type="radio" name="ev_comunicacion"
                                        value="0" required> Celulares con señal</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_comunicacion"
                                        value="5"> Algunas zonas sin señal</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_comunicacion"
                                        value="10"> Todo el viaje sin señal</label>
                            </div>
                        </div>

                        <div class="evaluacion-item">
                            <div class="evaluacion-titulo">5. Condiciones clima</div>
                            <div class="evaluacion-opciones">
                                <label class="evaluacion-radio"><input type="radio" name="ev_clima" value="0"
                                        required> Clima seco (No lluvias)</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_clima"
                                        value="5"> Clima parcialmente nublado / Llovizna</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_clima"
                                        value="10"> Clima nublado / Lluvia fuerte</label>
                            </div>
                        </div>

                        <div class="evaluacion-item">
                            <div class="evaluacion-titulo">6. Condiciones de iluminación</div>
                            <div class="evaluacion-opciones">
                                <label class="evaluacion-radio"><input type="radio" name="ev_iluminacion"
                                        value="0" required> Iluminación Clara</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_iluminacion"
                                        value="5"> Atardecer / Iluminación excesiva</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_iluminacion"
                                        value="10"> Noche - Poca o nula iluminación</label>
                            </div>
                        </div>

                        <div class="evaluacion-item">
                            <div class="evaluacion-titulo">7. Condiciones de la carretera</div>
                            <div class="evaluacion-opciones">
                                <label class="evaluacion-radio"><input type="radio" name="ev_carretera"
                                        value="0" required> En buen estado, condiciones normales</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_carretera"
                                        value="5"> Carretera con baches, huecos, mal estado</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_carretera"
                                        value="5"> Zonas de carretera con tráfico / reparaciones</label>
                            </div>
                        </div>

                        <div class="evaluacion-item">
                            <div class="evaluacion-titulo">8. Otras Cond. de la carretera</div>
                            <div class="evaluacion-opciones">
                                <label class="evaluacion-radio"><input type="radio" name="ev_otras" value="5"
                                        required> Zona con curvas y pendientes</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_otras"
                                        value="10"> Carretera con superficies mojadas; vados</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_otras"
                                        value="10"> Carretera solitaria (unidad no puede detenerse)</label>
                            </div>
                        </div>

                        <div class="evaluacion-item">
                            <div class="evaluacion-titulo">9. Animales de la zona</div>
                            <div class="evaluacion-opciones">
                                <label class="evaluacion-radio"><input type="radio" name="ev_animales"
                                        value="0" required> No se conoce actividad o cruce</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_animales"
                                        value="5"> Poca actividad o cruce de animales</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_animales"
                                        value="10"> Alta actividad o cruce de animales</label>
                            </div>
                        </div>

                        <div class="evaluacion-item">
                            <div class="evaluacion-titulo">10. Seguridad de la ruta</div>
                            <div class="evaluacion-opciones">
                                <label class="evaluacion-radio"><input type="radio" name="ev_seguridad"
                                        value="0" required> No se conoce actividad de riesgo</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_seguridad"
                                        value="5"> Se sabe de mediana actividad de riesgo</label>
                                <label class="evaluacion-radio"><input type="radio" name="ev_seguridad"
                                        value="10"> Se sabe de alta actividad de riesgo</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="form-footer">
                <button type="button" class="btn-viajes btn-primary" onclick="guardarEvaluacion()">
                    <i class="fas fa-save"></i> Guardar Evaluación
                </button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalPreConvoy">
        <div class="modal-content">
            <div class="form-header-modal">
                <div class="form-header-info">
                    <div class="form-header-title">
                        <h2>REUNIÓN PRE-CONVOY Y CHECKLIST DE SEGURIDAD</h2>
                        <p>Asegure la coordinación y seguridad de todas las unidades.</p>
                    </div>
                </div>
                <div class="form-code-document">
                    <button type="button" class="btn-viajes btn-cancel" onclick="gestionarModalPreConvoy(false)">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>

            <form id="formPreConvoy" class="modal-inspeccion-body">
                <h3 class="inspeccion-modal-title">
                    <i class="fas fa-users"></i>
                    Designación de Líder y Confirmación de Seguridad
                </h3>

                <div class="form-group full-width" style="margin-bottom: 30px;">
                    <label for="liderConvoy">
                        <i class="fas fa-crown"></i>
                        Líder de Convoy <span class="required">*</span>
                    </label>
                    <select id="liderConvoy" name="lider_convoy" class="form-control" required>
                        <option value="">Seleccione un conductor como Líder</option>
                    </select>
                </div>

                <div class="inspeccion-grid" id="checklistPreConvoy">
                </div>
            </form>

            <div class="form-footer">
                <button type="button" class="btn-viajes btn-primary" id="btnGuardarPreConvoy"
                    onclick="guardarPreConvoy()">
                    <i class="fas fa-save"></i> Guardar y Confirmar Reunión
                </button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalInspeccion">
        <div class="modal-content">
            <div class="form-header-modal">
                <div class="form-header-info">
                    <div class="form-header-title">
                        <h2>INSPECCIÓN VEHICULAR PRE-VIAJE</h2>
                        <p>Unidad: <span id="inspeccionUnidadNombre">N/A</span></p>
                    </div>
                </div>
                <div class="form-code-document">
                    <button type="button" class="btn-viajes btn-cancel" onclick="gestionarModalInspeccion(false)">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>

            <form id="formInspeccion" class="modal-inspeccion-body">
                <input type="hidden" id="inspeccionUnidadIndex" name="unidad_index">

                <h3 class="inspeccion-modal-title">
                    <i class="fas fa-car-side"></i>
                    Revisión de Documentos y Elementos de Seguridad
                </h3>

                <div class="inspeccion-grid" id="inspeccionGrid">
                </div>
            </form>

            <div class="form-footer">
                <button type="button" class="btn-viajes btn-primary" onclick="guardarInspeccion()">
                    <i class="fas fa-save"></i> Guardar Inspección
                </button>
            </div>
        </div>
    </div>

    <footer class="footer-viajes">
        <p>
            <i class="fas fa-shield-alt"></i>
            Sistema de Gerenciamiento de Viajes - Vinco Energy © 2025 | Todos los derechos reservados
        </p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // El código siempre es completo.
        let codigoViaje = 4;
        let contadorUnidades = 0;
        const MAX_UNIDADES = 5;
        const MAX_PASAJEROS = 4;
        const MAX_PARADAS = 4;

        // Almacenamiento temporal
        const datosInspeccion = {};
        // 🔑 Almacenamiento temporal para la reunión de convoy 🔑
        let datosReunionConvoy = {}; // Para guardar los datos del checklist
        let reunionPreConvoyGuardada = false;

        // 🚀 Variables para manejo de Paradas
        let contadorParadas = 0;

        // 🚀 Variables para Evaluación de Riesgo 🚀
        let evaluacionRiesgoGuardada = false;
        let puntajeRiesgoTotal = 0; // Se calcula pero no se muestra

        // Datos de ejemplo
        const vehiculos = [
            "Camioneta Pick-up",
            "Camión de Carga 3.5T",
            "Camión de Carga 5T",
            "SUV Ejecutivo",
            "Vehículo de Servicio",
            "Grúa",
            "Motoniveladora",
            "Retroexcavadora"
        ];

        // 🚀 NUEVA CLASIFICACIÓN DE VEHÍCULOS 🚀
        const clasificacionVehiculos = {
            "Camioneta Pick-up": "Unidad Ligera",
            "SUV Ejecutivo": "Unidad Ligera",
            "Vehículo de Servicio": "Unidad Ligera",
            "Camión de Carga 3.5T": "Unidad Pesada",
            "Camión de Carga 5T": "Unidad Pesada",
            "Grúa": "Unidad Pesada",
            "Motoniveladora": "Unidad Pesada",
            "Retroexcavadora": "Unidad Pesada"
        };


        // Lugares predefinidos para el nuevo selector
        const lugaresDestino = [
            "Coatzacoalcos, Veracruz",
            "Paraíso, Tabasco",
            "Cárdenas, Tabasco",
            "Ciudad del Carmen, Campeche"
        ];


        const datosConductores = {
            "Juan Pérez González": {
                vigencia: "2026-05-10",
                manDefVigencia: "2026-10-01"
            },
            "María González Sánchez": {
                vigencia: "2025-12-01",
                manDefVigencia: "2025-11-28"
            },
            "Carlos Rodríguez López": {
                vigencia: "2027-01-20",
                manDefVigencia: "2027-01-15"
            },
            "Ana López Martínez": {
                vigencia: "2025-10-15",
                manDefVigencia: "2025-09-30"
            },
            "Pedro Martínez Ruiz": {
                vigencia: "2026-11-25",
                manDefVigencia: "2026-11-15"
            },
            "Raúl Sánchez Gómez": {
                vigencia: "2027-03-08",
                manDefVigencia: "2027-03-01"
            },
            "Laura Ramírez Hernández": {
                vigencia: "2025-08-30",
                manDefVigencia: "2025-08-25"
            },
            "Gabriel Torres Cruz": {
                vigencia: "2026-06-12",
                manDefVigencia: "2026-06-05"
            }
        };

        const conductores = Object.keys(datosConductores);

        // Ítems de inspección
        const itemsInspeccion = [{
                label: "Documentos en regla (Tarjeta, Póliza)",
                icon: "fas fa-file-alt",
                name: "docs"
            },
            {
                label: "Llantas en buen estado (incl. refacción)",
                icon: "fas fa-tire",
                name: "llantas"
            },
            {
                label: "Luces funcionales (alta, baja, freno)",
                icon: "fas fa-lightbulb",
                name: "luces"
            },
            {
                label: "Extintor vigente y cargado",
                icon: "fas fa-fire-extinguisher",
                name: "extintor"
            },
            {
                label: "Botiquín de primeros auxilios",
                icon: "fas fa-suitcase-medical",
                name: "botiquin"
            },
            {
                label: "Kit de carretera (triángulos, chaleco)",
                icon: "fas fa-road",
                name: "kit"
            },
            {
                label: "Niveles de fluidos (aceite, agua)",
                icon: "fas fa-gas-pump",
                name: "fluidos"
            },
            {
                label: "Frenos en óptimas condiciones",
                icon: "fas fa-hand-paper",
                name: "frenos"
            }
        ];

        // 🔑 Ítems del Checklist de Reunión Pre-Convoy 🔑
        const checklistPreConvoy = [{
                label: "Todos los conductores comprenden donde serán los puntos de parada o reporte?",
                name: "puntos_parada",
                icon: "fas fa-map-marked-alt"
            },
            {
                label: "¿Todos los conductores saben que hacer en caso de ruptura del convoy?",
                name: "ruptura_convoy",
                icon: "fas fa-road-barrier"
            },
            {
                label: "Se asegura que todos los conductores verificarón la documentación vigente (tarjeta, poliza, permisos)",
                name: "doc_vigente",
                icon: "fas fa-folder-open"
            },
            {
                label: "Se asegura que los conductores sean concientes de aplicar la medidas y controles para prevenir accidentes o incidentes",
                name: "prevencion_acc",
                icon: "fas fa-helmet-safety"
            },
            {
                label: "Todos los conductores tiene y llevan consigo los contactos de emergencia / PRE",
                name: "contactos_emerg",
                icon: "fas fa-phone-volume"
            },
            {
                label: "Como líder de convoy manifiesta tener el compromiso y liderazgo para guiar este viaje",
                name: "compromiso_lider",
                icon: "fas fa-users-line"
            }
        ];


        // Nueva configuración para la hora con selector visual AM/PM
        const configHoraVisual = {
            enableTime: true,
            noCalendar: true,
            dateFormat: "h:i K", // h:i para 12h, K para AM/PM
            time_24hr: false, // Forzar modo 12 horas para usar AM/PM
            minuteIncrement: 15,
            disableMobile: true,
            allowInput: true,
            static: true, // Para mantener el selector de hora como un dropdown más bonito.
            wrap: false, // Importante para que no aplique estilos adicionales
            locale: flatpickr.l10ns.es, // Aplicar localización en AM/PM
        };

        // Inicialización de Flatpickr
        function inicializarFlatpickr() {
            flatpickr.localize(flatpickr.l10ns.es);

            // Configuración para fechas en español
            const configFecha = {
                dateFormat: "d/m/Y",
                locale: "es",
                minDate: "today",
                disableMobile: true, // Mejor experiencia en desktop
                allowInput: true,
                clickOpens: true,
                nextArrow: '<i class="fas fa-chevron-right"></i>',
                prevArrow: '<i class="fas fa-chevron-left"></i>',
            };

            // Aplicar a los campos principales
            const fechaInicio = flatpickr("#fechaInicioViaje", configFecha);
            const fechaFin = flatpickr("#fechaFinViaje", configFecha);

            // Aplicar la nueva configuración de hora con AM/PM al modal principal
            const horaInicio = flatpickr("#horaInicioViaje", configHoraVisual);
            const horaFin = flatpickr("#horaFinViaje", configHoraVisual);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Configurar fecha actual y departamento
            const hoy = new Date();

            // Formato legible para el span (DD/MM/YYYY)
            const fechaDisplay = hoy.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });

            // Formato para el input hidden (YYYY-MM-DD)
            const fechaHidden = hoy.toISOString().split('T')[0];

            document.getElementById('fechaSolicitudDisplay').textContent = fechaDisplay;
            document.getElementById('fechaSolicitudHidden').value = fechaHidden;

            document.getElementById('departamento').value = 'Administracion';
            // Configurar Solicitante (simulado)
            document.getElementById('solicitante').value =
                'Saul Falcon Perez'; // Simulación de usuario logeado

            // Navegación
            document.querySelectorAll('.nav-link-viajes').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelectorAll('.nav-link-viajes').forEach(item => {
                        item.classList.remove('active');
                    });
                    this.classList.add('active');
                });
            });

            // Lógica para el campo "Especifique Destino"
            const destinoSelector = document.getElementById('destinoPredefinido');
            const destinoEspecifico = document.getElementById('destinoEspecifico');

            destinoSelector.addEventListener('change', function() {
                // Si el valor seleccionado es "Otro", el campo "Especifique Destino" es obligatorio (required)
                if (this.value === 'Otro') {
                    destinoEspecifico.required = true;
                    // El campo ya no se deshabilita, pero el required sí se activa.
                } else {
                    // Si se selecciona un destino predefinido, el campo no es requerido y se limpia (opcionalmente)
                    // destinoEspecifico.value = ''; // Se mantiene el texto en el campo si es un destino predefinido.
                    destinoEspecifico.required = false;
                }
            });
            // Al inicio, si el selector no tiene "Otro", no es requerido
            if (destinoSelector.value !== 'Otro') {
                destinoEspecifico.required = false;
            }


            // Inicializar Flatpickr
            inicializarFlatpickr();

            // Envío del formulario
            document.getElementById('formViaje').addEventListener('submit', function(e) {
                e.preventDefault();
                enviarSolicitud();
            });

            // Generar código inicial
            generarCodigoViaje(false);

            // Inicializar el label de tipo de unidad
            actualizarLabelTipoUnidad();
            // 🔑 NUEVO: Inicializar el estado del botón de Reunión 🔑
            actualizarBotonReunionConvoy();
            // 🚀 Inicializar botón Evaluación
            actualizarBotonEvaluacion();
        });

        // ====================================================================
        // Funciones de Autocompletado y Docs del Conductor
        // ====================================================================
        function inicializarAutocompleteConductor(unidadNumero) {
            const input = document.getElementById(`conductor-${unidadNumero}`);
            const listContainer = document.getElementById(`autocomplete-list-${unidadNumero}`);
            let currentFocus = -1;

            const updateList = () => {
                const val = input.value;
                listContainer.innerHTML = '';
                currentFocus = -1;

                if (!val) {
                    // Limpiar solo si se borra el contenido
                    if (input.dataset.conductorSeleccionado !== undefined) {
                        actualizarDatosConductor(unidadNumero, '');
                    }
                    delete input.dataset.conductorSeleccionado;
                    return false;
                }

                const filtered = conductores.filter(c => c.toUpperCase().includes(val.toUpperCase()));

                if (filtered.length === 0) {
                    actualizarDatosConductor(unidadNumero, '');
                    listContainer.innerHTML =
                        `<div class="autocomplete-item" style="color: var(--accent-red); font-weight: normal; cursor: default;">No encontrado</div>`;
                } else {
                    filtered.forEach((c, index) => {
                        const item = document.createElement('div');
                        item.classList.add('autocomplete-item');
                        item.innerHTML = c;

                        // 🔑 CORRECCIÓN CRÍTICA PARA TOUCHPAD/CLIC 🔑
                        // Usamos 'mousedown' en lugar de 'click' porque 'mousedown' ocurre antes de que el input pierda el foco (blur).
                        // e.preventDefault() evita que el input pierda el foco inmediatamente.
                        item.addEventListener('mousedown', function(e) {
                            e.preventDefault(); // Evita que el input pierda el foco
                            input.value = c;
                            input.dataset.conductorSeleccionado = c; // Marcar como seleccionado
                            listContainer.innerHTML = '';
                            actualizarDatosConductor(unidadNumero, c);
                            // 🔑 NUEVO: Verificar el estado del botón Reunión al seleccionar un conductor 🔑
                            actualizarBotonReunionConvoy();
                            actualizarBotonEvaluacion();
                        });

                        listContainer.appendChild(item);
                    });
                }
            }

            const navigateList = (direction) => {
                const items = listContainer.querySelectorAll('.autocomplete-item');

                if (items.length === 0) return;

                if (currentFocus !== -1) {
                    items[currentFocus].classList.remove('selected');
                }

                currentFocus += direction;

                if (currentFocus >= items.length) {
                    currentFocus = 0;
                }
                if (currentFocus < 0) {
                    currentFocus = items.length - 1;
                }

                items[currentFocus].classList.add('selected');
                items[currentFocus].scrollIntoView({
                    block: 'nearest'
                });
            }

            input.addEventListener('input', updateList);
            input.addEventListener('focus', updateList);

            input.addEventListener('keydown', function(e) {
                if (e.key === "ArrowDown") {
                    e.preventDefault();
                    navigateList(1);
                } else if (e.key === "ArrowUp") {
                    e.preventDefault();
                    navigateList(-1);
                } else if (e.key === "Enter" || e.key === "Tab") {
                    if (currentFocus > -1) {
                        e.preventDefault();
                        // Simulamos mousedown para mantener consistencia
                        const selectedItem = listContainer.querySelectorAll('.autocomplete-item')[
                            currentFocus];
                        const event = new MouseEvent('mousedown', {
                            bubbles: true,
                            cancelable: true,
                            view: window
                        });
                        selectedItem.dispatchEvent(event);
                    } else if (e.key === "Enter") {
                        const exactMatch = conductores.find(c => c.toUpperCase() === input.value.toUpperCase());
                        if (exactMatch) {
                            input.value = exactMatch;
                            input.dataset.conductorSeleccionado = exactMatch;
                            actualizarDatosConductor(unidadNumero, exactMatch);
                            listContainer.innerHTML = '';
                            // 🔑 NUEVO: Verificar el estado del botón Reunión al presionar Enter 🔑
                            actualizarBotonReunionConvoy();
                            actualizarBotonEvaluacion();
                        } else {
                            actualizarDatosConductor(unidadNumero, '');
                            listContainer.innerHTML = '';
                        }
                    }
                }
            });

            input.addEventListener('blur', function() {
                // Pequeño timeout para permitir que otros eventos se procesen si es necesario,
                // pero la lógica principal ahora está en mousedown.
                setTimeout(() => {
                    listContainer.innerHTML = '';
                    const exactMatch = conductores.find(c => c.toUpperCase() === input.value
                        .toUpperCase());
                    // Si no hay un match exacto con el valor, o si no se ha seleccionado previamente, limpiar la vigencia
                    if (!exactMatch || input.dataset.conductorSeleccionado !== input.value) {
                        actualizarDatosConductor(unidadNumero, '');
                        // 🔑 NUEVO: Verificar el estado del botón Reunión al perder el foco 🔑
                        actualizarBotonReunionConvoy();
                        actualizarBotonEvaluacion();
                    }
                    // No borramos dataset inmediatamente aqui para permitir re-seleccion si es el mismo texto
                }, 150);
            });
        }

        function actualizarDatosConductor(unidadNumero, nombreConductor) {
            const inputVigenciaLic = document.getElementById(`vigencia-lic-${unidadNumero}`);
            const inputVigenciaMan = document.getElementById(`vigencia-man-${unidadNumero}`);

            const data = datosConductores[nombreConductor];

            if (data) {
                // Vigencia Licencia (sigue igual)
                inputVigenciaLic.value = data.vigencia;

                // Llenar Vigencia Man. Def. con fecha predefinida y hacerlo de solo lectura
                inputVigenciaMan.value = data.manDefVigencia; // Usar la fecha del objeto de datos
                inputVigenciaMan.readOnly = true; // Forzar a solo lectura para evitar modificación
            } else {
                inputVigenciaLic.value = '';
                inputVigenciaMan.value = ''; // Limpiar también la fecha de Man. Def. si se borra el conductor
                inputVigenciaMan.readOnly = true; // Deshabilitar si no hay conductor seleccionado de la lista
            }
        }

        // ====================================================================
        // Funciones de Cálculo de Horas
        // ====================================================================
        function parseTimeForCalculation(timeStr) {
            if (!timeStr) return null;

            // Usa una expresión regular para manejar H:i o h:i K (12h AM/PM)
            const regex12hr = /(\d{1,2}):(\d{2})\s*(AM|PM)/i;
            const match12hr = timeStr.match(regex12hr);

            if (match12hr) {
                let [, h, m, ampm] = match12hr;
                h = parseInt(h);
                m = parseInt(m);

                if (ampm.toUpperCase() === 'PM' && h !== 12) {
                    h += 12;
                } else if (ampm.toUpperCase() === 'AM' && h === 12) {
                    h = 0; // Medianoche
                }
                return h * 60 + m; // Total de minutos en 24h
            } else {
                // Intenta formato 24h simple si no coincide con AM/PM
                const [h, m] = timeStr.split(':').map(Number);
                if (!isNaN(h) && !isNaN(m)) {
                    return h * 60 + m;
                }
            }

            return null; // Fallback
        }

        function calcularHorasDormidas(unidadNumero) {
            const horaDormirInput = document.getElementById(`dormir-${unidadNumero}`);
            const horaLevantarInput = document.getElementById(`levantar-${unidadNumero}`);
            const totalDormidasInput = document.getElementById(`total-hrs-dormidas-${unidadNumero}`);

            const dormir = horaDormirInput ? horaDormirInput.value : null;
            const levantar = horaLevantarInput ? horaLevantarInput.value : null;

            const totalMinutosDormir = parseTimeForCalculation(dormir);
            let totalMinutosLevantar = parseTimeForCalculation(levantar);

            if (totalMinutosDormir !== null && totalMinutosLevantar !== null) {
                // Si la hora de levantar es antes o igual a la de dormir, asume que es al día siguiente (+24 horas)
                if (totalMinutosLevantar <= totalMinutosDormir) {
                    totalMinutosLevantar += 24 * 60;
                }

                const duracionMinutos = totalMinutosLevantar - totalMinutosDormir;
                const duracionHoras = (duracionMinutos / 60).toFixed(1);

                totalDormidasInput.value = duracionHoras;
            } else {
                totalDormidasInput.value = '0.0';
            }
        }

        function calcularTotalHoras(unidadNumero) {
            // Esta función ha sido modificada para manejar la conversión de texto a número si es posible,
            // pero ya no se usa onchange, por lo que este cálculo es solo de ejemplo si se activara manualmente.
            const hrsDespiertoInput = document.getElementById(`horas-despierto-${unidadNumero}`);
            const hrsViajeInput = document.getElementById(`horas-viaje-${unidadNumero}`);
            const totalHrsInput = document.getElementById(`total-hrs-finalizar-${unidadNumero}`);

            // Intentar parsear los valores de texto a flotante
            const hrsDespierto = parseFloat(hrsDespiertoInput ? hrsDespiertoInput.value : 0) || 0;
            const hrsViaje = parseFloat(hrsViajeInput ? hrsViajeInput.value : 0) || 0;

            const totalHoras = (hrsDespierto + hrsViaje).toFixed(1);
            if (totalHrsInput) {
                totalHrsInput.value = totalHoras;

                // Lógica de advertencia (se mantiene)
                if (totalHoras > 14) {
                    totalHrsInput.style.backgroundColor = 'var(--accent-red)';
                    totalHrsInput.style.color = 'var(--white)';
                    Swal.fire({
                        title: '¡Advertencia de Horas!',
                        text: `La unidad ${unidadNumero} acumulará ${totalHoras} horas totales. Esto excede el límite recomendado de 14 horas.`,
                        icon: 'warning',
                        confirmButtonColor: 'var(--primary-blue)'
                    });
                } else {
                    totalHrsInput.style.backgroundColor = 'var(--light-gray)';
                    totalHrsInput.style.color = 'var(--dark-gray)';
                }
            }
        }

        // ====================================================================
        // Funciones de Gestión del Modal Principal
        // ====================================================================
        function gestionarModalFormulario(abrir) {
            const modal = document.getElementById('modalFormulario');
            if (abrir) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
                if (contadorUnidades === 0) {
                    agregarUnidad();
                }
            } else {
                Swal.fire({
                    title: '¿Desea cerrar el formulario?',
                    text: 'Se perderán los datos no guardados de la solicitud actual.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cerrar',
                    cancelButtonText: 'Continuar editando',
                    confirmButtonColor: 'var(--accent-red)',
                    cancelButtonColor: 'var(--primary-blue)',
                }).then((result) => {
                    if (result.isConfirmed) {
                        modal.classList.remove('active');
                        document.body.style.overflow = 'auto';
                        limpiarFormulario();
                    }
                });
            }
        }

        function limpiarFormulario() {
            document.getElementById('formViaje').reset();
            document.getElementById('cuerpoTablaUnidades').innerHTML = '';
            contadorUnidades = 0;
            actualizarContadorUnidades();
            actualizarLabelTipoUnidad(); // Limpiar label
            actualizarBotonAgregar();
            generarCodigoViaje(false);

            for (const key in datosInspeccion) {
                delete datosInspeccion[key];
            }
            // 🔑 NUEVO: Limpiar datos de convoy 🔑
            reunionPreConvoyGuardada = false;
            datosReunionConvoy = {};
            actualizarBotonReunionConvoy();

            // Limpiar Paradas
            document.getElementById('listaParadas').innerHTML = '';
            contadorParadas = 0;
            toggleSeccionParadas(false);

            // 🚀 Limpiar Evaluación
            evaluacionRiesgoGuardada = false;
            puntajeRiesgoTotal = 0;
            const btnEval = document.getElementById('btnEvaluacionRiesgo');
            btnEval.classList.remove('evaluacion-completada');
            btnEval.innerHTML = '<i class="fas fa-list-check"></i> Realizar Evaluación del Viaje (Requerido)';
            document.getElementById('formEvaluacionRiesgo').reset();
            actualizarBotonEvaluacion();
        }

        document.getElementById('modalFormulario').addEventListener('click', function(e) {
            if (e.target === this) {
                gestionarModalFormulario(false);
            }
        });

        // ====================================================================
        // 🚀 FUNCIONES MODIFICADAS PARA GESTIÓN DE PARADAS (4 A LO LARGO) 🚀
        // ====================================================================
        function toggleSeccionParadas(mostrar) {
            const contenedor = document.getElementById('contenedorParadas');
            const lista = document.getElementById('listaParadas');

            if (mostrar) {
                contenedor.classList.remove('hidden');
                // Si no hay paradas, agregamos una por defecto
                if (lista.children.length === 0) {
                    agregarParada();
                }
            } else {
                contenedor.classList.add('hidden');
                // Limpiar la lista si se oculta
                lista.innerHTML = '';
                contadorParadas = 0;
            }
        }

        function agregarParada() {
            // 🚀 NUEVA VALIDACIÓN: Verificar límite de paradas 🚀
            if (contadorParadas >= MAX_PARADAS) {
                Swal.fire({
                    title: 'Límite de Paradas Alcanzado',
                    text: `Solo se permiten hasta ${MAX_PARADAS} paradas por viaje.`,
                    icon: 'warning',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                return; // Detiene la función, no agrega nada más
            }

            contadorParadas++;
            const lista = document.getElementById('listaParadas');

            // Obtener el número de paradas actual
            const paradasActuales = lista.querySelectorAll('.parada-item').length;

            // Verificar si necesitamos crear una nueva fila (cada 4 paradas)
            if (paradasActuales % 4 === 0) {
                const nuevaFila = document.createElement('div');
                nuevaFila.classList.add('paradas-fila');
                nuevaFila.id = `paradas-fila-${Math.ceil((paradasActuales + 1) / 4)}`;
                lista.appendChild(nuevaFila);
            }

            // Obtener la última fila
            const filas = lista.querySelectorAll('.paradas-fila');
            const ultimaFila = filas[filas.length - 1];

            // Crear la nueva parada
            const paradaDiv = document.createElement('div');
            paradaDiv.classList.add('parada-item');
            paradaDiv.id = `parada-${contadorParadas}`;

            paradaDiv.innerHTML = `
                <div class="parada-header">
                    <span class="parada-titulo">Parada ${contadorParadas}</span>
                    <button type="button" class="btn-remove-parada-compact" onclick="eliminarParada(${contadorParadas})" title="Eliminar Parada">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="parada-body-compact">
                    <div>
                        <label class="parada-label-small">Propósito</label>
                        <select name="paradas[${contadorParadas}][motivo]" class="form-control form-control-sm" required>
                            <option value="">Seleccione...</option>
                            <option value="Carga de Combustible">Combustible</option>
                            <option value="Alimentos">Alimentos</option>
                            <option value="Descanso">Descanso</option>
                            <option value="Sanitario">Sanitario</option>
                            <option value="Pernocta">Pernocta</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="parada-label-small">Ubicación</label>
                        <input type="text" name="paradas[${contadorParadas}][lugar]" class="form-control form-control-sm" placeholder="Lugar / Ciudad" required>
                    </div>
                </div>
            `;

            ultimaFila.appendChild(paradaDiv);

            // Si después de agregar hay más de 4 elementos en la fila, reorganizar
            reorganizarParadas();
        }

        function eliminarParada(id) {
            const elemento = document.getElementById(`parada-${id}`);
            if (elemento) {
                elemento.remove();
                contadorParadas--;

                // Reorganizar los números y la estructura
                reorganizarParadas();

                // Si eliminamos todas, ocultar la sección
                const lista = document.getElementById('listaParadas');
                if (lista.children.length === 0) {
                    document.querySelector('input[name="tiene_paradas"][value="no"]').checked = true;
                    toggleSeccionParadas(false);
                }
            }
        }

        function reorganizarParadas() {
            const lista = document.getElementById('listaParadas');
            const todasParadas = lista.querySelectorAll('.parada-item');

            // Limpiar todas las filas existentes
            const filas = lista.querySelectorAll('.paradas-fila');
            filas.forEach(fila => fila.remove());

            if (todasParadas.length === 0) {
                contadorParadas = 0;
                return;
            }

            // Recrear las filas con las paradas existentes
            let contadorGlobal = 0;

            todasParadas.forEach((parada, index) => {
                contadorGlobal++;

                // Crear nueva fila cada 4 paradas
                if (index % 4 === 0) {
                    const nuevaFila = document.createElement('div');
                    nuevaFila.classList.add('paradas-fila');
                    nuevaFila.id = `paradas-fila-${Math.floor(index / 4) + 1}`;
                    lista.appendChild(nuevaFila);
                }

                // Obtener la última fila
                const filasActuales = lista.querySelectorAll('.paradas-fila');
                const ultimaFila = filasActuales[filasActuales.length - 1];

                // Actualizar número de parada (título centrado)
                const paradaTitulo = parada.querySelector('.parada-titulo');
                if (paradaTitulo) {
                    paradaTitulo.textContent = `Parada ${contadorGlobal}`;
                }

                // Actualizar atributos del select e input
                const select = parada.querySelector('select');
                const input = parada.querySelector('input[type="text"]');

                if (select) {
                    select.setAttribute('name', `paradas[${contadorGlobal}][motivo]`);
                }
                if (input) {
                    input.setAttribute('name', `paradas[${contadorGlobal}][lugar]`);
                }

                // Actualizar el botón eliminar
                const btnEliminar = parada.querySelector('.btn-remove-parada-compact');
                if (btnEliminar) {
                    btnEliminar.setAttribute('onclick', `eliminarParada(${contadorGlobal})`);
                    btnEliminar.setAttribute('title', `Eliminar Parada ${contadorGlobal}`);
                }

                // Actualizar el ID de la parada
                parada.id = `parada-${contadorGlobal}`;

                // Mover la parada a la última fila
                ultimaFila.appendChild(parada);
            });

            // Actualizar el contador global
            contadorParadas = contadorGlobal;
        }

        // ====================================================================
        // Funciones de Gestión del Modal de Inspección
        // ====================================================================
        let unidadEnInspeccion = null;

        function gestionarModalInspeccion(abrir, unidadNumero = null) {
            const modal = document.getElementById('modalInspeccion');
            const formGrid = document.getElementById('inspeccionGrid');

            if (abrir && unidadNumero !== null) {
                unidadEnInspeccion = unidadNumero;

                const selectVehiculo = document.querySelector(`#unidad-${unidadNumero} .unidad-vehiculo`);
                const nombreVehiculo = selectVehiculo ? selectVehiculo.value : 'Unidad ' + unidadNumero;
                document.getElementById('inspeccionUnidadNombre').textContent = nombreVehiculo;
                document.getElementById('inspeccionUnidadIndex').value = unidadNumero;

                formGrid.innerHTML = '';
                itemsInspeccion.forEach(item => {
                    const savedValue = datosInspeccion[unidadNumero] && datosInspeccion[unidadNumero][item.name] ||
                        '';

                    const itemDiv = document.createElement('div');
                    itemDiv.classList.add('inspeccion-item');
                    itemDiv.innerHTML = `
        <div class="inspeccion-item-label">
            <i class="${item.icon}"></i>
            <span>${item.label}</span>
        </div>
        <div class="inspeccion-radio-group">
            <label class="si">
                <input type="radio" name="inspeccion_${item.name}" value="si" ${savedValue === 'si' ? 'checked' : ''} required>
                Sí
            </label>
            <label class="no">
                <input type="radio" name="inspeccion_${item.name}" value="no" ${savedValue === 'no' ? 'checked' : ''}>
                No
            </label>
        </div>`;
                    formGrid.appendChild(itemDiv);
                });

                modal.classList.add('active');
                document.body.style.overflow = 'hidden';

            } else {
                modal.classList.remove('active');
                document.body.style.overflow = 'auto';
                unidadEnInspeccion = null;
            }
        }

        function guardarInspeccion() {
            const form = document.getElementById('formInspeccion');
            const unidadNumero = parseInt(document.getElementById('inspeccionUnidadIndex').value);

            let formValido = true;
            itemsInspeccion.forEach(item => {
                const checked = form.querySelector(`input[name="inspeccion_${item.name}"]:checked`);
                if (!checked) {
                    formValido = false;
                }
            });

            if (!formValido) {
                Swal.fire({
                    title: 'Inspección Incompleta',
                    text: 'Debe responder sí o no a todos los elementos de la inspección.',
                    icon: 'warning',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                return;
            }

            const data = {};
            let todoAprobado = true;

            itemsInspeccion.forEach(item => {
                const value = form.querySelector(`input[name="inspeccion_${item.name}"]:checked`).value;
                data[item.name] = value;
                if (value === 'no') {
                    todoAprobado = false;
                }
            });

            datosInspeccion[unidadNumero] = data;

            const btnInspeccion = document.getElementById(`btn-inspeccion-${unidadNumero}`);
            if (btnInspeccion) {
                if (todoAprobado) {
                    btnInspeccion.classList.add('btn-submit', 'btn-inspeccion-aprobado');
                    btnInspeccion.classList.remove('btn-inspeccion');
                    // Ícono de verificación
                    btnInspeccion.innerHTML = '<i class="fas fa-check-circle"></i>';
                    btnInspeccion.dataset.aprobado = 'true';
                    btnInspeccion.title = 'Inspección Aprobada';
                } else {
                    btnInspeccion.classList.add('btn-inspeccion');
                    btnInspeccion.classList.remove('btn-submit', 'btn-inspeccion-aprobado');
                    // Ícono de advertencia para no aprobado
                    btnInspeccion.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
                    btnInspeccion.dataset.aprobado = 'false';
                    btnInspeccion.title = 'Revisar Inspección';
                }
            }

            Swal.fire({
                title: 'Inspección Guardada',
                text: `La inspección para la Unidad ${unidadNumero} ha sido registrada.`,
                icon: 'success',
                confirmButtonColor: 'var(--primary-blue)'
            }).then(() => {
                gestionarModalInspeccion(false);
                // 🔑 NUEVO: Verificar estado del botón Reunión Convoy después de guardar inspección 🔑
                actualizarBotonReunionConvoy();
                actualizarBotonEvaluacion();
            });
        }

        // ====================================================================
        // 🚀 FUNCIONES DE EVALUACIÓN DE RIESGO 🚀
        // ====================================================================

        function actualizarBotonEvaluacion() {
            const btn = document.getElementById('btnEvaluacionRiesgo');
            if (contadorUnidades > 0) {
                btn.disabled = false;
            } else {
                btn.disabled = true;
            }
            actualizarBotonEnviarSolicitud();
        }

        function gestionarModalEvaluacion(abrir) {
            const modal = document.getElementById('modalEvaluacion');
            if (abrir) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            } else {
                modal.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        }

        function guardarEvaluacion() {
            const form = document.getElementById('formEvaluacionRiesgo');

            // Validar que todos los radios estén seleccionados
            // Hay 10 categorías
            const categorias = ['ev_manejo', 'ev_horas', 'ev_vehiculos', 'ev_comunicacion', 'ev_clima', 'ev_iluminacion',
                'ev_carretera', 'ev_otras', 'ev_animales', 'ev_seguridad'
            ];
            let completo = true;
            let totalPuntos = 0;

            for (let cat of categorias) {
                const seleccionado = form.querySelector(`input[name="${cat}"]:checked`);
                if (!seleccionado) {
                    completo = false;
                    break;
                }
                totalPuntos += parseInt(seleccionado.value);
            }

            if (!completo) {
                Swal.fire({
                    title: 'Evaluación Incompleta',
                    text: 'Por favor seleccione una opción para cada categoría.',
                    icon: 'warning',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                return;
            }

            // Guardar
            puntajeRiesgoTotal = totalPuntos;
            evaluacionRiesgoGuardada = true;

            const btn = document.getElementById('btnEvaluacionRiesgo');
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Evaluación Completada';
            btn.classList.add('evaluacion-completada');

            gestionarModalEvaluacion(false);
            actualizarBotonEnviarSolicitud();

            Swal.fire({
                title: 'Evaluación Guardada',
                text: 'La evaluación de riesgos ha sido registrada correctamente.',
                icon: 'success',
                confirmButtonColor: 'var(--primary-blue)'
            });
        }

        // ====================================================================
        // 🔑 NUEVAS FUNCIONES DE GESTIÓN DE REUNIÓN PRE-CONVOY 🔑
        // ====================================================================

        /**
         * 🔑 NUEVA FUNCIÓN CLAVE 🔑: Habilita/deshabilita el botón de Reunión Pre-Convoy.
         * Se activa solo si contadorUnidades >= 2 Y la tabla de unidades está completa.
         */
        function actualizarBotonReunionConvoy() {
            const btn = document.getElementById('btnReunionPreConvoy');
            if (!btn) return;

            // 1. Verificar si hay al menos dos unidades
            if (contadorUnidades < 2) {
                btn.disabled = true;
                btn.classList.remove('btn-submit', 'btn-secondary-convoy-completed');
                btn.classList.add('btn-secondary-convoy');
                btn.title = "Se requiere un mínimo de 2 unidades para un Convoy";
                btn.innerHTML = '<i class="fas fa-handshake"></i> Reunión Pre-convoy';
                return;
            }

            // 2. Verificar si todos los campos requeridos de las unidades están llenos.
            let unidadesCompletas = true;
            for (let i = 1; i <= contadorUnidades; i++) {
                const fila = document.getElementById(`unidad-${i}`);
                if (!fila) {
                    unidadesCompletas = false;
                    break;
                }

                // Lista de selectores de inputs requeridos en la fila de la unidad
                const requiredSelectors = [
                    `.unidad-conductor`,
                    `#vigencia-lic-${i}`,
                    `#vigencia-man-${i}`, // Ahora es readonly, pero contiene datos esenciales
                    `.unidad-alcoholimetria`,
                    `#dormir-${i}`,
                    `#levantar-${i}`,
                    `.unidad-vehiculo`
                ];

                // Checkear inputs principales
                for (const selector of requiredSelectors) {
                    const input = fila.querySelector(selector);
                    if (input && input.value.trim() === "") {
                        unidadesCompletas = false;
                        break;
                    }
                }
                if (!unidadesCompletas) break;

                // Checkear pasajeros (al menos 1 pasajero por unidad es requerido por la plantilla)
                const pasajeros = fila.querySelectorAll('.pasajero-input-group input');
                for (const input of pasajeros) {
                    if (input.value.trim() === "") {
                        unidadesCompletas = false;
                        break;
                    }
                }
                if (!unidadesCompletas) break;
            }

            if (unidadesCompletas) {
                btn.disabled = false;
                btn.title = "Realizar la reunión antes de enviar la solicitud.";

                if (reunionPreConvoyGuardada) {
                    // 🔑 CORRECCIÓN: Cambiar a estilo de botón completado (blanco sin borde, texto e icono verde)
                    btn.classList.add('btn-secondary-convoy-completed');
                    btn.classList.remove('btn-secondary-convoy', 'btn-submit');
                    btn.innerHTML = '<i class="fas fa-check-circle"></i> Reunión Confirmada';
                } else {
                    btn.classList.add('btn-secondary-convoy');
                    btn.classList.remove('btn-submit', 'btn-secondary-convoy-completed');
                    btn.innerHTML = '<i class="fas fa-handshake"></i> Reunión Pre-convoy';
                }
            } else {
                btn.disabled = true;
                btn.classList.remove('btn-submit', 'btn-secondary-convoy-completed');
                btn.classList.add('btn-secondary-convoy');
                btn.innerHTML = '<i class="fas fa-handshake"></i> Reunión Pre-convoy';
                btn.title = "Complete todos los campos de Conductor y Unidad antes de realizar la Reunión.";
                // Si la tabla no está completa, invalidamos la reunión previa
                reunionPreConvoyGuardada = false;
            }

            // También se debe actualizar el botón de Enviar Solicitud
            actualizarBotonEnviarSolicitud();
        }

        /**
         * Habilita/deshabilita el botón de Enviar Solicitud.
         * Se activa solo si el viaje es de Unidad Única O si el viaje es Convoy Y la Reunión ha sido confirmada.
         */
        function actualizarBotonEnviarSolicitud() {
            const btnEnviar = document.getElementById('btnEnviarSolicitud');
            if (!btnEnviar) return;

            // 🚀 Requisito: Evaluación de Riesgo completada
            if (!evaluacionRiesgoGuardada) {
                btnEnviar.disabled = true;
                btnEnviar.title = "Debe realizar la Evaluación del Viaje.";
                return;
            }

            if (contadorUnidades <= 1) {
                // Unidad única: siempre disponible si el formulario principal está lleno (se valida en enviarSolicitud)
                btnEnviar.disabled = false;
                btnEnviar.title = "Enviar Solicitud";
            } else {
                // Convoy: Solo disponible si la reunión ha sido guardada
                if (reunionPreConvoyGuardada) {
                    btnEnviar.disabled = false;
                    btnEnviar.title = "Enviar Solicitud";
                } else {
                    btnEnviar.disabled = true;
                    btnEnviar.title = "Debe completar y confirmar la Reunión Pre-convoy.";
                }
            }
        }

        /**
         * Abre o cierra el modal de Reunión Pre-Convoy.
         */
        function gestionarModalPreConvoy(abrir) {
            const modal = document.getElementById('modalPreConvoy');
            const liderSelect = document.getElementById('liderConvoy');
            const checklistContainer = document.getElementById('checklistPreConvoy');

            if (abrir) {
                // 1. Generar la lista de conductores para el selector de Líder
                const conductoresDisponibles = obtenerConductoresUnidades();
                liderSelect.innerHTML = '<option value="">Seleccione un conductor como Líder</option>';

                conductoresDisponibles.forEach(c => {
                    const option = document.createElement('option');
                    option.value = c;
                    option.textContent = c;
                    // Preseleccionar el valor guardado
                    if (datosReunionConvoy.lider_convoy === c) {
                        option.selected = true;
                    }
                    liderSelect.appendChild(option);
                });

                // 2. Generar el Checklist de Seguridad
                checklistContainer.innerHTML = '';
                checklistPreConvoy.forEach(item => {
                    // Valor guardado o cadena vacía si no existe
                    const savedValue = datosReunionConvoy[item.name] || '';

                    const itemDiv = document.createElement('div');
                    itemDiv.classList.add('inspeccion-item');
                    itemDiv.innerHTML = `
                        <div class="inspeccion-item-label">
                            <i class="${item.icon}"></i>
                            <span>${item.label}</span>
                        </div>
                        <div class="inspeccion-radio-group">
                            <label class="si">
                                <input type="radio" name="checklist_${item.name}" value="si" ${savedValue === 'si' ? 'checked' : ''} required>
                                Sí
                            </label>
                            <label class="no">
                                <input type="radio" name="checklist_${item.name}" value="no" ${savedValue === 'no' ? 'checked' : ''}>
                                No
                            </label>
                        </div>
                    `;
                    checklistContainer.appendChild(itemDiv);
                });

                modal.classList.add('active');
                document.body.style.overflow = 'hidden';

            } else {
                modal.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        }

        /**
         * Obtiene la lista de nombres de conductores actualmente en la tabla.
         */
        function obtenerConductoresUnidades() {
            const conductoresList = [];
            for (let i = 1; i <= contadorUnidades; i++) {
                const inputConductor = document.getElementById(`conductor-${i}`);
                if (inputConductor && inputConductor.value.trim() !== '') {
                    conductoresList.push(inputConductor.value.trim());
                }
            }
            return conductoresList;
        }

        /**
         * Guarda los datos del formulario de Reunión Pre-Convoy.
         */
        function guardarPreConvoy() {
            const form = document.getElementById('formPreConvoy');
            const liderSelect = document.getElementById('liderConvoy');

            // 1. Validar Líder
            if (liderSelect.value.trim() === '') {
                Swal.fire({
                    title: 'Líder No Seleccionado',
                    text: 'Debe seleccionar un conductor como Líder de Convoy.',
                    icon: 'warning',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                return;
            }

            // 2. Validar Checklist
            let checklistCompleto = true;
            const data = {
                lider_convoy: liderSelect.value
            };

            checklistPreConvoy.forEach(item => {
                const checked = form.querySelector(`input[name="checklist_${item.name}"]:checked`);
                if (!checked) {
                    checklistCompleto = false;
                } else {
                    data[item.name] = checked.value;
                }
            });

            if (!checklistCompleto) {
                Swal.fire({
                    title: 'Checklist Incompleto',
                    text: 'Debe responder sí o no a todos los elementos del checklist de seguridad.',
                    icon: 'warning',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                return;
            }

            // 3. Verificar NOs en el Checklist (Advertencia o Rechazo)
            let noAprobado = false;
            let noItems = [];
            for (const key in data) {
                if (key !== 'lider_convoy' && data[key] === 'no') {
                    noAprobado = true;
                    // Buscar el label original
                    const item = checklistPreConvoy.find(i => i.name === key);
                    noItems.push(item ? item.label : key);
                }
            }

            if (noAprobado) {
                Swal.fire({
                    title: '¡Advertencia de Seguridad!',
                    html: `Se encontraron puntos de seguridad no confirmados (NO) en el checklist:<br><ul>${noItems.map(i => `<li>${i}</li>`).join('')}</ul><br>
                            Si continúa, el viaje será marcado con **ALTO RIESGO** y requerirá una aprobación superior.`,
                    icon: 'error', // Usamos error/warning para mayor impacto
                    showCancelButton: true,
                    confirmButtonText: 'Guardar y Continuar (Alto Riesgo)',
                    cancelButtonText: 'Corregir Checklist (Recomendado)',
                    confirmButtonColor: 'var(--accent-red)',
                    cancelButtonColor: 'var(--primary-blue)',
                }).then((result) => {
                    if (result.isConfirmed) {
                        finalizarGuardadoPreConvoy(data, false); // No aprobado = false
                    }
                });
            } else {
                // 4. Guardado exitoso (todo SI)
                finalizarGuardadoPreConvoy(data, true); // Aprobado = true
            }
        }

        function finalizarGuardadoPreConvoy(data, todoAprobado) {
            datosReunionConvoy = data;
            reunionPreConvoyGuardada = true;
            gestionarModalPreConvoy(false);
            actualizarBotonReunionConvoy();

            let title = 'Reunión Confirmada';
            let text = `La Reunión Pre-convoy ha sido registrada. La solicitud está lista para ser enviada.`;
            let icon = 'success';

            if (!todoAprobado) {
                title = 'Reunión Guardada con Advertencias';
                text = `La Reunión Pre-convoy ha sido registrada con **NOs**. El viaje será marcado como **ALTO RIESGO**.`;
                icon = 'warning';
            }

            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                confirmButtonColor: 'var(--primary-blue)'
            });
        }

        // ====================================================================
        // Fin de Funciones de Gestión de Reunión Pre-Convoy
        // ====================================================================


        // ====================================================================
        // Funciones de Pasajeros
        // ====================================================================
        function agregarPasajero(unidadNumero) {
            const container = document.getElementById(`pasajeros-unidad-${unidadNumero}`);
            const currentPasajeros = container.querySelectorAll('.pasajero-input-group').length;

            if (currentPasajeros >= MAX_PASAJEROS) {
                Swal.fire('Límite de Pasajeros',
                    `Solo se permiten ${MAX_PASAJEROS} pasajeros (adicionales al conductor) por unidad.`, 'warning');
                return;
            }

            const nuevoPasajeroIndex = currentPasajeros + 1;
            const passengerFieldName = `unidad[${unidadNumero}][pasajeros][p${nuevoPasajeroIndex}]`;

            const newPasajero = document.createElement('div');
            newPasajero.classList.add('pasajero-input-group');
            newPasajero.setAttribute('data-p-index', nuevoPasajeroIndex);
            newPasajero.innerHTML = `
        <input type="text" class="table-input" name="${passengerFieldName}" placeholder="Pasajero ${nuevoPasajeroIndex} (Nombre completo)" required>
        <button type="button" class="btn-remove-pasajero" onclick="eliminarPasajero(${unidadNumero}, ${nuevoPasajeroIndex})">
            <i class="fas fa-minus"></i>
        </button>
    `;

            container.appendChild(newPasajero);

            if (currentPasajeros + 1 >= MAX_PASAJEROS) {
                document.getElementById(`btn-add-pasajero-${unidadNumero}`).disabled = true;
            }
            // 🔑 NUEVO: Verificar el estado del botón Reunión al agregar un pasajero 🔑
            actualizarBotonReunionConvoy();
        }

        function eliminarPasajero(unidadNumero, pasajeroIndex) {
            const container = document.getElementById(`pasajeros-unidad-${unidadNumero}`);
            const inputGroup = container.querySelector(`.pasajero-input-group[data-p-index="${pasajeroIndex}"]`);

            if (inputGroup) {
                inputGroup.remove();
            }

            const remainingPasajeros = container.querySelectorAll('.pasajero-input-group');
            remainingPasajeros.forEach((group, index) => {
                const newIndex = index + 1;
                group.setAttribute('data-p-index', newIndex);

                const input = group.querySelector('input');
                input.setAttribute('name', `unidad[${unidadNumero}][pasajeros][p${newIndex}]`);
                input.setAttribute('placeholder', `Pasajero ${newIndex} (Nombre completo)`);

                const removeBtn = group.querySelector('.btn-remove-pasajero');
                if (removeBtn) {
                    removeBtn.setAttribute('onclick', `eliminarPasajero(${unidadNumero}, ${newIndex})`);
                }
            });

            if (remainingPasajeros.length < MAX_PASAJEROS) {
                document.getElementById(`btn-add-pasajero-${unidadNumero}`).disabled = false;
            }
            // 🔑 NUEVO: Verificar el estado del botón Reunión al eliminar un pasajero 🔑
            actualizarBotonReunionConvoy();
        }

        // ====================================================================
        // Funciones de Unidades y Auxiliares
        // ====================================================================
        function generarCodigoViaje(incrementar = false) {
            if (incrementar) {
                codigoViaje++;
            }
            // El código siempre es completo: se asegura que tenga 3 dígitos
            const codigo = `N°:GV-${String(codigoViaje).padStart(3, '0')}`;
            const codigoElement = document.getElementById('codigoViaje');
            if (codigoElement) {
                codigoElement.textContent = codigo;
            }
        }

        function actualizarContadorUnidades() {
            document.getElementById('contadorUnidades').textContent = contadorUnidades;
        }

        function actualizarLabelTipoUnidad() {
            const labelElement = document.getElementById('label-tipo-unidad');
            if (!labelElement) return;

            if (contadorUnidades === 1) {
                labelElement.textContent = ' (Unidad Única)';
                labelElement.classList.remove('convoy');
            } else if (contadorUnidades > 1) {
                labelElement.textContent = ' (Convoy de Unidades)';
                labelElement.classList.add('convoy');
            } else {
                labelElement.textContent = '';
                labelElement.classList.remove('convoy');
            }
        }

        function actualizarBotonAgregar() {
            const boton = document.getElementById('btnAgregarUnidad');
            if (contadorUnidades >= MAX_UNIDADES) {
                boton.disabled = true;
                boton.textContent = `Límite de ${MAX_UNIDADES} Unidades Alcanzado`;
            } else {
                const restantes = MAX_UNIDADES - contadorUnidades;
                boton.innerHTML =
                    `<i class="fas fa-plus-circle"></i> Agregar Unidad (${restantes} restante${restantes !== 1 ? 's' : ''})`;
            }
        }

        // 🚀 NUEVA FUNCIÓN SOLICITADA 🚀
        function actualizarTipoVehiculo(unidadNumero) {
            const select = document.querySelector(`#unidad-${unidadNumero} .unidad-vehiculo`);
            const label = document.getElementById(`tipo-vehiculo-${unidadNumero}`);
            if (!select || !label) return;

            const vehiculo = select.value;
            const tipo = clasificacionVehiculos[vehiculo] || "";

            label.textContent = tipo;
            // Limpiar clases anteriores
            label.className = "tipo-vehiculo-text";

            if (tipo === "Unidad Ligera") {
                label.classList.add("tipo-ligera");
            } else if (tipo === "Unidad Pesada") {
                label.classList.add("tipo-pesada");
            }
        }

        function agregarUnidad() {
            if (contadorUnidades >= MAX_UNIDADES) {
                Swal.fire({
                    title: 'Límite alcanzado',
                    text: `Solo puedes agregar hasta ${MAX_UNIDADES} unidades por solicitud.`,
                    icon: 'warning',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                return;
            }

            contadorUnidades++;
            const numeroUnidad = contadorUnidades;
            const filaId = `unidad-${numeroUnidad}`;

            datosInspeccion[numeroUnidad] = {};

            if (numeroUnidad === 2) {
                Swal.fire({
                    title: '¡Convoy de Unidades!',
                    html: 'Has agregado una segunda unidad. Tu solicitud ahora será gestionada como un Convoy de Unidades. Asegúrate de que las unidades cumplan con todos los requisitos.',
                    icon: 'info',
                    confirmButtonColor: 'var(--primary-blue)',
                    customClass: {
                        popup: 'swal2-convoy'
                    }
                });
            }

            const nuevaFila = document.createElement('tr');
            nuevaFila.id = filaId;
            nuevaFila.innerHTML = `
        <td>
            <div class="conductor-completo-group">
                <div class="hour-input-group" style="align-items: center;">
                    <label><i class="fas fa-user-circle"></i> Nombre Completo</label>
                    <div class="conductor-input-group">
                        <input type="text" class="table-input large unidad-conductor" id="conductor-${numeroUnidad}" name="unidad[${numeroUnidad}][conductor]" placeholder="Escriba nombre y seleccione" required autocomplete="off">
                        <div class="autocomplete-list" id="autocomplete-list-${numeroUnidad}"></div>
                    </div>
                </div>

                <div class="hour-input-group" style="display: none;">
                        <input type="hidden" id="licencia-num-${numeroUnidad}" name="unidad[${numeroUnidad}][licencia_num]">
                </div>

                <div class="hour-input-group" style="display: none;">
                        <input type="hidden" id="vigencia-lic-hidden-${numeroUnidad}" name="unidad[${numeroUnidad}][vigencia_lic_hidden]">
                </div>


                <div class="hour-input-group">
                    <label><i class="fas fa-wind"></i> ¿Realizó Alcoholimetría?</label>
                    <select class="table-input small unidad-alcoholimetria" name="unidad[${numeroUnidad}][alcoholimetria]" required onchange="actualizarBotonReunionConvoy()">
                        <option value="">Seleccionar</option>
                        <option value="si">Sí</option>
                        <option value="no">No</option>
                    </select>
                </div>
            </div>
        </td>

        <td>
            <div class="hour-input-group">
                <label><i class="fas fa-id-card"></i> Vigencia Licencia</label>
                <input type="text" class="table-input" id="vigencia-lic-${numeroUnidad}" name="unidad[${numeroUnidad}][vigencia_lic]" placeholder="202X-XX-XX" required readonly>
            </div>
            <div class="hour-input-group" style="margin-top: 5px;">
                <label><i class="fas fa-calendar-alt"></i> Vigencia Man. Def.</label>
                <input type="text" class="table-input" id="vigencia-man-${numeroUnidad}" name="unidad[${numeroUnidad}][vigencia_man]" placeholder="202X-XX-XX" required readonly>
            </div>
        </td>

        <td>
            <div class="hour-input-group">
                <label><i class="fas fa-bed"></i> Hr que Durmió</label>
                <input type="text" class="table-input small unidad-hora-dormir" id="dormir-${numeroUnidad}" name="unidad[${numeroUnidad}][hora_dormir]" placeholder="HH:MM AM/PM" required>
            </div>
            <div class="hour-input-group">
                <label><i class="fas fa-sun"></i> Hr que Despertó</label>
                <input type="text" class="table-input small unidad-hora-levantar" id="levantar-${numeroUnidad}" name="unidad[${numeroUnidad}][hora_levantar]" placeholder="HH:MM AM/PM" required>
            </div>
            <div class="hour-input-group" style="margin-top: 5px;">
                <label style="font-weight: 700; color: var(--primary-blue);"><i class="fas fa-hourglass-half"></i> Hrs Dormidas</label>
                <input type="text" class="table-input small unidad-total-dormidas hour-input-result" id="total-hrs-dormidas-${numeroUnidad}" name="unidad[${numeroUnidad}][total_dormidas]" placeholder="0.0" readonly>
            </div>
        </td>

        <td class="td-horas-conduccion">
            <div class="hour-inputs-group-combined-vertical">
                <div class="hour-input-group">
                    <label><i class="fas fa-bed"></i> Hr Despierto</label>
                    <input type="text" class="table-input small unidad-horas-despierto" id="horas-despierto-${numeroUnidad}" name="unidad[${numeroUnidad}][horas_despierto]" placeholder="0.0" required onchange="calcularTotalHoras(${numeroUnidad}); actualizarBotonReunionConvoy()">
                </div>
                <div class="hour-input-group">
                    <label><i class="fas fa-route"></i> Duración Viaje</label>
                    <input type="text" class="table-input small unidad-horas-viaje" id="horas-viaje-${numeroUnidad}" name="unidad[${numeroUnidad}][horas_viaje]" placeholder="0.0" required onchange="calcularTotalHoras(${numeroUnidad}); actualizarBotonReunionConvoy()">
                </div>
                <div class="hour-input-group" style="margin-top: 5px;">
                    <label style="font-weight: 700; color: var(--primary-blue);"><i class="fas fa-clock"></i> Total Hrs</label>
                    <input type="text" class="table-input small unidad-total-finalizar hour-input-result" id="total-hrs-finalizar-${numeroUnidad}" name="unidad[${numeroUnidad}][total_finalizar]" placeholder="0.0" readonly>
                </div>
            </div>
        </td>

        <td class="td-pasajeros">
            <div id="pasajeros-unidad-${numeroUnidad}" class="pasajero-container">
            </div>
            <button type="button" class="btn-add-pasajero" id="btn-add-pasajero-${numeroUnidad}" onclick="agregarPasajero(${numeroUnidad})" title="Agregar pasajero">
                <i class="fas fa-user-plus"></i>
            </button>
        </td>

        <td>
            <select class="table-input large unidad-vehiculo" name="unidad[${numeroUnidad}][vehiculo]" required onchange="actualizarNombreVehiculoInspeccion(${numeroUnidad}); actualizarBotonReunionConvoy(); actualizarTipoVehiculo(${numeroUnidad})">
                <option value="">Seleccionar Vehículo</option>
                ${vehiculos.map(v => `<option value="${v}">${v}</option>`).join('')}
            </select>
            <div id="tipo-vehiculo-${numeroUnidad}" class="tipo-vehiculo-text"></div>
        </td>

        <td>
            <button type="button" class="btn-viajes btn-inspeccion" id="btn-inspeccion-${numeroUnidad}" data-aprobado="false" onclick="gestionarModalInspeccion(true, ${numeroUnidad})" title="Realizar Inspección Pre-Viaje">
                <i class="fas fa-clipboard"></i>
            </button>
        </td>

        <td class="acciones-td">
            <button type="button" class="btn-accion eliminar" onclick="eliminarUnidad(${numeroUnidad})" title="Eliminar unidad">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;

            document.getElementById('cuerpoTablaUnidades').appendChild(nuevaFila);
            actualizarContadorUnidades();
            actualizarLabelTipoUnidad();
            actualizarBotonAgregar();
            inicializarAutocompleteConductor(numeroUnidad);

            flatpickr(nuevaFila.querySelector('.unidad-hora-dormir'), configHoraVisual);
            flatpickr(nuevaFila.querySelector('.unidad-hora-levantar'), configHoraVisual);

            nuevaFila.querySelector('.unidad-hora-dormir').addEventListener('change', function() {
                calcularHorasDormidas(numeroUnidad);
                actualizarBotonReunionConvoy();
            });

            nuevaFila.querySelector('.unidad-hora-levantar').addEventListener('change', function() {
                calcularHorasDormidas(numeroUnidad);
                actualizarBotonReunionConvoy();
            });

            nuevaFila.querySelector('.unidad-alcoholimetria').addEventListener('change', function() {
                actualizarBotonReunionConvoy();
            });

            nuevaFila.querySelector(`#horas-despierto-${numeroUnidad}`).addEventListener('change', function() {
                calcularTotalHoras(numeroUnidad);
                actualizarBotonReunionConvoy();
            });
            nuevaFila.querySelector(`#horas-viaje-${numeroUnidad}`).addEventListener('change', function() {
                calcularTotalHoras(numeroUnidad);
                actualizarBotonReunionConvoy();
            });
            const selectVehiculo = nuevaFila.querySelector('.unidad-vehiculo');
            if (selectVehiculo) {
                selectVehiculo.setAttribute('onchange',
                    `actualizarNombreVehiculoInspeccion(${numeroUnidad}); actualizarBotonReunionConvoy(); actualizarTipoVehiculo(${numeroUnidad})`
                );
            }


            agregarPasajero(numeroUnidad);
            actualizarBotonReunionConvoy();
            actualizarBotonEvaluacion();
        }

        function eliminarUnidad(numero) {
            Swal.fire({
                title: '¿Eliminar unidad?',
                text: 'Se eliminarán todos los datos de esta unidad y sus pasajeros.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: 'var(--accent-red)',
                cancelButtonColor: 'var(--primary-blue)',
            }).then((result) => {
                if (result.isConfirmed) {
                    const fila = document.getElementById(`unidad-${numero}`);
                    fila.remove();

                    delete datosInspeccion[numero];

                    contadorUnidades--;
                    actualizarContadorUnidades();
                    actualizarLabelTipoUnidad();
                    actualizarBotonAgregar();
                    reordenarNumerosUnidades();
                    actualizarBotonReunionConvoy();
                    actualizarBotonEvaluacion();
                }
            });
        }

        function actualizarNombreVehiculoInspeccion(unidadNumero) {
            if (unidadEnInspeccion === unidadNumero) {
                const selectVehiculo = document.querySelector(`#unidad-${unidadNumero} .unidad-vehiculo`);
                const nombreVehiculo = selectVehiculo ? selectVehiculo.value : 'Unidad ' + unidadNumero;
                document.getElementById('inspeccionUnidadNombre').textContent = nombreVehiculo;
            }
        }



        function reordenarNumerosUnidades() {
            const filas = document.querySelectorAll('#cuerpoTablaUnidades tr');
            contadorUnidades = 0;

            const nuevosDatosInspeccion = {};

            filas.forEach((fila, index) => {
                const numeroAnterior = parseInt(fila.id.split('-')[1]);
                const nuevoNumero = index + 1;
                contadorUnidades = nuevoNumero;
                const filaId = `unidad-${nuevoNumero}`;
                fila.id = filaId;

                if (datosInspeccion[numeroAnterior]) {
                    nuevosDatosInspeccion[nuevoNumero] = datosInspeccion[numeroAnterior];
                    delete datosInspeccion[numeroAnterior];
                }

                const oldLicenciaNum = fila.querySelector(`#licencia-num-${numeroAnterior}`);
                const oldVigenciaLic = fila.querySelector(`#vigencia-lic-${numeroAnterior}`);
                const oldVigenciaMan = fila.querySelector(`#vigencia-man-${numeroAnterior}`);

                if (oldLicenciaNum) oldLicenciaNum.id = `licencia-num-${nuevoNumero}`;
                if (oldVigenciaLic) oldVigenciaLic.id = `vigencia-lic-${nuevoNumero}`;
                if (oldVigenciaMan) oldVigenciaMan.id = `vigencia-man-${nuevoNumero}`;

                fila.querySelector('.unidad-conductor').id = `conductor-${nuevoNumero}`;
                fila.querySelector('#autocomplete-list-' + numeroAnterior).id = `autocomplete-list-${nuevoNumero}`;

                inicializarAutocompleteConductor(nuevoNumero);

                const btnEliminar = fila.querySelector('.btn-accion.eliminar');
                if (btnEliminar) {
                    btnEliminar.setAttribute('onclick', `eliminarUnidad(${nuevoNumero})`);
                }

                const btnInspeccion = fila.querySelector('.btn-inspeccion, .btn-submit, .btn-inspeccion-aprobado');
                if (btnInspeccion) {
                    btnInspeccion.id = `btn-inspeccion-${nuevoNumero}`;
                    btnInspeccion.setAttribute('onclick', `gestionarModalInspeccion(true, ${nuevoNumero})`);
                    if (btnInspeccion.dataset.aprobado === 'true') {
                        btnInspeccion.innerHTML = '<i class="fas fa-check-circle"></i>';
                    } else {
                        btnInspeccion.innerHTML = '<i class="fas fa-clipboard"></i>';
                    }
                }



                const oldDormir = document.getElementById(`dormir-${numeroAnterior}`);
                const oldLevantar = document.getElementById(`levantar-${numeroAnterior}`);
                const oldTotalDormidas = document.getElementById(`total-hrs-dormidas-${numeroAnterior}`);
                const oldHrsDespierto = document.getElementById(`horas-despierto-${numeroAnterior}`);
                const oldHrsViaje = document.getElementById(`horas-viaje-${numeroAnterior}`);
                const oldTotalFinalizar = document.getElementById(`total-hrs-finalizar-${numeroAnterior}`);
                const tipoVehiculoDiv = document.getElementById(`tipo-vehiculo-${numeroAnterior}`);
                const selectVehiculo = fila.querySelector('.unidad-vehiculo');


                if (oldDormir) oldDormir.id = `dormir-${nuevoNumero}`;
                if (oldLevantar) oldLevantar.id = `levantar-${nuevoNumero}`;
                if (oldTotalDormidas) oldTotalDormidas.id = `total-hrs-dormidas-${nuevoNumero}`;
                if (oldHrsDespierto) oldHrsDespierto.id = `horas-despierto-${nuevoNumero}`;
                if (oldHrsViaje) oldHrsViaje.id = `horas-viaje-${nuevoNumero}`;
                if (oldTotalFinalizar) oldTotalFinalizar.id = `total-hrs-finalizar-${nuevoNumero}`;
                if (tipoVehiculoDiv) tipoVehiculoDiv.id = `tipo-vehiculo-${nuevoNumero}`;


                // Actualizar eventos
                fila.querySelector('.unidad-hora-dormir').addEventListener('change', function() {
                    calcularHorasDormidas(nuevoNumero);
                    actualizarBotonReunionConvoy();
                });

                fila.querySelector('.unidad-hora-levantar').addEventListener('change', function() {
                    calcularHorasDormidas(nuevoNumero);
                    actualizarBotonReunionConvoy();
                });

                fila.querySelector('.unidad-alcoholimetria').addEventListener('change', function() {
                    actualizarBotonReunionConvoy();
                });

                fila.querySelector(`#horas-despierto-${nuevoNumero}`).addEventListener('change', function() {
                    calcularTotalHoras(nuevoNumero);
                    actualizarBotonReunionConvoy();
                });
                fila.querySelector(`#horas-viaje-${nuevoNumero}`).addEventListener('change', function() {
                    calcularTotalHoras(nuevoNumero);
                    actualizarBotonReunionConvoy();
                });
                if (selectVehiculo) {
                    selectVehiculo.setAttribute('onchange',
                        `actualizarNombreVehiculoInspeccion(${nuevoNumero}); actualizarBotonReunionConvoy(); actualizarTipoVehiculo(${nuevoNumero})`
                    );
                }


                const pasajeroContainer = fila.querySelector('.pasajero-container');
                if (pasajeroContainer) {
                    pasajeroContainer.id = `pasajeros-unidad-${nuevoNumero}`;
                    const btnAddPasajero = fila.querySelector('.btn-add-pasajero');
                    btnAddPasajero.id = `btn-add-pasajero-${nuevoNumero}`;
                    btnAddPasajero.setAttribute('onclick', `agregarPasajero(${nuevoNumero})`);

                    pasajeroContainer.querySelectorAll('.pasajero-input-group').forEach((group, pIndex) => {
                        const newIndex = pIndex + 1;
                        const input = group.querySelector('input');
                        const removeBtn = group.querySelector('.btn-remove-pasajero');

                        group.setAttribute('data-p-index', newIndex);
                        input.setAttribute('name', `unidad[${nuevoNumero}][pasajeros][p${newIndex}]`);
                        if (removeBtn) {
                            removeBtn.setAttribute('onclick',
                                `eliminarPasajero(${nuevoNumero}, ${newIndex})`);
                        }
                    });
                }

                // Actualizar atributos `name`
                const inputs = fila.querySelectorAll('input, select');
                inputs.forEach(input => {
                    const name = input.getAttribute('name');
                    if (name && name.startsWith('unidad')) {
                        const newName = name.replace(/unidad\[\d+\]/, `unidad[${nuevoNumero}]`);
                        input.setAttribute('name', newName);
                    }
                });
            });

            Object.assign(datosInspeccion, nuevosDatosInspeccion);
            actualizarContadorUnidades();
            actualizarLabelTipoUnidad(); // Actualizar label
            actualizarBotonAgregar();
            actualizarBotonReunionConvoy();
        }

        function enviarSolicitud() {
            const form = document.getElementById('formViaje');

            // Valida campos del formulario principal
            if (!form.checkValidity()) {
                form.reportValidity();
                Swal.fire({
                    title: 'Faltan Campos',
                    text: 'Por favor, llene todos los campos requeridos en la sección de Información General y Detalles del Trayecto.',
                    icon: 'warning',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                return;
            }

            // Validar Destino si es "Otro" y/o si el campo específico está vacío
            const destinoSelector = document.getElementById('destinoPredefinido');
            const destinoEspecifico = document.getElementById('destinoEspecifico');

            // Si el selector es "Otro" O si el selector está vacío Y el campo específico también, entonces hay error.
            if (destinoSelector.value === '' || (destinoSelector.value === 'Otro' && destinoEspecifico.value.trim() ===
                    '')) {
                Swal.fire({
                    title: 'Destino no especificado',
                    text: 'Por favor, seleccione un destino de la lista o especifique uno en el campo de texto.',
                    icon: 'warning',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                // Enfocar el campo correspondiente
                if (destinoSelector.value === '') {
                    destinoSelector.focus();
                } else {
                    destinoEspecifico.focus();
                }
                return;
            }

            // 🚀 VALIDACIÓN DE PARADAS 🚀
            const tieneParadas = document.querySelector('input[name="tiene_paradas"]:checked').value;
            if (tieneParadas === 'si') {
                const listaParadas = document.getElementById('listaParadas');
                if (listaParadas.children.length === 0) {
                    Swal.fire({
                        title: 'Paradas Requeridas',
                        text: 'Indicó que realizaría paradas, pero no ha agregado ninguna. Por favor agregue al menos una o cambie la opción a "No".',
                        icon: 'warning',
                        confirmButtonColor: 'var(--primary-blue)'
                    });
                    return;
                }

                // Validar que los campos de las paradas no estén vacíos
                let paradasCompletas = true;
                listaParadas.querySelectorAll('input, select').forEach(input => {
                    if (input.value.trim() === '') {
                        paradasCompletas = false;
                    }
                });

                if (!paradasCompletas) {
                    Swal.fire({
                        title: 'Datos de Paradas Incompletos',
                        text: 'Por favor, complete todos los campos de Propósito y Ubicación para las paradas agregadas.',
                        icon: 'warning',
                        confirmButtonColor: 'var(--primary-blue)'
                    });
                    return;
                }
            }


            if (contadorUnidades === 0) {
                Swal.fire({
                    title: 'Sin unidades',
                    text: 'Debe agregar al menos una unidad vehicular y su conductor.',
                    icon: 'warning',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                return;
            }

            let unidadesCompletas = true;
            let inspeccionesAprobadas = true;
            let unidadConProblema = 0;

            for (let i = 1; i <= contadorUnidades; i++) {
                const btnInspeccion = document.getElementById(`btn-inspeccion-${i}`);
                if (!btnInspeccion || btnInspeccion.dataset.aprobado !== 'true') {
                    inspeccionesAprobadas = false;
                    unidadConProblema = i;
                }

                const fila = document.getElementById(`unidad-${i}`);
                if (fila) {
                    // Validar campos requeridos de la fila de la unidad
                    fila.querySelectorAll('input[required], select[required]').forEach(input => {
                        // Verifica campos vacíos
                        if (input.value.trim() === "") {
                            unidadesCompletas = false;
                            unidadConProblema = i;
                        }
                        // Revisa el campo de Vigencia Licencia (si está vacío, no se seleccionó conductor)
                        if (input.id.startsWith('vigencia-lic-') && input.value.trim() === "") {
                            unidadesCompletas = false;
                            unidadConProblema = i;
                        }
                    });
                }

                // Validar campos de pasajeros requeridos (que no estén vacíos)
                const pasajeroContainer = document.getElementById(`pasajeros-unidad-${i}`);
                if (pasajeroContainer) {
                    pasajeroContainer.querySelectorAll('input[required]').forEach(input => {
                        if (input.value.trim() === "") {
                            unidadesCompletas = false;
                            unidadConProblema = i;
                        }
                    });
                }

                if (!inspeccionesAprobadas || !unidadesCompletas) {
                    break;
                }
            }

            if (!unidadesCompletas) {
                Swal.fire({
                    title: 'Información de Unidades Incompleta',
                    html: `Asegúrese de **seleccionar un conductor de la lista** y llenar todos los campos requeridos de la Unidad **#${unidadConProblema}**, incluyendo la **Vigencia Man. Def.** y los **pasajeros** con nombre completo.`,
                    icon: 'warning',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                return;
            }

            if (!inspeccionesAprobadas) {
                Swal.fire({
                    title: 'Inspección Pendiente/Incompleta',
                    text: `La Unidad #${unidadConProblema} debe tener la inspección vehicular realizada y **aprobada** antes de enviar la solicitud.`,
                    icon: 'error',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                return;
            }

            // Validar Reunión Pre-Convoy si hay más de una unidad
            if (contadorUnidades > 1 && !reunionPreConvoyGuardada) {
                Swal.fire({
                    title: 'Reunión Pre-Convoy Requerida',
                    text: 'Al ser un Convoy, debe realizar y confirmar la Reunión Pre-convoy antes de enviar la solicitud.',
                    icon: 'error',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                return;
            }


            Swal.fire({
                title: '¿Enviar solicitud?',
                text: 'La solicitud será enviada para su aprobación.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Revisar',
                confirmButtonColor: 'var(--primary-blue)',
                cancelButtonColor: 'var(--medium-gray)',
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: '¡Solicitud enviada!',
                        html: `<p>Tu solicitud <strong>${document.getElementById('codigoViaje').textContent}</strong> ha sido enviada exitosamente. Será revisada por el área de control.</p>`,
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: 'var(--primary-blue)'
                    }).then(() => {
                        generarCodigoViaje(true);
                        document.getElementById('modalFormulario').classList.remove('active');
                        document.body.style.overflow = 'auto';
                        limpiarFormulario();
                    });
                }
            });
        }
    </script>
    @stack('scripts')
</body>

</html>
