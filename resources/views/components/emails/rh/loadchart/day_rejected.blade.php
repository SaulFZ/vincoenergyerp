<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Elementos Rechazados - Sistema LoadChart</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 650px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 25px;
            text-align: center;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 15px;
        }
        .logo {
            max-width: 160px;
            height: auto;
        }
        .header h1 {
            margin: 10px 0 0 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .alert-box {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 5px solid #dc3545;
        }
        .alert-box h3 {
            margin: 0 0 10px 0;
            color: #721c24;
            font-size: 18px;
        }
        .details-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
        }
        .details-box h3 {
            margin-top: 0;
            color: #495057;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .info-grid {
            /* Usamos flexbox para asegurar el layout en clientes de correo */
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .info-item {
            width: 50%; /* 2 columnas */
            box-sizing: border-box;
            padding-right: 15px;
            margin-bottom: 15px;
        }
        .info-label {
            font-weight: bold;
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .info-value {
            color: #495057;
            font-size: 16px;
        }
        .items-section {
            margin-top: 20px;
        }
        .items-title {
            font-weight: bold;
            color: #495057;
            margin-bottom: 10px;
            font-size: 17px;
        }
        .item-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        /* Ajuste: Reducimos espaciado en los elementos de la lista */
        .item-list li {
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            margin-bottom: 10px;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .item-type {
            font-weight: 700;
            color: #dc3545;
            font-size: 15px; /* Ligeramente más pequeño */
            display: block;
            margin-bottom: 5px;
        }
        .activity-detail {
            font-size: 14px;
            color: #495057;
            margin-top: 4px;
        }
        /* Ajuste: Reducimos espaciado en el motivo de rechazo */
        .reason-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 10px;
            margin-top: 8px;
            border-left: 4px solid #ffc107;
        }
        .reason-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .reason-text {
            color: #856404;
            margin: 0;
            line-height: 1.4;
            font-style: italic;
        }
        .actions-box {
            background-color: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin-top: 25px;
        }
        .actions-title {
            margin-top: 0;
            color: #495057;
            font-size: 18px;
        }
        .actions-list {
            padding-left: 20px;
            margin-bottom: 0;
        }
        .actions-list li {
            margin-bottom: 8px;
            line-height: 1.5;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 13px;
        }
        .label-highlight {
            font-weight: bold; /* Uso de font-weight en lugar de <strong> */
        }
        .badge-info {
            display: inline-block;
            padding: 4px 8px;
            background-color: #dc3545;
            color: white;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
            white-space: nowrap;
        }
        @media (max-width: 600px) {
            .info-item {
                width: 100%; /* 1 columna en móviles */
                padding-right: 0;
            }
            .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-container">
                <img src="{{ asset('assets/img/logovinco2.png') }}" alt="Logo Vinco" class="logo" width="160">
            </div>
            <h1>Elementos Rechazados - Sistema LoadChart</h1>
        </div>

        <div class="content">
            <div class="alert-box">
                <h3>Atención: Elementos Requieren Corrección</h3>
                <p style="margin: 0; color: #721c24;">Uno o más elementos de tu registro del día han sido rechazados y requieren tu atención inmediata en el <span class="label-highlight">Sistema LoadChart</span>.</p>
            </div>

            <div class="details-box">
                <h3>Información General</h3>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Empleado:</span>
                        <span class="info-value">{{ $employeeName }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fecha del Registro:</span>
                        <span class="info-value">{{ $date }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Rechazado por:</span>
                        <span class="info-value">{{ $rejectedBy }}</span>
                    </div>
                    {{-- INICIO: CAMBIO SOLICITADO (Añadir "Por corregir" después del número) --}}
                    <div class="info-item">
                        <span class="info-label">Total Rechazado:</span>
                        <span class="info-value">{{ count($rejectedItems) }} <span class="badge-info">Por corregir</span></span>
                    </div>
                    {{-- FIN: CAMBIO SOLICITADO --}}
                </div>

                @if(!empty($rejectedItems))
                <div class="items-section">
                    <div class="items-title">Detalle de Elementos Rechazados:</div>
                    <ul class="item-list">
                        @foreach($rejectedItems as $item)
                        <li>
                            <div>
                                {{-- Tipo de Ítem (Ej: Bono: Desayuno, Actividad: Trabajo en Base) --}}
                                <span class="item-type">{{ $item['type'] ?? 'Elemento Desconocido' }}</span>

                                {{-- Detalles Adicionales (Ej: Nombre del proyecto, Notas) --}}
                                @if(isset($item['details']) && $item['details'])
                                <div class="activity-detail" style="font-style: italic; margin-top: 2px;">{{ $item['details'] }}</div>
                                @endif
                            </div>

                            {{-- Motivo de Rechazo Individual (Recuadro Amarillo) --}}
                            @if(isset($item['rejection_reason']) && $item['rejection_reason'])
                            <div class="reason-box">
                                <div class="reason-title">Motivo del Rechazo:</div>
                                <p class="reason-text">"{{ $item['rejection_reason'] }}"</p>
                            </div>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>

            <div class="actions-box">
                <h4 class="actions-title">Acciones Requeridas</h4>
                <ol class="actions-list">
                    <li><span class="label-highlight">Revisa detenidamente</span> cada ítem rechazado y su motivo específico.</li>
                    <li><span class="label-highlight">Corrige la información</span> según las indicaciones.</li>
                    <li><span class="label-highlight">Vuelve a enviar el registro</span> para una nueva revisión y aprobación en el sistema.</li>
                    <li><span class="label-highlight">Contacta al supervisor</span> si necesitas clarificaciones adicionales.</li>
                </ol>
            </div>
        </div>

        <div class="footer">
            <p>Este es un mensaje automático del <span class="label-highlight">Sistema LoadChart</span> - Vinco</p>
            <p>Fecha de envío: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
            <p>© {{ date('Y') }} Vinco - Todos los derechos reservados</p>
        </div>
    </div>
</body>
</html>
