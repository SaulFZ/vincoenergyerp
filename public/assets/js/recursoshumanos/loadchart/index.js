function initCardDirectNavigation() {
    document.querySelectorAll(".card:not(.disabled)").forEach((card) => {
        card.addEventListener("click", function () {
            const route = this.getAttribute("data-route");
            if (route && route !== "#") {
                window.location.href = route; // Funciona para todas las rutas
            }
        });
    });
}
