<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinco Energy - Acceso</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">

    <link href="{{ asset('assets/css/login/login.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
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

        <h2 class="animate-text">Bienvenido</h2>
        <p class="subtitle animate-text">Inicia sesión para continuar</p>

        <form id="loginForm" method="POST" action="{{ route('login') }}">
            @csrf

            <div class="input-group">
                <label>Usuario</label>
                <div class="field-wrapper">
                    <input type="text" class="custom-input" name="username" id="username"
                        value="{{ old('username') }}" placeholder="Ingresa tu usuario" required autocomplete="username">
                    <i class="fas fa-user input-icon"></i>
                </div>
            </div>

            <div class="input-group">
                <label>Contraseña</label>
                <div class="field-wrapper">
                    <input type="password" class="custom-input" name="password" id="password" placeholder="••••••••"
                        required autocomplete="current-password">
                    <i class="fas fa-lock input-icon"></i>
                    <i class="fas fa-eye-slash toggle-pass" id="eyeIcon"></i>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <span>INGRESAR</span>
                <div class="spinner"></div>
            </button>

            <div class="forgot-container">
                <a href="#" id="forgotPasswordLink" class="forgot-link">
                    <i class="fas fa-lock-open"></i> ¿Olvidaste tu contraseña?
                </a>
            </div>

        </form>

        <div class="footer-copyright">
            Vinco Energy &copy; <span id="yearSpan"></span> | Todos los derechos reservados
        </div>
    </div>

    <script>
        // --- 7. ANIMACIONES GSAP (CONTROL DE CARGA) ---
        // Usamos window.load para esperar a que el video y recursos carguen completamente
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
                // 2. Aparecer Video suavemente (evita el glitch visual)
                .to("#video-bg", {
                    duration: 1.5,
                    opacity: 1,
                    visibility: "visible",
                    ease: "power2.out"
                }, "-=0.2")
                // 3. Revelar el contenedor del login
                .to(".login-wrapper", {
                    duration: 1,
                    opacity: 1,
                    y: 0,
                    visibility: "visible",
                    ease: "power3.out"
                }, "-=1")
                // 4. Animación en cascada de los elementos internos
                .from(".logo-img", {
                    duration: 0.8,
                    scale: 0.8,
                    opacity: 0,
                    ease: "back.out(1.7)"
                }, "-=0.6")
                .from(".animate-text", {
                    duration: 0.6,
                    y: 20,
                    opacity: 0,
                    stagger: 0.1,
                    ease: "power2.out"
                }, "-=0.4")
                .from(".input-group", {
                    duration: 0.6,
                    x: -30,
                    opacity: 0,
                    stagger: 0.15,
                    ease: "power2.out"
                }, "-=0.4")
                .from(".btn-login", {
                    duration: 0.6,
                    y: 20,
                    opacity: 0,
                    ease: "power2.out"
                }, "-=0.2")
                .from(".forgot-container, .footer-copyright", {
                    duration: 0.6,
                    opacity: 0,
                    ease: "power2.out"
                }, "-=0.4");
        });

        $(document).ready(function() {

            // --- 0. RECARGA AUTOMÁTICA (Cada 10 Minutos) ---
            setTimeout(function() {
                window.location.reload();
            }, 900000); // 15 minutos

            // --- 1. AÑO AUTOMÁTICO ---
            $('#yearSpan').text(new Date().getFullYear());

            // --- 2. INPUTS FOCUS ---
            function checkInputs() {
                $('.custom-input').each(function() {
                    if ($(this).val().length > 0) {
                        $(this).addClass('has-content');
                    } else {
                        $(this).removeClass('has-content');
                    }
                });
            }
            checkInputs();
            $('.custom-input').on('input change blur focus', checkInputs);
            setTimeout(checkInputs, 200);

            // --- SE ELIMINÓ LA LÓGICA DE RECARGA AL CAMBIAR DE PESTAÑA (visibilitychange) ---

            // --- 4. VER / OCULTAR CONTRASEÑA ---
            $('#eyeIcon').on('click', function() {
                let input = $('#password');
                let icon = $(this);
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                }
            });

          // --- 5. LOGIN AJAX ---
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                let btn = $('.btn-login');
                btn.addClass('loading').prop('disabled', true);

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(resp) {
                        // Solo entra aquí si el código HTTP es 200 (Login exitoso)
                        if (resp.success) {
                            gsap.to(".login-wrapper", {
                                duration: 0.5,
                                opacity: 0,
                                y: -20,
                                ease: "power2.in"
                            });
                            setTimeout(() => {
                                window.location.href = "{{ route('splash') }}";
                            }, 500);
                        } else {
                            showError(resp.message);
                            btn.removeClass('loading').prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        btn.removeClass('loading').prop('disabled', false);

                        if (xhr.status === 419) {
                            // Expiración del token CSRF
                            window.location.reload();
                        } else if (xhr.status === 401) {
                            // 401: Credenciales incorrectas (Devuelto por tu controlador)
                            let response = xhr.responseJSON;
                            showError(response && response.message ? response.message : 'Credenciales inválidas.');
                        } else if (xhr.status === 422) {
                            // 422: Validaciones fallidas de Laravel (Ej. campos vacíos)
                            let errors = xhr.responseJSON.errors;
                            let errorMsg = '';
                            for (let field in errors) {
                                errorMsg += errors[field][0] + '<br>';
                            }
                            showError(errorMsg || 'Por favor, llena los campos requeridos.');
                        } else {
                            // 500 u otros errores de servidor
                            console.error(error);
                            showError('Error de conexión al servidor.');
                        }
                    }
                });
            });

            function showError(msg) {
                // Pequeña animación de "shake" en el formulario si falla
                gsap.fromTo(".login-wrapper", {
                    x: -10
                }, {
                    x: 10,
                    duration: 0.1,
                    repeat: 3,
                    yoyo: true,
                    ease: "power1.inOut"
                });

                Swal.fire({
                    icon: 'error',
                    title: 'Acceso Denegado',
                    text: msg || 'Credenciales incorrectas',
                    confirmButtonText: 'Reintentar',
                    confirmButtonColor: '#334c95'
                });
            }

            // --- 6. RECUPERAR CONTRASEÑA ---
            let userEmail = '';
            $('#forgotPasswordLink').on('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Recuperar Cuenta',
                    html: '<p style="color:#64748b; font-size:14px;">Ingresa tu usuario:</p>',
                    input: 'text',
                    showCancelButton: true,
                    confirmButtonText: 'Buscar',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showLoaderOnConfirm: true,
                    preConfirm: (u) => {
                        if (!u) return Swal.showValidationMessage('Campo obligatorio');
                        return $.post("{{ route('password.getUserEmail') }}", {
                                _token: "{{ csrf_token() }}",
                                username: u
                            })
                            .catch(err => Swal.showValidationMessage('Usuario no encontrado'));
                    }
                }).then((res) => {
                    if (res.isConfirmed) {
                        userEmail = res.value.email;
                        Swal.fire({
                            html: `Enviar código a: <b>${res.value.maskedEmail}</b>`,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Enviar',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showLoaderOnConfirm: true,
                            preConfirm: () => $.post("{{ route('password.sendCode') }}", {
                                _token: "{{ csrf_token() }}",
                                email: userEmail
                            })
                        }).then((r2) => {
                            if (r2.isConfirmed) verifyCode();
                        });
                    }
                });
            });

            function verifyCode() {
                Swal.fire({
                    title: 'Verificación',
                    html: `<div class="otp-row"><input class="otp-inp" maxlength="1"><input class="otp-inp" maxlength="1"><input class="otp-inp" maxlength="1"><input class="otp-inp" maxlength="1"><input class="otp-inp" maxlength="1"><input class="otp-inp" maxlength="1"></div>`,
                    showCancelButton: true,
                    confirmButtonText: 'Validar',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        const i = document.querySelectorAll('.otp-inp');
                        i[0].focus();
                        i.forEach((el, idx) => {
                            el.addEventListener('input', function() {
                                this.value = this.value.replace(/\D/g, '');
                                if (this.value && idx < 5) i[idx + 1].focus();
                            });
                            el.addEventListener('keydown', function(e) {
                                if (e.key === 'Backspace' && !this.value && idx > 0) i[
                                    idx - 1].focus();
                            });
                        });
                    },
                    preConfirm: () => {
                        let c = '';
                        $('.otp-inp').each((_, e) => c += e.value);
                        if (c.length < 6) return Swal.showValidationMessage('Código incompleto');
                        return $.post("{{ route('password.verifyCode') }}", {
                            _token: "{{ csrf_token() }}",
                            email: userEmail,
                            code: c
                        }).then(r => r.redirect).catch(() => Swal.showValidationMessage(
                            'Código incorrecto'));
                    }
                }).then(r => {
                    if (r.isConfirmed && r.value) window.location.href = r.value;
                });
            }

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    text: "{{ session('success') }}"
                });
            @endif
            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    text: "{{ session('error') }}"
                });
            @endif
        });
    </script>
