<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@php
    // Ejemplo de cómo podrías pasar el conteo de notificaciones desde el backend
    $notificationCount = 3;

    // 🚨 LÓGICA DE DETECCIÓN DE GÉNERO
    $userGender = null;
    if (Auth::check() && Auth::user()->employee) {
        $gender = strtolower(Auth::user()->employee->gender ?? '');
        if (str_contains($gender, 'femenino') || $gender === 'f' || $gender === 'female') {
            $userGender = 'female';
        }
    }

    // Asegurarse de tener la variable $employee si está autenticado
    $employee = Auth::check() && Auth::user()->employee ? Auth::user()->employee : null;

    // ✅ LÓGICA DE DETECCIÓN INTELIGENTE (Esquivando el choque de nombres)
    $areaName = null;
    $departmentName = null;

    if ($employee) {
        // 1. Obtener Área (Aquí no hay choque porque ya borraste la columna de texto 'area')
        if ($employee->area_id && $employee->area) {
            $areaName = $employee->area->name;
        }

        // 2. Obtener Departamento
        if ($employee->department_id) {
            // Forzamos la relación usando los paréntesis () y ->first()
            // para que Laravel no nos traiga la columna de texto por error.
            $deptRelation = $employee->department()->first();
            $departmentName = $deptRelation ? $deptRelation->name : null;
        } else {
            // Si NO tiene department_id, leemos la columna vieja de texto cruda
            $textoViejo = $employee->getAttributes()['department'] ?? null;
            if (!empty($textoViejo) && $textoViejo !== $areaName) {
                $departmentName = $textoViejo;
            }
        }
    }

    // Formatear fechas
    $hireDateFormatted = $employee && $employee->hire_date
        ? \Carbon\Carbon::parse($employee->hire_date)->format('d/m/Y')
        : 'No disponible';
    $birthDateFormatted = $employee && $employee->birth_date
        ? \Carbon\Carbon::parse($employee->birth_date)->format('d/m/Y')
        : 'No disponible';

    // Placeholder para el logo
    $companyLogoPath = 'assets/img/logo.png';
    $companyName = 'Vinco Energy';

    // Estado laboral
    $employmentStatus = $employee->employment_status ?? 'No disponible';
    $employmentStatusFormatted = (strtolower($employmentStatus) === 'active') ? 'Activo' : $employmentStatus;

    // Correos
    $corporateEmail = Auth::check() ? Auth::user()->email : null;
    $personalEmail = $employee ? $employee->personal_email : null;

    if (!empty($corporateEmail)) {
        $emailLabel = 'Correo Corporativo';
        $emailValue = $corporateEmail;
        $emailIcon = 'fas fa-envelope';
    } elseif (!empty($personalEmail)) {
        $emailLabel = 'Correo Personal';
        $emailValue = $personalEmail;
        $emailIcon = 'fas fa-envelope-open-text';
    } else {
        $emailLabel = 'Correo Electrónico';
        $emailValue = 'No disponible';
        $emailIcon = 'fas fa-envelope';
    }

    // Foto
    $photoUrl = null;
    if ($employee && $employee->photo) {
        $photoUrl = str_starts_with($employee->photo, 'assets/')
            ? asset($employee->photo)
            : asset('storage/' . $employee->photo);
    }
@endphp

