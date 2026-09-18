<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - VesCore</title>
</head>

<body
    style="margin: 0; padding: 0; background-color: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif; color: #333333; -webkit-font-smoothing: antialiased;">

    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0"
        style="background-color: #f0f2f5; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" border="0" cellspacing="0" cellpadding="0"
                    style="max-width: 600px; width: 100%; margin: auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);">

                    <!-- Encabezado: Logo a la izquierda, texto centrado -->
                    <tr>
                        <td bgcolor="#334c95"
                            style="background-color: #334c95; padding: 22px 25px; border-bottom: 4px solid #d67e29;">
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td width="70" style="vertical-align: middle; text-align: left;">
                                        @if (file_exists(public_path('assets/img/logovinco2.png')))
                                        <img src="{{ asset('assets/img/logovinco2.png') }}" alt="Vinco"
                                            style="display: inline-block; vertical-align: middle; max-height: 34px; width: auto; filter: brightness(0) invert(1);">
                                        @else
                                        <span
                                            style="color: #ffffff; font-size: 22px; font-weight: bold; letter-spacing: 1px;">VINCO</span>
                                        @endif
                                    </td>
                                    <td style="vertical-align: middle; text-align: center;">
                                        <h1
                                            style="margin: 0; font-size: 22px; font-weight: bold; color: #ffffff; letter-spacing: 1.5px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif;">
                                            VesCore
                                        </h1>
                                    </td>
                                    <td width="70" style="vertical-align: middle; text-align: right;">
                                        <!-- Espacio de equilibrio -->
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Contenido -->
                    <tr>
                        <td style="padding: 40px 35px;">

                            <h2
                                style="margin: 0 0 15px 0; font-size: 22px; color: #334c95; font-weight: 600; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif;">
                                ¡Hola, {{ $userName }}!
                            </h2>

                            <p
                                style="margin: 0 0 16px 0; font-size: 15px; color: #4a5568; line-height: 1.7; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif;">
                                Hemos recibido una solicitud para <strong style="color: #334c95;">restablecer la
                                    contraseña</strong> de tu cuenta en VesCore.
                            </p>

                            <p
                                style="margin: 0 0 25px 0; font-size: 15px; color: #4a5568; line-height: 1.7; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif;">
                                Para continuar con el proceso, utiliza el siguiente código de verificación en la
                                aplicación:
                            </p>

                            <!-- Caja del código -->
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0"
                                style="background-color: #f7fafc; border: 2px dashed #cbd5e0; border-radius: 10px;">
                                <tr>
                                    <td style="padding: 30px 20px; text-align: center;">
                                        <p
                                            style="margin: 0 0 18px 0; font-size: 13px; color: #718096; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif;">
                                            Tu Código de Verificación
                                        </p>
                                        <div
                                            style="display: inline-block; background-color: #d67e29; color: #ffffff; padding: 20px 40px; font-size: 42px; font-weight: bold; letter-spacing: 10px; border-radius: 10px; box-shadow: 0 8px 20px rgba(214, 126, 41, 0.35); font-family: 'Courier New', Courier, monospace;">
                                            {{ $token }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Nota de Responsabilidad -->
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0"
                                style="margin-top: 25px; background-color: #eef2fb; border: 1px solid #334c95; border-radius: 8px;">
                                <tr>
                                    <td style="padding: 15px 20px;">
                                        <p
                                            style="margin: 0; font-size: 14px; line-height: 1.6; color: #334c95; font-weight: 500; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif;">
                                            <strong style="color: #d67e29;">Nota importante:</strong> Al realizar el
                                            cambio de contraseña, el área de sistemas ya no tendrá acceso al control
                                            sobre la misma. A partir de ese momento, la gestión y resguardo de la
                                            contraseña será <strong>responsabilidad exclusiva del usuario</strong>.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Advertencia de Caducidad -->
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0"
                                style="margin-top: 20px; background-color: #fdf3e7; border-left: 5px solid #d67e29; border-radius: 8px;">
                                <tr>
                                    <td style="padding: 20px 25px;">
                                        <p
                                            style="margin: 0 0 12px 0; font-size: 14px; line-height: 1.6; color: #744210; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif;">
                                            <strong style="font-weight: 600; color: #5a2d0c;">Este código expirará en {{
                                                $expirationMinutes }} minutos.</strong><br>
                                            Por favor, úsalo pronto para restablecer tu contraseña de forma segura.
                                        </p>
                                        <p
                                            style="margin: 0; font-size: 14px; line-height: 1.6; color: #744210; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif;">
                                            <strong style="font-weight: 600; color: #5a2d0c;">Por seguridad, no
                                                compartas este código con nadie.</strong>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Divisor -->
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0"
                                style="margin: 30px 0;">
                                <tr>
                                    <td style="height: 1px; background-color: #e2e8f0;"></td>
                                </tr>
                            </table>

                            <!-- Consejos de Seguridad -->
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0"
                                style="background-color: #f7fafc; border-radius: 8px;">
                                <tr>
                                    <td style="padding: 25px;">
                                        <p
                                            style="margin: 0 0 15px 0; font-size: 16px; color: #334c95; font-weight: 600; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif;">
                                            Consejos de Seguridad
                                        </p>
                                        <table role="presentation" width="100%" border="0" cellspacing="0"
                                            cellpadding="0">
                                            <tr>
                                                <td
                                                    style="padding: 8px 0; font-size: 14px; color: #4a5568; line-height: 1.5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif;">
                                                    &bull;&nbsp; No compartas este código con nadie.
                                                </td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="padding: 8px 0; font-size: 14px; color: #4a5568; line-height: 1.5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif;">
                                                    &bull;&nbsp; Nuestro equipo nunca te pedirá este código.
                                                </td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="padding: 8px 0; font-size: 14px; color: #4a5568; line-height: 1.5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif;">
                                                    &bull;&nbsp; Usa una contraseña única y segura.
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p
                                style="margin: 30px 0 0 0; font-size: 15px; color: #4a5568; line-height: 1.7; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif;">
                                Si <strong style="color: #334c95;">no solicitaste</strong> este cambio de contraseña,
                                puedes ignorar este mensaje de forma segura. Tu cuenta permanecerá protegida.
                            </p>

                            <p
                                style="margin: 30px 0 0 0; font-size: 15px; color: #4a5568; line-height: 1.7; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif;">
                                Saludos cordiales,<br>
                                <strong style="color: #334c95;">El Equipo de TI de Vinco Energy Services.</strong>
                            </p>

                        </td>
                    </tr>

                    <!-- Pie de página -->
                    <tr>
                        <td align="center" style="background-color: #334c95; padding: 30px 25px;">
                            <p
                                style="margin: 0 0 12px 0; font-size: 16px; font-weight: 600; color: #ffffff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif; letter-spacing: 0.5px;">
                                VesCore
                            </p>
                            <p
                                style="margin: 0 0 6px 0; font-size: 13px; color: #cbd5e0; line-height: 1.5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif;">
                                &copy; {{ date('Y') }} Vinco Energy Services. Todos los derechos reservados.
                            </p>
                            <p
                                style="margin: 0; font-size: 13px; color: #cbd5e0; line-height: 1.5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif;">
                                Este es un correo automático, por favor no respondas a este mensaje.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>

</html>
