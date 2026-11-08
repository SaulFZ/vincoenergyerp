@extends('modules.operations.sgc.index')

@push('styles')
    <link href="{{ asset('assets/css/operations/sgc/dashboard.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="tab-shell">

        <!-- Barra de pestañas -->
        <div class="tab-shell-nav no-print">
            <span class="tab-title">Dashboard:</span>
            <!-- NOTA: Agregamos la clase 'tab-link' para que el JS sepa que esto carga por AJAX -->
            <a href="{{ route('operations.sgc.index', ['view' => 'executive']) }}"
               class="tab-btn tab-link {{ $view === 'executive' ? 'active' : '' }}">General</a>

            <a href="{{ route('operations.sgc.index', ['view' => 'npt']) }}"
               class="tab-btn tab-link {{ $view === 'npt' ? 'active' : '' }}">NPT</a>

            <a href="{{ route('operations.sgc.index', ['view' => 'compliance']) }}"
               class="tab-btn tab-link {{ $view === 'compliance' ? 'active' : '' }}">Cumplimiento SGC</a>

            <a href="{{ route('operations.sgc.index', ['view' => 'crew']) }}"
               class="tab-btn tab-link {{ $view === 'crew' ? 'active' : '' }}">Cuadrilla + satisfacción</a>

            <a href="{{ route('operations.sgc.index', ['view' => 'shots']) }}"
               class="tab-btn tab-link {{ $view === 'shots' ? 'active' : '' }}">Disparos / MCR</a>
        </div>

        <!-- Cuerpo dinámico -->
        <div class="tab-body">
            @if ($view === 'npt')
                @include('modules.operations.sgc.tabs.dash.npt')
            @elseif ($view === 'compliance')
                @include('modules.operations.sgc.tabs.dash.compliance')
            @elseif ($view === 'crew')
                @include('modules.operations.sgc.tabs.dash.crew')
            @elseif ($view === 'shots')
                @include('modules.operations.sgc.tabs.dash.shots')
            @else
                @include('modules.operations.sgc.tabs.dash.executive')
            @endif
        </div>

    </div>
@endsection
