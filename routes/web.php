<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\RecuperarContrasenaController;

// Admin — alias para evitar colisión con módulos del mismo nombre
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\EspecialidadController;
use App\Http\Controllers\Admin\MedicoController       as AdminMedicoController;
use App\Http\Controllers\Admin\ParamedicoController   as AdminParamedicoController;
use App\Http\Controllers\Admin\PacienteController     as AdminPacienteController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\ConfiguracionController;
use App\Http\Controllers\Admin\AuditoriaController;
use App\Http\Controllers\Admin\PermisosController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\ExamenAdminController;
use App\Http\Controllers\Admin\CredencialesController;

// Médico
use App\Http\Controllers\Medico\MedicoController;
use App\Http\Controllers\Medico\ExamenMedicoController;
use App\Http\Controllers\Medico\PacienteMedicoController;
use App\Http\Controllers\Medico\AgendaController;
use App\Http\Controllers\Medico\CitaController;

// Paramédico
use App\Http\Controllers\Paramedico\ParamedicoController;
use App\Http\Controllers\Paramedico\NotificacionController;  // está en Paramedico\, no en Medico\

// Paciente
use App\Http\Controllers\Paciente\PacienteController;
use App\Http\Controllers\Paciente\ExamenController    as PacienteExamenController;

// Laboratorio y Farmacia
use App\Http\Controllers\Laboratorio\LaboratorioController;
use App\Http\Controllers\Farmacia\FarmaciaController;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/
Route::get('/',             [PublicController::class, 'inicio'])       ->name('inicio');
Route::get('/nosotros',     [PublicController::class, 'nosotros'])     ->name('nosotros');
Route::get('/telemedicina', [PublicController::class, 'telemedicina']) ->name('telemedicina');
Route::get('/contacto',     [PublicController::class, 'contacto'])     ->name('contacto');
Route::get('/terminos',     [PublicController::class, 'terminos'])     ->name('terminos');

/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/
Route::get('/login',    [LoginController::class,   'showLogin'])   ->name('login');
Route::post('/login',   [LoginController::class,   'login'])        ->name('login.post');
Route::post('/logout',  [LoginController::class,   'logout'])       ->name('logout');
Route::get('/registro', [RegisterController::class, 'showRegister'])->name('registro');
Route::post('/registro',[RegisterController::class, 'register'])    ->name('registro.post');

