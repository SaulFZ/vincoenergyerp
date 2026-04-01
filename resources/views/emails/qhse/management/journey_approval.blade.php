<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Viaje: {{ $journey->folio }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f7f6; font-family: Arial, Helvetica, sans-serif; color: #333333; -webkit-font-smoothing: antialiased;">

    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f4f7f6; padding: 20px 0;">
        <tr>
            <td align="center">

                <table role="presentation" width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; border: 1px solid #eaeaea; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); max-width: 600px; width: 100%; margin: auto;">

                    <tr>
                        <td bgcolor="#1e3a8a" align="center" style="padding: 25px 20px; background-color: #1e3a8a; border-bottom: 4px solid #1e40af;">
                            <h2 style="margin: 0; font-size: 22px; font-weight: bold; color: #ffffff; font-family: Arial, Helvetica, sans-serif;">
                                Solicitud de Viaje: {{ $journey->folio }}
                            </h2>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 30px 25px;">
                            <p style="margin: 0 0 25px 0; font-size: 15px; line-height: 1.6; color: #555555; font-family: Arial, Helvetica, sans-serif;">
                                Se ha registrado un nuevo <strong>Gerenciamiento de Viaje</strong> en el sistema y se encuentra en espera de tu revisión y autorización.
                            </p>

                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                                <tr>
                                    <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; width: 35%; font-size: 14px; color: #64748b; font-weight: bold; font-family: Arial, Helvetica, sans-serif; vertical-align: top;">Fecha de Solicitud:</td>
                                    <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #1e293b; font-family: Arial, Helvetica, sans-serif;">
                                        {{ \Carbon\Carbon::parse($journey->request_date)->format('d/m/Y') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #64748b; font-weight: bold; font-family: Arial, Helvetica, sans-serif; vertical-align: top;">Folio:</td>
                                    <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; font-size: 15px; color: #0f172a; font-weight: bold; font-family: Arial, Helvetica, sans-serif;">
                                        {{ $journey->folio }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #64748b; font-weight: bold; font-family: Arial, Helvetica, sans-serif; vertical-align: top;">Solicitante:</td>
                                    <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #1e293b; font-family: Arial, Helvetica, sans-serif;">
                                        {{ $journey->creator_name }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #64748b; font-weight: bold; font-family: Arial, Helvetica, sans-serif; vertical-align: top;">Departamento:</td>
                                    <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #1e293b; font-family: Arial, Helvetica, sans-serif;">
                                        {{ $journey->department }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #64748b; font-weight: bold; font-family: Arial, Helvetica, sans-serif; vertical-align: top;">Tipo de Viaje:</td>
                                    <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #1e293b; font-family: Arial, Helvetica, sans-serif;">
                                        {{ $journey->fleet_type }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #64748b; font-weight: bold; font-family: Arial, Helvetica, sans-serif; vertical-align: top;">Destino:</td>
                                    <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #1e293b; font-family: Arial, Helvetica, sans-serif;">
                                        {{ $journey->destination_region }} <br>
                                        <span style="color: #64748b; font-size: 12px; font-weight: normal;">({{ $journey->specific_destination }})</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 15px; font-size: 14px; color: #64748b; font-weight: bold; font-family: Arial, Helvetica, sans-serif; vertical-align: top;">Nivel de Riesgo:</td>
                                    <td style="padding: 15px; font-family: Arial, Helvetica, sans-serif;">

                                        @php
                                            $bg = '#c8e6c9';
                                            $txt = '#1b5e20';
                                            $border = '#a5d6a7';

                                            if(strtolower($journey->risk_level) == 'medio') {
                                                $bg = '#fff9c4'; $txt = '#7f6000'; $border = '#fff59d';
                                            } elseif(strtolower($journey->risk_level) == 'alto') {
                                                $bg = '#ffe0b2'; $txt = '#e65100'; $border = '#ffcc80';
                                            } elseif(strtolower($journey->risk_level) == 'muy_alto') {
                                                $bg = '#ffcdd2'; $txt = '#b71c1c'; $border = '#ef9a9a';
                                            }
                                        @endphp

                                        <span style="background-color: {{ $bg }}; color: {{ $txt }}; border: 1px solid {{ $border }}; padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 12px; display: inline-block; text-transform: uppercase;">
                                            {{ str_replace('_', ' ', strtoupper($journey->risk_level)) }}
                                        </span>

                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top: 30px;">
                                <tr>
                                    <td align="center">
                                        <table role="presentation" border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td align="center" bgcolor="#1e3a8a" style="border-radius: 6px;">
                                                    <a href="https://vincoerp.vincoenergy.com/login" target="_blank" style="font-size: 15px; font-family: Arial, Helvetica, sans-serif; color: #ffffff; text-decoration: none; border-radius: 6px; padding: 14px 28px; border: 1px solid #1e3a8a; display: inline-block; font-weight: bold;">
                                                        Revisar Solicitud
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                        <p style="margin: 12px 0 0 0; font-size: 13px; color: #64748b; font-family: Arial, Helvetica, sans-serif;">
                                            * Serás redirigido a la pantalla de inicio de sesión para ingresar al sistema.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="background-color: #f8fafc; padding: 20px; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0 0 8px 0; font-size: 12px; color: #64748b; font-family: Arial, Helvetica, sans-serif; line-height: 1.5;">
                                Este es un mensaje automático generado por el Sistema de Seguridad y Gerenciamiento de Viajes (QHSE).
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #64748b; font-family: Arial, Helvetica, sans-serif;">
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
