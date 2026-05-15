<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Impulso Escolar</title>
  <style>
    :root{--bg:#f6f7f8;--card:#ffffff;--text:#1f2933;--muted:#5b6672;--line:#d9e0e6;--primary:#1f7a6e;--primary-soft:#e4f3f1}
    *{box-sizing:border-box}
    body{margin:0;font-family:"Segoe UI",sans-serif;background:var(--bg);color:var(--text);line-height:1.45}
    .container{max-width:920px;margin:0 auto;padding:20px 16px}
    .panel{background:var(--card);border:1px solid var(--line);border-radius:12px;padding:20px}
    h1,h2{margin:0 0 10px}
    p{margin:0;color:var(--muted)}
    .actions{margin-top:16px;display:flex;gap:10px;flex-wrap:wrap}
    .btn{display:inline-block;text-decoration:none;border-radius:10px;padding:10px 14px;font-weight:600}
    .btn-primary{background:var(--primary);color:#fff}
    .btn-soft{background:var(--primary-soft);color:var(--primary)}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:18px}
    .item{background:#fff;border:1px solid var(--line);border-radius:10px;padding:14px}
    .section{margin-top:14px}
    .list{margin:8px 0 0;padding-left:18px;color:var(--muted)}
    .list li{margin:4px 0}
    footer{padding:22px 0;text-align:center;color:var(--muted)}
    @media(max-width:760px){.grid{grid-template-columns:1fr}}
  </style>
</head>
<body>
  <main class="container">
    <section class="panel">
      <h1>Calcula tu nota con claridad</h1>
      <p>Una herramienta simple para saber cuánto necesitas en los próximos trimestres y tomar decisiones a tiempo.</p>
      <div class="actions">
        <a class="btn btn-primary" href="{{ route('login.view') }}">Iniciar sesión</a>
        <a class="btn btn-soft" href="{{ route('register.view') }}">Crear cuenta</a>
      </div>
    </section>

    <section class="grid section">
      <article class="item">
        <h2 style="font-size:1.1rem;">Qué puedes hacer</h2>
        <ul class="list">
          <li>Calcular lo necesario para aprobar.</li>
          <li>Simular escenarios para el 2do y 3er trimestre.</li>
          <li>Guardar resultados para revisarlos después.</li>
        </ul>
      </article>
      <article class="item">
        <h2 style="font-size:1.1rem;">Contacto rápido</h2>
        <p>Si necesitas apoyo escolar, puedes escribirnos directamente.</p>
        <div class="actions" style="margin-top:12px;">
          <a class="btn btn-soft" href="https://wa.me/59171324941" target="_blank">WhatsApp</a>
        </div>
      </article>
    </section>
  </main>
  <footer>Impulso Escolar</footer>
</body>
</html>
