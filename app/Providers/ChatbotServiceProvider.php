<?php

namespace App\Providers;

use App\Services\Chatbot\Prolog\HttpPrologClient;
use App\Services\Chatbot\Prolog\PrologClient;
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
        $this->app->bind(
            PrologClient::class,
            fn () => HttpPrologClient::desdeConfig()
        );

        $this->app->bind(
            \App\Services\Chatbot\PrologService::class,
            fn (\Illuminate\Contracts\Foundation\Application $app) => new \App\Services\Chatbot\PrologService(
                $app->make(\App\Services\Chatbot\Prolog\PrologClient::class),
                $app->make(\App\Services\Chatbot\ChatbotCatalogoService::class),
            )
        );
    }

    public function boot(): void
    {
        //
    }
}
