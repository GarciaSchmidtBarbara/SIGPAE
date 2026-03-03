<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Evento;
use App\Models\Notificacion;
use App\Models\Persona;
use App\Models\PlanDeAccion;
use App\Models\Profesional;
use App\Enums\TipoNotificacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class NotificacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');

        $this->profesional = Profesional::factory()->create([
            'fk_id_persona' => Persona::factory()->create()->id_persona,
            'contrasenia'   => Hash::make('password123'),
        ]);

        $this->otroProf = Profesional::factory()->create([
            'fk_id_persona' => Persona::factory()->create()->id_persona,
        ]);
    }

    #[Test]
    public function puede_obtener_notificaciones_del_usuario_autenticado()
    {
        Notificacion::factory()->count(3)->create([
            'fk_id_profesional_destinatario' => $this->profesional->id_profesional,
            'fk_id_profesional_origen'       => $this->otroProf->id_profesional,
        ]);

        //Notificaciones de otro profesional (no deben aparecer)
        Notificacion::factory()->count(2)->create([
            'fk_id_profesional_destinatario' => $this->otroProf->id_profesional,
            'fk_id_profesional_origen'       => $this->profesional->id_profesional,
        ]);

        $response = $this->actingAs($this->profesional)
            ->getJson(route('notificaciones.index'));

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'notificaciones');
    }

    #[Test]
    public function puede_marcar_una_notificacion_como_leida()
    {
        $notificacion = Notificacion::factory()->create([
            'fk_id_profesional_destinatario' => $this->profesional->id_profesional,
            'fk_id_profesional_origen'       => $this->otroProf->id_profesional,
            'leida' => false,
        ]);

        $response = $this->actingAs($this->profesional)
            ->post(route('notificaciones.leer', $notificacion->id_notificacion));

        $response->assertRedirect();
        $notificacion->refresh();
        $this->assertTrue($notificacion->leida);
    }

    #[Test]
    public function puede_marcar_todas_las_notificaciones_como_leidas()
    {
        Notificacion::factory()->count(4)->create([
            'fk_id_profesional_destinatario' => $this->profesional->id_profesional,
            'fk_id_profesional_origen'       => $this->otroProf->id_profesional,
            'leida' => false,
        ]);

        $response = $this->actingAs($this->profesional)
            ->post(route('notificaciones.leer-todas'));

        $response->assertSuccessful();

        $noLeidas = Notificacion::where('fk_id_profesional_destinatario', $this->profesional->id_profesional)
            ->where('leida', false)
            ->count();

        $this->assertEquals(0, $noLeidas);
    }

    #[Test]
    public function no_puede_marcar_como_leida_una_notificacion_ajena()
    {
        $notificacion = Notificacion::factory()->create([
            'fk_id_profesional_destinatario' => $this->otroProf->id_profesional,
            'fk_id_profesional_origen'       => $this->profesional->id_profesional,
            'leida' => false,
        ]);

        $this->actingAs($this->profesional)
            ->post(route('notificaciones.leer', $notificacion->id_notificacion));

        $notificacion->refresh();
        $this->assertFalse($notificacion->leida);
    }

    #[Test]
    public function contador_solo_cuenta_notificaciones_no_leidas()
    {
        Notificacion::factory()->count(3)->create([
            'fk_id_profesional_destinatario' => $this->profesional->id_profesional,
            'fk_id_profesional_origen'       => $this->otroProf->id_profesional,
            'leida' => false,
        ]);
        Notificacion::factory()->count(2)->create([
            'fk_id_profesional_destinatario' => $this->profesional->id_profesional,
            'fk_id_profesional_origen'       => $this->otroProf->id_profesional,
            'leida' => true,
        ]);

        $response = $this->actingAs($this->profesional)
            ->getJson(route('notificaciones.index'));

        $response->assertStatus(200);
        $response->assertJsonPath('no_leidas', 3);
    }

    #[Test]
    public function notificacion_url_destino_es_null_cuando_recurso_borrado()
    {
        $notificacion = Notificacion::factory()->create([
            'fk_id_profesional_destinatario' => $this->profesional->id_profesional,
            'fk_id_profesional_origen'       => $this->otroProf->id_profesional,
            'tipo'           => TipoNotificacion::EVENTO_BORRADO,
            'fk_id_evento'   => null,
        ]);

        $this->assertNull($notificacion->urlDestino());
    }

    #[Test]
    public function notificacion_url_destino_apunta_al_evento_correcto()
    {
        $evento = Evento::factory()->create([
            'fk_id_profesional_creador' => $this->otroProf->id_profesional,
        ]);

        $notificacion = Notificacion::factory()->create([
            'fk_id_profesional_destinatario' => $this->profesional->id_profesional,
            'fk_id_profesional_origen'       => $this->otroProf->id_profesional,
            'tipo'         => TipoNotificacion::EVENTO_EDITADO,
            'fk_id_evento' => $evento->id_evento,
        ]);

        $this->assertNotNull($notificacion->urlDestino());
        $this->assertStringContainsString((string) $evento->id_evento, $notificacion->urlDestino());
    }
}
