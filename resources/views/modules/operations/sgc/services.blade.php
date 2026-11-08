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

            <a href="{{ route('operations.sgc.services', ['view' => 'new_service']) }}"
                class="tab-btn tab-link {{ $view === 'new_service' ? 'active' : '' }}">Nuevo Servicio</a>
        </div>

        {{-- Cuerpo dinámico --}}
        <div class="tab-body">
            @if ($view === 'new_service')
                @include('modules.operations.sgc.tabs.serv.new_service')
            @elseif ($view === 'staff')
                @include('modules.operations.sgc.tabs.serv.staff')
            @else
                @include('modules.operations.sgc.tabs.serv.list')
            @endif
        </div>

    </div>
@endsection
