<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinco - Iniciando...</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

    <style>
        :root {
            --azul-oscuro: #1a2a5f;
            --azul: #334c95;
            --azul-medio: #2a3d7a;
            --azul-claro: #5b7fd4;
            --azul-brillante: #7fa4f2;
            --naranja: #d67e29;
            --blanco: #ffffff;
            --texto: #334155;
            --texto-claro: #64748b;
            --fondo-track: #e8edf8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body, html {
            height: 100%;
            width: 100%;
            font-family: 'Montserrat', sans-serif;
            overflow: hidden;
            background-color: var(--blanco);
        }

        /* === FONDO CON TEXTURA SUTIL === */
        .splash-wrapper {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: var(--blanco);
            position: relative;
        }

        /* Círculos decorativos de fondo */
        .bg-circle {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }

        .bg-circle-1 {
            width: 500px;
            height: 500px;
            top: -180px;
            right: -160px;
            background: radial-gradient(circle, rgba(51, 76, 149, 0.07) 0%, transparent 70%);
        }

        .bg-circle-2 {
            width: 400px;
            height: 400px;
            bottom: -140px;
            left: -120px;
            background: radial-gradient(circle, rgba(26, 42, 95, 0.06) 0%, transparent 70%);
        }

        .bg-circle-3 {
            width: 180px;
            height: 180px;
            top: 20%;
            left: 10%;
            background: radial-gradient(circle, rgba(91, 127, 212, 0.05) 0%, transparent 70%);
        }

        /* === LÍNEA DECORATIVA SUPERIOR === */
        .top-accent {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, transparent 0%, var(--azul) 30%, var(--azul-claro) 60%, transparent 100%);
            opacity: 0;
        }

        /* === LOGO === */
        .logo-container {
            margin-bottom: 52px;
            opacity: 0;
            transform: translateY(30px) scale(0.9);
            position: relative;
        }

        /* Halo sutil detrás del logo */
        .logo-container::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 260px;
            height: 90px;
            background: radial-gradient(ellipse, rgba(51, 76, 149, 0.08) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .logo-img {
            width: 220px;
            height: auto;
            display: block;
            position: relative;
            z-index: 1;
        }

        /* === LOADER === */
        .loader-wrapper {
            width: 360px;
            text-align: center;
            opacity: 0;
            transform: translateY(20px);
        }

        /* === BARRA DE PROGRESO === */
        .progress-track {
            width: 100%;
            height: 6px;
            background-color: var(--fondo-track);
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            margin-bottom: 22px;
            box-shadow: inset 0 1px 3px rgba(26, 42, 95, 0.08);
        }

        .progress-fill {
            height: 100%;
            width: 0%;
            /* Solo azules: del azul oscuro al azul claro/brillante */
            background: linear-gradient(
                90deg,
                var(--azul-oscuro) 0%,
                var(--azul) 40%,
                var(--azul-claro) 75%,
                var(--azul-brillante) 100%
            );
            border-radius: 10px;
            position: relative;
        }

        /* Shimmer animado sobre la barra */
        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(
                90deg,
                transparent 0%,
                rgba(255, 255, 255, 0.45) 50%,
                transparent 100%
            );
            transform: translateX(-100%);
            animation: shimmer 1.8s infinite;
        }

        /* Punto luminoso al final de la barra */
        .progress-fill::before {
            content: '';
            position: absolute;
            top: 50%;
            right: 0;
            transform: translateY(-50%);
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--azul-brillante);
            box-shadow: 0 0 8px 3px rgba(127, 164, 242, 0.6);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .progress-fill.active::before {
            opacity: 1;
        }

        @keyframes shimmer { 100% { transform: translateX(100%); } }

        /* === TEXTOS === */
        .text-container {
            height: 54px;
        }

        .status-text {
            font-size: 16px;
            font-weight: 700;
            color: var(--azul-oscuro);
            margin-bottom: 6px;
            display: block;
            letter-spacing: -0.2px;
        }

        .sub-status {
            font-size: 12px;
            color: var(--texto-claro);
            font-weight: 500;
            letter-spacing: 0.3px;
            display: block;
        }

        /* === PUNTOS INDICADORES === */
        .dots-row {
            display: flex;
            justify-content: center;
            gap: 6px;
            margin-top: 28px;
            opacity: 0;
        }

        .dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background-color: var(--fondo-track);
            transition: background-color 0.4s ease, transform 0.4s ease;
        }

        .dot.active {
            background-color: var(--azul);
            transform: scale(1.3);
        }

        .dot.done {
            background-color: var(--azul-claro);
        }

        /* === FOOTER === */
        .splash-footer {
            position: absolute;
            bottom: 30px;
            font-size: 11px;
            font-weight: 600;
            color: #1a3b8a;
            letter-spacing: 1.5px;
            opacity: 0;
        }
    </style>
</head>

