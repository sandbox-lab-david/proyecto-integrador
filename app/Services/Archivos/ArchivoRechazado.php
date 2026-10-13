<?php

namespace App\Services\Archivos;

use RuntimeException;

/**
 * El archivo no cumple lo que se pidió (tipo, tamaño o cantidad). El mensaje
 * está escrito para mostrarse tal cual a quien lo subió.
 */
class ArchivoRechazado extends RuntimeException {}
