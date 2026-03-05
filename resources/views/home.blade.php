<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vinco Energy ERP</title>

    {{-- FontAwesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    {{-- CSS Principal --}}
    <link href="{{ asset('assets/css/home.css') }}" rel="stylesheet">
    @stack('styles')
</head>

<body>
    <div class="dashboard">

        {{-- Overlay para móviles --}}
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        {{-- HEADER --}}
        <header class="header">
            <div class="header-left">
                {{-- Botón Menú Móvil --}}
                <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú">
                    <i class="fas fa-bars"></i>
                </button>

                {{-- Logo y Texto en una sola línea --}}
                <div class="brand-container">
                    <img src="{{ asset('assets/img/logovinco2.png') }}" alt="Vinco Energy" class="brand-logo" />

                    {{-- Texto unificado
                    <h1 class="company-name">
                        Vinco Energy <span class="erp-text">ERP</span>
                    </h1> --}}
                </div>
            </div>

            {{-- Perfil de Usuario --}}
            <div class="header-right">
                @include('components.layouts._user-profile')
            </div>
        </header>

        {{-- SIDEBAR --}}
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-content">
                @php
                    $userPermissions = Auth::check()
                        ? \App\Models\Sistemas\UserPermission::getUserPermissions(Auth::id())
                        : [];
                @endphp

                {{-- Sección: Administración --}}
                @if (isset($userPermissions['administracion']))
                    <div class="nav-group">
                        <div class="nav-header active">
                            <div class="nav-header-title">
                                <i class="fas fa-cogs"></i>
                                <span>Administración</span>
                            </div>
                            <i class="fas fa-chevron-right arrow-icon"></i>
                        </div>
                        <ul class="nav-list active">
                            @if (in_array('facturacion', $userPermissions['administracion']) || empty($userPermissions['administracion']))
                                <li class="nav-item" data-route="/administracion">
                                    <i class="fas fa-file-invoice nav-icon"></i>
                                    <span class="nav-text">Facturación</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif

                {{-- Sección: Recursos Humanos --}}
                @if (isset($userPermissions['recursoshumanos']))
                    <div class="nav-group">
                        <div class="nav-header active">
                            <div class="nav-header-title">
                                <i class="fas fa-users-cog"></i>
                                <span>Recursos Humanos</span>
                            </div>
                            <i class="fas fa-chevron-right arrow-icon"></i>
                        </div>
                        <ul class="nav-list active">
                            @if (in_array('loadchart', $userPermissions['recursoshumanos']) || empty($userPermissions['recursoshumanos']))
                                <li class="nav-item" data-route="/recursoshumanos/loadchart">
                                    <i class="fas fa-chart-pie nav-icon"></i>
                                    <span class="nav-text">Mi LoadChart</span>
                                </li>
                            @endif

                            @if (in_array('altasempleados', $userPermissions['recursoshumanos']) || empty($userPermissions['recursoshumanos']))
                                <li class="nav-item" data-route="/recursoshumanos/altasempleados">
                                    <i class="fas fa-user-plus nav-icon"></i>
                                    <span class="nav-text">Altas Empleados</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif

                {{-- Sección: QHSE --}}
                @if (isset($userPermissions['qhse']))
                    <div class="nav-group">
                        <div class="nav-header active">
                            <div class="nav-header-title">
                                <i class="fas fa-shield-alt"></i>
                                <span>QHSE</span>
                            </div>
                            <i class="fas fa-chevron-right arrow-icon"></i>
                        </div>
                        <ul class="nav-list active">
                            @if (in_array('gerenciamiento', $userPermissions['qhse']) || empty($userPermissions['qhse']))
                                <li class="nav-item" data-route="/qhse/gerenciamiento">
                                    <i class="fas fa-road nav-icon"></i>
                                    <span class="nav-text">Gerenciamiento De Viajes</span>
                                </li>
                            @endif
                            @if (in_array('incidencias', $userPermissions['qhse']) || empty($userPermissions['qhse']))
                                <li class="nav-item" data-route="/qhse/incidencias">
                                    <i class="fas fa-exclamation-triangle nav-icon"></i>
                                    <span class="nav-text">Mis Incidencias</span>
                                </li>
                            @endif
                            @if (in_array('vescap', $userPermissions['qhse']) || empty($userPermissions['qhse']))
                                <li class="nav-item" data-route="/qhse/vescap">
                                    <i class="fas fa-fire-extinguisher nav-icon"></i>
                                    <span class="nav-text">VESCAP</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif

                {{-- Sección: Sistemas --}}
                @if (isset($userPermissions['sistemas']))
                    <div class="nav-group">
                        <div class="nav-header active">
                            <div class="nav-header-title">
                                <i class="fas fa-server"></i>
                                <span>Sistemas</span>
                            </div>
                            <i class="fas fa-chevron-right arrow-icon"></i>
                        </div>
                        <ul class="nav-list active">
                            @if (in_array('gestionderoles', $userPermissions['sistemas']) || empty($userPermissions['sistemas']))
                                <li class="nav-item" data-route="/sistemas/gestionderoles">
                                    <i class="fas fa-user-shield nav-icon"></i>
                                    <span class="nav-text">Gestión de Roles</span>
                                </li>
                            @endif
                            @if (in_array('tickets', $userPermissions['sistemas']) || empty($userPermissions['sistemas']))
                                <li class="nav-item" data-route="/sistemas/tickets">
                                    <i class="fas fa-ticket-alt nav-icon"></i>
                                    <span class="nav-text">Gestión de Tickets</span>
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
                @yield('content')

                {{-- Empty State --}}
                <div class="empty-state-wrapper">
                    <div class="empty-state-card">
                        <div class="icon-circle">
                            <i class="fas fa-bell-slash"></i>
                        </div>
                        <h2 class="empty-title">Sin novedades recientes</h2>
                        <p class="empty-description">
                            No hay comunicados o avisos importantes para mostrar en este momento.
                        </p>
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
                    position: 'top-end'
                });
            });
        </script>
    @endif
    <script src="{{ asset('assets/js/sessionTimer.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Sidebar Accordion Logic
            const headers = document.querySelectorAll('.nav-header');
            headers.forEach(header => {
                const setHeight = (element) => {
                    const list = element.nextElementSibling;
                    const icon = element.querySelector('.arrow-icon');
                    if (element.classList.contains('active')) {
                        list.style.maxHeight = list.scrollHeight + "px";
                        icon.style.transform = "rotate(90deg)";
                    } else {
                        list.style.maxHeight = "0px";
                        icon.style.transform = "rotate(0deg)";
                    }
                };
                setHeight(header);
                header.addEventListener('click', () => {
                    header.classList.toggle('active');
                    header.nextElementSibling.classList.toggle('active');
                    setHeight(header);
                });
            });

            // Active Link Logic
            const navItems = document.querySelectorAll('.nav-item');
            const currentPath = window.location.pathname;
            navItems.forEach(item => {
                if(item.dataset.route === currentPath) {
                    item.classList.add('active');
                    const parentGroup = item.closest('.nav-group');
                    if(parentGroup){
                        const header = parentGroup.querySelector('.nav-header');
                        const list = parentGroup.querySelector('.nav-list');
                        header.classList.add('active');
                        list.classList.add('active');
                        list.style.maxHeight = list.scrollHeight + "px";
                    }
                }
                item.addEventListener('click', function() {
                    const route = this.getAttribute('data-route');
                    if (route) window.location.href = route;
                });
            });

            // Mobile Menu Logic
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            function toggleMenu() {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('show');
            }

            if(menuToggle){
                menuToggle.addEventListener('click', toggleMenu);
                overlay.addEventListener('click', toggleMenu);
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
