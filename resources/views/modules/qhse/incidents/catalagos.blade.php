<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../css/stylecatalogo.css" />
</head>

<body>
    <!-- Header Section -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <img src="/modulos/qhse/img/logovinco1.png" alt="Logo Empresa" />
                <h2 class="dashboard-title"></h2>
            </div>
            <nav class="header-nav">
                <ul>
                    <li>
                        <a href="../view/index.html" class="nav-link">
                            <i class="fas fa-home"></i>
                            <span>Inicio</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link">
                            <i class="fas fa-tasks"></i>
                            <span>Reporte de Acciones Abiertas</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link">
                            <i class="fas fa-clipboard-check"></i>
                            <span>Monitoreo, Verificación y Evaluación</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link">
                            <i class="fas fa-book"></i>
                            <span>Catálogos</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link">
                            <i class="fas fa-chart-pie"></i>
                            <span>Estadísticas</span>
                        </a>
                    </li>
                </ul>

            </nav>
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

    <main class="main-container">
        <main class="main-container">
            <section class="catalogo-section">
                <div class="catalogo-header">
                    <h2>Catálogos de QHSE</h2>
                </div>

                <div class="catalogo-menu">
                    <a href="#" class="section-link active" data-section="categoria-accion">Categoría Acción</a>
                    <a href="#" class="section-link" data-section="tipo-peligro">Tipo Peligro</a>
                    <a href="#" class="section-link" data-section="tipo-riesgo">Tipo Riesgo</a>
                    <a href="#" class="section-link" data-section="probabilidad-riesgo">Probabilidad Riesgo</a>
                    <a href="#" class="section-link" data-section="categoria-qhse">Categoría QHSE</a>
                </div>

                <div class="section-content active" id="categoria-accion">
                    <div class="section-header">Categoría Acción</div>
                    <div class="section-search">
                        <input type="text" class="section-buscar" placeholder="Buscar categoría...">
                        <button class="btn-buscar">
                            <i class="fas fa-search"></i>
                            Buscar
                        </button>
                        <button class="btn-nuevo">
                            <i class="fas fa-plus"></i>
                            Nuevo
                        </button>
                        <button class="btn-limpiar">
                            <i class="fas fa-broom"></i>
                            Limpiar
                        </button>
                    </div>

                    <div class="table-container">
                        <table class="section-table">
                            <thead>
                                <tr>
                                    <th class="checkbox-column">
                                        <input type="checkbox" class="section-checkbox">
                                    </th>
                                    <th>Num</th>
                                    <th>Categoría</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="checkbox-column">
                                        <input type="checkbox" class="section-checkbox">
                                    </td>
                                    <td>1</td>
                                    <td>CALIDAD</td>
                                    <td>Descripción de la categoría CALIDAD</td>
                                    <td class="action-column">
                                        <button class="edit-btn"><i class="fas fa-edit"></i>Editar</button>
                                        <button class="delete-btn"><i class="fas fa-trash"></i>Eliminar</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="checkbox-column">
                                        <input type="checkbox" class="section-checkbox">
                                    </td>
                                    <td>2</td>
                                    <td>SEGURIDAD</td>
                                    <td>Descripción de la categoría SEGURIDAD</td>
                                    <td class="action-column">
                                        <button class="edit-btn">
                                            <i class="fas fa-edit"></i>
                                            Editar
                                        </button>
                                        <button class="delete-btn"><i class="fas fa-trash"></i>
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tipo Peligro Section -->
                <div class="section-content" id="tipo-peligro">
                    <div class="section-header">Tipo Peligro</div>
                    <div class="section-search">
                        <input type="text" class="section-buscar" placeholder="Buscar tipo de peligro...">
                        <button class="btn-buscar">
                            <i class="fas fa-search"></i>
                            Buscar
                        </button>
                        <button class="btn-nuevo">
                            <i class="fas fa-plus"></i>
                            Nuevo
                        </button>
                        <button class="btn-limpiar">
                            <i class="fas fa-broom"></i>
                            Limpiar
                        </button>
                    </div>

                    <div class="table-container">
                        <table class="section-table">
                            <thead>
                                <tr>
                                    <th class="checkbox-column">
                                        <input type="checkbox" class="section-checkbox">
                                    </th>
                                    <th>Num</th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="checkbox-column">
                                        <input type="checkbox" class="section-checkbox">
                                    </td>
                                    <td>1</td>
                                    <td>MECÁNICO</td>
                                    <td>Riesgos asociados con maquinaria y equipos</td>
                                    <td class="action-column">
                                        <button class="edit-btn"><i class="fas fa-edit"></i>Editar</button>
                                        <button class="delete-btn"><i class="fas fa-trash"></i>Eliminar</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="checkbox-column">
                                        <input type="checkbox" class="section-checkbox">
                                    </td>
                                    <td>2</td>
                                    <td>QUÍMICO</td>
                                    <td>Exposición a sustancias químicas peligrosas</td>
                                    <td class="action-column">
                                        <button class="edit-btn"><i class="fas fa-edit"></i>Editar</button>
                                        <button class="delete-btn"><i class="fas fa-trash"></i>Eliminar</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tipo Riesgo Section -->
                <div class="section-content" id="tipo-riesgo">
                    <div class="section-header">Tipo Riesgo</div>
                    <div class="section-search">
                        <input type="text" class="section-buscar" placeholder="Buscar tipo de riesgo...">
                        <button class="btn-buscar">
                            <i class="fas fa-search"></i>
                            Buscar
                        </button>
                        <button class="btn-nuevo">
                            <i class="fas fa-plus"></i>
                            Nuevo
                        </button>
                        <button class="btn-limpiar">
                            <i class="fas fa-broom"></i>
                            Limpiar
                        </button>
                    </div>

                    <div class="table-container">
                        <table class="section-table">
                            <thead>
                                <tr>
                                    <th class="checkbox-column">
                                        <input type="checkbox" class="section-checkbox">
                                    </th>
                                    <th>Num</th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="checkbox-column">
                                        <input type="checkbox" class="section-checkbox">
                                    </td>
                                    <td>1</td>
                                    <td>ALTO</td>
                                    <td>Riesgo con potencial de daño grave</td>
                                    <td class="action-column">
                                        <button class="edit-btn"><i class="fas fa-edit"></i>Editar</button>
                                        <button class="delete-btn"><i class="fas fa-trash"></i>Eliminar</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="checkbox-column">
                                        <input type="checkbox" class="section-checkbox">
                                    </td>
                                    <td>2</td>
                                    <td>MEDIO</td>
                                    <td>Riesgo con potencial de daño moderado</td>
                                    <td class="action-column">
                                        <button class="edit-btn"><i class="fas fa-edit"></i>Editar</button>
                                        <button class="delete-btn"><i class="fas fa-trash"></i>Eliminar</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Probabilidad Riesgo Section -->
                <div class="section-content" id="probabilidad-riesgo">
                    <div class="section-header">Probabilidad Riesgo</div>
                    <div class="section-search">
                        <input type="text" class="section-buscar" placeholder="Buscar probabilidad...">
                        <button class="btn-buscar">
                            <i class="fas fa-search"></i>
                            Buscar
                        </button>
                        <button class="btn-nuevo">
                            <i class="fas fa-plus"></i>
                            Nuevo
                        </button>
                        <button class="btn-limpiar">
                            <i class="fas fa-broom"></i>
                            Limpiar
                        </button>
                    </div>

                    <div class="table-container">
                        <table class="section-table">
                            <thead>
                                <tr>
                                    <th class="checkbox-column">
                                        <input type="checkbox" class="section-checkbox">
                                    </th>
                                    <th>Num</th>
                                    <th>Nivel</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="checkbox-column">
                                        <input type="checkbox" class="section-checkbox">
                                    </td>
                                    <td>1</td>
                                    <td>PROBABLE</td>
                                    <td>Alta probabilidad de ocurrencia</td>
                                    <td class="action-column">
                                        <button class="edit-btn"><i class="fas fa-edit"></i>Editar</button>
                                        <button class="delete-btn"><i class="fas fa-trash"></i>Eliminar</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="checkbox-column">
                                        <input type="checkbox" class="section-checkbox">
                                    </td>
                                    <td>2</td>
                                    <td>POSIBLE</td>
                                    <td>Probabilidad media de ocurrencia</td>
                                    <td class="action-column">
                                        <button class="edit-btn"><i class="fas fa-edit"></i>Editar</button>
                                        <button class="delete-btn"><i class="fas fa-trash"></i>Eliminar</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Categoría QHSE Section -->
                <div class="section-content" id="categoria-qhse">
                    <div class="section-header">Categoría QHSE</div>
                    <div class="section-search">
                        <input type="text" class="section-buscar" placeholder="Buscar categoría QHSE...">
                        <button class="btn-buscar">
                            <i class="fas fa-search"></i>
                            Buscar
                        </button>
                        <button class="btn-nuevo">
                            <i class="fas fa-plus"></i>
                            Nuevo
                        </button>
                        <button class="btn-limpiar">
                            <i class="fas fa-broom"></i>
                            Limpiar
                        </button>
                    </div>

                    <div class="table-container">
                        <table class="section-table">
                            <thead>
                                <tr>
                                    <th class="checkbox-column">
                                        <input type="checkbox" class="section-checkbox">
                                    </th>
                                    <th>Num</th>
                                    <th>Categoría</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="checkbox-column">
                                        <input type="checkbox" class="section-checkbox">
                                    </td>
                                    <td>1</td>
                                    <td>HSE</td>
                                    <td>Health, Safety and Environment</td>
                                    <td class="action-column">
                                        <button class="edit-btn"><i class="fas fa-edit"></i>Editar</button>
                                        <button class="delete-btn"><i class="fas fa-trash"></i>Eliminar</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="checkbox-column">
                                        <input type="checkbox" class="section-checkbox">
                                    </td>
                                    <td>2</td>
                                    <td>QA</td>
                                    <td>Quality Assurance</td>
                                    <td class="action-column">
                                        <button class="edit-btn"><i class="fas fa-edit"></i>Editar</button>
                                        <button class="delete-btn"><i class="fas fa-trash"></i>Eliminar</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </main>


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sectionLinks = document.querySelectorAll('.section-link');

            sectionLinks.forEach(link => {
                link.addEventListener('click', (event) => {
                    event.preventDefault();

                    // Remove active class from all links
                    sectionLinks.forEach(l => l.classList.remove('active'));

                    // Add active class to clicked link
                    link.classList.add('active');

                    const sectionId = link.dataset.section;
                    const sectionContent = document.getElementById(sectionId);

                    // Hide all section content
                    document.querySelectorAll('.section-content').forEach(section => {
                        section.classList.remove('active');
                    });

                    // Show the selected section content
                    sectionContent.classList.add('active');
                });
            });
        });
    </script>
</body>

</html>
