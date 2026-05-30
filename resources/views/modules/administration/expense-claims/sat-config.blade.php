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
            --alert-orange: #f59e0b;
        }

        /* ── CONTENEDOR PRINCIPAL FLUIDO ── */
        .vault-container {
            width: 100%;
            padding: 2rem;
            font-family: 'Poppins', sans-serif;
        }

        .view-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
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

        /* ── TARJETA DE AUDITORÍA ── */
        .card-vault {
            background: var(--surface);
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.03);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card-vault-header {
            background: var(--slate-dark);
            padding: 1.25rem 2rem;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-vault-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* ── TABLA DE NODOS (FULL WIDTH) ── */
        .table-scroll {
            overflow-x: auto;
            width: 100%;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .data-table thead tr {
            background: var(--slate-light);
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .data-table thead th {
            padding: 1rem 1.5rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .data-table tbody tr {
            border-bottom: 1px solid var(--surface-alt);
            transition: background .12s;
        }

        .data-table tbody tr:hover {
            background: #f8fafc;
        }

        .data-table tbody td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
            font-size: 0.85rem;
            color: var(--slate-mid);
        }

        /* ── BADGES Y ESTADOS ── */
        .badge-live {
            background: #d1fae5;
            color: #065f46;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .badge-expired {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            animation: pulse-red 2s infinite;
        }

        .badge-history {
            background: #e2e8f0;
            color: #475569;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        @keyframes pulse-red {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            70% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        /* ── BOTONES ── */
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

        .btn-primary {
            background: var(--teal-dark);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--teal-medium);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(20, 184, 166, 0.25);
        }

        .btn-alert {
            background: var(--alert-red);
            color: #fff;
        }

        .btn-alert:hover {
            background: #dc2626;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
        }

        .btn-ghost {
            background: transparent;
            color: #64748b;
            border: 1px solid #cbd5e1;
        }

        .btn-ghost:hover {
            background: var(--slate-light);
            color: var(--slate-dark);
        }

        /* ── MODAL FLOTANTE (ESTILO ERP) ── */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-box {
            background: var(--surface);
            width: 100%;
            max-width: 850px;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transform: translateY(20px) scale(0.98);
            transition: all 0.3s ease;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        .modal-overlay.active .modal-box {
            transform: translateY(0) scale(1);
        }

        .modal-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--slate-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--surface-alt);
            border-radius: 1rem 1rem 0 0;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            color: var(--slate-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-close {
            background: transparent;
            border: none;
            font-size: 1.5rem;
            color: #94a3b8;
            cursor: pointer;
            transition: color 0.2s;
        }

        .btn-close:hover {
            color: var(--alert-red);
        }

        .modal-body {
            padding: 2rem;
            overflow-y: auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem 2rem;
        }

        .modal-footer {
            padding: 1.5rem 2rem;
            border-top: 1px solid var(--slate-light);
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            background: var(--surface-alt);
            border-radius: 0 0 1rem 1rem;
        }

        /* ── FORMULARIOS INTERNOS ── */
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

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-group i.field-icon {
            position: absolute;
            left: 1rem;
            color: #94a3b8;
            font-size: 1.2rem;
            pointer-events: none;
            z-index: 2;
        }

        .input-text {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.8rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            font-family: inherit;
            color: var(--slate-dark);
            background: var(--surface);
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .input-text:focus {
            outline: none;
            border-color: var(--teal-medium);
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
            height: 100%;
        }

        .file-upload-wrapper.has-file {
            border-color: var(--teal-medium);
            background: rgba(20, 184, 166, 0.08);
            border-style: solid;
        }

        .file-upload-wrapper:hover {
            border-color: var(--teal-medium);
        }

        .file-upload-wrapper i {
            font-size: 2.5rem;
            color: #94a3b8;
            margin-bottom: 0.5rem;
        }

        .file-upload-wrapper.has-file i { color: var(--teal-medium); }

        .file-upload-wrapper p {
            margin: 0;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--slate-mid);
        }

        .file-upload-wrapper small {
            color: #64748b;
            font-size: 0.75rem;
        }

        .file-upload-wrapper input[type="file"] {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0; cursor: pointer;
        }

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
            .modal-body {
                grid-template-columns: 1fr;
            }
            .form-group[style] {
                grid-column: 1 / -1 !important;
            }
        }
    </style>

    <div class="vault-container">

        <header class="view-header">
            <div>
                <h2 class="view-title">
                    <i class="bx bx-shield-quarter"></i>
                    Gestión de <strong>Nodos de Seguridad</strong>
                </h2>
                <p class="view-subtitle">Monitorización y cifrado de credenciales de red para integración de facturación electrónica.</p>
            </div>
            <div>
                <button class="btn btn-primary" onclick="openNodeModal()">
                    <i class="bx bx-plus-circle"></i> Nuevo Nodo
                </button>
            </div>
        </header>

        {{-- ── TABLA DE AUDITORÍA: HISTORIAL DE NODOS ── --}}
        <div class="card-vault">
            <div class="card-vault-header">
                <h3><i class="bx bx-server"></i> Nodos Criptográficos en el Servidor</h3>
            </div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;"># ID</th>
                            <th>Entidad / Razón Social</th>
                            <th>Identificador (RFC)</th>
                            <th>Validez (Inicio - Fin)</th>
                            <th>Estado del Nodo</th>
                            <th style="text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($nodes as $node)
                            @php
                                // Verificamos si la fecha actual ya superó la fecha de caducidad
                                $isExpired = $node->end_date && clone $node->end_date->startOfDay() < now()->startOfDay();

                                // Preparamos los datos del nodo para el modal
                                $nodeData = json_encode([
                                    'id'         => $node->id,
                                    'entity_n'   => $node->e_name,
                                    'gov_id'     => $node->g_id,
                                    'start_date' => $node->start_date ? $node->start_date->format('Y-m-d') : '',
                                    'end_date'   => $node->end_date ? $node->end_date->format('Y-m-d') : ''
                                ]);
                            @endphp
                            <tr>
                                <td><strong>{{ $node->id }}</strong></td>
                                <td>{{ $node->e_name }}</td>
                                <td><span style="font-family: monospace; font-weight: 600; color:var(--teal-dark);">{{ $node->g_id }}</span></td>
                                <td>
                                    @if($node->start_date && $node->end_date)
                                        {{ $node->start_date->format('d/m/Y') }} <i class="bx bx-right-arrow-alt" style="color:#94a3b8;"></i> {{ $node->end_date->format('d/m/Y') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if($node->is_live)
                                        @if($isExpired)
                                            <span class="badge-expired" title="El certificado ha caducado. Los servicios dependientes fallarán.">
                                                <i class="bx bx-error-circle"></i> Requiere Renovación
                                            </span>
                                        @else
                                            <span class="badge-live"><i class="bx bx-check-circle"></i> Activo (En Uso)</span>
                                        @endif
                                    @else
                                        <span class="badge-history"><i class="bx bx-archive"></i> Histórico</span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    @if($node->is_live && $isExpired)
                                        <button class="btn btn-alert" style="padding: 0.4rem 1rem; font-size: 0.8rem;" onclick="openNodeModal({{ $nodeData }})">
                                            <i class="bx bx-refresh"></i> Renovar
                                        </button>
                                    @elseif($node->is_live)
                                        <button class="btn btn-primary" style="padding: 0.4rem 1rem; font-size: 0.8rem;" onclick="openNodeModal({{ $nodeData }})" title="Actualizar datos actuales">
                                            <i class="bx bx-edit"></i> Actualizar
                                        </button>
                                    @else
                                        <button class="btn btn-ghost" style="padding: 0.4rem 1rem; font-size: 0.8rem;" onclick="openNodeModal({{ $nodeData }})" title="Ver o editar histórico">
                                            <i class="bx bx-edit-alt"></i> Editar Histórico
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 3rem;">
                                    <div style="background: rgba(20, 184, 166, 0.05); display:inline-flex; padding: 1.5rem; border-radius: 50%; margin-bottom: 1rem;">
                                        <i class="bx bx-shield-x" style="font-size: 3rem; color: var(--teal-medium);"></i>
                                    </div>
                                    <h4 style="margin: 0; color: var(--slate-dark);">Sin Nodos Configurados</h4>
                                    <p style="margin: 0.5rem 0 0 0; color: #64748b; max-width: 400px; margin-left: auto; margin-right:auto;">El sistema requiere la vinculación de un nodo para habilitar la consulta y automatización de archivos CFDI.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- ── MODAL FLOTANTE: FORMULARIO DE REGISTRO / ACTUALIZACIÓN ── --}}
    <div class="modal-overlay" id="node-modal">
        <div class="modal-box">

            <div class="modal-header">
                <h3><i class="bx bx-lock-alt" style="color:var(--teal-medium);"></i> <span id="modal-title-text">Configuración de Nodo Criptográfico</span></h3>
                <button class="btn-close" onclick="closeNodeModal()"><i class="bx bx-x"></i></button>
            </div>

            <form action="{{ route('expense-claims.node.store') }}" method="POST" enctype="multipart/form-data" id="sat-vault-form">
                @csrf
                <input type="hidden" name="node_id" id="node_id" value="">

                <div class="modal-body">

                    {{-- ── ALERTA DE SEGURIDAD CAMUFLADA Y REESCRITA ── --}}
                    <div style="grid-column: 1 / -1; background: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid #f59e0b; padding: 1rem; border-radius: 0.5rem; display: flex; gap: 1rem; align-items: flex-start;">
                        <i class="bx bx-info-circle" style="color: #d97706; font-size: 1.2rem; margin-top:0.1rem;"></i>
                        <p style="margin: 0; font-size: 0.8rem; color: #92400e; line-height:1.4;">
                            <strong>Garantía de Aislamiento:</strong> El sistema procesa la información mediante algoritmos de cifrado avanzado de extremo a extremo. Los archivos se depositan en una bóveda digital aislada y completamente inaccesible desde la red pública. Al autorizar un nuevo nodo, la iteración anterior quedará bloqueada en estado Histórico para auditoría interna. Si actualiza un nodo, deje en blanco los archivos/contraseña para conservar los actuales.
                        </p>
                    </div>

                    {{-- Fila 1: Razón Social --}}
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Identidad de la Entidad (Razón Social)</label>
                        <div class="input-group">
                            <i class="bx bx-buildings field-icon"></i>
                            <input type="text" name="entity_n" id="entity_n" class="input-text" placeholder="Ej. Vinco Energy Services, S.A. de C.V." required autocomplete="off">
                        </div>
                    </div>

                    {{-- Fila 2: RFC y Contraseña --}}
                    <div class="form-group">
                        <label>ID Gubernamental (RFC)</label>
                        <div class="input-group">
                            <i class="bx bx-id-card field-icon"></i>
                            <input type="text" name="gov_id" id="gov_id" class="input-text" placeholder="VES1607057K7" maxlength="13" style="text-transform: uppercase;" required autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Token de Seguridad (Passphrase)</label>
                        <div class="input-group">
                            <i class="bx bx-key field-icon"></i>
                            <input type="password" name="s_token" id="passphrase-input" class="input-text" placeholder="••••••••••••" autocomplete="new-password">
                            <i class="bx bx-hide" id="toggle-password" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8; cursor: pointer; font-size: 1.2rem; z-index: 3;"></i>
                        </div>
                        <small id="token-hint" style="color:#94a3b8; font-size:0.75rem; margin-top:0.2rem; display:none;">Déjalo en blanco para mantener el actual.</small>
                    </div>

                    {{-- Fila 3: Fechas de Vigencia --}}
                    <div class="form-group">
                        <label>Fecha de Emisión (Inicio)</label>
                        <div class="input-group">
                            <i class="bx bx-calendar field-icon"></i>
                            <input type="text" name="start_d" id="start_d" class="input-text date-picker" placeholder="Seleccione fecha..." required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Fecha de Caducidad (Fin)</label>
                        <div class="input-group">
                            <i class="bx bx-calendar-x field-icon"></i>
                            <input type="text" name="end_d" id="end_d" class="input-text date-picker" placeholder="Seleccione fecha..." required>
                        </div>
                    </div>

                    {{-- Fila 4: Zona de Archivos --}}
                    <div class="form-group">
                        <label>Documento de Certificación (.CER)</label>
                        <div class="file-upload-wrapper" id="cer-wrapper">
                            <i class="bx bx-badge-check" id="cer-icon"></i>
                            <p id="cer-text">Cargar archivo .cer</p>
                            <small id="cer-subtext">Certificado público de la entidad</small>
                            <input type="file" name="doc_c" id="cer-input" accept=".cer">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Binario Privado (.KEY)</label>
                        <div class="file-upload-wrapper" id="key-wrapper">
                            <i class="bx bx-file" id="key-icon"></i>
                            <p id="key-text">Cargar archivo .key</p>
                            <small id="key-subtext">Llave privada de encriptación</small>
                            <input type="file" name="doc_k" id="key-input" accept=".key">
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" onclick="closeNodeModal()">Cancelar Operación</button>
                    <button type="submit" class="btn btn-primary" id="btn-submit-vault">
                        <i class="bx bx-save"></i> Procesar y Cifrar Nodo
                    </button>
                </div>
            </form>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>

    <script>
        /* ── LÓGICA DEL MODAL FLOTANTE ── */
        const modal = document.getElementById('node-modal');
        const formVault = document.getElementById('sat-vault-form');

        let fpStart, fpEnd;

        function openNodeModal(node = null) {
            formVault.reset();

            // Reset de inputs de archivo
            document.getElementById('cer-wrapper').classList.remove('has-file');
            document.getElementById('cer-text').textContent = 'Cargar archivo .cer';
            document.getElementById('cer-text').style.color = 'var(--slate-mid)';
            document.getElementById('cer-subtext').textContent = 'Certificado público de la entidad';

            document.getElementById('key-wrapper').classList.remove('has-file');
            document.getElementById('key-text').textContent = 'Cargar archivo .key';
            document.getElementById('key-text').style.color = 'var(--slate-mid)';
            document.getElementById('key-subtext').textContent = 'Llave privada de encriptación';

            if (node) {
                // Modo Edición / Actualización
                document.getElementById('modal-title-text').textContent = 'Actualizar Nodo Criptográfico';
                document.getElementById('node_id').value = node.id;
                document.getElementById('entity_n').value = node.entity_n;
                document.getElementById('gov_id').value = node.gov_id;

                // Actualizar Flatpickr instances
                fpStart.setDate(node.start_date);
                fpEnd.setDate(node.end_date);

                // Inputs de archivos y password ya no son estrictamente required si es update
                document.getElementById('passphrase-input').removeAttribute('required');
                document.getElementById('cer-input').removeAttribute('required');
                document.getElementById('key-input').removeAttribute('required');

                document.getElementById('token-hint').style.display = 'block';
                document.getElementById('cer-subtext').textContent = 'Opcional si no cambia el certificado';
                document.getElementById('key-subtext').textContent = 'Opcional si no cambia la llave';
            } else {
                // Modo Creación
                document.getElementById('modal-title-text').textContent = 'Configuración de Nodo Criptográfico';
                document.getElementById('node_id').value = '';

                fpStart.clear();
                fpEnd.clear();

                document.getElementById('passphrase-input').setAttribute('required', 'required');
                document.getElementById('cer-input').setAttribute('required', 'required');
                document.getElementById('key-input').setAttribute('required', 'required');

                document.getElementById('token-hint').style.display = 'none';
            }

            modal.classList.add('active');
        }

        function closeNodeModal() {
            modal.classList.remove('active');
        }

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeNodeModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {

            /* ── INICIALIZAR FLATPICKR (FECHAS) ── */
            fpStart = flatpickr("#start_d", {
                locale: "es",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                allowInput: true
            });

            fpEnd = flatpickr("#end_d", {
                locale: "es",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                allowInput: true
            });

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
            function setupFileInput(inputId, wrapperId, iconId, textId, subtextId, defaultText) {
                const input = document.getElementById(inputId);
                const wrapper = document.getElementById(wrapperId);
                const text = document.getElementById(textId);
                const subtext = document.getElementById(subtextId);

                input.addEventListener('change', function(e) {
                    if (this.files && this.files.length > 0) {
                        const file = this.files[0];
                        wrapper.classList.add('has-file');
                        text.textContent = 'Archivo Listo';
                        text.style.color = 'var(--teal-dark)';
                        subtext.textContent = file.name;
                    } else {
                        wrapper.classList.remove('has-file');
                        text.textContent = defaultText;
                        text.style.color = 'var(--slate-mid)';

                        // Si estamos en edición mostramos el hint adecuado
                        if (document.getElementById('node_id').value) {
                             subtext.textContent = 'Opcional si no cambia el archivo';
                        } else {
                             subtext.textContent = inputId === 'cer-input' ? 'Certificado público de la entidad' : 'Llave privada de encriptación';
                        }
                    }
                });
            }

            setupFileInput('cer-input', 'cer-wrapper', 'cer-icon', 'cer-text', 'cer-subtext', 'Cargar archivo .cer');
            setupFileInput('key-input', 'key-wrapper', 'key-icon', 'key-text', 'key-subtext', 'Cargar archivo .key');

            /* ── ENVÍO VÍA AJAX (JAVASCRIPT) FETCH API ── */
            const btnSubmit = document.getElementById('btn-submit-vault');

            formVault.addEventListener('submit', function(e) {
                e.preventDefault();

                const originalBtnContent = btnSubmit.innerHTML;
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="spinner"></span> Procesando...';

                const formData = new FormData(formVault);

                fetch(formVault.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeNodeModal();
                        Swal.fire({
                            title: '<span style="font-family:\'Poppins\', sans-serif;">Actualización Completa</span>',
                            html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">${data.message}</span>`,
                            icon: 'success',
                            confirmButtonColor: 'var(--teal-dark)',
                            confirmButtonText: '<span style="font-family:\'Poppins\', sans-serif; font-weight:600;">Entendido</span>'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        // Manejo de errores de validación de Laravel
                        let errorText = data.message;
                        if(data.errors) {
                            errorText = Object.values(data.errors).map(err => err.join(', ')).join('<br>');
                        }

                        Swal.fire({
                            title: 'Error de Verificación',
                            html: errorText,
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
                        title: 'Fallo de Red',
                        text: 'El servidor rechazó la conexión o ocurrió un error inesperado al procesar los archivos de seguridad.',
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
