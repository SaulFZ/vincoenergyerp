@extends('modules.operations.sgc-ves.index')

@section('content')

<!-- ========================================== -->
<!-- 1. SECCIÓN DE FILTROS AUTOMÁTICOS -->
<!-- ========================================== -->
<section class="filter-section no-print">
    <form id="filter-form" action="{{ route('operations.sgc-ves.index') }}" method="GET">
        <div class="filter-grid">

            <div class="filter-group">
                <label class="filter-label">Módulo</label>
                <select name="view" class="filter-input">
                    <!-- Valores en inglés -->
                    <option value="executive" {{ $view === 'executive' ? 'selected' : '' }}>Dashboard Ejecutivo</option>
                    <option value="npt" {{ $view === 'npt' ? 'selected' : '' }}>NPT / No Productivo</option>
                    <option value="compliance" {{ $view === 'compliance' ? 'selected' : '' }}>Cumplimiento SGC</option>
                    <option value="crew" {{ $view === 'crew' ? 'selected' : '' }}>Cuadrilla + Satisfacción</option>
                    <option value="shots" {{ $view === 'shots' ? 'selected' : '' }}>Disparos / MCR</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Compañía</label>
                <select name="client" class="filter-input">
                    <option value="TODOS" selected>Todas las compañías</option>
                    <option value="PEMEX">PEMEX</option>
                    <option value="SLB">SLB</option>
                    <option value="HALLIBURTON">HALLIBURTON</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Servicio</label>
                <select name="serviceType" class="filter-input">
                    <option value="TODOS" selected>Todos los tipos</option>
                    <option value="Registros Geofísicos">Registros Geofísicos</option>
                    <option value="Disparos de Producción">Disparos de Producción</option>
                </select>
            </div>

            <div class="filter-group custom-dropdown">
                <label class="filter-label">Años</label>
                <div class="filter-input dropdown-trigger" id="year-dropdown-btn">
                    <span>Seleccionar años</span>
                    <i class="fas fa-chevron-down text-slate-400"></i>
                </div>
                <div class="dropdown-menu" id="year-dropdown-menu">
                    <label class="dropdown-item"><input type="checkbox" name="year[]" value="2026" checked> 2026</label>
                    <label class="dropdown-item"><input type="checkbox" name="year[]" value="2025"> 2025</label>
                    <label class="dropdown-item"><input type="checkbox" name="year[]" value="2024"> 2024</label>
                </div>
            </div>

            <div class="filter-group">
                <label class="filter-label">Periodo</label>
                <select name="period" class="filter-input">
                    <option value="ANUAL" selected>Anual</option>
                    <option value="Q1">Q1 · Ene-Mar</option>
                    <option value="Q2">Q2 · Abr-Jun</option>
                    <option value="Q3">Q3 · Jul-Sep</option>
                    <option value="Q4">Q4 · Oct-Dic</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Mes comp.</label>
                <select name="compareMonth" class="filter-input">
                    <option value="TODOS" selected>Todos los meses</option>
                    <option value="ACTUAL">Mes actual vs años</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Tipo NPT</label>
                <select name="eventType" class="filter-input">
                    <option value="TODOS" selected>Todos</option>
                    <option value="Falla de herramienta">Falla de herramienta</option>
                    <option value="Falla de cable">Falla de cable</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Cálculo SQIR</label>
                <select name="sqirMode" class="filter-input">
                    <option value="os" selected>Por trabajos</option>
                    <option value="horas">Por horas</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label" style="opacity: 0;">Acciones</label>
                <div class="filter-actions">
                    <button type="button" onclick="window.print()" class="btn-icon btn-pdf" title="Exportar a PDF">
                        <i class="fas fa-file-pdf"></i> &nbsp;PDF
                    </button>
                    <a href="{{ route('operations.sgc-ves.index') }}" class="btn-icon btn-clear" title="Limpiar filtros">
                        <i class="fas fa-undo-alt"></i>
                    </a>
                </div>
            </div>

        </div>
    </form>
</section>

