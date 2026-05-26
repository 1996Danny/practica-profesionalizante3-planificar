<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstadosAnualController extends Controller
{
    // Mostrar todos los registros
    public function index()
    {
        return DB::table('estados_anual')->get();
    }

    // Crear un nuevo registro
    public function store(Request $request)
    {
        $id = DB::table('estados_anual')->insertGetId([
            'estado' => $request->estado,
            'fecha' => $request->fecha,
            'planificacion_anual_id' => $request->planificacion_anual_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $estado = DB::table('estados_anual')->where('id', $id)->first();

        return [
            'mensaje' => 'registro creado correctamente',
            'estado' => $estado
        ];
    }

    // Mostrar un registro específico
    public function show($id)
    {
        return DB::table('estados_anual')->where('id', $id)->first();
    }

    // Actualizar un registro
    public function update(Request $request, $id)
    {
        $updateData = [];
        
        if ($request->has('estado')) {
            $updateData['estado'] = $request->estado;
        }
        if ($request->has('fecha')) {
            $updateData['fecha'] = $request->fecha;
        }
        if ($request->has('planificacion_anual_id')) {
            $updateData['planificacion_anual_id'] = $request->planificacion_anual_id;
        }
        
        $updateData['updated_at'] = now();

        DB::table('estados_anual')
            ->where('id', $id)
            ->update($updateData);

        $estado = DB::table('estados_anual')->where('id', $id)->first();

        return [
            'mensaje' => 'datos actualizado correctamente',
            'estado' => $estado
        ];
    }

    // Eliminar un registro
    public function destroy($id)
    {
        DB::table('estados_anual')->where('id', $id)->delete();
        
        return [
            'mensaje' => 'datos eliminado correctamente'
        ];
    }
}