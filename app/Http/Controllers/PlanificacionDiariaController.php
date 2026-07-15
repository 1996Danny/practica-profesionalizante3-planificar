<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanificacionDiariaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = PlanificacionDiaria::with([
            'personaCargoCursado.personaCargo.persona',
            'personaCargoCursado.cursado.curso',
            'estados' // Si tiene estados configurados
        ]);

        // 🔐 SI ES DOCENTE: Solo ve sus planificaciones diarias
        if ($user && $user->role === 'docente') {
            $query->whereHas('personaCargoCursado.personaCargo.persona.user', function ($q) use ($user) {
                $q->where('id', $user->id);
            });
        }

        // 👁️ SI ES DIRECTOR
        if ($user && $user->role === 'director') {
            $query->whereHas('estados', function ($q) {
                $q->where('estado', '!=', 'Borrador');
            });
        }

        $planificaciones = $query->get();

        return response()->json($planificaciones);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::table('planificacion_diaria')
        -> insert(
            [
            "fecha_estimada" => $request ["fecha_estimada"] ,
            "fecha_desarrollada" => $request ["fecha_desarrollada"] ,
            "fecha_presentacion" => $request ["fecha_presentacion"] ,
            "contenidos_especificos" => $request ["contenidos_especificos"] ,
            "actividades" => $request ["actividades"] ,
            "tareas" => $request ["tareas"] ,
            "persona_cargo_cursado_id" => $request ["persona_cargo_cursado_id"] ,
            "tipo_planificacion" => $request ["tipo_planificacion"] ,
            "created_at" => now() ,
            "updated_at" => now()
            ]);

            return response()->json(['mensaje' => 'La planificacion se agregado correctamente']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return DB::table('planificacion_diaria') -> where('id', '=', $id) -> get();
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::table('planificacion_diaria')->where('id', '=', $id)
        -> update([
            "fecha_estimada" => $request ["fecha_estimada"] ,
            "fecha_desarrollada" => $request ["fecha_desarrollada"] ,
            "fecha_presentacion" => $request ["fecha_presentacion"] ,
            "contenidos_especificos" => $request ["contenidos_especificos"] ,
            "actividades" => $request ["actividades"] ,
            "tareas" => $request ["tareas"] ,
            "persona_cargo_cursado_id" => $request ["persona_cargo_cursado_id"] ,
            "tipo_planificacion" => $request ["tipo_planificacion"] ,
            "created_at" => now() ,
            "updated_at" => now()
        ]);

        return response()->json(['mensaje' => 'La planificacion se actualizo correctamente']);
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::table('planificacion_diaria')-> where('id', '=', $id)-> delete();
        return response()->json(['mensaje' => 'La planificacion se ha borrado correctamente']);
        //
    }
}
