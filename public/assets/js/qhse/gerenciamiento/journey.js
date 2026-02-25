// ====================================================================
// VARIABLES Y CONFIGURACIÓN GLOBAL
// ====================================================================
let codigoViaje = 1;
let contadorUnidades = 0;
const MAX_UNIDADES = 5;
const MAX_PASAJEROS = 4;
const MAX_PARADAS = 4;

const datosInspeccionLigera = {};
const datosInspeccionPesada = {};
let datosReunionConvoy = {};
let reunionPreConvoyGuardada = false;
let contadorParadas = 0;
let evaluacionRiesgoGuardada = false;
let puntajeRiesgoTotal = 0;

let clasificacionVehiculos = {};
let vehiculosLigeros = [];
let vehiculosPesados = [];
let conductoresGlobales = [];
let datosConductoresGlobales = {};
let datosDestinosGlobal = [];

let unidadEnInspeccion = null;
let tipoVehiculoInspeccion = null;
let streamCamara = null;
let tipoActualCamara = null; // 'ligera' o 'pesada'
let usarCamaraFrontal = false; // false = Trasera (Default), true = Frontal
let fotosSubidas = { ligera: [], pesada: [] };

const configHora = {
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
    time_24hr: true,
    minuteIncrement: 15,
    disableMobile: true,
    allowInput: true,
    static: true,
    wrap: false,
    locale: flatpickr.l10ns.es,
};

// Modificar el DOMContentLoaded para incluir la fecha
document.addEventListener("DOMContentLoaded", async function () {
    await Promise.all([
        cargarVehiculosDesdeBD(),
        cargarConductoresDesdeBD(),
        obtenerDestinosBackend(),
    ]);

    configurarEventosAnomalias();
    inicializarFlatpickr();
    inicializarInputsFotos();
    configurarEventosGlobales();
    cargarFolioEstimado();
    actualizarBotonReunionConvoy();
    actualizarBotonEvaluacion();

    // AGREGAR ESTA LÍNEA
    mostrarFechaActual();
});

// ====================================================================
// VARIABLES GLOBALES PARA PAGINACIÓN Y FILTROS
// ====================================================================
let currentPage = 1;
let currentPerPage = 5;
let currentSearch = "";
let currentStatusGv = "all"; // NUEVO: Variable para estado GV
let currentStatusViaje = "all"; // NUEVO: Variable para estado Viaje
let currentRiskLevel = "all";
let currentDestination = "all";
let currentFechaSolicitud = "";

// ====================================================================
// CARGA DINÁMICA DE LA TABLA DE VIAJES
// ====================================================================
async function cargarViajes(page = 1) {
    const container = document.getElementById("tablaViajesContainer");
    const paginationContainer = document.getElementById("paginationContainer");

    currentPage = page;
    container.innerHTML = document.getElementById("loaderTemplate").innerHTML;

    try {
        let url = `/qhse/gerenciamiento/journeys?page=${currentPage}&per_page=${currentPerPage}`;

        if (currentSearch)
            url += `&search=${encodeURIComponent(currentSearch)}`;
        if (currentStatusGv !== "all") url += `&status_gv=${currentStatusGv}`; // Añadido
        if (currentStatusViaje !== "all")
            url += `&status_viaje=${currentStatusViaje}`; // Añadido
        if (currentRiskLevel !== "all")
            url += `&risk_level=${currentRiskLevel}`;
        if (currentDestination !== "all")
            url += `&destination=${currentDestination}`;
        if (currentFechaSolicitud)
            url += `&fecha_solicitud=${encodeURIComponent(currentFechaSolicitud)}`;

        await new Promise((resolve) => setTimeout(resolve, 500));

        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
            if (result.data.length === 0) {
                container.innerHTML =
                    document.getElementById("emptyTemplate").innerHTML;
                paginationContainer.innerHTML = "";
            } else {
                renderTablaViajes(result.data, container);
                if (result.pagination && result.pagination.total > 0) {
                    renderPaginacion(result.pagination, paginationContainer);
                }
            }
            actualizarEstadisticas();
        } else {
            mostrarError(result.message || "Error al cargar los datos");
        }
    } catch (error) {
        console.error("Error:", error);
        mostrarError("Error de conexión al servidor");
    }
}

function mostrarError(mensaje) {
    const container = document.getElementById("tablaViajesContainer");
    const errorTemplate = document.getElementById("errorTemplate").innerHTML;
    container.innerHTML = errorTemplate;
    document.getElementById("errorMensaje").textContent = mensaje;
}

function renderTablaViajes(viajes, container) {
    let html = `
        <table class="table-viajes">
            <thead>
                <tr>
                    <th class="th-codigo">N°</th>
                    <th class="th-nombre">Solicitante</th>
                    <th class="th-departamento">Departamento</th>
                    <th class="th-destino">Destino</th>
                    <th class="th-fechas">Fechas de Viaje</th>
                    <th class="th-riesgo">Riesgo</th>
                    <th class="th-estado">Estado GV</th>
                    <th class="th-estado">Estado Viaje</th>
                    <th class="th-acciones" style="text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
    `;

    viajes.forEach((viaje) => {
        html += `
            <tr>
                <td><strong>${viaje.folio}</strong></td>
                <td>${viaje.solicitante}</td>
                <td>${viaje.departamento}</td>
                <td>${viaje.destino}</td>
                <td>${viaje.fechas}</td>
                <td><span class="badge-riesgo ${viaje.riesgo.clase}">${viaje.riesgo.texto}</span></td>
                <td><span class="badge-status ${viaje.estado_gv.clase}">${viaje.estado_gv.texto}</span></td>
                <td><span class="badge-status ${viaje.estado_viaje.clase}">${viaje.estado_viaje.texto}</span></td>
                <td>
                    <div style="display: flex; gap: 8px; justify-content: center;">
                        <button class="btn-action-small btn-view" title="Ver detalles" onclick="verDetalleViaje('${viaje.folio}')">
                            <i class="fas fa-eye"></i>
                        </button>
        `;

        if (
            viaje.estado_viaje.texto === "En Curso" ||
            viaje.estado_viaje.texto === "Por Iniciar"
        ) {
            html += `
                        <button class="btn-action-small btn-tracking" title="Ruta Operativa" onclick="abrirModalRuta('${viaje.folio}')">
                            <i class="fas fa-route"></i> Ruta
                        </button>
            `;
        } else {
            html += `
                        <button class="btn-action-small btn-history" title="Ver Historial" onclick="abrirModalHistorial('${viaje.folio}')">
                            <i class="fas fa-list-alt"></i> Historial
                        </button>
            `;
        }

        html += `
                    </div>
                </td>
            </tr>
        `;
    });

    html += `
            </tbody>
        </table>
    `;

    container.innerHTML = html;
}

// ====================================================================
// RENDERIZADO DEL PAGINADOR CORREGIDO
// ====================================================================
function renderPaginacion(pagination, container) {
    if (!pagination) return;

    let html = `
        <div class="pagination-container">
            <div class="pagination-left-group">
                <div class="pagination-info">
                    Mostrando ${pagination.from || 0} a ${pagination.to || 0} de ${pagination.total} registros
                </div>

                <div class="pagination-limit">
                    <label for="reg-limit">Ver:</label>
                    <select id="reg-limit" name="reg-limit" onchange="cambiarLimiteRegistros(this.value)">
    `;

    [5, 10, 15, 25, 50].forEach((limit) => {
        html += `<option value="${limit}" ${pagination.per_page == limit ? "selected" : ""}>${limit}</option>`;
    });

    html += `
                    </select>
                </div>
            </div>
            <div class="pagination-controls">
    `;

    // Botón Anterior
    let prevDisabled = pagination.current_page === 1 ? "disabled" : "";
    let prevOnClick =
        pagination.current_page === 1
            ? ""
            : `onclick="cambiarPagina(${pagination.current_page - 1})"`;
    html += `
                <button class="page-btn" ${prevDisabled} ${prevOnClick}>
                    <i class="fas fa-chevron-left"></i> Anterior
                </button>
    `;

    // Lógica de números de página con puntos suspensivos
    let lastPage = pagination.last_page;
    let currPage = pagination.current_page;

    for (let i = 1; i <= lastPage; i++) {
        if (
            i === 1 ||
            i === lastPage ||
            (i >= currPage - 1 && i <= currPage + 1)
        ) {
            let activeClass = i === currPage ? "active" : "";
            html += `<button class="page-btn ${activeClass}" onclick="cambiarPagina(${i})">${i}</button>`;
        } else if (i === currPage - 2 || i === currPage + 2) {
            html += `<span class="page-dots">...</span>`;
        }
    }

    // Botón Siguiente
    let nextDisabled = currPage === lastPage ? "disabled" : "";
    let nextOnClick =
        currPage === lastPage ? "" : `onclick="cambiarPagina(${currPage + 1})"`;
    html += `
                <button class="page-btn" ${nextDisabled} ${nextOnClick}>
                    Siguiente <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    `;

    container.innerHTML = html;
}

function cambiarPagina(page) {
    cargarViajes(page);
}

function cambiarLimiteRegistros(limit) {
    currentPerPage = parseInt(limit);
    currentPage = 1; // Regresa a la pág 1 al cambiar límite
    cargarViajes(1);
}

function verDetalleViaje(folio) {
    Swal.fire({
        title: "Detalle del Viaje",
        text: `Mostrando información del viaje ${folio}`,
        icon: "info",
        confirmButtonColor: "#1e3a8a",
    });
}

async function actualizarEstadisticas() {
    try {
        // 👇 AQUÍ ESTÁ EL CAMBIO: Actualizamos la URL a /journeys/stats
        const response = await fetch("/qhse/gerenciamiento/journeys/stats");
        const result = await response.json();

        if (result.success) {
            document.getElementById("active-count").textContent = result.data.activos || 0;
            document.getElementById("pending-count").textContent = result.data.pendientes || 0;
            document.getElementById("completed-count").textContent = result.data.completados || 0;
        }
    } catch (error) {
        console.error("Error cargando estadísticas:", error);
    }
}

// ====================================================================
// CONFIGURACIÓN DE EVENTOS (FILTROS)
// ====================================================================
document.addEventListener("DOMContentLoaded", function () {
    // Configuración flatpickr...
    if (document.getElementById("solicitud-date")) {
        flatpickr("#solicitud-date", {
            dateFormat: "d/m/Y",
            locale: "es",
            onChange: function (selectedDates, dateStr, instance) {
                currentFechaSolicitud = dateStr;
                currentPage = 1;
                cargarViajes(1);
            },
        });
    }

    // Buscador...
    const searchInput = document.getElementById("searchViajes");
    if (searchInput) {
        let timeout = null;
        searchInput.addEventListener("input", function () {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                currentSearch = this.value;
                currentPage = 1;
                cargarViajes(1);
            }, 500);
        });
    }

    // EVENTOS PARA LOS NUEVOS FILTROS
    const statusGvFilter = document.getElementById("status-gv-filter");
    if (statusGvFilter) {
        statusGvFilter.addEventListener("change", function () {
            currentStatusGv = this.value;
            currentPage = 1;
            cargarViajes(1);
        });
    }

    const statusViajeFilter = document.getElementById("status-viaje-filter");
    if (statusViajeFilter) {
        statusViajeFilter.addEventListener("change", function () {
            currentStatusViaje = this.value;
            currentPage = 1;
            cargarViajes(1);
        });
    }

    const destinoFilter = document.getElementById("destino-filter");
    if (destinoFilter) {
        destinoFilter.addEventListener("change", function () {
            currentDestination = this.value;
            currentPage = 1;
            cargarViajes(1);
        });
    }

    const riesgoFilter = document.getElementById("riesgo-filter");
    if (riesgoFilter) {
        riesgoFilter.addEventListener("change", function () {
            currentRiskLevel = this.value;
            currentPage = 1;
            cargarViajes(1);
        });
    }

    // BOTÓN LIMPIAR FILTROS (ACTUALIZADO)
    const clearFilters = document.getElementById("clear-filters");
    if (clearFilters) {
        clearFilters.addEventListener("click", function () {
            document.getElementById("searchViajes").value = "";
            document.getElementById("destino-filter").value = "all";
            document.getElementById("status-gv-filter").value = "all"; // Limpiar GV
            document.getElementById("status-viaje-filter").value = "all"; // Limpiar Viaje
            document.getElementById("riesgo-filter").value = "all";

            const fechaInput = document.getElementById("solicitud-date");
            if (fechaInput && fechaInput._flatpickr) {
                fechaInput._flatpickr.clear();
            }

            currentSearch = "";
            currentStatusGv = "all";
            currentStatusViaje = "all";
            currentRiskLevel = "all";
            currentDestination = "all";
            currentFechaSolicitud = "";
            currentPage = 1;

            cargarViajes(1);
        });
    }

    // Carga inicial
    cargarViajes();
});

// ====================================================================
// OBTENER FOLIO ESTIMADO DESDE LA BD
// ====================================================================
async function cargarFolioEstimado() {
    try {
        const response = await fetch(
            "/qhse/gerenciamiento/journeys/next-folio",
        );
        const data = await response.json();

        if (data.success) {
            const codigoElement = document.getElementById("codigoViaje");
            if (codigoElement) {
                // Mostramos el folio real de la BD con el aviso de estimado
                codigoElement.innerHTML = `${data.next_folio}`;
            }
        }
    } catch (error) {
        // Respaldo en caso de error de red
        document.getElementById("codigoViaje").innerHTML = `GV-PENDIENTE`;
    }
}

function configurarEventosGlobales() {
    const horaInicioViaje = document.getElementById("horaInicioViaje");
    const horaFinViaje = document.getElementById("horaFinViaje");

    if (horaInicioViaje) {
        horaInicioViaje.addEventListener("change", function () {
            calcularHorasViajeParaTodasUnidades();
            actualizarBotonReunionConvoy();
        });
    }

    if (horaFinViaje) {
        horaFinViaje.addEventListener("change", function () {
            calcularHorasViajeParaTodasUnidades();
            actualizarBotonReunionConvoy();
        });
    }

    // Reemplaza esto en tu configurarEventosGlobales():
    document.addEventListener("click", function (e) {
        // Cierra el menú del formulario si se clica afuera
        const wrapperDestino = document.getElementById("wrapperDestino");
        if (wrapperDestino && !wrapperDestino.contains(e.target)) {
            wrapperDestino.classList.remove("open");
        }

        // Cierra el menú del filtro si se clica afuera
        const wrapperDestinoFiltro = document.getElementById(
            "wrapperDestinoFiltro",
        );
        if (wrapperDestinoFiltro && !wrapperDestinoFiltro.contains(e.target)) {
            wrapperDestinoFiltro.classList.remove("open");
        }
    });

    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") cerrarCamara();
    });

    const modalFormulario = document.getElementById("modalFormulario");
    if (modalFormulario) {
        modalFormulario.addEventListener("click", function (e) {
            if (e.target === this) gestionarModalFormulario(false);
        });
    }

    const formViaje = document.getElementById("formViaje");
    if (formViaje) {
        formViaje.addEventListener("submit", (e) => e.preventDefault());
    }

    document.querySelectorAll(".nav-link-viajes").forEach((link) => {
        link.addEventListener("click", function (e) {
            e.preventDefault();
            document
                .querySelectorAll(".nav-link-viajes")
                .forEach((item) => item.classList.remove("active"));
            this.classList.add("active");
        });
    });

    // IMPORTANTE: El campo "Especifique Destino" ahora es siempre obligatorio.
    const destinoEspecifico = document.getElementById("destinoEspecifico");
    if (destinoEspecifico) {
        destinoEspecifico.required = true;
        destinoEspecifico.style.display = "block";
    }
}

function inicializarFlatpickr() {
    flatpickr.localize(flatpickr.l10ns.es);

    // Configuración para fechas
    const configFecha = {
        dateFormat: "d/m/Y",
        locale: "es",
        minDate: "today",
        disableMobile: true,
        allowInput: true,
        clickOpens: true,
        nextArrow: '<i class="fas fa-chevron-right"></i>',
        prevArrow: '<i class="fas fa-chevron-left"></i>',
    };

    // Configuración para fechas sin restricción de "today" (para solicitudes)
    const configFechaSolicitud = {
        dateFormat: "d/m/Y",
        locale: "es",
        disableMobile: true,
        allowInput: true,
        clickOpens: true,
        nextArrow: '<i class="fas fa-chevron-right"></i>',
        prevArrow: '<i class="fas fa-chevron-left"></i>',
        // Sin minDate para permitir fechas pasadas
    };

    // Fechas de viaje
    const fechaInicioViaje = document.getElementById("fechaInicioViaje");
    const fechaFinViaje = document.getElementById("fechaFinViaje");
    if (fechaInicioViaje) flatpickr(fechaInicioViaje, configFecha);
    if (fechaFinViaje) flatpickr(fechaFinViaje, configFecha);

    // HORAS - asumo que tienes configHora definida en otro lado
    const horaInicioViaje = document.getElementById("horaInicioViaje");
    const horaFinViaje = document.getElementById("horaFinViaje");
    if (horaInicioViaje) flatpickr(horaInicioViaje, configHora);
    if (horaFinViaje) flatpickr(horaFinViaje, configHora);

    // NUEVO: Fecha de solicitud de viaje
    const solicitudDate = document.getElementById("solicitud-date");
    if (solicitudDate) flatpickr(solicitudDate, configFechaSolicitud);
}

// ====================================================================
// FUNCIÓN PARA MOSTRAR FECHA ACTUAL
// ====================================================================
function mostrarFechaActual() {
    const fechaDisplay = document.getElementById("fechaSolicitudDisplay");
    const fechaHidden = document.getElementById("fechaSolicitudHidden");

    if (fechaDisplay && fechaHidden) {
        const hoy = new Date();
        const dia = String(hoy.getDate()).padStart(2, "0");
        const mes = String(hoy.getMonth() + 1).padStart(2, "0");
        const año = hoy.getFullYear();

        const fechaFormateada = `${dia}/${mes}/${año}`;
        fechaDisplay.textContent = fechaFormateada;
        fechaHidden.value = fechaFormateada;
    }
}

// ====================================================================
// FUNCIONES DE CARGA DE DATOS
// ====================================================================
let detallesVehiculos = {}; // Aquí guardaremos marcas y propiedad

async function cargarVehiculosDesdeBD() {
    try {
        const response = await fetch("/qhse/gerenciamiento/vehicles");
        if (!response.ok) throw new Error("Error al cargar vehículos");

        const data = await response.json();
        if (data.success) {
            vehiculosLigeros = data.ligeras || [];
            vehiculosPesados = data.pesadas || [];
            clasificacionVehiculos = data.clasificacion || {};

            // AQUI GUARDAMOS LOS DETALLES QUE VIENEN DEL PHP
            detallesVehiculos = data.detalles || {};
        } else {
            throw new Error(data.message || "Error del servidor");
        }
    } catch (error) {
        console.error("Error cargando vehículos:", error);
        vehiculosLigeros = [];
        vehiculosPesados = [];
        clasificacionVehiculos = {};
        detallesVehiculos = {}; // Reiniciar en caso de error
        Swal.fire(
            "Error de Conexión",
            "No se pudieron cargar los vehículos.",
            "error",
        );
    }
}

async function cargarConductoresDesdeBD() {
    try {
        const response = await fetch("/qhse/gerenciamiento/conductores");
        if (!response.ok) throw new Error("Error al cargar conductores");

        const data = await response.json();
        if (data.success) {
            conductoresGlobales = data.conductores || [];
            datosConductoresGlobales = data.datosConductores || {};
        } else {
            throw new Error(
                data.message || "Error en la respuesta del servidor",
            );
        }
    } catch (error) {
        console.error("Error cargando conductores:", error);
        conductoresGlobales = [];
        datosConductoresGlobales = {};
        Swal.fire(
            "Error de Conexión",
            "No se pudieron cargar los conductores.",
            "error",
        );
    }
}

async function obtenerDestinosBackend() {
    try {
        const response = await fetch("/qhse/gerenciamiento/get-destinations");
        const data = await response.json();
        if (data.success) {
            datosDestinosGlobal = data.data;
            construirMenuDestinos(); // Construye el del formulario
            construirMenuDestinosFiltro(); // NUEVO: Construye el del filtro
        }
    } catch (error) {
        console.error("Error cargando destinos:", error);
    }
}
// ====================================================================
// FUNCIONES DE CÁLCULO DE HORAS
// ====================================================================
function parseTimeForCalculation(timeStr) {
    if (!timeStr?.trim()) return null;
    timeStr = timeStr.trim().toUpperCase();

    const simple24h = timeStr.match(/^(\d{1,2}):(\d{2})$/);
    if (simple24h) {
        const h = parseInt(simple24h[1]);
        const m = parseInt(simple24h[2]);
        if (h >= 0 && h <= 23 && m >= 0 && m <= 59) return h * 60 + m;
    }

    const ampm = timeStr.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
    if (ampm) {
        let h = parseInt(ampm[1]);
        const m = parseInt(ampm[2]);
        const meridiem = ampm[3].toUpperCase();
        if (h === 12) h = meridiem === "AM" ? 0 : 12;
        else if (meridiem === "PM") h += 12;
        if (h >= 0 && h <= 23 && m >= 0 && m <= 59) return h * 60 + m;
    }
    return null;
}

function minutosAStringHora(totalMinutos) {
    if (!totalMinutos || isNaN(totalMinutos)) return "0:00";
    const horas = Math.floor(totalMinutos / 60);
    const minutos = totalMinutos % 60;
    return `${horas}:${minutos.toString().padStart(2, "0")}`;
}

function stringHoraAMinutos(timeStr) {
    if (!timeStr) return 0;
    const partes = timeStr.split(":");
    return (parseInt(partes[0]) || 0) * 60 + (parseInt(partes[1]) || 0);
}

function calcularDiferenciaHoras(hora1, hora2) {
    if (!hora1?.trim() || !hora2?.trim()) return "0:00";

    const minutos1 = parseTimeForCalculation(hora1);
    const minutos2 = parseTimeForCalculation(hora2);
    if (minutos1 === null || minutos2 === null) return "0:00";

    let diferencia = minutos2 - minutos1;
    if (diferencia < 0) diferencia += 24 * 60;
    return minutosAStringHora(diferencia);
}

function calcularHorasDormidas(unidadNumero) {
    const dormir = document.getElementById(`dormir-${unidadNumero}`)?.value;
    let levantar = document.getElementById(`levantar-${unidadNumero}`)?.value;
    const totalDormidasInput = document.getElementById(
        `total-hrs-dormidas-${unidadNumero}`,
    );

    const minutosDormir = parseTimeForCalculation(dormir);
    let minutosLevantar = parseTimeForCalculation(levantar);

    if (minutosDormir !== null && minutosLevantar !== null) {
        if (minutosLevantar <= minutosDormir) minutosLevantar += 24 * 60;
        const duracionMinutos = minutosLevantar - minutosDormir;
        if (totalDormidasInput)
            totalDormidasInput.value = minutosAStringHora(duracionMinutos);
    } else if (totalDormidasInput) {
        totalDormidasInput.value = "0:00";
    }
}

function calcularHorasViaje(unidadNumero) {
    const horaInicioViaje = document.getElementById("horaInicioViaje")?.value;
    const horaFinViaje = document.getElementById("horaFinViaje")?.value;
    const horaLevantar = document.getElementById(
        `levantar-${unidadNumero}`,
    )?.value;

    if (horaLevantar && horaInicioViaje) {
        const horasDespiertoStr = calcularDiferenciaHoras(
            horaLevantar,
            horaInicioViaje,
        );
        const inputDespierto = document.getElementById(
            `horas-despierto-${unidadNumero}`,
        );
        if (inputDespierto) inputDespierto.value = horasDespiertoStr;
    }

    if (horaInicioViaje && horaFinViaje) {
        const duracionViajeStr = calcularDiferenciaHoras(
            horaInicioViaje,
            horaFinViaje,
        );
        const inputDuracion = document.getElementById(
            `horas-viaje-${unidadNumero}`,
        );
        if (inputDuracion) inputDuracion.value = duracionViajeStr;
    }

    calcularTotalHoras(unidadNumero);
}

function calcularTotalHoras(unidadNumero) {
    const inputDespierto = document.getElementById(
        `horas-despierto-${unidadNumero}`,
    );
    const inputDuracion = document.getElementById(
        `horas-viaje-${unidadNumero}`,
    );
    const totalInput = document.getElementById(
        `total-hrs-finalizar-${unidadNumero}`,
    );

    if (inputDespierto && inputDuracion && totalInput) {
        const valDespierto = inputDespierto.value || "0:00";
        const valViaje = inputDuracion.value || "0:00";

        const minutosDespierto = stringHoraAMinutos(valDespierto);
        const minutosViaje = stringHoraAMinutos(valViaje);
        const totalMinutos = minutosDespierto + minutosViaje;
        const totalHorasStr = minutosAStringHora(totalMinutos);

        totalInput.value = totalHorasStr;

        // Si excede 15 horas (900 minutos)
        if (totalMinutos > 900) {
            totalInput.style.backgroundColor = "#dc3545";
            totalInput.style.color = "#ffffff";

            if (!totalInput.dataset.warningShown) {
                Swal.fire({
                    title: "¡Advertencia de Horas!",
                    html: `
                        La Unidad <strong>${unidadNumero}</strong> acumulará <strong>${totalHorasStr} horas</strong> totales.<br>
                        <div style="background-color: #f8d7da; color: #721c24; padding: 5px; border-radius: 4px; margin: 10px 0; font-weight: bold;">
                             ${valDespierto} (Despierto) ${valViaje} (Viaje)
                        </div>
                        Esto excede el límite recomendado de 15 horas.
                    `,
                    icon: "warning",
                    confirmButtonColor: "#0056b3",
                });
                totalInput.dataset.warningShown = "true";
            }
        } else {
            totalInput.style.backgroundColor = "#e9ecef";
            totalInput.style.color = "#495057";
            delete totalInput.dataset.warningShown;
        }
    }
}

function calcularHorasViajeParaTodasUnidades() {
    for (let i = 1; i <= contadorUnidades; i++) calcularHorasViaje(i);
}

