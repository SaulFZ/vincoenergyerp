<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resolución de Anticipo | Reembolsos - VesCore</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: Arial, Helvetica, sans-serif; color: #111827; -webkit-font-smoothing: antialiased;">

    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9; padding: 20px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; border: 1px solid #4b5563; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); max-width: 600px; width: 100%; margin: auto;">

                    <!-- Encabezado -->
                    <tr>
                        <td bgcolor="#0c182b" style="padding: 18px 20px; background-color: #0c182b; border-bottom: 4px solid #20406d;">
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                      <td width="60" style="vertical-align: middle; text-align: left;">
                                        <img src="{{ asset('assets/img/logo.png') }}"
                                             alt="VesCore"
                                             style="display: inline-block; vertical-align: middle; max-height: 32px; width: auto; color: #ffffff;">
                                    </td>
                                    <td style="vertical-align: middle; text-align: center;">
                                        <h1 style="margin: 0; font-size: 22px; font-weight: bold; color: #ffffff; font-family: Arial, Helvetica, sans-serif; letter-spacing: 1px;">
                                            VesCore
                                        </h1>
                                    </td>
                                    <td width="60" style="vertical-align: middle; text-align: right;"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Contenido -->
                    <tr>
                        <td style="padding: 30px 25px;">
                            <h2 style="margin: 0 0 15px 0; font-size: 18px; color: #0c182b; font-family: Arial, Helvetica, sans-serif;">
                                Notificación de Dictamen
                            </h2>
                            <p style="margin: 0 0 25px 0; font-size: 15px; line-height: 1.6; color: #1f2937; font-family: Arial, Helvetica, sans-serif;">
                                Hola <strong>{{ $advance->user->name ?? 'Usuario' }}</strong>, tu solicitud de anticipo operativo ha sido evaluada por la gerencia. A continuación, los detalles de la resolución:
                            </p>

                            <!-- Tabla de Datos -->
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; border: 1px solid #4b5563; border-radius: 6px;">
                                <tr>
                                    <td style="padding: 12px 15px; border-bottom: 1px solid #4b5563; width: 35%; font-size: 13px; color: #1f2937; font-weight: bold; font-family: Arial, Helvetica, sans-serif; vertical-align: middle;">Folio:</td>
                                    <td style="padding: 12px 15px; border-bottom: 1px solid #4b5563; font-size: 14px; color: #0c182b; font-family: 'Courier New', Courier, monospace; font-weight: bold;">
                                        {{ $advance->folio_system }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 15px; border-bottom: 1px solid #4b5563; font-size: 13px; color: #1f2937; font-weight: bold; font-family: Arial, Helvetica, sans-serif; vertical-align: middle;">Estatus Final:</td>
                                    <td style="padding: 12px 15px; border-bottom: 1px solid #4b5563; font-size: 14px; font-family: Arial, Helvetica, sans-serif; font-weight: bold; color: {{ $advance->status === 'aprobado' ? '#059669' : '#dc2626' }}; text-transform: uppercase;">
                                        {{ $advance->status }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 15px; border-bottom: 1px solid #4b5563; font-size: 13px; color: #1f2937; font-weight: bold; font-family: Arial, Helvetica, sans-serif; vertical-align: middle;">Tipo de anticipo:</td>
                                    <td style="padding: 12px 15px; border-bottom: 1px solid #4b5563; font-size: 14px; color: #0c182b; font-family: Arial, Helvetica, sans-serif;">
                                        {{ $advance->advance_type }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 15px; font-size: 13px; color: #1f2937; font-weight: bold; font-family: Arial, Helvetica, sans-serif; vertical-align: middle;">Monto solicitado:</td>
                                    <td style="padding: 15px; font-size: 18px; color: #16a34a; font-family: 'Courier New', Courier, monospace; font-weight: bold;">
                                        ${{ number_format($advance->amount, 2) }} MXN
                                    </td>
                                </tr>
                            </table>

                            <!-- Botón -->
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top: 30px;">
                                <tr>
                                    <td align="center">
                                        <table role="presentation" border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td align="center" bgcolor="#0c182b" style="border-radius: 6px;">
                                                    <a href="{{ route('expense-claims.reimbursements') }}" target="_blank" style="font-size: 15px; font-family: Arial, Helvetica, sans-serif; color: #ffffff; text-decoration: none; border-radius: 6px; padding: 12px 25px; border: 1px solid #0c182b; display: inline-block; font-weight: bold;">
                                                        Acceder al sistema
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                        <p style="margin: 12px 0 0 0; font-size: 12px; color: #1f2937; font-family: Arial, Helvetica, sans-serif;">
                                            Ingresa a la sistema para ver tus anticipos.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- Pie de página -->
                   <tr>
                        <td align="center" style="background-color: #f1f5f9; padding: 20px; border-top: 1px solid #4b5563;">
                            <p style="margin: 0 0 8px 0; font-size: 12px; color: #1f2937; font-family: Arial, Helvetica, sans-serif; line-height: 1.5;">
                                Este es un mensaje automático generado por el módulo de <strong>Reembolsos</strong> en el sistema <strong>VesCore</strong>.
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #1f2937; font-family: Arial, Helvetica, sans-serif;">
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
