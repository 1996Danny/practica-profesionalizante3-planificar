<?php

namespace Database\Seeders;

use App\Models\PersonaCargoCursado;
use App\Models\PlanificacionAnual;
use Illuminate\Database\Seeder;

class PlanificacionAnualSeeder extends Seeder
{
    public function run()
    {
        // Obtenemos los IDs reales que se acaban de crear en PersonaCargoCursadoSeeder
        $relaciones = PersonaCargoCursado::pluck('id')->toArray();

        if (empty($relaciones)) {
            // Si por alguna razón la tabla está vacía, evitamos que rompa
            return;
        }

        // Ejemplo insertando usando IDs que SÍ existen
        PlanificacionAnual::create([
            'aprendizajes_esperados' => 'Comprender conceptos fundamentales del área',
            'areas_id' => 1,
            'bibliografia' => 'Sommerville, Ian. Ingeniería de Software. 10ma edición',
            'criterios' => 'Participación, evaluación continua y proyectos integradores',
            'diagnostico' => 'Buen nivel general con algunas dificultades de aplicación',
            'fecha_presentacion' => '2025-03-15',
            'persona_cargo_cursado_id' => $relaciones[0], // ID real existente (usualmente 1)
            'saberes' => 'Teóricos y prácticos relacionados al contenido anual',
            'tipo_planificacion' => 'Anual',
        ]);

        // Si tienes al menos un segundo registro existente, lo usas.
        // Si no, reutilizas el primero para no violar la clave foránea.
        $segundoId = isset($relaciones[1]) ? $relaciones[1] : $relaciones[0];

        PlanificacionAnual::create([
            'aprendizajes_esperados' => 'Desarrollar habilidades de pensamiento crítico',
            'areas_id' => 2,
            'bibliografia' => 'Pressman, Ingeniería del Software. 9na edición',
            'criterios' => 'Evaluación continua y resolución de problemas',
            'diagnostico' => 'Grupo heterogéneo con potencial alto de aprendizaje',
            'fecha_presentacion' => '2025-03-20',
            'persona_cargo_cursado_id' => $segundoId, // Evita poner '2' a ciegas si no existe
            'saberes' => 'Conceptos interdisciplinares aplicados a la práctica',
            'tipo_planificacion' => 'Trimestral',
        ]);
    }
}
