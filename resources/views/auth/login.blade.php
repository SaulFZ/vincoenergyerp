<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinco Energy - Mantenimiento</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">

    <link href="{{ asset('assets/css/login/login.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

    <style>
        /* Estilos específicos para la vista de mantenimiento */
        .maintenance-card {
            text-align: center;
            padding: 20px 10px;
        }

        .status-badge {
            display: inline-block;
            background: rgba(51, 76, 149, 0.1);
            color: #334c95;
            padding: 6px 18px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .lottie-container {
            display: flex;
            justify-content: center;
            margin: -10px 0;
            /* Ajuste para reducir espacios en blanco de la animación */
        }

        .maintenance-text {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .maintenance-text b {
            color: #334c95;
        }

        .login-wrapper {
            max-width: 480px;
            /* Un poco más ancho para que respiren los textos */
        }
    </style>
</head>

<body>

    <div id="preloader">
        <div class="loader-logo"></div>
    </div>

    <video autoplay muted loop playsinline id="video-bg">
        <source src="/assets/vid/fondov1.mp4" type="video/mp4">
    </video>

    <div class="login-wrapper">

        <div class="logo-container">
            <img src="{{ asset('assets/img/logovinco2.png') }}" alt="Vinco Energy" class="logo-img">
        </div>

        <div class="maintenance-card">

            <div class="status-badge animate-item">Sistema en Actualización</div>

            <div class="lottie-container animate-item">
                <lottie-player src="{{ asset('assets/lottie/Mante.json') }}" background="transparent" speed="1"
                    style="width: 220px; height: 220px;" loop autoplay>
                </lottie-player>
            </div>

            <h2 class="animate-item" style="margin-top: 0;">Estamos mejorando</h2>

            <p class="maintenance-text animate-item">
                Nos encontramos realizando labores de mantenimiento técnico y el despliegue de nuevas funciones para
                ofrecerte una experiencia más rápida, intuitiva y segura.
                <br><br>
                <b>El ingreso estará disponible en cuanto finalicen los trabajos de actualización. Agradecemos tu
                    paciencia.</b>
            </p>

            <div class="footer-copyright animate-item" style="position: static; margin-top: 15px;">
                Vinco Energy &copy; <span id="yearSpan"></span> | Todos los derechos reservados
            </div>

        </div>
    </div>

    <script>
        // --- ANIMACIONES GSAP (CONTROL DE CARGA) ---
        window.addEventListener("load", () => {
            const tl = gsap.timeline();

            // 1. Desaparecer Preloader
            tl.to("#preloader", {
                    duration: 0.6,
                    opacity: 0,
                    ease: "power2.inOut",
                    onComplete: () => {
                        document.querySelector("#preloader").style.display = "none";
                    }
                })
                // 2. Aparecer Video suavemente
                .to("#video-bg", {
                    duration: 1.5,
                    opacity: 1,
                    visibility: "visible",
                    ease: "power2.out"
                }, "-=0.2")
                // 3. Revelar el contenedor principal
                .to(".login-wrapper", {
                    duration: 1,
                    opacity: 1,
                    y: 0,
                    visibility: "visible",
                    ease: "power3.out"
                }, "-=1")
                // 4. Animar el logo
                .from(".logo-img", {
                    duration: 0.8,
                    scale: 0.8,
                    opacity: 0,
                    ease: "back.out(1.7)"
                }, "-=0.6")
                // 5. Animación en cascada para todos los elementos de mantenimiento (incluyendo el Lottie)
                .from(".animate-item", {
                    duration: 0.6,
                    y: 20,
                    opacity: 0,
                    stagger: 0.15,
                    ease: "power2.out"
                }, "-=0.4");
        });

        $(document).ready(function() {
            // --- RECARGA AUTOMÁTICA (Cada 30 Minutos) ---
            setTimeout(function() {
                window.location.reload();
            }, 1800000); // 1800000 ms = 30 minutos

            // --- AÑO AUTOMÁTICO ---
            $('#yearSpan').text(new Date().getFullYear());
        });
    </script>
</body>

</html>
