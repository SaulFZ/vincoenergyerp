<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $data['full_name'] ?? 'Tarjeta' }} · Vinco Energy Services</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primario: #1a2a5f;
            --secundario: #2c4285;
            --acento: #d67e29;
            --fondo: #f4f7fe;
            --blanco: #ffffff;
            --borde: #e2e8f0;
            --texto-suave: #64748b;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Sora', system-ui, sans-serif;
            background: var(--fondo);
            color: var(--primario);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        .card {
            width: 100%;
            max-width: 380px;
            background: var(--blanco);
            border: 1px solid var(--borde);
            border-radius: 20px;
            overflow: hidden;
            text-align: center;
        }

        .card-top {
            background: var(--primario);
            color: var(--blanco);
            padding: 18px 16px 16px;
            font-size: .78rem;
            font-weight: 600;
            letter-spacing: .14em;
            text-transform: uppercase;
            border-bottom: 3px solid var(--acento);
        }

        .card-body {
            padding: 28px 24px 24px;
        }

        .photo {
            width: 112px;
            height: 112px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 0 auto 16px;
            border: 3px solid var(--fondo);
            background: var(--fondo);
        }

        .name {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
            color: var(--primario);
        }

        .position {
            margin: 6px 0 0;
            font-size: .92rem;
            font-weight: 500;
            color: var(--secundario);
        }

        .department {
            margin: 2px 0 0;
            font-size: .82rem;
            color: var(--texto-suave);
        }

        .info {
            list-style: none;
            padding: 0;
            margin: 24px 0 24px;
            text-align: left;
        }

        .info li {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-top: 1px solid var(--borde);
            font-size: .92rem;
        }

        .info li:last-child {
            border-bottom: 1px solid var(--borde);
        }

        .info a {
            color: var(--primario);
            text-decoration: none;
            word-break: break-all;
        }

        .ico {
            width: 36px;
            height: 36px;
            flex-shrink: 0;
            border-radius: 10px;
            background: var(--fondo);
            color: var(--secundario);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .9rem;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            background: var(--primario);
            color: var(--blanco);
            font-weight: 600;
            text-decoration: none;
            font-size: .95rem;
            transition: background .2s ease;
        }

        .btn:hover,
        .btn:focus-visible {
            background: var(--secundario);
        }

        .footer {
            font-size: .72rem;
            color: var(--texto-suave);
            margin: 14px 0 0;
        }
    </style>
</head>

<body>
    <main class="card">
        <div class="card-top">Vinco Energy Services</div>

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

            @if (! empty($data['phone_work']) || ! empty($data['email']))
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
            @endif

            <a class="btn" href="{{ route('employee-card.vcard', $token) }}">Guardar contacto</a>
            <p class="footer">Tarjeta de contacto oficial</p>
        </div>
    </main>
</body>

</html>