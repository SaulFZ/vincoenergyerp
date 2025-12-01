<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Vacaciones</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        h1 {
            font-size: 18px; /* Aumentado un poco para mejor visibilidad */
            text-align: center;
            margin-bottom: 10px;
            color: #283848; /* Color principal */
        }
        h2 {
            font-size: 14px;
            margin: 15px 0 10px 0;
            color: #4a5568;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
        }
        .info-box {
            border: 1px solid #4299e1; /* Borde más distintivo */
            padding: 10px;
            margin-bottom: 15px;
            background-color: #e8f4fd; /* Fondo azul claro */
            border-radius: 6px;
        }
        .summary-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #cbd5e0;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #283848; /* Fondo más oscuro y profesional */
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: right;
            font-size: 8px;
            color: #718096;
            padding: 5px 10px;
            border-top: 1px solid #e2e8f0;
        }

        .page-break {
            page-break-after: always;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-bold {
            font-weight: bold;
        }
        .section-header {
            background-color: #edf2f7;
            padding: 8px;
            margin: 15px 0 10px 0;
            border-left: 4px solid #4299e1;
            font-weight: bold;
            color: #2d3748;
        }
        .total-row {
            background-color: #e6fffa;
            font-weight: bold;
        }
        .department-total {
            background-color: #f0fff4;
            font-weight: bold;
            font-size: 11px;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: capitalize; /* Cambiado a capitalize para mejor lectura */
        }
        .status-approved {
            background-color: #48bb78;
            color: white;
        }
        .status-reviewed {
            background-color: #ed8936;
            color: white;
        }
        .status-under_review {
            background-color: #ecc94b;
            color: #744210;
        }
        .status-rejected {
            background-color: #f56565;
            color: white;
        }
    </style>
</head>
<body>
    @php
        // Helper para traducir el estatus
        function translateStatus($status) {
            switch (strtolower(str_replace(' ', '_', $status))) {
                case 'approved': return 'Aprobado';
                case 'reviewed': return 'Revisado';
                case 'under_review': return 'Bajo Revisión';
                case 'rejected': return 'Rechazado';
                default: return $status;
            }
        }
        // Helper para obtener el nombre del empleado a partir del ID
        function getEmployeeNameFromId($employeeId, $allEmployees) {
            return $allEmployees[$employeeId]['full_name'] ?? 'Empleado Desconocido';
        }

        $allDepartments = $reportData ? collect($reportData)->pluck('area')->unique()->filter()->sort()->values()->toArray() : [];

        $filterDepartments = $filters['departments'] ?? [];
        $filterEmployees = $filters['employees'] ?? [];
        $filterStatus = $filters['status_filter'] ?? [];
        $allEmp = $allEmployees ?? [];
    @endphp

    <h1>{{ $title }}</h1>

    <div class="info-box">
        <div class="text-bold" style="margin-bottom: 8px; font-size: 11px; color: #283848;">Filtros Aplicados:</div>
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; padding: 2px; width: 30%;" class="text-bold">Tipo de Reporte:</td>
                <td style="border: none; padding: 2px;">{{ $reportType === 'AVAILABLE' ? 'Días Disponibles' : 'Días Tomados' }}</td>
                <td style="border: none; padding: 2px; width: 30%;" class="text-bold">Total Registros:</td>
                <td style="border: none; padding: 2px;">{{ count($reportData) }}</td>
            </tr>
            @if(count($allDepartments) > 0)
            <tr>
                <td style="border: none; padding: 2px;" class="text-bold">Departamentos:</td>
                <td style="border: none; padding: 2px;" colspan="3">
                    @if(count($filterDepartments) === 0 || count($filterDepartments) === count($allDepartments))
                        Todos
                    @else
                        {{ implode(', ', $filterDepartments) }}
                    @endif
                </td>
            </tr>
            @endif
            @if($reportType === 'TAKEN' && ($filters['date_from'] || $filters['date_to']))
            <tr>
                <td style="border: none; padding: 2px;" class="text-bold">Período:</td>
                <td style="border: none; padding: 2px;" colspan="3">
                    {{ $filters['date_from'] ? \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') : 'Inicio' }}
                    a
                    {{ $filters['date_to'] ? \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') : 'Fin' }}
                </td>
            </tr>
            @endif
             @if($reportType === 'TAKEN' && count($filterStatus) > 0)
            <tr>
                <td style="border: none; padding: 2px;" class="text-bold">Estatus:</td>
                <td style="border: none; padding: 2px;" colspan="3">
                    {{ implode(', ', array_map('translateStatus', $filterStatus)) }}
                </td>
            </tr>
            @endif
             @if(count($filterEmployees) > 0)
            <tr>
                <td style="border: none; padding: 2px;" class="text-bold">Empleados:</td>
                <td style="border: none; padding: 2px;" colspan="3">
                    @if(count($filterEmployees) === count($allEmp))
                        Todos
                    @else
                        {{ implode(', ', array_map(fn($id) => getEmployeeNameFromId($id, $allEmp), $filterEmployees)) }}
                    @endif
                </td>
            </tr>
            @endif
        </table>
    </div>

    @if(count($reportData) > 0)
        @if($reportType === 'AVAILABLE')
            {{-- RESUMEN POR DEPARTAMENTO PARA DÍAS DISPONIBLES (SIN DÍAS DE DESCANSO) --}}
            <div class="section-header">Resumen por Departamento</div>
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>Departamento</th>
                        <th class="text-center">Total Empleados</th>
                        <th class="text-center">Total Días Vacaciones</th>
                        <th class="text-center">Promedio Días Vacaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $grandTotalEmployees = 0;
                        $grandTotalVacation = 0;
                    @endphp
                    @foreach($summaryByDepartment as $department => $summary)
                    @php
                        // Nota: El Controller ya ha quitado 'total_rest_days',
                        // solo usamos las claves disponibles.
                        $totalEmployees = $summary['total_employees'] ?? 0;
                        $totalVacationDays = $summary['total_vacation_days'] ?? 0;

                        $grandTotalEmployees += $totalEmployees;
                        $grandTotalVacation += $totalVacationDays;
                    @endphp
                    <tr>
                        <td>{{ $department }}</td>
                        <td class="text-center">{{ $totalEmployees }}</td>
                        <td class="text-center">{{ $totalVacationDays }}</td>
                        <td class="text-center">{{ $totalEmployees > 0 ? number_format($totalVacationDays / $totalEmployees, 1) : 0 }}</td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td class="text-bold">TOTAL GENERAL</td>
                        <td class="text-center text-bold">{{ $grandTotalEmployees }}</td>
                        <td class="text-center text-bold">{{ $grandTotalVacation }}</td>
                        <td class="text-center text-bold">{{ $grandTotalEmployees > 0 ? number_format($grandTotalVacation / $grandTotalEmployees, 1) : 0 }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="page-break"></div>
            <h2>Detalle de Días Disponibles por Empleado</h2>

        @else
            {{-- RESUMEN POR DEPARTAMENTO PARA DÍAS TOMADOS (CÓDIGO ANTERIOR OK) --}}
            <div class="section-header">Resumen por Departamento</div>
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>Departamento</th>
                        <th class="text-center">Total Días Tomados</th>
                        <th class="text-center">Aprobados</th>
                        <th class="text-center">Revisados</th>
                        <th class="text-center">Bajo Revisión</th>
                        <th class="text-center">Rechazados</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $grandTotalDays = 0;
                        $grandTotalApproved = 0;
                        $grandTotalReviewed = 0;
                        $grandTotalUnderReview = 0;
                        $grandTotalRejected = 0;
                    @endphp
                    @foreach($summaryByDepartment as $department => $summary)
                    @php
                        $grandTotalDays += $summary['total_days'] ?? 0;
                        $grandTotalApproved += $summary['approved'] ?? 0;
                        $grandTotalReviewed += $summary['reviewed'] ?? 0;
                        $grandTotalUnderReview += $summary['under_review'] ?? 0;
                        $grandTotalRejected += $summary['rejected'] ?? 0;
                    @endphp
                    <tr>
                        <td>{{ $department }}</td>
                        <td class="text-center">{{ $summary['total_days'] ?? 0 }}</td>
                        <td class="text-center">{{ $summary['approved'] ?? 0 }}</td>
                        <td class="text-center">{{ $summary['reviewed'] ?? 0 }}</td>
                        <td class="text-center">{{ $summary['under_review'] ?? 0 }}</td>
                        <td class="text-center">{{ $summary['rejected'] ?? 0 }}</td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td class="text-bold">TOTAL GENERAL</td>
                        <td class="text-center text-bold">{{ $grandTotalDays }}</td>
                        <td class="text-center text-bold">{{ $grandTotalApproved }}</td>
                        <td class="text-center text-bold">{{ $grandTotalReviewed }}</td>
                        <td class="text-center text-bold">{{ $grandTotalUnderReview }}</td>
                        <td class="text-center text-bold">{{ $grandTotalRejected }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="page-break"></div>
            <h2>Detalle de Días Tomados por Empleado</h2>
        @endif

        <table>
            <thead>
                @if ($reportType === 'AVAILABLE')
                    <tr>
                        <th>No. Empleado</th>
                        <th>Nombre</th>
                        <th>Área</th>
                        <th class="text-center">Días Vacaciones Disp.</th>
                        <th class="text-center">Años de Servicio</th>
                    </tr>
                @else
                    <tr>
                        <th>No. Empleado</th>
                        <th>Nombre</th>
                        <th>Área</th>
                        <th class="text-center">Fecha de Toma</th>
                        <th class="text-center">Estatus</th>
                        <th class="text-center">Días Disp. Actual (Ref.)</th>
                    </tr>
                @endif
            </thead>
            <tbody>
                @php
                    $currentDepartment = null;
                @endphp
                @foreach ($reportData as $index => $row)
                    @if($row['area'] !== $currentDepartment)
                        @php $currentDepartment = $row['area']; @endphp
                        <tr class="department-total">
                            <td colspan="{{ $reportType === 'AVAILABLE' ? 5 : 6 }}" class="text-bold">
                                Departamento: {{ $currentDepartment }}
                            </td>
                        </tr>
                    @endif

                    @if ($reportType === 'AVAILABLE')
                        <tr>
                            <td>{{ $row['employee_number'] }}</td>
                            <td>{{ $row['full_name'] }}</td>
                            <td>{{ $row['area'] }}</td>
                            <td class="text-center">{{ $row['vacation_days_available'] }}</td>
                            <td class="text-center">{{ $row['years_of_service'] }}</td>
                        </tr>
                    @else
                        <tr>
                            <td>{{ $row['employee_number'] }}</td>
                            <td>{{ $row['full_name'] }}</td>
                            <td>{{ $row['area'] }}</td>
                            <td class="text-center">{{ $row['date'] }}</td>
                            <td class="text-center">
                                <span class="status-badge status-{{ strtolower(str_replace(' ', '_', $row['status'])) }}">
                                    {{ translateStatus($row['status']) }}
                                </span>
                            </td>
                            <td class="text-center">{{ $row['vacation_days_available'] }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 40px; color: #718096;">
            <p>No hay datos para el periodo y filtros seleccionados.</p>
        </div>
    @endif

    <div class="footer">
        Generado el: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }} | Página <span class="page-number"></span>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $size = 8;
            $font = $fontMetrics->get_font("Arial, sans-serif");
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $x = ($pdf->get_width() - $width) - 30;
            $y = $pdf->get_height() - 20;
            $pdf->page_text(500, $y, $text, $font, $size, array(0.44, 0.50, 0.56));
        }
    </script>

</body>
</html>
