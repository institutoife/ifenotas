<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gráficos del simulador · IFE</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f3fbfa;color:#375f7a;font:16px Arial,sans-serif}main{max-width:1200px;margin:auto;padding:24px}header,nav,form,.controls{display:flex;gap:16px;flex-wrap:wrap;align-items:center}header{justify-content:space-between}a{color:#137e70}h1{font-size:28px}h2{margin:0 0 12px;font-size:22px}.note{color:#587486;line-height:1.5;font-size:14px}.panel{min-width:0;background:white;border:1px solid #d6e8e5;border-radius:16px;padding:20px;margin:20px 0}form{align-items:end}label{display:grid;gap:6px}input,select,button{font:inherit;padding:10px;border:1px solid #b9d3d0;border-radius:8px;max-width:100%}button{background:#375f7a;color:white;cursor:pointer}.charts{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px}.charts .panel{margin:0}.chart{width:100%;height:340px}.summary{font-size:18px;font-weight:700}.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse;white-space:nowrap}th,td{text-align:left;padding:12px;border-bottom:1px solid #d6e8e5}.empty{padding:24px;text-align:center}.controls{margin-top:16px}.failed{color:#bb2525}.passed{color:#26baa5}.pending{color:#375f7a}@media(max-width:700px){main{padding:12px}.charts{grid-template-columns:1fr}.panel{padding:14px}.chart{height:300px}header{align-items:flex-start}h1{font-size:24px}}
        .sort-button{background:transparent;color:#375f7a;border:0;padding:4px;text-align:left;font-weight:700;white-space:nowrap}.sort-button:focus-visible{outline:2px solid #26baa5}tfoot{font-weight:700;background:#f3fbfa}
    </style>
</head>
<body><main>
    <header><h1>Gráficos y porcentajes</h1><nav><a href="{{ route('admin.simulator-records', $filters) }}">Cantidades y notas</a><a href="{{ route('admin') }}">Administración</a></nav></header>
    <p class="note">Análisis de consultas registradas, no de estudiantes únicos. Una persona puede realizar varias consultas.</p>
    <form method="GET">
        <label>Materia<select name="subject"><option value="">Todas</option>@foreach(config('ife.subjects') as $subject)<option value="{{ $subject }}" @selected(($filters['subject'] ?? '') === $subject)>{{ $subject }}</option>@endforeach</select></label>
        <label>Desde<input type="date" name="from" value="{{ $filters['from'] ?? '' }}"></label>
        <label>Hasta<input type="date" name="to" value="{{ $filters['to'] ?? '' }}"></label>
        <button type="submit">Actualizar datos</button><a href="{{ route('admin.simulator-charts') }}">Limpiar filtros</a>
    </form>
    @if($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif
    @if($total)
        <section class="panel" aria-labelledby="overall-title">
            <h2 id="overall-title">Explorar resultados</h2>
            <p class="note">Pasa el cursor para ver cantidades y porcentajes. Selecciona una materia en las barras inferiores para analizarla en los dos gráficos superiores.</p>
            <div class="controls">
                <label>Detalle de materia<select id="chart-subject"><option value="all">Todas las materias filtradas</option>@foreach($subjectStats as $index=>$stat)<option value="{{ $index }}">{{ $stat['subject'] }}</option>@endforeach</select></label>
                <label>Escala de barras<select id="chart-scale"><option value="percent">Porcentajes</option><option value="count">Cantidades</option></select></label>
                <label>Orden de materias<select id="chart-order"><option value="failed">Mayor % de reprobados</option><option value="total">Más consultas</option><option value="subject">Nombre de materia</option></select></label>
            </div>
            <p id="chart-summary" class="summary" aria-live="polite">{{ $total }} consultas</p>
            <p id="chart-loading" class="note" role="status">Cargando Google Charts…</p>
        </section>
        <div class="charts">
            <section class="panel"><h2>Distribución circular</h2><div class="chart" id="status-pie" role="img" aria-label="Porcentajes por estado; los valores están disponibles en la tabla inferior"></div></section>
            <section class="panel"><h2>Comparación por estado</h2><div class="chart" id="status-bars" role="img" aria-label="Barras por estado; los valores están disponibles en la tabla inferior"></div></section>
        </div>
        <section class="panel"><h2>Comparación por materia</h2><p class="note">Rojo: reprobados · Turquesa: ya aprobaron · Azul IFE: en carrera. En porcentajes, cada barra usa el total de su materia como base.</p><div class="chart" id="subject-bars" role="img" aria-label="Barras apiladas por materia; selecciona una barra para ver su detalle"></div></section>
    @foreach(['failed'=>'Reprobados','passed'=>'Ya aprobaron','pending'=>'En carrera'] as $key=>$label)
        <section class="panel"><h2>{{ $label }} por materia</h2>
            <p class="note">El circular muestra cómo se distribuyen las consultas de este estado entre materias. Las barras comparan su cantidad o el porcentaje dentro de cada materia, según la escala seleccionada arriba.</p>
            <p class="note" id="{{ $key }}-empty" hidden>No hay consultas de este estado en el período seleccionado.</p>
            <div class="charts">
                <div><h3>Distribución entre materias</h3><div class="chart" id="{{ $key }}-pie" role="img" aria-label="{{ $label }}: distribución circular por materia"></div></div>
                <div><h3>Comparación entre materias</h3><div class="chart" id="{{ $key }}-bars" role="img" aria-label="{{ $label }}: comparación de barras por materia"></div></div>
            </div>
        </section>
    @endforeach

    @else
        <section class="panel"><h2 id="overall-title">Distribución general</h2><p class="empty">Sin consultas para calcular porcentajes.</p></section>
    @endif
    @foreach(['counts'=>'Cantidades', 'percent'=>'Porcentajes'] as $tableType=>$tableTitle)
    <section class="panel"><h2>Datos del análisis · {{ $tableTitle }}</h2>
        <p class="note">Pulsa cualquier encabezado para alternar entre orden ascendente y descendente. @if($tableType === 'percent') Cada porcentaje usa el total de consultas de su materia. @endif</p>
        <div class="table-wrap"><table data-sortable>
            <thead><tr>@foreach(['Materia', 'Total de consultas', 'Reprobados', 'Ya aprobaron', 'En carrera'] as $heading)<th scope="col" aria-sort="none"><button type="button" class="sort-button" data-column="{{ $loop->index }}" data-type="{{ $loop->first ? 'text' : 'number' }}">{{ $heading }} <span aria-hidden="true">↕</span></button></th>@endforeach</tr></thead>
            <tbody>@foreach($subjectStats as $stat)<tr><th scope="row" data-value="{{ $stat['subject'] }}">{{ $stat['subject'] }}</th><td data-value="{{ $stat['total'] }}">{{ $stat['total'] }}</td>@foreach(['failed','passed','pending'] as $key)<td class="{{ $key }}" data-value="{{ $tableType === 'percent' ? $stat[$key.'_percent'] : $stat[$key] }}">{{ $tableType === 'percent' ? number_format($stat[$key.'_percent'], 1, ',', '.').'%' : $stat[$key] }}</td>@endforeach</tr>@endforeach</tbody>
            <tfoot><tr><th>Total filtrado</th><td>{{ $total }}</td>@foreach(['failed','passed','pending'] as $key)<td class="{{ $key }}">{{ $tableType === 'percent' ? number_format($percentages[$key], 1, ',', '.').'%' : ($counts[$key] ?? 0) }}</td>@endforeach</tr></tfoot>
        </table></div>
    </section>
    @endforeach

    @if($total)
        <script type="application/json" id="simulator-chart-data">@json($subjectStats)</script>
        <script src="{{ asset('js/ife-admin-charts.js') }}?v=3" defer></script>
    @endif
</main></body></html>
