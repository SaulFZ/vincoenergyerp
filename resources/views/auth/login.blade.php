<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinco Energy - Acceso</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="{{ asset('assets/css/login/login.css') }}" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <video autoplay muted loop playsinline id="video-bg">
        <source src="/assets/vid/fondov1.mp4" type="video/mp4">
    </video>


    <div class="login-wrapper">

        <div class="logo-container">
            <img src="{{ asset('assets/img/logovinco2.png') }}" alt="Vinco Energy" class="logo-img">
        </div>

        <h2>Bienvenido</h2>
        <p class="subtitle">Inicia sesión para continuar</p>

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
        $(document).ready(function() {

            // --- 0. RECARGA AUTOMÁTICA POR EXPIRACIÓN (NUEVO) ---
            // Si la sesión dura 10 minutos (600,000 ms), recargamos la página
            // automáticamente al pasar ese tiempo para renovar el token.
            setTimeout(function() {
                window.location.reload();
            }, 600000); // 10 minutos


            // --- 1. AÑO AUTOMÁTICO ---
            $('#yearSpan').text(new Date().getFullYear());


            // --- 2. COLOREAR ICONO AL ESCRIBIR ---
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


            // --- 3. RECARGA PESTAÑA OCULTA ---
            let wasHidden = false;
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) wasHidden = true;
                else if (wasHidden) window.location.reload();
            });


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


            // --- 5. LOGIN AJAX (SILENCIOSO) ---
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
                        if (resp.success) {
                            window.location.href = "{{ route('splash') }}";
                        } else {
                            showError(resp.message);
                            btn.removeClass('loading').prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        // SI ES ERROR 419 (Token Vencido), RECARGAMOS SIN AVISAR
                        if (xhr.status === 419) {
                            window.location.reload();
                        } else {
                            // Si es otro error (internet, servidor 500), mostramos mensaje
                            console.error(error);
                            showError('Sin conexión al servidor.');
                            btn.removeClass('loading').prop('disabled', false);
                        }
                    }
                });
            });

            function showError(msg) {
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
