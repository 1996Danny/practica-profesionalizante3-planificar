<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Persona;
use App\Models\PlanificacionAnual;
use App\Models\PlanificacionDiaria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * GET /api/admin/dashboard
     */
    public function dashboard(): JsonResponse
    {
        return response()->json([
            'usuarios' => [
                'total'      => User::count(),
                'admins'     => User::where('role', 'admin')->count(),
                'directores' => User::where('role', 'director')->count(),
                'docentes'   => User::where('role', 'docente')->count(),
                'users'      => User::where('role', 'user')->count(),
            ],
            'planificaciones' => [
                'anuales' => [
                    'total'   => PlanificacionAnual::count(),
                    'activas' => PlanificacionAnual::count(),
                    'ocultas' => PlanificacionAnual::onlyTrashed()->count(),
                ],
                'diarias' => [
                    'total'   => PlanificacionDiaria::count(),
                    'activas' => PlanificacionDiaria::count(),
                    'ocultas' => PlanificacionDiaria::onlyTrashed()->count(),
                ],
            ],
            'personas' => [
                'total'     => Persona::count(),
                'eliminadas' => Persona::onlyTrashed()->count(),
            ],
        ]);
    }

    /**
     * GET /api/admin/usuarios
     */
    public function usuarios(): JsonResponse
    {
        $usuarios = User::with('persona')
            ->orderBy('role')
            ->orderBy('name')
            ->get()
            ->map(fn($user) => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'role'       => $user->role,
                'persona_id' => $user->persona_id,
                'persona'    => $user->persona ? [
                    'id'        => $user->persona->id,
                    'nombres'   => $user->persona->nombres,
                    'apellidos' => $user->persona->apellidos,
                    'dni'       => $user->persona->dni,
                    'email'     => $user->persona->email, // Usa el accessor
                ] : null,
                'created_at' => $user->created_at,
            ]);

        return response()->json([
            'data'  => $usuarios,
            'total' => $usuarios->count(),
        ]);
    }

    /**
     * GET /api/admin/usuarios/{id}
     */
    public function showUsuario(int $id): JsonResponse
    {
        $user = User::with('persona')->find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        return response()->json([
            'data' => [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'role'    => $user->role,
                'persona' => $user->persona ? [
                    'id'              => $user->persona->id,
                    'nombres'         => $user->persona->nombres,
                    'apellidos'       => $user->persona->apellidos,
                    'dni'             => $user->persona->dni,
                    'email'           => $user->persona->email,
                    'telefono'        => $user->persona->telefono,
                    'direccion'       => $user->persona->direccion,
                    'fecha_nacimiento' => $user->persona->fecha_nacimiento,
                ] : null,
            ]
        ]);
    }

    /**
     * DELETE /api/admin/usuarios/{id}
     */
    public function destroyUsuario(Request $request, int $id): JsonResponse
    {
        $authUser = $request->user();

        if ($authUser->id === $id) {
            return response()->json([
                'message' => 'No puedes eliminar tu propia cuenta'
            ], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $nombre = $user->name;
        $user->delete();

        return response()->json([
            'message' => "Usuario {$nombre} eliminado correctamente"
        ]);
    }

    /**
     * GET /api/admin/personas
     */
    public function personas(): JsonResponse
    {
        $personas = Persona::withTrashed()
            ->with('user')
            ->orderBy('apellidos')
            ->get()
            ->map(fn($persona) => [
                'id'         => $persona->id,
                'apellidos'  => $persona->apellidos,
                'nombres'    => $persona->nombres,
                'dni'        => $persona->dni,
                'email'      => $persona->email, // Accessor
                'telefono'   => $persona->telefono,
                'eliminada'  => !is_null($persona->deleted_at),
                'user'       => $persona->user ? [
                    'id'   => $persona->user->id,
                    'role' => $persona->user->role,
                ] : null,
            ]);

        return response()->json([
            'data'  => $personas,
            'total' => $personas->count(),
        ]);
    }
}
