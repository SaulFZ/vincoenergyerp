<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinco - Cargando</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"
        integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --naranja: #d67e29;
            --naranja-claro: #fa9333;
            --naranja-oscuro: #b96520;
            --azul: #334c95;
            --azul-oscuro: #263671;
            --azul-claro: #4b56cc;
            --blanco: #fff;
            --gris-muy-claro: #f8f9fa;
            --gris-claro: #eaedf2;
            --gris-medio: #a0a8c0;
            --gris-texto: #4a4b57;
            --shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s ease;
        }

        body,
        html {
            height: 100%;
            margin: 0;
            padding: 0;
            background-color: var(--blanco);
            font-family: 'Inter', sans-serif;
            overflow: hidden;
            color: var(--gris-texto);
        }

        .splash-container {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            background: var(--blanco);
        }

        .header-accent {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--azul) 0%, var(--azul-claro) 100%);
            z-index: 10;
        }

        .content {
            z-index: 2;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 340px;
            padding: 40px 20px;
            transform: translateY(0);
            opacity: 0;
            animation: fadeIn 0.8s forwards ease-out 0.2s;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        .logo-container {
            margin-bottom: 20px;
            position: relative;
        }

        .logo {
            width: 130px;
            height: auto;
        }



        .loading-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 260px;
            margin-top: 15px;
        }

        .loading-bar {
            width: 100%;
            height: 4px;
            background-color: var(--gris-claro);
            border-radius: 2px;
            position: relative;
            overflow: hidden;
            opacity: 0;
            animation: fadeIn 0.5s forwards 0.8s;
        }

        .loading-progress {
            position: absolute;
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--azul) 0%, var(--naranja) 100%);
            border-radius: 2px;
            animation: progressAnimation 2s forwards cubic-bezier(0.1, 0.8, 0.2, 1) 1s;
        }

        @keyframes progressAnimation {
            to {
                width: 100%;
            }
        }

        .loading-text {
            font-size: 13px;
            color: var(--gris-medio);
            margin-top: 12px;
            opacity: 0;
            animation: fadeIn 0.5s forwards 0.9s;
            font-weight: 400;
            letter-spacing: 0.3px;
        }

        .welcome-message {
            margin-top: 40px;
            font-size: 22px;
            font-weight: 600;
            color: var(--azul);
            opacity: 0;
            animation: fadeIn 0.6s forwards 1.5s;
        }

        .subtitle {
            font-size: 14px;
            color: var(--gris-texto);
            margin-top: 8px;
            opacity: 0;
            animation: fadeIn 0.6s forwards 1.7s;
            max-width: 280px;
            line-height: 1.5;
        }

        .company-info {
            position: absolute;
            bottom: 20px;
            font-size: 12px;
            color: var(--gris-medio);
            opacity: 0;
            animation: fadeIn 0.5s forwards 2s;
        }

        @media (max-width: 480px) {
            .content {
                padding: 30px 15px;
            }

            .logo {
                width: 90px;
            }

            .welcome-message {
                font-size: 20px;
            }

            .subtitle {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>
    <div class="splash-container">
        <div class="header-accent"></div>

        <div class="content">
            <div class="logo-container">
                <img class="logo" src="{{ asset('assets/img/logovinco1.png') }}" alt="Logo Vinco" />
            </div>


            <div class="loading-container">
                <div class="loading-bar">
                    <div class="loading-progress"></div>
                </div>
                <div class="loading-text">Iniciando sesión...</div>
            </div>

            <div class="welcome-message">Bienvenido a Vinco</div>
            <div class="subtitle">Estamos preparando todo para brindarte la mejor experiencia</div>
        </div>

        <div class="company-info">© Vinco Energy Service</div>
    </div>

    <script>
        $(document).ready(function() {
            // Obtener datos del usuario de la sesión
            var userName = "{{ session('auth_user')['name'] ?? 'Usuario' }}";

            // Actualizar el mensaje de bienvenida con el nombre del usuario
            setTimeout(function() {
                $(".welcome-message").text("Bienvenido, " + userName);

                // Redireccionar después de que las animaciones terminen
                setTimeout(function() {
                    window.location.href = "{{ route('home') }}";
                }, 2500);
            }, 1600);
        });
    </script>
</body>

</html>
