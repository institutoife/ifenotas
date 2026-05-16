<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ife notas - administración</title>
    <link rel="icon" href="{{ asset('images/ife.ico') }}" type="image/x-icon">
    <style>
        :root{--bg:#f3fbfa;--card:#fff;--text:rgb(55,95,122);--muted:#587486;--line:#d6ebe8;--primary:rgb(38,186,165);--primary-dark:rgb(55,95,122);--soft:#e6f8f5;--ok:#138a4d;--ok-bg:#e8f8ef;--danger:#c83232;--danger-bg:#fdeaea;--warn:#9a6a00;--warn-bg:#fff4d6}
        @font-face{font-family:"GlyphaLTStd";src:url("/fonts/GlyphaLTStd-Bold.otf") format("opentype");font-weight:700;font-style:normal;font-display:swap}
        *{box-sizing:border-box}
        body{margin:0;font-family:"Segoe UI",sans-serif;background:var(--bg);color:var(--text)}
        .wrap{max-width:980px;margin:0 auto;padding:16px}
        .head,.card{background:var(--card);border:1px solid var(--line);border-radius:12px;box-shadow:0 12px 30px rgba(55,95,122,.07)}
        .head{padding:14px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
        .sub{margin-top:4px;color:var(--muted);font-size:.92rem}
        .card{margin-top:12px;padding:12px}
        .row{display:grid;grid-template-columns:1.2fr 1fr 1fr auto;gap:10px;padding:10px 0;border-top:1px solid var(--line);align-items:center}
        .filters{display:grid;grid-template-columns:1.2fr repeat(4,1fr) auto;gap:8px;align-items:end;margin:10px 0 6px}
        .field{display:grid;gap:4px}
        .field label{font-size:.78rem;font-weight:700;color:var(--muted)}
        .field input,.field select{width:100%;border:1px solid var(--line);border-radius:10px;padding:8px 10px;background:#fff;color:var(--text);font:inherit}
        .stats{display:flex;gap:8px;flex-wrap:wrap;margin:10px 0}
        .calc-row{display:grid;grid-template-columns:1.1fr 1fr .8fr .9fr 1.2fr .9fr;gap:10px;padding:10px 0;border-top:1px solid var(--line);align-items:center}
        .calc-head{font-weight:800;color:var(--primary-dark)}
        .calc-meta{color:var(--muted);font-size:.86rem}
        .actions-inline{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
        .btn{border:1px solid var(--line);border-radius:10px;padding:8px 12px;font-weight:600;cursor:pointer;background:#fff;color:var(--primary-dark);text-decoration:none}
        .btn-primary{background:var(--primary);border-color:var(--primary);color:#fff}
        .btn-soft{background:var(--soft);border-color:var(--soft);color:var(--primary-dark)}
        .link{color:var(--primary);text-decoration:none;font-weight:600}
        .status-pill{display:inline-flex;align-items:center;gap:7px;width:max-content;border-radius:999px;padding:6px 10px;font-size:.84rem;font-weight:800;line-height:1;text-transform:uppercase;letter-spacing:.02em}
        .status-pill::before{content:"";width:9px;height:9px;border-radius:999px;background:currentColor;box-shadow:0 0 0 3px rgba(255,255,255,.75)}
        .status-enabled{background:var(--ok-bg);color:var(--ok);border:1px solid rgba(19,138,77,.22)}
        .status-blocked{background:var(--danger-bg);color:var(--danger);border:1px solid rgba(200,50,50,.24)}
        .status-pending{background:var(--warn-bg);color:var(--warn);border:1px solid rgba(154,106,0,.22)}
        .status-regular{background:var(--warn-bg);color:var(--warn);border:1px solid rgba(154,106,0,.22)}
        .brand-name{font-family:"GlyphaLTStd","Segoe UI",sans-serif;text-transform:lowercase;letter-spacing:0}
        .logo{display:inline-block;width:128px;max-height:38px;object-fit:contain;vertical-align:middle}
        @media (max-width:900px){.row,.filters,.calc-row{grid-template-columns:1fr}.calc-head{display:none}}
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
                <div><span class="status-pill status-pending">Pendiente</span></div>
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
                <div>
                    @if($u->is_follower_unlocked)
                        <span class="status-pill status-enabled">Habilitado</span>
                    @else
                        <span class="status-pill status-blocked">Bloqueado</span>
                    @endif
                </div>
                <div class="actions-inline">
                    <form method="POST" action="{{ route('admin.toggleFollower', $u) }}">@csrf<button class="btn btn-primary" type="submit">Cambiar</button></form>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <h3>Notas y materias registradas</h3>
        <form class="filters" method="GET" action="{{ route('admin') }}">
            <div class="field">
                <label for="search">Buscar</label>
                <input id="search" name="search" value="{{ $calculationFilters['search'] }}" placeholder="Teléfono, colegio o materia">
            </div>
            <div class="field">
                <label for="subject">Materia</label>
                <select id="subject" name="subject">
                    <option value="">Todas</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject }}" @selected($calculationFilters['subject'] === $subject)>{{ $subject }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="quality">Calificación</label>
                <select id="quality" name="quality">
                    <option value="">Todas</option>
                    <option value="bad" @selected($calculationFilters['quality'] === 'bad')>Mala</option>
                    <option value="regular" @selected($calculationFilters['quality'] === 'regular')>Regular</option>
                    <option value="good" @selected($calculationFilters['quality'] === 'good')>Buena</option>
                </select>
            </div>
            <div class="field">
                <label for="kind">Tipo</label>
                <select id="kind" name="kind">
                    <option value="">Todos</option>
                    <option value="calculation" @selected($calculationFilters['kind'] === 'calculation')>Cálculo</option>
                    <option value="simulation" @selected($calculationFilters['kind'] === 'simulation')>Simulación</option>
                </select>
            </div>
            <div class="field">
                <label for="status">Estado</label>
                <select id="status" name="status">
                    <option value="">Todos</option>
                    <option value="ok" @selected($calculationFilters['status'] === 'ok')>Correcto</option>
                    <option value="passed" @selected($calculationFilters['status'] === 'passed')>Encaminado</option>
                    <option value="warning" @selected($calculationFilters['status'] === 'warning')>En riesgo</option>
                    <option value="risk" @selected($calculationFilters['status'] === 'risk')>Crítico</option>
                </select>
            </div>
            <button class="btn btn-primary" type="submit">Filtrar</button>
        </form>
        <div class="stats">
            <span class="status-pill status-enabled">Buena: {{ $calculations->where('first_term', '>=', 70)->count() }}</span>
            <span class="status-pill status-regular">Regular: {{ $calculations->whereBetween('first_term', [51, 69])->count() }}</span>
            <span class="status-pill status-blocked">Mala: {{ $calculations->where('first_term', '<', 51)->count() }}</span>
            <a class="btn btn-soft" href="{{ route('admin') }}">Limpiar filtros</a>
        </div>

        <div class="calc-row calc-head">
            <div>Usuario</div>
            <div>Materia</div>
            <div>Calificación</div>
            <div>Notas</div>
            <div>Colegio</div>
            <div>Fecha</div>
        </div>
        @forelse($calculations as $calculation)
            @php
                $qualityClass = $calculation->first_term < 51 ? 'status-blocked' : ($calculation->first_term < 70 ? 'status-regular' : 'status-enabled');
                $qualityText = $calculation->first_term < 51 ? 'Mala' : ($calculation->first_term < 70 ? 'Regular' : 'Buena');
            @endphp
            <div class="calc-row">
                <div>
                    {{ $calculation->user?->phone ?? 'Sin usuario' }}
                    <div class="calc-meta">{{ $calculation->user?->name ?? '-' }}</div>
                </div>
                <div>
                    {{ $calculation->subject }}
                    <div class="calc-meta">{{ $calculation->kind === 'simulation' ? 'Simulación' : 'Cálculo' }}</div>
                </div>
                <div><span class="status-pill {{ $qualityClass }}">{{ $qualityText }}</span></div>
                <div>
                    T1: {{ $calculation->first_term }}
                    @if($calculation->kind === 'simulation')
                        · T2: {{ $calculation->second_term }} · T3: {{ $calculation->third_term_needed }}
                    @else
                        · Prom.: {{ $calculation->third_term_needed }}
                    @endif
                    <div class="calc-meta">{{ $calculation->summary }}</div>
                </div>
                <div>
                    {{ $calculation->school?->nombre ?? data_get($calculation->meta, 'school_text', 'Sin colegio') }}
                    @if($calculation->school?->codigo_rue)
                        <div class="calc-meta">RUE: {{ $calculation->school->codigo_rue }}</div>
                    @endif
                </div>
                <div>{{ $calculation->created_at?->format('d/m/Y H:i') }}</div>
            </div>
        @empty
            <p class="sub">No hay notas registradas con esos filtros.</p>
        @endforelse
    </div>
</div>
</body>
</html>

