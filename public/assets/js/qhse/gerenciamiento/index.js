// ====================================================================
// VARIABLES Y CONFIGURACIÓN GLOBAL
// ====================================================================
let codigoViaje = 4;
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
let tipoActualCamara = null;
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

// ====================================================================
// INICIALIZACIÓN
// ====================================================================
document.addEventListener("DOMContentLoaded", async function () {
    await Promise.all([
        cargarVehiculosDesdeBD(),
        cargarConductoresDesdeBD(),
        obtenerDestinosBackend(),
    ]);

    inicializarFlatpickr();
    inicializarInputsFotos();
    configurarEventosGlobales();
    generarCodigoViaje(false);
    actualizarBotonReunionConvoy();
    actualizarBotonEvaluacion();
});

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

    document.addEventListener("click", function (e) {
        const wrapper = document.getElementById("wrapperDestino");
        if (wrapper && !wrapper.contains(e.target))
            wrapper.classList.remove("open");
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
    if (formViaje)
        formViaje.addEventListener("submit", (e) => e.preventDefault());

    document.querySelectorAll(".nav-link-viajes").forEach((link) => {
        link.addEventListener("click", function (e) {
            e.preventDefault();
            document
            .querySelectorAll(".nav-link-viajes")
            .forEach((item) => item.classList.remove("active"));
            this.classList.add("active");
        });
    });

    const destinoSelector = document.getElementById("destinoPredefinido");
    const destinoEspecifico = document.getElementById("destinoEspecifico");
    if (destinoSelector && destinoEspecifico) {
        destinoSelector.addEventListener("change", function () {
            const isOtro = this.value === "Otro";
            destinoEspecifico.required = isOtro;
            destinoEspecifico.style.display = isOtro ? "block" : "none";
        });
    }
}

function inicializarFlatpickr() {
    flatpickr.localize(flatpickr.l10ns.es);
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

    const fechaInicioViaje = document.getElementById("fechaInicioViaje");
    const fechaFinViaje = document.getElementById("fechaFinViaje");
    if (fechaInicioViaje) flatpickr(fechaInicioViaje, configFecha);
    if (fechaFinViaje) flatpickr(fechaFinViaje, configFecha);

    const horaInicioViaje = document.getElementById("horaInicioViaje");
    const horaFinViaje = document.getElementById("horaFinViaje");
    if (horaInicioViaje) flatpickr(horaInicioViaje, configHora);
    if (horaFinViaje) flatpickr(horaFinViaje, configHora);
}

// ====================================================================
// FUNCIONES DE CARGA DE DATOS
// ====================================================================
async function cargarVehiculosDesdeBD() {
    try {
        const response = await fetch("/qhse/gerenciamiento/vehicles");
        if (!response.ok) throw new Error("Error al cargar vehículos");

        const data = await response.json();
        if (data.success) {
            vehiculosLigeros = data.ligeras || [];
            vehiculosPesados = data.pesadas || [];
            clasificacionVehiculos = data.clasificacion || {};
        } else {
            throw new Error(data.message || "Error del servidor");
        }
    } catch (error) {
        console.error("Error cargando vehículos:", error);
        vehiculosLigeros = [];
        vehiculosPesados = [];
        clasificacionVehiculos = {};
        Swal.fire(
            "Error de Conexión",
            "No se pudieron cargar los vehículos.",
            "error"
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
                data.message || "Error en la respuesta del servidor"
            );
        }
    } catch (error) {
        console.error("Error cargando conductores:", error);
        conductoresGlobales = [];
        datosConductoresGlobales = {};
        Swal.fire(
            "Error de Conexión",
            "No se pudieron cargar los conductores.",
            "error"
        );
    }
}

