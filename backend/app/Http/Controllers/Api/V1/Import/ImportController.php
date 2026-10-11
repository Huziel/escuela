<?php
namespace App\Http\Controllers\Api\V1\Import;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Modules\Calificaciones\Services\CalificacionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportController extends Controller
{
    use ApiResponse;

    public function __construct(
        private CalificacionService $calificacionService
    ) {}

    private array $templates = [
        'alumnos' => [
            'headers' => ['nombre', 'apellido_paterno', 'apellido_materno', 'matricula', 'curp', 'semestre', 'especialidad', 'grupo', 'email', 'telefono', 'ciclo_escolar', 'generacion'],
            'example' => ['Juan', 'Pérez', 'López', 'A2024001', 'PELJ040101HDFRNN09', 1, 'Programación', '1A', 'juan.perez@cetis.edu.mx', '5551234567', '2026-1', '2026-2030'],
        ],
        'docentes' => [
            'headers' => ['nombre', 'apellido_paterno', 'apellido_materno', 'numero_empleado', 'curp', 'rfc', 'especialidad', 'grado_academico', 'email', 'telefono'],
            'example' => ['María', 'García', 'Hernández', 'D2024001', 'GAHM800101MDFRRN09', 'GAHM800101', 'Programación', 'Licenciatura', 'maria.garcia@cetis.edu.mx', '5559876543'],
        ],
        'grupos' => [
            'headers' => ['nombre', 'especialidad', 'semestre', 'turno', 'capacidad', 'ciclo_escolar', 'tutor_email'],
            'example' => ['1A', 'Programación', 1, 'matutino', 40, '2026-1', 'tutor@cetis.edu.mx'],
        ],
        'calificaciones' => [
            'headers' => ['matricula_alumno', 'materia', 'parcial1', 'parcial2', 'parcial3', 'asistencia_p1', 'asistencia_p2', 'asistencia_p3', 'estatus'],
            'example' => ['A2024001', 'Matemáticas', 8, 9, 7, 90, 85, 95, 'cursando'],
        ],
        'materias' => [
            'headers' => ['nombre', 'especialidad', 'semestre'],
            'example' => ['Álgebra', 'Todas', 1],
        ],
        'horarios' => [
            'headers' => ['grupo', 'dia', 'hora_inicio', 'hora_fin', 'materia', 'docente_email', 'aula'],
            'example' => ['1A', 'lunes', '07:00', '08:00', 'Matemáticas', 'profesor@cetis.edu.mx', 'A-12'],
        ],
    ];

    public function template(string $type): StreamedResponse
    {
        $tpl = $this->templates[$type] ?? null;
        if (!$tpl) abort(404, 'Tipo de plantilla no encontrado');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $col = 'A';
        foreach ($tpl['headers'] as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        $col = 'A';
        foreach ($tpl['example'] as $val) {
            $sheet->setCellValue($col . '2', $val);
            $col++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = "plantilla_{$type}.xlsx";

        return response()->streamDownload(
            fn() => $writer->save('php://output'),
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    public function preview(Request $request, string $type): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);
        $data = $this->parseFile($request->file('file'));
        $result = $this->validateRows($data, $type);

        Log::info('ImportController::preview', [
            'type' => $type,
            'total_rows' => count($data),
            'valid' => $result['valid'] ?? 0,
            'errors' => $result['errors'] ?? 0,
            'rows_count' => count($result['rows'] ?? []),
        ]);

        return $this->success($result, 'Vista previa generada.');
    }

    public function process(Request $request, string $type): JsonResponse
    {
        set_time_limit(600);
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);
        $data = $this->parseFile($request->file('file'));
        $validated = $this->validateRows($data, $type);

        Log::info('ImportController::process - validation result', [
            'type' => $type,
            'valid' => $validated['valid'] ?? 0,
            'errors' => $validated['errors'] ?? 0,
            'rows_to_import' => count($validated['raw'] ?? $validated['rows'] ?? []),
        ]);

        if ($validated['errors'] > 0 && $validated['valid'] === 0) {
            return $this->error('No hay registros válidos para importar. Corrige los errores.', 422);
        }

        // Use raw rows (with original nulls and _error key) for import.
        // Fall back to 'rows' (preview format) if 'raw' is not present for BC.
        $importRows = $validated['raw'] ?? $validated['rows'] ?? [];

        $result = match ($type) {
            'alumnos' => $this->importAlumnos($importRows),
            'docentes' => $this->importDocentes($importRows),
            'grupos' => $this->importGrupos($importRows),
            'calificaciones' => $this->importCalificaciones($importRows),
            'materias' => $this->importMaterias($importRows),
            'horarios' => $this->importHorarios($importRows),
            default => ['imported' => 0, 'errors' => []],
        };

        Log::info('ImportController::process - import completed', [
            'type' => $type,
            'imported' => $result['imported'] ?? 0,
            'import_errors' => count($result['errors'] ?? []),
        ]);

        return $this->success([
            'imported' => $result['imported'] ?? 0,
            'errors' => $result['errors'] ?? [],
        ], 'Importación completada. ' . ($result['imported'] ?? 0) . ' registros importados.');
    }

    private function parseFile($file): array
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();
        return $worksheet->toArray();
    }

