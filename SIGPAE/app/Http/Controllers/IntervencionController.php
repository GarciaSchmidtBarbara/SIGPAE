<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use App\Services\Interfaces\IntervencionServiceInterface;

use App\Models\Intervencion;


class IntervencionController extends Controller
{
    public function __construct(IntervencionServiceInterface $intervencionService)
    {
        $this->service = $intervencionService;

    }

    public function vista()
    {
        $intervenciones = $this->service->obtenerTodos();
        $tiposIntervencion = $this->service->obtenerTipos(); 
        $cursos = $this->service->obtenerAulasParaFiltro();
        $alumnos = []; 

        return view('intervenciones.principal', compact('intervenciones', 'tiposIntervencion', 'cursos', 'alumnos'));
    }

    public function crear()
    {
        return view('intervenciones.crear-editar');
    }

    public function guardar(Request $request)
    {
        $data = $request->validate([
            'fecha_hora_intervencion' => 'required|date',
            'lugar' => 'required|string|max:255',
            'modalidad' => 'required|string',
            'otra_modalidad' => 'nullable|string',
            'temas_tratados' => 'nullable|string',
            'compromisos' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        try {
            $intervencion = $this->service->crear($data);
            return redirect()
                ->route('intervenciones.principal')
                ->with('success', 'Intervención creada exitosamente.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function editar(int $id)
    {
        $intervencion = $this->service->buscar($id);

        if (!$intervencion) {
            return redirect()->route('intervenciones.principal')->with('error', 'Intervención no encontrada.');
        }

        return view('intervenciones.crear-editar', compact('intervencion'));
    }

    public function actualizar(Request $request, int $id)
    {
        $data = $request->validate([
            'fecha_hora_intervencion' => 'required|date',
            'lugar' => 'required|string|max:255',
            'modalidad' => 'required|string',
            'otra_modalidad' => 'nullable|string',
            'temas_tratados' => 'nullable|string',
            'compromisos' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ]);

        try {
            $this->service->actualizar($id, $data);
            return redirect()
                ->route('intervenciones.principal')
                ->with('success', 'Intervención actualizada.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function eliminar(int $id)
    {
        $this->service->eliminar($id);

        return redirect()
            ->route('intervenciones.principal')
            ->with('success', 'Intervención eliminada.');
    }
}
