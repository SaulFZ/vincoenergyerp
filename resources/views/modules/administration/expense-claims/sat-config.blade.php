@extends('modules.administration.expense-claims.index')

@section('content')
    <style>
        :root {
            --slate-dark: #0f172a;
            --slate-mid: #1e293b;
            --slate-light: #f1f5f9;
            --surface: #ffffff;
            --surface-alt: #f8fafc;
            --teal-dark: #0d9488;
            --teal-medium: #14b8a6;
            --alert-red: #ef4444;
        }

        .vault-container {
            max-width: 900px;
            margin: 0 auto;
            font-family: 'Poppins', sans-serif;
            padding: 2rem 1rem;
        }

        .view-header {
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--slate-light);
            padding-bottom: 1rem;
        }

        .view-title {
            font-size: 1.8rem;
            color: var(--slate-dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .view-title i {
            color: var(--teal-medium);
            font-size: 2.2rem;
        }

        .view-subtitle {
            color: #64748b;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .alert-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 4px solid #f59e0b;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        .alert-box i {
            color: #d97706;
            font-size: 1.5rem;
            margin-top: 0.2rem;
        }

        .alert-box p {
            margin: 0;
            font-size: 0.85rem;
            color: #92400e;
        }

        .card-vault {
            background: var(--surface);
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.03);
            overflow: hidden;
        }

        .card-vault-header {
            background: var(--slate-dark);
            padding: 1.5rem 2rem;
            color: #fff;
        }

        .card-vault-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-vault-body {
            padding: 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--slate-mid);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .input-text {
            padding: 0.75rem 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            font-family: inherit;
            color: var(--slate-dark);
            background: var(--surface-alt);
            transition: border-color 0.2s;
        }

        .input-text:focus {
            outline: none;
            border-color: var(--teal-medium);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1);
        }

        .file-upload-wrapper {
            position: relative;
            border: 2px dashed #cbd5e1;
            border-radius: 0.5rem;
            padding: 2rem 1rem;
            text-align: center;
            background: var(--surface-alt);
            transition: all 0.2s;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .file-upload-wrapper.has-file {
            border-color: var(--teal-medium);
            background: rgba(20, 184, 166, 0.08);
            border-style: solid;
        }

        .file-upload-wrapper:hover {
            border-color: var(--teal-medium);
            background: rgba(20, 184, 166, 0.05);
        }

        .file-upload-wrapper i {
            font-size: 2.5rem;
            color: #94a3b8;
            margin-bottom: 0.5rem;
            transition: color 0.2s;
        }

        .file-upload-wrapper.has-file i {
            color: var(--teal-medium);
        }

        .file-upload-wrapper p {
            margin: 0;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--slate-mid);
        }

        .file-upload-wrapper small {
            color: #64748b;
            font-size: 0.75rem;
            word-break: break-all;
        }

        .file-upload-wrapper input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .card-vault-footer {
            padding: 1.5rem 2rem;
            background: var(--surface-alt);
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            transition: all 0.2s;
        }

        .btn-ghost {
            background: transparent;
            color: #64748b;
        }

        .btn-ghost:hover {
            background: #e2e8f0;
            color: var(--slate-dark);
        }

        .btn-primary {
            background: var(--teal-dark);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--teal-medium);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(20, 184, 166, 0.25);
        }

        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Spinner nativo */
        .spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .card-vault-body {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="vault-container">

        <header class="view-header">
            <h2 class="view-title">
                <i class="bx bx-shield-quarter"></i>
                Bóveda de <strong>Credenciales SAT</strong>
            </h2>
            <p class="view-subtitle">Gestión segura de la e.firma (FIEL) para la automatización de consultas y validación de comprobantes XML.</p>
        </header>

        <div class="alert-box">
            <i class="bx bx-info-circle"></i>
            <div>
                <strong>Política de Privacidad y Seguridad:</strong>
                <p>Los archivos depositados en este módulo son encriptados y almacenados en un entorno aislado del servidor. La contraseña se somete a un algoritmo de cifrado irreversible para el personal de TI. El personal de desarrollo no tiene acceso a esta información.</p>
            </div>
        </div>

        {{-- Formulario con ID para capturarlo con JavaScript --}}
        <form action="{{ route('expense-claims.sat.store') }}" method="POST" enctype="multipart/form-data" id="sat-vault-form">
            @csrf
            <div class="card-vault">
                <div class="card-vault-header">
                    <h3><i class="bx bx-lock-alt"></i> Actualización de e.firma (FIEL)</h3>
                </div>

                <div class="card-vault-body">
                    {{-- Datos de la Empresa --}}
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Razón Social</label>
                        <input type="text" name="business_name" class="input-text" placeholder="Ej. Vinco Energy Services, S.A. de C.V." required>
                    </div>

                    <div class="form-group">
                        <label>RFC de la Empresa</label>
                        <input type="text" name="rfc" class="input-text" placeholder="VES1607057K7" maxlength="13" style="text-transform: uppercase;" required>
                    </div>

                    <div class="form-group">
                        <label>Contraseña de la Llave Privada (Passphrase)</label>
                        <div style="position: relative;">
                            <input type="password" name="passphrase" id="passphrase-input" class="input-text" placeholder="••••••••••••" style="width: 100%;" required>
                            <i class="bx bx-hide" id="toggle-password" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8; cursor: pointer; font-size: 1.2rem;"></i>
                        </div>
                    </div>

                    {{-- Zona de Archivos --}}
                    <div class="form-group">
                        <label>Certificado Público (.CER)</label>
                        <div class="file-upload-wrapper" id="cer-wrapper">
                            <i class="bx bx-badge-check" id="cer-icon"></i>
                            <p id="cer-text">Cargar archivo .cer</p>
                            <small id="cer-subtext">Solo archivos emitidos por el SAT</small>
                            <input type="file" name="cer_file" id="cer-input" accept=".cer" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Llave Privada (.KEY)</label>
                        <div class="file-upload-wrapper" id="key-wrapper">
                            <i class="bx bx-key" id="key-icon"></i>
                            <p id="key-text">Cargar archivo .key</p>
                            <small id="key-subtext">Cifrado bajo estándar de seguridad</small>
                            <input type="file" name="key_file" id="key-input" accept=".key" required>
                        </div>
                    </div>
                </div>

                <div class="card-vault-footer">
                    {{-- Usa el mismo sistema de redirección de tu JS --}}
                    <button type="button" class="btn btn-ghost" onclick="window.location.href='/administration/expense-claims/reimbursements'">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btn-submit-vault">
                        <i class="bx bx-save"></i> Guardar y Encriptar Credenciales
                    </button>
                </div>
            </div>
        </form>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* ── VISIBILIDAD DE CONTRASEÑA ── */
            const togglePassword = document.getElementById('toggle-password');
            const passwordInput = document.getElementById('passphrase-input');

            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                if (type === 'text') {
                    this.classList.remove('bx-hide');
                    this.classList.add('bx-show');
                    this.style.color = 'var(--teal-dark)';
                } else {
                    this.classList.remove('bx-show');
                    this.classList.add('bx-hide');
                    this.style.color = '#94a3b8';
                }
            });

            /* ── FEEDBACK VISUAL PARA CARGA DE ARCHIVOS ── */
            function setupFileInput(inputId, wrapperId, iconId, textId, subtextId, defaultText, defaultSubtext) {
                const input = document.getElementById(inputId);
                const wrapper = document.getElementById(wrapperId);
                const text = document.getElementById(textId);
                const subtext = document.getElementById(subtextId);

                input.addEventListener('change', function(e) {
                    if (this.files && this.files.length > 0) {
                        const file = this.files[0];
                        wrapper.classList.add('has-file');
                        text.textContent = 'Archivo Seleccionado';
                        text.style.color = 'var(--teal-dark)';
                        subtext.textContent = file.name;
                    } else {
                        wrapper.classList.remove('has-file');
                        text.textContent = defaultText;
                        text.style.color = 'var(--slate-mid)';
                        subtext.textContent = defaultSubtext;
                    }
                });
            }

            setupFileInput('cer-input', 'cer-wrapper', 'cer-icon', 'cer-text', 'cer-subtext', 'Cargar archivo .cer', 'Solo archivos emitidos por el SAT');
            setupFileInput('key-input', 'key-wrapper', 'key-icon', 'key-text', 'key-subtext', 'Cargar archivo .key', 'Cifrado bajo estándar de seguridad');

            /* ── ENVÍO VÍA AJAX (JAVASCRIPT) FETCH API ── */
            const vaultForm = document.getElementById('sat-vault-form');
            const btnSubmit = document.getElementById('btn-submit-vault');

            vaultForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Evita que la página recargue

                // Estado de carga
                const originalBtnContent = btnSubmit.innerHTML;
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="spinner"></span> Procesando...';

                // Recopilar datos
                const formData = new FormData(vaultForm);

                // Enviar al controlador Laravel
                fetch(vaultForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest', // Le dice a Laravel que es Ajax
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Alerta de éxito y redirección nativa
                        Swal.fire({
                            title: '<span style="font-family:\'Poppins\', sans-serif;">Actualización Exitosa</span>',
                            html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">${data.message}</span>`,
                            icon: 'success',
                            confirmButtonColor: 'var(--teal-dark)',
                            confirmButtonText: '<span style="font-family:\'Poppins\', sans-serif; font-weight:600;">Entendido</span>'
                        }).then(() => {
                            // Redirección usando tu lógica de URls
                            window.location.href = '/administration/expense-claims/reimbursements';
                        });
                    } else {
                        // Alerta de error de validación
                        Swal.fire({
                            title: 'Error de Validación',
                            text: data.message,
                            icon: 'warning',
                            confirmButtonColor: 'var(--teal-dark)'
                        });
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = originalBtnContent;
                    }
                })
                .catch(error => {
                    console.error("Error en la solicitud:", error);
                    Swal.fire({
                        title: 'Fallo de Conexión',
                        text: 'Ocurrió un error al procesar los archivos en el servidor.',
                        icon: 'error',
                        confirmButtonColor: 'var(--teal-dark)'
                    });
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = originalBtnContent;
                });
            });

        });
    </script>
@endpush
