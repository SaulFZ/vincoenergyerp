<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios y Permisos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.12/sweetalert2.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('assets/css/sistemas/gestionderoles/index.css') }}" rel="stylesheet">
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="header-left">
                <div class="logo">E</div>
                <div class="header-title">
                    <i class="fas fa-users-cog"></i>
                    <h1>Gestión de Usuarios y Permisos</h1>
                </div>
            </div>
            @include('components.layouts._user-profile')
        </div>
    </header>

    <!-- Main Container -->
    <div class="container">
        <!-- Controls -->
        <div class="controls">
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Buscar usuario...">
                <i class="fas fa-search search-icon"></i>
            </div>
            <div class="action-buttons">
                <button class="btn btn-primary" onclick="openNewUserModal()">
                    <i class="fas fa-plus"></i>
                    Nuevo Usuario
                </button>
                <button class="btn btn-secondary">
                    <i class="fas fa-download"></i>
                    Exportar
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Permisos</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <!-- Los datos se muestran mediante javascript -->
                </tbody>
            </table>
            <!-- Paginación -->
            <div class="pagination-container">
                <div class="pagination-info">
                    <div class="items-per-page">
                        <label for="itemsPerPage">Mostrar:</label>
                        <select id="itemsPerPage">
                            <option value="5">5 usuarios</option>
                            <option value="10" selected>10 usuarios</option>
                            <option value="25">25 usuarios</option>
                            <option value="50">50 usuarios</option>
                        </select>
                    </div>
                </div>

                <div class="pagination-nav">
                    <div class="page-info">
                        Mostrando <span id="startItem">1</span>-<span id="endItem">10</span> de <span
                            id="totalItems">50</span> usuarios
                    </div>
                    <div class="pagination-controls">
                        <button class="page-btn" id="firstPage" title="Primera página">
                            <i class="fas fa-angle-double-left"></i>
                        </button>
                        <button class="page-btn" id="prevPage" title="Página anterior">
                            <i class="fas fa-angle-left"></i>
                        </button>
                        <button class="page-btn active" id="currentPageBtn">1</button>
                        <button class="page-btn" id="nextPage" title="Página siguiente">
                            <i class="fas fa-angle-right"></i>
                        </button>
                        <button class="page-btn" id="lastPage" title="Última página">
                            <i class="fas fa-angle-double-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Usuario -->
    <div class="modal-overlay" id="newUserModal">
        <div class="modal">
            <div class="modal-header">
                <h3><i class="fas fa-user-plus"></i>Nuevo Usuario</h3>
                <button class="close-btn" onclick="closeNewUserModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="permissionsForm">
                    <!-- Sección de datos del usuario - Nueva disposición de 3 campos por fila -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="section-title"><i class="fas fa-user me-2"></i>Datos del Usuario</h4>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Foto del Empleado</label>
                            <div class="employee-photo-container">
                                <div class="photo-preview" id="photoPreview">
                                    <img id="photoDisplay"
                                        src="{{ asset('assets/img/fotouser.png') }}"alt="Foto del empleado">
                                </div>
                                <div class="photo-actions">
                                    <input type="file" id="photoInput" accept="image/*" style="display: none;">
                                    <button type="button" class="btn btn-sm btn-primary"
                                        onclick="document.getElementById('photoInput').click()">
                                        <i class="fas fa-upload"></i> Cambiar Foto
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" id="removePhotoBtn"
                                        style="display: none;">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </div>
                                <input type="hidden" id="photo" name="photo">
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="name" class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="Ingrese el nombre completo" required autocomplete="off">
                            <input type="hidden" id="employee_id" name="employee_id">
                            <div id="employeeSuggestions" class="suggestions-dropdown" style="display: none;"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="username" class="form-label">Nombre de Usuario</label>
                            <input type="text" class="form-control" id="username" name="username"
                                placeholder="Ingrese el nombre de usuario" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Ingrese una contraseña segura" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="ejemplo@correo.com" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">Estado</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="" selected disabled>Seleccione un estado</option>
                                <option value="active">Activo</option>
                                <option value="inactive">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <!-- Dentro del modal-body, después de la sección de Datos del Usuario -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="permission-tabs">
                                <button class="tab-btn active" data-tab="module-permissions">
                                    <i class="fas fa-cubes me-2"></i>Configuración de Permisos
                                </button>
                                <button class="tab-btn" data-tab="role-permissions">
                                    <i class="fas fa-user-tag me-2"></i>Rol y Permisos
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Contenido de Configuración de Permisos (tu sección actual) -->
                    <div id="module-permissions" class="tab-content active">
                        <!-- Todo tu contenido actual de Configuración de Permisos aquí -->
                        <div class="row">
                            <div class="col-12">
                                <h4 class="section-title"><i class="fas fa-key me-2"></i>Configuración de Permisos
                                </h4>
                                <p class="text-muted mb-4">Seleccione los módulos a los que el usuario tendrá acceso.
                                </p>
                            </div>

                            <!-- Columna 1 -->
                            <div class="col-md-6">
                                <!-- Módulo Administración -->
                                <div class="module-area compact">
                                    <div class="module-header" onclick="toggleModule('administracion-body')">
                                        <h5 class="module-title"><i class="fas fa-cogs me-2"></i>Administración</h5>
                                        <label class="switch" onclick="event.stopPropagation()">
                                            <input type="checkbox" class="module-toggle"
                                                data-module="administracion">
                                            <span class="slider"></span>
                                        </label>
                                    </div>

                                    <div class="module-body" id="administracion-body">
                                        <div class="permission-item">
                                            <span>Gestión de reembolsos</span>
                                            <label class="switch">
                                                <input type="checkbox" name="permissions[administracion][reembolsos]">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Módulo QHSE -->
                                <div class="module-area compact">
                                    <div class="module-header" onclick="toggleModule('qhse-body')">
                                        <h5 class="module-title"><i class="fas fa-shield-alt me-2"></i>QHSE</h5>
                                        <label class="switch" onclick="event.stopPropagation()">
                                            <input type="checkbox" class="module-toggle" data-module="qhse">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    <div class="module-body" id="qhse-body">
                                        <div class="permission-item">
                                            <span>Gerenciamiento</span>
                                            <label class="switch">
                                                <input type="checkbox" name="permissions[qhse][gerenciamiento]">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                        <div class="permission-item">
                                            <span>VESCAP</span>
                                            <label class="switch">
                                                <input type="checkbox" name="permissions[qhse][vescap]">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                        <div class="permission-item">
                                            <span>Incidencias</span>
                                            <label class="switch">
                                                <input type="checkbox" name="permissions[qhse][incidencias]">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                        <div class="permission-item">
                                            <span>Auditorías</span>
                                            <label class="switch">
                                                <input type="checkbox" name="permissions[qhse][auditorias]">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Módulo Ventas -->
                                <div class="module-area compact">
                                    <div class="module-header" onclick="toggleModule('ventas-body')">
                                        <h5 class="module-title"><i class="fas fa-chart-line me-2"></i>Ventas</h5>
                                        <label class="switch" onclick="event.stopPropagation()">
                                            <input type="checkbox" class="module-toggle" data-module="ventas">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    <div class="module-body" id="ventas-body">
                                        <div class="permission-item">
                                            <span>Clientes</span>
                                            <label class="switch">
                                                <input type="checkbox" name="permissions[ventas][clientes]">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                        <div class="permission-item">
                                            <span>Cotizaciones</span>
                                            <label class="switch">
                                                <input type="checkbox" name="permissions[ventas][cotizaciones]">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                        <div class="permission-item">
                                            <span>Oportunidades</span>
                                            <label class="switch">
                                                <input type="checkbox" name="permissions[ventas][oportunidades]">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Módulo Recursos Humanos -->
                                <div class="module-area compact">
                                    <div class="module-header" onclick="toggleModule('recursoshumanos-body')">
                                        <h5 class="module-title"><i class="fas fa-users me-2"></i>Recursos Humanos
                                        </h5>
                                        <label class="switch" onclick="event.stopPropagation()">
                                            <input type="checkbox" class="module-toggle"
                                                data-module="recursoshumanos">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    <div class="module-body" id="recursoshumanos-body">
                                        <div class="permission-item">
                                            <span>Altas de empleados</span>
                                            <label class="switch">
                                                <input type="checkbox"
                                                    name="permissions[recursoshumanos][altasempleados]">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                        <div class="permission-item">
                                            <span>L&O Chart</span>
                                            <label class="switch">
                                                <input type="checkbox" name="permissions[recursoshumanos][loadchart]">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Módulo Sistemas -->
                                <div class="module-area compact">
                                    <div class="module-header" onclick="toggleModule('sistemas-body')">
                                        <h5 class="module-title"><i class="fas fa-laptop-code me-2"></i>Sistemas</h5>
                                        <label class="switch" onclick="event.stopPropagation()">
                                            <input type="checkbox" class="module-toggle" data-module="sistemas">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    <div class="module-body" id="sistemas-body">
                                        <div class="permission-item">
                                            <span>Gestión de roles</span>
                                            <label class="switch">
                                                <input type="checkbox" name="permissions[sistemas][gestionderoles]">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                        <div class="permission-item">
                                            <span>Gestión de tickets</span>
                                            <label class="switch">
                                                <input type="checkbox" name="permissions[sistemas][tickets]">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Columna 2 -->
                            <div class="col-md-6">
                                <!-- Módulo Suministro -->
                                <div class="module-area compact">
                                    <div class="module-header" onclick="toggleModule('suministro-body')">
                                        <h5 class="module-title"><i class="fas fa-truck me-2"></i>Suministro</h5>
                                        <label class="switch" onclick="event.stopPropagation()">
                                            <input type="checkbox" class="module-toggle" data-module="suministro">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    <div class="module-body" id="suministro-body">
                                        <div class="permission-item">
                                            <span>Pedidos</span>
                                            <label class="switch">
                                                <input type="checkbox" name="permissions[suministro][pedidos]">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Módulo Operaciones -->
                                <div class="module-area compact">
                                    <div class="module-header" onclick="toggleModule('operaciones-body')">
                                        <h5 class="module-title"><i class="fas fa-cog me-2"></i>Operaciones</h5>
                                        <label class="switch" onclick="event.stopPropagation()">
                                            <input type="checkbox" class="module-toggle" data-module="operaciones">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    <div class="module-body" id="operaciones-body">
                                        <div class="permission-item">
                                            <span>Procesos</span>
                                            <label class="switch">
                                                <input type="checkbox" name="permissions[operaciones][procesos]">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Módulo Almacén -->
                                <div class="module-area compact">
                                    <div class="module-header" onclick="toggleModule('almacen-body')">
                                        <h5 class="module-title"><i class="fas fa-warehouse me-2"></i>Almacén</h5>
                                        <label class="switch" onclick="event.stopPropagation()">
                                            <input type="checkbox" class="module-toggle" data-module="almacen">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    <div class="module-body" id="almacen-body">
                                        <div class="permission-item">
                                            <span>Inventario</span>
                                            <label class="switch">
                                                <input type="checkbox" name="permissions[almacen][inventario]">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Módulo Geociencias -->
                                <div class="module-area compact">
                                    <div class="module-header" onclick="toggleModule('geociencias-body')">
                                        <h5 class="module-title"><i class="fas fa-globe-americas me-2"></i>Geociencias
                                        </h5>
                                        <label class="switch" onclick="event.stopPropagation()">
                                            <input type="checkbox" class="module-toggle" data-module="geociencias">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    <div class="module-body" id="geociencias-body">
                                        <div class="permission-item">
                                            <span>Exploraciones</span>
                                            <label class="switch">
                                                <input type="checkbox" name="permissions[geociencias][exploraciones]">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                        <div class="permission-item">
                                            <span>Análisis</span>
                                            <label class="switch">
                                                <input type="checkbox" name="permissions[geociencias][analisis]">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Nueva sección de Rol y Permisos -->
                    <div id="role-permissions" class="tab-content">
                        <div class="row">
                            <div class="col-12">
                                <h4 class="section-title"><i class="fas fa-user-tag me-2"></i>Rol y Permisos</h4>
                                <p class="text-muted mb-4">Asigne un rol y defina los permisos específicos para este
                                    usuario.</p>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="user_role" class="form-label">Rol del Usuario</label>
                                <select class="form-select" id="user_role" name="user_role">
                                    <option value="" selected disabled>Seleccione un rol</option>
                                    <!-- Las opciones se llenarán dinámicamente con JavaScript -->
                                </select>
                            </div>

                            <!-- Reemplaza la sección de permisos del rol con esto: -->
                            <div class="col-12">
                                <h5 class="subsection-title"><i class="fas fa-key me-2"></i>Permisos Directos</h5>
                                <div class="permissions-grid" id="directPermissionsContainer">
                                    <!-- Los permisos se cargarán dinámicamente aquí -->
                                    <div class="text-center py-3">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Cargando permisos...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12 text-end">
                            <button type="button" onclick="closeNewUserModal()"
                                class="btn btn-secondary me-2">Cancelar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Guardar Permisos
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.12/sweetalert2.min.js"></script>
    <script src="{{ asset('assets/js/sistemas/gestionderoles/index.js') }}"></script>

</body>

</html>
