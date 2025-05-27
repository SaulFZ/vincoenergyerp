document.addEventListener("DOMContentLoaded", () => {
    initUserDropdown();
    initCardNavigation();
    initCardHoverEffects();
});


function checkPermissionAndNavigate(module, permission, route, moduleName, moduleIcon, moduleDescription, moduleImage) {
    // Si no se especificó un permiso, usar 'general' por defecto
    permission = permission || 'general';

    console.log(`Verificando permiso: ${module}/${permission}`);

    fetch(`/api/check-permission/${module}/${permission}`)
        .then(response => response.json())
        .then(data => {
            if (data.message === 'Permiso concedido.') {
                // Tiene permiso, ir a transición
                const transitionUrl = `/transition?module=${encodeURIComponent(moduleName)}&icon=${encodeURIComponent(moduleIcon)}&description=${encodeURIComponent(moduleDescription)}&image=${encodeURIComponent(moduleImage)}&redirect=${encodeURIComponent(route)}`;
                window.location.href = transitionUrl;
            } else {
                // Mostrar alerta si no tiene permiso
                Swal.fire({
                    title: 'Acceso Denegado',
                    icon: 'error',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Entendido',
                    text: 'No tienes permiso para acceder a este modulo.'
                });
            }
        })
        .catch(error => {
            console.error('Error al verificar permisos:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al verificar los permisos. Por favor, intenta nuevamente.'
            });
        });
}


// Manejo del dropdown del usuario
function initUserDropdown() {
    const userAvatarContainer = document.getElementById("userAvatarContainer");
    const userDropdown = document.getElementById("userDropdown");
    const dropdownOverlay = document.getElementById("dropdownOverlay");

    if (!userAvatarContainer || !userDropdown || !dropdownOverlay) {
        console.warn("Elementos de usuario no encontrados");
        return;
    }

    userAvatarContainer.addEventListener("click", (e) => {
        e.stopPropagation();
        userAvatarContainer.classList.toggle("active");
        userDropdown.classList.toggle("show");
        dropdownOverlay.classList.toggle("active");
    });

    dropdownOverlay.addEventListener("click", () => {
        userAvatarContainer.classList.remove("active");
        userDropdown.classList.remove("show");
        dropdownOverlay.classList.remove("active");
    });

    // Prevent dropdown from closing when clicking inside
    userDropdown.addEventListener("click", (e) => {
        e.stopPropagation();
    });
}

// Navegación a las diferentes áreas con imagen
function initCardNavigation() {
    document.querySelectorAll(".card").forEach((card) => {
        card.addEventListener("click", function () {
            const route = this.getAttribute("data-route");
            const moduleName = this.getAttribute("data-module-name") || this.querySelector("h1")?.textContent || "Módulo";

            // Obtener el módulo y permiso de los atributos de la tarjeta
            const module = this.getAttribute("data-area");
            const permission = this.getAttribute("data-permission") || 'general';
            const moduleIcon = this.getAttribute("data-icon") || "fa-circle-notch";
            const moduleDescription = this.getAttribute("data-description") || "";
            const moduleImage = this.querySelector("img")?.src || "";

            console.log(`Intentando navegar a: ${module} / ${permission}`);

            // Llama a la función con validación previa
            checkPermissionAndNavigate(module, permission, route, moduleName, moduleIcon, moduleDescription, moduleImage);
        });
    });
}

// Opcional: Helper de transición independiente actualizado para incluir imagen
function navigateWithTransition(route, moduleName, moduleIcon = 'fa-circle-notch', moduleDescription = '', moduleImage = '') {
    const transitionUrl = `/transition?module=${encodeURIComponent(moduleName)}&icon=${encodeURIComponent(moduleIcon)}&description=${encodeURIComponent(moduleDescription)}&image=${encodeURIComponent(moduleImage)}&redirect=${encodeURIComponent(route)}`;
    window.location.href = transitionUrl;
}

// Efecto de brillo al pasar el mouse
function initCardHoverEffects() {
    const cards = document.querySelectorAll('.card');

    cards.forEach(card => {
        const shine = card.querySelector('.card-shine');
        if (!shine) return;

        card.addEventListener('mousemove', function (e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            // Actualizar la posición del efecto de brillo
            shine.style.background = `radial-gradient(circle at ${x}px ${y}px, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0) 60%)`;
            shine.style.opacity = '1';
        });

        card.addEventListener('mouseleave', function () {
            shine.style.opacity = '0';
        });
    });
}