async function obtenerDestinosBackend() {
    try {
        const response = await fetch("/qhse/gerenciamiento/get-destinations");
        const data = await response.json();
        if (data.success) {
            datosDestinosGlobal = data.data;
            construirMenuDestinos();
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
        `total-hrs-dormidas-${unidadNumero}`
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
        `levantar-${unidadNumero}`
    )?.value;

    if (horaLevantar && horaInicioViaje) {
        const horasDespiertoStr = calcularDiferenciaHoras(
            horaLevantar,
            horaInicioViaje
        );
        const inputDespierto = document.getElementById(
            `horas-despierto-${unidadNumero}`
        );
        if (inputDespierto) inputDespierto.value = horasDespiertoStr;
    }

    if (horaInicioViaje && horaFinViaje) {
        const duracionViajeStr = calcularDiferenciaHoras(
            horaInicioViaje,
            horaFinViaje
        );
        const inputDuracion = document.getElementById(
            `horas-viaje-${unidadNumero}`
        );
        if (inputDuracion) inputDuracion.value = duracionViajeStr;
    }

    calcularTotalHoras(unidadNumero);
}

function calcularTotalHoras(unidadNumero) {
    const inputDespierto = document.getElementById(
        `horas-despierto-${unidadNumero}`
    );
    const inputDuracion = document.getElementById(
        `horas-viaje-${unidadNumero}`
    );
    const totalInput = document.getElementById(
        `total-hrs-finalizar-${unidadNumero}`
    );

    if (inputDespierto && inputDuracion && totalInput) {
        const minutosDespierto = stringHoraAMinutos(inputDespierto.value);
        const minutosViaje = stringHoraAMinutos(inputDuracion.value);
        const totalMinutos = minutosDespierto + minutosViaje;
        const totalHorasStr = minutosAStringHora(totalMinutos);
        totalInput.value = totalHorasStr;

        if (totalMinutos > 900) {
            totalInput.style.backgroundColor = "#dc3545";
            totalInput.style.color = "#ffffff";
            if (!totalInput.dataset.warningShown) {
                Swal.fire({
                    title: "¡Advertencia de Horas!",
                    html: `La Unidad <strong>${unidadNumero}</strong> acumulará <strong>${totalHorasStr} horas</strong> totales.<br>Esto excede el límite recomendado de 15 horas.`,
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
                        `${municipio.name}, ${estado.name}`
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
// ====================================================================
function generarCodigoViaje(incrementar = false) {
    if (incrementar) codigoViaje++;
    const codigo = `N°:GV-${String(codigoViaje).padStart(3, "0")}`;
    const codigoElement = document.getElementById("codigoViaje");
    if (codigoElement) codigoElement.textContent = codigo;
}

function agregarUnidad() {
    if (contadorUnidades >= MAX_UNIDADES) {
        Swal.fire(
            "Límite alcanzado",
            `Solo puedes agregar hasta ${MAX_UNIDADES} unidades.`,
            "warning"
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
    return `
    <td>
        <div id="tipo-vehiculo-${numeroUnidad}" class="tipo-vehiculo-text"></div>
        <div class="input-wrapper" style="position: relative;">
            <input type="hidden" id="vehicle-hidden-${numeroUnidad}" name="unidad[${numeroUnidad}][vehiculo]" required>
            <div id="vehicle-trigger-${numeroUnidad}" class="vehicle-select-trigger">
                <span>Vehículo</span>
            </div>
        </div>
        <button type="button" class="btn-inspeccion" id="btn-inspeccion-${numeroUnidad}" data-aprobado="false" onclick="abrirInspeccion(${numeroUnidad})" title="Realizar Inspección Pre-Viaje">
            <i class="fas fa-clipboard"></i> Inspeccion
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
                <input type="text" class="table-input" id="presion-valor-${numeroUnidad}" name="unidad[${numeroUnidad}][presion_valor]" placeholder="Ej: 120/80" style="width: 70px;">
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
        <button type="button" class="btn-accion eliminar" onclick="eliminarUnidad(${numeroUnidad})" title="Eliminar unidad">
            <i class="fas fa-trash"></i>
        </button>
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
            `#btn-inspeccion-${nuevoNumero}`
        );
        if (btnInspeccion)
            btnInspeccion.setAttribute(
                "onclick",
                `abrirInspeccion(${nuevoNumero})`
            );

        const btnEliminar = fila.querySelector(".btn-accion.eliminar");
        if (btnEliminar)
            btnEliminar.setAttribute(
                "onclick",
                `eliminarUnidad(${nuevoNumero})`
            );

        const btnAddPasajero = fila.querySelector(
            `#btn-add-pasajero-${nuevoNumero}`
        );
        if (btnAddPasajero)
            btnAddPasajero.setAttribute(
                "onclick",
                `agregarPasajero(${nuevoNumero})`
            );

        const medicamentoSelect = fila.querySelector(
            `#medicamento-${nuevoNumero}`
        );
        if (medicamentoSelect) {
            medicamentoSelect.setAttribute(
                "onchange",
                `toggleMedicamentoDetalle(${nuevoNumero})`
            );
        }

        if (fila.querySelector(".unidad-hora-dormir")) {
            fila.querySelector(".unidad-hora-dormir").addEventListener(
                "change",
                function () {
                    calcularHorasDormidas(nuevoNumero);
                    calcularHorasViaje(nuevoNumero);
                    actualizarBotonReunionConvoy();
                }
            );
        }

        if (fila.querySelector(".unidad-hora-levantar")) {
            fila.querySelector(".unidad-hora-levantar").addEventListener(
                "change",
                function () {
                    calcularHorasDormidas(nuevoNumero);
                    calcularHorasViaje(nuevoNumero);
                    actualizarBotonReunionConvoy();
                }
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
                    `unidad[${nuevoNumero}]`
                );
                input.setAttribute("name", newName);
            }
        });

        const pasajeroContainer = fila.querySelector(
            `#pasajeros-unidad-${nuevoNumero}`
        );
        if (pasajeroContainer) {
            pasajeroContainer
            .querySelectorAll(".pasajero-input-group")
            .forEach((group, pIndex) => {
                const newIndex = pIndex + 1;
                const input = group.querySelector("input");
                const removeBtn = group.querySelector(".btn-remove-pasajero");

                group.id = `fila-p-${nuevoNumero}-${newIndex}`;
                group.dataset.index = newIndex;

                if (input) {
                    input.id = `p-nombre-${nuevoNumero}-${newIndex}`;
                    input.setAttribute(
                        "name",
                        `unidad[${nuevoNumero}][pasajeros][p${newIndex}][nombre]`
                    );
                }

                if (removeBtn) {
                    removeBtn.setAttribute(
                        "onclick",
                        `eliminarPasajero(${nuevoNumero}, ${newIndex})`
                    );
                }

                const icon = fila.querySelector(
                    `#p-icon-${nuevoNumero}-${numeroAnterior}`
                );
                if (icon) icon.id = `p-icon-${nuevoNumero}-${newIndex}`;

                const badge = fila.querySelector(
                    `#p-badge-${nuevoNumero}-${numeroAnterior}`
                );
                if (badge) badge.id = `p-badge-${nuevoNumero}-${newIndex}`;

                const autocomplete = fila.querySelector(
                    `#p-autocomplete-${nuevoNumero}-${numeroAnterior}`
                );
                if (autocomplete)
                    autocomplete.id = `p-autocomplete-${nuevoNumero}-${newIndex}`;

                inicializarAutocompleteConductor(nuevoNumero, true, newIndex);
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
        `medicamento-detalle-${unidadNumero}`
    );
    if (select && detalleDiv) {
        if (select.value === "si") {
            detalleDiv.style.display = "block";
        } else {
            detalleDiv.style.display = "none";
            const medicamentoNombre = document.getElementById(
                `medicamento-nombre-${unidadNumero}`
            );
            if (medicamentoNombre) medicamentoNombre.value = "";
        }
    }
}

// ====================================================================
// AUTOCOMPLETE DE CONDUCTORES
// ====================================================================
function inicializarAutocompleteConductor(
    unidadNumero,
    esPasajero = false,
    pasajeroIndex = null
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
        true
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
            return;
        }

        const filtered = conductoresGlobales.filter((c) =>
            c.toUpperCase().includes(val.toUpperCase())
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
                item.innerHTML = c;
                item.addEventListener("mousedown", function (e) {
                    e.preventDefault();
                    input.value = c;
                    input.dataset.conductorSeleccionado = c;
                    listContainer.innerHTML = "";
                    listContainer.style.display = "none";
                    if (!esPasajero) {
                        setTimeout(() => {
                            actualizarDatosConductor(unidadNumero, c);
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
                    (c) => c.toUpperCase() === input.value.toUpperCase()
                );
                if (exactMatch) {
                    input.value = exactMatch;
                    input.dataset.conductorSeleccionado = exactMatch;
                    listContainer.innerHTML = "";
                    listContainer.style.display = "none";
                    if (!esPasajero) {
                        actualizarDatosConductor(unidadNumero, exactMatch);
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
        `vigencia-lic-${unidadNumero}`
    );
    const inputVigenciaMan = document.getElementById(
        `vigencia-man-${unidadNumero}`
    );
    const tipoVehiculoDiv = document.getElementById(
        `tipo-vehiculo-${unidadNumero}`
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
            `conductor-${unidadNumero}`
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
        true
    );

    window.addEventListener("resize", () => {
        if (listContainer.style.display === "block") positionDropdown();
    });
}

function actualizarTipoVehiculoCustom(unidadNumero, valorVehiculo) {
    const labelTipo = document.getElementById(`tipo-vehiculo-${unidadNumero}`);
    const inputVigenciaLic = document.getElementById(
        `vigencia-lic-${unidadNumero}`
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
        `pasajeros-unidad-${unidadNumero}`
    );
    if (!container) return;

    const currentPasajeros = container.querySelectorAll(
        ".pasajero-input-group"
    ).length;
    if (currentPasajeros >= MAX_PASAJEROS) {
        Swal.fire(
            "Límite",
            `Solo se permiten ${MAX_PASAJEROS} pasajeros.`,
            "warning"
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

    div.innerHTML = `
        <div class="pasajero-nombre-container input-wrapper" style="flex-grow: 1;">
            <i class="fa-solid fa-user field-icon pasajero"
                id="p-icon-${unidadNumero}-${index}"
                title="Clic para asignar como Segundo Conductor"
                onclick="gestionarRolPasajero(${unidadNumero}, ${index})"></i>
            <input type="text" class="table-input has-icon"
                name="unidad[${unidadNumero}][pasajeros][p${index}][nombre]"
                id="p-nombre-${unidadNumero}-${index}"
                placeholder="Pasajero ${index}" required autocomplete="off">
            <i class="fas fa-star badge-principal" id="p-badge-${unidadNumero}-${index}" style="display:none; position: absolute; right: 5px; top: 10px;"></i>
            <div class="autocomplete-list" id="p-autocomplete-${unidadNumero}-${index}"></div>
        </div>
        <button type="button" class="btn-remove-pasajero" onclick="eliminarPasajero(${unidadNumero}, ${index})" style="margin-left: 5px;" title="Eliminar pasajero">
            <i class="fas fa-trash"></i>
        </button>
    `;

    container.appendChild(div);
    inicializarAutocompleteConductor(unidadNumero, true, index);
    actualizarBotonesPasajeros(unidadNumero);

    const btnAddPasajero = document.getElementById(
        `btn-add-pasajero-${unidadNumero}`
    );
    if (btnAddPasajero && currentPasajeros + 1 >= MAX_PASAJEROS) {
        btnAddPasajero.disabled = true;
    }
}

function eliminarPasajero(unidadNumero, pasajeroIndex) {
    const fila = document.getElementById(
        `fila-p-${unidadNumero}-${pasajeroIndex}`
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
        `pasajeros-unidad-${unidadNumero}`
    );
    if (container?.querySelectorAll(".pasajero-input-group").length <= 1)
        return;

    if (fila) fila.remove();

    const remainingPasajeros = container.querySelectorAll(
        ".pasajero-input-group"
    );
    remainingPasajeros.forEach((group, idx) => {
        const newIndex = idx + 1;
        const input = group.querySelector("input");
        const removeBtn = group.querySelector(".btn-remove-pasajero");

        if (input) input.placeholder = `Pasajero ${newIndex}`;
        if (removeBtn) {
            removeBtn.setAttribute(
                "onclick",
                `eliminarPasajero(${unidadNumero}, ${newIndex})`
            );
        }
    });

    if (remainingPasajeros.length < MAX_PASAJEROS) {
        const btnAdd = document.getElementById(
            `btn-add-pasajero-${unidadNumero}`
        );
        if (btnAdd) btnAdd.disabled = false;
    }

    actualizarBotonVerConductor2(unidadNumero);
}

function gestionarRolPasajero(unidad, index) {
    const fila = document.getElementById(`fila-p-${unidad}-${index}`);
    if (!fila) return;

    const nombre = document.getElementById(
        `p-nombre-${unidad}-${index}`
    )?.value;
    if (!nombre) {
        Swal.fire(
            "Falta Nombre",
            "Escriba el nombre del pasajero antes de asignarle un rol.",
            "info"
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
                    "success"
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
        horaFinViaje
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
                "swal-medicamento-detalle-container"
            );
            if (medicamentoSelect && medicamentoDetalle) {
                medicamentoSelect.addEventListener("change", function () {
                    if (this.value === "si") {
                        medicamentoDetalle.style.display = "flex";
                    } else {
                        medicamentoDetalle.style.display = "none";
                        document.getElementById(
                            "swal-medicamento-nombre"
                        ).value = "";
                    }
                });
            }

            const nombreConductor = document.getElementById(
                `p-nombre-${unidad}-${index}`
            ).value;
            const data = datosConductoresGlobales[nombreConductor];
            const licInput = document.getElementById("swal-vigencia-lic");
            const manInput = document.getElementById("swal-vigencia-man");
            const licStatus = document.getElementById("swal-hidden-lic-status");
            const manStatus = document.getElementById("swal-hidden-man-status");

            if (data) {
                const validacion = validarDocumentacionConductor(
                    data,
                    esPesada
                );
                licInput.value = validacion.licencia.mensaje;
                licStatus.value = validacion.licencia.estilo;
                aplicarEstiloPorValidacion(
                    licInput,
                    validacion.licencia.estilo
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
                "swal-duracion-viaje"
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
                        totalMinutosLevantar - totalMinutosDormir
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
                    hrDespiertoInput.value
                );
                const minutosViaje = stringHoraAMinutos(
                    duracionViajeInput.value
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
                "swal-medicamento-nombre"
            ).value;

            if (!alcoholPct) {
                Swal.showValidationMessage(
                    "Ingrese el porcentaje de alcoholimetría"
                );
                return false;
            }
            if (parseFloat(alcoholPct) > 0.4) {
                Swal.showValidationMessage("Alcoholimetría no apta (> 0.4)");
                return false;
            }
            if (!dormir || !levantar) {
                Swal.showValidationMessage(
                    "Complete las horas de dormir y despertar"
                );
                return false;
            }
            if (!medicamento) {
                Swal.showValidationMessage("Seleccione si toma medicamento");
                return false;
            }
            if (medicamento === "si" && !medicamentoNombre) {
                Swal.showValidationMessage(
                    "Especifique el nombre del medicamento"
                );
                return false;
            }

            const licStatus = document.getElementById(
                "swal-hidden-lic-status"
            ).value;
            const manStatus = document.getElementById(
                "swal-hidden-man-status"
            ).value;
            const erroresDocs = [];

            if (licStatus === "expired" || licStatus === "missing") {
                erroresDocs.push(
                    `${esPesada ? "Licencia Federal" : "Licencia"} (${
                        licStatus === "expired" ? "VENCIDA" : "NO REGISTRADA"
                    })`
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
                    })`
                );
            }

            if (erroresDocs.length > 0) {
                Swal.showValidationMessage(
                    `NO SE PUEDE ASIGNAR:<br>• ${erroresDocs.join("<br>• ")}`
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
        `pasajeros-unidad-${unidadNumero}`
    );
    const filaRelevo = container?.querySelector(
        ".pasajero-input-group.es-relevo"
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
        `tipo-vehiculo-${unidadNumero}`
    );
    const esPesada =
        tipoVehiculoDiv?.textContent.includes("Pesada") ||
        tipoVehiculoDiv?.classList.contains("tipo-pesada") ||
        false;

    const pIdx = filaRelevo.dataset.index;
    const pNombre = document.getElementById(
        `p-nombre-${unidadNumero}-${pIdx}`
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
            pMedicamento === "si" ? `Sí (${pMedicamentoNombre})` : "No"
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
        `pasajeros-unidad-${unidadNumero}`
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
        `pasajeros-unidad-${unidadNumero}`
    );
    if (!container) return;

    const grupos = container.querySelectorAll(".pasajero-input-group");
    grupos.forEach((grupo, index) => {
        const btn = grupo.querySelector(".btn-remove-pasajero");
        if (!btn) return;

        if (grupos.length === 1) {
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
    const hiddenInput = document.getElementById(
        `vehicle-hidden-${unidadNumero}`
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

function gestionarModalInspeccionLigera(abrir, unidadNumero = null) {
    const modal = document.getElementById("modalInspeccionLigera");

    if (abrir && unidadNumero !== null) {
        unidadEnInspeccion = unidadNumero;
        tipoVehiculoInspeccion = "ligera";

        fotosSubidas.ligera = [];
        actualizarVistaPrevia("ligera");

        const hiddenInput = document.getElementById(
            `vehicle-hidden-${unidadNumero}`
        );
        const nombreVehiculo = hiddenInput?.value || "Unidad " + unidadNumero;
        const inputNoEconomicoLigera = document.getElementById(
            "inputNoEconomicoLigera"
        );
        if (inputNoEconomicoLigera)
            inputNoEconomicoLigera.value = nombreVehiculo;

        const inspeccionUnidadIndexLigera = document.getElementById(
            "inspeccionUnidadIndexLigera"
        );
        if (inspeccionUnidadIndexLigera)
            inspeccionUnidadIndexLigera.value = unidadNumero;

        const savedData = datosInspeccionLigera[unidadNumero] || {};
        const items = [
            "docs",
            "llantas",
            "luces",
            "extintor",
            "botiquin",
            "kit",
            "fluidos",
            "frenos",
        ];

        items.forEach((item) => {
            const radio = document.querySelector(
                `input[name="inspeccion_${item}"][value="${savedData[item]}"]`
            );
            if (radio) radio.checked = true;
        });

        const comentariosInput = document.getElementById(
            "comentariosInspeccionLigera"
        );
        if (comentariosInput && savedData.comentarios) {
            comentariosInput.value = savedData.comentarios;
        }

        if (modal) {
            modal.classList.add("active");
            document.body.style.overflow = "hidden";
        }
    } else if (!abrir) {
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

function gestionarModalInspeccionPesada(abrir, unidadNumero = null) {
    const modal = document.getElementById("modalInspeccionPesada");

    if (abrir && unidadNumero !== null) {
        unidadEnInspeccion = unidadNumero;
        tipoVehiculoInspeccion = "pesada";

        fotosSubidas.pesada = [];
        actualizarVistaPrevia("pesada");

        const hiddenInput = document.getElementById(
            `vehicle-hidden-${unidadNumero}`
        );
        const nombreVehiculo = hiddenInput?.value || "Unidad " + unidadNumero;
        const inputNoEconomicoPesada = document.getElementById(
            "inputNoEconomicoPesada"
        );
        if (inputNoEconomicoPesada)
            inputNoEconomicoPesada.value = nombreVehiculo;

        const inspeccionUnidadIndexPesada = document.getElementById(
            "inspeccionUnidadIndexPesada"
        );
        if (inspeccionUnidadIndexPesada)
            inspeccionUnidadIndexPesada.value = unidadNumero;

        const savedData = datosInspeccionPesada[unidadNumero] || {};
        const items = [
            "docs_p",
            "llantas_p",
            "luces_p",
            "extintor_p",
            "botiquin_p",
            "kit_p",
            "fluidos_p",
            "frenos_p",
            "acople_p",
            "senalizacion_p",
        ];

        items.forEach((item) => {
            const radio = document.querySelector(
                `input[name="inspeccion_${item}"][value="${savedData[item]}"]`
            );
            if (radio) radio.checked = true;
        });

        const comentariosInput = document.getElementById(
            "comentariosInspeccionPesada"
        );
        if (comentariosInput && savedData.comentarios) {
            comentariosInput.value = savedData.comentarios;
        }

        if (modal) {
            modal.classList.add("active");
            document.body.style.overflow = "hidden";
        }
    } else if (!abrir) {
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

function guardarInspeccionLigera() {
    const comentarios =
        document.getElementById("comentariosInspeccionLigera")?.value.trim() ||
        "";

    if (comentarios && fotosSubidas.ligera.length === 0) {
        Swal.fire({
            title: "¿Sin evidencia fotográfica?",
            text: "Has reportado anomalías pero no has agregado fotos. ¿Deseas continuar sin evidencia?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Continuar sin fotos",
            cancelButtonText: "Agregar fotos",
            confirmButtonColor: "#0056b3",
            cancelButtonColor: "#dc3545",
        }).then((result) => {
            if (result.isConfirmed) procederGuardadoLigera(comentarios);
        });
    } else {
        procederGuardadoLigera(comentarios);
    }
}

function procederGuardadoLigera(comentarios) {
    const form = document.getElementById("formInspeccionLigera");
    const unidadNumero = parseInt(
        document.getElementById("inspeccionUnidadIndexLigera")?.value
    );
    const items = [
        "docs",
        "llantas",
        "luces",
        "extintor",
        "botiquin",
        "kit",
        "fluidos",
        "frenos",
    ];

    let formValido = true;
    items.forEach((item) => {
        if (!form.querySelector(`input[name="inspeccion_${item}"]:checked`))
            formValido = false;
    });

    if (!formValido) {
        Swal.fire({
            title: "Inspección Incompleta",
            text: "Debe responder sí o no a todos los elementos de la inspección.",
            icon: "warning",
            confirmButtonColor: "#0056b3",
        });
        return;
    }

    const data = {};
    let todoAprobado = true;
    items.forEach((item) => {
        const value = form.querySelector(
            `input[name="inspeccion_${item}"]:checked`
        ).value;
        data[item] = value;
        if (value === "no") todoAprobado = false;
    });

    data.comentarios = comentarios;
    data.fotos = fotosSubidas.ligera.length;
    data.fotosArray = fotosSubidas.ligera.map((foto) => ({
        nombre: foto.name,
        tamaño: foto.size,
        tipo: foto.type,
    }));

    datosInspeccionLigera[unidadNumero] = data;

    const btnInspeccion = document.getElementById(
        `btn-inspeccion-${unidadNumero}`
    );
    if (btnInspeccion) {
        if (todoAprobado) {
            btnInspeccion.classList.add(
                "btn-submit",
                "btn-inspeccion-aprobado"
            );
            btnInspeccion.classList.remove("btn-inspeccion");
            btnInspeccion.innerHTML = '<i class="fas fa-check-circle"></i>';
            btnInspeccion.dataset.aprobado = "true";
            btnInspeccion.title = "Inspección Aprobada";
        } else {
            btnInspeccion.classList.add("btn-inspeccion");
            btnInspeccion.classList.remove(
                "btn-submit",
                "btn-inspeccion-aprobado"
            );
            btnInspeccion.innerHTML =
                '<i class="fas fa-exclamation-triangle"></i>';
            btnInspeccion.dataset.aprobado = "false";
            btnInspeccion.title = "Revisar Inspección";
        }
    }

    Swal.fire({
        title: "Inspección Guardada",
        html: `La inspección para la Unidad ${unidadNumero} ha sido registrada.<br>
               <small>Fotos adjuntas: ${
                   fotosSubidas.ligera.length
               } | Comentarios: ${comentarios ? "Sí" : "No"}</small>`,
        icon: "success",
        confirmButtonColor: "#0056b3",
    }).then(() => {
        gestionarModalInspeccionLigera(false);
        actualizarBotonReunionConvoy();
        actualizarBotonEvaluacion();
    });
}

function guardarInspeccionPesada() {
    const comentarios =
        document.getElementById("comentariosInspeccionPesada")?.value.trim() ||
        "";

    if (comentarios && fotosSubidas.pesada.length === 0) {
        Swal.fire({
            title: "¿Sin evidencia fotográfica?",
            text: "Has reportado anomalías pero no has agregado fotos. ¿Deseas continuar sin evidencia?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Continuar sin fotos",
            cancelButtonText: "Agregar fotos",
            confirmButtonColor: "#0056b3",
            cancelButtonColor: "#dc3545",
        }).then((result) => {
            if (result.isConfirmed) procederGuardadoPesada(comentarios);
        });
    } else {
        procederGuardadoPesada(comentarios);
    }
}

function procederGuardadoPesada(comentarios) {
    const form = document.getElementById("formInspeccionPesada");
    const unidadNumero = parseInt(
        document.getElementById("inspeccionUnidadIndexPesada")?.value
    );
    const items = [
        "docs_p",
        "llantas_p",
        "luces_p",
        "extintor_p",
        "botiquin_p",
        "kit_p",
        "fluidos_p",
        "frenos_p",
        "acople_p",
        "senalizacion_p",
    ];

    let formValido = true;
    items.forEach((item) => {
        if (!form.querySelector(`input[name="inspeccion_${item}"]:checked`))
            formValido = false;
    });

    if (!formValido) {
        Swal.fire({
            title: "Inspección Incompleta",
            text: "Debe responder sí o no a todos los elementos de la inspección.",
            icon: "warning",
            confirmButtonColor: "#0056b3",
        });
        return;
    }

    const data = {};
    let todoAprobado = true;
    items.forEach((item) => {
        const value = form.querySelector(
            `input[name="inspeccion_${item}"]:checked`
        ).value;
        data[item] = value;
        if (value === "no") todoAprobado = false;
    });

    data.comentarios = comentarios;
    data.fotos = fotosSubidas.pesada.length;
    data.fotosArray = fotosSubidas.pesada.map((foto) => ({
        nombre: foto.name,
        tamaño: foto.size,
        tipo: foto.type,
    }));

    datosInspeccionPesada[unidadNumero] = data;

    const btnInspeccion = document.getElementById(
        `btn-inspeccion-${unidadNumero}`
    );
    if (btnInspeccion) {
        if (todoAprobado) {
            btnInspeccion.classList.add(
                "btn-submit",
                "btn-inspeccion-aprobado"
            );
            btnInspeccion.classList.remove("btn-inspeccion");
            btnInspeccion.innerHTML = '<i class="fas fa-check-circle"></i>';
            btnInspeccion.dataset.aprobado = "true";
            btnInspeccion.title = "Inspección Aprobada";
        } else {
            btnInspeccion.classList.add("btn-inspeccion");
            btnInspeccion.classList.remove(
                "btn-submit",
                "btn-inspeccion-aprobado"
            );
            btnInspeccion.innerHTML =
                '<i class="fas fa-exclamation-triangle"></i>';
            btnInspeccion.dataset.aprobado = "false";
            btnInspeccion.title = "Revisar Inspección";
        }
    }

    Swal.fire({
        title: "Inspección Guardada",
        html: `La inspección para la Unidad ${unidadNumero} ha sido registrada.<br>
               <small>Fotos adjuntas: ${
                   fotosSubidas.pesada.length
               } | Comentarios: ${comentarios ? "Sí" : "No"}</small>`,
        icon: "success",
        confirmButtonColor: "#0056b3",
    }).then(() => {
        gestionarModalInspeccionPesada(false);
        actualizarBotonReunionConvoy();
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
            `evidenciaInspeccion${tipo.charAt(0).toUpperCase() + tipo.slice(1)}`
        );
        const dropZone = document.getElementById(
            `dropZone${tipo.charAt(0).toUpperCase() + tipo.slice(1)}`
        );

        if (input && dropZone) {
            input.addEventListener("change", (e) =>
                manejarSeleccionArchivos(e, tipo)
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
            file.name.toLowerCase().endsWith(".pdf")
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
            (foto) => foto.name === file.name && foto.size === file.size
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
        `previewContainer${tipoCapitalizado}`
    );
    const placeholder = document.getElementById(
        `placeholder${tipoCapitalizado}`
    );
    const grid = document.getElementById(`previewGrid${tipoCapitalizado}`);
    const countSpan = document.getElementById(
        `previewCount${tipoCapitalizado}`
    );
    const input = document.getElementById(
        `evidenciaInspeccion${tipoCapitalizado}`
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
                }`
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

function abrirCamara(tipo) {
    tipoActualCamara = tipo;
    const modal = document.getElementById("modalCamara");
    const video = document.getElementById("videoCamara");

    if (!modal || !video) return;

    modal.classList.add("active");
    document.body.style.overflow = "hidden";

    navigator.mediaDevices
    .getUserMedia({
        video: {
            facingMode: "environment",
            width: { ideal: 1280 },
            height: { ideal: 720 },
        },
        audio: false,
    })
    .then((stream) => {
        streamCamara = stream;
        video.srcObject = stream;
        video.play();
    })
    .catch((error) => {
        console.error("Error al acceder a la cámara:", error);
        Swal.fire({
            title: "Error de cámara",
            text: "No se pudo acceder a la cámara. Asegúrate de dar permisos.",
            icon: "error",
            confirmButtonColor: "#0056b3",
        }).then(() => cerrarCamara());
    });
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

    if (video) video.srcObject = null;
    tipoActualCamara = null;
}

function capturarFoto() {
    const video = document.getElementById("videoCamara");
    const canvas = document.getElementById("canvasCamara");

    if (!video || !canvas || !tipoActualCamara) return;

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext("2d").drawImage(video, 0, 0, canvas.width, canvas.height);

    canvas.toBlob(
        (blob) => {
            if (!blob) return;

            const file = new File([blob], `foto_camara_${Date.now()}.jpg`, {
                type: "image/jpeg",
                lastModified: Date.now(),
            });

            agregarFotoALista(file, tipoActualCamara);
            actualizarVistaPrevia(tipoActualCamara);

            Swal.fire({
                title: "¡Foto tomada!",
                text: "La foto se ha agregado a la lista.",
                icon: "success",
                timer: 1500,
                showConfirmButton: false,
            });
        },
        "image/jpeg",
        0.9
    );
}

function limpiarFotosAlCerrarModal(tipo) {
    Swal.fire({
        title: "¿Desea guardar las fotos?",
        text: "Las fotos no se han guardado aún. ¿Desea conservarlas o eliminarlas?",
        icon: "question",
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonText: "Guardar y salir",
        denyButtonText: "Eliminar y salir",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isDenied) {
            fotosSubidas[tipo] = [];
            const input = document.getElementById(
                `evidenciaInspeccion${
                    tipo.charAt(0).toUpperCase() + tipo.slice(1)
                }`
            );
            if (input) input.value = "";
            actualizarVistaPrevia(tipo);
            if (tipo === "ligera") {
                gestionarModalInspeccionLigera(false);
            } else {
                gestionarModalInspeccionPesada(false);
            }
        } else if (result.isConfirmed) {
            if (tipo === "ligera") {
                gestionarModalInspeccionLigera(false);
            } else {
                gestionarModalInspeccionPesada(false);
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

function gestionarModalEvaluacion(abrir) {
    const modal = document.getElementById("modalEvaluacion");
    if (!modal) return;

    if (abrir) {
        modal.classList.add("active");
        document.body.style.overflow = "hidden";
    } else {
        modal.classList.remove("active");
        document.body.style.overflow = "auto";
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
    let iconoSwal = "success";
    let btnConfirmColor = "";
    let iconoBadge = "";
    let mensajeAdicional = "";

    if (totalPuntos <= 55) {
        nivelRiesgoTexto = "Bajo Riesgo";
        cssClassBtn = "btn-riesgo-bajo";
        iconoSwal = "success";
        btnConfirmColor = "#28a745";
        iconoBadge = '<i class="fas fa-check-circle"></i>';
        mensajeAdicional = "El viaje puede proceder normalmente.";
    } else if (totalPuntos <= 105) {
        nivelRiesgoTexto = "Riesgo Medio";
        cssClassBtn = "btn-riesgo-medio";
        iconoSwal = "warning";
        btnConfirmColor = "#ffc107";
        iconoBadge = '<i class="fas fa-exclamation-circle"></i>';
        mensajeAdicional = "Se requieren precauciones adicionales.";
    } else if (totalPuntos <= 145) {
        nivelRiesgoTexto = "Alto Riesgo";
        cssClassBtn = "btn-riesgo-alto";
        iconoSwal = "warning";
        btnConfirmColor = "#fd7e14";
        iconoBadge = '<i class="fas fa-exclamation-triangle"></i>';
        mensajeAdicional =
            "Se requiere aprobación especial y medidas de seguridad.";
    } else {
        nivelRiesgoTexto = "Muy Alto Riesgo";
        cssClassBtn = "btn-riesgo-muy-alto";
        iconoSwal = "error";
        btnConfirmColor = "#dc3545";
        iconoBadge = '<i class="fas fa-ban"></i>';
        mensajeAdicional =
            "Se recomienda mucha precaución. Requiere autorización de gerencia.";
    }

    puntajeRiesgoTotal = totalPuntos;
    evaluacionRiesgoGuardada = true;

    const btn = document.getElementById("btnEvaluacionRiesgo");
    if (btn) {
        btn.innerHTML = `<i class="fas fa-check-circle"></i> Evaluación: ${nivelRiesgoTexto} <br><small>(Clic para editar)</small>`;
        btn.classList.remove(
            "btn-riesgo-bajo",
            "btn-riesgo-medio",
            "btn-riesgo-alto",
            "btn-riesgo-muy-alto"
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
                <div class="riesgo-badge ${cssClassBtn.replace(
                    "btn-",
                    "status-"
                )}">
                    ${iconoBadge} ${nivelRiesgoTexto}
                </div>
                <div class="resultado-mensaje">${mensajeAdicional}</div>
                <div class="resultado-puntaje">Puntaje total: <strong>${totalPuntos}</strong></div>
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

    let unidadesCompletas = true;
    for (let i = 1; i <= contadorUnidades; i++) {
        const fila = document.getElementById(`unidad-${i}`);
        if (!fila) {
            unidadesCompletas = false;
            break;
        }

        const requiredSelectors = [
            `.unidad-conductor`,
            `#vigencia-lic-${i}`,
            `#vigencia-man-${i}`,
            `.unidad-alcoholimetria`,
            `#dormir-${i}`,
            `#levantar-${i}`,
            `.unidad-vehiculo`,
        ];

        for (const selector of requiredSelectors) {
            const input = fila.querySelector(selector);
            if (input && input.value.trim() === "") {
                unidadesCompletas = false;
                break;
            }
        }
        if (!unidadesCompletas) break;

        const pasajeros = fila.querySelectorAll(".pasajero-input-group input");
        for (const input of pasajeros) {
            if (input.value.trim() === "") {
                unidadesCompletas = false;
                break;
            }
        }
        if (!unidadesCompletas) break;
    }

    if (unidadesCompletas) {
        btn.disabled = false;
        btn.title = "Realizar o ver la reunión antes de enviar la solicitud.";
        if (reunionPreConvoyGuardada) {
            btn.classList.add("btn-secondary-convoy-completed");
            btn.classList.remove("btn-secondary-convoy", "btn-submit");
            btn.innerHTML =
                '<i class="fas fa-check-circle"></i> Reunión Confirmada';
        } else {
            btn.classList.add("btn-secondary-convoy");
            btn.classList.remove(
                "btn-submit",
                "btn-secondary-convoy-completed"
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
            "Complete todos los campos de Conductor y Unidad antes de realizar la Reunión.";
        reunionPreConvoyGuardada = false;
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
                option.value = c;
                option.textContent = c;
                if (datosReunionConvoy.lider_convoy === c)
                    option.selected = true;
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
                    `input[name="checklist_${item}"][value="${savedValue}"]`
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
        if (inputConductor?.value.trim()) {
            conductoresList.push(inputConductor.value.trim());
        }
    }
    return conductoresList;
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
    const data = { lider_convoy: liderSelect.value };

    checklistItems.forEach((item) => {
        const checked = form.querySelector(
            `input[name="checklist_${item}"]:checked`
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
        if (key !== "lider_convoy" && data[key] === "no") {
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
                'input[name="tiene_paradas"][value="no"]'
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
                `eliminarParada(${contadorGlobal})`
            );
            btnEliminar.setAttribute(
                "title",
                `Eliminar Parada ${contadorGlobal}`
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
    generarCodigoViaje(false);

    Object.keys(datosInspeccionLigera).forEach(
        (key) => delete datosInspeccionLigera[key]
    );
    Object.keys(datosInspeccionPesada).forEach(
        (key) => delete datosInspeccionPesada[key]
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
            "btn-riesgo-muy-alto"
        );
        btnEval.innerHTML =
            '<i class="fas fa-list-check"></i> Realizar Evaluación del Viaje (Requerido)';
    }

    const formEvaluacionRiesgo = document.getElementById(
        "formEvaluacionRiesgo"
    );
    if (formEvaluacionRiesgo) formEvaluacionRiesgo.reset();

    actualizarBotonEvaluacion();
}

function actualizarBotonEnviarSolicitud() {
    const btnEnviar = document.getElementById("btnEnviarSolicitud");
    if (!btnEnviar) return;

    if (!evaluacionRiesgoGuardada) {
        btnEnviar.disabled = true;
        btnEnviar.title = "Debe realizar la Evaluación del Viaje.";
        return;
    }

    if (contadorUnidades <= 1) {
        btnEnviar.disabled = false;
        btnEnviar.title = "Enviar Solicitud";
    } else {
        btnEnviar.disabled = !reunionPreConvoyGuardada;
        btnEnviar.title = reunionPreConvoyGuardada
            ? "Enviar Solicitud"
            : "Debe completar y confirmar la Reunión Pre-convoy.";
    }
}

function enviarSolicitud() {
    const form = document.getElementById("formViaje");
    if (!form.checkValidity()) {
        form.reportValidity();
        Swal.fire({
            title: "Faltan Campos",
            text: "Por favor, llene todos los campos requeridos en la sección de Información General y Detalles del Trayecto.",
            icon: "warning",
            confirmButtonColor: "#0056b3",
        });
        return;
    }

    const destinoSelector = document.getElementById("destinoPredefinido");
    const destinoEspecifico = document.getElementById("destinoEspecifico");

    if (
        destinoSelector.value === "" ||
        (destinoSelector.value === "Otro" && !destinoEspecifico.value.trim())
    ) {
        Swal.fire({
            title: "Destino no especificado",
            text: "Por favor, seleccione un destino de la lista o especifique uno en el campo de texto.",
            icon: "warning",
            confirmButtonColor: "#0056b3",
        });
        (destinoSelector.value === ""
            ? destinoSelector
            : destinoEspecifico
        ).focus();
        return;
    }

    const tieneParadas = document.querySelector(
        'input[name="tiene_paradas"]:checked'
    )?.value;
    if (tieneParadas === "si") {
        const listaParadas = document.getElementById("listaParadas");
        if (listaParadas.children.length === 0) {
            Swal.fire({
                title: "Paradas Requeridas",
                text: "Indicó que realizaría paradas, pero no ha agregado ninguna.",
                icon: "warning",
                confirmButtonColor: "#0056b3",
            });
            return;
        }

        let paradasCompletas = true;
        listaParadas.querySelectorAll("input, select").forEach((input) => {
            if (!input.value.trim()) paradasCompletas = false;
        });

        if (!paradasCompletas) {
            Swal.fire({
                title: "Datos de Paradas Incompletos",
                text: "Por favor, complete todos los campos de Propósito y Ubicación para las paradas agregadas.",
                icon: "warning",
                confirmButtonColor: "#0056b3",
            });
            return;
        }
    }

    if (contadorUnidades === 0) {
        Swal.fire({
            title: "Sin unidades",
            text: "Debe agregar al menos una unidad vehicular y su conductor.",
            icon: "warning",
            confirmButtonColor: "#0056b3",
        });
        return;
    }

    let unidadesCompletas = true;
    let inspeccionesAprobadas = true;
    let unidadConProblema = 0;

    for (let i = 1; i <= contadorUnidades; i++) {
        const btnInspeccion = document.getElementById(`btn-inspeccion-${i}`);
        if (!btnInspeccion || btnInspeccion.dataset.aprobado !== "true") {
            inspeccionesAprobadas = false;
            unidadConProblema = i;
        }

        const fila = document.getElementById(`unidad-${i}`);
        if (fila) {
            fila.querySelectorAll("input[required], select[required]").forEach(
                (input) => {
                    if (!input.value.trim()) {
                        unidadesCompletas = false;
                        unidadConProblema = i;
                    }
                }
            );

            const medicamentoSelect = document.getElementById(
                `medicamento-${i}`
            );
            const medicamentoNombre = document.getElementById(
                `medicamento-nombre-${i}`
            );
            if (
                medicamentoSelect?.value === "si" &&
                (!medicamentoNombre || !medicamentoNombre.value.trim())
            ) {
                unidadesCompletas = false;
                unidadConProblema = i;
            }
        }

        const pasajeroContainer = document.getElementById(
            `pasajeros-unidad-${i}`
        );
        if (pasajeroContainer) {
            pasajeroContainer
            .querySelectorAll("input[required]")
            .forEach((input) => {
                if (!input.value.trim()) {
                    unidadesCompletas = false;
                    unidadConProblema = i;
                }
            });
        }

        if (!inspeccionesAprobadas || !unidadesCompletas) break;
    }

    if (!unidadesCompletas) {
        Swal.fire({
            title: "Información de Unidades Incompleta",
            html: `Asegúrese de **seleccionar un conductor de la lista** y llenar todos los campos requeridos de la Unidad **#${unidadConProblema}**.`,
            icon: "warning",
            confirmButtonColor: "#0056b3",
        });
        return;
    }

    if (!inspeccionesAprobadas) {
        Swal.fire({
            title: "Inspección Pendiente/Incompleta",
            text: `La Unidad #${unidadConProblema} debe tener la inspección vehicular realizada y **aprobada** antes de enviar la solicitud.`,
            icon: "error",
            confirmButtonColor: "#0056b3",
        });
        return;
    }

    if (contadorUnidades > 1 && !reunionPreConvoyGuardada) {
        Swal.fire({
            title: "Reunión Pre-Convoy Requerida",
            text: "Al ser un Convoy, debe realizar y confirmar la Reunión Pre-convoy antes de enviar la solicitud.",
            icon: "error",
            confirmButtonColor: "#0056b3",
        });
        return;
    }

    Swal.fire({
        title: "¿Enviar solicitud?",
        text: "La solicitud será enviada para su aprobación.",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, enviar",
        cancelButtonText: "Revisar",
        confirmButtonColor: "#0056b3",
        cancelButtonColor: "#6c757d",
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: "¡Solicitud enviada!",
                html: `<p>Tu solicitud <strong>${
                    document.getElementById("codigoViaje")?.textContent || ""
                }</strong> ha sido enviada exitosamente.</p>`,
                icon: "success",
                confirmButtonText: "Aceptar",
                confirmButtonColor: "#0056b3",
            }).then(() => {
                generarCodigoViaje(true);
                document
                .getElementById("modalFormulario")
                ?.classList.remove("active");
                document.body.style.overflow = "auto";
                limpiarFormulario();
            });
        }
    });
}
