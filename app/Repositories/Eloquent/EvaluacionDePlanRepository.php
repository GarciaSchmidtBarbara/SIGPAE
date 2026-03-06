<?php

namespace App\Repositories\Eloquent;

use App\Models\EvaluacionDePlan;
use App\Repositories\Interfaces\EvaluacionDePlanRepositoryInterface;

class EvaluacionDePlanRepository implements EvaluacionDePlanRepositoryInterface
{
    public function crear(array $data): EvaluacionDePlan
    {
        return EvaluacionDePlan::create($data);
    }
}