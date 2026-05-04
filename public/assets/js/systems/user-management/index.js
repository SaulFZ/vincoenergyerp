/* ══════════════════════════════════════════════
   GESTIÓN DE USUARIOS — JS v3
   ══════════════════════════════════════════════ */

let users        = [];
let currentPage  = 1;
let itemsPerPage = 10;
let totalPages   = 1;
let editingUserId = null;

const DEFAULT_PHOTO_SRC = "/assets/img/fotouser.png";

/* ── Ayudante de Rutas de Imágenes ── */
function getImageUrl(path) {
    if (!path) return DEFAULT_PHOTO_SRC;
    if (path.startsWith('data:') || path.startsWith('http')) return path;
    if (path.startsWith('assets/')) return `/${path}`;
    if (path.startsWith('rh/')) return `/storage/${path}`;
    return `/${path}`;
}

/* ── Shuffle ── */
function shuffleArray(arr) {
    const a = [...arr];
    for (let i = a.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [a[i], a[j]] = [a[j], a[i]];
    }
    return a;
}

/* ── Module maps ── */
const MODULE_NAMES = {
    administracion: 'Administración', qhse: 'QHSE', ventas: 'Ventas',
    rh: 'Recursos Humanos', suministro: 'Suministro',
    operaciones: 'Operaciones', sistemas: 'Sistemas', almacen: 'Almacén',
    geociencias: 'Geociencias',
};

const MODULE_CLASSES = {
    'Administración': 'administracion', 'QHSE': 'qhse', 'Ventas': 'ventas',
    'Recursos Humanos': 'recursos-humanos', 'Suministro': 'suministro',
    'Operaciones': 'operaciones', 'Sistemas': 'sistemas',
    'Almacén': 'almacen', 'Geociencias': 'geociencias',
};

const MODULE_ICONS = {
    'Administración': 'fa-cogs', 'QHSE': 'fa-shield-alt', 'Ventas': 'fa-chart-line',
    'Recursos Humanos': 'fa-users', 'Suministro': 'fa-truck',
    'Operaciones': 'fa-cog', 'Sistemas': 'fa-laptop-code',
    'Almacén': 'fa-warehouse', 'Geociencias': 'fa-globe-americas',
};

const MODULE_ICON_CLASSES = {
    'Administración': 'mod--admin', 'QHSE': 'mod--qhse', 'Ventas': 'mod--ventas',
    'Recursos Humanos': 'mod--rrhh', 'Suministro': 'mod--suministro',
    'Operaciones': 'mod--operaciones', 'Sistemas': 'mod--sistemas',
    'Almacén': 'mod--almacen', 'Geociencias': 'mod--geo',
};

function formatModuleName(key) {
    return MODULE_NAMES[key.toLowerCase()] || key.charAt(0).toUpperCase() + key.slice(1).toLowerCase();
}

function getModuleClass(name) {
    return MODULE_CLASSES[name] || 'administracion';
}

function getModulesFromPermissions(permissions) {
    if (!permissions || typeof permissions !== 'object') return [];
    return shuffleArray(Object.keys(permissions).map(formatModuleName));
}

function formatDetailedPermissions(permissions) {
    if (!permissions || typeof permissions !== 'object') return [];
    const result = [];
    for (const [module, perms] of Object.entries(permissions)) {
        const moduleName = formatModuleName(module);
        const keys = Array.isArray(perms) ? perms : (typeof perms === 'object' ? Object.keys(perms) : []);
        if (keys.length) {
            keys.forEach(p => result.push({ module: moduleName, permission: p }));
        } else {
            result.push({ module: moduleName, permission: 'Acceso general' });
        }
    }
    return shuffleArray(result);
}

/* ── Stats animados ── */
function updateStats() {
    const total    = users.length;
    const active   = users.filter(u => u.status === 'active').length;
    const inactive = total - active;
    animateNumber('statTotalNum', total);
    animateNumber('statActiveNum', active);
    animateNumber('statInactiveNum', inactive);
}