// Recuperación de contraseña (OTP por WhatsApp, sin tabla nueva)
Route::get('/recuperar-contrasena',           [RecuperarContrasenaController::class, 'showForm'])      ->name('recuperar.form');
Route::post('/recuperar-contrasena',          [RecuperarContrasenaController::class, 'enviarOtp'])     ->name('recuperar.enviar');
Route::get('/recuperar-contrasena/verificar', [RecuperarContrasenaController::class, 'showVerificar']) ->name('recuperar.verificar');
Route::post('/recuperar-contrasena/verificar',[RecuperarContrasenaController::class, 'verificarOtp'])  ->name('recuperar.verificar.post');
Route::get('/recuperar-contrasena/nueva',     [RecuperarContrasenaController::class, 'showNueva'])     ->name('recuperar.nueva');
Route::post('/recuperar-contrasena/nueva',    [RecuperarContrasenaController::class, 'guardarNueva'])  ->name('recuperar.guardar');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Especialidades
    Route::get('/especialidades',         [EspecialidadController::class, 'index'])  ->name('especialidades.index');
    Route::post('/especialidades',        [EspecialidadController::class, 'store'])  ->name('especialidades.store');
    Route::put('/especialidades/{id}',    [EspecialidadController::class, 'update']) ->name('especialidades.update');
    Route::delete('/especialidades/{id}', [EspecialidadController::class, 'destroy'])->name('especialidades.destroy');

    // Médicos
    Route::get('/medicos',                 [AdminMedicoController::class, 'index']) ->name('medicos.index');
    Route::get('/medicos/crear',           [AdminMedicoController::class, 'create'])->name('medicos.create');
    Route::post('/medicos',                [AdminMedicoController::class, 'store']) ->name('medicos.store');
    Route::get('/medicos/{cedula}',        [AdminMedicoController::class, 'show'])  ->name('medicos.show');
    Route::get('/medicos/{cedula}/editar', [AdminMedicoController::class, 'edit'])  ->name('medicos.edit');
    Route::put('/medicos/{cedula}',        [AdminMedicoController::class, 'update'])->name('medicos.update');
    Route::delete('/medicos/{cedula}',     [AdminMedicoController::class, 'destroy'])    ->name('medicos.destroy');
    Route::post('/medicos/{cedula}/toggle', [AdminMedicoController::class, 'toggleEstado'])->name('medicos.toggle-estado');

    // Paramédicos
    Route::get('/paramedicos',                 [AdminParamedicoController::class, 'index']) ->name('paramedicos.index');
    Route::get('/paramedicos/crear',           [AdminParamedicoController::class, 'create'])->name('paramedicos.create');
    Route::post('/paramedicos',                [AdminParamedicoController::class, 'store']) ->name('paramedicos.store');
    Route::get('/paramedicos/{cedula}/editar', [AdminParamedicoController::class, 'edit'])  ->name('paramedicos.edit');
    Route::put('/paramedicos/{cedula}',        [AdminParamedicoController::class, 'update'])->name('paramedicos.update');
    Route::delete('/paramedicos/{cedula}',     [AdminParamedicoController::class, 'destroy'])->name('paramedicos.destroy');

    // Pacientes — estáticas ANTES de {cedula}
    Route::get('/pacientes',                                    [AdminPacienteController::class, 'index'])                ->name('pacientes.index');
    Route::post('/pacientes/reagendar',                         [AdminPacienteController::class, 'reagendar'])            ->name('pacientes.reagendar');
    Route::get('/pacientes/ajax/medicos-por-especialidad/{id}', [AdminPacienteController::class, 'medicosPorEspecialidad'])->name('pacientes.ajax.medicos');
    Route::get('/pacientes/ajax/agenda-disponible',             [AdminPacienteController::class, 'agendaDisponible'])     ->name('pacientes.ajax.agenda');
    Route::get('/pacientes/ajax/dias-disponibles',              [AdminPacienteController::class, 'diasDisponibles'])      ->name('pacientes.ajax.dias');
    Route::get('/pacientes/{cedula}',                           [AdminPacienteController::class, 'show'])                 ->name('pacientes.show');
    Route::get('/pacientes/{cedula}/editar',                    [AdminPacienteController::class, 'edit'])                 ->name('pacientes.edit');
    Route::put('/pacientes/{cedula}',                           [AdminPacienteController::class, 'update'])               ->name('pacientes.update');
    Route::post('/pacientes/{cedula}/toggle',                   [AdminPacienteController::class, 'toggleEstado'])         ->name('pacientes.toggle-estado');
    Route::delete('/pacientes/{cedula}',                        [AdminPacienteController::class, 'destroy'])              ->name('pacientes.destroy');

    // Reportes
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');

    // Configuración
    Route::get('/configuracion',             [ConfiguracionController::class, 'index'])          ->name('configuracion.index');
    Route::put('/configuracion/{grupo}',     [ConfiguracionController::class, 'update'])         ->name('configuracion.update');
    Route::post('/configuracion/cache',      [ConfiguracionController::class, 'limpiarCache'])   ->name('configuracion.cache');

    // WhatsApp WAHA
    Route::get('/whatsapp/status',           [ConfiguracionController::class, 'wahaStatus'])       ->name('whatsapp.status');
    Route::get('/whatsapp/qr',               [ConfiguracionController::class, 'wahaQr'])           ->name('whatsapp.qr');
    Route::post('/whatsapp/logout',          [ConfiguracionController::class, 'wahaLogout'])       ->name('whatsapp.logout');
    Route::post('/whatsapp/recordatorios',   [ConfiguracionController::class, 'enviarRecordatorios'])->name('whatsapp.recordatorios');

    // Auditoría
    Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');

    // Permisos
    Route::get('/permisos',              [PermisosController::class, 'index']) ->name('permisos.index');
    Route::put('/permisos/{cedula}',     [PermisosController::class, 'update'])->name('permisos.update');

    // Backup
    Route::get('/backup',           [BackupController::class, 'index'])     ->name('backup.index');
    Route::get('/backup/descargar', [BackupController::class, 'descargar']) ->name('backup.descargar');
    Route::post('/backup/restaurar',[BackupController::class, 'restaurar']) ->name('backup.restaurar');

    // Documentos de pacientes
    Route::get('/examenes',                              [ExamenAdminController::class, 'index'])     ->name('examenes.index');
    Route::get('/examenes/{cedula}/{id}/descargar',      [ExamenAdminController::class, 'descargar']) ->name('examenes.descargar');
    Route::post('/examenes/backup-drive',                [ExamenAdminController::class, 'backupDrive'])    ->name('examenes.backup-drive');
    Route::get('/examenes/backup-drive/log',             [ExamenAdminController::class, 'backupLog'])     ->name('examenes.backup-log');
    Route::post('/examenes/restaurar-drive',             [ExamenAdminController::class, 'restaurarDrive']) ->name('examenes.restaurar-drive');
    Route::get('/examenes/restaurar-drive/log',          [ExamenAdminController::class, 'restaurarLog'])   ->name('examenes.restaurar-log');
    Route::get('/examenes/restaurar-drive/estado',       [ExamenAdminController::class, 'restaurarEstado'])->name('examenes.restaurar-estado');
    Route::get('/examenes/cambiar-drive',                [ExamenAdminController::class, 'cambiarDrive'])  ->name('examenes.cambiar-drive');
    Route::get('/examenes/cambiar-drive/listo',          [ExamenAdminController::class, 'guardarDrive'])  ->name('examenes.guardar-drive');

    // Credenciales (lab/farmacia)
    Route::get('/credenciales',                          [CredencialesController::class, 'index'])        ->name('credenciales.index');
    Route::post('/credenciales/generar',                 [CredencialesController::class, 'generar'])      ->name('credenciales.generar');
    Route::post('/credenciales/{cedula}/revocar',        [CredencialesController::class, 'revocar'])      ->name('credenciales.revocar');
    Route::post('/credenciales/{cedula}/reset-password', [CredencialesController::class, 'resetPassword'])->name('credenciales.reset');

    // Perfil
    Route::get('/perfil', [AdminController::class, 'perfil'])          ->name('perfil.index');
    Route::put('/perfil', [AdminController::class, 'actualizarPerfil'])->name('perfil.actualizar');
});

