/*
 * Los cambios de selects y Enter ya los maneja sgc-tabs.js.
 * Aquí se conecta únicamente el submit del botón Filtrar.
 * La delegación funciona incluso después de reemplazar .tab-body por AJAX.
 */
(() => {
    if (window.sgcMaintenanceSubmitBound) return;
    window.sgcMaintenanceSubmitBound = true;

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (
            event.defaultPrevented ||
            !(form instanceof HTMLFormElement) ||
            !form.matches('#filter-form[data-maintenance-filters]')
        ) {
            return;
        }

        // Si el JS general no está disponible, continúa el GET tradicional.
        if (typeof window.fetchTabContent !== 'function') return;

        event.preventDefault();

        const url = new URL(form.action, window.location.origin);
        const params = new URLSearchParams(new FormData(form));

        params.set('view', 'list');
        url.search = params.toString();

        window.fetchTabContent(url.href);
    });
})();
