// Variables globales
let users = [];
let currentPage = 1;
let itemsPerPage = 10;
let totalPages = 1;
let editingUserId = null; // Para saber si estamos editando
// **FIX: Definir la URL por defecto para el reseteo**
const DEFAULT_PHOTO_SRC = "{{ asset('assets/img/fotouser.png') }}";

// Función para mezclar array aleatoriamente (Fisher-Yates shuffle)
function shuffleArray(array) {
    const shuffled = [...array]; // Crear una copia del array
    for (let i = shuffled.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
    }
    return shuffled;
}

// Función para limpiar todos los checkboxes de permisos directos
function clearDirectPermissionsCheckboxes() {
    document.querySelectorAll('input[name="direct_permissions[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
}

// Función para cargar usuarios desde el backend
async function loadUsers() {
    try {
        const response = await fetch('/sistemas/gestionderoles/roles', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                    'content')
            }
        });

        if (!response.ok) {
            throw new Error('Error al cargar los usuarios');
        }

        const data = await response.json();
        users = data.users || [];
        totalPages = Math.ceil(users.length / itemsPerPage);
        renderUsers();
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error',
            text: 'No se pudieron cargar los usuarios',
            icon: 'error',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#3498db'
        });
    }
}

// Función para cargar roles desde el backend
async function loadRoles() {
    try {
        const response = await fetch('/sistemas/gestionderoles/get-roles', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                    'content')
            }
        });

        if (!response.ok) {
            throw new Error('Error al cargar los roles');
        }

        const data = await response.json();
        if (data.success && data.roles) {
            const roleSelect = document.getElementById('user_role');
            roleSelect.innerHTML = '<option value="" selected disabled>Seleccione un rol</option>';

            for (const [id, name] of Object.entries(data.roles)) {
                const option = document.createElement('option');
                option.value = id;
                option.textContent = name;
                roleSelect.appendChild(option);
            }
        }
    } catch (error) {
        console.error('Error al cargar roles:', error);
    }
}

// Agrega esta función para cargar los permisos
async function loadPermissions() {
    try {
        const response = await fetch('/sistemas/gestionderoles/get-permission', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                    'content')
            }
        });

        if (!response.ok) {
            throw new Error('Error al cargar los permisos');
        }

        const data = await response.json();
        if (data.success && data.permissions) {
            renderPermissions(data.permissions);
        }
    } catch (error) {
        console.error('Error al cargar permisos:', error);
        document.getElementById('directPermissionsContainer').innerHTML = `
            <div class="alert alert-danger">
                Error al cargar los permisos. Intente recargar la página.
            </div>
        `;
    }
}

// Función para renderizar los permisos
function renderPermissions(permissions) {
    const container = document.getElementById('directPermissionsContainer');
    container.innerHTML = '';

    if (permissions.length === 0) {
        container.innerHTML = '<p class="text-muted">No hay permisos disponibles</p>';
        return;
    }

    permissions.forEach(permission => {
        const permId = `direct_perm_${permission.id}`;
        const permElement = document.createElement('div');
        permElement.className = 'permission-checkbox';
        permElement.innerHTML = `
            <input type="checkbox" id="${permId}" name="direct_permissions[]"
                    value="${permission.id}" data-name="${permission.name}">
            <label for="${permId}" title="${permission.description || 'Sin descripción'}">
                ${permission.display_name}
            </label>
        `;
        container.appendChild(permElement);
    });
}

