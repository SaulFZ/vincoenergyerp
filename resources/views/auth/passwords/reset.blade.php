<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinco - Nueva Contraseña</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/login/reset.css') }}" rel="stylesheet">
</head>

<body>

    <div class="login-wrapper">

        <div class="logo-container">
            <img src="{{ asset('assets/img/logovinco2.png') }}" alt="Vinco Energy" class="logo-img">
        </div>

        <h2>Restablecer Contraseña</h2>
        <p class="subtitle">Ingresa y confirma tu nueva contraseña</p>

        <form id="resetForm" method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="code" value="{{ $token }}">

            <div class="input-group">
                <label>Correo Electrónico</label>
                <div class="field-wrapper">
                    <input type="email" class="custom-input has-content" name="email" value="{{ $email }}"
                        readonly required>
                    <i class="fas fa-envelope input-icon"></i>
                </div>
            </div>

            <div class="input-group">
                <label>Nueva Contraseña</label>
                <div class="field-wrapper">
                    <input type="password" class="custom-input" name="password" id="newPassword"
                        placeholder="Mínimo 8 caracteres" required>
                    <i class="fas fa-lock input-icon"></i>
                    <i class="fas fa-eye-slash toggle-pass"></i>
                </div>
            </div>

            <div class="input-group">
                <label>Confirmar Contraseña</label>
                <div class="field-wrapper">
                    <input type="password" class="custom-input" name="password_confirmation" id="confirmPassword"
                        placeholder="Repite la contraseña" required>
                    <i class="fas fa-check-circle input-icon"></i>
                    <i class="fas fa-eye-slash toggle-pass"></i>
                </div>
            </div>

            <button type="submit" class="acptBtn btn-login">
                <span>CAMBIAR CONTRASEÑA</span>
                <div class="spinner"></div>
            </button>

        </form>

        <div class="footer-copyright">
            Vinco Energy &copy; <span id="yearSpan"></span> | Todos los derechos reservados
        </div>
    </div>

    <script>
        $(document).ready(function() {

            // --- 1. AÑO AUTOMÁTICO ---
            $('#yearSpan').text(new Date().getFullYear());


            // --- 2. VER CONTRASEÑA (Lógica inteligente para ambos campos) ---
            $('.toggle-pass').on('click', function() {
                let icon = $(this);
                // Busca el input hermano en el mismo div
                let input = icon.siblings('input');

                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                }
            });


            // --- 3. DETECCIÓN DE CONTENIDO (Colorea iconos) ---
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


            // --- 4. AJAX Submit ---
            $('#resetForm').on('submit', function(e) {
                e.preventDefault();
                const $form = $(this);
                const $button = $form.find('.btn-login');

                $button.addClass('loading').prop('disabled', true);

                $.ajax({
                    url: $form.attr('action'),
                    method: $form.attr('method'),
                    data: $form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Contraseña Actualizada!',
                            text: 'Tu contraseña ha sido restablecida exitosamente.',
                            confirmButtonText: 'Iniciar Sesión',
                            allowOutsideClick: false,
                            confirmButtonColor: '#334c95'
                        }).then(() => {
                            window.location.href = response.redirect;
                        });
                    },
                    error: function(xhr) {
                        $button.removeClass('loading').prop('disabled', false);

                        let msg = 'Ocurrió un error inesperado.';
                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            if (errors.password) msg = errors.password[0];
                            else if (errors.email) msg = errors.email[0];
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg,
                            confirmButtonText: 'Corregir',
                            confirmButtonColor: '#d67e29'
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>
