<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de habilitación</title>
    <style>
        :root{--bg:#f5f7f8;--card:#fff;--text:#1f2933;--muted:#66717d;--line:#d9e0e6;--primary:#1f7a6e}
        body{margin:0;padding:16px;font-family:"Segoe UI",sans-serif;background:var(--bg);color:var(--text)}
        .card{max-width:680px;margin:0 auto;background:var(--card);border:1px solid var(--line);border-radius:12px;padding:18px}
        .btn{border:0;border-radius:10px;padding:10px 14px;font-weight:600;background:var(--primary);color:#fff;cursor:pointer}
        .link{display:inline-block;margin-top:10px;color:var(--muted);text-decoration:none}
        p{color:var(--muted)}
    </style>
</head>
<body>
    <div class="card">
        <h1>Revisión de solicitud</h1>
        <p><strong>Usuario:</strong> {{ $enableRequest->user?->phone ?? 'No disponible' }}</p>
        <p><strong>Estado:</strong> {{ $enableRequest->status }}</p>
        <form method="POST" action="{{ route('admin.requests.approve', $enableRequest) }}">
            @csrf
            <button type="submit" class="btn">Dar de alta y habilitar simulador</button>
        </form>
        <a href="{{ route('admin') }}" class="link">Volver al panel de administración</a>
    </div>
</body>
</html>
