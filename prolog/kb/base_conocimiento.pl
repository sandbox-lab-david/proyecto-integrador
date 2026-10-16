% Base de conocimiento inicial - Portal de Notas
materia(programacion_web, 5).
materia(inteligencia_artificial, 6).
materia(sistemas_expertos, 6).

% Regla de aprobacion
aprobado(Nota) :-
    Nota >= 7.