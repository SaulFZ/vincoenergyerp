/**
 * Inicialización de la Interfaz de Vinco Energy
 */
function initApp() {

   const yearSpan = document.getElementById('current-year');
    if (yearSpan) {
        yearSpan.textContent = new Date().getFullYear();
    }

    // 2. Lógica de Navegación Activa
    const currentPath = window.location.pathname.split('/').pop() || 'reimbursements';

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
                window.location.href = `/administration/expense-claims/${route}`;
            }
        });
    });
}
// Ejecutar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', initApp);