function animateNumber(id, target) {
    const el = document.getElementById(id);
    if (!el) return;
    const steps = 24, delay = 600 / steps;
    let step = 0;
    const t = setInterval(() => {
        step++;
        el.textContent = Math.round(target * (step / steps));
        if (step >= steps) { el.textContent = target; clearInterval(t); }
    }, delay);
}

/* ── API: load users ── */
async function loadUsers() {
    try {
        const res  = await fetch('/systems/user-management/users', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
        });
        if (!res.ok) throw new Error();
        const data = await res.json();
        users      = data.users || [];
        totalPages = Math.ceil(users.length / itemsPerPage) || 1;
        renderUsers();
        updateStats();
    } catch {
        Swal.fire({ title:'Error', text:'No se pudieron cargar los usuarios.', icon:'error', confirmButtonColor:'#DC143C' });
    }
}

/* ── API: load roles ── */
async function loadRoles() {
    try {
        const res  = await fetch('/systems/user-management/get-roles', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
        });
        if (!res.ok) throw new Error();
        const data = await res.json();
        if (data.success && data.roles) {
            const sel = document.getElementById('user_role');
            sel.innerHTML = '<option value="" disabled selected>Seleccionar rol</option>';
            for (const [id, name] of Object.entries(data.roles))
                sel.insertAdjacentHTML('beforeend', `<option value="${id}">${name}</option>`);
        }
    } catch { console.error('Error cargando roles'); }
}

/* ── API: load permissions ── */
async function loadPermissions() {
    try {
        const res  = await fetch('/systems/user-management/get-permissions', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
        });
        if (!res.ok) throw new Error();
        const data = await res.json();
        if (data.success && data.permissions) renderDirectPermissions(data.permissions);
        else throw new Error();
    } catch {
        document.getElementById('directPermissionsContainer').innerHTML =
            `<div style="grid-column:1/-1;text-align:center;color:rgba(15,23,42,.4);padding:2rem;font-size:.84rem;">Error al cargar permisos. Recarga la página.</div>`;
    }
}

function renderDirectPermissions(permissions) {
    const container = document.getElementById('directPermissionsContainer');
    container.innerHTML = '';
    if (!permissions.length) {
        container.innerHTML = '<p style="color:rgba(15,23,42,.38);font-size:.84rem;">Sin permisos disponibles.</p>';
        return;
    }
    permissions.forEach(perm => {
        const id  = `dp_${perm.id}`;
        const el  = document.createElement('label');
        el.className = 'perm-checkbox-item';
        el.htmlFor   = id;
        el.innerHTML = `
            <input type="checkbox" id="${id}" name="direct_permissions[]" value="${perm.id}" data-name="${perm.name}">
            <span class="perm-chk-box"><i class="fas fa-check"></i></span>
            <label for="${id}" title="${perm.description || ''}">${perm.display_name}</label>`;
        container.appendChild(el);
    });
}

function clearDirectPermissions() {
    document.querySelectorAll('input[name="direct_permissions[]"]').forEach(c => c.checked = false);
}

