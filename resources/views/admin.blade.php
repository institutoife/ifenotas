<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ife notas - administración</title>
    <link rel="icon" href="{{ asset('images/ife.ico') }}" type="image/x-icon">
    <style>
        :root{--bg:#f3fbfa;--card:#fff;--text:rgb(55,95,122);--muted:#587486;--line:#d6ebe8;--primary:rgb(38,186,165);--primary-dark:rgb(55,95,122);--soft:#e6f8f5}
        @font-face{font-family:"GlyphaLTStd";src:url("/fonts/GlyphaLTStd-Bold.otf") format("opentype");font-weight:700;font-style:normal;font-display:swap}
        *{box-sizing:border-box}
        body{margin:0;font-family:"Segoe UI",sans-serif;background:var(--bg);color:var(--text)}
        .wrap{max-width:980px;margin:0 auto;padding:16px}
        .head,.card{background:var(--card);border:1px solid var(--line);border-radius:12px;box-shadow:0 12px 30px rgba(55,95,122,.07)}
        .head{padding:14px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
        .sub{margin-top:4px;color:var(--muted);font-size:.92rem}
        .card{margin-top:12px;padding:12px}
        .row{display:grid;grid-template-columns:1.2fr 1fr 1fr auto;gap:10px;padding:10px 0;border-top:1px solid var(--line);align-items:center}
        .btn{border:1px solid var(--line);border-radius:10px;padding:8px 12px;font-weight:600;cursor:pointer;background:#fff;color:var(--primary-dark);text-decoration:none}
        .btn-primary{background:var(--primary);border-color:var(--primary);color:#fff}
        .btn-soft{background:var(--soft);border-color:var(--soft);color:var(--primary-dark)}
        .link{color:var(--primary);text-decoration:none;font-weight:600}
        .brand-name{font-family:"GlyphaLTStd","Segoe UI",sans-serif;text-transform:lowercase;letter-spacing:0}
        .logo{display:inline-block;width:128px;max-height:38px;object-fit:contain;vertical-align:middle}
        @media (max-width:800px){.row{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="wrap">
    <div class="head">
        <div>
            <strong><img class="logo" src="{{ asset('images/logo.png') }}" alt="ife notas"> - administración</strong>
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