// ====================================================================
// VALIDACIÓN DE DOCUMENTACIÓN
// ====================================================================
function validarDocumentacionConductor(data, esPesada) {
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);

    const resultado = {
        licencia: {
            valida: false,
            mensaje: "",
            estilo: "invalid",
            fecha: null,
        },
        curso: { valida: false, mensaje: "", estilo: "invalid", fecha: null },
    };

    if (esPesada) {
        const fechaFederal = data.federalVigencia;
        if (!fechaFederal) {
            resultado.licencia.mensaje = "No registrada";
            resultado.licencia.estilo = "missing";
        } else {
            const fechaLic = new Date(fechaFederal + "T00:00:00");
            resultado.licencia.fecha = fechaLic.toLocaleDateString("es-ES");
            resultado.licencia.mensaje = resultado.licencia.fecha;
            const diasLic = Math.ceil((fechaLic - hoy) / (1000 * 60 * 60 * 24));

            if (diasLic < 0) resultado.licencia.estilo = "expired";
            else if (diasLic <= 30) {
                resultado.licencia.valida = true;
                resultado.licencia.estilo = "orange";
            } else if (diasLic <= 60) {
                resultado.licencia.valida = true;
                resultado.licencia.estilo = "warning";
            } else {
                resultado.licencia.valida = true;
                resultado.licencia.estilo = "ok";
            }
        }
    } else {
        const esPermanente =
            data.permanente === true ||
            data.permanente === 1 ||
            data.permanente === "1";
        const fechaNormal = data.vigencia;

        if (esPermanente) {
            resultado.licencia.valida = true;
            resultado.licencia.mensaje = "Permanente";
            resultado.licencia.estilo = "ok";
        } else if (!fechaNormal) {
            resultado.licencia.mensaje = "No registrada";
            resultado.licencia.estilo = "missing";
        } else {
            const fechaLic = new Date(fechaNormal + "T00:00:00");
            resultado.licencia.fecha = fechaLic.toLocaleDateString("es-ES");
            resultado.licencia.mensaje = resultado.licencia.fecha;
            const diasLic = Math.ceil((fechaLic - hoy) / (1000 * 60 * 60 * 24));

            if (diasLic < 0) resultado.licencia.estilo = "expired";
            else if (diasLic <= 30) {
                resultado.licencia.valida = true;
                resultado.licencia.estilo = "orange";
            } else if (diasLic <= 60) {
                resultado.licencia.valida = true;
                resultado.licencia.estilo = "warning";
            } else {
                resultado.licencia.valida = true;
                resultado.licencia.estilo = "ok";
            }
        }
    }

    const fechaCursoRaw = esPesada
        ? data.cursoPesadoVigencia
        : data.manDefVigencia;
    if (!fechaCursoRaw) {
        resultado.curso.mensaje = "No registrado";
        resultado.curso.estilo = "missing";
    } else {
        const fechaCurso = new Date(fechaCursoRaw + "T00:00:00");
        resultado.curso.fecha = fechaCurso.toLocaleDateString("es-ES");
        resultado.curso.mensaje = resultado.curso.fecha;
        const diasCurso = Math.ceil((fechaCurso - hoy) / (1000 * 60 * 60 * 24));

        if (diasCurso < 0) resultado.curso.estilo = "expired";
        else if (diasCurso <= 30) {
            resultado.curso.valida = true;
            resultado.curso.estilo = "orange";
        } else if (diasCurso <= 60) {
            resultado.curso.valida = true;
            resultado.curso.estilo = "warning";
        } else {
            resultado.curso.valida = true;
            resultado.curso.estilo = "ok";
        }
    }

    return resultado;
}

function aplicarEstiloPorValidacion(input, estado) {
    if (!input) return;
    input.style.fontWeight = "bold";

    switch (estado) {
        case "ok":
            input.style.backgroundColor = "#28a745";
            input.style.color = "#ffffff";
            input.title = "Vigente";
            break;
        case "orange":
            input.style.backgroundColor = "#fd7e14";
            input.style.color = "#ffffff";
            input.title = "Vence pronto (≤ 30 días)";
            break;
        case "warning":
            input.style.backgroundColor = "#ffc107";
            input.style.color = "#212529";
            input.title = "Precaución (≤ 60 días)";
            break;
        case "expired":
            input.style.backgroundColor = "#dc3545";
            input.style.color = "#ffffff";
            input.title = "VENCIDO";
            break;
        case "missing":
            input.style.backgroundColor = "#e9ecef";
            input.style.color = "#6c757d";
            input.title = "No registrado";
            break;
        default:
            input.style.backgroundColor = "#e9ecef";
            input.style.color = "#495057";
            input.title = "";
    }
}

function aplicarEstiloGris(input) {
    if (!input) return;
    input.style.backgroundColor = "#e9ecef";
    input.style.color = "#6c757d";
    input.style.fontWeight = "normal";
    input.title = "";
}
// ====================================================================
// MENÚ DE DESTINOS (VERSIÓN FILTRO DE BÚSQUEDA)
// ====================================================================
function toggleMenuDestinoFiltro() {
    const wrapper = document.getElementById("wrapperDestinoFiltro");
    if (wrapper) wrapper.classList.toggle("open");
}

function construirMenuDestinosFiltro() {
    const lista = document.getElementById("listaOpcionesDestinoFiltro");
    if (!lista) return;

    lista.innerHTML = "";

    // 1. Opción "Todos los Destinos" (Exclusiva del filtro)
    const divTodos = document.createElement("div");
    divTodos.className = "option-group-header";
    divTodos.innerHTML = `<span><i class="fas fa-globe"></i> Todos los Destinos</span>`;
    divTodos.onclick = (e) => {
        e.stopPropagation();
        finalizarSeleccionFiltro("all", "Todos los Destinos");
    };
    lista.appendChild(divTodos);

    // 2. Mapeo de Estados y Municipios
    datosDestinosGlobal.forEach((estado) => {
        const grupoDiv = document.createElement("div");
        grupoDiv.className = "option-group";

        const header = document.createElement("div");
        header.className = "option-group-header";
        header.innerHTML = `<span>${estado.name}</span><i class="fas fa-chevron-right"></i>`;
        header.onclick = (e) => {
            e.stopPropagation();
            document
                .querySelectorAll("#listaOpcionesDestinoFiltro .option-group")
                .forEach((el) => {
                    if (el !== grupoDiv) {
                        el.classList.remove("active");
                        const icon = el.querySelector(".option-group-header i");
                        if (icon) icon.className = "fas fa-chevron-right";
                    }
                });
            grupoDiv.classList.toggle("active");
            const icon = header.querySelector("i");
            icon.className = grupoDiv.classList.contains("active")
                ? "fas fa-chevron-down"
                : "fas fa-chevron-right";
        };

        const childrenDiv = document.createElement("div");
        childrenDiv.className = "option-group-children";

        if (estado.children?.length > 0) {
            estado.children.forEach((municipio) => {
                const item = document.createElement("div");
                item.className = "option-child";
                item.textContent = municipio.name;
                item.onclick = (e) => {
                    e.stopPropagation();
                    // Al hacer click, mandamos el municipio para que el LIKE del PHP lo encuentre
                    finalizarSeleccionFiltro(
                        `${municipio.name}`,
                        `${municipio.name}, ${estado.name}`,
                    );
                };
                childrenDiv.appendChild(item);
            });
        } else {
            const empty = document.createElement("div");
            empty.className = "option-child";
            empty.style.color = "#999";
            empty.textContent = "Sin datos";
            childrenDiv.appendChild(empty);
        }

        grupoDiv.appendChild(header);
        grupoDiv.appendChild(childrenDiv);
        lista.appendChild(grupoDiv);
    });

    // 3. Opción "Otro"
    const divOtro = document.createElement("div");
    divOtro.className = "option-group-header";
    divOtro.innerHTML = `<span>Otro Destino...</span>`;
    divOtro.style.borderTop = "1px solid #dee2e6";
    divOtro.onclick = (e) => {
        e.stopPropagation();
        finalizarSeleccionFiltro("OTRO", "Otro Destino");
    };
    lista.appendChild(divOtro);
}

function finalizarSeleccionFiltro(valorReal, textoVisual) {
    // Actualizar los inputs visuales y ocultos
    const inputHidden = document.getElementById("inputDestinoFiltroHidden");
    if (inputHidden) inputHidden.value = valorReal;

    const label = document.getElementById("labelDestinoFiltroSeleccionado");
    if (label) {
        label.textContent = textoVisual;
        label.style.color = "#212529";
    }

    // Cerrar el menú
    const wrapper = document.getElementById("wrapperDestinoFiltro");
    if (wrapper) wrapper.classList.remove("open");

    // DISPARAR LA RECARGA DE LA TABLA (Equivalente al antiguo 'change' event)
    currentDestination = valorReal;
    currentPage = 1;
    cargarViajes(1);
}
// ====================================================================
// MENÚ DE DESTINOS
// ====================================================================
function toggleMenuDestino() {
    const wrapper = document.getElementById("wrapperDestino");
    if (wrapper) wrapper.classList.toggle("open");
}

function construirMenuDestinos() {
    const lista = document.getElementById("listaOpcionesDestino");
    if (!lista) return;

    lista.innerHTML = "";
    datosDestinosGlobal.forEach((estado) => {
        const grupoDiv = document.createElement("div");
        grupoDiv.className = "option-group";

        const header = document.createElement("div");
        header.className = "option-group-header";
        header.innerHTML = `<span>${estado.name}</span><i class="fas fa-chevron-right"></i>`;
        header.onclick = (e) => {
            e.stopPropagation();
            document.querySelectorAll(".option-group").forEach((el) => {
                if (el !== grupoDiv) {
                    el.classList.remove("active");
                    const icon = el.querySelector(".option-group-header i");
                    if (icon) icon.className = "fas fa-chevron-right";
                }
            });
            grupoDiv.classList.toggle("active");
            const icon = header.querySelector("i");
            icon.className = grupoDiv.classList.contains("active")
                ? "fas fa-chevron-down"
                : "fas fa-chevron-right";
        };

        const childrenDiv = document.createElement("div");
        childrenDiv.className = "option-group-children";

        if (estado.children?.length > 0) {
            estado.children.forEach((municipio) => {
                const item = document.createElement("div");
                item.className = "option-child";
                item.textContent = municipio.name;
                item.onclick = (e) => {
                    e.stopPropagation();
                    finalizarSeleccion(
                        `${municipio.name}, ${estado.name}`,
                        `${municipio.name}, ${estado.name}`,
                    );
                };
                childrenDiv.appendChild(item);
            });
        } else {
            const empty = document.createElement("div");
            empty.className = "option-child";
            empty.style.color = "#999";
            empty.textContent = "Sin datos";
            childrenDiv.appendChild(empty);
        }

        grupoDiv.appendChild(header);
        grupoDiv.appendChild(childrenDiv);
        lista.appendChild(grupoDiv);
    });

    const divOtro = document.createElement("div");
    divOtro.className = "option-group-header";
    divOtro.innerHTML = `<span>Otro...</span>`;
    divOtro.style.borderTop = "1px solid #dee2e6";
    divOtro.onclick = (e) => {
        e.stopPropagation();
        finalizarSeleccion("Otro", "Otro");
    };
    lista.appendChild(divOtro);
}

function finalizarSeleccion(valorReal, textoVisual) {
    const inputHidden = document.getElementById("inputDestinoHidden");
    if (inputHidden) inputHidden.value = valorReal;

    const label = document.getElementById("labelDestinoSeleccionado");
    if (label) {
        label.textContent = textoVisual;
        label.style.color = "#212529";
    }

    const wrapper = document.getElementById("wrapperDestino");
    if (wrapper) wrapper.classList.remove("open");
}

// ====================================================================
// FUNCIONES DE UNIDADES
// ===================================================================

function agregarUnidad() {
    if (contadorUnidades >= MAX_UNIDADES) {
        Swal.fire(
            "Límite alcanzado",
            `Solo puedes agregar hasta ${MAX_UNIDADES} unidades.`,
            "warning",
        );
        return;
    }

    contadorUnidades++;
    const numeroUnidad = contadorUnidades;

    if (numeroUnidad === 2) {
        Swal.fire({
            title: "¡Convoy de Unidades!",
            html: "Has agregado una segunda unidad. Se gestionará como Convoy.",
            icon: "info",
            confirmButtonColor: "#0056b3",
        });
    }

    const nuevaFila = document.createElement("tr");
    nuevaFila.id = `unidad-${numeroUnidad}`;
    nuevaFila.innerHTML = generarHTMLUnidad(numeroUnidad);

    const cuerpoTablaUnidades = document.getElementById("cuerpoTablaUnidades");
    if (cuerpoTablaUnidades) cuerpoTablaUnidades.appendChild(nuevaFila);

    inicializarControlesUnidad(numeroUnidad, nuevaFila);

    agregarPasajero(numeroUnidad);
    actualizarContadorUnidades();
    actualizarLabelTipoUnidad();
    actualizarBotonAgregar();
    actualizarBotonReunionConvoy();
    actualizarBotonEvaluacion();
}

function generarHTMLUnidad(numeroUnidad) {
    const botonEliminarHTML =
        numeroUnidad === 1
            ? `<button type="button" class="btn-accion eliminar" style="opacity: 0.3; pointer-events: none;" title="La primera unidad es obligatoria y no se puede eliminar">
               <i class="fas fa-trash"></i>
           </button>`
            : `<button type="button" class="btn-accion eliminar" onclick="eliminarUnidad(${numeroUnidad})" title="Eliminar unidad">
               <i class="fas fa-trash"></i>
           </button>`;

    return `
    <td>
        <div id="tipo-vehiculo-${numeroUnidad}" class="tipo-vehiculo-text"></div>
        <div class="input-wrapper" style="position: relative;">
            <input type="hidden" id="vehicle-hidden-${numeroUnidad}" name="unidad[${numeroUnidad}][vehiculo]" required>
            <div id="vehicle-trigger-${numeroUnidad}" class="vehicle-select-trigger">
                <span>Vehículo</span>
            </div>
        </div>
        <button type="button" class="btn-inspeccion" id="btn-inspeccion-${numeroUnidad}"
                data-realizada="false" data-tiene-nos="false"
                onclick="abrirInspeccion(${numeroUnidad})"
                title="Realizar Inspección Pre-Viaje">
            <i class="fas fa-clipboard-list"></i> Inspeccion
        </button>
    </td>
    <td>
        <div class="conductor-completo-group">
            <div class="hour-input-group" style="align-items: center;">
                <div style="display: flex; align-items: center; justify-content: center; width: 100%; margin-bottom: 5px;">
                    <label style="margin: 0;"><i class="fas fa-user-circle"></i> Nombre Conductor</label>
                </div>
                <div class="conductor-input-group input-wrapper">
                    <i class="fas fa-user field-icon conductor-main"></i>
                    <input type="text" class="table-input large unidad-conductor has-icon" id="conductor-${numeroUnidad}" name="unidad[${numeroUnidad}][conductor]" data-es-principal="true" placeholder="Nombre completo" required autocomplete="off">
                    <i class="fas fa-star badge-principal" id="badge-principal-${numeroUnidad}" style="display:none;"></i>
                    <div class="autocomplete-list" id="autocomplete-list-${numeroUnidad}"></div>
                </div>
                <button type="button" class="btn-ver-conductor2" onclick="mostrarInfoConductor2(${numeroUnidad})" style="display: none;">
                    <i class="fas fa-eye"></i> Ver Conductor 2
                </button>
            </div>
            <div class="hour-input-group" style="display: none;">
                <input type="hidden" id="licencia-num-${numeroUnidad}" name="unidad[${numeroUnidad}][licencia_num]">
                <input type="hidden" id="vigencia-lic-hidden-${numeroUnidad}" name="unidad[${numeroUnidad}][vigencia_lic_hidden]">
            </div>
        </div>
    </td>
    <td>
        <div class="hour-input-group">
            <label><i class="fas fa-wind"></i> Alcoholimetría</label>
            <div class="input-group-row" style="justify-content: center; display: flex; align-items: center;">
                <input type="number" step="0.1" class="table-input input-porcentaje unidad-alcoholimetria" id="alcohol-pct-${numeroUnidad}" name="unidad[${numeroUnidad}][alcohol_pct]" placeholder="0.0" value="0.0" required onblur="formatearPorcentaje(this)" onchange="actualizarBotonReunionConvoy()">
            </div>
        </div>
        <div class="hour-input-group">
            <label><i class="fas fa-heartbeat"></i> Presión Arterial</label>
            <div class="input-group-row" style="justify-content: center; display: flex; align-items: center;">
                <input type="text" class="table-input" id="presion-valor-${numeroUnidad}" name="unidad[${numeroUnidad}][presion_valor]" placeholder="Ej: 120/80" style="width: 70px;" required>
            </div>
        </div>
        <div class="hour-input-group">
            <label><i class="fas fa-pills"></i> Toma Medicamento</label>
            <div class="input-group-row" style="justify-content: center; display: flex; align-items: center;">
                <select class="table-input medicina-select"
                        id="medicamento-${numeroUnidad}"
                        name="unidad[${numeroUnidad}][toma_medicamento]"
                        required
                        onchange="toggleMedicamentoDetalle(${numeroUnidad})">
                    <option value="">...</option>
                    <option value="si">Sí</option>
                    <option value="no">No</option>
                </select>
            </div>
            <div id="medicamento-detalle-${numeroUnidad}" class="medicina-hidden-container" style="display: none;">
                <label class="medicina-label-detail">
                    <i class="fas fa-prescription"></i> ¿Cuál?
                </label>
                <input type="text"
                       class="table-input medicina-input-nombre"
                       id="medicamento-nombre-${numeroUnidad}"
                       name="unidad[${numeroUnidad}][medicamento_nombre]"
                       placeholder="Nombre..."
                       autocomplete="off">
            </div>
        </div>
    </td>
    <td>
        <div class="hour-input-group">
            <label><i class="fas fa-id-card"></i> Vigencia Licencia</label>
            <input type="text" class="table-input" id="vigencia-lic-${numeroUnidad}" name="unidad[${numeroUnidad}][vigencia_lic]" placeholder="dd/mm/aaaa" required readonly>
        </div>
        <div class="hour-input-group" style="margin-top: 5px;">
            <label><i class="fas fa-calendar-alt"></i> Vigencia Man. Def.</label>
            <input type="text" class="table-input" id="vigencia-man-${numeroUnidad}" name="unidad[${numeroUnidad}][vigencia_man]" placeholder="dd/mm/aaaa" required readonly>
        </div>
    </td>
    <td>
        <div class="hour-input-group">
            <label><i class="fas fa-bed"></i> Hr que Durmió</label>
            <div class="datetime-input-wrapper">
                <input type="text" class="table-input small unidad-hora-dormir" id="dormir-${numeroUnidad}" name="unidad[${numeroUnidad}][hora_dormir]" placeholder="HH:MM" required>
            </div>
        </div>
        <div class="hour-input-group">
            <label><i class="fas fa-sun"></i> Hr que Despertó</label>
            <div class="datetime-input-wrapper">
                <input type="text" class="table-input small unidad-hora-levantar" id="levantar-${numeroUnidad}" name="unidad[${numeroUnidad}][hora_levantar]" placeholder="HH:MM" required>
            </div>
        </div>
        <div class="hour-input-group" style="margin-top: 5px;">
            <label style="font-weight: 700; color: #0056b3;"><i class="fas fa-hourglass-half"></i> Hrs Dormidas</label>
            <input type="text" class="table-input small unidad-total-dormidas hour-input-result" id="total-hrs-dormidas-${numeroUnidad}" name="unidad[${numeroUnidad}][total_dormidas]" placeholder="0:00" readonly>
        </div>
    </td>
    <td class="td-horas-conduccion">
        <div class="hour-inputs-group-combined-vertical">
            <div class="hour-input-group">
                <label><i class="fas fa-bed"></i> Hr Despierto</label>
                <input type="text" class="table-input small unidad-horas-despierto" id="horas-despierto-${numeroUnidad}" name="unidad[${numeroUnidad}][horas_despierto]" placeholder="0:00" required onchange="calcularTotalHoras(${numeroUnidad}); actualizarBotonReunionConvoy()" readonly>
            </div>
            <div class="hour-input-group">
                <label><i class="fas fa-route"></i> Duración Viaje</label>
                <input type="text" class="table-input small unidad-horas-viaje" id="horas-viaje-${numeroUnidad}" name="unidad[${numeroUnidad}][horas_viaje]" placeholder="0:00" required onchange="calcularTotalHoras(${numeroUnidad}); actualizarBotonReunionConvoy()" readonly>
            </div>
            <div class="hour-input-group" style="margin-top: 5px;">
                <label style="font-weight: 700; color: #0056b3;"><i class="fas fa-clock"></i> Total Hrs</label>
                <input type="text" class="table-input small unidad-total-finalizar hour-input-result" id="total-hrs-finalizar-${numeroUnidad}" name="unidad[${numeroUnidad}][total_finalizar]" placeholder="0:00" readonly>
            </div>
        </div>
    </td>
    <td class="td-pasajeros">
        <div id="pasajeros-unidad-${numeroUnidad}" class="pasajero-container"></div>
        <button type="button" class="btn-add-pasajero" id="btn-add-pasajero-${numeroUnidad}" onclick="agregarPasajero(${numeroUnidad})" title="Agregar pasajero">
            <i class="fas fa-user-plus"></i>
        </button>
    </td>
    <td class="acciones-td">
        ${botonEliminarHTML}
    </td>`;
}

function inicializarControlesUnidad(numeroUnidad, fila) {
    const unidadHoraDormir = fila.querySelector(".unidad-hora-dormir");
    const unidadHoraLevantar = fila.querySelector(".unidad-hora-levantar");

    if (unidadHoraDormir) {
        flatpickr(unidadHoraDormir, configHora);
        unidadHoraDormir.addEventListener("change", () => {
            calcularHorasDormidas(numeroUnidad);
            calcularHorasViaje(numeroUnidad);
            actualizarBotonReunionConvoy();
        });
    }

    if (unidadHoraLevantar) {
        flatpickr(unidadHoraLevantar, configHora);
        unidadHoraLevantar.addEventListener("change", () => {
            calcularHorasDormidas(numeroUnidad);
            calcularHorasViaje(numeroUnidad);
            actualizarBotonReunionConvoy();
        });
    }

    inicializarSelectorVehiculo(numeroUnidad);
    inicializarAutocompleteConductor(numeroUnidad, false, null);
}

function eliminarUnidad(numero) {
    if (numero === 1) {
        Swal.fire({
            title: "Acción No Permitida",
            text: "La primera unidad no puede ser eliminada. El viaje requiere al menos un vehículo.",
            icon: "error",
            confirmButtonColor: "#0056b3",
        });
        return;
    }

    Swal.fire({
        title: "¿Eliminar unidad?",
        text: "Se eliminarán todos los datos de esta unidad y sus pasajeros.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#0056b3",
    }).then((result) => {
        if (result.isConfirmed) {
            const fila = document.getElementById(`unidad-${numero}`);
            if (fila) fila.remove();
            delete datosInspeccionLigera[numero];
            delete datosInspeccionPesada[numero];
            contadorUnidades--;
            actualizarContadorUnidades();
            actualizarLabelTipoUnidad();
            actualizarBotonAgregar();
            reordenarNumerosUnidades();
            actualizarBotonReunionConvoy();
            actualizarBotonEvaluacion();
        }
    });
}

