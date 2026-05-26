<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DocenteController;
use App\Http\Controllers\Api\DirectorController;
use App\Http\Controllers\Api\PlanificacionAnualController;
use App\Http\Controllers\Api\PlanificacionDiariaController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
// use App\Http\Controllers\API\PlanificacionController;

// Rutas Públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// Rutas Protegidas por Sanctum
Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);

    // --- GESTIÓN DE USUARIOS Y ROLES ---
    // Solo Admin y Director pueden asignar roles 
    Route::put('/users/{id}/role', [UserController::class, 'assignRole'])
        ->middleware('role:admin,director');

    // El admin, director y docente pueden ver (Index / Show)
    Route::get('/planificaciones', [PlanificacionController::class, 'index'])
        ->middleware('role:admin,director,docente');
    Route::get('/planificaciones/{id}', [PlanificacionController::class, 'show'])
        ->middleware('role:admin,director,docente');

    // El Docente es el único que puede Modificar/Crear/Eliminar sus planificaciones
    Route::post('/planificaciones', [PlanificacionController::class, 'store'])
        ->middleware('role:docente');
    Route::put('/planificaciones/{id}', [PlanificacionController::class, 'update'])
        ->middleware('role:docente');
    Route::delete('/planificaciones/{id}', [PlanificacionController::class, 'destroy'])
        ->middleware('role:docente');
});

/*
|--------------------------------------------------------------------------
| RUTAS DE DOCENTES
|--------------------------------------------------------------------------
*/

Route::prefix('docentes')->group(function () {
    Route::get('/',                    [DocenteController::class, 'index']);
    Route::get('/buscar',              [DocenteController::class, 'buscar']);
    Route::get('/{id}',                [DocenteController::class, 'show']);
    Route::get('/{id}/cursados',       [DocenteController::class, 'cursados']);
});

/*
|--------------------------------------------------------------------------
| RUTAS DE DIRECTORES
|--------------------------------------------------------------------------
*/
Route::prefix('directores')->group(function () {
    Route::get('/',                                [DirectorController::class, 'index']);
    Route::get('/activo',                          [DirectorController::class, 'activo']);
    Route::get('/supervision/docentes',            [DirectorController::class, 'docentes']);
    Route::get('/planificaciones/pendientes',      [DirectorController::class, 'planificacionesPendientes']);
    Route::get('/estadisticas/estados',            [DirectorController::class, 'resumenEstados']);
    Route::get('/{id}',                            [DirectorController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| RUTAS DE PLANIFICACIONES ANUALES
|--------------------------------------------------------------------------
*/
Route::prefix('planificaciones/anuales')->group(function () {
    Route::get('/',                        [PlanificacionAnualController::class, 'index']);
    Route::post('/',                       [PlanificacionAnualController::class, 'store']);
    Route::get('/docente/{personaId}',     [PlanificacionAnualController::class, 'porDocente']);
    Route::get('/estado/{estado}',         [PlanificacionAnualController::class, 'porEstado']);
    Route::get('/{id}',                    [PlanificacionAnualController::class, 'show']);
    Route::put('/{id}',                    [PlanificacionAnualController::class, 'update']);
    Route::delete('/{id}',                 [PlanificacionAnualController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| RUTAS DE PLANIFICACIONES DIARIAS
|--------------------------------------------------------------------------
*/
Route::prefix('planificaciones/diarias')->group(function () {
    Route::get('/',                        [PlanificacionDiariaController::class, 'index']);
    Route::post('/',                       [PlanificacionDiariaController::class, 'store']);
    Route::get('/docente/{personaId}',     [PlanificacionDiariaController::class, 'porDocente']);
    Route::get('/estado/{estado}',         [PlanificacionDiariaController::class, 'porEstado']);
    Route::get('/{id}',                    [PlanificacionDiariaController::class, 'show']);
    Route::put('/{id}',                    [PlanificacionDiariaController::class, 'update']);
    Route::delete('/{id}',                 [PlanificacionDiariaController::class, 'destroy']);
});