// Función para buscar empleados
async function searchEmployees(query) {
    try {
        const response = await fetch(`/sistemas/gestionderoles/search-employeesquery=${encodeURIComponent(query)}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                    'content')
            }
        });

        if (!response.ok) {
            throw new Error('Error en la búsqueda de empleados');
        }

        return await response.json();
    } catch (error) {
        console.error('Error:', error);
        return [];
    }
}

// Función para mostrar sugerencias
function showSuggestions(suggestions) {
    const dropdown = document.getElementById('employeeSuggestions');
    dropdown.innerHTML = '';

    if (suggestions.length === 0) {
        dropdown.style.display = 'none';
        return;
    }

    suggestions.forEach(employee => {
        const item = document.createElement('div');
        item.className = 'suggestion-item';
        item.textContent = employee.full_name;
        item.dataset.id = employee.id;

        item.addEventListener('click', () => {
            document.getElementById('name').value = employee.full_name;
            document.getElementById('employee_id').value = employee.id;
            dropdown.style.display = 'none';
        });

        dropdown.appendChild(item);
    });

    dropdown.style.display = 'block';
}

// Event listener para el input de búsqueda
document.getElementById('name').addEventListener('input', async function (e) {
    const query = e.target.value.trim();
    const dropdown = document.getElementById('employeeSuggestions');

    if (query.length < 2) {
        dropdown.style.display = 'none';
        document.getElementById('employee_id').value = '';
        return;
    }

    const employees = await searchEmployees(query);
    showSuggestions(employees);
});

// Manejar navegación con teclado
document.getElementById('name').addEventListener('keydown', function (e) {
    const dropdown = document.getElementById('employeeSuggestions');
    if (dropdown.style.display === 'none') return;

    const items = dropdown.querySelectorAll('.suggestion-item');
    if (items.length === 0) return;

    let currentIndex = -1;
    items.forEach((item, index) => {
        if (item.classList.contains('highlighted')) {
            currentIndex = index;
            item.classList.remove('highlighted');
        }
    });

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        const nextIndex = (currentIndex + 1) % items.length;
        items[nextIndex].classList.add('highlighted');
        items[nextIndex].scrollIntoView({
            block: 'nearest'
        });
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        const prevIndex = (currentIndex - 1 + items.length) % items.length;
        items[prevIndex].classList.add('highlighted');
        items[prevIndex].scrollIntoView({
            block: 'nearest'
        });
    } else if (e.key === 'Enter' && currentIndex >= 0) {
        e.preventDefault();
        items[currentIndex].click();
    }
});

// Ocultar dropdown al hacer clic fuera
document.addEventListener('click', function (e) {
    if (!e.target.closest('#name') && !e.target.closest('#employeeSuggestions')) {
        document.getElementById('employeeSuggestions').style.display = 'none';
    }
});

// Agregar estas funciones para manejar la foto
function setupPhotoUpload() {
    const photoInput = document.getElementById('photoInput');
    const photoDisplay = document.getElementById('photoDisplay');
    const photoPreview = document.getElementById('photoPreview');
    const removePhotoBtn = document.getElementById('removePhotoBtn');
    const photoHidden = document.getElementById('photo');


    photoInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (event) {
                photoDisplay.src = event.target.result;
                // Almacenar la base64 directamente en el campo oculto
                photoHidden.value = event.target.result;
                removePhotoBtn.style.display = 'inline-block';
                photoPreview.classList.add('has-photo');
            };
            reader.readAsDataURL(file);
        }
    });

    removePhotoBtn.addEventListener('click', function () {
        // **FIX** Usar la constante global para resetear la imagen visible
        photoDisplay.src = DEFAULT_PHOTO_SRC;
        // Dejar el valor oculto VACIÓ cuando se elimina
        photoHidden.value = '';
        removePhotoBtn.style.display = 'none';
        photoPreview.classList.remove('has-photo');
        photoInput.value = '';
    });
}

// Función para obtener clase CSS de módulo
function getModuleClass(module) {
    const classes = {
        "Administración": "administracion",
        "QHSE": "qhse",
        "Ventas": "ventas",
        "Recursos Humanos": "recursos-humanos",
        "Suministro": "suministro",
        "Operaciones": "operaciones",
        "sistemas": "sistemas",
        "Sistemas": "sistemas",
        "Almacen": "almacen",
        "Geociencias": "geociencias"
    };
    return classes[module] || "administracion";
}

// Función para formatear nombre de módulo
function formatModuleName(module) {
    const moduleNames = {
        "administracion": "Administración",
        "qhse": "QHSE",
        "ventas": "Ventas",
        "recursoshumanos": "Recursos Humanos",
        "suministro": "Suministro",
        "operaciones": "Operaciones",
        "sistemas": "Sistemas",
        "almacen": "Almacen",
        "geociencias": "Geociencias"
    };

    return moduleNames[module.toLowerCase()] ||
        module.charAt(0).toUpperCase() + module.slice(1).toLowerCase();
}

// Función para obtener solo los módulos (para mostrar en tabla) - AHORA CON ORDEN ALEATORIO
function getModulesFromPermissions(permissions) {
    if (!permissions || typeof permissions !== 'object') {
        return [];
    }

    const modules = Object.keys(permissions).map(module => formatModuleName(module));
    // Mezclar los módulos aleatoriamente antes de devolverlos
    return shuffleArray(modules);
}

// Función para formatear permisos detallados (para vista completa)
function formatDetailedPermissions(permissions) {
    if (!permissions || typeof permissions !== 'object') {
        return [];
    }

    const detailedPermissions = [];

    for (const [module, modulePermissions] of Object.entries(permissions)) {
        const moduleName = formatModuleName(module);

        if (Array.isArray(modulePermissions) && modulePermissions.length > 0) {
            // Si tiene permisos específicos, agregarlos
            modulePermissions.forEach(permission => {
                detailedPermissions.push({
                    module: moduleName,
                    permission: permission,
                    display: `${moduleName} - ${permission}`
                });
            });
        } else if (typeof modulePermissions === 'object' && Object.keys(modulePermissions).length > 0) {
            // Si es un objeto con permisos específicos
            Object.keys(modulePermissions).forEach(permission => {
                detailedPermissions.push({
                    module: moduleName,
                    permission: permission,
                    display: `${moduleName} - ${permission}`
                });
            });
        } else {
            // Si solo tiene acceso al módulo general
            detailedPermissions.push({
                module: moduleName,
                permission: 'No tiene permisos detallados',
                display: `${moduleName} - No tiene permisos detallados`
            });
        }
    }

    // También aleatorizar los permisos detallados
    return shuffleArray(detailedPermissions);
}

function renderUsers() {
    const tbody = document.getElementById('userTableBody');
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const currentUsers = users.slice(startIndex, endIndex);

    tbody.innerHTML = currentUsers.map(user => {
        // Los módulos ya vienen mezclados aleatoriamente de getModulesFromPermissions
        const modules = getModulesFromPermissions(user.permissions?.permissions || {});
        const visibleModules = modules.slice(0, 4);
        const hiddenCount = modules.length - visibleModules.length;

        return `
        <tr data-user-id="${user.id}">
            <td>
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <h4>${user.name || 'N/A'}</h4>
                        <p>${user.username || 'N/A'}</p>
                    </div>
                </div>
            </td>
            <td>${user.email || 'N/A'}</td>
            <td>
                <div class="permissions">
                    ${visibleModules.map(module =>
            `<span class="permission-tag ${getModuleClass(module)}">${module}</span>`
        ).join('')}
                    ${hiddenCount > 0 ? `<span class="permission-tag more-permissions">+${hiddenCount} más</span>` : ''}
                    ${modules.length === 0 ? '<span class="permission-tag">Sin permisos</span>' : ''}
                </div>
            </td>
            <td>
                <span class="status ${user.status || 'inactive'}">
                    <span class="status-dot"></span>
                    ${user.status === 'active' ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>
                <div class="actions">
                    <button class="action-btn view" title="Ver" onclick="viewUser(${user.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="action-btn edit" title="Editar" onclick="editUser(${user.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete" title="Eliminar" onclick="deleteUser(${user.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
        `;
    }).join('');

    updatePaginationInfo();
}

function updatePaginationInfo() {
    const startItem = users.length === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1;
    const endItem = Math.min(currentPage * itemsPerPage, users.length);

    document.getElementById('startItem').textContent = startItem;
    document.getElementById('endItem').textContent = endItem;
    document.getElementById('totalItems').textContent = users.length;
    document.getElementById('currentPageBtn').textContent = currentPage;

    // Actualizar botones de navegación
    document.getElementById('firstPage').disabled = currentPage === 1;
    document.getElementById('prevPage').disabled = currentPage === 1;
    document.getElementById('nextPage').disabled = currentPage === totalPages;
    document.getElementById('lastPage').disabled = currentPage === totalPages;
}

function viewUser(userId) {
    const user = users.find(u => u.id === userId);
    if (!user) return;

    const detailedPermissions = formatDetailedPermissions(user.permissions?.permissions || {});

    let permissionsHtml = '';
    if (detailedPermissions.length > 0) {
        // Agrupar permisos por módulo para mejor visualización
        const groupedPermissions = {};
        detailedPermissions.forEach(perm => {
            if (!groupedPermissions[perm.module]) {
                groupedPermissions[perm.module] = [];
            }
            groupedPermissions[perm.module].push(perm.permission);
        });

        // Aleatorizar también el orden de los módulos agrupados
        const moduleEntries = shuffleArray(Object.entries(groupedPermissions));

        permissionsHtml = moduleEntries.map(([module, permissions]) => `
            <div class="uvm-module-block">
                <div class="uvm-module-header">
                    <i class="uvm-module-icon fas fa-folder"></i>
                    <span class="uvm-module-name ${getModuleClass(module)}">${module}</span>
                    <span class="uvm-permissions-count">${permissions.length}</span>
                </div>
                <div class="uvm-module-permissions">
                    ${shuffleArray(permissions).map(permission =>
            `<span class="uvm-permission-item">
                                                             <i class="fas fa-check-circle"></i>
                                                             ${permission}
                                                         </span>`
        ).join('')}
                </div>
            </div>
        `).join('');
    } else {
        permissionsHtml = `
            <div class="uvm-no-permissions">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Sin permisos asignados</span>
            </div>
        `;
    }

    // Determinar el icono de estado
    const statusIcon = user.status === 'active' ? 'fas fa-check-circle' : 'fas fa-times-circle';
    const statusText = user.status === 'active' ? 'Activo' : 'Inactivo';

    Swal.fire({
        title: `<div class="uvm-title-section">
                        <i class="fas fa-user-circle uvm-user-avatar"></i>
                        <span class="uvm-user-name">${user.name}</span>
                    </div>`,
        html: `
        <div class="user-view-modal-content">
            <div class="uvm-info-grid">
                <div class="uvm-info-item">
                    <div class="uvm-info-label">
                        <i class="fas fa-user"></i>
                        Usuario
                    </div>
                    <div class="uvm-info-value">${user.username}</div>
                </div>

                <div class="uvm-info-item">
                    <div class="uvm-info-label">
                        <i class="fas fa-envelope"></i>
                        Email
                    </div>
                    <div class="uvm-info-value">${user.email}</div>
                </div>

                <div class="uvm-info-item">
                    <div class="uvm-info-label">
                        <i class="${statusIcon}"></i>
                        Estado
                    </div>
                    <div class="uvm-info-value">
                        <span class="uvm-status uvm-status-${user.status}">${statusText}</span>
                    </div>
                </div>

                <div class="uvm-info-item">
                    <div class="uvm-info-label">
                        <i class="fas fa-calendar-alt"></i>
                        Creado
                    </div>
                    <div class="uvm-info-value">${new Date(user.created_at).toLocaleString()}</div>
                </div>
            </div>

            <div class="uvm-permissions-section">
                <div class="uvm-section-header">
                    <i class="fas fa-shield-alt"></i>
                    <span>Permisos Detallados</span>
                </div>
                <div class="uvm-permissions-container">
                    ${permissionsHtml}
                </div>
            </div>
        </div>
        `,
        width: 1200,
        confirmButtonText: '<i class="fas fa-times"></i> Cerrar',
        confirmButtonColor: '#6c757d',
        customClass: {
            popup: 'user-view-modal-popup',
            title: 'user-view-modal-title',
            htmlContainer: 'user-view-modal-html',
            confirmButton: 'user-view-modal-button'
        },
        showClass: {
            popup: 'uvm-animate-in'
        },
        hideClass: {
            popup: 'uvm-animate-out'
        }
    });
}

function editUser(userId) {
    const user = users.find(u => u.id === userId);
    if (!user) return;
    editingUserId = userId;

    // **FIX: Limpiar los Permisos Directos del usuario anterior**
    clearDirectPermissionsCheckboxes();

    // Cambiar el título del modal
    document.querySelector('#newUserModal .modal-header h3').innerHTML =
        '<i class="fas fa-user-edit"></i>Editar Usuario';

    // Llenar los campos del formulario
    document.getElementById('name').value = user.name || '';
    document.getElementById('username').value = user.username || '';
    document.getElementById('email').value = user.email || '';
    document.getElementById('password').value = '';
    document.getElementById('password').removeAttribute('required');
    document.getElementById('password').placeholder = 'Dejar vacío para mantener contraseña actual';
    document.getElementById('status').value = user.status || 'inactive';
    document.getElementById('employee_id').value = user.employee_id || '';

    // Establecer el rol del usuario
    if (user.role_id) {
        document.getElementById('user_role').value = user.role_id;
    }

    // Configurar la foto
    const photoDisplay = document.getElementById('photoDisplay');
    const photoHidden = document.getElementById('photo');
    const removePhotoBtn = document.getElementById('removePhotoBtn');
    const photoPreview = document.getElementById('photoPreview');

    // Limpiar el input de file para evitar subidas accidentales
    document.getElementById('photoInput').value = '';

    if (user.employee_photo) {
        photoDisplay.src = "/" + user.employee_photo;
        // Guardar la ruta del archivo existente
        photoHidden.value = user.employee_photo;
        removePhotoBtn.style.display = 'inline-block';
        photoPreview.classList.add('has-photo');
    } else {
        // **FIX** Usar la constante global para resetear la imagen visible
        photoDisplay.src = DEFAULT_PHOTO_SRC;
        photoHidden.value = '';
        removePhotoBtn.style.display = 'none';
        photoPreview.classList.remove('has-photo');
    }

    // Cargar permisos directos del usuario
    if (user.direct_permissions && user.direct_permissions.length > 0) {
        user.direct_permissions.forEach(perm => {
            const checkbox = document.querySelector(
                `input[name="direct_permissions[]"][value="${perm.id}"]`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
    }

    // Limpiar permisos previos (Modulares)
    document.querySelectorAll('.module-toggle').forEach(toggle => {
        toggle.checked = false;
    });
    document.querySelectorAll('input[name^="permissions"]').forEach(input => {
        input.checked = false;
    });
    document.querySelectorAll('.module-body').forEach(body => {
        body.classList.remove('active');
    });

    // Cargar permisos modulares del usuario
    if (user.permissions && user.permissions.permissions) {
        const userPermissions = user.permissions.permissions;

        for (const [module, modulePermissions] of Object.entries(userPermissions)) {
            // Activar el toggle del módulo
            const moduleToggle = document.querySelector(`.module-toggle[data-module="${module}"]`);
            if (moduleToggle) {
                moduleToggle.checked = true;
                document.getElementById(`${module}-body`).classList.add('active');
            }

            // Activar permisos específicos
            if (Array.isArray(modulePermissions)) {
                modulePermissions.forEach(permission => {
                    const permissionInput = document.querySelector(
                        `input[name="permissions[${module}][${permission}]"]`);
                    if (permissionInput) {
                        permissionInput.checked = true;
                    }
                });
            } else if (typeof modulePermissions === 'object') {
                Object.keys(modulePermissions).forEach(permission => {
                    const permissionInput = document.querySelector(
                        `input[name="permissions[${module}][${permission}]"]`);
                    if (permissionInput) {
                        permissionInput.checked = true;
                    }
                });
            }
        }
    }

    // Mostrar el modal
    document.getElementById('newUserModal').classList.add('show');
}

async function deleteUser(userId) {
    const result = await Swal.fire({
        title: '¿Eliminar usuario?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef476f',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'rounded-4'
        }
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(`/sistemas/gestionderoles/roles/${userId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                        'content')
                }
            });

            const data = await response.json();

            if (data.success) {
                await Swal.fire({
                    title: '¡Eliminado!',
                    text: data.message || 'El usuario ha sido eliminado correctamente',
                    icon: 'success',
                    confirmButtonColor: '#4361ee',
                    customClass: {
                        popup: 'rounded-4'
                    }
                });

                // Recargar la lista de usuarios
                await loadUsers();
            } else {
                throw new Error(data.message || 'Error al eliminar el usuario');
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error',
                text: error.message || 'No se pudo eliminar el usuario',
                icon: 'error',
                confirmButtonColor: '#3498db'
            });
        }
    }
}

// Event listeners para paginación
document.addEventListener('DOMContentLoaded', function () {
    // Cargar usuarios al inicializar
    loadUsers();
    loadRoles();
    loadPermissions();
    setupPhotoUpload();

    // Event listeners para paginación
    document.getElementById('itemsPerPage').addEventListener('change', function () {
        itemsPerPage = parseInt(this.value);
        totalPages = Math.ceil(users.length / itemsPerPage);
        currentPage = 1;
        renderUsers();
    });

    document.getElementById('firstPage').addEventListener('click', function () {
        currentPage = 1;
        renderUsers();
    });

    document.getElementById('prevPage').addEventListener('click', function () {
        if (currentPage > 1) {
            currentPage--;
            renderUsers();
        }
    });

    document.getElementById('nextPage').addEventListener('click', function () {
        if (currentPage < totalPages) {
            currentPage++;
            renderUsers();
        }
    });

    document.getElementById('lastPage').addEventListener('click', function () {
        currentPage = totalPages;
        renderUsers();
    });

    // Funcionalidad de búsqueda mejorada
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function (e) {
            const searchTerm = e.target.value.toLowerCase();

            if (searchTerm === '') {
                currentPage = 1;
                renderUsers();
                return;
            }

            const filteredUsers = users.filter(user => {
                const name = (user.name || '').toLowerCase();
                const username = (user.username || '').toLowerCase();
                const email = (user.email || '').toLowerCase();
                const modules = getModulesFromPermissions(user.permissions?.permissions ||
                    {});
                const modulesText = modules.join(' ').toLowerCase();
                const status = user.status === 'active' ? 'activo' : 'inactivo';

                return name.includes(searchTerm) ||
                    username.includes(searchTerm) ||
                    email.includes(searchTerm) ||
                    modulesText.includes(searchTerm) ||
                    status.includes(searchTerm);
            });

            // Renderizar usuarios filtrados
            const tbody = document.getElementById('userTableBody');
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const currentUsers = filteredUsers.slice(startIndex, endIndex);

            tbody.innerHTML = currentUsers.map(user => {
                // Los módulos también se mezclan aleatoriamente en las búsquedas
                const modules = getModulesFromPermissions(user.permissions?.permissions ||
                    {});
                const visibleModules = modules.slice(0, 3);
                const hiddenCount = modules.length - visibleModules.length;

                return `
                <tr data-user-id="${user.id}">
                    <td>
                        <div class="user-info">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="user-details">
                                <h4>${user.name || 'N/A'}</h4>
                                <p>${user.username || 'N/A'}</p>
                            </div>
                        </div>
                    </td>
                    <td>${user.email || 'N/A'}</td>
                    <td>
                        <div class="permissions">
                            ${visibleModules.map(module =>
                    `<span class="permission-tag ${getModuleClass(module)}">${module}</span>`
                ).join('')}
                            ${hiddenCount > 0 ? `<span class="permission-tag more-permissions">+${hiddenCount} más</span>` : ''}
                            ${modules.length === 0 ? '<span class="permission-tag">Sin permisos</span>' : ''}
                        </div>
                    </td>
                    <td>
                        <span class="status ${user.status || 'inactive'}">
                            <span class="status-dot"></span>
                            ${user.status === 'active' ? 'Activo' : 'Inactivo'}
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <button class="action-btn view" title="Ver" onclick="viewUser(${user.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="action-btn edit" title="Editar" onclick="editUser(${user.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete" title="Eliminar" onclick="deleteUser(${user.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            }).join('');

            // Actualizar info de paginación para resultados filtrados
            const startItem = filteredUsers.length === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1;
            const endItem = Math.min(currentPage * itemsPerPage, filteredUsers.length);

            document.getElementById('startItem').textContent = startItem;
            document.getElementById('endItem').textContent = endItem;
            document.getElementById('totalItems').textContent = filteredUsers.length;
        });
    }

    // Animación de placeholder tipo máquina de escribir
    if (searchInput) {
        const phrases = ["Buscar por usuario...", "Buscar por email...", "Buscar por módulos...",
            "Buscar por estado..."
        ];
        let phraseIndex = 0;
        let charIndex = 0;
        let isDeleting = false;
        let isEnd = false;

        function typePlaceholder() {
            const currentPhrase = phrases[phraseIndex];

            if (isDeleting) {
                searchInput.placeholder = currentPhrase.substring(0, charIndex - 1);
                charIndex--;

                if (charIndex === 0) {
                    isDeleting = false;
                    phraseIndex = (phraseIndex + 1) % phrases.length;
                }
            } else {
                searchInput.placeholder = currentPhrase.substring(0, charIndex + 1);
                charIndex++;

                if (charIndex === currentPhrase.length) {
                    isEnd = true;
                    isDeleting = true;
                    setTimeout(typePlaceholder, 1500);
                    return;
                }
            }

            const typingSpeed = isDeleting ? 50 : 100;
            const randomSpeed = Math.random() * 50 + typingSpeed;
            setTimeout(typePlaceholder, isEnd ? randomSpeed : typingSpeed);
            isEnd = false;
        }

        setTimeout(typePlaceholder, 1000);
    }
});

// Funciones para modal
function openNewUserModal() {
    editingUserId = null;

    document.querySelector('#newUserModal .modal-header h3').innerHTML =
        '<i class="fas fa-user-plus"></i>Nuevo Usuario';

    document.getElementById('permissionsForm').reset();
    document.getElementById('password').placeholder = 'Ingrese una contraseña segura';
    document.getElementById('password').setAttribute('required', 'required'); // Requerir contraseña

    document.querySelectorAll('.module-toggle').forEach(toggle => {
        toggle.checked = false;
    });
    document.querySelectorAll('input[name^="permissions"]').forEach(input => {
        input.checked = false;
    });
    document.querySelectorAll('.module-body').forEach(body => {
        body.classList.remove('active');
    });

    // **FIX: Limpiar los Permisos Directos al abrir para Nuevo Usuario**
    clearDirectPermissionsCheckboxes();

    // **FIX: Resetear foto visible y valor oculto usando la constante global**
    document.getElementById('photoDisplay').src = DEFAULT_PHOTO_SRC;
    document.getElementById('photo').value = '';
    document.getElementById('removePhotoBtn').style.display = 'none';
    document.getElementById('photoPreview').classList.remove('has-photo');
    document.getElementById('photoInput').value = '';

    document.getElementById('newUserModal').classList.add('show');
}

function closeNewUserModal() {
    const modal = document.getElementById('newUserModal');
    if (modal) {
        modal.classList.remove('show');
        editingUserId = null;
        // Limpieza de permisos directos al cerrar
        clearDirectPermissionsCheckboxes();
        // **FIX: Resetear foto al cerrar**
        document.getElementById('photoDisplay').src = DEFAULT_PHOTO_SRC;
        document.getElementById('photo').value = '';
        document.getElementById('removePhotoBtn').style.display = 'none';
        document.getElementById('photoPreview').classList.remove('has-photo');
    }
}

function toggleModule(moduleBodyId) {
    const moduleBody = document.getElementById(moduleBodyId);
    if (moduleBody) {
        moduleBody.classList.toggle('active');
    }
}

// Manejar formulario de creación/edición
if (typeof $ !== 'undefined') {
    $(document).ready(function () {
        $('.module-toggle').on('change', function () {
            const module = $(this).data('module');
            const isChecked = $(this).prop('checked');

            if (isChecked) {
                $(`#${module}-body`).addClass('active');
            } else {
                $(`input[name^="permissions[${module}]"]`).prop('checked', false);
                $(`#${module}-body`).removeClass('active');
            }
        });

        $('input[name^="permissions"]').on('change', function () {
            const moduleKey = $(this).attr('name').split('[')[1].split(']')[0];
            const checkedModulePermissions = $(`input[name^="permissions[${moduleKey}]"]:checked`);

            if (checkedModulePermissions.length > 0) {
                $(`.module-toggle[data-module="${moduleKey}"]`).prop('checked', true);
                $(`#${moduleKey}-body`).addClass('active');
            }
        });

        $('#permissionsForm').on('submit', function (e) {
            e.preventDefault();

            let isValid = true;
            const requiredFields = ['name', 'username', 'email', 'status', 'user_role'];

            // Si estamos creando y no editando, la contraseña es requerida
            if (!editingUserId && !$('#password').val().trim()) {
                requiredFields.push('password');
            }

            let hasValidationErrors = false;
            $('#permissionsForm .form-control, #permissionsForm .form-select').removeClass('is-invalid');

            for (const field of requiredFields) {
                if (!$(`#${field}`).val() && field !== 'employee_id' && field !== 'password') {
                    $(`#${field}`).addClass('is-invalid');
                    hasValidationErrors = true;
                }
            }
            if (!editingUserId && !$('#password').val().trim()) {
                $('#password').addClass('is-invalid');
                hasValidationErrors = true;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if ($('#email').val().trim() && !emailRegex.test($('#email').val())) {
                $('#email').addClass('is-invalid');
                hasValidationErrors = true;
            }

            if (hasValidationErrors) {
                Swal.fire({
                    title: 'Error de validación',
                    text: 'Por favor, complete todos los campos requeridos correctamente',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#3498db'
                });
                return;
            }

            const formData = {
                name: $('#name').val(),
                username: $('#username').val(),
                email: $('#email').val(),
                status: $('#status').val(),
                employee_id: $('#employee_id').val(),
                role_id: $('#user_role').val(),
                permissions: {},
                // Asegurar que 'photo' se envíe (ruta o base64)
                photo: $('#photo').val(),

                direct_permissions: $('input[name="direct_permissions[]"]:checked').map(
                    function () {
                        return $(this).val();
                    }).get()
            };

            const passwordValue = $('#password').val();
            if (passwordValue.trim()) {
                formData.password = passwordValue;
            }

            // Recolección de permisos modulares
            $('.module-toggle:checked').each(function () {
                const module = $(this).data('module');
                formData.permissions[module] = {};

                $(`input[name^="permissions[${module}]"]:checked`).each(function () {
                    const nameAttr = $(this).attr('name');
                    const match = nameAttr.match(/permissions\[(.*?)\]\[(.*?)\]/);
                    if (match) {
                        const permission = match[2];
                        formData.permissions[module][permission] = true;
                    }
                });
            });

            Swal.fire({
                title: editingUserId ? 'Actualizando...' : 'Guardando...',
                text: 'Espere mientras se procesan los datos',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const url = editingUserId
                ? `/sistemas/gestionderoles/roles/${editingUserId}`
                : '/sistemas/gestionderoles/roles';
            const method = editingUserId ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                type: method,
                data: JSON.stringify(formData),
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    Swal.close();

                    if (response.success) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: response.message || (editingUserId ?
                                'Usuario actualizado correctamente' :
                                'Usuario creado correctamente'),
                            icon: 'success',
                            confirmButtonText: 'Aceptar',
                            confirmButtonColor: '#3498db'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                closeNewUserModal();
                                loadUsers();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.message ||
                                'Hubo un error al guardar los datos',
                            icon: 'error',
                            confirmButtonText: 'Aceptar',
                            confirmButtonColor: '#3498db'
                        });
                    }
                },
                error: function (xhr) {
                    Swal.close();
                    let errorMessage = 'Hubo un error al procesar la solicitud';

                    // Limpieza de errores visuales anteriores
                    $('#permissionsForm .form-control, #permissionsForm .form-select').removeClass('is-invalid');

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        let errorList = '<ul class="text-start">';

                        for (const field in errors) {
                            $(`#${field}`).addClass('is-invalid');
                            errorList += `<li>${errors[field][0]}</li>`;
                        }

                        errorList += '</ul>';

                        Swal.fire({
                            title: 'Error de validación',
                            html: errorList,
                            icon: 'error',
                            confirmButtonText: 'Aceptar',
                            confirmButtonColor: '#3498db'
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'Aceptar',
                            confirmButtonColor: '#3498db'
                        });
                    }
                }
            });
        });
    });
}

// Manejar cambio de pestañas
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();

        // Remover active de todos los botones y contenidos
        document.querySelectorAll('.tab-btn, .tab-content').forEach(el => {
            el.classList.remove('active');
        });

        // Activar la pestaña clickeada
        this.classList.add('active');
        const tabId = this.getAttribute('data-tab');
        document.getElementById(tabId).classList.add('active');
    });
});
