<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinco - Restablecer Contraseña</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://use.fontawesome.com/releases/v5.0.1/js/all.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.transit/0.9.12/jquery.transit.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&family=Work+Sans:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}" />
    <style>
        .frmDiv {
            /* Ajuste para mantener la separación con mensajes de error */
            margin-bottom: 25px !important;
        }
        /* Alineación del icono de sobre para el input de email */
        .frmDiv .fa-envelope {
            position: absolute;
            top: 25px;
            left: 10px;
            color: #ffffff;
            font-size: 18px;
        }
        /* Ajuste de altura del contenedor de login para el formulario de reset */
        .acptContainer {
            height: 520px;
        }
        /* Estilo para el mensaje de error de validación */
        .error-message {
            display: block;
            color: #ff4444; /* Un rojo más vivo */
            font-size: 11px;
            margin-top: 5px;
            margin-left: 5px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div id="cont">
        <div id="invContainer">
            <div class="logoCont">
                <img class="logo" src="{{ asset('assets/img/logo.png') }}" alt="Logo Vinco" />
            </div>
            <div class="acptContainer">
                <form id="resetForm" method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <input type="hidden" name="code" value="{{ $token }}">

                    <h1>Establece tu Nueva Contraseña</h1>

                    <div class="frmContainer">

                        <div class="frmDiv" style="transition-delay: 0.2s">
                            <i class="fas fa-envelope"></i>
                            <p>Correo de Recuperación</p>
                            <input type="email" value="{{ $email }}" required readonly class="email-readonly" />
                            <small class="email-note">Se utilizará este correo con el código validado.</small>
                        </div>

                        <div class="frmDiv" style="transition-delay: 0.4s">
                            <i class="fas fa-lock"></i>
                            <p>Nueva Contraseña</p>
                            <input type="password" name="password" id="newPassword" class="password-input" placeholder="Mínimo 8 caracteres" required />
                            <span class="toggle-password">
                                <i class="fas fa-eye-slash eye-icon"></i>
                            </span>
                        </div>

                        <div class="frmDiv" style="transition-delay: 0.6s">
                            <i class="fas fa-lock"></i>
                            <p>Confirmar Contraseña</p>
                            <input type="password" name="password_confirmation" id="confirmPassword" class="password-input" placeholder="Repite la contraseña" required />
                            <span class="toggle-password">
                                <i class="fas fa-eye-slash eye-icon"></i>
                            </span>
                        </div>

                        <div class="frmDiv" style="transition-delay: 0.8s; margin-top: 10px;">
                            <button class="acptBtn" type="submit">
                                <span class="btn-text">Cambiar Contraseña</span>
                                <span class="btn-loader-container">
                                    <div class="loader"></div>
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            // Animación de entrada
            (function() {
                setTimeout(function() {
                    $(".logoCont").transition({ scale: 1 }, 700, "ease");
                    setTimeout(function() {
                        $(".logoCont .logo").addClass("loadIn");
                        setTimeout(function() {
                            // Altura adaptada para el formulario de reset
                            $(".acptContainer").transition({ height: "520px" });
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

            // Función para mostrar/ocultar contraseña
            $('.toggle-password').on('click', function() {
                const passwordInput = $(this).siblings('input.password-input');
                const eyeIcon = $(this).find('i');
                const isPasswordVisible = passwordInput.attr('type') === 'text';
                passwordInput.attr('type', isPasswordVisible ? 'password' : 'text');
                eyeIcon.toggleClass('fa-eye-slash fa-eye');
            });

            // Envío del formulario de Reset con AJAX
            $('#resetForm').on('submit', function(e) {
                e.preventDefault();
                const $form = $(this);
                const $button = $form.find('.acptBtn');
                $button.addClass('loading').prop('disabled', true);
                $('.error-message').remove(); // Limpiar errores previos

                $.ajax({
                    url: $form.attr('action'),
                    method: $form.attr('method'),
                    data: $form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        // Alerta de éxito y redirección al login
                        Swal.fire({
                            icon: 'success',
                            title: '¡Contraseña actualizada!',
                            text: response.message,
                            confirmButtonColor: '#ff7b00ef',
                            confirmButtonText: 'Iniciar Sesión',
                            customClass: { popup: 'swal2-popup', title: 'swal2-title', confirmButton: 'swal2-confirm' }
                        }).then(() => {
                            window.location.href = response.redirect;
                        });
                    },
                    error: function(xhr) {
                        $button.removeClass('loading').prop('disabled', false);

                        if (xhr.status === 422 && xhr.responseJSON.errors) { // Error de validación (ej: password muy corta)
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                let inputField = $(`[name="${key}"]`);
                                // Determinar si el error es de confirmación o longitud
                                let errorMessage = key.includes('password') && value[0].includes('confirmation')
                                    ? 'Las contraseñas no coinciden.'
                                    : (key.includes('password') && value[0].includes('mínimo 8')
                                        ? 'La contraseña debe tener mínimo 8 caracteres.'
                                        : value[0]);

                                // Insertar error después del elemento que puede ser el input o el toggle-password
                                let target = inputField.siblings('.toggle-password').length > 0 ? inputField.siblings('.toggle-password') : inputField;
                                // Asegurar que el mensaje se ponga al final del DIV
                                inputField.closest('.frmDiv').append(`<span class="error-message">${errorMessage}</span>`);
                            });
                        } else { // Otro tipo de error (ej: token expirado o inválido)
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON.message || 'Hubo un problema al cambiar la contraseña. Por favor, reinicia el proceso.',
                                confirmButtonColor: '#ff7b00ef',
                                customClass: { popup: 'swal2-popup', title: 'swal2-title', confirmButton: 'swal2-confirm' }
                            }).then(() => {
                                // Redirigir al login si el error es grave (token expirado/inválido)
                                window.location.href = "{{ route('login') }}";
                            });
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
