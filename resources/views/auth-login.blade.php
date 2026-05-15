<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ife notas - iniciar sesión</title>
  <link rel="icon" href="{{ asset('images/ife.ico') }}" type="image/x-icon">
  <style>
    :root{--bg:#f3fbfa;--card:#fff;--text:rgb(55,95,122);--muted:#587486;--line:#d6ebe8;--primary:rgb(38,186,165);--primary-dark:rgb(55,95,122)}
    @font-face{font-family:"GlyphaLTStd";src:url("/fonts/GlyphaLTStd-Bold.otf") format("opentype");font-weight:700;font-style:normal;font-display:swap}
    *{box-sizing:border-box}
    body{margin:0;min-height:100vh;display:grid;place-items:center;padding:16px;background:var(--bg);font-family:"Segoe UI",sans-serif;color:var(--text)}
    .card{width:min(400px,100%);background:var(--card);border:1px solid var(--line);border-radius:12px;padding:18px;box-shadow:0 14px 36px rgba(55,95,122,.08)}
    h1{margin:0 0 10px;font-size:1.35rem}
    .logo{display:block;width:170px;max-width:100%;height:auto;margin:0 0 12px}
    .brand-name{font-family:"GlyphaLTStd","Segoe UI",sans-serif;text-transform:lowercase;letter-spacing:0}
    label{display:block;margin:9px 0 5px;color:var(--muted)}
    .row{display:grid;grid-template-columns:80px 1fr;gap:7px}
    input{width:100%;padding:10px;border:1px solid var(--line);border-radius:10px;color:var(--primary-dark)}
    input:focus{outline:2px solid rgba(38,186,165,.22);border-color:var(--primary)}
    input[readonly]{background:#eef8f6;font-weight:600}
    .btn{width:100%;border:0;border-radius:10px;padding:10px;font-weight:600;background:var(--primary);color:#fff;margin-top:10px;cursor:pointer}
    .error{background:#fdecec;color:#8b1f1f;border-radius:10px;padding:9px;margin-bottom:8px}
    .back{display:inline-block;margin-top:10px;color:var(--primary-dark);text-decoration:none}
  </style>
</head>
<body>
  <form class="card" method="POST" action="{{ route('login') }}">
    @csrf
    <h1><img class="logo" src="{{ asset('images/logo.png') }}" alt="ife notas"></h1>
    @if ($errors->any())<div class="error">{{ $errors->first() }}</div>@endif
    <label for="phone">Número</label>
    <div class="row"><input value="+591" readonly><input id="phone" name="phone" type="tel" inputmode="numeric" pattern="\d{8}" maxlength="8" required></div>
    <label for="password">Contraseña</label>
    <input id="password" name="password" type="password" required>
    <button class="btn" type="submit">Entrar</button>
    <a class="back" href="{{ route('auth') }}">Volver</a>
  </form>
</body>
</html>

