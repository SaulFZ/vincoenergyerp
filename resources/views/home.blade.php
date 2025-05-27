<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vinco ERP</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/home.css') }}" />
</head>

<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <img src="{{ asset('assets/img/logovinco2.png') }}" alt="Logo Vinco" />
                <h2 class="dashboard-title">Panel de Áreas Vinco Energy</h2>
            </div>
            <div class="user-profile">
                <div id="userAvatarContainer" class="user-avatar-container">
                    <div class="user-avatar">
                        @php
                            $initials = '';
                            $name = session('auth_user')['name'] ?? 'Usuario';
                            $nameParts = explode(' ', $name);
                            foreach ($nameParts as $part) {
                                if (!empty($part)) {
                                    $initials .= substr($part, 0, 1);
                                }
                                if (strlen($initials) >= 2) {
                                    break;
                                }
                            }
                        @endphp
                        <span>{{ strtoupper($initials) }}</span>
                    </div>
                    <div class="user-info">
                        <h1>{{ session('auth_user')['name'] ?? 'Usuario' }}</h1>
                    </div>
                    <i class="fas fa-chevron-down chevron-down"></i>
                </div>
                <div id="userDropdown" class="user-dropdown">
                    <div class="dropdown-header">
                        <div class="user-avatar">
                            <span>{{ strtoupper($initials) }}</span>
                        </div>
                        <div class="dropdown-header-info">
                            <p>{{ session('auth_user')['name'] ?? 'Usuario' }}</p>
                            <p>{{ session('auth_user')['email'] ?? 'correo@vinco.com' }}</p>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="logout-form">
                        @csrf
                        <button type="submit" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i>
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
                <div id="dropdownOverlay" class="dropdown-overlay"></div>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="department">
            <div class="card" id="card-administracion" data-area="administracion" data-route="/administracion">
                <div class="card-shine"></div>
                <img src="assets/img/areas/administracion.png" alt="Administracion" />
                <div class="card-content">
                    <h1>Administración</h1>
                    <p>Gestión eficiente de recursos y procesos administrativos.</p>
                </div>
            </div>

            <div class="card" id="card-qhse" data-area="qhse" data-route="/qhse">
                <div class="card-shine"></div>
                <img src="assets/img/areas/QHSE.png" alt="QHSE" />
                <div class="card-content">
                    <h1>QHSE</h1>
                    <p>Calidad, Salud, Seguridad y Medio Ambiente.</p>
                </div>
            </div>

            <div class="card" id="card-ventas" data-area="ventas" data-route="/ventas">
                <div class="card-shine"></div>
                <img src="assets/img/areas/ventas.png" alt="Ventas" />
                <div class="card-content">
                    <h1>Ventas</h1>
                    <p>Estrategias de venta y relaciones con clientes.</p>
                </div>
            </div>

            <div class="card" id="card-recursos-humanos" data-area="recursoshumanos" data-route="/recursoshumanos">
                <div class="card-shine"></div>
                <img src="assets/img/areas/rh.png" alt="Recursos Humanos" />
                <div class="card-content">
                    <h1>Recursos Humanos</h1>
                    <p>Gestión del personal y desarrollo organizacional.</p>
                </div>
            </div>

            <div class="card" id="card-suministro" data-area="suministro" data-route="/suministro">
                <div class="card-shine"></div>
                <img src="assets/img/areas/suministro.png" alt="Suministro" />
                <div class="card-content">
                    <h1>Suministro</h1>
                    <p>Gestión de la cadena de suministro y logística.</p>
                </div>
            </div>

            <div class="card" id="card-operaciones" data-area="operaciones" data-route="/operaciones">
                <div class="card-shine"></div>
                <img src="assets/img/areas/operaciones.png" alt="Operaciones" />
                <div class="card-content">
                    <h1>Operaciones</h1>
                    <p>Coordinación y optimización de procesos operativos.</p>
                </div>
            </div>

            <div class="card" id="card-sistemas" data-area="sistemas" data-route="/sistemas">
                <div class="card-shine"></div>
                <img src="assets/img/areas/sistemas.png" alt="Sistemas" />
                <div class="card-content">
                    <h1>Sistemas</h1>
                    <p>Desarrollo y mantenimiento de infraestructura tecnológica.</p>
                </div>
            </div>

            <div class="card" id="card-almacen" data-area="almacen" data-route="/almacen">
                <div class="card-shine"></div>
                <img src="assets/img/areas/almacen.png" alt="Almacén" />
                <div class="card-content">
                    <h1>Almacén</h1>
                    <p>Gestión de inventario y almacenamiento de productos.</p>
                </div>
            </div>

            <div class="card" id="card-geociencias" data-area="geociencias" data-route="/geociencias">
                <div class="card-shine"></div>
                <img src="assets/img/areas/geociencias.png" alt="Geociencias" />
                <div class="card-content">
                    <h1>Geociencias</h1>
                    <p>Estudios geológicos y geofísicos para exploración y producción.</p>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2024 ERP Vinco. Todos los derechos reservados.</p>
    </footer>

    <!-- Cargar el script desde un archivo externo -->
    <script src="{{ asset('assets/js/home.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

</html>
