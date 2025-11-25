<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Vinco</title>
    <style>
        /* Reset básico para compatibilidad con clientes de correo */
        body,
        table,
        td,
        a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #f0f2f5;
            color: #333333;
        }

        table,
        td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
            border-collapse: collapse;
        }

        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            display: block;
        }

        /* Contenedor principal */
        .email-wrapper {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            overflow: hidden;
        }

        /* Header con degradado */
        .header {
            background: linear-gradient(135deg, #ff7b00 0%, #ff9933 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }

        .logo {
            max-width: 160px;
            margin: 0 auto;
            filter: brightness(0) invert(1);
        }

        .logo-text {
            color: #ffffff;
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 0;
            text-transform: uppercase;
        }

        /* Contenido */
        .content {
            padding: 40px 35px;
        }

        h1 {
            font-size: 26px;
            color: #1a1a1a;
            margin: 0 0 20px 0;
            font-weight: 600;
        }

        p {
            font-size: 15px;
            color: #4a5568;
            line-height: 1.7;
            margin: 0 0 16px 0;
        }

        .greeting {
            font-size: 20px;
            color: #2d3748;
            font-weight: 600;
            margin-bottom: 20px;
        }

        /* Sección del código */
        .code-section {
            text-align: center;
            margin: 35px 0;
            padding: 30px 20px;
            background-color: #f7fafc;
            border-radius: 10px;
            border: 2px dashed #cbd5e0;
        }

        .code-label {
            font-size: 13px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            margin-bottom: 18px;
        }

        /* *** ESTILOS CLAVE DEL CÓDIGO (MODO CLARO) *** Fondo degradado naranja y texto negro/oscuro.
        */
        .code-box {
            display: inline-block;
            background: linear-gradient(135deg, #ff7b00 0%, #ff9933 100%);
            color: #1a1a1a; /* Texto NEGRO en Modo Claro */
            padding: 20px 40px;
            font-size: 42px;
            font-weight: bold;
            letter-spacing: 10px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(255, 123, 0, 0.35);
            font-family: 'Courier New', Courier, monospace;
        }

        /* --- Estilo Específico para Dark Mode --- */
        @media (prefers-color-scheme: dark) {
            /* Adaptar el contenedor principal a modo oscuro */
            body {
                background-color: #1a1a1a !important; /* Fondo del cliente de correo */
            }
            .email-wrapper {
                background-color: #2d3748 !important; /* Fondo del contenedor del correo */
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5) !important;
            }
            .content, p, .security-item {
                color: #e2e8f0 !important; /* Texto claro */
            }
            .greeting, h1 {
                color: #ffffff !important; /* Títulos blancos */
            }
            .divider {
                background: linear-gradient(to right, transparent, #4a5568, transparent) !important;
            }

            /* Sección de código en modo oscuro */
            .code-section {
                background-color: #1a202c !important; /* Fondo oscuro de la sección */
                border: 2px dashed #4a5568 !important; /* Borde oscuro */
            }

            .code-label {
                color: #a0aec0 !important; /* Etiqueta clara */
            }

            /* *** ESTILOS CLAVE DEL CÓDIGO (MODO OSCURO) *** Fondo del degradado original para que resalte y texto BLANCO.
            */
            .code-box {
                background: linear-gradient(135deg, #ff7b00 0%, #ff9933 100%) !important; /* Mantener el degradado naranja */
                color: #ffffff !important; /* Texto BLANCO en Modo Oscuro */
            }

            /* Otras secciones para modo oscuro */
            .security-section {
                background-color: #1a202c !important;
            }
            .security-title {
                color: #ffffff !important;
            }
            .warning-box {
                background: #4a5568 !important;
                border-left: 5px solid #ff7b00 !important;
            }
            .warning-item, .warning-item strong {
                color: #e2e8f0 !important;
            }
            .responsibility-box {
                background-color: #1a365d !important;
                border: 1px solid #4299e1 !important;
            }
            .responsibility-text {
                color: #90cdf4 !important;
            }
            .responsibility-text strong {
                color: #ff9933 !important;
            }

        }
        /* -------------------------------------- */


        /* Caja de advertencia (Nota de Caducidad) */
        .warning-box {
            background: linear-gradient(135deg, #fff5e6 0%, #ffe9cc 100%);
            border-left: 5px solid #ff9933;
            padding: 20px 25px;
            margin: 30px 0;
            border-radius: 8px;
        }

        .warning-item {
            margin: 0 0 12px 0;
            color: #744210;
            font-size: 14px;
            line-height: 1.6;
            padding-left: 28px;
            position: relative;
        }

        .warning-item:last-child {
            margin-bottom: 0;
        }

        .warning-icon {
            position: absolute;
            left: 0;
            font-size: 18px;
        }

        .warning-item strong {
            font-weight: 600;
            color: #5a2d0c;
        }

        /* Nueva Caja de Responsabilidad */
        .responsibility-box {
            background-color: #ebf8ff; /* Azul claro */
            border: 1px solid #90cdf4;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 8px;
        }

        .responsibility-text {
            color: #2c5282; /* Azul oscuro para el texto */
            font-size: 14px;
            line-height: 1.6;
            font-weight: 500;
        }


        /* Sección de seguridad */
        .security-section {
            background-color: #f7fafc;
            padding: 25px;
            border-radius: 8px;
            margin: 25px 0;
        }

        .security-title {
            font-size: 16px;
            color: #2d3748;
            font-weight: 600;
            margin: 0 0 15px 0;
        }

        .security-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .security-item {
            padding: 10px 0 10px 30px;
            position: relative;
            color: #4a5568;
            font-size: 14px;
            line-height: 1.5;
        }

        .security-item-icon {
            position: absolute;
            left: 0;
            font-size: 16px;
        }

        /* Divisor */
        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #e2e8f0, transparent);
            margin: 30px 0;
        }

        /* Footer */
        .footer {
            background-color: #2d3748;
            color: #a0aec0;
            padding: 35px;
            text-align: center;
        }

        .footer-brand {
            color: #ff7b00;
            font-weight: 600;
            font-size: 18px;
            margin: 0 0 15px 0;
        }

        .footer-text {
            font-size: 13px;
            margin: 8px 0;
            color: #718096;
            line-height: 1.5;
        }

        .footer-link {
            color: #ff7b00;
            text-decoration: none;
        }

        .footer-divider {
            height: 1px;
            background-color: #4a5568;
            margin: 20px 0;
        }

        /* Responsive para móviles */
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                width: 100% !important;
                margin: 0 !important;
                border-radius: 0 !important;
            }

            .header {
                padding: 30px 20px !important;
            }

            .content {
                padding: 30px 20px !important;
            }

            .code-box {
                font-size: 32px !important;
                padding: 16px 28px !important;
                letter-spacing: 6px !important;
            }

            h1 {
                font-size: 22px !important;
            }

            .logo-text {
                font-size: 28px !important;
            }

            .security-section,
            .warning-box,
            .responsibility-box {
                padding: 18px !important;
            }
        }
    </style>
</head>

<body>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%"
        style="background-color: #f0f2f5;">
        <tr>
            <td align="center" style="padding: 40px 15px;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600"
                    class="email-wrapper"
                    style="max-width: 600px; background-color: #ffffff; border-radius: 12px;">

                    <tr>
                        <td class="header"
                            style="background: linear-gradient(135deg, #ff7b00 0%, #ff9933 100%); padding: 40px 30px; text-align: center;">
                            @if (file_exists(public_path('assets/img/logovinco2.png')))
                                <img src="{{ asset('assets/img/logovinco2.png') }}" alt="Logo Vinco" width="160"
                                    class="logo"
                                    style="max-width: 160px; margin: 0 auto; filter: brightness(0) invert(1);">
                            @else
                                <h1 class="logo-text"
                                    style="color: #ffffff; font-size: 36px; font-weight: bold; letter-spacing: 2px; margin: 0;">
                                    VINCO</h1>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td class="content" style="padding: 40px 35px;">

                            <p class="greeting"
                                style="font-size: 20px; color: #2d3748; font-weight: 600; margin-bottom: 20px;">
                                👋 ¡Hola, {{ $userName }}!</p>

                            <p
                                style="font-size: 15px; color: #4a5568; line-height: 1.7; margin: 0 0 16px 0;">
                                Hemos recibido una solicitud para <strong
                                    style="color: #2d3748;">restablecer la
                                    contraseña</strong> de tu cuenta en Vinco ERP.
                            </p>

                            <p
                                style="font-size: 15px; color: #4a5568; line-height: 1.7; margin: 0 0 16px 0;">
                                Para continuar con el proceso, utiliza el siguiente
                                **código de verificación** en la aplicación:
                            </p>

                            <table role="presentation"
                                border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td class="code-section"

                                        style="text-align: center; margin: 35px 0; padding: 30px 20px; background-color: #f7fafc; border-radius: 10px; border: 2px dashed #cbd5e0;">
                                        <div class="code-label"

                                            style="font-size: 13px; color: #718096; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; margin-bottom: 18px;">
                                            Tu Código de Verificación
                                        </div>
                                        <div class="code-box"

                                            style="display: inline-block; background: linear-gradient(135deg, #ff7b00 0%, #ff9933 100%); color: #1a1a1a; padding: 20px 40px; font-size: 42px; font-weight: bold; letter-spacing: 10px; border-radius: 10px; box-shadow: 0 8px 20px rgba(255, 123, 0, 0.35); font-family: 'Courier New', Courier, monospace;">
                                            {{ $token }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            {{-- Bloque de Nota de Responsabilidad --}}
                            <table role="presentation"
                                border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td class="responsibility-box"
                                        style="background-color: #ebf8ff; border: 1px solid #90cdf4; padding: 15px 20px; margin: 20px 0; border-radius: 8px;">
                                        <p class="responsibility-text"
                                            style="color: #2c5282; font-size: 14px; line-height: 1.6; font-weight: 500; margin: 0;">
                                            <strong style="color: #ff7b00;">Nota Importante:</strong> Al realizar el cambio de contraseña, el área de sistemas ya no tendrá acceso al control sobre la misma. A partir de ese momento, la gestión y resguardo de la contraseña será **responsabilidad exclusiva del usuario**.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            {{-- Bloque de Advertencia de Caducidad --}}
                            <table role="presentation"
                                border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td class="warning-box"
                                        style="background: linear-gradient(135deg, #fff5e6 0%, #ffe9cc 100%); border-left: 5px solid #ff9933; padding: 20px 25px; margin: 30px 0; border-radius: 8px;">
                                        <div class="warning-item"

                                            style="margin: 0 0 12px 0; color: #744210; font-size: 14px; line-height: 1.6; padding-left: 28px; position: relative;">
                                            <span class="warning-icon"

                                                style="position: absolute; left: 0; font-size: 18px;">⏱️</span>
                                            <strong
                                                style="font-weight: 600; color: #5a2d0c;">Este código expirará en
                                                {{ $expirationMinutes }}
                                                minutos</strong><br>
                                            Por favor, úsalo pronto para
                                            restablecer tu contraseña de forma segura.
                                        </div>
                                        <div class="warning-item"

                                            style="margin: 0; color: #744210; font-size: 14px; line-height: 1.6; padding-left: 28px; position: relative;">
                                            <span class="warning-icon"

                                                style="position: absolute; left: 0; font-size: 18px;">🔒</span>
                                            <strong
                                                style="font-weight: 600; color: #5a2d0c;">Por seguridad, no
                                                compartas este código
                                                con nadie</strong>
                                        </div>
                                    </td>
                                </tr>
                            </table>


                            <div class="divider"
                                style="height: 1px; background: linear-gradient(to right, transparent, #e2e8f0, transparent); margin: 30px 0;">
                            </div>

                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td class="security-section"

                                        style="background-color: #f7fafc; padding: 25px; border-radius: 8px; margin: 25px 0;">
                                        <div class="security-title"

                                            style="font-size: 16px; color: #2d3748; font-weight: 600; margin: 0 0 15px 0;">
                                            🛡️ Consejos de Seguridad
                                        </div>
                                        <div class="security-item"

                                            style="padding: 10px 0 10px 30px; position: relative; color: #4a5568; font-size: 14px; line-height: 1.5;">
                                            <span class="security-item-icon"

                                                style="position: absolute; left: 0; font-size: 16px;">🔒</span>
                                            No compartas este código con
                                            nadie
                                        </div>
                                        <div class="security-item"

                                            style="padding: 10px 0 10px 30px; position: relative; color: #4a5568; font-size: 14px; line-height: 1.5;">
                                            <span class="security-item-icon"

                                                style="position: absolute; left: 0; font-size: 16px;">🔒</span>
                                            Nuestro equipo nunca te pedirá
                                            este código
                                        </div>
                                        <div class="security-item"

                                            style="padding: 10px 0 10px 30px; position: relative; color: #4a5568; font-size: 14px; line-height: 1.5;">
                                            <span class="security-item-icon"

                                                style="position: absolute; left: 0; font-size: 16px;">🔒</span>
                                            Usa una contraseña única y
                                            segura
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <p
                                style="font-size: 15px; color: #4a5568; line-height: 1.7; margin: 0 0 16px 0;">
                                Si <strong style="color: #2d3748;">no
                                    solicitaste</strong> este cambio de contraseña,
                                puedes ignorar este mensaje de forma segura. Tu cuenta
                                permanecerá protegida.
                            </p>

                            <p
                                style="font-size: 15px; color: #4a5568; line-height: 1.7; margin: 30px 0 0 0;">
                                Saludos cordiales,<br>
                                <strong style="color: #2d3748;">El Equipo de Sistemas de Vinco Energy</strong>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td class="footer"
                            style="background-color: #2d3748; color: #a0aec0; padding: 35px; text-align: center;">
                            <div class="footer-brand"
                                style="color: #ff7b00; font-weight: 600; font-size: 18px; margin: 0 0 15px 0;">
                                VINCO ENERGY ERP
                            </div>
                            <p class="footer-text"
                                style="font-size: 13px; margin: 8px 0; color: #718096; line-height: 1.5;">
                                &copy; {{ date('Y') }} Vinco Energy. Todos los
                                derechos reservados.
                            </p>
                            <p class="footer-text"
                                style="font-size: 13px; margin: 8px 0; color: #718096; line-height: 1.5;">
                                Este es un correo automático, por favor no respondas a
                                este mensaje.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
