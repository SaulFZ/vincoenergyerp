<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios y Permisos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.12/sweetalert2.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('assets/css/sistemas/gestionderoles/index.css') }}" rel="stylesheet">
</head>
<body>

<!-- ════════ HEADER ════════ -->
<header class="header">
    <div class="header-inner">
        <div class="header-brand">
            <div class="brand-logo">E</div>
            <div class="brand-text">
                <span class="brand-label">Sistema</span>
                <h1>Usuarios <span class="accent">&amp;</span> Permisos</h1>
            </div>
        </div>
        <nav class="header-nav">
            <div class="nav-pill">
                <i class="fas fa-users-cog"></i>
                <span>Gestión de Roles</span>
            </div>
            @include('components.layouts._user-profile')
        </nav>
    </div>
</header>

<!-- ════════ MAIN ════════ -->
<main class="main-wrapper">

    <!-- Stats -->
    <div class="stats-bar" id="statsBar">
        <div class="stat-card">
            <div class="stat-icon stat-icon--blue"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <span class="stat-number" id="statTotalNum">—</span>
                <span class="stat-label">Total usuarios</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon--green"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <span class="stat-number" id="statActiveNum">—</span>
                <span class="stat-label">Activos</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon--red"><i class="fas fa-times-circle"></i></div>
            <div class="stat-info">
                <span class="stat-number" id="statInactiveNum">—</span>
                <span class="stat-label">Inactivos</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon--amber"><i class="fas fa-cubes"></i></div>
            <div class="stat-info">
                <span class="stat-number">10</span>
                <span class="stat-label">Módulos</span>
            </div>
        </div>
    </div>

    <!-- Panel tabla -->
    <div class="panel">
        <div class="panel-controls">
            <div class="search-wrap">
                <i class="fas fa-search search-ico"></i>
                <input type="text" class="search-input" placeholder="Buscar usuario...">
            </div>
            <div class="controls-right">
                <div class="items-select-wrap">
                    <label>Mostrar</label>
                    <select id="itemsPerPage" class="items-select">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <label>por página</label>
                </div>
                <button class="btn-export" title="Exportar">
                    <i class="fas fa-download"></i>
                    <span>Exportar</span>
                </button>
                <button class="btn-new" onclick="openNewUserModal()">
                    <i class="fas fa-plus"></i>
                    <span>Nuevo Usuario</span>
                </button>
            </div>
        </div>

        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Permisos</th>
                        <th>Estado</th>
                        <th class="th-actions">Acciones</th>
                    </tr>
                </thead>
                <tbody id="userTableBody"></tbody>
            </table>
            <div class="empty-state" id="emptyState" style="display:none;">
                <div class="empty-icon"><i class="fas fa-user-slash"></i></div>
                <p class="empty-title">Sin usuarios encontrados</p>
                <p class="empty-sub">Intenta otra búsqueda o crea un nuevo usuario</p>
            </div>
        </div>

        <div class="pagination-bar">
            <span class="page-count">
                Mostrando <b id="startItem">1</b>–<b id="endItem">10</b> de <b id="totalItems">0</b>
            </span>
            <div class="pag-controls">
                <button class="pag-btn" id="firstPage" title="Primera"><i class="fas fa-angle-double-left"></i></button>
                <button class="pag-btn" id="prevPage"  title="Anterior"><i class="fas fa-angle-left"></i></button>
                <button class="pag-btn pag-current" id="currentPageBtn">1</button>
                <button class="pag-btn" id="nextPage"  title="Siguiente"><i class="fas fa-angle-right"></i></button>
                <button class="pag-btn" id="lastPage"  title="Última"><i class="fas fa-angle-double-right"></i></button>
            </div>
        </div>
    </div>

</main>

