<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista de Gerente - Capacitación de Equipo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Incluir Chart.js para las gráficas -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Usamos la nueva fuente 'Poppins' */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f4f6; /* Fondo gris claro */
        }
        /* Estilo para los modales */
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
        }
        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 90%;
            width: 600px;
        }
        .text-xxs {
            font-size: 0.65rem;
        }
        /* Estilo para la barra de progreso de "cuadritos" */
        .progress-squares-container {
            display: flex;
            gap: 2px;
            width: 100%;
            height: 12px;
            padding: 2px;
            border-radius: 4px;
            background-color: #e5e7eb;
        }
        .progress-square {
            flex-grow: 1; /* Permite que los cuadritos se expandan para llenar el espacio */
            height: 8px;
            border-radius: 2px;
            background-color: #e5e7eb;
        }
        /* Colores dinámicos para la barra de progreso */
        .progress-square.filled.red {
            background-color: #ef4444; /* Tailwind red-500 */
        }
        .progress-square.filled.orange {
            background-color: #f97316; /* Tailwind orange-500 */
        }
        .progress-square.filled.yellow {
            background-color: #f59e0b; /* Tailwind yellow-500 */
        }
        .progress-square.filled.green {
            background-color: #10b981; /* Tailwind green-500 */
        }
        /* Estilo para la lista de selección múltiple de cursos y áreas */
        .multi-select-dropdown {
            position: relative;
        }
        .multi-select-list {
            position: absolute;
            z-index: 10;
            width: 100%;
            margin-top: 0.25rem;
            background-color: white;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            max-height: 150px;
            overflow-y: auto;
        }
        .multi-select-list .checkbox-item {
            padding: 0.5rem 0.75rem;
        }
        .multi-select-list .checkbox-item:hover {
            background-color: #f3f4f6;
        }
        .multi-select-list input[type="checkbox"] {
            margin-right: 0.5rem;
        }
        /* Estilo para las sugerencias del campo de búsqueda de curso */
        .course-suggestion-item {
            padding: 0.5rem 0.75rem;
            cursor: pointer;
        }
        .course-suggestion-item:hover {
            background-color: #f3f4f6;
        }
        /* Estilo para el contenedor de la lista de empleados */
        .employee-list-container-wrapper {
            position: relative;
        }
        .employee-list-dropdown {
            position: absolute;
            z-index: 10;
            width: 100%;
            margin-top: 0.25rem;
            background-color: white;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2 px 4px -1px rgba(0, 0, 0, 0.06);
            max-height: 150px;
            overflow-y: auto;
        }
        .employee-list-dropdown .checkbox-item {
            padding: 0.5rem 0.75rem;
        }
        .employee-list-dropdown .checkbox-item:hover {
            background-color: #f3f4f6;
        }
        .employee-list-dropdown input[type="checkbox"] {
            margin-right: 0.5rem;
        }
        .icon-button {
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }
    </style>
