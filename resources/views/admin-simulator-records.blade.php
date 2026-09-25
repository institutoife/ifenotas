<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Consultas del simulador · IFE</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f3fbfa;color:#375f7a;font-family:Arial,sans-serif}.wrap{max-width:1050px;margin:auto;padding:20px}header{display:flex;justify-content:space-between;align-items:center;gap:15px}a{color:#137e70}.stats,form{display:flex;flex-wrap:wrap;gap:12px;margin:20px 0}.stat,.panel{background:white;border:1px solid #d6e8e5;border-radius:12px;padding:18px}.stat{flex:1;min-width:150px}.stat b{display:block;font-size:32px;margin-top:8px}.failed{color:#bb2525}.passed{color:#168046}.pending{color:#966700}label{display:grid;gap:6px}input,select,button{font:inherit;padding:9px;border:1px solid #cbdcde;border-radius:8px}button{background:#375f7a;color:white;cursor:pointer}form{align-items:end}.table-wrap{overflow-x:auto}table{border-collapse:collapse;width:100%;white-space:nowrap}th,td{text-align:left;padding:12px;border-bottom:1px solid #d6e8e5}.note{font-size:14px;line-height:1.5;color:#587486}.pagination{display:flex;justify-content:space-between;margin-top:18px}
    </style>
</head>
<body><main class="wrap">
    <header><h1>Consultas del simulador</h1><a href="{{ route('admin') }}">Volver a administración</a></header>
    <p class="note">Cada registro corresponde a una consulta confirmada con el boton Consultar y una materia. Los totales cuentan consultas, no estudiantes unicos. Escribir o mover la barra no guarda registros.</p>
    <div class="stats">
        <div class="stat">Total de consultas<b>{{ $counts->sum() }}</b></div>
        <div class="stat failed">Aplazados<b>{{ $counts['failed'] ?? 0 }}</b></div>
        <div class="stat passed">Ya aprobaron<b>{{ $counts['passed'] ?? 0 }}</b></div>
        <div class="stat pending">Aún necesitan nota<b>{{ $counts['pending'] ?? 0 }}</b></div>
    </div>
    <form method="GET"><label>Materia<select name="subject"><option value="">Todas</option>@foreach(config('ife.subjects') as $subject)<option value="{{ $subject }}" @selected(($filters['subject'] ?? '') === $subject)>{{ $subject }}</option>@endforeach</select></label>
        <label>Estado<select name="status"><option value="">Todos</option>@foreach(['failed'=>'Aplazado','passed'=>'Ya aprobado','pending'=>'Pendiente'] as $key=>$label)<option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>@endforeach</select></label>
        <label>Desde<input type="date" name="from" value="{{ $filters['from'] ?? '' }}"></label>
        <label>Hasta<input type="date" name="to" value="{{ $filters['to'] ?? '' }}"></label>
        <button type="submit">Filtrar</button><a href="{{ route('admin.simulator-records') }}">Limpiar</a>
    </form>
    @if($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif
    <p class="note">Aplazado: necesita más de 100. Ya aprobado: necesita 0. Los contadores corresponden al período seleccionado.</p>
    <div class="panel"><div class="table-wrap"><table>
        <thead><tr><th>Fecha</th><th>Materia</th><th>1.ª nota</th><th>2.ª nota</th><th>Necesita</th><th>Estado</th></tr></thead>
        <tbody>@forelse($records as $record)<tr><td>{{ $record->created_at->format('d/m/Y H:i') }}</td><td>{{ $record->subject ?? 'Sin materia (registro anterior)' }}</td><td>{{ $record->first }}</td><td>{{ $record->second }}</td><td>{{ $record->required_third }}</td><td class="{{ $record->status }}">{{ ['failed'=>'Aplazado','passed'=>'Ya aprobado','pending'=>'Pendiente'][$record->status] }}</td></tr>@empty<tr><td colspan="6">No hay consultas registradas con estos filtros.</td></tr>@endforelse</tbody>
    </table></div><nav class="pagination" aria-label="Páginas">@if($records->previousPageUrl())<a href="{{ $records->previousPageUrl() }}">Anterior</a>@endif<span>Página {{ $records->currentPage() }} de {{ $records->lastPage() }}</span>@if($records->hasMorePages())<a href="{{ $records->nextPageUrl() }}">Siguiente</a>@endif</nav></div>
</main></body></html>