function reordenarNumerosUnidades() {
    const filas = document.querySelectorAll("#cuerpoTablaUnidades tr");
    contadorUnidades = 0;
    const nuevosDatosInspeccionLigera = {};
    const nuevosDatosInspeccionPesada = {};

    filas.forEach((fila, index) => {
        const numeroAnterior = parseInt(fila.id.split("-")[1]);
        const nuevoNumero = index + 1;
        contadorUnidades = nuevoNumero;

        fila.id = `unidad-${nuevoNumero}`;

        if (datosInspeccionLigera[numeroAnterior]) {
            nuevosDatosInspeccionLigera[nuevoNumero] =
                datosInspeccionLigera[numeroAnterior];
            delete datosInspeccionLigera[numeroAnterior];
        }
        if (datosInspeccionPesada[numeroAnterior]) {
            nuevosDatosInspeccionPesada[nuevoNumero] =
                datosInspeccionPesada[numeroAnterior];
            delete datosInspeccionPesada[numeroAnterior];
        }

        const elementos = [
            "licencia-num",
            "vigencia-lic",
            "vigencia-man",
            "conductor",
            "autocomplete-list",
            "dormir",
            "levantar",
            "total-hrs-dormidas",
            "horas-despierto",
            "horas-viaje",
            "total-hrs-finalizar",
            "tipo-vehiculo",
            "medicamento",
            "medicamento-nombre",
            "medicamento-detalle",
            "alcohol-pct",
            "presion-valor",
            "btn-inspeccion",
            "vehicle-hidden",
            "vehicle-trigger",
            "badge-principal",
            "btn-add-pasajero",
            "pasajeros-unidad",
        ];

        elementos.forEach((el) => {
            const oldEl = fila.querySelector(`#${el}-${numeroAnterior}`);
            if (oldEl) oldEl.id = `${el}-${nuevoNumero}`;
        });

        const btnInspeccion = fila.querySelector(
            `#btn-inspeccion-${nuevoNumero}`,
        );
        if (btnInspeccion) {
            btnInspeccion.setAttribute(
                "onclick",
                `abrirInspeccion(${nuevoNumero})`,
            );

            if (btnInspeccion.dataset.realizada === "true") {
                if (btnInspeccion.dataset.tieneNos === "true") {
                    btnInspeccion.className =
                        "btn-inspeccion-realizada-warning";
                    btnInspeccion.innerHTML =
                        '<i class="fas fa-exclamation-triangle"></i> Realizado';
                } else {
                    btnInspeccion.className = "btn-inspeccion-realizada-ok";
                    btnInspeccion.innerHTML =
                        '<i class="fas fa-check-circle"></i> Realizado';
                }
            } else {
                btnInspeccion.className = "btn-inspeccion";
                btnInspeccion.innerHTML =
                    '<i class="fas fa-clipboard-list"></i> Realizar';
            }
        }

        const btnEliminar = fila.querySelector(".btn-accion.eliminar");
        if (btnEliminar) {
            if (nuevoNumero === 1) {
                btnEliminar.style.opacity = "0.3";
                btnEliminar.style.pointerEvents = "none";
                btnEliminar.removeAttribute("onclick");
                btnEliminar.title =
                    "La primera unidad es obligatoria y no se puede eliminar";
            } else {
                btnEliminar.style.opacity = "1";
                btnEliminar.style.pointerEvents = "auto";
                btnEliminar.setAttribute(
                    "onclick",
                    `eliminarUnidad(${nuevoNumero})`,
                );
                btnEliminar.title = "Eliminar unidad";
            }
        }

        const btnAddPasajero = fila.querySelector(
            `#btn-add-pasajero-${nuevoNumero}`,
        );
        if (btnAddPasajero)
            btnAddPasajero.setAttribute(
                "onclick",
                `agregarPasajero(${nuevoNumero})`,
            );

        const medicamentoSelect = fila.querySelector(
            `#medicamento-${nuevoNumero}`,
        );
        if (medicamentoSelect) {
            medicamentoSelect.setAttribute(
                "onchange",
                `toggleMedicamentoDetalle(${nuevoNumero})`,
            );
        }

        if (fila.querySelector(".unidad-hora-dormir")) {
            fila.querySelector(".unidad-hora-dormir").addEventListener(
                "change",
                function () {
                    calcularHorasDormidas(nuevoNumero);
                    calcularHorasViaje(nuevoNumero);
                    actualizarBotonReunionConvoy();
                },
            );
        }

        if (fila.querySelector(".unidad-hora-levantar")) {
            fila.querySelector(".unidad-hora-levantar").addEventListener(
                "change",
                function () {
                    calcularHorasDormidas(nuevoNumero);
                    calcularHorasViaje(nuevoNumero);
                    actualizarBotonReunionConvoy();
                },
            );
        }

        inicializarAutocompleteConductor(nuevoNumero, false, null);
        calcularHorasViaje(nuevoNumero);

        const inputs = fila.querySelectorAll("input, select");
        inputs.forEach((input) => {
            const name = input.getAttribute("name");
            if (name?.startsWith("unidad")) {
                const newName = name.replace(
                    /unidad\[\d+\]/,
                    `unidad[${nuevoNumero}]`,
                );
                input.setAttribute("name", newName);
            }
        });

        const pasajeroContainer = fila.querySelector(
            `#pasajeros-unidad-${nuevoNumero}`,
        );
        if (pasajeroContainer) {
            pasajeroContainer
                .querySelectorAll(".pasajero-input-group")
                .forEach((group, pIndex) => {
                    const newIndex = pIndex + 1;
                    const input = group.querySelector("input");
                    const removeBtn = group.querySelector(
                        ".btn-remove-pasajero",
                    );

                    group.id = `fila-p-${nuevoNumero}-${newIndex}`;
                    group.dataset.index = newIndex;

                    if (input) {
                        input.id = `p-nombre-${nuevoNumero}-${newIndex}`;
                        input.setAttribute(
                            "name",
                            `unidad[${nuevoNumero}][pasajeros][p${newIndex}][nombre]`,
                        );
                    }

                    if (removeBtn && newIndex > 1) {
                        removeBtn.setAttribute(
                            "onclick",
                            `eliminarPasajero(${nuevoNumero}, ${newIndex})`,
                        );
                    }

                    const icon = fila.querySelector(
                        `#p-icon-${nuevoNumero}-${numeroAnterior}`,
                    );
                    if (icon) icon.id = `p-icon-${nuevoNumero}-${newIndex}`;

                    const badge = fila.querySelector(
                        `#p-badge-${nuevoNumero}-${numeroAnterior}`,
                    );
                    if (badge) badge.id = `p-badge-${nuevoNumero}-${newIndex}`;

                    const autocomplete = fila.querySelector(
                        `#p-autocomplete-${nuevoNumero}-${numeroAnterior}`,
                    );
                    if (autocomplete)
                        autocomplete.id = `p-autocomplete-${nuevoNumero}-${newIndex}`;

                    inicializarAutocompleteConductor(
                        nuevoNumero,
                        true,
                        newIndex,
                    );
                    actualizarIconoPasajero(group, newIndex, nuevoNumero);
                });
            actualizarBotonesPasajeros(nuevoNumero);
            actualizarBotonVerConductor2(nuevoNumero);
        }
    });

    Object.assign(datosInspeccionLigera, nuevosDatosInspeccionLigera);
    Object.assign(datosInspeccionPesada, nuevosDatosInspeccionPesada);

    actualizarContadorUnidades();
    actualizarLabelTipoUnidad();
    actualizarBotonAgregar();
    actualizarBotonReunionConvoy();
}

function actualizarContadorUnidades() {
    const contadorUnidadesElement = document.getElementById("contadorUnidades");
    if (contadorUnidadesElement)
        contadorUnidadesElement.textContent = contadorUnidades;
}

function actualizarLabelTipoUnidad() {
    const labelElement = document.getElementById("label-tipo-unidad");
    if (!labelElement) return;

    if (contadorUnidades === 1) {
        labelElement.textContent = " (Unidad Única)";
        labelElement.classList.remove("convoy");
    } else if (contadorUnidades > 1) {
        labelElement.textContent = " (Convoy de Unidades)";
        labelElement.classList.add("convoy");
    } else {
        labelElement.textContent = "";
        labelElement.classList.remove("convoy");
    }
}

function actualizarBotonAgregar() {
    const boton = document.getElementById("btnAgregarUnidad");
    if (!boton) return;

    if (contadorUnidades >= MAX_UNIDADES) {
        boton.disabled = true;
        boton.textContent = `Límite de ${MAX_UNIDADES} Unidades Alcanzado`;
    } else {
        const restantes = MAX_UNIDADES - contadorUnidades;
        boton.innerHTML = `<i class="fas fa-plus-circle"></i> Agregar Unidad (${restantes} restante${
            restantes !== 1 ? "s" : ""
        })`;
    }
}

function formatearPorcentaje(input) {
    if (input.value !== "") input.value = parseFloat(input.value).toFixed(1);
}

function toggleMedicamentoDetalle(unidadNumero) {
    const select = document.getElementById(`medicamento-${unidadNumero}`);
    const detalleDiv = document.getElementById(
        `medicamento-detalle-${unidadNumero}`,
    );
    if (select && detalleDiv) {
        if (select.value === "si") {
            detalleDiv.style.display = "block";
        } else {
            detalleDiv.style.display = "none";
            const medicamentoNombre = document.getElementById(
                `medicamento-nombre-${unidadNumero}`,
            );
            if (medicamentoNombre) medicamentoNombre.value = "";
        }
    }
}

// ====================================================================
// VALIDACIÓN DE DUPLICADOS EN PERSONAL
// ====================================================================
function validarPersonaDuplicada(inputActual, nombreSeleccionado) {
    if (!nombreSeleccionado) return false;

    let esDuplicado = false;

    const conductores = document.querySelectorAll(".unidad-conductor");
    const pasajeros = document.querySelectorAll('input[id^="p-nombre-"]');
    const todosLosInputs = [...conductores, ...pasajeros];

    for (let i = 0; i < todosLosInputs.length; i++) {
        const otroInput = todosLosInputs[i];
        if (
            otroInput !== inputActual &&
            otroInput.value.trim().toUpperCase() ===
                nombreSeleccionado.trim().toUpperCase()
        ) {
            esDuplicado = true;
            break;
        }
    }

    if (esDuplicado) {
        Swal.fire({
            title: "Personal Duplicado",
            text: `La persona "${nombreSeleccionado}" ya ha sido agregada a este viaje en otra posición o unidad.`,
            icon: "error",
            confirmButtonColor: "#dc3545",
        });
        return true;
    }

    return false;
}

