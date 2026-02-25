function initNavigationViajes() {
    // Obtener la ruta actual (ej: "journey", "unidades", "driver-licenses")
    // Si está en la raíz del módulo, por defecto que marque "journey"
    const currentPath = window.location.pathname.split('/').pop() || 'journey';

    // Manejar clics en los enlaces de navegación
    document.querySelectorAll('.nav-viajes a').forEach(link => {
        const linkRoute = link.getAttribute('data-route');

        // Marcar como activo si coincide con la ruta actual
        if (linkRoute === currentPath) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }

        // Manejar el clic para redireccionar
        link.addEventListener('click', function(e) {
            e.preventDefault();
            // Aquí armamos la URL correcta hacia Gerenciamiento
            window.location.href = `/qhse/gerenciamiento/${linkRoute}`;
        });
    });
}

document.addEventListener('DOMContentLoaded', initNavigationViajes);
