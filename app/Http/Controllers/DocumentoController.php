<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Documento;
use App\Models\Intervencion;
use App\Models\PlanDeAccion;
use App\Services\Interfaces\DocumentoServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentoController extends Controller
{
    public function __construct(
        protected DocumentoServiceInterface $service
    ) {}

    // ── Listado principal ──────────────────────────────────────

    public function index(Request $request): View
    {
        $documentos = $this->service->listar($request);

        return view('documentos.principal', [
            'documentos' => $documentos,
        ]);
    }

    // ── Formulario de creación ─────────────────────────────────

    public function create(): View
    {
        $alumnos = Alumno::with('persona')
            ->join('personas', 'alumnos.fk_id_persona', '=', 'personas.id_persona')
            ->orderBy('personas.apellido')
            ->orderBy('personas.nombre')
            ->select('alumnos.*')
            ->get();

        $planes = PlanDeAccion::orderBy('id_plan_de_accion')->get();

        $intervenciones = Intervencion::orderByDesc('fecha_hora_intervencion')->get();

        return view('documentos.crear', compact('alumnos', 'planes', 'intervenciones'));
    }

    // ── Guardar documento ──────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre'                => 'required|string|max:255',
            'contexto'              => 'required|in:perfil_alumno,plan_accion,intervencion,institucional',
            'disponible_presencial' => 'required|in:presencial,solo_digital',
            'archivo'               => 'required|file|max:10240', // 10 MB en KB
            'fk_id_entidad'         => 'nullable|integer',
        ]);

        $archivo = $request->file('archivo');

        // Comprobar extensión antes de llamar al servicio
        $ext = strtolower($archivo->getClientOriginalExtension());
        if (!array_key_exists($ext, Documento::MIMES_PERMITIDOS)) {
            return back()
                ->withInput()
                ->with('error_formato', true);
        }

        try {
            $data = [
                'nombre'                => $request->input('nombre'),
                'contexto'              => $request->input('contexto'),
                'disponible_presencial' => $request->input('disponible_presencial') === 'presencial',
                'fk_id_entidad'         => $request->input('fk_id_entidad'),
            ];

            $profesionalId = auth()->user()->id_profesional ?? auth()->id();

            $this->service->subir($data, $archivo, $profesionalId);

            return redirect()->route('documentos.principal')
                ->with('success', 'Documento cargado con éxito');

        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->with('error_subida', $e->getMessage());
        }
    }

    // ── Descarga ───────────────────────────────────────────────

    public function download(int $id)
    {
        try {
            $doc = $this->service->descargar($id);
            $ruta = Storage::disk('local')->path($doc->ruta_archivo);
            return response()->download($ruta, $doc->nombre . '.' . strtolower($doc->tipo_formato->value));
        } catch (\RuntimeException $e) {
            return back()->with('error', 'No se pudo descargar el archivo. Intente nuevamente o contacte al administrador.');
        }
    }

    // ── Visualización online ───────────────────────────────────

    public function preview(int $id)
    {
        try {
            $doc = $this->service->descargar($id);

            if (!$doc->visualizable_online) {
                return back()->with('error', 'El documento no puede visualizarse en línea. Descárguelo para consultarlo.');
            }

            $ruta = Storage::disk('local')->path($doc->ruta_archivo);
            $mime = mime_content_type($ruta);

            return response()->file($ruta, ['Content-Type' => $mime]);
        } catch (\RuntimeException $e) {
            return back()->with('error', 'No se pudo descargar el archivo. Intente nuevamente o contacte al administrador.');
        }
    }

    // ── Eliminación ────────────────────────────────────────────

    public function destroy(int $id): RedirectResponse
    {
        $this->service->eliminar($id);

        return redirect()->route('documentos.principal')
            ->with('success', 'Documento eliminado');
    }

    // ── API: búsqueda de entidades asociadas (Ajax) ────────────

    public function buscarEntidad(Request $request): JsonResponse
    {
        $contexto = $request->get('contexto', '');
        $termino  = $request->get('q', '');

        if (strlen($termino) < 2) {
            return response()->json([]);
        }

        $resultados = $this->service->buscarEntidadPorContexto($contexto, $termino);

        return response()->json($resultados);
    }
}
