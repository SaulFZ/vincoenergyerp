<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil de Capacitación</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --dark-color: #1f2937;
            --light-color: #f3f4f6;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #334155;
        }

        /* Estilo para el modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 50;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 90%;
            width: 500px;
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }

        .modal-overlay.active .modal-content {
            transform: translateY(0);
        }

        /* Estilo personalizado para el círculo de progreso */
        .progress-circle {
            position: relative;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .progress-circle::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: conic-gradient(
                var(--progress-color, var(--primary-color)) 0%,
                var(--progress-color, var(--primary-color)) var(--progress-percent, 0%),
                #e5e7eb var(--progress-percent, 0%),
                #e5e7eb 100%
            );
        }

        .progress-circle span {
            z-index: 10;
            background-color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.875rem;
        }

        /* Estilo para los badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
        }

        .badge-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .badge-success {
            background-color: var(--success-color);
            color: white;
        }

        .badge-warning {
            background-color: var(--warning-color);
            color: white;
        }

        .badge-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .badge-info {
            background-color: var(--info-color);
            color: white;
        }

        .badge-gray {
            background-color: #e5e7eb;
            color: #4b5563;
        }

        /* Animaciones */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }

        /* Transiciones para elementos interactivos */
        .transition-all {
            transition-property: all;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }

        /* Estilo para los cards */
        .card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Estilo para los botones */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            font-size: 0.875rem;
            line-height: 1.25rem;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid #d1d5db;
            color: #374151;
        }

        .btn-outline:hover {
            background-color: #f3f4f6;
        }

        /* Estilo para los inputs */
        .input-field {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            border: 1px solid #d1d5db;
            font-size: 0.875rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .input-field:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* Estilo para el header */
        .profile-header {
            background: linear-gradient(135deg, #1e40af 0%, #3730a3 100%);
        }
    </style>
</head>
<body class="min-h-screen p-4">
    <div class="max-w-4xl mx-auto">
        <!-- Encabezado del Perfil -->
        <div class="profile-header rounded-xl shadow-lg overflow-hidden text-white mb-6">
            <div class="p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <!-- Información del Empleado -->
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <img class="w-16 h-16 rounded-full border-2 border-white object-cover shadow-md"
                             src="https://placehold.co/64x64/FFFFFF/000000?text=JP"
                             alt="Foto del Empleado">
                        <div class="absolute -bottom-1 -right-1 bg-green-500 rounded-full w-4 h-4 border-2 border-white"></div>
                    </div>
                    <div>
                        <p class="text-sm font-semibold opacity-90">MI VESCAP</p>
                        <h1 class="text-xl font-bold">Juan Pérez López</h1>
                        <p class="text-xs font-light opacity-80">Empleado: 89456</p>
                        <p class="text-xs font-normal opacity-70">Analista de Datos Senior</p>
                    </div>
                </div>

                <!-- Círculos de Progreso -->
                <div class="flex items-center gap-6">
                    <div class="flex flex-col items-center">
                        <div class="progress-circle" style="--progress-percent: 85%; --progress-color: var(--success-color);">
                            <span>85%</span>
                        </div>
                        <p class="mt-2 text-xs font-medium">General</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="progress-circle" style="--progress-percent: 95%; --progress-color: var(--success-color);">
                            <span>95%</span>
                        </div>
                        <p class="mt-2 text-xs font-medium">HSE</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="progress-circle" style="--progress-percent: 70%; --progress-color: var(--warning-color);">
                            <span>70%</span>
                        </div>
                        <p class="mt-2 text-xs font-medium">Técnico</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cuerpo principal de la aplicación -->
        <div class="space-y-6">
            <!-- Sección de Búsqueda y Filtros -->
            <div class="card p-4">
                <div id="search-header" class="flex items-center justify-between cursor-pointer">
                    <div class="flex items-center gap-2">
                        <div class="bg-blue-500 rounded-full text-white w-6 h-6 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h2 class="text-base font-semibold text-gray-800">Buscar Capacitaciones</h2>
                    </div>
                    <svg id="toggle-icon" class="w-5 h-5 text-gray-500 transform transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>

                <div id="search-body" class="max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                    <div class="mt-4 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="search-course" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                                <input type="text" id="search-course" class="input-field" placeholder="Buscar por nombre...">
                            </div>
                            <div>
                                <label for="search-client" class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                                <input type="text" id="search-client" class="input-field" placeholder="Filtrar por cliente...">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="filter-type" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                                <select id="filter-type" class="input-field">
                                    <option value="">Todos los tipos</option>
                                    <option value="HSE">HSE</option>
                                    <option value="Tecnica">Técnica</option>
                                    <option value="Externa">Externa</option>
                                </select>
                            </div>
                            <div>
                                <label for="filter-status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select id="filter-status" class="input-field">
                                    <option value="">Todos los estados</option>
                                    <option value="Vigente">Vigente</option>
                                    <option value="Por Vencer">Por Vencer</option>
                                    <option value="Vencido">Vencido</option>
                                    <option value="Pendiente">Pendiente</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="search-start-date" class="block text-sm font-medium text-gray-700 mb-1">Vigencia Desde</label>
                                <input type="date" id="search-start-date" class="input-field">
                            </div>
                            <div>
                                <label for="search-end-date" class="block text-sm font-medium text-gray-700 mb-1">Vigencia Hasta</label>
                                <input type="date" id="search-end-date" class="input-field">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button id="clear-filters-btn" class="btn btn-outline">
                            Limpiar
                        </button>
                        <button id="search-btn" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                            Buscar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sección de Documentos Personales -->
            <div class="card p-4">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">Documentos Personales</h2>
                    <button class="text-xs font-medium text-blue-600 hover:text-blue-800">
                        Ver todos
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <!-- Documento 1 -->
                    <div class="document-item p-3 border border-gray-200 rounded-lg hover:border-blue-200 transition-all"
                         data-document-name="Acta de Nacimiento" data-expiry-date="N/A">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <div class="bg-blue-100 p-1.5 rounded-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <h3 class="font-medium text-gray-900">Acta Nacimiento</h3>
                                </div>
                                <div class="mt-2 flex items-center gap-2 text-xs text-gray-600">
                                    <span>Vigencia:</span>
                                    <span class="date-display font-medium text-gray-400">N/A</span>
                                    <span class="document-status badge badge-gray">N/A</span>
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <button onclick="viewDocument('Acta de Nacimiento')" class="p-1.5 text-gray-500 hover:text-blue-600 rounded-md hover:bg-blue-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <button onclick="openUpdateModal(this.closest('.document-item'))" class="p-1.5 text-gray-500 hover:text-blue-600 rounded-md hover:bg-blue-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Documento 2 -->
                    <div class="document-item p-3 border border-gray-200 rounded-lg hover:border-blue-200 transition-all"
                         data-document-name="CURP" data-expiry-date="N/A">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <div class="bg-blue-100 p-1.5 rounded-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <h3 class="font-medium text-gray-900">CURP</h3>
                                </div>
                                <div class="mt-2 flex items-center gap-2 text-xs text-gray-600">
                                    <span>Vigencia:</span>
                                    <span class="date-display font-medium text-gray-400">N/A</span>
                                    <span class="document-status badge badge-gray">N/A</span>
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <button onclick="viewDocument('CURP')" class="p-1.5 text-gray-500 hover:text-blue-600 rounded-md hover:bg-blue-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <button onclick="openUpdateModal(this.closest('.document-item'))" class="p-1.5 text-gray-500 hover:text-blue-600 rounded-md hover:bg-blue-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Documento 3 -->
                    <div class="document-item p-3 border border-gray-200 rounded-lg hover:border-blue-200 transition-all"
                         data-document-name="INE" data-expiry-date="2025-05-30">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <div class="bg-blue-100 p-1.5 rounded-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <h3 class="font-medium text-gray-900">INE</h3>
                                </div>
                                <div class="mt-2 flex items-center gap-2 text-xs text-gray-600">
                                    <span>Vigencia:</span>
                                    <span class="date-display font-medium"></span>
                                    <span class="document-status badge badge-danger">Vencido</span>
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <button onclick="viewDocument('INE')" class="p-1.5 text-gray-400 rounded-md cursor-not-allowed" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <button onclick="openUpdateModal(this.closest('.document-item'))" class="p-1.5 text-gray-500 hover:text-blue-600 rounded-md hover:bg-blue-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Documento 4 -->
                    <div class="document-item p-3 border border-gray-200 rounded-lg hover:border-blue-200 transition-all"
                         data-document-name="Certificado de Estudios" data-expiry-date="2030-12-31">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <div class="bg-blue-100 p-1.5 rounded-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <h3 class="font-medium text-gray-900">Cert. Estudios</h3>
                                </div>
                                <div class="mt-2 flex items-center gap-2 text-xs text-gray-600">
                                    <span>Vigencia:</span>
                                    <span class="date-display font-medium"></span>
                                    <span class="document-status badge badge-success">Vigente</span>
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <button onclick="viewDocument('Certificado de Estudios')" class="p-1.5 text-gray-500 hover:text-blue-600 rounded-md hover:bg-blue-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <button onclick="openUpdateModal(this.closest('.document-item'))" class="p-1.5 text-gray-500 hover:text-blue-600 rounded-md hover:bg-blue-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de Capacitaciones -->
            <div class="space-y-6">
                <!-- Capacitaciones de HSE -->
                <div class="card p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <div class="bg-blue-100 p-1.5 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.707 1.707l-6 6a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L10 5.586V2a1 1 0 011.3-.954zM19 10a9 9 0 11-18 0 9 9 0 0118 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            Capacitaciones de HSE
                        </h3>
                        <button class="text-xs font-medium text-blue-600 hover:text-blue-800">
                            Ver todas
                        </button>
                    </div>

                    <div class="space-y-3">
                        <!-- Curso 1 -->
                        <div class="course-item p-3 border border-gray-200 rounded-lg hover:border-blue-200 transition-all animate-fade-in"
                             data-course-type="HSE" data-course-status="Pendiente" data-client-name="" data-expiry-date="">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="bg-blue-100 p-1.5 rounded-lg mt-0.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.707 1.707l-6 6a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L10 5.586V2a1 1 0 011.3-.954zM19 10a9 9 0 11-18 0 9 9 0 0118 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">Primeros Auxilios</h4>
                                        <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-600">
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                                </svg>
                                                Asignado: 15/07/25
                                            </span>
                                            <span class="badge badge-gray">Presencial</span>
                                        </div>
                                    </div>
                                </div>
                                <button onclick="accessCourse('Primeros Auxilios')" class="btn btn-primary text-sm whitespace-nowrap">
                                    Acceder
                                </button>
                            </div>
                        </div>

                        <!-- Curso 2 -->
                        <div class="course-item p-3 border border-gray-200 rounded-lg hover:border-blue-200 transition-all animate-fade-in"
                             data-course-type="HSE" data-course-status="Vigente" data-client-name="" data-expiry-date="2026-05-10">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="bg-blue-100 p-1.5 rounded-lg mt-0.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.707 1.707l-6 6a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L10 5.586V2a1 1 0 011.3-.954zM19 10a9 9 0 11-18 0 9 9 0 0118 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">Seguridad Trabajo</h4>
                                        <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-600">
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                                </svg>
                                                Realizado: 10/05/25
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                </svg>
                                                Vigencia: 10/05/26
                                            </span>
                                            <span class="badge badge-success">Vigente</span>
                                        </div>
                                    </div>
                                </div>
                                <button onclick="accessCourse('Seguridad Trabajo')" class="btn btn-primary text-sm whitespace-nowrap">
                                    Acceder
                                </button>
                            </div>
                        </div>

                        <!-- Curso 3 -->
                        <div class="course-item p-3 border border-gray-200 rounded-lg hover:border-blue-200 transition-all animate-fade-in"
                             data-course-type="HSE" data-course-status="Por Vencer" data-client-name="" data-expiry-date="2025-07-20">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="bg-blue-100 p-1.5 rounded-lg mt-0.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.707 1.707l-6 6a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L10 5.586V2a1 1 0 011.3-.954zM19 10a9 9 0 11-18 0 9 9 0 0118 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">Manejo de Extintores</h4>
                                        <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-600">
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                                </svg>
                                                Realizado: 20/07/24
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                </svg>
                                                Vigencia: 20/07/25
                                            </span>
                                            <span class="badge badge-warning">Por Vencer</span>
                                        </div>
                                    </div>
                                </div>
                                <button onclick="accessCourse('Manejo de Extintores')" class="btn btn-primary text-sm whitespace-nowrap">
                                    Acceder
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Capacitaciones Técnicas -->
                <div class="card p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <div class="bg-orange-100 p-1.5 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 2a8 8 0 100 16 8 8 0 000-16zM5 10a5 5 0 1110 0 5 5 0 01-10 0z" />
                                </svg>
                            </div>
                            Capacitaciones Técnicas
                        </h3>
                        <button class="text-xs font-medium text-blue-600 hover:text-blue-800">
                            Ver todas
                        </button>
                    </div>

                    <div class="space-y-3">
                        <!-- Curso 1 -->
                        <div class="course-item p-3 border border-gray-200 rounded-lg hover:border-blue-200 transition-all animate-fade-in"
                             data-course-type="Tecnica" data-course-status="Vencido" data-client-name="" data-expiry-date="2024-03-15">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="bg-orange-100 p-1.5 rounded-lg mt-0.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 2a8 8 0 100 16 8 8 0 000-16zM5 10a5 5 0 1110 0 5 5 0 01-10 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">Python para Análisis de Datos</h4>
                                        <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-600">
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                                </svg>
                                                Realizado: 15/03/24
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                </svg>
                                                Vigencia: 15/03/25
                                            </span>
                                            <span class="badge badge-danger">Vencido</span>
                                        </div>
                                    </div>
                                </div>
                                <button onclick="accessCourse('Python para Análisis de Datos')" class="btn btn-primary text-sm whitespace-nowrap">
                                    Acceder
                                </button>
                            </div>
                        </div>

                        <!-- Curso 2 -->
                        <div class="course-item p-3 border border-gray-200 rounded-lg hover:border-blue-200 transition-all animate-fade-in"
                             data-course-type="Tecnica" data-course-status="Vigente" data-client-name="" data-expiry-date="2026-11-20">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="bg-orange-100 p-1.5 rounded-lg mt-0.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 2a8 8 0 100 16 8 8 0 000-16zM5 10a5 5 0 1110 0 5 5 0 01-10 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">Certificación en AWS</h4>
                                        <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-600">
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                                </svg>
                                                Realizado: 20/11/24
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                </svg>
                                                Vigencia: 20/11/26
                                            </span>
                                            <span class="badge badge-success">Vigente</span>
                                        </div>
                                    </div>
                                </div>
                                <button onclick="accessCourse('Certificación en AWS')" class="btn btn-primary text-sm whitespace-nowrap">
                                    Acceder
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Capacitaciones Externas -->
                <div class="card p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <div class="bg-indigo-100 p-1.5 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-7H5a1 1 0 010-2h4V5a1 1 0 112 0v4h4a1 1 0 110 2h-4v4a1 1 0 11-2 0v-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            Capacitaciones Externas
                        </h3>
                        <button class="text-xs font-medium text-blue-600 hover:text-blue-800">
                            Ver todas
                        </button>
                    </div>

                    <div class="space-y-3">
                        <!-- Curso 1 -->
                        <div class="course-item p-3 border border-gray-200 rounded-lg hover:border-blue-200 transition-all animate-fade-in"
                             data-course-type="Externa" data-course-status="Vigente" data-client-name="Cliente A" data-expiry-date="2025-10-01">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="bg-indigo-100 p-1.5 rounded-lg mt-0.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-7H5a1 1 0 010-2h4V5a1 1 0 112 0v4h4a1 1 0 110 2h-4v4a1 1 0 11-2 0v-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">Protocolo de Seguridad del Cliente A</h4>
                                        <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-600">
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                                </svg>
                                                Realizado: 01/10/24
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                </svg>
                                                Vigencia: 01/10/25
                                            </span>
                                            <span class="badge badge-success">Vigente</span>
                                            <span class="badge badge-info">Cliente A</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para subir/actualizar documento -->
    <div id="update-modal" class="modal-overlay">
        <div class="modal-content">
            <button class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 transition-colors" onclick="closeUpdateModal()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Actualizar Documento</h3>
            <p class="text-gray-600 mb-6">Completa los siguientes datos para subir un nuevo documento o actualizar el actual.</p>

            <div class="space-y-4">
                <div>
                    <label for="modal-document-name" class="block text-sm font-medium text-gray-700 mb-1">Nombre del Documento</label>
                    <input type="text" id="modal-document-name" readonly class="input-field bg-gray-50">
                </div>
                <div>
                    <label for="modal-expiry-date" class="block text-sm font-medium text-gray-700 mb-1">Fecha de Vigencia</label>
                    <input type="date" id="modal-expiry-date" class="input-field">
                </div>
                <div>
                    <label for="modal-document-file" class="block text-sm font-medium text-gray-700 mb-1">Subir Archivo</label>
                    <div class="mt-1 flex items-center gap-3">
                        <label for="modal-document-file" class="cursor-pointer">
                            <span class="btn btn-outline flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                Seleccionar archivo
                            </span>
                            <input type="file" id="modal-document-file" class="hidden">
                        </label>
                        <span id="file-name" class="text-sm text-gray-500 truncate max-w-xs">Ningún archivo seleccionado</span>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <button onclick="closeUpdateModal()" class="btn btn-outline">
                    Cancelar
                </button>
                <button onclick="saveDocument()" class="btn btn-primary flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Guardar Cambios
                </button>
            </div>
        </div>
    </div>

    <!-- Notificación Toast -->
    <div id="toast" class="fixed bottom-4 right-4 max-w-xs w-full bg-white shadow-lg rounded-lg overflow-hidden hidden">
        <div class="p-4">
            <div class="flex items-start">
                <div id="toast-icon" class="flex-shrink-0"></div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p id="toast-title" class="text-sm font-medium text-gray-900"></p>
                    <p id="toast-message" class="mt-1 text-sm text-gray-500"></p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button onclick="hideToast()" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <div id="toast-progress" class="h-1 bg-blue-500"></div>
    </div>

    <script>
        // Variables globales
        let currentDocumentItem = null;

        // Inicialización al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar eventos
            setupEventListeners();

            // Actualizar estados de documentos
            updateDocumentStatuses();

            // Mostrar fecha actual en los campos de fecha
            setCurrentDateInInputs();

            // Configurar el selector de archivos
            setupFileInput();
        });

        // Configurar eventos
        function setupEventListeners() {
            // Toggle de la sección de búsqueda
            document.getElementById('search-header').addEventListener('click', toggleSearchSection);

            // Botón de limpiar filtros
            document.getElementById('clear-filters-btn').addEventListener('click', clearFilters);

            // Botón de búsqueda
            document.getElementById('search-btn').addEventListener('click', applyFilters);
        }

        // Alternar visibilidad de la sección de búsqueda
        function toggleSearchSection() {
            const searchBody = document.getElementById('search-body');
            const toggleIcon = document.getElementById('toggle-icon');

            searchBody.classList.toggle('max-h-0');
            searchBody.classList.toggle('max-h-[500px]');
            toggleIcon.classList.toggle('rotate-180');
        }

        // Limpiar todos los filtros
        function clearFilters() {
            document.getElementById('search-course').value = '';
            document.getElementById('search-client').value = '';
            document.getElementById('filter-type').value = '';
            document.getElementById('filter-status').value = '';
            document.getElementById('search-start-date').value = '';
            document.getElementById('search-end-date').value = '';

            // También podrías volver a aplicar los filtros para mostrar todos los elementos
            applyFilters();
        }

        // Aplicar filtros a los cursos
        function applyFilters() {
            const courseName = document.getElementById('search-course').value.toLowerCase();
            const clientName = document.getElementById('search-client').value.toLowerCase();
            const typeFilter = document.getElementById('filter-type').value;
            const statusFilter = document.getElementById('filter-status').value;
            const startDate = document.getElementById('search-start-date').value;
            const endDate = document.getElementById('search-end-date').value;

            const courseItems = document.querySelectorAll('.course-item');

            courseItems.forEach(item => {
                const courseTitle = item.querySelector('h4').textContent.toLowerCase();
                const courseType = item.getAttribute('data-course-type');
                const courseStatus = item.getAttribute('data-course-status');
                const courseClient = item.getAttribute('data-client-name')?.toLowerCase() || '';
                const expiryDate = item.getAttribute('data-expiry-date');

                // Aplicar filtros
                const nameMatch = courseTitle.includes(courseName);
                const clientMatch = courseClient.includes(clientName);
                const typeMatch = !typeFilter || courseType === typeFilter;
                const statusMatch = !statusFilter || courseStatus === statusFilter;
                const dateMatch = !startDate || !endDate ||
                                 (expiryDate >= startDate && expiryDate <= endDate);

                // Mostrar/ocultar según coincidan los filtros
                if (nameMatch && clientMatch && typeMatch && statusMatch && dateMatch) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });

            // Mostrar notificación
            showToast('success', 'Filtros aplicados', 'Se han aplicado los filtros de búsqueda.');
        }

        // Abrir modal para actualizar documento
        function openUpdateModal(documentItem) {
            currentDocumentItem = documentItem;
            const modal = document.getElementById('update-modal');
            const docName = documentItem.getAttribute('data-document-name');
            const expiryDate = documentItem.getAttribute('data-expiry-date');

            document.getElementById('modal-document-name').value = docName;
            document.getElementById('modal-expiry-date').value = expiryDate !== 'N/A' ? expiryDate : '';
            document.getElementById('file-name').textContent = 'Ningún archivo seleccionado';

            modal.classList.add('active');
        }

        // Cerrar modal
        function closeUpdateModal() {
            document.getElementById('update-modal').classList.remove('active');
            currentDocumentItem = null;
        }

        // Guardar documento (simulado)
        function saveDocument() {
            const docName = document.getElementById('modal-document-name').value;
            const expiryDate = document.getElementById('modal-expiry-date').value;
            const fileInput = document.getElementById('modal-document-file');

            // Validaciones básicas
            if (!expiryDate) {
                showToast('error', 'Error', 'Debes especificar una fecha de vigencia.');
                return;
            }

            if (fileInput.files.length === 0) {
                showToast('error', 'Error', 'Debes seleccionar un archivo.');
                return;
            }

            // Simular guardado (en una aplicación real, aquí harías una petición AJAX)
            setTimeout(() => {
                // Actualizar el elemento del documento en la UI
                if (currentDocumentItem) {
                    currentDocumentItem.setAttribute('data-expiry-date', expiryDate);

                    // Actualizar la fecha mostrada
                    const dateDisplay = currentDocumentItem.querySelector('.date-display');
                    dateDisplay.textContent = formatDate(expiryDate);
                    dateDisplay.classList.remove('text-gray-400');

                    // Actualizar el estado
                    updateDocumentStatus(currentDocumentItem);
                }

                // Cerrar modal y mostrar notificación
                closeUpdateModal();
                showToast('success', 'Documento guardado', 'El documento se ha actualizado correctamente.');
            }, 1000);
        }

        // Ver documento (simulado)
        function viewDocument(docName) {
            showToast('info', 'Visualización de documento', `Abriendo documento: ${docName}`);
            // En una aplicación real, aquí abrirías el documento o lo descargarías
        }

        // Acceder a curso (simulado)
        function accessCourse(courseName) {
            showToast('info', 'Acceso a curso', `Redirigiendo al curso: ${courseName}`);
            // En una aplicación real, aquí redirigirías al curso correspondiente
        }

        // Configurar el input de archivo para mostrar el nombre del archivo seleccionado
        function setupFileInput() {
            const fileInput = document.getElementById('modal-document-file');
            const fileNameSpan = document.getElementById('file-name');

            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    fileNameSpan.textContent = this.files[0].name;
                } else {
                    fileNameSpan.textContent = 'Ningún archivo seleccionado';
                }
            });
        }

        // Actualizar estados de todos los documentos
        function updateDocumentStatuses() {
            const documentItems = document.querySelectorAll('.document-item');
            documentItems.forEach(item => updateDocumentStatus(item));
        }

        // Actualizar el estado de un documento individual
        function updateDocumentStatus(documentItem) {
            const expiryDateStr = documentItem.getAttribute('data-expiry-date');
            const dateDisplay = documentItem.querySelector('.date-display');
            const statusSpan = documentItem.querySelector('.document-status');
            const viewButton = documentItem.querySelector('button[onclick*="viewDocument"]');

            // Si no hay fecha de vigencia
            if (!expiryDateStr || expiryDateStr === 'N/A') {
                dateDisplay.textContent = 'N/A';
                dateDisplay.classList.add('text-gray-400');
                statusSpan.textContent = 'N/A';
                statusSpan.className = 'document-status badge badge-gray';
                return;
            }

            // Formatear fecha para mostrar
            dateDisplay.textContent = formatDate(expiryDateStr);
            dateDisplay.classList.remove('text-gray-400');

            // Calcular días restantes
            const today = new Date();
            const expiryDate = new Date(expiryDateStr);
            const diffTime = expiryDate - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            // Determinar estado
            if (diffDays <= 0) {
                statusSpan.textContent = 'Vencido';
                statusSpan.className = 'document-status badge badge-danger';

                // Deshabilitar botón de ver si está vencido
                if (viewButton) {
                    viewButton.disabled = true;
                    viewButton.classList.add('text-gray-400', 'cursor-not-allowed');
                    viewButton.classList.remove('hover:text-blue-600', 'hover:bg-blue-50');
                }
            } else if (diffDays <= 30) {
                statusSpan.textContent = 'Por Vencer';
                statusSpan.className = 'document-status badge badge-warning';

                // Habilitar botón de ver
                if (viewButton) {
                    viewButton.disabled = false;
                    viewButton.classList.remove('text-gray-400', 'cursor-not-allowed');
                    viewButton.classList.add('hover:text-blue-600', 'hover:bg-blue-50');
                }
            } else {
                statusSpan.textContent = 'Vigente';
                statusSpan.className = 'document-status badge badge-success';

                // Habilitar botón de ver
                if (viewButton) {
                    viewButton.disabled = false;
                    viewButton.classList.remove('text-gray-400', 'cursor-not-allowed');
                    viewButton.classList.add('hover:text-blue-600', 'hover:bg-blue-50');
                }
            }
        }

        // Formatear fecha a DD/MM/AAAA
        function formatDate(dateStr) {
            if (!dateStr || dateStr === 'N/A') return 'N/A';

            const date = new Date(dateStr);
            if (isNaN(date.getTime())) return 'N/A';

            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();

            return `${day}/${month}/${year}`;
        }

        // Establecer fecha actual en los campos de fecha
        function setCurrentDateInInputs() {
            const today = new Date();
            const formattedDate = today.toISOString().split('T')[0];

            // Establecer fecha actual en el campo de fecha de vigencia del modal
            document.getElementById('modal-expiry-date').min = formattedDate;

            // Establecer fecha actual en los filtros de fecha
            document.getElementById('search-start-date').max = formattedDate;
            document.getElementById('search-end-date').min = formattedDate;
        }

        // Mostrar notificación toast
        function showToast(type, title, message) {
            const toast = document.getElementById('toast');
            const toastIcon = document.getElementById('toast-icon');
            const toastTitle = document.getElementById('toast-title');
            const toastMessage = document.getElementById('toast-message');
            const toastProgress = document.getElementById('toast-progress');

            // Configurar según el tipo
            let iconHtml = '';
            let progressColor = '';

            switch (type) {
                case 'success':
                    iconHtml = `
                        <div class="h-6 w-6 rounded-full bg-green-100 flex items-center justify-center">
                            <svg class="h-4 w-4 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    `;
                    progressColor = 'bg-green-500';
                    break;
                case 'error':
                    iconHtml = `
                        <div class="h-6 w-6 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="h-4 w-4 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    `;
                    progressColor = 'bg-red-500';
                    break;
                case 'info':
                    iconHtml = `
                        <div class="h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center">
                            <svg class="h-4 w-4 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    `;
                    progressColor = 'bg-blue-500';
                    break;
                default:
                    iconHtml = '';
                    progressColor = 'bg-gray-500';
            }

            // Configurar contenido
            toastIcon.innerHTML = iconHtml;
            toastTitle.textContent = title;
            toastMessage.textContent = message;
            toastProgress.className = `h-1 ${progressColor}`;

            // Mostrar toast
            toast.classList.remove('hidden');

            // Animación de la barra de progreso
            toastProgress.style.width = '100%';
            toastProgress.style.transition = 'none';
            setTimeout(() => {
                toastProgress.style.transition = 'width 4.5s linear';
                toastProgress.style.width = '0%';
            }, 50);

            // Ocultar después de 5 segundos
            setTimeout(hideToast, 5000);
        }

        // Ocultar notificación toast
        function hideToast() {
            document.getElementById('toast').classList.add('hidden');
        }
    </script>
</body>
</html>
