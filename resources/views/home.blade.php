<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP Vinco Energy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --naranja: #D67E29;
            --naranja-claro: #FF9D42;
            --naranja-oscuro: #B96520;
            --azul: #334c95;
            --azul-oscuro: #1A2A5F;
            --azul-claro: #4B6BC6;
            --blanco: #FFFFFF;
            --negro: #1E293B;
            --gris-claro: #F8FAFC;
            --gris-medio: #E2E8F0;
            --gris-oscuro: #64748B;
            --sombra: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --sombra-intensa: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --sombra-suave: 0 2px 4px rgba(0, 0, 0, 0.05);
            --transicion: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transicion-rapida: all 0.2s ease;
            --border-radius: 12px;
            --border-radius-sm: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, var(--gris-claro) 0%, #f1f5f9 100%);
            color: var(--negro);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Header Moderno y Mejorado */
        .header {
            background: linear-gradient(135deg, var(--azul-oscuro) 0%, var(--azul) 50%, var(--azul-claro) 100%);
            height: 75px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            position: fixed;
            width: 100%;
            z-index: 1000;
            color: var(--blanco);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .menu-toggle {
            display: none;
            width: 45px;
            height: 45px;
            border-radius: var(--border-radius-sm);
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: var(--blanco);
            cursor: pointer;
            transition: var(--transicion);
            justify-content: center;
            align-items: center;
            font-size: 1.2rem;
        }

        .menu-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }

        .logo {
            width: 85px;
            height: 60px;
            background: var(--blanco);
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: var(--sombra);
            transition: var(--transicion);
        }

        .logo:hover {
            transform: scale(1.05);
            box-shadow: var(--sombra-intensa);
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .company-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--blanco);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            letter-spacing: 0.5px;
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .notification-icon,
        .user-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transicion);
            position: relative;
            border: 2px solid transparent;
        }

        .notification-icon {
            background: rgba(255, 255, 255, 0.15);
            color: var(--blanco);
            backdrop-filter: blur(10px);
        }

        .notification-icon:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px) scale(1.05);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .notification-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: linear-gradient(135deg, var(--naranja) 0%, var(--naranja-claro) 100%);
            color: var(--blanco);
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            border: 2px solid var(--blanco);
            box-shadow: var(--sombra);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .user-icon {
            background: linear-gradient(135deg, var(--naranja) 0%, var(--naranja-claro) 100%);
            color: var(--blanco);
            box-shadow: var(--sombra);
        }

        .user-icon:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: var(--sombra-intensa);
        }

        .user-name {
            margin-left: 12px;
            font-weight: 600;
            font-size: 1rem;
            color: var(--blanco);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .user-dropdown {
            position: absolute;
            top: 85px;
            right: 20px;
            background: var(--blanco);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            border-radius: var(--border-radius);
            width: 260px;
            padding: 12px 0;
            display: none;
            border: 1px solid var(--gris-medio);
            z-index: 1001;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .user-dropdown.active {
            display: block;
            animation: slideDown 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-15px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .dropdown-item {
            padding: 16px 24px;
            cursor: pointer;
            transition: var(--transicion);
            display: flex;
            align-items: center;
            color: var(--negro);
            font-size: 15px;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .dropdown-item i {
            margin-right: 15px;
            width: 24px;
            text-align: center;
            color: var(--azul);
            font-size: 16px;
        }

        .dropdown-item:hover {
            background: linear-gradient(135deg, var(--gris-claro) 0%, #f1f5f9 100%);
            transform: translateX(5px);
        }

        .logout {
            color: var(--naranja) !important;
            border-top: 1px solid var(--gris-medio);
            margin-top: 8px;
        }

        .logout i {
            color: var(--naranja) !important;
        }

        .logout:hover {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        }

        .logout-form {
            margin: 0;
            padding: 0;
        }

        /* Sidebar Moderno y Mejorado */
        .sidebar {
            width: 320px;
            background: linear-gradient(180deg, var(--azul-oscuro) 0%, var(--azul) 100%);
            padding: 90px 0 20px 0;
            position: fixed;
            height: 100vh;
            z-index: 900;
            overflow-y: auto;
            box-shadow: 8px 0 25px rgba(0, 0, 0, 0.15);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        .area-section {
            margin-bottom: 12px;
        }

        .area-header {
            padding: 20px 28px;
            background: rgba(255, 255, 255, 0.1);
            color: var(--blanco);
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: var(--transicion);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid var(--naranja);
            border-radius: 0 var(--border-radius-sm) var(--border-radius-sm) 0;
            margin: 0 8px;
            backdrop-filter: blur(10px);
        }

        .area-header:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
            box-shadow: var(--sombra-suave);
        }

        .area-header i {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 14px;
        }

        .area-header.active i {
            transform: rotate(90deg);
        }

        .systems-list {
            list-style: none;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255, 255, 255, 0.05);
            margin: 0 8px;
            border-radius: 0 0 var(--border-radius-sm) var(--border-radius-sm);
        }

        .systems-list.active {
            max-height: 1000px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .system-item {
            padding: 18px 28px 18px 55px;
            cursor: pointer;
            transition: var(--transicion);
            display: flex;
            align-items: center;
            position: relative;
            font-size: 15px;
            border-left: 4px solid transparent;
            color: rgba(255, 255, 255, 0.9);
            border-radius: var(--border-radius-sm);
            margin: 4px 8px;
        }

        .system-item:hover {
            background: rgba(255, 255, 255, 0.15);
            color: var(--blanco);
            transform: translateX(8px);
            box-shadow: var(--sombra-suave);
        }

        .system-item.active {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
            font-weight: 600;
            color: var(--blanco);
            border-left: 4px solid var(--naranja);
            box-shadow: var(--sombra-suave);
        }

        .system-item.active .system-icon {
            color: var(--naranja-claro);
        }

        .system-icon {
            margin-right: 18px;
            color: rgba(255, 255, 255, 0.7);
            width: 24px;
            text-align: center;
            font-size: 16px;
            transition: var(--transicion-rapida);
        }

        .system-item:hover .system-icon {
            color: var(--naranja-claro);
            transform: scale(1.1);
        }

        /* Main Content Mejorado */
        .main-content {
            flex: 1;
            margin-left: 320px;
            padding: 100px 40px 40px 40px;
            background: linear-gradient(135deg, var(--gris-claro) 0%, #f1f5f9 100%);
            min-height: 100vh;
            position: relative;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 70vh;
            color: var(--gris-oscuro);
            text-align: center;
            background: var(--blanco);
            border-radius: var(--border-radius);
            box-shadow: var(--sombra);
            padding: 60px 40px;
            margin: 20px 0;
        }

        .empty-state i {
            font-size: 80px;
            margin-bottom: 30px;
            color: var(--azul);
            opacity: 0.6;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .empty-state h2 {
            font-size: 28px;
            margin-bottom: 15px;
            color: var(--azul-oscuro);
            font-weight: 700;
        }

        .empty-state p {
            max-width: 600px;
            line-height: 1.7;
            font-size: 16px;
            color: var(--gris-oscuro);
        }

        /* Responsive Design Mejorado */
        @media (max-width: 1200px) {
            .sidebar {
                width: 280px;
            }
            .main-content {
                margin-left: 280px;
                padding: 100px 30px 30px 30px;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 260px;
            }
            .main-content {
                margin-left: 260px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                z-index: 950;
                width: 300px;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 100px 25px 30px 25px;
            }

            .menu-toggle {
                display: flex;
            }

            .header {
                padding: 0 20px;
            }

            .company-name {
                font-size: 1.3rem;
            }
        }

        @media (max-width: 576px) {
            .user-name {
                display: none;
            }

            .main-content {
                padding: 100px 20px 30px 20px;
            }

            .company-name {
                font-size: 1.1rem;
            }

            .empty-state {
                padding: 40px 20px;
            }

            .empty-state h2 {
                font-size: 24px;
            }

            .empty-state i {
                font-size: 60px;
            }
        }

        /* Overlay para móvil */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 899;
            display: none;
            backdrop-filter: blur(2px);
        }

        @media (max-width: 768px) {
            .sidebar-overlay.active {
                display: block;
            }
        }

        /* Animaciones adicionales */
        .system-item, .area-header {
            animation: slideInFromLeft 0.3s ease-out;
        }

        @keyframes slideInFromLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>

<body>
    <div class="dashboard">
        <!-- Overlay para móvil -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Header Rediseñado -->
        <header class="header">
            <div class="logo-container">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="logo">
                    <img src="{{ asset('assets/img/logovinco2.png') }}" alt="Vinco Energy Logo">
                </div>
                <h1 class="company-name">Vinco Energy</h1>
            </div>
            <div class="user-actions">
                <div class="notification-icon">
                    <i class="fas fa-bell"></i>
                    <span class="notification-count">3</span>
                </div>
                <div class="user-icon" id="userDropdownBtn">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-name">{{ session('auth_user')['name'] ?? 'Usuario' }}</div>
                <div class="user-dropdown" id="userDropdown">
                    <div class="dropdown-item"><i class="fas fa-user-circle"></i> Mi perfil</div>
                    <div class="dropdown-item"><i class="fas fa-cog"></i> Configuración</div>
                    <form action="{{ route('logout') }}" method="POST" class="logout-form">
                        @csrf
                        <button type="submit" class="dropdown-item logout">
                            <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Sidebar Rediseñado con Desplegables -->
        <aside class="sidebar" id="sidebar">
            <!-- Verificación de permisos para cada área -->
            @php
                $userId = session('auth_user.id') ?? null;
                $userPermissions = $userId ? \App\Models\Sistemas\UserPermission::getUserPermissions($userId) : [];
            @endphp

            <!-- Área de Administración -->
            @if (isset($userPermissions['administracion']))
                <div class="area-section">
                    <div class="area-header active">
                        <span><i class="fas fa-cogs"></i> Administración</span>
                        <i class="fas fa-chevron-right"></i>
                    </div>
                    <ul class="systems-list active">
                        @if (in_array('facturacion', $userPermissions['administracion']) || empty($userPermissions['administracion']))
                            <li class="system-item" data-route="/administracion">
                                <i class="fas fa-file-invoice system-icon"></i>
                                <span class="system-text">Facturación</span>
                            </li>
                        @endif
                    </ul>
                </div>
            @endif

            <!-- Área de Recursos Humanos -->
            @if (isset($userPermissions['recursoshumanos']))
                <div class="area-section">
                    <div class="area-header active">
                        <span><i class="fas fa-users-cog"></i> Recursos Humanos</span>
                        <i class="fas fa-chevron-right"></i>
                    </div>
                    <ul class="systems-list active">
                        @if (in_array('loadchart', $userPermissions['recursoshumanos']) || empty($userPermissions['recursoshumanos']))
                            <li class="system-item" data-route="/recursoshumanos/loadchart">
                                <i class="fas fa-chart-pie system-icon"></i>
                                <span class="system-text">Mi LoadChart</span>
                            </li>
                        @endif

                        @if (in_array('altasempleados', $userPermissions['recursoshumanos']) || empty($userPermissions['recursoshumanos']))
                            <li class="system-item" data-route="/recursoshumanos/altasempleados">
                                <i class="fas fa-user-plus system-icon"></i>
                                <span class="system-text">Altas Empleados</span>
                            </li>
                        @endif
                    </ul>
                </div>
            @endif

            <!-- Área de QHSE -->
            @if (isset($userPermissions['qhse']))
                <div class="area-section">
                    <div class="area-header active">
                        <span><i class="fas fa-shield-alt"></i> QHSE</span>
                        <i class="fas fa-chevron-right"></i>
                    </div>
                    <ul class="systems-list active">
                        @if (in_array('incidencias', $userPermissions['qhse']) || empty($userPermissions['qhse']))
                            <li class="system-item" data-route="/qhse/incidencias">
                                <i class="fas fa-exclamation-triangle system-icon"></i>
                                <span class="system-text">Mis Incidencias</span>
                            </li>
                        @endif

                        @if (in_array('vescap', $userPermissions['qhse']) || empty($userPermissions['qhse']))
                            <li class="system-item" data-route="/qhse/vescap">
                                <i class="fas fa-fire-extinguisher system-icon"></i>
                                <span class="system-text">VESCAP</span>
                            </li>
                        @endif
                    </ul>
                </div>
            @endif

            <!-- Área de Sistemas -->
            @if (isset($userPermissions['sistemas']))
                <div class="area-section">
                    <div class="area-header active">
                        <span><i class="fas fa-server"></i> Sistemas</span>
                        <i class="fas fa-chevron-right"></i>
                    </div>
                    <ul class="systems-list active">
                        @if (in_array('gestionderoles', $userPermissions['sistemas']) || empty($userPermissions['sistemas']))
                            <li class="system-item" data-route="/sistemas/gestionderoles">
                                <i class="fas fa-user-shield system-icon"></i>
                                <span class="system-text">Gestión de Roles</span>
                            </li>
                        @endif
                    </ul>
                </div>
            @endif
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Toggle user dropdown
        document.getElementById('userDropdownBtn').addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('userDropdown').classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.user-dropdown')) {
                document.getElementById('userDropdown').classList.remove('active');
            }
        });

        // Toggle area sections
        document.querySelectorAll('.area-header').forEach(header => {
            header.addEventListener('click', function() {
                this.classList.toggle('active');
                const systemsList = this.nextElementSibling;
                systemsList.classList.toggle('active');
            });
        });

        // Navegación a sistemas
        document.querySelectorAll('.system-item').forEach(item => {
            item.addEventListener('click', function() {
                // Remover clase active de todos los items
                document.querySelectorAll('.system-item').forEach(i => i.classList.remove('active'));
                // Agregar clase active al item clickeado
                this.classList.add('active');

                const route = this.getAttribute('data-route');
                if (route) {
                    window.location.href = route;
                }
            });
        });

        // Toggle sidebar on mobile
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });

        // Cerrar sidebar al hacer click en overlay
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });

        // Cerrar sidebar al hacer click en un item (móvil)
        document.querySelectorAll('.system-item').forEach(item => {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });

        // Animación de entrada para elementos del sidebar
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarItems = document.querySelectorAll('.system-item, .area-header');
            sidebarItems.forEach((item, index) => {
                item.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
    @yield('scripts')
</body>

</html>
