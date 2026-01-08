<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>vinco Energy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="{{ asset('assets/css/home.css') }}" rel="stylesheet">
    @stack('styles')
</head>

<body>
    <div class="dashboard">

        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <header class="header">
            <div class="logo-container">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="logo">
                    <img src="{{ asset('assets/img/logovinco2.png') }}" alt="Vinco Energy Logo" />
                </div>
                <h1 class="company-name">Vinco Energy ERP</h1>
            </div>

            @include('components.layouts._user-profile')

        </header>

        <aside class="sidebar" id="sidebar">
            @php
                // Lógica de obtención de permisos (se mantiene)
                $userPermissions = Auth::check()
                    ? \App\Models\Sistemas\UserPermission::getUserPermissions(Auth::id())
                    : [];
            @endphp

            {{-- Sección: Administración --}}
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

            {{-- Sección: Recursos Humanos --}}
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

            {{-- Sección: QHSE --}}
            @if (isset($userPermissions['qhse']))
                <div class="area-section">
                    <div class="area-header active">
                        <span><i class="fas fa-shield-alt"></i> QHSE</span>
                        <i class="fas fa-chevron-right"></i>
                    </div>
                    <ul class="systems-list active">
                        @if (in_array('gerenciamiento', $userPermissions['qhse']) || empty($userPermissions['qhse']))
                            <li class="system-item" data-route="/qhse/gerenciamiento">
                                {{-- Ícono cambiado a fas fa-tachometer-alt (Tablero/Gestión) --}}
                                <i class="fas fa-solid fa-road system-icon"></i>
                                <span class="system-text">Gerenciamiento</span>
                            </li>
                        @endif
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

            {{-- Sección: Sistemas --}}
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

        <main class="main-content">
            @yield('content')
            <div class="empty-state-container">
                <div class="empty-state-content">
                    <div class="empty-state-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h2 class="empty-state-title">
                        No hay comunicados o avisos recientes.
                    </h2>
                    <p class="empty-state-text">
                        En este espacio se mostrarán las noticias importantes, comunicados internos.
                    </p>
                </div>
            </div>
        </main>
    </div>

    @if (session('swal'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '{{ session('swal')['icon'] }}',
                    title: '{{ session('swal')['title'] }}',
                    text: '{{ session('swal')['text'] }}',
                    timer: {{ session('swal')['timer'] ?? 4000 }},
                    showConfirmButton: false
                });
            });
        </script>
    @endif
    <script src="{{ asset('assets/js/sessionTimer.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Toggle area sections
        document.querySelectorAll('.area-header').forEach((header) => {
            header.addEventListener('click', function() {
                this.classList.toggle('active');
                const systemsList = this.nextElementSibling;
                systemsList.classList.toggle('active');
            });
        });

        // Navegación a sistemas
        document.querySelectorAll('.system-item').forEach((item) => {
            item.addEventListener('click', function() {
                // Remover clase active de todos los items
                document
                    .querySelectorAll('.system-item')
                    .forEach((i) => i.classList.remove('active'));
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
        document.querySelectorAll('.system-item').forEach((item) => {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });

        // Animación de entrada para elementos del sidebar (se mantiene)
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarItems = document.querySelectorAll(
                '.system-item, .area-header',
            );
            sidebarItems.forEach((item, index) => {
                item.style.animationDelay = `${index * 0.05}s`; // Animación más rápida
            });
        });
    </script>
    @stack('scripts')
</body>

</html>
