<?php

namespace App\Services\Implementations;

use App\Models\Documento;
use App\Repositories\Interfaces\DocumentoRepositoryInterface;
use App\Services\Interfaces\DocumentoServiceInterface;
use App\Services\Interfaces\AlumnoServiceInterface;
use App\Services\Interfaces\PlanDeAccionServiceInterface;
use App\Services\Interfaces\IntervencionServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentoService implements DocumentoServiceInterface
{
    public function __construct(
        protected DocumentoRepositoryInterface $repo,
        protected AlumnoServiceInterface $alumnoService,
        protected PlanDeAccionServiceInterface $planDeAccionService,
        protected IntervencionServiceInterface $intervencionService,
    ) {}


    public function listar(Request $request): Collection
    {
        return $this->repo->filtrar($request)->map(fn (Documento $d) => [
            'id_documento'          => $d->id_documento,
            'nombre'                => $d->nombre,
            'contexto'              => $d->contexto,
            'etiqueta_contexto'     => $d->etiqueta_contexto,
            'pertenece_a'           => $d->pertenece_a,
            'tipo_formato'          => $d->tipo_formato?->value,
            'disponible_presencial' => $d->disponible_presencial,
            'visualizable_online'   => $d->visualizable_online,
            'tamanio_formateado'    => $d->tamanio_formateado,
            'fecha'                 => $d->fecha_hora_carga?->format('d/m/Y'),
        ]);
    }


    public function subir(array $data, UploadedFile $archivo, int $idProfesional): Documento
    {
        if ($archivo->getSize() > Documento::MAX_SIZE_BYTES) {
            throw new \RuntimeException('El archivo supera el tamaño máximo permitido (10 MB).');
        }

        $extension = strtolower($archivo->getClientOriginalExtension());
        if (!array_key_exists($extension, Documento::MIMES_PERMITIDOS)) {
            throw new \RuntimeException('Formato de archivo no permitido. Use: PDF, DOC, DOCX, XLS, XLSX, JPG o PNG.');
        }

        //Guardar el archivo en storage/app/documentos
        $nombreArchivo = Str::uuid() . '.' . $extension;
        $ruta = $archivo->storeAs('documentos', $nombreArchivo, 'local');

        $tipoFormato = match ($extension) {
            'jpg', 'jpeg' => 'JPG',
            'png'         => 'PNG',
            'pdf'         => 'PDF',
            'doc'         => 'DOC',
            'docx'        => 'DOCX',
            'xls'         => 'XLS',
            'xlsx'        => 'XLSX',
            default       => strtoupper($extension),
        };

        $payload = [
            'nombre'                => $data['nombre'],
            'contexto'              => $data['contexto'],
            'tipo_formato'          => $tipoFormato,
            'disponible_presencial' => $data['disponible_presencial'] ?? false,
            'ruta_archivo'          => $ruta,
            'tamanio_archivo'       => $archivo->getSize(),
            'fk_id_profesional'     => $idProfesional,
        ];

        match ($data['contexto']) {
            'perfil_alumno' => $payload['fk_id_alumno']        = $data['fk_id_entidad'] ?? null,
            'plan_accion'   => $payload['fk_id_plan_de_accion'] = $data['fk_id_entidad'] ?? null,
            'intervencion'  => $payload['fk_id_intervencion']   = $data['fk_id_entidad'] ?? null,
            default         => null,
        };

        try {
            return $this->repo->crear($payload);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            Storage::disk('local')->delete($ruta);
            throw new \RuntimeException(
                'Ya existe un documento con el nombre "' . $data['nombre'] . '". Por favor, elija un nombre diferente.'
            );
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getCode(), '23505')) {
                Storage::disk('local')->delete($ruta);
                throw new \RuntimeException(
                    'Ya existe un documento con el nombre "' . $data['nombre'] . '". Por favor, elija un nombre diferente.'
                );
            }
            Storage::disk('local')->delete($ruta);
            throw $e;
        }
    }


    public function descargar(int $id): Documento
    {
        $doc = $this->repo->buscarPorId($id);
        if (!$doc) {
            throw new \RuntimeException('Documento no encontrado.');
        }
        if (!Storage::disk('local')->exists($doc->ruta_archivo)) {
            throw new \RuntimeException('El archivo no existe en el servidor.');
        }
        return $doc;
    }


    public function eliminar(int $id): bool
    {
        $doc = $this->repo->buscarPorId($id);
        if (!$doc) {
            return false;
        }
        if ($doc->ruta_archivo && Storage::disk('local')->exists($doc->ruta_archivo)) {
            Storage::disk('local')->delete($doc->ruta_archivo);
        }
        return $this->repo->eliminar($id);
    }

    public function eliminarVarios(array $ids): void
    {
        foreach ($ids as $id) {
            $this->eliminar((int) $id);
        }
    }


    public function listarParaAlumno(int $idAlumno): array
    {
        return $this->repo->buscarPorAlumno($idAlumno)
            ->map(fn (Documento $d) => [
                'id_documento'  => $d->id_documento,
                'nombre'        => $d->nombre,
                'tipo_formato'  => $d->tipo_formato?->value ?? '',
                'tamanio'       => $d->tamanio_formateado,
                'fecha'         => $d->fecha_hora_carga?->format('d/m/Y') ?? '',
                'ruta_descarga' => route('documentos.descargar', $d->id_documento),
            ])
            ->values()
            ->toArray();
    }

    public function listarParaIntervencion(int $idIntervencion): array
    {
        return $this->repo->buscarPorIntervencion($idIntervencion)
            ->map(fn (Documento $d) => [
                'id_documento'  => $d->id_documento,
                'nombre'        => $d->nombre,
                'tipo_formato'  => $d->tipo_formato?->value ?? '',
                'tamanio'       => $d->tamanio_formateado,
                'fecha'         => $d->fecha_hora_carga?->format('d/m/Y') ?? '',
                'ruta_descarga' => route('documentos.descargar', $d->id_documento),
            ])
            ->values()
            ->toArray();
    }

    public function listarParaPlanDeAccion(int $idPlan): array
    {
        return $this->repo->buscarPorPlanDeAccion($idPlan)
            ->map(fn (Documento $d) => [
                'id_documento'  => $d->id_documento,
                'nombre'        => $d->nombre,
                'tipo_formato'  => $d->tipo_formato?->value ?? '',
                'tamanio'       => $d->tamanio_formateado,
                'fecha'         => $d->fecha_hora_carga?->format('d/m/Y') ?? '',
                'ruta_descarga' => route('documentos.descargar', $d->id_documento),
            ])
            ->values()
            ->toArray();
    }


    public function buscarEntidadPorContexto(string $contexto, string $termino): array
    {
        $termino = strtolower(trim($termino));

        return match ($contexto) {
            'perfil_alumno' => $this->alumnoService->buscar($termino)
                ->take(10)
                ->map(fn ($a) => [
                    'id'          => $a->id_alumno,
                    'descripcion' => trim(($a->persona->apellido ?? '') . ', ' . ($a->persona->nombre ?? '')) . ' — DNI ' . ($a->persona->dni ?? ''),
                ])
                ->values()
                ->toArray(),

            'plan_accion' => $this->planDeAccionService->buscarPorTermino($termino)
                ->map(fn ($p) => [
                    'id'          => $p->id_plan_de_accion,
                    'descripcion' => $p->descripcion,
                ])
                ->toArray(),

            'intervencion' => $this->intervencionService->buscarPorTermino($termino)
                ->map(fn ($i) => [
                    'id'          => $i->id_intervencion,
                    'descripcion' => 'Intervención N°' . $i->id_intervencion . ' — ' . $i->tipo_intervencion,
                ])
                ->toArray(),

            default => [],
        };
    }

    public function datosParaFormulario(): array
    {
        $alumnos = $this->alumnoService->listar();
        $planes = $this->planDeAccionService->obtenerTodos();
        $intervenciones = $this->intervencionService->obtenerTodos();

        return compact('alumnos', 'planes', 'intervenciones');
    }
}
