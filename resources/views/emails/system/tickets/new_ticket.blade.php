<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Ticket Registrado: {{ $ticket->folio }} | Mis Tickets - VesCore</title>
</head>
<body style="margin: 0; padding: 0; background-color: #E9EEF0; font-family: Arial, Helvetica, sans-serif; color: #344955; -webkit-font-smoothing: antialiased;">

    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #E9EEF0; padding: 20px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; border: 1px solid #8EACB8; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); max-width: 600px; width: 100%; margin: auto;">

                    <!-- Encabezado: Logo a la izquierda, texto centrado -->
                    <tr>
                        <td bgcolor="#344955" style="padding: 18px 20px; background-color: #344955; border-bottom: 4px solid #4A6572;">
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td width="60" style="vertical-align: middle; text-align: left;">
                                        <img src="{{ asset('assets/img/logo.png') }}"
                                             alt="VesCore"
                                             style="display: inline-block; vertical-align: middle; max-height: 32px; width: auto;">
                                    </td>
                                    <td style="vertical-align: middle; text-align: center;">
                                        <h1 style="margin: 0; font-size: 22px; font-weight: bold; color: #ffffff; font-family: Arial, Helvetica, sans-serif; letter-spacing: 1px;">
                                            VesCore
                                        </h1>
                                    </td>
                                    <td width="60" style="vertical-align: middle; text-align: right;">
                                        <!-- Espacio vacío para equilibrar -->
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Contenido -->
                    <tr>
                        <td style="padding: 30px 25px;">
                            <h2 style="margin: 0 0 15px 0; font-size: 18px; color: #344955; font-family: Arial, Helvetica, sans-serif;">
                                Nuevo ticket
                            </h2>
                            <p style="margin: 0 0 25px 0; font-size: 15px; line-height: 1.6; color: #4A6572; font-family: Arial, Helvetica, sans-serif;">
                                Se ha registrado un ticket en el sistema. Requiere su revisión y atención.
                            </p>

                            <!-- Tabla de Datos (SIN la descripción) -->
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #E9EEF0; border: 1px solid #8EACB8; border-radius: 6px;">
                                <tr>
                                    <td style="padding: 12px 15px; border-bottom: 1px solid #8EACB8; width: 35%; font-size: 13px; color: #4A6572; font-weight: bold; font-family: Arial, Helvetica, sans-serif; vertical-align: middle;">Folio:</td>
                                    <td style="padding: 12px 15px; border-bottom: 1px solid #8EACB8; font-size: 14px; color: #344955; font-family: 'Courier New', Courier, monospace; font-weight: bold;">
                                        {{ $ticket->folio }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 15px; border-bottom: 1px solid #8EACB8; font-size: 13px; color: #4A6572; font-weight: bold; font-family: Arial, Helvetica, sans-serif; vertical-align: middle;">Solicitante:</td>
                                    <td style="padding: 12px 15px; border-bottom: 1px solid #8EACB8; font-size: 14px; color: #344955; font-family: Arial, Helvetica, sans-serif;">
                                        {{ $ticket->user->name ?? 'Usuario del sistema' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 15px; border-bottom: 1px solid #8EACB8; font-size: 13px; color: #4A6572; font-weight: bold; font-family: Arial, Helvetica, sans-serif; vertical-align: middle;">Departamento:</td>
                                    <td style="padding: 12px 15px; border-bottom: 1px solid #8EACB8; font-size: 14px; color: #344955; font-family: Arial, Helvetica, sans-serif;">
                                        {{ $ticket->department_code }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 15px; font-size: 13px; color: #4A6572; font-weight: bold; font-family: Arial, Helvetica, sans-serif; vertical-align: middle;">Asunto:</td>
                                    <td style="padding: 12px 15px; font-size: 14px; color: #344955; font-family: Arial, Helvetica, sans-serif; font-weight: bold;">
                                        {{ $ticket->subject }}
                                    </td>
                                </tr>
                            </table>

                            <!-- Caja de Descripción Separada -->
                            <div style="margin-top: 20px; padding: 15px; background-color: #E9EEF0; border-left: 4px solid #344955; border-radius: 4px;">
                                <p style="margin: 0 0 8px 0; font-size: 13px; color: #4A6572; font-weight: bold; font-family: Arial, Helvetica, sans-serif; text-transform: uppercase;">Descripción del Problema:</p>
                                <p style="margin: 0; font-size: 14px; color: #344955; font-family: Arial, Helvetica, sans-serif; line-height: 1.5; font-style: italic;">
                                    "{{ $ticket->description }}"
                                </p>
                            </div>

                            <!-- Botón -->
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top: 30px;">
                                <tr>
                                    <td align="center">
                                        <table role="presentation" border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td align="center" bgcolor="#344955" style="border-radius: 6px;">
                                                    <a href="https://vescore.tech" target="_blank" style="font-size: 15px; font-family: Arial, Helvetica, sans-serif; color: #ffffff; text-decoration: none; border-radius: 6px; padding: 12px 25px; border: 1px solid #344955; display: inline-block; font-weight: bold;">
                                                        Ver ticket
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                        <p style="margin: 12px 0 0 0; font-size: 12px; color: #4A6572; font-family: Arial, Helvetica, sans-serif;">
                                            Serás redirigido al sistema para gestionar el ticket.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- Pie de página -->
                    <tr>
                        <td align="center" style="background-color: #E9EEF0; padding: 20px; border-top: 1px solid #8EACB8;">
                            <p style="margin: 0 0 8px 0; font-size: 12px; color: #4A6572; font-family: Arial, Helvetica, sans-serif; line-height: 1.5;">
                                Este es un mensaje automático generado por el módulo de <strong>Mis Tickets</strong> en el sistema <strong>VesCore</strong>.
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #4A6572; font-family: Arial, Helvetica, sans-serif;">
                                Por favor, no respondas a este correo.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
