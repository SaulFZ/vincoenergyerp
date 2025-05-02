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
                <img src="{{ asset('assets/img/logovinco2.png') }}" alt="Logo Empresa" />
                <h2 class="dashboard-title">Panel de Areas Vinco Energy</h2>
            </div>
            <div class="user-profile">
                <div id="userAvatarContainer" class="user-avatar-container">
                    <div class="user-avatar">
                        @php
                            $initials = '';
                            $name = session('auth_user')['name'] ?? 'Usuario';
                            $nameParts = explode(' ', $name);
                            foreach($nameParts as $part) {
                                if(!empty($part)) {
                                    $initials .= substr($part, 0, 1);
                                }
                                if(strlen($initials) >= 2) break;
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
        <!-- HTML con IDs únicos -->
        <div class="department">
            <div class="card" id="card-administracion">
                <img src="{{ asset('assets/img/areas/administracion.png') }}" alt="Administracion" />
                <div class="card-content">
                    <h1>Administracion</h1>
                    <p>Gestión eficiente de recursos y procesos administrativos.</p>
                </div>
            </div>

            <div class="card" id="card-qhse">
                <img src="{{ asset('assets/img/areas/QHSE.png') }}" alt="QHSE" />
                <div class="card-content">
                    <h1>QHSE</h1>
                    <p>Calidad, Salud, Seguridad y Medio Ambiente.</p>
                </div>
            </div>

            <div class="card" id="card-ventas">
                <img src="{{ asset('assets/img/areas/ventas.png') }}" alt="Ventas" />
                <div class="card-content">
                    <h1>Ventas</h1>
                    <p>Estrategias de venta y relaciones con clientes.</p>
                </div>
            </div>
            <div class="card" id="card-recursos-humanos">
                <img src="{{ asset('assets/img/areas/rh.png') }}" alt="Recursos Humanos" />
                <div class="card-content">
                    <h1>Recursos Humanos</h1>
                    <p>Gestión del personal y desarrollo organizacional.</p>
                </div>
            </div>

            <div class="card" id="card-suministro">
                <img src="{{ asset('assets/img/areas/suministro.png') }}" alt="Suministro" />
                <div class="card-content">
                    <h1>Suministro</h1>
                    <p>Gestión de la cadena de suministro y logística.</p>
                </div>
            </div>

            <div class="card" id="card-operaciones">
                <img src="{{ asset('assets/img/areas/operaciones.png') }}" alt="Operaciones" />
                <div class="card-content">
                    <h1>Operaciones</h1>
                    <p>Coordinación y optimización de procesos operativos.</p>
                </div>
            </div>

            <div class="card" id="card-sistemas">
                <img src="{{ asset('assets/img/areas/sistemas.png') }}" alt="Sistemas" />
                <div class="card-content">
                    <h1>Sistemas</h1>
                    <p>Desarrollo y mantenimiento de infraestructura tecnológica.</p>
                </div>
            </div>

            <div class="card" id="card-almacen">
                <img src="{{ asset('assets/img//areas/almacen.png') }}" alt="Almacen" />
                <div class="card-content">
                    <h1>Almacen</h1>
                    <p>Gestión de inventario y almacenamiento de productos.</p>
                </div>
            </div>

            <div class="card" id="card-geociencias">
                <img src="{{ asset('assets/img/areas/geociencias.png') }}" alt="Geociencias" />
                <div class="card-content">
                    <h1>Geociencias</h1>
                    <p>
                        Estudios geológicos y geofísicos para exploración y producción.
                    </p>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const userAvatarContainer = document.getElementById(
                "userAvatarContainer"
            );
            const userDropdown = document.getElementById("userDropdown");
            const dropdownOverlay = document.getElementById("dropdownOverlay");

            userAvatarContainer.addEventListener("click", (e) => {
                e.stopPropagation();
                userAvatarContainer.classList.toggle("active");
                userDropdown.classList.toggle("show");
                dropdownOverlay.classList.toggle("active");
            });

            dropdownOverlay.addEventListener("click", () => {
                userAvatarContainer.classList.remove("active");
                userDropdown.classList.remove("show");
                dropdownOverlay.classList.remove("active");
            });

            // Prevent dropdown from closing when clicking inside
            userDropdown.addEventListener("click", (e) => {
                e.stopPropagation();
            });
        });

        // Configuración de rutas para cada área
        const ROUTES_CONFIG = {
            administracion: "{{ asset('modulos/administracion/loginadministracion.html') }}",
            qhse: "{{ asset('modulos/qhse/loginqhse.html') }}",
            ventas: "{{ asset('modulos/ventas/loginventas.html') }}",
            "recursos-humanos": "{{ asset('modulos/recursoshumanos/loginrecursoshumanos.html') }}",
            suministro: "{{ asset('modulos/suministro/loginsuministro.html') }}",
            operaciones: "{{ asset('modulos/operaciones/loginoperaciones.html') }}",
            sistemas: "{{ asset('modulos/sistemas/loginsistemas.html') }}",
            almacen: "{{ asset('modulos/almacen/loginalmacen.html') }}",
            geociencias: "{{ asset('modulos/geociencias/logingeociencias.html') }}"
        };

        class CardNavigationHandler {
            constructor() {
                this.addEventListeners();
                this.initializeStyles();
            }

            addEventListeners() {
                document.querySelectorAll(".card").forEach((card) => {
                    card.addEventListener("click", this.handleCardClick.bind(this));
                    card.style.cursor = "pointer";
                });
            }

            handleCardClick(event) {
                const card = event.currentTarget;
                const cardId = card.id.replace("card-", "");
                const route = ROUTES_CONFIG[cardId];

                if (route) {
                    this.addClickEffect(card);
                    setTimeout(() => this.navigateToLogin(route), 200);
                } else {
                    console.warn(`Ruta no encontrada para: ${cardId}`);
                }
            }

            addClickEffect(card) {
                card.classList.add("card-clicked");
                setTimeout(() => card.classList.remove("card-clicked"), 200);
            }

            navigateToLogin(route) {
                try {
                    window.location.href = route;
                } catch (error) {
                    console.error("Error al navegar:", error);
                    window.open(route, "_blank");
                }
            }

            initializeStyles() {
                const styleElement = document.createElement("style");
                styleElement.textContent = `
      .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
      }

      .card-clicked {
        transform: scale(0.95);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      }

      .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      }
    `;
                document.head.appendChild(styleElement);
            }
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener("DOMContentLoaded", () => {
            new CardNavigationHandler();
        });
    </script>
    <footer class="footer">
        <p>&copy; 2024 ERP Vinco. Todos los derechos reservados.</p>
    </footer>
</body>

</html>
