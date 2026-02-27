<?php

namespace App\Services\Implementations;

use App\Models\Alumno;
use App\Models\Documento;
use App\Models\Intervencion;
use App\Models\PlanDeAccion;
use App\Repositories\Interfaces\DocumentoRepositoryInterface;
use App\Services\Interfaces\DocumentoServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentoService implements DocumentoServiceInterface
{
    public function __construct(
        protected DocumentoRepositoryInterface $repo
    ) {}

    // ── Listado / filtrado ─────────────────────────────────────

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

    // ── Subida de archivo ──────────────────────────────────────

    public function subir(array $data, UploadedFile $archivo, int $idProfesional): Documento
    {
        // Validar tamaño (10 MB)
        if ($archivo->getSize() > Documento::MAX_SIZE_BYTES) {
            throw new \RuntimeException('El archivo supera el tamaño máximo permitido (10 MB).');
        }

        // Derivar extensión y validar formato
        $extension = strtolower($archivo->getClientOriginalExtension());
        if (!array_key_exists($extension, Documento::MIMES_PERMITIDOS)) {
            throw new \RuntimeException('Formato de archivo no permitido. Use: PDF, DOC, DOCX, XLS, XLSX, JPG o PNG.');
        }

        // Guardar el archivo en storage/app/documentos
        $nombreArchivo = Str::uuid() . '.' . $extension;
        $ruta = $archivo->storeAs('documentos', $nombreArchivo, 'local');

        // Mapear extensión a enum TipoFormato
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

        // Asociar entidad según contexto
        match ($data['contexto']) {
            'perfil_alumno' => $payload['fk_id_alumno']        = $data['fk_id_entidad'] ?? null,
            'plan_accion'   => $payload['fk_id_plan_de_accion'] = $data['fk_id_entidad'] ?? null,
            'intervencion'  => $payload['fk_id_intervencion']   = $data['fk_id_entidad'] ?? null,
            default         => null,
        };

        return $this->repo->crear($payload);
    }

    // ── Descarga de archivo ────────────────────────────────────

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

    // ── Eliminación ────────────────────────────────────────────

    public function eliminar(int $id): bool
    {
        $doc = $this->repo->buscarPorId($id);
        if (!$doc) {
            return false;
        }
        // Eliminar el archivo físico
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

    // ── Listado por alumno ─────────────────────────────────────

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

    // ── Búsqueda de entidades asociadas ───────────────────────

    public function buscarEntidadPorContexto(string $contexto, string $termino): array
    {
        $termino = strtolower(trim($termino));

        return match ($contexto) {
            'perfil_alumno' => Alumno::with('persona')
                ->whereHas('persona', function ($q) use ($termino) {
                    $q->whereRaw('LOWER(nombre) LIKE ?', ["%{$termino}%"])
                      ->orWhereRaw('LOWER(apellido) LIKE ?', ["%{$termino}%"])
                      ->orWhereRaw('CAST(dni AS TEXT) LIKE ?', ["%{$termino}%"]);
                })
                ->limit(10)
                ->get()
                ->map(fn ($a) => [
                    'id'          => $a->id_alumno,
                    'descripcion' => trim(($a->persona->apellido ?? '') . ', ' . ($a->persona->nombre ?? '')) . ' — DNI ' . ($a->persona->dni ?? ''),
                ])
                ->toArray(),

            'plan_accion' => PlanDeAccion::whereRaw('CAST(id_plan_de_accion AS TEXT) LIKE ?', ["%{$termino}%"])
                ->orWhereRaw('LOWER(tipo_plan::text) LIKE ?', ["%{$termino}%"])
                ->limit(10)
                ->get()
                ->map(fn ($p) => [
                    'id'          => $p->id_plan_de_accion,
                    'descripcion' => $p->descripcion,
                ])
                ->toArray(),

            'intervencion' => Intervencion::with('alumnos.persona')
                ->whereRaw('CAST(id_intervencion AS TEXT) LIKE ?', ["%{$termino}%"])
                ->orWhereRaw('LOWER(tipo_intervencion::text) LIKE ?', ["%{$termino}%"])
                ->limit(10)
                ->get()
                ->map(fn ($i) => [
                    'id'          => $i->id_intervencion,
                    'descripcion' => 'Intervención N°' . $i->id_intervencion . ' — ' . $i->tipo_intervencion,
                ])
                ->toArray(),

            default => [],
        };
    }
}
