<?php

namespace Tests\Feature\Chatbot;

use App\Models\User;
use App\Services\Chatbot\Prolog\PrologClient;
use Tests\TestCase;

class ChatbotMensajeValidationTest extends TestCase
{
    public function test_texto_libre_sin_tramite_id_no_da_422(): void
    {
        $response = $this->postJson('/chatbot/mensaje', [
            'version' => 2,
            'texto'   => '¿Cuáles son los requisitos de examen de recuperación?',
            'tramite_id' => null,
        ]);

        $this->assertNotEquals(422, $response->getStatusCode());
    }

    public function test_texto_libre_con_tramite_inexistente_falla_con_mensaje_en_espanol(): void
    {
        $response = $this->postJson('/chatbot/mensaje', [
            'version'    => 2,
            'texto'      => 'informacion general',
            'tramite_id' => 'tramite_que_no_existe_xyz',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.codigo', 'datos_invalidos')
            ->assertJsonPath('error.campos.tramite_id.0', 'El trámite indicado no existe en el catálogo.');
    }

    public function test_validacion_contenido_datos_usuario_falla_si_promedio_fuera_de_rango(): void
    {
        $estudiante = new User(['name' => 'Carlos Estudiante', 'email' => 'carlos@uees.edu.ec']);

        $response = $this->actingAs($estudiante)->postJson('/chatbot/mensaje', [
            'version'       => 2,
            'pregunta_id'   => 'examen_recuperacion__elegibilidad',
            'tramite_id'    => 'examen_recuperacion',
            'intencion'     => 'evaluar_elegibilidad',
            'origen_datos'  => 'manual',
            'datos_usuario' => [
                'promedio' => 150, // Inválido, debe ser entre 0 y 100
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.codigo', 'datos_invalidos');

        $this->assertEquals(
            'El promedio debe estar entre 0 y 100.',
            $response->json('error.campos')['datos_usuario.promedio'][0]
        );
    }

    public function test_api01_visitante_no_autenticado_es_rechazado_con_401_en_evaluacion_personal(): void
    {
        // Petición directa a evaluación de elegibilidad sin sesión iniciada
        $response = $this->postJson('/chatbot/mensaje', [
            'version'       => 2,
            'pregunta_id'   => 'examen_recuperacion__elegibilidad',
            'tramite_id'    => 'examen_recuperacion',
            'intencion'     => 'evaluar_elegibilidad',
            'origen_datos'  => 'manual',
            'datos_usuario' => [
                'promedio' => 85,
            ],
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('error.codigo', 'sesion_requerida')
            ->assertJsonPath('error.mensaje', 'Debes iniciar sesión con tu cuenta institucional para realizar una evaluación personal de elegibilidad.');
    }

    public function test_api01_usuario_suspendido_es_rechazado_con_403_en_evaluacion_personal(): void
    {
        $usuarioSuspendido = new User([
            'name'  => 'Usuario Suspendido',
            'email' => 'suspendido@uees.edu.ec',
        ]);
        $usuarioSuspendido->rol = 'suspendido';

        $response = $this->actingAs($usuarioSuspendido)->postJson('/chatbot/mensaje', [
            'version'       => 2,
            'pregunta_id'   => 'examen_recuperacion__elegibilidad',
            'tramite_id'    => 'examen_recuperacion',
            'intencion'     => 'evaluar_elegibilidad',
            'origen_datos'  => 'manual',
            'datos_usuario' => [
                'promedio' => 85,
            ],
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('error.codigo', 'acceso_denegado');
    }

    public function test_api01_consultas_generales_son_publicas_para_visitantes(): void
    {
        $mockClient = $this->createMock(PrologClient::class);
        $mockClient->expects($this->once())
            ->method('consultar')
            ->willReturn([
                'version' => 2,
                'tramite_id' => 'examen_recuperacion',
                'intencion' => 'consultar_requisitos',
                'items' => ['Requisito público general'],
            ]);
        $this->app->instance(PrologClient::class, $mockClient);

        // Sin sesión iniciada (visitante)
        $response = $this->postJson('/chatbot/mensaje', [
            'version'       => 2,
            'pregunta_id'   => 'examen_recuperacion__requisitos',
            'tramite_id'    => 'examen_recuperacion',
            'intencion'     => 'consultar_requisitos',
            'origen_datos'  => 'ninguno',
            'datos_usuario' => [],
        ]);

        $response->assertOk()
            ->assertJsonPath('tipo', 'requisitos')
            ->assertJsonPath('items.0', 'Requisito público general');
    }

    public function test_api01_texto_libre_de_elegibilidad_informa_requerimiento_de_sesion_a_visitante(): void
    {
        // Un visitante escribe texto libre que infiere evaluación de elegibilidad
        $response = $this->postJson('/chatbot/mensaje', [
            'version' => 2,
            'texto'   => '¿Soy elegible para el examen de recuperación?',
        ]);

        $response->assertOk()
            ->assertJsonPath('titulo', 'Inicio de sesión requerido')
            ->assertJsonPath('acciones.0.url', '/login');
    }

    public function test_api02_cambiar_origen_datos_a_sesion_desde_cliente_nunca_confiere_verificacion_institucional(): void
    {
        $estudiante = new User(['name' => 'Carlos Estudiante', 'email' => 'carlos@uees.edu.ec']);

        $mockClient = $this->createMock(PrologClient::class);
        $mockClient->expects($this->once())
            ->method('evaluar')
            ->with($this->callback(function ($payload) {
                // El backend debe forzar origen_datos a manual hacia el motor, ignorando el valor del cliente
                return $payload['origen_datos'] === 'manual';
            }))
            ->willReturn([
                'version' => 2,
                'tramite_id' => 'examen_recuperacion',
                'intencion' => 'evaluar_elegibilidad',
                'resultado' => [
                    'estado' => 'cumple',
                    'motivos' => [['codigo' => 'promedio_ok', 'texto' => 'GPA adecuado']],
                ],
            ]);
        $this->app->instance(PrologClient::class, $mockClient);

        // Cliente envía origen_datos = sesion en el cuerpo HTTP
        $response = $this->actingAs($estudiante)->postJson('/chatbot/mensaje', [
            'version'       => 2,
            'pregunta_id'   => 'examen_recuperacion__elegibilidad',
            'tramite_id'    => 'examen_recuperacion',
            'intencion'     => 'evaluar_elegibilidad',
            'origen_datos'  => 'sesion', // Intento de falsificar procedencia institucional
            'datos_usuario' => [
                'promedio' => 85,
            ],
        ]);

        $response->assertOk();
        // Criterio API-02: Cambiar origen_datos en una petición nunca acredita datos institucionales
        $this->assertEquals('manual', $response->json('meta.origen_datos'));
        $this->assertEquals('declarada', $response->json('meta.verificacion'));
        $this->assertStringContainsString('declarativo', (string) $response->json('aviso'));
    }

    public function test_reporte_de_conversaciones_no_esta_disponible(): void
    {
        $estudiante = new User(['name' => 'Carlos Estudiante', 'email' => 'carlos@uees.edu.ec']);
        $estudiante->id = 5;
        $estudiante->email_verified_at = now();

        $this->actingAs($estudiante)
            ->get('/chatbot/reporte')
            ->assertNotFound();
    }

    public function test_api01_exceso_de_solicitudes_dispara_throttle_429(): void
    {
        $response = null;
        for ($i = 0; $i < 62; $i++) {
            $response = $this->postJson('/chatbot/mensaje', [
                'version' => 2,
                'texto'   => 'consulta general de prueba de carga',
            ]);
            if ($response->getStatusCode() === 429) {
                break;
            }
        }

        $this->assertEquals(429, $response->getStatusCode());
    }

    public function test_api04_combinacion_invalida_de_pregunta_y_tramite_falla_con_422(): void
    {
        $response = $this->postJson('/chatbot/mensaje', [
            'version'       => 2,
            'pregunta_id'   => 'examen_recuperacion__costo',
            'tramite_id'    => 'tercer_registro', // Trámite no coincide con la pregunta
            'intencion'     => 'consultar_costo',
            'origen_datos'  => 'ninguno',
            'datos_usuario' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.codigo', 'datos_invalidos');
        $this->assertStringContainsString('no corresponde a la pregunta', $response->json('error.campos.tramite_id.0'));
    }

    public function test_api04_combinacion_invalida_de_pregunta_e_intencion_falla_con_422(): void
    {
        $response = $this->postJson('/chatbot/mensaje', [
            'version'       => 2,
            'pregunta_id'   => 'examen_recuperacion__costo',
            'tramite_id'    => 'examen_recuperacion',
            'intencion'     => 'consultar_requisitos', // Intención no coincide con __costo
            'origen_datos'  => 'ninguno',
            'datos_usuario' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.codigo', 'datos_invalidos');
        $this->assertStringContainsString('no corresponde a la pregunta', $response->json('error.campos.intencion.0'));
    }

    public function test_api04_pregunta_inexistente_en_catalogo_falla_con_422(): void
    {
        $response = $this->postJson('/chatbot/mensaje', [
            'version'       => 2,
            'pregunta_id'   => 'pregunta_inventada__desconocida',
            'tramite_id'    => 'examen_recuperacion',
            'intencion'     => 'consultar_descripcion',
            'origen_datos'  => 'ninguno',
            'datos_usuario' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.codigo', 'datos_invalidos')
            ->assertJsonPath('error.campos.pregunta_id.0', 'El identificador de pregunta no existe en el catálogo.');
    }

    public function test_api04_consulta_informativa_con_campos_en_datos_usuario_falla_con_422(): void
    {
        $response = $this->postJson('/chatbot/mensaje', [
            'version'       => 2,
            'pregunta_id'   => 'examen_recuperacion__requisitos',
            'tramite_id'    => 'examen_recuperacion',
            'intencion'     => 'consultar_requisitos',
            'origen_datos'  => 'ninguno',
            'datos_usuario' => [
                'promedio' => 85, // No permitido en consultas informativas
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.codigo', 'datos_invalidos')
            ->assertJsonPath('error.campos.datos_usuario.0', 'Las consultas informativas no deben enviar campos en datos_usuario.');
    }

    public function test_api04_evaluacion_con_campo_no_permitido_para_el_tramite_falla_con_422(): void
    {
        $estudiante = new User(['name' => 'Carlos Estudiante', 'email' => 'carlos@uees.edu.ec']);

        $response = $this->actingAs($estudiante)->postJson('/chatbot/mensaje', [
            'version'       => 2,
            'pregunta_id'   => 'examen_recuperacion__elegibilidad',
            'tramite_id'    => 'examen_recuperacion',
            'intencion'     => 'evaluar_elegibilidad',
            'origen_datos'  => 'manual',
            'datos_usuario' => [
                'promedio'             => 85,
                'attended_as_listener' => true, // Campo de registro extemporáneo, inválido para examen_recuperacion
            ],
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString(
            'no está permitido para el trámite',
            $response->json('error.campos')['datos_usuario.attended_as_listener'][0]
        );
    }

    public function test_api05_consultar_costo_desconocido_se_representa_como_por_confirmar_y_no_gratuito(): void
    {
        $mockClient = $this->createMock(PrologClient::class);
        $mockClient->expects($this->once())
            ->method('consultar')
            ->willReturn([
                'version'    => 2,
                'tramite_id' => 'examen_recuperacion',
                'intencion'  => 'consultar_costo',
                'costo'      => null,
                'aviso'      => null,
            ]);
        $this->app->instance(PrologClient::class, $mockClient);

        $response = $this->postJson('/chatbot/mensaje', [
            'version'       => 2,
            'pregunta_id'   => 'examen_recuperacion__costo',
            'tramite_id'    => 'examen_recuperacion',
            'intencion'     => 'consultar_costo',
            'origen_datos'  => 'ninguno',
            'datos_usuario' => [],
        ]);

        $response->assertOk()
            ->assertJsonPath('titulo', 'Costo por confirmar')
            ->assertJsonPath('costo', null);
        $this->assertStringContainsString('por confirmar con la Facultad', $response->json('mensaje'));
        $this->assertNotNull($response->json('aviso'));
    }

    public function test_api05_consultar_plazo_desconocido_se_representa_como_por_confirmar(): void
    {
        $mockClient = $this->createMock(PrologClient::class);
        $mockClient->expects($this->once())
            ->method('consultar')
            ->willReturn([
                'version'    => 2,
                'tramite_id' => 'examen_recuperacion',
                'intencion'  => 'consultar_plazo',
                'plazo'      => null,
                'aviso'      => null,
            ]);
        $this->app->instance(PrologClient::class, $mockClient);

        $response = $this->postJson('/chatbot/mensaje', [
            'version'       => 2,
            'pregunta_id'   => 'examen_recuperacion__plazo',
            'tramite_id'    => 'examen_recuperacion',
            'intencion'     => 'consultar_plazo',
            'origen_datos'  => 'ninguno',
            'datos_usuario' => [],
        ]);

        $response->assertOk()
            ->assertJsonPath('titulo', 'Plazos por confirmar')
            ->assertJsonPath('plazo', null);
        $this->assertStringContainsString('por confirmar con la Facultad', $response->json('mensaje'));
        $this->assertNotNull($response->json('aviso'));
    }

    public function test_api05_evaluacion_requiere_revision_conserva_motivos_y_aviso_del_motor(): void
    {
        $estudiante = new User(['name' => 'Carlos Estudiante', 'email' => 'carlos@uees.edu.ec']);

        $mockClient = $this->createMock(PrologClient::class);
        $mockClient->expects($this->once())
            ->method('evaluar')
            ->willReturn([
                'version'    => 2,
                'tramite_id' => 'examen_recuperacion',
                'intencion'  => 'evaluar_elegibilidad',
                'resultado'  => [
                    'estado'  => 'requiere_revision',
                    'motivos' => [
                        ['codigo' => 'revision_facultad', 'texto' => 'Falta confirmar materias excluidas por la Facultad.'],
                    ],
                ],
                'aviso'      => 'La Facultad debe confirmar excepciones reglamentarias.',
            ]);
        $this->app->instance(PrologClient::class, $mockClient);

        $response = $this->actingAs($estudiante)->postJson('/chatbot/mensaje', [
            'version'       => 2,
            'pregunta_id'   => 'examen_recuperacion__elegibilidad',
            'tramite_id'    => 'examen_recuperacion',
            'intencion'     => 'evaluar_elegibilidad',
            'origen_datos'  => 'manual',
            'datos_usuario' => [
                'promedio' => 70,
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('titulo', 'Revisión por la Facultad requerida')
            ->assertJsonPath('resultado.estado', 'requiere_revision')
            ->assertJsonPath('resultado.motivos.0.codigo', 'revision_facultad')
            ->assertJsonPath('resultado.motivos.0.texto', 'Falta confirmar materias excluidas por la Facultad.');
        $this->assertStringContainsString('La Facultad debe confirmar excepciones', (string) $response->json('aviso'));
    }

    public function test_evaluacion_con_datos_declarados_no_presenta_resultado_como_definitivo(): void
    {
        $estudiante = new User(['name' => 'Carlos Estudiante', 'email' => 'carlos@uees.edu.ec']);

        $mockClient = $this->createMock(PrologClient::class);
        $mockClient->expects($this->once())
            ->method('evaluar')
            ->willReturn([
                'version'    => 2,
                'tramite_id' => 'examen_recuperacion',
                'intencion'  => 'evaluar_elegibilidad',
                'resultado'  => [
                    'estado'  => 'cumple',
                    'motivos' => [['codigo' => 'promedio_ok', 'texto' => 'Promedio suficiente']],
                ],
            ]);
        $this->app->instance(PrologClient::class, $mockClient);

        $response = $this->actingAs($estudiante)->postJson('/chatbot/mensaje', [
            'version'       => 2,
            'pregunta_id'   => 'examen_recuperacion__elegibilidad',
            'tramite_id'    => 'examen_recuperacion',
            'intencion'     => 'evaluar_elegibilidad',
            'origen_datos'  => 'manual',
            'datos_usuario' => ['promedio' => 85],
        ]);

        $response->assertOk();
        $this->assertStringContainsString('orientativo y no definitivo', $response->json('mensaje'));
        $this->assertStringContainsString('Facultad', $response->json('mensaje'));
    }

    public function test_evaluacion_parcial_aclara_condiciones_restantes_por_confirmar(): void
    {
        $estudiante = new User(['name' => 'Carlos Estudiante', 'email' => 'carlos@uees.edu.ec']);

        $mockClient = $this->createMock(PrologClient::class);
        $mockClient->expects($this->once())
            ->method('evaluar')
            ->willReturn([
                'version'    => 2,
                'tramite_id' => 'examen_recuperacion',
                'intencion'  => 'evaluar_elegibilidad',
                'resultado'  => [
                    'estado'  => 'requiere_revision',
                    'motivos' => [
                        ['codigo' => 'promedio_ok', 'texto' => 'El promedio declarado (80) alcanza el mínimo de 75.'],
                        ['codigo' => 'condiciones_no_evaluadas', 'texto' => 'No se han comprobado plazos.'],
                    ],
                ],
            ]);
        $this->app->instance(PrologClient::class, $mockClient);

        $response = $this->actingAs($estudiante)->postJson('/chatbot/mensaje', [
            'version'       => 2,
            'pregunta_id'   => 'examen_recuperacion__elegibilidad',
            'tramite_id'    => 'examen_recuperacion',
            'intencion'     => 'evaluar_elegibilidad',
            'origen_datos'  => 'manual',
            'datos_usuario' => ['promedio' => 80],
        ]);

        $response->assertOk();
        $this->assertStringContainsString('Solo se evaluó una parte de los requisitos', $response->json('mensaje'));
    }

    public function test_acciones_hacia_portal_y_ficha_del_tramite(): void
    {
        $mockClient = $this->createMock(PrologClient::class);
        $mockClient->expects($this->once())
            ->method('consultar')
            ->willReturn([
                'version'    => 2,
                'tramite_id' => 'examen_recuperacion',
                'intencion'  => 'consultar_descripcion',
                'items'      => ['Descripción de prueba'],
            ]);
        $this->app->instance(PrologClient::class, $mockClient);

        $response = $this->postJson('/chatbot/mensaje', [
            'version'       => 2,
            'pregunta_id'   => 'examen_recuperacion__descripcion',
            'tramite_id'    => 'examen_recuperacion',
            'intencion'     => 'consultar_descripcion',
            'origen_datos'  => 'ninguno',
            'datos_usuario' => [],
        ]);

        $response->assertOk();
        $acciones = $response->json('acciones');
        $this->assertNotEmpty($acciones);
        $this->assertEquals('iniciar_solicitud', $acciones[0]['tipo']);
        $this->assertEquals('/estudiante/solicitudes/recuperacion/nueva', $acciones[0]['url']);
        $this->assertEquals('url', $acciones[1]['tipo']);
        $this->assertEquals('/estudiante/tramites/examen-recuperacion', $acciones[1]['url']);
    }

    public function test_transformacion_documentos_con_obligacion(): void
    {
        $mockClient = $this->createMock(PrologClient::class);
        $mockClient->expects($this->once())
            ->method('consultar')
            ->willReturn([
                'version'    => 2,
                'tramite_id' => 'examen_recuperacion',
                'intencion'  => 'consultar_documentos',
                'documentos' => [
                    [
                        'id' => 'doc1',
                        'nombre' => 'Solicitud',
                        'descripcion' => 'Desc',
                        'obligatorio' => true,
                        'formato' => 'PDF',
                        'url' => null,
                    ],
                    [
                        'id' => 'doc2',
                        'nombre' => 'Respaldo',
                        'descripcion' => 'Desc',
                        'obligatorio' => false,
                        'formato' => 'PDF',
                        'url' => null,
                    ],
                ],
            ]);
        $this->app->instance(PrologClient::class, $mockClient);

        $response = $this->postJson('/chatbot/mensaje', [
            'version'       => 2,
            'pregunta_id'   => 'examen_recuperacion__documentos',
            'tramite_id'    => 'examen_recuperacion',
            'intencion'     => 'consultar_documentos',
            'origen_datos'  => 'ninguno',
            'datos_usuario' => [],
        ]);

        $response->assertOk();
        $docs = $response->json('documentos');
        $this->assertEquals('obligatorio', $docs[0]['obligacion']);
        $this->assertEquals('por_confirmar', $docs[1]['obligacion']);
    }

    public function test_acciones_hacia_portal_para_ayudante_suficiencia_y_supletorio(): void
    {
        $mockClient = $this->createMock(PrologClient::class);
        $mockClient->method('consultar')
            ->willReturnCallback(function (array $payload) {
                return [
                    'version'    => 2,
                    'tramite_id' => $payload['tramite_id'],
                    'intencion'  => $payload['intencion'],
                    'items'      => ['Detalle de prueba'],
                ];
            });
        $this->app->instance(PrologClient::class, $mockClient);

        // 1. Ayudante de cátedra
        $respAyudante = $this->postJson('/chatbot/mensaje', [
            'version'       => 2,
            'pregunta_id'   => 'ayudante_catedra__descripcion',
            'tramite_id'    => 'ayudante_catedra',
            'intencion'     => 'consultar_descripcion',
            'origen_datos'  => 'ninguno',
            'datos_usuario' => [],
        ])->assertOk();
        $accionesAyudante = $respAyudante->json('acciones');
        $this->assertEquals('/estudiante/solicitudes/nueva?tramite=ayudante', $accionesAyudante[0]['url']);
        $this->get('/estudiante/solicitudes/nueva?tramite=ayudante')->assertOk();
        // Fallback/alias también debe responder 200 sin 404
        $this->get('/estudiante/solicitudes/nueva?tramite=ayudante-catedra')->assertOk();

        // 2. Examen de suficiencia
        $respSuficiencia = $this->postJson('/chatbot/mensaje', [
            'version'       => 2,
            'pregunta_id'   => 'examen_suficiencia__descripcion',
            'tramite_id'    => 'examen_suficiencia',
            'intencion'     => 'consultar_descripcion',
            'origen_datos'  => 'ninguno',
            'datos_usuario' => [],
        ])->assertOk();
        $accionesSuficiencia = $respSuficiencia->json('acciones');
        $this->assertEquals('/estudiante/solicitudes/nueva?tramite=suficiencia', $accionesSuficiencia[0]['url']);
        $this->get('/estudiante/solicitudes/nueva?tramite=suficiencia')->assertOk();
        $this->get('/estudiante/solicitudes/nueva?tramite=examen-suficiencia')->assertOk();

        // 3. Examen supletorio
        $respSupletorio = $this->postJson('/chatbot/mensaje', [
            'version'       => 2,
            'pregunta_id'   => 'examen_supletorio__descripcion',
            'tramite_id'    => 'examen_supletorio',
            'intencion'     => 'consultar_descripcion',
            'origen_datos'  => 'ninguno',
            'datos_usuario' => [],
        ])->assertOk();
        $accionesSupletorio = $respSupletorio->json('acciones');
        $this->assertEquals('/estudiante/solicitudes/supletorio/nueva', $accionesSupletorio[0]['url']);
        $this->get('/estudiante/solicitudes/supletorio/nueva')->assertOk();
        $this->get('/estudiante/solicitudes/nueva?tramite=supletorio')->assertOk();
        $this->get('/estudiante/solicitudes/nueva?tramite=examen-supletorio')->assertOk();
    }

    public function test_con_datos_verificados_es_false_cuando_el_origen_es_sesion(): void
    {
        $mockClient = $this->createMock(PrologClient::class);
        $mockClient->expects($this->once())
            ->method('evaluar')
            ->willReturn([
                'version'    => 2,
                'tramite_id' => 'examen_recuperacion',
                'intencion'  => 'evaluar_elegibilidad',
                'resultado'  => [
                    'estado'  => 'cumple',
                    'motivos' => [['codigo' => 'promedio_ok', 'texto' => 'Cumple promedio']],
                ],
            ]);
        $this->app->instance(PrologClient::class, $mockClient);

        $estudiante = new User(['name' => 'Ana', 'email' => 'ana@uees.edu.ec']);

        // Se envía solicitud autenticada con origen sesion
        $response = $this->actingAs($estudiante)->postJson('/chatbot/mensaje', [
            'version'       => 2,
            'pregunta_id'   => 'examen_recuperacion__elegibilidad',
            'tramite_id'    => 'examen_recuperacion',
            'intencion'     => 'evaluar_elegibilidad',
            'origen_datos'  => 'sesion',
            'datos_usuario' => ['promedio' => 88],
        ]);

        $response->assertOk();
        $this->assertFalse($response->json('resultado.con_datos_verificados'));
        $this->assertEquals('declarada', $response->json('meta.verificacion'));
    }
}
