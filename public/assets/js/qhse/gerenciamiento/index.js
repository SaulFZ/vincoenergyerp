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

// 🚀 CLASIFICACIÓN DE VEHÍCULOS
const clasificacionVehiculos = {
    // Unidades Ligeras
    "VH-105": "Unidad Ligera",
    "VH-106": "Unidad Ligera",
    "VH-107": "Unidad Ligera",
    "VH-108": "Unidad Ligera",
    "VH-109": "Unidad Ligera",
    "VH-110": "Unidad Ligera",
    "VH-111": "Unidad Ligera",
    "VH-112": "Unidad Ligera",
    "VH-113": "Unidad Ligera",
    "VH-114": "Unidad Ligera",
    "VH-115": "Unidad Ligera",
    "VH-116": "Unidad Ligera",

    // Unidades Pesadas
    "VH-201": "Unidad Pesada",
    "VH-202": "Unidad Pesada",
    "VH-204": "Unidad Pesada",
    "VH-206": "Unidad Pesada",
    "VH-207": "Unidad Pesada",
    "VH-208": "Unidad Pesada",
    "VH-209": "Unidad Pesada",
    "VH-210": "Unidad Pesada",
    "VH-211": "Unidad Pesada",
    "VH-212": "Unidad Pesada"
};

// Datos de ejemplo para vehículos
const vehiculos = [
    "VH-105", "VH-106", "VH-107", "VH-108", "VH-109", "VH-110", "VH-111", "VH-112",
    "VH-113", "VH-114", "VH-115", "VH-116", "VH-201", "VH-202", "VH-204", "VH-206",
    "VH-207", "VH-208", "VH-209", "VH-210", "VH-211", "VH-212"
];

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
    locale: flatpickr.l10ns.es
};

// ====================================================================
// FUNCIONES DE CÁLCULO DE HORAS
// ====================================================================
function minutosAStringHora(totalMinutos) {
    if (totalMinutos === null || isNaN(totalMinutos)) return "0:00";
    const horas = Math.floor(totalMinutos / 60);
    const minutos = totalMinutos % 60;
    const minutosStr = minutos.toString().padStart(2, '0');
    return `${horas}:${minutosStr}`;
}

function stringHoraAMinutos(timeStr) {
    if (!timeStr) return 0;
    const partes = timeStr.split(':');
    if (partes.length !== 2) return 0;
    const h = parseInt(partes[0]) || 0;
    const m = parseInt(partes[1]) || 0;
    return (h * 60) + m;
}

function calcularDiferenciaHoras(hora1, hora2) {
    if (!hora1 || !hora2) return "0:00";
    const minutos1 = parseTimeForCalculation(hora1);
    const minutos2 = parseTimeForCalculation(hora2);
    if (minutos1 === null || minutos2 === null) return "0:00";
    let diferencia = minutos2 - minutos1;
    if (diferencia < 0) {
        diferencia += (24 * 60);
    }
    return minutosAStringHora(diferencia);
}

function calcularHorasViaje(unidadNumero) {
    const horaInicioViaje = document.getElementById('horaInicioViaje').value;
    const horaFinViaje = document.getElementById('horaFinViaje').value;
    const horaLevantar = document.getElementById(`levantar-${unidadNumero}`).value;

    if (horaLevantar && horaInicioViaje) {
        const horasDespiertoStr = calcularDiferenciaHoras(horaLevantar, horaInicioViaje);
        const inputDespierto = document.getElementById(`horas-despierto-${unidadNumero}`);
        if (inputDespierto) {
            inputDespierto.value = horasDespiertoStr;
        }
    }

    if (horaInicioViaje && horaFinViaje) {
        const duracionViajeStr = calcularDiferenciaHoras(horaInicioViaje, horaFinViaje);
        const inputDuracion = document.getElementById(`horas-viaje-${unidadNumero}`);
        if (inputDuracion) {
            inputDuracion.value = duracionViajeStr;
        }
    }

    calcularTotalHoras(unidadNumero);
}

function calcularTotalHoras(unidadNumero) {
    const inputDespierto = document.getElementById(`horas-despierto-${unidadNumero}`);
    const inputDuracion = document.getElementById(`horas-viaje-${unidadNumero}`);
    const totalInput = document.getElementById(`total-hrs-finalizar-${unidadNumero}`);

    if (inputDespierto && inputDuracion && totalInput) {
        const minutosDespierto = stringHoraAMinutos(inputDespierto.value);
        const minutosViaje = stringHoraAMinutos(inputDuracion.value);
        const totalMinutos = minutosDespierto + minutosViaje;
        const totalHorasStr = minutosAStringHora(totalMinutos);
        totalInput.value = totalHorasStr;

        // ⚠️ Lógica de advertencia (> 14 horas)
        if (totalMinutos > 840) {
            totalInput.style.backgroundColor = 'var(--accent-red)';
            totalInput.style.color = 'var(--white)';
            if (!totalInput.dataset.warningShown) {
                Swal.fire({
                    title: '¡Advertencia de Horas!',
                    html: `La Unidad <strong>${unidadNumero}</strong> acumulará <strong>${totalHorasStr} horas</strong> totales.<br>Esto excede el límite recomendado de 14 horas.`,
                    icon: 'warning',
                    confirmButtonColor: 'var(--primary-blue)'
                });
                totalInput.dataset.warningShown = 'true';
            }
        } else {
            totalInput.style.backgroundColor = 'var(--light-gray)';
            totalInput.style.color = 'var(--dark-gray)';
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
    const horaLevantarInput = document.getElementById(`levantar-${unidadNumero}`);
    const totalDormidasInput = document.getElementById(`total-hrs-dormidas-${unidadNumero}`);

    const dormir = horaDormirInput ? horaDormirInput.value : null;
    const levantar = horaLevantarInput ? horaLevantarInput.value : null;

    const totalMinutosDormir = parseTimeForCalculation(dormir);
    let totalMinutosLevantar = parseTimeForCalculation(levantar);

    if (totalMinutosDormir !== null && totalMinutosLevantar !== null) {
        if (totalMinutosLevantar <= totalMinutosDormir) {
            totalMinutosLevantar += 24 * 60;
        }
        const duracionMinutos = totalMinutosLevantar - totalMinutosDormir;
        totalDormidasInput.value = minutosAStringHora(duracionMinutos);
    } else {
        totalDormidasInput.value = '0:00';
    }
}

// ====================================================================
// INICIALIZACIÓN
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

    flatpickr("#fechaInicioViaje", configFecha);
    flatpickr("#fechaFinViaje", configFecha);
    flatpickr("#horaInicioViaje", configHora);
    flatpickr("#horaFinViaje", configHora);
}

// ====================================================================
// FUNCIÓN PARA CARGAR CONDUCTORES DESDE LA BASE DE DATOS
// ====================================================================
async function cargarConductoresDesdeBD() {
    try {
        const response = await fetch('/qhse/gerenciamiento/conductores');
        if (!response.ok) throw new Error('Error al cargar conductores');

        const data = await response.json();

        conductoresGlobales = data.conductores || [];
        datosConductoresGlobales = data.datosConductores || {};

        console.log('Conductores cargados:', conductoresGlobales.length);
    } catch (error) {
        console.error('Error cargando conductores:', error);
        // Si hay error, usar datos por defecto (solo para desarrollo)
        conductoresGlobales = ["Juan Pérez González", "María González Sánchez", "Carlos Rodríguez López"];
        datosConductoresGlobales = {
            "Juan Pérez González": {
                vigencia: "2026-05-10",
                manDefVigencia: "2026-10-01"
            },
            "María González Sánchez": {
                vigencia: "2025-12-01",
                manDefVigencia: "2025-11-28"
            },
            "Carlos Rodríguez López": {
                vigencia: "2027-01-20",
                manDefVigencia: "2027-01-15"
            }
        };

        Swal.fire({
            title: 'Advertencia',
            text: 'No se pudieron cargar los conductores. Usando datos de ejemplo.',
            icon: 'warning',
            timer: 3000
        });
    }
}

document.addEventListener('DOMContentLoaded', async function () {
    // Cargar conductores desde la base de datos
    await cargarConductoresDesdeBD();

    // Establecer fecha actual
    const hoy = new Date();
    const fechaDisplay = hoy.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
    const fechaHidden = hoy.toISOString().split('T')[0];

    // Los campos de solicitante y departamento ahora vienen del backend
    // No los sobrescribimos aquí

    document.querySelectorAll('.nav-link-viajes').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelectorAll('.nav-link-viajes').forEach(item => {
                item.classList.remove('active');
            });
            this.classList.add('active');
        });
    });

    const destinoSelector = document.getElementById('destinoPredefinido');
    const destinoEspecifico = document.getElementById('destinoEspecifico');

    destinoSelector.addEventListener('change', function () {
        if (this.value === 'Otro') {
            destinoEspecifico.required = true;
        } else {
            destinoEspecifico.required = false;
        }
    });

    if (destinoSelector.value !== 'Otro') {
        destinoEspecifico.required = false;
    }

    inicializarFlatpickr();

    document.getElementById('formViaje').addEventListener('submit', function (e) {
        e.preventDefault();
        enviarSolicitud();
    });

    generarCodigoViaje(false);
    actualizarLabelTipoUnidad();
    actualizarBotonReunionConvoy();
    actualizarBotonEvaluacion();

    document.getElementById('horaInicioViaje').addEventListener('change', function () {
        calcularHorasViajeParaTodasUnidades();
    });

    document.getElementById('horaFinViaje').addEventListener('change', function () {
        calcularHorasViajeParaTodasUnidades();
    });
});

