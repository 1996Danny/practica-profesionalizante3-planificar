<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\DirectorController;
use App\Http\Controllers\Api\DocenteController;
use App\Http\Controllers\Api\PlanificacionController;
use App\Http\Controllers\Api\PlanificacionAnualController;
use App\Http\Controllers\Api\PlanificacionDiariaController;
use App\Http\Controllers\Api\EstadoAnualController;
use App\Http\Controllers\Api\EstadoDiariaController;

// =============================================
// RUTAS PÚBLICAS
// =============================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// =============================================
// RUTAS PROTEGIDAS
// =============================================
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // ──────────────────────────────────────────
    // GESTIÓN DE ROLES
    // ──────────────────────────────────────────
    Route::put('/users/{id}/role', [UserController::class, 'assignRole'])
        ->middleware('role:admin,director');

    // ──────────────────────────────────────────
    // MÓDULO ADMIN
    // ──────────────────────────────────────────
    Route::prefix('admin')
        ->middleware('role:admin')
        ->group(function () {
            Route::get('/dashboard',          [AdminController::class, 'dashboard']);
            Route::get('/usuarios',           [AdminController::class, 'usuarios']);
            Route::get('/usuarios/{id}',      [AdminController::class, 'showUsuario']);
            Route::delete('/usuarios/{id}',   [AdminController::class, 'destroyUsuario']);
            Route::get('/personas',           [AdminController::class, 'personas']);
        });

    // ──────────────────────────────────────────
    // MÓDULO DIRECTOR
    // ──────────────────────────────────────────
    Route::prefix('directores')
        ->middleware('role:admin,director')
        ->group(function () {
            Route::get('/',                          [DirectorController::class, 'index']);
            Route::get('/activo',                    [DirectorController::class, 'activo']);
            Route::get('/supervision/docentes',      [DirectorController::class, 'docentes']);
            Route::get('/planificaciones/pendientes', [DirectorController::class, 'planificacionesPendientes']);
            Route::get('/estadisticas/estados',      [DirectorController::class, 'resumenEstados']);
            Route::get('/{id}',                      [DirectorController::class, 'show']);
        });

    // ──────────────────────────────────────────
    // MÓDULO DOCENTE
    // ──────────────────────────────────────────
    Route::prefix('docentes')
        ->middleware('role:admin,director,docente')
        ->group(function () {
            Route::get('/',              [DocenteController::class, 'index']);
            Route::get('/buscar',        [DocenteController::class, 'buscar']);
            Route::get('/{id}',          [DocenteController::class, 'show']);
            Route::get('/{id}/cursados', [DocenteController::class, 'cursados']);
        });

   // ──────────────────────────────────────────
    // PLANIFICACIONES ANUALES (Rutas estáticas/específicas primero 🟢)
    // ──────────────────────────────────────────
    Route::prefix('planificaciones/anuales')->group(function () {
        Route::get('', [PlanificacionAnualController::class, 'index']); // Sin '/' al inicio
        Route::post('', [PlanificacionAnualController::class, 'store'])->middleware('role:docente');
        Route::put('/{id}', [PlanificacionAnualController::class, 'update'])->middleware('role:docente');
        Route::get('/{id}', [PlanificacionAnualController::class, 'show']);

        Route::middleware('role:admin')->group(function () {
            Route::delete('/{id}',          [PlanificacionAnualController::class, 'destroy']);
            Route::get('/gestion/papelera', [PlanificacionAnualController::class, 'trashed']);
            Route::post('/{id}/restore',    [PlanificacionAnualController::class, 'restore']);
        });
    });

    // ──────────────────────────────────────────
    // PLANIFICACIONES DIARIAS
    // ──────────────────────────────────────────
   Route::prefix('planificaciones/diarias')->group(function () {
        Route::get('', [PlanificacionDiariaController::class, 'index']);
        Route::get('/{id}', [PlanificacionDiariaController::class, 'show']);
        Route::post('', [PlanificacionDiariaController::class, 'store'])->middleware('role:docente');
        Route::put('/{id}', [PlanificacionDiariaController::class, 'update'])->middleware('role:docente');
        Route::delete('/{id}', [PlanificacionDiariaController::class, 'destroy'])->middleware('role:admin');
    });

    // ──────────────────────────────────────────
    // DASHBOARD UNIFICADO (Rutas dinámicas con {id} al final de todo 🛑)
    // ──────────────────────────────────────────
Route::get('/planificaciones', [PlanificacionController::class, 'index'])
        ->middleware('role:admin,director,docente');

    Route::get('/planificaciones/{id}', [PlanificacionController::class, 'show'])
        ->middleware('role:admin,director,docente');

    // ──────────────────────────────────────────
    // ESTADOS - ANUALES
    // ──────────────────────────────────────────
    Route::prefix('planificaciones/anuales/{id}/estados')->group(function () {

        Route::get('/', [EstadoAnualController::class, 'index'])
            ->middleware('role:admin,director,docente');

        Route::post('/enviar-revision', [EstadoAnualController::class, 'enviarRevision'])
            ->middleware('role:docente');

        Route::post('/aprobar',  [EstadoAnualController::class, 'aprobar'])
            ->middleware('role:admin,director');
        Route::post('/rechazar', [EstadoAnualController::class, 'rechazar'])
            ->middleware('role:admin,director');
    });

    // ──────────────────────────────────────────
    // ESTADOS - DIARIAS
    // ──────────────────────────────────────────
    Route::prefix('planificaciones/diarias/{id}/estados')->group(function () {

        Route::get('/', [EstadoDiariaController::class, 'index'])
            ->middleware('role:admin,director,docente');

        Route::post('/enviar-revision', [EstadoDiariaController::class, 'enviarRevision'])
            ->middleware('role:docente');

        Route::post('/aprobar',  [EstadoDiariaController::class, 'aprobar'])
            ->middleware('role:admin,director');
        Route::post('/rechazar', [EstadoDiariaController::class, 'rechazar'])
            ->middleware('role:admin,director');
    });
}); // Fin auth:sanctum