// ====================================================================
// AUTOCOMPLETE DE CONDUCTORES
// ====================================================================
function inicializarAutocompleteConductor(
    unidadNumero,
    esPasajero = false,
    pasajeroIndex = null,
) {
    const inputId = esPasajero
        ? `p-nombre-${unidadNumero}-${pasajeroIndex}`
        : `conductor-${unidadNumero}`;
    const listId = esPasajero
        ? `p-autocomplete-${unidadNumero}-${pasajeroIndex}`
        : `autocomplete-list-${unidadNumero}`;

    const input = document.getElementById(inputId);
    let listContainer = document.getElementById(listId);

    if (!input || !listContainer) return;

    document.body.appendChild(listContainer);
    let currentFocus = -1;

    const positionList = () => {
        if (listContainer.style.display === "none") return;
        const rect = input.getBoundingClientRect();
        listContainer.style.width = rect.width + "px";
        listContainer.style.left = rect.left + "px";
        listContainer.style.top = rect.bottom + 5 + "px";
    };

    window.addEventListener(
        "scroll",
        () => {
            if (listContainer.innerHTML !== "") positionList();
        },
        true,
    );

    window.addEventListener("resize", () => {
        if (listContainer.innerHTML !== "") positionList();
    });

    const updateList = () => {
        const val = input.value;
        document.querySelectorAll(".autocomplete-list").forEach((el) => {
            if (el !== listContainer) el.innerHTML = "";
        });

        currentFocus = -1;

        if (!val) {
            listContainer.style.display = "none";
            listContainer.innerHTML = "";
            if (!esPasajero) actualizarDatosConductor(unidadNumero, "");
            delete input.dataset.conductorSeleccionado;
            delete input.dataset.conductorId; // Limpiamos el ID
            return;
        }

        // Filtramos usando la propiedad 'nombre' del objeto
        const filtered = conductoresGlobales.filter((c) =>
            c.nombre.toUpperCase().includes(val.toUpperCase()),
        );

        listContainer.style.display = "block";
        listContainer.innerHTML = "";

        if (filtered.length === 0) {
            if (!esPasajero) actualizarDatosConductor(unidadNumero, "");
            listContainer.innerHTML = `<div class="autocomplete-item" style="color: red; cursor: default;">No encontrado</div>`;
        } else {
            filtered.forEach((c) => {
                const item = document.createElement("div");
                item.classList.add("autocomplete-item");
                item.innerHTML = c.nombre;

                item.addEventListener("mousedown", function (e) {
                    e.preventDefault();

                    if (validarPersonaDuplicada(input, c.nombre)) {
                        listContainer.innerHTML = "";
                        listContainer.style.display = "none";
                        input.value = "";
                        return;
                    }

                    // GUARDAMOS EL ID Y EL NOMBRE
                    input.value = c.nombre;
                    input.dataset.conductorId = c.id;
                    input.dataset.conductorSeleccionado = c.nombre;

                    listContainer.innerHTML = "";
                    listContainer.style.display = "none";
                    if (!esPasajero) {
                        setTimeout(() => {
                            actualizarDatosConductor(unidadNumero, c.nombre);
                            actualizarBotonReunionConvoy();
                            actualizarBotonEvaluacion();
                        }, 10);
                    }
                });
                listContainer.appendChild(item);
            });
        }
        positionList();
    };

    input.addEventListener("input", updateList);
    input.addEventListener("focus", () => {
        if (input.value.trim() !== "") updateList();
    });

    input.addEventListener("keydown", function (e) {
        let items = listContainer.getElementsByClassName("autocomplete-item");
        if (e.key === "ArrowDown") {
            currentFocus++;
            addActive(items);
        } else if (e.key === "ArrowUp") {
            currentFocus--;
            addActive(items);
        } else if (e.key === "Enter" || e.key === "Tab") {
            if (currentFocus > -1 && items) {
                e.preventDefault();
                const event = new MouseEvent("mousedown", {
                    bubbles: true,
                    cancelable: true,
                    view: window,
                });
                items[currentFocus].dispatchEvent(event);
            } else if (e.key === "Enter") {
                e.preventDefault();
                const exactMatch = conductoresGlobales.find(
                    (c) => c.nombre.toUpperCase() === input.value.toUpperCase(),
                );

                if (exactMatch) {
                    if (validarPersonaDuplicada(input, exactMatch.nombre)) {
                        listContainer.style.display = "none";
                        input.value = "";
                        return;
                    }

                    input.value = exactMatch.nombre;
                    input.dataset.conductorId = exactMatch.id; // GUARDA ID
                    input.dataset.conductorSeleccionado = exactMatch.nombre;
                    listContainer.innerHTML = "";
                    listContainer.style.display = "none";
                    if (!esPasajero) {
                        actualizarDatosConductor(
                            unidadNumero,
                            exactMatch.nombre,
                        );
                        actualizarBotonReunionConvoy();
                        actualizarBotonEvaluacion();
                    }
                } else {
                    listContainer.style.display = "none";
                }
            }
        }
    });

    input.addEventListener("blur", function () {
        const val = input.value.trim();

        if (val) {
            // Buscamos si el usuario lo tecleó a mano pero existe en la BD
            const conductorEncontrado = conductoresGlobales.find(
                (c) => c.nombre.toUpperCase() === val.toUpperCase(),
            );

            if (conductorEncontrado) {
                if (validarPersonaDuplicada(input, val)) {
                    input.value = "";
                    if (!esPasajero) {
                        actualizarDatosConductor(unidadNumero, "");
                        actualizarBotonReunionConvoy();
                    }
                } else {
                    // Si existe y no está duplicado, forzamos a guardar su ID
                    input.dataset.conductorId = conductorEncontrado.id;
                }
            } else {
                // Si el nombre no existe en la base de datos, lo borramos
                input.value = "";
                delete input.dataset.conductorId;
                if (!esPasajero) {
                    actualizarDatosConductor(unidadNumero, "");
                    actualizarBotonReunionConvoy();
                }
            }
        }

        setTimeout(() => {
            listContainer.style.display = "none";
            listContainer.innerHTML = "";
        }, 200);
    });

    function addActive(x) {
        if (!x?.length) return;
        removeActive(x);
        if (currentFocus >= x.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = x.length - 1;
        x[currentFocus].classList.add("selected");
        x[currentFocus].scrollIntoView({ block: "nearest" });
    }

    function removeActive(x) {
        for (let i = 0; i < x.length; i++) x[i].classList.remove("selected");
    }
}
function actualizarDatosConductor(unidadNumero, nombreConductor) {
    const inputVigenciaLic = document.getElementById(
        `vigencia-lic-${unidadNumero}`,
    );
    const inputVigenciaMan = document.getElementById(
        `vigencia-man-${unidadNumero}`,
    );
    const tipoVehiculoDiv = document.getElementById(
        `tipo-vehiculo-${unidadNumero}`,
    );

    const esPesada =
        tipoVehiculoDiv?.textContent.includes("Pesada") ||
        tipoVehiculoDiv?.classList.contains("tipo-pesada") ||
        false;

    const labelLicenciaContainer =
        inputVigenciaLic?.closest(".hour-input-group");
    const labelLicenciaElement = labelLicenciaContainer?.querySelector("label");
    const labelCursoContainer = inputVigenciaMan?.closest(".hour-input-group");
    const labelCursoElement = labelCursoContainer?.querySelector("label");

    if (labelLicenciaElement) {
        if (esPesada) {
            labelLicenciaElement.innerHTML = `<i class="fas fa-id-card"></i> Vigencia Licencia Federal`;
            labelLicenciaElement.style.color = "#f08a1f";
        } else {
            labelLicenciaElement.innerHTML = `<i class="fas fa-id-card"></i> Vigencia Licencia`;
            labelLicenciaElement.style.color = "";
        }
    }

    if (labelCursoElement) {
        if (esPesada) {
            labelCursoElement.innerHTML = `<i class="fas fa-calendar-alt"></i> Vigencia Man. Def. Pesada`;
        } else {
            labelCursoElement.innerHTML = `<i class="fas fa-calendar-alt"></i> Vigencia Man. Def. Ligera`;
        }
        labelCursoElement.style.color = "#0056b3";
    }

    limpiarInputsVigencia(inputVigenciaLic);
    limpiarInputsVigencia(inputVigenciaMan);

    if (!nombreConductor || !datosConductoresGlobales[nombreConductor]) {
        aplicarEstiloGris(inputVigenciaLic);
        aplicarEstiloGris(inputVigenciaMan);
        return;
    }

    const data = datosConductoresGlobales[nombreConductor];
    const validacion = validarDocumentacionConductor(data, esPesada);

    inputVigenciaLic.value = validacion.licencia.mensaje;
    aplicarEstiloPorValidacion(inputVigenciaLic, validacion.licencia.estilo);

    inputVigenciaMan.value = validacion.curso.mensaje;
    aplicarEstiloPorValidacion(inputVigenciaMan, validacion.curso.estilo);

    const problemasDocumentacion = [];
    if (
        validacion.licencia.estilo === "expired" ||
        validacion.licencia.estilo === "missing"
    ) {
        const tipoLic = esPesada ? "Licencia Federal" : "Licencia";
        const estadoTexto =
            validacion.licencia.estilo === "expired"
                ? "VENCIDA"
                : "NO REGISTRADA";
        problemasDocumentacion.push(`${tipoLic} (${estadoTexto})`);
    }

    if (
        validacion.curso.estilo === "expired" ||
        validacion.curso.estilo === "missing"
    ) {
        const tipoCurso = esPesada
            ? "Curso Man. Def. Pesada"
            : "Curso Man. Def. Ligera";
        const estadoTexto =
            validacion.curso.estilo === "expired" ? "VENCIDO" : "NO REGISTRADO";
        problemasDocumentacion.push(`${tipoCurso} (${estadoTexto})`);
    }

    if (problemasDocumentacion.length > 0) {
        const inputConductor = document.getElementById(
            `conductor-${unidadNumero}`,
        );
        Swal.fire({
            title: "¡PROBLEMAS DE DOCUMENTACIÓN!",
            html: `
                <div style="text-align: left;">
                    El conductor <strong>${nombreConductor}</strong> no puede ser asignado.<br><br>
                    Se detectaron los siguientes problemas:
                    <ul style="color: #dc3545; font-weight: bold; margin-top: 10px; text-align: left;">
                        ${problemasDocumentacion
                            .map((prob) => `<li>${prob}</li>`)
                            .join("")}
                    </ul>
                    <br>
                    <i class="fas fa-ban"></i> <strong>ACCIÓN REQUERIDA:</strong><br>
                    Cambie de conductor o actualice sus documentos.
                </div>
            `,
            icon: "error",
            confirmButtonText: "Entendido",
            confirmButtonColor: "#dc3545",
            allowOutsideClick: false,
        }).then(() => {
            if (inputConductor) {
                inputConductor.value = "";
                delete inputConductor.dataset.conductorSeleccionado;
            }
            actualizarDatosConductor(unidadNumero, "");
            actualizarBotonReunionConvoy();
            actualizarBotonEvaluacion();
        });
    }
}

function limpiarInputsVigencia(input) {
    if (!input) return;
    input.value = "";
    input.style.backgroundColor = "";
    input.style.color = "";
    input.style.fontWeight = "normal";
    input.title = "";
}

// ====================================================================
// SELECTOR DE VEHÍCULOS
// ====================================================================
function inicializarSelectorVehiculo(unidadNumero) {
    const triggerId = `vehicle-trigger-${unidadNumero}`;
    const hiddenInputId = `vehicle-hidden-${unidadNumero}`;
    const listId = `vehicle-list-${unidadNumero}`;

    const trigger = document.getElementById(triggerId);
    const hiddenInput = document.getElementById(hiddenInputId);

    let listContainer = document.getElementById(listId);
    if (!listContainer) {
        listContainer = document.createElement("div");
        listContainer.id = listId;
        listContainer.className = "vehicle-dropdown-list";
        document.body.appendChild(listContainer);
    }

    const construirLista = () => {
        let htmlContent = "";
        if (vehiculosLigeros.length === 0 && vehiculosPesados.length === 0) {
            htmlContent = `<div style="padding:10px; color:red;">Cargando datos... o sin vehículos.</div>`;
        } else {
            if (vehiculosLigeros.length > 0) {
                htmlContent += `<div class="vehicle-group-title"><i class="fas fa-car"></i> Unidades Ligeras</div>`;
                vehiculosLigeros.forEach((v) => {
                    htmlContent += `<div class="vehicle-option" data-value="${v}">
                                        <i class="fas fa-car" style="color: #0056b3;"></i> ${v}
                                    </div>`;
                });
            }
            if (vehiculosPesados.length > 0) {
                htmlContent += `<div class="vehicle-group-title"><i class="fas fa-truck"></i> Unidades Pesadas</div>`;
                vehiculosPesados.forEach((v) => {
                    htmlContent += `<div class="vehicle-option" data-value="${v}">
                                        <i class="fas fa-truck" style="color: #f08a1f;"></i> ${v}
                                    </div>`;
                });
            }
        }
        listContainer.innerHTML = htmlContent;
    };

    const positionDropdown = () => {
        if (!trigger || listContainer.style.display === "none") return;
        const rect = trigger.getBoundingClientRect();
        listContainer.style.width = rect.width + "px";
        listContainer.style.left = rect.left + "px";
        listContainer.style.top = rect.bottom + 5 + "px";
    };

    if (trigger) {
        trigger.onclick = (e) => {
            e.stopPropagation();
            document
                .querySelectorAll(".vehicle-dropdown-list")
                .forEach((el) => {
                    if (el.id !== listId) el.style.display = "none";
                });

            if (listContainer.style.display === "block") {
                listContainer.style.display = "none";
            } else {
                construirLista();
                listContainer.style.display = "block";
                positionDropdown();
            }
        };
    }

    listContainer.onclick = (e) => {
        e.stopPropagation();
        const option = e.target.closest(".vehicle-option");
        if (option) {
            const valor = option.dataset.value;
            const iconoElement = option.querySelector("i");
            const iconoHTML = iconoElement ? iconoElement.outerHTML : "";

            trigger.innerHTML = `<span>${iconoHTML} ${valor}</span>`;
            trigger.style.color = "#495057";
            trigger.style.fontWeight = "600";

            hiddenInput.value = valor;

            actualizarTipoVehiculoCustom(unidadNumero, valor);
            actualizarBotonReunionConvoy();

            listContainer.style.display = "none";
        }
    };

    document.addEventListener("click", (e) => {
        if (
            trigger &&
            listContainer &&
            !trigger.contains(e.target) &&
            !listContainer.contains(e.target)
        ) {
            listContainer.style.display = "none";
        }
    });

    window.addEventListener(
        "scroll",
        () => {
            if (listContainer.style.display === "block") positionDropdown();
        },
        true,
    );

    window.addEventListener("resize", () => {
        if (listContainer.style.display === "block") positionDropdown();
    });
}

function actualizarTipoVehiculoCustom(unidadNumero, valorVehiculo) {
    const labelTipo = document.getElementById(`tipo-vehiculo-${unidadNumero}`);
    const inputVigenciaLic = document.getElementById(
        `vigencia-lic-${unidadNumero}`,
    );

    if (!labelTipo) return;

    const tipo = clasificacionVehiculos[valorVehiculo] || "";
    const esPesada = tipo === "Pesada";

    labelTipo.textContent = tipo ? `Unidad ${tipo}` : "";
    labelTipo.className = "tipo-vehiculo-text";

    if (tipo.toLowerCase().includes("ligera")) {
        labelTipo.classList.add("tipo-ligera");
    } else if (tipo.toLowerCase().includes("pesada")) {
        labelTipo.classList.add("tipo-pesada");
    }

    const containerLicencia = inputVigenciaLic?.closest(".hour-input-group");
    const labelLicencia = containerLicencia?.querySelector("label");

    if (labelLicencia) {
        if (esPesada) {
            labelLicencia.innerHTML = `<i class="fas fa-id-card"></i> Vigencia Licencia Federal`;
            labelLicencia.style.color = "#f08a1f";
        } else {
            labelLicencia.innerHTML = `<i class="fas fa-id-card"></i> Vigencia Licencia`;
            labelLicencia.style.color = "";
        }
    }

    const inputConductor = document.getElementById(`conductor-${unidadNumero}`);
    if (inputConductor?.value) {
        actualizarDatosConductor(unidadNumero, inputConductor.value);
    }
}

// ====================================================================
// GESTIÓN DE PASAJEROS Y CONDUCTOR 2
// ====================================================================
function agregarPasajero(unidadNumero) {
    const container = document.getElementById(
        `pasajeros-unidad-${unidadNumero}`,
    );
    if (!container) return;

    const currentPasajeros = container.querySelectorAll(
        ".pasajero-input-group",
    ).length;
    if (currentPasajeros >= MAX_PASAJEROS) {
        Swal.fire(
            "Límite",
            `Solo se permiten ${MAX_PASAJEROS} pasajeros.`,
            "warning",
        );
        return;
    }

    const index = currentPasajeros + 1;
    const div = document.createElement("div");
    div.classList.add("pasajero-input-group");
    div.dataset.index = index;
    div.id = `fila-p-${unidadNumero}-${index}`;
    div.dataset.alcoholPct = "0.0";
    div.dataset.dormir = "";
    div.dataset.levantar = "";
    div.dataset.presionValor = "";
    div.dataset.medicamento = "";
    div.dataset.medicamentoNombre = "";
    div.dataset.esPrincipal = "false";

    const botonEliminarHTML =
        index === 1
            ? `<button type="button" class="btn-remove-pasajero" style="margin-left: 5px; opacity: 0.3; pointer-events: none;" title="El primer pasajero no se puede eliminar">
               <i class="fas fa-trash"></i>
           </button>`
            : `<button type="button" class="btn-remove-pasajero" onclick="eliminarPasajero(${unidadNumero}, ${index})" style="margin-left: 5px;" title="Eliminar pasajero">
               <i class="fas fa-trash"></i>
           </button>`;

    div.innerHTML = `
        <div class="pasajero-nombre-container input-wrapper" style="flex-grow: 1;">
            <i class="fa-solid fa-user field-icon pasajero"
                id="p-icon-${unidadNumero}-${index}"
                title="Clic para asignar como Segundo Conductor"
                onclick="gestionarRolPasajero(${unidadNumero}, ${index})"></i>
            <input type="text" class="table-input has-icon"
                name="unidad[${unidadNumero}][pasajeros][p${index}][nombre]"
                id="p-nombre-${unidadNumero}-${index}"
                placeholder="Pasajero ${index}" autocomplete="off">
            <i class="fas fa-star badge-principal" id="p-badge-${unidadNumero}-${index}" style="display:none; position: absolute; right: 5px; top: 10px;"></i>
            <div class="autocomplete-list" id="p-autocomplete-${unidadNumero}-${index}"></div>
        </div>
        ${botonEliminarHTML}
    `;

    container.appendChild(div);
    inicializarAutocompleteConductor(unidadNumero, true, index);
    actualizarBotonesPasajeros(unidadNumero);

    const btnAddPasajero = document.getElementById(
        `btn-add-pasajero-${unidadNumero}`,
    );
    if (btnAddPasajero && currentPasajeros + 1 >= MAX_PASAJEROS) {
        btnAddPasajero.disabled = true;
    }
}

function eliminarPasajero(unidadNumero, pasajeroIndex) {
    if (pasajeroIndex === 1) {
        Swal.fire({
            title: "Acción No Permitida",
            text: "El primer pasajero no puede ser eliminado.",
            icon: "error",
            confirmButtonColor: "#0056b3",
        });
        return;
    }

    const fila = document.getElementById(
        `fila-p-${unidadNumero}-${pasajeroIndex}`,
    );
    if (fila?.dataset.esPrincipal === "true") {
        Swal.fire({
            title: "Acción No Permitida",
            text: "No puedes eliminar al Conductor Principal de la lista.",
            icon: "error",
        });
        return;
    }

    const container = document.getElementById(
        `pasajeros-unidad-${unidadNumero}`,
    );
    if (container?.querySelectorAll(".pasajero-input-group").length <= 1)
        return;

    if (fila) fila.remove();

    const remainingPasajeros = container.querySelectorAll(
        ".pasajero-input-group",
    );
    remainingPasajeros.forEach((group, idx) => {
        const newIndex = idx + 1;
        const input = group.querySelector("input");
        const removeBtn = group.querySelector(".btn-remove-pasajero");

        group.id = `fila-p-${unidadNumero}-${newIndex}`;
        group.dataset.index = newIndex;

        if (input) {
            input.id = `p-nombre-${unidadNumero}-${newIndex}`;
            input.placeholder = `Pasajero ${newIndex}`;
            input.setAttribute(
                "name",
                `unidad[${unidadNumero}][pasajeros][p${newIndex}][nombre]`,
            );
        }

        if (removeBtn) {
            if (newIndex === 1) {
                removeBtn.style.opacity = "0.3";
                removeBtn.style.pointerEvents = "none";
                removeBtn.removeAttribute("onclick");
                removeBtn.title = "El primer pasajero no se puede eliminar";
            } else {
                removeBtn.style.opacity = "1";
                removeBtn.style.pointerEvents = "auto";
                removeBtn.setAttribute(
                    "onclick",
                    `eliminarPasajero(${unidadNumero}, ${newIndex})`,
                );
                removeBtn.title = "Eliminar pasajero";
            }
        }

        const icon = group.querySelector(".field-icon");
        if (icon) {
            icon.id = `p-icon-${unidadNumero}-${newIndex}`;
            icon.setAttribute(
                "onclick",
                `gestionarRolPasajero(${unidadNumero}, ${newIndex})`,
            );
        }
        const badge = group.querySelector(".badge-principal");
        if (badge) badge.id = `p-badge-${unidadNumero}-${newIndex}`;
        const autocomplete = group.querySelector(".autocomplete-list");
        if (autocomplete)
            autocomplete.id = `p-autocomplete-${unidadNumero}-${newIndex}`;

        inicializarAutocompleteConductor(unidadNumero, true, newIndex);
    });

    if (remainingPasajeros.length < MAX_PASAJEROS) {
        const btnAdd = document.getElementById(
            `btn-add-pasajero-${unidadNumero}`,
        );
        if (btnAdd) btnAdd.disabled = false;
    }

    actualizarBotonVerConductor2(unidadNumero);
}

function gestionarRolPasajero(unidad, index) {
    const fila = document.getElementById(`fila-p-${unidad}-${index}`);
    if (!fila) return;

    const nombre = document.getElementById(
        `p-nombre-${unidad}-${index}`,
    )?.value;
    if (!nombre) {
        Swal.fire(
            "Falta Nombre",
            "Escriba el nombre del pasajero antes de asignarle un rol.",
            "info",
        );
        return;
    }

    if (fila.classList.contains("es-relevo")) {
        Swal.fire({
            title: "¿Quitar asignación de Conductor?",
            text: `¿Desea que ${nombre} deje de ser Conductor? Sus datos serán borrados.`,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sí, degradar a Pasajero",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#d33",
        }).then((result) => {
            if (result.isConfirmed) {
                fila.dataset.alcoholPct = "0.0";
                fila.dataset.dormir = "";
                fila.dataset.levantar = "";
                fila.dataset.presionValor = "";
                fila.dataset.medicamento = "";
                fila.dataset.medicamentoNombre = "";
                fila.dataset.vigenciaLic = "";
                fila.dataset.vigenciaMan = "";
                fila.dataset.hrsDormidas = "";
                fila.dataset.hrDespierto = "";
                fila.dataset.duracionViaje = "";
                fila.dataset.totalHrs = "";
                actualizarIconoPasajero(fila, index, unidad);
                actualizarBotonVerConductor2(unidad);
                Swal.fire(
                    "Actualizado",
                    "El usuario ahora es Pasajero.",
                    "success",
                );
            }
        });
        return;
    }

    const container = document.getElementById(`pasajeros-unidad-${unidad}`);
    if (container) {
        const relevosExistentes =
            container.querySelectorAll(".es-relevo").length;
        if (relevosExistentes >= 1) {
            Swal.fire({
                title: "Límite de Conductores Alcanzado",
                html: `Ya existe un Segundo Conductor asignado.<br>Solo se permiten 2 conductores por unidad.`,
                icon: "warning",
                confirmButtonColor: "#f08a1f",
            });
            return;
        }
    }

    const tipoVehiculoDiv = document.getElementById(`tipo-vehiculo-${unidad}`);
    const esPesada =
        tipoVehiculoDiv?.textContent.includes("Pesada") ||
        tipoVehiculoDiv?.classList.contains("tipo-pesada") ||
        false;

    const horaInicioViaje = document.getElementById("horaInicioViaje")?.value;
    const horaFinViaje = document.getElementById("horaFinViaje")?.value;
    const duracionViajeStr = calcularDiferenciaHoras(
        horaInicioViaje,
        horaFinViaje,
    );

    const labelLicenciaModal = esPesada
        ? "Licencia Federal"
        : "Vigencia Licencia";
    const labelCursoModal = esPesada
        ? "Man. Defensivo (Pesada)"
        : "Man. Defensivo (Ligera)";

    Swal.fire({
        title: `<div style="font-size: 1.1em; color: #0056b3; border-bottom: 2px solid #e9ecef; padding-bottom: 10px;">Asignar Segundo Conductor:<br><span style="color: #333; font-weight: 700;">${nombre}</span></div>`,
        html: `
            <style>
                .swal-modal-grid-compact {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 12px 15px;
                    text-align: left;
                    margin-top: 5px;
                }
                .swal-field-group { display: flex; flex-direction: column; }
                .swal-field-group label {
                    font-size: 0.75rem;
                    font-weight: 700;
                    margin-bottom: 4px;
                    color: #6c757d;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                .swal-field-group input, .swal-field-group select {
                    width: 100%;
                    padding: 8px 10px;
                    border: 1px solid #ced4da;
                    border-radius: 6px;
                    font-size: 0.9rem;
                    transition: border-color 0.2s, box-shadow 0.2s;
                }
                .swal-field-group input:focus, .swal-field-group select:focus {
                    border-color: #80bdff;
                    outline: 0;
                    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
                }
                .swal-section-title {
                    grid-column: span 2;
                    font-size: 0.85rem;
                    color: #0056b3;
                    padding-bottom: 2px;
                    border-bottom: 1px dashed #dee2e6;
                    margin-top: 15px;
                    margin-bottom: 2px;
                    font-weight: 800;
                    text-transform: uppercase;
                }
                .input-readonly-colored {
                    font-weight: bold;
                    background-color: #e9ecef;
                    cursor: not-allowed;
                }
            </style>
            <div class="swal-modal-grid-compact">
                <div class="swal-section-title"><i class="fas fa-user-md"></i> Estado Físico</div>
                <div class="swal-field-group">
                    <label>Alcoholimetría (%)</label>
                    <input id="swal-alcohol-pct" type="number" step="0.1" value="0.0" required>
                </div>
                <div class="swal-field-group">
                    <label>Presión Arterial</label>
                    <input id="swal-presion-valor" type="text" placeholder="Ej: 120/80">
                </div>
                <div class="swal-field-group">
                    <label>Toma Medicamento</label>
                    <select id="swal-medicamento">
                        <option value="">Seleccione...</option>
                        <option value="si">Sí</option>
                        <option value="no">No</option>
                    </select>
                </div>
                <div class="swal-field-group" id="swal-medicamento-detalle-container" style="display: none;">
                    <label>¿Cuál medicamento?</label>
                    <input id="swal-medicamento-nombre" type="text" placeholder="Especifique nombre...">
                </div>
                <div class="swal-section-title"><i class="fas fa-file-alt"></i> Documentación</div>
                <div class="swal-field-group">
                    <label>${labelLicenciaModal}</label>
                    <input id="swal-vigencia-lic" class="input-readonly-colored" type="text" placeholder="Verificando..." readonly>
                    <input type="hidden" id="swal-hidden-lic-status" value="invalid">
                </div>
                <div class="swal-field-group">
                    <label>${labelCursoModal}</label>
                    <input id="swal-vigencia-man" class="input-readonly-colored" type="text" placeholder="Verificando..." readonly>
                    <input type="hidden" id="swal-hidden-man-status" value="invalid">
                </div>
                <div class="swal-section-title"><i class="fas fa-bed"></i> Gestión de Sueño y Fatiga</div>
                <div class="swal-field-group">
                    <label>Hora Dormir</label>
                    <input id="swal-dormir" class="flatpickr-modal" placeholder="HH:MM">
                </div>
                <div class="swal-field-group">
                    <label>Hora Despertar</label>
                    <input id="swal-levantar" class="flatpickr-modal" placeholder="HH:MM">
                </div>
                <div class="swal-field-group">
                    <label>Hrs Dormidas</label>
                    <input id="swal-hrs-dormidas" type="text" placeholder="0:00" readonly style="background-color: #f8f9fa;">
                </div>
                <div class="swal-field-group">
                    <label>Hr Despierto</label>
                    <input id="swal-hr-despierto" type="text" placeholder="0:00" readonly style="background-color: #f8f9fa;">
                </div>
                <div class="swal-field-group">
                    <label>Duración Viaje</label>
                    <input id="swal-duracion-viaje" type="text" value="${duracionViajeStr}" placeholder="0:00" readonly style="background-color: #f8f9fa;">
                </div>
                <div class="swal-field-group" style="position: relative;">
                    <label>Total Horas Activo</label>
                    <input id="swal-total-hrs" type="text" placeholder="0:00" readonly style="font-weight: 800; background-color: #e9ecef;">
                    <div id="swal-warning-msg" style="color: #dc3545; font-size: 0.75rem; font-weight: 700; display: none; margin-top: 4px; position: absolute; bottom: -18px;">
                        <i class="fas fa-exclamation-triangle"></i> Excede 15 hrs sugeridas
                    </div>
                </div>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-save"></i> Guardar Conductor',
        confirmButtonColor: "#28a745",
        cancelButtonText: "Cancelar",
        width: "650px",

        didOpen: () => {
            flatpickr(".flatpickr-modal", configHora);

            const medicamentoSelect =
                document.getElementById("swal-medicamento");
            const medicamentoDetalle = document.getElementById(
                "swal-medicamento-detalle-container",
            );
            if (medicamentoSelect && medicamentoDetalle) {
                medicamentoSelect.addEventListener("change", function () {
                    if (this.value === "si") {
                        medicamentoDetalle.style.display = "flex";
                    } else {
                        medicamentoDetalle.style.display = "none";
                        document.getElementById(
                            "swal-medicamento-nombre",
                        ).value = "";
                    }
                });
            }

            const nombreConductor = document.getElementById(
                `p-nombre-${unidad}-${index}`,
            ).value;
            const data = datosConductoresGlobales[nombreConductor];
            const licInput = document.getElementById("swal-vigencia-lic");
            const manInput = document.getElementById("swal-vigencia-man");
            const licStatus = document.getElementById("swal-hidden-lic-status");
            const manStatus = document.getElementById("swal-hidden-man-status");

            if (data) {
                const validacion = validarDocumentacionConductor(
                    data,
                    esPesada,
                );
                licInput.value = validacion.licencia.mensaje;
                licStatus.value = validacion.licencia.estilo;
                aplicarEstiloPorValidacion(
                    licInput,
                    validacion.licencia.estilo,
                );
                manInput.value = validacion.curso.mensaje;
                manStatus.value = validacion.curso.estilo;
                aplicarEstiloPorValidacion(manInput, validacion.curso.estilo);
            } else {
                licInput.value = "Conductor no encontrado";
                manInput.value = "Conductor no encontrado";
                aplicarEstiloGris(licInput);
                aplicarEstiloGris(manInput);
                licStatus.value = "missing";
                manStatus.value = "missing";
            }

            const dormirInput = document.getElementById("swal-dormir");
            const levantarInput = document.getElementById("swal-levantar");
            const hrsDormidasInput =
                document.getElementById("swal-hrs-dormidas");
            const hrDespiertoInput =
                document.getElementById("swal-hr-despierto");
            const duracionViajeInput = document.getElementById(
                "swal-duracion-viaje",
            );
            const totalHrsInput = document.getElementById("swal-total-hrs");
            const warningMsg = document.getElementById("swal-warning-msg");

            function actualizarHorasModal() {
                const dormir = dormirInput.value;
                const levantar = levantarInput.value;

                const totalMinutosDormir = parseTimeForCalculation(dormir);
                let totalMinutosLevantar = parseTimeForCalculation(levantar);

                if (
                    totalMinutosDormir !== null &&
                    totalMinutosLevantar !== null
                ) {
                    if (totalMinutosLevantar <= totalMinutosDormir)
                        totalMinutosLevantar += 24 * 60;
                    hrsDormidasInput.value = minutosAStringHora(
                        totalMinutosLevantar - totalMinutosDormir,
                    );
                } else {
                    hrsDormidasInput.value = "0:00";
                }

                const minutosLevantar = parseTimeForCalculation(levantar);
                const minutosInicioViaje =
                    parseTimeForCalculation(horaInicioViaje);

                if (minutosLevantar !== null && minutosInicioViaje !== null) {
                    let diferencia = minutosInicioViaje - minutosLevantar;
                    if (diferencia < 0) diferencia += 24 * 60;
                    hrDespiertoInput.value = minutosAStringHora(diferencia);
                } else {
                    hrDespiertoInput.value = "0:00";
                }

                const minutosDespierto = stringHoraAMinutos(
                    hrDespiertoInput.value,
                );
                const minutosViaje = stringHoraAMinutos(
                    duracionViajeInput.value,
                );
                const totalMinutos = minutosDespierto + minutosViaje;
                totalHrsInput.value = minutosAStringHora(totalMinutos);

                if (totalMinutos > 900) {
                    totalHrsInput.style.backgroundColor = "#dc3545";
                    totalHrsInput.style.color = "#ffffff";
                    warningMsg.style.display = "block";
                    totalHrsInput.dataset.excede = "true";
                } else {
                    totalHrsInput.style.backgroundColor = "#e9ecef";
                    totalHrsInput.style.color = "#495057";
                    warningMsg.style.display = "none";
                    totalHrsInput.dataset.excede = "false";
                }
            }

            if (dormirInput)
                dormirInput.addEventListener("change", actualizarHorasModal);
            if (levantarInput)
                levantarInput.addEventListener("change", actualizarHorasModal);
            actualizarHorasModal();
        },

        preConfirm: () => {
            const alcoholPct =
                document.getElementById("swal-alcohol-pct").value;
            const dormir = document.getElementById("swal-dormir").value;
            const levantar = document.getElementById("swal-levantar").value;
            const medicamento =
                document.getElementById("swal-medicamento").value;
            const medicamentoNombre = document.getElementById(
                "swal-medicamento-nombre",
            ).value;

            if (!alcoholPct) {
                Swal.showValidationMessage(
                    "Ingrese el porcentaje de alcoholimetría",
                );
                return false;
            }
            if (parseFloat(alcoholPct) > 0.4) {
                Swal.showValidationMessage("Alcoholimetría no apta (> 0.4)");
                return false;
            }
            if (!dormir || !levantar) {
                Swal.showValidationMessage(
                    "Complete las horas de dormir y despertar",
                );
                return false;
            }
            if (!medicamento) {
                Swal.showValidationMessage("Seleccione si toma medicamento");
                return false;
            }
            if (medicamento === "si" && !medicamentoNombre) {
                Swal.showValidationMessage(
                    "Especifique el nombre del medicamento",
                );
                return false;
            }

            const licStatus = document.getElementById(
                "swal-hidden-lic-status",
            ).value;
            const manStatus = document.getElementById(
                "swal-hidden-man-status",
            ).value;
            const erroresDocs = [];

            if (licStatus === "expired" || licStatus === "missing") {
                erroresDocs.push(
                    `${esPesada ? "Licencia Federal" : "Licencia"} (${
                        licStatus === "expired" ? "VENCIDA" : "NO REGISTRADA"
                    })`,
                );
            }
            if (manStatus === "expired" || manStatus === "missing") {
                erroresDocs.push(
                    `${
                        esPesada
                            ? "Curso Man. Def. Pesada"
                            : "Curso Man. Def. Ligera"
                    } (${
                        manStatus === "expired" ? "VENCIDO" : "NO REGISTRADO"
                    })`,
                );
            }

            if (erroresDocs.length > 0) {
                Swal.showValidationMessage(
                    `NO SE PUEDE ASIGNAR:<br>• ${erroresDocs.join("<br>• ")}`,
                );
                return false;
            }

            return {
                alcoholPct,
                dormir,
                levantar,
                presionValor:
                    document.getElementById("swal-presion-valor").value,
                medicamento,
                medicamentoNombre,
                vigenciaLic: document.getElementById("swal-vigencia-lic").value,
                vigenciaMan: document.getElementById("swal-vigencia-man").value,
                hrsDormidas: document.getElementById("swal-hrs-dormidas").value,
                hrDespierto: document.getElementById("swal-hr-despierto").value,
                duracionViaje: document.getElementById("swal-duracion-viaje")
                    .value,
                totalHrs: document.getElementById("swal-total-hrs").value,
            };
        },
    }).then((result) => {
        if (result.isConfirmed) {
            const datos = result.value;
            Object.assign(fila.dataset, {
                alcoholPct: datos.alcoholPct,
                dormir: datos.dormir,
                levantar: datos.levantar,
                presionValor: datos.presionValor,
                medicamento: datos.medicamento,
                medicamentoNombre: datos.medicamentoNombre,
                vigenciaLic: datos.vigenciaLic,
                vigenciaMan: datos.vigenciaMan,
                hrsDormidas: datos.hrsDormidas,
                hrDespierto: datos.hrDespierto,
                duracionViaje: datos.duracionViaje,
                totalHrs: datos.totalHrs,
            });

            actualizarIconoPasajero(fila, index, unidad);
            actualizarBotonVerConductor2(unidad);

            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: "Segundo Conductor Asignado Correctamente",
                showConfirmButton: false,
                timer: 3000,
            });
        }
    });
}

function mostrarInfoConductor2(unidadNumero) {
    const container = document.getElementById(
        `pasajeros-unidad-${unidadNumero}`,
    );
    const filaRelevo = container?.querySelector(
        ".pasajero-input-group.es-relevo",
    );

    if (!filaRelevo) {
        Swal.fire({
            title: "No hay Segundo Conductor",
            text: "No se ha asignado un Segundo Conductor para esta unidad.",
            icon: "info",
            confirmButtonColor: "#f08a1f",
        });
        return;
    }

    const tipoVehiculoDiv = document.getElementById(
        `tipo-vehiculo-${unidadNumero}`,
    );
    const esPesada =
        tipoVehiculoDiv?.textContent.includes("Pesada") ||
        tipoVehiculoDiv?.classList.contains("tipo-pesada") ||
        false;

    const pIdx = filaRelevo.dataset.index;
    const pNombre = document.getElementById(
        `p-nombre-${unidadNumero}-${pIdx}`,
    ).value;
    const pAlcoholPct = filaRelevo.dataset.alcoholPct || "0.0";
    const pDormir = filaRelevo.dataset.dormir || "";
    const pLevantar = filaRelevo.dataset.levantar || "";
    const pPresionValor = filaRelevo.dataset.presionValor || "";
    const pMedicamento = filaRelevo.dataset.medicamento || "";
    const pMedicamentoNombre = filaRelevo.dataset.medicamentoNombre || "";
    const pVigenciaLic = filaRelevo.dataset.vigenciaLic || "";
    const pVigenciaMan = filaRelevo.dataset.vigenciaMan || "";
    const pHrsDormidas = filaRelevo.dataset.hrsDormidas || "";
    const pHrDespierto = filaRelevo.dataset.hrDespierto || "";
    const pDuracionViaje = filaRelevo.dataset.duracionViaje || "";
    const pTotalHrs = filaRelevo.dataset.totalHrs || "";

    Swal.fire({
        title: "Información del Segundo Conductor",
        html: `
            <div style="text-align: left; max-width: 600px; margin: 0 auto;">
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    <h3 style="color: #0056b3; margin-top: 0;">${pNombre}</h3>
                    <div style="display: flex; gap: 15px; margin-top: 10px;">
                        <div>
                            <strong>${
                                esPesada
                                    ? "Licencia Federal"
                                    : "Vigencia Licencia"
                            }:</strong><br>
                            <span style="color: ${
                                pVigenciaLic.includes("No registrada")
                                    ? "#dc3545"
                                    : "#28a745"
                            }">${pVigenciaLic || "No registrada"}</span>
                        </div>
                        <div>
                            <strong>${
                                esPesada
                                    ? "Man. Def. Pesada"
                                    : "Man. Def. Ligera"
                            }:</strong><br>
                            <span style="color: ${
                                pVigenciaMan.includes("No registrada")
                                    ? "#dc3545"
                                    : "#28a745"
                            }">${pVigenciaMan || "No registrada"}</span>
                        </div>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <strong><i class="fas fa-wind"></i> Alcoholimetría:</strong><br>
                        <span style="color: ${
                            parseFloat(pAlcoholPct) === 0
                                ? "#28a745"
                                : "#dc3545"
                        }">${pAlcoholPct}%</span>
                    </div>
                    <div>
                        <strong><i class="fas fa-heartbeat"></i> Presión Arterial:</strong><br>
                        <span>${pPresionValor || "No registrada"}</span>
                    </div>
                    <div>
                        <strong><i class="fas fa-bed"></i> Hora Durmió:</strong><br>
                        <span>${pDormir || "No registrada"}</span>
                    </div>
                    <div>
                        <strong><i class="fas fa-sun"></i> Hora Despertó:</strong><br>
                        <span>${pLevantar || "No registrada"}</span>
                    </div>
                    <div>
                        <strong><i class="fas fa-hourglass-half"></i> Horas Dormidas:</strong><br>
                        <span>${pHrsDormidas || "No calculado"}</span>
                    </div>
                    <div>
                        <strong><i class="fas fa-pills"></i> Toma Medicamento:</strong><br>
                        <span style="color: ${
                            pMedicamento === "si" ? "#ffc107" : "#28a745"
                        }">${
                            pMedicamento === "si"
                                ? `Sí (${pMedicamentoNombre})`
                                : "No"
                        }</span>
                    </div>
                </div>
                <div style="background: #e9ecef; padding: 10px; border-radius: 6px; margin-top: 15px;">
                    <h4 style="color: #0056b3; margin-top: 0; margin-bottom: 10px;">Horas de Conducción:</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div><strong>Hr Despierto:</strong><br><span>${
                            pHrDespierto || "No calculado"
                        }</span></div>
                        <div><strong>Duración Viaje:</strong><br><span>${
                            pDuracionViaje || "No calculado"
                        }</span></div>
                        <div style="grid-column: span 2;">
                            <strong>Total Hrs:</strong><br>
                            <span style="font-weight: bold; color: #0056b3;">${
                                pTotalHrs || "No calculado"
                            }</span>
                        </div>
                    </div>
                </div>
            </div>
        `,
        icon: "info",
        confirmButtonText: "Cerrar",
        confirmButtonColor: "#0056b3",
        width: 650,
    });
}

function actualizarBotonVerConductor2(unidadNumero) {
    const filaUnidad = document.getElementById(`unidad-${unidadNumero}`);
    if (!filaUnidad) return;

    const btnVer = filaUnidad.querySelector(".btn-ver-conductor2");
    if (!btnVer) return;

    const container = document.getElementById(
        `pasajeros-unidad-${unidadNumero}`,
    );
    const hayRelevo = container?.querySelector(".es-relevo");

    btnVer.style.display = hayRelevo ? "inline-flex" : "none";
}

function actualizarIconoPasajero(fila, index, unidad) {
    const icon = document.getElementById(`p-icon-${unidad}-${index}`);
    const badgeStar = document.getElementById(`p-badge-${unidad}-${index}`);

    if (fila.dataset.alcoholPct && fila.dataset.dormir) {
        if (icon) {
            icon.className = "fa-solid fa-truck-front field-icon relevo";
            icon.title = "Conductor Relevo (Clic para ver/quitar)";
        }
        fila.classList.add("es-relevo");
    } else {
        if (icon) {
            icon.className = "fa-solid fa-user field-icon pasajero";
            icon.title = "Pasajero (Clic para asignar como Relevo)";
        }
        fila.classList.remove("es-relevo");
    }

    if (badgeStar) {
        badgeStar.style.display =
            fila.dataset.esPrincipal === "true" ? "inline-block" : "none";
        if (fila.dataset.esPrincipal === "true")
            badgeStar.title = "Conductor Principal (Descansando)";
    }
}

function actualizarBotonesPasajeros(unidadNumero) {
    const container = document.getElementById(
        `pasajeros-unidad-${unidadNumero}`,
    );
    if (!container) return;

    const grupos = container.querySelectorAll(".pasajero-input-group");
    grupos.forEach((grupo, index) => {
        const btn = grupo.querySelector(".btn-remove-pasajero");
        if (!btn) return;

        if (index === 0) {
            btn.style.opacity = "0.3";
            btn.style.pointerEvents = "none";
        } else {
            btn.style.opacity = "1";
            btn.style.pointerEvents = "auto";
        }
    });
}

// ====================================================================
// MODALES DE INSPECCIÓN
// ====================================================================
function abrirInspeccion(unidadNumero) {
    const inputConductor = document.getElementById(`conductor-${unidadNumero}`);
    const nombreConductor = inputConductor ? inputConductor.value.trim() : "";

    if (!nombreConductor) {
        Swal.fire({
            title: "Falta Conductor",
            text: "Por favor, seleccione el Conductor de la unidad antes de realizar la inspección.",
            icon: "warning",
            confirmButtonColor: "#0056b3",
        });
        if (inputConductor) inputConductor.focus();
        return;
    }

    const hiddenInput = document.getElementById(
        `vehicle-hidden-${unidadNumero}`,
    );
    const vehiculo = hiddenInput?.value || "";

    if (!vehiculo?.trim()) {
        Swal.fire({
            title: "Seleccione un vehículo",
            text: "Primero seleccione un vehículo para saber qué tipo de inspección realizar.",
            icon: "warning",
            confirmButtonColor: "#0056b3",
        });
        return;
    }

    const tipo = clasificacionVehiculos[vehiculo] || "";
    if (tipo === "Ligera") {
        gestionarModalInspeccionLigera(true, unidadNumero);
    } else if (tipo === "Pesada") {
        gestionarModalInspeccionPesada(true, unidadNumero);
    } else {
        Swal.fire({
            title: "Tipo de vehículo no reconocido",
            text: "El vehículo seleccionado no está clasificado como ligero o pesado.",
            icon: "warning",
            confirmButtonColor: "#0056b3",
        });
    }
}

function configurarEventosAnomalias() {
    const radiosLigera = document.getElementsByName("anomalias_ligera");
    radiosLigera.forEach((radio) => {
        radio.addEventListener("change", function () {
            const container = document.getElementById(
                "evidenceContainerLigera",
            );
            if (this.value === "si") {
                container.style.display = "block";
            } else {
                container.style.display = "none";
            }
        });
    });

    const radiosPesada = document.getElementsByName("anomalias_pesada");
    radiosPesada.forEach((radio) => {
        radio.addEventListener("change", function () {
            const container = document.getElementById(
                "evidenceContainerPesada",
            );
            if (this.value === "si") {
                container.style.display = "block";
            } else {
                container.style.display = "none";
            }
        });
    });
}

// ====================================================================
// FORMATEO DE KILOMETRAJE CON COMAS
// ====================================================================
function formatearKilometraje(input) {
    // 1. Quitar cualquier cosa que no sea un número (letras, símbolos, o comas previas)
    let valorLimpio = input.value.replace(/\D/g, "");

    // 2. Si está vacío, no hacer nada
    if (valorLimpio === "") {
        input.value = "";
        return;
    }

    // 3. Formatear con comas para los miles
    input.value = parseInt(valorLimpio, 10).toLocaleString("en-US");
}

// ====================================================================
// FUNCIÓN PARA CONVERTIR FOTOS A BASE64
// ====================================================================
function convertirArchivoABase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => resolve(reader.result);
        reader.onerror = (error) => reject(error);
    });
}

function gestionarModalInspeccionLigera(
    abrir,
    unidadNumero = null,
    guardadoExitoso = false,
) {
    const modal = document.getElementById("modalInspeccionLigera");

    if (abrir && unidadNumero !== null) {
        unidadEnInspeccion = unidadNumero;
        tipoVehiculoInspeccion = "ligera";
        fotosSubidas.ligera = [];
        actualizarVistaPrevia("ligera");

        const inputConductorPrincipal = document.getElementById(
            `conductor-${unidadNumero}`,
        );
        const nombreConductor = inputConductorPrincipal
            ? inputConductorPrincipal.value
            : "";
        const hiddenInput = document.getElementById(
            `vehicle-hidden-${unidadNumero}`,
        );
        const numeroEconomico = hiddenInput?.value || "";
        const nombreMostrar = numeroEconomico || "Unidad " + unidadNumero;
        let marca = "";
        let propiedad = "";

        if (numeroEconomico && detallesVehiculos[numeroEconomico]) {
            marca = detallesVehiculos[numeroEconomico].marca || "Sin Marca";
            propiedad =
                detallesVehiculos[numeroEconomico].propiedad || "Desconocido";
        }

        document.getElementById("inputNombreConductorLigera").value =
            nombreConductor;
        document.getElementById("inputNoEconomicoLigera").value = nombreMostrar;
        document.getElementById("inputMarcaLigera").value = marca;
        document.getElementById("inputVesRentadaLigera").value = propiedad;
        document.getElementById("inspeccionUnidadIndexLigera").value =
            unidadNumero;

        const savedData = datosInspeccionLigera[unidadNumero] || {};

        const allRadios = modal.querySelectorAll('input[type="radio"]');
        allRadios.forEach((r) => (r.checked = false));

        const inputKm = document.querySelector(
            '#modalInspeccionLigera input[name="kilometraje"]',
        );
        if (inputKm) inputKm.value = savedData.kilometraje || "";
        const selectGas = document.querySelector(
            '#modalInspeccionLigera select[name="nivel_gasolina"]',
        );
        if (selectGas) selectGas.value = savedData.nivel_gasolina || "";

        const allItems = [
            "doc_tarjeta",
            "doc_poliza",
            "doc_tel_emergencia",
            "doc_licencia",
            "vis_botiquin",
            "vis_triangulo",
            "vis_extintor",
            "vis_gato",
            "vis_cables",
            "vis_herramientas",
            "vis_linterna",
            "vis_espejos",
            "vis_refaccion",
            "vis_neumaticos",
            "vis_pintura",
            "vis_parabrisas",
            "vis_defensas",
            "vis_luces_gral",
            "vis_luces_stop",
            "vis_claxon",
            "vis_logos",
            "vis_asientos",
            "vis_panel",
            "vis_cinturones",
            "mant_fecha_km",
            "mant_fugas",
            "mant_niveles",
            "mant_bandas",
        ];

        allItems.forEach((item) => {
            if (savedData[item]) {
                const radio = document.querySelector(
                    `#modalInspeccionLigera input[name="${item}"][value="${savedData[item]}"]`,
                );
                if (radio) radio.checked = true;
            }
        });

        const containerEvidencia = document.getElementById(
            "evidenceContainerLigera",
        );
        if (savedData.anomalias_detectadas) {
            const radioAnomalia = document.querySelector(
                `#modalInspeccionLigera input[name="anomalias_ligera"][value="${savedData.anomalias_detectadas}"]`,
            );
            if (radioAnomalia) radioAnomalia.checked = true;
            containerEvidencia.style.display =
                savedData.anomalias_detectadas === "si" ? "block" : "none";
        } else {
            containerEvidencia.style.display = "none";
        }

        const comentariosInput = document.getElementById(
            "comentariosInspeccionLigera",
        );
        if (comentariosInput)
            comentariosInput.value = savedData.comentarios || "";

        if (modal) {
            modal.classList.add("active");
            document.body.style.overflow = "hidden";
        }
    } else if (!abrir) {
        if (guardadoExitoso) {
            if (modal) modal.classList.remove("active");
            document.body.style.overflow = "auto";

            unidadEnInspeccion = null;
            tipoVehiculoInspeccion = null;
            fotosSubidas.ligera = [];
            const comentariosInput = document.getElementById(
                "comentariosInspeccionLigera",
            );
            if (comentariosInput) comentariosInput.value = "";
            const fileInput = document.getElementById(
                "evidenciaInspeccionLigera",
            );
            if (fileInput) fileInput.value = "";

            return;
        }

        const comentarios =
            document
                .getElementById("comentariosInspeccionLigera")
                ?.value.trim() || "";
        const hayFotos = fotosSubidas.ligera.length > 0;

        if (comentarios || hayFotos) {
            limpiarFotosAlCerrarModal("ligera");
        } else {
            if (modal) modal.classList.remove("active");
            document.body.style.overflow = "auto";
            unidadEnInspeccion = null;
            tipoVehiculoInspeccion = null;
        }
    }
}