// ====================================================================
// FUNCIONES DE AUTOGESTIÓN DE CONDUCTOR (MODIFICADA PARA USAR BD)
// ====================================================================
function inicializarAutocompleteConductor(unidadNumero, esPasajero = false, pasajeroIndex = null) {
    const inputId = esPasajero ? `p-nombre-${unidadNumero}-${pasajeroIndex}` : `conductor-${unidadNumero}`;
    const listId = esPasajero ? `p-autocomplete-${unidadNumero}-${pasajeroIndex}` : `autocomplete-list-${unidadNumero}`;

    const input = document.getElementById(inputId);
    const listContainer = document.getElementById(listId);

    if (!input || !listContainer) return;

    let currentFocus = -1;

    const updateList = () => {
        const val = input.value;
        listContainer.innerHTML = '';
        currentFocus = -1;

        if (!val) {
            if (input.dataset.conductorSeleccionado !== undefined) {
                if (!esPasajero) {
                    actualizarDatosConductor(unidadNumero, '');
                }
            }
            delete input.dataset.conductorSeleccionado;
            return false;
        }

        const filtered = conductoresGlobales.filter(c =>
            c.toUpperCase().includes(val.toUpperCase())
        );

        if (filtered.length === 0) {
            if (!esPasajero) {
                actualizarDatosConductor(unidadNumero, '');
            }
            listContainer.innerHTML =
                `<div class="autocomplete-item" style="color: var(--accent-red); font-weight: normal; cursor: default;">No encontrado</div>`;
        } else {
            filtered.forEach((c, index) => {
                const item = document.createElement('div');
                item.classList.add('autocomplete-item');
                item.innerHTML = c;

                item.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    input.value = c;
                    input.dataset.conductorSeleccionado = c;
                    listContainer.innerHTML = '';

                    if (!esPasajero) {
                        actualizarDatosConductor(unidadNumero, c);
                        actualizarBotonReunionConvoy();
                        actualizarBotonEvaluacion();
                    }
                });

                listContainer.appendChild(item);
            });
        }
    }

    const navigateList = (direction) => {
        const items = listContainer.querySelectorAll('.autocomplete-item');
        if (items.length === 0) return;
        if (currentFocus !== -1) {
            items[currentFocus].classList.remove('selected');
        }
        currentFocus += direction;
        if (currentFocus >= items.length) {
            currentFocus = 0;
        }
        if (currentFocus < 0) {
            currentFocus = items.length - 1;
        }
        items[currentFocus].classList.add('selected');
        items[currentFocus].scrollIntoView({
            block: 'nearest'
        });
    }

    input.addEventListener('input', updateList);
    input.addEventListener('focus', updateList);

    input.addEventListener('keydown', function (e) {
        if (e.key === "ArrowDown") {
            e.preventDefault();
            navigateList(1);
        } else if (e.key === "ArrowUp") {
            e.preventDefault();
            navigateList(-1);
        } else if (e.key === "Enter" || e.key === "Tab") {
            if (currentFocus > -1) {
                e.preventDefault();
                const selectedItem = listContainer.querySelectorAll('.autocomplete-item')[
                    currentFocus];
                const event = new MouseEvent('mousedown', {
                    bubbles: true,
                    cancelable: true,
                    view: window
                });
                selectedItem.dispatchEvent(event);
            } else if (e.key === "Enter") {
                const exactMatch = conductoresGlobales.find(c => c.toUpperCase() === input.value.toUpperCase());
                if (exactMatch) {
                    input.value = exactMatch;
                    input.dataset.conductorSeleccionado = exactMatch;
                    if (!esPasajero) {
                        actualizarDatosConductor(unidadNumero, exactMatch);
                        actualizarBotonReunionConvoy();
                        actualizarBotonEvaluacion();
                    }
                    listContainer.innerHTML = '';
                } else {
                    if (!esPasajero) {
                        actualizarDatosConductor(unidadNumero, '');
                    }
                    listContainer.innerHTML = '';
                }
            }
        }
    });

    input.addEventListener('blur', function () {
        setTimeout(() => {
            listContainer.innerHTML = '';
            const exactMatch = conductoresGlobales.find(c => c.toUpperCase() === input.value.toUpperCase());
            if (!exactMatch || input.dataset.conductorSeleccionado !== input.value) {
                if (!esPasajero) {
                    actualizarDatosConductor(unidadNumero, '');
                    actualizarBotonReunionConvoy();
                    actualizarBotonEvaluacion();
                }
            }
        }, 150);
    });
}

function actualizarDatosConductor(unidadNumero, nombreConductor) {
    const inputVigenciaLic = document.getElementById(`vigencia-lic-${unidadNumero}`);
    const inputVigenciaMan = document.getElementById(`vigencia-man-${unidadNumero}`);

    const data = datosConductoresGlobales[nombreConductor];

    if (data) {
        inputVigenciaLic.value = data.vigencia;
        inputVigenciaMan.value = data.manDefVigencia;
        inputVigenciaMan.readOnly = true;
    } else {
        inputVigenciaLic.value = '';
        inputVigenciaMan.value = '';
        inputVigenciaMan.readOnly = true;
    }
}

// ====================================================================
// GESTIÓN DE MODALES PRINCIPALES
// ====================================================================
function gestionarModalFormulario(abrir) {
    const modal = document.getElementById('modalFormulario');
    if (abrir) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        if (contadorUnidades === 0) {
            agregarUnidad();
        }
    } else {
        Swal.fire({
            title: '¿Desea cerrar el formulario?',
            text: 'Se perderán los datos no guardados de la solicitud actual.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cerrar',
            cancelButtonText: 'Continuar editando',
            confirmButtonColor: 'var(--accent-red)',
            cancelButtonColor: 'var(--primary-blue)',
        }).then((result) => {
            if (result.isConfirmed) {
                modal.classList.remove('active');
                document.body.style.overflow = 'auto';
                limpiarFormulario();
            }
        });
    }
}

function limpiarFormulario() {
    document.getElementById('formViaje').reset();
    document.getElementById('cuerpoTablaUnidades').innerHTML = '';
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

    document.getElementById('listaParadas').innerHTML = '';
    contadorParadas = 0;
    toggleSeccionParadas(false);

    evaluacionRiesgoGuardada = false;
    puntajeRiesgoTotal = 0;
    const btnEval = document.getElementById('btnEvaluacionRiesgo');
    btnEval.classList.remove('evaluacion-completada');
    btnEval.innerHTML = '<i class="fas fa-list-check"></i> Realizar Evaluación del Viaje (Requerido)';
    document.getElementById('formEvaluacionRiesgo').reset();
    actualizarBotonEvaluacion();
}

document.getElementById('modalFormulario').addEventListener('click', function (e) {
    if (e.target === this) {
        gestionarModalFormulario(false);
    }
});

// ====================================================================
// GESTIÓN DE PARADAS
// ====================================================================
function toggleSeccionParadas(mostrar) {
    const contenedor = document.getElementById('contenedorParadas');
    const lista = document.getElementById('listaParadas');

    if (mostrar) {
        contenedor.classList.remove('hidden');
        if (lista.children.length === 0) {
            agregarParada();
        }
    } else {
        contenedor.classList.add('hidden');
        lista.innerHTML = '';
        contadorParadas = 0;
    }
}

function agregarParada() {
    if (contadorParadas >= MAX_PARADAS) {
        Swal.fire({
            title: 'Límite de Paradas Alcanzado',
            text: `Solo se permiten hasta ${MAX_PARADAS} paradas por viaje.`,
            icon: 'warning',
            confirmButtonColor: 'var(--primary-blue)'
        });
        return;
    }

    contadorParadas++;
    const lista = document.getElementById('listaParadas');
    const paradasActuales = lista.querySelectorAll('.parada-item').length;

    if (paradasActuales % 4 === 0) {
        const nuevaFila = document.createElement('div');
        nuevaFila.classList.add('paradas-fila');
        nuevaFila.id = `paradas-fila-${Math.ceil((paradasActuales + 1) / 4)}`;
        lista.appendChild(nuevaFila);
    }

    const filas = lista.querySelectorAll('.paradas-fila');
    const ultimaFila = filas[filas.length - 1];

    const paradaDiv = document.createElement('div');
    paradaDiv.classList.add('parada-item');
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

        const lista = document.getElementById('listaParadas');
        if (lista.children.length === 0) {
            document.querySelector('input[name="tiene_paradas"][value="no"]').checked = true;
            toggleSeccionParadas(false);
        }
    }
}