<!-- ════════ MODAL NUEVO / EDITAR ════════ -->
<div class="modal-backdrop" id="newUserModal">
    <div class="modal-box">

        <div class="modal-top">
            <div class="modal-heading">
                <div class="modal-icon-wrap"><i class="fas fa-user-plus" id="modalIconHeader"></i></div>
                <div>
                    <p class="modal-subtitle">Gestión de accesos</p>
                    <h2 class="modal-title" id="modalTitle">Nuevo Usuario</h2>
                </div>
            </div>
            <button class="modal-close" onclick="closeNewUserModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="modal-body">
            <form id="permissionsForm" autocomplete="off">

                <!-- Sección: Datos -->
                <section class="form-section">
                    <div class="section-header">
                        <div class="section-dot"></div>
                        <h3>Datos del Usuario</h3>
                    </div>
                    <div class="form-grid">
                        <!-- Foto -->
                        <div class="photo-col">
                            <label class="field-label">Foto</label>
                            <div class="photo-ring" id="photoPreview">
                                <img id="photoDisplay" src="{{ asset('assets/img/fotouser.png') }}" alt="Foto">
                                <div class="photo-overlay" onclick="document.getElementById('photoInput').click()">
                                    <i class="fas fa-camera"></i>
                                </div>
                            </div>
                            <div class="photo-btns">
                                <input type="file" id="photoInput" accept="image/*" style="display:none;">
                                <button type="button" class="btn-photo-change" onclick="document.getElementById('photoInput').click()">
                                    <i class="fas fa-upload"></i> Cambiar
                                </button>
                                <button type="button" class="btn-photo-remove" id="removePhotoBtn" style="display:none;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <input type="hidden" id="photo" name="photo">
                        </div>

                        <!-- Campos -->
                        <div class="fields-col">
                            <div class="fields-row">
                                <div class="field-group" style="position:relative;">
                                    <label class="field-label" for="name">Nombre Completo</label>
                                    <div class="field-icon-wrap">
                                        <i class="fas fa-user field-ico"></i>
                                        <input type="text" class="field-input" id="name" name="name" placeholder="Nombre completo" required autocomplete="off">
                                    </div>
                                    <input type="hidden" id="employee_id" name="employee_id">
                                    <div id="employeeSuggestions" class="suggestions-dropdown" style="display:none;"></div>
                                </div>
                                <div class="field-group">
                                    <label class="field-label" for="username">Usuario</label>
                                    <div class="field-icon-wrap">
                                        <i class="fas fa-fingerprint field-ico"></i>
                                        <input type="text" class="field-input" id="username" name="username" placeholder="nombre_usuario" required>
                                    </div>
                                </div>
                            </div>
                            <div class="fields-row">
                                <div class="field-group">
                                    <label class="field-label" for="email">Correo</label>
                                    <div class="field-icon-wrap">
                                        <i class="fas fa-envelope field-ico"></i>
                                        <input type="email" class="field-input" id="email" name="email" placeholder="correo@empresa.com" required>
                                    </div>
                                </div>
                                <div class="field-group">
                                    <label class="field-label" for="password">Contraseña</label>
                                    <div class="field-icon-wrap">
                                        <i class="fas fa-lock field-ico"></i>
                                        <input type="password" class="field-input" id="password" name="password" placeholder="••••••••" required>
                                        <button type="button" class="pwd-toggle" id="pwdToggle" onclick="togglePassword()">
                                            <i class="fas fa-eye" id="pwdEye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="fields-row">
                                <div class="field-group">
                                    <label class="field-label" for="status">Estado</label>
                                    <div class="field-icon-wrap">
                                        <i class="fas fa-toggle-on field-ico"></i>
                                        <select class="field-input field-select" id="status" name="status" required>
                                            <option value="" disabled selected>Seleccionar estado</option>
                                            <option value="active">Activo</option>
                                            <option value="inactive">Inactivo</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="field-group">
                                    <label class="field-label" for="user_role">Rol</label>
                                    <div class="field-icon-wrap">
                                        <i class="fas fa-user-tag field-ico"></i>
                                        <select class="field-input field-select" id="user_role" name="user_role">
                                            <option value="" disabled selected>Seleccionar rol</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Sección: Permisos -->
                <section class="form-section">
                    <div class="section-header">
                        <div class="section-dot"></div>
                        <h3>Permisos y Accesos</h3>
                    </div>

                    <div class="perm-tabs">
                        <button type="button" class="perm-tab active" data-tab="module-permissions">
                            <i class="fas fa-cubes"></i> Módulos
                        </button>
                        <button type="button" class="perm-tab" data-tab="role-permissions">
                            <i class="fas fa-shield-alt"></i> Permisos Directos
                        </button>
                    </div>

                    <!-- Tab Módulos -->
                    <div id="module-permissions" class="perm-tab-content active">
                        <p class="tab-hint">Activa los módulos a los que el usuario tendrá acceso.</p>
                        <div class="modules-grid">

                            <div class="mod-card">
                                <div class="mod-card-head" onclick="toggleModule('administracion-body')">
                                    <div class="mod-card-left">
                                        <span class="mod-icon mod--admin"><i class="fas fa-cogs"></i></span>
                                        <span class="mod-name">Administración</span>
                                    </div>
                                    <label class="tog" onclick="event.stopPropagation()">
                                        <input type="checkbox" class="module-toggle" data-module="administracion">
                                        <span class="tog-track"><span class="tog-thumb"></span></span>
                                    </label>
                                </div>
                                <div class="mod-card-body" id="administracion-body">
                                    <label class="perm-row"><input type="checkbox" name="permissions[administracion][reembolsos]"><span class="perm-check-icon"><i class="fas fa-check"></i></span><span>Gestión de reembolsos</span></label>
                                </div>
                            </div>

                            <div class="mod-card">
                                <div class="mod-card-head" onclick="toggleModule('qhse-body')">
                                    <div class="mod-card-left">
                                        <span class="mod-icon mod--qhse"><i class="fas fa-shield-alt"></i></span>
                                        <span class="mod-name">QHSE</span>
                                    </div>
                                    <label class="tog" onclick="event.stopPropagation()">
                                        <input type="checkbox" class="module-toggle" data-module="qhse">
                                        <span class="tog-track"><span class="tog-thumb"></span></span>
                                    </label>
                                </div>
                                <div class="mod-card-body" id="qhse-body">
                                    <label class="perm-row"><input type="checkbox" name="permissions[qhse][gerenciamiento]"><span class="perm-check-icon"><i class="fas fa-check"></i></span><span>Gerenciamiento</span></label>
                                    <label class="perm-row"><input type="checkbox" name="permissions[qhse][vescap]"><span class="perm-check-icon"><i class="fas fa-check"></i></span><span>VESCAP</span></label>
                                    <label class="perm-row"><input type="checkbox" name="permissions[qhse][incidencias]"><span class="perm-check-icon"><i class="fas fa-check"></i></span><span>Incidencias</span></label>
                                    <label class="perm-row"><input type="checkbox" name="permissions[qhse][auditorias]"><span class="perm-check-icon"><i class="fas fa-check"></i></span><span>Auditorías</span></label>
                                </div>
                            </div>

                            <div class="mod-card">
                                <div class="mod-card-head" onclick="toggleModule('ventas-body')">
                                    <div class="mod-card-left">
                                        <span class="mod-icon mod--ventas"><i class="fas fa-chart-line"></i></span>
                                        <span class="mod-name">Ventas</span>
                                    </div>
                                    <label class="tog" onclick="event.stopPropagation()">
                                        <input type="checkbox" class="module-toggle" data-module="ventas">
                                        <span class="tog-track"><span class="tog-thumb"></span></span>
                                    </label>
                                </div>
                                <div class="mod-card-body" id="ventas-body">
                                    <label class="perm-row"><input type="checkbox" name="permissions[ventas][clientes]"><span class="perm-check-icon"><i class="fas fa-check"></i></span><span>Clientes</span></label>
                                    <label class="perm-row"><input type="checkbox" name="permissions[ventas][cotizaciones]"><span class="perm-check-icon"><i class="fas fa-check"></i></span><span>Cotizaciones</span></label>
                                    <label class="perm-row"><input type="checkbox" name="permissions[ventas][oportunidades]"><span class="perm-check-icon"><i class="fas fa-check"></i></span><span>Oportunidades</span></label>
                                </div>
                            </div>

                            <div class="mod-card">
                                <div class="mod-card-head" onclick="toggleModule('rh-body')">
                                    <div class="mod-card-left">
                                        <span class="mod-icon mod--rrhh"><i class="fas fa-users"></i></span>
                                        <span class="mod-name">Recursos Humanos</span>
                                    </div>
                                    <label class="tog" onclick="event.stopPropagation()">
                                        <input type="checkbox" class="module-toggle" data-module="rh">
                                        <span class="tog-track"><span class="tog-thumb"></span></span>
                                    </label>
                                </div>
                                <div class="mod-card-body" id="rh-body">
                                    <label class="perm-row"><input type="checkbox" name="permissions[rh][altasempleados]"><span class="perm-check-icon"><i class="fas fa-check"></i></span><span>Altas de empleados</span></label>
                                    <label class="perm-row"><input type="checkbox" name="permissions[rh][loadchart]"><span class="perm-check-icon"><i class="fas fa-check"></i></span><span>L&amp;O Chart</span></label>
                                </div>
                            </div>

                            <div class="mod-card">
                                <div class="mod-card-head" onclick="toggleModule('sistemas-body')">
                                    <div class="mod-card-left">
                                        <span class="mod-icon mod--sistemas"><i class="fas fa-laptop-code"></i></span>
                                        <span class="mod-name">Sistemas</span>
                                    </div>
                                    <label class="tog" onclick="event.stopPropagation()">
                                        <input type="checkbox" class="module-toggle" data-module="sistemas">
                                        <span class="tog-track"><span class="tog-thumb"></span></span>
                                    </label>
                                </div>
                                <div class="mod-card-body" id="sistemas-body">
                                    <label class="perm-row"><input type="checkbox" name="permissions[sistemas][gestionderoles]"><span class="perm-check-icon"><i class="fas fa-check"></i></span><span>Gestión de roles</span></label>
                                    <label class="perm-row"><input type="checkbox" name="permissions[sistemas][tickets]"><span class="perm-check-icon"><i class="fas fa-check"></i></span><span>Gestión de tickets</span></label>
                                </div>
                            </div>

                            <div class="mod-card">
                                <div class="mod-card-head" onclick="toggleModule('suministro-body')">
                                    <div class="mod-card-left">
                                        <span class="mod-icon mod--suministro"><i class="fas fa-truck"></i></span>
                                        <span class="mod-name">Suministro</span>
                                    </div>
                                    <label class="tog" onclick="event.stopPropagation()">
                                        <input type="checkbox" class="module-toggle" data-module="suministro">
                                        <span class="tog-track"><span class="tog-thumb"></span></span>
                                    </label>
                                </div>
                                <div class="mod-card-body" id="suministro-body">
                                    <label class="perm-row"><input type="checkbox" name="permissions[suministro][pedidos]"><span class="perm-check-icon"><i class="fas fa-check"></i></span><span>Pedidos</span></label>
                                </div>
                            </div>

                            <div class="mod-card">
                                <div class="mod-card-head" onclick="toggleModule('operaciones-body')">
                                    <div class="mod-card-left">
                                        <span class="mod-icon mod--operaciones"><i class="fas fa-cog"></i></span>
                                        <span class="mod-name">Operaciones</span>
                                    </div>
                                    <label class="tog" onclick="event.stopPropagation()">
                                        <input type="checkbox" class="module-toggle" data-module="operaciones">
                                        <span class="tog-track"><span class="tog-thumb"></span></span>
                                    </label>
                                </div>
                                <div class="mod-card-body" id="operaciones-body">
                                    <label class="perm-row"><input type="checkbox" name="permissions[operaciones][procesos]"><span class="perm-check-icon"><i class="fas fa-check"></i></span><span>Procesos</span></label>
                                </div>
                            </div>

                            <div class="mod-card">
                                <div class="mod-card-head" onclick="toggleModule('almacen-body')">
                                    <div class="mod-card-left">
                                        <span class="mod-icon mod--almacen"><i class="fas fa-warehouse"></i></span>
                                        <span class="mod-name">Almacén</span>
                                    </div>
                                    <label class="tog" onclick="event.stopPropagation()">
                                        <input type="checkbox" class="module-toggle" data-module="almacen">
                                        <span class="tog-track"><span class="tog-thumb"></span></span>
                                    </label>
                                </div>
                                <div class="mod-card-body" id="almacen-body">
                                    <label class="perm-row"><input type="checkbox" name="permissions[almacen][inventario]"><span class="perm-check-icon"><i class="fas fa-check"></i></span><span>Inventario</span></label>
                                </div>
                            </div>

                            <div class="mod-card">
                                <div class="mod-card-head" onclick="toggleModule('geociencias-body')">
                                    <div class="mod-card-left">
                                        <span class="mod-icon mod--geo"><i class="fas fa-globe-americas"></i></span>
                                        <span class="mod-name">Geociencias</span>
                                    </div>
                                    <label class="tog" onclick="event.stopPropagation()">
                                        <input type="checkbox" class="module-toggle" data-module="geociencias">
                                        <span class="tog-track"><span class="tog-thumb"></span></span>
                                    </label>
                                </div>
                                <div class="mod-card-body" id="geociencias-body">
                                    <label class="perm-row"><input type="checkbox" name="permissions[geociencias][exploraciones]"><span class="perm-check-icon"><i class="fas fa-check"></i></span><span>Exploraciones</span></label>
                                    <label class="perm-row"><input type="checkbox" name="permissions[geociencias][analisis]"><span class="perm-check-icon"><i class="fas fa-check"></i></span><span>Análisis</span></label>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Tab Permisos directos -->
                    <div id="role-permissions" class="perm-tab-content">
                        <p class="tab-hint">Asigna permisos específicos directamente al usuario.</p>
                        <div class="direct-perms-grid" id="directPermissionsContainer">
                            <div class="loading-perms">
                                <div class="spin-ring"></div>
                                <span>Cargando permisos...</span>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeNewUserModal()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Guardar Usuario
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.12/sweetalert2.min.js"></script>
<script src="{{ asset('assets/js/sistemas/gestionderoles/index.js') }}"></script>
</body>
</html>
