<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Informe de notas · Ecuador · IFE</title>
    <link rel="icon" href="{{ asset('images/icono-ife-educabol-instituto-formacion-educabol.svg') }}" type="image/svg+xml">
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f3fbfa;color:#375F7A;font:16px/1.5 "Segoe UI",Arial,sans-serif}.wrap{max-width:1200px;margin:auto;padding:20px}header{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}h1{margin:0;font-size:clamp(1.5rem,5vw,2.2rem);line-height:1.2}a{color:#137e70}.note{color:#587486;font-size:.9rem}.btn{display:inline-flex;align-items:center;justify-content:center;min-height:44px;border:1px solid #cfe7e4;border-radius:10px;background:#fff;padding:10px 14px;color:#375F7A;text-decoration:none;font:inherit;font-weight:700;cursor:pointer}.primary{background:#375F7A;border-color:#375F7A;color:#fff}.btn:focus-visible,input:focus-visible,select:focus-visible{outline:3px solid #26BAA5;outline-offset:3px}.btn[aria-disabled=true]{opacity:.5;cursor:default}.stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin:24px 0}.stat,.panel{min-width:0;border:1px solid #d6e8e5;border-radius:14px;background:#fff;padding:18px}.stat strong{display:block;margin-top:8px;font-size:2rem;line-height:1.1}.possible{color:#137447}.impossible{color:#a42626}.filters{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;align-items:end}label{display:grid;gap:6px;font-weight:650;font-size:.9rem}input,select{width:100%;min-width:0;min-height:44px;border:1px solid #bdcfce;border-radius:9px;background:#fff;padding:9px;color:#375F7A;font:inherit;font-size:16px}.actions{display:flex;gap:10px;flex-wrap:wrap;margin:16px 0}.errors{border-left:4px solid #b42318;padding:12px;color:#9c241b;background:#fff}.table-wrap{overflow-x:auto}table{width:100%;border-collapse:collapse;text-align:left;font-size:.93rem}th,td{padding:13px 10px;border-bottom:1px solid #d6e8e5;vertical-align:top}th{white-space:nowrap}td{overflow-wrap:anywhere}td small{display:block;color:#587486}.grade{white-space:nowrap;font-weight:800}.pill{display:inline-block;border-radius:999px;padding:5px 9px;font-size:.82rem;font-weight:800;line-height:1.3}.pill.possible{background:#e8f8ef}.pill.impossible{background:#fdeaea}.pagination{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:20px}.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}.empty{padding:24px 0;text-align:center}
        @media(max-width:760px){.filters{grid-template-columns:repeat(2,minmax(0,1fr))}.wrap{padding:14px}}
        @media(max-width:520px){.filters,.stats{grid-template-columns:1fr}.stat{padding:14px}.stat strong{font-size:1.7rem}.panel{padding:12px}table,tbody,tr,td{display:block}thead{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0)}tbody tr{border:1px solid #d6e8e5;border-radius:12px;margin-top:12px;padding:8px}td{display:grid;grid-template-columns:minmax(90px,.85fr) minmax(0,1.15fr);gap:10px;padding:9px 4px;white-space:normal}td::before{content:attr(data-label);font-weight:700}td:last-child{border-bottom:0}.pagination{justify-content:center}}
    </style>
</head>
<body>
<main class="wrap">
    <header>
        <h1>Informe de notas · Ecuador</h1>
        <a class="btn" href="{{ route('admin') }}">Volver a administración</a>
    </header>
    <p class="note">Solo consultas con dos notas reales sobre 10. Datos de Ecuador, independientes de Bolivia. No se incluyen escenarios simulados.</p>

    @if($errors->any())
        <div class="errors" role="alert">Revisa los filtros:
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="stats" aria-label="Resumen de los filtros actuales">
        <div class="stat">Consultas registradas<strong>{{ number_format($total, 0, ',', '.') }}</strong></div>
        <div class="stat possible">Pueden alcanzar 7,00<strong>{{ number_format((int) ($counts['possible'] ?? 0), 0, ',', '.') }}</strong></div>
        <div class="stat impossible">No alcanza ni con 10,00<strong>{{ number_format((int) ($counts['impossible'] ?? 0), 0, ',', '.') }}</strong></div>
    </section>

    <section class="panel" aria-labelledby="filtersTitle">
        <h2 id="filtersTitle" class="sr-only">Filtrar consultas de Ecuador</h2>
        <form method="GET" action="{{ route('admin.ecuador-records') }}">
            <div class="filters">
                <label for="ecProvince">Provincia<select id="ecProvince" name="province"><option value="">Todas</option>@foreach(config('ife.countries.ecuador.provinces') as $province)<option value="{{ $province }}" @selected(($filters['province'] ?? '') === $province)>{{ $province }}</option>@endforeach</select></label>
                <label for="ecSubject">Materia<select id="ecSubject" name="subject"><option value="">Todas</option>@foreach(config('ife.countries.ecuador.subjects') as $subject)<option value="{{ $subject }}" @selected(($filters['subject'] ?? '') === $subject)>{{ $subject }}</option>@endforeach<option value="__other__" @selected(($filters['subject'] ?? '') === '__other__')>Otras materias</option></select></label>
                <label for="ecSearch">Buscar materia<input id="ecSearch" type="search" name="search" maxlength="80" value="{{ $filters['search'] ?? '' }}" placeholder="También nombres ingresados en Otra"></label>
                <label for="ecStatus">Resultado<select id="ecStatus" name="status"><option value="">Todos</option><option value="possible" @selected(($filters['status'] ?? '') === 'possible')>Puede alcanzar 7,00</option><option value="impossible" @selected(($filters['status'] ?? '') === 'impossible')>No alcanza ni con 10,00</option></select></label>
                <label for="ecFrom">Desde<input id="ecFrom" type="date" name="from" value="{{ $filters['from'] ?? '' }}"></label>
                <label for="ecTo">Hasta<input id="ecTo" type="date" name="to" value="{{ $filters['to'] ?? '' }}"></label>
            </div>
            <div class="actions"><button class="btn primary" type="submit">Aplicar filtros</button><a class="btn" href="{{ route('admin.ecuador-records') }}">Limpiar filtros</a></div>
        </form>
        <p class="note">Fechas y horas en {{ $timezone }}. Las cantidades corresponden a los filtros actuales, no a visitantes únicos.</p>

        @if($records->isEmpty())
            <p class="empty">No hay consultas guardadas de Ecuador para estos filtros. Los nuevos registros aparecen cuando se pulsa «Calcular» con dos notas reales válidas.</p>
        @else
            <div class="table-wrap">
                <table>
                    <caption class="sr-only">Notas reales y resultados calculados en Ecuador</caption>
                    <thead><tr><th>Registro / fecha</th><th>Provincia</th><th>Materia</th><th>1.º real</th><th>2.º real</th><th>Necesita en 3.º</th><th>Promedio máximo</th><th>Resultado</th></tr></thead>
                    <tbody>
                    @foreach($records as $record)
                        <tr>
                            <td data-label="Registro / fecha">#{{ $record->id }}<small>{{ $record->created_at?->timezone($timezone)->format('d/m/Y H:i') ?? 'Sin fecha' }}</small></td>
                            <td data-label="Provincia">{{ $record->province }}</td>
                            <td data-label="Materia">{{ $record->subject }}@if($record->is_custom_subject)<small>Materia ingresada en «Otra»</small>@endif</td>
                            <td data-label="1.º real" class="grade">{{ \App\Support\EcuadorGrades::format($record->first_cents) }}</td>
                            <td data-label="2.º real" class="grade">{{ \App\Support\EcuadorGrades::format($record->second_cents) }}</td>
                            <td data-label="Necesita en 3.º" class="grade">{{ \App\Support\EcuadorGrades::format($record->required_third_cents) }}@if($record->status === 'impossible')<small>Supera el máximo de 10,00</small>@endif</td>
                            <td data-label="Promedio máximo" class="grade">{{ \App\Support\EcuadorGrades::format($record->maximum_average_cents) }}@if($record->status === 'impossible' && $record->maximum_average_cents >= 700)<small>Inferior a 7,00 antes de redondear</small>@else<small>Con 10,00 en el tercero</small>@endif</td>
                            <td data-label="Resultado"><span class="pill {{ $record->status }}">{{ $record->status === 'possible' ? 'Puede alcanzar 7,00' : 'No alcanza ni con 10,00' }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <nav class="pagination" aria-label="Paginación de consultas">
                @if($records->previousPageUrl())<a class="btn" href="{{ $records->previousPageUrl() }}">← Anterior</a>@else<span class="btn" aria-disabled="true">← Anterior</span>@endif
                <span>Página {{ $records->currentPage() }} de {{ $records->lastPage() }} · {{ $records->total() }} consultas</span>
                @if($records->nextPageUrl())<a class="btn" href="{{ $records->nextPageUrl() }}">Siguiente →</a>@else<span class="btn" aria-disabled="true">Siguiente →</span>@endif
            </nav>
        @endif
        <p class="note">Tres trimestres con igual peso y objetivo de 7,00 exactos por materia. El promedio máximo se redondea solo para mostrarlo; no se usa ese redondeo para decidir si alcanza.</p>
    </section>
</main>
</body>
</html>