/*
|--------------------------------------------------------------------------
| Médico
|--------------------------------------------------------------------------
*/
Route::middleware('role:medico')->prefix('medico')->name('medico.')->group(function () {
    Route::get('/',             [MedicoController::class, 'dashboard'])    ->name('dashboard');
    Route::get('/dashboard/data', [MedicoController::class, 'dashboardData'])->name('dashboard.data');

    // Notificaciones AJAX
    Route::get('/notif',                [NotificacionController::class, 'index'])           ->name('notif.index');
    Route::post('/notif/leida',         [NotificacionController::class, 'marcarLeida'])     ->name('notif.leida');
    Route::post('/notif/todas',         [NotificacionController::class, 'marcarTodas'])     ->name('notif.todas');
    Route::post('/emergencia/atender',  [NotificacionController::class, 'aceptarEmergencia'])->name('emergencia.atender');

    // Mis Pacientes — /crear ANTES de /{cedula}
    Route::get('/pacientes',          [PacienteMedicoController::class, 'index']) ->name('pacientes.index');
    Route::get('/pacientes/crear',    [PacienteMedicoController::class, 'create'])->name('pacientes.create');
    Route::post('/pacientes',         [PacienteMedicoController::class, 'store']) ->name('pacientes.store');
    Route::get('/pacientes/{cedula}', [PacienteMedicoController::class, 'show'])  ->name('pacientes.show');

    // Agenda — estáticas ANTES de /{id}
    Route::get('/agenda',                  [AgendaController::class, 'index'])       ->name('agenda.index');
    Route::get('/agenda/gestionar',        [AgendaController::class, 'gestionar'])   ->name('agenda.gestionar');
    Route::post('/agenda/generar',         [AgendaController::class, 'generarSlots'])->name('agenda.generar');
    Route::delete('/agenda/{id}/eliminar', [AgendaController::class, 'eliminarSlot'])->name('agenda.eliminar');

    // Citas — estáticas ANTES de rutas con parámetros
    Route::get('/historial',          [CitaController::class, 'historial'])           ->name('historial.index');
    Route::get('/citas',              [CitaController::class, 'index'])                ->name('citas.index');
    Route::get('/citas/nueva',        [CitaController::class, 'nuevaCita'])            ->name('citas.nueva');
    Route::post('/citas',             [CitaController::class, 'guardarCita'])          ->name('citas.guardar');
    Route::get('/citas/agendar',      [CitaController::class, 'agendarPaciente'])      ->name('citas.agendar');
    Route::post('/citas/agendar',     [CitaController::class, 'guardarAgendaPaciente'])->name('citas.agendar.guardar');
    // AJAX disponibilidad del médico logueado
    Route::get('/ajax/dias',          [CitaController::class, 'misDiasDisponibles'])   ->name('ajax.dias');
    Route::get('/ajax/horarios',      [CitaController::class, 'misHorarios'])          ->name('ajax.horarios');

    // Perfil
    Route::get('/perfil', [MedicoController::class, 'perfil'])          ->name('perfil.index');
    Route::put('/perfil', [MedicoController::class, 'actualizarPerfil'])->name('perfil.actualizar');

    Route::get('/video', [VideoController::class, 'index'])->name('video.index');

    // Documentos de pacientes
    Route::get('/pacientes/{cedula}/examenes',               [ExamenMedicoController::class, 'index'])    ->name('examenes.index');
    Route::get('/pacientes/{cedula}/examenes/{id}/descargar',[ExamenMedicoController::class, 'descargar'])->name('examenes.descargar');
});

