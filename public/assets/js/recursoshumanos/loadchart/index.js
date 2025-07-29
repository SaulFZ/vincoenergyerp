function initNavigation() {
    // Obtener la ruta actual (ej: "calendar", "history", "stats")
    const currentPath = window.location.pathname.split('/').pop() || 'calendar';

    // Manejar clics en los enlaces del header
    document.querySelectorAll('.nav-links a').forEach(link => {
        const linkRoute = link.getAttribute('data-route');

        // Marcar como activo si coincide con la ruta actual
        if (linkRoute === currentPath) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }

        // Manejar el clic
        link.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = `/recursoshumanos/loadchart/${linkRoute}`;
        });
    });
}

document.addEventListener('DOMContentLoaded', initNavigation);
