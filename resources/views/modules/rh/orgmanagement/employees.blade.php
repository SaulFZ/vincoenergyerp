@extends('modules.rh.orgmanagement.index')

@section('content')
<div class="employees-page">

    {{-- =========================================================
             TOOLBAR: título, búsqueda, filtros y botón de alta
        ========================================================== --}}
    <div class="page-toolbar">
        <div class="toolbar-heading">
            <h1>Altas de Empleados</h1>
            <p>Consulta el personal registrado o da de alta un nuevo empleado.</p>
        </div>

        <div class="toolbar-actions">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar por nombre o número de empleado...">
            </div>

            <select id="filterArea" class="filter-select">
                <option value="">Todas las áreas</option>
            </select>

            <select id="filterStatus" class="filter-select">
                <option value="">Todos los estados</option>
                <option value="active">Activo</option>
                <option value="inactive">Inactivo</option>
            </select>

            <button type="button" class="btn btn-primary" id="btnNuevaAlta">
                <i class="fas fa-user-plus"></i> Nueva Alta
            </button>
        </div>
    </div>

    {{-- =========================================================
             TABLA DE EMPLEADOS
        ========================================================== --}}
    <div class="table-card">
        <div class="table-wrapper">
            <table class="employees-table" id="employeesTable">
                <thead>
                    <tr>
                        <th class="col-photo"></th>
                        <th>Empleado</th>
                        <th>Puesto</th>
                        <th>Área</th>
                        <th>Estado</th>
                        <th>Fecha de ingreso</th>
                        <th class="col-actions">Acciones</th>
                    </tr>
                </thead>
                <tbody id="employeesTableBody">
                    {{-- Spinner de carga inicial y filas inyectadas por JS --}}
                </tbody>
            </table>

            <div class="empty-state" id="emptyState" hidden>
                <i class="fas fa-users-slash"></i>
                <h3>No se encontraron empleados</h3>
                <p>Ajusta la búsqueda o los filtros, o da de alta un nuevo empleado.</p>
            </div>
        </div>

        <div class="table-footer">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span id="resultsSummary">Mostrando 0 de 0 empleados</span>
                <select id="perPageSelect" class="filter-select"
                    style="padding: 4px 8px; font-size: 12px; border-radius: 6px;">
                    <option value="6" selected>6 por página</option>
                    <option value="12">12 por página</option>
                    <option value="999999">Todos</option>
                </select>
            </div>
            <div class="pagination" id="pagination"></div>
        </div>
    </div>
</div>

{{-- =========================================================
         MODAL: VER DETALLES DEL EMPLEADO (DISEÑO MEJORADO)
    ========================================================== --}}
<div class="modal-overlay" id="viewModalOverlay" hidden>
    <div class="modal-view" role="dialog" aria-modal="true" aria-labelledby="viewModalTitle">
        <div class="modal-header">
            <h2 id="viewModalTitle"><i class="fas fa-user-circle"></i> Ficha del Empleado</h2>
            <button type="button" class="btn-icon" id="btnCloseViewModal" aria-label="Cerrar">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body" style="background: var(--surface-alt);">
            <div class="view-content" id="viewContent">
                {{-- Tarjetas generadas dinámicamente --}}
            </div>
        </div>

        <div class="modal-footer">
            <div class="footer-spacer"></div>
            <button type="button" class="btn btn-secondary" id="btnCloseViewBtn">Cerrar</button>
            <button type="button" class="btn btn-primary" id="btnEditEmployee">
                <i class="fas fa-pen"></i> Editar Datos
            </button>
        </div>
    </div>
</div>

{{-- =========================================================
         MODAL: FORMULARIO DE ALTA/EDICIÓN (WIZARD DE PASOS)
    ========================================================== --}}