/*
|--------------------------------------------------------------------------
| Paramédico
|--------------------------------------------------------------------------
*/
Route::middleware('role:paramedico')->prefix('paramedico')->name('paramedico.')->group(function () {
    Route::get('/', [ParamedicoController::class, 'dashboard'])->name('dashboard');

    // Notificaciones
    Route::get('/notif',                [NotificacionController::class, 'index'])            ->name('notif.index');
    Route::post('/notif/leida',         [NotificacionController::class, 'marcarLeida'])      ->name('notif.leida');
    Route::post('/notif/todas',         [NotificacionController::class, 'marcarTodas'])      ->name('notif.todas');
    Route::post('/emergencia/atender',  [NotificacionController::class, 'aceptarEmergencia'])->name('emergencia.atender');

    // Pacientes — /crear ANTES de /{cedula}
    Route::get('/pacientes',          [ParamedicoController::class, 'pacientes'])      ->name('pacientes.index');
    Route::get('/pacientes/crear',    [ParamedicoController::class, 'crearPaciente'])  ->name('pacientes.crear');
    Route::post('/pacientes',         [ParamedicoController::class, 'guardarPaciente'])->name('pacientes.guardar');
    Route::get('/pacientes/{cedula}', [ParamedicoController::class, 'verPaciente'])    ->name('pacientes.ver');

    // Primera Atención
    Route::get('/atencion',  [ParamedicoController::class, 'primeraAtencion']) ->name('atencion.index');
    Route::post('/atencion', [ParamedicoController::class, 'guardarAtencion']) ->name('atencion.guardar');

    // Citas — /agendar ANTES de parámetros
    Route::get('/citas/agendar', [ParamedicoController::class, 'agendarCita']) ->name('citas.agendar');
    Route::post('/citas',        [ParamedicoController::class, 'guardarCita']) ->name('citas.guardar');

    // Emergencias
    Route::get('/emergencias',          [ParamedicoController::class, 'emergencias'])      ->name('emergencias.index');
    Route::post('/emergencias/atender', [ParamedicoController::class, 'atenderEmergencia'])->name('emergencias.atender');

    // Perfil
    Route::get('/perfil', [ParamedicoController::class, 'perfil'])          ->name('perfil.index');
    Route::put('/perfil', [ParamedicoController::class, 'actualizarPerfil'])->name('perfil.actualizar');

    // AJAX
    Route::get('/ajax/medicos/{id}', [ParamedicoController::class, 'medicosPorEspecialidad'])->name('ajax.medicos');
    Route::get('/ajax/agenda',       [ParamedicoController::class, 'agendaDisponible'])      ->name('ajax.agenda');

    Route::get('/video', [VideoController::class, 'index'])->name('video.index');
});

