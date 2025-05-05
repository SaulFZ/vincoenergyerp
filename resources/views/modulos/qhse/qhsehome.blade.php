<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QHSE Systems Dashboard</title>
    <!-- Font Awesome CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome Script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
    <style>
        :root {
            --naranja: #D67E29;
            --naranja-claro: #fa9333;
            --naranja-oscuro: #b96520;
            --azul: #334c95;
            --azul-oscuro: #263671;
            --azul-claro: #4b56cc;
            --blanco: #fff;
            --negro: #000;
            --gris-claro: #f8fafc;
            --gris-medio: #e2e8f0;
            --gris-oscuro: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--gris-claro);
            color: var(--negro);
            line-height: 1.6;
        }

        .container {
            max-width: 2000px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .header {
            background-color: var(--azul);
            color: var(--blanco);
            padding: 0.25rem 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: end;
            max-width: 1900px;
            margin: 0 auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo img {
            height: 60px;
            width: auto;
        }

        .dashboard-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--blanco);
            margin-bottom: 0.5rem;
        }

        .user-profile {
            position: relative;
        }

        .user-avatar-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        .user-avatar-container:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, var(--naranja), var(--naranja-oscuro));
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--blanco);
            font-weight: bold;
            font-size: 0.9rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .user-avatar::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 8px;
            height: 8px;
            background-color: #4caf50;
            border-radius: 50%;
            border: 1.5px solid var(--blanco);
        }

        .user-info h1 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--blanco);
        }

        .chevron-down {
            color: rgba(255, 255, 255, 0.7);
            margin-left: 0.25rem;
            transition: transform 0.3s ease;
            font-size: 0.8rem;
        }

        .user-avatar-container.active .chevron-down {
            transform: rotate(180deg);
        }

        .user-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            width: 250px;
            background-color: var(--blanco);
            border-radius: 6px;
            margin-top: 0.5rem;
            z-index: 1000;
            overflow: hidden;
            opacity: 0;
            transform: translateY(-20px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .user-dropdown.show {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .dropdown-header {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--gris-claro);
        }

        .dropdown-header .user-avatar {
            width: 32px;
            height: 32px;
            margin-right: 1rem;
        }

        .dropdown-header-info p:first-child {
            font-weight: 600;
            color: var(--azul-claro);
            font-size: 0.9rem;
        }

        .dropdown-header-info p:last-child {
            color: var(--negro);
            font-size: 0.8rem;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            width: 100%;
            background-color: var(--rojo);
            color: var(--blanco);
            padding: 0.75rem 1rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 0.9rem;
        }

        .logout-btn:hover {
            background-color: #ff1a0f;
        }

        .logout-btn i {
            margin-right: 1rem;
            font-size: 0.9rem;
        }

        .dropdown-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 999;
            display: none;
            background-color: rgba(0, 0, 0, 0.5);
        }

        /* Main Content */
        main {
            padding: 1rem 0;
            min-height: calc(100vh - 140px);
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 1rem;
        }

        .dashboard-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--azul);
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .card {
            background: var(--blanco);
            border-radius: 1rem;
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            border: 1px solid var(--gris-medio);
            position: relative;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card:hover {
            transform: translateY(-4px) scale(1.01);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
            border-color: var(--azul-claro);
        }

        .card-image {
            width: 100%;
            height: 200px;
            object-fit: contain;
            border-bottom: 1px solid var(--gris-medio);
            transition: transform 0.5s ease;
        }

        .card:hover .card-image {
            transform: scale(1.05);
        }

        .card-content {
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--gris-claro) 0%, var(--blanco) 100%);
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            transition: all 0.3s ease;
        }

        .card:hover .card-content {
            background: linear-gradient(135deg, var(--azul-claro) 0%, var(--azul) 100%);
            color: var(--blanco);
        }

        .card-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--azul);
            transition: color 0.3s ease;
        }

        .card:hover .card-title {
            color: var(--blanco);
        }

        .card-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--gris-claro);
            border-radius: 0.5rem;
            color: var(--naranja);
            transition: all 0.3s ease;
        }

        .card:hover .card-icon {
            background-color: var(--naranja);
            color: var(--blanco);
            transform: rotate(360deg);
        }

        .status-indicator {
            margin-top: auto;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #28a745;
            /* Color verde para el texto */
            background-color: #f8f9fa;
            /* Color gris claro */
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            width: fit-content;
            transition: all 0.3s ease;
            border: 1px solid #d4edda;
            /* Color verde claro para el borde */
        }

        .card:hover .status-indicator {
            background-color: #28a745;
            /* Color verde al pasar el ratón */
            color: #ffffff;
            /* Color blanco para el texto al pasar el ratón */
            border-color: #d4edda;
            /* Color verde claro para el borde al pasar el ratón */
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #28a745;
            /* Color verde para el punto de estado */
            position: relative;
        }

        .card:hover .status-dot {
            background-color: #ffffff;
            /* Color blanco para el punto de estado al pasar el ratón */
        }

        .status-dot::after {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 50%;
            background-color: #28a745;
            /* Color verde para el pulso */
            animation: pulse 2s infinite;
        }

        .card:hover .status-dot::after {
            background-color: #ffffff;
            /* Color blanco para el pulso al pasar el ratón */
        }


        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 0.8;
            }

            50% {
                transform: scale(1.5);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 0.8;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .grid {
                grid-template-columns: 1fr;
            }
        }

        /* Footer Styles */
        footer {
            background: var(--azul-oscuro);
            color: var(--blanco);
            padding: 2rem 0;
            margin-top: 3rem;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .footer-info {
            font-size: 0.875rem;
            opacity: 0.8;
        }

        .footer-links {
            display: flex;
            gap: 2rem;
        }

        .footer-link {
            color: var(--blanco);
            text-decoration: none;
            font-size: 0.875rem;
            opacity: 0.8;
            transition: all 0.2s ease;
        }

        .footer-link:hover {
            opacity: 1;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
            }

            .footer-links {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <img src="/img/logovinco2.png" alt="Logo Empresa" />
                <h2 class="">Modulo de QHSE</h2>
            </div>
            <div class="user-profile">
                <div id="userAvatarContainer" class="user-avatar-container">
                    <div class="user-avatar">
                        <span>SF</span>
                    </div>
                    <div class="user-info">
                        <h1>Saul Falcon Perez</h1>
                    </div>
                    <i class="fas fa-chevron-down chevron-down"></i>
                </div>
                <div id="userDropdown" class="user-dropdown">
                    <div class="dropdown-header">
                        <div class="user-avatar">
                            <span>SF</span>
                        </div>
                        <div class="dropdown-header-info">
                            <p>Saul Falcon</p>
                            <p>saul.falcon@empresa.com</p>
                        </div>
                    </div>
                    <button class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        Cerrar Sesión
                    </button>
                </div>
                <div id="dropdownOverlay" class="dropdown-overlay"></div>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="dashboard-header">
                <h2 class="dashboard-title">Sistemas de Gestión QHSE</h2>
            </div>

            <div class="grid">
                <a href="../qhse/vescap/loginvescap.html" class="card">
                    <img src="../qhse/img/vescap.png" alt="VESCAP" class="card-image">
                    <div class="card-content">
                        <h3 class="card-title">
                            <span class="card-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </span>
                            VESCAP
                        </h3>
                        <div class="status-indicator">
                            <div class="status-dot"></div>
                            <span>Sistema Activo</span>
                        </div>
                    </div>
                </a>

                <a href="../qhse/tacisqhse/loginincidencias.html" class="card">
                    <img src="../qhse/img/incidencia.png" alt="Gestión de Incidencias QHSE" class="card-image">
                    <div class="card-content">
                        <h3 class="card-title">
                            <span class="card-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                                <!-- Cambiado el icono para reflejar incidencias -->
                            </span>
                            Gestión de Incidencias QHSE
                        </h3>
                        <div class="status-indicator">
                            <div class="status-dot"></div>
                            <span>Sistema Activo</span> <!-- Puedes cambiarlo según el estado deseado -->
                        </div>
                    </div>
                </a>


                <a href="#" class="card">
                    <img src="/api/placeholder/400/200" alt="Sistema de Auditorías" class="card-image">
                    <div class="card-content">
                        <h3 class="card-title">
                            <span class="card-icon">
                                <i class="fas fa-clipboard-check"></i>
                            </span>
                            Sistema de Auditorías
                        </h3>
                        <div class="status-indicator">
                            <div class="status-dot"></div>
                            <span>Sistema Activo</span>
                        </div>
                    </div>
                </a>

                <a href="#" class="card">
                    <img src="/api/placeholder/400/200" alt="Nuevo Sistema" class="card-image">
                    <div class="card-content">
                        <h3 class="card-title">
                            <span class="card-icon">
                                <i class="fas fa-plus-circle"></i>
                            </span>
                            Nuevo Sistema
                        </h3>
                        <div class="status-indicator">
                            <div class="status-dot"></div>
                            <span>Próximamente</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-info">
                    <p>&copy; 2024 QHSE Systems Dashboard</p>
                </div>
            </div>
        </div>
    </footer>


</body>

</html>
