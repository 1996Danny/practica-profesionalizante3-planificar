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
    // DASHBOARD UNIFICADO
    // ──────────────────────────────────────────
    Route::get('/planificaciones', [PlanificacionController::class, 'index'])
        ->middleware('role:admin,director,docente');
    Route::get('/planificaciones/{id}', [PlanificacionController::class, 'show'])
        ->middleware('role:admin,director,docente');

    // ──────────────────────────────────────────
    // PLANIFICACIONES ANUALES
    // ──────────────────────────────────────────
    Route::prefix('planificaciones/anuales')->group(function () {

        Route::get('/',     [PlanificacionAnualController::class, 'index']);
        Route::get('/{id}', [PlanificacionAnualController::class, 'show']);

        Route::post('/',     [PlanificacionAnualController::class, 'store'])
            ->middleware('role:docente');
        Route::put('/{id}',  [PlanificacionAnualController::class, 'update'])
            ->middleware('role:docente');

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

        Route::get('/',     [PlanificacionDiariaController::class, 'index']);
        Route::get('/{id}', [PlanificacionDiariaController::class, 'show']);

        Route::post('/',    [PlanificacionDiariaController::class, 'store'])
            ->middleware('role:docente');
        Route::put('/{id}', [PlanificacionDiariaController::class, 'update'])
            ->middleware('role:docente');

        Route::delete('/{id}', [PlanificacionDiariaController::class, 'destroy'])
            ->middleware('role:admin');
    });

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
