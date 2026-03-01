<?php

namespace App\Services;

use App\Models\PlanDeAccion;
use App\Repositories\EvaluacionDePlanRepository;
use Exception;

class EvaluacionDePlanService
{
    protected $repository;

    public function __construct(EvaluacionDePlanRepository $repository)
    {
        $this->repository = $repository;
    }

    public function crear(array $data)
    {
        $plan = PlanDeAccion::findOrFail($data['fk_id_plan_de_accion']);

        // VALIDACIÓN: solo planes abiertos
        if ($plan->estado !== 'abierto') {
            throw new Exception('No se puede evaluar un plan cerrado.');
        }

        $evaluacion = $this->repository->crear($data);

        // Si es evaluación final cerramos plan
        if ($data['tipo'] === 'final') {
            $plan->estado = 'cerrado';
            $plan->save();
        }

        return $evaluacion;
    }
}