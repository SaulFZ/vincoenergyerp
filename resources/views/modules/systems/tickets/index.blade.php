<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinco Energy - Ticket</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    {{-- CSS Genérico --}}
    <link href="{{ asset('assets/css/systems/tickets/index.css') }}" rel="stylesheet">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('css')
</head>

<body>
    <header class="header-main">
        <div class="container-logo">
            <div class="logo-wrapper">
                <div class="logo-img">
                    <img src="{{ asset('assets/img/logovinco1.png') }}" alt="Vinco Energy">
                </div>
            </div>
        </div>

        <nav class="nav-container">
            <a href="#" class="nav-link active" data-route="management-tickets">
                <i class="fas fa-chart-pie"></i> inicio
            </a>

            <a href="#" class="nav-link" data-route="stats">
                <i class="fas fa-ticket-alt"></i> Estadisticas
            </a>

        </nav>

        @include('components.layouts._user-profile')
    </header>

    <main class="container-main" id="main-content">
        @yield('content')
    </main>

    @yield('modals')

    <footer class="footer-main">
        <p>
            <i class="fas fa-headset"></i>
            Centro de Soporte Técnico - Vinco Energy © 2026 | Eficiencia y Tecnología
        </p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/js/sessionTimer.js') }}"></script>
        <script src="{{ asset('assets/js/systems/tickets/index.js') }}"></script>

    @stack('scripts')
</body>

</html>
