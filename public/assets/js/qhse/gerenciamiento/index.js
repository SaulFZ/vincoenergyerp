// ====================================================================
// VARIABLES Y CONFIGURACIÓN GLOBAL
// ====================================================================
let codigoViaje = 4;
let contadorUnidades = 0;
const MAX_UNIDADES = 5;
const MAX_PASAJEROS = 4;
const MAX_PARADAS = 4;
// Almacenamiento temporal
const datosInspeccionLigera = {};
const datosInspeccionPesada = {};
let datosReunionConvoy = {};
let reunionPreConvoyGuardada = false;
let contadorParadas = 0;
let evaluacionRiesgoGuardada = false;
let puntajeRiesgoTotal = 0;
// 🚀 CLASIFICACIÓN DE VEHÍCULOS (Se cargará dinámicamente)
let clasificacionVehiculos = {};
let vehiculosLigeros = [];
let vehiculosPesados = [];
let optionsLigeras = "";
let optionsPesadas = "";
// Variable global para almacenar conductores desde la base de datos
let conductoresGlobales = [];
let datosConductoresGlobales = {};
// 🕒 CONFIGURACIÓN HORA 24HR
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
let datosDestinosGlobal = [];

// SINGLE DOMContentLoaded to avoid multiple firings and null errors
document.addEventListener("DOMContentLoaded", async function () {
    // 1. Cargar datos al iniciar (merged all calls)
    await cargarVehiculosDesdeBD();
    await cargarConductoresDesdeBD();
    await obtenerDestinosBackend();

    // 2. Establecer fecha actual (from one of the blocks)
    const hoy = new Date();
    const fechaDisplay = hoy.toLocaleDateString("es-ES", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
    const fechaHidden = hoy.toISOString().split("T")[0];

    // 3. Inicializar flatpickr para fechas y horas (merged)
    inicializarFlatpickr();

    // 4. Asignar eventos a los flatpickr para calcular horas automáticamente
    const horaInicioViaje = document.getElementById("horaInicioViaje");
    if (horaInicioViaje) {
        horaInicioViaje.addEventListener("change", function () {
            console.log("Hora inicio cambio:", this.value);
            calcularHorasViajeParaTodasUnidades();
            actualizarBotonReunionConvoy();
        });
    }
    const horaFinViaje = document.getElementById("horaFinViaje");
    if (horaFinViaje) {
        horaFinViaje.addEventListener("change", function () {
            console.log("Hora fin cambio:", this.value);
            calcularHorasViajeParaTodasUnidades();
            actualizarBotonReunionConvoy();
        });
    }

    // 5. Cerrar menú si se hace clic fuera (merged)
    document.addEventListener("click", function (e) {
        const wrapper = document.getElementById("wrapperDestino");
        if (wrapper && !wrapper.contains(e.target)) {
            wrapper.classList.remove("open");
        }
    });

    // 6. Configurar navegación
    document.querySelectorAll(".nav-link-viajes").forEach((link) => {
        link.addEventListener("click", function (e) {
            e.preventDefault();
            document.querySelectorAll(".nav-link-viajes").forEach((item) => {
                item.classList.remove("active");
            });
            this.classList.add("active");
        });
    });

    // 7. Manejo de destino
    const destinoSelector = document.getElementById("destinoPredefinido");
    const destinoEspecifico = document.getElementById("destinoEspecifico");
    if (destinoSelector && destinoEspecifico) {
        destinoSelector.addEventListener("change", function () {
            if (this.value === "Otro") {
                destinoEspecifico.required = true;
                destinoEspecifico.style.display = "block";
            } else {
                destinoEspecifico.required = false;
                destinoEspecifico.style.display = "none";
            }
        });
        if (destinoSelector.value !== "Otro") {
            destinoEspecifico.required = false;
            destinoEspecifico.style.display = "none";
        }
    }

    // 8. Eventos del formulario
    const formViaje = document.getElementById("formViaje");
    if (formViaje) {
        formViaje.addEventListener("submit", function (e) {
            e.preventDefault();
            enviarSolicitud();
        });
    }

    // 9. Generar código de viaje inicial
    generarCodigoViaje(false);

    // 10. Actualizar estados iniciales
    actualizarLabelTipoUnidad();
    actualizarBotonReunionConvoy();
    actualizarBotonEvaluacion();
});

// Función para abrir/cerrar el menú
function toggleMenuDestino() {
    const wrapper = document.getElementById("wrapperDestino");
    if (wrapper) wrapper.classList.toggle("open");
}

// Función Fetch datos
async function obtenerDestinosBackend() {
    const lista = document.getElementById("listaOpcionesDestino");
    if (!lista) return; // Null check
    try {
        const response = await fetch("/qhse/gerenciamiento/get-destinations");
        const data = await response.json();
        if (data.success) {
            datosDestinosGlobal = data.data;
            construirMenuDestinos();
        } else {
            lista.innerHTML =
                '<div style="padding:10px; color:red;">Error al cargar datos</div>';
        }
    } catch (error) {
        console.error(error);
        lista.innerHTML =
            '<div style="padding:10px; color:red;">Error de conexión</div>';
    }
}

// Función Renderizar (Dibujar HTML)
function construirMenuDestinos() {
    const lista = document.getElementById("listaOpcionesDestino");
    if (!lista) return; // Null check
    lista.innerHTML = ""; // Limpiar
    // 1. Agregar opción "Otro" al principio o final (aquí lo pongo al final por estándar)
    // Recorremos los estados
    datosDestinosGlobal.forEach((estado) => {
        // Contenedor del grupo (Estado)
        const grupoDiv = document.createElement("div");
        grupoDiv.className = "option-group";
        // Cabecera del Estado
        const header = document.createElement("div");
        header.className = "option-group-header";
        header.innerHTML = `
            <span>${estado.name}</span>
            <i class="fas fa-chevron-right"></i>
        `;
        // Evento: Expandir/Colapsar Estado
        header.onclick = (e) => {
            e.stopPropagation(); // No cerrar menú principal
            // Cerrar otros abiertos (Acordeón estricto)
            document.querySelectorAll(".option-group").forEach((el) => {
                if (el !== grupoDiv) {
                    el.classList.remove("active");
                    const icon = el.querySelector(".option-group-header i");
                    if (icon) icon.className = "fas fa-chevron-right";
                }
            });
            // Toggle actual
            grupoDiv.classList.toggle("active");
            // Cambiar icono de flecha
            const icon = header.querySelector("i");
            if (grupoDiv.classList.contains("active")) {
                icon.className = "fas fa-chevron-down";
            } else {
                icon.className = "fas fa-chevron-right";
            }
        };
        // Contenedor de Hijos (Municipios)
        const childrenDiv = document.createElement("div");
        childrenDiv.className = "option-group-children";
        if (estado.children && estado.children.length > 0) {
            estado.children.forEach((municipio) => {
                const item = document.createElement("div");
                item.className = "option-child";
                item.textContent = municipio.name;
                // Evento: Seleccionar Municipio
                item.onclick = (e) => {
                    e.stopPropagation();
                    // Guardamos: "Estado, Municipio" (o al revés, según prefieras)
                    const valorGuardar = `${municipio.name}, ${estado.name}`;
                    finalizarSeleccion(valorGuardar, valorGuardar); // Texto visual y valor real iguales
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
    // 2. Agregar opción "Otro" manualmente al final
    const divOtro = document.createElement("div");
    divOtro.className = "option-group-header"; // Usamos estilo de cabecera para que destaque igual
    divOtro.innerHTML = `<span>Otro...</span>`;
    divOtro.style.borderTop = "1px solid #dee2e6"; // Separador sutil
    divOtro.onclick = (e) => {
        e.stopPropagation();
        finalizarSeleccion("Otro", "Otro");
    };
    lista.appendChild(divOtro);
}

// Función Final: Guardar valor y cerrar
function finalizarSeleccion(valorReal, textoVisual) {
    // 1. Actualizar input oculto
    const inputHidden = document.getElementById("inputDestinoHidden");
    if (inputHidden) inputHidden.value = valorReal;
    // 2. Actualizar texto visible
    const label = document.getElementById("labelDestinoSeleccionado");
    if (label) {
        label.textContent = textoVisual;
        label.style.color = "#212529"; // Color texto normal
    }
    // 3. Cerrar menú
    const wrapper = document.getElementById("wrapperDestino");
    if (wrapper) wrapper.classList.remove("open");
}

// ====================================================================
// FUNCIONES DE UNIDADES
// ====================================================================
function generarCodigoViaje(incrementar = false) {
    if (incrementar) {
        codigoViaje++;
    }
    const codigo = `N°:GV-${String(codigoViaje).padStart(3, "0")}`;
    const codigoElement = document.getElementById("codigoViaje");
    if (codigoElement) {
        codigoElement.textContent = codigo;
    }
}

// ====================================================================
// FUNCIONES DE CÁLCULO DE HORAS
// ====================================================================
function minutosAStringHora(totalMinutos) {
    if (totalMinutos === null || isNaN(totalMinutos)) return "0:00";
    const horas = Math.floor(totalMinutos / 60);
    const minutos = totalMinutos % 60;
    const minutosStr = minutos.toString().padStart(2, "0");
    return `${horas}:${minutosStr}`;
}
function stringHoraAMinutos(timeStr) {
    if (!timeStr) return 0;
    const partes = timeStr.split(":");
    if (partes.length !== 2) return 0;
    const h = parseInt(partes[0]) || 0;
    const m = parseInt(partes[1]) || 0;
    return h * 60 + m;
}
function calcularDiferenciaHoras(hora1, hora2) {
    if (!hora1 || !hora2) return "0:00";
    const minutos1 = parseTimeForCalculation(hora1);
    const minutos2 = parseTimeForCalculation(hora2);
    if (minutos1 === null || minutos2 === null) return "0:00";
    let diferencia = minutos2 - minutos1;
    if (diferencia < 0) {
        diferencia += 24 * 60;
    }
    return minutosAStringHora(diferencia);
}
function calcularHorasViaje(unidadNumero) {
    const horaInicioViaje = document.getElementById("horaInicioViaje")?.value;
    const horaFinViaje = document.getElementById("horaFinViaje")?.value;
    const horaLevantar = document.getElementById(
        `levantar-${unidadNumero}`,
    )?.value;
    console.log(`Calculando para unidad ${unidadNumero}:`);
    console.log(`Hora inicio viaje: ${horaInicioViaje}`);
    console.log(`Hora fin viaje: ${horaFinViaje}`);
    console.log(`Hora levantar: ${horaLevantar}`);
    if (horaLevantar && horaInicioViaje) {
        const horasDespiertoStr = calcularDiferenciaHoras(
            horaLevantar,
            horaInicioViaje,
        );
        const inputDespierto = document.getElementById(
            `horas-despierto-${unidadNumero}`,
        );
        console.log(`Horas despierto calculadas: ${horasDespiertoStr}`);
        if (inputDespierto) {
            inputDespierto.value = horasDespiertoStr;
        }
    }
    if (horaInicioViaje && horaFinViaje) {
        const duracionViajeStr = calcularDiferenciaHoras(
            horaInicioViaje,
            horaFinViaje,
        );
        const inputDuracion = document.getElementById(
            `horas-viaje-${unidadNumero}`,
        );
        console.log(`Duración viaje calculada: ${duracionViajeStr}`);
        if (inputDuracion) {
            inputDuracion.value = duracionViajeStr;
        }
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
        const minutosDespierto = stringHoraAMinutos(inputDespierto.value);
        const minutosViaje = stringHoraAMinutos(inputDuracion.value);
        const totalMinutos = minutosDespierto + minutosViaje;
        const totalHorasStr = minutosAStringHora(totalMinutos);
        totalInput.value = totalHorasStr;
        // ⚠️ LÓGICA DE ADVERTENCIA (> 15 HORAS = 900 MINUTOS) - MODIFICADO
        if (totalMinutos > 900) {
            // Cambiado de 840 a 900
            totalInput.style.backgroundColor = "var(--accent-red)";
            totalInput.style.color = "var(--white)";
            if (!totalInput.dataset.warningShown) {
                Swal.fire({
                    title: "¡Advertencia de Horas!",
                    html: `La Unidad <strong>${unidadNumero}</strong> acumulará <strong>${totalHorasStr} horas</strong> totales.<br>Esto excede el límite recomendado de 15 horas.`, // Cambiado texto
                    icon: "warning",
                    confirmButtonColor: "var(--primary-blue)",
                });
                totalInput.dataset.warningShown = "true";
            }
        } else {
            totalInput.style.backgroundColor = "var(--light-gray)";
            totalInput.style.color = "var(--dark-gray)";
            delete totalInput.dataset.warningShown;
        }
    }
}
function calcularHorasViajeParaTodasUnidades() {
    for (let i = 1; i <= contadorUnidades; i++) {
        calcularHorasViaje(i);
    }
}

// ====================================================================
// FUNCIONES EXISTENTES MODIFICADAS
// ====================================================================
function parseTimeForCalculation(timeStr) {
    if (!timeStr) return null;
    const simple24h = timeStr.match(/^(\d{1,2}):(\d{2})$/);
    if (simple24h) {
        const h = parseInt(simple24h[1]);
        const m = parseInt(simple24h[2]);
        if (h >= 0 && h <= 23 && m >= 0 && m <= 59) {
            return h * 60 + m;
        }
    }
    return null;
}
function calcularHorasDormidas(unidadNumero) {
    const horaDormirInput = document.getElementById(`dormir-${unidadNumero}`);
    const horaLevantarInput = document.getElementById(
        `levantar-${unidadNumero}`,
    );
    const totalDormidasInput = document.getElementById(
        `total-hrs-dormidas-${unidadNumero}`,
    );
    const dormir = horaDormirInput ? horaDormirInput.value : null;
    const levantar = horaLevantarInput ? horaLevantarInput.value : null;
    const totalMinutosDormir = parseTimeForCalculation(dormir);
    let totalMinutosLevantar = parseTimeForCalculation(levantar);
    if (totalMinutosDormir !== null && totalMinutosLevantar !== null) {
        if (totalMinutosLevantar <= totalMinutosDormir) {
            totalMinutosLevantar += 24 * 60;
        }
        const duracionMinutos = totalMinutosLevantar - totalMinutosDormir;
        if (totalDormidasInput)
            totalDormidasInput.value = minutosAStringHora(duracionMinutos);
    } else {
        if (totalDormidasInput) totalDormidasInput.value = "0:00";
    }
}

// ====================================================================
// INICIALIZACIÓN FLATPICKR CORREGIDA
// ====================================================================
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
    // Inicializar fechas (null checks)
    const fechaInicioViaje = document.getElementById("fechaInicioViaje");
    if (fechaInicioViaje) flatpickr(fechaInicioViaje, configFecha);
    const fechaFinViaje = document.getElementById("fechaFinViaje");
    if (fechaFinViaje) flatpickr(fechaFinViaje, configFecha);
    // Inicializar horas de inicio y fin del viaje
    const horaInicioViaje = document.getElementById("horaInicioViaje");
    if (horaInicioViaje) flatpickr(horaInicioViaje, configHora);
    const horaFinViaje = document.getElementById("horaFinViaje");
    if (horaFinViaje) flatpickr(horaFinViaje, configHora);
}

// ====================================================================
// FUNCIÓN PARA AGREGAR UNIDAD (CON INICIALIZACIÓN DE FLATPICKR)
// ====================================================================
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
    const filaId = `unidad-${numeroUnidad}`;
    if (numeroUnidad === 2) {
        Swal.fire({
            title: "¡Convoy de Unidades!",
            html: "Has agregado una segunda unidad. Se gestionará como Convoy.",
            icon: "info",
            confirmButtonColor: "var(--primary-blue)",
        });
    }
    const nuevaFila = document.createElement("tr");
    nuevaFila.id = filaId;
    nuevaFila.innerHTML = `
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
                    <i class="fas fa-star badge-principal" id="badge-principal-${numeroUnidad}" title="Conductor Principal" style="display:none;"></i>
                    <div class="autocomplete-list" id="autocomplete-list-${numeroUnidad}"></div>
                </div>
                <button type="button" class="btn-ver-conductor2" onclick="mostrarInfoConductor2(${numeroUnidad})" title="Ver información del Segundo Conductor" style="display: none;">
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
            <label style="font-weight: 700; color: var(--primary-blue);"><i class="fas fa-hourglass-half"></i> Hrs Dormidas</label>
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
                <label style="font-weight: 700; color: var(--primary-blue);"><i class="fas fa-clock"></i> Total Hrs</label>
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
    </td>
    `;
    const cuerpoTablaUnidades = document.getElementById("cuerpoTablaUnidades");
    if (cuerpoTablaUnidades) cuerpoTablaUnidades.appendChild(nuevaFila);
    // Inicializar los campos de Flatpickr para esta unidad
    const configHoraUnidad = { ...configHora }; // Copy config
    const unidadHoraDormir = nuevaFila.querySelector(".unidad-hora-dormir");
    if (unidadHoraDormir) flatpickr(unidadHoraDormir, configHoraUnidad);
    const unidadHoraLevantar = nuevaFila.querySelector(".unidad-hora-levantar");
    if (unidadHoraLevantar) flatpickr(unidadHoraLevantar, configHoraUnidad);
    // Inicializar el selector de vehículos
    inicializarSelectorVehiculo(numeroUnidad);
    // Inicializar autocomplete de conductor
    inicializarAutocompleteConductor(numeroUnidad);
    // Listeners para horas (with null checks)
    if (unidadHoraDormir) {
        unidadHoraDormir.addEventListener("change", () => {
            calcularHorasDormidas(numeroUnidad);
            actualizarBotonReunionConvoy();
        });
    }
    if (unidadHoraLevantar) {
        unidadHoraLevantar.addEventListener("change", () => {
            calcularHorasDormidas(numeroUnidad);
            calcularHorasViaje(numeroUnidad);
            actualizarBotonReunionConvoy();
        });
    }
    // Agregar pasajero inicial
    agregarPasajero(numeroUnidad);
    // Actualizar estados
    actualizarContadorUnidades();
    actualizarLabelTipoUnidad();
    actualizarBotonAgregar();
    actualizarBotonReunionConvoy();
    actualizarBotonEvaluacion();
}

// ====================================================================
// FUNCIÓN CALCULAR HORAS DE VIAJE CORREGIDA
// ====================================================================
function calcularHorasViaje(unidadNumero) {
    const horaInicioViaje = document.getElementById("horaInicioViaje")?.value;
    const horaFinViaje = document.getElementById("horaFinViaje")?.value;
    const horaLevantar = document.getElementById(
        `levantar-${unidadNumero}`,
    )?.value;
    console.log(`Calculando para unidad ${unidadNumero}:`);
    console.log(`Hora inicio viaje: ${horaInicioViaje}`);
    console.log(`Hora fin viaje: ${horaFinViaje}`);
    console.log(`Hora levantar: ${horaLevantar}`);
    if (horaLevantar && horaInicioViaje) {
        const horasDespiertoStr = calcularDiferenciaHoras(
            horaLevantar,
            horaInicioViaje,
        );
        const inputDespierto = document.getElementById(
            `horas-despierto-${unidadNumero}`,
        );
        console.log(`Horas despierto calculadas: ${horasDespiertoStr}`);
        if (inputDespierto) {
            inputDespierto.value = horasDespiertoStr;
        }
    }
    if (horaInicioViaje && horaFinViaje) {
        const duracionViajeStr = calcularDiferenciaHoras(
            horaInicioViaje,
            horaFinViaje,
        );
        const inputDuracion = document.getElementById(
            `horas-viaje-${unidadNumero}`,
        );
        console.log(`Duración viaje calculada: ${duracionViajeStr}`);
        if (inputDuracion) {
            inputDuracion.value = duracionViajeStr;
        }
    }
    calcularTotalHoras(unidadNumero);
}

// ====================================================================
// FUNCIÓN PARSE TIME MEJORADA
// ====================================================================
function parseTimeForCalculation(timeStr) {
    if (!timeStr || timeStr.trim() === "") return null;
    // Remover espacios y convertir a mayúsculas
    timeStr = timeStr.trim().toUpperCase();
    // Formato HH:MM (24h)
    const simple24h = timeStr.match(/^(\d{1,2}):(\d{2})$/);
    if (simple24h) {
        const h = parseInt(simple24h[1]);
        const m = parseInt(simple24h[2]);
        // Validar rangos
        if (h >= 0 && h <= 23 && m >= 0 && m <= 59) {
            return h * 60 + m;
        }
    }
    // Formato HH:MM AM/PM
    const ampm = timeStr.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
    if (ampm) {
        let h = parseInt(ampm[1]);
        const m = parseInt(ampm[2]);
        const meridiem = ampm[3].toUpperCase();
        if (h === 12) {
            h = meridiem === "AM" ? 0 : 12;
        } else if (meridiem === "PM") {
            h += 12;
        }
        if (h >= 0 && h <= 23 && m >= 0 && m <= 59) {
            return h * 60 + m;
        }
    }
    console.warn(`Formato de hora no reconocido: "${timeStr}"`);
    return null;
}

// ====================================================================
// FUNCIÓN CALCULAR DIFERENCIA DE HORAS MEJORADA
// ====================================================================
function calcularDiferenciaHoras(hora1, hora2) {
    if (!hora1 || !hora2 || hora1.trim() === "" || hora2.trim() === "") {
        console.log("Hora1 o Hora2 vacías, retornando 0:00");
        return "0:00";
    }
    const minutos1 = parseTimeForCalculation(hora1);
    const minutos2 = parseTimeForCalculation(hora2);
    console.log(
        `Diferencia entre ${hora1} (${minutos1}min) y ${hora2} (${minutos2}min)`,
    );
    if (minutos1 === null || minutos2 === null) {
        console.warn("No se pudo parsear una de las horas");
        return "0:00";
    }
    let diferencia = minutos2 - minutos1;
    // Si la diferencia es negativa, asumimos que cruza la medianoche
    if (diferencia < 0) {
        diferencia += 24 * 60; // Añadir 24 horas
    }
    const resultado = minutosAStringHora(diferencia);
    console.log(`Diferencia calculada: ${resultado}`);
    return resultado;
}

// ====================================================================
// FUNCIÓN PARA CARGAR CONDUCTORES DESDE LA BASE DE DATOS
// ====================================================================
async function cargarConductoresDesdeBD() {
    try {
        const response = await fetch("/qhse/gerenciamiento/conductores");
        if (!response.ok) throw new Error("Error al cargar conductores");
        const data = await response.json();
        if (data.success) {
            conductoresGlobales = data.conductores || [];
            datosConductoresGlobales = data.datosConductores || {};
            console.log(
                "Conductores cargados desde BD:",
                conductoresGlobales.length,
            );
            console.log("Datos de conductores:", datosConductoresGlobales);
        } else {
            throw new Error(
                data.message || "Error en la respuesta del servidor",
            );
        }
    } catch (error) {
        console.error("Error cargando conductores:", error);
        conductoresGlobales = [];
        datosConductoresGlobales = {};
        Swal.fire({
            title: "Error de Conexión",
            text: "No se pudieron cargar los conductores desde la base de datos. Por favor, recargue la página.",
            icon: "error",
            timer: 5000,
        });
    }
}

// ====================================================================
// FUNCIÓN PARA CARGAR VEHÍCULOS DESDE LA BASE DE DATOS (Sin cambios, pero agregamos un chequeo global)
// ====================================================================
async function cargarVehiculosDesdeBD() {
    const CACHE_KEY = "vehiculos_cache_v1";
    const CACHE_DURATION = 24 * 60 * 60 * 1000; // 24 Horas en milisegundos
    // 1. INTENTAR CARGAR DE LOCALSTORAGE (Carga Instantánea)
    const cachedData = localStorage.getItem(CACHE_KEY);
    if (cachedData) {
        const parsed = JSON.parse(cachedData);
        const now = new Date().getTime();
        // Si el caché existe y no ha expirado (menos de 24h)
        if (now - parsed.timestamp < CACHE_DURATION) {
            procesarDatosVehiculos(parsed.data);
            return; // ¡TERMINAMOS AQUÍ! No tocamos el servidor.
        }
    }
    // 2. SI NO HAY CACHÉ O EXPIRÓ, PEDIMOS AL SERVIDOR
    try {
        console.log("🌐 Descargando lista de vehículos del servidor...");
        const response = await fetch("/qhse/gerenciamiento/vehicles");
        if (!response.ok) throw new Error("Error al cargar vehículos");
        const data = await response.json();
        if (data.success) {
            // Guardamos en LocalStorage con fecha de hoy
            const cacheObject = {
                timestamp: new Date().getTime(),
                data: data,
            };
            localStorage.setItem(CACHE_KEY, JSON.stringify(cacheObject));
            // Procesamos los datos
            procesarDatosVehiculos(data);
        } else {
            throw new Error(data.message || "Error del servidor");
        }
    } catch (error) {
        console.error("Error cargando vehículos:", error);
        // Si falla la red, intentar usar caché viejo si existe como respaldo de emergencia
        if (cachedData) {
            console.warn("⚠️ Usando caché expirado por falta de conexión.");
            procesarDatosVehiculos(JSON.parse(cachedData).data);
        } else {
            vehiculosLigeros = [];
            vehiculosPesados = [];
        }
    }
}

// Función auxiliar para no repetir código (limpieza y orden)
function procesarDatosVehiculos(data) {
    vehiculosLigeros = data.ligeras || [];
    vehiculosPesados = data.pesadas || [];
    clasificacionVehiculos = data.clasificacion || {};
    // Variables globales para selects nativos (si los usas)
    optionsLigeras = vehiculosLigeros
        .map((v) => `<option value="${v}">${v}</option>`)
        .join("");
    optionsPesadas = vehiculosPesados
        .map((v) => `<option value="${v}">${v}</option>`)
        .join("");
    console.log(
        "Total vehículos cargados:",
        vehiculosLigeros.length + vehiculosPesados.length,
    );
}

// ====================================================================
// FUNCIONES DE AUTOCOMPLETE (FLOTANTE Y POSICIONADO)
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
    // Obtenemos referencia al div de la lista (o lo creamos si no existe en memoria)
    let listContainer = document.getElementById(listId);
    if (!input || !listContainer) return;
    let currentFocus = -1;
    // --- FUNCIÓN PARA POSICIONAR LA LISTA ---
    const positionList = () => {
        if (!listContainer || listContainer.style.display === "none") return;
        const rect = input.getBoundingClientRect();
        // Copiamos el ancho del input
        listContainer.style.width = rect.width + "px";
        // Alineamos a la izquierda del input
        listContainer.style.left = rect.left + "px";
        // 🔥 AQUÍ ESTÁ EL TRUCO PARA "MÁS ABAJO" 🔥
        // rect.bottom es el final del input. Le sumamos 5px de espacio.
        listContainer.style.top = rect.bottom + 5 + "px";
    };
    // Actualizar posición si se hace scroll o redimensiona la ventana
    window.addEventListener(
        "scroll",
        () => {
            if (listContainer.innerHTML !== "") positionList();
        },
        true,
    ); // Use capture para detectar scroll en cualquier contenedor
    window.addEventListener("resize", () => {
        if (listContainer.innerHTML !== "") positionList();
    });
    const updateList = () => {
        const val = input.value;
        // Limpiamos listas previas
        const x = document.getElementsByClassName("autocomplete-list");
        for (let i = 0; i < x.length; i++) {
            if (x[i] != listContainer) x[i].innerHTML = "";
        }
        currentFocus = -1;
        if (!val) {
            listContainer.style.display = "none";
            listContainer.innerHTML = "";
            if (
                input.dataset.conductorSeleccionado !== undefined &&
                !esPasajero
            ) {
                actualizarDatosConductor(unidadNumero, "");
            }
            delete input.dataset.conductorSeleccionado;
            return false;
        }
        const filtered = conductoresGlobales.filter((c) =>
            c.toUpperCase().includes(val.toUpperCase()),
        );
        // 🔥 CRÍTICO: MOVER AL BODY PARA EVITAR QUE LA TABLA LO CORTE 🔥
        document.body.appendChild(listContainer);
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
                // Usamos mousedown para prevenir que el input pierda el foco antes del click
                item.addEventListener("mousedown", function (e) {
                    e.preventDefault();
                    input.value = c;
                    input.dataset.conductorSeleccionado = c;
                    listContainer.innerHTML = "";
                    listContainer.style.display = "none";
                    // --- ESTA ES LA PARTE IMPORTANTE PARA QUE SE ACTUALICE SIEMPRE ---
                    if (!esPasajero) {
                        // Forzamos un pequeño timeout para asegurar que el valor se asentó
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
        // Calculamos la posición después de agregar el contenido
        positionList();
    };
    // --- EVENT LISTENERS ---
    input.addEventListener("input", updateList);
    input.addEventListener("focus", () => {
        if (input.value.trim() !== "") updateList();
    });
    // Navegación con teclado (Flechas y Enter)
    input.addEventListener("keydown", function (e) {
        let items = listContainer.getElementsByClassName("autocomplete-item");
        if (e.key === "ArrowDown") {
            currentFocus++;
            addActive(items);
        } else if (e.key === "ArrowUp") {
            currentFocus--;
            addActive(items);
        } else if (e.key === "Enter" || e.key === "Tab") {
            if (currentFocus > -1) {
                if (items) {
                    e.preventDefault();
                    // Simulamos click en el item seleccionado
                    const event = new MouseEvent("mousedown", {
                        bubbles: true,
                        cancelable: true,
                        view: window,
                    });
                    items[currentFocus].dispatchEvent(event);
                }
            } else if (e.key === "Enter") {
                // Si presiona enter sin seleccionar con flechas, buscar coincidencia exacta
                e.preventDefault();
                const exactMatch = conductoresGlobales.find(
                    (c) => c.toUpperCase() === input.value.toUpperCase(),
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
    function addActive(x) {
        if (!x) return false;
        removeActive(x);
        if (currentFocus >= x.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = x.length - 1;
        x[currentFocus].classList.add("selected");
        // Scroll automático dentro de la lista
        x[currentFocus].scrollIntoView({ block: "nearest" });
    }
    function removeActive(x) {
        for (let i = 0; i < x.length; i++) {
            x[i].classList.remove("selected");
        }
    }
    input.addEventListener("blur", function () {
        // Retraso pequeño para permitir que el click se registre
        setTimeout(() => {
            listContainer.style.display = "none";
            listContainer.innerHTML = "";
            // Validación extra al salir
            const exactMatch = conductoresGlobales.find(
                (c) => c.toUpperCase() === input.value.toUpperCase(),
            );
            if (!exactMatch && !esPasajero && input.value !== "") {
                // Aquí decides si limpias el campo si no es válido
                // actualizarDatosConductor(unidadNumero, '');
            }
        }, 200);
    });
}

// ====================================================================
// FUNCIÓN UNIFICADA DE VALIDACIÓN DE DOCUMENTACIÓN (REUTILIZABLE)
// ====================================================================
function validarDocumentacionConductor(data, esPesada) {
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);

    let licenciaValida = false;
    let licenciaMensaje = "";
    let licenciaFecha = null;
    let licenciaEstilo = "invalid";

    let cursoValido = false;
    let cursoMensaje = "";
    let cursoFecha = null;
    let cursoEstilo = "invalid";

    // --- VALIDACIÓN LICENCIA ---
    if (esPesada) {
        // LICENCIA FEDERAL (PESADA)
        const fechaFederal = data.federalVigencia;
        if (!fechaFederal) {
            licenciaMensaje = "No registrada";
            licenciaEstilo = "missing";
        } else {
            const fechaLic = new Date(fechaFederal + "T00:00:00");
            licenciaFecha = fechaLic.toLocaleDateString("es-ES");
            licenciaMensaje = licenciaFecha;
            const diasLic = Math.ceil((fechaLic - hoy) / (1000 * 60 * 60 * 24));

            if (diasLic < 0) {
                licenciaEstilo = "expired";
            } else if (diasLic <= 30) {
                licenciaValida = true;
                licenciaEstilo = "orange";
            } else if (diasLic <= 60) {
                licenciaValida = true;
                licenciaEstilo = "warning";
            } else {
                licenciaValida = true;
                licenciaEstilo = "ok";
            }
        }
    } else {
        // LICENCIA NORMAL (LIGERA)
        const esPermanente = (data.permanente === true || data.permanente === 1 || data.permanente === "1");
        const fechaNormal = data.vigencia;

        if (esPermanente) {
            licenciaValida = true;
            licenciaMensaje = "Permanente";
            licenciaEstilo = "ok";
        } else if (!fechaNormal) {
            licenciaMensaje = "No registrada";
            licenciaEstilo = "missing";
        } else {
            const fechaLic = new Date(fechaNormal + "T00:00:00");
            licenciaFecha = fechaLic.toLocaleDateString("es-ES");
            licenciaMensaje = licenciaFecha;
            const diasLic = Math.ceil((fechaLic - hoy) / (1000 * 60 * 60 * 24));

            if (diasLic < 0) {
                licenciaEstilo = "expired";
            } else if (diasLic <= 30) {
                licenciaValida = true;
                licenciaEstilo = "orange";
            } else if (diasLic <= 60) {
                licenciaValida = true;
                licenciaEstilo = "warning";
            } else {
                licenciaValida = true;
                licenciaEstilo = "ok";
            }
        }
    }

    // --- VALIDACIÓN CURSO MANEJO ---
    const fechaCursoRaw = esPesada ? data.cursoPesadoVigencia : data.manDefVigencia;

    if (!fechaCursoRaw) {
        cursoMensaje = "No registrado";
        cursoEstilo = "missing";
    } else {
        const fechaCurso = new Date(fechaCursoRaw + "T00:00:00");
        cursoFecha = fechaCurso.toLocaleDateString("es-ES");
        cursoMensaje = cursoFecha;
        const diasCurso = Math.ceil((fechaCurso - hoy) / (1000 * 60 * 60 * 24));

        if (diasCurso < 0) {
            cursoEstilo = "expired";
        } else if (diasCurso <= 30) {
            cursoValido = true;
            cursoEstilo = "orange";
        } else if (diasCurso <= 60) {
            cursoValido = true;
            cursoEstilo = "warning";
        } else {
            cursoValido = true;
            cursoEstilo = "ok";
        }
    }

    return {
        licencia: {
            valida: licenciaValida,
            mensaje: licenciaMensaje,
            estilo: licenciaEstilo,
            fecha: licenciaFecha
        },
        curso: {
            valida: cursoValido,
            mensaje: cursoMensaje,
            estilo: cursoEstilo,
            fecha: cursoFecha
        }
    };
}

// ====================================================================
// FUNCIÓN ACTUALIZADA: DATOS CONDUCTOR (Tabla Principal)
// ====================================================================
function actualizarDatosConductor(unidadNumero, nombreConductor) {
    const inputConductor = document.getElementById(`conductor-${unidadNumero}`);
    const inputVigenciaLic = document.getElementById(`vigencia-lic-${unidadNumero}`);
    const inputVigenciaMan = document.getElementById(`vigencia-man-${unidadNumero}`);
    const tipoVehiculoDiv = document.getElementById(`tipo-vehiculo-${unidadNumero}`);

    // --- 1. DETERMINAR TIPO DE VEHÍCULO ---
    let esPesada = false;
    if (tipoVehiculoDiv && (tipoVehiculoDiv.textContent.includes("Pesada") || tipoVehiculoDiv.classList.contains("tipo-pesada"))) {
        esPesada = true;
    }

    // --- 2. CONFIGURAR ETIQUETAS ---
    const labelCursoContainer = inputVigenciaMan?.closest(".hour-input-group");
    const labelCursoElement = labelCursoContainer ? labelCursoContainer.querySelector("label") : null;
    const labelLicenciaContainer = inputVigenciaLic?.closest(".hour-input-group");
    const labelLicenciaElement = labelLicenciaContainer ? labelLicenciaContainer.querySelector("label") : null;

    if (labelLicenciaElement) {
        if (esPesada) {
            labelLicenciaElement.innerHTML = `<i class="fas fa-id-card"></i> Vigencia Licencia Federal`;
            labelLicenciaElement.style.color = "var(--primary-orange)";
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
        labelCursoElement.style.color = "var(--primary-blue)";
    }

    // --- 3. LIMPIEZA INICIAL ---
    if (!nombreConductor || !datosConductoresGlobales[nombreConductor]) {
        limpiarInputsVigencia(inputVigenciaLic);
        limpiarInputsVigencia(inputVigenciaMan);
        aplicarEstiloGris(inputVigenciaMan);
        aplicarEstiloGris(inputVigenciaLic);
        return;
    }

    const data = datosConductoresGlobales[nombreConductor];

    // --- 4. USAR FUNCIÓN UNIFICADA DE VALIDACIÓN ---
    const validacion = validarDocumentacionConductor(data, esPesada);

    // --- 5. APLICAR RESULTADOS A LOS INPUTS ---
    // Aplicar licencia
    inputVigenciaLic.value = validacion.licencia.mensaje;
    aplicarEstiloPorValidacion(inputVigenciaLic, validacion.licencia.estilo);

    // Aplicar curso
    inputVigenciaMan.value = validacion.curso.mensaje;
    aplicarEstiloPorValidacion(inputVigenciaMan, validacion.curso.estilo);

    // --- 6. BLOQUEO DE SEGURIDAD SI HAY PROBLEMAS GRAVES ---
    const problemasDocumentacion = [];

    if (validacion.licencia.estilo === "expired" || validacion.licencia.estilo === "missing") {
        const tipoLic = esPesada ? "Licencia Federal" : "Licencia";
        const estadoTexto = validacion.licencia.estilo === "expired" ? "VENCIDA" : "NO REGISTRADA";
        problemasDocumentacion.push(`${tipoLic} (${estadoTexto})`);
    }

    if (validacion.curso.estilo === "expired" || validacion.curso.estilo === "missing") {
        const tipoCurso = esPesada ? "Curso Man. Def. Pesada" : "Curso Man. Def. Ligera";
        const estadoTexto = validacion.curso.estilo === "expired" ? "VENCIDO" : "NO REGISTRADO";
        problemasDocumentacion.push(`${tipoCurso} (${estadoTexto})`);
    }

    if (problemasDocumentacion.length > 0) {
        Swal.fire({
            title: '¡PROBLEMAS DE DOCUMENTACIÓN!',
            html: `
                <div style="text-align: left;">
                    El conductor <strong>${nombreConductor}</strong> no puede ser asignado.<br><br>
                    Se detectaron los siguientes problemas:
                    <ul style="color: #dc3545; font-weight: bold; margin-top: 10px; text-align: left;">
                        ${problemasDocumentacion.map(prob => `<li>${prob}</li>`).join('')}
                    </ul>
                    <br>
                    <i class="fas fa-ban"></i> <strong>ACCIÓN REQUERIDA:</strong><br>
                    Cambie de conductor o actualice sus documentos.
                </div>
            `,
            icon: 'error',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#dc3545',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                if (inputConductor) {
                    inputConductor.value = "";
                    delete inputConductor.dataset.conductorSeleccionado;
                }
                actualizarDatosConductor(unidadNumero, ""); // Limpiar recursivamente
                actualizarBotonReunionConvoy();
                actualizarBotonEvaluacion();
            }
        });
    }
}

// ====================================================================
// FUNCIÓN AUXILIAR PARA APLICAR ESTILOS SEGÚN VALIDACIÓN
// ====================================================================
function aplicarEstiloPorValidacion(input, estado) {
    if (!input) return;

    input.style.fontWeight = "bold";

    switch(estado) {
        case 'ok':
            input.style.backgroundColor = "#28a745"; // Verde
            input.style.color = "#ffffff";
            input.title = "Vigente";
            break;
        case 'orange':
            input.style.backgroundColor = "#fd7e14"; // Naranja (≤ 30 días)
            input.style.color = "#ffffff";
            input.title = "Vence pronto (≤ 30 días)";
            break;
        case 'warning':
            input.style.backgroundColor = "#ffc107"; // Amarillo (≤ 60 días)
            input.style.color = "#212529";
            input.title = "Precaución (≤ 60 días)";
            break;
        case 'expired':
            input.style.backgroundColor = "#dc3545"; // Rojo (Vencido)
            input.style.color = "#ffffff";
            input.title = "VENCIDO";
            break;
        case 'missing':
            input.style.backgroundColor = "#e9ecef"; // Gris
            input.style.color = "#6c757d";
            input.title = "No registrado";
            break;
        default:
            input.style.backgroundColor = "#e9ecef";
            input.style.color = "#495057";
            input.title = "";
    }
}

// ====================================================================
// FUNCIONES AUXILIARES DE ESTILO
// ====================================================================
function aplicarEstiloGris(input) {
    if (!input) return;
    input.style.backgroundColor = "#e9ecef"; // Gris claro
    input.style.color = "#6c757d"; // Texto gris oscuro
    input.style.fontWeight = "normal";
    input.title = "";
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
// GESTIÓN DE MODALES PRINCIPALES
// ====================================================================
function gestionarModalFormulario(abrir) {
    const modal = document.getElementById("modalFormulario");
    if (abrir) {
        if (modal) {
            modal.classList.add("active");
            document.body.style.overflow = "hidden";
            if (contadorUnidades === 0) {
                agregarUnidad();
            }
        }
    } else {
        Swal.fire({
            title: "¿Desea cerrar el formulario?",
            text: "Se perderán los datos no guardados de la solicitud actual.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, cerrar",
            cancelButtonText: "Continuar editando",
            confirmButtonColor: "var(--accent-red)",
            cancelButtonColor: "var(--primary-blue)",
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
    // Limpiar datos de inspección
    for (const key in datosInspeccionLigera) {
        delete datosInspeccionLigera[key];
    }
    for (const key in datosInspeccionPesada) {
        delete datosInspeccionPesada[key];
    }
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
        btnEval.classList.remove("evaluacion-completada");
        btnEval.innerHTML =
            '<i class="fas fa-list-check"></i> Realizar Evaluación del Viaje (Requerido)';
    }
    const formEvaluacionRiesgo = document.getElementById(
        "formEvaluacionRiesgo",
    );
    if (formEvaluacionRiesgo) formEvaluacionRiesgo.reset();
    actualizarBotonEvaluacion();
}
const modalFormulario = document.getElementById("modalFormulario");
if (modalFormulario) {
    modalFormulario.addEventListener("click", function (e) {
        if (e.target === this) {
            gestionarModalFormulario(false);
        }
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
            if (lista && lista.children.length === 0) {
                agregarParada();
            }
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
            confirmButtonColor: "var(--primary-blue)",
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
        if (lista && lista.children.length === 0) {
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
        if (paradaTitulo) {
            paradaTitulo.textContent = `Parada ${contadorGlobal}`;
        }
        const select = parada.querySelector("select");
        const input = parada.querySelector('input[type="text"]');
        const btnEliminar = parada.querySelector(".btn-remove-parada-compact");
        if (select) {
            select.setAttribute("name", `paradas[${contadorGlobal}][motivo]`);
        }
        if (input) {
            input.setAttribute("name", `paradas[${contadorGlobal}][lugar]`);
        }
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
// GESTIÓN DE MODALES DE INSPECCIÓN
// ====================================================================
let unidadEnInspeccion = null;
let tipoVehiculoInspeccion = null;
function gestionarModalInspeccionLigera(abrir, unidadNumero = null) {
    const modal = document.getElementById("modalInspeccionLigera");
    if (abrir && unidadNumero !== null) {
        unidadEnInspeccion = unidadNumero;
        tipoVehiculoInspeccion = "ligera";
        const hiddenInput = document.getElementById(
            `vehicle-hidden-${unidadNumero}`,
        );
        const nombreVehiculo = hiddenInput
            ? hiddenInput.value
            : "Unidad " + unidadNumero;
        const inputNoEconomicoLigera = document.getElementById(
            "inputNoEconomicoLigera",
        );
        if (inputNoEconomicoLigera)
            inputNoEconomicoLigera.textContent = nombreVehiculo;
        const inspeccionUnidadIndexLigera = document.getElementById(
            "inspeccionUnidadIndexLigera",
        );
        if (inspeccionUnidadIndexLigera)
            inspeccionUnidadIndexLigera.value = unidadNumero;
        // Cargar datos guardados si existen
        const savedData = datosInspeccionLigera[unidadNumero] || {};
        Object.keys(savedData).forEach((key) => {
            const radio = document.querySelector(
                `input[name="inspeccion_${key}"][value="${savedData[key]}"]`,
            );
            if (radio) {
                radio.checked = true;
            }
        });
        if (modal) modal.classList.add("active");
        document.body.style.overflow = "hidden";
    } else {
        if (modal) modal.classList.remove("active");
        document.body.style.overflow = "auto";
        unidadEnInspeccion = null;
        tipoVehiculoInspeccion = null;
    }
}
function gestionarModalInspeccionPesada(abrir, unidadNumero = null) {
    const modal = document.getElementById("modalInspeccionPesada");
    if (abrir && unidadNumero !== null) {
        unidadEnInspeccion = unidadNumero;
        tipoVehiculoInspeccion = "pesada";
        const hiddenInput = document.getElementById(
            `vehicle-hidden-${unidadNumero}`,
        );
        const nombreVehiculo = hiddenInput
            ? hiddenInput.value
            : "Unidad " + unidadNumero;
        const inputNoEconomicoPesada = document.getElementById(
            "inputNoEconomicoPesada",
        );
        if (inputNoEconomicoPesada)
            inputNoEconomicoPesada.textContent = nombreVehiculo;
        const inspeccionUnidadIndexPesada = document.getElementById(
            "inspeccionUnidadIndexPesada",
        );
        if (inspeccionUnidadIndexPesada)
            inspeccionUnidadIndexPesada.value = unidadNumero;
        // Cargar datos guardados si existen
        const savedData = datosInspeccionPesada[unidadNumero] || {};
        Object.keys(savedData).forEach((key) => {
            const radio = document.querySelector(
                `input[name="inspeccion_${key}_p"][value="${savedData[key]}"]`,
            );
            if (radio) {
                radio.checked = true;
            }
        });
        if (modal) modal.classList.add("active");
        document.body.style.overflow = "hidden";
    } else {
        if (modal) modal.classList.remove("active");
        document.body.style.overflow = "auto";
        unidadEnInspeccion = null;
        tipoVehiculoInspeccion = null;
    }
}
function abrirInspeccion(unidadNumero) {
    // Obtener el vehículo seleccionado del input oculto
    const hiddenInput = document.getElementById(
        `vehicle-hidden-${unidadNumero}`,
    );
    const vehiculo = hiddenInput ? hiddenInput.value : "";
    if (!vehiculo || vehiculo.trim() === "") {
        Swal.fire({
            title: "Seleccione un vehículo",
            text: "Primero seleccione un vehículo para saber qué tipo de inspección realizar.",
            icon: "warning",
            confirmButtonColor: "var(--primary-blue)",
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
            confirmButtonColor: "var(--primary-blue)",
        });
    }
}
function guardarInspeccionLigera() {
    const form = document.getElementById("formInspeccionLigera");
    const unidadNumero = parseInt(
        document.getElementById("inspeccionUnidadIndexLigera")?.value,
    );
    // Validar que todos los campos estén completos
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
        const checked = form.querySelector(
            `input[name="inspeccion_${item}"]:checked`,
        );
        if (!checked) {
            formValido = false;
        }
    });
    if (!formValido) {
        Swal.fire({
            title: "Inspección Incompleta",
            text: "Debe responder sí o no a todos los elementos de la inspección.",
            icon: "warning",
            confirmButtonColor: "var(--primary-blue)",
        });
        return;
    }
    const data = {};
    let todoAprobado = true;
    items.forEach((item) => {
        const value = form.querySelector(
            `input[name="inspeccion_${item}"]:checked`,
        ).value;
        data[item] = value;
        if (value === "no") {
            todoAprobado = false;
        }
    });
    datosInspeccionLigera[unidadNumero] = data;
    const btnInspeccion = document.getElementById(
        `btn-inspeccion-${unidadNumero}`,
    );
    if (btnInspeccion) {
        if (todoAprobado) {
            btnInspeccion.classList.add(
                "btn-submit",
                "btn-inspeccion-aprobado",
            );
            btnInspeccion.classList.remove("btn-inspeccion");
            btnInspeccion.innerHTML = '<i class="fas fa-check-circle"></i>';
            btnInspeccion.dataset.aprobado = "true";
            btnInspeccion.title = "Inspección Aprobada";
        } else {
            btnInspeccion.classList.add("btn-inspeccion");
            btnInspeccion.classList.remove(
                "btn-submit",
                "btn-inspeccion-aprobado",
            );
            btnInspeccion.innerHTML =
                '<i class="fas fa-exclamation-triangle"></i>';
            btnInspeccion.dataset.aprobado = "false";
            btnInspeccion.title = "Revisar Inspección";
        }
    }
    Swal.fire({
        title: "Inspección Guardada",
        text: `La inspección para la Unidad ${unidadNumero} ha sido registrada.`,
        icon: "success",
        confirmButtonColor: "var(--primary-blue)",
    }).then(() => {
        gestionarModalInspeccionLigera(false);
        actualizarBotonReunionConvoy();
        actualizarBotonEvaluacion();
    });
}
function guardarInspeccionPesada() {
    const form = document.getElementById("formInspeccionPesada");
    const unidadNumero = parseInt(
        document.getElementById("inspeccionUnidadIndexPesada")?.value,
    );
    // Validar que todos los campos estén completos
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
        const checked = form.querySelector(
            `input[name="inspeccion_${item}"]:checked`,
        );
        if (!checked) {
            formValido = false;
        }
    });
    if (!formValido) {
        Swal.fire({
            title: "Inspección Incompleta",
            text: "Debe responder sí o no a todos los elementos de la inspección.",
            icon: "warning",
            confirmButtonColor: "var(--primary-blue)",
        });
        return;
    }
    const data = {};
    let todoAprobado = true;
    items.forEach((item) => {
        const value = form.querySelector(
            `input[name="inspeccion_${item}"]:checked`,
        ).value;
        data[item] = value;
        if (value === "no") {
            todoAprobado = false;
        }
    });
    datosInspeccionPesada[unidadNumero] = data;
    const btnInspeccion = document.getElementById(
        `btn-inspeccion-${unidadNumero}`,
    );
    if (btnInspeccion) {
        if (todoAprobado) {
            btnInspeccion.classList.add(
                "btn-submit",
                "btn-inspeccion-aprobado",
            );
            btnInspeccion.classList.remove("btn-inspeccion");
            btnInspeccion.innerHTML = '<i class="fas fa-check-circle"></i>';
            btnInspeccion.dataset.aprobado = "true";
            btnInspeccion.title = "Inspección Aprobada";
        } else {
            btnInspeccion.classList.add("btn-inspeccion");
            btnInspeccion.classList.remove(
                "btn-submit",
                "btn-inspeccion-aprobado",
            );
            btnInspeccion.innerHTML =
                '<i class="fas fa-exclamation-triangle"></i>';
            btnInspeccion.dataset.aprobado = "false";
            btnInspeccion.title = "Revisar Inspección";
        }
    }
    Swal.fire({
        title: "Inspección Guardada",
        text: `La inspección para la Unidad ${unidadNumero} ha sido registrada.`,
        icon: "success",
        confirmButtonColor: "var(--primary-blue)",
    }).then(() => {
        gestionarModalInspeccionPesada(false);
        actualizarBotonReunionConvoy();
        actualizarBotonEvaluacion();
    });
}

// ====================================================================
// EVALUACIÓN DE RIESGO
// ====================================================================
function actualizarBotonEvaluacion() {
    const btn = document.getElementById("btnEvaluacionRiesgo");
    if (btn) {
        if (contadorUnidades > 0) {
            btn.disabled = false;
        } else {
            btn.disabled = true;
        }
    }
    actualizarBotonEnviarSolicitud();
}
function gestionarModalEvaluacion(abrir) {
    const modal = document.getElementById("modalEvaluacion");
    if (modal) {
        if (abrir) {
            modal.classList.add("active");
            document.body.style.overflow = "hidden";
        } else {
            modal.classList.remove("active");
            document.body.style.overflow = "auto";
        }
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
    // 1. Calcular puntos
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
            confirmButtonColor: "var(--primary-blue)",
        });
        return;
    }
    // 2. Definir variables según puntaje
    let nivelRiesgoTexto = "";
    let cssClassBadge = ""; // Clase para el SweetAlert
    let cssClassBtn = ""; // Clase para el Botón principal
    let iconoSwal = "success";
    let btnConfirmColor = "";
    let iconoBadge = "";
    let mensajeAdicional = "";
    if (totalPuntos <= 55) {
        nivelRiesgoTexto = "Bajo Riesgo";
        cssClassBadge = "status-riesgo-bajo";
        cssClassBtn = "btn-riesgo-bajo"; // <--- Clase CSS del botón verde
        iconoSwal = "success";
        btnConfirmColor = "var(--riesgo-bajo-color)";
        iconoBadge = '<i class="fas fa-check-circle"></i>';
        mensajeAdicional = "El viaje puede proceder normalmente.";
    } else if (totalPuntos >= 56 && totalPuntos <= 105) {
        nivelRiesgoTexto = "Riesgo Medio";
        cssClassBadge = "status-riesgo-medio";
        cssClassBtn = "btn-riesgo-medio"; // <--- Clase CSS del botón amarillo
        iconoSwal = "warning";
        btnConfirmColor = "var(--riesgo-medio-color)";
        iconoBadge = '<i class="fas fa-exclamation-circle"></i>';
        mensajeAdicional = "Se requieren precauciones adicionales.";
    } else if (totalPuntos >= 106 && totalPuntos <= 145) {
        nivelRiesgoTexto = "Alto Riesgo";
        cssClassBadge = "status-riesgo-alto";
        cssClassBtn = "btn-riesgo-alto"; // <--- Clase CSS del botón naranja
        iconoSwal = "warning";
        btnConfirmColor = "var(--riesgo-alto-color)";
        iconoBadge = '<i class="fas fa-exclamation-triangle"></i>';
        mensajeAdicional =
            "Se requiere aprobación especial y medidas de seguridad.";
    } else if (totalPuntos > 145) {
        nivelRiesgoTexto = "Muy Alto Riesgo";
        cssClassBadge = "status-riesgo-muy-alto";
        cssClassBtn = "btn-riesgo-muy-alto"; // <--- Clase CSS del botón rojo
        iconoSwal = "error";
        btnConfirmColor = "var(--riesgo-muy-alto-color)";
        iconoBadge = '<i class="fas fa-ban"></i>';
        mensajeAdicional =
            "Se recomienda mucha precaución. Requiere autorización de gerencia.";
    }
    // 3. Variables Globales
    puntajeRiesgoTotal = totalPuntos;
    evaluacionRiesgoGuardada = true;
    // 4. ACTUALIZAR EL BOTÓN PRINCIPAL
    const btn = document.getElementById("btnEvaluacionRiesgo");
    if (btn) {
        // Cambiar texto para indicar que se puede editar
        btn.innerHTML = `<i class="fas fa-check-circle"></i> Evaluación: ${nivelRiesgoTexto} <br><small>(Clic para editar)</small>`;
        // Limpiar clases previas de riesgo y agregar la nueva
        btn.classList.remove(
            "btn-riesgo-bajo",
            "btn-riesgo-medio",
            "btn-riesgo-alto",
            "btn-riesgo-muy-alto",
        );
        btn.classList.add("evaluacion-completada", cssClassBtn);
        // Nota: Ya NO aplicamos estilos inline (style.backgroundColor) para dejar que el CSS actúe
        btn.style = "";
    }
    // 5. Cerrar modal
    gestionarModalEvaluacion(false);
    actualizarBotonEnviarSolicitud();
    // 6. SweetAlert
    Swal.fire({
        title: "Evaluación Guardada",
        html: `
            <div style="display: flex; flex-direction: column; align-items: center;">
                <div class="resultado-titulo">Nivel de Riesgo:</div>
                <div class="riesgo-badge ${cssClassBadge}">
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
                if (datosReunionConvoy.lider_convoy === c) {
                    option.selected = true;
                }
                liderSelect.appendChild(option);
            });
        }
        // Cargar datos guardados del checklist
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
                if (radio) {
                    radio.checked = true;
                }
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
            conductoresList.push(inputConductor.value.trim());
        }
    }
    return conductoresList;
}
function guardarPreConvoy() {
    const form = document.getElementById("formPreConvoy");
    const liderSelect = document.getElementById("liderConvoy");
    if (liderSelect && liderSelect.value.trim() === "") {
        Swal.fire({
            title: "Líder No Seleccionado",
            text: "Debe seleccionar un conductor como Líder de Convoy.",
            icon: "warning",
            confirmButtonColor: "var(--primary-blue)",
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
    let checklistCompleto = true;
    const data = {
        lider_convoy: liderSelect.value,
    };
    checklistItems.forEach((item) => {
        const checked = form.querySelector(
            `input[name="checklist_${item}"]:checked`,
        );
        if (!checked) {
            checklistCompleto = false;
        } else {
            data[item] = checked.value;
        }
    });
    if (!checklistCompleto) {
        Swal.fire({
            title: "Checklist Incompleto",
            text: "Debe responder sí o no a todos los elementos del checklist de seguridad.",
            icon: "warning",
            confirmButtonColor: "var(--primary-blue)",
        });
        return;
    }
    let noAprobado = false;
    let noItems = [];
    for (const key in data) {
        if (key !== "lider_convoy" && data[key] === "no") {
            noAprobado = true;
            noItems.push(key);
        }
    }
    if (noAprobado) {
        Swal.fire({
            title: "¡Advertencia de Seguridad!",
            html: `Se encontraron puntos de seguridad no confirmados (NO) en el checklist.<br><br>
                    Si continúa, el viaje será marcado con **ALTO RIESGO** y requerirá una aprobación superior.`,
            icon: "error",
            showCancelButton: true,
            confirmButtonText: "Guardar y Continuar (Alto Riesgo)",
            cancelButtonText: "Corregir Checklist (Recomendado)",
            confirmButtonColor: "var(--accent-red)",
            cancelButtonColor: "var(--primary-blue)",
        }).then((result) => {
            if (result.isConfirmed) {
                finalizarGuardadoPreConvoy(data, false);
            }
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
    let title = "Reunión Confirmada";
    let text = `La Reunión Pre-convoy ha sido registrada. La solicitud está lista para ser enviada.`;
    let icon = "success";
    if (!todoAprobado) {
        title = "Reunión Guardada con Advertencias";
        text = `La Reunión Pre-convoy ha sido registrada con **NOs**. El viaje será marcado como **ALTO RIESGO**.`;
        icon = "warning";
    }
    Swal.fire({
        title: title,
        text: text,
        icon: icon,
        confirmButtonColor: "var(--primary-blue)",
    });
}
function actualizarBotonEnviarSolicitud() {
    const btnEnviar = document.getElementById("btnEnviarSolicitud");
    if (!btnEnviar) return;
    if (!evaluacionRiesgoGuardada) {
        btnEnviar.disabled = true;
        btnEnviar.title = "Debe realizar la Evaluación del Viaje.";
        return;
    }
    // Resto de la lógica sigue igual...
    if (contadorUnidades <= 1) {
        btnEnviar.disabled = false;
        btnEnviar.title = "Enviar Solicitud";
    } else {
        if (reunionPreConvoyGuardada) {
            btnEnviar.disabled = false;
            btnEnviar.title = "Enviar Solicitud";
        } else {
            btnEnviar.disabled = true;
            btnEnviar.title =
                "Debe completar y confirmar la Reunión Pre-convoy.";
        }
    }
}

// ====================================================================
// FUNCIÓN: TOGGLE MEDICAMENTO DETALLE
// ====================================================================
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
            // Limpiar campo si se cambia a "No"
            const medicamentoNombre = document.getElementById(
                `medicamento-nombre-${unidadNumero}`,
            );
            if (medicamentoNombre) medicamentoNombre.value = "";
        }
    }
}

// ====================================================================
// FUNCIÓN: INICIALIZAR SELECTOR DE VEHÍCULOS (CLASES ACTUALIZADAS)
// ====================================================================
function inicializarSelectorVehiculo(unidadNumero) {
    const triggerId = `vehicle-trigger-${unidadNumero}`;
    const hiddenInputId = `vehicle-hidden-${unidadNumero}`;
    const listId = `vehicle-list-${unidadNumero}`;
    const trigger = document.getElementById(triggerId);
    const hiddenInput = document.getElementById(hiddenInputId);
    // Crear el contenedor de la lista si no existe (lo agregamos al body)
    let listContainer = document.getElementById(listId);
    if (!listContainer) {
        listContainer = document.createElement("div");
        listContainer.id = listId;
        listContainer.className = "vehicle-dropdown-list";
        document.body.appendChild(listContainer);
    }
    // --- FUNCIÓN PARA CONSTRUIR EL HTML (SE EJECUTA AL HACER CLIC) ---
    const construirLista = () => {
        let htmlContent = "";
        // Verificar si hay datos cargados
        if (vehiculosLigeros.length === 0 && vehiculosPesados.length === 0) {
            htmlContent = `<div style="padding:10px; color:red;">Cargando datos... o sin vehículos.</div>`;
        } else {
            // Grupo Ligeras
            if (vehiculosLigeros.length > 0) {
                htmlContent += `<div class="vehicle-group-title"><i class="fas fa-car"></i> Unidades Ligeras</div>`;
                vehiculosLigeros.forEach((v) => {
                    htmlContent += `<div class="vehicle-option" data-value="${v}">
                                        <i class="fas fa-car" style="color: var(--primary-blue);"></i> ${v}
                                    </div>`;
                });
            }
            // Grupo Pesadas
            if (vehiculosPesados.length > 0) {
                htmlContent += `<div class="vehicle-group-title"><i class="fas fa-truck"></i> Unidades Pesadas</div>`;
                vehiculosPesados.forEach((v) => {
                    htmlContent += `<div class="vehicle-option" data-value="${v}">
                                        <i class="fas fa-truck" style="color: var(--primary-orange);"></i> ${v}
                                    </div>`;
                });
            }
        }
        listContainer.innerHTML = htmlContent;
    };
    // --- FUNCIÓN DE POSICIONAMIENTO ---
    const positionDropdown = () => {
        if (!trigger || listContainer.style.display === "none") return;
        const rect = trigger.getBoundingClientRect();
        listContainer.style.width = rect.width + "px";
        listContainer.style.left = rect.left + "px";
        listContainer.style.top = rect.bottom + 5 + "px";
    };
    // --- EVENTO 1: ABRIR/CERRAR (TRIGGER) ---
    // Usamos .onclick para evitar múltiples listeners si la función se llama varias veces
    if (trigger) {
        trigger.onclick = (e) => {
            e.stopPropagation();
            // 1. Cerrar otros selectores abiertos
            document
                .querySelectorAll(".vehicle-dropdown-list")
                .forEach((el) => {
                    if (el.id !== listId) el.style.display = "none";
                });
            // 2. Toggle actual
            if (listContainer.style.display === "block") {
                listContainer.style.display = "none";
            } else {
                // AQUÍ ESTÁ LA MAGIA: Construimos la lista en este momento exacto
                construirLista();
                listContainer.style.display = "block";
                positionDropdown();
            }
        };
    }
    // --- EVENTO 2: SELECCIONAR OPCIÓN (DELEGACIÓN DE EVENTOS) ---
    // Usamos delegación en el contenedor padre en lugar de loops individuales
    listContainer.onclick = (e) => {
        e.stopPropagation();
        // Buscamos si el clic fue dentro de una opción
        const option = e.target.closest(".vehicle-option");
        if (option) {
            const valor = option.dataset.value;
            // Obtenemos el icono dentro de la opción para copiarlo
            const iconoElement = option.querySelector("i");
            const iconoHTML = iconoElement ? iconoElement.outerHTML : "";
            // Actualizar trigger visualmente
            trigger.innerHTML = `<span>${iconoHTML} ${valor}</span>`;
            trigger.style.color = "var(--dark-gray)";
            trigger.style.fontWeight = "600";
            // Actualizar input oculto
            hiddenInput.value = valor;
            console.log(
                `Vehículo seleccionado para unidad ${unidadNumero}:`,
                valor,
            );
            // Ejecutar lógica de negocio
            actualizarTipoVehiculoCustom(unidadNumero, valor);
            actualizarBotonReunionConvoy();
            // Cerrar lista
            listContainer.style.display = "none";
        }
    };
    // --- EVENTOS GLOBALES (SCROLL, RESIZE, CLICK OUTSIDE) ---
    // Usamos listeners nombrados o verificaciones para no duplicar lógica excesiva
    // (En este caso simple, mantenemos los listeners globales anónimos pero protegidos)
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
            if (listContainer && listContainer.style.display === "block")
                positionDropdown();
        },
        true,
    );
    window.addEventListener("resize", () => {
        if (listContainer && listContainer.style.display === "block")
            positionDropdown();
    });
}



// ====================================================================
// FUNCIÓN ACTUALIZAR TIPO VEHÍCULO (CAMBIO DE ETIQUETAS LIGERA/PESADA)
// ====================================================================
function actualizarTipoVehiculoCustom(unidadNumero, valorVehiculo) {
    const labelTipo = document.getElementById(`tipo-vehiculo-${unidadNumero}`);
    const inputVigenciaLic = document.getElementById(`vigencia-lic-${unidadNumero}`);

    if (!labelTipo) return;

    const tipo = clasificacionVehiculos[valorVehiculo] || "";

    // 1. Actualizar texto del Tipo de Unidad
    let tipoTexto = tipo;
    let esPesada = false;

    if (tipo === "Ligera") {
        tipoTexto = "Unidad Ligera";
        esPesada = false;
    } else if (tipo === "Pesada") {
        tipoTexto = "Unidad Pesada";
        esPesada = true;
    }

    labelTipo.textContent = tipoTexto;
    labelTipo.className = "tipo-vehiculo-text";

    if (tipo.toLowerCase().includes("ligera")) {
        labelTipo.classList.add("tipo-ligera");
    } else if (tipo.toLowerCase().includes("pesada")) {
        labelTipo.classList.add("tipo-pesada");
    }

    // 2. CAMBIO DE ETIQUETA: LICENCIA NORMAL VS FEDERAL
    // Buscamos el label asociado al input de licencia
    const containerLicencia = inputVigenciaLic?.closest('.hour-input-group');
    const labelLicencia = containerLicencia ? containerLicencia.querySelector('label') : null;

    if (labelLicencia) {
        if (esPesada) {
            // Si es pesada, pedimos Licencia Federal
            labelLicencia.innerHTML = `<i class="fas fa-id-card"></i> Vigencia Licencia Federal`;
            labelLicencia.style.color = "var(--primary-orange)"; // Opcional: Color distintivo
        } else {
            // Si es ligera o no definido, Licencia Normal
            labelLicencia.innerHTML = `<i class="fas fa-id-card"></i> Vigencia Licencia`;
            labelLicencia.style.color = ""; // Restaurar color
        }
    }

    // 3. Recargar datos del conductor (si ya hay uno seleccionado)
    // Esto es necesario para cambiar la fecha (de normal a federal) en el input
    const inputConductor = document.getElementById(`conductor-${unidadNumero}`);
    if (inputConductor && inputConductor.value) {
        actualizarDatosConductor(unidadNumero, inputConductor.value);
    }
}

// ====================================================================
// FUNCIÓN AGREGAR PASAJERO - MODIFICADA
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
    // Datos ocultos iniciales
    div.dataset.alcoholPct = "0.0"; // Solo porcentaje ahora
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
        `btn-add-pasajero-${unidadNumero}`,
    );
    if (btnAddPasajero && currentPasajeros + 1 >= MAX_PASAJEROS) {
        btnAddPasajero.disabled = true;
    }
}
// ====================================================================
// FUNCIÓN GESTIONAR ROL PASAJERO (UI MEJORADA CON VALIDACIÓN UNIFICADA)
// ====================================================================
function gestionarRolPasajero(unidad, index) {
    const fila = document.getElementById(`fila-p-${unidad}-${index}`);
    if (!fila) return;
    const nombre = document.getElementById(`p-nombre-${unidad}-${index}`)?.value;

    if (!nombre) {
        Swal.fire("Falta Nombre", "Escriba el nombre del pasajero antes de asignarle un rol.", "info");
        return;
    }

    // A. SI YA ES RELEVO -> Degradar
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
                // Borrar datos
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
                Swal.fire("Actualizado", "El usuario ahora es Pasajero.", "success");
            }
        });
        return;
    }

    // B. VALIDAR LÍMITE DE CONDUCTORES
    const container = document.getElementById(`pasajeros-unidad-${unidad}`);
    if (container) {
        const relevosExistentes = container.querySelectorAll(".es-relevo").length;
        if (relevosExistentes >= 1) {
            Swal.fire({
                title: "Límite de Conductores Alcanzado",
                html: `Ya existe un Segundo Conductor asignado.<br>Solo se permiten 2 conductores por unidad.`,
                icon: "warning",
                confirmButtonColor: "var(--primary-orange)",
            });
            return;
        }
    }

    // --- C. DETECTAR TIPO DE VEHÍCULO (LIGERA O PESADA) ---
    const tipoVehiculoDiv = document.getElementById(`tipo-vehiculo-${unidad}`);
    let esPesada = false;
    let labelLicenciaModal = '<i class="fas fa-id-card"></i> Vigencia Licencia';
    let labelCursoModal = '<i class="fas fa-calendar-alt"></i> Man. Defensivo (Ligera)';

    if (tipoVehiculoDiv && (tipoVehiculoDiv.textContent.includes("Pesada") || tipoVehiculoDiv.classList.contains("tipo-pesada"))) {
        esPesada = true;
        labelLicenciaModal = '<i class="fas fa-id-card"></i> Licencia Federal';
        labelCursoModal = '<i class="fas fa-calendar-alt"></i> Man. Defensivo (Pesada)';
    }

    // --- D. OBTENER DATOS DE TIEMPO GLOBALES ---
    const horaInicioViaje = document.getElementById("horaInicioViaje")?.value;
    const horaFinViaje = document.getElementById("horaFinViaje")?.value;
    const duracionViajeStr = calcularDiferenciaHoras(horaInicioViaje, horaFinViaje);

    // --- E. HTML DEL MODAL UI MEJORADO ---
    Swal.fire({
        title: `<div style="font-size: 1.1em; color: var(--primary-blue); border-bottom: 2px solid #e9ecef; padding-bottom: 10px;">Asignar Segundo Conductor:<br><span style="color: #333; font-weight: 700;">${nombre}</span></div>`,
        html: `
            <style>
                .swal-modal-grid-compact {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 12px 15px;
                    text-align: left;
                    margin-top: 5px;
                }
                .swal-field-group {
                    display: flex;
                    flex-direction: column;
                }
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
                    color: #495057;
                    transition: border-color 0.2s, box-shadow 0.2s;
                    background-color: #fff;
                }
                .swal-field-group input:focus, .swal-field-group select:focus {
                    border-color: #80bdff;
                    outline: 0;
                    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
                }
                .swal-section-title {
                    grid-column: span 2;
                    font-size: 0.85rem;
                    color: var(--primary-blue, #0056b3);
                    padding-bottom: 2px;
                    border-bottom: 1px dashed #dee2e6;
                    margin-top: 15px;
                    margin-bottom: 2px;
                    font-weight: 800;
                    text-transform: uppercase;
                }
                /* Sin !important para permitir cambios de color JS */
                .input-readonly-colored {
                    font-weight: bold;
                    background-color: #e9ecef;
                    cursor: not-allowed;
                }
                #swal-medicamento-detalle-container {
                    animation: fadeIn 0.3s ease-in-out;
                }
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateX(-10px); }
                    to { opacity: 1; transform: translateX(0); }
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
                    <input id="swal-total-hrs" type="text" placeholder="0:00" readonly style="font-weight: 800; font-size: 1.05em; background-color: #e9ecef;">
                    <div id="swal-warning-msg" style="color: #dc3545; font-size: 0.75rem; font-weight: 700; display: none; margin-top: 4px; position: absolute; bottom: -18px;">
                        <i class="fas fa-exclamation-triangle"></i> Excede 15 hrs sugeridas
                    </div>
                </div>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-save"></i> Guardar Conductor',
        confirmButtonColor: "var(--accent-green)",
        cancelButtonText: "Cancelar",
        width: "650px",

        // --- F. LÓGICA AL ABRIR EL MODAL (DID OPEN) ---
        didOpen: () => {
            flatpickr(".flatpickr-modal", configHora);

            // 1. Toggle Medicamento
            const medicamentoSelect = document.getElementById("swal-medicamento");
            const medicamentoDetalle = document.getElementById("swal-medicamento-detalle-container");
            if (medicamentoSelect && medicamentoDetalle) {
                medicamentoSelect.addEventListener("change", function () {
                    if (this.value === "si") {
                        medicamentoDetalle.style.display = "flex";
                    } else {
                        medicamentoDetalle.style.display = "none";
                        document.getElementById("swal-medicamento-nombre").value = "";
                    }
                });
            }

            // 2. CARGAR Y VALIDAR DOCUMENTACIÓN (USANDO FUNCIÓN UNIFICADA)
            const nombreConductor = document.getElementById(`p-nombre-${unidad}-${index}`).value;
            const data = datosConductoresGlobales[nombreConductor];

            const licInput = document.getElementById("swal-vigencia-lic");
            const manInput = document.getElementById("swal-vigencia-man");
            const licStatus = document.getElementById("swal-hidden-lic-status");
            const manStatus = document.getElementById("swal-hidden-man-status");

            if (data) {
                // USAR LA MISMA FUNCIÓN DE VALIDACIÓN QUE EN LA TABLA PRINCIPAL
                const validacion = validarDocumentacionConductor(data, esPesada);

                // Aplicar resultados
                licInput.value = validacion.licencia.mensaje;
                licStatus.value = validacion.licencia.estilo;
                aplicarEstiloPorValidacion(licInput, validacion.licencia.estilo);

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

            // 3. CALCULADORA DE HORAS
            const dormirInput = document.getElementById("swal-dormir");
            const levantarInput = document.getElementById("swal-levantar");
            const hrsDormidasInput = document.getElementById("swal-hrs-dormidas");
            const hrDespiertoInput = document.getElementById("swal-hr-despierto");
            const duracionViajeInput = document.getElementById("swal-duracion-viaje");
            const totalHrsInput = document.getElementById("swal-total-hrs");
            const warningMsg = document.getElementById("swal-warning-msg");

            function actualizarHorasModal() {
                const dormir = dormirInput.value;
                const levantar = levantarInput.value;

                // Dormido
                const totalMinutosDormir = parseTimeForCalculation(dormir);
                let totalMinutosLevantar = parseTimeForCalculation(levantar);

                if (totalMinutosDormir !== null && totalMinutosLevantar !== null) {
                    if (totalMinutosLevantar <= totalMinutosDormir) {
                        totalMinutosLevantar += 24 * 60;
                    }
                    const duracionMinutos = totalMinutosLevantar - totalMinutosDormir;
                    hrsDormidasInput.value = minutosAStringHora(duracionMinutos);
                } else {
                    hrsDormidasInput.value = "0:00";
                }

                // Despierto
                const minutosLevantar = parseTimeForCalculation(levantar);
                const minutosInicioViaje = parseTimeForCalculation(horaInicioViaje);

                if (minutosLevantar !== null && minutosInicioViaje !== null) {
                    let diferencia = minutosInicioViaje - minutosLevantar;
                    if (diferencia < 0) diferencia += 24 * 60;
                    hrDespiertoInput.value = minutosAStringHora(diferencia);
                } else {
                    hrDespiertoInput.value = "0:00";
                }

                // Total
                const minutosDespierto = stringHoraAMinutos(hrDespiertoInput.value);
                const minutosViaje = stringHoraAMinutos(duracionViajeInput.value);
                const totalMinutos = minutosDespierto + minutosViaje;

                totalHrsInput.value = minutosAStringHora(totalMinutos);

                // ALERTA VISUAL DE HORAS (900 min = 15 horas)
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

            if (dormirInput) dormirInput.addEventListener("change", actualizarHorasModal);
            if (levantarInput) levantarInput.addEventListener("change", actualizarHorasModal);
            actualizarHorasModal(); // Llamada inicial
        },

        // --- G. VALIDACIÓN AL GUARDAR (PRE CONFIRM) ---
  preConfirm: () => {
    const alcoholPct = document.getElementById("swal-alcohol-pct").value;
    const dormir = document.getElementById("swal-dormir").value;
    const levantar = document.getElementById("swal-levantar").value;
    const medicamento = document.getElementById("swal-medicamento").value;
    const medicamentoNombre = document.getElementById("swal-medicamento-nombre").value;

    // 1. Validaciones Básicas de Campos
    if (!alcoholPct || alcoholPct === "") {
        Swal.showValidationMessage("Ingrese el porcentaje de alcoholimetría");
        return false;
    }
    if (parseFloat(alcoholPct) > 0.4) {
        Swal.showValidationMessage("Alcoholimetría no apta (> 0.4)");
        return false;
    }
    if (!dormir || !levantar) {
        Swal.showValidationMessage("Complete las horas de dormir y despertar");
        return false;
    }
    if (!medicamento) {
        Swal.showValidationMessage("Seleccione si toma medicamento");
        return false;
    }
    if (medicamento === "si" && !medicamentoNombre) {
        Swal.showValidationMessage("Especifique el nombre del medicamento");
        return false;
    }

    // 2. VALIDACIÓN DE DOCUMENTACIÓN (BLOQUEANTE) - MISMA LÓGICA QUE EN TABLA
    const licStatus = document.getElementById("swal-hidden-lic-status").value;
    const manStatus = document.getElementById("swal-hidden-man-status").value;
    let erroresDocs = [];

    if (licStatus === "expired" || licStatus === "missing") {
        const tipoLic = esPesada ? "Licencia Federal" : "Licencia";
        const estadoTexto = licStatus === "expired" ? "VENCIDA" : "NO REGISTRADA";
        erroresDocs.push(`${tipoLic} (${estadoTexto})`);
    }

    if (manStatus === "expired" || manStatus === "missing") {
        const tipoCurso = esPesada ? "Curso Man. Def. Pesada" : "Curso Man. Def. Ligera";
        const estadoTexto = manStatus === "expired" ? "VENCIDO" : "NO REGISTRADO";
        erroresDocs.push(`${tipoCurso} (${estadoTexto})`);
    }

    if (erroresDocs.length > 0) {
        Swal.showValidationMessage(`NO SE PUEDE ASIGNAR:<br>• ${erroresDocs.join("<br>• ")}`);
        return false;
    }

    // 3. RETORNO DE DATOS LIMPIOS
    return {
        alcoholPct, dormir, levantar,
        presionValor: document.getElementById("swal-presion-valor").value,
        medicamento, medicamentoNombre,
        vigenciaLic: document.getElementById("swal-vigencia-lic").value,
        vigenciaMan: document.getElementById("swal-vigencia-man").value,
        hrsDormidas: document.getElementById("swal-hrs-dormidas").value,
        hrDespierto: document.getElementById("swal-hr-despierto").value,
        duracionViaje: document.getElementById("swal-duracion-viaje").value,
        totalHrs: document.getElementById("swal-total-hrs").value,
    };
},
    }).then((result) => {
        if (result.isConfirmed) {
            const datos = result.value;

            // Guardar en Dataset para la fila principal
            fila.dataset.alcoholPct = datos.alcoholPct;
            fila.dataset.dormir = datos.dormir;
            fila.dataset.levantar = datos.levantar;
            fila.dataset.presionValor = datos.presionValor;
            fila.dataset.medicamento = datos.medicamento;
            fila.dataset.medicamentoNombre = datos.medicamentoNombre;
            fila.dataset.vigenciaLic = datos.vigenciaLic;
            fila.dataset.vigenciaMan = datos.vigenciaMan;
            fila.dataset.hrsDormidas = datos.hrsDormidas;
            fila.dataset.hrDespierto = datos.hrDespierto;
            fila.dataset.duracionViaje = datos.duracionViaje;
            fila.dataset.totalHrs = datos.totalHrs;

            // Actualizar Icono UI en la tabla
            actualizarIconoPasajero(fila, index, unidad);
            actualizarBotonVerConductor2(unidad);

            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            Toast.fire({ icon: "success", title: "Segundo Conductor Asignado Correctamente" });
        }
    });
}
// ====================================================================
// FUNCIÓN MOSTRAR INFO CONDUCTOR 2 (MODAL) - MODIFICADA
// ====================================================================
function mostrarInfoConductor2(unidadNumero) {
    const container = document.getElementById(`pasajeros-unidad-${unidadNumero}`);
    const filaRelevo = container.querySelector(".pasajero-input-group.es-relevo");

    if (!filaRelevo) {
        Swal.fire({
            title: "No hay Segundo Conductor",
            text: "No se ha asignado un Segundo Conductor para esta unidad.",
            icon: "info",
            confirmButtonColor: "var(--primary-orange)",
        });
        return;
    }

    // --- DETECTAR TIPO DE VEHÍCULO PARA LOS LABELS ---
    const tipoVehiculoDiv = document.getElementById(`tipo-vehiculo-${unidadNumero}`);

    // Valores por defecto
    let labelLicenciaModal = "Vigencia Licencia";
    let labelCursoModal = "Vigencia Man. Def. Ligera";

    if (tipoVehiculoDiv && (tipoVehiculoDiv.textContent.includes("Pesada") || tipoVehiculoDiv.classList.contains("tipo-pesada"))) {
        labelLicenciaModal = "Vigencia Licencia Federal";
        labelCursoModal = "Vigencia Man. Def. Pesada"; // <--- CAMBIO AQUÍ
    }

    const pIdx = filaRelevo.dataset.index;
    const pNombre = document.getElementById(`p-nombre-${unidadNumero}-${pIdx}`).value;

    // Dataset values
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

    const alcoholColor = parseFloat(pAlcoholPct) === 0 ? "#28a745" : "#dc3545";
    const medicamentoColor = pMedicamento === "si" ? "#ffc107" : "#28a745";

    Swal.fire({
        title: `Información del Segundo Conductor`,
        html: `
            <div style="text-align: left; max-width: 600px; margin: 0 auto;">
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    <h3 style="color: var(--primary-blue); margin-top: 0;">${pNombre}</h3>
                    <div style="display: flex; gap: 15px; margin-top: 10px;">
                        <div>
                            <strong>${labelLicenciaModal}:</strong><br>
                            <span style="color: ${pVigenciaLic.includes("No registrada") ? "#dc3545" : "#28a745"}">${pVigenciaLic || "No registrada"}</span>
                        </div>
                        <div>
                            <strong>${labelCursoModal}:</strong><br>
                            <span style="color: ${pVigenciaMan.includes("No registrada") ? "#dc3545" : "#28a745"}">${pVigenciaMan || "No registrada"}</span>
                        </div>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <strong><i class="fas fa-wind"></i> Alcoholimetría:</strong><br>
                        <span style="color: ${alcoholColor}">${pAlcoholPct}%</span>
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
                        <span style="color: ${medicamentoColor}">${pMedicamento === "si" ? "Sí" : "No"} ${pMedicamento === "si" ? `(${pMedicamentoNombre})` : ""}</span>
                    </div>
                </div>
                <div style="background: #e9ecef; padding: 10px; border-radius: 6px; margin-top: 15px;">
                    <h4 style="color: var(--primary-blue); margin-top: 0; margin-bottom: 10px;">Horas de Conducción:</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <strong>Hr Despierto:</strong><br>
                            <span>${pHrDespierto || "No calculado"}</span>
                        </div>
                        <div>
                            <strong>Duración Viaje:</strong><br>
                            <span>${pDuracionViaje || "No calculado"}</span>
                        </div>
                        <div style="grid-column: span 2;">
                            <strong>Total Hrs:</strong><br>
                            <span style="font-weight: bold; color: var(--primary-blue);">${pTotalHrs || "No calculado"}</span>
                        </div>
                    </div>
                </div>
            </div>
        `,
        icon: "info",
        confirmButtonText: "Cerrar",
        confirmButtonColor: "var(--primary-blue)",
        width: 650,
    });
}
// ====================================================================
// FUNCIÓN ELIMINAR PASAJERO (PROTECCIÓN PRINCIPAL)
// ====================================================================
function eliminarPasajero(unidadNumero, pasajeroIndex) {
    const fila = document.getElementById(
        `fila-p-${unidadNumero}-${pasajeroIndex}`,
    );
    if (fila && fila.dataset.esPrincipal === "true") {
        Swal.fire({
            title: "Acción No Permitida",
            text: "No puedes eliminar al Conductor Principal de la lista. Debes rotarlo a la posición de manejo si deseas cambiarlo, pero el registro del responsable de unidad debe mantenerse.",
            icon: "error",
        });
        return;
    }
    const container = document.getElementById(
        `pasajeros-unidad-${unidadNumero}`,
    );
    if (
        container &&
        container.querySelectorAll(".pasajero-input-group").length <= 1
    )
        return;
    if (fila) fila.remove();
    const remainingPasajeros = container.querySelectorAll(
        ".pasajero-input-group",
    );
    remainingPasajeros.forEach((group, index) => {
        const newIndex = index + 1;
    });
    if (remainingPasajeros.length < MAX_PASAJEROS) {
        document.getElementById(`btn-add-pasajero-${unidadNumero}`).disabled =
            false;
    }
    actualizarBotonVerConductor2(unidadNumero);
}

// ====================================================================
// FUNCIÓN FALTANTE: ACTUALIZAR VISIBILIDAD BOTÓN CONDUCTOR 2
// ====================================================================
function actualizarBotonVerConductor2(unidadNumero) {
    // 1. Buscamos la fila de la unidad y el botón específico
    const filaUnidad = document.getElementById(`unidad-${unidadNumero}`);
    if (!filaUnidad) return;

    const btnVer = filaUnidad.querySelector('.btn-ver-conductor2');
    if (!btnVer) return;

    // 2. Buscamos el contenedor de pasajeros
    const container = document.getElementById(`pasajeros-unidad-${unidadNumero}`);

    // 3. Verificamos si existe AL MENOS UN pasajero con la clase "es-relevo"
    // (Esta clase se agrega en la función actualizarIconoPasajero)
    const hayRelevo = container ? container.querySelector('.es-relevo') : null;

    // 4. Si hay relevo mostramos el botón, si no, lo ocultamos
    if (hayRelevo) {
        btnVer.style.display = 'inline-flex'; // O 'inline-block' según tu CSS
    } else {
        btnVer.style.display = 'none';
    }
}

// ===================================================================
// AUXILIARES
// ====================================================================
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
    if (fila.dataset.esPrincipal === "true") {
        if (badgeStar) badgeStar.style.display = "inline-block";
        if (badgeStar) badgeStar.title = "Conductor Principal (Descansando)";
    } else {
        if (badgeStar) badgeStar.style.display = "none";
    }
}
function formatearPorcentaje(input) {
    let val = input.value;
    if (val !== "") {
        input.value = parseFloat(val).toFixed(1);
    }
}
function actualizarBotonesPasajeros(unidadNumero) {
    const container = document.getElementById(
        `pasajeros-unidad-${unidadNumero}`,
    );
    if (!container) return;
    const grupos = container.querySelectorAll(".pasajero-input-group");
    if (grupos.length === 1) {
        const btn = grupos[0].querySelector(".btn-remove-pasajero");
        if (btn) {
            btn.style.opacity = "0.3";
            btn.style.pointerEvents = "none";
        }
    } else {
        grupos.forEach((grupo) => {
            const btn = grupo.querySelector(".btn-remove-pasajero");
            if (btn) {
                btn.style.opacity = "1";
                btn.style.pointerEvents = "auto";
            }
        });
    }
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
    if (boton) {
        if (contadorUnidades >= MAX_UNIDADES) {
            boton.disabled = true;
            boton.textContent = `Límite de ${MAX_UNIDADES} Unidades Alcanzado`;
        } else {
            const restantes = MAX_UNIDADES - contadorUnidades;
            boton.innerHTML = `<i class="fas fa-plus-circle"></i> Agregar Unidad (${restantes} restante${restantes !== 1 ? "s" : ""
                })`;
        }
    }
}
function eliminarUnidad(numero) {
    Swal.fire({
        title: "¿Eliminar unidad?",
        text: "Se eliminarán todos los datos de esta unidad y sus pasajeros.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "var(--accent-red)",
        cancelButtonColor: "var(--primary-blue)",
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
        const filaId = `unidad-${nuevoNumero}`;
        fila.id = filaId;
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
        const oldLicenciaNum = fila.querySelector(
            `#licencia-num-${numeroAnterior}`,
        );
        const oldVigenciaLic = fila.querySelector(
            `#vigencia-lic-${numeroAnterior}`,
        );
        const oldVigenciaMan = fila.querySelector(
            `#vigencia-man-${numeroAnterior}`,
        );
        if (oldLicenciaNum) oldLicenciaNum.id = `licencia-num-${nuevoNumero}`;
        if (oldVigenciaLic) oldVigenciaLic.id = `vigencia-lic-${nuevoNumero}`;
        if (oldVigenciaMan) oldVigenciaMan.id = `vigencia-man-${nuevoNumero}`;
        fila.querySelector(".unidad-conductor").id = `conductor-${nuevoNumero}`;
        fila.querySelector("#autocomplete-list-" + numeroAnterior).id =
            `autocomplete-list-${nuevoNumero}`;
        inicializarAutocompleteConductor(nuevoNumero);
        const btnEliminar = fila.querySelector(".btn-accion.eliminar");
        if (btnEliminar) {
            btnEliminar.setAttribute(
                "onclick",
                `eliminarUnidad(${nuevoNumero})`,
            );
        }
        const btnInspeccion = fila.querySelector(
            ".btn-inspeccion, .btn-submit, .btn-inspeccion-aprobado",
        );
        if (btnInspeccion) {
            btnInspeccion.id = `btn-inspeccion-${nuevoNumero}`;
            btnInspeccion.setAttribute(
                "onclick",
                `abrirInspeccion(${nuevoNumero})`,
            );
            if (btnInspeccion.dataset.aprobado === "true") {
                btnInspeccion.innerHTML = '<i class="fas fa-check-circle"></i>';
            } else {
                btnInspeccion.innerHTML = '<i class="fas fa-clipboard"></i>';
            }
        }
        const oldDormir = document.getElementById(`dormir-${numeroAnterior}`);
        const oldLevantar = document.getElementById(
            `levantar-${numeroAnterior}`,
        );
        const oldTotalDormidas = document.getElementById(
            `total-hrs-dormidas-${numeroAnterior}`,
        );
        const oldHrsDespierto = document.getElementById(
            `horas-despierto-${numeroAnterior}`,
        );
        const oldHrsViaje = document.getElementById(
            `horas-viaje-${numeroAnterior}`,
        );
        const oldTotalFinalizar = document.getElementById(
            `total-hrs-finalizar-${numeroAnterior}`,
        );
        const tipoVehiculoDiv = document.getElementById(
            `tipo-vehiculo-${numeroAnterior}`,
        );
        if (oldDormir) oldDormir.id = `dormir-${nuevoNumero}`;
        if (oldLevantar) oldLevantar.id = `levantar-${nuevoNumero}`;
        if (oldTotalDormidas)
            oldTotalDormidas.id = `total-hrs-dormidas-${nuevoNumero}`;
        if (oldHrsDespierto)
            oldHrsDespierto.id = `horas-despierto-${nuevoNumero}`;
        if (oldHrsViaje) oldHrsViaje.id = `horas-viaje-${nuevoNumero}`;
        if (oldTotalFinalizar)
            oldTotalFinalizar.id = `total-hrs-finalizar-${nuevoNumero}`;
        if (tipoVehiculoDiv)
            tipoVehiculoDiv.id = `tipo-vehiculo-${nuevoNumero}`;
        // Actualizar IDs de medicamento
        const oldMedicamento = document.getElementById(
            `medicamento-${numeroAnterior}`,
        );
        const oldMedicamentoNombre = document.getElementById(
            `medicamento-nombre-${numeroAnterior}`,
        );
        const oldMedicamentoDetalle = document.getElementById(
            `medicamento-detalle-${numeroAnterior}`,
        );
        if (oldMedicamento) oldMedicamento.id = `medicamento-${nuevoNumero}`;
        if (oldMedicamentoNombre)
            oldMedicamentoNombre.id = `medicamento-nombre-${nuevoNumero}`;
        if (oldMedicamentoDetalle)
            oldMedicamentoDetalle.id = `medicamento-detalle-${nuevoNumero}`;
        // Actualizar IDs de alcohol y presión
        const oldAlcoholPct = document.getElementById(
            `alcohol-pct-${numeroAnterior}`,
        );
        const oldPresionValor = document.getElementById(
            `presion-valor-${numeroAnterior}`,
        );
        if (oldAlcoholPct) oldAlcoholPct.id = `alcohol-pct-${nuevoNumero}`;
        if (oldPresionValor)
            oldPresionValor.id = `presion-valor-${nuevoNumero}`;
        // Reasignar eventos
        fila.querySelector(".unidad-hora-dormir").addEventListener(
            "change",
            function () {
                calcularHorasDormidas(nuevoNumero);
                calcularHorasViaje(nuevoNumero);
                actualizarBotonReunionConvoy();
            },
        );
        fila.querySelector(".unidad-hora-levantar").addEventListener(
            "change",
            function () {
                calcularHorasDormidas(nuevoNumero);
                calcularHorasViaje(nuevoNumero);
                actualizarBotonReunionConvoy();
            },
        );
        // Reasignar evento de medicamento
        const medicamentoSelect = document.getElementById(
            `medicamento-${nuevoNumero}`,
        );
        if (medicamentoSelect) {
            medicamentoSelect.setAttribute(
                "onchange",
                `toggleMedicamentoDetalle(${nuevoNumero})`,
            );
        }
        calcularHorasViaje(nuevoNumero);
        const pasajeroContainer = fila.querySelector(".pasajero-container");
        if (pasajeroContainer) {
            pasajeroContainer.id = `pasajeros-unidad-${nuevoNumero}`;
            const btnAddPasajero = fila.querySelector(".btn-add-pasajero");
            btnAddPasajero.id = `btn-add-pasajero-${nuevoNumero}`;
            btnAddPasajero.setAttribute(
                "onclick",
                `agregarPasajero(${nuevoNumero})`,
            );
            pasajeroContainer
                .querySelectorAll(".pasajero-input-group")
                .forEach((group, pIndex) => {
                    const newIndex = pIndex + 1;
                    const input = group.querySelector("input");
                    const removeBtn = group.querySelector(
                        ".btn-remove-pasajero",
                    );
                    group.setAttribute("data-p-index", newIndex);
                    input.setAttribute(
                        "name",
                        `unidad[${nuevoNumero}][pasajeros][p${newIndex}]`,
                    );
                    if (removeBtn) {
                        removeBtn.setAttribute(
                            "onclick",
                            `eliminarPasajero(${nuevoNumero}, ${newIndex})`,
                        );
                    }
                });
            actualizarBotonesPasajeros(nuevoNumero);
        }
        const inputs = fila.querySelectorAll("input, select");
        inputs.forEach((input) => {
            const name = input.getAttribute("name");
            if (name && name.startsWith("unidad")) {
                const newName = name.replace(
                    /unidad\[\d+\]/,
                    `unidad[${nuevoNumero}]`,
                );
                input.setAttribute("name", newName);
            }
        });
    });
    Object.assign(datosInspeccionLigera, nuevosDatosInspeccionLigera);
    Object.assign(datosInspeccionPesada, nuevosDatosInspeccionPesada);
    actualizarContadorUnidades();
    actualizarLabelTipoUnidad();
    actualizarBotonAgregar();
    actualizarBotonReunionConvoy();
}

// ====================================================================
// ENVÍO DE SOLICITUD - MODIFICADO PARA NUEVAS VALIDACIONES
// ====================================================================
function enviarSolicitud() {
    const form = document.getElementById("formViaje");
    if (!form.checkValidity()) {
        form.reportValidity();
        Swal.fire({
            title: "Faltan Campos",
            text: "Por favor, llene todos los campos requeridos en la sección de Información General y Detalles del Trayecto.",
            icon: "warning",
            confirmButtonColor: "var(--primary-blue)",
        });
        return;
    }
    const destinoSelector = document.getElementById("destinoPredefinido");
    const destinoEspecifico = document.getElementById("destinoEspecifico");
    if (
        destinoSelector.value === "" ||
        (destinoSelector.value === "Otro" &&
            destinoEspecifico.value.trim() === "")
    ) {
        Swal.fire({
            title: "Destino no especificado",
            text: "Por favor, seleccione un destino de la lista o especifique uno en el campo de texto.",
            icon: "warning",
            confirmButtonColor: "var(--primary-blue)",
        });
        if (destinoSelector.value === "") {
            destinoSelector.focus();
        } else {
            destinoEspecifico.focus();
        }
        return;
    }
    const tieneParadas = document.querySelector(
        'input[name="tiene_paradas"]:checked',
    ).value;
    if (tieneParadas === "si") {
        const listaParadas = document.getElementById("listaParadas");
        if (listaParadas.children.length === 0) {
            Swal.fire({
                title: "Paradas Requeridas",
                text: 'Indicó que realizaría paradas, pero no ha agregado ninguna. Por favor agregue al menos una o cambie la opción a "No".',
                icon: "warning",
                confirmButtonColor: "var(--primary-blue)",
            });
            return;
        }
        let paradasCompletas = true;
        listaParadas.querySelectorAll("input, select").forEach((input) => {
            if (input.value.trim() === "") {
                paradasCompletas = false;
            }
        });
        if (!paradasCompletas) {
            Swal.fire({
                title: "Datos de Paradas Incompletos",
                text: "Por favor, complete todos los campos de Propósito y Ubicación para las paradas agregadas.",
                icon: "warning",
                confirmButtonColor: "var(--primary-blue)",
            });
            return;
        }
    }
    if (contadorUnidades === 0) {
        Swal.fire({
            title: "Sin unidades",
            text: "Debe agregar al menos una unidad vehicular y su conductor.",
            icon: "warning",
            confirmButtonColor: "var(--primary-blue)",
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
                    if (input.value.trim() === "") {
                        unidadesCompletas = false;
                        unidadConProblema = i;
                    }
                    if (
                        input.id.startsWith("vigencia-lic-") &&
                        input.value.trim() === ""
                    ) {
                        unidadesCompletas = false;
                        unidadConProblema = i;
                    }
                },
            );
            // Validar medicamento si es "Sí"
            const medicamentoSelect = document.getElementById(
                `medicamento-${i}`,
            );
            const medicamentoNombre = document.getElementById(
                `medicamento-nombre-${i}`,
            );
            if (
                medicamentoSelect &&
                medicamentoSelect.value === "si" &&
                (!medicamentoNombre || medicamentoNombre.value.trim() === "")
            ) {
                unidadesCompletas = false;
                unidadConProblema = i;
            }
        }
        const pasajeroContainer = document.getElementById(
            `pasajeros-unidad-${i}`,
        );
        if (pasajeroContainer) {
            pasajeroContainer
                .querySelectorAll("input[required]")
                .forEach((input) => {
                    if (input.value.trim() === "") {
                        unidadesCompletas = false;
                        unidadConProblema = i;
                    }
                });
        }
        if (!inspeccionesAprobadas || !unidadesCompletas) {
            break;
        }
    }
    if (!unidadesCompletas) {
        Swal.fire({
            title: "Información de Unidades Incompleta",
            html: `Asegúrese de **seleccionar un conductor de la lista** y llenar todos los campos requeridos de la Unidad **#${unidadConProblema}**, incluyendo la **Vigencia Man. Def.** y los **pasajeros** con nombre completo.`,
            icon: "warning",
            confirmButtonColor: "var(--primary-blue)",
        });
        return;
    }
    if (!inspeccionesAprobadas) {
        Swal.fire({
            title: "Inspección Pendiente/Incompleta",
            text: `La Unidad #${unidadConProblema} debe tener la inspección vehicular realizada y **aprobada** antes de enviar la solicitud.`,
            icon: "error",
            confirmButtonColor: "var(--primary-blue)",
        });
        return;
    }
    if (contadorUnidades > 1 && !reunionPreConvoyGuardada) {
        Swal.fire({
            title: "Reunión Pre-Convoy Requerida",
            text: "Al ser un Convoy, debe realizar y confirmar la Reunión Pre-convoy antes de enviar la solicitud.",
            icon: "error",
            confirmButtonColor: "var(--primary-blue)",
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
        confirmButtonColor: "var(--primary-blue)",
        cancelButtonColor: "var(--medium-gray)",
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: "¡Solicitud enviada!",
                html: `<p>Tu solicitud <strong>${document.getElementById("codigoViaje").textContent
                    }</strong> ha sido enviada exitosamente. Será revisada por el aprobador.</p>`,
                icon: "success",
                confirmButtonText: "Aceptar",
                confirmButtonColor: "var(--primary-blue)",
            }).then(() => {
                generarCodigoViaje(true);
                document
                    .getElementById("modalFormulario")
                    .classList.remove("active");
                document.body.style.overflow = "auto";
                limpiarFormulario();
            });
        }
    });
}
