<?php

namespace Tests\Feature\Chatbot;

use App\Services\Chatbot\PrologService;
use App\Services\Chatbot\ChatbotCatalogoService;
use App\Services\Chatbot\Prolog\PrologClient;
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
                return $payload['version'] === 2
                    && $payload['tramite_id'] === 'examen_recuperacion'
                    && $payload['intencion'] === 'evaluar_elegibilidad'
                    && $payload['perfil']['promedio'] === 80;
            }))
            ->willReturn([
                'version' => 2,
                'resultado' => ['estado' => 'cumple', 'motivos' => []],
            ]);

        $catalogoService = new ChatbotCatalogoService();
        $servicio = new PrologService($mockClient, $catalogoService);

        $resultado = $servicio->evaluar('examen_recuperacion', ['promedio' => 80]);
        $this->assertEquals('cumple', $resultado['resultado']['estado']);
    }

    public function test_evaluar_admite_objeto_solicitud(): void
    {
        $mockClient = $this->createMock(PrologClient::class);
        $mockClient->expects($this->once())
            ->method('evaluar')
            ->with($this->callback(function ($payload) {
                return $payload['tramite_id'] === 'examen_recuperacion'
                    && $payload['con_datos_verificados'] === true;
            }))
            ->willReturn([
                'version' => 2,
                'resultado' => ['estado' => 'cumple', 'motivos' => []],
            ]);

        $catalogoService = new ChatbotCatalogoService();
        $servicio = new PrologService($mockClient, $catalogoService);

        $solicitudFicticia = (object) [
            'tramite' => 'examen_recuperacion',
            'detalle' => ['gpa_periodo' => 85],
            'materias' => [
                ['codigo' => 'MAT101', 'asistencia' => 90],
            ],
            'user_id' => 10,
        ];

        $resultado = $servicio->evaluar($solicitudFicticia, conDatosVerificados: true);
        $this->assertEquals('cumple', $resultado['resultado']['estado']);
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