<style>
    /* ==========================================================================
       ESTILOS BASE Y UTILITARIOS
     ========================================================================== */
    :root {
        /* Colores Primarios Intensos */
        --color-primary-male: #0f5db6;
        --color-primary-male-dark: #0756b0;
        --color-primary-female: #D81B60;
        --color-primary-female-dark: #AD1457;

        /* Colores Neutros de Hover y Estado */
        --color-neutral-hover: #e9ecef;
        --color-neutral-text: #343a40;
        --color-online: #28a745;

        --color-secondary: #6c757d;
        --color-danger: #dc3545;
        --color-success: #28a745;
        --color-light: #f8f9fa;
        --color-dark: #343a40;
        --transicion: all 0.3s ease-in-out;
        --border-radius: 12px;
        --sombra: 0 4px 12px rgba(0, 0, 0, 0.1);
        --sombra-intensa: 0 8px 20px rgba(0, 0, 0, 0.15);
        --sombra-dropdown: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .user-actions {
        display: flex;
        align-items: center;
        gap: 15px;
        position: relative;
        font-family: "Poppins", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    /* --- Icono de Notificación / Icono de Inicio --- */
    .notification-icon,
    .home-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transicion);
        position: relative;
        background: white;
        color: var(--color-dark);
        font-size: 1.1rem;
        box-shadow: var(--sombra);
    }

    .notification-icon:hover,
    .home-icon:hover {
        background: var(--color-light);
        color: var(--color-dark);
        transform: scale(1.05);
    }

    .notification-count {
        position: absolute;
        top: -5px;
        right: -5px;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 700;
        background: var(--color-danger);
        color: white;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        animation: pulse 2s infinite;
    }

    /* --- Perfil de Usuario --- */
    .user-profile {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        transition: var(--transicion);
        padding: 6px 15px 6px 6px;
        border-radius: 30px;
        background: white;
        box-shadow: var(--sombra);
        border: 1px solid #dee2e6;
    }

    .user-profile:hover {
        background: var(--color-light);
        box-shadow: var(--sombra-intensa);
    }

    /* Contenedor para foto y estado */
    .user-photo-status-container {
        position: relative;
        width: 40px;
        height: 40px;
    }

    .user-photo,
    .user-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: none;
        transition: var(--transicion);
        opacity: 0;
    }

    .user-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--color-secondary);
        color: white;
        font-size: 1.2rem;
        opacity: 1;
    }

    /* Punto de estado "En línea" */
    .status-dot {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        border: 2px solid white;
        z-index: 5;
    }

    .status-dot.online {
        background-color: var(--color-online);
    }

    .user-name {
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--color-dark);
    }

    /* --- Dropdown --- */
    .user-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 10px;
        background: white;
        box-shadow: var(--sombra-dropdown);
        border-radius: var(--border-radius);
        width: 320px;
        padding: 0;
        display: none;
        border: 1px solid #e1e5e9;
        z-index: 1001;
        overflow: hidden;
        transform-origin: top right;
        color: var(--color-dark);
    }

    .user-dropdown.active {
        display: block;
        animation: dropdownSlide 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .dropdown-header {
        padding: 15px 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        background: #f4f6f9;
        border-bottom: 1px solid #e1e5e9;
    }

    .dropdown-photo {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid white;
        box-shadow: var(--sombra);
        opacity: 0;
    }

    .dropdown-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--color-secondary);
        color: white;
        font-size: 20px;
        box-shadow: var(--sombra);
    }

    .dropdown-username {
        font-weight: 700;
        font-size: 15px;
    }

    .dropdown-email {
        font-size: 13px;
        color: var(--color-secondary);
    }

    .dropdown-divider {
        height: 1px;
        background: #e9ecef;
        margin: 5px 0;
    }

    .dropdown-item {
        padding: 12px 20px;
        cursor: pointer;
        transition: var(--transicion);
        display: flex;
        align-items: center;
        font-size: 14px;
        text-decoration: none;
        color: var(--color-neutral-text);
        width: 100%;
        background: none;
        border: none;
        text-align: left;
    }

    .dropdown-item:hover {
        background: var(--color-neutral-hover);
        color: var(--color-neutral-text);
    }

    .dropdown-item i {
        margin-right: 12px;
        width: 18px;
        text-align: center;
        font-size: 15px;
        color: var(--color-secondary);
        transition: var(--transicion);
    }

    .dropdown-item:hover i {
        color: var(--color-dark);
    }

    .logout {
        color: var(--color-danger) !important;
        border-top: 1px solid #e9ecef;
    }

    .logout i {
        color: var(--color-danger) !important;
    }

    .logout:hover {
        background: var(--color-danger) !important;
        color: white !important;
    }

    .logout:hover i {
        color: white !important;
    }

    /* ==========================================================================
       MODAL DE PERFIL DE EMPLEADO
     ========================================================================== */
    .profile-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1002;
        opacity: 0;
        visibility: hidden;
        display: none;
        transition: opacity 0.3s ease, visibility 0.3s ease;
        align-items: center;
        justify-content: center;
        padding: 20px;
        box-sizing: border-box;
        overflow-y: auto;
    }

    .profile-modal.active {
        opacity: 1;
        visibility: visible;
        display: flex !important;
    }

    .profile-modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
    }

    .profile-modal-container {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 950px;
        margin: auto;
        box-shadow: var(--sombra-intensa);
        border-radius: var(--border-radius);
    }

    .profile-modal.active .profile-modal-container {
        animation: modalFadeIn 0.3s forwards;
    }

    @keyframes modalFadeIn {
        from {
            transform: translateY(-20px) scale(0.98);
            opacity: 0;
        }

        to {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
    }

    .profile-modal-content {
        background: white;
        border-radius: var(--border-radius);
        width: 100%;
        overflow: hidden;
        display: flex;
        max-height: 90vh;
        position: relative;
    }

    .profile-modal-close {
        position: absolute;
        top: 15px;
        right: 15px;
        background: var(--color-danger);
        border: none;
        color: white;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transicion);
        font-size: 1rem;
        z-index: 10;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .profile-modal-close:hover {
        background: #c82333;
        transform: rotate(90deg) scale(1.1);
    }

    .profile-sidebar {
        width: 350px;
        background: linear-gradient(145deg, var(--color-primary-male) 0%, var(--color-primary-male-dark) 100%);
        color: white;
        padding: 20px 30px 40px 30px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        justify-content: flex-start;
        box-sizing: border-box;
    }

    .company-header {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-bottom: 20px;
        width: 100%;
    }

    .company-logo {
        width: 45px;
        height: 45px;
        object-fit: contain;
        border-radius: 0;
        padding: 0;
        flex-shrink: 0;
        filter: brightness(0) invert(1);
    }

    .company-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: white;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .sidebar-title {
        font-size: 1rem;
        font-weight: 500;
        margin-bottom: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.5);
        padding-bottom: 10px;
        width: 100%;
    }

    .profile-modal.theme-female .profile-sidebar {
        background: linear-gradient(145deg, var(--color-primary-female) 0%, var(--color-primary-female-dark) 100%);
    }

    .profile-photo-container {
        position: relative;
        margin-bottom: 15px;
        cursor: pointer;
    }

    .profile-photo-large {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid rgba(255, 255, 255, 0.9);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        transition: var(--transicion);
        opacity: 0;
    }

    .profile-photo-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        font-size: 3.5rem;
        opacity: 1;
    }

    .profile-photo-container:hover .profile-photo-large {
        transform: scale(1.05);
        border-color: white;
    }

    .profile-photo-zoom {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background: white;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
        opacity: 0;
        transition: var(--transicion);
        color: var(--color-primary-male);
    }

    .profile-photo-container:hover .profile-photo-zoom {
        opacity: 1;
        transform: scale(1.1);
    }

    .profile-modal.theme-female .profile-photo-zoom {
        color: var(--color-primary-female);
    }

    .profile-name-large {
        font-size: 1.6rem;
        font-weight: 700;
        margin-bottom: 5px;
        line-height: 1.2;
    }

    .profile-position {
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 5px;
        font-weight: 500;
    }

    .profile-department {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 500;
        padding: 6px 15px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 20px;
        display: inline-block;
        margin-bottom: 25px;
    }

    /* Stats */
    .profile-stats {
        display: flex;
        justify-content: space-around;
        width: 100%;
        margin-top: auto;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.3);
        padding-bottom: 10px;
    }

    .profile-stat {
        flex: 1;
        padding: 0 10px;
    }

    .profile-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .profile-stat-label {
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.9);
    }

    .profile-main {
        flex-grow: 1;
        padding: 40px;
        overflow-y: auto;
        max-height: 90vh;
        box-sizing: border-box;
    }

    .profile-modal-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--color-primary-male-dark);
        margin-top: 25px;
    }

    .profile-main h2:first-child {
        margin-top: 0;
    }

    .profile-modal-title i {
        font-size: 1.3rem;
    }

    .profile-modal.theme-female .profile-main .profile-modal-title {
        color: var(--color-primary-female-dark);
    }

    .profile-info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }

    .profile-info-item.full-width {
        grid-column: 1 / -1;
    }

    .profile-info-label {
        font-size: 0.75rem;
        color: var(--color-secondary);
        font-weight: 600;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .profile-info-label i {
        width: 16px;
        text-align: center;
        font-size: 0.9rem;
        color: var(--color-primary-male);
    }

    .profile-modal.theme-female .profile-main .profile-info-label i {
        color: var(--color-primary-female);
    }

    .profile-info-value {
        font-size: 1rem;
        color: var(--color-dark);
        font-weight: 500;
        padding: 12px 15px;
        background: var(--color-light);
        border-radius: var(--border-radius);
        min-height: 45px;
        display: flex;
        align-items: center;
        transition: var(--transicion);
        border-left: 4px solid var(--color-primary-male);
    }

    .profile-modal.theme-female .profile-main .profile-info-value {
        border-left: 4px solid var(--color-primary-female);
    }

    .profile-info-value:hover {
        background: #e9ecef;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .profile-info-value.empty {
        color: var(--color-secondary);
        font-style: italic;
    }

    .profile-main-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .profile-sidebar-placeholder {
        justify-content: center;
    }

    .info-placeholder {
        text-align: center;
        padding: 50px;
        color: var(--color-secondary);
    }

    .info-placeholder i {
        font-size: 3rem;
        margin-bottom: 15px;
        color: var(--color-primary-male);
    }

    .profile-sidebar-placeholder .info-placeholder i {
        color: var(--color-secondary);
    }

    .info-placeholder p {
        font-size: 1.1rem;
    }

    /* ==========================================================================
       MODAL DE FOTO AMPLIADA
     ========================================================================== */
    .photo-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1003;
        opacity: 0;
        visibility: hidden;
        display: none;
        transition: opacity 0.3s ease, visibility 0.3s ease;
        align-items: center;
        justify-content: center;
        padding: 20px;
        box-sizing: border-box;
    }

    .photo-modal.active {
        opacity: 1;
        visibility: visible;
        display: flex !important;
    }

    .photo-modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        z-index: 1;
    }

    .photo-modal-container {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .photo-modal-content {
        position: relative;
        max-width: 90%;
        max-height: 90%;
        transform: scale(0.8);
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .photo-modal.active .photo-modal-content {
        transform: scale(1);
    }

    .photo-modal-img {
        max-width: 100%;
        max-height: 90vh;
        border-radius: var(--border-radius);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .photo-modal-close {
        position: absolute;
        top: -50px;
        right: 0;
        background: none;
        border: none;
        color: white;
        font-size: 2rem;
        cursor: pointer;
        transition: transform 0.3s ease;
        text-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
    }

    .photo-modal-close:hover {
        transform: scale(1.2);
    }

    /* ==========================================================================
       ANIMACIONES Y RESPONSIVE
     ========================================================================== */
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    @keyframes dropdownSlide {
        from {
            opacity: 0;
            transform: translateY(-5px) scale(0.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @media (max-width: 992px) {
        .profile-modal-content {
            flex-direction: column;
            max-height: 95vh;
            overflow-y: auto;
        }

        .profile-sidebar,
        .profile-main {
            width: 100%;
            padding: 30px;
        }

        .profile-sidebar {
            padding-bottom: 20px;
        }

        .profile-main {
            padding-top: 15px;
            overflow-y: visible;
            max-height: none;
        }

        .profile-stats {
            margin-top: 15px;
            padding-top: 15px;
            padding-bottom: 0;
        }

        .profile-modal-title {
            margin-top: 15px;
            font-size: 1.4rem;
        }

        .profile-info-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
    }

    @media (max-width: 768px) {
        .user-name { display: none; }
        .user-profile { padding: 4px 6px 4px 4px; }
        .user-dropdown {
            width: 90vw;
            max-width: 320px;
            right: 0;
        }
        .profile-modal { padding: 10px; }
        .profile-modal-container { width: 100%; }
        .profile-info-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
    }

    @media (max-width: 480px) {
        .dropdown-header {
            flex-direction: column;
            text-align: center;
            padding: 15px;
        }
        .dropdown-user-info { text-align: center; }
        .user-photo,
        .user-icon,
        .user-photo-status-container {
            width: 35px;
            height: 35px;
            font-size: 1rem;
        }
        .notification-icon,
        .home-icon {
            width: 40px;
            height: 40px;
        }
        .status-dot {
            width: 8px;
            height: 8px;
            border: 1px solid white;
        }
        .profile-sidebar,
        .profile-main {
            padding: 20px;
        }
    }
</style>

<div class="user-actions" data-gender="{{ $userGender }}">
    <a href="{{ route('home') }}" class="home-icon" title="Ir a Inicio">
        <i class="fas fa-home"></i>
    </a>

    <div class="notification-icon" style="display: none;">
        <i class="fas fa-bell"></i>
        @if ($notificationCount > 0)
            <span class="notification-count">{{ $notificationCount }}</span>
        @endif
    </div>

    <div class="user-profile" id="userDropdownBtn">
        @auth
            <div class="user-photo-status-container">
                @if ($photoUrl)
                    <img src="{{ $photoUrl }}" alt="Foto de perfil" class="user-photo" onload="this.style.opacity='1'" />
                @else
                    <div class="user-icon">
                        <i class="fas fa-user"></i>
                    </div>
                @endif
                <span class="status-dot online"></span>
            </div>
            <div class="user-name">{{ Auth::user()->name }}</div>
        @else
            <div class="user-icon">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-name">Usuario</div>
        @endauth
    </div>

    <div class="user-dropdown" id="userDropdown">
        <div class="dropdown-header">
            @auth
                @if ($photoUrl)
                    <img src="{{ $photoUrl }}" alt="Foto de perfil" class="dropdown-photo" onload="this.style.opacity='1'" />
                @else
                    <div class="dropdown-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                @endif
                <div class="dropdown-user-info">
                    <div class="dropdown-username">{{ $employee->full_name ?? Auth::user()->name }}</div>
                    <div class="dropdown-email">{{ $emailValue }}</div>
                </div>
            @else
                <div class="dropdown-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="dropdown-user-info">
                    <div class="dropdown-username">Usuario</div>
                    <div class="dropdown-email">No autenticado</div>
                </div>
            @endauth
        </div>

        <div class="dropdown-divider"></div>

        <a class="dropdown-item" id="profileBtn">
            <i class="fas fa-user-circle"></i>
            Mi perfil
        </a>

        <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="logout-form">
            @csrf
            <button type="submit" class="dropdown-item logout">
                <i class="fas fa-sign-out-alt"></i>
                Cerrar sesión
            </button>
        </form>
    </div>
</div>

<div class="profile-modal" id="profileModal" style="display: none !important;">
    <div class="profile-modal-backdrop"></div>
    <div class="profile-modal-container">
        <div class="profile-modal-content">
            <button class="profile-modal-close" id="profileModalClose">
                <i class="fas fa-times"></i>
            </button>

            @auth
                @if ($employee)
                    <div class="profile-sidebar">
                        <div class="company-header">
                            <img src="{{ asset($companyLogoPath) }}" alt="Logo de {{ $companyName }}" class="company-logo">
                            <div class="company-name">{{ $companyName }}</div>
                        </div>
                        <h2 class="sidebar-title">Información del Empleado</h2>

                        <div class="profile-photo-container" id="profilePhotoContainer">
                            @if ($photoUrl)
                                <img src="{{ $photoUrl }}" alt="Foto de perfil" class="profile-photo-large" id="profilePhoto" onload="this.style.opacity='1'" />
                            @else
                                <div class="profile-photo-large profile-photo-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                            <div class="profile-photo-zoom">
                                <i class="fas fa-search-plus"></i>
                            </div>
                        </div>

                        <div class="profile-name-large">{{ $employee->full_name ?? Auth::user()->name }}</div>

                        @if ($employee->job_title)
                            <div class="profile-position">{{ $employee->job_title }}</div>
                        @endif

                        {{-- ✅ MODIFICADO: Mostrar Área y (si existe) Departamento --}}
                        @if ($areaName || $departmentName)
                            <div class="profile-department" style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                                @if($areaName)
                                    <span><i class="fas fa-building" style="margin-right: 4px;"></i> {{ $areaName }}</span>
                                @endif

                                @if($areaName && $departmentName)
                                    <span style="opacity: 0.5;">|</span>
                                @endif

                                @if($departmentName)
                                    <span><i class="fas fa-sitemap" style="margin-right: 4px; font-size: 0.9em;"></i> {{ $departmentName }}</span>
                                @endif
                            </div>
                        @endif

                        <div class="profile-stats">
                            <div class="profile-stat">
                                <div class="profile-stat-value">
                                    {{ $employee->employee_number ?? '-' }}
                                </div>
                                <div class="profile-stat-label">ID Empleado</div>
                            </div>
                            <div class="profile-stat">
                                <div class="profile-stat-value">
                                    {{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('Y') : '-' }}
                                </div>
                                <div class="profile-stat-label">Año Ingreso</div>
                            </div>
                        </div>
                    </div>

                    <div class="profile-main">
                        <h2 class="profile-modal-title">
                            <i class="fas fa-user-tie"></i>
                            Detalles Laborales
                        </h2>

                        <div class="profile-info-grid">
                            <div class="profile-info-item">
                                <div class="profile-info-label">
                                    <i class="{{ $emailIcon }}"></i>
                                    {{ $emailLabel }}
                                </div>
                                <div class="profile-info-value {{ $emailValue == 'No disponible' ? 'empty' : '' }}">
                                    {{ $emailValue }}
                                </div>
                            </div>

                            <div class="profile-info-item">
                                <div class="profile-info-label">
                                    <i class="fas fa-phone"></i>
                                    Teléfono
                                </div>
                                <div class="profile-info-value {{ !$employee->phone ? 'empty' : '' }}">
                                    {{ $employee->phone ?? 'No disponible' }}
                                </div>
                            </div>

                            <div class="profile-info-item">
                                <div class="profile-info-label">
                                    <i class="fas fa-id-badge"></i>
                                    Número de empleado
                                </div>
                                <div class="profile-info-value {{ !$employee->employee_number ? 'empty' : '' }}">
                                    {{ $employee->employee_number ?? 'No disponible' }}
                                </div>
                            </div>

                            <div class="profile-info-item">
                                <div class="profile-info-label">
                                    <i class="fas fa-calendar-alt"></i>
                                    Fecha de contratación
                                </div>
                                <div class="profile-info-value {{ !$employee->hire_date ? 'empty' : '' }}">
                                    {{ $hireDateFormatted }}
                                </div>
                            </div>

                            <div class="profile-info-item full-width">
                                <div class="profile-info-label">
                                    <i class="fas fa-briefcase"></i>
                                    Estado laboral
                                </div>
                                <div class="profile-info-value {{ $employmentStatusFormatted == 'No disponible' ? 'empty' : '' }}">
                                    {{ $employmentStatusFormatted }}
                                </div>
                            </div>
                        </div>

                        <h2 class="profile-modal-title">
                            <i class="fas fa-info-circle"></i>
                            Información Personal
                        </h2>

                        <div class="profile-info-grid">
                            <div class="profile-info-item">
                                <div class="profile-info-label">
                                    <i class="fas fa-venus-mars"></i>
                                    Género
                                </div>
                                <div class="profile-info-value {{ !$employee->gender ? 'empty' : '' }}">
                                    {{ $employee->gender ?? 'No disponible' }}
                                </div>
                            </div>

                            <div class="profile-info-item">
                                <div class="profile-info-label">
                                    <i class="fas fa-birthday-cake"></i>
                                    Fecha de nacimiento
                                </div>
                                <div class="profile-info-value {{ !$employee->birth_date ? 'empty' : '' }}">
                                    {{ $birthDateFormatted }}
                                </div>
                            </div>

                            <div class="profile-info-item full-width">
                                <div class="profile-info-label">
                                    <i class="fas fa-globe-americas"></i>
                                    Nacionalidad
                                </div>
                                <div class="profile-info-value {{ !$employee->nationality ? 'empty' : '' }}">
                                    {{ $employee->nationality ?? 'No disponible' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="profile-sidebar profile-sidebar-placeholder">
                        <div class="company-header">
                            <img src="{{ asset($companyLogoPath) }}" alt="Logo de {{ $companyName }}" class="company-logo">
                            <div class="company-name">{{ $companyName }}</div>
                        </div>
                        <h2 class="sidebar-title">Información del Empleado</h2>
                        <div class="profile-photo-large profile-photo-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="profile-name-large">{{ Auth::user()->name }}</div>
                        <div class="profile-position">Usuario</div>
                    </div>
                    <div class="profile-main profile-main-placeholder">
                        <div class="info-placeholder">
                            <i class="fas fa-exclamation-circle"></i>
                            <p>No hay información detallada de empleado disponible.</p>
                        </div>
                    </div>
                @endif
            @else
                <div class="profile-sidebar profile-sidebar-placeholder">
                    <div class="company-header">
                        <img src="{{ asset($companyLogoPath) }}" alt="Logo de {{ $companyName }}" class="company-logo">
                        <div class="company-name">{{ $companyName }}</div>
                    </div>
                    <h2 class="sidebar-title">Información del Empleado</h2>
                    <div class="profile-photo-large profile-photo-placeholder">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="profile-name-large">Usuario</div>
                    <div class="profile-position">No autenticado</div>
                </div>
                <div class="profile-main profile-main-placeholder">
                    <div class="info-placeholder">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Debe iniciar sesión para ver los detalles del perfil.</p>
                    </div>
                </div>
            @endauth
        </div>
    </div>
</div>

<div class="photo-modal" id="photoModal" style="display: none !important;">
    <div class="photo-modal-backdrop"></div>
    <div class="photo-modal-container">
        <div class="photo-modal-content">
            <button class="photo-modal-close" id="photoModalClose">
                <i class="fas fa-times"></i>
            </button>
            @auth
                @if ($photoUrl)
                    <img src="{{ $photoUrl }}" alt="Foto de perfil ampliada" class="photo-modal-img" onload="this.style.opacity='1'" />
                @endif
            @endauth
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const userDropdownBtn = document.getElementById('userDropdownBtn');
        const userDropdown = document.getElementById('userDropdown');
        const profileBtn = document.getElementById('profileBtn');
        const profileModal = document.getElementById('profileModal');
        const profileModalClose = document.getElementById('profileModalClose');
        const profilePhotoContainer = document.getElementById('profilePhotoContainer');
        const photoModal = document.getElementById('photoModal');
        const photoModalClose = document.getElementById('photoModalClose');

        // LÓGICA DE GÉNERO DINÁMICO
        const userActions = document.querySelector('.user-actions');
        const gender = userActions ? userActions.getAttribute('data-gender') : null;

        if (profileModal && gender === 'female') {
            profileModal.classList.add('theme-female');
        }

        // DROPDOWN
        if (userDropdownBtn && userDropdown) {
            userDropdownBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('active');
            });

            document.addEventListener('click', function(e) {
                if (!userDropdown.contains(e.target) && !userDropdownBtn.contains(e.target)) {
                    userDropdown.classList.remove('active');
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && userDropdown.classList.contains('active')) {
                    userDropdown.classList.remove('active');
                }
            });
        }

        // ALERTA DE CERRAR SESIÓN
        const logoutForm = document.getElementById('logoutForm');
        if (logoutForm) {
            logoutForm.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Cerrando sesión...',
                    html: 'Por favor espere un momento.',
                    icon: 'info',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                        setTimeout(() => {
                            logoutForm.submit();
                        }, 1000);
                    }
                });
            });
        }

        function openProfileModal() {
            if (userDropdown) userDropdown.classList.remove('active');
            if (profileModal) {
                profileModal.style.display = '';
                profileModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        if (profileBtn) {
            profileBtn.addEventListener('click', openProfileModal);
        }

        function closeProfileModal() {
            if (profileModal) {
                profileModal.classList.remove('active');
                document.body.style.overflow = '';
                setTimeout(() => {
                    if (!profileModal.classList.contains('active')) {
                        profileModal.style.display = 'none';
                    }
                }, 300);
            }
        }

        if (profileModalClose) {
            profileModalClose.addEventListener('click', closeProfileModal);
        }

        const profileModalBackdrop = profileModal ? profileModal.querySelector('.profile-modal-backdrop') : null;
        if (profileModalBackdrop) {
            profileModalBackdrop.addEventListener('click', closeProfileModal);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && profileModal && profileModal.classList.contains('active')) {
                closeProfileModal();
            }
        });

        // Modal de foto
        if (profilePhotoContainer) {
            const profilePhoto = document.getElementById('profilePhoto');
            if (profilePhoto) {
                profilePhotoContainer.addEventListener('click', function() {
                    if (profilePhoto.src) {
                        if (photoModal) {
                            photoModal.style.display = '';
                            photoModal.classList.add('active');
                            document.body.style.overflow = 'hidden';
                        }
                    }
                });
            }
        }

        function closePhotoModal() {
            if (photoModal) {
                photoModal.classList.remove('active');
                setTimeout(() => {
                    if (!photoModal.classList.contains('active')) {
                        photoModal.style.display = 'none';
                    }
                }, 300);

                if (profileModal && !profileModal.classList.contains('active')) {
                    document.body.style.overflow = '';
                } else if (!profileModal) {
                    document.body.style.overflow = '';
                }
            }
        }

        if (photoModalClose) {
            photoModalClose.addEventListener('click', closePhotoModal);
        }

        const photoModalBackdrop = photoModal ? photoModal.querySelector('.photo-modal-backdrop') : null;
        if (photoModalBackdrop) {
            photoModalBackdrop.addEventListener('click', closePhotoModal);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && photoModal && photoModal.classList.contains('active')) {
                closePhotoModal();
            }
        });

        const images = document.querySelectorAll('img');
        images.forEach(img => {
            if (img.complete) {
                img.style.opacity = '1';
            }
        });
    });

    window.closeModals = function() {
        document.querySelectorAll('.profile-modal, .photo-modal, .user-dropdown').forEach(modal => {
            modal.classList.remove('active');
            if (modal.id === 'profileModal' || modal.id === 'photoModal') {
                modal.style.display = 'none';
            }
        });
        document.body.style.overflow = '';
    };
</script>
