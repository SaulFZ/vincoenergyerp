<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinco Energy - Load Chart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link href="{{ asset('assets/css/recursoshumanos/loadchart/index.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
            <a data-route="history"><i class="fas fa-history"></i> Historial</a>
            <a data-route="stats"><i class="fas fa-chart-bar"></i> Estadísticas</a>
        </div>
        <div class="user-info">
            <div class="welcome">

                <i class="fas fa-user-circle"></i> {{ Auth::user()->name }}
            </div>
            <div class="logout" onclick="window.location.href='{{ route('home') }}'">
                <i class="fas fa-home"></i> Salir
            </div>
        </div>
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
