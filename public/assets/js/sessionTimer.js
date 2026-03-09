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
    const SESSION_TIMEOUT_MINUTES = 15;
    const WARNING_TIME_MINUTES    = 1;

    const EXPIRY_TIME_MS       = SESSION_TIMEOUT_MINUTES * 60 * 1000;
    const ALERT_THRESHOLD_MS   = (SESSION_TIMEOUT_MINUTES - WARNING_TIME_MINUTES) * 60 * 1000;
    const FINAL_ALERT_DURATION = 6000;

    let lastActivityTime  = Date.now();
    let isWarningShown    = false;
    let isFinalAlertShown = false;
    let checkInactivityInterval;

    // ====== ESTILOS GLOBALES ======
    const styleEl = document.createElement('style');
    styleEl.textContent = `
        .swal-session-popup {
            border-radius: 16px !important;
            padding: 28px 24px 24px !important;
            box-shadow: 0 20px 60px rgba(0,0,0,0.18) !important;
            font-family: 'Segoe UI', system-ui, sans-serif !important;
        }
        .swal-session-title {
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            margin-bottom: 4px !important;
        }
        .swal-session-confirm {
            border-radius: 10px !important;
            font-weight: 600 !important;
            font-size: 0.9rem !important;
            padding: 10px 24px !important;
            letter-spacing: 0.3px !important;
            box-shadow: 0 4px 12px rgba(59,130,246,0.35) !important;
            transition: transform 0.15s, box-shadow 0.15s !important;
        }
        .swal-session-confirm:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 16px rgba(59,130,246,0.45) !important;
        }
        .session-timer-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #fff5f5 0%, #ffe4e4 100%);
            border: 1.5px solid #fca5a5;
            border-radius: 14px;
            padding: 14px 40px;
            margin: 10px 0 16px;
        }
        .session-timer-number {
            font-size: 2.8rem;
            font-weight: 800;
            color: #dc2626;
            line-height: 1;
            min-width: 64px;
            text-align: center;
            transition: transform 0.2s, color 0.2s;
            font-variant-numeric: tabular-nums;
        }
        .session-timer-label {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 4px;
            text-align: center;
        }
        .session-divider {
            width: 36px;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6, #6366f1);
            border-radius: 99px;
            margin: 0 auto 14px;
        }
        .session-subtitle {
            color: #64748b;
            font-size: 0.92rem;
            margin: 0 0 4px;
            line-height: 1.6;
        }
        .session-hint {
            color: #94a3b8;
            font-size: 0.78rem;
            margin: 0;
        }
    `;
    document.head.appendChild(styleEl);

    // ====== DETECCIÓN DE MODALES AJENOS ======
    function isExternalSwalOpen() {
        const swalVisible = Swal.isVisible();
        if (!swalVisible) return false;
        const ourModal = document.querySelector('.swal2-container [data-session-modal="true"]');
        return swalVisible && !ourModal;
    }

    // ====== AUXILIARES ======
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
        if (isWarningShown || isFinalAlertShown) return;
        lastActivityTime = Date.now();
    }

    async function extendSession() {
        await pingSession();
        lastActivityTime = Date.now();
        isWarningShown   = false;
        Swal.close();
    }

    // ====== MODAL: ADVERTENCIA (1 min antes) ======
    function showTimeoutWarning() {
        if (isWarningShown || isFinalAlertShown) return;
        if (isExternalSwalOpen()) return;

        isWarningShown = true;

        Swal.fire({
            icon: 'warning',
            title: 'Sesión a punto de expirar',
            html: `
                <div data-session-modal="true">
                    <div class="session-divider"></div>
                    <p class="session-subtitle">
                        Tu sesión cerrará pronto por <strong>inactividad</strong>.<br>
                        Tiempo restante:
                    </p>
                    <div class="session-timer-pill">
                        <div>
                            <div id="sessionTimer" class="session-timer-number">--</div>
                            <div class="session-timer-label">segundos</div>
                        </div>
                    </div>
                    <p class="session-hint">Haz clic en el botón para continuar trabajando.</p>
                </div>
            `,
            didOpen: () => {
                const popup = Swal.getPopup();
                if (popup) popup.setAttribute('data-session-modal', 'true');

                const timerEl  = document.getElementById('sessionTimer');
                const timerInt = setInterval(() => {
                    const elapsed  = Date.now() - lastActivityTime;
                    const timeLeft = Math.max(0, Math.ceil((EXPIRY_TIME_MS - elapsed) / 1000));

                    if (timerEl) {
                        timerEl.textContent = timeLeft;
                        if (timeLeft <= 10) {
                            timerEl.style.color     = '#b91c1c';
                            timerEl.style.transform = 'scale(1.12)';
                        } else {
                            timerEl.style.color     = '#dc2626';
                            timerEl.style.transform = 'scale(1)';
                        }
                    }

                    if (timeLeft <= 0) {
                        clearInterval(timerInt);
                        showFinalAlert();
                    }
                }, 1000);

                if (popup) popup._timerInt = timerInt;
            },
            willClose: () => {
                const popup = Swal.getPopup();
                if (popup && popup._timerInt) clearInterval(popup._timerInt);
            },
            showCancelButton: false,
            confirmButtonText: 'Continuar trabajando',
            confirmButtonColor: '#3b82f6',
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: {
                popup:         'swal-session-popup',
                title:         'swal-session-title',
                confirmButton: 'swal-session-confirm',
            }
        }).then((result) => {
            if (result.isConfirmed) extendSession();
        });
    }

    // ====== MODAL: SESIÓN EXPIRADA ======
    function showFinalAlert() {
        if (isFinalAlertShown) return;
        isFinalAlertShown = true;
        isWarningShown    = false;

        Swal.close();

        const overTime = Date.now() - lastActivityTime;
        if (overTime > EXPIRY_TIME_MS + 5000) {
            window.location.href = '/login';
            return;
        }

        Swal.fire({
            icon: 'error',
            title: 'Sesión cerrada',
            html: `
                <div data-session-modal="true">
                    <div class="session-divider" style="background: linear-gradient(90deg, #ef4444, #dc2626);"></div>
                    <p class="session-subtitle" style="margin-bottom: 10px;">
                        Tu sesión fue cerrada por <strong>inactividad</strong>.<br>
                        Serás redirigido al inicio de sesión.
                    </p>
                    <div class="session-timer-pill" style="
                        background: linear-gradient(135deg, #fff5f5, #fee2e2);
                        border-color: #fca5a5;
                    ">
                        <div>
                            <div id="finalTimer" class="session-timer-number">
                                ${Math.ceil(FINAL_ALERT_DURATION / 1000)}
                            </div>
                            <div class="session-timer-label">segundos</div>
                        </div>
                    </div>
                </div>
            `,
            didOpen: () => {
                const popup = Swal.getPopup();
                if (popup) popup.setAttribute('data-session-modal', 'true');

                const timerEl  = document.getElementById('finalTimer');
                const interval = setInterval(() => {
                    const left = Swal.getTimerLeft();
                    if (timerEl && left !== undefined) timerEl.textContent = Math.ceil(left / 1000);
                    if (!left) clearInterval(interval);
                }, 100);
            },
            timer: FINAL_ALERT_DURATION,
            timerProgressBar: true,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: {
                popup: 'swal-session-popup',
                title: 'swal-session-title',
            }
        }).then(() => {
            window.location.href = '/login';
        });
    }

    // ====== MONITOR PRINCIPAL ======
    function checkInactivity() {
        if (isFinalAlertShown) return;

        const elapsed = Date.now() - lastActivityTime;

        if (elapsed >= EXPIRY_TIME_MS) {
            showFinalAlert();
        } else if (elapsed >= ALERT_THRESHOLD_MS && !isWarningShown) {
            if (!isExternalSwalOpen()) showTimeoutWarning();
        }
    }

    // ====== INICIALIZACIÓN ======
    function init() {
        const activityEvents = ['click', 'keydown', 'scroll', 'touchstart', 'mousemove'];
        activityEvents.forEach(e => document.addEventListener(e, resetActivity, { passive: true }));

        checkInactivityInterval = setInterval(checkInactivity, 1000);

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) checkInactivity();
        });

        setInterval(() => {
            if (isTabActive() && !isWarningShown && !isFinalAlertShown) pingSession();
        }, 300000);
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