<body>
    <div class="splash-wrapper">

        <!-- Fondo decorativo -->
        <div class="bg-circle bg-circle-1"></div>
        <div class="bg-circle bg-circle-2"></div>
        <div class="bg-circle bg-circle-3"></div>
        <div class="top-accent"></div>

        <!-- Logo -->
        <div class="logo-container">
            <img src="{{ asset('assets/img/logovinco2.png') }}" alt="Vinco Energy" class="logo-img">
        </div>

        <!-- Loader -->
        <div class="loader-wrapper">
            <div class="progress-track">
                <div class="progress-fill"></div>
            </div>
            <div class="text-container">
                <span class="status-text" id="mainText">Iniciando sistema</span>
                <span class="sub-status" id="subText">Cargando configuraciones...</span>
            </div>

            <!-- Indicadores de paso -->
            <div class="dots-row" id="dotsRow">
                <div class="dot active" data-step="0"></div>
                <div class="dot" data-step="1"></div>
                <div class="dot" data-step="2"></div>
                <div class="dot" data-step="3"></div>
            </div>
        </div>

        <div class="splash-footer">Vinco Energy Services &copy; {{ date('Y') }}</div>
    </div>

    <script>
        $(document).ready(function () {
            const userName = "{{ Auth::check() ? Auth::user()->name : 'Usuario' }}";
            const homeRoute = "{{ route('home') }}";

            const steps = [
                { main: "Iniciando sistema",       sub: "Cargando configuraciones..." },
                { main: "Verificando sesión",       sub: "Autenticación exitosa..." },
                { main: "Preparando módulos",       sub: "Cargando datos del panel..." },
                { main: "¡Listo!",                  sub: "Accediendo al panel principal" },
            ];

            // Activar punto luminoso al iniciar
            $(".progress-fill").addClass("active");

            const tl = gsap.timeline();

            // 1. Acento superior
            tl.to(".top-accent", {
                duration: 0.8,
                opacity: 1,
                ease: "power2.out"
            })

            // 2. Logo entra
            .to(".logo-container", {
                duration: 1,
                opacity: 1,
                y: 0,
                scale: 1,
                ease: "back.out(1.2)"
            }, "-=0.4")

            // 3. Loader entra
            .to(".loader-wrapper", {
                duration: 0.8,
                opacity: 1,
                y: 0,
                ease: "power3.out"
            }, "-=0.6")

            // 4. Dots aparecen
            .to("#dotsRow", {
                duration: 0.5,
                opacity: 1,
                ease: "power2.out"
            }, "-=0.3")

            // 5. Footer aparece
            .to(".splash-footer", {
                duration: 1,
                opacity: 1,
                ease: "power2.out"
            }, "-=0.4");

            // 6. Progreso de carga con cambios de texto
            let lastStep = -1;
            tl.to(".progress-fill", {
                duration: 2.4,
                width: "100%",
                ease: "power1.inOut",
                onUpdate: function () {
                    const p = this.progress();
                    let stepIndex = 0;
                    if (p >= 0.75) stepIndex = 3;
                    else if (p >= 0.5) stepIndex = 2;
                    else if (p >= 0.25) stepIndex = 1;

                    if (stepIndex !== lastStep) {
                        lastStep = stepIndex;

                        // Actualizar texto con fade
                        gsap.to(["#mainText", "#subText"], {
                            opacity: 0, y: -6, duration: 0.2,
                            onComplete: () => {
                                $("#mainText").text(steps[stepIndex].main);
                                $("#subText").text(steps[stepIndex].sub);
                                gsap.to(["#mainText", "#subText"], { opacity: 1, y: 0, duration: 0.3 });
                            }
                        });

                        // Actualizar dots
                        $(".dot").each(function (i) {
                            if (i < stepIndex) {
                                $(this).removeClass("active").addClass("done");
                            } else if (i === stepIndex) {
                                $(this).addClass("active").removeClass("done");
                            } else {
                                $(this).removeClass("active done");
                            }
                        });
                    }
                }
            });

            // 7. Saludo final
            tl.to(["#mainText", "#subText"], {
                duration: 0.3, y: -10, opacity: 0, ease: "power2.in",
                onComplete: () => {
                    $("#mainText").text("¡Bienvenido, " + userName + "!")
                                  .css("color", "var(--azul)");
                    $("#subText").text("Accediendo al panel principal");
                }
            })
            .to(["#mainText", "#subText"], {
                duration: 0.5, y: 0, opacity: 1, ease: "back.out(1.5)"
            });

            // 8. Salida hacia home
            tl.to(".splash-wrapper", {
                duration: 0.9,
                opacity: 0,
                scale: 1.04,
                ease: "power2.inOut",
                delay: 0.6,
                onComplete: () => {
                    window.location.href = homeRoute;
                }
            });
        });
    </script>
</body>
</html>
