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
        /* --- VARIABLES --- */
        :root {
            --azul-oscuro: #1a2a5f;
            --azul: #334c95;
            --naranja: #d67e29;
            --naranja-claro: #ff9d42;
            --blanco: #ffffff;
            --texto: #334155;
            --texto-claro: #64748b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body, html {
            height: 100%;
            width: 100%;
            font-family: 'Montserrat', sans-serif;
            overflow: hidden;
            background-color: var(--blanco);
        }

        .splash-wrapper {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: var(--blanco);
        }

        /* --- ELEMENTOS (Inicialmente ocultos y desplazados para la animación) --- */
        .logo-container {
            margin-bottom: 50px;
            /* Estado inicial para GSAP */
            opacity: 0;
            transform: translateY(30px) scale(0.9);
        }

        .logo-img {
            width: 220px; /* Tamaño grande solicitado */
            height: auto;
            display: block;
        }

        .loader-wrapper {
            width: 360px; /* Ancho grande solicitado */
            text-align: center;
            /* Estado inicial para GSAP */
            opacity: 0;
            transform: translateY(20px);
        }

        .progress-track {
            width: 100%;
            height: 8px;
            background-color: #f1f5f9; /* Un gris muy sutil */
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            margin-bottom: 20px;
        }

        .progress-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--azul) 0%, var(--naranja) 100%);
            border-radius: 10px;
            position: relative;
        }

        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent);
            transform: translateX(-100%);
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer { 100% { transform: translateX(100%); } }

        .text-container {
            height: 50px; /* Altura fija para evitar saltos */
        }

        .status-text {
            font-size: 16px;
            font-weight: 700; /* Más bold como los títulos del login */
            color: var(--azul-oscuro);
            margin-bottom: 5px;
            display: block;
        }

        .sub-status {
            font-size: 13px;
            color: var(--texto-claro);
            font-weight: 500;
        }

        .splash-footer {
            position: absolute;
            bottom: 30px;
            font-size: 12px;
            font-weight: 600;
            color: #cbd5e1;
            opacity: 0;
        }

    </style>
</head>

<body>

    <div class="splash-wrapper">
        <div class="logo-container">
            <img src="{{ asset('assets/img/logovinco2.png') }}" alt="Vinco Energy" class="logo-img">
        </div>

        <div class="loader-wrapper">
            <div class="progress-track">
                <div class="progress-fill"></div>
            </div>
            <div class="text-container">
                <span class="status-text" id="mainText">Iniciando sistema</span>
                <span class="sub-status">Cargando configuraciones...</span>
            </div>
        </div>

        <div class="splash-footer">VINCO ENERGY SERVICES &copy; {{ date('Y') }}</div>
    </div>

    <script>
        $(document).ready(function() {
            const userName = "{{ Auth::check() ? Auth::user()->name : 'Usuario' }}";
            const homeRoute = "{{ route('home') }}";

            const tl = gsap.timeline();

            // --- 1. ENTRADA SINCRONIZADA CON EL LOGIN ---
            // Usamos 'power3.out' y movimiento en Y, igual que tu Login.

            // A. El Logo entra primero
            tl.to(".logo-container", {
                duration: 1,
                opacity: 1,
                y: 0,
                scale: 1,
                ease: "back.out(1.2)" // Sutil, no elástico exagerado
            })
            // B. La barra y textos entran justo después (stagger)
            .to(".loader-wrapper", {
                duration: 0.8,
                opacity: 1,
                y: 0,
                ease: "power3.out"
            }, "-=0.6")
            // C. Footer al final
            .to(".splash-footer", {
                duration: 1,
                opacity: 1,
                ease: "power2.out"
            }, "-=0.4");


            // --- 2. PROGRESO DE CARGA ---
            tl.to(".progress-fill", {
                duration: 2.2,
                width: "100%",
                ease: "power1.inOut", // Carga suave, empieza lento, acelera, termina lento
                onUpdate: function() {
                    // Cambio de texto sincronizado
                    if (this.progress() > 0.5 && this.progress() < 0.52) {
                        gsap.to(".sub-status", {
                            opacity: 0,
                            duration: 0.2,
                            onComplete: () => {
                                $(".sub-status").text("Autenticación exitosa...");
                                gsap.to(".sub-status", { opacity: 1, duration: 0.2 });
                            }
                        });
                    }
                }
            });

            // --- 3. TRANSICIÓN FINAL (SALUDO) ---
            tl.to("#mainText", {
                duration: 0.3,
                y: -10,
                opacity: 0,
                ease: "power2.in",
                onComplete: () => {
                    $("#mainText").text("¡Bienvenido, " + userName + "!");
                    $("#mainText").css("color", "var(--azul)");
                    $(".sub-status").text("Accediendo al panel principal");
                }
            })
            .to("#mainText", {
                duration: 0.5,
                y: 0,
                opacity: 1,
                ease: "back.out(1.5)"
            });

            // --- 4. SALIDA HACIA EL HOME ---
            // Esta animación simula "entrar" en la pantalla (Scale up + Fade out)
            tl.to(".splash-wrapper", {
                duration: 0.8,
                opacity: 0,
                scale: 1.05, // Efecto sutil de zoom hacia adentro
                ease: "power2.inOut",
                delay: 0.5,
                onComplete: () => {
                    window.location.href = homeRoute;
                }
            });
        });
    </script>
</body>
</html>