function reorganizarParadas() {
    const lista = document.getElementById('listaParadas');
    const todasParadas = lista.querySelectorAll('.parada-item');
    const filas = lista.querySelectorAll('.paradas-fila');
    filas.forEach(fila => fila.remove());

    if (todasParadas.length === 0) {
        contadorParadas = 0;
        return;
    }

    let contadorGlobal = 0;
    todasParadas.forEach((parada, index) => {
        contadorGlobal++;
        if (index % 4 === 0) {
            const nuevaFila = document.createElement('div');
            nuevaFila.classList.add('paradas-fila');
            nuevaFila.id = `paradas-fila-${Math.floor(index / 4) + 1}`;
            lista.appendChild(nuevaFila);
        }

        const filasActuales = lista.querySelectorAll('.paradas-fila');
        const ultimaFila = filasActuales[filasActuales.length - 1];
        const paradaTitulo = parada.querySelector('.parada-titulo');

        if (paradaTitulo) {
            paradaTitulo.textContent = `Parada ${contadorGlobal}`;
        }

        const select = parada.querySelector('select');
        const input = parada.querySelector('input[type="text"]');
        const btnEliminar = parada.querySelector('.btn-remove-parada-compact');

        if (select) {
            select.setAttribute('name', `paradas[${contadorGlobal}][motivo]`);
        }
        if (input) {
            input.setAttribute('name', `paradas[${contadorGlobal}][lugar]`);
        }
        if (btnEliminar) {
            btnEliminar.setAttribute('onclick', `eliminarParada(${contadorGlobal})`);
            btnEliminar.setAttribute('title', `Eliminar Parada ${contadorGlobal}`);
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
    const modal = document.getElementById('modalInspeccionLigera');

    if (abrir && unidadNumero !== null) {
        unidadEnInspeccion = unidadNumero;
        tipoVehiculoInspeccion = 'ligera';

        const selectVehiculo = document.querySelector(`#unidad-${unidadNumero} .unidad-vehiculo`);
        const nombreVehiculo = selectVehiculo ? selectVehiculo.value : 'Unidad ' + unidadNumero;
        document.getElementById('inputNoEconomicoLigera').textContent = nombreVehiculo;
        document.getElementById('inspeccionUnidadIndexLigera').value = unidadNumero;

        // Cargar datos guardados si existen
        const savedData = datosInspeccionLigera[unidadNumero] || {};
        Object.keys(savedData).forEach(key => {
            const radio = document.querySelector(
                `input[name="inspeccion_${key}"][value="${savedData[key]}"]`);
            if (radio) {
                radio.checked = true;
            }
        });

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    } else {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
        unidadEnInspeccion = null;
        tipoVehiculoInspeccion = null;
    }
}

function gestionarModalInspeccionPesada(abrir, unidadNumero = null) {
    const modal = document.getElementById('modalInspeccionPesada');

    if (abrir && unidadNumero !== null) {
        unidadEnInspeccion = unidadNumero;
        tipoVehiculoInspeccion = 'pesada';

        const selectVehiculo = document.querySelector(`#unidad-${unidadNumero} .unidad-vehiculo`);
        const nombreVehiculo = selectVehiculo ? selectVehiculo.value : 'Unidad ' + unidadNumero;
        document.getElementById('inputNoEconomicoPesada').textContent = nombreVehiculo;
        document.getElementById('inspeccionUnidadIndexPesada').value = unidadNumero;

        // Cargar datos guardados si existen
        const savedData = datosInspeccionPesada[unidadNumero] || {};
        Object.keys(savedData).forEach(key => {
            const radio = document.querySelector(
                `input[name="inspeccion_${key}_p"][value="${savedData[key]}"]`);
            if (radio) {
                radio.checked = true;
            }
        });

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    } else {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
        unidadEnInspeccion = null;
        tipoVehiculoInspeccion = null;
    }
}

function abrirInspeccion(unidadNumero) {
    const selectVehiculo = document.querySelector(`#unidad-${unidadNumero} .unidad-vehiculo`);
    const vehiculo = selectVehiculo ? selectVehiculo.value : '';
    const tipo = clasificacionVehiculos[vehiculo] || "";

    if (tipo === "Unidad Ligera") {
        gestionarModalInspeccionLigera(true, unidadNumero);
    } else if (tipo === "Unidad Pesada") {
        gestionarModalInspeccionPesada(true, unidadNumero);
    } else {
        Swal.fire({
            title: 'Seleccione un vehículo',
            text: 'Primero seleccione un vehículo para saber qué tipo de inspección realizar.',
            icon: 'warning',
            confirmButtonColor: 'var(--primary-blue)'
        });
    }
}

function guardarInspeccionLigera() {
    const form = document.getElementById('formInspeccionLigera');
    const unidadNumero = parseInt(document.getElementById('inspeccionUnidadIndexLigera').value);

    // Validar que todos los campos estén completos
    const items = [
        'docs', 'llantas', 'luces', 'extintor',
        'botiquin', 'kit', 'fluidos', 'frenos'
    ];

    let formValido = true;
    items.forEach(item => {
        const checked = form.querySelector(`input[name="inspeccion_${item}"]:checked`);
        if (!checked) {
            formValido = false;
        }
    });

    if (!formValido) {
        Swal.fire({
            title: 'Inspección Incompleta',
            text: 'Debe responder sí o no a todos los elementos de la inspección.',
            icon: 'warning',
            confirmButtonColor: 'var(--primary-blue)'
        });
        return;
    }

    const data = {};
    let todoAprobado = true;

    items.forEach(item => {
        const value = form.querySelector(`input[name="inspeccion_${item}"]:checked`).value;
        data[item] = value;
        if (value === 'no') {
            todoAprobado = false;
        }
    });

    datosInspeccionLigera[unidadNumero] = data;

    const btnInspeccion = document.getElementById(`btn-inspeccion-${unidadNumero}`);
    if (btnInspeccion) {
        if (todoAprobado) {
            btnInspeccion.classList.add('btn-submit', 'btn-inspeccion-aprobado');
            btnInspeccion.classList.remove('btn-inspeccion');
            btnInspeccion.innerHTML = '<i class="fas fa-check-circle"></i>';
            btnInspeccion.dataset.aprobado = 'true';
            btnInspeccion.title = 'Inspección Aprobada';
        } else {
            btnInspeccion.classList.add('btn-inspeccion');
            btnInspeccion.classList.remove('btn-submit', 'btn-inspeccion-aprobado');
            btnInspeccion.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
            btnInspeccion.dataset.aprobado = 'false';
            btnInspeccion.title = 'Revisar Inspección';
        }
    }

    Swal.fire({
        title: 'Inspección Guardada',
        text: `La inspección para la Unidad ${unidadNumero} ha sido registrada.`,
        icon: 'success',
        confirmButtonColor: 'var(--primary-blue)'
    }).then(() => {
        gestionarModalInspeccionLigera(false);
        actualizarBotonReunionConvoy();
        actualizarBotonEvaluacion();
    });
}

function guardarInspeccionPesada() {
    const form = document.getElementById('formInspeccionPesada');
    const unidadNumero = parseInt(document.getElementById('inspeccionUnidadIndexPesada').value);

    // Validar que todos los campos estén completos
    const items = [
        'docs_p', 'llantas_p', 'luces_p', 'extintor_p',
        'botiquin_p', 'kit_p', 'fluidos_p', 'frenos_p',
        'acople_p', 'senalizacion_p'
    ];

    let formValido = true;
    items.forEach(item => {
        const checked = form.querySelector(`input[name="inspeccion_${item}"]:checked`);
        if (!checked) {
            formValido = false;
        }
    });

    if (!formValido) {
        Swal.fire({
            title: 'Inspección Incompleta',
            text: 'Debe responder sí o no a todos los elementos de la inspección.',
            icon: 'warning',
            confirmButtonColor: 'var(--primary-blue)'
        });
        return;
    }

    const data = {};
    let todoAprobado = true;

    items.forEach(item => {
        const value = form.querySelector(`input[name="inspeccion_${item}"]:checked`).value;
        data[item] = value;
        if (value === 'no') {
            todoAprobado = false;
        }
    });

    datosInspeccionPesada[unidadNumero] = data;

    const btnInspeccion = document.getElementById(`btn-inspeccion-${unidadNumero}`);
    if (btnInspeccion) {
        if (todoAprobado) {
            btnInspeccion.classList.add('btn-submit', 'btn-inspeccion-aprobado');
            btnInspeccion.classList.remove('btn-inspeccion');
            btnInspeccion.innerHTML = '<i class="fas fa-check-circle"></i>';
            btnInspeccion.dataset.aprobado = 'true';
            btnInspeccion.title = 'Inspección Aprobada';
        } else {
            btnInspeccion.classList.add('btn-inspeccion');
            btnInspeccion.classList.remove('btn-submit', 'btn-inspeccion-aprobado');
            btnInspeccion.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
            btnInspeccion.dataset.aprobado = 'false';
            btnInspeccion.title = 'Revisar Inspección';
        }
    }

    Swal.fire({
        title: 'Inspección Guardada',
        text: `La inspección para la Unidad ${unidadNumero} ha sido registrada.`,
        icon: 'success',
        confirmButtonColor: 'var(--primary-blue)'
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
    const btn = document.getElementById('btnEvaluacionRiesgo');
    if (contadorUnidades > 0) {
        btn.disabled = false;
    } else {
        btn.disabled = true;
    }
    actualizarBotonEnviarSolicitud();
}

function gestionarModalEvaluacion(abrir) {
    const modal = document.getElementById('modalEvaluacion');
    if (abrir) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    } else {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

function guardarEvaluacion() {
    const form = document.getElementById('formEvaluacionRiesgo');
    const categorias = ['ev_manejo', 'ev_horas', 'ev_vehiculos', 'ev_comunicacion', 'ev_clima', 'ev_iluminacion',
        'ev_carretera', 'ev_otras', 'ev_animales', 'ev_seguridad'
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
            title: 'Evaluación Incompleta',
            text: 'Por favor seleccione una opción para cada categoría.',
            icon: 'warning',
            confirmButtonColor: 'var(--primary-blue)'
        });
        return;
    }

    let nivelRiesgo = "";
    let icono = "success";
    let color = "var(--primary-green)";
    let mensajeAdicional = "";

    if (totalPuntos <= 55) {
        nivelRiesgo = "Bajo Riesgo";
        icono = "success";
        color = "var(--primary-green)";
        mensajeAdicional = "El viaje puede proceder normalmente.";
    } else if (totalPuntos >= 56 && totalPuntos <= 105) {
        nivelRiesgo = "Riesgo Medio";
        icono = "warning";
        color = "var(--accent-yellow)";
        mensajeAdicional = "Se requieren precauciones adicionales.";
    } else if (totalPuntos >= 106 && totalPuntos <= 145) {
        nivelRiesgo = "Alto Riesgo";
        icono = "warning";
        color = "var(--accent-orange)";
        mensajeAdicional = "Se requiere aprobación especial y medidas de seguridad reforzadas.";
    } else if (totalPuntos > 145) {
        nivelRiesgo = "Muy Alto Riesgo";
        icono = "error";
        color = "var(--accent-red)";
        mensajeAdicional = "NO SE RECOMIENDA EL VIAJE. Requiere autorización de gerencia.";
    }

    puntajeRiesgoTotal = totalPuntos;
    evaluacionRiesgoGuardada = true;

    const btn = document.getElementById('btnEvaluacionRiesgo');
    btn.innerHTML = '<i class="fas fa-check-circle"></i> Evaluación Completada';
    btn.classList.add('evaluacion-completada');

    if (totalPuntos >= 70) {
        btn.style.backgroundColor = color;
        btn.style.color = 'white';
    }

    gestionarModalEvaluacion(false);
    actualizarBotonEnviarSolicitud();

    Swal.fire({
        title: `Evaluación Guardada - ${nivelRiesgo}`,
        html: `<div style="text-align: left;">
            <strong>Puntaje total: ${totalPuntos} puntos</strong><br>
            <strong>Nivel de riesgo: <span style="color: ${color}">${nivelRiesgo}</span></strong><br><br>
            ${mensajeAdicional}
            </div>`,
        icon: icono,
        confirmButtonColor: color,
        width: 600,
        customClass: {
            popup: 'swal2-evaluacion-riesgo'
        }
    });
}

// ====================================================================
// REUNIÓN PRE-CONVOY
// ====================================================================
function actualizarBotonReunionConvoy() {
    const btn = document.getElementById('btnReunionPreConvoy');
    if (!btn) return;

    if (contadorUnidades < 2) {
        btn.disabled = true;
        btn.classList.remove('btn-submit', 'btn-secondary-convoy-completed');
        btn.classList.add('btn-secondary-convoy');
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
            `.unidad-vehiculo`
        ];

        for (const selector of requiredSelectors) {
            const input = fila.querySelector(selector);
            if (input && input.value.trim() === "") {
                unidadesCompletas = false;
                break;
            }
        }
        if (!unidadesCompletas) break;

        const pasajeros = fila.querySelectorAll('.pasajero-input-group input');
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
            btn.classList.add('btn-secondary-convoy-completed');
            btn.classList.remove('btn-secondary-convoy', 'btn-submit');
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Reunión Confirmada';
        } else {
            btn.classList.add('btn-secondary-convoy');
            btn.classList.remove('btn-submit', 'btn-secondary-convoy-completed');
            btn.innerHTML = '<i class="fas fa-handshake"></i> Reunión Pre-convoy';
        }
    } else {
        btn.disabled = true;
        btn.classList.remove('btn-submit', 'btn-secondary-convoy-completed');
        btn.classList.add('btn-secondary-convoy');
        btn.innerHTML = '<i class="fas fa-handshake"></i> Reunión Pre-convoy';
        btn.title = "Complete todos los campos de Conductor y Unidad antes de realizar la Reunión.";
        reunionPreConvoyGuardada = false;
    }

    actualizarBotonEnviarSolicitud();
}

function gestionarModalPreConvoy(abrir) {
    const modal = document.getElementById('modalPreConvoy');
    const liderSelect = document.getElementById('liderConvoy');

    if (abrir) {
        const conductoresDisponibles = obtenerConductoresUnidades();
        liderSelect.innerHTML = '<option value="">Seleccione un conductor como Líder</option>';

        conductoresDisponibles.forEach(c => {
            const option = document.createElement('option');
            option.value = c;
            option.textContent = c;
            if (datosReunionConvoy.lider_convoy === c) {
                option.selected = true;
            }
            liderSelect.appendChild(option);
        });

        // Cargar datos guardados del checklist
        const checklistItems = [
            'puntos_parada', 'ruptura_convoy', 'doc_vigente',
            'prevencion_acc', 'contactos_emerg', 'compromiso_lider'
        ];

        checklistItems.forEach(item => {
            const savedValue = datosReunionConvoy[item];
            if (savedValue) {
                const radio = document.querySelector(
                    `input[name="checklist_${item}"][value="${savedValue}"]`);
                if (radio) {
                    radio.checked = true;
                }
            }
        });

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    } else {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

function obtenerConductoresUnidades() {
    const conductoresList = [];
    for (let i = 1; i <= contadorUnidades; i++) {
        const inputConductor = document.getElementById(`conductor-${i}`);
        if (inputConductor && inputConductor.value.trim() !== '') {
            conductoresList.push(inputConductor.value.trim());
        }
    }
    return conductoresList;
}

function guardarPreConvoy() {
    const form = document.getElementById('formPreConvoy');
    const liderSelect = document.getElementById('liderConvoy');

    if (liderSelect.value.trim() === '') {
        Swal.fire({
            title: 'Líder No Seleccionado',
            text: 'Debe seleccionar un conductor como Líder de Convoy.',
            icon: 'warning',
            confirmButtonColor: 'var(--primary-blue)'
        });
        return;
    }

    const checklistItems = [
        'puntos_parada', 'ruptura_convoy', 'doc_vigente',
        'prevencion_acc', 'contactos_emerg', 'compromiso_lider'
    ];

    let checklistCompleto = true;
    const data = {
        lider_convoy: liderSelect.value
    };

    checklistItems.forEach(item => {
        const checked = form.querySelector(`input[name="checklist_${item}"]:checked`);
        if (!checked) {
            checklistCompleto = false;
        } else {
            data[item] = checked.value;
        }
    });

    if (!checklistCompleto) {
        Swal.fire({
            title: 'Checklist Incompleto',
            text: 'Debe responder sí o no a todos los elementos del checklist de seguridad.',
            icon: 'warning',
            confirmButtonColor: 'var(--primary-blue)'
        });
        return;
    }

    let noAprobado = false;
    let noItems = [];
    for (const key in data) {
        if (key !== 'lider_convoy' && data[key] === 'no') {
            noAprobado = true;
            noItems.push(key);
        }
    }

    if (noAprobado) {
        Swal.fire({
            title: '¡Advertencia de Seguridad!',
            html: `Se encontraron puntos de seguridad no confirmados (NO) en el checklist.<br><br>
                    Si continúa, el viaje será marcado con **ALTO RIESGO** y requerirá una aprobación superior.`,
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Guardar y Continuar (Alto Riesgo)',
            cancelButtonText: 'Corregir Checklist (Recomendado)',
            confirmButtonColor: 'var(--accent-red)',
            cancelButtonColor: 'var(--primary-blue)',
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

    let title = 'Reunión Confirmada';
    let text = `La Reunión Pre-convoy ha sido registrada. La solicitud está lista para ser enviada.`;
    let icon = 'success';

    if (!todoAprobado) {
        title = 'Reunión Guardada con Advertencias';
        text = `La Reunión Pre-convoy ha sido registrada con **NOs**. El viaje será marcado como **ALTO RIESGO**.`;
        icon = 'warning';
    }

    Swal.fire({
        title: title,
        text: text,
        icon: icon,
        confirmButtonColor: 'var(--primary-blue)'
    });
}

function actualizarBotonEnviarSolicitud() {
    const btnEnviar = document.getElementById('btnEnviarSolicitud');
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
        if (reunionPreConvoyGuardada) {
            btnEnviar.disabled = false;
            btnEnviar.title = "Enviar Solicitud";
        } else {
            btnEnviar.disabled = true;
            btnEnviar.title = "Debe completar y confirmar la Reunión Pre-convoy.";
        }
    }
}

// ====================================================================
// FUNCIÓN AGREGAR UNIDAD (TABLA PRINCIPAL)
// ====================================================================
function agregarUnidad() {
    if (contadorUnidades >= MAX_UNIDADES) {
        Swal.fire('Límite alcanzado', `Solo puedes agregar hasta ${MAX_UNIDADES} unidades.`, 'warning');
        return;
    }

    contadorUnidades++;
    const numeroUnidad = contadorUnidades;
    const filaId = `unidad-${numeroUnidad}`;

    if (numeroUnidad === 2) {
        Swal.fire({
            title: '¡Convoy de Unidades!',
            html: 'Has agregado una segunda unidad. Se gestionará como Convoy.',
            icon: 'info',
            confirmButtonColor: 'var(--primary-blue)'
        });
    }

    const nuevaFila = document.createElement('tr');
    nuevaFila.id = filaId;

    nuevaFila.innerHTML = `
    <td>
        <div class="conductor-completo-group">
            <div class="hour-input-group" style="align-items: center;">

                <div style="display: flex; align-items: center; justify-content: center; width: 100%; margin-bottom: 5px;">
                    <label style="margin: 0;"><i class="fas fa-user-circle"></i> Nombre Conductor</label>
                    <button type="button" class="btn-rotate-role" onclick="rotarConductor(${numeroUnidad})" title="Rotar roles (Carrusel)">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>

                <div class="conductor-input-group input-wrapper">
                    <i class="fas fa-user field-icon conductor-main"></i>

                    <input type="text" class="table-input large unidad-conductor has-icon"
                        id="conductor-${numeroUnidad}"
                        name="unidad[${numeroUnidad}][conductor]"
                        data-es-principal="true"
                        placeholder="Nombre completo"
                        required autocomplete="off">

                    <i class="fas fa-star badge-principal" id="badge-principal-${numeroUnidad}" title="Conductor Principal (Dueño de Unidad)"></i>

                    <div class="autocomplete-list" id="autocomplete-list-${numeroUnidad}"></div>
                </div>
            </div>

            <div class="hour-input-group" style="display: none;">
                <input type="hidden" id="licencia-num-${numeroUnidad}" name="unidad[${numeroUnidad}][licencia_num]">
                <input type="hidden" id="vigencia-lic-hidden-${numeroUnidad}" name="unidad[${numeroUnidad}][vigencia_lic_hidden]">
            </div>

            <div class="hour-input-group">
                <label><i class="fas fa-wind"></i> Alcoholimetría</label>
                <div class="input-group-row" style="justify-content: center; display: flex; align-items: center;">
                    <select class="table-input small unidad-alcoholimetria"
                            id="alcohol-${numeroUnidad}"
                            name="unidad[${numeroUnidad}][alcoholimetria]"
                            required
                            onchange="actualizarBotonReunionConvoy()"> <option value="">¿Realizó?</option>
                        <option value="si">Sí</option>
                        <option value="no">No</option>
                    </select>
                    <input type="number" step="0.1"
                        class="table-input input-porcentaje"
                        id="alcohol-pct-${numeroUnidad}"
                        name="unidad[${numeroUnidad}][alcohol_pct]"
                        placeholder="%"
                        onblur="formatearPorcentaje(this)">
                </div>
            </div>
        </div>
    </td>

    <td>
        <div class="hour-input-group">
            <label><i class="fas fa-id-card"></i> Vigencia Licencia</label>
            <input type="text" class="table-input" id="vigencia-lic-${numeroUnidad}" name="unidad[${numeroUnidad}][vigencia_lic]" placeholder="202X-XX-XX" required readonly>
        </div>
        <div class="hour-input-group" style="margin-top: 5px;">
            <label><i class="fas fa-calendar-alt"></i> Vigencia Man. Def.</label>
            <input type="text" class="table-input" id="vigencia-man-${numeroUnidad}" name="unidad[${numeroUnidad}][vigencia_man]" placeholder="202X-XX-XX" required readonly>
        </div>
    </td>

    <td>
        <div class="hour-input-group">
            <label><i class="fas fa-bed"></i> Hr que Durmió</label>
            <div class="datetime-input-wrapper">
                <input type="text" class="table-input small unidad-hora-dormir"
                    id="dormir-${numeroUnidad}"
                    name="unidad[${numeroUnidad}][hora_dormir]"
                    placeholder="HH:MM" required>
            </div>
        </div>
        <div class="hour-input-group">
            <label><i class="fas fa-sun"></i> Hr que Despertó</label>
            <div class="datetime-input-wrapper">
                <input type="text" class="table-input small unidad-hora-levantar"
                    id="levantar-${numeroUnidad}"
                    name="unidad[${numeroUnidad}][hora_levantar]"
                    placeholder="HH:MM" required>
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

    <td>
        <select class="table-input large unidad-vehiculo" name="unidad[${numeroUnidad}][vehiculo]" required onchange="actualizarBotonReunionConvoy(); actualizarTipoVehiculo(${numeroUnidad})">
            <option value="">Seleccionar Vehículo</option>
            <optgroup label="Unidades Pesadas">
                <option value="VH-201">VH-201</option>
                <option value="VH-202">VH-202</option>
                <option value="VH-204">VH-204</option>
                <option value="VH-206">VH-206</option>
                <option value="VH-207">VH-207</option>
                <option value="VH-208">VH-208</option>
                <option value="VH-209">VH-209</option>
                <option value="VH-210">VH-210</option>
                <option value="VH-211">VH-211</option>
                <option value="VH-212">VH-212</option>
            </optgroup>
            <optgroup label="Unidades Ligeras">
                <option value="VH-105">VH-105</option>
                <option value="VH-106">VH-106</option>
                <option value="VH-107">VH-107</option>
                <option value="VH-108">VH-108</option>
                <option value="VH-109">VH-109</option>
                <option value="VH-110">VH-110</option>
                <option value="VH-111">VH-111</option>
                <option value="VH-112">VH-112</option>
                <option value="VH-113">VH-113</option>
                <option value="VH-114">VH-114</option>
                <option value="VH-115">VH-115</option>
                <option value="VH-116">VH-116</option>
            </optgroup>
        </select>
        <div id="tipo-vehiculo-${numeroUnidad}" class="tipo-vehiculo-text"></div>
    </td>

    <td>
        <button type="button" class="btn-viajes btn-inspeccion" id="btn-inspeccion-${numeroUnidad}" data-aprobado="false" onclick="abrirInspeccion(${numeroUnidad})" title="Realizar Inspección Pre-Viaje">
            <i class="fas fa-clipboard"></i>
        </button>
    </td>

    <td class="acciones-td">
        <button type="button" class="btn-accion eliminar" onclick="eliminarUnidad(${numeroUnidad})" title="Eliminar unidad">
            <i class="fas fa-trash"></i>
        </button>
    </td>
    `;

    document.getElementById('cuerpoTablaUnidades').appendChild(nuevaFila);

    // Inicializaciones
    actualizarContadorUnidades();
    actualizarLabelTipoUnidad();
    actualizarBotonAgregar();
    inicializarAutocompleteConductor(numeroUnidad);

    // Flatpickrs
    flatpickr(nuevaFila.querySelector('.unidad-hora-dormir'), configHora);
    flatpickr(nuevaFila.querySelector('.unidad-hora-levantar'), configHora);

    // Listeners para Cálculos y Validaciones de Completitud
    nuevaFila.querySelector('.unidad-hora-dormir').addEventListener('change', () => {
        calcularHorasDormidas(numeroUnidad);
        actualizarBotonReunionConvoy();
    });
    nuevaFila.querySelector('.unidad-hora-levantar').addEventListener('change', () => {
        calcularHorasDormidas(numeroUnidad);
        calcularHorasViaje(numeroUnidad);
        actualizarBotonReunionConvoy();
    });

    calcularHorasViaje(numeroUnidad);
    agregarPasajero(numeroUnidad);
    actualizarBotonReunionConvoy();
    actualizarBotonEvaluacion();
}

// ====================================================================
// FUNCIÓN AGREGAR PASAJERO
// ====================================================================
function agregarPasajero(unidadNumero) {
    const container = document.getElementById(`pasajeros-unidad-${unidadNumero}`);
    const currentPasajeros = container.querySelectorAll('.pasajero-input-group').length;

    if (currentPasajeros >= MAX_PASAJEROS) {
        Swal.fire('Límite', `Solo se permiten ${MAX_PASAJEROS} pasajeros.`, 'warning');
        return;
    }

    const index = currentPasajeros + 1;
    const div = document.createElement('div');
    div.classList.add('pasajero-input-group');
    div.dataset.index = index;
    div.id = `fila-p-${unidadNumero}-${index}`;

    // Datos ocultos iniciales
    div.dataset.alcohol = "";
    div.dataset.pct = "";
    div.dataset.dormir = "";
    div.dataset.levantar = "";
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

        <button type="button" class="btn-remove-pasajero" onclick="eliminarPasajero(${unidadNumero}, ${index})" style="margin-left: 5px;">
            <i class="fas fa-minus"></i>
        </button>
    `;

    container.appendChild(div);
    inicializarAutocompleteConductor(unidadNumero, true, index);
    actualizarBotonesPasajeros(unidadNumero);

    if (currentPasajeros + 1 >= MAX_PASAJEROS) {
        document.getElementById(`btn-add-pasajero-${unidadNumero}`).disabled = true;
    }
}

// ====================================================================
// FUNCIÓN GESTIONAR ROL PASAJERO (MODAL Y LÍMITES)
// ====================================================================
function gestionarRolPasajero(unidad, index) {
    const fila = document.getElementById(`fila-p-${unidad}-${index}`);
    const nombre = document.getElementById(`p-nombre-${unidad}-${index}`).value;

    if (!nombre) {
        Swal.fire('Falta Nombre', 'Escriba el nombre del pasajero antes de asignarle un rol.', 'info');
        return;
    }

    // A. SI YA ES RELEVO (Icono Camión) -> Preguntar si degradar
    if (fila.classList.contains('es-relevo')) {
        Swal.fire({
            title: '¿Quitar asignación de Conductor?',
            text: `¿Desea que ${nombre} deje de ser Conductor? Sus datos de alcoholimetría y sueño serán borrados.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, degradar a Pasajero',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                // Borrar datos
                fila.dataset.alcohol = "";
                fila.dataset.pct = "";
                fila.dataset.dormir = "";
                fila.dataset.levantar = "";
                actualizarIconoPasajero(fila, index, unidad);
                Swal.fire('Actualizado', 'El usuario ahora es Pasajero.', 'success');
            }
        });
        return;
    }

    // B. SI ES PASAJERO (Icono User) -> Intentar convertir en Relevo

    // 1. VALIDAR LÍMITE: ¿Ya existe otro relevo en la lista?
    const container = document.getElementById(`pasajeros-unidad-${unidad}`);
    const relevosExistentes = container.querySelectorAll('.es-relevo').length;

    if (relevosExistentes >= 1) {
        Swal.fire({
            title: 'Límite de Conductores Alcanzado',
            html: `Ya existe un Segundo Conductor asignado.<br><br>
                <b>Regla:</b> Solo se permiten 2 conductores por unidad (1 Principal + 1 Relevo).<br><br>
                Para asignar a <b>${nombre}</b>, primero debes quitar el rol al otro conductor de relevo.`,
            icon: 'warning',
            confirmButtonColor: 'var(--primary-orange)'
        });
        return;
    }

    // 2. MODAL GRID PARA LLENAR DATOS
    Swal.fire({
        title: `Asignar Conductor: ${nombre}`,
        html: `
            <div class="swal-modal-grid">
                <div class="swal-field-group">
                    <label>Alcoholimetría</label>
                    <select id="swal-alcohol">
                        <option value="">Seleccione...</option>
                        <option value="si">Sí (Aprobado)</option>
                        <option value="no">No</option>
                    </select>
                </div>
                <div class="swal-field-group">
                    <label>Porcentaje (%)</label>
                    <input id="swal-pct" type="number" step="0.1" placeholder="0.0">
                </div>

                <div class="swal-field-group">
                    <label>Hora Dormir</label>
                    <input id="swal-dormir" class="flatpickr-modal" placeholder="HH:MM">
                </div>
                <div class="swal-field-group">
                    <label>Hora Despertar</label>
                    <input id="swal-levantar" class="flatpickr-modal" placeholder="HH:MM">
                </div>

                <div class="swal-full-width">
                    <i class="fas fa-info-circle"></i> Se validarán estos datos para habilitar al conductor.
                </div>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-save"></i> Guardar y Asignar',
        confirmButtonColor: 'var(--accent-green)',
        cancelButtonText: 'Cancelar',
        width: '550px',
        didOpen: () => {
            flatpickr(".flatpickr-modal", configHora);
        },
        preConfirm: () => {
            const alcohol = document.getElementById('swal-alcohol').value;
            const pct = document.getElementById('swal-pct').value;
            const dormir = document.getElementById('swal-dormir').value;
            const levantar = document.getElementById('swal-levantar').value;

            if (!alcohol || !dormir || !levantar) {
                Swal.showValidationMessage('Debe completar Alcoholimetría y Horas de Sueño');
                return false;
            }
            if (alcohol === 'no') {
                Swal.showValidationMessage('El conductor no es apto (Alcoholimetría NO).');
                return false;
            }
            return { alcohol, pct, dormir, levantar };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const datos = result.value;
            // Guardar datos en atributos ocultos
            fila.dataset.alcohol = datos.alcohol;
            fila.dataset.pct = datos.pct;
            fila.dataset.dormir = datos.dormir;
            fila.dataset.levantar = datos.levantar;

            // Actualizar visualmente (Icono y Color)
            actualizarIconoPasajero(fila, index, unidad);

            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
            Toast.fire({ icon: 'success', title: 'Segundo Conductor Asignado' });
        }
    });
}

// ====================================================================
// FUNCIÓN ROTAR CONDUCTOR (INTERCAMBIO PRINCIPAL <-> RELEVO)
// ====================================================================
function rotarConductor(unidadNumero) {
    const container = document.getElementById(`pasajeros-unidad-${unidadNumero}`);
    const filasPasajeros = Array.from(container.querySelectorAll('.pasajero-input-group'));

    // 1. VALIDACIÓN: ¿Hay alguien en el asiento del conductor principal?
    const inputConductor = document.getElementById(`conductor-${unidadNumero}`);
    const cNombre = inputConductor.value.trim();

    if (!cNombre) {
        Swal.fire({
            title: 'Falta Conductor Principal',
            text: 'Primero debes escribir el nombre del Conductor Principal para poder realizar una rotación.',
            icon: 'warning',
            confirmButtonColor: 'var(--primary-blue)'
        });
        return;
    }

    // 2. BUSCAR AL CONDUCTOR 2 (RELEVO)
    const filaRelevo = filasPasajeros.find(fila => fila.classList.contains('es-relevo'));

    if (!filaRelevo) {
        Swal.fire({
            title: 'No hay Conductor de Relevo',
            html: 'Para rotar, primero debes designar quién es el <b>Conductor 2</b>.<br><br>Haz clic en el icono de usuario (<i class="fa-solid fa-user"></i>) del pasajero correspondiente y llena sus datos para asignarlo.',
            icon: 'info',
            confirmButtonColor: 'var(--primary-orange)'
        });
        return;
    }

    // --- SI LLEGAMOS AQUÍ, PODEMOS ROTAR (SWAP) ---

    // 3. RECOGER DATOS DEL CONDUCTOR ACTUAL (PRINCIPAL)
    const cEsPrincipal = inputConductor.dataset.esPrincipal === "true";
    const cAlcohol = document.getElementById(`alcohol-${unidadNumero}`).value;
    const cPct = document.getElementById(`alcohol-pct-${unidadNumero}`).value;
    const cDormir = document.getElementById(`dormir-${unidadNumero}`).value;
    const cLevantar = document.getElementById(`levantar-${unidadNumero}`).value;

    // 4. RECOGER DATOS DEL RELEVO (PASAJERO SELECCIONADO)
    const pIdx = filaRelevo.dataset.index;
    const pNombreInput = document.getElementById(`p-nombre-${unidadNumero}-${pIdx}`);
    const pNombre = pNombreInput.value;

    const pEsPrincipal = filaRelevo.dataset.esPrincipal === "true";
    const pAlcohol = filaRelevo.dataset.alcohol || "";
    const pPct = filaRelevo.dataset.pct || "";
    const pDormir = filaRelevo.dataset.dormir || "";
    const pLevantar = filaRelevo.dataset.levantar || "";

    // 5. REALIZAR EL INTERCAMBIO (SWAP)

    // A. SUBIR EL RELEVO -> AL PUESTO DE CONDUCTOR
    inputConductor.value = pNombre;
    inputConductor.dataset.conductorSeleccionado = pNombre;
    inputConductor.dataset.esPrincipal = pEsPrincipal ? "true" : "false";

    document.getElementById(`alcohol-${unidadNumero}`).value = pAlcohol;
    document.getElementById(`alcohol-pct-${unidadNumero}`).value = pPct;
    document.getElementById(`dormir-${unidadNumero}`).value = pDormir;
    document.getElementById(`levantar-${unidadNumero}`).value = pLevantar;

    const badgePrincipalTabla = document.getElementById(`badge-principal-${unidadNumero}`);
    if (badgePrincipalTabla) {
        badgePrincipalTabla.style.display = pEsPrincipal ? "inline-block" : "none";
    }

    if (typeof actualizarDatosConductor === "function") actualizarDatosConductor(unidadNumero, pNombre);
    if (typeof calcularHorasDormidas === "function") calcularHorasDormidas(unidadNumero);
    if (typeof calcularHorasViaje === "function") calcularHorasViaje(unidadNumero);

    // B. BAJAR EL EX-CONDUCTOR -> AL PUESTO DEL RELEVO
    pNombreInput.value = cNombre;

    filaRelevo.dataset.esPrincipal = cEsPrincipal ? "true" : "false";
    filaRelevo.dataset.alcohol = cAlcohol;
    filaRelevo.dataset.pct = cPct;
    filaRelevo.dataset.dormir = cDormir;
    filaRelevo.dataset.levantar = cLevantar;

    actualizarIconoPasajero(filaRelevo, pIdx, unidadNumero);

    actualizarBotonReunionConvoy();

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });
    Toast.fire({
        icon: 'success',
        title: `Cambio realizado: ${pNombre} ahora conduce`
    });
}

// ====================================================================
// FUNCIÓN ELIMINAR PASAJERO (PROTECCIÓN PRINCIPAL)
// ====================================================================
function eliminarPasajero(unidadNumero, pasajeroIndex) {
    const fila = document.getElementById(`fila-p-${unidadNumero}-${pasajeroIndex}`);

    if (fila && fila.dataset.esPrincipal === "true") {
        Swal.fire({
            title: 'Acción No Permitida',
            text: 'No puedes eliminar al Conductor Principal de la lista. Debes rotarlo a la posición de manejo si deseas cambiarlo, pero el registro del responsable de unidad debe mantenerse.',
            icon: 'error'
        });
        return;
    }

    const container = document.getElementById(`pasajeros-unidad-${unidadNumero}`);
    if (container.querySelectorAll('.pasajero-input-group').length <= 1) return;

    if (fila) fila.remove();

    const remainingPasajeros = container.querySelectorAll('.pasajero-input-group');
    remainingPasajeros.forEach((group, index) => {
        const newIndex = index + 1;
    });

    if (remainingPasajeros.length < MAX_PASAJEROS) {
        document.getElementById(`btn-add-pasajero-${unidadNumero}`).disabled = false;
    }
}

// ===================================================================
// AUXILIARES
// ====================================================================
function actualizarIconoPasajero(fila, index, unidad) {
    const icon = document.getElementById(`p-icon-${unidad}-${index}`);
    const badgeStar = document.getElementById(`p-badge-${unidad}-${index}`);

    if (fila.dataset.alcohol === 'si' && fila.dataset.dormir) {
        icon.className = 'fa-solid fa-truck-front field-icon relevo';
        icon.title = "Conductor Relevo (Clic para ver/quitar)";
        fila.classList.add('es-relevo');
    } else {
        icon.className = 'fa-solid fa-user field-icon pasajero';
        icon.title = "Pasajero (Clic para asignar como Relevo)";
        fila.classList.remove('es-relevo');
    }

    if (fila.dataset.esPrincipal === "true") {
        badgeStar.style.display = "inline-block";
        badgeStar.title = "Conductor Principal (Descansando)";
    } else {
        badgeStar.style.display = "none";
    }
}

function formatearPorcentaje(input) {
    let val = input.value;
    if (val !== "") {
        input.value = parseFloat(val).toFixed(1);
    }
}

function actualizarBotonesPasajeros(unidadNumero) {
    const container = document.getElementById(`pasajeros-unidad-${unidadNumero}`);
    const grupos = container.querySelectorAll('.pasajero-input-group');

    if (grupos.length === 1) {
        const btn = grupos[0].querySelector('.btn-remove-pasajero');
        if (btn) {
            btn.style.opacity = "0.3";
            btn.style.pointerEvents = "none";
        }
    } else {
        grupos.forEach(grupo => {
            const btn = grupo.querySelector('.btn-remove-pasajero');
            if (btn) {
                btn.style.opacity = "1";
                btn.style.pointerEvents = "auto";
            }
        });
    }
}

// ====================================================================
// FUNCIONES DE UNIDADES
// ====================================================================
function generarCodigoViaje(incrementar = false) {
    if (incrementar) {
        codigoViaje++;
    }
    const codigo = `N°:GV-${String(codigoViaje).padStart(3, '0')}`;
    const codigoElement = document.getElementById('codigoViaje');
    if (codigoElement) {
        codigoElement.textContent = codigo;
    }
}

function actualizarContadorUnidades() {
    document.getElementById('contadorUnidades').textContent = contadorUnidades;
}

function actualizarLabelTipoUnidad() {
    const labelElement = document.getElementById('label-tipo-unidad');
    if (!labelElement) return;

    if (contadorUnidades === 1) {
        labelElement.textContent = ' (Unidad Única)';
        labelElement.classList.remove('convoy');
    } else if (contadorUnidades > 1) {
        labelElement.textContent = ' (Convoy de Unidades)';
        labelElement.classList.add('convoy');
    } else {
        labelElement.textContent = '';
        labelElement.classList.remove('convoy');
    }
}

function actualizarBotonAgregar() {
    const boton = document.getElementById('btnAgregarUnidad');
    if (contadorUnidades >= MAX_UNIDADES) {
        boton.disabled = true;
        boton.textContent = `Límite de ${MAX_UNIDADES} Unidades Alcanzado`;
    } else {
        const restantes = MAX_UNIDADES - contadorUnidades;
        boton.innerHTML =
            `<i class="fas fa-plus-circle"></i> Agregar Unidad (${restantes} restante${restantes !== 1 ? 's' : ''})`;
    }
}

function actualizarTipoVehiculo(unidadNumero) {
    const select = document.querySelector(`#unidad-${unidadNumero} .unidad-vehiculo`);
    const label = document.getElementById(`tipo-vehiculo-${unidadNumero}`);
    if (!select || !label) return;

    const vehiculo = select.value;
    const tipo = clasificacionVehiculos[vehiculo] || "";

    label.textContent = tipo;
    label.className = "tipo-vehiculo-text";

    if (tipo === "Unidad Ligera") {
        label.classList.add("tipo-ligera");
    } else if (tipo === "Unidad Pesada") {
        label.classList.add("tipo-pesada");
    }
}

function eliminarUnidad(numero) {
    Swal.fire({
        title: '¿Eliminar unidad?',
        text: 'Se eliminarán todos los datos de esta unidad y sus pasajeros.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: 'var(--accent-red)',
        cancelButtonColor: 'var(--primary-blue)',
    }).then((result) => {
        if (result.isConfirmed) {
            const fila = document.getElementById(`unidad-${numero}`);
            fila.remove();

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
    const filas = document.querySelectorAll('#cuerpoTablaUnidades tr');
    contadorUnidades = 0;

    const nuevosDatosInspeccionLigera = {};
    const nuevosDatosInspeccionPesada = {};

    filas.forEach((fila, index) => {
        const numeroAnterior = parseInt(fila.id.split('-')[1]);
        const nuevoNumero = index + 1;
        contadorUnidades = nuevoNumero;
        const filaId = `unidad-${nuevoNumero}`;
        fila.id = filaId;

        if (datosInspeccionLigera[numeroAnterior]) {
            nuevosDatosInspeccionLigera[nuevoNumero] = datosInspeccionLigera[numeroAnterior];
            delete datosInspeccionLigera[numeroAnterior];
        }

        if (datosInspeccionPesada[numeroAnterior]) {
            nuevosDatosInspeccionPesada[nuevoNumero] = datosInspeccionPesada[numeroAnterior];
            delete datosInspeccionPesada[numeroAnterior];
        }

        const oldLicenciaNum = fila.querySelector(`#licencia-num-${numeroAnterior}`);
        const oldVigenciaLic = fila.querySelector(`#vigencia-lic-${numeroAnterior}`);
        const oldVigenciaMan = fila.querySelector(`#vigencia-man-${numeroAnterior}`);

        if (oldLicenciaNum) oldLicenciaNum.id = `licencia-num-${nuevoNumero}`;
        if (oldVigenciaLic) oldVigenciaLic.id = `vigencia-lic-${nuevoNumero}`;
        if (oldVigenciaMan) oldVigenciaMan.id = `vigencia-man-${nuevoNumero}`;

        fila.querySelector('.unidad-conductor').id = `conductor-${nuevoNumero}`;
        fila.querySelector('#autocomplete-list-' + numeroAnterior).id = `autocomplete-list-${nuevoNumero}`;

        inicializarAutocompleteConductor(nuevoNumero);

        const btnEliminar = fila.querySelector('.btn-accion.eliminar');
        if (btnEliminar) {
            btnEliminar.setAttribute('onclick', `eliminarUnidad(${nuevoNumero})`);
        }

        const btnInspeccion = fila.querySelector('.btn-inspeccion, .btn-submit, .btn-inspeccion-aprobado');
        if (btnInspeccion) {
            btnInspeccion.id = `btn-inspeccion-${nuevoNumero}`;
            btnInspeccion.setAttribute('onclick', `abrirInspeccion(${nuevoNumero})`);
            if (btnInspeccion.dataset.aprobado === 'true') {
                btnInspeccion.innerHTML = '<i class="fas fa-check-circle"></i>';
            } else {
                btnInspeccion.innerHTML = '<i class="fas fa-clipboard"></i>';
            }
        }

        const oldDormir = document.getElementById(`dormir-${numeroAnterior}`);
        const oldLevantar = document.getElementById(`levantar-${numeroAnterior}`);
        const oldTotalDormidas = document.getElementById(`total-hrs-dormidas-${numeroAnterior}`);
        const oldHrsDespierto = document.getElementById(`horas-despierto-${numeroAnterior}`);
        const oldHrsViaje = document.getElementById(`horas-viaje-${numeroAnterior}`);
        const oldTotalFinalizar = document.getElementById(`total-hrs-finalizar-${numeroAnterior}`);
        const tipoVehiculoDiv = document.getElementById(`tipo-vehiculo-${numeroAnterior}`);
        const selectVehiculo = fila.querySelector('.unidad-vehiculo');

        if (oldDormir) oldDormir.id = `dormir-${nuevoNumero}`;
        if (oldLevantar) oldLevantar.id = `levantar-${nuevoNumero}`;
        if (oldTotalDormidas) oldTotalDormidas.id = `total-hrs-dormidas-${nuevoNumero}`;
        if (oldHrsDespierto) oldHrsDespierto.id = `horas-despierto-${nuevoNumero}`;
        if (oldHrsViaje) oldHrsViaje.id = `horas-viaje-${nuevoNumero}`;
        if (oldTotalFinalizar) oldTotalFinalizar.id = `total-hrs-finalizar-${nuevoNumero}`;
        if (tipoVehiculoDiv) tipoVehiculoDiv.id = `tipo-vehiculo-${nuevoNumero}`;

        fila.querySelector('.unidad-hora-dormir').addEventListener('change', function () {
            calcularHorasDormidas(nuevoNumero);
            calcularHorasViaje(nuevoNumero);
            actualizarBotonReunionConvoy();
        });

        fila.querySelector('.unidad-hora-levantar').addEventListener('change', function () {
            calcularHorasDormidas(nuevoNumero);
            calcularHorasViaje(nuevoNumero);
            actualizarBotonReunionConvoy();
        });

        fila.querySelector('.unidad-alcoholimetria').addEventListener('change', function () {
            actualizarBotonReunionConvoy();
        });

        calcularHorasViaje(nuevoNumero);

        if (selectVehiculo) {
            selectVehiculo.setAttribute('onchange',
                `actualizarBotonReunionConvoy(); actualizarTipoVehiculo(${nuevoNumero})`
            );
        }

        const pasajeroContainer = fila.querySelector('.pasajero-container');
        if (pasajeroContainer) {
            pasajeroContainer.id = `pasajeros-unidad-${nuevoNumero}`;
            const btnAddPasajero = fila.querySelector('.btn-add-pasajero');
            btnAddPasajero.id = `btn-add-pasajero-${nuevoNumero}`;
            btnAddPasajero.setAttribute('onclick', `agregarPasajero(${nuevoNumero})`);

            pasajeroContainer.querySelectorAll('.pasajero-input-group').forEach((group, pIndex) => {
                const newIndex = pIndex + 1;
                const input = group.querySelector('input');
                const removeBtn = group.querySelector('.btn-remove-pasajero');

                group.setAttribute('data-p-index', newIndex);
                input.setAttribute('name', `unidad[${nuevoNumero}][pasajeros][p${newIndex}]`);
                if (removeBtn) {
                    removeBtn.setAttribute('onclick',
                        `eliminarPasajero(${nuevoNumero}, ${newIndex})`);
                }
            });

            actualizarBotonesPasajeros(nuevoNumero);
        }

        const inputs = fila.querySelectorAll('input, select');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            if (name && name.startsWith('unidad')) {
                const newName = name.replace(/unidad\[\d+\]/, `unidad[${nuevoNumero}]`);
                input.setAttribute('name', newName);
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
// ENVÍO DE SOLICITUD
// ====================================================================
function enviarSolicitud() {
    const form = document.getElementById('formViaje');

    if (!form.checkValidity()) {
        form.reportValidity();
        Swal.fire({
            title: 'Faltan Campos',
            text: 'Por favor, llene todos los campos requeridos en la sección de Información General y Detalles del Trayecto.',
            icon: 'warning',
            confirmButtonColor: 'var(--primary-blue)'
        });
        return;
    }

    const destinoSelector = document.getElementById('destinoPredefinido');
    const destinoEspecifico = document.getElementById('destinoEspecifico');

    if (destinoSelector.value === '' || (destinoSelector.value === 'Otro' && destinoEspecifico.value.trim() ===
        '')) {
        Swal.fire({
            title: 'Destino no especificado',
            text: 'Por favor, seleccione un destino de la lista o especifique uno en el campo de texto.',
            icon: 'warning',
            confirmButtonColor: 'var(--primary-blue)'
        });
        if (destinoSelector.value === '') {
            destinoSelector.focus();
        } else {
            destinoEspecifico.focus();
        }
        return;
    }

    const tieneParadas = document.querySelector('input[name="tiene_paradas"]:checked').value;
    if (tieneParadas === 'si') {
        const listaParadas = document.getElementById('listaParadas');
        if (listaParadas.children.length === 0) {
            Swal.fire({
                title: 'Paradas Requeridas',
                text: 'Indicó que realizaría paradas, pero no ha agregado ninguna. Por favor agregue al menos una o cambie la opción a "No".',
                icon: 'warning',
                confirmButtonColor: 'var(--primary-blue)'
            });
            return;
        }

        let paradasCompletas = true;
        listaParadas.querySelectorAll('input, select').forEach(input => {
            if (input.value.trim() === '') {
                paradasCompletas = false;
            }
        });

        if (!paradasCompletas) {
            Swal.fire({
                title: 'Datos de Paradas Incompletos',
                text: 'Por favor, complete todos los campos de Propósito y Ubicación para las paradas agregadas.',
                icon: 'warning',
                confirmButtonColor: 'var(--primary-blue)'
            });
            return;
        }
    }

    if (contadorUnidades === 0) {
        Swal.fire({
            title: 'Sin unidades',
            text: 'Debe agregar al menos una unidad vehicular y su conductor.',
            icon: 'warning',
            confirmButtonColor: 'var(--primary-blue)'
        });
        return;
    }

    let unidadesCompletas = true;
    let inspeccionesAprobadas = true;
    let unidadConProblema = 0;

    for (let i = 1; i <= contadorUnidades; i++) {
        const btnInspeccion = document.getElementById(`btn-inspeccion-${i}`);
        if (!btnInspeccion || btnInspeccion.dataset.aprobado !== 'true') {
            inspeccionesAprobadas = false;
            unidadConProblema = i;
        }

        const fila = document.getElementById(`unidad-${i}`);
        if (fila) {
            fila.querySelectorAll('input[required], select[required]').forEach(input => {
                if (input.value.trim() === "") {
                    unidadesCompletas = false;
                    unidadConProblema = i;
                }
                if (input.id.startsWith('vigencia-lic-') && input.value.trim() === "") {
                    unidadesCompletas = false;
                    unidadConProblema = i;
                }
            });
        }

        const pasajeroContainer = document.getElementById(`pasajeros-unidad-${i}`);
        if (pasajeroContainer) {
            pasajeroContainer.querySelectorAll('input[required]').forEach(input => {
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
            title: 'Información de Unidades Incompleta',
            html: `Asegúrese de **seleccionar un conductor de la lista** y llenar todos los campos requeridos de la Unidad **#${unidadConProblema}**, incluyendo la **Vigencia Man. Def.** y los **pasajeros** con nombre completo.`,
            icon: 'warning',
            confirmButtonColor: 'var(--primary-blue)'
        });
        return;
    }

    if (!inspeccionesAprobadas) {
        Swal.fire({
            title: 'Inspección Pendiente/Incompleta',
            text: `La Unidad #${unidadConProblema} debe tener la inspección vehicular realizada y **aprobada** antes de enviar la solicitud.`,
            icon: 'error',
            confirmButtonColor: 'var(--primary-blue)'
        });
        return;
    }

    if (contadorUnidades > 1 && !reunionPreConvoyGuardada) {
        Swal.fire({
            title: 'Reunión Pre-Convoy Requerida',
            text: 'Al ser un Convoy, debe realizar y confirmar la Reunión Pre-convoy antes de enviar la solicitud.',
            icon: 'error',
            confirmButtonColor: 'var(--primary-blue)'
        });
        return;
    }

    Swal.fire({
        title: '¿Enviar solicitud?',
        text: 'La solicitud será enviada para su aprobación.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar',
        cancelButtonText: 'Revisar',
        confirmButtonColor: 'var(--primary-blue)',
        cancelButtonColor: 'var(--medium-gray)',
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: '¡Solicitud enviada!',
                html: `<p>Tu solicitud <strong>${document.getElementById('codigoViaje').textContent}</strong> ha sido enviada exitosamente. Será revisada por el área de control.</p>`,
                icon: 'success',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: 'var(--primary-blue)'
            }).then(() => {
                generarCodigoViaje(true);
                document.getElementById('modalFormulario').classList.remove('active');
                document.body.style.overflow = 'auto';
                limpiarFormulario();
            });
        }
    });
}
