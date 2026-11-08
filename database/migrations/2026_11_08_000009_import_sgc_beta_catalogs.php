<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TABLES = [
        'clients', 'equipment', 'tool_families', 'tools',
        'service_types', 'cables', 'training_courses',
    ];

    public function up(): void
    {
        // Snapshot incluido en el repositorio: no depende de la carpeta o base de beta.
        $catalogs = [];
        foreach (self::TABLES as $table) {
            $path = database_path('data/sgc_beta/'.$table.'.json');
            $contents = file_get_contents($path);
            if ($contents === false) {
                throw new RuntimeException('No se pudo leer la carga inicial de '.$table);
            }
            $catalogs[$table] = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        }

        DB::transaction(function () use ($catalogs) {
            $now = now()->format('Y-m-d H:i:s');
            foreach ($catalogs as $table => $rows) {
                $familyIds = $table === 'tools'
                    ? DB::table('tool_families')->pluck('id', 'name')->all()
                    : [];

                foreach ($rows as &$row) {
                    if ($table === 'tools') {
                        $family = $row['family_name'];
                        if ($family !== null && !array_key_exists($family, $familyIds)) {
                            throw new RuntimeException('Familia no encontrada: '.$family);
                        }
                        $row['tool_family_id'] = $family === null ? null : $familyIds[$family];
                        unset($row['family_name']);
                    }
                    $row['created_at'] = $row['created_at'] ?? $now;
                    $row['updated_at'] = $row['updated_at'] ?? $now;
                }
                unset($row);

                foreach (array_chunk($rows, 100) as $chunk) {
                    DB::table($table)->insert($chunk);
                }
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            foreach (array_reverse(self::TABLES) as $table) {
                DB::table($table)->where('source', 'sgc_beta')->delete();
            }
        });
    }
};