/* ── Search employees ── */
async function searchEmployees(query) {
    try {
        const res = await fetch(`/systems/user-management/search-employees?query=${encodeURIComponent(query)}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
        });
        if (!res.ok) throw new Error();
        return await res.json();
    } catch { return []; }
}

function showSuggestions(items) {
    const dd = document.getElementById('employeeSuggestions');
    dd.innerHTML = '';
    if (!items.length) { dd.style.display = 'none'; return; }
    items.forEach(emp => {
        const div = document.createElement('div');
        div.className   = 'suggestion-item';
        div.textContent = emp.full_name;
        div.dataset.id  = emp.id;
        div.addEventListener('click', () => {
            document.getElementById('name').value        = emp.full_name;
            document.getElementById('employee_id').value = emp.id;
            dd.style.display = 'none';
        });
        dd.appendChild(div);
    });
    dd.style.display = 'block';
}

document.getElementById('name').addEventListener('input', async function () {
    const q  = this.value.trim();
    const dd = document.getElementById('employeeSuggestions');
    if (q.length < 2) { dd.style.display = 'none'; document.getElementById('employee_id').value = ''; return; }
    showSuggestions(await searchEmployees(q));
});

document.getElementById('name').addEventListener('keydown', function (e) {
    const dd    = document.getElementById('employeeSuggestions');
    if (dd.style.display === 'none') return;
    const items = dd.querySelectorAll('.suggestion-item');
    if (!items.length) return;
    let cur = -1;
    items.forEach((it, i) => { if (it.classList.contains('highlighted')) { cur = i; it.classList.remove('highlighted'); } });
    if (e.key === 'ArrowDown')  { e.preventDefault(); const n=(cur+1)%items.length; items[n].classList.add('highlighted'); items[n].scrollIntoView({block:'nearest'}); }
    else if (e.key === 'ArrowUp')   { e.preventDefault(); const p=(cur-1+items.length)%items.length; items[p].classList.add('highlighted'); items[p].scrollIntoView({block:'nearest'}); }
    else if (e.key === 'Enter' && cur >= 0) { e.preventDefault(); items[cur].click(); }
});

document.addEventListener('click', e => {
    if (!e.target.closest('#name') && !e.target.closest('#employeeSuggestions'))
        document.getElementById('employeeSuggestions').style.display = 'none';
});

/* ── Password toggle ── */
function togglePassword() {
    const input = document.getElementById('password');
    const eye   = document.getElementById('pwdEye');
    if (input.type === 'password') { input.type = 'text'; eye.className = 'fas fa-eye-slash'; }
    else { input.type = 'password'; eye.className = 'fas fa-eye'; }
}

/* ── Photo upload ── */
function setupPhotoUpload() {
    const input   = document.getElementById('photoInput');
    const display = document.getElementById('photoDisplay');
    const preview = document.getElementById('photoPreview');
    const remove  = document.getElementById('removePhotoBtn');
    const hidden  = document.getElementById('photo');

    input.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        // Validación preventiva de peso máximo (ej: 2MB)
        if (file.size > 2 * 1024 * 1024) {
            Swal.fire('Foto muy pesada', 'La imagen es demasiado grande. Selecciona una menor a 2MB.', 'warning');
            this.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = e => {
            display.src    = e.target.result;
            hidden.value   = e.target.result;
            remove.style.display = 'inline-flex';
            preview.classList.add('has-photo');

            // INFALIBLE: Resetear el input para permitir reselección libre de la misma imagen
            input.value = '';
        };
        reader.readAsDataURL(file);
    });

    remove.addEventListener('click', () => {
        display.src        = DEFAULT_PHOTO_SRC;
        hidden.value       = '';
        remove.style.display = 'none';
        preview.classList.remove('has-photo');
        input.value = '';
    });
}

/* ── Render table ── */
function renderUsers(filteredList) {
    const list  = filteredList ?? users;
    const tbody = document.getElementById('userTableBody');
    const empty = document.getElementById('emptyState');
    const start = (currentPage - 1) * itemsPerPage;
    const slice = list.slice(start, start + itemsPerPage);

    if (!slice.length) {
        tbody.innerHTML = '';
        if (empty) empty.style.display = 'flex';
        updatePaginationInfo(list);
        return;
    }
    if (empty) empty.style.display = 'none';

    tbody.innerHTML = slice.map(user => {
        const mods    = getModulesFromPermissions(user.permissions?.permissions || {});
        const visible = mods.slice(0, 4);
        const hidden  = mods.length - visible.length;
        const sClass  = user.status === 'active' ? 'active' : 'inactive';
        const sText   = user.status === 'active' ? 'Activo'  : 'Inactivo';

        const avatarHtml = user.employee_photo
            ? `<img src="${getImageUrl(user.employee_photo)}" alt="${user.name}">`
            : `<i class="fas fa-user"></i>`;

        const tags = visible.map(m =>
            `<span class="ptag ptag-${getModuleClass(m)}">${m}</span>`
        ).join('') +
        (hidden ? `<span class="ptag ptag-more">+${hidden}</span>` : '') +
        (!mods.length ? `<span class="ptag ptag-none">Sin permisos</span>` : '');

        return `
        <tr data-id="${user.id}">
            <td>
                <div class="user-cell">
                    <div class="user-avatar">${avatarHtml}</div>
                    <div>
                        <div class="user-name">${user.name || 'N/A'}</div>
                        <div class="user-uname"><i class="fas fa-user"></i>${user.username || 'N/A'}</div>
                    </div>
                </div>
            </td>
            <td style="font-size:.84rem;color:rgba(15,23,42,.6);">${user.email || 'N/A'}</td>
            <td><div class="perm-tags">${tags}</div></td>
            <td><span class="status-badge ${sClass}"><span class="dot"></span>${sText}</span></td>
            <td>
                <div class="row-actions">
                    <button class="act-btn act-btn--view"   title="Ver"      onclick="viewUser(${user.id})"><i class="fas fa-eye"></i></button>
                    <button class="act-btn act-btn--edit"   title="Editar"   onclick="editUser(${user.id})"><i class="fas fa-pencil-alt"></i></button>
                    <button class="act-btn act-btn--delete" title="Eliminar" onclick="deleteUser(${user.id})"><i class="fas fa-trash"></i></button>
                </div>
            </td>
        </tr>`;
    }).join('');

    updatePaginationInfo(list);
}

function updatePaginationInfo(list) {
    const src    = list ?? users;
    const total  = src.length;
    const start  = total === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1;
    const end    = Math.min(currentPage * itemsPerPage, total);
    const tPages = Math.ceil(total / itemsPerPage) || 1;

    document.getElementById('startItem').textContent      = start;
    document.getElementById('endItem').textContent        = end;
    document.getElementById('totalItems').textContent     = total;
    document.getElementById('currentPageBtn').textContent = currentPage;

    document.getElementById('firstPage').disabled = currentPage === 1;
    document.getElementById('prevPage').disabled  = currentPage === 1;
    document.getElementById('nextPage').disabled  = currentPage >= tPages;
    document.getElementById('lastPage').disabled  = currentPage >= tPages;
}

/* ── VIEW USER ── */
function viewUser(userId) {
    const user = users.find(u => u.id === userId);
    if (!user) return;

    const perms   = formatDetailedPermissions(user.permissions?.permissions || {});
    const grouped = {};
    perms.forEach(p => { grouped[p.module] = grouped[p.module] || []; grouped[p.module].push(p.permission); });

    const totalPerms   = perms.length;
    const totalModules = Object.keys(grouped).length;

    const modulesHtml = totalModules
        ? shuffleArray(Object.entries(grouped)).map(([mod, ps]) => {
            const ico   = MODULE_ICONS[mod]        || 'fa-folder';
            const icoCls= MODULE_ICON_CLASSES[mod] || 'mod--admin';
            return `
            <div class="uvm-mod">
                <div class="uvm-mod-head">
                    <span class="uvm-mod-ico ${icoCls}"><i class="fas ${ico}"></i></span>
                    <span class="uvm-mod-name">${mod}</span>
                    <span class="uvm-mod-cnt">${ps.length}</span>
                </div>
                <div class="uvm-mod-body">
                    ${shuffleArray(ps).map(p =>
                        `<span class="uvm-perm-chip"><i class="fas fa-check-circle"></i>${p}</span>`
                    ).join('')}
                </div>
            </div>`;
        }).join('')
        : `<div class="uvm-no-perms"><i class="fas fa-exclamation-circle" style="margin-right:.5rem;color:rgba(15,23,42,.3);"></i>Sin permisos asignados</div>`;

    const sClass  = user.status === 'active' ? 'active'  : 'inactive';
    const sText   = user.status === 'active' ? 'Activo'  : 'Inactivo';
    const sIcon   = user.status === 'active' ? 'fa-check' : 'fa-times';
    const created = user.created_at ? new Date(user.created_at).toLocaleDateString('es-MX', { year:'numeric', month:'short', day:'numeric' }) : 'N/A';

    const avatarHtml = user.employee_photo
        ? `<img src="${getImageUrl(user.employee_photo)}" alt="${user.name}">`
        : `<i class="fas fa-user"></i>`;

    Swal.fire({
        html: `
        <div>
            <div class="uvm-hero">
                <div class="uvm-hero-inner">
                    <div class="uvm-avatar-wrap">
                        <div class="uvm-avatar">${avatarHtml}</div>
                        <span class="uvm-status-badge-hero ${sClass}">
                            <i class="fas ${sIcon}"></i>${sText}
                        </span>
                    </div>
                    <div class="uvm-hero-text">
                        <div class="uvm-hero-name">${user.name}</div>
                        <div class="uvm-hero-meta">
                            <span class="uvm-meta-chip mono">
                                <i class="fas fa-user"></i>${user.username}
                            </span>
                            <span class="uvm-meta-chip">
                                <i class="fas fa-envelope"></i>${user.email}
                            </span>
                        </div>
                        <div class="uvm-hero-stats">
                            <div class="uvm-hstat">
                                <span class="uvm-hstat-num">${totalModules}</span>
                                <span class="uvm-hstat-lbl">Módulos</span>
                            </div>
                            <div class="uvm-hstat-sep"></div>
                            <div class="uvm-hstat">
                                <span class="uvm-hstat-num">${totalPerms}</span>
                                <span class="uvm-hstat-lbl">Permisos</span>
                            </div>
                            <div class="uvm-hstat-sep"></div>
                            <div class="uvm-hstat">
                                <span class="uvm-hstat-num">${created}</span>
                                <span class="uvm-hstat-lbl">Creado</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="uvm-body">
                <div class="uvm-info-grid">
                    <div class="uvm-info-card">
                        <div class="uvm-info-ico"><i class="fas fa-user"></i></div>
                        <div>
                            <span class="uvm-info-label">Usuario</span>
                            <div class="uvm-info-val" style="font-family:var(--font-mono);font-size:.8rem;">${user.username}</div>
                        </div>
                    </div>
                    <div class="uvm-info-card">
                        <div class="uvm-info-ico"><i class="fas fa-envelope"></i></div>
                        <div>
                            <span class="uvm-info-label">Email</span>
                            <div class="uvm-info-val" style="font-size:.8rem;">${user.email}</div>
                        </div>
                    </div>
                    <div class="uvm-info-card">
                        <div class="uvm-info-ico"><i class="fas fa-toggle-on"></i></div>
                        <div>
                            <span class="uvm-info-label">Estado</span>
                            <div class="uvm-info-val">
                                <span class="status-badge ${sClass}" style="display:inline-flex;">
                                    <span class="dot"></span>${sText}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="uvm-info-card">
                        <div class="uvm-info-ico"><i class="fas fa-calendar-alt"></i></div>
                        <div>
                            <span class="uvm-info-label">Creado</span>
                            <div class="uvm-info-val">${created}</div>
                        </div>
                    </div>
                </div>

                <div class="uvm-perms-header">
                    <div class="uvm-perms-title">
                        <i class="fas fa-shield-alt"></i>
                        Permisos por módulo
                    </div>
                    <span class="uvm-cnt-pill">${totalPerms} permisos</span>
                </div>
                <div class="uvm-modules-list">${modulesHtml}</div>
            </div>
        </div>`,
        showConfirmButton: true,
        confirmButtonText: '<i class="fas fa-times" style="margin-right:.4rem;"></i> Cerrar',
        customClass: {
            popup:         'swal-view-popup',
            confirmButton: 'uvm-confirm-btn',
            htmlContainer: 'swal2-html-no-pad',
        },
        width: 760,
    });
}

/* ── EDIT USER ── */
function editUser(userId) {
    const user = users.find(u => u.id === userId);
    if (!user) return;
    editingUserId = userId;

    clearDirectPermissions();
    resetModules();

    document.getElementById('modalIconHeader').className = 'fas fa-user-edit';
    document.getElementById('modalTitle').textContent    = 'Editar Usuario';

    document.getElementById('name').value        = user.name        || '';
    document.getElementById('username').value    = user.username    || '';
    document.getElementById('email').value       = user.email       || '';
    document.getElementById('password').value    = '';
    document.getElementById('password').removeAttribute('required');
    document.getElementById('password').placeholder = 'Dejar vacío para mantener';
    document.getElementById('status').value      = user.status      || 'inactive';
    document.getElementById('employee_id').value = user.employee_id || '';
    if (user.role_id) document.getElementById('user_role').value = user.role_id;

    // Foto
    const pd = document.getElementById('photoDisplay');
    const ph = document.getElementById('photo');
    const rb = document.getElementById('removePhotoBtn');
    const pp = document.getElementById('photoPreview');
    document.getElementById('photoInput').value = '';

    if (user.employee_photo) {
        pd.src = getImageUrl(user.employee_photo);
        ph.value = user.employee_photo;
        rb.style.display = 'inline-flex';
        pp.classList.add('has-photo');
    } else {
        pd.src = DEFAULT_PHOTO_SRC;
        ph.value = '';
        rb.style.display = 'none';
        pp.classList.remove('has-photo');
    }

    // Permisos directos
    if (user.direct_permissions?.length) {
        user.direct_permissions.forEach(p => {
            const cb = document.querySelector(`input[name="direct_permissions[]"][value="${p.id}"]`);
            if (cb) cb.checked = true;
        });
    }

    // Permisos modulares
    if (user.permissions?.permissions) {
        for (const [mod, perms] of Object.entries(user.permissions.permissions)) {
            const tog  = document.querySelector(`.module-toggle[data-module="${mod}"]`);
            const body = document.getElementById(`${mod}-body`);
            if (tog)  tog.checked = true;
            if (body) body.classList.add('active');
            const keys = Array.isArray(perms) ? perms : (typeof perms === 'object' ? Object.keys(perms) : []);
            keys.forEach(p => {
                const cb = document.querySelector(`input[name="permissions[${mod}][${p}]"]`);
                if (cb) cb.checked = true;
            });
        }
    }

    document.getElementById('newUserModal').classList.add('show');
}

/* ── DELETE USER ── */
async function deleteUser(userId) {
    const confirm = await Swal.fire({
        title:'¿Eliminar usuario?', text:'Esta acción no se puede deshacer.',
        icon:'warning', showCancelButton:true,
        confirmButtonColor:'#DC143C', cancelButtonColor:'rgba(15,23,42,.4)',
        confirmButtonText:'Sí, eliminar', cancelButtonText:'Cancelar',
    });
    if (!confirm.isConfirmed) return;

    try {
        const res  = await fetch(`/systems/user-management/users/${userId}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
        });
        const data = await res.json();
        if (data.success) {
            await Swal.fire({ title:'¡Eliminado!', text: data.message || 'Usuario eliminado.', icon:'success', confirmButtonColor:'#DC143C' });
            await loadUsers();
        } else { throw new Error(data.message || 'Error'); }
    } catch (err) {
        Swal.fire({ title:'Error', text: err.message || 'No se pudo eliminar.', icon:'error', confirmButtonColor:'#DC143C' });
    }
}

