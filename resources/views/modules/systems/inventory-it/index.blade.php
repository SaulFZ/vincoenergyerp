<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- Título actualizado -->
    <title>VesCore - Inventario TI</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- CSS del Layout Principal Inventario TI -->
    <link href="{{ asset('assets/css/systems/inventoryit/index.css') }}" rel="stylesheet">

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
            <!-- Menú adaptado a Inventario TI -->
            <a href="{{ route('systems.inventory-it.index') }}" class="nav-link active" data-route="dashboard">
                <i class="fas fa-home"></i> Principal
            </a>

            <a href="#" class="nav-link" data-route="equipos">
                <i class="fas fa-laptop"></i> Equipos
            </a>

            <a href="#" class="nav-link" data-route="asignaciones">
                <i class="fas fa-user-tag"></i> Asignaciones
            </a>

            <a href="#" class="nav-link" data-route="reportes">
                <i class="fas fa-chart-bar"></i> Reportes
            </a>
        </nav>

        @include('components.layouts._user-profile')
    </header>

    <main class="main-container" id="main-content">
        {{-- Aquí se inyectará el dashboard con las tarjetas o las tablas --}}
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
    <script src="{{ asset('assets/js/systems/inventoryit/index.js') }}"></script>

    @stack('scripts')
</body>

</html>