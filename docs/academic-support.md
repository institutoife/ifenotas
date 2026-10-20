# Apoyo escolar después del resultado

La clasificación de nueve niveles vive en `public/js/ife-grade-engine.js`, en `classifyAcademicNeed`. El cálculo mantiene el total mínimo de 153 y el promedio mínimo de 51. La recomendación, los colores, el resultado y los mensajes de WhatsApp usan el mismo objeto `result.status`.

El componente `components/academic-support-cta.blade.php` se incluye desde el parcial compartido del simulador, tanto en la ruta pública como en el dashboard. `public/js/ife-grade-simulator.js` actualiza su contenido y reutiliza el selector de materias existente. En dos notas se conserva el registro por materia/departamento; en una nota se identifica expresamente la segunda nota como simulada.

En dos notas, una barra permite simular el tercer trimestre entre 0 y 100. Empieza en la nota mínima requerida (limitada al rango del control), separa el tramo rojo del verde y mantiene una marca fija en el mínimo. Si la aprobación está asegurada, toda la barra es verde; si ni con 100 alcanza, es roja. Moverla actualiza únicamente la proyección: conserva la clasificación, la recomendación y el mensaje basados en las dos notas reales, sin registrar notas simuladas como consultas nuevas. La fila duplicada de notas reales permanece oculta.

Configuración en `config/ife.php`, con estas variables opcionales de entorno:

```dotenv
IFE_NOTAS_SUPPORT_WHATSAPP=59160902299
IFE_NOTAS_SCHOOL_YEAR_END_DATE=2026-12-02
```

El número de apoyo es independiente del contacto general `IFE_WHATSAPP`. La fecha se interpreta como día de calendario de `America/La_Paz`. El reloj parte de la hora del servidor; la página actualiza el contexto cada minuto. Después del cierre, la recomendación y la solicitud por WhatsApp pasan a refuerzo y preparación para la siguiente etapa, sin alterar el estado académico. Si la configuración de fecha no es válida, se omite la urgencia temporal.

El bloque mantiene una sola estructura: marca IFE, título con la situación académica, una frase breve, CTA y «Compartir nota». La frase usa la materia real y los textos definidos en la clasificación centralizada, sin repetir notas ni la nota necesaria. El selector de materia de una nota está junto a los controles, fuera del bloque comercial. La fecha adapta esa misma frase, sin añadir otro párrafo ni ocultar el estado del título; tras el cierre se ofrece refuerzo para la siguiente etapa. Los casos positivos y matemáticamente imposibles no reciben presión por la fecha.

El botón conserva el verde WhatsApp `#25D366`, texto blanco e icono SVG de WhatsApp en todos los estados. Su animación es discreta y se desactiva con `prefers-reduced-motion`. El acento y el título reflejan la gravedad del resultado sin cambiar todo el fondo.

No se agregó analytics ni se envían notas a servicios externos. El enlace de apoyo solo prepara el mensaje: el estudiante decide abrir WhatsApp y enviarlo. La opción de compartir el resultado sigue disponible como enlace secundario «Compartir nota», sin textos adicionales bajo el CTA.

Verificación:

```powershell
php artisan test
node --test --test-isolation=none tests/js/academic-support.test.mjs
```

Prueba visual sin dependencias adicionales, usando Chrome local y un servidor Laravel en el puerto 8000:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
node tests/js/academic-support.browser.mjs
```

La prueba de navegador intercepta únicamente el guardado de consultas para no insertar datos artificiales. La suite de Laravel comprueba el guardado real usando SQLite en memoria. Se pueden configurar `IFE_TEST_BASE_URL` y `CHROME_PATH` para otro puerto o ejecutable. Las capturas se generan en `storage/app/support-qa`.
