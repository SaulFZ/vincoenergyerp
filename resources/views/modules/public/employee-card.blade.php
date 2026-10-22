<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $data['full_name'] ?? 'Tarjeta' }} · Vinco Energy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Sora', system-ui, sans-serif;
            background: linear-gradient(160deg, #0f172a, #1e293b);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            color: #0f172a;
        }
        .card {
            width: 100%;
            max-width: 360px;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, .35);
            text-align: center;
        }
        .card-top {
            background: #0f172a;
            color: #fff;
            padding: 14px;
            font-size: .8rem;
            letter-spacing: .12em;
            text-transform: uppercase;
            font-weight: 600;
        }
        .card-body { padding: 28px 24px 24px; }
        .photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 4px 14px rgba(0, 0, 0, .15);
            margin: -80px auto 12px;
            display: block;
            background: #e5e7eb;
        }
        .name { font-size: 1.3rem; font-weight: 700; margin: 0; }
        .position { color: #64748b; margin: 4px 0 4px; font-size: .9rem; }
        .department { color: #94a3b8; margin: 0 0 20px; font-size: .82rem; }
        .info { list-style: none; padding: 0; margin: 0 0 22px; text-align: left; }
        .info li {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-top: 1px solid #e2e8f0;
            font-size: .92rem;
        }
        .info a { color: inherit; text-decoration: none; word-break: break-all; }
        .ico {
            width: 36px; height: 36px; flex-shrink: 0;
            border-radius: 10px; background: #fff7ed; color: #ea580c;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700;
        }
        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            background: #ea580c;
            color: #fff;
            font-weight: 600;
            text-decoration: none;
            font-size: .95rem;
        }
        .btn:hover { background: #c2410c; }
        .footer { font-size: .72rem; color: #94a3b8; margin-top: 14px; }
    </style>
</head>

<body>
    <main class="card">
        <div class="card-top">Vinco Energy</div>

        <div class="card-body">
            @if (! empty($data['photo']))
                <img class="photo"
                     src="{{ route('employee-card.photo', $token) }}"
                     alt="Foto de {{ $data['full_name'] ?? 'empleado' }}">
            @else
                <img class="photo" src="{{ asset('assets/img/fotouser.png') }}" alt="Sin foto">
            @endif

            <h1 class="name">{{ $data['full_name'] ?? 'Colaborador' }}</h1>

            @if (! empty($data['position']))
                <p class="position">{{ $data['position'] }}</p>
            @endif

            @if (! empty($data['department']))
                <p class="department">{{ $data['department'] }}</p>
            @endif

            <ul class="info">
                @if (! empty($data['phone_work']))
                    <li>
                        <span class="ico">T</span>
                        <a href="tel:{{ preg_replace('/\s+/', '', $data['phone_work']) }}">{{ $data['phone_work'] }}</a>
                    </li>
                @endif
                @if (! empty($data['email']))
                    <li>
                        <span class="ico">@</span>
                        <a href="mailto:{{ $data['email'] }}">{{ $data['email'] }}</a>
                    </li>
                @endif
            </ul>

            <a class="btn" href="{{ route('employee-card.vcard', $token) }}">Guardar contacto</a>
            <p class="footer">Tarjeta de contacto oficial</p>
        </div>
    </main>
</body>

</html>