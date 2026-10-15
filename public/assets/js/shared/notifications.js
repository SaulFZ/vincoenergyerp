/* ══════════════════════════════════════════════════════════════════════════
   VESCORE - SISTEMA DE NOTIFICACIONES UNIFICADO
   Archivo: public/assets/js/shared/notifications.js

   API PÚBLICA (window.notify.*)

   ── TOASTS ──
   notify.toast({ type, title, message, duration, position, icon, closable, progress })
   notify.info(msg, title?), notify.success(), notify.validation(),
   notify.warning(), notify.danger(), notify.error = notify.danger

   ── ALERT (modal simple, 1 botón) ──
   notify.alert({ type, title, subtitle, text, icon, confirmText, confirmStyle })

   ── CONFIRM (modal 2 botones, promesa) ──
   const { confirmed } = await notify.confirm({ type, title, text,
       confirmText, cancelText, confirmStyle, cancelStyle })

   ── PROMPT (modal con input/textarea, promesa) ──
   const { confirmed, value } = await notify.prompt({
       type, title, text, inputType, placeholder, required, minLength, maxLength })

   ── LOADING ──
   notify.loading({ title, text, type })
   notify.hideLoading()

   ── UTILIDADES ──
   notify.closeAll()
   ══════════════════════════════════════════════════════════════════════════ */

