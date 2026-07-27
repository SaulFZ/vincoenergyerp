<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 20px; }
        .container { background-color: #ffffff; max-width: 600px; margin: 0 auto; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; }
        .header { background-color: #0c2d5e; color: #ffffff; padding: 20px; text-align: center; }
        .content { padding: 30px; color: #334155; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .data-table td { padding: 10px; border-bottom: 1px solid #e2e8f0; }
        .data-label { font-weight: bold; width: 30%; color: #475569; }
        .footer { background-color: #f1f5f9; padding: 15px; text-align: center; font-size: 12px; color: #64748b; }
        .btn { display: inline-block; background-color: #1a5fb4; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Nuevo Ticket Registrado</h2>
        </div>

        <div class="content">
            <p>Hola, equipo de soporte.</p>
            <p>Se ha generado un nuevo ticket en el sistema que requiere atención.</p>

            <table class="data-table">
                <tr>
                    <td class="data-label">Folio:</td>
                    <td><strong>{{ $ticket->folio }}</strong></td>
                </tr>
                <tr>
                    <td class="data-label">Solicitante:</td>
                    <td>{{ $ticket->user->name ?? 'Usuario del Sistema' }}</td>
                </tr>
                <tr>
                    <td class="data-label">Departamento:</td>
                    <td>{{ $ticket->department_code }}</td>
                </tr>
                <tr>
                    <td class="data-label">Asunto:</td>
                    <td>{{ $ticket->subject }}</td>
                </tr>
            </table>

            <div style="margin-top: 20px; padding: 15px; background-color: #f8fafc; border-left: 4px solid #1a5fb4;">
                <p style="margin: 0;"><strong>Descripción:</strong><br><br>
                {{ $ticket->description }}</p>
            </div>

            <div style="text-align: center;">
                <!-- Cambia esta URL por la ruta real de tu sistema -->
                <a href="https://vescore.tech" class="btn">Ir al Sistema</a>
            </div>
        </div>

        <div class="footer">
            Este es un mensaje automático de Vinco ERP. Por favor, no respondas a este correo.
        </div>
    </div>
</body>
</html>
