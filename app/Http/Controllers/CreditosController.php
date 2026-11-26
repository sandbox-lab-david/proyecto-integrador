<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CreditosController extends Controller
{
    public function __invoke(): View
    {
        return view('creditos', ['creditos' => Str::markdown($this->seccionDeCreditos())]);
    }

    private function seccionDeCreditos(): string
    {
        $ruta = base_path('README.md');
        $readme = File::exists($ruta) ? File::get($ruta) : '';

        $inicio = mb_strpos($readme, "\n## Créditos");

        if ($inicio === false) {
            return '';
        }

        $seccion = mb_substr($readme, $inicio + mb_strlen("\n## Créditos"));
        $fin = mb_strpos($seccion, "\n## ");

        if ($fin !== false) {
            $seccion = mb_substr($seccion, 0, $fin);
        }

        return trim(preg_replace('/\n---\s*$/', '', rtrim($seccion)));
    }
}
