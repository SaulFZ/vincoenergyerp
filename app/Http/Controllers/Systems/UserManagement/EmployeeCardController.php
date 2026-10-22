<?php

namespace App\Http\Controllers\Systems\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\EmployeeCard;
use Illuminate\Support\Facades\Storage;

class EmployeeCardController extends Controller
{
    /**
     * Busca una tarjeta accesible (activa, no expirada, empleado existente).
     * Cualquier otro caso responde 404, nunca 500.
     */
    private function findAccessibleCard(string $token): EmployeeCard
    {
        $card = EmployeeCard::with('employee')
            ->where('token', $token)
            ->accessible()
            ->first();

        if (! $card || ! $card->employee) {
            abort(404);
        }

        return $card;
    }

    public function show(string $token)
    {
        $card = $this->findAccessibleCard($token);
        $data = $card->visibleData();

        if (empty($data)) {
            abort(404);
        }

        return view('modules.public.employee-card', [
            'data'  => $data,
            'token' => $token,
        ]);
    }

    public function photo(string $token)
    {
        $card = $this->findAccessibleCard($token);

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
        $card = $this->findAccessibleCard($token);
        $data = $card->visibleData();

        if (empty($data)) {
            abort(404);
        }

        $lines = [
            'BEGIN:VCARD',
            'VERSION:3.0',
            'FN:' . $this->escapeVcard($data['full_name'] ?? 'Empleado'),
            'ORG:Vinco Energy',
        ];

        if (! empty($data['position'])) {
            $lines[] = 'TITLE:' . $this->escapeVcard($data['position']);
        }
        if (! empty($data['department'])) {
            $lines[] = 'NOTE:' . $this->escapeVcard('Departamento: ' . $data['department']);
        }
        if (! empty($data['phone_work'])) {
            $lines[] = 'TEL;TYPE=WORK:' . $this->escapeVcard($data['phone_work']);
        }
        if (! empty($data['email'])) {
            $lines[] = 'EMAIL:' . $this->escapeVcard($data['email']);
        }

        $lines[] = 'END:VCARD';

        return response(implode("\r\n", $lines), 200, [
            'Content-Type'        => 'text/vcard; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $card->token . '.vcf"',
        ]);
    }

    /**
     * Escapa caracteres especiales de vCard (, ; \ y saltos de línea).
     */
    private function escapeVcard(string $value): string
    {
        return str_replace(
            ['\\', ';', ',', "\r\n", "\n"],
            ['\\\\', '\;', '\,', '\n', '\n'],
            $value
        );
    }
}