<div class="modal-overlay" id="altaModalOverlay" hidden>
    <div class="modal-alta" role="dialog" aria-modal="true" aria-labelledby="altaModalTitle">
        <div class="modal-header">
            <h2 id="altaModalTitle"><i class="fas fa-user-plus"></i> Nueva Alta de Empleado</h2>
            <button type="button" class="btn-icon" id="btnCloseModal" aria-label="Cerrar">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        <div class="steps-indicator" id="stepsIndicator">
            <div class="step-item active" data-step="1"><span class="step-circle">1</span><span
                    class="step-label">Identificación</span></div>
            <div class="step-item" data-step="2"><span class="step-circle">2</span><span class="step-label">Datos
                    personales</span></div>
            <div class="step-item" data-step="3"><span class="step-circle">3</span><span class="step-label">Puesto /
                    Estructura</span></div>
            <div class="step-item" data-step="4"><span class="step-circle">4</span><span class="step-label">Contacto /
                    Legal</span></div>
            <div class="step-item" data-step="5"><span class="step-circle">5</span><span
                    class="step-label">Confirmar</span></div>
        </div>

        <form id="formAlta" class="form-alta" novalidate>
            {{-- PASO 1: IDENTIFICACIÓN Y FOTO --}}
            <section class="form-step active" data-step="1">
                <h3 class="step-title" style="text-align: center;">Identificación y foto</h3>
                <div class="photo-upload">
                    <div class="photo-preview" id="photoPreview"><i class="fas fa-user"></i></div>
                    <div class="photo-actions">
                        <label for="photoInput" class="btn btn-secondary btn-sm"><i class="fas fa-camera"></i> Subir
                            foto</label>
                        <input type="file" id="photoInput" name="photo" accept="image/*" hidden>
                        <button type="button" class="btn btn-ghost btn-sm" id="btnRemovePhoto" hidden>Quitar
                            foto</button>
                    </div>
                </div>
                <div class="field-group">
                    <label for="employeeNumber">Número de empleado <span class="req">*</span></label>
                    <input type="text" id="employeeNumber" name="employee_number" placeholder="Ej. VIN-001" required>
                </div>
            </section>

            {{-- PASO 2: DATOS PERSONALES --}}
            <section class="form-step" data-step="2">
                <h3 class="step-title">Datos personales</h3>
                <div class="field-row">
                    <div class="field-group">
                        <label for="firstName">Primer nombre <span class="req">*</span></label>
                        <input type="text" id="firstName" name="first_name" placeholder="Ej. Juan" required>
                    </div>
                    <div class="field-group">
                        <label for="secondName">Segundo nombre</label>
                        <input type="text" id="secondName" name="second_name" placeholder="Ej. Carlos">
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label for="firstSurname">Primer apellido <span class="req">*</span></label>
                        <input type="text" id="firstSurname" name="first_surname" placeholder="Ej. Pérez" required>
                    </div>
                    <div class="field-group">
                        <label for="secondSurname">Segundo apellido</label>
                        <input type="text" id="secondSurname" name="second_surname" placeholder="Ej. López">
                    </div>
                </div>
                <div class="field-group">
                    <label for="fullName">Nombre completo</label>
                    <input type="text" id="fullName" name="full_name" placeholder="Se genera automáticamente" disabled>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label for="gender">Género <span class="req">*</span></label>
                        <select id="gender" name="gender" required>
                            <option value="">Selecciona...</option>
                            <option value="F">Femenino</option>
                            <option value="M">Masculino</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label for="birthDate">Fecha de nacimiento <span class="req">*</span></label>
                        <input type="text" id="birthDate" name="birth_date" placeholder="dd/mm/aaaa" required>
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label for="nationality">Nacionalidad <span class="req">*</span></label>
                        <select id="nationality" name="nationality" required>
                            <option value="">Selecciona...</option>
                        </select>
                        <p class="field-hint">El texto se ajusta según el género.</p>
                    </div>
                    <div class="field-group">
                        <label for="secondNationality">Segunda nacionalidad (Opcional)</label>
                        <select id="secondNationality" name="second_nationality">
                            <option value="">Ninguna</option>
                        </select>
                    </div>
                </div>
            </section>

            {{-- PASO 3: PUESTO Y ESTRUCTURA --}}
            <section class="form-step" data-step="3">
                <h3 class="step-title">Datos laborales y estructura</h3>
                <div class="field-row">
                    <div class="field-group">
                        <label for="position">Puesto <span class="req">*</span></label>
                        <input type="text" id="position" name="position" placeholder="Ej. Desarrollador Frontend"
                            required>
                    </div>
                    <div class="field-group">
                        <label for="jobTitle">Título del puesto</label>
                        <input type="text" id="jobTitle" name="job_title" placeholder="Ej. Ing. en Sistemas">
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label for="hireDate">Fecha de ingreso <span class="req">*</span></label>
                        <input type="text" id="hireDate" name="hire_date" placeholder="dd/mm/aaaa" required>
                    </div>
                    <div class="field-group">
                        <label for="employmentStatus">Estado <span class="req">*</span></label>
                        <select id="employmentStatus" name="employment_status" required>
                            <option value="active" selected>Activo</option>
                            <option value="inactive">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label for="area">Área <span class="req">*</span></label>
                        <select id="area" name="area_id" required>
                            <option value="">Selecciona un área...</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label for="department">Departamento</label>
                        <select id="department" name="department_id" disabled>
                            <option value="">Selecciona un área primero...</option>
                        </select>
                    </div>
                </div>
                <div class="field-group">
                    <label for="manager">Jefe directo</label>
                    <input type="text" id="managerSearch" placeholder="Buscar empleado por nombre..."
                        autocomplete="off">
                    <input type="hidden" id="manager" name="manager_id">
                    <div class="autocomplete-list" id="managerList" hidden></div>
                </div>
            </section>

            {{-- PASO 4: CONTACTO Y LEGAL/SALUD --}}
            <section class="form-step" data-step="4">
                <h3 class="step-title">Contacto, documentación y salud</h3>
                <div class="field-row">
                    <div class="field-group">
                        <label for="phone">Teléfono</label>
                        <input type="tel" id="phone" name="phone" placeholder="Ej. 55 1234 5678">
                    </div>
                    <div class="field-group">
                        <label for="personalEmail">Correo personal</label>
                        <input type="email" id="personalEmail" name="personal_email"
                            placeholder="Ej. juan.perez@correo.com">
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label for="rfc">RFC</label>
                        <input type="text" id="rfc" name="rfc" maxlength="13" placeholder="Ej. XAXX010101000">
                    </div>
                    <div class="field-group">
                        <label for="curp">CURP</label>
                        <input type="text" id="curp" name="unique_population_code" maxlength="18"
                            placeholder="Ej. ABC12345678901234">
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label for="nss">NSS</label>
                        <input type="text" id="nss" name="social_security_number" maxlength="11"
                            placeholder="Ej. 12345678901">
                    </div>
                    <div class="field-group">
                        <label for="bloodType">Tipo de sangre</label>
                        <select id="bloodType" name="blood_type">
                            <option value="">Selecciona...</option>
                            <option>O+</option>
                            <option>O-</option>
                            <option>A+</option>
                            <option>A-</option>
                            <option>B+</option>
                            <option>B-</option>
                            <option>AB+</option>
                            <option>AB-</option>
                        </select>
                    </div>
                </div>
                <div class="field-group">
                    <label for="medicalHistory">Antecedentes médicos relevantes</label>
                    <textarea id="medicalHistory" name="medical_history" rows="3"
                        placeholder="Alergias, padecimientos crónicos, etc. (opcional)"></textarea>
                </div>
            </section>

            {{-- PASO 5: CONFIRMACIÓN --}}
            <section class="form-step" data-step="5">
                <h3 class="step-title">Confirma los datos antes de guardar</h3>
                <div class="summary-grid" id="summaryGrid"></div>
            </section>
        </form>

        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" id="btnPrevStep" hidden><i class="fas fa-arrow-left"></i>
                Atrás</button>
            <div class="footer-spacer"></div>
            <button type="button" class="btn btn-secondary" id="btnCancelAlta">Cancelar</button>
            <button type="button" class="btn btn-primary" id="btnNextStep">Siguiente <i
                    class="fas fa-arrow-right"></i></button>
            <button type="button" class="btn btn-primary" id="btnSubmitAlta" hidden><i class="fas fa-check"></i> Guardar
                empleado</button>
        </div>
    </div>
