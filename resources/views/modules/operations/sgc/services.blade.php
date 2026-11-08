@extends('modules.operations.sgc.index')

@push('styles')
    <link href="{{ asset('assets/css/operations/sgc/services.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="tab-shell">

        {{-- Barra de pestañas --}}
        <div class="tab-shell-nav no-print">
            <span class="tab-title">Vistas:</span>

            <a href="{{ route('operations.sgc.services', ['view' => 'list']) }}"
                class="tab-btn tab-link {{ $view === 'list' ? 'active' : '' }}">Lista de Servicios</a>

            <a href="{{ route('operations.sgc.services', ['view' => 'staff']) }}"
                class="tab-btn tab-link {{ $view === 'staff' ? 'active' : '' }}">Por Personal</a>


        </div>

        {{-- Cuerpo dinámico --}}
        <div class="tab-body">
            @if ($view === 'staff')
                @include('modules.operations.sgc.tabs.serv.staff')
            @else
                @include('modules.operations.sgc.tabs.serv.list')
            @endif
        </div>

    </div>
    <!-- modal fullscreen para crear, ver o editar un servicio -->
    <dialog id="service-modal" class="service-modal" aria-label="Servicio">
        <div class="service-modal-bar no-print">
            <span>Orden de servicio</span>
            <button type="button" class="btn btn-outline btn-sm" data-service-close aria-label="Cerrar modal">
                <i class="fas fa-times" aria-hidden="true"></i> Cerrar
            </button>
        </div>
        <div id="service-modal-content" class="service-modal-content" aria-live="polite"></div>
    </dialog>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/operations/sgc/services.js') }}"></script>
@endpush
