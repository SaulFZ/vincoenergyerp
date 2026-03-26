<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Vinco Energy - Employee Management</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <link href="{{ asset('assets/css/recursoshumanos/altas/index.css') }}" rel="stylesheet">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('styles')
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
            <a href="#" class="nav-link active" data-route="employee_registration">
                <i class="fas fa-user-plus"></i> Altas Empleados
            </a>

            <a href="#" class="nav-link" data-route="history">
                <i class="fas fa-history"></i> Historial
            </a>

            @if (\App\Helpers\PermissionHelper::hasDirectPermission('ver_estadisticas'))
                <a href="#" class="nav-link" data-route="stats">
                    <i class="fas fa-chart-bar"></i> Estadísticas
                </a>
            @endif

            @if (\App\Helpers\PermissionHelper::hasDirectPermission('ver_gestion_vacaciones'))
                <a href="#" class="nav-link" data-route="employee_vacation_balance">
                    <i class="fas fa-suitcase-rolling"></i> Vacaciones
                </a>
            @endif
        </nav>

        @include('components.layouts._user-profile')
    </header>

    <main class="main-container" id="main-content">
        @yield('content')
    </main>

    <footer class="footer-main">
        <p>
            Vinco Energy © <span id="current-year"></span> | Todos los derechos reservados
        </p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/js/recursoshumanos/altas/index.js') }}"></script>
    <script src="{{ asset('assets/js/sessionTimer.js') }}"></script>
    @stack('scripts')
</body>
</html>
