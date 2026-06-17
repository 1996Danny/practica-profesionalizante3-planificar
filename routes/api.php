<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PlanificacionController;
use App\Http\Controllers\Api\PlanificacionAnualController;
use App\Http\Controllers\Api\PlanificacionDiariaController;

// 1. RUTAS PÚBLICAS
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 2. RUTAS PROTEGIDAS (Requieren Token)
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // --- Gestión de Usuarios y Roles (Solo Admin y Director) ---
    Route::put('/users/{id}/role', [UserController::class, 'assignRole'])
        ->middleware('role:admin,director');

    // --- Dashboard Unificado (Test 9) ---
    Route::get('/planificaciones', [PlanificacionController::class, 'index'])
        ->middleware('role:admin,director,docente');

    Route::get('/planificaciones/{id}', [PlanificacionController::class, 'show'])
        ->middleware('role:admin,director,docente');

    // --- Módulo de Planificaciones Anuales ---
    Route::prefix('planificaciones/anuales')->group(function () {
        // Consultas generales
        Route::get('/', [PlanificacionAnualController::class, 'index']);
        Route::get('/{id}', [PlanificacionAnualController::class, 'show']);

        // Docente: Crear y Actualizar
        Route::post('/', [PlanificacionAnualController::class, 'store'])->middleware('role:docente');
        Route::put('/{id}', [PlanificacionAnualController::class, 'update'])->middleware('role:docente');

        // Admin: Gestión de borrado y recuperación
        Route::middleware('role:admin')->group(function () {
            Route::delete('/{id}', [PlanificacionAnualController::class, 'destroy']);
            Route::get('/gestion/papelera', [PlanificacionAnualController::class, 'trashed']);
            Route::post('/{id}/restore', [PlanificacionAnualController::class, 'restore']);
        });
    });

    // --- Módulo de Planificaciones Diarias ---
    Route::prefix('planificaciones/diarias')->group(function () {
        Route::get('/', [PlanificacionDiariaController::class, 'index']);
        Route::get('/{id}', [PlanificacionDiariaController::class, 'show']);

        // Docente: Crear y Actualizar Diarias
        Route::post('/', [PlanificacionDiariaController::class, 'store'])->middleware('role:docente');
        Route::put('/{id}', [PlanificacionDiariaController::class, 'update'])->middleware('role:docente');

        // Admin: Borrado de Diarias
        Route::delete('/{id}', [PlanificacionDiariaController::class, 'destroy'])->middleware('role:admin');
    });
}); // Fin del middleware auth:sanctum