</body>

</html>




{{--
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinco Energy - Mantenimiento</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">

    <link href="{{ asset('assets/css/login/login.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

    <style>
        /* Ajuste de diseño corporativo y colores a NEGRO */
        .migration-card {
            text-align: center;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .status-badge {
            background: rgba(0, 0, 0, 0.05);
            color: #000000;
            padding: 6px 18px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            border: 1px solid rgba(0, 0, 0, 0.2);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Punto parpadeante VERDE para indicar actividad */
        .pulse-dot {
            width: 8px;
            height: 8px;
            background-color: #22c55e; /* Verde */
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }

        .migration-icon {
            font-size: 3rem;
            color: #000000;
            margin-bottom: 15px;
        }

        h2.animate-text {
            color: #000000 !important;
            font-weight: 800;
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }

        .migration-text {
            color: #000000;
            font-size: 1.05rem;
            line-height: 1.6;
            margin: 15px 0;
            font-weight: 500;
            max-width: 320px;
        }

        .highlight-text {
            color: #000000;
            font-weight: 800;
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        /* Barra de progreso infinita elegante */
        .loading-line-container {
            width: 80%;
            height: 2px;
            background: rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            position: relative;
            overflow: hidden;
            border-radius: 2px;
        }

        .loading-line {
            width: 40%;
            height: 100%;
            background: #000000;
            position: absolute;
            left: -40%;
            animation: loadingBar 2s infinite ease-in-out;
            border-radius: 2px;
        }

        @keyframes loadingBar {
            0% { left: -40%; width: 30%; }
            50% { width: 60%; }
            100% { left: 100%; width: 30%; }
        }

        .support-info {
            font-size: 12px;
            color: #000000;
            font-weight: 700;
            opacity: 0.8;
            letter-spacing: 0.5px;
        }

        .footer-copyright {
            color: #000000 !important;
            font-weight: 600;
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

        <div class="migration-card">
            <div class="status-badge animate-text">
                <div class="pulse-dot"></div> Mantenimiento Activo
            </div>

            <i class="fas fa-server migration-icon animate-text"></i>

            <h2 class="animate-text">ACTUALIZACIÓN DE SERVIDOR</h2>

            <div class="migration-text animate-text">
                <p><strong>Vinco ERP</strong> se encuentra en un proceso de <span class="highlight-text">MIGRACIÓN DE INFRAESTRUCTURA</span>.</p>
                <p style="font-size: 0.90rem; margin-top: 10px;">Esto <b>NO ES UN FALLO</b>. Estamos optimizando nuestros servicios para garantizar mayor estabilidad y velocidad.</p>
            </div>

            <div class="loading-line-container animate-text">
                <div class="loading-line"></div>
            </div>

            <div class="animate-text" style="margin-top: 10px;">
                <p class="support-info">EL ACCESO SE RESTABLECERÁ AUTOMÁTICAMENTE</p>
            </div>
        </div>

        <div class="footer-copyright">
            Vinco Energy &copy; <span id="yearSpan"></span> | Todos los derechos reservados
        </div>
    </div>

    <script>
        window.addEventListener("load", () => {
            const tl = gsap.timeline();

            tl.to("#preloader", {
                    duration: 0.6,
                    opacity: 0,
                    ease: "power2.inOut",
                    onComplete: () => {
                        document.querySelector("#preloader").style.display = "none";
                    }
                })
                .to("#video-bg", {
                    duration: 1.5,
                    opacity: 1,
                    visibility: "visible",
                    ease: "power2.out"
                }, "-=0.2")
                .to(".login-wrapper", {
                    duration: 1,
                    opacity: 1,
                    y: 0,
                    visibility: "visible",
                    ease: "power3.out"
                }, "-=1")
                .from(".logo-img", {
                    duration: 0.8,
                    scale: 0.8,
                    opacity: 0,
                    ease: "back.out(1.7)"
                }, "-=0.6")
                .from(".animate-text", {
                    duration: 0.6,
                    y: 20,
                    opacity: 0,
                    stagger: 0.15,
                    ease: "power2.out"
                }, "-=0.4")
                .from(".footer-copyright", {
                    duration: 0.6,
                    opacity: 0,
                    ease: "power2.out"
                }, "-=0.2");
        });

        $(document).ready(function() {
            $('#yearSpan').text(new Date().getFullYear());
        });
    </script>
</body>

</html>









 --}}
