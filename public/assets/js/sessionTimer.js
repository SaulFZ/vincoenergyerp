// public/js/session-timer.js

(function() {
    // --- CONFIGURACIÓN DE TIEMPOS (15 minutos, coincide con SESSION_LIFETIME en PHP) ---
    const SESSION_TIMEOUT_MINUTES = 15;

    // Alerta a los 14 minutos exactos (1 minuto antes de la expiración del servidor)
    const ALERT_BEFORE_EXPIRY_MS = 60 * 1000;
    const ALERT_TIME_MS = (SESSION_TIMEOUT_MINUTES * 60 * 1000) - ALERT_BEFORE_EXPIRY_MS;

    // Tiempo de inactividad total (15 minutos)
    const EXPIRY_TIME_MS = SESSION_TIMEOUT_MINUTES * 60 * 1000;

    let lastActivityTime = Date.now();
    let intervalChecker;
    let isWarningShown = false;

    function getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : null;
    }

    /**
     * Envía una petición POST a Laravel para refrescar la marca de tiempo de la sesión.
     */
    function pingSession() {
        if (!getCsrfToken()) {
            forceLogout();
            return;
        }

        fetch('/session-ping', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            // Si Laravel indica que la sesión ya expiró por token o autenticación, forzamos el logout.
            if (response.status === 419 || response.status === 401) {
                forceLogout();
            }
        })
        .catch(error => {
             console.error('Error de red al intentar refrescar la sesión.');
        });
    }

    /**
     * Resetea el tiempo de inactividad local. No extiende la sesión aquí.
     */
    function resetActivity() {
        const now = Date.now();

        // Evita pings excesivos
        if (now - lastActivityTime > 5000) {
             // Hacemos un ping ligero, pero la extensión oficial es con el botón 'Continuar'.
             pingSession();
        }

        lastActivityTime = now;

        if (isWarningShown) {
             // Si el usuario interactúa, cerramos la alerta (pero la sesión solo se extendió
             // ligeramente con el ping de arriba. El tiempo de 15 min se basa en el servidor).
             if (typeof Swal !== 'undefined') {
                 Swal.close();
             }
             isWarningShown = false;
        }
    }

    /**
     * Función que se ejecuta SOLO al presionar 'Continuar Sesión'.
     * Esta es la acción oficial de extensión.
     */
    function extendSession() {
        pingSession();

        // Reiniciar el contador local después de la extensión
        const now = Date.now();
        lastActivityTime = now;
        isWarningShown = false;
    }

    /**
     * Muestra la alerta a los 14 minutos de inactividad.
     */
    function showTimeoutWarning() {
        if (isWarningShown || typeof Swal === 'undefined') return;

        isWarningShown = true;

        // --- Configuración de la alerta UX estricta ---
        Swal.fire({
            title: 'Sesión a punto de caducar',
            html: 'Tu sesión expirará en <strong id="sessionTimer">60</strong> segundos por inactividad. Haz clic en "Continuar Sesión" para evitar el cierre.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Continuar Sesión',
            cancelButtonText: 'Cerrar Sesión',
            allowOutsideClick: false, // El usuario debe interactuar con los botones.
            allowEscapeKey: false,
            timer: ALERT_BEFORE_EXPIRY_MS,
            timerProgressBar: true,
            didOpen: () => {
                const timerStrong = document.getElementById('sessionTimer');
                let timerInterval = setInterval(() => {
                    const timeLeft = Swal.getTimerLeft();
                    if (timerStrong && timeLeft) {
                        timerStrong.textContent = Math.ceil(timeLeft / 1000);
                    } else if (timeLeft <= 0) {
                        clearInterval(timerInterval);
                    }
                }, 100);
            }
        }).then((result) => {
            isWarningShown = false;

            if (result.isConfirmed) {
                // SÓLO aquí se extiende la sesión
                extendSession();
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                // Cierre explícito
                window.location.href = '/logout';
            } else if (result.dismiss === 'timer') {
                // Si el tiempo de 60 segundos termina automáticamente, vamos a login
                forceLogout();
            }
        });
    }

    /**
     * Recarga la página para forzar la redirección al login.
     */
    function forceLogout() {
        console.log('Sesión expirada. Redirigiendo a Login...');
        window.location.reload();
    }

    /**
     * Función principal que verifica el tiempo transcurrido (cada 5 segundos).
     */
    function checkInactivity() {
        const inactiveTime = Date.now() - lastActivityTime;

        // 1. Mostrar la alerta a los 14 minutos exactos
        if (!isWarningShown && inactiveTime >= ALERT_TIME_MS) {
            showTimeoutWarning();
        }

        // 2. Si llega a 15 minutos, forzamos la recarga (si el usuario ignora la alerta o la cerró sin extender)
        if (inactiveTime >= EXPIRY_TIME_MS) {
            clearInterval(intervalChecker);
            forceLogout();
        }
    }

    // --- Inicialización Universal ---
    document.addEventListener('DOMContentLoaded', function () {
        // Monitorear cualquier actividad, solo para reiniciar el lastActivityTime
        ['click', 'mousemove', 'keydown', 'scroll', 'touchstart'].forEach(eventType => {
            document.addEventListener(eventType, resetActivity, true);
        });

        // Iniciar la verificación periódica
        intervalChecker = setInterval(checkInactivity, 5000);

        // Reseteamos el tiempo de actividad al cargar la página
        resetActivity();
    });
})();
