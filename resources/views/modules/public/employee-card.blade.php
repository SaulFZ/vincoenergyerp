<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $employee->full_name ?? 'Tarjeta' }} · Vinco Energy Services</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            border-radius: 24px;
            overflow: hidden;
            text-align: center;
        }

        .card-top {
            background: var(--primario);
            padding: 24px 20px 72px 20px;
            border-bottom: 3px solid var(--acento);
        }

        .header-brand {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center; /* El texto queda centrado */
            min-height: 28px;        /* Misma altura que el logo */
        }

        .logo {
            position: absolute;
            left: 0;                 /* El logo se queda a la izquierda */
            top: 50%;
            transform: translateY(-50%);
            display: block;
            max-height: 28px;
            width: auto;
            filter: brightness(0) invert(1);
        }

        .header-company {
            color: var(--blanco);
            font-size: 1.15rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
            text-align: center;
            padding: 0 48px;         /* Evita que el texto se encime con el logo */
        }

        .card-body {
            padding: 0 28px 28px;
        }

        .photo {
            width: 128px;
            height: 128px;
            border-radius: 50%;
            object-fit: cover;
            border: 6px solid var(--blanco);
            background: var(--fondo);
            margin: -64px auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--secundario);
            font-size: 2.4rem;
        }

        .name {
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
            color: var(--primario);
        }

        .position {
            margin: 8px 0 24px;
            font-size: .95rem;
            font-weight: 600;
            color: var(--secundario);
        }

        .info-list {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: left;
        }

        .info-list li {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 0;
            border-top: 1px solid var(--borde);
            font-size: .92rem;
        }

        .info-list li:last-child {
            border-bottom: 1px solid var(--borde);
        }

        .ico-wrap {
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            border-radius: 12px;
            background: var(--fondo);
            color: var(--secundario);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .label-text {
            display: block;
            font-size: .72rem;
            color: var(--texto-suave);
            margin-bottom: 2px;
        }

        .info-list a {
            color: var(--primario);
            text-decoration: none;
            font-weight: 600;
            word-break: break-word;
            transition: color .2s ease;
        }

        .info-list a:hover,
        .info-list a:focus-visible {
            color: var(--acento);
        }

        .footer {
            margin: 24px 0 0;
            font-size: .75rem;
            font-weight: 600;
            color: var(--texto-suave);
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="card-top">
            <div class="header-brand">
                <img class="logo" src="{{ asset('assets/img/logob.png') }}" alt="Logo">
                <h2 class="header-company">Vinco Energy Services</h2>
            </div>
        </div>

        <div class="card-body">
            @if ($employee->photo)
                <img class="photo"
                     src="{{ route('employee-card.photo', $token) }}"
                     alt="Foto de {{ $employee->full_name ?? 'empleado' }}">
            @else
                <div class="photo" aria-label="Sin foto">
                    <i class="fas fa-user" aria-hidden="true"></i>
                </div>
            @endif

            <h1 class="name">{{ $employee->full_name ?? 'Colaborador' }}</h1>
            <p class="position">{{ $employee->job_title ?? 'Colaborador' }}</p>

            <ul class="info-list">

                {{-- Correo Institucional --}}
                @if ($employee->user && $employee->user->email)
                    <li>
                        <div class="ico-wrap"><i class="fas fa-envelope" aria-hidden="true"></i></div>
                        <div>
                            <span class="label-text">Correo electrónico</span>
                            <a href="mailto:{{ $employee->user->email }}">{{ $employee->user->email }}</a>
                        </div>
                    </li>
                @endif

                @php
                    // Teléfono personal del empleado (columna `phone` de la tabla employees)
                    $telefonoPersonal = trim((string) ($employee->phone ?? ''));
                    // Teléfono de la empresa (fijo para todos)
                    $telefonoEmpresa = '9931515588';
                @endphp

                {{-- Teléfono personal: solo aparece si el empleado lo tiene --}}
                @if ($telefonoPersonal !== '')
                    <li>
                        <div class="ico-wrap"><i class="fas fa-mobile-screen-button" aria-hidden="true"></i></div>
                        <div>
                            <span class="label-text">Teléfono personal</span>
                            <a href="tel:{{ preg_replace('/[^\d+]/', '', $telefonoPersonal) }}">{{ $telefonoPersonal }}</a>
                        </div>
                    </li>
                @endif

                {{-- Teléfono de la empresa: siempre aparece --}}
                <li>
                    <div class="ico-wrap"><i class="fas fa-phone" aria-hidden="true"></i></div>
                    <div>
                        <span class="label-text">Teléfono empresa</span>
                        <a href="tel:{{ $telefonoEmpresa }}">{{ $telefonoEmpresa }}</a>
                    </div>
                </li>

                {{-- Sitio Web --}}
                <li>
                    <div class="ico-wrap"><i class="fas fa-globe" aria-hidden="true"></i></div>
                    <div>
                        <span class="label-text">Sitio web</span>
                        <a href="https://vincoenergy.com/" target="_blank" rel="noopener">vincoenergy.com</a>
                    </div>
                </li>

                {{-- Ubicación --}}
                <li>
                    <div class="ico-wrap"><i class="fas fa-map-marker-alt" aria-hidden="true"></i></div>
                    <div>
                        <span class="label-text">Ubicación</span>
                        <a href="https://maps.google.com/?q=Carretera+Villahermosa-Cárdenas+Km+0.8,+Col.+Anacleto+Canabal+3ra+Secc.,+CP+86103,+Villahermosa,+Tabasco" target="_blank" rel="noopener" style="font-size: 0.8rem; line-height: 1.4; display: inline-block;">
                            Carretera Villahermosa-Cárdenas Km 0.8, Col. Anacleto Canabal 3ra Secc., CP 86103, Villahermosa, Tabasco.
                        </a>
                    </div>
                </li>
            </ul>

            <p class="footer">Tarjeta de contacto oficial</p>
        </div>
    </main>
</body>
</html>