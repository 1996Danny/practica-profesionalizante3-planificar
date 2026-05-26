<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PersonasSeeder::class);
        $this->call(CursoSeeder::class);
        $this->call(CargoSeeder::class);
        $this->call(SitRevistaSeeder::class);
        $this->call(AreasSeeder::class);
        $this->call(PersonaCargoSeeder::class);
        $this->call(CursadoSeeder::class);
        $this->call(PersonaCargoCursadoSeeder::class);
        $this->call(PlanificacionAnualSeeder::class);
        $this->call(PlanificacionDiariaSeeder::class);
        $this->call(EstadosAnualSeeder::class);
        $this->call(EstadosDiariaSeeder::class);
        $this->call(UserSeeder::class);

    }
}