/* ── MODAL open/close ── */
function openNewUserModal() {
    editingUserId = null;
    document.getElementById('modalIconHeader').className = 'fas fa-user-plus';
    document.getElementById('modalTitle').textContent    = 'Nuevo Usuario';
    document.getElementById('permissionsForm').reset();
    document.getElementById('password').placeholder = '••••••••';
    document.getElementById('password').setAttribute('required', 'required');
    resetModules();
    clearDirectPermissions();
    resetPhoto();
    document.getElementById('newUserModal').classList.add('show');
}

function closeNewUserModal() {
    document.getElementById('newUserModal').classList.remove('show');
    editingUserId = null;
    clearDirectPermissions();
    resetPhoto();
}

function resetPhoto() {
    document.getElementById('photoDisplay').src          = DEFAULT_PHOTO_SRC;
    document.getElementById('photo').value               = '';
    document.getElementById('removePhotoBtn').style.display = 'none';
    document.getElementById('photoPreview').classList.remove('has-photo');
    document.getElementById('photoInput').value          = '';
}

function resetModules() {
    document.querySelectorAll('.module-toggle').forEach(t  => t.checked = false);
    document.querySelectorAll('input[name^="permissions"]').forEach(c => c.checked = false);
    document.querySelectorAll('.mod-card-body').forEach(b  => b.classList.remove('active'));
}