<!-- ========================================== -->
<!-- 2. CONTENEDOR DEL DASHBOARD COMPLETO -->
<!-- ========================================== -->
<div class="dashboard-wrapper">

    <!-- PESTAÑAS (TABS) INTEGRADAS -->
    <div class="dashboard-tabs no-print">
        <span class="tab-title">Dashboard:</span>
        <a href="{{ route('operations.sgc-ves.index', ['view' => 'executive']) }}" class="tab-btn {{ $view === 'executive' ? 'active' : '' }}">General</a>
        <a href="{{ route('operations.sgc-ves.index', ['view' => 'npt']) }}" class="tab-btn {{ $view === 'npt' ? 'active' : '' }}">NPT</a>
        <a href="{{ route('operations.sgc-ves.index', ['view' => 'compliance']) }}" class="tab-btn {{ $view === 'compliance' ? 'active' : '' }}">Cumplimiento SGC</a>
        <a href="{{ route('operations.sgc-ves.index', ['view' => 'crew']) }}" class="tab-btn {{ $view === 'crew' ? 'active' : '' }}">Cuadrilla + satisfacción</a>
        <a href="{{ route('operations.sgc-ves.index', ['view' => 'shots']) }}" class="tab-btn {{ $view === 'shots' ? 'active' : '' }}">Disparos / MCR</a>
    </div>

    <!-- CUERPO PRINCIPAL (Inyección dinámica) -->
    <div class="dashboard-body">

        @if ($view === 'npt')
            @include('modules.operations.sgc-ves.tabs.npt')

        @elseif ($view === 'compliance')
            @include('modules.operations.sgc-ves.tabs.compliance')

        @elseif ($view === 'crew')
            @include('modules.operations.sgc-ves.tabs.crew')

        @elseif ($view === 'shots')
            @include('modules.operations.sgc-ves.tabs.shots')

        @else
            <!-- Por defecto inyecta la vista general -->
            @include('modules.operations.sgc-ves.tabs.executive')

        @endif


    </div>
</div>

@endsection

@push('scripts')
<script>
    // ----------------------------------------------------
    // 1. Lógica del Dropdown de Años
    // ----------------------------------------------------
    const dropdownBtn = document.getElementById('year-dropdown-btn');
    const dropdownMenu = document.getElementById('year-dropdown-menu');

    dropdownBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdownMenu.classList.toggle('show');
    });

    document.addEventListener('click', function(e) {
        if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.remove('show');
        }
    });


    // ----------------------------------------------------
    // 2. LA MAGIA: Carga dinámica sin recargar la página
    // ----------------------------------------------------
    const dashboardBody = document.querySelector('.dashboard-body');
    const filterForm = document.getElementById('filter-form');

    // Función principal que hace la petición al servidor
    function fetchDashboardContent(url) {
        // Ponemos un efecto visual de "Cargando..."
        dashboardBody.style.opacity = '0.4';
        dashboardBody.style.pointerEvents = 'none';

        fetch(url, {
            headers: {
                // Este header le avisa a Laravel que es AJAX ($request->ajax() será true)
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            // 1. Inyectar el nuevo HTML dentro del body
            dashboardBody.innerHTML = html;

            // 2. Restaurar opacidad
            dashboardBody.style.opacity = '1';
            dashboardBody.style.pointerEvents = 'auto';

            // 3. Cambiar la URL en el navegador sin recargar (para que sirva copiar/pegar el link)
            window.history.pushState({}, '', url);

            // 4. Actualizar colores de los botones de pestañas
            updateActiveTabColors(url);
        })
        .catch(error => {
            // Si algo falla por red, hacemos recarga tradicional
            window.location.href = url;
        });
    }

    // Función para cambiar la clase "active" en el menú de pestañas
    function updateActiveTabColors(url) {
        const urlParams = new URL(url, window.location.origin).searchParams;
        const currentView = urlParams.get('view') || 'executive';

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.getAttribute('href').includes('view=' + currentView)) {
                btn.classList.add('active');
            }
        });

        // También sincronizamos el select "Módulo" del formulario para que coincida
        const viewSelect = filterForm.querySelector('select[name="view"]');
        if (viewSelect) viewSelect.value = currentView;
    }

    // A) Atapar clics en las Pestañas (Tabs)
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault(); // Evitamos que salte de página
            fetchDashboardContent(this.href);
        });
    });

    // B) Atapar cambios en los Filtros (Selects y Checkboxes)
    filterForm.querySelectorAll('select, input[type="checkbox"]').forEach(el => {
        el.addEventListener('change', () => {
            // Construimos la URL completa con todos los parámetros elegidos
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);
            const url = filterForm.action + '?' + params.toString();

            fetchDashboardContent(url);
        });
    });

    // C) Atapar el botón de "Limpiar filtros"
    const btnClear = document.querySelector('.btn-clear');
    if(btnClear) {
        btnClear.addEventListener('click', function(e) {
            e.preventDefault();
            // Resetear el formulario visualmente
            filterForm.reset();
            // Hacer la petición a la ruta base limpia
            fetchDashboardContent(this.href);
        });
    }

    // Manejo de los botones atrás/adelante del navegador para que no se rompa la vista
    window.addEventListener('popstate', function() {
        fetchDashboardContent(window.location.href);
    });
</script>
@endpush
