<?php

namespace App\Repositories;

use App\Models\EvaluacionDePlan;

class EvaluacionDePlanRepository
{
    public function crear(array $data): EvaluacionDePlan
    {
        return EvaluacionDePlan::create($data);
    }
}