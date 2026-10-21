<x-chat.pagina titulo="Pruebas del asistente">
    <p>Modo de pruebas local: las comprobaciones son opcionales y usan los datos que ingreses.</p>
    <x-chat-widget :tramite="request('tramite')" :simulado="false" :url-mensaje="route('chatbot.pruebas.mensaje')" />
</x-chat.pagina>
