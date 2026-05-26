<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Persona;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder {
    public function run(): void {
        
        // Administrador
        $pAdmin = Persona::create([
            'nombres' => 'Admininistrador', 
            'apellidos' => 'Prueba', 
            'dni' => '11111111',
            'e-mail' => 'admin@planificar.com',
            'telefono' => '3644000000',
            'direccion' => 'Juan José Castelli, Chaco',
            'fecha_nacimiento' => '1995-01-01'
        ]);
        
        User::create([
            'name' => 'admin',
            'email' => 'admin@planificar.com',
            'password' => Hash::make('admin1234'),
            'role' => 'admin',
            'persona_id' => $pAdmin->id
        ]);

        // Director
        $pDirector = Persona::create([
            'nombres' => 'Juan', 
            'apellidos' => 'Director', 
            'dni' => '22222222',
            'e-mail' => 'director@planificar.com',
            'telefono' => '3644111111',
            'direccion' => 'Juan José Castelli, Chaco',
            'fecha_nacimiento' => '1988-05-15'
        ]);
        
        User::create([
            'name' => 'director',
            'email' => 'director@planificar.com',
            'password' => Hash::make('director1234'),
            'role' => 'director',
            'persona_id' => $pDirector->id
        ]);

        // Docente
        $pDocente = Persona::create([
            'nombres' => 'Ana', 
            'apellidos' => 'Docente', 
            'dni' => '33333333',
            'e-mail' => 'docente@planificar.com',
            'telefono' => '3644222222',
            'direccion' => 'Juan José Castelli, Chaco',
            'fecha_nacimiento' => '1992-09-20'
        ]);
        
        User::create([
            'name' => 'docente',
            'email' => 'docente@planificar.com',
            'password' => Hash::make('docente1234'),
            'role' => 'docente',
            'persona_id' => $pDocente->id
        ]);

        // usuario comun con rol 'user'
        $pUser = Persona::create([
            'nombres' => 'Carlos', 
            'apellidos' => 'Comun', 
            'dni' => '44444444',
            'e-mail' => 'user@planificar.com',
            'telefono' => '3644333333',
            'direccion' => 'Juan José Castelli, Chaco',
            'fecha_nacimiento' => '1999-12-25'
        ]);
        
        User::create([
            'name' => 'user',
            'email' => 'user@planificar.com',
            'password' => Hash::make('user1234'),
            'role' => 'user',
            'persona_id' => $pUser->id
        ]);
    }
}