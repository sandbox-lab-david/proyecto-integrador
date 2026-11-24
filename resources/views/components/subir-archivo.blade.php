{{--
    Campo para elegir archivos, con validación en el navegador y vista previa (contrato C56).

    <x-subir-archivo name="archivo" accept="pdf,jpg,png" max="10240" />

    accept  Extensiones separadas por coma, las mismas que recibe ArchivoService::guardar().
    max     Tamaño máximo de cada archivo, en KB.

    Opcionales: etiqueta, multiple y :vista-previa="false". Los demás atributos
    (id, required…) pasan al <input>.

    Los archivos que no cumplen se quitan del campo y se avisa debajo. Con los que
    quedan se emite el evento «subir-archivo:cambio» (detail.archivos), que sube por el
    DOM. El servidor siempre vuelve a validar con ArchivoService.
--}}
@props([
    'name',
    'accept' => 'pdf,jpg,png',
    'max' => 10240,
    'etiqueta' => null,
    'multiple' => false,
    'vistaPrevia' => true,
])

@php
    $max = (int) $max;
    $extensiones = collect(explode(',', strtolower($accept)))
        ->map(fn (string $extension) => ltrim(trim($extension), '.'))
        ->map(fn (string $extension) => $extension === 'jpeg' ? 'jpg' : $extension)
        ->filter()
        ->unique()
        ->values();

    $id = $attributes->get('id', 'archivo-'.\Illuminate\Support\Str::slug($name));
    $tipos = $extensiones->map(fn (string $extension) => strtoupper($extension))->join(', ', ' o ');
    $tamano = $max >= 1024 ? round($max / 1024, 1).' MB' : $max.' KB';
@endphp

<div class="subir-archivo" data-subir-archivo
    data-extensiones="{{ $extensiones->implode(',') }}"
    data-max-kb="{{ $max }}"
    data-tipos="{{ $tipos }}"
    data-tamano="{{ $tamano }}">

    <label for="{{ $id }}">{{ $etiqueta ?? ($multiple ? 'Agregar archivos' : 'Elegir archivo') }}</label>

    <input {{ $attributes->merge(['id' => $id]) }} type="file" name="{{ $name }}"
        accept="{{ $extensiones->flatMap(fn (string $e) => $e === 'jpg' ? ['.jpg', '.jpeg'] : ['.'.$e])->implode(',') }}"
        aria-describedby="{{ $id }}-ayuda" @if ($multiple) multiple @endif>

    <p id="{{ $id }}-ayuda" class="subir-archivo__ayuda">
        {{ $tipos }}. Máximo {{ $tamano }}{{ $multiple ? ' por archivo' : '' }}.
    </p>

    <p class="subir-archivo__error" role="alert" data-subir-archivo-error></p>

    @if ($vistaPrevia)
        <ul class="subir-archivo__vista" data-subir-archivo-vista aria-label="Archivos elegidos"></ul>
    @endif
</div>

