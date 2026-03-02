(function () {
    // ====== VERIFICACIÓN INICIAL ======
    const currentPath = window.location.pathname.toLowerCase();
    const excludedPaths = ['/login', '/logout', '/password', '/reset', '/register', '/verify-email'];
    const hasLoginElements = document.querySelector('#loginForm') !== null ||
                             document.querySelector('#forgotPasswordLink') !== null ||
                             document.querySelector('.toggle-password') !== null;

    // Si estamos en la página de login o similares, NO ejecutamos este script
    const shouldSkip = excludedPaths.some(path => currentPath.includes(path)) || hasLoginElements;
    if (shouldSkip) return;

    // ====== CONFIGURACIÓN ======
    const SESSION_TIMEOUT_MINUTES = 10; // Tiempo total de inactividad
    const WARNING_TIME_MINUTES = 1;     // Cuándo mostrar la advertencia (1 minuto antes)

    const EXPIRY_TIME_MS = SESSION_TIMEOUT_MINUTES * 60 * 1000;
    const ALERT_THRESHOLD_MS = (SESSION_TIMEOUT_MINUTES - WARNING_TIME_MINUTES) * 60 * 1000;
    const FINAL_ALERT_DURATION_MS = 5000;

    let lastActivityTime = Date.now();
    let isWarningShown = false;
    let isFinalAlertShown = false;
    let checkInterval;

    // ====== FUNCIONES AUXILIARES ======
    function isTabActive() { return !document.hidden; }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : null;
    }

    async function pingSession() {
        const csrf = getCsrfToken();
        if (!csrf) return false;
        try {
            const resp = await fetch('/session-ping', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });
            return resp.ok;
        } catch (e) { return false; }
    }

    function resetActivity() {
        // No resetear si el modal de advertencia o el de cierre final están en pantalla
        if (isWarningShown || isFinalAlertShown) return;
        lastActivityTime = Date.now();
    }

    async function extendSession() {
        console.log("🔄 Extendiendo sesión local...");
        lastActivityTime = Date.now();
        isWarningShown = false;

        Swal.close(); // Cierra la advertencia

        // Avisar al servidor
        pingSession().then(success => {
            if (success) console.log("✅ Servidor sincronizado");
        });
    }

    // ====== MODALES ======
    function showTimeoutWarning() {
        if (isWarningShown || isFinalAlertShown) return;
        isWarningShown = true;

        Swal.fire({
            title: "¿Sigues ahí?",
            html: `
                <div style="text-align: center; padding: 10px;">
                    <p>Tu sesión expirará por inactividad.</p>
                    <p>Tiempo restante: <strong style="color: #d63031; font-size: 1.5em;" id="sessionTimer">--</strong></p>
                    <p>Haz clic en el botón para continuar.</p>
                </div>
            `,
            icon: "warning",
            showCancelButton: false,
            confirmButtonText: "SÍ, CONTINUAR TRABAJANDO",
            confirmButtonColor: "#3085d6",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                const timerEl = document.getElementById("sessionTimer");
                const timerInt = setInterval(() => {
                    const now = Date.now();
                    const timeLeft = Math.max(0, Math.ceil((EXPIRY_TIME_MS - (now - lastActivityTime)) / 1000));

                    if (timerEl) timerEl.textContent = `${timeLeft}s`;

                    // Si el tiempo llega a 0 estando el modal abierto, forzamos el cierre
                    if (timeLeft <= 0) {
                        clearInterval(timerInt);
                        showFinalAlert();
                    }
                }, 480000);
            }
        }).then((result) => {
            if (result.isConfirmed) {
                extendSession();
            }
        });
    }

    function showFinalAlert() {
        if (isFinalAlertShown) return;
        isFinalAlertShown = true;
        isWarningShown = false;

        Swal.close(); // Cerramos cualquier alerta previa

        const now = Date.now();
        const overTime = now - lastActivityTime;

        // Si el usuario regresa y se pasó del límite por más de 5 segundos,
        // lo mandamos directo al login sin mostrarle el modal de los 5 segundos.
        if (overTime > (EXPIRY_TIME_MS + 5000)) {
            window.location.href = '/login';
            return;
        }

        // Si está en la pestaña viendo cómo se acaba el tiempo, le mostramos el conteo final
        Swal.fire({
            title: "🔴 Sesión Agotada",
            html: `
                <div style="text-align: center; padding: 10px;">
                    <p><strong style="color: #d63031;">Tu tiempo de inactividad ha superado el límite.</strong></p>
                    <p>Redirigiendo al login en <strong id="finalTimer">${FINAL_ALERT_DURATION_MS / 1000}</strong>...</p>
                </div>
            `,
            icon: "error",
            timer: FINAL_ALERT_DURATION_MS,
            timerProgressBar: true,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                const timerEl = document.getElementById("finalTimer");
                const interval = setInterval(() => {
                    const left = Swal.getTimerLeft();
                    if (timerEl && left) timerEl.textContent = Math.ceil(left / 1000);
                    if (!left) clearInterval(interval);
                }, 100);
            }
        }).then(() => {
            window.location.href = '/login';
        });
    }

    // ====== MONITOR DE INACTIVIDAD ======
    function checkInactivity() {
        const now = Date.now();
        const inactiveTime = now - lastActivityTime;

        if (inactiveTime >= EXPIRY_TIME_MS) {
            showFinalAlert();
        } else if (inactiveTime >= ALERT_THRESHOLD_MS && !isWarningShown && !isFinalAlertShown) {
            showTimeoutWarning();
        }
    }

    function init() {
        // Eventos que reinician la actividad del usuario
        const events = ['click', 'keydown', 'scroll', 'touchstart', 'mousemove'];
        events.forEach(e => document.addEventListener(e, resetActivity, { passive: true }));

        // Validar inactividad cada segundo
        checkInactivityInterval = setInterval(checkInactivity, 1000);

        // ESTO ES LO NUEVO Y MÁS IMPORTANTE:
        // Se dispara en el milisegundo exacto en que el usuario vuelve a la pestaña
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                checkInactivity();
            }
        });

        // Ping de mantenimiento cada 5 min (solo si está activo)
        setInterval(() => {
            if (isTabActive() && !isWarningShown && !isFinalAlertShown) {
                pingSession();
            }
        }, 300000);

        console.log("✅ Sistema de sesión estricto y preciso activado.");
    }

    // Esperar a que SweetAlert2 cargue si aún no lo ha hecho
    if (typeof Swal === 'undefined') {
        const checkSwal = setInterval(() => {
            if (typeof Swal !== 'undefined') {
                clearInterval(checkSwal);
                init();
            }
        }, 500);
    } else {
        init();
    }
})();
