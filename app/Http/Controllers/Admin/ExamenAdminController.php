<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Services\ExamenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExamenAdminController extends Controller
{
    private const RCLONE_REMOTE = 'mimedico-drive:mimedico-backups/examenes';
    private const LOG_PATH      = 'logs/rclone-drive.log';

    public function index()
    {
        $resumen = ExamenService::resumenAdmin();

        foreach ($resumen['pacientes'] as &$p) {
            $persona   = Persona::find($p['cedula']);
            $p['nombre'] = $persona ? $persona->nombre_completo : 'Paciente ' . $p['cedula'];
        }

        foreach ($resumen['recientes'] as &$r) {
            $persona              = Persona::find($r['cedula']);
            $r['nombre_paciente'] = $persona ? $persona->nombre_completo : $r['cedula'];
        }

        $backupInfo = $this->leerInfoBackup();

        return view('admin.examenes.index', compact('resumen', 'backupInfo'));
    }

    public function descargar(string $cedula, string $id)
    {
        $entry = ExamenService::obtener($cedula, $id);
        if (!$entry) abort(404);

        $ruta = ExamenService::rutaStorage($cedula, $entry['nombre_guardado']);
        if (!Storage::exists($ruta)) abort(404);

        return Storage::download($ruta, $entry['nombre_original']);
    }

    // ── Ejecutar backup a Google Drive ───────────────────────────────
    public function backupDrive()
    {
        $configFile = $this->rcloneConfigFile();
        if (empty($configFile)) {
            return back()->with('backup_error', 'Archivo de configuración de rclone no encontrado en storage/rclone.conf');
        }

        $bin = $this->rcloneBin();
        if (empty($bin)) {
            return back()->with('backup_error',
                'rclone no está instalado en el contenedor PHP. ' .
                'Reconstruye los contenedores: docker compose build php && docker compose up -d php'
            );
        }

        $origen  = Storage::path('examenes');   // respeta storage/app/private en Laravel 11
        $remoto  = self::RCLONE_REMOTE;
        $logFile = storage_path(self::LOG_PATH);

        // Crear carpeta si no existe (cuando aún no hay archivos subidos)
        if (!is_dir($origen)) {
            mkdir($origen, 0775, true);
        }

        $header = "\n[" . now()->format('Y-m-d H:i:s') . "] Iniciando backup a Google Drive...\n";
        file_put_contents($logFile, $header, FILE_APPEND);

        $cmd = escapeshellarg($bin)
             . " --config=" . escapeshellarg($configFile)
             . " copy " . escapeshellarg($origen)
             . " " . escapeshellarg($remoto)
             . " --log-file=" . escapeshellarg($logFile)
             . " --log-level INFO 2>&1 &";

        exec($cmd);
        file_put_contents(storage_path('logs/rclone-ultimo.txt'), now()->toDateTimeString());

        return back()->with('backup_ok', 'Backup iniciado. Recarga la página en unos minutos para ver el progreso en el log.');
    }

    // ── Ver log de backup ────────────────────────────────────────────
    public function backupLog()
    {
        $logFile = storage_path(self::LOG_PATH);
        $log     = file_exists($logFile) ? file_get_contents($logFile) : 'Sin registros todavía.';

        return response($log, 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    // ── Restaurar exámenes desde Google Drive ────────────────────────
    public function restaurarDrive(Request $request)
    {
        $cedula = preg_replace('/\D/', '', (string) $request->input('cedula', ''));

        $configFile = $this->rcloneConfigFile();
        if (empty($configFile)) {
            return back()->with('restaurar_error', 'Archivo de configuración de rclone no encontrado en storage/rclone.conf');
        }

        $bin = $this->rcloneBin();
        if (empty($bin)) {
            return back()->with('restaurar_error',
                'rclone no está instalado. Reconstruye los contenedores: docker compose build php && docker compose up -d php'
            );
        }

        $logFile = storage_path('logs/rclone-restaurar.log');

        if ($cedula) {
            $remoto  = self::RCLONE_REMOTE . '/' . $cedula;
            $destino = Storage::path('examenes/' . $cedula);
        } else {
            $remoto  = self::RCLONE_REMOTE;
            $destino = Storage::path('examenes');
        }

        if (!is_dir($destino)) {
            mkdir($destino, 0775, true);
        }

        $etiqueta = $cedula ? " (cédula: {$cedula})" : " completa";
        $header   = "\n[" . now()->format('Y-m-d H:i:s') . "] Iniciando restauración{$etiqueta} desde Google Drive...\n";
        file_put_contents($logFile, $header, FILE_APPEND);

        $cmd = escapeshellarg($bin)
             . " --config=" . escapeshellarg($configFile)
             . " copy " . escapeshellarg($remoto)
             . " " . escapeshellarg($destino)
             . " --log-file=" . escapeshellarg($logFile)
             . " --log-level INFO 2>&1 &";

        exec($cmd);

        file_put_contents(storage_path('logs/rclone-restaurar-estado.json'), json_encode([
            'inicio' => now()->toDateTimeString(),
            'cedula' => $cedula ?: null,
        ], JSON_PRETTY_PRINT));

        $msg = $cedula
            ? "Restauración iniciada para cédula {$cedula}. Monitorea el progreso abajo."
            : 'Restauración completa iniciada. Monitorea el progreso abajo.';

        return back()->with('restaurar_ok', $msg);
    }

    // ── Estado en tiempo real de la restauración (AJAX) ────────────────
    public function restaurarEstado()
    {
        $logFile    = storage_path('logs/rclone-restaurar.log');
        $estadoFile = storage_path('logs/rclone-restaurar-estado.json');

        $result = [
            'en_progreso' => false,
            'completado'  => false,
            'hay_error'   => false,
            'log_lines'   => [],
            'inicio'      => null,
            'cedula'      => null,
        ];

        if (file_exists($estadoFile)) {
            $data = json_decode(file_get_contents($estadoFile), true) ?? [];
            $result['inicio'] = $data['inicio'] ?? null;
            $result['cedula'] = $data['cedula'] ?? null;
        }

        if (!file_exists($logFile) || !$result['inicio']) {
            return response()->json($result);
        }

        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $inicioIdx = 0;
        foreach ($lines as $i => $line) {
            if (str_contains($line, 'Iniciando restauración')) {
                $inicioIdx = $i;
            }
        }

        $bloque = array_slice($lines, $inicioIdx);
        $result['log_lines'] = array_slice($bloque, -25);

        foreach ($bloque as $line) {
            if (str_contains($line, 'Transferred:') || str_contains($line, 'There was nothing to transfer')) {
                $result['completado'] = true;
            }
            if (str_contains($line, 'ERROR') || str_contains($line, 'Failed to copy')) {
                $result['hay_error'] = true;
            }
        }

        $result['en_progreso'] = !$result['completado'];

        return response()->json($result);
    }

    // ── Ver log de restauración ──────────────────────────────────────
    public function restaurarLog()
    {
        $logFile = storage_path('logs/rclone-restaurar.log');
        $log     = file_exists($logFile) ? file_get_contents($logFile) : 'Sin registros de restauración todavía.';

        return response($log, 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    // ── Iniciar cambio de cuenta Drive (OAuth automático) ────────────
    public function cambiarDrive()
    {
        $bin        = $this->rcloneBin();
        $configFile = $this->rcloneConfigFile();

        if (empty($bin) || empty($configFile)) {
            return redirect()->route('admin.examenes.index')
                ->with('backup_error', 'rclone no está disponible. Asegúrate de haber reconstruido los contenedores.');
        }

        // Terminar cualquier proceso de auth previo
        exec('pkill -f "rclone.*reconnect" 2>/dev/null');

        // Arrancar el servidor OAuth de rclone en background
        // Escribe el nuevo token directamente en storage/rclone.conf
        exec(escapeshellarg($bin)
            . ' config reconnect mimedico-drive:'
            . ' --config=' . escapeshellarg($configFile)
            . ' --auto-config > /dev/null 2>&1 &');

        // Dar 2 segundos para que rclone levante su servidor HTTP
        sleep(2);

        // La vista redirige al navegador al puerto OAuth de rclone
        return view('admin.examenes.cambiar-drive');
    }

    // ── Confirmar que el cambio fue exitoso ──────────────────────────
    public function guardarDrive()
    {
        // El token ya fue guardado automáticamente por rclone en el archivo de config.
        // Solo sincronizamos la copia en WSL si es accesible.
        $configFile = $this->rcloneConfigFile();
        $wslConfig  = '/home/dody_ortega/.config/rclone/rclone.conf';

        if ($configFile && file_exists($configFile) && file_exists($wslConfig) && is_writable($wslConfig)) {
            file_put_contents($wslConfig, file_get_contents($configFile));
        }

        return redirect()->route('admin.examenes.index')
            ->with('backup_ok', 'Cuenta de Google Drive actualizada. El próximo backup usará la nueva cuenta.');
    }

    // ── Info del último backup ────────────────────────────────────────
    private function leerInfoBackup(): array
    {
        $ultimoFile = storage_path('logs/rclone-ultimo.txt');
        $logFile    = storage_path(self::LOG_PATH);

        $ultimo   = file_exists($ultimoFile) ? file_get_contents($ultimoFile) : null;
        $logLines = [];
        $hayError = false;

        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            // Encontrar el inicio del ÚLTIMO backup (línea "[YYYY-MM-DD...] Iniciando...")
            $inicioUltimo = 0;
            foreach ($lines as $i => $line) {
                if (preg_match('/^\[\d{4}-\d{2}-\d{2}.*Iniciando backup/', $line)) {
                    $inicioUltimo = $i;
                }
            }
            $ultimasLineas = array_slice($lines, $inicioUltimo);
            $logLines      = array_slice($ultimasLineas, 0, 30);

            // Evaluar errores solo en el último backup
            foreach ($ultimasLineas as $line) {
                if (str_contains($line, 'ERROR') || str_contains($line, 'Failed to copy')) {
                    $hayError = true;
                    break;
                }
            }
        }

        $configFile  = $this->rcloneConfigFile();
        $remoteName  = explode(':', self::RCLONE_REMOTE)[0];
        $configOk    = !empty($configFile) && str_contains(file_get_contents($configFile) ?: '', "[{$remoteName}]");
        $execActivo  = function_exists('exec') && !in_array('exec', array_map('trim', explode(',', ini_get('disable_functions'))));
        $bin         = $execActivo ? $this->rcloneBin() : '(exec deshabilitado)';

        return [
            'ultimo'       => $ultimo,
            'log_lines'    => $logLines,
            'hay_error'    => $hayError,
            'configurado'  => $configOk,
            'debug' => [
                'bin'         => $bin ?: 'NO ENCONTRADO',
                'config_file' => $configFile ?: 'NO ENCONTRADO',
                'config_ok'   => $configOk,
                'home'        => getenv('HOME') ?: '(vacío)',
                'exec_ok'     => $execActivo,
            ],
        ];
    }

    private function rcloneBin(): string
    {
        // open_basedir bloquea file_exists() fuera de /var/www,
        // pero shell_exec() sí puede ejecutar binarios del sistema
        foreach (['/usr/bin/rclone', '/usr/local/bin/rclone', '/snap/bin/rclone'] as $r) {
            $test = shell_exec("{$r} version 2>/dev/null");
            if (!empty($test) && str_contains($test, 'rclone')) return $r;
        }
        // Último intento: dejar que el shell lo encuentre por PATH
        $path = trim((string) shell_exec('which rclone 2>/dev/null'));
        if (!empty($path) && str_starts_with($path, '/')) {
            $test = shell_exec("{$path} version 2>/dev/null");
            if (!empty($test)) return $path;
        }
        return '';
    }

    private function rcloneConfigFile(): string
    {
        // Primero busca en storage/ (copiado por el usuario una vez)
        $enStorage = storage_path('rclone.conf');
        if (file_exists($enStorage)) return $enStorage;

        // Luego intenta las rutas del sistema
        $candidatos = [
            '/home/dody_ortega/.config/rclone/rclone.conf',
            getenv('HOME') . '/.config/rclone/rclone.conf',
            '/root/.config/rclone/rclone.conf',
        ];
        foreach ($candidatos as $f) {
            if (!empty($f) && file_exists($f)) return $f;
        }
        return '';
    }

    private function rcloneConfigurado(): bool
    {
        // Basar detección solo en el config — shell_exec/exec pueden estar bloqueados
        $configFile = $this->rcloneConfigFile();
        if (empty($configFile)) return false;

        $remoteName = explode(':', self::RCLONE_REMOTE)[0];
        $contenido  = file_get_contents($configFile);

        return str_contains($contenido, "[{$remoteName}]");
    }
}
