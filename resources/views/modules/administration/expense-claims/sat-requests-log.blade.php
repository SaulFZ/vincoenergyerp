{{-- ════════════════════════════════════════════════════════════════════════════
     VISTA BLADE: BITÁCORA DE SINCRONIZACIÓN SAT (VesCore Premium)
     ════════════════════════════════════════════════════════════════════════════ --}}
@extends('modules.administration.expense-claims.index')

@section('title', 'Bitácora de Sincronización SAT | Administración - VesCore')

@push('styles')
<style>
    /* ── BOTÓN DE SINCRONIZACIÓN FORZADA EN CABECERA DE TABLA ── */
    .btn-force-sync {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--primary-dark);
        color: #ffffff;
        border: none;
        padding: 0.5rem 1.2rem;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-force-sync:hover {
        background: var(--primary-mid);
        transform: translateY(-1px);
    }

    .btn-force-sync:disabled {
        background: #cbd5e1;
        cursor: not-allowed;
        color: #475569;
        transform: none;
    }

    /* ── TARJETA Y TABLA ── */
    .sat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-top: 1rem;
    }

    .sat-card-header {
        padding: 1rem 1.5rem;
        background: var(--surface-alt);
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between; /* <-- Separa el título del botón a los extremos */
        align-items: center;
        font-weight: 700;
        color: var(--secondary-dark);
        font-size: 1rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .sat-card-title-wrap {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .sat-table {
        width: 100%;
        border-collapse: collapse;
    }

    .sat-table thead tr {
        background: var(--primary-dark);
        color: #ffffff;
    }

    .sat-table th {
        padding: 1rem;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
        text-align: left;
        white-space: nowrap;
    }

    .sat-table tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.15s;
    }

    .sat-table tbody tr:hover {
        background: var(--surface-alt);
    }

    .sat-table td {
        padding: 1rem;
        font-size: 0.85rem;
        color: var(--secondary-medium);
        vertical-align: middle;
    }

    .sat-ticket-id {
        font-family: 'Courier New', Courier, monospace;
        font-weight: 700;
        color: var(--primary-dark);
        background: #f1f5f9;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
        display: inline-block;
    }

    /* ── BADGES (DISEÑO CUADRADO REDONDEADO 4px) ── */
    .sat-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.3rem 0.7rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 700;
        white-space: nowrap;
        border: 1px solid transparent;
    }

    /* Estatus de la petición */
    .status-completed { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
    .status-failed { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
    .status-pending { background: #fef9c3; color: #854d0e; border-color: #fef08a; }

    /* Tipos de petición (Colores Solicitados) */
    .type-diario { background: #dcfce7; color: #166534; border-color: #bbf7d0; } /* Verde */
    .type-mensual { background: #e0f2fe; color: #075985; border-color: #bae6fd; } /* Azul */
    .type-manual { background: #ffedd5; color: #c2410c; border-color: #fed7aa; } /* Naranja */

    /* ── PAGINACIÓN PERSONALIZADA VESCORE ── */
    .sat-pagination-wrapper {
        padding: 1rem 1.5rem;
        background: #ffffff;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
    }

    .sat-pagination-wrapper nav {
        display: flex;
        align-items: center;
    }

    .sat-pagination-wrapper nav ul.pagination {
        display: flex;
        padding-left: 0;
        list-style: none;
        margin: 0;
        gap: 0.3rem;
    }

    .sat-pagination-wrapper .page-item .page-link {
        position: relative;
        display: block;
        padding: 0.4rem 0.8rem;
        font-size: 0.85rem;
        color: var(--secondary-dark);
        background-color: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        font-weight: 600;
        transition: all 0.2s;
        text-decoration: none;
    }

    .sat-pagination-wrapper .page-item.active .page-link {
        z-index: 3;
        color: #fff;
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
    }

    .sat-pagination-wrapper .page-item.disabled .page-link {
        color: #94a3b8;
        pointer-events: none;
        background-color: #f8fafc;
        border-color: #e2e8f0;
    }

    .sat-pagination-wrapper .page-item:not(.active):not(.disabled) .page-link:hover {
        background-color: #f1f5f9;
        border-color: #94a3b8;
    }

    .sat-pagination-wrapper p.small { display: none; }

</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- ── ALERTAS DEL SISTEMA ── --}}
    @if(session('success'))
        <div style="background: #dcfce7; border-left: 4px solid #16a34a; padding: 1rem; border-radius: 4px; margin-top: 1rem; margin-bottom: 1.5rem; color: #166534; font-weight: 600; display:flex; align-items:center; gap:0.5rem;">
            <i class='bx bx-check-circle' style="font-size: 1.3rem;"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('warning'))
        <div style="background: #fef9c3; border-left: 4px solid #ca8a04; padding: 1rem; border-radius: 4px; margin-top: 1rem; margin-bottom: 1.5rem; color: #854d0e; font-weight: 600; display:flex; align-items:center; gap:0.5rem;">
            <i class='bx bx-error-circle' style="font-size: 1.3rem;"></i> {{ session('warning') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background: #fee2e2; border-left: 4px solid #dc2626; padding: 1rem; border-radius: 4px; margin-top: 1rem; margin-bottom: 1.5rem; color: #991b1b; font-weight: 600; display:flex; align-items:center; gap:0.5rem;">
            <i class='bx bx-x-circle' style="font-size: 1.3rem;"></i> {{ session('error') }}
        </div>
    @endif

    {{-- ── TARJETA Y TABLA ── --}}
    <div class="sat-card">
        <div class="sat-card-header">
            <div class="sat-card-title-wrap">
                <i class='bx bx-list-ul'></i> Registro Histórico de Peticiones
            </div>
            <div>
                {{-- Formulario incrustado en el lado derecho del Header --}}
                <form action="{{ route('expense-claims.sat-sync.force') }}" method="POST" id="forceSyncForm" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn-force-sync" id="btnForceSync" {{ $hasRequestToday ? 'disabled' : '' }}>
                        @if($hasRequestToday)
                            <i class='bx bx-loader-alt bx-spin'></i> Sincronización en proceso...
                        @else
                            <i class='bx bx-refresh'></i> Sincronización Manual
                        @endif
                    </button>
                </form>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="sat-table">
                <thead>
                    <tr>
                        <th># ID</th>
                        <th>Fecha de Consulta</th>
                        <th>Ticket ID (Asignado por SAT)</th>
                        <th>Hora de Creación</th>
                        <th>Última Actualización</th>
                        <th>Origen</th>
                        <th class="text-center">Estatus de Paquete</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        @php
                            // Lógica de Estatus
                            $statusClass = 'status-pending';
                            $statusIcon = 'bx bx-hourglass bx-spin';
                            $statusLabel = 'Pendiente';

                            if($req->status === 'completed') {
                                $statusClass = 'status-completed';
                                $statusIcon = 'bx bx-check-double';
                                $statusLabel = 'Completado';
                            } elseif($req->status === 'failed') {
                                $statusClass = 'status-failed';
                                $statusIcon = 'bx bx-x';
                                $statusLabel = 'Fallido';
                            }

                            // Lógica Limpia para Colores de Origen
                            $rawType = strtolower($req->type ?? 'diario');
                            $typeClass = 'type-diario'; // Verde por defecto
                            $typeLabel = 'Diario';

                            if($rawType === 'mensual') {
                                $typeClass = 'type-mensual'; // Azul
                                $typeLabel = 'Mensual';
                            } elseif($rawType === 'manual') {
                                $typeClass = 'type-manual'; // Naranja
                                $typeLabel = 'Manual';
                            }
                        @endphp
                        <tr>
                            <td style="font-weight: 600; color: var(--primary-dark);">{{ $req->id }}</td>
                            <td><i class='bx bx-calendar' style="color:var(--secondary-lighter);"></i> {{ \Carbon\Carbon::parse($req->request_date)->format('d/m/Y') }}</td>
                            <td>
                                @if($req->ticket_id)
                                    <span class="sat-ticket-id">{{ $req->ticket_id }}</span>
                                @else
                                    @if($req->status === 'failed')
                                        <span style="color: #ef4444; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.3rem; background: #fee2e2; padding: 0.2rem 0.5rem; border-radius: 4px;">
                                            <i class='bx bx-block'></i> Conexión rechazada
                                        </span>
                                    @else
                                        <span style="color: var(--secondary-lighter); font-style: italic; display: inline-flex; align-items: center; gap: 0.3rem;">
                                            <i class='bx bx-loader-alt bx-spin'></i> Esperando asignación...
                                        </span>
                                    @endif
                                @endif
                            </td>
                            <td>{{ $req->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $req->updated_at->format('d/m/Y H:i:s') }}</td>
                            <td>
                                <span class="sat-badge {{ $typeClass }}">{{ $typeLabel }}</span>
                            </td>
                            <td class="text-center">
                                <span class="sat-badge {{ $statusClass }}">
                                    <i class='{{ $statusIcon }}'></i> {{ $statusLabel }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 3rem; color: var(--secondary-lighter);">
                                <i class='bx bx-data' style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                                No existen registros de sincronización en la base de datos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ── PAGINACIÓN PERSONALIZADA (10 registros fijos) ── --}}
        @if($requests->hasPages())
            <div class="sat-pagination-wrapper">
                {{ $requests->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Prevenir dobles envíos en el botón de sincronización manual
    document.getElementById('forceSyncForm').addEventListener('submit', function() {
        const btn = document.getElementById('btnForceSync');
        btn.disabled = true;
        btn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Iniciando petición...";
    });
</script>
@endpush
