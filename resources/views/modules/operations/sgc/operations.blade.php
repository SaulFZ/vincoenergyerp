@extends('modules.operations.sgc.index')

@push('styles')
    <link href="{{ asset('assets/css/operations/sgc/services.css') }}" rel="stylesheet">

    <link href="{{ asset('assets/css/operations/sgc/operations.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="tab-shell">

        {{-- Barra de pestañas --}}
        <div class="tab-shell-nav no-print">
            <span class="tab-title">Vistas:</span>

            <a href="{{ route('operations.sgc.operations', ['view' => 'list']) }}"
                class="tab-btn tab-link {{ $view === 'list' ? 'active' : '' }}">
                Flujo de operaciones
            </a>
        </div>

        {{-- Contenido que reemplaza sgc-tabs.js --}}
        <div class="tab-body">
            @include('modules.operations.sgc.tabs.oper.list')
        </div>

    </div>
@endsection
