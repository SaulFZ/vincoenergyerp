<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Reseteo básico para clientes de correo */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; }

        /* Estilos principales usando la paleta solicitada */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #4a5568; /* medium-gray */
            line-height: 1.6;
            background-color: #f7fafc;
            margin: 0;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #e2e8f0; /* light-gray */
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .header {
            background-color: #2d3748; /* dark-gray */
            color: #ffffff;
            padding: 25px 20px;
            text-align: center;
        }

        .header h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .content {
            padding: 30px 25px;
        }

        .content p {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 15px;
        }

        .details-box {
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 6px;
            margin: 25px 0;
            border: 1px solid #e2e8f0; /* light-gray */
            border-left: 5px solid #4a5568; /* medium-gray */
        }

        .details-box ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .details-box li {
            margin-bottom: 12px;
            font-size: 15px;
            border-bottom: 1px dashed #e2e8f0; /* light-gray */
            padding-bottom: 8px;
        }

        .details-box li:last-child {
            margin-bottom: 0;
            border-bottom: none;
            padding-bottom: 0;
        }

        .details-box strong {
            color: #2d3748; /* dark-gray */
            display: inline-block;
            min-width: 170px;
        }

        .footer {
            background-color: #e2e8f0; /* light-gray */
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #4a5568; /* medium-gray */
        }

        .footer p {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Notificación de Área Comisionada</h2>
        </div>

        <div class="content">
            <p>Se ha registrado una nueva actividad de <strong>Comisión</strong> en el Load Chart. A continuación, se detallan los datos del registro:</p>

            <div class="details-box">
                <ul>
                    <li>
                        <strong>Empleado:</strong>
                        {{ $employee->full_name ?? ($employee->first_name . ' ' . $employee->last_name) }}
                    </li>
                    <li>
                        <strong>Fecha de Actividad:</strong>
                        {{ $fechaFormat }}
                    </li>
                    <li>
                        <strong>Área Comisionada:</strong>
                        {{ $area ? $area->name : 'N/A' }}
                    </li>
                    <li>
                        <strong>Tipo de Comisión:</strong>
                        {{ $activityType ?? 'N/A' }}
                    </li>
                </ul>
            </div>

            <p>Recibes este correo porque eres revisor/aprobador del empleado, responsable en la jerarquía del área, o cuentas con los permisos de notificación correspondientes.</p>
        </div>

        <div class="footer">
            <p>Este es un correo generado automáticamente por el Sistema ERP de Vinco Energy Services.</p>
            <p>Por favor, no respondas a este mensaje.</p>
        </div>
    </div>
</body>
</html>
