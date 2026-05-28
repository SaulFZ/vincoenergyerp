<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta de Anomalía: {{ $journey->folio }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f7f6; font-family: Arial, Helvetica, sans-serif; color: #333333; -webkit-font-smoothing: antialiased;">

    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f4f7f6; padding: 20px 0;">
        <tr>
            <td align="center">

                <table role="presentation" width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; border: 1px solid #eaeaea; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); max-width: 600px; width: 100%; margin: auto;">

                    <tr>
                        <td bgcolor="#d32f2f" align="center" style="padding: 25px 20px; border-bottom: 4px solid #b71c1c;">
                            <h2 style="margin: 0; font-size: 22px; font-weight: bold; color: #ffffff; font-family: Arial, Helvetica, sans-serif;">
                                ⚠️ REPORTE DE ANOMALÍAS VEHICULARES
                            </h2>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 30px 25px;">
                            <p style="margin: 0 0 25px 0; font-size: 15px; line-height: 1.6; color: #555555; font-family: Arial, Helvetica, sans-serif;">
                                El <strong>Gerenciamiento de Viaje {{ $journey->folio }}</strong> ha reportado anomalías durante la inspección pre-viaje que requieren atención inmediata.
                            </p>

                            @foreach($anomaliesList as $anomaly)
                            <div style="background-color: #fffaf0; border: 1px solid #ffeeba; border-left: 5px solid #f08a1f; padding: 18px; margin-bottom: 20px; border-radius: 6px;">
                                <h3 style="margin: 0 0 12px 0; font-size: 17px; color: #b75200; font-family: Arial, Helvetica, sans-serif;">
                                    Unidad: {{ $anomaly['unidad'] }} <span style="font-size: 13px; color: #888; font-weight: normal;">({{ $anomaly['tipo'] }})</span>
                                </h3>

                                @if(count($anomaly['puntos_fallidos']) > 0)
                                    <p style="margin: 0 0 5px 0; font-size: 14px; color: #333; font-weight: bold; font-family: Arial, Helvetica, sans-serif;">
                                        Puntos de inspección marcados con "NO":
                                    </p>
                                    <ul style="margin: 0 0 15px 0; padding-left: 20px; color: #d32f2f; font-size: 14px; font-family: Arial, Helvetica, sans-serif; line-height: 1.5;">
                                        @foreach($anomaly['puntos_fallidos'] as $punto)
                                            <li>{{ $punto }}</li>
                                        @endforeach
                                    </ul>
                                @endif

                                <p style="margin: 0; font-size: 14px; color: #555; line-height: 1.5; font-family: Arial, Helvetica, sans-serif; background: #ffffff; padding: 10px; border: 1px dashed #f08a1f; border-radius: 4px;">
                                    <strong style="color: #333;">Comentarios del conductor:</strong><br>
                                    {{ $anomaly['comentarios'] }}
                                </p>
                            </div>
                            @endforeach

                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top: 35px;">
                                <tr>
                                    <td align="center">
                                        <table role="presentation" border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td align="center" bgcolor="#d32f2f" style="border-radius: 6px;">
                                                    <a href="https://vescore.tech" target="_blank" style="font-size: 15px; font-family: Arial, Helvetica, sans-serif; color: #ffffff; text-decoration: none; border-radius: 6px; padding: 14px 28px; display: inline-block; font-weight: bold;">
                                                        Acceder al Sistema
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="background-color: #f8fafc; padding: 20px; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0 0 8px 0; font-size: 12px; color: #64748b; font-family: Arial, Helvetica, sans-serif; line-height: 1.5;">
                                Este es un mensaje de notificación prioritaria del Sistema de Seguridad y Gerenciamiento de Viajes (QHSE).
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
