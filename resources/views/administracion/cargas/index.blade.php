@extends('layouts.app')

@section('titulo', 'Importaciones SIAC')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-normal">
        <div>
            <p class="ui-antetitulo">Datos</p>
            <h1 class="ui-titulo">Importaciones SIAC</h1>
            <p class="ui-texto-gris mt-chico">Carga los archivos exportados del SIAC. Antes de guardar verás una vista previa con las filas válidas, duplicadas y con error.</p>
        </div>
        <a href="{{ route('cargas.historial') }}" class="ui-btn ui-btn-secundario">Ver historial</a>
    </div>

    <div class="grid gap-amplio mt-amplio lg:grid-cols-2 items-start">
        <section class="ui-tarjeta">
            <p class="ui-etiqueta ui-etiqueta-vino">Paso 1</p>
            <h2 class="ui-subtitulo">Descarga la plantilla</h2>
            <p class="ui-texto-gris">Usa la plantilla del tipo de información que vas a registrar, con los encabezados esperados.</p>
            <div class="ui-acciones">
                <a href="{{ route('cargas.plantilla', 'estudiantes') }}" class="ui-btn ui-btn-secundario ui-btn-chico">Plantilla de estudiantes</a>
                <a href="{{ route('cargas.plantilla', 'notas') }}" class="ui-btn ui-btn-secundario ui-btn-chico">Plantilla de notas</a>
                <a href="{{ route('cargas.plantilla', 'mallas') }}" class="ui-btn ui-btn-secundario ui-btn-chico">Plantilla de mallas</a>
            </div>
        </section>

        <section class="ui-tarjeta">
            <p class="ui-etiqueta ui-etiqueta-vino">Paso 2</p>
            <h2 class="ui-subtitulo">Sube el archivo</h2>
            <form action="{{ route('cargas.preview') }}" method="POST" enctype="multipart/form-data" class="ui-form">
                @csrf
                <div class="ui-campo @error('tipo_carga') ui-campo-error @enderror">
                    <label for="tipo_carga">Tipo de carga</label>
                    <select name="tipo_carga" id="tipo_carga" class="ui-control" required>
                        <option value="">Selecciona el tipo de información</option>
                        <option value="estudiantes" @selected(old('tipo_carga') === 'estudiantes')>Estudiantes</option>
                        <option value="notas" @selected(old('tipo_carga') === 'notas')>Notas</option>
                        <option value="mallas" @selected(old('tipo_carga') === 'mallas')>Mallas curriculares</option>
                    </select>
                    @error('tipo_carga')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="ui-campo @error('archivo_excel') ui-campo-error @enderror">
                    <label for="archivo_excel">Archivo</label>
                    <label class="ui-archivo">
                        <input type="file" name="archivo_excel" id="archivo_excel" accept=".xlsx,.xls,.csv" required>
                        <strong>Elige un archivo</strong>
                        <span class="ui-ayuda">.xlsx, .xls o .csv, hasta 10 MB</span>
                    </label>
                    @error('archivo_excel')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="ui-acciones">
                    <button type="submit" class="ui-btn ui-btn-primario">Cargar y ver vista previa</button>
                </div>
            </form>
        </section>
    </div>
@endsection