function toggleModule(bodyId) {
    document.getElementById(bodyId)?.classList.toggle('active');
}

/* ── Tab switching ── */
document.querySelectorAll('.perm-tab').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.perm-tab, .perm-tab-content').forEach(el => el.classList.remove('active'));
        this.classList.add('active');
        document.getElementById(this.dataset.tab)?.classList.add('active');
    });
});

/* ── CSRF helper ── */
function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }

/* ── DOMContentLoaded ── */
document.addEventListener('DOMContentLoaded', () => {
    loadUsers();
    loadRoles();
    loadPermissions();
    setupPhotoUpload();

    document.getElementById('itemsPerPage').addEventListener('change', function () {
        itemsPerPage = parseInt(this.value);
        totalPages   = Math.ceil(users.length / itemsPerPage) || 1;
        currentPage  = 1;
        renderUsers();
    });

    ['firstPage','prevPage','nextPage','lastPage'].forEach(id => {
        document.getElementById(id).addEventListener('click', () => {
            if      (id === 'firstPage') currentPage = 1;
            else if (id === 'prevPage')  currentPage = Math.max(1, currentPage - 1);
            else if (id === 'nextPage')  currentPage = Math.min(totalPages, currentPage + 1);
            else if (id === 'lastPage')  currentPage = totalPages;
            renderUsers();
        });
    });

    // Live search
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            if (!q) { currentPage = 1; totalPages = Math.ceil(users.length / itemsPerPage) || 1; renderUsers(); return; }
            const filtered = users.filter(u => {
                const mods = getModulesFromPermissions(u.permissions?.permissions || {});
                return [u.name, u.username, u.email, mods.join(' '), u.status === 'active' ? 'activo' : 'inactivo']
                    .some(v => (v || '').toLowerCase().includes(q));
            });
            currentPage = 1;
            totalPages  = Math.ceil(filtered.length / itemsPerPage) || 1;
            renderUsers(filtered);
        });

        // Typewriter placeholder
        const phrases = ['Buscar por nombre...','Buscar por email...','Buscar por módulo...','Buscar por estado...'];
        let pi = 0, ci = 0, del = false, pausing = false;
        function type() {
            if (pausing) return;
            const phrase = phrases[pi];
            if (!del) {
                searchInput.placeholder = phrase.slice(0, ci + 1); ci++;
                if (ci === phrase.length) { pausing = true; setTimeout(() => { pausing = false; del = true; type(); }, 1600); return; }
            } else {
                searchInput.placeholder = phrase.slice(0, ci - 1); ci--;
                if (ci === 0) { del = false; pi = (pi + 1) % phrases.length; }
            }
            setTimeout(type, del ? 40 : 90);
        }
        setTimeout(type, 800);
    }
});

