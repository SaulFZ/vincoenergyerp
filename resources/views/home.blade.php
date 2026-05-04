<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vinco Energy</title>

    {{-- FontAwesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">

    {{-- CSS Principal --}}
    <link href="{{ asset('assets/css/home.css') }}" rel="stylesheet">
    @stack('styles')
</head>

<body>
<div class="dashboard">

    {{-- Overlay móvil --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- ======= HEADER ======= --}}
    <header class="header">
        <div class="header-left">

            {{-- Botón menú móvil --}}
            <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú">
                <i class="fas fa-bars"></i>
            </button>

            {{-- Logo — clic minimiza el sidebar en escritorio --}}
            <div class="brand-container" id="logoToggle" style="cursor:pointer" title="Minimizar/Maximizar menú">
                <img src="{{ asset('assets/img/logovinco1.png') }}" alt="Vinco Hub" class="brand-logo" id="mainLogo" />
            </div>

        </div>

        <div class="header-right">
            @include('components.layouts._user-profile')
        </div>
    </header>

    {{-- ======= SIDEBAR ======= --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-content">

            @php
                $userPermissions = Auth::check()? \App\Models\Systems\UserManagement\UserPermission::getUserPermissions(Auth::id()) : [];
            @endphp

            {{-- Administración --}}
            @if (isset($userPermissions['administracion']))
                <div class="nav-group">
                    <div class="nav-header active" data-name="Administración">
                        <div class="nav-header-title">
                            <i class="fas fa-cogs"></i>
                            <span>Administración</span>
                        </div>
                        <i class="fas fa-chevron-right arrow-icon"></i>
                    </div>
                    <ul class="nav-list active">
                        @if (in_array('reembolsos', $userPermissions['administracion']) || empty($userPermissions['administracion']))
                            <li class="nav-item" data-route="/administracion/reembolsos" data-name="Reembolsos">
                                <i class="fas fa-receipt nav-icon"></i>
                                <span class="nav-text">Reembolsos</span>
                            </li>
                        @endif
                        @if (in_array('facturacion', $userPermissions['administracion']) || empty($userPermissions['administracion']))
                            <li class="nav-item" data-route="/administracion" data-name="Facturación">
                                <i class="fas fa-file-invoice nav-icon"></i>
                                <span class="nav-text">Facturación</span>
                            </li>
                        @endif
                    </ul>
                </div>
            @endif

            {{-- Recursos Humanos --}}
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
                                <span class="nav-text">Altas Empleados</span>
                            </li>
                        @endif
                    </ul>
                </div>
            @endif

            {{-- QHSE --}}
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
                            <li class="nav-item" data-route="/qhse/management" data-name="Gerenciamiento De Viajes">
                                <i class="fas fa-road nav-icon"></i>
                                <span class="nav-text">Gerenciamiento De Viajes</span>
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
                                <span class="nav-text">VESCAP</span>
                            </li>
                        @endif
                    </ul>
                </div>
            @endif

            {{-- Sistemas --}}
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
                            <li class="nav-item" data-route="/systems/user-management" data-name="Gestión de Roles">
                                <i class="fas fa-user-shield nav-icon"></i>
                                <span class="nav-text">Gestión de Roles</span>
                            </li>
                        @endif
                        @if (in_array('tickets', $userPermissions['systems']) || empty($userPermissions['systems']))
                            <li class="nav-item" data-route="/systems/tickets" data-name="Gestión de Tickets">
                                <i class="fas fa-ticket-alt nav-icon"></i>
                                <span class="nav-text">Gestión de Tickets</span>
                            </li>
                        @endif
                    </ul>
                </div>
            @endif

            {{-- Operaciones --}}
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

    {{-- ======= MAIN CONTENT ======= --}}
    <main class="main-content">
        <div class="content-wrapper">

            @hasSection('content')
                @yield('content')
            @else
                {{-- ESTADO VACÍO (NOTIFICACIONES) --}}
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
            @endif

        </div>
    </main>

</div>

{{-- ======= SCRIPTS ======= --}}

{{-- SweetAlert sesión --}}
@if (session('swal'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon:              '{{ session('swal')['icon'] }}',
                title:             '{{ session('swal')['title'] }}',
                text:              '{{ session('swal')['text'] }}',
                timer:             {{ session('swal')['timer'] ?? 4000 }},
                showConfirmButton: false,
                toast:             true,
                position:          'top-end',
            });
        });
    </script>
@endif

<script src="{{ asset('assets/js/sessionTimer.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ── Acordeón sidebar ── */
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

    /* ── Ítem activo según ruta ── */
    const currentPath = window.location.pathname;

    document.querySelectorAll('.nav-item').forEach(item => {
        if (item.dataset.route === currentPath) {
            item.classList.add('active');
            const group  = item.closest('.nav-group');
            const header = group?.querySelector('.nav-header');
            const list   = group?.querySelector('.nav-list');
            if (header && list) {
                header.classList.add('active');
                list.classList.add('active');
                setTimeout(() => setHeight(header), 50);
            }
        }
        item.addEventListener('click', function () {
            const route = this.dataset.route;
            if (route) window.location.href = route;
        });
    });

    /* ── Toggle móvil ── */
    const menuToggle = document.getElementById('menuToggle');
    const sidebar    = document.getElementById('sidebar');
    const overlay    = document.getElementById('sidebarOverlay');

    const toggleMobile = () => {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('show');
    };

    menuToggle?.addEventListener('click', toggleMobile);
    overlay?.addEventListener('click', toggleMobile);

    /* ── Minimizar escritorio & Cambio de Logo Suave ── */
    const logoImg = document.getElementById('mainLogo');

    document.getElementById('logoToggle')?.addEventListener('click', () => {
        if (window.innerWidth > 768) {
            document.body.classList.toggle('sidebar-minimized');

            // Animación de fade para que no se vea cortado al cambiar la imagen
            logoImg.style.opacity = '0';

            setTimeout(() => {
                if (document.body.classList.contains('sidebar-minimized')) {
                    logoImg.src = "{{ asset('assets/img/logo.png') }}";
                } else {
                    logoImg.src = "{{ asset('assets/img/logovinco1.png') }}";
                }
                logoImg.style.opacity = '1';
            }, 200); // 200ms coincide con la transición CSS

            // Recalcular alturas tras transición
            setTimeout(() => {
                document.querySelectorAll('.nav-header.active').forEach(header => {
                    const list = header.nextElementSibling;
                    if (list) list.style.maxHeight = list.scrollHeight + 'px';
                });
            }, 360);
        }
    });

});
</script>

@stack('scripts')
</body>
</html>