</div>


<script>
    /* ==========================================================================
           Altas de Empleados — Lógica real conectada al backend de Vinco ERP.
        ========================================================================== */
    (function() {
        'use strict';
        /* ---- VARIABLES GLOBALES Y ESTADO ---- */
        const DEFAULT_PHOTO_SRC = '/assets/img/default-avatar.png';
        let employees = [];
        let areas = [];
        let managers = [];
        let currentEditingId = null;
        const NATIONALITIES = [{
                code: 'MX',
                m: 'Mexicano',
                f: 'Mexicana'
            },
            {
                code: 'VE',
                m: 'Venezolano',
                f: 'Venezolana'
            },
            {
                code: 'CO',
                m: 'Colombiano',
                f: 'Colombiana'
            },
            {
                code: 'AR',
                m: 'Argentino',
                f: 'Argentina'
            },
            {
                code: 'CL',
                m: 'Chileno',
                f: 'Chilena'
            },
            {
                code: 'GT',
                m: 'Guatemalteco',
                f: 'Guatemalteca'
            },
            {
                code: 'SV',
                m: 'Salvadoreño',
                f: 'Salvadoreña'
            },
            {
                code: 'DO',
                m: 'Dominicano',
                f: 'Dominicana'
            },
            {
                code: 'ES',
                m: 'Español',
                f: 'Española'
            },
            {
                code: 'US',
                m: 'Estadounidense',
                f: 'Estadounidense'
            },
        ];
        const STATUS_LABEL = {
            active: 'Activo',
            inactive: 'Inactivo'
        };
        const STATUS_BADGE_CLASS = {
            active: 'badge-active',
            inactive: 'badge-inactive'
        };
        const state = {
            search: '',
            filterArea: '',
            filterStatus: '',
            page: 1,
            perPage: 6, // 6 registros por página iniciales
            currentStep: 1,
            totalSteps: 5,
        };
        /* ---- HELPERS ---- */
        function $(id) {
            return document.getElementById(id);
        }

        function getInitials(name) {
            if (!name) return '';
            const parts = name.trim().split(/\s+/);
            return ((parts[0] ? . [0] || '') + (parts[1] ? . [0] || '')).toUpperCase();
        }

        function formatDate(isoDate) {
            if (!isoDate) return '—';
            const [y, m, d] = isoDate.split('-');
            return `${d}/${m}/${y}`;
        }

        function getImageUrl(path) {
            if (!path) return DEFAULT_PHOTO_SRC;
            let cleanPath = path.replace('/storage/', '').replace('storage/', '');
            if (cleanPath.startsWith('data:') || cleanPath.startsWith('http')) return cleanPath;
            if (cleanPath.startsWith('assets/')) return `/${cleanPath}`;
            if (cleanPath.startsWith('rh/')) return `/systems/user-management/photo/${cleanPath}`;
            return `/${cleanPath}`;
        }

        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }
        /* ---- PETICIONES AL BACKEND ---- */
        async function loadInitialData() {
            try {
                showTableLoading();
                const resCreate = await fetch('/rh/orgmanagement/employees/create-data');
                const dataCreate = await resCreate.json();
                areas = dataCreate.areas || [];
                managers = dataCreate.managers || [];
                renderFilterOptions();
                renderAreaOptions();
                await loadEmployees();
            } catch (error) {
                console.error('Error cargando datos:', error);
                Swal.fire('Error', 'No se pudieron cargar los datos del servidor.', 'error');
            }
        }
        async function loadEmployees() {
            try {
                showTableLoading();
                const res = await fetch('/rh/orgmanagement/employees/data');
                employees = await res.json();
                renderTable();
            } catch (error) {
                console.error('Error cargando empleados:', error);
                hideTableLoading(); // Por si falla, quita el loader
            }
        }
        /* ---- TABLA: LOADER SPINNER ---- */
        function showTableLoading() {
            const tbody = $('employeesTableBody');
            $('emptyState').hidden = true;
            // Inyectamos el diseño del loader directamente en el tbody
            tbody.innerHTML = `
                    <tr>
                        <td colspan="7">
                            <div class="loader-container">
                                <div class="spinner"></div>
                                <span>Cargando información del personal...</span>
                            </div>
                        </td>
                    </tr>
                `;
        }

        function hideTableLoading() {
            // Al renderizar la tabla, se sobreescribe el innerHTML del tbody automáticamente
        }
        /* ---- RENDERIZADO TABLA Y FILTROS ---- */
        function renderFilterOptions() {
            const areaSelect = $('filterArea');
            areaSelect.innerHTML = '<option value="">Todas las áreas</option>';
            areas.forEach(area => {
                const opt = document.createElement('option');
                opt.value = area.id;
                opt.textContent = area.name;
                areaSelect.appendChild(opt);
            });
        }

        function getFilteredEmployees() {
            return employees.filter(emp => {
                const matchesSearch = state.search === '' ||
                    emp.full_name ? .toLowerCase().includes(state.search) ||
                    emp.employee_number ? .toLowerCase().includes(state.search);
                const matchesArea = state.filterArea === '' || emp.area_id === Number(state.filterArea);
                const matchesStatus = state.filterStatus === '' || emp.employment_status === state
                    .filterStatus;
                return matchesSearch && matchesArea && matchesStatus;
            });
        }

        function renderTable() {
            const filtered = getFilteredEmployees();
            const totalPages = Math.max(1, Math.ceil(filtered.length / state.perPage));
            state.page = Math.min(state.page, totalPages);
            const start = (state.page - 1) * state.perPage;
            const pageItems = filtered.slice(start, start + state.perPage);
            const tbody = $('employeesTableBody');
            const emptyState = $('emptyState');
            tbody.innerHTML = '';
            if (pageItems.length === 0) {
                emptyState.hidden = false;
            } else {
                emptyState.hidden = true;
                // Pasamos el índice (i) para la animación en cascada
                pageItems.forEach((emp, i) => tbody.appendChild(buildRow(emp, i)));
            }
            $('resultsSummary').textContent = `Mostrando ${pageItems.length} de ${filtered.length} empleados`;
            renderPagination(totalPages);
        }

        function buildRow(emp, index) {
            const tr = document.createElement('tr');
            // Clase para la animación CSS y retraso dinámico
            tr.className = 'row-animate';
            tr.style.animationDelay = `${index * 0.04}s`;
            const photoHtml = emp.photo ?
                `<img src="${getImageUrl(emp.photo)}" alt="${emp.full_name}">` :
                getInitials(emp.full_name);
            const areaName = emp.area ? emp.area.name : '—';
            tr.innerHTML = `
                    <td class="col-photo"><div class="avatar">${photoHtml}</div></td>
                    <td>
                        <div class="cell-employee">
                            <div>
                                <strong>${emp.full_name}</strong>
                                <span>${emp.employee_number}</span>
                            </div>
                        </div>
                    </td>
                    <td>${emp.position}</td>
                    <td>${areaName}</td>
                    <td><span class="badge ${STATUS_BADGE_CLASS[emp.employment_status]}">${STATUS_LABEL[emp.employment_status]}</span></td>
                    <td>${formatDate(emp.hire_date)}</td>
                    <td>
                        <div class="row-actions">
                            <button type="button" class="btn-icon view-btn" title="Ver detalle"><i class="fas fa-eye"></i></button>
                            <button type="button" class="btn-icon edit-btn" title="Editar"><i class="fas fa-pen"></i></button>
                        </div>
                    </td>
                `;
            tr.querySelector('.view-btn').addEventListener('click', () => viewEmployee(emp.id));
            tr.querySelector('.edit-btn').addEventListener('click', () => editEmployee(emp.id));
            return tr;
        }

        function renderPagination(totalPages) {
            const container = $('pagination');
            container.innerHTML = '';
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                btn.type = 'button';
                if (i === state.page) btn.classList.add('active');
                btn.addEventListener('click', () => {
                    state.page = i;
                    renderTable();
                });
                container.appendChild(btn);
            }
        }
        /* ---- MODAL: VER DETALLES (Diseño Tarjetas) ---- */
        function viewEmployee(id) {
            const emp = employees.find(e => e.id === id);
            if (!emp) return;
            const photoHtml = emp.photo ?
                `<img src="${getImageUrl(emp.photo)}" alt="${emp.full_name}">` :
                `<div class="avatar" style="width: 120px; height: 120px; font-size: 45px; margin: 0 auto; border: 4px solid var(--teal-light);">${getInitials(emp.full_name)}</div>`;
            const areaName = emp.area ? emp.area.name : '—';
            const deptName = emp.department ? emp.department.name : '—';
            const managerName = emp.manager ? emp.manager.full_name : '—';
            const genderLabel = {
                M: 'Masculino',
                F: 'Femenino'
            } [emp.gender] || '—';
            const statusLabel = STATUS_LABEL[emp.employment_status] || '—';
            // Generación del HTML con el nuevo layout de tarjetas (grid)
            const html = `
                    <div class="view-profile-header">
                        ${emp.photo ? `<img src="${getImageUrl(emp.photo)}" alt="${emp.full_name}">` : photoHtml}
                        <h3>${emp.full_name}</h3>
                        <p>${emp.employee_number} &nbsp;•&nbsp; <span class="badge ${STATUS_BADGE_CLASS[emp.employment_status]}">${statusLabel}</span></p>
                    </div>

                    <div class="view-grid">
                        <div class="view-card">
                            <div class="view-card-title"><i class="fas fa-id-card"></i> Información Personal</div>
                            <div class="view-info-group"><div class="view-label">Género</div><div class="view-value">${genderLabel}</div></div>
                            <div class="view-info-group"><div class="view-label">Fecha Nacimiento</div><div class="view-value">${formatDate(emp.birth_date)}</div></div>
                            <div class="view-info-group"><div class="view-label">Nacionalidad(es)</div><div class="view-value">${emp.nationality || '—'}</div></div>
                        </div>

                        <div class="view-card">
                            <div class="view-card-title"><i class="fas fa-briefcase"></i> Datos Laborales</div>
                            <div class="view-info-group"><div class="view-label">Puesto</div><div class="view-value">${emp.position}</div></div>
                            <div class="view-info-group"><div class="view-label">Área / Departamento</div><div class="view-value">${areaName} / ${deptName}</div></div>
                            <div class="view-info-group"><div class="view-label">Jefe Directo</div><div class="view-value">${managerName}</div></div>
                            <div class="view-info-group"><div class="view-label">Fecha de Ingreso</div><div class="view-value">${formatDate(emp.hire_date)}</div></div>
                        </div>

                        <div class="view-card">
                            <div class="view-card-title"><i class="fas fa-address-book"></i> Contacto</div>
                            <div class="view-info-group"><div class="view-label">Teléfono</div><div class="view-value">${emp.phone || '—'}</div></div>
                            <div class="view-info-group"><div class="view-label">Correo Electrónico</div><div class="view-value">${emp.personal_email || '—'}</div></div>
                        </div>

                        <div class="view-card">
                            <div class="view-card-title"><i class="fas fa-file-contract"></i> Legal e Identidad</div>
                            <div class="view-info-group"><div class="view-label">RFC</div><div class="view-value">${emp.rfc || '—'}</div></div>
                            <div class="view-info-group"><div class="view-label">CURP</div><div class="view-value">${emp.unique_population_code || '—'}</div></div>
                            <div class="view-info-group"><div class="view-label">NSS</div><div class="view-value">${emp.social_security_number || '—'}</div></div>
                            <div class="view-info-group"><div class="view-label">Tipo de Sangre</div><div class="view-value">${emp.blood_type || '—'}</div></div>
                        </div>

                        <div class="view-card" style="grid-column: 1 / -1;">
                            <div class="view-card-title"><i class="fas fa-notes-medical"></i> Antecedentes Médicos Relevantes</div>
                            <div class="view-value" style="font-size: 13.5px; line-height: 1.5;">${emp.medical_history || 'Sin registros médicos capturados en el sistema.'}</div>
                        </div>
                    </div>
                `;
            $('viewContent').innerHTML = html;
            $('btnEditEmployee').onclick = () => {
                closeViewModal();
                editEmployee(id);
            };
            $('viewModalOverlay').hidden = false;
        }

        function closeViewModal() {
            $('viewModalOverlay').hidden = true;
        }
        /* ---- MODAL: EDITAR EMPLEADO ---- */
        function editEmployee(id) {
            const emp = employees.find(e => e.id === id);
            if (!emp) return;
            currentEditingId = id;
            $('employeeNumber').value = emp.employee_number;
            $('firstName').value = emp.first_name || '';
            $('secondName').value = emp.second_name || '';
            $('firstSurname').value = emp.first_surname || '';
            $('secondSurname').value = emp.second_surname || '';
            $('fullName').value = emp.full_name || '';
            $('gender').value = emp.gender || '';
            renderNationalityOptions(emp.gender);
            if (emp.nationality) {
                const nats = emp.nationality.split(', ');
                $('nationality').value = nats[0] || '';
                $('secondNationality').value = nats[1] || '';
            } else {
                $('nationality').value = '';
                $('secondNationality').value = '';
            }
            if (emp.birth_date && typeof $('birthDate')._flatpickr !== 'undefined') {
                $('birthDate')._flatpickr.setDate(formatDate(emp.birth_date), true, "d/m/Y");
            }
            $('position').value = emp.position || '';
            $('jobTitle').value = emp.job_title || '';
            if (emp.hire_date && typeof $('hireDate')._flatpickr !== 'undefined') {
                $('hireDate')._flatpickr.setDate(formatDate(emp.hire_date), true, "d/m/Y");
            }
            $('employmentStatus').value = emp.employment_status || 'active';
            $('area').value = emp.area_id || '';
            const deptSelect = $('department');
            if (emp.area_id) {
                const areaObj = areas.find(a => a.id === emp.area_id);
                if (areaObj && areaObj.departments) {
                    deptSelect.innerHTML = '<option value="">Selecciona...</option>';
                    areaObj.departments.forEach(dept => {
                        const opt = document.createElement('option');
                        opt.value = dept.id;
                        opt.textContent = dept.name;
                        deptSelect.appendChild(opt);
                    });
                    deptSelect.disabled = false;
                    deptSelect.value = emp.department_id || '';
                }
            } else {
                deptSelect.innerHTML = '<option value="">Selecciona un área primero...</option>';
                deptSelect.disabled = true;
            }
            $('managerSearch').value = emp.manager ? emp.manager.full_name : '';
            $('manager').value = emp.manager_id || '';
            $('phone').value = emp.phone || '';
            $('personalEmail').value = emp.personal_email || '';
            $('rfc').value = emp.rfc || '';
            $('curp').value = emp.unique_population_code || '';
            $('nss').value = emp.social_security_number || '';
            $('bloodType').value = emp.blood_type || '';
            $('medicalHistory').value = emp.medical_history || '';
            $('employeeNumber').disabled = true;
            if (emp.photo) {
                const img = document.createElement('img');
                img.src = getImageUrl(emp.photo);
                $('photoPreview').innerHTML = '';
                $('photoPreview').appendChild(img);
                $('btnRemovePhoto').hidden = false;
            } else {
                $('photoPreview').innerHTML = '<i class="fas fa-user"></i>';
                $('btnRemovePhoto').hidden = true;
            }
            $('altaModalTitle').innerHTML = '<i class="fas fa-pen"></i> Editar Empleado';
            $('btnSubmitAlta').innerHTML = '<i class="fas fa-save"></i> Actualizar empleado';
            goToStep(1);
            $('altaModalOverlay').hidden = false;
        }
        /* ---- MODAL ALTA NUEVA Y NAVEGACIÓN ---- */
        function openModal() {
            currentEditingId = null;
            $('employeeNumber').disabled = false;
            $('altaModalTitle').innerHTML = '<i class="fas fa-user-plus"></i> Nueva Alta de Empleado';
            $('btnSubmitAlta').innerHTML = '<i class="fas fa-check"></i> Guardar empleado';
            resetForm();
            goToStep(1);
            $('altaModalOverlay').hidden = false;
        }

        function closeModal() {
            $('altaModalOverlay').hidden = true;
            resetForm();
            currentEditingId = null;
        }

        function confirmClose() {
            Swal.fire({
                title: currentEditingId ? '¿Cancelar edición?' : '¿Cancelar el alta?',
                text: 'Se perderá la información capturada.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'Seguir capturando',
                confirmButtonColor: '#35506b',
                cancelButtonColor: '#64748b',
            }).then(result => {
                if (result.isConfirmed) closeModal();
            });
        }

        function goToStep(step) {
            state.currentStep = step;
            document.querySelectorAll('.form-step').forEach(section => {
                section.classList.toggle('active', Number(section.dataset.step) === step);
            });
            document.querySelectorAll('.step-item').forEach(item => {
                const itemStep = Number(item.dataset.step);
                item.classList.toggle('active', itemStep === step);
                item.classList.toggle('completed', itemStep < step);
            });
            $('btnPrevStep').hidden = step === 1;
            $('btnNextStep').hidden = step === state.totalSteps;
            $('btnSubmitAlta').hidden = step !== state.totalSteps;
            if (step === state.totalSteps) buildSummary();
        }

        function validateStep(step) {
            const section = document.querySelector(`.form-step[data-step="${step}"]`);
            let valid = true;
            section.querySelectorAll('[required]').forEach(field => {
                const group = field.closest('.field-group');
                if (!field.value || !field.value.trim()) {
                    valid = false;
                    group ? .classList.add('invalid');
                } else {
                    group ? .classList.remove('invalid');
                }
            });
            if (!valid) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Faltan datos obligatorios',
                    text: 'Revisa los campos marcados en rojo antes de continuar.',
                    confirmButtonColor: '#35506b',
                });
            }
            return valid;
        }
        /* ---- LÓGICA DEL FORMULARIO ---- */
        function updateFullName() {
            const parts = ['firstName', 'secondName', 'firstSurname', 'secondSurname']
                .map(id => $(id).value.trim()).filter(Boolean);
            $('fullName').value = parts.join(' ');
        }

        function renderNationalityOptions(genderValue) {
            const select1 = $('nationality');
            const select2 = $('secondNationality');
            const val1 = select1.value;
            const val2 = select2 ? select2.value : '';
            select1.innerHTML = '<option value="">Selecciona...</option>';
            if (select2) select2.innerHTML = '<option value="">Ninguna</option>';
            NATIONALITIES.forEach(nat => {
                const text = (genderValue === 'M') ? nat.m : (genderValue === 'F' ? nat.f :
                    `${nat.m} / ${nat.f}`);
                const opt1 = document.createElement('option');
                opt1.value = text;
                opt1.textContent = text;
                select1.appendChild(opt1);
                if (select2) {
                    const opt2 = document.createElement('option');
                    opt2.value = text;
                    opt2.textContent = text;
                    select2.appendChild(opt2);
                }
            });
            select1.value = val1;
            if (select2) select2.value = val2;
        }

        function renderAreaOptions() {
            const select = $('area');
            select.innerHTML = '<option value="">Selecciona un área...</option>';
            areas.forEach(area => {
                const opt = document.createElement('option');
                opt.value = area.id;
                opt.textContent = area.name;
                select.appendChild(opt);
            });
        }

        function bindAreaToDepartment() {
            $('area').addEventListener('change', (e) => {
                const deptSelect = $('department');
                const area = areas.find(a => a.id === Number(e.target.value));
                if (!area || !area.departments || area.departments.length === 0) {
                    deptSelect.innerHTML = '<option value="">No hay departamentos</option>';
                    deptSelect.disabled = true;
                    return;
                }
                deptSelect.disabled = false;
                deptSelect.innerHTML = '<option value="">Selecciona...</option>';
                area.departments.forEach(dept => {
                    const opt = document.createElement('option');
                    opt.value = dept.id;
                    opt.textContent = dept.name;
                    deptSelect.appendChild(opt);
                });
            });
        }

        function bindManagerAutocomplete() {
            const input = $('managerSearch');
            const list = $('managerList');
            input.addEventListener('input', () => {
                const term = input.value.trim().toLowerCase();
                list.innerHTML = '';
                if (term.length < 2) {
                    list.hidden = true;
                    return;
                }
                const matches = managers.filter(emp => emp.full_name.toLowerCase().includes(term)).slice(0,
                    6);
                if (matches.length === 0) {
                    list.hidden = true;
                    return;
                }
                matches.forEach(emp => {
                    const item = document.createElement('div');
                    item.textContent = `${emp.full_name} — ${emp.position || 'Sin puesto'}`;
                    item.addEventListener('click', () => {
                        input.value = emp.full_name;
                        $('manager').value = emp.id;
                        list.hidden = true;
                    });
                    list.appendChild(item);
                });
                list.hidden = false;
            });
            document.addEventListener('click', (e) => {
                if (e.target !== input) list.hidden = true;
            });
        }

        function bindPhotoUpload() {
            const input = $('photoInput');
            const preview = $('photoPreview');
            const removeBtn = $('btnRemovePhoto');
            input.addEventListener('change', () => {
                const file = input.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    removeBtn.hidden = false;
                };
                reader.readAsDataURL(file);
            });
            removeBtn.addEventListener('click', () => {
                input.value = '';
                preview.innerHTML = '<i class="fas fa-user"></i>';
                removeBtn.hidden = true;
            });
        }

        function initDatePickers() {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#birthDate', {
                    dateFormat: 'd/m/Y',
                    maxDate: 'today',
                    locale: {
                        firstDayOfWeek: 1
                    }
                });
                flatpickr('#hireDate', {
                    dateFormat: 'd/m/Y',
                    maxDate: 'today',
                    locale: {
                        firstDayOfWeek: 1
                    }
                });
            }
        }
        /* ---- RESUMEN Y GUARDADO ---- */
        function buildSummary() {
            const genderLabel = {
                M: 'Masculino',
                F: 'Femenino'
            } [$('gender').value] || '—';
            const nat1 = $('nationality').value || '—';
            const nat2Select = $('secondNationality');
            const nat2 = nat2Select ? nat2Select.value : '';
            const combinedNat = nat2 ? `${nat1}, ${nat2}` : nat1;
            const areaOpt = $('area').selectedOptions[0];
            const deptOpt = $('department').selectedOptions[0];
            const items = [
                ['No. de empleado', $('employeeNumber').value || '—'],
                ['Nombre completo', $('fullName').value || '—'],
                ['Género', genderLabel],
                ['Nacionalidad(es)', combinedNat],
                ['Fecha Nac.', $('birthDate').value || '—'],
                ['Puesto', $('position').value || '—'],
                ['Área', areaOpt ? .textContent || '—'],
                ['Departamento', deptOpt ? .textContent || '—'],
                ['Ingreso', $('hireDate').value || '—'],
                ['Estado', STATUS_LABEL[$('employmentStatus').value] || '—'],
                ['Jefe directo', $('managerSearch').value || 'Sin asignar'],
                ['Contacto', ($('phone').value || '—') + ' / ' + ($('personalEmail').value || '—')],
            ];
            $('summaryGrid').innerHTML = items.map(([label, value]) => `
                    <div class="summary-item"><span>${label}</span><strong>${value}</strong></div>
                `).join('');
        }
        async function submitAlta() {
            $('btnSubmitAlta').disabled = true;
            const form = $('formAlta');
            const formData = new FormData(form);
            if (currentEditingId) {
                formData.append('_method', 'PUT');
            }
            try {
                const url = currentEditingId ?
                    `/rh/orgmanagement/employees/${currentEditingId}` :
                    '/rh/orgmanagement/employees';
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const result = await response.json();
                if (!response.ok) {
                    let errorMsg = result.message || 'Ocurrió un error al guardar.';
                    if (result.errors) {
                        errorMsg = Object.values(result.errors).flat().join('<br>');
                    }
                    throw new Error(errorMsg);
                }
                Swal.fire({
                    icon: 'success',
                    title: currentEditingId ? 'Empleado actualizado' : 'Empleado registrado',
                    text: currentEditingId ? 'Los cambios se guardaron correctamente.' :
                        'El empleado fue dado de alta correctamente.',
                    confirmButtonColor: '#35506b',
                });
                closeModal();
                await loadEmployees(); // Recarga la tabla de inmediato
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: error.message,
                    confirmButtonColor: '#35506b',
                });
            } finally {
                $('btnSubmitAlta').disabled = false;
            }
        }

        function resetForm() {
            $('formAlta').reset();
            $('fullName').value = '';
            $('photoPreview').innerHTML = '<i class="fas fa-user"></i>';
            $('btnRemovePhoto').hidden = true;
            $('department').innerHTML = '<option value="">Selecciona un área primero...</option>';
            $('department').disabled = true;
            $('manager').value = '';
            $('managerSearch').value = '';
            if ($('secondNationality')) $('secondNationality').innerHTML = '<option value="">Ninguna</option>';
            renderNationalityOptions('');
            document.querySelectorAll('.field-group.invalid').forEach(g => g.classList.remove('invalid'));
            $('photoInput').value = '';
            if ($('birthDate')._flatpickr) $('birthDate')._flatpickr.clear();
            if ($('hireDate')._flatpickr) $('hireDate')._flatpickr.clear();
            goToStep(1);
        }
        /* ---- INICIALIZACIÓN Y EVENTOS ---- */
        function bindEvents() {
            // Filtros y Paginación
            $('searchInput').addEventListener('input', (e) => {
                state.search = e.target.value.toLowerCase();
                state.page = 1;
                renderTable();
            });
            $('filterArea').addEventListener('change', (e) => {
                state.filterArea = e.target.value;
                state.page = 1;
                renderTable();
            });
            $('filterStatus').addEventListener('change', (e) => {
                state.filterStatus = e.target.value;
                state.page = 1;
                renderTable();
            });
            $('perPageSelect').addEventListener('change', (e) => {
                state.perPage = Number(e.target.value);
                state.page = 1;
                renderTable();
            });
            // Modales
            $('btnNuevaAlta').addEventListener('click', openModal);
            $('btnCloseModal').addEventListener('click', confirmClose);
            $('btnCancelAlta').addEventListener('click', confirmClose);
            $('altaModalOverlay').addEventListener('click', (e) => {
                if (e.target.id === 'altaModalOverlay') confirmClose();
            });
            $('btnCloseViewModal').addEventListener('click', closeViewModal);
            $('btnCloseViewBtn').addEventListener('click', closeViewModal);
            $('viewModalOverlay').addEventListener('click', (e) => {
                if (e.target.id === 'viewModalOverlay') closeViewModal();
            });
            // Wizard
            $('btnNextStep').addEventListener('click', () => {
                if (validateStep(state.currentStep) && state.currentStep < state.totalSteps) goToStep(state
                    .currentStep + 1);
            });
            $('btnPrevStep').addEventListener('click', () => {
                if (state.currentStep > 1) goToStep(state.currentStep - 1);
            });
            $('btnSubmitAlta').addEventListener('click', submitAlta);
            // Formularios
            ['firstName', 'secondName', 'firstSurname', 'secondSurname'].forEach(id => $(id).addEventListener(
                'input', updateFullName));
            $('gender').addEventListener('change', (e) => renderNationalityOptions(e.target.value));
            bindAreaToDepartment();
            bindManagerAutocomplete();
            bindPhotoUpload();
        }

        function init() {
            bindEvents();
            initDatePickers();
            renderNationalityOptions('');
            loadInitialData();
        }
        document.addEventListener('DOMContentLoaded', init);
    })();
</script>
@endsection
