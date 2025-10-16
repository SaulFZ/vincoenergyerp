<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinco - Login</title>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"
        integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script defer src="https://use.fontawesome.com/releases/v5.0.1/js/all.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.transit/0.9.12/jquery.transit.js"
        integrity="sha256-mkdmXjMvBcpAyyFNCVdbwg4v+ycJho65QLDwVE3ViDs=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&family=Work+Sans:wght@700&display=swap"
        rel="stylesheet">
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
                    <h1>!Bienvenido!</h1>
                    <div class="frmContainer">
                        <div class="frmDiv" style="transition-delay: 0.2s">
                            <i class="fas fa-user"></i>
                            <p>Usuario</p>
                            <input type="text" name="username" value="{{ old('username') }}" />
                        </div>
                        <div class="frmDiv" style="transition-delay: 0.4s">
                            <i class="fas fa-lock"></i>
                            <p>Contraseña</p>
                            <input type="password" name="password" id="password" />
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
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.toggle-password').on('click', function() {
                const passwordInput = $('#password');
                const eyeIcon = $('#eyeIcon');

                const isPasswordVisible = passwordInput.attr('type') === 'text';
                passwordInput.attr('type', isPasswordVisible ? 'password' : 'text');

                eyeIcon.removeClass('fa-eye fa-eye-slash');
                eyeIcon.addClass(isPasswordVisible ? 'fa-eye-slash' : 'fa-eye');
            });
        });

        // Inicialización de la animación
        $(function() {
            setTimeout(function() {
                $(".logoCont").transition({
                    scale: 1
                }, 700, "ease");
                setTimeout(function() {
                    $(".logoCont .logo").addClass("loadIn");
                    setTimeout(function() {
                        $(".acptContainer").transition({
                            height: "380px"
                        });
                        setTimeout(function() {
                            $(".acptContainer").addClass("loadIn");
                            setTimeout(function() {
                                $(".frmDiv, form h1").addClass("loadIn");
                            }, 500);
                        }, 500);
                    }, 500);
                }, 1000);
            }, 10);
        });

        // Manejo del formulario con prevención de recargas
        $(document).ready(function() {
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();

                // Animación del botón - Mostrar animación de carga
                const $button = $(this).find('.acptBtn');
                $button.addClass('loading');

                // Deshabilitar el botón durante la petición
                $button.prop('disabled', true);

                $.ajax({
                    url: $(this).attr('action'),
                    method: $(this).attr('method'),
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Mostrar pantalla de carga en lugar de redireccionar directamente
                            window.location.href = "{{ route('splash') }}";
                        } else {
                            // Restaurar el botón
                            $button.removeClass('loading');
                            $button.prop('disabled', false);

                            Swal.fire({
                                icon: 'error',
                                title: 'Error de acceso',
                                text: response.message || 'Credenciales incorrectas',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    },
                    error: function() {
                        // Restaurar el botón
                        $button.removeClass('loading');
                        $button.prop('disabled', false);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al procesar la solicitud',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>
