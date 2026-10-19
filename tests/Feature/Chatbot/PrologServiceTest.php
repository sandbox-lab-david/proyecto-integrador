<?php

namespace Tests\Feature\Chatbot;

use App\Services\Chatbot\PrologService;
use App\Services\Chatbot\ChatbotCatalogoService;
use App\Services\Chatbot\Prolog\PrologClient;
use InvalidArgumentException;
use Tests\TestCase;

class PrologServiceTest extends TestCase
{
    public function test_consultar_envia_payload_esperado(): void
    {
        $mockClient = $this->createMock(PrologClient::class);
        $mockClient->expects($this->once())
            ->method('consultar')
            ->with([
                'version'    => 2,
                'tramite_id' => 'examen_recuperacion',
                'intencion'  => 'consultar_requisitos',
            ])
            ->willReturn([
                'version' => 2,
                'tramite_id' => 'examen_recuperacion',
                'intencion' => 'consultar_requisitos',
                'items' => ['Requisito de prueba'],
            ]);

        $catalogoService = new ChatbotCatalogoService();
        $servicio = new PrologService($mockClient, $catalogoService);

        $resultado = $servicio->consultar('examen_recuperacion', 'consultar_requisitos');
        $this->assertEquals(2, $resultado['version']);
        $this->assertEquals(['Requisito de prueba'], $resultado['items']);
    }

    public function test_evaluar_admite_perfil_array(): void
    {
        $mockClient = $this->createMock(PrologClient::class);
        $mockClient->expects($this->once())
            ->method('evaluar')
            ->with($this->callback(function ($payload) {
                return array_keys($payload) === ['version', 'tramite_id', 'intencion', 'fecha', 'origen_datos', 'perfil']
                    && $payload['version'] === 2
                    && $payload['tramite_id'] === 'examen_recuperacion'
                    && $payload['intencion'] === 'evaluar_elegibilidad'
                    && $payload['origen_datos'] === 'manual'
                    && $payload['perfil'] === ['promedio' => 80.0];
            }))
            ->willReturn([
                'version' => 2,
                'resultado' => ['estado' => 'requiere_revision', 'motivos' => []],
            ]);

        $catalogoService = new ChatbotCatalogoService();
        $servicio = new PrologService($mockClient, $catalogoService);

        $resultado = $servicio->evaluar('examen_recuperacion', ['promedio' => 80]);
        $this->assertEquals('requiere_revision', $resultado['resultado']['estado']);
    }

    public function test_evaluar_admite_objeto_solicitud(): void
    {
        $mockClient = $this->createMock(PrologClient::class);
        $mockClient->expects($this->once())
            ->method('evaluar')
            ->with($this->callback(function ($payload) {
                return $payload['tramite_id'] === 'examen_recuperacion'
                    && $payload['origen_datos'] === 'manual'
                    && !array_key_exists('con_datos_verificados', $payload)
                    && $payload['perfil'] === [
                        'promedio' => 85.0,
                        'asistencia' => 90.0,
                        'materia_id' => 'MAT101',
                    ];
            }))
            ->willReturn([
                'version' => 2,
                'resultado' => ['estado' => 'requiere_revision', 'motivos' => []],
            ]);

        $catalogoService = new ChatbotCatalogoService();
        $servicio = new PrologService($mockClient, $catalogoService);

        $solicitudFicticia = (object) [
            'tramite' => 'examen_recuperacion',
            'detalle' => ['gpa_periodo' => 85, 'dato_extra' => 'no enviar'],
            'materias' => [
                ['codigo' => 'MAT101', 'asistencia' => 90],
            ],
            'user_id' => 10,
        ];

        $resultado = $servicio->evaluar($solicitudFicticia, conDatosVerificados: true);
        $this->assertEquals('requiere_revision', $resultado['resultado']['estado']);
    }

    public function test_evaluar_rechaza_identificador_no_catalogado_antes_de_llamar_al_motor(): void
    {
        $mockClient = $this->createMock(PrologClient::class);
        $mockClient->expects($this->never())->method('evaluar');

        $servicio = new PrologService($mockClient, new ChatbotCatalogoService());

        $this->expectException(InvalidArgumentException::class);
        $servicio->evaluar((object) ['tipo_tramite_id' => 42, 'detalle' => []]);
    }

    public function test_evaluar_convierte_identificador_numerico_de_materia_a_texto(): void
    {
        $mockClient = $this->createMock(PrologClient::class);
        $mockClient->expects($this->once())
            ->method('evaluar')
            ->with($this->callback(fn ($payload) => $payload['perfil']['materia_id'] === '101'))
            ->willReturn(['resultado' => ['estado' => 'requiere_revision', 'motivos' => []]]);

        $servicio = new PrologService($mockClient, new ChatbotCatalogoService());
        $servicio->evaluar((object) [
            'tramite' => 'examen_recuperacion',
            'detalle' => [],
            'materias' => [['materia_id' => 101]],
        ]);
    }

    public function test_campos_formulario_retorna_campos_del_tramite(): void
    {
        $mockClient = $this->createMock(PrologClient::class);
        $catalogoService = new ChatbotCatalogoService();
        $servicio = new PrologService($mockClient, $catalogoService);

        $campos = $servicio->camposFormulario('examen_recuperacion');
        $this->assertIsArray($campos);
    }
}