    /**
     * Validate all rows from the parsed spreadsheet.
     *
     * Returns:
     *  - headers:  normalized header names
     *  - rows:     preview rows (for frontend display) — null values kept as null
     *  - raw:      raw validated rows (for import) — includes _error key, preserves nulls
     *  - valid:    count of rows that passed validation
     *  - errors:   count of rows that failed validation
     */
    private function validateRows(array $data, string $type): array
    {
        if (count($data) < 2) {
            Log::warning('ImportController::validateRows - insufficient data rows', [
                'type' => $type,
                'data_count' => count($data),
            ]);
            return ['headers' => [], 'rows' => [], 'raw' => [], 'valid' => 0, 'errors' => 0];
        }

        $headers = array_map('trim', array_map('strtolower', $data[0]));
        $rows = [];       // raw validated rows — used for import
        $preview = [];    // display rows — used for preview
        $valid = 0;
        $errorCount = 0;
        $skippedEmpty = 0;

        for ($i = 1; $i < count($data); $i++) {
            // Build row from headers, trimming all cell values
            $row = [];
            $error = null;
            $allEmpty = true;

            foreach ($headers as $j => $h) {
                $cellValue = $data[$i][$j] ?? null;

                // Trim string values to catch whitespace-only cells
                if (is_string($cellValue)) {
                    $cellValue = trim($cellValue);
                }

                $row[$h] = $cellValue;

                // Track whether the entire row is effectively empty
                if ($cellValue !== null && $cellValue !== '') {
                    $allEmpty = false;
                }
            }

            // Skip completely empty rows (all cells null/empty/whitespace)
            if ($allEmpty) {
                $skippedEmpty++;
                continue;
            }

            $row = match ($type) {
                'alumnos' => $this->validateAlumno($row, $error),
                'docentes' => $this->validateDocente($row, $error),
                'grupos' => $this->validateGrupo($row, $error),
                'calificaciones' => $this->validateCalificacion($row, $error),
                'materias' => $this->validateMateria($row, $error),
                'horarios' => $this->validateHorario($row, $error),
                default => null,
            };

            // match default returns null — skip unknown types
            if ($row === null) {
                Log::warning('ImportController::validateRows - unknown type in match default', [
                    'type' => $type,
                    'row_index' => $i,
                ]);
                continue;
            }

            // Attach error flag to the raw row
            $row['_error'] = $error;

            if (!$error) {
                $valid++;
            } else {
                $errorCount++;
            }

            $rows[] = $row;

            // Build preview row: same keys as raw, but without _error (use 'error' for frontend)
            $entry = [];
            foreach ($headers as $h) {
                $entry[$h] = $row[$h] ?? null;
            }
            $entry['error'] = $error;
            $preview[] = $entry;
        }

        Log::info('ImportController::validateRows - summary', [
            'type' => $type,
            'total_data_rows' => count($data) - 1,
            'skipped_empty' => $skippedEmpty,
            'processed' => count($rows),
            'valid' => $valid,
            'errors' => $errorCount,
            'headers' => $headers,
        ]);

        if (count($rows) > 0) {
            Log::debug('ImportController::validateRows - sample first row', [
                'first_row' => array_slice($rows[0], 0, 10),
            ]);
        }

        return [
            'headers' => $headers,
            'rows' => $preview,   // for preview display (key: 'error')
            'raw' => $rows,       // for import (key: '_error', nulls preserved)
            'valid' => $valid,
            'errors' => $errorCount,
        ];
    }

