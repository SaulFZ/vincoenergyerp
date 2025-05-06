<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Vinco ERP')</title>
    <!-- Font Awesome CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome Script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/areahome.css') }}">
    @yield('styles')
</head>

<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <img src="{{ asset('assets/img/logovinco2.png') }}" alt="Logo Vinco" />
                <h2 class="module-title">Módulo de @yield('module-name', 'Área')</h2>
            </div>

            <div class="user-actions">
                <a href="{{ route('home') }}" class="home-button">
                    <i class="fas fa-home"></i>
                    <span>Inicio</span>
                </a>
                <div class="user-profile">
                    <div class="user-avatar-container">
                        <div class="user-avatar">
                            @php
                                $initials = '';
                                $name = session('auth_user')['name'] ?? 'Usuario';
                                $nameParts = explode(' ', $name);
                                foreach ($nameParts as $part) {
                                    if (!empty($part)) {
                                        $initials .= substr($part, 0, 1);
                                    }
                                    if (strlen($initials) >= 2) {
                                        break;
                                    }
                                }
                            @endphp
                            <span>{{ strtoupper($initials) }}</span>
                        </div>
                        <div class="user-info">
                            <h1>{{ session('auth_user')['name'] ?? 'Usuario' }}</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            @yield('content')
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-info">
                    <p>&copy; 2024 ERP Vinco. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </footer>

    @yield('scripts')
    <script src="{{ asset('assets/js/areahome.js') }}"></script>
</body>

</html>
