// Este script contiene funcionalidades compartidas para todos los layouts de área
document.addEventListener("DOMContentLoaded", () => {
    // Añadir efectos de hover y transiciones para elementos interactivos
    initButtonEffects();
    initCardDirectNavigation();


    // Para futuras expansiones, podemos agregar más inicializadores aquí
});



function initButtonEffects() {
    // Efecto de hover para el botón de inicio
    const homeButton = document.querySelector('.home-button');
    if (homeButton) {
        homeButton.addEventListener('mouseenter', function () {
            this.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.2)';
        });

        homeButton.addEventListener('mouseleave', function () {
            this.style.boxShadow = 'none';
        });

        // Efecto de clic
        homeButton.addEventListener('click', function () {
            // Añadir clase que reduce ligeramente el tamaño del botón al hacer clic
            this.classList.add('clicked');

            // Remover la clase después de la transición
            setTimeout(() => {
                this.classList.remove('clicked');
            }, 150);
        });
    }
}



// Función auxiliar para añadir la animación de transición al navegar entre páginas
function navigateWithTransition(url) {
    // Crear y aplicar un efecto de desvanecimiento antes de la navegación
    const overlay = document.createElement('div');
    overlay.style.position = 'fixed';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.width = '100%';
    overlay.style.height = '100%';
    overlay.style.backgroundColor = 'rgba(0, 0, 0, 0)';
    overlay.style.transition = 'background-color 0.3s ease';
    overlay.style.zIndex = '9999';
    document.body.appendChild(overlay);

    // Aplicar desvanecimiento
    setTimeout(() => {
        overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.2)';
    }, 10);

    // Redirigir después de completar la animación
    setTimeout(() => {
        window.location.href = url;
    }, 300);
}


function initCardDirectNavigation() {
    document.querySelectorAll(".card:not(.disabled)").forEach((card) => {
        card.addEventListener("click", function () {
            const route = this.getAttribute("data-route");
            if (route && route !== "#") {
                window.location.href = route;
            }
        });
    });
}