/*
|--------------------------------------------------------------------------
| Paciente
|--------------------------------------------------------------------------
*/
Route::middleware("role:paciente")->prefix('paciente')->name('paciente.')->group(function () {

    // 6.1 Dashboard
    Route::get('/', [PacienteController::class, 'dashboard'])->name('dashboard');

    // AJAX — siempre ANTES de rutas con parámetros
    Route::get('/ajax/horarios', [PacienteController::class, 'horariosDisponibles'])->name('ajax.horarios');

    // 6.3 Agendar y cancelar — ANTES de /citas/{id}
    Route::get('/citas/agendar',   [PacienteController::class, 'agendarCita']) ->name('citas.agendar');
    Route::post('/citas/cancelar', [PacienteController::class, 'cancelarCita'])->name('citas.cancelar');
    Route::post('/citas',          [PacienteController::class, 'guardarCita']) ->name('citas.guardar');

    // 6.2 Listado y detalle
    Route::get('/citas',      [PacienteController::class, 'misCitas'])   ->name('citas');
    Route::get('/citas/{id}', [PacienteController::class, 'detalleCita'])->name('citas.detalle');

    // 6.4 – 6.7
    Route::get('/historial',     [PacienteController::class, 'historial'])    ->name('historial');
    Route::get('/recetas',       [PacienteController::class, 'recetas'])      ->name('recetas');
    Route::get('/documentos',    [PacienteController::class, 'documentos'])   ->name('documentos');

    // Exámenes / archivos subidos por el paciente
    Route::post('/documentos/subir',            [PacienteExamenController::class, 'subir'])    ->name('examenes.subir');
    Route::get('/documentos/{id}/descargar',    [PacienteExamenController::class, 'descargar'])->name('examenes.descargar');
    Route::delete('/documentos/{id}',           [PacienteExamenController::class, 'eliminar']) ->name('examenes.eliminar');
    Route::get('/teleconsultas', [PacienteController::class, 'teleconsultas'])->name('teleconsultas');

    // 6.8 Perfil
    Route::get('/perfil', [PacienteController::class, 'perfil'])          ->name('perfil');
    Route::put('/perfil', [PacienteController::class, 'actualizarPerfil'])->name('perfil.actualizar');

    // 6.9 Emergencia AJAX
    Route::post('/emergencia', [PacienteController::class, 'solicitarEmergencia'])->name('emergencia');
});

// ── Laboratorio ─────────────────────────────────────────────────
Route::middleware('role:laboratorio')->prefix('laboratorio')->name('laboratorio.')->group(function () {
    Route::get('/',       [LaboratorioController::class, 'dashboard'])->name('dashboard');
    Route::get('/buscar', [LaboratorioController::class, 'buscar'])   ->name('buscar');
});

// ── Farmacia ─────────────────────────────────────────────────────
Route::middleware('role:farmacia')->prefix('farmacia')->name('farmacia.')->group(function () {
    Route::get('/',       [FarmaciaController::class, 'dashboard'])->name('dashboard');
    Route::get('/buscar', [FarmaciaController::class, 'buscar'])   ->name('buscar');
});

// ── Video WebRTC — accesible a todos los roles autenticados ───────
Route::prefix('video')->name('video.')->group(function () {
    Route::get('/sala/{room}',   [VideoController::class, 'sala'])      ->name('sala');
    Route::post('/iniciar',      [VideoController::class, 'iniciar'])   ->name('iniciar');
    Route::post('/signal',       [VideoController::class, 'signal'])    ->name('signal');
    Route::post('/candidate',    [VideoController::class, 'candidate']) ->name('candidate');
    Route::get('/poll',          [VideoController::class, 'poll'])      ->name('poll');
    Route::get('/entrante',      [VideoController::class, 'entrante'])  ->name('entrante');
    Route::post('/mensaje',      [VideoController::class, 'mensaje'])   ->name('mensaje');
    Route::post('/terminar',     [VideoController::class, 'terminar'])  ->name('terminar');
});