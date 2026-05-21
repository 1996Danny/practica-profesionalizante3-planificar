<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DocenteController;
use App\Http\Controllers\Api\DirectorController;
use App\Http\Controllers\Api\PlanificacionAnualController;
use App\Http\Controllers\Api\PlanificacionDiariaController;

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
