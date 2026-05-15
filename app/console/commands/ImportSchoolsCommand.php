<?php

namespace App\Console\Commands;

use App\Models\School;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class ImportSchoolsCommand extends Command
{
    protected $signature = 'import:schools';

    protected $description = 'Importa colegios desde archivos JSON ubicados en public/colegios/';

    public function handle(): int
    {
        $directory = public_path('colegios');

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $files = File::glob($directory . DIRECTORY_SEPARATOR . '*.json');

        if ($files === []) {
            $this->warn('No se encontraron archivos JSON.');
            $this->newLine();
            $this->line('Por favor coloque los archivos en:');
            $this->line('public/colegios/');
            $this->newLine();
            $this->line('Ejemplos válidos:');
            $this->line('public/colegios/colegios_santacruz.json');
            $this->line('public/colegios/colegios_lapaz.json');

            return self::SUCCESS;
        }

        $this->info('Iniciando la importación de colegios...');

        $imported = 0;
        $updated = 0;
        $skipped = 0;

        DB::beginTransaction();

        try {
            foreach ($files as $file) {
                $fileName = basename($file);
                $contents = File::get($file);
                $records = json_decode($contents, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->error("Error al decodificar {$fileName}: " . json_last_error_msg());
                    continue;
                }

                if (! is_array($records)) {
                    $this->warn("El archivo {$fileName} no contiene una lista válida de colegios, omitiendo...");
                    continue;
                }

                $this->newLine();
                $this->info("Importando colegios desde {$fileName}...");

                $this->withProgressBar($records, function (array $record) use (&$imported, &$updated, &$skipped): void {
                    $payload = $this->normalizeSchool($record);

                    if ($payload['codigo_rue'] === null) {
                        $skipped++;

                        return;
                    }

                    $school = School::updateOrCreate(
                        ['codigo_rue' => $payload['codigo_rue']],
                        $payload
                    );

                    $school->wasRecentlyCreated ? $imported++ : $updated++;
                });
            }

            DB::commit();

            $this->newLine(2);
            $this->info('Importación completada correctamente.');
            $this->line("Colegios nuevos: {$imported}");
            $this->line("Colegios actualizados: {$updated}");
            $this->line("Registros omitidos sin RUE: {$skipped}");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            DB::rollBack();

            $this->error('Ocurrió un error durante la importación: ' . $exception->getMessage());
            $this->error('Línea: ' . $exception->getLine() . ' en ' . $exception->getFile());

            return self::FAILURE;
        }
    }

    /**
     * @param array<string, mixed> $record
     * @return array<string, mixed>
     */
    private function normalizeSchool(array $record): array
    {
        $general = is_array($record['general'] ?? null) ? $record['general'] : $record;
        $location = is_array($record['ubicacion'] ?? null) ? $record['ubicacion'] : [];

        return [
            'nombre' => $this->clean($general['nombre'] ?? $record['nombre'] ?? 'Sin nombre'),
            'codigo_rue' => $this->clean($general['codigo_rue'] ?? $general['rue'] ?? $record['codigo_rue'] ?? $record['rue'] ?? null),
            'departamento' => $this->clean($location['departamento'] ?? $record['departamento'] ?? null),
            'provincia' => $this->clean($location['provincia'] ?? $record['provincia'] ?? null),
            'municipio' => $this->clean($location['municipio'] ?? $record['municipio'] ?? null),
            'distrito' => $this->clean($location['distrito'] ?? $record['distrito'] ?? null),
            'area' => $this->clean($location['area'] ?? $general['area'] ?? $record['area'] ?? null),
            'dependencia' => $this->clean($general['dependencia'] ?? $record['dependencia'] ?? null),
            'niveles' => $this->clean($general['niveles'] ?? $general['nivel'] ?? $record['niveles'] ?? $record['nivel'] ?? null),
            'turnos' => $this->clean($general['turnos'] ?? $general['turno'] ?? $record['turnos'] ?? $record['turno'] ?? null),
            'director' => $this->clean($general['director'] ?? $record['director'] ?? null),
            'direccion' => $this->clean($general['direccion'] ?? $record['direccion'] ?? null),
            'telefonos' => $this->clean($general['telefonos'] ?? $general['telefono'] ?? $record['telefonos'] ?? $record['telefono'] ?? null),
            'url_ficha' => $this->clean($record['url'] ?? $general['url_ficha'] ?? $record['url_ficha'] ?? null),
            'metadata' => $record,
        ];
    }

    private function clean(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            $value = implode(', ', array_filter($value));
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
