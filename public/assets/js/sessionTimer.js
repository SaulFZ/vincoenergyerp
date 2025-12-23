(function () {
    // ====== VERIFICACIÓN INICIAL ======
    const currentPath = window.location.pathname.toLowerCase();
    const excludedPaths = ['/login', '/logout', '/password', '/reset', '/register', '/verify-email'];
    const hasLoginElements = document.querySelector('#loginForm') !== null ||
                             document.querySelector('#forgotPasswordLink') !== null ||
                             document.querySelector('.toggle-password') !== null;

    const shouldSkip = excludedPaths.some(path => currentPath.includes(path)) || hasLoginElements;

    if (shouldSkip) return;

    // ====== CONFIGURACIÓN ======
    const SESSION_TIMEOUT_MINUTES = 10;
    const WARNING_TIME_MINUTES = 1;

    const EXPIRY_TIME_MS = SESSION_TIMEOUT_MINUTES * 60 * 1000;
    const ALERT_THRESHOLD_MS = (SESSION_TIMEOUT_MINUTES - WARNING_TIME_MINUTES) * 60 * 1000;
    const FINAL_ALERT_DURATION_MS = 5000;
    const EXTEND_GRACE_PERIOD_MS = 10000; // 10 segundos de "gracia" tras dar a continuar

    let lastActivityTime = Date.now();
    let isWarningShown = false;
    let isFinalAlertShown = false;
    let lastExtendClickTime = 0; // Para evitar que el modal reaparezca instantáneamente

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
        // Si el modal está visible, no reseteamos por movimiento externo (queremos que den clic al botón)
        if (isWarningShown || isFinalAlertShown) return;
        lastActivityTime = Date.now();
    }

    async function extendSession() {
        console.log("🔄 Extendiendo sesión local...");

        // 1. Reiniciar tiempos inmediatamente
        lastActivityTime = Date.now();
        lastExtendClickTime = Date.now(); // Marca el momento del clic
        isWarningShown = false;

        // 2. Cerrar alerta
        Swal.close();

        // 3. Avisar al servidor en segundo plano
        pingSession().then(success => {
            if (success) console.log("✅ Servidor sincronizado");
        });
    }

    // ====== MODALES ======

    function showTimeoutWarning() {
        // NO mostrar si ya está visible, si la sesión ya acabó, o si acabamos de dar clic a "Continuar"
        const timeSinceLastExtend = Date.now() - lastExtendClickTime;
        if (isWarningShown || isFinalAlertShown || timeSinceLastExtend < EXTEND_GRACE_PERIOD_MS) return;

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
            allowEnterKey: true,
            didOpen: () => {
                const timerEl = document.getElementById("sessionTimer");
                const timerInt = setInterval(() => {
                    const now = Date.now();
                    const timeLeft = Math.max(0, Math.ceil((EXPIRY_TIME_MS - (now - lastActivityTime)) / 1000));

                    if (timerEl) timerEl.textContent = `${timeLeft}s`;

                    // Si el usuario da clic o el tiempo se agota, matamos este intervalo
                    if (!isWarningShown || timeLeft <= 0 || isFinalAlertShown) {
                        clearInterval(timerInt);
                    }
                }, 1000);
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

        Swal.close();

        Swal.fire({
            title: "🔴 Sesión Agotada",
            html: `
                <div style="text-align: center; padding: 10px;">
                    <p><strong style="color: #d63031;">Tu tiempo de inactividad ha superado el límite.</strong></p>
                    <p>Redirigiendo al login en <strong id="finalTimer">5</strong>...</p>
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

        // Si ya expiró el tiempo total
        if (inactiveTime >= EXPIRY_TIME_MS) {
            showFinalAlert();
        }
        // Si entró en el rango de alerta y NO estamos en periodo de gracia
        else if (inactiveTime >= ALERT_THRESHOLD_MS && !isWarningShown) {
            showTimeoutWarning();
        }
    }

    function init() {
        const events = ['click', 'keydown', 'scroll', 'touchstart', 'mousemove'];
        events.forEach(e => document.addEventListener(e, resetActivity, { passive: true }));

        setInterval(checkInactivity, 1000);

        // Ping de mantenimiento cada 5 min (siempre que el usuario sea activo)
        setInterval(() => {
            if (isTabActive() && !isWarningShown && !isFinalAlertShown) {
                pingSession();
            }
        }, 300000);

        console.log("✅ Sistema de sesión robusto con periodo de gracia activado.");
    }

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
