<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ife notas - solicitud</title>
    <link rel="icon" href="{{ asset('images/ife.ico') }}" type="image/x-icon">
    <style>
        :root{--bg:#f3fbfa;--card:#fff;--text:rgb(55,95,122);--muted:#587486;--line:#d6ebe8;--primary:rgb(38,186,165);--primary-dark:rgb(55,95,122)}
        @font-face{font-family:"GlyphaLTStd";src:url("/fonts/GlyphaLTStd-Bold.otf") format("opentype");font-weight:700;font-style:normal;font-display:swap}
        body{margin:0;padding:16px;font-family:"Segoe UI",sans-serif;background:var(--bg);color:var(--text)}
        .card{max-width:680px;margin:0 auto;background:var(--card);border:1px solid var(--line);border-radius:12px;padding:18px;box-shadow:0 12px 30px rgba(55,95,122,.07)}
        .btn{border:0;border-radius:10px;padding:10px 14px;font-weight:600;background:var(--primary);color:#fff;cursor:pointer}
        .link{display:inline-block;margin-top:10px;color:var(--primary-dark);text-decoration:none}
        p{color:var(--muted)}
        .brand-name{font-family:"GlyphaLTStd","Segoe UI",sans-serif;text-transform:lowercase;letter-spacing:0}
        .logo{display:block;width:170px;max-width:100%;height:auto;margin:0 0 12px}
    </style>
</head>
<body>
    <div class="card">
        <h1><img class="logo" src="{{ asset('images/logo.png') }}" alt="ife notas"></h1>
        <p><strong>Usuario:</strong> {{ $enableRequest->user?->phone ?? 'No disponible' }}</p>
        <p><strong>Estado:</strong> {{ $enableRequest->status }}</p>
        <form method="POST" action="{{ route('admin.requests.approve', $enableRequest) }}">
            @csrf
            <button type="submit" class="btn">Dar de alta y habilitar simulador y chat</button>
        </form>
        <a href="{{ route('admin') }}" class="link">Volver al panel de administración</a>
    </div>
</body>
</html>