</head>
<body class="p-2 sm:p-4">
    <div class="max-w-full mx-auto bg-white rounded-xl shadow-md overflow-hidden">
        <!-- Encabezado del Dashboard de Gerente -->
        <div class="bg-gradient-to-br from-blue-800 to-indigo-600 p-4 text-white relative">
            <h1 class="text-xl sm:text-2xl font-bold">Vista de Gerente</h1>
            <p class="text-sm font-light opacity-80">Dashboard de Capacitación de Equipo</p>
        </div>

        <!-- Línea delgada naranja debajo del encabezado -->
        <div class="w-full h-1 bg-orange-500"></div>

        <!-- Contenido de Bienvenida (se muestra por defecto) -->
        <div id="welcome-pane" class="tab-pane p-4 text-center bg-white rounded-md shadow-inner">
            <h2 class="text-xl font-bold text-gray-800 mb-2">Bienvenido al Dashboard de Capacitación</h2>
        </div>

        <!-- Indicadores de Alto Nivel -->
        <div class="p-3 bg-white grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3">
            <!-- Indicador 1: Total de Empleados -->
            <div class="bg-white rounded-lg p-3 text-center shadow-sm flex flex-col items-center">
                <div class="bg-blue-100 p-3 rounded-full mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users h-8 w-8 text-blue-600">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-700">Total de Empleados</p>
                <p class="text-3xl font-bold text-blue-900 mt-1">15</p>
            </div>
            <!-- Indicador 2: Horas de Capacitación Presencial -->
            <div class="bg-white rounded-lg p-3 text-center shadow-sm flex flex-col items-center">
                <div class="bg-orange-100 p-3 rounded-full mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar-check h-8 w-8 text-orange-600">
                        <path d="M8 2v4"/>
                        <path d="M16 2v4"/>
                        <rect width="18" height="18" x="3" y="4" rx="2"/>
                        <path d="M3 10h18"/>
                        <path d="m9 16 2 2 4-4"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-700">Horas de Capacitación Presencial</p>
                <p class="text-3xl font-bold text-orange-900 mt-1">200</p>
            </div>
            <!-- Indicador 3: Horas de Capacitación en Línea -->
            <div class="bg-white rounded-lg p-3 text-center shadow-sm flex flex-col items-center">
                <div class="bg-purple-100 p-3 rounded-full mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-monitor h-8 w-8 text-purple-600">
                        <rect width="20" height="14" x="2" y="3" rx="2"/>
                        <path d="M12 17v4"/>
                        <path d="M8 21h8"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-700">Horas de Capacitación en Línea</p>
                <p class="text-3xl font-bold text-purple-900 mt-1">120</p>
            </div>
            <!-- Indicador 4: Capacitación HSE -->
            <div class="bg-white rounded-lg p-3 text-center shadow-sm flex flex-col items-center">
                <div class="bg-rose-100 p-3 rounded-full mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-check h-8 w-8 text-rose-600">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="m9 12 2 2 4-4"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-700">Capacitación HSE</p>
                <p class="text-3xl font-bold text-rose-900 mt-1">92%</p>
            </div>
            <!-- Indicador 5: Capacitación Técnica -->
            <div class="bg-white rounded-lg p-3 text-center shadow-sm flex flex-col items-center">
                <div class="bg-indigo-100 p-3 rounded-full mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-cpu h-8 w-8 text-indigo-600">
                        <rect x="4" y="4" width="16" height="16" rx="2"/>
                        <rect x="9" y="9" width="6" height="6" rx="1"/>
                        <path d="M15 2v2"/>
                        <path d="M15 20v2"/>
                        <path d="M2 15h2"/>
                        <path d="M20 15h2"/>
                        <path d="M2 9h2"/>
                        <path d="M20 9h2"/>
                        <path d="M9 2v2"/>
                        <path d="M9 20v2"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-700">Capacitación Técnica</p>
                <p class="text-3xl font-bold text-indigo-900 mt-1">85%</p>
            </div>
            <!-- Nueva Tarjeta: Conciencia de Alertas (HSE/Calidad) -->
            <div class="bg-white rounded-lg p-3 text-center shadow-sm flex flex-col items-center">
                <div class="bg-yellow-100 p-3 rounded-full mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell h-8 w-8 text-yellow-600">
                        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                        <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-700">Conciencia de Alertas (HSE/Calidad)</p>
                <p class="text-3xl font-bold text-yellow-900 mt-1">95%</p>
            </div>
        </div>

        <!-- Contenedor Principal con Tabs de Gestión -->
        <div class="p-2 sm:p-4">
            <!-- Mensaje de instrucción -->
            <p class="text-gray-600 text-center mb-4">Selecciona una de las pestañas para comenzar a gestionar los cursos de tu equipo.</p>

            <!-- Menú de Pestañas con Iconos -->
            <div class="flex border-b border-gray-200 mb-4">
                <!-- NUEVA Pestaña: Crear o Editar -->
                <button class="tab-button flex flex-col items-center p-2 text-sm sm:text-base font-semibold text-gray-600 border-b-2 border-transparent transition-colors duration-200 hover:text-blue-600 focus:outline-none focus:text-blue-600 focus:border-blue-600 group" data-tab="create-edit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil inline-block mb-1 h-10 w-10 text-blue-400 group-hover:text-blue-600 group-focus:text-blue-600">
                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                        <path d="M15 5l4 4"/>
                    </svg>
                    Crear o Editar
                </button>
                <button class="tab-button flex flex-col items-center p-2 text-sm sm:text-base font-semibold text-gray-600 border-b-2 border-transparent transition-colors duration-200 hover:text-blue-600 focus:outline-none focus:text-blue-600 focus:border-blue-600 group" data-tab="assign">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-plus inline-block mb-1 h-10 w-10 text-blue-400 group-hover:text-blue-600 group-focus:text-blue-600">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <line x1="19" x2="19" y1="8" y2="14"/>
                        <line x1="22" x2="22" y1="11" y2="11"/>
                    </svg>
                    Asignar Cursos
                </button>
                <button class="tab-button flex flex-col items-center p-2 text-sm sm:text-base font-semibold text-gray-600 border-b-2 border-transparent transition-colors duration-200 hover:text-blue-600 focus:outline-none focus:text-blue-600 focus:border-blue-600 group" data-tab="validate">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-square inline-block mb-1 h-10 w-10 text-blue-400 group-hover:text-blue-600 group-focus:text-blue-600">
                        <polyline points="9 11 12 14 22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    Validar Cursos
                </button>
                <button class="tab-button flex flex-col items-center p-2 text-sm sm:text-base font-semibold text-gray-600 border-b-2 border-transparent transition-colors duration-200 hover:text-blue-600 focus:outline-none focus:text-blue-600 focus:border-blue-600 group" data-tab="score">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bar-chart inline-block mb-1 h-10 w-10 text-blue-400 group-hover:text-blue-600 group-focus:text-blue-600">
                        <line x1="12" x2="12" y1="20" y2="10"/>
                        <line x1="18" x2="18" y1="20" y2="4"/>
                        <line x1="6" x2="6" y1="20" y2="16"/>
                    </svg>
                    Score por curso
                </button>
                <button class="tab-button flex flex-col items-center p-2 text-sm sm:text-base font-semibold text-gray-600 border-b-2 border-transparent transition-colors duration-200 hover:text-blue-600 focus:outline-none focus:text-blue-600 focus:border-blue-600 group" data-tab="matrix">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-network inline-block mb-1 h-10 w-10 text-blue-400 group-hover:text-blue-600 group-focus:text-blue-600">
                        <rect x="16" y="16" width="6" height="6" rx="1"/>
                        <rect x="2" y="16" width="6" height="6" rx="1"/>
                        <rect x="9" y="2" width="6" height="6" rx="1"/>
                        <path d="M12 8v4"/>
                        <path d="M12 12h-4"/>
                        <path d="M12 12h4"/>
                        <path d="M8 16v-4"/>
                        <path d="M16 16v-4"/>
                    </svg>
                    Matriz de capacitación
                </button>
            </div>

            <!-- Contenido de las Pestañas -->
            <div id="tab-content-panes">
                <!-- NUEVO Contenido de Crear o Editar -->
                <div id="create-edit" class="tab-pane p-2 hidden relative">
                    <!-- Iconos de navegación y filtros en la esquina superior izquierda -->
                    <div class="absolute top-4 left-4 flex space-x-2 z-10">
                        <button class="icon-button" onclick="goToHomePage()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-home h-8 w-8 text-gray-600 hover:text-blue-600">
                                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                        </button>
                        <button class="icon-button" onclick="clearSpecificTabFilters('create-edit')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-broom h-8 w-8 text-gray-600 hover:text-blue-600">
                                <path d="M9 10V6a3 3 0 0 1 3-3v0a3 3 0 0 1 3 3v4"/>
                                <path d="M12 10v12"/>
                                <path d="M18 15v-3a3 3 0 0 0-3-3H9a3 3 0 0 0-3 3v3a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2Z"/>
                            </svg>
                        </button>
                    </div>
                    <h2 class="text-lg font-bold text-gray-800 mb-2 mt-16">Crear o Editar Cursos</h2>
                    <div class="bg-gray-100 rounded-md p-4 shadow-inner">
                        <p class="text-gray-600">Aquí podrás crear nuevos cursos o editar los existentes. ¡Pronto tendremos más funcionalidades aquí!</p>
                        <!-- Puedes añadir formularios o tablas para la gestión de cursos aquí -->
                    </div>
                </div>

                <!-- Contenido de Asignar Cursos -->
                <div id="assign" class="tab-pane p-2 hidden relative">
                    <!-- Iconos de navegación y filtros en la esquina superior izquierda -->
                    <div class="absolute top-4 left-4 flex space-x-2 z-10">
                        <button class="icon-button" onclick="goToHomePage()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-home h-8 w-8 text-gray-600 hover:text-blue-600">
                                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                        </button>
                        <button class="icon-button" onclick="clearSpecificTabFilters('assign')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-broom h-8 w-8 text-gray-600 hover:text-blue-600">
                                <path d="M9 10V6a3 3 0 0 1 3-3v0a3 3 0 0 1 3 3v4"/>
                                <path d="M12 10v12"/>
                                <path d="M18 15v-3a3 3 0 0 0-3-3H9a3 3 0 0 0-3 3v3a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2Z"/>
                            </svg>
                        </button>
                    </div>
                    <h2 class="text-lg font-bold text-gray-800 mb-2 mt-16">Asignar Cursos a Empleados</h2>
                    <div class="bg-gray-100 rounded-md p-4 shadow-inner">

                        <!-- New grid container for course filters and selection -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- NEW: Course Category Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Filtrar Cursos por Categoría</label>
                                <div class="multi-select-dropdown">
                                    <button id="multi-select-toggle-course-categories" class="w-full text-left bg-white border border-gray-300 rounded-md p-2 mt-1 text-gray-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        <span id="selected-course-categories-text">Seleccionar Categorías</span>
                                        <svg class="h-5 w-5 text-gray-400 inline-block float-right" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div id="multi-select-list-course-categories" class="multi-select-list hidden">
                                        <div id="assign-course-category-checkbox-list" class="p-2 space-y-1">
                                            <!-- Categories will be inserted here -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modified: Select Course for Multi-select -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Seleccionar Curso(s)</label>
                                <div class="multi-select-dropdown">
                                    <button id="multi-select-toggle-assign-courses" class="w-full text-left bg-white border border-gray-300 rounded-md p-2 mt-1 text-gray-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        <span id="selected-assign-courses-text">Seleccionar Cursos</span>
                                        <svg class="h-5 w-5 text-gray-400 inline-block float-right" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div id="multi-select-list-assign-courses" class="multi-select-list hidden">
                                        <div id="assign-course-checkbox-list" class="p-2 space-y-1">
                                            <!-- Courses will be inserted here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Existing grid container for employee filters and selection -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Sección de selección de empleados por área -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Filtrar Empleados por Área</label>
                                <div class="multi-select-dropdown">
                                    <button id="multi-select-toggle-assign-areas" class="w-full text-left bg-white border border-gray-300 rounded-md p-2 mt-1 text-gray-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        <span id="selected-assign-areas-text">Seleccionar Áreas</span>
                                        <svg class="h-5 w-5 text-gray-400 inline-block float-right" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div id="multi-select-list-assign-areas" class="multi-select-list hidden">
                                        <div id="assign-area-checkbox-list" class="p-2 space-y-1">
                                            <!-- Checkboxes se generarán dinámicamente -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- MODIFIED: Seleccionar Empleados (ahora dropdown multi-select) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Seleccionar Empleados</label>
                                <div class="multi-select-dropdown">
                                    <button id="multi-select-toggle-assign-employees" class="w-full text-left bg-white border border-gray-300 rounded-md p-2 mt-1 text-gray-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        <span id="selected-assign-employees-text">Seleccionar Empleados</span>
                                        <svg class="h-5 w-5 text-gray-400 inline-block float-right" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div id="multi-select-list-assign-employees" class="multi-select-list hidden">
                                        <div class="flex items-center mb-1 p-2">
                                            <input type="checkbox" id="assign-employee-all-checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                                            <label for="assign-employee-all-checkbox" class="text-sm text-gray-700 font-bold">Seleccionar Todos</label>
                                        </div>
                                        <hr class="my-2">
                                        <!-- Contenedor dinámico para la lista de empleados -->
                                        <div id="assign-employee-checkbox-list" class="p-2 space-y-1">
                                            <!-- Los empleados se cargarán aquí dinámicamente -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button onclick="assignCourses()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-full shadow-sm transition-colors duration-300">
                                Asignar Curso
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Contenido de Validar Cursos -->
                <div id="validate" class="tab-pane p-2 hidden relative">
                    <!-- Iconos de navegación y filtros en la esquina superior izquierda -->
                    <div class="absolute top-4 left-4 flex space-x-2 z-10">
                        <button class="icon-button" onclick="goToHomePage()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-home h-8 w-8 text-gray-600 hover:text-blue-600">
                                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                        </button>
                        <button class="icon-button" onclick="clearSpecificTabFilters('validate')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-broom h-8 w-8 text-gray-600 hover:text-blue-600">
                                <path d="M9 10V6a3 3 0 0 1 3-3v0a3 3 0 0 1 3 3v4"/>
                                <path d="M12 10v12"/>
                                <path d="M18 15v-3a3 3 0 0 0-3-3H9a3 3 0 0 0-3 3v3a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2Z"/>
                            </svg>
                        </button>
                    </div>
                    <h2 class="text-lg font-bold text-gray-800 mb-2 mt-16">Cursos Pendientes de Validación</h2>
                    <div class="bg-gray-100 rounded-md p-4 shadow-inner">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 mb-4">
                            <!-- Bloque de selección de área de trabajo (MODIFICADO A SELECCIÓN MÚLTIPLE) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Filtrar por Área de Trabajo</label>
                                <div class="multi-select-dropdown">
                                    <button id="multi-select-toggle-validate-areas" class="w-full text-left bg-white border border-gray-300 rounded-md p-2 mt-1 text-gray-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        <span id="selected-validate-areas-text">Seleccionar Áreas</span>
                                        <svg class="h-5 w-5 text-gray-400 inline-block float-right" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div id="multi-select-list-validate-areas" class="multi-select-list hidden">
                                        <div id="validate-area-checkbox-list" class="p-2 space-y-1">
                                            <!-- Checkboxes de áreas se generarán dinámicamente -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Filtrar curso por categoría (multi-select) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Filtrar curso por categoría</label>
                                <div class="multi-select-dropdown">
                                    <button id="multi-select-toggle-validate-course-categories" class="w-full text-left bg-white border border-gray-300 rounded-md p-2 mt-1 text-gray-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        <span id="selected-validate-course-categories-text">Seleccionar Categorías</span>
                                        <svg class="h-5 w-5 text-gray-400 inline-block float-right" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div id="multi-select-list-validate-course-categories" class="multi-select-list hidden">
                                        <div id="validate-course-category-checkbox-list" class="p-2 space-y-1">
                                            <!-- Categories will be inserted here -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bloque de selección de curso (MODIFICADO A SELECCIÓN MÚLTIPLE) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Filtrar por Curso</label>
                                <div class="multi-select-dropdown">
                                    <button id="multi-select-toggle-validate-courses" class="w-full text-left bg-white border border-gray-300 rounded-md p-2 mt-1 text-gray-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        <span id="selected-validate-courses-text">Seleccionar Cursos</span>
                                        <svg class="h-5 w-5 text-gray-400 inline-block float-right" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div id="multi-select-list-validate-courses" class="multi-select-list hidden">
                                        <div id="validate-course-checkbox-list" class="p-2 space-y-1">
                                            <!-- Checkboxes de cursos se generarán dinámicamente -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- NEW FLEX CONTAINER FOR TABLE AND CONTROLS -->
                        <div class="flex flex-col md:flex-row md:space-x-4">
                            <!-- Lista de cursos pendientes de validación (left half on medium screens and up) -->
                            <div class="mt-4 md:mt-0 w-full md:w-1/2 border border-gray-300 rounded-md p-2 h-64 overflow-y-auto">
                                <div class="flex items-center mb-1">
                                    <input type="checkbox" id="validate-select-all" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                                    <label for="validate-select-all" class="text-sm text-gray-700 font-bold">Seleccionar Todos</label>
                                </div>
                                <hr class="my-2">
                                <div id="validation-list" class="space-y-2">
                                    <!-- Los ítems de validación se renderizarán aquí dinámicamente -->
                                </div>
                            </div>
                            <!-- Controles de validación masiva (right half on medium screens and up) -->
                            <div class="mt-4 md:mt-0 w-full md:w-1/2 p-4 bg-white rounded-md shadow-sm border border-gray-200 flex flex-col justify-center items-center">
                                <h3 class="text-base font-semibold text-gray-800 mb-2">Validar Cursos Seleccionados</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-end w-full">
                                    <div>
                                        <label for="bulk-validate-expiry" class="block text-sm font-medium text-gray-700">Fecha de Vigencia</label>
                                        <input type="date" id="bulk-validate-expiry" class="w-full border border-gray-300 rounded-md p-2 text-gray-800 focus:outline-none focus:border-blue-500">
                                    </div>
                                    <button onclick="validateSelectedCourses()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md shadow-sm transition-colors duration-300 w-full">
                                        Validar Seleccionados
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenido de Score por curso -->
                <div id="score" class="tab-pane p-2 hidden relative">
                    <!-- Iconos de navegación y filtros en la esquina superior izquierda -->
                    <div class="absolute top-4 left-4 flex space-x-2 z-10">
                        <button class="icon-button" onclick="goToHomePage()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-home h-8 w-8 text-gray-600 hover:text-blue-600">
                                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                        </button>
                        <button class="icon-button" onclick="clearSpecificTabFilters('score')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-broom h-8 w-8 text-gray-600 hover:text-blue-600">
                                <path d="M9 10V6a3 3 0 0 1 3-3v0a3 3 0 0 1 3 3v4"/>
                                <path d="M12 10v12"/>
                                <path d="M18 15v-3a3 3 0 0 0-3-3H9a3 3 0 0 0-3 3v3a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2Z"/>
                            </svg>
                        </button>
                    </div>
                    <h2 class="text-lg font-bold text-gray-800 mb-4 mt-16">Índice de Capacitación por Curso</h2>
                    <div class="bg-gray-100 rounded-md p-4 shadow-inner">
                        <!-- Filtros de búsqueda -->
                        <div class="mb-4 p-4 bg-white rounded-lg shadow-sm border border-gray-200 max-w-lg mx-auto">
                            <div class="flex justify-between items-center cursor-pointer py-2 px-3 rounded-md bg-gray-50 hover:bg-gray-100 transition-colors duration-200" id="filter-header">
                                <h3 class="font-semibold text-gray-800">Filtros</h3>
                                <svg id="filter-arrow" class="h-5 w-5 text-gray-600 transform rotate-0 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div id="filter-content" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 hidden">
                                <!-- Filtro de Período -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Período</label>
                                    <div class="flex items-center space-x-2 mt-1">
                                        <input type="date" id="score-filter-date-from" class="w-1/2 border border-gray-300 rounded-md p-2 text-gray-800 focus:outline-none focus:border-blue-500">
                                        <span class="text-sm text-gray-600">a</span>
                                        <input type="date" id="score-filter-date-to" class="w-1/2 border border-gray-300 rounded-md p-2 text-gray-800 focus:outline-none focus:border-blue-500">
                                    </div>
                                </div>

                                <!-- Filtro de Cursos -->
                                <div class="multi-select-dropdown">
                                    <label class="block text-sm font-medium text-gray-700">Cursos</label>
                                    <button id="multi-select-toggle-courses" class="w-full text-left bg-white border border-gray-300 rounded-md p-2 mt-1 text-gray-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        <span id="selected-courses-text">Seleccionar Cursos</span>
                                        <svg class="h-5 w-5 text-gray-400 inline-block float-right" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div id="multi-select-list-courses" class="multi-select-list hidden">
                                        <div id="course-checkbox-list" class="p-2 space-y-1">
                                            <!-- Checkboxes se generarán dinámicamente -->
                                        </div>
                                    </div>
                                </div>

                                <!-- Filtro de Área de Trabajo (Nuevo) -->
                                <div class="multi-select-dropdown">
                                    <label class="block text-sm font-medium text-gray-700">Área de Trabajo</label>
                                    <button id="multi-select-toggle-areas" class="w-full text-left bg-white border border-gray-300 rounded-md p-2 mt-1 text-gray-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        <span id="selected-areas-text">Seleccionar Áreas</span>
                                        <svg class="h-5 w-5 text-gray-400 inline-block float-right" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div id="multi-select-list-areas" class="multi-select-list hidden">
                                        <div id="area-checkbox-list" class="p-2 space-y-1">
                                            <!-- Checkboxes se generarán dinámicamente -->
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-end md:col-span-2 gap-2">
                                    <button id="apply-filters-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md shadow-sm transition-colors duration-300 w-full">
                                        Aplicar Filtros
                                    </button>
                                    <button id="clear-filters-btn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md shadow-sm transition-colors duration-300 w-full">
                                        Limpiar Filtros
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Contenedor de resultados -->
                        <div id="score-results-list" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <!-- Los resultados se mostrarán aquí dinámicamente -->
                        </div>
                    </div>
                </div>

                <!-- Contenido de Matriz de Capacitación (Nueva Pestaña) -->
                <div id="matrix" class="tab-pane p-2 hidden relative">
                    <!-- Iconos de navegación y filtros en la esquina superior izquierda -->
                    <div class="absolute top-4 left-4 flex space-x-2 z-10">
                        <button class="icon-button" onclick="goToHomePage()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-home h-8 w-8 text-gray-600 hover:text-blue-600">
                                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                        </button>
                        <button class="icon-button" onclick="clearSpecificTabFilters('matrix')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-broom h-8 w-8 text-gray-600 hover:text-blue-600">
                                <path d="M9 10V6a3 3 0 0 1 3-3v0a3 3 0 0 1 3 3v4"/>
                                <path d="M12 10v12"/>
                                <path d="M18 15v-3a3 3 0 0 0-3-3H9a3 3 0 0 0-3 3v3a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2Z"/>
                            </svg>
                        </button>
                    </div>
                    <h2 class="text-lg font-bold text-gray-800 mb-2 mt-16">Matriz de Capacitación</h2>
                    <div class="bg-gray-100 rounded-md p-4 shadow-inner">
                        <p class="text-gray-600">Aquí se mostrará la matriz de capacitación. Puedes agregar contenido dinámico aquí.</p>
                        <!-- Contenido específico para la matriz de capacitación -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Validar Curso (validación individual) -->
    <div id="validate-modal" class="modal-overlay hidden">
        <div class="modal-content relative text-sm">
            <button class="absolute top-4 right-4 text-gray-500 hover:text-gray-800" onclick="closeValidateModal()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <h3 class="text-xl font-bold mb-4">Validar Curso</h3>
            <p class="text-gray-600 mb-4">Confirma la validación del curso para el empleado seleccionado.</p>

            <div class="space-y-4">
                <div>
                    <label for="modal-validate-employee" class="block text-gray-700 font-semibold mb-1">Empleado</label>
                    <input type="text" id="modal-validate-employee" readonly class="w-full bg-gray-100 border border-gray-300 rounded-md p-2 text-gray-800">
                </div>
                <div>
                    <label for="modal-validate-course" class="block text-gray-700 font-semibold mb-1">Curso</label>
                    <input type="text" id="modal-validate-course" readonly class="w-full bg-gray-100 border border-gray-300 rounded-md p-2 text-gray-800">
                </div>
                <div>
                    <label for="modal-validate-expiry" class="block text-gray-700 font-semibold mb-1">Fecha de Vigencia</label>
                    <input type="date" id="modal-validate-expiry" class="w-full border border-gray-300 rounded-md p-2 text-gray-800 focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button onclick="closeValidateModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-full transition-colors duration-200">
                    Cancelar
                </button>
                <button onclick="confirmValidation()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-full transition-colors duration-200">
                    Confirmar
                </button>
            </div>
        </div>
    </div>

    <script>
        // Helper function to get an element by ID with error logging
        function getElement(id, functionName) {
            const element = document.getElementById(id);
            if (!element) {
                console.error(`ERROR in ${functionName}: Element with ID '${id}' not found.`);
            }
            return element;
        }

        // Datos de empleados de ejemplo con su área de trabajo
        const allEmployees = [
            { id: 1, name: "Juan Pérez López", area: "Producción" },
            { id: 2, name: "María García Ruiz", area: "IT" },
            { id: 3, name: "Carlos Sánchez", area: "Producción" },
            { id: 4, name: "Ana Torres", area: "Recursos Humanos" },
            { id: 5, name: "Luis Mendoza", area: "IT" },
            { id: 6, name: "Sofía Vargas", area: "Ventas" },
            { id: 7, name: "Javier Ríos", area: "Producción" },
            { id: 8, name: "Valeria Ortiz", area: "Recursos Humanos" },
        ];

        // Datos de cursos pendientes de validación (simulados)
        const pendingValidations = [
            { employeeId: 1, employeeName: "Juan Pérez López", course: "Manejo de Montacargas", date: "10/07/25", area: "Producción", type: "Tecnica" },
            { employeeId: 2, employeeName: "María García Ruiz", course: "Trabajo en Alturas", date: "05/07/25", area: "IT", type: "HSE" },
            { employeeId: 5, employeeName: "Luis Mendoza", course: "Seguridad en el Trabajo", date: "08/07/25", area: "IT", type: "HSE" },
            { employeeId: 4, employeeName: "Ana Torres", course: "Primeros Auxilios", date: "12/07/25", area: "Recursos Humanos", type: "HSE" },
            { employeeId: 6, employeeName: "Sofía Vargas", course: "Protocolo de Seguridad del Cliente A", date: "01/07/25", area: "Ventas", type: "Externa" },
        ];

        // Nuevos datos para el cálculo del score por curso, incluyendo la fecha de finalización y áreas relevantes
        const coursesData = [
            { name: "Manejo de Montacargas", totalEnrolled: 10, completed: 8, dateCompleted: "2025-07-15", relevantAreas: ["Producción"], type: "Tecnica" },
            { name: "Trabajo en Alturas", totalEnrolled: 15, completed: 12, dateCompleted: "2025-07-10", relevantAreas: ["Producción", "IT"], type: "HSE" },
            { name: "Seguridad en el Trabajo", totalEnrolled: 20, completed: 18, dateCompleted: "2025-06-25", relevantAreas: ["Producción", "IT", "Recursos Humanos", "Ventas"], type: "HSE" },
            { name: "Primeros Auxilios", totalEnrolled: 12, completed: 12, dateCompleted: "2025-07-01", relevantAreas: ["Recursos Humanos", "Ventas"], type: "HSE" },
            { name: "Python para Análisis de Datos", totalEnrolled: 5, completed: 3, dateCompleted: "2025-06-18", relevantAreas: ["IT"], type: "Tecnica" },
            { name: "Certificación en AWS", totalEnrolled: 3, completed: 1, dateCompleted: "2025-07-20", relevantAreas: ["IT"], type: "Tecnica" },
            { name: "Protocolo de Seguridad del Cliente A", totalEnrolled: 7, completed: 5, dateCompleted: "2025-10-01", relevantAreas: ["Ventas"], type: "Externa" }
        ];

        // Lista de todas las categorías de cursos
        const allCourseCategories = ["HSE", "Tecnica", "Externa"];

        // Variable para controlar si la pestaña de Asignar ya ha sido inicializada
        let assignTabInitialized = false;
        // Variable para controlar si la pestaña de Validar ya ha sido inicializada
        let validateTabInitialized = false; // Flag for Validate tab initialization
        // Variable para controlar si la pestaña de Score ya ha sido inicializada
        let scoreTabInitialized = false;
        // Variable para controlar si la pestaña de Crear/Editar ya ha sido inicializada
        let createEditTabInitialized = false;
        // Variable para controlar si la pestaña de Matriz ya ha sido inicializada
        let matrixTabInitialized = false;


        // --- FUNCIONES AUXILIARES ---

        // Función para renderizar la lista de empleados en la pestaña de asignar
        function renderAssignEmployeeList() {
            const container = getElement('assign-employee-checkbox-list', 'renderAssignEmployeeList');
            if (!container) return; // Error logged by getElement

            container.innerHTML = ''; // Limpiar el contenedor
            if (allEmployees.length === 0) {
                container.innerHTML = '<p class="text-center text-sm text-gray-500">No hay empleados disponibles.</p>';
                return;
            }
            allEmployees.forEach((employee) => {
                const employeeDiv = document.createElement('div');
                employeeDiv.className = 'checkbox-item flex items-center'; // Usar la clase para estilos de dropdown
                employeeDiv.innerHTML = `
                    <input type="checkbox" id="assign-emp-${employee.id}" name="assign-employees" value="${employee.name}" data-area="${employee.area}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                    <label for="assign-emp-${employee.id}" class="text-sm text-gray-700">${employee.name}</label>
                `;
                container.appendChild(employeeDiv);
            });
            console.log(`Rendered ${allEmployees.length} employees for assignment.`);
        }

        function updateSelectedAssignAreasText() {
            const selectedAreas = Array.from(document.querySelectorAll('input[name="assign-areas-checkbox"]:checked'))
                                         .map(checkbox => checkbox.value);
            const textSpan = getElement('selected-assign-areas-text', 'updateSelectedAssignAreasText');
            if (textSpan) {
                if (selectedAreas.length === 0) {
                    textSpan.textContent = 'Seleccionar Áreas';
                } else if (selectedAreas.length === 1) {
                    textSpan.textContent = selectedAreas[0];
                } else {
                    textSpan.textContent = `${selectedAreas.length} áreas seleccionadas`;
                }
            } else {
                console.warn("WARN in updateSelectedAssignAreasText: Element with ID 'selected-assign-areas-text' not found.");
            }
        }

        // Función para renderizar la lista de validación en la pestaña de validar
        function renderValidationList(validations) {
            const container = getElement('validation-list', 'renderValidationList');
            if (!container) return; // Error logged by getElement

            container.innerHTML = ''; // Limpiar el contenedor
            if (validations.length === 0) {
                container.innerHTML = '<p class="text-center text-sm text-gray-500">No hay cursos pendientes de validación con los filtros seleccionados.</p>';
                return;
            }
            validations.forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'bg-white rounded-md p-3 shadow-sm border border-gray-200 flex flex-col sm:flex-row items-start sm:items-center justify-between';
                // Usar JSON.stringify para escapar correctamente los strings en el atributo onclick
                const employeeNameEscaped = JSON.stringify(item.employeeName);
                const courseNameEscaped = JSON.stringify(item.course);
                itemDiv.innerHTML = `
                    <div class="flex-1 min-w-0 flex items-center mb-2 sm:mb-0">
                        <input type="checkbox" id="validate-item-${item.employeeId}-${item.course.replace(/\s/g, '-')}" name="validate-items" data-employee-name="${item.employeeName}" data-course-name="${item.course}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                        <label for="validate-item-${item.employeeId}-${item.course.replace(/\s/g, '-')}" class="flex-1">
                            <h3 class="font-semibold text-gray-900">${item.employeeName}</h3>
                            <p class="text-sm text-gray-600">Curso: <span class="font-medium">${item.course}</span></p>
                            <p class="text-sm text-gray-600">Fecha de Solicitud: <span class="font-medium">${item.date}</span></p>
                        </label>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick='openValidateModal(${employeeNameEscaped}, ${courseNameEscaped})' class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-1 px-3 rounded-md shadow-sm transition-colors duration-300 text-sm">Validar Individual</button>
                    </div>
                `;
                container.appendChild(itemDiv);
            });
        }

        // Lógica principal para filtrar cursos de validación por área y curso
        function filterValidationList() {
            console.log("--- START: filterValidationList() ---");
            const selectedAreas = Array.from(document.querySelectorAll('input[name="validate-areas-checkbox"]:checked'))
                                         .map(checkbox => checkbox.value);
            const selectedCourseCategories = Array.from(document.querySelectorAll('input[name="validate-course-categories-checkbox"]:checked'))
                                         .map(checkbox => checkbox.value);
            const selectedCourses = Array.from(document.querySelectorAll('input[name="validate-courses-checkbox"]:checked'))
                                         .map(checkbox => checkbox.value);

            console.log("Selected Areas for Validation:", selectedAreas);
            console.log("Selected Course Categories for Validation:", selectedCourseCategories);
            console.log("Selected Courses for Validation:", selectedCourses);

            let filteredValidations = pendingValidations;

            // Filtrar por área si hay alguna seleccionada
            if (selectedAreas.length > 0) {
                filteredValidations = filteredValidations.filter(validation =>
                    selectedAreas.includes(validation.area)
                );
                console.log("Filtered by areas:", filteredValidations.map(v => v.employeeName + " - " + v.course));
            }

            // Filtrar por categoría de curso si hay alguna seleccionada
            if (selectedCourseCategories.length > 0) {
                filteredValidations = filteredValidations.filter(validation =>
                    selectedCourseCategories.includes(validation.type)
                );
                console.log("Filtered by course categories:", filteredValidations.map(v => v.employeeName + " - " + v.course));
            }

            // Filtrar por curso si hay uno seleccionado
            if (selectedCourses.length > 0) {
                filteredValidations = filteredValidations.filter(validation =>
                    selectedCourses.includes(validation.course)
                );
                console.log("Filtered by courses:", filteredValidations.map(v => v.employeeName + " - " + v.course));
            }

            renderValidationList(filteredValidations);
            console.log("--- END: filterValidationList() ---");
        }

        // Update text for validate areas dropdown button
        function updateSelectedValidateAreasText() {
            const selectedAreas = Array.from(document.querySelectorAll('input[name="validate-areas-checkbox"]:checked'))
                                         .map(checkbox => checkbox.value);
            const textSpan = getElement('selected-validate-areas-text', 'updateSelectedValidateAreasText');
            if (textSpan) {
                if (selectedAreas.length === 0) {
                    textSpan.textContent = 'Seleccionar Áreas';
                } else if (selectedAreas.length === 1) {
                    textSpan.textContent = selectedAreas[0];
                } else {
                    textSpan.textContent = `${selectedAreas.length} áreas seleccionadas`;
                }
            } else {
                console.warn("WARN in updateSelectedValidateAreasText: Element with ID 'selected-validate-areas-text' not found.");
            }
            filterValidationList(); // Re-filter when selection changes
        }

        // Update text for validate course categories dropdown button
        function updateSelectedValidateCourseCategoriesText() {
            const selectedCategories = Array.from(document.querySelectorAll('input[name="validate-course-categories-checkbox"]:checked'))
                                         .map(checkbox => checkbox.value);
            const textSpan = getElement('selected-validate-course-categories-text', 'updateSelectedValidateCourseCategoriesText');
            if (textSpan) {
                if (selectedCategories.length === 0) {
                    textSpan.textContent = 'Seleccionar Categorías';
                } else if (selectedCategories.length === 1) {
                    textSpan.textContent = selectedCategories[0];
                } else {
                    textSpan.textContent = `${selectedCategories.length} categorías seleccionadas`;
                }
            } else {
                console.warn("WARN in updateSelectedValidateCourseCategoriesText: Element with ID 'selected-validate-course-categories-text' not found.");
            }
            filterValidationList(); // Re-filter when selection changes
        }

        // Update text for validate courses dropdown button
        function updateSelectedValidateCoursesText() {
            const selectedCourses = Array.from(document.querySelectorAll('input[name="validate-courses-checkbox"]:checked'))
                                         .map(checkbox => checkbox.value);
            const textSpan = getElement('selected-validate-courses-text', 'updateSelectedValidateCoursesText');
            if (textSpan) {
                if (selectedCourses.length === 0) {
                    textSpan.textContent = 'Seleccionar Cursos';
                } else if (selectedCourses.length === 1) {
                    textSpan.textContent = selectedCourses[0];
                } else {
                    textSpan.textContent = `${selectedCourses.length} cursos seleccionados`;
                }
            } else {
                console.warn("WARN in updateSelectedValidateCoursesText: Element with ID 'selected-validate-courses-text' not found.");
            }
            filterValidationList(); // Re-filter when selection changes
        }

        // Function to clear all filters in the Validate Courses section
        function clearValidateFilters() {
            console.log("--- START: clearValidateFilters() ---");
            // Desmarcar todos los checkboxes de áreas de validación
            document.querySelectorAll('input[name="validate-areas-checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            updateSelectedValidateAreasText(); // Actualizar el texto del botón de áreas

            // Desmarcar todos los checkboxes de categorías de cursos de validación
            document.querySelectorAll('input[name="validate-course-categories-checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            updateSelectedValidateCourseCategoriesText(); // Actualizar el texto del botón de categorías

            // Desmarcar todos los checkboxes de cursos de validación
            document.querySelectorAll('input[name="validate-courses-checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            updateSelectedValidateCoursesText(); // Actualizar el texto del botón de cursos

            // Desmarcar el checkbox "Seleccionar Todos" de la lista de validación
            const validateSelectAllCheckbox = getElement('validate-select-all', 'clearValidateFilters');
            if (validateSelectAllCheckbox) {
                validateSelectAllCheckbox.checked = false;
            }

            // Volver a renderizar la lista de validación con los filtros limpios
            filterValidationList();
            console.log("--- END: clearValidateFilters() ---");
        }


        function updateSelectedCoursesText() {
            const selectedCourses = Array.from(document.querySelectorAll('input[name="score-courses"]:checked'))
                                         .map(checkbox => checkbox.value);
            const textSpan = getElement('selected-courses-text', 'updateSelectedCoursesText');
            if (textSpan) {
                if (selectedCourses.length === 0) {
                    textSpan.textContent = 'Seleccionar Cursos';
                } else if (selectedCourses.length === 1) {
                    textSpan.textContent = selectedCourses[0];
                } else {
                    textSpan.textContent = `${selectedCourses.length} cursos seleccionados`;
                }
            } else {
                console.warn("WARN in updateSelectedCoursesText: Element with ID 'selected-courses-text' not found.");
            }
        }

        function updateSelectedAreasText() {
            const selectedAreas = Array.from(document.querySelectorAll('input[name="score-areas"]:checked'))
                                         .map(checkbox => checkbox.value);
            const textSpan = getElement('selected-areas-text', 'updateSelectedAreasText');
            if (textSpan) {
                if (selectedAreas.length === 0) {
                    textSpan.textContent = 'Seleccionar Áreas';
                } else if (selectedAreas.length === 1) {
                    textSpan.textContent = selectedAreas[0];
                } else {
                    textSpan.textContent = `${selectedAreas.length} áreas seleccionadas`;
                }
            } else {
                console.warn("WARN in updateSelectedAreasText: Element with ID 'selected-areas-text' not found.");
            }
        }

        // Función para limpiar todos los filtros de la sección de Score
        function clearScoreFilters() {
            console.log("--- START: clearScoreFilters() ---");
            // Limpiar campos de fecha
            const scoreFilterDateFrom = getElement('score-filter-date-from', 'clearScoreFilters');
            const scoreFilterDateTo = getElement('score-filter-date-to', 'clearScoreFilters');
            if (scoreFilterDateFrom) scoreFilterDateFrom.value = '';
            if (scoreFilterDateTo) scoreFilterDateTo.value = '';
            console.log("Date filters cleared.");

            // Desmarcar todos los checkboxes de cursos
            document.querySelectorAll('input[name="score-courses"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            updateSelectedCoursesText(); // Actualizar el texto del botón de cursos
            console.log("Course checkboxes cleared.");

            // Desmarcar todos los checkboxes de áreas
            document.querySelectorAll('input[name="score-areas"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            updateSelectedAreasText(); // Actualizar el texto del botón de áreas
            console.log("Area checkboxes cleared.");

            // Volver a renderizar los cursos sin filtros
            renderCourseScores();
            console.log("renderCourseScores() called after clearing filters.");
            console.log("--- END: clearScoreFilters() ---");
        }

        // Nueva función para renderizar el score de los cursos
        function renderCourseScores() {
            const container = getElement('score-results-list', 'renderCourseScores');
            if (!container) return; // Error logged by getElement

            container.innerHTML = ''; // Limpiar el contenedor

            // Obtener los filtros
            const dateFromInput = getElement('score-filter-date-from', 'renderCourseScores');
            const dateToInput = getElement('score-filter-date-to', 'renderCourseScores');

            const dateFrom = dateFromInput ? dateFromInput.value : '';
            const dateTo = dateToInput ? dateToInput.value : '';

            const selectedCourses = Array.from(document.querySelectorAll('input[name="score-courses"]:checked'))
                                         .map(checkbox => checkbox.value);
            const selectedAreas = Array.from(document.querySelectorAll('input[name="score-areas"]:checked'))
                                         .map(checkbox => checkbox.value);

            console.log("--- Inicia renderCourseScores ---");
            console.log("Filtros actuales:");
            console.log("Fecha Desde:", dateFrom);
            console.log("Fecha Hasta:", dateTo);
            console.log("Cursos Seleccionados:", selectedCourses);
            console.log("Áreas Seleccionadas:", selectedAreas);

            let filteredCourses = coursesData;
            console.log("Cursos iniciales (coursesData):", coursesData);

            // Aplicar filtro de cursos
            if (selectedCourses.length > 0) {
                filteredCourses = filteredCourses.filter(course => selectedCourses.includes(course.name));
                console.log("Cursos después de filtrar por nombre:", filteredCourses);
            }

            // Aplicar filtro de área
            if (selectedAreas.length > 0) {
                filteredCourses = filteredCourses.filter(course =>
                    course.relevantAreas && course.relevantAreas.some(area => selectedAreas.includes(area))
                );
                console.log("Cursos después de filtrar por área:", filteredCourses);
            }

            // Aplicar filtro de fecha
            if (dateFrom && dateTo) {
                const fromDate = new Date(dateFrom);
                const toDate = new Date(dateTo);
                toDate.setHours(23, 59, 59, 999); // Ajustar toDate para incluir el día completo
                console.log("Fechas de filtro (Date objetos):", fromDate, toDate);

                filteredCourses = filteredCourses.filter(course => {
                    if (course.dateCompleted) {
                        const courseDate = new Date(course.dateCompleted);
                        console.log(`Comparando ${course.name}: ${courseDate.toISOString()} >= ${fromDate.toISOString()} && ${courseDate.toISOString()} <= ${toDate.toISOString()}`);
                        return courseDate >= fromDate && courseDate <= toDate;
                    }
                    return false;
                });
                console.log("Cursos después de filtrar por fecha:", filteredCourses);
            }

            if (filteredCourses.length === 0) {
                container.innerHTML = '<p class="text-center text-gray-500 col-span-full">No hay datos de cursos disponibles con los filtros aplicados.</p>';
                console.log("No se encontraron cursos con los filtros aplicados.");
                return;
            }

            filteredCourses.forEach(course => {
                const completionIndex = course.totalEnrolled > 0 ? (course.completed / course.totalEnrolled) * 100 : 0;
                const remainingParticipants = course.totalEnrolled - course.completed; // Calculate remaining

                let colorClass = '';
                if (completionIndex <= 60) {
                    colorClass = 'red';
                } else if (completionIndex >= 61 && completionIndex < 80) {
                    colorClass = 'orange';
                } else if (completionIndex >= 80 && completionIndex <= 94) {
                    colorClass = 'yellow';
                } else { // completionIndex >= 95
                    colorClass = 'green';
                }

                const scoreElement = document.createElement('div');
                scoreElement.className = 'bg-white rounded-xl shadow-lg p-5 hover:shadow-xl transition-shadow duration-300';

                // Construir la barra de progreso de "cuadritos"
                let progressSquaresHtml = '';
                const filledSquaresCount = Math.floor(completionIndex / 10);
                for (let i = 0; i < 10; i++) {
                    const isFilled = i < filledSquaresCount;
                    progressSquaresHtml += `<div class="progress-square ${isFilled ? 'filled ' + colorClass : ''}"></div>`;
                }

                scoreElement.innerHTML = `
                    <h3 class="font-semibold text-lg text-gray-900 mb-2 truncate">${course.name}</h3>
                    <div class="text-center my-3">
                        <span class="text-5xl font-extrabold text-blue-600 tracking-tight">${completionIndex.toFixed(0)}%</span>
                        <p class="text-xs font-medium text-gray-500">Índice de Capacitación</p>
                    </div>
                    <!-- New section for completed and remaining participants -->
                    <div class="flex justify-between text-xs font-medium text-gray-500 mt-2 mb-3">
                        <span>Completados: <span class="text-gray-900 font-semibold">${course.completed}</span></span>
                        <span>Faltantes: <span class="text-gray-900 font-semibold">${remainingParticipants}</span></span>
                    </div>
                    <div class="progress-squares-container">
                        ${progressSquaresHtml}
                    </div>
                `;
                container.appendChild(scoreElement);
            });
            console.log("--- Fin renderCourseScores ---");
        }

        // --- FIN FUNCIONES AUXILIARES ---

        // Función para ir a la página principal (vista de bienvenida)
        function goToHomePage() {
            console.log("--- goToHomePage() called. ---");
            // Ocultar todos los paneles de contenido de las pestañas
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.add('hidden');
            });
            // Mostrar el panel de bienvenida
            const welcomePane = getElement('welcome-pane', 'goToHomePage');
            if (welcomePane) {
                welcomePane.classList.remove('hidden');
            }

            // Desactivar visualmente todos los botones de las pestañas
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('border-blue-600', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-600');
                const icon = btn.querySelector('svg');
                if (icon) {
                    icon.classList.remove('text-blue-600');
                    icon.classList.add('text-blue-400');
                }
            });
            console.log("--- Returned to home page state. ---");
        }

        // Lógica para cambiar de pestaña
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                console.log(`--- Tab button "${button.getAttribute('data-tab')}" clicked. ---`);
                // Eliminar el estilo activo de todos los botones y ocultar todos los contenidos
                document.querySelectorAll('.tab-button').forEach(btn => {
                    btn.classList.remove('border-blue-600', 'text-blue-600');
                    btn.classList.add('border-transparent', 'text-gray-600');
                    const icon = btn.querySelector('svg');
                    if (icon) {
                        icon.classList.remove('text-blue-600');
                        icon.classList.add('text-blue-400');
                    }
                });
                document.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.add('hidden');
                });
                console.log("All tabs hidden and buttons inactive.");

                // Agregar el estilo activo al botón clicado y mostrar su contenido
                button.classList.remove('border-transparent', 'text-gray-600');
                button.classList.add('border-blue-600', 'text-blue-600');
                const activeIcon = button.querySelector('svg');
                if (activeIcon) {
                    activeIcon.classList.remove('text-blue-400');
                    activeIcon.classList.add('text-blue-600');
                }

                const targetTab = button.getAttribute('data-tab');
                const targetPane = getElement(targetTab, 'tab-button click handler');
                if (targetPane) {
                    targetPane.classList.remove('hidden');
                    console.log(`Tab "${targetTab}" set to visible. Element found:`, targetPane);
                } else {
                    console.error(`ERROR in tab-button click handler: Panel para la pestaña "${targetTab}" no encontrado.`);
                }


                // Ocultar la pantalla de bienvenida
                const welcomePane = getElement('welcome-pane', 'tab-button click handler');
                if (welcomePane) {
                    welcomePane.classList.add('hidden');
                }
                console.log("Welcome pane hidden.");

                // Lógica de inicialización condicional de pestañas
                if (targetTab === 'create-edit') {
                    if (!createEditTabInitialized) {
                        // initializeCreateEditTab(); // Call initialization function if needed
                        createEditTabInitialized = true;
                        console.log("create-edit tab initialized (first time).");
                    }
                } else if (targetTab === 'assign') {
                    if (!assignTabInitialized) {
                        initializeAssignTab(); // Nueva función para la configuración de la pestaña de asignar
                        assignTabInitialized = true;
                        console.log("initializeAssignTab() called for 'assign' tab (first time).");
                    } else {
                        // Re-renderizar o actualizar si es necesario en visitas posteriores
                        filterAssignCoursesByCategoryAndRender(); // Update course list based on category selection
                        // No need to call renderAssignEmployeeList here, it's always rendered on init.
                        console.log("Assign tab already initialized, updating filters.");
                    }
                } else if (targetTab === 'validate') {
                    if (!validateTabInitialized) { // Check if validate tab is initialized
                        initializeValidateTab(); // Initialize validate tab
                        validateTabInitialized = true;
                        console.log("initializeValidateTab() called for 'validate' tab (first time).");
                    } else {
                        filterValidationList(); // Always filter when opening the validate tab
                        console.log("filterValidationList() called for 'validate' tab (subsequent time).");
                    }
                } else if (targetTab === 'score') {
                    if (!scoreTabInitialized) {
                        setupScoreFilters(); // Esto configurará los filtros y llamará a renderCourseScores
                        scoreTabInitialized = true;
                        console.log("setupScoreFilters() called for 'score' tab (first time).");
                    } else {
                        // Si ya está inicializada, solo renderizamos los scores con los filtros actuales
                        renderCourseScores();
                        console.log("renderCourseScores() called for 'score' tab (subsequent time).");
                    }
                } else if (targetTab === 'matrix') {
                    if (!matrixTabInitialized) {
                        // initializeMatrixTab(); // Call initialization function if needed
                        matrixTabInitialized = true;
                        console.log("matrix tab initialized (first time).");
                    }
                }
                console.log("--- Tab click handler finished. ---");
            });
        });

        // Lógica del modal de validación individual
        function openValidateModal(employeeName, courseName) {
            const modalValidateEmployee = getElement('modal-validate-employee', 'openValidateModal');
            const modalValidateCourse = getElement('modal-validate-course', 'openValidateModal');
            const validateModal = getElement('validate-modal', 'openValidateModal');

            if (modalValidateEmployee) modalValidateEmployee.value = employeeName;
            if (modalValidateCourse) modalValidateCourse.value = courseName;
            if (validateModal) validateModal.classList.remove('hidden');
        }

        function closeValidateModal() {
            const validateModal = getElement('validate-modal', 'closeValidateModal');
            if (validateModal) validateModal.classList.add('hidden');
        }

        function confirmValidation() {
            const expiryDateInput = getElement('modal-validate-expiry', 'confirmValidation');
            const expiryDate = expiryDateInput ? expiryDateInput.value : '';
            const employeeNameInput = getElement('modal-validate-employee', 'confirmValidation');
            const employeeName = employeeNameInput ? employeeNameInput.value : '';
            const courseNameInput = getElement('modal-validate-course', 'confirmValidation');
            const courseName = courseNameInput ? courseNameInput.value : '';

            if (!expiryDate) {
                console.error('ERROR in confirmValidation: La fecha de vigencia es obligatoria.');
                return;
            }

            console.log(`Validando curso: ${courseName} para ${employeeName} con vigencia hasta ${expiryDate}`);

            // Aquí iría la lógica para enviar los datos al backend
            closeValidateModal();
        }

        // Lógica para validar múltiples cursos seleccionados
        function validateSelectedCourses() {
            const bulkValidateExpiry = getElement('bulk-validate-expiry', 'validateSelectedCourses');
            const expiryDate = bulkValidateExpiry ? bulkValidateExpiry.value : '';
            const selectedItems = Array.from(document.querySelectorAll('#validation-list input[name="validate-items"]:checked'));

            if (!expiryDate) {
                console.error('ERROR in validateSelectedCourses: La fecha de vigencia es obligatoria.');
                return;
            }

            if (selectedItems.length === 0) {
                console.error('ERROR in validateSelectedCourses: No se ha seleccionado ningún curso para validar.');
                return;
            }

            const validationsToPerform = selectedItems.map(item => {
                return {
                    employeeName: item.getAttribute('data-employee-name'),
                    courseName: item.getAttribute('data-course-name'),
                    expiryDate: expiryDate
                };
            });

            console.log("Validando múltiples cursos:");
            console.log(validationsToPerform);

            // Aquí iría la lógica para enviar los datos al backend

            // Limpiar la selección después de la validación
            selectedItems.forEach(item => item.checked = false);
            if (bulkValidateExpiry) bulkValidateExpiry.value = '';

            // Opcionalmente, refrescar la lista
            filterValidationList(); // Re-filter after validation
        }

        // Lógica de selección de todos los elementos para la validación
        const validateSelectAll = getElement('validate-select-all', 'global scope (validateSelectAll)');
        if (validateSelectAll) {
            validateSelectAll.addEventListener('change', (e) => {
                document.querySelectorAll('#validation-list input[name="validate-items"]').forEach(checkbox => {
                    checkbox.checked = e.target.checked;
                });
            });
        }


        // Lógica para asignar cursos
        function assignCourses() {
            console.log("--- START: assignCourses() ---");
            const selectedCourses = Array.from(document.querySelectorAll('#assign-course-checkbox-list input[name="assign-courses"]:checked'))
                                         .map(checkbox => checkbox.value);

            if (selectedCourses.length === 0) {
                console.error('ERROR in assignCourses: Selecciona al menos un curso para asignar.');
                return;
            }

            const selectedIndividualEmployees = Array.from(document.querySelectorAll('#assign-employee-checkbox-list input[name="assign-employees"]:checked'))
                                         .map(checkbox => checkbox.value);
            const selectedAreas = Array.from(document.querySelectorAll('input[name="assign-areas-checkbox"]:checked'))
                                         .map(checkbox => checkbox.value);

            let employeesToAssign = [];

            if (selectedIndividualEmployees.length > 0) {
                // Si hay empleados individuales seleccionados, se asigna solo a ellos
                employeesToAssign = selectedIndividualEmployees;
                console.log("Assigning to explicitly selected employees.");
            } else if (selectedAreas.length > 0) {
                // Si no hay empleados individuales seleccionados, pero sí áreas,
                // se asigna a todos los empleados de esas áreas.
                employeesToAssign = allEmployees.filter(employee =>
                    selectedAreas.includes(employee.area)
                ).map(employee => employee.name);
                console.log("Assigning to all employees in selected areas.");
            } else {
                console.error('ERROR in assignCourses: Selecciona al menos un área o empleados específicos para asignar el curso.');
                return;
            }

            if (employeesToAssign.length === 0) {
                console.error('ERROR in assignCourses: No se encontraron empleados para asignar con las selecciones actuales.');
                return;
                }

            console.log(`Asignando los siguientes cursos:`);
            console.log(selectedCourses);
            console.log(`A los siguientes empleados:`);
            console.log(employeesToAssign);

            // Aquí iría la lógica para enviar los datos al backend
            // Por ejemplo, una llamada fetch a tu API:
            // fetch('/api/assign-course', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify({ courses: selectedCourses, employees: employeesToAssign })
            // })
            // .then(response => response.json())
            // .then(data => {
            //     console.log('Asignación exitosa:', data);
            //     // Opcional: mostrar un mensaje de éxito al usuario
            // })
            // .catch(error => {
            //     console.error('Error al asignar el curso:', error);
            //     // Opcional: mostrar un mensaje de error al usuario
            // });

            // Limpiar las selecciones después de la asignación (opcional)
            document.querySelectorAll('input[name="assign-courses"]').forEach(checkbox => checkbox.checked = false);
            document.querySelectorAll('input[name="assign-areas-checkbox"]').forEach(checkbox => checkbox.checked = false);
            document.querySelectorAll('input[name="assign-employees"]').forEach(checkbox => checkbox.checked = false);
            document.querySelectorAll('input[name="assign-course-categories"]').forEach(checkbox => checkbox.checked = false); // Clear category filters too
            updateSelectedAssignCoursesText(); // Update course button text
            updateSelectedAssignAreasText(); // Update area button text
            updateSelectedAssignEmployeesText(); // Update employee button text
            filterAssignCoursesByCategoryAndRender(); // Re-render courses based on cleared categories
            renderAssignEmployeeList(); // Re-render employees list in assign tab

            console.log("--- END: assignCourses() ---");
        }

        // Renders courses based on selected categories
        function filterAssignCoursesByCategoryAndRender() {
            console.log("--- START: filterAssignCoursesByCategoryAndRender() ---");
            const selectedCategories = Array.from(document.querySelectorAll('input[name="assign-course-categories"]:checked'))
                                             .map(checkbox => checkbox.value);
            const courseCheckboxList = getElement('assign-course-checkbox-list', 'filterAssignCoursesByCategoryAndRender');
            if (!courseCheckboxList) return;

            courseCheckboxList.innerHTML = ''; // Clear existing courses

            let coursesToRender = coursesData;

            if (selectedCategories.length > 0) {
                coursesToRender = coursesData.filter(course => selectedCategories.includes(course.type));
                console.log("Courses filtered by category:", coursesToRender.map(c => c.name));
            } else {
                console.log("No categories selected, showing all courses.");
            }

            if (coursesToRender.length === 0) {
                courseCheckboxList.innerHTML = '<p class="text-center text-sm text-gray-500">No hay cursos disponibles para las categorías seleccionadas.</p>';
            } else {
                coursesToRender.forEach(course => {
                    const checkboxDiv = document.createElement('div');
                    checkboxDiv.className = 'checkbox-item flex items-center';
                    checkboxDiv.innerHTML = `
                        <input type="checkbox" id="assign-course-${course.name.replace(/\s/g, '-')}" name="assign-courses" value="${course.name}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="assign-course-${course.name.replace(/\s/g, '-')}" class="text-sm text-gray-700 ml-2">${course.name}</label>
                    `;
                    courseCheckboxList.appendChild(checkboxDiv);
                });
            }
            console.log("--- END: filterAssignCoursesByCategoryAndRender() ---");
        }

        // Updates the text for selected courses in the Assign tab
        function updateSelectedAssignCoursesText() {
            const selectedCourses = Array.from(document.querySelectorAll('input[name="assign-courses"]:checked'))
                                         .map(checkbox => checkbox.value);
            const textSpan = getElement('selected-assign-courses-text', 'updateSelectedAssignCoursesText');
            if (textSpan) {
                if (selectedCourses.length === 0) {
                    textSpan.textContent = 'Seleccionar Cursos';
                } else if (selectedCourses.length === 1) {
                    textSpan.textContent = selectedCourses[0];
                } else {
                    textSpan.textContent = `${selectedCourses.length} cursos seleccionados`;
                }
            } else {
                console.warn("WARN in updateSelectedAssignCoursesText: Element with ID 'selected-assign-courses-text' not found.");
            }
        }

        // NEW: Updates the text for selected employees in the Assign tab
        function updateSelectedAssignEmployeesText() {
            const selectedEmployees = Array.from(document.querySelectorAll('input[name="assign-employees"]:checked'))
                                         .map(checkbox => checkbox.value);
            const textSpan = getElement('selected-assign-employees-text', 'updateSelectedAssignEmployeesText');
            if (textSpan) {
                if (selectedEmployees.length === 0) {
                    textSpan.textContent = 'Seleccionar Empleados';
                } else if (selectedEmployees.length === 1) {
                    textSpan.textContent = selectedEmployees[0];
                } else {
                    textSpan.textContent = `${selectedEmployees.length} empleados seleccionados`;
                }
            } else {
                console.warn("WARN in updateSelectedAssignEmployeesText: Element with ID 'selected-assign-employees-text' not found.");
            }
        }

        // Inicialización de la pestaña de Asignar Cursos
        function initializeAssignTab() {
            console.log("--- START: initializeAssignTab() ---");
            try {
                // Lógica para el desplegable de selección múltiple de categorías de cursos
                const courseCategoryChecklistContainer = getElement('assign-course-category-checkbox-list', 'initializeAssignTab');
                if (courseCategoryChecklistContainer) {
                    courseCategoryChecklistContainer.innerHTML = '';
                    allCourseCategories.forEach(categoryName => {
                        const checkboxDiv = document.createElement('div');
                        checkboxDiv.className = 'checkbox-item flex items-center';
                        checkboxDiv.innerHTML = `
                            <input type="checkbox" id="assign-course-category-${categoryName.replace(/\s/g, '-')}" name="assign-course-categories" value="${categoryName}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="assign-course-category-${categoryName.replace(/\s/g, '-')}" class="text-sm text-gray-700 ml-2">${categoryName}</label>
                        `;
                        courseCategoryChecklistContainer.appendChild(checkboxDiv);
                    });
                    console.log("Assign course category checkboxes populated.");

                    const toggleButton = getElement('multi-select-toggle-course-categories', 'initializeAssignTab');
                    const dropdownList = getElement('multi-select-list-course-categories', 'initializeAssignTab');

                    if (toggleButton && dropdownList) {
                        toggleButton.addEventListener('click', (event) => {
                            event.stopPropagation();
                            dropdownList.classList.toggle('hidden');
                            // Close other dropdowns if open
                            getElement('multi-select-list-assign-courses', 'initializeAssignTab')?.classList.add('hidden');
                            getElement('multi-select-list-assign-areas', 'initializeAssignTab')?.classList.add('hidden');
                            getElement('multi-select-list-assign-employees', 'initializeAssignTab')?.classList.add('hidden'); // NEW
                            console.log("Assign course categories dropdown toggled.");
                        });

                        document.addEventListener('click', (e) => {
                            if (dropdownList && !dropdownList.contains(e.target) && e.target !== toggleButton) {
                                dropdownList.classList.add('hidden');
                            }
                        });
                        console.log("Assign course categories dropdown listeners setup.");
                    } else {
                        console.warn("WARN in initializeAssignTab: Assign course categories toggle button or dropdown list not found.");
                    }

                    document.querySelectorAll('input[name="assign-course-categories"]').forEach(checkbox => {
                        checkbox.addEventListener('change', () => {
                            const selectedCategories = Array.from(document.querySelectorAll('input[name="assign-course-categories"]:checked'))
                                .map(cb => cb.value);
                            const textSpan = getElement('selected-course-categories-text', 'updateSelectedCourseCategoriesText');
                            if (textSpan) {
                                if (selectedCategories.length === 0) {
                                    textSpan.textContent = 'Seleccionar Categorías';
                                } else if (selectedCategories.length === 1) {
                                    textSpan.textContent = selectedCategories[0];
                                } else {
                                    textSpan.textContent = `${selectedCategories.length} categorías seleccionadas`;
                                }
                            }
                            filterAssignCoursesByCategoryAndRender(); // Re-render courses when categories change
                            updateSelectedAssignCoursesText(); // Clear selected courses when category changes
                        });
                    });
                    // Initial render for course categories
                    const initialSelectedCategories = Array.from(document.querySelectorAll('input[name="assign-course-categories"]:checked')).map(cb => cb.value);
                    const initialTextSpan = getElement('selected-course-categories-text', 'initial category text');
                    if (initialTextSpan) {
                        if (initialSelectedCategories.length === 0) {
                            initialTextSpan.textContent = 'Seleccionar Categorías';
                        } else if (initialSelectedCategories.length === 1) {
                            initialTextSpan.textContent = initialSelectedCategories[0];
                        } else {
                            initialTextSpan.textContent = `${initialSelectedCategories.length} categorías seleccionadas`;
                        }
                    }
                    console.log("Assign course category change listeners set.");
                } else {
                    console.error("ERROR in initializeAssignTab: Element 'assign-course-category-checkbox-list' not found.");
                }


                // Lógica para el desplegable de selección múltiple de cursos
                const assignCourseChecklistContainer = getElement('assign-course-checkbox-list', 'initializeAssignTab');
                if (assignCourseChecklistContainer) {
                    // Initial render of courses (all courses if no category selected)
                    filterAssignCoursesByCategoryAndRender();
                    console.log("Initial courses rendered based on categories.");

                    const toggleButton = getElement('multi-select-toggle-assign-courses', 'initializeAssignTab');
                    const dropdownList = getElement('multi-select-list-assign-courses', 'initializeAssignTab');

                    if (toggleButton && dropdownList) {
                        toggleButton.addEventListener('click', (event) => {
                            event.stopPropagation();
                            dropdownList.classList.toggle('hidden');
                            // Close other dropdowns if open
                            getElement('multi-select-list-course-categories', 'initializeAssignTab')?.classList.add('hidden');
                            getElement('multi-select-list-assign-areas', 'initializeAssignTab')?.classList.add('hidden');
                            getElement('multi-select-list-assign-employees', 'initializeAssignTab')?.classList.add('hidden'); // NEW
                            console.log("Assign courses dropdown toggled.");
                        });

                        document.addEventListener('click', (e) => {
                            if (dropdownList && !dropdownList.contains(e.target) && e.target !== toggleButton) {
                                dropdownList.classList.add('hidden');
                            }
                        });
                        console.log("Assign courses dropdown listeners setup.");
                    } else {
                        console.warn("WARN in initializeAssignTab: Assign courses toggle button or dropdown list not found.");
                    }

                    // Event listener for individual course checkboxes
                    assignCourseChecklistContainer.addEventListener('change', (event) => {
                        if (event.target.name === 'assign-courses') {
                            updateSelectedAssignCoursesText();
                        }
                    });
                    console.log("Assign courses checkbox change listener set.");
                    updateSelectedAssignCoursesText(); // Initial update of button text
                } else {
                    console.error("ERROR in initializeAssignTab: Element 'assign-course-checkbox-list' not found.");
                }

                // Lógica para el desplegable de selección múltiple de áreas en la sección de Asignar Cursos
                const areaChecklistContainer = getElement('assign-area-checkbox-list', 'initializeAssignTab');
                if (areaChecklistContainer) {
                    areaChecklistContainer.innerHTML = '';
                    const allUniqueAreas = [...new Set(allEmployees.map(emp => emp.area))];

                    allUniqueAreas.forEach(areaName => {
                        const checkboxDiv = document.createElement('div');
                        checkboxDiv.className = 'checkbox-item flex items-center';
                        checkboxDiv.innerHTML = `
                            <input type="checkbox" id="assign-area-checkbox-${areaName.replace(/\s/g, '-')}" name="assign-areas-checkbox" value="${areaName}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="assign-area-checkbox-${areaName.replace(/\s/g, '-')}" class="text-sm text-gray-700 ml-2">${areaName}</label>
                        `;
                        areaChecklistContainer.appendChild(checkboxDiv);
                    });
                    console.log("Assign area checkboxes populated.");

                    const toggleButton = getElement('multi-select-toggle-assign-areas', 'initializeAssignTab');
                    const dropdownList = getElement('multi-select-list-assign-areas', 'initializeAssignTab');

                    if (toggleButton && dropdownList) {
                        toggleButton.addEventListener('click', (event) => {
                            event.stopPropagation();
                            dropdownList.classList.toggle('hidden');
                            // Close other dropdowns if open
                            getElement('multi-select-list-course-categories', 'initializeAssignTab')?.classList.add('hidden');
                            getElement('multi-select-list-assign-courses', 'initializeAssignTab')?.classList.add('hidden');
                            getElement('multi-select-list-assign-employees', 'initializeAssignTab')?.classList.add('hidden'); // NEW
                            console.log("Assign areas dropdown toggled.");
                        });

                        document.addEventListener('click', (e) => {
                            if (dropdownList && !dropdownList.contains(e.target) && e.target !== toggleButton) {
                                dropdownList.classList.add('hidden');
                            }
                        });
                        console.log("Assign areas dropdown listeners setup.");
                    } else {
                        console.warn("WARN in initializeAssignTab: Assign areas toggle button or dropdown list not found.");
                    }

                    document.querySelectorAll('input[name="assign-areas-checkbox"]').forEach(checkbox => {
                        checkbox.addEventListener('change', updateSelectedAssignAreasText);
                    });
                    console.log("Assign areas checkbox change listeners set.");
                    updateSelectedAssignAreasText();
                } else {
                    console.error("ERROR in initializeAssignTab: Element 'assign-area-checkbox-list' not found.");
                }

                // NEW: Lógica para el desplegable de selección múltiple de empleados en la sección de Asignar Cursos
                const assignEmployeeChecklistContainer = getElement('assign-employee-checkbox-list', 'initializeAssignTab');
                if (assignEmployeeChecklistContainer) {
                    renderAssignEmployeeList(); // Populate with all employees
                    console.log("Assign employee list populated.");

                    const toggleButton = getElement('multi-select-toggle-assign-employees', 'initializeAssignTab');
                    const dropdownList = getElement('multi-select-list-assign-employees', 'initializeAssignTab');
                    const selectAllCheckbox = getElement('assign-employee-all-checkbox', 'initializeAssignTab');

                    if (toggleButton && dropdownList && selectAllCheckbox) {
                        toggleButton.addEventListener('click', (event) => {
                            event.stopPropagation();
                            dropdownList.classList.toggle('hidden');
                            // Close other dropdowns if open
                            getElement('multi-select-list-course-categories', 'initializeAssignTab')?.classList.add('hidden');
                            getElement('multi-select-list-assign-courses', 'initializeAssignTab')?.classList.add('hidden');
                            getElement('multi-select-list-assign-areas', 'initializeAssignTab')?.classList.add('hidden');
                            console.log("Assign employees dropdown toggled.");
                        });

                        document.addEventListener('click', (e) => {
                            if (dropdownList && !dropdownList.contains(e.target) && e.target !== toggleButton) {
                                dropdownList.classList.add('hidden');
                            }
                        });
                        console.log("Assign employees dropdown listeners setup.");

                        selectAllCheckbox.addEventListener('change', (e) => {
                            document.querySelectorAll('#assign-employee-checkbox-list input[name="assign-employees"]').forEach(checkbox => {
                                checkbox.checked = e.target.checked;
                            });
                            updateSelectedAssignEmployeesText(); // Update button text after select all
                        });
                        console.log("Assign employee 'select all' listener set.");

                    } else {
                        console.warn("WARN in initializeAssignTab: Assign employees toggle button, dropdown list, or select all checkbox not found.");
                    }

                    document.querySelectorAll('input[name="assign-employees"]').forEach(checkbox => {
                        checkbox.addEventListener('change', updateSelectedAssignEmployeesText);
                    });
                    console.log("Assign employees checkbox change listeners set.");
                    updateSelectedAssignEmployeesText(); // Initial update
                } else {
                    console.error("ERROR in initializeAssignTab: Element 'assign-employee-checkbox-list' not found.");
                }

            } catch (e) {
                console.error(`An error occurred during initializeAssignTab: ${e.message}`, e);
            }
            console.log("--- END: initializeAssignTab() ---");
        }

        // Initialize Validate Tab
        function initializeValidateTab() {
            console.log("--- START: initializeValidateTab() ---");
            try {
                // Populate Area Checkboxes for Validate Tab
                const validateAreaChecklistContainer = getElement('validate-area-checkbox-list', 'initializeValidateTab');
                if (validateAreaChecklistContainer) {
                    validateAreaChecklistContainer.innerHTML = '';
                    const allUniqueAreas = [...new Set(allEmployees.map(emp => emp.area))];
                    allUniqueAreas.forEach(areaName => {
                        const checkboxDiv = document.createElement('div');
                        checkboxDiv.className = 'checkbox-item flex items-center';
                        checkboxDiv.innerHTML = `
                            <input type="checkbox" id="validate-area-checkbox-${areaName.replace(/\s/g, '-')}" name="validate-areas-checkbox" value="${areaName}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="validate-area-checkbox-${areaName.replace(/\s/g, '-')}" class="text-sm text-gray-700 ml-2">${areaName}</label>
                        `;
                        validateAreaChecklistContainer.appendChild(checkboxDiv);
                    });
                    console.log("Validate area checkboxes populated.");

                    const toggleButton = getElement('multi-select-toggle-validate-areas', 'initializeValidateTab');
                    const dropdownList = getElement('multi-select-list-validate-areas', 'initializeValidateTab');
                    if (toggleButton && dropdownList) {
                        toggleButton.addEventListener('click', (event) => {
                            event.stopPropagation();
                            dropdownList.classList.toggle('hidden');
                            getElement('multi-select-list-validate-course-categories', 'initializeValidateTab')?.classList.add('hidden');
                            getElement('multi-select-list-validate-courses', 'initializeValidateTab')?.classList.add('hidden');
                            console.log("Validate areas dropdown toggled.");
                        });
                        document.addEventListener('click', (e) => {
                            if (dropdownList && !dropdownList.contains(e.target) && e.target !== toggleButton) {
                                dropdownList.classList.add('hidden');
                            }
                        });
                        console.log("Validate areas dropdown listeners setup.");
                    } else {
                        console.warn("WARN in initializeValidateTab: Validate areas toggle button or dropdown list not found.");
                    }

                    document.querySelectorAll('input[name="validate-areas-checkbox"]').forEach(checkbox => {
                        checkbox.addEventListener('change', updateSelectedValidateAreasText);
                    });
                    console.log("Validate areas checkbox change listeners set.");
                    updateSelectedValidateAreasText(); // Initial update
                } else {
                    console.error("ERROR in initializeValidateTab: Element 'validate-area-checkbox-list' not found.");
                }

                // Populate Course Category Checkboxes for Validate Tab
                const validateCourseCategoryChecklistContainer = getElement('validate-course-category-checkbox-list', 'initializeValidateTab');
                if (validateCourseCategoryChecklistContainer) {
                    validateCourseCategoryChecklistContainer.innerHTML = '';
                    allCourseCategories.forEach(categoryName => {
                        const checkboxDiv = document.createElement('div');
                        checkboxDiv.className = 'checkbox-item flex items-center';
                        checkboxDiv.innerHTML = `
                            <input type="checkbox" id="validate-course-category-checkbox-${categoryName.replace(/\s/g, '-')}" name="validate-course-categories-checkbox" value="${categoryName}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="validate-course-category-checkbox-${categoryName.replace(/\s/g, '-')}" class="text-sm text-gray-700 ml-2">${categoryName}</label>
                        `;
                        validateCourseCategoryChecklistContainer.appendChild(checkboxDiv);
                    });
                    console.log("Validate course category checkboxes populated.");

                    const toggleButton = getElement('multi-select-toggle-validate-course-categories', 'initializeValidateTab');
                    const dropdownList = getElement('multi-select-list-validate-course-categories', 'initializeValidateTab');
                    if (toggleButton && dropdownList) {
                        toggleButton.addEventListener('click', (event) => {
                            event.stopPropagation();
                            dropdownList.classList.toggle('hidden');
                            getElement('multi-select-list-validate-areas', 'initializeValidateTab')?.classList.add('hidden');
                            getElement('multi-select-list-validate-courses', 'initializeValidateTab')?.classList.add('hidden');
                            console.log("Validate course categories dropdown toggled.");
                        });
                        document.addEventListener('click', (e) => {
                            if (dropdownList && !dropdownList.contains(e.target) && e.target !== toggleButton) {
                                dropdownList.classList.add('hidden');
                            }
                        });
                        console.log("Validate course categories dropdown listeners setup.");
                    } else {
                        console.warn("WARN in initializeValidateTab: Validate course categories toggle button or dropdown list not found.");
                    }

                    document.querySelectorAll('input[name="validate-course-categories-checkbox"]').forEach(checkbox => {
                        checkbox.addEventListener('change', updateSelectedValidateCourseCategoriesText);
                    });
                    console.log("Validate course category checkbox change listeners set.");
                    updateSelectedValidateCourseCategoriesText(); // Initial update
                } else {
                    console.error("ERROR in initializeValidateTab: Element 'validate-course-category-checkbox-list' not found.");
                }


                // Populate Course Checkboxes for Validate Tab
                const validateCourseChecklistContainer = getElement('validate-course-checkbox-list', 'initializeValidateTab');
                if (validateCourseChecklistContainer) {
                    validateCourseChecklistContainer.innerHTML = '';
                    const allUniqueCourses = [...new Set(coursesData.map(c => c.name))]; // Use courses from all available data
                    allUniqueCourses.forEach(courseName => {
                        const checkboxDiv = document.createElement('div');
                        checkboxDiv.className = 'checkbox-item flex items-center';
                        checkboxDiv.innerHTML = `
                            <input type="checkbox" id="validate-course-checkbox-${courseName.replace(/\s/g, '-')}" name="validate-courses-checkbox" value="${courseName}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="validate-course-checkbox-${courseName.replace(/\s/g, '-')}" class="text-sm text-gray-700 ml-2">${courseName}</label>
                        `;
                        validateCourseChecklistContainer.appendChild(checkboxDiv);
                    });
                    console.log("Validate course checkboxes populated.");

                    const toggleButton = getElement('multi-select-toggle-validate-courses', 'initializeValidateTab');
                    const dropdownList = getElement('multi-select-list-validate-courses', 'initializeValidateTab');
                    if (toggleButton && dropdownList) {
                        toggleButton.addEventListener('click', (event) => {
                            event.stopPropagation();
                            dropdownList.classList.toggle('hidden');
                            getElement('multi-select-list-validate-areas', 'initializeValidateTab')?.classList.add('hidden');
                            getElement('multi-select-list-validate-course-categories', 'initializeValidateTab')?.classList.add('hidden');
                            console.log("Validate courses dropdown toggled.");
                        });
                        document.addEventListener('click', (e) => {
                            if (dropdownList && !dropdownList.contains(e.target) && e.target !== toggleButton) {
                                dropdownList.classList.add('hidden');
                            }
                        });
                        console.log("Validate courses dropdown listeners setup.");
                    } else {
                        console.warn("WARN in initializeValidateTab: Validate courses toggle button or dropdown list not found.");
                    }

                    document.querySelectorAll('input[name="validate-courses-checkbox"]').forEach(checkbox => {
                        checkbox.addEventListener('change', updateSelectedValidateCoursesText);
                    });
                    console.log("Validate courses checkbox change listeners set.");
                    updateSelectedValidateCoursesText(); // Initial update
                } else {
                    console.error("ERROR in initializeValidateTab: Element 'validate-course-checkbox-list' not found.");
                }

                // Initial filter and render of validation list
                filterValidationList();

            } catch (e) {
                console.error(`An error occurred during initializeValidateTab: ${e.message}`, e);
            }
            console.log("--- END: initializeValidateTab() ---");
        }


        // Lógica de los nuevos filtros de score
        function setupScoreFilters() {
            console.log("--- START: setupScoreFilters() ---");
            try {
                // Llenar la lista de cursos para la selección múltiple
                const courseChecklistContainer = getElement('course-checkbox-list', 'setupScoreFilters');
                if (!courseChecklistContainer) {
                    console.error("ERROR in setupScoreFilters: Element 'course-checkbox-list' not found.");
                    return;
                }

                courseChecklistContainer.innerHTML = '';
                const allCourseNames = [...new Set(coursesData.map(c => c.name))];
                allCourseNames.forEach(courseName => {
                    const checkboxDiv = document.createElement('div');
                    checkboxDiv.className = 'checkbox-item flex items-center';
                    checkboxDiv.innerHTML = `
                        <input type="checkbox" id="score-course-${courseName.replace(/\s/g, '-')}" name="score-courses" value="${courseName}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="score-course-${courseName.replace(/\s/g, '-')}" class="text-sm text-gray-700 ml-2">${courseName}</label>
                    `;
                    courseChecklistContainer.appendChild(checkboxDiv);
                });
                console.log("Course checkboxes populated.");

                // Llenar la lista de áreas para la selección múltiple
                const areaChecklistContainer = getElement('area-checkbox-list', 'setupScoreFilters');
                if (!areaChecklistContainer) {
                    console.error("ERROR in setupScoreFilters: Element 'area-checkbox-list' not found.");
                    return;
                }

                areaChecklistContainer.innerHTML = '';
                const allAreas = [...new Set(allEmployees.map(emp => emp.area))];
                allAreas.forEach(areaName => {
                    const checkboxDiv = document.createElement('div');
                    checkboxDiv.className = 'checkbox-item flex items-center';
                    checkboxDiv.innerHTML = `
                        <input type="checkbox" id="score-area-${areaName.replace(/\s/g, '-')}" name="score-areas" value="${areaName}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="score-area-${areaName.replace(/\s/g, '-')}" class="text-sm text-gray-700 ml-2">${areaName}</label>
                    `;
                    areaChecklistContainer.appendChild(checkboxDiv);
                });
                console.log("Area checkboxes populated.");


                // Toggle para el dropdown de cursos
                const toggleButtonCourses = getElement('multi-select-toggle-courses', 'setupScoreFilters');
                const dropdownListCourses = getElement('multi-select-list-courses', 'setupScoreFilters');
                if (toggleButtonCourses && dropdownListCourses) {
                    toggleButtonCourses.addEventListener('click', (event) => {
                        event.stopPropagation(); // Evita que el clic se propague al documento y cierre el dropdown
                        dropdownListCourses.classList.toggle('hidden');
                        const dropdownListAreas = getElement('multi-select-list-areas', 'setupScoreFilters (internal)');
                        if (dropdownListAreas) dropdownListAreas.classList.add('hidden'); // Cierra el otro dropdown
                        console.log("Course dropdown toggled.");
                    });
                } else {
                    console.warn("WARN in setupScoreFilters: Course toggle button or dropdown list not found.");
                }

                // Toggle para el dropdown de áreas
                const toggleButtonAreas = getElement('multi-select-toggle-areas', 'setupScoreFilters');
                const dropdownListAreas = getElement('multi-select-list-areas', 'setupScoreFilters');
                if (toggleButtonAreas && dropdownListAreas) {
                    toggleButtonAreas.addEventListener('click', (event) => {
                        event.stopPropagation(); // Evita que el clic se propague al documento y cierre el dropdown
                        dropdownListAreas.classList.toggle('hidden');
                        const dropdownListCourses = getElement('multi-select-list-courses', 'setupScoreFilters (internal)');
                        if (dropdownListCourses) dropdownListCourses.classList.add('hidden'); // Cierra el otro dropdown
                        console.log("Area dropdown toggled.");
                    });
                } else {
                    console.warn("WARN in setupScoreFilters: Area toggle button or dropdown list not found.");
                }

                // Cerrar dropdowns al hacer clic fuera (ahora dentro de setupScoreFilters)
                document.addEventListener('click', (e) => {
                    if (dropdownListCourses && !dropdownListCourses.contains(e.target) && e.target !== toggleButtonCourses) {
                        dropdownListCourses.classList.add('hidden');
                    }
                    if (dropdownListAreas && !dropdownListAreas.contains(e.target) && e.target !== toggleButtonAreas) {
                        dropdownListAreas.classList.add('hidden');
                    }
                });
                console.log("Local click listener for dropdowns set.");

                // Event listener para el botón de aplicar filtros
                const applyFiltersBtn = getElement('apply-filters-btn', 'setupScoreFilters');
                if (applyFiltersBtn) {
                    applyFiltersBtn.addEventListener('click', renderCourseScores);
                    console.log("Apply Filters button listener set.");
                } else {
                    console.error("ERROR in setupScoreFilters: Element 'apply-filters-btn' not found.");
                }

                // Inicializar el texto del botón de selección múltiple de cursos
                updateSelectedCoursesText();
                document.querySelectorAll('input[name="score-courses"]').forEach(checkbox => {
                    checkbox.addEventListener('change', updateSelectedCoursesText);
                });
                console.log("Course checkbox change listeners set.");

                // Inicializar el texto del botón de selección múltiple de áreas
                updateSelectedAreasText();
                document.querySelectorAll('input[name="score-areas"]').forEach(checkbox => {
                    checkbox.addEventListener('change', updateSelectedAreasText);
                });
                console.log("Area checkbox change listeners set.");

                // Lógica para el toggle de la sección de filtros
                const filterHeader = getElement('filter-header', 'setupScoreFilters');
                const filterContent = getElement('filter-content', 'setupScoreFilters');
                const filterArrow = getElement('filter-arrow', 'setupScoreFilters');

                if (filterHeader && filterContent && filterArrow) {
                    filterHeader.addEventListener('click', () => {
                        filterContent.classList.toggle('hidden');
                        filterArrow.classList.toggle('rotate-180');
                        console.log("Filter header toggled.");
                    });
                } else {
                    console.warn("WARN in setupScoreFilters: Filter header, content, or arrow not found.");
                }

                // Llama a renderCourseScores para mostrar los resultados iniciales al cargar la pestaña
                renderCourseScores();
            } catch (e) {
                console.error(`An error occurred during setupScoreFilters: ${e.message}`, e);
            }
            console.log("--- END: setupScoreFilters() ---");
        }

        // NEW: Function to clear all filters in the Assign Courses section
        function clearAssignFilters() {
            console.log("--- START: clearAssignFilters() ---");
            document.querySelectorAll('input[name="assign-course-categories"]').forEach(checkbox => checkbox.checked = false);
            document.querySelectorAll('input[name="assign-courses"]').forEach(checkbox => checkbox.checked = false);
            document.querySelectorAll('input[name="assign-areas-checkbox"]').forEach(checkbox => checkbox.checked = false);
            document.querySelectorAll('input[name="assign-employees"]').forEach(checkbox => checkbox.checked = false);

            const assignEmployeeAllCheckbox = getElement('assign-employee-all-checkbox', 'clearAssignFilters');
            if (assignEmployeeAllCheckbox) assignEmployeeAllCheckbox.checked = false;

            updateSelectedAssignCoursesText();
            updateSelectedAssignAreasText();
            updateSelectedAssignEmployeesText();

            // Re-render courses based on cleared categories
            filterAssignCoursesByCategoryAndRender();
            console.log("--- END: clearAssignFilters() ---");
        }

        // NEW: Function to clear all filters in the Matrix tab (placeholder)
        function clearMatrixFilters() {
            console.log("--- START: clearMatrixFilters() ---");
            console.log("Limpiando filtros de la Matriz de Capacitación (funcionalidad pendiente).");
            // Add actual filter clearing logic here when matrix filters are implemented
            console.log("--- END: clearMatrixFilters() ---");
        }

        // NEW: Generic function to clear filters based on tab ID
        function clearSpecificTabFilters(tabId) {
            console.log(`Calling clear filters for tab: ${tabId}`);
            switch(tabId) {
                case 'create-edit':
                    console.log("No filters to clear in 'Crear o Editar' tab yet.");
                    break;
                case 'assign':
                    clearAssignFilters();
                    break;
                case 'validate':
                    clearValidateFilters();
                    break;
                case 'score':
                    clearScoreFilters();
                    break;
                case 'matrix':
                    clearMatrixFilters();
                    break;
                default:
                    console.warn(`Unknown tabId for clearing filters: ${tabId}`);
            }
        }

        // Inicializar la página para mostrar la bienvenida por defecto
        window.onload = function() {
            console.log("--- Window loaded. Initializing application state. ---");
            goToHomePage(); // Llama a esta función para establecer el estado inicial
            // Las funciones de setup ahora se llaman solo cuando se activa su pestaña.
            // No es necesario llamarlas aquí globalmente al inicio.
            console.log("--- Application initial state set. ---");
        };
    </script>
</body>
</html>