    private function validateAlumno(array $row, ?string &$error): ?array
    {
        if (empty($row['nombre']) || empty($row['apellido_paterno'])) {
            $error = 'Falta nombre o apellido'; return $row;
        }
        if (empty($row['matricula'])) {
            $error = 'Falta matrícula'; return $row;
        }
        return $row;
    }

    private function validateDocente(array $row, ?string &$error): ?array
    {
        if (empty($row['nombre']) || empty($row['apellido_paterno'])) {
            $error = 'Falta nombre o apellido'; return $row;
        }
        return $row;
    }

    private function validateGrupo(array $row, ?string &$error): ?array
    {
        if (empty($row['nombre'])) {
            $error = 'Falta nombre del grupo'; return $row;
        }
        return $row;
    }

    private function validateCalificacion(array $row, ?string &$error): ?array
    {
        if (empty($row['matricula_alumno']) || empty($row['materia'])) {
            $error = 'Falta matrícula o materia'; return $row;
        }
        return $row;
    }

    private function validateMateria(array $row, ?string &$error): ?array
    {
        if (empty($row['nombre'])) {
            $error = 'Falta nombre de la materia'; return $row;
        }
        return $row;
    }

    private function importAlumnos(array $rows): array
    {
        $imported = 0;
        $errors = [];
        $defaultPass = bcrypt(bin2hex(random_bytes(8)));
        $especialidades = \App\Modules\Especialidades\Models\Especialidad::pluck('id', 'nombre');
        $fallbackEspId = $especialidades->first();
        $grupos = \App\Modules\Grupos\Models\Grupo::select('id', 'nombre', 'ciclo_escolar')->get()
            ->groupBy('nombre');
        $generaciones = \App\Models\Generacion::pluck('id', 'nombre')->all();

        foreach ($rows as $i => $r) {
            if (($r['_error'] ?? $r['error'] ?? null) !== null) continue;
            try {
                $nombre = trim($r['nombre'] ?? '');
                $apellidoPaterno = trim($r['apellido_paterno'] ?? '');
                $apellidoMaterno = !empty($r['apellido_materno']) ? trim($r['apellido_materno']) : null;
                $matricula = trim($r['matricula'] ?? '');
                if (!$matricula) { $errors[] = ['row' => $i + 1, 'matricula' => '', 'error' => 'Matrícula vacía']; continue; }
                $curp = !empty($r['curp']) ? trim($r['curp']) : null;
                $semestre = !empty($r['semestre']) ? (int) $r['semestre'] : 1;
                $email = !empty($r['email']) ? trim($r['email']) : ($matricula . '@cetis.edu.mx');
                $telefono = !empty($r['telefono']) ? trim($r['telefono']) : null;
                $cicloEscolar = !empty($r['ciclo_escolar']) ? trim($r['ciclo_escolar']) : (date('Y') . '-1');

                $espNombre = !empty($r['especialidad']) ? trim($r['especialidad']) : null;
                $especialidadId = $fallbackEspId;
                if ($espNombre) {
                    foreach ($especialidades as $nombreDB => $idDB) {
                        if (stripos($nombreDB, $espNombre) !== false || stripos($espNombre, $nombreDB) !== false) {
                            $especialidadId = $idDB; break;
                        }
                    }
                }

                $grupoNombre = !empty($r['grupo']) ? trim($r['grupo']) : null;
                $grupoId = null;
                if ($grupoNombre && isset($grupos[$grupoNombre])) {
                    foreach ($grupos[$grupoNombre] as $g) {
                        if ($g->ciclo_escolar == $cicloEscolar) { $grupoId = $g->id; break; }
                    }
                    if (!$grupoId) $grupoId = $grupos[$grupoNombre]->first()->id;
                }

                $generacionNombre = !empty($r['generacion']) ? trim($r['generacion']) : null;
                $generacionId = null;
                if ($generacionNombre && $generaciones) {
                    foreach ($generaciones as $nombre => $id) {
                        if (stripos($nombre, $generacionNombre) !== false || stripos($generacionNombre, $nombre) !== false) {
                            $generacionId = $id; break;
                        }
                    }
                }

                $password = $curp ? bcrypt($curp) : $defaultPass;
                $user = \App\Models\User::firstOrCreate(
                    ['email' => $email],
                    ['password' => $password, 'activo' => true,
                     'nombre' => $nombre, 'apellido_paterno' => $apellidoPaterno,
                     'apellido_materno' => $apellidoMaterno, 'telefono' => $telefono,
                     'username' => $matricula]
                );
                $user->update(['nombre' => $nombre, 'apellido_paterno' => $apellidoPaterno,
                    'apellido_materno' => $apellidoMaterno, 'telefono' => $telefono]);

                $alumno = \App\Modules\Alumnos\Models\Alumno::updateOrCreate(
                    ['matricula' => $matricula],
                    ['user_id' => $user->id, 'semestre' => $semestre,
                     'especialidad_id' => $especialidadId, 'grupo_id' => $grupoId,
                     'ciclo_escolar' => $cicloEscolar, 'estatus' => 'activo', 'fecha_ingreso' => now(),
                     'generacion_id' => $generacionId]
                );
                // Try to set CURP; fallback to null if it conflicts with another alumno
                if ($curp) {
                    try {
                        $alumno->update(['curp' => $curp]);
                    } catch (\Exception $e) {
                        if (str_contains($e->getMessage(), 'curp_unique')) {
                            $alumno->update(['curp' => null]);
                        } else {
                            throw $e;
                        }
                    }
                }

                try { $user->assignRole('alumno'); } catch (\Exception $e) {}

                $imported++;
            } catch (\Exception $e) {
                $errors[] = ['row' => $i + 1, 'matricula' => $r['matricula'] ?? '', 'error' => $e->getMessage()];
            }
        }
        return ['imported' => $imported, 'errors' => $errors];
    }

