(function () {
    // ====== VERIFICACIÓN INICIAL - NO ejecutar en login/password reset ======
    const currentPath = window.location.pathname.toLowerCase();

    // Lista de rutas/páginas donde NO debe ejecutarse el timeout
    const excludedPaths = [
        '/login',
        '/logout',
        '/password',
        '/reset',
        '/register',
        '/verify-email'
    ];

    // También verificar por elementos específicos de la página de login
    const hasLoginElements = document.querySelector('#loginForm') !== null ||
                             document.querySelector('#forgotPasswordLink') !== null ||
                             document.querySelector('.toggle-password') !== null;

    // Si estamos en una ruta excluida O tiene elementos de login, NO ejecutar
    const shouldSkip = excludedPaths.some(path => currentPath.includes(path)) || hasLoginElements;

    if (shouldSkip) {
        console.log(`🔒 Script de timeout desactivado (ruta: ${currentPath})`);
        return; // Salir inmediatamente - NO ejecutar el timeout
    }

    // ====== CONFIGURACIÓN DEL TIMEOUT (solo para usuarios autenticados) ======
    const SESSION_TIMEOUT_MINUTES = 10;
    const ALERT_BEFORE_EXPIRY_MINUTES = 1;
    const EXPIRY_TIME_MS = SESSION_TIMEOUT_MINUTES * 60 * 1000;
    const ALERT_TIME_MS = (SESSION_TIMEOUT_MINUTES - ALERT_BEFORE_EXPIRY_MINUTES) * 60 * 1000;
    const PING_INTERVAL_MS = 5 * 60 * 1000;

    let lastActivityTime = Date.now();
    let isWarningShown = false;
    let intervalChecker = null;
    let pingInterval = null;
    let isChecking = true;
    let currentSessionAlert = null;

    // ====== FUNCIONES DEL TIMEOUT ======
    function isTabActive() {
        return !document.hidden;
    }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : null;
    }

    async function pingSession() {
        const csrf = getCsrfToken();
        if (!csrf) {
            console.warn("Token CSRF no encontrado");
            return false;
        }

        try {
            const resp = await fetch('/session-ping', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            });

            if (resp.status === 419 || resp.status === 401) {
                return false;
            }
            return resp.ok;
        } catch (error) {
            console.warn("Error en ping:", error.message);
            return false;
        }
    }

    function closeSessionAlert() {
        if (currentSessionAlert && typeof Swal !== 'undefined') {
            const allAlerts = document.querySelectorAll('.swal2-container');
            let ourAlertActive = false;

            allAlerts.forEach(alert => {
                if (alert.contains(document.getElementById('sessionTimer'))) {
                    ourAlertActive = true;
                }
            });

            if (ourAlertActive) {
                Swal.close();
            }
        }
        currentSessionAlert = null;
        isWarningShown = false;
    }

    function resetActivity() {
        if (!isTabActive()) return;

        lastActivityTime = Date.now();

        if (isWarningShown) {
            closeSessionAlert();
        }
    }

    async function extendSession() {
        const success = await pingSession();
        if (success) {
            lastActivityTime = Date.now();
            closeSessionAlert();
            console.log("✅ Sesión extendida manualmente");
            return true;
        }
        return false;
    }

    function showTimeoutWarning() {
        if (isWarningShown || !isTabActive() || typeof Swal === 'undefined') return;

        const existingAlerts = document.querySelectorAll('.swal2-container');
        if (existingAlerts.length > 0) {
            let hasOurAlert = false;
            existingAlerts.forEach(alert => {
                if (alert.querySelector('#sessionTimer')) {
                    hasOurAlert = true;
                }
            });

            if (!hasOurAlert) {
                console.log("⚠️ Hay otra alerta activa, posponiendo alerta de sesión");
                setTimeout(() => {
                    if (isTabActive() && !isWarningShown) {
                        showTimeoutWarning();
                    }
                }, 5000);
                return;
            }
        }

        isWarningShown = true;

        currentSessionAlert = Swal.fire({
            title: "⚠️ Sesión a punto de expirar",
            html: `
                <div style="text-align: center; padding: 10px;">
                    <p>Tu sesión expirará en <strong style="color: #d63031; font-size: 1.2em;" id="sessionTimer">60</strong> segundos</p>
                    <p><small>Presiona "Continuar" para mantenerte conectado</small></p>
                </div>
            `,
            icon: "warning",
            showCancelButton: false,
            confirmButtonText: "Continuar sesión",
            confirmButtonColor: "#3085d6",
            // Los siguientes parámetros aseguran que solo el botón o el temporizador cierren la alerta.
            allowOutsideClick: false, // 🛑 Mantiene la alerta abierta si se hace clic fuera.
            allowEscapeKey: false,    // 🛑 Mantiene la alerta abierta si se presiona Escape.
            allowEnterKey: true,
            timer: 60000,
            timerProgressBar: true,
            backdrop: true,
            customClass: {
                popup: 'session-timeout-alert',
                container: 'session-alert-container'
            },
            didOpen: (modal) => {
                modal.setAttribute('data-alert-type', 'session-timeout');
                const timerEl = document.getElementById("sessionTimer");
                if (!timerEl) return;

                const interval = setInterval(() => {
                    const left = Swal.getTimerLeft();
                    if (!left || left <= 0) {
                        clearInterval(interval);
                        return;
                    }
                    timerEl.textContent = Math.ceil(left / 1000);
                }, 200);
            }
        }).then(async (result) => {
            isWarningShown = false;
            currentSessionAlert = null;

            if (result.isConfirmed) {
                const extended = await extendSession();
                if (!extended) {
                    forceLogout();
                }
            } else if (result.dismiss === 'timer') {
                forceLogout();
            }
        });
    }

    function checkStateOnReturn() {
        if (!isTabActive()) return;

        const inactive = Date.now() - lastActivityTime;

        if (inactive >= EXPIRY_TIME_MS) {
            forceLogout();
            return;
        }

        if (inactive >= ALERT_TIME_MS && inactive < EXPIRY_TIME_MS) {
            setTimeout(() => {
                if (isTabActive() && !isWarningShown) {
                    showTimeoutWarning();
                }
            }, 1000);
            return;
        }

        pingSession();
    }

    function forceLogout() {
        if (!isChecking) return;

        isChecking = false;
        clearInterval(intervalChecker);
        if (pingInterval) clearInterval(pingInterval);

        closeSessionAlert();

        setTimeout(() => {
            window.location.href = '/login';
        }, 100);
    }

    function checkInactivity() {
        if (!isTabActive()) return;

        const inactive = Date.now() - lastActivityTime;

        if (!isWarningShown && inactive >= ALERT_TIME_MS && inactive < EXPIRY_TIME_MS) {
            showTimeoutWarning();
        }

        if (inactive >= EXPIRY_TIME_MS) {
            forceLogout();
        }
    }

    // ====== INICIALIZACIÓN ======
    function initSessionTimeout() {
        console.log("✅ Controlador de timeout activado para usuarios autenticados");

        const activityEvents = ['click', 'mousemove', 'keydown', 'scroll', 'touchstart', 'mousedown', 'input'];

        activityEvents.forEach(event => {
            document.addEventListener(event, resetActivity, { passive: true });
        });

        intervalChecker = setInterval(checkInactivity, 1000);

        pingInterval = setInterval(() => {
            if (isTabActive()) {
                pingSession();
            }
        }, PING_INTERVAL_MS);

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                setTimeout(checkStateOnReturn, 300);
            } else {
                closeSessionAlert();
            }
        });

        lastActivityTime = Date.now();

        setTimeout(() => pingSession(), 2000);
    }

    // ====== VERIFICAR SI SWEETALERT ESTÁ DISPONIBLE ======
    if (typeof Swal === 'undefined') {
        let checkSwalCount = 0;
        const checkSwalInterval = setInterval(() => {
            checkSwalCount++;
            if (typeof Swal !== 'undefined') {
                clearInterval(checkSwalInterval);
                initSessionTimeout();
            } else if (checkSwalCount > 10) {
                clearInterval(checkSwalInterval);
                console.warn("SweetAlert no cargado, iniciando sin él");
                // Si Swal no carga, el timeout se inicia, pero sin la alerta interactiva
                initSessionTimeout();
            }
        }, 500);
    } else {
        initSessionTimeout();
    }

})();
