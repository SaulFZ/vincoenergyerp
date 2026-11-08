document.addEventListener('DOMContentLoaded', () => {
    const tabBody = document.querySelector('.tab-body');

    // Si no estamos en una vista con el contenedor de pestañas, no hacemos nada
    if (!tabBody) return;

    // ==========================================
    // 1. FUNCIÓN PRINCIPAL DE CARGA AJAX
    // ==========================================
    window.fetchTabContent = function(url) {
        tabBody.style.opacity = '0.4';
        tabBody.style.pointerEvents = 'none';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => {
                if (!response.ok) throw new Error('Error en la petición del servidor');
                return response.text();
            })
            .then(html => {
                // Inyectamos el nuevo HTML (filtros + contenido)
                tabBody.innerHTML = html;

                tabBody.style.opacity = '1';
                tabBody.style.pointerEvents = 'auto';

                window.history.pushState({}, '', url);
                updateActiveTabColors(url);

                // Disparamos un evento por si alguna vista necesita inicializar JS
                const urlParams   = new URL(url, window.location.origin).searchParams;
                const viewLoaded  = urlParams.get('view') || 'list';
                document.dispatchEvent(new CustomEvent('sgcTabLoaded', { detail: { view: viewLoaded } }));
            })
            .catch(error => {
                console.error("AJAX falló:", error);
                // Si falla, restauramos la vista y forzamos recarga tradicional
                tabBody.style.opacity = '1';
                tabBody.style.pointerEvents = 'auto';
                window.location.href = url;
            });
    };

    function updateActiveTabColors(url) {
        const urlParams   = new URL(url, window.location.origin).searchParams;
        const currentView = urlParams.get('view');

        document.querySelectorAll('.tab-btn, .tab-link').forEach(btn => {
            btn.classList.remove('active');

            const btnHref = btn.getAttribute('href');
            if (currentView) {
                if (btnHref.includes('view=' + currentView)) {
                    btn.classList.add('active');
                }
            } else {
                if (btnHref.includes('view=executive') || btnHref.includes('view=list')) {
                    btn.classList.add('active');
                }
            }
        });
    }

    // ==========================================
    // 2. DELEGACIÓN DE EVENTOS (PESTAÑAS)
    // ==========================================
    document.addEventListener('click', function(e) {
        const tabBtn = e.target.closest('.tab-link, .tab-btn');

        if (tabBtn) {
            e.preventDefault();
            fetchTabContent(tabBtn.href);
        }
    });

    // ==========================================
    // 3. DELEGACIÓN DE EVENTOS (FILTROS)
    // ==========================================
    document.addEventListener('change', (e) => {
        const filterForm = e.target.closest('#filter-form');
        if (filterForm && (e.target.tagName === 'SELECT' || e.target.type === 'checkbox')) {
            submitFilters(filterForm);
        }
    });

    document.addEventListener('keypress', (e) => {
        const filterForm = e.target.closest('#filter-form');
        if (filterForm && e.key === 'Enter') {
            e.preventDefault();
            submitFilters(filterForm);
        }
    });

    function submitFilters(formElement) {
        const formData  = new FormData(formElement);
        const params    = new URLSearchParams(formData);
        const urlParams = new URL(window.location.href).searchParams;

        // Si la vista actual requiere conservar el view (como precaution)
        if (!params.has('view')) {
            let currentView = urlParams.get('view');
            if (currentView) {
                params.set('view', currentView);
            }
        }

        const url = formElement.action + '?' + params.toString();
        fetchTabContent(url);
    }

    // ==========================================
    // 4. HISTORIAL DEL NAVEGADOR
    // ==========================================
    let initialLoad = true;
    window.addEventListener('popstate', function() {
        if (initialLoad) {
            initialLoad = false;
            return;
        }
        fetchTabContent(window.location.href);
    });

    setTimeout(() => { initialLoad = false; }, 1000);

    // ==========================================
    // 5. DELEGACIÓN DE EVENTOS (DROPDOWN AÑOS)
    // ==========================================
    document.addEventListener('click', function(e) {
        const dropdownBtn  = e.target.closest('#year-dropdown-btn');
        const dropdownMenu = document.getElementById('year-dropdown-menu');

        // Toggle del menú si se clickea el botón
        if (dropdownBtn && dropdownMenu) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        }
        // Cerrar menú si se hace click fuera
        else if (dropdownMenu && !e.target.closest('#year-dropdown-menu')) {
            dropdownMenu.classList.remove('show');
        }
    });
});
