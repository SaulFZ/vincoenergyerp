<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinco - Cargando</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"
        integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.transit/0.9.12/jquery.transit.js"
        integrity="sha256-mkdmXjMvBcpAyyFNCVdbwg4v+ycJho65QLDwVE3ViDs=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --accent-color: #4cc9f0;
            --text-color: #2b2d42;
            --light-text: #6c757d;
            --background: #f8f9fa;
            --white: #ffffff;
        }

        body,
        html {
            height: 100%;
            margin: 0;
            padding: 0;
            background-color: var(--background);
            font-family: 'Poppins', sans-serif;
            overflow: hidden;
            color: var(--text-color);
        }

        .splash-container {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            background: radial-gradient(ellipse at center, var(--white) 0%, var(--background) 100%);
        }

        .decoration {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }

        .circle {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
        }

        .circle-1 {
            width: 300px;
            height: 300px;
            background: var(--primary-color);
            top: -100px;
            left: -100px;
        }

        .circle-2 {
            width: 400px;
            height: 400px;
            background: var(--secondary-color);
            bottom: -150px;
            right: -150px;
        }

        .circle-3 {
            width: 200px;
            height: 200px;
            background: var(--accent-color);
            top: 20%;
            right: 10%;
        }

        .content {
            z-index: 1;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 600px;
            padding: 0 20px;
        }

        .logo-container {
            text-align: center;
            transform: scale(0);
            opacity: 0;
            transition: all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            margin-bottom: 30px;
        }

        .logo {
            max-width: 180px;
            height: auto;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
        }

        .logo-pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
            }

            50% {
                transform: scale(1.05);
                filter: drop-shadow(0 8px 12px rgba(0, 0, 0, 0.15));
            }

            100% {
                transform: scale(1);
                filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
            }
        }

        .loading-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 300px;
        }

        .loading-bar {
            width: 100%;
            height: 6px;
            background-color: rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
            opacity: 0;
            transition: opacity 0.5s ease;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .loading-progress {
            position: absolute;
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--accent-color) 100%);
            border-radius: 8px;
            transition: width 2s cubic-bezier(0.1, 0.5, 0.9, 1);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .loading-text {
            font-size: 15px;
            color: var(--light-text);
            margin-top: 12px;
            opacity: 0;
            transition: opacity 0.5s ease;
            font-weight: 400;
            letter-spacing: 0.3px;
        }

        .welcome-message {
            margin-top: 30px;
            font-size: 26px;
            font-weight: 600;
            color: var(--text-color);
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .subtitle {
            font-size: 16px;
            color: var(--light-text);
            margin-top: 8px;
            opacity: 0;
            transform: translateY(15px);
            transition: all 0.5s ease;
            transition-delay: 0.1s;
            max-width: 400px;
        }

        @media (max-width: 480px) {
            .logo {
                max-width: 150px;
            }

            .welcome-message {
                font-size: 22px;
            }

            .subtitle {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="splash-container">
        <div class="decoration">
            <div class="circle circle-1"></div>
            <div class="circle circle-2"></div>
            <div class="circle circle-3"></div>
        </div>

        <div class="content">
            <div class="logo-container">
                <img class="logo" src="{{ asset('assets/img/logovinco2.png') }}" alt="Logo Vinco" />
            </div>

            <div class="loading-container">
                <div class="loading-bar">
                    <div class="loading-progress"></div>
                </div>
                <div class="loading-text">Iniciando sesión...</div>
            </div>

            <div class="welcome-message">¡Bienvenido a Vinco!</div>
            <div class="subtitle">Estamos preparando todo para brindarte la mejor experiencia</div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Obtener datos del usuario de la sesión
            var userName = "{{ session('auth_user')['name'] ?? 'Usuario' }}";

            // Actualizar el mensaje de bienvenida con el nombre del usuario
            $(".welcome-message").text("¡Bienvenido, " + userName + "!");

            // Secuencia de animación
            setTimeout(function() {
                // Mostrar logo con animación
                $(".logo-container").css({
                    'transform': 'scale(1)',
                    'opacity': '1'
                });

                // Mostrar barra de carga
                setTimeout(function() {
                    $(".loading-bar, .loading-text").css('opacity', '1');

                    // Animar progreso
                    setTimeout(function() {
                        $(".loading-progress").css('width', '100%');

                        // Añadir efecto de pulso al logo
                        $(".logo").addClass('logo-pulse');

                        // Mostrar mensaje de bienvenida
                        setTimeout(function() {
                            $(".welcome-message").css({
                                'opacity': '1',
                                'transform': 'translateY(0)'
                            });

                            // Mostrar subtítulo
                            setTimeout(function() {
                                $(".subtitle").css({
                                    'opacity': '0.8',
                                    'transform': 'translateY(0)'
                                });

                                // Redireccionar a home
                                setTimeout(function() {
                                    window.location.href = "{{ route('home') }}";
                                }, 1200);
                            }, 200);
                        }, 800);
                    }, 400);
                }, 600);
            }, 300);
        });
    </script>
</body>

</html>