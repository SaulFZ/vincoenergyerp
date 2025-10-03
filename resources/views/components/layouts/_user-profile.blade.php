@php
    // Ejemplo de cómo podrías pasar el conteo de notificaciones desde el backend
    $notificationCount = 3; // Reemplaza esto con una variable real de tu controlador
@endphp

<style>





    /* ESTILOS DEL COMPONENTE DE USUARIO */
    .user-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
    }

    .notification-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transicion);
        position: relative;
        border: 2px solid transparent;
        background: rgba(0, 0, 0, 0.229);
        color: var(--blanco);
        backdrop-filter: blur(10px);
    }

    .notification-icon:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px) scale(1.05);
        border-color: rgba(255, 255, 255, 0.3);
    }

    .notification-count {
        position: absolute;
        top: -8px;
        right: -8px;
        background: linear-gradient(135deg, var(--naranja) 0%, var(--naranja-claro) 100%);
        color: var(--blanco);
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        border: 2px solid var(--blanco);
        box-shadow: var(--sombra);
        animation: pulse 2s infinite;
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        transition: var(--transicion);
        padding: 4px 8px;
        border-radius: 24px;
        background: rgba(0, 0, 0, 0);
        backdrop-filter: blur(10px);
    }

    .user-profile:hover {
        background: rgba(0, 0, 0, 0.127);
        transform: translateY(-2px);
    }

    .user-photo {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid var(--blanco);
        box-shadow: var(--sombra);
        transition: var(--transicion);
    }

    .user-photo:hover {
        transform: scale(1.05);
        box-shadow: var(--sombra-intensa);
    }

    .user-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--blanco);
        box-shadow: var(--sombra);
    }

    .user-name {
        font-weight: 700;
        font-size: 1rem;
        color: var(--blanco);
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    /* ESTILOS DEL MENÚ DESPLEGABLE */
    .user-dropdown {
        position: absolute;
        top: 85px;
        right: 20px;
        background: var(--blanco);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        border-radius: var(--border-radius);
        width: 340px;
        padding: 0;
        display: none;
        border: 1px solid var(--gris-medio);
        z-index: 1001;
        overflow: hidden;
        backdrop-filter: blur(10px);
    }

    .user-dropdown.active {
        display: block;
        animation: slideDown 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .dropdown-header {
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        background: linear-gradient(135deg, var(--azul) 0%, var(--azul) 100%);
        color: var(--blanco);
    }

    .dropdown-photo {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--blanco);
        box-shadow: var(--sombra);
    }

    .dropdown-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.2);
        color: var(--blanco);
        font-size: 20px;
    }

    .dropdown-user-info {
        flex: 1;
    }

    .dropdown-username {
        font-weight: 600;
        font-size: 15px;
        margin-bottom: 4px;
    }

    .dropdown-email {
        font-size: 13px;
        opacity: 0.9;
    }

    .dropdown-divider {
        height: 1px;
        background: var(--gris-claro);
        margin: 0;
    }

    .dropdown-item {
        padding: 14px 20px;
        cursor: pointer;
        transition: var(--transicion);
        display: flex;
        align-items: center;
        color: var(--negro);
        font-size: 15px;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        text-decoration: none;
    }

    .dropdown-item i {
        margin-right: 15px;
        width: 24px;
        text-align: center;
        color: var(--azul);
        font-size: 16px;
    }

    .dropdown-item:hover {
        background: linear-gradient(135deg, var(--gris-claro) 0%, #f1f5f9 100%);
        transform: translateX(5px);
    }

    .logout {
        color: #ff0000 !important;
        border-top: 1px solid #e0e0e0;
        margin-top: 0;
        transition: all 0.3s ease;
    }

    .logout i {
        color: #ff0000 !important;
        transition: all 0.3s ease;
    }

    .logout:hover {
        background: #ff0000 !important;
        color: white !important;
    }

    .logout:hover i {
        color: white !important;
    }

    .logout-form {
        margin: 0;
        padding: 0;
    }

    /* ANIMACIONES */
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-15px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /* ESTILOS RESPONSIVOS */
    @media (max-width: 576px) {
        .user-name {
            display: none;
        }
    }
</style>

<div class="user-actions">
    <div class="notification-icon">
        <i class="fas fa-bell"></i>
        @if ($notificationCount > 0)
            <span class="notification-count">{{ $notificationCount }}</span>
        @endif
    </div>

    <div class="user-profile" id="userDropdownBtn">
        @auth
            @if (Auth::user()->employee && Auth::user()->employee->photo)
                <img src="{{ asset(Auth::user()->employee->photo) }}" alt="Foto de perfil" class="user-photo" />
            @else
                <div class="user-icon">
                    <i class="fas fa-user"></i>
                </div>
            @endif
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
                @if (Auth::user()->employee && Auth::user()->employee->photo)
                    <img src="{{ asset(Auth::user()->employee->photo) }}" alt="Foto de perfil" class="dropdown-photo" />
                @else
                    <div class="dropdown-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                @endif
                <div class="dropdown-user-info">
                    <div class="dropdown-username">{{ Auth::user()->name }}</div>
                    <div class="dropdown-email">{{ Auth::user()->email }}</div>
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

        <a class="dropdown-item">
            <i class="fas fa-user-circle"></i>
            Mi perfil
        </a>

        <a class="dropdown-item">
            <i class="fas fa-cog"></i>
            Configuración
        </a>

        <a class="dropdown-item" href="{{ route('home') }}">
            <i class="fas fa-home"></i>
            Inicio
        </a>

        <form action="{{ route('logout') }}" method="POST" class="logout-form">
            @csrf
            <button type="submit" class="dropdown-item logout">
                <i class="fas fa-sign-out-alt"></i>
                Cerrar sesión
            </button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const userDropdownBtn = document.getElementById('userDropdownBtn');
        const userDropdown = document.getElementById('userDropdown');

        if (userDropdownBtn && userDropdown) {
            userDropdownBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('active');
            });

            // Cerrar el dropdown al hacer clic fuera
            document.addEventListener('click', function(e) {
                if (
                    !userDropdown.contains(e.target) &&
                    !userDropdownBtn.contains(e.target)
                ) {
                    userDropdown.classList.remove('active');
                }
            });
        }
    });
</script>
