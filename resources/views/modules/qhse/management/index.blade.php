<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">

    <title>Vinco Energy - Gestión</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="{{ asset('assets/css/qhse/management/index.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/qhse/management/journey.css') }}" rel="stylesheet">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @stack('css')
</head>

<body>
    <header class="header-main">
        <div class="logo-container">
            <div class="logo-wrapper">
                <div class="logo-img">
                    <img src="{{ asset('assets/img/logovinco1.png') }}" alt="Vinco Energy">
                </div>
            </div>
        </div>

        <nav class="nav-main">
            <a href="#" class="nav-link" data-route="journey">
                <i class="fas fa-tachometer-alt"></i> Inicio
            </a>

            @if (\App\Helpers\PermissionHelper::hasDirectPermission('ver_control_lic_gv'))
                <a href="#" class="nav-link" data-route="driver_licenses">
                    <i class="fas fa-id-card-alt"></i> Control de Licencias
                </a>
            @endif

            @if (\App\Helpers\PermissionHelper::hasDirectPermission('ver_estadisticas_gv'))
                <a href="#" class="nav-link" data-route="stats">
                    <i class="fas fa-chart-pie"></i> Estadísticas
                </a>
            @endif
        </nav>

        @include('components.layouts._user-profile')
    </header>

    <main class="main-container" id="main-content">
        @yield('content')
    </main>

    @yield('modals')

   <footer class="footer-main">
    <p>
        Vinco Energy © <span id="current-year">2026</span> | Todos los derechos reservados
    </p>
</footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="{{ asset('assets/js/qhse/management/index.js') }}"></script>
    <script src="{{ asset('assets/js/sessionTimer.js') }}"></script>

    @stack('scripts')
</body>

</html>
