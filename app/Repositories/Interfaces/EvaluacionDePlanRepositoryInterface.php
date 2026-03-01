<?php

namespace App\Repositories\Interfaces;

use App\Models\EvaluacionDePlan;

interface EvaluacionDePlanRepositoryInterface
{
    public function crear(array $data): EvaluacionDePlan;
}