    private function importDocentes(array $rows): array
    {
        $count = 0;
        $defaultPass = bcrypt(bin2hex(random_bytes(8)));
        foreach ($rows as $i => $r) {
            if (($r['_error'] ?? $r['error'] ?? null) !== null) {
                continue;
            }
            try {
                // Extract and trim all string values
                $nombre = trim($r['nombre'] ?? '');
                $apellidoPaterno = trim($r['apellido_paterno'] ?? '');
                $apellidoMaterno = !empty($r['apellido_materno']) ? trim($r['apellido_materno']) : null;
                $numeroEmpleado = !empty($r['numero_empleado']) && !in_array(trim($r['numero_empleado']), ['-', 'N/A', 'n/a']) ? trim($r['numero_empleado']) : null;
                $curp = !empty($r['curp']) && trim($r['curp']) !== '-' ? trim($r['curp']) : null;
                $rfc = !empty($r['rfc']) && trim($r['rfc']) !== '-' ? trim($r['rfc']) : null;
                $gradoAcademico = !empty($r['grado_academico']) && trim($r['grado_academico']) !== '-' ? trim($r['grado_academico']) : null;

                // Generate deterministic username BEFORE email (email uses username as fallback)
                $nombreASCII = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nombre) ?: $nombre;
                $apellidoASCII = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $apellidoPaterno) ?: $apellidoPaterno;
                $nombreSlug = preg_replace('/[^a-zA-Z0-9]/', '', $nombreASCII);
                $apellidoSlug = preg_replace('/[^a-zA-Z0-9]/', '', $apellidoASCII);
                $usernameBase = strtolower($nombreSlug . '.' . $apellidoSlug);
                $username = $usernameBase;

                $email = !empty($r['email']) && trim($r['email']) !== '-' ? trim($r['email']) : ($numeroEmpleado ? $numeroEmpleado . '@cetis.edu.mx' : ($username . '@cetis.edu.mx'));
                $telefono = !empty($r['telefono']) && trim($r['telefono']) !== '-' ? trim($r['telefono']) : null;

                // Resolve especialidad
                $especialidadNombre = !empty($r['especialidad']) && trim($r['especialidad']) !== '-' ? trim($r['especialidad']) : null;
                $especialidad = null;
                if (!empty($especialidadNombre)) {
                    $especialidad = \App\Modules\Especialidades\Models\Especialidad::where('nombre', 'like', '%' . $especialidadNombre . '%')->first();
                }
                $especialidadId = $especialidad?->id;

                $userData = ['nombre' => $nombre, 'apellido_paterno' => $apellidoPaterno, 'apellido_materno' => $apellidoMaterno, 'username' => $username, 'password' => $defaultPass, 'telefono' => $telefono, 'activo' => true];
                if (!empty($email)) {
                    $userData['email'] = $email;
                    $user = \App\Models\User::updateOrCreate(['email' => $email], $userData);
                } else {
                    $user = \App\Models\User::updateOrCreate(['username' => $username], $userData);
                }

                // Create docente — use firstOrCreate by numero_empleado to avoid duplicates
                $docenteData = [
                    'user_id' => $user->id,
                    'curp' => $curp,
                    'rfc' => $rfc,
                    'especialidad_id' => $especialidadId,
                    'grado_academico' => $gradoAcademico,
                    'estatus' => 'activo',
                    'fecha_ingreso' => now(),
                ];

                Log::debug('ImportController::importDocentes - creating docente', [
                    'row_index' => $i,
                    'data' => $docenteData,
                ]);

                if ($numeroEmpleado) {
                    $docenteData['numero_empleado'] = $numeroEmpleado;
                    \App\Modules\Docentes\Models\Docente::updateOrCreate(
                        ['numero_empleado' => $numeroEmpleado],
                        $docenteData
                    );
                } else {
                    \App\Modules\Docentes\Models\Docente::updateOrCreate(
                        ['user_id' => $user->id],
                        $docenteData
                    );
                }

                // Assign role in separate try block to avoid breaking the import
                try {
                    $user->assignRole('docente');
                } catch (\Exception $roleEx) {
                    Log::warning('ImportController::importDocentes - assignRole failed', [
                        'row_index' => $i,
                        'user_id' => $user->id,
                        'numero_empleado' => $numeroEmpleado,
                        'exception' => $roleEx->getMessage(),
                    ]);
                }

                $count++;
            } catch (\Exception $e) {
                Log::error('ImportController::importDocentes - failed row', [
                    'row_index' => $i,
                    'nombre' => $r['nombre'] ?? 'N/A',
                    'numero_empleado' => $r['numero_empleado'] ?? 'N/A',
                    'email' => $r['email'] ?? null,
                    'especialidad' => $r['especialidad'] ?? null,
                    'exception' => $e->getMessage(),
                ]);
            }
        }
        return ['imported' => $count, 'errors' => []];
    }

    private function importGrupos(array $rows): array
    {
        $count = 0;
        foreach ($rows as $i => $r) {
            if (($r['_error'] ?? $r['error'] ?? null) !== null) {
                continue;
            }
            try {
                // Resolve especialidad (optional — only set if found)
                $especialidadNombre = $r['especialidad'] ?? null;
                $especialidad = null;
                if (!empty($especialidadNombre)) {
                    $especialidad = \App\Modules\Especialidades\Models\Especialidad::where('nombre', 'like', '%' . $especialidadNombre . '%')->first();
                }
                $especialidadId = $especialidad?->id;
                if (!$especialidadId) {
                    $especialidadId = \App\Modules\Especialidades\Models\Especialidad::first()?->id;
                }

                // Normalize turno: lowercase to match DB ENUM ('matutino','vespertino','nocturno')
                $turnoRaw = $r['turno'] ?? null;
                $turno = $turnoRaw ? strtolower(trim($turnoRaw)) : 'matutino';

                // Validate turno against allowed ENUM values
                $turnosValidos = ['matutino', 'vespertino', 'nocturno'];
                if (!in_array($turno, $turnosValidos)) {
                    Log::warning('ImportController::importGrupos - invalid turno, using default', [
                        'row_index' => $i,
                        'received' => $turnoRaw,
                        'defaulting_to' => 'matutino',
                    ]);
                    $turno = 'matutino';
                }

                // Resolve semestre and capacidad with safe defaults
                $semestre = !empty($r['semestre']) ? (int) $r['semestre'] : 1;
                $capacidad = !empty($r['capacidad']) ? (int) $r['capacidad'] : 40;

                // ciclo_escolar: use provided value or default
                $cicloEscolar = !empty($r['ciclo_escolar']) ? $r['ciclo_escolar'] : (date('Y') . '-1');

                $grupoData = [
                    'nombre' => $r['nombre'],
                    'especialidad_id' => $especialidadId,
                    'semestre' => $semestre,
                    'turno' => $turno,
                    'capacidad' => $capacidad,
                    'ciclo_escolar' => $cicloEscolar,
                ];

                Log::debug('ImportController::importGrupos - creating grupo', [
                    'row_index' => $i,
                    'data' => $grupoData,
                ]);

                \App\Modules\Grupos\Models\Grupo::updateOrCreate(
                    ['nombre' => $r['nombre'], 'ciclo_escolar' => $cicloEscolar, 'turno' => $turno],
                    $grupoData
                );

                $count++;
            } catch (\Exception $e) {
                Log::error('ImportController::importGrupos - failed row', [
                    'row_index' => $i,
                    'nombre' => $r['nombre'] ?? 'N/A',
                    'especialidad' => $r['especialidad'] ?? null,
                    'turno' => $r['turno'] ?? null,
                    'exception' => $e->getMessage(),
                ]);
            }
        }
        return ['imported' => $count, 'errors' => []];
    }

    private function importCalificaciones(array $rows): array
    {
        set_time_limit(600);
        $imported = 0;
        $errors = [];
        
        $alumnos = \App\Modules\Alumnos\Models\Alumno::all()->keyBy('matricula');
        $materias = \App\Modules\Materias\Models\Materia::all()->keyBy(fn($m) => strtolower(trim($m->nombre)));
        
        foreach ($rows as $i => $r) {
            if (($r['_error'] ?? $r['error'] ?? null) !== null) continue;
            try {
                $matriculaAlumno = trim($r['matricula_alumno'] ?? '');
                $materiaNombre = strtolower(trim($r['materia'] ?? ''));
                
                $alumno = $alumnos->get($matriculaAlumno);
                if (!$alumno) {
                    $errors[] = ['row' => $i + 1, 'matricula' => $matriculaAlumno, 'error' => 'Alumno no encontrado'];
                    continue;
                }
                $materia = $materias->get($materiaNombre);
                if (!$materia) {
                    $errors[] = ['row' => $i + 1, 'matricula' => $matriculaAlumno, 'materia' => $r['materia'] ?? '', 'error' => 'Materia no encontrada'];
                    continue;
                }

                $parcial1 = !empty($r['parcial1']) && is_numeric($r['parcial1']) ? (float) $r['parcial1'] : null;
                $parcial2 = !empty($r['parcial2']) && is_numeric($r['parcial2']) ? (float) $r['parcial2'] : null;
                $parcial3 = !empty($r['parcial3']) && is_numeric($r['parcial3']) ? (float) $r['parcial3'] : null;
                $asistenciaP1 = !empty($r['asistencia_p1']) && is_numeric($r['asistencia_p1']) ? (float) $r['asistencia_p1'] : null;
                $asistenciaP2 = !empty($r['asistencia_p2']) && is_numeric($r['asistencia_p2']) ? (float) $r['asistencia_p2'] : null;
                $asistenciaP3 = !empty($r['asistencia_p3']) && is_numeric($r['asistencia_p3']) ? (float) $r['asistencia_p3'] : null;
                $estatus = !empty($r['estatus']) ? strtolower(trim($r['estatus'])) : 'cursando';
                if (!in_array($estatus, ['cursando', 'aprobada', 'reprobada', 'regularizacion'])) $estatus = 'cursando';

                $grupoId = $alumno->grupo_id;
                if (!$grupoId) {
                    $grupoId = \App\Modules\Grupos\Models\Grupo::first()?->id ?? 0;
                }

                \App\Modules\Calificaciones\Models\Calificacion::updateOrCreate(
                    ['alumno_id' => $alumno->id, 'materia_id' => $materia->id],
                    [
                        'grupo_id' => $grupoId,
                        'periodo' => $alumno->ciclo_escolar ?? (date('Y') . '-1'),
                        'parcial1' => $parcial1, 'parcial2' => $parcial2, 'parcial3' => $parcial3,
                        'asistencia_p1' => $asistenciaP1, 'asistencia_p2' => $asistenciaP2, 'asistencia_p3' => $asistenciaP3,
                        'estatus' => $estatus,
                        'promedio_final' => (is_numeric($parcial1) && is_numeric($parcial2) && is_numeric($parcial3))
                            ? round(($parcial1 + $parcial2 + $parcial3) / 3, 1) : null,
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $errors[] = ['row' => $i + 1, 'matricula' => $r['matricula_alumno'] ?? '', 'materia' => $r['materia'] ?? '', 'error' => $e->getMessage()];
            }
        }
        return ['imported' => $imported, 'errors' => $errors];
    }

    private function importMaterias(array $rows): array
    {
        $count = 0;
        foreach ($rows as $i => $r) {
            if (($r['_error'] ?? $r['error'] ?? null) !== null) continue;
            try {
                $nombre = trim($r['nombre']);
                $semestre = !empty($r['semestre']) ? (int) $r['semestre'] : 1;

                $especialidadId = null;
                $espNombre = $r['especialidad'] ?? null;
                if (!empty($espNombre) && strtolower(trim($espNombre)) !== 'todas') {
                    $esp = \App\Modules\Especialidades\Models\Especialidad::where('nombre', 'like', '%' . trim($espNombre) . '%')->first();
                    $especialidadId = $esp?->id;
                }

                \App\Modules\Materias\Models\Materia::updateOrCreate(
                    ['nombre' => $nombre, 'especialidad_id' => $especialidadId, 'semestre' => $semestre],
                    [
                        'especialidad_id' => $especialidadId,
                        'semestre' => $semestre,
                    ]
                );

                $count++;
            } catch (\Exception $e) {
                Log::error('ImportController::importMaterias - failed row', [
                    'row_index' => $i,
                    'nombre' => $r['nombre'] ?? 'N/A',
                    'exception' => $e->getMessage(),
                ]);
            }
        }
        return ['imported' => $count, 'errors' => []];
    }

    private function validateHorario(array $row, ?string &$error): ?array
    {
        if (empty($row['grupo']) || empty($row['dia']) || empty($row['hora_inicio']) || empty($row['hora_fin']) || empty($row['materia'])) {
            $error = 'Falta grupo, día, hora o materia'; return $row;
        }
        return $row;
    }

    private function importHorarios(array $rows): array
    {
        $imported = 0; $errors = [];
        $grupos = \App\Modules\Grupos\Models\Grupo::pluck('id', 'nombre');
        $materias = \App\Modules\Materias\Models\Materia::pluck('id', 'nombre');
        $docentes = \App\Modules\Docentes\Models\Docente::with('user')->get()->keyBy(fn($d) => $d->user?->email);

        foreach ($rows as $i => $r) {
            if (($r['_error'] ?? $r['error'] ?? null) !== null) continue;
            try {
                $grupoNombre = trim($r['grupo'] ?? '');
                $grupoId = $grupos[$grupoNombre] ?? null;
                if (!$grupoId) { $errors[] = ['row' => $i + 1, 'grupo' => $grupoNombre, 'error' => 'Grupo no encontrado']; continue; }

                $materiaNombre = trim($r['materia'] ?? '');
                $materiaId = $materias->first(fn($v, $k) => stripos($k, $materiaNombre) !== false);
                if (!$materiaId) { $errors[] = ['row' => $i + 1, 'materia' => $materiaNombre, 'error' => 'Materia no encontrada']; continue; }

                $docenteId = null;
                $docenteEmail = trim($r['docente_email'] ?? '');
                if ($docenteEmail && isset($docentes[$docenteEmail])) $docenteId = $docentes[$docenteEmail]->id;

                $dia = strtolower(trim($r['dia'] ?? 'lunes'));
                $diasValidos = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
                if (!in_array($dia, $diasValidos)) $dia = 'lunes';

                \App\Modules\Horarios\Models\Horario::updateOrCreate(
                    ['grupo_id' => $grupoId, 'dia' => $dia, 'hora_inicio' => trim($r['hora_inicio'] ?? '')],
                    [
                        'materia_id' => $materiaId,
                        'docente_id' => $docenteId,
                        'hora_fin' => trim($r['hora_fin'] ?? ''),
                        'aula' => trim($r['aula'] ?? ''),
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $errors[] = ['row' => $i + 1, 'error' => $e->getMessage()];
            }
        }
        return ['imported' => $imported, 'errors' => $errors];
    }
}
