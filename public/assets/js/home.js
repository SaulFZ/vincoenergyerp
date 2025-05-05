document.addEventListener("DOMContentLoaded", () => {
    initUserDropdown();
    initCardNavigation();
    initCardHoverEffects();
});

// Manejo del dropdown del usuario
function initUserDropdown() {
    const userAvatarContainer = document.getElementById("userAvatarContainer");
    const userDropdown = document.getElementById("userDropdown");
    const dropdownOverlay = document.getElementById("dropdownOverlay");

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

// Navegación a las diferentes áreas - Simplificada para usar solo rutas Laravel
// Modificación a tu archivo initCardNavigation() existente
function initCardNavigation() {
    document.querySelectorAll(".card").forEach((card) => {
        card.addEventListener("click", function () {
            const area = this.getAttribute("data-area");

            // Agregar efecto de clic
            this.classList.add("card-clicked");

            // Obtener datos para la transición
            const route = this.getAttribute("data-route");
            const moduleName = this.getAttribute("data-module-name") || this.querySelector("h1")?.textContent || area;
            const moduleIcon = this.getAttribute("data-icon") || "fa-circle-notch";
            const moduleDescription = this.getAttribute("data-description") || this.querySelector("p")?.textContent || "";

            // Redirigir a la página de transición con parámetros
            const transitionUrl = `/transition?module=${encodeURIComponent(moduleName)}&icon=${encodeURIComponent(moduleIcon)}&description=${encodeURIComponent(moduleDescription)}&redirect=${encodeURIComponent(route)}`;

            // Redirigir después de un breve retraso para permitir la animación de clic
            setTimeout(() => {
                window.location.href = transitionUrl;
            }, 300);
        });
    });
}

// Opcional: Puedes usar esto como un helper de transición independiente para otras partes de la app
function navigateWithTransition(route, moduleName, moduleIcon = 'fa-circle-notch', moduleDescription = '') {
    const transitionUrl = `/transition?module=${encodeURIComponent(moduleName)}&icon=${encodeURIComponent(moduleIcon)}&description=${encodeURIComponent(moduleDescription)}&redirect=${encodeURIComponent(route)}`;
    window.location.href = transitionUrl;
}

// Efecto de brillo al pasar el mouse
function initCardHoverEffects() {
    const cards = document.querySelectorAll('.card');

    cards.forEach(card => {
        card.addEventListener('mousemove', function (e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            // Actualizar la posición del efecto de brillo
            const shine = this.querySelector('.card-shine');
            shine.style.background = `radial-gradient(circle at ${x}px ${y}px, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0) 60%)`;
            shine.style.opacity = '1';
        });

        card.addEventListener('mouseleave', function () {
            const shine = this.querySelector('.card-shine');
            shine.style.opacity = '0';
        });
    });
}
