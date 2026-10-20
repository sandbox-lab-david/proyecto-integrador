<x-chat.pagina titulo="Pruebas del asistente">
    <p>Modo de pruebas local: las evaluaciones son orientativas y usan los datos que ingreses.</p>
    <x-chat-widget :tramite="request('tramite')" :simulado="false" :url-mensaje="route('chatbot.pruebas.mensaje')" />
</x-chat.pagina>
