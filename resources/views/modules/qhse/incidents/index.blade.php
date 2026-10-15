<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>VesCore - QHSE Incidencias</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- CSS del Layout Principal QHSE Incidencias -->
    <link href="{{ asset('assets/css/qhse/incidents/index.css') }}" rel="stylesheet">

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
            {{-- Menú adaptado a Gestión de Incidencias TACIS --}}
            <a href="#" class="nav-link" data-route="records">
                <i class="fas fa-tachometer-alt"></i> Inicio
            </a>

            <a href="#" class="nav-link" data-route="incidencias">
                <i class="fas fa-exclamation-triangle"></i> Incidencias
            </a>

            <a href="#" class="nav-link" data-route="investigaciones">
                <i class="fas fa-search-plus"></i> Investigaciones
            </a>

            <a href="#" class="nav-link" data-route="acciones">
                <i class="fas fa-tasks"></i> Acciones TACIS
            </a>
        </nav>

        @include('components.layouts._user-profile')
    </header>

    <main class="main-container" id="main-content">
        {{-- Aquí se inyectará el contenido principal del módulo --}}
        @yield('content')
    </main>

    <footer class="footer-main">
        <p>
            Vinco Energy © <span id="current-year"></span> | Todos los derechos reservados
        </p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/js/sessionTimer.js') }}"></script>

    <!-- JS General del módulo -->
    <script src="{{ asset('assets/js/qhse/incidents/index.js') }}"></script>

    @stack('scripts')
</body>

</html>