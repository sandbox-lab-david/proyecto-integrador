{{--
    Solicitudes guardadas del estudiante. Se incluye solo cuando el seguimiento
    está disponible: usa el badge de estado de G3 (C68) y los modelos de G3 y G4,
    que todavía no están en develop, así que esta vista no se ha ejecutado.

    El badge va como componente dinámico porque Blade busca los componentes al
    compilar: con la etiqueta directa, «view:cache» falla mientras G3 no lo publique.
--}}
@if ($solicitudes->isEmpty())
    <p class="vacio">Todavía no has registrado ninguna solicitud.</p>
@else
    <ul class="lista-solicitudes">
        @foreach ($solicitudes as $solicitud)
            <li>
                <div>
                    <strong>{{ $solicitud->tipoTramite->nombre }}</strong>

                    <span class="dato">
                        {{ $solicitud->codigo }} ·
                        {{ $solicitud->enviada_at
                            ? 'enviada el '.$solicitud->enviada_at->format('d/m/Y')
                            : 'creada el '.$solicitud->created_at->format('d/m/Y') }}
                    </span>

                    <div class="estado-solicitud">
                        <x-dynamic-component component="estado-badge" :estado="$solicitud->estadoSolicitud" vista="estudiante" />
                    </div>
                </div>

                <div class="acciones-solicitud">
                    <a class="boton-fila" href="{{ route('solicitudes.show', $solicitud) }}"
                        aria-label="Ver el seguimiento de la solicitud {{ $solicitud->codigo }}">
                        Ver seguimiento →
                    </a>
                </div>
            </li>
        @endforeach
    </ul>
@endif
