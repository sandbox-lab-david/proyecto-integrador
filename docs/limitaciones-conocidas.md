# Limitaciones conocidas

Comportamientos que se detectaron en las pruebas del sistema y que, por ahora, no se van a cambiar. Se dejan escritos para que nadie los confunda con un error nuevo.

| Módulo | Qué pasa | Detalle |
|---|---|---|
| Chatbot (Grupo 4) | El formulario no frena el **tercer registro** en Medicina y Enfermería | La tabla de carreras ya marca esas dos carreras con `admite_tercer_registro = false`, pero la base de conocimiento en Prolog no tiene la regla "Tu carrera no admite tercer registro". El estudiante puede llenar el trámite sin aviso; la facultad lo detecta al revisarlo |
| Chatbot (Grupo 4) | El chatbot no reconoce algunas preguntas sobre retiros | Por ejemplo, "Quiero retirarme de una materia, ¿hasta cuándo puedo?" no se identifica como Retiro de materia y el chatbot muestra el catálogo general |
| Chatbot (Grupo 4) | El aviso de documentos que ve la asistente está escrito para el estudiante | La "Comprobación automática de documentos" muestra el texto que genera Prolog ("No puedes avanzar al siguiente paso…"), pensado para el formulario del estudiante |
| Chatbot (Grupo 4) | `/chatbot` y `/chatbot/demo` se abren sin iniciar sesión | Están en `routes/chatbot.php` fuera del grupo `SoloEntornoLocal`. Para cerrarlas en producción bastaría moverlas dentro de ese grupo |
| Revisión (Grupo 3) | Tras una corrección, el director ve el documento anterior y el nuevo | Los dos aparecen en el expediente con el mismo nombre; la asistente valida ambos |
| Revisión (Grupo 3) | El director **rechaza** el caso en lugar de **devolverlo a la asistente** | El Word (RF-3.8) dice "Devolver a la asistente con comentario". Queda pendiente de la decisión del profesor |