(function (window, document) {
    'use strict';

    /* ══════════════════════════════════════════════════════════════
       ── CONFIGURACIÓN DE TIPOS ──
       ══════════════════════════════════════════════════════════════ */
    const TYPES = {
        info: {
            icon: 'bx-info-circle',
            title: 'Información',
            subtitle: 'Aviso del sistema',
            btnStyle: 'info'
        },
        success: {
            icon: 'bx-check-circle',
            title: '¡Operación Exitosa!',
            subtitle: 'La acción se completó correctamente',
            btnStyle: 'success'
        },
        validation: {
            icon: 'bx-list-check',
            title: 'Validación Requerida',
            subtitle: 'Revisa la información proporcionada',
            btnStyle: 'validation'
        },
        warning: {
            icon: 'bx-error-circle',
            title: 'Atención Requerida',
            subtitle: 'Verifica antes de continuar',
            btnStyle: 'warning'
        },
        danger: {
            icon: 'bx-x-circle',
            title: 'Error en la Operación',
            subtitle: 'Algo salió mal',
            btnStyle: 'danger'
        }
    };

    const DEFAULT_TYPE = 'info';
    const DEFAULT_POSITION = 'top-right';

    /* ══════════════════════════════════════════════════════════════
       ── UTILIDADES INTERNAS ──
       ══════════════════════════════════════════════════════════════ */
    function getTypeConfig(type) {
        return TYPES[type] || TYPES[DEFAULT_TYPE];
    }

    function createElement(tag, className, html) {
        const el = document.createElement(tag);
        if (className) el.className = className;
        if (html !== undefined) el.innerHTML = html;
        return el;
    }

    function safeText(str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    /* ══════════════════════════════════════════════════════════════
       ── 1. TOASTS ──
       ══════════════════════════════════════════════════════════════ */
    const toastContainers = {};

    function getToastContainer(position) {
        if (toastContainers[position] && document.body.contains(toastContainers[position])) {
            return toastContainers[position];
        }
        const container = createElement('div', `notif-toast-container notif-toast-${position}`);
        container.setAttribute('aria-live', 'polite');
        container.setAttribute('aria-atomic', 'true');
        document.body.appendChild(container);
        toastContainers[position] = container;
        return container;
    }

    function toast(options) {
        if (typeof options === 'string') {
            options = { message: options };
        }
        const opts = options || {};
        const type = opts.type || DEFAULT_TYPE;
        const config = getTypeConfig(type);
        const position = opts.position || DEFAULT_POSITION;
        const duration = opts.duration === undefined ? 4500 : opts.duration;
        const closable = opts.closable !== false;
        const showProgress = opts.progress !== false && duration > 0;
        const title = opts.title !== undefined ? opts.title : config.title;
        const icon = opts.icon || config.icon;

        const container = getToastContainer(position);

        const toastEl = createElement('div', `notif-toast type-${type}`);
        toastEl.setAttribute('role', 'alert');

        const iconEl = createElement('div', 'notif-toast-icon', `<i class="bx ${icon}"></i>`);
        toastEl.appendChild(iconEl);

        const contentEl = createElement('div', 'notif-toast-content');
        if (title) {
            const titleEl = createElement('div', 'notif-toast-title', safeText(title));
            contentEl.appendChild(titleEl);
        }
        const messageEl = createElement('div', 'notif-toast-message');
        messageEl.innerHTML = opts.html ? opts.message : safeText(opts.message || '');
        contentEl.appendChild(messageEl);
        toastEl.appendChild(contentEl);

        if (closable) {
            const closeBtn = createElement('button', 'notif-toast-close', '<i class="bx bx-x"></i>');
            closeBtn.setAttribute('aria-label', 'Cerrar notificación');
            toastEl.appendChild(closeBtn);
        }

        if (showProgress) {
            const progress = createElement('div', 'notif-toast-progress');
            progress.style.animationDuration = `${duration}ms`;
            toastEl.appendChild(progress);
        }

        container.appendChild(toastEl);

        let isRemoved = false;
        function remove() {
            if (isRemoved) return;
            isRemoved = true;
            toastEl.classList.add('notif-toast-hiding');
            setTimeout(() => toastEl.remove(), 320);
        }

        if (closable) {
            const closeBtn = toastEl.querySelector('.notif-toast-close');
            if (closeBtn) closeBtn.addEventListener('click', remove);
        }

        // Pausar animación al pasar el mouse (si tiene duración)
        if (duration > 0) {
            let timer = setTimeout(remove, duration);
            toastEl.addEventListener('mouseenter', () => {
                clearTimeout(timer);
                const progress = toastEl.querySelector('.notif-toast-progress');
                if (progress) progress.style.animationPlayState = 'paused';
            });
            toastEl.addEventListener('mouseleave', () => {
                const progress = toastEl.querySelector('.notif-toast-progress');
                if (progress) progress.style.animationPlayState = 'running';
                timer = setTimeout(remove, 1200);
            });
        }

        return { close: remove, element: toastEl };
    }

    /* Atajos de toast */
    function quickToast(type) {
        return function (message, title) {
            return toast({ type, message, title: title || getTypeConfig(type).title });
        };
    }

    /* ══════════════════════════════════════════════════════════════
       ── 2. MODAL BASE (usado por alert, confirm, prompt) ──
       ══════════════════════════════════════════════════════════════ */
    function createModal(options) {
        const opts = options || {};
        const type = opts.type || DEFAULT_TYPE;
        const config = getTypeConfig(type);
        const icon = opts.icon || config.icon;

        const overlay = createElement('div', `notif-modal-overlay type-${type}`);

        const modal = createElement('div', 'notif-modal');
        if (opts.width === 'wide') modal.classList.add('notif-modal-wide');
        if (opts.width === 'sm') modal.classList.add('notif-modal-sm');

        // Topbar
        modal.appendChild(createElement('div', 'notif-modal-topbar'));

        // Header
        const header = createElement('div', 'notif-modal-header');
        const iconEl = createElement('div', 'notif-modal-icon', `<i class="bx ${icon}"></i>`);
        header.appendChild(iconEl);

        const headText = createElement('div', 'notif-modal-headtext');
        const titleEl = createElement('div', 'notif-modal-title', safeText(opts.title || config.title));
        headText.appendChild(titleEl);
        if (opts.subtitle !== false) {
            const subtitleEl = createElement('div', 'notif-modal-subtitle', safeText(opts.subtitle || config.subtitle));
            headText.appendChild(subtitleEl);
        }
        header.appendChild(headText);
        modal.appendChild(header);

        // Body
        const body = createElement('div', 'notif-modal-body');
        if (opts.text || opts.html) {
            const textEl = createElement('div', 'notif-modal-text');
            textEl.innerHTML = opts.html ? opts.text : safeText(opts.text);
            body.appendChild(textEl);
        }

        // Input / Textarea
        let inputEl = null;
        let errorMsgEl = null;
        let charCountEl = null;

        if (opts.input) {
            const inputOpts = typeof opts.input === 'object' ? opts.input : {};
            const inputWrap = createElement('div', 'notif-modal-input-wrap');

            if (inputOpts.label) {
                const label = createElement('label', 'notif-modal-input-label', safeText(inputOpts.label));
                inputWrap.appendChild(label);
            }

            const isTextarea = inputOpts.inputType !== 'text';
            inputEl = createElement(isTextarea ? 'textarea' : 'input',
                isTextarea ? 'notif-modal-textarea' : 'notif-modal-input');

            if (!isTextarea) inputEl.type = 'text';
            inputEl.placeholder = inputOpts.placeholder || 'Escribe aquí...';
            if (inputOpts.value) inputEl.value = inputOpts.value;
            if (inputOpts.maxLength) inputEl.maxLength = inputOpts.maxLength;
            inputWrap.appendChild(inputEl);

            // Meta (contador + error)
            const meta = createElement('div', 'notif-modal-input-meta');
            errorMsgEl = createElement('span', 'notif-modal-input-error-msg');
            meta.appendChild(errorMsgEl);

            if (inputOpts.maxLength) {
                charCountEl = createElement('span', 'notif-modal-char-count');
                charCountEl.textContent = `0 / ${inputOpts.maxLength}`;
                meta.appendChild(charCountEl);
                inputEl.addEventListener('input', () => {
                    charCountEl.textContent = `${inputEl.value.length} / ${inputOpts.maxLength}`;
                    if (inputEl.value.length > inputOpts.maxLength) {
                        charCountEl.style.color = 'var(--notif-danger-color)';
                    } else {
                        charCountEl.style.color = '';
                    }
                });
            }
            inputWrap.appendChild(meta);
            body.appendChild(inputWrap);
        }

        modal.appendChild(body);

        // Footer
        const footer = createElement('div', 'notif-modal-footer');
        modal.appendChild(footer);
        overlay.appendChild(modal);
        document.body.appendChild(overlay);

        // Animar entrada
        requestAnimationFrame(() => {
            overlay.classList.add('notif-active');
            overlay.setAttribute('aria-hidden', 'false');
            if (inputEl) setTimeout(() => inputEl.focus(), 200);
        });

        return {
            overlay,
            modal,
            footer,
            inputEl,
            errorMsgEl,
            inputOpts: opts.input ? (typeof opts.input === 'object' ? opts.input : {}) : null
        };
    }

    function buildButton(text, style, icon) {
        const btn = createElement('button', `notif-btn notif-btn-${style}`);
        btn.type = 'button';
        const iconHtml = icon ? `<i class="bx ${icon}"></i>` : '';
        btn.innerHTML = `${iconHtml}<span>${safeText(text)}</span>`;
        return btn;
    }

    /* ══════════════════════════════════════════════════════════════
       ── 3. ALERT (modal 1 botón) ──
       ══════════════════════════════════════════════════════════════ */
    function alertModal(options) {
        return new Promise((resolve) => {
            const opts = options || {};
            const modal = createModal(opts);
            const type = opts.type || DEFAULT_TYPE;
            const config = getTypeConfig(type);

            const confirmStyle = opts.confirmStyle || config.btnStyle;
            const confirmIcon = opts.confirmIcon !== undefined ? opts.confirmIcon : 'bx-check';
            const confirmText = opts.confirmText || 'Entendido';
            const btnConfirm = buildButton(confirmText, confirmStyle, confirmIcon);
            modal.footer.appendChild(btnConfirm);

            let closed = false;
            function close(result) {
                if (closed) return;
                closed = true;
                modal.overlay.classList.remove('notif-active');
                setTimeout(() => modal.overlay.remove(), 300);
                document.removeEventListener('keydown', onKey);
                resolve(result || { confirmed: true });
            }

            function onKey(e) { if (e.key === 'Escape') close({ confirmed: true }); }

            btnConfirm.addEventListener('click', () => close({ confirmed: true }));
            if (opts.closeOnOverlay !== false) {
                modal.overlay.addEventListener('click', (e) => {
                    if (e.target === modal.overlay) close({ confirmed: true });
                });
            }
            document.addEventListener('keydown', onKey);
            setTimeout(() => btnConfirm.focus(), 200);
        });
    }

    /* ══════════════════════════════════════════════════════════════
       ── 4. CONFIRM (modal 2 botones, promesa) ──
       ══════════════════════════════════════════════════════════════ */
    function confirmModal(options) {
        return new Promise((resolve) => {
            const opts = options || {};
            const modal = createModal(opts);
            const type = opts.type || 'warning';
            const config = getTypeConfig(type);

            const cancelStyle = opts.cancelStyle || 'ghost';
            const cancelText = opts.cancelText || 'Cancelar';
            const cancelIcon = opts.cancelIcon !== undefined ? opts.cancelIcon : 'bx-x';

            const confirmStyle = opts.confirmStyle || config.btnStyle;
            const confirmText = opts.confirmText || 'Confirmar';
            const confirmIcon = opts.confirmIcon !== undefined ? opts.confirmIcon : 'bx-check';

            const btnCancel = buildButton(cancelText, cancelStyle, cancelIcon);
            const btnConfirm = buildButton(confirmText, confirmStyle, confirmIcon);

            modal.footer.appendChild(btnCancel);
            modal.footer.appendChild(btnConfirm);

            let closed = false;
            function close(result) {
                if (closed) return;
                closed = true;
                modal.overlay.classList.remove('notif-active');
                setTimeout(() => modal.overlay.remove(), 300);
                document.removeEventListener('keydown', onKey);
                resolve(result);
            }

            function onKey(e) {
                if (e.key === 'Escape') close({ confirmed: false });
                if (e.key === 'Enter' && document.activeElement !== btnCancel) {
                    close({ confirmed: true });
                }
            }

            btnConfirm.addEventListener('click', () => close({ confirmed: true }));
            btnCancel.addEventListener('click', () => close({ confirmed: false }));
            if (opts.closeOnOverlay !== false) {
                modal.overlay.addEventListener('click', (e) => {
                    if (e.target === modal.overlay) close({ confirmed: false });
                });
            }
            document.addEventListener('keydown', onKey);
            setTimeout(() => btnConfirm.focus(), 200);
        });
    }

    /* ══════════════════════════════════════════════════════════════
       ── 5. PROMPT (modal con input/textarea, promesa) ──
       ══════════════════════════════════════════════════════════════ */
    function promptModal(options) {
        return new Promise((resolve) => {
            const opts = options || {};
            const inputOpts = typeof opts.input === 'object' ? opts.input : {};
            const modal = createModal(Object.assign({}, opts, { input: inputOpts }));

            const type = opts.type || DEFAULT_TYPE;
            const config = getTypeConfig(type);

            const cancelStyle = opts.cancelStyle || 'ghost';
            const cancelText = opts.cancelText || 'Cancelar';
            const confirmStyle = opts.confirmStyle || config.btnStyle;
            const confirmText = opts.confirmText || 'Confirmar';

            const btnCancel = buildButton(cancelText, cancelStyle, 'bx-x');
            const btnConfirm = buildButton(confirmText, confirmStyle, 'bx-check');

            modal.footer.appendChild(btnCancel);
            modal.footer.appendChild(btnConfirm);

            let closed = false;
            function close(result) {
                if (closed) return;
                closed = true;
                modal.overlay.classList.remove('notif-active');
                setTimeout(() => modal.overlay.remove(), 300);
                document.removeEventListener('keydown', onKey);
                resolve(result);
            }

            function showError(msg) {
                if (!modal.errorMsgEl) return;
                modal.errorMsgEl.textContent = msg;
                modal.errorMsgEl.classList.add('notif-show');
                if (modal.inputEl) {
                    modal.inputEl.classList.add('notif-input-error');
                    setTimeout(() => modal.inputEl.classList.remove('notif-input-error'), 500);
                    modal.inputEl.focus();
                }
            }

            function clearError() {
                if (modal.errorMsgEl) modal.errorMsgEl.classList.remove('notif-show');
            }

            function validate() {
                const val = modal.inputEl ? modal.inputEl.value.trim() : '';
                if (inputOpts.required && !val) {
                    showError(inputOpts.requiredMsg || 'Este campo es obligatorio.');
                    return false;
                }
                if (inputOpts.minLength && val.length < inputOpts.minLength) {
                    showError(`Debe tener al menos ${inputOpts.minLength} caracteres.`);
                    return false;
                }
                if (inputOpts.maxLength && val.length > inputOpts.maxLength) {
                    showError(`No puede exceder ${inputOpts.maxLength} caracteres.`);
                    return false;
                }
                if (inputOpts.validator) {
                    const err = inputOpts.validator(val);
                    if (err) { showError(err); return false; }
                }
                return true;
            }

            function onConfirm() {
                if (inputOpts.validate !== false && !validate()) return;
                const val = modal.inputEl ? modal.inputEl.value.trim() : '';
                close({ confirmed: true, value: val });
            }

            function onCancel() { close({ confirmed: false, value: null }); }

            function onKey(e) {
                if (e.key === 'Escape') onCancel();
                if (e.key === 'Enter' && !e.shiftKey && modal.inputEl && modal.inputEl.tagName === 'INPUT') {
                    e.preventDefault();
                    onConfirm();
                }
            }

            btnConfirm.addEventListener('click', onConfirm);
            btnCancel.addEventListener('click', onCancel);
            if (opts.closeOnOverlay !== false) {
                modal.overlay.addEventListener('click', (e) => {
                    if (e.target === modal.overlay) onCancel();
                });
            }
            if (modal.inputEl) {
                modal.inputEl.addEventListener('input', clearError);
            }
            document.addEventListener('keydown', onKey);
        });
    }

    /* ══════════════════════════════════════════════════════════════
       ── 6. LOADING ──
       ══════════════════════════════════════════════════════════════ */
    let loadingOverlay = null;
    let loadingCounter = 0;

    function showLoading(options) {
        if (typeof options === 'string') options = { title: options };
        const opts = options || {};
        const type = opts.type || 'info';

        loadingCounter++;

        if (!loadingOverlay) {
            loadingOverlay = createElement('div', `notif-loading-overlay type-${type}`);
            loadingOverlay.setAttribute('aria-hidden', 'true');

            const box = createElement('div', 'notif-loading-box');
            const spinner = createElement('div', 'notif-loading-spinner');
            box.appendChild(spinner);

            const titleEl = createElement('div', 'notif-loading-text');
            titleEl.textContent = opts.title || 'Procesando...';
            box.appendChild(titleEl);

            if (opts.text || opts.subtext) {
                const subtextEl = createElement('div', 'notif-loading-subtext');
                subtextEl.textContent = opts.text || opts.subtext;
                box.appendChild(subtextEl);
            }

            loadingOverlay.appendChild(box);
            document.body.appendChild(loadingOverlay);
        } else {
            // Actualiza el texto si ya existe
            const titleEl = loadingOverlay.querySelector('.notif-loading-text');
            if (titleEl && opts.title) titleEl.textContent = opts.title;
            const subtextEl = loadingOverlay.querySelector('.notif-loading-subtext');
            const newSubtext = opts.text || opts.subtext;
            if (subtextEl && newSubtext) subtextEl.textContent = newSubtext;
            loadingOverlay.className = `notif-loading-overlay type-${type}`;
        }

        requestAnimationFrame(() => {
            loadingOverlay.classList.add('notif-active');
            loadingOverlay.setAttribute('aria-hidden', 'false');
        });
    }

    function hideLoading() {
        if (loadingCounter > 0) loadingCounter--;
        if (loadingCounter > 0) return;
        if (!loadingOverlay) return;
        loadingOverlay.classList.remove('notif-active');
        loadingOverlay.setAttribute('aria-hidden', 'true');
        const overlayRef = loadingOverlay;
        setTimeout(() => {
            if (overlayRef && overlayRef.parentNode) overlayRef.remove();
            if (loadingOverlay === overlayRef) loadingOverlay = null;
        }, 250);
    }

    /* ══════════════════════════════════════════════════════════════
       ── 7. UTILIDADES ──
       ══════════════════════════════════════════════════════════════ */
    function closeAll() {
        document.querySelectorAll('.notif-toast').forEach(t => t.remove());
        document.querySelectorAll('.notif-modal-overlay').forEach(m => {
            m.classList.remove('notif-active');
            setTimeout(() => m.remove(), 300);
        });
        hideLoading();
    }

    /* ══════════════════════════════════════════════════════════════
       ── 8. API PÚBLICA ──
       ══════════════════════════════════════════════════════════════ */
    window.notify = {
        // Toasts
        toast,
        info: quickToast('info'),
        success: quickToast('success'),
        validation: quickToast('validation'),
        warning: quickToast('warning'),
        danger: quickToast('danger'),
        error: quickToast('danger'),

        // Modales
        alert: alertModal,
        confirm: confirmModal,
        prompt: promptModal,

        // Loading
        loading: showLoading,
        showLoading: showLoading,
        hideLoading,
        closeLoading: hideLoading,

        // Extras
        closeAll,
        types: TYPES
    };

    // Aliases legacy (para reemplazar rápidamente Swal.fire)
    window.showToast = window.notify.toast;
    window.showConfirm = window.notify.confirm;
    window.showAlert = function (msg, type) {
        return window.notify.toast(msg, type || 'info');
    };

})(window, document);
