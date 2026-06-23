{{-- ════════════════════════════════════════════════════════════════════════════
     VISTA BLADE: BITÁCORA DE SINCRONIZACIÓN SAT (sat-requests-log.blade.php)
     ════════════════════════════════════════════════════════════════════════════ --}}
@extends('modules.administration.expense-claims.index')

@section('content')
    <div class="reimbursements-container">

        {{-- ── ENCABEZADO PRINCIPAL DE LA VISTA ── --}}
        <header class="view-header">
            <div>
                <h2 class="view-title">Bitácora de <strong>Sincronización SAT</strong></h2>
                <p class="view-subtitle">
                    <i class="bx bx-cloud-download"></i>
                    Monitorea el estatus de las descargas masivas automáticas y tickets solicitados al SAT.
                </p>
            </div>

            {{-- Formulario para forzar la sincronización manual --}}
            <form id="form-force-sync" action="{{ route('expense-claims.sat-sync.force') }}" method="POST">
                @csrf

                {{-- La variable $hasRequestToday ahora viene limpia y directa desde el Controlador --}}
                @if ($hasRequestToday)
                    <button type="button" class="btn btn-secondary" disabled
                        title="Ya existe una petición registrada hoy.">
                        <i class="bx bx-check-shield"></i> Sincronización de Hoy Registrada
                    </button>
                @else
                    <button type="button" class="btn btn-primary" onclick="confirmSync()"
                        aria-label="Forzar petición al SAT">
                        <i class="bx bx-refresh"></i> Forzar Sincronización Manual
                    </button>
                @endif
            </form>
        </header>

        {{-- ── TABLA MAESTRA: HISTORIAL DE TICKETS ── --}}
        <div class="card history-card">
            <div class="card-header">
                <span class="card-title">
                    <i class="bx bx-list-ul"></i> Registro de Peticiones (CRON / Manual)
                </span>
            </div>

            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="th-w-42"># ID</th>
                            <th>Fecha de Consulta</th>
                            <th>Ticket ID (Asignado por SAT)</th>
                            <th>Hora de Creación</th>
                            <th>Última Actualización</th>
                            <th>Estatus de Paquete</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr>
                                <td class="row-index">{{ $req->id }}</td>
                                <td>
                                    <span class="row-date">
                                        <i class="bx bx-calendar"></i>
                                        {{ \Carbon\Carbon::parse($req->request_date)->format('d/m/Y') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="row-folio user-folio" style="font-family: monospace;">
                                        {{ $req->ticket_id ?? 'Esperando asignación...' }}
                                    </span>
                                </td>
                                <td><span class="row-motive">{{ $req->created_at->format('d/m/Y H:i:s') }}</span></td>
                                <td><span class="row-motive">{{ $req->updated_at->format('d/m/Y H:i:s') }}</span></td>
                                <td>
                                    @if ($req->status === 'completed')
                                        <span class="status-badge badge-ok">
                                            <i class="bx bx-check-circle"></i> Completado
                                        </span>
                                    @elseif($req->status === 'pending')
                                        <span class="status-badge badge-wait">
                                            <i class="bx bx-loader-alt bx-spin"></i> En Proceso
                                        </span>
                                    @else
                                        <span class="status-badge badge-fail">
                                            <i class="bx bx-x-circle"></i> Fallido
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center" style="padding: 3rem;">
                                    <div class="empty-state"
                                        style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                        <i class="bx bx-server empty-icon" style="font-size: 3rem; color: #cbd5e1;"></i>
                                        <p class="empty-title" style="margin-top: 1rem; color: #475569; font-weight: 600;">
                                            Sin peticiones registradas</p>
                                        <p class="empty-desc" style="color: #94a3b8; font-size: 0.875rem;">Aún no se ha
                                            ejecutado ninguna descarga masiva hacia los servidores del SAT.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ── PAGINACIÓN ── --}}
            @if ($requests->hasPages())
                <div class="table-footer" style="display: flex; justify-content: flex-end; padding: 1rem;">
                    {{ $requests->links() }}
                </div>
            @else
                <div class="table-footer">
                    <span class="table-count-label">Mostrando {{ $requests->count() }} registro(s)</span>
                </div>
            @endif
        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // ── MANEJADOR DE ALERTAS DE SESIÓN (SweetAlert2) ──
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: `<span style="font-family:'Poppins', sans-serif;">¡Operación Exitosa!</span>`,
                    html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">{{ session('success') }}</span>`,
                    confirmButtonColor: 'var(--teal-dark)'
                });
            @endif

            @if (session('warning'))
                Swal.fire({
                    icon: 'warning',
                    title: `<span style="font-family:'Poppins', sans-serif;">Atención Requerida</span>`,
                    html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">{{ session('warning') }}</span>`,
                    confirmButtonColor: '#f59e0b'
                });
            @endif

            @if (session('info'))
                Swal.fire({
                    icon: 'info',
                    title: `<span style="font-family:'Poppins', sans-serif;">Información</span>`,
                    html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">{{ session('info') }}</span>`,
                    confirmButtonColor: '#3b82f6'
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: `<span style="font-family:'Poppins', sans-serif;">Error en la Operación</span>`,
                    html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">{{ session('error') }}</span>`,
                    confirmButtonColor: '#ef4444'
                });
            @endif
        });

        // ── MANEJADOR DEL BOTÓN DE SINCRONIZACIÓN ──
        function confirmSync() {
            Swal.fire({
                title: `<span style="font-family:'Poppins', sans-serif;">¿Forzar Descarga?</span>`,
                html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">Esto encolará una petición inmediata al SAT para descargar los XML del día de hoy.</span>`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: 'var(--teal-dark)',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: `<span style="font-family:'Poppins', sans-serif; font-weight:600;">Sí, sincronizar</span>`,
                cancelButtonText: `<span style="font-family:'Poppins', sans-serif;">Cancelar</span>`
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Conectando...',
                        text: 'Enviando petición a los servidores del SAT.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    document.getElementById('form-force-sync').submit();
                }
            });
        }
    </script>
@endpush