function guardarInspeccionLigera() {
    const form = document.getElementById("formInspeccionLigera");

    const anomaliasRadio = form.querySelector(
        'input[name="anomalias_ligera"]:checked',
    );
    if (!anomaliasRadio) {
        Swal.fire(
            "Atención",
            "Debe indicar si detectó anomalías (Sí o No).",
            "warning",
        );
        return;
    }

    const hayAnomalias = anomaliasRadio.value === "si";
    const comentarios =
        document.getElementById("comentariosInspeccionLigera")?.value.trim() ||
        "";
    const tieneFotos = fotosSubidas.ligera.length > 0;

    if (hayAnomalias) {
        if (comentarios === "") {
            Swal.fire(
                "Comentario Requerido",
                "Debe describir las anomalías detectadas.",
                "warning",
            );
            return;
        }
        if (!tieneFotos) {
            Swal.fire({
                title: "¿Sin evidencia fotográfica?",
                text: "Reportó anomalías sin fotos. ¿Desea continuar?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Continuar",
                cancelButtonText: "Agregar fotos",
                confirmButtonColor: "#0056b3",
                cancelButtonColor: "#dc3545",
            }).then((result) => {
                if (result.isConfirmed)
                    procederGuardadoLigera(comentarios, "si");
            });
            return;
        }
        procederGuardadoLigera(comentarios, "si");
    } else {
        procederGuardadoLigera(comentarios, "no");
    }
}

async function procederGuardadoLigera(comentarios, anomaliasValor) {
    const form = document.getElementById("formInspeccionLigera");
    const unidadNumero = parseInt(
        document.getElementById("inspeccionUnidadIndexLigera")?.value,
    );

    const kilometrajeRaw =
        form.querySelector('input[name="kilometraje"]')?.value || "0";
    const kilometraje = kilometrajeRaw.replace(/,/g, "");
    const nivelGasolina = form.querySelector(
        'select[name="nivel_gasolina"]',
    )?.value;

    const docItems = [
        "doc_tarjeta",
        "doc_poliza",
        "doc_tel_emergencia",
        "doc_licencia",
    ];
    const visualItems = [
        "vis_botiquin",
        "vis_triangulo",
        "vis_extintor",
        "vis_gato",
        "vis_cables",
        "vis_herramientas",
        "vis_linterna",
        "vis_espejos",
        "vis_refaccion",
        "vis_neumaticos",
        "vis_pintura",
        "vis_parabrisas",
        "vis_defensas",
        "vis_luces_gral",
        "vis_luces_stop",
        "vis_claxon",
        "vis_logos",
        "vis_asientos",
        "vis_panel",
        "vis_cinturones",
    ];
    const mantItems = [
        "mant_fecha_km",
        "mant_fugas",
        "mant_niveles",
        "mant_bandas",
    ];

    const data = {};
    let tieneItemsNo = false;

    docItems.forEach((item) => {
        const input = form.querySelector(`input[name="${item}"]:checked`);
        if (input) {
            data[item] = input.value;
            if (input.value === "no") tieneItemsNo = true;
        }
    });
    visualItems.forEach((item) => {
        const input = form.querySelector(`input[name="${item}"]:checked`);
        if (input) {
            data[item] = input.value;
            if (input.value === "no") tieneItemsNo = true;
        }
    });
    mantItems.forEach((item) => {
        const input = form.querySelector(`input[name="${item}"]:checked`);
        if (input) {
            data[item] = input.value;
            if (input.value === "no") tieneItemsNo = true;
        }
    });

    data.kilometraje = kilometraje;
    data.nivel_gasolina = nivelGasolina;
    data.anomalias_detectadas = anomaliasValor;
    data.comentarios = comentarios;

    // AQUI ESTÁ EL CAMBIO PARA LAS FOTOS (Convertir a Base64)
    const arrayFotosBase64 = [];
    for (let i = 0; i < fotosSubidas.ligera.length; i++) {
        let foto = fotosSubidas.ligera[i];
        let base64String = await convertirArchivoABase64(foto.file);
        arrayFotosBase64.push({
            nombre: foto.name,
            tamaño: foto.size,
            tipo: foto.type,
            base64: base64String, // <--- ¡Esto es lo que el PHP estaba esperando!
        });
    }

    // Ahora lo guardamos exactamente donde el PHP lo busca
    data.fotos = arrayFotosBase64;

    datosInspeccionLigera[unidadNumero] = data;

    const btnInspeccion = document.getElementById(
        `btn-inspeccion-${unidadNumero}`,
    );
    if (btnInspeccion) {
        btnInspeccion.dataset.realizada = "true";
        if (tieneItemsNo || anomaliasValor === "si") {
            btnInspeccion.classList.remove(
                "btn-inspeccion",
                "btn-inspeccion-realizada-ok",
            );
            btnInspeccion.classList.add("btn-inspeccion-realizada-warning");
            btnInspeccion.innerHTML =
                '<i class="fas fa-exclamation-triangle"></i> Realizado';
            btnInspeccion.dataset.tieneNos = "true";
            btnInspeccion.title = "Inspección realizada con observaciones";
        } else {
            btnInspeccion.classList.remove(
                "btn-inspeccion",
                "btn-inspeccion-realizada-warning",
            );
            btnInspeccion.classList.add("btn-inspeccion-realizada-ok");
            btnInspeccion.innerHTML =
                '<i class="fas fa-check-circle"></i> Realizado';
            btnInspeccion.dataset.tieneNos = "false";
            btnInspeccion.title = "Inspección realizada sin observaciones";
        }
    }

    Swal.fire({
        title: "Inspección Realizada",
        html: `Unidad ${unidadNumero} inspeccionada.<br><small>Anomalías reportadas: <b>${anomaliasValor.toUpperCase()}</b></small>`,
        icon: "success",
        confirmButtonColor: "#0056b3",
    }).then(() => {
        gestionarModalInspeccionLigera(false, null, true);
        if (typeof actualizarBotonReunionConvoy === "function")
            actualizarBotonReunionConvoy();
        if (typeof actualizarBotonEvaluacion === "function")
            actualizarBotonEvaluacion();
    });
}

function gestionarModalInspeccionPesada(
    abrir,
    unidadNumero = null,
    guardadoExitoso = false,
) {
    const modal = document.getElementById("modalInspeccionPesada");

    if (abrir && unidadNumero !== null) {
        unidadEnInspeccion = unidadNumero;
        tipoVehiculoInspeccion = "pesada";
        fotosSubidas.pesada = [];
        actualizarVistaPrevia("pesada");

        const inputConductorPrincipal = document.getElementById(
            `conductor-${unidadNumero}`,
        );
        const nombreConductor = inputConductorPrincipal
            ? inputConductorPrincipal.value
            : "";
        const hiddenInput = document.getElementById(
            `vehicle-hidden-${unidadNumero}`,
        );
        const numeroEconomico = hiddenInput?.value || "";
        const nombreMostrar = numeroEconomico || "Unidad " + unidadNumero;
        let marca = "";
        let propiedad = "";

        if (numeroEconomico && detallesVehiculos[numeroEconomico]) {
            marca = detallesVehiculos[numeroEconomico].marca || "Sin Marca";
            propiedad =
                detallesVehiculos[numeroEconomico].propiedad || "Desconocido";
        }

        document.getElementById("inputNombreConductorPesada").value =
            nombreConductor;
        document.getElementById("inputNoEconomicoPesada").value = nombreMostrar;
        document.getElementById("inputMarcaPesada").value = marca;
        document.getElementById("inputVesRentadaPesada").value = propiedad;
        document.getElementById("inspeccionUnidadIndexPesada").value =
            unidadNumero;

        const savedData = datosInspeccionPesada[unidadNumero] || {};
        const allRadios = modal.querySelectorAll('input[type="radio"]');
        allRadios.forEach((r) => (r.checked = false));

        const inputKm = document.querySelector(
            '#modalInspeccionPesada input[name="kilometraje"]',
        );
        if (inputKm) inputKm.value = savedData.kilometraje || "";
        const selectDiesel = document.querySelector(
            '#modalInspeccionPesada select[name="nivel_diesel"]',
        );
        if (selectDiesel) selectDiesel.value = savedData.nivel_diesel || "";

        const allItems = [
            "doc_tarjeta",
            "doc_poliza",
            "doc_permiso_carga",
            "doc_bajos_contam",
            "doc_fisico_mec",
            "doc_carta_porte",
            "doc_tel_emergencia",
            "doc_licencia",
            "vis_botiquin",
            "vis_conos",
            "vis_extintor",
            "vis_gato",
            "vis_cables",
            "vis_linterna",
            "vis_espejos",
            "vis_refaccion",
            "vis_llantas_estado",
            "vis_llantas_calib",
            "vis_puertas",
            "vis_golpes",
            "vis_limpiaparabrisas",
            "vis_aire_acond",
            "vis_resortes",
            "vis_bolsas_aire",
            "vis_luces_gral",
            "vis_claxon",
            "vis_alarma_reversa",
            "vis_logos",
            "vis_asientos",
            "vis_cinturones",
            "vis_torreta",
            "mant_fecha_km",
            "mant_encendido",
            "mant_presion_aceite",
            "mant_temp_motor",
            "mant_presion_aire",
            "mant_fan_clutch",
            "mant_baterias",
            "mant_velocimetro",
            "mant_rpm",
            "mant_nivel_aceite",
            "mant_nivel_anticongelante",
            "mant_nivel_hidraulico",
            "mant_nivel_diesel",
            "mant_freno_motor",
            "mant_freno_parqueo",
            "mant_bandas",
            "mant_purgado",
        ];

        allItems.forEach((item) => {
            if (savedData[item]) {
                const radio = document.querySelector(
                    `#modalInspeccionPesada input[name="${item}"][value="${savedData[item]}"]`,
                );
                if (radio) radio.checked = true;
            }
        });

        const containerEvidencia = document.getElementById(
            "evidenceContainerPesada",
        );
        if (savedData.anomalias_detectadas) {
            const radioAnomalia = document.querySelector(
                `#modalInspeccionPesada input[name="anomalias_pesada"][value="${savedData.anomalias_detectadas}"]`,
            );
            if (radioAnomalia) radioAnomalia.checked = true;
            containerEvidencia.style.display =
                savedData.anomalias_detectadas === "si" ? "block" : "none";
        } else {
            containerEvidencia.style.display = "none";
        }

        const comentariosInput = document.getElementById(
            "comentariosInspeccionPesada",
        );
        if (comentariosInput)
            comentariosInput.value = savedData.comentarios || "";

        if (modal) {
            modal.classList.add("active");
            document.body.style.overflow = "hidden";
        }
    } else if (!abrir) {
        if (guardadoExitoso) {
            if (modal) modal.classList.remove("active");
            document.body.style.overflow = "auto";

            unidadEnInspeccion = null;
            tipoVehiculoInspeccion = null;
            fotosSubidas.pesada = [];
            const comentariosInput = document.getElementById(
                "comentariosInspeccionPesada",
            );
            if (comentariosInput) comentariosInput.value = "";
            const fileInput = document.getElementById(
                "evidenciaInspeccionPesada",
            );
            if (fileInput) fileInput.value = "";

            return;
        }

        const comentarios =
            document
                .getElementById("comentariosInspeccionPesada")
                ?.value.trim() || "";
        const hayFotos = fotosSubidas.pesada.length > 0;

        if (comentarios || hayFotos) {
            limpiarFotosAlCerrarModal("pesada");
        } else {
            if (modal) modal.classList.remove("active");
            document.body.style.overflow = "auto";
            unidadEnInspeccion = null;
            tipoVehiculoInspeccion = null;
        }
    }
}

function guardarInspeccionPesada() {
    const form = document.getElementById("formInspeccionPesada");

    const anomaliasRadio = form.querySelector(
        'input[name="anomalias_pesada"]:checked',
    );
    if (!anomaliasRadio) {
        Swal.fire(
            "Atención",
            "Debe indicar si detectó anomalías (Sí o No).",
            "warning",
        );
        return;
    }

    const hayAnomalias = anomaliasRadio.value === "si";
    const comentarios =
        document.getElementById("comentariosInspeccionPesada")?.value.trim() ||
        "";
    const tieneFotos = fotosSubidas.pesada.length > 0;

    if (hayAnomalias) {
        if (comentarios === "") {
            Swal.fire(
                "Comentario Requerido",
                "Debe describir las anomalías detectadas.",
                "warning",
            );
            return;
        }
        if (!tieneFotos) {
            Swal.fire({
                title: "¿Sin evidencia fotográfica?",
                text: "Reportó anomalías sin fotos. ¿Desea continuar?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Continuar",
                cancelButtonText: "Agregar fotos",
                confirmButtonColor: "#0056b3",
                cancelButtonColor: "#dc3545",
            }).then((result) => {
                if (result.isConfirmed)
                    procederGuardadoPesada(comentarios, "si");
            });
            return;
        }
        procederGuardadoPesada(comentarios, "si");
    } else {
        procederGuardadoPesada(comentarios, "no");
    }
}

async function procederGuardadoPesada(comentarios, anomaliasValor) {
    const form = document.getElementById("formInspeccionPesada");
    const unidadNumero = parseInt(
        document.getElementById("inspeccionUnidadIndexPesada")?.value,
    );

    const kilometrajeRaw =
        form.querySelector('input[name="kilometraje"]')?.value || "0";
    const kilometraje = kilometrajeRaw.replace(/,/g, "");
    const nivelDiesel = form.querySelector(
        'select[name="nivel_diesel"]',
    )?.value;

    const docItems = [
        "doc_tarjeta",
        "doc_poliza",
        "doc_permiso_carga",
        "doc_bajos_contam",
        "doc_fisico_mec",
        "doc_carta_porte",
        "doc_tel_emergencia",
        "doc_licencia",
    ];
    const visualItems = [
        "vis_botiquin",
        "vis_conos",
        "vis_extintor",
        "vis_gato",
        "vis_cables",
        "vis_linterna",
        "vis_espejos",
        "vis_refaccion",
        "vis_llantas_estado",
        "vis_llantas_calib",
        "vis_puertas",
        "vis_golpes",
        "vis_limpiaparabrisas",
        "vis_aire_acond",
        "vis_resortes",
        "vis_bolsas_aire",
        "vis_luces_gral",
        "vis_claxon",
        "vis_alarma_reversa",
        "vis_logos",
        "vis_asientos",
        "vis_cinturones",
        "vis_torreta",
    ];
    const mantItems = [
        "mant_fecha_km",
        "mant_encendido",
        "mant_presion_aceite",
        "mant_temp_motor",
        "mant_presion_aire",
        "mant_fan_clutch",
        "mant_baterias",
        "mant_velocimetro",
        "mant_rpm",
        "mant_nivel_aceite",
        "mant_nivel_anticongelante",
        "mant_nivel_hidraulico",
        "mant_nivel_diesel",
        "mant_freno_motor",
        "mant_freno_parqueo",
        "mant_bandas",
        "mant_purgado",
    ];

    const data = {};
    let tieneItemsNo = false;

    docItems.forEach((item) => {
        const input = form.querySelector(`input[name="${item}"]:checked`);
        if (input) {
            data[item] = input.value;
            if (input.value === "no") tieneItemsNo = true;
        }
    });
    visualItems.forEach((item) => {
        const input = form.querySelector(`input[name="${item}"]:checked`);
        if (input) {
            data[item] = input.value;
            if (input.value === "no") tieneItemsNo = true;
        }
    });
    mantItems.forEach((item) => {
        const input = form.querySelector(`input[name="${item}"]:checked`);
        if (input) {
            data[item] = input.value;
            if (input.value === "no") tieneItemsNo = true;
        }
    });

    data.kilometraje = kilometraje;
    data.nivel_diesel = nivelDiesel;
    data.anomalias_detectadas = anomaliasValor;
    data.comentarios = comentarios;

    // AQUI ESTÁ EL CAMBIO PARA LAS FOTOS PESADAS (Convertir a Base64)
    const arrayFotosBase64 = [];
    for (let i = 0; i < fotosSubidas.pesada.length; i++) {
        let foto = fotosSubidas.pesada[i];
        let base64String = await convertirArchivoABase64(foto.file);
        arrayFotosBase64.push({
            nombre: foto.name,
            tamaño: foto.size,
            tipo: foto.type,
            base64: base64String, // <--- Enviamos el código 64 al PHP
        });
    }

    // Lo guardamos en 'fotos' para que haga match con el backend PHP
    data.fotos = arrayFotosBase64;

    datosInspeccionPesada[unidadNumero] = data;

    const btnInspeccion = document.getElementById(
        `btn-inspeccion-${unidadNumero}`,
    );
    if (btnInspeccion) {
        btnInspeccion.dataset.realizada = "true";
        if (tieneItemsNo || anomaliasValor === "si") {
            btnInspeccion.classList.remove(
                "btn-inspeccion",
                "btn-inspeccion-realizada-ok",
            );
            btnInspeccion.classList.add("btn-inspeccion-realizada-warning");
            btnInspeccion.innerHTML =
                '<i class="fas fa-exclamation-triangle"></i> Realizado';
            btnInspeccion.dataset.tieneNos = "true";
            btnInspeccion.title = "Inspección realizada con observaciones";
        } else {
            btnInspeccion.classList.remove(
                "btn-inspeccion",
                "btn-inspeccion-realizada-warning",
            );
            btnInspeccion.classList.add("btn-inspeccion-realizada-ok");
            btnInspeccion.innerHTML =
                '<i class="fas fa-check-circle"></i> Realizado';
            btnInspeccion.dataset.tieneNos = "false";
            btnInspeccion.title = "Inspección realizada sin observaciones";
        }
    }

    Swal.fire({
        title: "Inspección Realizada",
        html: `Unidad ${unidadNumero} inspeccionada.<br><small>Anomalías reportadas: <b>${anomaliasValor.toUpperCase()}</b></small>`,
        icon: "success",
        confirmButtonColor: "#0056b3",
    }).then(() => {
        gestionarModalInspeccionPesada(false, null, true);
        if (typeof actualizarBotonReunionConvoy === "function")
            actualizarBotonReunionConvoy();
        if (typeof actualizarBotonEvaluacion === "function")
            actualizarBotonEvaluacion();
    });
}

// ====================================================================
// GESTIÓN DE FOTOS Y CÁMARA
// ====================================================================
function inicializarInputsFotos() {
    const tipos = ["ligera", "pesada"];
    tipos.forEach((tipo) => {
        const input = document.getElementById(
            `evidenciaInspeccion${tipo.charAt(0).toUpperCase() + tipo.slice(1)}`,
        );
        const dropZone = document.getElementById(
            `dropZone${tipo.charAt(0).toUpperCase() + tipo.slice(1)}`,
        );

        if (input && dropZone) {
            input.addEventListener("change", (e) =>
                manejarSeleccionArchivos(e, tipo),
            );

            dropZone.addEventListener("dragover", (e) => {
                e.preventDefault();
                dropZone.style.borderColor = "#0056b3";
                dropZone.style.backgroundColor = "#f0f7ff";
            });

            dropZone.addEventListener("dragleave", (e) => {
                e.preventDefault();
                dropZone.style.borderColor = "#ccc";
                dropZone.style.backgroundColor = "#ffffff";
            });

            dropZone.addEventListener("drop", (e) => {
                e.preventDefault();
                dropZone.style.borderColor = "#ccc";
                dropZone.style.backgroundColor = "#ffffff";
                if (e.dataTransfer.files.length > 0) {
                    input.files = e.dataTransfer.files;
                    manejarSeleccionArchivos({ target: input }, tipo);
                }
            });
        }
    });
}

