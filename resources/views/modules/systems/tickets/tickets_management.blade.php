@extends('modules.systems.tickets.index')

@section('content')
    <style>
        :root {
            /* --- PALETA VINCO ENERGY (BURDEOS) --- */
            --primary-burgundy: #181466;
            --burgundy-dark: #100d47;
            --burgundy-soft: #f4f2fd;
            --primary-accent: #d4af37;
            /* Dorado */

            --white: #ffffff;
            --light-gray: #f8fafc;
            --border-color: #e2e8f0;

            /* === 6 ESTADOS DEFINIDOS === */
            --status-new: #8b5cf6;
            /* Púrpura (Nuevo) */
            --status-open: #3b82f6;
            /* Azul (Abierto) */
            --status-waiting: #f59e0b;
            /* Naranja (En Espera) */
            --status-pending: #ef4444;
            /* Rojo (Por Concluir) */
            --status-success: #10b981;
            /* Verde (Realizado) */
            --status-cancelled: #6b7280;
            /* Gris (Cancelado) */

            --status-waiting: #d97706;
            --status-pending: #dc2626;
            --status-success: #16a34a;
        }

        /* === LAYOUT PRINCIPAL === */
        .content.active {
            background: var(--light-gray);
            min-height: 100vh;
        }

        .tickets-layout {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 25px;
            align-items: start;
            transition: grid-template-columns 0.4s ease-in-out;
            margin-top: 25px;
        }

        .tickets-layout.form-hidden {
            grid-template-columns: 0 1fr;
        }

        /* Estilo Base de la Tarjeta */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            /* Cambiado a 4 columnas fijas para igualar tamaño */
            gap: 1.5rem;
            padding: 1rem;
            background: #f8fafc;
        }

        /* Base Card Style */
        .stat-card {
            background: var(--white);
            border-radius: 16px;
            padding: 1.25rem;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
        }

        /* Card Main (The Featured One) */
        .card-main {
            grid-column: span 1;
            /* Cambiado de span 2 a span 1 para que ocupe una columna */
            background: linear-gradient(135deg, var(--primary-burgundy) 0%, var(--burgundy-dark) 100%);
            color: white;
        }

        .card-main .stat-label {
            color: rgba(255, 255, 255, 0.8);
        }

        .card-main .stat-value {
            color: white;
            font-size: 2rem;
        }

        /* Ajustado para igualar tamaño */
        .card-main .stat-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 10px;
            font-size: 0.75rem;
            opacity: 0.8;
        }

        /* Content Layout */
        .stat-body {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .stat-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: #1e293b;
            margin: 0.25rem 0;
        }

        /* Icons Styles */
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        /* Specific Status Colors */
        .status-waiting .stat-icon {
            background: #fff7ed;
            color: var(--status-waiting);
        }

        .status-pending .stat-icon {
            background: #fef2f2;
            color: var(--status-pending);
        }

        .status-success .stat-icon {
            background: #ecfdf5;
            color: var(--status-success);
        }

        /* Decorative Indicator Line */
        .stat-indicator {
            font-size: 0.75rem;
            color: #64748b;
            padding-top: 0.75rem;
            border-top: 1px solid #f1f5f9;
            position: relative;
        }

        /* Trend indicator for the main card */
        .stat-trend {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.85rem;
            margin-top: 4px;
        }

        .stat-trend.positive {
            color: #34d399;
        }

        /* Icon Blob Effect for main card */
        .icon-blob {
            background: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            backdrop-filter: blur(4px);
        }

        /* === FORMULARIO LATERAL === */
        .form-section {
            background: var(--white);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
            position: sticky;
            top: 20px;
            max-height: 85vh;
            overflow-y: auto;
            min-width: 380px;
            opacity: 1;
            transform: translateX(0);
            transition: all 0.4s ease-in-out;
        }

        .tickets-layout.form-hidden .form-section {
            opacity: 0;
            transform: translateX(-100%);
            padding: 0;
            min-width: 0;
            overflow: hidden;
            border: none;
        }

        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--burgundy-soft);
            padding-bottom: 10px;
        }

        .form-header h3 {
            color: var(--primary-burgundy);
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
        }

        .btn-close-form {
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            font-size: 1.2rem;
            transition: 0.2s;
        }

        .btn-close-form:hover {
            color: var(--status-pending);
            transform: scale(1.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--burgundy-dark);
            margin-bottom: 5px;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.9rem;
            transition: 0.3s;
            color: #1e293b;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-burgundy);
            box-shadow: 0 0 0 3px var(--burgundy-soft);
        }

        .form-static-control {
            background: var(--light-gray);
            padding: 10px;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #1e293b;
            font-weight: 600;
            border: 1px dashed var(--border-color);
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .form-actions .btn {
            flex: 1;
            justify-content: center;
        }

        /* === TABLA === */
        .data-section {
            background: var(--white);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            min-width: 0;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-header h3 {
            color: var(--primary-burgundy);
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0;
        }

        .table-controls {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-box {
            flex-grow: 1;
            position: relative;
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }

        .search-box input {
            width: 100%;
            padding: 10px 10px 10px 35px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
        }

        .main-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .main-table th {
            background: var(--primary-burgundy);
            color: white;
            padding: 15px;
            text-align: left;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .main-table th:first-child {
            border-top-left-radius: 10px;
        }

        .main-table th:last-child {
            border-top-right-radius: 10px;
        }

        .main-table td {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            color: #1e293b;
            font-size: 0.9rem;
            transition: 0.2s;
        }

        .main-table tr:hover td {
            background-color: var(--burgundy-soft);
        }

        .main-table tr:last-child td {
            border-bottom: none;
        }

        /* === ETIQUETAS DE ESTADO (6 ESTADOS) === */
        .status-tag {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: white;
        }

        .nuevo {
            background: var(--status-new);
        }

        /* Púrpura */

        .abierto {
            background: var(--status-open);
        }

        /* Azul */

        .en-espera {
            background: var(--status-waiting);
        }

        /* Naranja */

        .por-concluir {
            background: var(--status-pending);
        }

        /* Rojo */

        .realizado {
            background: var(--status-success);
        }

        /* Verde */

        .cancelado {
            background: var(--status-cancelled);
        }

        /* Gris */

        /* Prioridades */
        .priority-high {
            color: var(--status-pending);
            font-weight: 800;
            background: rgba(239, 68, 68, 0.1);
            padding: 4px 8px;
            border-radius: 4px;
        }

        .priority-medium {
            color: var(--primary-accent);
            font-weight: 700;
            background: rgba(212, 175, 55, 0.1);
            padding: 4px 8px;
            border-radius: 4px;
        }

        .priority-low {
            color: var(--status-success);
            font-weight: 600;
            background: rgba(16, 185, 129, 0.1);
            padding: 4px 8px;
            border-radius: 4px;
        }

        /* Botones */
        .btn {
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            border: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }

        .btn-primary {
            background: var(--primary-burgundy);
            color: white;
        }

        .btn-primary:hover {
            background: var(--burgundy-dark);
            box-shadow: 0 4px 10px rgba(99, 18, 45, 0.3);
        }

        .btn-secondary {
            background: var(--light-gray);
            color: #1e293b;
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .btn-danger {
            background: var(--status-pending);
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .table-actions button {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
            margin: 0 2px;
        }

        .btn-view {
            background: var(--burgundy-soft);
            color: var(--primary-burgundy);
        }

        .btn-edit {
            background: #e5e7eb;
            color: #4b5563;
        }

        .btn-cancel {
            background: #fee2e2;
            color: #dc2626;
        }

        .btn-view:hover,
        .btn-edit:hover,
        .btn-cancel:hover {
            transform: scale(1.1);
        }
    </style>

    <div class="content active">

        {{-- Resumen de Estadísticas --}}
        <div class="stats-grid">
            <div class="stat-card card-main">
                <div class="stat-body">
                    <div class="stat-content">
                        <span class="stat-label">Total de Tickets</span>
                        <h2 class="stat-value">156</h2>
                    </div>
                    <div class="stat-visual">
                        <div class="icon-blob">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="stat-footer">
                    <span>Resumen de los últimos 30 días</span>
                </div>
            </div>

            <div class="stat-card status-waiting">
                <div class="stat-body">
                    <div class="stat-content">
                        <span class="stat-label">En Espera</span>
                        <h2 class="stat-value">5</h2>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-indicator">Esperando información</div>
            </div>

            <div class="stat-card status-pending">
                <div class="stat-body">
                    <div class="stat-content">
                        <span class="stat-label">Por Concluir</span>
                        <h2 class="stat-value">4</h2>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="stat-indicator">Requieren acción inmediata</div>
            </div>

            <div class="stat-card status-success">
                <div class="stat-body">
                    <div class="stat-content">
                        <span class="stat-label">Realizados</span>
                        <h2 class="stat-value">118</h2>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-indicator">Tickets completados</div>
            </div>
        </div>
        {{-- Layout Principal --}}
        <div class="tickets-layout" id="ticketsLayout">

            {{-- COLUMNA 1: Formulario Lateral --}}
            <div class="form-section">
                <div class="form-header">
                    <h3><i class="fas fa-plus-circle"></i> Nuevo Ticket</h3>
                    <button type="button" class="btn-close-form" onclick="toggleTicketForm()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="ticket-form">
                    @csrf
                    <div class="form-group">
                        <label>Solicitante</label>
                        <div class="form-static-control"><i class="fas fa-user"></i> Admin Logeado
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Departamento</label>
                        <div class="form-static-control"><i class="fas fa-building"></i> Sistemas
                            (SIS)</div>
                    </div>

                    <div class="form-group">
                        <label for="prioridad">Prioridad *</label>
                        <select id="prioridad" class="form-control" required>
                            <option value="">Seleccionar...</option>
                            <option value="alta">Alta</option>
                            <option value="media">Media</option>
                            <option value="baja">Baja</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="asunto">Asunto *</label>
                        <input type="text" id="asunto" class="form-control" placeholder="Resumen del fallo..."
                            required>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Detalle *</label>

                        <textarea id="descripcion" class="form-control" rows="3" placeholder="Describa el incidente..." required></textarea>

                    </div>

                    <div class="form-group">
                        <label>Estado Inicial</label>
                        {{-- 6 ESTADOS --}}
                        <select id="estado" class="form-control" onchange="checkStatus(this.value)">
                            <option value="nuevo" selected>1. Nuevo</option>
                            <option value="abierto">2. Abierto</option>
                            <option value="en-espera">3. En Espera</option>
                            <option value="por-concluir">4. Por Concluir</option>
                            <option value="realizado">5. Realizado</option>
                            <option value="cancelado">6. Cancelado</option>
                        </select>
                    </div>

                    <div class="form-group" id="comentario-group" style="display:none;">
                        <label id="comentario-label">Comentario *</label>

                        <textarea id="comentario" class="form-control" rows="2" placeholder="Agregue un comentario..."></textarea>

                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                        <button type="reset" class="btn btn-secondary"><i class="fas fa-eraser"></i></button>
                    </div>
                </form>
            </div>

            {{-- COLUMNA 2: Tabla de Datos --}}
            <section class="data-section">
                <div class="section-header">
                    <h3>Gestión de Tickets</h3>
                    <div class="header-actions">

                    </div>
                </div>

                <div class="table-controls">
                    <button class="btn btn-primary" onclick="toggleTicketForm()">
                        <i class="fas fa-plus"></i> Nuevo Ticket
                    </button>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="buscador" placeholder="Buscar ticket, usuario o ID...">
                    </div>

                    {{-- Filtro con 6 estados --}}
                    <select id="filtro-estado" class="form-control" style="width: auto;">
                        <option value="">Estado: Todos</option>
                        <option value="nuevo">Nuevo</option>
                        <option value="abierto">Abierto</option>
                        <option value="en-espera">En Espera</option>
                        <option value="por-concluir">Por Concluir</option>
                        <option value="realizado">Realizado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>

                    <select id="filtro-prioridad" class="form-control" style="width: auto;">
                        <option value="">Prioridad: Todas</option>
                        <option value="alta">Alta</option>
                        <option value="media">Media</option>
                        <option value="baja">Baja</option>
                    </select>

                    <button class="btn btn-secondary" onclick="exportarTabla()">
                        <i class="fas fa-file-export"></i> Exportar
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="main-table" id="tabla-tickets">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Folio</th>
                                <th>Asunto</th>
                                <th>Usuario</th>
                                <th>Depto</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td><strong>ADM-001</strong></td>
                                <td>Error en Outlook</td>
                                <td>Ana Garcia</td>
                                <td>Administración</td>
                                <td><span class="priority-medium">Media</span></td>
                                <td><span class="status-tag en-espera">En Espera</span></td>
                                <td class="table-actions">
                                    <button class="btn-view" title="Ver" onclick="verTicket(1)"><i
                                            class="fas fa-eye"></i></button>
                                    <button class="btn-edit" title="Editar" onclick="editarTicket(1)"><i
                                            class="fas fa-edit"></i></button>
                                    <button class="btn-cancel" title="Cancelar" onclick="cancelarTicket(1)"><i
                                            class="fas fa-ban"></i></button>
                                </td>
                            </tr>

                            <tr>
                                <td>2</td>
                                <td><strong>DG-045</strong></td>
                                <td>Acceso a Servidor</td>
                                <td>Mario Lopez</td>
                                <td>Dirección General</td>
                                <td><span class="priority-high">Alta</span></td>
                                <td><span class="status-tag abierto">Abierto</span></td>
                                <td class="table-actions">
                                    <button class="btn-view" title="Ver" onclick="verTicket(2)"><i
                                            class="fas fa-eye"></i></button>
                                    <button class="btn-edit" title="Editar" onclick="editarTicket(2)"><i
                                            class="fas fa-edit"></i></button>
                                    <button class="btn-cancel" title="Cancelar" onclick="cancelarTicket(2)"><i
                                            class="fas fa-ban"></i></button>
                                </td>
                            </tr>

                            <tr>
                                <td>3</td>
                                <td><strong>WT-102</strong></td>
                                <td>Calibración Sensor</td>
                                <td>Juan Perez</td>
                                <td>Well Testing</td>
                                <td><span class="priority-high">Alta</span></td>
                                <td><span class="status-tag por-concluir">Por Concluir</span></td>
                                <td class="table-actions">
                                    <button class="btn-view" title="Ver" onclick="verTicket(3)"><i
                                            class="fas fa-eye"></i></button>
                                    <button class="btn-edit" title="Editar" onclick="editarTicket(3)"><i
                                            class="fas fa-edit"></i></button>
                                    <button class="btn-cancel" title="Cancelar" onclick="cancelarTicket(3)"><i
                                            class="fas fa-ban"></i></button>
                                </td>
                            </tr>

                            <tr>
                                <td>4</td>
                                <td><strong>OPR-099</strong></td>
                                <td>Solicitud completada</td>
                                <td>Lucia Mendez</td>
                                <td>Operaciones</td>
                                <td><span class="priority-low">Baja</span></td>
                                <td><span class="status-tag realizado">Realizado</span></td>
                                <td class="table-actions">
                                    <button class="btn-view" title="Ver" onclick="verTicket(4)"><i
                                            class="fas fa-eye"></i></button>
                                    <button class="btn-edit" title="Editar" onclick="editarTicket(4)"><i
                                            class="fas fa-edit"></i></button>
                                    <button class="btn-cancel" title="Cancelar" onclick="cancelarTicket(4)"><i
                                            class="fas fa-ban"></i></button>
                                </td>
                            </tr>

                            <tr>
                                <td>5</td>
                                <td><strong>QHSE-023</strong></td>
                                <td>Reporte de Incidente</td>
                                <td>Carlos Ruiz</td>
                                <td>QHSE</td>
                                <td><span class="priority-medium">Media</span></td>
                                <td><span class="status-tag nuevo">Nuevo</span></td>
                                <td class="table-actions">
                                    <button class="btn-view" title="Ver" onclick="verTicket(5)"><i
                                            class="fas fa-eye"></i></button>
                                    <button class="btn-edit" title="Editar" onclick="editarTicket(5)"><i
                                            class="fas fa-edit"></i></button>
                                    <button class="btn-cancel" title="Cancelar" onclick="cancelarTicket(5)"><i
                                            class="fas fa-ban"></i></button>
                                </td>
                            </tr>

                            <tr>
                                <td>6</td>
                                <td><strong>SUM-078</strong></td>
                                <td>Pedido duplicado</td>
                                <td>Pedro Martinez</td>
                                <td>Suministros</td>
                                <td><span class="priority-low">Baja</span></td>
                                <td><span class="status-tag cancelado">Cancelado</span></td>
                                <td class="table-actions">
                                    <button class="btn-view" title="Ver" onclick="verTicket(6)"><i
                                            class="fas fa-eye"></i></button>
                                    <button class="btn-edit" title="Editar" onclick="editarTicket(6)"><i
                                            class="fas fa-edit"></i></button>
                                    <button class="btn-cancel" title="Cancelar" onclick="cancelarTicket(6)"><i
                                            class="fas fa-ban"></i></button>
                                </td>
                            </tr>

                            <tr>
                                <td>7</td>
                                <td><strong>GEO-112</strong></td>
                                <td>Análisis Sísmico</td>
                                <td>Sofía Lima</td>
                                <td>Geociencias</td>
                                <td><span class="priority-high">Alta</span></td>
                                <td><span class="status-tag nuevo">Nuevo</span></td>
                                <td class="table-actions">
                                    <button class="btn-view" title="Ver" onclick="verTicket(7)"><i
                                            class="fas fa-eye"></i></button>
                                    <button class="btn-edit" title="Editar" onclick="editarTicket(7)"><i
                                            class="fas fa-edit"></i></button>
                                    <button class="btn-cancel" title="Cancelar" onclick="cancelarTicket(7)"><i
                                            class="fas fa-ban"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Configuración inicial
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar con el formulario oculto
            document.getElementById('ticketsLayout').classList.add('form-hidden');
        });

        // Lógica para mostrar/ocultar el formulario
        function toggleTicketForm() {
            const layout = document.getElementById('ticketsLayout');
            layout.classList.toggle('form-hidden');

            // Si el formulario se muestra, resetearlo
            if (!layout.classList.contains('form-hidden')) {
                document.getElementById('ticket-form').reset();
                document.getElementById('comentario-group').style.display = 'none';
            }
        }

        // Lógica para mostrar campo de comentario según el estado
        function checkStatus(val) {
            const commentGroup = document.getElementById('comentario-group');
            const commentLabel = document.getElementById('comentario-label');
            const commentField = document.getElementById('comentario');

            const estadosConComentario = ['por-concluir', 'cancelado'];

            if (estadosConComentario.includes(val)) {
                commentGroup.style.display = 'block';
                if (val === 'por-concluir') {
                    commentLabel.textContent = 'Motivo de "Por Concluir" *';
                    commentField.placeholder = '¿Qué impide cerrar el ticket? (Ej: Espera de refacción)';
                } else if (val === 'cancelado') {
                    commentLabel.textContent = 'Motivo de Cancelación *';
                    commentField.placeholder = 'Explique el motivo de la cancelación...';
                }
            } else {
                commentGroup.style.display = 'none';
            }
        }

        // Manejar envío del formulario
        document.getElementById('ticket-form').addEventListener('submit', function(e) {
            e.preventDefault();

            // Validar comentario si es necesario
            const estado = document.getElementById('estado').value;
            const comentario = document.getElementById('comentario').value;

            if ((estado === 'por-concluir' || estado === 'cancelado') && !comentario.trim()) {
                Swal.fire({
                    title: 'Campo requerido',
                    text: 'Debe agregar un comentario para este estado',
                    icon: 'warning',
                    confirmButtonColor: '#63122D'
                });
                return;
            }

            Swal.fire({
                title: '¡Registrado!',
                text: 'El ticket ha sido guardado exitosamente.',
                icon: 'success',
                confirmButtonColor: '#63122D'
            }).then((result) => {
                toggleTicketForm();
            });
        });

        // Funciones para acciones de tabla
        function verTicket(id) {
            Swal.fire({
                title: 'Ticket #' + id,
                text: 'Mostrando detalles del ticket',
                icon: 'info',
                confirmButtonColor: '#63122D'
            });
        }

        function editarTicket(id) {
            Swal.fire({
                title: 'Editar Ticket',
                text: 'Editando ticket #' + id,
                icon: 'info',
                confirmButtonColor: '#63122D'
            });
        }

        function cancelarTicket(id) {
            Swal.fire({
                title: '¿Cancelar ticket?',
                text: "Esta acción no se puede revertir",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No, mantener'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire(
                        '¡Cancelado!',
                        'El ticket ha sido cancelado.',
                        'success'
                    );
                }
            });
        }

        // Función de búsqueda en tabla
        document.getElementById('buscador').addEventListener('keyup', function() {
            const input = this.value.toLowerCase();
            const rows = document.querySelectorAll('#tabla-tickets tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(input) ? '' : 'none';
            });
        });

        // Filtro por estado
        document.getElementById('filtro-estado').addEventListener('change', function() {
            filtrarTabla();
        });

        // Filtro por prioridad
        document.getElementById('filtro-prioridad').addEventListener('change', function() {
            filtrarTabla();
        });

        function filtrarTabla() {
            const estadoFiltro = document.getElementById('filtro-estado').value.toLowerCase();
            const prioridadFiltro = document.getElementById('filtro-prioridad').value.toLowerCase();
            const rows = document.querySelectorAll('#tabla-tickets tbody tr');

            rows.forEach(row => {
                let mostrar = true;

                // Filtrar por estado
                if (estadoFiltro) {
                    const estadoCell = row.querySelector('td:nth-child(7) span');
                    if (estadoCell) {
                        const estadoTexto = estadoCell.textContent.toLowerCase().trim();
                        if (!estadoTexto.includes(estadoFiltro.replace('-', ' '))) {
                            mostrar = false;
                        }
                    }
                }

                // Filtrar por prioridad
                if (mostrar && prioridadFiltro) {
                    const prioridadCell = row.querySelector('td:nth-child(6) span');
                    if (prioridadCell) {
                        const prioridadTexto = prioridadCell.textContent.toLowerCase();
                        if (!prioridadTexto.includes(prioridadFiltro)) {
                            mostrar = false;
                        }
                    }
                }

                row.style.display = mostrar ? '' : 'none';
            });
        }

        // Función para exportar tabla
        function exportarTabla() {
            Swal.fire({
                title: 'Exportar Datos',
                text: 'Seleccione el formato de exportación',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#63122D',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Excel',
                cancelButtonText: 'PDF'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Exportado', 'Datos exportados a Excel', 'success');
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire('Exportado', 'Datos exportados a PDF', 'success');
                }
            });
        }
    </script>
@endpush
