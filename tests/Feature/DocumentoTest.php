<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Alumno;
use App\Models\Aula;
use App\Models\Documento;
use App\Models\Intervencion;
use App\Models\Persona;
use App\Models\PlanDeAccion;
use App\Models\Profesional;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\Interfaces\NotificacionServiceInterface;

class DocumentoTest extends TestCase
{
    use DatabaseTruncation;

    protected Profesional $profesional;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(NotificacionServiceInterface::class, function ($mock) {
            $mock->shouldReceive('crear')->andReturn();
        });

        Aula::factory()->create();
        Aula::factory()->create();
        Aula::factory()->create();

        $persona = Persona::factory()->create();
        $this->profesional = Profesional::factory()->create([
            'fk_id_persona' => $persona->id_persona,
            'contrasenia'   => Hash::make('password123'),
        ]);
    }


    #[Test]
    public function usuario_autenticado_puede_ver_lista_de_documentos()
    {
        $response = $this->actingAs($this->profesional)
            ->get(route('documentos.principal'));

        $response->assertStatus(200);
        $response->assertViewIs('documentos.principal');
    }

    #[Test]
    public function usuario_no_autenticado_no_puede_acceder_a_documentos()
    {
        $response = $this->get(route('documentos.principal'));

        $response->assertRedirect(route('login'));
    }


    #[Test]
    public function puede_ver_formulario_de_subida()
    {
        $response = $this->actingAs($this->profesional)
            ->get(route('documentos.crear'));

        $response->assertStatus(200);
        $response->assertViewIs('documentos.crear');
    }


    #[Test]
    public function puede_subir_documento_institucional()
    {
        Storage::fake('local');

        $archivo = UploadedFile::fake()->create('informe.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->profesional)
            ->post(route('documentos.guardar'), [
                'nombre'                => 'Informe Institucional Test',
                'contexto'              => 'institucional',
                'disponible_presencial' => 'solo_digital',
                'archivo'               => $archivo,
            ]);

        $response->assertRedirect(route('documentos.principal'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('documentos', [
            'nombre'   => 'Informe Institucional Test',
            'contexto' => 'institucional',
        ]);
    }

    #[Test]
    public function puede_subir_documento_asociado_a_alumno()
    {
        Storage::fake('local');

        $aula   = Aula::first();
        $alumno = Alumno::factory()->create(['fk_id_aula' => $aula->id_aula]);

        $archivo = UploadedFile::fake()->create('certificado.pdf', 200, 'application/pdf');

        $response = $this->actingAs($this->profesional)
            ->post(route('documentos.guardar'), [
                'nombre'                => 'Certificado Alumno',
                'contexto'              => 'perfil_alumno',
                'disponible_presencial' => 'presencial',
                'archivo'               => $archivo,
                'fk_id_entidad'         => $alumno->id_alumno,
            ]);

        $response->assertRedirect(route('documentos.principal'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('documentos', [
            'nombre'      => 'Certificado Alumno',
            'contexto'    => 'perfil_alumno',
            'fk_id_alumno' => $alumno->id_alumno,
        ]);
    }

    #[Test]
    public function no_puede_subir_documento_sin_campos_requeridos()
    {
        $response = $this->actingAs($this->profesional)
            ->post(route('documentos.guardar'), []);

        $response->assertSessionHasErrors(['nombre', 'contexto', 'disponible_presencial', 'archivo']);
    }

    #[Test]
    public function no_puede_subir_documento_con_contexto_invalido()
    {
        Storage::fake('local');

        $archivo = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->profesional)
            ->post(route('documentos.guardar'), [
                'nombre'                => 'Test',
                'contexto'              => 'contexto_invalido',
                'disponible_presencial' => 'solo_digital',
                'archivo'               => $archivo,
            ]);

        $response->assertSessionHasErrors('contexto');
    }

    #[Test]
    public function rechaza_formato_de_archivo_no_permitido()
    {
        Storage::fake('local');

        $archivo = UploadedFile::fake()->create('script.exe', 100);

        $response = $this->actingAs($this->profesional)
            ->post(route('documentos.guardar'), [
                'nombre'                => 'Archivo Malicioso',
                'contexto'              => 'institucional',
                'disponible_presencial' => 'solo_digital',
                'archivo'               => $archivo,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error_formato');
    }


    #[Test]
    public function puede_descargar_documento_existente()
    {
        Storage::fake('local');
        $ruta = 'documentos/test_file.pdf';
        Storage::disk('local')->put($ruta, 'contenido de prueba');

        $doc = Documento::factory()->create([
            'ruta_archivo'      => $ruta,
            'tipo_formato'      => 'PDF',
            'fk_id_profesional' => $this->profesional->id_profesional,
        ]);

        $response = $this->actingAs($this->profesional)
            ->get(route('documentos.descargar', $doc->id_documento));

        $response->assertOk();
        $response->assertDownload();
    }

    #[Test]
    public function descargar_documento_sin_archivo_fisico_redirige_con_error()
    {
        $doc = Documento::factory()->create([
            'ruta_archivo'      => 'documentos/inexistente.pdf',
            'fk_id_profesional' => $this->profesional->id_profesional,
        ]);

        $response = $this->actingAs($this->profesional)
            ->get(route('documentos.descargar', $doc->id_documento));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }


    #[Test]
    public function puede_previsualizar_documento_visualizable()
    {
        Storage::fake('local');
        $ruta = 'documentos/preview_test.pdf';
        Storage::disk('local')->put($ruta, '%PDF-1.4 contenido simulado');

        $doc = Documento::factory()->create([
            'ruta_archivo'      => $ruta,
            'tipo_formato'      => 'PDF',
            'fk_id_profesional' => $this->profesional->id_profesional,
        ]);

        $response = $this->actingAs($this->profesional)
            ->get(route('documentos.ver', $doc->id_documento));

        if ($doc->visualizable_online) {
            $response->assertOk();
        } else {
            $response->assertRedirect();
        }
    }


    #[Test]
    public function puede_eliminar_documento()
    {
        Storage::fake('local');
        $ruta = 'documentos/to_delete.pdf';
        Storage::disk('local')->put($ruta, 'contenido');

        $doc = Documento::factory()->create([
            'ruta_archivo'      => $ruta,
            'fk_id_profesional' => $this->profesional->id_profesional,
        ]);

        $response = $this->actingAs($this->profesional)
            ->delete(route('documentos.eliminar', $doc->id_documento));

        $response->assertRedirect(route('documentos.principal'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('documentos', [
            'id_documento' => $doc->id_documento,
        ]);
    }


    #[Test]
    public function buscar_entidad_alumno_retorna_resultados()
    {
        $aula    = Aula::first();
        $persona = Persona::factory()->create(['nombre' => 'BuscableAlumno', 'apellido' => 'TestDoc']);
        Alumno::factory()->create([
            'fk_id_persona' => $persona->id_persona,
            'fk_id_aula'    => $aula->id_aula,
        ]);

        $response = $this->actingAs($this->profesional)
            ->getJson(route('documentos.buscar-entidad', [
                'contexto' => 'perfil_alumno',
                'q'        => 'BuscableAlumno',
            ]));

        $response->assertOk();
        $data = $response->json();
        $this->assertNotEmpty($data);
        $this->assertStringContainsString('BuscableAlumno', $data[0]['descripcion']);
    }

    #[Test]
    public function buscar_entidad_con_termino_corto_retorna_vacio()
    {
        $response = $this->actingAs($this->profesional)
            ->getJson(route('documentos.buscar-entidad', [
                'contexto' => 'perfil_alumno',
                'q'        => 'a',
            ]));

        $response->assertOk();
        $response->assertJson([]);
    }

    #[Test]
    public function buscar_entidad_plan_accion_retorna_resultados()
    {
        $aula   = Aula::first();
        $alumno = Alumno::factory()->create(['fk_id_aula' => $aula->id_aula]);

        $plan = PlanDeAccion::factory()->create([
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
        ]);

        $response = $this->actingAs($this->profesional)
            ->getJson(route('documentos.buscar-entidad', [
                'contexto' => 'plan_accion',
                'q'        => (string) $plan->id_plan_de_accion,
            ]));

        $response->assertOk();
    }

    #[Test]
    public function buscar_entidad_con_contexto_institucional_retorna_vacio()
    {
        $response = $this->actingAs($this->profesional)
            ->getJson(route('documentos.buscar-entidad', [
                'contexto' => 'institucional',
                'q'        => 'algo',
            ]));

        $response->assertOk();
        $response->assertJson([]);
    }


    #[Test]
    public function puede_subir_documento_formato_imagen()
    {
        Storage::fake('local');

        $archivo = UploadedFile::fake()->create('foto.jpg', 200, 'image/jpeg');

        $response = $this->actingAs($this->profesional)
            ->post(route('documentos.guardar'), [
                'nombre'                => 'Foto Test',
                'contexto'              => 'institucional',
                'disponible_presencial' => 'solo_digital',
                'archivo'               => $archivo,
            ]);

        $response->assertRedirect(route('documentos.principal'));
        $response->assertSessionHas('success');
    }
}
