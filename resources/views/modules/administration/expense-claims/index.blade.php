<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Vinco One - Módulo de Reembolsos</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <link href="{{ asset('assets/css/administration/expense-claims/index.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/administration/expense-claims/reimbursements.css') }}" rel="stylesheet">
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
            <a href="#" class="nav-link" data-route="reimbursements">
                <i class="fas fa-home"></i> Reembolsos
            </a>

            <a href="#" class="nav-link" data-route="stats">
                <i class="fas fa-chart-pie"></i> Estadísticas
            </a>

            {{-- ── NUEVO ENLACE: BÓVEDA SAT ── --}}
            <a href="#" class="nav-link" data-route="sat-credentials">
                <i class="fas fa-shield-alt"></i> Bóveda SAT
            </a>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/js/administration/expense-claims/index.js') }}"></script>
    <script src="{{ asset('assets/js/administration/expense-claims/reimbursements.js') }}"></script>
    <script src="{{ asset('assets/js/sessionTimer.js') }}"></script>
    @stack('scripts')
</body>

</html>
