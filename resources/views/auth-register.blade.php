<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Crear cuenta</title>
  <style>
    :root{--bg:#f5f7f8;--card:#fff;--text:#1f2933;--muted:#66717d;--line:#d9e0e6;--primary:#1f7a6e}
    *{box-sizing:border-box}
    body{margin:0;min-height:100vh;display:grid;place-items:center;padding:16px;background:var(--bg);font-family:"Segoe UI",sans-serif;color:var(--text)}
    .card{width:min(400px,100%);background:var(--card);border:1px solid var(--line);border-radius:12px;padding:18px}
    h1{margin:0 0 10px;font-size:1.35rem}
    label{display:block;margin:9px 0 5px;color:var(--muted)}
    .row{display:grid;grid-template-columns:80px 1fr;gap:7px}
    input{width:100%;padding:10px;border:1px solid var(--line);border-radius:10px}
    input[readonly]{background:#f3f6f8;font-weight:600}
    .btn{width:100%;border:0;border-radius:10px;padding:10px;font-weight:600;background:var(--primary);color:#fff;margin-top:10px;cursor:pointer}
    .error{background:#fdecec;color:#8b1f1f;border-radius:10px;padding:9px;margin-bottom:8px}
    .back{display:inline-block;margin-top:10px;color:var(--muted);text-decoration:none}
  </style>
</head>
<body>
  <form class="card" method="POST" action="{{ route('register') }}">
    @csrf
    <h1>Crear cuenta</h1>
    @if ($errors->any())<div class="error">{{ $errors->first() }}</div>@endif
    <label for="phone">Número</label>
    <div class="row"><input value="+591" readonly><input id="phone" name="phone" type="tel" inputmode="numeric" pattern="\d{8}" maxlength="8" required></div>
    <label for="password">Contraseña</label>
    <input id="password" name="password" type="password" minlength="6" required>
    <label for="password_confirmation">Confirmar contraseña</label>
    <input id="password_confirmation" name="password_confirmation" type="password" minlength="6" required>
    <button class="btn" type="submit">Crear cuenta</button>
    <a class="back" href="{{ route('auth') }}">Volver</a>
  </form>
</body>
</html>
