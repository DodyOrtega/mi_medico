<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.configuracion.index');
    }

    // ── Genera y descarga el backup como .sql ─────────────────────
    public function descargar()
    {
        $dbName  = config('database.connections.mysql.database');
        $colName = 'Tables_in_' . $dbName;
        $tables  = DB::select('SHOW TABLES');

        $sql  = "-- ============================================================\n";
        $sql .= "-- Backup MiMedico · Generado: " . now()->format('Y-m-d H:i:s') . "\n";
        $sql .= "-- Base de datos: {$dbName}\n";
        $sql .= "-- ============================================================\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n";

        foreach ($tables as $tableRow) {
            $tabla = $tableRow->$colName;

            // Estructura — el nombre de columna varía (Create Table / Create View)
            $create     = DB::select("SHOW CREATE TABLE `{$tabla}`");
            $createCols = array_values((array) $create[0]);
            $createStmt = $createCols[1]; // siempre el segundo valor
            $sql .= "-- Tabla: {$tabla}\n";
            $sql .= "DROP TABLE IF EXISTS `{$tabla}`;\n";
            $sql .= $createStmt . ";\n\n";

            // Datos — leemos en bloques de 500 para no saturar memoria
            $offset  = 0;
            $primera = true;
            $hayDatos = false;

            while (true) {
                $filas = DB::table($tabla)->offset($offset)->limit(500)->get();
                if ($filas->isEmpty()) break;

                if ($primera) {
                    $sql .= "INSERT INTO `{$tabla}` VALUES\n";
                    $hayDatos = true;
                }

                foreach ($filas as $fila) {
                    if (!$primera) $sql .= ",\n";
                    $primera = false;

                    $valores = array_map(function ($v) {
                        if ($v === null) return 'NULL';
                        return "'" . str_replace(
                            ["\\", "'", "\n", "\r"],
                            ["\\\\", "\\'", "\\n", "\\r"],
                            (string) $v
                        ) . "'";
                    }, (array) $fila);

                    $sql .= '(' . implode(', ', $valores) . ')';
                }

                $offset += 500;
            }

            if ($hayDatos) $sql .= ";\n\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $nombre = 'mimedico_backup_' . now()->format('Y-m-d_H-i-s') . '.sql';

        Auditoria::registrar('backup_descarga', "Backup descargado: {$nombre}");

        return response($sql, 200, [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => "attachment; filename=\"{$nombre}\"",
            'Cache-Control'       => 'no-store',
        ]);
    }

    // ── Restaura la BD desde un archivo .sql subido ───────────────
    public function restaurar(Request $request)
    {
        if (!$request->hasFile('backup_file') ||
            strtolower($request->file('backup_file')->getClientOriginalExtension()) !== 'sql') {
            return back()->with('error', 'Debes subir un archivo .sql válido.');
        }

        $contenido  = file_get_contents($request->file('backup_file')->path());
        $sentencias = explode(';', $contenido);

        try {
            // DDL (DROP/CREATE TABLE) no puede revertirse en MySQL, sin transaction
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            foreach ($sentencias as $sentencia) {
                $sentencia = trim($sentencia);

                if (empty($sentencia)) continue;

                // Saltar bloques que sean solo comentarios -- o líneas vacías
                $sinComentarios = trim(preg_replace('/--[^\n]*/m', '', $sentencia));
                if (empty($sinComentarios)) continue;

                DB::statement($sentencia);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            Auditoria::registrar('backup_restaurar', 'Base de datos restaurada desde archivo .sql');
            return back()->with('exito', 'Base de datos restaurada correctamente desde el archivo.');

        } catch (\Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            return back()->with('error', 'Error al restaurar: ' . $e->getMessage());
        }
    }
}