/* ── jQuery: toggles + submit ── */
if (typeof $ !== 'undefined') {
    $(document).ready(function () {

        $(document).on('change', '.module-toggle', function () {
            const mod     = $(this).data('module');
            const checked = $(this).prop('checked');
            if (checked) {
                $(`#${mod}-body`).addClass('active');
            } else {
                $(`#${mod}-body`).removeClass('active');
                $(`input[name^="permissions[${mod}]"]`).prop('checked', false);
            }
        });

        $(document).on('change', 'input[name^="permissions"]', function () {
            const mod = $(this).attr('name').split('[')[1].split(']')[0];
            if ($(`input[name^="permissions[${mod}]"]:checked`).length > 0) {
                $(`.module-toggle[data-module="${mod}"]`).prop('checked', true);
                $(`#${mod}-body`).addClass('active');
            }
        });

        $('#permissionsForm').on('submit', function (e) {
            e.preventDefault();

            const required = ['name','username','email','status','user_role'];
            let hasErrors  = false;
            $('#permissionsForm .field-input').removeClass('is-invalid');

            required.forEach(f => { if (!$(`#${f}`).val()) { $(`#${f}`).addClass('is-invalid'); hasErrors = true; } });

            if (!editingUserId && !$('#password').val().trim()) {
                $('#password').addClass('is-invalid'); hasErrors = true;
            }

            if ($('#email').val() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test($('#email').val())) {
                $('#email').addClass('is-invalid'); hasErrors = true;
            }

            // Alerta extra si se intenta enviar foto sin un employee_id ligado
            if ($('#photo').val() && !$('#employee_id').val() && !$('#photo').val().startsWith('rh/')) {
                Swal.fire({
                    title: 'Advertencia',
                    text: 'Estás subiendo una foto pero el usuario no está correctamente vinculado a un registro de empleado. Búscalo en la lista desplegable.',
                    icon: 'warning'
                });
                return;
            }

            if (hasErrors) {
                Swal.fire({ title:'Campos incompletos', text:'Completa todos los campos requeridos.', icon:'error', confirmButtonColor:'#DC143C' });
                return;
            }

            const formData = {
                name:               $('#name').val(),
                username:           $('#username').val(),
                email:              $('#email').val(),
                status:             $('#status').val(),
                employee_id:        $('#employee_id').val(),
                role_id:            $('#user_role').val(),
                photo:              $('#photo').val(),
                permissions:        {},
                direct_permissions: $('input[name="direct_permissions[]"]:checked').map(function(){ return $(this).val(); }).get(),
            };

            const pwd = $('#password').val();
            if (pwd.trim()) formData.password = pwd;

            $('.module-toggle:checked').each(function () {
                const mod = $(this).data('module');
                formData.permissions[mod] = {};
                $(`input[name^="permissions[${mod}]"]:checked`).each(function () {
                    const match = $(this).attr('name').match(/permissions\[.*?\]\[(.*?)\]/);
                    if (match) formData.permissions[mod][match[1]] = true;
                });
            });

            const btn     = document.querySelector('.btn-save');
            const origTxt = btn.innerHTML;
            btn.innerHTML = '<div class="spin-ring" style="width:14px;height:14px;border-width:2px;border-top-color:#fff;"></div>&nbsp;Guardando...';
            btn.disabled  = true;

            const url    = editingUserId ? `/systems/user-management/users/${editingUserId}` : '/systems/user-management/users';
            const method = editingUserId ? 'PUT' : 'POST';

            $.ajax({
                url, type: method,
                data: JSON.stringify(formData),
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (res) {
                    btn.innerHTML = origTxt; btn.disabled = false;
                    if (res.success) {
                        Swal.fire({
                            title:'¡Guardado!',
                            text: res.message || (editingUserId ? 'Usuario actualizado.' : 'Usuario creado.'),
                            icon:'success', confirmButtonColor:'#DC143C',
                        }).then(r => { if (r.isConfirmed) { closeNewUserModal(); loadUsers(); } });
                    } else {
                        Swal.fire({ title:'Error', text: res.message || 'Error al guardar.', icon:'error', confirmButtonColor:'#DC143C' });
                    }
                },
                error: function (xhr) {
                    console.error("AJAX Error:", xhr); // <-- Para ver si el servidor cortó la conexión por peso
                    btn.innerHTML = origTxt; btn.disabled = false;
                    if (xhr.status === 413) {
                        Swal.fire({ title:'Fallo del servidor', text:'La imagen es demasiado grande y el servidor rechazó la solicitud.', icon:'error', confirmButtonColor:'#DC143C' });
                    } else if (xhr.responseJSON?.errors) {
                        const errs = xhr.responseJSON.errors;
                        let list = '<ul style="text-align:left;margin:.5rem 0 0;">';
                        for (const f in errs) { $(`#${f}`).addClass('is-invalid'); list += `<li>${errs[f][0]}</li>`; }
                        list += '</ul>';
                        Swal.fire({ title:'Error de validación', html:list, icon:'error', confirmButtonColor:'#DC143C' });
                    } else {
                        Swal.fire({ title:'Error', text: xhr.responseJSON?.message || 'Error en la solicitud HTTP.', icon:'error', confirmButtonColor:'#DC143C' });
                    }
                },
            });
        });
    });
}
