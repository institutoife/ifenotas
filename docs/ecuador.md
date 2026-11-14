# IFE Notas Ecuador

`/` conserva su portada original (hero, contador, TikTok, apoyo escolar, estadísticas, servicios, redes y pie de página). Solo se añade «Elige tu país» después del hero. Cada opción enlaza a una página propia; no se insertan simuladores en la portada. «Cambiar país» regresa a `/`, también para estudiantes con sesión iniciada. El contador mantiene su lógica habitual y todos los registros y ajustes existentes se conservan.

Los países se definen en `config/ife.php`, bajo `countries`: nombre, bandera, imagen local opcional, nombre de ruta y destino de WhatsApp. Una entrada adicional genera automáticamente su enlace en la portada; la ruta debe existir y apuntar al simulador adecuado. Ecuador también define el parcial de su formulario, provincias y materias en esta configuración.

- Bolivia: `/simulador-notas` (`notes.simulator`). Los botones de ambos modos permanecen visibles y conservan la primera nota al cambiar. Los accesos `/simulador-notas?mode=one` y `/simulador-notas?mode=two` continúan seleccionando directamente su modo. Se reutilizan el motor de 0 a 100, la consulta, los resultados y WhatsApp existentes.
- Ecuador: `/ecuador/simulador-notas` (`notes.ecuador`). Se elige directamente uno de los dos modos, sin pedir régimen escolar. El orden es notas, provincia, materia y «Calcular». Cambiar de modo conserva la primera nota real y el contexto ingresado. En dos notas, provincia y materia son obligatorias. «Otra» exige escribir el nombre. En una nota, la materia es opcional y la segunda nota se identifica siempre como simulada.

## Apariencia clásica compartida

Ambas páginas usan `layouts/notes-simulator.blade.php`, el mismo estilo `partials/grade-simulator-styles`, el selector `partials/simulator-mode-picker`, la barra `partials/simulator-slider` y el componente de apoyo escolar existente. Ecuador mantiene cabecera, fondo, campos grandes, tarjetas de resultado, botón verde de WhatsApp y «Compartir nota» del clásico. Solo ajusta la tipografía numérica para que entren notas como `10,00` en móvil. No se carga su anterior formulario de estilo diferente ni se cambia la portada.

En una nota, la barra simula el segundo trimestre en 0–10 y el valor exacto editable está en la tarjeta «SIMULADO», sincronizado con ella. El valor inicial es el mínimo que permite alcanzar 7,00 si en el tercero se obtiene 10,00. El resultado y el apoyo se actualizan al instante.

En dos notas, después de «Calcular», la misma barra permite simular el tercer trimestre en 0–10. El círculo se coloca inicialmente en la nota mínima necesaria (o 10,00 si no alcanza), el marcador identifica ese mínimo y la barra separa rojo/verde. La comparación de aprobación se hace con enteros, sin redondear promedios. Si no alcanza, el título es «APLAZADO», como en Bolivia, y se conserva visible el promedio máximo posible.

El CTA y las recomendaciones reutilizan los mensajes de Bolivia, sin copiar una fecha de cierre escolar no configurada para Ecuador. Las consultas ecuatorianas no se insertan en los registros ni en las estadísticas de Bolivia.

Listados de referencia configurables: provincias contrastadas con [INEC, Censo 2022](https://www.censoecuador.gob.ec/public/Boletin_Nacional.htm) y asignaturas del [currículo priorizado del Ministerio de Educación](https://educacion.gob.ec/wp-content/uploads/downloads/2025/07/Curriculo-Priorizado-Elemental.pdf). Se ofrecen materias de EGB y Bachillerato.

Variables opcionales de entorno:

```dotenv
IFE_BOLIVIA_WHATSAPP=59160902299
```

Para Ecuador, configurar `IFE_ECUADOR_WHATSAPP` con el número internacional completo cuando se disponga del destino. Si una variable no está definida, se usa el contacto de apoyo escolar existente (`IFE_NOTAS_SUPPORT_WHATSAPP`). Para usar ese destino predeterminado, omitir `IFE_ECUADOR_WHATSAPP` (no dejarla vacía). No se inventó un número local ecuatoriano.

`IfeWhatsApp::messageUrl` reutiliza la normalización y construcción de enlaces existentes. El navegador codifica provincia, materia, notas y resultado y abre WhatsApp para que el estudiante revise y envíe el mensaje; la aplicación no envía mensajes automáticamente ni pide/inventa un régimen. En una nota se indica explícitamente que el segundo valor es simulado, sin atribuirlo a una nota real.

`public/js/ife-ecuador-engine.js` convierte coma o punto decimal a centésimas enteras y calcula `2100 - primerTrimestre - segundoTrimestre`. El mínimo es 700 centésimas de promedio exacto, con tres trimestres de igual peso. No se afirma que el resultado determine la promoción del curso: siempre se presenta por materia.

El resultado y WhatsApp reutilizan los nombres y mensajes de situación de `IfeGradeEngine` (Bolivia), normalizando únicamente la nota requerida para su presentación (`requiredCents / 10` en escala de 0 a 100). Esta descripción no participa en el cálculo ni altera el mínimo exacto de Ecuador. El mensaje imposible menciona 10,00, nunca 100. Dos notas de 10,00 aún requieren 1,00 en el tercero; no se presenta falsamente «ya pasaste».

Cuando la nota requerida supera 1000 centésimas, se muestra el promedio máximo con 10,00 en el tercero, redondeado a dos decimales solo para visualización. Un promedio que redondee a 7,00 pero quede por debajo del mínimo exacto sigue marcado como insuficiente y se explica esa diferencia.

El modo de una nota identifica siempre el segundo trimestre como simulado. Sus mensajes de WhatsApp son condicionales. El modo de dos notas calcula al pulsar «Calcular», invalida el resultado si se cambian las notas y conserva los dos valores como reales. Ecuador no inserta datos simulados en los registros estadísticos de Bolivia ni agrega consultas o contadores independientes.

Pruebas:

```powershell
php artisan test
node --test --test-isolation=none tests/js/ecuador-engine.test.mjs
```

La prueba de navegador `node tests/js/ecuador.browser.mjs` requiere Chrome y un servidor local ya iniciado. Usa `IFE_TEST_BASE_URL` para elegir su URL (predeterminado `http://127.0.0.1:8001`). Ejecutarla contra una base de datos de pruebas aislada: abrir `/` incrementa el contador real de esa base, igual que cualquier visita. Comprueba los dos modos en 320, 360, 390, 412, 430, 768 y 1280 px, compara los estilos calculados de Ecuador y Bolivia y guarda capturas en `storage/app/ecuador-qa`. Valida extremos, barras, mensajes y enlaces de WhatsApp sin enviar mensajes ni guardar consultas artificiales.