function manejarSeleccionArchivos(event, tipo) {
    const input = event.target;
    const maxFiles = parseInt(input.dataset.maxFiles) || 6;
    let files = Array.from(input.files);

    if (files.length > maxFiles) {
        Swal.fire({
            title: "Demasiadas fotos",
            text: `Solo puedes subir un máximo de ${maxFiles} fotos. Se seleccionarán las primeras ${maxFiles}.`,
            icon: "warning",
            confirmButtonColor: "#0056b3",
        });
        files = files.slice(0, maxFiles);
    }

    const tiposPermitidos = [
        "image/jpeg",
        "image/png",
        "image/jpg",
        "application/pdf",
    ];
    const archivosValidos = files.filter(
        (file) =>
            tiposPermitidos.includes(file.type) ||
            file.name.toLowerCase().endsWith(".pdf"),
    );

    if (archivosValidos.length === 0) {
        Swal.fire({
            title: "Formato no válido",
            text: "Solo se permiten archivos JPG, PNG o PDF.",
            icon: "error",
            confirmButtonColor: "#0056b3",
        });
        input.value = "";
        return;
    }

    archivosValidos.forEach((file) => agregarFotoALista(file, tipo));
    actualizarVistaPrevia(tipo);
}

function agregarFotoALista(file, tipo) {
    if (
        fotosSubidas[tipo].some(
            (foto) => foto.name === file.name && foto.size === file.size,
        )
    )
        return;

    if (fotosSubidas[tipo].length >= 6) {
        Swal.fire({
            title: "Límite alcanzado",
            text: "Ya tienes 6 fotos. Elimina alguna antes de agregar más.",
            icon: "warning",
            confirmButtonColor: "#0056b3",
        });
        return;
    }

    fotosSubidas[tipo].push({
        id: Date.now() + Math.random().toString(36).substr(2, 9),
        file: file,
        name: file.name,
        size: file.size,
        type: file.type,
        url: URL.createObjectURL(file),
    });
}

function eliminarFoto(id, tipo) {
    fotosSubidas[tipo] = fotosSubidas[tipo].filter((foto) => foto.id !== id);
    actualizarVistaPrevia(tipo);
}

function actualizarVistaPrevia(tipo) {
    const tipoCapitalizado = tipo.charAt(0).toUpperCase() + tipo.slice(1);
    const container = document.getElementById(
        `previewContainer${tipoCapitalizado}`,
    );
    const placeholder = document.getElementById(
        `placeholder${tipoCapitalizado}`,
    );
    const grid = document.getElementById(`previewGrid${tipoCapitalizado}`);
    const countSpan = document.getElementById(
        `previewCount${tipoCapitalizado}`,
    );
    const input = document.getElementById(
        `evidenciaInspeccion${tipoCapitalizado}`,
    );

    if (!grid || !countSpan) return;

    if (fotosSubidas[tipo].length > 0) {
        container.style.display = "block";
        if (placeholder) placeholder.style.display = "none";
    } else {
        container.style.display = "none";
        if (placeholder) placeholder.style.display = "flex";
    }

    countSpan.textContent = `${fotosSubidas[tipo].length} ${
        fotosSubidas[tipo].length === 1 ? "foto" : "fotos"
    } seleccionada${fotosSubidas[tipo].length === 1 ? "" : "s"}`;
    grid.innerHTML = "";

    fotosSubidas[tipo].forEach((foto) => {
        const item = document.createElement("div");
        item.className = "preview-item";
        item.dataset.id = foto.id;

        if (
            foto.type === "application/pdf" ||
            foto.name.toLowerCase().endsWith(".pdf")
        ) {
            item.innerHTML = `
                <div class="preview-file-icon">
                    <i class="fas fa-file-pdf"></i>
                    <span>${
                        foto.name.length > 15
                            ? foto.name.substring(0, 12) + "..."
                            : foto.name
                    }</span>
                </div>
                <div class="preview-overlay">
                    <div class="preview-actions">
                        <button type="button" class="btn-preview-action btn-preview-view" onclick="verArchivo('${tipo}', '${
                            foto.id
                        }')" title="Ver PDF"><i class="fas fa-eye"></i></button>
                        <button type="button" class="btn-preview-action btn-preview-delete" onclick="eliminarFoto('${
                            foto.id
                        }', '${tipo}')" title="Eliminar"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            `;
        } else {
            item.innerHTML = `
                <img src="${foto.url}" alt="${foto.name}" class="preview-image">
                <div class="preview-overlay">
                    <div class="preview-actions">
                        <button type="button" class="btn-preview-action btn-preview-view" onclick="verArchivo('${tipo}', '${foto.id}')" title="Ver imagen"><i class="fas fa-eye"></i></button>
                        <button type="button" class="btn-preview-action btn-preview-delete" onclick="eliminarFoto('${foto.id}', '${tipo}')" title="Eliminar"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            `;
        }
        grid.appendChild(item);
    });

    const dataTransfer = new DataTransfer();
    fotosSubidas[tipo].forEach((foto) => dataTransfer.items.add(foto.file));
    input.files = dataTransfer.files;
}

function limpiarFotos(tipo) {
    Swal.fire({
        title: "¿Eliminar todas las fotos?",
        text: "Esta acción no se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#0056b3",
    }).then((result) => {
        if (result.isConfirmed) {
            fotosSubidas[tipo] = [];
            const input = document.getElementById(
                `evidenciaInspeccion${
                    tipo.charAt(0).toUpperCase() + tipo.slice(1)
                }`,
            );
            if (input) input.value = "";
            actualizarVistaPrevia(tipo);
        }
    });
}

function verArchivo(tipo, id) {
    const foto = fotosSubidas[tipo].find((f) => f.id === id);
    if (!foto) return;

    if (
        foto.type === "application/pdf" ||
        foto.name.toLowerCase().endsWith(".pdf")
    ) {
        window.open(foto.url, "_blank");
    } else {
        Swal.fire({
            imageUrl: foto.url,
            imageAlt: foto.name,
            showConfirmButton: false,
            showCloseButton: true,
            width: "80%",
            padding: "0",
        });
    }
}

async function abrirCamara(tipo) {
    tipoActualCamara = tipo;
    const modal = document.getElementById("modalCamara");
    const video = document.getElementById("videoCamara");

    if (!modal || !video) return;

    modal.classList.add("active");
    document.body.style.overflow = "hidden";

    video.setAttribute("autoplay", "");
    video.setAttribute("muted", "");
    video.setAttribute("playsinline", "");

    await iniciarStream();
}

async function iniciarStream() {
    const video = document.getElementById("videoCamara");

    if (streamCamara) {
        streamCamara.getTracks().forEach((track) => track.stop());
    }

    const facingMode = usarCamaraFrontal ? "user" : "environment";

    const constraints = {
        audio: false,
        video: {
            facingMode: { ideal: facingMode },
            width: { ideal: 1280 },
            height: { ideal: 720 },
        },
    };

    try {
        const stream = await navigator.mediaDevices.getUserMedia(constraints);
        streamCamara = stream;
        video.srcObject = stream;

        video.onloadedmetadata = () => {
            video.play();
        };
    } catch (error) {
        console.error("Error cámara:", error);

        let titulo = "Error";
        let mensaje = "No se pudo acceder a la cámara.";

        if (
            error.name === "NotAllowedError" ||
            error.name === "PermissionDeniedError"
        ) {
            titulo = "Permiso Denegado";
            mensaje =
                "Por favor, permite el acceso a la cámara en la configuración de tu navegador.";
        } else if (error.name === "NotFoundError") {
            mensaje = "No se encontró ninguna cámara en este dispositivo.";
        }

        Swal.fire({
            title: titulo,
            text: mensaje,
            icon: "error",
            confirmButtonColor: "#0056b3",
        }).then(() => cerrarCamara());
    }
}

function alternarCamara() {
    usarCamaraFrontal = !usarCamaraFrontal;
    iniciarStream();
}

function cerrarCamara() {
    const modal = document.getElementById("modalCamara");
    const video = document.getElementById("videoCamara");

    if (modal) modal.classList.remove("active");
    document.body.style.overflow = "auto";

    if (streamCamara) {
        streamCamara.getTracks().forEach((track) => track.stop());
        streamCamara = null;
    }

    if (video) {
        video.srcObject = null;
    }

    tipoActualCamara = null;
}

function capturarFoto() {
    const video = document.getElementById("videoCamara");
    const canvas = document.getElementById("canvasCamara");

    if (!video || !canvas || !tipoActualCamara) return;

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    const context = canvas.getContext("2d");

    if (usarCamaraFrontal) {
        context.translate(canvas.width, 0);
        context.scale(-1, 1);
    }

    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    canvas.toBlob(
        (blob) => {
            if (!blob) return;

            const file = new File(
                [blob],
                `foto_${tipoActualCamara}_${Date.now()}.jpg`,
                {
                    type: "image/jpeg",
                    lastModified: Date.now(),
                },
            );

            agregarFotoALista(file, tipoActualCamara);
            actualizarVistaPrevia(tipoActualCamara);

            Swal.fire({
                icon: "success",
                title: "Foto capturada",
                showConfirmButton: false,
                timer: 800,
                position: "center",
                background: "rgba(0,0,0,0.8)",
                color: "#fff",
            });
        },
        "image/jpeg",
        0.9,
    );
}

function limpiarFotosAlCerrarModal(tipo) {
    Swal.fire({
        title: "¿Cerrar sin guardar?",
        text: "Tienes fotos o comentarios sin guardar. ¿Deseas descartarlos y salir?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, descartar y salir",
        cancelButtonText: "No, seguir editando",
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#0056b3",
    }).then((result) => {
        if (result.isConfirmed) {
            fotosSubidas[tipo] = [];

            const tipoCap = tipo.charAt(0).toUpperCase() + tipo.slice(1);

            const comentariosInput = document.getElementById(
                `comentariosInspeccion${tipoCap}`,
            );
            if (comentariosInput) comentariosInput.value = "";

            const fileInput = document.getElementById(
                `evidenciaInspeccion${tipoCap}`,
            );
            if (fileInput) fileInput.value = "";

            actualizarVistaPrevia(tipo);

            if (tipo === "ligera") {
                gestionarModalInspeccionLigera(false, null, true);
            } else {
                gestionarModalInspeccionPesada(false, null, true);
            }
        }
    });
}

// ====================================================================
// EVALUACIÓN DE RIESGO
// ====================================================================
function actualizarBotonEvaluacion() {
    const btn = document.getElementById("btnEvaluacionRiesgo");
    if (btn) btn.disabled = contadorUnidades === 0;
    actualizarBotonEnviarSolicitud();
}

function calcularAutomarcadoEvaluacion() {
    const form = document.getElementById("formEvaluacionRiesgo");
    if (!form) return;

    let maxTotalHoras = 0;
    let hayManejoVencido = false;
    let hayManejoNoTiene = false;
    let hayPasajeros = false;
    let minHorasDormidas = 99;

    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);

    for (let i = 1; i <= contadorUnidades; i++) {
        const inputConductor = document.getElementById(`conductor-${i}`);
        const nombreConductor = inputConductor ? inputConductor.value : "";
        const tipoVehiculoDiv = document.getElementById(`tipo-vehiculo-${i}`);
        const esPesada =
            tipoVehiculoDiv?.textContent.includes("Pesada") ||
            tipoVehiculoDiv?.classList.contains("tipo-pesada");

        if (nombreConductor && datosConductoresGlobales[nombreConductor]) {
            const data = datosConductoresGlobales[nombreConductor];
            const fechaCursoRaw = esPesada
                ? data.cursoPesadoVigencia
                : data.manDefVigencia;
            if (!fechaCursoRaw) hayManejoNoTiene = true;
            else {
                const fechaCurso = new Date(fechaCursoRaw + "T00:00:00");
                if (fechaCurso < hoy) hayManejoVencido = true;
            }
        } else {
            if (nombreConductor) hayManejoNoTiene = true;
        }

        const inputTotalHoras = document.getElementById(
            `total-hrs-finalizar-${i}`,
        );
        if (inputTotalHoras && inputTotalHoras.value) {
            const partes = inputTotalHoras.value.split(":");
            const horas = parseInt(partes[0]) || 0;
            const minutos = parseInt(partes[1]) || 0;
            const totalDecimal = horas + minutos / 60;
            if (totalDecimal > maxTotalHoras) maxTotalHoras = totalDecimal;
        }

        const containerPasajeros = document.getElementById(
            `pasajeros-unidad-${i}`,
        );
        if (containerPasajeros) {
            const inputsPasajeros = containerPasajeros.querySelectorAll(
                'input[name*="[nombre]"]',
            );
            let pasajerosEnUnidad = 0;
            inputsPasajeros.forEach((inp) => {
                if (inp.value.trim() !== "") pasajerosEnUnidad++;
            });
            if (pasajerosEnUnidad > 0) hayPasajeros = true;
        }

        const inputDormidas = document.getElementById(
            `total-hrs-dormidas-${i}`,
        );
        if (inputDormidas && inputDormidas.value) {
            const partesD = inputDormidas.value.split(":");
            const horasD = parseInt(partesD[0]) || 0;
            const minsD = parseInt(partesD[1]) || 0;
            const dormidasDecimal = horasD + minsD / 60;
            if (dormidasDecimal < minHorasDormidas)
                minHorasDormidas = dormidasDecimal;
        }
    }

    let valorManejo = "5";
    if (hayManejoNoTiene) valorManejo = "15";
    else if (hayManejoVencido) valorManejo = "10";
    seleccionarYBloquearRadio(form, "ev_manejo", valorManejo);

    let valorHoras = "5";
    if (maxTotalHoras <= 8) valorHoras = "5";
    else if (maxTotalHoras <= 12) valorHoras = "10";
    else valorHoras = "15";
    seleccionarYBloquearRadio(form, "ev_horas", valorHoras);

    const esConvoy = contadorUnidades > 1;
    let indiceRadioVehiculo = 0;
    if (!esConvoy) indiceRadioVehiculo = hayPasajeros ? 0 : 1;
    else indiceRadioVehiculo = hayPasajeros ? 2 : 3;
    marcarRadioPorIndiceYBloquear(form, "ev_vehiculos", indiceRadioVehiculo);

    const horaInicioStr =
        document.getElementById("horaInicioViaje")?.value || "00:00";
    const horaFinStr =
        document.getElementById("horaFinViaje")?.value || "00:00";

    const minInicio = stringHoraAMinutos(horaInicioStr);
    const minFin = stringHoraAMinutos(horaFinStr);

    const factorNocturno = minFin > 1260 || minInicio >= 1260;
    const factorPocoSueno = minHorasDormidas <= 6;
    const factorMedianoche = minFin < minInicio;
    const factor16Horas = maxTotalHoras > 16;

    manejarCheckboxFactor(form, "ev_horario_nocturno", factorNocturno);
    manejarCheckboxFactor(form, "ev_horas_dormidas", factorPocoSueno);
    manejarCheckboxFactor(form, "ev_rebase_medianoche", factorMedianoche);
    manejarCheckboxFactor(form, "ev_16hrs_despierto", factor16Horas);
}

function manejarCheckboxFactor(form, name, estado) {
    const check = form.querySelector(`input[name="${name}"]`);
    if (check) {
        check.checked = estado;
        check.disabled = true;
        check.parentElement.style.opacity = estado ? "1" : "0.5";
        if (estado) {
            check.parentElement.style.color = "#dc3545";
            check.parentElement.style.fontWeight = "bold";
        } else {
            check.parentElement.style.color = "";
            check.parentElement.style.fontWeight = "normal";
        }
    }
}

function seleccionarYBloquearRadio(form, groupName, value) {
    const radios = form.querySelectorAll(`input[name="${groupName}"]`);
    radios.forEach((radio) => {
        if (radio.value === value) radio.checked = true;
        radio.disabled = true;
        radio.parentElement.style.opacity = radio.checked ? "1" : "0.6";
        radio.parentElement.style.pointerEvents = "none";
    });
}

function marcarRadioPorIndiceYBloquear(form, groupName, index) {
    const radios = form.querySelectorAll(`input[name="${groupName}"]`);
    radios.forEach((radio, i) => {
        if (i === index) radio.checked = true;
        else radio.checked = false;
        radio.disabled = true;
        radio.parentElement.style.opacity = i === index ? "1" : "0.6";
        radio.parentElement.style.pointerEvents = "none";
    });
}

function gestionarModalEvaluacion(abrir) {
    const modal = document.getElementById("modalEvaluacion");
    if (!modal) return;

    if (abrir) {
        calcularAutomarcadoEvaluacion();
        modal.classList.add("active");
        document.body.style.overflow = "hidden";
    } else {
        modal.classList.remove("active");
        document.body.style.overflow = "auto";
    }
}

async function cargarAutorizadores(nivelRiesgo) {
    const seccion = document.getElementById("seccionDestinatario");
    const grid = document.getElementById("gridAutorizadores");

    seccion.style.display = "block";
    grid.innerHTML =
        '<p style="padding: 15px; color: #6c757d; font-style: italic;">Buscando personal autorizado...</p>';

    try {
        const response = await fetch(
            `/qhse/gerenciamiento/autorizadores/${nivelRiesgo}`,
        );
        const result = await response.json();

        if (!result.success || result.data.length === 0) {
            grid.innerHTML = `
                <div style="padding: 15px; color: #dc3545; background: #f8d7da; border-radius: 8px; width: 100%;">
                    <i class="fas fa-exclamation-triangle"></i>
                    No hay personal activo configurado para autorizar un riesgo <b>${nivelRiesgo.toUpperCase()}</b>.
                    Por favor, contacte a sistemas.
                </div>`;
            return;
        }

        grid.innerHTML = "";
        result.data.forEach((user) => {
            const htmlTarjeta = `
                <label class="destinatario-card">
                    <input type="radio" name="autorizador_id" value="${user.id}" required>
                    <div class="card-content">
                        <div class="destinatario-avatar">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="destinatario-info">
                            <h4>${user.nombre}</h4>
                            <span>${user.puesto}</span>
                        </div>
                        <div class="check-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </label>
            `;
            grid.insertAdjacentHTML("beforeend", htmlTarjeta);
        });
    } catch (error) {
        console.error("Error al obtener autorizadores:", error);
        grid.innerHTML =
            '<p style="color: red; padding: 15px;">Error de red al cargar autorizadores.</p>';
    }
}

function guardarEvaluacion() {
    const form = document.getElementById("formEvaluacionRiesgo");
    if (!form) return;

    const categorias = [
        "ev_manejo",
        "ev_horas",
        "ev_vehiculos",
        "ev_comunicacion",
        "ev_clima",
        "ev_iluminacion",
        "ev_carretera",
        "ev_otras",
        "ev_animales",
        "ev_seguridad",
    ];

    let completo = true;
    let totalPuntos = 0;

    for (let cat of categorias) {
        const seleccionado = form.querySelector(`input[name="${cat}"]:checked`);
        if (!seleccionado) {
            completo = false;
            break;
        }
        totalPuntos += parseInt(seleccionado.value);
    }

    const radiactivoSeleccionado = form.querySelector(
        'input[name="ev_radiactivo"]:checked',
    );
    if (!radiactivoSeleccionado) completo = false;

    if (!completo) {
        Swal.fire({
            title: "Evaluación Incompleta",
            text: "Por favor seleccione una opción para cada categoría.",
            icon: "warning",
            confirmButtonColor: "#0056b3",
        });
        return;
    }

    let nivelRiesgoTexto = "";
    let cssClassBtn = "";
    let iconoSwal = "";
    let btnConfirmColor = "";
    let iconoBadge = "";
    let mensajeAdicional = "";
    let esRiesgoForzado = false;
    let nivelInterno = "";

    // 1. Identificamos qué opción se seleccionó (0, 1, 2 o 3)
    const radiosRadiactivos = Array.from(
        form.querySelectorAll('input[name="ev_radiactivo"]'),
    );
    const indexRadiactivo = radiosRadiactivos.indexOf(radiactivoSeleccionado);

    // 2. Separamos la lógica
    const esEquipoPesado = indexRadiactivo === 1; // Opción 2: Maquinaria (Riesgo Medio)
    const esMaterialPeligroso = indexRadiactivo === 2 || indexRadiactivo === 3; // Opciones 3 y 4: Rad/Exp (Riesgo Alto)

    const factorNocturno = form.querySelector(
        'input[name="ev_horario_nocturno"]',
    ).checked;
    const factorPocoSueno = form.querySelector(
        'input[name="ev_horas_dormidas"]',
    ).checked;
    const factorMedianoche = form.querySelector(
        'input[name="ev_rebase_medianoche"]',
    ).checked;
    const factor16Horas = form.querySelector(
        'input[name="ev_16hrs_despierto"]',
    ).checked;

    // --- APLICACIÓN DE REGLAS DE RIESGO ---

    if (factorMedianoche || factor16Horas) {
        // REGLA 1: Factores extremos fuerzan a Muy Alto
        nivelInterno = "muy_alto";
        nivelRiesgoTexto = "Riesgo Muy Alto";
        cssClassBtn = "btn-riesgo-muy-alto";
        iconoSwal = "error";
        btnConfirmColor = "#dc3545";
        iconoBadge = '<i class="fas fa-ban"></i>';
        mensajeAdicional =
            "Se recomienda mucha precaución. Requiere autorización de gerencia. (Detectado por Factores Críticos)";
        esRiesgoForzado = true;
    } else if (esMaterialPeligroso || factorNocturno || factorPocoSueno) {
        // REGLA 2: Material Rad/Exp o fatiga fuerzan a Alto
        nivelInterno = "alto";
        nivelRiesgoTexto = "Riesgo Alto";
        cssClassBtn = "btn-riesgo-alto";
        iconoSwal = "warning";
        btnConfirmColor = "#fd7e14";
        iconoBadge = '<i class="fas fa-exclamation-triangle"></i>';
        mensajeAdicional =
            "Se requiere aprobación especial y medidas de seguridad. (Detectado por Material Peligroso o Factores de Fatiga)";
        esRiesgoForzado = true;
    } else if (esEquipoPesado && totalPuntos <= 55) {
        // REGLA 3: Si lleva Maquinaria y su puntaje era Bajo (<= 55), lo forzamos a Medio.
        // Si su puntaje ya era > 55, lo dejamos pasar a la lógica normal porque ya será Medio o mayor por sí solo.
        nivelInterno = "medio";
        nivelRiesgoTexto = "Riesgo Medio";
        cssClassBtn = "btn-riesgo-medio";
        iconoSwal = "warning";
        btnConfirmColor = "#ffc107";
        iconoBadge = '<i class="fas fa-exclamation-circle"></i>';
        mensajeAdicional =
            "Se requieren precauciones adicionales. (Detectado por Transporte de Equipo/Maquinaria)";
        esRiesgoForzado = true;
    } else {
        // REGLA 4: Lógica normal de suma de puntos
        if (totalPuntos <= 55) {
            nivelInterno = "bajo";
            nivelRiesgoTexto = "Riesgo Bajo";
            cssClassBtn = "btn-riesgo-bajo";
            iconoSwal = "success";
            btnConfirmColor = "#28a745";
            iconoBadge = '<i class="fas fa-check-circle"></i>';
            mensajeAdicional = "El viaje puede proceder normalmente.";
        } else if (totalPuntos <= 105) {
            nivelInterno = "medio";
            nivelRiesgoTexto = "Riesgo Medio";
            cssClassBtn = "btn-riesgo-medio";
            iconoSwal = "warning";
            btnConfirmColor = "#ffc107";
            iconoBadge = '<i class="fas fa-exclamation-circle"></i>';
            mensajeAdicional = "Se requieren precauciones adicionales.";
        } else if (totalPuntos <= 145) {
            nivelInterno = "alto";
            nivelRiesgoTexto = "Riesgo Alto";
            cssClassBtn = "btn-riesgo-alto";
            iconoSwal = "warning";
            btnConfirmColor = "#fd7e14";
            iconoBadge = '<i class="fas fa-exclamation-triangle"></i>';
            mensajeAdicional =
                "Se requiere aprobación especial y medidas de seguridad.";
        } else {
            nivelInterno = "muy_alto";
            nivelRiesgoTexto = "Riesgo Muy Alto";
            cssClassBtn = "btn-riesgo-muy-alto";
            iconoSwal = "error";
            btnConfirmColor = "#dc3545";
            iconoBadge = '<i class="fas fa-ban"></i>';
            mensajeAdicional =
                "Se recomienda mucha precaución. Requiere autorización de gerencia.";
        }
    }

    puntajeRiesgoTotal = totalPuntos;
    evaluacionRiesgoGuardada = true;

    cargarAutorizadores(nivelInterno);

    const btn = document.getElementById("btnEvaluacionRiesgo");
    if (btn) {
        btn.innerHTML = `<i class="fas fa-check-circle"></i> Evaluación: ${nivelRiesgoTexto} <br><small>(Clic para editar)</small>`;
        btn.classList.remove(
            "btn-riesgo-bajo",
            "btn-riesgo-medio",
            "btn-riesgo-alto",
            "btn-riesgo-muy-alto",
        );
        btn.classList.add("evaluacion-completada", cssClassBtn);
        btn.style = "";
    }

    gestionarModalEvaluacion(false);
    actualizarBotonEnviarSolicitud();

    Swal.fire({
        title: "Evaluación Guardada",
        html: `
            <div style="display: flex; flex-direction: column; align-items: center;">
                <div class="resultado-titulo">Nivel de Riesgo:</div>
                <div class="riesgo-badge ${cssClassBtn.replace("btn-", "status-")}">
                    ${iconoBadge} ${nivelRiesgoTexto}
                </div>
                <div class="resultado-mensaje">${mensajeAdicional}</div>
                <div class="resultado-puntaje">
                    ${
                        esRiesgoForzado
                            ? `Puntaje base: ${totalPuntos} <br><span style='color:red; font-size:0.9em'>(Nivel elevado por Factores de Riesgo Críticos)</span>`
                            : `Puntaje total: <strong>${totalPuntos}</strong>`
                    }
                </div>
            </div>
        `,
        icon: iconoSwal,
        confirmButtonColor: btnConfirmColor,
        confirmButtonText: "Entendido",
        width: 500,
    });
}