use App\Http\Controllers\EstadosDiariaController;       // Importa el controlador de EstadosDiariaController.
// use App\Http\Controllers\PlanificacionDiariaController;       // Importa el controlador de PlanificacionDiariaController.
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Route;
use App\Http\Controllers\areasController;
use App\Http\Controllers\estados_anualController;


use App\Http\Controllers\CursadosController;


Route::get('cursados', [CursadosController::class, 'index']);
Route::get('cursados/{id}', [CursadosController::class, 'show']);
Route::put('cursados/{id}', [CursadosController::class, 'update']);
Route::delete('cursados/{id}', [CursadosController::class, 'destroy']);
Route::post('cursados', [CursadosController::class, 'store']);
use App\Http\Controllers\PersonaCargoCursadoController;

Route::get('persona-cargo-cursado', [PersonaCargoCursadoController::class, 'index']);
Route::get('persona-cargo-cursado/{id}', [PersonaCargoCursadoController::class, 'show']);
Route::put('persona-cargo-cursado/{id}', [PersonaCargoCursadoController::class, 'update']);
Route::delete('persona-cargo-cursado/{id}', [PersonaCargoCursadoController::class, 'destroy']);
Route::post('persona-cargo-cursado', [PersonaCargoCursadoController::class, 'store']);

use App\Http\Controllers\PersonasController;
use App\Http\Controllers\CursosController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('areas', areasController::class);
Route::apiResource('estados_anual',estados_anualController::class);


//                          RUTAS estados_diaria

// Esta ruta muestra todos los estados de las planificaciones diarias de la base de datos en formato JSON.
Route::get('/estados_diaria', [EstadosDiariaController::class, 'index']);

// Esta ruta muestra uno de los estados de la planificación diaria de la base de datos en formato JSON según su id.
Route::get('/estados_diaria/{id}', [EstadosDiariaController::class, 'show']);

// Esta ruta agrega un estado de la planificación diaria a la base de datos en formato JSON.
Route::post('/estados_diaria', [EstadosDiariaController::class, 'store']);

// Esta ruta borra un estado de la planificacion diaria de la base de datos en formato JSON según su id.
Route::delete('/estados_diaria/{id}', [EstadosDiariaController::class, 'destroy']);

// Esta ruta actualiza un estado de la planificacion diaria de la base de datos en formato JSON según su id.

Route::put('/estados_diaria/{id}', [EstadosDiariaController::class, 'update']);
Route::get('/personas',[PersonasController::class, 'index']);
Route::get('/personas/{id}',[PersonasController::class, 'show']);
Route::post('/personas', [PersonasController::class, 'store']);
Route::put('/personas/{id}', [PersonasController::class, 'update']);
Route::delete('/personas/{id}', [PersonasController::class, 'destroy']);

Route::get('/cursos', [CursosController::class,'index']);
Route::get('/cursos/{id}', [CursosController::class,'show']);
Route::post('/cursos', [CursosController::class,'store']);
Route::put('/cursos/{id}', [CursosController::class,'update']);
Route::delete('/cursos/{id}', [CursosController::class,'destroy']);

use App\Http\Controllers\PersonaCargosController;
Route::get('/persona_cargos', [PersonaCargosController::class, 'index']);
Route::get('/persona_cargos/{id}', [PersonaCargosController::class, 'show']);
Route::put('/persona_cargos/{id}', [PersonaCargosController::class, 'update']);
Route::delete('/persona_cargos/{id}', [PersonaCargosController::class, 'destroy']);
Route::post('/persona_cargos', [PersonaCargosController::class, 'store']);
use App\Http\Controllers\CargosController;

Route::get('/cargos', [CargosController::class, 'index']);
Route::get('/cargos/{id}', [CargosController::class, 'show']);
Route::post('/cargos', [CargosController::class, 'store']);
Route::put('/cargos/{id}', [CargosController::class, 'update']);
Route::delete('/cargos/{id}', [CargosController::class, 'destroy']);
use App\Http\Controllers\SitRevistaController;

Route::get('/sit_revista', [SitRevistaController::class, 'index']);
Route::get('/sit_revista/{id}', [SitRevistaController::class, 'show']);
Route::post('/sit_revista', [SitRevistaController::class, 'store']);
Route::put('/sit_revista/{id}', [SitRevistaController::class, 'update']);
Route::delete('/sit_revista/{id}', [SitRevistaController::class, 'destroy']);
