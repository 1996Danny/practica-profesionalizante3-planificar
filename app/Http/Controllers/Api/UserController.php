<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function assignRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:admin,director,docente,user'
        ]);

        $auth_user = $request->user(); // Usuario autenticado realizando la acción
        $target_user = User::findOrFail($id); // Usuario al que se le quiere cambiar el rol
        $new_role = $request->role;

        // Evitar que se edite a sí mismo
        if ($auth_user->id === $target_user->id) {
            return response()->json(['message' => 'No puedes cambiar tu propio rol.'], 403);
        }

        // Jerarquía de ADMIN
        if ($auth_user->role === 'admin') {
            // El admin no puede desgradar a otro admin (si hubiera más) por seguridad básica, o podés permitirlo quitando esta regla.
            if ($target_user->role === 'admin') {
                return response()->json(['message' => 'No tienes permisos para modificar a otro administrador.'], 403);
            }

            $target_user->role = $new_role;
            $target_user->save();
            return response()->json(['message' => "Rol actualizado a {$new_role} correctamente."]);
        }

        // Jerarquía de DIRECTOR
        if ($auth_user->role === 'director') {
            // El director solo puede interactuar si el usuario actual es 'docente' o 'user' Y el nuevo rol es 'docente' o 'user'.
            // No puede tocar administradores, ni nombrar directores, ni tocar a otros directores.
            if (in_array($target_user->role, ['admin', 'director']) || in_array($new_role, ['admin', 'director'])) {
                return response()->json(['message' => 'Los directores solo pueden asignar o remover el rol de docente.'], 403);
            }

            $target_user->role = $new_role;
            $target_user->save();
            return response()->json(['message' => "Rol actualizado a {$new_role} por el Director."]);
        }

        return response()->json(['message' => 'Acción no autorizada para tu rol.'], 403);
    }
}
