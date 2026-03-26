/**
 * Inicialización de la Interfaz de Load Chart - Vinco Energy
 */
function initApp() {
    // 1. Actualizar año dinámico en el footer
    const yearSpan = document.getElementById('current-year');
    if (yearSpan) {
        yearSpan.textContent = new Date().getFullYear();
    }

    // 2. Lógica de Navegación Activa
    // Obtenemos la ruta actual (ej: 'calendar', 'history', 'stats')
    const currentPath = window.location.pathname.split('/').pop() || 'calendar';

    // Seleccionamos todos los enlaces de navegación
    const navLinks = document.querySelectorAll('.nav-link');

    navLinks.forEach(link => {
        const linkRoute = link.getAttribute('data-route');

        // Si la ruta del link coincide con la URL actual, añadimos 'active'
        if (linkRoute === currentPath) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }

        // Manejador de clics para redirección limpia
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const route = this.getAttribute('data-route');
            if (route) {
                // Importante: Ruta apuntando al módulo de Load Chart
                window.location.href = `/rh/loadchart/${route}`;
            }
        });
    });
}

// Ejecutar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', initApp);
