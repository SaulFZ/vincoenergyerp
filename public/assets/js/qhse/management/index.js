/**
 * Inicialización de la Interfaz de Vinco Energy
 */
function initApp() {

   const yearSpan = document.getElementById('current-year');
    if (yearSpan) {
        yearSpan.textContent = new Date().getFullYear();
    }

    // 2. Lógica de Navegación Activa
    // Obtenemos la ruta actual (ej: 'journey', 'driver_licenses', 'stats')
    const currentPath = window.location.pathname.split('/').pop() || 'journey';

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
                window.location.href = `/qhse/management/${route}`;
            }
        });
    });
}
// Ejecutar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', initApp);