@once
    <style>
        .subir-archivo {
            padding: 20px;
            border: 1px dashed #b8c1ce;
            border-radius: 10px;
            background: #f5f6fa;
        }

        .subir-archivo label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: bold;
        }

        .subir-archivo input[type="file"] {
            width: 100%;
            max-width: 100%;
            padding: 14px;
            border: 1px solid #b8c1ce;
            border-radius: 9px;
            font: inherit;
            font-size: 14px;
            background: white;
        }

        .subir-archivo__ayuda { margin: 8px 0 0; font-size: 12px; line-height: 1.6; color: #596579; }
        .subir-archivo__error { margin: 12px 0 0; font-size: 14px; line-height: 1.6; color: #b42318; }
        .subir-archivo__error:empty { display: none; }

        .subir-archivo__vista {
            display: grid;
            gap: 8px;
            list-style: none;
            padding: 0;
            margin: 12px 0 0;
        }

        .subir-archivo__vista:empty { display: none; }

        .subir-archivo__vista li {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px;
            border: 1px solid #e0e4eb;
            border-radius: 9px;
            background: white;
            font-size: 14px;
        }

        .subir-archivo__vista img,
        .subir-archivo__tipo {
            width: 48px;
            height: 48px;
            flex-shrink: 0;
            border-radius: 6px;
        }

        .subir-archivo__vista img { object-fit: cover; }

        .subir-archivo__tipo {
            display: grid;
            place-items: center;
            background: #f6e8ed;
            color: #781c35;
            font-size: 11px;
            font-weight: bold;
        }

        .subir-archivo__nombre { display: block; font-weight: bold; overflow-wrap: anywhere; }
        .subir-archivo__tamano { font-size: 12px; color: #596579; }
    </style>

    <script>
        (() => {
            const FIRMAS = {
                pdf: [0x25, 0x50, 0x44, 0x46],
                png: [0x89, 0x50, 0x4E, 0x47, 0x0D, 0x0A, 0x1A, 0x0A],
                jpg: [0xFF, 0xD8, 0xFF]
            };

            // Revisa los primeros bytes: no basta con la extensión del nombre.
            async function extensionReal(archivo) {
                const bytes = new Uint8Array(await archivo.slice(0, 8).arrayBuffer());

                for (const [extension, firma] of Object.entries(FIRMAS)) {
                    if (firma.every((valor, i) => bytes[i] === valor)) return extension;
                }

                // Los demás formatos (docx, xlsx, csv…) solo los puede confirmar el servidor.
                const extension = archivo.name.split('.').pop().toLowerCase();

                return extension in FIRMAS || extension === 'jpeg' ? null : extension;
            }

            function tamanoLegible(bytes) {
                return bytes < 1024 * 1024
                    ? `${Math.max(1, Math.round(bytes / 1024))} KB`
                    : `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
            }

            function pintarVista(lista, archivos) {
                lista.querySelectorAll('img').forEach(imagen => URL.revokeObjectURL(imagen.src));
                lista.innerHTML = '';

                archivos.forEach(({ archivo, extension }) => {
                    const fila = document.createElement('li');
                    let miniatura;

                    if (extension === 'jpg' || extension === 'png') {
                        miniatura = document.createElement('img');
                        miniatura.src = URL.createObjectURL(archivo);
                        miniatura.alt = '';
                    } else {
                        miniatura = document.createElement('span');
                        miniatura.className = 'subir-archivo__tipo';
                        miniatura.textContent = extension.toUpperCase();
                    }

                    const datos = document.createElement('span');
                    const nombre = document.createElement('span');
                    const tamano = document.createElement('span');
                    nombre.className = 'subir-archivo__nombre';
                    nombre.textContent = archivo.name;
                    tamano.className = 'subir-archivo__tamano';
                    tamano.textContent = tamanoLegible(archivo.size);
                    datos.append(nombre, tamano);

                    fila.append(miniatura, datos);
                    lista.append(fila);
                });
            }

            document.addEventListener('change', async evento => {
                const entrada = evento.target;
                const campo = entrada instanceof HTMLInputElement && entrada.type === 'file'
                    ? entrada.closest('[data-subir-archivo]')
                    : null;

                if (!campo) return;

                const permitidas = campo.dataset.extensiones.split(',');
                const maximo = Number(campo.dataset.maxKb) * 1024;
                const validos = [];
                const rechazados = [];

                for (const archivo of [...entrada.files]) {
                    if (archivo.size > maximo) {
                        rechazados.push(`${archivo.name} supera los ${campo.dataset.tamano}`);
                        continue;
                    }

                    const extension = await extensionReal(archivo);

                    if (!permitidas.includes(extension)) {
                        rechazados.push(`${archivo.name} no es un ${campo.dataset.tipos} válido`);
                        continue;
                    }

                    validos.push({ archivo, extension });
                }

                // El campo se queda solo con los archivos que cumplen.
                const transferencia = new DataTransfer();
                validos.forEach(({ archivo }) => transferencia.items.add(archivo));
                entrada.files = transferencia.files;

                campo.querySelector('[data-subir-archivo-error]').textContent = rechazados.length
                    ? `No se agregaron: ${rechazados.join('; ')}.`
                    : '';

                const vista = campo.querySelector('[data-subir-archivo-vista]');
                if (vista) pintarVista(vista, validos);

                campo.dispatchEvent(new CustomEvent('subir-archivo:cambio', {
                    bubbles: true,
                    detail: { archivos: validos.map(({ archivo }) => archivo), rechazados }
                }));
            });
        })();
    </script>
@endonce
