<?php

namespace App\Services\Interfaces;

use App\Models\EvaluacionDePlan;

interface EvaluacionDePlanServiceInterface
{
    public function crear(array $data);
}