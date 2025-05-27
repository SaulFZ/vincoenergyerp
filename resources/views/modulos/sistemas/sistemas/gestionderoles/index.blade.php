<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestión de Roles y Permisos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.12/sweetalert2.min.css">
    <style>
        :root {
            /* Colores principales */
            --primary-color: #4361ee;
            --primary-hover: #3a56d4;
            --primary-light: rgba(67, 97, 238, 0.1);
            --secondary-color: #2b2d42;
            --accent-color: #48bfe3;

            /* Colores de estado */
            --success-color: #06d6a0;
            --success-light: rgba(6, 214, 160, 0.1);
            --warning-color: #ffd166;
            --warning-light: rgba(255, 209, 102, 0.1);
            --danger-color: #ef476f;
            --danger-light: rgba(239, 71, 111, 0.1);

            /* Colores de fondo */
            --light-bg: #ffffff;
            --dark-bg: #1a1c23;
            --light-gray: #f8fafc;
            --medium-gray: #f1f5f9;

            /* Colores de texto */
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;

            /* Bordes */
            --border-color: #e2e8f0;
            --border-radius-sm: 8px;
            --border-radius-md: 12px;
            --border-radius-lg: 16px;

            /* Sombras */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);

            /* Transiciones */
            --transition-fast: 0.15s ease-in-out;
            --transition-medium: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 0.5s ease;
        }

        /* Reset y estilos base */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--light-gray) 0%, var(--medium-gray) 100%);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
            font-size: 0.875rem;
            /* 14px base */
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            line-height: 1.2;
            font-weight: 600;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        img {
            max-width: 100%;
            height: auto;
        }

        /* Layout */
        .main-container {
            min-height: 100vh;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .container-fluid {
            padding: 1.5rem;
            max-width: 100%;
            width: 100%;
        }

        /* Header */
        .page-header {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #3d4257 100%);
            color: var(--light-bg);
            padding: 2rem 0;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            z-index: 1;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s var(--transition-slow);
            z-index: -1;
        }

        .page-header:hover::before {
            transform: translateX(100%);
        }

        .page-header h1 {
            font-size: clamp(1.5rem, 4vw, 2rem);
            margin: 0;
            display: flex;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .page-header h1 i {
            margin-right: 1rem;
            background: rgba(255, 255, 255, 0.15);
            width: clamp(50px, 8vw, 60px);
            height: clamp(50px, 8vw, 60px);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--border-radius-md);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
            font-size: clamp(1.2rem, 3vw, 1.5rem);
        }

        /* Search and Actions */
        .search-actions-container {
            background: var(--light-bg);
            padding: 1.5rem;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
            transition: transform var(--transition-medium), box-shadow var(--transition-medium);
        }

        .search-actions-container:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .search-actions-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color), var(--success-color));
        }

        .search-input {
            border-radius: var(--border-radius-md);
            padding: 0.875rem 1rem 0.875rem 3rem;
            border: 2px solid var(--border-color);
            background: var(--light-gray);
            transition: all var(--transition-medium);
            font-size: 0.9rem;
            font-weight: 500;
            width: 100%;
        }

        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px var(--primary-light);
            background: var(--light-bg);
            outline: none;
        }

        .search-input::placeholder {
            color: var(--text-muted);
            opacity: 0.8;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            z-index: 2;
        }

        /* Table Styles */
        .table-wrapper {
            background: var(--light-bg);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            border: 1px solid var(--border-color);
            margin: 0;
            transition: all var(--transition-medium);
        }

        .table-wrapper:hover {
            box-shadow: var(--shadow-lg);
        }

        .table {
            margin: 0;
            font-size: 0.9rem;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table thead th {
            background: linear-gradient(135deg, var(--light-gray) 0%, var(--medium-gray) 100%);
            color: var(--text-primary);
            font-weight: 700;
            padding: 1.25rem 1.5rem;
            border: none;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            position: relative;
            white-space: nowrap;
            vertical-align: middle;
        }

        .table thead th:first-child {
            padding-left: 2rem;
            border-top-left-radius: var(--border-radius-lg);
        }

        .table thead th:last-child {
            padding-right: 2rem;
            border-top-right-radius: var(--border-radius-lg);
        }

        .table thead th::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }

        .table tbody td {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            transition: all var(--transition-fast);
        }

        .table tbody td:first-child {
            padding-left: 2rem;
        }

        .table tbody td:last-child {
            padding-right: 2rem;
        }

        .table tbody tr {
            transition: all var(--transition-medium);
        }

        .table tbody tr:not(:last-child) td {
            border-bottom: 1px solid var(--border-color);
        }

        .table tbody tr:hover {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.03), rgba(72, 191, 227, 0.03));
            transform: translateX(4px);
            box-shadow: inset 4px 0 0 var(--primary-color);
        }

        /* User Info */
        .user-info {
            display: flex;
            align-items: center;
            min-width: 200px;
        }

        .user-avatar {
            width: 48px;
            height: 48px;
            border-radius: var(--border-radius-md);
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--light-bg);
            font-weight: 700;
            font-size: 1rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .user-avatar::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color), var(--success-color));
            border-radius: calc(var(--border-radius-md) + 2px);
            z-index: -1;
        }

        .user-details h6 {
            margin: 0 0 0.25rem 0;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.95rem;
            line-height: 1.3;
        }

        .user-details small {
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.8rem;
            display: block;
        }

        /* Permission Badges */
        .permissions-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            max-width: 250px;
        }

        .permission-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.8rem;
            background: var(--primary-light);
            color: var(--primary-color);
            border-radius: var(--border-radius-sm);
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid rgba(67, 97, 238, 0.2);
            transition: all var(--transition-fast);
            white-space: nowrap;
            cursor: default;
        }

        .permission-badge:hover {
            background: var(--primary-color);
            color: var(--light-bg);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.3);
        }

        .permission-badge.more {
            background: var(--text-muted);
            color: var(--light-bg);
            border-color: var(--text-muted);
            cursor: pointer;
        }

        /* Status */
        .status-badge {
            display: inline-flex;
            align-items: center;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
        }

        .status-active {
            background-color: rgba(6, 214, 160, 0.1);
            color: var(--success-color);
        }

        .status-active i {
            margin-right: 0.5rem;
            animation: pulse 2s infinite;
        }

        .status-inactive {
            background-color: rgba(239, 71, 111, 0.1);
            color: var(--danger-color);
        }

        .status-inactive i {
            margin-right: 0.5rem;
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

        /* Button Styles */
        .btn {
            border-radius: var(--border-radius-md);
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all var(--transition-medium);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            text-decoration: none;
            border: none;
            cursor: pointer;
            user-select: none;
            vertical-align: middle;
            white-space: nowrap;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left var(--transition-slow);
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:focus {
            outline: none;
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #05c896);
            color: var(--light-bg);
            box-shadow: 0 4px 15px rgba(6, 214, 160, 0.4);
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #05c896, var(--success-color));
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(6, 214, 160, 0.4);
            color: var(--light-bg);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: var(--light-bg);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.3);
        }

        .btn-outline-danger {
            border: 2px solid var(--danger-color);
            color: var(--danger-color);
            background: transparent;
        }

        .btn-outline-danger:hover {
            background: var(--danger-color);
            color: var(--light-bg);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 71, 111, 0.3);
        }

        .btn i {
            margin-right: 0.5rem;
            font-size: 0.9em;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
            border-radius: var(--border-radius-sm);
        }

        .btn-sm i {
            margin-right: 0.25rem;
            font-size: 0.8em;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Form Container */
        .form-container {
            display: none;
            animation: fadeIn 0.4s ease-out;
        }

        .form-container.show {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-container .card {
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .form-container .card-header {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #3d4257 100%);
            color: var(--light-bg);
            border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0 !important;
            padding: 1.75rem 2rem;
            font-weight: 600;
            border-bottom: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-container .card-header h3 {
            font-size: 1.5rem;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .form-container .card-header h3 i {
            margin-right: 0.75rem;
        }

        .btn-light {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: var(--light-bg);
            backdrop-filter: blur(10px);
        }

        .btn-light:hover {
            background: rgba(255, 255, 255, 0.3);
            color: var(--light-bg);
            transform: translateY(-1px);
        }

        /* Form Elements */
        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control {
            border-radius: var(--border-radius-sm);
            padding: 0.75rem 1rem;
            border: 2px solid var(--border-color);
            background: var(--light-gray);
            transition: all var(--transition-medium);
            font-size: 0.9rem;
            width: 100%;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px var(--primary-light);
            background: var(--light-bg);
            outline: none;
        }

        .form-control.is-invalid {
            border-color: var(--danger-color);
            background-color: rgba(239, 71, 111, 0.05);
        }

        .section-title {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .section-title i {
            margin-right: 0.75rem;
            color: var(--primary-color);
        }

        /* Module Permissions */
        .module-area {
            margin-bottom: 1.5rem;
            border-radius: var(--border-radius-md);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .module-header {
            padding: 1rem 1.5rem;
            background: var(--light-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .module-header:hover {
            background: var(--medium-gray);
        }

        .module-title {
            margin: 0;
            font-size: 1rem;
            display: flex;
            align-items: center;
        }

        .module-title i {
            margin-right: 0.75rem;
            color: var(--primary-color);
        }

        .module-body {
            padding: 1rem 1.5rem;
            background: var(--light-bg);
            display: none;
        }

        .module-body.active {
            display: block;
            animation: fadeIn 0.3s ease-out;
        }

        .permission-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .permission-item:last-child {
            border-bottom: none;
        }

        /* Switch Toggle */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
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
            background-color: #ccc;
            transition: all var(--transition-fast);
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: '';
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: all var(--transition-fast);
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: var(--primary-color);
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .container-fluid {
                padding: 1.25rem;
            }

            .user-info {
                min-width: auto;
            }

            .permissions-container {
                max-width: 180px;
            }
        }

        @media (max-width: 768px) {
            .container-fluid {
                padding: 1rem;
            }

            .page-header {
                padding: 1.5rem 0;
            }

            .table thead th,
            .table tbody td {
                padding: 1rem;
            }

            .table thead th:first-child,
            .table tbody td:first-child {
                padding-left: 1.25rem;
            }

            .table thead th:last-child,
            .table tbody td:last-child {
                padding-right: 1.25rem;
            }

            .user-avatar {
                width: 40px;
                height: 40px;
                margin-right: 0.75rem;
            }

            .permissions-container {
                max-width: 150px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 0.5rem;
            }

            .btn-sm {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .search-actions-container .row {
                flex-direction: column;
                gap: 1rem;
            }

            .search-actions-container .col-md-6 {
                width: 100%;
            }

            .form-container .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                padding: 1.25rem;
            }

            .form-container .card-header h3 {
                font-size: 1.25rem;
            }

            .module-header {
                padding: 0.75rem 1rem;
            }

            .module-body {
                padding: 0.75rem 1rem;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.5s ease-out forwards;
        }

        /* Utility Classes */
        .text-muted {
            color: var(--text-muted) !important;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .text-success {
            color: var(--success-color) !important;
        }

        .text-danger {
            color: var(--danger-color) !important;
        }

        .bg-light {
            background-color: var(--light-gray) !important;
        }

        .rounded-sm {
            border-radius: var(--border-radius-sm) !important;
        }

        .rounded-md {
            border-radius: var(--border-radius-md) !important;
        }

        .rounded-lg {
            border-radius: var(--border-radius-lg) !important;
        }

        .shadow-sm {
            box-shadow: var(--shadow-sm) !important;
        }

        .shadow-md {
            box-shadow: var(--shadow-md) !important;
        }

        .shadow-lg {
            box-shadow: var(--shadow-lg) !important;
        }

        .mt-1 {
            margin-top: 0.25rem !important;
        }

        .mt-2 {
            margin-top: 0.5rem !important;
        }

        .mt-3 {
            margin-top: 1rem !important;
        }

        .mb-1 {
            margin-bottom: 0.25rem !important;
        }

        .mb-2 {
            margin-bottom: 0.5rem !important;
        }

        .mb-3 {
            margin-bottom: 1rem !important;
        }

        .p-2 {
            padding: 0.5rem !important;
        }

        .p-3 {
            padding: 1rem !important;
        }

        .d-flex {
            display: flex !important;
        }

        .align-items-center {
            align-items: center !important;
        }

        .justify-content-between {
            justify-content: space-between !important;
        }

        .w-100 {
            width: 100% !important;
        }
    </style>
</head>

<body>
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="container-fluid">
                <h1><i class="fas fa-users-cog"></i>Gestión de Usuarios y Permisos</h1>
            </div>
        </div>

        <div class="container-fluid">
            <!-- Search and Actions -->
            <div class="search-actions-container">
                <div class="row align-items-center g-3">
                    <div class="col-lg-8 col-md-6">
                        <div class="position-relative">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="form-control search-input" id="searchUsers"
                                placeholder="Buscar usuarios por nombre, email o permisos...">
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 text-md-end">
                        <button id="btn-new-user" class="btn btn-success me-2">
                            <i class="fas fa-plus"></i>Nuevo Usuario
                        </button>
                        <button class="btn btn-outline-primary">
                            <i class="fas fa-download"></i>Exportar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="table-wrapper" id="users-table-container">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Permisos</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body">
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">JP</div>
                                        <div class="user-details">
                                            <h6>Juan Pérez</h6>
                                            <small>@juan.perez</small>
                                        </div>
                                    </div>
                                </td>
                                <td>juan.perez@empresa.com</td>
                                <td>
                                    <div class="permissions-container">
                                        <span class="permission-badge">Administración</span>
                                        <span class="permission-badge">QHSE</span>
                                        <span class="permission-badge more">+3 más</span>
                                    </div>
                                </td>
                                <td><span class="status-active"><i class="fas fa-circle"></i>Activo</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-outline-primary btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">MG</div>
                                        <div class="user-details">
                                            <h6>María González</h6>
                                            <small>@maria.gonzalez</small>
                                        </div>
                                    </div>
                                </td>
                                <td>maria.gonzalez@empresa.com</td>
                                <td>
                                    <div class="permissions-container">
                                        <span class="permission-badge">Recursos Humanos</span>
                                        <span class="permission-badge">Ventas</span>
                                    </div>
                                </td>
                                <td><span class="status-active"><i class="fas fa-circle"></i>Activo</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-outline-primary btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">CR</div>
                                        <div class="user-details">
                                            <h6>Carlos Rodríguez</h6>
                                            <small>@carlos.rodriguez</small>
                                        </div>
                                    </div>
                                </td>
                                <td>carlos.rodriguez@empresa.com</td>
                                <td>
                                    <div class="permissions-container">
                                        <span class="permission-badge">Sistemas</span>
                                        <span class="permission-badge">Operaciones</span>
                                        <span class="permission-badge">Almacén</span>
                                    </div>
                                </td>
                                <td><span class="status-inactive"><i class="fas fa-circle"></i>Inactivo</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-outline-primary btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Form Container (Hidden by default) -->
            <div class="form-container" id="user-form-container">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-user-plus me-2"></i>Nuevo Usuario</h3>
                        <button id="btn-close-form" class="btn btn-light btn-sm">
                            <i class="fas fa-times"></i>Cerrar
                        </button>
                    </div>
                    <div class="card-body">
                        <form id="permissionsForm">
                            <!-- Sección de datos del usuario -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h4 class="section-title"><i class="fas fa-user me-2"></i>Datos del Usuario</h4>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nombre Completo</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Nombre de Usuario</label>
                                    <input type="text" class="form-control" id="username" name="username"
                                        required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Correo Electrónico</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" id="password" name="password"
                                        required>
                                </div>
                            </div>

                            <!-- Sección de permisos -->
                            <div class="row">
                                <div class="col-12">
                                    <h4 class="section-title"><i class="fas fa-key me-2"></i>Configuración de Permisos
                                    </h4>
                                    <p class="text-muted mb-4">Seleccione las áreas y módulos a los que el usuario
                                        tendrá acceso.</p>
                                </div>

                                <!-- Módulo Administración -->
                                <div class="col-12">
                                    <div class="module-area">
                                        <div class="module-header" data-target="administracion-body">
                                            <h5 class="module-title"><i class="fas fa-cogs me-2"></i>Administración
                                            </h5>
                                            <label class="switch">
                                                <input type="checkbox" class="module-toggle"
                                                    data-module="administracion">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                        <div class="module-body" id="administracion-body">
                                            <!-- No se incluyen subsistemas para Administración -->
                                        </div>
                                    </div>
                                </div>

                                <!-- Módulo QHSE -->
                                <div class="col-12">
                                    <div class="module-area">
                                        <div class="module-header" data-target="qhse-body">
                                            <h5 class="module-title"><i class="fas fa-shield-alt me-2"></i>QHSE</h5>
                                            <label class="switch">
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
                                        <div class="module-header" data-target="recursoshumanos-body">
                                            <h5 class="module-title"><i class="fas fa-users me-2"></i>Recursos Humanos
                                            </h5>
                                            <label class="switch">
                                                <input type="checkbox" class="module-toggle"
                                                    data-module="recursoshumanos">
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
                                                    <input type="checkbox"
                                                        name="permissions[recursoshumanos][loandchart]">
                                                    <span class="slider"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Módulo Sistemas -->
                                <div class="col-12">
                                    <div class="module-area">
                                        <div class="module-header" data-target="sistemas-body">
                                            <h5 class="module-title"><i class="fas fa-laptop-code me-2"></i>Sistemas
                                            </h5>
                                            <label class="switch">
                                                <input type="checkbox" class="module-toggle" data-module="sistemas">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                        <div class="module-body" id="sistemas-body">
                                            <div class="permission-item">
                                                <span>Gestión de roles</span>
                                                <label class="switch">
                                                    <input type="checkbox"
                                                        name="permissions[sistemas][gestionderoles]">
                                                    <span class="slider"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Módulo Ventas -->
                                <div class="col-12">
                                    <div class="module-area">
                                        <div class="module-header" data-target="ventas-body">
                                            <h5 class="module-title"><i class="fas fa-chart-line me-2"></i>Ventas</h5>
                                            <label class="switch">
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
                                        <div class="module-header" data-target="suministro-body">
                                            <h5 class="module-title"><i class="fas fa-truck me-2"></i>Suministro</h5>
                                            <label class="switch">
                                                <input type="checkbox" class="module-toggle"
                                                    data-module="suministro">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                        <div class="module-body" id="suministro-body">
                                            <!-- Subsistemas de Suministro -->
                                        </div>
                                    </div>
                                </div>

                                <!-- Módulo Operaciones -->
                                <div class="col-12">
                                    <div class="module-area">
                                        <div class="module-header" data-target="operaciones-body">
                                            <h5 class="module-title"><i class="fas fa-cog me-2"></i>Operaciones</h5>
                                            <label class="switch">
                                                <input type="checkbox" class="module-toggle"
                                                    data-module="operaciones">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                        <div class="module-body" id="operaciones-body">
                                            <!-- Subsistemas de Operaciones -->
                                        </div>
                                    </div>
                                </div>

                                <!-- Módulo Almacén -->
                                <div class="col-12">
                                    <div class="module-area">
                                        <div class="module-header" data-target="almacen-body">
                                            <h5 class="module-title"><i class="fas fa-warehouse me-2"></i>Almacén</h5>
                                            <label class="switch">
                                                <input type="checkbox" class="module-toggle" data-module="almacen">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                        <div class="module-body" id="almacen-body">
                                            <!-- Subsistemas de Almacén -->
                                        </div>
                                    </div>
                                </div>

                                <!-- Módulo Geociencias -->
                                <div class="col-12">
                                    <div class="module-area">
                                        <div class="module-header" data-target="geociencias-body">
                                            <h5 class="module-title"><i
                                                    class="fas fa-globe-americas me-2"></i>Geociencias</h5>
                                            <label class="switch">
                                                <input type="checkbox" class="module-toggle"
                                                    data-module="geociencias">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                        <div class="module-body" id="geociencias-body">
                                            <div class="permission-item">
                                                <span>Exploraciones</span>
                                                <label class="switch">
                                                    <input type="checkbox"
                                                        name="permissions[geociencias][exploraciones]">
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
                                    <button type="button" id="btn-cancel"
                                        class="btn btn-secondary me-2">Cancelar</button>
                                    <button type="submit" id="btn-save" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Guardar Permisos
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.12/sweetalert2.min.js"></script>
    <script>
        // JavaScript actualizado para el formulario
        $(document).ready(function() {
            // Manejar toggle de módulos completos
            $('.module-toggle').on('change', function() {
                const module = $(this).data('module');
                const isChecked = $(this).prop('checked');

                // Si el módulo está activado, mostrar su contenido
                if (isChecked) {
                    $(`#${module}-body`).addClass('active');
                } else {
                    // Si se desactiva el módulo, desmarcar todos los permisos y ocultar el cuerpo
                    $(`input[name^="permissions[${module}]"]`).prop('checked', false);
                    $(`#${module}-body`).removeClass('active');
                }
            });

            // Manejar cambios en los permisos individuales
            $('input[name^="permissions"]').on('change', function() {
                const moduleKey = $(this).attr('name').split('[')[1].split(']')[0];
                const allModulePermissions = $(`input[name^="permissions[${moduleKey}]"]`);
                const checkedModulePermissions = $(`input[name^="permissions[${moduleKey}]"]:checked`);

                // Si hay algún permiso marcado, asegurarnos que el toggle del módulo esté activado
                if (checkedModulePermissions.length > 0) {
                    $(`.module-toggle[data-module="${moduleKey}"]`).prop('checked', true);
                    $(`#${moduleKey}-body`).addClass('active');
                }
            });

            // Manejar el envío del formulario
            $('#permissionsForm').on('submit', function(e) {
                e.preventDefault();

                // Validar el formulario primero
                let isValid = true;
                const requiredFields = ['name', 'username', 'email', 'password'];

                // Verificar campos requeridos
                for (const field of requiredFields) {
                    if (!$(`#${field}`).val().trim()) {
                        $(`#${field}`).addClass('is-invalid');
                        isValid = false;
                    } else {
                        $(`#${field}`).removeClass('is-invalid');
                    }
                }

                // Validar formato de email
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test($('#email').val())) {
                    $('#email').addClass('is-invalid');
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

                // Recopilar datos del formulario
                const formData = {
                    name: $('#name').val(),
                    username: $('#username').val(),
                    email: $('#email').val(),
                    password: $('#password').val(),
                    permissions: {}
                };

                // Recorre los toggles marcados (módulos activos)
                $('.module-toggle:checked').each(function() {
                    const module = $(this).data('module');
                    formData.permissions[module] = {}; // Siempre agregar aunque esté vacío

                    // Buscar los permisos marcados dentro del módulo
                    $(`input[name^="permissions[${module}]"]:checked`).each(function() {
                        const nameAttr = $(this).attr('name');
                        const match = nameAttr.match(/permissions\[(.*?)\]\[(.*?)\]/);
                        if (match) {
                            const permission = match[2];
                            formData.permissions[module][permission] = true;
                        }
                    });
                });


                // Mostrar loader durante el proceso
                Swal.fire({
                    title: 'Guardando...',
                    text: 'Espere mientras se guardan los datos',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Registrar para depuración
                console.log('Enviando permisos:', formData);

                // Enviar datos al servidor
                $.ajax({
                    url: '/sistemas/roles', // Esta ruta debe coincidir con la definida en web.php
                    type: 'POST',
                    data: JSON.stringify(formData),
                    contentType: 'application/json', // Importante
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.close();

                        if (response.success) {
                            Swal.fire({
                                title: '¡Éxito!',
                                text: response.message ||
                                    'Los permisos han sido guardados correctamente',
                                icon: 'success',
                                confirmButtonText: 'Aceptar',
                                confirmButtonColor: '#3498db'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = '/sistemas/roles';
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

                        // Si hay errores de validación
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

            // Mostrar formulario de nuevo usuario
            $('#btn-new-user').on('click', function() {
                $('#users-table-container').fadeOut(300, function() {
                    $('#user-form-container').addClass('show').fadeIn(400);
                });
            });

            // Cerrar formulario
            $('#btn-close-form').on('click', function() {
                $('#user-form-container').fadeOut(300, function() {
                    $(this).removeClass('show');
                    $('#users-table-container').fadeIn(400);
                });
            });

            // Búsqueda en tiempo real
            $('#searchUsers').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('#users-table-body tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });

            // Eliminar usuario
            $(document).on('click', '.btn-outline-danger', function() {
                Swal.fire({
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
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Aquí iría la lógica para eliminar el usuario
                        $(this).closest('tr').fadeOut(400, function() {
                            $(this).remove();
                        });

                        Swal.fire({
                            title: '¡Eliminado!',
                            text: 'El usuario ha sido eliminado correctamente',
                            icon: 'success',
                            confirmButtonColor: '#4361ee',
                            customClass: {
                                popup: 'rounded-4'
                            }
                        });
                    }
                });
            });

            // Efecto hover para las filas
            $('.table tbody tr').hover(
                function() {
                    $(this).addClass('table-row-hover');
                },
                function() {
                    $(this).removeClass('table-row-hover');
                }
            );

            // Animación de entrada para elementos
            function animateElements() {
                $('.search-actions-container, .table-wrapper').each(function(index) {
                    $(this).css({
                        'opacity': '0',
                        'transform': 'translateY(20px)'
                    }).delay(index * 100).animate({
                        'opacity': '1'
                    }, 600).css('transform', 'translateY(0)');
                });
            }

            // Inicializar animaciones
            animateElements();

            // Tooltip para botones de acción
            $('[title]').tooltip({
                placement: 'top',
                trigger: 'hover'
            });
        });
    </script>
</body>

</html>
