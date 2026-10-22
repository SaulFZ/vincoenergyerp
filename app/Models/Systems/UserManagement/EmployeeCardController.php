<?php

namespace App\Http\Controllers\Systems\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\EmployeeCard;
use Illuminate\Support\Facades\Storage;

class EmployeeCardController extends Controller
{
    public function show(string $token)
    {
        $card = EmployeeCard::with('employee')
            ->where('token', $token)
            ->accessible()
            ->firstOrFail();

        return view('modules.public.employee-card', [
            'data'  => $card->visibleData(),
            'token' => $token,
        ]);
    }

    public function photo(string $token)
    {
        $card = EmployeeCard::with('employee')
            ->where('token', $token)
            ->accessible()
            ->firstOrFail();

        if (! in_array('photo', $card->visible_fields ?? [], true)) {
            abort(404);
        }

        $path = $card->employee->photo;

        if (! $path || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($path), [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function vcard(string $token)
    {
        $card = EmployeeCard::with('employee')
            ->where('token', $token)
            ->accessible()
            ->firstOrFail();

        $data = $card->visibleData();

        $lines = [
            'BEGIN:VCARD',
            'VERSION:3.0',
            'FN:' . ($data['full_name'] ?? 'Empleado'),
            'ORG:Vinco Energy',
        ];

        if (! empty($data['position'])) {
            $lines[] = 'TITLE:' . $data['position'];
        }
        if (! empty($data['department'])) {
            $lines[] = 'NOTE:Departamento: ' . $data['department'];
        }
        if (! empty($data['phone_work'])) {
            $lines[] = 'TEL;TYPE=WORK:' . $data['phone_work'];
        }
        if (! empty($data['email'])) {
            $lines[] = 'EMAIL:' . $data['email'];
        }

        $lines[] = 'END:VCARD';

        return response(implode("\r\n", $lines), 200, [
            'Content-Type'        => 'text/vcard; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $card->token . '.vcf"',
        ]);
    }
}