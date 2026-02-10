<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinco Energy - Gerenciamiento de Viajes</title>

    <link rel="stylesheet"href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link
        rel="stylesheet"href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="{{ asset('assets/css/qhse/gerenciamiento/index.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @stack('css')
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
                <i class="fas fa-tachometer-alt"></i> Inicio
            </a>

            <a href="#" class="nav-link-viajes" data-route="unidades">
                <i class="fas fa-truck-moving"></i> Gestión de Unidades
            </a>
        </nav>

        @include('components.layouts._user-profile')
    </header>

    <main class="container-viajes" id="main-content">
        @yield('content')
    </main>

    @yield('modals')

    <footer class="footer-viajes">
        <p>
            <i class="fas fa-shield-alt"></i>
            Sistema de Gerenciamiento de Viajes - Vinco Energy © 2025 | Todos los derechos reservados
        </p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="{{ asset('assets/js/sessionTimer.js') }}"></script>
    @stack('scripts')
</body>

</html>
