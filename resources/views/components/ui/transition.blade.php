<!-- resources/views/components/ui/transition.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinco - Cargando Módulo</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"
        integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8="
        crossorigin="anonymous"></script>
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
            background: var(--white);
        }

        .transition-content {
            z-index: 1;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 20px;
        }

        .module-icon {
            font-size: 40px;
            color: var(--primary-color);
            margin-bottom: 15px;
            opacity: 0;
            transform: scale(0.8);
            transition: all 0.4s ease;
        }

        .module-name {
            font-size: 22px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 5px;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.4s ease;
        }

        .module-description {
            font-size: 14px;
            color: var(--light-text);
            margin-bottom: 15px;
            opacity: 0;
            transform: translateY(8px);
            transition: all 0.4s ease;
            max-width: 320px;
            text-align: center;
        }

        .loader {
            width: 60px;
            height: 6px;
            border-radius: 3px;
            background-color: rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
            margin-bottom: 10px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .loader-bar {
            position: absolute;
            height: 100%;
            width: 30%;
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--accent-color) 100%);
            border-radius: 3px;
            animation: loading 1s infinite ease-in-out;
        }

        .loading-text {
            font-size: 14px;
            color: var(--light-text);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        @keyframes loading {
            0% {
                left: -30%;
            }
            100% {
                left: 100%;
            }
        }

        /* Decoración sutil */
        .decoration {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            opacity: 0.2;
        }

        .decoration-item {
            position: absolute;
            border-radius: 50%;
            background: var(--primary-color);
            opacity: 0.1;
        }

        .item-1 {
            width: 100px;
            height: 100px;
            top: 10%;
            left: 5%;
        }

        .item-2 {
            width: 80px;
            height: 80px;
            bottom: 15%;
            right: 10%;
            background: var(--accent-color);
        }
    </style>
</head>
<body>
    <div class="transition-container">
        <div class="decoration">
            <div class="decoration-item item-1"></div>
            <div class="decoration-item item-2"></div>
        </div>

        <div class="transition-content">
            <div class="module-icon">
                <i id="moduleIcon" class="fa-solid fa-circle-notch"></i>
            </div>
            <div class="module-name" id="moduleName">Cargando módulo...</div>
            <div class="module-description" id="moduleDescription"></div>
            <div class="loader">
                <div class="loader-bar"></div>
            </div>
            <div class="loading-text">Preparando interfaz</div>
        </div>
    </div>

    <script>
        // Obtiene el nombre del módulo y el ícono desde los parámetros de URL
        function getQueryParam(param) {
            var urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }

        $(document).ready(function() {
            // Obtener parámetros de URL o usar valores predeterminados
            var moduleName = getQueryParam('module') || 'Vinco';
            var moduleIcon = getQueryParam('icon') || 'fa-circle-notch';
            var moduleDescription = getQueryParam('description') || '';
            var redirectTo = getQueryParam('redirect') || "{{ route('home') }}";

            // Actualizar contenido
            $("#moduleName").text("Cargando " + moduleName);
            $("#moduleIcon").removeClass().addClass("fas " + moduleIcon);
            $("#moduleDescription").text(moduleDescription);

            // Secuencia de animación (más ligera)
            setTimeout(function() {
                $(".module-icon").css({
                    'opacity': '1',
                    'transform': 'scale(1)'
                });

                setTimeout(function() {
                    $(".module-name").css({
                        'opacity': '1',
                        'transform': 'translateY(0)'
                    });

                    // Mostrar descripción si existe
                    if(moduleDescription) {
                        setTimeout(function() {
                            $(".module-description").css({
                                'opacity': '0.8',
                                'transform': 'translateY(0)'
                            });
                        }, 100);
                    }

                    setTimeout(function() {
                        $(".loader, .loading-text").css('opacity', '1');

                        // Redireccionar después de un tiempo más corto
                        setTimeout(function() {
                            window.location.href = redirectTo;
                        }, 800);
                    }, 200);
                }, 150);
            }, 100);
        });
    </script>

    <!-- Font Awesome para iconos - agregar si es necesario -->
    <script src="https://kit.fontawesome.com/your-code-here.js" crossorigin="anonymous"></script>
</body>
</html>
