<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinco - Login</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://use.fontawesome.com/releases/v5.0.1/js/all.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.transit/0.9.12/jquery.transit.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&family=Work+Sans:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}" />

</head>

<body>
    <div id="cont">
        <div id="invContainer">
            <div class="logoCont">
                <img class="logo" src="{{ asset('assets/img/logo.png') }}" alt="Logo Vinco" />
            </div>
            <div class="acptContainer">
                <form id="loginForm" method="POST" action="{{ route('login') }}">
                    @csrf
                    <h1>¡Bienvenido!</h1>
                    <div class="frmContainer">
                        <div class="frmDiv" style="transition-delay: 0.2s">
                            <i class="fas fa-user"></i>
                            <p>Usuario</p>
                            <input type="text" name="username" value="{{ old('username') }}" required />
                        </div>
                        <div class="frmDiv" style="transition-delay: 0.4s">
                            <i class="fas fa-lock"></i>
                            <p>Contraseña</p>
                            <input type="password" name="password" id="password" required />
                            <span class="toggle-password">
                                <i id="eyeIcon" class="fas fa-eye-slash"></i>
                            </span>
                        </div>

                        <div class="frmDiv" style="transition-delay: 0.6s">
                            <button class="acptBtn" type="submit">
                                <span class="btn-text">Login</span>
                                <span class="btn-loader-container">
                                    <div class="loader"></div>
                                </span>
                            </button>
                        </div>
                        <div class="frmDiv text-center" style="transition-delay: 0.8s">
                            <a href="#" id="forgotPasswordLink" class="frgtPas">¿Olvidaste tu contraseña?</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let userEmail = '';
            let userName = '';

            // 🛑 NUEVA LÓGICA DE RECARGA AL VOLVER 🛑
            // Registra si la pestaña ha estado oculta
            let wasHidden = false;

            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    // La pestaña se ocultó (minimizó o se cambió de pestaña)
                    wasHidden = true;
                    console.log("Pestaña oculta. Se marcará para recarga al volver.");
                } else {
                    // La pestaña se hizo visible
                    if (wasHidden) {
                        console.log("⚠️ Pestaña visible de nuevo. Recargando inmediatamente para actualizar el token CSRF.");
                        // Recargar la página de login inmediatamente
                        window.location.reload();
                    }
                    // Restablecer la bandera si no se recargó (o después de la recarga)
                    wasHidden = false;
                }
            });
            // 🛑 FIN NUEVA LÓGICA 🛑

            $('.toggle-password').on('click', function() {
                const passwordInput = $('#password');
                const eyeIcon = $('#eyeIcon');
                const isPasswordVisible = passwordInput.attr('type') === 'text';
                passwordInput.attr('type', isPasswordVisible ? 'password' : 'text');
                eyeIcon.removeClass('fa-eye fa-eye-slash').addClass(isPasswordVisible ? 'fa-eye-slash' : 'fa-eye');
            });

            // Animaciones de entrada (sin cambios)
            (function() {
                setTimeout(function() {
                    $(".logoCont").transition({ scale: 1 }, 700, "ease");
                    setTimeout(function() {
                        $(".logoCont .logo").addClass("loadIn");
                        setTimeout(function() {
                            $(".acptContainer").transition({ height: "420px" });
                            setTimeout(function() {
                                $(".acptContainer").addClass("loadIn");
                                setTimeout(function() {
                                    $(".frmDiv, form h1").addClass("loadIn");
                                }, 500);
                            }, 500);
                        }, 500);
                    }, 1000);
                }, 10);
            })();

            // Envío del login (sin cambios funcionales)
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                const $button = $(this).find('.acptBtn');
                $button.addClass('loading').prop('disabled', true);

                $.ajax({
                    url: $(this).attr('action'),
                    method: $(this).attr('method'),
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            window.location.href = "{{ route('splash') }}";
                        } else {
                            $button.removeClass('loading').prop('disabled', false);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de acceso',
                                text: response.message || 'Credenciales incorrectas',
                                confirmButtonColor: '#ff7b00ef',
                                customClass: { popup: 'swal2-popup', title: 'swal2-title', confirmButton: 'swal2-confirm' }
                            });
                        }
                    },
                    error: function() {
                        $button.removeClass('loading').prop('disabled', false);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al procesar la solicitud',
                            confirmButtonColor: '#ff7b00ef',
                            customClass: { popup: 'swal2-popup', title: 'swal2-title', confirmButton: 'swal2-confirm' }
                        });
                    }
                });
            });

            // PASO 1: Solicitar nombre de usuario (sin cambios)
            $('#forgotPasswordLink').on('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Restablecer Contraseña',
                    html: `<p>Ingresa tu <span class="swal-highlight">nombre de usuario</span> para verificar tu cuenta:</p>`,
                    input: 'text',
                    inputPlaceholder: 'tu.usuario',
                    showCancelButton: true,
                    confirmButtonText: 'Continuar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#ff7b00ef',
                    customClass: {
                        popup: 'swal2-popup',
                        title: 'swal2-title',
                        confirmButton: 'swal2-confirm',
                        cancelButton: 'swal2-cancel',
                        input: 'swal2-input'
                    },
                    showLoaderOnConfirm: true,
                    preConfirm: (username) => {
                        if (!username) {
                            Swal.showValidationMessage('El nombre de usuario es obligatorio.');
                            return false;
                        }
                        return $.ajax({
                            url: "{{ route('password.getUserEmail') }}",
                            method: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                username: username
                            },
                            dataType: 'json'
                        }).catch(error => {
                            Swal.showValidationMessage(`Error: ${error.responseJSON.message || 'Usuario no encontrado'}`);
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        userEmail = result.value.email;
                        userName = result.value.userName;
                        showSendCodeStep(result.value.maskedEmail);
                    }
                });
            });

            // PASO 2: Mostrar correo y enviar código (sin cambios)
            function showSendCodeStep(maskedEmail) {
                Swal.fire({
                    title: 'Confirmar Correo',
                    html: `
                        <p style="font-size: 15px;">Se enviará un código de verificación de <span class="swal-highlight">6 dígitos</span> a:</p>
                        <p class="masked-email">${maskedEmail}</p>
                        <p style="font-size: 12px; color: #666; margin-top: 15px; font-weight: 500;">
                            ⚠️ El código será válido por <span class="swal-highlight">5 minutos</span>.
                        </p>
                        <div style="background-color: #ebf8ff; border-radius: 5px; padding: 10px; margin-top: 10px; text-align: left; border: 1px solid #90cdf4;">
                            <p style="font-size: 11px; color: #2c5282; margin: 0; line-height: 1.4;">
                                <strong>Nota:</strong> Al realizar el cambio de contraseña, el área de sistemas ya no tendrá acceso al control sobre la misma. A partir de ese momento, la gestión y resguardo de la contraseña será responsabilidad exclusiva del usuario.
                            </p>
                        </div>
                    `,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-paper-plane"></i> Enviar Código',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#ff7b00ef',
                    customClass: {
                        popup: 'swal2-popup',
                        title: 'swal2-title',
                        confirmButton: 'swal2-confirm',
                        cancelButton: 'swal2-cancel'
                    },
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return $.ajax({
                            url: "{{ route('password.sendCode') }}",
                            method: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                email: userEmail,
                            },
                            dataType: 'json'
                        }).catch(error => {
                            Swal.showValidationMessage(`Error: ${error.responseJSON.message || 'No se pudo enviar el código'}`);
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.close();
                        showVerifyCodeStep();
                    }
                });
            }

            // PASO 3: Ingresar código de 6 dígitos (sin cambios)
            function showVerifyCodeStep() {
                Swal.fire({
                    title: 'Ingresa el Código de Verificación',
                    html: `
                        <p style="font-size: 15px; margin-bottom: 25px;">Revisa tu correo e ingresa el código de 6 dígitos enviado.</p>
                        <div class="code-input-container">
                            <input type="text" class="code-input" maxlength="1" data-index="0">
                            <input type="text" class="code-input" maxlength="1" data-index="1">
                            <input type="text" class="code-input" maxlength="1" data-index="2">
                            <input type="text" class="code-input" maxlength="1" data-index="3">
                            <input type="text" class="code-input" maxlength="1" data-index="4">
                            <input type="text" class="code-input" maxlength="1" data-index="5">
                        </div>
                        <p style="font-size: 12px; color: #666; margin-top: 5px;">El código tiene una validez de 5 minutos.</p>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-check"></i> Verificar Código',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#ff7b00ef',
                    customClass: {
                        popup: 'swal2-popup',
                        title: 'swal2-title',
                        confirmButton: 'swal2-confirm',
                        cancelButton: 'swal2-cancel'
                    },
                    showLoaderOnConfirm: true,
                    didOpen: () => {
                        const inputs = document.querySelectorAll('.code-input');
                        inputs[0].focus();

                        inputs.forEach((input, index) => {
                            input.addEventListener('input', function(e) {
                                this.value = this.value.replace(/[^0-9]/g, '');

                                if (this.value.length === 1 && index < 5) {
                                    inputs[index + 1].focus();
                                }
                                const allFilled = Array.from(inputs).every(i => i.value.length === 1);
                                Swal.getConfirmButton().disabled = !allFilled;
                            });

                            input.addEventListener('keydown', function(e) {
                                if (e.key === 'Backspace' && this.value === '' && index > 0) {
                                    inputs[index - 1].focus();
                                }
                            });

                            input.addEventListener('keypress', function(e) {
                                if (!/[0-9]/.test(e.key)) {
                                    e.preventDefault();
                                }
                            });
                        });
                        Swal.getConfirmButton().disabled = true;
                    },
                    preConfirm: () => {
                        const inputs = document.querySelectorAll('.code-input');
                        const code = Array.from(inputs).map(input => input.value).join('');

                        if (code.length !== 6 || !/^\d{6}$/.test(code)) {
                            Swal.showValidationMessage('Debes ingresar los 6 dígitos numéricos.');
                            return false;
                        }

                        return $.ajax({
                            url: "{{ route('password.verifyCode') }}",
                            method: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                email: userEmail,
                                code: code
                            },
                            dataType: 'json'
                        }).then(response => {
                            return response.redirect;
                        }).catch(error => {
                            Swal.showValidationMessage(`Error: ${error.responseJSON.message || 'Código inválido o expirado'}`);
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        window.location.href = result.value;
                    }
                });
            }

            // Manejo de mensajes de sesión existentes (Laravel Flash Messages - sin cambios)
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: "{{ session('success') }}",
                    confirmButtonColor: '#ff7b00ef',
                    customClass: { popup: 'swal2-popup', title: 'swal2-title', confirmButton: 'swal2-confirm' }
                });
            @endif
            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: "{{ session('error') }}",
                    confirmButtonColor: '#ff7b00ef',
                    customClass: { popup: 'swal2-popup', title: 'swal2-title', confirmButton: 'swal2-confirm' }
                });
            @endif
        });
    </script>
</body>

</html>
