@extends('modules.operations.sgc.index')

@push('styles')
    <link href="{{ asset('assets/css/operations/sgc/services.css') }}" rel="stylesheet">

    <link href="{{ asset('assets/css/operations/sgc/maintenance.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="tab-shell maintenance-shell">

        <div class="tab-shell-nav no-print">
            <span class="tab-title">Vistas:</span>

            <a href="{{ route('operations.sgc.maintenance', ['view' => 'list']) }}"
                class="tab-btn tab-link {{ $view === 'list' ? 'active' : '' }}">
                Control general de mantenimientos
            </a>

            <a href="{{ route('operations.sgc.maintenance', ['view' => 'formats']) }}"
                class="tab-btn tab-link {{ $view === 'formats' ? 'active' : '' }}">
                Formatos
            </a>
        </div>

        <div class="tab-body">
            @if ($view === 'formats')
                @include('modules.operations.sgc.tabs.maint.formats')
            @else
                @include('modules.operations.sgc.tabs.maint.list')
            @endif
        </div>

    </div>
@endsection

@push('scripts')
    {{-- El abuelo ya carga sgc-tabs.js. --}}
    <script src="{{ asset('assets/js/operations/sgc/maintenance.js') }}"></script>

    <script src="{{ asset('assets/js/operations/sgc/maintenance-formats.js') }}?v=2"></script>
@endpush
