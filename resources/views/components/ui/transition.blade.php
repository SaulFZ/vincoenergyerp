<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinco - Cargando Módulo</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"
        integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8="
        crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-light: #738bfa;
            --secondary-color: #3a0ca3;
            --accent-color: #4cc9f0;
            --accent-light: #73e5ff;
            --text-color: #2b2d42;
            --light-text: #6c757d;
            --background: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 10px 30px rgba(67, 97, 238, 0.1);
        }

        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            background-color: var(--background);
            font-family: 'Poppins', sans-serif;
            overflow: hidden;
            color: var(--text-color);
        }

        .transition-container {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }

        .transition-content {
            z-index: 10;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px;
            background: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 400px;
            width: 90%;
            position: relative;
            overflow: hidden;
        }

        .transition-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            z-index: 1;
        }

        .module-icon {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-light), var(--primary-color));
            color: var(--white);
            border-radius: 50%;
            margin-bottom: 20px;
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.2);
            font-size: 32px;
            opacity: 0;
            transform: scale(0.8) translateY(-10px);
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* Estilo para la imagen del módulo */
        .module-image-container {
            width: 100px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            opacity: 0;
            transform: scale(0.8) translateY(-10px);
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .module-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .module-name {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 12px;
            opacity: 0;
            transform: translateY(15px);
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            background: linear-gradient(90deg, var(--text-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .module-description {
            font-size: 15px;
            color: var(--light-text);
            margin-bottom: 25px;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            max-width: 320px;
            text-align: center;
            line-height: 1.5;
        }

        .loader-container {
            width: 100%;
            margin-bottom: 15px;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .loader {
            width: 100%;
            height: 8px;
            border-radius: 4px;
            background-color: rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .loader-bar {
            position: absolute;
            height: 100%;
            width: 30%;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 4px;
            animation: loading 1.2s infinite ease-in-out;
            box-shadow: 0 0 10px rgba(76, 201, 240, 0.3);
        }

        .loading-text {
            font-size: 14px;
            color: var(--light-text);
            margin-top: 8px;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .loading-details {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            font-size: 12px;
            color: var(--light-text);
            margin-top: 5px;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .loading-status {
            font-weight: 500;
            color: var(--primary-color);
        }

        @keyframes loading {
            0% {
                left: -30%;
            }
            100% {
                left: 100%;
            }
        }

        /* Decoración mejorada */
        .decoration {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .decoration-item {
            position: absolute;
            border-radius: 50%;
            opacity: 0.7;
            filter: blur(5px);
            transition: all 3s ease-in-out;
        }

        .item-1 {
            width: 150px;
            height: 150px;
            top: 15%;
            left: 10%;
            background: linear-gradient(135deg, var(--primary-light), var(--primary-color));
            animation: float 8s infinite alternate ease-in-out;
        }

        .item-2 {
            width: 100px;
            height: 100px;
            bottom: 20%;
            right: 15%;
            background: linear-gradient(135deg, var(--accent-light), var(--accent-color));
            animation: float 6s infinite alternate-reverse ease-in-out;
        }

        .item-3 {
            width: 80px;
            height: 80px;
            top: 60%;
            left: 20%;
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            animation: float 9s infinite alternate ease-in-out;
        }

        .item-4 {
            width: 120px;
            height: 120px;
            top: 30%;
            right: 25%;
            background: linear-gradient(135deg, var(--accent-color), var(--primary-light));
            animation: float 7s infinite alternate-reverse ease-in-out;
        }

        /* Elementos decorativos adicionales */
        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: 2;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            opacity: 0.2;
            border-radius: 50%;
            background-color: var(--primary-color);
            animation: particle-float 20s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(0) translateX(0);
            }
            100% {
                transform: translateY(20px) translateX(20px);
            }
        }

        @keyframes particle-float {
            0% {
                transform: translateY(0) translateX(0) rotate(0deg);
            }
            100% {
                transform: translateY(-100vh) translateX(100px) rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <div class="transition-container">
        <div class="decoration">
            <div class="decoration-item item-1"></div>
            <div class="decoration-item item-2"></div>
            <div class="decoration-item item-3"></div>
            <div class="decoration-item item-4"></div>
        </div>

        <div class="particles" id="particles"></div>

        <div class="transition-content">
            <!-- Contenedor para la imagen del módulo -->
            <div class="module-image-container" id="moduleImageContainer">
                <img id="moduleImage" class="module-image" src="" alt="Imagen de módulo">
            </div>

            <!-- Icono alternativo (se muestra solo si no hay imagen) -->
            <div class="module-icon" id="moduleIconContainer">
                <i id="moduleIcon" class="fa-solid fa-circle-notch"></i>
            </div>

            <div class="module-name" id="moduleName">Cargando módulo...</div>
            <div class="module-description" id="moduleDescription"></div>

            <div class="loader-container">
                <div class="loader">
                    <div class="loader-bar"></div>
                </div>
                <div class="loading-details">
                    <span>Inicializando...</span>
                    <span class="loading-status" id="loadingStatus">0%</span>
                </div>
            </div>

            <div class="loading-text">Preparando tu experiencia Vinco</div>
        </div>
    </div>

    <script>
        // Obtiene el nombre del módulo y el ícono desde los parámetros de URL
        function getQueryParam(param) {
            var urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }

        // Crear partículas decorativas
        function createParticles() {
            const container = document.getElementById('particles');
            const particleCount = 30;

            for (let i = 0; i < particleCount; i++) {
                const size = Math.random() * 5 + 2;
                const particle = document.createElement('div');
                particle.classList.add('particle');

                // Posición inicial aleatoria
                const posX = Math.random() * 100;
                const posY = Math.random() * 100 + 100; // Empezar desde abajo

                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${posX}%`;
                particle.style.bottom = `${posY}%`;
                particle.style.opacity = Math.random() * 0.3 + 0.1;

                // Duración y retraso aleatorios para animación
                const duration = Math.random() * 20 + 10;
                const delay = Math.random() * 5;

                particle.style.animation = `particle-float ${duration}s ${delay}s infinite linear`;

                container.appendChild(particle);
            }
        }

        $(document).ready(function() {
            // Crear partículas
            createParticles();

            // Obtener parámetros de URL o usar valores predeterminados
            var moduleName = getQueryParam('module') || 'Vinco';
            var moduleIcon = getQueryParam('icon') || 'fa-circle-notch';
            var moduleDescription = getQueryParam('description') || 'Estamos preparando todo para ofrecerte la mejor experiencia';
            var moduleImage = getQueryParam('image') || ''; // Nueva URL de imagen
            var redirectTo = getQueryParam('redirect') || "{{ route('home') }}";

            // Actualizar contenido
            $("#moduleName").text("Cargando " + moduleName);
            $("#moduleDescription").text(moduleDescription);

            // Gestionar la imagen o el icono según corresponda
            if (moduleImage && moduleImage !== '') {
                // Si hay imagen, mostrarla y ocultar el icono
                $("#moduleImage").attr("src", moduleImage);
                $("#moduleImageContainer").show();
                $("#moduleIconContainer").hide();
            } else {
                // Si no hay imagen, mostrar el icono y ocultar el contenedor de imagen
                $("#moduleIcon").removeClass().addClass("fas " + moduleIcon);
                $("#moduleImageContainer").hide();
                $("#moduleIconContainer").show();
            }

            // Secuencia de animación mejorada
            setTimeout(function() {
                // Mostrar la imagen o el icono, dependiendo de cuál esté visible
                $("#moduleImageContainer, #moduleIconContainer").css({
                    'opacity': '1',
                    'transform': 'scale(1) translateY(0)'
                });

                setTimeout(function() {
                    $(".module-name").css({
                        'opacity': '1',
                        'transform': 'translateY(0)'
                    });

                    // Mostrar descripción
                    setTimeout(function() {
                        $(".module-description").css({
                            'opacity': '0.8',
                            'transform': 'translateY(0)'
                        });

                        setTimeout(function() {
                            $(".loader-container, .loading-text, .loading-details").css('opacity', '1');

                            // Simular progreso
                            let progress = 0;
                            const progressInterval = setInterval(function() {
                                progress += Math.floor(Math.random() * 10) + 5;
                                if (progress >= 100) {
                                    progress = 100;
                                    clearInterval(progressInterval);

                                    // Redireccionar después de completar
                                    setTimeout(function() {
                                        window.location.href = redirectTo;
                                    }, 300);
                                }
                                $("#loadingStatus").text(progress + "%");
                            }, 200);

                        }, 200);
                    }, 200);
                }, 200);
            }, 200);
        });
    </script>

</body>
</html>
