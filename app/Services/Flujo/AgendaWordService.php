<?php

namespace App\Services\Flujo;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use App\Models\CorteAgenda;

class AgendaWordService
{
    public function generarAgendaWord(int $corteId)
    {
        $corte = CorteAgenda::with(['solicitudes'])->find($corteId);

        if (!$corte) {
            $corte = CorteAgenda::create([
                'id' => $corteId,
                'facultad_id' => 1,
                'fecha_sesion' => now(),
                'asistentes' => 'Consejo de Facultad - Prueba'
            ]);
            $corte->load(['solicitudes']);
        }

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $section->addTitle('AGENDA DE SESIÓN DE CONSEJO DE FACULTAD', 1);
        $section->addText('Fecha de Sesión: ' . $corte->fecha_sesion);
        $section->addText('Asistentes: ' . $corte->asistentes);
        $section->addTextBreak(1);

        $section->addTitle('Casos Listos para Consejo', 2);
        
        foreach ($corte->solicitudes as $solicitud) {
            $section->addListItem('Caso ID: ' . $solicitud->id);
        }

        $fileName = 'agenda_consejo_' . $corte->id . '.docx';
        $path = storage_path('app/public/' . $fileName);
        
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($path);

        return response()->download($path);
    }
}