<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Credenciales QR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.12/sweetalert2.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('assets/css/systems/user-management/index.css') }}?v=4.2" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="header-inner">
            <div class="header-brand">
                <div class="brand-logo">E</div>
                <div class="brand-text">
                    <span class="brand-label">Sistema</span>
                    <h1 class="brand-title">Credenciales <span class="accent">&amp;</span> QR</h1>
                </div>
            </div>
            <nav class="header-nav">
                <a href="{{ route('systems.users.index') }}" class="btn-export" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-arrow-left"></i> <span>Volver a Usuarios</span>
                </a>
                @include('components.layouts._user-profile')
            </nav>
        </div>
    </header>

    <main class="main-wrapper" id="mainContent" tabindex="-1">
        <section class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Directorio de Credenciales Digitales</h2>
                    <p>Genera y administra los códigos QR institucionales de los colaboradores.</p>
                </div>
            </div>
            <div class="panel-controls">
                <div class="search-wrap">
                    <i class="fas fa-search search-ico"></i>
                    <input type="text" class="search-input" id="qrSearchInput" placeholder="Buscar por nombre o matrícula…">
                </div>
            </div>

            <div class="table-scroll" tabindex="0" role="region">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th scope="col">Colaborador</th>
                            <th scope="col">Puesto</th>
                            <th scope="col">Estado QR</th>
                            <th scope="col" class="th-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="qrTableBody">
                        <tr><td colspan="4" style="text-align:center; padding: 20px;">Cargando empleados...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    {{-- MODAL GESTIÓN QR --}}
    <div class="modal-backdrop" id="qrModalBackdrop" style="display:none;">
        <div class="modal-box" role="dialog" aria-modal="true" style="max-width: 420px; text-align: center;">
            <div class="modal-top">
                <div class="modal-heading">
                    <div class="modal-icon-wrap"><i class="fas fa-qrcode"></i></div>
                    <div>
                        <h2 class="modal-title">Tarjeta Digital QR</h2>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeQrModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <h3 id="modalEmpName" style="font-size: 18px; margin-bottom: 4px; color: var(--text-main);"></h3>
                <p id="modalEmpMeta" style="color: var(--text-muted); font-size: 13px; margin-bottom: 16px;"></p>
                
                <div id="qrCanvasContainer" style="display: inline-flex; padding: 16px; background: #fff; border: 2px dashed #cbd5e1; border-radius: 16px; margin-bottom: 16px;"></div>

                <div style="margin-bottom: 16px;">
                    <input type="text" id="modalPublicUrl" readonly style="width: 100%; padding: 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 8px; text-align: center; background: #f8fafc;" />
                </div>

                <div style="display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;">
                    <button type="button" class="btn-save" id="btnGenQr" style="padding: 8px 14px; font-size: 13px;"><i class="fas fa-rotate"></i> Regenerar</button>
                    <button type="button" class="btn-export" id="btnDownloadQr" style="padding: 8px 14px; font-size: 13px; display:none;"><i class="fas fa-download"></i> Descargar</button>
                    <button type="button" class="btn-cancel" id="btnToggleQr" style="padding: 8px 14px; font-size: 13px; display:none;"><i class="fas fa-power-off"></i> Pausar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.12/sweetalert2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
    (function() {
        'use strict';
        let employees = [];
        let currentEmpId = null;

        async function loadEmployees() {
            try {
                const res = await fetch('/systems/user-management/employee-cards', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                employees = data.employees || [];
                renderTable(employees);
            } catch (err) {
                console.error(err);
            }
        }

        function renderTable(data) {
            const tbody = document.getElementById('qrTableBody');
            tbody.innerHTML = '';

            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; padding: 20px;">No hay empleados registrados.</td></tr>`;
                return;
            }

            data.forEach(emp => {
                const tr = document.createElement('tr');
                const isEnabled = Boolean(emp.qr_token && emp.qr_enabled && emp.employment_status === 'active');

                tr.innerHTML = `
                    <td><strong>${emp.full_name}</strong><br><span style="color:var(--text-muted); font-size:12px;">${emp.employee_number}</span></td>
                    <td>${emp.position || '—'}</td>
                    <td><span class="badge" style="padding: 4px 10px; border-radius: 6px; font-size: 11px; background: ${isEnabled ? '#dcfce7; color:#166534;' : '#fee2e2; color:#991b1b;'}">${isEnabled ? 'Activo' : 'Inactivo / Sin QR'}</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn-export btn-open-qr" data-id="${emp.id}" style="padding: 6px 12px; font-size: 12px;">
                            <i class="fas fa-qrcode"></i> Gestionar QR
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            document.querySelectorAll('.btn-open-qr').forEach(btn => {
                btn.addEventListener('click', () => openQrModal(Number(btn.dataset.id)));
            });
        }

        window.openQrModal = function(id) {
            const emp = employees.find(e => e.id === id);
            if (!emp) return;
            currentEmpId = id;

            document.getElementById('modalEmpName').textContent = emp.full_name;
            document.getElementById('modalEmpMeta').textContent = `${emp.employee_number} • ${emp.position || 'Sin puesto'}`;
            
            renderQrBox(emp);
            document.getElementById('qrModalBackdrop').style.display = 'flex';
        }

        window.closeQrModal = function() {
            document.getElementById('qrModalBackdrop').style.display = 'none';
            currentEmpId = null;
        }

        function renderQrBox(emp) {
            const container = document.getElementById('qrCanvasContainer');
            const urlInput = document.getElementById('modalPublicUrl');
            const btnToggle = document.getElementById('btnToggleQr');
            const btnDownload = document.getElementById('btnDownloadQr');
            
            container.innerHTML = '';

            if (!emp.qr_token) {
                container.innerHTML = '<span style="color:#64748b; font-size:13px; padding:20px;">Sin código QR institucional generado.</span>';
                urlInput.value = 'Haga clic en Regenerar para crear el código';
                btnToggle.style.display = 'none';
                btnDownload.style.display = 'none';
                return;
            }

            
            const url = `${window.location.origin}/tarjeta/${emp.qr_token}`;
            urlInput.value = url;
            btnToggle.style.display = 'inline-flex';
            btnDownload.style.display = 'inline-flex';

            new QRCode(container, {
                text: url,
                width: 170,
                height: 170,
                colorDark: '#0f172a',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });

            btnToggle.innerHTML = emp.qr_enabled ? '<i class="fas fa-ban"></i> Pausar' : '<i class="fas fa-check"></i> Activar';
        }

        async function generateQr() {
            const res = await fetch(`/systems/user-management/employees/${currentEmpId}/qr/generate`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
            });
            const data = await res.json();
            const emp = employees.find(e => e.id === currentEmpId);
            if (emp) {
                emp.qr_token = data.qr_token;
                emp.qr_enabled = data.qr_enabled;
                renderQrBox(emp);
                renderTable(employees);
            }
        }

        async function toggleQr() {
            const res = await fetch(`/systems/user-management/employees/${currentEmpId}/qr/toggle`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
            });
            const data = await res.json();
            const emp = employees.find(e => e.id === currentEmpId);
            if (emp) {
                emp.qr_enabled = data.qr_enabled;
                renderQrBox(emp);
                renderTable(employees);
            }
        }

        function downloadPng() {
            const img = document.getElementById('qrCanvasContainer').querySelector('img') || document.getElementById('qrCanvasContainer').querySelector('canvas');
            const emp = employees.find(e => e.id === currentEmpId);
            if (!img || !emp) return;

            const a = document.createElement('a');
            a.href = img.src || img.toDataURL('image/png');
            a.download = `QR_Institucional_${emp.employee_number}.png`;
            a.click();
        }

        document.getElementById('qrSearchInput').addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();
            const filtered = employees.filter(emp => emp.full_name.toLowerCase().includes(term) || emp.employee_number.toLowerCase().includes(term));
            renderTable(filtered);
        });

        document.getElementById('btnGenQr').onclick = generateQr;
        document.getElementById('btnToggleQr').onclick = toggleQr;
        document.getElementById('btnDownloadQr').onclick = downloadPng;

        document.addEventListener('DOMContentLoaded', loadEmployees);
    })();
    </script>
</body>
</html>