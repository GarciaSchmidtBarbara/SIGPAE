<?php

namespace App\Services\Implementations;

use App\Repositories\Interfaces\EvaluacionDePlanRepositoryInterface;
use App\Services\Interfaces\EvaluacionDePlanServiceInterface;
use App\Services\Interfaces\PlanDeAccionServiceInterface;
use App\Enums\EstadoPlan;
use Exception;

class EvaluacionDePlanService implements EvaluacionDePlanServiceInterface
{
    protected EvaluacionDePlanRepositoryInterface $repository;
    protected PlanDeAccionServiceInterface $planService;

    public function __construct(EvaluacionDePlanRepositoryInterface $repository, PlanDeAccionServiceInterface $planService)
    {
        $this->repository = $repository;
        $this->planService = $planService;
    }

    public function crear(array $data)
    {
        $plan = $this->planService->buscarPorId($data['fk_id_plan_de_accion']);

        if (!$plan) {
            throw new Exception('Plan de acción no encontrado.');
        }

        if ($plan->estado_plan !== EstadoPlan::ABIERTO) {
            throw new Exception('No se puede evaluar un plan cerrado.');
        }

        $evaluacion = $this->repository->crear($data);

        if ($data['tipo'] === 'final') {
            $this->planService->cambiarActivo($plan->id_plan_de_accion);
        }

        return $evaluacion;
    }
}