// ====================================================================
// REUNIÓN PRE-CONVOY
// ====================================================================
function actualizarBotonReunionConvoy() {
    const btn = document.getElementById("btnReunionPreConvoy");
    if (!btn) return;

    if (contadorUnidades < 2) {
        btn.disabled = true;
        btn.classList.remove("btn-submit", "btn-secondary-convoy-completed");
        btn.classList.add("btn-secondary-convoy");
        btn.title = "Se requiere un mínimo de 2 unidades para un Convoy";
        btn.innerHTML = '<i class="fas fa-handshake"></i> Reunión Pre-convoy';
        return;
    }

    let conductoresCompletos = true;
    for (let i = 1; i <= contadorUnidades; i++) {
        const inputConductor = document.getElementById(`conductor-${i}`);
        if (!inputConductor || inputConductor.value.trim() === "") {
            conductoresCompletos = false;
            break;
        }
    }

    if (conductoresCompletos) {
        btn.disabled = false;
        btn.title = "Realizar o ver la reunión de convoy.";

        if (reunionPreConvoyGuardada) {
            btn.classList.add("btn-secondary-convoy-completed");
            btn.classList.remove("btn-secondary-convoy", "btn-submit");
            btn.innerHTML =
                '<i class="fas fa-check-circle"></i> Reunión Confirmada';
        } else {
            btn.classList.add("btn-secondary-convoy");
            btn.classList.remove(
                "btn-submit",
                "btn-secondary-convoy-completed",
            );
            btn.innerHTML =
                '<i class="fas fa-handshake"></i> Reunión Pre-convoy';
        }
    } else {
        btn.disabled = true;
        btn.classList.remove("btn-submit", "btn-secondary-convoy-completed");
        btn.classList.add("btn-secondary-convoy");
        btn.innerHTML = '<i class="fas fa-handshake"></i> Reunión Pre-convoy';
        btn.title =
            "Debe asignar el Nombre del Conductor en todas las unidades para activar la Reunión.";
    }

    actualizarBotonEnviarSolicitud();
}

function gestionarModalPreConvoy(abrir) {
    const modal = document.getElementById("modalPreConvoy");
    const liderSelect = document.getElementById("liderConvoy");

    if (abrir) {
        const conductoresDisponibles = obtenerConductoresUnidades();

        if (liderSelect) {
            liderSelect.innerHTML =
                '<option value="">Seleccione un conductor como Líder</option>';

            conductoresDisponibles.forEach((c) => {
                const option = document.createElement("option");
                // EL VALUE AHORA ES EL ID (O EL NOMBRE SI EL ID FALLA COMO RESPALDO)
                option.value = c.id || c.nombre;
                option.textContent = c.nombre;

                // Mantenemos la selección si ya había uno guardado
                if (datosReunionConvoy.lider_convoy_id == c.id) {
                    option.selected = true;
                }
                liderSelect.appendChild(option);
            });
        }

        const checklistItems = [
            "puntos_parada",
            "ruptura_convoy",
            "doc_vigente",
            "prevencion_acc",
            "contactos_emerg",
            "compromiso_lider",
        ];

        checklistItems.forEach((item) => {
            const savedValue = datosReunionConvoy[item];
            if (savedValue) {
                const radio = document.querySelector(
                    `input[name="checklist_${item}"][value="${savedValue}"]`,
                );
                if (radio) radio.checked = true;
            }
        });

        if (modal) modal.classList.add("active");
        document.body.style.overflow = "hidden";
    } else {
        if (modal) modal.classList.remove("active");
        document.body.style.overflow = "auto";
    }
}
function obtenerConductoresUnidades() {
    const conductoresList = [];
    for (let i = 1; i <= contadorUnidades; i++) {
        const inputConductor = document.getElementById(`conductor-${i}`);
        if (inputConductor && inputConductor.value.trim() !== "") {
            conductoresList.push({
                id: inputConductor.dataset.conductorId || null,
                nombre: inputConductor.value.trim(),
            });
        }
    }
    return conductoresList;
}

function obtenerConductoresUnidades() {
    const conductoresList = [];
    for (let i = 1; i <= contadorUnidades; i++) {
        const inputConductor = document.getElementById(`conductor-${i}`);
        if (inputConductor && inputConductor.value.trim() !== "") {
            conductoresList.push({
                id: inputConductor.dataset.conductorId || null,
                nombre: inputConductor.value.trim(),
            });
        }
    }
    return conductoresList;
}

function gestionarModalPreConvoy(abrir) {
    const modal = document.getElementById("modalPreConvoy");
    const liderSelect = document.getElementById("liderConvoy");

    if (abrir) {
        const conductoresDisponibles = obtenerConductoresUnidades();

        if (liderSelect) {
            liderSelect.innerHTML =
                '<option value="">Seleccione un conductor como Líder</option>';

            conductoresDisponibles.forEach((c) => {
                const option = document.createElement("option");
                // EL VALUE AHORA ES EL ID (O EL NOMBRE SI EL ID FALLA COMO RESPALDO)
                option.value = c.id || c.nombre;
                option.textContent = c.nombre;

                // Mantenemos la selección si ya había uno guardado
                if (datosReunionConvoy.lider_convoy_id == c.id) {
                    option.selected = true;
                }
                liderSelect.appendChild(option);
            });
        }

        const checklistItems = [
            "puntos_parada",
            "ruptura_convoy",
            "doc_vigente",
            "prevencion_acc",
            "contactos_emerg",
            "compromiso_lider",
        ];

        checklistItems.forEach((item) => {
            const savedValue = datosReunionConvoy[item];
            if (savedValue) {
                const radio = document.querySelector(
                    `input[name="checklist_${item}"][value="${savedValue}"]`,
                );
                if (radio) radio.checked = true;
            }
        });

        if (modal) modal.classList.add("active");
        document.body.style.overflow = "hidden";
    } else {
        if (modal) modal.classList.remove("active");
        document.body.style.overflow = "auto";
    }
}

function guardarPreConvoy() {
    const form = document.getElementById("formPreConvoy");
    const liderSelect = document.getElementById("liderConvoy");

    if (liderSelect?.value.trim() === "") {
        Swal.fire({
            title: "Líder No Seleccionado",
            text: "Debe seleccionar un conductor como Líder de Convoy.",
            icon: "warning",
            confirmButtonColor: "#0056b3",
        });
        return;
    }

    const checklistItems = [
        "puntos_parada",
        "ruptura_convoy",
        "doc_vigente",
        "prevencion_acc",
        "contactos_emerg",
        "compromiso_lider",
    ];

    const nombresChecklist = {
        puntos_parada: "Puntos de parada acordados",
        ruptura_convoy: "Procedimiento por ruptura de convoy",
        doc_vigente: "Documentación vigente",
        prevencion_acc: "Prevención de accidentes",
        contactos_emerg: "Contactos de emergencia",
        compromiso_lider: "Compromiso del líder",
    };

    let checklistCompleto = true;

    // AQUÍ ESTÁ EL CAMBIO: Guardamos el ID del líder, y también el nombre para validaciones visuales
    const data = {
        lider_convoy_id: liderSelect.value,
        lider_convoy_nombre:
            liderSelect.options[liderSelect.selectedIndex].text,
    };

    checklistItems.forEach((item) => {
        const checked = form.querySelector(
            `input[name="checklist_${item}"]:checked`,
        );
        if (!checked) checklistCompleto = false;
        else data[item] = checked.value;
    });

    if (!checklistCompleto) {
        Swal.fire({
            title: "Checklist Incompleto",
            text: "Debe responder sí o no a todos los elementos del checklist de seguridad.",
            icon: "warning",
            confirmButtonColor: "#0056b3",
        });
        return;
    }

    let noAprobado = false;
    let noItemsHTML = "";

    for (const key in data) {
        // Excluimos las llaves del líder para que no interfieran con la validación de los "no"
        if (
            key !== "lider_convoy_id" &&
            key !== "lider_convoy_nombre" &&
            data[key] === "no"
        ) {
            noAprobado = true;
            noItemsHTML += `<li>${nombresChecklist[key] || key}</li>`;
        }
    }

    if (noAprobado) {
        Swal.fire({
            title: "Puntos sin confirmar",
            html: `Se marcaron con <strong>NO</strong> los siguientes puntos:<br><br>
                   <ul style="text-align: left; display: inline-block;">${noItemsHTML}</ul><br><br>
                   ¿Estás de acuerdo con esto? Recomendamos verificar nuevamente los puntos.`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, estoy de acuerdo",
            cancelButtonText: "Verificar nuevamente",
            confirmButtonColor: "#0056b3",
            cancelButtonColor: "#6c757d",
        }).then((result) => {
            if (result.isConfirmed) finalizarGuardadoPreConvoy(data, false);
        });
    } else {
        finalizarGuardadoPreConvoy(data, true);
    }
}

function finalizarGuardadoPreConvoy(data, todoAprobado) {
    datosReunionConvoy = data;
    reunionPreConvoyGuardada = true;
    gestionarModalPreConvoy(false);
    actualizarBotonReunionConvoy();

    Swal.fire({
        title: todoAprobado
            ? "Reunión Confirmada"
            : "Reunión Guardada con Observaciones",
        text: todoAprobado
            ? "La Reunión Pre-convoy ha sido registrada. La solicitud está lista para ser enviada."
            : "La Reunión Pre-convoy ha sido registrada con algunos puntos marcados en 'NO'.",
        icon: todoAprobado ? "success" : "info",
        confirmButtonColor: "#0056b3",
    });
}

// ====================================================================
// GESTIÓN DE PARADAS
// ====================================================================
function toggleSeccionParadas(mostrar) {
    const contenedor = document.getElementById("contenedorParadas");
    const lista = document.getElementById("listaParadas");

    if (contenedor) {
        if (mostrar) {
            contenedor.classList.remove("hidden");
            if (lista?.children.length === 0) agregarParada();
        } else {
            contenedor.classList.add("hidden");
            if (lista) lista.innerHTML = "";
            contadorParadas = 0;
        }
    }
}

function agregarParada() {
    if (contadorParadas >= MAX_PARADAS) {
        Swal.fire({
            title: "Límite de Paradas Alcanzado",
            text: `Solo se permiten hasta ${MAX_PARADAS} paradas por viaje.`,
            icon: "warning",
            confirmButtonColor: "#0056b3",
        });
        return;
    }

    contadorParadas++;
    const lista = document.getElementById("listaParadas");
    if (!lista) return;

    const paradasActuales = lista.querySelectorAll(".parada-item").length;
    if (paradasActuales % 4 === 0) {
        const nuevaFila = document.createElement("div");
        nuevaFila.classList.add("paradas-fila");
        nuevaFila.id = `paradas-fila-${Math.ceil((paradasActuales + 1) / 4)}`;
        lista.appendChild(nuevaFila);
    }

    const filas = lista.querySelectorAll(".paradas-fila");
    const ultimaFila = filas[filas.length - 1];
    const paradaDiv = document.createElement("div");
    paradaDiv.classList.add("parada-item");
    paradaDiv.id = `parada-${contadorParadas}`;
    paradaDiv.innerHTML = `
        <div class="parada-header">
            <span class="parada-titulo">Parada ${contadorParadas}</span>
            <button type="button" class="btn-remove-parada-compact" onclick="eliminarParada(${contadorParadas})" title="Eliminar Parada">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="parada-body-compact">
            <div>
                <label class="parada-label-small">Propósito</label>
                <select name="paradas[${contadorParadas}][motivo]" class="form-control form-control-sm" required>
                    <option value="">Seleccione...</option>
                    <option value="Carga de Combustible">Combustible</option>
                    <option value="Alimentos">Alimentos</option>
                    <option value="Aseguramiento de Carga">Aseguramiento de Carga</option>
                    <option value="Notificacion de Reporte">Notificacion de Reporte</option>
                    <option value="Cambio de Conductor">Cambio de Conductor</option>
                </select>
            </div>
            <div>
                <label class="parada-label-small">Ubicación</label>
                <input type="text" name="paradas[${contadorParadas}][lugar]" class="form-control form-control-sm" placeholder="Lugar / Ciudad" required>
            </div>
        </div>`;

    ultimaFila.appendChild(paradaDiv);
    reorganizarParadas();
}

function eliminarParada(id) {
    const elemento = document.getElementById(`parada-${id}`);
    if (elemento) {
        elemento.remove();
        contadorParadas--;
        reorganizarParadas();

        const lista = document.getElementById("listaParadas");
        if (lista?.children.length === 0) {
            const inputNo = document.querySelector(
                'input[name="tiene_paradas"][value="no"]',
            );
            if (inputNo) inputNo.checked = true;
            toggleSeccionParadas(false);
        }
    }
}

function reorganizarParadas() {
    const lista = document.getElementById("listaParadas");
    if (!lista) return;

    const todasParadas = lista.querySelectorAll(".parada-item");
    const filas = lista.querySelectorAll(".paradas-fila");
    filas.forEach((fila) => fila.remove());

    if (todasParadas.length === 0) {
        contadorParadas = 0;
        return;
    }

    let contadorGlobal = 0;
    todasParadas.forEach((parada, index) => {
        contadorGlobal++;
        if (index % 4 === 0) {
            const nuevaFila = document.createElement("div");
            nuevaFila.classList.add("paradas-fila");
            nuevaFila.id = `paradas-fila-${Math.floor(index / 4) + 1}`;
            lista.appendChild(nuevaFila);
        }

        const filasActuales = lista.querySelectorAll(".paradas-fila");
        const ultimaFila = filasActuales[filasActuales.length - 1];

        const paradaTitulo = parada.querySelector(".parada-titulo");
        if (paradaTitulo) paradaTitulo.textContent = `Parada ${contadorGlobal}`;

        const select = parada.querySelector("select");
        const input = parada.querySelector('input[type="text"]');
        const btnEliminar = parada.querySelector(".btn-remove-parada-compact");

        if (select)
            select.setAttribute("name", `paradas[${contadorGlobal}][motivo]`);
        if (input)
            input.setAttribute("name", `paradas[${contadorGlobal}][lugar]`);
        if (btnEliminar) {
            btnEliminar.setAttribute(
                "onclick",
                `eliminarParada(${contadorGlobal})`,
            );
            btnEliminar.setAttribute(
                "title",
                `Eliminar Parada ${contadorGlobal}`,
            );
        }

        parada.id = `parada-${contadorGlobal}`;
        ultimaFila.appendChild(parada);
    });

    contadorParadas = contadorGlobal;
}

// ====================================================================
// GESTIÓN DE FORMULARIO PRINCIPAL
// ====================================================================
function gestionarModalFormulario(abrir) {
    const modal = document.getElementById("modalFormulario");

    if (abrir) {
        if (modal) {
            // Llama a la BD para obtener el folio más reciente
            cargarFolioEstimado();

            modal.classList.add("active");
            document.body.style.overflow = "hidden";
            if (contadorUnidades === 0) agregarUnidad();
        }
    } else {
        Swal.fire({
            title: "¿Desea cerrar el formulario?",
            text: "Se perderán los datos no guardados de la solicitud actual.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, cerrar",
            cancelButtonText: "Continuar editando",
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#0056b3",
        }).then((result) => {
            if (result.isConfirmed) {
                if (modal) modal.classList.remove("active");
                document.body.style.overflow = "auto";
                limpiarFormulario();
            }
        });
    }
}

function limpiarFormulario() {
    const formViaje = document.getElementById("formViaje");
    if (formViaje) formViaje.reset();

    const cuerpoTablaUnidades = document.getElementById("cuerpoTablaUnidades");
    if (cuerpoTablaUnidades) cuerpoTablaUnidades.innerHTML = "";

    contadorUnidades = 0;
    actualizarContadorUnidades();
    actualizarLabelTipoUnidad();
    actualizarBotonAgregar();
    cargarFolioEstimado();

    Object.keys(datosInspeccionLigera).forEach(
        (key) => delete datosInspeccionLigera[key],
    );
    Object.keys(datosInspeccionPesada).forEach(
        (key) => delete datosInspeccionPesada[key],
    );

    reunionPreConvoyGuardada = false;
    datosReunionConvoy = {};
    actualizarBotonReunionConvoy();

    const listaParadas = document.getElementById("listaParadas");
    if (listaParadas) listaParadas.innerHTML = "";
    contadorParadas = 0;
    toggleSeccionParadas(false);

    evaluacionRiesgoGuardada = false;
    puntajeRiesgoTotal = 0;

    const btnEval = document.getElementById("btnEvaluacionRiesgo");
    if (btnEval) {
        btnEval.classList.remove(
            "evaluacion-completada",
            "btn-riesgo-bajo",
            "btn-riesgo-medio",
            "btn-riesgo-alto",
            "btn-riesgo-muy-alto",
        );
        btnEval.innerHTML =
            '<i class="fas fa-list-check"></i> Realizar Evaluación del Viaje (Requerido)';
    }

    const formEvaluacionRiesgo = document.getElementById(
        "formEvaluacionRiesgo",
    );
    if (formEvaluacionRiesgo) formEvaluacionRiesgo.reset();

    actualizarBotonEvaluacion();
}

// ====================================================================
// FUNCIÓN DE VALIDACIÓN COMPLETA PARA ENVÍO DE SOLICITUD
// ====================================================================
function validarSolicitudCompleta() {
    const form = document.getElementById("formViaje");

    // =========================================================
    // 1. VALIDACIÓN DEL DESTINO DEL VIAJE
    // =========================================================
    const destinoHidden = document.getElementById("inputDestinoHidden");
    const destinoEspecifico = document.getElementById("destinoEspecifico");
    const wrapperDestino = document.getElementById("wrapperDestino");

    if (!destinoHidden || destinoHidden.value.trim() === "") {
        mostrarError({
            titulo: "Región de Destino Faltante",
            texto: "Abra el menú desplegable y seleccione el Estado y Municipio al que se dirige el viaje.",
            elementoScroll: wrapperDestino,
            claseAnimacion: "error-shake",
        });
        return false;
    }

    if (!destinoEspecifico || destinoEspecifico.value.trim() === "") {
        mostrarError({
            titulo: "Dirección Exacta Faltante",
            texto: "Escriba el nombre de la instalación, pozo, empresa o la dirección específica a donde se dirige.",
            elementoFocus: destinoEspecifico,
            elementoScroll: destinoEspecifico,
            claseAnimacion: "error-shake",
        });
        return false;
    }

    // =========================================================
    // 2. VALIDACIÓN DETALLES DEL TRAYECTO
    // =========================================================
    const origen = form.querySelector('input[name="origen"]');
    if (!origen || origen.value.trim() === "") {
        mostrarError({
            titulo: "Lugar de Salida Faltante",
            texto: "Indique la ciudad, instalación o base de donde partirá el viaje.",
            elementoFocus: origen,
            elementoScroll: origen,
            claseAnimacion: "error-shake",
        });
        return false;
    }

    const llegada = form.querySelector('input[name="llegada"]');
    if (!llegada || llegada.value.trim() === "") {
        mostrarError({
            titulo: "Lugar de Llegada Faltante",
            texto: "Indique el destino final exacto al que llegará el viaje.",
            elementoFocus: llegada,
            elementoScroll: llegada,
            claseAnimacion: "error-shake",
        });
        return false;
    }

    const fechaInicio = document.getElementById("fechaInicioViaje");
    if (!fechaInicio || fechaInicio.value.trim() === "") {
        mostrarError({
            titulo: "Fecha de Salida Faltante",
            texto: "Seleccione en el calendario la fecha programada para iniciar el viaje.",
            elementoFocus: fechaInicio,
            elementoScroll: fechaInicio,
        });
        return false;
    }

    const fechaFin = document.getElementById("fechaFinViaje");
    if (!fechaFin || fechaFin.value.trim() === "") {
        mostrarError({
            titulo: "Fecha de Retorno Faltante",
            texto: "Seleccione en el calendario la fecha estimada en la que concluirá el viaje.",
            elementoFocus: fechaFin,
            elementoScroll: fechaFin,
        });
        return false;
    }

    const horaInicio = document.getElementById("horaInicioViaje");
    if (!horaInicio || horaInicio.value.trim() === "") {
        mostrarError({
            titulo: "Hora de Salida Faltante",
            texto: "Indique la hora exacta a la que el vehículo comenzará el trayecto.",
            elementoFocus: horaInicio,
            elementoScroll: horaInicio,
        });
        return false;
    }

    const horaFin = document.getElementById("horaFinViaje");
    if (!horaFin || horaFin.value.trim() === "") {
        mostrarError({
            titulo: "Hora de Término Faltante",
            texto: "Indique la hora estimada a la que terminará el viaje.",
            elementoFocus: horaFin,
            elementoScroll: horaFin,
        });
        return false;
    }

    // =========================================================
    // 3. VALIDACIÓN EXHAUSTIVA DE PARADAS INTERMEDIAS
    // =========================================================
    const tieneParadas = document.querySelector(
        'input[name="tiene_paradas"]:checked',
    )?.value;
    if (tieneParadas === "si") {
        const listaParadas = document.getElementById("listaParadas");
        if (!listaParadas || listaParadas.children.length === 0) {
            mostrarError({
                titulo: "Bloque de Paradas Vacío",
                texto: "Usted indicó que realizará paradas, pero no ha registrado ninguna en el sistema.",
                elementoScroll: document.querySelector(".radio-group-paradas"),
            });
            return false;
        }

        const itemsParadas = listaParadas.querySelectorAll(".parada-item");
        for (let i = 0; i < itemsParadas.length; i++) {
            const parada = itemsParadas[i];
            const select = parada.querySelector("select");
            const inputLugar = parada.querySelector('input[type="text"]');

            if (!select?.value) {
                mostrarError({
                    titulo: `Motivo Faltante en Parada #${i + 1}`,
                    texto: "Seleccione la razón o propósito por el cual se detendrá en esta ubicación.",
                    elementoFocus: select,
                    elementoScroll: parada,
                    claseAnimacion: "error-shake",
                });
                return false;
            }
            if (!inputLugar?.value.trim()) {
                mostrarError({
                    titulo: `Ubicación Faltante en Parada #${i + 1}`,
                    texto: "Escriba el nombre del lugar, gasolinera o punto de control donde realizará esta parada.",
                    elementoFocus: inputLugar,
                    elementoScroll: parada,
                    claseAnimacion: "error-shake",
                });
                return false;
            }
        }
    }

    // =========================================================
    // 4. VALIDACIÓN ESTRUCTURAL DE UNIDADES VEHICULARES
    // =========================================================
    if (contadorUnidades === 0) {
        Swal.fire({
            title: "Registro Sin Unidades",
            text: "Debe agregar al menos un vehículo y asignar a su respectivo conductor para poder procesar el gerenciamiento.",
            icon: "warning",
            confirmButtonColor: "#0056b3",
        });
        return false;
    }

    // =========================================================
    // 5. VALIDACIÓN INDIVIDUAL DE CADA UNIDAD ASIGNADA
    // =========================================================
    for (let i = 1; i <= contadorUnidades; i++) {
        const fila = document.getElementById(`unidad-${i}`);
        if (!fila) continue;

        // 5A. Selección de Vehículo
        const vehiculoHidden = document.getElementById(`vehicle-hidden-${i}`);
        if (!vehiculoHidden?.value) {
            mostrarError({
                titulo: `Vehículo Faltante en Unidad #${i}`,
                texto: "Despliegue el menú de vehículos y asigne qué equipo se utilizará en esta posición.",
                elementoScroll: fila,
                claseAnimacion: "error-shake",
            });
            return false;
        }

        // 5B. Estado de la Inspección Vehicular
        const btnInspeccion = document.getElementById(`btn-inspeccion-${i}`);
        if (!btnInspeccion || btnInspeccion.dataset.realizada !== "true") {
            mostrarError({
                titulo: `Inspección Vehicular Pendiente`,
                texto: `Es indispensable realizar y guardar el registro de la inspección pre-viaje para la <strong>Unidad #${i}</strong>.`,
                icono: "error",
                color: "#dc3545",
                elementoScroll: btnInspeccion,
                claseAnimacion: "pulse-animation",
            });
            return false;
        }

        // 5C. Asignación del Conductor Principal
        const conductorInput = document.getElementById(`conductor-${i}`);
        if (!conductorInput?.value.trim()) {
            mostrarError({
                titulo: `Conductor Faltante en Unidad #${i}`,
                texto: `Busque y asigne el nombre del conductor principal responsable de operar este vehículo.`,
                elementoFocus: conductorInput,
                elementoScroll: fila,
            });
            return false;
        }

        // 5D. Registro de Alcoholimetría
        const alcoholPct = document.getElementById(`alcohol-pct-${i}`);
        if (!alcoholPct?.value.trim()) {
            mostrarError({
                titulo: `Registro de Alcoholimetría Faltante`,
                texto: `Debe ingresar el resultado numérico de la prueba de alcoholimetría aplicada al conductor de la Unidad #${i}.`,
                elementoFocus: alcoholPct,
                elementoScroll: fila,
            });
            return false;
        } else if (parseFloat(alcoholPct.value) > 0.4) {
            mostrarError({
                titulo: `Conductor Fuera de Norma`,
                texto: `El nivel de alcohol registrado (${alcoholPct.value}%) en la Unidad #${i} excede el límite máximo permitido de 0.4%. El conductor no es apto para el viaje.`,
                icono: "error",
                color: "#dc3545",
                elementoFocus: alcoholPct,
                elementoScroll: fila,
            });
            return false;
        }

        // 5E. Registro de Presión Arterial
        const presionValor = document.getElementById(`presion-valor-${i}`);
        if (!presionValor?.value.trim()) {
            mostrarError({
                titulo: `Presión Arterial Faltante`,
                texto: `Ingrese los valores sistólicos y diastólicos (ej. 120/80) del conductor en la Unidad #${i}.`,
                elementoFocus: presionValor,
                elementoScroll: fila,
            });
            return false;
        }

        // 5F. Control Médico
        const medicamentoSelect = document.getElementById(`medicamento-${i}`);
        if (!medicamentoSelect?.value) {
            mostrarError({
                titulo: `Control de Medicamentos Faltante`,
                texto: `Especifique seleccionando 'Sí' o 'No' respecto al consumo actual de medicamentos del conductor en la Unidad #${i}.`,
                elementoFocus: medicamentoSelect,
                elementoScroll: fila,
            });
            return false;
        }

        const medicamentoNombre = document.getElementById(
            `medicamento-nombre-${i}`,
        );
        if (
            medicamentoSelect.value === "si" &&
            (!medicamentoNombre || !medicamentoNombre.value.trim())
        ) {
            mostrarError({
                titulo: `Sustancia Activa No Especificada`,
                texto: `Debido a que marcó consumo médico activo en la Unidad #${i}, es estrictamente necesario redactar el nombre del medicamento.`,
                elementoFocus: medicamentoNombre,
                elementoScroll: fila,
            });
            return false;
        }

        // 5G. Gestión de Sueño
        const dormir = document.getElementById(`dormir-${i}`);
        const levantar = document.getElementById(`levantar-${i}`);
        if (!dormir?.value.trim()) {
            mostrarError({
                titulo: `Inicio de Descanso Faltante`,
                texto: `Establezca la hora exacta en la que el conductor de la Unidad #${i} comenzó su ciclo de sueño.`,
                elementoFocus: dormir,
                elementoScroll: fila,
            });
            return false;
        }
        if (!levantar?.value.trim()) {
            mostrarError({
                titulo: `Fin de Descanso Faltante`,
                texto: `Establezca la hora exacta en la que el conductor de la Unidad #${i} finalizó su ciclo de sueño.`,
                elementoFocus: levantar,
                elementoScroll: fila,
            });
            return false;
        }
    }

    // =========================================================
    // 6. VALIDACIÓN DE CONVOY (MÚLTIPLES UNIDADES)
    // =========================================================
    const btnReunionPreConvoy = document.getElementById("btnReunionPreConvoy");
    if (contadorUnidades > 1 && !reunionPreConvoyGuardada) {
        mostrarError({
            titulo: "Protocolo Pre-Convoy Incompleto",
            texto: "El sistema detecta múltiples unidades. Por normatividad, debe ejecutar y respaldar la lista de verificación en la Reunión Pre-Convoy.",
            icono: "warning",
            color: "#f08a1f",
            elementoScroll: btnReunionPreConvoy,
            claseAnimacion: "pulse-animation",
        });
        return false;
    }

    // =========================================================
    // 7. CERTIFICACIÓN DE LA EVALUACIÓN DE RIESGOS
    // =========================================================
    const btnEvaluacionRiesgo = document.getElementById("btnEvaluacionRiesgo");
    if (!evaluacionRiesgoGuardada) {
        mostrarError({
            titulo: "Matriz de Riesgo Faltante",
            texto: "Antes de someter este gerenciamiento a escrutinio, es indispensable ponderar y calcular el nivel de riesgo del trayecto empleando el módulo de Evaluación.",
            elementoScroll: btnEvaluacionRiesgo,
            claseAnimacion: "pulse-animation",
        });
        return false;
    }

    // =========================================================
    // 8. VALIDACIÓN DEL AUTORIZADOR
    // =========================================================
    const autorizadorSeleccionado = document.querySelector(
        'input[name="autorizador_id"]:checked',
    );
    if (!autorizadorSeleccionado) {
        const seccionDestinatario = document.getElementById(
            "seccionDestinatario",
        );
        mostrarError({
            titulo: "Aprobador No Seleccionado",
            texto: "Debe designar a la autoridad correspondiente que será responsable de revisar y emitir la aprobación final de esta solicitud.",
            elementoScroll: seccionDestinatario,
            claseAnimacion: "pulseWarning",
        });
        return false;
    }

    return true; // Todas las validaciones pasaron
}

