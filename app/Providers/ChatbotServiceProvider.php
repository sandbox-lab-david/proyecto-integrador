<?php

namespace App\Providers;

use App\Services\Chatbot\ChatbotCatalogoService;
use App\Services\Chatbot\Prolog\HttpPrologClient;
use App\Services\Chatbot\Prolog\PrologClient;
use App\Services\Chatbot\PrologService;
use App\Services\Solicitudes\IntegracionEnvio;
use App\Services\Solicitudes\IntegracionEnvioInstitucional;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ChatbotServiceProvider extends ServiceProvider
{
    /**
     * Registra los servicios del módulo Chatbot en el contenedor de Laravel.
     *
     * Al vincular PrologClient::class con HttpPrologClient::desdeConfig(),
     * Laravel resuelve la dependencia automáticamente por inyección en cualquier
     * constructor que declare PrologClient — sin necesidad de hacer `new` manual.
     */
    public function register(): void
    {
        $this->app->bind(IntegracionEnvio::class, IntegracionEnvioInstitucional::class);
        $this->app->bind(
            PrologClient::class,
            fn () => HttpPrologClient::desdeConfig()
        );

        $this->app->bind(
            PrologService::class,
            fn (Application $app) => new PrologService(
                $app->make(PrologClient::class),
                $app->make(ChatbotCatalogoService::class),
            )
        );
    }

    public function boot(): void
    {
        //
    }
}
