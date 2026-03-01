<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Interfaces\EvaluacionDePlanServiceInterface;
use Exception;

class EvaluacionDePlanController extends Controller
{
    protected $service;

    public function __construct(EvaluacionDePlanServiceInterface $service)
    {
        $this->service = $service;
    }

    public function store(Request $request)
    {
        $request->validate([
            'fk_id_plan_de_accion' => 'required|exists:planes_de_accion,id_plan_de_accion',
            'criterios' => 'required|string',
            'conclusiones' => 'required|string',
            'tipo' => 'required|in:parcial,final',
        ]);

        try {
            $this->service->crear($request->all());

            return back()->with('success', 'Evaluación creada correctamente.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}