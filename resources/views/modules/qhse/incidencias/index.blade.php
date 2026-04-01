<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('assets/css/qhse/incidencias/incidencias.css') }}">
</head>

<body>
    <!-- Header Section -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <img src="/modulos/qhse/img/logovinco1.png" alt="Logo Empresa" />
                <h2 class="dashboard-title"></h2>
            </div>
            <nav class="header-nav">
                <ul>
                    <li>
                        <a href="#" class="nav-link">
                            <i class="fas fa-home"></i>
                            <span>Inicio</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link">
                            <i class="fas fa-tasks"></i>
                            <span>Reporte de Acciones Abiertas</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link">
                            <i class="fas fa-clipboard-check"></i>
                            <span>Monitoreo, Verificación y Evaluación</span>
                        </a>
                    </li>
                    <li>
                        <a href="../view/catalogos.html" class="nav-link">
                            <i class="fas fa-book"></i>
                            <span>Catálogos</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link">
                            <i class="fas fa-chart-pie"></i>
                            <span>Estadísticas</span>
                        </a>
                    </li>
                </ul>
            </nav>
                        @include('components.layouts._user-profile')

        </div>
    </header>

    <main class="main-container">
        <!-- Dashboard Stats -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-title">Total Reportes</div>
                    <div class="stat-value">1,284</div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i>
                        12.5% vs mes anterior
                    </div>
                </div>
                <div class="mini-chart"></div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-title">Reportes Abiertos</div>
                    <div class="stat-value">45</div>
                    <div class="stat-trend down">
                        <i class="fas fa-arrow-down"></i>
                        5.2% vs mes anterior
                    </div>
                </div>
                <div class="mini-chart"></div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-title">Riesgo Alto</div>
                    <div class="stat-value">12</div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i>2.1% vs mes anterior
                    </div>
                </div>
                <div class="mini-chart"></div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-title">Completados Hoy</div>
                    <div class="stat-value">8</div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i>15.8% vs ayer
                    </div>
                </div>
                <div class="mini-chart"></div>
            </div>
        </div>

        <section class="reports">
            <div class="search">
                <h2 class="search-title">
                    Sistema de Reportes de Acciones Correctivas
                </h2>
                <div class="search-form">
                    <div class="search-content">
                        <div class="field-group">
                            <div class="field">
                                <label>Responsable</label>
                                <select>
                                    <option value="">Seleccionar</option>
                                    <option value="1">Alberto Jiménez</option>
                                    <option value="2">Ana García</option>
                                    <option value="3">Carlos Martínez</option>
                                    <option value="4">Diana López</option>
                                    <option value="5">Eduardo Torres</option>
                                </select>
                            </div>
                            <div class="field">
                                <label>Año</label>
                                <select>
                                    <option value="2024">2024</option>
                                    <option value="2023">2023</option>
                                    <option value="2022">2022</option>
                                    <option value="2021">2021</option>
                                </select>
                            </div>
                            <div class="field">
                                <label>Número de Reporte</label>
                                <input type="text" placeholder="Ingrese número" />
                            </div>
                        </div>
                        <div class="search-actions">
                            <button type="button" class="btn-reset">
                                <i class="fas fa-rotate"></i>
                                Restablecer
                            </button>
                            <button type="button" class="btn-new">
                                <i class="fas fa-plus"></i>
                                Nuevo
                            </button>
                            <button type="submit" class="btn-search">
                                <i class="fas fa-search"></i>
                                Buscar
                            </button>
                            <button class="btn-export">
                                <i class="fas fa-file-export"></i>
                                Exportar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="cont-tabla">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Num</th>
                                    <th>Mes</th>
                                    <th>Fecha</th>
                                    <th>Categoría</th>
                                    <th>Tipo</th>
                                    <th>Condición</th>
                                    <th>Título</th>
                                    <th>Peligro</th>
                                    <th>Riesgo</th>
                                    <th>Nivel</th>
                                    <th>Reporta</th>
                                    <th>Estado</th>
                                    <th>Ver Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="numr">1</td>
                                    <td>Oct</td>
                                    <td>21/10/2024</td>
                                    <td><span class="tag tag-quality">CALIDAD</span></td>
                                    <td>Condición</td>
                                    <td><span class="tag tag-unsafe">INSEGURO</span></td>
                                    <td>POLYVORIN 2 ALMACENAMIENTO</td>
                                    <td><span class="tag tag-explosive">Explosivos</span></td>
                                    <td>Daño a materiales</td>
                                    <td><span class="tag tag-high">Muy Alto</span></td>
                                    <td>Alberto Jiménez</td>
                                    <td><span class="tag tag-closed">Cerrado</span></td>
                                    <td class="cont-accion">
                                        <button class="ver-btn">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination">
                        <button>Anterior</button>
                        <button class="active">1</button>
                        <button>2</button>
                        <button>3</button>
                        <span>...</span>
                        <button>10</button>
                        <button>Siguiente</button>
                    </div>
                </div>
            </div>
        </section>
    </main>


    <div class="reporte-modal">
        <div class="reporte-modal-content">
            <div class="reporte-modal-header">
                <h2>
                    <i class="material-icons">assignment</i>
                    Captura de Reporte
                </h2>
                <button class="reporte-modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="column">
                <div class="form-group">
                    <label for="num-reporte">
                        <i class="bx bx-hash"></i>
                        Número de Reporte
                    </label>
                    <input type="text" id="num-reporte" name="num-reporte" required
                        placeholder="Ingrese número de reporte">
                </div>

                <div class="form-group">
                    <label for="titulo-reporte">
                        <i class="material-icons">title</i>
                        Titulo del Reporte
                    </label>
                    <input type="text" id="titulo-reporte" name="titulo-reporte" required
                        placeholder="Ingrese título del reporte">
                </div>

                <div class="form-group">
                    <label for="categoria">
                        <i class="bx bx-tag"></i>
                        Categoría
                    </label>
                    <select id="categoria" name="categoria" required>
                        <option value="">Seleccione una categoría</option>
                        <option value="calidad">CALIDAD</option>
                        <option value="seguridad">Seguridad</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tipo-tarjeta">
                        <i class="material-icons">credit_card</i>
                        Tipo Tarjeta
                    </label>
                    <select id="tipo-tarjeta" name="tipo-tarjeta" required>
                        <option value="">Seleccione un Tipo de Tarjeta</option>
                        <option value="tipo1">Tipo 1</option>
                        <option value="tipo2">Tipo 2</option>
                        <option value="tipo3">Tipo 3</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tipo-condicion">
                        <i class="bx bx-check-shield"></i>
                        Tipo Condición
                    </label>
                    <select id="tipo-condicion" name="tipo-condicion" required>
                        <option value="">Seleccione un Tipo de Condición</option>
                        <option value="seguro">Seguro</option>
                        <option value="inseguro">Inseguro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="peligro">
                        <i class="material-icons">warning</i>
                        Peligro
                    </label>
                    <select id="peligro" name="peligro" required>
                        <option value="">Seleccione un Peligro</option>
                        <option value="caida">Caida de objetos</option>
                        <option value="incendio">Incendio</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="descripcion">
                        <i class="bx bx-detail"></i>
                        Descripción
                    </label>
                    <textarea id="descripcion" name="descripcion" rows="5" required
                        placeholder="Ingrese descripción detallada"></textarea>
                </div>
            </div>

            <div class="column">
                <div class="form-group">
                    <label for="consecuencia-perdida">
                        <i class="material-icons">trending_down</i>
                        Consecuencia o Perdida
                    </label>
                    <input type="text" id="consecuencia-perdida" name="consecuencia-perdida" required
                        placeholder="Ingrese consecuencia o perdida">
                </div>

                <div class="form-group">
                    <label for="probabilidad">
                        <i class="material-icons">bar_chart</i>
                        Probabilidad
                    </label>
                    <select id="probabilidad" name="probabilidad" required>
                        <option value="">Seleccione Probabilidad</option>
                        <option value="baja">Baja</option>
                        <option value="media">Media</option>
                        <option value="alta">Alta</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="valor-riesgo">
                        <i class="bx bx-error"></i>
                        Valor Riesgo
                    </label>
                    <input type="number" id="valor-riesgo" name="valor-riesgo" required
                        placeholder="Ingrese valor de riesgo">
                </div>

                <div class="form-group">
                    <label for="nivel-riesgo">
                        <i class="bx bx-tachometer"></i>
                        Nivel de Riesgo
                    </label>
                    <select id="nivel-riesgo" name="nivel-riesgo" required>
                        <option value="">Seleccione Nivel de Riesgo</option>
                        <option value="bajo">Bajo</option>
                        <option value="medio">Medio</option>
                        <option value="alto">Alto</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="cliente">
                        <i class="material-icons">person</i>
                        Cliente
                    </label>
                    <input type="text" id="cliente" name="cliente" required placeholder="Ingrese nombre del cliente">
                </div>
            </div>

            <div class="column">
                <div class="form-group">
                    <label for="mes-reporte">
                        <i class="bx bx-calendar"></i>
                        Mes Reporte
                    </label>
                    <select id="mes-reporte" name="mes-reporte" required>
                        <option value="">Seleccione un Mes</option>
                        <option value="enero">Enero</option>
                        <option value="febrero">Febrero</option>
                        <option value="marzo">Marzo</option>
                        <option value="abril">Abril</option>
                        <option value="mayo">Mayo</option>
                        <option value="junio">Junio</option>
                        <option value="julio">Julio</option>
                        <option value="agosto">Agosto</option>
                        <option value="septiembre">Septiembre</option>
                        <option value="octubre">Octubre</option>
                        <option value="noviembre">Noviembre</option>
                        <option value="diciembre">Diciembre</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fecha-reporte">
                        <i class="material-icons">event</i>
                        Fecha Reporte
                    </label>
                    <input type="date" id="fecha-reporte" name="fecha-reporte" required>
                </div>

                <div class="form-group">
                    <label for="fecha-evento">
                        <i class="bx bx-calendar-event"></i>
                        Fecha del Evento
                    </label>
                    <input type="date" id="fecha-evento" name="fecha-evento" required>
                </div>

                <div class="form-group">
                    <label for="fecha-termino">
                        <i class="material-icons">event_available</i>
                        Fecha Termino
                    </label>
                    <input type="date" id="fecha-termino" name="fecha-termino" required>
                </div>

                <div class="form-group">
                    <label for="delay">
                        <i class="bx bx-time"></i>
                        Delay
                    </label>
                    <input type="text" id="delay" name="delay" required placeholder="Ingrese delay">
                </div>
            </div>

            <div class="column">
                <div class="form-group">
                    <label for="estatus">
                        <i class="material-icons">check_circle</i>
                        Estatus
                    </label>
                    <select id="estatus" name="estatus" required>
                        <option value="">Seleccione un Estatus</option>
                        <option value="abierto">Abierto</option>
                        <option value="cerrado">Cerrado</option>
                        <option value="proceso">En Proceso</option>
                        <option value="pendiente">Pendiente</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tipo-atencion">
                        <i class="bx bx-first-aid"></i>
                        Tipo Atención
                    </label>
                    <select id="tipo-atencion" name="tipo-atencion" required>
                        <option value="">Seleccione Tipo de Atención</option>
                        <option value="preventiva">Preventiva</option>
                        <option value="correctiva">Correctiva</option>
                        <option value="inmediata">Inmediata</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="reporta">
                        <i class="material-icons">person</i>
                        Reporta
                    </label>
                    <select id="reporta" name="reporta" required>
                        <option value="">Seleccione quién reporta</option>
                        <option value="usuario1">Juan Pérez</option>
                        <option value="usuario2">Ana López</option>
                        <option value="usuario3">Carlos Gómez</option>
                        <option value="usuario4">María Fernández</option>
                    </select>
                </div>

            </div>

            <div class="form-actions">
                <button type="button" class="btn-cancelar">
                    <i class="material-icons">cancel</i>
                    Cancelar
                </button>
                <button type="submit" class="btn-guardar">
                    <i class="bx bx-save"></i>
                    Guardar
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const reporteModal = document.querySelector('.reporte-modal');
            const btnNuevo = document.querySelector('.btn-new');
            const reporteModalClose = document.querySelector('.reporte-modal-close');
            const btnCancelar = document.querySelector('.btn-cancelar');
            const reportForm = document.getElementById('reportForm');

            function openModal() {
                reporteModal.classList.add('show');
            }

            function closeModal() {
                reporteModal.classList.remove('show');
            }



            btnNuevo.addEventListener('click', openModal);
            reporteModalClose.addEventListener('click', closeModal);
            btnCancelar.addEventListener('click', closeModal);

            reporteModal.addEventListener('click', (event) => {
                if (event.target === reporteModal) {
                    closeModal();
                }
            });
        });
    </script>
</body>

</html>
