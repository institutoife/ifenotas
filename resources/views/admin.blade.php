<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de administración</title>
    <style>
        :root{--bg:#f5f7f8;--card:#fff;--text:#1f2933;--muted:#66717d;--line:#d9e0e6;--primary:#1f7a6e;--soft:#e7f3f1}
        *{box-sizing:border-box}
        body{margin:0;font-family:"Segoe UI",sans-serif;background:var(--bg);color:var(--text)}
        .wrap{max-width:980px;margin:0 auto;padding:16px}
        .head,.card{background:var(--card);border:1px solid var(--line);border-radius:12px}
        .head{padding:14px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
        .sub{margin-top:4px;color:var(--muted);font-size:.92rem}
        .card{margin-top:12px;padding:12px}
        .row{display:grid;grid-template-columns:1.2fr 1fr 1fr auto;gap:10px;padding:10px 0;border-top:1px solid var(--line);align-items:center}
        .btn{border:1px solid var(--line);border-radius:10px;padding:8px 12px;font-weight:600;cursor:pointer;background:#fff;color:var(--text);text-decoration:none}
        .btn-primary{background:var(--primary);border-color:var(--primary);color:#fff}
        .btn-soft{background:var(--soft);border-color:var(--soft);color:var(--primary)}
        .link{color:var(--primary);text-decoration:none;font-weight:600}
        @media (max-width:800px){.row{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="wrap">
    <div class="head">
        <div>
            <strong>Panel de administración</strong>
            <div class="sub">Gestión de usuarios y solicitudes</div>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-soft">Volver</a>
    </div>

    @if(session('status'))
        <div class="card">{{ session('status') }}</div>
    @endif

    <div class="card">
        <h3>Solicitudes pendientes</h3>
        @forelse($pendingRequests as $request)
            <div class="row">
                <div>{{ $request->user?->phone ?? 'Sin usuario' }}</div>
                <div>Estado: {{ $request->status }}</div>
                <div>{{ $request->created_at }}</div>
                <div><a class="link" href="{{ URL::temporarySignedRoute('admin.requests.show', now()->addDays(7), ['enableRequest' => $request->id]) }}">Revisar</a></div>
            </div>
        @empty
            <p class="sub">No hay solicitudes pendientes.</p>
        @endforelse
    </div>

    <div class="card">
        <h3>Usuarios</h3>
        <div class="row" style="border-top:0;font-weight:700;"><div>Teléfono</div><div>Rol</div><div>Simulador</div><div>Acción</div></div>
        @foreach($users as $u)
            <div class="row">
                <div>{{ $u->phone ?? '-' }}</div>
                <div>{{ $u->is_admin ? 'Admin' : 'Estudiante' }}</div>
                <div>{{ $u->is_follower_unlocked ? 'Habilitado' : 'Bloqueado' }}</div>
                <div>
                    <form method="POST" action="{{ route('admin.toggleFollower', $u) }}">@csrf<button class="btn btn-primary" type="submit">Cambiar</button></form>
                </div>
            </div>
        @endforeach
    </div>
</div>
</body>
</html>
