<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios y Permisos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.12/sweetalert2.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #2c3e50;
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%);
            padding: 1.2rem 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            border-bottom: 3px solid #dc143c;
        }

        .header-content {
            display: flex;
            align-items: center;
            gap: 1rem;
            max-width: 100%;
            margin: 0 auto;
        }

        .logo {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #dc143c 0%, #8b0000 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.4rem;
            box-shadow: 0 4px 15px rgba(220, 20, 60, 0.3);
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header-title i {
            color: #dc143c;
            font-size: 1.4rem;
        }

        .header-title h1 {
            font-size: 1.6rem;
            font-weight: 700;
            color: white;
        }


        /* Main Container */
        .container {
            max-width: 2000px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Controls Bar */
        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2rem 2rem 1.5rem 2rem;
            gap: 1rem;
        }

        .search-container {
            position: relative;
            flex: 1;
            max-width: 450px;
        }

        .search-input {
            width: 100%;
            padding: 0.9rem 1.2rem 0.9rem 3rem;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 0.95rem;
            background: white;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #dc143c;
            box-shadow: 0 0 0 4px rgba(220, 20, 60, 0.1);
            transform: translateY(-1px);
        }

        .search-input::placeholder {
            color: #0c0d0d;
            transition: all 0.5s ease;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #dc143c;
            font-size: 1.1rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.9rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #dc143c 0%, #b91c3c 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #b91c3c 0%, #991b3c 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 20, 60, 0.25);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%);
            color: white;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #1a1a1a 0%, #000000 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        /* Table */
        .table-container {
            padding: 0 2rem 2rem 2rem;
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .table th {
            background: linear-gradient(135deg, #1a1a1a 0%, #1a1a1a 100%);
            padding: 1.2rem;
            text-align: left;
            font-weight: 700;
            color: white;
            font-size: 0.9rem;
            letter-spacing: 1px;
            position: relative;
        }

        .table th:first-child {
            border-top-left-radius: 15px;
        }

        .table th:last-child {
            border-top-right-radius: 15px;
        }

        .table td {
            padding: 1.2rem;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
            background: white;
        }

        .table tr:hover td {
            background: linear-gradient(135deg, #fff5f5 0%, #fef2f2 100%);
        }

        .table tbody tr:last-child td:first-child {
            border-bottom-left-radius: 15px;
        }

        .table tbody tr:last-child td:last-child {
            border-bottom-right-radius: 15px;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #dc143c 0%, #8b0000 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            box-shadow: 0 4px 15px rgba(220, 20, 60, 0.2);
        }

        .user-details h4 {
            font-size: 1rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 0.3rem;
        }

        .user-details p {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 500;
        }

        .permissions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .permission-tag {
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 25px;
            font-size: 0.75rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        /* Colores específicos para cada área */
        .permission-tag.administracion {
            background: linear-gradient(135deg, #dc143c 0%, #8b0000 100%);
        }

        .permission-tag.qhse {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .permission-tag.ventas {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }

        .permission-tag.recursos-humanos {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .permission-tag.suministro {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }

        .permission-tag.operaciones {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        .permission-tag.sistemas {
            background: linear-gradient(135deg, #11bd36 0%, #11bd36b3 100%);
        }

        .permission-tag.almacen {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        }

        .permission-tag.geociencias {
            background: linear-gradient(135deg, #84cc16 0%, #65a30d 100%);
        }

        .more-permissions {
            background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%);
            color: white;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status.active {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
        }

        .status.inactive {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.2);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .actions {
            display: flex;
            gap: 0.6rem;
        }

        .action-btn {
            width: 38px;
            height: 38px;
            border: none;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .action-btn.view {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
        }

        .action-btn.edit {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .action-btn.delete {
            background: linear-gradient(135deg, #dc143c 0%, #8b0000 100%);
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        /* Paginación */
        .pagination-container {
            padding: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-top: 1px solid #e2e8f0;
        }

        .pagination-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .items-per-page {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .items-per-page label {
            font-weight: 600;
            color: #374151;
        }

        .items-per-page select {
            padding: 0.5rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            background: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .items-per-page select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .pagination-nav {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-info {
            font-weight: 600;
            color: #374151;
            padding: 0 1rem;
        }

        .pagination-controls {
            display: flex;
            gap: 0.5rem;
        }

        .page-btn {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 10px;
            background: white;
            color: #374151;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .page-btn:hover {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            transform: translateY(-2px);
        }

        .page-btn.active {
            background: linear-gradient(135deg, #dc143c 0%, #8b0000 100%);
            color: white;
        }

        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .page-btn:disabled:hover {
            background: white;
            color: #374151;
        }

        @media (max-width: 768px) {
            .pagination-container {
                flex-direction: column;
                gap: 1rem;
            }

            .pagination-nav {
                flex-direction: column;
                gap: 1rem;
            }
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeIn 0.4s ease;
            padding: 20px;
        }

        .modal-overlay.show {
            display: flex;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .modal {
            background: white;
            border-radius: 24px;
            box-shadow: 0 32px 64px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 900px;
            max-height: 95vh;
            overflow: hidden;
            animation: slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            display: flex;
            flex-direction: column;
        }

        @keyframes slideUp {
            from {
                transform: translateY(60px) scale(0.95);
                opacity: 0;
            }

            to {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        .modal-header {
            padding: 2.5rem 3rem;
            border-bottom: 4px solid #dc143c;
            background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .modal-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 0%, rgba(220, 20, 60, 0.1) 50%, transparent 100%);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {

            0%,
            100% {
                transform: translateX(-100%);
            }

            50% {
                transform: translateX(100%);
            }
        }

        .modal-header h3 {
            font-size: 1.75rem;
            font-weight: 700;
            color: white;
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 1;
            position: relative;
        }

        .modal-header h3 i {
            color: #dc143c;
            font-size: 1.5rem;
            background: rgba(220, 20, 60, 0.2);
            padding: 0.5rem;
            border-radius: 12px;
        }

        .close-btn {
            background: rgba(220, 20, 60, 0.15);
            border: 2px solid #dc143c;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #dc143c;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1;
            position: relative;
        }

        .close-btn:hover {
            background: #dc143c;
            color: white;
            transform: rotate(90deg) scale(1.1);
            box-shadow: 0 8px 20px rgba(220, 20, 60, 0.4);
        }

        .modal-body {
            padding: 3rem;
            overflow-y: auto;
            flex: 1;
        }

        /* Form Styles */
        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f1f3f4;
        }

        .section-title i {
            color: #dc143c;
            background: rgba(220, 20, 60, 0.1);
            padding: 0.5rem;
            border-radius: 10px;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -15px;
        }

        .col-12 {
            flex: 0 0 100%;
            max-width: 100%;
            padding: 0 15px;
        }

        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 15px;
        }

        @media (max-width: 768px) {
            .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }

        .mb-3 {
            margin-bottom: 1.5rem;
        }

        .mb-4 {
            margin-bottom: 2.5rem;
        }

        .mt-4 {
            margin-top: 2.5rem;
        }

        .me-2 {
            margin-right: 0.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: #2c2c2c;
            font-size: 0.95rem;
            letter-spacing: 0.25px;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e9ecef;
            border-radius: 16px;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
            color: #2c2c2c;
            font-family: inherit;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #dc143c;
            box-shadow: 0 0 0 6px rgba(220, 20, 60, 0.1);
            transform: translateY(-2px);
        }

        .form-control::placeholder {
            color: #adb5bd;
        }

        /* Module Styles */
        .module-area {
            background: white;
            border: 2px solid #f1f3f4;
            border-radius: 20px;
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .module-area:hover {
            border-color: #dc143c;
            box-shadow: 0 8px 24px rgba(220, 20, 60, 0.1);
            transform: translateY(-2px);
        }

        .module-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 1px solid #f1f3f4;
        }

        .module-header:hover {
            background: linear-gradient(135deg, #dc143c 0%, #b8102f 100%);
        }

        .module-header:hover .module-title {
            color: white;
        }

        .module-header:hover .module-title i {
            color: white;
            background: rgba(255, 255, 255, 0.2);
        }

        .module-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c2c2c;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
        }

        .module-title i {
            background: rgba(220, 20, 60, 0.1);
            color: #dc143c;
            padding: 0.5rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .module-body {
            padding: 0;
            max-height: 0;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .module-body.active {
            max-height: 500px;
            padding: 1.5rem 2rem;
        }

        .permission-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid #f1f3f4;
            transition: all 0.3s ease;
        }

        .permission-item:last-child {
            border-bottom: none;
        }

        .permission-item:hover {
            background: rgba(220, 20, 60, 0.05);
            padding-left: 1rem;
            margin: 0 -1rem;
            border-radius: 12px;
        }

        .permission-item span {
            font-size: 0.95rem;
            font-weight: 500;
            color: #2c2c2c;
        }

        /* Switch Styles */
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 32px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cdd4da;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 32px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 24px;
            width: 24px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        input:checked+.slider {
            background-color: #dc143c;
            box-shadow: 0 0 0 4px rgba(220, 20, 60, 0.2);
        }

        input:checked+.slider:before {
            transform: translateX(28px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 2rem;
            border: none;
            border-radius: 16px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            font-family: inherit;
            min-width: 140px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #dc143c 0%, #b8102f 100%);
            color: white;
            box-shadow: 0 4px 16px rgba(220, 20, 60, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(220, 20, 60, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            box-shadow: 0 4px 16px rgba(108, 117, 125, 0.3);
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(108, 117, 125, 0.4);
        }

        .text-end {
            text-align: right;
        }

        .text-muted {
            color: #6c757d;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .modal {
                margin: 10px;
                max-width: calc(100% - 20px);
            }

            .modal-header {
                padding: 2rem;
            }

            .modal-header h3 {
                font-size: 1.5rem;
            }

            .modal-body {
                padding: 2rem;
            }

            .text-end {
                text-align: center;
            }

            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }

/* ===== MODAL DE VISUALIZACIÓN DE USUARIO ===== */
/* Contenedor principal del modal */
.user-view-modal-popup {
    border-radius: 24px !important;
    box-shadow: 0 32px 64px rgba(0, 0, 0, 0.25) !important;
    border: none !important;
    overflow: hidden !important;
}

/* Título del modal */
.user-view-modal-title {
    background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%) !important;
    color: white !important;
    padding: 1rem 2rem !important;
    margin: 0 !important;
    border-radius: 0 !important;
    font-size: 1.75rem !important;
    font-weight: 900 !important;
    border-bottom: 2px solid #dc143c !important;
    position: relative !important;
    overflow: hidden !important;
}

.user-view-modal-title::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 0%, rgba(220, 20, 60, 0.05) 50%, transparent 100%);
    animation: shimmer 6s ease-in-out infinite;
}

@keyframes shimmer {
    0%, 100% {
        transform: translateX(-100%);
        opacity: 0;
    }
    50% {
        transform: translateX(100%);
        opacity: 1;
    }
}

.uvm-title-section {
    display: flex;
    align-items: center;
    gap: 1rem;
    justify-content: center;
    z-index: 1;
    position: relative;
}

.uvm-user-avatar {
    color: #dc143c;
    font-size: 1.5rem;
    background: rgba(220, 20, 60, 0.2);
    padding: 0.5rem;
    border-radius: 12px;
    filter: none;
}

.uvm-user-name {
    font-size: 1.75rem;
    font-weight: 700;
    text-shadow: none;
}

/* Contenido del modal */
.user-view-modal-html {
    padding: 0 !important;
    margin: 0 !important;
}

.user-view-modal-content {
    padding: 1rem;
    background: white;
    min-height: 400px;
}

/* Información básica - diseño limpio en 2 filas */
.uvm-info-grid {
    margin-bottom: 2.5rem;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.uvm-info-item {
    background: white;
    padding: 1.5rem;
    border-bottom: 1px solid #f1f3f4;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-radius: 12px;
    border: 1px solid #f1f3f4;
    transition: all 0.2s ease;
}

.uvm-info-item:hover {
    border-color: rgba(220, 20, 60, 0.2);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
}

.uvm-info-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.95rem;
    font-weight: 600;
    color: #2c2c2c;
    letter-spacing: 0.25px;
}

.uvm-info-label i {
    background: rgba(220, 20, 60, 0.1);
    color: #dc143c;
    padding: 0.5rem;
    border-radius: 10px;
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.uvm-info-value {
    font-size: 0.95rem;
    color: #2c2c2c;
    font-weight: 500;
    word-break: break-word;
}

/* Estados */
.uvm-status {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.uvm-status-active {
    background: rgba(40, 167, 69, 0.15);
    color: #28a745;
    border: 1px solid rgba(40, 167, 69, 0.3);
}

.uvm-status-inactive {
    background: rgba(220, 20, 60, 0.15);
    color: #dc143c;
    border: 1px solid rgba(220, 20, 60, 0.3);
}

/* Sección de permisos */
.uvm-permissions-section {
    margin-top: 2.5rem;
}

.uvm-section-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.25rem;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 2rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #f1f3f4;
}

.uvm-section-header i {
    background: rgba(220, 20, 60, 0.1);
    color: #dc143c;
    padding: 0.5rem;
    border-radius: 10px;
}

.uvm-permissions-container {
    max-height: 400px;
    overflow-y: auto;
    padding-right: 10px;
    /* Grid en 2 columnas para módulos */
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

/* Scrollbar personalizado para el contenedor de permisos */
.uvm-permissions-container::-webkit-scrollbar {
    width: 6px;
}

.uvm-permissions-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.uvm-permissions-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.uvm-permissions-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Bloques de módulos */
.uvm-module-block {
    background: white;
    border: 2px solid #f1f3f4;
    border-radius: 16px;
    margin-bottom: 0;
    overflow: hidden;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    height: fit-content;
}

.uvm-module-block:hover {
    border-color: rgba(220, 20, 60, 0.3);
    box-shadow: 0 4px 12px rgba(220, 20, 60, 0.08);
    transform: translateY(-1px);
}

.uvm-module-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f1f3f4;
    transition: all 0.2s ease;
}

.uvm-module-header:hover {
    background: linear-gradient(135deg, rgba(220, 20, 60, 0.05) 0%, rgba(220, 20, 60, 0.08) 100%);
}

.uvm-module-header:hover .uvm-module-name {
    color: #dc143c;
}

.uvm-module-header:hover .uvm-module-icon {
    color: #dc143c;
    background: rgba(220, 20, 60, 0.15);
    transform: scale(1.05);
}

.uvm-module-icon {
    background: rgba(220, 20, 60, 0.1);
    color: #dc143c;
    padding: 0.5rem;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.2s ease;
}

.uvm-module-name {
    font-size: 1rem;
    font-weight: 600;
    color: #2c2c2c;
    flex-grow: 1;
    text-transform: capitalize;
    margin-left: 0.75rem;
    transition: all 0.2s ease;
}

.uvm-permissions-count {
    background: #dc143c;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    min-width: 24px;
    text-align: center;
    transition: all 0.2s ease;
}

/* Permisos en 3 columnas */
.uvm-module-permissions {
    padding: 1.25rem;
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 0.75rem;
}

.uvm-permission-item {
    background: #fafbfc;
    border: 1px solid #e9ecef;
    padding: 0.6rem 0.8rem;
    border-radius: 10px;
    font-size: 0.85rem;
    color: #2c2c2c;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease;
    font-weight: 500;
    text-align: center;
    justify-content: center;
}

.uvm-permission-item:hover {
    background: rgba(220, 20, 60, 0.05);
    color: #dc143c;
    border-color: rgba(220, 20, 60, 0.2);
    transform: scale(1.02);
}

.uvm-permission-item i {
    font-size: 0.75em;
    color: #28a745;
    transition: all 0.15s ease;
}

.uvm-permission-item:hover i {
    color: #dc143c;
}

/* Sin permisos */
.uvm-no-permissions {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 12px;
    padding: 30px;
    text-align: center;
    color: #856404;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    grid-column: 1 / -1;
}

.uvm-no-permissions i {
    font-size: 1.5em;
    color: #f39c12;
}

/* Botón de cerrar */
.user-view-modal-button {
    background: rgba(220, 20, 60, 0.15) !important;
    border: 2px solid #dc143c !important;
    color: #dc143c !important;
    padding: 12px 25px !important;
    font-weight: 600 !important;
    border-radius: 50px !important;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.user-view-modal-button:hover {
    background: #dc143c !important;
    color: white !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(220, 20, 60, 0.3) !important;
}

/* Animaciones más sutiles */
@keyframes uvmSlideInUp {
    from {
        opacity: 0;
        transform: translate3d(0, 20px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

@keyframes uvmSlideOutDown {
    from {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
    to {
        opacity: 0;
        transform: translate3d(0, 20px, 0);
    }
}

.uvm-animate-in {
    animation: uvmSlideInUp 0.3s ease-out;
}

.uvm-animate-out {
    animation: uvmSlideOutDown 0.2s ease-in;
}

/* Responsive */
@media (max-width: 1024px) {
    .uvm-permissions-container {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .uvm-module-permissions {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 768px) {
    .user-view-modal-popup {
        width: 95% !important;
        margin: 10px !important;
    }

    .user-view-modal-content {
        padding: 20px;
    }

    .uvm-info-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .uvm-title-section {
        flex-direction: column;
        gap: 10px;
    }

    .uvm-user-avatar {
        font-size: 2em;
    }

    .uvm-permissions-container {
        max-height: 300px;
        grid-template-columns: 1fr;
    }

    .uvm-module-permissions {
        padding: 15px;
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px) {
    .uvm-module-header {
        padding: 12px 15px;
        flex-wrap: wrap;
    }

    .uvm-permission-item {
        font-size: 0.8em;
        padding: 6px 10px;
    }

    .uvm-info-item {
        padding: 15px;
    }

    .uvm-module-permissions {
        grid-template-columns: 1fr;
    }
}
    </style>
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">E</div>
            <div class="header-title">
                <i class="fas fa-users-cog"></i>
                <h1>Gestión de Usuarios y Permisos</h1>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="container">
        <!-- Controls -->
        <div class="controls">
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Buscar usuario...">
                <i class="fas fa-search search-icon"></i>
            </div>
            <div class="action-buttons">
                <button class="btn btn-primary" onclick="openNewUserModal()">
                    <i class="fas fa-plus"></i>
                    Nuevo Usuario
                </button>
                <button class="btn btn-secondary">
                    <i class="fas fa-download"></i>
                    Exportar
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Permisos</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <!--Los datos se muetran mediante javascript-->

                </tbody>
            </table>
            <!-- Paginación -->
            <div class="pagination-container">
                <div class="pagination-info">
                    <div class="items-per-page">
                        <label for="itemsPerPage">Mostrar:</label>
                        <select id="itemsPerPage">
                            <option value="5">5 usuarios</option>
                            <option value="10" selected>10 usuarios</option>
                            <option value="25">25 usuarios</option>
                            <option value="50">50 usuarios</option>
                        </select>
                    </div>
                </div>

                <div class="pagination-nav">
                    <div class="page-info">
                        Mostrando <span id="startItem">1</span>-<span id="endItem">10</span> de <span
                            id="totalItems">50</span> usuarios
                    </div>
                    <div class="pagination-controls">
                        <button class="page-btn" id="firstPage" title="Primera página">
                            <i class="fas fa-angle-double-left"></i>
                        </button>
                        <button class="page-btn" id="prevPage" title="Página anterior">
                            <i class="fas fa-angle-left"></i>
                        </button>
                        <button class="page-btn active" id="currentPageBtn">1</button>
                        <button class="page-btn" id="nextPage" title="Página siguiente">
                            <i class="fas fa-angle-right"></i>
                        </button>
                        <button class="page-btn" id="lastPage" title="Última página">
                            <i class="fas fa-angle-double-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    </div>

    <!-- Modal Nuevo Usuario -->
    <div class="modal-overlay" id="newUserModal">
        <div class="modal">
            <div class="modal-header">
                <h3><i class="fas fa-user-plus"></i>Nuevo Usuario</h3>
                <button class="close-btn" onclick="closeNewUserModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="permissionsForm">
                    <!-- Sección de datos del usuario -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="section-title"><i class="fas fa-user me-2"></i>Datos del Usuario</h4>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="Ingrese el nombre completo" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Nombre de Usuario</label>
                            <input type="text" class="form-control" id="username" name="username"
                                placeholder="Ingrese el nombre de usuario" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="ejemplo@correo.com" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Ingrese una contraseña segura" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Estado</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="" selected disabled>Seleccione un estado</option>
                                <option value="active">Activo</option>
                                <option value="inactive">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <!-- Sección de permisos -->
                    <div class="row">
                        <div class="col-12">
                            <h4 class="section-title"><i class="fas fa-key me-2"></i>Configuración de Permisos</h4>
                            <p class="text-muted mb-4">Seleccione las áreas y módulos a los que el usuario tendrá
                                acceso. Haga clic en cada módulo para expandir sus opciones.</p>
                        </div>

                        <!-- Módulo Administración -->
                        <div class="col-12">
                            <div class="module-area">
                                <div class="module-header" onclick="toggleModule('administracion-body')">
                                    <h5 class="module-title"><i class="fas fa-cogs me-2"></i>Administración</h5>
                                    <label class="switch" onclick="event.stopPropagation()">
                                        <input type="checkbox" class="module-toggle" data-module="administracion">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="module-body" id="administracion-body">
                                    <div class="permission-item">
                                        <span>Configuración general</span>
                                        <label class="switch">
                                            <input type="checkbox" name="permissions[administracion][config]">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Módulo QHSE -->
                        <div class="col-12">
                            <div class="module-area">
                                <div class="module-header" onclick="toggleModule('qhse-body')">
                                    <h5 class="module-title"><i class="fas fa-shield-alt me-2"></i>QHSE</h5>
                                    <label class="switch" onclick="event.stopPropagation()">
                                        <input type="checkbox" class="module-toggle" data-module="qhse">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="module-body" id="qhse-body">
                                    <div class="permission-item">
                                        <span>VESCAP</span>
                                        <label class="switch">
                                            <input type="checkbox" name="permissions[qhse][vescap]">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    <div class="permission-item">
                                        <span>Incidencias</span>
                                        <label class="switch">
                                            <input type="checkbox" name="permissions[qhse][incidencias]">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    <div class="permission-item">
                                        <span>Auditorías</span>
                                        <label class="switch">
                                            <input type="checkbox" name="permissions[qhse][auditorias]">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Módulo Recursos Humanos -->
                        <div class="col-12">
                            <div class="module-area">
                                <div class="module-header" onclick="toggleModule('recursoshumanos-body')">
                                    <h5 class="module-title"><i class="fas fa-users me-2"></i>Recursos Humanos</h5>
                                    <label class="switch" onclick="event.stopPropagation()">
                                        <input type="checkbox" class="module-toggle" data-module="recursoshumanos">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="module-body" id="recursoshumanos-body">
                                    <div class="permission-item">
                                        <span>Altas de empleados</span>
                                        <label class="switch">
                                            <input type="checkbox"
                                                name="permissions[recursoshumanos][altasempleados]">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    <div class="permission-item">
                                        <span>L&O Chart</span>
                                        <label class="switch">
                                            <input type="checkbox" name="permissions[recursoshumanos][loandchart]">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Módulo Sistemas -->
                        <div class="col-12">
                            <div class="module-area">
                                <div class="module-header" onclick="toggleModule('sistemas-body')">
                                    <h5 class="module-title"><i class="fas fa-laptop-code me-2"></i>Sistemas</h5>
                                    <label class="switch" onclick="event.stopPropagation()">
                                        <input type="checkbox" class="module-toggle" data-module="sistemas">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="module-body" id="sistemas-body">
                                    <div class="permission-item">
                                        <span>Gestión de roles</span>
                                        <label class="switch">
                                            <input type="checkbox" name="permissions[sistemas][gestionderoles]">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Módulo Ventas -->
                        <div class="col-12">
                            <div class="module-area">
                                <div class="module-header" onclick="toggleModule('ventas-body')">
                                    <h5 class="module-title"><i class="fas fa-chart-line me-2"></i>Ventas</h5>
                                    <label class="switch" onclick="event.stopPropagation()">
                                        <input type="checkbox" class="module-toggle" data-module="ventas">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="module-body" id="ventas-body">
                                    <div class="permission-item">
                                        <span>Clientes</span>
                                        <label class="switch">
                                            <input type="checkbox" name="permissions[ventas][clientes]">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    <div class="permission-item">
                                        <span>Cotizaciones</span>
                                        <label class="switch">
                                            <input type="checkbox" name="permissions[ventas][cotizaciones]">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    <div class="permission-item">
                                        <span>Oportunidades</span>
                                        <label class="switch">
                                            <input type="checkbox" name="permissions[ventas][oportunidades]">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Módulo Suministro -->
                        <div class="col-12">
                            <div class="module-area">
                                <div class="module-header" onclick="toggleModule('suministro-body')">
                                    <h5 class="module-title"><i class="fas fa-truck me-2"></i>Suministro</h5>
                                    <label class="switch" onclick="event.stopPropagation()">
                                        <input type="checkbox" class="module-toggle" data-module="suministro">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="module-body" id="suministro-body">
                                    <div class="permission-item">
                                        <span>Pedidos</span>
                                        <label class="switch">
                                            <input type="checkbox" name="permissions[suministro][pedidos]">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Módulo Operaciones -->
                        <div class="col-12">
                            <div class="module-area">
                                <div class="module-header" onclick="toggleModule('operaciones-body')">
                                    <h5 class="module-title"><i class="fas fa-cog me-2"></i>Operaciones</h5>
                                    <label class="switch" onclick="event.stopPropagation()">
                                        <input type="checkbox" class="module-toggle" data-module="operaciones">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="module-body" id="operaciones-body">
                                    <div class="permission-item">
                                        <span>Procesos</span>
                                        <label class="switch">
                                            <input type="checkbox" name="permissions[operaciones][procesos]">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Módulo Almacén -->
                        <div class="col-12">
                            <div class="module-area">
                                <div class="module-header" onclick="toggleModule('almacen-body')">
                                    <h5 class="module-title"><i class="fas fa-warehouse me-2"></i>Almacén</h5>
                                    <label class="switch" onclick="event.stopPropagation()">
                                        <input type="checkbox" class="module-toggle" data-module="almacen">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="module-body" id="almacen-body">
                                    <div class="permission-item">
                                        <span>Inventario</span>
                                        <label class="switch">
                                            <input type="checkbox" name="permissions[almacen][inventario]">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Módulo Geociencias -->
                        <div class="col-12">
                            <div class="module-area">
                                <div class="module-header" onclick="toggleModule('geociencias-body')">
                                    <h5 class="module-title"><i class="fas fa-globe-americas me-2"></i>Geociencias
                                    </h5>
                                    <label class="switch" onclick="event.stopPropagation()">
                                        <input type="checkbox" class="module-toggle" data-module="geociencias">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="module-body" id="geociencias-body">
                                    <div class="permission-item">
                                        <span>Exploraciones</span>
                                        <label class="switch">
                                            <input type="checkbox" name="permissions[geociencias][exploraciones]">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    <div class="permission-item">
                                        <span>Análisis</span>
                                        <label class="switch">
                                            <input type="checkbox" name="permissions[geociencias][analisis]">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12 text-end">
                            <button type="button" onclick="closeNewUserModal()"
                                class="btn btn-secondary me-2">Cancelar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Guardar Permisos
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.12/sweetalert2.min.js"></script>
    <script>
        // Variables globales
        let users = [];
        let currentPage = 1;
        let itemsPerPage = 10;
        let totalPages = 1;
        let editingUserId = null; // Para saber si estamos editando

        // Función para mezclar array aleatoriamente (Fisher-Yates shuffle)
        function shuffleArray(array) {
            const shuffled = [...array]; // Crear una copia del array
            for (let i = shuffled.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
            }
            return shuffled;
        }

        // Función para cargar usuarios desde el backend
        async function loadUsers() {
            try {
                const response = await fetch('/sistemas/roles', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    }
                });

                if (!response.ok) {
                    throw new Error('Error al cargar los usuarios');
                }

                const data = await response.json();
                users = data.users || [];
                totalPages = Math.ceil(users.length / itemsPerPage);
                renderUsers();
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudieron cargar los usuarios',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#3498db'
                });
            }
        }

        // Función para obtener clase CSS de módulo
        function getModuleClass(module) {
            const classes = {
                "Administración": "administracion",
                "QHSE": "qhse",
                "Ventas": "ventas",
                "Recursos Humanos": "recursos-humanos",
                "Suministro": "suministro",
                "Operaciones": "operaciones",
                "sistemas": "sistemas",
                "Sistemas": "sistemas",
                "Almacen": "almacen",
                "Geociencias": "geociencias"
            };
            return classes[module] || "administracion";
        }

        // Función para formatear nombre de módulo
        function formatModuleName(module) {
            const moduleNames = {
                "administracion": "Administración",
                "qhse": "QHSE",
                "ventas": "Ventas",
                "recursoshumanos": "Recursos Humanos",
                "suministro": "Suministro",
                "operaciones": "Operaciones",
                "sistemas": "Sistemas",
                "almacen": "Almacen",
                "geociencias": "Geociencias"
            };

            return moduleNames[module.toLowerCase()] ||
                module.charAt(0).toUpperCase() + module.slice(1).toLowerCase();
        }

        // Función para obtener solo los módulos (para mostrar en tabla) - AHORA CON ORDEN ALEATORIO
        function getModulesFromPermissions(permissions) {
            if (!permissions || typeof permissions !== 'object') {
                return [];
            }

            const modules = Object.keys(permissions).map(module => formatModuleName(module));
            // Mezclar los módulos aleatoriamente antes de devolverlos
            return shuffleArray(modules);
        }

        // Función para formatear permisos detallados (para vista completa)
        function formatDetailedPermissions(permissions) {
            if (!permissions || typeof permissions !== 'object') {
                return [];
            }

            const detailedPermissions = [];

            for (const [module, modulePermissions] of Object.entries(permissions)) {
                const moduleName = formatModuleName(module);

                if (Array.isArray(modulePermissions) && modulePermissions.length > 0) {
                    // Si tiene permisos específicos, agregarlos
                    modulePermissions.forEach(permission => {
                        detailedPermissions.push({
                            module: moduleName,
                            permission: permission,
                            display: `${moduleName} - ${permission}`
                        });
                    });
                } else if (typeof modulePermissions === 'object' && Object.keys(modulePermissions).length > 0) {
                    // Si es un objeto con permisos específicos
                    Object.keys(modulePermissions).forEach(permission => {
                        detailedPermissions.push({
                            module: moduleName,
                            permission: permission,
                            display: `${moduleName} - ${permission}`
                        });
                    });
                } else {
                    // Si solo tiene acceso al módulo general
                    detailedPermissions.push({
                        module: moduleName,
                        permission: 'No tiene permisos detallados',
                        display: `${moduleName} - No tiene permisos detallados`
                    });
                }
            }

            // También aleatorizar los permisos detallados
            return shuffleArray(detailedPermissions);
        }

        function renderUsers() {
            const tbody = document.getElementById('userTableBody');
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const currentUsers = users.slice(startIndex, endIndex);

            tbody.innerHTML = currentUsers.map(user => {
                // Los módulos ya vienen mezclados aleatoriamente de getModulesFromPermissions
                const modules = getModulesFromPermissions(user.permissions?.permissions || {});
                const visibleModules = modules.slice(0, 4);
                const hiddenCount = modules.length - visibleModules.length;

                return `
        <tr data-user-id="${user.id}">
            <td>
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <h4>${user.name || 'N/A'}</h4>
                        <p>${user.username || 'N/A'}</p>
                    </div>
                </div>
            </td>
            <td>${user.email || 'N/A'}</td>
            <td>
                <div class="permissions">
                    ${visibleModules.map(module =>
                        `<span class="permission-tag ${getModuleClass(module)}">${module}</span>`
                    ).join('')}
                    ${hiddenCount > 0 ? `<span class="permission-tag more-permissions">+${hiddenCount} más</span>` : ''}
                    ${modules.length === 0 ? '<span class="permission-tag">Sin permisos</span>' : ''}
                </div>
            </td>
            <td>
                <span class="status ${user.status || 'inactive'}">
                    <span class="status-dot"></span>
                    ${user.status === 'active' ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>
                <div class="actions">
                    <button class="action-btn view" title="Ver" onclick="viewUser(${user.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="action-btn edit" title="Editar" onclick="editUser(${user.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete" title="Eliminar" onclick="deleteUser(${user.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
        `;
            }).join('');

            updatePaginationInfo();
        }

        function updatePaginationInfo() {
            const startItem = users.length === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1;
            const endItem = Math.min(currentPage * itemsPerPage, users.length);

            document.getElementById('startItem').textContent = startItem;
            document.getElementById('endItem').textContent = endItem;
            document.getElementById('totalItems').textContent = users.length;
            document.getElementById('currentPageBtn').textContent = currentPage;

            // Actualizar botones de navegación
            document.getElementById('firstPage').disabled = currentPage === 1;
            document.getElementById('prevPage').disabled = currentPage === 1;
            document.getElementById('nextPage').disabled = currentPage === totalPages;
            document.getElementById('lastPage').disabled = currentPage === totalPages;
        }

 function viewUser(userId) {
    const user = users.find(u => u.id === userId);
    if (!user) return;

    const detailedPermissions = formatDetailedPermissions(user.permissions?.permissions || {});

    let permissionsHtml = '';
    if (detailedPermissions.length > 0) {
        // Agrupar permisos por módulo para mejor visualización
        const groupedPermissions = {};
        detailedPermissions.forEach(perm => {
            if (!groupedPermissions[perm.module]) {
                groupedPermissions[perm.module] = [];
            }
            groupedPermissions[perm.module].push(perm.permission);
        });

        // Aleatorizar también el orden de los módulos agrupados
        const moduleEntries = shuffleArray(Object.entries(groupedPermissions));

        permissionsHtml = moduleEntries.map(([module, permissions]) => `
            <div class="uvm-module-block">
                <div class="uvm-module-header">
                    <i class="uvm-module-icon fas fa-folder"></i>
                    <span class="uvm-module-name ${getModuleClass(module)}">${module}</span>
                    <span class="uvm-permissions-count">${permissions.length}</span>
                </div>
                <div class="uvm-module-permissions">
                    ${shuffleArray(permissions).map(permission =>
                        `<span class="uvm-permission-item">
                            <i class="fas fa-check-circle"></i>
                            ${permission}
                        </span>`
                    ).join('')}
                </div>
            </div>
        `).join('');
    } else {
        permissionsHtml = `
            <div class="uvm-no-permissions">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Sin permisos asignados</span>
            </div>
        `;
    }

    // Determinar el icono de estado
    const statusIcon = user.status === 'active' ? 'fas fa-check-circle' : 'fas fa-times-circle';
    const statusText = user.status === 'active' ? 'Activo' : 'Inactivo';

    Swal.fire({
        title: `<div class="uvm-title-section">
                    <i class="fas fa-user-circle uvm-user-avatar"></i>
                    <span class="uvm-user-name">${user.name}</span>
                </div>`,
        html: `
        <div class="user-view-modal-content">
            <div class="uvm-info-grid">
                <div class="uvm-info-item">
                    <div class="uvm-info-label">
                        <i class="fas fa-user"></i>
                        Usuario
                    </div>
                    <div class="uvm-info-value">${user.username}</div>
                </div>

                <div class="uvm-info-item">
                    <div class="uvm-info-label">
                        <i class="fas fa-envelope"></i>
                        Email
                    </div>
                    <div class="uvm-info-value">${user.email}</div>
                </div>

                <div class="uvm-info-item">
                    <div class="uvm-info-label">
                        <i class="${statusIcon}"></i>
                        Estado
                    </div>
                    <div class="uvm-info-value">
                        <span class="uvm-status uvm-status-${user.status}">${statusText}</span>
                    </div>
                </div>

                <div class="uvm-info-item">
                    <div class="uvm-info-label">
                        <i class="fas fa-calendar-alt"></i>
                        Creado
                    </div>
                    <div class="uvm-info-value">${new Date(user.created_at).toLocaleString()}</div>
                </div>
            </div>

            <div class="uvm-permissions-section">
                <div class="uvm-section-header">
                    <i class="fas fa-shield-alt"></i>
                    <span>Permisos Detallados</span>
                </div>
                <div class="uvm-permissions-container">
                    ${permissionsHtml}
                </div>
            </div>
        </div>
        `,
        width: 1200,
        confirmButtonText: '<i class="fas fa-times"></i> Cerrar',
        confirmButtonColor: '#6c757d',
        customClass: {
            popup: 'user-view-modal-popup',
            title: 'user-view-modal-title',
            htmlContainer: 'user-view-modal-html',
            confirmButton: 'user-view-modal-button'
        },
        showClass: {
            popup: 'uvm-animate-in'
        },
        hideClass: {
            popup: 'uvm-animate-out'
        }
    });
}

        function editUser(userId) {
            const user = users.find(u => u.id === userId);
            if (!user) return;

            editingUserId = userId;

            // Cambiar el título del modal
            document.querySelector('#newUserModal .modal-header h3').innerHTML =
                '<i class="fas fa-user-edit"></i>Editar Usuario';

            // Llenar los campos del formulario
            document.getElementById('name').value = user.name || '';
            document.getElementById('username').value = user.username || '';
            document.getElementById('email').value = user.email || '';
            document.getElementById('password').value = ''; // Dejar vacío para no cambiar
            document.getElementById('password').placeholder = 'Dejar vacío para mantener contraseña actual';
            document.getElementById('status').value = user.status || 'inactive';

            // Limpiar permisos previos
            document.querySelectorAll('.module-toggle').forEach(toggle => {
                toggle.checked = false;
            });
            document.querySelectorAll('input[name^="permissions"]').forEach(input => {
                input.checked = false;
            });
            document.querySelectorAll('.module-body').forEach(body => {
                body.classList.remove('active');
            });

            // Cargar permisos del usuario
            if (user.permissions && user.permissions.permissions) {
                const userPermissions = user.permissions.permissions;

                for (const [module, modulePermissions] of Object.entries(userPermissions)) {
                    // Activar el toggle del módulo
                    const moduleToggle = document.querySelector(`.module-toggle[data-module="${module}"]`);
                    if (moduleToggle) {
                        moduleToggle.checked = true;
                        document.getElementById(`${module}-body`).classList.add('active');
                    }

                    // Activar permisos específicos
                    if (Array.isArray(modulePermissions)) {
                        modulePermissions.forEach(permission => {
                            const permissionInput = document.querySelector(
                                `input[name="permissions[${module}][${permission}]"]`);
                            if (permissionInput) {
                                permissionInput.checked = true;
                            }
                        });
                    } else if (typeof modulePermissions === 'object') {
                        Object.keys(modulePermissions).forEach(permission => {
                            const permissionInput = document.querySelector(
                                `input[name="permissions[${module}][${permission}]"]`);
                            if (permissionInput) {
                                permissionInput.checked = true;
                            }
                        });
                    }
                }
            }

            // Mostrar el modal
            document.getElementById('newUserModal').classList.add('show');
        }

        async function deleteUser(userId) {
            const result = await Swal.fire({
                title: '¿Eliminar usuario?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef476f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    popup: 'rounded-4'
                }
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/sistemas/roles/${userId}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        await Swal.fire({
                            title: '¡Eliminado!',
                            text: data.message || 'El usuario ha sido eliminado correctamente',
                            icon: 'success',
                            confirmButtonColor: '#4361ee',
                            customClass: {
                                popup: 'rounded-4'
                            }
                        });

                        // Recargar la lista de usuarios
                        await loadUsers();
                    } else {
                        throw new Error(data.message || 'Error al eliminar el usuario');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: error.message || 'No se pudo eliminar el usuario',
                        icon: 'error',
                        confirmButtonColor: '#3498db'
                    });
                }
            }
        }

        // Event listeners para paginación
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar usuarios al inicializar
            loadUsers();

            // Event listeners para paginación
            document.getElementById('itemsPerPage').addEventListener('change', function() {
                itemsPerPage = parseInt(this.value);
                totalPages = Math.ceil(users.length / itemsPerPage);
                currentPage = 1;
                renderUsers();
            });

            document.getElementById('firstPage').addEventListener('click', function() {
                currentPage = 1;
                renderUsers();
            });

            document.getElementById('prevPage').addEventListener('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    renderUsers();
                }
            });

            document.getElementById('nextPage').addEventListener('click', function() {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderUsers();
                }
            });

            document.getElementById('lastPage').addEventListener('click', function() {
                currentPage = totalPages;
                renderUsers();
            });

            // Funcionalidad de búsqueda mejorada
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();

                    if (searchTerm === '') {
                        currentPage = 1;
                        renderUsers();
                        return;
                    }

                    const filteredUsers = users.filter(user => {
                        const name = (user.name || '').toLowerCase();
                        const username = (user.username || '').toLowerCase();
                        const email = (user.email || '').toLowerCase();
                        const modules = getModulesFromPermissions(user.permissions?.permissions ||
                            {});
                        const modulesText = modules.join(' ').toLowerCase();
                        const status = user.status === 'active' ? 'activo' : 'inactivo';

                        return name.includes(searchTerm) ||
                            username.includes(searchTerm) ||
                            email.includes(searchTerm) ||
                            modulesText.includes(searchTerm) ||
                            status.includes(searchTerm);
                    });

                    // Renderizar usuarios filtrados
                    const tbody = document.getElementById('userTableBody');
                    const startIndex = (currentPage - 1) * itemsPerPage;
                    const endIndex = startIndex + itemsPerPage;
                    const currentUsers = filteredUsers.slice(startIndex, endIndex);

                    tbody.innerHTML = currentUsers.map(user => {
                        // Los módulos también se mezclan aleatoriamente en las búsquedas
                        const modules = getModulesFromPermissions(user.permissions?.permissions ||
                            {});
                        const visibleModules = modules.slice(0, 3);
                        const hiddenCount = modules.length - visibleModules.length;

                        return `
                <tr data-user-id="${user.id}">
                    <td>
                        <div class="user-info">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="user-details">
                                <h4>${user.name || 'N/A'}</h4>
                                <p>${user.username || 'N/A'}</p>
                            </div>
                        </div>
                    </td>
                    <td>${user.email || 'N/A'}</td>
                    <td>
                        <div class="permissions">
                            ${visibleModules.map(module =>
                                `<span class="permission-tag ${getModuleClass(module)}">${module}</span>`
                            ).join('')}
                            ${hiddenCount > 0 ? `<span class="permission-tag more-permissions">+${hiddenCount} más</span>` : ''}
                            ${modules.length === 0 ? '<span class="permission-tag">Sin permisos</span>' : ''}
                        </div>
                    </td>
                    <td>
                        <span class="status ${user.status || 'inactive'}">
                            <span class="status-dot"></span>
                            ${user.status === 'active' ? 'Activo' : 'Inactivo'}
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <button class="action-btn view" title="Ver" onclick="viewUser(${user.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="action-btn edit" title="Editar" onclick="editUser(${user.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete" title="Eliminar" onclick="deleteUser(${user.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
                    }).join('');

                    // Actualizar info de paginación para resultados filtrados
                    const startItem = filteredUsers.length === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1;
                    const endItem = Math.min(currentPage * itemsPerPage, filteredUsers.length);

                    document.getElementById('startItem').textContent = startItem;
                    document.getElementById('endItem').textContent = endItem;
                    document.getElementById('totalItems').textContent = filteredUsers.length;
                });
            }

            // Animación de placeholder tipo máquina de escribir
            if (searchInput) {
                const phrases = ["Buscar por usuario...", "Buscar por email...", "Buscar por módulos...",
                    "Buscar por estado..."
                ];
                let phraseIndex = 0;
                let charIndex = 0;
                let isDeleting = false;
                let isEnd = false;

                function typePlaceholder() {
                    const currentPhrase = phrases[phraseIndex];

                    if (isDeleting) {
                        searchInput.placeholder = currentPhrase.substring(0, charIndex - 1);
                        charIndex--;

                        if (charIndex === 0) {
                            isDeleting = false;
                            phraseIndex = (phraseIndex + 1) % phrases.length;
                        }
                    } else {
                        searchInput.placeholder = currentPhrase.substring(0, charIndex + 1);
                        charIndex++;

                        if (charIndex === currentPhrase.length) {
                            isEnd = true;
                            isDeleting = true;
                            setTimeout(typePlaceholder, 1500);
                            return;
                        }
                    }

                    const typingSpeed = isDeleting ? 50 : 100;
                    const randomSpeed = Math.random() * 50 + typingSpeed;
                    setTimeout(typePlaceholder, isEnd ? randomSpeed : typingSpeed);
                    isEnd = false;
                }

                setTimeout(typePlaceholder, 1000);
            }
        });


        // Funciones para modal
        function openNewUserModal() {
            editingUserId = null;

            document.querySelector('#newUserModal .modal-header h3').innerHTML =
                '<i class="fas fa-user-plus"></i>Nuevo Usuario';

            document.getElementById('permissionsForm').reset();
            document.getElementById('password').placeholder = 'Ingrese una contraseña segura';

            document.querySelectorAll('.module-toggle').forEach(toggle => {
                toggle.checked = false;
            });
            document.querySelectorAll('input[name^="permissions"]').forEach(input => {
                input.checked = false;
            });
            document.querySelectorAll('.module-body').forEach(body => {
                body.classList.remove('active');
            });

            document.getElementById('newUserModal').classList.add('show');
        }

        function closeNewUserModal() {
            const modal = document.getElementById('newUserModal');
            if (modal) {
                modal.classList.remove('show');
                editingUserId = null;
            }
        }

        function toggleModule(moduleBodyId) {
            const moduleBody = document.getElementById(moduleBodyId);
            if (moduleBody) {
                moduleBody.classList.toggle('active');
            }
        }

        // Manejar formulario de creación/edición
        if (typeof $ !== 'undefined') {
            $(document).ready(function() {
                $('.module-toggle').on('change', function() {
                    const module = $(this).data('module');
                    const isChecked = $(this).prop('checked');

                    if (isChecked) {
                        $(`#${module}-body`).addClass('active');
                    } else {
                        $(`input[name^="permissions[${module}]"]`).prop('checked', false);
                        $(`#${module}-body`).removeClass('active');
                    }
                });

                $('input[name^="permissions"]').on('change', function() {
                    const moduleKey = $(this).attr('name').split('[')[1].split(']')[0];
                    const checkedModulePermissions = $(`input[name^="permissions[${moduleKey}]"]:checked`);

                    if (checkedModulePermissions.length > 0) {
                        $(`.module-toggle[data-module="${moduleKey}"]`).prop('checked', true);
                        $(`#${moduleKey}-body`).addClass('active');
                    }
                });

                $('#permissionsForm').on('submit', function(e) {
                    e.preventDefault();

                    let isValid = true;
                    const requiredFields = ['name', 'username', 'email', 'status'];

                    if (!editingUserId) {
                        requiredFields.push('password');
                    }

                    for (const field of requiredFields) {
                        if (!$(`#${field}`).val().trim()) {
                            $(`#${field}`).addClass('is-invalid');
                            isValid = false;
                        } else {
                            $(`#${field}`).removeClass('is-invalid');
                        }
                    }

                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test($('#email').val())) {
                        $('#email').addClass('is-invalid');
                        isValid = false;
                    }

                    const statusValue = $('#status').val();
                    if (statusValue && !['active', 'inactive'].includes(statusValue)) {
                        $('#status').addClass('is-invalid');
                        isValid = false;
                    }

                    if (!isValid) {
                        Swal.fire({
                            title: 'Error de validación',
                            text: 'Por favor, complete todos los campos requeridos correctamente',
                            icon: 'error',
                            confirmButtonText: 'Aceptar',
                            confirmButtonColor: '#3498db'
                        });
                        return;
                    }

                    const formData = {
                        name: $('#name').val(),
                        username: $('#username').val(),
                        email: $('#email').val(),
                        status: $('#status').val(),
                        permissions: {}
                    };

                    const passwordValue = $('#password').val();
                    if (passwordValue.trim()) {
                        formData.password = passwordValue;
                    }

                    $('.module-toggle:checked').each(function() {
                        const module = $(this).data('module');
                        formData.permissions[module] = {};

                        $(`input[name^="permissions[${module}]"]:checked`).each(function() {
                            const nameAttr = $(this).attr('name');
                            const match = nameAttr.match(/permissions\[(.*?)\]\[(.*?)\]/);
                            if (match) {
                                const permission = match[2];
                                formData.permissions[module][permission] = true;
                            }
                        });
                    });

                    Swal.fire({
                        title: editingUserId ? 'Actualizando...' : 'Guardando...',
                        text: 'Espere mientras se procesan los datos',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const url = editingUserId ? `/sistemas/roles/${editingUserId}` : '/sistemas/roles';
                    const method = editingUserId ? 'PUT' : 'POST';

                    $.ajax({
                        url: url,
                        type: method,
                        data: JSON.stringify(formData),
                        contentType: 'application/json',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.close();

                            if (response.success) {
                                Swal.fire({
                                    title: '¡Éxito!',
                                    text: response.message || (editingUserId ?
                                        'Usuario actualizado correctamente' :
                                        'Usuario creado correctamente'),
                                    icon: 'success',
                                    confirmButtonText: 'Aceptar',
                                    confirmButtonColor: '#3498db'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        closeNewUserModal();
                                        loadUsers();
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: response.message ||
                                        'Hubo un error al guardar los datos',
                                    icon: 'error',
                                    confirmButtonText: 'Aceptar',
                                    confirmButtonColor: '#3498db'
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.close();
                            let errorMessage = 'Hubo un error al procesar la solicitud';

                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }

                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                const errors = xhr.responseJSON.errors;
                                let errorList = '<ul class="text-start">';

                                for (const field in errors) {
                                    $(`#${field}`).addClass('is-invalid');
                                    errorList += `<li>${errors[field][0]}</li>`;
                                }

                                errorList += '</ul>';

                                Swal.fire({
                                    title: 'Error de validación',
                                    html: errorList,
                                    icon: 'error',
                                    confirmButtonText: 'Aceptar',
                                    confirmButtonColor: '#3498db'
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: errorMessage,
                                    icon: 'error',
                                    confirmButtonText: 'Aceptar',
                                    confirmButtonColor: '#3498db'
                                });
                            }
                        }
                    });
                });
            });
        }
    </script>

</body>

</html>
