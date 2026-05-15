<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acceso restringido</title>
  <style>
    :root{--bg:#f5f7f8;--card:#fff;--text:#1f2933;--muted:#66717d;--line:#d9e0e6;--primary:#1f7a6e}
    body{margin:0;min-height:100vh;display:grid;place-items:center;padding:16px;font-family:"Segoe UI",sans-serif;background:var(--bg);color:var(--text)}
    .card{max-width:520px;background:var(--card);border:1px solid var(--line);border-radius:12px;padding:18px;text-align:center}
    .btn{display:inline-block;margin-top:12px;background:var(--primary);color:#fff;text-decoration:none;border-radius:10px;padding:10px 14px;font-weight:600;border:0}
    p{color:var(--muted)}
  </style>
</head>
<body>
  <div class="card">
    <h1>No tienes permisos de administrador para acceder a esta sección.</h1>
    <p>Puedes volver a solicitar que habiliten tu acceso al simulador.</p>
    <form method="POST" action="{{ route('request.enable') }}">
      @csrf
      <button class="btn" type="submit">Solicitar habilitación nuevamente</button>
    </form>
  </div>
</body>
</html>
