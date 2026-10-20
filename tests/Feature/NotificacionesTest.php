<?php

namespace Tests\Feature;

use App\Models\CarreraEstudiante;
use App\Models\EstadoSolicitud;
use App\Models\Estudiante;
use App\Models\Solicitud;
use App\Models\User;
use App\Notifications\NotificacionPortal;
use App\Services\Notificaciones\EnlaceNotificacion;
use App\Services\Notificaciones\NotificacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class NotificacionesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['mail.default' => 'array', 'queue.default' => 'sync']);
    }

    private function aviso(User $usuario, string $titulo = 'Aviso propio', string $url = '/estudiante/solicitudes')
    {
        return $usuario->notifications()->create([
            'id' => (string) Str::uuid(), 'type' => NotificacionPortal::class,
            'data' => ['titulo' => $titulo, 'mensaje' => 'Se actualizó tu solicitud.', 'url' => $url],
        ]);
    }

    public function test_notifica_al_propietario_por_database_y_mail_sin_duplicados(): void
    {
        $usuario = User::factory()->create();
        $otro = User::factory()->create();
        app(NotificacionService::class)->notificarUsuarios(
            [$usuario, $usuario], 'Cambio de estado', '<script>alert(1)</script>', route('solicitudes.index'),
        );

        $this->assertSame(1, $usuario->unreadNotifications()->count());
        $this->assertSame(0, $otro->notifications()->count());
        $aviso = $usuario->notifications()->firstOrFail();
        $this->assertSame('/estudiante/solicitudes', $aviso->data['url']);
        $correos = Mail::mailer()->getSymfonyTransport()->messages();
        $this->assertCount(1, $correos);
        $correo = $correos->first()->getOriginalMessage();
        $this->assertSame($usuario->email, $correo->getTo()[0]->getAddress());
        $this->assertSame('Cambio de estado', $correo->getSubject());
        $this->assertStringNotContainsString('<script>', $correo->getHtmlBody());
        $this->assertStringContainsString('&lt;script&gt;', $correo->getHtmlBody());
    }

    public function test_cambio_de_estado_usa_el_dueno_y_solo_la_observacion_publica(): void
    {
        $dueno = User::factory()->create();
        $operador = User::factory()->create();
        $this->actingAs($operador);
        $solicitud = (new Solicitud)->forceFill(['id' => 42, 'codigo' => 'SOL-2026-00042']);
        $solicitud->setRelation('carreraEstudiante', (new CarreraEstudiante)->forceFill(['user_id' => $dueno->id]));
        $estado = (new EstadoSolicitud)->forceFill([
            'nombre' => 'estado_interno', 'etiqueta_estudiante' => 'Requiere corrección',
            'descripcion' => 'Nota interna que no debe salir',
        ]);

        app(NotificacionService::class)->notificarCambioEstado($solicitud, $estado, 'Adjunta el respaldo legible.');

        $this->assertSame(0, $operador->notifications()->count());
        $datos = $dueno->notifications()->firstOrFail()->data;
        $this->assertStringContainsString('SOL-2026-00042', $datos['titulo']);
        $this->assertStringContainsString('Requiere corrección', $datos['mensaje']);
        $this->assertStringContainsString('Adjunta el respaldo legible.', $datos['mensaje']);
        $this->assertStringNotContainsString('Nota interna', $datos['mensaje']);
        $this->assertSame('/estudiante/solicitudes/42', $datos['url']);
    }

    public function test_resuelve_el_destinatario_del_contrato_estudiante_id(): void
    {
        $estudiante = Estudiante::factory()->create();
        $solicitud = (new Solicitud)->forceFill(['id' => 43, 'codigo' => 'SOL-2026-00043']);
        $solicitud->setRelation('carreraEstudiante', (new CarreraEstudiante)->forceFill(['estudiante_id' => $estudiante->id]));
        app(NotificacionService::class)->notificarCambioEstado($solicitud, new EstadoSolicitud(['nombre' => 'Enviado']));
        $this->assertSame(1, $estudiante->user->notifications()->count());
    }

    public function test_espera_el_commit_y_descarta_avisos_de_una_transaccion_revertida(): void
    {
        $usuario = User::factory()->create();
        DB::beginTransaction();
        app(NotificacionService::class)->notificarUsuarios([$usuario], 'Revertido', 'No debe llegar', '/notificaciones');
        $this->assertSame(0, $usuario->notifications()->count());
        $this->assertCount(0, Mail::mailer()->getSymfonyTransport()->messages());
        DB::rollBack();
        $this->assertSame(0, $usuario->notifications()->count());
        $this->assertCount(0, Mail::mailer()->getSymfonyTransport()->messages());

        DB::beginTransaction();
        app(NotificacionService::class)->notificarUsuarios([$usuario], 'Confirmado', 'Ya está guardado', '/notificaciones');
        $this->assertSame(0, $usuario->notifications()->count());
        DB::commit();
        $this->assertSame(1, $usuario->notifications()->count());
        $this->assertSame('Confirmado', $usuario->notifications()->firstOrFail()->data['titulo']);
        $this->assertCount(1, Mail::mailer()->getSymfonyTransport()->messages());
    }

    public function test_invitados_no_pueden_consultar_ni_marcar_avisos(): void
    {
        $aviso = $this->aviso(User::factory()->create());
        $this->getJson(route('notificaciones.index'))->assertUnauthorized();
        $this->getJson(route('notificaciones.resumen'))->assertUnauthorized();
        $this->postJson(route('notificaciones.leer', $aviso->id))->assertUnauthorized();
        $this->postJson(route('notificaciones.leer-todas'))->assertUnauthorized();
        $this->assertNull($aviso->fresh()->read_at);
        $this->blade('<x-campana-notificaciones />')->assertDontSee('data-campana-notificaciones');
    }

    public function test_lista_y_resumen_muestran_solo_los_avisos_del_usuario(): void
    {
        $usuario = User::factory()->create();
        $propio = $this->aviso($usuario);
        $this->aviso(User::factory()->create(), 'Aviso ajeno');
        $this->actingAs($usuario)->get(route('notificaciones.index'))
            ->assertOk()->assertSee('Aviso propio')->assertDontSee('Aviso ajeno')
            ->assertSee('data-campana-notificaciones', false);
        $this->getJson(route('notificaciones.resumen'))->assertOk()
            ->assertJsonPath('sin_leer', 1)->assertJsonCount(1, 'notificaciones')
            ->assertJsonPath('notificaciones.0.id', $propio->id);
    }

    public function test_abrir_marca_solo_el_aviso_propio_y_rechaza_ids_ajenos(): void
    {
        $usuario = User::factory()->create();
        $propio = $this->aviso($usuario);
        $ajeno = $this->aviso(User::factory()->create());
        $this->actingAs($usuario)->post(route('notificaciones.leer', $ajeno->id))->assertNotFound();
        $this->assertNull($ajeno->fresh()->read_at);
        $this->get(route('notificaciones.leer', $propio->id))->assertStatus(405);
        $this->assertNull($propio->fresh()->read_at);
        $this->post(route('notificaciones.leer', $propio->id))->assertRedirect('/estudiante/solicitudes');
        $this->assertNotNull($propio->fresh()->read_at);
    }

    public function test_marcar_todas_no_afecta_a_otros_usuarios(): void
    {
        $usuario = User::factory()->create();
        $this->aviso($usuario);
        $this->aviso($usuario);
        $ajeno = $this->aviso(User::factory()->create());
        $this->actingAs($usuario)->postJson(route('notificaciones.leer-todas'))->assertOk()->assertJsonPath('sin_leer', 0);
        $this->assertSame(0, $usuario->unreadNotifications()->count());
        $this->assertNull($ajeno->fresh()->read_at);
    }

    public function test_escapa_el_contenido_y_no_redirige_fuera_del_portal(): void
    {
        $usuario = User::factory()->create();
        $aviso = $this->aviso($usuario, '<script>alert(1)</script>', 'https://otro-sitio.example');
        $this->actingAs($usuario)->get(route('notificaciones.index'))->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
        $this->post(route('notificaciones.leer', $aviso->id))->assertRedirect(route('notificaciones.index'));
    }

    public function test_pagina_avisos_antiguos_y_limita_el_resumen(): void
    {
        $usuario = User::factory()->create();
        for ($i = 1; $i <= 21; $i++) {
            $this->aviso($usuario, 'Aviso '.$i);
        }
        $this->actingAs($usuario)->getJson(route('notificaciones.resumen'))->assertOk()
            ->assertJsonPath('sin_leer', 21)->assertJsonCount(20, 'notificaciones');
        $this->get(route('notificaciones.index', ['page' => 2]))->assertOk()->assertSee('Página 2 de 2');
    }

    #[DataProvider('enlacesInvalidos')]
    public function test_rechaza_destinos_externos_o_ambiguos(string $enlace): void
    {
        $this->assertNull(EnlaceNotificacion::local($enlace));
        $this->expectException(\InvalidArgumentException::class);
        app(NotificacionService::class)->notificarUsuarios([], 'Título', 'Mensaje', $enlace);
    }

    public static function enlacesInvalidos(): array
    {
        return [['https://otro-sitio.example'], ['//otro-sitio.example'], ['/\\otro-sitio.example'],
            ['/%2fotro-sitio.example'], ['javascript:alert(1)'], ["/\r\nLocation:otro"], ['']];
    }
}
