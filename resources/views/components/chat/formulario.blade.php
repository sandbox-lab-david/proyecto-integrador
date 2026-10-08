<form {{ $attributes->class('cb-form') }} data-cb="formulario" novalidate>
    <p class="cb-resumen-error" data-cb="form-error" role="alert" hidden>Revisa los campos marcados.</p>
    <div class="cb-form-campos" data-cb="form-campos"></div>
    <button type="submit" class="cb-btn cb-btn-primario">Revisar</button>
</form>