// ====================================================================
// FUNCIÓN MAESTRA PARA MOSTRAR ERRORES Y SCROLL SIN REBOTES
// ====================================================================
function mostrarError(config) {
    if (config.elementoScroll) {
        config.elementoScroll.scrollIntoView({
            behavior: "smooth",
            block: "center",
        });

        if (config.claseAnimacion) {
            config.elementoScroll.classList.add(config.claseAnimacion);
            setTimeout(
                () =>
                    config.elementoScroll.classList.remove(
                        config.claseAnimacion,
                    ),
                2000,
            );
        }
    }

    Swal.fire({
        title: config.titulo,
        html: config.texto,
        icon: config.icono || "warning",
        confirmButtonColor: config.color || "#0056b3",
        returnFocus: false,
    }).then(() => {
        if (config.elementoFocus) {
            config.elementoFocus.focus({ preventScroll: true });
        }
    });
}

// AÑADE ESTA FUNCIÓN AL INICIO DE TU ARCHIVO
function convertirFechaParaMySQL(fechaDMA) {
    if (!fechaDMA) return "";

    // Limpiar caracteres de escape
    fechaDMA = fechaDMA.replace(/\\/g, "");

    // Si ya está en formato YYYY-MM-DD, devolverlo
    if (/^\d{4}-\d{2}-\d{2}$/.test(fechaDMA)) {
        return fechaDMA;
    }

    // Convertir de DD/MM/YYYY a YYYY-MM-DD
    const partes = fechaDMA.split(/[\/\-]/);
    if (partes.length === 3) {
        const dia = partes[0].padStart(2, "0");
        const mes = partes[1].padStart(2, "0");
        const año = partes[2];
        const fechaMySQL = `${año}-${mes}-${dia}`;
        console.log("Fecha convertida:", fechaDMA, "->", fechaMySQL);
        return fechaMySQL;
    }

    return fechaDMA;
}
// ====================================================================
// FUNCIÓN PARA ENVIAR SOLICITUD AL BACKEND
// ====================================================================
function enviarSolicitudAJAX() {
    Swal.fire({
        title: "Guardando solicitud...",
        text: "Por favor espere",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });

    // Convertir las fechas al formato MySQL antes de enviar
    const fechaSolicitud = convertirFechaParaMySQL(
        document.getElementById("fechaSolicitudHidden")?.value || "",
    );
    const fechaInicio = convertirFechaParaMySQL(
        document.getElementById("fechaInicioViaje")?.value || "",
    );
    const fechaFin = convertirFechaParaMySQL(
        document.getElementById("fechaFinViaje")?.value || "",
    );

    console.log("Fechas convertidas:", {
        solicitud: fechaSolicitud,
        inicio: fechaInicio,
        fin: fechaFin,
    });

    const data = {
        //folio: document.getElementById('codigoViaje')?.textContent || '',
        fecha_solicitud: fechaSolicitud,
        solicitante: document.getElementById("solicitante")?.value || "",
        departamento: document.getElementById("departamento")?.value || "",
        destino_predefinido:
            document.getElementById("inputDestinoHidden")?.value || "",
        destino_especifico:
            document.getElementById("destinoEspecifico")?.value || "",
        origen: document.querySelector('input[name="origen"]')?.value || "",
        llegada: document.querySelector('input[name="llegada"]')?.value || "",
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
        hora_inicio: document.getElementById("horaInicioViaje")?.value || "",
        hora_fin: document.getElementById("horaFinViaje")?.value || "",
        tiene_paradas:
            document.querySelector('input[name="tiene_paradas"]:checked')
                ?.value || "no",
        autorizador_id:
            document.querySelector('input[name="autorizador_id"]:checked')
                ?.value || "",
        riesgo_puntaje: puntajeRiesgoTotal || 0,
        riesgo_nivel: document
            .getElementById("btnEvaluacionRiesgo")
            ?.textContent.includes("Bajo")
            ? "bajo"
            : document
                    .getElementById("btnEvaluacionRiesgo")
                    ?.textContent.includes("Medio")
              ? "medio"
              : document
                      .getElementById("btnEvaluacionRiesgo")
                      ?.textContent.includes("Alto")
                ? "alto"
                : "muy_alto",
    };

    // Recolectar paradas
    data.paradas = [];
    if (data.tiene_paradas === "si") {
        const paradas = document.querySelectorAll(".parada-item");
        paradas.forEach((parada, index) => {
            const select = parada.querySelector("select");
            const input = parada.querySelector('input[type="text"]');
            data.paradas.push({
                index: index + 1,
                motivo: select?.value || "",
                lugar: input?.value || "",
            });
        });
    }

    // Recolectar unidades
    data.unidades = [];
    for (let i = 1; i <= contadorUnidades; i++) {
        const unidad = recolectarUnidad(i);
        if (unidad) data.unidades.push(unidad);
    }

    // Recolectar evaluación de riesgo
    if (evaluacionRiesgoGuardada) {
        data.evaluacion_riesgo = recolectarEvaluacionRiesgo();
    }

    // Recolectar reunión pre-convoy
    if (reunionPreConvoyGuardada) {
        data.reunion_pre_convoy = datosReunionConvoy;
    }

    // URL CORREGIDA - Usando la ruta que definiste
    fetch("/qhse/gerenciamiento/journeys/store", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                ?.content,
            Accept: "application/json",
        },
        body: JSON.stringify(data),
    })
        .then((response) => {
            if (!response.ok) {
                return response.text().then((text) => {
                    throw new Error(
                        `Error ${response.status}: ${text.substring(0, 200)}`,
                    );
                });
            }
            return response.json();
        })
        .then((result) => {
            if (result.success) {
                Swal.fire({
                    title: "¡Viaje Registrado!",
                    // AQUÍ ESTÁ EL CAMBIO: Muestra el folio que le asignó el backend
                    html: `${result.message}<br><br><span style="font-size: 1.2em;">Folio asignado: <strong>${result.folio}</strong></span>`,
                    icon: "success",
                    confirmButtonColor: "#0056b3",
                }).then(() => {
                    document
                        .getElementById("modalFormulario")
                        ?.classList.remove("active");
                    document.body.style.overflow = "auto";
                    limpiarFormulario();

                    if (typeof cargarViajes === "function") cargarViajes();
                });
            } else {
                Swal.fire({
                    title: "Error",
                    text: result.message || "Error al guardar el viaje",
                    icon: "error",
                    confirmButtonColor: "#dc3545",
                });
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            Swal.fire({
                title: "Error de conexión",
                text: error.message || "No se pudo conectar con el servidor",
                icon: "error",
                confirmButtonColor: "#dc3545",
            });
        });
}
// ====================================================================
// FUNCIÓN AUXILIAR PARA RECOLECTAR UNA UNIDAD
// ====================================================================
// ====================================================================
// FUNCIÓN AUXILIAR PARA RECOLECTAR UNA UNIDAD
// ====================================================================
function recolectarUnidad(numero) {
    const unidad = {};

    unidad.vehiculo =
        document.getElementById(`vehicle-hidden-${numero}`)?.value || "";
    unidad.tipo_vehiculo =
        document.getElementById(`tipo-vehiculo-${numero}`)?.textContent || "";

    const conductorInput = document.getElementById(`conductor-${numero}`);
    unidad.conductor = conductorInput?.value || "";
    // AQUI AGREGAMOS LA LÍNEA PARA EL ID:
    unidad.conductor_id = conductorInput?.dataset.conductorId || null;

    unidad.alcohol_pct =
        document.getElementById(`alcohol-pct-${numero}`)?.value || "0.0";
    unidad.presion_valor =
        document.getElementById(`presion-valor-${numero}`)?.value || "";
    unidad.toma_medicamento =
        document.getElementById(`medicamento-${numero}`)?.value || "";
    unidad.medicamento_nombre =
        document.getElementById(`medicamento-nombre-${numero}`)?.value || "";

    unidad.vigencia_lic =
        document.getElementById(`vigencia-lic-${numero}`)?.value || "";
    unidad.vigencia_man =
        document.getElementById(`vigencia-man-${numero}`)?.value || "";

    unidad.hora_dormir =
        document.getElementById(`dormir-${numero}`)?.value || "";
    unidad.hora_levantar =
        document.getElementById(`levantar-${numero}`)?.value || "";
    unidad.total_dormidas =
        document.getElementById(`total-hrs-dormidas-${numero}`)?.value ||
        "0:00";
    unidad.horas_despierto =
        document.getElementById(`horas-despierto-${numero}`)?.value || "0:00";
    unidad.horas_viaje =
        document.getElementById(`horas-viaje-${numero}`)?.value || "0:00";
    unidad.total_finalizar =
        document.getElementById(`total-hrs-finalizar-${numero}`)?.value ||
        "0:00";

    unidad.pasajeros = [];
    const containerPasajeros = document.getElementById(
        `pasajeros-unidad-${numero}`,
    );
    if (containerPasajeros) {
        const filas = containerPasajeros.querySelectorAll(
            ".pasajero-input-group",
        );
        filas.forEach((fila) => {
            const index = fila.dataset.index;
            const input = fila.querySelector("input");
            if (input && input.value.trim()) {
                const pasajero = {
                    // AQUÍ CAPTURAMOS EL ID DEL PASAJERO/RELEVO
                    id: input.dataset.conductorId || null,
                    nombre: input.value,
                    es_relevo: fila.classList.contains("es-relevo"),
                };

                if (pasajero.es_relevo) {
                    pasajero.alcohol_pct = fila.dataset.alcoholPct;
                    pasajero.presion_valor = fila.dataset.presionValor;
                    pasajero.medicamento = fila.dataset.medicamento;
                    pasajero.medicamento_nombre =
                        fila.dataset.medicamentoNombre;
                    pasajero.dormir = fila.dataset.dormir;
                    pasajero.levantar = fila.dataset.levantar;
                    pasajero.hrs_dormidas = fila.dataset.hrsDormidas;
                    pasajero.hr_despierto = fila.dataset.hrDespierto;
                    pasajero.duracion_viaje = fila.dataset.duracionViaje;
                    pasajero.total_hrs = fila.dataset.totalHrs;
                    pasajero.vigencia_lic = fila.dataset.vigenciaLic;
                    pasajero.vigencia_man = fila.dataset.vigenciaMan;
                }

                unidad.pasajeros.push(pasajero);
            }
        });
    }

    if (datosInspeccionLigera[numero]) {
        unidad.inspeccion_ligera = datosInspeccionLigera[numero];
    }
    if (datosInspeccionPesada[numero]) {
        unidad.inspeccion_pesada = datosInspeccionPesada[numero];
    }

    return unidad;
}

// ====================================================================
// FUNCIÓN AUXILIAR PARA RECOLECTAR EVALUACIÓN DE RIESGO (CORREGIDA)
// ====================================================================
function recolectarEvaluacionRiesgo() {
    const form = document.getElementById("formEvaluacionRiesgo");
    if (!form) return null;

    const evaluacion = {};

    const mapeoCampos = {
        ev_manejo: "defensive_driving",
        ev_horas: "awake_hours",
        ev_vehiculos: "fleet_composition",
        ev_comunicacion: "communication",
        ev_clima: "weather",
        ev_iluminacion: "lighting",
        ev_carretera: "road_condition",
        ev_otras: "extra_road_hazards",
        ev_animales: "wildlife_activity",
        ev_seguridad: "route_security",
        ev_radiactivo: "hazardous_material",
    };
    for (const [campoFront, campoBD] of Object.entries(mapeoCampos)) {
        const seleccionado = form.querySelector(
            `input[name="${campoFront}"]:checked`,
        );
        if (seleccionado) {
            // EL CAMBIO ESTÁ EN ESTA LÍNEA:
            // .replace(/\s+/g, ' ') convierte cualquier salto de línea o múltiples espacios en un solo espacio limpio.
            const textoOpcion = seleccionado.parentElement.textContent
                .replace(/\s+/g, " ")
                .trim();

            // Asignamos el texto a la columna '_option' y el número a '_score'
            evaluacion[`${campoBD}_option`] = textoOpcion;
            evaluacion[`${campoBD}_score`] = parseInt(seleccionado.value) || 0;
        }
    }

    // El resto se mantiene exactamente igual
    evaluacion.is_night_shift =
        form.querySelector('input[name="ev_horario_nocturno"]')?.checked ||
        false;
    evaluacion.has_low_sleep =
        form.querySelector('input[name="ev_horas_dormidas"]')?.checked || false;
    evaluacion.exceeds_midnight =
        form.querySelector('input[name="ev_rebase_medianoche"]')?.checked ||
        false;
    evaluacion.extreme_fatigue =
        form.querySelector('input[name="ev_16hrs_despierto"]')?.checked ||
        false;
    evaluacion.total_score = puntajeRiesgoTotal || 0;
    evaluacion.risk_level = document
        .getElementById("btnEvaluacionRiesgo")
        ?.textContent.includes("Bajo")
        ? "bajo"
        : document
                .getElementById("btnEvaluacionRiesgo")
                ?.textContent.includes("Medio")
          ? "medio"
          : document
                  .getElementById("btnEvaluacionRiesgo")
                  ?.textContent.includes("Alto")
            ? "alto"
            : "muy_alto";

    return evaluacion;
}

// ====================================================================
// FUNCIÓN PRINCIPAL DE ENVÍO DE SOLICITUD
// ====================================================================
function enviarSolicitud() {
    if (!validarSolicitudCompleta()) {
        return;
    }

    enviarSolicitudAJAX();
}

function actualizarBotonEnviarSolicitud() {
    const btnEnviar = document.getElementById("btnEnviarSolicitud");
    if (!btnEnviar) return;

    if (!evaluacionRiesgoGuardada) {
        btnEnviar.title = "⚠️ Falta completar la Evaluación de Riesgo";
        btnEnviar.style.opacity = "1";
        btnEnviar.style.pointerEvents = "auto";
    } else if (contadorUnidades <= 1) {
        btnEnviar.title = "Enviar Solicitud";
    } else if (contadorUnidades > 1 && !reunionPreConvoyGuardada) {
        btnEnviar.title = "⚠️ Falta completar la Reunión Pre-convoy";
    } else {
        btnEnviar.title = "Enviar Solicitud";
    }

    btnEnviar.disabled = false;
}

// ====== VARIABLES Y ESTADOS PARA SEGUIMIENTO ======
let estadoViajeActual = "Por Iniciar";

// ====== GESTIÓN DE MODALES DE SEGUIMIENTO ======
function abrirModalRuta(idViaje) {
    document.getElementById("lblRutaViaje").innerText = idViaje;
    estadoViajeActual = "Por Iniciar";
    actualizarBotonPrincipalRuta();

    const modal = document.getElementById("modalSeguimientoRuta");
    modal.style.display = "flex";
    setTimeout(() => {
        modal.classList.add("active");
    }, 10);
}

function cerrarModalRuta() {
    const modal = document.getElementById("modalSeguimientoRuta");
    modal.classList.remove("active");
    setTimeout(() => {
        modal.style.display = "none";
    }, 300);
}

function abrirModalHistorial(idViaje) {
    document.getElementById("lblHistorialViaje").innerText = idViaje;
    const modal = document.getElementById("modalHistorialActividad");
    modal.style.display = "flex";
    setTimeout(() => {
        modal.classList.add("active");
    }, 10);
}

function cerrarModalHistorial() {
    const modal = document.getElementById("modalHistorialActividad");
    modal.classList.remove("active");
    setTimeout(() => {
        modal.style.display = "none";
    }, 300);
}

// ====== LÓGICA DEL BOTÓN: INICIAR / FINALIZAR VIAJE ======
function actualizarBotonPrincipalRuta() {
    const btnMain = document.getElementById("btnMainViaje");
    if (estadoViajeActual === "Por Iniciar") {
        btnMain.innerHTML = '<i class="fas fa-play"></i> Iniciar Viaje';
        btnMain.style.background = "var(--primary-blue)";
    } else {
        btnMain.innerHTML =
            '<i class="fas fa-flag-checkered"></i> Finalizar Viaje';
        btnMain.style.background = "#166534";
    }
}

function toggleEstadoViaje() {
    if (estadoViajeActual === "Por Iniciar") {
        Swal.fire({
            title: "¿Quieres iniciar el viaje?",
            text: "El estado pasará a 'En Curso' y podrás registrar paradas e incidencias.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#1e3c72",
            cancelButtonColor: "#64748b",
            confirmButtonText: "Sí, iniciar",
            cancelButtonText: "No",
        }).then((result) => {
            if (result.isConfirmed) {
                estadoViajeActual = "En Curso";
                actualizarBotonPrincipalRuta();
                agregarEventoTimelineGlobal(
                    "Viaje Iniciado",
                    "La unidad ha salido a ruta.",
                    "bg-blue",
                    "fas fa-truck-moving",
                );
                Swal.fire({
                    toast: true,
                    position: "top-end",
                    icon: "success",
                    title: "Viaje Iniciado",
                    showConfirmButton: false,
                    timer: 2000,
                });
            }
        });
    } else {
        Swal.fire({
            title: "¿Estás seguro de que vas a finalizar el viaje?",
            text: "La bitácora se cerrará, el estado pasará a 'Finalizado' y solo se podrá ver el Historial.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#166534",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, finalizar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                agregarEventoTimelineGlobal(
                    "Viaje Finalizado",
                    "La unidad llegó exitosamente y el viaje se ha cerrado.",
                    "bg-green",
                    "fas fa-flag-checkered",
                );
                Swal.fire(
                    "¡Finalizado!",
                    "El viaje ha terminado. Puedes revisarlo en el Historial.",
                    "success",
                ).then(() => {
                    cerrarModalRuta();
                });
            }
        });
    }
}

// ====== FUNCIÓN AUXILIAR: AGREGAR AL TIMELINE DEL HISTORIAL ======
function agregarEventoTimelineGlobal(
    titulo,
    descripcion,
    colorClass,
    iconClass,
) {
    const timeline = document.getElementById("timelineHistorialGlobal");
    const horaActual = new Date().toLocaleTimeString("es-MX", {
        hour: "2-digit",
        minute: "2-digit",
    });

    let textColor = "";
    if (colorClass === "bg-red") textColor = "#c62828";
    if (colorClass === "bg-green") textColor = "#2e7d32";
    if (colorClass === "bg-purple") textColor = "#7e22ce";

    const htmlExtra = `
        <div class="timeline-item">
            <div class="timeline-icon ${colorClass}">
                <i class="${iconClass}"></i>
            </div>
            <div class="timeline-content">
                <h4 style="color: ${textColor}">${titulo}</h4>
                <span class="timeline-time"><i class="far fa-clock"></i> Hoy, ${horaActual}</span>
                <p>${descripcion}</p>
            </div>
        </div>
    `;
    timeline.insertAdjacentHTML("beforeend", htmlExtra);
    timeline.scrollTop = timeline.scrollHeight;
}

// ====== LÓGICA DE DETENCIÓN E INCIDENCIAS CON ALERTA ======
function abrirFormularioDetencion() {
    if (estadoViajeActual === "Por Iniciar") {
        Swal.fire(
            "Aviso",
            "Debes Iniciar el Viaje antes de poder registrar incidencias.",
            "info",
        );
        return;
    }
    document.getElementById("btnDetenerMarcha").style.display = "none";
    document.getElementById("formDetencion").style.display = "block";
}

function cancelarDetencion() {
    document.getElementById("formDetencion").style.display = "none";
    document.getElementById("btnDetenerMarcha").style.display = "flex";
    document.getElementById("motivoDetencionSelect").value = "";
    document.getElementById("notasDetencion").value = "";
}

function confirmarDetencion() {
    const selector = document.getElementById("motivoDetencionSelect");
    const motivo = selector.value;
    const notas = document.getElementById("notasDetencion").value;

    if (!motivo) {
        Swal.fire({
            icon: "warning",
            title: "Atención",
            text: "Seleccione un motivo de detención.",
        });
        return;
    }

    Swal.fire({
        title: "¿Notificar Incidencia?",
        text: `Se registrará una alerta por: ${motivo}`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d32f2f",
        cancelButtonColor: "#64748b",
        confirmButtonText: "Sí, notificar detención",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById("dotEstado").className =
                "status-dot pulsing-red";
            document.getElementById("txtEstadoActual").innerText =
                "UNIDAD DETENIDA";
            document.getElementById("txtEstadoActual").className =
                "status-value text-red";

            document.getElementById("formDetencion").style.display = "none";
            document.getElementById("btnReanudarMarcha").style.display = "flex";

            const textoDescripcion = notas
                ? `${motivo} - Notas: ${notas}`
                : motivo;
            agregarEventoTimelineGlobal(
                "Incidencia / Unidad Detenida",
                textoDescripcion,
                "bg-red",
                "fas fa-hand-paper",
            );

            selector.value = "";
            document.getElementById("notasDetencion").value = "";

            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "error",
                title: "Detención notificada",
                showConfirmButton: false,
                timer: 2000,
            });
        }
    });
}

function confirmarReanudacion() {
    document.getElementById("dotEstado").className = "status-dot pulsing-green";
    document.getElementById("txtEstadoActual").innerText = "EN RUTA";
    document.getElementById("txtEstadoActual").className =
        "status-value text-green";

    document.getElementById("btnReanudarMarcha").style.display = "none";
    document.getElementById("btnDetenerMarcha").style.display = "flex";

    agregarEventoTimelineGlobal(
        "Ruta Reanudada",
        "La unidad ha retomado su trayecto.",
        "bg-green",
        "fas fa-play",
    );

    Swal.fire({
        toast: true,
        position: "top-end",
        icon: "success",
        title: "Ruta reanudada",
        showConfirmButton: false,
        timer: 2000,
    });
}

// ====== LÓGICA DE PARADAS PROGRAMADAS CON ALERTA ======
function marcarParada(idCard, ubicacion, motivo, botonElemento) {
    if (estadoViajeActual === "Por Iniciar") {
        Swal.fire(
            "Aviso",
            "Debes Iniciar el viaje para registrar paradas operativas.",
            "info",
        );
        return;
    }

    Swal.fire({
        title: "¿Confirmar Parada?",
        text: `¿Estás seguro de registrar la parada en ${ubicacion}?`,
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#1e3c72",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, confirmar parada",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            const card = document.getElementById(idCard);
            card.classList.add("completed");
            botonElemento.disabled = true;
            botonElemento.innerHTML = '<i class="fas fa-check-double"></i>';

            agregarEventoTimelineGlobal(
                "Parada Alcanzada",
                `<i class="fas fa-map-marker-alt" style="color:var(--primary-blue)"></i> <strong>${ubicacion}</strong><br><small>Motivo: ${motivo}</small>`,
                "bg-purple",
                "fas fa-check-circle",
            );

            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: "Parada confirmada",
                showConfirmButton: false,
                timer: 2000,
            });
        }
    });
}

// ====== LÓGICA DE RELEVO DE CONDUCTOR ======
function ejecutarRelevo() {
    if (estadoViajeActual === "Por Iniciar") {
        Swal.fire("Aviso", "Inicia el viaje para registrar el relevo.", "info");
        return;
    }

    Swal.fire({
        title: "¿Confirmar Cambio de Conductor?",
        text: "Se registrará el relevo en el historial.",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#d67e29",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, cambiar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            const lblAlVolante = document.getElementById(
                "lblConductorAlVolante",
            );
            const lblDescansando = document.getElementById(
                "lblConductorDescansando",
            );

            const temp = lblAlVolante.innerText;
            lblAlVolante.innerText = lblDescansando.innerText;
            lblDescansando.innerText = temp;

            const caja = document.querySelector(".conductor-swap-box");
            caja.style.transform = "scale(0.96)";
            setTimeout(() => {
                caja.style.transform = "scale(1)";
            }, 150);

            agregarEventoTimelineGlobal(
                "Cambio de Conductor",
                `El conductor <strong>${lblAlVolante.innerText}</strong> ha tomado el volante.`,
                "bg-orange",
                "fas fa-user-check",
            );

            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: "Relevo registrado",
                showConfirmButton: false,
                timer: 2000,
            });
        }
    });
}
