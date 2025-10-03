<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinco Energy - Load Chart</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link href="{{ asset('assets/css/recursoshumanos/loadchart/index.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('styles')

</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="logo-container">
            <div class="logo">
                <div class="logo-img">
                    <img src="{{ asset('assets/img/logovinco2.png') }}" alt="Vinco Energy">
                </div>
                <div class="logo-text">Load Chart</div>
            </div>
        </div>
        <div class="nav-links">
            <a data-route="calendar"><i class="fas fa-home"></i> Inicio</a>

            {{-- Add the notification badge span here --}}
            <a data-route="history"><i class="fas fa-history"></i> Historial <span
                    class="notification-badge">10</span></a>

            @if (\App\Helpers\PermissionHelper::hasDirectPermission('ver_estadisticas'))
                <a data-route="stats"><i class="fas fa-chart-bar"></i> Estadísticas</a>
            @endif

            @if (\App\Helpers\PermissionHelper::hasDirectPermission('ver_gestionRyA'))
                <a data-route="review_assignments"><i class="fas fa-chart-bar"></i> Gestión de RyA</a>
            @endif

            {{-- NUEVO: Enlace para Bonos de Campo --}}
            @if (\App\Helpers\PermissionHelper::hasDirectPermission('ver_gestion_bonos'))
                <a data-route="field_bonuses"><i class="fas fa-money-bill-wave"></i> Gestion de Bonos</a>
            @endif

            {{-- NUEVO: Enlace para Gestión de Vacaciones --}}
            @if (\App\Helpers\PermissionHelper::hasDirectPermission('ver_gestion_vacaciones'))
                <a data-route="employee_vacation_balance"><i class="fas fa-suitcase-rolling"></i> Gestion de Vacaciones</a>
            @endif
        </div>

        @include('components.layouts._user-profile')


    </header>

    <!-- Main Content -->
    <div class="container" id="main-content">
        <!-- Content will be loaded here dynamically -->
        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>Sistema Load Chart - Vinco Energy © 2025 | Todos los derechos reservados</p>
    </footer>

    <script src="{{ asset('assets/js/recursoshumanos/loadchart/index.js') }}"></script>
    @stack('scripts')
</body>

</html>
