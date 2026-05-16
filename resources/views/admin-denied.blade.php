<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ife notas - acceso restringido</title>
  <link rel="icon" href="{{ asset('images/ife.ico') }}" type="image/x-icon">
  <style>
    :root{--bg:#f3fbfa;--card:#fff;--text:rgb(55,95,122);--muted:#587486;--line:#d6ebe8;--primary:rgb(38,186,165);--primary-dark:rgb(55,95,122)}
    @font-face{font-family:"GlyphaLTStd";src:url("/fonts/GlyphaLTStd-Bold.otf") format("opentype");font-weight:700;font-style:normal;font-display:swap}
    body{margin:0;min-height:100vh;display:grid;place-items:center;padding:16px;font-family:"Segoe UI",sans-serif;background:var(--bg);color:var(--text)}
    .card{max-width:520px;background:var(--card);border:1px solid var(--line);border-radius:12px;padding:18px;text-align:center;box-shadow:0 12px 30px rgba(55,95,122,.07)}
    .btn{display:inline-block;margin-top:12px;background:var(--primary);color:#fff;text-decoration:none;border-radius:10px;padding:10px 14px;font-weight:600;border:0}
    p{color:var(--muted)}
    .brand-name{font-family:"GlyphaLTStd","Segoe UI",sans-serif;text-transform:lowercase;letter-spacing:0}
    .logo{display:block;width:170px;max-width:100%;height:auto;margin:0 auto 12px}
  </style>
</head>
<body>
  <div class="card">
    <h1><img class="logo" src="{{ asset('images/logo.png') }}" alt="ife notas"></h1>
    <p>Este enlace es solo para administradores.</p>
    <p>Si ya sigues a ife notas en TikTok, puedes solicitar la habilitación del simulador y chat.</p>
    <form method="POST" action="{{ route('request.enable') }}">
      @csrf
      <button class="btn" type="submit">Ya te sigo, habilitar</button>
    </form>
  </div>
</body>
</html>
